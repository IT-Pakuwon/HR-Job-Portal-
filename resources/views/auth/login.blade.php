<x-authentication-layout>
    <div class="mx-auto w-full max-w-md rounded-2xl bg-white p-8 shadow-xl dark:bg-gray-800">
        <!-- Header -->
        <div class="mb-8 text-center">
            <img src="{{ asset('logo/logo.png') }}" alt="App Logo" class="mx-auto mb-4 h-10 w-auto">

            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">
                {{ __('APP SYSTEM') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                {{ __('Welcome back! Please sign in to continue.') }}
            </p>
        </div>

        @if (session('status'))
            <div
                class="mb-6 rounded-lg bg-green-50 px-3 py-2 text-xs text-green-700 dark:bg-green-900/20 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Login -->
            <div>
                <x-label for="login" class="text-xs font-medium text-gray-700 dark:text-gray-200"
                    value="{{ __('Email or Username') }}" />
                <x-input id="login" name="login" type="text" required autofocus :value="old('login')"
                    class="mt-2 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
            </div>

            <!-- Password -->
            <div>
                <x-label for="password" class="text-xs font-medium text-gray-700 dark:text-gray-200"
                    value="{{ __('Password') }}" />

                <div x-data="{ show: false }" class="relative mt-2">
                    <x-input x-bind:type="show ? 'text' : 'password'" id="password" name="password" required
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 pr-10 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />

                    <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200"
                        tabindex="-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                     c4.478 0 8.268 2.943 9.542 7
                                     -1.274 4.057-5.064 7-9.542 7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Remember + Forgot -->
            <div class="justify-right flex items-center text-xs">
                {{-- <label class="flex cursor-pointer items-center gap-2 text-gray-600 dark:text-gray-300">
                    <input type="checkbox" name="remember"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                    {{ __('Remember me') }}
                </label> --}}

                <a href="{{ route('password.request') }}"
                    class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                    {{ __('Forgot password?') }}
                </a>
            </div>

            <!-- Submit -->
            <button type="submit"
                class="mt-4 w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-semibold text-white shadow-md transition-all hover:bg-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('Sign in') }}
            </button>
        </form>

        <x-validation-errors class="mt-6" />
    </div>
</x-authentication-layout>
