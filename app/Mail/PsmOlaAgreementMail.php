<?php

namespace App\Mail;

use App\Models\TrAgreement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Vinkla\Hashids\Facades\Hashids;

/**
 * A PSM/OLA or Addendum agreement was created or saved: tells the creator and
 * the PIC Legal(s).
 */
class PsmOlaAgreementMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public TrAgreement $agreement,
        public string $event,
        public string $actor,
        public string $picLegalNames,
    ) {
    }

    public function build()
    {
        $created = $this->event === 'created';
        $addendum = $this->agreement->agreement_type === 'ADDENDUM';
        $label = $addendum ? 'Addendum' : 'PSM / OLA';
        $eid = Hashids::encode($this->agreement->id);

        return $this
            ->subject('[LEGAL AGREEMENT]['.($created ? 'NEW' : 'UPDATED').'] '
                .$this->agreement->agreement_id.' - '.$label.' '.($created ? 'Created' : 'Updated'))
            ->view('emails.psm-ola-agreement')
            ->with([
                'created' => $created,
                'label' => $label,
                'docUrl' => url('/legal-new-agreement/'.($addendum ? 'addendum/' : '').$eid),
            ]);
    }
}
