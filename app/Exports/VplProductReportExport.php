<?php

namespace App\Exports;

use App\Exports\Concerns\PrettifiesSheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VplProductReportExport implements FromView, WithEvents
{
    use PrettifiesSheet;

    /** Column the blade table renders "Image Product" into. */
    private const PHOTO_COLUMN = 'C';

    public function __construct(
        private array $groups,
        private string $cpnyid,
        private int $year,
        private int $month
    ) {
    }

    public function view(): View
    {
        return view('pages.report-vpl.partials.product-report-table', [
            'groups'    => $this->groups,
            'cpnyid'    => $this->cpnyid,
            'year'      => $this->year,
            'month'     => $this->month,
            'forExport' => true,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->drawPhotos($event->sheet->getDelegate());
                $this->prettifySheet($event->sheet->getDelegate(), 1, '0284C7');
            },
        ];
    }

    /**
     * Places each tenant's photo as a real PhpSpreadsheet drawing instead of relying
     * on the blade's <img> tag — the HTML-to-XLSX reader can't turn a data: URI <img>
     * into something the Xlsx writer can save (see photoBytes() on the controller for
     * why), so the blade renders the "Image Product" cell empty for export and this
     * fills it in afterwards. Row numbers are derived by walking $this->groups in the
     * exact same order/branching the blade template uses to emit <tr> rows, since
     * that's what determines which spreadsheet row each tenant block lands on.
     */
    private function drawPhotos(Worksheet $sheet): void
    {
        $row = 1; // header row

        foreach ($this->groups as $group) {
            if ($group['is_first_of_category']) {
                $row++;
            }

            $rowsCount = max(1, count($group['rows']));

            for ($i = 0; $i < $rowsCount; $i++) {
                $row++;

                if ($i === 0 && $group['is_first_of_tenant'] && $group['photo']) {
                    $this->drawPhoto($sheet, $group['photo'], $group['tenant'], $row);
                }
            }
        }
    }

    private function drawPhoto(Worksheet $sheet, array $photo, string $tenant, int $row): void
    {
        $gd = @imagecreatefromstring($photo['bytes']);

        if ($gd === false) {
            return;
        }

        [$renderingFunction, $mimeType] = match ($photo['extension']) {
            'png'   => [MemoryDrawing::RENDERING_PNG, MemoryDrawing::MIMETYPE_PNG],
            'gif'   => [MemoryDrawing::RENDERING_GIF, MemoryDrawing::MIMETYPE_GIF],
            default => [MemoryDrawing::RENDERING_JPEG, MemoryDrawing::MIMETYPE_JPEG],
        };

        $drawing = new MemoryDrawing();
        $drawing->setName($tenant);
        $drawing->setImageResource($gd);
        $drawing->setRenderingFunction($renderingFunction);
        $drawing->setMimeType($mimeType);
        $drawing->setResizeProportional(true);
        $drawing->setHeight(48);
        $drawing->setOffsetX(4);
        $drawing->setOffsetY(4);
        $drawing->setCoordinates(self::PHOTO_COLUMN.$row);
        $drawing->setWorksheet($sheet);

        $sheet->getRowDimension($row)->setRowHeight(40);
    }
}
