<?php

namespace App\Services;

use App\Models\User;
use App\Models\TrTicket;
use App\Models\SysUserRole;

use App\Mail\TicketCreatedMail;
use App\Mail\TicketAssignedMail;
use App\Mail\TicketCompletedMail;
use App\Mail\TicketReopenMail;
use App\Mail\TicketTransferMail;
use App\Mail\TicketCancelledMail;

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
                        'ticketid' =>
                            $ticket->ticketid,

                        'email' =>
                            $email,

                        'error' =>
                            $e->getMessage(),
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
                    'ticketid' =>
                        $ticket->ticketid,

                    'email' =>
                        $email,

                    'error' =>
                        $e->getMessage(),
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
                    'ticketid' =>
                        $ticket->ticketid,

                    'email' =>
                        $email,

                    'error' =>
                        $e->getMessage(),
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
                        'ticketid' =>
                            $ticket->ticketid,

                        'email' =>
                            $email,

                        'error' =>
                            $e->getMessage(),
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

                new TicketTransferMail(
                    $ticket
                )

            );

        } catch (\Throwable $e) {

            Log::error(
                'Ticket Transfer Mail Failed',
                [
                    'ticketid' =>
                        $ticket->ticketid,

                    'email' =>
                        $email,

                    'error' =>
                        $e->getMessage(),
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
                    'ticketid' =>
                        $ticket->ticketid,

                    'email' =>
                        $email,

                    'error' =>
                        $e->getMessage(),
                ]
            );

        }

    }
}
