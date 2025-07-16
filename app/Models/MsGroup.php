<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsGroup extends Model
{
    use HasFactory;

    protected $table = 'ms_group'; // Nama tabel
    protected $primaryKey = 'group_id'; // Primary key
    protected $fillable = ['group_code', 'group_name', 'status']; // Kolom yang bisa diisi
    public $timestamps = true; // Gunakan timestamps jika ada kolom created_at & updated_at

    public static function getAllGroups()
    {
        return self::all(); // Mengambil semua data groups
    }
}
