<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ProjectTask;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\T_Message;
use App\Models\Attachment;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\JobLevel;
use App\Models\JobResponsiblities;
use App\Models\JobQualification;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;

class ProjectTaskController extends Controller
{
    public function index()
    {
        return view('pages.tasks.tasks');
    }
   
    public function json()
    {       
        $task = ProjectTask::orderBy('id', 'desc')->get();
        return response()->json(['data' => $task]);
    }

    public function createTask()
    {
        $user = request()->user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();
        $companies = Company::select('cpnyid')->get();
        $departements = MsDepartment::select('department_id')->get();
        $userlist = User::where('status','A')
            ->get();
       
        return view('pages.tasks.createtasks', compact('companies','departements','userlist','usercpny','usercpny2','userdept','userdept2'));
    }


    public function storeTask(Request $request)
    {
        dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'tasktype' => 'required|string',
            'summary' => 'required|string',
            'description' => 'required|string',
            'startdate' => 'nullable|date',
            'duedate' => 'nullable|date|after_or_equal:startdate',  
            'attachments.*' => 'file|max:2048' // Validasi file, max 2MB
        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'TSK';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

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
            $docid = $doctype . $tglbln . sprintf("%03d", $urutan);

            $userlist = User::where('status','A')
                ->get(); 

            $participantlist = $request->input('participant');
            if($participantlist <> null){
                $userlist->appreance = implode(',', $participantlist);
            }else{
                $userlist->appreance = '';
            }

                       
            $task = ProjectTask::create([
                'taskid' => $docid,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'taskdate' => $datenow,
                'tasktype' => $request->tasktype,
                'taskpriority' => $request->taskpriority,                
                'summary' => $request->summary,                
                'description' => $request->description,
                'status' => $request->status ?? 'P',
                'startdate' => $request->startdate,
                'duedate' => $request->duedate,     
                'created_user' => $user->username,
                'participant' => $userlist->appreance            
            ]);

            //read ms_approval
            $m_approval = M_approval::where('aprvdoctype', $doctype)
                ->where('aprvcpnyid', $request->cpnyid)
                ->where('aprvdeptid', $request->departementid)
                ->where('status', 'A')
                ->get();

            //insert trx_approval
            foreach ($m_approval as $mp) {
                $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
                T_approval::create([
                    'docid' => $docid,
                    'aprvid' => $mp->aprvid,
                    'aprvdoctype' => $mp->aprvdoctype,
                    'aprvcpnyid' => $mp->aprvcpnyid,
                    'aprvdeptid' => $mp->aprvdeptid,
                    'aprvusername' => $mp->aprvusername,
                    'name' => $mp->name,
                    'aprvdatebefore' => $aprvdatebefore,
                    'aprvtotalday' => 1,
                    'status' => 'P',
                    'created_user' => $user->username
                ]);
            }           
           

            // Simpan Attachments ke attachments          
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                   
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $attachfile = md5($randomNumber) . '-' . $originalName;

                    //attach to folder
                    $folder_attach = public_path() . '/attachments/'.$year;
                    $config['upload_path'] = $folder_attach;                   
                    if(!is_dir($folder_attach))
                    {
                        mkdir($folder_attach, 0777);
                    }
                    
                    $folder_upload = $folder_attach;
                    // $folder_upload = public_path() . '/attachments';
                    $file->move($folder_upload, $attachfile);

                    //insert to table attachments
                    $attach = new Attachment();
                    $attach->docid = $docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'task' => $task]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan task', 'message' => $e->getMessage()], 500);
        }
    }

    public function editTask($id)
    {
        $user = request()->user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();
        $task = ProjectTask::findOrFail($id);
        $companies = Company::select('cpnyid')->get();
        $departements = Dept::select('deptname')->get();
        $joblevel = JobLevel::select('title_level')->get();
        $jobres = JobResponsiblities::where('docid', $task->docid)           
            ->get();
        $jobqua = JobQualification::where('docid', $task->docid)           
            ->get();
        $attachment = Attachment::where('docid', $task->docid)  
            ->where('status','A')         
            ->get();

        return view('pages.tasks.edittasks', compact('task', 'companies', 'departements', 'joblevel','jobres','jobqua','attachment','usercpny','usercpny2','userdept','userdept2'));
    }
    
    public function updateTask(Request $request, $id)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'job_title' => 'required|string',
            'job_level' => 'required|string',
            'immediate_superior' => 'required|string',
            'state_position' => 'required|string',
            'job_type' => 'required|string|in:Replacement,Temporary',
            'reason_vacancy' => 'required|string',
            'required' => 'required|integer|min:1',
            'actual' => 'required|integer|min:0',
            'total_actual' => 'required|integer|min:0'
            
        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'PRF';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            $task = ProjectTask::findOrFail($id);
                       
            $task -> update([              
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'date' => $datenow,
                'job_title' => $request->job_title,
                'job_level' => $request->job_level,                
                'immediate_superior' => $request->immediate_superior,                
                'state_position' => $request->state_position,
                'job_type' => $request->job_type,
                'reason_vacancy' => $request->reason_vacancy,
                'required' => $request->required,
                'actual' => $request->actual,
                'total_actual' => $request->total_actual,                
                'created_user' => $user->username,
                'status' => $request->status ?? 'P'                
            ]);

            //read ms_approval
            $m_approval = M_approval::where('aprvdoctype', $doctype)
                ->where('aprvcpnyid', $request->cpnyid)
                ->where('aprvdeptid', $request->departementid)
                ->where('status', 'A')
                ->get();

            //insert trx_approval
            foreach ($m_approval as $mp) {
                $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
                T_approval::create([
                    'docid' => $task->docid,
                    'aprvid' => $mp->aprvid,
                    'aprvdoctype' => $mp->aprvdoctype,
                    'aprvcpnyid' => $mp->aprvcpnyid,
                    'aprvdeptid' => $mp->aprvdeptid,
                    'aprvusername' => $mp->aprvusername,
                    'name' => $mp->name,
                    'aprvdatebefore' => $aprvdatebefore,
                    'aprvtotalday' => 1,
                    'status' => 'P',
                    'created_user' => $user->username
                ]);
            }            

            if ($request->has('responsibilities')) {
                JobResponsiblities::where('docid', $task->docid)->delete();
                foreach ($request->responsibilities as $index => $responsibility) {                    
                    JobResponsiblities::create([
                        'docid' => $task->docid,
                        'no_job_responsiblities' => $index + 1, // Urutan dimulai dari 1
                        'job_responsibilities_descr' => $responsibility,
                        'created_user' => $user->username,
                        'status' => 'P'                                               
                    ]);
                }
            }
            
            // Simpan Qualification
            if ($request->has('qualification')) {
                JobQualification::where('docid', $task->docid)->delete();
                foreach ($request->qualification as $index => $qualification) {
                    JobQualification::create([
                        'docid' => $task->docid,
                        'no_job_qualification' => $index + 1,
                        'job_qualification_descr' => $qualification,
                        'created_user' => $user->username,
                        'status' => 'P'   
                    ]);
                }
            }

            // Simpan Attachments ke attachments          
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                   
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $attachfile = md5($randomNumber) . '-' . $originalName;

                    //attach to folder
                    $folder_attach = public_path() . '/attachments/'.$year;
                    $config['upload_path'] = $folder_attach;                   
                    if(!is_dir($folder_attach))
                    {
                        mkdir($folder_attach, 0777);
                    }
                    
                    $folder_upload = $folder_attach;
                    // $folder_upload = public_path() . '/attachments';
                    $file->move($folder_upload, $attachfile);

                    //insert to table attachments
                    $attach = new Attachment();
                    $attach->docid = $task->docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'task' => $task]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan task', 'message' => $e->getMessage()], 500);
        }
    }

    public function removeAttachment($id)
    {
        try {
            $attachment = Attachment::findOrFail($id);
            $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }
    }
 

    public function showTask($id)
    {        
        $task = ProjectTask::findOrFail($id);
        $approval = T_approval::where('docid', $task->docid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();

        $jobres = JobResponsiblities::where('docid', $task->docid)           
            ->get();
        $jobqua = JobQualification::where('docid', $task->docid)           
            ->get();
        $attachment = Attachment::where('docid', $task->docid)           
            ->get();
       
        return view('pages.tasks.showtasks', compact('task','jobres','jobqua','approval','attachment'));
    }

    
    public function fetchComments($id)
    {
        
        $comments = T_Message::where('docid', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'comments' => $comments
        ]);
    }
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);
        // dd($id);
        $user = request()->user();
        $comment = new T_Message();
        $comment->docid = $id;
        $comment->doctype = 'PRF';
        $comment->username = $user->username; 
        $comment->name = $user->name; 
        $comment->message = $request->comment;
        $comment->status = 'A';
        $comment->created_at = now();
        $comment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment added successfully!',
            'comment' => $comment
        ]);
    }

    public function approveTask(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login
        
        $task = ProjectTask::where('docid', $docid)->first();   

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Prf not found'], 404);
        }        

        $count_approval = T_approval::where('docid', '=', $task->docid)
            ->where('status', '=', 'P')
            ->count();
    
        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $task->docid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->first();
        // dd($t_approval);
        if ($t_approval == null) {
            return response()->json(['success' => false, 'message' => "You Can't Approve!"], 403);
        } else {
            $t_approval->status = 'A';
            $t_approval->aprvdateafter = $datestamp;
            $t_approval->aprvusername = $user->username;
            $t_approval->name = $user->name;
            $t_approval->save();
        }   

        if ($count_approval == 1) {
            $task->status = 'C';
            $task->completed_user = $user->username;
            $task->completed_at = $datestamp;
            $task->save();
        }

        $t_approval_next = T_approval::where('docid', $task->docid)
            ->where('status', 'P')
            ->orderby('aprvid','ASC')
            ->first();

        if ($count_approval <> 1) {
            //update datebefore
            $t_approval_next->aprvdatebefore = $datestamp;
            $t_approval_next->save();

            //send email 
            // $data = array(
            //     'docid' => $t_approval_next->docid,
            //     'cpnyid' => $t_approval_next->aprvcpnyid,
            //     'deptname' => $t_approval_next->aprvdeptid,
            //     'locationname' => $ms_site->site,
            //     'date' => $t_approval_next->aprvdatebefore,
            //     'name' => $t_approval_next->created_user,
            //     'info' => $vplrequest->request_remark,               
            //     'url' => url('/showvplrequest_') . $id

            // );

            // $multiapp = explode(',', $t_approval_next->aprvusername);

            // $email_it = User::whereIN('username', $multiapp)
            //     ->where('status', 'A')
            //     ->get();

            // foreach ($email_it as $emailsit) {
            //     Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

            //         $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Waiting Approval Usage');
            //         $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            //     });
            // }
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectTask(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $task = ProjectTask::where('docid', $docid)->first();  
        
        
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $task->docid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->first();
        // dd($t_approval);
        if ($t_approval == null) {
            return response()->json(['success' => false, 'message' => "You Can't Rejected!"], 403);
        } else {
            $t_approval->status = 'R';
            $t_approval->aprvdateafter = $datestamp;           
            $t_approval->save();

            $task->status = 'R';
            $task->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $task->docid)
            ->where('status', '=', 'P')
            ->get();

        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        // $data = array(
        //     'docid' => $t_approval->docid,
        //     'cpnyid' => $t_approval->aprvcpnyid,
        //     'deptname' => $t_approval->aprvdeptid,           
        //     'date' => $t_approval->aprvdatebefore,
        //     'name' => $t_approval->created_user,
        //     'info' => $task->summary,            
        //     // 'url' => url('/showvplrequest_') . $id

        // );

        // $email_it = User::where('username', $vplrequest->user)
        //         ->where('status', 'A')
        //         ->get();

        // foreach ($email_it as $emailsit) {
        //     Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
        //         $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Rejected Usage');
        //         $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
        //     });
        // }
        $id = $task->id;
        $doctype ='PRF';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Task rejected successfully']);
    }

    public function reviseTask(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $task = ProjectTask::where('docid', $docid)->first();  
        
        
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $task->docid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->first();
        // dd($t_approval);
        if ($t_approval == null) {
            return response()->json(['success' => false, 'message' => "You Can't Revise!"], 403);
        } else {
            $t_approval->status = 'D';
            $t_approval->aprvdateafter = $datestamp;           
            $t_approval->save();

            $task->status = 'D';
            $task->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $task->docid)
            ->where('status', '=', 'P')
            ->get();

        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        // $data = array(
        //     'docid' => $t_approval->docid,
        //     'cpnyid' => $t_approval->aprvcpnyid,
        //     'deptname' => $t_approval->aprvdeptid,           
        //     'date' => $t_approval->aprvdatebefore,
        //     'name' => $t_approval->created_user,
        //     'info' => $task->summary,            
        //     // 'url' => url('/showvplrequest_') . $id

        // );

        // $email_it = User::where('username', $vplrequest->user)
        //         ->where('status', 'A')
        //         ->get();

        // foreach ($email_it as $emailsit) {
        //     Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
        //         $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Rejected Usage');
        //         $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
        //     });
        // }
        $id = $task->id;
        $doctype ='PRF';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Task revise successfully']);
    }




}
