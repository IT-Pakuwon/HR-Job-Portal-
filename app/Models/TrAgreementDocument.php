<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrAgreementDocument extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_agreement_document';

    protected $casts = [
        'agreementdocument_required' => 'boolean',
        'agreementdocument_received' => 'boolean',
        'agreementdocument_received_at' => 'datetime',
    ];

    protected $fillable = [
        'agreement_id', 'cpny_id', 'agreementdocument_id', 'agreementdocument_descr',
        'agreementdocument_required', 'agreementdocument_received', 'agreementdocument_received_at', 'agreementdocument_note',
        'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at',
    ];

    public function agreement()
    {
        return $this->belongsTo(TrAgreement::class, 'agreement_id', 'agreement_id');
    }

    public function document()
    {
        return $this->belongsTo(MsAgreementDocument::class, 'agreementdocument_id', 'agreementdocument_id');
    }
}
