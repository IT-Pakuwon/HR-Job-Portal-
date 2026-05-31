<?php

namespace App\Http\Controllers;

use App\Exports\MeetingOnlineExport;
use App\Exports\MeetingRoomExport;
use App\Exports\VoucherTaxiExport;
use App\Exports\BookingCarExport;
use App\Models\MsMeetingRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportGeneralGAController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX (Main Page)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $rooms = MsMeetingRoom::select('room_id', 'room_name')
            ->where('status', 'A')
            ->where('room_name', 'not ilike', '%Teams Only%')
            ->where('room_name', 'not ilike', '%Zoom Only%')
            ->orderBy('room_name')
            ->get();

        $users = User::query()
            ->where('status', 'A')
            ->orderBy('name')
            ->get();

        $drivers = DB::connection('pgsql5')
            ->table('ms_driver_opr')
            ->select('drivername')
            ->whereNotNull('drivername')
            ->orderBy('drivername')
            ->get();

        $kendaraan = DB::connection('pgsql5')
            ->table('ms_kendaraan_opr')
            ->select('nopol_kendaraan')
            ->whereNotNull('nopol_kendaraan')
            ->orderBy('nopol_kendaraan')
            ->get();

        $user = auth()->user();

        $hasCSACCESS = $user->hasRole('CSACCESS');
        $hasADMIN    = strtolower($user->user_role) === 'admin';
        $hasGAACCESS = $user->hasRole('GAACCESS');

        $tabCount = ($hasCSACCESS ? 1 : 0) + ($hasADMIN ? 1 : 0) + ($hasGAACCESS ? 3 : 0);

        $defaultReport = match (true) {
            $hasGAACCESS => 'operational-car',
            $hasADMIN    => 'meeting-online',
            default      => 'meeting-room',
        };

        return view('pages.report-ga.index', [

            'rooms' => $rooms,

            'users' => $users,

            'drivers' => $drivers,

            'kendaraan' => $kendaraan,

            'hasCSACCESS'   => $hasCSACCESS,

            'hasADMIN'      => $hasADMIN,

            'hasGAACCESS'   => $hasGAACCESS,

            'tabCount'      => $tabCount,

            'defaultReport' => $defaultReport,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | JSON (DataTables Source)
    |--------------------------------------------------------------------------
    */
    public function json(Request $request, $type)
    {
        switch ($type) {
            case 'meeting-room':
                return $this->meetingRoomJson($request);

            case 'meeting-online':
                return $this->meetingOnlineJson($request);

            case 'operational-car':
                return $this->operationalCarJson($request);

            case 'voucher-taxi':
                return $this->voucherTaxiJson($request);

            default:
                abort(404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */
    public function export(Request $request, $type)
    {
        switch ($type) {
            case 'meeting-room':
                return $this->exportMeetingRoom($request);

            case 'meeting-online':
                return $this->exportMeetingOnline($request);



            case 'operational-car':
                return $this->exportOperationalCar($request);

            case 'voucher-taxi':
                return $this->exportVoucherTaxi($request);

            default:
                abort(404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ================= REPORT SECTION =================
    |--------------------------------------------------------------------------
    | Keep each report isolated (clean & scalable)
    */

    private function meetingRoomJson($request)
    {
        $departments = \App\Models\MsDepartment::pluck('department_name', 'department_id');
        $users = User::pluck('name', 'username');
        $user = auth()->user();

        $companyIds = collect(
            explode(',', (string) $user->cpny_id)
        )
        ->map(fn ($x) => trim($x))
        ->filter()
        ->values()
        ->toArray();
        $query = DB::connection('pgsql5')
            ->table('tr_meeting as m')

            ->leftJoin('ms_meeting_room as r', function ($join) {
                $join->on(
                    DB::raw('r.room_id::text'),
                    '=',
                    DB::raw('m.room_id')
                );
            })

            ->leftJoin('ms_meeting_accessories as a', function ($join) {
                $join->on(DB::raw('a.acc_id::text'), '=', DB::raw("ANY(string_to_array(m.acc_id, ','))"));
            })
            ->leftJoin('tr_meeting_participant as p', function ($join) {
                $join->on('p.docid', '=', 'm.docid');
            })
            ->whereIn('m.cpny_id', $companyIds)

            // ->leftJoin('ms_department as d', 'd.department_id', '=', 'm.department_id')
            ->select([
                'm.docid',
                'm.meeting_date',
                'm.start_meeting_time',
                'm.end_meeting_time',
                'm.meeting_title',
                'm.user_peminta',
                'm.total_participant',
                'm.external_participant',
                DB::raw("
                    STRING_AGG(
                        DISTINCT CASE
                            WHEN p.external_participant = true
                            AND p.company_participant IS NOT NULL
                            AND p.company_participant <> ''
                            THEN p.company_participant
                        END,
                        ', '
                    ) as external_company
                "),
                'm.status',
                'm.department_id',
                'r.room_name',

                // 'd.department_name',

                DB::raw("STRING_AGG(DISTINCT a.acc_name, ', ') as accessories"),
            ])

            ->groupBy([
                'm.docid',
                'm.meeting_date',
                'm.start_meeting_time',
                'm.end_meeting_time',
                'm.meeting_title',
                'm.user_peminta',
                'm.total_participant',
                'm.external_participant',

                'm.status',
                'm.department_id',
                'r.room_name',
                // 'd.department_name',
            ]);

        // 🔥 EXCLUDE ONLINE ONLY
        $query->where(function ($q) {
            $q->where('r.room_name', 'not ilike', '%Teams Only%')
            ->where('r.room_name', 'not ilike', '%Zoom Only%');
        });
        // FILTER
        if ($request->date_from) {
            $query->whereDate('m.meeting_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('m.meeting_date', '<=', $request->date_to);
        }

        if ($request->room) {
            $query->where('r.room_name', $request->room);
        }

        if ($request->requester) {
            $query->where('m.user_peminta', 'ilike', "%{$request->requester}%");
        }

        if ($request->status === 'A') {
            $query->whereNotIn('m.status', ['X']);
        }

        if ($request->status === 'X') {
            $query->where('m.status', 'X');
        }
        $users = User::pluck('name', 'username');

        return DataTables::of($query)
            ->addColumn('accessories', function ($row) {
                return $row->accessories ?: '-';
            })

            ->editColumn('meeting_date', fn ($row) => $row->meeting_date
            ? Carbon::parse($row->meeting_date)->format('d-M-Y')
            : ''
            )

            ->addColumn('time', function ($row) {
                if (!$row->start_meeting_time || !$row->end_meeting_time) {
                    return '-';
                }

                return
                    Carbon::parse($row->start_meeting_time)->format('H:i')
                    .' - '.
                    Carbon::parse($row->end_meeting_time)->format('H:i');
            })

            ->addColumn('department', function ($row) use ($departments) {
                return $departments[$row->department_id] ?? '-';
            })
            ->addColumn('start_time', function ($row) {
                if (!$row->start_meeting_time) {
                    return '-';
                }

                return Carbon::parse($row->start_meeting_time)->format('H:i');
            })

            ->addColumn('end_time', function ($row) {
                if (!$row->end_meeting_time) {
                    return '-';
                }

                return Carbon::parse($row->end_meeting_time)->format('H:i');
            })

            ->addColumn('requester', fn ($row) => $users[$row->user_peminta] ?? $row->user_peminta
            )

            ->addColumn('type', fn ($row) => $row->external_participant ? 'External' : 'Internal'
            )

            ->addColumn('duration', function ($row) {
                if (!$row->start_meeting_time || !$row->end_meeting_time) {
                    return 0;
                }

                return Carbon::parse($row->start_meeting_time)
                    ->diffInMinutes(Carbon::parse($row->end_meeting_time));
            })

            ->addColumn('duration_label', function ($row) {
                if (!$row->start_meeting_time || !$row->end_meeting_time) {
                    return '-';
                }

                $minutes = Carbon::parse($row->start_meeting_time)
                    ->diffInMinutes(Carbon::parse($row->end_meeting_time));

                return round($minutes / 60, 1).' hrs';
            })

            ->addColumn('status_label', fn ($row) => match ($row->status) {
                'X' => 'Cancelled',
                // 'C' => 'Completed',
                // 'P' => 'On Progress',
                default => 'Active',
            }
            )

            ->orderColumn('meeting_date', 'm.meeting_date $1')

            ->make(true);
    }

    private function meetingOnlineJson($request)
    {
        $departments = \App\Models\MsDepartment::pluck('department_name', 'department_id');
        $users = User::pluck('name', 'username');
        $user = auth()->user();

        $companyIds = collect(
            explode(',', (string) $user->cpny_id)
        )
        ->map(fn ($x) => trim($x))
        ->filter()
        ->values()
        ->toArray();

        $query = DB::connection('pgsql5')
            ->table('tr_meeting as m')

            ->leftJoin('ms_meeting_room as r', function ($join) {
                $join->on(
                    DB::raw('r.room_id::text'),
                    '=',
                    DB::raw('m.room_id')
                );
            })

            ->leftJoin('ms_meeting_accessories as a', function ($join) {
                $join->on(
                    DB::raw('a.acc_id::text'),
                    '=',
                    DB::raw("ANY(COALESCE(string_to_array(m.acc_id, ','), ARRAY[]::text[]))")
                );
            })
            ->whereIn('m.cpny_id', $companyIds)

            ->select([
                'm.docid',
                'm.meeting_date',
                'm.start_meeting_time',
                'm.end_meeting_time',
                'm.meeting_title',
                'm.user_peminta',
                'm.total_participant',
                'm.external_participant',
                'm.status',
                'm.department_id',
                'm.zoom_id',
                'm.msteams_event_id',
                'r.room_name',

                DB::raw("STRING_AGG(DISTINCT a.acc_name, ', ') as accessories"),
            ])

            ->groupBy([
                'm.docid',
                'm.meeting_date',
                'm.start_meeting_time',
                'm.end_meeting_time',
                'm.meeting_title',
                'm.user_peminta',
                'm.total_participant',
                'm.external_participant',
                'm.status',
                'm.department_id',
                'm.zoom_id',
                'm.msteams_event_id',
                'r.room_name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | 🔥 INCLUDE ONLY ONLINE
        |--------------------------------------------------------------------------
        */
        $query->where(function ($q) {
            $q->where('r.room_name', 'ilike', '%Teams Only%')
            ->orWhere('r.room_name', 'ilike', '%Zoom Only%');
        });
        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */
        if ($request->date_from) {
            $query->whereDate('m.meeting_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('m.meeting_date', '<=', $request->date_to);
        }

        if ($request->requester) {
            $query->where('m.user_peminta', 'ilike', "%{$request->requester}%");
        }

        if ($request->status) {
            $query->where('m.status', $request->status);
        }

        if ($request->platform === 'zoom') {
            $query->whereNotNull('m.zoom_id');
        }

        if ($request->platform === 'teams') {
            $query->whereNotNull('m.msteams_event_id');
        }

        return DataTables::of($query)

            ->addColumn('accessories', fn ($row) => $row->accessories ?: '-')

            ->editColumn('meeting_date', fn ($row) => $row->meeting_date
                    ? Carbon::parse($row->meeting_date)->format('d-M-Y')
                    : ''
            )

            ->addColumn('start_time', fn ($row) => $row->start_meeting_time
                    ? Carbon::parse($row->start_meeting_time)->format('H:i')
                    : '-'
            )

            ->addColumn('end_time', fn ($row) => $row->end_meeting_time
                    ? Carbon::parse($row->end_meeting_time)->format('H:i')
                    : '-'
            )

            ->addColumn('department', fn ($row) => $departments[$row->department_id] ?? '-'
            )

            ->addColumn('requester', fn ($row) => $users[$row->user_peminta] ?? $row->user_peminta
            )

            ->addColumn('type', fn ($row) => $row->external_participant ? 'External' : 'Internal'
            )

            ->addColumn('platform', function ($row) {
                $room = strtolower($row->room_name ?? '');

                if (str_contains($room, 'teams')) {
                    return 'Teams';
                }
                if (str_contains($room, 'zoom')) {
                    return 'Zoom';
                }

                return '-';
            })
            ->addColumn('duration_label', function ($row) {
                if (!$row->start_meeting_time || !$row->end_meeting_time) {
                    return '-';
                }

                $minutes = Carbon::parse($row->start_meeting_time)
                    ->diffInMinutes(Carbon::parse($row->end_meeting_time));

                return round($minutes / 60, 1).' hrs';
            })

            ->addColumn('status_label', fn ($row) => match ($row->status) {
                'X' => 'Cancelled',
                default => 'Active',
            })

            ->make(true);
    }

    private function operationalCarJson(Request $request)
    {
        $departments = \App\Models\MsDepartment::pluck(
            'department_name',
            'department_id'
        );

        $users = User::pluck('name', 'username');

        $user = auth()->user();

        $companyIds = collect(
            explode(',', (string) $user->cpny_id)
        )
        ->map(fn ($x) => trim($x))
        ->filter()
        ->values()
        ->toArray();

        $query = DB::connection('pgsql5')
            ->table('tr_booking_car as bc')

            ->whereIn('bc.cpny_id', $companyIds)
            ->leftJoin(
                'tr_booking_car_detail as bcd',
                'bcd.docid',
                '=',
                'bc.docid'
            )

            ->select([
                'bc.docid',

                'bc.booking_date',

                'bc.cpny_id',

                'bc.department_id',

                'bc.user_peminta',

                'bc.cpny_id_site',

                'bc.purpose_id',

                'bc.purpose_descr',

                'bc.start_time',

                'bc.end_time',

                // 'bc.location_from',

                // 'bc.destination',

                'bc.user_request',

                'bc.driver',

                'bc.handphone',

                'bc.no_polisi',

                'bc.passenger',

                'bc.checked_by',

                'bc.checked_at',

                'bc.status',

                'bc.created_by',

                'bc.created_at',

                'bcd.booking_order',
                'bcd.origin',
                'bcd.destination',
            ]);

        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->date_from) {

            $query->whereDate(
                'bc.booking_date',
                '>=',
                $request->date_from
            );
        }

        if ($request->date_to) {

            $query->whereDate(
                'bc.booking_date',
                '<=',
                $request->date_to
            );
        }

        if ($request->requester) {

            $query->where(
                'bc.user_peminta',
                'ilike',
                "%{$request->requester}%"
            );
        }

        if ($request->status) {

            $query->where(
                'bc.status',
                $request->status
            );
        }

        if ($request->driver) {

            $query->where(
                'bc.driver',
                $request->driver
            );
        }

        if ($request->vehicle) {

            $query->where(
                'bc.no_polisi',
                $request->vehicle
            );
        }
        /*
        |--------------------------------------------------------------------------
        | DATATABLE
        |--------------------------------------------------------------------------
        */

        return DataTables::of($query)

            ->editColumn('booking_date', function ($row) {
                return $row->booking_date
                    ? Carbon::parse($row->booking_date)
                        ->format('d-M-Y')
                    : '-';
            })

            ->editColumn('start_time', function ($row) {
                return $row->start_time
                    ? Carbon::parse($row->start_time)
                        ->format('H:i')
                    : '-';
            })

            ->editColumn('end_time', function ($row) {
                return $row->end_time
                    ? Carbon::parse($row->end_time)
                        ->format('H:i')
                    : '-';
            })

            ->editColumn('driver', function ($row) {
                return $row->driver ?: '-';
            })

            ->editColumn('no_polisi', function ($row) {
                return $row->no_polisi ?: '-';
            })

            ->addColumn('requester', function ($row) use ($users) {
                return $users[$row->user_peminta]
                    ?? $row->user_peminta;
            })

            ->addColumn('department', function ($row) use ($departments) {
                return $departments[$row->department_id]
                    ?? '-';
            })

            ->addColumn('route', function ($row) {

                $origin = $row->origin ?: '-';

                $destination = $row->destination ?: '-';

                return $origin . ' → ' . $destination;
            })

            ->addColumn('duration_label', function ($row) {

                if (!$row->start_time || !$row->end_time) {
                    return '-';
                }

                $minutes = Carbon::parse($row->start_time)
                    ->diffInMinutes(
                        Carbon::parse($row->end_time)
                    );

                return round($minutes / 60, 1).' hrs';
            })

            ->addColumn('status_label', function ($row) {

                return match ($row->status) {

                    'P' => 'On Progress',

                    'C' => 'Completed',

                    'R' => 'Rejected',

                    'D' => 'Revise',

                    'X' => 'Cancelled',

                    default => '-',
                };
            })

            ->orderColumn(
                'booking_date',
                'bc.booking_date $1'
            )

            ->rawColumns([
                'route',
            ])

            ->make(true);
    }

    private function voucherTaxiJson(Request $request)
    {
        $departments = \App\Models\MsDepartment::pluck(
            'department_name',
            'department_id'
        );

        $companies = \App\Models\MsCompany::pluck(
            'cpny_name',
            'cpny_id'
        );

        $users = User::pluck(
            'name',
            'username'
        );

        $user = auth()->user();

        $companyIds = collect(
            explode(',', (string) $user->cpny_id)
        )
            ->map(fn($x) => trim($x))
            ->filter()
            ->values()
            ->toArray();

        $query = DB::connection('pgsql5')
            ->table('tr_voucher_taxi as vt')
            ->whereIn('vt.cpny_id', $companyIds)
            ->select([
                'vt.docid',

                'vt.voucher_date',

                'vt.created_by',

                'vt.user_peminta_expense',
                'vt.department_id_expense',
                'vt.cpny_id_expense',

                'vt.origin',
                'vt.destination',

                'vt.purpose_descr',

                'vt.type_trip',

                'vt.actual_budget',

                'vt.status',

                'vt.created_at',
            ]);

        if ($request->date_from) {
            $query->whereDate(
                'vt.voucher_date',
                '>=',
                $request->date_from
            );
        }

        if ($request->date_to) {
            $query->whereDate(
                'vt.voucher_date',
                '<=',
                $request->date_to
            );
        }

        if ($request->requester) {
            $query->where(
                'vt.user_peminta_expense',
                'ilike',
                "%{$request->requester}%"
            );
        }

        if ($request->status) {
            $query->where(
                'vt.status',
                $request->status
            );
        }

        if ($request->type_trip) {
            $query->where(
                'vt.type_trip',
                $request->type_trip
            );
        }

        return DataTables::of($query)

            ->editColumn('voucher_date', function ($row) {
                return $row->voucher_date
                    ? Carbon::parse($row->voucher_date)
                        ->format('d-M-Y')
                    : '-';
            })

            ->editColumn('created_by', function ($row) use ($users) {
                return $users[$row->created_by]
                    ?? $row->created_by;
            })

            ->addColumn('requester', function ($row) use ($users) {
                return $users[$row->user_peminta_expense]
                    ?? $row->user_peminta_expense;
            })

            ->addColumn('department', function ($row) use ($departments) {
                return $departments[$row->department_id_expense]
                    ?? '-';
            })

            ->addColumn('company', function ($row) use ($companies) {
                return $companies[$row->cpny_id_expense]
                    ?? '-';
            })

            ->addColumn('origin_label', function ($row) {

                if (is_array($row->origin)) {
                    return implode('<br>', $row->origin);
                }

                $decoded = json_decode(
                    $row->origin,
                    true
                );

                if (is_array($decoded)) {
                    return implode('<br>', $decoded);
                }

                return $row->origin ?: '-';
            })

            ->addColumn('destination_label', function ($row) {

                if (is_array($row->destination)) {
                    return implode('<br>', $row->destination);
                }

                $decoded = json_decode(
                    $row->destination,
                    true
                );

                if (is_array($decoded)) {
                    return implode('<br>', $decoded);
                }

                return $row->destination ?: '-';
            })

            ->addColumn('purpose', function ($row) {
                return $row->purpose_descr ?: '-';
            })

            ->addColumn('trip_label', function ($row) {
                return match ($row->type_trip) {
                    'ONEWAY' => 'One Way',
                    'ROUNDTRIP' => 'Round Trip',
                    default => $row->type_trip ?? '-',
                };
            })

            ->editColumn('actual_budget', function ($row) {
                return $row->actual_budget
                    ? 'Rp ' . number_format(
                        $row->actual_budget,
                        0,
                        ',',
                        '.'
                    )
                    : '-';
            })

            ->addColumn('status_label', function ($row) {
                return match ($row->status) {
                    'P' => 'On Progress',
                    'C' => 'Completed',
                    'R' => 'Rejected',
                    'D' => 'Revise',
                    'X' => 'Cancelled',
                    default => '-',
                };
            })

            ->orderColumn(
                'voucher_date',
                'vt.voucher_date $1'
            )

            ->rawColumns([
                'origin_label',
                'destination_label',
            ])

            ->make(true);
    }
    /*
    |--------------------------------------------------------------------------
    | EXPORT SECTION
    |--------------------------------------------------------------------------
    */

    private function exportMeetingRoom(Request $request)
    {
        if ($request->format === 'pdf') {
            return $this->exportMeetingRoomPdf($request);
        }

        return Excel::download(
            new MeetingRoomExport($request),
            'meeting-room-report.xlsx'
        );
    }

    private function exportMeetingRoomPdf(Request $request)
    {
        $user = auth()->user();

        $companyIds = collect(
            explode(',', (string) $user->cpny_id)
        )
        ->map(fn ($x) => trim($x))
        ->filter()
        ->values()
        ->toArray();

        $data = DB::connection('pgsql5')
            ->table('tr_meeting as m')

            ->whereIn('m.cpny_id', $companyIds)

            ->leftJoin('ms_meeting_room as r', function ($join) {
                $join->on(
                    DB::raw('r.room_id::text'),
                    '=',
                    DB::raw('m.room_id')
                );
            })

            ->leftJoin('tr_meeting_participant as p', function ($join) {
                $join->on('p.docid', '=', 'm.docid');
            })

            ->leftJoin('ms_meeting_accessories as a', function ($join) {
                $join->on(
                    DB::raw('a.acc_id::text'),
                    '=',
                    DB::raw("ANY(string_to_array(m.acc_id, ','))")
                );
            })

            ->select([
                'm.docid',
                'm.meeting_date',
                'm.start_meeting_time',
                'm.end_meeting_time',
                'm.meeting_title',
                'm.user_peminta',
                'm.total_participant',
                'm.external_participant',
                'm.department_id',
                'm.status',
                'r.room_name',

                DB::raw("
                    STRING_AGG(
                        DISTINCT CASE
                            WHEN p.external_participant = true
                            AND p.company_participant IS NOT NULL
                            AND p.company_participant <> ''
                            THEN p.company_participant
                        END,
                        ', '
                    ) as external_company
                "),

                DB::raw("
                    STRING_AGG(
                        DISTINCT a.acc_name,
                        ', '
                    ) as accessories
                "),
            ])

            ->groupBy([
                'm.docid',
                'm.meeting_date',
                'm.start_meeting_time',
                'm.end_meeting_time',
                'm.meeting_title',
                'm.user_peminta',
                'm.total_participant',
                'm.external_participant',
                'm.department_id',
                'm.status',
                'r.room_name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | EXCLUDE ONLINE ONLY
        |--------------------------------------------------------------------------
        */
        $data->where(function ($q) {
            $q->where('r.room_name', 'not ilike', '%Teams Only%')
            ->where('r.room_name', 'not ilike', '%Zoom Only%');
        });

        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */
        if ($request->date_from) {
            $data->whereDate(
                'm.meeting_date',
                '>=',
                $request->date_from
            );
        }

        if ($request->date_to) {
            $data->whereDate(
                'm.meeting_date',
                '<=',
                $request->date_to
            );
        }

        if ($request->room) {
            $data->where(
                'r.room_name',
                $request->room
            );
        }

        if ($request->requester) {
            $data->where(
                'm.user_peminta',
                'ilike',
                "%{$request->requester}%"
            );
        }

        if ($request->status === 'A') {
            $data->whereNotIn('m.status', ['X']);
        }

        if ($request->status === 'X') {
            $data->where('m.status', 'X');
        }

        $data = $data
            ->orderBy('m.meeting_date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DEPARTMENT MAPPING
        |--------------------------------------------------------------------------
        */
        $departments = \App\Models\MsDepartment::pluck(
            'department_name',
            'department_id'
        );

        $users = User::pluck('name', 'username');

        $data->transform(function ($row) use ($departments, $users) {

            $row->department_name =
                $departments[$row->department_id] ?? '-';

            $row->requester =
                $users[$row->user_peminta] ?? $row->user_peminta;

            $row->duration_label = '-';

            if ($row->start_meeting_time && $row->end_meeting_time) {

                $minutes = Carbon::parse($row->start_meeting_time)
                    ->diffInMinutes(
                        Carbon::parse($row->end_meeting_time)
                    );

                $row->duration_label =
                    round($minutes / 60, 1).' hrs';
            }

            $row->status_label =
                $row->status === 'X'
                    ? 'Cancelled'
                    : 'Active';

            return $row;
        });

        $pdf = Pdf::loadView(
            'pages.report-ga.pdf-meetingroom',
            compact('data')
        )->setPaper('a4', 'landscape');

        return $pdf->download('meeting-room-report.pdf');
    }

    private function exportMeetingOnline(Request $request)
    {
        return Excel::download(
            new MeetingOnlineExport($request),
            'meeting-online-report.xlsx'
        );
    }

    private function exportOperationalCar(Request $request)
    {
        return Excel::download(
            new BookingCarExport($request),
            'booking-car-report.xlsx'
        );
    }

    private function exportVoucherTaxi(Request $request)
    {
        return Excel::download(
            new VoucherTaxiExport($request),
            'voucher-taxi-report.xlsx'
        );
    }

}
