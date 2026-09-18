<?php

namespace App\Console\Commands;

use App\Http\Controllers\LegalAgreementController;
use App\Models\TrAgreement;
use Illuminate\Console\Command;

class ProcessAgreementFollowups extends Command
{
    protected $signature = 'agreement:process-followups';

    protected $description = 'Send Surat 1 / Surat 2 / escalation for legal agreements past their H+14/H+14/H+7 calendar-day thresholds';

    public function handle(LegalAgreementController $controller): int
    {
        $agreements = TrAgreement::query()
            ->where('agreement_step_id', 'ACTIVE')
            ->where('status', 'P')
            ->whereNotNull('psm_or_addendum_delivery_date')
            ->get();

        $sentSurat1 = 0;
        $sentSurat2 = 0;
        $escalated = 0;
        $failed = 0;

        foreach ($agreements as $agreement) {
            try {
                // Same cycle/threshold logic the list UI uses to show "Day X
                // of Y" — kept in one place (agreementCycleInfo()) so the
                // display and the trigger can never disagree.
                $info = $controller->agreementCycleInfo($agreement);

                if (!$info['cycle'] || $info['days_elapsed'] === null || $info['days_elapsed'] < $info['days_threshold']) {
                    continue;
                }

                if ($info['cycle'] === 'AWAL') {
                    $controller->sendSurat1($agreement, 'system');
                    $sentSurat1++;
                    $this->info("Surat 1 sent for {$agreement->agreement_id}");
                } elseif ($info['cycle'] === 'REMINDER1') {
                    $controller->sendSurat2($agreement, 'system');
                    $sentSurat2++;
                    $this->info("Surat 2 sent for {$agreement->agreement_id}");
                } elseif ($info['cycle'] === 'REMINDER2') {
                    $controller->sendEscalationEmail($agreement, 'system');
                    $escalated++;
                    $this->info("Escalated {$agreement->agreement_id}");
                }
            } catch (\Throwable $e) {
                $failed++;
                report($e);
                $this->error("Failed processing {$agreement->agreement_id}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Surat 1: {$sentSurat1}, Surat 2: {$sentSurat2}, Escalated: {$escalated}, Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
