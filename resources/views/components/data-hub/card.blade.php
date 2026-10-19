@props(['title', 'description', 'icon', 'href' => null, 'badge' => null, 'external' => false])
@php($available = filled($href))
<a href="{{ $available ? $href : '#' }}" class="dh-card {{ $available ? '' : 'dh-card-disabled' }}"
    @if ($available && $external) target="_blank" rel="noopener noreferrer" @endif
    @if (!$available) aria-disabled="true" onclick="return false" @endif>
    <div class="dh-card-top">
        <span aria-hidden="true" class="dh-icon">{{ $icon }}</span>
        @if ($badge)
            <span class="dh-badge">{{ $badge }}</span>
        @endif
    </div>
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>
    <span class="dh-link">{{ $available ? 'Open Dashboard →' : 'Link belum diisi' }}</span>
</a>
