<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable;

  
   
    // protected $connection = 'mysql2';
    // protected $table = "users";   
    protected $connection = 'pgsql2';
    protected $table = "ms_user_test";   
    public $incrementing = true;
  
    protected $keyType = 'int';    
    protected $fillable = [
        'name',
        'username',
        'email',    
        'phonenumber',
        'password',
        'cpny_id',
        'department_id',
        'user_role',
        'notification_email',
        'npk',
        'approval_line',
        'status',
        'created_by',
        'updated_by',

    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];
  
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

  

}
