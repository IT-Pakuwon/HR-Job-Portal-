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
use App\Models\JobLevel;
use App\Models\JobResponsiblities;
use App\Models\JobQualification;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Jobposting;
use App\Models\JobpostingResponsiblities;
use App\Models\JobpostingQualification;
use App\Models\AutonbrJobportal;
use App\Models\ViewCareer;
use Mail;
use Vinkla\Hashids\Facades\Hashids;

class JobapplicantController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); // atau $request->user()

        if (!$user) {
            // Session habis / belum login
            return $request->expectsJson()
                ? response()->json(['message' => 'Your session has expired. Please sign in again.'], 401)
                : redirect()->route('login')->with('error', 'Your session has expired. Please sign in again.');
        }

        // Ambil jumlah total pelamar dari viewtrxcareer     
        $all = ViewCareer::count();
        $unchecked = ViewCareer::where('is_read', 'N')->count();
        $checked = ViewCareer::where('is_read', 'Y')->whereIn('status', ['H', 'P'])->count();
        $reject = ViewCareer::where('status', 'R')->count();       
        $approved = ViewCareer::where('status', 'C')->count();

        return view('pages.careers.jobapplicant', compact('all', 'unchecked', 'checked', 'reject', 'approved'));
    }
    
    public function json(Request $request)
    {
        $jobTLExact = trim((string) $request->input('job_tl_exact', ''));
        $status     = $request->query('status');
        $cpnyid     = $request->query('cpnyid');
        

        $start      = (int) $request->input('start', 0);
        $length     = (int) $request->input('length', 10);
        $global     = trim((string) $request->input('search.value', ''));
        $orderIdx   = (int) $request->input('order.0.column', 0);
        $orderDir   = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Peta nama kolom DataTables -> kolom DB
        $nameToDb = [
            'docid'                  => 'vc.docid',
            'apply_date'             => 'vc.apply_date',
            'fullname'               => 'vc.fullname',
            'education_name'         => 'vc.education_name',
            'religion'               => 'vc.religion',
            'height'                 => 'vc.height',
            'weight'                 => 'vc.weight',
            'company_name'           => 'vc.company_name',
            'match_score_percentage' => 'vs.match_score_percentage',
            'prev_apply_step'        => 'vc.prev_apply_step',
        ];

        // Base query
        $base = DB::connection('mysql3')
            ->table('viewtrxcareer as vc')
            ->leftJoin('viewtrxcareer_scoring as vs', 'vc.docid', '=', 'vs.docid');

        // Filter "tetap" (status, cpnyid) – ini memang bagian dari dataset
        if (!empty($status)) {
            if ($status === 'is_read_Y') {
                $base->where('vc.is_read', 'Y')->whereIn('status', ['H', 'P']);
            } elseif ($status === 'is_read_N') {
                $base->where('vc.is_read', 'N');
            } else {
                $base->where('vc.status', $status);
            }
        }
        if (!empty($cpnyid)) {
            $base->where('vc.cpnyid', $cpnyid);
        }

        
        // Total sebelum global/column search
        $recordsTotal = (clone $base)->count();

        // Query yang akan diberi filter pencarian
        $query = (clone $base);

        // Global search
        if ($global !== '') {
            $query->where(function ($q) use ($global) {
                $like = "%{$global}%";
                $q->where('vc.fullname', 'like', $like)
                ->orWhere('vc.education_name', 'like', $like)
                ->orWhere('vc.religion', 'like', $like)
                ->orWhere('vc.company_name', 'like', $like)
                ->orWhere('vc.docid', 'like', $like)
                ->orWhere('vc.prev_apply_step', 'like', $like)
                ->orWhereRaw('CAST(IFNULL(vs.match_score_percentage,0) AS CHAR) LIKE ?', [$like]);
            });
        }

        // Per-kolom search (columns[i][name], columns[i][search][value])
        $cols = $request->input('columns', []);
        foreach ($cols as $c) {
            $name = $c['name'] ?? null;
            $val  = isset($c['search']['value']) ? trim((string)$c['search']['value']) : '';
            if (!$name || $val === '') continue;

            $dbcol = $nameToDb[$name] ?? null;
            if (!$dbcol) continue;

            if ($name === 'prev_apply_step') {
                // dropdown kode step → exact
                $query->where($dbcol, $val);
            } elseif ($name === 'match_score_percentage') {
                // dukung >=80, <=90, 70-85, atau fallback LIKE
                if (preg_match('/^\s*(>=|<=|>|<)\s*(\d+)\s*$/', $val, $m)) {
                    $op  = $m[1]; $num = (int)$m[2];
                    $query->where('vs.match_score_percentage', $op, $num);
                } elseif (preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/', $val, $m)) {
                    $a = (int)$m[1]; $b = (int)$m[2];
                    if ($a > $b) [$a,$b] = [$b,$a];
                    $query->whereBetween('vs.match_score_percentage', [$a, $b]);
                } else {
                    $query->whereRaw('CAST(IFNULL(vs.match_score_percentage,0) AS CHAR) LIKE ?', ["%{$val}%"]);
                }
            } else {
                // default LIKE
                $query->where($dbcol, 'like', "%{$val}%");
            }
        }

        if ($jobTLExact !== '') {
            [$exactTitle, $exactLevel] = array_pad(explode('|||', $jobTLExact, 2), 2, '');
            if ($exactTitle !== '' && $exactLevel !== '') {
                $query->where('vc.job_title', $exactTitle)
                    ->where('vc.job_level', $exactLevel);
            }
        }

        // Sorting – pakai nama kolom yg dikirim DataTables agar robust
        $orderName = $request->input("columns.$orderIdx.name");
        $orderBy   = $nameToDb[$orderName] ?? 'vc.docid';

        // Hitung setelah filter
        $recordsFiltered = (clone $query)->count();

        // Ambil data (paging)
        if ($length !== -1) {
            $query->skip($start)->take($length);
        }

        // $data = $query->select(
        //         'vc.*',
        //         DB::raw('IFNULL(vs.total_tags, 0) as total_tags'),
        //         DB::raw('IFNULL(vs.matched_count, 0) as matched_count'),
        //         DB::raw('IFNULL(vs.match_score_percentage, 0) as match_score_percentage')
        //     )
        //     ->orderBy($orderBy, $orderDir)
        //     ->get();
        $rows = $query->select([
            DB::raw('vc.id as _id'),
            'vc.docid',
            'vc.apply_date',
            'vc.fullname',
            'vc.education_name',
            'vc.religion',
            'vc.height',
            'vc.weight',
            'vc.company_name',
            'vc.prev_apply_step',
            'vc.job_title',
            'vc.job_level',
            'vc.status_app',
            'vc.status',
            'vc.cpnyid',
            'vc.created_user',
            'vc.is_read',
            DB::raw('IFNULL(vs.total_tags, 0) as total_tags'),
            DB::raw('IFNULL(vs.matched_count, 0) as matched_count'),
            DB::raw('IFNULL(vs.match_score_percentage, 0) as match_score_percentage'),
        ])
        ->orderBy($orderBy, $orderDir)
        ->get();

        $data = $rows->map(function ($r) {
            return [
                'eid'                    => Hashids::encode($r->_id),
                'docid'                  => $r->docid,
                'apply_date'             => $r->apply_date, // bisa diformat Y-m-d jika perlu
                'fullname'               => $r->fullname,
                'education_name'         => $r->education_name,
                'religion'               => $r->religion,
                'height'                 => $r->height,
                'weight'                 => $r->weight,
                'company_name'           => $r->company_name,
                'prev_apply_step'        => $r->prev_apply_step,
                'job_title'              => $r->job_title,
                'job_level'              => $r->job_level,
                'status_app'             => $r->status_app,
                'status'                 => $r->status,
                'cpnyid'                 => $r->cpnyid,
                'created_user'           => $r->created_user,
                'is_read'                => $r->is_read,
                'total_tags'             => (int) $r->total_tags,
                'matched_count'          => (int) $r->matched_count,
                'match_score_percentage' => (int) $r->match_score_percentage,
            ];
        });

        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function jobTitleLevels(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $rows = DB::connection('mysql3')->table('viewtrxcareer as vc')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('vc.job_title', 'like', "%{$q}%")
                    ->orWhere('vc.job_level', 'like', "%{$q}%");
                });
            })
            ->whereNotNull('vc.job_title')->where('vc.job_title', '!=', '')
            ->whereNotNull('vc.job_level')->where('vc.job_level', '!=', '')
            ->distinct()
            ->orderBy('vc.job_title')
            ->orderBy('vc.job_level')
            ->limit(50)
            ->get(['vc.job_title','vc.job_level']);

        // Select2 butuh {id, text}. id berisi "title|||level" (delimiter aman)
        $data = $rows->map(function ($r) {
            return [
                'id'   => $r->job_title . '|||' . $r->job_level,
                'text' => $r->job_title . ' — ' . $r->job_level,
            ];
        });

        return response()->json($data);
    }


    public function getCounts(Request $request)
    {
        $cpnyid = $request->query('cpnyid');

        $query = Jobposting::query();
        if (!empty($cpnyid)) {
            $query->where('cpnyid', $cpnyid);
        }

        $all = $query->count();
        $onProgress = (clone $query)->where('status', 'P')->count();
        $reject = (clone $query)->where('status', 'R')->count();
        $revise = (clone $query)->where('status', 'D')->count();
        $completed = (clone $query)->where('status', 'C')->count();

        return response()->json([
            'all' => $all,
            'onProgress' => $onProgress,
            'reject' => $reject,
            'revise' => $revise,
            'completed' => $completed
        ]);
    }


    public function JobApplicants($jobId)
    {
        // dd($jobId);
        // $applicants = ViewCareer::where('docidposting', $jobId)->get();
        $applicants = DB::connection('mysql3')
            ->table('viewtrxcareer as vc')
            ->leftJoin('viewtrxcareer_scoring as vs', 'vc.docid', '=', 'vs.docid')
            ->where('vc.docidposting', $jobId)
            ->select(
                'vc.*',
                DB::raw('IFNULL(vs.total_tags, 0) as total_tags'),
                DB::raw('IFNULL(vs.matched_count, 0) as matched_count'),
                DB::raw('IFNULL(vs.match_score_percentage, 0) as match_score_percentage')
            )            
            ->get();

        // dd($applicants);
        return response()->json(['data' => $applicants]);
    }
        
    public function showJobposting($id)
    {        
        $jobposting = Jobposting::findOrFail($id);
        $approval = T_approval::where('docid', $jobposting->docid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();

        $jobres = JobpostingResponsiblities::where('docid', $jobposting->docid)           
            ->get();
        $jobqua = JobpostingQualification::where('docid', $jobposting->docid)           
            ->get();
        $attachment = Attachment::where('docid', $jobposting->docid)    
            ->where('status','A')        
            ->get();
       
        return view('pages.jobpostings.showjobpostings', compact('jobposting','jobres','jobqua','approval','attachment'));
    }

    

}
