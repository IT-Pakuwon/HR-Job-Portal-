<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'mysql2';
    protected $table = "company";

    protected $fillable = [
        'cpnyid',
        'cpnyname',
        'parent',
        'project',
        'created_user',
        'status'
    ];
}
