<?php

namespace App\Models\Vms;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $connection = 'mysql7';

    protected $table = 'master_document';

    protected $primaryKey = 'document_id';

    public $timestamps = false;

    protected $fillable = ['document_name', 'document_remark', 'devision_id', 'document_order', 'has_expire_date', 'status', 'has_conditional', 'conditional_date', 'multiple_files', 'related_privy'];

    protected $casts = ['status' => 'integer', 'document_order' => 'integer'];
}
