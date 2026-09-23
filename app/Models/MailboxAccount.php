<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailboxAccount extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'mailbox_accounts';

    protected $fillable = [
        'username',
        'email',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_validate_cert',
        'imap_username',
        'imap_password',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'status',
    ];

    protected $casts = [
        'imap_password' => 'encrypted',
        'imap_validate_cert' => 'boolean',
        'status' => 'boolean',
    ];

    protected $hidden = [
        'imap_password',
    ];
}
