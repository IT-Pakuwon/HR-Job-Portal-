{{-- Card header for list pages that are not SPP (Item Request, Stock/Nonstock Jobs, Inventory): icon badge + title + hint, optional Create button, extra controls in the slot.
     <x-list-page-header title="Item Request" subtitle="..." :create-url="url('/createitemreq')" title-id="pageTitle">filters here</x-list-page-header> --}}
@props(['title', 'subtitle' => 'Create, track and manage your requests', 'createUrl' => null, 'titleId' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 rounded-t-xl border-b border-gray-100 px-5 py-3.5 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between']) }}>
    <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
            @if ($icon)
                {!! $icon !!}
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            @endif
        </span>
        <div>
            <h2 @if ($titleId) id="{{ $titleId }}" @endif class="text-base font-bold tracking-tight text-gray-800 dark:text-gray-100">{{ $title }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        {{ $slot }}
        @if ($createUrl)
            <a href="{{ $createUrl }}" class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                <i class="fa-solid fa-plus mr-2 text-xs"></i>Create
            </a>
        @endif
    </div>
</div>
