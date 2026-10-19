<?php

namespace Tests\Unit;

use App\Exports\KontrakReportExport;
use App\Http\Controllers\ReportKontrakController;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\PostgresConnection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\View\Compilers\BladeCompiler;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class KontrakReportTest extends TestCase
{
    public function test_query_matches_company_and_contract_and_pivots_payment_periods(): void
    {
        $previousResolver = Model::getConnectionResolver();
        $connection = new PostgresConnection(fn () => throw new \RuntimeException('No database expected'));
        Model::setConnectionResolver(new ConnectionResolver(['pgsql' => $connection]));

        try {
            $request = new class(['year' => '2026', 'cpny_id' => 'AW', 'kontrakid' => 'KO26']) extends Request {
                public function validate(array $rules): array
                {
                    return $this->all();
                }
            };
            $request->setUserResolver(fn () => new class {
                public function hasFullDataScope(): bool
                {
                    return true;
                }
            });
            $method = new ReflectionMethod(ReportKontrakController::class, 'query');
            $method->setAccessible(true);
            $query = $method->invoke(new ReportKontrakController(), $request);
            $sql = $query->toSql();

            $this->assertStringContainsString('left join', $sql);
            $this->assertStringContainsString('"r"."kontrak_id" = "k"."kontrakid"', $sql);
            $this->assertStringContainsString('"r"."cpny_id" = "k"."cpny_id"', $sql);
            $this->assertStringContainsString('group by', $sql);
            $this->assertStringContainsString('substring(r.period from 1 for 4)', $sql);
            $this->assertStringContainsString("WHEN 'P' THEN 'On Progress'", $sql);
            $this->assertStringContainsString("WHEN 'C' THEN 'Completed'", $sql);
            for ($month = 1; $month <= 12; $month++) {
                $this->assertStringContainsString('as month_' . $month, $sql);
                $this->assertContains(sprintf('%02d', $month), $query->getBindings());
            }
            $this->assertContains('2026', $query->getBindings());
            $this->assertContains('AW', $query->getBindings());
            $this->assertContains('%KO26%', $query->getBindings());
        } finally {
            if ($previousResolver) {
                Model::setConnectionResolver($previousResolver);
            } else {
                Model::unsetConnectionResolver();
            }
        }
    }

    public function test_excel_keeps_headers_when_empty_and_preserves_text_cells(): void
    {
        $export = new KontrakReportExport(collect());
        $this->assertCount(18, $export->headings());
        $this->assertSame('Januari', $export->headings()[6]);
        $this->assertSame('Desember', $export->headings()[17]);
        $sheet = (new Spreadsheet())->getActiveSheet();
        $export->bindValue($sheet->getCell('A2'), '=1+1');
        $this->assertSame('s', $sheet->getCell('A2')->getDataType());
        $export->styles($sheet);
        $this->assertSame('G2', $sheet->getFreezePane());
    }

    public function test_report_blade_directives_compile_to_valid_php(): void
    {
        $source = file_get_contents(__DIR__ . '/../../resources/views/pages/report-kontrak/index.blade.php');
        $source = preg_replace('/<\/?x-[^>]*>/', '', $source);
        $compiler = new BladeCompiler(new Filesystem(), sys_get_temp_dir());
        $tokens = token_get_all($compiler->compileString($source), TOKEN_PARSE);
        $this->assertNotEmpty($tokens);
    }
}
