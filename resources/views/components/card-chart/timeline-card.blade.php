@props([
    'title'    => 'Event Timeline',
    'subtitle' => '',
    'color'    => 'violet',
    'items'    => [],
])

@php
    $hex = ['violet'=>['#8B5CF6','#7C3AED'],'blue'=>['#3B82F6','#06B6D4'],'green'=>['#10B981','#0D9488'],'orange'=>['#F59E0B','#D97706'],'red'=>['#EF4444','#F43F5E'],'pink'=>['#EC4899','#C026D3'],'cyan'=>['#06B6D4','#3B82F6']];
    $c = $hex[$color] ?? $hex['violet'];

    $dotHex = ['violet'=>'#8B5CF6','blue'=>'#3B82F6','green'=>'#10B981','orange'=>'#F59E0B','red'=>'#EF4444','pink'=>'#EC4899','cyan'=>'#06B6D4'];
    $badgeBg = ['violet'=>'rgba(139,92,246,.12)','blue'=>'rgba(59,130,246,.12)','green'=>'rgba(16,185,129,.12)','orange'=>'rgba(245,158,11,.12)','red'=>'rgba(239,68,68,.12)','pink'=>'rgba(236,72,153,.12)','cyan'=>'rgba(6,182,212,.12)'];

    if (empty($items)) {
        $items = [
            ['date' => '15 Jan', 'label' => 'Campaign Launch',    'description' => 'Social media & digital ads go live',     'color' => 'green',  'done' => true],
            ['date' => '01 Feb', 'label' => 'Mid-Term Review',    'description' => 'Analyze reach, leads & CTR',             'color' => 'blue',   'done' => true],
            ['date' => '20 Feb', 'label' => 'Open House Event',   'description' => 'Property showcase for qualified leads',  'color' => 'violet', 'done' => false],
            ['date' => '10 Mar', 'label' => 'Closing Period',     'description' => 'Final negotiations & contract signing',  'color' => 'orange', 'done' => false],
        ];
    }
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg dark:border-slate-700/60 dark:bg-slate-900']) }}>
    <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,{{ $c[0] }},{{ $c[1] }})"></div>

    <div class="flex items-start justify-between px-5 pt-5 pb-3">
        <div>
            @if($subtitle)<p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $subtitle }}</p>@endif
            <h3 class="mt-0.5 text-base font-bold text-slate-800 dark:text-white">{{ $title }}</h3>
        </div>
        <span class="mt-1 text-[10px] font-semibold text-slate-400 dark:text-slate-500">{{ count($items) }} milestones</span>
    </div>

    <div class="px-5 pb-5">
        <ol class="relative border-l border-slate-200 dark:border-slate-700">
            @foreach($items as $index => $item)
                @php
                    $ic   = $item['color'] ?? $color;
                    $done = $item['done']  ?? false;
                    $dot  = $dotHex[$ic]   ?? '#8B5CF6';
                    $bg   = $badgeBg[$ic]  ?? 'rgba(139,92,246,.12)';
                    $isLast = $index === count($items) - 1;
                @endphp
                <li class="ml-5 {{ $isLast ? 'pb-0' : 'pb-5' }}">
                    {{-- Timeline dot --}}
                    <div class="absolute -left-2 flex h-4 w-4 items-center justify-center rounded-full"
                         style="background:{{ $done ? $dot : 'var(--bg,#fff)' }};outline:2px solid {{ $done ? $dot : $dot.'66' }};outline-offset:0">
                        @if($done)
                            <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 14 14">
                                <path d="M2 7l3.5 3.5L12 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @else
                            <span class="h-2 w-2 rounded-full opacity-50" style="background:{{ $dot }}"></span>
                        @endif
                    </div>

                    <div class="flex items-start gap-3">
                        {{-- Date badge --}}
                        <span class="shrink-0 rounded-md px-1.5 py-0.5 text-center text-[10px] font-bold"
                              style="background:{{ $bg }};color:{{ $dot }}">
                            {{ $item['date'] ?? '' }}
                        </span>

                        {{-- Label & description --}}
                        <div>
                            <p class="text-sm font-semibold leading-snug {{ $done ? 'text-slate-400 line-through dark:text-slate-500' : 'text-slate-800 dark:text-white' }}">
                                {{ $item['label'] ?? '' }}
                            </p>
                            @if(!empty($item['description']))
                                <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                                    {{ $item['description'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</div>
