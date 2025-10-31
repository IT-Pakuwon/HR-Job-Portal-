<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrMessage extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "tr_message";
    protected $primaryKey = 'id';    

    protected $fillable = [
        'refnbr',
        'doctype',
        'message_date',
        'cpnyid',
        'departementid',
        'username',
        'name',
        'message',
        'status',
        'created_by',
        'updated_by',
    ];

    // protected $casts = [
    //     'message_date' => 'datetime',
    // ];
}
