<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
// use App\Models\T_Message;
// use App\Models\Attachment;
// use App\Models\M_approval;
// use App\Models\M_approval_other;
// use App\Models\T_approval;
use App\Models\Company;
use App\Models\Dept;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Site;
use App\Models\Division;
use App\Models\TrSPPB;
use App\Models\TrSPPBdetail;
use App\Models\TrSPPJ;
use App\Models\TrSPPJdetail;
use App\Models\TrSPPK;
use App\Models\TrSPPKdetail;
use App\Models\TrSPPT;
use App\Models\TrSPPTdetail;
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use App\Models\vAssignList;
use App\Models\vSppbjktOnProgress;
use App\Models\TrCS;
use App\Models\TrCSdetail;
use App\Models\BudgetDetail;
use App\Models\vCsJobs;
use App\Models\vCsRevision;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\TrBQCS;
use App\Models\TrBQCSDetail;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;  
use App\Models\TrPO; 
use App\Services\CsQtyValidator;
use App\Models\vSppbjktCompleted;



class CsJobController extends Controller
{
   
    public function CsJobs()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u = $user->username ?? '';

        $mine     = vCsJobs::where('assignpurchasing', $u)->count();
        $revision = vCsRevision::where('created_by', $u)->count();
        $all      = vCsJobs::count();
        $sppbjkt  = vSppbjktOnProgress::count();

        $completed = vSppbjktCompleted::where('completed_by', $u)->count();

        return view('pages.canvass.csjobs', compact('mine','revision','all','sppbjkt','completed'));
    }


    public function CsJobsDatasetCounts(Request $request)
    {
        $user = Auth::user();
        $u = $user->username ?? '';

        return response()->json([
            'mine'      => vCsJobs::where('assignpurchasing', $u)->count(),
            'revision'  => vCsRevision::where('created_by', $u)->count(),
            'all'       => vCsJobs::count(),
            'sppbjkt'   => vSppbjktOnProgress::count(),
            'completed' => vSppbjktCompleted::where('completed_by', $u)->count(),
        ]);
    }



    private function buildJobsJson($base, Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $doc    = (string) $request->query('doc', '');

        $columns = [
            0 => 'assigndate',
            1 => 'doc_no',
            2 => 'doc_date',
            3 => 'cpny_id',
            4 => 'created_by',
            5 => 'assignpurchasing',
            6 => 'assignby',
            7 => 'department_id',
            8 => 'keperluan',
        ];
        $orderIdx = (int) $request->input('order.0.column', 2);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'doc_date';

        $base->with('creator:username,name');

        if ($doc !== '') {
            $base->where('doc_type', $doc);
        }

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('doc_no', 'ilike', "%{$search}%")
                ->orWhere('doc_type', 'ilike', "%{$search}%")
                ->orWhere('created_by', 'ilike', "%{$search}%")
                ->orWhere('assignpurchasing', 'ilike', "%{$search}%")
                ->orWhere('assignby', 'ilike', "%{$search}%")
                ->orWhere('keperluan', 'ilike', "%{$search}%")
                ->orWhereRaw("CAST(cpny_id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("CAST(department_id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR(doc_date, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR(assigndate, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select(
                    'doc_type',
                    'src_id',
                    'doc_no',
                    'doc_date',
                    'assigndate',
                    'assignby',
                    'assignpurchasing',
                    'cpny_id',
                    'created_by',
                    'department_id',
                    'keperluan',
                    'row_id'
                )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('doc_no', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        // enrich
        $data->transform(function ($row) {
            $row->created_by_name = optional($row->creator)->name;
            // normalisasi optional field biar aman di front-end
            $row->assigndate        = $row->assigndate ?? null;
            $row->assignby          = $row->assignby ?? null;
            $row->assignpurchasing  = $row->assignpurchasing ?? null;
            $row->eid = Hashids::encode($row->src_id);
            return $row;
        });
        

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    private function buildRevisionJson(Request $request, $base)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));

        // kolom yang ada di tabel revision (TrPO)
        $columns = [
            0 => 'ponbr',
            1 => 'podate',
            2 => 'cpny_id',
            3 => 'created_by',
            4 => 'department_id',
            5 => 'csid',
            6 => 'sppbjktid',
            7 => 'vendorname'
        ];

        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'podate';

        // total rows
        $recordsTotal = (clone $base)->count();

        // search
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('ponbr', 'ilike', "%{$search}%")
                ->orWhere('cpny_id', 'ilike', "%{$search}%")
                ->orWhere('department_id', 'ilike', "%{$search}%")
                ->orWhere('csid', 'ilike', "%{$search}%")
                ->orWhere('sppbjktid', 'ilike', "%{$search}%")
                ->orWhere('vendorname', 'ilike', "%{$search}%")
                ->orWhere('created_by', 'ilike', "%{$search}%")
                ->orWhereRaw("TO_CHAR(podate, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        // select field khusus revisi
        $data = $base->select(
                    'id',
                    'ponbr',
                    'podate',
                    'cpny_id',
                    'created_by',
                    'department_id',
                    'csid',
                    'sppbjktid',
                    'vendorname'
                )
                ->orderBy($orderCol, $orderDir)
                ->skip($start)
                ->take($length)
                ->get();

        // enrichment: samakan field agar cocok dengan DataTables existing
        $data->transform(function($r){
            $r->doc_type = 'PO';   // wajib untuk tombol CreateCS
            $r->eid = \Hashids::encode($r->id); // sama seperti dokumen lain
            $r->doc_no = $r->ponbr;

            // kolom yang tidak ada → diisi null
            $r->assigndate = null;
            $r->assignpurchasing = null;
            $r->assignby = null;

            // NAME ambil dari created_by → gunakan apa adanya
            $r->created_by_name = $r->created_by;

            // DESCRIPTION pake csid dan sppbjktid
            $r->keperluan = "CSID: {$r->csid}, SPPBJKT: {$r->sppbjktid}";

            return $r;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }


    /**
     * TAB 1: CS Jobs (punya saya) -> vCsJobs where assignpurchasing = user login
     */
    public function CsJobsMineJson(Request $request)
    {
        $username = Auth::user()->username ?? '';
        $base = vCsJobs::query()->where('assignpurchasing', $username);
        return $this->buildJobsJson($base, $request);
    }

    /**
     * TAB 2: All Jobs -> vCsJobs (tanpa filter assignee)
     */
    public function CsJobsAllJson(Request $request)
    {
        $base = vCsJobs::query();
        return $this->buildJobsJson($base, $request);
    }

    /**
     * TAB 3: My Revision -> vCsRevision
     */
    public function CsJobsRevisionJson(Request $request)
    {
        $username = Auth::user()->username ?? '';

        $base = vCsRevision::query()
            ->where('created_by', $username);

        return $this->buildRevisionJson($request, $base);
    }


    /**
     * TAB 4: SPPBJKT IN Progress -> vSppbjktOnProgress
     */
    public function SppbjktOnProgressJson(Request $request)
    {
        $base = vSppbjktOnProgress::query();
        return $this->buildJobsJson($base, $request);
    }

    public function CsJobsCompletedJson(Request $request)
    {
        $user = Auth::user();
        $u = $user->username ?? '';

        $base = vSppbjktCompleted::query()
            ->where('completed_by', $u);

        // pakai builder yang sama, karena fields view completed sudah mirip:
        // doc_type, src_id, doc_no, doc_date, cpny_id, department_id, keperluan, created_by, row_id, etc.
        // note: assigndate/assignby/assignpurchasing mungkin null → aman, colSetWithoutCreate handle defaultContent
        return $this->buildJobsJson($base, $request);
    }


   
    private function baseQueryForTab(string $tab)
    {
        switch ($tab) {
            case 'mine':
                $username = Auth::user()->username ?? '';
                return vCsJobs::query()->where('assignpurchasing', $username);
            case 'all':
                return vCsJobs::query();
            case 'revision':
                $username = Auth::user()->username ?? '';
                return vCsRevision::query()->where('created_by', $username);
            case 'sppbjkt':
                return vSppbjktOnProgress::query();
            default:
                return vCsJobs::query();
        }
    }

    /**
     * GET /csjobs/counts?tab=mine|all|revision|sppbjkt
     * Kembalikan angka All/SPPB/SPPJ/SPPK/SPPT untuk tab aktif
     */
    public function CsJobsCounts(Request $request)
    {
        $tab  = $request->query('tab', 'mine');
        $base = $this->baseQueryForTab($tab);

        // total semua dokumen
        $all = (clone $base)->count();

        // pecah per doc_type
        $perType = (clone $base)
            ->select('doc_type', DB::raw('count(*) as cnt'))
            ->groupBy('doc_type')
            ->pluck('cnt', 'doc_type'); // ['SPPB'=>x, 'SPPJ'=>y, ...]

        return response()->json([
            'all'  => $all,
            'sppb' => (int)($perType['SPPB'] ?? 0),
            'sppj' => (int)($perType['SPPJ'] ?? 0),
            'sppk' => (int)($perType['SPPK'] ?? 0),
            'sppt' => (int)($perType['SPPT'] ?? 0),
        ]);
    }

   

    public function CsJobsEntryJson(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));

        $username = Auth::user()->username ?? '';

        // base query
        $base = TrCS::query()
            ->where('status', 'H')
            ->where('created_by', $username);

        $columns = [
            0 => 'csid',
            1 => 'csdate',
            2 => 'cpny_id',
            3 => 'department_id',
            4 => 'user_peminta',
            5 => 'csnote',
            6 => 'sppbjktid',
        ];

        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'csdate';

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('csid', 'ilike', "%{$search}%")
                ->orWhere('cpny_id', 'ilike', "%{$search}%")
                ->orWhere('department_id', 'ilike', "%{$search}%")
                ->orWhere('user_peminta', 'ilike', "%{$search}%")
                ->orWhere('csnote', 'ilike', "%{$search}%")
                ->orWhere('sppbjktid', 'ilike', "%{$search}%")
                ->orWhereRaw("TO_CHAR(csdate, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        // get rows
        $data = $base
            ->select(
                'id',
                'csid',
                'csdate',
                'cpny_id',
                'department_id',
                'user_peminta',
                'csnote',
                'created_by',
                'status',
                'sppbjktid'
            )
            ->orderBy($orderCol, $orderDir)
            ->orderBy('csid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        // build SPPBJKT button data
        foreach ($data as $row) {

            $row->eid = Hashids::encode($row->id);

            $docNo = $row->sppbjktid;
            $row->sppbjkt_eid = null;
            $row->sppbjkt_doc_type = null;

            if (!$docNo) continue;

            // Cari di tr_sppb
            $sppb = TrSPPB::where('sppbid', $docNo)->first();
            if ($sppb) {
                $row->sppbjkt_eid = Hashids::encode($sppb->id);
                $row->sppbjkt_doc_type = 'SPPB';
                continue;
            }

            // Cari di tr_sppj
            $sppj = TrSPPJ::where('sppjid', $docNo)->first();
            if ($sppj) {
                $row->sppbjkt_eid = Hashids::encode($sppj->id);
                $row->sppbjkt_doc_type = 'SPPJ';
                continue;
            }

            // Cari di tr_sppk
            $sppk = TrSPPK::where('sppkid', $docNo)->first();
            if ($sppk) {
                $row->sppbjkt_eid = Hashids::encode($sppk->id);
                $row->sppbjkt_doc_type = 'SPPK';
                continue;
            }

            // Cari di tr_sppt
            $sppt = TrSPPT::where('spptid', $docNo)->first();
            if ($sppt) {
                $row->sppbjkt_eid = Hashids::encode($sppt->id);
                $row->sppbjkt_doc_type = 'SPPT';
                continue;
            }
        }

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }


   

    public function CompleteRemainingOpen_xxx(string $doc, string $eid)
    {
        $ids = \Vinkla\Hashids\Facades\Hashids::decode($eid);
        abort_if(empty($ids), 404);
        $srcId = $ids[0];

        $doc = strtoupper($doc);
        abort_unless(in_array($doc, ['SPPB','SPPJ','SPPK','SPPT']), 422, 'Invalid doc type');

        DB::connection('pgsql')->beginTransaction();
        try {
            // ambil header + detail & nama kolom key nomor urut seperti helper lain
            switch ($doc) {
                case 'SPPB':
                    $updated = \App\Models\TrSPPB::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPBdetail::on('pgsql')->where('sppbid', $updated->sppbid)->get();
                    break;
                case 'SPPJ':
                    $updated = \App\Models\TrSPPJ::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPJdetail::on('pgsql')->where('sppjid', $updated->sppjid)->get();
                    break;
                case 'SPPK':
                    $updated = \App\Models\TrSPPK::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPKdetail::on('pgsql')->where('sppkid', $updated->sppkid)->get();
                    break;
                case 'SPPT':
                    $updated = \App\Models\TrSPPT::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPTdetail::on('pgsql')->where('spptid', $updated->spptid)->get();
                    break;
            }

            $detTable = fn($m) => $m->getTable();
            $hdrTable = $updated->getTable();

            $sumCompletedAdded = 0.0;
            $sumOpenReduced    = 0.0;

            foreach ($details as $d) {
                // angka dasar
                $qty        = (float)($d->qty ?? 0);
                $ordered    = (float)($d->ordered ?? 0);
                $rejected   = (float)($d->rejectordered ?? 0);
                $completed  = (float)($d->completeordered ?? 0);

                // remaining: pakai openordered kalau ada, else hitung manual
                if (\Schema::connection('pgsql')->hasColumn($detTable($d), 'openordered') && $d->openordered !== null) {
                    $remaining = (float)$d->openordered;
                } else {
                    $remaining = max($qty - $ordered - $rejected - $completed, 0);
                }

                if ($remaining <= 0) continue;

                // update detail: completeordered += remaining, openordered = 0 (jika kolom ada)
                if (\Schema::connection('pgsql')->hasColumn($detTable($d), 'completeordered')) {
                    $d->completeordered = $completed + $remaining;
                }
                if (\Schema::connection('pgsql')->hasColumn($detTable($d), 'openordered')) {
                    $d->openordered = 0;
                }

                // simpan detail
                $d->save();

                // akumulasi untuk header
                $sumCompletedAdded += $remaining;
                $sumOpenReduced    += $remaining;
            }

            // update header agregat bila kolom tersedia
            if (\Schema::connection('pgsql')->hasColumn($hdrTable, 'totalcompleteordered')) {
                $updated->totalcompleteordered = (float)($updated->totalcompleteordered ?? 0) + $sumCompletedAdded;
            }
            if (\Schema::connection('pgsql')->hasColumn($hdrTable, 'totalopenordered')) {
                $updated->totalopenordered = max(0, (float)($updated->totalopenordered ?? 0) - $sumOpenReduced);
            }

            // opsional: kalau semua detail sudah complete, set status header (mis. 'C' atau tetap sesuai workflow)
            // if (method_exists($details, 'sum')) { ... } — skip bila belum butuh.

            $updated->save();

            DB::connection('pgsql')->commit();

            return response()->json([
                'ok'      => true,
                'message' => 'Sisa qty berhasil di-mark Completed.',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            \Log::error('CompleteRemainingOpen failed', ['error'=>$e->getMessage()]);
            return response()->json([
                'ok' => false,
                'message' => 'Gagal memproses: '.$e->getMessage(),
            ], 422);
        }
    }

    public function CompleteRemainingOpen(Request $request, string $doc, string $eid)
    {
        $data = $request->validate([
            'reason' => ['required','string','min:5'],
        ]);

        $ids = Hashids::decode($eid);
        abort_if(empty($ids), 404);
        $srcId = $ids[0];

        $user = Auth::user();

        $doc = strtoupper($doc);
        abort_unless(in_array($doc, ['SPPB','SPPJ','SPPK','SPPT']), 422, 'Invalid doc type');

        // mapping doctype untuk SendCommentController
        $commentDocMap = [
            'SPPB' => 'PB',
            'SPPJ' => 'PJ',
            'SPPK' => 'PK',
            'SPPT' => 'PT',
        ];
        $commentDocType = $commentDocMap[$doc];

        // mapping url show dokumen
        $showRouteMap = [
            'SPPB' => 'showsppbs',
            'SPPJ' => 'showsppjs',
            'SPPK' => 'showsppks',
            'SPPT' => 'showsppts',
        ];

        DB::connection('pgsql')->beginTransaction();
        try {
            switch ($doc) {
                case 'SPPB':
                    $updated = \App\Models\TrSPPB::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPBdetail::on('pgsql')->where('sppbid', $updated->sppbid)->get();
                    $refnbr  = (string) $updated->sppbid; // nomor dokumen
                    break;

                case 'SPPJ':
                    $updated = \App\Models\TrSPPJ::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPJdetail::on('pgsql')->where('sppjid', $updated->sppjid)->get();
                    $refnbr  = (string) $updated->sppjid;
                    break;

                case 'SPPK':
                    $updated = \App\Models\TrSPPK::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPKdetail::on('pgsql')->where('sppkid', $updated->sppkid)->get();
                    $refnbr  = (string) $updated->sppkid;
                    break;

                case 'SPPT':
                    $updated = \App\Models\TrSPPT::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPTdetail::on('pgsql')->where('spptid', $updated->spptid)->get();
                    $refnbr  = (string) $updated->spptid;
                    break;
            }

            $detTable = fn($m) => $m->getTable();
            $hdrTable = $updated->getTable();

            $sumCompletedAdded = 0.0;
            $sumOpenReduced    = 0.0;

            foreach ($details as $d) {
                $qty       = (float)($d->qty ?? 0);
                $ordered   = (float)($d->ordered ?? 0);
                $rejected  = (float)($d->rejectordered ?? 0);
                $completed = (float)($d->completeordered ?? 0);

                if (Schema::connection('pgsql')->hasColumn($detTable($d), 'openordered') && $d->openordered !== null) {
                    $remaining = (float)$d->openordered;
                } else {
                    $remaining = max($qty - $ordered - $rejected - $completed, 0);
                }

                if ($remaining <= 0) continue;

                if (Schema::connection('pgsql')->hasColumn($detTable($d), 'completeordered')) {
                    $d->completeordered = $completed + $remaining;
                }
                if (Schema::connection('pgsql')->hasColumn($detTable($d), 'openordered')) {
                    $d->openordered = 0;
                }

                $d->completed_by = $user->username;
                $d->completed_at = Carbon::now();
                $d->save();

                $sumCompletedAdded += $remaining;
                $sumOpenReduced    += $remaining;
            }

            if (Schema::connection('pgsql')->hasColumn($hdrTable, 'totalcompleteordered')) {
                $updated->totalcompleteordered = (float)($updated->totalcompleteordered ?? 0) + $sumCompletedAdded;
            }
            if (Schema::connection('pgsql')->hasColumn($hdrTable, 'totalopenordered')) {
                $updated->totalopenordered = max(0, (float)($updated->totalopenordered ?? 0) - $sumOpenReduced);
            }

            $updated->save();

            // ✅ kirim reason ke SendCommentController
            $request->merge([
                'message' => $data['reason'],
                'comment' => $data['reason'],
            ]);

            try {
                app(\App\Http\Controllers\SendCommentController::class)
                    ->sendmsg($refnbr, $commentDocType, $request);
            } catch (\Throwable $e) {
                // optional log
            }

            // ✅ EMAIL ke CREATED_BY
            try {
                $creatorUsername = $updated->created_by ?? null;

                if ($creatorUsername) {
                    $creator = \App\Models\User::query()
                        ->where('username', $creatorUsername)
                        ->where('status', 'A')
                        ->first();

                    if ($creator && $creator->notification_email) {

                        // url ke halaman show dokumen
                        $showBase = $showRouteMap[$doc] ?? null;
                        $url = $showBase ? url('/' . $showBase . '/' . Hashids::encode($updated->id)) : null;

                        $emailData = [
                            'docid'     => $refnbr,
                            'cpnyid'    => (string)($updated->cpny_id ?? ''),
                            'deptname'  => (string)($updated->department_id ?? ''),
                            'date'      => Carbon::now(),
                            'info'      => 'Detail Item marked as Completed. Reason: ' . $data['reason'],
                            'name'      => (string)($creator->name ?? ''),
                            'status'    => 'C',              // ✅ Completed
                            'docname'   => $doc,             // ✅ SPPB/SPPJ/...
                            'url'       => $url,
                            'createdby' => (string)($user->name ?? $user->username),
                            'reason'    => $data['reason'],  // ✅ tambahan (kalau mau dipakai di template)
                        ];

                        \Mail::send('emails.mailapprovenew', $emailData, function ($message) use ($creator, $refnbr, $doc) {
                            $message->to($creator->notification_email)
                                ->subject($refnbr . ' - Completed Detail Item ' . $doc)
                                ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        });
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Email completed failed: ' . $e->getMessage(), [
                    'doc' => $doc,
                    'srcId' => $srcId,
                ]);
            }

            DB::connection('pgsql')->commit();

            return response()->json([
                'ok'      => true,
                'message' => 'Sisa qty berhasil di-mark Completed.',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            \Log::error('CompleteRemainingOpen failed', ['error' => $e->getMessage()]);

            return response()->json([
                'ok'      => false,
                'message' => 'Gagal memproses: ' . $e->getMessage(),
            ], 422);
        }
    }


    public function checkQtyBeforeSubmit(Request $request)
    {
        $doc    = (string) $request->input('doc');      // SPPB | SPPJ | SPPK | SPPT
        $srcId  = (string) $request->input('src_id');   // hashids dari sppbid/sppjid/...
        $detailsJson = $request->input('details', '[]');
        $details = json_decode($detailsJson, true) ?? [];
        // dd($doc.$srcId);
        $result = CsQtyValidator::validate($doc, $srcId, $details);

        if (! $result['ok']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function cancelCS(Request $request, $csid)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Optional: alasan cancel
        $reason = trim((string) $request->input('reason', ''));
        // dd($reason);
        DB::beginTransaction();
        try {
            $updated = TrCS::where('csid', $csid)->first();

            if (!$updated) {
                return response()->json([
                    'ok' => false,
                    'message' => 'CS tidak ditemukan.',
                ], 404);
            }

            // Optional proteksi: kalau sudah X / sudah final, stop
            if (strtoupper((string) $updated->status) === 'X') {
                return response()->json([
                    'ok' => true,
                    'message' => 'CS sudah Cancel sebelumnya.',
                    'redirect' => url('/cslist'),
                ]);
            }

            // Update header
            $updated->csnote = $reason;
            $updated->status = 'X';           
            $updated->save();

            // Update detail
            $q = TrCSdetail::where('csid', $csid)->update([
                'status' => 'X',              
            ]);

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'CS berhasil dicancel.',
                'redirect' => url('/csjobs'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Gagal cancel CS: ' . $e->getMessage(),
            ], 500);
        }
    }

    

    public function reviseSPPBJKT(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'doc_type'       => 'required|in:SPPB,SPPJ,SPPK,SPPT',
            'doc_no'         => 'required|string',
            'cpny_id'        => 'required|string',
            'department_id' => 'required|string',
            'reason'         => 'required|string|min:5',
        ]);

        DB::beginTransaction();
        try {
            $updated = 0;
            $docType = $data['doc_type'];
            $docNo   = $data['doc_no'];
            $reason  = $data['reason'];

            switch ($docType) {
                case 'SPPB':
                    $updated = TrSPPB::where('sppbid', $docNo)->update([
                        'status' => 'D',
                        'updated_by' => $user->username,
                        'updated_at' => Carbon::now(),
                    ]);
                    $header = TrSPPB::where('sppbid', $docNo)->first();
                    $doc_type  = 'PB';      
                    $eid       = \Vinkla\Hashids\Facades\Hashids::encode($header->id);      
                    $url       =  url('/showsppbs/' . $eid);  
                    
                    break;

                case 'SPPJ':
                    $updated = TrSPPJ::where('sppjid', $docNo)->update([
                        'status' => 'D',
                        'updated_by' => $user->username,
                        'updated_at' => Carbon::now(),
                    ]);
                    $header = TrSPPJ::where('sppjid', $docNo)->first();
                    $doc_type ='PJ';
                    $eid      = \Vinkla\Hashids\Facades\Hashids::encode($header->id);
                    $url       =  url('/showsppjs/' . $eid);
                    break;

                case 'SPPK':
                    $updated = TrSPPK::where('sppkid', $docNo)->update([
                        'status' => 'D',
                        'updated_by' => $user->username,
                        'updated_at' => Carbon::now(),
                    ]);
                    $header = TrSPPK::where('sppkid', $docNo)->first();
                    $doc_type ='PK';
                    $eid      = \Vinkla\Hashids\Facades\Hashids::encode($header->id);
                    $url       =  url('/showsppks/' . $eid);
                    break;

                case 'SPPT':
                    $updated = TrSPPT::where('spptid', $docNo)->update([
                        'status' => 'D',
                        'updated_by' => $user->username,
                        'updated_at' => Carbon::now(),
                    ]);
                    $header = TrSPPT::where('spptid', $docNo)->first();
                    $doc_type ='PT';
                    $eid      = \Vinkla\Hashids\Facades\Hashids::encode($header->id);
                    $url       =  url('/showsppts/' . $eid);
                    break;
            }

            if (!$updated || !$header) {
                DB::rollBack();
                return response()->json([
                    'ok' => false,
                    'message' => "Dokumen $docType ($docNo) tidak ditemukan."
                ], 404);
            }

            // === INSERT APPROVAL ===
            TrApproval::create([
                'refnbr'             => $docNo,
                'aprv_leveling'      => 10,
                'aprv_doctype'       => $doc_type,
                'aprv_cpnyid'        => $data['cpny_id'],
                'aprv_departementid' => $data['department_id'],
                'aprv_username'      => $user->username,
                'aprv_name'          => $user->name,
                'aprv_datebefore'    => Carbon::now(),
                'aprv_type'          => '',
                'aprv_condition'     => '',
                'status'             => 'D',
                'created_by'         => $user->username,
                'updated_by'         => $user->username,
            ]);
         
            // === SEND COMMENT ===
            try {
                app(\App\Http\Controllers\SendCommentController::class)
                    ->sendmsg($header->id, $doc_type, $request);
            } catch (\Throwable $e) {
                \Log::warning('SendComment failed: '.$e->getMessage());
            }

            // === EMAIL ke CREATED_BY ===
            try {
                $creatorUsername = $header->created_by ?? null;

                if ($creatorUsername) {
                    $creator = \App\Models\User::query()
                        ->where('username', $creatorUsername)
                        ->where('status', 'A')
                        ->first();

                    if ($creator && $creator->notification_email) {                       
                       
                        $emailData = [
                            'docid'     => $docNo,
                            'cpnyid'    => $header->cpny_id ?? $data['cpny_id'],
                            'deptname'  => $header->department_id ?? $data['department_id'],
                            'date'      => Carbon::now(),
                            'info'      => $header->keperluan,
                            'name'      => $creator->name,                            
                            'status'    => 'D',
                            'docname'   => $docType,
                            'url'       => $url,                            
                            'createdby' => $user->name,
                        ];

                        \Mail::send('emails.mailapprovenew', $emailData, function ($message) use ($creator, $docNo, $docType) {
                            $message->to($creator->notification_email)
                                ->subject($docNo . ' - Revise Approval ' . $docType)
                                ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        });
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Email revise failed: '.$e->getMessage());
            }
   

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => "Dokumen $docType ($docNo) berhasil direvise."
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'message' => 'Gagal revise: ' . $e->getMessage()
            ], 500);
        }
    }

  






}
