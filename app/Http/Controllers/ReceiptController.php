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
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use App\Models\Attachment;
use App\Models\T_Message;
use App\Models\MsVendor;
use App\Models\MsCompany;
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
use App\Http\Controllers\TrAttachmentController;
use App\Models\TrAttachment;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;

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
            'inventoryid','inventory_descr','siteid','inventory_type',
            DB::raw("COALESCE(uom,'')               AS uom"),
            DB::raw("COALESCE(qty,0)                AS qty_original"),
            DB::raw("COALESCE(qty_received,0)       AS qty_received"),
            DB::raw("COALESCE(qty_completed,0)      AS qty_completed"),  // ✅ add
            DB::raw("COALESCE(qty_return,0)         AS qty_return"),
            DB::raw("
                GREATEST(
                    COALESCE(qty,0)
                    - COALESCE(qty_received,0)
                    - COALESCE(qty_completed,0)
                    + COALESCE(qty_return,0),
                    0
                ) AS qty_sisa
            "),
        ])
        ->where('ponbr', $po->ponbr)
        ->orderBy('id')
        ->get()
        ->filter(fn($r) => (float)$r->qty_sisa > 0)
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
        // dd($request->all()); // Debugging: check request data
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $ponbr = trim((string)$request->input('ponbr', ''));
        if ($ponbr === '') {
            return back()->withErrors(['PO number not found.'])->withInput();
        }

        /** @var TrPO|null $po */
        $po = TrPO::where('ponbr', $ponbr)->first();
        if (!$po) {
            return back()->withErrors(['PO not found.'])->withInput();
        }

        $poDetails = TrPOdetail::where('ponbr', $ponbr)->get()->keyBy('id');
        if ($poDetails->isEmpty()) {
            return back()->withErrors(['PO detail not found.'])->withInput();
        }

        $qtyReceiptInput = (array) $request->input('qty_receipt', []);
        if (empty($qtyReceiptInput)) {
            $qtyReceiptInput = (array) $request->input('qty_received', []);
        }

        $detailNoteInput = (array) $request->input('detail_note', []);
        
        $hasAnyQty = false;
        foreach ($qtyReceiptInput as $k => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty > 0) { $hasAnyQty = true; break; }
        }
        if (!$hasAnyQty) {
            return back()->withErrors(['Qty receipt minimal satu baris harus > 0.'])->withInput();
        }

        $siteInput = (array) $request->input('siteid', []);

        $doctype = 'GR';
        $cpnyid  = $po->cpny_id       ?? ($request->input('cpnyid')       ?? null);
        // $deptid  = $po->department_id ?? ($request->input('departmentid') ?? null);
        $deptid  = 'WAREHOUSE';

        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyid, $deptid);

        return DB::connection('pgsql')->transaction(function () use (
            $request, $username, $ponbr, $poDetails, $po, $qtyReceiptInput, $siteInput,
            $doctype, $cpnyid, $deptid, $approvalCtl, $detailNoteInput   
        ) {
            $now   = \Carbon\Carbon::now();
            $year  = (int) $now->year;
            $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);

            $autonbr = Autonbr::where('doctype', $doctype)
                ->where('year', $year)->where('month', $month)
                ->lockForUpdate()->first();

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
                $urut = (int)($autonbr->number ?? 0) + 1;
                $autonbr->update(['number' => $urut]);
            }
            $yymm       = substr((string)$year, 2) . $month;
            $receiptnbr = $doctype . $yymm . sprintf('%04d', $urut);

            $header = new TrReceipt();
            $header->receiptnbr        = $receiptnbr;
            $header->receiptdate       = $now->toDateString();
            $header->receipttype       = 'PR';
            $header->ponbr             = $ponbr;
            $header->ref_receiptnbr    = null;
            $header->cpny_id           = $po->cpny_id ?? null;
            $header->csid              = $po->csid ?? null;
            $header->sppbjktid         = $po->sppbjktid ?? null;
            $header->department_id     = $po->department_id ?? null;
            $header->user_peminta      = $po->user_peminta ?? null;
            $header->receiptnote       = $request->input('receiptnote');
            $header->vendorid          = $po->vendorid ?? null;
            $header->vendorname        = $po->vendorname ?? null;
            $header->totalqty_received = 0;
            $header->status            = 'P';
            $header->created_by        = $username;
            $header->created_at        = $now;
            $header->save();

            $lineNo = 0;
            $totalQtyReceived = 0.0;

            foreach ($poDetails as $srcId => $src) {
                $qtyRecRaw = $qtyReceiptInput[$srcId] ?? 0;
                $qtyRec    = (float) str_replace(',', '.', (string)$qtyRecRaw);
                if ($qtyRec <= 0) continue;

                $lineNo++;
                $siteFromForm = isset($siteInput[$srcId]) ? trim((string)$siteInput[$srcId]) : null;

                // ambil note per detail (boleh kosong)
                // $lineNoteRaw = $detailNoteInput[$srcId] ?? null;
                // $lineNote    = is_null($lineNoteRaw) ? null : trim((string)$lineNoteRaw);
                $lineNote = isset($detailNoteInput[$srcId]) ? trim((string)$detailNoteInput[$srcId]) : null;

                $det = new TrReceiptdetail();
                $det->receiptnbr              = $receiptnbr;
                $det->receipt_no              = $lineNo;
                $det->ponbr                   = $ponbr;
                $det->po_no                   = $src->po_no ?? null;
                $det->csid                    = $po->csid ?? null;
                $det->cs_no                   = $src->cs_no ?? null;
                $det->sppbjktid               = $po->sppbjktid ?? null;
                $det->sppbjktid_no            = $src->sppbjktid_no ?? null;
                $det->inventory_type          = $src->inventory_type ?? null;
                $det->inventoryid             = $src->inventoryid;
                $det->inventory_descr         = $src->inventory_descr;
                $det->inventory_category      = $src->inventory_category ?? null;
                $det->inventory_sub_type      = $src->inventory_sub_type ?? null;
                $det->qtyordered              = (float)($src->qty ?? 0);
                $det->uom                     = $src->uom;
                $det->type_multiplier         = $src->type_multiplier   ?? null;
                $det->base_multiplier         = $src->base_multiplier   ?? 1;
                $det->base_qty                = $qtyRec * (float)($det->base_multiplier ?: 1);
                $det->base_uom                = $src->base_uom          ?? $src->uom;
                $det->unitcost                = $src->unitcost ?? 0;
                $det->taxcodeid               = $src->taxcodeid ?? null;
                $det->taxamt                  = $src->taxamt ?? 0;
                $det->totalcost               = $src->totalcost ?? 0;
                $det->receipttype             = 'PR';
                $det->siteid                  = $siteFromForm !== '' ? $siteFromForm : ($src->siteid ?? null);
                // $det->qty_open_ordered        = $qtyRec;
                // $det->base_qty_open_ordered   = $det->base_qty;
                $det->qty_received            = $qtyRec;
                $det->base_qty_received       = $det->base_qty;
                $det->qty_return              = 0;
                $det->base_qty_return         = 0;
                $det->ref_receiptnbr          = null;
                $det->budget_perpost          = $src->budget_perpost ?? null;
                $det->budget_cpny_id          = $src->budget_cpny_id ?? null;
                $det->budget_business_unit_id = $src->budget_business_unit_id ?? null;
                $det->budget_department_fin_id= $src->budget_department_fin_id ?? null;
                $det->budget_account_id       = $src->budget_account_id ?? null;
                $det->budget_activity_id      = $src->budget_activity_id ?? null;
                $det->budget_activity_descr   = $src->budget_activity_descr ?? null;
                $det->receiptnote_detail      = $lineNote !== '' ? $lineNote : ($src->ponote_detail ?? null);
                $det->status                  = 'P';
                $det->created_by              = $username;
                $det->created_at              = $now;
                $det->save();

                $totalQtyReceived += $qtyRec;
            }

            if ($totalQtyReceived <= 0) {
                throw new \RuntimeException('Qty receipt minimal satu baris harus > 0.');
            }

            $header->totalqty_received = $totalQtyReceived;
            $header->save();

            // ✅ UPDATE PO di store
            try {
                app(static::class)->updatePO($request, $header->id);
            } catch (\Throwable $e) {
                \Log::error('storeReceipt: updatePO failed', [
                    'receiptnbr' => $header->receiptnbr,
                    'ponbr'      => $header->ponbr,
                    'id'         => $header->id,
                    'error'      => $e->getMessage(),
                ]);
                throw $e; // biar konsisten & aman
            }

            // === Generate TrApproval (GR tidak cek nominal)
            $ctx = ['ignore_nominal' => true];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $receiptnbr,
                $doctype,
                $cpnyid,
                $deptid,
                $username,
                $ctx,
                $now
            );

            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $now; // <-- ganti $dt jadi $now
                $header->save();
            }

            // Attachments
            $files = [];
            if ($request->hasFile('attachments')) {
                $files = (array) $request->file('attachments');
            } elseif ($request->hasFile('attachment')) {
                $files = (array) $request->file('attachment');
            }
            if (!empty($files)) {
                $meta = [
                    'refnbr'        => $receiptnbr,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyid,
                    'departementid' => $deptid,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $username,
                ];
                $uploader = app(\App\Http\Controllers\TrAttachmentController::class);
                $uploader->uploadInternal($meta, $files);
            }

            // Notifikasi approver pertama
            $eid = \Vinkla\Hashids\Facades\Hashids::encode($header->id);
            $approvalCtl->notifyFirstApprover(
                $receiptnbr,
                $doctype,
                $header->status, // 'P'
                'Receipt',
                url('/showreceipt/' . $eid),
                [
                    'info'      => 'Request from user ' . ($po->user_peminta ?? '-'),
                    'createdby' => $header->created_by,
                    'date'      => $now->toDateTimeString(),
                ]
            );

            return redirect()
                ->route('receiptlist')
                ->with('success', "Receipt {$receiptnbr} created. Total Qty: {$totalQtyReceived}");
        }, 3);
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

        // --- Approval trail ---
        $approval = T_approval::where('docid', $rcp->receiptnbr)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->orderBy('aprvid')
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

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload     = $rcp->created_by === $loginUsername;

        return view('pages.receipt.showreceipt', [
            'rcp'            => $rcp,
            'rcpdetail'      => $rcpdetail,
            'attachment'     => $attachment,
            'hash'           => $hash,
            'eid_receiptnbr' => $eid_receiptnbr,
            'poUrl'          => $poUrl,
            'sppbUrl'        => $sppbUrl,
            'csUrl'          => $csUrl,
            'approval'      => $approval,
            'canUpload'      => $canUpload,
        ]);
    }

    public function editReceipt($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        /** @var TrReceipt $rcp */
        $rcp = TrReceipt::findOrFail($id);

        // Hanya boleh edit saat status Revise (D) & oleh pembuat
        if (!in_array(($rcp->status ?? ''), ['D'])) {
            abort(403, 'Receipt tidak dalam status Revise.');
        }
        if (($rcp->created_by ?? '') !== ($user->username ?? '')) {
            abort(403, 'Anda tidak berhak mengedit dokumen ini.');
        }

        // Ambil detail
        $details = TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
            ->orderBy('receipt_no')
            ->get();

        // Attachment (doctype GR, refnbr = receiptnbr)
        $rows = TrAttachment::where('refnbr', $rcp->receiptnbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // Signed URL GCS (10 menit)
        $config      = config('filesystems.disks.gcs');
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
            $signedUrl  = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }
            return (object) [
                'id'            => $r->id,
                'display_name'  => $r->attachment_name ?? $r->name ?? $r->filename,
                'created_by'    => $r->created_by,
                'created_at'    => $r->created_at,
                'url'           => $signedUrl,
                'folder'        => $r->folder,
                'filename'      => $r->filename,
                'extention'     => $r->extention,
                'size'          => $r->filesize,
            ];
        });

        // hash utk link balik
        $hash_receipt = $hash;

        return view('pages.receipt.editreceipt', [
            'rcp'         => $rcp,
            'details'     => $details,
            'attachments' => $attachments,
            'hash'        => $hash_receipt,
        ]);
    }

    public function updateReceipt(Request $request, $hash)
    {
        $now      = \Carbon\Carbon::now();
        $user     = $request->user();
        $username = $user->username ?? 'system';

        // ===== Temukan Receipt by hash (id) atau fallback ke receiptnbr =====
        $ids = \Vinkla\Hashids\Facades\Hashids::decode($hash);
        if (!empty($ids)) {
            $rcp = \App\Models\TrReceipt::find($ids[0]);
        } else {
            // fallback: jika {hash} ternyata receiptnbr
            $rcp = \App\Models\TrReceipt::where('receiptnbr', $hash)->first();
        }
        if (!$rcp) {
            return response()->json(['message' => 'Receipt not found'], 404);
        }

        // Hanya boleh update jika status Revise (D) dan oleh pembuat dokumen
        if (strtoupper((string)$rcp->status) !== 'D' || (string)$rcp->created_by !== (string)$username) {
            return response()->json(['message' => "You can't update this document"], 403);
        }

        // ===== Ambil PO header & detail untuk validasi (TANPA update PO di sini) =====
        $ponbr = $rcp->ponbr;
        if (!$ponbr) {
            return response()->json(['message' => 'PO number not found on Receipt'], 422);
        }

        // Pastikan nama model konsisten
        /** @var \App\Models\TrPO|null $po */
        $po = \App\Models\TrPO::where('ponbr', $ponbr)->first();
        if (!$po) {
            return response()->json(['message' => 'PO header not found'], 422);
        }

        // Ambil semua detail PO → untuk matching by (inventoryid|uom)
        $poDetails     = \App\Models\TrPOdetail::where('ponbr', $ponbr)->get();
        $poByKey       = $poDetails->keyBy(fn($row) => ($row->inventoryid ?? '').'|'.($row->uom ?? ''));
        $poByInventory = $poDetails->groupBy('inventoryid');

        // ===== Input arrays =====
        $qtyReceivedInput = (array) $request->input('qty_received', []); // key = TrReceiptdetail.id
        $qtyReturnInput   = (array) $request->input('qty_return',   []); // key = TrReceiptdetail.id
        $siteInput        = (array) $request->input('siteid',       []); // key = TrReceiptdetail.id

        $rawType = strtoupper(trim((string)($rcp->receipttype ?? 'PR')));
        if (in_array($rawType, ['PR', 'RECEIPT', 'RCP'], true)) {
            $type = 'PR';
        } elseif (in_array($rawType, ['RR', 'RETURN', 'RET'], true)) {
            $type = 'RR';
        } else {
            return response()->json(['message' => 'Invalid receipttype'], 422);
        }

        // Minimal ada satu baris dikirim
        $payload = $type === 'RR' ? $qtyReturnInput : $qtyReceivedInput;
        if (count($payload) === 0) {
            return response()->json(['message' => 'Tidak ada qty yang dikirim.'], 422);
        }

        // ===== Ambil detail receipt keyed by id =====
        $detailRows = \App\Models\TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)->get()->keyBy('id');

        // ===== Validasi per-baris terhadap PO open =====
        foreach ($payload as $detailId => $v) {
            /** @var \App\Models\TrReceiptdetail|null $det */
            $det = $detailRows->get((int)$detailId);
            if (!$det) {
                return response()->json(['message' => "Receipt detail (ID: {$detailId}) tidak ditemukan."], 422);
            }

            $newQty = (float) str_replace(',', '.', (string)$v);
            if ($newQty < 0) {
                $label = $type === 'RR' ? 'Qty Return' : 'Qty Received';
                return response()->json(['message' => "{$label} untuk detail {$detailId} tidak boleh negatif."], 422);
            }

            // match ke PO detail
            $key   = ($det->inventoryid ?? '').'|'.($det->uom ?? '');
            $poDet = $poByKey->get($key) ?: optional($poByInventory->get($det->inventoryid))->first();
            if (!$poDet) {
                return response()->json(['message' => "PO Detail untuk item {$det->inventoryid} tidak ditemukan."], 422);
            }

            if ($type === 'PR') {
                // open PO = ordered - qty_received_tercatat
                $ordered  = (float)($poDet->qty ?? 0);
                $received = (float)($poDet->qty_received ?? 0);
                $openPo   = max($ordered - $received, 0.0);

                $oldQty   = (float)($det->qty_received ?? 0); // qty lama di baris ini
                $ceiling  = $openPo + $oldQty;

                if ($newQty > $ceiling + 1e-9) {
                    return response()->json([
                        'message' => "Qty Received item {$det->inventoryid} melebihi sisa open PO ({$openPo}) + qty saat ini ({$oldQty}). Maks: {$ceiling}"
                    ], 422);
                }
            } else {
                // RETURN: batasi ke (old_return + old_received_barismet) sebagai ceiling sederhana
                $oldRet  = (float)($det->qty_return ?? 0);
                $oldRec  = (float)($det->qty_received ?? 0);
                $ceiling = $oldRet + $oldRec;

                if ($newQty > $ceiling + 1e-9) {
                    return response()->json([
                        'message' => "Qty Return item {$det->inventoryid} terlalu besar. Maks revisi saat ini: {$ceiling} (return lama + received baris ini)."
                    ], 422);
                }
            }
        }

        // ===== Cek approval line aktif (doctype GR) =====
        $doctype = 'GR';
        $cpnyid  = $rcp->cpny_id ?? $rcp->cpnyid ?? null;
        // $deptid  = $rcp->department_id ?? $rcp->departementid ?? null;
        $deptid  = 'WAREHOUSE';

        // ===== Approval controller =====
        $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyid, $deptid);

        // ===== EXEC UPDATE DALAM TRANSAKSI =====
        return \DB::connection('pgsql')->transaction(function () use (
            $request, $now, $rcp, $detailRows, $qtyReceivedInput, $qtyReturnInput, $siteInput,
            $doctype, $cpnyid, $deptid, $username, $type, $approvalCtl
        ) {
            $receiptnbr   = $rcp->receiptnbr;
            $totalQtyEdit = 0.0;

            foreach ($detailRows as $detId => $det) {
                if ($type === 'PR') {
                    $qtyNew = array_key_exists($detId, $qtyReceivedInput)
                        ? (float) str_replace(',', '.', (string)$qtyReceivedInput[$detId])
                        : (float) ($det->qty_received ?? 0);
                    if ($qtyNew < 0) $qtyNew = 0;

                    $det->qty_received      = $qtyNew;
                    // hitung base pakai multiplier yang sudah tersimpan, default 1
                    $bm = (float)($det->base_multiplier ?: 1);
                    $det->base_qty_received = $qtyNew * $bm;

                    // open ordered tampilkan sisa di baris ini (opsional sesuai desain)
                    // $det->qty_open_ordered      = $qtyNew;
                    // $det->base_qty_open_ordered = $det->base_qty_received;
                } else {
                    $qtyNew = array_key_exists($detId, $qtyReturnInput)
                        ? (float) str_replace(',', '.', (string)$qtyReturnInput[$detId])
                        : (float) ($det->qty_return ?? 0);
                    if ($qtyNew < 0) $qtyNew = 0;

                    $det->qty_return      = $qtyNew;
                    $bm = (float)($det->base_multiplier ?: 1);
                    $det->base_qty_return = $qtyNew * $bm;
                }

                // site baru (opsional)
                if (array_key_exists($detId, $siteInput)) {
                    $newSite   = trim((string)$siteInput[$detId]);
                    $det->siteid = $newSite !== '' ? $newSite : $det->siteid;
                }

                $det->status     = 'P';
                $det->updated_by = $username;
                $det->updated_at = $now;
                $det->save();

                $totalQtyEdit += (float)$qtyNew;
            }

            // Update header total & reset status
            if ($type === 'PR') {
                $rcp->totalqty_received = $totalQtyEdit;
            } else {
                if (\Illuminate\Support\Facades\Schema::hasColumn($rcp->getTable(), 'totalqty_return')) {
                    $rcp->totalqty_return = $totalQtyEdit;
                }
            }
            $rcp->status       = 'P';
            $rcp->completed_by = null;
            $rcp->completed_at = null;
            $rcp->updated_by   = $username;
            $rcp->updated_at   = $now;
            $rcp->save();

            // ✅ UPDATE PO di store
            try {
                app(static::class)->updatePO($request, $rcp->id);
            } catch (\Throwable $e) {
                \Log::error('storeReceipt: updatePO failed', [
                    'receiptnbr' => $rcp->receiptnbr,
                    'ponbr'      => $rcp->ponbr,
                    'id'         => $rcp->id,
                    'error'      => $e->getMessage(),
                ]);
                throw $e; // biar konsisten & aman
            }

            // Generate TrApproval (GR tidak cek nominal)
            $ctx = ['ignore_nominal' => true];
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $receiptnbr,
                $doctype,
                $cpnyid,
                $deptid,
                $username,
                $ctx,
                $now
            );

            if ($firstApprovalUsernames) {
                $rcp->completed_by = $firstApprovalUsernames;
                $rcp->completed_at = $now;
                $rcp->save();
            }

            // Attachment baru (opsional)
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $receiptnbr,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyid ?? null,
                    'departementid' => $deptid ?? null,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $username,
                ];
                $files    = (array) $request->file('attachments');
                $uploader = app(\App\Http\Controllers\TrAttachmentController::class);
                $uploader->uploadInternal($meta, $files);
            }

            // Email approver pertama
            $eid = \Vinkla\Hashids\Facades\Hashids::encode($rcp->id);
            $approvalCtl->notifyFirstApprover(
                $receiptnbr,
                $doctype,
                $rcp->status, // 'P'
                'Receipt',
                url('/showreceipt/' . $eid),
                [
                    'info'      => 'Revised by user '.$username,
                    'createdby' => $rcp->created_by,
                    'date'      => $now->toDateTimeString(),
                ]
            );

            $label = $type === 'RR' ? 'Total Return' : 'Total Qty';
            return response()->json([
                'success' => true,
                'message' => "Receipt {$receiptnbr} updated. {$label}: {$totalQtyEdit}",
            ]);
        }, 3);
    }


    public function approveReceipt_xxx(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'GR';

        $receipt = TrReceipt::with('creator')->where('receiptnbr', $docid)->first();
        if (!$receipt) return response()->json(['success'=>false,'message'=>'Receipt not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($receipt->id);
        // (opsional) samakan route show jika pakai singular:
        $docUrl   = url('/showreceipt/' . $eid);
        $fullname = data_get($receipt, 'creator.name') ?: $receipt->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $receipt->receiptnbr,
            $doctype,
            $user->username,
            $user->name,

            // ==== COMPLETE CALLBACK: dokumen finish (C) ====
            function (string $refnbr, \Carbon\Carbon $now) use ($receipt, $fullname, $docUrl, $request) {
                 // 3) >>>> UPDATE PO setelah receipt COMPLETE <<<<
                try {
                    // Panggil fungsi updatePO (id = id TrReceipt)
                    app(static::class)->updatePO($request, $receipt->id);
                } catch (\Throwable $e) {
                    \Log::error('approveReceipt: updatePO failed', [
                        'receiptnbr' => $receipt->receiptnbr,
                        'id'         => $receipt->id,
                        'error'      => $e->getMessage(),
                    ]);
                    // Tidak melempar ulang agar approval tetap sukses
                }
                
                // 1) Update header & detail ke C
                $receipt->status       = 'C';
                $receipt->completed_by = $receipt->completed_by ?: (auth()->user()->username ?? $receipt->completed_by);
                $receipt->completed_at = $now;
                $receipt->save();

                TrReceiptdetail::where('receiptnbr', $receipt->receiptnbr)->update(['status' => 'C']);

                // 2) Notifikasi ke requester (creator)
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $receipt->receiptnbr,
                    'Receipt',
                    'C',
                    $receipt->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $receipt->cpny_id ?? $receipt->cpnyid ?? '',
                        'deptname' => $receipt->department_id ?? $receipt->departementid ?? '',
                        'date'     => $receipt->receiptdate,
                        'info'     => $receipt->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,
                    ]
                );

               
            },

            // ==== NEXT APPROVER CALLBACK: lanjut ke approver berikutnya ====
            function ($next, \Carbon\Carbon $now) use ($receipt, $docUrl) {
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $receipt->receiptnbr,
                    'GR',
                    'P',
                    'Receipt',
                    $docUrl,
                    [
                        'info'      => $receipt->keperluan,
                        'createdby' => $receipt->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (opsional)
                $receipt->completed_by = auth()->user()->username ?? $receipt->completed_by;
                $receipt->completed_at = $now;
                $receipt->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Task approved successfully']);
    }

    public function approveReceipt(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'GR';

        $receipt = TrReceipt::with('creator')->where('receiptnbr', $docid)->first();
        if (!$receipt) return response()->json(['success'=>false,'message'=>'Receipt not found'],404);

        // ================== VALIDASI: BLOCK APPROVE JIKA PO SUDAH FULL RECEIVED ==================
        // $ponbr = $receipt->ponbr ?? null;

        // if ($ponbr) {
        //     $totalLines = TrPOdetail::where('ponbr', $ponbr)->count();

        //     if ($totalLines > 0) {
        //         $fullLines = TrPOdetail::where('ponbr', $ponbr)
        //             ->whereRaw('COALESCE(qty_received, 0) >= COALESCE(qty, 0)')
        //             ->count();

        //         if ($fullLines >= $totalLines) {
        //             return response()->json([
        //                 'success' => false,
        //                 'code'    => 'PO_ALREADY_FULLY_RECEIVED',
        //                 'message' => "Tidak bisa approve karena PO {$ponbr} sudah di-receipt (qty received sudah sama). Silahkan Reject transaksi ini."
        //             ], 422);
        //         }
        //     }
        // }
        // =================================================================================================

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($receipt->id);
        $docUrl   = url('/showreceipt/' . $eid);
        $fullname = data_get($receipt, 'creator.name') ?: $receipt->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $receipt->receiptnbr,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($receipt, $fullname, $docUrl, $request) {
                // try {
                //     app(static::class)->updatePO($request, $receipt->id);
                // } catch (\Throwable $e) {
                //     \Log::error('approveReceipt: updatePO failed', [
                //         'receiptnbr' => $receipt->receiptnbr,
                //         'id'         => $receipt->id,
                //         'error'      => $e->getMessage(),
                //     ]);
                // }

                $receipt->status       = 'C';
                $receipt->completed_by = $receipt->completed_by ?: (auth()->user()->username ?? $receipt->completed_by);
                $receipt->completed_at = $now;
                $receipt->save();

                TrReceiptdetail::where('receiptnbr', $receipt->receiptnbr)->update(['status' => 'C']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $receipt->receiptnbr,
                    'Receipt',
                    'C',
                    $receipt->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $receipt->cpny_id ?? $receipt->cpnyid ?? '',
                        'deptname' => $receipt->department_id ?? $receipt->departementid ?? '',
                        'date'     => $receipt->receiptdate,
                        'info'     => $receipt->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,
                    ]
                );
            },

            function ($next, \Carbon\Carbon $now) use ($receipt, $docUrl) {
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $receipt->receiptnbr,
                    'GR',
                    'P',
                    'Receipt',
                    $docUrl,
                    [
                        'info'      => $receipt->keperluan,
                        'createdby' => $receipt->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                $receipt->completed_by = auth()->user()->username ?? $receipt->completed_by;
                $receipt->completed_at = $now;
                $receipt->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Task approved successfully']);
    }

    public function rejectReceipt(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'GR';

        $receipt = \App\Models\TrReceipt::with('creator')->where('receiptnbr', $docid)->first();
        if (!$receipt) return response()->json(['success'=>false,'message'=>'Receipt not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($receipt->id);
        $docUrl   = url('/showreceipts/' . $eid);
        $fullname = data_get($receipt, 'creator.name') ?: $receipt->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $receipt->receiptnbr,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($receipt, $fullname, $docUrl, $request) {

                // ✅ rollback PO sesuai receipttype PR/RR + set receipt/detail status R
                $this->rollbackReceiptAndPo((int)$receipt->id, auth()->user()->username ?? 'system', 'R');

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $receipt->receiptnbr,
                    'Receipt',
                    'R',
                    $receipt->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $receipt->cpny_id ?? $receipt->cpnyid ?? '',
                        'deptname' => $receipt->department_id ?? $receipt->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $receipt->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,
                    ]
                );

                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($receipt->id, 'GR', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Receipt rejected successfully']);
    }

    public function reviseReceipt(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'GR';

        $receipt = \App\Models\TrReceipt::with('creator')->where('receiptnbr', $docid)->first();
        if (!$receipt) return response()->json(['success'=>false,'message'=>'Receipt not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($receipt->id);
        $docUrl   = url('/showreceipts/' . $eid);
        $fullname = data_get($receipt, 'creator.name') ?: $receipt->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $receipt->receiptnbr,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($receipt, $fullname, $docUrl, $request) {

                // ✅ rollback PO sesuai receipttype PR/RR + set receipt/detail status D
                $this->rollbackReceiptAndPo((int)$receipt->id, auth()->user()->username ?? 'system', 'D');

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $receipt->receiptnbr,
                    'Receipt',
                    'D',
                    $receipt->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $receipt->cpny_id ?? $receipt->cpnyid ?? '',
                        'deptname' => $receipt->department_id ?? $receipt->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $receipt->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,
                    ]
                );

                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($receipt->id, 'GR', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Revise failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Receipt revised successfully']);
    }



    public function rejectReceipt_xxx(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'GR';

        $receipt = \App\Models\TrReceipt::with('creator')->where('receiptnbr', $docid)->first();
        if (!$receipt) return response()->json(['success'=>false,'message'=>'Receipt not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($receipt->id);
        $docUrl   = url('/showreceipts/' . $eid);
        $fullname = data_get($receipt, 'creator.name') ?: $receipt->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $receipt->receiptnbr,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($receipt, $fullname, $docUrl) {
                $receipt->status       = 'R';
                $receipt->completed_by = auth()->user()->username;
                $receipt->completed_at = $now;
                $receipt->save();

                // optional: tandai detail R
                // \App\Models\TrReceiptdetail::where('receiptnbr', $receipt->receiptnbr)->update(['status' => 'R']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $receipt->receiptnbr,
                    'Receipt',
                    'R',
                    $receipt->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $receipt->cpny_id ?? $receipt->cpnyid ?? '',
                        'deptname' => $receipt->department_id ?? $receipt->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $receipt->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($receipt->id, 'GR', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Receipt rejected successfully']);
    }

    public function reviseReceipt_xxx(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'GR';

        $receipt = \App\Models\TrReceipt::with('creator')->where('receiptnbr', $docid)->first();
        if (!$receipt) return response()->json(['success'=>false,'message'=>'Receipt not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($receipt->id);
        $docUrl   = url('/showreceipts/' . $eid);
        $fullname = data_get($receipt, 'creator.name') ?: $receipt->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $receipt->receiptnbr,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($receipt, $fullname, $docUrl) {
                // === HEADER Receipt -> D ===
                $receipt->status       = 'D';
                $receipt->completed_by = auth()->user()->username;
                $receipt->completed_at = $now;
                $receipt->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrReceiptdetail::where('receiptnbr', $receipt->receiptnbr)->update(['status' => 'D']);

                // === Email ke requester ===
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $receipt->receiptnbr,
                    'Receipt',
                    'D',
                    $receipt->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $receipt->cpny_id ?? $receipt->cpnyid ?? '',
                        'deptname' => $receipt->department_id ?? $receipt->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $receipt->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,   // <<< tambahkan ini
                    ]
                );


                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($receipt->id, 'GR', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success'=>false,
                'message'=>$result['message'] ?? 'Revise failed'
            ], 403);
        }

        return response()->json(['success'=>true,'message'=>'Receipt revised successfully']);
    }

       

    public function uploadAttachments(Request $request, $poid)
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
            $attachment = TrAttachment::findOrFail($id);
            $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }
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

    public function updatePO(Request $request, $id)
    {
        try {
            $user  = $request->user();
            $uname = $user->username ?? 'system';

            $result = $this->finalizeReceiptAndPo((int)$id, $uname);

            return response()->json([
                'ok'      => true,
                'message' => $result['type'] === 'PR'
                    ? 'Receipt (penerimaan) berhasil di-approve & PO diperbarui.'
                    : 'Receipt (return) berhasil di-approve & qty_return PO diperbarui.',
                'data'    => $result
            ]);
        } catch (\Throwable $e) {
            \Log::error('updatePO failed', ['error' => $e->getMessage()]);
            return response()->json([
                'ok' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Gagal memproses update PO'
            ], 422);
        }
    }


    private function finalizeReceiptAndPo(int $receiptId, string $uname): array
    {
        
        return DB::connection('pgsql')->transaction(function () use ($receiptId, $uname) {
            $now = \Carbon\Carbon::now();

            // Lock header
            $rcp = TrReceipt::where('id', $receiptId)->lockForUpdate()->firstOrFail();

            if (!in_array($rcp->status, ['P','C'], true)) {
                throw new \RuntimeException('Receipt tidak dalam status yang dapat difinalisasi.');
            }

            // Ambil semua detail receipt
            $rcpdetails = TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
                ->orderBy('receipt_no')->get();

            if ($rcpdetails->isEmpty()) {
                throw new \RuntimeException('Tidak ada detail receipt untuk diproses.');
            }

            // Lock PO
            $po = TrPO::where('ponbr', $rcp->ponbr)->lockForUpdate()->first();
            if (!$po) {
                throw new \RuntimeException('PO terkait tidak ditemukan.');
            }

            // Siapkan map detail PO
            $poDetailRows  = TrPOdetail::where('ponbr', $rcp->ponbr)->get();
            $poByKey       = $poDetailRows->keyBy(fn($row) => ($row->inventoryid ?? '').'|'.($row->uom ?? ''));
            $poByInventory = $poDetailRows->groupBy('inventoryid');

            $totalQtyReceivedThisReceipt = 0.0;
            $totalQtyReturnThisReceipt   = 0.0;

            if ($rcp->receipttype === 'PR') {
                // ===== FINALIZE penerimaan =====
                foreach ($rcpdetails as $rd) {
                    $key   = ($rd->inventoryid ?? '').'|'.($rd->uom ?? '');
                    $poDet = $poByKey->get($key) ?: optional($poByInventory->get($rd->inventoryid))->first();
                    if (!$poDet) continue;

                    $qtyRec     = (float) ($rd->qty_received ?? 0);
                    $baseQtyRec = (float) ($rd->base_qty_received ?? 0);

                    $poDet->qty_received      = (float) ($poDet->qty_received ?? 0) + $qtyRec;
                    $poDet->base_qty_received = (float) ($poDet->base_qty_received ?? 0) + $baseQtyRec;

                    $ordered = (float) ($poDet->qty ?? 0);
                    if ($ordered > 0 && $poDet->qty_received >= $ordered) {
                        $poDet->received  = true;
                        $poDet->completed = true;
                        $poDet->status    = 'C';
                    } else {
                        $poDet->received  = true;
                        $poDet->completed = false;
                        $poDet->status    = 'P';
                    }

                    $poDet->updated_by = $uname;
                    $poDet->save();

                    $totalQtyReceivedThisReceipt += $qtyRec;
                }

                // akumulasi header PO
                $po->totalqtyreceived = TrPOdetail::where('ponbr', $rcp->ponbr)->sum('qty_received');

                // close PO bila semua completed
                $openExists = TrPOdetail::where('ponbr', $rcp->ponbr)
                    ->whereRaw('(COALESCE(qty,0) > COALESCE(qty_received,0))')
                    ->exists();

                if (!$openExists) {
                    $po->status       = 'C';
                    $po->completed_by = $uname;
                    $po->completed_at = $now;
                }
                $po->updated_by = $uname;
                $po->save();

                // update header & detail receipt ke Completed
                $rcp->status            = 'P';
                $rcp->totalqty_received = $totalQtyReceivedThisReceipt;
                $rcp->completed_by      = $uname;
                $rcp->completed_at      = $now;
                $rcp->updated_by        = $uname;
                $rcp->save();

                TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
                    ->update(['status' => 'P']);

                return [
                    'type' => 'PR',
                    'receiptnbr' => $rcp->receiptnbr,
                    'ponbr' => $rcp->ponbr,
                    'po_totalqtyreceived' => (float) $po->totalqtyreceived,
                ];
            }

            if ($rcp->receipttype === 'RR') {
                // ===== FINALIZE return =====
                foreach ($rcpdetails as $rd) {
                    $key   = ($rd->inventoryid ?? '').'|'.($rd->uom ?? '');
                    $poDet = $poByKey->get($key) ?: optional($poByInventory->get($rd->inventoryid))->first();
                    if (!$poDet) continue;

                    $qtyRet     = (float) ($rd->qty_return ?? 0);
                    $baseQtyRet = (float) ($rd->base_qty_return ?? 0);

                    $poDet->qty_return      = (float) ($poDet->qty_return ?? 0) + $qtyRet;
                    $poDet->base_qty_return = (float) ($poDet->base_qty_return ?? 0) + $baseQtyRet;

                    $poDet->updated_by = $uname;
                    $poDet->save();

                    $totalQtyReturnThisReceipt += $qtyRet;
                }

                $po->updated_by = $uname;
                $po->save();

                $rcp->status       = 'P';
                if (\Schema::hasColumn($rcp->getTable(), 'totalqty_return')) {
                    $rcp->totalqty_return = $totalQtyReturnThisReceipt;
                }
                $rcp->completed_by = $uname;
                $rcp->completed_at = $now;
                $rcp->updated_by   = $uname;
                $rcp->save();

                TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
                    ->update(['status' => 'P']);

                return [
                    'type' => 'return',
                    'receiptnbr' => $rcp->receiptnbr,
                    'ponbr' => $rcp->ponbr,
                ];
            }

            throw new \RuntimeException('Tipe receipt tidak dikenali.');
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
                ->where('receipttype', 'RR')
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
        // $user = $request->user();
        $user = Auth::user();
        $username = $user ? $user->username : 'system';
        // $username = $user->username ?? 'system';

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

        // $deptid = $src->department_id;
        $deptid  = 'WAREHOUSE';
        $cpnyid = $src->cpny_id;

        $doctype = 'GR';
        $now   = Carbon::now();
        $year  = (int)$now->year;
        $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);

        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyid, $deptid);


        DB::beginTransaction();
        try {
            
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
            $hdr->receipttype       = 'RR'; // <— sesuai filter returnjobs (status C + receipttype='receipt')
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
                // $det->qty_open_ordered        = 0;
                // $det->base_qty_open_ordered   = 0;

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
           

            $ctx = ['ignore_nominal' => true];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $receiptnbr,
                $doctype,
                $cpnyid,
                $deptid,
                $username,
                $ctx,
                $now
            );

            if ($firstApprovalUsernames) {
                $hdr->completed_by = $firstApprovalUsernames;
                $hdr->completed_at = $now; // <-- ganti $dt jadi $now
                $hdr->save();
            }   
           
            
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
                        'message' => 'Failed to create Return',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

            // Notifikasi approver pertama
            $eid = \Vinkla\Hashids\Facades\Hashids::encode($hdr->id);
            $approvalCtl->notifyFirstApprover(
                $receiptnbr,
                $doctype,
                $hdr->status, // 'P'
                'Return',
                url('/showreceipt/' . $eid),
                [
                    'info'      => 'Request from user ' . ($po->user_peminta ?? '-'),
                    'createdby' => $hdr->created_by,
                    'date'      => $now->toDateTimeString(),
                ]
            );

            DB::commit();

            return redirect()->route('receiptlist')
                ->with('success', "Return {$receiptnbr} created from {$src->receiptnbr}. Total Qty Return: {$totalReturn}");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors([config('app.debug') ? $e->getMessage() : 'Failed to create Return'])->withInput();
        }
    }

    public function validateApprove(string $receiptnbr)
    {
        // Ambil receipt untuk dapat ponbr
        $rcp = TrReceipt::where('receiptnbr', $receiptnbr)->first();

        if (!$rcp) {
            return response()->json([
                'ok' => false,
                'message' => 'Receipt not found.'
            ], 404);
        }

        if (empty($rcp->ponbr)) {
            return response()->json([
                'ok' => false,
                'message' => 'PO Nbr is empty on this receipt.'
            ], 422);
        }

        $ponbr = $rcp->ponbr;

        // total line PO
        $totalLines = TrPOdetail::where('ponbr', $ponbr)->count();

        if ($totalLines <= 0) {
            return response()->json([
                'ok' => false,
                'message' => 'PO detail not found for this PO.'
            ], 422);
        }

        // line yang sudah full received (qty_received >= qty)
        $fullLines = TrPOdetail::where('ponbr', $ponbr)
            ->whereRaw('COALESCE(qty_received, 0) >= COALESCE(qty, 0)')
            ->count();

        // jika semua line sudah full received → block approve
        if ($fullLines >= $totalLines) {
            return response()->json([
                'ok' => false,
                'code' => 'PO_ALREADY_FULLY_RECEIVED',
                'message' => "Tidak bisa approve. Semua item pada PO {$ponbr} sudah di-receipt (fully received). Silahkan Reject transaksi ini."
            ], 200);
        }

        return response()->json([
            'ok' => true,
            'message' => 'OK to approve.'
        ], 200);
    }

   
    private function rollbackReceiptAndPo(int $receiptId, string $uname, string $targetStatus): array
    {
        return DB::connection('pgsql')->transaction(function () use ($receiptId, $uname, $targetStatus) {
            $now = \Carbon\Carbon::now();

            // Lock receipt header
            /** @var \App\Models\TrReceipt $rcp */
            $rcp = \App\Models\TrReceipt::where('id', $receiptId)->lockForUpdate()->firstOrFail();

            // Ambil detail receipt
            $rcpdetails = \App\Models\TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
                ->orderBy('receipt_no')
                ->get();

            if ($rcpdetails->isEmpty()) {
                throw new \RuntimeException('Tidak ada detail receipt untuk diproses rollback.');
            }

            // Lock PO header
            $po = \App\Models\TrPO::where('ponbr', $rcp->ponbr)->lockForUpdate()->first();
            if (!$po) {
                throw new \RuntimeException('PO terkait tidak ditemukan.');
            }

            // Ambil PO detail & siapkan map
            $poDetailRows  = \App\Models\TrPOdetail::where('ponbr', $rcp->ponbr)->lockForUpdate()->get();
            $poByKey       = $poDetailRows->keyBy(fn($row) => ($row->inventoryid ?? '').'|'.($row->uom ?? ''));
            $poByInventory = $poDetailRows->groupBy('inventoryid');

            $rolledBack = 0;

            // ========= ROLLBACK PR (Penerimaan) =========
            if ($rcp->receipttype === 'PR') {
                foreach ($rcpdetails as $rd) {
                    $key   = ($rd->inventoryid ?? '').'|'.($rd->uom ?? '');
                    $poDet = $poByKey->get($key) ?: optional($poByInventory->get($rd->inventoryid))->first();
                    if (!$poDet) continue;

                    $qtyRec     = (float) ($rd->qty_received ?? 0);
                    $baseQtyRec = (float) ($rd->base_qty_received ?? 0);

                    // rollback: kurangi qty_received
                    $poDet->qty_received      = max(((float)($poDet->qty_received ?? 0)) - $qtyRec, 0);
                    $poDet->base_qty_received = max(((float)($poDet->base_qty_received ?? 0)) - $baseQtyRec, 0);

                    // recompute line flags/status
                    $ordered = (float) ($poDet->qty ?? 0);
                    if ($ordered > 0 && $poDet->qty_received >= $ordered) {
                        $poDet->received  = true;
                        $poDet->completed = true;
                        $poDet->status    = 'C';
                    } else {
                        $poDet->received  = ($poDet->qty_received > 0);
                        $poDet->completed = false;
                        $poDet->status    = 'P';
                    }

                    $poDet->updated_by = $uname;
                    $poDet->updated_at = $now;
                    $poDet->save();

                    $rolledBack++;
                }

                // recompute PO header fields
                $po->totalqtyreceived = (float) \App\Models\TrPOdetail::where('ponbr', $rcp->ponbr)->sum('qty_received');

                $openExists = \App\Models\TrPOdetail::where('ponbr', $rcp->ponbr)
                    ->whereRaw('(COALESCE(qty,0) > COALESCE(qty_received,0))')
                    ->exists();

                if ($openExists) {
                    $po->status = 'P';
                    $po->completed_by = null;
                    $po->completed_at = null;
                } else {
                    $po->status = 'C';
                    $po->completed_by = $po->completed_by ?: $uname;
                    $po->completed_at = $po->completed_at ?: $now;
                }

                $po->updated_by = $uname;
                $po->updated_at = $now;
                $po->save();
            }

            // ========= ROLLBACK RR (Return) =========
            else if ($rcp->receipttype === 'RR') {
                foreach ($rcpdetails as $rd) {
                    $key   = ($rd->inventoryid ?? '').'|'.($rd->uom ?? '');
                    $poDet = $poByKey->get($key) ?: optional($poByInventory->get($rd->inventoryid))->first();
                    if (!$poDet) continue;

                    $qtyRet     = (float) ($rd->qty_return ?? 0);
                    $baseQtyRet = (float) ($rd->base_qty_return ?? 0);

                    // rollback: kurangi qty_return
                    $poDet->qty_return      = max(((float)($poDet->qty_return ?? 0)) - $qtyRet, 0);
                    $poDet->base_qty_return = max(((float)($poDet->base_qty_return ?? 0)) - $baseQtyRet, 0);

                    $poDet->updated_by = $uname;
                    $poDet->updated_at = $now;
                    $poDet->save();

                    $rolledBack++;
                }

                $po->updated_by = $uname;
                $po->updated_at = $now;
                $po->save();
            } else {
                throw new \RuntimeException('Tipe receipt tidak dikenali untuk rollback.');
            }

            // Update receipt header + detail status (R atau D)
            $rcp->status       = $targetStatus; // 'R' reject, 'D' revise
            $rcp->updated_by   = $uname;
            $rcp->updated_at   = $now;
            $rcp->completed_by = $uname;
            $rcp->completed_at = $now;
            $rcp->save();

            \App\Models\TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
                ->update([
                    'status'     => $targetStatus,
                    'updated_by' => $uname,
                    'updated_at' => $now,
                ]);

            return [
                'receiptnbr'   => $rcp->receiptnbr,
                'receipttype'  => $rcp->receipttype,
                'ponbr'        => $rcp->ponbr,
                'rolled_back_lines' => $rolledBack,
                'target_status' => $targetStatus,
            ];
        });
    }



    


}
