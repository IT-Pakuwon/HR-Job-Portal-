<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrPO extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'tr_po';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'ponbr','poautonbr','podate','potype','cpny_id','csid','sppbjktid','department_id',
        'user_peminta','keperluan','ponote',
        'vendorid','vendorname','vendoralamat','vendortelp','vendorcp','vendortop',
        'totalamt','taxcodeid','taxamt','grandtotalamt',
        'submitdate','podeliverydate','spkstartworkingdate','spkendtworkingdate',
        'spktotalday','spkworkschedule','spkmanpower','spkpic','spkwarranty',
        'status','created_by','updated_by','completed_by','completed_at',
    ];

    protected $casts = [
        'submitdate' => 'datetime',
        'podate'     => 'date',
        'completed_at' => 'datetime',
    ];

    // Relasi
    public function details()
    {
        return $this->hasMany(TrPOdetail::class, 'ponbr', 'ponbr');
    }

    public function cs()
    {
        return $this->belongsTo(TrCS::class, 'csid', 'csid');
    }
}
