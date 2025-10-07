<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\T_approval;
use App\Models\Attachment;
use App\Models\T_Message;
use Vinkla\Hashids\Facades\Hashids;

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

        // Approval & Attachment pakai docid = ponbr
        $approval = T_approval::where('docid', $po->ponbr)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->orderBy('aprvid')
            ->get();

        $attachment = Attachment::where('docid', $po->ponbr)
            ->where('status', 'A')
            ->get();

        // Kembalikan ke view
        // - kirim variabel baru: $po, $podetail
        // - plus alias lama ($sppb, $sppbdetail) untuk kompatibilitas view lama
        return view('pages.purchase.showpo', [
            'po'         => $po,
            'podetail'   => $podetail,
            'approval'   => $approval,
            'attachment' => $attachment,
            'hash'       => $hash,           
            'sppb'       => $po,
            'sppbdetail' => $podetail,
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
        if (strtoupper($po->potype ?? '') === 'PB') {
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

            if (strtoupper($po->potype ?? '') === 'PB') {
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
                // $pm = strtoupper($req->input('payment_method'));
                // $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '') .
                //     "Cara Pembayaran: {$pm}");
            }

            // ubah status ke Purchase Order
            $po->status = 'P';
            $po->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Submit berhasil. Status berubah menjadi Purchase Order (P).'
        ]);
    }

    /** POST /po/{ponbr}/cancel-reuse */
    public function cancelReuse(Request $req, $ponbr)
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
    public function cancel(Request $req, $ponbr)
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
            ->orderBy('cs_no') // ganti jika nama kolom baris berbeda
            ->get();

        // --- Totals (anggap totalcost = DPP/Net line, taxamt = PPN line) ---
        $dpp  = (float) $podetail->sum('totalcost');
        $ppn  = (float) $podetail->sum('taxamt');
        $grand = $dpp + $ppn;

        // Data tambahan utk view
        $data = [
            'po'       => $po,
            'podetail' => $podetail,
            'totals'   => [
                'dpp'   => $dpp,
                'ppn'   => $ppn,
                'grand' => $grand,
            ],
            'now'      => Carbon::now(),
            'user'     => $authUser,
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


}
