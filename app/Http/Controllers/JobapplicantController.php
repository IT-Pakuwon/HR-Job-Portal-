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
        $all = Jobposting::count();
        $onProgress = Jobposting::where('status', 'P')->count();
        $reject = Jobposting::where('status', 'R')->count();
        $revise = Jobposting::where('status', 'D')->count();
        $completed = Jobposting::where('status', 'C')->count();
       
        return view('pages.careers.jobapplicant', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }
    
    public function json(Request $request)
    {
        $status = $request->query('status');
        $cpnyid = $request->query('cpnyid');

        $query = Jobposting::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($cpnyid)) {
            $query->where('cpnyid', $cpnyid);
        }

        $jobposting = $query->orderBy('id', 'desc')->get();

        return response()->json(['data' => $jobposting]);
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
