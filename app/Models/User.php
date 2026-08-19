<?php

namespace App\Models;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    // protected $connection = 'mysql2';
    // protected $table = "users";
    protected $connection = 'pgsql2';
    protected $table = 'ms_user';
    public $incrementing = true;

    protected $keyType = 'int';
    protected $fillable = [
        'name',
        'username',
        'email',
        'phonenumber',
        'password',
        'cpny_id',
        'business_unit_id',
        'division_id',
        'department_id',
        'user_role',
        'group_cpny_id',
        'origin_cpny_id',
        'origin_department_id',
        'homepage',
        'notification_email',
        'npk',
        'approval_line',
        'jabatan',
        'last_login_at',
        'last_login_ip',
        'login_count',
        'is_darkmode',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_darkmode' => 'boolean',
    ];

    public function roleIds()
    {
        return SysUserRole::where('username', $this->username)
            ->where('status', 'A')
            ->pluck('role_id');
    }

    public function hasRole($roleId)
    {
        return $this->roleIds()->contains($roleId);
    }

    public function isIT()
    {
        return $this->hasRole('ITHARDWARE')
            || $this->hasRole('ITSOFTWARE');
    }

    /**
     * Parsed, lowercased user_role string (e.g. "Admin, AdminSby" -> ['admin', 'adminsby']).
     * Single source of truth so every consumer (middleware, Blade, policies) agrees on
     * what roles a user has, regardless of casing/whitespace in the stored value.
     */
    public function roles(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', strtolower((string) $this->user_role)))));
    }

    public function isAdmin(): bool
    {
        return in_array('admin', $this->roles(), true) || in_array('adminsby', $this->roles(), true);
    }

    /**
     * True for roles that should see every transaction across every company/department and
     * be let into every screen their menu access grants — bypassing cpny_id/department_id
     * scoping AND any hard hasRole('XACCESS') view gate. Distinct from isAdmin() because
     * isAdmin() also gates edit/delete permissions in some controllers, which this must not grant.
     */
    public function hasFullDataScope(): bool
    {
        return $this->hasRole('DIRECTORACCESS');
    }

    /**
     * Company ids this user's transaction lists should be scoped to — every company for
     * hasFullDataScope() roles, otherwise just the user's own comma-separated cpny_id.
     */
    public function scopedCompanyIds(): array
    {
        if ($this->hasFullDataScope()) {
            return \App\Models\MsCompany::pluck('cpny_id')->toArray();
        }

        return collect(explode(',', (string) $this->cpny_id))
            ->map(fn ($x) => trim($x))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Department ids this user's transaction lists should be scoped to — every department for
     * hasFullDataScope() roles, otherwise just the user's own comma-separated department_id.
     */
    public function scopedDepartmentIds(): array
    {
        if ($this->hasFullDataScope()) {
            return \App\Models\MsDepartment::pluck('department_id')->toArray();
        }

        return collect(explode(',', (string) $this->department_id))
            ->map(fn ($x) => trim($x))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Personal check-in barcode value — one per user, valid at any training
     * event (unlike TrLndTrainingRegistration::attendance_code, which is
     * minted per-registration/per-event). The USR- prefix keeps it visually
     * and programmatically distinct from those TRN- codes so a scanner can
     * tell which lookup path to use (see TrainingAttendanceController::scan()).
     */
    public function getBarcodeCodeAttribute(): string
    {
        return 'USR-' . strtoupper($this->username);
    }

    /**
     * Auth (session, remember-me, Auth::id()) identifies users by username instead of
     * the auto-increment id, because `id` is a pgsql2 surrogate key that can drift or be
     * reused across environments/DB copies while `username` stays stable — see other
     * models (Usercpny, Userdept, Userdivision, Userbusinessunit, SysUserRole) which
     * already key off username for the same reason.
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Overrides Jetstream's HasProfilePhoto::profilePhotoUrl(). ms_user has no
     * profile_photo_path column / local disk storage; photos are uploaded through the
     * app's generic GCS attachment pipeline (TrAttachment, doctype=AVATAR — tr_attachment.doctype
     * is varchar(10), so "USERPROFILE" doesn't fit — keyed by username) — see
     * ProfileController::updatePhoto(). The bucket is private, so the URL is a freshly-signed
     * link generated on every access rather than a stored path.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            $attachment = TrAttachment::where('refnbr', $this->username)
                ->where('doctype', 'AVATAR')
                ->where('status', 'A')
                ->latest('id')
                ->first();

            if (!$attachment) {
                return $this->defaultProfilePhotoUrl();
            }

            try {
                $config = config('filesystems.disks.gcs');
                $keyFilePath = $config['key_file'];
                if (!str_starts_with($keyFilePath, '/') && !preg_match('/^[A-Za-z]:\\\\/', $keyFilePath)) {
                    $keyFilePath = base_path($keyFilePath);
                }

                $storage = new StorageClient([
                    'projectId' => $config['project_id'],
                    'keyFilePath' => $keyFilePath,
                ]);

                $objectPath = rtrim($attachment->folder, '/').'/'.$attachment->filename;

                return $storage->bucket($config['bucket'])->object($objectPath)->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                return $this->defaultProfilePhotoUrl();
            }
        });
    }
}
