<?php

namespace App\Mail\Concerns;

use App\Models\TrTicket;

trait ResolvesTicketSystemLabel
{
    protected function systemLabelFor(TrTicket $ticket): string
    {
        return match ($ticket->ticket_type) {
            'ENGSUPPORTTICKET', 'BA_ENG' => 'Engineering Ticketing System',
            'BSSUPPORTTICKET', 'BA_BS' => 'Building Service Ticketing System',
            'FOSUPPORTTICKET', 'BA_FO' => 'Fit Out Ticketing System',
            'ITSUPPORTTICKET' => 'IT Support Ticketing System',
            default => 'Ticketing System',
        };
    }
}
