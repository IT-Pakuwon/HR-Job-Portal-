<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPostgresConnections extends Command
{
    protected $signature = 'postgres:check-connections';
    protected $description = 'Check PostgreSQL connections and restart service if max_connections is reached';

    public function handle()
    {
        try {
            $host = env('PG_MONITOR_HOST', '127.0.0.1');
            $port = env('PG_MONITOR_PORT', '5432');
            $db   = env('PG_MONITOR_DATABASE', 'postgres');
            $user = env('PG_MONITOR_USERNAME', 'postgres');
            $pass = env('PG_MONITOR_PASSWORD', '');

            $maxConnections = $this->runPsqlScalar(
                $host,
                $port,
                $db,
                $user,
                $pass,
                "SHOW max_connections;"
            );

            if (!is_numeric($maxConnections) || (int) $maxConnections <= 0) {
                $msg = 'postgres:check-connections gagal membaca max_connections via psql';
                $this->error($msg);
                Log::warning($msg, ['raw' => $maxConnections]);
                return self::FAILURE;
            }

            $maxConnections = (int) $maxConnections;

            $activityCount = $this->runPsqlScalar(
                $host,
                $port,
                $db,
                $user,
                $pass,
                "SELECT COUNT(*) FROM pg_stat_activity;"
            );

            if (!is_numeric($activityCount)) {
                $msg = 'postgres:check-connections gagal membaca pg_stat_activity via psql';
                $this->error($msg);
                Log::warning($msg, ['raw' => $activityCount]);
                return self::FAILURE;
            }

            $activityCount = (int) $activityCount;

            $this->info("max_connections: {$maxConnections}");
            $this->info("pg_stat_activity count: {$activityCount}");

            Log::info('postgres:check-connections result', [
                'max_connections' => $maxConnections,
                'activity_count'  => $activityCount,
            ]);

            $threshold = (int) floor($maxConnections * 0.95);

            if ($activityCount >= $threshold) {
                $cmd = 'sudo /usr/bin/systemctl restart postgresql';

                exec($cmd . ' 2>&1', $output, $exitCode);

                Log::warning('PostgreSQL restart attempt because connections reached threshold', [
                    'max_connections' => $maxConnections,
                    'threshold'       => $threshold,
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

                $this->warn("PostgreSQL restarted successfully because connection count reached threshold {$threshold}.");
            } else {
                $this->info("Connections are still below threshold {$threshold}. No restart needed.");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('postgres:check-connections error', [
                'message' => $e->getMessage(),
            ]);

            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    protected function runPsqlScalar(
        string $host,
        string $port,
        string $database,
        string $username,
        string $password,
        string $sql
    ): string {
        $escapedHost = escapeshellarg($host);
        $escapedPort = escapeshellarg($port);
        $escapedDb   = escapeshellarg($database);
        $escapedUser = escapeshellarg($username);
        $escapedSql  = escapeshellarg($sql);

        $prefix = '';
        if ($password !== '') {
            $prefix = 'PGPASSWORD=' . escapeshellarg($password) . ' ';
        }

        $cmd = $prefix
            . "psql -h {$escapedHost} -p {$escapedPort} -U {$escapedUser} -d {$escapedDb} -t -A -c {$escapedSql} 2>&1";

        exec($cmd, $output, $exitCode);

        $result = trim(implode("\n", $output));

        if ($exitCode !== 0) {
            throw new \RuntimeException("psql command failed: " . $result);
        }

        return trim($result);
    }
}