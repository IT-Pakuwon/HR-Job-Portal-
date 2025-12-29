<?php

use Illuminate\Http\Request;
use App\Http\Controllers\MsGroupController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\MsApplicationController;
use App\Http\Controllers\ProjectTaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MsVendorController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

Route::get('/auth-header-debug', function (Request $request) {
    return response()->json([
        'authorization_header' => $request->header('authorization'),
        'bearer_token'         => $request->bearerToken(),
        'all_headers'          => collect($request->headers->all())->map(fn($v)=>$v[0] ?? $v),
    ]);
});

// Route::get('/__authdebug', function (Request $r) {
//     $bearer = $r->bearerToken();

//     $m = config('sanctum.personal_access_token_model');
//     $pat = $bearer ? $m::findToken($bearer) : null;

//     return response()->json([
//         'has_authorization_header' => $r->headers->has('authorization'),
//         'authorization_header' => $r->headers->get('authorization'),
//         'bearerToken' => $bearer,
//         'findToken' => (bool) $pat,
//         'token_id' => $pat->id ?? null,
//         'token_conn' => $pat?->getConnectionName(),
//         'tokenable_id' => $pat->tokenable_id ?? null,
//     ]);
// });

Route::get('/__authdebug2', function (Request $r) {
    $bearer = $r->bearerToken();
    $m = config('sanctum.personal_access_token_model');
    $pat = $bearer ? $m::findToken($bearer) : null;

    $tokenableOk = false;
    $tokenableConn = null;
    $tokenableClass = null;
    $tokenableKey = null;
    $tokenableUsername = null;

    if ($pat) {
        $u = $pat->tokenable;
        $tokenableOk = (bool) $u;
        if ($u) {
            $tokenableClass = get_class($u);
            $tokenableConn  = $u->getConnectionName();
            $tokenableKey   = $u->getKey();
            $tokenableUsername = $u->username ?? null;
        }
    }

    return response()->json([
        'findToken' => (bool) $pat,
        'token_id' => $pat->id ?? null,
        'tokenable_ok' => $tokenableOk,
        'tokenable_class' => $tokenableClass,
        'tokenable_conn' => $tokenableConn,
        'tokenable_key' => $tokenableKey,
        'tokenable_username' => $tokenableUsername,
        'auth_check' => auth()->check(),
        'auth_user' => auth()->user(),
    ]);
});

Route::get('/__authdebug3', function (Request $request) {
    return response()->json([
        'auth_check' => auth()->check(),
        'user' => $request->user(),
        'guard_default' => config('auth.defaults.guard'),
        'guard_sanctum' => config('auth.guards.sanctum'),
        'sanctum_guard_cfg' => config('sanctum.guard'),
        'bearer' => $request->bearerToken(),
    ]);
});


// Route::get('/me-debug', function (Request $request) {
//     try {
//         $authHeader = $request->header('Authorization', '');
//         if (strpos($authHeader, 'Bearer ') !== 0) {
//             return response()->json(['ok' => false, 'step' => 'header', 'msg' => 'No Bearer token'], 400);
//         }

//         $bearer = substr($authHeader, 7);
//         if (strpos($bearer, '|') === false) {
//             return response()->json(['ok' => false, 'step' => 'format', 'msg' => 'Token must be in id|token format'], 400);
//         }

//         // ✅ pakai model yang kamu set di config/sanctum.php
//         $tokenModel = config('sanctum.personal_access_token_model');
//         $pat = $tokenModel::findToken($bearer);

//         if (!$pat) {
//             return response()->json([
//                 'ok' => false,
//                 'step' => 'findToken',
//                 'msg' => 'Token not found',
//                 'token_model' => $tokenModel,
//             ], 401);
//         }

//         return response()->json([
//             'ok' => true,
//             'token_model' => $tokenModel,
//             'token_id' => $pat->id,
//             'tokenable_type' => $pat->tokenable_type,
//             'tokenable_id' => $pat->tokenable_id,
//             'user' => $pat->tokenable,
//         ]);
//     } catch (\Throwable $e) {
//         return response()->json([
//             'ok' => false,
//             'step' => 'exception',
//             'err' => get_class($e),
//             'msg' => $e->getMessage(),
//             'file' => $e->getFile(),
//             'line' => $e->getLine(),
//         ], 500);
//     }
// });



// Route::get('/log-test', function () {
//     Log::debug('LOG TEST OK');
//     return response()->json(['ok' => true]);
// });

// use App\Http\Controllers\VendorController;

// Route::get('/vendors', [VendorController::class, 'index']);
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::get('/agendas/month', [AgendaController::class, 'getMonthlyAgendas']);






Route::post('/login', [AuthController::class, 'login']);

Route::middleware('bearer.sanctum')->group(function () {


    Route::get('/me', function (Request $request) {
        return response()->json(['ok' => true, 'user' => $request->user()]);
    });


    Route::post('/logout', [AuthController::class, 'logout']);

    // Vendor API (PROTECTED)
    Route::get('/vendors', [MsVendorController::class, 'index']);
    Route::post('/vendors', [MsVendorController::class, 'store']);
    Route::get('/vendors/{id}', [MsVendorController::class, 'show']);
    Route::put('/vendors/{id}', [MsVendorController::class, 'update']);
    Route::delete('/vendors/{id}', [MsVendorController::class, 'destroy']);

});

