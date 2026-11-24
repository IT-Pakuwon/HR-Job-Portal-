<?php

namespace App\Console\Commands;

use App\Models\TrAgreement;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;

/**
 * Every 7 days (7, 14, 21 ... days after the agreement was created) an Active PSM / OLA /
 * Addendum / Others that still has documents not received reminds its creator and PIC Legal(s).
 * One email per person, listing each of their agreements and its missing documents.
 * Stateless on purpose (no new columns): the day count decides, the cache only stops a double run.
 */
class SendAgreementDocumentReminder extends Command
{
    protected $signature = 'email:agreement-document-reminder {--date= : Run as of this date (Y-m-d), for testing} {--dry : List who would be emailed, send nothing}';

    protected $description = 'Remind creator + PIC Legal every 7 days about agreement documents not received yet';

    public const INTERVAL_DAYS = 7;

    public const TYPES = ['PSM', 'OLA', 'ADDENDUM', 'OTHERS'];

    private const URL_PREFIX = ['ADDENDUM' => 'addendum/', 'OTHERS' => 'others/'];

    public function handle(): int
    {
        $today = $this->option('date') ? Carbon::parse($this->option('date'))->startOfDay() : today();

        $agreements = TrAgreement::query()
            ->whereNull('deleted_at')
            ->whereIn('agreement_type', self::TYPES)
            ->where('agreement_step_id', 'ACTIVE')
            ->whereNotNull('agreement_date')
            ->whereHas('documents', fn ($q) => $q->whereNull('deleted_at')->where('agreementdocument_received', false))
            ->with(['documents' => fn ($q) => $q->whereNull('deleted_at')->where('agreementdocument_received', false)->orderBy('id')])
            ->get();

        $byUser = [];

        foreach ($agreements as $agreement) {
            $days = (int) Carbon::parse($agreement->agreement_date)->startOfDay()->diffInDays($today, false);

            if ($days <= 0 || $days % self::INTERVAL_DAYS !== 0) {
                continue;
            }

            $key = "agreement-doc-reminder:{$agreement->agreement_id}:{$today->toDateString()}";

            if (!$this->option('dry') && Cache::has($key)) {
                continue;
            }

            $item = [
                'agreement_id' => $agreement->agreement_id,
                'label' => ['ADDENDUM' => 'Addendum', 'OTHERS' => 'Others'][$agreement->agreement_type] ?? 'PSM / OLA',
                'tenant' => $agreement->trade_name ?: $agreement->business_name,
                'unit' => collect([$agreement->floor_id, $agreement->unit_id])->filter()->implode(' - '),
                'days' => $days,
                'url' => url('/legal-new-agreement/'.(self::URL_PREFIX[$agreement->agreement_type] ?? '').Hashids::encode($agreement->id)),
                'documents' => $agreement->documents->pluck('agreementdocument_descr')->map(fn ($d) => trim((string) $d))->all(),
                'cache_key' => $key,
            ];

            $people = collect([$agreement->created_user])->merge($agreement->picLegalList())->filter()
                ->unique(fn ($u) => strtolower($u));

            foreach ($people as $username) {
                $byUser[strtolower($username)][] = $item;
            }
        }

        if (!$byUser) {
            $this->info('No agreement document reminders due today.');

            return self::SUCCESS;
        }

        $users = User::query()->whereIn(DB::raw('lower(username)'), array_keys($byUser))->where('status', 'A')->get()
            ->keyBy(fn ($u) => strtolower($u->username));

        $sent = $failed = 0;
        $done = [];

        foreach ($byUser as $username => $items) {
            $user = $users->get($username);
            $email = trim((string) ($user?->notification_email ?: $user?->email));

            if ($email === '') {
                $this->warn("No active email address for: {$username}");

                continue;
            }

            if ($this->option('dry')) {
                $this->line("[dry] {$username} <{$email}>: ".implode(', ', array_map(fn ($i) => $i['agreement_id'].' ('.count($i['documents']).' docs)', $items)));

                continue;
            }

            try {
                Mail::send('emails.agreement-document-reminder', [
                    'name' => $user->name ?: $user->username,
                    'items' => $items,
                ], function ($message) use ($email, $items) {
                    $message->to($email)
                        ->subject('[LEGAL AGREEMENT][REMINDER] Documents not received - '.count($items).' agreement(s)')
                        ->from(config('mail.from.address'), config('app.name'));
                });

                foreach ($items as $i) {
                    $done[$i['cache_key']] = true;
                }

                $sent++;
                $this->info("Reminder sent to {$email}");
            } catch (\Throwable $e) {
                $failed++;
                report($e);
                $this->error("Failed to send to {$username}: {$e->getMessage()}");
            }
        }

        foreach (array_keys($done) as $key) {
            Cache::put($key, true, now()->addDays(2));
        }

        $this->info("Finished. Email sent: {$sent}, failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
