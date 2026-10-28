@props(['title' => 'Summary', 'subtitle' => '', 'kpis' => [], 'sample' => true])
{{-- Shared frame of the *Summary* panel templates (OM Summary, GM Summary): header with period switch,
     a KPI row, then whatever sections the panel puts in its slot. Unlike <x-panel.shell> this is not a
     list, so it has no url/JSON contract yet — wire its data in when the panel gets its backend.
     $kpis = [['label' => '', 'value' => '', 'hint' => '', 'trend' => 'up|down|flat', 'tone' => 'indigo|emerald|amber|rose|sky|violet']] --}}
@php
    $tones = [
        'indigo'  => ['bar' => 'bg-indigo-500',  'icon' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-300'],
        'emerald' => ['bar' => 'bg-emerald-500', 'icon' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300'],
        'amber'   => ['bar' => 'bg-amber-500',   'icon' => 'bg-amber-500/10 text-amber-600 dark:text-amber-300'],
        'rose'    => ['bar' => 'bg-rose-500',    'icon' => 'bg-rose-500/10 text-rose-600 dark:text-rose-300'],
        'sky'     => ['bar' => 'bg-sky-500',     'icon' => 'bg-sky-500/10 text-sky-600 dark:text-sky-300'],
        'violet'  => ['bar' => 'bg-violet-500',  'icon' => 'bg-violet-500/10 text-violet-600 dark:text-violet-300'],
    ];
@endphp
<div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
    x-data="{ period: 'month' }">

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 dark:border-slate-700">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <h2 class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $title }}</h2>
                @if ($sample)
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Template · sample data</span>
                @endif
            </div>
            @if ($subtitle)
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <div class="flex rounded-lg bg-slate-100 p-0.5 text-xs font-semibold dark:bg-slate-700">
                <template x-for="p in [['today', 'Today'], ['week', 'This Week'], ['month', 'This Month']]" :key="p[0]">
                    <button type="button" @click="period = p[0]"
                        class="rounded-md px-3 py-1 transition"
                        :class="period === p[0] ? 'bg-white text-indigo-600 shadow-sm dark:bg-slate-800 dark:text-indigo-300' : 'text-slate-500 hover:text-slate-700 dark:text-slate-300'"
                        x-text="p[1]"></button>
                </template>
            </div>
            <button type="button" title="Refresh"
                class="flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- KPI row --}}
    @if (count($kpis))
        <div class="grid grid-cols-2 gap-3 border-b border-slate-200 p-4 lg:grid-cols-4 dark:border-slate-700">
            @foreach ($kpis as $k)
                @php $t = $tones[$k['tone'] ?? 'indigo'] ?? $tones['indigo']; $trend = $k['trend'] ?? 'flat'; @endphp
                <div class="relative overflow-hidden rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-700/30">
                    <span class="absolute inset-y-0 left-0 w-1 {{ $t['bar'] }}"></span>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ $k['label'] }}</p>
                    <div class="mt-1 flex items-end justify-between gap-2">
                        <span class="text-2xl font-extrabold tabular-nums text-slate-800 dark:text-slate-100">{{ $k['value'] }}</span>
                        @if ($trend !== 'flat')
                            <span class="mb-1 flex items-center gap-0.5 text-xs font-bold {{ $trend === 'up' ? 'text-emerald-500' : 'text-rose-500' }}">
                                <svg class="h-3 w-3 {{ $trend === 'down' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                {{ $k['delta'] ?? '' }}
                            </span>
                        @endif
                    </div>
                    @if (!empty($k['hint']))
                        <p class="mt-0.5 truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $k['hint'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Panel-specific sections --}}
    <div class="p-4">
        {{ $slot }}
    </div>
</div>
