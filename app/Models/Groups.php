<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groups extends Model
{
    protected $connection = 'mysql2';
    protected $table = "ms_groups";
    protected $fillable = [
        'groupsname',
    ];
}
