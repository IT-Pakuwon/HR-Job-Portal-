<?php

namespace App\Models\Tenancy;

use Illuminate\Database\Eloquent\Model;

class TsSite extends Model
{
    protected $connection = 'mysql5';
    protected $table = 'mssite';
    public $timestamps = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'siteid', 'sitename', 'sitetype', 'companyid', 'sitecompanyname',
        'siteaddress', 'sitephone', 'sitefax', 'sitefilename', 'status',
        'created_user', 'created_datetime', 'lastupdate_user', 'lastupdate_datetime',
    ];
}
