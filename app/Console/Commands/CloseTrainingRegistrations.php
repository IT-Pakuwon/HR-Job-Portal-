<?php

namespace App\Console\Commands;

use App\Models\MsLndTrainingSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CloseTrainingRegistrations extends Command
{
    protected $signature = 'training:close-registrations';
    protected $description = 'Auto-close training schedules past their registration deadline (H-3 closing)';

    public function handle(): void
    {
        $due = MsLndTrainingSchedule::where('status', 'P')
            ->whereNotNull('registration_deadline')
            ->where('registration_deadline', '<=', now()->toDateString())
            ->get();

        foreach ($due as $detail) {
            try {
                $detail->update([
                    'status' => 'C',
                    'updated_by' => 'system',
                ]);

                Log::info('Training schedule auto-closed', ['schedule_id' => $detail->schedule_id, 'schedule_date' => $detail->schedule_date]);
            } catch (\Throwable $e) {
                Log::error('Training schedule auto-close failed', [
                    'schedule_id' => $detail->schedule_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
