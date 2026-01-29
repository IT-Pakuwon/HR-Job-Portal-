<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $connection = 'pgsql3';
    protected $table = 'trx_agenda'; // Sesuai dengan standar penamaan tabel Laravel
    // protected $primaryKey = 'agenda_id'; // Ubah primary key ke screen_id
    protected $fillable = [     
        'docid',
        'agendadate',
        'agendatype',
        'cpnyid',
        'departementid',
        'agendapriority',
        'title',
        'description',
        'participant',
        'startdate',
        'enddate',
        'duedate',
        'site',
        'location',
        'location_address',
        'refid',
        'reftype',
        'agenda_note',
        'status',
        'created_user',
        'updated_user'
    ];
   
}
