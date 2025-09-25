<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrCSdetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection  = 'pgsql';
    protected $table       = 'tr_cs_detail'; // ganti bila berbeda di DB
    protected $primaryKey  = 'id';
    public $incrementing   = true;
    protected $keyType     = 'int';

    // gampangnya: larang mass-assign hanya id
    protected $guarded = ['id'];

    protected $casts = [
        // waktu
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',

        // numerik utama
        'qty'                   => 'decimal:2',
        'base_qty'              => 'decimal:2',
        'inventory_last_price'  => 'decimal:2',

        // vendor 1
        'vendorprice1'          => 'decimal:2',
        'vendortotalprice1'     => 'decimal:2',
        'vendor1selected'       => 'boolean',

        // vendor 2
        'vendorprice2'          => 'decimal:2',
        'vendortotalprice2'     => 'decimal:2',
        'vendor2selected'       => 'boolean',

        // vendor 3
        'vendorprice3'          => 'decimal:2',
        'vendortotalprice3'     => 'decimal:2',
        'vendor3selected'       => 'boolean',

        // vendor 4
        'vendorprice4'          => 'decimal:2',
        'vendortotalprice4'     => 'decimal:2',
        'vendor4selected'       => 'boolean',

        // vendor 5
        'vendorprice5'          => 'decimal:2',
        'vendortotalprice5'     => 'decimal:2',
        'vendor5selected'       => 'boolean',

        // vendor 6
        'vendorprice6'          => 'decimal:2',
        'vendortotalprice6'     => 'decimal:2',
        'vendor6selected'       => 'boolean',

        // lain-lain
        'budget_perpost'        => 'integer',

        // pengikat header numerik (kalau dipakai)
        'cs_no'                 => 'integer',
        'sppbjkt_no'               => 'integer',
    ];

    /* =========================
       RELATIONS (pilih sesuai FK yang kamu pakai)
       ========================= */

    public function location()
    {
        return $this->belongsTo(MsLocationPG::class, 'location_id', 'location_id');
    }

    // sub_location_id (FK) -> MsSubLocationPG.sublocationid (PK)
    public function subLocation()
    {
        return $this->belongsTo(MsSubLocationPG::class, 'sub_location_id', 'sub_location_id');
    }
}
