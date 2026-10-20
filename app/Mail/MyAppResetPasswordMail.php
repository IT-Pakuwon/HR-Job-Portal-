<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MyAppResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $resetUrl)
    {
    }

    public function build()
    {
        return $this
            ->subject('[MY APP] Reset your password')
            ->view('emails.myapp-reset-password')
            ->with(['systemLabel' => 'My APP']);
    }
}
