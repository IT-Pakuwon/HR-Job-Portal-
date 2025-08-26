<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Budget;
use App\Models\BudgetDetail;
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
use Mail;
use App\Imports\MsBudgetTempImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Models\MsBudgetTemp;
use App\Models\CompanyPG;
use App\Models\BusinessUnitPG;
use Illuminate\Support\Str;


class BudgetController extends Controller
{
    public function index()
    {
        $all = Budget::count();
        $onProgress = Budget::where('status', 'P')->count();
        $reject = Budget::where('status', 'R')->count();
        $revise = Budget::where('status', 'D')->count();
        $completed = Budget::where('status', 'C')->count();
       
        return view('pages.budgets.budgets', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }
    
    public function json(Request $request)
    {       
        $status = $request->has('status') ? $request->query('status') : 'P';

        $query = Budget::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $budget = $query->orderBy('id', 'desc')->get();

        return response()->json(['data' => $budget]);
    }

   
    public function createBudget()
    {
        $user = request()->user();
     
        $companies = CompanyPG::select('cpny_id','cpny_name')->where('status','A')->get();
        $departements = Dept::select('deptname')->get();    

        // $tempData = MsBudgetTemp::latest()->get();

        $temp_id = session('import_temp_id'); // ambil dari session

        $tempData = [];
        if ($temp_id) {
            $tempData = MsBudgetTemp::where('temp_id', $temp_id)->get();
        }

       
        return view('pages.budgets.createbudgets', compact('companies','departements','tempData','temp_id'));
    }

    public function import_xxx(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'cpny_id' => 'required',
            'business_unit_id' => 'required',
            'department_fin_id' => 'required',
        ]);

        try {
            $username = Auth::user()->username; // ambil email user yang login
            $temp_id = Str::uuid()->toString(); // generate UUID untuk temp_id

            // 🔴 Hapus data sebelumnya yang diimport oleh user yang sama
            MsBudgetTemp::where('created_by', $username)->delete();

            // ✅ Lakukan import Excel
            Excel::import(new MsBudgetTempImport(
                $temp_id,
                $request->cpny_id,
                $request->business_unit_id,
                $request->department_fin_id,
                $username // dikirimkan ke Import
            ), $request->file('file'));

            // Simpan temp_id ke session
            session(['import_temp_id' => $temp_id]);

            return redirect()->route('budget.create')->with('success', 'Data berhasil diimport.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function import(Request $request, Budget $budget = null)
    {
        $request->validate([
            'file'              => 'required|mimes:xlsx,xls,csv',
            'cpny_id'           => 'required',
            'business_unit_id'  => 'required',
            'department_fin_id' => 'required',
        ]);

        try {
            $username = Auth::user()->username;
            $temp_id  = Str::uuid()->toString();

            MsBudgetTemp::where('created_by', $username)->delete();

            // Import Excel
            Excel::import(
                new MsBudgetTempImport(
                    $temp_id,
                    $request->cpny_id,
                    $request->business_unit_id,
                    $request->department_fin_id,
                    $username
                ),
                $request->file('file')
            );

            session(['import_temp_id' => $temp_id]);

            /* ───────── Redirect ───────── */
            return $budget
                ? redirect()->route('budget.edit', $budget->id)
                            ->with('success', 'Data berhasil di‑import (edit mode).')
                : redirect()->route('budget.create')
                            ->with('success', 'Data berhasil di‑import.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    public function getBusinessUnits($cpny_id)
    {        
        $units = BusinessUnitPG::where('cpny_id', $cpny_id)->get();

        return response()->json($units);
    }
  
    public function storeBudget(Request $request)
    {
               
        // dd($request->all());               
        $temp_id = $request->input('temp_id'); 
        $doctype = 'BUD';
        $tempData = MsBudgetTemp::where('temp_id', $temp_id)->get();
        $tempHead = $tempData->first(); // ambil 1 record untuk akses data header
        $business_unit = BusinessUnitPG::where('business_unit_id', $tempHead->business_unit_id)->first();

        if (!$tempHead) {
            return response()->json(['message' => 'Tidak ada data budget import ditemukan!'], 422);
        }

        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $tempHead->cpny_id)
            ->where('aprvdeptid', $tempHead->department_fin_id)
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
         
            $budget = Budget::create([
                'budget_id' => $docid,
                'budget_date' => $datenow,
                'perpost' => $tempHead->perpost,
                'cpny_id' => $tempHead->cpny_id,
                'business_unit_id' => $business_unit->business_unit_name,  
                'department_fin_id' => $tempHead->department_fin_id,              
                'totalbudget' => $tempData->sum('totalbudget'),              
                'created_by' => $user->username,
                'status' => 'P'
            ]);
                       
            foreach ($tempData as $row) {
                BudgetDetail::create([
                    'budget_id'          => $docid,
                    'perpost'            => $row->perpost,
                    'cpny_id'            => $row->cpny_id,
                    'business_unit_id'   => $row->business_unit_id,
                    'department_fin_id'  => $row->department_fin_id,
                    'account_id'         => $row->account_id,
                    'activity_id'        => $row->activity_id,
                    'activity_detail'    => $row->activity_detail,
                    'totalbudget'        => $row->totalbudget,

                    'period01_budget'    => $row->period01_budget,
                    'period02_budget'    => $row->period02_budget,
                    'period03_budget'    => $row->period03_budget,
                    'period04_budget'    => $row->period04_budget,
                    'period05_budget'    => $row->period05_budget,
                    'period06_budget'    => $row->period06_budget,
                    'period07_budget'    => $row->period07_budget,
                    'period08_budget'    => $row->period08_budget,
                    'period09_budget'    => $row->period09_budget,
                    'period10_budget'    => $row->period10_budget,
                    'period11_budget'    => $row->period11_budget,
                    'period12_budget'    => $row->period12_budget,

                    // ===== NEW: reserve (default 0)
                    'period01_reserve'   => 0,
                    'period02_reserve'   => 0,
                    'period03_reserve'   => 0,
                    'period04_reserve'   => 0,
                    'period05_reserve'   => 0,
                    'period06_reserve'   => 0,
                    'period07_reserve'   => 0,
                    'period08_reserve'   => 0,
                    'period09_reserve'   => 0,
                    'period10_reserve'   => 0,
                    'period11_reserve'   => 0,
                    'period12_reserve'   => 0,

                    // ===== NEW: used (default 0)
                    'period01_used'      => 0,
                    'period02_used'      => 0,
                    'period03_used'      => 0,
                    'period04_used'      => 0,
                    'period05_used'      => 0,
                    'period06_used'      => 0,
                    'period07_used'      => 0,
                    'period08_used'      => 0,
                    'period09_used'      => 0,
                    'period10_used'      => 0,
                    'period11_used'      => 0,
                    'period12_used'      => 0,

                    'created_by'         => $user->username,
                    'status'             => 'P',
                ]);
            }


            MsBudgetTemp::where('temp_id', $temp_id)->delete();
           
            //read ms_approval
            $m_approval = M_approval::where('aprvdoctype', $doctype)
                ->where('aprvcpnyid', $tempHead->cpny_id)
                ->where('aprvdeptid', $tempHead->department_fin_id)
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
            $id = $budget->id;
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,       
                'info' => 'Budget Company ' . $tempHead->cpny_id . ' Department ' . $tempHead->department_fin_id . ' ' . $tempHead->perpost,      
                'url' => url('/showbudgets_')  . $id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Budgets');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
            }       

            DB::commit();
            return response()->json(['success' => true, 'budget' => $budget]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan budget', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function editBudget($id)
    {
        $budget = Budget::findOrFail($id);

        $companies     = CompanyPG::select('cpny_id', 'cpny_name')
                        ->where('status','A')->get();

        // business‑unit untuk company yg sedang diedit
        $businessUnits = BusinessUnitPG::where('cpny_id', $budget->cpny_id)
                        ->select('business_unit_id','business_unit_name')
                        ->get();

        $departements  = Dept::select('deptname')->get();
        
        $budget_detail = BudgetDetail::where('budget_id', $budget->budget_id) 
            ->get();
        $temp_id  = session('import_temp_id');
        $tempData = $temp_id ? MsBudgetTemp::where('temp_id', $temp_id)->get() : [];
        $attachment = Attachment::where('docid', $budget->budget_id)  
            ->where('status','A')         
            ->get();

        return view('pages.budgets.editbudgets', compact(
            'budget',
            'budget_detail',
            'companies',
            'businessUnits',
            'departements',
            'temp_id',
            'tempData',
            'attachment'
        ));
    }

    
    public function updateBudget(Request $request, $id)
    {
        // dd($request->all());      
        $temp_id = $request->input('temp_id'); 
        $doctype = 'BUD';
        $tempData = MsBudgetTemp::where('temp_id', $temp_id)->get();
        $tempHead = $tempData->first(); // ambil 1 record untuk akses data header
        $business_unit = BusinessUnitPG::where('business_unit_id', $tempHead->business_unit_id)->first();

        if (!$tempHead) {
            return response()->json(['message' => 'Tidak ada data budget import ditemukan!'], 422);
        }

        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $tempHead->cpny_id)
            ->where('aprvdeptid', $tempHead->department_fin_id)
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

            $budget = Budget::findOrFail($id);
                       
            $budget -> update([              
                'budget_date' => $datenow,
                'perpost' => $tempHead->perpost,
                'cpny_id' => $tempHead->cpny_id,
                'business_unit_id' => $business_unit->business_unit_name,  
                'department_fin_id' => $tempHead->department_fin_id,              
                'totalbudget' => $tempData->sum('totalbudget'),              
                'created_by' => $user->username,
                'status' => 'P'             
            ]);

            BudgetDetail::where('budget_id', $budget->budget_id)->delete();

            foreach ($tempData as $row) {
                BudgetDetail::create([     
                    'budget_id' => $budget->budget_id,             
                    'perpost' => $row->perpost,
                    'cpny_id' => $row->cpny_id,
                    'business_unit_id' => $row->business_unit_id,
                    'department_fin_id' => $row->department_fin_id,
                    'account_id' => $row->account_id,
                    'activity_id' => $row->activity_id,
                    'activity_detail' => $row->activity_detail,
                    'totalbudget' => $row->totalbudget,
                    'period01_budget' => $row->period01_budget,
                    'period02_budget' => $row->period02_budget,
                    'period03_budget' => $row->period03_budget,
                    'period04_budget' => $row->period04_budget,
                    'period05_budget' => $row->period05_budget,
                    'period06_budget' => $row->period06_budget,
                    'period07_budget' => $row->period07_budget,
                    'period08_budget' => $row->period08_budget,
                    'period09_budget' => $row->period09_budget,
                    'period10_budget' => $row->period10_budget,
                    'period11_budget' => $row->period11_budget,
                    'period12_budget' => $row->period12_budget,
                    'created_by' => $user->username,
                    'status' => 'P'
                ]);
            }

            MsBudgetTemp::where('temp_id', $temp_id)->delete();

            //read ms_approval
             $m_approval = M_approval::where('aprvdoctype', $doctype)
                ->where('aprvcpnyid', $tempHead->cpny_id)
                ->where('aprvdeptid', $tempHead->department_fin_id)
                ->where('status', 'A')
                ->get();

            //insert trx_approval
            foreach ($m_approval as $mp) {
                $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
                T_approval::create([
                    'docid' => $budget->budget_id,
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
                    $attach->docid = $budget->docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }

            $t_approval_next = T_approval::where('docid', $budget->budget_id)
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
                'url' => url('/showbudgets_') . $budget->id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Budgets');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
            }

            DB::commit();
            return response()->json(['success' => true, 'budget' => $budget]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan budget', 'message' => $e->getMessage()], 500);
        }
    }

    public function removeAttachment($id)
    {
        try {
            $attachment = Attachment::findOrFail($id);
            $attachment->update(['status' => 'X']); 

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }
    }
 

    public function showBudget($id)
    {        
        $budget = Budget::findOrFail($id);
        // $budget = Budget::with('departement.subgrading')->findOrFail($id);
        $approval = T_approval::where('docid', $budget->budget_id)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();

        $budgetdetail = BudgetDetail::where('budget_id', $budget->budget_id)           
            ->get();      
        $attachment = Attachment::where('docid', $budget->budget_id)    
            ->where('status','A')        
            ->get();      
       
        return view('pages.budgets.showbudgets', compact('budget','budgetdetail','approval','attachment'));
    }

    
    public function fetchComments($id)
    {
    
        // dd($id);
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
        $comment->doctype = 'BUD';
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

    public function approveBudget(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login
        
        $budget = Budget::where('budget_id', $docid)->first();   

        if (!$budget) {
            return response()->json(['success' => false, 'message' => 'Prf not found'], 404);
        }        

        $count_approval = T_approval::where('docid', '=', $budget->budget_id)
            ->where('status', '=', 'P')
            ->count();
       
        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $budget->budget_id)
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
            $budget->status = 'C';
            $budget->completed_by = $user->username;
            $budget->completed_at = $datestamp;
            $budget->save();           
        }

        $t_approval_next = T_approval::where('docid', $budget->budget_id)
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
                'info' => 'Budget Company ' . $budget->cpny_id . ' Department ' . $budget->department_fin_id . ' ' . $budget->perpost,                
                'url' => url('/showbudgets/') . $budget->id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Budget');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
            }
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectBudget(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $budget = Budget::where('budget_id', $docid)->first();  
        
        
        if (!$budget) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $budget->budget_id)
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

            $budget->status = 'R';
            $budget->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $budget->budget_id)
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
            'info' => 'Budget Company ' . $budget->cpny_id . ' Department ' . $budget->department_fin_id . ' ' . $budget->perpost,                 
            'url' => url('/showbudgets/') . $budget->id

        );

       
        $email_it = User::where('username', $budget->created_by)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected Budget');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
            });
        }

        $id = $budget->id;
        $doctype ='BUD';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Budget rejected successfully']);
    }

    public function reviseBudget(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $budget = Budget::where('budget_id', $docid)->first();  
        
        
        if (!$budget) {
            return response()->json(['success' => false, 'message' => 'Budget not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $budget->budget_id)
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

            $budget->status = 'D';
            $budget->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $budget->budget_id)
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
            'info' => 'Budget Company ' . $budget->cpny_id . ' Department ' . $budget->department_fin_id . ' ' . $budget->perpost,               
            'url' => url('/showbudgets/') . $budget->id

        );

       
        $email_it = User::where('username', $budget->created_by)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Revise Budget');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
            });
        }

        $id = $budget->id;
        $doctype ='BUD';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Budget revise successfully']);
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

            // $budget = Budget::where('docid', $id)          
            //     ->first();
            $budget = Budget::with(['divisionRef'])
                ->where('docid', $id)
                ->first();

                  
            $task = Jobposting::create([
                'docid' => $docid,
                'refid' => $budget->docid,
                'cpnyid' => $budget->cpnyid,
                'departementid' => $budget->departementid,
                'division_id' => optional($budget->divisionRef)->division_name,
                'date' => $datenow,
                'job_title' => $budget->job_title,
                'job_level' => $budget->job_level,                
                'immediate_superior' => $budget->immediate_superior,                
                'state_position' => $budget->state_position,
                'job_type' => $budget->job_type,
                'reason_vacancy' => $budget->reason_vacancy,
                'required' => $budget->required,
                'actual' => $budget->actual,
                'total_actual' => $budget->total_actual,       
                'education' => $budget->education,
                'experience_start' => $budget->experience_start,
                'experience_end' => $budget->experience_end,           
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
        $sites = Site::select('id', 'site')         
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
            ->select('e.id as employee_id', 'e.employee_name', 'e.employee_company', 'd.departement_id', 'd.departement_name','d.subgrade_name','d.parent_id')
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

    





}
