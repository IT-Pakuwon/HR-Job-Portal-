<?php

namespace App\Http\Controllers;

use App\Models\Jobposting;
use App\Models\MsCompany;
use App\Models\SysUserRole;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\ViewCareer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class JobapplicantController extends Controller
{
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

    private function userDivisionIds($user): array
    {
        return $this->splitCsv($user->division_id);
    }

    // Accepts a plain value ("170"), an operator (">=170", "<180"), or a
    // "min-max" range ("160-180") on a numeric column; falls back to the
    // existing LIKE-on-string behavior for anything else.
    private function applyNumericRangeSearch($query, string $val, string $dbcol, string $likeExpr): void
    {
        if (preg_match('/^\s*(>=|<=|>|<)\s*(\d+(?:\.\d+)?)\s*$/', $val, $m)) {
            $query->where($dbcol, $m[1], (float) $m[2]);
        } elseif (preg_match('/^\s*(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)\s*$/', $val, $m)) {
            $a = (float) $m[1];
            $b = (float) $m[2];
            if ($a > $b) {
                [$a, $b] = [$b, $a];
            }
            $query->whereBetween($dbcol, [$a, $b]);
        } else {
            $query->whereRaw("{$likeExpr} LIKE ?", ["%{$val}%"]);
        }
    }

    // Accepts a "YYYY-MM-DD - YYYY-MM-DD" (or "... to ...") range on a date
    // column; falls back to the existing LIKE-on-string behavior otherwise,
    // so typing a single date (or a partial "2026-08") still works as before.
    private function applyDateRangeSearch($query, string $val, string $dbcol): void
    {
        if (preg_match('/^\s*(\d{4}-\d{2}-\d{2})\s*(?:-|to)\s*(\d{4}-\d{2}-\d{2})\s*$/i', $val, $m)) {
            [$from, $to] = [$m[1], $m[2]];
            if ($from > $to) {
                [$from, $to] = [$to, $from];
            }
            $query->where($dbcol, '>=', "{$from} 00:00:00")
                ->where($dbcol, '<=', "{$to} 23:59:59");
        } else {
            $query->where($dbcol, 'like', "%{$val}%");
        }
    }

    private function hasRole($user, string $roleId): bool
    {
        return SysUserRole::query()
            ->where('username', $user->username)
            ->where('role_id', $roleId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'A');
            })
            ->exists();
    }

    // RECACCALLDEPT & RECDIRACCESS => bisa lihat semua applicant + pakai filter Job Title-Level
    // RECACCESS saja => hanya applicant di division user, filter disembunyikan
    private function hasFullApplicantAccess($user): bool
    {
        return $this->hasRole($user, 'RECACCALLDEPT')
            || $this->hasRole($user, 'RECDIRACCESS')
            || $user->hasFullDataScope();
    }

    // Hanya role full (RECACCALLDEPT / RECDIRACCESS / DIRECTORACCESS) yang tidak dibatasi division.
    // HCBPACCESS tetap dibatasi company + division user (JKT dan SBY), sama seperti RECACCESS.
    private function canSkipDivisionRestriction($user): bool
    {
        return $this->hasFullApplicantAccess($user);
    }

    private function userDeptIds($user): array
    {
        return $this->splitCsv($user->department_id);
    }

    // SBY: RECACCESS (tanpa role full/HCBP) juga dibatasi ke department user, selain company & division.
    // JKT dan HCBPACCESS hanya company + division.
    private function applyDepartmentScope($query, $user, string $deptColumn): void
    {
        if (strtoupper(trim((string) $user->group_cpny_id)) !== 'SBY'
            || $this->canSkipDivisionRestriction($user)
            || $this->hasRole($user, 'HCBPACCESS')
        ) {
            return;
        }

        $deptIds = $this->userDeptIds($user);

        if (empty($deptIds)) {
            $query->whereRaw('1=0');
        } else {
            $query->whereIn($deptColumn, $deptIds);
        }
    }

    // RECACCALLDEPT / RECDIRACCESS => bisa lihat semua company di group-nya, bukan cuma company yang di-assign ke user
    private function userCpnyIds($user): array
    {
        if ($this->hasFullApplicantAccess($user)) {
            return MsCompany::pluck('cpny_id')->toArray();
        }

        return Usercpny::where('username', $user->username)
            ->pluck('cpny_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    // A remap keeps the old form frozen and files a copy as <root>-N (APP261100090 → APP261100090-1).
    private function rootApplicantId(?string $applicantId): string
    {
        return preg_replace('/-\d+$/', '', (string) $applicantId);
    }

    // hr_ms_applicant_* tables holding the candidate's form; copied on remap so the
    // old copy (what HR saw for the previous job) is never touched by later edits.
    private const APPLICANT_FORM_TABLES = [
        'hr_ms_applicant_additional',
        'hr_ms_applicant_course',
        'hr_ms_applicant_driver_license',
        'hr_ms_applicant_education',
        'hr_ms_applicant_family_background',
        'hr_ms_applicant_language',
        'hr_ms_applicant_marital_and_children',
        'hr_ms_applicant_organization',
        'hr_ms_applicant_reference',
        'hr_ms_applicant_skill',
        'hr_ms_applicant_sw',
        'hr_ms_applicant_working_exp',
    ];

    // Clusters hr_ms_applicant records that are likely the same real person, so
    // the "Duplicate Users" tab and the Applicant List row-click panel always
    // agree on who's grouped together and why.
    //
    // A pair of applicant_id records is linked if EITHER:
    //   - ktp_id + date_of_birth both match (strongest signal), OR
    //   - date_of_birth + mobile_phone both match (fallback when KTP is blank), OR
    //   - date_of_birth + email_address both match (fallback when KTP is blank)
    // Each signal requires BOTH fields non-empty — DOB alone is never enough
    // (too many unrelated people share a birthday).
    //
    // Returns [applicantToGroup, groupMatchedBy]:
    //   applicantToGroup: applicant_id => group key (only present if in a duplicate cluster)
    //   groupMatchedBy:    group key => human label, e.g. "KTP + DOB", "DOB + Phone"
    private function buildApplicantDuplicateClusters(string $groupCompanyId): array
    {
        // Only cluster applicants who actually submitted a job application.
        // hr_ms_applicant also holds abandoned/incomplete registrations (no
        // row in hr_trx_job_apply) — including those pollutes the clusters
        // and flags people as "duplicate" even though they only ever have
        // one real application in the Applicant List.
        //
        // Scoped to the caller's group_cpny_id — same tenant boundary as the
        // Applicant List — so applicants from other groups never get pulled
        // into a cluster's matching signal, even if it doesn't leak into
        // rendered rows (which are filtered by group_cpny_id downstream).
        $applicants = DB::connection('mysql3')
            ->table('hr_ms_applicant as a')
            ->where('a.group_cpny_id', $groupCompanyId)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('hr_trx_job_apply as ja')
                    ->whereColumn('ja.applicant_id', 'a.applicant_id')
                    ->where('ja.status', '!=', 'X');
            })
            ->select('a.applicant_id', 'a.ktp_id', 'a.date_of_birth', 'a.mobile_phone', 'a.email_address')
            ->get();

        $parent = [];
        $find = function ($x) use (&$parent, &$find) {
            if (!isset($parent[$x])) {
                $parent[$x] = $x;
            }
            while ($parent[$x] !== $x) {
                $parent[$x] = $parent[$parent[$x]];
                $x = $parent[$x];
            }

            return $x;
        };
        $union = function ($a, $b) use (&$parent, $find) {
            $ra = $find($a);
            $rb = $find($b);
            if ($ra !== $rb) {
                $parent[$ra] = $rb;
            }
        };

        foreach ($applicants as $a) {
            $find($a->applicant_id);
        }

        $signalHasDup = ['ktp_dob' => [], 'dob_phone' => [], 'dob_email' => []];

        $groupAndUnion = function (string $signal, \Closure $keyFn) use ($applicants, $union, &$signalHasDup) {
            $groups = [];
            foreach ($applicants as $a) {
                $key = $keyFn($a);
                if ($key !== null) {
                    $groups[$key][] = $a->applicant_id;
                }
            }
            foreach ($groups as $ids) {
                // A remap copy (APP…-N) is the same person as its root, so a group only
                // counts as a duplicate when it spans more than one distinct root.
                $roots = array_unique(array_map(fn ($id) => $this->rootApplicantId($id), $ids));
                if (count($roots) > 1) {
                    foreach ($ids as $id) {
                        $union($ids[0], $id);
                        $signalHasDup[$signal][$id] = true;
                    }
                }
            }
        };

        $groupAndUnion('ktp_dob', fn ($a) => (!empty($a->ktp_id) && !empty($a->date_of_birth))
            ? $a->ktp_id.'|'.$a->date_of_birth
            : null);
        $groupAndUnion('dob_phone', fn ($a) => (!empty($a->date_of_birth) && !empty($a->mobile_phone))
            ? $a->date_of_birth.'|'.$a->mobile_phone
            : null);
        $groupAndUnion('dob_email', fn ($a) => (!empty($a->date_of_birth) && !empty($a->email_address))
            ? $a->date_of_birth.'|'.strtolower($a->email_address)
            : null);

        $clusters = [];
        foreach ($applicants as $a) {
            $clusters[$find($a->applicant_id)][] = $a->applicant_id;
        }

        $applicantToGroup = [];
        $groupMatchedBy = [];
        $i = 0;

        foreach ($clusters as $ids) {
            $ids = array_values(array_unique($ids));
            if (count($ids) <= 1) {
                continue;
            }

            ++$i;
            $groupKey = 'grp'.$i;

            $labels = [];
            $hasSignal = fn ($signal) => collect($ids)->contains(fn ($id) => !empty($signalHasDup[$signal][$id] ?? false));

            if ($hasSignal('ktp_dob')) {
                $labels[] = 'KTP + DOB';
            }
            if ($hasSignal('dob_phone')) {
                $labels[] = 'DOB + Phone';
            }
            if ($hasSignal('dob_email')) {
                $labels[] = 'DOB + Email';
            }

            $groupMatchedBy[$groupKey] = !empty($labels) ? implode(' / ', $labels) : 'KTP + DOB';

            foreach ($ids as $id) {
                $applicantToGroup[$id] = $groupKey;
            }
        }

        return [$applicantToGroup, $groupMatchedBy];
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your session has expired. Please sign in again.'], 401)
                : redirect()->route('login')->with('error', 'Your session has expired. Please sign in again.');
        }

        $userCpnyIds = $this->userCpnyIds($user);

        $canFilterJobTL = $this->hasFullApplicantAccess($user);
        $skipDivisionRestriction = $this->canSkipDivisionRestriction($user);

        // RECACCESS / HCBPACCESS (tanpa RECACCALLDEPT/RECDIRACCESS) hanya boleh lihat
        // applicant pada job posting yang division-nya sama dengan division user tsb.
        $allowedJobIds = null;
        if (!$skipDivisionRestriction) {
            $divisionIds = $this->userDivisionIds($user);

            $allowedJobIds = DB::connection('mysql3')
                ->table('hr_trx_jobposting as jp')
                ->leftJoin('hr_ms_department as dept', 'dept.department_id', '=', 'jp.departementid')
                ->when(!empty($divisionIds), function ($q) use ($divisionIds) {
                    $q->whereIn('dept.division_id', $divisionIds);
                }, function ($q) {
                    $q->whereRaw('1=0');
                })
                ->tap(fn ($q) => $this->applyDepartmentScope($q, $user, 'jp.departementid'))
                ->pluck('jp.docid');
        }

        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

        $base = ViewCareer::query()
            ->where('group_cpny_id', $groupCompanyId)
            ->where('status', '!=', 'X')
            ->when(!empty($userCpnyIds), function ($q) use ($userCpnyIds) {
                $q->whereIn('cpnyid', $userCpnyIds);
            })
            ->when(!$skipDivisionRestriction, function ($q) use ($allowedJobIds) {
                $q->whereIn('docidposting', $allowedJobIds);
            });

        $activeBase = (clone $base)->where('status', '!=', 'T');

        $all = (clone $activeBase)->count();

        $unchecked = (clone $activeBase)
            ->where('is_read', 'N')
            ->count();

        $checked = (clone $activeBase)
            ->where('is_read', 'Y')
            ->whereIn('status', ['H', 'P'])
            ->count();

        $reject = (clone $activeBase)
            ->where('status', 'R')
            ->count();

        $approved = (clone $activeBase)
            ->where('status', 'C')
            ->count();

        $transferred = (clone $base)
            ->where('status', 'T')
            ->count();

        return view('pages.careers.jobapplicant', compact(
            'all',
            'unchecked',
            'checked',
            'reject',
            'approved',
            'transferred',
            'canFilterJobTL'
        ));
    }

    public function json(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Your session has expired. Please sign in again.',
                ], 401);
            }

            $userCpnyIds = $this->userCpnyIds($user);
            $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

            $canFilterJobTL = $this->hasFullApplicantAccess($user);
            $skipDivisionRestriction = $this->canSkipDivisionRestriction($user);
            $jobTLExact = $canFilterJobTL ? trim((string) $request->input('job_tl_exact', '')) : '';
            $status = $request->query('status');
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $global = trim((string) $request->input('search.value', ''));
            $orderIdx = (int) $request->input('order.0.column', 0);
            $orderDir = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            $nameToDb = [
                'docid' => 'vc.docid',
                'apply_date' => 'vc.apply_date',
                'fullname' => 'vc.fullname',
                'education_name' => 'vc.education_name',
                'religion' => 'vc.religion',
                'height' => 'vc.height',
                'weight' => 'vc.weight',
                'company_name' => 'vc.company_name',
                'match_score_percentage' => 'vs.match_score_percentage',
                'apply_step' => 'vc.apply_step',
            ];

            $base = DB::connection('mysql3')
                ->table('viewtrxcareer as vc')
                ->leftJoin('viewtrxcareer_scoring as vs', function ($join) {
                    $join->on('vc.docid', '=', 'vs.docid')
                        ->on('vc.docidposting', '=', 'vs.jobid');
                })
                ->leftJoin('hr_trx_jobposting as jp', function ($join) {
                    $join->on('jp.docid', '=', 'vc.docidposting')
                        ->on('jp.group_cpny_id', '=', 'vc.group_cpny_id');
                })
                ->leftJoin('hr_ms_department as dept', function ($join) {
                    $join->on('dept.department_id', '=', 'jp.departementid')
                        ->on('dept.group_cpny_id', '=', 'vc.group_cpny_id');
                })
                ->leftJoin('hr_ms_division as div', function ($join) {
                    $join->on('div.division_id', '=', 'dept.division_id')
                        ->on('div.group_cpny_id', '=', 'vc.group_cpny_id');
                })
                ->where('vc.group_cpny_id', $groupCompanyId)
                ->where('vc.status', '!=', 'X')
                ->when(!empty($userCpnyIds), function ($q) use ($userCpnyIds) {
                    $q->whereIn('vc.cpnyid', $userCpnyIds);
                })
                ->when(!$skipDivisionRestriction, function ($q) use ($user) {
                    $divisionIds = $this->userDivisionIds($user);
                    if (empty($divisionIds)) {
                        $q->whereRaw('1=0');
                    } else {
                        $q->whereIn('div.division_id', $divisionIds);
                    }
                    $this->applyDepartmentScope($q, $user, 'jp.departementid');
                });

            if (!empty($status)) {
                if ($status === 'T') {
                    $base->where('vc.status', 'T');
                } elseif ($status === 'is_read_Y') {
                    $base->where('vc.is_read', 'Y')
                        ->whereIn('vc.status', ['H', 'P']);
                } elseif ($status === 'is_read_N') {
                    $base->where('vc.is_read', 'N')
                        ->where('vc.status', '!=', 'T');
                } else {
                    $base->where('vc.status', $status);
                }
            } else {
                $base->where('vc.status', '!=', 'T');
            }

            $recordsTotal = (clone $base)->count();

            $query = (clone $base);

            // global search semua kolom penting
            if ($global !== '') {
                $like = "%{$global}%";

                $query->where(function ($q) use ($like) {
                    $q->where('vc.docid', 'like', $like)
                        ->orWhere('vc.applicant_id', 'like', $like)
                        ->orWhere('vc.fullname', 'like', $like)
                        ->orWhere('vc.job_title', 'like', $like)
                        ->orWhere('vc.job_level', 'like', $like)
                        ->orWhere('vc.job_type', 'like', $like)
                        ->orWhere('vc.cpnyid', 'like', $like)
                        ->orWhere('vc.departementid', 'like', $like)
                        ->orWhere('vc.docidposting', 'like', $like)
                        ->orWhere('vc.refid', 'like', $like)
                        ->orWhere('vc.religion', 'like', $like)
                        ->orWhere('vc.mobile_phone', 'like', $like)
                        ->orWhere('vc.education_name', 'like', $like)
                        ->orWhere('vc.education_type', 'like', $like)
                        ->orWhere('vc.company_name', 'like', $like)
                        ->orWhere('vc.work_job_title', 'like', $like)
                        // ->orWhere('vc.apply_step', 'like', $like)
                        ->orWhere(function ($sq) use ($like) {
                            $sq->where(function ($x) use ($like) {
                                $x->where('vc.apply_step', 'JOAPHC')
                                ->whereRaw('? LIKE ?', ['Job Apply HC', $like]);
                            })
                                ->orWhere(function ($x) use ($like) {
                                    $x->where('vc.apply_step', 'JOAPUS')
                                    ->whereRaw('? LIKE ?', ['Job Apply User', $like]);
                                })
                                ->orWhere(function ($x) use ($like) {
                                    $x->where('vc.apply_step', 'WIHC')
                                    ->whereRaw('? LIKE ?', ['Create Schedule Interview HC', $like]);
                                })
                                ->orWhere(function ($x) use ($like) {
                                    $x->where('vc.apply_step', 'IHC')
                                    ->whereRaw('? LIKE ?', ['Interview HC', $like]);
                                })
                                ->orWhere(function ($x) use ($like) {
                                    $x->where('vc.apply_step', 'WIU')
                                    ->whereRaw('? LIKE ?', ['Create Schedule Interview User', $like]);
                                })
                                ->orWhere(function ($x) use ($like) {
                                    $x->where('vc.apply_step', 'IU')
                                    ->whereRaw('? LIKE ?', ['Interview User', $like]);
                                })
                                ->orWhere(function ($x) use ($like) {
                                    $x->where('vc.apply_step', 'WPT')
                                    ->whereRaw('? LIKE ?', ['Waiting Psycho Test', $like]);
                                })
                                ->orWhere(function ($x) use ($like) {
                                    $x->where('vc.apply_step', 'PT')
                                    ->whereRaw('? LIKE ?', ['Psycho Test', $like]);
                                })
                                ->orWhere(function ($x) use ($like) {
                                    $x->where('vc.apply_step', 'OFF')
                                    ->whereRaw('? LIKE ?', ['Offering', $like]);
                                })
                                ->orWhere(function ($x) use ($like) {
                                    $x->where('vc.apply_step', 'JOIN')
                                    ->whereRaw('? LIKE ?', ['Join', $like]);
                                });
                        })
                        // ->orWhere('vc.prev_apply_step', 'like', $like)
                        ->orWhere('vc.status', 'like', $like)
                        ->orWhere('vc.status_app', 'like', $like)
                        ->orWhere('vc.created_user', 'like', $like)
                        ->orWhereRaw('CAST(vc.apply_date AS CHAR) LIKE ?', [$like])
                        ->orWhereRaw('CAST(vc.height AS CHAR) LIKE ?', [$like])
                        ->orWhereRaw('CAST(vc.weight AS CHAR) LIKE ?', [$like])
                        ->orWhereRaw('CAST(vc.end_year AS CHAR) LIKE ?', [$like])
                        ->orWhereRaw('CAST(vc.education_score AS CHAR) LIKE ?', [$like])
                        ->orWhereRaw('CAST(vc.end_date AS CHAR) LIKE ?', [$like])
                        ->orWhereRaw('CAST(IFNULL(vs.match_score_percentage,0) AS CHAR) LIKE ?', [$like]);
                });
            }

            // search per kolom
            $cols = $request->input('columns', []);
            foreach ($cols as $c) {
                $name = $c['name'] ?? null;
                $val = isset($c['search']['value']) ? trim((string) $c['search']['value']) : '';

                if (!$name || $val === '') {
                    continue;
                }

                $dbcol = $nameToDb[$name] ?? null;
                if (!$dbcol) {
                    continue;
                }

                if ($name === 'apply_step') {
                    $query->where($dbcol, $val);
                } elseif ($name === 'match_score_percentage') {
                    $this->applyNumericRangeSearch($query, $val, 'vs.match_score_percentage', 'CAST(IFNULL(vs.match_score_percentage,0) AS CHAR)');
                } elseif ($name === 'height' || $name === 'weight') {
                    $this->applyNumericRangeSearch($query, $val, $dbcol, "CAST(IFNULL({$dbcol},0) AS CHAR)");
                } elseif ($name === 'apply_date') {
                    $this->applyDateRangeSearch($query, $val, $dbcol);
                } else {
                    $query->where($dbcol, 'like', "%{$val}%");
                }
            }

            // exact filter job title + level
            if ($jobTLExact !== '') {
                [$exactTitle, $exactLevel] = array_pad(explode('|||', $jobTLExact, 2), 2, '');
                if ($exactTitle !== '' && $exactLevel !== '') {
                    $query->where('vc.job_title', $exactTitle)
                        ->where('vc.job_level', $exactLevel);
                }
            }

            $orderName = $request->input("columns.$orderIdx.name");
            $orderBy = $nameToDb[$orderName] ?? 'vc.apply_date';

            $recordsFiltered = (clone $query)->count();

            if ($length !== -1) {
                $query->skip($start)->take($length);
            }

            $rows = $query->select([
                DB::raw('vc.id as _id'),
                'vc.created_at',
                'vc.docid',
                'vc.applicant_id',
                'vc.docidposting',
                'vc.apply_date',
                'vc.fullname',
                'vc.education_name',
                'vc.religion',
                'vc.height',
                'vc.weight',
                'vc.company_name',
                'vc.apply_step',
                'vc.job_title',
                'vc.job_level',
                'vc.status_app',
                'vc.status',
                'vc.cpnyid',
                'vc.created_user',
                'vc.is_read',
                DB::raw('IFNULL(vs.total_tags, 0) as total_tags'),
                DB::raw('IFNULL(vs.matched_count, 0) as matched_count'),
                DB::raw('IFNULL(vs.match_score_percentage, 0) as match_score_percentage'),
                DB::raw('IFNULL(div.division_name, "") as division_name'),
                DB::raw('IFNULL(dept.department_name, "") as department_name'),
                DB::raw('IFNULL(jp.cpnyid, "") as posting_cpnyid'),
            ])
                ->orderBy($orderBy, $orderDir)
                ->get();

            $workflowSteps = DB::connection('mysql3')->table('hr_trx_job_apply_step')
                ->where('group_cpny_id', $groupCompanyId)
                ->whereIn('docid', $rows->pluck('docid')->unique())
                ->whereIn('jobid', $rows->pluck('docidposting')->unique())
                ->orderBy('step_order')->get()
                ->groupBy(fn ($step) => json_encode([$step->docid, $step->jobid]));
            $durationNow = now();
            // Same clustering as the Duplicate tab; short cache so paging/sorting the list
            // doesn't rescan hr_ms_applicant every time. Drives the "Duplicate List" action.
            [$dupApplicantToGroup] = \Cache::remember(
                'jobapp_dup_clusters_'.$groupCompanyId, 120,
                fn () => $this->buildApplicantDuplicateClusters($groupCompanyId)
            );
            $data = $rows->map(function ($r) use ($workflowSteps, $durationNow, $dupApplicantToGroup) {
                $duration = \App\Services\RecruitmentStepDuration::cumulative(
                    $workflowSteps->get(json_encode([$r->docid, $r->docidposting]), collect()),
                    $r->apply_step, $r->created_at, $r->status, $durationNow
                );
                return [
                    'eid' => Hashids::encode($r->_id),
                    'docid' => $r->docid,
                    'has_duplicate' => isset($dupApplicantToGroup[$r->applicant_id]),
                    'docidposting' => $r->docidposting,
                    'apply_date' => $r->apply_date,
                    'fullname' => $r->fullname,
                    'education_name' => $r->education_name,
                    'religion' => $r->religion,
                    'height' => $r->height,
                    'weight' => $r->weight,
                    'company_name' => $r->company_name,
                    'apply_step' => $r->apply_step,
                    'step_total_days' => $duration['days'] ?? null,
                    'step_duration_start' => $duration['start'] ?? null,
                    'step_duration_end' => $duration['end'] ?? null,
                    'job_title' => $r->job_title,
                    'job_level' => $r->job_level,
                    'status_app' => $r->status_app,
                    'status' => $r->status,
                    'cpnyid' => $r->cpnyid,
                    'created_user' => $r->created_user,
                    'is_read' => $r->is_read,
                    'total_tags' => (int) $r->total_tags,
                    'matched_count' => (int) $r->matched_count,
                    'match_score_percentage' => (int) $r->match_score_percentage,
                    'division_name' => $r->division_name,
                    'department_name' => $r->department_name,
                    'posting_cpnyid' => $r->posting_cpnyid,
                ];
            });

            // $steps = (clone $base)
            //     ->whereNotNull('vc.apply_step')
            //     ->distinct()
            //     ->orderBy('vc.apply_step')
            //     ->pluck('vc.apply_step')
            //     ->values();
            $stepOrder = [
                'JOAPHC',
                'JOAPUS',
                'WIHC',
                'IHC',
                'WIU',
                'IU',
                'WPT',
                'PT',
                'OFF',
                'JOIN',
            ];

            $steps = (clone $base)
                ->whereNotNull('vc.apply_step')
                ->distinct()
                ->pluck('vc.apply_step')
                ->filter()
                ->sortBy(function ($step) use ($stepOrder) {
                    $idx = array_search($step, $stepOrder, true);

                    return $idx === false ? 999 : $idx;
                })
                ->values();

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
                'steps' => $steps,
            ]);
        } catch (\Throwable $e) {
            \Log::error('jobapplicant.json error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'steps' => [],
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function duplicatesJson(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['message' => 'Your session has expired. Please sign in again.'], 401);
            }

            if (!$this->hasRole($user, 'RECACCALLDEPT')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $userCpnyIds = $this->userCpnyIds($user);
            $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

            [$applicantToGroup, $groupMatchedBy] = $this->buildApplicantDuplicateClusters($groupCompanyId);

            if (empty($applicantToGroup)) {
                return response()->json(['data' => []]);
            }

            $rows = DB::connection('mysql3')
                ->table('hr_ms_applicant as a')
                ->join('viewtrxcareer as vc', function ($join) {
                    $join->on('vc.applicant_id', '=', 'a.applicant_id')
                        ->on('vc.group_cpny_id', '=', 'a.group_cpny_id');
                })
                ->where('vc.group_cpny_id', $groupCompanyId)
                ->where('vc.status', '!=', 'X')
                ->when(!empty($userCpnyIds), function ($q) use ($userCpnyIds) {
                    $q->whereIn('vc.cpnyid', $userCpnyIds);
                })
                ->whereIn('a.applicant_id', array_keys($applicantToGroup))
                ->select([
                    DB::raw('vc.id as _id'),
                    'a.applicant_id',
                    'a.ktp_id',
                    'a.date_of_birth',
                    'a.full_name',
                    'vc.docid',
                    'vc.docidposting',
                    'vc.job_title',
                    'vc.job_level',
                    'vc.company_name',
                    'vc.apply_date',
                    'vc.apply_step',
                    'vc.status',
                    'vc.is_read',
                ])
                ->orderByDesc('vc.apply_date')
                ->get();

            $data = $rows->map(function ($r) use ($applicantToGroup, $groupMatchedBy) {
                if ($r->status === 'T') {
                    $statusLabel = 'Transfer';
                } elseif ($r->status === 'R') {
                    $statusLabel = 'Reject';
                } elseif ($r->status === 'C') {
                    $statusLabel = 'Approved';
                } elseif ($r->is_read === 'N') {
                    $statusLabel = 'Unchecked';
                } elseif ($r->is_read === 'Y' && in_array($r->status, ['H', 'P'], true)) {
                    $statusLabel = 'Checked';
                } else {
                    $statusLabel = $r->status;
                }

                $groupKey = $applicantToGroup[$r->applicant_id] ?? null;

                return [
                    'eid' => Hashids::encode($r->_id),
                    'ktp_id' => $r->ktp_id,
                    'date_of_birth' => $r->date_of_birth,
                    'full_name' => $r->full_name,
                    'docid' => $r->docid,
                    'docidposting' => $r->docidposting,
                    'job_title' => $r->job_title,
                    'job_level' => $r->job_level,
                    'company_name' => $r->company_name,
                    'apply_date' => $r->apply_date,
                    'status_label' => $statusLabel,
                    'matched_by' => $groupKey ? ($groupMatchedBy[$groupKey] ?? 'KTP + DOB') : 'KTP + DOB',
                    'group_key' => $groupKey,
                ];
            });

            $data = $data->sort(function ($a, $b) {
                $cmp = strcmp((string) $a['group_key'], (string) $b['group_key']);

                return $cmp !== 0 ? $cmp : strcmp((string) $b['apply_date'], (string) $a['apply_date']);
            })->values();

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            \Log::error('jobapplicant.duplicatesJson error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'data' => [],
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Baris di Applicant List di-klik → kalau applicant ini (matched by KTP+DOB)
    // punya lebih dari 1 job apply, kembalikan daftarnya buat expand panel.
    // Kalau cuma 1 (tidak ada duplikat), balikin data kosong supaya FE tidak buka panel.
    public function rowDuplicates(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['message' => 'Your session has expired. Please sign in again.'], 401);
            }

            $docid = trim((string) $request->query('docid', ''));
            if ($docid === '') {
                return response()->json(['data' => []]);
            }

            $applicant = DB::connection('mysql3')
                ->table('hr_trx_job_apply as ja')
                ->where('ja.docid', $docid)
                ->select('ja.applicant_id')
                ->first();

            if (!$applicant) {
                return response()->json(['data' => []]);
            }

            $userCpnyIds = $this->userCpnyIds($user);
            $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

            [$applicantToGroup, $groupMatchedBy] = $this->buildApplicantDuplicateClusters($groupCompanyId);

            $groupKey = $applicantToGroup[$applicant->applicant_id] ?? null;
            $matchedBy = $groupKey ? ($groupMatchedBy[$groupKey] ?? 'KTP + DOB') : 'KTP + DOB';

            $matchedApplicantIds = $groupKey
                ? collect($applicantToGroup)->filter(fn ($g) => $g === $groupKey)->keys()->values()
                : collect([$applicant->applicant_id]);

            $skipDivisionRestriction = $this->canSkipDivisionRestriction($user);

            $rows = DB::connection('mysql3')
                ->table('viewtrxcareer as vc')
                ->leftJoin('hr_trx_jobposting as jp', function ($join) {
                    $join->on('jp.docid', '=', 'vc.docidposting')
                        ->on('jp.group_cpny_id', '=', 'vc.group_cpny_id');
                })
                ->leftJoin('hr_ms_department as dept', function ($join) {
                    $join->on('dept.department_id', '=', 'jp.departementid')
                        ->on('dept.group_cpny_id', '=', 'vc.group_cpny_id');
                })
                ->leftJoin('hr_ms_division as div', function ($join) {
                    $join->on('div.division_id', '=', 'dept.division_id')
                        ->on('div.group_cpny_id', '=', 'vc.group_cpny_id');
                })
                ->whereIn('vc.applicant_id', $matchedApplicantIds)
                ->where('vc.group_cpny_id', $groupCompanyId)
                ->where('vc.status', '!=', 'X')
                ->when(!empty($userCpnyIds), function ($q) use ($userCpnyIds) {
                    $q->whereIn('vc.cpnyid', $userCpnyIds);
                })
                ->when(!$skipDivisionRestriction, function ($q) use ($user) {
                    $divisionIds = $this->userDivisionIds($user);
                    if (empty($divisionIds)) {
                        $q->whereRaw('1=0');
                    } else {
                        $q->whereIn('div.division_id', $divisionIds);
                    }
                    $this->applyDepartmentScope($q, $user, 'jp.departementid');
                })
                ->orderByDesc('vc.apply_date')
                ->select([
                    DB::raw('vc.id as _id'),
                    'vc.applicant_id',
                    'vc.docid',
                    'vc.fullname',
                    'vc.job_title',
                    'vc.job_level',
                    'vc.company_name',
                    'vc.apply_date',
                    'vc.apply_step',
                    'vc.status',
                    'vc.is_read',
                ])
                ->get();

            if ($rows->count() <= 1) {
                return response()->json(['data' => []]);
            }

            $stepLabels = [
                'JOAPHC' => 'Job Apply HC',
                'JOAPUS' => 'Job Apply User',
                'WIHC' => 'Create Schedule Interview HC',
                'IHC' => 'Interview HC',
                'WIU' => 'Create Schedule Interview User',
                'IU' => 'Interview User',
                'WPT' => 'Waiting Psycho Test',
                'PT' => 'Psycho Test',
                'OFF' => 'Offering',
                'JOIN' => 'Join',
            ];

            $data = $rows->map(function ($r) use ($stepLabels, $matchedBy) {
                if ($r->status === 'T') {
                    $statusLabel = 'Transfer';
                } elseif ($r->status === 'R') {
                    $statusLabel = 'Reject';
                } elseif ($r->status === 'C') {
                    $statusLabel = 'Approved';
                } elseif ($r->is_read === 'N') {
                    $statusLabel = 'Unchecked';
                } elseif ($r->is_read === 'Y' && in_array($r->status, ['H', 'P'], true)) {
                    $statusLabel = 'Checked';
                } else {
                    $statusLabel = $r->status;
                }

                return [
                    'eid' => Hashids::encode($r->_id),
                    'docid' => $r->docid,
                    'full_name' => $r->fullname,
                    'job_title' => $r->job_title,
                    'job_level' => $r->job_level,
                    'company_name' => $r->company_name,
                    'apply_date' => $r->apply_date,
                    'status_label' => $statusLabel,
                    'step_label' => $stepLabels[$r->apply_step] ?? $r->apply_step,
                    'step_index' => ($i = array_search($r->apply_step, array_keys($stepLabels), true)) === false ? 0 : $i + 1,
                    'matched_by' => $matchedBy,
                ];
            });

            return response()->json(['data' => $data->values()]);
        } catch (\Throwable $e) {
            \Log::error('jobapplicant.rowDuplicates error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'data' => [],
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function jobTitleLevels(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Your session has expired. Please sign in again.'], 401);
        }

        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));
        $q = trim((string) $request->query('q', ''));

        $rows = DB::connection('mysql3')->table('viewtrxcareer as vc')
            ->where('vc.group_cpny_id', $groupCompanyId)
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('vc.job_title', 'like', "%{$q}%")
                    ->orWhere('vc.job_level', 'like', "%{$q}%");
                });
            })
            ->whereNotNull('vc.job_title')->where('vc.job_title', '!=', '')
            ->whereNotNull('vc.job_level')->where('vc.job_level', '!=', '')
            ->distinct()
            ->orderBy('vc.job_title')
            ->orderBy('vc.job_level')
            ->limit(50)
            ->get(['vc.job_title', 'vc.job_level']);

        // Select2 butuh {id, text}. id berisi "title|||level" (delimiter aman)
        $data = $rows->map(function ($r) {
            return [
                'id' => $r->job_title.'|||'.$r->job_level,
                'text' => $r->job_title.' — '.$r->job_level,
            ];
        });

        return response()->json($data);
    }

    public function getCounts(Request $request)
    {
        $cpnyid = $request->query('cpnyid');

        $query = Jobposting::query();
        if (!empty($cpnyid)) {
            $query->where('cpnyid', $cpnyid);
        }

        $all = $query->count();
        $onProgress = (clone $query)->where('status', 'P')->count();
        $reject = (clone $query)->where('status', 'R')->count();
        $revise = (clone $query)->where('status', 'D')->count();
        $completed = (clone $query)->where('status', 'C')->count();

        return response()->json([
            'all' => $all,
            'onProgress' => $onProgress,
            'reject' => $reject,
            'revise' => $revise,
            'completed' => $completed,
        ]);
    }

    public function JobApplicants($jobId)
    {
        $user = auth()->user();
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));
        $userCpnyIds = $this->userCpnyIds($user);
        $skipDivisionRestriction = $this->canSkipDivisionRestriction($user);

        // Same scope as the Applicant List (json()): group, company, division (+ department for SBY).
        $applicants = DB::connection('mysql3')
            ->table('viewtrxcareer as vc')
            ->leftJoin('viewtrxcareer_scoring as vs', function ($join) {
                $join->on('vc.docid', '=', 'vs.docid')
                    ->on('vc.docidposting', '=', 'vs.jobid');
            })
            ->leftJoin('hr_trx_jobposting as jp', function ($join) {
                $join->on('jp.docid', '=', 'vc.docidposting')
                    ->on('jp.group_cpny_id', '=', 'vc.group_cpny_id');
            })
            ->leftJoin('hr_ms_department as dept', function ($join) {
                $join->on('dept.department_id', '=', 'jp.departementid')
                    ->on('dept.group_cpny_id', '=', 'vc.group_cpny_id');
            })
            ->leftJoin('hr_ms_division as div', function ($join) {
                $join->on('div.division_id', '=', 'dept.division_id')
                    ->on('div.group_cpny_id', '=', 'vc.group_cpny_id');
            })
            ->where('vc.docidposting', $jobId)
            ->where('vc.group_cpny_id', $groupCompanyId)
            ->when(!empty($userCpnyIds), function ($q) use ($userCpnyIds) {
                $q->whereIn('vc.cpnyid', $userCpnyIds);
            })
            ->when(!$skipDivisionRestriction, function ($q) use ($user) {
                $divisionIds = $this->userDivisionIds($user);
                if (empty($divisionIds)) {
                    $q->whereRaw('1=0');
                } else {
                    $q->whereIn('div.division_id', $divisionIds);
                }
                $this->applyDepartmentScope($q, $user, 'jp.departementid');
            })
            ->select(
                'vc.*',
                DB::raw('IFNULL(vs.total_tags, 0) as total_tags'),
                DB::raw('IFNULL(vs.matched_count, 0) as matched_count'),
                DB::raw('IFNULL(vs.match_score_percentage, 0) as match_score_percentage')
            )
            ->get();

        // dd($applicants);
        return response()->json(['data' => $applicants]);
    }

    // True if any applicant matching this one (KTP + DOB, DOB + Phone or DOB + Email, each
    // pair needing both fields non-empty) already has a live apply (not cancelled) for $jobId.
    private function hasMatchedApplicantApplyForJob($applicantId, string $jobId, string $groupCompanyId): bool
    {
        $me = DB::connection('mysql3')
            ->table('hr_ms_applicant')
            ->where('applicant_id', $applicantId)
            ->where('group_cpny_id', $groupCompanyId)
            ->first();

        if (!$me || empty($me->date_of_birth)) {
            return false;
        }

        $matches = DB::connection('mysql3')
            ->table('hr_ms_applicant as a')
            ->where('a.group_cpny_id', $groupCompanyId)
            ->where('a.date_of_birth', $me->date_of_birth)
            ->where(function ($q) use ($me) {
                $any = false;
                if (!empty($me->ktp_id)) {
                    $q->orWhere('a.ktp_id', $me->ktp_id);
                    $any = true;
                }
                if (!empty($me->mobile_phone)) {
                    $q->orWhere('a.mobile_phone', $me->mobile_phone);
                    $any = true;
                }
                if (!empty($me->email_address)) {
                    $q->orWhereRaw('LOWER(a.email_address) = ?', [strtolower($me->email_address)]);
                    $any = true;
                }
                if (!$any) {
                    $q->whereRaw('1=0');
                }
            })
            ->pluck('a.applicant_id');

        if ($matches->isEmpty()) {
            return false;
        }

        return DB::connection('mysql3')
            ->table('hr_trx_job_apply')
            ->where('group_cpny_id', $groupCompanyId)
            ->where('jobid', $jobId)
            ->where('status', '!=', 'X')
            ->whereIn('applicant_id', $matches)
            ->exists();
    }

    // Every apply this person has held (root applicant + its APP…-N remap copies), oldest first,
    // so the "History Remap" modal shows the full A → B → C trail. Works for both the current
    // model (a remap files a new JAP with remap_from_docid) and legacy remaps that reused one docid.
    public function remapHistory(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Your session has expired. Please sign in again.'], 401);
        }

        $id = Hashids::decode((string) $request->query('eid', ''))[0] ?? null;
        if (!$id) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

        $apply = DB::connection('mysql3')->table('hr_trx_job_apply')
            ->where('id', $id)
            ->where('group_cpny_id', $groupCompanyId)
            ->first();
        if (!$apply) {
            return response()->json(['error' => 'Apply record not found'], 404);
        }

        $root = $this->rootApplicantId($apply->applicant_id);
        $userCpnyIds = $this->userCpnyIds($user);

        $rows = DB::connection('mysql3')
            ->table('hr_trx_job_apply as ja')
            ->leftJoin('viewtrxcareer as vc', 'vc.id', '=', 'ja.id')
            ->where('ja.group_cpny_id', $groupCompanyId)
            ->where('ja.status', '!=', 'X')
            ->where(function ($q) use ($root) {
                $q->where('ja.applicant_id', $root)
                    ->orWhere('ja.applicant_id', 'like', $root.'-%');
            })
            ->when(!empty($userCpnyIds), fn ($q) => $q->whereIn('ja.cpnyid', $userCpnyIds))
            ->orderBy('ja.id')
            ->select([
                'ja.id', 'ja.docid', 'ja.jobid', 'ja.status', 'ja.apply_step', 'ja.is_read',
                'ja.remap_from_docid', 'ja.created_at', 'ja.updated_at', 'ja.updated_user',
                'vc.job_title', 'vc.job_level', 'vc.company_name',
            ])
            ->get();

        $stepLabels = [
            'JOAPHC' => 'Job Apply HC', 'JOAPUS' => 'Job Apply User',
            'WIHC' => 'Create Schedule Interview HC', 'IHC' => 'Interview HC',
            'WIU' => 'Create Schedule Interview User', 'IU' => 'Interview User',
            'WPT' => 'Waiting Psycho Test', 'PT' => 'Psycho Test',
            'OFF' => 'Offering', 'JOIN' => 'Join',
        ];
        $statusLabels = ['T' => 'Remapped', 'R' => 'Reject', 'C' => 'Approved'];

        $data = $rows->map(fn ($r) => [
            'eid' => Hashids::encode($r->id),
            'docid' => $r->docid,
            'jobid' => $r->jobid,
            'job' => trim(implode(' — ', array_filter([$r->job_title, $r->job_level]))),
            'company' => $r->company_name,
            'remap_from_docid' => $r->remap_from_docid,
            'step' => $stepLabels[$r->apply_step] ?? $r->apply_step,
            'step_index' => ($i = array_search($r->apply_step, array_keys($stepLabels), true)) === false ? 0 : $i + 1,
            'status' => $statusLabels[$r->status] ?? 'Active',
            'status_code' => $r->status,
            'date' => $r->created_at ? substr((string) $r->created_at, 0, 16) : null,
            'transferred_at' => $r->status === 'T' && $r->updated_at ? substr((string) $r->updated_at, 0, 16) : null,
            'by' => $r->status === 'T' ? $r->updated_user : null,
            'is_this' => (int) $r->id === (int) $id,
        ])->values();

        return response()->json(['data' => $data]);
    }

    public function storeRemap(Request $request)
    {
        $request->validate([
            'apply_id' => 'required',
            'new_jobid' => 'required|string',
        ]);

        $authUser = auth()->user();
        if (!$authUser) {
            return response()->json(['error' => 'Your session has expired. Please sign in again.'], 401);
        }

        $groupCompanyId = strtoupper(trim((string) $authUser->group_cpny_id));

        // Same authority as approve/rollback on an apply (STEP access), or the recruiter
        // all-department role — remap silently restarts the whole workflow, so not everyone
        // who can open the page should be able to do it.
        $canRemap = \App\Models\GroupAccspecific::where('username', $authUser->username)
                ->where('group_access_id', 'STEP')
                ->where('status', 'A')
                ->exists()
            || $this->hasRole($authUser, 'RECACCALLDEPT');

        if (!$canRemap) {
            return response()->json(['error' => 'You are not allowed to remap applicants.'], 403);
        }

        $decoded = Hashids::decode($request->apply_id);
        $id = $decoded[0] ?? null;

        if (!$id) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $apply = DB::connection('mysql3')
            ->table('hr_trx_job_apply')
            ->where('id', $id)
            ->first();

        if (!$apply) {
            return response()->json(['error' => 'Apply record not found'], 404);
        }

        // docid/id can collide across groups, so the apply record must be pinned to the
        // logged-in user's own group_cpny_id before it's allowed to be touched at all.
        if (strtoupper(trim((string) $apply->group_cpny_id)) !== $groupCompanyId) {
            return response()->json(['error' => 'Apply record not found'], 404);
        }

        // Only a live apply can be moved: 'T' was already remapped, 'X' is cancelled and
        // 'C' is completed (joined) — remapping any of those would fork or corrupt the record.
        if (in_array($apply->status, ['T', 'X', 'C'], true)) {
            return response()->json(['error' => 'This applicant\'s current status does not allow remapping.'], 422);
        }

        $newJobposting = DB::connection('mysql3')
            ->table('hr_trx_jobposting')
            ->where('docid', $request->new_jobid)
            ->where('group_cpny_id', $groupCompanyId)
            ->first();

        if (!$newJobposting) {
            return response()->json(['error' => 'Job posting not found'], 404);
        }

        if ($newJobposting->docid === $apply->jobid) {
            return response()->json(['error' => 'The applicant is already mapped to this position.'], 422);
        }

        // Steps, assessments, etc. are keyed by docid + jobid, so a second apply row for a job
        // this applicant already had (e.g. A -> B -> A) would collide with the first one.
        // Each remap now gets its own JAP and applicant copy (APP…-N), so "had this job
        // before" is checked per person (root applicant + all its copies), not per docid.
        $rootApplicantId = $this->rootApplicantId($apply->applicant_id);
        $alreadyHadJob = DB::connection('mysql3')
            ->table('hr_trx_job_apply')
            ->where('group_cpny_id', $groupCompanyId)
            ->where('jobid', $newJobposting->docid)
            ->where(function ($q) use ($rootApplicantId) {
                $q->where('applicant_id', $rootApplicantId)
                    ->orWhere('applicant_id', 'like', $rootApplicantId.'-%');
            })
            ->exists();

        if ($alreadyHadJob) {
            return response()->json(['error' => 'The applicant already has (or had) an apply for that position.'], 422);
        }

        // The same person can hold several applicant_id/docid (re-registered). Use the same
        // identity signals as the Duplicate Users tab so they can't be remapped onto a job
        // they already applied to under another record.
        if ($this->hasMatchedApplicantApplyForJob($apply->applicant_id, $newJobposting->docid, $groupCompanyId)) {
            return response()->json(['error' => 'This applicant (matched by KTP / DOB + Phone / DOB + Email) has already applied for that position.'], 422);
        }

        $user = $authUser->username ?? 'system';

        DB::connection('mysql3')->beginTransaction();
        try {
            // Soft-delete steps lama
            DB::connection('mysql3')
                ->table('hr_trx_job_apply_step')
                ->where('docid', $apply->docid)
                ->where('jobid', $apply->jobid)
                ->where('group_cpny_id', $groupCompanyId)
                ->update([
                    'status' => 'X',
                    'updated_user' => $user,
                    'updated_at' => now(),
                ]);

            // Set apply lama ke Transfer Candidate
            DB::connection('mysql3')
                ->table('hr_trx_job_apply')
                ->where('id', $id)
                ->update([
                    'status' => 'T',
                    'updated_user' => $user,
                    'updated_at' => now(),
                ]);

            // New JAP number, same sequence the candidate portal uses (ms_autonbr JAP per
            // group company / year / month), so the numbers never collide.
            $now = now();
            $year = (string) $now->year;
            $month = str_pad((string) $now->month, 2, '0', STR_PAD_LEFT);
            $autonbr = DB::connection('mysql3')->table('ms_autonbr')
                ->lockForUpdate()
                ->where('doctype', 'JAP')
                ->where('cpnyid', $groupCompanyId)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();
            if ($autonbr) {
                $urutan = $autonbr->number + 1;
                DB::connection('mysql3')->table('ms_autonbr')->where('id', $autonbr->id)->update(['number' => $urutan]);
            } else {
                $urutan = 1;
                DB::connection('mysql3')->table('ms_autonbr')->insert([
                    'doctype' => 'JAP', 'cpnyid' => $groupCompanyId, 'year' => $year,
                    'month' => $month, 'status' => 'A', 'number' => 1,
                ]);
            }
            $newDocid = 'JAP'.substr($year, 2).$month.sprintf('%05d', $urutan);

            // Copy the candidate's form to APP…-N so the old form stays frozen as HR saw it
            // for the previous job; the candidate updates the copy for the new job.
            $sourceApplicant = DB::connection('mysql3')->table('hr_ms_applicant')
                ->where('applicant_id', $apply->applicant_id)
                ->where('group_cpny_id', $groupCompanyId)
                ->lockForUpdate()
                ->first();
            if (!$sourceApplicant) {
                throw new \RuntimeException('Applicant form not found for '.$apply->applicant_id);
            }

            $lastSuffix = DB::connection('mysql3')->table('hr_ms_applicant')
                ->where('group_cpny_id', $groupCompanyId)
                ->where('applicant_id', 'like', $rootApplicantId.'-%')
                ->pluck('applicant_id')
                ->map(fn ($aid) => (int) substr($aid, strlen($rootApplicantId) + 1))
                ->max() ?? 0;
            $newApplicantId = $rootApplicantId.'-'.($lastSuffix + 1);

            $copy = (array) $sourceApplicant;
            unset($copy['id']);
            $copy['applicant_id'] = $newApplicantId;
            $copy['form_update_required'] = 'Y';
            $copy['updated_user'] = $user;
            $copy['updated_at'] = $now;
            DB::connection('mysql3')->table('hr_ms_applicant')->insert($copy);

            foreach (self::APPLICANT_FORM_TABLES as $formTable) {
                $rows = DB::connection('mysql3')->table($formTable)
                    ->where('applicant_id', $apply->applicant_id)
                    ->where('group_cpny_id', $groupCompanyId)
                    ->get();
                foreach ($rows as $row) {
                    $r = (array) $row;
                    unset($r['id']);
                    $r['applicant_id'] = $newApplicantId;
                    DB::connection('mysql3')->table($formTable)->insert($r);
                }
            }

            // Insert apply baru
            DB::connection('mysql3')->table('hr_trx_job_apply')->insert([
                'docid' => $newDocid,
                'remap_from_docid' => $apply->docid,
                'cpnyid' => $newJobposting->cpnyid,
                'group_cpny_id' => $groupCompanyId,
                'jobid' => $request->new_jobid,
                'applicant_id' => $newApplicantId,
                'apply_date' => $apply->apply_date ?: now(),
                'apply_step' => 'JOAPHC',
                'prev_apply_step' => 'JOAPHC',
                'is_read' => 'N',
                'status' => 'H',
                'created_user' => $user,
                'updated_user' => $user,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert steps baru
            $steps = DB::connection('mysql3')
                ->table('hr_ms_job_step')
                ->where('group_cpny_id', $groupCompanyId)
                ->where('status', 'A')
                ->orderBy('step_order', 'ASC')
                ->get();

            foreach ($steps as $step) {
                DB::connection('mysql3')->table('hr_trx_job_apply_step')->insert([
                    'docid' => $newDocid,
                    'cpnyid' => $newJobposting->cpnyid,
                    'group_cpny_id' => $groupCompanyId,
                    'jobid' => $request->new_jobid,
                    'applicant_id' => $newApplicantId,
                    'step_id' => $step->step_id,
                    'step_order' => $step->step_order,
                    'type' => $step->type,
                    'step_pic' => $step->step_pic,
                    'step_approve' => $step->step_approve,
                    'status' => 'P',
                    'created_user' => $user,
                    'created_at' => now(),
                ]);
            }

            DB::connection('mysql3')->commit();

            \Log::info('Applicant remapped', [
                'docid' => $apply->docid,
                'new_docid' => $newDocid,
                'from_applicant_id' => $apply->applicant_id,
                'new_applicant_id' => $newApplicantId,
                'from_jobid' => $apply->jobid,
                'from_apply_status' => $apply->status,
                'to_jobid' => $request->new_jobid,
                'by' => $user,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql3')->rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
