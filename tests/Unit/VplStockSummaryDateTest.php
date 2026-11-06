<?php

namespace Tests\Unit;

use App\Http\Controllers\VplReportController;
use Illuminate\Support\Carbon;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class VplStockSummaryDateTest extends TestCase
{
    private $previousApplication;
    private $previousResolver;
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApplication = Facade::getFacadeApplication();
        $this->previousResolver = Model::getConnectionResolver();
        $this->connection = new class extends PostgresConnection {
            public array $queries = [];
            public array $results = [];

            public function __construct()
            {
                parent::__construct(function () { throw new \RuntimeException('No live database access.'); });
            }

            public function select($query, $bindings = [], $useReadPdo = true)
            {
                $this->queries[] = ['sql' => $query, 'bindings' => $bindings];
                return array_shift($this->results) ?? [];
            }
        };
        $manager = new class($this->connection) {
            public function __construct(private $db) {}
            public function connection($name) { return $this->db; }
            public function raw($sql) { return new Expression($sql); }
        };
        $container = new Container();
        $container->instance('db', $manager);
        Facade::clearResolvedInstance('db');
        Facade::setFacadeApplication($container);
        Model::setConnectionResolver(new ConnectionResolver(['pgsql5' => $this->connection]));
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstance('db');
        Facade::setFacadeApplication($this->previousApplication);
        if ($this->previousResolver) Model::setConnectionResolver($this->previousResolver);
        else Model::unsetConnectionResolver();
        parent::tearDown();
    }

    private function invoke(string $method, array $arguments)
    {
        $reflection = new ReflectionMethod(VplReportController::class, $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs(new VplReportController(), $arguments);
    }

    public function test_summary_usage_filter_uses_conditional_date_but_settlement_keeps_own_date(): void
    {
        $this->invoke('batchPurposeOut', ['EP', Carbon::parse('2026-11-01'), Carbon::parse('2026-11-30')->endOfDay(), true]);
        $this->assertStringContainsString("CASE WHEN tr_vpl_usage.department = 'CUSTOMERSERVICE' THEN tr_vpl_usage.usage_date ELSE tr_vpl_usage.completed_at END between", $this->connection->queries[0]['sql']);
        $this->assertStringContainsString('"tr_vpl_settlement"."settlement_date" between', $this->connection->queries[1]['sql']);
    }

    public function test_other_report_using_shared_purpose_query_keeps_usage_date(): void
    {
        $this->invoke('batchPurposeOut', ['EP', Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'), false]);
        $this->assertStringContainsString('tr_vpl_usage.usage_date between', $this->connection->queries[0]['sql']);
        $this->assertStringNotContainsString('completed_at', $this->connection->queries[0]['sql']);
    }

    public function test_summary_stock_groups_by_effective_period_instead_of_original_posting_month(): void
    {
        $base = ['product_id' => 'V20039', 'expired_date' => '2026-12-31', 'whs_id' => 'WHPROMOTION', 'transaction_source' => 'Usage', 'perpost' => '202610', 'qty' => -25, 'doc_department' => 'PROMOTION'];
        $this->connection->results = [[
            (object) array_replace($base, ['report_perpost' => '202611']),
            (object) array_replace($base, ['report_perpost' => '202610', 'doc_department' => 'CUSTOMERSERVICE', 'qty' => -10]),
        ]];
        [$in, $out] = $this->invoke('ledgerMonthlyInOut', ['EP', 2026, true]);
        $this->assertSame(25.0, $out['V20039|2026-12-31'][11]);
        $this->assertSame(10.0, $out['V20039|2026-12-31'][10]);
        $sql = $this->connection->queries[0]['sql'];
        $this->assertStringContainsString("l.transaction_source IN ('Usage', 'Return')", $sql);
        $this->assertStringContainsString("THEN u.usage_date ELSE u.completed_at END, 'YYYYMM') ELSE l.perpost END LIKE ?", $sql);
        $this->assertContains('2026%', $this->connection->queries[0]['bindings']);
    }

    public function test_stock_voucher_ledger_keeps_original_posting_period(): void
    {
        $this->invoke('ledgerMonthlyInOut', ['EP', 2026, false]);
        $this->assertStringContainsString('l.perpost LIKE ?', $this->connection->queries[0]['sql']);
        $this->assertStringNotContainsString('completed_at', $this->connection->queries[0]['sql']);
    }
}
