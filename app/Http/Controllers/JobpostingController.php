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
use Mail;


class JobpostingController extends Controller
{
    public function index()
    {
        $all = Jobposting::count();
        $onProgress = Jobposting::where('status', 'P')->count();
        $reject = Jobposting::where('status', 'R')->count();
        $revise = Jobposting::where('status', 'D')->count();
        $completed = Jobposting::where('status', 'C')->count();
       
        return view('pages.jobpostings.jobpostings', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }
    
    public function json(Request $request)
    {
        // $status = $request->query('status', 'P');
        $status = $request->has('status') ? $request->query('status') : 'P';

        $query = Jobposting::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $jobposting = $query->orderBy('id', 'desc')->get();

        return response()->json(['data' => $jobposting]);
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
