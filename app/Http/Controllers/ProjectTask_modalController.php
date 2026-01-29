<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use App\Models\UserDas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\T_Message;
use App\Models\Attachment;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProjectTaskController extends Controller
{
    /**
     * Menampilkan halaman utama dengan DataTables
     */
    public function index()
    {
        $participant = UserDas::where('status', 'A')          
            ->get();
        // dd($participant);
        // $email = request()->user()->email;
        // dd($email);
        return view('pages.tasks.index', compact('participant'));
        
        
    }

    /**
     * Mengambil data task untuk DataTables (JSON Response)
     */
    public function json()
    {
        $tasks = ProjectTask::select(['id', 'taskid', 'summary', 'startdate', 'participant', 'taskpriority', 'duedate', 'status'])
            ->latest()
            ->get();

        return response()->json(['data' => $tasks]);
    }

    /**
     * Mengambil daftar peserta untuk Select2
     */
    // public function getParticipants()
    // {
    //     return response()->json(UserDas::select('username', 'name')->get());
    // }
    public function getParticipants(Request $request)
    {
        $query = UserDas::query();

        // Tambahkan pencarian jika ada parameter `q`
        if ($request->has('q')) {
            $search = $request->q;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Pastikan `id` diambil dari `username`, bukan dari database default
        $participants = $query->selectRaw("username AS idx, name AS text")->limit(5)->get();

        return response()->json($participants);
    }
 
    

    /**
     * Menyimpan task baru
     */
    public function store(Request $request)
    {
        // dd($request->all()); // Debugging untuk cek data yang diterima
        
        // Validasi input
        $request->validate([
            'summary' => 'required|string|max:255',
            'taskpriority' => 'required|string',
            'startdate' => 'nullable|date',
            'duedate' => 'nullable|date|after_or_equal:startdate',            
            'description' => 'nullable|string',           
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

            $userlist = UserDas::where('status','A')
                ->get(); 

            $participantlist = $request->input('participant');
            if($participantlist <> null){
                $userlist->appreance = implode(',', $participantlist);
            }else{
                $userlist->appreance = '';
            }

            // Simpan task dengan participant sebagai JSON
            $task = ProjectTask::create([
                'taskid' => $docid,
                'taskdate' => $datenow,
                'tasktype' => $request->tasktype,
                'summary' => $request->summary,
                'taskpriority' => $request->taskpriority,
                'status' => $request->status ?? 'P',
                'startdate' => $request->startdate,
                'duedate' => $request->duedate,
                'description' => $request->description,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
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

    /**
     * Mengambil data task berdasarkan ID (untuk edit)
     */
    // public function edit($id)
    // {
    //     $task = ProjectTask::findOrFail($id);
    //     return response()->json($task);
    // }

    public function edit($id)
    {
        $task = ProjectTask::findOrFail($id);

        // Ambil semua attachment dengan status 'A'
        $attachments = Attachment::where('docid', $task->taskid)
            ->where('status', 'A')
            ->get()
            ->map(function ($file) {
                // Ambil tahun dari created_at
                $year = $file->created_at ? $file->created_at->format('Y') : date('Y');

                return [
                    'id' => $file->id,
                    'file_name' => $file->name,  // Sesuai nama kolom di database
                    'file_url' => url("/attachments/{$year}/{$file->attachfile}") // Format yang diminta
                ];
            });

        return response()->json([
            'task' => $task,
            'attachments' => $attachments
        ]);
    }   

    /**
     * Mengupdate task berdasarkan ID
     */
    public function update(Request $request, $taskid)
    {
        // dd($request->all());
        // Validasi input
        $request->validate([
            'summary' => 'required|string|max:255',
            'taskpriority' => 'required|string',
            'startdate' => 'nullable|date',
            'duedate' => 'nullable|date|after_or_equal:startdate',           
            'description' => 'nullable|string',
            // 'participant' => 'nullable|string' 
        ]);
       
        DB::beginTransaction();
        try {
            $task = ProjectTask::findOrFail($taskid);
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();
            // dd($task);
            $userlist = UserDas::where('status','A')
                ->get(); 

            $participantlist = $request->input('participant');
            if($participantlist <> null){
                $userlist->appreance = implode(',', $participantlist);
            }else{
                $userlist->appreance = '';
            }

            $task->update([
                'summary' => $request->summary,
                'taskpriority' => $request->taskpriority,
                'tasktype' => $request->tasktype,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,               
                'startdate' => $request->startdate,
                'status' => 'P',
                'duedate' => $request->duedate,
                'description' => $request->description,
                'participant' => $userlist->appreance
            ]);

            //read ms_approval
            $m_approval = M_approval::where('aprvdoctype', 'TSK')
                ->where('aprvcpnyid', $request->cpnyid)
                ->where('aprvdeptid', $request->departementid)
                ->where('status', 'A')
                ->get();
            // dd($m_approval);
            //insert trx_approval
            foreach ($m_approval as $mp) {
                $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
                T_approval::create([
                    'docid' => $task->taskid,
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
            return response()->json(['error' => 'Gagal memperbarui task', 'message' => $e->getMessage()], 500);
        }
    }

    public function getCompany()
    {
        $companies = Company::select('cpnyid')->get();
        return response()->json($companies);
    }

    public function getDepartement()
    {
        $departement = MsDepartment::select('department_id')->get();       
        return response()->json($departement);
    }
    
    public function show($taskId)
    {
        $projecttask = ProjectTask::findOrFail($taskId);

        $t_approval = T_approval::where('docid', $projecttask->taskid)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->orderBy('aprvid')
            ->get();

        $t_attachment = Attachment::where('docid', $projecttask->taskid)
            ->where('status', 'A')
            ->get();
            

        return response()->json([
            'task' => $projecttask,
            'approvals' => $t_approval,
            'attachments' => $t_attachment,
        ]);
    }
 
    public function getApprovals($taskId)
    {
        // Ambil data approval berdasarkan taskId
        // $approvals = Approval::where('task_id', $taskId)->get();
        $task = ProjectTask::find($taskId);
        
        $approvals = T_approval::where('docid', $task->taskid)
            ->where('status','<>','X')   
            ->orderBy('created_at')
            ->orderBy('aprvid')         
            ->get();
        
        return response()->json($approvals);
    }

    public function storeComments(Request $request)
    {
        $user = request()->user();

        $task = ProjectTask::where('taskid', $request->task_id)->first();       
        $comment = T_Message::create([
            'docid' => $request->task_id,
            'doctype' => 'TSK',
            'username' => $user->username,
            'name' => $user->fullname,
            'message' => $request->comment,
            'status' => 'A',
        ]);
     
        return response()->json([
            'status' => 'success',
            'message' => 'Comment added successfully',
            'comment' => [
                'taskId' => $task->id,
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user' => $comment->name,
                'created_at' => $comment->created_at->diffForHumans(),
            ],
        ]);
    }
   
    public function showcomments($taskId)
    {
        // Cek apakah task ditemukan
        $task = ProjectTask::find($taskId);

        if (!$task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found'
            ], 404);
        }

        // Ambil komentar berdasarkan taskid
        $comments = T_Message::where('docid', $task->taskid)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'comments' => $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->message,  // Pastikan pakai 'message'
                    'user' => $comment->name,  // Ambil nama user
                    'created_at' => $comment->created_at->diffForHumans(),
                ];
            })
        ]);
    }

    public function approveTask(Request $request, $taskId)
    {
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $task = ProjectTask::where('taskid', $taskId)->first();   

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }        

        $count_approval = T_approval::where('docid', '=', $task->taskid)
            ->where('status', '=', 'P')
            ->count();
    
        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $task->taskid)
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
            $t_approval->name = $user->fullname;
            $t_approval->save();
        }   

        if ($count_approval == 1) {
            $task->status = 'C';
            $task->completed_user = $user->username;
            $task->completed_at = $datestamp;
            $task->save();
        }

        $t_approval_next = T_approval::where('docid', $task->taskid)
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


    public function rejectTask(Request $request, $taskId)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $task = ProjectTask::where('taskid', $taskId)->first();  
        
        
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $task->taskid)
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
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $task->taskid)
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

    public function reviseTask(Request $request, $taskId)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $task = ProjectTask::where('taskid', $taskId)->first();  
        
        
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $task->taskid)
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
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $task->taskid)
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

    public function destroyAttach($id)
    {
        // Cari attachment berdasarkan ID
        $attachment = Attachment::findOrFail($id);
    
        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found'], 404);
        }

        // // Lokasi file di dalam folder public/attachments
        // $filePath = public_path('attachments/' . $attachment->attachfile);

        // // Hapus file dari storage jika ada
        // if (File::exists($filePath)) {
        //     File::delete($filePath);
        // }

        // Hapus record dari database
        $attachment->delete();

        return response()->json(['success' => 'Attachment deleted successfully']);
    }




    


}
