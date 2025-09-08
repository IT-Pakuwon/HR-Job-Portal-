<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bq extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_bq";
    
    protected $fillable = [
        'bqid',
        'sppjtid',
        'cpny_id',
        'bq_type',
        'grand_total_est_material_price',
        'grand_total_est_jasa_price',
        'status',
        'created_by',
        'updated_by',
        'completed_by',

    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
