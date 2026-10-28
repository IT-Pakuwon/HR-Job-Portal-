<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Integration\SLAPIPOController;

class AutoSchedulerProcessPOSL extends Command
{
    protected $signature = 'ifca:posl-auto-process';

    protected $description = 'Auto process PO SOLOMON P ke C berdasarkan reviewed_at H-14 sampai hari ini';

    public function handle()
    {
        try {
            $this->info('Start auto process PO Solomon...');

            $controller = app(SLAPIPOController::class);

            // ini function baru khusus scheduler
            $result = $controller->SchedulerProcessPOSL();

            Log::info('PO Solomon Auto Process Result', $result);

            $this->info(json_encode($result, JSON_PRETTY_PRINT));

            return (!empty($result['ok'])) ? Command::SUCCESS : Command::FAILURE;
        } catch (\Throwable $e) {
            Log::error('PO Solomon Auto Process Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
