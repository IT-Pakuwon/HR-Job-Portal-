<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class UserEng extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable;

    protected $connection = 'mysql4';
    
    protected $table = "users";   

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'NPK',
        'phone_number',
        'office_number',
        'position_id',
        'Attendance_Type',
        'warranty_alarm',
        'user_img',
        'device_token',
        'active_status',
        'Last_update_By',
        'remember_token'

    ];

     public function departments()
    {
        return $this->belongsToMany(DepartmentEng::class, 'department_user', 'user_id', 'department_id')
                    ->withPivot('company_id', 'Last_update_By');
    }

    public function position()
    {
        return $this->belongsTo(PositionEng::class, 'position_id');
    }

    public function companyroles()
    {
        return $this->hasMany(\App\Models\CompanyEngRole::class, 'user_id');
    }



   

}
