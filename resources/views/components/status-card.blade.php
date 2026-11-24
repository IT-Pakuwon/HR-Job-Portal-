{{--
    Status filter card (Alpine). Single source of the card style used by the Tenant Support listings.
    Usage: <x-status-card key="P" label="On Progress" icon="⏳" tone="yellow" />
    Expects the parent x-data to expose: f.status, counts, setStatus(key).
    Tones: all, yellow, blue, violet, green, red, cancel.
--}}
@props([
    'key',
    'label',
    'icon' => '',
    'tone' => 'all',
    'active' => 'f.status',
    'counts' => 'counts',
    'click' => 'setStatus',
])

@php
    $tones = [
        'all' => 'border-slate-400 bg-slate-200/20 text-slate-600 hover:bg-slate-100 dark:border-slate-500 dark:text-slate-300 dark:hover:bg-slate-700/30',
        'yellow' => 'border-yellow-500 bg-yellow-100/30 text-yellow-600 hover:bg-yellow-100 dark:text-yellow-400 dark:hover:bg-yellow-500/20',
        'blue' => 'border-blue-500 bg-blue-100/30 text-blue-600 hover:bg-blue-100 dark:text-blue-400 dark:hover:bg-blue-500/20',
        'violet' => 'border-violet-500 bg-violet-100/30 text-violet-600 hover:bg-violet-100 dark:text-violet-400 dark:hover:bg-violet-500/20',
        'green' => 'border-green-600 bg-green-200/20 text-green-600 hover:bg-green-100 dark:border-green-500 dark:text-green-400 dark:hover:bg-green-500/20',
        'red' => 'border-red-600 bg-red-200/20 text-red-600 hover:bg-red-100 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-500/20',
        'cancel' => 'border-red-600 bg-red-200/20 text-red-600 hover:bg-red-100 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-500/20',
    ];

    // Selected state: same colour family, darker fill + border (light and dark). `!` beats the tone's own bg/border.
    $actives = [
        'all' => 'border-2! p-[11px]! font-bold! bg-slate-100! border-slate-600! text-slate-700! shadow-[0_4px_14px_-4px_rgba(71,85,105,.55)]! -translate-y-px dark:bg-slate-400/20! dark:shadow-[0_4px_16px_-4px_rgba(148,163,184,.5)]! dark:border-slate-300! dark:text-slate-100!',
        'yellow' => 'border-2! p-[11px]! font-bold! bg-yellow-100! border-yellow-600! text-yellow-700! shadow-[0_4px_14px_-4px_rgba(234,179,8,.55)]! -translate-y-px dark:bg-yellow-500/20! dark:shadow-[0_4px_16px_-4px_rgba(234,179,8,.5)]! dark:border-yellow-400! dark:text-yellow-200!',
        'blue' => 'border-2! p-[11px]! font-bold! bg-blue-100! border-blue-700! text-blue-700! shadow-[0_4px_14px_-4px_rgba(59,130,246,.55)]! -translate-y-px dark:bg-blue-500/20! dark:shadow-[0_4px_16px_-4px_rgba(59,130,246,.5)]! dark:border-blue-400! dark:text-blue-200!',
        'violet' => 'border-2! p-[11px]! font-bold! bg-violet-100! border-violet-700! text-violet-700! shadow-[0_4px_14px_-4px_rgba(86, 177, 243,.55)]! -translate-y-px dark:bg-violet-500/20! dark:shadow-[0_4px_16px_-4px_rgba(86, 177, 243,.5)]! dark:border-violet-400! dark:text-violet-200!',
        'green' => 'border-2! p-[11px]! font-bold! bg-green-100! border-green-700! text-green-700! shadow-[0_4px_14px_-4px_rgba(22,163,74,.55)]! -translate-y-px dark:bg-green-500/20! dark:shadow-[0_4px_16px_-4px_rgba(34,197,94,.5)]! dark:border-green-400! dark:text-green-200!',
        'red' => 'border-2! p-[11px]! font-bold! bg-red-100! border-red-700! text-red-700! shadow-[0_4px_14px_-4px_rgba(220,38,38,.55)]! -translate-y-px dark:bg-red-500/20! dark:shadow-[0_4px_16px_-4px_rgba(239,68,68,.5)]! dark:border-red-400! dark:text-red-200!',
        'cancel' => 'border-2! p-[11px]! font-bold! bg-red-100! border-red-700! text-red-700! shadow-[0_4px_14px_-4px_rgba(220,38,38,.55)]! -translate-y-px dark:bg-red-500/20! dark:shadow-[0_4px_16px_-4px_rgba(239,68,68,.5)]! dark:border-red-400! dark:text-red-200!',
    ];
@endphp

<button type="button" @click="{{ $click }}('{{ $key }}')" class="text-left">
    <div class="flex h-full items-center gap-3 rounded-lg border p-3 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md active:scale-95 {{ $tones[$tone] ?? $tones['all'] }}"
        :class="{{ $active }} === '{{ $key }}' ? '{{ $actives[$tone] ?? $actives['all'] }}' : ''">
        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">{{ $icon }}</div>
        <div class="flex min-w-0 grow flex-col leading-tight"><p class="wrap-break-word text-sm font-medium" :class="{{ $active }} === '{{ $key }}' ? 'font-bold!' : ''">{{ $label }}</p></div>
        <p class="shrink-0 text-base font-bold" x-text="{{ $counts }}['{{ $key }}'] ?? 0"></p>
    </div>
</button>
