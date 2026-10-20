<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Integration\IFCAAPIPOController;

class AutoSchedulerProcessPOHold extends Command
{
    protected $signature = 'ifca:pohold-auto-process';

    protected $description = 'Auto process IFCA PO Hold H ke D dari H-3 sampai hari ini';

    public function handle()
    {
        try {
            $this->info('Start auto process IFCA PO Hold...');

            $controller = app(IFCAAPIPOController::class);

            // ini function baru khusus scheduler
            $result = $controller->SchedulerProcessPOHold();

            Log::info('IFCA PO Hold Auto Process Result', $result);

            $this->info(json_encode($result, JSON_PRETTY_PRINT));

            return (!empty($result['ok'])) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $e) {
            Log::error('IFCA PO Hold Auto Process Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
