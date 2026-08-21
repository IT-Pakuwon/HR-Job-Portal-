<?php

namespace App\Mail;

use App\Mail\Concerns\ResolvesTicketSystemLabel;
use App\Models\TrTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCreatedMail extends Mailable
{
    use Queueable, SerializesModels, ResolvesTicketSystemLabel;

    public $ticket;

    public function __construct(
        TrTicket $ticket
    ) {

        $this->ticket = $ticket;
    }

    public function build()
    {
        $systemLabel = $this->systemLabelFor($this->ticket);

        return $this

            ->subject(
                '[TICKET][CREATED] '
                . $this->ticket->ticketid
            )

            ->view(
                'emails.ticket-created'
            )

            ->with('systemLabel', $systemLabel)

            ->with('docUrl', $this->docUrlFor($this->ticket));
    }
}
