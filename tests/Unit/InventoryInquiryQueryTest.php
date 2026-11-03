<?php

namespace Tests\Unit;

use App\Http\Controllers\ReportWarehouseController;
use App\Models\ViewInventoryInquiry;
use App\Exports\InventoryInquiryExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\PostgresConnection;
use Illuminate\Http\Request;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class InventoryInquiryQueryTest extends TestCase
{
    private $previousResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousResolver = Model::getConnectionResolver();
        Model::setConnectionResolver(new ConnectionResolver([
            'pgsql' => new PostgresConnection(function () {
                throw new \RuntimeException('Tests must not access the live database.');
            }),
        ]));
    }

    protected function tearDown(): void
    {
        if ($this->previousResolver) {
            Model::setConnectionResolver($this->previousResolver);
        } else {
            Model::unsetConnectionResolver();
        }
        parent::tearDown();
    }

    private function query(array $filters = [], array $companies = ['AW', 'EP'])
    {
        $method = new ReflectionMethod(ReportWarehouseController::class, 'inventoryInquiryQuery');
        $method->setAccessible(true);
        return $method->invoke(new ReportWarehouseController(), new Request($filters), $companies);
    }

    public function test_model_reads_the_view_without_timestamps_or_incrementing_key(): void
    {
        $model = new ViewInventoryInquiry();
        $this->assertSame('pgsql', $model->getConnectionName());
        $this->assertSame('v_inventory_inquiry', $model->getTable());
        $this->assertFalse($model->usesTimestamps());
        $this->assertFalse($model->getIncrementing());
    }

    public function test_query_selects_requested_columns_and_enforces_company_scope(): void
    {
        $query = $this->query();
        $this->assertSame(['AW', 'EP'], $query->getBindings());
        $this->assertSame(['docid', 'docdate', 'refnbr', 'cpny_id', 'business_unit', 'doctype', 'qty', 'uom', 'unitcost', 'note'], $query->getQuery()->columns);
        $this->assertStringContainsString('"cpny_id" in (?, ?)', $query->toSql());
    }

    public function test_all_five_filters_are_applied_without_replacing_access_scope(): void
    {
        $query = $this->query([
            'date_from' => '2026-11-01', 'date_to' => '2026-11-03',
            'inventoryid' => 'PANLOG1041', 'cpny_id' => 'GPS', 'business_unit' => 'GPSMALL',
        ]);
        $this->assertSame(['AW', 'EP', '2026-11-01', '2026-11-03', 'PANLOG1041', 'GPS', 'GPSMALL'], $query->getBindings());
        $this->assertStringContainsString('"docdate"::date >= ?', $query->toSql());
        $this->assertStringContainsString('"docdate"::date <= ?', $query->toSql());
        $this->assertStringContainsString('"cpny_id" in (?, ?) and', $query->toSql());
    }

    public function test_empty_company_scope_cannot_return_other_company_data(): void
    {
        $this->assertStringContainsString('0 = 1', $this->query([], [])->toSql());
    }

    public function test_blank_filters_are_ignored_and_end_date_can_be_used_alone(): void
    {
        $query = $this->query(['date_from' => '', 'date_to' => '2026-11-03', 'inventoryid' => '', 'cpny_id' => '', 'business_unit' => '']);
        $this->assertSame(['AW', 'EP', '2026-11-03'], $query->getBindings());
        $this->assertStringNotContainsString('"inventoryid" =', $query->toSql());
    }

    public function test_inquiry_blade_compiles_to_valid_php(): void
    {
        $compiler = new BladeCompiler(new Filesystem(), sys_get_temp_dir());
        $compiled = $compiler->compileString(file_get_contents(__DIR__.'/../../resources/views/pages/report-warehouse/inventory-inquiry.blade.php'));
        $this->assertNotEmpty(token_get_all($compiled, TOKEN_PARSE));
    }

    public function test_type_labels_match_requested_names_and_keep_unknown_codes(): void
    {
        foreach (['RR' => 'Return Purchase Receipt', 'PR' => 'Purchase Receipt', 'IS' => 'Issue', 'RI' => 'Return Issue', 'OTHER' => 'OTHER'] as $code => $label) {
            $this->assertSame($label, ViewInventoryInquiry::typeLabel($code));
        }
    }

    public function test_export_maps_all_table_columns_and_keeps_numeric_values(): void
    {
        $export = new InventoryInquiryExport($this->query(['cpny_id' => 'AW']));
        $mapped = $export->map((object) [
            'docid' => 'IS26110028', 'docdate' => '2026-11-03', 'refnbr' => '0010',
            'cpny_id' => 'AW', 'business_unit' => 'AWMALL', 'doctype' => 'RI',
            'qty' => '1.000', 'uom' => 'PAK', 'unitcost' => '23465.67', 'note' => '= repeat order =',
        ]);
        $this->assertSame(['IS26110028', '03-Nov-2026', '0010', 'AW', 'AWMALL', 'Return Issue', 1.0, 'PAK', 23465.67, '= repeat order ='], $mapped);
        $this->assertCount(10, $export->headings());
        $this->assertSame(['AW', 'EP', 'AW'], $export->query()->getBindings());
        $this->assertNull($export->query()->getQuery()->limit);
        $this->assertSame(['G' => '#,##0.000', 'I' => '#,##0.00'], $export->columnFormats());

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $export->bindValue($sheet->getCell('C2'), $mapped[2]);
        $export->bindValue($sheet->getCell('J2'), $mapped[9]);
        $export->bindValue($sheet->getCell('G2'), $mapped[6]);
        $this->assertSame('0010', $sheet->getCell('C2')->getValue());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('J2')->getDataType());
        $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCell('G2')->getDataType());
        $spreadsheet->disconnectWorksheets();
    }
}
