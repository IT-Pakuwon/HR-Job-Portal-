<?php

namespace App\Services;

use App\Mail\AgreementActivatedMail;
use App\Mail\AgreementCompletedMail;
use App\Mail\AgreementCreatedMail;
use App\Mail\AgreementEscalationMail;
use App\Mail\AgreementHoldMail;
use App\Mail\AgreementSurat1Mail;
use App\Mail\AgreementSurat2Mail;
use App\Models\TrAgreement;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LegalAgreementNotificationService
{
    protected function getUserEmail(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return $user->notification_email
            ?: $user->email;
    }

    protected function emailsForUsernames(\Illuminate\Support\Collection $usernames): \Illuminate\Support\Collection
    {
        $emails = collect();

        $users = User::query()
            ->whereIn('username', $usernames->filter()->unique()->values())
            ->where('status', 'A')
            ->get();

        foreach ($users as $user) {
            $email = $this->getUserEmail($user);

            if ($email) {
                $emails->push($email);
            }
        }

        return $emails->filter()->unique()->values();
    }

    protected function emailForUsername(?string $username): ?string
    {
        if (!$username) {
            return null;
        }

        return $this->emailsForUsernames(collect([$username]))->first();
    }

    protected function creatorEmail(TrAgreement $agreement): ?string
    {
        return $this->emailForUsername($agreement->created_user);
    }

    protected function picLegalEmails(TrAgreement $agreement): array
    {
        return $this->emailsForUsernames(collect($agreement->picLegalList()))->all();
    }

    protected function picLeasingEmails(TrAgreement $agreement): array
    {
        return $this->emailsForUsernames(collect($agreement->picLeasingList()))->all();
    }

    /**
     * Sends one real email with proper To/Cc/Bcc headers — as opposed to
     * looping over a flat recipient list and sending N separate copies
     * (the old approach, where every recipient saw themselves as the sole
     * "To" and couldn't see who else was notified).
     */
    protected function sendAgreementMail(
        TrAgreement $agreement,
        $mailable,
        $to,
        array $cc = [],
        array $bcc = [],
        string $logLabel = 'Agreement Mail'
    ): void {
        $to = is_array($to) ? array_values(array_filter($to)) : $to;

        if (empty($to)) {
            Log::warning("$logLabel: no primary (To) recipient resolved, mail not sent", [
                'agreement_id' => $agreement->agreement_id,
            ]);

            return;
        }

        try {
            $mail = Mail::to($to);

            if (!empty($cc)) {
                $mail->cc($cc);
            }

            if (!empty($bcc)) {
                $mail->bcc($bcc);
            }

            $mail->send($mailable);
        } catch (\Throwable $e) {
            Log::error("$logLabel Failed", [
                'agreement_id' => $agreement->agreement_id,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Creator + PIC Legal only — used just for comment notifications, which
     * still get a flat recipient list (a comment reply doesn't have a
     * single obvious "To"). Every other agreement email uses
     * sendAgreementMail() with a real To/Cc/Bcc split instead.
     */
    protected function recipientEmails(TrAgreement $agreement): \Illuminate\Support\Collection
    {
        return $this->emailsForUsernames(
            collect([$agreement->created_user])
                ->merge($agreement->picLegalList())
        );
    }

    public function agreementCreated(
        TrAgreement $agreement
    ) {
        $this->sendAgreementMail(
            $agreement,
            new AgreementCreatedMail($agreement),
            $this->creatorEmail($agreement),
            $this->picLeasingEmails($agreement),
            $this->picLegalEmails($agreement),
            'Agreement Created Mail'
        );
    }

    public function agreementHeld(
        TrAgreement $agreement
    ) {
        $this->sendAgreementMail(
            $agreement,
            new AgreementHoldMail($agreement),
            $this->creatorEmail($agreement),
            $this->picLeasingEmails($agreement),
            $this->picLegalEmails($agreement),
            'Agreement Hold Mail'
        );
    }

    public function agreementActivated(
        TrAgreement $agreement
    ) {
        $this->sendAgreementMail(
            $agreement,
            new AgreementActivatedMail($agreement),
            $this->creatorEmail($agreement),
            $this->picLeasingEmails($agreement),
            $this->picLegalEmails($agreement),
            'Agreement Activated Mail'
        );
    }

    /**
     * Surat 1 / Surat 2 both go to the tenant PIC directly (an external
     * recipient, unlike every other agreement email) in addition to
     * Creator + PIC Legal + PIC Leasing, since they're reminder letters
     * addressed to the tenant.
     */
    public function agreementSurat1(
        TrAgreement $agreement
    ) {
        $this->sendAgreementMail(
            $agreement,
            new AgreementSurat1Mail($agreement),
            $agreement->pic_email_penyewa,
            array_merge($this->picLegalEmails($agreement), $this->picLeasingEmails($agreement)),
            array_filter([$this->creatorEmail($agreement)]),
            'Agreement Surat 1 Mail'
        );
    }

    /**
     * $surat1SentDate: when Surat 1 actually went out. Pass the real
     * timestamp once it's tracked; until then callers should pass
     * psm_or_addendum_delivery_date + 14 days (see AgreementSurat2Mail).
     */
    public function agreementSurat2(
        TrAgreement $agreement,
        $surat1SentDate
    ) {
        $this->sendAgreementMail(
            $agreement,
            new AgreementSurat2Mail($agreement, $surat1SentDate),
            $agreement->pic_email_penyewa,
            array_merge($this->picLegalEmails($agreement), $this->picLeasingEmails($agreement)),
            array_filter([$this->creatorEmail($agreement)]),
            'Agreement Surat 2 Mail'
        );
    }

    /**
     * The automatic H+7-after-Surat-2 escalation notice — the only path to
     * ESCALATED now that manual escalation has been retired. To:
     * Marketing/Leasing (PIC Leasing) — they're the ones who need to act.
     * PIC Legal is Cc'd for visibility, Created User Bcc'd. No tenant on
     * this one. Attaches a reconstructed copy of Surat 2 for reference.
     */
    public function agreementEscalationNotice(
        TrAgreement $agreement,
        $surat1SentDate,
        $surat2SentDate
    ) {
        $this->sendAgreementMail(
            $agreement,
            new AgreementEscalationMail($agreement, $surat1SentDate, $surat2SentDate),
            $this->picLeasingEmails($agreement),
            $this->picLegalEmails($agreement),
            array_filter([$this->creatorEmail($agreement)]),
            'Agreement Escalation Notice Mail'
        );
    }

    public function agreementCompleted(
        TrAgreement $agreement
    ) {
        $this->sendAgreementMail(
            $agreement,
            new AgreementCompletedMail($agreement),
            $this->creatorEmail($agreement),
            $this->picLeasingEmails($agreement),
            $this->picLegalEmails($agreement),
            'Agreement Completed Mail'
        );
    }

    public function agreementCommented(
        TrAgreement $agreement,
        string $commenterUsername,
        string $message
    ): void {
        $commenter = User::query()
            ->where('username', $commenterUsername)
            ->first();

        $commenterEmail = $commenter
            ? $this->getUserEmail($commenter)
            : null;

        $commenterName = $commenter?->name ?? $commenterUsername;

        $emails = $this->recipientEmails($agreement)
            ->reject(fn ($email) => $email === $commenterEmail)
            ->values();

        foreach ($emails as $email) {
            try {
                Mail::to($email)->send(
                    new \App\Mail\CommentNotificationMail(
                        'AGR',
                        $agreement->agreement_id,
                        $commenterName,
                        $message,
                        'LEGAL AGREEMENT'
                    )
                );
            } catch (\Throwable $e) {
                Log::error('Agreement Comment Mail Failed', [
                    'agreement_id' => $agreement->agreement_id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
