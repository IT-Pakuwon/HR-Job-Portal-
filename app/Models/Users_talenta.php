<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users_talenta extends Model
{
    protected $connection = 'mysql2';
    protected $table = "users_talenta"; 
    protected $fillable = [       
    'user_id',
    'created_talenta',
    'updated_talenta',
    'first_name',
    'last_name',
    'email',
    'identity_type',
    'identity_number',
    'expired_date_identity_id',
    'postal_code',
    'address',
    'current_address',
    'birth_place',
    'birth_date',
    'phone',
    'mobile_phone',
    'gender',
    'marital_status',
    'religion',
    'avatar',
    'employee_id',
    'company_id',
    'organization_id',
    'organization_name',
    'job_position_id',
    'job_position',
    'job_level',
    'employment_status',
    'end_date',
    'branch_id',
    'branch',
    'join_date',
    'length_of_service',
    'grade',
    'class',
    'approval_line',
    'status_talenta',
    'resign_date',
    'avatar_local',
    'status',
    'created_user',    
    'updated_user'
                   
    ];
}
