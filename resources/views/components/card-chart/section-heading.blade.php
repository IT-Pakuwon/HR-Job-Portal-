@props([
    'title'       => '',
    'description' => '',
    'color'       => 'violet',
])

<div {{ $attributes->merge(['class' => 'px-1 pt-1']) }}>
    <h2 class="text-[13px] font-extrabold uppercase tracking-wide text-slate-700 dark:text-slate-200">{{ $title }}</h2>
    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p>
</div>
