<?php

namespace App\Exports;

use App\Models\StagingContractAgreement;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JobsExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = StagingContractAgreement::query()->whereNull('deleted_at')->where('status', 'A')->withoutContractNo();

        if ($this->request->filled('cpny_id')) {
            $query->where('cpny_id', $this->request->cpny_id);
        }

        if ($this->request->filled('tenant_no')) {
            $query->where('tenant_no', 'ilike', "%{$this->request->tenant_no}%");
        }

        if ($this->request->filled('trade_name')) {
            $query->where('trade_name', 'ilike', "%{$this->request->trade_name}%");
        }

        if ($this->request->filled('property_cd')) {
            $query->where('property_cd', 'ilike', "%{$this->request->property_cd}%");
        }

        if (is_string($this->request->search) && $this->request->filled('search')) {
            $search = $this->request->search;

            $query->where(function ($q) use ($search) {
                $q->where('contract_no', 'ilike', "%{$search}%")
                    ->orWhere('tenant_no', 'ilike', "%{$search}%")
                    ->orWhere('trade_name', 'ilike', "%{$search}%")
                    ->orWhere('property_cd', 'ilike', "%{$search}%");
            });
        }

        return $query
            ->orderBy('cpny_id')
            ->orderBy('contract_no')
            ->get([
                'cpny_id', 'contract_no', 'business_id', 'tenant_no',
                'trade_name', 'property_cd', 'status',
            ])
            ->map(function ($row) {
                return [
                    'cpny_id' => $row->cpny_id,
                    'contract_no' => $row->contract_no,
                    'business_id' => $row->business_id,
                    'tenant_no' => $row->tenant_no,
                    'trade_name' => $row->trade_name,
                    'property_cd' => $row->property_cd,
                    'status' => match ($row->status) {
                        'A' => 'Pending',
                        'C' => 'On Progress',
                        'X' => 'Cancelled',
                        default => $row->status,
                    },
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Company',
            'Contract No',
            'Business ID',
            'Tenant No',
            'Trade Name',
            'Property CD',
            'Status',
        ];
    }
}
