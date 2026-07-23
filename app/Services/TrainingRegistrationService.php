<?php

namespace App\Services;

use App\Models\TrTrainingRegistration;
use App\Models\TrTrainingScheduleDetail;

class TrainingRegistrationService
{
    /**
     * Freed by a self-cancel of an Approved registration: only auto-offer while
     * the schedule is still open for registration (pre-D-3). Once CLOSED, the
     * slot sits forfeited until an HCDEVACCESS user manually offers it.
     */
    public static function promoteWaitlistIfOpen(TrTrainingRegistration $cancelled): void
    {
        $detail = TrTrainingScheduleDetail::find($cancelled->schedule_detail_id);

        if (!$detail || $detail->status !== 'PUBLISHED') {
            return;
        }

        $next = self::nextWaitlisted($cancelled->schedule_detail_id, $cancelled->cpny_id);

        if ($next) {
            self::offerSlot($next);
        }
    }

    /**
     * Continuation of an already-initiated offer round (auto pre-D-3, or a
     * manual HCDEVACCESS offer post-D-3) — cascades regardless of schedule
     * status, since the slot was already carved out for this specific queue.
     */
    public static function cascadeToNextWaitlist(TrTrainingRegistration $declinedOrExpired): void
    {
        $next = self::nextWaitlisted($declinedOrExpired->schedule_detail_id, $declinedOrExpired->cpny_id);

        if ($next) {
            self::offerSlot($next);
        }
    }

    public static function offerSlot(TrTrainingRegistration $registration): void
    {
        $now = now();

        $registration->status = TrTrainingRegistration::STATUS_OFFERED;
        $registration->offered_at = $now;
        $registration->offer_expires_at = $now->copy()->addHours(24);
        $registration->updated_by = 'system';
        $registration->updated_at = $now;
        $registration->save();

        TrainingWaitlistNotifier::sendOffer($registration->fresh(['scheduleDetail.schedule.training']));
    }

    private static function nextWaitlisted(int $scheduleDetailId, string $cpnyId): ?TrTrainingRegistration
    {
        return TrTrainingRegistration::where('schedule_detail_id', $scheduleDetailId)
            ->where('cpny_id', $cpnyId)
            ->where('status', TrTrainingRegistration::STATUS_WAITLISTED)
            ->orderBy('created_at')
            ->lockForUpdate()
            ->first();
    }
}
