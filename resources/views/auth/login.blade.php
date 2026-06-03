<x-authentication-layout>
<div class="w-full overflow-hidden rounded-2xl sm:rounded-[36px] lg:h-full bg-white shadow-[0_20px_80px_rgba(0,0,0,.12)] dark:border dark:border-slate-800 dark:bg-slate-900">

    <div class="grid lg:grid-cols-[58%_42%] lg:h-full">

        {{-- LEFT HERO --}}
        <div class="relative hidden p-6 lg:block">

            <div
                class="relative h-full overflow-hidden rounded-[30px]"
                x-data="loginHero()">

                {{-- Background Slider --}}
                <template x-for="(image,index) in images" :key="index">
                    <img
                        :src="image"
                        x-show="current === index"
                        x-transition:enter="transition-opacity duration-1000"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity duration-1000"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute inset-0 h-full w-full object-cover"
                        alt="">
                </template>

                {{-- Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-black/20 to-black/80"></div>

                {{-- Top Bar --}}
                <div class="absolute inset-x-0 top-0 z-20 flex items-center justify-between p-8">

                    <img
                        src="{{ asset('images/Logo Pakuwon.png') }}"
                        alt="Logo"
                        class="h-12 w-auto">


                    {{-- Clock --}}
                    <div
                        class="rounded-2xl border border-white/15 bg-white/10 px-5 py-3 backdrop-blur-xl shadow-lg shadow-black/10">

                        <div class="flex items-center gap-3">

                            <div class="flex items-center gap-2 text-white">

                                <span
                                    x-text="time"
                                    class="text-lg font-semibold tracking-tight">
                                </span>

                                <span class="text-white/40">
                                    •
                                </span>

                                <span
                                    x-text="date"
                                    class="text-md text-white/70">
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- Bottom Content --}}
                <div class="absolute bottom-12 left-12 z-20 max-w-xl text-white">



                    {{-- Modules --}}
                    <div class="mb-6 flex flex-wrap gap-3">

                        <span class="rounded-full border text-md border-white/20 bg-white/10 px-4 py-2 text-xs font-medium backdrop-blur-xl">
                            Purchase Requisition
                        </span>

                        <span class="rounded-full border text-md border-white/20 bg-white/10 px-4 py-2 text-xs font-medium backdrop-blur-xl">
                           Item Request
                        </span>

                        <span class="rounded-full border text-md border-white/20 bg-white/10 px-4 py-2 text-xs font-medium backdrop-blur-xl">
                            Digital Approval
                        </span>

                        <span class="rounded-full border text-md border-white/20 bg-white/10 px-4 py-2 text-xs font-medium backdrop-blur-xl">
                           IT Support
                        </span>

                        <span class="rounded-full border text-md border-white/20 bg-white/10 px-4 py-2 text-xs font-medium backdrop-blur-xl">
                           GA Support
                        </span>

                        <span class="rounded-full border text-md border-white/20 bg-white/10 px-4 py-2 text-xs font-medium backdrop-blur-xl">
                           PRF
                        </span>

                    </div>

                    <h2 class="text-5xl font-bold leading-tight">
                        APP System
                    </h2>

                </div>

            </div>

        </div>

        {{-- RIGHT PANEL --}}
        <div class="relative overflow-y-auto flex flex-col px-6 py-8 sm:px-8 sm:py-12 lg:px-20 lg:py-16">

            <div class="w-full max-w-2xl mx-auto my-auto">

                {{-- Theme Toggle --}}
                <div class="absolute right-5 top-5 sm:right-10 sm:top-10">

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

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 3v2.25M12 18.75V21M4.22 4.22l1.59 1.59M18.19 18.19l1.59 1.59M3 12h2.25M18.75 12H21M4.22 19.78l1.59-1.59M18.19 5.81l1.59-1.59M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>

                        </svg>

                        <svg
                            x-show="dark"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-slate-200"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21.752 15.002A9.718 9.718 0 0112 22a10 10 0 010-20c.34 0 .678.017 1.01.05A8 8 0 0021.752 15z"/>

                        </svg>

                    </button>

                </div>

                {{-- Mobile Logo --}}
                <div class="mb-8 sm:mb-12 lg:hidden">
                    <img
                        src="{{ asset('images/Logo Pakuwon.png') }}"
                        class="h-14">
                </div>

                {{-- Header --}}
                <div>

                    <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-slate-900 dark:text-white">
                        Welcome Back
                    </h1>

                    <p class="mt-3 text-base lg:text-lg text-slate-500 dark:text-slate-400">
                        Sign in to continue accessing APP System.
                    </p>

                </div>

                @if(session('status'))
                    <div class="mt-8 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-md text-green-700 dark:border-green-900 dark:bg-green-900/20 dark:text-green-300">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Login Form --}}
                <form
                    method="POST"
                    action="{{ route('login') }}"
                    class="mt-6 sm:mt-8 lg:mt-10 space-y-5 sm:space-y-6">

                    @csrf

                    <div>

                        <label class="mb-2 block text-md font-semibold text-slate-700 dark:text-slate-300">
                            Email Address
                        </label>

                        <input
                            type="text"
                            name="login"
                            value="{{ old('login') }}"
                            required
                            autofocus
                            placeholder="john.doe@pakuwon.com"
                            class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-5 text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                    </div>

                    <div x-data="{ show:false }">

                        <label class="mb-2 block text-md font-semibold text-slate-700 dark:text-slate-300">
                            Password
                        </label>

                        <div class="relative">

                            <input
                                x-bind:type="show ? 'text' : 'password'"
                                name="password"
                                required
                                placeholder="Enter password"
                                class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-5 pr-14 text-slate-900 outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600 dark:hover:text-slate-300">

                                <!-- Eye -->
                                <svg
                                    x-show="!show"
                                    x-cloak
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-5 w-5">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5
                                        c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431
                                        0 .639C20.577 16.49 16.64 19.5 12 19.5
                                        c-4.638 0-8.573-3.007-9.964-7.178z" />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />

                                </svg>

                                <!-- Eye Off -->
                                <svg
                                    x-show="show"
                                    x-cloak
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-5 w-5">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 3l18 18" />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M10.477 10.484a3 3 0 104.243 4.243" />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9.88 5.09A10.94 10.94 0 0112 4.5
                                        c4.638 0 8.573 3.007 9.963 7.178
                                        .07.207.07.431 0 .639a10.964 10.964 0 01-4.043 5.154M6.228
                                        6.228A10.965 10.965 0 001.935 12.5
                                        C3.227 16.838 7.244 19.5 12 19.5
                                        a10.96 10.96 0 004.71-1.074" />

                                </svg>

                            </button>
                        </div>

                        <div class="mt-3 flex justify-end">

                            <a
                                href="{{ route('password.request') }}"
                                class="text-md font-medium text-indigo-600 hover:text-indigo-700">
                                Forgot password?
                            </a>

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="h-14 w-full rounded-2xl bg-slate-900 text-base font-semibold text-white transition hover:bg-black dark:bg-white dark:text-slate-900">

                        Sign In

                    </button>

                </form>

                {{-- Footer Help --}}
                <div class="mt-6 sm:mt-8 lg:mt-10">

                    <div class="flex items-center gap-4">

                        <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>

                        <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">
                            Support
                        </span>

                        <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>

                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900">

                        <p class="text-center text-md text-slate-600 dark:text-slate-400">
                            For account activation, or access requests,
                            please contact the IT Department.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@if ($errors->any())
<script>
document.addEventListener('DOMContentLoaded', () => {

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: 'Unable to sign in',
        text: 'Invalid username or password.',
        timer: 5000,
        timerProgressBar: true,
        showConfirmButton: false,
        showCloseButton: true,
        background: document.documentElement.classList.contains('dark')
            ? '#111827'
            : '#ffffff',
        color: document.documentElement.classList.contains('dark')
            ? '#f8fafc'
            : '#111827'
    });

});
</script>
@endif

</x-authentication-layout>
