@php
    $favouriteCatalog = collect();
    $allowedIds = collect($allowedMenuIds ?? [])->all();

    foreach (($rootMenus ?? collect()) as $rootMenu) {
        foreach ($rootMenu->children as $lvl1) {
            $lvl2 = $lvl1->children->whereIn('menu_id', $allowedIds);

            if ($lvl2->isEmpty() && !empty($lvl1->menu_route) && $lvl1->screen_id) {
                $favouriteCatalog->push([
                    'screen_id'      => $lvl1->screen_id,
                    'application_id' => $lvl1->application_id,
                    'menu_name'      => $lvl1->menu_name,
                    'menu_icon'      => $lvl1->menu_icon,
                    'parent_name'    => $rootMenu->menu_name,
                ]);
            } elseif ($lvl2->isNotEmpty()) {
                foreach ($lvl2 as $leaf) {
                    if ($leaf->screen_id) {
                        $favouriteCatalog->push([
                            'screen_id'      => $leaf->screen_id,
                            'application_id' => $leaf->application_id,
                            'menu_name'      => $leaf->menu_name,
                            'menu_icon'      => $leaf->menu_icon,
                            'parent_name'    => $lvl1->menu_name,
                        ]);
                    }
                }
            }
        }
    }

    $favouriteCatalog = $favouriteCatalog->unique(fn ($e) => $e['screen_id'] . '|' . $e['application_id'])->values();
@endphp

<style>
    @keyframes favourite-wiggle-kf {
        0%, 100% { transform: rotate(-1deg); }
        50% { transform: rotate(1deg); }
    }
    .favourite-wiggle {
        animation: favourite-wiggle-kf 0.22s ease-in-out infinite;
    }
    .favourite-wiggle:nth-child(2n) {
        animation-delay: -0.08s;
    }
    .favourite-wiggle:nth-child(3n) {
        animation-delay: -0.15s;
    }
    .shortcut-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .shortcut-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .shortcut-scroll::-webkit-scrollbar-thumb {
        background-color: rgb(209 213 219);
        border-radius: 9999px;
    }
    .dark .shortcut-scroll::-webkit-scrollbar-thumb {
        background-color: rgb(75 85 99);
    }
</style>

<div class="col-span-full rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
    x-data="favouriteMenus({{ $favouriteCatalog->toJson() }})" x-init="init()">

    <div class="flex items-center justify-between" :class="expanded ? 'mb-3' : ''">
        <button type="button" @click="toggleExpanded()" class="flex items-center gap-2">
            <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 2.5l2.29 4.64 5.12.74-3.7 3.61.87 5.1L10 14.9l-4.58 2.4.87-5.1-3.7-3.61 5.12-.74L10 2.5z" />
            </svg>
            <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100">My Favourites</h2>
            <svg class="h-3.5 w-3.5 text-gray-400 transition-transform" :class="expanded ? '' : '-rotate-90'"
                fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
            </svg>
        </button>

        <button type="button" x-show="expanded" @click="customizing = !customizing"
            class="flex items-center gap-1 text-xs font-medium text-gray-400 transition-colors hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span x-text="customizing ? 'Done' : 'Customize'"></span>
        </button>
    </div>

    <div x-show="expanded" x-collapse>

        <div class="flex flex-wrap gap-3">

            <div id="favouriteMenuList" class="contents">
                <template x-for="item in items" :key="item.id">
                    <a :href="customizing ? '#' : item.url" @click="customizing && $event.preventDefault()"
                        :data-id="item.id" :class="{ 'favourite-wiggle': customizing }"
                        class="favourite-tile group relative flex w-48 shrink-0 cursor-grab items-center gap-2.5 rounded-xl border border-gray-100 bg-gray-50 p-2.5 text-left transition-colors hover:border-indigo-200 hover:bg-indigo-50 active:cursor-grabbing dark:border-gray-700 dark:bg-gray-700/40 dark:hover:bg-gray-700">

                        <button type="button" x-show="customizing" @click.stop.prevent="removeItem(item)"
                            class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-gray-400 text-white shadow ring-2 ring-white hover:bg-gray-500 dark:bg-gray-500 dark:ring-gray-800 dark:hover:bg-gray-400"
                            style="display: none;">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <span :class="colorFor(item).bg" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg">
                            <svg class="h-4 w-4" :class="colorFor(item).text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.menu_icon"></path>
                            </svg>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-xs font-semibold text-gray-700 dark:text-gray-200" x-text="item.menu_name"></span>
                            <span class="block truncate text-[11px] text-gray-400 dark:text-gray-500" x-text="item.parent_name"></span>
                        </span>
                    </a>
                </template>
            </div>

            <button type="button" @click="showAddModal = true; search = ''"
                class="flex w-48 shrink-0 items-center gap-2.5 rounded-xl border border-dashed border-gray-300 p-2.5 text-left text-gray-400 transition-colors hover:border-indigo-300 hover:text-indigo-500 dark:border-gray-600 dark:text-gray-500 dark:hover:border-indigo-400 dark:hover:text-indigo-400">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700/60">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-xs font-semibold">Add Shortcut</span>
                    <span class="block truncate text-[11px] text-gray-400 dark:text-gray-500">Pin a module</span>
                </span>
            </button>
        </div>

    </div>

    <template x-teleport="body">
        <div x-show="showAddModal" x-transition.opacity @keydown.escape.window="showAddModal = false"
            class="fixed inset-0 z-9999 flex items-center justify-center bg-black/40 p-4" style="display: none;">
            <div @click.outside="showAddModal = false"
                class="flex max-h-[80vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800">

                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Add a shortcut</h3>
                    <button @click="showAddModal = false"
                        class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-3">
                    <input type="text" x-model="search" placeholder="Search menu..."
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div class="shortcut-scroll flex-1 overflow-y-auto px-2 pb-3">
                    <template x-if="availableCatalog.length === 0">
                        <p class="px-3 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
                            No more menus to pin — everything is already a favourite.
                        </p>
                    </template>

                    <template x-for="group in groupedCatalog" :key="group.name">
                        <div class="mb-2">
                            <p class="sticky top-0 z-10 -mx-2 bg-white/95 px-5 pb-1.5 pt-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 backdrop-blur dark:bg-gray-800/95 dark:text-gray-500"
                                x-text="group.name"></p>

                            <template x-for="entry in group.items" :key="entry.screen_id + '|' + entry.application_id">
                                <button type="button" @click="addFromCatalog(entry)"
                                    class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition-colors hover:bg-indigo-50 dark:hover:bg-gray-700/50">
                                    <span :class="colorFor(entry).bg" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition-transform group-hover:scale-105">
                                        <svg class="h-4 w-4" :class="colorFor(entry).text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="entry.menu_icon"></path>
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1 truncate font-medium text-gray-700 dark:text-gray-200" x-text="entry.menu_name"></span>
                                    <svg class="h-4 w-4 shrink-0 text-gray-300 opacity-0 transition-opacity group-hover:opacity-100 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function favouriteMenus(catalog) {
    return {
        items: [],
        catalog: catalog,
        sortable: null,
        customizing: false,
        showAddModal: false,
        search: '',
        expanded: true,

        palette: [
            { bg: 'bg-blue-500/10', text: 'text-blue-600 dark:text-blue-400' },
            { bg: 'bg-violet-500/10', text: 'text-violet-600 dark:text-violet-400' },
            { bg: 'bg-emerald-500/10', text: 'text-emerald-600 dark:text-emerald-400' },
            { bg: 'bg-orange-500/10', text: 'text-orange-600 dark:text-orange-400' },
            { bg: 'bg-rose-500/10', text: 'text-rose-600 dark:text-rose-400' },
            { bg: 'bg-cyan-500/10', text: 'text-cyan-600 dark:text-cyan-400' },
            { bg: 'bg-amber-500/10', text: 'text-amber-600 dark:text-amber-400' },
            { bg: 'bg-indigo-500/10', text: 'text-indigo-600 dark:text-indigo-400' },
        ],

        colorFor(entry) {
            const key = `${entry.screen_id}|${entry.application_id}`;
            let hash = 0;
            for (let i = 0; i < key.length; i++) hash = (hash * 31 + key.charCodeAt(i)) >>> 0;
            return this.palette[hash % this.palette.length];
        },

        get availableCatalog() {
            const favouritedKeys = new Set(this.items.map(i => `${i.screen_id}|${i.application_id}`));
            const term = this.search.trim().toLowerCase();

            return this.catalog.filter(entry => {
                if (favouritedKeys.has(`${entry.screen_id}|${entry.application_id}`)) return false;
                if (!term) return true;
                return entry.menu_name.toLowerCase().includes(term)
                    || (entry.parent_name || '').toLowerCase().includes(term);
            });
        },

        get groupedCatalog() {
            const groups = new Map();
            for (const entry of this.availableCatalog) {
                const name = entry.parent_name || 'Other';
                if (!groups.has(name)) groups.set(name, []);
                groups.get(name).push(entry);
            }
            return Array.from(groups, ([name, items]) => ({ name, items }));
        },

        init() {
            this.expanded = localStorage.getItem('favourites_expanded') !== '0';
            this.load();
            window.addEventListener('favourites-changed', () => this.load());
        },

        toggleExpanded() {
            this.expanded = !this.expanded;
            localStorage.setItem('favourites_expanded', this.expanded ? '1' : '0');
        },

        async load() {
            try {
                const res = await fetch('{{ route('menu-favourites.index') }}', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const { data = [] } = await res.json();
                this.items = data;
                this.$nextTick(() => this.initSortable());
            } catch (e) {
                console.error('favourite menus load failed', e);
            }
        },

        initSortable() {
            const el = document.getElementById('favouriteMenuList');
            if (!el || this.sortable) return;
            this.sortable = new Sortable(el, {
                animation: 150,
                onEnd: () => {
                    const ids = Array.from(el.querySelectorAll('[data-id]')).map(n => parseInt(n.dataset.id, 10));
                    this.reorder(ids);
                }
            });
        },

        async reorder(ids) {
            try {
                await fetch('{{ route('menu-favourites.reorder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids })
                });
            } catch (e) {
                console.error('favourite reorder failed', e);
            }
        },

        async toggleFavourite(screenId, applicationId) {
            await fetch('{{ route('menu-favourites.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ screen_id: screenId, application_id: applicationId })
            });
        },

        async addFromCatalog(entry) {
            try {
                await this.toggleFavourite(entry.screen_id, entry.application_id);
                await this.load();
                window.dispatchEvent(new CustomEvent('favourites-changed'));
            } catch (e) {
                console.error('add shortcut failed', e);
            }
        },

        async removeItem(item) {
            try {
                await this.toggleFavourite(item.screen_id, item.application_id);
                this.items = this.items.filter(i => i.id !== item.id);
                window.dispatchEvent(new CustomEvent('favourites-changed'));
            } catch (e) {
                console.error('remove favourite failed', e);
            }
        }
    };
}
</script>
