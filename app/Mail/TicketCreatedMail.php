<?php

namespace App\Mail;

use App\Models\TrTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketCreatedMail extends Mailable
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
        $systemLabel = match ($this->ticket->ticket_type) {
            'ENGSUPPORTTICKET' => 'Engineering Ticketing System',
            'BSFOSUPPORTTICKET' => 'BS-FO Ticketing System',
            default => 'IT Ticketing System',
        };

        return $this

            ->subject(
                '[TICKET][CREATED] '
                . $this->ticket->ticketid
            )

            ->view(
                'emails.ticket-created'
            )

            ->with('systemLabel', $systemLabel);
    }
}
