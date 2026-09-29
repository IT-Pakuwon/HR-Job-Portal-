<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrFavorite extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_favorite';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'fav_type',
        'ref_id',
        'created_at',
    ];
}
