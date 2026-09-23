<?php

namespace App\Services;

use App\Models\MsLndTrainingSchedule;
use App\Models\TrLndTrainingRegistration;

class TrainingRegistrationService
{
    /**
     * Freed by a self-cancel of an Approved registration: only auto-offer while
     * the schedule is still open for registration (pre-deadline). Once CLOSED,
     * the slot sits forfeited until an HCDEVACCESS user manually offers it.
     */
    public static function promoteWaitlistIfOpen(TrLndTrainingRegistration $cancelled): void
    {
        $detail = MsLndTrainingSchedule::where('schedule_id', $cancelled->schedule_id)->first();

        if (!$detail || $detail->status !== 'P') {
            return;
        }

        $next = self::nextWaitlisted($cancelled->schedule_id, $cancelled->cpny_id);

        if ($next) {
            self::offerSlot($next);
        }
    }

    /**
     * Continuation of an already-initiated offer round (auto pre-deadline, or
     * a manual HCDEVACCESS offer post-closing) — cascades regardless of
     * schedule status, since the slot was already carved out for this queue.
     */
    public static function cascadeToNextWaitlist(TrLndTrainingRegistration $declinedOrExpired): void
    {
        $next = self::nextWaitlisted($declinedOrExpired->schedule_id, $declinedOrExpired->cpny_id);

        if ($next) {
            self::offerSlot($next);
        }
    }

    /**
     * Offer a freed slot: set status_registration = 'O' and stamp
     * process_registration_date = now() — the 24h offer window is always
     * computed from that timestamp.
     */
    public static function offerSlot(TrLndTrainingRegistration $registration): void
    {
        $now = now();

        $registration->status_registration = TrLndTrainingRegistration::REG_STATUS_OFFERED;
        $registration->process_registration_user = 'system';
        $registration->process_registration_date = $now;
        $registration->updated_by = 'system';
        $registration->updated_at = $now;
        $registration->save();

        TrainingWaitlistNotifier::sendOffer($registration->fresh(['schedule.schedule.training']));
    }

    private static function nextWaitlisted(string $scheduleId, string $cpnyId): ?TrLndTrainingRegistration
    {
        return TrLndTrainingRegistration::where('schedule_id', $scheduleId)
            ->where('cpny_id', $cpnyId)
            ->where('status_registration', TrLndTrainingRegistration::REG_STATUS_WAITLISTED)
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->orderBy('created_at')
            ->lockForUpdate()
            ->first();
    }
}
