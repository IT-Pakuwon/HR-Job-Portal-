<?php

namespace App\Mail;

use App\Mail\Concerns\RendersAgreementLetters;
use App\Models\TrAgreement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgreementSurat1Mail extends Mailable
{
    use Queueable, SerializesModels, RendersAgreementLetters;

    public $agreement;

    public function __construct(
        TrAgreement $agreement
    ) {
        $this->agreement = $agreement;
    }

    public function build()
    {
        $mail = $this
            ->subject(
                '[LEGAL AGREEMENT][SURAT 1] '
                . $this->agreement->agreement_id
                . ' - Pengingat Pengembalian Dokumen PSM/Addendum'
            )
            ->view('emails.agreement-surat1');

        $mail->attachData(
            $this->renderSurat1Pdf($this->agreement, now()),
            'Surat-1-'.$this->agreement->agreement_id.'.pdf',
            ['mime' => 'application/pdf']
        );

        $tandaTerima = $this->findLatestAttachmentByLabel($this->agreement, 'Bukti Pengiriman');

        if ($tandaTerima) {
            $file = $this->downloadAgreementAttachmentFile($tandaTerima);

            if ($file) {
                $mail->attachData(
                    $file['content'],
                    'Tanda-Terima-'.$this->agreement->agreement_id.'.'.$file['ext'],
                    ['mime' => $file['mime']]
                );
            }
        }

        return $mail;
    }
}
