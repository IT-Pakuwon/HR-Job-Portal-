@props([
    'title'   => 'Table',
    'subtitle' => '',
    'color'   => 'blue',
    'columns' => [],
    'rows'    => [],
])

@php
    $hex = ['violet'=>['#8B5CF6','#7C3AED'],'blue'=>['#3B82F6','#06B6D4'],'green'=>['#10B981','#0D9488'],'orange'=>['#F59E0B','#D97706'],'red'=>['#EF4444','#F43F5E'],'pink'=>['#EC4899','#C026D3'],'cyan'=>['#06B6D4','#3B82F6']];
    $c = $hex[$color] ?? $hex['blue'];
    $cols = is_string($columns) ? json_decode($columns,true) : $columns;
    $data = is_string($rows)    ? json_decode($rows,true)    : $rows;
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>
    <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,{{ $c[0] }},{{ $c[1] }})"></div>

    <div class="flex items-start justify-between px-5 pt-5 pb-3">
        <div>
            @if($subtitle)<p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $subtitle }}</p>@endif
            <h3 class="mt-0.5 text-base font-bold text-slate-800 dark:text-white">{{ $title }}</h3>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
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
                            <td class="px-5 py-3 text-slate-700 dark:text-slate-300">{{ $cell }}</td>
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
        </table>
    </div>
</div>
