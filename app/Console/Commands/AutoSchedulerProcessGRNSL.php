<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Integration\SLAPIGRNController;

class AutoSchedulerProcessGRNSL extends Command
{
    protected $signature = 'ifca:grnsl-auto-process';

    protected $description = 'Auto process GRN SOLOMON P ke C dari H-14 sampai hari ini';

    public function handle()
    {
        try {
            $this->info('Start auto process GRN Solomon...');

            $controller = app(SLAPIGRNController::class);

            // ini function baru khusus scheduler
            $result = $controller->SchedulerProcessGRNSL();

            Log::info('GRN Solomon Auto Process Result', $result);

            $this->info(json_encode($result, JSON_PRETTY_PRINT));

            return (!empty($result['ok'])) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $e) {
            Log::error('GRN Solomon Auto Process Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
