<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsDepartment extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'msdepartment';
    public $timestamps = false;

    protected $fillable = [
        'doctype', 'siteid', 'departmentid', 'departmentname', 'template', 'urlaccess', 'status',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];

    public function site()
    {
        return $this->belongsTo(TsSite::class, 'siteid', 'siteid');
    }
}
