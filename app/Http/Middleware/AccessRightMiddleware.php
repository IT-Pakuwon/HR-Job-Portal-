<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\SysUserRole;
use App\Models\SysAccessRight;

class AccessRightMiddleware
{
    public function handle($request, Closure $next, $screenId, $action)
    {
        // if (!auth()->check()) {
        //     abort(401);
        // }
        if (!auth()->check()) {
            return redirect()->guest(route('login')); // penting: guest => set intended
        }

        $username = auth()->user()->username;

        // Ambil semua role user
        $roleIds = SysUserRole::where('username', $username)
            ->where('status', 'A')
            ->pluck('role_id');

        // Cari access right yang cocok dengan screen + action
        $access = SysAccessRight::whereIn('role_id', $roleIds)
            ->where('screen_id', $screenId)
            ->where('access_name', $action)   // ← perubahan utamanya
            ->where('access_right', true)   // ← perubahan utamanya
            ->where('status', 'A')
            ->first();

        // Debug jika perlu
        // \Log::debug('ACCESS', ['user'=>$username, 'screen'=>$screenId, 'action'=>$action, 'data'=>$access]);

        // Jika tidak ada baris permission → tidak boleh akses
        if (!$access || !$access->access_right) {
            abort(403, "Forbidden");
        }

        return $next($request);
    }

}
