<?php

namespace App\Exports;

use App\Models\TrVoucherTaxi;
use App\Models\User;
use App\Models\MsDepartment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VoucherTaxiExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $request = $this->request;

        $departments = MsDepartment::pluck(
            'department_name',
            'department_id'
        );

        $users = User::pluck(
            'name',
            'username'
        );

        $user = auth()->user();

        $companyIds = collect(
            explode(',', (string) $user->cpny_id)
        )
        ->map(fn ($x) => trim($x))
        ->filter()
        ->values()
        ->toArray();

        $query = TrVoucherTaxi::query()
            ->whereIn('cpny_id', $companyIds);

        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->date_from) {
            $query->whereDate(
                'voucher_date',
                '>=',
                $request->date_from
            );
        }

        if ($request->date_to) {
            $query->whereDate(
                'voucher_date',
                '<=',
                $request->date_to
            );
        }

        if ($request->requester) {
            $query->where(
                'user_peminta',
                'ilike',
                "%{$request->requester}%"
            );
        }

        if ($request->status === 'A') {
            $query->where('status', '!=', 'X');
        }

        if ($request->status === 'X') {
            $query->where('status', 'X');
        }

        $rows = $query
            ->orderByDesc('voucher_date')
            ->get();

        return $rows->map(function ($row) use (
            $departments,
            $users
        ) {

            return [
                'Document No' => $row->docid,

                'Voucher Date' => $row->voucher_date
                    ? Carbon::parse($row->voucher_date)
                        ->format('d-M-Y')
                    : '-',

                'Requester' => $users[$row->user_peminta]
                    ?? $row->user_peminta,

                'Department' => $departments[$row->department_id]
                    ?? $row->department_id,

                'Company' => $row->cpny_id,

                'Origin' => $row->origin,

                'Destination' => $row->destination,

                'Purpose' => $row->purpose,

                'Type Trip' => $row->type_trip,

                // 'Max Trip' => $row->max_trip,

                // 'Max Budget' => $row->max_budget,

                'Actual Budget' => $row->actual_budget,

                'Status' => match ($row->status) {
                    'P' => 'Pending',
                    'C' => 'Completed',
                    'R' => 'Rejected',
                    'D' => 'Revise',
                    'X' => 'Cancelled',
                    default => '-',
                },

                'Created By' => $row->created_by,

                'Created At' => $row->created_at
                    ? Carbon::parse($row->created_at)
                        ->format('d-M-Y H:i')
                    : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Document No',
            'Voucher Date',
            'Requester',
            'Department',
            'Company',
            'Origin',
            'Destination',
            'Purpose',
            'Type Trip',
            // 'Max Trip',
            // 'Max Budget',
            'Actual Budget',
            'Status',
            'Created By',
            'Created At',
        ];
    }
}
