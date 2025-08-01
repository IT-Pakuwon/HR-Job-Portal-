<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Changesto;
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
use App\Models\MJobtag;
use App\Models\TrJobtag;
use App\Models\Jobpostingtag;
use App\Models\Site;
use App\Models\StoEmployee;
use App\Models\StoDepartement;
use App\Models\StoJobProfile;
use App\Models\StoJobSpec;
use App\Models\Division;
use App\Models\StoSubGrading;
use Mail;


class ChangestoController extends Controller
{
    public function index()
    {
        $all = Changesto::count();
        $onProgress = Changesto::where('status', 'P')->count();
        $reject = Changesto::where('status', 'R')->count();
        $revise = Changesto::where('status', 'D')->count();
        $completed = Changesto::where('status', 'C')->count();
       
        return view('pages.changestos.changestos', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }
    
    public function json(Request $request)
    {
        // $status = $request->query('status', 'P');
        $status = $request->has('status') ? $request->query('status') : 'P';

        $query = Changesto::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $changesto = $query->orderBy('id', 'desc')->get();

        return response()->json(['data' => $changesto]);
    }



    public function createChangesto()
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
        $subgrading = StoSubGrading::select('subgrade_id','subgrade_name')->get();        
       
        return view('pages.changestos.createchangestos', compact('subgrading','usercpny','usercpny2','userdept','userdept2'));
    }

    
   public function storeChangesto(Request $request)
    {
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'departement_name' => 'required|string',
            'subgrade_name' => 'required|string',            
            'changerequest_note' => 'required|string',
            'attachments.*' => 'file|max:2048'
        ]);

        $doctype = 'CSO';
        $user = $request->user();
        $datenow = Carbon::now()->format('Y-m-d');
        $dt = Carbon::now();
        $year = $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = Carbon::now()->toDateTimeString();

        $approvalCount = M_approval::where([
            ['status', '=', 'A'],
            ['aprvcpnyid', '=', $request->cpnyid],
            ['aprvdeptid', '=', $request->departementid],
            ['aprvdoctype', '=', $doctype],
        ])->count();

        if ($approvalCount === 0) {
            return response()->json([
                'message' => 'Approval line belum di-setup, Please contact IT!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate autonbr dan docid
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
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
            $docid = $doctype . $tglbln . sprintf("%03d", $urutan);
            // dd($docid);
            // Simpan ke changesto
            $changesto = Changesto::create([
                'changerequest_id' => $docid,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'departement_name' => $request->departement_name,
                'subgrade_name' => $request->subgrade_name,
                'changerequest_note' => $request->changerequest_note,
                'changerequest_date' => $datenow,
                'user' => $user->username,
                'created_user' => $user->username,
                'status' => 'P'
            ]);

            // Simpan approval dari M_approval ke T_approval
            $approvals = M_approval::where([
                ['status', '=', 'A'],
                ['aprvcpnyid', '=', $request->cpnyid],
                ['aprvdeptid', '=', $request->departementid],
                ['aprvdoctype', '=', $doctype],
            ])->get();

            foreach ($approvals as $a) {
                T_approval::create([
                    'docid' => $docid,
                    'aprvid' => $a->aprvid,
                    'aprvdoctype' => $a->aprvdoctype,
                    'aprvcpnyid' => $a->aprvcpnyid,
                    'aprvdeptid' => $a->aprvdeptid,
                    'aprvusername' => $a->aprvusername,
                    'name' => $a->name,
                    'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
                    'aprvtotalday' => 1,
                    'status' => 'P',
                    'created_user' => $user->username
                ]);
            }

            // Upload attachments jika ada
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

            // Kirim email ke approver pertama
            $firstApproval = T_approval::where('docid', $docid)
                ->where('status', 'P')
                ->orderBy('aprvid')
                ->first();

            if ($firstApproval) {
                $data = [
                    'docid' => $firstApproval->docid,
                    'cpnyid' => $firstApproval->aprvcpnyid,
                    'deptname' => $firstApproval->aprvdeptid,
                    'date' => $firstApproval->aprvdatebefore,
                    'name' => $user->username,
                    'info' => $request->changerequest_note,
                    'url' => url('/showchangestos/' . $changesto->id)
                ];

                $approvers = explode(',', $firstApproval->aprvusername);
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    Mail::send('emails.mailapprove', $data, function ($message) use ($email, $data) {
                        $message->to($email)
                            ->subject($data['docid'] . ' - Waiting Approval Change STO')
                            ->from('digitalserver@pakuwon.com', 'HR System');
                    });
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'task' => $changesto]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menyimpan data',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function editChangesto($id)
    {
        $changesto = Changesto::findOrFail($id);
        $user = request()->user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();
        $subgrading = StoSubGrading::select('subgrade_id','subgrade_name')->get();   

        $attachment = Attachment::where('docid', $changesto->changerequest_id)  
            ->where('status','A')         
            ->get();
       
        return view('pages.changestos.editchangestos', compact('subgrading','usercpny','usercpny2','userdept','userdept2','changesto','attachment'));
    }
    
    public function updateChangesto(Request $request, $id)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'departement_name' => 'required|string',
            'subgrade_name' => 'required|string',            
            'changerequest_note' => 'required|string',
            'attachments.*' => 'file|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'CSO';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            $changesto = Changesto::findOrFail($id);
                                   
            $changesto -> update([              
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'departement_name' => $request->departement_name,
                'subgrade_name' => $request->subgrade_name,
                'changerequest_note' => $request->changerequest_note,
                'changerequest_date' => $datenow,
                'user' => $user->username,
                'created_user' => $user->username,
                'status' => 'P'        
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
                    'docid' => $changesto->changerequest_id,
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
                    $attach->docid = $changesto->changerequest_id;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }

            $t_approval_next = T_approval::where('docid', $changesto->changerequest_id)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();
           
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,                          
                'info' => $request->job_title,           
                'url' => url('/showchangestos/') . $changesto->id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Change STOs');
                    $message->from('digitalserver@pakuwon.com', 'HR System');
                });
            }

            DB::commit();
            return response()->json(['success' => true, 'changesto' => $changesto]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan changesto', 'message' => $e->getMessage()], 500);
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
 

    public function showChangesto($id)
    {        
        $changesto = Changesto::findOrFail($id);
        // $changesto = Changesto::with('departement.subgrading')->findOrFail($id);
        $approval = T_approval::where('docid', $changesto->changerequest_id)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();
       
        $attachment = Attachment::where('docid', $changesto->changerequest_id)    
            ->where('status','A')        
            ->get();
       
       
        return view('pages.changestos.showchangestos', compact('changesto','approval','attachment'));
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
        $comment->doctype = 'CSO';
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

    public function approveChangesto(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login
        
        $changesto = Changesto::where('changerequest_id', $docid)->first();   

        if (!$changesto) {
            return response()->json(['success' => false, 'message' => 'Prf not found'], 404);
        }        

        $count_approval = T_approval::where('docid', '=', $changesto->changerequest_id)
            ->where('status', '=', 'P')
            ->count();
    
        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $changesto->changerequest_id)
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
            $changesto->status = 'C';
            $changesto->completed_user = $user->username;
            $changesto->completed_at = $datestamp;
            $changesto->save();
            app('App\Http\Controllers\ChangestoController')->insert_jobposting($docid);
        }

        $t_approval_next = T_approval::where('docid', $changesto->changerequest_id)
            ->where('status', 'P')
            ->orderby('aprvid','ASC')
            ->first();

        if ($count_approval <> 1) {
            //update datebefore
            $t_approval_next->aprvdatebefore = $datestamp;
            $t_approval_next->save();

            //send email 
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,               
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,
                'info' => $changesto->changerequest_note,               
                'url' => url('/showchangestos/') . $changesto->id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Change STO');
                    $message->from('digitalserver@pakuwon.com', 'HR System');
                });
            }
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectChangesto(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $changesto = Changesto::where('changerequest_id', $docid)->first();  
        
        
        if (!$changesto) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $changesto->changerequest_id)
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

            $changesto->status = 'R';
            $changesto->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $changesto->changerequest_id)
            ->where('status', '=', 'P')
            ->get();

        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        //send email 
        $data = array(
            'docid' => $t_approval->docid,
            'cpnyid' => $t_approval->aprvcpnyid,
            'deptname' => $t_approval->aprvdeptid,
            // 'locationname' => $ms_site->site,
            'date' => $t_approval->aprvdatebefore,
            'name' => $t_approval->created_user,
            'info' => $changesto->changerequest_note,               
            'url' => url('/showchangestos/') . $changesto->id

        );

       
        $email_it = User::where('username', $changesto->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected Changesto');
                $message->from('digitalserver@pakuwon.com', 'HR System');
            });
        }

        $id = $changesto->id;
        $doctype ='CSO';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Changesto rejected successfully']);
    }

    public function reviseChangesto(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $changesto = Changesto::where('changerequest_id', $docid)->first();  
        
        
        if (!$changesto) {
            return response()->json(['success' => false, 'message' => 'Changesto not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $changesto->changerequest_id)
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

            $changesto->status = 'D';
            $changesto->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $changesto->changerequest_id)
            ->where('status', '=', 'P')
            ->get();

        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        //send email 
        $data = array(
            'docid' => $t_approval->docid,
            'cpnyid' => $t_approval->aprvcpnyid,
            'deptname' => $t_approval->aprvdeptid,
            // 'locationname' => $ms_site->site,
            'date' => $t_approval->aprvdatebefore,
            'name' => $t_approval->created_user,
            'info' => $changesto->changerequest_note,               
            'url' => url('/showchangestos/') . $changesto->id

        );

       
        $email_it = User::where('username', $changesto->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Revise Changesto');
                $message->from('digitalserver@pakuwon.com', 'HR System');
            });
        }

        $id = $changesto->id;
        $doctype ='CSO';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Changesto revise successfully']);
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
