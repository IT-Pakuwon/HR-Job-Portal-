@props(['dashCd', 'items' => null])
@php
    // Tiles come from ms_dashboard_menu / ms_dashboard_menu_map (see database/sql/dashboard_menu.sql).
    // Shares the parent's Alpine scope: a tile with a panel_key toggles the parent's `panel` state.
    $menuItems = $items ?? \App\Models\DashboardMenu::forDashboard($dashCd, auth()->user());
@endphp
@php
    // panel => whether it opens by default for this user (their own pin, else the admin default)
    $pins = $menuItems->filter(fn ($i) => !empty($i['panel']))->mapWithKeys(fn ($i) => [$i['panel'] => (bool) ($i['default'] ?? false)])->all();
    // one default per dashboard: if older data has several, only the first (the one that actually opens) shows as pinned
    $firstPinned = array_search(true, $pins, true);
    $pins = array_map(fn ($k) => $k === $firstPinned, array_combine(array_keys($pins), array_keys($pins)));

    // Template panels (components/panel/<key>.blade.php) render into the page's host below the dashboard,
    // lazily: x-if only builds one when its tile is open, so unopened panels cost nothing.
    $templateItems = $menuItems->filter(fn ($i) => !empty($i['panel']) && \App\Models\DashboardMenu::isTemplatePanel($i['panel']))->values();
    $templatePanels = $templateItems->pluck('panel')->all();
@endphp
@if ($templateItems->isNotEmpty())
    {{-- Engine for <x-panel.*> templates; loaded only where a panel tile exists, before Alpine mounts a panel. --}}
    <script src="{{ asset('assets/js/Dashboard-Panel/dash-panel.js') }}?v={{ file_exists(public_path('assets/js/Dashboard-Panel/dash-panel.js')) ? filemtime(public_path('assets/js/Dashboard-Panel/dash-panel.js')) : time() }}"></script>
@endif
@foreach ($templateItems as $ti)
    <template x-teleport="#dashForeignPanelHost">
        <div x-show="panel === '{{ $ti['panel'] }}'" x-cloak style="display: none;" @if (!empty($ti['default'])) x-init="panel = panel || '{{ $ti['panel'] }}'" @endif>
            <template x-if="panel === '{{ $ti['panel'] }}'">
                <div>
                    <x-dynamic-component :component="'panel.' . $ti['panel']" :title="$ti['name']" />
                </div>
            </template>
        </div>
    </template>
@endforeach
@if ($menuItems->isNotEmpty())
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
        x-data="{
            expanded: (() => { try { return localStorage.getItem('dashMenuExpanded') !== '0'; } catch (e) { return true; } })(),
            toggle() { this.expanded = !this.expanded; try { localStorage.setItem('dashMenuExpanded', this.expanded ? '1' : '0'); } catch (e) {} },
            pins: @js($pins ?: new stdClass),
            async togglePin(panel) {
                const next = !this.pins[panel];
                const text = next
                    ? 'Open this panel by default every time you visit this dashboard? Any other default panel will be switched off.'
                    : 'Stop opening this panel by default?';
                const ok = window.Swal
                    ? (await Swal.fire({ title: next ? 'Set as default?' : 'Remove default?', text, icon: 'question', showCancelButton: true, confirmButtonText: next ? 'Yes, set it' : 'Yes, remove it', cancelButtonText: 'Cancel', confirmButtonColor: '#4f46e5' })).isConfirmed
                    : confirm(text);
                if (!ok) return;
                const before = { ...this.pins };
                // one default per dashboard: pinning this one unpins the rest
                Object.keys(this.pins).forEach(k => this.pins[k] = false);
                this.pins[panel] = next; // optimistic
                try {
                    const res = await fetch('{{ route('dashboard-menu.pin') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                        body: JSON.stringify({ dash: '{{ strtoupper($dashCd) }}', panel, open: next }),
                    });
                    if (!res.ok) throw new Error('pin failed');
                } catch (e) { this.pins = before; }
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
                <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100">Menu</h2>
                <svg class="h-3.5 w-3.5 text-gray-400 transition-transform" :class="expanded ? '' : '-rotate-90'"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                </svg>
            </button>
            {{-- Legend: icon colour = tile type (only the types this dashboard actually shows) --}}
            @php
                $legend = collect([
                    'menu'  => ['Menu', 'bg-blue-500'],
                    'panel' => ['Panel', 'bg-indigo-500'],
                    'link'  => ['Link', 'bg-amber-500'],
                ])->filter(fn ($v, $k) => $menuItems->contains(fn ($i) => ($i['kind'] ?? 'panel') === $k));
            @endphp
            @if ($legend->count() > 1)
                <div x-show="expanded" x-cloak class="flex items-center gap-3 text-[11px] text-gray-500 dark:text-gray-400">
                    @foreach ($legend as [$label, $dot])
                        <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full {{ $dot }}"></span>{{ $label }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div x-show="expanded" x-collapse>
            <div class="flex flex-wrap gap-3">
                @foreach ($menuItems as $item)
                    @php
                        $tileClass = 'group relative flex w-48 shrink-0 items-center gap-2.5 rounded-xl border border-gray-100 bg-gray-50 p-2.5 text-left transition-colors hover:border-indigo-200 hover:bg-indigo-50 dark:border-gray-700 dark:bg-gray-700/40 dark:hover:bg-gray-700';
                    @endphp
                    @if (!empty($item['panel']))
                        <div class="relative shrink-0">
                        <button type="button"
                            @click="panel =(panel === '{{ $item['panel'] }}' ? null : '{{ $item['panel'] }}'); $nextTick(() => panel && ({{ in_array($item['panel'], $templatePanels, true) ? "document.getElementById('dashForeignPanelHost')" : '$refs.omPanel' }})?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
                            :class="panel === '{{ $item['panel'] }}' ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500 dark:border-indigo-400 dark:ring-indigo-400' : ''"
                            class="{{ $tileClass }}">
                    @else
                        <a href="{{ $item['href'] }}" @if (!empty($item['external'])) target="_blank" rel="noopener noreferrer" @endif class="{{ $tileClass }}">
                    @endif
                        @php
                            // icon colour by tile type: link = yellow, panel = indigo (unchanged), menu (sidebar item) = blue
                            [$iconBox, $iconInk] = match ($item['kind'] ?? 'panel') {
                                'link' => ['bg-amber-100 dark:bg-amber-900/40', 'text-amber-600 dark:text-amber-300'],
                                'menu' => ['bg-blue-100 dark:bg-blue-900/40', 'text-blue-600 dark:text-blue-300'],
                                default => ['bg-indigo-100 dark:bg-indigo-900/40', 'text-indigo-600 dark:text-indigo-300'],
                            };
                        @endphp
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $iconBox }}">
                            <svg class="h-4 w-4 {{ $iconInk }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
