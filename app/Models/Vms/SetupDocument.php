<?php

namespace App\Models\Vms;

use Illuminate\Database\Eloquent\Model;

class SetupDocument extends Model
{
    protected $connection = 'mysql7';

    protected $table = 'setup_document';

    public $timestamps = false;

    protected $fillable = ['category_id', 'document_id', 'is_mandatory', 'is_published', 'created_date'];

    protected $casts = ['is_mandatory' => 'integer', 'is_published' => 'integer'];
}
