@props([
    'title'             => 'Table',
    'subtitle'          => '',
    'color'             => 'blue',
    'columns'           => [],
    'rows'              => [],
    'searchable'        => false,
    'sortable'          => false,
    'searchPlaceholder' => 'Search...',
    'maxHeight'         => '380px',
])

@php
    $hex = ['violet'=>['#8B5CF6','#7C3AED'],'blue'=>['#3B82F6','#06B6D4'],'green'=>['#10B981','#0D9488'],'orange'=>['#F59E0B','#D97706'],'red'=>['#EF4444','#F43F5E'],'pink'=>['#EC4899','#C026D3'],'cyan'=>['#06B6D4','#3B82F6']];
    $c = $hex[$color] ?? $hex['blue'];
    $cols = is_string($columns) ? json_decode($columns,true) : $columns;
    $data = is_string($rows)    ? json_decode($rows,true)    : $rows;

    $interactive = $searchable || $sortable;
    $normalizedCols = $interactive
        ? array_map(fn ($col) => is_array($col) ? $col : ['label' => $col, 'key' => $col], $cols)
        : $cols;
    $defaultCol = $interactive ? ($normalizedCols[0] ?? null) : null;
@endphp

<div
    @if($interactive)
        x-data="{
            rows: {{ json_encode($data) }},
            search: '',
            sortKey: {{ json_encode($sortable ? ($defaultCol['key'] ?? null) : null) }},
            sortDir: 'asc',
            sortNumeric: {{ json_encode($defaultCol['numeric'] ?? false) }},
            toggleSort(col) {
                if (!col.key) return;
                if (this.sortKey === col.key) {
                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortKey = col.key;
                    this.sortDir = 'asc';
                    this.sortNumeric = !!col.numeric;
                }
            },
            get filteredRows() {
                let list = this.rows;
                if (this.search.trim() !== '') {
                    const q = this.search.toLowerCase();
                    list = list.filter(r => Object.values(r).some(v => String(v ?? '').toLowerCase().includes(q)));
                }
                if (this.sortKey) {
                    const key = this.sortKey, dir = this.sortDir === 'asc' ? 1 : -1, num = this.sortNumeric;
                    list = [...list].sort((a, b) => {
                        if (num) { return ((parseFloat(a[key]) || 0) - (parseFloat(b[key]) || 0)) * dir; }
                        return String(a[key] ?? '').localeCompare(String(b[key] ?? '')) * dir;
                    });
                }
                return list;
            }
        }"
    @endif
    {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>
    <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,{{ $c[0] }},{{ $c[1] }})"></div>

    <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-5 pb-3">
        <div>
            @if($subtitle)<p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $subtitle }}</p>@endif
            <h3 class="mt-0.5 text-base font-bold text-slate-800 dark:text-white">{{ $title }}</h3>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            @isset($headerEnd)
                {{ $headerEnd }}
            @endisset
            @if($searchable)
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <input type="text" x-model="search" placeholder="{{ $searchPlaceholder }}"
                        class="w-48 rounded-xl border border-slate-200 bg-slate-50 py-1.5 pl-8 pr-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-slate-300 focus:outline-none focus:ring-1 focus:ring-slate-300 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200 dark:placeholder:text-slate-500" />
                </div>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto{{ $interactive ? ' overflow-y-auto' : '' }}" @if($interactive) style="max-height:{{ $maxHeight }}" @endif>
        <table class="w-full table-fixed text-sm">
            @if($interactive)
                @if(count($normalizedCols))
                    <colgroup>
                        @foreach($normalizedCols as $col)
                            <col @if(!empty($col['width'])) style="width:{{ $col['width'] }}" @endif>
                        @endforeach
                    </colgroup>
                    <thead>
                        <tr class="border-t border-slate-100 dark:border-slate-700/60">
                            @foreach($normalizedCols as $col)
                                @php $isNumeric = !empty($col['numeric']) && (($col['type'] ?? null) !== 'bar'); @endphp
                                <th @if($sortable && !empty($col['key'])) @click="toggleSort({{ json_encode($col) }})" @endif
                                    class="bg-slate-50 px-5 py-2.5 {{ $isNumeric ? 'text-right' : 'text-left' }} text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500 {{ ($sortable && !empty($col['key'])) ? 'select-none cursor-pointer hover:text-slate-600 dark:hover:text-slate-300 transition-colors' : '' }}">
                                    {{ $col['label'] }}
                                    @if($sortable && !empty($col['key']))
                                        <span class="ml-0.5 opacity-30" x-text="sortKey === '{{ $col['key'] }}' ? (sortDir === 'asc' ? '↑' : '↓') : '↕'"></span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                @endif
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <template x-for="(row, idx) in filteredRows" :key="idx">
                        <tr class="transition hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                            @foreach($normalizedCols as $col)
                                @if(($col['type'] ?? null) === 'bar')
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-1.5 w-full min-w-0 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/60">
                                                <div class="h-1.5 rounded-full" style="background:linear-gradient(to right,{{ $c[0] }},{{ $c[1] }})" :style="{ width: Math.min(100, row['{{ $col['key'] }}'] || 0) + '%' }"></div>
                                            </div>
                                            <span class="w-11 shrink-0 text-right text-xs font-extrabold text-slate-800 dark:text-slate-200" x-text="(row['{{ $col['key'] }}'] || 0) + '%'"></span>
                                        </div>
                                    </td>
                                @elseif(!empty($col['numeric']))
                                    <td class="px-5 py-3.5 text-right font-extrabold tabular-nums text-slate-900 dark:text-white" x-text="row['{{ $col['key'] }}']"></td>
                                @else
                                    <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300">
                                        <span class="block truncate" x-text="row['{{ $col['key'] }}']" :title="row['{{ $col['key'] }}']"></span>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    </template>
                    <tr x-show="filteredRows.length === 0">
                        <td colspan="{{ max(count($normalizedCols), 1) }}" class="px-5 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                            No matching results
                        </td>
                    </tr>
                </tbody>
            @else
                @if(count($cols))
                    <thead>
                        <tr class="border-t border-slate-100 dark:border-slate-700/60">
                            @foreach($cols as $col)
                                <th class="px-5 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/50">
                                    {{ $col }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                @endif
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($data as $row)
                        <tr class="transition hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                            @foreach((array)$row as $cell)
                                <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300">
                                    <span class="block truncate">{{ $cell }}</span>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ max(count($cols),1) }}" class="px-5 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                                No data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            @endif
        </table>
    </div>
</div>
