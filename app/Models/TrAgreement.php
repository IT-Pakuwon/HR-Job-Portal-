<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAgreement extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_agreement';

    protected $fillable = [
        'agreement_id', 'renewal_sequence', 'agreement_date', 'prev_agreement_id', 'parent_agreement_id', 'agreement_type', 'cpny_id', 'site_id',
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

    public function documents()
    {
        return $this->hasMany(TrAgreementDocument::class, 'agreement_id', 'agreement_id');
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

    /**
     * Comma-joined display names for a PIC username list; a username with no
     * matching user falls back to the raw username.
     */
    public static function picDisplayNames(array $usernames): string
    {
        if (! $usernames) {
            return '';
        }

        $names = \App\Models\User::query()
            ->whereIn('username', $usernames)
            ->pluck('name', 'username')
            ->mapWithKeys(fn ($name, $username) => [strtolower($username) => $name]);

        return collect($usernames)
            ->map(fn ($u) => $names->get(strtolower($u)) ?: $u)
            ->implode(', ');
    }

    public function hasPic(string $username): bool
    {
        $username = strtolower(trim($username));

        return in_array($username, array_map('strtolower', $this->picLegalList()), true)
            || in_array($username, array_map('strtolower', $this->picLeasingList()), true);
    }

    // PSM/OLA: the creator and the PIC Legal(s) may change the agreement.
    public function canBeUpdatedBy(string $username): bool
    {
        $username = strtolower(trim($username));

        return $username !== ''
            && ($username === strtolower((string) $this->created_user)
                || in_array($username, array_map('strtolower', $this->picLegalList()), true));
    }

    // Types made by New Agreement (PSM / OLA / Addendum). Agreement FU never handles these.
    public const NEW_AGREEMENT_TYPES = ['PSM', 'OLA', 'PEMBUATAN', 'ADDENDUM'];

    // Agreement FU's Job tab lists PSM / OLA agreements; creating the follow-up
    // converts that row in place to the matching FU type (Mall PSM -> FU_PSM, Office OLA -> FU_OLA).
    public const FU_SOURCE_TYPES = ['PSM', 'OLA'];

    public const FU_TYPES = ['FU_PSM', 'FU_OLA'];

    public static function fuTypeFor(?string $type): ?string
    {
        return ['PSM' => 'FU_PSM', 'OLA' => 'FU_OLA'][$type] ?? null;
    }

    // Agreement FU's own agreements: everything except the New Agreement types.
    public function scopeFollowUp($query)
    {
        return $query->where(fn ($q) => $q->whereNull('agreement_type')->orWhereNotIn('agreement_type', self::NEW_AGREEMENT_TYPES));
    }

    // Addendums still Active that were made from this agreement (it can't be cancelled while they are).
    public static function activeAddendumIds(string $agreementId): array
    {
        return static::query()
            ->whereNull('deleted_at')
            ->where('agreement_type', 'ADDENDUM')
            ->where('agreement_step_id', 'ACTIVE')
            ->where('prev_agreement_id', $agreementId)
            ->pluck('agreement_id')
            ->all();
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
