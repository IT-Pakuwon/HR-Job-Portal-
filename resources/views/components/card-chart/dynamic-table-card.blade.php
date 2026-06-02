@props([
    'title'            => '',
    'subtitle'         => '',
    'color'            => 'blue',
    'gradient'         => null,
    'columns'          => [],
    'tableBodyId'      => '',
    'countBadgeId'     => '',
    'paginationPrefix' => '',
])

@php
    $hex = [
        'violet' => ['#8B5CF6', '#7C3AED'],
        'blue'   => ['#3B82F6', '#06B6D4'],
        'green'  => ['#10B981', '#06B6D4'],
        'orange' => ['#F59E0B', '#EF4444'],
        'red'    => ['#EF4444', '#F43F5E'],
        'pink'   => ['#EC4899', '#C026D3'],
        'cyan'   => ['#06B6D4', '#3B82F6'],
    ];
    $c    = $hex[$color] ?? $hex['blue'];
    $grad = $gradient ?? 'linear-gradient(to right,' . $c[0] . ',' . $c[1] . ')';
    $cols = is_string($columns) ? json_decode($columns, true) : $columns;
    $pp   = $paginationPrefix;
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>

    {{-- Gradient accent top bar --}}
    <div class="absolute inset-x-0 top-0 h-0.75" style="background: {{ $grad }}"></div>

    {{-- Card Header --}}
    <div class="flex items-center justify-between px-5 pb-3 pt-5">
        <div>
            @if($subtitle)
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $subtitle }}</p>
            @endif
            <h3 class="mt-0.5 text-sm font-bold text-slate-800 dark:text-white">{{ $title }}</h3>
        </div>
        @if($countBadgeId)
            <span id="{{ $countBadgeId }}"
                  class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400"></span>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table @if($tableBodyId) id="{{ $tableBodyId }}-tbl" @endif class="w-full min-w-[520px] text-xs">
            @if(count($cols))
                <thead>
                    <tr class="border-t border-slate-100 dark:border-slate-700/60">
                        @foreach($cols as $i => $col)
                            @php
                                $label      = is_array($col) ? $col['label'] : $col;
                                $sortKey    = is_array($col) && isset($col['key'])     ? $col['key']     : null;
                                $sortNum    = is_array($col) && !empty($col['numeric']) ? 'true'          : 'false';
                                $isFirst    = $i === 0;
                                $isLast     = $i === count($cols) - 1;
                                $align      = is_array($col) && isset($col['align'])
                                    ? $col['align']
                                    : ($isFirst ? 'left' : ($isLast ? 'center' : 'right'));
                                $px         = $isFirst ? 'px-5' : 'px-4';
                            @endphp
                            <th class="whitespace-nowrap bg-slate-50 {{ $px }} py-2.5 text-{{ $align }} text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500 {{ $sortKey ? 'select-none cursor-pointer hover:text-slate-600 dark:hover:text-slate-300 transition-colors' : '' }}"
                                @if($sortKey) data-sort-key="{{ $sortKey }}" data-sort-numeric="{{ $sortNum }}" @endif>
                                {{ $label }}
                                @if($sortKey)
                                    <span class="sort-icon ml-0.5 opacity-30">↕</span>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody @if($tableBodyId) id="{{ $tableBodyId }}" @endif
                   class="divide-y divide-slate-100 dark:divide-slate-700/60">
                <tr>
                    <td colspan="{{ max(count($cols), 1) }}"
                        class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">Loading…</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pp)
        <div id="{{ $pp }}Pagination"
             class="hidden flex items-center justify-between border-t border-slate-100 px-5 py-3 dark:border-slate-700/60">
            <span id="{{ $pp }}PageInfo" class="text-xs text-slate-500 dark:text-slate-400"></span>
            <div class="flex items-center gap-1">
                <button id="{{ $pp }}Prev" type="button"
                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    ‹
                </button>
                <div id="{{ $pp }}PageNums" class="flex items-center gap-1"></div>
                <button id="{{ $pp }}Next" type="button"
                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    ›
                </button>
            </div>
        </div>
    @endif

</div>
