<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class vCsRevision extends Model
{
    protected $connection  = 'pgsql';
    protected $table       = 'v_sppbjkt_on_progress';
    protected $primaryKey  = 'row_id';
    public $incrementing   = false;
    protected $keyType     = 'string';
    public $timestamps     = false;

    // 👉 otomatis ikut di-serialize ke JSON
    protected $appends = ['created_by_name'];

    public function creator()
    {
        // pastikan Model User memiliki $connection yang benar (server users)
        return $this->belongsTo(User::class, 'created_by', 'username')
                    ->select(['username','name']);
    }

    // 👉 accessor: fallback ke username jika name tidak ada
    public function getCreatedByNameAttribute()
    {
        return optional($this->creator)->name ?? $this->created_by;
    }
}
