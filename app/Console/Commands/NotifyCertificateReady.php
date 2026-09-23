<?php

namespace App\Console\Commands;

use App\Models\MsLndTrainingSchedule;
use App\Models\TrLndTrainingRegistration;
use App\Services\TrainingWaitlistNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyCertificateReady extends Command
{
    protected $signature = 'training:notify-certificate-ready';
    protected $description = 'Email attendees once their training certificate crosses the H+1 eligibility window (Approved + attended + event date passed)';

    public function handle(): void
    {
        $eligibleScheduleIds = MsLndTrainingSchedule::on('pgsql5')
            ->whereDate('schedule_date', '<', now()->toDateString())
            ->pluck('schedule_id');

        if ($eligibleScheduleIds->isEmpty()) {
            return;
        }

        $registrations = TrLndTrainingRegistration::on('pgsql5')
            ->whereIn('schedule_id', $eligibleScheduleIds)
            ->where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->whereNotNull('completed_at')
            ->whereNull('certificate_notified_at')
            ->get();

        foreach ($registrations as $registration) {
            try {
                TrainingWaitlistNotifier::notifyCertificateReady($registration);

                $registration->update(['certificate_notified_at' => now()]);

                Log::info('Training certificate-ready notification sent', ['training_regist_id' => $registration->training_regist_id]);
            } catch (\Throwable $e) {
                Log::error('Training certificate-ready notification failed', [
                    'training_regist_id' => $registration->training_regist_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
