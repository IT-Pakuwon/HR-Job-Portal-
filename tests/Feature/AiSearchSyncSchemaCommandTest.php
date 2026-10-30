<?php

namespace Tests\Feature;

use App\Services\AiSearch\SchemaSynchronizer;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class AiSearchSyncSchemaCommandTest extends TestCase
{
    public function test_invalid_source_does_not_start_sync(): void
    {
        $service = Mockery::mock(SchemaSynchronizer::class);
        $service->shouldNotReceive('sync');
        $this->app->instance(SchemaSynchronizer::class, $service);

        $this->artisan('ai-search:sync-schema', ['--source' => 'invalid'])
            ->expectsOutput('Isi ID sumber yang valid, misalnya --source=3.')
            ->assertExitCode(1);
    }

    public function test_source_option_is_passed_to_sync_and_counts_are_reported(): void
    {
        $service = Mockery::mock(SchemaSynchronizer::class);
        $service->shouldReceive('sync')->once()->with(3)->andReturn([
            'tables' => 2, 'columns' => 5, 'relations' => 1, 'skipped_relations' => 0,
        ]);
        $this->app->instance(SchemaSynchronizer::class, $service);

        $this->artisan('ai-search:sync-schema', ['--source' => '3'])
            ->expectsOutput('Membaca struktur sumber ID 3...')
            ->expectsOutput('Selesai: 2 tabel/view, 5 kolom, 1 relasi.')
            ->expectsOutput('Foreign key yang dilewati (gabungan atau di luar schema): 0.')
            ->expectsOutput('Metadata baru tetap nonaktif. Periksa katalog dan izin sebelum mengaktifkan pencarian.')
            ->assertExitCode(0);
    }

    public function test_failure_returns_nonzero_without_printing_exception_secrets(): void
    {
        $service = Mockery::mock(SchemaSynchronizer::class);
        $service->shouldReceive('sync')->once()->with(3)
            ->andThrow(new RuntimeException('password=secret'));
        $this->app->instance(SchemaSynchronizer::class, $service);

        $this->artisan('ai-search:sync-schema', ['--source' => '3'])
            ->expectsOutput('Membaca struktur sumber ID 3...')
            ->expectsOutput('Sinkronisasi gagal. Periksa sumber, koneksi Laravel, izin baca metadata, dan tabel katalog pgsql9.')
            ->expectsOutput('Jenis error: RuntimeException')
            ->doesntExpectOutput('password=secret')
            ->assertExitCode(1);
    }
}
