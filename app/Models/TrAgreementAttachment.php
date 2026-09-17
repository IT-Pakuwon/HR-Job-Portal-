<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrAgreementAttachment extends Model
{
    protected $connection = 'pgsql5';
    protected $table = 'tr_agreement_attachment';

    protected $casts = [
        'attachment_date' => 'datetime',
    ];

    protected $fillable = [
        'agreement_id', 'renewal_sequence', 'attachment_date', 'cpny_id', 'attachment_name',
        'folder', 'filename', 'filesize', 'extention',
        'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at',
    ];

    public function agreement()
    {
        return $this->belongsTo(TrAgreement::class, 'agreement_id', 'agreement_id');
    }
}
