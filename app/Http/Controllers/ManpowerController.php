<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use App\Models\Manpower;
use App\Models\Manpowerdetail;
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
use App\Models\Msmonth;
use Mail;


class ManpowerController extends Controller
{
    public function index()
    {
        $all = Manpower::count();
        $onProgress = Manpower::where('status', 'P')->count();
        $reject = Manpower::where('status', 'R')->count();
        $revise = Manpower::where('status', 'D')->count();
        $completed = Manpower::where('status', 'C')->count();
       
        return view('pages.manpowers.manpowers', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }
    
    public function json(Request $request)
    {
        // $status = $request->query('status', 'P');
        $status = $request->has('status') ? $request->query('status') : 'P';

        $query = Manpower::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $manpower = $query->orderBy('id', 'desc')->get();

        return response()->json(['data' => $manpower]);
    }


    public function createManpower()
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
        $month = Msmonth::select('id','month')->get();       
        $joblevel = JobLevel::select('title_level')->get();
       
        return view('pages.manpowers.createmanpowers', compact('month','joblevel','usercpny','usercpny2','userdept','userdept2'));
    }


    public function storeManpower(Request $request)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'periodyear' => 'required|string',
            'job_level' => 'required|string',   
            'actual' => 'required|integer',           
        ]);

        $doctype = 'MPP';
        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $request->cpnyid)
            ->where('aprvdeptid', $request->departementid)
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
            
            $details = [];
            $totalQty = 0;

            foreach ($request->job_title as $index => $title) {
                $expectedDate = $request->expected_employment_date[$index];
                
                $month = date('n', strtotime($expectedDate));
                $qty = (int) $request->qty[$index];
                $totalQty += $qty;

                $details[] = [
                    'periodemonth' => $month,
                    'job_title' => $title,
                    'job_level' => $request->job_level[$index],
                    'qty' => $qty,
                    'reason_vacancy' => $request->reason_vacancy[$index],
                    'expected_employment_date' => $expectedDate,
                ];
            }

            // Simpan header setelah tahu total qty
            $task = Manpower::create([
                'docid' => $docid,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'date' => $datenow,
                'periodyear' => $request->periodyear,
                'required' => $totalQty, 
                'actual' => $request->actual,
                'total_actual' => $totalQty + $request->actual,
                'created_user' => $user->username,
                'status' => $request->status ?? 'P'
            ]);

            // Simpan semua detail ke DB
            foreach ($details as $row) {                
                ManpowerDetail::create([
                    'docid' => $docid,        
                    'periodyear' => $request->periodyear,           
                    'periodmonth' => $month,
                    'job_title' => $row['job_title'],
                    'job_level' => $row['job_level'],
                    'qty' => $row['qty'],
                    'reason_vacancy' => $row['reason_vacancy'],
                    'expected_employment_date' => $row['expected_employment_date'],
                    'status' => 'P',
                    'created_user' => $user->username
                ]);
            }
           
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
            

            $t_approval_next = T_approval::where('docid', $docid)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();
            $id = $task->id;
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,                          
                'info' => $request->periodyear,           
                'url' => url('/showmanpowers/') . $id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Waiting Approval Manpower');
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

    public function editManpower($id)
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
        $manpower = Manpower::findOrFail($id);
        
        $joblevel = JobLevel::select('title_level')->get();
        $manpowerdetail = Manpowerdetail::where('docid', $manpower->docid)           
            ->get(); 
        $attachment = Attachment::where('docid', $manpower->docid)  
            ->where('status','A')         
            ->get();

        return view('pages.manpowers.editmanpowers', compact('manpower', 'joblevel','manpowerdetail','attachment','usercpny','usercpny2','userdept','userdept2'));
    }
    
    public function updateManpower(Request $request, $id)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'periodyear' => 'required|string',
            // 'job_level' => 'required|string',   
            'actual' => 'required|integer',           
        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'MPP';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            $manpower = Manpower::findOrFail($id);

            $details = [];
            $totalQty = 0;

            foreach ($request->job_title as $index => $title) {
                $expectedDate = $request->expected_employment_date[$index];
                
                $month = date('n', strtotime($expectedDate));
                $qty = (int) $request->qty[$index];
                $totalQty += $qty;

                $details[] = [
                    'periodemonth' => $month,
                    'job_title' => $title,
                    'job_level' => $request->job_level[$index],
                    'qty' => $qty,
                    'reason_vacancy' => $request->reason_vacancy[$index],
                    'expected_employment_date' => $expectedDate,
                ];
            }
                       
            $manpower -> update([          
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'date' => $datenow,
                'periodyear' => $request->periodyear,
                'required' => $totalQty, 
                'actual' => $request->actual,
                'total_actual' => $totalQty + $request->actual,
                'created_user' => $user->username,
                'status' => $request->status ?? 'P'                
            ]);

            // Hapus detail lama sebelum menyimpan ulang
            ManpowerDetail::where('docid', $manpower->docid)->delete();

            // Simpan semua detail ke DB
            foreach ($details as $row) {                
                ManpowerDetail::create([
                    'docid' => $manpower->docid,        
                    'periodyear' => $request->periodyear,           
                    'periodmonth' => $month,
                    'job_title' => $row['job_title'],
                    'job_level' => $row['job_level'],
                    'qty' => $row['qty'],
                    'reason_vacancy' => $row['reason_vacancy'],
                    'expected_employment_date' => $row['expected_employment_date'],
                    'status' => 'P',
                    'created_user' => $user->username
                ]);
            }

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
                    'docid' => $manpower->docid,
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
                    $attach->docid = $manpower->docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }

            $t_approval_next = T_approval::where('docid', $manpower->docid)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();
           
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,                          
                'info' => $request->periodyear,           
                'url' => url('/showmanpowers/') . $manpower->id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Waiting Approval Manpower');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
            }

            DB::commit();
            return response()->json(['success' => true, 'manpower' => $manpower]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan manpower', 'message' => $e->getMessage()], 500);
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
 

    public function showManpower($id)
    {        
        $manpower = Manpower::findOrFail($id);
        $approval = T_approval::where('docid', $manpower->docid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();

        $manpowerdetail = Manpowerdetail::where('docid', $manpower->docid)           
            ->get();       
        $attachment = Attachment::where('docid', $manpower->docid)    
            ->where('status','A')        
            ->get();
       
        return view('pages.manpowers.showmanpowers', compact('manpower','manpowerdetail','approval','attachment'));
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
        $comment->doctype = 'MPP';
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

    public function approveManpower(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login
        
        $manpower = Manpower::where('docid', $docid)->first();   

        if (!$manpower) {
            return response()->json(['success' => false, 'message' => 'Prf not found'], 404);
        }        

        $count_approval = T_approval::where('docid', '=', $manpower->docid)
            ->where('status', '=', 'P')
            ->count();
    
        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $manpower->docid)
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
            $manpower->status = 'C';
            $manpower->completed_user = $user->username;
            $manpower->completed_at = $datestamp;
            $manpower->save();
        }

        $t_approval_next = T_approval::where('docid', $manpower->docid)
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
                'info' => $manpower->periodyear,               
                'url' => url('/showmanpowers/') . $manpower->id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Waiting Approval Manpower');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
            }
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectManpower(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $manpower = Manpower::where('docid', $docid)->first();  
        
        
        if (!$manpower) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $manpower->docid)
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

            $manpower->status = 'R';
            $manpower->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $manpower->docid)
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
            'info' => $manpower->periodyear,               
            'url' => url('/showmanpowers/') . $manpower->id

        );

       
        $email_it = User::where('username', $manpower->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Rejected Manpower');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
            });
        }

        $id = $manpower->id;
        $doctype ='MPP';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Manpower rejected successfully']);
    }

    public function reviseManpower(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $manpower = Manpower::where('docid', $docid)->first();  
        
        
        if (!$manpower) {
            return response()->json(['success' => false, 'message' => 'Manpower not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $manpower->docid)
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

            $manpower->status = 'D';
            $manpower->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $manpower->docid)
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
            'info' => $manpower->periodyear,               
            'url' => url('/showmanpowers/') . $manpower->id

        );

       
        $email_it = User::where('username', $manpower->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Revise Manpower');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
            });
        }

        $id = $manpower->id;
        $doctype ='MPP';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Manpower revise successfully']);
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
