<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_ms_applicant';

    protected $fillable = [
        'applicant_id',
        'full_name',
        'nick_name',
        'birth_place',
        'date_of_birth',
        'age',
        'religion',
        'gender',
        'blood_type',
        'martial_status',
        'ktp_id',
        'citizenship',
        'id_address',
        'idem_address',
        'domicile_address',
        'domicile_city',
        'domicile_postal_code',
        'phone_number',
        'mobile_phone',
        'email_address',
        'height',
        'weight',
        'sosmed_facebook_account',
        'sosmed_instagram_account',
        'sosmed_x_account',
        'sosmed_linkedin_account',
        'source_information',
        'urgent_contact_name',
        'urgent_phone',
        'urgent_contact_relation',
        'existing_last_thp',
        'expected_thp',
        'expectations',
        'relative_work_status',
        'relative_work_name',
        'relative_work_division',
        'career_achievement',
        'reference_name',
        'reference_division',
        'reference_contact_number',
        'apply_other_on_progress',
        'apply_other_on_progress_descr',
        'apply_status',
        'upload_cv',
        'upload_coverletter',
        'upload_photo',
        'upload_transkip_nilai',
        'upload_ijazah',
        'process_step',
        'status',
        'created_user',
        'updated_user',
        'completed_user',
    ];

    public function driverLicenses()
    {
        return $this->hasMany(ApplicantDriverLicense::class, 'applicant_id', 'applicant_id');
    }
}
