@props(['dashCd', 'off' => false])
@php
    $panel = \App\Models\DashboardMenu::panelKey($dashCd);
    // When this dashboard has a tile for its own panel, the card stays hidden until that tile
    // (or a KPI card, via the `dash-open-panel` event) opens it. Otherwise it renders as before.
    $tile = ($off || !$panel) ? null : \App\Models\DashboardMenu::forDashboard($dashCd, auth()->user())->firstWhere('panel', $panel);
    $held = $tile !== null;
@endphp
@if ($held)
    {{-- Shown while the panel is closed, so the page doesn't end in empty space. --}}
    <section x-show="panel !== '{{ $panel }}'" x-cloak style="display: none;"
        class="flex flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-gray-300 px-6 py-10 text-center dark:border-gray-600">
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-900/40">
            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tile['icon'] }}" />
            </svg>
        </span>
        <h3 class="text-[15px] font-bold text-gray-800 dark:text-gray-100">Nothing open yet</h3>
        <p class="max-w-md text-[13px] leading-relaxed text-gray-500 dark:text-gray-400">
            Pick the <strong class="text-gray-700 dark:text-gray-200">{{ $tile['name'] }}</strong> tile, or tap a KPI card.
            The panel opens right here and jumps to the matching tab.
        </p>
    </section>
@endif
<div x-ref="dashPanel" @if ($held) data-panel-held x-show="panel === '{{ $panel }}'" x-cloak style="display: none;" @if (!empty($tile['default'])) x-init="panel = '{{ $panel }}'" @endif @endif>
    @if ($held)
        <div class="mb-2 flex items-center justify-between gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex min-w-0 items-center gap-2">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-900/40">
                    <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tile['icon'] }}" />
                    </svg>
                </span>
                <h2 class="truncate text-sm font-bold text-gray-800 dark:text-gray-100">{{ $tile['name'] }}</h2>
            </div>
            <button type="button" @click="panel = null"
                class="flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                Close panel
            </button>
        </div>
    @endif
    {{ $slot }}
</div>
