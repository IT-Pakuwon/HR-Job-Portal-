<?php

namespace App\Console\Commands;

use App\Http\Controllers\Integration\VmsRfpStagingController;
use Illuminate\Console\Command;

class RunVmsRfpPostStaging extends Command
{
    protected $signature = 'staging:vms-rfp-post';
    protected $description = 'Run only post staging VMS RFP to tr_rfp';

    public function handle()
    {
        try {
            $controller = app(VmsRfpStagingController::class);
            $result = $controller->runPostStagingToTrRfp();

            $this->info('VMS RFP post staging executed successfully.');
            $this->line(json_encode($result, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed run staging:vms-rfp-post');
            $this->error($e->getMessage());

            \Log::error('Command staging:vms-rfp-post error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return self::FAILURE;
        }
    }
}
