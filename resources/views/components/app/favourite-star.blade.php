@props(['screenId', 'applicationId', 'favouriteKeys' => []])

@php
    $isFavourited = $screenId && in_array($screenId . '|' . $applicationId, $favouriteKeys);
@endphp

@if ($screenId)
    <button type="button"
        class="favourite-star mr-2 flex h-11 w-11 shrink-0 items-center justify-center rounded transition-colors {{ $isFavourited ? 'text-amber-400 hover:text-amber-500' : 'text-gray-300 hover:text-amber-400 dark:text-gray-600' }}"
        data-screen-id="{{ $screenId }}"
        data-application-id="{{ $applicationId }}"
        data-favourited="{{ $isFavourited ? '1' : '0' }}"
        title="{{ $isFavourited ? 'Remove from favourites' : 'Add to favourites' }}">
        <svg class="favourite-star-icon h-4 w-4" viewBox="0 0 20 20"
            fill="{{ $isFavourited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M10 2.5l2.29 4.64 5.12.74-3.7 3.61.87 5.1L10 14.9l-4.58 2.4.87-5.1-3.7-3.61 5.12-.74L10 2.5z" />
        </svg>
    </button>
@endif
