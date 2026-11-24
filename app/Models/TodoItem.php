<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TodoItem extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_todo';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'is_done' => 'boolean',
    ];
}
