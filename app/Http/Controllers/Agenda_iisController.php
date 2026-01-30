<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use App\Models\Agenda;
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
use App\Models\JobApplySch;
use App\Models\JobApply;
use App\Models\JobApplyStep;
use App\Models\Jobposting;
use App\Models\Applicant;
use App\Models\Meeting;
use App\Models\Roommeet;
use App\Models\Accesoriesroom;
use Mail;
use App\Services\ZoomApi;
use App\Models\Viewtrxmeeting;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;


class AgendaController extends Controller
{
    protected $zoomApi;    

    public function __construct(ZoomApi $zoomApi)
    {
        $this->zoomApi = $zoomApi;
    }
    
    public function index()
    {
        $username = auth()->user()->username;

        $baseQuery = Agenda::where(function ($q) use ($username) {
            $q->where('created_user', $username)
            ->orWhereRaw('FIND_IN_SET(?, participant)', [$username]);
        });

        $all = (clone $baseQuery)->count();
        $onProgress = (clone $baseQuery)->where('status', 'P')->count();
        $reject = (clone $baseQuery)->where('status', 'R')->count();
        $revise = (clone $baseQuery)->where('status', 'D')->count();
        $completed = (clone $baseQuery)->where('status', 'C')->count();

        return view('pages.agendas.agendas', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }

   
    public function json(Request $request)
    {
        // $status = $request->query('status', 'P');
        $status = $request->has('status') ? $request->query('status') : 'P';
        $username = auth()->user()->username;

        $query = Agenda::query();
        
        // $query->where('created_user', $username);
        $query->where(function ($q) use ($username) {
            $q->where('created_user', $username)
              ->orWhereRaw('FIND_IN_SET(?, participant)', [$username]);
        });


        if (!empty($status)) {
            $query->where('status', $status);
        }

        $agenda = $query->orderBy('id', 'desc')->get();

        return response()->json(['data' => $agenda]);
    }

    public function createAgenda()
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
        $userlist = User::where('status','A')
            ->get();

        $room = Roommeet::where('status','A')
            ->where('room_id','<>','p')
            ->get();
        $accessories = Accesoriesroom::where('status','A')  
            ->get();
       
        return view('pages.agendas.createagendas', compact('usercpny','usercpny2','userdept','userdept2','userlist','room','accessories'));
    }


    public function storeAgenda(Request $request)
    {
        // dd($request->all()); 

        $roomId = $request->input('room_id');
        $accId = $request->input('acc_id');
       
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',           
            'title' => 'required|string',           
            'startdate' => 'nullable|date',
            'enddate' => 'nullable|date|after_or_equal:startdate',  
            'attachments.*' => 'file|max:2048' // Validasi file, max 2MB
        ]);

               
        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'AGD';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            // Generate agenda ID
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
                       
            $agenda = Agenda::create([
                'docid' => $docid,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'agendadate' => $datenow,                               
                'title' => $request->title,                
                'description' => $request->description,
                'status' => $request->status ?? 'P',
                'startdate' => $request->startdate,
                'enddate' => $request->enddate,   
                'reftype' => $request->reftype,
                'location' => $request->location,  
                'location_address' => $request->location_address,    
                'created_user' => $user->username,
                'refid' => $request->refid,
                'participant' => $userlist->appreance              
            ]);
            
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
            
            $docidagenda = $docid;

            if($roomId && $accId){               
                $this->insert_meeting($docidagenda, $user,$roomId,$accId);
            }elseif($roomId){               
                $this->insert_meeting($docidagenda, $user,$roomId,$accId);
            }elseif($accId){                                
                dd('zoom only');     
            }else{
                if ($request->has('participant')) {
                    foreach ($request->participant as $mp) {                
                        T_approval::create([
                            'docid' => $docid,
                            'aprvid' => 1,
                            'aprvdoctype' => 'AGD',
                            'aprvcpnyid' => $request->departementid,
                            'aprvdeptid' => $request->cpnyid,
                            'aprvusername' => $mp,
                            'name' => $mp,
                            'aprvdatebefore' => $datestamp,
                            'aprvtotalday' => 1,
                            'status' => 'P',
                            'created_user' => $user->username
                        ]);
                    }            
                }    

                $t_approval_all = T_approval::where('docid', $docid)
                    ->where('status', 'P')
                    ->orderby('aprvid', 'ASC')
                    ->get();

                if (!$t_approval_all->isEmpty()) {
                    $id = $agenda->id;
                
                    foreach ($t_approval_all as $approval) {
                        $data = [
                            'docid' => $approval->docid,
                            'cpnyid' => $approval->aprvcpnyid,
                            'deptname' => $approval->aprvdeptid,
                            'date' => $approval->aprvdatebefore,
                            'name' => $approval->created_user,
                            'info' => $request->title,
                            'url' => url('/showagendas/') . $id
                        ];
                
                        $multiapp = explode(',', $approval->aprvusername);
                
                        $email_it = User::whereIn('username', $multiapp)
                            ->where('status', 'A')
                            ->get();
                
                        foreach ($email_it as $emailsit) {
                            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                                $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Waiting Approval Agendas');
                                $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                            });
                        }
                    }
                }

            }


            DB::commit();
            return response()->json(['success' => true, 'agenda' => $agenda]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan agenda', 'message' => $e->getMessage()], 500);
        }
    }

    public function editAgenda($id)
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
        $agenda = Agenda::findOrFail($id);        
        $attachment = Attachment::where('docid', $agenda->docid)  
            ->where('status','A')         
            ->get();

        $participantlist_user = explode(',', $agenda->participant);
        $userlist = User::where('status','A')
            ->get();
        

        return view('pages.agendas.editagendas', compact('agenda', 'attachment','usercpny','usercpny2','userdept','userdept2','userlist','participantlist_user'));
    }
    
    public function updateAgenda(Request $request, $id)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'agendatype' => 'required|string',
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
            $doctype = 'AGD';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            $agenda = Agenda::findOrFail($id);

            $userlist = User::where('status','A')
                ->get(); 

            $participantlist = $request->input('participant');
            if($participantlist <> null){
                $userlist->appreance = implode(',', $participantlist);
            }else{
                $userlist->appreance = '';
            }
                       
            $agenda -> update([              
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'agendadate' => $datenow,
                'agendatype' => $request->agendatype,
                'agendapriority' => $request->agendapriority,                
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
                    'docid' => $agenda->docid,
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
                    $attach->docid = $agenda->docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }

            $t_approval_next = T_approval::where('docid', $agenda->docid)
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
                'url' => url('/showagendas/') . $agenda->id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Waiting Approval Agendas');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
            }     

            DB::commit();
            return response()->json(['success' => true, 'agenda' => $agenda]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan agenda', 'message' => $e->getMessage()], 500);
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
 

    public function showAgenda($id)
    {        
        $agenda = Agenda::findOrFail($id);
        $approval = T_approval::where('docid', $agenda->docid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();

        $attachment = Attachment::where('docid', $agenda->docid)        
            ->where('status','A')   
            ->get();
       
        return view('pages.agendas.showagendas', compact('agenda','approval','attachment'));
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
        $comment->doctype = 'AGD';
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

    public function approveAgenda(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login
        
        $agenda = Agenda::where('docid', $docid)->first();   

        if (!$agenda) {
            return response()->json(['success' => false, 'message' => 'Prf not found'], 404);
        }        

        $count_approval = T_approval::where('docid', '=', $agenda->docid)
            ->where('status', '=', 'P')
            ->count();
    
        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $agenda->docid)
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
            $agenda->status = 'C';
            $agenda->completed_user = $user->username;
            $agenda->completed_at = $datestamp;
            $agenda->save();

            $this->insert_JobApplySch($agenda, $user);
            $this->sendemail_interview($agenda, $user);
            
        }

        $t_approval_next = T_approval::where('docid', $agenda->docid)
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

        return response()->json(['success' => true, 'message' => 'Agenda approved successfully']);
    }

    public function insert_JobApplySch($agenda, $user)
    {
        // dd($agenda);
        DB::beginTransaction();
        try {
                          
            $datestamp = Carbon::now()->toDateTimeString();             
            $user = Auth::user();
                        
            // $existing = JobApplySch::where('docid', $agenda->docid)
            //     ->where('jobapply_id', $agenda->refid)                
            //     ->first();
            
            // if ($existing) {
            //     return response()->json([
            //     'error' => true,
            //     'message' => 'You have already Job Schedule.'
            //     ], 409); // Conflict
            // }      
                   
    
            $jobapply = JobApply::where('docid', $agenda->refid)->first();
            $jobapplystep = JobApplyStep::where('docid', $jobapply->docid)
                ->where('status','A')
                ->orderby('step_order','DESC')
                ->first();
            // dd($jobapplystep);
            $jobsch = JobApplySch::create([
                'docid' => $agenda->docid,
                'jobapply_id' => $agenda->refid,
                'jobid' => $jobapply->jobid,
                'applicant_id' => $jobapply->applicant_id,       
                'step_id' => $jobapplystep->step_id,             
                'title' => $agenda->title,                
                'description' => $agenda->description,
                'status' => 'P',
                'startdate' => $agenda->startdate,
                'enddate' => $agenda->enddate,   
                'location' => $agenda->location,  
                'location_address' => $agenda->location_address,  
                'reftype' => $agenda->reftype,  
                'created_user' => $user->username,               
                'participant' => $agenda->participant             
            ]);                      
           
            DB::commit();
            return response()->json(['success' => true, 'jobsch' => $jobsch]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan Transaksi Checklist', 'message' => $e->getMessage()], 500);
        }
    }

    public function rejectAgenda(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $agenda = Agenda::where('docid', $docid)->first();  
        
        
        if (!$agenda) {
            return response()->json(['success' => false, 'message' => 'Agenda not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $agenda->docid)
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

            $agenda->status = 'R';
            $agenda->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $agenda->docid)
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
        //     'info' => $agenda->summary,            
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
        $id = $agenda->id;
        $doctype ='AGD';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Agenda rejected successfully']);
    }

    public function reviseAgenda(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $agenda = Agenda::where('docid', $docid)->first();  
        
        
        if (!$agenda) {
            return response()->json(['success' => false, 'message' => 'Agenda not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $agenda->docid)
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

            $agenda->status = 'D';
            $agenda->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $agenda->docid)
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
        //     'info' => $agenda->summary,            
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
        $id = $agenda->id;
        $doctype ='AGD';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Agenda revise successfully']);
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

    public function getAgendas(Request $request)
    {
        $date = $request->query('date', Carbon::now()->format('Y-m-d')); // Default ke hari ini jika tidak ada tanggal

        if (!$date) {
            return response()->json(['error' => 'Date parameter is required'], 400);
        }
        $username = auth()->user()->username;
       
        // $agendas = Agenda::whereDate('startdate', $date)           
        //     ->where(function ($query) use ($username) {
        //         $query->where('created_user', $username)
        //             ->orWhereRaw("CONCAT(',', participant, ',') LIKE ?", ["%,$username,%"]);
        //     })
        //     ->orderBy('startdate', 'asc')
        //     ->get();
            
        // Ambil docid dari trx_approval di database mysql2
        $docids = DB::connection('mysql2')
            ->table('trx_approval')
            ->where('status', 'A')
            ->where('aprvusername', $username)
            ->pluck('docid')
            ->toArray();

        // Query agenda
        $agendas = Agenda::whereDate('startdate', $date)
            ->where(function ($query) use ($username, $docids) {
                $query->where('created_user', $username)
                    ->orWhereIn('docid', $docids);
            })
            ->orderBy('startdate', 'asc')
            ->get();


        return response()->json($agendas);
    }

    public function show($id)
    {
        $agenda = Agenda::find($id);

        if (!$agenda) {
            return response()->json(['message' => 'Agenda not found'], 404);
        }

        return response()->json($agenda);
    }

    public function getMonthlyAgendas(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $username = auth()->user()->username;

        // $agendas = Agenda::whereYear('startdate', $year)
        //     ->whereMonth('startdate', $month)           
        //     ->where(function ($query) use ($username) {
        //         $query->where('created_user', $username)
        //             ->orWhereRaw("CONCAT(',', participant, ',') LIKE ?", ["%,$username,%"]);
        //     })
        //     ->get();
        // Ambil docid dari trx_approval di database mysql2
        $docids = DB::connection('mysql2')
            ->table('trx_approval')
            ->where('status', 'A')
            ->where('aprvusername', $username)
            ->pluck('docid')
            ->toArray();

        // Query agenda
        $agendas = Agenda::whereYear('startdate', $year)
            ->whereMonth('startdate', $month)
            ->where(function ($query) use ($username, $docids) {
                $query->where('created_user', $username)
                    ->orWhereIn('docid', $docids);
            })           
            ->get();

        return response()->json($agendas);
    }

    public function sendemail_interview($agenda, $user)
    {

        $jobstep = JobApplyStep::where('docid', $agenda->refid)->first();

        $applicant = Applicant::where('applicant_id', $jobstep->applicant_id)->first();
        $jobposting = Jobposting::where('docid', $jobstep->jobid)->first();

        if (!$applicant || empty($applicant->email_address)) {
            return response()->json(['error' => 'Applicant email not found.'], 404);
        }

        $data = [
            'name' => $applicant->full_name ?? 'Pelamar',
            'location' => $agenda->location ?? '',
            'address' => $agenda->location_address ?? '',
            'startdate' => Carbon::parse($agenda->startdate)->translatedFormat('l, d F Y'), // e.g., Senin, 05 Mei 2025
            'starttime' => Carbon::parse($agenda->startdate)->format('H:i'), // e.g., 09:00
            'endtime'   => Carbon::parse($agenda->enddate)->format('H:i'),   // e.g., 10:00
            'jobtitle' => $jobposting->job_title ?? '',
        ];

        Mail::send('emails.mailinterview', $data, function ($message) use ($applicant,$data) {
            $message->to($applicant->email_address)
                    ->subject('📩 Panggilan Interview Pakuwon Career');
            $message->from('digitalserver@pakuwon.com', 'Pakuwon Career');
        });

        return response()->json(['success' => 'Email has been sent to applicant.']);
    }

    public function cancelAgenda(Request $request)
    {

        // dd($request->all());
        // $request->validate([
        //     'agenda_id' => 'required|exists:agendas,id',
        //     'reason' => 'required|string'
        // ]);

        $agenda = Agenda::findOrFail($request->agenda_id);
        $agenda->status = 'X';
        $agenda->agenda_note = $request->reason;
        $agenda->save();

        return response()->json(['success' => true]);
    }

    public function checkRoomAvailability(Request $request)
    {
        $roomId = $request->input('room_id');
        $startDate = $request->input('startdate');
        $endDate = $request->input('enddate');

        // Convert to Carbon instances for easier date comparison
        $startDate = \Carbon\Carbon::parse($startDate);
        $endDate = \Carbon\Carbon::parse($endDate);

        // Check if there are any existing meetings with the same room_id and overlapping times
        $roomBooking = Meeting::where('room_id', $roomId)
                            ->where(function($query) use ($startDate, $endDate) {
                                $query->whereBetween('start', [$startDate, $endDate])
                                    ->orWhereBetween('end', [$startDate, $endDate])
                                    ->orWhere(function($query) use ($startDate, $endDate) {
                                        $query->where('start', '<=', $startDate)
                                                ->where('end', '>=', $endDate);
                                    });
                            })
                            ->first();

        if ($roomBooking) {
            return response()->json(['status' => 'booked']);
        } else {
            return response()->json(['status' => 'available']);
        }
    }

    public function insert_meeting($docidagenda, $user,$roomId,$accId)
    {
        // dd($roomId.'-'.$accId);         

        // if($accId){
            $accessories = Accesoriesroom::where('status','A')  
                ->where('room_id',$roomId)
                ->orderby('id','ASC')
                ->first();
        // }

        $agenda = Agenda::where('docid',$docidagenda)             
            ->first();
                   
        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'MTR';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            // Generate agenda ID
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

            $participantUsernames = explode(',', $agenda->participant);

            // Ambil email valid dari tabel User berdasarkan username
            $participants = User::whereIn('username', $participantUsernames)
                ->whereNotNull('notification_email')
                ->pluck('notification_email')
                ->toArray();

            // Gabungkan menjadi string email dengan koma
            $participantEmails = implode(',', $participants);
                                      
            $meeting = Meeting::create([
                'docid' => $docid,
                'cpnyid' => $agenda->cpnyid,
                'deptname' => $agenda->departementid,
                'locationname' => '',
                'date' => $datenow,
                'user' => $user->username,                        
                'status' => 'P',
                'start' => $agenda->startdate,
                'end' => $agenda->enddate, 
                'title' => $agenda->title,  
                'descr' => $agenda->description,
                'participant' => '', 
                'participantlist' => $participantEmails,
                'acc_id' => $accessories->id,
                'room_id' => $roomId, 
                'checked' => '',     
                'created_user' => $user->name,
                'site' => '',   
                'checkin' => 'N',  
                'checkout' => 'N',    
                'fullbooked'=> 'N',      
                'cpnyid_site' => ''
            ]);

            T_approval::create([
                'docid' => $docid,
                'aprvid' => '1',
                'aprvdoctype' => 'MTR',
                'aprvcpnyid' => $agenda->cpnyid,
                'aprvdeptid' => $agenda->departementid,
                'aprvusername' => 'system',
                'name' => 'SYSTEM',
                'aprvdatebefore' => $datestamp,
                'aprvdateafter' => $datestamp,
                'aprvtotalday' => 1,
                'status' => 'A',
                'created_user' => $user->name
            ]);
                              
            $agenda->refid = $docid;
            $agenda->save();        

            $id = $meeting->id;
            if($accId){                
                $this->create_zoom_id($docid);
                $this->send_email_all($id);
            }

            DB::commit();
            return response()->json(['success' => true, 'meeting' => $meeting]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan meeting', 'message' => $e->getMessage()], 500);
        }
    }

    public function create_zoom_id($docid)
    {
        // dd($docid);
        // $start_time = $start_datetime;
        $meeting = Meeting::where('docid', $docid)->first();

        $dateTimeObject1 = date_create($meeting->start); 
        $dateTimeObject2 = date_create($meeting->end); 
            
        // Calculating the difference between DateTime Objects
        $interval = date_diff($dateTimeObject1, $dateTimeObject2);         
        $min = $interval->days * 24 * 60;
        $min += $interval->h * 60;
        $min += $interval->i;  
        

        $accessories = Accesoriesroom::where('status','A')
            ->where('id',$meeting->acc_id)
            ->first();
        $user_idzoom = $accessories->user_idzoom;
         
                
        $data = [
            'topic' => $meeting->title,
            'type' => 2,
            'start_time' => $meeting->start,
            'duration' => $min,
            'timezone' => 'UTC',
            'settings' => [
                'join_before_host' => true,
                'waiting_room' => false
            ]
        ];
                
        $api_zoom = $this->zoomApi->createMeeting($data,$user_idzoom);        
        $getinvitation = $this->zoomApi->getinvitation($api_zoom->id);

        $meeting->zoom_id = $api_zoom->id;
        $meeting->info_zoom = $getinvitation['invitation'];        
        $meeting->save();
         
        
    }

    public function send_email_all(int $id)
    // public function send_email_all()
    {
        // $id=8092;
        $meeting = Viewtrxmeeting::find($id);  
           
        $date_start = Carbon::createFromFormat("Y-m-d H:i:s", $meeting->start)->setTimezone('UTC');      
        $start_datetime = $date_start->format("Ymd\THis\Z");
        
        $date_end = Carbon::createFromFormat("Y-m-d H:i:s", $meeting->end)->setTimezone('UTC');
        $end_datetime = $date_end->format("Ymd\THis\Z");

        $date_startx = Carbon::createFromFormat("Y-m-d H:i:s", $meeting->start);      
        $start_x = $date_startx->format("l, d F Y | h:i A");
        
        $date_endx = Carbon::createFromFormat("Y-m-d H:i:s", $meeting->end);
        $end_x = $date_endx->format("l, d F Y | h:i A");
        
        $filename = "invite.ics";
        $gen_ics = Calendar::create($meeting->title)
            ->event(Event::create($meeting->title)
            ->startsAt($date_startx)
            ->endsAt($date_endx)
            )
            ->get();
            // dd($gen_ics);
        $info_zoom = preg_replace('#\b(http|ftp)(s)?\://([^ \s\t\r\n]+?)([\s\t\r\n])+#smui', '<a href="$1$2://$3" target="_blank">$1$2://$3</a>$4', $meeting->info_zoom);
        // echo nl2br($test);
        $descr = preg_replace('#\b(http|ftp)(s)?\://([^ \s\t\r\n]+?)([\s\t\r\n])+#smui', '<a href="$1$2://$3" target="_blank">$1$2://$3</a>$4', $meeting->descr);
        
        $email_to = User::where('username', $meeting->user)
            ->where('status', 'A')
            ->first();
        // dd($email_to);
        if($meeting->status == 'X'){
            $subject = 'Cancel Schedule Meeting Room';
        }else{
            $subject = 'Schedule Meeting Room';
        }    
                   
        $data = array(
            'docid' => $meeting->docid,
            'cpnyid' => $meeting->cpnyid,
            'deptname' => $meeting->deptname,
            'locationname' => $meeting->locationname,
            'info' => $meeting->title,               
            'date' => $meeting->date,
            'name' => $meeting->user,
            'descr' => nl2br($descr),
            'room' => $meeting->name,
            'start'=> $start_datetime,
            'end'=> $end_datetime,
            'startx'=> $start_x,
            'endx'=> $end_x,
            'zoom'=> $meeting->acc_name,
            'info_zoom'=> nl2br($info_zoom),
            'email' => $email_to->notification_email,                      
            'url' => url('/showmeeting_') . $meeting->id,
            'emailcc' => explode(',', $meeting->participantlist.','.$email_to->notification_email),
            'gen_ics'=> $gen_ics,
            'subject'=> $subject,
        );
        
        $multiapp = explode(',', $meeting->participantlist);       
        // dd($multiapp);        
        $email_bcc = User::where('status', 'A')
            ->whereIN('notification_email', $multiapp)            
            ->get();  
        // dd($email_bcc);    
        $emailbcc = [];
        foreach ($email_bcc as $email_part) {
            foreach (explode(',', $email_part->email_bcc) as $value) {
                $emailbcc[] = trim($value);
            }
        }     
                 
        Mail::send('emails.mailmeeting', $data, function ($message) use ($data) {  
            $message->to($data['emailcc'])->subject($data['docid'] . ' - '.$data['subject']);                               
            $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');                
            $message->attachData($data['gen_ics'], 'invite.ics', [
                'mime' => 'text/calendar;charset=UTF-8;method=REQUEST',
            ]);
        });
       
    }





}
