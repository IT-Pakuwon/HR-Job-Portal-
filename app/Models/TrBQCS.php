<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrBQCS extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'tr_bq_cs';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false; // karena kamu pakai created_at / updated_at manual

    protected $fillable = [
        'bqid',
        'csid',
        'sppjtid',
        'cpny_id',
        'bq_type',
        'vendorid1',
        'grandtotalmaterialvendor1',
        'grandtotaljasavendor1',
        'vendorid2',
        'grandtotalmaterialvendor2',
        'grandtotaljasavendor2',
        'vendorid3',
        'grandtotalmaterialvendor3',
        'grandtotaljasavendor3',
        'vendorid4',
        'grandtotalmaterialvendor4',
        'grandtotaljasavendor4',
        'vendorid5',
        'grandtotalmaterialvendor5',
        'grandtotaljasavendor5',
        'vendorid6',
        'grandtotalmaterialvendor6',
        'grandtotaljasavendor6',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
        'completed_by',
        'completed_at',
    ];

    /** Relasi ke detail */
    public function details()
    {
        return $this->hasMany(TrBQCSDetail::class, 'bqid', 'bqid');
    }
}
