<?php

namespace App\Services;

use App\Models\SysUserRole;
use App\Models\TrLndTrainingRegistration;
use App\Models\TrMessage;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;

class TrainingWaitlistNotifier
{
    public static function sendOffer(TrLndTrainingRegistration $registration): void
    {
        $trainingName = $registration->schedule?->schedule?->training?->training_name ?? 'Training';
        $scheduleDate = $registration->schedule_date ?? $registration->schedule?->schedule_date;
        $scheduleDateLabel = $scheduleDate?->format('d M Y');

        $eid = Hashids::encode($registration->id);
        $url = url('/training-list/my/' . $eid);

        TrMessage::create([
            'refnbr' => $registration->training_regist_id,
            'doctype' => 'TRN',
            'message_date' => now(),
            'message_type' => 'S_OFFER',
            'cpny_id' => $registration->cpny_id,
            'department_id' => $registration->department_id,
            'username' => 'system',
            'name' => 'System',
            'message' => "A slot is available for {$trainingName}" . ($scheduleDateLabel ? " ({$scheduleDateLabel})" : '') . '. You have 24 hours to confirm before this slot is offered to the next person on the waiting list.',
            'status' => 'A',
            'created_by' => 'system',
        ]);

        $user = User::where('username', $registration->user_registration)->where('status', 'A')->first();
        $to = $user ? ($user->notification_email ?: $user->email) : null;

        if (!$to) {
            return;
        }

        Mail::send('emails.trainingwaitlistoffer', [
            'name' => $user->name ?: $user->username,
            'docid' => $registration->training_regist_id,
            'training_name' => $trainingName,
            'schedule_date' => $scheduleDate,
            'expires_at' => $registration->offer_expires_at,
            'url' => $url,
        ], function ($m) use ($to, $registration) {
            $m->to($to)
                ->subject($registration->training_regist_id . ' - Training Slot Available (Waiting List)')
                ->from(config('mail.from.address'), config('app.name'));
        });
    }

    /**
     * HCDEV-wide notice when a participant responds to their waitlist offer.
     * TrMessage doctype 'TRN' is registered in DocumentNotificationService's
     * extendedDocTypeConfig(), so this also surfaces in the bell for the
     * creator, current approval line, and HCDEVACCESS holders.
     */
    public static function notifyHcdevOfferResponse(TrLndTrainingRegistration $registration, bool $accepted): void
    {
        $trainingName = $registration->schedule?->schedule?->training?->training_name ?? 'Training';
        $scheduleDate = $registration->schedule_date ?? $registration->schedule?->schedule_date;
        $scheduleDateLabel = $scheduleDate?->format('d M Y');
        $participant = User::where('username', $registration->user_registration)->first();
        $participantName = $participant->name ?? $registration->user_registration;

        $eid = Hashids::encode($registration->id);
        $url = url('/training-list/my/' . $eid);
        $verb = $accepted ? 'accepted' : 'declined';

        TrMessage::create([
            'refnbr' => $registration->training_regist_id,
            'doctype' => 'TRN',
            'message_date' => now(),
            'message_type' => 'S_OFFRESP',
            'cpny_id' => $registration->cpny_id,
            'department_id' => $registration->department_id,
            'username' => 'system',
            'name' => 'System',
            'message' => "{$participantName} has {$verb} the waiting list slot for {$trainingName}" . ($scheduleDateLabel ? " ({$scheduleDateLabel})" : '') . '.',
            'status' => 'A',
            'created_by' => 'system',
        ]);

        $hcdevUsernames = SysUserRole::where('role_id', 'HCDEVACCESS')->where('status', 'A')->pluck('username');

        if ($hcdevUsernames->isEmpty()) {
            return;
        }

        $recipients = User::whereIn('username', $hcdevUsernames)->where('status', 'A')->get();

        foreach ($recipients as $recipient) {
            $to = $recipient->notification_email ?: $recipient->email;

            if (!$to) {
                continue;
            }

            Mail::send('emails.trainingofferresponse', [
                'name' => $recipient->name ?: $recipient->username,
                'docid' => $registration->training_regist_id,
                'participant_name' => $participantName,
                'training_name' => $trainingName,
                'schedule_date' => $scheduleDate,
                'accepted' => $accepted,
                'url' => $url,
            ], function ($m) use ($to, $registration, $accepted) {
                $m->to($to)
                    ->subject($registration->training_regist_id . ' - Waiting List ' . ($accepted ? 'Accepted' : 'Declined'))
                    ->from(config('mail.from.address'), config('app.name'));
            });
        }
    }

    /**
     * Notify the batch submitter (created_by) when HCDEV manually seats a
     * waitlisted participant post-close. Also surfaces in the bell via the
     * same TRN doctype registration as notifyHcdevOfferResponse().
     */
    public static function notifyCreatorManualAccept(TrLndTrainingRegistration $registration, string $actorName): void
    {
        $trainingName = $registration->schedule?->schedule?->training?->training_name ?? 'Training';
        $scheduleDate = $registration->schedule_date ?? $registration->schedule?->schedule_date;
        $scheduleDateLabel = $scheduleDate?->format('d M Y');
        $participant = User::where('username', $registration->user_registration)->first();
        $participantName = $participant->name ?? $registration->user_registration;

        $eid = Hashids::encode($registration->id);
        $url = url('/training-list/my/' . $eid);

        TrMessage::create([
            'refnbr' => $registration->training_regist_id,
            'doctype' => 'TRN',
            'message_date' => now(),
            'message_type' => 'S_MANACC',
            'cpny_id' => $registration->cpny_id,
            'department_id' => $registration->department_id,
            'username' => 'system',
            'name' => 'System',
            'message' => "{$participantName} was accepted from the waiting list for {$trainingName}" . ($scheduleDateLabel ? " ({$scheduleDateLabel})" : '') . " by {$actorName}.",
            'status' => 'A',
            'created_by' => 'system',
        ]);

        $creator = User::where('username', $registration->created_by)->where('status', 'A')->first();

        if (!$creator) {
            return;
        }

        $to = $creator->notification_email ?: $creator->email;

        if (!$to) {
            return;
        }

        Mail::send('emails.trainingmanualaccept', [
            'name' => $creator->name ?: $creator->username,
            'docid' => $registration->training_regist_id,
            'participant_name' => $participantName,
            'training_name' => $trainingName,
            'schedule_date' => $scheduleDate,
            'url' => $url,
        ], function ($m) use ($to, $registration) {
            $m->to($to)
                ->subject($registration->training_regist_id . ' - Participant Accepted from Waiting List')
                ->from(config('mail.from.address'), config('app.name'));
        });
    }

    /**
     * A PUBLISHED/CLOSED schedule's date moved while this registration still
     * held a seat/waitlist slot/offer. Unlike the other notices here this
     * carries a cancel link front and center — a kept seat that silently
     * follows the new date can strand someone who can't make it.
     */
    public static function notifyReschedule(TrLndTrainingRegistration $registration, ?string $oldDate, string $newDate, string $reason): void
    {
        $trainingName = $registration->schedule?->schedule?->training?->training_name ?? 'Training';

        $eid = Hashids::encode($registration->id);
        $url = url('/training-list/my/' . $eid);

        TrMessage::create([
            'refnbr' => $registration->training_regist_id,
            'doctype' => 'TRN',
            'message_date' => now(),
            'message_type' => 'S_RESCHED',
            'cpny_id' => $registration->cpny_id,
            'department_id' => $registration->department_id,
            'username' => 'system',
            'name' => 'System',
            'message' => "The schedule for {$trainingName} has changed" . ($oldDate ? " from {$oldDate}" : '') . " to {$newDate}. Reason: {$reason}",
            'status' => 'A',
            'created_by' => 'system',
        ]);

        $user = User::where('username', $registration->user_registration)->where('status', 'A')->first();
        $to = $user ? ($user->notification_email ?: $user->email) : null;

        if (!$to) {
            return;
        }

        Mail::send('emails.trainingreschedule', [
            'name' => $user->name ?: $user->username,
            'docid' => $registration->training_regist_id,
            'training_name' => $trainingName,
            'old_date' => $oldDate,
            'new_date' => $newDate,
            'reason' => $reason,
            'url' => $url,
            'systemLabel' => 'Learning & Development System',
        ], function ($m) use ($to, $registration) {
            $m->to($to)
                ->subject($registration->training_regist_id . ' - Training Schedule Changed')
                ->from(config('mail.from.address'), config('app.name'));
        });
    }

    /**
     * One-shot notice (see NotifyCertificateReady command) that a
     * participant's certificate has crossed the H+1 eligibility window and
     * can now be downloaded from My Registration. No file is attached — the
     * certificate itself is only ever rendered on demand.
     */
    public static function notifyCertificateReady(TrLndTrainingRegistration $registration): void
    {
        $trainingName = $registration->schedule?->schedule?->training?->training_name ?? 'Training';
        $scheduleDate = $registration->schedule_date ?? $registration->schedule?->schedule_date;
        $scheduleDateLabel = $scheduleDate?->format('d M Y');

        $eid = Hashids::encode($registration->id);
        $url = url('/training-list/my/' . $eid);

        TrMessage::create([
            'refnbr' => $registration->training_regist_id,
            'doctype' => 'TRN',
            'message_date' => now(),
            'message_type' => 'S_CERTRDY',
            'cpny_id' => $registration->cpny_id,
            'department_id' => $registration->department_id,
            'username' => 'system',
            'name' => 'System',
            'message' => "The certificate for {$trainingName}" . ($scheduleDateLabel ? " ({$scheduleDateLabel})" : '') . ' is now available for download.',
            'status' => 'A',
            'created_by' => 'system',
        ]);

        $user = User::where('username', $registration->user_registration)->where('status', 'A')->first();
        $to = $user ? ($user->notification_email ?: $user->email) : null;

        if (!$to) {
            return;
        }

        Mail::send('emails.trainingcertificateready', [
            'name' => $user->name ?: $user->username,
            'docid' => $registration->training_regist_id,
            'training_name' => $trainingName,
            'schedule_date' => $scheduleDate,
            'url' => $url,
        ], function ($m) use ($to, $registration) {
            $m->to($to)
                ->subject($registration->training_regist_id . ' - Training Certificate Available')
                ->from(config('mail.from.address'), config('app.name'));
        });
    }
}
