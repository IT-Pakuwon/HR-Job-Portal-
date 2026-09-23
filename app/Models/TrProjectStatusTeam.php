<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProjectStatusTeam extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_status_team';
    public $timestamps = false;

    protected $fillable = [
        'status_id',
        'team_id',
        'status',
        'created_by',
        'created_at',
    ];
}
