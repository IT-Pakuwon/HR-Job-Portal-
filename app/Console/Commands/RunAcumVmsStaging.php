<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Integration\AcumVmsStagingController;

class RunAcumVmsStaging extends Command
{
    protected $signature = 'staging:acumvms {--app=ACUMVMS}';
    protected $description = 'Inject data PGSQL -> MySQL7 staging tables (upsert) based on sys_staging_setting window';

    public function handle()
    {
        $appId = $this->option('app') ?? 'ACUMVMS';

        $runner = app(AcumVmsStagingController::class);
        $res = $runner->run($appId, 'SCHEDULER');

        $this->info(json_encode($res, JSON_PRETTY_PRINT));

        return $res['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
