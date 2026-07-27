<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MsGroup extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_group';

    protected $fillable = [
        'group_id',
        'group_name',
        'group_description',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    public function details()
    {
        return $this->hasMany(TrGroupDetail::class, 'group_id', 'group_id')
            ->where('status', 'A');
    }

    public function projects()
    {
        return $this->hasMany(MsProject::class, 'group_id', 'group_id');
    }

    public function departmentOprIds()
    {
        return $this->details()->pluck('department_opr_id');
    }

    public function members()
    {
        return $this->hasMany(TrGroupMember::class, 'group_id', 'group_id')
            ->where('status', 'A');
    }

    // Candidate pool for the "add member" picker: must hold PROJECTACCESS
    // (module access) and their ms_department must chain into one of this
    // Group's assigned ms_department_opr rows. Departments narrow who's
    // *offered* — being in this pool does not itself grant membership.
    public function candidateUsers()
    {
        $departmentIds = MsDepartment::whereIn('department_opr_id', $this->departmentOprIds())
            ->pluck('department_id');

        return static::candidateUsersForDepartments($departmentIds);
    }

    // Same candidate-pool query, usable before a Group has been saved yet
    // (e.g. while building the create form) — see PmGroupController.
    public static function candidateUsersForDepartmentOprIds($departmentOprIds)
    {
        $departmentIds = MsDepartment::whereIn('department_opr_id', $departmentOprIds)
            ->pluck('department_id');

        return static::candidateUsersForDepartments($departmentIds);
    }

    private static function candidateUsersForDepartments($departmentIds)
    {
        $projectUsernames = SysUserRole::where('role_id', 'PROJECTACCESS')
            ->where('status', 'A')
            ->pluck('username')
            ->map(fn ($u) => strtolower(trim($u)));

        return User::whereIn('department_id', $departmentIds)
            ->whereIn(DB::raw('lower(username)'), $projectUsernames->all())
            ->get();
    }

    // Actual Group members (explicitly added via tr_group_member) — this is
    // what backs Task/Project assignee pickers, access checks, and
    // @mention audiences, NOT the raw department-matched candidate pool.
    public function eligibleUsers()
    {
        $usernames = $this->members()->pluck('username')
            ->map(fn ($u) => strtolower(trim($u)));

        return User::whereIn(DB::raw('lower(username)'), $usernames->all())->get();
    }
}
