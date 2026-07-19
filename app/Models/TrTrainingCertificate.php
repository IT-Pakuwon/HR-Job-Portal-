<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrTrainingCertificate extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_training_certificate';

    public $timestamps = false;

    protected $fillable = [
        'registration_id',
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(TrTrainingRegistration::class, 'registration_id', 'id');
    }
}
