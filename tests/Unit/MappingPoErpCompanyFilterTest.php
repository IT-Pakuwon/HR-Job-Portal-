<?php

namespace Tests\Unit;

use App\Http\Controllers\MappingPoERPController;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\PostgresConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class MappingPoErpCompanyFilterTest extends TestCase
{
    private $previousContainer;
    private $previousFacade;
    private $previousResolver;
    private $connection;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousContainer = Container::getInstance();
        $this->previousFacade = Facade::getFacadeApplication();
        $this->previousResolver = Model::getConnectionResolver();
        $this->user = (object) ['cpny_id' => 'EP, CIP, EP'];
        $this->connection = new class extends PostgresConnection {
            public string $sql = '';
            public array $bindings = [];

            public function __construct()
            {
                parent::__construct(fn () => throw new \RuntimeException('No live database access.'));
            }

            public function select($query, $bindings = [], $useReadPdo = true)
            {
                $this->sql = $query;
                $this->bindings = $bindings;
                return [];
            }
        };
        $container = new Container();
        $container->instance('auth', new class($this->user) {
            public function __construct(private $user) {}
            public function user() { return $this->user; }
        });
        $container->instance(ResponseFactory::class, new class {
            public function json($data, $status = 200) { return new JsonResponse($data, $status); }
        });
        Container::setInstance($container);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        Model::setConnectionResolver(new ConnectionResolver(['pgsql4' => $this->connection]));
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousFacade);
        Container::setInstance($this->previousContainer);
        if ($this->previousResolver) Model::setConnectionResolver($this->previousResolver);
        else Model::unsetConnectionResolver();
        parent::tearDown();
    }

    public function test_company_filter_combines_with_access_scope_status_and_search_before_limit(): void
    {
        $request = Request::create('/mapping-po-erp/json', 'GET', ['cpny_id' => 'CIP', 'status' => 'D', 'search' => 'printer']);
        $response = (new MappingPoERPController())->json($request);

        $this->assertSame([], $response->getData(true)['data']);
        $this->assertStringContainsString('"cpny_id" in (?, ?)', $this->connection->sql);
        $this->assertStringContainsString('"cpny_id" = ?', $this->connection->sql);
        $this->assertStringContainsString('"status" = ?', $this->connection->sql);
        $this->assertStringContainsString('limit 500', $this->connection->sql);
        $this->assertSame(['CIP', 'EP', 'CIP', 'D'], array_slice($this->connection->bindings, 0, 4));
        $this->assertContains('%printer%', $this->connection->bindings);
    }

    public function test_requested_company_never_replaces_allowed_company_scope(): void
    {
        (new MappingPoERPController())->json(Request::create('/mapping-po-erp/json', 'GET', ['cpny_id' => 'GPS']));
        $this->assertStringContainsString('"cpny_id" in (?, ?) and "cpny_id" = ?', $this->connection->sql);
        $this->assertSame(['CIP', 'EP', 'GPS'], $this->connection->bindings);
    }

    public function test_empty_scope_does_not_return_all_companies(): void
    {
        $this->user->cpny_id = '';
        (new MappingPoERPController())->json(Request::create('/mapping-po-erp/json'));
        $this->assertStringContainsString('0 = 1', $this->connection->sql);
    }
}
