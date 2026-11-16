<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTemplateVendorPerfomance extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'ms_template_vendor_perfomance';
    protected $fillable = [
        'template_vpe_id',
        'cpny_id',
        'department_id',
        'template_vpe_descr',
        'template_vpe_aspect_id',
        'template_vpe_aspect_descr',
        'template_vpe_aspect_total_bobot',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
}
