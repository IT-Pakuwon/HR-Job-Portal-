<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsProject extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_project';

    protected $fillable = [
        'project_id',
        'group_id',
        'project_name',
        'project_description',
        'start_date',
        'end_date',
        'status_id',
        'progress_percent',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function group()
    {
        return $this->belongsTo(MsGroup::class, 'group_id', 'group_id');
    }

    public function projectStatus()
    {
        return $this->belongsTo(MsProjectStatus::class, 'status_id', 'status_id');
    }

    public function taskStatuses()
    {
        return $this->hasMany(MsTaskStatus::class, 'project_id', 'project_id')
            ->where('status', 'A')
            ->orderBy('sort_order');
    }

    public function tasks()
    {
        return $this->hasMany(TrProjectTask::class, 'project_id', 'project_id');
    }

    // Links out (this project -> others it points to)
    public function links()
    {
        return $this->hasMany(TrProject::class, 'project_id', 'project_id')
            ->where('status', 'A');
    }

    // Links in (others pointing at this project) — the relationship is
    // symmetric in the UI, so both directions need checking.
    public function backLinks()
    {
        return $this->hasMany(TrProject::class, 'linked_project_id', 'project_id')
            ->where('status', 'A');
    }

    // All linked projects regardless of which side stored the row.
    public function linkedProjects()
    {
        $ids = $this->links()->pluck('linked_project_id')
            ->merge($this->backLinks()->pluck('project_id'))
            ->unique()
            ->values();

        return static::whereIn('project_id', $ids)->get();
    }
}
