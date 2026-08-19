<?php

namespace App\Console\Commands;

use App\Mail\EventH7ReminderMail;
use App\Models\MsEvent;
use App\Models\MsEventLocation;
use App\Models\SysUserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventH7PaidReminder extends Command
{
    protected $signature = 'email:event-h7-paid-reminder';
    protected $description = 'Send H-7 reminder email for Paid events to the internal PIC, the creator, and GMACCESS users';

    public function handle()
    {
        $this->info('Start SendEventH7PaidReminder...');

        $targetDate = now()->addDays(7)->toDateString();

        $events = MsEvent::query()
            ->whereNull('deleted_at')
            ->where('status', 'A')
            ->where('event_status', 'Paid')
            ->whereDate('event_start_date', $targetDate)
            ->orderBy('event_start_date')
            ->get();

        if ($events->isEmpty()) {
            $this->info("No Paid events starting on {$targetDate} (H-7).");
            return self::SUCCESS;
        }

        // Same for every event this run, so resolve once.
        $gmUsernames = SysUserRole::query()
            ->where('role_id', 'GMACCESS')
            ->where('status', 'A')
            ->pluck('username');

        $gmEmails = User::query()
            ->whereIn('username', $gmUsernames)
            ->where('status', 'A')
            ->get()
            ->map(fn (User $u) => $this->resolveEmail($u))
            ->filter()
            ->values();

        $sent = 0;
        $failed = 0;

        foreach ($events as $event) {
            $emails = collect($gmEmails);

            // PIC (internal): pic_event is a comma-separated list of User.name values
            // (free-text entry is allowed on the form, so not every name resolves).
            $picNames = collect(explode(',', (string) $event->pic_event))
                ->map(fn ($n) => trim($n))
                ->filter()
                ->values();

            if ($picNames->isNotEmpty()) {
                $placeholders = implode(',', array_fill(0, $picNames->count(), '?'));

                $picUsers = User::query()
                    ->whereRaw("LOWER(TRIM(name)) IN ({$placeholders})", $picNames->map(fn ($n) => strtolower($n))->all())
                    ->where('status', 'A')
                    ->get();

                foreach ($picUsers as $picUser) {
                    if ($email = $this->resolveEmail($picUser)) {
                        $emails->push($email);
                    }
                }
            }

            if ($event->created_user) {
                $creator = User::query()
                    ->where('username', $event->created_user)
                    ->where('status', 'A')
                    ->first();

                if ($email = $this->resolveEmail($creator)) {
                    $emails->push($email);
                }
            }

            $emails = $emails->filter()->unique()->values();

            if ($emails->isEmpty()) {
                $this->warn('No recipients resolved for event '.$event->event_id);
                continue;
            }

            $location = MsEventLocation::query()
                ->where('cpny_id', $event->cpnyid)
                ->where('event_location_id', $event->event_location_id)
                ->first();

            foreach ($emails as $email) {
                try {
                    Mail::to($email)->send(
                        new EventH7ReminderMail(
                            $event,
                            optional($location)->event_location_name,
                            optional($event->company)->cpny_name
                        )
                    );
                    $sent++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->error('Failed reminder for event '.$event->event_id.' to '.$email.': '.$e->getMessage());
                }
            }

            $this->info('Reminder sent for event '.$event->event_id.' to '.$emails->implode(', '));
        }

        $this->info("Finished. Sent: {$sent}, Failed: {$failed}");

        return self::SUCCESS;
    }

    private function resolveEmail(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return $user->notification_email ?: $user->email;
    }
}
