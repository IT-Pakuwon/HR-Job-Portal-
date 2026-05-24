<?php

namespace App\Exports;

use App\Models\TrVoucherTaxi;
use App\Models\User;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use Carbon\Carbon;
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

        $companies = MsCompany::pluck(
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
            ->map(fn ($x) => trim($x))
            ->filter()
            ->values()
            ->toArray();

        $query = TrVoucherTaxi::query()
            ->whereIn('cpny_id', $companyIds);

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
                'user_peminta_expense',
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
            $companies,
            $users
        ) {

            return [

                'DOC ID' => $row->docid,

                'DATE' => $row->voucher_date
                    ? Carbon::parse($row->voucher_date)
                        ->format('d-M-Y')
                    : '-',

                'CREATED USER' => $users[$row->created_by]
                    ?? $row->created_by,

                'REQUESTER' => $users[$row->user_peminta_expense]
                    ?? $row->user_peminta_expense,

                'DEPARTMENT' => $departments[$row->department_id_expense]
                    ?? $row->department_id_expense,

                'COMPANY' => $companies[$row->cpny_id_expense]
                    ?? $row->cpny_id_expense,

                'ORIGIN' => is_array($row->origin)
                    ? implode(', ', $row->origin)
                    : $row->origin,

                'DESTINATION' => is_array($row->destination)
                    ? implode(', ', $row->destination)
                    : $row->destination,

                'PURPOSE' => $row->purpose_descr,

                'TYPE TRIP' => match ($row->type_trip) {
                    'ONEWAY' => 'One Way',
                    'ROUNDTRIP' => 'Round Trip',
                    default => $row->type_trip,
                },

                'ACTUAL BUDGET' => $row->actual_budget,

                'STATUS' => match ($row->status) {
                    'P' => 'On Progress',
                    'C' => 'Completed',
                    'R' => 'Rejected',
                    'D' => 'Revise',
                    'X' => 'Cancelled',
                    default => $row->status,
                },
            ];
        });
    }

    public function headings(): array
    {
        return [
            'DOC ID',
            'DATE',
            'CREATED USER',
            'REQUESTER',
            'DEPARTMENT',
            'COMPANY',
            'ORIGIN',
            'DESTINATION',
            'PURPOSE',
            'TYPE TRIP',
            'ACTUAL BUDGET',
            'STATUS',
        ];
    }
}
