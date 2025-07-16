<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'trx_news';    
    protected $fillable = [     
        'docid',
        'newsdate',
        'newstype',
        'cpnyid',
        'departementid',
        'newspriority',
        'title',
        'description',
        'participant',
        'startdate',
        'enddate',
        'duedate',
        'status',
        'created_user',
        'updated_user'
    ]; 

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'docid', 'docid');
    }
}
