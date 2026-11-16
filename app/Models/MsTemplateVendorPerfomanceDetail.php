<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTemplateVendorPerfomanceDetail extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'ms_template_vendor_perfomance_detail';
    protected $fillable = [
        'cpny_id',
        'department_id',
        'template_vpe_id',
        'template_vpe_aspect_id',
        'template_vpe_aspect_descr',
        'template_vpe_subaspect_id',
        'template_vpe_subaspect_descr',
        'template_vpe_subaspect_kriteria',
        'template_vpe_subaspect_note',
        'template_vpe_aspect_bobot',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
}
