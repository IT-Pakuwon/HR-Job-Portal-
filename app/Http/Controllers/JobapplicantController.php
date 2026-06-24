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
use Mail;
use Vinkla\Hashids\Facades\Hashids;

class JobapplicantController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your session has expired. Please sign in again.'], 401)
                : redirect()->route('login')->with('error', 'Your session has expired. Please sign in again.');
        }

        $userCpnyIds = Usercpny::where('username', $user->username)
            ->pluck('cpny_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $base = ViewCareer::query()
            ->where('status', '!=', 'X')
            ->when(!empty($userCpnyIds), function ($q) use ($userCpnyIds) {
                $q->whereIn('cpnyid', $userCpnyIds);
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
            'transferred'
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

            $userCpnyIds = Usercpny::where('username', $user->username)
                ->pluck('cpny_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $jobTLExact = trim((string) $request->input('job_tl_exact', ''));
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
                ->leftJoin('hr_trx_jobposting as jp', 'jp.docid', '=', 'vc.docidposting')
                ->leftJoin('hr_ms_department as dept', 'dept.department_id', '=', 'jp.departementid')
                ->leftJoin('hr_ms_division as div', 'div.division_id', '=', 'dept.division_id')
                ->where('vc.status', '!=', 'X')
                ->when(!empty($userCpnyIds), function ($q) use ($userCpnyIds) {
                    $q->whereIn('vc.cpnyid', $userCpnyIds);
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
    public function jobTitleLevels(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $rows = DB::connection('mysql3')->table('viewtrxcareer as vc')
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

