<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Autonbr;
use App\Models\User;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\T_approval;
use App\Models\Attachment;
use App\Models\T_Message;
use App\Models\MsVendor;
use App\Models\MsCompany;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\TrCS;
use Vinkla\Hashids\Facades\Hashids;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Mail;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Str;
use App\Models\MsTopdetail;
use App\Models\TrPOterm;
use App\Models\TrRfca;
use App\Models\TrPOReuse;
use App\Models\Bq;
use App\Models\TrPoLastPrice;
use App\Models\MsInventory;


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

        // Detail PO
        $podetail = TrPOdetail::where('ponbr', $po->ponbr)
            ->orderBy('cs_no')
            ->get();

        // -------- Ambil lampiran dari tr_attachment & buat Signed URL --------
        $rows = TrAttachment::where('refnbr', $po->ponbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        // map untuk view
        $attachment = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename; // ex: att-purchasing-app/po/2025/xxx.pdf
            $object     = $bucket->object($objectPath);

            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'id'           => $r->id,
                'display_name' => $r->attachment_name,
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,      // bisa null jika gagal
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

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

        $sppbUrl = null;
        if (!empty($po->sppbjktid) && isset($routeMap[$prefix])) {
            $sppbHash = Hashids::encode($id);
            $sppbUrl  = url("/{$routeMap[$prefix]}/{$sppbHash}");
        }

        $id = TrCS::where('csid', $po->csid)->value('id');

        $csUrl = null;
        if (!empty($po->csid)) {
            $csHash = Hashids::encode($id);
            $csUrl  = url("/showcs/{$csHash}");
        }

        return view('pages.purchase.showpo', [
            'po'          => $po,
            'podetail'    => $podetail,
            'attachment'  => $attachment,   // <- sudah dalam format siap pakai
            'hash'        => $hash, 
            'eid_ponbr'   => $eid_ponbr,
            'sppbUrl'     => $sppbUrl,   
            'csUrl'       => $csUrl,     
        ]);
    }

    public function submitPO(Request $req, $ponbr)
    {
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
            $req->validate([
                'podeliverydate' => ['nullable','date'],
            ]);

            if (empty($deliveryDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The podeliverydate field is required.'
                ], 422);
            }
        } else {
            $req->validate([
                'work_date_from' => ['required','date'],
                'work_date_to'   => ['required','date','after_or_equal:work_date_from'],
                'work_days'      => ['required','integer','min:0'],
                'work_day_from'  => ['required','string'],
                'work_day_to'    => ['required','string'],
                'work_time_from' => ['required','date_format:H:i'],
                'work_time_to'   => ['required','date_format:H:i'],
                'manpower_total' => ['required','integer','min:0'],
                'pic_name'       => ['required','string'],
                'pic_phone'      => ['required','string'],
                // 'payment_method' => ['required','string'],
                'warranty'       => ['required','string'],
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
                if ($deliveryDate) {
                    $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '') .
                        'Delivery Date: '.Carbon::parse($deliveryDate)->format('d/m/Y'));
                }
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

                // simpan "cara pembayaran" ke ponote (kolom yang ada) — gunakan variabel berbeda!
                $pm = strtoupper($req->input('payment_method', '')); // <- TIDAK menimpa $po
                if (!empty($pm)) {
                    $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '')."Cara Pembayaran: {$pm}");
                }
            }
        
            // 1. Cek Detail PO ada sebelum Used Budget
            $detailCount = TrPODetail::where('ponbr', $po->ponbr)->count();
            if ($detailCount <= 0) {
                throw new \Exception("PO Detail kosong. Tidak bisa proses budget untuk PO {$po->ponbr}");
            }
            
            // 2. Used budget via SP (Submit)
            // DB::connection('pgsql')->statement(
            //     'CALL public.sp_process_budget(?, ?, ?, ?)',
            //     ['PO', $po->ponbr, 'Submit', Auth::user()->username]
            // );

            // ✅ INSERT/UPDATE last price
            if ($po->potype == 'PO'){    
                $this->insertPoLastPrice($po);
            }

            // 3. Sync term dari TOP
            $this->syncPoTermsFromTop($po);

            // 4. Generate RFCA dari term DP
            $this->generateRfcaFromPo($po);

            // 5. Update status ke Purchase Order
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

        $po->status     = 'D';
        $po->updated_by = Auth::user()->username ?? 'system';

        // simpan reason ke ponote (append)
        $stamp = Carbon::now()->format('d/m/Y H:i');
        $who   = Auth::user()->username ?? 'user';
        $reasonLine = "CANCEL REUSE: ".$data['reason'];       
        $po->save();

        // Insert detail ke tabel Reuse
        $this->insertPOReuse($po);

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

    

    public function uploadAttachments_xxx(Request $request, $poid)
    {
        try {
            // $user = $request->user();
            $user = Auth::user();
            $username = $user ? $user->username : 'system';
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

    public function uploadAttachments(Request $request, $ponbr)
    {
        try {
            $po = TrPO::where('ponbr', $ponbr)->firstOrFail();
            $user       = $request->user();
            $year       = (int) ($request->input('year') ?? now()->year);
            $refnbr     = (string) $ponbr;     // PO => pakai ponbr sebagai refnbr
            $doctype    = 'PO';
            $cpnyid     = $po->cpny_id;
            $deptId     = $po->department_id;
            $createdBy  = $user->username ?? $user->id ?? 'system';

            if (!$request->hasFile('attachments')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files received.',
                ], 422);
            }

            // === Delegasikan upload ke TrAttachmentController ===
            $meta = [
                'refnbr'        => $refnbr,
                'doctype'       => $doctype,
                'cpnyid'        => $cpnyid,
                'departementid' => $deptId,
                'base_folder'   => 'att-purchasing-app/'.strtolower($doctype), // <= PO
                'created_by'    => $createdBy,
            ];
            $files = (array) $request->file('attachments');

            try {
                /** @var \App\Http\Controllers\TrAttachmentController $uploader */
                $uploader     = app(TrAttachmentController::class);
                $uploadResult = $uploader->uploadInternal($meta, $files); // array paths (opsional)
            } catch (\Throwable $e) {
                \Log::error('PO uploadInternal gagal', ['error' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal upload attachment: '.$e->getMessage(),
                ], 500);
            }

            // === Ambil ulang daftar attachment untuk dikembalikan ke FE (dengan Signed URL) ===
            $rows = TrAttachment::where('refnbr', $refnbr)
                ->where('doctype', $doctype)
                ->where('status', 'A')
                ->orderBy('created_at', 'desc')
                ->get();

            // Siapkan Signed URL via GCS (biar FE bisa langsung klik)
            $config = config('filesystems.disks.gcs');
            $keyFilePath = $config['key_file'];
            if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
                $keyFilePath = base_path($keyFilePath);
            }
            $storage = new StorageClient([
                'projectId'   => $config['project_id'],
                'keyFilePath' => $keyFilePath,
            ]);
            $bucket = $storage->bucket($config['bucket']);

            $attachments = $rows->map(function ($r) use ($bucket) {
                $objectPath = rtrim($r->folder, '/').'/'.$r->filename;   // ex: att-purchasing-app/po/2025/xxxx-file.pdf
                $object     = $bucket->object($objectPath);

                $signedUrl = null;
                try {
                    $signedUrl = $object->signedUrl(
                        new \DateTimeImmutable('+10 minutes'),
                        ['version' => 'v4']
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
                }

                return [
                    'display_name' => $r->attachment_name,
                    'created_by'   => $r->created_by,
                    'created_at'   => optional($r->created_at)->toDateTimeString(),
                    'extention'    => $r->extention,
                    'size'         => $r->filesize,
                    'url'          => $signedUrl,   // bisa null jika gagal
                    'folder'       => $r->folder,
                    'filename'     => $r->filename,
                ];
            });

            return response()->json([
                'success'     => true,
                'message'     => 'Files uploaded.',
                // kembalikan daftar terbaru agar view (yang “pakai yg atas”) bisa langsung render
                'attachments' => $attachments,
            ]);
        } catch (\Throwable $e) {
            \Log::error('PO uploadAttachments error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
   
    public function listAttachment_xxx($ponbr)
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

    Public function listAttachment($ponbr)
    {
        $doctype = 'PO';

        $rows = TrAttachment::where('refnbr', $ponbr)
            ->where('doctype', $doctype)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // GCS signed URL
        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $object     = $bucket->object($objectPath);

            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return [
                'id'            => $r->id,
                // alias supaya kompatibel dengan JS lama & baru
                'name'          => $r->attachment_name,   // << digunakan oleh JS kamu
                'display_name'  => $r->attachment_name,
                'created_user'  => $r->created_by,        // << digunakan oleh JS kamu
                'created_by'    => $r->created_by,
                'created_at'    => optional($r->created_at)->toDateTimeString(),
                'extention'     => $r->extention,
                'size'          => $r->filesize,
                'url'           => $signedUrl,            // bisa null jika gagal
                'folder'        => $r->folder,
                'filename'      => $r->filename,
            ];
        });

        return response()->json([
            'success'     => true,
            'attachments' => $attachments,
        ]);
    }
    

    public function removeAttachment($id)
    {
        try {
            $attachment = TrAttachment::findOrFail($id);
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

        $company = MsCompany::where('cpny_id', $po->cpny_id)
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

        $company = MsCompany::where('cpny_id', $po->cpny_id)->first();

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

        $eid = Hashids::encode($po->id);
        // $emailfrom = User::where('username', $po->created_by)->value('notification_email');
        $user = User::where('username', $po->created_by)
            ->first(['name', 'notification_email']);

        $fromEmail = $user->notification_email;
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
            'eid'           => $eid,
        ]);
    }

      
    public function sendNowPO_xxx(Request $req, string $ponbr)
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
        $company = MsCompany::where('cpny_id', $po->cpny_id)->first();

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
        $senderName = User::where('notification_email', $data['from'])->value('name');
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
        $po       = TrPO::where('ponbr', $ponbr)->firstOrFail();
        $podetail = TrPOdetail::where('ponbr', $po->ponbr)->orderBy('cs_no')->get();

        $dpp       = $po->totalamt;
        $ppn       = $po->taxamt;
        $grand     = $po->grandtotalamt;
        $terbilang = ucfirst($this->terbilang($grand)) . ' rupiah';
        $company   = MsCompany::where('cpny_id', $po->cpny_id)->first();

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

        // 3) Tentukan display name pengirim
        $senderName = User::where('notification_email', $data['from'])->value('name')
            ?: User::where('username', $po->created_by)->value('name')
            ?: (Auth::check() ? (Auth::user()->name ?? Auth::user()->fullname) : null)
            ?: 'Pakuwon System';

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

        // 5) Ambil lampiran dari TrAttachment (GCS)
        //    refnbr = nomor dokumen (pakai ponbr untuk PO), doctype opsional kalau dipakai
        $rows = TrAttachment::where('refnbr', $po->ponbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // init GCS client sesuai config kamu
        $config      = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        // download lampiran untuk di-attach
        $gcsAttachments = []; // [ ['data' => binary, 'name' => 'file.ext', 'mime' => 'application/octet-stream'], ... ]
        foreach ($rows as $r) {
            $objectPath = rtrim((string)$r->folder, '/').'/'.(string)$r->filename;
            try {
                $object = $bucket->object($objectPath);
                if ($object->exists()) {
                    // ambil biner
                    $binary = $object->downloadAsString();

                    // tentukan nama file & mime
                    $name = $r->attachment_name ?: basename($objectPath);
                    $mime = $r->mimetype ?? $object->info()['contentType'] ?? 'application/octet-stream';

                    $gcsAttachments[] = [
                        'data' => $binary,
                        'name' => $name,
                        'mime' => $mime,
                    ];
                } else {
                    \Log::warning('GCS object not found', ['path' => $objectPath]);
                }
            } catch (\Throwable $e) {
                \Log::warning('GCS download failed', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }
        }

        // 6) Generate PDF + footer
        $view = $po->potype === 'PO' ? 'pages.purchase.pdf_po' : 'pages.purchase.pdf_spk';
        $pdf  = PDF::loadView($view, $viewData)->setPaper('A4', 'portrait');

        /** @var \Dompdf\Dompdf $dompdf */
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

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

        $y = $h - 28;
        $x = $canvas->get_width() - $w - 75;

        $canvas->page_text(20, $y, $leftTxt, $font, $size, [0,0,0]);
        $canvas->page_text($w - $x - $rightW - 20, $y, $rightTpl, $font, $size, [0,0,0]);

        $pdfBinary = $dompdf->output();
        $pdfName   = ($po->potype === 'PO' ? 'PO' : 'SPK') . "_{$po->ponbr}.pdf";

        // 7) Kirim email
        Mail::html($data['html'], function ($message) use ($data, $to, $cc, $bcc, $pdfBinary, $pdfName, $gcsAttachments, $senderName) {
            $message->from($data['from'], $senderName);
            $message->to($to);
            if (!empty($cc))  $message->cc($cc);
            if (!empty($bcc)) $message->bcc($bcc);
            $message->subject($data['subject']);

            // attach PDF hasil render + footer
            $message->attachData($pdfBinary, $pdfName, ['mime' => 'application/pdf']);

            // attach file-file dari GCS
            foreach ($gcsAttachments as $att) {
                $message->attachData($att['data'], $att['name'], ['mime' => $att['mime'] ?? 'application/octet-stream']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Email sudah dikirim beserta lampiran PDF & file dari GCS.'
        ]);
    }

    private function syncPoTermsFromTop(TrPO $po): void
    {
        $topId = $po->vendortop ?? null;

        $topDetails = MsTopdetail::where('topid', $topId)
            ->where('status', 'A')
            ->orderBy('order_term')
            ->get();

        if ($topDetails->isEmpty()) {
            return;
        }

        $bq = Bq::where('sppjtid', $po->sppbjktid)                 
            ->first();

        $username = Auth::user()->username ?? 'system';

        foreach ($topDetails as $detail) {

            // hitung bastamount = payment_pct% * total PO
            $poTotal    = $po->grandtotalamt ?? 0;
            $pct        = floatval($detail->payment_pct ?? 0);
            $bastAmount = $poTotal * ($pct / 100);

            TrPOterm::create([
                // ===== Header PO =====
                'ponbr'         => $po->ponbr,
                'cpny_id'       => $po->cpny_id,
                'csid'          => $po->csid ?? null,
                'sppbjktid'     => $po->sppbjktid ?? null,
                'bqid'          => $bq->bqid ?? null,
                'department_id' => $po->department_id ?? null,
                'user_peminta'  => $po->user_peminta ?? null,
                'keperluan'     => $po->keperluan ?? null,
                'vendorid'      => $po->vendorid ?? null,
                'vendorname'    => $po->vendorname ?? null,

                // ===== Term dari MsTopdetail =====
                'order_term'    => $detail->order_term,
                'terms_id'      => $detail->terms_id,
                'topid'         => $detail->topid,
                'top_type'      => $detail->top_type,
                'terms_name'    => $detail->terms_name,
                'payment_pct'   => $pct,
                'progress_pct'  => $detail->progress_pct ?? 0,
                'terms_type'    => $detail->terms_type,
                'flag_bast'     => $detail->flag_bast,

                // ===== Nominal =====
                'poamount'      => $poTotal,
                'bastamount'    => $bastAmount,   
                'penalty'       => 0,
                'dayslate'      => 0,
                'realizeamount' => 0,

                // ===== Status & audit =====
                'rfcaid'        => null,
                'calrid'        => null,
                'bastid'        => null,
                'status'        => 'A',
                'created_by'    => $username,
                'updated_by'    => $username,
            ]);
        }
    }

    private function generateRfcaFromPo(TrPO $po): void
    {
        // Ambil semua term DP pada PO ini
        $dpTerms = TrPOterm::where('ponbr', $po->ponbr)
            ->where('terms_type', 'DP')
            ->orderBy('order_term')
            ->get();

        if ($dpTerms->isEmpty()) {
            return;
        }

        $now      = Carbon::now();
        $year     = (int) $now->format('Y');
        $month    = $now->format('m');
        $doctype  = 'RC'; // kode dokumen RFCA (mengikuti konvensi yang sudah ada)
        $user     = Auth::user();
        $username = $user->username ?? 'system';

        foreach ($dpTerms as $term) {

            // === Generate nomor RFCA (rfcaid) pakai tabel autonbr, lockForUpdate ===
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => $year,
                    'month'   => $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $urutan = 1;
            } else {
                $urutan = (int) $autonbr->number + 1;
                $autonbr->update(['number' => $urutan]);
            }

            $tglbln = substr((string) $year, 2) . $month;      // YYMM
            $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);
            $rfcaid = $docid;

            // Nominal: pakai data dari term PO
            $poAmount    = $term->poamount ?? ($po->grandtotalamt ?? 0);
            $rfcaAmount  = $term->bastamount ?? 0;  // utk DP biasanya = payment_pct * total PO

            TrRfca::create([
                'rfcaid'          => $rfcaid,
                'rfcadate'        => $now,                 // tanggal RFCA = sekarang
                'ponbr'           => $po->ponbr,
                'cpny_id'         => $po->cpny_id,
                'csid'            => $po->csid,
                'sppbjktid'       => $po->sppbjktid,
                'department_id'   => $po->department_id,
                'user_peminta'    => $po->user_peminta,
                'keperluan'       => $po->keperluan,

                'order_term'      => $term->order_term,
                'terms_id'        => $term->terms_id,
                'topid'           => $term->topid,
                'payment_pct'     => $term->payment_pct,

                'vendorid'        => $po->vendorid,
                'vendorname'      => $po->vendorname,

                'po_amount'       => $poAmount,
                'rfca_amount'     => $rfcaAmount,

                // Untuk RFCA pertama, previous info dikosongkan dulu
                'prev_rfcaid'       => null,
                'prev_ponbr'        => null,
                'prev_csid'         => null,
                'prev_rfca_amount'  => 0,
                'add_rfca_amount'   => 0,

                'required_date'   => $now->copy()->addDays(9),   
                'calr_date'       => null,

                'status'          => 'A',    
                'rfca_type'       => '', 
                'rfca_step_order' => null,
                'rfca_step_id'    => null,
                'status_rfca'     => null,   

                'created_by'      => $username,
                'updated_by'      => $username,
            ]);
        }
    }

    private function insertPOReuse($po)
    {
        $user     = Auth::user();
        $username = $user->username ?? 'system';
        // Ambil semua detail dari TrPOdetail
        $details = \App\Models\TrPOdetail::where('ponbr', $po->ponbr)->get();

        if ($details->isEmpty()) {
            return;
        }

        foreach ($details as $d) {

            \App\Models\TrPOReuse::create([
                'cpny_id'                 => $po->cpny_id ?? null,
                'ponbr'                   => $d->ponbr,
                'po_no'                   => $d->po_no,
                'csid'                    => $d->csid,
                'cs_no'                   => $d->cs_no,
                'sppbjktid'               => $d->sppbjktid,
                'sppbjkt_no'              => $d->sppbjktid_no,
                'inventory_type'          => $d->inventory_type,
                'inventory_sub_type'      => $d->inventory_sub_type,
                'inventory_category'      => $d->inventory_category,
                'inventoryid'             => $d->inventoryid,
                'inventory_descr'         => $d->inventory_descr,
                'qty'                     => $d->qty,
                'uom'                     => $d->uom,
                'siteid'                  => $d->siteid,
                'type_multiplier'         => $d->type_multiplier,
                'base_multiplier'         => $d->base_multiplier,
                'base_qty'                => $d->base_qty,
                'base_uom'                => $d->base_uom,
                'budget_perpost'          => $d->budget_perpost,
                'budget_cpny_id'          => $d->budget_cpny_id,
                'budget_business_unit_id' => $d->budget_business_unit_id,
                'budget_department_fin_id'=> $d->budget_department_fin_id,
                'budget_account_id'       => $d->budget_account_id,
                'budget_activity_id'      => $d->budget_activity_id,
                'budget_activity_descr'   => $d->budget_activity_descr,

                // default openordered/ordered dkk = 0
                'openordered'             => $d->qty,
                'ordered'                 => 0,
                'rejectordered'           => 0,
                'completeordered'         => 0,

                'status'                  => 'D', // reuse
                'created_by'              => $username,
                'created_at'              => now(),
            ]);
        }
    }

    private function insertPoLastPrice(TrPO $po): void
    {
        $username = Auth::user()->username ?? 'system';
        $now = Carbon::now();

        $details = TrPODetail::where('ponbr', $po->ponbr)->get();

        foreach ($details as $d) {
            // optional: ambil info inventory untuk type/subtype/category
            $inv = null;
            if (!empty($d->inventoryid)) {
                $inv = MsInventory::query()
                    ->where('inventoryid', $d->inventoryid)
                    ->first();
            }

            // Kalau kamu mau “last price” per vendor+inventory, biasanya lebih aman pakai updateOrCreate
            // key bisa kamu sesuaikan (ini contoh)
            TrPoLastPrice::updateOrCreate(
                [
                    'cpny_id'     => $po->cpny_id,
                    'vendorid'    => $po->vendorid,
                    'inventoryid' => $d->inventoryid,
                ],
                [
                    'ponbr'              => $po->ponbr,
                    'podate'             => $po->podate ?? $po->submitdate ?? $now, // sesuaikan field tanggal PO kamu
                    'csid'               => $po->csid ?? null,
                    'sppbjktid'          => $po->sppbjktid ?? null,

                    'vendorname'         => $po->vendorname ?? null,

                    'inventory_type'     => $inv->inventory_type ?? ($d->inventory_type ?? null),
                    'inventory_sub_type' => $inv->item_sub_type ?? ($d->inventory_sub_type ?? null),
                    'inventory_category' => $inv->item_category ?? ($d->inventory_category ?? null),

                    'inventory_descr'    => $d->inventory_descr ?? ($inv->inventory_descr ?? null),

                    'qty'                => $d->qty ?? 0,
                    'uom'                => $d->uom ?? null,
                    'siteid'             => $d->siteid ?? null,

                    'unitcost'           => $d->unitcost ?? 0,
                    'taxcodeid'          => $d->taxcodeid ?? null,
                    'taxamt'             => $d->taxamt ?? 0,
                    'totalcost'          => $d->totalcost ?? (($d->qty ?? 0) * ($d->unitcost ?? 0) + ($d->taxamt ?? 0)),

                    'status'             => 'A',
                    'purchaser'          => $username,
                    'updated_by'         => $username,
                    'updated_at'         => $now,

                    // kalau record baru, fill created
                    'created_by'         => $username,
                    'created_at'         => $now,
                ]
            );
        }
    }

}
