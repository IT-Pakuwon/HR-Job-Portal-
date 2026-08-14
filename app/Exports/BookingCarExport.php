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
            'Company',
            'Company Expense',
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

        $companies = \App\Models\MsCompany::pluck(
            'cpny_name',
            'cpny_id'
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

            ->where(function ($q) use ($companyIds) {
                $q->whereIn('bc.cpny_id', $companyIds)
                    ->orWhereIn('bc.cpny_id_site', $companyIds);
            })

            ->leftJoin(
                'tr_booking_car_detail as bcd',
                'bcd.docid',
                '=',
                'bc.docid'
            )

            ->select([
                'bc.docid',

                'bc.booking_date',

                'bc.department_id',

                'bc.cpny_id',

                'bc.cpny_id_site',

                'bc.user_peminta',

                'bc.purpose_descr',

                'bc.start_time',

                'bc.end_time',

                'bc.passenger',

                'bc.driver',

                'bc.no_polisi',

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

        if ($request->status === 'A') {
            $query->whereNotIn('bc.status', ['X']);
        }

        if ($request->status === 'X') {
            $query->where('bc.status', 'X');
        }

        if ($request->company) {
            $query->where(function ($q) use ($request) {
                $q->where('bc.cpny_id', $request->company)
                    ->orWhere('bc.cpny_id_site', $request->company);
            });
        }

        return $query
            ->orderBy('bc.booking_date', 'desc')
            ->orderBy('bcd.booking_order')
            ->get()

            ->groupBy('docid')

            ->map(function ($group) use (
                $users,
                $departments,
                $companies
            ) {

                $row = $group->first();

                /*
                |--------------------------------------------------------------------------
                | ROUTE FORMAT
                |--------------------------------------------------------------------------
                */

                $routes = $group
                    ->map(function ($detail) {

                        if (!$detail->origin && !$detail->destination) {
                            return null;
                        }

                        return ($detail->origin ?: '-').' → '.($detail->destination ?: '-');
                    })
                    ->filter()
                    ->values()
                    ->all();

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

                    'company' => $companies[$row->cpny_id]
                        ?? '-',

                    'company_expense' => $companies[$row->cpny_id_site]
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

                        'F' => 'Processed',

                        'U' => 'Unprocessed',

                        default => '-',
                    },

                    'created_by' => $row->created_by
                        ?: '-',

                    'created_at' => $row->created_at
                        ? Carbon::parse($row->created_at)
                            ->format('d-M-Y H:i')
                        : '-',
                ];
            })

            ->values();
    }
}
