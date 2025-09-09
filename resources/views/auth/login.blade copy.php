<x-authentication-layout>
    <h2 class="text-4xl text-gray-800 dark:text-gray-100 mb-2 font-bold">{{ __('Welcome back 👋 ') }}</h2>
    <p class="text-m text-gray-800 dark:text-gray-100  mb-6">{{ __('Please sign in to your account!') }}</p>
    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif
    <!-- Form -->
    {{-- <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            </div>
            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" type="password" name="password" required autocomplete="current-password" />
            </div>
        </div>
        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <div class="mr-1">
                    <a class="text-sm underline hover:no-underline" href="{{ route('password.request') }}">
                        {{ __('Forgot Password?') }}
                    </a>
                </div>
            @endif
            <x-button class="ml-3">
                {{ __('Sign in') }}
            </x-button>
        </div>
    </form> --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="space-y-4">
            {{-- <div>
                <x-label class="text-lg text-gray-700 dark:text-white" for="email" value="{{ __('Email') }}" />
                <x-input class="w-90 py-3 px-4 text-lg mb-2"  id="email" type="email" name="email" :value="old('email')" required autofocus />
            </div> --}}
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
                <!-- Toggle Switch for Remember Me -->
                {{-- <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-300 peer-checked:bg-violet-500 rounded-full relative transition duration-300">
                        <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full   transition-all duration-300 peer-checked:left-6"></div>
                    </div>
                    <span class="ml-3 text-m text-gray-700 dark:text-gray-300">Remember Me</span>
                </label> --}}

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
    {{-- <!-- Footer -->
    <div class="pt-5 mt-6 border-t border-gray-100 dark:border-gray-700/60">
        <div class="text-m text-center">
            {{ __('Don\'t you have an account?') }} <a class="font-medium text-violet-500 hover:text-violet-600 dark:hover:text-violet-400" href="{{ route('register') }}">{{ __('Sign Up') }}</a>
        </div> --}}
        {{-- <!-- Warning -->
        <div class="mt-5">
            <div class="bg-yellow-500/20 text-yellow-700 px-3 py-2 rounded-lg">
                <svg class="inline w-3 h-3 shrink-0 fill-current" viewBox="0 0 12 12">
                    <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
                </svg>
                <span class="text-sm">
                    To support you during the pandemic super pro features are free until March 31st.
                </span>
            </div>
        </div> --}}
    </div>
    <!-- Tambahkan script reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

</x-authentication-layout>
