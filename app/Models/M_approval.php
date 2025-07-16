<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_approval extends Model
{
    protected $connection = 'mysql2';
    protected $table = "ms_approval";
    // protected $primaryKey = 'id';
    protected $fillable = [
        'aprvid',
        'aprvdoctype',
        'aprvcpnyid',
        'aprvdeptid',
        'aprvusername',
        'name',
        'status',
        'created_user'
    ];
}
