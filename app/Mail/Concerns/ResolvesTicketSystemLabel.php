<?php

namespace App\Mail\Concerns;

use App\Models\TrTicket;

trait ResolvesTicketSystemLabel
{
    protected function systemLabelFor(TrTicket $ticket): string
    {
        return match ($ticket->ticket_type) {
            'ENGSUPPORTTICKET', 'BA_ENG' => 'Engineering Ticketing System',
            'BSFOSUPPORTTICKET', 'BA_BS' => 'BS-FO Ticketing System',
            default => 'Engineering & BS Ticketing System',
        };
    }
}
