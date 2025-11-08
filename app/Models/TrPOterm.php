<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class TrPOterm extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'tr_po_term';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        // 'ponbr',
        // 'cpny_id',
        // 'csid',
        // 'sppbjktid',
        // 'bqid',
        // 'department_id',
        // 'user_peminta',
        // 'keperluan',
        // 'vendorid',
        // 'vendorname',
        // 'terms_id',
        // 'topid',
        // 'top_type',
        // 'terms_name',
        // 'progress_pct',
        // 'payment_pct',
        // 'terms_type',
        // 'flag_bast',
        // 'rfcaid',
        // 'calrid',
        // 'bastid',
        // 'poamount',
        // 'bastamount',
        // 'penalty',
        // 'dayslate',
        // 'realizeamount',
        // 'status',
        // 'created_by',
        // 'updated_by',
        // 'completed_by'
        'ponbr' , 'cpny_id' , 'csid' , 'sppbjktid' , 'bqid' , 'department_id' , 'user_peminta' , 'keperluan' , 'vendorid' , 
        'vendorname' , 'order_term' , 'terms_id' , 'topid' , 'top_type' , 'terms_name' , 'payment_pct' , 'progress_pct' , 
        'terms_type' , 'flag_bast' , 'rfcaid' , 'calrid' , 'bastid' , 'poamount' , 'bastamount' , 'penalty' , 'dayslate' , 
        'realizeamount' , 'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at' , 
        'completed_by' , 'completed_at'
    ];

    
}
