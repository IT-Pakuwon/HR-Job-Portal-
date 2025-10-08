<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Validasi reCAPTCHA sebagai custom validation rule
        Validator::extendImplicit('captcha', function ($attribute, $value, $parameters, $validator) {
            return $this->validateRecaptcha($value);
        }, 'Invalid reCAPTCHA. Please verify you are not a robot.');

        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8'); // untuk strftime

        // Load menu dari database dan bagikan ke semua view
        // $menus = DB::table('ms_screen')
        //     ->select('screen_id', 'screen_code', 'screen_name')           
        //     ->orderBy('screen_id', 'ASC')
        //     ->get()
        //     ->groupBy('screen_code');

        // View::share('menus', $menus);
    }
   
    protected function validateRecaptcha($recaptchaResponse)
    {
        $secretKey = config('recaptcha.api_secret_key');
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
        $responseKeys = json_decode($response, true);
        return $responseKeys['success'] ?? false;
    }
}
