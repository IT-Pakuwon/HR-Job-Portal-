<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BookingCarExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function headings(): array
    {
        return [
            'Doc ID',
            'Booking Date',
            'Start Time',
            'End Time',
            'Requester',
            'Department',
            'Purpose',
            'Route',
            'Passenger',
            'Driver',
            'Vehicle',
            'Duration',
            'Status',
            'Created By',
            'Created At',
        ];
    }

    public function collection()
    {
        $request = $this->request;

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

            ->select([
                'bc.docid',

                'bc.booking_date',

                'bc.department_id',

                'bc.user_peminta',

                'bc.purpose_descr',

                'bc.location_from',

                'bc.destination',

                'bc.start_time',

                'bc.end_time',

                'bc.passenger',

                'bc.driver',

                'bc.no_polisi',

                'bc.status',

                'bc.created_by',

                'bc.created_at',
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

        if ($request->status === 'A') {
            $query->whereNotIn('bc.status', ['X']);
        }

        if ($request->status === 'X') {
            $query->where('bc.status', 'X');
        }

        return $query
            ->orderBy('bc.booking_date', 'desc')
            ->get()

            ->map(function ($row) use (
                $users,
                $departments
            ) {

                /*
                |--------------------------------------------------------------------------
                | ROUTE FORMAT
                |--------------------------------------------------------------------------
                */

                $origins = [];

                if (is_array($row->location_from)) {

                    $origins = $row->location_from;

                } elseif (!empty($row->location_from)) {

                    $decoded = json_decode(
                        $row->location_from,
                        true
                    );

                    $origins = is_array($decoded)
                        ? $decoded
                        : [$row->location_from];
                }

                $destinations = [];

                if (is_array($row->destination)) {

                    $destinations = $row->destination;

                } elseif (!empty($row->destination)) {

                    $decoded = json_decode(
                        $row->destination,
                        true
                    );

                    $destinations = is_array($decoded)
                        ? $decoded
                        : [$row->destination];
                }

                $routes = [];

                foreach ($origins as $i => $from) {

                    $to = $destinations[$i] ?? '-';

                    $routes[] = $from.' → '.$to;
                }

                /*
                |--------------------------------------------------------------------------
                | DURATION
                |--------------------------------------------------------------------------
                */

                $duration = '-';

                if ($row->start_time && $row->end_time) {

                    $minutes = Carbon::parse($row->start_time)
                        ->diffInMinutes(
                            Carbon::parse($row->end_time)
                        );

                    $duration = round(
                        $minutes / 60,
                        1
                    ).' hrs';
                }

                /*
                |--------------------------------------------------------------------------
                | RETURN
                |--------------------------------------------------------------------------
                */

                return [
                    'docid' => $row->docid,

                    'booking_date' => $row->booking_date
                        ? Carbon::parse($row->booking_date)
                            ->format('d-M-Y')
                        : '-',

                    'start_time' => $row->start_time
                        ? Carbon::parse($row->start_time)
                            ->format('H:i')
                        : '-',

                    'end_time' => $row->end_time
                        ? Carbon::parse($row->end_time)
                            ->format('H:i')
                        : '-',

                    'requester' => $users[$row->user_peminta]
                        ?? $row->user_peminta,

                    'department' => $departments[$row->department_id]
                        ?? '-',

                    'purpose' => $row->purpose_descr
                        ?: '-',

                    'route' => count($routes)
                        ? implode(' | ', $routes)
                        : '-',

                    'passenger' => $row->passenger
                        ?: '-',

                    'driver' => $row->driver
                        ?: '-',

                    'vehicle' => $row->no_polisi
                        ?: '-',

                    'duration' => $duration,

                    'status' => match ($row->status) {

                        'P' => 'On Progress',

                        'C' => 'Completed',

                        'R' => 'Rejected',

                        'D' => 'Revise',

                        'X' => 'Cancelled',

                        default => '-',
                    },

                    'created_by' => $row->created_by
                        ?: '-',

                    'created_at' => $row->created_at
                        ? Carbon::parse($row->created_at)
                            ->format('d-M-Y H:i')
                        : '-',
                ];
            });
    }
}
