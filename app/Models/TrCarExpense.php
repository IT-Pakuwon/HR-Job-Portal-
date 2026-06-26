<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrCarExpense extends Model
{
    use HasFactory;

    protected $connection = 'pgsql5';
    protected $table = 'tr_car_expense';

    protected $fillable = [
        'refnbr',
        'ref_date',
        'nopol',
        'driver',
        'cost_type',
        'cost_descr',
        'cost_qty',
        'cost_amount',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
}
