<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Autonbr;
use App\Models\User;
use App\Models\MsVendor;
use App\Models\MsCompany;
use App\Models\TrIssue;
use App\Models\TrIssuedetail;
use App\Models\TrSPB;
use App\Models\TrSPBdetail;
use Vinkla\Hashids\Facades\Hashids;
use Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\TrAttachmentController;
use App\Models\TrAttachment;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;
use Illuminate\Support\Collection;
use App\Http\Controllers\Traits\HasAutonbr;


class IssueController extends Controller
{
    use HasAutonbr;

    public function storeIssue(Request $request)
    {
        // dd($request->all());
        // $user     = $request->user();
        // $username = $user->username ?? 'system';
        $doctype  = 'IS';
        $user     = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt        = Carbon::now();
        $year      = (int) $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();
        $now     = \Carbon\Carbon::now();

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

        // ===== Ambil input qty_issue, siteid, dan issuenote_detail =====
        $qtyIssueInput     = (array) $request->input('qty_issue', []);        // [spb_detail_id => qty]
        $siteInput         = (array) $request->input('siteid',    []);        // [spb_detail_id => siteid]
        $noteDetailsInput  = (array) $request->input('issuenote_detail', []); // [spb_detail_id => note]

        // Minimal satu baris > 0
        $hasAnyQty = false;
        foreach ($qtyIssueInput as $k => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty > 0) { $hasAnyQty = true; break; }
        }
        if (!$hasAnyQty) {
            return back()->withErrors(['Qty Issue minimal satu baris harus > 0.'])->withInput();
        }

        // ===== VALIDASI per baris: qty_issue <= (qty - issue_qty + return_qty) =====
        // Catatan: issue_qty & return_qty di sini hanya qty yang SUDAH APPROVE (posted sebelumnya).
        foreach ($qtyIssueInput as $detailId => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty <= 0) continue;

            /** @var TrSPBdetail|null $src */
            $src = $spbDetails->get((int)$detailId);
            if (!$src) {
                return back()->withErrors(["Detail SPB (ID: {$detailId}) tidak ditemukan."])->withInput();
            }

            $spbQty   = (float) ($src->qty ?? 0);
            $issued   = (float) ($src->issue_qty ?? 0);
            $returned = (float) ($src->return_qty ?? 0);

            // open untuk ISSUE = qty - issue_qty + return_qty
            $open = $spbQty - $issued + $returned;
            if ($open < 0) $open = 0;

            if ($qty > $open) {
                return back()->withErrors([
                    "Qty Issue untuk item {$src->inventoryid} melebihi sisa open ({$open})."
                ])->withInput();
            }
        }

        // ===== Siapkan info untuk autonumber & header =====
        // $doctype = 'IS'; // untuk autonumber & approval workflow

        // $year    = (int) $now->year;
        // $month   = (int) $now->month;

        $cpnyid  = $spb->cpny_id       ?? ($request->input('cpnyid')       ?? null);
        $deptid  = 'WAREHOUSE'; // menyesuaikan logic sebelumnya

        // Load garis approval
        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyid, $deptid);

        // ===== TRANSAKSI PGSQL =====
        return \DB::connection('pgsql')->transaction(function () use (
            $request,
            $doctype,
            $now,
            $year,
            $month,
            $spb,
            $spbid,
            $spbDetails,
            $qtyIssueInput,
            $siteInput,
            $noteDetailsInput,
            $cpnyid,
            $deptid,
            $username,
            $approvalCtl,
            $user
        ) {
            // ===== Autonumber (YYMM####) untuk IS =====
            /** @var Autonbr|null $autonbr */
            // $autonbr = Autonbr::lockForUpdate()
            //     ->where('doctype', $doctype)
            //     ->where('year',    $year)
            //     ->where('month',   $month)
            //     ->first();

            // if (!$autonbr) {
            //     $autonbr = Autonbr::create([
            //         'doctype' => $doctype,
            //         'year'    => $year,
            //         'month'   => $month,
            //         'status'  => 'A',
            //         'number'  => 1,
            //     ]);
            //     $urut = 1;
            // } else {
            //     $urut = (int) $autonbr->number + 1;
            //     $autonbr->update(['number' => $urut]);
            // }

            // $yymm    = substr((string)$year, 2) . str_pad((string)$month, 2, '0', STR_PAD_LEFT);
            // $issueid = $doctype . $yymm . sprintf('%04d', $urut); // IS2511xxxx
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'ISSUE'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string)$year, 2) . $month;   // YYMM
            $issueid  = $doctype . $tglbln . sprintf("%04d", $urutan);

            // ===== Header TrIssue (MODEL BARU) =====
            $header = new TrIssue();
            $header->issueid              = $issueid;
            $header->issuedate            = $now->toDateString();
            $header->issuetype            = 'IS'; // Issue
            $header->spbid                = $spbid;
            $header->woid                 = $spb->woid ?? null;
            $header->cpny_id              = $spb->cpny_id ?? null;
            $header->department_id        = $spb->department_id ?? null;
            $header->user_peminta         = $spb->created_by ?? null;
            $header->budget_perpost       = $spb->budget_perpost ?? null;
            $header->issuenote            = (string) $request->input('issuenote', '');
            $header->grandtotalcost       = 0;    // diisi setelah loop detail
            $header->totalissueqty        = 0;    // diisi setelah loop detail
            $header->totalreturnissueqty  = 0;    // untuk saat ini 0 (belum ada return)
            $header->status               = 'P';
            $header->created_by           = $username;
            $header->created_at           = $now;
            $header->save();

            // ===== Detail TrIssuedetail (KUMPULKAN untuk posting ke SPB) =====
            $lineNo          = 0;
            $totalIssueQty   = 0.0;
            $grandTotalCost  = 0.0;
            $createdDetails  = collect();   // <--- ini yg dikirim ke applyIssuePostingToSpb

            /** @var TrSPBdetail $src */
            foreach ($spbDetails as $detailId => $src) {
                $qtyRecRaw = $qtyIssueInput[$detailId] ?? 0;
                $qtyIssue  = (float) str_replace(',', '.', (string)$qtyRecRaw);
                if ($qtyIssue <= 0) continue;

                $siteFromForm = isset($siteInput[$detailId]) ? trim((string)$siteInput[$detailId]) : null;
                $lineNo++;

                // Ambil harga dari SPB detail (kalau ada)
                $unitCost = (float) ($src->unitcost ?? 0);
                $lineCost = $unitCost * $qtyIssue;

                $det = new TrIssuedetail();
                $det->issueid         = $issueid;
                $det->issue_no        = $lineNo;

                // relasi SPB
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

                // Base qty (pakai schema baru SPB detail)
                $det->type_multiplier = $src->type_multiplier ?: null;
                $det->base_multiplier = $src->base_multiplier ?? 1;
                $det->base_qty        = $qtyIssue * ($det->base_multiplier ?: 1);
                $det->base_uom        = $src->base_uom ?? $src->uom;

                // Cost
                $det->unitcost        = $unitCost;
                $det->totalcost       = $lineCost;

                // Note per detail
                $det->issuenote_detail = $noteDetailsInput[$detailId] ?? null;

                // Lokasi
                $det->location_id     = $src->location_id;
                $det->sub_location_id = $src->sub_location_id;

                // Budget (copy dari SPB detail bila ada)
                $det->budget_perpost              = $src->budget_perpost ?? null;
                $det->budget_cpny_id              = $src->budget_cpny_id ?? null;
                $det->budget_business_unit_id     = $src->budget_business_unit_id ?? null;
                $det->budget_department_fin_id    = $src->budget_department_fin_id ?? null;
                $det->budget_account_id           = $src->budget_account_id ?? null;
                $det->budget_activity_id          = $src->budget_activity_id ?? null;
                $det->budget_activity_descr       = $src->budget_activity_descr ?? null;

                // Issue / return fields
                $det->reason_code     = null;
                $det->base_issue_qty  = $det->base_qty; // sama dengan base_qty
                $det->qty_return      = 0;
                $det->base_qty_return = 0;
                $det->ref_issueid    = null;

                $det->status          = 'P';
                $det->created_by      = $username;
                $det->created_at      = $now;
                $det->save();

                // simpan ke koleksi untuk diposting ke SPB
                $createdDetails->push($det);

                $totalIssueQty  += $qtyIssue;
                $grandTotalCost += $lineCost;
            }

            if ($totalIssueQty <= 0) {
                throw new \RuntimeException('Qty Issue minimal satu baris harus > 0.');
            }

            // ===== Update total di header Issue =====
            $header->totalissueqty       = $totalIssueQty;
            $header->grandtotalcost      = $grandTotalCost;
            $header->totalreturnissueqty = 0;
            $header->save();

            // ====== POSTING KE SPB LANGSUNG DI SINI ======
            // Akan mengupdate:
            // - TrSPBdetail: issue_qty, base_issue_qty, return_qty, dsb
            // - TrSPB: totalspbqty, totalissueqty, totalreturnqty, totalsppbqty,
            //          totalcompleteqty, status_issue, status_sppb
            $this->applyIssuePostingToSpb($header, $createdDetails, $user, $now);

            // === Generate Approval Issue ===
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
                $header->completed_at = $now;
                $header->save();
            }

            // ===== Attachments (opsional) =====
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $issueid,
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

            // ===== Notif approver pertama =====
            $eid = \Vinkla\Hashids\Facades\Hashids::encode($header->id);
            $approvalCtl->notifyFirstApprover(
                $issueid,
                $doctype,
                $header->status, // 'P'
                'Issue',
                url('/showissue/' . $eid),
                [
                    'info'      => 'Request from user ' . ($header->user_peminta ?? '-'),
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


        // ===== Link ke SPB (opsional) -> /showissue/{hash}
        $spbUrl = null;
        if (!empty($iss->spbid)) {
            $spbId = TrSPB::where('spbid', $iss->spbid)->value('id');
            if ($spbId) {
                $spbHash = Hashids::encode($spbId);
                $spbUrl  = url("/showspbs/{$spbHash}");
            }
        }

        // convenience id terenkripsi untuk share (kalau perlu)
        $eid_issueid = Hashids::encode($iss->id);

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload     = $iss->created_by === $loginUsername;

        return view('pages.issue.showissue', [
            'iss'         => $iss,
            'issdetail'   => $issdetail,
            'hash'        => $hash,
            'eid_issueid' => $eid_issueid,
            'spbUrl'      => $spbUrl,
            'canUpload'      => $canUpload,
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
        $now      = Carbon::now();
        $user     = $request->user();
        $username = $user->username ?? 'system';

        // ===== Temukan Issue by hash (eid) atau fallback ke issueid langsung =====
        $ids = \Vinkla\Hashids\Facades\Hashids::decode($hash);

        if (!empty($ids)) {
            $iss = TrIssue::where('id', $ids[0])->first();
        } else {
            // fallback: jika {hash} ternyata issueid
            $iss = TrIssue::where('issueid', $hash)->first();
        }

        if (!$iss) {
            return response()->json(['message' => 'Issue not found'], 404);
        }

        // Batasan: hanya creator yang boleh update saat status revise (D)
        if (strtoupper((string)$iss->status) !== 'D' || (string)$iss->created_by !== (string)$username) {
            return response()->json(['message' => "You can't update this document"], 403);
        }

        // ===== Ambil SPB header & detail untuk validasi (TANPA update dulu) =====
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
        $spbDetails = TrSPBdetail::where('spbid', $spbid)->get();

        // ===== Ambil detail ISSUE yang ada sekarang (keyBy id detail issue) =====
        /** @var \Illuminate\Support\Collection|TrIssuedetail[] $issDetails */
        $issDetails = TrIssuedetail::where('issueid', $iss->issueid)->get()->keyBy('id');

        // ===== Ambil input sama seperti di form editIssue =====
        // DIHARAPKAN: name="qty_issue[{{ $issue_detail_id }}]"
        $qtyIssueInput    = (array) $request->input('qty_issue', []);        // [issue_detail_id => qty]
        $siteInput        = (array) $request->input('siteid',    []);        // [issue_detail_id => siteid]
        $noteDetailsInput = (array) $request->input('issuenote_detail', []); // [issue_detail_id => note]

        // Minimal satu baris >= 0 (boleh 0 tapi harus ada baris)
        $hasAnyQty = false;
        foreach ($qtyIssueInput as $k => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty >= 0) { $hasAnyQty = true; break; }
        }
        if (!$hasAnyQty) {
            return response()->json(['message' => 'Qty Issue minimal satu baris harus diisi (boleh 0).'], 422);
        }

        // ===== VALIDASI per baris terhadap SPB =====
        foreach ($qtyIssueInput as $detailId => $v) {
            $qty = (float) str_replace(',', '.', (string)$v);
            if ($qty <= 0) continue;

            /** @var TrIssuedetail|null $det */
            $det = $issDetails->get((int)$detailId);
            if (!$det) {
                return response()->json(['message' => "Detail Issue (ID: {$detailId}) tidak ditemukan."], 422);
            }

            // Cari pasangan SPB detail berdasarkan inventory + uom
            /** @var TrSPBdetail|null $src */
            $src = $spbDetails->first(function ($row) use ($det) {
                return (string)$row->inventoryid === (string)$det->inventoryid
                    && (string)$row->uom === (string)$det->uom;
            });

            if (!$src) {
                return response()->json([
                    'message' => "Tidak bisa menemukan pasangan SPB untuk item {$det->inventoryid} (UOM {$det->uom})."
                ], 422);
            }

            $spbQty   = (float) ($src->qty ?? 0);
            $issued   = (float) ($src->issue_qty ?? 0);
            $returned = (float) ($src->return_qty ?? 0);

            // open untuk ISSUE = qty - issue_qty + return_qty
            $open = $spbQty - $issued + $returned;
            if ($open < 0) $open = 0;

            if ($qty > $open + 1e-9) {
                return response()->json([
                    'message' => "Qty Issue untuk item {$src->inventoryid} melebihi sisa open ({$open})."
                ], 422);
            }
        }

        // ===== Siapkan info approval line =====
        $doctype = 'IS'; // issuetype Issue
        $cpnyid  = $spb->cpny_id ?? $iss->cpny_id;
        $deptid  = 'WAREHOUSE';

        $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyid, $deptid);

        // ====== EXEC UPDATE DALAM TRANSAKSI ======
        return \DB::connection('pgsql')->transaction(function () use (
            $request,
            $now,
            $user,
            $iss,
            $spb,
            $spbid,
            $spbDetails,
            $issDetails,
            $qtyIssueInput,
            $siteInput,
            $noteDetailsInput,
            $doctype,
            $cpnyid,
            $deptid,
            $username,
            $approvalCtl
        ) {
            $issueid = $iss->issueid;

            // (Opsional) bisa rollback dulu ke SPB di tempat lain sebelum status D,
            // di sini kita anggap SPB sudah "bersih" dari issue ini.

            $totalIssueQty  = 0.0;
            $grandTotalCost = 0.0;
            $createdDetails = collect();

            /** @var TrIssuedetail $det */
            foreach ($issDetails as $detailId => $det) {
                // qty baru dari form; jika tidak ada di form, pakai qty lama
                $qtyRecRaw = $qtyIssueInput[$detailId] ?? $det->issue_qty ?? 0;
                $qtyIssue  = (float) str_replace(',', '.', (string)$qtyRecRaw);
                if ($qtyIssue < 0) $qtyIssue = 0;

                $siteFromForm = isset($siteInput[$detailId]) ? trim((string)$siteInput[$detailId]) : null;
                $noteFromForm = $noteDetailsInput[$detailId] ?? null;

                // Cari pasangan SPB detail dari inventory + uom
                $src = $spbDetails->first(function ($row) use ($det) {
                    return (string)$row->inventoryid === (string)$det->inventoryid
                        && (string)$row->uom === (string)$det->uom;
                });

                // Kalau tidak ketemu (harusnya sudah dicek di atas), skip saja
                if (!$src) {
                    continue;
                }

                $unitCost = (float) ($src->unitcost ?? $det->unitcost ?? 0);
                $baseMult = $src->base_multiplier ?? $det->base_multiplier ?? 1;

                $lineCost = $unitCost * $qtyIssue;

                // Update detail issue
                $det->spbid           = $spbid;
                $det->spb_no          = $src->spb_no ?? $det->spb_no;

                $det->qty             = $qtyIssue;
                $det->issue_qty       = $qtyIssue;
                $det->uom             = $src->uom ?? $det->uom;

                $det->base_multiplier = $baseMult;
                $det->base_qty        = $qtyIssue * $baseMult;
                $det->base_uom        = $src->base_uom ?? $det->base_uom ?? $det->uom;

                $det->unitcost        = $unitCost;
                $det->totalcost       = $lineCost;

                if (!is_null($siteFromForm) && $siteFromForm !== '') {
                    $det->siteid = $siteFromForm;
                }

                $det->issuenote_detail = $noteFromForm;

                $det->status      = 'P';
                $det->updated_by  = $username;
                $det->updated_at  = $now;
                $det->save();

                if ($qtyIssue > 0) {
                    $createdDetails->push($det);
                    $totalIssueQty  += $qtyIssue;
                    $grandTotalCost += $lineCost;
                }
            }

            if ($totalIssueQty <= 0) {
                throw new \RuntimeException('Qty Issue minimal satu baris harus > 0.');
            }

            // 3) UPDATE HEADER Issue
            $iss->issuenote            = (string) $request->input('issuenote', '');
            $iss->grandtotalcost       = $grandTotalCost;
            $iss->totalissueqty        = $totalIssueQty;
            $iss->totalreturnissueqty  = 0;
            $iss->status               = 'P';      // kembali ke Waiting Approval
            $iss->completed_by         = null;
            $iss->completed_at         = null;
            $iss->updated_by           = $username;
            $iss->updated_at           = $now;
            $iss->save();

            // 4) POSTING KE SPB LAGI BERDASARKAN DETAIL BARU
            $this->applyIssuePostingToSpb($iss, $createdDetails, $user, $now);

            // 5) Regenerate approval line untuk Issue revisi ini
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

            // 6) Attachment baru (opsional)
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $issueid,
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

            // 7) Email / notif approver pertama
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
         $woid = optional($spb)->woid;

        // Detail issue (barang yang dikeluarkan)
        $issdetails = TrIssuedetail::where('issueid', $iss->issueid)
            ->orderBy('issue_no')
            ->get();

        // Company (untuk header)
        $company = MsCompany::where('cpny_id', $iss->cpny_id)->first();

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
             'woid'           => $woid,
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
                    // panggil fungsi posting ke SPB
                    // $this->applyIssuePostingToSpb($issue, $issDetails, $user, $now);

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
            // sementara untuk debug
            return response()->json([
                'success' => false,
                'result'  => $result,      // <--- liat isi lengkapnya
                'message' => $result['message'] ?? 'Approve failed',
            ], 403);
        }

        return response()->json(['success'=>true,'message'=>'Task approved successfully']);

    }


    public function rejectIssue(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'IS';

        $issue = TrIssue::with('creator')->where('issueid', $docid)->first();
        if (!$issue) return response()->json(['success'=>false,'message'=>'Issue not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($issue->id);
        $docUrl   = url('/showissues/' . $eid);
        $fullname = data_get($issue, 'creator.name') ?: $issue->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $issue->issueid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($issue, $fullname, $docUrl, $user) {
                DB::transaction(function () use ($issue, $fullname, $docUrl, $now, $user) {
                    // 1) Rollback qty ke SPB
                    $this->rollbackIssuePostingToSpb($issue, $user, $now);

                    // 2) Update status Issue
                    $issue->status       = 'R';
                    $issue->completed_by = $user->username ?? auth()->user()->username;
                    $issue->completed_at = $now;
                    $issue->save();

                    // (opsional) detail jadi R
                    // TrIssuedetail::where('issueid', $issue->issueid)->update(['status' => 'R']);

                    // 3) Notif requester
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
                            'info'     => $issue->issuenote,
                            'fullname' => $fullname,
                            'name'     => $fullname,
                            'createdby'=> $fullname,
                        ]
                    );

                    // 4) Simpan komentar (jika ada)
                    try {
                        app('App\Http\Controllers\SendCommentController')->sendmsg($issue->id, 'IS', request());
                    } catch (\Throwable $e) {}
                });
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

        $issue = TrIssue::with('creator')->where('issueid', $docid)->first();
        if (!$issue) return response()->json(['success'=>false,'message'=>'Issue not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($issue->id);
        $docUrl   = url('/showissues/' . $eid);
        $fullname = data_get($issue, 'creator.name') ?: $issue->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $issue->issueid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($issue, $fullname, $docUrl, $user) {
                DB::transaction(function () use ($issue, $fullname, $docUrl, $now, $user) {
                    // 1) Rollback qty ke SPB
                    $this->rollbackIssuePostingToSpb($issue, $user, $now);

                    // 2) HEADER Issue -> D
                    $issue->status       = 'D';
                    $issue->completed_by = $user->username ?? auth()->user()->username;
                    $issue->completed_at = $now;
                    $issue->save();

                    // (opsional) DETAIL -> D
                    // TrIssuedetail::where('issueid', $issue->issueid)->update(['status' => 'D']);

                    // 3) Email ke requester
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
                            'info'     => $issue->issuenote,
                            'fullname' => $fullname,
                            'name'     => $fullname,
                            'createdby'=> $fullname,
                        ]
                    );

                    // 4) Simpan komentar (jika ada)
                    try {
                        app('App\Http\Controllers\SendCommentController')->sendmsg($issue->id, 'IS', request());
                    } catch (\Throwable $e) {}
                });
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
        // pastikan jalan di koneksi yang sama (pgsql)
        DB::connection('pgsql')->transaction(function () use ($issue, $issueDetails, $user, $now) {

            // Lock header SPB
            $spb = TrSPB::where('spbid', $issue->spbid)->lockForUpdate()->first();
            if (!$spb) {
                throw new \RuntimeException('SPB terkait tidak ditemukan.');
            }

            // Lock detail SPB
            $spbDetailRows = TrSPBdetail::where('spbid', $issue->spbid)->lockForUpdate()->get();
            if ($spbDetailRows->isEmpty()) {
                return;
            }

            // index helper
            $spbBySpbNo     = $spbDetailRows->keyBy('spb_no');
            $spbByKey       = $spbDetailRows->keyBy(fn($r) => (($r->inventoryid ?? '') . '|' . ($r->uom ?? '')));
            $spbByInventory = $spbDetailRows->groupBy('inventoryid');

            $type = strtoupper((string) $issue->issuetype);
            $isIssue  = $type === 'IS';
            $isReturn = $type === 'RI';

            foreach ($issueDetails as $rd) {

                // ==========================================================
                // Ambil qty & baseQty (robust)
                // ==========================================================
                $qty = 0.0;
                $baseQty = 0.0;

                $bm = (int) (is_numeric($rd->base_multiplier) ? $rd->base_multiplier : 1);
                if ($bm <= 0) $bm = 1;

                if ($isIssue) {
                    // issue qty
                    $qty = (float) ($rd->issue_qty ?? 0);
                    if ($qty <= 0) $qty = (float) ($rd->qty ?? 0);

                    // base issue qty
                    $baseQty = (float) ($rd->base_issue_qty ?? 0);
                    if ($baseQty <= 0) $baseQty = (float) ($rd->base_qty ?? 0);
                    if ($baseQty <= 0) $baseQty = $qty * $bm;

                } elseif ($isReturn) {
                    // return qty
                    $qty = (float) ($rd->qty_return ?? 0);
                    if ($qty <= 0) $qty = (float) ($rd->qty ?? 0);

                    // base return qty
                    $baseQty = (float) ($rd->base_qty_return ?? 0);
                    if ($baseQty <= 0) $baseQty = (float) ($rd->base_qty ?? 0);
                    if ($baseQty <= 0) $baseQty = $qty * $bm;

                } else {
                    // issuetype lain tidak diproses di sini
                    continue;
                }

                if ($qty <= 0) continue;

                // ==========================================================
                // Cari pasangan SPB detail
                // ==========================================================
                $spbDet = null;

                // 1) by spb_no
                if (!empty($rd->spb_no) && $spbBySpbNo->has($rd->spb_no)) {
                    $spbDet = $spbBySpbNo->get($rd->spb_no);
                }

                // 2) by inventory + uom
                if (!$spbDet) {
                    $key = ($rd->inventoryid ?? '') . '|' . ($rd->uom ?? '');
                    $spbDet = $spbByKey->get($key);
                }

                // 3) fallback by inventory only
                if (!$spbDet) {
                    $bucket = $spbByInventory->get($rd->inventoryid);
                    $spbDet = $bucket ? $bucket->first() : null;
                }

                if (!$spbDet) continue;

                // ==========================================================
                // Update issue/return qty di SPB detail
                // - issue_qty di SPB adalah NET issue (sudah dikurangi return)
                // ==========================================================
                if ($isIssue) {
                    $spbDet->issue_qty      = (float) ($spbDet->issue_qty ?? 0) + $qty;
                    $spbDet->base_issue_qty = (float) ($spbDet->base_issue_qty ?? 0) + $baseQty;

                } else { // RI
                    $spbDet->return_qty      = (float) ($spbDet->return_qty ?? 0) + $qty;
                    $spbDet->base_return_qty = (float) ($spbDet->base_return_qty ?? 0) + $baseQty;

                    // NET issue turun
                    $spbDet->issue_qty      = max(0, (float) ($spbDet->issue_qty ?? 0) - $qty);
                    $spbDet->base_issue_qty = max(0, (float) ($spbDet->base_issue_qty ?? 0) - $baseQty);
                }

                // ==========================================================
                // MANUAL COMPLETE RULE
                // fulfilled = issue_net + sppb + manual_close(spb_completeqty)
                // applyIssuePostingToSpb hanya:
                // - update issue/return
                // - set status berdasarkan fulfilled
                // - TIDAK menurunkan spb_completeqty
                // - tidak memaksa spb_completeqty naik juga (itu hanya lewat fitur manual close)
                // ==========================================================
                $reqQty   = (float) ($spbDet->qty ?? 0);

                $issuedNet = (float) ($spbDet->issue_qty ?? 0);
                $sppbQty   = (float) ($spbDet->sppb_qty ?? 0);
                $closed    = (float) ($spbDet->spb_completeqty ?? 0);

                $fulfilled = $issuedNet + $sppbQty + $closed;

                $spbDet->status = ($reqQty > 0 && $fulfilled >= $reqQty) ? 'C' : 'P';

                $spbDet->updated_by = $user->username ?? 'system';
                $spbDet->updated_at = $now;
                $spbDet->save();
            }

            // ==========================================================
            // Refresh header totals
            // totalcompleteqty = SUM( LEAST(qty, issue_net + sppb + manual_close) )
            // ==========================================================
            $agg = TrSPBdetail::where('spbid', $issue->spbid)
                ->selectRaw('COALESCE(SUM(qty),0) AS total_spbqty')
                ->selectRaw('COALESCE(SUM(issue_qty),0) AS total_issueqty')      // NET issue
                ->selectRaw('COALESCE(SUM(return_qty),0) AS total_returnqty')
                ->selectRaw('COALESCE(SUM(sppb_qty),0) AS total_sppbqty')
                ->selectRaw('
                    COALESCE(
                        SUM(
                            LEAST(
                                qty,
                                COALESCE(issue_qty,0) + COALESCE(sppb_qty,0) + COALESCE(spb_completeqty,0)
                            )
                        ),
                    0) AS total_completeqty
                ')
                ->first();

            $spb->totalspbqty      = (float) $agg->total_spbqty;
            $spb->totalissueqty    = (float) $agg->total_issueqty;
            $spb->totalreturnqty   = (float) $agg->total_returnqty;
            $spb->totalsppbqty     = (float) $agg->total_sppbqty;
            $spb->totalcompleteqty = (float) $agg->total_completeqty;

            // status_issue berdasarkan fulfilled (totalcompleteqty)
            if ($spb->totalcompleteqty <= 0) {
                $spb->status_issue = 'Open';
            } elseif ($spb->totalspbqty > 0 && $spb->totalcompleteqty >= $spb->totalspbqty) {
                $spb->status_issue = 'Completed';
            } else {
                $spb->status_issue = 'Partial';
            }

            $spb->updated_by = $user->username ?? 'system';
            $spb->updated_at = $now;
            $spb->save();
        });
    }


    protected function rollbackIssuePostingToSpb(TrIssue $issue, User $user, Carbon $now): void
    {
        $issueDetails = TrIssuedetail::where('issueid', $issue->issueid)->get();
        if ($issueDetails->isEmpty()) return;

        DB::connection('pgsql')->transaction(function () use ($issue, $issueDetails, $user, $now) {

            $spb = TrSPB::where('spbid', $issue->spbid)->lockForUpdate()->first();
            if (!$spb) throw new \RuntimeException('SPB terkait tidak ditemukan saat rollback Issue.');

            $spbDetailRows = TrSPBdetail::where('spbid', $issue->spbid)->lockForUpdate()->get();
            if ($spbDetailRows->isEmpty()) return;

            $spbBySpbNo     = $spbDetailRows->keyBy('spb_no');
            $spbByKey       = $spbDetailRows->keyBy(fn($r) => (($r->inventoryid ?? '') . '|' . ($r->uom ?? '')));
            $spbByInventory = $spbDetailRows->groupBy('inventoryid');

            $type = strtoupper((string) $issue->issuetype);
            $isIssue  = $type === 'IS';
            $isReturn = $type === 'RI';

            foreach ($issueDetails as $rd) {

                $qty = 0.0;
                $baseQty = 0.0;

                $bm = (int) (is_numeric($rd->base_multiplier) ? $rd->base_multiplier : 1);
                if ($bm <= 0) $bm = 1;

                if ($isIssue) {
                    $qty = (float) ($rd->issue_qty ?? 0);
                    if ($qty <= 0) $qty = (float) ($rd->qty ?? 0);

                    $baseQty = (float) ($rd->base_issue_qty ?? 0);
                    if ($baseQty <= 0) $baseQty = (float) ($rd->base_qty ?? 0);
                    if ($baseQty <= 0) $baseQty = $qty * $bm;

                } elseif ($isReturn) {
                    $qty = (float) ($rd->qty_return ?? 0);
                    if ($qty <= 0) $qty = (float) ($rd->qty ?? 0);

                    $baseQty = (float) ($rd->base_qty_return ?? 0);
                    if ($baseQty <= 0) $baseQty = (float) ($rd->base_qty ?? 0);
                    if ($baseQty <= 0) $baseQty = $qty * $bm;
                } else {
                    continue;
                }

                if ($qty <= 0) continue;

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

                // ===== rollback numeric fields (TIDAK sentuh spb_completeqty) =====
                if ($isIssue) {
                    $spbDet->issue_qty      = max(0, (float)($spbDet->issue_qty ?? 0) - $qty);
                    $spbDet->base_issue_qty = max(0, (float)($spbDet->base_issue_qty ?? 0) - $baseQty);
                } else { // RI
                    $spbDet->return_qty      = max(0, (float)($spbDet->return_qty ?? 0) - $qty);
                    $spbDet->base_return_qty = max(0, (float)($spbDet->base_return_qty ?? 0) - $baseQty);

                    $spbDet->issue_qty      = (float)($spbDet->issue_qty ?? 0) + $qty;
                    $spbDet->base_issue_qty = (float)($spbDet->base_issue_qty ?? 0) + $baseQty;
                }

                // ===== status per baris pakai fulfilled =====
                $reqQty    = (float) ($spbDet->qty ?? 0);
                $issuedNet = (float) ($spbDet->issue_qty ?? 0);
                $sppbQty   = (float) ($spbDet->sppb_qty ?? 0);
                $closed    = (float) ($spbDet->spb_completeqty ?? 0);

                $fulfilled = $issuedNet + $sppbQty + $closed;

                $spbDet->status = ($reqQty > 0 && $fulfilled >= $reqQty) ? 'C' : 'P';

                $spbDet->updated_by = $user->username ?? 'system';
                $spbDet->updated_at = $now;
                $spbDet->save();
            }

            // ===== header totals =====
            $agg = TrSPBdetail::where('spbid', $issue->spbid)
                ->selectRaw('COALESCE(SUM(qty),0) AS total_spbqty')
                ->selectRaw('COALESCE(SUM(issue_qty),0) AS total_issueqty')
                ->selectRaw('COALESCE(SUM(return_qty),0) AS total_returnqty')
                ->selectRaw('COALESCE(SUM(sppb_qty),0) AS total_sppbqty')
                ->selectRaw('
                    COALESCE(
                        SUM(
                            LEAST(
                                qty,
                                COALESCE(issue_qty,0) + COALESCE(sppb_qty,0) + COALESCE(spb_completeqty,0)
                            )
                        ),
                    0) AS total_completeqty
                ')
                ->first();

            $spb->totalspbqty      = (float) $agg->total_spbqty;
            $spb->totalissueqty    = (float) $agg->total_issueqty;
            $spb->totalreturnqty   = (float) $agg->total_returnqty;
            $spb->totalsppbqty     = (float) $agg->total_sppbqty;
            $spb->totalcompleteqty = (float) $agg->total_completeqty;

            if ($spb->totalcompleteqty <= 0) {
                $spb->status_issue = 'Open';
            } elseif ($spb->totalspbqty > 0 && $spb->totalcompleteqty >= $spb->totalspbqty) {
                $spb->status_issue = 'Completed';
            } else {
                $spb->status_issue = 'Partial';
            }

            $spb->updated_by = $user->username ?? 'system';
            $spb->updated_at = $now;
            $spb->save();
        });
    }





    /**
     * Helper kecil supaya bisa dipakai di closure di atas
     */
    private function clamp(float $val, float $min, float $max): float
    {
        return max($min, min($max, $val));
    }




    public function createReturn_xxx(Request $request)
    {

        $id  = Hashids::decode($request->id)[0] ?? null;
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

    public function createReturn(Request $request)
    {
        $decoded = Hashids::decode($request->id);
        $id      = $decoded[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // simpan encoded id untuk view (kalau view perlu)
        $eid = $request->id;

        // Header issue asal (dokumen yang akan direturn)
        $iss = TrIssue::findOrFail($id);

        /**
         * Ambil detail issue asal sebagai "batas maksimal return".
         * Di tabel detail kamu yang ada adalah issue_qty / base_issue_qty.
         */
        $origDetails = TrIssuedetail::query()
            ->select([
                'id',
                'issueid',
                'issue_no',
                'inventoryid',
                'inventory_descr',
                'uom',
                'siteid',
                'location_id',
                'sub_location_id',

                // angka dasar (maks qty yang bisa direturn)
                DB::raw("COALESCE(issue_qty::numeric, 0)::numeric AS qty_issued"),
                DB::raw("COALESCE(base_issue_qty::numeric, 0)::numeric AS base_qty_issued"),

                // multiplier kadang varchar kosong
                DB::raw("COALESCE(NULLIF(type_multiplier, '')::int, 1) AS type_multiplier"),
                DB::raw("COALESCE(NULLIF(base_multiplier, '')::int, 1) AS base_multiplier"),
            ])
            ->where('issueid', $iss->issueid)
            // kalau di data kamu issue detail bisa campur, boleh aktifkan filter ini:
            // ->where(function($q){ $q->whereNull('issuetype')->orWhere('issuetype','issue'); })
            ->orderBy('issue_no')
            ->get();

        /**
         * Total qty_return yang SUDAH pernah dibuat dari dokumen return
         * yang mereferensikan dokumen asal ini.
         *
         * Referensi yang benar: tr_issue.ref_issueid = issue asal (mis. $iss->issueid)
         */
        $returnedAgg = TrIssuedetail::query()
            ->select([
                'inventoryid',
                'uom',
                'siteid',
                'location_id',
                'sub_location_id',
                DB::raw('SUM(COALESCE(qty_return,0))::numeric AS sum_returned'),
            ])
            ->whereIn('issueid', function ($q) use ($iss) {
                $q->select('issueid')
                    ->from('tr_issue')
                    ->whereRaw('LOWER(issuetype) = ?', ['return'])
                    ->where('ref_issueid', $iss->issueid); // ✅ FIX: bukan ref_issueid
            })
            ->groupBy('inventoryid', 'uom', 'siteid', 'location_id', 'sub_location_id')
            ->get()
            ->keyBy(function ($r) {
                return ($r->inventoryid ?? '') . '|'
                    . ($r->uom ?? '') . '|'
                    . ($r->siteid ?? '') . '|'
                    . ($r->location_id ?? '') . '|'
                    . ($r->sub_location_id ?? '');
            });

        // Hitung sisa return per baris asal; tampilkan hanya yang masih > 0
        $details = $origDetails
            ->map(function ($row) use ($returnedAgg) {
                $key = ($row->inventoryid ?? '') . '|'
                    . ($row->uom ?? '') . '|'
                    . ($row->siteid ?? '') . '|'
                    . ($row->location_id ?? '') . '|'
                    . ($row->sub_location_id ?? '');

                $sudahReturn = (float) (optional($returnedAgg->get($key))->sum_returned ?? 0);

                $qtyIssued = (float) $row->qty_issued;
                $sisa      = max($qtyIssued - $sudahReturn, 0);

                $row->qty_sisa_return = $sisa;
                $row->qty             = $sisa; // kompatibel dengan view yang pakai $d->qty

                return $row;
            })
            ->filter(fn ($r) => (float) $r->qty_sisa_return > 0)
            ->values();

        if ($details->isEmpty()) {
            return back()->with('warning', 'Semua item pada issue ini sudah tidak memiliki sisa untuk di-return.');
        }

        // referensi dokumen asal (pakai issueid sebagai nomor dokumen)
        $ref_issueid = $iss->issueid;

        return view('pages.issue.return_create', [
            'iss'         => $iss,
            'details'     => $details,
            'eid'         => $eid,
            'ref_issueid'=> $ref_issueid, // ✅ FIX: konsisten dengan model
        ]);
    }


    // Simpan dokumen return
    public function storeReturn(Request $request)
    {
        // $user = Auth::user();
        // $username = $user ? $user->username : 'system';

        $eid = (string) $request->input('iss', '');
        $id  = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $src = TrIssue::where('id', $id)->firstOrFail();

        $qtyInput = (array) $request->input('qty_return', []); // [detail_id => qty]
        $notes    = trim((string) $request->input('return_note', ''));

        // minimal satu qty > 0
        $hasAny = false;
        foreach ($qtyInput as $v) {
            $q = (float) str_replace(',', '.', (string) $v);
            if ($q > 0) { $hasAny = true; break; }
        }
        if (!$hasAny) {
            return back()->withErrors(['Minimal satu baris Qty Return > 0.'])->withInput();
        }


        $now   = Carbon::now();
        // $year  = (int) $now->year;
        // $month = str_pad((string)$now->month, 2, '0', STR_PAD_LEFT);
        $doctype  = 'IS';
        $user     = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt        = Carbon::now();
        $year      = (int) $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        $cpnyid = $src->cpny_id ?? null;
        $deptid = 'WAREHOUSE';

        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyid, $deptid);

        DB::beginTransaction();
        try {
            // =======================
            // AUTONUMBER
            // =======================
            // $autonbr = Autonbr::lockForUpdate()
            //     ->where('doctype', $doctype)
            //     ->where('year', $year)
            //     ->where('month', $month)
            //     ->first();

            // if (!$autonbr) {
            //     $autonbr = Autonbr::create([
            //         'doctype' => $doctype,
            //         'year'    => $year,
            //         'month'   => $month,
            //         'status'  => 'A',
            //         'number'  => 1,
            //     ]);
            //     $urut = 1;
            // } else {
            //     $urut = ((int)($autonbr->number ?? 0)) + 1;
            //     $autonbr->update(['number' => $urut]);
            // }

            // $issueid = $doctype . substr((string)$year, 2) . $month . sprintf('%04d', $urut);
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'ISSUE'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string)$year, 2) . $month;   // YYMM
            $issueid  = $doctype . $tglbln . sprintf("%04d", $urutan);

            // =======================
            // LOAD SOURCE DETAILS (LOCK)
            // =======================
            $srcDetails = TrIssuedetail::where('issueid', $src->issueid)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($srcDetails->isEmpty()) {
                DB::rollBack();
                return back()->withErrors(['Source Issue tidak memiliki detail.'])->withInput();
            }

            // =======================
            // HITUNG TOTAL RETURN SUDAH ADA (untuk validasi sisa)
            // =======================
            $returnedAgg = TrIssuedetail::query()
                ->select([
                    'ref_issueid',
                    'inventoryid',
                    'uom',
                    'siteid',
                    'location_id',
                    'sub_location_id',
                    DB::raw('SUM(COALESCE(qty_return,0)) AS sum_returned'),
                ])
                ->where('ref_issueid', $src->issueid)
                ->where('issuetype', 'RI')
                ->groupBy('ref_issueid','inventoryid','uom','siteid','location_id','sub_location_id')
                ->get()
                ->keyBy(function($r){
                    return ($r->inventoryid ?? '').'|'.($r->uom ?? '').'|'.($r->siteid ?? '').'|'.($r->location_id ?? '').'|'.($r->sub_location_id ?? '');
                });

            // =======================
            // HEADER RETURN (COPY SOURCE + OVERRIDE)
            // =======================
            $hdr = new TrIssue();

            // wajib
            $hdr->issueid   = $issueid;
            $hdr->issuedate = $now;
            $hdr->issuetype = 'RI';
            $hdr->ref_issueid = $src->issueid;

            // copy header penting dari source (biar tidak kosong)
            $hdr->spbid         = $src->spbid;
            $hdr->woid          = $src->woid ?? null;
            $hdr->cpny_id       = $src->cpny_id;
            $hdr->department_id = $src->department_id;
            $hdr->user_peminta  = $src->user_peminta;

            // budget/nominal header (kalau ada)
            $hdr->budget_perpost   = $src->budget_perpost ?? null;
            $hdr->grandtotalcost   = 0;              // return biasanya 0 (atau kalau mau hitung, bisa isi nanti)
            $hdr->totalissueqty    = 0;              // return header: issue qty 0
            $hdr->totalreturnissueqty = 0;

            // note return
            $hdr->issuenote   = $notes !== '' ? $notes : ($src->issuenote ?? null);

            $hdr->status      = 'P';
            $hdr->created_by  = $username;
            $hdr->created_at  = $now;
            $hdr->save();

            // =======================
            // DETAIL RETURN (COPY SOURCE ROW)
            // =======================
            $line = 0;
            $totalReturn = 0.0;
            $createdDetails = collect();

            foreach ($qtyInput as $detailId => $raw) {
                $qty = (float) str_replace(',', '.', (string) $raw);
                if ($qty <= 0) continue;

                $srcDet = $srcDetails->get((int)$detailId);
                if (!$srcDet) continue;

                // ==== VALIDASI: qty return <= sisa (issue_qty - returned sebelumnya)
                $key = ($srcDet->inventoryid ?? '').'|'.($srcDet->uom ?? '').'|'.($srcDet->siteid ?? '').'|'.($srcDet->location_id ?? '').'|'.($srcDet->sub_location_id ?? '');
                $sudahReturn = (float) optional($returnedAgg->get($key))->sum_returned ?? 0.0;

                $issued = (float) ($srcDet->issue_qty ?? $srcDet->qty ?? 0); // prefer issue_qty
                $sisa   = max($issued - $sudahReturn, 0);

                if ($qty - $sisa > 1e-9) {
                    DB::rollBack();
                    return back()->withErrors([
                        "Qty Return untuk {$srcDet->inventoryid} melebihi sisa return. Sisa: ".number_format($sisa,2,'.',',')
                    ])->withInput();
                }

                $line++;

                $det = new TrIssuedetail();

                // relasi & nomor
                $det->issueid  = $issueid;
                $det->issue_no = $line;

                // copy identitas dokumen asal
                $det->spbid  = $srcDet->spbid;
                $det->spb_no = $srcDet->spb_no;

                // copy item + lokasi
                $det->inventoryid       = $srcDet->inventoryid;
                $det->inventory_descr   = $srcDet->inventory_descr;
                $det->uom               = $srcDet->uom;

                $det->siteid            = $srcDet->siteid;
                $det->location_id       = $srcDet->location_id;
                $det->sub_location_id   = $srcDet->sub_location_id;

                // copy multiplier/base
                $det->type_multiplier   = $srcDet->type_multiplier;
                $det->base_multiplier   = $srcDet->base_multiplier ?: 1;
                $det->base_uom          = $srcDet->base_uom ?: $srcDet->uom;

                // copy costing (kalau dibutuhkan)
                $det->unitcost          = $srcDet->unitcost;
                $det->totalcost         = 0; // return biasanya 0 (atau $qty * unitcost kalau mau)
                $det->issuenote_detail  = $srcDet->issuenote_detail;

                // copy budget detail
                $det->budget_perpost           = $srcDet->budget_perpost;
                $det->budget_cpny_id           = $srcDet->budget_cpny_id;
                $det->budget_business_unit_id  = $srcDet->budget_business_unit_id;
                $det->budget_department_fin_id = $srcDet->budget_department_fin_id;
                $det->budget_account_id        = $srcDet->budget_account_id;
                $det->budget_activity_id       = $srcDet->budget_activity_id;
                $det->budget_activity_descr    = $srcDet->budget_activity_descr;

                // reason code (kalau ada field)
                $det->reason_code       = $srcDet->reason_code;

                // ===== angka return
                $det->issuetype       = 'RI';
                $det->qty             = $qty;     // supaya “qty” tidak kosong (di data kamu RI qty=3 itu OK)
                $det->base_qty        = $qty;

                $det->issue_qty       = 0;
                $det->base_issue_qty  = 0;

                $det->qty_return      = $qty;
                $det->base_qty_return = $qty;

                $det->ref_issueid     = $src->issueid;

                $det->status          = 'C';
                $det->created_by      = $username;
                $det->created_at      = $now;
                $det->save();

                $createdDetails->push($det);
                $totalReturn += $qty;
            }

            if ($totalReturn <= 0) {
                DB::rollBack();
                return back()->withErrors(['Minimal satu baris Qty Return > 0.'])->withInput();
            }

            // update total return header
            $hdr->totalreturnissueqty = $totalReturn;
            $hdr->updated_by = $username;
            $hdr->updated_at = $now;
            $hdr->save();

            // posting ke SPB / stock (kalau memang diperlukan)
            // pastikan function ini aman untuk RI
            if (method_exists($this, 'applyIssuePostingToSpb')) {
                $this->applyIssuePostingToSpb($hdr, $createdDetails, $user, $now);
            }

            // approval
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

            // attachment
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $issueid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $hdr->cpny_id,
                    'departementid' => $hdr->department_id,
                    'base_folder'   => 'att-purchasing-app/' . strtolower($doctype),
                    'created_by'    => $username,
                ];

                $files = (array) $request->file('attachments');
                app(TrAttachmentController::class)->uploadInternal($meta, $files);
            }

            DB::commit();

            return redirect()->route('issuelist')
                ->with('success', "Return {$issueid} created from {$src->issueid}. Total Qty Return: {$totalReturn}");
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('storeReturn failed', [
                'error' => $e->getMessage(),
                'issue_src' => $src->issueid ?? null,
                'user' => $username ?? null,
            ]);

            return back()
                ->withErrors([config('app.debug') ? $e->getMessage() : 'Failed to create Return'])
                ->withInput();
        }
    }









}
