<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Integration\IFCAAPIIssueController;

class AutoSchedulerProcessIssueHold extends Command
{
    protected $signature = 'ifca:issuehold-auto-process';

    protected $description = 'Auto process IFCA Issue Hold H ke D dari H-21 sampai hari ini';

    public function handle()
    {
        try {
            $this->info('Start auto process IFCA Issue Hold...');

            $controller = app(IFCAAPIIssueController::class);

            // ini function baru khusus scheduler
            $result = $controller->SchedulerProcessIssueHold();

            Log::info('IFCA Issue Hold Auto Process Result', $result);

            $this->info(json_encode($result, JSON_PRETTY_PRINT));

            return (!empty($result['ok'])) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $e) {
            Log::error('IFCA Issue Hold Auto Process Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
