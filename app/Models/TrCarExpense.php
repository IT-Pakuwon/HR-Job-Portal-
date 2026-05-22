<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrCarExpense extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql5';

    protected $table = 'tr_car_expense';

    protected $fillable = [
        'refnbr',
        'ref_date',
        'nopol',
        'driver',
        'cost_type',
        'cost_descr',
        'cost_amount',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'ref_date' => 'date',
        'cost_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(
            MsVehicle::class,
            'nopol',
            'nopol'
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by',
            'username'
        );
    }

    public function updater()
    {
        return $this->belongsTo(
            User::class,
            'updated_by',
            'username'
        );
    }

    public function deleter()
    {
        return $this->belongsTo(
            User::class,
            'deleted_by',
            'username'
        );
    }
}
