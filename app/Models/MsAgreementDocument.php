<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsAgreementDocument extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'ms_agreement_document';

    protected $casts = [
        'agreementdocument_required' => 'boolean',
    ];

    protected $fillable = [
        'agreementdocument_id', 'agreementdocument_descr', 'agreementdocument_required',
        'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at',
    ];

    public function agreementDocuments()
    {
        return $this->hasMany(TrAgreementDocument::class, 'agreementdocument_id', 'agreementdocument_id');
    }
}
