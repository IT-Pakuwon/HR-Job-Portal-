<?php

namespace App\Console\Commands;

use App\Models\TrPerizinan;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendPerizinanRenewalReminder extends Command
{
    protected $signature = 'email:perizinan-renewal-reminder';

    protected $description = 'Send permit renewal reminders at 90, 75, 60, 45, and 30 days before expiry';

    private const REMINDER_DAYS = [90, 75, 60, 45, 30];

    public function handle(): int
    {
        $today = today();
        $targetDates = collect(self::REMINDER_DAYS)
            ->map(fn (int $days) => $today->copy()->addDays($days)->toDateString())
            ->all();

        $permits = TrPerizinan::query()
            ->with('site')
            ->where('expired_date', true)
            ->whereNotNull('enddate')
            ->whereNotNull('user_peminta')
            ->whereNull('deleted_at')
            ->whereRaw(
                'enddate::date IN ('.implode(',', array_fill(0, count($targetDates), '?')).')',
                $targetDates
            )
            // A renewal record means the requester has already acted. Its current
            // status does not matter; no subsequent reminder is sent for the source.
            ->whereDoesntHave('renewals')
            ->orderBy('user_peminta')
            ->orderBy('enddate')
            ->get();

        $documentsByRequester = [];

        foreach ($permits as $permit) {
            $daysRemaining = (int) $today->diffInDays(Carbon::parse($permit->enddate)->startOfDay(), false);
            if (!in_array($daysRemaining, self::REMINDER_DAYS, true)) {
                continue;
            }

            $cacheKey = $this->cacheKey($permit->perizinan_id, $daysRemaining);
            if (Cache::has($cacheKey)) {
                continue;
            }

            $username = trim((string) $permit->user_peminta);
            if ($username === '') {
                continue;
            }

            $documentsByRequester[$username][] = [
                'perizinan_id' => $permit->perizinan_id,
                'title' => $permit->perizinan_title ?: '-',
                'category' => $permit->perizinan_category ?: '-',
                'company' => $permit->cpny_id ?: '-',
                'site' => $permit->site?->site_name ?: ($permit->site_id ?: '-'),
                'end_date' => Carbon::parse($permit->enddate)->format('d F Y'),
                'days_remaining' => $daysRemaining,
                'cache_key' => $cacheKey,
            ];
        }

        if ($documentsByRequester === []) {
            $this->info('No permit renewal reminders due today.');

            return self::SUCCESS;
        }

        $users = User::query()
            ->whereIn('username', array_keys($documentsByRequester))
            ->where('status', 'A')
            ->get(['username', 'name', 'email', 'notification_email'])
            ->keyBy('username');

        $sent = 0;
        $failed = 0;

        foreach ($documentsByRequester as $username => $documents) {
            $user = $users->get($username);
            $recipient = trim((string) ($user?->notification_email ?: $user?->email));

            if (!$user || $recipient === '') {
                $this->warn("No active email address for requester: {$username}");
                continue;
            }

            try {
                Mail::send('emails.perizinan-renewal-reminder', [
                    'name' => $user->name ?: $user->username,
                    'documents' => $documents,
                    'permitUrl' => url('/perizinan'),
                ], function ($message) use ($recipient, $documents) {
                    $message->to($recipient)
                        ->subject('Permit Renewal Reminder - '.count($documents).' Permit(s)')
                        ->from(config('mail.from.address'), config('app.name'));
                });

                foreach ($documents as $document) {
                    Cache::put($document['cache_key'], true, now()->addDays(2));
                }

                $sent++;
                $this->info("Permit reminder sent to {$recipient}");
            } catch (\Throwable $exception) {
                $failed++;
                report($exception);
                $this->error("Failed to send reminder to {$username}: {$exception->getMessage()}");
            }
        }

        $this->info("Finished. Email sent: {$sent}, failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function cacheKey(string $permitId, int $daysRemaining): string
    {
        return "perizinan-renewal-reminder:{$permitId}:{$daysRemaining}";
    }
}
