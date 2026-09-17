<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAgreementActivity extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_agreement_activity';

    protected $casts = [
        'response_date' => 'datetime',
        'working_start_date' => 'datetime',
        'working_end_date' => 'datetime',
    ];

    protected $fillable = [
        'agreement_id', 'cpny_id', 'agreement_step_id', 'agreement_step_order',
        'response_date', 'response_summary', 'response_descr',
        'working_start_date', 'working_end_date', 'status_pekerjaan',
        'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at',
    ];

    public function agreement()
    {
        return $this->belongsTo(TrAgreement::class, 'agreement_id', 'agreement_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }
}
