<?php

namespace App\Services;

use App\Models\TrMessage;
use App\Models\TrTrainingRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;

class TrainingWaitlistNotifier
{
    public static function sendOffer(TrTrainingRegistration $registration): void
    {
        $trainingName = optional(optional($registration->scheduleDetail)->schedule)->training->training_name
            ?? 'Training';
        $scheduleDate = optional($registration->scheduleDetail)->schedule_date;

        $eid = Hashids::encode($registration->id);
        $url = url('/training-list/my/' . $eid);

        TrMessage::create([
            'refnbr' => $registration->docid,
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

        $user = User::where('username', $registration->username)->where('status', 'A')->first();
        $to = $user ? ($user->notification_email ?: $user->email) : null;

        if (!$to) {
            return;
        }

        Mail::send('emails.trainingwaitlistoffer', [
            'name' => $user->name ?: $user->username,
            'docid' => $registration->docid,
            'training_name' => $trainingName,
            'schedule_date' => $scheduleDate,
            'expires_at' => $registration->offer_expires_at,
            'url' => $url,
        ], function ($m) use ($to, $registration) {
            $m->to($to)
                ->subject($registration->docid . ' - Slot Training Tersedia (Waiting List)')
                ->from(config('mail.from.address'), config('app.name'));
        });
    }
}
