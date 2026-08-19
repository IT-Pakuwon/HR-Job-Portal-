<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
use App\Models\ViewCareer;
use App\Models\SysUserRole;
use Mail;
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
        return $this->hasRole($user, 'RECACCALLDEPT') || $this->hasRole($user, 'RECDIRACCESS');
    }

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
    private function buildApplicantDuplicateClusters(): array
    {
        // Only cluster applicants who actually submitted a job application.
        // hr_ms_applicant also holds abandoned/incomplete registrations (no
        // row in hr_trx_job_apply) — including those pollutes the clusters
        // and flags people as "duplicate" even though they only ever have
        // one real application in the Applicant List.
        $applicants = DB::connection('mysql3')
            ->table('hr_ms_applicant as a')
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
                if (count($ids) > 1) {
                    foreach ($ids as $id) {
                        $union($ids[0], $id);
                        $signalHasDup[$signal][$id] = true;
                    }
                }
            }
        };

        $groupAndUnion('ktp_dob', fn ($a) => (!empty($a->ktp_id) && !empty($a->date_of_birth))
            ? $a->ktp_id . '|' . $a->date_of_birth
            : null);
        $groupAndUnion('dob_phone', fn ($a) => (!empty($a->date_of_birth) && !empty($a->mobile_phone))
            ? $a->date_of_birth . '|' . $a->mobile_phone
            : null);
        $groupAndUnion('dob_email', fn ($a) => (!empty($a->date_of_birth) && !empty($a->email_address))
            ? $a->date_of_birth . '|' . strtolower($a->email_address)
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

            $i++;
            $groupKey = 'grp' . $i;

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

        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

        $userCpnyIds = Usercpny::where('username', $user->username)
            ->pluck('cpny_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $canFilterJobTL = $this->hasFullApplicantAccess($user);

        // RECACCESS (tanpa RECACCALLDEPT/RECDIRACCESS) hanya boleh lihat applicant
        // pada job posting yang division-nya sama dengan division user tsb.
        $allowedJobIds = null;
        if (!$canFilterJobTL) {
            $divisionIds = $this->userDivisionIds($user);

            $allowedJobIds = DB::connection('mysql3')
                ->table('hr_trx_jobposting as jp')
                ->leftJoin('hr_ms_department as dept', 'dept.department_id', '=', 'jp.departementid')
                ->when(!empty($divisionIds), function ($q) use ($divisionIds) {
                    $q->whereIn('dept.division_id', $divisionIds);
                }, function ($q) {
                    $q->whereRaw('1=0');
                })
                ->pluck('jp.docid');
        }

        $base = ViewCareer::query()
            ->where('group_cpny_id', $groupCompanyId)
            ->where('status', '!=', 'X')
            ->when(!empty($userCpnyIds), function ($q) use ($userCpnyIds) {
                $q->whereIn('cpnyid', $userCpnyIds);
            })
            ->when(!$canFilterJobTL, function ($q) use ($allowedJobIds) {
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
                    'message' => 'Your session has expired. Please sign in again.'
                ], 401);
            }

            $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

            $userCpnyIds = Usercpny::where('username', $user->username)
                ->pluck('cpny_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $canFilterJobTL = $this->hasFullApplicantAccess($user);
            $jobTLExact = $canFilterJobTL ? trim((string) $request->input('job_tl_exact', '')) : '';
            $status     = $request->query('status');
            $start      = (int) $request->input('start', 0);
            $length     = (int) $request->input('length', 10);
            $global     = trim((string) $request->input('search.value', ''));
            $orderIdx   = (int) $request->input('order.0.column', 0);
            $orderDir   = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            $nameToDb = [
                'docid'                  => 'vc.docid',
                'apply_date'             => 'vc.apply_date',
                'fullname'               => 'vc.fullname',
                'education_name'         => 'vc.education_name',
                'religion'               => 'vc.religion',
                'height'                 => 'vc.height',
                'weight'                 => 'vc.weight',
                'company_name'           => 'vc.company_name',
                'match_score_percentage' => 'vs.match_score_percentage',
                'apply_step'        => 'vc.apply_step',
            ];

            $base = DB::connection('mysql3')
                ->table('viewtrxcareer as vc')
                ->leftJoin('viewtrxcareer_scoring as vs', 'vc.docid', '=', 'vs.docid')
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
                ->when(!$canFilterJobTL, function ($q) use ($user) {
                    $divisionIds = $this->userDivisionIds($user);
                    if (empty($divisionIds)) {
                        $q->whereRaw('1=0');
                    } else {
                        $q->whereIn('div.division_id', $divisionIds);
                    }
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
                $val  = isset($c['search']['value']) ? trim((string) $c['search']['value']) : '';

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
                    if (preg_match('/^\s*(>=|<=|>|<)\s*(\d+)\s*$/', $val, $m)) {
                        $op  = $m[1];
                        $num = (int) $m[2];
                        $query->where('vs.match_score_percentage', $op, $num);
                    } elseif (preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/', $val, $m)) {
                        $a = (int) $m[1];
                        $b = (int) $m[2];
                        if ($a > $b) [$a, $b] = [$b, $a];
                        $query->whereBetween('vs.match_score_percentage', [$a, $b]);
                    } else {
                        $query->whereRaw('CAST(IFNULL(vs.match_score_percentage,0) AS CHAR) LIKE ?', ["%{$val}%"]);
                    }
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
            $orderBy   = $nameToDb[$orderName] ?? 'vc.apply_date';

            $recordsFiltered = (clone $query)->count();

            if ($length !== -1) {
                $query->skip($start)->take($length);
            }

            $rows = $query->select([
                    DB::raw('vc.id as _id'),
                    'vc.docid',
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

            $data = $rows->map(function ($r) {
                return [
                    'eid'                    => Hashids::encode($r->_id),
                    'docid'                  => $r->docid,
                    'docidposting'           => $r->docidposting,
                    'apply_date'             => $r->apply_date,
                    'fullname'               => $r->fullname,
                    'education_name'         => $r->education_name,
                    'religion'               => $r->religion,
                    'height'                 => $r->height,
                    'weight'                 => $r->weight,
                    'company_name'           => $r->company_name,
                    'apply_step'        => $r->apply_step,
                    'job_title'              => $r->job_title,
                    'job_level'              => $r->job_level,
                    'status_app'             => $r->status_app,
                    'status'                 => $r->status,
                    'cpnyid'                 => $r->cpnyid,
                    'created_user'           => $r->created_user,
                    'is_read'                => $r->is_read,
                    'total_tags'             => (int) $r->total_tags,
                    'matched_count'          => (int) $r->matched_count,
                    'match_score_percentage' => (int) $r->match_score_percentage,
                    'division_name'          => $r->division_name,
                    'department_name'        => $r->department_name,
                    'posting_cpnyid'         => $r->posting_cpnyid,
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
                'draw'            => intval($request->input('draw')),
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data'            => $data,
                'steps'           => $steps,
            ]);
        } catch (\Throwable $e) {
            \Log::error('jobapplicant.json error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'draw'            => intval($request->input('draw')),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'steps'           => [],
                'message'         => $e->getMessage(),
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

            [$applicantToGroup, $groupMatchedBy] = $this->buildApplicantDuplicateClusters();

            if (empty($applicantToGroup)) {
                return response()->json(['data' => []]);
            }

            $userCpnyIds = Usercpny::where('username', $user->username)
                ->pluck('cpny_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $rows = DB::connection('mysql3')
                ->table('hr_ms_applicant as a')
                ->join('viewtrxcareer as vc', 'vc.applicant_id', '=', 'a.applicant_id')
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
                    'eid'           => Hashids::encode($r->_id),
                    'ktp_id'        => $r->ktp_id,
                    'date_of_birth' => $r->date_of_birth,
                    'full_name'     => $r->full_name,
                    'docid'         => $r->docid,
                    'docidposting'  => $r->docidposting,
                    'job_title'     => $r->job_title,
                    'job_level'     => $r->job_level,
                    'company_name'  => $r->company_name,
                    'apply_date'    => $r->apply_date,
                    'status_label'  => $statusLabel,
                    'matched_by'    => $groupKey ? ($groupMatchedBy[$groupKey] ?? 'KTP + DOB') : 'KTP + DOB',
                    'group_key'     => $groupKey,
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
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'data'    => [],
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

            [$applicantToGroup, $groupMatchedBy] = $this->buildApplicantDuplicateClusters();

            $groupKey = $applicantToGroup[$applicant->applicant_id] ?? null;
            $matchedBy = $groupKey ? ($groupMatchedBy[$groupKey] ?? 'KTP + DOB') : 'KTP + DOB';

            $matchedApplicantIds = $groupKey
                ? collect($applicantToGroup)->filter(fn ($g) => $g === $groupKey)->keys()->values()
                : collect([$applicant->applicant_id]);

            $userCpnyIds = Usercpny::where('username', $user->username)
                ->pluck('cpny_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $canFilterJobTL = $this->hasFullApplicantAccess($user);

            $rows = DB::connection('mysql3')
                ->table('viewtrxcareer as vc')
                ->leftJoin('hr_trx_jobposting as jp', 'jp.docid', '=', 'vc.docidposting')
                ->leftJoin('hr_ms_department as dept', 'dept.department_id', '=', 'jp.departementid')
                ->leftJoin('hr_ms_division as div', 'div.division_id', '=', 'dept.division_id')
                ->whereIn('vc.applicant_id', $matchedApplicantIds)
                ->where('vc.status', '!=', 'X')
                ->when(!empty($userCpnyIds), function ($q) use ($userCpnyIds) {
                    $q->whereIn('vc.cpnyid', $userCpnyIds);
                })
                ->when(!$canFilterJobTL, function ($q) use ($user) {
                    $divisionIds = $this->userDivisionIds($user);
                    if (empty($divisionIds)) {
                        $q->whereRaw('1=0');
                    } else {
                        $q->whereIn('div.division_id', $divisionIds);
                    }
                })
                ->orderByDesc('vc.apply_date')
                ->select([
                    DB::raw('vc.id as _id'),
                    'vc.applicant_id',
                    'vc.docid',
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
                'WIHC'   => 'Create Schedule Interview HC',
                'IHC'    => 'Interview HC',
                'WIU'    => 'Create Schedule Interview User',
                'IU'     => 'Interview User',
                'WPT'    => 'Waiting Psycho Test',
                'PT'     => 'Psycho Test',
                'OFF'    => 'Offering',
                'JOIN'   => 'Join',
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
                    'eid'          => Hashids::encode($r->_id),
                    'docid'        => $r->docid,
                    'job_title'    => $r->job_title,
                    'job_level'    => $r->job_level,
                    'company_name' => $r->company_name,
                    'apply_date'   => $r->apply_date,
                    'status_label' => $statusLabel,
                    'step_label'   => $stepLabels[$r->apply_step] ?? $r->apply_step,
                    'matched_by'   => $matchedBy,
                ];
            });

            return response()->json(['data' => $data->values()]);
        } catch (\Throwable $e) {
            \Log::error('jobapplicant.rowDuplicates error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'data'    => [],
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
            ->get(['vc.job_title','vc.job_level']);

        // Select2 butuh {id, text}. id berisi "title|||level" (delimiter aman)
        $data = $rows->map(function ($r) {
            return [
                'id'   => $r->job_title . '|||' . $r->job_level,
                'text' => $r->job_title . ' — ' . $r->job_level,
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
            'completed' => $completed
        ]);
    }


    public function JobApplicants($jobId)
    {
        // dd($jobId);
        // $applicants = ViewCareer::where('docidposting', $jobId)->get();
        $applicants = DB::connection('mysql3')
            ->table('viewtrxcareer as vc')
            ->leftJoin('viewtrxcareer_scoring as vs', 'vc.docid', '=', 'vs.docid')
            ->where('vc.docidposting', $jobId)
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

    public function storeRemap(Request $request)
    {
        $request->validate([
            'apply_id'  => 'required',
            'new_jobid' => 'required|string',
        ]);

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

        $user = auth()->user()->username ?? 'system';

        DB::connection('mysql3')->beginTransaction();
        try {
            // Soft-delete steps lama
            DB::connection('mysql3')
                ->table('hr_trx_job_apply_step')
                ->where('docid', $apply->docid)
                ->where('jobid', $apply->jobid)
                ->update([
                    'status'       => 'X',
                    'updated_user' => $user,
                    'updated_at'   => now(),
                ]);

            // Set apply lama ke Transfer Candidate
            DB::connection('mysql3')
                ->table('hr_trx_job_apply')
                ->where('id', $id)
                ->update([
                    'status'       => 'T',
                    'updated_user' => $user,
                    'updated_at'   => now(),
                ]);

            // Insert apply baru
            DB::connection('mysql3')->table('hr_trx_job_apply')->insert([
                'docid'           => $apply->docid,
                'jobid'           => $request->new_jobid,
                'applicant_id'    => $apply->applicant_id,
                'apply_date'      => now(),
                'apply_step'      => 'JOAPHC',
                'prev_apply_step' => 'JOAPHC',
                'is_read'         => 'N',
                'status'          => 'H',
                'created_user'    => $user,
                'updated_user'    => $user,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Insert steps baru
            $steps = DB::connection('mysql3')
                ->table('hr_ms_job_step')
                ->orderBy('step_order', 'ASC')
                ->get();

            foreach ($steps as $step) {
                DB::connection('mysql3')->table('hr_trx_job_apply_step')->insert([
                    'docid'        => $apply->docid,
                    'jobid'        => $request->new_jobid,
                    'applicant_id' => $apply->applicant_id,
                    'step_id'      => $step->step_id,
                    'step_order'   => $step->step_order,
                    'type'         => $step->type,
                    'step_pic'     => $step->step_pic,
                    'step_approve' => $step->step_approve,
                    'status'       => 'P',
                    'created_user' => $user,
                    'created_at'   => now(),
                ]);
            }

            DB::connection('mysql3')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql3')->rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
