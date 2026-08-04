<?php

namespace App\Mail;

use App\Mail\Concerns\ResolvesTicketSystemLabel;
use App\Models\TrTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCancelledMail extends Mailable
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
                '[TICKET][CANCELLED] '
                . $this->ticket->ticketid
            )

            ->view(
                'emails.ticket-cancelled'
            )

            ->with('systemLabel', $this->systemLabelFor($this->ticket));
    }
}
