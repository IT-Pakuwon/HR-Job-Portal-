<x-authentication-layout>
    <h2 class="text-4xl text-gray-800 dark:text-gray-100 mb-2 font-bold">{{ __('Welcome back 👋 ') }}</h2>
    <p class="text-m text-gray-800 dark:text-gray-100  mb-6">{{ __('Please sign in to your account!') }}</p>
    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif
   
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="space-y-4">          
            <div>
                <x-label class="text-lg text-gray-700 dark:text-white" for="login" value="{{ __('Email') }}" />
                <x-input class="w-90 py-3 px-4 text-lg mb-2" id="login" type="text" name="login" :value="old('login')" required autofocus />
            </div>
            
            <div>
                <x-label class="text-lg text-gray-700 dark:text-white" for="password" value="{{ __('Password') }}" />
                <x-input class="w-90 py-3 px-4 text-lg mb-4"  id="password" type="password" name="password" required autocomplete="current-password" />
            </div>
        </div>

        <!-- reCAPTCHA centang -->
        <div class="mt-4">
            <div class="g-recaptcha" data-sitekey="{{ config('recaptcha.api_site_key') }}"></div>
            @error('captcha')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="pt-5 border-gray-100 dark:border-gray-700/60">
            <div class="flex items-center justify-end">

                <!-- Forgot Password Link -->
                <div class="text-m text-right">
                    <a class="font-medium text-violet-500 hover:text-violet-600 dark:hover:text-violet-400" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                </div>
            </div>
        </div>

        <div class="w-110 flex items-center justify-between mt-6">
            <button type="submit" class="w-100 bg-blue-500 text-white px-4 py-2 rounded">Login</button>
        </div>
    </form>


    <x-validation-errors class="mt-4" />

    </div>
    <!-- Tambahkan script reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

</x-authentication-layout>
