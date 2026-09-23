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

    /**
     * "screen_id|application_id" keys the given username's active roles grant access to,
     * via sys_role_menu. Used to gate menu-favourite reads/writes to what the user can actually see.
     */
    public static function allowedScreenKeysForUsername(string $username): \Illuminate\Support\Collection
    {
        $roleIds = SysUserRole::where('username', $username)
            ->where('status', 'A')
            ->pluck('role_id');

        $menuIds = SysRoleMenu::whereIn('role_id', $roleIds)
            ->where('status', 'A')
            ->pluck('menu_id')
            ->unique();

        return static::whereIn('menu_id', $menuIds)
            ->where('status', 'A')
            ->whereNotNull('screen_id')
            ->get(['screen_id', 'application_id'])
            ->map(fn ($m) => $m->screen_id . '|' . $m->application_id)
            ->unique()
            ->values();
    }
}
