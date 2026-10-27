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

    /**
     * The Mailbox menu entry only makes sense once the user has connected a mailbox
     * (JKT group + an active account). Shared by the sidebar and the dashboard Menu tiles.
     */
    public static function isConnectedFor($user): bool
    {
        return $user
            && strtoupper(trim((string) $user->group_cpny_id)) === 'JKT'
            && static::where('username', $user->username)->where('status', true)->exists();
    }

    protected $casts = [
        'imap_password' => 'encrypted',
        'imap_validate_cert' => 'boolean',
        'status' => 'boolean',
    ];

    protected $hidden = [
        'imap_password',
    ];
}
