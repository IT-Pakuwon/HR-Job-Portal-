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
use App\Http\Controllers\TrAttachmentController;
use App\Models\TrAttachment;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;

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
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $ponbr = trim((string)$request->input('ponbr', ''));
        if ($ponbr === '') {
            return back()->withErrors(['PO number not found.'])->withInput();
        }

        // 1) Ambil PO header (pastikan nama model benar)
        $po = TrPO::where('ponbr', $ponbr)->first();
        if (!$po) {
            return back()->withErrors(['PO not found.'])->withInput();
        }

        // 2) Ambil detail PO (pastikan nama model benar)
        $poDetails = TrPOdetail::where('ponbr', $ponbr)->get()->keyBy('id');

        // 3) Validasi minimal ada qty receipt
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

        // 4) Approval line check (doctype GR)
        $doctype = 'GR'; // konsisten
        $cpnyid  = $po->cpny_id ?? ($request->input('cpnyid') ?? null);
        $deptid  = $po->department_id ?? ($request->input('departmentid') ?? null);

        $approvalCount = M_approval::where([
            ['status', '=', 'A'],
            ['aprvcpnyid', '=', $cpnyid],
            ['aprvdeptid', '=', $deptid],
            ['aprvdoctype', '=', $doctype],
        ])->count();

        if ($approvalCount === 0) {
            return response()->json(['message' => 'Approval line belum di-setup, Please contact IT!'], 422);
        }

        DB::beginTransaction();
        try {
            // 5) Autonumber GRYYMM####
            $now   = \Carbon\Carbon::now();
            $year  = (int) $now->year;
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

            $yymm       = substr((string)$year, 2) . $month;
            $receiptnbr = $doctype . $yymm . sprintf('%04d', $urut);

            // 6) Header TrReceipt (receipttype konsisten)
            $header = new TrReceipt();
            $header->receiptnbr        = $receiptnbr;
            $header->receiptdate       = $now->toDateString();
            $header->receipttype       = 'PR'; // <— PENTING: konsisten untuk approve
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

            // 7) Detail
            $lineNo = 0;
            $totalQtyReceived = 0.0;

            foreach ($poDetails as $srcId => $src) {
                $qtyRecRaw = $qtyReceiptInput[$srcId] ?? 0;
                $qtyRec    = (float) str_replace(',', '.', (string)$qtyRecRaw);
                if ($qtyRec <= 0) continue;

                $siteFromForm = isset($siteInput[$srcId]) ? trim((string)$siteInput[$srcId]) : null;

                $lineNo++;

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
                $det->qtyordered              = (float)($src->qty ?? 0);
                $det->uom                     = $src->uom;

                // Base
                $det->type_multiplier         = null;
                $det->base_multiplier         = 1;
                $det->base_qty                = $qtyRec;
                $det->base_uom                = $src->uom;

                // Harga / pajak → ambil dari PO detail (kalau memang mau dicopy)
                $det->unitcost                = $src->unitcost ?? 0;
                $det->taxcodeid               = $src->taxcodeid ?? null; // <— perbaikan
                $det->taxamt                  = $src->taxamt ?? 0;
                $det->totalcost               = $src->totalcost ?? 0;

                $det->receipttype             = 'PR'; // <— konsisten
                $det->siteid                  = $siteFromForm !== '' ? $siteFromForm : ($src->siteid ?? null);

                // Optional: kalau mau simpan sisa open order, hitung di sisi create
                $det->qty_open_ordered        = $qtyRec;          // atau null, tergantung desain
                $det->base_qty_open_ordered   = $qtyRec;

                $det->qty_received            = $qtyRec;
                $det->base_qty_received       = $qtyRec;

                $det->qty_return              = 0;
                $det->base_qty_return         = 0;

                $det->ref_receiptnbr          = null;

                // Budget fields
                $det->budget_perpost          = $src->budget_perpost ?? null;
                $det->budget_cpny_id          = $src->budget_cpny_id ?? null;
                $det->budget_business_unit_id = $src->budget_business_unit_id ?? null;
                $det->budget_department_fin_id= $src->budget_department_fin_id ?? null;
                $det->budget_account_id       = $src->budget_account_id ?? null;
                $det->budget_activity_id      = $src->budget_activity_id ?? null;
                $det->budget_activity_descr   = $src->budget_activity_descr ?? null;

                $det->status                  = 'P';
                $det->created_by              = $username;
                $det->created_at              = $now;
                $det->save();

                $totalQtyReceived += $qtyRec;
            }

            if ($totalQtyReceived <= 0) {
                DB::rollBack();
                return back()->withErrors(['Qty receipt minimal satu baris harus > 0.'])->withInput();
            }

            $header->totalqty_received = $totalQtyReceived;
            $header->save();

            // 8) Copy line approval (pakai cpnyid & deptid yang sudah dihitung)
            $datestamp = $now->toDateTimeString(); // <— definisikan
            $approvals = M_approval::where([
                ['status', '=', 'A'],
                ['aprvcpnyid', '=', $cpnyid],
                ['aprvdeptid', '=', $deptid],
                ['aprvdoctype', '=', $doctype],
            ])->get();

            foreach ($approvals as $a) {
                T_approval::create([
                    'docid'          => $receiptnbr,
                    'aprvid'         => $a->aprvid,
                    'aprvdoctype'    => $a->aprvdoctype,
                    'aprvcpnyid'     => $a->aprvcpnyid,
                    'aprvdeptid'     => $a->aprvdeptid,
                    'aprvusername'   => $a->aprvusername,
                    'name'           => $a->name,
                    'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
                    'aprvtotalday'   => 1,
                    'status'         => 'P',
                    'created_by'     => $username,
                ]);
            }

            // 9) Attachments (rapikan meta & error flow)
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'       => $receiptnbr,
                    'doctype'      => $doctype,                          // GR
                    'cpnyid'       => $cpnyid,
                    'departmentid' => $deptid,                           // <— perbaiki ejaan key
                    'base_folder'  => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'   => $username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(\App\Http\Controllers\TrAttachmentController::class);
                    $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    throw $e; // biar ketangkap catch luar → rollback & redirect
                }
            }

            // 10) Notif approver pertama (opsional)
            $firstApproval = T_approval::where('docid', $receiptnbr)
                ->where('status', 'P')->orderBy('aprvid')->first();

            if ($firstApproval) {
                $status     = $header->status;
                $subjectMap = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];
                $eid        = \Vinkla\Hashids\Facades\Hashids::encode($header->id);

                $data = [
                    'docid'    => $firstApproval->docid,
                    'cpnyid'   => $firstApproval->aprvcpnyid,
                    'deptname' => $firstApproval->aprvdeptid,
                    'date'     => $firstApproval->aprvdatebefore,
                    'name'     => $firstApproval->name,
                    'createdby'=> $header->created_by,
                    'info'     => 'Request from user '.$po->user_peminta,
                    'status'   => $status,
                    'docname'  => 'Receipt',
                    'url'      => url('/showreceipt/' . $eid),
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                            ->where('status', 'A')->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectMap, $status) {
                        $message->to($email)
                            ->subject($data['docid'].' - '.($subjectMap[$status] ?? 'Notification').' Receipt')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();

            return redirect()
                ->route('receiptlist') // <— samakan
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
        $now      = Carbon::now();
        $user     = $request->user();
        $username = $user->username ?? 'system';

        // ===== Temukan Receipt by hash (id) atau fallback ke receiptnbr =====
        $ids = Hashids::decode($hash);
        if (!empty($ids)) {           
            $rcp = TrReceipt::find($ids[0]);
        } else {
            // fallback: jika {hash} ternyata receiptnbr
            $rcp = TrReceipt::where('receiptnbr', $hash)->first();
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
       
        $po = TrPo::where('ponbr', $ponbr)->first();
        if (!$po) {
            return response()->json(['message' => 'PO header not found'], 422);
        }

        // Ambil semua detail PO → untuk matching by (inventoryid|uom)
        $poDetails    = TrPOdetail::where('ponbr', $ponbr)->get();
        $poByKey      = $poDetails->keyBy(fn($row) => ($row->inventoryid ?? '').'|'.($row->uom ?? ''));
        $poByInventory= $poDetails->groupBy('inventoryid');

        // ===== Input arrays =====
        // Form mengirim salah satu:
        // - receipttype 'receipt' → qty_received[detail_id]
        // - receipttype 'return'  → qty_return[detail_id]
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
        $detailRows = TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)->get()->keyBy('id');

        // ===== Validasi per-baris terhadap PO open =====
        // RULE:
        //  - tipe 'receipt' : qty_received_new <= openPO + old_qty_received_baris_ini
        //  - tipe 'return'  : qty_return_new   <= (old_qty_return_baris_ini + qty_received_total_yang_boleh_diretur_di_skenario_mu)
        //    (untuk sederhana & aman di fase edit, kita batasi: qty_return_new <= old_qty_return + base on-hand receipt baris ini)
        //    Jika kamu punya kolom akumulasi "maks retur" per item, ganti logika ceiling ini sesuai kebijakanmu.
        foreach ($payload as $detailId => $v) {          
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
                $oldRet   = (float)($det->qty_return ?? 0);
                $oldRec   = (float)($det->qty_received ?? 0);
                $ceiling  = $oldRet + $oldRec; // longgar; ganti jika punya aturan khusus

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
        $deptid  = $rcp->department_id ?? $rcp->departementid ?? null;

        $approvalCount = M_approval::where([
            ['status',      '=', 'A'],
            ['aprvcpnyid',  '=', $cpnyid],
            ['aprvdeptid',  '=', $deptid],
            ['aprvdoctype', '=', $doctype],
        ])->count();

        if ($approvalCount === 0) {
            return response()->json(['message' => 'Approval line belum di-setup, hubungi IT.'], 422);
        }

        // ===== EXEC UPDATE DALAM TRANSAKSI =====
        return DB::connection('pgsql')->transaction(function () use (
            $request, $now, $rcp, $detailRows, $qtyReceivedInput, $qtyReturnInput, $siteInput, $doctype, $cpnyid, $deptid, $username, $type
        ) {
            $receiptnbr = $rcp->receiptnbr;

            $totalQtyEdit = 0.0;

            foreach ($detailRows as $detId => $det) {
                // ambil nilai baru jika dikirim; kalau tidak, pakai qty lama
                if ($type === 'PR') {
                    $qtyNew = array_key_exists($detId, $qtyReceivedInput)
                        ? (float) str_replace(',', '.', (string)$qtyReceivedInput[$detId])
                        : (float) ($det->qty_received ?? 0);

                    if ($qtyNew < 0) $qtyNew = 0;

                    $det->qty_received      = $qtyNew;
                    $det->base_qty_received = $qtyNew; // jika base = 1:1; adjust jika ada konversi
                    // open ordered di baris ini (opsional untuk tampilan)
                    $det->qty_open_ordered      = $qtyNew;
                    $det->base_qty_open_ordered = $qtyNew;

                } else { // return
                    $qtyNew = array_key_exists($detId, $qtyReturnInput)
                        ? (float) str_replace(',', '.', (string)$qtyReturnInput[$detId])
                        : (float) ($det->qty_return ?? 0);

                    if ($qtyNew < 0) $qtyNew = 0;

                    $det->qty_return      = $qtyNew;
                    $det->base_qty_return = $qtyNew; // jika base = 1:1
                }

                // site baru (opsional)
                if (array_key_exists($detId, $siteInput)) {
                    $newSite = trim((string)$siteInput[$detId]);
                    $det->siteid = $newSite !== '' ? $newSite : $det->siteid;
                }

                // status detail kembali ke P (menunggu approve setelah revise)
                $det->status     = 'P';
                $det->updated_by = $username;
                $det->updated_at = $now;
                $det->save();

                $totalQtyEdit += (float)$qtyNew;
            }

            // Update header: total, status kembali ke P, clear completed
            if ($type === 'PR') {
                $rcp->totalqty_received = $totalQtyEdit;
            } else {
                if (\Schema::hasColumn($rcp->getTable(), 'totalqty_return')) {
                    $rcp->totalqty_return = $totalQtyEdit;
                }
            }

            $rcp->status        = 'P';     // kembali ke Waiting Approval setelah revise
            $rcp->completed_by  = null;
            $rcp->completed_at  = null;
            $rcp->updated_by    = $username;
            $rcp->updated_at    = $now;
            $rcp->save();

            // Reset approval → insert fresh approval lines
            $datestamp = $now->toDateTimeString();

            // (Opsional) bersihkan approval lama pending untuk doc ini
            T_approval::where('docid', $receiptnbr)->delete();

            $approvals = M_approval::where([
                ['status',      '=', 'A'],
                ['aprvcpnyid',  '=', $cpnyid],
                ['aprvdeptid',  '=', $deptid],
                ['aprvdoctype', '=', $doctype],
            ])->orderBy('aprvid')->get();

            foreach ($approvals as $a) {
                T_approval::create([
                    'docid'          => $receiptnbr,
                    'aprvid'         => $a->aprvid,
                    'aprvdoctype'    => $a->aprvdoctype,
                    'aprvcpnyid'     => $a->aprvcpnyid,
                    'aprvdeptid'     => $a->aprvdeptid,
                    'aprvusername'   => $a->aprvusername,
                    'name'           => $a->name,
                    'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
                    'aprvtotalday'   => 1,
                    'status'         => 'P',
                    'created_by'     => $username,
                ]);
            }

            // Attachment baru (opsional)
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $receiptnbr,
                    'doctype'       => $doctype,
                    'cpnyid'        => $rcp->cpny_id ?? null,
                    'departementid' => $rcp->department_id ?? null,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $username,
                ];
                $files    = (array) $request->file('attachments');
                $uploader = app(\App\Http\Controllers\TrAttachmentController::class);
                $uploader->uploadInternal($meta, $files);
            }

            // Email ke approver pertama
            $firstApproval = T_approval::where('docid', $receiptnbr)
                ->where('status', 'P')->orderBy('aprvid')->first();

            if ($firstApproval) {
                $status     = $rcp->status; // 'P'
                $subjectMap = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];
                $eid        = Hashids::encode($rcp->id);

                $data = [
                    'docid'     => $firstApproval->docid,
                    'cpnyid'    => $firstApproval->aprvcpnyid,
                    'deptname'  => $firstApproval->aprvdeptid,
                    'date'      => $firstApproval->aprvdatebefore,
                    'name'      => $firstApproval->name,
                    'createdby' => $rcp->created_by,
                    'info'      => 'Revised by user '.$username,
                    'status'    => $status,
                    'docname'   => 'Receipt',
                    'url'       => url('/showreceipt/' . $eid),
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails    = User::whereIn('username', $approvers)->where('status', 'A')->pluck('test_email');

                foreach ($emails as $email) {
                    try {
                        \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectMap, $status) {
                            $message->to($email)
                                ->subject($data['docid'].' - '.($subjectMap[$status] ?? 'Notification').' '.$data['docname'])
                                ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        });
                    } catch (\Throwable $e) {
                        \Log::error('Failed sending Receipt revised waiting-approval email', ['error' => $e->getMessage()]);
                    }
                }
            }

            $label = $type === 'RR' ? 'Total Return' : 'Total Qty';
            return response()->json([
                'success' => true,
                'message' => "Receipt {$receiptnbr} updated. {$label}: {$totalQtyEdit}",
            ]);
        }, 3);
    }


    public function approveReceipt(Request $request, $docid)
    {
        $now  = \Carbon\Carbon::now();
        $user = $request->user();

        $receipt = TrReceipt::with('creator')
            ->where('receiptnbr', $docid)
            ->first();

        if (!$receipt) {
            return response()->json(['success' => false, 'message' => 'Receipt not found'], 404);
        }

        $fullname = data_get($receipt, 'creator.name') ?: $receipt->created_by;

        // pastikan user memang approver aktif
        $tApproval = T_approval::where('docid', $receipt->receiptnbr)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%{$user->username}%")
            ->whereNotNull('aprvdatebefore')
            ->orderBy('aprvid', 'ASC')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
        }

        DB::beginTransaction();
        try {
            // Set current approver -> Approved
            $tApproval->status         = 'A';
            $tApproval->aprvdateafter  = $now;
            $tApproval->aprvusername   = $user->username;
            $tApproval->name           = $user->name;
            $tApproval->save();

            // Update "last touched" info (bukan closing)
            $receipt->completed_by = $user->username;
            $receipt->completed_at = $now;
            $receipt->save();

            // Cek masih ada P?
            $pendingCount = T_approval::where('docid', $receipt->receiptnbr)
                ->where('status', 'P')
                ->count();

            $eid = \Vinkla\Hashids\Facades\Hashids::encode($receipt->id);
            $subjectMap = [
                'P' => 'Waiting Approval',
                'R' => 'Rejected Approval',
                'D' => 'Revise Approval',
                'A' => 'Approved',
                'C' => 'Completed',
            ];

            if ($pendingCount > 0) {
                // buka giliran next approver
                $next = T_approval::where('docid', $receipt->receiptnbr)
                    ->where('status', 'P')
                    ->orderBy('aprvid', 'ASC')
                    ->first();

                if ($next) {
                    $next->aprvdatebefore = $now;
                    $next->save();

                    // email next approver(s)
                    $status        = 'P';
                    $subjectSuffix = $subjectMap[$status] ?? 'Notification';

                    $data = [
                        'docid'     => $next->docid,
                        'cpnyid'    => $next->aprvcpnyid,
                        'deptname'  => $next->aprvdeptid,
                        'date'      => $next->aprvdatebefore,
                        'fullname'  => $next->name,
                        'name'      => $next->name,
                        'createdby' => $receipt->created_by,
                        'docname'   => 'Receipt',
                        'info'      => 'Request from user '.$receipt->user_peminta,
                        'status'    => $status,
                        'url'       => url('/showreceipt/' . $eid),
                    ];

                    $usernames = array_filter(array_map('trim', explode(',', (string) $next->aprvusername)));
                    if (!empty($usernames)) {
                        $recipients = User::whereIn('username', $usernames)
                            ->where('status', 'A')->get();

                        foreach ($recipients as $rcp) {
                            try {
                                \Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
                                    $to = $rcp->test_email ?? $rcp->email;
                                    $message->to($to)
                                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Receipt')
                                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                                });
                            } catch (\Throwable $e) {
                                \Log::error('Failed sending Receipt waiting-approval email', ['error' => $e->getMessage()]);
                            }
                        }
                    } else {
                        \Log::warning('Next approver has empty aprvusername list', ['docid' => $receipt->receiptnbr]);
                    }
                }

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Approved. Forwarded to next approver.']);
            }

            // ======= FINAL LEVEL: lakukan finalisasi satu pintu =======
            DB::commit(); // commit dulu approval step, lalu finalize di transaksi terpisah

            $final = $this->finalizeReceiptAndPo($receipt->id, $user->username);

            // Kirim email completion ke creator
            $status        = 'C';
            $subjectSuffix = $subjectMap[$status] ?? 'Notification';

            $data = [
                'docid'     => $receipt->receiptnbr,
                'cpnyid'    => $receipt->cpny_id ?? $receipt->cpnyid ?? '',
                'deptname'  => $receipt->department_id ?? $receipt->departementid ?? '',
                'date'      => $receipt->spbdate,
                'fullname'  => $fullname,
                'name'      => $fullname,
                'createdby' => $fullname,
                'docname'   => 'Receipt',
                'info'      => 'Request from user '.$receipt->user_peminta,
                'status'    => $status,
                'url'       => url('/showreceipt/' . $eid),
            ];

            $recipients = User::where('username', $receipt->created_by)
                ->where('status', 'A')->get();

            foreach ($recipients as $rcp) {
                try {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
                        $to = $rcp->test_email ?? $rcp->email;
                        $message->to($to)
                            ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Receipt')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                } catch (\Throwable $e) {
                    \Log::error('Failed sending Receipt completion email', ['error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Task approved and finalized',
                'data'    => $final
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Approve Receipt failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
        }
    }

    
    public function rejectReceipt(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $receipt = TrReceipt::where('receiptnbr', $docid)->first();
        $receipt = TrReceipt::with('creator')
            ->where('receiptnbr', $docid)
            ->first();
        $fullname = data_get($receipt, 'creator.name') ?: $receipt->created_by;

        if (!$receipt) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Validasi: user harus approver aktif (status P) pada dokumen ini
        $tApproval = T_approval::where('docid', $receipt->receiptnbr)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%{$user->username}%")
            ->whereNotNull('aprvdatebefore') 
            ->orderBy('aprvid', 'ASC')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
        }

        DB::beginTransaction();
        try {
            // Tandai approval saat ini sebagai Rejected
            $tApproval->status        = 'R';
            $tApproval->aprvdateafter = $now;
            $tApproval->aprvusername  = $user->username; // catat siapa yang reject
            $tApproval->name          = $user->name;
            $tApproval->save();

            // Update header Receipt
            $receipt->status       = 'R';
            $receipt->completed_by = $user->username;
            $receipt->completed_at = $now;
            $receipt->save();

            // Batalkan semua approval yang masih pending
            T_approval::where('docid', $receipt->receiptnbr)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reject Receipt failed', ['docid' => $docid, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Reject failed'], 500);
        }

        // === Kirim Email ke requester (creator) ===
        $status = 'R'; // Rejected
        $subjectMap = [
            'P' => 'Waiting Approval',
            'R' => 'Rejected Approval',
            'D' => 'Revise Approval',
            'A' => 'Approved',
            'C' => 'Completed',
        ];
        $subjectSuffix = $subjectMap[$status] ?? 'Notification';
        $eid = Hashids::encode($receipt->id);

        $data = [
            'docid'     => $receipt->receiptnbr,
            'cpnyid'    => $receipt->cpny_id ?? $receipt->cpnyid ?? '',
            'deptname'  => $receipt->department_id ?? $receipt->departementid ?? '',
            'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
            'fullname'  => $fullname,               // view email kita pakai $fullname
            'name'      => $fullname,               // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'Receipt',
            'info'      => 'Request from user '.$receipt->user_peminta,
            'status'    => $status,
            'url'       => url('/showreceipt/' . $eid),
        ];

        $recipients = User::where('username', $receipt->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan field yang tersedia
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Receipt')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending Receipt rejected email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar penolakan (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($receipt->id, 'GR', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after reject failed', [
                'docid' => $receipt->receiptnbr,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Receipt rejected successfully']);
    }

    public function reviseReceipt(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $receipt = TrReceipt::where('receiptnbr', $docid)->first();
        $receipt = TrReceipt::with('creator')
            ->where('receiptnbr', $docid)
            ->first();
        $fullname = data_get($receipt, 'creator.name') ?: $receipt->created_by;
            
        if (!$receipt) {
            return response()->json(['success' => false, 'message' => 'Receipt not found'], 404);
        }

        // Pastikan user adalah approver aktif (status P) dokumen ini
        $tApproval = T_approval::where('docid', $receipt->receiptnbr)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%{$user->username}%")
            ->whereNotNull('aprvdatebefore')
            ->orderBy('aprvid', 'ASC')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
        }

        DB::beginTransaction();
        try {
            // Tandai approval saat ini sebagai Revise (D)
            $tApproval->status        = 'D';
            $tApproval->aprvdateafter = $now;
            $tApproval->aprvusername  = $user->username;  // catat siapa yang revise
            $tApproval->name          = $user->name;
            $tApproval->save();

            // Update header Receipt
            $receipt->status       = 'D';
            $receipt->completed_by = $user->username;        // mengikuti pola existing
            $receipt->completed_at = $now;
            $receipt->save();

            // Batalkan approval lain yang masih pending
            T_approval::where('docid', $receipt->receiptnbr)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Revise Receipt failed', ['docid' => $docid, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Revise failed'], 500);
        }

        // === Kirim email ke requester (creator) ===
        $status = 'D'; // Revise
        $subjectMap = [
            'P' => 'Waiting Approval',
            'R' => 'Rejected Approval',
            'D' => 'Revise Approval',
            'A' => 'Approved',
            'C' => 'Completed',
        ];
        $subjectSuffix = $subjectMap[$status] ?? 'Notification';
        $eid = Hashids::encode($receipt->id);

        $data = [
            'docid'     => $receipt->receiptnbr,
            'cpnyid'    => $receipt->cpny_id ?? $receipt->cpnyid ?? '',
            'deptname'  => $receipt->department_id ?? $receipt->departementid ?? '',
            'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
            'fullname'  => $fullname,             // template email pakai $fullname
            'name'      => $fullname,             // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'Receipt',
            'info'      => 'Request from user '.$receipt->user_peminta,
            'status'    => $status,
            'url'       => url('/showreceipt/' . $eid),
        ];

        $recipients = User::where('username', $receipt->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Receipt')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending Receipt revise email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar revisi (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($receipt->id, 'GR', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after revise failed', [
                'docid' => $receipt->receiptnbr,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Receipt revised successfully']);
    }  

    public function checkApproval($id, $action)
    {
        $user = Auth::user(); // Ambil user yang login
        // dd($action);
        // Query dasar untuk pengecekan
        $query = T_approval::where('docid', $id)
                    ->where('aprvusername', 'like', '%' . $user->username . '%')
                    ->where('status', 'P');                 

        // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
        if (in_array($action, ['reject', 'revise','approve'])) {
            $query->whereNotNull('aprvdatebefore');
        }

        // Cek apakah user bisa melakukan aksi
        $canPerformAction = $query->exists();

        return response()->json(['canPerformAction' => $canPerformAction]);
    }
   

    // public function fetchComments($id)
    // {
    
    //     $comments = T_Message::where('docid', $id)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return response()->json([
    //         'status' => 'success',
    //         'comments' => $comments
    //     ]);
    // }

    // public function storeComment(Request $request, $id)
    // {
    //     $user = Auth::user();
    //     $request->validate([
    //         'comment' => 'required|string|max:500',
    //     ]);
    //     // dd($id);
    //     $user = request()->user();
    //     $comment = new T_Message();
    //     $comment->docid = $id;
    //     $comment->doctype = 'PO';
    //     $comment->username = $user->username; 
    //     $comment->name = $user->name; 
    //     $comment->message = $request->comment;
    //     $comment->status = 'A';
    //     $comment->created_at = now();
    //     $comment->save();

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Comment added successfully!',
    //         'comment' => $comment
    //     ]);
    // }

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

            if ($rcp->status !== 'P') {
                throw new \RuntimeException('Receipt tidak dalam status PENDING.');
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
                $rcp->status            = 'C';
                $rcp->totalqty_received = $totalQtyReceivedThisReceipt;
                $rcp->completed_by      = $uname;
                $rcp->completed_at      = $now;
                $rcp->updated_by        = $uname;
                $rcp->save();

                TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
                    ->update(['status' => 'C']);

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

                $rcp->status       = 'C';
                if (\Schema::hasColumn($rcp->getTable(), 'totalqty_return')) {
                    $rcp->totalqty_return = $totalQtyReturnThisReceipt;
                }
                $rcp->completed_by = $uname;
                $rcp->completed_at = $now;
                $rcp->updated_by   = $uname;
                $rcp->save();

                TrReceiptdetail::where('receiptnbr', $rcp->receiptnbr)
                    ->update(['status' => 'C']);

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
                        'message' => 'Failed to create Return',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

            DB::commit();

            return redirect()->route('receiptlist')
                ->with('success', "Return {$receiptnbr} created from {$src->receiptnbr}. Total Qty Return: {$totalReturn}");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors([config('app.debug') ? $e->getMessage() : 'Failed to create Return'])->withInput();
        }
    }



    


}
