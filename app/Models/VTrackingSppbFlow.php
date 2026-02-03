<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VTrackingSppbFlow extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'v_tracking_sppb_flow';

    // VIEW: tidak ada PK yang jelas & tidak increment
    public $incrementing = false;
    public $timestamps   = false;

    // Eloquent butuh primaryKey, tapi untuk read-only aman set dummy
    protected $primaryKey = 'sppb_no';

    // rekomendasi: lindungi dari mass assign
    protected $guarded = [];

    // opsional: cast tanggal biar gampang format
    protected $casts = [
        'sppb_date' => 'date',
        'cs_date' => 'date',
        'po_date' => 'date',
        'receipt_date' => 'date',

        'sppb_created_at' => 'datetime',
        'cs_created_at' => 'datetime',
        'po_created_at' => 'datetime',
        'receipt_created_at' => 'datetime',

        'sppb_completed_at' => 'datetime',
        'cs_completed_at' => 'datetime',
        'po_completed_at' => 'datetime',
        'receipt_completed_at' => 'datetime',

        'sppb_is_approved' => 'boolean',
        'cs_is_approved' => 'boolean',
        'po_is_approved' => 'boolean',
        'receipt_is_approved' => 'boolean',
    ];

    // paksa read-only (biar gak ada yang iseng save)
    public function save(array $options = [])
    {
        throw new \Exception('VTrackingSppbFlow is read-only (view).');
    }
}
