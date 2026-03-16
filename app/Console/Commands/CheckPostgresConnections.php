<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckPostgresConnections extends Command
{
    protected $signature = 'postgres:check-connections';
    protected $description = 'Check PostgreSQL connections and restart service if max_connections is reached';

    public function handle()
    {
        try {
            // Ambil max_connections
            $maxConnRow = DB::connection('pgsql')->selectOne('SHOW max_connections');
            $maxConnections = (int) ($maxConnRow->max_connections ?? 0);

            if ($maxConnections <= 0) {
                $msg = 'postgres:check-connections gagal membaca max_connections';
                $this->error($msg);
                Log::warning($msg);
                return self::FAILURE;
            }

            // Hitung jumlah koneksi aktif
            $activityCountRow = DB::connection('pgsql')->selectOne("
                SELECT COUNT(*) AS total
                FROM pg_stat_activity
            ");
            $activityCount = (int) ($activityCountRow->total ?? 0);

            // Ambil detail koneksi untuk log
            $activities = DB::connection('pgsql')->select("
                SELECT
                    pid,
                    usename,
                    datname,
                    client_addr,
                    application_name,
                    state,
                    backend_start,
                    xact_start,
                    query_start,
                    wait_event_type,
                    wait_event,
                    query
                FROM pg_stat_activity
                ORDER BY backend_start
            ");

            Log::info('postgres:check-connections result', [
                'max_connections' => $maxConnections,
                'activity_count'  => $activityCount,
            ]);

            $this->info("max_connections: {$maxConnections}");
            $this->info("pg_stat_activity count: {$activityCount}");

            // Jalankan restart kalau melebihi / sama dengan max_connections
            if ($activityCount >= $maxConnections) {
                $cmd = 'sudo /bin/systemctl restart postgresql';

                exec($cmd . ' 2>&1', $output, $exitCode);

                Log::warning('PostgreSQL restarted because connections reached max limit', [
                    'max_connections' => $maxConnections,
                    'activity_count'  => $activityCount,
                    'command'         => $cmd,
                    'exit_code'       => $exitCode,
                    'output'          => $output,
                ]);

                if ($exitCode !== 0) {
                    $this->error('PostgreSQL restart failed');
                    $this->line(implode("\n", $output));
                    return self::FAILURE;
                }

                $this->warn('PostgreSQL restarted successfully because max_connections was reached.');
            } else {
                $this->info('Connections are still below max_connections. No restart needed.');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('postgres:check-connections error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}