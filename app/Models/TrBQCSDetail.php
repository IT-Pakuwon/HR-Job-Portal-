<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrBQCSDetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'tr_bq_cs_detail';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        // 'bqid',
        // 'csid',
        // 'sppjtid',
        // 'bq_no',
        // 'bq_line_no',
        // 'bq_descr',
        // 'qty',
        // 'uom',
        // 'vendorid1',
        // 'vendorproductprice1',
        // 'vendortotalproductprice1',
        // 'vendorjasaprice1',
        // 'vendortotaljasaprice1',
        // 'vendorid2',
        // 'vendorproductprice2',
        // 'vendortotalproductprice2',
        // 'vendorjasaprice2',
        // 'vendortotaljasaprice2',
        // 'vendorid3',
        // 'vendorproductprice3',
        // 'vendortotalproductprice3',
        // 'vendorjasaprice3',
        // 'vendortotaljasaprice3',
        // 'vendorid4',
        // 'vendorproductprice4',
        // 'vendortotalproductprice4',
        // 'vendorjasaprice4',
        // 'vendortotaljasaprice4',
        // 'vendorid5',
        // 'vendorproductprice5',
        // 'vendortotalproductprice5',
        // 'vendorjasaprice5',
        // 'vendortotaljasaprice5',
        // 'vendorid6',
        // 'vendorproductprice6',
        // 'vendortotalproductprice6',
        // 'vendorjasaprice6',
        // 'vendortotaljasaprice6',
        // 'status',
        // 'created_by',
        // 'created_at',
        // 'updated_by',
        // 'updated_at',
        // 'deleted_by',
        // 'deleted_at',
        'bqid' , 'csid' , 'sppjtid' , 'bq_no' , 'bq_line_no' , 'bq_descr' , 'qty' , 'uom' , 
        'vendorid1' , 'vendorproductprice1' , 'vendortotalproductprice1' , 'vendorjasaprice1' , 'vendortotaljasaprice1' , 
        'vendorid2' , 'vendorproductprice2' , 'vendortotalproductprice2' , 'vendorjasaprice2' , 'vendortotaljasaprice2' , 
        'vendorid3' , 'vendorproductprice3' , 'vendortotalproductprice3' , 'vendorjasaprice3' , 'vendortotaljasaprice3' , 
        'vendorid4' , 'vendorproductprice4' , 'vendortotalproductprice4' , 'vendorjasaprice4' , 'vendortotaljasaprice4' , 
        'vendorid5' , 'vendorproductprice5' , 'vendortotalproductprice5' , 'vendorjasaprice5' , 'vendortotaljasaprice5' , 
        'vendorid6' , 'vendorproductprice6' , 'vendortotalproductprice6' , 'vendorjasaprice6' , 'vendortotaljasaprice6' , 
        'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];

    /** Relasi ke header */
    public function header()
    {
        return $this->belongsTo(TrBQCS::class, 'bqid', 'bqid');
    }
}
