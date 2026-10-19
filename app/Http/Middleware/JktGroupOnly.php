<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Restricts a route group to users of the JKT group company (users.group_cpny_id).
class JktGroupOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(strtoupper(trim((string) optional($request->user())->group_cpny_id)) === 'JKT', 403);

        return $next($request);
    }
}
