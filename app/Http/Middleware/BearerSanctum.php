<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BearerSanctum
{
    public function handle(Request $request, Closure $next)
    {
        $bearer = $request->bearerToken();

        if (!$bearer) {
            return response()->json(['message' => 'Unauthorized. Missing token.'], 401);
        }

        $tokenModel = config('sanctum.personal_access_token_model');
        $pat = $tokenModel::findToken($bearer);

        if (!$pat) {
            return response()->json(['message' => 'Unauthorized. Invalid token.'], 401);
        }

        $user = $pat->tokenable;

        if (!$user) {
            return response()->json(['message' => 'Unauthorized. User not found.'], 401);
        }

        // ✅ set user ke request & auth context
        $request->setUserResolver(fn () => $user);
        auth()->setUser($user);

        // optional: update last_used_at
        try {
            $pat->forceFill(['last_used_at' => now()])->save();
        } catch (\Throwable $e) {}

        return $next($request);
    }
}
