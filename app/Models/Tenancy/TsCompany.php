<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsCompany extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'mscompany';
    public $timestamps = false;

    protected $fillable = [
        'companyname', 'companycity', 'status',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];
}
