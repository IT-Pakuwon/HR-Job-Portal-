<?php

namespace App\Console\Commands;

use App\Models\TrRfp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;

class SendEmailRfpKontrakSubmitReminder extends Command
{
    protected $signature = 'email:rfp-kontrak-submit-reminder';
    protected $description = 'Send reminder email to requesters for RFP Kontrak waiting to be submitted';

    public function handle()
    {
        $this->info('Start SendEmailRfpKontrakSubmitReminder...');

        $rfps = TrRfp::query()
            ->where('status', 'H')
            ->whereRaw("UPPER(TRIM(COALESCE(type_po, ''))) = ?", ['KONTRAK'])
            ->whereNotNull('user_peminta')
            ->whereRaw("TRIM(COALESCE(user_peminta, '')) <> ''")
            ->orderBy('user_peminta')
            ->orderBy('rfp_id')
            ->get();

        if ($rfps->isEmpty()) {
            $this->info('No hold RFP Kontrak found.');
            return self::SUCCESS;
        }

        $summaryByRequester = [];

        foreach ($rfps as $rfp) {
            $requester = trim((string) $rfp->user_peminta);

            if ($requester === '') {
                $this->warn('user_peminta kosong untuk RFP: ' . $rfp->rfp_id);
                continue;
            }

            $irNote = trim((string) ($rfp->ir_note ?? ''));
            $purpose = strlen($irNote) >= 5 ? $irNote : ($rfp->keperluan ?? '-');
            $hash = Hashids::encode($rfp->id);

            $summaryByRequester[$requester][] = [
                'rfp_id' => $rfp->rfp_id,
                'cpny_id' => $rfp->cpny_id ?? '-',
                'department_id' => $rfp->department_id ?? '-',
                'rfp_date' => $rfp->rfp_date ? Carbon::parse($rfp->rfp_date)->format('d-m-Y') : '-',
                'vendor_name' => $rfp->vendor_name ?? '-',
                'purpose' => $purpose,
                'amount' => (float) ($rfp->rfp_amount ?? 0),
                'url' => url('/createrfpkontrakbudget/' . $hash),
            ];
        }

        if (empty($summaryByRequester)) {
            $this->info('No valid requester found.');
            return self::SUCCESS;
        }

        $users = User::query()
            ->whereIn('username', array_keys($summaryByRequester))
            ->where('status', 'A')
            ->whereNotNull('notification_email')
            ->where('notification_email', '<>', '')
            ->get()
            ->keyBy('username');

        $sent = 0;
        $failed = 0;

        foreach ($summaryByRequester as $username => $documents) {
            try {
                $user = $users->get($username);

                if (!$user) {
                    $this->warn('No active email recipient for user_peminta: ' . $username);
                    continue;
                }

                $data = [
                    'name' => $user->name ?: $user->username,
                    'username' => $user->username,
                    'total' => count($documents),
                    'documents' => $documents,
                ];

                Mail::send('emails.rfp-kontrak-submit-reminder', $data, function ($message) use ($data, $user) {
                    $message->to($user->notification_email)
                        ->subject('Reminder Submit RFP Kontrak - Total ' . $data['total'] . ' Document(s)')
                        ->from(config('mail.from.address'), config('app.name'));
                });

                $sent++;
                $this->info('Reminder sent to ' . $user->notification_email);
            } catch (\Throwable $e) {
                $failed++;
                $this->error('Failed send reminder to ' . $username . ': ' . $e->getMessage());
            }
        }

        $this->info("Finished. Email Sent: {$sent}, Failed: {$failed}");

        return self::SUCCESS;
    }
}
