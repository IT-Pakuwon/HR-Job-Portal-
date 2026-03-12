<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class TrPO extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'tr_po';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [      

        'ponbr' , 'poautonbr' , 'podate' , 'potype' , 'cpny_id' , 'csid' , 'sppbjktid' , 'department_id' , 'user_peminta' , 
        'keperluan' , 'ponote' , 'vendorid' , 'vendorname' , 'vendoralamat' , 'vendortelp' , 'vendorcp' , 'vendornote','vendortop' , 
        'totalamt' , 'taxcodeid' , 'taxamt' , 'grandtotalamt' , 'totalqty' , 'totalqtyreceived' , 'submitdate' , 'podeliverydate' , 
        'spkstartworkingdate' , 'spkendtworkingdate' , 'spktotalday' , 'spkworkschedule' , 'spkmanpower' , 'spkpic' ,
        'spkpicjabatan' , 'spkpicphone' , 'spkpicemail' , 'spkvendor' , 'spkvendorjabatan' , 'spkvendorphone' , 'spkvendoremail' ,
        'spkwarranty' , 'spkcarabayar' , 'send_email' , 'send_email_at' , 'reuse' , 'reuse_at' , 'status' , 'created_by' , 'created_at' , 
        'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at' , 'completed_by' , 'completed_at, is_transfer'
    ];

    protected $casts = [
        // tanggal / waktu
        'podate'              => 'date',
        'submitdate'          => 'datetime',
        'podeliverydate'      => 'date',
        'spkstartworkingdate' => 'date',
        'spkendtworkingdate'  => 'date',
        'send_email_at'       => 'datetime',
        'reused_at'           => 'datetime',
        'completed_at'        => 'datetime',
        'deleted_at'          => 'datetime',

        // numerik
        'totalamt'            => 'decimal:2',
        'taxamt'              => 'decimal:2',
        'grandtotalamt'       => 'decimal:2',
        'totalqty'            => 'decimal:2',
        'totalqtyreceived'    => 'decimal:2',
        'spktotalday'         => 'integer',
        'spkmanpower'         => 'integer',

        // boolean / flag
        'send_email'          => 'boolean',
        'reuse'               => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

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
