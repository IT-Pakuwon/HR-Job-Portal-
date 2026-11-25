{{--
    Status filter cards for the list pages that filter with jQuery/DataTables (SPPB, SPPJ, SPPK, SPPT).
    Same look as <x-status-card> (SIK etc.) - one place to change the style.
    The click handler on the page toggles the "active" class on the .status-filter anchor; the selected look follows it.

    Usage:
      <x-status-filter-cards prefix="SPPB"
          :counts="['' => $all, 'P' => $onProgress, 'H' => $draft, 'R' => $reject, 'D' => $revise, 'C' => $completed]"
          :all-list="$showAllList ? $allListCount : null" :wo="$showWo ? $woSppbCount : null" />
--}}
{{-- NOTE: purple uses exact hex values on purpose - this project's theme (resources/css/app.css) remaps purple/violet/indigo/blue to sky blue. --}}
@props([
    'prefix' => '',
    'counts' => [],
    'allList' => null,
    'wo' => null,
    'custom' => null, // optional: [[attrs, label, icon, tone, value, active], ...] replaces the SPP cards; attrs e.g. ['data-filter' => 'jobs']
])

@php
    $idle = [
        'all' => 'border-slate-400 bg-slate-200/20 text-slate-600 hover:bg-slate-100 dark:border-slate-500 dark:text-slate-300 dark:hover:bg-slate-700/30',
        'yellow' => 'border-yellow-500 bg-yellow-100/30 text-yellow-600 hover:bg-yellow-100 dark:text-yellow-400 dark:hover:bg-yellow-500/20',
        'blue' => 'border-blue-500 bg-blue-100/30 text-blue-600 hover:bg-blue-100 dark:text-blue-400 dark:hover:bg-blue-500/20',
        'pink' => 'border-pink-500 bg-pink-100/30 text-pink-600 hover:bg-pink-100 dark:text-pink-400 dark:hover:bg-pink-500/20',
        'green' => 'border-green-600 bg-green-200/20 text-green-600 hover:bg-green-100 dark:border-green-500 dark:text-green-400 dark:hover:bg-green-500/20',
        'red' => 'border-red-600 bg-red-200/20 text-red-600 hover:bg-red-100 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-500/20',
        'purple' => 'border-[#a855f7] bg-[#f3e8ff]/40 text-[#9333ea] hover:bg-[#f3e8ff] dark:text-[#c084fc] dark:hover:bg-[#a855f7]/20',
        'sky' => 'border-sky-500 bg-sky-100/30 text-sky-600 hover:bg-sky-100 dark:text-sky-400 dark:hover:bg-sky-500/20',
    ];

    // Selected state = the shared selected look, applied when the parent anchor (.status-filter) has ".active".
    $selected = [
        'all' => 'group-[.active]/sf:border-2! group-[.active]/sf:p-[11px]! group-[.active]/sf:font-bold! group-[.active]/sf:bg-slate-100! group-[.active]/sf:border-slate-600! group-[.active]/sf:text-slate-700! group-[.active]/sf:shadow-[0_4px_14px_-4px_rgba(71,85,105,.55)]! group-[.active]/sf:-translate-y-px dark:group-[.active]/sf:bg-slate-400/20! dark:group-[.active]/sf:shadow-[0_4px_16px_-4px_rgba(148,163,184,.5)]! dark:group-[.active]/sf:border-slate-300! dark:group-[.active]/sf:text-slate-100!',
        'yellow' => 'group-[.active]/sf:border-2! group-[.active]/sf:p-[11px]! group-[.active]/sf:font-bold! group-[.active]/sf:bg-yellow-100! group-[.active]/sf:border-yellow-600! group-[.active]/sf:text-yellow-700! group-[.active]/sf:shadow-[0_4px_14px_-4px_rgba(234,179,8,.55)]! group-[.active]/sf:-translate-y-px dark:group-[.active]/sf:bg-yellow-500/20! dark:group-[.active]/sf:shadow-[0_4px_16px_-4px_rgba(234,179,8,.5)]! dark:group-[.active]/sf:border-yellow-400! dark:group-[.active]/sf:text-yellow-200!',
        'blue' => 'group-[.active]/sf:border-2! group-[.active]/sf:p-[11px]! group-[.active]/sf:font-bold! group-[.active]/sf:bg-blue-100! group-[.active]/sf:border-blue-700! group-[.active]/sf:text-blue-700! group-[.active]/sf:shadow-[0_4px_14px_-4px_rgba(59,130,246,.55)]! group-[.active]/sf:-translate-y-px dark:group-[.active]/sf:bg-blue-500/20! dark:group-[.active]/sf:shadow-[0_4px_16px_-4px_rgba(59,130,246,.5)]! dark:group-[.active]/sf:border-blue-400! dark:group-[.active]/sf:text-blue-200!',
        'pink' => 'group-[.active]/sf:border-2! group-[.active]/sf:p-[11px]! group-[.active]/sf:font-bold! group-[.active]/sf:bg-pink-100! group-[.active]/sf:border-pink-700! group-[.active]/sf:text-pink-700! group-[.active]/sf:shadow-[0_4px_14px_-4px_rgba(236,72,153,.55)]! group-[.active]/sf:-translate-y-px dark:group-[.active]/sf:bg-pink-500/20! dark:group-[.active]/sf:shadow-[0_4px_16px_-4px_rgba(236,72,153,.5)]! dark:group-[.active]/sf:border-pink-400! dark:group-[.active]/sf:text-pink-200!',
        'green' => 'group-[.active]/sf:border-2! group-[.active]/sf:p-[11px]! group-[.active]/sf:font-bold! group-[.active]/sf:bg-green-100! group-[.active]/sf:border-green-700! group-[.active]/sf:text-green-700! group-[.active]/sf:shadow-[0_4px_14px_-4px_rgba(22,163,74,.55)]! group-[.active]/sf:-translate-y-px dark:group-[.active]/sf:bg-green-500/20! dark:group-[.active]/sf:shadow-[0_4px_16px_-4px_rgba(34,197,94,.5)]! dark:group-[.active]/sf:border-green-400! dark:group-[.active]/sf:text-green-200!',
        'red' => 'group-[.active]/sf:border-2! group-[.active]/sf:p-[11px]! group-[.active]/sf:font-bold! group-[.active]/sf:bg-red-100! group-[.active]/sf:border-red-700! group-[.active]/sf:text-red-700! group-[.active]/sf:shadow-[0_4px_14px_-4px_rgba(220,38,38,.55)]! group-[.active]/sf:-translate-y-px dark:group-[.active]/sf:bg-red-500/20! dark:group-[.active]/sf:shadow-[0_4px_16px_-4px_rgba(239,68,68,.5)]! dark:group-[.active]/sf:border-red-400! dark:group-[.active]/sf:text-red-200!',
        'purple' => 'group-[.active]/sf:border-2! group-[.active]/sf:p-[11px]! group-[.active]/sf:font-bold! group-[.active]/sf:bg-[#f3e8ff]! group-[.active]/sf:border-[#7e22ce]! group-[.active]/sf:text-[#7e22ce]! group-[.active]/sf:shadow-[0_4px_14px_-4px_rgba(168,85,247,.55)]! group-[.active]/sf:-translate-y-px dark:group-[.active]/sf:bg-[#a855f7]/20! dark:group-[.active]/sf:shadow-[0_4px_16px_-4px_rgba(168,85,247,.5)]! dark:group-[.active]/sf:border-[#c084fc]! dark:group-[.active]/sf:text-[#e9d5ff]!',
        'sky' => 'group-[.active]/sf:border-2! group-[.active]/sf:p-[11px]! group-[.active]/sf:font-bold! group-[.active]/sf:bg-sky-100! group-[.active]/sf:border-sky-700! group-[.active]/sf:text-sky-700! group-[.active]/sf:shadow-[0_4px_14px_-4px_rgba(14,165,233,.55)]! group-[.active]/sf:-translate-y-px dark:group-[.active]/sf:bg-sky-500/20! dark:group-[.active]/sf:shadow-[0_4px_16px_-4px_rgba(14,165,233,.5)]! dark:group-[.active]/sf:border-sky-400! dark:group-[.active]/sf:text-sky-200!',
    ];

    $cards = [
        ['', 'All', '📦', 'all', null],
        ['P', 'On Progress', '⏳', 'yellow', null],
        ['H', 'Draft', '📝', 'pink', null],
        ['R', 'Reject', '⛔', 'red', null],
        ['D', 'Revise', '✏️', 'blue', null],
        ['C', 'Completed', '✅', 'green', null],
    ];

    $cols = [4 => 'xl:grid-cols-4', 5 => 'xl:grid-cols-5', 6 => 'xl:grid-cols-6', 7 => 'xl:grid-cols-7', 8 => 'xl:grid-cols-8', 9 => 'xl:grid-cols-9', 10 => 'xl:grid-cols-10'];
    $n = is_array($custom) ? count($custom) : count($cards) + (!is_null($allList) ? 1 : 0) + (!is_null($wo) ? 1 : 0);
@endphp

<div {{ $attributes->merge(['class' => ($cols[$n] ?? 'xl:grid-cols-6') . ' grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3']) }}>
    @if (is_array($custom))
        @foreach ($custom as $c)
            @php [$attrs, $label, $icon, $tone, $value, $isActive] = $c; @endphp
            @include('components.status-filter-card', ['attr' => collect($attrs)->map(fn ($v, $k) => $k . '="' . e($v) . '"')->implode(' '), 'label' => $label, 'icon' => $icon, 'tone' => $tone, 'value' => $value, 'idle' => $idle, 'selected' => $selected, 'activeClass' => ($isActive ? ' active' : '') . (isset($c[7]) ? ' ' . $c[7] : ''), 'valueId' => $c[6] ?? null, 'noFilter' => $c[8] ?? false])
        @endforeach
    @else
    @foreach ($cards as [$key, $label, $icon, $tone])
        @include('components.status-filter-card', ['attr' => 'data-status="' . $key . '"', 'label' => $label, 'icon' => $icon, 'tone' => $tone, 'value' => $counts[$key] ?? 0, 'idle' => $idle, 'selected' => $selected])
    @endforeach

    @if (!is_null($allList))
        @include('components.status-filter-card', ['attr' => 'data-mode="all"', 'label' => $prefix . ' All List', 'icon' => '📊', 'tone' => 'purple', 'value' => $allList, 'idle' => $idle, 'selected' => $selected])
    @endif

    @if (!is_null($wo))
        @include('components.status-filter-card', ['attr' => 'data-mode="wo"', 'label' => 'WO → ' . $prefix, 'icon' => '🛠️', 'tone' => 'sky', 'value' => $wo, 'idle' => $idle, 'selected' => $selected])
    @endif
    @endif
</div>
