<?php

namespace App\Mail;

use App\Models\MsEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventH7ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $locationName;
    public $companyName;

    public function __construct(MsEvent $event, ?string $locationName, ?string $companyName)
    {
        $this->event = $event;
        $this->locationName = $locationName;
        $this->companyName = $companyName;
    }

    public function build()
    {
        return $this
            ->subject('[EVENT][REMINDER H-7] '.$this->event->event_id.' - '.$this->event->event_name)
            ->view('emails.event-h7-reminder')
            ->with('systemLabel', 'Event Calendar');
    }
}
