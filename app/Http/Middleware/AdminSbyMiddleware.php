<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSbyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403);
        }

        $userRoles = auth()->user()->roles();

        if (!in_array('adminsby', $userRoles, true) && !in_array('admin', $userRoles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
