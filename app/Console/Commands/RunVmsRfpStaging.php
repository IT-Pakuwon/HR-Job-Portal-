<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Integration\VmsRfpStagingController;

class RunVmsRfpStaging extends Command
{
    protected $signature = 'staging:vms-rfp';
    protected $description = 'Run staging transfer VMS RFP';

    public function handle()
    {
        try {
            $controller = app(VmsRfpStagingController::class);
            $response = $controller->run();

            $data = method_exists($response, 'getData') ? $response->getData(true) : null;

            $this->info('VMS RFP staging executed successfully.');

            if ($data) {
                $this->line(json_encode($data, JSON_PRETTY_PRINT));
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed run staging:vms-rfp');
            $this->error($e->getMessage());

            \Log::error('Command staging:vms-rfp error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return self::FAILURE;
        }
    }
}