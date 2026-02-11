<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Personnel;
use App\Models\Autonbr;
use App\Models\MsCompany;
use App\Models\MsDepartment;
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
use Vinkla\Hashids\Facades\Hashids;
use App\Models\MsApproval;
use App\Models\TrApproval;
use App\Models\TrMessage;
use Google\Cloud\Storage\StorageClient;
use App\Models\TrAttachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mail;
use App\Models\SysUserRole;
use App\Models\DepartmentHR;
use App\Models\Userdivision;
use App\Http\Controllers\Traits\HasAutonbr;


class PersonnelController extends Controller
{
    use HasAutonbr;

    private function splitCsv(?string $value): array
    {
        if (!$value) return [];
        return collect(explode(',', $value))
            ->map(fn($x) => trim((string)$x))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function userCpnyIds($user): array
    {
        return $this->splitCsv($user->cpny_id);
    }

    private function userDeptIds($user): array
    {
        return $this->splitCsv($user->department_id);
    }

    private function userDivisionIds($user): array
    {
        return $this->splitCsv($user->division_id);
    }

    private function hasRoleAllDept($user): bool
    {
        return SysUserRole::query()
            ->where('username', $user->username)
            ->where('role_id', 'RECACCALLDEPT')
            ->where(function ($q) {
                // kalau sys_user_role tidak pakai status, boleh hapus blok ini
                $q->whereNull('status')->orWhere('status', 'A');
            })
            ->exists();
    }

    private function personnelScopeForUser($user)
    {
        $cpnyIds = $this->userCpnyIds($user);

        $q = Personnel::query();

        // wajib punya cpny
        if (empty($cpnyIds)) return $q->whereRaw('1=0');

        // filter cpnyid user (AW,EP,PSA,GPS)
        $q->whereIn('cpnyid', $cpnyIds);

        // role all dept -> bisa lihat semua division
        if ($this->hasRoleAllDept($user)) {
            return $q;
        }

        // selain itu: filter division_id dari user (langsung)
        $divisionIds = $this->userDivisionIds($user);
        if (empty($divisionIds)) return $q->whereRaw('1=0');

        return $q->whereIn('division_id', $divisionIds);
    }

    private function userDivisionIds_xxx($user): array
    {
        $deptIds = $this->userDeptIds($user);
        if (empty($deptIds)) return [];

        // 1) Utama: mapping dari ms_department (pgsql2) -> department_hr_id kamu isi division_id
        $divisions = MsDepartment::query()
            ->whereIn('department_id', $deptIds)
            ->whereNotNull('department_hr_id')
            ->pluck('department_hr_id')
            ->map(fn($x) => trim((string)$x))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($divisions)) return $divisions;

        // 2) Fallback: dari hr_ms_department (mysql3)
        $divisions2 = DepartmentHR::query()
            ->whereIn('department_id', $deptIds)
            ->whereNotNull('division_id')
            ->pluck('division_id')
            ->map(fn($x) => trim((string)$x))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $divisions2;
    }

    private function personnelScopeForUser_xxx($user)
    {
        $cpnyIds = $this->userCpnyIds($user);

        $q = Personnel::query();

        // wajib punya cpny
        if (empty($cpnyIds)) return $q->whereRaw('1=0');

        // filter cpnyid user (AW,EP,PSA,GPS)
        $q->whereIn('cpnyid', $cpnyIds);

        // role all dept -> bisa lihat semua division
        if ($this->hasRoleAllDept($user)) {
            return $q;
        }

        // selain itu: filter division_id
        $divisionIds = $this->userDivisionIds($user);
        if (empty($divisionIds)) return $q->whereRaw('1=0');

        return $q->whereIn('division_id', $divisionIds);
    }

    public function index()
    {
        $user = Auth::user();
       
        if (!$user) {
            return redirect()->route('login');
        }

        $base = $this->personnelScopeForUser($user);

        $counts = (clone $base)->selectRaw("
            COUNT(*) AS all,
            SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) AS on_progress,
            SUM(CASE WHEN status = 'R' THEN 1 ELSE 0 END) AS reject,
            SUM(CASE WHEN status = 'D' THEN 1 ELSE 0 END) AS revise,
            SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) AS completed
        ")->first();

        return view('pages.personnels.personnels', [
            'all'        => (int) ($counts->all ?? 0),
            'onProgress' => (int) ($counts->on_progress ?? 0),
            'reject'     => (int) ($counts->reject ?? 0),
            'revise'     => (int) ($counts->revise ?? 0),
            'completed'  => (int) ($counts->completed ?? 0),
        ]);
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        $status = $request->has('status') ? $request->query('status') : 'P';
        $status = is_string($status) ? trim($status) : $status;

        $query = $this->personnelScopeForUser($user);

        if ($status !== null && $status !== '' && strtolower((string)$status) !== 'all') {
            $query->where('status', $status);
        }

        // $rows = $query->orderByDesc('id')->get();
        $rows = $query
            ->orderByDesc('created_at')
            ->orderByDesc('docid')
            ->get();


        $personnel = $rows->map(function ($row) {
            return [
                'eid'            => Hashids::encode($row->id),
                'docid'          => $row->docid,
                'date'           => $row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : null,
                'cpnyid'         => $row->cpnyid,
                'departementid'  => $row->departementid,
                'division_id'    => $row->division_id,
                'job_title'      => $row->job_title,
                'job_level'      => $row->job_level,
                'created_user'   => $row->created_user,
                'status'         => $row->status,
            ];
        });

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
        $companies = MsCompany::select('cpny_id')->get();       
        $skillTags = MJobtag::select('id', 'job_tags')->get(); 
        $division = Division::select('division_id','division_name')
            ->where('status', 'A')
            ->get();

        $subgradings = StoSubGrading::select('subgrade_id','subgrade_name','group_grade')
            ->where('status', 'A')
            ->orderBy('grade_id', 'ASC')
            ->get();

        // 1) ambil division_id user dari PostgreSQL
        $userDivisionIds = Userdivision::query()
            ->where('username', $user->username)
            ->where('status', 'A')
            ->pluck('division_id')
            ->unique()
            ->values()
            ->toArray();

        // 2) ambil master division dari MySQL berdasarkan list id di atas
        $userdivison = Division::query()
            ->select('division_id', 'division_name')
            ->where('status', 'A')
            ->whereIn('division_id', $userDivisionIds)
            ->orderBy('division_name')
            ->get();

       
        return view('pages.personnels.createpersonnels', compact('companies','usercpny','usercpny2','userdept','userdept2','skillTags','division','subgradings','userdivison'));
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
        $companies = MsCompany::select('cpny_id')->get();
        $departements = MsDepartment::select('department_id')->get();
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

        $positionCondition = strtolower($request->job_type . ' ' . $request->group_grade);
        $doctype = 'PRF';           
        $user     = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';   
        $dt        = \Carbon\Carbon::now();
        $year      = (int) $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();
        $datenow   = Carbon::now()->format('Y-m-d');

        // cek availability approval line (Normal atau Condition yg cocok)
        $count_approval = MsApproval::where('status', 'A')
            ->where('aprv_cpnyid', $request->cpnyid)
            ->where('aprv_departementid', $request->departementid)
            ->where('aprv_doctype', $doctype)
            ->where(function($q) use ($positionCondition) {
                $q->where('aprv_type', 'Normal')
                ->orWhere(function($q2) use ($positionCondition) {
                    $q2->where('aprv_type', 'Condition')
                        ->where('aprv_condition', $positionCondition);
                });
            })
            ->count();
           
        if ($count_approval === 0) {
            return response()->json([
                'message' => 'Approval line belum di-setup untuk kombinasi ini (Normal/Condition). Please contact IT!'
            ], 422);
        }

        DB::beginTransaction();
        try {           

            // // Generate task ID
            // $autonbr = Autonbr::lockForUpdate()
            //     ->where('doctype', $doctype)
            //     ->where('year', $year)
            //     ->where('month', $month)
            //     ->where('status', 'A')
            //     ->first();

            // if (!$autonbr) {
            //     $autonbr = Autonbr::create([
            //         'doctype' => $doctype,
            //         'year' => $year,
            //         'month' => $month,
            //         'status' => 'A',
            //         'number' => 1
            //     ]);
            //     $urutan = 1;
            // } else {
            //     $urutan = $autonbr->number + 1;
            //     $autonbr->number = $urutan;
            //     $autonbr->save();
            // }

            // $tglbln = substr($year, 2) . $month;
            // $docid = $doctype . $tglbln . sprintf("%03d", $urutan);      
            
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'PRF'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string)$year, 2) . $month;   // YYMM
            $docid  = $doctype . $tglbln . sprintf("%03d", $urutan);  
          
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
          
          
            $msApprovalLines = MsApproval::where('status', 'A')
                ->where('aprv_cpnyid', $request->cpnyid)
                ->where('aprv_departementid', $request->departementid)
                ->where('aprv_doctype', $doctype)
                ->where(function($q) use ($positionCondition) {
                    $q->where('aprv_type', 'Normal')
                    ->orWhere(function($q2) use ($positionCondition) {
                        $q2->where('aprv_type', 'Condition')
                            ->where('aprv_condition', $positionCondition);
                    });
                })
                ->orderBy('aprv_leveling', 'ASC')
                ->get();

                // insert tr_approval
                foreach ($msApprovalLines as $line) {
                    $isFirstLevel = ((int)$line->aprv_leveling === 1);

                    TrApproval::create([
                        'refnbr'             => $docid,
                        'aprv_leveling'      => $line->aprv_leveling,
                        'aprv_doctype'       => $line->aprv_doctype,
                        'aprv_cpnyid'        => $line->aprv_cpnyid,
                        'aprv_departementid' => $line->aprv_departementid,
                        'aprv_username'      => $line->aprv_username,   // bisa comma-separated
                        'aprv_name'          => $line->aprv_name,
                        'aprv_datebefore'    => $isFirstLevel ? $datestamp : null,
                        'aprv_dateafter'     => null,
                        'aprv_type'          => $line->aprv_type,       // Normal / Condition
                        'aprv_condition'     => $line->aprv_condition,  // null / Staff / Manager
                        'aprv_start_nominal' => $line->aprv_start_nominal,
                        'aprv_end_nominal'   => $line->aprv_end_nominal,
                        'status'             => 'P',                    // Pending
                        'created_by'         => $user->username,
                        'updated_by'         => null,
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
           // === Upload attachments ke GCS & simpan ke tr_attachment ===
            if ($request->hasFile('attachments')) {
                $ymFolder = 'att-job-career/' .strtolower($doctype) . '/' . $year;

                // init GCS
                $config  = config('filesystems.disks.gcs');
                $storage = new StorageClient([
                    'projectId'   => $config['project_id'],
                    'keyFilePath' => $config['key_file'],
                ]);
                $bucket = $storage->bucket($config['bucket']);

                foreach ($request->file('attachments') as $file) {
                    if (!$file->isValid()) {
                        Log::warning('Attachment invalid', ['name' => $file->getClientOriginalName()]);
                        continue;
                    }

                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $nameOnly     = pathinfo($originalName, PATHINFO_FILENAME); // untuk attachment_name
                    $ext          = $file->getClientOriginalExtension();
                    $sizeBytes    = $file->getSize();

                    $randomPrefix = md5(random_int(1, 99999999));
                    $filename     = $randomPrefix . '.' . $ext;       // nama file di bucket
                    $gcsPath      = "{$ymFolder}/{$filename}";

                    try {
                        // upload ke GCS (private)
                        $bucket->upload(
                            fopen($file->getPathname(), 'r'),
                            [
                                'name'          => $gcsPath,
                                'predefinedAcl' => 'private',
                                'metadata'      => ['contentType' => $file->getMimeType()],
                            ]
                        );

                        Log::info('Attachment uploaded to GCS', ['docid' => $docid, 'path' => $gcsPath]);

                        // simpan metadata ke tr_attachment (pgsql2)
                        TrAttachment::create([
                            'refnbr'          => $docid,
                            'doctype'         => $doctype,
                            'attachment_date' => $datestamp,                  // Carbon::now()->toDateTimeString()
                            'cpnyid'          => $request->cpnyid,
                            'departementid'   => $request->departementid,
                            'attachment_name' => $nameOnly,                    // nama file tanpa ekstensi (asli)
                            'folder'          => $ymFolder,                    // folder di bucket
                            'filename'        => $filename,                    // nama file di bucket
                            'filesize'        => $sizeBytes,                   // byte
                            'extention'       => $ext,
                            'status'          => 'A',
                            'created_by'      => $user->username,
                            'updated_by'      => null,
                        ]);

                    } catch (\Throwable $e) {
                        Log::error('Gagal upload attachment ke GCS', [
                            'docid' => $docid,
                            'file'  => $originalName,
                            'err'   => $e->getMessage(),
                        ]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal upload lampiran: ' . $e->getMessage(),
                        ], 500);
                    }
                }
            }
                      
            $t_approval_next = TrApproval::where('refnbr', $docid)
                ->where('status', 'P')
                ->orderBy('aprv_leveling', 'ASC')
                ->first();

            $eid = Hashids::encode($task->id);

            $data = [
                'docid'   => $t_approval_next->refnbr,
                'cpnyid'  => $t_approval_next->aprv_cpnyid,
                'deptname'=> $t_approval_next->aprv_departementid,
                'date'    => $t_approval_next->aprv_datebefore,
                'name'    => $user->username, // atau $t_approval_next->created_by sesuai kebutuhan
                'info'    => $request->job_title,
                'url'     => url('/showvpersonels/'.$eid),
            ];

            // kirim email ke semua approver di level ini (bisa multi username)
            $multiapp = array_map('trim', explode(',', (string)$t_approval_next->aprv_username));

            $email_it = User::whereIn('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->notification_email)
                            ->subject($data['docid'].' - Waiting Approval Personnels');
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

    public function editPersonnel($hash)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $personnel = Personnel::findOrFail($id);

        $usercpny  = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept  = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $companies = MsCompany::select('cpny_id')->get();
        $skillTags = MJobtag::select('id', 'job_tags')->get();

        // subgradings sama seperti create (ambil group_grade)
        $subgradings = StoSubGrading::select('subgrade_id','subgrade_name','group_grade')
            ->where('status', 'A')
            ->orderBy('grade_id', 'ASC')
            ->get();

        // division user-based sama seperti create
        $userDivisionIds = Userdivision::query()
            ->where('username', $user->username)
            ->where('status', 'A')
            ->pluck('division_id')
            ->unique()
            ->values()
            ->toArray();

        $division = Division::query()
            ->select('division_id', 'division_name')
            ->where('status', 'A')
            ->whereIn('division_id', $userDivisionIds)
            ->orderBy('division_name')
            ->get();

        $attachment = TrAttachment::where('refnbr', $personnel->docid)
            ->where('status','A')
            ->orderByDesc('attachment_date')
            ->get(['id','attachment_name','filename','folder','extention','created_by','attachment_date']);

        $jobres = JobResponsiblities::where('docid', $personnel->docid)->get();
        $jobqua = JobQualification::where('docid', $personnel->docid)->get();

        $selectedTags = TrJobtag::where('docid', $personnel->docid)
            ->pluck('job_tags')
            ->toArray();

        return view('pages.personnels.editpersonnels', compact(
            'companies','usercpny','usercpny2','userdept','userdept2',
            'skillTags','division','personnel','attachment','subgradings',
            'jobres','jobqua','selectedTags','hash'
        ));
    }



    public function editPersonnel_xxx($hash) 
    {
        $user = Auth::user();       
        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $personnel = Personnel::findOrFail($id);
        $user = request()->user();

        $usercpny  = Usercpny::where('username', $user->username)->get();
        // dd($usercpny);
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept  = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();
        $companies = MsCompany::select('cpny_id')->get();
        $departements = MsDepartment::select('department_id')->get();
        // $joblevel = JobLevel::select('title_level')->get();
        $skillTags = MJobtag::select('id', 'job_tags')->get(); 
        $division  = Division::select('division_id','division_name')->get();

        // ⬇⬇ GANTI: Attachment -> TrAttachment (pakai refnbr & kolom baru)
        $attachment = TrAttachment::where('refnbr', $personnel->docid)
            ->where('status','A')
            ->orderByDesc('attachment_date')
            ->get(['id','attachment_name','filename','folder','extention','created_by','attachment_date']);

        $subgradings = StoSubGrading::select('subgrade_id','subgrade_name')
            ->where('status', 'A')
            ->orderBy('subgrade_id')
            ->get();

        $jobres = JobResponsiblities::where('docid', $personnel->docid)->get();
        $jobqua = JobQualification::where('docid', $personnel->docid)->get();

        $selectedTags = TrJobtag::where('docid', $personnel->docid)
            ->pluck('job_tags')
            ->toArray();

        return view('pages.personnels.editpersonnels', compact(
            'companies','departements','usercpny','usercpny2',
            'userdept','userdept2','skillTags','division','personnel',
            'attachment','subgradings','jobres','jobqua','selectedTags','hash'
        ));
    }

    public function updatePersonnel(Request $request, $hash)
    {

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);
        // Validasi utama
        $request->validate([
            'cpnyid'          => 'required|string',
            'departementid'   => 'required|string',
            'job_title'       => 'required|string',
            'subgrade_id'     => 'required|string',
            'immediate_superior' => 'required|string',
            'state_position'  => 'required|string',
            'job_type'        => 'required|string|in:Replacement,New',
            'reason_vacancy'  => 'required|string',
            'required'        => 'required|integer|min:1',
            'actual'          => 'required|integer|min:0',
            'total_actual'    => 'required|integer|min:0',
            // 'attachments.*' => 'file|max:20480', // opsional, 20MB
        ]);

        DB::beginTransaction();
        try {
            $datenow   = Carbon::now()->format('Y-m-d');
            $dt        = Carbon::now();
            $year      = (int) $dt->year;
            $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype   = 'PRF';
            $datestamp = Carbon::now()->toDateTimeString();
            $user      = $request->user();

            $personnel = Personnel::findOrFail($id);

            // Ambil grading (termasuk group_grade untuk logika approval)
            $grading = StoSubGrading::where('subgrade_id', $request->subgrade_id)
                ->where('status', 'A')
                ->first();

            if (!$grading) {
                return response()->json([
                    'error'   => 'Gagal menyimpan personnel',
                    'message' => 'Subgrading tidak ditemukan/Non-aktif'
                ], 422);
            }

            $groupGrade = (string)($grading->group_grade ?? ''); // ex: "Staff" / "Manager"

            // Update header personnel
            $personnel->update([
                'cpnyid'             => $request->cpnyid,
                'departementid'      => $request->departementid,
                'date'               => $datenow,
                'locationname'       => $request->siteid ?? null, // simpan ID site
                'user'               => $user->username,
                'job_title'          => $request->job_title,
                'subgrade_id'        => $request->subgrade_id,
                'job_level'          => $grading->subgrade_name,
                'immediate_superior' => $request->immediate_superior,
                'state_position'     => $request->state_position,
                'job_type'           => $request->job_type,
                'reason_vacancy'     => $request->reason_vacancy,
                'required'           => $request->required,
                'actual'             => $request->actual,
                'total_actual'       => $request->total_actual,
                'education'          => $request->education,
                'experience_start'   => $request->experience_start,
                'experience_end'     => $request->experience_end,
                'created_user'       => $user->username,
                'status'             => $request->status ?? 'P',
            ]);

            $docid = $personnel->docid;

            // ===== Rebuild Approval Lines (hapus pending lama, build ulang dari master) =====
            // Ambil baris approval: Normal + Condition yang cocok dengan group_grade
            $msApproval = MsApproval::where('aprv_doctype', $doctype)
                ->where('aprv_cpnyid', $request->cpnyid)
                ->where('aprv_departementid', $request->departementid)
                ->where('status', 'A')
                ->where(function ($q) use ($groupGrade) {
                    $q->where('aprv_type', 'Normal')
                    ->orWhere(function ($q2) use ($groupGrade) {
                        $q2->where('aprv_type', 'Condition')
                            ->where('aprv_condition', $groupGrade);
                    });
                })
                ->orderBy('aprv_leveling', 'asc')
                ->get();

            if ($msApproval->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Approval line belum di-setup (Normal/Condition) untuk kombinasi ini. Hubungi IT.'
                ], 422);
            }

            // Hapus pending lama agar tidak dobel
            TrApproval::where('refnbr', $docid)->where('status', 'P')->delete();

            // Sisipkan approval baru
            foreach ($msApproval as $row) {
                $isFirstLevel = ((int)$row->aprv_leveling === (int)$msApproval->min('aprv_leveling'));
                TrApproval::create([
                    'refnbr'             => $docid,
                    'aprv_leveling'      => $row->aprv_leveling,
                    'aprv_doctype'       => $row->aprv_doctype,
                    'aprv_cpnyid'        => $row->aprv_cpnyid,
                    'aprv_departementid' => $row->aprv_departementid,
                    'aprv_username'      => $row->aprv_username,
                    'aprv_name'          => $row->aprv_name,
                    'aprv_datebefore'    => $isFirstLevel ? $datestamp : null,
                    'aprv_type'          => $row->aprv_type,        // Normal / Condition
                    'aprv_condition'     => $row->aprv_condition,   // Staff / Manager (jika ada)
                    'status'             => 'P',
                    'created_by'         => $user->username,
                ]);
            }

            // ===== Rebuild Responsibilities =====
            if ($request->has('responsibilities')) {
                JobResponsiblities::where('docid', $docid)->delete();
                foreach ($request->responsibilities as $idx => $responsibility) {
                    if (trim((string)$responsibility) === '') continue;
                    JobResponsiblities::create([
                        'docid'                      => $docid,
                        'no_job_responsiblities'     => $idx + 1,
                        'job_responsibilities_descr' => $responsibility,
                        'created_user'               => $user->username,
                        'status'                     => 'P',
                    ]);
                }
            }

            // ===== Rebuild Qualification =====
            if ($request->has('qualification')) {
                JobQualification::where('docid', $docid)->delete();
                foreach ($request->qualification as $idx => $qualification) {
                    if (trim((string)$qualification) === '') continue;
                    JobQualification::create([
                        'docid'                    => $docid,
                        'no_job_qualification'     => $idx + 1,
                        'job_qualification_descr'  => $qualification,
                        'created_user'             => $user->username,
                        'status'                   => 'P',
                    ]);
                }
            }

            // ===== (Opsional) Tags — jika kamu juga mau perbarui di edit =====
            if ($request->has('tags')) {
                TrJobtag::where('docid', $docid)->delete();
                foreach ($request->tags as $tag) {
                    $t = trim((string)$tag);
                    if ($t === '') continue;

                    TrJobtag::create([
                        'docid'        => $docid,
                        'job_tags'     => $t,
                        'created_user' => $user->username,
                        'status'       => 'P',
                    ]);

                    if (!MJobtag::where('job_tags', $t)->exists()) {
                        MJobtag::create([
                            'job_tags'     => $t,
                            'created_user' => $user->username,
                            'status'       => 'A',
                        ]);
                    }
                }
            }

            // ===== Upload Attachment ke GCS + simpan TrAttachment =====
            if ($request->hasFile('attachments')) {
                $config = config('filesystems.disks.gcs');
                $storage = new StorageClient([
                    'projectId'   => $config['project_id'],
                    'keyFilePath' => $config['key_file'],
                ]);
                $bucket   = $storage->bucket($config['bucket']);
                $ymFolder = 'att-job-career/' . $doctype . '/' . $year; // ex: att-job-career/PRF/2025

                foreach ($request->file('attachments') as $file) {
                    if (!$file->isValid()) continue;

                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $ext          = $file->getClientOriginalExtension();
                    $randomPrefix = md5(random_int(1, 99999999)) . '-' . time();
                    $newFilename  = $randomPrefix . '.' . $ext;
                    $objectPath   = "{$ymFolder}/{$newFilename}";

                    try {
                        $bucket->upload(
                            fopen($file->getPathname(), 'r'),
                            [
                                'name' => $objectPath,
                                'predefinedAcl' => 'private',
                            ]
                        );

                        TrAttachment::create([
                            'refnbr'         => $docid,
                            'doctype'        => $doctype,
                            'attachment_date'=> $datestamp,
                            'cpnyid'         => $request->cpnyid,
                            'departementid'  => $request->departementid,
                            'attachment_name'=> pathinfo($originalName, PATHINFO_FILENAME),
                            'folder'         => $ymFolder,
                            'filename'       => $newFilename,
                            'filesize'       => $file->getSize(),
                            'extention'      => $ext,
                            'status'         => 'A',
                            'created_by'     => $user->username,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('GCS upload failed (updatePersonnel)', [
                            'docid'      => $docid,
                            'objectPath' => $objectPath,
                            'error'      => $e->getMessage()
                        ]);
                        DB::rollBack();
                        return response()->json([
                            'error'   => 'Gagal upload attachment',
                            'message' => $e->getMessage()
                        ], 500);
                    }
                }
            }

            // ===== Notifikasi ke approver berikutnya =====
            $next = TrApproval::where('refnbr', $docid)
                ->where('status', 'P')
                ->orderBy('aprv_leveling', 'ASC')
                ->first();

            $eid = Hashids::encode($personnel->id);

            if ($next) {
                // jika multi user dipisah comma
                $usernames = array_map('trim', explode(',', $next->aprv_username));
                $emailTargets = User::whereIn('username', $usernames)
                    ->where('status', 'A')
                    ->get();

                $mailData = [
                    'docid'   => $next->refnbr,
                    'cpnyid'  => $next->aprv_cpnyid,
                    'deptname'=> $next->aprv_departementid,
                    'date'    => $next->aprv_datebefore,
                    'name'    => $user->username,
                    'info'    => $request->job_title,
                    'url'     => url('/showvpersonels/' . $eid),
                ];

                foreach ($emailTargets as $recipient) {
                    Mail::send('emails.mailapprove', $mailData, function ($message) use ($mailData, $recipient) {
                        $message->to($recipient->notification_email)
                            ->subject($mailData['docid'] . ' - Waiting Approval Personnel')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'personnel' => $personnel]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan personnel',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function updatePersonnel_xxx(Request $request, $id)
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
            $year = (int) $dt->year;
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

            $eid = Hashids::encode($personnel->id);
           
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,                          
                'info' => $request->job_title,           
                'url' => url('/showvpersonels' .'/' . $eid)
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Waiting Approval Personnels');
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
            $attachment = TrAttachment::findOrFail($id);
            $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }
    }
 



    public function showPersonnel($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $personnel = Personnel::findOrFail($id);

        // === Approval pakai TrApproval (refnbr & aprv_leveling) ===
        $approval = TrApproval::where('refnbr', $personnel->docid)
            ->where('status', '<>', 'X')
            ->orderBy('aprv_leveling')
            ->orderBy('created_at')
            ->get();

        // === Detail lain tetap ===
        $jobres = JobResponsiblities::where('docid', $personnel->docid)->get();
        $jobqua = JobQualification::where('docid', $personnel->docid)->get();
        $jobtag = TrJobtag::where('docid', $personnel->docid)->get();

        // === Attachment di GCS + generate Signed URL ===
        $attachments = TrAttachment::where('refnbr', $personnel->docid)
            ->where('status', 'A')
            ->orderBy('created_at', 'asc')
            ->get();

        // Build signed URLs (aman, sementara, private)
        if ($attachments->isNotEmpty()) {
            $config  = config('filesystems.disks.gcs');
            $storage = new StorageClient([
                'projectId'   => $config['project_id'],
                'keyFilePath' => $config['key_file'],
            ]);
            $bucket = $storage->bucket($config['bucket']);

            foreach ($attachments as $at) {
                try {
                    $path   = rtrim($at->folder ?? '', '/').'/'.ltrim($at->filename ?? '', '/');
                    $object = $bucket->object($path);
                    // berlaku 10 menit; silakan ubah sesuai kebutuhan
                    $at->signed_url = $object->signedUrl(new \DateTime('+10 minutes'));
                } catch (\Throwable $e) {
                    // kalau gagal generate URL, kosongkan saja supaya link tidak muncul
                    $at->signed_url = null;
                }
            }
        }

        return view('pages.personnels.showpersonnels', [
            'personnel'  => $personnel,
            'jobres'     => $jobres,
            'jobqua'     => $jobqua,
            'approval'   => $approval,
            'attachment' => $attachments,
            'jobtag'     => $jobtag,
        ]);
    }


    public function fetchComments($refnbr)
    {
        $comments = TrMessage::where('refnbr', $refnbr)
            ->orderBy('message_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'comments' => $comments
        ]);
    }

    public function storeComment(Request $request, $refnbr)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $user = Auth::user();   // ambil user login

        $comment = TrMessage::create([
            'refnbr'        => $refnbr,
            'doctype'       => 'PRF',
            'message_date'  => now(),
            'cpny_id'        => $user->cpnyid ?? null,
            'department_id' => $user->departmentid ?? null,
            'username'      => $user->username,
            'name'          => $user->name,
            'message'       => $request->comment,
            'status'        => 'A',
            'created_by'    => $user->username,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment added successfully!',
            'comment' => $comment
        ]);
    }



    
    // public function fetchComments($id)
    // {
    
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
    //     $comment->doctype = 'PRF';
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


    public function approvePersonnel(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();
        $user = request()->user();

        $personnel = Personnel::where('docid', $docid)->first();
        if (!$personnel) {
            return response()->json(['success' => false, 'message' => 'PRF not found'], 404);
        }

        // Hitung sisa approval yang masih PENDING
        $countPending = TrApproval::where('refnbr', $personnel->docid)
            ->where('status', 'P')
            ->count();

        // Ambil baris approval yang sedang menunggu & sesuai user
        $tApproval = TrApproval::where('refnbr', $personnel->docid)
            ->where('status', 'P')
            ->where('aprv_username', 'like', '%'.$user->username.'%')
            ->orderBy('aprv_leveling')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
        }

        // Approve current level
        $tApproval->status          = 'A';
        $tApproval->aprv_dateafter  = $datestamp;
        $tApproval->aprv_username   = $user->username; // lock who approved
        $tApproval->aprv_name       = $user->name;
        $tApproval->save();

        // Jika ini approval terakhir -> close PRF
        if ($countPending === 1) {
            $personnel->status         = 'C';
            $personnel->completed_user = $user->username;
            $personnel->completed_at   = $datestamp;
            $personnel->save();

            // proses lanjutan setelah complete
            app('App\Http\Controllers\PersonnelController')->insert_jobposting($docid);

            return response()->json(['success' => true, 'message' => 'Task approved & completed']);
        }

        // Masih ada approval berikutnya
        $tNext = TrApproval::where('refnbr', $personnel->docid)
            ->where('status', 'P')
            ->orderBy('aprv_leveling', 'ASC')
            ->first();

        // Safety check
        if ($tNext) {
            $tNext->aprv_datebefore = $datestamp;
            $tNext->save();

            // Kirim email ke approver berikutnya
            $eid = \Hashids::encode($personnel->id);
            $data = [
                'docid'   => $tNext->refnbr,
                'cpnyid'  => $tNext->aprv_cpnyid,
                'deptname'=> $tNext->aprv_departementid,
                'date'    => $tNext->aprv_datebefore,
                'name'    => $tNext->created_by ?? $user->username, // fallback
                'info'    => $personnel->job_title,
                'url'     => url('/showvpersonels/'.$eid),
            ];

            $multiapp = explode(',', $tNext->aprv_username);
            $recipients = User::whereIn('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($recipients as $rcp) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $rcp) {
                    $message->to($rcp->notification_email)
                            ->subject($data['docid'].' - Waiting Approval Personnel')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectPersonnel(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();
        $user = request()->user();

        $personnel = Personnel::where('docid', $docid)->first();
        if (!$personnel) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        $tApproval = TrApproval::where('refnbr', $personnel->docid)
            ->where('status', 'P')
            ->where('aprv_username', 'like', '%'.$user->username.'%')
            ->orderBy('aprv_leveling')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
        }

        // Set current step -> Rejected
        $tApproval->status         = 'R';
        $tApproval->aprv_dateafter = $datestamp;
        $tApproval->save();

        // Set header -> Rejected
        $personnel->status = 'R';
        $personnel->save();

        // Batalkan semua sisa approval yang masih P
        TrApproval::where('refnbr', $personnel->docid)
            ->where('status', 'P')
            ->update(['status' => 'X']);

        // Kirim email ke creator
        $eid  = \Hashids::encode($personnel->id);
        $data = [
            'docid'    => $tApproval->refnbr,
            'cpnyid'   => $tApproval->aprv_cpnyid,
            'deptname' => $tApproval->aprv_departementid,
            'date'     => $tApproval->aprv_datebefore,
            'name'     => $tApproval->created_by ?? $user->username,
            'info'     => $personnel->job_title,
            'url'      => url('/showvpersonels/'.$eid),
        ];

        $creator = User::where('username', $personnel->created_user)
            ->where('status', 'A')
            ->get();

        foreach ($creator as $rcp) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $rcp) {
                $message->to($rcp->notification_email)
                        ->subject($data['docid'].' - Rejected Personnel')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        // Kirim komentar (alasan) via controller existing
        $id = $personnel->id;
        $doctype = 'PRF';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Personnel rejected successfully']);
    }

    public function revisePersonnel(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();
        $user = request()->user();

        $personnel = Personnel::where('docid', $docid)->first();
        if (!$personnel) {
            return response()->json(['success' => false, 'message' => 'Personnel not found'], 404);
        }

        $tApproval = TrApproval::where('refnbr', $personnel->docid)
            ->where('status', 'P')
            ->where('aprv_username', 'like', '%'.$user->username.'%')
            ->orderBy('aprv_leveling')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
        }

        // Set current step -> Revise
        $tApproval->status         = 'D';
        $tApproval->aprv_dateafter = $datestamp;
        $tApproval->save();

        // Header -> Revise
        $personnel->status = 'D';
        $personnel->save();

        // Batalkan semua sisa approval yang masih P
        TrApproval::where('refnbr', $personnel->docid)
            ->where('status', 'P')
            ->update(['status' => 'X']);

        // Email ke creator
        $eid  = \Hashids::encode($personnel->id);
        $data = [
            'docid'    => $tApproval->refnbr,
            'cpnyid'   => $tApproval->aprv_cpnyid,
            'deptname' => $tApproval->aprv_departementid,
            'date'     => $tApproval->aprv_datebefore,
            'name'     => $tApproval->created_by ?? $user->username,
            'info'     => $personnel->job_title,
            'url'      => url('/showvpersonels/'.$eid),
        ];

        $creator = User::where('username', $personnel->created_user)
            ->where('status', 'A')
            ->get();

        foreach ($creator as $rcp) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $rcp) {
                $message->to($rcp->notification_email)
                        ->subject($data['docid'].' - Revise Personnel')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        // Simpan komentar (alasan revisi)
        $id = $personnel->id;
        $doctype = 'PRF';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Personnel revised successfully']);
    }


    // public function approvePersonnel(Request $request, $docid)
    // {
    //     $datestamp = Carbon::now()->toDateTimeString();       
    //     $user = request()->user(); // Ambil user yang login
        
    //     $personnel = Personnel::where('docid', $docid)->first();   

    //     if (!$personnel) {
    //         return response()->json(['success' => false, 'message' => 'Prf not found'], 404);
    //     }        

    //     $count_approval = T_approval::where('docid', '=', $personnel->docid)
    //         ->where('status', '=', 'P')
    //         ->count();
    
    //     // Cek apakah user memiliki akses untuk approve
    //     $t_approval = T_approval::where('docid', $personnel->docid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%" . $user->username . "%")
    //         ->first();
    //     // dd($t_approval);
    //     if ($t_approval == null) {
    //         return response()->json(['success' => false, 'message' => "You Can't Approve!"], 403);
    //     } else {
    //         $t_approval->status = 'A';
    //         $t_approval->aprvdateafter = $datestamp;
    //         $t_approval->aprvusername = $user->username;
    //         $t_approval->name = $user->name;
    //         $t_approval->save();
    //     }   

    //     if ($count_approval == 1) {
    //         $personnel->status = 'C';
    //         $personnel->completed_user = $user->username;
    //         $personnel->completed_at = $datestamp;
    //         $personnel->save();
    //         app('App\Http\Controllers\PersonnelController')->insert_jobposting($docid);
    //     }

    //     $t_approval_next = T_approval::where('docid', $personnel->docid)
    //         ->where('status', 'P')
    //         ->orderby('aprvid','ASC')
    //         ->first();

    //     $eid = Hashids::encode($personnel->id);

    //     if ($count_approval <> 1) {
    //         //update datebefore
    //         $t_approval_next->aprvdatebefore = $datestamp;
    //         $t_approval_next->save();

    //         //send email 
    //         $data = array(
    //             'docid' => $t_approval_next->docid,
    //             'cpnyid' => $t_approval_next->aprvcpnyid,
    //             'deptname' => $t_approval_next->aprvdeptid,               
    //             'date' => $t_approval_next->aprvdatebefore,
    //             'name' => $t_approval_next->created_user,
    //             'info' => $personnel->job_title,               
    //             'url' => url('/showvpersonels' .'/' . $eid)  
                
    //         );

    //         $multiapp = explode(',', $t_approval_next->aprvusername);

    //         $email_it = User::whereIN('username', $multiapp)
    //             ->where('status', 'A')
    //             ->get();

    //         foreach ($email_it as $emailsit) {
    //             Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

    //                 $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Waiting Approval Personnel');
    //                 $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         }
    //     }

    //     return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    // }

    // public function rejectPersonnel(Request $request, $docid)
    // {
        
    //     // dd($request->all());         
    //     $datestamp = Carbon::now()->toDateTimeString();       
    //     $user = request()->user(); // Ambil user yang login

    //     $personnel = Personnel::where('docid', $docid)->first();  
        
        
    //     if (!$personnel) {
    //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    //     }

    //     // Cek apakah user memiliki akses untuk approve
    //     $t_approval = T_approval::where('docid', $personnel->docid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%" . $user->username . "%")
    //         ->first();
    //     // dd($t_approval);
    //     if ($t_approval == null) {
    //         return response()->json(['success' => false, 'message' => "You Can't Rejected!"], 403);
    //     } else {
    //         $t_approval->status = 'R';
    //         $t_approval->aprvdateafter = $datestamp;           
    //         $t_approval->save();

    //         $personnel->status = 'R';
    //         $personnel->save();
    //     }   
                       
    //     $t_aprv_sisa = T_approval::where('docid', '=', $personnel->docid)
    //         ->where('status', '=', 'P')
    //         ->get();

    //     foreach ($t_aprv_sisa as $t_aprv) {
    //         $t_aprv->status = 'X';
    //         $t_aprv->save();
    //     }

    //     $eid = Hashids::encode($personnel->id);

    //     //send email 
    //     $data = array(
    //         'docid' => $t_approval->docid,
    //         'cpnyid' => $t_approval->aprvcpnyid,
    //         'deptname' => $t_approval->aprvdeptid,
    //         // 'locationname' => $ms_site->site,
    //         'date' => $t_approval->aprvdatebefore,
    //         'name' => $t_approval->created_user,
    //         'info' => $personnel->job_title,               
    //         'url' => url('/showvpersonels' .'/' . $eid) 

    //     );

       
    //     $email_it = User::where('username', $personnel->created_user)
    //             ->where('status', 'A')
    //             ->get();

    //     foreach ($email_it as $emailsit) {
    //         Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

    //             $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Rejected Personnel');
    //             $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //         });
    //     }

    //     $id = $personnel->id;
    //     $doctype ='PRF';
    //     app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

    //     return response()->json(['success' => true, 'message' => 'Personnel rejected successfully']);
    // }

    // public function revisePersonnel(Request $request, $docid)
    // {
        
    //     // dd($request->all());         
    //     $datestamp = Carbon::now()->toDateTimeString();       
    //     $user = request()->user(); // Ambil user yang login

    //     $personnel = Personnel::where('docid', $docid)->first();  
        
        
    //     if (!$personnel) {
    //         return response()->json(['success' => false, 'message' => 'Personnel not found'], 404);
    //     }

    //     // Cek apakah user memiliki akses untuk approve
    //     $t_approval = T_approval::where('docid', $personnel->docid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%" . $user->username . "%")
    //         ->first();
    //     // dd($t_approval);
    //     if ($t_approval == null) {
    //         return response()->json(['success' => false, 'message' => "You Can't Revise!"], 403);
    //     } else {
    //         $t_approval->status = 'D';
    //         $t_approval->aprvdateafter = $datestamp;           
    //         $t_approval->save();

    //         $personnel->status = 'D';
    //         $personnel->save();
    //     }   
                       
    //     $t_aprv_sisa = T_approval::where('docid', '=', $personnel->docid)
    //         ->where('status', '=', 'P')
    //         ->get();

    //     foreach ($t_aprv_sisa as $t_aprv) {
    //         $t_aprv->status = 'X';
    //         $t_aprv->save();
    //     }

    //     $eid = Hashids::encode($personnel->id);
    //     //send email 
    //     $data = array(
    //         'docid' => $t_approval->docid,
    //         'cpnyid' => $t_approval->aprvcpnyid,
    //         'deptname' => $t_approval->aprvdeptid,
    //         // 'locationname' => $ms_site->site,
    //         'date' => $t_approval->aprvdatebefore,
    //         'name' => $t_approval->created_user,
    //         'info' => $personnel->job_title,               
    //         'url' => url('/showvpersonels' .'/' . $eid) 

    //     );

       
    //     $email_it = User::where('username', $personnel->created_user)
    //             ->where('status', 'A')
    //             ->get();

    //     foreach ($email_it as $emailsit) {
    //         Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

    //             $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Revise Personnel');
    //             $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //         });
    //     }

    //     $id = $personnel->id;
    //     $doctype ='PRF';
    //     app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

    //     return response()->json(['success' => true, 'message' => 'Personnel revise successfully']);
    // }

    

    // public function checkApproval($id, $action)
    // {
    //     $user = Auth::user(); // Ambil user yang login
    //     // dd($action);
    //     // Query dasar untuk pengecekan
    //     $query = T_approval::where('docid', $id)
    //                 ->where('aprvusername', 'like', '%' . $user->username . '%')
    //                 ->where('status', 'P');                 

    //     // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
    //     if (in_array($action, ['reject', 'revise','approve'])) {
    //         $query->whereNotNull('aprvdatebefore');
    //     }

    //     // Cek apakah user bisa melakukan aksi
    //     $canPerformAction = $query->exists();

    //     return response()->json(['canPerformAction' => $canPerformAction]);
    // }



    public function checkApproval($refnbr, $action)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['canPerformAction' => false]);
        }

        // Cek apakah ada pending step yang memuat user ini
        $qUser = TrApproval::where('refnbr', $refnbr)
            ->where('status', 'P')
            ->where('aprv_username', 'like', '%'.$user->username.'%');

        // Untuk approve/reject/revise: harus sudah "dibuka" (aprv_datebefore != null)
        if (in_array($action, ['approve', 'reject', 'revise'], true)) {
            $qUser->whereNotNull('aprv_datebefore');
        }

        $hasPendingForUser = $qUser->exists();

        // Hard guard: user hanya boleh bertindak jika dia berada di step PENDING TERENDAH (next approver)
        $next = TrApproval::where('refnbr', $refnbr)
            ->where('status', 'P')
            ->orderBy('aprv_leveling', 'asc')
            ->first();

        $onNextLevel = false;
        if ($next) {
            // cek username ada di list approver step berikutnya
            $userIsOnNext = Str::contains(
                ','.$next->aprv_username.',',
                ','.$user->username.','
            );

            // step berikutnya juga harus sudah "dibuka"
            $opened = !is_null($next->aprv_datebefore);

            $onNextLevel = $userIsOnNext && $opened;
        }

        $canPerformAction = $hasPendingForUser && $onNextLevel;

        return response()->json(['canPerformAction' => $canPerformAction]);
    }


    public function insert_jobposting($id)
    {
        
        DB::beginTransaction();
        try {
            $doctype = 'JOB';
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = (int) $dt->year;
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

    
    public function viewAttachment($id)
    {
        $att = TrAttachment::where('id', $id)->where('status','A')->firstOrFail();

        // Normalisasi object path:
        // 1) Kalau filename sudah mengandung '/', anggap itu full path.
        // 2) Kalau tidak, gabungkan folder + filename (kalau folder ada).
        $objectPath = trim((string)$att->filename ?? '', '/');
        if (!Str::contains($objectPath, '/')) {
            $folder = trim((string)$att->folder ?? '', '/');
            if ($folder !== '') {
                $objectPath = $folder . '/' . $objectPath;
            }
        }

        if ($objectPath === '') {
            abort(404, 'Empty object path');
        }

        // (Opsional) catat untuk debug cepat
        Log::info('GCS viewAttachment', [
            'id' => $att->id,
            'folder' => $att->folder,
            'filename' => $att->filename,
            'objectPath' => $objectPath,
        ]);

        $config = config('filesystems.disks.gcs');
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $config['key_file'],
        ]);
        $bucket = $storage->bucket($config['bucket']);
        $object = $bucket->object($objectPath);

        if (!$object->exists()) {
            // Tambahkan log supaya kelihatan path apa yang dicari
            Log::warning('GCS object not found', ['objectPath' => $objectPath]);
            abort(404, 'File not found in storage: ' . $objectPath);
        }

        // Signed URL (V4) 15 menit
        $url = $object->signedUrl(
            now()->addMinutes(15),
            ['version' => 'v4']
        );

        return redirect()->away($url);
    }

    public function byDivision(Request $request)
    {
        $divisionId = $request->query('division_id');

        if (!$divisionId) {
            return response()->json([], 200);
        }

        $departments = DepartmentHR::query()
            ->select('department_id', 'department_name', 'division_id')
            ->where('division_id', $divisionId)
            ->where('status', 'A')
            ->orderBy('department_name')
            ->get();

        return response()->json($departments, 200);
    }





}
