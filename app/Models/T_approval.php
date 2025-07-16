<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class T_approval extends Model
{
    protected $connection = 'mysql2';
    protected $table = "trx_approval";
    protected $primaryKey = 'approvalid';
    protected $fillable = [
        'docid',
        'aprvid',
        'aprvdoctype',
        'aprvcpnyid',
        'aprvdeptid',
        'aprvusername',
        'name',
        'aprvdatebefore',
        'aprvdateafter',
        'aprvtotalday',
        'status',
        'created_user'
    ];
}
