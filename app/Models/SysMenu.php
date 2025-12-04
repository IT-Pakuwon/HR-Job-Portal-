<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysMenu extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "sys_menu";
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'menu_id',
        'parent_menu_id',
        'menu_name',
        'menu_route',
        'menu_url',
        'menu_icon',
        'menu_sort_order',
        'screen_id',
        'application_id',        
        'status',
        'created_by',
        'updated_by'
       
    ];

    public function children()
    {
        return $this->hasMany(SysMenu::class, 'parent_menu_id', 'menu_id')
            ->orderBy('menu_sort_order');
    }
}
