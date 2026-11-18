<?php

namespace Tests\Unit;

use App\Http\Controllers\LastOrderController;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\PostgresConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class LastOrderBqTest extends TestCase
{
    private $previousContainer;
    private $previousFacade;
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousContainer = Container::getInstance();
        $this->previousFacade = Facade::getFacadeApplication();
        $this->connection = new class extends PostgresConnection {
            public array $queries = [];
            public array $results = [];

            public function __construct()
            {
                parent::__construct(fn () => throw new \RuntimeException('No live database access.'));
            }

            public function select($query, $bindings = [], $useReadPdo = true)
            {
                $this->queries[] = ['sql' => $query, 'bindings' => $bindings];
                return array_shift($this->results) ?? [];
            }
        };
        $connection = $this->connection;
        $container = new Container();
        $container->instance('db', new class($connection) {
            public function __construct(private $connection) {}
            public function connection($name) { return $this->connection; }
        });
        $container->instance('hashids', new class {
            public function encode($id) { return 'encoded'.$id; }
        });
        $container->instance(ResponseFactory::class, new class {
            public function json($data) { return new JsonResponse($data); }
        });
        Container::setInstance($container);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousFacade);
        Container::setInstance($this->previousContainer);
        parent::tearDown();
    }

    private function row(): object
    {
        return (object) [
            'bqid' => 'BQ26110086', 'csid' => 'CS26110376', 'cpny_id' => 'AW',
            'ponbr' => '1000000123', 'podate' => '2026-11-18',
            'po_id' => 12, 'bq_id' => 34, 'cs_id' => 56, 'vendor_id' => 'V001',
            'vendor_name' => 'Vendor', 'bq_descr' => 'Item', 'product_price' => 100,
            'jasa_price' => 0, 'total_price' => 100,
        ];
    }

    public function test_query_scopes_selected_vendor_po_by_cs_and_company_without_reuse_cs(): void
    {
        $this->connection->results = [[(object) ['aggregate' => 1]], [$this->row()]];
        $result = (new LastOrderController())->bqJson(new Request())->getData(true);
        $sql = $this->connection->queries[1]['sql'];

        $this->assertStringNotContainsString('join lateral', $sql);
        $this->assertStringContainsString('DISTINCT ON (po.csid, po.cpny_id, po.vendorid)', $sql);
        $this->assertStringContainsString('"selected_po"."csid" = "bq"."csid"', $sql);
        $this->assertStringContainsString('"selected_po"."cpny_id" = "cs"."cpny_id"', $sql);
        $this->assertStringContainsString('"selected_po"."vendorid" = "bq"."vendor_id"', $sql);
        $this->assertStringContainsString('"cs"."prev_csid" is null or "cs"."prev_csid" = ?', $sql);
        $this->assertStringContainsString('"po"."deleted_at" is null', $sql);
        $this->assertStringContainsString('po.podate DESC NULLS LAST', $sql);
        $this->assertCount(2, $this->connection->queries);
        $this->assertSame(1, $result['recordsFiltered']);
        $this->assertStringNotContainsString('"po"."reuse"', $sql);
        $this->assertSame('AW', $result['data'][0]['cpny_id']);
        $this->assertSame('1000000123', $result['data'][0]['ponbr']);
        $this->assertSame('encoded12', $result['data'][0]['po_eid']);
        $this->assertSame('encoded34', $result['data'][0]['bq_eid']);
        $this->assertSame('encoded56', $result['data'][0]['cs_eid']);
    }

    public function test_new_columns_can_be_searched_and_po_date_sorted(): void
    {
        $this->connection->results = [[(object) ['aggregate' => 5]], [(object) ['aggregate' => 1]], [$this->row()]];
        $request = new Request(['search' => ['value' => '1000000123'], 'order' => [['column' => 5, 'dir' => 'asc']], 'start' => 0, 'length' => 10]);
        $result = (new LastOrderController())->bqJson($request)->getData(true);
        $sql = $this->connection->queries[2]['sql'];

        $this->assertStringContainsString('"cs"."cpny_id"::text ilike ?', $sql);
        $this->assertStringContainsString('"selected_po"."ponbr"::text ilike ?', $sql);
        $this->assertStringContainsString("TO_CHAR(selected_po.podate, 'DD/MM/YYYY') ILIKE ?", $sql);
        $this->assertStringContainsString('order by "selected_po"."podate" asc', $sql);
        $this->assertContains('%1000000123%', $this->connection->queries[2]['bindings']);
        $this->assertSame(5, $result['recordsTotal']);
        $this->assertSame(1, $result['recordsFiltered']);
    }

    public function test_all_entries_does_not_apply_negative_limit(): void
    {
        $this->connection->results = [[(object) ['aggregate' => 0]], []];
        (new LastOrderController())->bqJson(new Request(['length' => -1]));
        $sql = $this->connection->queries[1]['sql'];
        $this->assertStringNotContainsString('limit -1', $sql);
        $this->assertStringNotContainsString('offset ', $sql);
    }
}
