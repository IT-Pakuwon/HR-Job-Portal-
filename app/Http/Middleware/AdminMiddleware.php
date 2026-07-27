<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $userRoles = array_map('trim', explode(',', auth()->user()->user_role ?? ''));

        if (!auth()->check() || !in_array('admin', $userRoles)) {
            abort(403);
        }

        return $next($request);
    }
}
