<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsMeetingAccessories extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "ms_meeting_accessories";
    
    protected $fillable = [
        'acc_id', 'room_id', 'acc_name', 'acc_qty', 'userid_zoom', 'userid_msteams', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
