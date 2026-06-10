<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrIMBudget extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'tr_imbudget';

    protected $fillable = [
        'imbudgetid',
        'imbudgetdate',
        'doctype',
        'csid',
        'sppbjktid',
        'spbid',
        'issueid',
        'rfp_id',
        'rfpnonpurchaseid',
        'calrnonpurchaseid',
        'cpny_id',
        'department_id',
        'user_peminta',
        'keperluan',
        'imbudgetnote',
        'budget_perpost',
        'total_amount_expense',
        'total_budget_remain',
        'total_budget_needed',
        'total_budget_requested',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function userpeminta()
    {
        return $this->belongsTo(User::class, 'user_peminta', 'username');
    }
}