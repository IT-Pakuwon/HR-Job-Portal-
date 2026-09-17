<?php

namespace App\Mail;

use App\Models\TrAgreement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Vinkla\Hashids\Facades\Hashids;

class AgreementCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $agreement;

    public function __construct(
        TrAgreement $agreement
    ) {
        $this->agreement = $agreement;
    }

    public function build()
    {
        return $this

            ->subject(
                '[LEGAL AGREEMENT][COMPLETED] '
                . $this->agreement->agreement_id
            )

            ->view(
                'emails.agreement-completed'
            )

            ->with('docUrl', $this->docUrl());
    }

    protected function docUrl(): string
    {
        $eid = Hashids::encode($this->agreement->id);

        return url('/show-legal-agreement/'.$eid);
    }
}
