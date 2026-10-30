<?php

namespace Tests\Feature;

use App\Services\AiSearch\AiSearchException;
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

class AiSearchPipelineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['ai-search.openrouter.base_url' => 'https://openrouter.ai/api/v1']);
    }

    private function catalog(): array
    {
        return ['source_id' => 3, 'schema' => 'public', 'table' => 'hr_trx_prf', 'columns' => ['job_title']];
    }

    public function test_provider_uses_server_side_key_and_parses_plan(): void
    {
        config(['ai-search.openrouter.api_key' => 'test-key', 'ai-search.openrouter.model' => 'test/model']);
        Http::preventStrayRequests();
        Http::fake(['openrouter.ai/*' => Http::response([
            'choices' => [['message' => ['content' => '{"operation":"count","table":"hr_trx_prf","job_title":"Programmer"}']]],
        ])]);
        $plan = app(OpenRouterClient::class)->plan('Berapa PRF Programmer?', $this->catalog());
        $this->assertSame('Programmer', $plan['job_title']);
        Http::assertSent(fn ($request) => $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-key')
            && $request['model'] === 'test/model');
    }

    public function test_missing_key_does_not_send_request(): void
    {
        Http::fake();
        config(['ai-search.openrouter.api_key' => null]);
        try {
            app(OpenRouterClient::class)->plan('Programmer', $this->catalog());
            $this->fail('Missing key must fail');
        } catch (AiSearchException $e) {
            $this->assertSame(503, $e->httpStatus);
            Http::assertNothingSent();
        }
    }

    public function test_invalid_provider_json_is_rejected(): void
    {
        config(['ai-search.openrouter.api_key' => 'test-key', 'ai-search.openrouter.model' => 'test/model']);
        Http::fake(['openrouter.ai/*' => Http::response([
            'choices' => [['message' => ['content' => 'SELECT * FROM hr_trx_prf']]],
        ])]);
        $this->expectException(AiSearchException::class);
        app(OpenRouterClient::class)->plan('Programmer', $this->catalog());
    }

    public function test_provider_error_does_not_expose_response_body(): void
    {
        config(['ai-search.openrouter.api_key' => 'test-key', 'ai-search.openrouter.model' => 'test/model']);
        Http::fake(['openrouter.ai/*' => Http::response(['error' => 'private-secret'], 401)]);
        try {
            app(OpenRouterClient::class)->plan('Programmer', $this->catalog());
            $this->fail('Provider rejection must fail');
        } catch (AiSearchException $e) {
            $this->assertSame(502, $e->httpStatus);
            $this->assertStringNotContainsString('private-secret', $e->getMessage());
        }
    }

    public function test_model_cannot_supply_raw_sql_or_extra_fields(): void
    {
        $this->expectException(AiSearchException::class);
        app(QueryValidator::class)->validate([
            'operation' => 'count', 'table' => 'hr_trx_prf', 'job_title' => 'Programmer',
            'sql' => 'DELETE FROM hr_trx_prf',
        ], $this->catalog());
    }

    public function test_unapproved_column_is_rejected(): void
    {
        $catalog = $this->catalog();
        $catalog['columns'] = [];
        $this->expectException(AiSearchException::class);
        app(QueryValidator::class)->validate([
            'operation' => 'count', 'table' => 'hr_trx_prf', 'job_title' => 'Programmer',
        ], $catalog);
    }

    public function test_executor_keeps_title_as_binding_in_read_only_transaction(): void
    {
        $title = "Programmer' OR 1=1 --";
        $database = Mockery::mock(DatabaseManager::class);
        $connection = Mockery::mock(Connection::class);
        $grammar = new PostgresGrammar;
        $catalog = Mockery::mock(SchemaCatalog::class);
        $catalog->shouldReceive('forUser')->once()->with('rikiparahat', 3)->andReturn($this->catalog());
        $database->shouldReceive('forSource')->once()->with(3, 'rikiparahat')->andReturn($connection);
        $connection->shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());
        $connection->shouldReceive('statement')->once()->with('SET TRANSACTION READ ONLY');
        $connection->shouldReceive('statement')->once()->with("SET LOCAL statement_timeout = '5s'");
        $connection->shouldReceive('getQueryGrammar')->andReturn($grammar);
        $builder = (new Builder($connection, $grammar, new Processor))->from('public.hr_trx_prf');
        $connection->shouldReceive('table')->with('public.hr_trx_prf')->andReturn($builder);
        $connection->shouldReceive('select')->once()->with(
            'select count(*) as aggregate from "public"."hr_trx_prf" where LOWER(TRIM(CAST("job_title" AS TEXT))) = LOWER(?)',
            [$title], false
        )->andReturn([(object) ['aggregate' => 0]]);
        $result = (new QueryExecutor($database, new QueryValidator, $catalog))->execute([
            'source_id' => 3, 'operation' => 'count', 'table' => 'hr_trx_prf', 'job_title' => $title,
        ], 'rikiparahat');
        $this->assertSame(0, $result['jumlah_prf']);
    }

    public function test_group_plan_accepts_authorized_column_and_normalizes_filters(): void
    {
        $catalog = $this->catalog();
        $catalog['columns'][] = 'reason_vacancy';
        $result = (new QueryValidator)->validate([
            'operation' => 'group_count', 'table' => 'hr_trx_prf',
            'column' => 'reason_vacancy', 'limit' => 10,
            'filters' => [['column' => 'job_title', 'value' => ' Programmer ']],
        ], $catalog);
        $this->assertSame('Programmer', $result['filters'][0]['value']);
        $this->assertSame('reason_vacancy', $result['column']);
    }

    public function test_grouping_rejects_unapproved_column(): void
    {
        $this->expectException(AiSearchException::class);
        (new QueryValidator)->validate([
            'operation' => 'group_count', 'table' => 'hr_trx_prf',
            'column' => 'reason_vacancy',
        ], $this->catalog());
    }

    public function test_grouping_rejects_injected_identifier_even_if_catalog_contains_it(): void
    {
        $catalog = $this->catalog();
        $column = 'job_title); DELETE FROM hr_trx_prf; --';
        $catalog['columns'][] = $column;
        $this->expectException(AiSearchException::class);
        (new QueryValidator)->validate([
            'operation' => 'group_count', 'table' => 'hr_trx_prf', 'column' => $column,
        ], $catalog);
    }

    public function test_grouping_rejects_excessive_limit(): void
    {
        $this->expectException(AiSearchException::class);
        (new QueryValidator)->validate([
            'operation' => 'group_count', 'table' => 'hr_trx_prf', 'column' => 'job_title', 'limit' => 999,
        ], $this->catalog());
    }

    public function test_revoked_column_is_checked_again_before_execution(): void
    {
        $catalog = Mockery::mock(SchemaCatalog::class);
        $catalog->shouldReceive('forUser')->andReturn($this->catalog());
        $database = Mockery::mock(DatabaseManager::class);
        $database->shouldNotReceive('forSource');
        $this->expectException(AiSearchException::class);
        (new QueryExecutor($database, new QueryValidator, $catalog))->execute([
            'source_id' => 3, 'operation' => 'group_count', 'table' => 'hr_trx_prf',
            'column' => 'reason_vacancy', 'limit' => 10, 'filters' => [],
        ], 'rikiparahat');
    }

    public function test_reason_frequency_query_orders_and_limits_results(): void
    {
        $allowed = $this->catalog();
        $allowed['columns'][] = 'reason_vacancy';
        $catalog = Mockery::mock(SchemaCatalog::class);
        $catalog->shouldReceive('forUser')->with('rikiparahat', 3)->andReturn($allowed);
        $database = Mockery::mock(DatabaseManager::class);
        $connection = Mockery::mock(Connection::class);
        $database->shouldReceive('forSource')->with(3, 'rikiparahat')->andReturn($connection);
        $grammar = new PostgresGrammar;
        $builder = (new Builder($connection, $grammar, new Processor))->from('public.hr_trx_prf');
        $connection->shouldReceive('getQueryGrammar')->andReturn($grammar);
        $connection->shouldReceive('table')->with('public.hr_trx_prf')->andReturn($builder);
        $connection->shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());
        $connection->shouldReceive('statement')->with('SET TRANSACTION READ ONLY');
        $connection->shouldReceive('statement')->with("SET LOCAL statement_timeout = '5s'");
        $connection->shouldReceive('select')->once()->with(
            Mockery::on(fn ($sql) => str_contains($sql, 'group by NULLIF(TRIM(CAST("reason_vacancy" AS TEXT))')
                && str_contains($sql, 'order by "jumlah_prf" desc, "nilai" asc limit 10')),
            [], false
        )->andReturn([
            (object) ['nilai' => 'Penambahan Karyawan', 'jumlah_prf' => 12],
            (object) ['nilai' => 'TEST', 'jumlah_prf' => 6],
        ]);
        $result = (new QueryExecutor($database, new QueryValidator, $catalog))->execute([
            'source_id' => 3, 'operation' => 'group_count', 'table' => 'hr_trx_prf',
            'column' => 'reason_vacancy', 'limit' => 10, 'filters' => [],
        ], 'rikiparahat');
        $this->assertSame(['Penambahan Karyawan', 12], $result['rows'][0]);
        $this->assertStringContainsString('12 dokumen PRF', $result['message']);
    }
}
