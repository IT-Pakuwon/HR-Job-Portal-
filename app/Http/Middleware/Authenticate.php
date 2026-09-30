<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if ($request->expectsJson() || $request->ajax()) {
            return null;
        }

        return route('login');
    }

    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson() || $request->ajax()) {
            abort(response()->json(['message' => 'Session expired. Please log in again.'], 401));
        }

        throw new \Illuminate\Auth\AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }

    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        // Catches remember-me auto-login and accounts deactivated mid-session,
        // not just credentials submitted at the login form.
        if (Auth::check() && Auth::user()->status !== 'A') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Your Account is not active, please check with your IT';

            if ($request->expectsJson() || $request->ajax()) {
                abort(response()->json(['message' => $message], 401));
            }

            return redirect()->route('login')->withErrors(['login' => $message]);
        }

        return $next($request);
    }
}
