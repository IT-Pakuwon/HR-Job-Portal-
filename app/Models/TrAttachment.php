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
        'doctype',
        'attachment_date',
        'cpnyid',
        'departementid',
        'attachment_name',
        'folder',
        'filename',
        'filesize',
        'extention',
        'status',
        'created_by',
        'updated_by',
    ];
}
