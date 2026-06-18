<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\ViewCareer;
use App\Models\Autonbr;
use App\Models\MsCompany;
use App\Models\MsDepartment;
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
use App\Models\Career;
use App\Models\Applicant;
use App\Models\ApplicantCourse;
use App\Models\ApplicantEducation;
use App\Models\ApplicantFamily;
use App\Models\ApplicantLanguage;
use App\Models\ApplicantMarital;
use App\Models\ApplicantSW;
use App\Models\ApplicantSkill;
use App\Models\ApplicantDriverLicense;
use App\Models\ApplicantReference;
use App\Models\ApplicantWorking;
use App\Models\JobApplyStep;
use App\Models\Mschecklist;
use App\Models\Trchecklist;
use App\Models\MsAssessment;
use App\Models\TrAssessment;
use App\Models\TrAssessmentdetail;
use App\Models\Agenda;
use Mail;
use PhpOffice\PhpWord\TemplateProcessor;
use PDF;
use Illuminate\Support\Str;
use App\Models\JobApply;
use App\Models\MPsychotest;
use App\Models\Payrollconfirm;
use App\Models\Msonboarding;
use App\Models\Tronboarding;
use Illuminate\Support\Facades\Crypt;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;
use App\Models\MJobApplyStep;
use App\Models\SignPayroll;
use App\Models\GroupAccspecific;
use App\Models\CompanyAddress;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\TrApproval;
use App\Models\TrMessage;


class CareerController extends Controller
{
    public function index()
    {
        $incompletedprofile = ViewCareer::where('status_app', 'H')->count();
        $completedprofile = ViewCareer::where('status_app', 'P')->count();
        $nocandidate = ViewCareer::where('status', 'H')->count();
        $candidate = ViewCareer::where('status', 'P')->count();
        $join = ViewCareer::where('status', 'C')->count();

        return view('pages.careers.careers', compact('incompletedprofile', 'completedprofile', 'nocandidate', 'candidate','join'));
    }

    public function stats(Request $request)
    {
        $cpnyid = $request->query('cpnyid');

        $query = ViewCareer::query();
        if (!empty($cpnyid)) {
            $query->where('cpnyid', $cpnyid);
        }

        return response()->json([
            'incompletedprofile' => (clone $query)->where('status_app', 'H')->count(),
            'completedprofile' => (clone $query)->where('status_app', 'P')->count(),
            'nocandidate' => (clone $query)->where('status', 'H')->count(),
            'candidate' => (clone $query)->where('status', 'P')->count(),
            'join' => (clone $query)->where('status', 'C')->count(),
        ]);
    }


    public function json(Request $request)
    {
        $status_app = $request->query('status_app');
        $status = $request->query('status');
        $cpnyid = $request->query('cpnyid');

        $query = ViewCareer::query();

        if (!empty($status_app)) {
            $query->where('status_app', $status_app);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($cpnyid)) {
            $query->where('cpnyid', $cpnyid);
        }

        $career = $query->orderBy('id', 'desc')->get();
        return response()->json(['data' => $career]);
    }


    public function jsonxxx(Request $request)
    {
        $status_app = $request->query('status_app');
        $status = $request->query('status');
        $cpnyid = $request->query('cpnyid');

        $query = ViewCareer::query();

        if (!empty($status_app)) {
            $query->where('status_app', $status_app);
        } elseif (!empty($status)) {
            $query->where('status', $status);
        } elseif (!empty($cpnyid)) {
            $query->where('cpnyid', $cpnyid);
        }


        $career = $query->orderBy('id', 'desc')->get();

        return response()->json(['data' => $career]);
    }


    public function showCareer($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $datenow = Carbon::now()->format('Y-m-d');
        $timenow = date('Y-m-d H:i:s');
        $career = ViewCareer::findOrFail($id);
        $job_apply = Career::find($id);

        $hasGroupAccess = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'STEP')
            ->where('status', 'A')
            ->first();
        // dd($hasGroupAccess);
        if ($hasGroupAccess) {
            $job_apply->is_read = 'Y';
            $job_apply->save();
        }
        // dd($job_apply);
        $applicant = Applicant::where('applicant_id', $career->applicant_id)->first();
        $applicant_family = ApplicantFamily::where('applicant_id', $career->applicant_id)->get();
        $applicant_marital = ApplicantMarital::where('applicant_id', $career->applicant_id)->get();
        $applicant_education = ApplicantEducation::where('applicant_id', $career->applicant_id)->get();
        $applicant_working = ApplicantWorking::where('applicant_id', $career->applicant_id)->get();
        $applicant_reference = ApplicantReference::where('applicant_id', $career->applicant_id)->get();
        $applicant_language = ApplicantLanguage::where('applicant_id', $career->applicant_id)->get();
        $applicant_course = ApplicantCourse::where('applicant_id', $career->applicant_id)->get();
        $applicant_sw = ApplicantSW::where('applicant_id', $career->applicant_id)->get();
        $applicant_skill = ApplicantSkill::where('applicant_id', $career->applicant_id)->get();

        $jobapplystep = JobApplyStep::leftjoin('hr_ms_job_step', 'hr_trx_job_apply_step.step_id', '=', 'hr_ms_job_step.step_id')
            ->select('hr_trx_job_apply_step.*', 'hr_ms_job_step.step_descr')
            ->where('hr_trx_job_apply_step.docid', $career->docid)
            ->where('hr_trx_job_apply_step.jobid', $career->docidposting)
            ->when($career->status !== 'T', fn($q) => $q->where('hr_trx_job_apply_step.status', '<>', 'X'))
            ->orderBy('hr_trx_job_apply_step.step_order', 'ASC')
            ->get();

        $jobposting = Jobposting::where('docid', $career->docidposting)->first();
        $jobres = JobpostingResponsiblities::where('docid', $career->docidposting)->get();
        $jobqua = JobpostingQualification::where('docid', $career->docidposting)->get();

        $tr_checklist = Trchecklist::leftjoin('hr_ms_doc_checklist', 'hr_trx_doc_checklist.checklist_id', '=', 'hr_ms_doc_checklist.checklist_id')
            ->select('hr_trx_doc_checklist.*', 'hr_ms_doc_checklist.checklist_descr')
            ->where('hr_trx_doc_checklist.jobapply_id',$career->docid)
            ->orderBy('hr_trx_doc_checklist.step_order', 'ASC')
            ->get();

        // ========== HC ASSESSMENT ==========
        $assessmentGroups = [];
        $tr_assessment = TrAssessment::where('jobapply_id', $career->docid)
            ->where('type','hc')
            ->first();

        if ($tr_assessment) {
            $assessmentData = TrAssessmentdetail::leftjoin('hr_ms_interview_assessment', 'hr_trx_interview_assessment_detail.assessment_id', '=', 'hr_ms_interview_assessment.assessment_id')
                ->select('hr_trx_interview_assessment_detail.*', 'hr_ms_interview_assessment.assessment_group', 'hr_ms_interview_assessment.assessment_descr')
                ->where('hr_trx_interview_assessment_detail.docid', $tr_assessment->docid)
                ->orderBy('hr_ms_interview_assessment.step_order_group', 'ASC')
                ->orderBy('hr_ms_interview_assessment.step_order', 'ASC')
                ->get()
                ->groupBy('step_order_group');

            foreach ($assessmentData as $group) {
                $first = $group->first();
                $assessmentGroups[] = [
                    'assessment_id' => $first->assessment_id,
                    'assessment_group' => $first->assessment_group,
                    'assessment_type' => $first->assessment_type,
                    'selected_score' => $group
                        ->filter(fn($item) => $item->assessment_score_value > 0)
                        ->pluck('assessment_score_value')
                        ->first() ?? 0,
                    'options' => $group->map(function ($item) {
                        return [
                            'assessment_score' => $item->assessment_score,
                            'assessment_descr' => $item->assessment_descr
                        ];
                    })->values()->toArray()
                ];
            }
        }

        // ========== USER ASSESSMENT ==========
        $assessmentGroupsUser = [];
        $tr_assessment_user = TrAssessment::where('jobapply_id', $career->docid)
            ->where('type','user')
            ->first();

        if ($tr_assessment_user) {
            $assessmentData_user = TrAssessmentdetail::leftjoin('hr_ms_interview_assessment', 'hr_trx_interview_assessment_detail.assessment_id', '=', 'hr_ms_interview_assessment.assessment_id')
                ->select('hr_trx_interview_assessment_detail.*', 'hr_ms_interview_assessment.assessment_group', 'hr_ms_interview_assessment.assessment_descr')
                ->where('hr_trx_interview_assessment_detail.docid', $tr_assessment_user->docid)
                ->orderBy('hr_ms_interview_assessment.step_order_group', 'ASC')
                ->orderBy('hr_ms_interview_assessment.step_order', 'ASC')
                ->get()
                ->groupBy('step_order_group');

            foreach ($assessmentData_user as $group) {
                $first = $group->first();
                $assessmentGroupsUser[] = [
                    'assessment_id' => $first->assessment_id,
                    'assessment_group' => $first->assessment_group,
                    'assessment_type' => $first->assessment_type,
                    'selected_score' => $group
                        ->filter(fn($item) => $item->assessment_score_value > 0)
                        ->pluck('assessment_score_value')
                        ->first() ?? 0,
                    'options' => $group->map(function ($item) {
                        return [
                            'assessment_score' => $item->assessment_score,
                            'assessment_descr' => $item->assessment_descr
                        ];
                    })->values()->toArray()
                ];
            }
        }

        $year = now()->year;
        // $photo = 'http://127.0.0.1:7777/attachments/'.$year.'/'.$applicant->upload_photo;
        // $cv = 'http://127.0.0.1:7777/attachments/'.$year.'/'.$applicant->upload_cv;
        // $coverletter = 'http://127.0.0.1:7777/attachments/'.$year.'/'.$applicant->upload_coverletter;

        $config = config('filesystems.disks.gcs');
        // Pastikan StorageClient di-import dan digunakan dengan benar
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $config['key_file'],
        ]);

        $bucket = $storage->bucket($config['bucket']);
        $expiration = \Carbon\Carbon::now()->addMinutes(30);

        $photo = null;
        $cv = null;
        $coverletter = null;
        $transkip = null;
        $ijazah = null;

        if (!empty($applicant->upload_photo)) {
            $object = $bucket->object($applicant->upload_photo);
            $photo = $object->signedUrl($expiration);
        }

        if (!empty($applicant->upload_cv)) {
            $object = $bucket->object($applicant->upload_cv);
            $cv = $object->signedUrl($expiration);
        }

        if (!empty($applicant->upload_transkip_nilai)) {
            $object = $bucket->object($applicant->upload_transkip_nilai);
            $transkip = $object->signedUrl($expiration);
        }

        if (!empty($applicant->upload_ijazah)) {
            $object = $bucket->object($applicant->upload_ijazah);
            $ijazah = $object->signedUrl($expiration);
        }

        $agenda = Agenda::where('refid', $career->docid)->get();
        $userlist = User::where('status','A')->orderby('name','ASC')->get();
        $agenda = Agenda::where('refid', $career->docid)->get();

        // $typestep = JobApplyStep::leftjoin('hr_ms_job_step', 'hr_trx_job_apply_step.step_id', '=', 'hr_ms_job_step.step_id')
        //     ->select('hr_trx_job_apply_step.step_id', 'hr_ms_job_step.step_descr')
        //     ->where('hr_trx_job_apply_step.docid',$career->docid)
        //     ->where('hr_trx_job_apply_step.status','<>','X')
        //     ->orderBy('hr_trx_job_apply_step.step_order', 'ASC')
        //     ->get();
        $typestep = MJobApplyStep::where('schedule', 1)->get();
        $payrolls = Payrollconfirm::where('jobapply_id', $career->docid)->get();

        $sign = SignPayroll::where('docid', $career->docid)->orderby('aprvid','ASC')->get();

        $onboarding = Tronboarding::where('jobapply_id', $career->docid)->first();
        // dd($career->subgrade_id);
        $canAccessPayroll = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'PAYROLL')
            ->where('parameter_access_id', $career->subgrade_id)
            ->where('status', 'A')
            ->exists();
        // dd($canAccessPayroll);
        $canAccessAssessment = GroupAccspecific::where('username', $user->username)
            ->where('status', 'A')
            ->exists();

        $canAccessChecklist = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'CHECKLIST')
            ->where('status', 'A')
            ->exists();

        $canAccessInterviewUser = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'INTERVIEWUSER')
            ->where('status', 'A')
            ->exists();

        $canAccessInterviewHC = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'INTERVIEWHC')
            ->where('status', 'A')
            ->exists();

        $canAccessJoin = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'JOIN')
            ->where('status', 'A')
            ->exists();

        $canAccessSchedule = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'SCHEDULE')
            ->where('status', 'A')
            ->exists();

        // tampilkan tab "Schedule" hanya jika ada step pada dokumen ini
        // yang statusnya masih pending dan jenis step-nya memang "schedule"
        // $currentStep = JobApplyStep::where('docid', $career->docid)
        //     ->where('status', 'P')
        //     ->orderBy('step_order', 'ASC')
        //     ->first();

        // $stepsSchedule = [3, 5]; // sesuaikan kebutuhanmu
        // $canAccessSchedule = $currentStep && in_array($currentStep->step_order, $stepsSchedule, true);

        $companyaddress = CompanyAddress::whereNotNull('site')
            ->where('status', 'A')
            ->get();


        $remapped_from = null;
        $remapped_to   = null;

        if ($career->status === 'T') {
            // Viewing the OLD apply — show where it was remapped TO
            $remapped_to = DB::connection('mysql3')
                ->table('hr_trx_job_apply as a')
                ->leftJoin('hr_trx_jobposting as jp', 'jp.docid', '=', 'a.jobid')
                ->where('a.docid', $career->docid)
                ->whereNotIn('a.status', ['T', 'X'])
                ->where('a.jobid', '!=', $career->docidposting)
                ->orderByDesc('a.id')
                ->select('a.jobid', 'jp.job_title')
                ->first();
        } else {
            // Viewing the NEW apply — show where it came FROM (most recent T record)
            $remapped_from = DB::connection('mysql3')
                ->table('hr_trx_job_apply as a')
                ->leftJoin('hr_trx_jobposting as jp', 'jp.docid', '=', 'a.jobid')
                ->where('a.docid', $career->docid)
                ->where('a.status', 'T')
                ->where('a.jobid', '!=', $career->docidposting)
                ->orderByDesc('a.id')
                ->select('a.jobid', 'jp.job_title')
                ->first();
        }

        return view('pages.careers.showcareers', compact(
            'career','applicant','applicant_family','applicant_marital','applicant_education','applicant_working',
            'applicant_reference','applicant_language','applicant_course','applicant_sw','applicant_skill','jobapplystep',
            'jobres','jobqua','jobposting','tr_checklist','year','photo','cv','coverletter','transkip','ijazah','user','datenow',
            'assessmentGroups','tr_assessment','tr_assessment_user','assessmentGroupsUser','agenda','userlist',
            'typestep','payrolls','onboarding','sign','canAccessPayroll','canAccessAssessment','canAccessSchedule','companyaddress',
            'canAccessChecklist','canAccessInterviewUser','canAccessInterviewHC','canAccessPayroll','canAccessJoin',
            'remapped_from', 'remapped_to'
        ));
    }


    public function updateAssessment(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $now = now();
        $docid = $request->docid;

        // Update header (TrAssessment)
        $assessment = TrAssessment::where('jobapply_id', $docid)
            ->where('type','hc')
            ->first();
        // dd($assessment);
        if ($assessment) {
            $assessment->assessment_date = $request->interview_date.' '.$request->interview_time;
            $assessment->total_assessment_score_value = $request->totalscore;
            // $assessment->result_status = $request->result_status;
            $assessment->user = $user->username;
            $assessment->updated_user = $user->username;
            $assessment->updated_at = $now;
            $assessment->save();
        }

        // Update detail scores
        foreach ($request->scores as $index => $value) {
            // Reset semua score_value jadi 0 dulu di group ini
            TrAssessmentdetail::where('docid', $assessment->docid)
                ->where('step_order_group', $index + 1)
                ->update([
                    'assessment_score_value' => 0,
                    'updated_user' => $user->username,
                    'updated_at' => $now
                ]);

            // Update score_value yang dipilih
            TrAssessmentdetail::where('docid', $assessment->docid)
                ->where('step_order_group', $index + 1)
                ->where('assessment_score', $value)
                ->update([
                    'assessment_score_value' => $value,
                    'updated_user' => $user->username,
                    'updated_at' => $now
                ]);
        }


        return response()->json(['success' => true, 'message' => 'Assessment updated successfully']);
    }

    public function updateAssessmentuser(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $now = now();
        $docid = $request->docid;

        // Update header (TrAssessment)
        $assessment = TrAssessment::where('jobapply_id', $docid)
            ->where('type','user')
            ->first();
        // dd($assessment);
        if ($assessment) {
            $assessment->assessment_date = $request->interview_date.' '.$request->interview_time;
            $assessment->total_assessment_score_value = $request->totalscore;
            // $assessment->result_status = $request->result_status;
            $assessment->user = $user->username;
            $assessment->updated_user = $user->username;
            $assessment->updated_at = $now;
            $assessment->save();
        }

        // Update detail scores
        foreach ($request->scores as $index => $value) {
            // Reset semua score_value jadi 0 dulu di group ini
            TrAssessmentdetail::where('docid', $assessment->docid)
                ->where('step_order_group', $index + 1)
                ->update([
                    'assessment_score_value' => 0,
                    'updated_user' => $user->username,
                    'updated_at' => $now
                ]);

            // Update score_value yang dipilih
            TrAssessmentdetail::where('docid', $assessment->docid)
                ->where('step_order_group', $index + 1)
                ->where('assessment_score', $value)
                ->update([
                    'assessment_score_value' => $value,
                    'updated_user' => $user->username,
                    'updated_at' => $now
                ]);
        }


        return response()->json(['success' => true, 'message' => 'Assessment updated successfully']);
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
    //     $request->validate([
    //         'comment' => 'required|string|max:500',
    //     ]);
    //     // dd($id);
    //     $user = request()->user();
    //     $comment = new T_Message();
    //     $comment->docid = $id;
    //     $comment->doctype = 'PRF';
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

     public function fetchComments($refnbr)
    {
        $comments = TrMessage::where('refnbr', $refnbr)
            ->orderBy('message_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'comments' => $comments
        ]);
    }

    public function storeComment(Request $request, $refnbr)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $user = Auth::user();   // ambil user login

        $comment = TrMessage::create([
            'refnbr'        => $refnbr,
            'doctype'       => 'PRF',
            'message_date'  => now(),
            'cpny_id'        => $user->cpnyid ?? null,
            'department_id' => $user->departmentid ?? null,
            'username'      => $user->username,
            'name'          => $user->name,
            'message'       => $request->comment,
            'status'        => 'A',
            'created_by'    => $user->username,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment added successfully!',
            'comment' => $comment
        ]);
    }

    public function approveCareer(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();
        $user = request()->user(); // Ambil user yang login

        $career = Career::where('docid', $docid)->whereNotIn('status', ['T', 'X'])->first();

        if (!$career) {
            return response()->json(['success' => false, 'message' => 'Career not found'], 404);
        }

        $jobposting = Jobposting::where('docid', $career->jobid)->first();

        if (!$jobposting) {
            return response()->json(['success' => false, 'message' => 'Job Posting not found'], 404);
        }
        // dd($user);
        // Cek apakah user termasuk dalam daftar approval
        // $cek_approval = T_approval::where('docid', $jobposting->refid)
        //     ->where('aprvusername', 'like', '%' . $user->username . '%')
        //     ->where('aprvid','>',1)
        //     ->first();
        $cek_approval = TrApproval::where('refnbr', $jobposting->refid)
            ->where('aprv_username', 'like', '%' . $user->username . '%')
            ->where('aprv_leveling', '>', 1)
            ->first();

        $hasGroupAccess = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'STEP')
            ->where('status', 'A')
            ->first();

        if (!$cek_approval && !$hasGroupAccess) {
            return response()->json(['success' => false, 'message' => "You Can't Approve!"], 403);
        }

        $t_approval = JobApplyStep::where('docid', $career->docid)
            ->where('jobid', $career->jobid)
            ->where('status', 'P')
            ->orderBy('step_order', 'ASC')
            ->first();

        if($hasGroupAccess){
            $user_step_pic = 'HC';
        }else{
            $user_step_pic = 'USER';
        }
        // perbaiki disini jika $t_approval->step_pic = $user_step_pic maka bisa approve, jika tidak sama tdk bisa approve

        if (!$t_approval) {
            return response()->json(['success' => false, 'message' => 'No pending step to approve'], 404);
        }

        // Tambahkan validasi step_pic
        if ($t_approval->step_pic !== $user_step_pic) {
            return response()->json(['success' => false, 'message' => "You can't approve this step"], 403);
        }

        if ($t_approval->step_order == 2) {
            $this->insert_checklist($career, $user);
            $this->insert_assessment($career, $user);
            // $this->insert_psychotest($career, $user);
            $this->update_trx_approval($career, $user);
            $this->sendemail_applicant($career, $user);
        }

        if ($t_approval->step_order == 1) {
            $this->insert_trx_approval($career, $user);
        }


        $t_approval->status = 'A';
        $t_approval->aprvuserdate = $datestamp;
        $t_approval->aprvusername = $user->username;
        $t_approval->save();

        $t_approval_next = JobApplyStep::where('docid', $career->docid)
            ->where('jobid', $career->jobid)
            ->where('status', 'P')
            ->orderBy('step_order', 'ASC')
            ->first();

        if ($t_approval_next) {
            $career->apply_step = $t_approval_next->step_id;
            $career->prev_apply_step = $t_approval->step_id;
        }

        $career->updated_user = $user->username;
        $career->updated_at = $datestamp;
        $career->save();

        // Hitung apakah ini adalah approval terakhir
        $count_approval = JobApplyStep::where('docid', $career->docid)
            ->where('jobid', $career->jobid)
            ->where('status', 'P')
            ->count();

        if ($count_approval === 0) {
            $career->status = 'C';
            $career->apply_step = $t_approval->step_id;
            $career->completed_user = $user->username;
            $career->completed_at = $datestamp;
            $career->save();
        }

        return response()->json(['success' => true, 'message' => 'Career approved successfully']);
    }

    public function rejectCareer(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();
        $user = request()->user(); // Ambil user yang login

        $career = Career::where('docid', $docid)->whereNotIn('status', ['T', 'X'])->first();
        if (!$career) {
            return response()->json(['success' => false, 'message' => 'Career not found'], 404);
        }

        $jobposting = Jobposting::where('docid', $career->jobid)->first();
        if (!$jobposting) {
            return response()->json(['success' => false, 'message' => 'Job Posting not found'], 404);
        }

        // Cek apakah user termasuk dalam daftar approval
        // $cek_approval = T_approval::where('docid', $jobposting->refid)
        //     ->where(function ($q) use ($user) {
        //         $q->where('aprvusername', $user->username)
        //         ->orWhere('aprvusername', 'like', '%'.$user->username.'%');
        //     })
        //     ->exists();
        $cek_approval = TrApproval::where('refnbr', $jobposting->refid)
            ->where(function ($q) use ($user) {
                $q->where('aprv_username', $user->username)
                ->orWhere('aprv_username', 'like', '%'.$user->username.'%');
            })
            ->exists();

        $hasGroupAccess = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'STEP')
            ->where('status', 'A')
            ->exists();

        if (!$cek_approval && !$hasGroupAccess) {
            return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
        }

        $t_approval = JobApplyStep::where('docid', $career->docid)
            ->where('jobid', $career->jobid)
            ->where('status', 'P')
            ->orderBy('step_order', 'ASC')
            ->first();

        if (!$t_approval) {
            return response()->json(['success' => false, 'message' => 'No pending step to reject'], 404);
        }

        // Role PIC user untuk validasi step_pic
        $user_step_pic = $hasGroupAccess ? 'HC' : 'USER';

        // Validasi step_pic harus sama
        if ($t_approval->step_pic !== $user_step_pic) {
            return response()->json(['success' => false, 'message' => "You can't reject this step"], 403);
        }

        // Reject step berjalan
        $t_approval->status = 'R';
        $t_approval->aprvuserdate = $datestamp;
        $t_approval->aprvusername = $user->username;
        $t_approval->save();

        // Set career jadi Rejected
        $career->status = 'R';
        $career->updated_user = $user->username;
        $career->updated_at = $datestamp;
        $career->save();

        // Tutup semua step pending lainnya
        $t_aprv_sisa = JobApplyStep::where('docid', $career->docid)
            ->where('jobid', $career->jobid)
            ->where('status', 'P')
            ->get();

        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        // Kirim notifikasi internal (comment) bila ada
        $id = $career->id;
        $doctype = 'JAP';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        // Kirim email reject ke applicant
        $this->sendemail_rejected_applicant($career, $user);

        return response()->json(['success' => true, 'message' => 'Career rejected successfully']);
    }


    public function rollbackCareer(Request $request, $docid)
    {
        $now  = Carbon::now()->toDateTimeString();
        // $user = $request->user();
        $user = Auth::user();
        $username = $user ? $user->username : 'system';

        $career = Career::where('docid', $docid)->whereNotIn('status', ['T', 'X'])->first();
        if (!$career) {
            return response()->json(['success' => false, 'message' => 'Career not found'], 404);
        }

        $jobposting = Jobposting::where('docid', $career->jobid)->first();
        if (!$jobposting) {
            return response()->json(['success' => false, 'message' => 'Job Posting not found'], 404);
        }

        // CEK USER DALAM APPROVAL LINE
        // $inApprovalLine = T_approval::where('docid', $jobposting->refid)
        //     ->where(function ($q) use ($user) {
        //         $q->where('aprvusername', $user->username)
        //         ->orWhere('aprvusername', 'like', '%'.$user->username.'%');
        //     })
        //     ->exists();
        $inApprovalLine = TrApproval::where('refnbr', $jobposting->refid)
            ->where(function ($q) use ($user) {
                $q->where('aprv_username', $user->username)
                ->orWhere('aprv_username', 'like', '%'.$user->username.'%');
            })
            ->exists();

        $hasGroupAccess = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'STEP')
            ->where('status', 'A')
            ->exists();

        if (!$inApprovalLine && !$hasGroupAccess) {
            return response()->json(['success' => false, 'message' => "You can't rollback!"], 403);
        }


        TrApproval::where('refnbr', $docid)   // mapping dari docid → refnbr
            ->where('status', 'P')
            ->delete();

        /** Ambil step terakhir yang sudah Approved / Rejected */
        $targetStep = JobApplyStep::where('docid', $career->docid)
            ->where('jobid', $career->jobid)
            ->whereIn('status', ['A', 'R'])
            ->orderBy('step_order', 'DESC')
            ->first();

        if (!$targetStep) {
            return response()->json(['success' => false, 'message' => 'No approved/rejected step to rollback'], 404);
        }

        // Role PIC user -> validasi step_pic
        $userStepPic = $hasGroupAccess ? 'HC' : 'USER';
        if (($targetStep->step_pic ?? '') !== $userStepPic) {
            return response()->json(['success' => false, 'message' => "You can't rollback this step"], 403);
        }

        DB::beginTransaction();
        try {

            /** Reset kembali ke pending */
            $targetStep->status       = 'P';
            $targetStep->aprvusername = null;
            $targetStep->aprvuserdate = null;
            $targetStep->save();

            /** Update status Career kembali Pending */
            $career->status       = 'P';
            $career->updated_user = $user->username;
            $career->updated_at   = $now;
            $career->save();

            /** Simpan komentar rollback */
            if ($request->reason) {
                $request->merge(['comment' => "[ROLLBACK] ".$request->reason]);
            }
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($career->id, 'JAP', $request);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Career rolled back successfully']);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['success' => false, 'message' => 'Failed to rollback career'], 500);
        }
    }



    public function checkApproval($id, $action)
    {
        $user = Auth::user(); // user login

        $career = Career::where('docid', $id)->whereNotIn('status', ['T', 'X'])->first();
        if (!$career) {
            return response()->json(['canPerformAction' => false, 'message' => 'Career not found'], 404);
        }

        $jobposting = Jobposting::where('docid', $career->jobid)->first();
        if (!$jobposting) {
            return response()->json(['canPerformAction' => false, 'message' => 'Job posting not found'], 404);
        }

        // Apakah user ada di daftar approval dokumen ini?
        // $isInApprovalList = T_approval::where('docid', $jobposting->refid)
        //     ->where(function ($q) use ($user) {
        //         $q->where('aprvusername', $user->username)
        //         ->orWhere('aprvusername', 'like', '%'.$user->username.'%');
        //     })
        //     ->exists();
        $isInApprovalList = TrApproval::where('refnbr', $jobposting->refid)
            ->where(function ($q) use ($user) {
                $q->where('aprv_username', $user->username)
                ->orWhere('aprv_username', 'like', '%'.$user->username.'%');
            })
            ->exists();

        // Apakah user punya akses group STEP aktif?
        $hasGroupAccess = GroupAccspecific::where('username', $user->username)
            ->where('group_access_id', 'STEP')
            ->where('status', 'A')
            ->exists();

        // Jika butuh logika tambahan per action, bisa ditaruh di sini
        if (in_array($action, ['approve', 'reject', 'revise'])) {
            // Tambahkan logika spesifik jika diperlukan
        }

        $canPerformAction = $isInApprovalList || $hasGroupAccess;

        return response()->json([
            'canPerformAction' => $canPerformAction,
            'message' => $canPerformAction ? 'Authorized' : 'Unauthorized'
        ]);
    }


    public function insert_checklist($career, $user)
    {
        // dd($career);

        DB::beginTransaction();
        try {
            $doctype ='CHK';
            $datenow = Carbon::now()->format('Y-m-d');
            $datestamp = Carbon::now()->toDateTimeString();
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $user = Auth::user();

            $existing = Trchecklist::where('jobid', $career->jobid)
                ->where('applicant_id', $career->applicant_id)
                ->first();

            if ($existing) {
                return response()->json([
                'error' => true,
                'message' => 'You have already checklist.'
                ], 409); // Conflict
            }

            // Generate task ID
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'A',
                    'number' => 1
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->number = $urutan;
                $autonbr->save();
            }

            $tglbln = substr($year, 2) . $month;
            $docid = $doctype . $tglbln . sprintf("%05d", $urutan);

            $ms_checklist = Mschecklist::where('status','A')
                ->orderby('step_order','ASC')
                ->get();

            foreach ($ms_checklist as $cek) {
                Trchecklist::create([
                    'docid' => $docid,
                    'jobapply_id' => $career->docid,
                    'jobid' => $career->jobid,
                    'applicant_id' => $career->applicant_id,
                    'checklist_id' => $cek->checklist_id,
                    'checklist_type' => $cek->checklist_type,
                    'step_order' => $cek->step_order,
                    'checklist_mandatory' => $cek->checklist_mandatory,
                    'checklist_receive' => 0,
                    'created_user' => $user->username,
                    'status' => 'P'
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'ms_checklist' => $ms_checklist]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan Transaksi Checklist', 'message' => $e->getMessage()], 500);
        }
    }

    public function insert_assessment($career, $user)
    {

        DB::beginTransaction();
        try {
            $doctype = 'JOS';
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

            $types = ['hc', 'user'];
            $createdDocs = [];

            // Validasi awal
            $existing = TrAssessment::where('jobid', $career->jobid)
                ->where('applicant_id', $career->applicant_id)
                ->whereIn('type', $types)
                ->exists();

            if ($existing) {
                return response()->json([
                    'error' => true,
                    'message' => 'Assessment already exists.'
                ], 409);
            }

            $ms_checklist = MsAssessment::orderBy('step_order_group', 'ASC')
                ->orderBy('step_order', 'ASC')
                ->get();

            if ($ms_checklist->isEmpty()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Master assessment not found.'
                ], 404);
            }

            foreach ($types as $type) {
                // Ambil nomor baru untuk masing-masing type
                $autonbr = Autonbr::lockForUpdate()
                    ->where('doctype', $doctype)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('status', 'A')
                    ->first();

                if (!$autonbr) {
                    $autonbr = Autonbr::create([
                        'doctype' => $doctype,
                        'year' => $year,
                        'month' => $month,
                        'status' => 'A',
                        'number' => 1
                    ]);
                    $urutan = 1;
                } else {
                    $urutan = $autonbr->number + 1;
                    $autonbr->number = $urutan;
                    $autonbr->save();
                }

                $tglbln = substr($year, 2) . $month;
                $docid = $doctype . $tglbln . sprintf("%05d", $urutan);

                // Insert detail
                foreach ($ms_checklist as $cek) {
                    TrAssessmentdetail::create([
                        'docid' => $docid,
                        'jobapply_id' => $career->docid,
                        'jobid' => $career->jobid,
                        'applicant_id' => $career->applicant_id,
                        'assessment_id' => $cek->assessment_id,
                        'step_order_group' => $cek->step_order_group,
                        'step_order' => $cek->step_order,
                        'assessment_type' => $cek->assessment_type,
                        'assessment_score' => $cek->assessment_score,
                        'assessment_score_value' => 0,
                        'created_user' => $user->username,
                        'status' => 'P'
                    ]);
                }

                // Insert header
                TrAssessment::create([
                    'docid' => $docid,
                    'jobapply_id' => $career->docid,
                    'jobid' => $career->jobid,
                    'applicant_id' => $career->applicant_id,
                    'type' => $type,
                    'total_assessment_score_value' => 0,
                    'created_user' => $user->username,
                    'status' => 'P'
                ]);

                $createdDocs[] = $docid;
            }

            DB::commit();
            return response()->json(['success' => true, 'created_docids' => $createdDocs]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to insert assessment.',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function insert_jobposting($id)
    {

        DB::beginTransaction();
        try {
            $doctype = 'JOB';
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            // Generate task ID
            $autonbr = AutonbrJobportal::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                $autonbr = AutonbrJobportal::create([
                    'doctype' => $doctype,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'A',
                    'number' => 1
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->number = $urutan;
                $autonbr->save();
            }

            $tglbln = substr($year, 2) . $month;
            $docid = $doctype . $tglbln . sprintf("%03d", $urutan);

            $career = Career::where('docid', $id)
                ->first();

            $task = Jobposting::create([
                'docid' => $docid,
                'refid' => $career->docid,
                'cpnyid' => $career->cpnyid,
                'departementid' => $career->departementid,
                'date' => $datenow,
                'job_title' => $career->job_title,
                'job_level' => $career->job_level,
                'immediate_superior' => $career->immediate_superior,
                'state_position' => $career->state_position,
                'job_type' => $career->job_type,
                'reason_vacancy' => $career->reason_vacancy,
                'required' => $career->required,
                'actual' => $career->actual,
                'total_actual' => $career->total_actual,
                'education' => $career->education,
                'experience_start' => $career->experience_start,
                'experience_end' => $career->experience_end,
                'created_user' => $user->username,
                'status' =>'C'
            ]);

            $jobres = JobResponsiblities::where('docid', $id)
                ->get();

            foreach ($jobres as $jr) {
                JobpostingResponsiblities::create([
                    'docid' => $docid,
                    'refid' => $jr->docid,
                    'no_job_responsiblities' => $jr->no_job_responsiblities,
                    'job_responsibilities_descr' => $jr->job_responsibilities_descr,
                    'created_user' => $jr->created_user,
                    'status' => 'C'
                ]);
            }

            $jobqua = JobQualification::where('docid', $id)
                ->get();

            foreach ($jobqua as $jq) {
                JobpostingQualification::create([
                    'docid' => $docid,
                    'refid' => $jq->docid,
                    'no_job_qualification' => $jq->no_job_qualification,
                    'job_qualification_descr' => $jq->job_qualification_descr,
                    'created_user' => $jq->created_user,
                    'status' => 'C'
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'task' => $task]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan task', 'message' => $e->getMessage()], 500);
        }
    }

    public function uploadDocument(Request $request)
    {
        $user      = $request->user();
        $now       = Carbon::now();
        $datestamp = $now->toDateTimeString();
        $year      = (int) $now->format('Y');
        $month     = (int) $now->format('m');

        $checklist = Trchecklist::findOrFail($request->checklist_id);

        // Validasi (aktifkan kembali)
        // $request->validate([
        //     'checklist_id' => 'required|exists:tr_checklist,id',
        //     'document'     => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB
        // ]);

        $originalName = null;
        $gcsPath      = null;

        if ($request->hasFile('document')) {
            $file         = $request->file('document');
            $originalName = str_replace('%', '', $file->getClientOriginalName());
            $ext          = $file->getClientOriginalExtension();
            $randomPrefix = md5(random_int(1, 99999999));

            // Folder: att-job-career/{Y}/{m}/{checklist_id}
            // $ymFolder = sprintf('att-job-career/%d/%02d/%s', $year, $month, $request->applicant_id);
            $ymFolder = 'att-job-career/' . $year . '/' . $month . '/' . $checklist->applicant_id;
            $filename = $randomPrefix . '.' . $ext;
            $gcsPath  = "{$ymFolder}/{$filename}";

            // --- Init GCS dari config disks.gcs ---
            $config  = config('filesystems.disks.gcs');
            $storage = new StorageClient([
                'projectId'   => $config['project_id'],
                'keyFilePath' => $config['key_file'],
            ]);
            $bucket = $storage->bucket($config['bucket']);

            try {
                $bucket->upload(
                    fopen($file->getPathname(), 'r'),
                    [
                        'name'         => $gcsPath,
                        'predefinedAcl'=> 'private', // simpan private
                        'metadata'     => [
                            'contentType' => $file->getMimeType(),
                        ],
                    ]
                );
                Log::info('Upload checklist document success', ['checklist_id' => $request->checklist_id, 'gcsPath' => $gcsPath]);
            } catch (\Throwable $e) {
                Log::error('Upload checklist document failed', [
                    'checklist_id' => $request->checklist_id,
                    'error' => $e->getMessage(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal upload dokumen: ' . $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada file yang diunggah.',
            ], 422);
        }

        // Simpan ke DB

        $checklist->checklist_filename   = $originalName; // nama asli user
        $checklist->checklist_attachfile = $gcsPath;      // path di GCS
        $checklist->checklist_receive    = 1;
        $checklist->checklist_by         = $user->username ?? $user->name ?? 'system';
        $checklist->checklist_at         = $datestamp;
        $checklist->status               = 'A';
        $checklist->save();

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded',
            'path'    => $gcsPath,
            'filename'=> $originalName,
        ]);
    }


    public function uploadDocument_xxx(Request $request)
    {
        $user = request()->user();
        $datestamp = Carbon::now()->toDateTimeString();
        $year = now()->year;

        // dd($request->all());

        // $request->validate([
        //     'checklist_id' => 'required|exists:tr_checklist,id',
        //     'document' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        // ]);

        $document = null;

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $randomNumber = random_int(10000000, 99999999);
            $originalName = str_replace('%', '', $file->getClientOriginalName());
            $ext        = $file->getClientOriginalExtension();
            $document = md5($randomNumber) . '.' . $ext;

            $folder_attach = public_path('/attachments/' . $year);
            if (!is_dir($folder_attach)) {
                mkdir($folder_attach, 0777, true);
            }
            $file->move($folder_attach, $document);
        }

        $checklist = Trchecklist::findOrFail($request->checklist_id);
        // dd($checklist);
        $checklist->checklist_filename = $originalName;
        $checklist->checklist_attachfile = $document;
        $checklist->checklist_receive = 1;
        $checklist->checklist_by = $user->username;
        $checklist->checklist_at = $datestamp;
        $checklist->status = 'A';
        $checklist->save();


        return response()->json(['success' => true, 'message' => 'Document uploaded']);
    }

    public function sendemail_applicant($career, $user)
    {
        $applicant = Applicant::where('applicant_id', $career->applicant_id)->first();

        $jobapply = JobApply::where('docid', $career->docid)
            ->where('applicant_id', $applicant->applicant_id)
            ->where('jobid', $career->jobid)
            ->first();

        $jobapply->status = 'P';
        $jobapply->save();

        if (!$applicant || empty($applicant->email_address)) {
            return response()->json(['error' => 'Applicant email not found.'], 404);
        }

<<<<<<< Updated upstream
        $encryptedDocId = Crypt::encryptString($career->applicant_id);

        $data = [
            'name' => $applicant->full_name ?? 'Pelamar',
            // 'url' => url('http://careerjakarta.pakuwon.local/checkform') // gunakan URL lengkap
            'url'  => url("https://careerjakarta.pakuwon.com/checkform/{$encryptedDocId}")
        ];

        Mail::send('emails.mailapplicant', $data, function ($message) use ($applicant,$data) {
            $message->to($applicant->email_address)
                    ->subject('📩 Lengkapi Aplikasi Anda di Pakuwon Career');
            $message->from('digitalserver@pakuwon.com', 'Pakuwon Career');
        });
=======
        $is_remapped = DB::connection('mysql3')
            ->table('hr_trx_job_apply')
            ->where('docid', $career->docid)
            ->where('status', 'T')
            ->where('jobid', '!=', $career->jobid)
            ->exists();

        if ($is_remapped && $applicant->process_step == 2) {
            // Remapped + already filled form — notify of position change only
            $jobposting = Jobposting::where('docid', $career->jobid)->first();
            $division   = \App\Models\MsDivision::where('division_id', $jobposting->division_id ?? '')->value('division_name');
            $department = \App\Models\DepartmentHR::where('department_id', $jobposting->departementid ?? '')->value('department_name');
            $data = [
                'name'       => $applicant->full_name ?? 'Pelamar',
                'job_title'  => $jobposting->job_title ?? '-',
                'division'   => $division ?? '-',
                'department' => $department ?? '-',
            ];
            Mail::send('emails.mailapplicant_remapped', $data, function ($message) use ($applicant) {
                $message->to($applicant->email_address)
                        ->subject('📩 Update Posisi Lamaran Anda di Pakuwon Career');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon Career');
            });
        } elseif ($is_remapped && $applicant->process_step != 2) {
            // Remapped + hasn't filled form — notify position change and send form link
            $jobposting     = Jobposting::where('docid', $career->jobid)->first();
            $encryptedDocId = Crypt::encryptString($career->applicant_id);
            $data = [
                'name'      => $applicant->full_name ?? 'Pelamar',
                'job_title' => $jobposting->job_title ?? '-',
                'url'       => url("https://careerjakarta.pakuwon.com/checkform/{$encryptedDocId}"),
            ];
            Mail::send('emails.mailapplicant_remapped_form', $data, function ($message) use ($applicant) {
                $message->to($applicant->email_address)
                        ->subject('📩 Update Posisi Lamaran Anda di Pakuwon Career');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon Career');
            });
        } else {
            // Not remapped + hasn't filled form — send form link
            $encryptedDocId = Crypt::encryptString($career->applicant_id);
            $data = [
                'name' => $applicant->full_name ?? 'Pelamar',
                'url'  => url("https://careerjakarta.pakuwon.com/checkform/{$encryptedDocId}"),
            ];
            Mail::send('emails.mailapplicant', $data, function ($message) use ($applicant) {
                $message->to($applicant->email_address)
                        ->subject('📩 Lengkapi Aplikasi Anda di Pakuwon Career');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon Career');
            });
        }
>>>>>>> Stashed changes

        return response()->json(['success' => 'Email has been sent to applicant.']);
    }

    public function checkRejectPermission($docid)
    {
        $user = Auth::user();

        $career = Career::where('docid', $docid)->whereNotIn('status', ['T', 'X'])->first();

        $step = JobApplyStep::where('docid', $docid)
            ->when($career, fn($q) => $q->where('jobid', $career->jobid))
            ->where('status', 'P')
            ->orderBy('step_order', 'ASC')
            ->first();

        if ($step) {
            $canReject = str_contains($step->step_approve, 'Reject');
            return response()->json(['canReject' => $canReject]);
        }

        return response()->json(['canReject' => false]);
    }

    public function checkRollbackPermission($docid)
    {
        $user = Auth::user();

        $career = Career::where('docid', $docid)->whereNotIn('status', ['T', 'X'])->first();

        $step = JobApplyStep::where('docid', $docid)
            ->when($career, fn($q) => $q->where('jobid', $career->jobid))
            ->whereIn('status', ['A', 'R'])
            ->orderBy('step_order', 'DESC')
            ->first();

        if ($step) {
            $canRollback = str_contains($step->step_approve ?? '', 'Rollback');
            return response()->json(['canRollback' => $canRollback]);
        }

        return response()->json(['canRollback' => false]);
    }


    public function generatePayroll(Request $request)
    {
        // dd($request->all());
        // $employee = \DB::table('employees')->where('id', $request->employee_id)->first();
        $applicant = Applicant::where('applicant_id', $request->applicant_id)->first();
        $company = MsCompany::where('cpny_id', $request->cpnyid)->first();

        $templatePath = storage_path('app/templates/PayrollConfirmation.docx');
        $tempDocPath = storage_path('app/temp_filled.docx');
        $datebirth = Carbon::parse($applicant->date_of_birth)->translatedFormat('d F Y');
        $templateProcessor = new TemplateProcessor($templatePath);

        // Set placeholder (harus cocok dengan yang di dalam DOCX)
        $templateProcessor->setValue('full_name', $applicant->full_name);
        $templateProcessor->setValue('gender', $applicant->gender);
        $templateProcessor->setValue('birth_place', $applicant->birth_place);
        $templateProcessor->setValue('datebirth', $applicant->date_of_birth);
        $templateProcessor->setValue('religion', $applicant->religion);
        $templateProcessor->setValue('ktp_id', $applicant->ktp_id);
        $templateProcessor->setValue('cpnyid', $company->cpnyname);
        $templateProcessor->setValue('departementid', $request->departementid);
        $templateProcessor->setValue('job_title', $request->job_title);
        $templateProcessor->setValue('job_level', $request->job_level);

        // $templateProcessor->setValue('salary', number_format($employee->salary, 2));
        // $templateProcessor->setValue('date', now()->format('d M Y'));

        $templateProcessor->saveAs($tempDocPath);

        // Convert DOCX to HTML (basic method)
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempDocPath);
        $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
        ob_start();
        $htmlWriter->save('php://output');
        $htmlContent = ob_get_clean();

        $pdf = PDF::loadHTML($htmlContent)->setPaper('A4', 'portrait');
        return $pdf->download('payroll-confirmation.pdf');
    }

    public function generateOffering(Request $request)
    {
        // dd($request->all());
        // $employee = \DB::table('employees')->where('id', $request->employee_id)->first();
        $applicant = Applicant::where('applicant_id', $request->applicant_id)->first();
        $company = MsCompany::where('cpny_id', $request->cpnyid)->first();

        $templatePath = storage_path('app/templates/PayrollConfirmation.docx');
        $tempDocPath = storage_path('app/temp_filled.docx');
        $datebirth = Carbon::parse($applicant->date_of_birth)->translatedFormat('d F Y');
        $templateProcessor = new TemplateProcessor($templatePath);

        // Set placeholder (harus cocok dengan yang di dalam DOCX)
        $templateProcessor->setValue('full_name', $applicant->full_name);
        $templateProcessor->setValue('gender', $applicant->gender);
        $templateProcessor->setValue('birth_place', $applicant->birth_place);
        $templateProcessor->setValue('datebirth', $datebirth);
        $templateProcessor->setValue('religion', $applicant->religion);
        $templateProcessor->setValue('ktp_id', $applicant->ktp_id);
        $templateProcessor->setValue('cpnyid', $company->cpnyname);
        $templateProcessor->setValue('departementid', $request->departementid);
        $templateProcessor->setValue('job_title', $request->job_title);
        $templateProcessor->setValue('job_level', $request->job_level);
        $templateProcessor->setValue('date', now()->format('d M Y'));
        // $templateProcessor->setValue('salary', number_format($employee->salary, 2));


        $templateProcessor->saveAs($tempDocPath);

        // Convert DOCX to HTML (basic method)
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempDocPath);
        $htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
        ob_start();
        $htmlWriter->save('php://output');
        $htmlContent = ob_get_clean();

        $pdf = PDF::loadHTML($htmlContent)->setPaper('A4', 'portrait');
        return $pdf->download('payroll-confirmation.pdf');
    }

    public function pdfPayrollconfirmation(Request $request)
    {
        // dd($request->all());
        $applicant = Applicant::where('applicant_id', $request->applicant_id)->first();
        $company = MsCompany::select(['cpny_id', 'cpnyname'])->where('cpny_id', $request->cpnyid)->first();
        $payrollconfirm = Payrollconfirm::where('applicant_id', $request->applicant_id)->first();
        $dept = MsDepartment::where('deptname', $request->departementid)->first();
        $t_approval = SignPayroll::where('docid', $request->jobapply_id)
            ->orderby('aprvid','ASC')
            ->get();

        $net_salary   = (float) (optional($payrollconfirm)->net_salary ?? 0);
        // dd($t_approval);
        if($payrollconfirm->contract_term == null){
            $contract_term = '';
        }else{
            $contract_term = '- Contract ' . $payrollconfirm->contract_term . ' bulan';
        }

        $data = [
            'cpnyid' => $company->cpnyname,
            'departementid' => ucwords(strtolower($dept->dept_fullname)) ?? ucwords(strtolower($request->departementid)),
            'full_name' => $applicant->full_name,
            'gender' => $applicant->gender,
            'birth_place' => $applicant->birth_place,
            'martial_status' => $applicant->martial_status,
            'datebirth' => \Carbon\Carbon::parse($applicant->date_of_birth)->format('d F Y'),
            'religion' => $applicant->religion,
            'ktp_id' => $applicant->ktp_id,
            'job_title' => $request->job_title,
            'job_level' => $request->job_level,
            'tax_liability' => $payrollconfirm->tax_liability ?? '-',
            'npwp_id' => $payrollconfirm->npwp_id ?? '-',
            'bank_account' => $payrollconfirm->bank_account ?? '-',
            'bank_name' => $payrollconfirm->bank_name ?? '-',
            'net_salary' => number_format($net_salary) ?? '0',
            'other_facility' => $payrollconfirm->other_facility ?? '-',
            'availability_date' => $payrollconfirm->availability_date ?? '-',
            'work_start_date' => $payrollconfirm->work_start_date ?? '-',
            'contract_term' => $contract_term,
            'employment_status' => $payrollconfirm->employment_status ?? '-',
            'approvals' => $t_approval,
        ];

        return Pdf::loadView('pages.careers.pdfpayroll', $data)
            ->setPaper('a4')
            ->stream('payroll-confirmation.pdf');
    }


    public function pdfOfferingletter(Request $request)
    {
        // dd($request->all());

        $applicant = Applicant::where('applicant_id', $request->applicant_id)->first();
        $company = MsCompany::where('cpny_id', $request->cpnyid)->first();
        $datebirth = Carbon::parse($applicant->date_of_birth)->translatedFormat('d F Y');
        $payrollconfirm = Payrollconfirm::where('applicant_id', $request->applicant_id)->first();

        // $net_salary = $payrollconfirm->net_salary ?? 0;
        // $salary_words = terbilang($net_salary) . ' rupiah';
         // Net salary aman + terbilang
        $net_salary   = (float) (optional($payrollconfirm)->net_salary ?? 0); // <- aman jika payroll null
        $salary_words = ucfirst($this->terbilang($net_salary)) . ' rupiah'; // <- pakai $this
        $work_start_date = $payrollconfirm->work_start_date ? Carbon::parse($payrollconfirm->work_start_date)->translatedFormat('d F Y') : '-';
        $availability_date = $payrollconfirm->availability_date ? Carbon::parse($payrollconfirm->availability_date)->translatedFormat('d F Y') : '-';

        $companyaddress = CompanyAddress::where('cpnyid', $request->cpnyid)->first();

        $data = [
            'cpnyid' => $company->cpnyname,
            'departementid' => $request->departementid,
            'full_name' => $applicant->full_name,
            'gender' => $applicant->gender,
            'birth_place' => $applicant->birth_place,
            'martial_status' => $applicant->martial_status,
            'datebirth' => $datebirth,
            'religion' => $applicant->religion,
            'ktp_id' => $applicant->ktp_id,
            'job_title' => $request->job_title,
            'job_level' => $request->job_level,
            'date' => now()->format('d F Y'),
            'net_salary' => number_format($net_salary) ?? '0',
            'salary_words' => $salary_words,
            'work_start_date' => $work_start_date,
            'availability_date' => $availability_date,
            'logo' => $company->cpnyid,
            'company_name' => $companyaddress->cpnyname ?? '-',
            'company_address' => $companyaddress->address ?? '-',

        ];

        $pdf = PDF::loadview('pages.careers.pdfofferingletter', $data);
        return $pdf->stream('offering-letter.pdf');

    }

    private function terbilang($angka): string
    {
        if (is_string($angka)) {
            $angka = str_replace([',', ' '], '', $angka);
        }
        if (!is_numeric($angka)) return '';

        $isMinus = $angka < 0;
        $angka = (int) abs((float) $angka);

        $bil = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        $fn = function ($n) use (&$fn, $bil): string {
            if ($n < 12)                  return ' '.$bil[$n];
            if ($n < 20)                  return $fn($n - 10).' belas';
            if ($n < 100)                 return $fn(intval($n / 10)).' puluh'.$fn($n % 10);
            if ($n < 200)                 return ' seratus'.$fn($n - 100);
            if ($n < 1000)                return $fn(intval($n / 100)).' ratus'.$fn($n % 100);
            if ($n < 2000)                return ' seribu'.$fn($n - 1000);
            if ($n < 1_000_000)           return $fn(intval($n / 1000)).' ribu'.$fn($n % 1000);
            if ($n < 1_000_000_000)       return $fn(intval($n / 1_000_000)).' juta'.$fn($n % 1_000_000);
            if ($n < 1_000_000_000_000)   return $fn(intval($n / 1_000_000_000)).' miliar'.$fn($n % 1_000_000_000);
            return $fn(intval($n / 1_000_000_000_000)).' triliun'.$fn($n % 1_000_000_000_000);
        };

        $hasil = trim(preg_replace('/\s+/', ' ', $fn($angka)));
        return ($isMinus ? 'minus ' : '').$hasil;
    }


    public function pdfPaktaintegritas(Request $request)
    {
        // dd($request->all());

        $applicant = Applicant::where('applicant_id', $request->applicant_id)->first();
        $company = MsCompany::where('cpny_id', $request->cpnyid)->first();
        $datebirth = Carbon::parse($applicant->date_of_birth)->translatedFormat('d F Y');

        $data = [
            'cpnyid' => $company->cpnyname,
            'departementid' => $request->departementid,
            'full_name' => $applicant->full_name,
            'job_title' => $request->job_title,
            'date' => now()->format('d F Y'),
        ];

        $pdf = PDF::loadview('pages.careers.pdfpaktaintegritas', $data);
        return $pdf->stream('pakta-integritas.pdf');

    }

    public function pdfPernyataanelectonik(Request $request)
    {
        // dd($request->all());

        $applicant = Applicant::where('applicant_id', $request->applicant_id)->first();
        $company = MsCompany::where('cpny_id', $request->cpnyid)->first();
        $datebirth = Carbon::parse($applicant->date_of_birth)->translatedFormat('d F Y');

        $data = [
            'cpnyid' => $company->cpnyname,
            'full_name' => $applicant->full_name,
            'ktp_id' => $applicant->ktp_id,
            'id_address' => $applicant->id_address,
            'date' => now()->format('d F Y'),
        ];

        $pdf = PDF::loadview('pages.careers.pdfpernyataanelectronik', $data);
        return $pdf->stream('pernyataan-electronik.pdf');

    }

    public function insert_psychotest($career, $user)
    {

        DB::beginTransaction();
        try {
            $doctype = 'JPS';
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

            $types = ['manager', 'staff'];
            $createdDocs = [];

            // Validasi awal
            $existing = TrAssessment::where('jobid', $career->jobid)
                ->where('applicant_id', $career->applicant_id)
                ->whereIn('type', $types)
                ->exists();

            if ($existing) {
                return response()->json([
                    'error' => true,
                    'message' => 'Assessment already exists.'
                ], 409);
            }

            $ms_checklist = MsAssessment::orderBy('step_order_group', 'ASC')
                ->orderBy('step_order', 'ASC')
                ->get();

            if ($ms_checklist->isEmpty()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Master assessment not found.'
                ], 404);
            }

            foreach ($types as $type) {
                // Ambil nomor baru untuk masing-masing type
                $autonbr = Autonbr::lockForUpdate()
                    ->where('doctype', $doctype)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('status', 'A')
                    ->first();

                if (!$autonbr) {
                    $autonbr = Autonbr::create([
                        'doctype' => $doctype,
                        'year' => $year,
                        'month' => $month,
                        'status' => 'A',
                        'number' => 1
                    ]);
                    $urutan = 1;
                } else {
                    $urutan = $autonbr->number + 1;
                    $autonbr->number = $urutan;
                    $autonbr->save();
                }

                $tglbln = substr($year, 2) . $month;
                $docid = $doctype . $tglbln . sprintf("%05d", $urutan);

                // Insert detail
                foreach ($ms_checklist as $cek) {
                    TrAssessmentdetail::create([
                        'docid' => $docid,
                        'jobapply_id' => $career->docid,
                        'jobid' => $career->jobid,
                        'applicant_id' => $career->applicant_id,
                        'assessment_id' => $cek->assessment_id,
                        'step_order_group' => $cek->step_order_group,
                        'step_order' => $cek->step_order,
                        'assessment_type' => $cek->assessment_type,
                        'assessment_score' => $cek->assessment_score,
                        'assessment_score_value' => 0,
                        'created_user' => $user->username,
                        'status' => 'P'
                    ]);
                }

                // Insert header
                TrAssessment::create([
                    'docid' => $docid,
                    'jobapply_id' => $career->docid,
                    'jobid' => $career->jobid,
                    'applicant_id' => $career->applicant_id,
                    'type' => $type,
                    'total_assessment_score_value' => 0,
                    'created_user' => $user->username,
                    'status' => 'P'
                ]);

                $createdDocs[] = $docid;
            }

            DB::commit();
            return response()->json(['success' => true, 'created_docids' => $createdDocs]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to insert assessment.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storePayroll(Request $request)
    {
        $user = Auth::user();

        $career = Career::where('docid', $request->jobapply_id)->first();

        // Validasi jika career tidak ditemukan
        if (!$career) {
            return response()->json([
                'error' => true,
                'message' => 'Data career tidak ditemukan.'
            ], 404);
        }

        // Cek apakah onboarding sudah ada
        $existing = Tronboarding::where('jobapply_id', $career->docid)
            ->where('applicant_id', $career->applicant_id)
            ->where('jobid', $career->jobid)
            ->exists();

        if ($existing) {
            return response()->json([
                'error' => true,
                'message' => 'Transaksi onboarding sudah ada.'
            ], 409);
        }

        // Lanjut insert onboarding
        try {
            $this->insert_onboarding($career, $user);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 409);
        }


        DB::beginTransaction();
        try {
            $doctype = 'OFF';
            $datenow = Carbon::now()->format('Y-m-d');
            $now = Carbon::now();
            $year = $now->year;
            $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);

            // Cek apakah payroll sudah ada
            $payrollExists = Payrollconfirm::where('jobapply_id', $career->docid)
                ->where('applicant_id', $career->applicant_id)
                ->where('jobid', $career->jobid)
                ->exists();

            if ($payrollExists) {
                return response()->json([
                    'error' => true,
                    'message' => 'Payroll untuk kandidat ini sudah ada.'
                ], 409);
            }

            // Ambil atau buat nomor urut dokumen
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'A',
                    'number' => 1
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->update(['number' => $urutan]);
            }

            $tglbln = substr($year, 2) . $month;
            $docid = $doctype . $tglbln . sprintf("%05d", $urutan);

            // Simpan payroll
            Payrollconfirm::create([
                'docid' => $docid,
                'jobapply_id' => $career->docid,
                'jobid' => $career->jobid,
                'applicant_id' => $career->applicant_id,
                'offer_date' => $datenow,
                'tax_liability' => $request->tax_liability,
                'npwp_id' => $request->npwp_id,
                'bank_account' => $request->bank_account,
                'bank_name' => $request->bank_name,
                'gross_salary' => $request->net_salary,
                'net_salary' => $request->net_salary,
                'other_facility' => $request->other_facility,
                'availability_date' => $request->availability_date,
                'work_start_date' => $request->work_start_date,
                'employment_status' => $request->employment_status,
                'contract_term' => $request->employment_status === 'PKWT' ? $request->contract_term : null,
                'status' => 'P',
                'created_user' => $user->username,
            ]);

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menyimpan Transaksi Payroll',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function editPayroll($id)
    {
        $data = Payrollconfirm::find($id);
        return response()->json($data);
    }

    public function updatePayroll(Request $request)
    {
        $user = Auth::user();
        $payroll = Payrollconfirm::where('applicant_id', $request->applicant_id)
            ->first();

        $payroll->tax_liability = $request->tax_liability;
        $payroll->npwp_id = $request->npwp_id;
        $payroll->bank_account = $request->bank_account;
        $payroll->bank_name = $request->bank_name;
        $payroll->net_salary = $request->net_salary;
        $payroll->other_facility = $request->other_facility;
        $payroll->availability_date = $request->availability_date;
        $payroll->work_start_date = $request->work_start_date;
        $payroll->employment_status = $request->employment_status;
        $payroll->contract_term = $request->employment_status === 'PKWT' ? $request->contract_term : null;;
        $payroll->updated_user = $user->username;
        $payroll->save();

        return response()->json(['success' => true]);
    }

    public function insert_onboarding($career, $user)
    {
        // dd($career);

        DB::beginTransaction();
        try {
            $doctype ='ONB';
            $datenow = Carbon::now()->format('Y-m-d');
            $datestamp = Carbon::now()->toDateTimeString();
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $user = Auth::user();


            $existing = Tronboarding::where('jobapply_id', $career->docid)
                ->where('applicant_id', $career->applicant_id)
                ->where('jobid', $career->jobid)
                ->exists();

            if ($existing) {
                return response()->json([
                'error' => true,
                'message' => 'You have already onboarding.'
                ], 409); // Conflict
            }

            // Generate task ID
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'A',
                    'number' => 1
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->number = $urutan;
                $autonbr->save();
            }

            $tglbln = substr($year, 2) . $month;
            $docid = $doctype . $tglbln . sprintf("%05d", $urutan);

            $ms_onboarding = Msonboarding::orderby('step_order','ASC')
                ->get();

            foreach ($ms_onboarding as $cek) {
                Tronboarding::create([
                    'docid' => $docid,
                    'jobapply_id' => $career->docid,
                    'jobid' => $career->jobid,
                    'applicant_id' => $career->applicant_id,
                    'checklist_id' => $cek->checklist_onboarding_id,
                    'checklist_type' => $cek->checklist_onboarding_type,
                    'step_order' => $cek->step_order,
                    'checklist_onboarding_mandatory' => $cek->checklist_onboarding_mandatory,
                    'checklist_onboarding_filename' => '',
                    'checklist_onboarding_attachfile' => '',
                    'checklist_onboarding_by' => '',
                    'created_user' => $user->username,
                    'status' => 'P'
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // lempar balik supaya controller bisa tangani
        }
    }

    public function getChecklist($docid_onboarding)
    {
        $checklists = Tronboarding::leftjoin('hr_ms_onboarding_checklist', 'hr_trx_onboarding_checklist.checklist_id', '=', 'hr_ms_onboarding_checklist.checklist_onboarding_id')
            ->select('hr_trx_onboarding_checklist.*', 'hr_ms_onboarding_checklist.checklist_onboarding_descr')
            ->where('hr_trx_onboarding_checklist.docid',$docid_onboarding)
            ->orderBy('hr_trx_onboarding_checklist.step_order', 'ASC')
            ->get();


        return response()->json($checklists);
    }


    public function updateChecklist(Request $request)
    {
        try {
            $ids = $request->input('checked', []);
            $docid = $request->docid_onboarding;
            $user = Auth::user();

            if (!$docid) {
                return response()->json(['error' => 'DocID kosong!'], 422);
            }

            // Reset semua checklist ke 0 dan kosongkan updated_user
            Tronboarding::where('docid', $docid)
                ->update([
                    'checklist_onboarding_receive' => 0,
                    'updated_user' => $user->username ?? 'system'
                ]);

            // Set checklist yang dipilih ke 1 dan update updated_user
            if (!empty($ids)) {
                Tronboarding::whereIn('id', $ids)
                    ->update([
                        'checklist_onboarding_receive' => 1,
                        'updated_user' => $user->username ?? 'system'
                    ]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Gagal update checklist: '.$e->getMessage());
            return response()->json(['error' => 'Terjadi error: '.$e->getMessage()], 500);
        }
    }

    public function pdfApplicantprofile(Request $request)
    {
        // dd($request->all());

        // Validasi input
        if (!$request->applicant_id || !$request->cpnyid) {
            return response()->json(['message' => 'Data tidak lengkap'], 422);
        }

        $applicant = Applicant::where('applicant_id', $request->applicant_id)->first();
        if (!$applicant) {
            return response()->json(['message' => 'Data pelamar tidak ditemukan'], 422);
        }
        $company = MsCompany::where('cpny_id', $request->cpnyid)->first();
        if (!$company) {
            return response()->json(['message' => 'Data perusahaan tidak ditemukan'], 422);
        }

        $datebirth = Carbon::parse($applicant->date_of_birth)->translatedFormat('d F Y');

        $year = now()->year;
        $config = config('filesystems.disks.gcs');
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $config['key_file'],
        ]);
        $bucket = $storage->bucket($config['bucket']);
        $expiration = \Carbon\Carbon::now()->addMinutes(30);
        $photo = null;
        if (!empty($applicant->upload_photo)) {
            $object = $bucket->object($applicant->upload_photo);
            $photo = $object->signedUrl($expiration);
        }

        $applicant_family = ApplicantFamily::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_marital = ApplicantMarital::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_education = ApplicantEducation::where('applicant_id', $applicant->applicant_id)->orderBy('id', 'asc')->get();
        $applicant_working = ApplicantWorking::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_language = ApplicantLanguage::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_course = ApplicantCourse::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_sw = ApplicantSW::where('applicant_id', $applicant->applicant_id)->orderBy('sw_type', 'asc')->get();
        $applicant_skill = ApplicantSkill::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_driver_license = ApplicantDriverLicense::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_reference = ApplicantReference::where('applicant_id', $applicant->applicant_id)->get();

        $data = [
            'cpnyid' => $company->cpnyname,
            'departementid' => $request->departementid,
            'full_name' => $applicant->full_name,
            'job_title' => $request->job_title,
            'date' => now()->format('d F Y'),
            'photo' =>  $photo,
            'applicant' => $applicant,
            'applicant_family' => $applicant_family,
            'applicant_marital' => $applicant_marital,
            'applicant_education' => $applicant_education,
            'applicant_working' => $applicant_working,
            'applicant_language' => $applicant_language,
            'applicant_course' => $applicant_course,
            'applicant_skill' => $applicant_skill,
            'applicant_sw' => $applicant_sw,
            'applicant_driver_license' => $applicant_driver_license,
            'applicant_reference' => $applicant_reference,
        ];

        $pdf = PDF::loadView('pages.careers.pdfapplicantprofile', $data)
          ->setPaper('A4', 'portrait')
          ->setOptions(['isRemoteEnabled' => true]);

        return $pdf->stream('applicant-profile.pdf');
    }


    public function insert_trx_approval($career, $user)
    {
        $datestamp = Carbon::now()->toDateTimeString();
        DB::beginTransaction();
        try {

            $jobposting = Jobposting::where('docid', $career->jobid)->first();

            // Ambil template approval dari master TrApproval
            $approvals = TrApproval::where('refnbr', $jobposting->refid)
                ->where('aprv_leveling','>', 1)
                ->where('status','A')
                ->orderBy('aprv_leveling', 'ASC')
                ->first();
            // dd($approvals);
            if (!$approvals) {
                throw new \Exception('No approval configuration found for this job posting.');
            }

            // Insert approval transaksi untuk docid career
            TrApproval::create([
                'refnbr'              => $career->docid,
                'aprv_leveling'       => 1,
                'aprv_doctype'        => 'JAP',
                'aprv_cpnyid'         => $approvals->aprv_cpnyid,
                'aprv_departementid'  => $approvals->aprv_departementid,
                'aprv_username'       => $approvals->aprv_username,
                'aprv_name'           => $approvals->aprv_name,
                'aprv_datebefore'     => $approvals->aprv_leveling == 2 ? $datestamp : null,
                'aprv_type'           => $approvals->aprv_type ?? null,
                'aprv_condition'      => $approvals->aprv_condition ?? null,
                'aprv_start_nominal'  => $approvals->aprv_start_nominal ?? null,
                'aprv_end_nominal'    => $approvals->aprv_end_nominal ?? null,
                'status'              => 'P',
                'created_by'          => $user->username,
            ]);

            // Kirim email ke approver pertama
            $firstApproval = TrApproval::where('refnbr', $career->docid)
                ->where('status', 'P')
                ->orderBy('aprv_leveling')
                ->first();

            $eid = Hashids::encode($career->id);

            if ($firstApproval) {
                $data = [
                    'docid'    => $firstApproval->refnbr,
                    'cpnyid'   => $firstApproval->aprv_cpnyid,
                    'deptname' => $firstApproval->aprv_departementid,
                    'date'     => $firstApproval->aprv_datebefore,
                    'name'     => $user->username,
                    'info'     => 'Apply Candidate',
                    'url'      => url('/showcareers/' . $eid)
                ];

                $approvers = explode(',', $firstApproval->aprv_username);
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('notification_email');

                foreach ($emails as $email) {
                    Mail::send('emails.mailapprove', $data, function ($message) use ($email, $data) {
                        $message->to($email)
                            ->subject($data['docid'] . ' - Waiting Approval Apply Candidate')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // lempar balik supaya controller bisa tangani
        }
    }

    public function update_trx_approval($career, $user)
    {
        $datestamp = Carbon::now()->toDateTimeString();
        DB::beginTransaction();
        try {

            // Ambil approval transaksi untuk career.docid level 1
            $approvals = TrApproval::where('refnbr', $career->docid)
                ->where('aprv_leveling', 1)
                ->first();

            if (!$approvals) {
                throw new \Exception("Approval transaksi level 1 tidak ditemukan.");
            }

            // Update approval level 1 menjadi Approved
            $approvals->status          = 'A';
            $approvals->aprv_dateafter  = $datestamp;
            $approvals->aprv_username   = $user->username;
            $approvals->aprv_name       = $user->name;
            $approvals->updated_by      = $user->username ?? null;
            $approvals->save();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // lempar ke controller
        }
    }


    public function storeSign(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();


        DB::beginTransaction();
        try {

            $datenow = Carbon::now()->format('Y-m-d');
            $now = Carbon::now();
            $year = $now->year;
            $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $request->validate([
                    'aprvid'        => 'required|array',
                    'aprvid.*'      => 'required|integer',
                    'aprvusername'  => 'required|array',
                    'aprvusername.*'=> 'required|string',
                    'aprvname'      => 'required|array',
                    'aprvname.*'    => 'required|string',
                ]);

            foreach ($request->aprvid as $i => $ord) {
                SignPayroll::create([
                    'docid'         => $request->jobapply_id,
                    'aprvid'       => $ord,
                    'aprvusername' => $request->aprvusername[$i],  // username
                    'name'         => $request->aprvname[$i],      // name
                    'jabatan'      => $request->jabatan[$i] ?? null,
                     'status' => 'P',
                    'created_user' => $user->username
                ]);
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menyimpan Transaksi Sign Payroll',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function editSign($id)
    {
        $data = SignPayroll::find($id);
        return response()->json($data);
    }

    public function updateSign(Request $request)
    {
        // Validasi minimal
        $request->validate([
            'id'             => ['required','integer','exists:ms_approval_payroll,id'], // sesuaikan nama tabel
            'aprvid'         => ['required'],
            'aprvusername'   => ['required'],
            // 'aprvname'       => ['required'], // <- hidden yg kamu kirim dari select
            'jabatan'        => ['required'],
        ]);

        $row = SignPayroll::findOrFail($request->id);

        // Helper untuk ambil single value dari array/single
        $pick = function ($key) use ($request) {
            $v = $request->input($key);
            return is_array($v) ? ($v[0] ?? null) : $v;
        };

        $row->aprvid        = (int) $pick('aprvid');         // ["3"] -> 3
        $row->aprvusername  = (string) $pick('aprvusername'); // ["benny"] -> "benny"
        $row->name          = (string) $pick('aprvname');     // gunakan aprvname (bukan name)
        $row->jabatan       = (string) $pick('jabatan');
        $row->updated_user  = Auth::user()->username ?? 'system';
        $row->save();

        return response()->json(['success' => true]);
    }

    public function destroySign($id)
    {
        $sign = SignPayroll::find($id);
        if (!$sign) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $sign->delete();
        return response()->json(['success' => true]);
    }


    public function updateSchedule(Request $request)
    {
        $data = $request->validate([
            'applicant_id'      => ['required','string'],
            'jobapply_id'       => ['nullable','string'],
            'availability_date' => ['required','date'],
            'work_start_date'   => ['required','date','after_or_equal:availability_date'],
        ]);

        DB::beginTransaction();
        try {
            // $user = $request->user();
            $user = Auth::user();
            $username = $user ? $user->username : 'system';

            // Ambil payroll berdasar applicant_id, dan tambahkan filter jobapply_id kalau ada
            $payrollQuery = PayrollConfirm::where('applicant_id', $data['applicant_id']);
            if (!empty($data['jobapply_id'])) {
                $payrollQuery->where('jobapply_id', $data['jobapply_id']);
            }
            $payroll = $payrollQuery->firstOrFail();

            $payroll->availability_date = $data['availability_date'];
            $payroll->work_start_date   = $data['work_start_date'];
            $payroll->updated_user      = $user->username ?? $user->name ?? 'system';
            $payroll->save();

            // Pastikan applicant ada
            $applicant = Applicant::where('applicant_id', $data['applicant_id'])->firstOrFail();

            // Tentukan penerima email:
            // kalau mapping User->notification_email ada, pakai itu; kalau tidak, fallback ke email applicant
            $mapped = User::where('username', $applicant->email_address)
                ->where('status', 'A')
                ->pluck('notification_email')
                ->filter()
                ->all();

            $recipients = !empty($mapped) ? $mapped : [$applicant->email_address];

            // Data untuk email (format tanggal yang rapi)
            $emailData = [
                'full_name'         => $applicant->full_name,
                'availability_date' => Carbon::parse($data['availability_date'])->format('F j, Y'),
                'work_start_date'   => Carbon::parse($data['work_start_date'])->format('F j, Y'),
            ];

            Mail::send('emails.mailjoinapplicant', $emailData, function ($message) use ($recipients) {
                $message->to($recipients)
                    ->subject('Your Employment Start Schedule')
                    ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Schedule saved & email sent.'
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Payroll or Applicant not found.'
            ], 404);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save: '.$e->getMessage()
            ], 500);
        }
    }

    public function sendemail_rejected_applicant($career, $user)
    {
        // Ambil data applicant
        $applicant = Applicant::where('applicant_id', $career->applicant_id)->first();
        if (!$applicant || empty($applicant->email_address)) {
            // Jangan gagal total — log saja dan keluar
            \Log::warning('Applicant email not found for rejection notice', [
                'career_docid' => $career->docid ?? null,
                'applicant_id' => $career->applicant_id ?? null,
            ]);
            return;
        }

        // Ambil info job (untuk subjek/konten)
        $jobposting = Jobposting::where('docid', $career->jobid)->first();
        $jobTitle = $jobposting->job_title ?? 'Your Application';

        $careerPortalUrl = url("https://careerjakarta.pakuwon.com");


        $data = [
            'name'        => $applicant->full_name ?? 'Candidate',
            'job_title'   => $jobTitle,
            'career_url'  => $careerPortalUrl,
            'company'     => 'Pakuwon Group Jakarta',
        ];

        // Kirim email pakai blade "emails.mailapplicant_rejected"
        Mail::send('emails.mailapplicant_rejected', $data, function ($message) use ($applicant, $jobTitle) {
            $message->to($applicant->email_address)
                    ->subject("📩 Application Update – {$jobTitle}")
                    ->from('recruitment@pakuwon.com', 'Pakuwon Career');
        });

        // (Opsional) return info sukses (tidak perlu response JSON di sini)
        \Log::info('Rejection email sent to applicant', [
            'email' => $applicant->email_address,
            'career_docid' => $career->docid ?? null,
        ]);
    }

    public function revealSalary(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'payroll_id' => 'required|integer',
            'password'   => 'required|string',
        ]);

        // Cek group akses (harus terdaftar di GroupAccspecific & aktif)
        $inGroup = GroupAccspecific::where('username', $user->username)
            ->where('status', 'A')
            ->exists();

        if (!$inGroup) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk melihat gaji.'], 403);
        }

        // Cek password user
        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json(['message' => 'Password salah.'], 401);
        }

        $payroll = Payrollconfirm::findOrFail($request->input('payroll_id'));

        return response()->json([
            'success' => true,
            'salary'  => (int) $payroll->net_salary,
        ]);
    }

    public function getPayroll($id, Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $p = Payrollconfirm::findOrFail($id);
        $data = $p->toArray();

        // default: sembunyikan salary
        unset($data['net_salary']);

        // opsional: kalau mau dukung reveal via parameter (edit flow),
        // bisa minta password lagi di sini
        if ($request->filled('password')) {
            $inGroup = GroupAccspecific::where('username', $user->username)
                ->where('status', 'A')
                ->exists();

            if ($inGroup && Hash::check($request->input('password'), $user->password)) {
                $data['net_salary'] = (int) $p->net_salary;
            }
        }

        return response()->json($data);
    }

    public function viewDocument($id)
    {
        $row = Trchecklist::findOrFail($id); // koneksi mysql3 dari model
        abort_if(!$row->checklist_attachfile, 404);

        $config  = config('filesystems.disks.gcs');
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $config['key_file'],
        ]);
        $bucket = $storage->bucket($config['bucket']);
        $object = $bucket->object($row->checklist_attachfile);
        abort_unless($object->exists(), 404, 'File not found on GCS');

        $url = $object->signedUrl(new \DateTime('+5 minutes'), ['version' => 'v4']);
        return redirect()->away($url);
    }



}
