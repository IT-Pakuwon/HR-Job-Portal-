<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDas extends Model
{
    protected $connection = 'mysql2';
    protected $table = "users";   
    protected $fillable = ['username', 'name'];
    
}
