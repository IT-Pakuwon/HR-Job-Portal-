<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use App\Models\User;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        dd($request->all());
        // **Mengubah field login dari "email" ke "login"**
        Fortify::username(fn() => 'login');

        // Menampilkan view login
        Fortify::loginView(fn() => view('auth.login'));

        // **Tambahkan Rate Limiter untuk Login**
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('login') . '|' . $request->ip());
        });

        // **Login menggunakan Email, Username, atau NIP**
        Fortify::authenticateUsing(function (Request $request) {
            // Validasi input
            $credentials = Validator::make($request->all(), [
                'login' => ['required'], // Bisa Email, Username, atau NIP
                'password' => ['required'],
                'g-recaptcha-response' => ['required'], // Validasi reCAPTCHA
            ])->validate();

            // Validasi reCAPTCHA
            if (!$this->validateRecaptcha($request->input('g-recaptcha-response'))) {
                throw ValidationException::withMessages(['captcha' => 'Captcha verification failed.']);
            }

            // Cek apakah user ada berdasarkan Email, Username, atau NIP
            $user = User::where('email', $credentials['login'])
                        ->orWhere('username', $credentials['login'])
                        ->orWhere('nip', $credentials['login'])
                        ->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'login' => ['These credentials do not match our records.'],
                ]);
            }

            // Jika valid, login pengguna
            Auth::login($user);
            return $user;
        });
    }

    // **Fungsi untuk Memvalidasi reCAPTCHA**
    protected function validateRecaptcha($recaptchaResponse)
    {
        $secretKey = config('recaptcha.api_secret_key');
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
        $responseKeys = json_decode($response, true);
        return $responseKeys['success'] ?? false;
    }
}
