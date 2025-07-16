<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    // protected function redirectTo(Request $request): ?string
    // {
    //     return $request->expectsJson() ? null : route('login');
    // }
    
    protected function redirectTo(Request $request): ?string
    {
        // Jika request mengharapkan JSON, jangan redirect, cukup kembalikan null
        if ($request->expectsJson()) {
            return null;
        }

        // Jika tidak, arahkan ke halaman login
        return route('login');
    }

    /**
     * Handle an unauthenticated user.
     */
    protected function unauthenticated($request, array $guards)
    {
        // Jika request mengharapkan JSON, kirim response 401 Unauthorized
        if ($request->expectsJson()) {
            abort(response()->json(['message' => 'Unauthorized. Please log in.'], 401));
        }

        // Jika bukan JSON, redirect ke login
        return redirect()->guest(route('login'));
    }
}
