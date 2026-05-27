<x-authentication-layout>

    <div class="w-full max-w-2xl">

        <div
            class="rounded-[32px] bg-white p-10 shadow-[0_20px_80px_rgba(0,0,0,.12)] dark:border dark:border-slate-800 dark:bg-slate-900">

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

                        <path
                            stroke-linecap="round"
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

                        <path
                            stroke-linecap="round"
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

                    Account Recovery

                </span>

                <h1 class="mt-6 text-5xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Forgot Password?
                </h1>

                <p class="mt-4 text-lg text-slate-500 dark:text-slate-400">
                    Enter your email address and we'll send you a password reset link.
                </p>

            </div>

            {{-- Success Message --}}
            @if (session('status'))
                <div
                    class="mt-8 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900 dark:bg-green-900/20 dark:text-green-300">

                    {{ session('status') }}

                </div>
            @endif

            {{-- Validation Error --}}
            @if ($errors->any())
                <div
                    class="mt-8 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300">

                    {{ $errors->first() }}

                </div>
            @endif

            {{-- Form --}}
            <form
                method="POST"
                action="{{ route('password.email') }}"
                class="mt-10 space-y-6">

                @csrf

                <div>

                    <label
                        for="email"
                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">

                        Email Address

                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="john.doe@pakuwon.com"
                        class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-5 text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                </div>

                <button
                    type="submit"
                    class="h-14 w-full rounded-2xl bg-slate-900 text-base font-semibold text-white transition hover:bg-black dark:bg-white dark:text-slate-900">

                    Send Reset Link

                </button>

            </form>

            {{-- Divider --}}
            <div class="mt-10 flex items-center gap-4">

                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>

                <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                    Support
                </span>

                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>

            </div>

            {{-- Support Card --}}
            <div
                class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900">

                <p class="text-center text-sm text-slate-600 dark:text-slate-400">
                    If you have any issues with account activation, or access requests,
                    please contact the IT Department for account recovery assistance.
                </p>

            </div>

            {{-- Back --}}
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
