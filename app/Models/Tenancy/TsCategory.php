<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsCategory extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'mscategory';
    public $timestamps = false;

    protected $fillable = [
        'name', 'type', 'status',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];
}
