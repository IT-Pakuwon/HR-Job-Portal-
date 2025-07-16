<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsApplication extends Model
{
    use HasFactory;

    protected $table = 'ms_application'; // Sesuai dengan standar penamaan tabel Laravel
    protected $primaryKey = 'application_id'; // Ubah primary key ke screen_id
    protected $fillable = ['application_code', 'application_name', 'status'];
    public $timestamps = true; // Pastikan timestamps aktif jika kolom created_at dan updated_at ada

    public static function getAllApplications()
    {
        return self::all(); // Ambil semua data aplikasi
    }

}
