<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Integration\SLAPIIssueController;

class AutoSchedulerProcessIssueSL extends Command
{
    protected $signature = 'ifca:issuesl-auto-process';

    protected $description = 'Auto process Issue SOLOMON P ke C berdasarkan reviewed_at H-14 sampai hari ini';

    public function handle()
    {
        try {
            $this->info('Start auto process Issue Solomon...');

            $controller = app(SLAPIIssueController::class);

            // ini function baru khusus scheduler
            $result = $controller->SchedulerProcessIssueSL();

            Log::info('Issue Solomon Auto Process Result', $result);

            $this->info(json_encode($result, JSON_PRETTY_PRINT));

            return (!empty($result['ok'])) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $e) {
            Log::error('Issue Solomon Auto Process Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
