<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsMeetingRoom extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "ms_meeting_room";
    
    protected $fillable = [
        'room_id', 'room_name', 'event_color', 'user_approval', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
