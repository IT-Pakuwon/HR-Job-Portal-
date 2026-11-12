<?php

namespace Tests\Unit;

use App\Http\Controllers\ItRecommendationController;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\PostgresConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ItRecommendationInventorySearchTest extends TestCase
{
    private $previousContainer;
    private $previousResolver;
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousContainer = Container::getInstance();
        $this->previousResolver = Model::getConnectionResolver();
        $container = new Container();
        $container->instance(ResponseFactory::class, new class {
            public function json($data) { return new JsonResponse($data); }
        });
        Container::setInstance($container);
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
        Model::setConnectionResolver(new ConnectionResolver(['pgsql' => $this->connection]));
    }

    protected function tearDown(): void
    {
        Container::setInstance($this->previousContainer);
        if ($this->previousResolver) Model::setConnectionResolver($this->previousResolver);
        else Model::unsetConnectionResolver();
        parent::tearDown();
    }

    public function test_computer_services_are_not_restricted_to_description_keywords(): void
    {
        (new ItRecommendationController())->inventorySearch(new Request(['q' => 'jasa recovery']));
        $this->assertStringContainsString('from "ms_inventory"', $this->connection->sql);
        $this->assertStringContainsString('"status" = ? and (("item_type" in (?, ?) and "item_category" = ?) or ("item_type" = ? and "item_category" = ? and (', $this->connection->sql);
        $this->assertSame(['A', 'NS', 'SE', 'Komputer', 'SE', 'Jasa'], array_slice($this->connection->bindings, 0, 6));
        $this->assertSame(['%jasa recovery%', '%jasa recovery%'], array_slice($this->connection->bindings, -2));
        $this->assertStringContainsString('and ("inventoryid"::text ilike ? or "inventory_descr"::text ilike ?)', $this->connection->sql);
    }

    public function test_general_service_keyword_restrictions_and_code_search_are_preserved(): void
    {
        (new ItRecommendationController())->inventorySearch(new Request(['q' => 'SVCOPR1928']));
        foreach (['%perbaikan laptop%', '%jasa perbaikan%', '%instalasi kabel jaringan%'] as $keyword) {
            $this->assertContains($keyword, $this->connection->bindings);
        }
        $this->assertSame(['%SVCOPR1928%', '%SVCOPR1928%'], array_slice($this->connection->bindings, -2));
    }
}
