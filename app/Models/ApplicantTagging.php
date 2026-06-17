<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantTagging extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_ms_applicant_tagging';

    protected $fillable = [
        'docid',
        'applicant_id',
        'departementid_tagging',
        'division_id_tagging',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
        'completed_at',
    ];
}
