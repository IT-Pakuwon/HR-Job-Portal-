<?php

namespace App\Mail;

use App\Models\TrAgreement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Vinkla\Hashids\Facades\Hashids;

/**
 * A PSM/OLA or Addendum agreement was created, saved, completed, cancelled or
 * reopened: tells the creator and the PIC Legal(s).
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
        public ?string $note = null,
    ) {
    }

    public function build()
    {
        $addendum = $this->agreement->agreement_type === 'ADDENDUM';
        $label = $addendum ? 'Addendum' : 'PSM / OLA';
        $eid = Hashids::encode($this->agreement->id);

        // event => [subject tag, past-tense verb]
        [$tag, $verb] = [
            'created' => ['NEW', 'created'],
            'updated' => ['UPDATED', 'saved changes to'],
            'completed' => ['COMPLETED', 'completed'],
            'cancelled' => ['CANCELLED', 'cancelled'],
            'reopened' => ['REOPENED', 'reopened'],
        ][$this->event] ?? ['UPDATED', 'updated'];

        // A cancelled agreement stays viewable (Cancelled tab), so it gets its link too.
        $docUrl = url('/legal-new-agreement/'.($addendum ? 'addendum/' : '').$eid);

        return $this
            ->subject('[LEGAL AGREEMENT]['.$tag.'] '.$this->agreement->agreement_id.' - '.$label.' '.ucfirst($this->event))
            ->view('emails.psm-ola-agreement')
            ->with([
                'event' => $this->event,
                'tag' => $tag,
                'verb' => $verb,
                'label' => $label,
                'note' => $this->note,
                'docUrl' => $docUrl,
            ]);
    }
}
