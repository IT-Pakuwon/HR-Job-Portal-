<?php

namespace App\Models\Vms;

use Illuminate\Database\Eloquent\Model;

class InvoiceDocument extends Model
{
    protected $connection = 'mysql7';

    protected $table = 'ord_masterdoc';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'Module', 'document_name', 'DivisionID', 'Required', 'scan_imagick', 'ICDP'];
}
