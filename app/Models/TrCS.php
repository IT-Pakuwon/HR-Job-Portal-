<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrCS extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table      = 'tr_cs';
    protected $primaryKey = 'id';
    public    $incrementing = true;
    protected $keyType    = 'int';

    protected $guarded = ['id'];

    protected $casts = [
        'csdate'       => 'date',
        'submitdate'   => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
        'completed_at' => 'datetime',

        'budget_perpost' => 'integer',
        'revcsid'        => 'integer',

        // vendor1
        'totalvendor1'              => 'decimal:2',
        'ppnvendor1'                => 'decimal:2',
        'pphvendor1'                => 'decimal:2',
        'taxvendor1'                => 'decimal:2',
        'grandtotalvendor1'         => 'decimal:2',
        'totalselectedvendor1'      => 'decimal:2',
        'taxselectedvendor1'        => 'decimal:2',
        'grandtotalselectedvendor1' => 'decimal:2',

        // vendor2
        'totalvendor2'              => 'decimal:2',
        'ppnvendor2'                => 'decimal:2',
        'pphvendor2'                => 'decimal:2',
        'taxvendor2'                => 'decimal:2',
        'grandtotalvendor2'         => 'decimal:2',
        'totalselectedvendor2'      => 'decimal:2',
        'taxselectedvendor2'        => 'decimal:2',
        'grandtotalselectedvendor2' => 'decimal:2',

        // vendor3
        'totalvendor3'              => 'decimal:2',
        'ppnvendor3'                => 'decimal:2',
        'pphvendor3'                => 'decimal:2',
        'taxvendor3'                => 'decimal:2',
        'grandtotalvendor3'         => 'decimal:2',
        'totalselectedvendor3'      => 'decimal:2',
        'taxselectedvendor3'        => 'decimal:2',
        'grandtotalselectedvendor3' => 'decimal:2',

        // vendor4
        'totalvendor4'              => 'decimal:2',
        'ppnvendor4'                => 'decimal:2',
        'pphvendor4'                => 'decimal:2',
        'taxvendor4'                => 'decimal:2',
        'grandtotalvendor4'         => 'decimal:2',
        'totalselectedvendor4'      => 'decimal:2',
        'taxselectedvendor4'        => 'decimal:2',
        'grandtotalselectedvendor4' => 'decimal:2',

        // vendor5
        'totalvendor5'              => 'decimal:2',
        'ppnvendor5'                => 'decimal:2',
        'pphvendor5'                => 'decimal:2',
        'taxvendor5'                => 'decimal:2',
        'grandtotalvendor5'         => 'decimal:2',
        'totalselectedvendor5'      => 'decimal:2',
        'taxselectedvendor5'        => 'decimal:2',
        'grandtotalselectedvendor5' => 'decimal:2',

        // vendor6
        'totalvendor6'              => 'decimal:2',
        'ppnvendor6'                => 'decimal:2',
        'pphvendor6'                => 'decimal:2',
        'taxvendor6'                => 'decimal:2',
        'grandtotalvendor6'         => 'decimal:2',
        'totalselectedvendor6'      => 'decimal:2',      
        'taxselectedvendor6'       => 'decimal:2',
        'grandtotalselectedvendor6' => 'decimal:2',
    ];

    // contoh relasi jika created_by/updated_by/completed_by adalah username user
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'username');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by', 'username');
    }
}
