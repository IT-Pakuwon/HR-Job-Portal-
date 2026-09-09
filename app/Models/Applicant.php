<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $connection = 'mysql3';
    protected $table = 'hr_ms_applicant';

    protected $fillable = [
        'applicant_id',
        'group_cpny_id',
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
        // applicant_id collides across group_cpny_id (SBY/JKT sequences overlap), so this
        // relation pins group_cpny_id from $this. That only works when called on an already
        // resolved instance (lazy-load) — eager-loading (Applicant::with('driverLicenses'))
        // calls this method on an empty model first, so $this->group_cpny_id would be null
        // and silently return zero rows for every applicant. Fail loudly instead.
        if (!$this->exists || $this->group_cpny_id === null) {
            throw new \LogicException('Applicant::driverLicenses() requires a resolved Applicant instance with group_cpny_id set; do not eager-load this relation — query ApplicantDriverLicense directly with both applicant_id and group_cpny_id instead.');
        }

        return $this->hasMany(ApplicantDriverLicense::class, 'applicant_id', 'applicant_id')
            ->where('group_cpny_id', $this->group_cpny_id);
    }
}
