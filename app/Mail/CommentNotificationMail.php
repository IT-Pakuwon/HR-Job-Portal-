<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $doctype;
    public string $docNo;
    public string $commenterName;
    public string $commentMessage;
    public string $moduleLabel;

    public function __construct(
        string $doctype,
        string $docNo,
        string $commenterName,
        string $commentMessage,
        string $moduleLabel
    ) {
        $this->doctype       = $doctype;
        $this->docNo         = $docNo;
        $this->commenterName = $commenterName;
        $this->commentMessage = $commentMessage;
        $this->moduleLabel   = $moduleLabel;
    }

    public function build()
    {
        return $this
            ->subject("[{$this->moduleLabel}][COMMENT] {$this->docNo}")
            ->view('emails.discussion-comment');
    }
}
