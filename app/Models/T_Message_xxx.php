<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class T_Message extends Model
{
    protected $connection = 'mysql2';
    protected $table = "trx_message";
    protected $primaryKey = 'id';
    protected $fillable = [
        'docid',
        'doctype',
        'username',
        'name',
        'message',
        'status',
        'created_user'
    ];
}
