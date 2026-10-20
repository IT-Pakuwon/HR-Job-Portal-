<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JktGroupOnlyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(strtoupper(trim((string) optional($request->user())->group_cpny_id)) === 'JKT', 403);

        return $next($request);
    }
}
