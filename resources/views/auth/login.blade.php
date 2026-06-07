<x-authentication-layout>
<div class="w-full overflow-hidden rounded-2xl sm:rounded-[36px] sm:h-full bg-white dark:bg-[#0f0f1a] shadow-[0_30px_100px_rgba(0,0,0,.18)] dark:shadow-[0_30px_100px_rgba(0,0,0,.55)]">

    <div class="grid lg:grid-cols-[58%_42%] sm:h-full">

        {{-- LEFT HERO --}}
        <div class="relative hidden p-6 lg:block bg-white dark:bg-[#0f0f1a]">

            <div
                class="relative h-full overflow-hidden rounded-[30px] bg-[#2a1208] dark:bg-[#1a0c05]"
                x-data="loginHero()">

                {{-- Background Image (both light and dark mode) --}}
                <img
                    src="{{ asset('images/login/Background 1.png') }}"
                    class="absolute inset-0 h-full w-full object-cover"
                    alt="">

                {{-- Light mode: subtle vignette --}}
                <div class="absolute inset-0 block dark:hidden" style="background:radial-gradient(ellipse at center,rgba(0,0,0,0.08) 0%,rgba(0,0,0,0.40) 100%);"></div>

                {{-- Dark mode: deeper vignette --}}
                <div class="absolute inset-0 hidden dark:block" style="background:radial-gradient(ellipse at center,rgba(0,0,0,0.05) 0%,rgba(0,0,0,0.52) 100%);"></div>

                {{-- Wave canvas (transparent — sits over image) --}}
                <canvas id="orbCanvas" class="absolute inset-0 w-full h-full"></canvas>

                {{-- Top Bar --}}
                <div class="absolute inset-x-0 top-0 z-20 flex items-center justify-between p-8">

                    <img
                        src="{{ asset('images/Logo Pakuwon.png') }}"
                        alt="Logo"
                        class="h-12 w-auto">

                    {{-- Clock --}}
                    <div class="rounded-2xl border border-white/20 bg-black/20 px-5 py-3 backdrop-blur-xl">
                        <div class="flex items-center gap-2 text-white">
                            <span x-text="time" class="text-lg font-semibold tracking-tight"></span>
                            <span class="text-white/40">•</span>
                            <span x-text="date" class="text-md text-white/65"></span>
                        </div>
                    </div>

                </div>

                {{-- Greeting --}}
                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-center px-10" style="padding-bottom:30%;">
                    <p id="greetingText" class="text-2xl font-bold text-white drop-shadow-lg leading-snug min-h-8"></p>
                    <p class="mt-2 text-xs tracking-widest uppercase text-white/50">Sign in to continue</p>
                </div>

                {{-- Bottom: APP SYSTEM + Bubbles --}}
                <div class="absolute bottom-9 inset-x-0 z-20 flex flex-col items-center text-white text-center px-10">

                    <h2 class="text-4xl font-bold tracking-tight drop-shadow-lg">APP SYSTEM</h2>

                    <p class="mt-1 text-xs font-medium tracking-widest uppercase text-white/55">
                        Pakuwon Group
                    </p>

                    <div class="mt-4 flex flex-wrap justify-center gap-2">

                        <span class="rounded-full border border-white/25 bg-black/20 px-3 py-1.5 text-xs font-medium backdrop-blur-xl">
                            Purchase Requisition
                        </span>

                        <span class="rounded-full border border-white/25 bg-black/20 px-3 py-1.5 text-xs font-medium backdrop-blur-xl">
                            Item Request
                        </span>

                        <span class="rounded-full border border-white/25 bg-black/20 px-3 py-1.5 text-xs font-medium backdrop-blur-xl">
                            Digital Approval
                        </span>

                        <span class="rounded-full border border-white/25 bg-black/20 px-3 py-1.5 text-xs font-medium backdrop-blur-xl">
                            IT Support
                        </span>

                        <span class="rounded-full border border-white/25 bg-black/20 px-3 py-1.5 text-xs font-medium backdrop-blur-xl">
                            GA Support
                        </span>

                        <span class="rounded-full border border-white/25 bg-black/20 px-3 py-1.5 text-xs font-medium backdrop-blur-xl">
                            Recruitment
                        </span>

                    </div>

                </div>

            </div>

        </div>

        {{-- RIGHT PANEL --}}
        <div class="relative overflow-y-auto flex flex-col px-6 py-8 sm:px-8 sm:py-8 md:px-12 md:py-10 lg:px-10 lg:py-10 xl:px-20 bg-white dark:bg-[#0f0f1a] border-l border-indigo-100/80 dark:border-white/5 [&::-webkit-scrollbar]:hidden" style="scrollbar-width:none">

            <div class="w-full max-w-2xl mx-auto flex-1 flex flex-col justify-center">

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
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-indigo-200 bg-white dark:border-white/15 dark:bg-white/8 shadow-sm transition hover:scale-105 hover:bg-indigo-50 dark:hover:bg-white/12 backdrop-blur-sm">

                        <svg
                            x-show="!dark"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-indigo-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 3v2.25M12 18.75V21M4.22 4.22l1.59 1.59M18.19 18.19l1.59 1.59M3 12h2.25M18.75 12H21M4.22 19.78l1.59-1.59M18.19 5.81l1.59-1.59M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>

                        </svg>

                        <svg
                            x-show="dark"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-white/70"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21.752 15.002A9.718 9.718 0 0112 22a10 10 0 010-20c.34 0 .678.017 1.01.05A8 8 0 0021.752 15z"/>

                        </svg>

                    </button>

                </div>

                {{-- Mobile Logo --}}
                <div class="mb-6 sm:mb-8 lg:hidden">
                    <img
                        src="{{ asset('images/Logo Pakuwon.png') }}"
                        class="h-14">
                </div>

                {{-- Header --}}
                <div>

                    <h1 class="mt-3 text-3xl sm:text-3xl md:text-4xl lg:text-4xl xl:text-5xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Welcome Back
                    </h1>

                    <p class="mt-3 text-base lg:text-lg text-gray-500 dark:text-white/40">
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
                    x-data="loginForm()"
                    @submit="onSubmit"
                    class="mt-5 sm:mt-6 md:mt-8 lg:mt-8 space-y-4 sm:space-y-5 md:space-y-6">

                    @csrf

                    <div>

                        <label class="mb-2 block text-md font-semibold text-gray-600 dark:text-white/55">
                            Email Address or Username
                        </label>

                        <input
                            type="text"
                            name="login"
                            id="login_input"
                            x-model="loginVal"
                            value="{{ old('login') }}"
                            required
                            autofocus
                            placeholder="john.doe@pakuwon.com"
                            class="h-14 w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 text-gray-900 placeholder:text-gray-400 outline-none transition-all focus:border-indigo-400 focus:ring-4 focus:ring-indigo-400/15 dark:border-white/12 dark:bg-white/8 dark:text-white dark:placeholder:text-white/25 dark:focus:border-indigo-400/70 dark:focus:ring-indigo-400/10">

                    </div>

                    <div x-data="{ show:false }">

                        <label class="mb-2 block text-md font-semibold text-gray-600 dark:text-white/55">
                            Password
                        </label>

                        <div class="relative">

                            <input
                                x-bind:type="show ? 'text' : 'password'"
                                name="password"
                                required
                                placeholder="Enter password"
                                class="h-14 w-full rounded-2xl border border-gray-200 bg-gray-50 px-5 pr-14 text-gray-900 placeholder:text-gray-400 outline-none transition-all focus:border-indigo-400 focus:ring-4 focus:ring-indigo-400/15 dark:border-white/12 dark:bg-white/8 dark:text-white dark:placeholder:text-white/25 dark:focus:border-indigo-400/70 dark:focus:ring-indigo-400/10">

                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-indigo-500 dark:text-white/30 dark:hover:text-white/70">

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

                    </div>

                    <div class="mt-3 flex items-center justify-between gap-2">

                        <label class="flex cursor-pointer items-center gap-2 shrink-0">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                x-model="remember"
                                class="h-4 w-4 cursor-pointer rounded border-gray-300 text-indigo-600 focus:ring-indigo-500/50 dark:border-white/20">
                            <span class="text-sm text-gray-500 dark:text-white/40">Keep me signed in</span>
                        </label>

                        <a
                            href="{{ route('password.request') }}"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors whitespace-nowrap">
                            Forgot password?
                        </a>

                    </div>

                    <button
                        type="submit"
                        class="h-14 w-full rounded-2xl bg-indigo-700 hover:bg-indigo-800 active:scale-[.98] text-base font-bold text-white transition-all shadow-lg shadow-indigo-900/30 dark:bg-indigo-600 dark:hover:bg-indigo-500 dark:shadow-indigo-900/40">

                        Sign In

                    </button>

                </form>

                {{-- Footer Help --}}
                <div class="mt-5 sm:mt-6 md:mt-8">

                    <div class="flex items-center gap-4">

                        <div class="h-px flex-1 bg-gray-200 dark:bg-white/8"></div>

                        <span class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-white/25">
                            Support
                        </span>

                        <div class="h-px flex-1 bg-gray-200 dark:bg-white/8"></div>

                    </div>

                    <div class="mt-6 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-5 dark:border-white/8 dark:bg-white/5">

                        <p class="text-center text-md text-indigo-900/65 dark:text-white/35">
                            For account activation, or access requests,
                            please contact the IT Department.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<script>
(function initSiri() {

    function startTypewriter() {
        const el = document.getElementById('greetingText');
        if (!el) return;

        const h = new Date().getHours();
        const greeting = h >= 5 && h < 12 ? 'Good Morning'
                       : h >= 12 && h < 17  ? 'Good Afternoon'
                       : h >= 17 && h < 21  ? 'Good Evening'
                       :                      'Good Night';

        const msgs = [
            greeting + '! Welcome back.',
            "Don't forget to check your notifications for document status updates.",
            "Check your dashboard — documents may be waiting for your approval.",
            "Stay on top of your workflow. Review any pending requests today.",
            "Have you reviewed your open purchase requisitions lately?",
            "Timely approvals keep your team moving. Check your approval list now.",
            "A quick dashboard check can save your team hours of waiting.",
            "Pending items in your queue? Head to the dashboard after sign in.",
            "New submissions may be waiting for your review in the system.",
            "Your team is counting on you — check for any open documents today.",
        ];
        let idx = 0;

        function typeIn(text, onDone) {
            let i = 0;
            el.textContent = '';
            const iv = setInterval(() => {
                if (i < text.length) {
                    el.textContent = text.substring(0, ++i) + '|';
                } else {
                    clearInterval(iv);
                    let blink = true;
                    const blinkIv = setInterval(() => {
                        blink = !blink;
                        el.textContent = text + (blink ? '|' : ' ');
                    }, 500);
                    setTimeout(() => { clearInterval(blinkIv); onDone(text); }, 30000);
                }
            }, 55);
        }

        function eraseOut(text, onDone) {
            let i = text.length;
            const iv = setInterval(() => {
                el.textContent = i > 0 ? text.substring(0, --i) + '|' : '';
                if (i === 0) { clearInterval(iv); setTimeout(onDone, 350); }
            }, 28);
        }

        function cycle() {
            typeIn(msgs[idx], (typed) => {
                eraseOut(typed, () => {
                    idx = (idx + 1) % msgs.length;
                    cycle();
                });
            });
        }

        setTimeout(cycle, 600);
    }

    function start() {
        const canvas = document.getElementById('orbCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let W = 0, H = 0, t = 0;

        function resize() {
            W = canvas.width  = canvas.offsetWidth;
            H = canvas.height = canvas.offsetHeight;
        }
        const ro = new ResizeObserver(resize);
        ro.observe(canvas);
        resize();

        const NUM   = 7;
        const waves = Array.from({length: NUM}, (_, i) => {
            const sp = i - (NUM - 1) / 2;
            return {
                vertOff:  sp * 11,
                ampScale: Math.exp(-sp * sp * 0.18),
                phase:    sp * 0.44,
                lw:       i === Math.floor(NUM / 2) ? 2.8 : Math.max(0.6, 1.8 - Math.abs(sp) * 0.3),
            };
        });

        function frame() {
            t += 0.012;
            if (!W || !H) { requestAnimationFrame(frame); return; }

            ctx.clearRect(0, 0, W, H);

            const cy      = H * 0.53;
            const baseAmp = Math.min(W, H) * 0.11 * (0.82 + 0.18 * Math.sin(t * 0.45));

            for (const wv of waves) {
                const amp = baseAmp * wv.ampScale;
                const a   = 0.28 + 0.72 * wv.ampScale;

                // Warm white/cream — visible on the dark photo in both modes
                const grd = ctx.createLinearGradient(0, 0, W, 0);
                grd.addColorStop(0,    `rgba(255, 230, 180, ${a * 0.80})`);
                grd.addColorStop(0.42, `rgba(255, 255, 248, ${a})`);
                grd.addColorStop(0.58, `rgba(255, 255, 248, ${a})`);
                grd.addColorStop(1,    `rgba(255, 220, 170, ${a * 0.80})`);

                ctx.beginPath();
                for (let px = 0; px <= W; px += 2) {
                    const xn = px / W;
                    const y  = cy + wv.vertOff + amp * (
                        0.68 * Math.sin(3.0 * Math.PI * xn + t * 1.6 + wv.phase) +
                        0.32 * Math.sin(5.8 * Math.PI * xn - t * 0.9 + wv.phase * 0.65)
                    );
                    px === 0 ? ctx.moveTo(px, y) : ctx.lineTo(px, y);
                }

                ctx.strokeStyle = grd;
                ctx.lineWidth   = wv.lw;
                ctx.shadowBlur  = wv.lw > 2 ? 14 : 5;
                ctx.shadowColor = 'rgba(255, 240, 200, 0.55)';
                ctx.stroke();
            }
            ctx.shadowBlur = 0;

            requestAnimationFrame(frame);
        }

        frame();
        startTypewriter();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
</script>

<script>
function loginForm() {
    const SAVED_KEY = 'app_saved_login';
    const saved     = localStorage.getItem(SAVED_KEY) || '';

    return {
        loginVal: '{{ old('login') }}' || saved,
        remember: !!saved,

        onSubmit() {
            if (this.remember && this.loginVal.trim()) {
                localStorage.setItem(SAVED_KEY, this.loginVal.trim());
            } else {
                localStorage.removeItem(SAVED_KEY);
            }
        },
    };
}
</script>

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
