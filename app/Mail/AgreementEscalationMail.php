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
        // PSM (Mall) / OLA (Office) / Addendum get the "Tindak Lanjut" letter; everything else keeps the old notice.
        $data = $this->psmOlaEmailData($this->agreement, withAddendum: true) + ['docUrl' => $this->docUrl(), 'systemLabel' => 'Legal Agreement'];
        $docLabel = $data['docLabel'];

        $mail = $this
            ->subject(
                '[LEGAL AGREEMENT][ESCALATED] '
                . $this->agreement->agreement_id
                . ($docLabel
                    ? ' - Tindak Lanjut Permohonan Pengembalian '.$docLabel
                    : ' - Dokumen PSM/Addendum Belum Dikembalikan Setelah 2 Pengingat')
            )
            ->view('emails.agreement-escalation')
            ->with($data);

        if (! $docLabel) {
            $mail->attachData(
                $this->renderSurat2Pdf($this->agreement, $this->surat2SentDate, $this->surat1SentDate),
                'Surat-2-'.$this->agreement->agreement_id.'.pdf',
                ['mime' => 'application/pdf']
            );

            return $mail;
        }

        // Lampiran of the letter: the letter itself, Surat 1 & 2 as one file, and the delivery receipt.
        $mail->attachData(
            $this->renderEscalationLetterPdf($this->agreement, now()),
            'Surat-Tindak-Lanjut-'.$this->agreement->agreement_id.'.pdf',
            ['mime' => 'application/pdf']
        );

        $mail->attachData(
            $this->renderSurat1And2Pdf($this->agreement, $this->surat1SentDate, $this->surat2SentDate),
            'Surat-1-dan-2-'.$this->agreement->agreement_id.'.pdf',
            ['mime' => 'application/pdf']
        );

        $tandaTerima = $this->findLatestAttachmentByLabel($this->agreement, 'Bukti Pengiriman');
        $file = $tandaTerima ? $this->downloadAgreementAttachmentFile($tandaTerima) : null;

        if ($file) {
            $mail->attachData(
                $file['content'],
                'Tanda-Terima-'.$this->agreement->agreement_id.'.'.$file['ext'],
                ['mime' => $file['mime']]
            );
        }

        return $mail;
    }

    protected function docUrl(): string
    {
        $eid = Hashids::encode($this->agreement->id);

        return url('/show-legal-agreement/'.$eid);
    }
}
