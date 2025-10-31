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
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Google\Cloud\Storage\StorageClient;

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
        $status = $request->query('status', 'P');

        $query = Budget::with(['businessUnit', 'departmentFin']);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $budget = $query->orderBy('id', 'desc')->get();

        // Tambahkan nama relasi langsung ke hasil
        $budget->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);

            // tampilkan nama relasi
            $row->business_unit_name = $row->businessUnit->business_unit_name ?? null;
            $row->department_name     = $row->departmentFin->department_name ?? null;

            unset($row->businessUnit, $row->departmentFin); // opsional: sembunyikan object relasi
            return $row;
        });

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
            $tempData = MsBudgetTemp::where('temp_budget_id', $temp_id)->get();
        }

       
        return view('pages.budgets.createbudgets', compact('companies','departements','tempData','temp_id'));
    }
   
    public function import_xxx(Request $request, Budget $budget = null)
    {

        
        $request->validate([
            'file'              => 'required|mimes:xlsx,xls,csv',
            'cpny_id'           => 'required',
            'business_unit_id'  => 'required',
            'department_fin_id' => 'required',
        ]);
       
        $eid = $budget ? Hashids::encode($budget->id) : null;

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
                ? redirect()->route('budget.edit', $eid)
                            ->with('success', 'Data berhasil di‑import (edit mode).')
                : redirect()->route('budget.create')
                            ->with('success', 'Data berhasil di‑import.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

   
    public function import(Request $request, $hash = null)
    {
        $request->validate([
            'file'              => 'required|mimes:xlsx,xls,csv',
            'cpny_id'           => 'required',
            'business_unit_id'  => 'required',
            'department_fin_id' => 'required',
        ]);

        // Jika edit, decode hash -> ambil Budget
        $budget = null;
        if ($hash) {
            $decoded = Hashids::decode($hash);
            $id = $decoded[0] ?? null;
            abort_if(!$id, 404, 'Invalid budget hash.');
            $budget = Budget::findOrFail($id);
        }

        try {
            $username = Auth::user()->username;
            $temp_id  = Str::uuid()->toString();

            MsBudgetTemp::where('created_by', $username)->delete();

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

            // untuk redirect cukup pakai hash yg sudah ada
            return $budget
                ? redirect()->route('budget.edit', $hash)
                        ->with('success', 'Data berhasil di-import (edit mode).')
                : redirect()->route('budget.create')
                        ->with('success', 'Data berhasil di-import.');
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
               
                   
        $temp_id = $request->input('temp_id'); 
        $doctype = 'BD';     
        $tempData = MsBudgetTemp::where('temp_budget_id', $temp_id)->get();
        $tempHead = $tempData->first(); // ambil 1 record untuk akses data header
        // $business_unit = BusinessUnitPG::where('business_unit_id', $tempHead->business_unit_id)->first();

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
            $docid = $doctype . $tglbln . sprintf("%04d", $urutan);
         
            $budget = Budget::create([
                'budget_id' => $docid,
                'budget_date' => $datenow,
                'perpost' => $tempHead->perpost,
                'cpny_id' => $tempHead->cpny_id,
                'business_unit_id' => $tempHead->business_unit_id,  
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
                    'activity_descr'     => $row->activity_descr,
                    'activity_detail'    => $row->activity_detail,
                    'qty_budget'         => $row->qty_budget,
                    'unit_price_budget'  => $row->unit_price_budget,
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

                    // ===== NEW: add (default 0)
                    'period01_budget_add'   => 0,
                    'period02_budget_add'   => 0,
                    'period03_budget_add'   => 0,
                    'period04_budget_add'   => 0,
                    'period05_budget_add'   => 0,
                    'period06_budget_add'   => 0,
                    'period07_budget_add'   => 0,
                    'period08_budget_add'   => 0,
                    'period09_budget_add'   => 0,
                    'period10_budget_add'   => 0,
                    'period11_budget_add'   => 0,
                    'period12_budget_add'   => 0,

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


            MsBudgetTemp::where('temp_budget_id', $temp_id)->delete();
           
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
            // if ($request->hasfile('attachments')) {
            //     foreach ($request->file('attachments') as $file) {
            //         $randomNumber = random_int(10000000, 99999999);
            //         $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                   
            //         $originalName = str_replace('%', '', $file->getClientOriginalName());
            //         $ext        = $file->getClientOriginalExtension();
            //         $attachfile = md5($randomNumber) . '.' . $ext;

            //         //attach to folder
            //         $folder_attach = public_path() . '/attachments/'.$year;
            //         $config['upload_path'] = $folder_attach;                   
            //         if(!is_dir($folder_attach))
            //         {
            //             mkdir($folder_attach, 0777);
            //         }
                    
            //         $folder_upload = $folder_attach;
            //         // $folder_upload = public_path() . '/attachments';
            //         $file->move($folder_upload, $attachfile);

            //         //insert to table attachments
            //         $attach = new Attachment();
            //         $attach->docid = $docid;
            //         $attach->name = $filename;
            //         $attach->attachfile = $attachfile;
            //         $attach->status = 'A';
            //         $attach->extention = $file->getClientOriginalExtension();
            //         $attach->created_user = $user->username;
            //         $attach->save();
            //     }
            // }            
             

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $docid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $tempHead->cpny_id,
                    'departementid' => $tempHead->department_fin_id,                    
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $user->username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                    // tidak return di sini!
                } catch (\Throwable $e) {
                    \DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create PB',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

            $t_approval_next = T_approval::where('docid', $docid)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();

            $status = 'P'; // 'P' | 'R' | 'D' | 'A' | 'C'
            
            $subjectMap = [
                'P' => 'Waiting Approval',
                'R' => 'Rejected Approval',
                'D' => 'Revise Approval',
                'A' => 'Approved',
                'C' => 'Completed',
            ];
            $subjectSuffix = $subjectMap[$status] ?? 'Notification';

            // $id = $budget->id;
            $eid = Hashids::encode($budget->id);
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->name,    
                'createdby'=> $budget->created_by,   
                'docname'  => 'Budget',
                'info' => 'Budget Company ' . $tempHead->cpny_id . ' Department ' . $tempHead->department_fin_id . ' ' . $tempHead->perpost,     
                'status'   => $status, 
                'url' => url('/showbudgets/' . $eid)
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Budgets');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }       

            DB::commit();
            return response()->json(['success' => true, 'budget' => $budget]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan budget', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function editBudget($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

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
        $tempData = $temp_id ? MsBudgetTemp::where('temp_budget_id', $temp_id)->get() : [];
        // $attachment = Attachment::where('docid', $budget->budget_id)  
        //     ->where('status','A')         
        //     ->get();

         $rows = TrAttachment::where('refnbr', $budget->budget_id)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config      = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $object     = $bucket->object($objectPath);
            $signedUrl  = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }
            return (object) [
                'id'          => $r->id,
                'display_name' => $r->attachment_name,
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

        return view('pages.budgets.editbudgets', compact(
            'budget',
            'budget_detail',
            'companies',
            'businessUnits',
            'departements',
            'temp_id',
            'tempData',
            'attachments',
            'hash'
        ));
    }

    public function updateBudget(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'BD tidak ditemukan.');

        $doctype  = 'BD';
        $user     = $request->user();
        $now      = Carbon::now();
        $datenow  = $now->toDateString();
        $datestamp= $now->toDateTimeString();

        // ambil budget yang mau diupdate
        $budget = Budget::findOrFail($id);

        // ambil temp (opsional)
        $temp_id  = $request->input('temp_id') ?: session('import_temp_id');
        $tempData = $temp_id ? MsBudgetTemp::where('temp_budget_id', $temp_id)->get() : collect();
        $useTemp  = $tempData->isNotEmpty();
        $tempHead = $useTemp ? $tempData->first() : null;

        // header yang dipakai untuk approval/meta
        // - jika ada import → ambil dari temp
        // - jika tidak ada import → pakai request (kalau dikirim), atau fallback ke nilai lama
        $cpnyId   = $useTemp ? $tempHead->cpny_id           : ($request->input('cpny_id')           ?? $budget->cpny_id);
        $deptId   = $useTemp ? $tempHead->department_fin_id : ($request->input('department_fin_id') ?? $budget->department_fin_id);
        $buId     = $useTemp ? $tempHead->business_unit_id  : ($request->input('business_unit_id')  ?? $budget->business_unit_id);
        // Perpost: form-mu tidak ada input perpost; kalau tanpa import, biarkan tetap.
        $perpost  = $useTemp ? $tempHead->perpost : $budget->perpost;

        // validasi approval line terhadap header yang dipakai
        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $cpnyId)
            ->where('aprvdeptid', $deptId)
            ->where('aprvdoctype', $doctype)
            ->count();

        if ($count_approval === 0) {
            return response()->json([
                'message' => 'Approval line belum di-setup, Please contact IT!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // totalbudget:
            // - dengan import → sum dari temp
            // - tanpa import  → sum dari detail existing
            $totalBudget = $useTemp
                ? $tempData->sum('totalbudget')
                : BudgetDetail::where('budget_id', $budget->budget_id)->sum('totalbudget');

            // update header
            $budget->update([
                'budget_date'       => $datenow,
                'perpost'           => $perpost,
                'cpny_id'           => $cpnyId,
                'business_unit_id'  => $buId,               // simpan ID; kalau butuh nama, ambil terpisah
                'department_fin_id' => $deptId,
                'totalbudget'       => $totalBudget,
                'status'            => 'P',
                'updated_by'        => $user->username,     // jaga created_by tetap milik pembuat awal
            ]);

            // kalau ada import → replace detail
            if ($useTemp) {
                BudgetDetail::where('budget_id', $budget->budget_id)->delete();

                foreach ($tempData as $row) {
                    BudgetDetail::create([
                        'budget_id'          => $budget->budget_id,
                        'perpost'            => $row->perpost,
                        'cpny_id'            => $row->cpny_id,
                        'business_unit_id'   => $row->business_unit_id,
                        'department_fin_id'  => $row->department_fin_id,
                        'account_id'         => $row->account_id,
                        'activity_id'        => $row->activity_id,
                        'activity_descr'     => $row->activity_descr,
                        'activity_detail'    => $row->activity_detail,
                        'qty_budget'         => $row->qty_budget,
                        'unit_price_budget'  => $row->unit_price_budget,
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
                        'created_by'         => $user->username,
                        'status'             => 'P',
                    ]);
                }

                // bersihkan temp bila sudah dipakai
                MsBudgetTemp::where('temp_budget_id', $temp_id)->delete();
            }

            // susun ulang approval (kalau memang prosesnya butuh dibuat ulang setiap submit)
            $m_approval = M_approval::where('aprvdoctype', $doctype)
                ->where('aprvcpnyid', $cpnyId)
                ->where('aprvdeptid', $deptId)
                ->where('status', 'A')
                ->get();

            // hapus draft t_approval lama untuk doc ini (opsional, jika memang logikanya begitu)
            // T_approval::where('docid', $budget->budget_id)->delete();

            foreach ($m_approval as $mp) {
                $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null;
                T_approval::create([
                    'docid'          => $budget->budget_id,
                    'aprvid'         => $mp->aprvid,
                    'aprvdoctype'    => $mp->aprvdoctype,
                    'aprvcpnyid'     => $mp->aprvcpnyid,
                    'aprvdeptid'     => $mp->aprvdeptid,
                    'aprvusername'   => $mp->aprvusername,
                    'name'           => $mp->name,
                    'aprvdatebefore' => $aprvdatebefore,
                    'aprvtotalday'   => 1,
                    'status'         => 'P',
                    'created_user'   => $user->username,
                ]);
            }

            // attachments tetap bisa diupload baik ada import maupun tidak
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $budget->budget_id,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyId,
                    'departementid' => $deptId,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $user->username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to update BD',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // cari approver berikutnya (jika ada) untuk notifikasi
            $t_approval_next = T_approval::where('docid', $budget->budget_id)
                ->where('status', 'P')
                ->orderBy('aprvid', 'ASC')
                ->first();

            if ($t_approval_next) {
                $eid  = Hashids::encode($budget->id);
                $data = [
                    'docid'     => $t_approval_next->docid,
                    'cpnyid'    => $t_approval_next->aprvcpnyid,
                    'deptname'  => $t_approval_next->aprvdeptid,
                    'date'      => $t_approval_next->aprvdatebefore,
                    'name'      => $t_approval_next->name,
                    'createdby' => $budget->created_by,
                    'docname'   => 'Budget',
                    'info'      => 'Budget Company '.$cpnyId.' Department '.$deptId.' '.$perpost,
                    'status'    => 'P',
                    'url'       => url('/showbudgets/'.$eid),
                ];

                $multiapp = explode(',', $t_approval_next->aprvusername);
                $email_it = User::whereIn('username', $multiapp)
                    ->where('status', 'A')
                    ->get();

                foreach ($email_it as $emailsit) {
                    Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $emailsit) {
                        $message->to($emailsit->test_email)->subject($data['docid'].' - Waiting Approval Budgets');
                        $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'budget' => $budget]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan budget',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    
    public function updateBudget_xxx(Request $request, $hash)
    {
        // dd($request->all());      

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'BD tidak ditemukan.');

        $temp_id = $request->input('temp_id'); 
        $doctype = 'BD';
        $tempData = MsBudgetTemp::where('temp_budget_id', $temp_id)->get();
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
                    'activity_descr' => $row->activity_descr,
                    'activity_detail' => $row->activity_detail,
                    'qty_budget'      => $row->qty_budget,
                    'unit_price_budget' => $row->unit_price_budget,
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

            MsBudgetTemp::where('temp_budget_id', $temp_id)->delete();

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
            // if ($request->hasfile('attachments')) {
            //     foreach ($request->file('attachments') as $file) {
            //         $randomNumber = random_int(10000000, 99999999);
            //         $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                   
            //         $originalName = str_replace('%', '', $file->getClientOriginalName());
            //         $ext        = $file->getClientOriginalExtension();
            //         $attachfile = md5($randomNumber) . '.' . $ext;

            //         //attach to folder
            //         $folder_attach = public_path() . '/attachments/'.$year;
            //         $config['upload_path'] = $folder_attach;                   
            //         if(!is_dir($folder_attach))
            //         {
            //             mkdir($folder_attach, 0777);
            //         }
                    
            //         $folder_upload = $folder_attach;
            //         // $folder_upload = public_path() . '/attachments';
            //         $file->move($folder_upload, $attachfile);

            //         //insert to table attachments
            //         $attach = new Attachment();
            //         $attach->docid = $budget->docid;
            //         $attach->name = $filename;
            //         $attach->attachfile = $attachfile;
            //         $attach->status = 'A';
            //         $attach->extention = $file->getClientOriginalExtension();
            //         $attach->created_user = $user->username;
            //         $attach->save();
            //     }
            // }

            
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $budget->budget_id,
                    'doctype'       => $doctype,
                    'cpnyid'        => $tempHead->cpny_id,
                    'departementid' => $tempHead->department_fin_id,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $user->username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to update BD',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            $t_approval_next = T_approval::where('docid', $budget->budget_id)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();

            $status = 'P'; 
            
            $subjectMap = [
                'P' => 'Waiting Approval',
                'R' => 'Rejected Approval',
                'D' => 'Revise Approval',
                'A' => 'Approved',
                'C' => 'Completed',
            ];
            $subjectSuffix = $subjectMap[$status] ?? 'Notification';

            $eid = Hashids::encode($budget->id);
           
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->name,    
                'createdby'=> $budget->created_by,   
                'docname'  => 'Budget',
                'info' => 'Budget Company ' . $tempHead->cpny_id . ' Department ' . $tempHead->department_fin_id . ' ' . $tempHead->perpost,     
                'status'   => $status,             
                'url' => url('/showbudgets/' . $eid)                 
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Budgets');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
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
            $attachment = TrAttachment::findOrFail($id);
            $attachment->update(['status' => 'X']); 

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }
    }
 

    public function showBudget($hash)
    {        
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        // $budget = Budget::findOrFail($id);
        $budget = Budget::with([
            'businessUnit',
            'departmentFin',
            'creator'
        ])->findOrFail($id);


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
       
        return view('pages.budgets.showbudgets', compact('budget','budgetdetail','approval','attachment','hash'));
    }

    
    // public function fetchComments($id)
    // {
    
    //     // dd($id);
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
    //     $comment->doctype = 'BD';
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

    public function approveBudget(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // eager load creator jika ada relasinya
        $budget = Budget::with('creator')   // pastikan relasi creator() ada, atau ganti sesuai relasi Anda
            ->where('budget_id', $docid)
            ->first();

        if (!$budget) {
            return response()->json(['success' => false, 'message' => 'Budget not found'], 404);
        }

        // ambil nama lengkap pembuat dokumen (fallback ke created_by)
        $fullname = data_get($budget, 'creator.name') ?: ($budget->created_by ?? '');

        // pastikan user adalah approver aktif (status P) di doc ini
        $tApproval = T_approval::where('docid', $budget->budget_id)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%{$user->username}%")
            ->whereNotNull('aprvdatebefore') 
            ->orderBy('aprvid', 'ASC')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
        }

        DB::beginTransaction();
        try {
            // Set current approver -> Approved
            $tApproval->status         = 'A';
            $tApproval->aprvdateafter  = $now;
            $tApproval->aprvusername   = $user->username;
            $tApproval->name           = $user->name;
            $tApproval->save();

            // Update header informasi "terakhir diproses"
            $budget->completed_by = $user->username;
            $budget->completed_at = $now;
            $budget->save();

            // Hitung sisa pending setelah approve ini
            $pendingCount = T_approval::where('docid', $budget->budget_id)
                ->where('status', 'P')
                ->count();

            // Pemetaan judul sesuai status
            $subjectMap = [
                'P' => 'Waiting Approval',
                'R' => 'Rejected Approval',
                'D' => 'Revise Approval',
                'A' => 'Approved',
                'C' => 'Completed',
            ];

            if ($pendingCount === 0) {
                // Tidak ada approver lagi -> dokumen complete
                $budget->status       = 'C';
                $budget->completed_by = $user->username;
                $budget->completed_at = $now;
                $budget->save();

                // Close semua detail
                $details = BudgetDetail::where('budget_id', $budget->budget_id)->get();
                foreach ($details as $d) {
                    $d->status = 'C';
                    $d->save();
                }

                // Email ke requester (creator)
                $status        = 'C';
                $subjectSuffix = $subjectMap[$status] ?? 'Notification';

                $eid = Hashids::encode($budget->id);

                $data = [
                    'docid'     => $budget->budget_id,
                    'cpnyid'    => $budget->cpny_id ?? '',
                    'deptname'  => $budget->department_fin_id ?? '',
                    'date'      => $budget->perpost ?? $now,
                    'fullname'  => $fullname,  // nama penerima di email
                    'name'      => $fullname,  // fallback
                    'createdby' => $fullname,
                    'docname'   => 'Budget',
                    'info'      => 'Budget Company ' . ($budget->cpny_id ?? '') . ' Department ' . ($budget->department_fin_id ?? '') . ' ' . ($budget->perpost ?? ''),
                    'status'    => $status,
                    'url'       => url('/showbudgets/' . $eid),
                ];

                // kirim ke pembuat dokumen
                $recipients = User::where('username', $budget->created_by ?? '')
                    ->where('status', 'A')
                    ->get();

                foreach ($recipients as $rcp) {
                    try {
                        $to = $rcp->test_email ?? $rcp->email;
                        if ($to) {
                            Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                                $message->to($to)
                                    ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Budget')
                                    ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                            });
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed sending Budget completion email', ['docid' => $budget->budget_id, 'error' => $e->getMessage()]);
                    }
                }
            } else {
                // Masih ada approver berikutnya -> cari level berikutnya
                $next = T_approval::where('docid', $budget->budget_id)
                    ->where('status', 'P')
                    ->orderBy('aprvid', 'ASC')
                    ->first();

                if ($next) {
                    // Stempel "datebefore" untuk approver berikutnya
                    $next->aprvdatebefore = $now;
                    $next->save();

                    // Email ke approver berikutnya
                    $status        = 'P';
                    $subjectSuffix = $subjectMap[$status] ?? 'Notification';

                    $data = [
                        'docid'     => $next->docid,
                        'cpnyid'    => $next->aprvcpnyid,
                        'deptname'  => $next->aprvdeptid,
                        'date'      => $next->aprvdatebefore,
                        'fullname'  => $next->name,
                        'name'      => $next->name,
                        'createdby' => $budget->created_by ?? '',
                        'docname'   => 'Budget',
                        'info'      => 'Budget Company ' . ($budget->cpny_id ?? '') . ' Department ' . ($budget->department_fin_id ?? '') . ' ' . ($budget->perpost ?? ''),
                        'status'    => $status,
                        'url'       => url('/showbudgets/' . $budget->id),
                    ];

                    $usernames = array_filter(array_map('trim', explode(',', (string) $next->aprvusername)));
                    if (!empty($usernames)) {
                        $recipients = User::whereIn('username', $usernames)
                            ->where('status', 'A')
                            ->get();

                        foreach ($recipients as $rcp) {
                            try {
                                $to = $rcp->test_email ?? $rcp->email;
                                if ($to) {
                                    Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                                        $message->to($to)
                                            ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Budget')
                                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                                    });
                                }
                            } catch (\Throwable $e) {
                                Log::error('Failed sending Budget waiting-approval email', ['docid' => $budget->budget_id, 'error' => $e->getMessage()]);
                            }
                        }
                    } else {
                        Log::warning('Next approver has empty aprvusername list', ['docid' => $budget->budget_id]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Task approved successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Approve Budget failed', ['docid' => $budget->budget_id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
        }
    }


    public function rejectBudget(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        // $budget = Budget::where('budget_id', $docid)->first();  
        $budget = Budget::with('creator')
            ->where('budget_id', $docid)
            ->first();
        $fullname = data_get($budget, 'creator.name') ?: $budget->created_by;

        
        if (!$budget) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $budget->budget_id)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->whereNotNull('aprvdatebefore') 
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

        $status = 'R'; // Rejected
        $subjectMap = [
            'P' => 'Waiting Approval',
            'R' => 'Rejected Approval',
            'D' => 'Revise Approval',
            'A' => 'Approved',
            'C' => 'Completed',
        ];
        $subjectSuffix = $subjectMap[$status] ?? 'Notification';

        $eid = Hashids::encode($budget->id);

        //send email 
        $data = array(
            'docid' => $t_approval->docid,
            'cpnyid' => $t_approval->aprvcpnyid,
            'deptname' => $t_approval->aprvdeptid,           
            'date' => $t_approval->aprvdatebefore,
            'fullname'  => $fullname,               
            'name'      => $fullname,               
            'createdby' => $fullname,
            'docname'   => 'Budget',
            'status'    => $status,
            'info' => 'Budget Company ' . $budget->cpny_id . ' Department ' . $budget->department_fin_id . ' ' . $budget->perpost,                 
            'url' => url('/showbudgets/' . $eid)

        );

       
        $email_it = User::where('username', $budget->created_by)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected Budget');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        $id = $budget->id;
        $doctype ='BD';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Budget rejected successfully']);
    }

    public function reviseBudget(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        // $budget = Budget::where('budget_id', $docid)->first();  
        $budget = Budget::with('creator')
            ->where('budget_id', $docid)
            ->first();
        $fullname = data_get($budget, 'creator.name') ?: $budget->created_by;
        
        
        if (!$budget) {
            return response()->json(['success' => false, 'message' => 'Budget not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $budget->budget_id)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->whereNotNull('aprvdatebefore') 
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

        $status = 'D'; // Revise
        $subjectMap = [
            'P' => 'Waiting Approval',
            'R' => 'Rejected Approval',
            'D' => 'Revise Approval',
            'A' => 'Approved',
            'C' => 'Completed',
        ];
        $subjectSuffix = $subjectMap[$status] ?? 'Notification';

        $eid = Hashids::encode($budget->id);

        //send email 
        $data = array(
            'docid' => $t_approval->docid,
            'cpnyid' => $t_approval->aprvcpnyid,
            'deptname' => $t_approval->aprvdeptid,           
            'date' => $t_approval->aprvdatebefore,
            'fullname'  => $fullname,             
            'name'      => $fullname,             
            'createdby' => $fullname,
            'docname'   => 'Budget',
            'status'    => $status,
            'info' => 'Budget Company ' . $budget->cpny_id . ' Department ' . $budget->department_fin_id . ' ' . $budget->perpost,               
            'url' => url('/showbudgets/' . $eid)

        );

       
        $email_it = User::where('username', $budget->created_by)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Revise Budget');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        $id = $budget->id;
        $doctype ='BD';
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

   
    public function getSitesByCompany($cpnyid)
    {
        // $sites = Site::where('cpnyid', $cpnyid)
        //     ->select('id', 'site')         
        //     ->get();
        $sites = Site::select('id', 'site')         
            ->get();

        return response()->json($sites);
    }

    public function printBudget($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil BDGET + relasi yang dibutuhkan
       $budget = Budget::findOrFail($id);

        // Detail baris BDGET
        $budgetdetail = BudgetDetail::where('budget_id', $budget->budget_id)           
            ->get();

        // Approval list (non-cancelled)
        $approval = T_approval::where('docid', $budget->budget_id)
            ->where('status', '<>', 'X')
            ->orderBy('aprvid')
            ->orderBy('created_at')
            ->get();

        $approve_count = $approval->count();

        // Company (handle null)
        $company = Company::where('cpnyid', $budget->cpny_id)->first();

        // Mapping status dokumen
        switch ($budget->status) {
            case 'R':
                $status_doc = 'Rejected';
                break;
            case 'C':
                $status_doc = 'Completed';
                break;
            case 'D':
                $status_doc = 'Hold';
                break;
            case 'X':
                $status_doc = 'Cancel';
                break;
            default:
                $status_doc = 'On Progress';
                break;
        }

        $data = [
            'title'               => 'Budget Report',
            'doc_type'            => 'BDGET',
            'docid'               => $budget->budget_id,
            'department_id'       => $budget->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $budget->created_by,
            'created_by_name'     => ucwords(strtolower(optional($budget->creator)->name)),
            'created_at_fmt'      => optional($budget->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($budget->created_at)->format('d M Y H:i'),
            'budgetdate'            => \Carbon\Carbon::parse($budget->budgetdate)->format('d F Y'),
            // konten
            'keperluan'           => $budget->perpost,
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($budget->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.budgets.pdf_budgets',
            array_merge($data, [
                'detail'         => $budgetdetail,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_budgets_{$budget->budget_id}.pdf");
    }



   



    

   

    





}
