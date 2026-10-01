<?php

namespace App\Console\Commands;

use App\Models\MsLndTrainingSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledTrainings extends Command
{
    protected $signature = 'training:publish-scheduled';
    protected $description = 'Auto-publish DRAFT training schedules whose published_datetime has arrived';

    public function handle(): void
    {
        $due = MsLndTrainingSchedule::where('status', 'D')
            ->whereNotNull('published_datetime')
            ->where('published_datetime', '<=', now())
            ->get();

        foreach ($due as $detail) {
            try {
                $detail->update([
                    'status' => 'P',
                    'updated_by' => 'system',
                ]);

                Log::info('Training schedule auto-published', ['schedule_id' => $detail->schedule_id, 'published_datetime' => $detail->published_datetime]);
            } catch (\Throwable $e) {
                Log::error('Training schedule auto-publish failed', [
                    'schedule_id' => $detail->schedule_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
