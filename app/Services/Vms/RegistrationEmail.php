<?php

namespace App\Services\Vms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrationEmail
{
    public function enqueue(string $activity, string $event, array $values, array $payload): ?int
    {
        $db = DB::connection('mysql7');
        if (! (bool) $db->table('email_activity_settings')->where('activity_key', $activity)->value('enabled')) {
            return null;
        }
        $to = collect($values)->flatMap(fn ($v) => preg_split('/[,;]/', (string) $v))->map(fn ($v) => mb_strtolower(trim($v)))->filter(fn ($v) => filter_var($v, FILTER_VALIDATE_EMAIL))->unique()->values()->all();
        if (! $to) {
            return null;
        }
        $inserted = $db->table('email_activity_deliveries')->insertOrIgnore(['activity_key' => $activity, 'event_key' => $event, 'recipients' => json_encode(['to' => $to, 'cc' => [], 'bcc' => []], JSON_THROW_ON_ERROR), 'payload' => json_encode($payload, JSON_THROW_ON_ERROR), 'status' => 'pending', 'attempts' => 0, 'available_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        return $inserted ? (int) $db->table('email_activity_deliveries')->where('event_key', $event)->value('id') : null;
    }

    public function deliver(?int $id): void
    {
        if (! $id) {
            return;
        }
        $db = DB::connection('mysql7');
        $row = $db->table('email_activity_deliveries')->where('id', $id)->first();
        if (! $row) {
            return;
        }
        if (! (bool) $db->table('email_activity_settings')->where('activity_key', $row->activity_key)->value('enabled')) {
            $db->table('email_activity_deliveries')->where('id', $id)->where('status', 'pending')->update(['status' => 'suppressed', 'updated_at' => now()]);

            return;
        }
        if (! $db->table('email_activity_deliveries')->where('id', $id)->where('status', 'pending')->update(['status' => 'sending', 'attempts' => $db->raw('attempts + 1'), 'updated_at' => now()])) {
            return;
        }
        try {
            $details = json_decode($row->payload, true, 512, JSON_THROW_ON_ERROR);
            $approved = $row->activity_key === 'vendor_account_approved';
            $profile = $row->activity_key === 'vendor_profile_review';
            $title = $profile ? 'Hasil Review Profil Vendor' : ($approved ? 'Registrasi Berhasil' : 'Registrasi Ditolak');
            $portalUrl = rtrim((string) config('vms.portal_url'), '/').'/login';
            Mail::send($profile ? 'emails.vms.profile-review' : 'emails.vms.registration', compact('details', 'approved', 'title', 'portalUrl'), function ($message) use ($row, $title) {
                $recipients = json_decode($row->recipients, true, 512, JSON_THROW_ON_ERROR);
                $message->to($recipients['to'])->subject($title.' - Pakuwon Group');
            });
            $db->table('email_activity_deliveries')->where('id', $id)->update(['status' => 'sent', 'sent_at' => now(), 'error_type' => null, 'updated_at' => now()]);
        } catch (\Throwable $e) {
            $db->table('email_activity_deliveries')->where('id', $id)->update(['status' => 'failed', 'error_type' => get_class($e), 'available_at' => now()->addMinutes(5), 'updated_at' => now()]);
            Log::warning('VMS registration email failed', ['delivery_id' => $id, 'exception_type' => get_class($e)]);
        }
    }
}
