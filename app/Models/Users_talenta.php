<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users_talenta extends Model
{
    // Points at the `das` database via the `mysql2` connection — NOT the generic
    // `mysql` (default) connection, which means different things per environment
    // (locally it can be pointed at `das` for convenience, but on production it's
    // `iamsys`, an unrelated database). `mysql2` is the connection name that
    // consistently resolves to `das` in both places — same one UserDas/hr_ms_approval
    // use, since on production they're all one database.
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
    'old_organization_id',
    'old_organization_name',
    'job_position_id',
    'job_position',
    'old_job_position_id',
    'old_job_position',
    'job_level',
    'old_job_level',
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
