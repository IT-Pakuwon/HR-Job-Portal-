<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts'; // Sesuai dengan standar penamaan tabel Laravel
    
    protected $fillable = ['title', 'content'];
    public $timestamps = true; // Pastikan timestamps aktif jika kolom created_at dan updated_at ada
}
