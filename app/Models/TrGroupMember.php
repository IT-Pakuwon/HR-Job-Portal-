<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrGroupMember extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_group_member';
    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'username',
        'added_by',
        'added_at',
        'status',
    ];

    public function group()
    {
        return $this->belongsTo(MsGroup::class, 'group_id', 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}
