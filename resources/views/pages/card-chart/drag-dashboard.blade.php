<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Editor — {{ config('app.name', 'Pakuwon System') }}</title>

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/Logo Pakuwon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        const _dm = localStorage.getItem('dark-mode');
        if (_dm === 'true') {
            document.documentElement.classList.add('dark');
            document.documentElement.style.colorScheme = 'dark';
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.style.colorScheme = 'light';
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Subtle grid background on canvas */
        #dbd-canvas {
            background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .dark #dbd-canvas {
            background-image: radial-gradient(circle, #1e293b 1px, transparent 1px);
        }
    </style>
</head>

<body class="antialiased bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300 overflow-hidden" style="height:100dvh">

{{-- ── Top Bar ──────────────────────────────────────────────────────── --}}
<header class="flex items-center justify-between gap-4 px-4
               bg-white dark:bg-slate-900
               border-b border-slate-200 dark:border-slate-800
               shadow-sm"
        style="height:56px">

    {{-- Left: back + brand + divider + name --}}
    <div class="flex items-center gap-3 min-w-0">

        {{-- Back to Catalog --}}
        <a href="{{ route('card-chart.catalog') }}"
           class="shrink-0 flex items-center gap-1.5 rounded-lg px-2.5 py-1.5
                  text-xs font-semibold text-slate-500
                  hover:bg-slate-100 hover:text-slate-700
                  dark:hover:bg-slate-800 dark:hover:text-slate-200
                  transition">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Catalog
        </a>

        <span class="h-5 w-px bg-slate-200 dark:bg-slate-700 shrink-0"></span>

        {{-- Brand icon + label --}}
        <div class="flex items-center gap-2 shrink-0">
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-600 shadow-sm shadow-violet-200 dark:shadow-violet-900/40">
                <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                </svg>
            </div>
            <span class="hidden sm:block text-[11px] font-extrabold tracking-widest uppercase text-slate-400 dark:text-slate-500">
                Dashboard Editor
            </span>
        </div>

        <span class="h-5 w-px bg-slate-200 dark:bg-slate-700 shrink-0"></span>

        {{-- Editable title --}}
        <div class="group flex items-center gap-1.5 min-w-0">
            <input id="dbd-name" type="text" value="My Dashboard"
                   class="min-w-0 w-44 rounded-lg bg-transparent px-2 py-1
                          text-sm font-bold text-slate-800 dark:text-white outline-none
                          hover:bg-slate-100 dark:hover:bg-slate-800/60
                          focus:bg-slate-100 dark:focus:bg-slate-800/60
                          focus:ring-2 focus:ring-violet-300 dark:focus:ring-violet-700/50
                          transition">
            <svg class="h-3 w-3 shrink-0 text-slate-300 group-hover:text-violet-400 dark:text-slate-600 dark:group-hover:text-violet-500 transition"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
        </div>
    </div>

    {{-- Center: section pill --}}
    <span id="dbd-count"
          class="hidden md:inline-flex items-center gap-1.5 rounded-full
                 bg-slate-100 dark:bg-slate-800
                 px-3 py-1 text-[11px] font-bold text-slate-500 dark:text-slate-400">
        <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
        0 sections
    </span>

    {{-- Right: dark mode toggle + reset + save --}}
    <div class="flex items-center gap-1.5 shrink-0">

        {{-- Dark mode toggle --}}
        <button id="dbd-toggle-dark" title="Toggle dark mode"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400
                       hover:bg-slate-100 hover:text-slate-600
                       dark:hover:bg-slate-800 dark:hover:text-slate-200
                       transition active:scale-95">
            {{-- Sun --}}
            <svg class="h-4 w-4 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m15.07-6.07-.71.71M6.34 17.66l-.71.71M17.66 17.66l-.71-.71M6.34 6.34l-.71-.71M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
            </svg>
            {{-- Moon --}}
            <svg class="h-4 w-4 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
        </button>

        <span class="h-5 w-px bg-slate-200 dark:bg-slate-700"></span>

        {{-- Reset --}}
        <button id="dbd-btn-clear" title="Reset dashboard"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400
                       hover:bg-red-50 hover:text-red-500
                       dark:hover:bg-red-900/20 dark:hover:text-red-400
                       active:scale-95 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>

        {{-- Save --}}
        <button id="dbd-btn-save"
                class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2
                       text-xs font-bold text-white
                       bg-linear-to-r from-violet-600 to-violet-500
                       shadow-sm shadow-violet-300 dark:shadow-violet-900/30
                       hover:from-violet-700 hover:to-violet-600 active:scale-95 transition">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Save
        </button>
    </div>
</header>

{{-- ── Canvas ───────────────────────────────────────────────────────── --}}
<div style="height:calc(100dvh - 56px); overflow-y:auto;" id="dbd-canvas" class="p-6 bg-slate-50 dark:bg-slate-950">

    {{-- Empty state / Template picker --}}
    <div id="dbd-empty" class="flex items-center justify-center" style="min-height:calc(100dvh - 120px)">
        <div class="w-full max-w-2xl">
            <div class="mb-10 text-center">
                <div class="mb-4 mx-auto flex h-16 w-16 items-center justify-center rounded-2xl
                            bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                    <svg class="h-8 w-8 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                    </svg>
                </div>
                <p class="text-lg font-bold text-slate-700 dark:text-slate-200">Start with a template</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">Choose a pre-built layout or start from scratch</p>
            </div>
            <div id="dbd-template-grid" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                {{-- Filled by JS --}}
            </div>
        </div>
    </div>

    {{-- Sections container --}}
    <div id="dbd-sections" class="space-y-5"></div>

    {{-- Add section footer button (shown after first section) --}}
    <div id="dbd-add-row" class="hidden pt-4 pb-2 justify-center">
        <button id="dbd-btn-add-section"
                class="inline-flex items-center gap-2 rounded-2xl border-2 border-dashed border-slate-300
                       bg-white/80 backdrop-blur-sm px-8 py-3 text-sm font-semibold text-slate-400
                       hover:border-violet-400 hover:text-violet-500 hover:bg-violet-50
                       dark:border-slate-700 dark:bg-slate-900/60 dark:hover:border-violet-500
                       dark:hover:text-violet-400 dark:hover:bg-violet-900/20
                       shadow-sm transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Section
        </button>
    </div>

</div>

{{-- ── Layout Picker Modal ─────────────────────────────────────── --}}
<div id="dbd-modal-layout" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="dbd-layout-backdrop"></div>
    <div class="relative z-10 w-full max-w-xl rounded-2xl bg-white dark:bg-slate-900
                shadow-2xl shadow-black/20 overflow-hidden border border-slate-200/50 dark:border-slate-700/50">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Choose Section Layout</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Pick a column arrangement for this section</p>
            </div>
            <button id="dbd-layout-close"
                    class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400
                           hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 transition text-sm">✕</button>
        </div>
        <div id="dbd-layout-options" class="grid grid-cols-3 gap-3 p-6">
            {{-- Filled by JS --}}
        </div>
    </div>
</div>

{{-- ── Chart Picker Modal ──────────────────────────────────────── --}}
<div id="dbd-modal-chart" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="dbd-chart-backdrop"></div>
    <div class="relative z-10 w-full max-w-2xl flex flex-col rounded-2xl bg-white dark:bg-slate-900
                shadow-2xl shadow-black/20 overflow-hidden border border-slate-200/50 dark:border-slate-700/50"
         style="max-height:85vh">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0">
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Choose a Chart</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Select the chart type to place in this slot</p>
            </div>
            <button id="dbd-chart-close"
                    class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400
                           hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 transition text-sm">✕</button>
        </div>
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 shrink-0">
            <input id="dbd-chart-search" type="text" placeholder="Search charts..."
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2
                          text-sm text-slate-700 outline-none
                          focus:border-violet-400 focus:ring-2 focus:ring-violet-100
                          dark:border-slate-700 dark:bg-slate-800 dark:text-white
                          dark:placeholder-slate-500 dark:focus:ring-violet-900/30 transition">
        </div>
        <div id="dbd-chart-catalog" class="flex-1 overflow-y-auto p-5">
            {{-- Filled by JS --}}
        </div>
    </div>
</div>

<script>
    /* Dark mode toggle inside the editor */
    document.getElementById('dbd-toggle-dark').addEventListener('click', function() {
        var html = document.documentElement;
        var isDark = html.classList.toggle('dark');
        html.style.colorScheme = isDark ? 'dark' : 'light';
        localStorage.setItem('dark-mode', isDark ? 'true' : 'false');
    });
</script>

<script src="{{ asset('assets/js/card-chart/drag-dashboard.js') }}"></script>

</body>
</html>
