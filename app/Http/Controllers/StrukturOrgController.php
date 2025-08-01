<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\TrSto;
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
use Mail;
use App\Models\StoEmployee;
use App\Models\StoDepartement;
use App\Models\StoJobProfile;
use App\Models\StoJobSpec;
use App\Models\StoGrading;
use App\Models\StoSubGrading;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class StrukturOrgController extends Controller
{
    public function index(Request $request)
    {
    
        $user = request()->user();
        $baseQuery = TrSto::query();
        // if (!isset($user->role) || $user->role !== 'admin') {
        //     $cpnyids = is_array($user->companyid) ? $user->companyid : explode(',', $user->companyid);
        //     $departementids = is_array($user->departmentid) ? $user->departmentid : explode(',', $user->departmentid);
        //     if ($cpnyids && $cpnyids[0] !== '') {
        //         $baseQuery->whereIn('cpnyid', (array)$cpnyids);
        //     }
        //     if ($departementids && $departementids[0] !== '') {
        //         $baseQuery->whereIn('departementid', (array)$departementids);
        //     }
        // }

        $all = (clone $baseQuery)->count();
        $onProgress = (clone $baseQuery)->where('status', 'P')->count();
        $reject = (clone $baseQuery)->where('status', 'R')->count();
        $revise = (clone $baseQuery)->whereIn('status', ['D', 'H'])->count();
        $completed = (clone $baseQuery)->where('status', 'C')->count();

        return view('pages.stos.stos', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }
    
    public function json(Request $request)
    {
        // DataTables server-side protocol
        $status = $request->has('status') ? $request->query('status') : 'P';
        $query = TrSto::query();

        // Filter by cpnyid and departementid if present     
        $user = request()->user();
        // if (!isset($user->role) || $user->role !== 'admin') {
        //     $cpnyids = is_array($user->companyid) ? $user->companyid : explode(',', $user->companyid);
        //     $departementids = is_array($user->departmentid) ? $user->departmentid : explode(',', $user->departmentid);
        //     if ($cpnyids && $cpnyids[0] !== '') {
        //         $query->whereIn('cpnyid', (array)$cpnyids);
        //     }
        //     if ($departementids && $departementids[0] !== '') {
        //         $query->whereIn('departementid', (array)$departementids);
        //     }
        // }

        // If status is 'D' (Revise) or 'H' (Draft), treat both as the same filter
        if (!empty($status)) {
            if ($status === 'D') {
                $query->whereIn('status', ['D', 'H']);
            } else {
                $query->where('status', $status);
            }
        }

        // Search
        $search = $request->input('search.value');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('sto_id', 'like', "%$search%")
                  ->orWhere('cpnyid', 'like', "%$search%")
                  ->orWhere('departementid', 'like', "%$search%")
                  ->orWhere('user', 'like', "%$search%")
                  ->orWhere('status', 'like', "%$search%")
                  ->orWhere('created_user', 'like', "%$search%")
                  ->orWhere('sto_date', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%")
                ;
            });
        }

        // Sorting
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = ['id', 'sto_date', 'cpnyid', 'departementid', 'user', 'status'];
        $orderColumn = $columns[$orderColumnIndex ?? 0] ?? 'id';
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));
        $total = $query->count();
        $data = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }


    public function createSto(Request $request)
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
        $departements = Dept::select('deptname')->get();
        $joblevel = JobLevel::select('title_level')->get(); 
        $subgrading = StoSubGrading::select('subgrade_id','subgrade_name')->get();
        
        $users = User::select('name','npk')
            ->where('status','A')
            ->get();
       
        // Cek apakah sudah ada STO yg belum di-submit
        $sto = TrSto::where('user', $user->username)
            ->where('status', 'H') // atau 'Draft'
            ->latest('created_at')
            ->first();

        if (!$sto) {
            // Jika belum ada, buat baru
            $sto_id = $this->insert_sto_autonbr($request, $usercpny2, $userdept2);
            $sto = TrSto::where('sto_id', $sto_id)->first();
        }
        // $subdepartments = StoDepartement::select('departement_id','departement_name','subgrade_name')
        //     ->where('status','A')
        //     ->get();

        $parentdepartments = StoDepartement::select('departement_id','departement_name','subgrade_name')
            ->where('status','A')
            ->get();

        $root = StoDepartement::where('departement_name', $user->departmentid)->where('status', 'A')->first();
        
        $subdepartments = collect();

        if ($root) {           
            $subdepartments = $this->getAllChildDepartments($root->departement_id);
            $parentdepartments = $this->getAllChildDepartments($root->departement_id);

        }
        
   
        return view('pages.stos.createstos', compact('companies','departements','joblevel','usercpny','usercpny2','userdept','userdept2','sto','users','subdepartments','subgrading','parentdepartments'));
    }

    private function getAllChildDepartments($parentId)
    {
        $children = StoDepartement::where('parent_id', $parentId)
            ->where('status', 'A')
            ->get();

        $all = collect($children);

        foreach ($children as $child) {
            $descendants = $this->getAllChildDepartments($child->departement_id);
            $all = $all->merge($descendants);
        }

        return $all;
    }



    public function storeSto(Request $request)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',        
            'attachments.*' => 'file|max:2048' // Validasi file, max 2MB
        ]);

        $doctype = 'STO';
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
            
            $sto = TrSto::where('status', 'H')
                ->where('sto_id', $request->sto_id)               
                ->first();

            $sto->sto_date = $datenow;
            $sto->cpnyid = $request->cpnyid;
            $sto->departementid = $request->departementid;
            $sto->updated_user = $user->username;
            $sto->status = 'P';
            $sto->save();
           
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
                    'docid' => $sto->sto_id,
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
                    $attach->docid = $sto->sto_id;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }
            

            $t_approval_next = T_approval::where('docid', $sto->sto_id)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();

            $id = $sto->id;
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,                          
                'info' => 'Struktur Organisasi New Employee',           
                'url' => url('/showsto/') . $id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval STO');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }       

            DB::commit();
            return response()->json(['success' => true, 'sto' => $sto]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan sto', 'message' => $e->getMessage()], 500);
        }
    }

    
    public function editSto(Request $request,$id)
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
        $departements = Dept::select('deptname')->get();
        $joblevel = JobLevel::select('title_level')->get(); 
        $subgrading = StoSubGrading::select('subgrade_id','subgrade_name')->get();
        
        $users = User::select('name','npk')
            ->where('status','A')
            ->get();
       
        
        $sto = TrSto::findOrFail($id);

        if (!$sto) {
            // Jika belum ada, buat baru
            $sto_id = $this->insert_sto_autonbr($request, $usercpny2, $userdept2);
            $sto = TrSto::where('sto_id', $sto_id)->first();
        }
        // $subdepartments = StoDepartement::select('departement_id','departement_name','subgrade_name')
        //     ->where('status','A')
        //     ->get();

        $parentdepartments = StoDepartement::select('departement_id','departement_name','subgrade_name')
            ->where('status','A')
            ->get();

        $root = StoDepartement::where('departement_name', $sto->departmentid)->where('status', 'A')->first();
        
        $subdepartments = collect();

        if ($root) {           
            $subdepartments = $this->getAllChildDepartments($root->departement_id);
            $parentdepartments = $this->getAllChildDepartments($root->departement_id);

        }

        $attachment = Attachment::where('docid', $sto->sto_id)  
            ->where('status','A')         
            ->get();
        
   
        return view('pages.stos.editstos', compact('companies','departements','joblevel','usercpny','usercpny2','userdept','userdept2','sto','users','subdepartments','subgrading','parentdepartments','attachment'));
    }

        
     public function updateSto(Request $request)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',        
            'attachments.*' => 'file|max:2048' // Validasi file, max 2MB
        ]);

        $doctype = 'STO';
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
            
            $sto = TrSto::where('status', 'D')
                ->where('sto_id', $request->sto_id)               
                ->first();

            $sto->sto_date = $datenow;
            $sto->cpnyid = $request->cpnyid;
            $sto->departementid = $request->departementid;
            $sto->updated_user = $user->username;
            $sto->status = 'P';
            $sto->save();
           
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
                    'docid' => $sto->sto_id,
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
                    $attach->docid = $sto->sto_id;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }
            

            $t_approval_next = T_approval::where('docid', $sto->sto_id)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();

            $id = $sto->id;
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,                          
                'info' => 'Struktur Organisasi New Employee',           
                'url' => url('/showsto/') . $id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval STO');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }       

            DB::commit();
            return response()->json(['success' => true, 'sto' => $sto]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan sto', 'message' => $e->getMessage()], 500);
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
 

    public function showSto($id)
    {        
        $sto = TrSto::findOrFail($id);
        $approval = T_approval::where('docid', $sto->sto_id)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();
       
        $attachment = Attachment::where('docid', $sto->sto_id)    
            ->where('status','A')        
            ->get();    
            
        // $employee = StoEmployee::where('refid', $sto->sto_id)    
        //     ->where('status','A')        
        //     ->get();
        $employee = StoEmployee::with('department') // ✅ load relasi departemen
            ->where('refid', $sto->sto_id)
            ->where('status', 'A')
            ->get();
        
        return view('pages.stos.showstos', compact('sto','approval','attachment','employee'));
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
        $comment->doctype = 'STO';
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

    public function approveSto(Request $request, $docid)
    {
        // dd($docid);
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login
        
        $sto = TrSto::where('sto_id', $docid)->first();   

        if (!$sto) {
            return response()->json(['success' => false, 'message' => 'Prf not found'], 404);
        }        

        $count_approval = T_approval::where('docid', '=', $sto->sto_id)
            ->where('status', '=', 'P')
            ->count();
    
        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $sto->sto_id)
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
            $sto->status = 'C';
            $sto->completed_user = $user->username;
            $sto->completed_at = $datestamp;
            $sto->save();
           
        }

        $t_approval_next = T_approval::where('docid', $sto->sto_id)
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
                'info' => 'Struktur Organisasi New Employee',               
                'url' => url('/showstos/') . $sto->id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval STO');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectSto(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $sto = TrSto::where('sto_id', $docid)->first();  
        
        
        if (!$sto) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $sto->sto_id)
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

            $sto->status = 'R';
            $sto->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $sto->sto_id)
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
            'info' => 'Struktur Organisasi New Employee',               
            'url' => url('/showstos/') . $sto->id

        );
       
        $email_it = User::where('username', $sto->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected STO');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        $id = $sto->id;
        $doctype ='STO';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'STO rejected successfully']);
    }

    public function reviseSto(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $sto = TrSto::where('sto_id', $docid)->first();  
        
        
        if (!$sto) {
            return response()->json(['success' => false, 'message' => 'STO not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $sto->sto_id)
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

            $sto->status = 'D';
            $sto->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $sto->sto_id)
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
            'info' => 'Struktur Organisasi New Employee',               
            'url' => url('/showstos/') . $sto->id

        );

       
        $email_it = User::where('username', $sto->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Revise STO');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        $id = $sto->id;
        $doctype ='STO';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'STO revise successfully']);
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
    
    public function jsonOrg_ori()
    {
        $user = Auth::user();
        if (!$user || !$user->departmentid) {
            return response()->json(['error' => 'Unauthorized or department not found'], 403);
        }

        $userDept = $user->departmentid;
        // $userDept = 'ENGINEERING';


        $root = StoDepartement::where('departement_name', $userDept)
            ->where('status', 'A')
            ->first();

        if (!$root) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $allDepartments = StoDepartement::where('status', 'A')->get();

        function getDescendants($departments, $parentId) {
            $result = collect();
            foreach ($departments as $dept) {
                if ($dept->parent_id == $parentId) {
                    $result->push($dept);
                    $result = $result->merge(getDescendants($departments, $dept->departement_id));
                }
            }
            return $result;
        }

        $filtered = collect([$root])->merge(getDescendants($allDepartments, $root->departement_id));
    
        $data = [];
        // $cpnyList = explode(',', $user->companyid);
        
        foreach ($filtered as $dept) {            

            $memberList = $dept->hr_ms_sto_employee()
                // ->whereIn('employee_company', $cpnyList)
                ->get()
                ->map(function ($m) {
                return [
                    'name' => $m->employee_name,
                    'company' => $m->employee_company,
                    'position' => $m->employee_level,
                    'image' => $m->image ?? 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
                ];
            });

            $data[] = [
                'id' => $dept->departement_id,
                'parentId' => $dept->parent_id,
                'name' => $dept->departement_name,
                'position' => 'Department',
                'members' => $memberList->toArray(),
                'image' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            ];
        }

        return response()->json($data);
    }

    public function jsonOrg()
    {
        $user = Auth::user();
        if (!$user || !$user->departmentid) {
            return response()->json(['error' => 'Unauthorized or department not found'], 403);
        }

        $userDept = $user->departmentid;

        $root = StoDepartement::where('departement_name', $userDept)
            ->where('status', 'A')
            ->first();

        if (!$root) {
            return response()->json(['error' => 'Department not found'], 404);
        }
        // if ($root) {
        //     $root->parent_id = null; // ⚠️ Trik ini agar tetap tampil meskipun aslinya punya parent
        // }

        $allDepartments = StoDepartement::where('status', 'A')->get();

        function getDescendants($departments, $parentId) {
            $result = collect();
            foreach ($departments as $dept) {
                if ($dept->parent_id == $parentId) {
                    $result->push($dept);
                    $result = $result->merge(getDescendants($departments, $dept->departement_id));
                }
            }
            return $result;
        }

        // $filtered = collect([$root])->merge(getDescendants($allDepartments, $root->departement_id));
        // Langkah 1: Ambil semua ID hasil filter
        $rawFiltered = collect([$root])->merge(getDescendants($allDepartments, $root->departement_id));

        // Langkah 2: Query ulang sebagai Eloquent Collection agar bisa eager-load relasi
        $filtered = StoDepartement::whereIn('departement_id', $rawFiltered->pluck('departement_id'))
            ->with('subgrading')
            ->get();

        $data = [];

        foreach ($filtered as $dept) {
            $memberList = $dept->hr_ms_sto_employee()
                ->get()
                ->map(function ($m) {
                    return [
                        'name' => $m->employee_name,
                        'company' => $m->employee_company,
                        // 'position' => $m->employee_level,
                        'image' => $m->image ?? 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
                    ];
                });

            $data[] = [
                'id' => (string) $dept->departement_id,
                'parentId' => $dept->parent_id ? (string)$dept->parent_id : null,
                'direct_parent_id' => $dept->direct_parent_id ? (string)$dept->direct_parent_id : null,
                'name' => $dept->departement_name,
                'position' => $dept->subgrade_name ?? '',
                'bgColor' => optional($dept->subgrading)->subgrade_color_code ?? '#f5f5f5',
                'members' => $memberList->toArray(),
                'image' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            ];
        }

        // 🔗 Ambil koneksi tambahan dari direct_parent_id
        $connections = $filtered->filter(function ($dept) {
            return $dept->direct_parent_id && $dept->direct_parent_id != $dept->parent_id;
        })->map(function ($dept) {
            return [
                'from' => (string) $dept->departement_id,
                'to' => (string) $dept->direct_parent_id,
                'label' => 'Additional Link',
            ];
        })->values();

        return response()->json([
            'nodes' => $data,
            'connections' => $connections,
        ]);
    }



    public function storeOrg(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();

        // Tentukan apakah ini request untuk Employee atau Departement berdasarkan field yang dikirim
       if ($request->has('full_name')) {
            $validator = Validator::make($request->all(), [
                'approval_line' => 'required|integer',
                'full_name' => 'required|string|max:100',
                // 'job_position' => 'required|string|max:100',
                'cpnyid' => 'required|string',
                'qty' => 'nullable|integer|min:1',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $qty = $request->input('qty', 1);
            
            // Simpan gambar jika ada
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('avatar'), $filename);
                $imageUrl = 'avatar/' . $filename;
            }

            for ($i = 0; $i < $qty; $i++) {
                $employee = new StoEmployee();
                $employee->departement_id = $request->approval_line;
                $employee->employee_name = $request->full_name;
                // $employee->employee_level = $request->job_position;
                $employee->employee_id = $request->npk;
                $employee->employee_company = $request->cpnyid;
                $employee->refid = $request->sto_id;
                $employee->created_user = $user->username;
                $employee->status = 'A';
                $employee->image = $imageUrl; // simpan URL-nya
                $employee->save();
            }

            return response()->json([
                'success' => true,
                'type' => 'employee',
                'message' => $qty . ' employee(s) saved'
            ]);

        } elseif ($request->has('departement_name')) {
            // === Menyimpan Departement ===
            $validator = Validator::make($request->all(), [
                'departement_name' => 'required|string|max:100',
                'parent_id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            $departement = new StoDepartement();
            $departement->departement_name = $request->departement_name;
            $departement->parent_id = $request->approval_line ?? null;
            $departement->refid = $request->sto_id;
            $departement->subgrade_id = $request->subgrade_id;

            // Ambil hanya bagian setelah tanda " - "
            $subgradeParts = explode(' - ', $request->subgrade_name);
            $departement->subgrade_name = $subgradeParts[1] ?? $request->subgrade_name;

            $departement->created_user = $user->username;
            $departement->status = 'A';

            $departement->save();

            
            // Update departement_id dengan ID yang baru saja dibuat
            $departement->departement_id = $departement->id;
            $departement->save();

            return response()->json(['success' => true, 'type' => 'departement']);

        } elseif ($request->has('job_purpose')) {
            $validator = Validator::make($request->all(), [
                'approval_line' => 'required|integer',
                'sto_id' => 'required|string',
                'job_purpose' => 'required|array|min:1',
                'job_purpose.*' => 'required|string|max:255',
                'education_level' => 'required|string|max:50',
                'education_major' => 'required|string|max:100',
                'experience_years' => 'required|integer|min:0',
                'experience_position' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $lastNumber = StoJobProfile::where('departement_id', $request->approval_line)
                ->where('refid', $request->sto_id)
                ->max('no_job_purpose') ?? 0;

            foreach ($request->job_purpose as $index => $purpose) {
                $jobProfile = new StoJobProfile(); 
                $jobProfile->departement_id = $request->approval_line;
                $jobProfile->refid = $request->sto_id;
                $jobProfile->created_user = $user->username;
                $jobProfile->job_purpose = $purpose;   
                $jobProfile->job_level = $request->job_level;  
                $jobProfile->no_job_purpose = $lastNumber + $index + 1;
                $jobProfile->status = 'P';
                $jobProfile->save();
            }

            $jobSpec = new StoJobSpec();
            $jobSpec->departement_id = $request->approval_line;
            $jobSpec->job_level = $request->job_level;;
            $jobSpec->refid = $request->sto_id;
            $jobSpec->education_min = $request->education_level;
            $jobSpec->education_jurusan = $request->education_major;
            $jobSpec->experience_min = $request->experience_years;
            $jobSpec->experience_position = $request->experience_position;
            $jobSpec->created_user = $user->username;
            $jobSpec->status = 'P';
            $jobSpec->save();

            return response()->json(['success' => true, 'type' => 'job_spec']);
        }


        return response()->json(['error' => 'Invalid request data'], 400);
    }

    public function getEmployeesByDept_old($dept_id)
    {
        $employees = StoEmployee::where('departement_id', $dept_id)
            ->where('status', 'A')
            ->get(['id','departement_id','employee_name', 'employee_company', 'employee_level', 'image']);

        $dept = StoDepartement::where('departement_id', $dept_id)->first();
        $departementName = $dept ? $dept->departement_name : 'Unknown Department';

        return response()->json([
        'departement_name' => $departementName,
        'employees' => $employees
    ]);
    }
    public function getEmployeesByDept($dept_id)
    {
        // Ambil departemen
        $dept = StoDepartement::where('departement_id', $dept_id)->first();
        $departementName = $dept ? $dept->departement_name : 'Unknown Department';
        $subgradeName = $dept ? $dept->subgrade_name : '-';

        // Ambil semua karyawan, lalu tambahkan field subgrade_name dari departemen
        $employees = StoEmployee::where('departement_id', $dept_id)
            ->where('status', 'A')
            ->get(['id', 'departement_id', 'employee_name', 'employee_company', 'image']) // Hapus employee_level
            ->map(function ($emp) use ($subgradeName) {
                return [
                    'id' => $emp->id,
                    'departement_id' => $emp->departement_id,
                    'employee_name' => $emp->employee_name,
                    'employee_company' => $emp->employee_company,
                    'employee_level' => $subgradeName, // Gantikan dengan subgrade_name                   
                    'image' => $emp->image ? asset('avatar/' . ltrim($emp->image, '/')) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
                ];
            });

        return response()->json([
            'departement_name' => $departementName,
            'employees' => $employees,
        ]);
    }


    public function insert_sto_autonbr($request, $usercpny2, $userdept2)
    {
       
        DB::beginTransaction();
        try {
            $doctype = 'STO';
            $datenow = Carbon::now()->format('Y-m-d');
            $now = Carbon::now();
            $year = $now->year;
            $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $user = Auth::user();

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
            $docid = $doctype . $tglbln . sprintf("%03d", $urutan);

            if ($usercpny2==null){
                $usercpny2 = $user->companyid;
                $userdept2 = $user->departmentid;
            }else{
                $usercpny2 = $user->cpnyid;
                $userdept2 = $user->deptname;
            }    

            $sto = new TrSto();
            $sto->sto_id = $docid;
            $sto->sto_date = $datenow;
            $sto->cpnyid = $usercpny2;
            $sto->departementid = $userdept2;
            $sto->user = $user->username;
            $sto->created_user = $user->username;
            $sto->status = 'H';
            $sto->save();

            DB::commit();
            return $docid; // <--- ini kuncinya
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }

    public function updateEmployee(Request $request, $id)
    {
        // dd($id);
        // dd($request->all());
        $employee = StoEmployee::findOrFail($id);
        
        $user = Auth::user();
      
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('avatar'), $filename);
            $imageUrl = 'avatar/' . $filename;
        }

            $employee->employee_name = $request->employee_name;          
            $employee->employee_id = $request->npk;
            $employee->employee_company = $request->employee_company;       
            $employee->updated_user = $user->username;
            $employee->status = 'A';
            $employee->image = $imageUrl; // simpan URL-nya
            $employee->save();
     
        return response()->json(['success' => true]);
    }

    public function deleteEmployee($id)
    {
        $employee = StoEmployee::findOrFail($id);
        $employee->delete();
        return response()->json(['success' => true]);
    }

    public function changeEmployeeDepartment(Request $request)
    {
        $request->validate([
            'old_dept_id' => 'required|integer',
            'new_dept_id' => 'required|integer',
        ]);

        // Update semua employee dengan departement lama ke departement baru
        StoEmployee::where('departement_id', $request->old_dept_id)
            ->update(['departement_id' => $request->new_dept_id]);

        return response()->json(['success' => true]);
    }

    public function stoall(Request $request)
    {
        $user = request()->user();
       
        $companies = Company::select('cpnyid')->get();
        $departements = Dept::select('deptname')->get();   
        $joblevel = JobLevel::select('title_level')->get();     
        $users = User::select('name')
            ->where('status','A')
            ->get();
        $departments = StoDepartement::select('departement_id','departement_name')
            ->where('status','A')
            ->get();
   
        return view('pages.stos.stoall', compact('companies','departements','joblevel','users','departments'));
    }

    public function jsonOrgall()
    {
        $user = Auth::user();
        if (!$user || !$user->departmentid) {
            return response()->json(['error' => 'Unauthorized or department not found'], 403);
        }

        $userDept = $user->departmentid;
        // $userDept = 'ENGINEERING';


        $root = StoDepartement::where('departement_name', $userDept)
            ->where('status', 'A')
            ->first();

        if (!$root) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $allDepartments = StoDepartement::where('status', 'A')->get();

        function getDescendants($departments, $parentId) {
            $result = collect();
            foreach ($departments as $dept) {
                if ($dept->parent_id == $parentId) {
                    $result->push($dept);
                    $result = $result->merge(getDescendants($departments, $dept->departement_id));
                }
            }
            return $result;
        }

        $filtered = collect([$root])->merge(getDescendants($allDepartments, $root->departement_id));
    
        $data = [];
        // $cpnyList = explode(',', $user->companyid);
        
        foreach ($filtered as $dept) {            

            $memberList = $dept->hr_ms_sto_employee()
                // ->whereIn('employee_company', $cpnyList)
                ->get()
                ->map(function ($m) {
                return [
                    'name' => $m->employee_name,
                    'company' => $m->employee_company,
                    'position' => $m->employee_level,
                    'image' => $m->image ?? 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
                ];
            });

            $data[] = [
                'id' => $dept->departement_id,
                'parentId' => $dept->parent_id,
                'name' => $dept->departement_name,
                'position' => 'Department',
                'members' => $memberList->toArray(),
                'image' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            ];
        }

        return response()->json($data);
    }

    public function jsonOrgByDept($deptname)
    {
        $companyFilter = request()->query('company'); // 🔍 Ambil filter company dari query string

        $root = StoDepartement::whereRaw('LOWER(departement_name) = ?', [strtolower($deptname)])
            ->where('status', 'A')
            ->first();

        if (!$root) {
            return response()->json(['nodes' => [], 'connections' => []]);
        }

        $allDepartments = StoDepartement::where('status', 'A')->get();

        function getDescendants($departments, $parentId) {
            $result = collect();
            foreach ($departments as $dept) {
                if ($dept->parent_id == $parentId) {
                    $result->push($dept);
                    $result = $result->merge(getDescendants($departments, $dept->departement_id));
                }
            }
            return $result;
        }

        // $filtered = collect([$root])->merge(getDescendants($allDepartments, $root->departement_id));
        $rawFiltered = collect([$root])->merge(getDescendants($allDepartments, $root->departement_id));

        // Langkah 2: Query ulang sebagai Eloquent Collection agar bisa eager-load relasi
        $filtered = StoDepartement::whereIn('departement_id', $rawFiltered->pluck('departement_id'))
            ->with('subgrading')
            ->get();

        $data = [];

        foreach ($filtered as $dept) {
            $employeeQuery = $dept->hr_ms_sto_employee(); // Relasi model

            if ($companyFilter) {
                $employeeQuery->where('employee_company', $companyFilter);
            }

            $memberList = $employeeQuery->get()->map(function ($m) {
                return [
                    'name' => $m->employee_name,
                    'company' => $m->employee_company,
                    // 'position' => $m->employee_level,
                    'image' => $m->image ? asset('avatar/' . ltrim($m->image, '/')) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
                ];
            });

            $data[] = [
                'id' => (string) $dept->departement_id,
                'parentId' => $dept->parent_id ? (string)$dept->parent_id : null,
                'direct_parent_id' => $dept->direct_parent_id ? (string)$dept->direct_parent_id : null,
                'name' => $dept->departement_name,
                'position' => $dept->subgrade_name ?? '',
                'bgColor' => optional($dept->subgrading)->subgrade_color_code ?? '#f5f5f5',
                'members' => $memberList->toArray(),
                'image' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            ];
        }

        // 🔗 Koneksi tambahan dari direct_parent_id
        $connections = $filtered->filter(function ($dept) {
            return $dept->direct_parent_id && $dept->direct_parent_id != $dept->parent_id;
        })->map(function ($dept) {
            return [
                'from' => (string) $dept->departement_id,
                'to' => (string) $dept->direct_parent_id,
                'label' => 'Additional Link',
            ];
        })->values();

        return response()->json([
            'nodes' => $data,
            'connections' => $connections,
        ]);
    }
   
  

    public function jsonOrgShow($id)
    {
        
        $sto = TrSto::findOrFail($id);

        $userDept = $sto->departementid;
       
        $root = StoDepartement::where('departement_name', $userDept)
            ->where('status', 'A')
            ->first();

        if (!$root) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $allDepartments = StoDepartement::where('status', 'A')->get();

        function getDescendants($departments, $parentId) {
            $result = collect();
            foreach ($departments as $dept) {
                if ($dept->parent_id == $parentId) {
                    $result->push($dept);
                    $result = $result->merge(getDescendants($departments, $dept->departement_id));
                }
            }
            return $result;
        }

        // $filtered = collect([$root])->merge(getDescendants($allDepartments, $root->departement_id));       
        $rawFiltered = collect([$root])->merge(getDescendants($allDepartments, $root->departement_id));

        // Langkah 2: Query ulang sebagai Eloquent Collection agar bisa eager-load relasi
        $filtered = StoDepartement::whereIn('departement_id', $rawFiltered->pluck('departement_id'))
            ->with('subgrading')
            ->get();
    
        $data = [];
        // $cpnyList = explode(',', $user->companyid);
        
        foreach ($filtered as $dept) {            

            $memberList = $dept->hr_ms_sto_employee()
                // ->whereIn('employee_company', $cpnyList)
                ->get()
                ->map(function ($m) {
                return [
                    'name' => $m->employee_name,
                    'company' => $m->employee_company,
                    // 'position' => $m->employee_level,                   
                    'image' => $m->image ? asset('avatar/' . ltrim($m->image, '/')) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png',

                ];
            });

            $data[] = [
                'id' => $dept->departement_id,
                'parentId' => $dept->parent_id,
                'name' => $dept->departement_name,
                'position' => $dept->subgrade_name ?? '',
                'bgColor' => optional($dept->subgrading)->subgrade_color_code ?? '#f5f5f5',               
                'members' => $memberList->toArray(),
                'image' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            ];
        }

         // 🔗 Koneksi tambahan dari direct_parent_id
        $connections = $filtered->filter(function ($dept) {
            return $dept->direct_parent_id && $dept->direct_parent_id != $dept->parent_id;
        })->map(function ($dept) {
            return [
                'from' => (string) $dept->departement_id,
                'to' => (string) $dept->direct_parent_id,
                'label' => 'Additional Link',
            ];
        })->values();

        return response()->json([
            'nodes' => $data,
            'connections' => $connections,
        ]);
    }

    public function getJobProfile($id)
    {
     
        $employee = StoEmployee::where('id', $id)           
            ->first();
        // dd($employee);
        $profiles = StoJobProfile::where('departement_id', $employee->departement_id)
            ->get();
         
        // $spec = StoJobSpec::where('departement_id', $employee->departement_id)
        //     ->orderby('id','Desc')
        //     ->first();
        $spec = DB::table('hr_ms_sto_job_spec as js')
            ->leftJoin('hr_ms_sto_subgrading as sg', 'js.job_level', '=', 'sg.subgrade_id')
            ->where('js.departement_id', $employee->departement_id)
            ->orderBy('js.id', 'desc')
            ->select('js.*', 'sg.subgrade_name')
            ->first();

        return response()->json([
            'profiles' => $profiles,
            'spec' => $spec,
        ]);
    }

    public function deleteJobProfile($id)
    {
        $profile = StoJobProfile::find($id);

        if (!$profile) {
            return response()->json(['error' => 'Data not found'], 404);
        }

        $profile->delete();

        return response()->json(['success' => true, 'message' => 'Job Purpose deleted']);
    }

    public function changeParent(Request $request)
    {
        // $request->validate([
        //     'dept_id' => 'required|integer',
        //     'new_parent_id' => 'required|integer',
        // ]);
        // dd($request->all());
        $dept = StoDepartement::findOrFail($request->dept_id);
     
        $dept->parent_id = $request->new_parent_id;
        $dept->save();

        return response()->json(['success' => true, 'message' => 'Parent department updated successfully.']);
    }

    public function getDepartmentDetail($id)
    {
       
        $dept = StoDepartement::with('parent')->findOrFail($id);
 
        return response()->json([
            'data' => [
                'departement_id' => $dept->departement_id,
                'departement_name' => $dept->departement_name,
                'parent_id' => $dept->parent_id,
                'parent_name' => optional($dept->parent)->departement_name
            ]
        ]);
    }

    public function fullscreen($id)
    {       
        $sto = TrSto::findOrFail($id);        
        return view('pages.stos.showfullstos', compact('sto'));
       

    }







}
