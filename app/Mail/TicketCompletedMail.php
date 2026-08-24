<?php

namespace App\Mail;

use App\Mail\Concerns\ResolvesTicketSystemLabel;
use App\Models\TrTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCompletedMail extends Mailable
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
        return $this

            ->subject(
                '[TICKET][COMPLETED] '
                . $this->ticket->ticketid
            )

            ->view(
                'emails.ticket-completed'
            )

            ->with('systemLabel', $this->systemLabelFor($this->ticket))

            ->with('docUrl', $this->docUrlFor($this->ticket));
    }
}
