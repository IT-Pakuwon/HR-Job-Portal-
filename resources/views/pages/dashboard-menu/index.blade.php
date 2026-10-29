<x-app-layout>
    {{-- Hosted by Dashboard Setup (?embed=1): the title, section heading and dashboard picker are the host's, so they are left out here. --}}
    @php $embedded = request()->boolean('embed'); @endphp
    <div class="max-w-9xl mx-auto w-full p-2"
        x-data="dashMenuAdmin(@js($tiles), @js($dashboards), @js($sysMenus), @js($templatePanelKeys))">

        @unless ($embedded)
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-indigo-600 to-violet-700 text-lg text-white shadow-sm">
                <i class="fa-solid fa-table-cells-large"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-gray-800 dark:text-white">Dashboard Menu</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">Define menu tiles once, then choose which tiles each dashboard shows and in what order.</p>
            </div>
        </div>

        @endunless

        <div x-show="msg" x-cloak x-transition
            :class="ok ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'"
            class="mb-3 rounded-lg border px-3 py-2 text-sm" x-text="msg"></div>

        <div class="grid grid-cols-1 gap-4">

            {{-- ============ Per-dashboard tile list ============ --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                @unless ($embedded)
                <div class="mb-4">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100">Tiles per dashboard</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Pick a dashboard, then choose which tiles it shows and in what order.</p>
                </div>

                <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Dashboard</label>
                {{-- Dashboard switcher --}}
                <div class="relative mb-4" @click.outside="dashOpen = false" @keydown.escape="dashOpen = false">
                    <button type="button" @click="dashOpen = !dashOpen; if (dashOpen) $nextTick(() => $refs.dashSearch.focus())"
                        class="flex w-full items-center gap-3 rounded-xl border border-gray-300 bg-white px-3 py-2 text-left shadow-sm transition hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5"
                        :class="dashOpen ? 'border-indigo-500 ring-2 ring-indigo-100' : ''">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300"><i class="fa-solid fa-table-cells-large text-sm"></i></span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="currentDash.name"></span>
                            <span class="block truncate font-mono text-[11px] text-gray-400" x-text="currentDash.code"></span>
                        </span>
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="countClass(currentDash)" x-text="countLabel(currentDash)"></span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="dashOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="dashOpen" x-cloak x-transition.origin.top
                        class="absolute left-0 right-0 z-30 mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-600 dark:bg-gray-800">
                        <div class="border-b border-gray-100 p-2 dark:border-white/10">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                                <input type="text" x-ref="dashSearch" x-model="dashQ" placeholder="Search dashboard…"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 py-1.5 pl-8 pr-3 text-sm focus:border-indigo-400 focus:bg-white focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-gray-100">
                            </div>
                        </div>
                        <div class="max-h-72 overflow-y-auto p-1.5">
                            <template x-for="d in dashList" :key="d.code">
                                <button type="button" @click="dash = d.code; dashOpen = false; dashQ = ''"
                                    class="flex w-full items-center gap-3 rounded-lg px-2 py-1.5 text-left transition hover:bg-indigo-50 dark:hover:bg-indigo-500/10"
                                    :class="d.code === dash ? 'bg-indigo-50 dark:bg-indigo-500/10' : ''">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm text-gray-800 dark:text-gray-100" x-text="d.name"></span>
                                        <span class="block truncate font-mono text-[11px] text-gray-400" x-text="d.code"></span>
                                    </span>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="countClass(d)" x-text="countLabel(d)"></span>
                                    <i class="fa-solid fa-check w-3 text-xs text-indigo-600" :class="d.code === dash ? '' : 'invisible'"></i>
                                </button>
                            </template>
                            <p x-show="!dashList.length" class="px-3 py-6 text-center text-xs text-gray-400">No matching dashboard.</p>
                        </div>
                    </div>
                </div>
                @endunless

                <ul class="space-y-2">
                    <template x-for="(it, i) in current.items" :key="it.t + it.key">
                        <li class="rounded-xl border border-gray-100 bg-gray-50/60 p-2.5 transition hover:border-indigo-200 dark:border-gray-700 dark:bg-gray-700/30">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-[11px] font-bold text-gray-400 shadow-sm dark:bg-gray-700" x-text="i + 1"></span>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/40">
                                    <svg class="h-[18px] w-[18px] text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="info(it).icon || ''" />
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex min-w-0 items-center gap-2">
                                        <span class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="info(it).name"></span>
                                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                            :class="{ 'bg-indigo-600 text-white': info(it).kind === 'panel', 'bg-gray-200 text-gray-600 dark:bg-gray-600 dark:text-gray-200': info(it).kind === 'menu', 'bg-amber-100 text-amber-800': info(it).kind === 'link' }"
                                            :title="info(it).kind === 'panel' ? 'Opens a panel on the dashboard' : (info(it).kind === 'link' ? 'Opens an external link in a new tab' : 'Opens a page in a new tab')"
                                            x-text="info(it).kind"></span>
                                    </span>
                                    <span class="block truncate text-[11px] text-gray-400" x-text="info(it).sub"></span>
                                </span>
                                <span class="flex shrink-0 items-center">
                                    <button type="button" @click="move(i, -1)" :disabled="i === 0" class="rounded-md p-1.5 text-gray-400 transition hover:bg-white hover:text-gray-700 disabled:opacity-30 disabled:hover:bg-transparent dark:hover:bg-gray-700" title="Move up"><i class="fa-solid fa-arrow-up text-xs"></i></button>
                                    <button type="button" @click="move(i, 1)" :disabled="i === current.items.length - 1" class="rounded-md p-1.5 text-gray-400 transition hover:bg-white hover:text-gray-700 disabled:opacity-30 disabled:hover:bg-transparent dark:hover:bg-gray-700" title="Move down"><i class="fa-solid fa-arrow-down text-xs"></i></button>
                                    <button type="button" @click="current.items.splice(i, 1)" class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10" title="Remove"><i class="fa-solid fa-trash-can text-xs"></i></button>
                                </span>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <input type="text" x-model="it.desc" maxlength="255" placeholder="Subtitle (optional)"
                                    @keydown.enter.prevent="dirty(it) && saveRow(it)"
                                    class="min-w-0 flex-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs transition focus:border-indigo-400 focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-gray-100">
                                <button type="button" x-show="dirty(it)" x-cloak @click="saveRow(it)" :disabled="busy"
                                    class="shrink-0 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50">Save</button>
                                <span x-show="it.saved === null" x-cloak class="shrink-0 text-[11px] text-amber-600">Not saved yet — use Save all</span>
                            </div>
                            <label x-show="info(it).kind === 'panel' || it.key === 'MAILBOX'" x-cloak class="mt-2 flex cursor-pointer items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <input type="checkbox" x-model="it.progress" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="font-semibold">Count in Your Progress</span>
                                <span class="text-gray-400">— untick to keep its count bubble but leave it out of the "things left" checklist.</span>
                            </label>
                            <label x-show="info(it).kind === 'panel'" x-cloak class="mt-2 flex cursor-pointer items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <input type="checkbox" x-model="it.default" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="font-semibold">Open by default</span>
                                <span class="text-gray-400">— for everyone. A user can pin or unpin it for themself on the dashboard.</span>
                            </label>
                        </li>
                    </template>
                    <li x-show="!current.items.length" class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-xs text-gray-400 dark:border-white/10">
                        <i class="fa-regular fa-rectangle-list mb-1 block text-lg text-gray-300"></i>
                        No tiles — the Main Menu card is hidden on this dashboard.
                    </li>
                </ul>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    {{-- Searchable tile picker --}}
                    <div class="relative min-w-40 flex-1" @click.outside="pickOpen = false" @keydown.escape="pickOpen = false">
                        <button type="button" @click="pickOpen = !pickOpen; if (pickOpen) $nextTick(() => $refs.pickSearch.focus())"
                            class="flex w-full items-center justify-between gap-2 rounded-lg border border-dashed border-indigo-300 bg-indigo-50/50 px-3 py-2 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-300">
                            <span class="flex items-center gap-2"><i class="fa-solid fa-plus text-xs"></i> Add a tile</span>
                            <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="pickOpen ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="pickOpen" x-cloak x-transition.origin.top
                            class="absolute left-0 right-0 z-30 mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-600 dark:bg-gray-800">
                            <div class="border-b border-gray-100 p-2 dark:border-white/10">
                                <div class="mb-2 grid grid-cols-3 gap-1 rounded-xl bg-gray-100 p-1 dark:bg-white/5">
                                    <template x-for="k in [['menu','fa-bars','Menu'],['panel','fa-window-maximize','Panel'],['link','fa-arrow-up-right-from-square','Link']]" :key="k[0]">
                                        <button type="button" @click="pickType = k[0]; pickQ = ''"
                                            :class="pickType === k[0] ? 'bg-white text-indigo-600 shadow-sm dark:bg-gray-700 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                            class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-1.5 text-xs font-semibold transition">
                                            <i class="fa-solid text-[10px]" :class="k[1]"></i><span x-text="k[2]"></span>
                                        </button>
                                    </template>
                                </div>
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                                    <input type="text" x-ref="pickSearch" x-model="pickQ" :placeholder="'Search ' + pickType + '…'"
                                        class="w-full rounded-lg border border-gray-200 bg-gray-50 py-1.5 pl-8 pr-3 text-sm focus:border-indigo-400 focus:bg-white focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-gray-100">
                                </div>
                            </div>
                            <div class="max-h-72 overflow-y-auto p-1.5">
                                <template x-for="g in pickGroups" :key="g.group">
                                    <div class="mb-1">
                                        <div class="px-2 pb-1 pt-2 text-[10px] font-bold uppercase tracking-wider text-gray-400" x-text="g.group"></div>
                                        <template x-for="m in g.items" :key="m.t + m.key">
                                            <button type="button" @click="pick(m)"
                                                class="flex w-full items-center gap-2.5 rounded-lg px-2 py-1.5 text-left transition hover:bg-indigo-50 dark:hover:bg-indigo-500/10">
                                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/40">
                                                    <svg class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="m.icon || ''" />
                                                    </svg>
                                                </span>
                                                <span class="truncate text-sm text-gray-700 dark:text-gray-200" x-text="m.name"></span>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                                <p x-show="!pickGroups.length" class="px-3 py-6 text-center text-xs text-gray-400"
                                    x-text="pickType === 'panel' ? 'No panel left to add — every panel is already on this dashboard.' : (pickType === 'link' ? (tiles.some(t => t.external_url && t.status === 'A') ? 'No link left to add — every link is already on this dashboard.' : 'No external link tiles yet. Create one below.') : 'No matching menu.')"></p>
                            </div>
                            <div x-show="pickType === 'link'" class="border-t border-gray-100 p-1.5 dark:border-white/10">
                                <button type="button" @click="pickOpen = false; edit(null, 'link')"
                                    class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-500/10">
                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                    <span x-text="pickType === 'link' ? 'New external link…' : 'New custom page tile…'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    @if ($embedded)
                    {{-- the side handle is awkward inside a frame: same drawer, opened from here --}}
                    <button type="button" @click="drawer = true"
                        class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-600 shadow-sm transition hover:bg-indigo-50 dark:border-indigo-500/40 dark:bg-gray-800 dark:text-indigo-300">
                        <i class="fa-solid fa-sliders text-xs"></i> Custom tiles
                        <span class="rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-bold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200" x-text="tiles.length"></span>
                    </button>
                    @endif
                    <button type="button" @click="saveMap()" :disabled="busy"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50">
                        <i class="fa-solid fa-floppy-disk text-xs"></i> Save all
                    </button>
                    <span x-show="unsaved(current)" x-cloak class="w-full text-xs font-medium text-amber-600">
                        <i class="fa-solid fa-circle-exclamation"></i> Unsaved changes on this dashboard — Save all keeps the order, new tiles, subtitles and defaults.
                    </span>
                </div>
                <p class="mt-3 text-[11px] leading-relaxed text-gray-400">Sidebar items appear only for users whose roles grant that menu — same as the sidebar. A dashboard only shows the Main Menu card if its view includes it, and panel tiles need that dashboard to contain the panel.</p>
            </section>

            {{-- ============ Custom tiles drawer (handle sticks to the right edge) ============ --}}
            <button type="button" x-show="!drawer && {{ $embedded ? 'false' : 'true' }}" @click="drawer = true"
                class="fixed right-0 top-1/2 z-40 flex -translate-y-1/2 flex-col items-center gap-2 rounded-l-xl bg-indigo-600 px-2 py-4 text-white shadow-lg transition-all hover:pr-3 hover:bg-indigo-700"
                title="Custom tiles">
                <i class="fa-solid fa-sliders text-sm"></i>
                <span class="text-xs font-semibold tracking-wide" style="writing-mode: vertical-rl;">Custom tiles</span>
                <span class="rounded-full bg-white/20 px-1.5 py-0.5 text-[10px] font-bold" x-text="tiles.length"></span>
            </button>

            <div x-show="drawer" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/30" @click="drawer = false"></div>
            <aside x-show="drawer" x-cloak @keydown.escape.window="drawer = false"
                x-transition:enter="transform transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col bg-white shadow-2xl dark:bg-gray-800">

                <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
                    <div class="min-w-0">
                        <h2 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            Custom tiles
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-500 dark:bg-white/10 dark:text-gray-400" x-text="shownTiles.length"></span>
                        </h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">For shortcuts that aren't sidebar items — panels and external links.</p>
                    </div>
                    <button type="button" @click="drawer = false" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <div class="inline-flex gap-1 rounded-xl bg-gray-100 p-1 dark:bg-white/5">
                            <template x-for="f in [['all','All'],['panel','Panel'],['link','Link']]" :key="f[0]">
                                <button type="button" @click="tileType = f[0]"
                                    :class="tileType === f[0] ? 'bg-white text-indigo-600 shadow-sm dark:bg-gray-700 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                    class="rounded-lg px-3 py-1 text-xs font-semibold transition" x-text="f[1]"></button>
                            </template>
                        </div>
                        <button type="button" @click="edit(null)" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                            <i class="fa-solid fa-plus text-[10px]"></i> New tile
                        </button>
                    </div>
                <ul class="space-y-2">
                    <template x-for="t in shownTiles" :key="t.menu_key">
                        <li class="group flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/60 p-3 transition hover:border-indigo-200 hover:bg-indigo-50/50 dark:border-gray-700 dark:bg-gray-700/30 dark:hover:border-indigo-500/40"
                            :class="t.status !== 'A' ? 'opacity-60' : ''">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/40">
                                <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="t.icon_path || ''" />
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-2">
                                    <span class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="t.menu_name"></span>
                                    <span class="rounded bg-gray-200/70 px-1.5 py-0.5 font-mono text-[10px] text-gray-500 dark:bg-white/10 dark:text-gray-400" x-text="t.menu_key"></span>
                                </span>
                                <span class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px]">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-medium"
                                        :class="t.route_name ? 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300' : (t.external_url ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' : 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300')">
                                        <i class="fa-solid text-[9px]" :class="t.route_name ? 'fa-link' : (t.external_url ? 'fa-arrow-up-right-from-square' : 'fa-window-maximize')"></i>
                                        <span class="max-w-56 truncate" x-text="t.route_name ? t.route_name : (t.external_url ? t.external_url.replace('https://', '') : 'panel: ' + t.panel_key)"></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="t.status === 'A' ? 'bg-green-500' : 'bg-gray-400'"></span><span x-text="t.status === 'A' ? 'Active' : 'Inactive'"></span>
                                    </span>
                                </span>
                            </span>
                            <button type="button" @click="edit(t)" class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-white/10 dark:bg-white/5">Edit</button>
                        </li>
                    </template>
                    <li x-show="!tiles.length" class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-xs text-gray-400">No custom tiles yet.</li>
                </ul>
                </div>
            </aside>
        </div>

        {{-- ============ Tile editor ============ --}}
        @php $inp = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-100'; @endphp
        <div x-show="form" x-cloak x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" @keydown.escape.window="form = null">
            <div class="flex max-h-full w-full max-w-xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800" @click.outside="if (!$event.target.closest('.select2-container, .select2-dropdown')) form = null">

                <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/40">
                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="form?.icon_path || 'M12 4.5v15m7.5-7.5h-15'" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-sm font-bold text-gray-800 dark:text-white" x-text="form?.menu_name || (isNew ? 'New tile' : 'Edit tile')"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="isNew ? 'Create a custom tile' : 'Editing ' + form?.menu_key"></p>
                    </div>
                    <button type="button" @click="form = null" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <template x-if="form">
                    <div class="space-y-5 overflow-y-auto px-5 py-4 text-sm">

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Key</label>
                                <input type="text" x-model="form.menu_key" :disabled="!isNew" @input="form.menu_key = form.menu_key.toUpperCase()" maxlength="30" placeholder="E.g. CICLE_TEAM" class="{{ $inp }} font-mono">
                                <p class="mt-1 text-[11px] text-gray-400">A–Z, 0–9, _ — can't change later.</p>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Name</label>
                                <input type="text" x-model="form.menu_name" maxlength="100" class="{{ $inp }}">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Description</label>
                            <input type="text" x-model="form.menu_desc" maxlength="255" placeholder="Short subtitle shown under the name" class="{{ $inp }}">
                        </div>

                        <div>
                            <div class="mb-1.5 flex items-center justify-between">
                                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Icon</label>
                                <span class="text-[11px] text-gray-400"><span x-text="iconChoices.length"></span> available</span>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 ring-1 ring-indigo-200 dark:bg-indigo-900/40 dark:ring-indigo-500/30">
                                        <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="form.icon_path || 'M12 4.5v15m7.5-7.5h-15'" :class="form.icon_path ? '' : 'opacity-30'" />
                                        </svg>
                                    </span>
                                    <div class="relative min-w-0 flex-1">
                                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400"></i>
                                        <input type="text" x-model="iconQ" placeholder="Search icons by name…" class="{{ $inp }} pl-8 text-xs">
                                    </div>
                                </div>
                                <div class="mt-3 grid max-h-44 grid-cols-6 gap-1.5 overflow-y-auto pr-1 sm:grid-cols-8" style="scrollbar-width: thin;">
                                    <template x-for="ic in iconChoices" :key="ic.path">
                                        <button type="button" :title="ic.label" @click="form.icon_path = ic.path"
                                            :class="form.icon_path === ic.path ? 'border-indigo-500 bg-indigo-50 text-indigo-600 ring-2 ring-indigo-200 dark:bg-indigo-500/20 dark:text-indigo-300 dark:ring-indigo-500/30' : 'border-gray-200 bg-white text-gray-600 hover:border-indigo-300 hover:text-indigo-600 dark:border-white/10 dark:bg-gray-800 dark:text-gray-300'"
                                            class="flex h-11 items-center justify-center rounded-xl border transition">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="ic.path" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                                <p x-show="!iconChoices.length" class="py-4 text-center text-xs text-gray-400">No icon matches.</p>
                                <details class="mt-3 text-xs">
                                    <summary class="cursor-pointer select-none font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400">Use a custom SVG path instead</summary>
                                    <input type="text" x-model="form.icon_path" placeholder='SVG path "d" value (24×24 outline)' class="{{ $inp }} mt-2 font-mono text-xs">
                                </details>
                            </div>
                        </div>

                        {{-- What the tile opens --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">When clicked, the tile…</label>
                            {{-- "Opens a page" (route) is retired for new tiles; it only shows when editing a tile that already uses one --}}
                            <div class="mb-2 grid gap-1 rounded-xl bg-gray-100 p-1 dark:bg-white/5" :class="form.route_name ? 'grid-cols-3' : 'grid-cols-2'">
                                <template x-for="k in [['route','fa-link','Opens a page'],['panel','fa-window-maximize','Opens a panel'],['link','fa-arrow-up-right-from-square','External link']].filter(k => k[0] !== 'route' || form.route_name)" :key="k[0]">
                                    <button type="button" @click="form.kind = k[0]"
                                        :class="form.kind === k[0] ? 'bg-white text-indigo-600 shadow-sm dark:bg-gray-700 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                        class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-1.5 text-xs font-semibold transition">
                                        <i class="fa-solid text-[10px]" :class="k[1]"></i><span x-text="k[2]"></span>
                                    </button>
                                </template>
                            </div>
                            <div x-show="form.kind === 'route'">
                                <input type="text" list="dmRoutes" x-model="form.route_name" placeholder="Route name, e.g. mailbox.index" class="{{ $inp }}">
                                <datalist id="dmRoutes">@foreach ($routeNames as $rn)<option value="{{ $rn }}">@endforeach</datalist>
                            </div>
                            <div x-show="form.kind === 'panel'">
                                <select x-select2="form.panel_key" data-s2-nosearch class="{{ $inp }}">
                                    <option value="">Choose a panel…</option>
                                    @foreach (\App\Models\DashboardMenu::templatePanels() as $pk => $label)<option value="{{ $pk }}">{{ $label }}</option>@endforeach
                                </select>
                                <p class="mt-1 text-[11px] text-gray-400">Any dashboard can show these panels as a Menu tile.</p>
                            </div>
                            <div x-show="form.kind === 'link'">
                                <input type="url" x-model="form.external_url" placeholder="https://…" class="{{ $inp }}">
                                <p class="mt-1 text-[11px] text-gray-400">https only. Opens in a new browser tab.</p>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Status</label>
                            <select x-select2="form.status" data-s2-nosearch class="{{ $inp }}">
                                <option value="A">Active</option><option value="I">Inactive (hidden everywhere)</option>
                            </select>
                            <p class="mt-1 text-[11px] text-gray-400">Whoever is assigned to a dashboard sees the tiles on it.</p>
                        </div>

                        <p x-show="formError" class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600 dark:bg-red-500/10" x-text="formError"></p>
                    </div>
                </template>

                <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/70 px-5 py-3 dark:border-white/10 dark:bg-white/5">
                    <button type="button" @click="form = null" class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:bg-transparent dark:text-gray-200">Cancel</button>
                    <button type="button" @click="saveTile()" :disabled="busy" class="rounded-lg bg-indigo-600 px-5 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-50">Save</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* select2 inside the tile form, styled like the other inputs */
        .select2-container { z-index: 70; }
        .select2-container--default .select2-selection--single { height: 38px; border: 1px solid #d1d5db; border-radius: 0.5rem; background: #fff; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; padding-left: 12px; font-size: 0.875rem; color: #111827; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; right: 6px; }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--single { border-color: #6366f1; box-shadow: 0 0 0 2px #e0e7ff; }
        .select2-dropdown { border-color: #d1d5db; border-radius: 0.5rem; overflow: hidden; font-size: 0.875rem; }
        .select2-container--default .select2-results__option--highlighted[aria-selected] { background: #4f46e5; }
        .select2-container--default .select2-search--dropdown .select2-search__field { border-radius: 0.375rem; border-color: #d1d5db; }
        .dark .select2-container--default .select2-selection--single { background: rgb(255 255 255 / 0.05); border-color: rgb(255 255 255 / 0.1); }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered { color: #f3f4f6; }
        .dark .select2-dropdown { background: #1f2937; border-color: rgb(255 255 255 / 0.1); color: #e5e7eb; }
        .dark .select2-container--default .select2-search--dropdown .select2-search__field { background: #111827; color: #f3f4f6; border-color: rgb(255 255 255 / 0.15); }
        .dark .select2-container--default .select2-results__option[aria-selected=true] { background: #374151; }
    </style>
    <script>
        // Leaving the page with edits that were never saved.
        window.addEventListener('beforeunload', (e) => {
            const root = document.querySelector('[x-data^="dashMenuAdmin"]');
            const data = root && window.Alpine ? window.Alpine.$data(root) : null;
            if (data && data.dashboards.some(d => data.unsaved(d))) { e.preventDefault(); e.returnValue = ''; }
        });

        // x-select2="path": a select2 bound to an Alpine value (select2 fires jQuery events, which x-model never sees).
        document.addEventListener('alpine:init', () => {
            Alpine.directive('select2', (el, { expression }, { effect, cleanup }) => {
                const $el = window.jQuery(el);
                $el.select2({
                    width: '100%',
                    dropdownParent: window.jQuery(el.closest('.fixed') || document.body),
                    minimumResultsForSearch: el.hasAttribute('data-s2-nosearch') ? Infinity : 0,
                });
                $el.on('change', () => Alpine.evaluate(el, expression + ' = __v', { scope: { __v: $el.val() } }));
                effect(() => {
                    const v = String(Alpine.evaluate(el, expression) ?? '');
                    if ($el.val() !== v) $el.val(v).trigger('change.select2');
                });
                cleanup(() => { try { $el.off('change').select2('destroy'); } catch (e) { /* already gone */ } });
            });
        });
        function dashMenuAdmin(tiles, dashboards, sysMenus, templatePanelKeys) {
            const csrf = '{{ csrf_token() }}';
            const tileUrl = '{{ route('dashboard-menu.tile') }}';
            const mapUrl = (d) => '{{ url('/dashboard-menu/map') }}/' + d;
            // what is stored per row, so a subtitle edit can show its own Save button
            dashboards.forEach(d => {
                d.items.forEach(it => { it.saved = it.desc || ''; it.default = !!it.default; it.savedDef = it.default; it.progress = it.progress !== false; it.savedProg = it.progress; });
                d.savedOrder = d.items.map(it => it.t + '|' + it.key).join(',');
            });

            return {
                tiles, dashboards, sysMenus,
                init() {
                    // inside Dashboard Setup the parent page asks "anything unsaved?" before it swaps this screen
                    if (@js(request()->boolean('embed'))) {
                        this.$watch(() => this.unsaved(this.current), v => { try { parent.postMessage({ type: 'dash-setup-dirty', source: 'menu', dirty: v }, location.origin); } catch (e) {} });
                    }
                },
                dash: (dashboards.find(d => d.code === @js(request('dash'))) || dashboards[0])?.code, pickOpen: false, pickQ: '', pickType: 'menu', dashOpen: false, dashQ: '', drawer: false, tileType: 'all', iconQ: '', icons: @js($iconLibrary),
                form: null, isNew: false, formError: '', busy: false, msg: '', ok: true,

                get currentDash() { return this.dashboards.find(d => d.code === this.dash) || { code: '', name: '', items: [] }; },
                get dashList() {
                    const q = this.dashQ.trim().toLowerCase();
                    return this.dashboards.filter(d => !q || d.name.toLowerCase().includes(q) || d.code.toLowerCase().includes(q));
                },
                countLabel(d) { return d.items.length + (d.items.length === 1 ? ' tile' : ' tiles'); },
                countClass(d) { return d.items.length ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400'; },
                get current() { return this.dashboards.find(d => d.code === this.dash) || { items: [] }; },
                tile(key) { return this.tiles.find(t => t.menu_key === key); },
                has(t, key) { return this.current.items.some(i => i.t === t && i.key === key); },
                // A panel tile only works on the dashboard that owns that panel (OM also hosts 'approval').
                // A template panel (tab) works on any dashboard; a dashboard card only on its owner (OM also hosts 'approval').
                panelOk(t) { return !t || !t.panel_key || templatePanelKeys.includes(t.panel_key) || (['DASHOM', 'DASHGM'].includes(this.dash) && t.panel_key === 'approval'); },
                get iconChoices() { const q = this.iconQ.trim().toLowerCase(); return q ? this.icons.filter(i => i.label.toLowerCase().includes(q)) : this.icons; },
                get shownTiles() { return this.tiles.filter(t => this.tileType === 'all' || (this.tileType === 'link' ? !!t.external_url : !!t.panel_key)); },
                get availableTiles() { return this.tiles.filter(t => t.status === 'A' && this.panelOk(t) && !this.has('c', t.menu_key)); },
                get availableSys() {
                    const groups = {};
                    this.sysMenus.filter(m => !this.has('s', m.menu_id)).forEach(m => (groups[m.group || 'Other'] ??= []).push(m));
                    return Object.entries(groups).map(([group, items]) => ({ group, items }));
                },
                info(it) {
                    if (it.t === 's') {
                        const m = this.sysMenus.find(x => x.menu_id === it.key);
                        return { kind: 'menu', name: m?.menu_name || it.key, icon: m?.icon_path, sub: 'Sidebar · ' + (m?.group ? m.group + ' › ' : '') + it.key };
                    }
                    const t = this.tile(it.key);
                    return { kind: t?.panel_key ? 'panel' : (t?.external_url ? 'link' : 'menu'), name: t?.menu_name || it.key, icon: t?.icon_path, sub: 'Custom · ' + it.key + (t && t.status !== 'A' ? ' — inactive (hidden)' : (!this.panelOk(t) ? ' — not a panel template (hidden)' : '')) };
                },
                // Groups for the picker: custom tiles first, then sidebar menus; filtered by the search box.
                get pickGroups() {
                    const q = this.pickQ.trim().toLowerCase();
                    const hit = (m) => !q || m.name.toLowerCase().includes(q) || m.group.toLowerCase().includes(q);
                    const groups = [];
                    const kindOf = (t) => t.panel_key ? 'panel' : (t.external_url ? 'link' : 'menu');
                    const label = { menu: 'Custom tiles', panel: 'Panels', link: 'External links' }[this.pickType];
                    const custom = this.availableTiles.filter(t => kindOf(t) === this.pickType)
                        .map(t => ({ t: 'c', key: t.menu_key, name: t.menu_name, icon: t.icon_path, group: label })).filter(hit);
                    if (custom.length) groups.push({ group: label, items: custom });
                    if (this.pickType !== 'menu') return groups;
                    this.availableSys.forEach(g => {
                        const items = g.items.map(m => ({ t: 's', key: m.menu_id, name: m.menu_name, icon: m.icon_path, group: g.group })).filter(hit);
                        if (items.length) groups.push({ group: g.group, items });
                    });
                    return groups;
                },
                pick(m) {
                    this.current.items.push({ t: m.t, key: m.key, desc: '', saved: null, default: false, savedDef: false, progress: true, savedProg: true });
                    this.pickOpen = false; this.pickQ = '';
                },
                move(i, dir) { const k = this.current.items; [k[i], k[i + dir]] = [k[i + dir], k[i]]; },
                flash(text, ok = true) { this.msg = text; this.ok = ok; setTimeout(() => this.msg = '', 4000); },

                edit(t, kind = 'panel') {
                    this.isNew = !t; this.formError = '';
                    this.form = t ? { ...t, kind: t.external_url ? 'link' : (t.panel_key ? 'panel' : 'route'), menu_desc: t.menu_desc || '', icon_path: t.icon_path || '', route_name: t.route_name || '', external_url: t.external_url || '', panel_key: t.panel_key || '' }
                        : { kind, menu_key: '', menu_name: '', menu_desc: '', icon_path: '', route_name: '', external_url: '', panel_key: '', status: 'A' };
                },

                async send(url, method, body) {
                    this.busy = true;
                    try {
                        const res = await fetch(url, { method, headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) });
                        const json = await res.json().catch(() => ({}));
                        if (!res.ok) return { error: json.message || Object.values(json.errors || {}).flat()[0] || 'Request failed.' };
                        return { json };
                    } catch (e) { return { error: 'Network error.' }; }
                    finally { this.busy = false; }
                },

                async saveTile() {
                    const f = this.form;
                    if (f.kind !== 'route') f.route_name = '';
                    if (f.kind !== 'panel') f.panel_key = '';
                    if (f.kind !== 'link') f.external_url = '';
                    const r = await this.send(tileUrl, 'POST', this.form);
                    if (r.error) { this.formError = r.error; return; }
                    const i = this.tiles.findIndex(t => t.menu_key === this.form.menu_key);
                    const row = { ...this.form, route_name: this.form.route_name || null, external_url: this.form.external_url || null, panel_key: this.form.panel_key || null, role_id: null };
                    i >= 0 ? this.tiles.splice(i, 1, row) : this.tiles.push(row);
                    this.form = null; this.flash('Tile saved.');
                },

                async saveMap() {
                    const r = await this.send(mapUrl(this.dash), 'PUT', { items: this.current.items });
                    if (r.error) return this.flash(r.error, false);
                    this.current.items.forEach(it => { it.saved = it.desc || ''; it.savedDef = !!it.default; it.savedProg = !!it.progress; });
                    this.current.savedOrder = this.current.items.map(it => it.t + '|' + it.key).join(',');
                    this.flash(this.dash + ' menu saved.');
                },

                // Anything on this dashboard not stored yet: order, added/removed tiles, a subtitle or a default flag.
                unsaved(d) {
                    return d.items.map(it => it.t + '|' + it.key).join(',') !== d.savedOrder
                        || d.items.some(it => it.saved === null || this.dirty(it));
                },

                // A stored row whose subtitle / default flag was edited (new rows go through Save all).
                dirty(it) { return it.saved !== null && ((it.desc || '') !== it.saved || !!it.default !== it.savedDef || !!it.progress !== it.savedProg); },

                // Save just one row's subtitle and "open by default".
                async saveRow(it) {
                    const r = await this.send(mapUrl(this.dash) + '/row', 'PATCH', { t: it.t, key: it.key, desc: it.desc || '', default: !!it.default, progress: !!it.progress });
                    if (r.error) return this.flash(r.error, false);
                    it.saved = it.desc || '';
                    it.savedDef = !!it.default;
                    it.savedProg = !!it.progress;
                    if (it.default) this.current.items.forEach(o => { if (o !== it) { o.default = false; o.savedDef = false; } });
                    this.flash('Saved.');
                },
            };
        }
    </script>
</x-app-layout>
