<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrProject extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_project';

    protected $fillable = [
        'project_id',
        'linked_project_id',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    public function project()
    {
        return $this->belongsTo(MsProject::class, 'project_id', 'project_id');
    }

    public function linkedProject()
    {
        return $this->belongsTo(MsProject::class, 'linked_project_id', 'project_id');
    }
}
