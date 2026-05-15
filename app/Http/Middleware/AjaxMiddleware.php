<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AjaxMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->ajax()) {

            abort(404);

        }

        return $next($request);
    }
}
