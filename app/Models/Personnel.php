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
        'budget_entity_id',
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',
        'completed_user',
        'completed_at',
    ];

    public function departement()
    {
        return $this->belongsTo(StoDepartement::class, 'job_title', 'departement_id');
    }

    public function divisionRef()
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    /**
     * Jurusan chosen on the PRF as a clean array (stored comma-separated).
     */
    public function educationJurusanList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->education_jurusan)), 'strlen'));
    }

    /**
     * Wording for the jurusan part of the education requirement.
     * Only SBY picks specific jurusan: "A", "A atau B", "A, B atau C".
     * Everyone else (and "Semua Jurusan" / nothing picked) returns null.
     */
    public function specificJurusanText(string $or = 'atau'): ?string
    {
        if ($this->group_cpny_id !== 'SBY') {
            return null;
        }

        $list = $this->educationJurusanList();

        if (!$list || in_array('Semua Jurusan', $list, true)) {
            return null;
        }

        if (count($list) === 1) {
            return $list[0];
        }

        $last = array_pop($list);

        return implode(', ', $list).' '.$or.' '.$last;
    }


}
