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

    // Users eligible to be added to Projects under this Group: must hold
    // PROJECTACCESS (module access) and their ms_department must chain
    // into one of this Group's assigned ms_department_opr rows.
    public function eligibleUsers()
    {
        $departmentIds = MsDepartment::whereIn('department_opr_id', $this->departmentOprIds())
            ->pluck('department_id');

        $projectUsernames = SysUserRole::where('role_id', 'PROJECTACCESS')
            ->where('status', 'A')
            ->pluck('username')
            ->map(fn ($u) => strtolower(trim($u)));

        return User::whereIn('department_id', $departmentIds)
            ->whereIn(DB::raw('lower(username)'), $projectUsernames->all())
            ->get();
    }
}
