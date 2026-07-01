<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\Autonbr;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\TrApproval;
use App\Models\TrMessage;
use App\Models\TrVoucherTaxi;
use App\Models\User;
use App\Models\MsCategory;
use App\Models\Usercpny;
use App\Models\Userdept;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Vinkla\Hashids\Facades\Hashids;
class VoucherTaxiController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 🔹 Normalize company & department
        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        // 🔥 CORRECT ROLE CHECK (same as json)
        $isGA = $user->hasRole('GAACCESS');

        // 🔹 Base query
        $q = TrVoucherTaxi::query();

        // 🔥 APPLY FILTER ONLY IF NOT GA
        if (!$isGA) {
            if (!empty($cpnyIds)) {
                $q->whereIn(DB::raw('TRIM(cpny_id)'), $cpnyIds);
            }

            if (!empty($deptIds)) {
                $q->whereIn(DB::raw('TRIM(department_id)'), $deptIds);
            }
        }

        // 🔹 Counts
        $all = (clone $q)->count();
        $onProgress = (clone $q)->where('status', 'P')->count();
        $reject = (clone $q)->where('status', 'R')->count();
        $revise = (clone $q)->where('status', 'D')->count();
        $completed = (clone $q)->where('status', 'C')->count();

        // 🔹 Other data (unchanged)
        $usercpny = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();

        $userdept = Userdept::where('ms_user_dept.username', $user->username)
            ->leftJoin('ms_department', 'ms_user_dept.department_id', '=', 'ms_department.department_id')
            ->select('ms_user_dept.department_id', 'ms_department.department_name')
            ->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $company = MsCompany::where('status', 'A')
            ->select('cpny_id', 'cpny_name')
            ->get();

         $departments = MsDepartment::query()
            ->where('status', 'A')
            ->orderBy('department_id')
            ->get();

        $requesters = User::query()
            ->whereNotNull('username')
            ->where('status', 'A')
            ->select('username', 'name', 'department_id')
            ->orderBy('name')
            ->get();

        $purposes = MsCategory::query()
            ->where('doctype', 'VCR')
            ->where('groups', 'PURPOSE')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->select('categoryid', 'category_name')
            ->get();

        return view('pages.vouchertaxi.vouchertaxi', compact(
            'all',
            'onProgress',
            'reject',
            'revise',
            'completed',
            'usercpny',
            'usercpny2',
            'userdept',
            'userdept2',
            'requesters',
            'company',
            'departments',
            'purposes'
        ));
    }

    public function json(Request $request)
    {
        $draw = (int) $request->input('draw');
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $search = trim($request->input('search.value'));

        $statusFilter = $request->input('status');
        $companyFilter = $request->input('company');
        $departmentFilter = $request->input('department');

        $columns = [
            0 => 'vt.docid',
            1 => 'vt.voucher_date',
            2 => 'vt.date_used',
            3 => 'vt.cpny_id',
            4 => 'vt.department_id',
            5 => 'vt.user_peminta',
            6 => 'vt.origin',
            7 => 'vt.destination',
            8 => 'vt.purpose_descr',
            9 => 'vt.status',
        ];

        $orderIdx = (int) data_get($request->input('order'), '0.column', 0);
        $orderDir = strtolower(data_get($request->input('order'), '0.dir', 'desc'));

        $orderDir = in_array($orderDir, ['asc', 'desc'])
            ? $orderDir
            : 'desc';

        $orderCol = $columns[$orderIdx] ?? 'vt.docid';

        $authUser = Auth::user();

        if (!$authUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $isGA = $authUser->hasRole('GAACCESS');

        $cpnyIds = is_string($authUser->cpny_id)
            ? array_filter(array_map('trim', explode(',', $authUser->cpny_id)))
            : (array) $authUser->cpny_id;

        $deptIds = is_string($authUser->department_id)
            ? array_filter(array_map('trim', explode(',', $authUser->department_id)))
            : (array) $authUser->department_id;

        $username = strtolower(trim($authUser->username));

        $query = TrVoucherTaxi::from('tr_voucher_taxi as vt')
            ->select([
                'vt.id',
                'vt.docid',
                'vt.voucher_date',
                'vt.date_used',
                'vt.cpny_id',
                'vt.department_id',
                'vt.user_peminta',
                'vt.origin',
                'vt.destination',
                'vt.purpose_id',
                'vt.purpose_descr as purpose',
                'vt.type_trip',
                'vt.max_budget',
                'vt.actual_budget',
                'vt.status',
                'vt.created_by',
                'vt.created_at',
            ]);

        if ($isGA) {
            // GA user: sees approval-chain vouchers OR own company/department (BOTH views)
            $approvalDocids = TrApproval::where('status', '!=', 'X')
                ->whereRaw(
                    "LOWER(aprv_username) ~ ?",
                    ['(^|,)\s*' . preg_quote($username, '/') . '\s*(,|$)']
                )
                ->pluck('refnbr')
                ->unique()
                ->values()
                ->toArray();

            $query->where(function ($q) use ($approvalDocids, $cpnyIds, $deptIds) {
                $q->whereIn('vt.docid', $approvalDocids);

                if (!empty($cpnyIds) || !empty($deptIds)) {
                    $q->orWhere(function ($sub) use ($cpnyIds, $deptIds) {
                        if (!empty($cpnyIds)) {
                            $sub->whereIn(DB::raw('TRIM(vt.cpny_id)'), $cpnyIds);
                        }
                        if (!empty($deptIds)) {
                            $sub->whereIn(DB::raw('TRIM(vt.department_id)'), $deptIds);
                        }
                    });
                }
            });
        } else {
            // Regular user: sees all vouchers in their company AND department
            if (!empty($cpnyIds)) {
                $query->whereIn(DB::raw('TRIM(vt.cpny_id)'), $cpnyIds);
            }

            if (!empty($deptIds)) {
                $query->whereIn(DB::raw('TRIM(vt.department_id)'), $deptIds);
            }
        }

        if (!empty($statusFilter)) {
            $query->where('vt.status', $statusFilter);
        }

        if (!empty($companyFilter)) {
            $query->where('vt.cpny_id', $companyFilter);
        }

        if (!empty($departmentFilter)) {
            $query->where('vt.department_id', $departmentFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('vt.docid', 'ILIKE', "%{$search}%")
                    ->orWhere('vt.cpny_id', 'ILIKE', "%{$search}%")
                    ->orWhere('vt.department_id', 'ILIKE', "%{$search}%")
                    ->orWhere('vt.user_peminta', 'ILIKE', "%{$search}%")
                    ->orWhere('vt.origin', 'ILIKE', "%{$search}%")
                    ->orWhere('vt.destination', 'ILIKE', "%{$search}%")
                    ->orWhere('vt.purpose_descr', 'ILIKE', "%{$search}%");
            });
        }

        $recordsTotal = TrVoucherTaxi::count();

        $recordsFiltered = (clone $query)->count();

        $rows = $query
            ->orderBy($orderCol, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $rows->transform(function ($row) {

            $row->eid = Hashids::encode($row->id);

            return $row;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    public function calendarJson(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $isGA     = $user->hasRole('GAACCESS');
        $username = strtolower(trim($user->username));

        $base = TrVoucherTaxi::query()
            ->whereIn('status', ['P', 'C', 'F', 'D'])
            ->select([
                'id', 'docid', 'date_used',
                'origin', 'destination',
                'purpose_descr', 'status',
                'cpny_id', 'department_id', 'created_by',
            ]);

        if ($isGA) {
            $docids = TrApproval::where('status', '!=', 'X')
                ->whereRaw(
                    "LOWER(aprv_username) ~ ?",
                    ['(^|,)\s*' . preg_quote($username, '/') . '\s*(,|$)']
                )
                ->pluck('refnbr')
                ->unique()
                ->values()
                ->toArray();

            $base->where(function ($q) use ($docids, $username) {
                $q->whereIn('docid', $docids)
                  ->orWhereRaw('LOWER(TRIM(created_by)) = ?', [$username]);
            });
        } else {
            $base->whereRaw('LOWER(TRIM(created_by)) = ?', [$username]);
        }

        $data = $base->orderBy('date_used', 'desc')->get();

        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            $row->route_summary = $row->origin . ' → ' . $row->destination;
            unset($row->id);
            return $row;
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function purposeSearch(Request $request)
    {
        try {

            $search = trim($request->q ?? '');

            $query = MsCategory::query()
                ->where('doctype', 'VCR')
                ->where('groups', 'PURPOSE')
                ->where('status', 'A');

            if ($search !== '') {

                $query->where(function ($q) use ($search) {

                    $q->where('categoryid', 'ILIKE', "%{$search}%")
                        ->orWhere('category_name', 'ILIKE', "%{$search}%");
                });
            }

            $data = $query
                ->orderBy('category_name')
                ->limit(20)
                ->get([
                    'categoryid',
                    'category_name'
                ])
                ->map(function ($item) {

                    return [
                        'id' => $item->category_name,
                        'text' => $item->category_name,
                        'categoryid' => $item->categoryid,
                        'category_name' => $item->category_name,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function employeeByDepartment(Request $request)
    {
        $dept = $request->department_id;

        $query = User::query()
            ->where('ms_user.status', 'A')
            ->whereIn('ms_user.username', function ($q) use ($dept) {
                $q->select('username')
                    ->from('ms_user_dept')
                    ->where('department_id', $dept);
            })
            ->select('ms_user.username', 'ms_user.name');

        if ($request->filled('cpny_id')) {
            $cpny = $request->cpny_id;
            $query->whereIn('ms_user.username', function ($q) use ($cpny) {
                $q->select('username')
                    ->from('ms_user_cpny')
                    ->where('cpny_id', $cpny);
            });
        }

        $employees = $query->orderBy('ms_user.name')->get();

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }
    public function storeVoucher(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'cpny_id' => ['required'],
            'department_id' => ['required'],
            'user_peminta' => ['required'],

            'date_used' => ['required', 'date'],
            'type_trip' => ['required'],

            'purpose_id' => ['required'],
            'purpose_descr' => ['required', 'string'],

            'user_topup' => ['required'],

            'origin' => ['required'],
            'destination' => ['required'],
        ]);

        if (!$user->hasRole('GAACCESS')) {
            $userCpnyIds = is_string($user->cpny_id)
                ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
                : (array) $user->cpny_id;

            $userDeptIds = is_string($user->department_id)
                ? array_filter(array_map('trim', explode(',', $user->department_id)))
                : (array) $user->department_id;

            if (!in_array($validated['cpny_id'], $userCpnyIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to the selected company.',
                ], 403);
            }

            if (!in_array($validated['department_id'], $userDeptIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to the selected department.',
                ], 403);
            }
        }

        DB::connection('pgsql5')->beginTransaction();

        try {

            $dt = now();

            $year = (int) $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

            $doctype = 'VCR';
            $username = $user->username;

            $cpny_id = $validated['cpny_id'];
            $department_id = $validated['department_id'];

            $approvalCtl = app(ApprovalController::class);

            $approvalCtl->loadLines(
                $doctype,
                $cpny_id,
                $department_id
            );

            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'Voucher Taxi'
            );

            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2) . $month;

            $docid = $doctype . $tglbln . sprintf('%03d', $urutan);

            $voucher = TrVoucherTaxi::create([
                'docid' => $docid,
                'voucher_date' => $dt->toDateString(),

                'cpny_id' => $validated['cpny_id'],
                'department_id' => $validated['department_id'],
                'user_peminta' => $validated['user_peminta'],

                'cpny_id_expense' => $validated['cpny_id'],
                'department_id_expense' => $validated['department_id'],
                'user_peminta_expense' => $validated['user_peminta'],

                'origin' => $validated['origin'],
                'destination' => $validated['destination'],

                'purpose_id' => $validated['purpose_id'],
                'purpose_descr' => $validated['purpose_descr'],

                'date_used' => $validated['date_used'],

                'type_trip' => $validated['type_trip'],
                'user_topup' => $validated['user_topup'],

                'status' => 'P',

                'created_by' => $username,
                'created_at' => $dt,
                'updated_by' => $username,
                'updated_at' => $dt,
            ]);

            $ctx = [
                'ignore_nominal'      => true,
                'approval_condition'  => $validated['cpny_id_expense'] ?? $validated['cpny_id'],
                'approval_conditions' => [
                    $validated['cpny_id_expense'] ?? $validated['cpny_id']
                ],
            ];

            [$firstApprovalUsernames, $linesCount] =
                $approvalCtl->generateForDocument(
                    $docid,
                    $doctype,
                    $cpny_id,
                    $department_id,
                    $username,
                    $ctx,
                    $dt
                );

            $eid = Hashids::encode($voucher->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $voucher->status,
                'Voucher Taxi',
                url('/showvouchertaxi/' . $eid),
                [
                    'info' => $voucher->purpose_descr,
                    'createdby' => $voucher->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher Taxi berhasil dibuat',
                'data' => [
                    'docid' => $voucher->docid,
                ],
            ]);
        } catch (\Throwable $e) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateVoucherTaxi(Request $request, $docid)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'cpny_id' => ['required'],
            'department_id' => ['required'],
            'user_peminta' => ['required'],

            'date_used' => ['required', 'date'],
            'type_trip' => ['required'],

            'purpose_id' => ['required'],
            'purpose_descr' => ['required', 'string'],

            'user_topup' => ['required'],

            'origin' => ['required'],
            'destination' => ['required'],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            $voucher = TrVoucherTaxi::where('docid', $docid)->firstOrFail();

            if ($voucher->status !== 'D') {
                throw new \Exception('Voucher Taxi hanya bisa diedit saat status Revise / Draft.');
            }

            if ($voucher->created_by !== $user->username) {
                throw new \Exception('Anda tidak berhak edit Voucher Taxi ini.');
            }


            $doctype = 'VCR';
            $dt = now();
            $username = $user->username;

            $cpny_id = $validated['cpny_id'];
            $department_id = $validated['department_id'];

            $approvalCtl = app(ApprovalController::class);

            $approvalCtl->loadLines(
                $doctype,
                $cpny_id,
                $department_id
            );

            $voucher->cpny_id = $validated['cpny_id'];
            $voucher->department_id = $validated['department_id'];
            $voucher->user_peminta = $validated['user_peminta'];

            $voucher->cpny_id_expense = $validated['cpny_id'];
            $voucher->department_id_expense = $validated['department_id'];
            $voucher->user_peminta_expense = $validated['user_peminta'];

            $voucher->origin = $validated['origin'];
            $voucher->destination = $validated['destination'];

            $voucher->purpose_id = $validated['purpose_id'];
            $voucher->purpose_descr = $validated['purpose_descr'];

            $voucher->date_used = $validated['date_used'];

            $voucher->type_trip = $validated['type_trip'];
            $voucher->user_topup = $validated['user_topup'];

            $voucher->status = 'P';

            $voucher->updated_by = $username;
            $voucher->updated_at = $dt;

            $voucher->save();

            $ctx = [
                'ignore_nominal'      => true,
                'approval_condition'  => $validated['cpny_id_expense'] ?? $validated['cpny_id'],
                'approval_conditions' => [
                    $validated['cpny_id_expense'] ?? $validated['cpny_id']
                ],
            ];

            [$firstApprovalUsernames, $linesCount] =
                $approvalCtl->generateForDocument(
                    $voucher->docid,
                    $doctype,
                    $cpny_id,
                    $department_id,
                    $username,
                    $ctx,
                    $dt
                );

            if ($firstApprovalUsernames) {

                $voucher->completed_by = is_array($firstApprovalUsernames)
                    ? implode(',', $firstApprovalUsernames)
                    : $firstApprovalUsernames;

                $voucher->completed_at = $dt;
                $voucher->save();
            }

            $eid = Hashids::encode($voucher->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $voucher->status,
                'Voucher Taxi',
                url('/showvouchertaxi/' . $eid),
                [
                    'info' => $voucher->purpose_descr,
                    'createdby' => $voucher->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher Taxi berhasil diupdate dan dikirim ulang approval.',
                'data' => [
                    'docid' => $voucher->docid,
                ],
            ]);
        } catch (\Throwable $e) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Request $request, $docid)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        DB::beginTransaction();

        try {
            $voucher = TrVoucherTaxi::where('docid', $docid)
                ->firstOrFail();

            // 🔥 only creator
            if (
                strtolower(trim($voucher->created_by)) !==
                strtolower(trim($user->username))
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot cancel this request',
                ], 403);
            }

            // 🔥 only revise
            if ($voucher->status !== 'D') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only revise document can be cancelled',
                ], 400);
            }

            // 🔥 cancel header
            $voucher->status = 'X';
            $voucher->updated_by = $user->username;
            $voucher->updated_at = now();
            $voucher->completed_by = $user->username;
            $voucher->completed_at = now();
            $voucher->save();

            // 🔥 cancel remaining approvals
            TrApproval::where('refnbr', $voucher->docid)
                ->where('status', 'P')
                ->update([
                    'status' => 'X',
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher request cancelled successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function detail(string $eid)
    {
        try {

            $id = Hashids::decode($eid)[0] ?? null;

            $voucher = TrVoucherTaxi::findOrFail($id);

            $user = auth()->user();

            $purposeCategory = MsCategory::where('categoryid', $voucher->purpose_id)->first();
            $purposeName = $purposeCategory?->category_name ?? $voucher->purpose_id;

            $canEdit = false;
            $canCancel = false;
            $canApprove = false;
            $canReject = false;
            $canRevise = false;
            $canProcess = false;

            if (
                $voucher->status === 'D' &&
                $voucher->created_by === $user->username
            ) {

                $canEdit = true;
                $canCancel = true;
            }

            $currentApproval = TrApproval::query()
                ->where('refnbr', $voucher->docid)
                ->where('status', 'P')
                ->orderByRaw('CAST(aprv_leveling AS INTEGER)')
                ->first();

            if ($currentApproval) {

                $approvers = collect(
                    explode(
                        ',',
                        $currentApproval->aprv_username ?? ''
                    )
                )
                    ->map(fn($item) => trim($item))
                    ->filter()
                    ->values();

                if (
                    $approvers->contains(
                        $user->username
                    )
                ) {

                    $canApprove = true;
                    $canReject = true;
                    $canRevise = true;
                }
            }

            if (
                $voucher->status === 'C' &&
                $user->hasRole('GAACCESS')
            ) {
                $canProcess = true;
            }

            return response()->json([
                'success' => true,
                'data' => [

                    'eid' => $eid,
                    'id' => $voucher->id,

                    'docid' => $voucher->docid,
                    'status' => $voucher->status,

                    'cpny_id' => $voucher->cpny_id,
                    'department_id' => $voucher->department_id,
                    'location_id' => $voucher->location_id,

                    'user_peminta' => $voucher->user_peminta,
                    'user_name' => optional(
                        User::where(
                            'username',
                            $voucher->user_peminta
                        )->first()
                    )->name,

                    'origin' => $voucher->origin,
                    'destination' => $voucher->destination,

                    'date_used' => $voucher->date_used,

                    'type_trip' => $voucher->type_trip,
                    'max_trip' => $voucher->max_trip,

                    'purpose_id' => $voucher->purpose_id,
                    'purpose_name' => $purposeName,
                    'purpose_descr' => $voucher->purpose_descr,

                    'cpny_id_expense'           => $voucher->cpny_id_expense,
                    'department_id_expense'     => $voucher->department_id_expense,
                    'user_peminta_expense'      => $voucher->user_peminta_expense,
                    'user_peminta_expense_name' => optional(
                        User::where('username', $voucher->user_peminta_expense)->first()
                    )->name ?? $voucher->user_peminta_expense,

                    'user_topup'      => $voucher->user_topup,
                    'user_topup_name' => optional(
                        User::where('username', $voucher->user_topup)->first()
                    )->name ?? $voucher->user_topup,

                    'max_budget' => $voucher->max_budget,
                    'actual_budget' => $voucher->actual_budget,

                    'checked_by' => $voucher->checked_by,
                    'checked_at' => $voucher->checked_at,

                    'created_by' => $voucher->created_by,
                    'created_at' => optional(
                        $voucher->created_at
                    )->format('d M Y H:i'),

                    'updated_by' => $voucher->updated_by,
                    'updated_at' => optional(
                        $voucher->updated_at
                    )->format('d M Y H:i'),

                    'completed_by' => $voucher->completed_by,
                    'completed_at' => optional(
                        $voucher->completed_at
                    )->format('d M Y H:i'),

                    'revise_reason' =>
                    $voucher->revise_reason ?? null,

                    'can_edit' => $canEdit,
                    'can_cancel' => $canCancel,

                    'can_approve' => $canApprove,
                    'can_reject' => $canReject,
                    'can_revise' => $canRevise,

                    'can_process' => $canProcess,
                ]
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateGaAdvice(Request $request, $docid)
    {
        $rawBudget = $request->actual_budget;
        // Strip thousand-separator dots then normalise decimal comma → dot
        $rawBudget = str_replace('.', '', (string) $rawBudget);
        $rawBudget = str_replace(',', '.', $rawBudget);

        $request->merge(['actual_budget' => $rawBudget]);

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!$user->hasRole('GAACCESS')) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        $validated = $request->validate([
            'actual_budget' => ['required', 'numeric', 'min:0'],

            'change_expense_owner' => ['nullable'],

            'cpny_id_expense' => ['nullable'],
            'department_id_expense' => ['nullable'],
            'user_peminta_expense' => ['nullable'],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {

            $voucher = TrVoucherTaxi::query()
                ->where('docid', $docid)
                ->firstOrFail();

            if (!in_array($voucher->status, ['C', 'F'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher must be approved (C) before processing.',
                ], 400);
            }

            if ($request->boolean('change_expense_owner')) {

                $request->validate([
                    'cpny_id_expense' => ['required'],
                    'department_id_expense' => ['required'],
                    'user_peminta_expense' => ['required'],
                ]);

                $voucher->cpny_id_expense = $request->cpny_id_expense;
                $voucher->department_id_expense = $request->department_id_expense;
                $voucher->user_peminta_expense = $request->user_peminta_expense;
            }

            $voucher->actual_budget = $validated['actual_budget'];

            // Set status to F (Processed) — same as Booking Car
            $voucher->status = 'F';

            $voucher->checked_by = $user->username;
            $voucher->checked_at = now();

            $voucher->updated_by = $user->username;
            $voucher->updated_at = now();

            $voucher->save();

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher Taxi processed successfully.',
            ]);
        } catch (\Throwable $e) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function approveVoucherTaxi(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'VCR';

        $voucher = TrVoucherTaxi::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher Taxi not found',
            ], 404);
        }

        $eid = Hashids::encode($voucher->id);
        $docUrl = url('/showvouchertaxi/' . $eid);
        $fullname = data_get($voucher, 'creator.name') ?: $voucher->created_by;

        $result = app(ApprovalController::class)->approveStep(
            $voucher->docid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($voucher, $fullname, $docUrl) {
                $voucher->status = 'C';
                $voucher->completed_by = $voucher->completed_by ?: auth()->user()->username;
                $voucher->completed_at = $now;
                $voucher->updated_by = auth()->user()->username;
                $voucher->updated_at = $now;
                $voucher->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $voucher->docid,
                    'Voucher Taxi',
                    'C',
                    $voucher->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $voucher->cpny_id ?? '',
                        'deptname' => $voucher->department_id ?? '',
                        'date' => $voucher->voucher_date,
                        'info' => $voucher->purpose_descr,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($voucher, $docUrl) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $voucher->docid,
                    'VCR',
                    'P',
                    'Voucher Taxi',
                    $docUrl,
                    [
                        'info' => $voucher->purpose_descr,
                        'createdby' => $voucher->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses
                $voucher->completed_by = auth()->user()->username;
                $voucher->completed_at = $now;
                $voucher->updated_by = auth()->user()->username;
                $voucher->updated_at = $now;
                $voucher->save();
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Approve failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher Taxi approved successfully',
        ]);
    }

    public function rejectVoucherTaxi(Request $request, $docid)
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $user = $request->user();
        $doctype = 'VCR';

        $voucher = TrVoucherTaxi::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher Taxi not found',
            ], 404);
        }

        $eid = Hashids::encode($voucher->id);
        $docUrl = url('/showvouchertaxi/' . $eid);
        $fullname = data_get($voucher, 'creator.name') ?: $voucher->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $voucher->docid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($voucher, $fullname, $docUrl, $doctype, $request) {
                $voucher->status = 'R';
                $voucher->completed_by = auth()->user()->username;
                $voucher->completed_at = $now;
                $voucher->updated_by = auth()->user()->username;
                $voucher->updated_at = $now;
                $voucher->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $voucher->docid,
                    'Voucher Taxi',
                    'R',
                    $voucher->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $voucher->cpny_id ?? '',
                        'deptname' => $voucher->department_id ?? '',
                        'date' => $now->toDateString(),
                        'info' => $voucher->purpose ?? '',
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // === Simpan reason ===
                app(\App\Http\Controllers\SendCommentController::class)
                    ->sendmsg($voucher->id, $doctype, $request);
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Reject failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher Taxi rejected successfully',
        ]);
    }

    public function reviseVoucherTaxi(Request $request, $docid)
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $user = $request->user();
        $doctype = 'VCR';

        $voucher = TrVoucherTaxi::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher Taxi not found',
            ], 404);
        }

        $eid = Hashids::encode($voucher->id);
        $docUrl = url('/showvouchertaxi/' . $eid);
        $fullname = data_get($voucher, 'creator.name') ?: $voucher->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $voucher->docid,          // refnbr
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($voucher, $fullname, $docUrl, $doctype, $request) {
                // === HEADER -> D (Revise) ===
                $voucher->status = 'D';
                $voucher->completed_by = auth()->user()->username;
                $voucher->completed_at = $now;
                $voucher->updated_by = auth()->user()->username;
                $voucher->updated_at = $now;
                $voucher->save();

                // === Email ke requester ===
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $voucher->docid,
                    'Voucher Taxi',
                    'D',
                    $voucher->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $voucher->cpny_id ?? '',
                        'deptname' => $voucher->department_id ?? '',
                        'date' => $now->toDateString(),
                        'info' => $voucher->purpose ?? '',
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // === Simpan reason ===
                app(\App\Http\Controllers\SendCommentController::class)
                    ->sendmsg($voucher->id, $doctype, $request);
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Revise failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher Taxi revised successfully',
        ]);
    }

    public function findByHash($eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;

        abort_if(!$id, 404);

        $voucher = TrVoucherTaxi::findOrFail($id);

        $purposeCategory = MsCategory::where('categoryid', $voucher->purpose_id)->first();
        $purposeName = $purposeCategory?->category_name ?? $voucher->purpose_id;

        return response()->json([
            'id' => $voucher->id,
            'docid' => $voucher->docid,
            'cpny_id' => $voucher->cpny_id,
            'department_id' => $voucher->department_id,
            'user_peminta' => $voucher->user_peminta,
            'origin' => $voucher->origin,
            'destination' => $voucher->destination,

            'purpose_id' => $voucher->purpose_id,
            'purpose_name' => $purposeName,

            'purpose_descr' => $voucher->purpose_descr,

            'date_used' => $voucher->date_used
                ? \Carbon\Carbon::parse($voucher->date_used)->format('Y-m-d')
                : null,

            'type_trip' => $voucher->type_trip,

            'cpny_id_expense' => $voucher->cpny_id_expense,
            'user_topup' => $voucher->user_topup,
        ]);
    }

    public function tracking($hash)
    {
        try {
            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);

            $voucher = TrVoucherTaxi::findOrFail($id);

            $getName = function (?string $username) {
                if (!$username) return null;
                return User::where('username', $username)->value('name') ?? $username;
            };

            // Resolve comma-separated usernames → display names
            $getApproverNames = function (?string $aprv_username) use ($getName) {
                if (!$aprv_username) return null;
                $names = array_filter(
                    array_map(
                        fn($u) => $getName(trim($u)),
                        explode(',', $aprv_username)
                    )
                );
                return implode(', ', $names) ?: $aprv_username;
            };

            $steps = [];

            // =========================
            // SUBMITTED
            // =========================

            $steps[] = [
                'key'    => 'submitted',
                'title'  => 'Submitted',
                'status' => 'C',
                'by'     => $getName($voucher->created_by),
                'at'     => optional($voucher->created_at)->format('d M Y H:i'),
            ];

            // =========================
            // APPROVALS
            // =========================

            $approvals = TrApproval::query()
                ->where('refnbr', $voucher->docid)
                ->where('status', '<>', 'X')
                // Processed records (non-null date) appear first, chronologically.
                // Pending records (null date) appear last, ordered by level.
                ->orderByRaw('CASE WHEN aprv_dateafter IS NULL THEN 1 ELSE 0 END ASC')
                ->orderBy('aprv_dateafter', 'asc')
                ->orderByRaw('CAST(aprv_leveling AS NUMERIC) ASC')
                ->get();

            $reason = null;

            try {
                $reason = DB::table('tr_reason')
                    ->where('refid', $voucher->id)
                    ->where('doctype', 'VCR')
                    ->latest('created_at')
                    ->value('reason');
            } catch (\Throwable $e) {
                // ignore if table not exists
            }

            $reasons = TrMessage::where('doctype', 'VCR')
                ->where('refnbr', $voucher->docid)
                ->orderByDesc('message_date')
                ->get([
                    'username',
                    'name',
                    'message',
                    'message_date',
                ]);

            foreach ($approvals as $aprv) {
                $steps[] = [
                    'key' => 'approval_' . $aprv->aprv_leveling,

                    'aprv_name' => $aprv->aprv_name,
                    'aprv_leveling' => $aprv->aprv_leveling,

                    'title'  => 'Approval Level ' . intval((float) $aprv->aprv_leveling),

                    'status' => $aprv->status,

                    'aprv_username' => $aprv->aprv_username,

                    'by' => $aprv->status === 'P'
                        ? $getApproverNames($aprv->aprv_username)
                        : $getName($aprv->updated_by ?: null) ?? $getApproverNames($aprv->aprv_username),

                    'at' => $aprv->aprv_dateafter
                        ? \Carbon\Carbon::parse($aprv->aprv_dateafter)->format('d M Y H:i')
                        : null,

                    'comment' => in_array($aprv->status, ['D', 'R'])
                        ? $reasons->first()?->message
                        : null,
                ];
            }

            $latestComment = TrMessage::where('doctype', 'VCR')
                ->where('refnbr', $voucher->docid)
                ->latest('message_date')
                ->first();

            return response()->json([
                'success' => true,

                'doc' => $voucher->docid,

                'steps' => $steps,

                'status' => $voucher->status,

                'status_label' => match ($voucher->status) {
                    'P' => 'Pending',
                    'C' => 'Completed',
                    'R' => 'Rejected',
                    'D' => 'Revise',
                    default => '-',
                },

                'revise_reason' => $latestComment?->message,
                'reasons' => $reasons,
            ]);
        } catch (\Throwable $e) {
            \Log::error('TRACKING ERROR', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function printVoucherTaxi($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $voucher = TrVoucherTaxi::with([
            'creator:username,name',
        ])->findOrFail($id);

        $approvals = TrApproval::query()
            ->where('refnbr', $voucher->docid)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS INTEGER)')
            ->get();

        $company = MsCompany::where('cpny_id', $voucher->cpny_id)
            ->first();

        $status_doc = match ($voucher->status) {
            'P' => 'On Progress',
            'C' => 'Completed',
            'R' => 'Rejected',
            'D' => 'Revise',
            'X' => 'Cancelled',
            default => '-',
        };

        $pdf = \PDF::loadView(
            'pages.vouchertaxi.pdf_vouchertaxi',
            [
                'voucher' => $voucher,
                'approvals' => $approvals,
                'company' => $company,
                'status_doc' => $status_doc,
            ]
        );

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream(
            'voucher_taxi_' . $voucher->docid . '.pdf'
        );
    }
}
