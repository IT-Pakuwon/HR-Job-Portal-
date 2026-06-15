<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Integration\IFCAAPISupplierController;

class AutoSchedulerProcessSupplier extends Command
{
    protected $signature = 'ifca:supplier-auto-process';

    protected $description = 'Auto process IFCA Supplier status H hari ini';

    public function handle()
    {
        try {
            $this->info('Start auto process IFCA Supplier...');

            $controller = app(IFCAAPISupplierController::class);

            // ini function baru khusus scheduler
            $result = $controller->SchedulerProcessSupplier();

            Log::info('IFCA Supplier Auto Process Result', $result);

            $this->info(json_encode($result, JSON_PRETTY_PRINT));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('IFCA Supplier Auto Process Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}