<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Biscolab\ReCaptcha\Facades\ReCaptcha;
use Illuminate\Support\Facades\Session;

class VerifyRecaptcha
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('recaptcha.enabled')) {
            return $next($request);
        }

        $recaptchaResponse = $request->input('g-recaptcha-response');
        $response = ReCaptcha::validate($recaptchaResponse);

        if (!$response->isSuccess()) {
            return back()->withErrors(['captcha' => 'Captcha verification failed.']);
        }

        return $next($request);
    }
}
