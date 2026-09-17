<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAgreement extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_agreement';

    protected $fillable = [
        'agreement_id', 'renewal_sequence', 'agreement_date', 'prev_agreement_id', 'cpny_id', 'site_id',
        'business_id', 'business_name', 'tenant_no', 'trade_name', 'property_cd', 'floor_id', 'unit_id',
        'business_address', 'pic_penyewa', 'pic_phonenumber_penyewa', 'pic_email_penyewa', 'pic_legal', 'pic_leasing',
        'no_psm_or_addendum', 'psm_or_addendum_date', 'psm_or_addendum_delivery_date',
        'agreement_step_id', 'agreement_step_order', 'agreement_step_created_user', 'agreement_step_created_at',
        'status', 'created_user', 'created_at', 'updated_user', 'updated_at', 'deleted_by', 'deleted_at',
    ];

    public function activities()
    {
        return $this->hasMany(TrAgreementActivity::class, 'agreement_id', 'agreement_id');
    }

    public function attachments()
    {
        return $this->hasMany(TrAgreementAttachment::class, 'agreement_id', 'agreement_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_user', 'username');
    }

    /**
     * pic_legal/pic_leasing hold a comma-separated list of usernames (same
     * convention as User.cpny_id/department_id elsewhere in this app), since
     * more than one PIC can be assigned.
     */
    public static function splitPicList(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values()
            ->all();
    }

    public static function joinPicList(array $usernames): ?string
    {
        $clean = collect($usernames)
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values();

        return $clean->isEmpty() ? null : $clean->implode(',');
    }

    public function picLegalList(): array
    {
        return self::splitPicList($this->pic_legal);
    }

    public function picLeasingList(): array
    {
        return self::splitPicList($this->pic_leasing);
    }

    public function hasPic(string $username): bool
    {
        $username = strtolower(trim($username));

        return in_array($username, array_map('strtolower', $this->picLegalList()), true)
            || in_array($username, array_map('strtolower', $this->picLeasingList()), true);
    }

    public function scopeWherePicLegal($query, string $username)
    {
        return $query->whereRaw("(',' || pic_legal || ',') ILIKE ?", ['%,'.$username.',%']);
    }

    public function scopeWherePicLeasing($query, string $username)
    {
        return $query->whereRaw("(',' || pic_leasing || ',') ILIKE ?", ['%,'.$username.',%']);
    }

    public function scopeWherePicLegalOrLeasing($query, string $username)
    {
        return $query->where(function ($q) use ($username) {
            $q->whereRaw("(',' || pic_legal || ',') ILIKE ?", ['%,'.$username.',%'])
                ->orWhereRaw("(',' || pic_leasing || ',') ILIKE ?", ['%,'.$username.',%']);
        });
    }
}
