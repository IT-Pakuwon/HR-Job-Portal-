<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrVendorPerfomanceDetail extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'tr_vendor_perfomance_detail';
    protected $fillable = [
        'vendor_perfomance_id',
        'vendor_perfomance_type',
        'ponbr',
        'cpny_id',
        'template_vpe_id',
        'template_vpe_aspect_id',
        'template_vpe_aspect_descr',
        'template_vpe_subaspect_id',
        'template_vpe_subaspect_descr',
        'template_vpe_subaspect_kriteria',
        'template_vpe_subaspect_note',
        'template_vpe_subaspect_bobot',
        'template_vpe_subaspect_score',
        'template_vpe_subaspect_nilai',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
}
