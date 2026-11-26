<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAttachment extends Model
{
    // protected $connection = 'mysql2';
    protected $connection = 'pgsql2';
    protected $table = "tr_attachment";
    protected $primaryKey = 'id';
    protected $fillable = [
        'refnbr',
        'reference_id',
        'doctype',
        'attachment_date',
        'cpny_id',
        'department_id',
        'attachment_name',
        'folder',
        'filename',
        'filesize',
        'extention',
        'status',
        'created_by',
        'updated_by',
    ];

    // A Project module file may only be changed or deleted by someone who can
    // open its thread, whichever module's URL the request came through (several
    // "remove attachment" routes accept any id). Admins are exempt so that
    // Attachments Master keeps working. Query-builder updates skip this, so
    // never run one on an id taken from a request.
    protected static function booted(): void
    {
        $guard = function (self $row) {
            $user = auth()->user();

            if ($user && !$user->isPrimaryAdmin()) {
                TrProjectTask::abortUnlessAccessible((string) $row->getOriginal('doctype'), $row->getOriginal('refnbr'));
            }
        };

        static::updating($guard);
        static::deleting($guard);
    }
}
