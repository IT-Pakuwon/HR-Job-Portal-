<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrItrecommendDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_itrecommend_detail';

    protected $fillable = [
        'docid',
        'recommend_descr',
        'qty',
        'uom',
        'category',
        'subcategory',
        'recommend_note',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function header()
    {
        return $this->belongsTo(TrItrecommend::class, 'docid', 'docid');
    }
}
