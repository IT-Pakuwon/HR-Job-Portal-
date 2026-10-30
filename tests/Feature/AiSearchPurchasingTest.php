<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AiSearch\AiOrchestrator;
use App\Services\AiSearch\AiSearchException;
use App\Services\AiSearch\CatalogAccessPolicy;
use App\Services\AiSearch\DatabaseManager;
use App\Services\AiSearch\OpenRouterClient;
use App\Services\AiSearch\QueryExecutor;
use App\Services\AiSearch\QueryValidator;
use App\Services\AiSearch\SchemaCatalog;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class AiSearchPurchasingTest extends TestCase
{
    private function catalog(): array
    {
        return [
            'source_id' => 1, 'connection_name' => 'pgsql',
            'tables' => [
                'public.purchase_orders' => ['schema' => 'public', 'table' => 'purchase_orders', 'columns' => ['id', 'vendor', 'status']],
                'public.suppliers' => ['schema' => 'public', 'table' => 'suppliers', 'columns' => ['id', 'name']],
            ],
        ];
    }

    private function permission(array $values = []): object
    {
        return (object) array_merge([
            'source_id' => 1, 'schema_table_id' => null, 'module_name' => null,
            'company_id' => null, 'department_id' => null, 'permission_type' => 'READ',
        ], $values);
    }

    public function test_source_permission_covers_all_tables_but_deny_wins(): void
    {
        $policy = new CatalogAccessPolicy;
        $table = (object) ['id' => 100, 'module_name' => 'purchase'];
        $this->assertTrue($policy->allows(collect([$this->permission()]), 1, $table));
        $this->assertFalse($policy->allows(collect([
            $this->permission(),
            $this->permission(['schema_table_id' => 100, 'permission_type' => 'DENY']),
        ]), 1, $table));
    }

    public function test_missing_permission_and_other_source_do_not_grant_access(): void
    {
        $policy = new CatalogAccessPolicy;
        $table = (object) ['id' => 100, 'module_name' => null];
        $this->assertFalse($policy->allows(collect(), 1, $table));
        $this->assertFalse($policy->allows(collect([$this->permission(['source_id' => 3])]), 1, $table));
        $this->assertFalse($policy->allows(collect([$this->permission(['company_id' => 'AW'])]), 1, $table));
    }

    public function test_table_permission_does_not_leak_to_other_tables(): void
    {
        $permissions = collect([$this->permission(['source_id' => null, 'schema_table_id' => 100])]);
        $policy = new CatalogAccessPolicy;
        $this->assertTrue($policy->allows($permissions, 1, (object) ['id' => 100, 'module_name' => null]));
        $this->assertFalse($policy->allows($permissions, 1, (object) ['id' => 101, 'module_name' => null]));
    }

    public function test_validator_supports_any_catalog_table_without_prf_names(): void
    {
        $result = (new QueryValidator)->validate([
            'operation' => 'select', 'schema' => 'public', 'table' => 'suppliers',
            'columns' => ['id', 'name'], 'limit' => 20, 'order_by' => 'id', 'direction' => 'desc',
        ], $this->catalog());
        $this->assertSame(1, $result['source_id']);
        $this->assertSame('suppliers', $result['table']);
    }

    public function test_unknown_table_is_rejected(): void
    {
        $this->expectException(AiSearchException::class);
        (new QueryValidator)->validate([
            'operation' => 'count', 'schema' => 'public', 'table' => 'hr_trx_prf',
        ], $this->catalog());
    }

    public function test_unknown_selection_column_is_rejected(): void
    {
        $this->expectException(AiSearchException::class);
        (new QueryValidator)->validate([
            'operation' => 'select', 'table' => 'suppliers', 'columns' => ['password'],
        ], $this->catalog());
    }

    public function test_order_column_must_be_authorized(): void
    {
        $this->expectException(AiSearchException::class);
        (new QueryValidator)->validate([
            'operation' => 'select', 'table' => 'suppliers', 'columns' => ['name'], 'order_by' => 'password',
        ], $this->catalog());
    }

    public function test_provider_receives_both_authorized_tables(): void
    {
        config([
            'ai-search.openrouter.base_url' => 'https://openrouter.ai/api/v1',
            'ai-search.openrouter.api_key' => 'test-key', 'ai-search.openrouter.model' => 'test/model',
        ]);
        Http::preventStrayRequests();
        Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => [
            'content' => '{"operation":"count","schema":"public","table":"suppliers","filters":[]}',
        ]]]])]);
        $plan = app(OpenRouterClient::class)->plan('Berapa jumlah supplier?', $this->catalog());
        $this->assertSame('suppliers', $plan['table']);
        Http::assertSent(fn ($r) => str_contains($r['messages'][0]['content'], 'public.suppliers')
            && str_contains($r['messages'][0]['content'], 'public.purchase_orders'));
    }

    public function test_canvass_document_question_uses_authorized_header_table(): void
    {
        config([
            'ai-search.openrouter.base_url' => 'https://openrouter.ai/api/v1',
            'ai-search.openrouter.api_key' => 'test-key', 'ai-search.openrouter.model' => 'test/model',
        ]);
        $catalog = [
            'source_id' => 1,
            'tables' => [
                'public.tr_cs' => ['schema' => 'public', 'table' => 'tr_cs', 'columns' => ['csid']],
                'public.tr_cs_detail' => ['schema' => 'public', 'table' => 'tr_cs_detail', 'columns' => ['csid']],
            ],
        ];
        Http::preventStrayRequests();
        Http::fake(['openrouter.ai/*' => Http::response(['choices' => [['message' => [
            'content' => '{"operation":"count","schema":"public","table":"tr_cs","filters":[]}',
        ]]]])]);
        $plan = app(OpenRouterClient::class)->plan('berapa banyak canvass sheet', $catalog);
        $validated = (new QueryValidator)->validate($plan, $catalog);
        $this->assertSame('tr_cs', $validated['table']);
        Http::assertSent(fn ($r) => str_contains($r['messages'][0]['content'], 'not tr_cs_detail items'));
    }

    public function test_missing_source_does_not_silently_search_default_database(): void
    {
        $user = new User;
        $user->username = 'rikiparahat';
        $orchestrator = Mockery::mock(AiOrchestrator::class);
        $orchestrator->shouldNotReceive('search');
        $this->app->instance(AiOrchestrator::class, $orchestrator);
        $this->withoutMiddleware()->actingAs($user)->postJson(route('ai-search.search'), [
            'question' => 'berapa banyak canvass sheet',
        ])->assertUnprocessable()->assertJsonValidationErrors('source_id');
    }

    public function test_executor_reads_selected_table_and_binds_literal_contains_filter(): void
    {
        $catalog = Mockery::mock(SchemaCatalog::class);
        $catalog->shouldReceive('forUser')->once()->with('rikiparahat', 1)->andReturn($this->catalog());
        $database = Mockery::mock(DatabaseManager::class);
        $connection = Mockery::mock(Connection::class);
        $database->shouldReceive('forSource')->once()->with(1, 'rikiparahat')->andReturn($connection);
        $grammar = new PostgresGrammar;
        $connection->shouldReceive('getQueryGrammar')->andReturn($grammar);
        $connection->shouldReceive('table')->once()->with('public.suppliers')
            ->andReturn((new Builder($connection, $grammar, new Processor))->from('public.suppliers'));
        $connection->shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());
        $connection->shouldReceive('statement')->once()->with('SET TRANSACTION READ ONLY');
        $connection->shouldReceive('statement')->once()->with("SET LOCAL statement_timeout = '5s'");
        $connection->shouldReceive('select')->once()->with(
            Mockery::on(fn ($sql) => str_contains($sql, 'from "public"."suppliers"')
                && str_contains($sql, 'POSITION(LOWER(?) IN')
                && str_contains($sql, 'order by "id" desc limit 10')),
            ["ACME%' OR 1=1 --"], false
        )->andReturn([(object) ['id' => 7, 'name' => 'ACME']]);
        $plan = (new QueryValidator)->validate([
            'operation' => 'select', 'table' => 'suppliers', 'columns' => ['id', 'name'],
            'order_by' => 'id', 'direction' => 'desc',
            'filters' => [['column' => 'name', 'operator' => 'contains', 'value' => "ACME%' OR 1=1 --"]],
        ], $this->catalog());
        $result = (new QueryExecutor($database, new QueryValidator, $catalog))->execute($plan, 'rikiparahat');
        $this->assertSame([[7, 'ACME']], $result['rows']);
        $this->assertStringContainsString('public.suppliers', $result['message']);
    }
}
