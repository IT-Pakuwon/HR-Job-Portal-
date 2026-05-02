<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsCompany;
use App\Models\TrApproval;
use App\Models\TrBookingCar;
use App\Models\TrMessage;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\TrBookingCarDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Vinkla\Hashids\Facades\Hashids;


class BookingCarController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // =========================================
        // NORMALIZE COMPANY & DEPARTMENT
        // =========================================

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        // =========================================
        // ROLE CHECK
        // =========================================

        $isGA = $user->hasRole('GAACCESS');

        // =========================================
        // BASE QUERY
        // =========================================

        $q = TrBookingCar::query();

        // =========================================
        // ROLE FILTER
        // =========================================

        if (!$isGA) {
            $q->where(function ($sub) use ($user, $cpnyIds, $deptIds) {
                // OWN DOCUMENT

                $sub->whereRaw(
                    'LOWER(TRIM(created_by)) = ?',
                    [strtolower(trim($user->username))]
                );

                // SAME COMPANY + SAME DEPARTMENT

                $sub->orWhere(function ($x) use ($cpnyIds, $deptIds) {
                    if (!empty($cpnyIds)) {
                        $x->whereIn(
                            DB::raw('TRIM(cpny_id)'),
                            $cpnyIds
                        );
                    }

                    if (!empty($deptIds)) {
                        $x->whereIn(
                            DB::raw('TRIM(department_id)'),
                            $deptIds
                        );
                    }
                });
            });
        }

        // =========================================
        // ONLY ACTIVE STATUS
        // =========================================

        $q->whereIn('status', ['P', 'C', 'D', 'R']);

        // =========================================
        // COUNTS
        // =========================================

        $all = (clone $q)->count();

        $onProgress = (clone $q)
            ->where('status', 'P')
            ->count();

        $reject = (clone $q)
            ->where('status', 'R')
            ->count();

        $revise = (clone $q)
            ->where('status', 'D')
            ->count();

        $completed = (clone $q)
            ->where('status', 'C')
            ->count();

        // =========================================
        // USER COMPANY
        // =========================================

        $usercpny = Usercpny::where(
            'username',
            $user->username
        )->get();

        $usercpny2 = Usercpny::where(
            'username',
            $user->username
        )->first();

        // =========================================
        // USER DEPARTMENT
        // =========================================

        $userdept = Userdept::where(
            'username',
            $user->username
        )->get();

        $userdept2 = Userdept::where(
            'username',
            $user->username
        )->first();

        // =========================================
        // COMPANY
        // =========================================

        $company = MsCompany::where('status', 'A')
            ->select(
                'cpny_id',
                'cpny_name'
            )
            ->orderBy('cpny_name')
            ->get();

        // =========================================
        // REQUESTERS
        // =========================================

        $requesters = User::query()
            ->whereNotNull('username')
            ->where('status', 'A')
            ->select(
                'username',
                'name',
                'department_id'
            )
            ->orderBy('name')
            ->get();

        // =========================================
        // DRIVER
        // =========================================

        $drivers = DB::connection('pgsql5')
            ->table('ms_driver_opr')
            ->where('status', 'A')
            ->orderBy('drivername')
            ->get();

        // =========================================
        // VEHICLE
        // =========================================

        $kendaraan = DB::connection('pgsql5')
            ->table('ms_kendaraan_opr')
            ->where('status', 'A')
            ->orderBy('nopol_kendaraan')
            ->get();

        // =========================================
        // VIEW
        // =========================================

        return view(
            'pages.bookingcar.bookingcar',
            compact(
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
                'drivers',
                'kendaraan'
            )
        );
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        // =========================================
        // NORMALIZE COMPANY & DEPARTMENT
        // =========================================

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        // =========================================
        // ROLE CHECK
        // =========================================

        $isGA = $user->hasRole('GAACCESS');

        // =========================================
        // DATATABLE PARAMS
        // =========================================

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);

        $search = trim((string) $request->input('search.value', ''));

        $status = trim((string) $request->query('status', ''));

        // =========================================
        // COLUMN MAP
        // =========================================

        $columns = [
            0 => 'bc.docid',
            1 => 'bc.booking_date',
            2 => 'bc.start_time',
            3 => 'bc.end_time',
            4 => 'bc.cpny_id',
            5 => 'bc.department_id',
            6 => 'bc.user_peminta',
            7 => 'bc.purpose_descr',
            8 => 'bc.driver',
            9 => 'bc.no_polisi',
            10 => 'bc.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);

        $orderDir = $request->input('order.0.dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $orderCol = $columns[$orderIdx] ?? 'bc.docid';

        // =========================================
        // BASE QUERY
        // =========================================

        $base = TrBookingCar::from('tr_booking_car as bc');

        // =========================================
        // STATUS FILTER
        // =========================================

        $base->whereIn('bc.status', ['P', 'C', 'D', 'R', 'X']);

        // =========================================
        // ROLE FILTER
        // =========================================

        if (!$isGA) {
            $base->where(function ($q) use ($user, $cpnyIds, $deptIds) {
                // OWN DOCUMENT

                $q->whereRaw(
                    'LOWER(TRIM(bc.created_by)) = ?',
                    [strtolower(trim($user->username))]
                );

                // SAME COMPANY + SAME DEPARTMENT

                $q->orWhere(function ($sub) use ($cpnyIds, $deptIds) {
                    if (!empty($cpnyIds)) {
                        $sub->whereIn(
                            DB::raw('TRIM(bc.cpny_id)'),
                            $cpnyIds
                        );
                    }

                    if (!empty($deptIds)) {
                        $sub->whereIn(
                            DB::raw('TRIM(bc.department_id)'),
                            $deptIds
                        );
                    }
                });
            });
        }

        // =========================================
        // EXTRA STATUS FILTER
        // =========================================

        if ($status !== '') {
            $base->where('bc.status', $status);
        }

        // =========================================
        // TOTAL BEFORE SEARCH
        // =========================================

        $recordsTotal = (clone $base)->count();

        // =========================================
        // SEARCH
        // =========================================

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('bc.docid', 'like', "%{$search}%")
                    ->orWhere('bc.booking_date', 'like', "%{$search}%")
                    ->orWhere('bc.start_time', 'like', "%{$search}%")
                    ->orWhere('bc.end_time', 'like', "%{$search}%")
                    ->orWhere('bc.cpny_id', 'like', "%{$search}%")
                    ->orWhere('bc.department_id', 'like', "%{$search}%")
                    ->orWhere('bc.user_peminta', 'like', "%{$search}%")
                    ->orWhere('bc.purpose_descr', 'like', "%{$search}%")
                    ->orWhere('bc.driver', 'like', "%{$search}%")
                    ->orWhere('bc.no_polisi', 'like', "%{$search}%")
                    ->orWhere('bc.status', 'like', "%{$search}%")
                    ->orWhere('bc.created_by', 'like', "%{$search}%");
            });
        }

        // =========================================
        // TOTAL AFTER SEARCH
        // =========================================

        $recordsFiltered = (clone $base)->count();

        // =========================================
        // FETCH DATA
        // =========================================

        $data = $base
            ->select([
                'bc.id',
                'bc.docid',
                'bc.booking_date',
                'bc.cpny_id',
                'bc.department_id',
                'bc.location_id',
                'bc.user_peminta',
                'bc.site_id',
                'bc.cpny_id_site',
                'bc.purpose_id',
                'bc.purpose_descr',
                'bc.start_time',
                'bc.end_time',
                'bc.user_request',
                'bc.driver',
                'bc.handphone',
                'bc.no_polisi',
                'bc.passenger',
                'bc.status',
                'bc.created_by',
                'bc.created_at',
            ])
            ->orderBy($orderCol, $orderDir)
            ->orderBy('bc.docid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        // =========================================
        // TRANSFORM DATA
        // =========================================

        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);

            $row->extendedProps = [
                'eid' => $row->eid,
            ];

            $route = TrBookingCarDetail::where(
                'docid',
                $row->docid
            )->first();

            $row->route_summary = $route
                ? $route->origin.' → '.$route->destination
                : '-';

            unset($row->id);

            return $row;
        });

        // =========================================
        // RESPONSE
        // =========================================

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function storeBookingCar(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        // =========================================
        // VALIDATION
        // =========================================

        $validated = $request->validate([
            'cpny_id' => ['required'],
            'department_id' => ['required'],

            'user_peminta' => ['required'],

            'cpny_id_site' => ['required'],

            'purpose_id' => ['required'],

            'purpose_descr' => [
                'nullable',
                'required_if:purpose_id,OTHER',
            ],

            'booking_date' => ['required', 'date'],

            'start_time' => ['required'],
            'end_time' => ['required'],

            'routes' => ['required', 'array', 'min:1'],

            'routes.*.origin' => ['required', 'string'],
            'routes.*.destination' => ['required', 'string'],

            'user_request' => ['nullable'],

            'driver' => ['nullable'],
            'handphone' => ['nullable'],
            'no_polisi' => ['nullable'],
            'passenger' => ['nullable'],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            // =========================================
            // BASIC SETUP
            // =========================================

            $dt = now();

            $year = (int) $dt->year;

            $month = str_pad(
                $dt->month,
                2,
                '0',
                STR_PAD_LEFT
            );

            $doctype = 'BCR';

            $username = $user->username;

            // =========================================
            // APPROVAL COMPANY
            // FOLLOW SITE COMPANY
            // =========================================

            $cpny_id = $validated['cpny_id_site'];

            $department_id = $validated['department_id'];

            // =========================================
            // APPROVAL CONTROLLER
            // =========================================

            $approvalCtl = app(
                ApprovalController::class
            );

            // =========================================
            // VALIDATE APPROVAL SETUP
            // =========================================

            $approvalCtl->loadLines(
                $doctype,
                $cpny_id,
                $department_id
            );

            // =========================================
            // AUTO NUMBER
            // =========================================

            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'Booking Car'
            );

            $urutan = (int) $auto['next'];

            $tglbln = substr(
                (string) $year,
                2
            ).$month;

            $docid = $doctype.
                $tglbln.
                sprintf('%03d', $urutan);

            // =========================================
            // CREATE DOCUMENT
            // =========================================

            $booking = TrBookingCar::create([
                'docid' => $docid,

                'booking_date' => $validated['booking_date'],

                'cpny_id' => $validated['cpny_id'],

                'department_id' => $validated['department_id'],

                'location_id' => null,

                'user_peminta' => $validated['user_peminta'],

                'site_id' => null,

                'cpny_id_site' => $validated['cpny_id_site'],

                'purpose_id' => $validated['purpose_id'],

                'purpose_descr' => $validated['purpose_descr'] ?? null,

                'start_time' => $validated['booking_date'].' '.$validated['start_time'],
                'end_time' => $validated['booking_date'].' '.$validated['end_time'],

                'user_request' => $validated['user_request'] ?? null,

                'driver' => $validated['driver'] ?? null,

                'handphone' => $validated['handphone'] ?? null,

                'no_polisi' => $validated['no_polisi'] ?? null,

                'passenger' => $validated['passenger'] ?? null,

                'status' => 'P',

                'created_by' => $username,
                'created_at' => now(),

                'updated_by' => $username,
                'updated_at' => now(),
            ]);

            foreach ($validated['routes'] as $route) {

            TrBookingCarDetail::create([
                'docid' => $docid,

                'cpny_id' => $validated['cpny_id'],

                'origin' => $route['origin'],

                'destination' => $route['destination'],

                'status' => 'A',

                'created_by' => $username,
                'created_at' => now(),

                'updated_by' => $username,
                'updated_at' => now(),
            ]);
        }

            // =========================================
            // GENERATE APPROVAL
            // =========================================

            $ctx = [
                'ignore_nominal' => true,
            ];

            [
                $firstApprovalUsernames,
                $linesCount
            ] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $cpny_id,
                $department_id,
                $username,
                $ctx,
                $dt
            );

            // =========================================
            // HASH ID
            // =========================================

            $eid = Hashids::encode($booking->id);

            // =========================================
            // NOTIFY FIRST APPROVER
            // =========================================

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $booking->status,
                'Booking Car',
                url('/showbookingcar/'.$eid),
                [
                    'info' => $booking->purpose_descr,

                    'createdby' => $booking->created_by,

                    'date' => $dt->toDateTimeString(),
                ]
            );

            // =========================================
            // COMMIT
            // =========================================

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,

                'message' => 'Booking Car berhasil dibuat',

                'data' => [
                    'docid' => $booking->docid,
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

    public function updateBookingCar(
        Request $request,
        $docid
    ) {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // =========================================
        // VALIDATION
        // =========================================

        $validated = $request->validate([
            'cpny_id' => ['required'],
            'department_id' => ['required'],
            'user_peminta' => ['required'],
            'cpny_id_site' => ['required'],

            'purpose_id' => ['required'],

            'purpose_descr' => [
                'nullable',
                'required_if:purpose_id,OTHER',
            ],

            'booking_date' => ['required', 'date'],

            'start_time' => ['required'],
            'end_time' => ['required'],

            'routes' => ['required', 'array', 'min:1'],

            'routes.*.origin' => ['required', 'string'],
            'routes.*.destination' => ['required', 'string'],
            'user_request' => ['nullable'],

            'driver' => ['nullable'],
            'handphone' => ['nullable'],
            'no_polisi' => ['nullable'],
            'passenger' => ['nullable'],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            // =========================================
            // FIND DOCUMENT
            // =========================================

            $booking = TrBookingCar::where(
                'docid',
                $docid
            )->firstOrFail();

            // =========================================
            // ONLY REVISE CAN EDIT
            // =========================================

            if ($booking->status !== 'D') {
                throw new \Exception('Booking Car hanya bisa diedit saat status Revise / Draft.');
            }

            // =========================================
            // ONLY CREATOR CAN EDIT
            // =========================================

            if (
                strtolower(trim($booking->created_by))
                !== strtolower(trim($user->username))
            ) {
                throw new \Exception('Anda tidak berhak edit Booking Car ini.');
            }

            // =========================================
            // BASIC SETUP
            // =========================================

            $doctype = 'BCR';

            $dt = now();

            $username = $user->username;

            // =========================================
            // APPROVAL COMPANY
            // FOLLOW SITE COMPANY
            // =========================================

            $cpny_id = $validated['cpny_id_site'];

            $department_id =
                $validated['department_id'];

            // =========================================
            // APPROVAL CONTROLLER
            // =========================================

            $approvalCtl = app(
                ApprovalController::class
            );

            // =========================================
            // VALIDATE APPROVAL SETUP
            // =========================================

            $approvalCtl->loadLines(
                $doctype,
                $cpny_id,
                $department_id
            );

            // =========================================
            // UPDATE HEADER
            // =========================================

            $booking->cpny_id =
                $validated['cpny_id'];

            $booking->department_id =
                $validated['department_id'];

            $booking->location_id = null;

            $booking->user_peminta =
                $validated['user_peminta'];

            $booking->site_id = null;

            $booking->cpny_id_site =
                $validated['cpny_id_site'];

            $booking->purpose_id =
                $validated['purpose_id'];

            $booking->purpose_descr =
                $validated['purpose_descr'] ?? null;

            $booking->booking_date =
                $validated['booking_date'];

            $booking->start_time =
                $validated['booking_date'].' '.$validated['start_time'];

            $booking->end_time =
                $validated['booking_date'].' '.$validated['end_time'];

            $booking->user_request =
                $validated['user_request'] ?? null;

            $booking->driver =
                $validated['driver'] ?? null;

            $booking->handphone =
                $validated['handphone'] ?? null;

            $booking->no_polisi =
                $validated['no_polisi'] ?? null;

            $booking->passenger =
                $validated['passenger'] ?? null;

            $booking->status = 'P';

            $booking->updated_by = $username;

            $booking->updated_at = $dt;

            $booking->save();

            TrBookingCarDetail::where(
                'docid',
                $booking->docid
            )->delete();

            foreach ($validated['routes'] as $route) {

                TrBookingCarDetail::create([
                    'docid' => $booking->docid,

                    'cpny_id' => $validated['cpny_id'],

                    'origin' => $route['origin'],

                    'destination' => $route['destination'],

                    'status' => 'A',

                    'created_by' => $booking->created_by,
                    'created_at' => now(),

                    'updated_by' => $username,
                    'updated_at' => now(),
                ]);
            }

            // =========================================
            // GENERATE APPROVAL AGAIN
            // =========================================

            $ctx = [
                'ignore_nominal' => true,
            ];

            TrApproval::where(
                'refnbr',
                $booking->docid
            )
            ->whereIn('status', ['P', 'D'])
            ->delete();

            [
                $firstApprovalUsernames,
                $linesCount
            ] = $approvalCtl->generateForDocument(
                $booking->docid,
                $doctype,
                $cpny_id,
                $department_id,
                $username,
                $ctx,
                $dt
            );

            // =========================================
            // UPDATE COMPLETED BY
            // =========================================

            if ($firstApprovalUsernames) {
                $booking->completed_by =
                    is_array($firstApprovalUsernames)
                    ? implode(
                        ',',
                        $firstApprovalUsernames
                    )
                    : $firstApprovalUsernames;

                $booking->completed_at = $dt;

                $booking->save();
            }

            // =========================================
            // HASH ID
            // =========================================

            $eid = Hashids::encode($booking->id);

            // =========================================
            // NOTIFY FIRST APPROVER
            // =========================================

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $booking->status,
                'Booking Car',
                url('/showbookingcar/'.$eid),
                [
                    'info' => $booking->purpose_descr,

                    'createdby' => $booking->created_by,

                    'date' => $dt->toDateTimeString(),
                ]
            );

            // =========================================
            // COMMIT
            // =========================================

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,

                'message' => 'Booking Car berhasil diupdate dan dikirim ulang approval.',

                'data' => [
                    'docid' => $booking->docid,
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

    public function cancel(
        Request $request,
        $docid
    ) {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            // =========================================
            // FIND DOCUMENT
            // =========================================

            $booking = TrBookingCar::where(
                'docid',
                $docid
            )->firstOrFail();

            // =========================================
            // ONLY CREATOR
            // =========================================

            if (
                strtolower(trim($booking->created_by))
                !== strtolower(trim($user->username))
            ) {
                return response()->json([
                    'success' => false,

                    'message' => 'You cannot cancel this request',
                ], 403);
            }

            // =========================================
            // ONLY REVISE DOCUMENT
            // =========================================

            if ($booking->status !== 'D') {
                return response()->json([
                    'success' => false,

                    'message' => 'Only revise document can be cancelled',
                ], 400);
            }

            // =========================================
            // CANCEL HEADER
            // =========================================

            $booking->status = 'X';

            $booking->updated_by =
                $user->username;

            $booking->updated_at = now();

            $booking->completed_by =
                $user->username;

            $booking->completed_at = now();

            $booking->save();

            // =========================================
            // CANCEL REMAINING APPROVALS
            // =========================================

            TrApproval::where(
                'refnbr',
                $booking->docid
            )
                ->where('status', 'P')
                ->update([
                    'status' => 'X',

                    'updated_by' => $user->username,

                    'updated_at' => now(),
                ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,

                'message' => 'Booking Car request cancelled successfully',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function detail($eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;

        // =========================================
        // INVALID HASH
        // =========================================

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Booking Car ID',
            ], 404);
        }

        // =========================================
        // FIND DOCUMENT
        // =========================================

        $booking = TrBookingCar::with('routes')
            ->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking Car not found',
            ], 404);
        }

        // =========================================
        // RESPONSE
        // =========================================

        return response()->json([
            'success' => true,

            'data' => [
                'docid' => $booking->docid,

                'eid' => $eid,

                'status' => $booking->status,

                // =====================================
                // REQUESTER
                // =====================================

                'user_peminta' => $booking->user_peminta,

                'created_by' => $booking->created_by,

                // =====================================
                // COMPANY / DEPARTMENT
                // =====================================

                'cpny_id' => $booking->cpny_id,

                'department_id' => $booking->department_id,

                // =====================================
                // LOCATION
                // =====================================

                'location_id' => $booking->location_id,

                'site_id' => $booking->site_id,

                'cpny_id_site' => $booking->cpny_id_site,

                // =====================================
                // PURPOSE
                // =====================================

                'purpose_id' => $booking->purpose_id,

                'purpose_descr' => $booking->purpose_descr,

                // =====================================
                // DATE & TIME
                // =====================================

                'booking_date' => $booking->booking_date,

                'start_time' => $booking->start_time,

                'end_time' => $booking->end_time,

                // =====================================
                // ROUTE
                // =====================================

                'routes' => $booking->routes
                    ->map(function ($route) {

                        return [
                            'origin' => $route->origin,
                            'destination' => $route->destination,
                        ];

                    })->values(),


                // =====================================
                // REQUEST DETAIL
                // =====================================

                'user_request' => $booking->user_request,

                // =====================================
                // DRIVER INFO
                // =====================================

                'driver' => $booking->driver,

                'handphone' => $booking->handphone,

                'no_polisi' => $booking->no_polisi,

                'passenger' => $booking->passenger,

                // =====================================
                // APPROVAL INFO
                // =====================================

                'checked_by' => $booking->checked_by,

                'checked_at' => $booking->checked_at,

                'completed_by' => $booking->completed_by,

                'completed_at' => $booking->completed_at,

                // =====================================
                // REVISE
                // =====================================

                'revise_reason' => $booking->revise_reason,
            ],
        ]);
    }

    public function updateGaAdvice(Request $request, $docid)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // =========================================
        // ONLY GA
        // =========================================

        if (!$user->hasRole('GAACCESS')) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        // =========================================
        // VALIDATION
        // =========================================

        $validated = $request->validate([
            'driver' => ['required'],
            'handphone' => ['nullable'],
            'no_polisi' => ['nullable'],
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            // =========================================
            // FIND DOCUMENT
            // =========================================

            $booking = TrBookingCar::where(
                'docid',
                $docid
            )->firstOrFail();

            // =========================================
            // MUST BE COMPLETED FIRST
            // =========================================

            if ($booking->status !== 'C') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking Car not completed yet',
                ], 400);
            }

            // =========================================
            // UPDATE DRIVER INFO
            // =========================================

            $booking->update([
                'driver' => $validated['driver'],

                'handphone' => $validated['handphone'] ?? null,

                'no_polisi' => $validated['no_polisi'] ?? null,

                'checked_by' => $user->username,
                'checked_at' => now(),

                'updated_by' => $user->username,
                'updated_at' => now(),
            ]);

            // =========================================
            // COMMIT
            // =========================================

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Driver information saved successfully',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function approveBookingCar(Request $request, $docid)
    {
        $user = $request->user();

        $doctype = 'BCR';

        $booking = TrBookingCar::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking Car not found',
            ], 404);
        }

        $eid = Hashids::encode($booking->id);

        $docUrl = url('/showbookingcar/'.$eid);

        $fullname = data_get(
            $booking,
            'creator.name'
        ) ?: $booking->created_by;

        $result = app(ApprovalController::class)->approveStep(
            $booking->docid,

            $doctype,

            $user->username,

            $user->name,

            // =====================================
            // COMPLETE
            // =====================================

            function (
                string $refnbr,
                \Carbon\Carbon $now
            ) use (
                $booking,
                $fullname,
                $docUrl
            ) {
                $booking->status = 'C';

                $booking->completed_by =
                    $booking->completed_by
                    ?: auth()->user()->username;

                $booking->completed_at = $now;

                $booking->updated_by =
                    auth()->user()->username;

                $booking->updated_at = $now;

                $booking->save();

                app(ApprovalController::class)
                    ->notifyRequesterOnStatus(
                        $booking->docid,
                        'Booking Car',
                        'C',
                        $booking->created_by,
                        $docUrl,
                        [
                            // APPROVAL COMPANY
                            'cpnyid' => $booking->cpny_id_site ?? '',

                            'deptname' => $booking->department_id ?? '',

                            'date' => $booking->booking_date,

                            'info' => $booking->purpose_descr,

                            'fullname' => $fullname,

                            'name' => $fullname,

                            'createdby' => $fullname,
                        ]
                    );
            },

            // =====================================
            // NEXT APPROVER
            // =====================================

            function (
                $next,
                \Carbon\Carbon $now
            ) use (
                $booking,
                $docUrl
            ) {
                app(ApprovalController::class)
                    ->notifyFirstApprover(
                        $booking->docid,
                        'BCR',
                        'P',
                        'Booking Car',
                        $docUrl,
                        [
                            'info' => $booking->purpose_descr,

                            'createdby' => $booking->created_by,

                            'date' => $now->toDateTimeString(),
                        ]
                    );

                // TRACK LAST PROCESS

                $booking->completed_by =
                    auth()->user()->username;

                $booking->completed_at = $now;

                $booking->updated_by =
                    auth()->user()->username;

                $booking->updated_at = $now;

                $booking->save();
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
                    ?? 'Approve failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking Car approved successfully',
        ]);
    }

    public function rejectBookingCar(Request $request, $docid)
    {
        $request->validate([
            'comment' => ['required', 'string'],
        ]);

        $user = $request->user();

        $doctype = 'BCR';

        $booking = TrBookingCar::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking Car not found',
            ], 404);
        }

        $eid = Hashids::encode($booking->id);

        $docUrl = url('/showbookingcar/'.$eid);

        $fullname = data_get(
            $booking,
            'creator.name'
        ) ?: $booking->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $booking->docid,

            $doctype,

            $user->username,

            $user->name,

            function (
                string $refnbr,
                \Carbon\Carbon $now
            ) use (
                $booking,
                $fullname,
                $docUrl
            ) {
                // =================================
                // UPDATE HEADER
                // =================================

                $booking->status = 'R';

                $booking->completed_by =
                    auth()->user()->username;

                $booking->completed_at = $now;

                $booking->updated_by =
                    auth()->user()->username;

                $booking->updated_at = $now;

                $booking->save();

                // =================================
                // NOTIFY REQUESTER
                // =================================

                app(ApprovalController::class)
                    ->notifyRequesterOnStatus(
                        $booking->docid,
                        'Booking Car',
                        'R',
                        $booking->created_by,
                        $docUrl,
                        [
                            // APPROVAL COMPANY
                            'cpnyid' => $booking->cpny_id_site ?? '',

                            'deptname' => $booking->department_id ?? '',

                            'date' => $now->toDateString(),

                            'info' => $booking->purpose_descr ?? '',

                            'fullname' => $fullname,

                            'name' => $fullname,

                            'createdby' => $fullname,
                        ]
                    );

                // =================================
                // SAVE COMMENT
                // =================================

                try {
                    app(SendCommentController::class)
                        ->sendmsg(
                            $booking->id,
                            'BCR',
                            request()
                        );
                } catch (\Throwable $e) {
                    \Log::warning(
                        'Send reject comment Booking Car failed',
                        [
                            'docid' => $booking->docid,

                            'error' => $e->getMessage(),
                        ]
                    );
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
                    ?? 'Reject failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking Car rejected successfully',
        ]);
    }

    public function reviseBookingCar(Request $request, $docid)
    {
        $request->validate([
            'comment' => ['required', 'string'],
        ]);

        $user = $request->user();

        $doctype = 'BCR';

        $booking = TrBookingCar::with('creator')
            ->where('docid', $docid)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking Car not found',
            ], 404);
        }

        $eid = Hashids::encode($booking->id);

        $docUrl = url('/showbookingcar/'.$eid);

        $fullname = data_get(
            $booking,
            'creator.name'
        ) ?: $booking->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $booking->docid,

            $doctype,

            $user->username,

            $user->name,

            function (
                string $refnbr,
                \Carbon\Carbon $now
            ) use (
                $booking,
                $fullname,
                $docUrl
            ) {
                // =================================
                // UPDATE HEADER
                // =================================

                $booking->status = 'D';

                $booking->completed_by =
                    auth()->user()->username;

                $booking->completed_at = $now;

                $booking->updated_by =
                    auth()->user()->username;

                $booking->updated_at = $now;

                $booking->save();

                // =================================
                // NOTIFY REQUESTER
                // =================================

                app(ApprovalController::class)
                    ->notifyRequesterOnStatus(
                        $booking->docid,
                        'Booking Car',
                        'D',
                        $booking->created_by,
                        $docUrl,
                        [
                            // APPROVAL COMPANY
                            'cpnyid' => $booking->cpny_id_site ?? '',

                            'deptname' => $booking->department_id ?? '',

                            'date' => $now->toDateString(),

                            'info' => $booking->purpose_descr ?? '',

                            'fullname' => $fullname,

                            'name' => $fullname,

                            'createdby' => $fullname,
                        ]
                    );

                // =================================
                // SAVE COMMENT
                // =================================

                try {
                    app(SendCommentController::class)
                        ->sendmsg(
                            $booking->id,
                            'BCR',
                            request()
                        );
                } catch (\Throwable $e) {
                    \Log::warning(
                        'Send revise comment Booking Car failed',
                        [
                            'docid' => $booking->docid,

                            'error' => $e->getMessage(),
                        ]
                    );
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
                    ?? 'Revise failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking Car revised successfully',
        ]);
    }

    public function findByHash($eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;

        abort_if(!$id, 404);

        $booking = TrBookingCar::with('routes')
            ->findOrFail($id);

        return response()->json($booking);
    }

    public function tracking($hash)
    {
        try {
            // =========================================
            // DECODE HASH
            // =========================================

            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);

            // =========================================
            // FIND DOCUMENT
            // =========================================

            $booking = TrBookingCar::with('routes')
                ->findOrFail($id);

            // =========================================
            // GET USER NAME
            // =========================================

            $getName = function (?string $username) {
                if (!$username) {
                    return null;
                }

                return User::where(
                    'username', $username
                )->value('name') ?? $username;
            };

            // =========================================
            // STEPS
            // =========================================

            $steps = [];

            // =========================================
            // SUBMITTED
            // =========================================

            $steps[] = [
                'key' => 'submitted',

                'title' => 'Booking Car',

                'status' => 'C',

                'status_label' => 'Submitted',

                'by' => $getName(
                    $booking->created_by
                ),

                'at' => optional(
                    $booking->created_at
                )->format('Y-m-d H:i'),
            ];

            // =========================================
            // APPROVALS
            // =========================================

            $approvals = TrApproval::query()
                ->where(
                    'refnbr',
                    $booking->docid
                )
                ->where(
                    'status',
                    '<>',
                    'X'
                )
                ->orderByRaw(
                    'CAST(aprv_leveling AS INTEGER)'
                )
                ->get();

            // =========================================
            // OPTIONAL COMMENT TABLE
            // =========================================

            $comment = null;

            try {
                $comment = DB::table('tr_comment')
                    ->where(
                        'refid',
                        $booking->id
                    )
                    ->where(
                        'doctype',
                        'BCR'
                    )
                    ->latest('created_at')
                    ->value('comment');
            } catch (\Throwable $e) {
                // ignore if table not exists
            }

            // =========================================
            // MESSAGE COMMENTS
            // =========================================

            $comments = TrMessage::where(
                'doctype',
                'BCR'
            )
                ->where(
                    'refnbr',
                    $booking->docid
                )
                ->orderByDesc(
                    'message_date'
                )
                ->get([
                    'username',
                    'name',
                    'message',
                    'message_date',
                ]);

            // =========================================
            // APPROVAL STEPS
            // =========================================

            foreach ($approvals as $aprv) {
                // =====================================
                // FIND COMMENT FOR THIS APPROVER
                // =====================================

                $stepComment = $comments->first(
                    function ($msg) use ($aprv) {
                        return strtolower(
                            trim($msg->username)
                        ) === strtolower(
                            trim(
                                $aprv->updated_by
                                ?: $aprv->aprv_username
                            )
                        );
                    }
                );

                // =====================================
                // STEP
                // =====================================

                $steps[] = [
                    'key' => 'approval_'.
                        $aprv->aprv_leveling,

                    'title' => $aprv->aprv_name
                        ?: (
                            'Approval Level '.
                            $aprv->aprv_leveling
                        ),

                    'status' => $aprv->status,

                    'status_label' => match (
                        $aprv->status
                    ) {
                        'P' => 'Waiting approval',
                        'A' => 'Approved',
                        'R' => 'Rejected',
                        'D' => 'Revise',
                        default => '-',
                    },

                    // APPROVER USERNAME

                    'aprv_username' => $aprv->aprv_username,

                    // DISPLAY USER

                    'by' => $aprv->status === 'P'
                        ? null
                        : $getName(
                            $aprv->updated_by
                            ?: $aprv->aprv_username
                        ),

                    // APPROVAL DATE

                    'at' => $aprv->aprv_dateafter
                        ? \Carbon\Carbon::parse(
                            $aprv->aprv_dateafter
                        )->format('Y-m-d H:i')
                        : null,

                    // COMMENT

                    'comment' => $stepComment?->message,
                ];
            }

            // =========================================
            // LATEST COMMENT
            // =========================================

            $latestComment = TrMessage::where(
                'doctype',
                'BCR'
            )
                ->where(
                    'refnbr',
                    $booking->docid
                )
                ->latest('message_date')
                ->first();

            // =========================================
            // RESPONSE
            // =========================================

            return response()->json([
                'success' => true,

                'doc' => $booking->docid,

                'steps' => $steps,

                'status' => $booking->status,

                'status_label' => match (
                    $booking->status
                ) {
                    'P' => 'Pending',
                    'C' => 'Completed',
                    'R' => 'Rejected',
                    'D' => 'Revise',
                    'X' => 'Cancelled',
                    default => '-',
                },

                'revise_reason' => $latestComment?->message,

                'comments' => $comments,
            ]);
        } catch (\Throwable $e) {
            \Log::error(
                'BOOKING CAR TRACKING ERROR',
                [
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,

                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function printBookingCar($hash)
    {
        // =========================================
        // DECODE HASH
        // =========================================

        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        // =========================================
        // AUTH
        // =========================================

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // =========================================
        // FIND DOCUMENT
        // =========================================

        $booking = TrBookingCar::with([
            'creator:username,name',
            'routes',
        ])->findOrFail($id);
        // =========================================
        // APPROVALS
        // =========================================

        $approvals = TrApproval::query()
            ->where('refnbr', $booking->docid)
            ->where('status', '<>', 'X')
            ->orderByRaw(
                'CAST(aprv_leveling AS INTEGER)'
            )
            ->get();

        // =========================================
        // COMPANY
        // =========================================

        $company = MsCompany::where(
            'cpny_id',
            $booking->cpny_id
        )->first();

        // =========================================
        // STATUS LABEL
        // =========================================

        $status_doc = match ($booking->status) {
            'P' => 'On Progress',
            'C' => 'Completed',
            'R' => 'Rejected',
            'D' => 'Revise',
            'X' => 'Cancelled',
            default => '-',
        };

        // =========================================
        // PDF
        // =========================================

        $pdf = \PDF::loadView(
            'pages.bookingcar.pdf_bookingcar',
            [
                'booking' => $booking,

                'approvals' => $approvals,

                'company' => $company,

                'status_doc' => $status_doc,
            ]
        );

        $pdf->setPaper('A4', 'portrait');

        // =========================================
        // STREAM
        // =========================================

        return $pdf->stream(
            'booking_car_'.
            $booking->docid.
            '.pdf'
        );
    }
}
