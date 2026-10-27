@props(['dashCd', 'items' => null])
@php
    // Tiles come from ms_dashboard_menu / ms_dashboard_menu_map (see database/sql/dashboard_menu.sql).
    // Shares the parent's Alpine scope: a tile with a panel_key toggles the parent's `panel` state.
    $menuItems = $items ?? \App\Models\DashboardMenu::forDashboard($dashCd, auth()->user());
@endphp
@php
    // panel => whether it opens by default for this user (their own pin, else the admin default)
    $pins = $menuItems->filter(fn ($i) => !empty($i['panel']))->mapWithKeys(fn ($i) => [$i['panel'] => (bool) ($i['default'] ?? false)])->all();
@endphp
@if ($menuItems->isNotEmpty())
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
        x-data="{
            expanded: (() => { try { return localStorage.getItem('dashMenuExpanded') !== '0'; } catch (e) { return true; } })(),
            toggle() { this.expanded = !this.expanded; try { localStorage.setItem('dashMenuExpanded', this.expanded ? '1' : '0'); } catch (e) {} },
            pins: @js($pins ?: new stdClass),
            async togglePin(panel) {
                const next = !this.pins[panel];
                this.pins[panel] = next; // optimistic
                try {
                    const res = await fetch('{{ route('dashboard-menu.pin') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                        body: JSON.stringify({ dash: '{{ strtoupper($dashCd) }}', panel, open: next }),
                    });
                    if (!res.ok) throw new Error('pin failed');
                } catch (e) { this.pins[panel] = !next; }
            },
            badges: { mailbox: 0, approval: window.__approvalWaiting || 0 },
            badgeText(src) { const n = this.badges[src] || 0; return n > 99 ? '99+' : n; },
            async loadMailbox() {
                try {
                    const res = await fetch('{{ Route::has('mailbox.unread-count') ? route('mailbox.unread-count') : '' }}', { headers: { 'Accept': 'application/json' } });
                    if (res.ok) this.badges.mailbox = (await res.json()).count || 0;
                } catch (e) { /* retried on next poll */ }
            },
            init() {
                @if ($menuItems->contains('badge', 'mailbox') && Route::has('mailbox.unread-count'))
                    this.loadMailbox();
                    setInterval(() => { if (document.visibilityState !== 'hidden') this.loadMailbox(); }, 30000);
                @endif
            }
        }"
        @approval-summary.window="badges.approval = $event.detail.waiting">

        <div class="flex items-center justify-between" :class="expanded ? 'mb-3' : ''">
            <button type="button" @click="toggle()" class="flex items-center gap-2">
                <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
                <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100">Standard Menu</h2>
                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ $menuItems->count() }} {{ \Illuminate\Support\Str::plural('tile', $menuItems->count()) }}</span>
                <svg class="h-3.5 w-3.5 text-gray-400 transition-transform" :class="expanded ? '' : '-rotate-90'"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                </svg>
            </button>
        </div>

        <div x-show="expanded" x-collapse>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr)); gap: 0.75rem;">
                @foreach ($menuItems as $item)
                    @php
                        $tileClass = 'group relative flex min-w-0 items-center gap-2.5 rounded-xl border border-gray-100 bg-gray-50 p-2.5 text-left transition-colors hover:border-indigo-200 hover:bg-indigo-50 dark:border-gray-700 dark:bg-gray-700/40 dark:hover:bg-gray-700';
                    @endphp
                    @if (!empty($item['panel']))
                        <div class="relative">
                        <button type="button"
                            @click="panel = (panel === '{{ $item['panel'] }}' ? null : '{{ $item['panel'] }}'); $nextTick(() => panel && ($refs.dashPanel || $refs.omPanel).scrollIntoView({ behavior: 'smooth', block: 'start' }))"
                            :class="panel === '{{ $item['panel'] }}' ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500 dark:border-indigo-400 dark:ring-indigo-400' : ''"
                            class="{{ $tileClass }}">
                    @else
                        <a href="{{ $item['href'] }}" target="_blank" rel="noopener noreferrer" class="{{ $tileClass }}">
                    @endif
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/40">
                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}" />
                            </svg>
                        </span>
                        @if (!empty($item['badge']))
                            <span x-show="badges['{{ $item['badge'] }}'] > 0" x-cloak x-text="badgeText('{{ $item['badge'] }}')"
                                class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white shadow ring-2 ring-white dark:ring-gray-800"></span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $item['name'] }}</span>
                            <span class="block truncate text-[11px] text-gray-400 dark:text-gray-500">{{ $item['desc'] }}</span>
                        </span>
                        @if (empty($item['panel']))
                            <svg title="Opens in a new tab" class="h-3.5 w-3.5 shrink-0 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" /></svg>
                        @endif
                    @if (!empty($item['panel']))
                        </button>
                        <button type="button" @click.stop="togglePin('{{ $item['panel'] }}')"
                            :class="pins['{{ $item['panel'] }}'] ? 'bg-indigo-600 text-white' : 'bg-white text-gray-400 opacity-70 hover:opacity-100 dark:bg-gray-700'"
                            :title="pins['{{ $item['panel'] }}'] ? 'Opens by default for you. Click to stop.' : 'Open this panel by default for me'"
                            :aria-pressed="pins['{{ $item['panel'] }}'] ? 'true' : 'false'"
                            aria-label="Open this panel by default"
                            class="absolute -left-1.5 -top-1.5 z-10 flex h-5 w-5 items-center justify-center rounded-full shadow ring-1 ring-gray-200 transition dark:ring-gray-600">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" /></svg>
                        </button>
                        </div>
                    @else
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

    </div>
@endif
