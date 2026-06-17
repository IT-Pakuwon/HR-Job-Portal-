@props([
    'title'    => '',
    'subtitle' => '',
    'color'    => 'violet',
    'gradient' => null,
])

@php
    $hex = [
        'violet'    => ['#8B5CF6', '#7C3AED'],
        'blue'      => ['#3B82F6', '#06B6D4'],
        'green'     => ['#10B981', '#06B6D4'],
        'orange'    => ['#F59E0B', '#D97706'],
        'red'       => ['#EF4444', '#F43F5E'],
        'red-green' => ['#EF4444', '#10B981'],
        'pink'      => ['#EC4899', '#C026D3'],
        'cyan'      => ['#06B6D4', '#3B82F6'],
    ];
    $c    = $hex[$color] ?? $hex['violet'];
    $grad = $gradient ?? 'linear-gradient(to right,' . $c[0] . ',' . $c[1] . ')';
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>

    {{-- Gradient accent top bar --}}
    <div class="absolute inset-x-0 top-0 h-0.75" style="background: {{ $grad }}"></div>

    @if($title || $subtitle || isset($headerEnd))
        <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2 px-5 pb-1 pt-5">
            <div class="min-w-0 shrink-0">
                @if($subtitle)
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $subtitle }}</p>
                @endif
                @if($title)
                    <h3 class="mt-0.5 text-sm font-bold text-slate-800 dark:text-white">{{ $title }}</h3>
                @endif
            </div>
            @if(isset($headerEnd))
                <div class="flex min-w-0 shrink items-center overflow-x-auto">
                    {{ $headerEnd }}
                </div>
            @endif
        </div>
    @endif

    {{ $slot }}

</div>
