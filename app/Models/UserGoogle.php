<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGoogle extends Model
{


    protected $connection = 'pgsql2'; 
    protected $table = 'ms_user_google';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'google_account_email',
        'access_token',
        'refresh_token',
        'token_expiry',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
