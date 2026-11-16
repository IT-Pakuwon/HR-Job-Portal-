<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTemplateVendorPerfomanceScore extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'ms_template_vendor_perfomance_score';
    protected $fillable = [
        'template_vpe_id',
        'cpny_id',
        'department_id',
        'template_vpe_aspect_id',
        'template_vpe_aspect_score',
        'template_vpe_aspect_nilai',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
}
