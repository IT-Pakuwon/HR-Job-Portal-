<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Personnel;
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
use App\Models\CompanyAddress;
use App\Models\StoSubGrading;


use Mail;


class PersonnelController extends Controller
{
    public function index()
    {
        $all = Personnel::count();
        $onProgress = Personnel::where('status', 'P')->count();
        $reject = Personnel::where('status', 'R')->count();
        $revise = Personnel::where('status', 'D')->count();
        $completed = Personnel::where('status', 'C')->count();
       
        return view('pages.personnels.personnels', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }
    
    public function json(Request $request)
    {
        // $status = $request->query('status', 'P');
        $status = $request->has('status') ? $request->query('status') : 'P';

        $query = Personnel::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $personnel = $query->orderBy('id', 'desc')->get();

        return response()->json(['data' => $personnel]);
    }



    public function createPersonnel()
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
        $skillTags = MJobtag::select('id', 'job_tags')->get(); 
        $division = Division::select('division_id','division_name')->get();

        $subgradings = StoSubGrading::select('subgrade_id','subgrade_name')
            ->where('status', 'A')
            ->orderBy('subgrade_id')
            ->get();

       
        return view('pages.personnels.createpersonnels', compact('companies','departements','joblevel','usercpny','usercpny2','userdept','userdept2','skillTags','division','subgradings'));
    }

    public function createPersonnelx()
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
        $skillTags = MJobtag::select('id', 'job_tags')->get(); 
       
        return view('pages.personnels.createpersonnelsx', compact('companies','departements','joblevel','usercpny','usercpny2','userdept','userdept2','skillTags'));
    }


    public function storePersonnel(Request $request)
    {
        // dd($request->all()); 
      
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            // 'division' => 'required|string',
            'job_title' => 'required|string',
            'subgrade_id' => 'required|string',
            'immediate_superior' => 'required|string',
            'state_position' => 'required|string',
            'job_type' => 'required|string',
            'reason_vacancy' => 'required|string',
            'required' => 'required|integer',
            'actual' => 'required|integer',
            'total_actual' => 'required|integer',
            'attachments.*' => 'file|max:2048' // Validasi file, max 2MB
        ]);

        $doctype = 'PRF';
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
           
            // $site = Site::where('id', $request->siteid)              
            //     ->where('status', 'A')
            //     ->first();

            $title = StoDepartement::where('departement_id', $request->job_title)              
                ->where('status', 'A')
                ->first();

            $grading = StoSubGrading::where('subgrade_id', $request->subgrade_id)              
                ->where('status', 'A')
                ->first();
                       
            $task = Personnel::create([
                'docid' => $docid,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'division_id' => $request->division,
                'locationname' => $request->siteid ?? null,
                'date' => $datenow,
                'user' => $user->username,
                'job_title' => $request->job_title,
                'subgrade_id' => $request->subgrade_id,
                'job_level' => $grading->subgrade_name,                
                'immediate_superior' => $request->immediate_superior,                
                'state_position' => $request->state_position,
                'job_type' => $request->job_type,
                'reason_vacancy' => $request->reason_vacancy,
                'required' => $request->required,
                'actual' => $request->actual,
                'total_actual' => $request->total_actual,       
                'education' => $request->education,
                'education_jurusan' => $request->education_jurusan,
                'experience_start' => $request->experience_start,
                'experience_end' => $request->experience_end, 
                'experience_position' => $request->experience_position,          
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

            if ($request->has('responsibilities')) {
                foreach ($request->responsibilities as $index => $responsibility) {
                    JobResponsiblities::create([
                        'docid' => $docid,
                        'no_job_responsiblities' => $index + 1, // Urutan dimulai dari 1
                        'job_responsibilities_descr' => $responsibility,
                        'created_user' => $user->username,
                        'status' => 'P'                                               
                    ]);
                }
            }
            
            // Simpan Qualification
            if ($request->has('qualification')) {
                foreach ($request->qualification as $index => $qualification) {
                    JobQualification::create([
                        'docid' => $docid,
                        'no_job_qualification' => $index + 1,
                        'job_qualification_descr' => $qualification,
                        'created_user' => $user->username,
                        'status' => 'P'   
                    ]);
                }
            }

            if ($request->has('tags')) {
                foreach ($request->tags as $tag) {
                    // Insert ke TrJobtag (langsung saja karena ini log history / transaksi)
                    TrJobtag::create([
                        'docid' => $docid,
                        'job_tags' => $tag,
                        'created_user' => $user->username,
                        'status' => 'P'
                    ]);

                    // Cek apakah tag sudah ada di MJobtag
                    $exists = MJobtag::where('job_tags', $tag)->exists();

                    // Jika belum ada, baru insert ke master
                    if (!$exists) {
                        MJobtag::create([
                            'job_tags' => $tag,
                            'created_user' => $user->username,
                            'status' => 'A'
                        ]);
                    }
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
                'info' => $request->job_title,           
                'url' => url('/showpersonnels/') . $id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Personnels');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }       

            DB::commit();
            return response()->json(['success' => true, 'task' => $task]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan task', 'message' => $e->getMessage()], 500);
        }
    }


    public function editPersonnel($id)
    {
        $personnel = Personnel::findOrFail($id);
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
        $skillTags = MJobtag::select('id', 'job_tags')->get(); 
        $division = Division::select('division_id','division_name')->get();

        $attachment = Attachment::where('docid', $personnel->docid)  
            ->where('status','A')         
            ->get();

        $subgradings = StoSubGrading::select('subgrade_id','subgrade_name')
            ->where('status', 'A')
            ->orderBy('subgrade_id')
            ->get();

        $jobres = JobResponsiblities::where('docid', $personnel->docid)           
            ->get();
        $jobqua = JobQualification::where('docid', $personnel->docid)           
            ->get();

        $selectedTags = TrJobtag::where('docid', $personnel->docid)           
            ->pluck('job_tags')
            ->toArray();

            

        return view('pages.personnels.editpersonnels', compact('companies','departements','joblevel','usercpny','usercpny2','userdept','userdept2','skillTags','division','personnel','attachment','subgradings','jobres','jobqua','selectedTags'));
    }
    
    public function updatePersonnel(Request $request, $id)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'job_title' => 'required|string',
            // 'job_level' => 'required|string',
            'subgrade_id' => 'required|string',
            'immediate_superior' => 'required|string',
            'state_position' => 'required|string',
            'job_type' => 'required|string|in:Replacement,New',
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

            $personnel = Personnel::findOrFail($id);

            $title = StoDepartement::where('departement_id', $request->job_title)              
                ->where('status', 'A')
                ->first();

            $grading = StoSubGrading::where('subgrade_id', $request->subgrade_id)              
                ->where('status', 'A')
                ->first();
                       
            $personnel -> update([              
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'date' => $datenow,
                'locationname' => $request->siteid ?? null,
                'user' => $user->username,
                'job_title' => $request->job_title,
                'subgrade_id' => $request->subgrade_id,
                'job_level' => $grading->subgrade_name,                
                'immediate_superior' => $request->immediate_superior,                
                'state_position' => $request->state_position,
                'job_type' => $request->job_type,
                'reason_vacancy' => $request->reason_vacancy,
                'required' => $request->required,
                'actual' => $request->actual,
                'total_actual' => $request->total_actual,   
                'education' => $request->education,
                'experience_start' => $request->experience_start,
                'experience_end' => $request->experience_end,             
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
                    'docid' => $personnel->docid,
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
                JobResponsiblities::where('docid', $personnel->docid)->delete();
                foreach ($request->responsibilities as $index => $responsibility) {                    
                    JobResponsiblities::create([
                        'docid' => $personnel->docid,
                        'no_job_responsiblities' => $index + 1, // Urutan dimulai dari 1
                        'job_responsibilities_descr' => $responsibility,
                        'created_user' => $user->username,
                        'status' => 'P'                                               
                    ]);
                }
            }
            
            // Simpan Qualification
            if ($request->has('qualification')) {
                JobQualification::where('docid', $personnel->docid)->delete();
                foreach ($request->qualification as $index => $qualification) {
                    JobQualification::create([
                        'docid' => $personnel->docid,
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
                    $attach->docid = $personnel->docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }

            $t_approval_next = T_approval::where('docid', $personnel->docid)
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
                'url' => url('/showpersonnels/') . $personnel->id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Personnels');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }

            DB::commit();
            return response()->json(['success' => true, 'personnel' => $personnel]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan personnel', 'message' => $e->getMessage()], 500);
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
 

    public function showPersonnel($id)
    {        
        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }
        
        $personnel = Personnel::findOrFail($id);
        // $personnel = Personnel::with('departement.subgrading')->findOrFail($id);
        $approval = T_approval::where('docid', $personnel->docid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();

        $jobres = JobResponsiblities::where('docid', $personnel->docid)           
            ->get();
        $jobqua = JobQualification::where('docid', $personnel->docid)           
            ->get();
        $attachment = Attachment::where('docid', $personnel->docid)    
            ->where('status','A')        
            ->get();
        $jobtag = TrJobtag::where('docid', $personnel->docid)           
            ->get();
       
        return view('pages.personnels.showpersonnels', compact('personnel','jobres','jobqua','approval','attachment','jobtag'));
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

    public function approvePersonnel(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login
        
        $personnel = Personnel::where('docid', $docid)->first();   

        if (!$personnel) {
            return response()->json(['success' => false, 'message' => 'Prf not found'], 404);
        }        

        $count_approval = T_approval::where('docid', '=', $personnel->docid)
            ->where('status', '=', 'P')
            ->count();
    
        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $personnel->docid)
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
            $personnel->status = 'C';
            $personnel->completed_user = $user->username;
            $personnel->completed_at = $datestamp;
            $personnel->save();
            app('App\Http\Controllers\PersonnelController')->insert_jobposting($docid);
        }

        $t_approval_next = T_approval::where('docid', $personnel->docid)
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
                'info' => $personnel->job_title,               
                'url' => url('/showvpersonels/') . $personnel->id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Personnel');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectPersonnel(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $personnel = Personnel::where('docid', $docid)->first();  
        
        
        if (!$personnel) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $personnel->docid)
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

            $personnel->status = 'R';
            $personnel->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $personnel->docid)
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
            'info' => $personnel->job_title,               
            'url' => url('/showvpersonels/') . $personnel->id

        );

       
        $email_it = User::where('username', $personnel->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected Personnel');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        $id = $personnel->id;
        $doctype ='PRF';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Personnel rejected successfully']);
    }

    public function revisePersonnel(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $personnel = Personnel::where('docid', $docid)->first();  
        
        
        if (!$personnel) {
            return response()->json(['success' => false, 'message' => 'Personnel not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $personnel->docid)
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

            $personnel->status = 'D';
            $personnel->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $personnel->docid)
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
            'info' => $personnel->job_title,               
            'url' => url('/showvpersonels/') . $personnel->id

        );

       
        $email_it = User::where('username', $personnel->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Revise Personnel');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        $id = $personnel->id;
        $doctype ='PRF';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Personnel revise successfully']);
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

            // $personnel = Personnel::where('docid', $id)          
            //     ->first();
            $personnel = Personnel::with(['divisionRef'])
                ->where('docid', $id)
                ->first();

                  
            $task = Jobposting::create([
                'docid' => $docid,
                'refid' => $personnel->docid,
                'cpnyid' => $personnel->cpnyid,
                'departementid' => $personnel->departementid,
                'division_id' => optional($personnel->divisionRef)->division_name,
                'locationname' => $personnel->locationname,
                'date' => $datenow,
                'job_title' => $personnel->job_title,
                'subgrade_id' => $personnel->subgrade_id,
                'job_level' => $personnel->job_level,                
                'immediate_superior' => $personnel->immediate_superior,                
                'state_position' => $personnel->state_position,
                'job_type' => $personnel->job_type,
                'reason_vacancy' => $personnel->reason_vacancy,
                'required' => $personnel->required,
                'actual' => $personnel->actual,
                'total_actual' => $personnel->total_actual,       
                'education' => $personnel->education,
                'experience_start' => $personnel->experience_start,
                'experience_end' => $personnel->experience_end,           
                'created_user' => $user->username,
                'status' =>'P'              
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
                    'status' => 'P'                                               
                ]);
            }   
            
            if (!$personnel) {
                throw new \RuntimeException('Personnel tidak ditemukan');
            }

            // nomor awal untuk qualification
            $no = 1;

            // Education
            $eduParts = array_filter([
                $personnel->education ?? null,
                // $personnel->education_jurusan ?? null,
                'All Major',
            ], fn ($v) => filled($v));

            if (count($eduParts)) {
                JobpostingQualification::create([
                    'docid' => $docid,
                    'refid' => $personnel->docid,
                    'no_job_qualification' => $no++,
                    'job_qualification_descr' => 'Minimum Education ' . implode(' ', $eduParts),
                    'created_user' => $user->username,
                    'status' => 'P',
                ]);
            }

            // Experience
            $start = $personnel->experience_start ?? null;
            // $role  = $personnel->experience_position ?? null;
            $role  = $personnel->job_title ?? null;

            $desc = null;
            if (filled($start) && filled($role)) {
                $desc = "Having Experience {$start} years as {$role}";
            } elseif (filled($start)) {
                $desc = "Having Experience {$start} years";
            } elseif (filled($role)) {
                $desc = "Having Experience as {$role}";
            }

            if ($desc) {
                JobpostingQualification::create([
                    'docid'                   => $docid,
                    'refid'                   => $personnel->docid,
                    'no_job_qualification'    => $no++,
                    'job_qualification_descr' => $desc,
                    'created_user'            => $user->username,
                    'status'                  => 'P',
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
                    'status' => 'P'                                               
                ]);
            }          

            $jobtag = TrJobtag::where('docid', $id)          
                ->get();
            
            foreach ($jobtag as $jt) {
                Jobpostingtag::create([
                    'docid' => $docid,
                    'refid' => $jt->docid,
                    'job_tags' => $jt->job_tags,                  
                    'created_user' => $jt->created_user,
                    'status' => 'P'                                               
                ]);
            }          
                      
            DB::commit();
            return response()->json(['success' => true, 'task' => $task]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan task', 'message' => $e->getMessage()], 500);
        }
    }

    public function getSitesByCompany($cpnyid)
    {
        // $sites = Site::where('cpnyid', $cpnyid)
        //     ->select('id', 'site')         
        //     ->get();

        // $sites = Site::select('id', 'site')         
        //     ->get();
        $sites = CompanyAddress::where('status', 'A')
            ->whereNotNull('sitelocation')
            ->where('sitelocation', '<>', '')        // optional: hindari string kosong
            ->select('sitelocation as site')
            ->distinct()
            ->get();



        return response()->json($sites);
    }

    public function getVacantByDepartment_xxx($deptId)
    {
        // Ambil ID departemen berdasarkan nama (misal "IT")
        $dept = StoDepartement::where('departement_name', $deptId)->first(['departement_id']);
       
        if (!$dept) {
            abort(404, 'Departemen tidak ditemukan');
        }

        $departments = DB::table('hr_ms_sto_employee as e')
            ->join('hr_ms_sto_departement as d2', 'e.departement_id', '=', 'd2.departement_id')
            ->where('e.employee_name', 'VACANT')
            ->where('e.status', 'A')
            ->whereIn('d2.parent_id', function ($query) use ($dept) {
                $query->select('d1.departement_id')
                    ->from('hr_ms_sto_departement as d1')
                    ->where('d1.parent_id', $dept->departement_id);
            })
            ->select('d2.departement_id', 'd2.departement_name', 'e.id','d2.parent_id', 'e.employee_level')           
            ->get();       
 
        return response()->json($departments);
    }

    public function getReplacementByTopParent($parentDeptName)
    {
        $topDept = DB::table('hr_ms_sto_departement')
            ->whereNull('parent_id')
            ->where('departement_name', $parentDeptName)
            ->first();

        if (!$topDept) {
            return response()->json(['error' => 'Parent departement not found'], 404);
        }

        $childIds = $this->getAllChildDepartments($topDept->departement_id);

        $employees = DB::table('hr_ms_sto_employee as e')
            ->join('hr_ms_sto_departement as d', 'e.departement_id', '=', 'd.departement_id')
            ->where('e.employee_name', '<>', 'VACANT')   // bukan VACANT
            ->where('e.status', 'A')
            ->whereNotNull('e.refid') // hanya yang memiliki refid
            ->whereIn('e.departement_id', $childIds)
            ->select('e.id as employee_id', 'e.employee_name', 'e.employee_company', 'd.departement_id', 'd.departement_name','d.subgrade_name','d.parent_id')
            ->get();

        return response()->json($employees);
    }


    public function getVacantByTopParent($parentDeptName)
    {
        // Ambil departemen root berdasarkan nama (ex: IT, ENGINEERING)
        $topDept = DB::table('hr_ms_sto_departement')
            ->whereNull('parent_id')
            ->where('departement_name', $parentDeptName)
            ->first();

        if (!$topDept) {
            return response()->json(['error' => 'Parent departement not found'], 404);
        }

        $childIds = $this->getAllChildDepartments($topDept->departement_id);

        $vacants = DB::table('hr_ms_sto_employee as e')
            ->join('hr_ms_sto_departement as d', 'e.departement_id', '=', 'd.departement_id')
            ->where('e.employee_name', 'VACANT')
            ->where('e.status', 'A')
            ->whereIn('e.departement_id', $childIds)
            ->select('e.id as employee_id', 'e.employee_name', 'e.employee_company', 'd.departement_id', 'd.departement_name','d.subgrade_name','d.parent_id','d.subgrade_id')
            ->get();

        return response()->json($vacants);
    }

    private function getAllChildDepartments($parentId)
    {
        $all = [$parentId];
        $stack = [$parentId];

        while (!empty($stack)) {
            $current = array_pop($stack);

            $children = DB::table('hr_ms_sto_departement')
                ->where('parent_id', $current)
                ->pluck('departement_id')
                ->toArray();

            $all = array_merge($all, $children);
            $stack = array_merge($stack, $children);
        }

        return array_unique($all);
    }



    public function getParentJobInfo_allkaryawan($parentId, $departementId, $deptId)
    {
        $employee = DB::table('hr_ms_sto_employee as e')
            ->join('hr_ms_sto_departement as d', 'e.departement_id', '=', 'd.departement_id')
            ->where('d.departement_id', $parentId)
            ->where('e.employee_name', '!=', 'VACANT') // pastikan bukan VACANT
            ->select('e.employee_name', 'e.employee_level')
            ->first();

        $jobprofile = DB::table('hr_ms_sto_job_profile')
            ->where('departement_id', $departementId)
            ->get();

        $jobspec = DB::table('hr_ms_sto_job_spec')
            ->where('departement_id', $departementId)
            ->first();

        $dept = StoDepartement::where('departement_name', $deptId)->first(['departement_id']);

        $childIds = $this->getAllChildDepartments($dept->departement_id);
        dd($childIds);
        $actual = DB::table('hr_ms_sto_employee as e')
            ->whereIn('e.departement_id', $childIds)
            ->where('e.employee_name', '!=', 'VACANT')
            ->where('e.status', 'A')
            ->count();

        return response()->json([
            'employee_name' => $employee->employee_name ?? 'Not Found',
            'employee_level' => $employee->employee_level ?? '',
            'experience_min' => $jobspec->experience_min ?? '',
            'experience_position' => $jobspec->experience_position ?? '',
            'education_min' => $jobspec->education_min ?? '',
            'education_jurusan' => $jobspec->education_jurusan ?? '',
            'job_profile' => $jobprofile,
            'actual' => $actual,
            'required' => 1,
            'total_actual' => $actual + 1,
        ]);
    }


    public function getParentJobInfo($parentId, $departementId,$deptId)
    {
        
        // Ambil 1 orang selain VACANT di parent_id tsb
        $employee = DB::table('hr_ms_sto_employee as e')
            ->join('hr_ms_sto_departement as d', 'e.departement_id', '=', 'd.departement_id')
            ->where('d.departement_id', $parentId)           
            ->select('e.employee_name', 'e.employee_level','d.subgrade_name')
            ->first();
        // dd($employee);
        $jobprofile = DB::table('hr_ms_sto_job_profile')           
            ->where('departement_id', $departementId)    
            ->get();

        $jobspec = DB::table('hr_ms_sto_job_spec')           
            ->where('departement_id', $departementId)    
            ->first();       

        $actual = DB::table('hr_ms_sto_employee as e')
            ->where('e.departement_id', $departementId)
            ->where('e.employee_name', '!=', 'VACANT')
            ->where('e.status', 'A')
            ->count();


        // $dept = StoDepartement::where('departement_name', $deptId)->first(['departement_id']);
        
        // $actual = DB::table('hr_ms_sto_employee as e')
        //     ->join('hr_ms_sto_departement as d2', 'e.departement_id', '=', 'd2.departement_id')
        //     ->where('e.employee_name', '!=','VACANT')
        //     ->where('e.status', 'A')
        //     ->whereIn('d2.parent_id', function ($query) use ($dept) {
        //         $query->select('d1.departement_id')
        //             ->from('hr_ms_sto_departement as d1')
        //             ->where('d1.parent_id', $departementId);
        //     })
        //     ->select('d2.departement_id', 'd2.departement_name', 'e.id','d2.parent_id', 'e.employee_level')           
        //     ->count(); 

                 
        return response()->json([
            'employee_name' => $employee->employee_name ?? 'Not Found',
            'employee_level' => $employee->subgrade_name ?? '',
            'experience_min' => $jobspec->experience_min ?? '',
            'experience_position' => $jobspec->experience_position ?? '',
            'education_min' => $jobspec->education_min ?? '',
            'education_jurusan' => $jobspec->education_jurusan ?? '',
            'job_profile' => $jobprofile,
            'actual' => $actual,
            'required' => 1,
            'total_actual' => $actual + 1,
        ]);
        
    }

    public function getJobParentInfoEdit($parentId, $departementId,$deptId, Request $request)
    {
        $docid = $request->query('docid');
        
        // Ambil 1 orang selain VACANT di parent_id tsb
        $employee = DB::table('hr_ms_sto_employee as e')
            ->join('hr_ms_sto_departement as d', 'e.departement_id', '=', 'd.departement_id')
            ->where('d.departement_id', $parentId)           
            ->select('e.employee_name', 'e.employee_level','d.subgrade_name')
            ->first();
        // dd($employee);
        $jobprofile = DB::table('hr_ms_sto_job_profile')           
            ->where('departement_id', $departementId)    
            ->get();

        $jobspec = DB::table('hr_ms_sto_job_spec')           
            ->where('departement_id', $departementId)    
            ->first();       

        $actual = DB::table('hr_ms_sto_employee as e')
            ->where('e.departement_id', $departementId)
            ->where('e.employee_name', '!=', 'VACANT')
            ->where('e.status', 'A')
            ->count();

        $skill = DB::table('hr_trx_prf_job_qualification')           
            ->where('docid', $docid)    
            ->get();

        $tags = DB::table('hr_trx_prf_job_tags')           
            ->where('docid', $docid)    
            ->get();

                     
        return response()->json([
            'employee_name' => $employee->employee_name ?? 'Not Found',
            'employee_level' => $employee->subgrade_name ?? '',
            'experience_min' => $jobspec->experience_min ?? '',
            'experience_position' => $jobspec->experience_position ?? '',
            'education_min' => $jobspec->education_min ?? '',
            'education_jurusan' => $jobspec->education_jurusan ?? '',
            'job_profile' => $jobprofile,
            'skill' => $skill,
            'tags' => $tags,
            'actual' => $actual,
            'required' => 1,
            'total_actual' => $actual + 1,
        ]);
        
    }






}
