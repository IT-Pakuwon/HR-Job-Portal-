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
        $startedAt = now();

        \Log::info('Command staging:vms-rfp started', [
            'started_at' => $startedAt->toDateTimeString(),
        ]);

        try {
            $controller = app(VmsRfpStagingController::class);
            $response = $controller->run();

            $data = method_exists($response, 'getData') ? $response->getData(true) : null;

            if (!($data['success'] ?? false)) {
                $this->error($data['message'] ?? 'VMS RFP staging failed.');

                \Log::warning('Command staging:vms-rfp failed', [
                    'started_at'  => $startedAt->toDateTimeString(),
                    'finished_at' => now()->toDateTimeString(),
                    'result'      => $data,
                ]);

                return self::FAILURE;
            }

            $this->info('VMS RFP staging executed successfully.');

            if ($data) {
                $this->line(json_encode($data, JSON_PRETTY_PRINT));
            }

            \Log::info('Command staging:vms-rfp completed', [
                'started_at'  => $startedAt->toDateTimeString(),
                'finished_at' => now()->toDateTimeString(),
                'result'      => $data,
            ]);

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
