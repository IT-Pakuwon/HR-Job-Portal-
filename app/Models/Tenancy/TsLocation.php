<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsLocation extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'mslocation';
    public $timestamps = false;

    protected $fillable = [
        'locationname', 'siteid', 'status',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];

    public function site()
    {
        return $this->belongsTo(TsSite::class, 'siteid', 'siteid');
    }
}
