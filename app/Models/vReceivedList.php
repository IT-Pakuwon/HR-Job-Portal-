<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vReceivedList extends Model
{
    protected $connection  = 'pgsql';
    protected $table       = 'v_received_list';
    protected $primaryKey  = 'row_id';
    public $incrementing   = false;
    protected $keyType     = 'string';
    public $timestamps     = false;

    // Jika ingin tetap akses relasi user dari created_by:
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }
   
    
}
