{{-- one status filter card (used by x-status-filter-cards) --}}
<a href="javascript:void(0)" role="button" class="status-filter{{ $activeClass ?? '' }} group/sf block h-full cursor-pointer text-left" {!! $attr !!}>
    <div class="flex h-full items-center gap-3 rounded-lg border p-3 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md active:scale-95 {{ $idle[$tone] }} {{ $selected[$tone] }}">
        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">{{ $icon }}</div>
        <div class="flex min-w-0 grow flex-col leading-tight"><p class="wrap-break-word text-sm font-medium group-[.active]/sf:font-bold!">{{ $label }}</p></div>
        <p class="shrink-0 text-base font-bold">{{ $value }}</p>
    </div>
</a>
