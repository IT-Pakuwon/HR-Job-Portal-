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


class JobapplicantController extends Controller
{
    public function index()
    {
        // Ambil jumlah total pelamar dari viewtrxcareer     
        $all = ViewCareer::count();
        $unchecked = ViewCareer::where('is_read', 'N')->count();
        $checked = ViewCareer::where('is_read', 'Y')->count();
        $reject = ViewCareer::where('status', 'R')->count();       
        $approved = ViewCareer::where('status', 'C')->count();

        return view('pages.careers.jobapplicant', compact('all', 'unchecked', 'checked', 'reject', 'approved'));
    }
    
    public function json(Request $request)
    {
        $status = $request->query('status');
        $cpnyid = $request->query('cpnyid');

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');

        // Kolom yang bisa diurutkan
        $columns = [
            'vc.docid', 'vc.apply_date', 'vc.fullname', 'vc.education_name', 'vc.religion',
            'vc.height', 'vc.weight', 'vc.company_name', 'vs.match_score_percentage', 'vc.prev_apply_step'
        ];
        $orderColumn = $columns[$orderColumnIndex] ?? 'vc.docid';

        $query = DB::connection('mysql3')->table('viewtrxcareer as vc')
            ->leftJoin('viewtrxcareer_scoring as vs', 'vc.docid', '=', 'vs.docid');

        // if (!empty($status)) {
        //     $query->where('vc.status', $status);
        // }

        if (!empty($status)) {
            if ($status === 'is_read_Y') {
                $query->where('vc.is_read', 'Y');
            } elseif ($status === 'is_read_N') {
                $query->where('vc.is_read', 'N');
            } else {
                $query->where('vc.status', $status);
            }
        }


        if (!empty($cpnyid)) {
            $query->where('vc.cpnyid', $cpnyid);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('vc.fullname', 'like', "%$search%")
                  ->orWhere('vc.education_name', 'like', "%$search%")
                  ->orWhere('vc.religion', 'like', "%$search%")
                  ->orWhere('vc.company_name', 'like', "%$search%")
                  ->orWhere('vc.docid', 'like', "%$search%")
                  ->orWhere('vc.prev_apply_step', 'like', "%$search%")
                  ->orWhere('vs.match_score_percentage', 'like', "%$search%")
                  ;
            });
        }

        $totalRecords = ViewCareer::count();
        $filteredRecords = $query->count();

        $applicants = $query->select(
            'vc.*',
            DB::raw('IFNULL(vs.total_tags, 0) as total_tags'),
            DB::raw('IFNULL(vs.matched_count, 0) as matched_count'),
            DB::raw('IFNULL(vs.match_score_percentage, 0) as match_score_percentage')
        )
        ->orderByRaw($orderColumn . ' ' . $orderDir)
        ->skip($start)
        ->take($length)
        ->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $applicants
        ]);
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
