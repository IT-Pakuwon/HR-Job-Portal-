<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\Autonbr;
use App\Models\AutonbrJobportal;
use App\Models\CompanyAddress;
use App\Models\DepartmentHR;
use App\Models\Division;
use App\Models\GroupAccspecific;
use App\Models\HrCompanyBudget;
use App\Models\JobLevel;
use App\Models\Jobposting;
use App\Models\JobpostingQualification;
use App\Models\JobpostingResponsiblities;
use App\Models\Jobpostingtag;
use App\Models\JobQualification;
use App\Models\JobResponsiblities;
use App\Models\MJobtag;
use App\Models\MsApproval;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsEntity;
use App\Models\Personnel;
use App\Models\Site;
use App\Models\StoDepartement;
use App\Models\StoSubGrading;
use App\Models\SysUserRole;
use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrJobtag;
use App\Models\TrMessage;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\Userdivision;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mail;
use Vinkla\Hashids\Facades\Hashids;

class PersonnelController extends Controller
{
    use HasAutonbr;

    private function splitCsv(?string $value): array
    {
        if (!$value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($x) => trim((string) $x))
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

    private function personnelMailMasterNames(Personnel $personnel): array
    {
        $companyName = MsCompany::query()
            ->where('cpny_id', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->value('cpny_name');

        $departmentName = DepartmentHR::query()
            ->where('department_id', $personnel->departementid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->value('department_name');

        $creatorName = User::query()
            ->where('username', $personnel->created_user)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->value('name');

        return [
            'company' => $companyName ?: $personnel->cpnyid,
            'department' => $departmentName ?: $personnel->departementid,
            'creator' => $creatorName ?: '-',
        ];
    }

    private function personnelScopeForUser($user)
    {
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

        $q = Personnel::query()
            ->where('group_cpny_id', $groupCompanyId);

        // RECACCALLDEPT / DIRECTORACCESS -> bisa lihat semua company & semua division
        if ($user->hasFullDataScope() || $this->hasRoleAllDept($user)) {
            return $q;
        }

        $cpnyIds = $this->userCpnyIds($user);

        // wajib punya cpny
        if (empty($cpnyIds)) {
            return $q->whereRaw('1=0');
        }

        // filter cpnyid user (AW,EP,PSA,GPS)
        $q->whereIn('cpnyid', $cpnyIds);

        // selain itu: filter division_id dari user (langsung)
        $divisionIds = $this->userDivisionIds($user);
        if (empty($divisionIds)) {
            return $q->whereRaw('1=0');
        }

        return $q->whereIn('division_id', $divisionIds);
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // ==============================
        // CHECK ACCESS RECACCESS
        // ==============================
        $hasAccess = $user->hasFullDataScope() || SysUserRole::query()
            ->where('username', $user->username)
            ->where('role_id', 'RECACCESS')
            ->where('status', 'A')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses untuk membuka halaman ini.');
        }

        // 🔽 dropdown department (buat filter HCBP)
        $departments = DepartmentHR::where('status', 'A')
            ->orderBy('department_name')
            ->get();

        $hasAllDeptAccess = $this->hasRoleAllDept($user);
        $filterCompanies = collect();
        $filterDivisions = collect();
        $filterDepartments = collect();

        if ($hasAllDeptAccess) {
            $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

            $filterCompanies = MsCompany::query()
                ->select('cpny_id', 'cpny_name')
                ->where('group_cpny_id', $groupCompanyId)
                ->where('status', 'A')
                ->orderBy('cpny_name')
                ->get();

            $filterDivisions = Division::query()
                ->select('division_id', 'division_name')
                ->where('group_cpny_id', $groupCompanyId)
                ->where('status', 'A')
                ->orderBy('division_name')
                ->get();

            $filterDepartments = DepartmentHR::query()
                ->select('department_id', 'department_name', 'division_id')
                ->where('group_cpny_id', $groupCompanyId)
                ->where('status', 'A')
                ->orderBy('department_name')
                ->get();
        }

        // =========================================================
        // 🔥 BASE NORMAL (ALL CARD) → HARUS PAKAI SCOPE USER
        // =========================================================
        $baseUser = $this->personnelScopeForUser($user);

        $counts = (clone $baseUser)->selectRaw("
            COUNT(*) AS all,
            SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) AS on_progress,
            SUM(CASE WHEN status = 'R' THEN 1 ELSE 0 END) AS reject,
            SUM(CASE WHEN status = 'D' THEN 1 ELSE 0 END) AS revise,
            SUM(CASE WHEN status = 'C' THEN 1 ELSE 0 END) AS completed,
            SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) AS draft
        ")->first();

        // =========================================================
        // 🔥 HCBP ALL (SEPARATE COUNT)
        // =========================================================
        $hcbpAll = null;

        if ($this->hasHcbpAccess($user)) {
            $baseHcbp = Personnel::query()
                ->where('group_cpny_id', strtoupper(trim((string) $user->group_cpny_id)))
                ->whereIn('cpnyid', $this->userCpnyIds($user));

            $hcbpAll = (clone $baseHcbp)->count();
        }

        // =========================================================
        return view('pages.personnels.personnels', [
            'all' => (int) ($counts->all ?? 0),
            'onProgress' => (int) ($counts->on_progress ?? 0),
            'reject' => (int) ($counts->reject ?? 0),
            'revise' => (int) ($counts->revise ?? 0),
            'completed' => (int) ($counts->completed ?? 0),
            'draft' => (int) ($counts->draft ?? 0),
            'hcbpAll' => (int) ($hcbpAll ?? 0), // 🔥 tambahan
            'departments' => $departments,
            'hasAllDeptAccess' => $hasAllDeptAccess,
            'filterCompanies' => $filterCompanies,
            'filterDivisions' => $filterDivisions,
            'filterDepartments' => $filterDepartments,
            'group_cpny_id' => strtoupper(trim((string) $user->group_cpny_id)),
        ]);
    }

    private function hasHcbpAccess($user): bool
    {
        return SysUserRole::query()
            ->where('username', $user->username)
            ->where('role_id', 'HCBPACCESS')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'A');
            })
            ->exists();
    }

    public function json(Request $request)
    {
        $user = Auth::user();
        $username = auth()->user()->username;

        $status = $request->query('status');

        if ($request->hcbp == 1 && $this->hasHcbpAccess($user)) {
            $cpnyIds = $this->userCpnyIds($user);

            $query = Personnel::query()
                ->where('group_cpny_id', strtoupper(trim((string) $user->group_cpny_id)))
                ->whereIn('cpnyid', $cpnyIds);
        } else {
            $query = $this->personnelScopeForUser($user);
        }

        // ✅ STATUS FILTER (ONLY ONE)
        if (!empty($status) && strtolower($status) !== 'all') {
            $query->where('status', $status);
        }

        if ($this->hasRoleAllDept($user)) {
            if ($request->filled('company')) {
                $query->where('cpnyid', $request->company);
            }

            if ($request->filled('division')) {
                $query->where('division_id', $request->division);
            }
        }

        // ✅ DEPARTMENT FILTER
        if ($request->filled('department')) {
            $query->where('departementid', $request->department);
        }

        $rows = $query
            ->orderByDesc('created_at')
            ->orderByDesc('docid')
            ->get();

        $refids = $rows->pluck('docid')->toArray();
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

        $jobpostingMap = Jobposting::whereIn('refid', $refids)
            ->where('group_cpny_id', $groupCompanyId)
            ->get()
            ->keyBy('refid');

        $hasPostingAccess = GroupAccspecific::where('username', $username)
            ->where('group_cpny_id', strtoupper(trim((string) $user->group_cpny_id)))
            ->where('group_access_id', 'POSTING')
            ->where('status', 'A') // optional kalau ada status aktif
            ->exists();

        $companyNames = MsCompany::query()
            ->where('group_cpny_id', $groupCompanyId)
            ->whereIn('cpny_id', $rows->pluck('cpnyid')->filter()->unique())
            ->pluck('cpny_name', 'cpny_id');
        $divisionNames = Division::query()
            ->where('group_cpny_id', $groupCompanyId)
            ->whereIn('division_id', $rows->pluck('division_id')->filter()->unique())
            ->pluck('division_name', 'division_id');
        $departmentNames = DepartmentHR::query()
            ->where('group_cpny_id', $groupCompanyId)
            ->whereIn('department_id', $rows->pluck('departementid')->filter()->unique())
            ->pluck('department_name', 'department_id');

        $personnel = $rows->map(function ($row) use (
            $jobpostingMap,
            $hasPostingAccess,
            $companyNames,
            $divisionNames,
            $departmentNames
        ) {
            $jobposting = $jobpostingMap[$row->docid] ?? 'Not Posted';

            return [
                'eid' => Hashids::encode($row->id),
                'docid' => $row->docid,
                'date' => $row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : null,
                'cpnyid' => $companyNames->get($row->cpnyid, $row->cpnyid),
                'cpnyid_code' => $row->cpnyid,
                'group_cpny_id' => $row->group_cpny_id,
                'departementid' => $departmentNames->get($row->departementid, $row->departementid),
                'departementid_code' => $row->departementid,
                'division_id' => $divisionNames->get($row->division_id, $row->division_id),
                'division_id_code' => $row->division_id,
                'job_title' => $row->job_title,
                'job_level' => $row->job_level,
                'created_user' => $row->created_user,
                'status' => $row->status,

                // 🔥 FIX HERE
                'jobposting_status' => $jobposting->status ?? null,
                'jobposting_reason' => $jobposting->reason ?? null,
                'can_toggle' => $hasPostingAccess,
            ];
        });

        return response()->json(['data' => $personnel]);
    }

    public function createPersonnel()
    {
        $user = request()->user();

        // ==============================
        // CHECK ACCESS RECACCESS
        // ==============================
        $hasAccess = $user->hasFullDataScope() || SysUserRole::query()
            ->where('username', $user->username)
            ->where('role_id', 'RECACCESS')
            ->where('status', 'A')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses untuk membuka halaman ini.');
        }

        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));
        $accessibleCompanyIds = $usercpny->pluck('cpny_id')->filter()->unique()->values();
        $companies = MsCompany::query()
            ->select('cpny_id', 'cpny_name')
            ->where('group_cpny_id', $groupCompanyId)
            ->where('status', 'A')
            ->whereIn('cpny_id', $accessibleCompanyIds)
            ->orderBy('cpny_id')
            ->get();
        $skillTags = MJobtag::select('id', 'job_tags')->get();

        $division = Division::select('division_id', 'division_name')
            ->where('status', 'A')
            ->where('group_cpny_id', $groupCompanyId)
            ->get();

        $subgradings = StoSubGrading::select('subgrade_id', 'subgrade_name', 'group_grade')
            ->where('status', 'A')
            ->where('group_cpny_id', $groupCompanyId)
            ->orderBy('subgrade_id', 'ASC')
            ->get();
// dd($subgradings);
        $activeUsers = User::select('username', 'name')
            ->where('status', 'A')
            ->where('group_cpny_id', $groupCompanyId)
            ->orderBy('name', 'ASC')
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
            ->where('group_cpny_id', $groupCompanyId)
            ->whereIn('division_id', $userDivisionIds)
            ->orderBy('division_name')
            ->get();

        $companyBudgets = HrCompanyBudget::query()
            ->select('cpnyid', 'budget_entity_id')
            ->where('group_cpny_id', $groupCompanyId)
            ->where('status', 'A')
            ->whereNull('deleted_at')
            ->orderBy('budget_entity_id')
            ->get();

        $entityNames = MsEntity::query()
            ->where('group_cpny_id', $groupCompanyId)
            ->where('status', 'A')
            ->whereIn('entity_id', $companyBudgets->pluck('budget_entity_id')->filter()->unique())
            ->pluck('entity_name', 'entity_id');

        $companyBudgets->each(function ($budget) use ($entityNames) {
            $budget->budget_entity_name = $entityNames->get($budget->budget_entity_id);
        });

        $view = $groupCompanyId === 'SBY'
            ? 'pages.personnels.createpersonnels_sby'
            : 'pages.personnels.createpersonnels';

        return view($view, compact('companies', 'usercpny', 'usercpny2', 'userdept', 'userdept2', 'skillTags', 'division', 'subgradings', 'userdivison', 'activeUsers', 'companyBudgets'));
    }


    public function storePersonnel(Request $request)
    {
        // Validasi input
        $user = $request->user();
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));
        $isDraft = $request->boolean('is_draft');

        if ($isDraft && $groupCompanyId !== 'SBY') {
            return response()->json(['message' => 'Save as Draft hanya tersedia untuk group SBY.'], 403);
        }

        $rules = [
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'attachments.*' => 'file|max:2048', // Validasi file, max 2MB
        ];

        if ($isDraft) {
            $rules += [
                'job_title' => 'nullable|string',
                'subgrade_id' => 'nullable|string',
                'immediate_superior' => 'nullable|string',
                'state_position' => 'nullable|string',
                'job_type' => 'nullable|string',
                'reason_vacancy' => 'nullable|string',
                'required' => 'nullable|integer',
                'actual' => 'nullable|integer',
                'total_actual' => 'nullable|integer',
                'budget_entity_id' => 'nullable|string',
            ];
        } else {
            $rules += [
                'job_title' => 'required|string',
                'subgrade_id' => 'required|string',
                'immediate_superior' => 'required|string',
                'state_position' => 'required|string',
                'job_type' => 'required|string',
                'reason_vacancy' => 'required|string',
                'required' => 'required|integer',
                'actual' => 'required|integer',
                'total_actual' => 'required|integer',
                'budget_entity_id' => $groupCompanyId === 'SBY' ? 'required|string' : 'nullable|string',
            ];
        }

        $request->validate($rules);

        if (!$isDraft) {
            if ($groupCompanyId === 'SBY') {
                $hasCompanyBudget = HrCompanyBudget::query()
                    ->where('group_cpny_id', $groupCompanyId)
                    ->where('cpnyid', $request->cpnyid)
                    ->where('budget_entity_id', $request->budget_entity_id)
                    ->where('status', 'A')
                    ->whereNull('deleted_at')
                    ->exists();

                if (!$hasCompanyBudget) {
                    return response()->json(['message' => 'Budget Company tidak valid untuk Company yang dipilih.'], 422);
                }
            }

            $hasSite = CompanyAddress::query()
                ->where('group_cpny_id', $groupCompanyId)
                ->where('sitelocation', $request->siteid)
                ->exists();

            if (!$hasSite) {
                return response()->json(['message' => 'Placement Location tidak valid untuk group company user.'], 422);
            }
        }

        $grading = null;
        if ($request->filled('subgrade_id')) {
            $grading = StoSubGrading::where('subgrade_id', $request->subgrade_id)
                ->where('status', 'A')
                ->where('group_cpny_id', $groupCompanyId)
                ->first();
        }

        if (!$isDraft && !$grading) {
            return response()->json([
                'message' => 'Job level tidak valid untuk subgrade yang dipilih.',
            ], 422);
        }

        $groupGrade = (string) ($grading->group_grade ?? '');
        // $positionCondition = strtolower(trim($request->job_type.' '.$groupGrade));
        $positionCondition = strtolower(trim($groupGrade));
        $doctype = 'PRF';
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';
        $dt = \Carbon\Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();
        $datenow = Carbon::now()->format('Y-m-d');

        if (!$isDraft) {
            // cek availability approval line (Normal atau Condition yg cocok)
            $count_approval = MsApproval::where('status', 'A')
                ->where('aprv_cpnyid', $request->cpnyid)
                ->where('aprv_departementid', $request->departementid)
                ->where('aprv_doctype', $doctype)
                ->where(function ($q) use ($positionCondition) {
                    $q->where('aprv_type', 'Normal')
                    ->orWhere(function ($q2) use ($positionCondition) {
                        $q2->where('aprv_type', 'Condition')
                            ->where('aprv_condition', $positionCondition);
                    });
                })
                ->count();

            if ($count_approval === 0) {
                return response()->json([
                    'message' => 'Approval line belum di-setup untuk kombinasi ini (Normal/Condition). Please contact IT!',
                ], 422);
            }
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

            $auto = $this->nextAutonbrByGroupCpnyid(
                $doctype,
                $year,
                $month,
                $groupCompanyId,
                $username,
                'PRF'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2).$month;   // YYMM
            $docid = $doctype.$tglbln.sprintf('%04d', $urutan);

            $title = StoDepartement::where('departement_id', $request->job_title)
                ->where('status', 'A')
                ->first();

            $task = Personnel::create([
                'docid' => $docid,
                'cpnyid' => $request->cpnyid,
                'group_cpny_id' => $groupCompanyId,
                'departementid' => $request->departementid,
                'division_id' => $request->division,
                'locationname' => $request->siteid ?? null,
                'budget_entity_id' => $request->budget_entity_id,
                'date' => $datenow,
                'user' => $user->username,
                'job_title' => $request->job_title,
                'subgrade_id' => $request->subgrade_id,
                'job_level' => $grading->subgrade_name ?? null,
                'immediate_superior' => $request->immediate_superior,
                'state_position' => $request->state_position,
                'immediate_replacement' => $request->immediate_replacement,
                'job_type' => $request->job_type,
                'reason_vacancy' => $request->reason_vacancy,
                'required' => $request->required ?? 0,
                'actual' => $request->actual ?? 0,
                'total_actual' => $request->total_actual ?? 0,
                'education' => $request->education,
                'education_jurusan' => $request->education_jurusan,
                'experience_start' => $request->experience_start,
                'experience_end' => $request->experience_end,
                'experience_position' => $request->experience_position,
                'created_user' => $user->username,
                'status' => $isDraft ? 'H' : ($request->status ?? 'P'),
            ]);

            if (!$isDraft) {
                $msApprovalLines = MsApproval::where('status', 'A')
                    ->where('aprv_cpnyid', $request->cpnyid)
                    ->where('aprv_departementid', $request->departementid)
                    ->where('aprv_doctype', $doctype)
                    ->where(function ($q) use ($positionCondition) {
                        $q->where('aprv_type', 'Normal')
                        ->orWhere(function ($q2) use ($positionCondition) {
                            $q2->where('aprv_type', 'Condition')
                                ->whereRaw('LOWER(TRIM(aprv_condition)) = ?', [trim($positionCondition)]);
                        });
                    })
                    ->orderBy('aprv_leveling', 'ASC')
                    ->get();

                // insert tr_approval
                foreach ($msApprovalLines as $line) {
                    $isFirstLevel = ((int) $line->aprv_leveling === 1);

                    TrApproval::create([
                        'refnbr' => $docid,
                        'aprv_leveling' => $line->aprv_leveling,
                        'aprv_doctype' => $line->aprv_doctype,
                        'aprv_cpnyid' => $line->aprv_cpnyid,
                        'aprv_departementid' => $line->aprv_departementid,
                        'aprv_username' => $line->aprv_username,   // bisa comma-separated
                        'aprv_name' => $line->aprv_name,
                        'aprv_datebefore' => $isFirstLevel ? $datestamp : null,
                        'aprv_dateafter' => null,
                        'aprv_type' => $line->aprv_type,       // Normal / Condition
                        'aprv_condition' => $line->aprv_condition,  // null / Staff / Manager
                        'aprv_start_nominal' => $line->aprv_start_nominal,
                        'aprv_end_nominal' => $line->aprv_end_nominal,
                        'status' => 'P',                    // Pending
                        'created_by' => $user->username,
                        'updated_by' => null,
                    ]);
                }
            }

            if ($request->has('responsibilities')) {
                foreach ($request->responsibilities as $index => $responsibility) {
                    JobResponsiblities::create([
                        'docid' => $docid,
                        'cpnyid' => $request->cpnyid,
                        'group_cpny_id' => $groupCompanyId,
                        'no_job_responsiblities' => $index + 1, // Urutan dimulai dari 1
                        'job_responsibilities_descr' => $responsibility,
                        'created_user' => $user->username,
                        'status' => 'P',
                    ]);
                }
            }

            // Simpan Qualification
            if ($request->has('qualification')) {
                foreach ($request->qualification as $index => $qualification) {
                    JobQualification::create([
                        'docid' => $docid,
                        'cpnyid' => $request->cpnyid,
                        'group_cpny_id' => $groupCompanyId,
                        'no_job_qualification' => $index + 1,
                        'job_qualification_descr' => $qualification,
                        'created_user' => $user->username,
                        'status' => 'P',
                    ]);
                }
            }

            if ($request->has('tags')) {
                foreach ($request->tags as $tag) {
                    // Insert ke TrJobtag (langsung saja karena ini log history / transaksi)
                    TrJobtag::create([
                        'docid' => $docid,
                        'cpnyid' => $request->cpnyid,
                        'group_cpny_id' => $groupCompanyId,
                        'job_tags' => $tag,
                        'created_user' => $user->username,
                        'status' => 'P',
                    ]);

                    // Cek apakah tag sudah ada di MJobtag
                    $exists = MJobtag::where('job_tags', $tag)->exists();

                    // Jika belum ada, baru insert ke master
                    if (!$exists) {
                        MJobtag::create([
                            'job_tags' => $tag,
                            'created_user' => $user->username,
                            'status' => 'A',
                        ]);
                    }
                }
            }

            // Simpan Attachments ke attachments
            // === Upload attachments ke GCS & simpan ke tr_attachment ===
            if ($request->hasFile('attachments')) {
                $ymFolder = 'att-job-career/'.strtolower($doctype).'/'.$year;

                // init GCS
                $config = config('filesystems.disks.gcs');
                $storage = new StorageClient([
                    'projectId' => $config['project_id'],
                    'keyFilePath' => $config['key_file'],
                ]);
                $bucket = $storage->bucket($config['bucket']);

                foreach ($request->file('attachments') as $file) {
                    if (!$file->isValid()) {
                        Log::warning('Attachment invalid', ['name' => $file->getClientOriginalName()]);
                        continue;
                    }

                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $nameOnly = pathinfo($originalName, PATHINFO_FILENAME); // untuk attachment_name
                    $ext = $file->getClientOriginalExtension();
                    $sizeBytes = $file->getSize();

                    $randomPrefix = md5(random_int(1, 99999999));
                    $filename = $randomPrefix.'.'.$ext;       // nama file di bucket
                    $gcsPath = "{$ymFolder}/{$filename}";

                    try {
                        // upload ke GCS (private)
                        $bucket->upload(
                            fopen($file->getPathname(), 'r'),
                            [
                                'name' => $gcsPath,
                                'predefinedAcl' => 'private',
                                'metadata' => ['contentType' => $file->getMimeType()],
                            ]
                        );

                        Log::info('Attachment uploaded to GCS', ['docid' => $docid, 'path' => $gcsPath]);

                        // simpan metadata ke tr_attachment (pgsql2)
                        TrAttachment::create([
                            'refnbr' => $docid,
                            'doctype' => $doctype,
                            'attachment_date' => $datestamp,                  // Carbon::now()->toDateTimeString()
                            'cpny_id' => $request->cpnyid,
                            'department_id' => $request->departementid,
                            'attachment_name' => $nameOnly,                    // nama file tanpa ekstensi (asli)
                            'folder' => $ymFolder,                    // folder di bucket
                            'filename' => $filename,                    // nama file di bucket
                            'filesize' => $sizeBytes,                   // byte
                            'extention' => $ext,
                            'status' => 'A',
                            'created_by' => $user->username,
                            'updated_by' => null,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Gagal upload attachment ke GCS', [
                            'docid' => $docid,
                            'file' => $originalName,
                            'err' => $e->getMessage(),
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal upload lampiran: '.$e->getMessage(),
                        ], 500);
                    }
                }
            }

            if (!$isDraft) {
                $t_approval_next = TrApproval::where('refnbr', $docid)
                    ->where('status', 'P')
                    ->orderBy('aprv_leveling', 'ASC')
                    ->first();

                $eid = Hashids::encode($task->id);
                $mailMaster = $this->personnelMailMasterNames($task);

                $data = [
                    'docid' => $t_approval_next->refnbr,
                    'cpnyid' => $mailMaster['company'],
                    'deptname' => $mailMaster['department'],
                    'date' => $t_approval_next->aprv_datebefore,
                    'name' => '-',
                    'createdby' => $mailMaster['creator'],
                    'docname' => 'Personnel Requisition',
                    'status' => 'P',
                    'info' => $request->job_title,
                    'url' => url('/showpersonnels/'.$eid),
                ];

                // kirim email ke semua approver di level ini (bisa multi username)
                $multiapp = array_map('trim', explode(',', (string) $t_approval_next->aprv_username));

                $email_it = User::whereIn('username', $multiapp)
                    ->where('group_cpny_id', $task->group_cpny_id)
                    ->where('status', 'A')
                    ->get();

                foreach ($email_it as $emailsit) {
                    $recipientData = array_merge($data, ['name' => $emailsit->name ?: 'User']);
                    \Mail::send('emails.mailapproveprf', $recipientData, function ($message) use ($recipientData, $emailsit) {
                        $message->to($emailsit->notification_email)
                                ->subject($recipientData['docid'].' - Waiting Approval Personnel');
                        $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'task' => $task,
                'is_draft' => $isDraft,
                'message' => $isDraft
                    ? 'Personnel Requisition saved as draft'
                    : 'Personnel Requisition submitted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Gagal menyimpan task', 'message' => $e->getMessage()], 500);
        }
    }

    public function copyPersonnel(Request $request, $hash)
    {
        $user = $request->user();
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

        if ($groupCompanyId !== 'SBY') {
            return response()->json([
                'success' => false,
                'message' => 'Copy Template hanya tersedia untuk group SBY.',
            ], 403);
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $source = Personnel::findOrFail($id);

        if (strtoupper(trim((string) $source->group_cpny_id)) !== $groupCompanyId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke Personnel Requisition ini.',
            ], 403);
        }

        if ($source->status !== 'C') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya PRF dengan status Completed yang bisa dijadikan template.',
            ], 422);
        }

        $doctype = 'PRF';
        $username = $user->username;
        $dt = Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad((string) $dt->month, 2, '0', STR_PAD_LEFT);
        $datenow = $dt->format('Y-m-d');

        DB::beginTransaction();
        try {
            $auto = $this->nextAutonbrByGroupCpnyid(
                $doctype,
                $year,
                $month,
                $groupCompanyId,
                $username,
                'PRF'
            );
            $urutan = (int) $auto['next'];
            $tglbln = substr((string) $year, 2).$month;
            $newDocid = $doctype.$tglbln.sprintf('%04d', $urutan);

            $new = Personnel::create([
                'docid' => $newDocid,
                'cpnyid' => $source->cpnyid,
                'group_cpny_id' => $groupCompanyId,
                'departementid' => $source->departementid,
                'division_id' => $source->division_id,
                'locationname' => $source->locationname,
                'budget_entity_id' => $source->budget_entity_id,
                'date' => $datenow,
                'user' => $username,
                'job_title' => $source->job_title,
                'subgrade_id' => $source->subgrade_id,
                'job_level' => $source->job_level,
                'immediate_superior' => $source->immediate_superior,
                'state_position' => $source->state_position,
                'immediate_replacement' => $source->immediate_replacement,
                'job_type' => $source->job_type,
                'reason_vacancy' => $source->reason_vacancy,
                'required' => $source->required,
                'actual' => $source->actual,
                'total_actual' => $source->total_actual,
                'education' => $source->education,
                'education_jurusan' => $source->education_jurusan,
                'experience_start' => $source->experience_start,
                'experience_end' => $source->experience_end,
                'experience_position' => $source->experience_position,
                'created_user' => $username,
                'status' => 'H',
            ]);

            foreach (
                JobResponsiblities::where('docid', $source->docid)
                    ->where('cpnyid', $source->cpnyid)
                    ->where('group_cpny_id', $groupCompanyId)
                    ->orderBy('no_job_responsiblities')
                    ->get() as $item
            ) {
                JobResponsiblities::create([
                    'docid' => $newDocid,
                    'cpnyid' => $new->cpnyid,
                    'group_cpny_id' => $groupCompanyId,
                    'no_job_responsiblities' => $item->no_job_responsiblities,
                    'job_responsibilities_descr' => $item->job_responsibilities_descr,
                    'created_user' => $username,
                    'status' => 'P',
                ]);
            }

            foreach (
                JobQualification::where('docid', $source->docid)
                    ->where('cpnyid', $source->cpnyid)
                    ->where('group_cpny_id', $groupCompanyId)
                    ->orderBy('no_job_qualification')
                    ->get() as $item
            ) {
                JobQualification::create([
                    'docid' => $newDocid,
                    'cpnyid' => $new->cpnyid,
                    'group_cpny_id' => $groupCompanyId,
                    'no_job_qualification' => $item->no_job_qualification,
                    'job_qualification_descr' => $item->job_qualification_descr,
                    'created_user' => $username,
                    'status' => 'P',
                ]);
            }

            foreach (
                TrJobtag::where('docid', $source->docid)
                    ->where('cpnyid', $source->cpnyid)
                    ->where('group_cpny_id', $groupCompanyId)
                    ->pluck('job_tags') as $tag
            ) {
                TrJobtag::create([
                    'docid' => $newDocid,
                    'cpnyid' => $new->cpnyid,
                    'group_cpny_id' => $groupCompanyId,
                    'job_tags' => $tag,
                    'created_user' => $username,
                    'status' => 'P',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'hash' => Hashids::encode($new->id),
                'message' => 'Draft PRF created from template.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function editPersonnel($hash)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $decoded = Hashids::decode($hash);
        $id = $decoded[0] ?? null;
        abort_if(!$id, 404);

        $personnel = Personnel::findOrFail($id);

        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $accessibleCompanyIds = $usercpny->pluck('cpny_id')->filter()->unique()->values();
        $companies = MsCompany::query()
            ->select('cpny_id', 'cpny_name')
            ->where('group_cpny_id', $groupCompanyId)
            ->where('status', 'A')
            ->whereIn('cpny_id', $accessibleCompanyIds)
            ->orderBy('cpny_id')
            ->get();
        $skillTags = MJobtag::select('id', 'job_tags')->get();

        $subgradings = StoSubGrading::select('subgrade_id', 'subgrade_name', 'group_grade')
            ->where('status', 'A')
            ->where('group_cpny_id', $groupCompanyId)
            ->orderBy('subgrade_id', 'ASC')
            ->get();

        $userDivisionIds = Userdivision::query()
            ->where('username', $user->username)
            ->where('status', 'A')
            ->pluck('division_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (!empty($personnel->division_id) && !in_array($personnel->division_id, $userDivisionIds)) {
            $userDivisionIds[] = $personnel->division_id;
        }

        $division = Division::query()
            ->select('division_id', 'division_name')
            ->where('status', 'A')
            ->where('group_cpny_id', $groupCompanyId)
            ->when(!empty($userDivisionIds), function ($q) use ($userDivisionIds) {
                $q->whereIn('division_id', $userDivisionIds);
            })
            ->orderBy('division_name')
            ->get();

        $departments = DepartmentHR::query()
            ->select('department_id', 'department_name', 'division_id')
            ->where('group_cpny_id', $groupCompanyId)
            ->where(function ($query) use ($personnel) {
                $query->where(function ($active) use ($personnel) {
                    $active->where('division_id', $personnel->division_id)
                        ->where('status', 'A');
                });

                if (!empty($personnel->departementid)) {
                    $query->orWhere('department_id', $personnel->departementid);
                }
            })
            ->orderBy('department_name')
            ->get();

        if (!empty($personnel->departementid) && !$departments->contains('department_id', $personnel->departementid)) {
            $currentDepartment = DepartmentHR::query()
                ->select('department_id', 'department_name', 'division_id')
                ->where('group_cpny_id', $groupCompanyId)
                ->where('department_id', $personnel->departementid)
                ->first();

            if ($currentDepartment) {
                $departments->push($currentDepartment);
            }
        }

        $attachment = TrAttachment::where('refnbr', $personnel->docid)
            ->where('cpny_id', $personnel->cpnyid)
            ->where('status', 'A')
            ->orderByDesc('attachment_date')
            ->get(['id', 'attachment_name', 'filename', 'folder', 'extention', 'created_by', 'attachment_date']);

        $jobres = JobResponsiblities::where('docid', $personnel->docid)
            ->where('cpnyid', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->get();

        $jobqua = JobQualification::where('docid', $personnel->docid)
            ->where('cpnyid', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->get();

        $selectedTags = TrJobtag::where('docid', $personnel->docid)
            ->where('cpnyid', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->pluck('job_tags')
            ->filter()
            ->values()
            ->toArray();

        $activeUsers = User::select('username', 'name')
            ->where('status', 'A')
            ->where('group_cpny_id', $groupCompanyId)
            ->orderBy('name', 'ASC')
            ->get();

        $companyBudgets = HrCompanyBudget::query()
            ->select('cpnyid', 'budget_entity_id')
            ->where('group_cpny_id', $groupCompanyId)
            ->where('status', 'A')
            ->whereNull('deleted_at')
            ->orderBy('budget_entity_id')
            ->get();

        $entityNames = MsEntity::query()
            ->where('group_cpny_id', $groupCompanyId)
            ->where('status', 'A')
            ->whereIn('entity_id', $companyBudgets->pluck('budget_entity_id')->filter()->unique())
            ->pluck('entity_name', 'entity_id');

        $companyBudgets->each(function ($budget) use ($entityNames) {
            $budget->budget_entity_name = $entityNames->get($budget->budget_entity_id);
        });

        $isSby = $groupCompanyId === 'SBY';
        $view = $isSby
            ? 'pages.personnels.editpersonnels_sby'
            : 'pages.personnels.editpersonnels';

        return view($view, [
            'companies' => $companies,
            'usercpny' => $usercpny,
            'usercpny2' => $usercpny2,
            'userdept' => $userdept,
            'userdept2' => $userdept2,
            'skillTags' => $skillTags,
            'division' => $division,
            'departments' => $departments,
            'personnel' => $personnel,
            'cpnyid' => $personnel->cpnyid,
            'group_cpny_id' => $personnel->group_cpny_id,
            'attachment' => $attachment,
            'subgradings' => $subgradings,
            'jobres' => collect($jobres),
            'jobqua' => collect($jobqua),
            'selectedTags' => is_array($selectedTags) ? $selectedTags : [],
            'activeUsers' => $activeUsers,
            'companyBudgets' => $companyBudgets,
            'isSby' => $isSby,
            'hash' => $hash,
        ]);
    }

    public function updatePersonnel(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);
        $user = $request->user();
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));
        $isDraft = $request->boolean('is_draft');

        if ($isDraft && $groupCompanyId !== 'SBY') {
            return response()->json(['message' => 'Save as Draft hanya tersedia untuk group SBY.'], 403);
        }

        // Validasi utama
        $rules = [
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            // 'attachments.*' => 'file|max:20480', // opsional, 20MB
        ];

        if ($isDraft) {
            $rules += [
                'job_title' => 'nullable|string',
                'subgrade_id' => 'nullable|string',
                'immediate_superior' => 'nullable|string',
                'state_position' => 'nullable|string',
                'job_type' => 'nullable|string|in:Replacement,New',
                'reason_vacancy' => 'nullable|string',
                'required' => 'nullable|integer|min:0',
                'actual' => 'nullable|integer|min:0',
                'total_actual' => 'nullable|integer|min:0',
                'budget_entity_id' => 'nullable|string',
            ];
        } else {
            $rules += [
                'job_title' => 'required|string',
                'subgrade_id' => 'required|string',
                'immediate_superior' => 'required|string',
                'state_position' => 'required|string',
                'job_type' => 'required|string|in:Replacement,New',
                'reason_vacancy' => 'required|string',
                'required' => 'required|integer|min:0',
                'actual' => 'required|integer|min:0',
                'total_actual' => 'required|integer|min:0',
                'budget_entity_id' => $groupCompanyId === 'SBY' ? 'required|string' : 'nullable|string',
            ];
        }

        $request->validate($rules);

        if (!$isDraft) {
            if ($groupCompanyId === 'SBY') {
                $hasCompanyBudget = HrCompanyBudget::query()
                    ->where('group_cpny_id', $groupCompanyId)
                    ->where('cpnyid', $request->cpnyid)
                    ->where('budget_entity_id', $request->budget_entity_id)
                    ->where('status', 'A')
                    ->whereNull('deleted_at')
                    ->exists();

                if (!$hasCompanyBudget) {
                    return response()->json(['message' => 'Budget Company tidak valid untuk Company yang dipilih.'], 422);
                }
            }

            $hasSite = CompanyAddress::query()
                ->where('group_cpny_id', $groupCompanyId)
                ->where('sitelocation', $request->siteid)
                ->exists();

            if (!$hasSite) {
                return response()->json(['message' => 'Placement Location tidak valid untuk group company user.'], 422);
            }
        }

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = (int) $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'PRF';
            $datestamp = Carbon::now()->toDateTimeString();
            $personnel = Personnel::findOrFail($id);
            $originalCpnyid = $personnel->cpnyid;
            $originalGroupCompanyId = $personnel->group_cpny_id ?: $groupCompanyId;

            // Ambil grading (termasuk group_grade untuk logika approval)
            $grading = null;
            if ($request->filled('subgrade_id')) {
                $grading = StoSubGrading::where('subgrade_id', $request->subgrade_id)
                    ->where('status', 'A')
                    ->where('group_cpny_id', $groupCompanyId)
                    ->first();
            }

            if (!$isDraft && !$grading) {
                return response()->json([
                    'error' => 'Gagal menyimpan personnel',
                    'message' => 'Subgrading tidak ditemukan/Non-aktif',
                ], 422);
            }

            // $groupGrade = (string)($grading->group_grade ?? ''); // ex: "Staff" / "Manager"
            $groupGrade = (string) ($grading->group_grade ?? '');
            // $positionCondition = strtolower(trim($request->job_type.' '.$groupGrade));
            $positionCondition = strtolower(trim($groupGrade));

            // Update header personnel
            $personnel->update([
                'cpnyid' => $request->cpnyid,
                'group_cpny_id' => $groupCompanyId,
                'departementid' => $request->departementid,
                'division_id' => $request->division_id,
                'date' => $datenow,
                'locationname' => $request->siteid ?? null, // simpan ID site
                'budget_entity_id' => $request->budget_entity_id,
                // 'user' => $user->username,
                'job_title' => $request->job_title,
                'subgrade_id' => $request->subgrade_id,
                'job_level' => $grading->subgrade_name ?? null,
                'immediate_superior' => $request->immediate_superior,
                'state_position' => $request->state_position,
                'immediate_replacement' => $request->immediate_replacement,
                'job_type' => $request->job_type,
                'reason_vacancy' => $request->reason_vacancy,
                'required' => $request->required ?? 0,
                'actual' => $request->actual ?? 0,
                'total_actual' => $request->total_actual ?? 0,
                'education' => $request->education,
                'experience_start' => $request->experience_start,
                'experience_end' => $request->experience_end,
                // 'created_user' => $user->username,
                'status' => $isDraft ? 'H' : 'P',
            ]);

            $docid = $personnel->docid;

            // ===== Rebuild Approval Lines (hapus pending lama, build ulang dari master) =====
            // Ambil baris approval: Normal + Condition yang cocok dengan group_grade
            if (!$isDraft) {
                $msApproval = MsApproval::where('aprv_doctype', $doctype)
                    ->where('aprv_cpnyid', $request->cpnyid)
                    ->where('aprv_departementid', $request->departementid)
                    ->where('status', 'A')
                    ->where(function ($q) use ($positionCondition) {
                        $q->where('aprv_type', 'Normal')
                        ->orWhere(function ($q2) use ($positionCondition) {
                            $q2->where('aprv_type', 'Condition')
                                ->whereRaw('LOWER(TRIM(aprv_condition)) = ?', [trim($positionCondition)]);
                        });
                    })
                    ->orderBy('aprv_leveling', 'asc')
                    ->get();

                if ($msApproval->isEmpty()) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Approval line belum di-setup (Normal/Condition) untuk kombinasi ini. Hubungi IT.',
                    ], 422);
                }

                $canEdit = GroupAccspecific::where('username', $user->username)
                    ->where('group_cpny_id', $groupCompanyId)
                    ->where('group_access_id', 'EDIT')
                    ->where('status', 'A')
                    ->exists();

                if (!$canEdit) {
                    // Hapus pending lama agar tidak dobel
                    // TrApproval::where('refnbr', $docid)->where('status', 'P')->delete();

                    // Sisipkan approval baru
                    foreach ($msApproval as $row) {
                        $isFirstLevel = ((int) $row->aprv_leveling === (int) $msApproval->min('aprv_leveling'));
                        TrApproval::create([
                            'refnbr' => $docid,
                            'aprv_leveling' => $row->aprv_leveling,
                            'aprv_doctype' => $row->aprv_doctype,
                            'aprv_cpnyid' => $row->aprv_cpnyid,
                            'aprv_departementid' => $row->aprv_departementid,
                            'aprv_username' => $row->aprv_username,
                            'aprv_name' => $row->aprv_name,
                            'aprv_datebefore' => $isFirstLevel ? $datestamp : null,
                            'aprv_type' => $row->aprv_type,        // Normal / Condition
                            'aprv_condition' => $row->aprv_condition,   // Staff / Manager (jika ada)
                            'status' => 'P',
                            'created_by' => $user->username,
                        ]);
                    }
                }
            }

            // ===== Rebuild Responsibilities =====
            if ($request->has('responsibilities')) {
                JobResponsiblities::where('docid', $docid)
                    ->where('cpnyid', $originalCpnyid)
                    ->where('group_cpny_id', $originalGroupCompanyId)
                    ->delete();
                foreach ($request->responsibilities as $idx => $responsibility) {
                    if (trim((string) $responsibility) === '') {
                        continue;
                    }
                    JobResponsiblities::create([
                        'docid' => $docid,
                        'cpnyid' => $request->cpnyid,
                        'group_cpny_id' => $groupCompanyId,
                        'no_job_responsiblities' => $idx + 1,
                        'job_responsibilities_descr' => $responsibility,
                        'created_user' => $user->username,
                        'status' => 'P',
                    ]);
                }
            }

            // ===== Rebuild Qualification =====
            if ($request->has('qualification')) {
                JobQualification::where('docid', $docid)
                    ->where('cpnyid', $originalCpnyid)
                    ->where('group_cpny_id', $originalGroupCompanyId)
                    ->delete();
                foreach ($request->qualification as $idx => $qualification) {
                    if (trim((string) $qualification) === '') {
                        continue;
                    }
                    JobQualification::create([
                        'docid' => $docid,
                        'cpnyid' => $request->cpnyid,
                        'group_cpny_id' => $groupCompanyId,
                        'no_job_qualification' => $idx + 1,
                        'job_qualification_descr' => $qualification,
                        'created_user' => $user->username,
                        'status' => 'P',
                    ]);
                }
            }

            // ===== (Opsional) Tags — jika kamu juga mau perbarui di edit =====
            if ($request->has('tags')) {
                TrJobtag::where('docid', $docid)
                    ->where('cpnyid', $originalCpnyid)
                    ->where('group_cpny_id', $originalGroupCompanyId)
                    ->delete();
                foreach ($request->tags as $tag) {
                    $t = trim((string) $tag);
                    if ($t === '') {
                        continue;
                    }

                    TrJobtag::create([
                        'docid' => $docid,
                        'cpnyid' => $request->cpnyid,
                        'group_cpny_id' => $groupCompanyId,
                        'job_tags' => $t,
                        'created_user' => $user->username,
                        'status' => 'P',
                    ]);

                    if (!MJobtag::where('job_tags', $t)->exists()) {
                        MJobtag::create([
                            'job_tags' => $t,
                            'created_user' => $user->username,
                            'status' => 'A',
                        ]);
                    }
                }
            }

            // ===== Upload Attachment ke GCS + simpan TrAttachment =====
            if ($request->hasFile('attachments')) {
                $config = config('filesystems.disks.gcs');
                $storage = new StorageClient([
                    'projectId' => $config['project_id'],
                    'keyFilePath' => $config['key_file'],
                ]);
                $bucket = $storage->bucket($config['bucket']);
                $ymFolder = 'att-job-career/'.$doctype.'/'.$year; // ex: att-job-career/PRF/2025

                foreach ($request->file('attachments') as $file) {
                    if (!$file->isValid()) {
                        continue;
                    }

                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $ext = $file->getClientOriginalExtension();
                    $randomPrefix = md5(random_int(1, 99999999)).'-'.time();
                    $newFilename = $randomPrefix.'.'.$ext;
                    $objectPath = "{$ymFolder}/{$newFilename}";

                    try {
                        $bucket->upload(
                            fopen($file->getPathname(), 'r'),
                            [
                                'name' => $objectPath,
                                'predefinedAcl' => 'private',
                            ]
                        );

                        TrAttachment::create([
                            'refnbr' => $docid,
                            'doctype' => $doctype,
                            'attachment_date' => $datestamp,
                            'cpny_id' => $request->cpnyid,
                            'department_id' => $request->departementid,
                            'attachment_name' => pathinfo($originalName, PATHINFO_FILENAME),
                            'folder' => $ymFolder,
                            'filename' => $newFilename,
                            'filesize' => $file->getSize(),
                            'extention' => $ext,
                            'status' => 'A',
                            'created_by' => $user->username,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('GCS upload failed (updatePersonnel)', [
                            'docid' => $docid,
                            'objectPath' => $objectPath,
                            'error' => $e->getMessage(),
                        ]);
                        DB::rollBack();

                        return response()->json([
                            'error' => 'Gagal upload attachment',
                            'message' => $e->getMessage(),
                        ], 500);
                    }
                }
            }

            // ===== Notifikasi ke approver berikutnya =====
            if (!$isDraft) {
                $next = TrApproval::where('refnbr', $docid)
                    ->where('status', 'P')
                    ->orderBy('aprv_leveling', 'ASC')
                    ->first();

                $eid = Hashids::encode($personnel->id);
                $mailMaster = $this->personnelMailMasterNames($personnel);

                if (!$canEdit) {
                    if ($next) {
                        // jika multi user dipisah comma
                        $usernames = array_map('trim', explode(',', $next->aprv_username));
                        $emailTargets = User::whereIn('username', $usernames)
                            ->where('group_cpny_id', $personnel->group_cpny_id)
                            ->where('status', 'A')
                            ->get();

                        $mailData = [
                            'docid' => $next->refnbr,
                            'cpnyid' => $mailMaster['company'],
                            'deptname' => $mailMaster['department'],
                            'date' => $next->aprv_datebefore,
                            'name' => '-',
                            'createdby' => $mailMaster['creator'],
                            'docname' => 'Personnel Requisition',
                            'status' => 'P',
                            'info' => $request->job_title,
                            'url' => url('/showpersonnels/'.$eid),
                        ];

                        foreach ($emailTargets as $recipient) {
                            $recipientData = array_merge($mailData, ['name' => $recipient->name ?: 'User']);
                            \Mail::send('emails.mailapproveprf', $recipientData, function ($message) use ($recipientData, $recipient) {
                                $message->to($recipient->notification_email)
                                    ->subject($recipientData['docid'].' - Waiting Approval Personnel')
                                    ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                            });
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'personnel' => $personnel,
                'is_draft' => $isDraft,
                'message' => $isDraft
                    ? 'Personnel Requisition saved as draft'
                    : 'Personnel Requisition updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Gagal menyimpan personnel',
                'message' => $e->getMessage(),
            ], 500);
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

        $companyName = MsCompany::query()
            ->where('cpny_id', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->value('cpny_name');

        $departmentName = DepartmentHR::query()
            ->where('department_id', $personnel->departementid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->value('department_name');

        $divisionName = Division::query()
            ->where('division_id', $personnel->division_id)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->value('division_name');

        // === Approval pakai TrApproval (refnbr & aprv_leveling) ===
        $approval = TrApproval::where('refnbr', $personnel->docid)
            ->where('aprv_cpnyid', $personnel->cpnyid)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->orderBy('aprv_leveling')
            ->get();

        // === Detail lain tetap ===
        $jobres = JobResponsiblities::query()
            ->where('docid', $personnel->docid)
            ->where('cpnyid', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->get();

        $jobqua = JobQualification::query()
            ->where('docid', $personnel->docid)
            ->where('cpnyid', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->get();

        $jobtag = TrJobtag::query()
            ->where('docid', $personnel->docid)
            ->where('cpnyid', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->get();

        // === Attachment di GCS + generate Signed URL ===
        $attachments = TrAttachment::where('refnbr', $personnel->docid)
            ->where('cpny_id', $personnel->cpnyid)
            ->where('status', 'A')
            ->orderBy('created_at', 'asc')
            ->get();

        // Build signed URLs (aman, sementara, private)
        if ($attachments->isNotEmpty()) {
            $config = config('filesystems.disks.gcs');
            $storage = new StorageClient([
                'projectId' => $config['project_id'],
                'keyFilePath' => $config['key_file'],
            ]);
            $bucket = $storage->bucket($config['bucket']);

            foreach ($attachments as $at) {
                try {
                    $path = rtrim($at->folder ?? '', '/').'/'.ltrim($at->filename ?? '', '/');
                    $object = $bucket->object($path);
                    // berlaku 10 menit; silakan ubah sesuai kebutuhan
                    $at->signed_url = $object->signedUrl(new \DateTime('+10 minutes'));
                } catch (\Throwable $e) {
                    // kalau gagal generate URL, kosongkan saja supaya link tidak muncul
                    $at->signed_url = null;
                }
            }
        }

        $canEdit = GroupAccspecific::where('username', $user->username)
            ->where('group_cpny_id', strtoupper(trim((string) $user->group_cpny_id)))
            ->where('group_access_id', 'EDIT')
            ->where('status', 'A')
            ->exists();

        $loginUsername = $user->username ?? $user->name ?? null;
        $isApprover = TrApproval::where('refnbr', $personnel->docid)
            ->where('aprv_doctype', 'PRF')
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->get()
            ->contains(function ($row) use ($loginUsername) {
                $list = preg_split('/[;,]/', (string) $row->aprv_username);
                $list = array_map('trim', $list);
                return in_array(strtolower((string) $loginUsername), array_map('strtolower', $list), true);
            });

        return view('pages.personnels.showpersonnels', [
            'personnel'  => $personnel,
            'companyName' => $companyName,
            'departmentName' => $departmentName,
            'divisionName' => $divisionName,
            'jobres'     => $jobres,
            'jobqua'     => $jobqua,
            'approval'   => $approval,
            'attachment' => $attachments,
            'jobtag'     => $jobtag,
            'canEdit'    => $canEdit,
            'isApprover' => $isApprover,
            'hash'       => $hash,
        ]);
    }

    public function printPdfPersonnel($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $personnel = Personnel::findOrFail($id);

        $companyName = MsCompany::query()
            ->where('cpny_id', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->value('cpny_name');

        $departmentName = DepartmentHR::query()
            ->where('department_id', $personnel->departementid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->value('department_name');

        $divisionName = Division::query()
            ->where('division_id', $personnel->division_id)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->value('division_name');

        $approval = TrApproval::where('refnbr', $personnel->docid)
            ->where('aprv_cpnyid', $personnel->cpnyid)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->orderBy('aprv_leveling')
            ->get();

        $jobres = JobResponsiblities::query()
            ->where('docid', $personnel->docid)
            ->where('cpnyid', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->get();

        $jobqua = JobQualification::query()
            ->where('docid', $personnel->docid)
            ->where('cpnyid', $personnel->cpnyid)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->get();

        $statusDoc = match ($personnel->status) {
            'D' => 'Revise',
            'H' => 'Draft',
            'P' => 'On Progress',
            'C' => 'Completed',
            'X' => 'Cancelled',
            'R' => 'Rejected',
            default => 'Unknown',
        };

        $createdByName = ucwords(strtolower((string) ($personnel->created_user ?? '-')));
        $reqDateFmt = $personnel->date
            ? Carbon::parse($personnel->date)->format('l, d F Y')
            : '-';

        $immediateSuperiorName = User::query()
            ->where('username', $personnel->immediate_superior)
            ->value('name');

        $pdf = \PDF::loadView('pages.personnels.pdf_personnel', [
            'personnel' => $personnel,
            'companyName' => $companyName,
            'departmentName' => $departmentName,
            'divisionName' => $divisionName,
            'approval' => $approval,
            'jobres' => $jobres,
            'jobqua' => $jobqua,
            'statusDoc' => $statusDoc,
            'createdByName' => $createdByName,
            'reqDateFmt' => $reqDateFmt,
            'immediateSuperiorName' => $immediateSuperiorName,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("PRF_{$personnel->docid}.pdf");
    }

    public function fetchComments(Request $request, $refnbr)
    {
        $request->validate([
            'cpnyid' => 'required|string|max:50',
        ]);

        Personnel::query()
            ->where('docid', $refnbr)
            ->where('cpnyid', $request->cpnyid)
            ->firstOrFail();

        $comments = TrMessage::where('refnbr', $refnbr)
            ->where('cpny_id', $request->cpnyid)
            ->orderBy('message_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'comments' => $comments,
        ]);
    }

    public function storeComment(Request $request, $refnbr)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
            'cpnyid' => 'required|string|max:50',
        ]);

        $user = Auth::user();   // ambil user login

        Personnel::query()
            ->where('docid', $refnbr)
            ->where('cpnyid', $request->cpnyid)
            ->firstOrFail();

        $comment = TrMessage::create([
            'refnbr' => $refnbr,
            'doctype' => 'PRF',
            'message_date' => now(),
            'cpny_id' => $request->cpnyid,
            'department_id' => $user->departmentid ?? null,
            'username' => $user->username,
            'name' => $user->name,
            'message' => $request->comment,
            'status' => 'A',
            'created_by' => $user->username,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Comment added successfully!',
            'comment' => $comment,
        ]);
    }

    public function approvePersonnel(Request $request, $docid)
    {
        $request->validate(['cpnyid' => 'required|string|max:50']);
        $datestamp = Carbon::now()->toDateTimeString();
        $user = request()->user();

        $personnel = Personnel::where('docid', $docid)
            ->where('cpnyid', $request->cpnyid)
            ->first();
        if (!$personnel) {
            return response()->json(['success' => false, 'message' => 'PRF not found'], 404);
        }

        // Hitung sisa approval yang masih PENDING
        $countPending = TrApproval::where('refnbr', $personnel->docid)
            ->where('aprv_cpnyid', $personnel->cpnyid)
            ->where('status', 'P')
            ->count();

        // Ambil baris approval yang sedang menunggu & sesuai user
        $tApproval = TrApproval::where('refnbr', $personnel->docid)
            ->where('aprv_cpnyid', $personnel->cpnyid)
            ->where('status', 'P')
            ->where('aprv_username', 'like', '%'.$user->username.'%')
            ->orderBy('aprv_leveling')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
        }

        // Approve current level
        $tApproval->status = 'A';
        $tApproval->aprv_dateafter = $datestamp;
        $tApproval->aprv_username = $user->username; // lock who approved
        $tApproval->aprv_name = $user->name;
        $tApproval->save();

        // Jika ini approval terakhir -> close PRF
        if ($countPending === 1) {
            $personnel->status = 'C';
            $personnel->completed_user = $user->username;
            $personnel->completed_at = $datestamp;
            $personnel->save();

            // proses lanjutan setelah complete
            app('App\Http\Controllers\PersonnelController')->insert_jobposting($docid, $personnel->cpnyid);

            $mailMaster = $this->personnelMailMasterNames($personnel);
            $creator = User::query()
                ->where('username', $personnel->created_user)
                ->where('group_cpny_id', $personnel->group_cpny_id)
                ->where('status', 'A')
                ->first();

            if ($creator && $creator->notification_email) {
                $completedData = [
                    'docid' => $personnel->docid,
                    'cpnyid' => $mailMaster['company'],
                    'deptname' => $mailMaster['department'],
                    'date' => $datestamp,
                    'name' => $creator->name ?: 'User',
                    'createdby' => $mailMaster['creator'],
                    'docname' => 'Personnel Requisition',
                    'status' => 'C',
                    'info' => $personnel->job_title,
                    'url' => url('/showpersonnels/'.Hashids::encode($personnel->id)),
                ];

                \Mail::send('emails.mailapproveprf', $completedData, function ($message) use ($completedData, $creator) {
                    $message->to($creator->notification_email)
                        ->subject($completedData['docid'].' - Completed Personnel')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }

            return response()->json(['success' => true, 'message' => 'Task approved & completed']);
        }

        // Masih ada approval berikutnya
        $tNext = TrApproval::where('refnbr', $personnel->docid)
            ->where('aprv_cpnyid', $personnel->cpnyid)
            ->where('status', 'P')
            ->orderBy('aprv_leveling', 'ASC')
            ->first();

        // Safety check
        if ($tNext) {
            $tNext->aprv_datebefore = $datestamp;
            $tNext->save();

            // Kirim email ke approver berikutnya
            $eid = \Hashids::encode($personnel->id);
            $mailMaster = $this->personnelMailMasterNames($personnel);
            $data = [
                'docid' => $tNext->refnbr,
                'cpnyid' => $mailMaster['company'],
                'deptname' => $mailMaster['department'],
                'date' => $tNext->aprv_datebefore,
                'name' => '-',
                'createdby' => $mailMaster['creator'],
                'docname' => 'Personnel Requisition',
                'status' => 'P',
                'info' => $personnel->job_title,
                'url' => url('/showpersonnels/'.$eid),
            ];

            $multiapp = explode(',', $tNext->aprv_username);
            $recipients = User::whereIn('username', $multiapp)
                ->where('group_cpny_id', $personnel->group_cpny_id)
                ->where('status', 'A')
                ->get();

            foreach ($recipients as $rcp) {
                $recipientData = array_merge($data, ['name' => $rcp->name ?: 'User']);
                \Mail::send('emails.mailapproveprf', $recipientData, function ($message) use ($recipientData, $rcp) {
                    $message->to($rcp->notification_email)
                            ->subject($recipientData['docid'].' - Waiting Approval Personnel')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectPersonnel(Request $request, $docid)
    {
        $request->validate(['cpnyid' => 'required|string|max:50']);
        $datestamp = Carbon::now()->toDateTimeString();
        $user = request()->user();

        $personnel = Personnel::where('docid', $docid)
            ->where('cpnyid', $request->cpnyid)
            ->first();
        if (!$personnel) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        $tApproval = TrApproval::where('refnbr', $personnel->docid)
            ->where('aprv_cpnyid', $personnel->cpnyid)
            ->where('status', 'P')
            ->where('aprv_username', 'like', '%'.$user->username.'%')
            ->orderBy('aprv_leveling')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
        }

        // Set current step -> Rejected
        $tApproval->status = 'R';
        $tApproval->aprv_dateafter = $datestamp;
        $tApproval->save();

        // Set header -> Rejected
        $personnel->status = 'R';
        $personnel->save();

        // Batalkan semua sisa approval yang masih P
        TrApproval::where('refnbr', $personnel->docid)
            ->where('aprv_cpnyid', $personnel->cpnyid)
            ->where('status', 'P')
            ->update(['status' => 'X']);

        // Kirim email ke creator
        $eid = \Hashids::encode($personnel->id);
        $mailMaster = $this->personnelMailMasterNames($personnel);
        $data = [
            'docid' => $tApproval->refnbr,
            'cpnyid' => $mailMaster['company'],
            'deptname' => $mailMaster['department'],
            'date' => $tApproval->aprv_datebefore,
            'name' => '-',
            'createdby' => $mailMaster['creator'],
            'docname' => 'Personnel Requisition',
            'status' => 'R',
            'info' => $personnel->job_title,
            'url' => url('/showpersonnels/'.$eid),
        ];

        $creator = User::where('username', $personnel->created_user)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->where('status', 'A')
            ->get();

        foreach ($creator as $rcp) {
            $recipientData = array_merge($data, ['name' => $rcp->name ?: 'User']);
            \Mail::send('emails.mailapproveprf', $recipientData, function ($message) use ($recipientData, $rcp) {
                $message->to($rcp->notification_email)
                        ->subject($recipientData['docid'].' - Rejected Personnel')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        // Kirim komentar (alasan) via controller existing
        $id = $personnel->id;
        $doctype = 'PRF';
        app('App\Http\Controllers\SendCommentController')->sendmsgWithCpnyid(
            $id,
            $doctype,
            $personnel->cpnyid,
            $request
        );

        return response()->json(['success' => true, 'message' => 'Personnel rejected successfully']);
    }

    public function revisePersonnel(Request $request, $docid)
    {
        $request->validate(['cpnyid' => 'required|string|max:50']);
        $datestamp = Carbon::now()->toDateTimeString();
        $user = request()->user();

        $personnel = Personnel::where('docid', $docid)
            ->where('cpnyid', $request->cpnyid)
            ->first();
        if (!$personnel) {
            return response()->json(['success' => false, 'message' => 'Personnel not found'], 404);
        }

        $tApproval = TrApproval::where('refnbr', $personnel->docid)
            ->where('aprv_cpnyid', $personnel->cpnyid)
            ->where('status', 'P')
            ->where('aprv_username', 'like', '%'.$user->username.'%')
            ->orderBy('aprv_leveling')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
        }

        // Set current step -> Revise
        $tApproval->status = 'D';
        $tApproval->aprv_dateafter = $datestamp;
        $tApproval->save();

        // Header -> Revise
        $personnel->status = 'D';
        $personnel->save();

        // Batalkan semua sisa approval yang masih P
        TrApproval::where('refnbr', $personnel->docid)
            ->where('aprv_cpnyid', $personnel->cpnyid)
            ->where('status', 'P')
            ->update(['status' => 'X']);

        // Email ke creator
        $eid = \Hashids::encode($personnel->id);
        $mailMaster = $this->personnelMailMasterNames($personnel);
        $data = [
            'docid' => $tApproval->refnbr,
            'cpnyid' => $mailMaster['company'],
            'deptname' => $mailMaster['department'],
            'date' => $tApproval->aprv_datebefore,
            'name' => '-',
            'createdby' => $mailMaster['creator'],
            'docname' => 'Personnel Requisition',
            'status' => 'D',
            'info' => $personnel->job_title,
            'url' => url('/showpersonnels/'.$eid),
        ];

        $creator = User::where('username', $personnel->created_user)
            ->where('group_cpny_id', $personnel->group_cpny_id)
            ->where('status', 'A')
            ->get();

        foreach ($creator as $rcp) {
            $recipientData = array_merge($data, ['name' => $rcp->name ?: 'User']);
            \Mail::send('emails.mailapproveprf', $recipientData, function ($message) use ($recipientData, $rcp) {
                $message->to($rcp->notification_email)
                        ->subject($recipientData['docid'].' - Revise Personnel')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        // Simpan komentar (alasan revisi)
        $id = $personnel->id;
        $doctype = 'PRF';
        app('App\Http\Controllers\SendCommentController')->sendmsgWithCpnyid(
            $id,
            $doctype,
            $personnel->cpnyid,
            $request
        );

        return response()->json(['success' => true, 'message' => 'Personnel revised successfully']);
    }


    public function checkApproval(Request $request, $refnbr, $action)
    {
        $request->validate(['cpnyid' => 'required|string|max:50']);
        $user = Auth::user();
        if (!$user) {
            return response()->json(['canPerformAction' => false]);
        }

        // Cek apakah ada pending step yang memuat user ini
        $qUser = TrApproval::where('refnbr', $refnbr)
            ->where('aprv_cpnyid', $request->cpnyid)
            ->where('status', 'P')
            ->where('aprv_username', 'like', '%'.$user->username.'%');

        // Untuk approve/reject/revise: harus sudah "dibuka" (aprv_datebefore != null)
        if (in_array($action, ['approve', 'reject', 'revise'], true)) {
            $qUser->whereNotNull('aprv_datebefore');
        }

        $hasPendingForUser = $qUser->exists();

        // Hard guard: user hanya boleh bertindak jika dia berada di step PENDING TERENDAH (next approver)
        $next = TrApproval::where('refnbr', $refnbr)
            ->where('aprv_cpnyid', $request->cpnyid)
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

    public function insert_jobposting($id, $cpnyid = null)
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

            $personnel = Personnel::with(['divisionRef'])
                ->where('docid', $id)
                ->when(
                    $cpnyid,
                    fn ($query) => $query->where('cpnyid', $cpnyid),
                    fn ($query) => $query->where('group_cpny_id', strtoupper(trim((string) $user->group_cpny_id)))
                )
                ->first();

            if (!$personnel) {
                throw new \RuntimeException('Personnel tidak ditemukan');
            }

            $groupCompanyId = strtoupper(trim((string) $personnel->group_cpny_id));
            if ($groupCompanyId === '') {
                throw new \RuntimeException('Group company Personnel belum tersedia');
            }

            // Sequence JOB terpisah per group company dan otomatis dibuat per bulan/tahun.
            $urutan = DB::connection('mysql3')->transaction(function () use (
                $doctype,
                $groupCompanyId,
                $year,
                $month,
                $user
            ) {
                $autonbr = AutonbrJobportal::query()
                    ->lockForUpdate()
                    ->where('doctype', $doctype)
                    ->where('cpnyid', $groupCompanyId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('status', 'A')
                    ->first();

                if (!$autonbr) {
                    $autonbr = AutonbrJobportal::create([
                        'doctype' => $doctype,
                        'cpnyid' => $groupCompanyId,
                        'year' => $year,
                        'month' => $month,
                        'number' => 0,
                        'number_temp' => 0,
                        'status' => 'A',
                        'created_user' => $user->username ?? 'system',
                    ]);
                }

                $next = ((int) ($autonbr->number ?? 0)) + 1;
                $autonbr->number = $next;
                $autonbr->save();

                return $next;
            });

            $tglbln = substr($year, 2).$month;
            $docid = $doctype.$tglbln.sprintf('%04d', $urutan);

            $task = Jobposting::create([
                'docid' => $docid,
                'refid' => $personnel->docid,
                'cpnyid' => $personnel->cpnyid,
                'group_cpny_id' => $groupCompanyId,
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
                'status' => 'U',
            ]);

            $jobres = JobResponsiblities::where('docid', $id)
                ->where('cpnyid', $personnel->cpnyid)
                ->where('group_cpny_id', $personnel->group_cpny_id)
                ->get();

            foreach ($jobres as $jr) {
                JobpostingResponsiblities::create([
                    'docid' => $docid,
                    'cpnyid' => $jr->cpnyid,
                    'group_cpny_id' => $jr->group_cpny_id,
                    'refid' => $jr->docid,
                    'no_job_responsiblities' => $jr->no_job_responsiblities,
                    'job_responsibilities_descr' => $jr->job_responsibilities_descr,
                    'created_user' => $jr->created_user,
                    'status' => 'P',
                ]);
            }

            // nomor awal untuk qualification
            $no = 1;

            // Education
            $eduParts = array_filter([
                $personnel->education ?? null,
                // $personnel->education_jurusan ?? null,
                'Semua Jurusan',
            ], fn ($v) => filled($v));

            if (count($eduParts)) {
                JobpostingQualification::create([
                    'docid' => $docid,
                    'cpnyid' => $personnel->cpnyid,
                    'group_cpny_id' => $personnel->group_cpny_id,
                    'refid' => $personnel->docid,
                    'no_job_qualification' => $no++,
                    'job_qualification_descr' => 'Minimal Pendidikan '.implode(' ', $eduParts),
                    'created_user' => $user->username,
                    'status' => 'P',
                ]);
            }

            // Experience
            $start = $personnel->experience_start ?? null;
            // $role  = $personnel->experience_position ?? null;
            $role = $personnel->job_title ?? null;

            $desc = null;
            if (filled($start) && filled($role)) {
                $desc = "Memiliki Pengalaman {$start} tahun sebagai {$role}";
            } elseif (filled($start)) {
                $desc = "Memiliki Pengalaman {$start} tahun";
            } elseif (filled($role)) {
                $desc = "Memiliki Pengalaman sebagai {$role}";
            }

            if ($desc) {
                JobpostingQualification::create([
                    'docid' => $docid,
                    'cpnyid' => $personnel->cpnyid,
                    'group_cpny_id' => $personnel->group_cpny_id,
                    'refid' => $personnel->docid,
                    'no_job_qualification' => $no++,
                    'job_qualification_descr' => $desc,
                    'created_user' => $user->username,
                    'status' => 'P',
                ]);
            }

            $jobqua = JobQualification::where('docid', $id)
                ->where('cpnyid', $personnel->cpnyid)
                ->where('group_cpny_id', $personnel->group_cpny_id)
                ->get();

            foreach ($jobqua as $jq) {
                JobpostingQualification::create([
                    'docid' => $docid,
                    'cpnyid' => $jq->cpnyid,
                    'group_cpny_id' => $jq->group_cpny_id,
                    'refid' => $jq->docid,
                    'no_job_qualification' => $jq->no_job_qualification,
                    'job_qualification_descr' => $jq->job_qualification_descr,
                    'created_user' => $jq->created_user,
                    'status' => 'P',
                ]);
            }

            $jobtag = TrJobtag::where('docid', $id)
                ->where('cpnyid', $personnel->cpnyid)
                ->where('group_cpny_id', $personnel->group_cpny_id)
                ->get();

            foreach ($jobtag as $jt) {
                Jobpostingtag::create([
                    'docid' => $docid,
                    'cpnyid' => $jt->cpnyid,
                    'group_cpny_id' => $jt->group_cpny_id,
                    'refid' => $jt->docid,
                    'job_tags' => $jt->job_tags,
                    'created_user' => $jt->created_user,
                    'status' => 'P',
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
        $groupCompanyId = strtoupper(trim((string) request()->user()->group_cpny_id));

        $sites = CompanyAddress::query()
            ->where('group_cpny_id', $groupCompanyId)
            ->pluck('sitelocation')
            ->map(fn ($site) => trim((string) $site))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn ($site) => ['site' => $site]);

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
            ->select('d2.departement_id', 'd2.departement_name', 'e.id', 'd2.parent_id', 'e.employee_level')
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
            ->select('e.id as employee_id', 'e.employee_name', 'e.employee_company', 'd.departement_id', 'd.departement_name', 'd.subgrade_name', 'd.parent_id')
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
            ->select('e.id as employee_id', 'e.employee_name', 'e.employee_company', 'd.departement_id', 'd.departement_name', 'd.subgrade_name', 'd.parent_id', 'd.subgrade_id')
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

    public function getParentJobInfo($parentId, $departementId, $deptId)
    {
        // Ambil 1 orang selain VACANT di parent_id tsb
        $employee = DB::table('hr_ms_sto_employee as e')
            ->join('hr_ms_sto_departement as d', 'e.departement_id', '=', 'd.departement_id')
            ->where('d.departement_id', $parentId)
            ->select('e.employee_name', 'e.employee_level', 'd.subgrade_name')
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

    public function getJobParentInfoEdit($parentId, $departementId, $deptId, Request $request)
    {
        $docid = $request->query('docid');

        // Ambil 1 orang selain VACANT di parent_id tsb
        $employee = DB::table('hr_ms_sto_employee as e')
            ->join('hr_ms_sto_departement as d', 'e.departement_id', '=', 'd.departement_id')
            ->where('d.departement_id', $parentId)
            ->select('e.employee_name', 'e.employee_level', 'd.subgrade_name')
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
        $att = TrAttachment::where('id', $id)->where('status', 'A')->firstOrFail();

        // Normalisasi object path:
        // 1) Kalau filename sudah mengandung '/', anggap itu full path.
        // 2) Kalau tidak, gabungkan folder + filename (kalau folder ada).
        $objectPath = trim((string) $att->filename ?? '', '/');
        if (!Str::contains($objectPath, '/')) {
            $folder = trim((string) $att->folder ?? '', '/');
            if ($folder !== '') {
                $objectPath = $folder.'/'.$objectPath;
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
            'projectId' => $config['project_id'],
            'keyFilePath' => $config['key_file'],
        ]);
        $bucket = $storage->bucket($config['bucket']);
        $object = $bucket->object($objectPath);

        if (!$object->exists()) {
            // Tambahkan log supaya kelihatan path apa yang dicari
            Log::warning('GCS object not found', ['objectPath' => $objectPath]);
            abort(404, 'File not found in storage: '.$objectPath);
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
        $selectedDepartmentId = $request->query('selected_department_id');
        $groupCompanyId = strtoupper(trim((string) ($request->user()->group_cpny_id ?? '')));

        if (!$divisionId || !$groupCompanyId) {
            return response()->json([], 200);
        }

        $departments = DepartmentHR::query()
            ->select('department_id', 'department_name', 'division_id')
            ->where('group_cpny_id', $groupCompanyId)
            ->where(function ($query) use ($divisionId, $selectedDepartmentId) {
                $query->where(function ($active) use ($divisionId) {
                    $active->where('division_id', $divisionId)
                        ->where('status', 'A');
                });

                if ($selectedDepartmentId) {
                    $query->orWhere('department_id', $selectedDepartmentId);
                }
            })
            ->orderBy('department_name')
            ->get();

        return response()->json($departments, 200);
    }

    public function toggleJobPostingStatus(Request $request)
    {
        // 1. VALIDATE
        $request->validate([
            'docid' => 'required',
            'status' => 'required|in:U,P,C,H,X',
            'reason' => 'nullable|string|required_if:status,H',
        ]);

        // 2. CHECK ACCESS
        $user = $request->user();
        $username = $user->username;
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

        $hasAccess = GroupAccspecific::where('username', $username)
            ->where('group_cpny_id', $groupCompanyId)
            ->where('group_access_id', 'POSTING')
            ->where('status', 'A')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 3. FIND DATA
        $job = Jobposting::where('refid', $request->docid)
            ->where('group_cpny_id', $groupCompanyId)
            ->first();

        if (!$job) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // 4. UPDATE
        $job->status = $request->status;

        if ($request->status === 'H') {
            $job->reason = $request->reason;
        } else {
            $job->reason = null;
        }

        $job->updated_user = $username;
        $job->save();

        // 5. RESPONSE
        return response()->json(['success' => true]);
    }
}
