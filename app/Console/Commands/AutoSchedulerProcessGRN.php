<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Integration\IFCAAPIGRNController;

class AutoSchedulerProcessGRN extends Command
{
    protected $signature = 'ifca:grn-auto-process';

    protected $description = 'Auto process GRN H ke P dan P-IFCA ke C dari H-14 sampai hari ini';

    public function handle()
    {
        try {
            $this->info('Start auto process IFCA GRN...');

            $controller = app(IFCAAPIGRNController::class);

            // ini function baru khusus scheduler
            $result = $controller->SchedulerProcessGRN();

            Log::info('IFCA GRN Auto Process Result', $result);

            $this->info(json_encode($result, JSON_PRETTY_PRINT));

            return (!empty($result['ok'])) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $e) {
            Log::error('IFCA GRN Auto Process Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
