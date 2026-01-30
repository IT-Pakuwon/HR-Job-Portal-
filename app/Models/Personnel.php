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
        'departementid',
        'division_id',
        'locationname',
        'date',
        'user',
        'job_title',
        'subgrade_id',
        'job_level',        
        'immediate_superior',
        'state_position',
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
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',
        'status',
        'site',
        'refid',
        'completed_user',
        'created_user',
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
