<?php

namespace App\Exports;

use App\Models\ViewInventoryInquiry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class InventoryInquiryExport extends DefaultValueBinder implements FromQuery, WithMapping, WithHeadings, WithColumnFormatting, WithCustomValueBinder
{
    public function __construct(private Builder $sourceQuery)
    {
    }

    public function query(): Builder
    {
        return clone $this->sourceQuery;
    }

    public function headings(): array
    {
        return ['Doc ID', 'Doc Date', 'Reference No', 'Company', 'Business Unit', 'Type', 'Qty', 'UoM', 'Unit Cost', 'Note'];
    }

    public function map($row): array
    {
        return [
            $row->docid,
            $row->docdate ? Carbon::parse($row->docdate)->format('d-M-Y') : '',
            $row->refnbr,
            $row->cpny_id,
            $row->business_unit,
            ViewInventoryInquiry::typeLabel($row->doctype),
            (float) ($row->qty ?? 0),
            $row->uom,
            (float) ($row->unitcost ?? 0),
            $row->note,
        ];
    }

    public function columnFormats(): array
    {
        return ['G' => '#,##0.000', 'I' => '#,##0.00'];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
