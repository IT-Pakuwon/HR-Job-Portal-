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
use App\Models\CompanyPG;
use App\Models\TrReceipt;
use App\Models\TrReceiptdetail;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\TrCS;
use Vinkla\Hashids\Facades\Hashids;
use Mail;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Models\Company;

class ReceiptController extends Controller
{
    public function createReceipt(Request $req) 
    {
        $ponbr_eid = (string) $req->query('ponbr', '');
        abort_if($ponbr_eid === '', 404, 'PO number required');

        $id = Hashids::decode($ponbr_eid)[0] ?? null;
        abort_if(!$id, 404);

        // --- Ambil header PO ---
        $po = TrPO::select([
                'id','ponbr','podate','sppbjktid','vendorname',
                'cpny_id','department_id','user_peminta'
            ])->where('id', $id)->first();

        abort_if(!$po, 404, 'PO not found');

        // --- Ambil detail PO + hitung sisa yang masih bisa diterima ---
        // qty_sisa = max(qty - qty_received + qty_return, 0)
        $details = TrPOdetail::select([
                'id','ponbr',
                'inventoryid','inventory_descr','siteid',
                DB::raw("COALESCE(uom,'')               AS uom"),
                DB::raw("COALESCE(qty,0)                AS qty_original"),
                DB::raw("COALESCE(qty_received,0)       AS qty_received"),
                DB::raw("COALESCE(qty_return,0)         AS qty_return"),
                DB::raw("GREATEST(COALESCE(qty,0) - COALESCE(qty_received,0) + COALESCE(qty_return,0), 0) AS qty_sisa")
            ])
            ->where('ponbr', $po->ponbr)
            ->orderBy('id')
            ->get()
            // tampilkan hanya yang masih ada sisa
            ->filter(fn($r) => (float)$r->qty_sisa > 0)
            // supaya view tetap pakai $d->qty → set ke qty_sisa
            ->map(function ($r) {
                $r->qty = (float) $r->qty_sisa;
                return $r;
            })
            ->values();

        // Saat create, attachment biasanya kosong
        $attachments = [];

        return view('pages.receipt.createreceipt', [
            'po'          => $po,
            'details'     => $details,
            'attachments' => $attachments,
        ]);
    }
    
    public function storeReceipt(Request $request)
    {
        // dd($request->all());
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $ponbr = trim((string)$request->input('ponbr', ''));
        if ($ponbr === '') {
            return back()->withErrors(['PO number not found.'])->withInput();
        }

        // ambil PO header
        $po = TrPo::where('ponbr', $ponbr)->first();
        if (!$po) {
            return back()->withErrors(['PO not found.'])->withInput();
        }

        // ambil detail PO
        $poDetails = TrPodetail::where('ponbr', $ponbr)
            ->get()->keyBy('id');
              
        // qty receipt dari form: qty_receipt[detail_id] => float
        $qtyReceiptInput = (array) $request->input('qty_receipt', []);
        $hasAnyQty = false;
        foreach ($qtyReceiptInput as $k => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty > 0) { $hasAnyQty = true; break; }
        }
        if (!$hasAnyQty) {
            return back()->withErrors(['Qty receipt minimal satu baris harus > 0.'])->withInput();
        }

        $siteInput = (array) $request->input('siteid', []);

        // === Approval line check (doctype GR) ===
        $doctype   = 'GR';
        $cpnyid    = $po->cpny_id ?? ($request->input('cpnyid') ?? null);
        $deptid    = $po->department_id ?? ($request->input('departementid') ?? null);
       
        
        DB::beginTransaction();
        try {
            // === Auto number: receiptnbr (GRYYMM####)
            $now   = Carbon::now();
            $year  = $now->year;
            $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);

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
                $urut = 1;
            } else {
                $urut = $autonbr->number + 1;
                $autonbr->update(['number' => $urut]);
            }

            $yymm        = substr($year, 2) . $month; // YYMM
            $receiptnbr  = $doctype . $yymm . sprintf('%04d', $urut);

            // === Header TrReceipt ===
            $header = new TrReceipt();
            $header->receiptnbr        = $receiptnbr;
            $header->receiptdate       = $now->toDateString();      // tanggal dokumen
            $header->receipttype       = 'receipt';                  // 'GR'
            $header->ponbr             = $ponbr;
            $header->ref_receiptnbr    = null;
            $header->cpny_id           = $po->cpny_id ?? null;
            $header->csid              = $po->csid ?? null;
            $header->sppbjktid         = $po->sppbjktid ?? null;
            $header->department_id     = $po->department_id ?? null;
            $header->user_peminta      = $po->user_peminta ?? null;
            $header->receiptnote       = $request->input('receiptnote'); // kalau ada textarea note, opsional
            $header->vendorid          = $po->vendorid ?? null;
            $header->vendorname        = $po->vendorname ?? null;
            $header->totalqty_received = 0; // update setelah loop detail
            $header->status            = 'P';
            $header->created_by        = $username;
            $header->created_at        = $now;
            $header->save();

            // === Detail TrReceiptdetail ===
            $lineNo           = 0;
            $totalQtyReceived = 0.0;

            // $poDetails sudah: TrPOdetail::where('ponbr', $ponbr)->get()->keyBy('id');
            foreach ($poDetails as $srcId => $src) {
                // Ambil qty dari input: qty_receipt[<id_detail>], default 0
                $qtyRecRaw = $qtyReceiptInput[$srcId] ?? 0;
                $qtyRec    = (float) str_replace(',', '.', (string) $qtyRecRaw);

                if ($qtyRec <= 0) {
                    continue;
                }

                $siteFromForm = isset($siteInput[$srcId]) ? trim((string)$siteInput[$srcId]) : null;

                $lineNo++;

                $det = new TrReceiptdetail();
                $det->receiptnbr              = $receiptnbr;
                $det->receipt_no              = $lineNo;

                // Relasi ke PO
                $det->ponbr                   = $ponbr;
                $det->po_no                   = $src->po_no;

                // Turunan header (kalau punya)
                $det->csid                    = $po->csid ?? null;
                $det->cs_no                   = $src->cs_no ?? null;
                $det->sppbjktid               = $po->sppbjktid ?? null;
                $det->sppbjktid_no            = $src->sppbjktid_no ?? null; 

                // Kolom inventory dari PO detail
                $det->inventory_type          = $src->inventory_type ?? null;
                $det->inventoryid             = $src->inventoryid;
                $det->inventory_descr         = $src->inventory_descr;
                $det->qtyordered              = $src->qty;
                $det->uom                     = $src->uom;

                // Base/default (tanpa konversi)
                $det->type_multiplier         = null;
                $det->base_multiplier         = 1;
                $det->base_qty                = $qtyRec;
                $det->base_uom                = $src->uom;

                // Harga/pajak (tidak diisi di receipt)
                $det->unitcost                = $src->unitcost;
                $det->taxcodeid               = $src->uom;
                $det->taxamt                  = $src->taxamt;
                $det->totalcost               = $src->totalcost;

                $det->receipttype             = 'receipt';
                $det->siteid                  = $siteFromForm !== '' ? $siteFromForm : ($src->siteid ?? null);

                // Open ordered (jika belum dihitung sisa, biarkan null)
                $det->qty_open_ordered        = $qtyRec;
                $det->base_qty_open_ordered   = $qtyRec;

                // YANG DIMINTA: qty_received dari form
                $det->qty_received            = $qtyRec;
                $det->base_qty_received       = $qtyRec;

                $det->qty_return              = 0;
                $det->base_qty_return         = 0;

                $det->ref_receiptnbr          = null;

                // Budget fields (kosong)
                $det->budget_perpost          = $src->budget_perpost;
                $det->budget_cpny_id          = $src->budget_cpny_id;
                $det->budget_business_unit_id = $src->budget_business_unit_id;
                $det->budget_department_fin_id= $src->budget_department_fin_id;
                $det->budget_account_id       = $src->budget_account_id;
                $det->budget_activity_id      = $src->budget_activity_id;
                $det->budget_activity_descr   = $src->budget_activity_descr;

                $det->status                  = 'P';
                $det->created_by              = $username;
                $det->created_at              = $now;

                $det->save();

                $totalQtyReceived += $qtyRec;
            }

            // Validasi minimal ada qty > 0 (setelah fakta tersimpan)
            if ($totalQtyReceived <= 0) {
                DB::rollBack();
                return back()->withErrors(['Qty receipt minimal satu baris harus > 0.'])->withInput();
            }

            // update total qty di header
            $header->totalqty_received = $totalQtyReceived;
            $header->save();


            // if ($request->hasfile('attachments')) {
            //     foreach ($request->file('attachments') as $file) {
            //         $randomNumber = random_int(10000000, 99999999);
            //         $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                   
            //         $originalName = str_replace('%', '', $file->getClientOriginalName());
            //         $ext        = $file->getClientOriginalExtension();
            //         $attachfile = md5($randomNumber) . '.' . $ext;

            //         //attach to folder
            //         $folder_attach = public_path() . '/attachments/'.$year;
            //         $config['upload_path'] = $folder_attach;                   
            //         if(!is_dir($folder_attach))
            //         {
            //             mkdir($folder_attach, 0777);
            //         }
                    
            //         $folder_upload = $folder_attach;
            //         // $folder_upload = public_path() . '/attachments';
            //         $file->move($folder_upload, $attachfile);

            //         //insert to table attachments
            //         $attach = new Attachment();
            //         $attach->docid = $receiptnbr;
            //         $attach->name = $filename;
            //         $attach->attachfile = $attachfile;
            //         $attach->status = 'A';
            //         $attach->extention = $file->getClientOriginalExtension();
            //         $attach->created_user = $user->username;
            //         $attach->save();
            //     }
            // }            

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $receiptnbr,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyid,
                    'departementid' => $deptid,                    
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $user->username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                    // tidak return di sini!
                } catch (\Throwable $e) {
                    \DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create PB',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }
                       
            DB::commit();

            return redirect()
                ->route('receiptlist.index')
                ->with('success', "Receipt {$receiptnbr} created. Total Qty: {$totalQtyReceived}");

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors([config('app.debug') ? $e->getMessage() : 'Failed to create Receipt'])->withInput();
        }
    }

    public function showReceipt($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // ===== Header Receipt
        $rcp = TrReceipt::findOrFail($id);

        // ===== Detail Receipt
        $rcpdetail = TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
            ->orderBy('receipt_no')
            ->get();

        // ===== Attachment by receiptnbr
        $attachment = Attachment::where('docid', $rcp->receiptnbr)
            ->where('status', 'A')
            ->get();

        // ===== Link ke PO (opsional)
        $poUrl = null;
        if (!empty($rcp->ponbr)) {
            $poId = TrPO::where('ponbr', $rcp->ponbr)->value('id');
            if ($poId) {
                $poHash = Hashids::encode($poId);
                $poUrl  = url("/showpo/{$poHash}");
            }
        }

       
        // ===== Link ke SPPB/J/K/T (opsional)
        $sppbUrl = null;
        $sppbjktid = (string)($rcp->sppbjktid ?? '');
        $prefix = strtoupper(substr($sppbjktid, 0, 2));

        $routeMap = [
            'PB' => 'showsppbs',
            'PJ' => 'showsppjs',
            'PK' => 'showsppks',
            'PT' => 'showsppts',
        ];

        if ($sppbjktid !== '' && isset($routeMap[$prefix])) {
            $docId = null;

            if ($prefix === 'PB') {                
                $docId = TrSPPB::where('sppbid', $sppbjktid)->value('id');
            } elseif ($prefix === 'PJ') {
                $docId = TrSPPJ::where('sppjid', $sppbjktid)->value('id');
            } elseif ($prefix === 'PK') {
                $docId = TrSPPK::where('sppkid', $sppbjktid)->value('id');
            } elseif ($prefix === 'PT') {
                $docId = TrSPPT::where('spptid', $sppbjktid)->value('id');
            }

            if (!empty($docId)) {
                $sppbHash = Hashids::encode($docId);
                $sppbUrl  = url('/' . $routeMap[$prefix] . '/' . $sppbHash);
            }
        }

        // ===== Link ke CS (opsional)
        $csUrl = null;
        if (!empty($rcp->csid)) {
            $csId = TrCS::where('csid', $rcp->csid)->value('id');
            if ($csId) {
                $csHash = Hashids::encode($csId);
                $csUrl  = url("/showcs/{$csHash}");
            }
        }

        // Untuk convenience (mis. kirim email dsb)
        $eid_receiptnbr = Hashids::encode($rcp->receiptnbr);

        return view('pages.receipt.showreceipt', [
            'rcp'            => $rcp,
            'rcpdetail'      => $rcpdetail,
            'attachment'     => $attachment,
            'hash'           => $hash,
            'eid_receiptnbr' => $eid_receiptnbr,
            'poUrl'          => $poUrl,
            'sppbUrl'        => $sppbUrl,
            'csUrl'          => $csUrl,
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

    public function printReceipt_xxx(string $hash)
    {
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Dokumen tidak ditemukan.');
        $id = $decoded[0];

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }
        
        $rcp = TrReceipt::findOrFail($id);

        // ===== DETAIL
        $rcpdetails = TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
            ->orderBy('receipt_no')
            ->get();

        $po = TrPO::where('ponbr', $rcp->ponbr)           
            ->first();

        // ===== COMPANY (untuk brand/footnote)
        $company = CompanyPG::where('cpny_id', $rcp->cpny_id)->first();

        // $createdName = ucwords(strtolower($authUser->name));
        $createdName = ucwords(strtolower(optional($rcp->creator)->name));
        
        $now = Carbon::now();

        $data = [
            'rcp'       => $rcp,
            'rcpdetails'   => $rcpdetails,
            'po'       => $po,
            'company'   => $company,
            'now'       => $now,
            'created'   => $createdName,
        ];

        // Gunakan satu view khusus receipt
        // $view = 'pages.receipt.pdf_receipt';
        $view = 'pages.receipt.pdf_bpg';

        // 1) render view -> Dompdf
        $pdf = Pdf::loadView($view, $data)->setPaper('A4', 'portrait');

        // 2) Render dulu supaya PAGE_COUNT siap
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        // 3) Footer via canvas
        $canvas  = $dompdf->get_canvas();
        $w       = $canvas->get_width();
        $h       = $canvas->get_height();

        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->get_font('sans-serif', 'normal');
        $size    = 9;

        $leftTxt  = "Created by: {$createdName}, Sent by: {$createdName}, On: ".$now->format('d/m/Y H:i');
        $rightTpl = "Page {PAGE_NUM} of {PAGE_COUNT}";

        $rightWidth = $metrics->getTextWidth($rightTpl, $font, $size);
        $y = $h - 28;               // ~10mm dari bawah (tergantung margin @page)
        $x = $canvas->get_width() - $w - 75;

        // kiri 20px, kanan (w - 20px - textwidth)
        $canvas->page_text(30, $y, $leftTxt, $font, $size, [0,0,0]);
        $canvas->page_text($w - $x - $rightWidth, $y, $rightTpl, $font, $size, [0,0,0]);

        // 4) Stream
        $basename = 'RCP';
        return $dompdf->stream("{$basename}_{$rcp->rcpnbr}.pdf", ['Attachment' => false]);
    }

    public function printReceipt(string $hash, Request $request)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        $rcp = TrReceipt::with(['creator:username,name'])->findOrFail($id);
        $po  = TrPO::where('ponbr', $rcp->ponbr)->first();
        $rcpdetails = TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
            ->orderBy('receipt_no')->get();
        $company = Company::where('cpnyid', $rcp->cpny_id)->first();

        $data = compact('rcp','po','rcpdetails','company');

        $type = strtolower((string)$request->query('type', 'sttb'));
        $view = $type === 'bpg' ? 'pages.receipt.pdf_bpg' : 'pages.receipt.pdf_receipt';

        $createdName = ucwords(strtolower(optional($rcp->creator)->name ?? $rcp->created_by));
        $now = now();

        $pdf = Pdf::loadView($view, $data)->setPaper('A4','portrait');

        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        // footer
        $canvas  = $dompdf->get_canvas();
        $w       = $canvas->get_width();
        $h       = $canvas->get_height();
        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->get_font('sans-serif', 'normal');
        $size    = 9;

        $leftTxt  = "Created by: {$createdName}, Sent by: {$createdName}, On: ".$now->format('d/m/Y H:i');
        $rightTpl = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $rightWidth = $metrics->getTextWidth($rightTpl, $font, $size);
        $y = $h - 28;
        $x = $canvas->get_width() - $w - 75;
        $canvas->page_text(30, $y, $leftTxt, $font, $size, [0,0,0]);
        $canvas->page_text($w - $x - $rightWidth, $y, $rightTpl, $font, $size, [0,0,0]);

        $basename = $type === 'bpg' ? 'BPG' : 'STTB';
        return $dompdf->stream("{$basename}_{$rcp->receiptnbr}.pdf", ['Attachment' => false]);
    }

    public function approveReceipt(Request $request, $id)
    {
        return DB::connection('pgsql')->transaction(function () use ($id) {

            $user  = Auth::user();
            $uname = $user->username ?? null;
            $now   = \Carbon\Carbon::now();

            // Lock header dulu
            $rcp = TrReceipt::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($rcp->status !== 'P') {
                return response()->json([
                    'ok' => false,
                    'message' => 'Receipt tidak dalam status PENDING.'
                ], 422);
            }

            // Ambil semua detail receipt
            $rcpdetails = TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
                ->orderBy('receipt_no')
                ->get();

            if ($rcpdetails->isEmpty()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Tidak ada detail receipt untuk diproses.'
                ], 422);
            }

            // Lock PO yang terkait
            $po = TrPO::where('ponbr', $rcp->ponbr)->lockForUpdate()->first();
            if (!$po) {
                return response()->json([
                    'ok' => false,
                    'message' => 'PO terkait tidak ditemukan.'
                ], 422);
            }

            // Ambil semua detail PO sekali, lalu buat peta (map) pencocokan
            $poDetailRows  = TrPOdetail::where('ponbr', $rcp->ponbr)->get();
            $poByKey       = $poDetailRows->keyBy(fn($row) => ($row->inventoryid ?? '').'|'.($row->uom ?? ''));
            $poByInventory = $poDetailRows->groupBy('inventoryid');

            $totalQtyReceivedThisReceipt = 0.0; // hanya untuk tipe 'receipt'
            $totalQtyReturnThisReceipt   = 0.0; // hanya untuk tipe 'return'

            if ($rcp->receipttype === 'receipt') {
                // ======== APPROVE untuk penerimaan barang ========
                foreach ($rcpdetails as $rd) {
                    $key   = ($rd->inventoryid ?? '').'|'.($rd->uom ?? '');
                    $poDet = $poByKey->get($key) ?: optional($poByInventory->get($rd->inventoryid))->first();
                    if (!$poDet) continue;

                    $qtyRec     = (float) ($rd->qty_received ?? 0);
                    $baseQtyRec = (float) ($rd->base_qty_received ?? 0);

                    $poDet->qty_received      = (float) ($poDet->qty_received ?? 0) + $qtyRec;
                    $poDet->base_qty_received = (float) ($poDet->base_qty_received ?? 0) + $baseQtyRec;

                    // set flag detail
                    $ordered = (float) ($poDet->qty ?? 0);
                    if ($ordered > 0 && $poDet->qty_received >= $ordered) {
                        $poDet->received  = true;
                        $poDet->completed = true;
                        $poDet->status    = 'C';
                    } else {
                        $poDet->received  = true;   // sudah ada penerimaan
                        $poDet->completed = false;
                        $poDet->status    = 'P';    // partial
                    }

                    $poDet->updated_by = $uname;
                    $poDet->save();

                    $totalQtyReceivedThisReceipt += $qtyRec;
                }

                // Recalculate total qty_received di header PO
                $po->totalqtyreceived = TrPOdetail::where('ponbr', $rcp->ponbr)->sum('qty_received');

                // Jika semua detail sudah completed, close PO
                $allCompleted = TrPOdetail::where('ponbr', $rcp->ponbr)
                    ->where(function ($q) {
                        // masih ada item yang qty > qty_received
                        $q->whereRaw('(COALESCE(qty,0) > COALESCE(qty_received,0))');
                    })
                    ->exists() === false;

                if ($allCompleted) {
                    $po->status       = 'C';
                    $po->completed_by = $uname;
                    $po->completed_at = $now;
                }
                $po->updated_by = $uname;
                $po->save();

                // Update header receipt
                $rcp->status            = 'C';
                $rcp->totalqty_received = $totalQtyReceivedThisReceipt;  // jika kolom ini memang ada
                $rcp->completed_by      = $uname;
                $rcp->completed_at      = $now;
                $rcp->updated_by        = $uname;
                $rcp->save();

                return response()->json([
                    'ok'      => true,
                    'message' => 'Receipt (penerimaan) berhasil di-approve & PO diperbarui.',
                    'data'    => [
                        'type'                 => $rcp->receipttype,
                        'receiptnbr'           => $rcp->receiptnbr,
                        'ponbr'                => $rcp->ponbr,
                        'po_totalqtyreceived'  => (float) $po->totalqtyreceived,
                    ],
                ]);
            }

            if ($rcp->receipttype === 'return') {
                // ======== APPROVE untuk return barang ========
                foreach ($rcpdetails as $rd) {
                    $key   = ($rd->inventoryid ?? '').'|'.($rd->uom ?? '');
                    $poDet = $poByKey->get($key) ?: optional($poByInventory->get($rd->inventoryid))->first();
                    if (!$poDet) continue;

                    $qtyRet = (float) ($rd->qty_return ?? 0);
                    $baseQtyRet = (float) ($rd->base_qty_return ?? 0);

                    // Catat di PO detail kolom return (TIDAK mengurangi qty_received di sini)
                    $poDet->qty_return      = (float) ($poDet->qty_return ?? 0) + $qtyRet;
                    if (property_exists($poDet, 'base_qty_return')) {
                        $poDet->base_qty_return = (float) ($poDet->base_qty_return ?? 0) + $baseQtyRet;
                    }

                    // Status PO tidak di-close karena return
                    $poDet->updated_by = $uname;
                    $poDet->save();

                    $totalQtyReturnThisReceipt += $qtyRet;
                }

                // (optional) kalau kamu punya kolom akumulasi return di header PO, isi di sini.
                // Contoh:
                // $po->totalqty_return = TrPOdetail::where('ponbr', $rcp->ponbr)->sum('qty_return');
                $po->updated_by = $uname;
                $po->save();

                // Update header receipt return → Completed
                $rcp->status       = 'C';
                // (optional) kalau ada kolom totalqty_return di header receipt, catat:
                if (Schema::hasColumn($rcp->getTable(), 'totalqty_return')) {
                    $rcp->totalqty_return = $totalQtyReturnThisReceipt;
                }
                $rcp->completed_by = $uname;
                $rcp->completed_at = $now;
                $rcp->updated_by   = $uname;
                $rcp->save();

                return response()->json([
                    'ok'      => true,
                    'message' => 'Receipt (return) berhasil di-approve & qty_return di PO diperbarui.',
                    'data'    => [
                        'type'                => $rcp->receipttype,
                        'receiptnbr'          => $rcp->receiptnbr,
                        'ponbr'               => $rcp->ponbr,
                        // 'po_totalqty_return' => TrPOdetail::where('ponbr', $rcp->ponbr)->sum('qty_return'), // jika ingin dikirim
                    ],
                ]);
            }

            // receipttype selain 'receipt' / 'return' → tolak
            return response()->json([
                'ok' => false,
                'message' => 'Tipe receipt tidak dikenali.'
            ], 422);
        });
    }



    public function createReturn(Request $request)
    {
        $eid = (string) $request->query('rcp', '');
        $id  = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        // Header receipt asal (tipe bebas; kita cuma mau referensinya)
        $rcp = TrReceipt::findOrFail($id);

        // Detail dari receipt asal (yang qty_received-nya menjadi dasar perhitungan)
        $origDetails = TrReceiptdetail::select([
            'id',
            'receiptnbr',
            'receipt_no',
            'inventoryid',
            'inventory_descr',
            'uom',
            'siteid',

            // Pastikan sama-sama numeric
            DB::raw("COALESCE((qty_received)::numeric, 0)::numeric AS qty_received"),
            DB::raw("COALESCE((base_qty_received)::numeric, 0)::numeric AS base_qty_received"),

            // Kolom multiplier sering disimpan varchar → kosongkan jadi NULL, lalu cast ke int, lalu COALESCE 1
            DB::raw("COALESCE(NULLIF(type_multiplier, '')::int, 1) AS type_multiplier"),
            DB::raw("COALESCE(NULLIF(base_multiplier, '')::int, 1) AS base_multiplier"),
        ])
        ->where('receiptnbr', $rcp->receiptnbr)
        ->orderBy('receipt_no')
        ->get();


        // Total qty_return yang SUDAH dibuat dari semua dokumen 'return' yang refer ke receipt ini
        // Asumsi: dokumen pengembalian (return) disimpan di TrReceipt dengan receipttype='return'
        // dan TrReceiptdetail.qty_return berisi qty return per item.
        $returnedAgg = TrReceiptdetail::query()
            ->select([
                'inventoryid',
                'uom',
                'siteid',
                DB::raw('SUM(COALESCE(qty_return,0)) AS sum_returned')
            ])
            ->whereIn('receiptnbr', function($q) use ($rcp) {
                $q->select('receiptnbr')
                ->from('tr_receipt') // tabel TrReceipt
                ->where('receipttype', 'return')
                ->where('ref_receiptnbr', $rcp->receiptnbr);
            })
            ->groupBy('inventoryid','uom','siteid')
            ->get()
            ->keyBy(function($r){
                return ($r->inventoryid ?? '').'|'.($r->uom ?? '').'|'.($r->siteid ?? '');
            });

        // Hitung sisa return per baris asal; tampilkan hanya yang masih > 0
        $details = $origDetails->map(function($row) use ($returnedAgg){
                $key = ($row->inventoryid ?? '').'|'.($row->uom ?? '').'|'.($row->siteid ?? '');
                $sudahReturn = (float) optional($returnedAgg->get($key))->sum_returned ?? 0.0;

                $qtyReceived = (float) $row->qty_received;
                $sisa        = max($qtyReceived - $sudahReturn, 0);

                // Agar view bisa langsung pakai $d->qty sebagai 'qty yang masih bisa direturn'
                $row->qty_sisa_return = $sisa;
                $row->qty             = $sisa; // kompatibel dengan view yg pakai $d->qty
                return $row;
            })
            ->filter(fn($r) => $r->qty_sisa_return > 0)
            ->values();

        // Jika semua item sudah habis direturn, optional: tampilkan info
        if ($details->isEmpty()) {
            return back()->with('warning', 'Semua item pada receipt ini sudah tidak memiliki sisa untuk di-return.');
        }

        // Kirimkan juga ref_receiptnbr untuk hidden input di form
        $ref_receiptnbr = $rcp->receiptnbr;

        // Tampilkan view input return (qty_return), hidden: ref_receiptnbr
        return view('pages.receipt.return_create', [
            'rcp'             => $rcp,
            'details'         => $details,
            'eid'             => $eid,
            'ref_receiptnbr'  => $ref_receiptnbr,
        ]);
    }

    // Simpan dokumen return
    public function storeReturn(Request $request)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $eid = (string)$request->input('rcp', '');
        $id  = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $src = TrReceipt::findOrFail($id); // receipt sumber (GR)
        $qtyInput = (array)$request->input('qty_return', []); // [detail_id => qty]
        $notes    = (string)$request->input('return_note', '');

        // minimal satu qty > 0
        $hasAny = false;
        foreach ($qtyInput as $k => $v) {
            $q = (float)str_replace(',', '.', (string)$v);
            if ($q > 0) { $hasAny = true; break; }
        }
        if (!$hasAny) {
            return back()->withErrors(['Minimal satu baris Qty Return > 0.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // === Autonumber: RTYYMM#### (ubah doctype bila perlu)
            $doctype = 'GR';
            $now   = Carbon::now();
            $year  = (int)$now->year;
            $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);

            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)->where('year', $year)->where('month', $month)
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => $year,
                    'month'   => $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $urut = 1;
            } else {
                $urut = ($autonbr->number ?? 0) + 1;
                $autonbr->update(['number' => $urut]);
            }

            $receiptnbr = $doctype . substr($year,2) . $month . sprintf('%04d', $urut);

            // === Header return (copy dari sumber)
            $hdr = new TrReceipt();
            $hdr->receiptnbr        = $receiptnbr;
            $hdr->receiptdate       = $now->toDateString();
            // agar muncul di tab Return Jobs sesuai filter kamu:
            $hdr->receipttype       = 'return'; // <— sesuai filter returnjobs (status C + receipttype='receipt')
            $hdr->ponbr             = $src->ponbr;
            $hdr->ref_receiptnbr    = $src->receiptnbr;            // <— penting
            $hdr->cpny_id           = $src->cpny_id;
            $hdr->csid              = $src->csid;
            $hdr->sppbjktid         = $src->sppbjktid;
            $hdr->department_id     = $src->department_id;
            $hdr->user_peminta      = $src->user_peminta;
            $hdr->receiptnote       = $notes;
            $hdr->vendorid          = $src->vendorid;
            $hdr->vendorname        = $src->vendorname;
            $hdr->totalqty_received = 0; // untuk return kita isi setelah loop sbg total qty return
            $hdr->status            = 'P'; // langsung complete, atau 'P' kalau mau ada approval
            $hdr->created_by        = $username;
            $hdr->created_at        = $now;
            $hdr->save();

            // === Detail return: simpan hanya baris qty_return > 0
            $srcDetails = TrReceiptdetail::where('receiptnbr', $src->receiptnbr)->get()->keyBy('id');
            $line  = 0; $totalReturn = 0.0;

            foreach ($qtyInput as $detailId => $raw) {
                $qty = (float)str_replace(',', '.', (string)$raw);
                if ($qty <= 0) continue;          // ⬅️ hanya simpan baris > 0
                $srcDet = $srcDetails[$detailId] ?? null;
                if (!$srcDet) continue;

                $line++;

                $det = new TrReceiptdetail();
                $det->receiptnbr              = $receiptnbr;
                $det->receipt_no              = $line;

                $det->ponbr                   = $src->ponbr;
                $det->po_no                   = $srcDet->po_no;

                $det->csid                    = $src->csid;
                $det->cs_no                   = $srcDet->cs_no;
                $det->sppbjktid               = $src->sppbjktid;
                $det->sppbjktid_no            = $srcDet->sppbjktid_no;

                $det->inventory_type          = $srcDet->inventory_type;
                $det->inventoryid             = $srcDet->inventoryid;
                $det->inventory_descr         = $srcDet->inventory_descr;
                $det->qtyordered              = $srcDet->qtyordered;
                $det->uom                     = $srcDet->uom;

                // base
                $det->type_multiplier         = null;
                $det->base_multiplier         = 1;
                $det->base_qty                = 0; // return: base qty utama tidak dipakai
                $det->base_uom                = $srcDet->uom;

                // harga (copy)
                $det->unitcost                = $srcDet->unitcost;
                $det->taxcodeid               = $srcDet->taxcodeid;
                $det->taxamt                  = $srcDet->taxamt;
                $det->totalcost               = $srcDet->totalcost;

                $det->receipttype             = $hdr->receipttype;

                // open ordered (tidak relevan utk return)
                $det->qty_open_ordered        = 0;
                $det->base_qty_open_ordered   = 0;

                // return qty
                $det->qty_received            = 0;
                $det->base_qty_received       = 0;

                $det->qty_return              = $qty;
                $det->base_qty_return         = $qty;

                $det->ref_receiptnbr          = $src->receiptnbr;  // <— referensi

                // budget (copy)
                $det->budget_perpost          = $srcDet->budget_perpost;
                $det->budget_cpny_id          = $srcDet->budget_cpny_id;
                $det->budget_business_unit_id = $srcDet->budget_business_unit_id;
                $det->budget_department_fin_id= $srcDet->budget_department_fin_id;
                $det->budget_account_id       = $srcDet->budget_account_id;
                $det->budget_activity_id      = $srcDet->budget_activity_id;
                $det->budget_activity_descr   = $srcDet->budget_activity_descr;

                $det->status                  = 'C';
                $det->created_by              = $username;
                $det->created_at              = $now;
                $det->save();

                $totalReturn += $qty;
            }

            if ($totalReturn <= 0) {
                DB::rollBack();
                return back()->withErrors(['Minimal satu baris Qty Return > 0.'])->withInput();
            }

            // simpan total return di header (pakai kolom totalqty_received sebagai penampung)
            $hdr->totalqty_received = $totalReturn;
            $hdr->save();
    
            $deptid = $src->department_id;
            $cpnyid = $src->cpny_id;
            
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $receiptnbr,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyid,
                    'departementid' => $deptid,                    
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $user->username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                    // tidak return di sini!
                } catch (\Throwable $e) {
                    \DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create PB',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

            DB::commit();

            return redirect()->route('receiptlist.index')
                ->with('success', "Return {$receiptnbr} created from {$src->receiptnbr}. Total Qty Return: {$totalReturn}");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors([config('app.debug') ? $e->getMessage() : 'Failed to create Return'])->withInput();
        }
    }



    


}
