<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsLndPlaces extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'ms_lnd_places';

    protected $fillable = [
        'places_id',
        'places_name',
        'places_address',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
