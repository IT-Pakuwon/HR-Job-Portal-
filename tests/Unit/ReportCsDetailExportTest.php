<?php

namespace Tests\Unit;

use App\Exports\ReportCsDetailExport;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Query\Builder;
use OpenSpout\Reader\XLSX\Reader;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\TestCase;

class ReportCsDetailExportTest extends TestCase
{
    private function sourceQuery(int $rowCount): Builder
    {
        $connection = new class($rowCount) extends PostgresConnection {
            public array $offsets = [];

            public function __construct(private int $rowCount)
            {
                parent::__construct(fn () => throw new \RuntimeException('No production database expected'));
            }

            public function select($query, $bindings = [], $useReadPdo = true)
            {
                preg_match('/limit (\d+) offset (\d+)/', $query, $page);
                $limit = (int) $page[1];
                $offset = (int) $page[2];
                $this->offsets[] = $offset;

                return $offset >= $this->rowCount ? [] : array_map(
                    fn ($id) => (object) ['id' => $id],
                    range($offset + 1, min($offset + $limit, $this->rowCount))
                );
            }
        };

        return (new Builder($connection))->from('tr_cs_detail')->orderBy('id');
    }

    public function test_excel_preserves_text_statuses_and_numeric_formats(): void
    {
        $query = $this->sourceQuery(2);
        $export = new ReportCsDetailExport($query, fn ($row) => [
            'CS_ID' => 'CS' . $row->id, 'Purpose' => $row->id === 1 ? '= repeat order =' : '=SUM(',
            'Qty' => $row->id === 1 ? 5.0 : 0.0, 'Unit Price' => 10999.0, 'DPP' => 54995.0,
            'COA' => $row->id === 1 ? '0201' : '0010', 'BAST No' => 'BA26100027 - Completed',
        ]);
        $tempPath = tempnam(sys_get_temp_dir(), 'report_cs_text_');
        try {
            $export->writeTo($tempPath);
            $loaded = IOFactory::load($tempPath);
            try {
                $sheet = $loaded->getActiveSheet();
                $this->assertSame(3, $sheet->getHighestRow());
                $this->assertSame('= repeat order =', (string) $sheet->getCell('J2')->getValue());
                $this->assertSame('=SUM(', (string) $sheet->getCell('J3')->getValue());
                $this->assertNotSame(DataType::TYPE_FORMULA, $sheet->getCell('J2')->getDataType());
                $this->assertSame('0201', (string) $sheet->getCell('W2')->getValue());
                $this->assertSame('0010', (string) $sheet->getCell('W3')->getValue());
                $this->assertSame('BA26100027 - Completed', (string) $sheet->getCell('F2')->getValue());
                $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCell('M2')->getDataType());
                $this->assertSame(0.0, (float) $sheet->getCell('M3')->getValue());
                $this->assertSame('54,995.00', $sheet->getCell('Q2')->getFormattedValue());
            } finally {
                $loaded->disconnectWorksheets();
            }
        } finally {
            unlink($tempPath);
        }
    }

    public function test_all_10001_rows_are_written_across_21_query_chunks(): void
    {
        $query = $this->sourceQuery(10001);
        $export = new ReportCsDetailExport($query, fn ($row) => [
            'CS_ID' => 'CS' . $row->id, 'Purpose' => '= repeat order =',
            'Qty' => (float) $row->id, 'DPP' => 1000.0, 'Status' => 'Completed',
        ]);
        $tempPath = tempnam(sys_get_temp_dir(), 'report_cs_all_');
        $reader = new Reader();
        try {
            $export->writeTo($tempPath);
            $this->assertSame(range(0, 10000, 500), $query->getConnection()->offsets);
            $this->assertNull($query->limit);
            $this->assertNull($query->offset);
            $reader->open($tempPath);
            $rowCount = 0;
            $last = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $last = $row->toArray();
                    $rowCount++;
                }
            }
            $this->assertSame(10002, $rowCount);
            $this->assertCount(25, $last);
            $this->assertSame('CS10001', $last[0]);
            $this->assertSame('= repeat order =', $last[9]);
            $this->assertSame(10001.0, (float) $last[12]);
            $this->assertSame('Completed', $last[24]);
        } finally {
            $reader->close();
            unlink($tempPath);
        }
    }

    public function test_empty_report_still_contains_all_column_headings(): void
    {
        $export = new ReportCsDetailExport($this->sourceQuery(0), fn ($row) => []);
        $tempPath = tempnam(sys_get_temp_dir(), 'report_cs_empty_');
        $reader = new Reader();
        try {
            $export->writeTo($tempPath);
            $reader->open($tempPath);
            $rows = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = $row->toArray();
                }
            }
            $this->assertCount(1, $rows);
            $this->assertSame($export->headings(), $rows[0]);
        } finally {
            $reader->close();
            unlink($tempPath);
        }
    }
}
