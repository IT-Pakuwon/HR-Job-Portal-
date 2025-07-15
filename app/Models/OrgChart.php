<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgChart extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'employee';     
    protected $fillable = [     
        'user_id',
        'first_name',
        'last_name',
        'organization_name',
        'job_position',
        'branch',
        'approval_line',
        'status_talenta'

    ];
   
}
