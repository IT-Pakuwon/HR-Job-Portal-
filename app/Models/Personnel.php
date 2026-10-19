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
     * Experience requirement sentence.
     * End = 0 means "no upper limit", Start = 0 means "no minimum".
     *   0 / 0   -> no experience required
     *   3 / 0   -> minimum 3 years
     *   0 / 2   -> maximum 2 years
     *   3 / 5   -> 3 - 5 years (Indonesian keeps the old "Memiliki Pengalaman 3 tahun")
     *
     * @param string $lang 'id' (Job Posting) or 'en' (PRF detail page)
     */
    public static function experienceWording($start, $end, ?string $role = null, string $lang = 'id'): ?string
    {
        $startFilled = $start !== null && trim((string) $start) !== '';
        $endFilled = $end !== null && trim((string) $end) !== '';
        $role = filled($role) ? trim($role) : null;

        if (!$startFilled && !$endFilled) {
            return ($lang === 'id' && $role) ? "Memiliki Pengalaman sebagai {$role}" : null;
        }

        $s = (int) $start;
        $e = (int) $end;

        if ($lang === 'id') {
            $as = $role ? " sebagai {$role}" : '';

            if ($s === 0 && $e === 0) {
                return 'Tidak memerlukan pengalaman';
            }
            if ($e === 0) {
                return "Minimal {$s} tahun pengalaman{$as}";
            }
            if ($s === 0) {
                return "Maksimal {$e} tahun pengalaman{$as}";
            }

            return "Memiliki Pengalaman {$s} tahun{$as}";
        }

        $as = $role ? " as {$role}" : '';
        $years = fn (int $n) => $n === 1 ? 'year' : 'years';

        if ($s === 0 && $e === 0) {
            return 'No prior experience required';
        }
        if ($e === 0) {
            return "Minimum {$s} {$years($s)} of experience{$as}";
        }
        if ($s === 0) {
            return "Maximum {$e} {$years($e)} of experience{$as}";
        }

        return "Minimum {$s} - {$e} Year of experience{$as}";
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
