<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantRating extends Model
{
    protected $connection = 'mysql3';
    protected $table = "hr_ms_applicant_ratings";

    protected $fillable = [
        'idx', 'applicant_id', 'group_cpny_id', 'rating', 'feedback', 'ip_address', 'source'
    ];
}
