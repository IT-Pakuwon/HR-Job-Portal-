<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roommeet extends Model
{
    protected $connection = 'mysql2';
    protected $table = "ms_room";

    protected $fillable = [
        'room_id',
        'name',
        'eventcolor',        
        'status',
        'approval',
        'created_user',
        'updated_user',      
    ];
}
