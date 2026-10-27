@props(['title', 'description', 'icon', 'href' => null, 'badge' => null, 'external' => false])
@php($available = filled($href))
<a href="{{ $available ? $href : '#' }}" class="dh-card {{ $available ? '' : 'dh-card-disabled' }}"
    @if ($available && $external) target="_blank" rel="noopener noreferrer" @endif
    @if (!$available) aria-disabled="true" onclick="return false" @endif>
    <span aria-hidden="true" class="dh-icon">{{ $icon }}</span>
    <div class="dh-body">
        <div class="dh-title-row">
            <h2>{{ $title }}</h2>
            @if ($badge)
                <span class="dh-badge">{{ $badge }}</span>
            @endif
        </div>
        <p>{{ $description }}</p>
        @unless ($available)
            <span class="dh-link">Link belum diisi</span>
        @endunless
    </div>
    @if ($available)
        <span class="dh-go" aria-label="Open Dashboard">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </span>
    @endif
</a>
