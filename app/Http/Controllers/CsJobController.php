<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\T_Message;
use App\Models\Attachment;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
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
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
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


class CsJobController extends Controller
{
   
    public function CsJobs()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $u = $user->username ?? '';

        $mine     = vCsJobs::where('assignpurchasing', $user->username)->count();
        $revision = TrPO::where('created_by', $u)->where('status','R')->count();            
        $all      = vCsJobs::count();
        $sppbjkt  = vSppbjktOnProgress::count();

        return view('pages.canvass.csjobs', compact('mine','revision','all','sppbjkt'));
    }

    public function CsJobsDatasetCounts(Request $request)
    {
        $user = Auth::user();
        $u = $user->username ?? '';

        return response()->json([
            'mine'     => vCsJobs::where('assignpurchasing', $u)->count(),
            'revision' => TrPO::where('created_by', $u)->where('status','R')->count(),
            'all'      => vCsJobs::count(),
            'sppbjkt'  => vSppbjktOnProgress::count(),
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

        $base = TrPO::query()
            ->where('created_by', $username)
            ->where('status', 'R');

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
                return TrPO::query()->where('created_by', $username)->where('status','R');
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

        // hanya draft (H) dan milik user login
        $base = TrCS::query()
            ->where('status', 'H')
            ->where('created_by', $username);

        // kolom yang diizinkan untuk ordering
        $columns = [
            0 => 'csid',
            1 => 'csdate',
            2 => 'cpny_id',
            3 => 'department_id',
            4 => 'user_peminta',
            5 => 'csnote',
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
                ->orWhereRaw("TO_CHAR(csdate, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select('id','csid','csdate','cpny_id','department_id','user_peminta','csnote','created_by','status')
            ->orderBy($orderCol, $orderDir)
            ->orderBy('csid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data->transform(function($r){
            $r->eid = Hashids::encode($r->id);
            unset($r->id); // opsional sembunyikan id asli
            return $r;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }
   

    public function CompleteRemainingOpen(string $doc, string $eid)
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
                    $header = \App\Models\TrSPPB::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPBdetail::on('pgsql')->where('sppbid', $header->sppbid)->get();
                    break;
                case 'SPPJ':
                    $header = \App\Models\TrSPPJ::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPJdetail::on('pgsql')->where('sppjid', $header->sppjid)->get();
                    break;
                case 'SPPK':
                    $header = \App\Models\TrSPPK::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPKdetail::on('pgsql')->where('sppkid', $header->sppkid)->get();
                    break;
                case 'SPPT':
                    $header = \App\Models\TrSPPT::on('pgsql')->lockForUpdate()->findOrFail($srcId);
                    $details = \App\Models\TrSPPTdetail::on('pgsql')->where('spptid', $header->spptid)->get();
                    break;
            }

            $detTable = fn($m) => $m->getTable();
            $hdrTable = $header->getTable();

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
                $header->totalcompleteordered = (float)($header->totalcompleteordered ?? 0) + $sumCompletedAdded;
            }
            if (\Schema::connection('pgsql')->hasColumn($hdrTable, 'totalopenordered')) {
                $header->totalopenordered = max(0, (float)($header->totalopenordered ?? 0) - $sumOpenReduced);
            }

            // opsional: kalau semua detail sudah complete, set status header (mis. 'C' atau tetap sesuai workflow)
            // if (method_exists($details, 'sum')) { ... } — skip bila belum butuh.

            $header->save();

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





}
