<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsScreen extends Model
{
    use HasFactory;

    protected $table = 'ms_screen'; // Sesuai dengan standar penamaan tabel Laravel
    protected $primaryKey = 'screen_id'; // Ubah primary key ke screen_id
    protected $fillable = ['screen_code', 'screen_name', 'status'];
    public $timestamps = true; // Pastikan timestamps aktif jika kolom created_at dan updated_at ada
}
