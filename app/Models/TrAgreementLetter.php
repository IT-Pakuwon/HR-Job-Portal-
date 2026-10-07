<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrAgreementLetter extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_agreement_letter';

    public $timestamps = false;

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    protected $fillable = [
        'agreement_id', 'agreement_step_order', 'letter_type', 'cpny_id',
        'letter_year', 'letter_seq', 'letter_no', 'sent_at',
    ];
}
