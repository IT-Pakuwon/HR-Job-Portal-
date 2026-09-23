<?php

namespace App\Mail;

use App\Mail\Concerns\RendersAgreementLetters;
use App\Models\TrAgreement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Vinkla\Hashids\Facades\Hashids;

class AgreementEscalationMail extends Mailable
{
    use Queueable, SerializesModels, RendersAgreementLetters;

    public $agreement;

    public $surat1SentDate;

    public $surat2SentDate;

    public function __construct(
        TrAgreement $agreement,
        $surat1SentDate,
        $surat2SentDate
    ) {
        $this->agreement = $agreement;
        $this->surat1SentDate = $surat1SentDate;
        $this->surat2SentDate = $surat2SentDate;
    }

    public function build()
    {
        $mail = $this
            ->subject(
                '[LEGAL AGREEMENT][ESCALATED] '
                . $this->agreement->agreement_id
                . ' - Dokumen PSM/Addendum Belum Dikembalikan Setelah 2 Pengingat'
            )
            ->view('emails.agreement-escalation')
            ->with('docUrl', $this->docUrl());

        $mail->attachData(
            $this->renderSurat2Pdf($this->agreement, $this->surat2SentDate, $this->surat1SentDate),
            'Surat-2-'.$this->agreement->agreement_id.'.pdf',
            ['mime' => 'application/pdf']
        );

        return $mail;
    }

    protected function docUrl(): string
    {
        $eid = Hashids::encode($this->agreement->id);

        return url('/show-legal-agreement/'.$eid);
    }
}
