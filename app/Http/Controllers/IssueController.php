<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Autonbr;
use App\Models\User;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use App\Models\Attachment;
use App\Models\T_Message;
use App\Models\MsVendor;
use App\Models\CompanyPG;
use App\Models\TrIssue;
use App\Models\TrIssuedetail;
use App\Models\TrSPB;
use App\Models\TrSPBdetail;
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

class IssueController extends Controller
{
    public function createIssue(Request $req) 
    {
        // Ambil spbid (plain) dari query
        $spbid = (string) $req->query('spbid', '');
        abort_if($spbid === '', 404, 'SPB ID required');

        // --- Ambil header SPB ---
        $spb = TrSPB::select([
                'id','spbid','spbdate','cpny_id','department_id','keperluan'
            ])
            ->where('spbid', $spbid)
            ->first();

        abort_if(!$spb, 404, 'SPB not found');

        // --- Ambil detail SPB + qty sisa untuk ISSUE ---
        // Gunakan spb_openqty sebagai sisa yang bisa di-issue
        $details = TrSPBdetail::select([
                'id','spbid','spb_no',
                'inventoryid','inventory_descr','siteid',
                DB::raw("COALESCE(uom,'') AS uom"),
                DB::raw("COALESCE(qty,0) AS qty_original"),
                DB::raw("COALESCE(spb_openqty,0) AS qty_sisa")
            ])
            ->where('spbid', $spb->spbid)
            ->orderBy('id')
            ->get()
            ->filter(fn($r) => (float)$r->qty_sisa > 0)
            ->map(function ($r) {
                // supaya view tetap pakai $d->qty → set ke qty_sisa
                $r->qty = (float) $r->qty_sisa;
                return $r;
            })
            ->values();

        $attachments = []; // biasanya kosong saat create

        return view('pages.issue.createissue', [
            'spb'         => $spb,
            'details'     => $details,
            'attachments' => $attachments,
        ]);
    }
    
    public function storeIssue(Request $request)
    {
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $spbid = trim((string)$request->input('spbid', ''));
        if ($spbid === '') {
            return back()->withErrors(['SPB ID tidak ditemukan.'])->withInput();
        }

        // ===== Ambil header SPB =====
        /** @var TrSPB|null $spb */
        $spb = TrSPB::where('spbid', $spbid)->first();
        if (!$spb) {
            return back()->withErrors(['SPB tidak ditemukan.'])->withInput();
        }

        // ===== Ambil detail SPB (keyBy id) =====
        /** @var \Illuminate\Support\Collection|TrSPBdetail[] $spbDetails */
        $spbDetails = TrSPBdetail::where('spbid', $spbid)->get()->keyBy('id');

        // ===== Ambil input qty_issue & siteid =====
        $qtyIssueInput = (array) $request->input('qty_issue', []);
        $siteInput     = (array) $request->input('siteid',    []);

        // Minimal satu baris > 0
        $hasAnyQty = false;
        foreach ($qtyIssueInput as $k => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty > 0) { $hasAnyQty = true; break; }
        }
        if (!$hasAnyQty) {
            return back()->withErrors(['Qty Issue minimal satu baris harus > 0.'])->withInput();
        }

        // Validasi per-baris: qty_issue <= spb_openqty (hanya validasi, TIDAK update SPB)
        foreach ($qtyIssueInput as $detailId => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty <= 0) continue;

            /** @var TrSPBdetail|null $src */
            $src = $spbDetails->get((int)$detailId);
            if (!$src) {
                return back()->withErrors(["Detail SPB (ID: {$detailId}) tidak ditemukan."])->withInput();
            }
            $open = (float) ($src->spb_openqty ?? 0);
            if ($qty > $open) {
                return back()->withErrors(["Qty Issue untuk item {$src->inventoryid} melebihi sisa open ({$open})."])->withInput();
            }
        }

        // ===== Siapkan info untuk autonumber & header =====
        $doctype = 'IS'; // untuk autonumber & approval workflow
        $now     = \Carbon\Carbon::now();
        $year    = (int) $now->year;
        $month   = (int) $now->month;

        $cpnyid  = $spb->cpny_id       ?? ($request->input('cpnyid')       ?? null);
        // $deptid  = $spb->department_id ?? ($request->input('departmentid') ?? null);
        $deptid  = 'WAREHOUSE';

        // $approvalCount = M_approval::where([
        //     ['status',      '=', 'A'],
        //     ['aprvcpnyid',  '=', $cpnyid],
        //     ['aprvdeptid',  '=', $deptid],
        //     ['aprvdoctype', '=', $doctype],
        // ])->count();

        // if ($approvalCount === 0) {
        //     // pakai redirect agar konsisten dengan flow form
        //     return back()->withErrors(['Approval line belum di-setup, hubungi IT.'])->withInput();
        // }

        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyid, $deptid);
        

        return \DB::connection('pgsql')->transaction(function () use (
            $request, $doctype, $now, $year, $month, $spb, $spbid, $spbDetails, $qtyIssueInput, $siteInput, $cpnyid, $deptid, $username, $approvalCtl
        ) {
            // ===== Autonumber (YYMM####) untuk IS =====
            /** @var Autonbr|null $autonbr */
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year',    $year)
                ->where('month',   $month)
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
                $urut = (int) $autonbr->number + 1;
                $autonbr->update(['number' => $urut]);
            }

            $yymm    = substr((string)$year, 2) . str_pad((string)$month, 2, '0', STR_PAD_LEFT);
            $issueid = $doctype . $yymm . sprintf('%04d', $urut); // IS2510xxxx

            // ===== Header TrIssue =====
            $header = new TrIssue();
            $header->issueid        = $issueid;
            $header->issuedate      = $now->toDateString();
            $header->issuetype      = 'IS'; // konsisten dengan list/filter
            $header->spbid          = $spbid;
            $header->cpny_id        = $spb->cpny_id ?? null;
            $header->department_id  = $spb->department_id ?? null;
            $header->user_peminta   = $spb->created_by ?? null;
            $header->budget_perpost = $spb->budget_perpost ?? null;
            $header->issuenote      = (string) $request->input('issuenote', '');
            $header->totalissueqty  = 0;   // diisi setelah loop detail
            $header->status         = 'P';
            $header->created_by     = $username;
            $header->created_at     = $now;
            $header->save();

            // ===== Detail TrIssuedetail (TANPA update SPB) =====
            $lineNo        = 0;
            $totalIssueQty = 0.0;

            /** @var TrSPBdetail $src */
            foreach ($spbDetails as $detailId => $src) {
                $qtyRecRaw = $qtyIssueInput[$detailId] ?? 0;
                $qtyIssue  = (float) str_replace(',', '.', (string)$qtyRecRaw);
                if ($qtyIssue <= 0) continue;

                $siteFromForm = isset($siteInput[$detailId]) ? trim((string)$siteInput[$detailId]) : null;
                $lineNo++;

                $det = new TrIssuedetail();
                $det->issueid         = $issueid;
                $det->issue_no        = $lineNo;

                // relasi SPB (untuk referensi saat approve)
                $det->spbid           = $spbid;
                $det->spb_no          = $src->spb_no ?? null;

                // inventory
                $det->issuetype       = 'IS';
                $det->inventoryid     = $src->inventoryid;
                $det->inventory_descr = $src->inventory_descr;
                $det->siteid          = $siteFromForm !== '' ? $siteFromForm : ($src->siteid ?? null);

                // Kuantitas Issue
                $det->qty             = $qtyIssue;
                $det->issue_qty       = $qtyIssue;
                $det->uom             = $src->uom ?? null;

                // Base (no conversion)
                $det->type_multiplier = 1;
                $det->base_multiplier = 1;
                $det->base_qty        = $qtyIssue;
                $det->base_uom        = $src->uom ?? null;

                // Cost tidak digunakan pada Issue
                $det->unitcost        = 0;
                $det->totalcost       = 0;

                // Budget (copy dari SPB detail bila ada)
                $det->budget_perpost              = $src->budget_perpost ?? null;
                $det->budget_cpny_id              = $src->budget_cpny_id ?? null;
                $det->budget_business_unit_id     = $src->budget_business_unit_id ?? null;
                $det->budget_department_fin_id    = $src->budget_department_fin_id ?? null;
                $det->budget_account_id           = $src->budget_account_id ?? null;
                $det->budget_activity_id          = $src->budget_activity_id ?? null;

                $det->status          = 'P';
                $det->created_by      = $username;
                $det->created_at      = $now;
                $det->save();

                $totalIssueQty += $qtyIssue;
            }

            if ($totalIssueQty <= 0) {
                throw new \RuntimeException('Qty Issue minimal satu baris harus > 0.');
            }

            // ===== Update total di header Issue =====
            $header->totalissueqty = $totalIssueQty;
            $header->save();

            // ===== TIDAK ADA UPDATE KE TrSPB / TrSPBdetail DI SINI =====
            // Posting Qty akan dilakukan di approveIssue FINAL.

            // ===== Generate approval instance (T_approval) =====
            // $datestamp = $now->toDateTimeString();
            // $approvals = M_approval::where([
            //     ['status',      '=', 'A'],
            //     ['aprvcpnyid',  '=', $cpnyid],
            //     ['aprvdeptid',  '=', $deptid],
            //     ['aprvdoctype', '=', $doctype],
            // ])->orderBy('aprvid')->get();

            // foreach ($approvals as $a) {
            //     T_approval::create([
            //         'docid'          => $issueid,
            //         'aprvid'         => $a->aprvid,
            //         'aprvdoctype'    => $a->aprvdoctype,
            //         'aprvcpnyid'     => $a->aprvcpnyid,
            //         'aprvdeptid'     => $a->aprvdeptid,
            //         'aprvusername'   => $a->aprvusername,
            //         'name'           => $a->name,
            //         'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
            //         'aprvtotalday'   => 1,
            //         'status'         => 'P',
            //         'created_by'     => $username,
            //     ]);
            // }

            // === Generate TrApproval (GR tidak cek nominal)
            $ctx = ['ignore_nominal' => true];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $issueid,
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


            // ===== Attachments (opsional) =====
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $issueid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyid ?? null,
                    'departementid' => $deptid ?? null,
                    'base_folder'   => 'att-issue/'.strtolower($doctype),
                    'created_by'    => $username,
                ];

                $files    = (array) $request->file('attachments');
                $uploader = app(\App\Http\Controllers\TrAttachmentController::class);
                $uploader->uploadInternal($meta, $files);
            }

            // // ===== Notif approver pertama (opsional) =====
            // $firstApproval = T_approval::where('docid', $issueid)
            //     ->where('status', 'P')->orderBy('aprvid')->first();

            // if ($firstApproval) {
            //     $status     = $header->status;
            //     $subjectMap = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];
            //     $eid        = \Vinkla\Hashids\Facades\Hashids::encode($header->id);

            //     $data = [
            //         'docid'     => $firstApproval->docid,
            //         'cpnyid'    => $firstApproval->aprvcpnyid,
            //         'deptname'  => $firstApproval->aprvdeptid,
            //         'date'      => $firstApproval->aprvdatebefore,
            //         'name'      => $firstApproval->name,
            //         'createdby' => $header->created_by,
            //         'info'      => 'Request from user '.$header->user_peminta,
            //         'status'    => $status,
            //         'docname'   => 'Issue',
            //         'url'       => url('/showissue/' . $eid),
            //     ];

            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails    = User::whereIn('username', $approvers)->where('status', 'A')->pluck('test_email');

            //     foreach ($emails as $email) {
            //         \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectMap, $status) {
            //             $message->to($email)
            //                 ->subject($data['docid'].' - '.($subjectMap[$status] ?? 'Notification').' Issue')
            //                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //         });
            //     }
            // }

            $eid = \Vinkla\Hashids\Facades\Hashids::encode($header->id);
            $approvalCtl->notifyFirstApprover(
                $issueid,
                $doctype,
                $header->status, // 'P'
                'Issue',
                url('/showissue/' . $eid),
                [
                    'info'      => 'Request from user ' . ($po->user_peminta ?? '-'),
                    'createdby' => $header->created_by,
                    'date'      => $now->toDateTimeString(),
                ]
            );


            return redirect()
                ->route('issuelist')
                ->with('success', "Issue {$issueid} created. Total Qty: {$totalIssueQty}");
        }, 3); // retry 3x jika deadlock
    }



    public function showIssue($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // ===== Header Issue (pakai issueid, sesuai model)
        /** @var TrIssue $iss */
        $iss = TrIssue::findOrFail($id);

        // ===== Detail Issue (berdasarkan issueid)
        $issdetail = TrIssuedetail::where('issueid', $iss->issueid)
            ->orderBy('issue_no')
            ->get();

        // --- Approval trail ---
        $approval = T_approval::where('docid', $iss->issueid)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->orderBy('aprvid')
            ->get();

        // ===== Attachment by issueid (doctype IS)
        $attachment = Attachment::where('docid', $iss->issueid)
            ->where('status', 'A')
            ->get();

        // ===== Link ke SPB (opsional) -> /showissue/{hash}
        $spbUrl = null;
        if (!empty($iss->spbid)) {
            $spbId = TrSPB::where('spbid', $iss->spbid)->value('id');
            if ($spbId) {
                $spbHash = Hashids::encode($spbId);
                $spbUrl  = url("/showissue/{$spbHash}");
            }
        }

        // convenience id terenkripsi untuk share (kalau perlu)
        $eid_issueid = Hashids::encode($iss->id);

        return view('pages.issue.showissue', [
            'iss'         => $iss,
            'issdetail'   => $issdetail,
            'attachment'  => $attachment,
            'hash'        => $hash,
            'eid_issueid' => $eid_issueid,
            'spbUrl'      => $spbUrl,
            'approval'      => $approval,
        ]);
    }

    public function editIssue($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Header
        /** @var TrIssue $iss */
        $iss = TrIssue::findOrFail($id);

        // Safety: hanya boleh edit saat status Revise (D) dan oleh pembuat dokumen
        if (!in_array($iss->status, ['D'])) {
            abort(403, 'Issue tidak dalam status Revise.');
        }
        if (($iss->created_by ?? '') !== ($user->username ?? '')) {
            abort(403, 'Anda tidak berhak mengedit dokumen ini.');
        }

        // Detail
        $issdetail = TrIssuedetail::where('issueid', $iss->issueid)
            ->orderBy('issue_no')
            ->get();

        // Attachment untuk Issue (doctype IS, refnbr = issueid)
        $rows = TrAttachment::where('refnbr', $iss->issueid)            
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

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

        $eid_issueid = Hashids::encode($iss->issueid); // jika perlu untuk email/link lain
        $hash_issue  = $hash;

        return view('pages.issue.editissue', [
            'iss'         => $iss,
            'issdetail'   => $issdetail,
            'attachments' => $attachments,
            'hash'        => $hash_issue,
            'eid_issueid' => $eid_issueid,
        ]);
    }

    public function updateIssue(Request $request, $hash)
    {
        // dd($request->all());
        $now  = Carbon::now();
        $user = $request->user();
        $username = $user->username ?? 'system';

        // ===== Temukan Issue by hash (eid) atau fallback ke issueid langsung =====
        $ids = Hashids::decode($hash);
        
        if (!empty($ids)) {
            // $iss = TrIssue::with('details')->find($ids[0]);
            $iss = TrIssue::find($ids[0]);
        } else {
            // fallback: jika {hash} ternyata issueid
            // $iss = TrIssue::with('details')->where('issueid', $hash)->first();
            $detailRows = TrIssuedetail::where('issueid', $iss->issueid)->get()->keyBy('id');
        }

        if (!$iss) {
            return response()->json(['message' => 'Issue not found'], 404);
        }

        // Batasan: hanya creator yang boleh update saat status revise (D) — opsional
        if (strtoupper((string)$iss->status) !== 'D' || (string)$iss->created_by !== (string)$username) {
            return response()->json(['message' => "You can't update this document"], 403);
        }

        // ===== Ambil SPB header & detail untuk validasi (TANPA update) =====
        $spbid = $iss->spbid;
        if (!$spbid) {
            return response()->json(['message' => 'SPB not found on Issue'], 422);
        }

        /** @var TrSPB|null $spb */
        $spb = TrSPB::where('spbid', $spbid)->first();
        if (!$spb) {
            return response()->json(['message' => 'SPB header not found'], 422);
        }

        /** @var \Illuminate\Support\Collection|TrSPBdetail[] $spbDetails */
        $spbDetails = TrSPBdetail::where('spbid', $spbid)->get()->keyBy('inventoryid'); // key by inventoryid untuk cocokkan

        // ===== Input arrays =====
        $qtyIssueInput = (array) $request->input('qty_issue', []); // key = detail id (TrIssuedetail.id)
        $siteInput     = (array) $request->input('siteid',    []); // key = detail id

        // Minimal satu baris terisi (boleh 0, tapi harus ada kunci)
        if (count($qtyIssueInput) === 0) {
            return response()->json(['message' => 'Tidak ada qty yang dikirim.'], 422);
        }

        // ===== Validasi per-baris:
        // qty_edit >= 0 dan qty_edit <= (spb_openqty + qty_lama_di_issue_baris_ini)
        // agar tidak melampaui sisa SPB ketika di-post saat approve final.
        // $detailRows = $iss->details()->get()->keyBy('id'); // TrIssuedetail keyed by its id (from form)
        $detailRows = TrIssuedetail::where('issueid', $iss->issueid)->get()->keyBy('id');
        foreach ($qtyIssueInput as $detailId => $v) {
            $qtyNew = (float) str_replace(',', '.', (string)$v);
            if ($qtyNew < 0) {
                return response()->json(['message' => "Qty Issue untuk detail {$detailId} tidak boleh negatif."], 422);
            }

            /** @var TrIssuedetail|null $det */
            $det = $detailRows->get((int)$detailId);
            if (!$det) {
                return response()->json(['message' => "Detail Issue (ID: {$detailId}) tidak ditemukan."], 422);
            }

            // Cocokkan ke SPB detail by inventory (lebih aman; bisa juga pakai linkage lain jika ada)
            $spbDet = $spbDetails->get($det->inventoryid);
            if (!$spbDet) {
                return response()->json(['message' => "SPB Detail untuk item {$det->inventoryid} tidak ditemukan."], 422);
            }

            $openSpb  = (float) ($spbDet->spb_openqty ?? 0);
            $oldQty   = (float) ($det->issue_qty ?? 0);
            $ceiling  = $openSpb + $oldQty; // batas maksimum revisi saat ini

            if ($qtyNew > $ceiling + 1e-9) {
                return response()->json([
                    'message' => "Qty Issue item {$det->inventoryid} melebihi sisa open SPB ({$openSpb}) + qty saat ini ({$oldQty}). Maks: {$ceiling}"
                ], 422);
            }
        }

        // ===== Siapkan approval line yg aktif untuk dokumen ini (akan direset) =====
        $doctype = 'IS'; // pertahankan issuetype (IS/RI)
        $cpnyid  = $iss->cpny_id;
        // $deptid  = $iss->department_id;
        $deptid  = 'WAREHOUSE';

       
        // ===== Approval controller =====
        $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyid, $deptid);


        // ====== EXEC UPDATE DALAM TRANSAKSI ======
        return DB::connection('pgsql')->transaction(function () use (
            $request, $now, $iss, $spb, $detailRows, $qtyIssueInput, $siteInput, $doctype, $cpnyid, $deptid, $username
        ) {
            $issueid = $iss->issueid;

            // === Update tiap baris TrIssuedetail (qty + optional site) ===
            $totalIssueQty = 0.0;

            foreach ($detailRows as $detId => $det) {
                // Jika baris tidak dikirim di form (mis. di-hide), pertahankan qty lama
                $qtyNew = array_key_exists($detId, $qtyIssueInput)
                    ? (float) str_replace(',', '.', (string)$qtyIssueInput[$detId])
                    : (float) ($det->issue_qty ?? 0);

                // Normalisasi minimal 0
                if ($qtyNew < 0) $qtyNew = 0;

                // Site baru (jika ada)
                $newSite = null;
                if (array_key_exists($detId, $siteInput)) {
                    $newSite = trim((string)$siteInput[$detId]);
                    if ($newSite === '') $newSite = null;
                }

                // Update fields yang diperlukan saja
                $det->issue_qty       = $qtyNew;
                $det->qty             = $qtyNew;
                $det->base_qty        = $qtyNew;
                if (!is_null($newSite)) {
                    $det->siteid = $newSite;
                }
                // status detail tetap P (menunggu approve setelah revise)
                $det->status          = 'P';
                $det->updated_by      = $username;
                $det->updated_at      = $now;
                $det->save();

                $totalIssueQty += $qtyNew;
            }

            if ($totalIssueQty < 0) {
                throw new \RuntimeException('Total qty tidak valid.');
            }

            // === Update header minimal: total, status kembali ke P, cap "last edited" ===
            $iss->totalissueqty = $totalIssueQty;
            $iss->status        = 'P';              // kembali ke Waiting Approval setelah revise
            $iss->completed_by  = null;
            $iss->completed_at  = null;
            $iss->updated_by    = $username;
            $iss->updated_at    = $now;
            $iss->save();

            
            // $datestamp = $now->toDateTimeString();
            // $approvals = M_approval::where([
            //     ['status',      '=', 'A'],
            //     ['aprvcpnyid',  '=', $cpnyid],
            //     ['aprvdeptid',  '=', $deptid],
            //     ['aprvdoctype', '=', $doctype],
            // ])->orderBy('aprvid')->get();

            // foreach ($approvals as $a) {
            //     T_approval::create([
            //         'docid'          => $issueid,
            //         'aprvid'         => $a->aprvid,
            //         'aprvdoctype'    => $a->aprvdoctype,
            //         'aprvcpnyid'     => $a->aprvcpnyid,
            //         'aprvdeptid'     => $a->aprvdeptid,
            //         'aprvusername'   => $a->aprvusername,
            //         'name'           => $a->name,
            //         'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
            //         'aprvtotalday'   => 1,
            //         'status'         => 'P',
            //         'created_by'     => $username,
            //     ]);
            // }

            // Generate TrApproval (GR tidak cek nominal)
            $ctx = ['ignore_nominal' => true];
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $issueid,
                $doctype,
                $cpnyid,
                $deptid,
                $username,
                $ctx,
                $now
            );

            if ($firstApprovalUsernames) {
                $iss->completed_by = $firstApprovalUsernames;
                $iss->completed_at = $now;
                $iss->save();
            }


            // === Attachment baru (opsional) ===
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $issueid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyid ?? null,
                    'departementid' => $deptid ?? null,
                    'base_folder'   => 'att-issue/'.strtolower($doctype),
                    'created_by'    => $username,
                ];
                $files    = (array) $request->file('attachments');
                $uploader = app(\App\Http\Controllers\TrAttachmentController::class);
                $uploader->uploadInternal($meta, $files);
            }

            // // === Email ke approver pertama ===
            // $firstApproval = T_approval::where('docid', $issueid)
            //     ->where('status', 'P')->orderBy('aprvid')->first();

            // if ($firstApproval) {
            //     $status     = $iss->status; // 'P'
            //     $subjectMap = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];
            //     $eid        = Hashids::encode($iss->id);

            //     $data = [
            //         'docid'     => $firstApproval->docid,
            //         'cpnyid'    => $firstApproval->aprvcpnyid,
            //         'deptname'  => $firstApproval->aprvdeptid,
            //         'date'      => $firstApproval->aprvdatebefore,
            //         'name'      => $firstApproval->name,
            //         'createdby' => $iss->created_by,
            //         'info'      => 'Revised by user '.$username,
            //         'status'    => $status,
            //         'docname'   => 'Issue',
            //         'url'       => url('/showissue/' . $eid),
            //     ];

            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails    = User::whereIn('username', $approvers)->where('status', 'A')->pluck('test_email');

            //     foreach ($emails as $email) {
            //         try {
            //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectMap, $status) {
            //                 $message->to($email)
            //                     ->subject($data['docid'].' - '.($subjectMap[$status] ?? 'Notification').' '.$data['docname'])
            //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //             });
            //         } catch (\Throwable $e) {
            //             Log::error('Failed sending Issue revised waiting-approval email', ['error' => $e->getMessage()]);
            //         }
            //     }
            // }

            // Email approver pertama
            $eid = \Vinkla\Hashids\Facades\Hashids::encode($iss->id);
            $approvalCtl->notifyFirstApprover(
                $issueid,
                $doctype,
                $iss->status, // 'P'
                'Issue',
                url('/showissue/' . $eid),
                [
                    'info'      => 'Revised by user '.$username,
                    'createdby' => $iss->created_by,
                    'date'      => $now->toDateTimeString(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Issue {$issueid} updated. Total Qty: {$totalIssueQty}",
            ]);
        }, 3);
    }

   
    public function uploadAttachments(Request $request, $spbid)
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
                    $attach->docid       = $spbid;
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
   
    public function listAttachment($spbid)
    {
        $rows = Attachment::where('docid', $spbid)
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
    

    // public function removeAttachment($id)
    // {
    //     try {
    //         $attachment = TrAttachment::findOrFail($id);
    //         $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

    //         return response()->json(['success' => true, 'message' => 'Attachment status updated']);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
    //     }
    // }

    
    public function printIssue(string $hash, Request $request)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Header + relasi pembuat
        /** @var TrIssue $iss */
        $iss = TrIssue::with(['creator:username,name'])->findOrFail($id);

        // SPB terkait (opsional, untuk meta)
        $spb = TrSPB::where('spbid', $iss->spbid)->first();

        // Detail issue (barang yang dikeluarkan)
        $issdetails = TrIssuedetail::where('issueid', $iss->issueid)
            ->orderBy('issue_no')
            ->get();

        // Company (untuk header)
        $company = Company::where('cpnyid', $iss->cpny_id)->first();

        // Label type
        $issuetypeLabel = $iss->issuetype === 'IS'
            ? 'Issue'
            : ($iss->issuetype === 'RI' ? 'Return Issue' : $iss->issuetype);

        // Total (opsional)
        $totalQty = (float) $issdetails->sum('issue_qty');

        // Creator
        $createdName = ucwords(strtolower(optional($iss->creator)->name ?? $iss->created_by));
        $printedAt   = now();

        $data = [
            'iss'            => $iss,
            'spb'            => $spb,
            'issdetails'     => $issdetails,
            'company'        => $company,
            'issuetypeLabel' => $issuetypeLabel,
            'totalQty'       => $totalQty,
            'createdName'    => $createdName,
            'printedAt'      => $printedAt,
        ];

        // Selalu pakai BPG (portrait)
        $pdf = Pdf::loadView('pages.issue.pdf_bpg', $data)->setPaper('A4', 'portrait');

        $dompdf  = $pdf->getDomPDF();
        $dompdf->render();

        // ===== Footer (created/printed & pagination) =====
        $canvas  = $dompdf->get_canvas();
        $w       = $canvas->get_width();
        $h       = $canvas->get_height();

        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->get_font('sans-serif', 'normal');
        $size    = 9;

        $leftTxt    = "Created by: {$createdName} • Printed: " . $printedAt->format('d/m/Y H:i');
        $rightTpl   = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $rightWidth = $metrics->getTextWidth($rightTpl, $font, $size);
        $y          = $h - 28;

        // kiri
        $canvas->page_text(30, $y, $leftTxt, $font, $size, [0,0,0]);
        // kanan
        $canvas->page_text($w - 30 - $rightWidth, $y, $rightTpl, $font, $size, [0,0,0]);

        return $dompdf->stream("BPG_{$iss->issueid}.pdf", ['Attachment' => false]);
    }

    public function approveIssue(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'IS';

        $issue = TrIssue::with('creator')->where('issueid', $docid)->first();
        if (!$issue) return response()->json(['success'=>false,'message'=>'Issue not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($issue->id);
        $docUrl   = url('/showissues/' . $eid);
        $fullname = data_get($issue, 'creator.name') ?: $issue->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $issue->issueid,
            $doctype,
            $user->username,
            $user->name,

            // ===== COMPLETE CALLBACK =====
            function (string $refnbr, \Carbon\Carbon $now) use ($issue, $fullname, $docUrl, $user) {
                DB::transaction(function () use ($issue, $fullname, $docUrl, $now, $user) {
                    // set header/detail issue complete
                    $issue->status       = 'C';
                    $issue->completed_by = $issue->completed_by ?: ($user->username ?? auth()->user()->username);
                    $issue->completed_at = $now;
                    $issue->save();

                    TrIssuedetail::where('issueid', $issue->issueid)->update(['status' => 'C']);

                    // === POSTING KE SPB HANYA DI FINAL ===
                    // ambil detail issue yang barusan di-approve
                    $issDetails = TrIssuedetail::where('issueid', $issue->issueid)->get();
                    // fungsi akan throw exception jika SPB tidak ada → otomatis rollback transaksi approveStep scope ini
                    $this->applyIssuePostingToSpb($issue, $issDetails, $user, $now);

                    // notifikasi requester: selesai
                    app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                        $issue->issueid,
                        'Issue',
                        'C',
                        $issue->created_by,
                        $docUrl,
                        [
                            'cpnyid'   => $issue->cpny_id ?? $issue->cpnyid ?? '',
                            'deptname' => $issue->department_id ?? $issue->departementid ?? '',
                            'date'     => $issue->issuedate,
                            'info'     => $issue->keperluan,
                            'fullname' => $fullname,
                            'name'     => $fullname,
                            'createdby'=> $fullname,
                        ]
                    );
                });
            },

            // ===== NOTIFY NEXT APPROVER (kalau belum final) =====
            function ($next, \Carbon\Carbon $now) use ($issue, $docUrl) {
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $issue->issueid,
                    'IS',
                    'P',
                    'Issue',
                    $docUrl,
                    [
                        'info'      => $issue->keperluan,
                        'createdby' => $issue->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                // jejak
                $issue->completed_by = auth()->user()->username;
                $issue->completed_at = $now;
                $issue->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Task approved successfully']);
    }


    public function rejectIssue(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'IS';

        $issue = \App\Models\TrIssue::with('creator')->where('issueid', $docid)->first();
        if (!$issue) return response()->json(['success'=>false,'message'=>'Issue not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($issue->id);
        $docUrl   = url('/showissues/' . $eid);
        $fullname = data_get($issue, 'creator.name') ?: $issue->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $issue->issueid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($issue, $fullname, $docUrl) {
                $issue->status       = 'R';
                $issue->completed_by = auth()->user()->username;
                $issue->completed_at = $now;
                $issue->save();

                // optional: tandai detail R
                // \App\Models\TrIssuedetail::where('issueid', $issue->issueid)->update(['status' => 'R']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $issue->issueid,
                    'Issue',
                    'R',
                    $issue->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $issue->cpny_id ?? $issue->cpnyid ?? '',
                        'deptname' => $issue->department_id ?? $issue->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $issue->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($issue->id, 'IS', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Issue rejected successfully']);
    }

    public function reviseIssue(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'IS';

        $issue = \App\Models\TrIssue::with('creator')->where('issueid', $docid)->first();
        if (!$issue) return response()->json(['success'=>false,'message'=>'Issue not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($issue->id);
        $docUrl   = url('/showissues/' . $eid);
        $fullname = data_get($issue, 'creator.name') ?: $issue->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $issue->issueid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($issue, $fullname, $docUrl) {
                // === HEADER Issue -> D ===
                $issue->status       = 'D';
                $issue->completed_by = auth()->user()->username;
                $issue->completed_at = $now;
                $issue->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrIssuedetail::where('issueid', $issue->issueid)->update(['status' => 'D']);

                // === Email ke requester ===
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $issue->issueid,
                    'Issue',
                    'D',
                    $issue->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $issue->cpny_id ?? $issue->cpnyid ?? '',
                        'deptname' => $issue->department_id ?? $issue->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $issue->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,   // <<< tambahkan ini
                    ]
                );


                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($issue->id, 'IS', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success'=>false,
                'message'=>$result['message'] ?? 'Revise failed'
            ], 403);
        }

        return response()->json(['success'=>true,'message'=>'Issue revised successfully']);
    }

    protected function applyIssuePostingToSpb(TrIssue $issue, Collection $issueDetails, User $user, Carbon $now): void
    {
        // Lock header SPB + detail agar konsisten
        $spb = TrSPB::where('spbid', $issue->spbid)->lockForUpdate()->first();
        if (!$spb) {
            throw new \RuntimeException('SPB terkait tidak ditemukan.');
        }

        $spbDetailRows = TrSPBdetail::where('spbid', $issue->spbid)->lockForUpdate()->get();

        // index helper
        $spbByKey       = $spbDetailRows->keyBy(fn($r) => (($r->inventoryid ?? '') . '|' . ($r->uom ?? '')));
        $spbByInventory = $spbDetailRows->groupBy('inventoryid');
        $spbBySpbNo     = $spbDetailRows->keyBy('spb_no');

        $isIssue  = strtoupper($issue->issuetype) === 'IS'; // keluar barang
        $isReturn = strtoupper($issue->issuetype) === 'RI'; // return

        foreach ($issueDetails as $rd) {
            // sumber qty:
            $qty = 0.0;
            if ($isIssue) {
                $qty = (float) ($rd->issue_qty ?? $rd->qty ?? 0);
            } elseif ($isReturn) {
                $qty = (float) ($rd->return_qty ?? $rd->qty_return ?? 0);
            }
            if ($qty <= 0) continue;

            // cari pasangan baris SPB
            $spbDet = null;
            if (!empty($rd->spb_no) && $spbBySpbNo->has($rd->spb_no)) {
                $spbDet = $spbBySpbNo->get($rd->spb_no);
            }
            if (!$spbDet) {
                $key = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '');
                $spbDet = $spbByKey->get($key);
            }
            if (!$spbDet) {
                $bucket = $spbByInventory->get($rd->inventoryid);
                $spbDet = $bucket ? $bucket->first() : null;
            }
            if (!$spbDet) continue;

            // update qty open/issue
            if ($isIssue) {
                $spbDet->spb_openqty = max(0, (float)($spbDet->spb_openqty ?? 0) - $qty);
                $spbDet->issue_qty   = (float)($spbDet->issue_qty ?? 0) + $qty;
            } else { // return
                $spbDet->spb_openqty = (float)($spbDet->spb_openqty ?? 0) + $qty;
                $currentIssued       = (float)($spbDet->issue_qty ?? 0);
                $spbDet->issue_qty   = max(0, $currentIssued - $qty);
            }

            // status baris
            $spbDet->status     = ($spbDet->spb_openqty <= 0) ? 'C' : 'P';
            $spbDet->updated_by = $user->username ?? 'system';
            $spbDet->updated_at = $now;
            $spbDet->save();
        }

        // === Refresh total header SPB ===
        $agg = TrSPBdetail::where('spbid', $issue->spbid)
            ->selectRaw('COALESCE(SUM(qty),0)                AS total_qty')
            ->selectRaw('COALESCE(SUM(spb_openqty),0)        AS total_open')
            ->selectRaw('COALESCE(SUM(issue_qty),0)          AS total_issue')
            ->selectRaw('COALESCE(SUM(spb_completeqty),0)    AS total_complete')
            ->first();

        $spb->totalspbqty      = (float) $agg->total_qty;
        $spb->totalspbopenqty  = (float) $agg->total_open;
        $spb->totalissueqty    = (float) $agg->total_issue;
        $spb->totalcompleteqty = (float) $agg->total_complete;

        // header complete jika semua open = 0
        $spb->status     = ($spb->totalspbopenqty <= 0) ? 'C' : 'P';
        $spb->updated_by = $user->username ?? 'system';
        $spb->updated_at = $now;
        $spb->save();
    }

    // public function approveIssue(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     $iss = TrIssue::with('creator')->where('issueid', $docid)->first();
    //     if (!$iss) {
    //         return response()->json(['success' => false, 'message' => 'Issue not found'], 404);
    //     }
    //     $fullname = data_get($iss, 'creator.name') ?: $iss->created_by;

    //     // pastikan user approver aktif
    //     $tApproval = T_approval::where('docid', $iss->issueid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%{$user->username}%")
    //         ->whereNotNull('aprvdatebefore')
    //         ->orderBy('aprvid', 'ASC')
    //         ->first();
    //     if (!$tApproval) {
    //         return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // approve current level
    //         $tApproval->status        = 'A';
    //         $tApproval->aprvdateafter = $now;
    //         $tApproval->aprvusername  = $user->username;
    //         $tApproval->name          = $user->name;
    //         $tApproval->save();

    //         // stamp header
    //         $iss->completed_by = $user->username;
    //         $iss->completed_at = $now;
    //         $iss->save();

    //         $pendingCount = T_approval::where('docid', $iss->issueid)->where('status','P')->count();

    //         $subjectMap = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];
    //         $eid = Hashids::encode($iss->id);

    //         if ($pendingCount === 0) {
    //             // ===== FINAL APPROVAL =====
    //             $iss->status       = 'C';
    //             $iss->completed_by = $user->username;
    //             $iss->completed_at = $now;
    //             $iss->save();

    //             // close all issue details
    //             $issdetails = TrIssuedetail::where('issueid',$iss->issueid)->orderBy('issue_no')->get();
    //             foreach ($issdetails as $d) { $d->status = 'C'; $d->save(); }

    //             // ====== POSTING KE SPB HANYA DI FINAL ======
    //             $spb = TrSPB::where('spbid',$iss->spbid)->lockForUpdate()->first();
    //             if (!$spb) {
    //                 DB::rollBack();
    //                 return response()->json(['success'=>false,'message'=>'SPB terkait tidak ditemukan.'],422);
    //             }

    //             $spbDetailRows = TrSPBdetail::where('spbid',$iss->spbid)->lockForUpdate()->get();
    //             $spbByKey       = $spbDetailRows->keyBy(fn($r)=>(($r->inventoryid??'').'|'.($r->uom??'')));
    //             $spbByInventory = $spbDetailRows->groupBy('inventoryid');
    //             $spbBySpbNo     = $spbDetailRows->keyBy('spb_no'); // opsional kalau ada

    //             if (strtoupper($iss->issuetype) === 'IS') {
    //                 // ===== ISSUE (keluar barang) → open turun, issue_qty naik
    //                 foreach ($issdetails as $rd) {
    //                     $qty = (float) ($rd->issue_qty ?? $rd->qty ?? 0);
    //                     if ($qty <= 0) continue;

    //                     $spbDet = null;
    //                     if (!empty($rd->spb_no) && $spbBySpbNo->has($rd->spb_no)) $spbDet = $spbBySpbNo->get($rd->spb_no);
    //                     if (!$spbDet) {
    //                         $key = ($rd->inventoryid ?? '').'|'.($rd->uom ?? '');
    //                         $spbDet = $spbByKey->get($key);
    //                     }
    //                     if (!$spbDet) {
    //                         $bucket = $spbByInventory->get($rd->inventoryid);
    //                         $spbDet = $bucket ? $bucket->first() : null;
    //                     }
    //                     if (!$spbDet) continue;

    //                     $spbDet->spb_openqty = max(0, (float)($spbDet->spb_openqty ?? 0) - $qty);
    //                     $spbDet->issue_qty   = (float)($spbDet->issue_qty ?? 0) + $qty;

    //                     $spbDet->status      = ($spbDet->spb_openqty <= 0) ? 'C' : 'P';
    //                     $spbDet->updated_by  = $user->username;
    //                     $spbDet->updated_at  = $now;
    //                     $spbDet->save();
    //                 }
    //             } elseif (strtoupper($iss->issuetype) === 'RI') {
    //                 // ===== RETURN ISSUE (barang kembali) → open naik, issue_qty turun
    //                 foreach ($issdetails as $rd) {
    //                     // GANTI nama kolom di bawah ini sesuai model detail kamu
    //                     $qtyReturn = (float) ($rd->return_qty ?? $rd->qty_return ?? 0);
    //                     if ($qtyReturn <= 0) continue;

    //                     $spbDet = null;
    //                     if (!empty($rd->spb_no) && $spbBySpbNo->has($rd->spb_no)) $spbDet = $spbBySpbNo->get($rd->spb_no);
    //                     if (!$spbDet) {
    //                         $key = ($rd->inventoryid ?? '').'|'.($rd->uom ?? '');
    //                         $spbDet = $spbByKey->get($key);
    //                     }
    //                     if (!$spbDet) {
    //                         $bucket = $spbByInventory->get($rd->inventoryid);
    //                         $spbDet = $bucket ? $bucket->first() : null;
    //                     }
    //                     if (!$spbDet) continue;

    //                     // open naik
    //                     $spbDet->spb_openqty = (float)($spbDet->spb_openqty ?? 0) + $qtyReturn;
    //                     // issue turun tidak boleh minus
    //                     $currentIssued = (float)($spbDet->issue_qty ?? 0);
    //                     $spbDet->issue_qty = max(0, $currentIssued - $qtyReturn);

    //                     // status baris
    //                     $spbDet->status      = ($spbDet->spb_openqty <= 0) ? 'C' : 'P';
    //                     $spbDet->updated_by  = $user->username;
    //                     $spbDet->updated_at  = $now;
    //                     $spbDet->save();
    //                 }
    //             }

    //             // aggregate header SPB
    //             $agg = TrSPBdetail::where('spbid',$iss->spbid)
    //                 ->selectRaw('
    //                     COALESCE(SUM(issue_qty),0)       AS total_issue,
    //                     COALESCE(SUM(spb_openqty),0)     AS total_open,
    //                     COALESCE(SUM(spb_completeqty),0) AS total_complete
    //                 ')->first();

    //             if ($agg) {
    //                 $spb->totalissueqty     = (float)$agg->total_issue;
    //                 $spb->totalspbopenqty   = (float)$agg->total_open;
    //                 $spb->totalcompleteqty  = (float)$agg->total_complete;
    //             }

    //             if ((float)($spb->totalspbopenqty ?? 0) <= 0) {
    //                 $spb->status       = 'C';
    //                 $spb->completed_by = $user->username;
    //                 $spb->completed_at = $now;
    //             }
    //             $spb->updated_by = $user->username;
    //             $spb->updated_at = $now;
    //             $spb->save();

    //             // ===== email COMPLETE ke creator
    //             $status        = 'C';
    //             $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    //             $data = [
    //                 'docid'     => $iss->issueid,
    //                 'cpnyid'    => $iss->cpny_id ?? $iss->cpnyid ?? '',
    //                 'deptname'  => $iss->department_id ?? $iss->departementid ?? '',
    //                 'date'      => $iss->issuedate ?? $now,
    //                 'fullname'  => $fullname,
    //                 'name'      => $fullname,
    //                 'createdby' => $fullname,
    //                 'docname'   => 'Issue',
    //                 'info'      => $iss->issuenote ?? $iss->keperluan,
    //                 'status'    => $status,
    //                 'url'       => url('/showissue/' . $eid),
    //             ];
    //             $recipients = User::where('username',$iss->created_by)->where('status','A')->get();
    //             foreach ($recipients as $iss) {
    //                 try {
    //                     Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $iss, $subjectSuffix) {
    //                         $to = $iss->test_email ?? $iss->email;
    //                         $message->to($to)
    //                                 ->subject($data['docid'].' - '.$subjectSuffix.' Issue')
    //                                 ->from('digitalserver@pakuwon.com','Pakuwon System');
    //                     });
    //                 } catch (\Throwable $e) {
    //                     Log::error('Failed sending Issue completion email', ['error'=>$e->getMessage()]);
    //                 }
    //             }
    //         } else {
    //             // ===== masih ada approver berikutnya
    //             $next = T_approval::where('docid',$iss->issueid)->where('status','P')->orderBy('aprvid','ASC')->first();
    //             if ($next) {
    //                 $next->aprvdatebefore = $now;
    //                 $next->save();

    //                 $status        = 'P';
    //                 $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    //                 $data = [
    //                     'docid'     => $next->docid,
    //                     'cpnyid'    => $next->aprvcpnyid,
    //                     'deptname'  => $next->aprvdeptid,
    //                     'date'      => $next->aprvdatebefore,
    //                     'fullname'  => $next->name,
    //                     'name'      => $next->name,
    //                     'createdby' => $iss->created_by,
    //                     'docname'   => 'Issue',
    //                     'info'      => $iss->issuenote ?? $iss->keperluan,
    //                     'status'    => $status,
    //                     'url'       => url('/showissue/' . $eid),
    //                 ];
    //                 $usernames = array_filter(array_map('trim', explode(',', (string)$next->aprvusername)));
    //                 if (!empty($usernames)) {
    //                     $recipients = User::whereIn('username',$usernames)->where('status','A')->get();
    //                     foreach ($recipients as $iss) {
    //                         try {
    //                             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $iss, $subjectSuffix) {
    //                                 $to = $iss->test_email ?? $iss->email;
    //                                 $message->to($to)
    //                                         ->subject($data['docid'].' - '.$subjectSuffix.' Issue')
    //                                         ->from('digitalserver@pakuwon.com','Pakuwon System');
    //                             });
    //                         } catch (\Throwable $e) {
    //                             Log::error('Failed sending Issue waiting-approval email', ['error'=>$e->getMessage()]);
    //                         }
    //                     }
    //                 } else {
    //                     Log::warning('Next approver has empty aprvusername list', ['docid'=>$iss->issueid]);
    //                 }
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Approve Issue failed', ['error'=>$e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
    //     }
    // }

    
    // public function rejectIssue(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $iss = TrIssue::where('issueid', $docid)->first();
    //     $iss = TrIssue::with('creator')
    //         ->where('issueid', $docid)
    //         ->first();
    //     $fullname = data_get($iss, 'creator.name') ?: $iss->created_by;

    //     if (!$iss) {
    //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    //     }

    //     // Validasi: user harus approver aktif (status P) pada dokumen ini
    //     $tApproval = T_approval::where('docid', $iss->issueid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%{$user->username}%")
    //         ->whereNotNull('aprvdatebefore') 
    //         ->orderBy('aprvid', 'ASC')
    //         ->first();

    //     if (!$tApproval) {
    //         return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Tandai approval saat ini sebagai Rejected
    //         $tApproval->status        = 'R';
    //         $tApproval->aprvdateafter = $now;
    //         $tApproval->aprvusername  = $user->username; // catat siapa yang reject
    //         $tApproval->name          = $user->name;
    //         $tApproval->save();

    //         // Update header Issue
    //         $iss->status       = 'R';
    //         $iss->completed_by = $user->username;
    //         $iss->completed_at = $now;
    //         $iss->save();

    //         // Batalkan semua approval yang masih pending
    //         T_approval::where('docid', $iss->issueid)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Reject Issue failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Reject failed'], 500);
    //     }

    //     // === Kirim Email ke requester (creator) ===
    //     $status = 'R'; // Rejected
    //     $subjectMap = [
    //         'P' => 'Waiting Approval',
    //         'R' => 'Rejected Approval',
    //         'D' => 'Revise Approval',
    //         'A' => 'Approved',
    //         'C' => 'Completed',
    //     ];
    //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    //     $eid = Hashids::encode($iss->id);

    //     $data = [
    //         'docid'     => $iss->issueid,
    //         'cpnyid'    => $iss->cpny_id ?? $iss->cpnyid ?? '',
    //         'deptname'  => $iss->department_id ?? $iss->departementid ?? '',
    //         'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
    //         'fullname'  => $fullname,               // view email kita pakai $fullname
    //         'name'      => $fullname,               // fallback jika view pakai $name
    //         'createdby' => $fullname,
    //         'docname'   => 'Issue',
    //         'info'      => $iss->keperluan,
    //         'status'    => $status,
    //         'url'       => url('/showissue/' . $eid),
    //     ];

    //     $recipients = User::where('username', $iss->created_by)
    //         ->where('status', 'A')
    //         ->get();

    //     foreach ($recipients as $iss) {
    //         try {
    //             $to = $iss->test_email ?? $iss->email; // sesuaikan field yang tersedia
    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Issue')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         } catch (\Throwable $e) {
    //             Log::error('Failed sending Issue rejected email', [
    //                 'docid' => $data['docid'],
    //                 'to'    => $iss->username,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     // Simpan komentar penolakan (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')
    //             ->sendmsg($iss->id, 'IS', $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after reject failed', [
    //             'docid' => $iss->issueid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'Issue rejected successfully']);
    // }

    // public function reviseIssue(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $iss = TrIssue::where('issueid', $docid)->first();
    //     $iss = TrIssue::with('creator')
    //         ->where('issueid', $docid)
    //         ->first();
    //     $fullname = data_get($iss, 'creator.name') ?: $iss->created_by;
            
    //     if (!$iss) {
    //         return response()->json(['success' => false, 'message' => 'Issue not found'], 404);
    //     }

    //     // Pastikan user adalah approver aktif (status P) dokumen ini
    //     $tApproval = T_approval::where('docid', $iss->issueid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%{$user->username}%")
    //         ->whereNotNull('aprvdatebefore')
    //         ->orderBy('aprvid', 'ASC')
    //         ->first();

    //     if (!$tApproval) {
    //         return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Tandai approval saat ini sebagai Revise (D)
    //         $tApproval->status        = 'D';
    //         $tApproval->aprvdateafter = $now;
    //         $tApproval->aprvusername  = $user->username;  // catat siapa yang revise
    //         $tApproval->name          = $user->name;
    //         $tApproval->save();

    //         // Update header Issue
    //         $iss->status       = 'D';
    //         $iss->completed_by = $user->username;        // mengikuti pola existing
    //         $iss->completed_at = $now;
    //         $iss->save();

    //         // Batalkan approval lain yang masih pending
    //         T_approval::where('docid', $iss->issueid)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Revise Issue failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Revise failed'], 500);
    //     }

    //     // === Kirim email ke requester (creator) ===
    //     $status = 'D'; // Revise
    //     $subjectMap = [
    //         'P' => 'Waiting Approval',
    //         'R' => 'Rejected Approval',
    //         'D' => 'Revise Approval',
    //         'A' => 'Approved',
    //         'C' => 'Completed',
    //     ];
    //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    //     $eid = Hashids::encode($iss->id);

    //     $data = [
    //         'docid'     => $iss->issueid,
    //         'cpnyid'    => $iss->cpny_id ?? $iss->cpnyid ?? '',
    //         'deptname'  => $iss->department_id ?? $iss->departementid ?? '',
    //         'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
    //         'fullname'  => $fullname,             // template email pakai $fullname
    //         'name'      => $fullname,             // fallback jika view pakai $name
    //         'createdby' => $fullname,
    //         'docname'   => 'Issue',
    //         'info'      => $iss->keperluan,
    //         'status'    => $status,
    //         'url'       => url('/showissue/' . $eid),
    //     ];

    //     $recipients = User::where('username', $iss->created_by)
    //         ->where('status', 'A')
    //         ->get();

    //     foreach ($recipients as $iss) {
    //         try {
    //             $to = $iss->test_email ?? $iss->email; // sesuaikan dengan kolom yang ada
    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Issue')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         } catch (\Throwable $e) {
    //             Log::error('Failed sending Issue revise email', [
    //                 'docid' => $data['docid'],
    //                 'to'    => $iss->username,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     // Simpan komentar revisi (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')
    //             ->sendmsg($iss->id, 'IS', $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after revise failed', [
    //             'docid' => $iss->issueid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'Issue revised successfully']);
    // }
    

    // public function checkApproval($id, $action)
    // {
    //     $user = Auth::user(); // Ambil user yang login
    //     // dd($action);
    //     // Query dasar untuk pengecekan
    //     $query = T_approval::where('docid', $id)
    //                 ->where('aprvusername', 'like', '%' . $user->username . '%')
    //                 ->where('status', 'P');                 

    //     // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
    //     if (in_array($action, ['reject', 'revise','approve'])) {
    //         $query->whereNotNull('aprvdatebefore');
    //     }

    //     // Cek apakah user bisa melakukan aksi
    //     $canPerformAction = $query->exists();

    //     return response()->json(['canPerformAction' => $canPerformAction]);
    // }

    public function approveIssue_xxx(Request $request, $id)
    {
        return \DB::connection('pgsql')->transaction(function () use ($id) {

            $user  = \Auth::user();
            $uname = $user->username ?? null;
            $now   = Carbon::now();

            // ===== Lock header Issue          
            $iss = TrIssue::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($iss->status !== 'P') {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Issue tidak dalam status PENDING.'
                ], 422);
            }

            // ===== Ambil semua detail Issue            
            $issdetails = TrIssuedetail::where('issueid', $iss->issueid)
                ->orderBy('issue_no')
                ->get();

            if ($issdetails->isEmpty()) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Tidak ada detail issue untuk diproses.'
                ], 422);
            }

            // ===== Lock SPB terkait
            /** @var TrSPB|null $spb */
            $spb = TrSPB::where('spbid', $iss->spbid)->lockForUpdate()->first();
            if (!$spb) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'SPB terkait tidak ditemukan.'
                ], 422);
            }

            // ===== Ambil semua detail SPB untuk pemetaan
            /** @var \Illuminate\Support\Collection|TrSPBdetail[] $spbDetailRows */
            $spbDetailRows = TrSPBdetail::where('spbid', $iss->spbid)->get();

            // Kunci pencocokan utama: inventoryid|uom|spb_no; fallback: inventoryid|uom
            $spbByKeyFull = $spbDetailRows->keyBy(function ($r) {
                return ($r->inventoryid ?? '') . '|' . ($r->uom ?? '') . '|' . ($r->spb_no ?? '');
            });
            $spbByKey = $spbDetailRows->keyBy(function ($r) {
                return ($r->inventoryid ?? '') . '|' . ($r->uom ?? '');
            });

            $sumIssueThisDoc  = 0.0; // total issue pada dokumen ini (untuk issuetype='issue')
            $sumReturnThisDoc = 0.0; // total return pada dokumen ini (untuk issuetype='return')

            // ===== Helper untuk clamp angka 0..qty
            $clamp = function (float $val, float $min, float $max) {
                return max($min, min($max, $val));
            };

            if ($iss->issuetype === 'IS') {
                // ========== APPROVE ISSUE ==========
                foreach ($issdetails as $rd) {
                    $qty = (float) ($rd->issue_qty ?? 0);
                    if ($qty <= 0) continue;

                    $keyFull = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '') . '|' . ($rd->spb_no ?? '');
                    $keyBase = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '');

                    /** @var TrSPBdetail|null $spbDet */
                    $spbDet = $spbByKeyFull->get($keyFull) ?? $spbByKey->get($keyBase);
                    if (!$spbDet) continue;

                    $ordered      = (float) ($spbDet->qty ?? 0);
                    $issuedSoFar  = (float) ($spbDet->issue_qty ?? 0);
                    $openSoFar    = (float) ($spbDet->spb_openqty ?? max($ordered - $issuedSoFar, 0));

                    // Tambah issue, kurangi open
                    $newIssued = $this->clamp(($issuedSoFar + $qty), 0.0, $ordered);
                    $delta     = $newIssued - $issuedSoFar; // real yang ditambahkan (terclamp)
                    $newOpen   = $this->clamp(($openSoFar - $delta), 0.0, $ordered);

                    $spbDet->issue_qty       = $newIssued;
                    $spbDet->spb_openqty     = $newOpen;
                    $spbDet->spb_completeqty = $this->clamp(($spbDet->spb_completeqty ?? 0) + $delta, 0.0, $ordered);

                    // Status detail
                    if ($newOpen <= 0.0000001) {
                        $spbDet->status = 'C';
                    } else {
                        $spbDet->status = 'P';
                    }

                    $spbDet->updated_by = $uname;
                    $spbDet->save();

                    $sumIssueThisDoc += $delta;
                }

                // ===== Recalculate header SPB totals (dari tabel detail)
                $totals = TrSPBdetail::selectRaw("
                        COALESCE(SUM(qty),0)              AS total_spbqty,
                        COALESCE(SUM(spb_openqty),0)      AS total_spbopenqty,
                        COALESCE(SUM(issue_qty),0)        AS total_issueqty,
                        COALESCE(SUM(spb_completeqty),0)  AS total_completeqty
                    ")
                    ->where('spbid', $iss->spbid)
                    ->first();

                $spb->totalspbqty       = (float) $totals->total_spbqty;
                $spb->totalspbopenqty   = (float) $totals->total_spbopenqty;
                $spb->totalissueqty     = (float) $totals->total_issueqty;
                $spb->totalcompleteqty  = (float) $totals->total_completeqty;

                // Close SPB jika semua open = 0
                if ($spb->totalspbopenqty <= 0.0000001) {
                    $spb->status       = 'C';
                    $spb->completed_by = $uname;
                    // jika punya kolom completed_at di DB, set di sini; jika tidak, hapus baris ini
                    // $spb->completed_at = $now;
                } else {
                    // tetap progress
                    $spb->status = 'P';
                }
                $spb->updated_by = $uname;
                $spb->save();

                // ===== Update header Issue
                $iss->status           = 'C';
                $iss->totalissueqty    = (float) (($iss->totalissueqty ?? 0) + $sumIssueThisDoc);
                $iss->completed_by     = $uname;
                // jika ada kolom completed_at, aktifkan:
                // $iss->completed_at     = $now;
                $iss->updated_by       = $uname;
                $iss->save();

                return response()->json([
                    'ok'      => true,
                    'message' => 'Issue berhasil di-approve & SPB diperbarui.',
                    'data'    => [
                        'type'                => $iss->issuetype,
                        'issueid'             => $iss->issueid,
                        'spbid'               => $iss->spbid,
                        'added_issue_qty'     => $sumIssueThisDoc,
                        'spb_total_issueqty'  => (float) $spb->totalissueqty,
                        'spb_total_openqty'   => (float) $spb->totalspbopenqty,
                        'spb_status'          => $spb->status,
                    ],
                ]);
            }

            if ($iss->issuetype === 'RI') {
                // ========== APPROVE RETURN ==========
                foreach ($issdetails as $rd) {
                    $qty = (float) ($rd->issue_qty ?? 0); // dipakai sebagai qty return dari dokumen return
                    if ($qty <= 0) continue;

                    $keyFull = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '') . '|' . ($rd->spb_no ?? '');
                    $keyBase = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '');

                    /** @var TrSPBdetail|null $spbDet */
                    $spbDet = $spbByKeyFull->get($keyFull) ?? $spbByKey->get($keyBase);
                    if (!$spbDet) continue;

                    $ordered      = (float) ($spbDet->qty ?? 0);
                    $issuedSoFar  = (float) ($spbDet->issue_qty ?? 0);
                    $openSoFar    = (float) ($spbDet->spb_openqty ?? max($ordered - $issuedSoFar, 0));

                    // Return: kurangi issue_qty, tambah openqty
                    $newIssued = $this->clamp(($issuedSoFar - $qty), 0.0, $ordered);
                    $delta     = $issuedSoFar - $newIssued; // real yang dikembalikan
                    $newOpen   = $this->clamp(($openSoFar + $delta), 0.0, $ordered);

                    $spbDet->issue_qty       = $newIssued;
                    $spbDet->spb_openqty     = $newOpen;

                    // completeqty turunkan sesuai delta (jika semantics kamu begitu)
                    $spbDet->spb_completeqty = $this->clamp(($spbDet->spb_completeqty ?? 0) - $delta, 0.0, $ordered);

                    // Status detail
                    if ($newOpen <= 0.0000001) {
                        $spbDet->status = 'C';
                    } else {
                        $spbDet->status = 'P';
                    }

                    $spbDet->updated_by = $uname;
                    $spbDet->save();

                    $sumReturnThisDoc += $delta;
                }

                // ===== Recalculate header SPB totals
                $totals = TrSPBdetail::selectRaw("
                        COALESCE(SUM(qty),0)              AS total_spbqty,
                        COALESCE(SUM(spb_openqty),0)      AS total_spbopenqty,
                        COALESCE(SUM(issue_qty),0)        AS total_issueqty,
                        COALESCE(SUM(spb_completeqty),0)  AS total_completeqty
                    ")
                    ->where('spbid', $iss->spbid)
                    ->first();

                $spb->totalspbqty       = (float) $totals->total_spbqty;
                $spb->totalspbopenqty   = (float) $totals->total_spbopenqty;
                $spb->totalissueqty     = (float) $totals->total_issueqty;
                $spb->totalcompleteqty  = (float) $totals->total_completeqty;

                // Status SPB setelah return
                if ($spb->totalspbopenqty <= 0.0000001) {
                    $spb->status       = 'C';
                    $spb->completed_by = $uname;
                    // $spb->completed_at = $now; // jika kolom ada
                } else {
                    $spb->status = 'P';
                }
                $spb->updated_by = $uname;
                $spb->save();

                // ===== Update header Issue (return)
                $iss->status               = 'C';
                $iss->totalreturnissueqty  = (float) (($iss->totalreturnissueqty ?? 0) + $sumReturnThisDoc);
                $iss->completed_by         = $uname;
                // $iss->completed_at          = $now; // jika kolom ada
                $iss->updated_by           = $uname;
                $iss->save();

                return response()->json([
                    'ok'      => true,
                    'message' => 'Return berhasil di-approve & SPB diperbarui.',
                    'data'    => [
                        'type'                   => $iss->issuetype,
                        'issueid'                => $iss->issueid,
                        'spbid'                  => $iss->spbid,
                        'returned_issue_qty'     => $sumReturnThisDoc,
                        'spb_total_issueqty'     => (float) $spb->totalissueqty,
                        'spb_total_openqty'      => (float) $spb->totalspbopenqty,
                        'spb_status'             => $spb->status,
                    ],
                ]);
            }

            // issuetype selain 'issue' / 'return'
            return response()->json([
                'ok'      => false,
                'message' => 'Tipe issue tidak dikenali.'
            ], 422);
        });
    }

    /**
     * Helper kecil supaya bisa dipakai di closure di atas
     */
    private function clamp(float $val, float $min, float $max): float
    {
        return max($min, min($max, $val));
    }




    public function createReturn(Request $request)
    {
        $eid = (string) $request->query('iss', '');
        $id  = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        // Header issue asal (tipe bebas; kita cuma mau referensinya)
        $iss = TrIssue::findOrFail($id);

        // Detail dari issue asal (yang qty_received-nya menjadi dasar perhitungan)
        $origDetails = TrIssuedetail::select([
            'id',
            'issueid',
            'issue_no',
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
        ->where('issueid', $iss->issueid)
        ->orderBy('issue_no')
        ->get();


        // Total qty_return yang SUDAH dibuat dari semua dokumen 'return' yang refer ke issue ini
        // Asumsi: dokumen pengembalian (return) disimpan di TrIssue dengan issuetype='return'
        // dan TrIssuedetail.qty_return berisi qty return per item.
        $returnedAgg = TrIssuedetail::query()
            ->select([
                'inventoryid',
                'uom',
                'siteid',
                DB::raw('SUM(COALESCE(qty_return,0)) AS sum_returned')
            ])
            ->whereIn('issueid', function($q) use ($iss) {
                $q->select('issueid')
                ->from('tr_issue') // tabel TrIssue
                ->where('issuetype', 'return')
                ->where('ref_issueid', $iss->issueid);
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
            return back()->with('warning', 'Semua item pada issue ini sudah tidak memiliki sisa untuk di-return.');
        }

        // Kirimkan juga ref_issueid untuk hidden input di form
        $ref_issueid = $iss->issueid;

        // Tampilkan view input return (qty_return), hidden: ref_issueid
        return view('pages.issue.return_create', [
            'iss'             => $iss,
            'details'         => $details,
            'eid'             => $eid,
            'ref_issueid'  => $ref_issueid,
        ]);
    }

    // Simpan dokumen return
    public function storeReturn(Request $request)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $eid = (string)$request->input('iss', '');
        $id  = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $src = TrIssue::findOrFail($id); // issue sumber (GR)
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

        $doctype = 'IS';
        $now   = Carbon::now();
        $year  = (int)$now->year;
        $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);

        $cpnyid  = $spb->cpny_id       ?? ($request->input('cpnyid')       ?? null);
        // $deptid  = $spb->department_id ?? ($request->input('departmentid') ?? null);
        $deptid  = 'WAREHOUSE';

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

            $issueid = $doctype . substr($year,2) . $month . sprintf('%04d', $urut);

            // === Header return (copy dari sumber)
            $hdr = new TrIssue();
            $hdr->issueid        = $issueid;
            $hdr->issuedate       = $now->toDateString();
            // agar muncul di tab Return Jobs sesuai filter kamu:
            $hdr->issuetype       = 'RI'; // <— sesuai filter returnjobs (status C + issuetype='issue')
            $hdr->spbid             = $src->spbid;
            $hdr->ref_issueid    = $src->issueid;            // <— penting
            $hdr->cpny_id           = $src->cpny_id;
            $hdr->csid              = $src->csid;
            $hdr->sppbjktid         = $src->sppbjktid;
            $hdr->department_id     = $src->department_id;
            $hdr->user_peminta      = $src->user_peminta;
            $hdr->issuenote       = $notes;
            $hdr->vendorid          = $src->vendorid;
            $hdr->vendorname        = $src->vendorname;
            $hdr->totalqty_received = 0; // untuk return kita isi setelah loop sbg total qty return
            $hdr->status            = 'P'; // langsung complete, atau 'P' kalau mau ada approval
            $hdr->created_by        = $username;
            $hdr->created_at        = $now;
            $hdr->save();

            // === Detail return: simpan hanya baris qty_return > 0
            $srcDetails = TrIssuedetail::where('issueid', $src->issueid)->get()->keyBy('id');
            $line  = 0; $totalReturn = 0.0;

            foreach ($qtyInput as $detailId => $raw) {
                $qty = (float)str_replace(',', '.', (string)$raw);
                if ($qty <= 0) continue;          // ⬅️ hanya simpan baris > 0
                $srcDet = $srcDetails[$detailId] ?? null;
                if (!$srcDet) continue;

                $line++;

                $det = new TrIssuedetail();
                $det->issueid              = $issueid;
                $det->issue_no              = $line;

                $det->spbid                   = $src->spbid;
                $det->spb_no                   = $srcDet->spb_no;

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

                $det->issuetype             = $hdr->issuetype;

                // open ordered (tidak relevan utk return)
                $det->qty_open_ordered        = 0;
                $det->base_qty_open_ordered   = 0;

                // return qty
                $det->qty_received            = 0;
                $det->base_qty_received       = 0;

                $det->qty_return              = $qty;
                $det->base_qty_return         = $qty;

                $det->ref_issueid          = $src->issueid;  // <— referensi

                // budget (copy)
                $det->budget_perspbst          = $srcDet->budget_perspbst;
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
                    'refnbr'        => $issueid,
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

            return redirect()->route('issuelist')
                ->with('success', "Return {$issueid} created from {$src->issueid}. Total Qty Return: {$totalReturn}");
        } catch (\Throwable $e) {
            DB::rollBack();
            respbrt($e);
            return back()->withErrors([config('app.debug') ? $e->getMessage() : 'Failed to create Return'])->withInput();
        }
    }



    


}
