<?php

namespace App\Services;

use App\Mail\AgreementActivatedMail;
use App\Mail\AgreementCompletedMail;
use App\Mail\AgreementCreatedMail;
use App\Mail\AgreementEscalatedMail;
use App\Mail\AgreementHoldMail;
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

    /**
     * Creator + PIC Legal only — PIC Leasing is deliberately not notified
     * on any of these agreement emails.
     */
    protected function recipientEmails(TrAgreement $agreement): \Illuminate\Support\Collection
    {
        $emails = collect();

        $usernames = collect([$agreement->created_user])
            ->merge($agreement->picLegalList())
            ->filter()
            ->unique();

        $users = User::query()
            ->whereIn('username', $usernames)
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

    public function agreementCreated(
        TrAgreement $agreement
    ) {
        foreach ($this->recipientEmails($agreement) as $email) {
            try {
                Mail::to($email)->send(
                    new AgreementCreatedMail($agreement)
                );
            } catch (\Throwable $e) {
                Log::error('Agreement Created Mail Failed', [
                    'agreement_id' => $agreement->agreement_id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function agreementHeld(
        TrAgreement $agreement
    ) {
        foreach ($this->recipientEmails($agreement) as $email) {
            try {
                Mail::to($email)->send(
                    new AgreementHoldMail($agreement)
                );
            } catch (\Throwable $e) {
                Log::error('Agreement Hold Mail Failed', [
                    'agreement_id' => $agreement->agreement_id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function agreementActivated(
        TrAgreement $agreement
    ) {
        foreach ($this->recipientEmails($agreement) as $email) {
            try {
                Mail::to($email)->send(
                    new AgreementActivatedMail($agreement)
                );
            } catch (\Throwable $e) {
                Log::error('Agreement Activated Mail Failed', [
                    'agreement_id' => $agreement->agreement_id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function agreementEscalated(
        TrAgreement $agreement
    ) {
        foreach ($this->recipientEmails($agreement) as $email) {
            try {
                Mail::to($email)->send(
                    new AgreementEscalatedMail($agreement)
                );
            } catch (\Throwable $e) {
                Log::error('Agreement Escalated Mail Failed', [
                    'agreement_id' => $agreement->agreement_id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function agreementCompleted(
        TrAgreement $agreement
    ) {
        foreach ($this->recipientEmails($agreement) as $email) {
            try {
                Mail::to($email)->send(
                    new AgreementCompletedMail($agreement)
                );
            } catch (\Throwable $e) {
                Log::error('Agreement Completed Mail Failed', [
                    'agreement_id' => $agreement->agreement_id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
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
            ->reject(fn($email) => $email === $commenterEmail)
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
                    'email'    => $email,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
    }
}
