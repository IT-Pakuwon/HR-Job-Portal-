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

        $eid = Hashids::encode($registration->id);
        $url = url('/training-list/my/' . $eid);

        TrMessage::create([
            'refnbr' => $registration->training_regist_id,
            'doctype' => 'TRN',
            'message_date' => now(),
            'message_type' => 'SYSTEM',
            'cpny_id' => $registration->cpny_id,
            'department_id' => $registration->department_id,
            'username' => 'system',
            'name' => 'System',
            'message' => "Slot tersedia untuk {$trainingName}" . ($scheduleDate ? " ({$scheduleDate})" : '') . '. Anda punya waktu 24 jam untuk konfirmasi sebelum slot ini ditawarkan ke orang berikutnya.',
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
                ->subject($registration->training_regist_id . ' - Slot Training Tersedia (Waiting List)')
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
        $participant = User::where('username', $registration->user_registration)->first();
        $participantName = $participant->name ?? $registration->user_registration;

        $eid = Hashids::encode($registration->id);
        $url = url('/training-list/my/' . $eid);
        $verb = $accepted ? 'menerima' : 'menolak';

        TrMessage::create([
            'refnbr' => $registration->training_regist_id,
            'doctype' => 'TRN',
            'message_date' => now(),
            'message_type' => 'SYSTEM',
            'cpny_id' => $registration->cpny_id,
            'department_id' => $registration->department_id,
            'username' => 'system',
            'name' => 'System',
            'message' => "{$participantName} telah {$verb} slot waiting list untuk {$trainingName}" . ($scheduleDate ? " ({$scheduleDate})" : '') . '.',
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
                    ->subject($registration->training_regist_id . ' - Waiting List ' . ($accepted ? 'Diterima' : 'Ditolak'))
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
        $participant = User::where('username', $registration->user_registration)->first();
        $participantName = $participant->name ?? $registration->user_registration;

        $eid = Hashids::encode($registration->id);
        $url = url('/training-list/my/' . $eid);

        TrMessage::create([
            'refnbr' => $registration->training_regist_id,
            'doctype' => 'TRN',
            'message_date' => now(),
            'message_type' => 'SYSTEM',
            'cpny_id' => $registration->cpny_id,
            'department_id' => $registration->department_id,
            'username' => 'system',
            'name' => 'System',
            'message' => "{$participantName} diterima dari waiting list untuk {$trainingName}" . ($scheduleDate ? " ({$scheduleDate})" : '') . " oleh {$actorName}.",
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
                ->subject($registration->training_regist_id . ' - Peserta Diterima dari Waiting List')
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
            'message_type' => 'SYSTEM',
            'cpny_id' => $registration->cpny_id,
            'department_id' => $registration->department_id,
            'username' => 'system',
            'name' => 'System',
            'message' => "Jadwal {$trainingName} diubah" . ($oldDate ? " dari {$oldDate}" : '') . " ke {$newDate}. Alasan: {$reason}",
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
                ->subject($registration->training_regist_id . ' - Jadwal Training Diubah')
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

        $eid = Hashids::encode($registration->id);
        $url = url('/training-list/my/' . $eid);

        TrMessage::create([
            'refnbr' => $registration->training_regist_id,
            'doctype' => 'TRN',
            'message_date' => now(),
            'message_type' => 'SYSTEM',
            'cpny_id' => $registration->cpny_id,
            'department_id' => $registration->department_id,
            'username' => 'system',
            'name' => 'System',
            'message' => "Sertifikat untuk {$trainingName}" . ($scheduleDate ? " ({$scheduleDate})" : '') . ' sudah tersedia untuk diunduh.',
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
                ->subject($registration->training_regist_id . ' - Sertifikat Training Tersedia')
                ->from(config('mail.from.address'), config('app.name'));
        });
    }
}
