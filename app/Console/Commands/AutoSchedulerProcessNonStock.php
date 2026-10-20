<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Integration\IFCAAPINonStockController;

class AutoSchedulerProcessNonStock extends Command
{
    protected $signature = 'ifca:nonstock-auto-process';

    protected $description = 'Auto process IFCA NonStock status H dari H-6 sampai H-3';

    public function handle()
    {
        try {
            $this->info('Start auto process IFCA NonStock...');

            $controller = app(IFCAAPINonStockController::class);

            // ini function baru khusus scheduler
            $result = $controller->SchedulerProcessNonStock();

            Log::info('IFCA NonStock Auto Process Result', $result);

            $this->info(json_encode($result, JSON_PRETTY_PRINT));

            return (!empty($result['ok'])) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $e) {
            Log::error('IFCA NonStock Auto Process Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
