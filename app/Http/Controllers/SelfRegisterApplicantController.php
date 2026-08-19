<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantCourse;
use App\Models\ApplicantEducation;
use App\Models\ApplicantFamily;
use App\Models\ApplicantLanguage;
use App\Models\ApplicantMarital;
use App\Models\ApplicantSkill;
use App\Models\ApplicantSW;
use App\Models\ApplicantWorking;
use App\Models\GroupAccspecific;
use App\Models\SelfPosting;
use App\Models\User;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class SelfRegisterApplicantController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your session has expired. Please sign in again.'], 401)
                : redirect()->route('login')->with('error', 'Your session has expired. Please sign in again.');
        }
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

        $base = DB::connection('mysql3')
            ->table('viewselfregister as vc')
            ->leftJoin('hr_ms_applicant_tagging as tag', function ($join) {
                $join->on('vc.docid', '=', 'tag.docid')
                    ->on('vc.applicant_id', '=', 'tag.applicant_id')
                    ->on('vc.group_cpny_id', '=', 'tag.group_cpny_id')
                    ->where('tag.status', '!=', 'X');
            })
            ->leftJoin('hr_trx_job_apply as map', function ($join) {
                $join->on('vc.docid', '=', 'map.docid')
                    ->on('vc.applicant_id', '=', 'map.applicant_id')
                    ->on('vc.group_cpny_id', '=', 'map.group_cpny_id')
                    ->where('map.status', '!=', 'X');
            })
            ->leftJoin('hr_trx_selfposting as sp', function ($join) {
                $join->on('vc.id', '=', 'sp.id')
                    ->on('vc.group_cpny_id', '=', 'sp.group_cpny_id');
            })
            ->where('vc.group_cpny_id', $groupCompanyId);

        $all       = (clone $base)->distinct()->count('vc.id');
        $unchecked = (clone $base)->where(function ($q) {
            $q->where('sp.is_read', 'N')->orWhereNull('sp.is_read');
        })->whereIn('vc.status', ['H', 'P'])->distinct()->count('vc.id');
        $checked   = (clone $base)->where('sp.is_read', 'Y')->whereIn('vc.status', ['H', 'P'])->distinct()->count('vc.id');
        $reject    = (clone $base)->where('vc.status', 'R')->distinct()->count('vc.id');
        $mapped    = (clone $base)->whereNotNull('map.jobid')->distinct()->count('vc.id');
        $unmapped  = (clone $base)->whereNull('map.jobid')->distinct()->count('vc.id');
        $tagged    = (clone $base)->whereNotNull('tag.id')->distinct()->count('vc.id');
        $untagged  = (clone $base)->whereNull('tag.id')->distinct()->count('vc.id');

        $divisions = \App\Models\Division::where('status', 'A')
            ->where('group_cpny_id', $groupCompanyId)
            ->orderBy('division_name')
            ->get(['division_id', 'division_name']);

        return view('pages.selfregister.selfapplicant', compact(
            'all', 'unchecked', 'checked', 'reject', 'mapped', 'unmapped', 'tagged', 'untagged', 'divisions'
        ));
    }

    public function json(Request $request)
    {
        $user = $request->user();
        $groupCompanyId = strtoupper(trim((string) ($user->group_cpny_id ?? '')));
        $status           = $request->query('status');
        $divisionFilter   = trim((string) $request->input('division_filter', ''));
        $departmentFilter = trim((string) $request->input('department_filter', ''));

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $global = trim((string) $request->input('search.value', ''));
        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = strtolower($request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $nameToDb = [
            'docid'           => 'vc.docid',
            'apply_date'      => 'vc.apply_date',
            'fullname'        => 'vc.fullname',
            'division_name'   => DB::raw('COALESCE(div_tag.division_name, div_reg.division_name)'),
            'department_name' => DB::raw('COALESCE(dept_tag.department_name, dept_reg.department_name)'),
            'education_name'  => 'vc.education_name',
            'religion'        => 'vc.religion',
            'height'          => 'vc.height',
            'weight'          => 'vc.weight',
            'company_name'    => 'vc.company_name',
            'status'          => 'vc.status',
        ];

        $base = DB::connection('mysql3')
            ->table('viewselfregister as vc')
            ->leftJoin('hr_ms_applicant_tagging as tag', function ($join) {
                $join->on('vc.docid', '=', 'tag.docid')
                    ->on('vc.applicant_id', '=', 'tag.applicant_id')
                    ->on('vc.group_cpny_id', '=', 'tag.group_cpny_id')
                    ->where('tag.status', '!=', 'X');
            })
            ->leftJoin('hr_trx_job_apply as map', function ($join) {
                $join->on('vc.docid', '=', 'map.docid')
                    ->on('vc.applicant_id', '=', 'map.applicant_id')
                    ->on('vc.group_cpny_id', '=', 'map.group_cpny_id')
                    ->where('map.status', '!=', 'X');
            })
            ->leftJoin('hr_trx_jobposting as jp', function ($join) {
                $join->on('map.jobid', '=', 'jp.docid')
                    ->on('map.group_cpny_id', '=', 'jp.group_cpny_id');
            })
            ->leftJoin('hr_trx_selfposting as sp', function ($join) {
                $join->on('vc.id', '=', 'sp.id')
                    ->on('vc.group_cpny_id', '=', 'sp.group_cpny_id');
            })
            ->leftJoin('hr_ms_division as div_tag', function ($join) {
                $join->on('tag.division_id_tagging', '=', 'div_tag.division_id')
                    ->on('tag.group_cpny_id', '=', 'div_tag.group_cpny_id');
            })
            ->leftJoin('hr_ms_division as div_reg', function ($join) {
                $join->on('sp.division_id', '=', 'div_reg.division_id')
                    ->on('sp.group_cpny_id', '=', 'div_reg.group_cpny_id');
            })
            ->leftJoin('hr_ms_department as dept_tag', function ($join) {
                $join->on('tag.departementid_tagging', '=', 'dept_tag.department_id')
                    ->on('tag.group_cpny_id', '=', 'dept_tag.group_cpny_id');
            })
            ->leftJoin('hr_ms_department as dept_reg', function ($join) {
                $join->on('sp.departementid', '=', 'dept_reg.department_id')
                    ->on('sp.group_cpny_id', '=', 'dept_reg.group_cpny_id');
            })
            ->where('vc.group_cpny_id', $groupCompanyId);

        // Filter status card
        if (!empty($status)) {
            if ($status === 'is_read_N') {
                $base->where(function ($q) {
                    $q->where('sp.is_read', 'N')->orWhereNull('sp.is_read');
                })->whereIn('vc.status', ['H', 'P']);
            } elseif ($status === 'is_read_Y') {
                $base->where('sp.is_read', 'Y')
                    ->whereIn('vc.status', ['H', 'P']);
            } elseif ($status === 'mapping') {
                $base->whereNotNull('map.jobid');
            } elseif ($status === 'unmapping') {
                $base->whereNull('map.jobid');
            } elseif ($status === 'tagged') {
                $base->whereNotNull('tag.id');
            } elseif ($status === 'untagged') {
                $base->whereNull('tag.id');
            } else {
                $base->where('vc.status', $status);
            }
        }

        if ($divisionFilter !== '') {
            $base->where(DB::raw('COALESCE(tag.division_id_tagging, sp.division_id)'), $divisionFilter);
        }
        if ($departmentFilter !== '') {
            $base->where(DB::raw('COALESCE(tag.departementid_tagging, sp.departementid)'), $departmentFilter);
        }

        $recordsTotal = (clone $base)->distinct()->count('vc.id');

        $query = (clone $base);

        // Global search
        if ($global !== '') {
            $like = "%{$global}%";
            $query->where(function ($q) use ($like) {
                $q->where('vc.docid', 'like', $like)
                ->orWhere('vc.fullname', 'like', $like)
                ->orWhere('vc.education_name', 'like', $like)
                ->orWhere('vc.religion', 'like', $like)
                ->orWhere('vc.company_name', 'like', $like)
                ->orWhere('vc.apply_date', 'like', $like)
                ->orWhere('vc.status', 'like', $like);
            });
        }

        // Per-column search
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

            $query->where($dbcol, 'like', "%{$val}%");
        }

        $recordsFiltered = (clone $query)->distinct()->count('vc.id');

        // Sorting
        $orderName = $request->input("columns.$orderIdx.name");
        $orderBy = $nameToDb[$orderName] ?? 'vc.docid';

        // Paging
        if ($length !== -1) {
            $query->skip($start)->take($length);
        }

        $rows = $query->select([
            'vc.id',
            'vc.docid',
            'vc.apply_date',
            'vc.fullname',
            'vc.education_name',
            'vc.religion',
            'vc.height',
            'vc.weight',
            'vc.company_name',
            'vc.status',
            'sp.is_read',
            'tag.id as tag_id',
            DB::raw('COALESCE(tag.division_id_tagging, sp.division_id) as division_id'),
            DB::raw('COALESCE(tag.departementid_tagging, sp.departementid) as department_id'),
            DB::raw('COALESCE(div_tag.division_name, div_reg.division_name) as division_name'),
            DB::raw('COALESCE(dept_tag.department_name, dept_reg.department_name) as department_name'),
            'map.jobid as jobposting_docid',
            'jp.job_title',
            'jp.job_level',
        ])
            ->orderBy($orderBy, $orderDir)
            ->get();

        $data = $rows->map(function ($r) {
            return [
                'eid'             => Hashids::encode($r->id),
                'docid'           => $r->docid,
                'apply_date'      => $r->apply_date,
                'fullname'        => $r->fullname,
                'education_name'  => $r->education_name,
                'religion'        => $r->religion,
                'height'          => $r->height,
                'weight'          => $r->weight,
                'company_name'    => $r->company_name,
                'division_id'     => $r->division_id,
                'department_id'   => $r->department_id,
                'division_name'   => $r->division_name,
                'department_name' => $r->department_name,
                'is_tagged'       => !empty($r->tag_id),
                'status'          => $r->status,
                'is_read'         => $r->is_read,
                'jobposting_docid' => $r->jobposting_docid,
                'job_name'        => $r->job_title
                    ? $r->job_title . ' (Lvl ' . $r->job_level . ')'
                    : null,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function showSelfRegister($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }
        $groupCompanyId = strtoupper(trim((string) $user->group_cpny_id));

        $career = SelfPosting::whereKey($id)
            ->where('group_cpny_id', $groupCompanyId)
            ->firstOrFail();

        $hasGroupAccess = GroupAccspecific::where('username', $user->username)
            ->where('group_cpny_id', $groupCompanyId)
            ->where('group_access_id', 'STEP')
            ->where('status', 'A')
            ->first();

        if ($hasGroupAccess) {
            $career->is_read = 'Y';
            $career->save();
        }

        $applicant = Applicant::where('applicant_id', $career->applicant_id)
            ->where('group_cpny_id', $groupCompanyId)
            ->firstOrFail();
        $applicant_family = ApplicantFamily::where('applicant_id', $career->applicant_id)->where('group_cpny_id', $groupCompanyId)->get();
        $applicant_marital = ApplicantMarital::where('applicant_id', $career->applicant_id)->where('group_cpny_id', $groupCompanyId)->get();
        $applicant_education = ApplicantEducation::where('applicant_id', $career->applicant_id)->where('group_cpny_id', $groupCompanyId)->get();
        $applicant_working = ApplicantWorking::where('applicant_id', $career->applicant_id)->where('group_cpny_id', $groupCompanyId)->get();
        $applicant_language = ApplicantLanguage::where('applicant_id', $career->applicant_id)->where('group_cpny_id', $groupCompanyId)->get();
        $applicant_course = ApplicantCourse::where('applicant_id', $career->applicant_id)->where('group_cpny_id', $groupCompanyId)->get();
        $applicant_sw = ApplicantSW::where('applicant_id', $career->applicant_id)->where('group_cpny_id', $groupCompanyId)->get();
        $applicant_skill = ApplicantSkill::where('applicant_id', $career->applicant_id)->where('group_cpny_id', $groupCompanyId)->get();

        $year = now()->year;
        $config = config('filesystems.disks.gcs');
        // Pastikan StorageClient di-import dan digunakan dengan benar
        $storage = new StorageClient([
            'projectId' => $config['project_id'],
            'keyFilePath' => $config['key_file'],
        ]);

        $bucket = $storage->bucket($config['bucket']);
        $expiration = \Carbon\Carbon::now()->addMinutes(30);

        $photo = null;
        $cv = null;
        $coverletter = null;
        $transkip = null;
        $ijazah = null;

        if (!empty($applicant->upload_photo)) {
            $object = $bucket->object($applicant->upload_photo);
            $photo = $object->signedUrl($expiration);
        }

        if (!empty($applicant->upload_cv)) {
            $object = $bucket->object($applicant->upload_cv);
            $cv = $object->signedUrl($expiration);
        }

        if (!empty($applicant->upload_coverletter)) {
            $object = $bucket->object($applicant->upload_coverletter);
            $coverletter = $object->signedUrl($expiration);
        }

        if (!empty($applicant->upload_transkip_nilai)) {
            $object = $bucket->object($applicant->upload_transkip_nilai);
            $transkip = $object->signedUrl($expiration);
        }

        if (!empty($applicant->upload_ijazah)) {
            $object = $bucket->object($applicant->upload_ijazah);
            $ijazah = $object->signedUrl($expiration);
        }

        return view('pages.selfregister.showapplicant', compact(
            'hash', 'career', 'applicant', 'applicant_family', 'applicant_marital', 'applicant_education', 'applicant_working', 'applicant_language', 'applicant_course', 'applicant_sw',
            'applicant_skill', 'year', 'photo', 'cv', 'coverletter', 'transkip', 'ijazah'
        ));
    }

    // public function storeMapping(Request $request)
    // {
    //     $decoded = Hashids::decode($request->applicant_id);
    //     $id = $decoded[0] ?? null;

    //     if (!$id) {
    //         return response()->json(['error' => 'Invalid ID'], 400);
    //     }

    //     // ambil SLF docid dari view
    //     $self = DB::connection('mysql3')
    //         ->table('viewselfregister')
    //         ->where('id', $id)
    //         ->first();

    //     if (!$self) {
    //         return response()->json(['error' => 'Self register not found'], 404);
    //     }

    //     // 🔥 ambil applicant_id dari selfposting
    //     $posting = DB::connection('mysql3')
    //         ->table('hr_trx_selfposting')
    //         ->where('docid', $self->docid)
    //         ->first();

    //     if (!$posting) {
    //         return response()->json(['error' => 'Self posting not found'], 404);
    //     }

    //     DB::connection('mysql3')->table('hr_trx_job_apply')->updateOrInsert(
    //         [
    //             'applicant_id' => $posting->applicant_id,
    //             'jobid'        => $request->jobposting_docid
    //         ],
    //         [
    //             'docid'      => $self->docid,
    //             'apply_date' => now(),
    //             'status'     => 'H',
    //             'apply_step' => 'JOAPHC',
    //             'prev_apply_step' => 'JOAPHC',

    //             // 🔥 TAMBAHAN
    //             'created_user' => auth()->user()->username ?? 'system',
    //             'updated_user' => auth()->user()->username ?? 'system',
    //             'is_read'      => 'N',

    //             'created_at' => now(),
    //             'updated_at' => now()
    //         ]
    //     );
    //     return response()->json(['success' => true]);
    // }

    public function storeMapping(Request $request)
    {
        $user = $request->user();
        $groupCompanyId = strtoupper(trim((string) ($user->group_cpny_id ?? '')));
        DB::connection('mysql3')->beginTransaction();

        try {
            $decoded = Hashids::decode($request->applicant_id);
            $id = $decoded[0] ?? null;

            if (!$id) {
                DB::connection('mysql3')->rollBack();
                return response()->json(['error' => 'Invalid ID'], 400);
            }

            $self = DB::connection('mysql3')
                ->table('viewselfregister')
                ->where('id', $id)
                ->where('group_cpny_id', $groupCompanyId)
                ->first();

            if (!$self) {
                DB::connection('mysql3')->rollBack();
                return response()->json(['error' => 'Self register not found'], 404);
            }

            $posting = DB::connection('mysql3')
                ->table('hr_trx_selfposting')
                ->where('id', $self->id)
                ->where('applicant_id', $self->applicant_id)
                ->where('group_cpny_id', $groupCompanyId)
                ->first();

            if (!$posting) {
                DB::connection('mysql3')->rollBack();
                return response()->json(['error' => 'Self posting not found'], 404);
            }

            $jobposting = DB::connection('mysql3')
                ->table('hr_trx_jobposting')
                ->where('docid', $request->jobposting_docid)
                ->where('group_cpny_id', $groupCompanyId)
                ->first();

            if (!$jobposting) {
                DB::connection('mysql3')->rollBack();
                return response()->json(['error' => 'Job posting not found for this company group'], 404);
            }

            // 🔥 PREVENT DOUBLE MAPPING
            $exists = DB::connection('mysql3')
                ->table('hr_trx_job_apply')
                ->where('docid', $self->docid)
                ->where('applicant_id', $posting->applicant_id)
                ->where('group_cpny_id', $groupCompanyId)
                ->where('status', '!=', 'X')
                ->exists();

            if ($exists) {
                DB::connection('mysql3')->rollBack();
                return response()->json(['error' => 'Already mapped'], 400);
            }

            // 🔥 CREATE JOB APPLY (pakai SLF sebagai docid)
            DB::connection('mysql3')->table('hr_trx_job_apply')->insert([
                'docid' => $self->docid, // ✅ SLF
                'cpnyid' => $jobposting->cpnyid,
                'group_cpny_id' => $groupCompanyId,
                'jobid' => $request->jobposting_docid,
                'applicant_id' => $posting->applicant_id,

                'apply_date' => now(),
                'apply_step' => 'JOAPHC',
                'prev_apply_step' => 'JOAPHC',

                'is_read' => 'N',
                'status' => 'H',

                'created_user' => auth()->user()->username ?? 'system',
                'updated_user' => auth()->user()->username ?? 'system', // ✅ tambah
                'created_at' => now(),
                'updated_at' => now(), // ✅ tambah
            ]);

            // 🔥 CREATE JOB APPLY STEP (INI YANG BARU)
            $steps = DB::connection('mysql3')
                ->table('hr_ms_job_step')
                ->where('group_cpny_id', $groupCompanyId)
                ->orderBy('step_order', 'ASC')
                ->get();

            foreach ($steps as $step) {
                DB::connection('mysql3')->table('hr_trx_job_apply_step')->insert([
                    'docid' => $self->docid, // tetap SLF
                    'cpnyid' => $jobposting->cpnyid,
                    'group_cpny_id' => $groupCompanyId,
                    'jobid' => $request->jobposting_docid,
                    'applicant_id' => $posting->applicant_id,

                    'step_id' => $step->step_id,
                    'step_order' => $step->step_order,
                    'type' => $step->type,
                    'step_pic' => $step->step_pic,
                    'step_approve' => $step->step_approve,

                    'status' => 'P',

                    'created_user' => auth()->user()->username ?? 'system',
                    'created_at' => now(),
                ]);
            }

            DB::connection('mysql3')
                ->table('hr_trx_selfposting')
                ->where('id', $self->id)
                ->where('applicant_id', $posting->applicant_id)
                ->where('group_cpny_id', $groupCompanyId)
                ->update([
                    'status' => 'M',
                    'updated_user' => auth()->user()->username ?? 'system',
                    'updated_at' => now(),
                ]);
            DB::connection('mysql3')->commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql3')->rollBack();

            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // public function rollbackMapping(Request $request)
    // {
    //     if (!$request->jobposting_docid) {
    //         return response()->json(['error' => 'Job ID missing'], 400);
    //     }

    //     $decoded = Hashids::decode($request->applicant_id);
    //     $id = $decoded[0] ?? null;

    //     if (!$id) {
    //         return response()->json(['error' => 'Invalid ID'], 400);
    //     }

    //     $self = DB::connection('mysql3')
    //         ->table('viewselfregister')
    //         ->where('id', $id)
    //         ->first();

    //     if (!$self) {
    //         return response()->json(['error' => 'Self not found'], 404);
    //     }

    //     $posting = DB::connection('mysql3')
    //         ->table('hr_trx_selfposting')
    //         ->where('docid', $self->docid)
    //         ->first();

    //     if (!$posting) {
    //         return response()->json(['error' => 'Posting not found'], 404);
    //     }

    //     DB::connection('mysql3')
    //         ->table('hr_trx_job_apply')
    //         ->where('applicant_id', $posting->applicant_id)
    //         ->where('jobid', $request->jobposting_docid)
    //         ->update([
    //             'status' => 'X',
    //             'updated_user' => auth()->user()->username ?? 'system', // 🔥 TAMBAH
    //             'updated_at' => now()
    //     ]);
    //     return response()->json(['success' => true]);
    // }

    public function rollbackMapping(Request $request)
    {
        $user = $request->user();
        $groupCompanyId = strtoupper(trim((string) ($user->group_cpny_id ?? '')));
        DB::connection('mysql3')->beginTransaction();

        try {
            if (!$request->jobposting_docid) {
                DB::connection('mysql3')->rollBack();
                return response()->json(['error' => 'Job ID missing'], 400);
            }

            $decoded = Hashids::decode($request->applicant_id);
            $id = $decoded[0] ?? null;

            if (!$id) {
                DB::connection('mysql3')->rollBack();
                return response()->json(['error' => 'Invalid ID'], 400);
            }

            $self = DB::connection('mysql3')
                ->table('viewselfregister')
                ->where('id', $id)
                ->where('group_cpny_id', $groupCompanyId)
                ->first();

            if (!$self) {
                DB::connection('mysql3')->rollBack();
                return response()->json(['error' => 'Self not found'], 404);
            }

            $posting = DB::connection('mysql3')
                ->table('hr_trx_selfposting')
                ->where('id', $self->id)
                ->where('applicant_id', $self->applicant_id)
                ->where('group_cpny_id', $groupCompanyId)
                ->first();

            if (!$posting) {
                DB::connection('mysql3')->rollBack();
                return response()->json(['error' => 'Posting not found'], 404);
            }

            // 🔥 STEP → SOFT DELETE
            DB::connection('mysql3')
                ->table('hr_trx_job_apply_step')
                ->where('docid', $self->docid)
                ->where('applicant_id', $posting->applicant_id)
                ->where('group_cpny_id', $groupCompanyId)
                ->where('jobid', $request->jobposting_docid)
               ->update([
                   'status' => 'X',
                   'updated_user' => auth()->user()->username ?? 'system',
                   'updated_at' => now(),
               ]);

            // 🔥 APPLY → SOFT DELETE
            DB::connection('mysql3')
                ->table('hr_trx_job_apply')
                ->where('docid', $self->docid)
                ->where('applicant_id', $posting->applicant_id)
                ->where('group_cpny_id', $groupCompanyId)
                ->where('jobid', $request->jobposting_docid)
                ->update([
                    'status' => 'X',
                    'updated_user' => auth()->user()->username ?? 'system',
                    'updated_at' => now(),
                ]);

            DB::connection('mysql3')->commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql3')->rollBack();

            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDepartments(Request $request)
    {
        $divisionId = $request->query('division_id');
        $groupCompanyId = strtoupper(trim((string) ($request->user()->group_cpny_id ?? '')));

        $query = \App\Models\DepartmentHR::where('status', 'A')
            ->where('group_cpny_id', $groupCompanyId)
            ->select('department_id', 'department_name');

        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }

        return response()->json($query->orderBy('department_name')->get());
    }

    public function storeTag(Request $request)
    {
        $authUser = $request->user();
        $groupCompanyId = strtoupper(trim((string) ($authUser->group_cpny_id ?? '')));
        $request->validate([
            'applicant_id' => 'required',
            'division_id'  => 'required|string',
            'department_id' => 'required|string',
        ]);

        $decoded = Hashids::decode($request->applicant_id);
        $id = $decoded[0] ?? null;

        if (!$id) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $self = DB::connection('mysql3')
            ->table('viewselfregister')
            ->where('id', $id)
            ->where('group_cpny_id', $groupCompanyId)
            ->first();

        if (!$self) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $posting = DB::connection('mysql3')
            ->table('hr_trx_selfposting')
            ->where('id', $self->id)
            ->where('applicant_id', $self->applicant_id)
            ->where('group_cpny_id', $groupCompanyId)
            ->first();

        if (!$posting) {
            return response()->json(['error' => 'Self posting not found'], 404);
        }

        $divisionExists = DB::connection('mysql3')->table('hr_ms_division')
            ->where('division_id', $request->division_id)
            ->where('group_cpny_id', $groupCompanyId)
            ->where('status', 'A')
            ->exists();
        $departmentExists = DB::connection('mysql3')->table('hr_ms_department')
            ->where('department_id', $request->department_id)
            ->where('division_id', $request->division_id)
            ->where('group_cpny_id', $groupCompanyId)
            ->where('status', 'A')
            ->exists();

        if (!$divisionExists || !$departmentExists) {
            return response()->json(['error' => 'Division or department does not belong to this company group'], 422);
        }

        $user = $authUser->username ?? 'system';

        DB::connection('mysql3')->beginTransaction();
        try {
            // Soft-delete semua tag aktif sebelumnya
            DB::connection('mysql3')
                ->table('hr_ms_applicant_tagging')
                ->where('docid', $self->docid)
                ->where('applicant_id', $posting->applicant_id)
                ->where('group_cpny_id', $groupCompanyId)
                ->where('status', '!=', 'X')
                ->update([
                    'status'       => 'X',
                    'updated_user' => $user,
                    'updated_at'   => now(),
                ]);

            // Insert tag baru
            DB::connection('mysql3')->table('hr_ms_applicant_tagging')->insert([
                'docid'                 => $self->docid,
                'applicant_id'          => $posting->applicant_id ?? null,
                'group_cpny_id'         => $groupCompanyId,
                'division_id_tagging'   => $request->division_id,
                'departementid_tagging' => $request->department_id,
                'status'                => 'A',
                'created_user'          => $user,
                'created_at'            => now(),
                'updated_user'          => $user,
                'updated_at'            => now(),
            ]);

            DB::connection('mysql3')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql3')->rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true]);
    }

    public function storeReject(Request $request)
    {
        $user = $request->user();
        $groupCompanyId = strtoupper(trim((string) ($user->group_cpny_id ?? '')));
        $request->validate([
            'applicant_id' => 'required',
        ]);

        $decoded = Hashids::decode($request->applicant_id);
        $id = $decoded[0] ?? null;

        if (!$id) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $self = DB::connection('mysql3')
            ->table('viewselfregister')
            ->where('id', $id)
            ->where('group_cpny_id', $groupCompanyId)
            ->first();

        if (!$self) {
            return response()->json(['error' => 'Not found'], 404);
        }

        DB::connection('mysql3')
            ->table('hr_trx_selfposting')
            ->where('id', $self->id)
            ->where('applicant_id', $self->applicant_id)
            ->where('group_cpny_id', $groupCompanyId)
            ->update([
                'status'       => 'R',
                'updated_user' => $user->username ?? 'system',
                'updated_at'   => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function downloadDocument(Request $request, $hash, $type)
    {
        $user = $request->user();
        $groupCompanyId = strtoupper(trim((string) ($user->group_cpny_id ?? '')));
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $career = SelfPosting::whereKey($id)
            ->where('group_cpny_id', $groupCompanyId)
            ->firstOrFail();
        $applicant = Applicant::where('applicant_id', $career->applicant_id)
            ->where('group_cpny_id', $groupCompanyId)
            ->firstOrFail();

        $fieldMap = [
            'cv'       => ['field' => 'upload_cv',            'label' => 'CurriculumVitae'],
            'transkip' => ['field' => 'upload_transkip_nilai', 'label' => 'TranskripNilai'],
            'ijazah'   => ['field' => 'upload_ijazah',         'label' => 'Ijazah'],
        ];

        abort_if(!isset($fieldMap[$type]), 404);

        $field    = $fieldMap[$type]['field'];
        $label    = $fieldMap[$type]['label'];
        $gcsPath  = $applicant->$field;
        abort_if(empty($gcsPath), 404);

        $config  = config('filesystems.disks.gcs');
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $config['key_file'],
        ]);

        $object  = $storage->bucket($config['bucket'])->object($gcsPath);
        $content = $object->downloadAsString();

        $name     = preg_replace('/\s+/', '', $applicant->full_name ?? 'Applicant');
        $filename = "{$label}_{$name}.pdf";

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
