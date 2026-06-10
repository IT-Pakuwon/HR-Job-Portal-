<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrIMBudgetdetail extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'tr_imbudget_detail';

    protected $fillable = [
        'imbudgetid',
        'doctype',
        'csid',
        'sppbjktid',
        'spbid',
        'issueid',
        'rfp_id',
        'rfpnonpurchaseid',
        'calrnonpurchaseid',
        'budget_perpost',
        'budget_cpny_id',
        'budget_business_unit_id',
        'budget_department_fin_id',
        'budget_account_id',
        'budget_activity_id',
        'budget_activity_descr',
        'amount_expense',
        'budget_remain',
        'budget_needed',
        'budget_requested',
        'note',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];
}