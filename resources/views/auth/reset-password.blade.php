<x-authentication-layout>

<div class="w-full max-w-2xl">

    <div class="rounded-4xl bg-white p-10 shadow-[0_20px_80px_rgba(0,0,0,.18)] dark:bg-[#0f0f1a] dark:border dark:border-indigo-900/40">

        {{-- Theme Toggle --}}
        <div class="flex justify-end">

            <button
                x-data="{ dark: document.documentElement.classList.contains('dark') }"
                @click="
                    dark = !dark;

                    if(dark){
                        document.documentElement.classList.add('dark');
                        document.documentElement.style.colorScheme='dark';
                        localStorage.setItem('dark-mode', true);
                    }else{
                        document.documentElement.classList.remove('dark');
                        document.documentElement.style.colorScheme='light';
                        localStorage.setItem('dark-mode', false);
                    }
                "
                class="flex h-11 w-11 items-center justify-center rounded-full border border-indigo-200 bg-white shadow-sm transition hover:scale-105 hover:bg-indigo-50 dark:border-indigo-800/60 dark:bg-indigo-950/60">

                <svg
                    x-show="!dark"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-indigo-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 3v2.25M12 18.75V21M4.22 4.22l1.59 1.59M18.19 18.19l1.59 1.59M3 12h2.25M18.75 12H21M4.22 19.78l1.59-1.59M18.19 5.81l1.59-1.59M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />

                </svg>

                <svg
                    x-show="dark"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-white/70"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21.752 15.002A9.718 9.718 0 0112 22a10 10 0 010-20c.34 0 .678.017 1.01.05A8 8 0 0021.752 15z" />

                </svg>

            </button>

        </div>

        {{-- Header --}}
        <div class="mt-4">

            <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300">
                Security
            </span>

            <h1 class="mt-6 text-5xl font-bold tracking-tight text-gray-900 dark:text-white">
                Reset Password
            </h1>

            <p class="mt-4 text-lg text-gray-500 dark:text-white/40">
                Create a new secure password to regain access to your account.
            </p>

        </div>

        {{-- Validation --}}
        @if ($errors->any())
            <div class="mt-8 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('password.update') }}" class="mt-10 space-y-6">

            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- Email --}}
            <div>

                <label for="email" class="mb-2 block text-sm font-semibold text-gray-600 dark:text-white/55">
                    Email Address
                </label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    autofocus
                    value="{{ old('email', $request->email) }}"
                    placeholder="john.doe@pakuwon.com"
                    class="h-14 w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 text-gray-900 placeholder:text-gray-400 outline-none transition-all focus:border-indigo-400 focus:ring-4 focus:ring-indigo-400/15 dark:border-white/12 dark:bg-white/8 dark:text-white dark:placeholder:text-white/25 dark:focus:border-indigo-400/70 dark:focus:ring-indigo-400/10">

            </div>

            {{-- New Password --}}
            <div x-data="{ show:false }">

                <label for="password" class="mb-2 block text-sm font-semibold text-gray-600 dark:text-white/55">
                    New Password
                </label>

                <div class="relative">

                    <input
                        x-bind:type="show ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="Enter new password"
                        class="h-14 w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 pr-14 text-gray-900 placeholder:text-gray-400 outline-none transition-all focus:border-indigo-400 focus:ring-4 focus:ring-indigo-400/15 dark:border-white/12 dark:bg-white/8 dark:text-white dark:placeholder:text-white/25 dark:focus:border-indigo-400/70 dark:focus:ring-indigo-400/10">

                    <button type="button" @click="show=!show"
                        class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-indigo-500 dark:text-white/30 dark:hover:text-white/70">
                        <svg x-show="!show" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.477 10.484a3 3 0 104.243 4.243" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0112 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639a10.964 10.964 0 01-4.043 5.154M6.228 6.228A10.965 10.965 0 001.935 12.5C3.227 16.838 7.244 19.5 12 19.5a10.96 10.96 0 004.71-1.074" />
                        </svg>
                    </button>

                </div>

            </div>

            {{-- Confirm Password --}}
            <div x-data="{ show:false }">

                <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-gray-600 dark:text-white/55">
                    Confirm Password
                </label>

                <div class="relative">

                    <input
                        x-bind:type="show ? 'text' : 'password'"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm new password"
                        class="h-14 w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 pr-14 text-gray-900 placeholder:text-gray-400 outline-none transition-all focus:border-indigo-400 focus:ring-4 focus:ring-indigo-400/15 dark:border-white/12 dark:bg-white/8 dark:text-white dark:placeholder:text-white/25 dark:focus:border-indigo-400/70 dark:focus:ring-indigo-400/10">

                    <button type="button" @click="show=!show"
                        class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-indigo-500 dark:text-white/30 dark:hover:text-white/70">
                        <svg x-show="!show" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.477 10.484a3 3 0 104.243 4.243" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0112 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639a10.964 10.964 0 01-4.043 5.154M6.228 6.228A10.965 10.965 0 001.935 12.5C3.227 16.838 7.244 19.5 12 19.5a10.96 10.96 0 004.71-1.074" />
                        </svg>
                    </button>

                </div>

            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="h-14 w-full rounded-2xl bg-indigo-700 hover:bg-indigo-800 active:scale-[.98] text-base font-semibold text-white transition-all shadow-lg shadow-indigo-900/30 dark:bg-indigo-600 dark:hover:bg-indigo-500">

                Reset Password

            </button>

        </form>

        {{-- Footer --}}
        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                ← Back to Sign In
            </a>
        </div>

    </div>

</div>

</x-authentication-layout>
