<x-authentication-layout>
    <div
        class="card w-xl mx-auto transform rounded-xl bg-white p-8 shadow-xl transition-all duration-300 hover:shadow-2xl dark:bg-gray-800">
        <div class="flex flex-row items-center justify-center gap-6">
            <img src="{{ asset('logo/logo.png') }}" alt="App Logo" class="mb-3 h-8 w-auto md:h-12">

            <h2 class="text-center text-3xl font-extrabold text-gray-900 md:text-4xl dark:text-gray-100">
                {{ __('APP SYSTEM') }}
            </h2>
        </div>

        <p class="mb-8 text-center text-base text-gray-600 dark:text-gray-300">
            {{ __('Welcome back! Please sign in to continue.') }}

        </p>

        @if (session('status'))
            <div
                class="mb-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-600 dark:bg-green-900/20 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="space-y-5">
                <div>
                    <x-label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="login"
                        value="{{ __('Email') }} or {{ __('Username') }}" />
                    <x-input
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 shadow-sm transition-colors duration-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                        id="login" type="text" name="login" :value="old('login')" required autofocus />
                </div>

                <div>
                    <x-label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="password"
                        value="{{ __('Password') }}" />
                    <x-input
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 shadow-sm transition-colors duration-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                        id="password" type="password" name="password" required autocomplete="current-password" />
                </div>
            </div>
            <div class="mt-6">
                <div class="flex items-center justify-end">
                    <a class="text-sm font-medium text-indigo-600 transition-colors duration-200 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                        href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                </div>
            </div>

            <div class="mt-8">
                <button type="submit"
                    class="hover: w-full transform rounded-lg bg-indigo-600 px-4 py-3 text-lg font-semibold text-white shadow-md transition-all duration-300 ease-in-out hover:scale-105 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
                    Login
                </button>
            </div>
        </form>

        <x-validation-errors class="mt-6" />

    </div>

</x-authentication-layout>
