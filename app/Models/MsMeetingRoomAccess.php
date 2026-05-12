<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsMeetingRoomAccess extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_meeting_room_access';

    protected $fillable = [
        'room_id',
        'username',
        'created_by',
        'updated_by',
    ];
}
