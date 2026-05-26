<x-authentication-layout>

<div class="w-full max-w-2xl">

    <div class="rounded-[32px] bg-white p-10 shadow-[0_20px_80px_rgba(0,0,0,.12)] dark:border dark:border-slate-800 dark:bg-slate-900">

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
                class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm transition hover:scale-105 dark:border-slate-700 dark:bg-slate-800">

                <svg
                    x-show="!dark"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-slate-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M12 3v2.25M12 18.75V21M4.22 4.22l1.59 1.59M18.19 18.19l1.59 1.59M3 12h2.25M18.75 12H21M4.22 19.78l1.59-1.59M18.19 5.81l1.59-1.59M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />

                </svg>

                <svg
                    x-show="dark"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-slate-200"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M21.752 15.002A9.718 9.718 0 0112 22a10 10 0 010-20c.34 0 .678.017 1.01.05A8 8 0 0021.752 15z" />

                </svg>

            </button>

        </div>

        {{-- Header --}}
        <div class="mt-4">

            <span
                class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-slate-600 dark:bg-slate-800 dark:text-slate-300">

                Security

            </span>

            <h1 class="mt-6 text-5xl font-bold tracking-tight text-slate-900 dark:text-white">
                Reset Password
            </h1>

            <p class="mt-4 text-lg text-slate-500 dark:text-slate-400">
                Create a new secure password to regain access to your account.
            </p>

        </div>

        {{-- Validation --}}
        @if ($errors->any())
            <div
                class="mt-8 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300">

                {{ $errors->first() }}

            </div>
        @endif

        {{-- Form --}}
        <form
            method="POST"
            action="{{ route('password.update') }}"
            class="mt-10 space-y-6">

            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ $request->route('token') }}">

            {{-- Email --}}
            <div>

                <label
                    for="email"
                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">

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
                    class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-5 text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

            </div>

            {{-- Password --}}
            <div x-data="{ show:false }">

                <label
                    for="password"
                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">

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
                        class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-5 pr-14 text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                    <button
                        type="button"
                        @click="show=!show"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">

                        👁

                    </button>

                </div>

            </div>

            {{-- Confirm Password --}}
            <div x-data="{ show:false }">

                <label
                    for="password_confirmation"
                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">

                    Confirm Password

                </label>

                <div class="relative">

                    <input
                        x-bind:type="show ? 'text' : 'password'"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-5 pr-14 text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                    <button
                        type="button"
                        @click="show=!show"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">

                        👁

                    </button>

                </div>

            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="h-14 w-full rounded-2xl bg-slate-900 text-base font-semibold text-white transition hover:bg-black dark:bg-white dark:text-slate-900">

                Reset Password

            </button>

        </form>

        {{-- Footer --}}
        <div class="mt-8 text-center">

            <a
                href="{{ route('login') }}"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-700">

                ← Back to Sign In

            </a>

        </div>

    </div>

</div>

</x-authentication-layout>
