<?php

namespace App\Console\Commands;

use App\Models\TrServiceorderEnvision;
use App\Models\TrTicket;
use App\Models\TrTicketActivity;
use Illuminate\Console\Command;

class SyncEnvisionSolvedTickets extends Command
{
    protected $signature = 'ticket:sync-envision-solved';

    protected $description = 'Promote ENVISION tickets to ENVISION CHECKED/SOLVED when a service order exists';

    public function handle(): int
    {
        $tickets = TrTicket::query()
            ->where('status_pekerjaan', 'ENVISION')
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('No ENVISION tickets to process.');
            return self::SUCCESS;
        }

        // Single batch query to pgsql5 instead of one EXISTS per ticket
        $ticketIds = $tickets->pluck('ticketid')->all();

        $serviceOrders = TrServiceorderEnvision::query()
            ->whereIn('ticketid', $ticketIds)
            ->orderByDesc('serviceorderdate')
            ->get()
            ->unique('ticketid')
            ->keyBy('ticketid');

        $updated = 0;

        foreach ($tickets as $ticket) {
            $so = $serviceOrders->get($ticket->ticketid);

            if (!$so) {
                continue;
            }

            $ticket->update([
                'status_pekerjaan' => 'ENVISION CHECKED / SOLVED',
                'updated_by'       => 'SYSTEM',
            ]);

            $alreadyLogged = TrTicketActivity::query()
                ->where('ticketid', $ticket->ticketid)
                ->where('status_pekerjaan', 'ENVISION CHECKED / SOLVED')
                ->exists();

            if (!$alreadyLogged) {
                TrTicketActivity::create([
                    'ticketid'          => $ticket->ticketid,
                    'cpny_id'           => $ticket->cpny_id,
                    'department_id'     => $ticket->department_id,
                    'pic_ticket'        => $ticket->pic_ticket,
                    'response_date'     => now(),
                    'response_summary'  => 'Envision Solved',
                    'response_descr'    => 'Note: ' . ($so->serviceorder_action ?? '-'),
                    'status_pekerjaan'  => 'ENVISION CHECKED / SOLVED',
                    'status'            => 'A',
                    'created_by'        => 'SYSTEM',
                ]);
            }

            $updated++;
            $this->line("  → {$ticket->ticketid} updated to ENVISION CHECKED / SOLVED");
        }

        $this->info("Done. {$updated} ticket(s) updated.");

        return self::SUCCESS;
    }
}
