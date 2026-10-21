<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Integration\IFCAAPIIssueController;

class AutoSchedulerProcessIssueOnProgress extends Command
{
    protected $signature = 'ifca:issueonprogress-auto-process';

    protected $description = 'Auto process IFCA Issue OnProgress P-IFCA ke C dari H-3 sampai hari ini';

    public function handle()
    {
        try {
            $this->info('Start auto process IFCA Issue OnProgress...');

            $controller = app(IFCAAPIIssueController::class);

            // ini function baru khusus scheduler
            $result = $controller->SchedulerProcessIssueOnProgress();

            Log::info('IFCA Issue OnProgress Auto Process Result', $result);

            $this->info(json_encode($result, JSON_PRETTY_PRINT));

            return (!empty($result['ok'])) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $e) {
            Log::error('IFCA Issue OnProgress Auto Process Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
