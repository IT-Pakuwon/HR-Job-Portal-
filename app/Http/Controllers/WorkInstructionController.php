<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use App\Models\ProjectTask;
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
use App\Models\Mscomplaint;
use App\Models\Mslocation;
use App\Models\Mssublocation;
use App\Models\Msworktype;
use App\Models\Mssubworktype;
use App\Models\Trwoworker;
use App\Models\Trworkorder;
use Mail;
use Yajra\DataTables\Facades\DataTables;



class WorkInstructionController extends Controller
{
    
    
    public function assignWo(Request $request)
    {        
        $tittle = 'Assign WO';
        $user = Auth::user();       

        if ($request->ajax()) {  
           
            // $data = ProjectTask::where('status', 'C')->get();            
            $data = ProjectTask::leftjoin('task_trx_workorder', 'task_trx_task.docid', '=', 'task_trx_workorder.task_id')                                      
                ->select('task_trx_task.*', 'task_trx_workorder.task_id')
                ->where('task_trx_task.status',  'C') 
                ->where('task_trx_workorder.task_id',  null)                
                ->get(); 
                // dd($data);
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('docid', function ($row) {
                    $url = url('showtasks/' . $row->id);
                    return '<a href="' . $url . '" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700">' . $row->docid . '</a>';
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 create-wi-btn" 
                                data-id="' . $row->id . '" 
                                data-docid="' . $row->docid . '" 
                                data-taskdate="' . $row->taskdate . '" 
                                data-cpnyid="' . $row->cpnyid . '" 
                                data-departementid="' . $row->departementid . '" 
                                data-assign="' . $row->assign . '" 
                                data-tasktype="' . $row->tasktype . '" 
                                data-taskpriority="' . $row->taskpriority . '" 
                                data-startdate="' . $row->startdate . '" 
                                data-enddate="' . $row->enddate . '" 
                                data-summary="' . $row->summary . '" 
                                data-description="' . $row->description . '">
                                Create Work
                            </button>';
                })

                ->rawColumns(['docid', 'action']) // <- pastikan 'docid' ditambahkan di sini
                ->make(true);
        }
        
        return view('pages.workorder.showworkorder', compact('tittle'));
        
    }

    public function workInstruction(Request $request)
    {        
        $tittle = 'Work';
        $user = Auth::user();       
        
        if ($request->ajax()) {                          
             
            $data = Trworkorder::all();             
            
            return Datatables::of($data)
                ->addIndexColumn()    
                ->addColumn('action', function($row){   
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-sm editGroupbiaya" style="background-color:#FFCD05; color:white">Edit</a>';                    
                    return $btn;
                })
                
                ->rawColumns(['status','action'])                                           
                ->make(true);
        }
        
        return view('pages.workorder.showworkorder', compact('tittle'));
        
    }

    public function getComplaintTypes(Request $request)
    {
        
        $complaints = Mscomplaint::where('cpnyid', $request->cpnyid)
            ->where('departementid', $request->departementid)
            ->get(['complaintid', 'complaint_descr']);

        return response()->json($complaints);
    }

    public function getWoTypes(Request $request)
    {
        
        $complaints = Msworktype::where('cpnyid', $request->cpnyid)
            ->where('departementid', $request->departementid)
            ->get(['worktype_id', 'worktype_descr']);

        return response()->json($complaints);
    }

    public function getSubWorkTypes(Request $request)
    {
       
        $subworks = Mssubworktype::where('worktype_id', $request->worktype_id)
            ->get(['subworktype_id', 'subworktype_descr']);

        return response()->json($subworks);
    }
    
    public function getLocations(Request $request)
    {
        
        $locations = Mslocation::where('cpnyid', $request->cpnyid)
            ->get(['location_id', 'location_descr']);

        return response()->json($locations);
    }

    public function getSubLocations(Request $request)
    {
       
        $subLocations = Mssublocation::where('location_id', $request->location_id)
            ->get(['sublocation_id', 'sublocation_descr']);

        return response()->json($subLocations);
    }

    public function getWorkers(Request $request)
    {
        
        $workers = User::where('departmentid', $request->departementid)
            ->get(['username', 'name']);

        return response()->json($workers);
    }
      

    public function storeWi(Request $request)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'wo_priority' => 'required|string',
            'complaint_type' => 'required|string',
            'work_type' => 'required|string',
            'sub_work_type' => 'required|string',
            'location_id' => 'required|string',
            'sub_location_id' => 'required|string',
            'work_start_date' => 'nullable|date',
            'workers' => 'required|array',
            'workers.*' => 'string'
        ]);

       
        $doctype = 'WI';      
        $task = ProjectTask::findOrFail($request->work_id);
        // dd($task);
        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $task->cpnyid)
            ->where('aprvdeptid', $task->assign)
            ->where('aprvdoctype', $doctype)
            ->count();
        
        if ($count_approval === 0) {
            return response()->json([
                'message' => 'Approval line belum di-setup, Please contact IT!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);            
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
        
            $wi = Trworkorder::create([
                'docid' => $docid,
                'task_id' => $task->docid,
                'cpnyid' => $task->cpnyid,
                'departementid' => $task->departementid,
                'wo_date' => $datenow,
                'wo_priority' => $request->wo_priority,
                'complaint_type' => $request->complaint_type,                                
                'work_type' => $request->work_type,                
                'sub_work_type' => $request->sub_work_type,
                'location_id' => $request->location_id,
                'sub_location_id' => $request->sub_location_id,
                'work_start_date' => $request->work_start_date,
                'work_end_date' => $request->work_end_date,
                'work_description' => $request->work_description,
                'work_response' => '',
                'status' => 'P',                   
                'created_user' => $user->username,                          
            ]);

            $worker = $request->input('workers');
            foreach ($worker as $wk) {               
                Trwoworker::create([
                    'docid' => $docid,
                    'worker' => $wk,
                    'worker_start_date' => $request->work_start_date,
                    'worker_end_date' => $request->work_end_date,                    
                    'worker_response' => '',
                    'worker_comment' => '',                   
                    'status' => 'P',
                    'created_user' => $user->username
                ]);
            }
                            
            $wi_new = Trworkorder::where('docid', $docid)               
                ->first();

            $worker = Trwoworker::where('docid', $docid)               
                ->get();

            $id = $wi->id;
            $data = array(
                'docid' => $wi_new->docid,
                'cpnyid' => $wi_new->cpnyid,
                'deptname' => $wi_new->departementid,                
                'date' => $wi_new->wo_date,
                'name' => $wi_new->created_user,
                'info' => $wi_new->work_description,   
                'url' => url('/showwi/') . $id
    
            );
    
            $multiapp = explode(',', $worker->worker);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Waiting Approval Tasks');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
            }       

            DB::commit();
            return response()->json(['success' => true, 'wi' => $wi]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan wi', 'message' => $e->getMessage()], 500);
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
        $attachment = Attachment::where('docid', $task->docid)  
            ->where('status','A')         
            ->get();

        $participantlist_user = explode(',', $task->participant);
        $userlist = User::where('status','A')
            ->get();
        

        return view('pages.tasks.edittasks', compact('task', 'attachment','usercpny','usercpny2','userdept','userdept2','userlist','participantlist_user'));
    }
    
    public function updateTask(Request $request, $id)
    {
        // dd($request->all()); 
        
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
            // $doctype = 'TSK';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            $task = ProjectTask::findOrFail($id);

            $userlist = User::where('status','A')
                ->get(); 

            $participantlist = $request->input('participant');
            if($participantlist <> null){
                $userlist->appreance = implode(',', $participantlist);
            }else{
                $userlist->appreance = '';
            }
                       
            $task -> update([              
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
                'assign' => $request->assign, 
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

            $t_approval_next = T_approval::where('docid', $task->docid)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();
            
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,
                'info' => $request->summary,      
                'url' => url('/showtasks/') . $task->id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Waiting Approval Tasks');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
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

        $attachment = Attachment::where('docid', $task->docid)        
            ->where('status','A')   
            ->get();
       
        return view('pages.tasks.showtasks', compact('task','approval','attachment'));
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
        $comment->doctype = 'TSK';
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
        $doctype ='TSK';
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
        $doctype ='TSK';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Task revise successfully']);
    }

    public function checkApprovalx($id)
    {
        // Ambil user yang sedang login
        $user = Auth::user();
        
        // Cek apakah user login ada di table trx_approval dengan status 'P'
        $approval = T_approval::where('docid', $id)
            ->where('aprvusername', 'like', '%' . $user->username . '%')
            ->where('status', 'P')
            ->whereNotNull('aprvdatebefore')
            ->exists();

        return response()->json(['canReject' => $approval]);


    }

    public function checkApproval($id, $action)
    {
        $user = Auth::user(); // Ambil user yang login
        // dd($action);
        // Query dasar untuk pengecekan
        $query = T_approval::where('docid', $id)
                    ->where('aprvusername', 'like', '%' . $user->username . '%')
                    ->where('status', 'P');                 

        // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
        if (in_array($action, ['reject', 'revise','approve'])) {
            $query->whereNotNull('aprvdatebefore');
        }

        // Cek apakah user bisa melakukan aksi
        $canPerformAction = $query->exists();

        return response()->json(['canPerformAction' => $canPerformAction]);
    }





}
