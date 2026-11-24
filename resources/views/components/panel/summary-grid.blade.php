@props(['panel', 'title' => ''])
{{-- Renderer of the Summary panels (OM Summary, GM Summary). The widget list comes from SummaryLayout (config/summary_panels.php
     today); this only lays out empty cards. Data is fetched once on mount from route summary-panel.data and drawn by
     public/assets/js/Dashboard-Panel/summary-panel.js, which maps each widget `type` to a drawing function. --}}
@php
    $cfg     = \App\Services\Dashboard\SummaryPanels\SummaryLayout::panel($panel) ?? [];
    $widgets = \App\Services\Dashboard\SummaryPanels\SummaryLayout::widgets($panel, auth()->user());
    $hex = ['violet' => ['#8B5CF6', '#7C3AED'], 'blue' => ['#3B82F6', '#06B6D4'], 'green' => ['#10B981', '#06B6D4'], 'orange' => ['#F59E0B', '#D97706'], 'red' => ['#EF4444', '#F43F5E'], 'pink' => ['#EC4899', '#C026D3'], 'cyan' => ['#06B6D4', '#3B82F6']];
    // static class names so Tailwind keeps them
    $spans = [3 => 'lg:col-span-3', 4 => 'lg:col-span-4', 5 => 'lg:col-span-5', 6 => 'lg:col-span-6', 7 => 'lg:col-span-7', 8 => 'lg:col-span-8', 12 => 'lg:col-span-12'];
@endphp
<div data-sp-root data-sp-url="{{ route('summary-panel.data', $panel) }}" data-sp-period="{{ \App\Services\Dashboard\SummaryPanels\SummaryLayout::period($panel) }}"
    x-init="(function (el, tries) { (function go() { if (window.SummaryPanel) return SummaryPanel.mount(el); if (tries-- > 0) return setTimeout(go, 100); el.querySelectorAll('[data-sp-widget]').forEach(w => w.textContent = 'Could not load the panel script — reload the page'); })(); })($el, 100)">

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
        <div class="min-w-0">
            <h2 class="flex items-center gap-2 truncate text-base font-extrabold tracking-tight text-slate-800 dark:text-slate-100">
                <span class="relative flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span></span>
                {{ $title ?: ($cfg['title'] ?? '') }}
            </h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $cfg['subtitle'] ?? '' }} · <span data-sp="updated">loading…</span></p>
        </div>
        {{-- Filter bar: one pill — company · period · refresh (same look as the All Reports filter bar) --}}
        <div class="inline-flex max-w-full flex-wrap items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-800">

            {{-- Company: filled by the JS from the response, and only shown to users who have more than one company --}}
            <div data-sp="company-box" hidden class="relative flex items-center gap-1">
                {{-- multi-select: tick one or more companies; the panel reloads when the list closes --}}
                <button type="button" data-sp="company-btn" title="Company"
                    class="flex max-w-64 items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
                    <svg class="h-4 w-4 shrink-0 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    <span data-sp="company-label" class="truncate">All companies</span>
                    <svg class="h-3 w-3 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div data-sp="company-menu" hidden
                    class="absolute left-0 top-full z-30 mt-2 max-h-80 w-80 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl dark:border-slate-600 dark:bg-slate-800"></div>
                <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-600"></span>
            </div>

            {{-- Period --}}
            <div class="flex items-center gap-0.5 text-xs font-semibold">
                <svg class="mx-1.5 h-4 w-4 shrink-0 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $p => $label)
                    <button type="button" data-sp-period="{{ $p }}" class="rounded-lg px-3 py-1.5 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white">{{ $label }}</button>
                @endforeach
            </div>

            <span class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-600"></span>

            {{-- Refresh (the icon spins while the panel loads) --}}
            <button type="button" data-sp="refresh" title="Refresh now"
                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-200 dark:hover:bg-slate-700">
                <svg data-sp="spinner" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- one connected surface: cells are flat, the 1px gap shows the divider colour --}}
    <div class="grid grid-cols-1 gap-px overflow-hidden rounded-b-2xl border-t border-slate-200 bg-slate-200 dark:border-slate-700/60 dark:bg-slate-700/60 lg:grid-cols-12">
        @foreach ($widgets as $w)
            @php $c = $hex[$w['color'] ?? 'violet'] ?? $hex['violet']; $span = $spans[$w['span'] ?? 12] ?? 'lg:col-span-12'; @endphp
            @if (($w['type'] ?? '') === 'insight')
                {{-- insight strip has its own frame --}}
                <div class="{{ $span }} @container bg-white p-3.5 dark:bg-slate-900">
                    <div class="mb-1.5 flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0c-.943.945-1.657 1.657-1.657 3.657h-3.758c0-2-.714-2.712-1.657-3.657z" /></svg>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Summary Insight</span>
                    </div>
                    <div data-sp-widget="{{ $w['id'] }}" data-sp-type="insight" class="text-xs text-slate-400">Loading insights…</div>
                </div>
            @else
                {{-- flat cell: soft colour wash from the top, a gradient icon chip, no card chrome (the container is the card) --}}
                @php
                    $icons = [
                        'split'  => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'kpi'    => 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6',
                        'mix'    =>'M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6zM13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z',
                        'donut'  => 'M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6zM13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z',
                        'column' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
                        'table'  => 'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5',
                    ];
                    $icon = $icons[$w['type'] ?? ''] ?? $icons['column'];
                @endphp
                <section class="{{ $span }} @container group relative flex min-w-0 flex-col bg-white transition-colors dark:bg-slate-900"
                    style="background-image:linear-gradient(180deg,{{ $c[0] }}14 0,transparent 140px)">
                    <header class="flex items-center gap-3 px-5 pb-1 pt-4 group-[.sp-empty]:opacity-50 group-[.sp-empty]:grayscale">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-white shadow-md" style="background:linear-gradient(135deg,{{ $c[0] }},{{ $c[1] }});box-shadow:0 4px 10px -2px {{ $c[0] }}66">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-[10px] font-bold uppercase tracking-widest" style="color:{{ $c[1] }}">{{ $w['subtitle'] ?? '' }}</p>
                            <h3 class="truncate text-sm font-bold text-slate-800 dark:text-white">{{ $w['title'] ?? '' }}</h3>
                        </div>
                    </header>
                    <div data-sp-widget="{{ $w['id'] }}" data-sp-type="{{ $w['type'] }}" data-sp-height="{{ $w['height'] ?? 260 }}" data-sp-color="{{ $w['color'] ?? 'violet' }}"
                        data-sp-from="{{ $c[0] }}" data-sp-to="{{ $c[1] }}" class="flex-1 px-5 pb-4 pt-3 text-xs text-slate-400">Loading…</div>
                </section>
            @endif
        @endforeach
    </div>
    </div>
</div>
