<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\T_approval;
use App\Models\Attachment;
use App\Models\T_Message;
use App\Models\MsVendor;
use App\Models\CompanyPG;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\TrCS;
use Vinkla\Hashids\Facades\Hashids;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Mail;
use Barryvdh\DomPDF\Facade\Pdf; 


class PoController extends Controller
{
    public function showPo($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Header PO
        $po = TrPO::findOrFail($id);

        // Detail PO (pakai ponbr sebagai foreign key detail)
        $podetail = TrPOdetail::where('ponbr', $po->ponbr)
            ->orderBy('cs_no') // ganti sesuai nama kolom line kalau berbeda
            ->get();

       
        $attachment = Attachment::where('docid', $po->ponbr)
            ->where('status', 'A')
            ->get();

        $eid_ponbr = Hashids::encode($po->ponbr);

        $prefix = strtoupper(substr((string) $po->sppbjktid, 0, 2));
       if ($prefix === 'PB') {
            $id = TrSPPB::where('sppbid', $po->sppbjktid)->value('id');
        } elseif ($prefix === 'PJ') {
            $id = TrSPPJ::where('sppjid', $po->sppbjktid)->value('id');
        } elseif ($prefix === 'PK') {
            $id = TrSPPK::where('sppkid', $po->sppbjktid)->value('id');
        } elseif ($prefix === 'PT') {
            $id = TrSPPT::where('spptid', $po->sppbjktid)->value('id');
        } else {
            abort(422, 'Invalid doc type');
        }
        $routeMap = [
            'PB' => 'showsppbs',
            'PJ' => 'showsppjs',
            'PK' => 'showsppks',
            'PT' => 'showsppts',
        ];

        // SPPB/J/K/T URL (opsional)
        $sppbUrl = null;
        if (!empty($po->sppbjktid) && isset($routeMap[$prefix])) {
            $sppbHash = Hashids::encode($id);
            $sppbUrl  = url("/{$routeMap[$prefix]}/{$sppbHash}");
        }

        $id = TrCS::where('csid', $po->csid)->value('id');

        // CS URL (opsional)
        $csUrl = null;
        if (!empty($po->csid)) {
            $csHash = Hashids::encode($id);
            $csUrl  = url("/showcs/{$csHash}");
        }

        // Kembalikan ke view
        // - kirim variabel baru: $po, $podetail
        // - plus alias lama ($sppb, $sppbdetail) untuk kompatibilitas view lama
        return view('pages.purchase.showpo', [
            'po'         => $po,
            'podetail'   => $podetail,          
            'attachment' => $attachment,
            'hash'       => $hash, 
            'eid_ponbr' => $eid_ponbr,
            'sppbUrl'    => $sppbUrl,   
            'csUrl'      => $csUrl,     
        ]);
    }

    public function submitPO(Request $req, $ponbr)
    {
        // dd($req->all());
        $po = TrPO::where('ponbr', $ponbr)->firstOrFail();

        if ($po->status !== 'H') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen hanya bisa di-Submit jika status = HOLD (H).'
            ], 422);
        }

        // Terima kedua nama field: po_deliverydate (dari view lama) atau podeliverydate (kolom DB)
        $deliveryDate = $req->input('podeliverydate') ?? $req->input('po_deliverydate');

        // Validasi dinamis sesuai po type
        if (strtoupper($po->potype ?? '') === 'PO') {
            $validated = $req->validate([
                'podeliverydate'   => ['nullable','date'],   // supaya lolos kalau pakai podeliverydate               
            ]);

            if (empty($deliveryDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The podeliverydate field is required.'
                ], 422);
            }

        } else {
            $validated = $req->validate([
                'work_date_from'   => ['required','date'],
                'work_date_to'     => ['required','date','after_or_equal:work_date_from'],
                'work_days'        => ['required','integer','min:0'],
                'work_day_from'    => ['required','string'],
                'work_day_to'      => ['required','string'],
                'work_time_from'   => ['required','date_format:H:i'],
                'work_time_to'     => ['required','date_format:H:i'],
                'manpower_total'   => ['required','integer','min:0'],
                'pic_name'         => ['required','string'],
                'pic_phone'        => ['required','string'],
                // 'payment_method'   => ['required','string'],
                'warranty'         => ['required','string'],
            ]);
        }

        DB::transaction(function () use ($po, $req, $deliveryDate) {
            $now = Carbon::now();
            $po->submitdate = $now;
            $po->updated_by = Auth::user()->username ?? 'system';

            if (strtoupper($po->potype ?? '') === 'PO') {
                // hanya simpan tanggal delivery
                $po->podeliverydate = $deliveryDate ? Carbon::parse($deliveryDate) : null;

                // (opsional) catat sedikit ringkasan di ponote
                $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '') .
                    'Delivery Date: '.Carbon::parse($deliveryDate)->format('d/m/Y'));
            } else {
                // simpan field SPK ke kolom yang tersedia di model
                $po->spkstartworkingdate = $req->input('work_date_from');
                $po->spkendtworkingdate  = $req->input('work_date_to');
                $po->spktotalday         = $req->input('work_days');

                // schedule: "Hari X s/d Y Pukul a s/d b WIB"
                $schedule = sprintf(
                    'Hari %s s/d %s Pukul %s s/d %s WIB',
                    $req->input('work_day_from'),
                    $req->input('work_day_to'),
                    $req->input('work_time_from'),
                    $req->input('work_time_to')
                );
                $po->spkworkschedule = $schedule;

                // manpower & PIC
                $po->spkmanpower = $req->input('manpower_total');
                $po->spkpic      = trim($req->input('pic_name').' || HP '.$req->input('pic_phone'));
                $po->spkwarranty = $req->input('warranty');

                // simpan "cara pembayaran" ke ponote (kolom yang ada)
                $po = strtoupper($req->input('payment_method'));
                // $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '') .
                //     "Cara Pembayaran: {$pm}");
            }

            // ubah status ke Purchase Order
            $po->status = 'P';
            $po->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Submit berhasil. Status berubah menjadi Purchase Order.'
        ]);
    }

    /** POST /po/{ponbr}/cancel-reuse */
    public function ReusePO(Request $req, $ponbr)
    {
        $po = TrPO::where('ponbr', $ponbr)->firstOrFail();

        $data = $req->validate([
            'reason' => ['required','string']
        ]);

        $po->status     = 'R';
        $po->updated_by = Auth::user()->username ?? 'system';

        // simpan reason ke ponote (append)
        $stamp = Carbon::now()->format('d/m/Y H:i');
        $who   = Auth::user()->username ?? 'user';
        $reasonLine = "CANCEL REUSE: ".$data['reason'];       
        $po->save();

        $fakeReq = new \Illuminate\Http\Request([
            'docid'  => $po->ponbr,
            'reason' => $reasonLine,
        ]);

        app('App\Http\Controllers\SendCommentController')
                ->sendmsg($po->ponbr, 'PO', $fakeReq);

        return response()->json([
            'success' => true,
            'message' => 'Status diubah menjadi REUSE (R).'
        ]);
    }

    /** POST /po/{ponbr}/cancel */
    public function cancelPO(Request $req, $ponbr)
    {
        $po = TrPO::where('ponbr', $ponbr)->firstOrFail();

        $data = $req->validate([
            'reason' => ['required','string']
        ]);

        $po->status     = 'X';
        $po->updated_by = Auth::user()->username ?? 'system';

        // simpan reason ke ponote (append)
        $stamp = Carbon::now()->format('d/m/Y H:i');
        $who   = Auth::user()->username ?? 'user';
        $reasonLine = "CANCEL: ".$data['reason'];       
        $po->save();

        $fakeReq = new \Illuminate\Http\Request([
            'docid'  => $po->ponbr,
            'reason' => $reasonLine,
        ]);

        app('App\Http\Controllers\SendCommentController')
                ->sendmsg($po->ponbr, 'PO', $fakeReq);

        return response()->json([
            'success' => true,
            'message' => 'Status diubah menjadi CANCEL (X).'
        ]);
    }

    public function fetchComments($id)
    {
    
        $comments = T_Message::where('docid', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'comments' => $comments
        ]);
    }

    public function storeComment(Request $request, $id)
    {
        $user = Auth::user();
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);
        // dd($id);
        $user = request()->user();
        $comment = new T_Message();
        $comment->docid = $id;
        $comment->doctype = 'PO';
        $comment->username = $user->username; 
        $comment->name = $user->name; 
        $comment->message = $request->comment;
        $comment->status = 'A';
        $comment->created_at = now();
        $comment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment added successfully!',
            'comment' => $comment
        ]);
    }

    public function uploadAttachments(Request $request, $poid)
    {
        try {
            $user = $request->user();
            $year = (int) ($request->input('year') ?? now()->year);

            $created = [];

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $filename     = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                    // bersihkan nama original dari %
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $ext        = $file->getClientOriginalExtension();
                    $attachfile = md5($randomNumber) . '.' . $ext;

                    // folder tujuan
                    $folder_attach = public_path('attachments/'.$year);
                    if (!is_dir($folder_attach)) {
                        @mkdir($folder_attach, 0777, true);
                    }

                    // pindahkan file (tanpa ekstensi di nama file, sesuai contoh kamu)
                    $file->move($folder_attach, $attachfile);

                    // simpan DB
                    $attach = new Attachment();
                    $attach->docid       = $poid;
                    $attach->name        = $filename; // tampilkan nama tanpa ekstensi
                    $attach->attachfile  = $attachfile;
                    $attach->status      = 'A';
                    $attach->extention   = $file->getClientOriginalExtension();
                    $attach->created_user= $user->username ?? 'system';
                    $attach->save();

                    $created[] = [
                        'id'         => $attach->id,
                        'name'       => $attach->name,
                        'attachfile' => $attach->attachfile,
                        'ext'        => $attach->extention,
                        'year'       => $year,
                    ];
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No files received.'
                ], 422);
            }

            return response()->json([
                'success'     => true,
                'attachments' => $created
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
   
    public function listAttachment($ponbr)
    {
        $rows = Attachment::where('docid', $ponbr)
            ->where('status', 'A')
            ->orderByDesc('id')->get()
            ->map(function($a){
            return [
                'id'         => $a->id,
                'name'       => $a->name . '.' . $a->extention,
                'attachfile' => $a->attachfile,               // sudah termasuk extension
                'year'       => optional($a->created_at)->year ?? now()->year,
                'created_at' => optional($a->created_at)->toDateTimeString(),
                'created_user'=> $a->created_user,
                'url'        => url('/attachments/'.(optional($a->created_at)->year ?? now()->year).'/'.$a->attachfile),
            ];
        });

        return response()->json(['success'=>true, 'attachments'=>$rows]);
    }
    

    public function removeAttachment($id)
    {
        try {
            $attachment = Attachment::findOrFail($id);
            $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['success'=>true]);
    }

    public function printPO_xxx(string $hash)
    {
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Dokumen tidak ditemukan.');
        $id = $decoded[0];

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Header PO
        $po = TrPO::findOrFail($id);

        // Detail pakai ponbr
        $podetail = TrPOdetail::where('ponbr', $po->ponbr)
            ->orderBy('cs_no') // ganti jika nama kolom baris berbeda
            ->get();

        //po header amt
        $dpp  = $po->totalamt;
        $ppn  = $po->taxamt;
        $grand = $po->grandtotalamt;
        $terbilang = ucfirst($this->terbilang($grand)) . ' rupiah';

        $company = CompanyPG::where('cpny_id', $po->cpny_id)
            ->first();

        $purchaser = ucwords(strtolower($authUser->name));

        // Data tambahan utk view
        $data = [
            'po'       => $po,
            'podetail' => $podetail,           
            'dpp'   => $dpp,
            'ppn'   => $ppn,
            'grand' => $grand,          
            'terbilang' => $terbilang,
            'company'  => $company,
            'now'      => Carbon::now(),
            'purchaser'     => $purchaser,
        ];

        // Pilih view
        $view = $po->potype === 'PO'
            ? 'pages.purchase.pdf_po'
            : 'pages.purchase.pdf_spk';

        $pdf = \PDF::loadView($view, $data)
            ->setPaper('A4', 'portrait');

        // Nama file stream yang informatif
        $basename = $po->potype === 'PO' ? 'PO' : 'SPK';
        return $pdf->stream("{$basename}_{$po->ponbr}.pdf");
    }
    


    public function printPO(string $hash)
    {
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Dokumen tidak ditemukan.');
        $id = $decoded[0];

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Header PO
        $po = TrPO::findOrFail($id);

        // Detail pakai ponbr
        $podetail = TrPOdetail::where('ponbr', $po->ponbr)
            ->orderBy('cs_no')
            ->get();

        // Header amount
        $dpp    = $po->totalamt;
        $ppn    = $po->taxamt;
        $grand  = $po->grandtotalamt;
        $terbilang = ucfirst($this->terbilang($grand)) . ' rupiah';

        $company = CompanyPG::where('cpny_id', $po->cpny_id)->first();

        // tampilkan nama pembuat / pengirim
        $purchaser = ucwords(strtolower($authUser->name));

        $data = [
            'po'        => $po,
            'podetail'  => $podetail,
            'dpp'       => $dpp,
            'ppn'       => $ppn,
            'grand'     => $grand,
            'terbilang' => $terbilang,
            'company'   => $company,
            'now'       => Carbon::now(),
            'purchaser' => $purchaser,
        ];

        $view = $po->potype === 'PO' ? 'pages.purchase.pdf_po' : 'pages.purchase.pdf_spk';

        // 1) render view -> Dompdf
        $pdf = Pdf::loadView($view, $data)->setPaper('A4', 'portrait');

        // 2) Ambil Dompdf & RENDER lebih dulu (supaya PAGE_COUNT terisi)
        /** @var \Dompdf\Dompdf $dompdf */
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        // 3) Tulis footer via canvas
        $canvas  = $dompdf->get_canvas();
        $w       = $canvas->get_width();
        $h       = $canvas->get_height();

        // pakai font aman unicode
        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->get_font('sans-serif', 'normal'); // bundled Dompdf
        $size    = 9;

        $now     = $data['now'];
        $leftTxt = "Created by: {$purchaser}, Sent by: {$purchaser}, On: " . $now->format('d/m/Y H:i');
        $rightTpl = "Page {PAGE_NUM} of {PAGE_COUNT}";

        $rightWidth = $metrics->getTextWidth($rightTpl, $font, $size);
        $y = $h - 28; // ~10mm dari bawah

        $x = $canvas->get_width() - $w - 75;

        // kiri & kanan
        $canvas->page_text(20, $y, $leftTxt,  $font, $size, [0,0,0]);
        $canvas->page_text($w - $x - $rightWidth, $y, $rightTpl, $font, $size, [0,0,0]);

        // 4) Stream seperti biasa
        $basename = $po->potype === 'PO' ? 'PO' : 'SPK';
        // return $dompdf->stream("{$basename}_{$po->ponbr}.pdf");
        return $dompdf->stream("{$basename}_{$po->ponbr}.pdf", ['Attachment' => false]);
    }


    private function terbilang($angka): string
    {
        if (is_string($angka)) {
            $angka = str_replace([',', ' '], '', $angka);
        }
        if (!is_numeric($angka)) return '';

        $isMinus = $angka < 0;
        $angka = (int) abs((float) $angka);

        $bil = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        $fn = function ($n) use (&$fn, $bil): string {
            if ($n < 12)                  return ' '.$bil[$n];
            if ($n < 20)                  return $fn($n - 10).' belas';
            if ($n < 100)                 return $fn(intval($n / 10)).' puluh'.$fn($n % 10);
            if ($n < 200)                 return ' seratus'.$fn($n - 100);
            if ($n < 1000)                return $fn(intval($n / 100)).' ratus'.$fn($n % 100);
            if ($n < 2000)                return ' seribu'.$fn($n - 1000);
            if ($n < 1_000_000)           return $fn(intval($n / 1000)).' ribu'.$fn($n % 1000);
            if ($n < 1_000_000_000)       return $fn(intval($n / 1_000_000)).' juta'.$fn($n % 1_000_000);
            if ($n < 1_000_000_000_000)   return $fn(intval($n / 1_000_000_000)).' miliar'.$fn($n % 1_000_000_000);
            return $fn(intval($n / 1_000_000_000_000)).' triliun'.$fn($n % 1_000_000_000_000);
        };

        $hasil = trim(preg_replace('/\s+/', ' ', $fn($angka)));
        return ($isMinus ? 'minus ' : '').$hasil;
    }

  

    private function extractBodyHtml(string $fullHtml): string
    {
        try {
            $dom = new \DOMDocument();
            // suppress warning HTML tidak sempurna
            @$dom->loadHTML($fullHtml, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
            $body = $dom->getElementsByTagName('body')->item(0);
            if (!$body) return $fullHtml;

            $innerHTML = '';
            foreach ($body->childNodes as $child) {
                $innerHTML .= $dom->saveHTML($child);
            }
            return $innerHTML;
        } catch (\Throwable $e) {
            return $fullHtml;
        }
    }

    public function viewEmailPO(string $hash)
    {
        $ponbr = Hashids::decode($hash)[0] ?? null;
        abort_if(!$ponbr, 404);

        $po = TrPO::where('ponbr', $ponbr)->firstOrFail();

       
        // $emailfrom = User::where('username', $po->created_by)->value('test_email');
        $user = User::where('username', $po->created_by)
            ->first(['name', 'test_email']);

        $fromEmail = $user->test_email;
        $purchaser = ucwords(strtolower($user->name));

        // $emailto   = MsVendor::where('vendor_id', $po->vendorid)->value('email');
        $emailto ='bedriamaail@pakuwon.com ; rikiparahat@pakuwon.com';

        $subject_email = $po->potype == 'PO'
            ? 'Purchase Order Nomor '.$ponbr.' untuk '.trim($po->vendorname).' - '.$po->keperluan
            : 'Surat Perintah Kerja Nomor '.$ponbr.' untuk '.trim($po->vendorname).' - '.$po->keperluan;

        $html = file_get_contents(public_path('template/email_templates.html'));

        // URL absolut ke gambar di public/template/po_footer.jpg
        $footerUrl = asset('template/po_footer.jpg');

        $map = [
            '${POTYPE}'      => $po->potype,
            '${PONBR}'       => $po->ponbr,
            '${VENDORNAME}'  => $po->vendorname,
            '${CSKEPERLUAN}' => $po->keperluan,
            '${CONTACTNAME}' => $po->vendorcp,
            '${PURCHASER}'   => $purchaser,
            '${FOOTER_URL}'  => $footerUrl, // <- ini penting
        ];

        $initial_html = strtr($html, $map);

        return view('emails.sendemailpo', [
            'ponbr'         => $ponbr,
            'po'            => $po,
            'vendor'        => $po->vendorname,
            'template'      => strtoupper($po->potype ?? 'PO'),
            'subject_email' => $subject_email,
            'from_email'    => $fromEmail,
            'purchaser'    => $purchaser,
            'to_email'      => $emailto,
            'initial_html'  => $initial_html,
        ]);
    }

    public function sendNowPO_xxx(Request $req, string $ponbr)
    {
        $authUser = Auth::user();

        // 1) Validasi payload dari form compose (To/Cc/Bcc bisa array atau string dipisah koma)
        $data = $req->validate([
            'from'    => ['required','email'],
            'to'      => ['required'],              // array email atau string “a@b.com, c@d.com”
            'cc'      => ['nullable'],
            'bcc'     => ['nullable'],
            'subject' => ['required','string','max:200'],
            'html'    => ['required','string'],     // body HTML dari Summernote
        ]);

        
        // Tentukan display name pengirim
        $senderName = User::where('test_email', $data['from'])->value('name'); // ganti 'name' bila kolommu 'fullname'
        if (!$senderName) {
            // fallback: nama pembuat PO
            $senderName = User::where('username', $po->created_by)->value('name'); // sesuaikan kolom
        }
        if (!$senderName && Auth::check()) {
            // fallback: nama user yang login
            $senderName = Auth::user()->name ?? Auth::user()->fullname ?? null;
        }
        $senderName = $senderName ?: 'Pakuwon System';


        // Normalisasi daftar email
        // $norm = function ($v) {
        //     if (!$v) return [];
        //     if (is_array($v)) return array_values(array_unique(array_filter(array_map('trim',$v))));
        //     return array_values(array_unique(array_filter(array_map('trim', explode(',', $v)))));
        // };
        $norm = function ($v) {
            if (!$v) return [];
            if (is_array($v)) return array_values(array_unique(array_filter(array_map('trim',$v))));
            return array_values(array_unique(array_filter(array_map('trim', preg_split('/[,;]+/', $v)))));
        };


        $to  = $norm($data['to']);
        $cc  = $norm($data['cc'] ?? []);
        $bcc = $norm($data['bcc'] ?? []);

        if (empty($to)) {
            return response()->json(['success'=>false,'message'=>'Field "To" wajib diisi.'], 422);
        }

        // 2) Ambil header + detail PO
        $po = TrPO::where('ponbr', $ponbr)->firstOrFail();
        $podetail = TrPOdetail::where('ponbr', $po->ponbr)->orderBy('cs_no')->get();
        // Header amount
        $dpp    = $po->totalamt;
        $ppn    = $po->taxamt;
        $grand  = $po->grandtotalamt;
        $terbilang = ucfirst($this->terbilang($grand)) . ' rupiah';

        $company = CompanyPG::where('cpny_id', $po->cpny_id)->first();

        // tampilkan nama pembuat / pengirim
        $purchaser = ucwords(strtolower($authUser->name));

        $viewData = [
            'po'        => $po,
            'podetail'  => $podetail,
            'dpp'       => $dpp,
            'ppn'       => $ppn,
            'grand'     => $grand,
            'terbilang' => $terbilang,
            'company'   => $company,
            'now'       => Carbon::now(),
            'purchaser' => $purchaser,
        ];

        // 3) Siapkan lampiran dari tabel Attachment (public/attachments/{YEAR}/{$attach->attachfile})
        $attachments = Attachment::where('docid', $po->ponbr)
            ->where('status', 'A')
            ->get();

        $filePaths = [];
        foreach ($attachments as $row) {
            // ambil tahun dari created_at jika ada, fallback ke tahun sekarang
            $year = $row->created_at ? Carbon::parse($row->created_at)->year : Carbon::now()->year;
            $path = public_path("attachments/{$year}/{$row->attachfile}");
            if (is_file($path)) {
                $filePaths[] = $path;
            }
        }

        // 4) Generate PDF PO/SPK (tanpa stream), lalu attach
        $view = $po->potype === 'PO' ? 'pages.purchase.pdf_po' : 'pages.purchase.pdf_spk';
        $pdf  = \PDF::loadView($view, $viewData)->setPaper('A4', 'portrait');
        $pdfBinary = $pdf->output(); // <- ambil binary untuk attachData
        $pdfName = ($po->potype === 'PO' ? 'PO' : 'SPK') . "_{$po->ponbr}.pdf";

        // 5) Kirim email (pakai body HTML langsung dari Summernote)
        //    Laravel 9+: bisa pakai Mail::html(). Jika kamu di versi lebih lama, gunakan Mail::send dengan view sederhana.
        Mail::html($data['html'], function ($message) use ($data, $to, $cc, $bcc, $pdfBinary, $pdfName, $filePaths, $po, $senderName) {
            $message->from($data['from'], $senderName);
            $message->to($to);
            if (!empty($cc))  $message->cc($cc);
            if (!empty($bcc)) $message->bcc($bcc);
            $message->subject($data['subject']);

            // attach PDF hasil render
            $message->attachData($pdfBinary, $pdfName, ['mime' => 'application/pdf']);

            // attach file-file existing
            foreach ($filePaths as $path) {
                $message->attach($path);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Email sudah dikirim beserta lampiran.'
        ]);
    }

   
    public function sendNowPO(Request $req, string $ponbr)
    {
        $authUser = Auth::user();

        // 1) Validasi payload
        $data = $req->validate([
            'from'    => ['required','email'],
            'to'      => ['required'],
            'cc'      => ['nullable'],
            'bcc'     => ['nullable'],
            'subject' => ['required','string','max:200'],
            'html'    => ['required','string'],
        ]);

        // 2) Ambil PO + detail + data untuk view
        $po = TrPO::where('ponbr', $ponbr)->firstOrFail();
        $podetail = TrPOdetail::where('ponbr', $po->ponbr)->orderBy('cs_no')->get();

        $dpp   = $po->totalamt;
        $ppn   = $po->taxamt;
        $grand = $po->grandtotalamt;
        $terbilang = ucfirst($this->terbilang($grand)) . ' rupiah';
        $company = CompanyPG::where('cpny_id', $po->cpny_id)->first();

        // tampilkan nama pembuat/pengirim
        $purchaser = ucwords(strtolower($authUser->name));

        $viewData = [
            'po'        => $po,
            'podetail'  => $podetail,
            'dpp'       => $dpp,
            'ppn'       => $ppn,
            'grand'     => $grand,
            'terbilang' => $terbilang,
            'company'   => $company,
            'now'       => Carbon::now(),
            'purchaser' => $purchaser,
        ];

        // 3) Tentukan display name pengirim (sesudah $po ada)
        $senderName = User::where('test_email', $data['from'])->value('name');
        if (!$senderName) {
            $senderName = User::where('username', $po->created_by)->value('name');
        }
        if (!$senderName && Auth::check()) {
            $senderName = Auth::user()->name ?? Auth::user()->fullname ?? null;
        }
        $senderName = $senderName ?: 'Pakuwon System';

        // 4) Normalisasi daftar email
        $norm = function ($v) {
            if (!$v) return [];
            if (is_array($v)) return array_values(array_unique(array_filter(array_map('trim',$v))));
            return array_values(array_unique(array_filter(array_map('trim', preg_split('/[,;]+/', $v)))));
        };
        $to  = $norm($data['to']);
        $cc  = $norm($data['cc'] ?? []);
        $bcc = $norm($data['bcc'] ?? []);
        if (empty($to)) {
            return response()->json(['success'=>false,'message'=>'Field "To" wajib diisi.'], 422);
        }

        // 5) Kumpulkan attachment dari tabel Attachment
        $attachments = Attachment::where('docid', $po->ponbr)->where('status','A')->get();
        $filePaths = [];
        foreach ($attachments as $row) {
            $year = $row->created_at ? Carbon::parse($row->created_at)->year : Carbon::now()->year;
            $path = public_path("attachments/{$year}/{$row->attachfile}");
            if (is_file($path)) $filePaths[] = $path;
        }

        // 6) Generate PDF + tambahkan footer "Created by..." dan "Page X of Y"
        $view = $po->potype === 'PO' ? 'pages.purchase.pdf_po' : 'pages.purchase.pdf_spk';
        $pdf  = Pdf::loadView($view, $viewData)->setPaper('A4', 'portrait');

        /** @var \Dompdf\Dompdf $dompdf */
        $dompdf = $pdf->getDomPDF();
        $dompdf->render(); // wajib supaya {PAGE_COUNT} tersedia

        $canvas  = $dompdf->get_canvas();
        $w       = $canvas->get_width();
        $h       = $canvas->get_height();

        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->get_font('sans-serif', 'normal');
        $size    = 9;

        $now       = $viewData['now'];
        $leftTxt   = "Created by: {$purchaser}, Sent by: {$purchaser}, On: " . $now->format('d/m/Y H:i');
        $rightTpl  = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $rightW    = $metrics->getTextWidth($rightTpl, $font, $size);

        $y = $h - 28;                 // ~10mm dari bawah
        // $pad = 20;                    // margin horizontal
        $pad = $canvas->get_width() - $w - 75;
        $canvas->page_text(20, $y, $leftTxt,  $font, $size, [0,0,0]);                 // kiri
        $canvas->page_text($w - $pad - $rightW, $y, $rightTpl, $font, $size, [0,0,0]);  // kanan

        $pdfBinary = $dompdf->output(); // ambil binary SETELAH footer ditulis
        $pdfName   = ($po->potype === 'PO' ? 'PO' : 'SPK') . "_{$po->ponbr}.pdf";

        // 7) Kirim email
        Mail::html($data['html'], function ($message) use ($data, $to, $cc, $bcc, $pdfBinary, $pdfName, $filePaths, $senderName) {
            $message->from($data['from'], $senderName);
            $message->to($to);
            if (!empty($cc))  $message->cc($cc);
            if (!empty($bcc)) $message->bcc($bcc);
            $message->subject($data['subject']);

            // attach PDF hasil render + footer
            $message->attachData($pdfBinary, $pdfName, ['mime' => 'application/pdf']);

            // attach file-file existing
            foreach ($filePaths as $path) {
                $message->attach($path);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Email sudah dikirim beserta lampiran.'
        ]);
    }



    


}
