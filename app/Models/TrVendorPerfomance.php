<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrVendorPerfomance extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'tr_vendor_perfomance';
    protected $fillable = [
        'vendor_perfomance_id',
        'vendor_perfomance_date',
        'vendor_perfomance_type',
        'ponbr',
        'cpny_id',
        'csid',
        'sppbjktid',
        'department_id',
        'user_peminta',
        'keperluan',
        'vendor_perfomance_note',
        'vendorid',
        'vendorname',
        'vendor_perfomance_start_date',
        'vendor_perfomance_end_date',
        'template_vpe_id',
        'template_vpe_descr',
        'template_vpe_aspect_grandtotal_score',
        'template_vpe_aspect_result',
        'template_vpe_aspect_comment',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
}
