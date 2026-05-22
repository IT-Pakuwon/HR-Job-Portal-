<?php

namespace App\Services;

use App\Mail\TicketAssignedMail;
use App\Mail\TicketCancelledMail;
use App\Mail\TicketCompletedMail;
use App\Mail\TicketCreatedMail;
use App\Mail\TicketReopenMail;
use App\Mail\TicketTransferMail;
use App\Models\SysUserRole;
use App\Models\TrTicket;
use App\Models\TrTicketActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketNotificationService
{
    protected function getUserEmail(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return $user->notification_email
            ?: $user->email;
    }

    protected function getITUsers()
    {
        $usernames = SysUserRole::query()

            ->whereIn('role_id', [
                'ITHARDWARE',
                'ITSOFTWARE',
            ])

            ->pluck('username')

            ->unique()

            ->values();

        return User::query()

            ->whereIn(
                'username',
                $usernames
            )

            ->where('status', 'A')

            ->get();
    }

    public function ticketCreated(
        TrTicket $ticket
    ) {
        $emails = collect();

        $itUsers =
            $this->getITUsers();

        foreach ($itUsers as $user) {
            $email =
                $this->getUserEmail($user);

            if ($email) {
                $emails->push($email);
            }
        }

        $requester = User::query()

            ->where(
                'username',
                $ticket->user_peminta
            )

            ->first();

        $requesterEmail =
            $this->getUserEmail(
                $requester
            );

        if ($requesterEmail) {
            $emails->push(
                $requesterEmail
            );
        }

        $emails = $emails

            ->filter()

            ->unique()

            ->values();

        foreach ($emails as $email) {
            try {
                Mail::to($email)->send(
                    new TicketCreatedMail(
                        $ticket
                    )
                );
            } catch (\Throwable $e) {
                Log::error(
                    'Ticket Created Mail Failed',
                    [
                        'ticketid' => $ticket->ticketid,

                        'email' => $email,

                        'error' => $e->getMessage(),
                    ]
                );
            }
        }
    }

    public function ticketAssigned(
        TrTicket $ticket
    ) {
        if (!$ticket->pic_ticket) {
            return;
        }

        $pic = User::query()

            ->where(
                'username',
                $ticket->pic_ticket
            )

            ->first();

        $email =
            $this->getUserEmail(
                $pic
            );

        if (!$email) {
            return;
        }

        try {
            Mail::to($email)->send(
                new TicketAssignedMail(
                    $ticket
                )
            );
        } catch (\Throwable $e) {
            Log::error(
                'Ticket Assigned Mail Failed',
                [
                    'ticketid' => $ticket->ticketid,

                    'email' => $email,

                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function ticketCompleted(
        TrTicket $ticket
    ) {
        $requester = User::query()

            ->where(
                'username',
                $ticket->user_peminta
            )

            ->first();

        $email =
            $this->getUserEmail(
                $requester
            );

        if (!$email) {
            return;
        }

        try {
            Mail::to($email)->send(
                new TicketCompletedMail(
                    $ticket
                )
            );
        } catch (\Throwable $e) {
            Log::error(
                'Ticket Completed Mail Failed',
                [
                    'ticketid' => $ticket->ticketid,

                    'email' => $email,

                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function ticketReopened(
        TrTicket $ticket
    ) {
        $emails = collect();

        $requester = User::query()

            ->where(
                'username',
                $ticket->user_peminta
            )

            ->first();

        $requesterEmail =
            $this->getUserEmail(
                $requester
            );

        if ($requesterEmail) {
            $emails->push(
                $requesterEmail
            );
        }

        if ($ticket->pic_ticket) {
            $pic = User::query()

                ->where(
                    'username',
                    $ticket->pic_ticket
                )

                ->first();

            $picEmail =
                $this->getUserEmail(
                    $pic
                );

            if ($picEmail) {
                $emails->push(
                    $picEmail
                );
            }
        }

        $emails = $emails

            ->filter()

            ->unique()

            ->values();

        foreach ($emails as $email) {
            try {
                Mail::to($email)->send(
                    new TicketReopenMail(
                        $ticket
                    )
                );
            } catch (\Throwable $e) {
                Log::error(
                    'Ticket Reopen Mail Failed',
                    [
                        'ticketid' => $ticket->ticketid,

                        'email' => $email,

                        'error' => $e->getMessage(),
                    ]
                );
            }
        }
    }

    public function ticketTransferred(
        TrTicket $ticket
    ) {
        if (!$ticket->pic_ticket) {
            return;
        }

        $pic = User::query()

            ->where(
                'username',
                $ticket->pic_ticket
            )

            ->first();

        $email =
            $this->getUserEmail(
                $pic
            );

        if (!$email) {
            return;
        }

        try {
            Mail::to($email)->send(
                new TicketTransferMail($ticket)
            );
        } catch (\Throwable $e) {
            Log::error(
                'Ticket Transfer Mail Failed',
                [
                    'ticketid' => $ticket->ticketid,

                    'email' => $email,

                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function ticketCancelled(
        TrTicket $ticket
    ) {
        $requester = User::query()

            ->where(
                'username',
                $ticket->user_peminta
            )

            ->first();

        $email =
            $this->getUserEmail(
                $requester
            );

        if (!$email) {
            return;
        }

        try {
            Mail::to($email)->send(
                new TicketCancelledMail(
                    $ticket
                )
            );
        } catch (\Throwable $e) {
            Log::error(
                'Ticket Cancelled Mail Failed',
                [
                    'ticketid' => $ticket->ticketid,

                    'email' => $email,

                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    protected WhatsappService $whatsapp;

    public function __construct(
        WhatsappService $whatsapp
    ) {
        $this->whatsapp = $whatsapp;
    }
public function ticketEnvision(
    TrTicket $ticket,
    string $responseDescr
): void {

    $ticket->load([
        'location',
        'subLocation',
    ]);

    $activity = TrTicketActivity::query()
        ->where('ticketid', $ticket->ticketid)
        ->where('status_pekerjaan', 'ENVISION')
        ->latest('id')
        ->first();

    Log::info('WA ENVISION ACTIVITY', [
        'ticketid' => $ticket->ticketid,
        'activity_id' => $activity?->id,
        'working_start_date' => $activity?->working_start_date,
        'working_end_date' => $activity?->working_end_date,
    ]);

    $requestDate = $ticket->ticketdate
        ? Carbon::parse($ticket->ticketdate)
            ->format('d-m-Y')
        : '-';

    $actionDate = $activity?->working_start_date
        ? Carbon::parse(
            $activity->working_start_date
        )->format('d-m-Y')
        : '-';

    $actionTime = $activity?->working_start_date
        ? Carbon::parse(
            $activity->working_start_date
        )->format('H:i')
        : '-';

   $message = "
PAKUWON SYSTEM
TICKET ORDER
=================
PROJECT : {$ticket->department_id}
LOCATION : {$ticket->location?->location_name}
SUB LOCATION : {$ticket->subLocation?->sub_location_name}

REQUEST DATE : {$requestDate}
ACTION DATE : {$actionDate}
ACTION TIME : {$actionTime}

PIC REQUEST : {$ticket->pic_ticket}

----------------------------------
NO TICKET - PKW : #{$ticket->ticketid}
USER COMPLAINT : {$ticket->created_by}
NO-HP USER : -
DEPARTMENT : {$ticket->department_id}

----------------------------------
SUBJECT : {$ticket->issue_summary}

Dear Team,
{$responseDescr}
----------------------------------
ORDER/MONTHLY : Monthly
";

    try {

        $result = $this->whatsapp->sendText(
            '120363428152916612@g.us',
            $message
        );

        Log::info(
            'Ticket Envision WhatsApp Success',
            [
                'ticketid' => $ticket->ticketid,
                'response' => $result,
            ]
        );

    } catch (\Throwable $e) {

        Log::error(
            'Ticket Envision WhatsApp Failed',
            [
                'ticketid' => $ticket->ticketid,
                'error' => $e->getMessage(),
            ]
        );
    }
}
}
