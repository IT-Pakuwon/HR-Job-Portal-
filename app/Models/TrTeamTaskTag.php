<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrTeamTaskTag extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_team_task_tag';
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'tag_id',
        'status',
    ];
}
