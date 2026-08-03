<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "hr_trx_prf";
    // protected $primaryKey = 'id';
    protected $fillable = [
        'docid',
        'cpnyid',
        'group_cpny_id',
        'departementid',
        'division_id',
        'locationname',
        'area_id',
        'date',
        'user',
        'job_title',
        'subgrade_id',
        'job_level',
        'immediate_superior',
        'state_position',
        'immediate_replacement',
        'job_type',
        'name_job',
        'reason_vacancy',
        'other_reason',
        'required',
        'actual',
        'total_actual',
        'education',
        'education_jurusan',
        'experience_start',
        'experience_end',
        'experience_position',
        'expected_employment_date',
        'budget_cpnyid',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',
        'status',
        'site',
        'refid',
        'completed_user',
        'completed_at',
        'cpnyid_site'
    ];

    public function departement()
    {
        return $this->belongsTo(StoDepartement::class, 'job_title', 'departement_id');
    }

    public function divisionRef()
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }


}
