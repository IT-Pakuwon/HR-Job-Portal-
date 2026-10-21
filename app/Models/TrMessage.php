<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrMessage extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'tr_message';
    protected $primaryKey = 'id';

    protected $fillable = [
        'refnbr',
        'doctype',
        'message_date',
        'message_type',
        'cpny_id',
        'department_id',
        'username',
        'name',
        'message',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // protected $casts = [
    //     'message_date' => 'datetime',
    // ];

    // A file shared in a chat message is stored inline as [[file:<tr_attachment.id>|<name>]].
    // The name is only a fallback label — the chat UI shows the attachment's current
    // (possibly renamed) name, and "file removed" once it's deleted.
    public const FILE_MARKER_PATTERN = '/\[\[file:(\d+)\|([^\]]*)\]\]/';

    public static function fileMarker(TrAttachment $att): string
    {
        $name = $att->attachment_name . ($att->extention ? '.' . $att->extention : '');
        $name = \Illuminate\Support\Str::limit(str_replace(['[', ']', '|'], '', $name), 80, '…');

        return "[[file:{$att->id}|{$name}]]";
    }

    // Message text with file markers turned into "📎 name" — for notifications/previews.
    public static function plainText(?string $message): string
    {
        return trim(preg_replace(self::FILE_MARKER_PATTERN, '📎 $2', (string) $message));
    }
}
