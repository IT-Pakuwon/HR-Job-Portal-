<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    // Requests from the My Page app (its own pages, or XHR sent from them) must land on the in-app login,
    // because the normal /login is outside the installed app's scope and would open the phone's browser.
    private function loginUrl(Request $request): string
    {
        $refererPath = (string) parse_url((string) $request->headers->get('referer'), PHP_URL_PATH);

        return ($request->is('my-page*') || str_starts_with($refererPath, '/my-page'))
            ? route('my-page.login')
            : route('login');
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Laravel 11 converts TokenMismatchException into an HttpException(419)
        // before renderable callbacks run, so match on the converted form too.
        $expired = function (Throwable $e, Request $request) {
            $isExpired = $e instanceof TokenMismatchException
                || ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 419);

            if (! $isExpired) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please log in again.',
                    'redirect' => $this->loginUrl($request),
                ], 419);
            }

            return redirect()->to($this->loginUrl($request))
                ->with('error', 'Your session has expired. Please log in again.');
        };

        $this->renderable($expired);

        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please log in again.',
                    'redirect' => $this->loginUrl($request),
                ], 401);
            }

            return redirect()->to($this->loginUrl($request))
                ->with('error', 'Your session has expired. Please log in again.');
        });
    }
}
