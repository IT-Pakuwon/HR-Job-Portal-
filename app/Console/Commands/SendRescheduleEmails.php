<?php

namespace App\Console\Commands;

use App\Models\TrLndRescheduleEmailQueue;
use App\Services\TrainingWaitlistNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendRescheduleEmails extends Command
{
    private const MAX_ATTEMPTS = 3;

    protected $signature = 'training:send-reschedule-emails {--limit=15 : Max emails per run}';
    protected $description = 'Send queued training reschedule emails in small batches (one schedule per run) to stay under the SMTP rate limit';

    public function handle(): void
    {
        $limit = max(1, (int) $this->option('limit'));

        // One schedule per run: finish the oldest pending schedule before
        // starting the next so a reschedule's participants hear about it
        // together rather than interleaved with another session's.
        $scheduleId = TrLndRescheduleEmailQueue::where('status', TrLndRescheduleEmailQueue::STATUS_PENDING)
            ->orderBy('id')
            ->value('schedule_id');

        if (!$scheduleId) {
            return;
        }

        $batch = TrLndRescheduleEmailQueue::where('status', TrLndRescheduleEmailQueue::STATUS_PENDING)
            ->where('schedule_id', $scheduleId)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $sent = 0;

        foreach ($batch as $queued) {
            try {
                $delivered = TrainingWaitlistNotifier::sendRescheduleEmail($queued);

                // No active user / address: nothing to retry, mark it done.
                $queued->status = TrLndRescheduleEmailQueue::STATUS_SENT;
                $queued->last_error = $delivered ? null : 'No recipient (user inactive or no email)';
                $queued->sent_at = $delivered ? now() : null;
                $queued->save();

                $sent += $delivered ? 1 : 0;
            } catch (\Throwable $e) {
                $queued->attempts++;
                $queued->last_error = mb_substr($e->getMessage(), 0, 500);

                if ($queued->attempts >= self::MAX_ATTEMPTS) {
                    $queued->status = TrLndRescheduleEmailQueue::STATUS_FAILED;
                }

                $queued->save();

                Log::error('Training reschedule email failed', [
                    'schedule_id' => $scheduleId,
                    'training_regist_id' => $queued->training_regist_id,
                    'attempts' => $queued->attempts,
                    'message' => $e->getMessage(),
                ]);

                // The relay's rate limit applies to everything after this
                // too — stop and let the next run pick the rest up.
                if (str_contains($e->getMessage(), '421') || stripos($e->getMessage(), 'too many') !== false) {
                    break;
                }
            }
        }

        $this->info("Schedule {$scheduleId}: sent {$sent} of ".count($batch).' queued');
    }
}
