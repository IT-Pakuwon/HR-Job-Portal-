<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProjectPic extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_pic';
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'pic_type',
        'ref_id',
        'added_by',
        'added_at',
        'status',
    ];

    public function isTeam(): bool
    {
        return $this->pic_type === 'TEAM';
    }

    public function isUser(): bool
    {
        return $this->pic_type === 'USER';
    }
}
