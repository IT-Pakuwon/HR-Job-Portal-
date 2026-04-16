<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\Autonbr;
use App\Models\MsCompany;
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use App\Models\MsWorktypeDept;
use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrWO;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Mail;
use PDF;
use Vinkla\Hashids\Facades\Hashids;

class WoController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Company multi
        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        // Department multi
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        // ===============================
        // APPROVAL STATUS (existing)
        // ===============================
        $all = TrWO::whereIn('cpny_id', $cpnyIds)
                ->whereIn('department_id', $deptIds)
                ->count();

        $onProgress = TrWO::where('status', 'P')
                ->whereIn('cpny_id', $cpnyIds)
                ->whereIn('department_id', $deptIds)
                ->count();

        $reject = TrWO::where('status', 'R')
                ->whereIn('cpny_id', $cpnyIds)
                ->whereIn('department_id', $deptIds)
                ->count();

        $revise = TrWO::where('status', 'D')
                ->whereIn('cpny_id', $cpnyIds)
                ->whereIn('department_id', $deptIds)
                ->count();

        $completed = TrWO::where('status', 'C')
                ->whereIn('cpny_id', $cpnyIds)
                ->whereIn('department_id', $deptIds)
                ->count();

        $woAll = 0;

        if ($user->hasRole('COSTCTRLACCESS')) {
            $woAll = TrWO::whereIn('cpny_id', $cpnyIds)
                ->whereIn('status', ['P', 'C']) // same logic as BAST All
                ->count();
        }

        // ===============================
        // 🔥 JOB STATUS (NEW)
        // ===============================

        $jobOnProgress = TrWO::where('status_pekerjaan', 'P')
                ->whereIn('cpny_id', $cpnyIds)
                ->whereIn('department_id', $deptIds)
                ->count();

        $jobCancel = TrWO::where('status_pekerjaan', 'X')
                ->whereIn('cpny_id', $cpnyIds)
                ->whereIn('department_id', $deptIds)
                ->count();

        $jobCompleted = TrWO::where('status_pekerjaan', 'C')
                ->whereIn('cpny_id', $cpnyIds)
                ->whereIn('department_id', $deptIds)
                ->count();

        $jobHold = TrWO::where('status_pekerjaan', 'H')
                ->whereIn('cpny_id', $cpnyIds)
                ->whereIn('department_id', $deptIds)
                ->count();

        return view('pages.wos.wos', compact(
            'all',
            'onProgress',
            'reject',
            'revise',
            'completed',
            'woAll',

            // 👇 new variables
            'jobOnProgress',
            'jobCancel',
            'jobCompleted',
            'jobHold'
        ));
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        // department_id juga bisa multi, tapi di debug sudah "IT"
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', ''); // '' = all
        $scope = (string) $request->query('scope', '');

        $baseTable = (new TrWO())->getTable(); // "tr_wo"

        $columns = [
            0 => 'wo.woid',
            1 => 'wo.wodate',
            2 => 'wo.cpny_id',
            3 => 'wo.department_id',
            4 => 'wt.worktype_name',
            5 => 'wo.worequest',
            6 => 'wo.keperluan',
            7 => 'wo.status',
            8 => 'wo.status_pekerjaan',
            9 => 'wo.budget_use',
            10 => 'wo.subworktypeid',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'wo.woid';

        $base = TrWO::from($baseTable.' as wo')
            ->leftJoin('ms_worktype as wt', function ($join) {
                $join->on('wt.worktypeid', '=', 'wo.worktypeid');
            })
            ->whereIn('wo.cpny_id', $cpnyIds)           // 🔹 filter cpny sesuai user
            // ✅ NORMAL MODE → filter by department
            ->when(
                $scope !== 'wo_all',
                fn ($q) => $q->whereIn('wo.department_id', $deptIds)
            )

            // ✅ WO ALL MODE → cross department + status filter
            ->when(
                $scope === 'wo_all',
                fn ($q) => $q->whereIn('wo.status', ['P', 'C'])
            );
        if ($status !== '') {
            $base->where('wo.status', $status);
        }

        $recordsTotal = (clone $base)->distinct('wo.woid')->count('wo.woid');

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('wo.woid', 'ilike', "%{$search}%")
                ->orWhere('wo.cpny_id', 'ilike', "%{$search}%")
                ->orWhere('wo.department_id', 'ilike', "%{$search}%")
                ->orWhere('wt.worktype_name', 'ilike', "%{$search}%")
                ->orWhere('wo.worequest', 'ilike', "%{$search}%")
                ->orWhere('wo.keperluan', 'ilike', "%{$search}%")
                ->orWhere('wo.status', 'ilike', "%{$search}%")
                ->orWhere('wo.status_pekerjaan', 'ilike', "%{$search}%")
                ->orWhere('wo.budget_use', 'ilike', "%{$search}%")
                ->orWhere('wo.subworktypeid', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->distinct('wo.woid')->count('wo.woid');

        $data = $base->select(
            'wo.id',                // untuk hashids -> eid
            'wo.woid',
            'wo.wodate',
            'wo.cpny_id',
            'wo.department_id',
            'wt.worktype_name',
            'wo.worequest',
            'wo.keperluan',
            'wo.status',
            'wo.status_pekerjaan',
            'wo.budget_use',
            'wo.subworktypeid',
            'wo.created_by'
        )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('wo.woid', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            unset($row->id);

            return $row;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function createWo()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();

        return view('pages.wos.createwos', compact('usercpny', 'usercpny2', 'userdept', 'userdept2'));
    }

    public function storeWo(Request $request)
    {
        // dd($request->all());
        $doctype = 'WO';
        $user = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt = \Carbon\Carbon::now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        // Normalisasi angka lokal → float
        $toFloat = function ($v): ?float {
            if ($v === null || $v === '') {
                return null;
            }
            $s = preg_replace('/\s+/', '', (string) $v);
            $hasComma = strpos($s, ',') !== false;
            $hasDot = strpos($s, '.') !== false;

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot = strrpos($s, '.');
                if ($lastComma > $lastDot) {
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot) {
                if (substr_count($s, '.') > 1) {
                    $s = str_replace('.', '', $s);
                }
            }

            return is_numeric($s) ? (float) $s : null;
        };

        // ===== Validasi utama + validasi kondisional COA =====
        $baseRules = [
            'cpnyid' => ['required', 'string', 'max:20'],
            'departementid' => ['required', 'string', 'max:100'],
            'wotype' => ['required', 'string', 'max:100'],
            'worequest' => ['required', 'string', 'max:100'],
            'worktypeid' => ['required', 'string', 'max:50'],
            'subworktypeid' => ['required', 'string', 'max:50'],
            'picrequester' => ['required', 'string', 'max:100'],
            'biaya_wo' => ['nullable', 'string', 'max:50'], // dinormalisasi manual
            'location_id' => ['required', 'string', 'max:50'],
            'sub_location_id' => ['required', 'string', 'max:50'],
            'keperluan' => ['nullable', 'string', 'max:1000'],
            'wobudget' => ['required', 'in:Pemberi Kerja,Penerima Kerja'], // Pemberi Kerja/ Penerima Kerja
        ];

        // Kalau budget = Internal (Pemberi Kerja) → COA wajib + perpost dipakai
        $input = $request->all();
        if (($input['wobudget'] ?? null) === 'Pemberi Kerja') {
            $baseRules = array_merge($baseRules, [
                'perpost' => ['required', 'string', 'max:10'],
                'coa_id' => ['required', 'string', 'max:100'],
                'activity_id' => ['required', 'string', 'max:100'],
                'business_unit_id' => ['required', 'string', 'max:100'],
                'department_fin_id' => ['required', 'string', 'max:100'],
                'activity_descr' => ['required', 'string', 'max:255'],
            ]);
        } else {
            // External boleh tanpa COA, tapi jika dikirim tetap batasi panjang
            $baseRules = array_merge($baseRules, [
                'perpost' => ['nullable', 'string', 'max:10'],
                'coa_id' => ['nullable', 'string', 'max:100'],
                'activity_id' => ['nullable', 'string', 'max:100'],
                'business_unit_id' => ['nullable', 'string', 'max:100'],
                'department_fin_id' => ['nullable', 'string', 'max:100'],
                'activity_descr' => ['nullable', 'string', 'max:255'],
            ]);
        }

        $messages = [
            'cpnyid.required' => 'Company wajib.',
            'departementid.required' => 'Department wajib.',
            'wotype.required' => 'WO Type wajib.',
            'worequest.required' => 'WO Request wajib.',
            'worktypeid.required' => 'Worktype wajib.',
            'subworktypeid.required' => 'Sub Worktype wajib.',
            'location_id.required' => 'Location wajib.',
            'sub_location_id.required' => 'Sub Location wajib.',
            'picrequester.required' => 'PIC Requester wajib.',
            'wobudget.required' => 'Budget wajib.',
            'perpost.required' => 'Perpost wajib untuk Budget Pemberi Kerja.',
            'coa_id.required' => 'COA wajib untuk Budget Pemberi Kerja.',
            'activity_id.required' => 'Activity wajib untuk Budget Pemberi Kerja.',
            'business_unit_id.required' => 'Business Unit wajib untuk Budget Pemberi Kerja.',
            'department_fin_id.required' => 'Department Finance wajib untuk Budget Pemberi Kerja.',
            'activity_descr.required' => 'Deskripsi activity wajib untuk Budget Pemberi Kerja.',
        ];

        $validator = \Validator::make($input, $baseRules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }
        $validated = $validator->validated();

        // ===== generate TrApproval dari MsApproval sesuai context =====
        $approvalCtl = app(ApprovalController::class);
        // Pastikan line approval ada (validasi awal)
        $approvalCtl->loadLines($doctype, $validated['cpnyid'], $validated['departementid']);

        \DB::beginTransaction();
        try {
            // === generate autonbr & docid (lock) ===
            // $autonbr = Autonbr::lockForUpdate()
            //     ->where('doctype', $doctype)
            //     ->where('year', $year)
            //     ->where('month', $month)
            //     ->first();

            // if (!$autonbr) {
            //     $autonbr = Autonbr::create([
            //         'doctype' => $doctype,
            //         'year'    => $year,
            //         'month'   => $month,
            //         'status'  => 'A',
            //         'number'  => 1,
            //     ]);
            //     $urutan = 1;
            // } else {
            //     $urutan = $autonbr->number + 1;
            //     $autonbr->update(['number' => $urutan]);
            // }

            // $tglbln = substr($year, 2) . $month;               // YYMM
            // $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'SPB'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2).$month;   // YYMM
            $docid = $doctype.$tglbln.sprintf('%04d', $urutan);

            // === header ===
            $wo = new TrWO();
            $wo->woid = $docid;
            $wo->cpny_id = $validated['cpnyid'];
            $wo->department_id = $validated['departementid'];
            $wo->wotype = $validated['wotype'];
            $wo->worequest = $validated['worequest'];
            $wo->worktypeid = $validated['worktypeid'];
            $wo->subworktypeid = $validated['subworktypeid'];
            $wo->picrequester = $validated['picrequester'];
            $wo->biaya_wo = $toFloat($validated['biaya_wo'] ?? null) ?? 0;
            $wo->location_id = $validated['location_id'];
            $wo->sub_location_id = $validated['sub_location_id'];
            $wo->keperluan = $validated['keperluan'] ?? null;
            $wo->wodate = $dt;
            $wo->status = 'P';
            $wo->created_by = $username;

            // Simpan info Budget + COA (jika Internal)
            $wo->budget_use = $validated['wobudget'];
            if ($validated['wobudget'] === 'Pemberi Kerja') {
                $wo->budget_perpost = $validated['perpost'] ?? null;
                $wo->budget_cpny_id = $validated['cpnyid'] ?? null;
                $wo->budget_account_id = $validated['coa_id'] ?? null;
                $wo->budget_activity_id = $validated['activity_id'] ?? null;
                $wo->budget_business_unit_id = $validated['business_unit_id'] ?? null;
                $wo->budget_department_fin_id = $validated['department_fin_id'] ?? null;
                $wo->budget_activity_descr = $validated['activity_descr'] ?? null;
            } else {
                // Pastikan null untuk keamanan
                $wo->budget_perpost = $validated['perpost'] ?? null;
                $wo->budget_cpny_id = $validated['cpnyid'] ?? null;
                $wo->budget_account_id = null;
                $wo->budget_activity_id = null;
                $wo->budget_business_unit_id = $validated['business_unit_id'] ?? null;
                $wo->budget_department_fin_id = null;
                $wo->budget_activity_descr = null;
            }

            $wo->save();

            // ===== Generate TrApproval (WO)
            $wotype = strtoupper(trim((string) ($validated['wotype'] ?? '')));
            $worktypeid = strtoupper(trim((string) ($validated['worktypeid'] ?? '')));

            $ctx = ['ignore_nominal' => true];

            if ($wotype === 'DAILY') {
                $ctx['approval_conditions'] = array_values(array_filter([$worktypeid]));
            }

            if ($wotype === 'IMPROVEMENT') {
                $ctx['approval_conditions'] = array_values(array_filter([
                    $worktypeid,
                    "Improvement {$worktypeid}",
                ]));
            }

            if (!isset($ctx['approval_conditions']) && $worktypeid !== '') {
                $ctx['approval_conditions'] = [$worktypeid];
            }

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $validated['cpnyid'],
                $validated['departementid'],
                $username,
                $ctx,
                $dt
            );

            // (opsional) Jika kamu punya kolom hint di header WO, bisa disimpan.
            // Contoh:
            // if (!empty($firstApprovalUsernames)) {
            //     $wo->first_approvers = $firstApprovalUsernames;
            //     $wo->save();
            // }

            // ===== Attachments (opsional)
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpnyid' => $validated['cpnyid'],
                    'departementid' => $validated['departementid'],
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    \DB::rollBack();

                    return response()->json([
                        'message' => 'Failed to create WO',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // ===== Notifikasi ke approver pertama
            $eid = \Hashids::encode($wo->id);
            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $wo->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                'WO',
                url('/showwos/'.$eid),
                [
                    'info' => $validated['keperluan'] ?? null,
                    'createdby' => $wo->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            \DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'WO created successfully',
                'id' => $wo->id,
                'docid' => $docid,
                'attachments' => $uploadResult, // opsional
            ]);
        } catch (\Throwable $e) {
            \DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create WO',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function editWo($hash)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $wo = TrWO::with([
            'worktype',       // MsWorktype
            'subworktype',    // MsSubworktype
            'location',       // MsLocation
            'sublocation',    // MsSubLocation
            'creator:username,name',
            'spbs',
            'sppbs',
            'sppjs',            
            'sppts',
        ])->findOrFail($id);

        $user = request()->user();
        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        // attachments
        $rows = TrAttachment::where('refnbr', $wo->woid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }
        $storage = new StorageClient([
            'projectId' => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $object = $bucket->object($objectPath);
            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'id' => $r->id,
                'display_name' => $r->attachment_name,
                'created_by' => $r->created_by,
                'created_at' => $r->created_at,
                'url' => $signedUrl,
                'folder' => $r->folder,
                'filename' => $r->filename,
                'extention' => $r->extention,
                'size' => $r->filesize,
            ];
        });

        // ==== nilai awal untuk prefill (aman jika relasi null) ====
        $prefill = [
            'cpnyid' => $wo->cpny_id ?? '',
            'departementid' => $wo->department_id ?? '',
            'wotype' => $wo->wotype ?? '',            // (string nama kategori, sama seperti create)
            'worequest' => $wo->worequest ?? '',         // (string nama kategori)
            'location_id' => $wo->location_id ?? '',
            'location_name' => optional($wo->location)->location_name ?? ($wo->location_id ?? ''),
            'sub_location_id' => $wo->sub_location_id ?? '',
            'sub_location_name' => optional($wo->sublocation)->sub_location_name ?? ($wo->sub_location_id ?? ''),
            'worktypeid' => $wo->worktypeid ?? '',
            'worktype_name' => optional($wo->worktype)->worktype_name ?? ($wo->worktypeid ?? ''),
            'subworktypeid' => $wo->subworktypeid ?? '',
            'subworktype_name' => optional($wo->subworktype)->subworktype_name ?? ($wo->subworktypeid ?? ''),
            'picrequester' => $wo->picrequester ?? ($wo->created_by ?? ''),
            'biaya_wo' => $wo->biaya_wo ?? null,
            'keperluan' => $wo->keperluan ?? '',
            'woid' => $wo->woid ?? '',
            'hash' => request()->route('hash') ?? '',

            'budget_use' => $wo->budget_use,               // Internal / External
            'perpost' => $wo->budget_perpost,            // year
            'coa_id' => $wo->budget_account_id,
            'activity_id' => $wo->budget_activity_id,
            'business_unit_id' => $wo->budget_business_unit_id,
            'department_fin_id' => $wo->budget_department_fin_id,
            'activity_descr' => $wo->budget_activity_descr,
        ];

        return view('pages.wos.editwos', compact(
            'wo', 'usercpny', 'usercpny2', 'userdept', 'userdept2', 'attachments', 'prefill'
        ));
    }

    public function updateWo(Request $request, $hash)
    {
        // --- ambil id dari hash ---
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'WO tidak ditemukan.');

        $doctype = 'WO';
        $user = $request->user();
        $username = $user->username ?? 'system';

        $dt = \Carbon\Carbon::now();
        $datestamp = $dt->toDateTimeString();

        // Normalisasi angka lokal → float (copy dari create)
        $toFloat = function ($v): ?float {
            if ($v === null || $v === '') {
                return null;
            }
            $s = preg_replace('/\s+/', '', (string) $v);
            $hasComma = strpos($s, ',') !== false;
            $hasDot = strpos($s, '.') !== false;

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot = strrpos($s, '.');
                if ($lastComma > $lastDot) {
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot) {
                if (substr_count($s, '.') > 1) {
                    $s = str_replace('.', '', $s);
                }
            }

            return is_numeric($s) ? (float) $s : null;
        };

        // ===== Validasi utama + validasi kondisional COA (SAMA dengan create) =====
        $baseRules = [
            'cpnyid' => ['required', 'string', 'max:20'],
            'departementid' => ['required', 'string', 'max:100'],
            'wotype' => ['required', 'string', 'max:100'],
            'worequest' => ['required', 'string', 'max:100'],
            'worktypeid' => ['required', 'string', 'max:50'],
            'subworktypeid' => ['required', 'string', 'max:50'],
            'picrequester' => ['required', 'string', 'max:100'],
            'biaya_wo' => ['nullable', 'string', 'max:50'],
            'location_id' => ['required', 'string', 'max:50'],
            'sub_location_id' => ['required', 'string', 'max:50'],
            'keperluan' => ['nullable', 'string', 'max:1000'],
            'wobudget' => ['required', 'in:Pemberi Kerja,Penerima Kerja'],
        ];

        $input = $request->all();
        if (($input['wobudget'] ?? null) === 'Pemberi Kerja') {
            $baseRules = array_merge($baseRules, [
                'perpost' => ['required', 'string', 'max:10'],
                'coa_id' => ['required', 'string', 'max:100'],
                'activity_id' => ['required', 'string', 'max:100'],
                'business_unit_id' => ['required', 'string', 'max:100'],
                'department_fin_id' => ['required', 'string', 'max:100'],
                'activity_descr' => ['required', 'string', 'max:255'],
            ]);
        } else {
            $baseRules = array_merge($baseRules, [
                'perpost' => ['nullable', 'string', 'max:10'],
                'coa_id' => ['nullable', 'string', 'max:100'],
                'activity_id' => ['nullable', 'string', 'max:100'],
                'business_unit_id' => ['nullable', 'string', 'max:100'],
                'department_fin_id' => ['nullable', 'string', 'max:100'],
                'activity_descr' => ['nullable', 'string', 'max:255'],
            ]);
        }

        $messages = [
            'cpnyid.required' => 'Company wajib.',
            'departementid.required' => 'Department wajib.',
            'wotype.required' => 'WO Type wajib.',
            'worequest.required' => 'WO Request wajib.',
            'worktypeid.required' => 'Worktype wajib.',
            'subworktypeid.required' => 'Sub Worktype wajib.',
            'location_id.required' => 'Location wajib.',
            'sub_location_id.required' => 'Sub Location wajib.',
            'picrequester.required' => 'PIC Requester wajib.',
            'wobudget.required' => 'Budget wajib.',
            'perpost.required' => 'Perpost wajib untuk Budget Pemberi Kerja.',
            'coa_id.required' => 'COA wajib untuk Budget Pemberi Kerja.',
            'activity_id.required' => 'Activity wajib untuk Budget Pemberi Kerja.',
            'business_unit_id.required' => 'Business Unit wajib untuk Budget Pemberi Kerja.',
            'department_fin_id.required' => 'Department Finance wajib untuk Budget Pemberi Kerja.',
            'activity_descr.required' => 'Deskripsi activity wajib untuk Budget Pemberi Kerja.',
        ];

        $validator = \Validator::make($input, $baseRules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }
        $validated = $validator->validated();

        // ===== ApprovalController (SAMA seperti create) =====
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada (validasi awal) -> sama seperti create
        $approvalCtl->loadLines($doctype, $validated['cpnyid'], $validated['departementid']);

        \DB::beginTransaction();
        try {
            $wo = TrWO::lockForUpdate()->findOrFail($id);
            $docid = $wo->woid;

            // --- update header (SAMA seperti create) ---
            $wo->cpny_id = $validated['cpnyid'];
            $wo->department_id = $validated['departementid'];
            $wo->wotype = $validated['wotype'];
            $wo->worequest = $validated['worequest'];
            $wo->worktypeid = $validated['worktypeid'];
            $wo->subworktypeid = $validated['subworktypeid'];
            $wo->picrequester = $validated['picrequester'];
            $wo->biaya_wo = $toFloat($validated['biaya_wo'] ?? null) ?? 0;
            $wo->location_id = $validated['location_id'];
            $wo->sub_location_id = $validated['sub_location_id'];
            $wo->keperluan = $validated['keperluan'] ?? null;
            $wo->status = 'P';
            $wo->updated_by = $username;

            // Simpan info Budget + COA (SAMA seperti create)
            $wo->budget_use = $validated['wobudget'];

            if ($validated['wobudget'] === 'Pemberi Kerja') {
                $wo->budget_perpost = $validated['perpost'] ?? null;
                $wo->budget_cpny_id = $validated['cpnyid'] ?? null;
                $wo->budget_account_id = $validated['coa_id'] ?? null;
                $wo->budget_activity_id = $validated['activity_id'] ?? null;
                $wo->budget_business_unit_id = $validated['business_unit_id'] ?? null;
                $wo->budget_department_fin_id = $validated['department_fin_id'] ?? null;
                $wo->budget_activity_descr = $validated['activity_descr'] ?? null;
            } else {
                $wo->budget_perpost = $validated['perpost'] ?? null;
                $wo->budget_cpny_id = null;
                $wo->budget_account_id = null;
                $wo->budget_activity_id = null;
                $wo->budget_business_unit_id = null;
                $wo->budget_department_fin_id = null;
                $wo->budget_activity_descr = null;
            }

            $wo->save();

            // ===== Generate TrApproval (WO) - SAMA seperti create =====
            $wotype = strtoupper(trim((string) ($validated['wotype'] ?? '')));
            $worktypeid = strtoupper(trim((string) ($validated['worktypeid'] ?? '')));

            $ctx = ['ignore_nominal' => true];

            if ($wotype === 'DAILY') {
                $ctx['approval_conditions'] = array_values(array_filter([$worktypeid]));
            }

            if ($wotype === 'IMPROVEMENT') {
                $ctx['approval_conditions'] = array_values(array_filter([
                    $worktypeid,
                    "Improvement {$worktypeid}",
                ]));
            }

            if (!isset($ctx['approval_conditions']) && $worktypeid !== '') {
                $ctx['approval_conditions'] = [$worktypeid];
            }

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $validated['cpnyid'],
                $validated['departementid'],
                $username,
                $ctx,
                $dt
            );

            // ===== Attachments (SAMA seperti create) =====
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpnyid' => $validated['cpnyid'],
                    'departementid' => $validated['departementid'],
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    \DB::rollBack();

                    return response()->json([
                        'message' => 'Failed to update WO',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // ===== Notifikasi ke approver pertama (SAMA seperti create) =====
            $eid = \Hashids::encode($wo->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $wo->status,
                'WO',
                url('/showwos/'.$eid),
                [
                    'info' => $validated['keperluan'] ?? null,
                    'createdby' => $wo->created_by,
                    'date' => $dt->toDateTimeString(),
                    'updatedby' => $username,
                ]
            );

            \DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'WO updated successfully',
                'id' => $wo->id,
                'docid' => $docid,
                'attachments' => $uploadResult,
            ]);
        } catch (\Throwable $e) {
            \DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to update WO',
                'error' => config('app.debug') ? $e->getMessage() : null,
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

    public function showWo($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $wo = TrWO::with([
            'worktype',
            'subworktype',
            'location',
            'sublocation',
            'creator:username,name',
        ])->findOrFail($id);

        // // =========================
        // // APPROVAL + ATTACHMENTS (punya kamu tetap)
        // // =========================
        // $approval = T_approval::where('docid', $wo->woid)
        //     ->where('status', '<>', 'X')
        //     ->orderBy('created_at')
        //     ->orderBy('aprvid')
        //     ->get();

        $rows = TrAttachment::where('refnbr', $wo->woid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // siapkan Signed URL dari GCS
        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId' => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        // map jadi data siap pakai di view
        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;   // ex: att-purchasing-app/wo/2025/xxxx-file.pdf
            $object = $bucket->object($objectPath);

            // Signed URL 10 menit
            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                // kalau gagal signed URL, biarkan null; di UI tampilkan nama saja
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'display_name' => $r->attachment_name,         // nama yang enak dibaca
                'created_by' => $r->created_by,
                'created_at' => $r->created_at,
                'url' => $signedUrl,                  // bisa null jika gagal
                'folder' => $r->folder,
                'filename' => $r->filename,
                'extention' => $r->extention,
                'size' => $r->filesize,
            ];
        });

        // ... signed url mapping attachments (punya kamu tetap)
        // $attachments = ...

        // =========================
        // ✅ HITUNG isProcessor DI CONTROLLER
        // department_id user: "ENGINEERING,ENGINEERING HVAC,..." => array
        // =========================
        $userDeptRaw = (string) ($user->department_id ?? '');
        $userDepts = collect(explode(',', $userDeptRaw))
            ->map(fn ($v) => strtoupper(trim($v)))
            ->filter()
            ->unique()
            ->values();

        // MsWorktypeDept: ambil semua department utk worktype WO ini
        $worktypeDepts = MsWorktypeDept::where('worktypeid', $wo->worktypeid)
            ->pluck('department_id')
            ->map(fn ($v) => strtoupper(trim((string) $v)))
            ->filter()
            ->unique()
            ->values();

        $deptMatch = $worktypeDepts->contains('ALL')
            || $userDepts->intersect($worktypeDepts)->isNotEmpty();

        // PIC WO boleh proses juga
        $loginUsername = strtolower(trim((string) ($user->username ?? $user->name ?? '')));
        $pic = strtolower(trim((string) ($wo->pic_wo ?? '')));

        $isPicWo = ($pic !== '' && $pic === $loginUsername); // ✅ hanya PIC yang boleh save/edit
        $hasPic = ($pic !== ''); // ✅ sudah diprocess

        $canProcess = ($deptMatch && !$hasPic) || $isPicWo; // ✅ tombol process hanya kalau belum ada PIC, atau user adalah PIC

        // =========================
        // canUpload + userdept (punya kamu)
        // =========================
        $loginUsername2 = $user->username ?? $user->name ?? null;
        $canUpload = $wo->created_by === $loginUsername2;

        $userdept = Userdept::where('username', '=', $user->username)->get();
        $userdept2 = Userdept::where('username', '=', $user->username)->first();

        return view('pages.wos.showwos', compact(
            'wo',
            'attachments',
            'hash',
            'canUpload',
            'userdept',
            'userdept2',
            'canProcess',
            'isPicWo',
            'worktypeDepts',
            'userDepts'
        ));
    }

    public function approveWo(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'WO';

        $wo = TrWO::with('creator')->where('woid', $docid)->first();
        if (!$wo) {
            return response()->json(['success' => false, 'message' => 'WO not found'], 404);
        }

        $eid = Hashids::encode($wo->id);
        $docUrl = url('/showwos/'.$eid);
        $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

        $result = app(ApprovalController::class)->approveStep(
            $wo->woid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($wo, $fullname, $docUrl) {
                $wo->status = 'C';
                $wo->status_pekerjaan = 'H';
                $wo->completed_by = $wo->completed_by ?: auth()->user()->username;
                $wo->completed_at = $now;
                $wo->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $wo->woid,
                    'WO',
                    'C',
                    $wo->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $wo->cpny_id ?? $wo->cpnyid ?? '',
                        'deptname' => $wo->department_id ?? $wo->departementid ?? '',
                        'date' => $wo->wodate,
                        'info' => $wo->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($wo, $docUrl) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $wo->woid,
                    'WO',
                    'P',
                    'WO',
                    $docUrl,
                    [
                        'info' => $wo->keperluan,
                        'createdby' => $wo->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $wo->completed_by = auth()->user()->username;
                $wo->completed_at = $now;
                $wo->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectWo(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'WO';

        $wo = TrWO::with('creator')->where('woid', $docid)->first();
        if (!$wo) {
            return response()->json(['success' => false, 'message' => 'WO not found'], 404);
        }

        $eid = Hashids::encode($wo->id);
        $docUrl = url('/showwos/'.$eid);
        $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $wo->woid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($wo, $fullname, $docUrl) {
                $wo->status = 'R';
                $wo->completed_by = auth()->user()->username;
                $wo->completed_at = $now;
                $wo->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $wo->woid,
                    'WO',
                    'R',
                    $wo->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $wo->cpny_id ?? $wo->cpnyid ?? '',
                        'deptname' => $wo->department_id ?? $wo->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $wo->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($wo->id, 'WO', request());
                } catch (\Throwable $e) {
                }
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'WO rejected successfully']);
    }

    public function reviseWo(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'WO';

        $wo = TrWO::with('creator')->where('woid', $docid)->first();
        if (!$wo) {
            return response()->json(['success' => false, 'message' => 'WO not found'], 404);
        }

        $eid = Hashids::encode($wo->id);
        $docUrl = url('/showwos/'.$eid);
        $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $wo->woid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($wo, $fullname, $docUrl) {
                // === HEADER WO -> D ===
                $wo->status = 'D';
                $wo->completed_by = auth()->user()->username;
                $wo->completed_at = $now;
                $wo->save();

                // === Email ke requester ===
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $wo->woid,
                    'WO',
                    'D',
                    $wo->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $wo->cpny_id ?? $wo->cpnyid ?? '',
                        'deptname' => $wo->department_id ?? $wo->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $wo->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,   // <<< tambahkan ini
                    ]
                );

                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($wo->id, 'WO', request());
                } catch (\Throwable $e) {
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Revise failed',
            ], 403);
        }

        return response()->json(['success' => true, 'message' => 'WO revised successfully']);
    }

    // // public function approveWo(Request $request, $docid)
    // // {
    // //     $now  = Carbon::now();
    // //     $user = $request->user();

    // //     // $wo = TrWO::where('woid', $docid)->first();
    // //     $wo = TrWO::with('creator')
    // //         ->where('woid', $docid)
    // //         ->first();
    // //     $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

    // //     if (!$wo) {
    // //         return response()->json(['success' => false, 'message' => 'WO not found'], 404);
    // //     }

    // //     // pastikan user memang approver aktif (status P) di doc ini
    // //     $tApproval = T_approval::where('docid', $wo->woid)
    // //         ->where('status', 'P')
    // //         ->where('aprvusername', 'ilike', "%{$user->username}%")
    // //         ->whereNotNull('aprvdatebefore')
    // //         ->orderBy('aprvid', 'ASC')
    // //         ->first();

    // //     if (!$tApproval) {
    // //         return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
    // //     }

    // //     DB::beginTransaction();
    // //     try {
    // //         // Set current approver -> Approved
    // //         $tApproval->status         = 'A';
    // //         $tApproval->aprvdateafter  = $now;
    // //         $tApproval->aprvusername   = $user->username;
    // //         $tApproval->name           = $user->name;
    // //         $tApproval->save();

    // //         // Update header informasi "terakhir diproses"
    // //         $wo->completed_by = $user->username;
    // //         $wo->completed_at = $now;
    // //         $wo->save();

    // //         // Hitung sisa pending setelah approve ini
    // //         $pendingCount = T_approval::where('docid', $wo->woid)
    // //             ->where('status', 'P')
    // //             ->count();

    // //         // Pemetaan judul sesuai status
    // //         $subjectMap = [
    // //             'P' => 'Waiting Approval',
    // //             'R' => 'Rejected Approval',
    // //             'D' => 'Revise Approval',
    // //             'A' => 'Approved',
    // //             'C' => 'Completed',
    // //         ];

    // //         $eid = Hashids::encode($wo->id);

    // //         if ($pendingCount === 0) {
    // //             // Tidak ada approver lagi -> dokumen complete
    // //             $wo->status       = 'C';
    // //             $wo->completed_by = $user->username;
    // //             $wo->completed_at = $now;
    // //             $wo->save();

    // //             // Kirim email ke requester (creator)
    // //             $status        = 'C';
    // //             $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    // //             $data = [
    // //                 'docid'     => $wo->woid,
    // //                 'cpnyid'    => $wo->cpny_id ?? $wo->cpnyid ?? '',
    // //                 'deptname'  => $wo->department_id ?? $wo->departementid ?? '',
    // //                 'date'      => $wo->wodate,
    // //                 'fullname'  => $fullname,  // nama penerima di email
    // //                 'name'      => $fullname,  // fallback
    // //                 'createdby' => $fullname,
    // //                 'docname'   => 'WO',
    // //                 'info'      => $wo->keperluan,
    // //                 'status'    => $status,
    // //                 'url'       => url('/showwos/' . $eid),
    // //             ];

    // //             $recipients = User::where('username', $wo->created_by)
    // //                 ->where('status', 'A')
    // //                 ->get();

    // //             foreach ($recipients as $rcp) {
    // //                 try {
    // //                     Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
    // //                         $to = $rcp->notification_email ?? $rcp->email; // pakai field yang memang ada
    // //                         $message->to($to)
    // //                             ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
    // //                             ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    // //                     });
    // //                 } catch (\Throwable $e) {
    // //                     Log::error('Failed sending WO completion email', ['error' => $e->getMessage()]);
    // //                 }
    // //             }
    // //         } else {
    // //             // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
    // //             $next = T_approval::where('docid', $wo->woid)
    // //                 ->where('status', 'P')
    // //                 ->orderBy('aprvid', 'ASC')
    // //                 ->first();

    // //             if ($next) {
    // //                 // Stempel "datebefore" untuk approver berikutnya
    // //                 $next->aprvdatebefore = $now;
    // //                 $next->save();

    // //                 // Kirim email ke semua username yang ada di kolom aprvusername (dipisah koma)
    // //                 $status        = 'P';
    // //                 $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    // //                 $data = [
    // //                     'docid'     => $next->docid,
    // //                     'cpnyid'    => $next->aprvcpnyid,
    // //                     'deptname'  => $next->aprvdeptid,
    // //                     'date'      => $next->aprvdatebefore,
    // //                     'fullname'  => $next->name,
    // //                     'name'      => $next->name,
    // //                     'createdby' => $wo->created_by,
    // //                     'docname'   => 'WO',
    // //                     'info'      => $wo->keperluan,
    // //                     'status'    => $status,
    // //                     'url'       => url('/showwos/' . $eid),
    // //                 ];

    // //                 $usernames = array_filter(array_map('trim', explode(',', (string) $next->aprvusername)));
    // //                 if (!empty($usernames)) {
    // //                     $recipients = User::whereIn('username', $usernames)
    // //                         ->where('status', 'A')
    // //                         ->get();

    // //                     foreach ($recipients as $rcp) {
    // //                         try {
    // //                             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
    // //                                 $to = $rcp->notification_email ?? $rcp->email;
    // //                                 $message->to($to)
    // //                                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
    // //                                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    // //                             });
    // //                         } catch (\Throwable $e) {
    // //                             Log::error('Failed sending WO waiting-approval email', ['error' => $e->getMessage()]);
    // //                         }
    // //                     }
    // //                 } else {
    // //                     Log::warning('Next approver has empty aprvusername list', ['docid' => $wo->woid]);
    // //                 }
    // //             }
    // //         }

    // //         DB::commit();
    // //         return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    // //     } catch (\Throwable $e) {
    // //         DB::rollBack();
    // //         Log::error('Approve WO failed', ['error' => $e->getMessage()]);
    // //         return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
    // //     }
    // // }

    // // public function rejectWo(Request $request, $docid)
    // // {
    // //     $now  = Carbon::now();
    // //     $user = $request->user();

    // //     // $wo = TrWO::where('woid', $docid)->first();
    // //     $wo = TrWO::with('creator')
    // //         ->where('woid', $docid)
    // //         ->first();
    // //     $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

    // //     if (!$wo) {
    // //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    // //     }

    // //     // Validasi: user harus approver aktif (status P) pada dokumen ini
    // //     $tApproval = T_approval::where('docid', $wo->woid)
    // //         ->where('status', 'P')
    // //         ->where('aprvusername', 'ilike', "%{$user->username}%")
    // //         ->whereNotNull('aprvdatebefore')
    // //         ->orderBy('aprvid', 'ASC')
    // //         ->first();

    // //     if (!$tApproval) {
    // //         return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
    // //     }

    // //     DB::beginTransaction();
    // //     try {
    // //         // Tandai approval saat ini sebagai Rejected
    // //         $tApproval->status        = 'R';
    // //         $tApproval->aprvdateafter = $now;
    // //         $tApproval->aprvusername  = $user->username; // catat siapa yang reject
    // //         $tApproval->name          = $user->name;
    // //         $tApproval->save();

    // //         // Update header WO
    // //         $wo->status       = 'R';
    // //         $wo->completed_by = $user->username;
    // //         $wo->completed_at = $now;
    // //         $wo->save();

    // //         // Batalkan semua approval yang masih pending
    // //         T_approval::where('docid', $wo->woid)
    // //             ->where('status', 'P')
    // //             ->update(['status' => 'X']);

    // //         DB::commit();
    // //     } catch (\Throwable $e) {
    // //         DB::rollBack();
    // //         Log::error('Reject WO failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    // //         return response()->json(['success' => false, 'message' => 'Reject failed'], 500);
    // //     }

    // //     // === Kirim Email ke requester (creator) ===
    // //     $status = 'R'; // Rejected
    // //     $subjectMap = [
    // //         'P' => 'Waiting Approval',
    // //         'R' => 'Rejected Approval',
    // //         'D' => 'Revise Approval',
    // //         'A' => 'Approved',
    // //         'C' => 'Completed',
    // //     ];
    // //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    // //     $eid = Hashids::encode($wo->id);

    // //     $data = [
    // //         'docid'     => $wo->woid,
    // //         'cpnyid'    => $wo->cpny_id ?? $wo->cpnyid ?? '',
    // //         'deptname'  => $wo->department_id ?? $wo->departementid ?? '',
    // //         'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
    // //         'fullname'  => $fullname,               // view email kita pakai $fullname
    // //         'name'      => $fullname,               // fallback jika view pakai $name
    // //         'createdby' => $fullname,
    // //         'docname'   => 'WO',
    // //         'info'      => $wo->keperluan,
    // //         'status'    => $status,
    // //         'url'       => url('/showwos/' . $eid),
    // //     ];

    // //     $recipients = User::where('username', $wo->created_by)
    // //         ->where('status', 'A')
    // //         ->get();

    // //     foreach ($recipients as $rcp) {
    // //         try {
    // //             $to = $rcp->notification_email ?? $rcp->email; // sesuaikan field yang tersedia
    // //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    // //                 $message->to($to)
    // //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
    // //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    // //             });
    // //         } catch (\Throwable $e) {
    // //             Log::error('Failed sending WO rejected email', [
    // //                 'docid' => $data['docid'],
    // //                 'to'    => $rcp->username,
    // //                 'error' => $e->getMessage()
    // //             ]);
    // //         }
    // //     }

    // //     // Simpan komentar penolakan (jika ada)
    // //     try {
    // //         app('App\Http\Controllers\SendCommentController')
    // //             ->sendmsg($wo->id, 'WO', $request);
    // //     } catch (\Throwable $e) {
    // //         Log::warning('SendComment after reject failed', [
    // //             'docid' => $wo->woid,
    // //             'error' => $e->getMessage()
    // //         ]);
    // //     }

    // //     return response()->json(['success' => true, 'message' => 'WO rejected successfully']);
    // // }

    // // public function reviseWo(Request $request, $docid)
    // // {
    // //     $now  = Carbon::now();
    // //     $user = $request->user();

    // //     // $wo = TrWO::where('woid', $docid)->first();
    // //     $wo = TrWO::with('creator')
    // //         ->where('woid', $docid)
    // //         ->first();
    // //     $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

    // //     if (!$wo) {
    // //         return response()->json(['success' => false, 'message' => 'WO not found'], 404);
    // //     }

    // //     // Pastikan user adalah approver aktif (status P) dokumen ini
    // //     $tApproval = T_approval::where('docid', $wo->woid)
    // //         ->where('status', 'P')
    // //         ->where('aprvusername', 'ilike', "%{$user->username}%")
    // //         ->whereNotNull('aprvdatebefore')
    // //         ->orderBy('aprvid', 'ASC')
    // //         ->first();

    // //     if (!$tApproval) {
    // //         return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
    // //     }

    // //     DB::beginTransaction();
    // //     try {
    // //         // Tandai approval saat ini sebagai Revise (D)
    // //         $tApproval->status        = 'D';
    // //         $tApproval->aprvdateafter = $now;
    // //         $tApproval->aprvusername  = $user->username;  // catat siapa yang revise
    // //         $tApproval->name          = $user->name;
    // //         $tApproval->save();

    // //         // Update header WO
    // //         $wo->status       = 'D';
    // //         $wo->completed_by = $user->username;        // mengikuti pola existing
    // //         $wo->completed_at = $now;
    // //         $wo->save();

    // //         // Batalkan approval lain yang masih pending
    // //         T_approval::where('docid', $wo->woid)
    // //             ->where('status', 'P')
    // //             ->update(['status' => 'X']);

    // //         DB::commit();
    // //     } catch (\Throwable $e) {
    // //         DB::rollBack();
    // //         Log::error('Revise WO failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    // //         return response()->json(['success' => false, 'message' => 'Revise failed'], 500);
    // //     }

    // //     // === Kirim email ke requester (creator) ===
    // //     $status = 'D'; // Revise
    // //     $subjectMap = [
    // //         'P' => 'Waiting Approval',
    // //         'R' => 'Rejected Approval',
    // //         'D' => 'Revise Approval',
    // //         'A' => 'Approved',
    // //         'C' => 'Completed',
    // //     ];
    // //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    // //     $eid = Hashids::encode($wo->id);

    // //     $data = [
    // //         'docid'     => $wo->woid,
    // //         'cpnyid'    => $wo->cpny_id ?? $wo->cpnyid ?? '',
    // //         'deptname'  => $wo->department_id ?? $wo->departementid ?? '',
    // //         'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
    // //         'fullname'  => $fullname,             // template email pakai $fullname
    // //         'name'      => $fullname,             // fallback jika view pakai $name
    // //         'createdby' => $fullname,
    // //         'docname'   => 'WO',
    // //         'info'      => $wo->keperluan,
    // //         'status'    => $status,
    // //         'url'       => url('/showwos/' . $eid),
    // //     ];

    // //     $recipients = User::where('username', $wo->created_by)
    // //         ->where('status', 'A')
    // //         ->get();

    // //     foreach ($recipients as $rcp) {
    // //         try {
    // //             $to = $rcp->notification_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
    // //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    // //                 $message->to($to)
    // //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
    // //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    // //             });
    // //         } catch (\Throwable $e) {
    // //             Log::error('Failed sending WO revise email', [
    // //                 'docid' => $data['docid'],
    // //                 'to'    => $rcp->username,
    // //                 'error' => $e->getMessage()
    // //             ]);
    // //         }
    // //     }

    // //     // Simpan komentar revisi (jika ada)
    // //     try {
    // //         app('App\Http\Controllers\SendCommentController')
    // //             ->sendmsg($wo->id, 'WO', $request);
    // //     } catch (\Throwable $e) {
    // //         Log::warning('SendComment after revise failed', [
    // //             'docid' => $wo->woid,
    // //             'error' => $e->getMessage()
    // //         ]);
    // //     }

    // //     return response()->json(['success' => true, 'message' => 'WO revised successfully']);
    // // }

    // // public function checkApproval($id, $action)
    // // {
    // //     $user = Auth::user(); // Ambil user yang login
    // //     // dd($action);
    // //     // Query dasar untuk pengecekan
    // //     $query = T_approval::where('docid', $id)
    // //                 ->where('aprvusername', 'ilike', '%' . $user->username . '%')
    // //                 ->where('status', 'P');

    // //     // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
    // //     if (in_array($action, ['reject', 'revise','approve'])) {
    // //         $query->whereNotNull('aprvdatebefore');
    // //     }

    // //     // Cek apakah user bisa melakukan aksi
    // //     $canPerformAction = $query->exists();

    // //     return response()->json(['canPerformAction' => $canPerformAction]);
    // // }

    // public function tracking($hash)
    // {
    //     $id = Hashids::decode($hash)[0] ?? null;
    //     abort_if(!$id, 404);

    //     $wo = TrWO::findOrFail($id);

    //     $getName = function (?string $username) {
    //         if (!$username) {
    //             return null;
    //         }
    //         $u = User::where('username', $username)->first();

    //         return $u->name ?? $username;
    //     };

    //     $createdByName = $getName($wo->created_by ?? null);
    //     $createdAt = $wo->created_at ? \Carbon\Carbon::parse($wo->created_at)->format('Y-m-d H:i') : null;

    //     $completedByName = $getName($wo->completed_by ?? null);
    //     $completedAt = $wo->completed_at ? \Carbon\Carbon::parse($wo->completed_at)->format('Y-m-d H:i') : null;

    //     // kolom opsional, kalau tidak ada biarkan null
    //     $rejectedByName = $getName($wo->rejected_by ?? null);
    //     $rejectedAt = isset($wo->rejected_at) ? \Carbon\Carbon::parse($wo->rejected_at)->format('Y-m-d H:i') : null;

    //     $revisedByName = $getName($wo->revised_by ?? null);
    //     $revisedAt = isset($wo->revised_at) ? \Carbon\Carbon::parse($wo->revised_at)->format('Y-m-d H:i') : null;

    //     $status = (string) ($wo->status ?? '');
    //     $labelMap = [
    //         'P' => 'Waiting approval',
    //         'R' => 'Rejected',
    //         'D' => 'Revise',
    //         'C' => 'Completed',
    //     ];
    //     $statusLabel = $labelMap[$status] ?? $status;

    //     // selalu mulai dari Submitted
    //     $steps = [[
    //         'key' => 'submitted',
    //         'title' => 'WO',
    //         'status' => 'C',              // dibuat = completed
    //         'status_label' => 'Submitted',
    //         'by' => $createdByName,
    //         'at' => $createdAt,
    //     ]];

    //     switch ($status) {
    //         case 'P':
    //             // masih menunggu/berjalan → tampilkan Approval saja
    //             $steps[] = [
    //                 'key' => 'approval',
    //                 'title' => 'Approval',
    //                 'status' => 'P',
    //                 'status_label' => 'Waiting approval',
    //                 'by' => $completedByName,
    //                 'at' => $completedAt,
    //             ];
    //             break;

    //         case 'R':
    //             // DITOLAK → langsung Submitted → Rejected (tanpa Approval)
    //             $steps[] = [
    //                 'key' => 'rejected',
    //                 'title' => 'Rejected',
    //                 'status' => 'R',
    //                 'status_label' => 'Rejected',
    //                 'by' => $completedByName,
    //                 'at' => $completedAt,
    //             ];
    //             break;

    //         case 'D':
    //             // REVISE → Submitted → Revise
    //             $steps[] = [
    //                 'key' => 'revise',
    //                 'title' => 'Revise',
    //                 'status' => 'D',
    //                 'status_label' => 'Revise',
    //                 'by' => $completedByName,
    //                 'at' => $completedAt,
    //             ];
    //             break;

    //         case 'C':
    //             // SELESAI → bisa langsung Submitted → Completed
    //             // (kalau kamu ingin menampilkan Approval yang sudah dilalui,
    //             // tambahkan step 'approval' sebelum 'completed')
    //             $steps[] = [
    //                 'key' => 'completed',
    //                 'title' => 'Completed',
    //                 'status' => 'C',
    //                 'status_label' => 'Completed',
    //                 'by' => $completedByName,
    //                 'at' => $completedAt,
    //             ];
    //             break;

    //         default:
    //             // status tidak dikenal → biarkan hanya Submitted
    //             break;
    //     }

    //     return response()->json([
    //         'doc' => $wo->woid ?? (string) $wo->id,
    //         'steps' => $steps,
    //         'status' => $status,
    //         'status_label' => $statusLabel,
    //     ]);
    // }

    public function tracking($hash)
    {
        $id = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $wo = \App\Models\TrWO::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $steps = [];

        // ======================
        // 1. SUBMITTED
        // ======================
        $steps[] = [
            'type' => 'header',
            'title' => 'WO Submitted',
            'status' => 'C',
            'status_label' => 'Submitted',
            'by' => $getName($wo->created_by),
            'at' => optional($wo->created_at)->format('Y-m-d H:i'),
        ];

        // ======================
        // 2. GET APPROVALS
        // ======================
        $all = \App\Models\TrApproval::where('refnbr', $wo->woid)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->get();

        // ======================
        // 3. GROUP INTO CYCLES
        // ======================
        $groups = $all->groupBy(function ($a) {
            return \Carbon\Carbon::parse($a->created_at)->format('Y-m-d H:i:s');
        });

        $hasMultipleCycle = $groups->count() > 1;
        $cycleIndex = 1;

        foreach ($groups as $group) {

            // ✅ SHOW cycle only if needed
            if ($hasMultipleCycle) {
                $steps[] = [
                    'type' => 'cycle',
                    'title' => 'Cycle ' . $cycleIndex,
                ];
            }

            // sort by level
            $sorted = $group->sortBy(fn($a) => (float)$a->aprv_leveling);

            foreach ($sorted as $a) {

                $map = match ($a->status) {
                    'A' => ['label' => 'Approved', 'status' => 'C'],
                    'P' => ['label' => 'Waiting Approval', 'status' => 'P'],
                    'R' => ['label' => 'Rejected', 'status' => 'R'],
                    'D' => ['label' => 'Revised', 'status' => 'D'],
                    'X' => ['label' => 'Cancelled', 'status' => 'X'],
                    default => ['label' => 'Pending', 'status' => '_']
                };

                $steps[] = [
                    'type' => 'approval',
                    'title' => 'Approval Lv ' . $a->aprv_leveling,
                    'status' => $map['status'],          // ✅ clean status
                    'status_label' => $map['label'],     // ✅ clean label
                    'by' => $getName($a->aprv_username),
                    'at' => $a->aprv_dateafter
                        ? \Carbon\Carbon::parse($a->aprv_dateafter)->format('Y-m-d H:i')
                        : null,
                ];
            }

            $cycleIndex++;
        }

        // ======================
        // 4. FINAL STATUS
        // ======================
        if ($wo->status === 'C') {
            $steps[] = [
                'type' => 'footer',
                'title' => 'Completed',
                'status' => 'C',
                'status_label' => 'Completed',
                'by' => $getName($wo->completed_by),
                'at' => optional($wo->completed_at)->format('Y-m-d H:i'),
            ];
        }

        if ($wo->status === 'R') {
            $steps[] = [
                'type' => 'footer',
                'title' => 'Rejected',
                'status' => 'R',
                'status_label' => 'Rejected',
                'by' => $getName($wo->completed_by),
                'at' => optional($wo->completed_at)->format('Y-m-d H:i'),
            ];
        }

        if ($wo->status === 'D') {
            $steps[] = [
                'type' => 'footer',
                'title' => 'Revised',
                'status' => 'D',
                'status_label' => 'Revised',
                'by' => $getName($wo->completed_by),
                'at' => optional($wo->completed_at)->format('Y-m-d H:i'),
            ];
        }

        return response()->json([
            'doc' => $wo->woid,
            'steps' => array_values($steps),
        ]);
    }

    public function printWo(Request $request, $hash)
    {
        $id = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        if (!\Auth::check()) {
            return redirect()->route('login');
        }

        $wo = TrWO::with([
            'worktype',      // MsWorktype
            'subworktype',   // MsSubworktype
            'location',      // MsLocation
            'sublocation',   // MsSubLocation
            'creator:username,name',
        ])->findOrFail($id);

        // $approval = TrApproval::query()
        //     ->where('refnbr', $wo->woid)          // dulu: docid
        //     ->where('status', '<>', 'X')
        //     ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
        //     ->orderBy('created_at', 'ASC')            // tie-breaker kalau leveling sama
        //     ->get();
        $refnbr = $wo->woid;
        $apprTable = (new TrApproval())->getTable(); // "tr_approval"

        $approval = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('status', '<>', 'X')
            ->reorder()
            ->orderBy('created_at', 'asc')
            ->orderBy('aprv_leveling', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'aprv_leveling',
                'aprv_name',
                'aprv_datebefore',
                'aprv_dateafter',
                'status',
                'aprv_type',
                'aprv_condition',
            ]);

        $approve_count = $approval->count();

        $company = MsCompany::where('cpny_id', $wo->cpny_id)->first();

        // mapping status
        $status_map = [
            'R' => 'Rejected',
            'C' => 'Completed',
            'D' => 'Hold',
            'X' => 'Cancel',
            'P' => 'On Progress',
        ];
        $status_doc = $status_map[$wo->status] ?? 'On Progress';

        // pilih varian tampilan
        $variant = $request->query('variant', 'default'); // default | tenant
        $view = $variant === 'tenant'
            ? 'pages.wos.pdf_wos_tenant'
            : 'pages.wos.pdf_wos';

        $data = [
            'title' => $variant === 'tenant' ? 'Work Order (Tenant)' : 'Work Order (WO)',
            'doc_type' => 'WO',
            'docid' => $wo->woid,
            'department_id' => $wo->department_id,
            'cpnyname' => optional($company)->cpny_name,
            'cpnyid' => $wo->cpny_id,
            'created_by_username' => $wo->created_by,
            'created_by_name' => ucwords(strtolower(optional($wo->creator)->name)),
            'created_at_fmt' => optional($wo->created_at)->format('d F Y'),
            'req_date_fmt' => optional($wo->created_at)->format('d M Y H:i'),
            'wodate' => \Carbon\Carbon::parse($wo->wodate)->format('d F Y'),
            'keperluan' => $wo->keperluan,
            'status_doc' => $status_doc,
            'budget_use' => $wo->budget_use,
            // info tambahan yang sering dipakai di template
            'wotype' => $wo->wotype,                      // disimpan string category_name
            'worequest' => $wo->worequest,                   // disimpan string category_name
            'worktype_name' => optional($wo->worktype)->worktype_name,
            'subworktype_name' => optional($wo->subworktype)->subworktype_name,
            'location_name' => optional($wo->location)->location_name,
            'sub_location_name' => optional($wo->sublocation)->sub_location_name,
            'picrequester' => $wo->picrequester,
            'biaya_wo' => number_format($wo->biaya_wo, 0, ',', '.'),
        ];

        $pdf = \PDF::loadView($view, array_merge($data, [
            'approval' => $approval,
            'approve_count' => $approve_count,
        ]));

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        $suffix = $variant === 'tenant' ? '_tenant' : '';

        return $pdf->stream("pdf_wos{$suffix}_{$wo->woid}.pdf");
    }

    public function woJobs()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 📌 Company bisa multi (cpny1,cpny2,...)
        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        // 📌 Department juga bisa multi (IT,HRD,...)
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }
        // dd($deptIds);
        // Kalau salah satu kosong → tidak ada data
        if (empty($cpnyIds) || empty($deptIds)) {
            $all = $onProgress = $cancel = $completed = $wojobs = 0;

            return view('pages.wos.wojobs', compact('all', 'onProgress', 'cancel', 'wojobs', 'completed'));
        }

        $base = TrWO::from('tr_wo as wo')
            ->join('ms_worktype_dept as wtd', function ($j) {
                $j->on('wtd.worktypeid', '=', 'wo.worktypeid');
            })
            ->whereIn('wo.cpny_id', $cpnyIds)          // 🔥 filter company
            ->whereIn('wtd.department_id', $deptIds)   // 🔥 filter department
            ->where('wo.status', 'C');                 // dokumen closed saja

        // Hitung pakai DISTINCT woid
        $all = (clone $base)->selectRaw('COUNT(DISTINCT wo.woid) AS c')->value('c');
        $onProgress = (clone $base)->where('wo.status_pekerjaan', 'P')->selectRaw('COUNT(DISTINCT wo.woid) AS c')->value('c');
        $cancel = (clone $base)->where('wo.status_pekerjaan', 'X')->selectRaw('COUNT(DISTINCT wo.woid) AS c')->value('c');
        $completed = (clone $base)->where('wo.status_pekerjaan', 'C')->selectRaw('COUNT(DISTINCT wo.woid) AS c')->value('c');
        $wojobs = (clone $base)->where('wo.status_pekerjaan', 'H')->selectRaw('COUNT(DISTINCT wo.woid) AS c')->value('c');

        return view('pages.wos.wojobs', compact('all', 'onProgress', 'cancel', 'wojobs', 'completed'));
    }

    public function jsonJobs(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        // Company multi
        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        // Department multi
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        if (empty($cpnyIds) || empty($deptIds)) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $jobStatus = (string) $request->query('job_status', '');
        $businessUnit = (string) $request->query('business_unit', '');

        $columns = [
            0 => 'wo.woid',
            1 => 'wo.wodate',
            2 => 'wo.cpny_id',
            3 => 'wo.department_id',
            4 => 'wt.worktype_name',
            5 => 'wo.worequest',
            6 => 'wo.keperluan',
            7 => 'wo.status_pekerjaan',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'wo.woid';

        $base = TrWO::from('tr_wo as wo')

            ->leftJoin('ms_worktype as wt', function ($j) {
                $j->on('wt.worktypeid', '=', 'wo.worktypeid');
            })

            ->join('ms_worktype_dept as wtd', function ($j) {
                $j->on('wtd.worktypeid', '=', 'wo.worktypeid');
            })

            // LOCATION
            ->leftJoin('ms_location as loc', function ($j) {
                $j->on('loc.location_id', '=', 'wo.location_id');
            })

            // SUB LOCATION
            ->leftJoin('ms_sub_location as subloc', function ($j) {
                $j->on('subloc.sub_location_id', '=', 'wo.sub_location_id');
            })

            ->whereIn('wo.cpny_id', $cpnyIds)
            ->whereIn('wtd.department_id', $deptIds);

        // Filter job status
        if ($jobStatus !== '') {
            $base->where('wo.status_pekerjaan', $jobStatus);
        }

        if ($businessUnit !== '') {
            $base->where('wo.budget_business_unit_id', $businessUnit);
        }

        $recordsTotal = (clone $base)->distinct()->count('wo.woid');

        // Search
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('wo.woid', 'ilike', "%{$search}%")
                  ->orWhere('wo.cpny_id', 'ilike', "%{$search}%")
                  ->orWhere('wo.department_id', 'ilike', "%{$search}%")
                  ->orWhere('wt.worktype_name', 'ilike', "%{$search}%")
                  ->orWhere('wo.worequest', 'ilike', "%{$search}%")
                  ->orWhere('wo.keperluan', 'ilike', "%{$search}%")
                  ->orWhere('wo.status_pekerjaan', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->distinct()->count('wo.woid');

        $data = $base->select(
            'wo.id',
            'wo.woid',
            'wo.wodate',
            'wo.cpny_id',
            'wo.department_id',

            'wo.pic_wo',

            'wt.worktype_name',
            'wo.worequest',
            'wo.keperluan',

            'wo.budget_business_unit_id',

            'loc.location_name',

            // FIXED COLUMN NAME
            'subloc.sub_location_name as sublocation_name',

            'wo.status',
            'wo.status_pekerjaan',
            'wo.created_by'
        )
            ->orderBy($orderCol, $orderDir)
            ->orderBy('wo.woid', 'desc')
            ->distinct('wo.woid')
            ->skip($start)
            ->take($length)
            ->get();

        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            unset($row->id);

            return $row;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function businessUnits()
    {
        $user = Auth::user();

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $data = TrWO::from('tr_wo as wo')
            ->join('ms_worktype_dept as wtd', function ($j) {
                $j->on('wtd.worktypeid', '=', 'wo.worktypeid');
            })
            ->whereIn('wo.cpny_id', $cpnyIds)
            ->whereIn('wtd.department_id', $deptIds)
            ->whereNotNull('wo.budget_business_unit_id')
            ->distinct()
            ->pluck('wo.budget_business_unit_id');

        return response()->json($data);
    }

    // POST /wo/{woid}/process
    public function processWo($woid)
    {
        $user = auth()->user();

        $wo = TrWO::where('woid', $woid)->firstOrFail();

        if ($wo->pic_wo) {
            return response()->json([
                'success' => false,
                'message' => 'WO already processed.',
            ], 400);
        }

        $wo->pic_wo = $user->username;

        // REMOVE this if column does not exist
        // $wo->pic_department = $user->department_id ?? null;

        $wo->status_pekerjaan = 'P';

        $wo->save();

        return response()->json([
            'success' => true,
            'pic_wo' => $wo->pic_wo,
            'status_pekerjaan' => $wo->status_pekerjaan,
        ]);
    }

    // POST /wo/{woid}/job-status
    public function updateJobStatus(Request $req, $woid)
    {
        $req->validate([
            'status_pekerjaan' => 'required|in:P,X,C',
            'pic_wo_comment' => 'nullable|string',
            'pic_department' => 'nullable|string',
            'flag_sppbjkt' => 'nullable',
            'attachment' => 'nullable|file|max:10240', // 10MB
        ]);

        $wo = TrWO::where('woid', $woid)->firstOrFail();

        // =========================
        // UPDATE JOB STATUS
        // =========================
        $wo->status_pekerjaan = $req->status_pekerjaan;
        $wo->pic_wo_comment = $req->pic_wo_comment;
        $wo->pic_department = $req->pic_department;

        // =========================
        // COMPLETED TIMESTAMP
        // =========================
        if ($req->status_pekerjaan === 'C') {
            $wo->pic_completed_wo = now();
        }

        // =========================
        // FLAG NORMALIZATION
        // =========================
        $flag = filter_var($req->input('flag_sppbjkt'), FILTER_VALIDATE_BOOLEAN)
                || $req->input('flag_sppbjkt') == 1;

        $wo->flag_sppbjkt = $req->has('flag_sppbjkt') && $req->flag_sppbjkt == 1 ? 'Y' : 'N';

        $wo->save();

        // =========================
        // ATTACHMENT UPLOAD
        // =========================
        if ($req->hasFile('attachment')) {
            $meta = [
                'refnbr' => $wo->woid,
                'doctype' => 'WO',
                'cpnyid' => $wo->cpny_id,
                'departementid' => $wo->department_id,
                'base_folder' => 'att-purchasing-app/wo-job',
                'created_by' => auth()->user()->username,
            ];

            $files = [$req->file('attachment')];

            try {
                app(TrAttachmentController::class)->uploadInternal($meta, $files);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attachment upload failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Job status updated.',
            'data' => [
                'status_pekerjaan' => $wo->status_pekerjaan,
                'pic_department' => $wo->pic_department,
                'pic_wo_comment' => $wo->pic_wo_comment,
                'pic_completed_wo' => $wo->pic_completed_wo,
                'flag_sppbjkt' => $wo->flag_sppbjkt,
            ],
        ]);
    }
}
