<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailboxEmail extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'mailbox_emails';

    protected $fillable = [
        'mailbox',
        'username',
        'folder',
        'uid',
        'message_id',
        'subject',
        'from_address',
        'from_name',
        'to_address',
        'email_date',
        'body_preview',
        'body_html',
        'body_text',
        'has_attachments',
        'is_read',
    ];

    protected $casts = [
        'email_date' => 'datetime',
        'has_attachments' => 'boolean',
        'is_read' => 'boolean',
    ];
}
