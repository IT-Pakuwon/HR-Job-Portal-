<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $connection = 'mysql2';
    protected $table = "ms_site";

    protected $fillable = [
        'site',
        'cpnyid',
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',
    ];
}
