<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsApproval extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'msapproval';
    public $timestamps = false;

    protected $fillable = [
        'doctype', 'siteid', 'departmentid', 'urutan', 'username', 'accesstype', 'conditiontype', 'status',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];

    public function site()
    {
        return $this->belongsTo(TsSite::class, 'siteid', 'siteid');
    }
}
