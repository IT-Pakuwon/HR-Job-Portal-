{{-- Card header of the Tenancy Support lists: icon badge, title, one-line hint; the slot holds filters and buttons. --}}
@props(['title', 'hint' => null, 'icon' => 'fa-file-lines'])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 rounded-t-xl border-b border-gray-100 px-5 py-3.5 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between']) }}>
    <div class="flex min-w-0 items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
            <i class="fa-solid {{ $icon }}"></i>
        </span>
        <div class="min-w-0">
            <h2 class="text-base! font-bold tracking-tight text-gray-800 dark:text-gray-100">{{ $title }}</h2>
            @if ($hint)
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
            @endif
        </div>
    </div>

    @if (trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
