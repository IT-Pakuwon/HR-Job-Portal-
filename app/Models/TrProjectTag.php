<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProjectTag extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project_tag';
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'tag_id',
        'status',
    ];
}
