<?php

namespace App\Mail;

use App\Models\TrTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketReopenMail extends Mailable
{
    use Queueable, SerializesModels;

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
                '[TICKET][REOPEN] '
                . $this->ticket->ticketid
            )

            ->view(
                'emails.ticket-reopen'
            );
    }
}
