<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class vCsRevision extends Model
{
    protected $connection  = 'pgsql';
    protected $table       = 'v_po_reuse';
    protected $primaryKey  = 'row_id';
    public $incrementing   = false;
    protected $keyType     = 'string';
    public $timestamps     = false;

   
}
