<?php

namespace App\Exports;

use App\Models\TrAgreement;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AgreementExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = TrAgreement::query();

        if ($this->request->filled('status')) {

            $query->where(
                'agreement_step_id',
                $this->request->status
            );
        }

        if ($this->request->filled('cpny_id')) {

            $query->where(
                'cpny_id',
                $this->request->cpny_id
            );
        }

        if ($this->request->filled('date_from')) {

            $query->whereDate(
                'agreement_date',
                '>=',
                $this->request->date_from
            );
        }

        if ($this->request->filled('date_to')) {

            $query->whereDate(
                'agreement_date',
                '<=',
                $this->request->date_to
            );
        }

        if ($this->request->filled('search')) {

            $search = $this->request->search;

            $query->where(function ($q) use ($search) {

                $q->where('agreement_id', 'ilike', "%{$search}%")
                    ->orWhere('business_name', 'ilike', "%{$search}%")
                    ->orWhere('trade_name', 'ilike', "%{$search}%");
            });
        }

        return $query
            ->orderByDesc('agreement_date')
            ->get([
                'agreement_id',
                'agreement_date',
                'cpny_id',
                'business_name',
                'trade_name',
                'tenant_no',
                'pic_legal',
                'pic_leasing',
                'agreement_step_id',
                'status',
                'created_user',
            ]);
    }

    public function headings(): array
    {
        return [
            'Agreement No',
            'Date',
            'Company',
            'Business Name',
            'Trade Name',
            'Tenant No',
            'PIC Legal',
            'PIC Leasing',
            'Step',
            'Status',
            'Created By',
        ];
    }
}
