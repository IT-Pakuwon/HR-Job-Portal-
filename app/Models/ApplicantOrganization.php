<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantOrganization extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_ms_applicant_organization';

    protected $fillable = [
        'applicant_id',
        'group_cpny_id',
        'organization_name',
        'organization_type',
        'organization_year',
        'organization_position',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
    ];
}
