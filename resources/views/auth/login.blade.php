<x-authentication-layout>

    <div
        class="min-h-[95vh] w-full overflow-hidden rounded-[32px] bg-white shadow-2xl dark:border dark:border-slate-800 dark:bg-slate-900">

        <div class="grid min-h-[95vh] lg:grid-cols-[1.25fr_0.9fr]">

            {{-- LEFT PANEL --}}
            <div class="relative hidden p-6 lg:block">

                <div class="relative h-full overflow-hidden rounded-[28px]" x-data="{
                    current: 0,
                    images: [
                        '{{ asset('images/login/Background 1.png') }}',
                        '{{ asset('images/login/Background 2.png') }}',
                        '{{ asset('images/login/Background 3.png') }}',
                        '{{ asset('images/login/Background 4.png') }}'
                    ],
                    init() {
                        setInterval(() => {
                            this.current = (this.current + 1) % this.images.length;
                        }, 5000);
                    }
                }">

                    <template x-for="(image,index) in images" :key="index">
                        <img :src="image" x-show="current === index"
                            x-transition:enter="transition-opacity duration-1000" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-1000"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="absolute inset-0 h-full w-full object-cover" alt="">
                    </template>

                    <div class="absolute inset-0 bg-gradient-to-br from-slate-900/30 via-slate-900/10 to-slate-900/80">
                    </div>

                    {{-- Your Logo --}}
                    <div class="absolute left-8 top-8 z-20">
                        <img src="{{ asset('images/Logo Pakuwon.png') }}" alt="Logo" class="h-12">
                    </div>

                    {{-- Your content --}}
                    <div class="absolute bottom-12 left-12 z-20 max-w-xl text-white">

                        <div class="mb-6 flex flex-wrap gap-3">
                            <span class="rounded-full bg-white/15 px-4 py-2 text-sm backdrop-blur">
                                Approval
                            </span>

                            <span class="rounded-full bg-white/15 px-4 py-2 text-sm backdrop-blur">
                                Ticketing
                            </span>

                            <span class="rounded-full bg-white/15 px-4 py-2 text-sm backdrop-blur">
                                Workflow
                            </span>
                        </div>

                        <h2 class="text-5xl font-bold">
                            APP System
                        </h2>

                        <p class="mt-4 text-lg text-white/80">
                            Centralized enterprise workflow platform.
                        </p>

                    </div>

                </div>

            </div>

            {{-- RIGHT PANEL --}}
            <div class="flex items-center justify-center px-8 py-12 lg:px-16 xl:px-20">

                <div class="w-full max-w-xl">

                    {{-- Mobile Logo --}}
                    <div class="mb-10 flex items-center gap-3 lg:hidden">

                        <img src="{{ asset('images/Logo Pakuwon.png') }}" alt="Logo" class="h-12">

                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white">
                                APP System
                            </div>

                            <div class="text-sm text-slate-500">
                                Enterprise Platform
                            </div>
                        </div>

                    </div>

                    <h1 class="text-5xl font-semibold tracking-tight text-slate-900 xl:text-6xl dark:text-white">
                        Welcome Back
                    </h1>

                    <p class="mt-4 text-lg text-slate-500 dark:text-slate-400">
                        Sign in to continue your work and access the APP System dashboard.
                    </p>

                    @if (session('status'))
                        <div
                            class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-10 space-y-6">

                        @csrf

                        {{-- Username --}}
                        <div>

                            <label for="login"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Email or Username
                            </label>

                            <input id="login" name="login" type="text" required autofocus
                                value="{{ old('login') }}" placeholder="Enter your username"
                                class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 text-base text-slate-900 outline-none transition-all duration-200 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                        </div>

                        {{-- Password --}}
                        <div x-data="{ show: false }">

                            <label for="password"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Password
                            </label>

                            <div class="relative">

                                <input :type="show ? 'text' : 'password'" id="password" name="password" required
                                    placeholder="Enter your password"
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 pr-14 text-base text-slate-900 outline-none transition-all duration-200 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                                <button type="button" @click="show=!show"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">

                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.5a10.522 10.522 0 01-4.293 5.774" />
                                    </svg>

                                </button>

                            </div>

                            <div class="mt-3 flex justify-end">

                                <a href="{{ route('password.request') }}"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">

                                    Forgot password?

                                </a>

                            </div>

                        </div>

                        {{-- Remember Me --}}
                        <label class="flex items-center gap-3">

                            <input type="checkbox" name="remember"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">

                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                Keep me signed in
                            </span>

                        </label>

                        {{-- Submit --}}
                        <button type="submit"
                            class="h-14 w-full rounded-2xl bg-indigo-600 text-base font-semibold text-white transition-all duration-200 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/25">

                            Sign In

                        </button>

                    </form>

                    <x-validation-errors class="mt-6" />

                </div>

            </div>

        </div>

    </div>

</x-authentication-layout>
