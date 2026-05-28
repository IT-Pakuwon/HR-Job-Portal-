<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsCategory;
use App\Models\MsCompany;
use App\Models\TrApproval;
use App\Models\TrBookingCar;
use App\Models\TrBookingCarDetail;
use App\Models\TrMessage;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Log;

class BookingCarController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        $isGA = $user->hasRole('GAACCESS');

        $q = TrBookingCar::query();

        if (!$isGA) {
            $q->where(function ($sub) use (
                $user,
                $cpnyIds,
                $deptIds
            ) {
                // OWN DOCUMENT

                $sub->whereRaw(
                    'LOWER(TRIM(created_by)) = ?',
                    [strtolower(trim($user->username))]
                );

                // SAME COMPANY + SAME DEPARTMENT

                $sub->orWhere(function ($x) use (
                    $cpnyIds,
                    $deptIds
                ) {
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
        $q->whereIn(
            'status',
            ['P', 'C', 'D', 'R']
        );

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

        $usercpny = Usercpny::where(
            'username',
            $user->username
        )->get();

        $usercpny2 = Usercpny::where(
            'username',
            $user->username
        )->first();

        $userdept = Userdept::where(
            'username',
            $user->username
        )->get();

        $userdept2 = Userdept::where(
            'username',
            $user->username
        )->first();

        $company = MsCompany::where('status', 'A')
            ->select(
                'cpny_id',
                'cpny_name'
            )
            ->orderBy('cpny_name')
            ->get();

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

        $drivers = DB::connection('pgsql5')
            ->table('ms_driver_opr')
            ->where('status', 'A')
            ->orderBy('drivername')
            ->get();


        $kendaraan = DB::connection('pgsql')
            ->table('ms_kendaraan')
            ->where('status', 'A')
            ->where('kategori_kendaraan', 'Operational')
            ->orderBy('no_polisi')
            ->get();

        $purposes = MsCategory::query()
            ->where('doctype', 'BCR')
            ->where('groups', 'PURPOSE')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->get([
                'categoryid',
                'category_name',
            ]);

        $statusPerjalanan = MsCategory::query()
            ->where('doctype', 'BCR')
            ->where('groups', 'STATUS')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->get([
                'categoryid',
                'category_name',
            ]);

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
                'kendaraan',
                'purposes',
                'statusPerjalanan'

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

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;


        $isGA = $user->hasRole('GAACCESS');

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);

        $search = trim((string) $request->input('search.value', ''));

        $status = trim((string) $request->query('status', ''));

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


        $base = TrBookingCar::from('tr_booking_car as bc');

        $base->whereIn('bc.status', ['P', 'C', 'D', 'R', 'X']);

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

        if ($status !== '') {
            $base->where('bc.status', $status);
        }

        $recordsTotal = (clone $base)->count();

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

        $recordsFiltered = (clone $base)->count();

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

        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);


            $routes = TrBookingCarDetail::where(
                'docid',
                $row->docid
            )
                ->orderBy('booking_order')
                ->get([
                    'origin',
                    'destination',
                    'booking_order',
                ]);


            $firstRoute = $routes->first();

            $row->route_summary = $firstRoute
                ? $firstRoute->origin . ' → ' . $firstRoute->destination
                : '-';


            $row->routes = $routes;

            $row->extendedProps = [
                'eid' => $row->eid,
                'status' => $row->status,
            ];

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

    public function storeBookingCar(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->merge([
            'routes' => collect($request->location_from)
                ->map(function ($origin, $index) use ($request) {
                    return [
                        'origin' => $origin,
                        'destination' => $request->destination[$index] ?? null,
                    ];
                })
                ->toArray(),
        ]);

        $validated = $request->validate([
            'cpny_id' => ['required'],
            'department_id' => ['required'],

            'user_peminta' => ['required'],

            'cpny_id_site' => ['required'],

            'purpose_id' => ['required'],

            'purpose_descr' => [
                'required',
                'string',
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

        $purpose = MsCategory::query()
            ->where('doctype', 'BCR')
            ->where('groups', 'PURPOSE')
            ->where('status', 'A')
            ->where('categoryid', $validated['purpose_id'])
            ->first();

        if (!$purpose) {
            throw ValidationException::withMessages(['purpose_id' => 'Purpose tidak valid.']);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
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

            $cpny_id = $validated['cpny_id_site'];

            $department_id = $validated['department_id'];

            $approvalCtl = app(
                ApprovalController::class
            );

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
                'Booking Car'
            );

            $urutan = (int) $auto['next'];

            $tglbln = substr(
                (string) $year,
                2
            ) . $month;

            $docid = $doctype .
                $tglbln .
                sprintf('%03d', $urutan);

            $booking = TrBookingCar::create([
                'docid' => $docid,

                'booking_date' => $validated['booking_date'],

                'cpny_id' => $validated['cpny_id'],

                'department_id' => $validated['department_id'],

                'location_id' => null,

                'user_peminta' => $validated['user_peminta'],

                'site_id' => null,

                'cpny_id_site' => $validated['cpny_id_site'],

                'purpose_id' => $purpose->categoryid,

                'purpose_descr' => $validated['purpose_descr'],

                'start_time' => $validated['booking_date'] . ' ' . $validated['start_time'],

                'end_time' => $validated['booking_date'] . ' ' . $validated['end_time'],

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

            foreach ($validated['routes'] as $index => $route) {
                TrBookingCarDetail::create([
                    'docid' => $docid,

                    'cpny_id' => $validated['cpny_id'],

                    'booking_order' => $index + 1,

                    'origin' => $route['origin'],

                    'destination' => $route['destination'],

                    'status' => 'A',

                    'created_by' => $username,
                    'created_at' => now(),

                    'updated_by' => $username,
                    'updated_at' => now(),
                ]);
            }

            $ctx = [
                'ignore_nominal' => true,

                'condition' => $validated['cpny_id_site'] ?? $validated['cpny_id'],

                'purpose' => $validated['purpose_id'],
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

            $eid = Hashids::encode($booking->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $booking->status,
                'Booking Car',
                url('/showbookingcar/' . $eid),
                [
                    'info' => $booking->purpose_descr,

                    'createdby' => $booking->created_by,

                    'date' => $dt->toDateTimeString(),
                ]
            );

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

        $request->merge([
            'routes' => collect($request->location_from)
                ->map(function ($origin, $index) use ($request) {
                    return [
                        'origin' => $origin,
                        'destination' => $request->destination[$index] ?? null,
                    ];
                })
                ->toArray(),
        ]);

        $validated = $request->validate([
            'cpny_id' => ['required'],
            'department_id' => ['required'],

            'user_peminta' => ['required'],

            'cpny_id_site' => ['required'],

            'purpose_id' => ['required'],

            'purpose_descr' => [
                'required',
                'string',
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

        $purpose = MsCategory::query()
            ->where('doctype', 'BCR')
            ->where('groups', 'PURPOSE')
            ->where('status', 'A')
            ->where('categoryid', $validated['purpose_id'])
            ->first();

        if (!$purpose) {
            throw ValidationException::withMessages(['purpose_id' => 'Purpose tidak valid.']);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $booking = TrBookingCar::where(
                'docid',
                $docid
            )->firstOrFail();

            if ($booking->status !== 'D') {
                throw new \Exception('Booking Car hanya bisa diedit saat status Revise.');
            }

            if (
                strtolower(trim($booking->created_by))
                !== strtolower(trim($user->username))
            ) {
                throw new \Exception('Anda tidak berhak edit Booking Car ini.');
            }

            $doctype = 'BCR';

            $dt = now();

            $username = $user->username;

            $cpny_id = $validated['cpny_id_site'];

            $department_id = $validated['department_id'];

            $approvalCtl = app(
                ApprovalController::class
            );

            $approvalCtl->loadLines(
                $doctype,
                $cpny_id,
                $department_id
            );

            $booking->booking_date =
                $validated['booking_date'];

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
                $purpose->categoryid;

            $booking->purpose_descr =
                $validated['purpose_descr'];

            $booking->start_time =
                $validated['booking_date'] . ' ' . $validated['start_time'];

            $booking->end_time =
                $validated['booking_date'] . ' ' . $validated['end_time'];

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

            $booking->updated_by =
                $username;

            $booking->updated_at =
                $dt;

            $booking->save();

            TrBookingCarDetail::where(
                'docid',
                $booking->docid
            )->delete();

            foreach ($validated['routes'] as $index => $route) {
                TrBookingCarDetail::create([
                    'docid' => $booking->docid,

                    'cpny_id' => $validated['cpny_id'],

                    'booking_order' => $index + 1,

                    'origin' => $route['origin'],

                    'destination' => $route['destination'],

                    'status' => 'A',

                    'created_by' => $booking->created_by,
                    'created_at' => now(),

                    'updated_by' => $username,
                    'updated_at' => now(),
                ]);
            }

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

            if ($firstApprovalUsernames) {
                $booking->completed_by =
                    is_array($firstApprovalUsernames)
                    ? implode(',', $firstApprovalUsernames)
                    : $firstApprovalUsernames;

                $booking->completed_at =
                    $dt;

                $booking->save();
            }

            $eid = Hashids::encode(
                $booking->id
            );

            $approvalCtl->notifyFirstApprover(
                $booking->docid,
                $doctype,
                $booking->status,
                'Booking Car',
                url('/showbookingcar/' . $eid),
                [
                    'info' => $booking->purpose_descr,

                    'createdby' => $booking->created_by,

                    'date' => $dt->toDateTimeString(),
                ]
            );

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

            $booking = TrBookingCar::where(
                'docid',
                $docid
            )->firstOrFail();


            if (
                strtolower(trim($booking->created_by))
                !== strtolower(trim($user->username))
            ) {
                return response()->json([
                    'success' => false,

                    'message' => 'You cannot cancel this request',
                ], 403);
            }

            if ($booking->status !== 'D') {
                return response()->json([
                    'success' => false,

                    'message' => 'Only revise document can be cancelled',
                ], 400);
            }


            $booking->status = 'X';

            $booking->updated_by =
                $user->username;

            $booking->updated_at = now();

            $booking->completed_by =
                $user->username;

            $booking->completed_at = now();

            $booking->save();

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

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Booking Car ID',
            ], 404);
        }

        $booking = TrBookingCar::with([
            'routes',
        ])->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking Car not found',
            ], 404);
        }

        $approvals = TrApproval::query()
            ->where('refnbr', $booking->docid)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS INTEGER)')
            ->get();

        $tracking = TrMessage::query()
            ->where('doctype', 'BCR')
            ->where('refnbr', $booking->docid)
            ->orderByDesc('message_date')
            ->get();

        return response()->json([
            'success' => true,

            'data' => [

                'docid' => $booking->docid,

                'eid' => $eid,

                'status' => $booking->status,

                'user_peminta' => $booking->user_peminta,

                'user_request' => $booking->user_request,

                'created_by' => $booking->created_by,

                'cpny_id' => $booking->cpny_id,

                'department_id' => $booking->department_id,

                'location_id' => $booking->location_id,

                'site_id' => $booking->site_id,

                'cpny_id_site' => $booking->cpny_id_site,

                'purpose_id' => $booking->purpose_id,

                'purpose_descr' => $booking->purpose_descr,

                'booking_date' => $booking->booking_date,

                'start_time' => $booking->start_time,

                'end_time' => $booking->end_time,

                'driver_name' => $booking->driver,

                'handphone' => $booking->handphone,

                'nopol' => $booking->no_polisi,

                'passenger' => $booking->passenger,

                'checked_by' => $booking->checked_by,

                'checked_at' => $booking->checked_at,

                'completed_by' => $booking->completed_by,

                'completed_at' => $booking->completed_at,

                'revise_reason' => $booking->revise_reason,

                'details' => $booking->routes
                    ->map(function ($route) {

                        return [

                            'origin' => $route->origin,

                            'destination' => $route->destination,

                            'booking_order' => $route->booking_order,
                        ];
                    })->values(),

                'approvals' => $approvals
                    ->map(function ($approval) {

                        return [

                            'aprv_name' => $approval->aprv_name,

                            'aprv_leveling' => $approval->aprv_leveling,

                            'status' => $approval->status,

                            'remarks' => $approval->remarks,

                            'aprv_dateafter' => $approval->aprv_dateafter,

                            'approval_label' => $approval->aprv_leveling
                                ? 'APPROVAL ' . $approval->aprv_leveling . '.00'
                                : 'APPROVAL',
                        ];

                    })->values(),

                'tracking' => $tracking
                    ->map(function ($track) {

                        return [

                            'title' => $track->name
                                ?: $track->username,

                            'description' => $track->message,

                            'created_at' => $track->message_date
                                ? \Carbon\Carbon::parse(
                                    $track->message_date
                                )->format('d M Y H:i')
                                : null,
                        ];
                    })->values(),

                'can_edit' => (
                    $booking->status === 'D'
                    && strtolower(trim($booking->created_by))
                        === strtolower(trim(Auth::user()->username))
                ),

                'can_cancel' => (
                    $booking->status === 'D'
                    && strtolower(trim($booking->created_by))
                        === strtolower(trim(Auth::user()->username))
                ),

                'can_approve' => TrApproval::query()
                    ->where('refnbr', $booking->docid)
                    ->where('status', 'P')
                    ->get()
                    ->contains(function ($approval) {

                        $usernames = collect(
                            explode(',', strtolower($approval->aprv_username))
                        )->map(fn ($x) => trim($x));

                        return $usernames->contains(
                            strtolower(trim(Auth::user()->username))
                        );
                    }),

                'can_reject' => TrApproval::query()
                    ->where('refnbr', $booking->docid)
                    ->where('status', 'P')
                    ->get()
                    ->contains(function ($approval) {

                        $usernames = collect(
                            explode(',', strtolower($approval->aprv_username))
                        )->map(fn ($x) => trim($x));

                        return $usernames->contains(
                            strtolower(trim(Auth::user()->username))
                        );
                    }),

                'can_revise' => TrApproval::query()
                    ->where('refnbr', $booking->docid)
                    ->where('status', 'P')
                    ->get()
                    ->contains(function ($approval) {

                        $usernames = collect(
                            explode(',', strtolower($approval->aprv_username))
                        )->map(fn ($x) => trim($x));

                        return $usernames->contains(
                            strtolower(trim(Auth::user()->username))
                        );
                    }),
            ],
        ]);
    }

    public function updateGaAdvice(Request $request, string $hash)
    {
        try {

            $bookingId = Hashids::decode($hash)[0] ?? null;

            if (!$bookingId) {
                throw new \Exception('Invalid booking reference.');
            }

            $booking = TrBookingCar::findOrFail($bookingId);

            if (!Auth::user()->hasRole('GAACCESS')) {
                throw new \Exception('You are not authorized to process this booking.');
            }

            if (!in_array($booking->status, ['C', 'X'])) {
                throw new \Exception(
                    'Booking cannot be processed. Only completed or previously processed bookings are allowed.'
                );
            }

            $expiredDate = Carbon::parse($booking->booking_date)
                ->addDays(3)
                ->endOfDay();

            if (now()->gt($expiredDate)) {
                throw new \Exception(
                    'Booking can only be processed until H+3 from booking date.'
                );
            }

            $validated = $request->validate([
                'status_perjalanan' => ['required'],
                'driver'            => ['nullable', 'string', 'max:255'],
                'handphone'         => ['nullable', 'string', 'max:100'],
                'no_polisi'         => ['nullable', 'string', 'max:100'],
            ]);

            $statusPerjalanan = MsCategory::query()
                ->where('doctype', 'BCR')
                ->where('groups', 'STATUS')
                ->where('status', 'A')
                ->where('category_name', $validated['status_perjalanan'])
                ->first();

            if (!$statusPerjalanan) {
                throw ValidationException::withMessages([
                    'status_perjalanan' => 'Status perjalanan tidak valid.'
                ]);
            }

            if ($validated['status_perjalanan'] === 'Handle by Taxi') {

                if (empty($validated['driver'])) {
                    throw ValidationException::withMessages([
                        'driver' => 'Driver wajib dipilih untuk Handle by Taxi.'
                    ]);
                }

                if (empty($validated['no_polisi'])) {
                    throw ValidationException::withMessages([
                        'no_polisi' => 'Kendaraan wajib dipilih untuk Handle by Taxi.'
                    ]);
                }
            }

            DB::connection('pgsql5')->transaction(function () use (
                $booking,
                $validated
            ) {

                $booking->status_perjalanan =
                    $validated['status_perjalanan'];

                $booking->driver =
                    $validated['driver'] ?? null;

                $booking->handphone =
                    $validated['handphone'] ?? null;

                $booking->no_polisi =
                    $validated['no_polisi'] ?? null;

                $booking->status = 'X';

                $booking->completed_by = Auth::user()->username;

                $booking->completed_at = now();

                $booking->updated_by = Auth::user()->username;

                $booking->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Booking Car processed successfully.',
            ]);

        } catch (ValidationException $e) {

            throw $e;

        } catch (\Throwable $e) {

            Log::error('BookingCar GA Process Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function changeCompanyExpense(Request $request, string $hash)
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('GAACCESS')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'cpny_id_site' => ['required']
        ]);

        $decoded = Hashids::decode($hash);

        if (empty($decoded)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document'
            ], 404);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {

            $booking = TrBookingCar::findOrFail($decoded[0]);

            if (in_array($booking->status, ['C', 'R', 'X'])) {
                throw new \Exception(
                    'Company expense cannot be changed because the document has been completed, rejected, or cancelled.'
                );
            }

            if (
                trim($booking->cpny_id_site) ===
                trim($validated['cpny_id_site'])
            ) {
                throw new \Exception(
                    'Selected company expense is the same as current company expense.'
                );
            }
            $booking->cpny_id_site = $validated['cpny_id_site'];
            $booking->updated_by = $user->username;
            $booking->updated_at = now();
            $booking->save();


            TrApproval::query()
                ->where('refnbr', $booking->docid)
                ->whereRaw(
                    'UPPER(TRIM(aprv_type)) = ?',
                    ['CONDITION']
                )
                ->whereIn('status', ['P', 'D'])
                ->delete();


            $doctype = 'BCR';

            $dt = now();

            $approvalCtl = app(
                ApprovalController::class
            );

            $approvalCtl->loadLines(
                $doctype,
                $validated['cpny_id_site'],
                $booking->department_id
            );

            $ctx = [
                'ignore_nominal' => true,
            ];

            [
                $firstApprovalUsernames,
                $linesCount
            ] = $approvalCtl->generateForDocument(
                $booking->docid,
                $doctype,
                $validated['cpny_id_site'],
                $booking->department_id,
                $user->username,
                $ctx,
                $dt
            );


            $eid = Hashids::encode($booking->id);

            $approvalCtl->notifyFirstApprover(
                $booking->docid,
                $doctype,
                $booking->status,
                'Booking Car',
                url('/showbookingcar/' . $eid),
                [
                    'info' => $booking->purpose_descr,
                    'createdby' => $booking->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Company expense updated successfully. Approval has been regenerated.'
            ]);
        } catch (\Throwable $e) {

            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
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

        $docUrl = url('/showbookingcar/' . $eid);

        $fullname = data_get(
            $booking,
            'creator.name'
        ) ?: $booking->created_by;

        $result = app(ApprovalController::class)->approveStep(
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

        $docUrl = url('/showbookingcar/' . $eid);

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

                $booking->status = 'R';

                $booking->completed_by =
                    auth()->user()->username;

                $booking->completed_at = $now;

                $booking->updated_by =
                    auth()->user()->username;

                $booking->updated_at = $now;

                $booking->save();

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

                try {
                    request()->merge([
                        'reason' => request('comment'),
                        'docid' => $booking->docid,
                        'status' => 'R',
                    ]);

                    app(SendCommentController::class)
                        ->sendmsg(
                            $booking->docid,
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

        $docUrl = url('/showbookingcar/' . $eid);

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
                    request()->merge([
                        'reason' => request('comment'),
                        'docid' => $booking->docid,
                        'status' => 'D',
                    ]);

                    app(SendCommentController::class)
                        ->sendmsg(
                            $booking->docid,
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

            $id = Hashids::decode($hash)[0] ?? null;

            abort_if(!$id, 404);



            $booking = TrBookingCar::with('routes')
                ->findOrFail($id);

            $getName = function (?string $username) {
                if (!$username) {
                    return null;
                }

                return User::where(
                    'username',
                    $username
                )->value('name') ?? $username;
            };

            $steps = [];

            $steps[] = [
                'key' => 'submitted',
                'status' => 'C',
                'status_label' => 'Submitted',
                'by' => $booking->createdBy?->name ?? $booking->created_by,
                'at' => optional($booking->created_at)->format('Y-m-d H:i'),
            ];
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

            foreach ($approvals as $aprv) {

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

                $steps[] = [
                    'key' => 'approval_' .
                        $aprv->aprv_leveling,

                    'title' => $aprv->aprv_name
                        ?: (
                            'Approval Level ' .
                            $aprv->aprv_leveling
                        ),

                    'status' => $aprv->status,

                    'status_label' => match ($aprv->status) {
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

            return response()->json([
                'success' => true,

                'doc' => $booking->docid,

                'steps' => $steps,

                'status' => $booking->status,

                'status_label' => match ($booking->status) {
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

        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);


        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }


        $booking = TrBookingCar::with([
            'creator:username,name',
            'routes',
        ])->findOrFail($id);


        $approvals = TrApproval::query()
            ->where('refnbr', $booking->docid)
            ->where('status', '<>', 'X')
            ->orderByRaw(
                'CAST(aprv_leveling AS INTEGER)'
            )
            ->get();


        $company = MsCompany::where(
            'cpny_id',
            $booking->cpny_id
        )->first();


        $status_doc = match ($booking->status) {
            'P' => 'On Progress',
            'C' => 'Completed',
            'R' => 'Rejected',
            'D' => 'Revise',
            'X' => 'Cancelled',
            default => '-',
        };

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

        return $pdf->stream(
            'booking_car_' .
                $booking->docid .
                '.pdf'
        );
    }
}
