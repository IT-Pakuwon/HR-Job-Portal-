<?php

namespace App\Mail;

use App\Mail\Concerns\RendersAgreementLetters;
use App\Models\TrAgreement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgreementSurat2Mail extends Mailable
{
    use Queueable, SerializesModels, RendersAgreementLetters;

    public $agreement;

    public $surat1SentDate;

    /**
     * $surat1SentDate is when Surat 1 actually went out. There's no column
     * yet recording that (the H+14/H+14/H+7 engine isn't built), so callers
     * without a real value should pass psm_or_addendum_delivery_date + 14
     * days — the date Surat 1 should have gone out under the rule — rather
     * than leave this null.
     */
    public function __construct(
        TrAgreement $agreement,
        $surat1SentDate
    ) {
        $this->agreement = $agreement;
        $this->surat1SentDate = $surat1SentDate;
    }

    public function build()
    {
        $mail = $this
            ->subject(
                '[LEGAL AGREEMENT][SURAT 2] '
                . $this->agreement->agreement_id
                . ' - Pengingat Terakhir Pengembalian Dokumen PSM/Addendum'
            )
            ->view('emails.agreement-surat2');

        $mail->attachData(
            $this->renderSurat2Pdf($this->agreement, now(), $this->surat1SentDate),
            'Surat-2-'.$this->agreement->agreement_id.'.pdf',
            ['mime' => 'application/pdf']
        );

        $mail->attachData(
            $this->renderSurat1Pdf($this->agreement, $this->surat1SentDate),
            'Surat-1-'.$this->agreement->agreement_id.'.pdf',
            ['mime' => 'application/pdf']
        );

        return $mail;
    }
}
