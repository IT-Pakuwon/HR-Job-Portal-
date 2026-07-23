<?php

namespace App\Services;

use App\Models\TrTrainingRegistration;
use App\Models\TrTrainingScheduleDetail;
use App\Models\TrTrainingScheduleQuota;

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

    /**
     * Called right after a Waitlisted registrant's approval chain finishes —
     * a slot may have already opened up while they were mid-approval, and
     * this is the only trigger that would ever offer it to them in that case
     * (unlike promoteWaitlistIfOpen/cascadeToNextWaitlist, which only run off
     * a cancel/decline/expiry event and don't need to re-check quota math).
     */
    public static function offerIfSlotAlreadyFree(TrTrainingRegistration $approvedWaitlisted): void
    {
        $detail = TrTrainingScheduleDetail::find($approvedWaitlisted->schedule_detail_id);

        if (!$detail || $detail->status !== 'PUBLISHED') {
            return;
        }

        $quota = TrTrainingScheduleQuota::where('schedule_detail_id', $approvedWaitlisted->schedule_detail_id)
            ->where('cpny_id', $approvedWaitlisted->cpny_id)
            ->lockForUpdate()
            ->first();

        if (!$quota) {
            return;
        }

        $reserved = TrTrainingRegistration::where('schedule_detail_id', $approvedWaitlisted->schedule_detail_id)
            ->where('cpny_id', $approvedWaitlisted->cpny_id)
            ->whereIn('status', [TrTrainingRegistration::STATUS_PENDING, TrTrainingRegistration::STATUS_APPROVED])
            ->count();

        if ($reserved >= $quota->quota_pax) {
            return;
        }

        $next = self::nextWaitlisted($approvedWaitlisted->schedule_detail_id, $approvedWaitlisted->cpny_id);

        // Only this registrant, and only if they're actually first in line —
        // an earlier-queued, already-approved person takes the slot instead.
        if ($next && $next->id === $approvedWaitlisted->id) {
            self::offerSlot($next);
        }
    }

    /**
     * Only offers to Waitlisted rows whose approval has already completed
     * (approved_at set) — approval now starts the moment someone joins the
     * waitlist, so by the time a slot is offered it should already be
     * resolved; anyone still mid-approval is skipped until they finish.
     */
    private static function nextWaitlisted(int $scheduleDetailId, string $cpnyId): ?TrTrainingRegistration
    {
        return TrTrainingRegistration::where('schedule_detail_id', $scheduleDetailId)
            ->where('cpny_id', $cpnyId)
            ->where('status', TrTrainingRegistration::STATUS_WAITLISTED)
            ->whereNotNull('approved_at')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->first();
    }
}
