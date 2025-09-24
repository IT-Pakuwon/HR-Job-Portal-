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
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use App\Models\vReceivedList;
use App\Models\vSppbjktOnProgress;
 use App\Models\TrCS;
use App\Models\vCsJobs;
use App\Models\vCsRevision;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class CsJobController extends Controller
{

    public function CsJobs()
    {
        // Kartu ringkasan
        $all  = vCsJobs::count();
        $sppb = vCsJobs::where('doc_type', 'SPPB')->count();
        $sppj = vCsJobs::where('doc_type', 'SPPJ')->count();
        $sppk = vCsJobs::where('doc_type', 'SPPK')->count();
        $sppt = vCsJobs::where('doc_type', 'SPPT')->count();

        return view('pages.canvass.csjobs', compact('all', 'sppb', 'sppj', 'sppk', 'sppt'));
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
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
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
        $base = vCsRevision::query();
        return $this->buildJobsJson($base, $request);
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
                return vCsRevision::query();
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

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }






}
