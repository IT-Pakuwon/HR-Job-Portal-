<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Integration\IFCAAPIPOController;

class AutoSchedulerProcessPOOnProgress extends Command
{
    protected $signature = 'ifca:poonprogress-auto-process';

    protected $description = 'Auto process IFCA PO OnProgress P-IFCA ke C dari H-3 sampai hari ini';

    public function handle()
    {
        try {
            $this->info('Start auto process IFCA PO OnProgress...');

            $controller = app(IFCAAPIPOController::class);

            // ini function baru khusus scheduler
            $result = $controller->SchedulerProcessPOOnProgress();

            Log::info('IFCA PO OnProgress Auto Process Result', $result);

            $this->info(json_encode($result, JSON_PRETTY_PRINT));

            return (!empty($result['ok'])) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $e) {
            Log::error('IFCA PO OnProgress Auto Process Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
