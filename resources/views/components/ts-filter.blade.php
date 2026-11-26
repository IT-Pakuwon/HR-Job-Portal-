{{-- One header filter: leading icon + a <select class="ts-s2"> in the slot. --}}
@props(['icon' => 'fa-filter'])

<div {{ $attributes->merge(['class' => 'sppb-f relative']) }}>
    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-xs text-gray-400">
        <i class="fa-solid {{ $icon }}"></i>
    </span>
    {{ $slot }}
</div>
