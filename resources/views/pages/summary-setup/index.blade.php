<x-app-layout>
    @php $label = strtoupper($p); $embedded = request()->boolean('embed'); @endphp
    <div class="max-w-9xl mx-auto w-full p-2"
        x-data="summarySetup(@js($widgets), @js(route('summary-setup.save', $p)), @js(route('summary-setup.reset', $p)))">

        {{-- Header --}}
        <div class="mb-4 flex flex-wrap items-center gap-3 {{ $embedded ? 'justify-end' : '' }}">
            @unless ($embedded)
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-indigo-600 to-violet-700 text-lg text-white shadow-sm">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">{{ $label }} Summary — Layout</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">Design what the {{ $label }} Summary panel shows. Drag cards to reorder, click one to change it. Applies to everyone who opens the panel.</p>
            </div>
            @endunless
            <span x-show="dirty" x-cloak class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Unsaved changes</span>
            <div class="flex shrink-0 items-center gap-2">
                <button type="button" @click="reset()" :disabled="busy"
                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Reset to default</button>
                <button type="button" @click="save()" :disabled="busy || !dirty"
                    class="rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">Save layout</button>
            </div>
        </div>

        <div x-show="msg" x-cloak x-transition
            :class="ok ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'"
            class="mb-3 rounded-lg border px-3 py-2 text-sm" x-text="msg"></div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">

            {{-- ============ Canvas ============ --}}
            <div class="xl:col-span-8 2xl:col-span-9">
                <div class="rounded-2xl border border-gray-200 bg-gray-100/70 p-4 shadow-inner dark:border-gray-700 dark:bg-gray-900/40">
                    <div class="mb-3 flex items-center justify-between text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                        <span><i class="fa-solid fa-table-columns mr-1"></i> Panel preview · <span x-text="shown.length"></span> cards</span>
                        <span class="normal-case tracking-normal">Tile width = width on a large screen</span>
                    </div>

                    <div class="grid grid-cols-12 gap-3" @dragover.prevent>
                        <template x-for="w in shown" :key="w.id">
                            <div draggable="true"
                                @dragstart="dragId = w.id; $event.dataTransfer.effectAllowed = 'move'"
                                @dragend="dragId = null; overId = null"
                                @dragenter.prevent="overId = w.id"
                                @drop.prevent="drop(w.id)"
                                @click="sel = w.id"
                                :style="`grid-column: span ${w.span} / span ${w.span}; min-height: ${tileH(w)}px`"
                                class="group relative cursor-grab overflow-hidden rounded-2xl border bg-white p-3 pt-4 shadow-sm transition active:cursor-grabbing dark:bg-gray-900"
                                :class="[
                                    sel === w.id ? 'border-indigo-500 ring-2 ring-indigo-400/60' : 'border-gray-200 hover:border-indigo-300 dark:border-gray-700',
                                    dragId === w.id ? 'opacity-40' : '',
                                    overId === w.id && dragId && dragId !== w.id ? 'scale-[0.98] ring-2 ring-emerald-400' : ''
                                ]">

                                <div class="absolute inset-x-0 top-0 h-1" :style="`background: linear-gradient(to right, ${hex(w.color)[0]}, ${hex(w.color)[1]})`"></div>

                                {{-- hover controls --}}
                                <div class="absolute right-2 top-2 flex gap-1 opacity-0 transition group-hover:opacity-100">
                                    <button type="button" @click.stop="move(w.id, -1)" class="flex h-6 w-6 items-center justify-center rounded-md bg-white/90 text-[10px] text-gray-500 shadow ring-1 ring-gray-200 hover:text-indigo-600 dark:bg-gray-800 dark:ring-gray-600" title="Earlier"><i class="fa-solid fa-arrow-left"></i></button>
                                    <button type="button" @click.stop="move(w.id, 1)" class="flex h-6 w-6 items-center justify-center rounded-md bg-white/90 text-[10px] text-gray-500 shadow ring-1 ring-gray-200 hover:text-indigo-600 dark:bg-gray-800 dark:ring-gray-600" title="Later"><i class="fa-solid fa-arrow-right"></i></button>
                                    <button type="button" @click.stop="w.is_visible = false; touch(); if (sel === w.id) sel = null" class="flex h-6 w-6 items-center justify-center rounded-md bg-white/90 text-[10px] text-gray-500 shadow ring-1 ring-gray-200 hover:text-rose-600 dark:bg-gray-800 dark:ring-gray-600" title="Hide"><i class="fa-solid fa-eye-slash"></i></button>
                                </div>

                                <p class="truncate pr-16 text-[10px] font-bold uppercase tracking-widest text-gray-400" x-text="w.type === 'insight' ? 'Summary Insight' : (w.title || '—')"></p>

                                {{-- mini previews by type --}}
                                <div class="mt-2" x-html="preview(w)"></div>

                                <span class="absolute bottom-1.5 right-2 rounded bg-gray-100 px-1.5 py-0.5 text-[9px] font-bold uppercase text-gray-400 dark:bg-gray-800" x-text="w.type + ' · ' + w.span + '/12'"></span>
                            </div>
                        </template>

                        {{-- + Add card: opens the picker of every All Reports card not on the panel --}}
                        <button type="button" @click="openPicker()" x-show="hiddenList.length" x-cloak
                            style="grid-column: span 3 / span 3; min-height: 120px"
                            class="group flex flex-col items-center justify-center gap-1.5 rounded-2xl border-2 border-dashed border-gray-300 bg-white/50 text-gray-400 transition hover:border-indigo-400 hover:bg-indigo-50/50 hover:text-indigo-600 dark:border-gray-600 dark:bg-transparent dark:hover:border-indigo-400">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-lg transition group-hover:bg-indigo-100 dark:bg-gray-800"><i class="fa-solid fa-plus"></i></span>
                            <span class="text-xs font-semibold">Add card</span>
                            <span class="text-[10px]" x-text="hiddenList.length + ' available'"></span>
                        </button>
                    </div>
                </div>

                {{-- ============ Card picker ============ --}}
                <div x-show="picker" x-cloak x-transition.opacity @keydown.escape.window="picker = false"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="picker = false">
                    <div class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800" @click.stop>
                        <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                            <div class="min-w-0 flex-1">
                                <h2 class="text-sm font-bold text-gray-800 dark:text-white">Add a card</h2>
                                <p class="text-xs text-gray-400">Cards from the All Reports page. Pick one to put it on the panel.</p>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                                <input type="text" x-model="q" x-ref="pickerSearch" placeholder="Search cards…"
                                    class="w-56 rounded-lg border border-gray-200 bg-gray-50 py-1.5 pl-8 pr-3 text-sm focus:border-indigo-400 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </div>
                            <button type="button" @click="picker = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"><i class="fa-solid fa-xmark"></i></button>
                        </div>

                        <div class="overflow-y-auto p-5">
                            <template x-for="g in pickerGroups" :key="g.name">
                                <div class="mb-5 last:mb-0">
                                    <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400" x-text="g.name + ' · ' + g.items.length"></p>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        <template x-for="w in g.items" :key="w.id">
                                            <button type="button" @click="add(w.id)"
                                                class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-3 pt-4 text-left transition hover:border-indigo-400 hover:shadow-md dark:border-gray-600 dark:bg-gray-900">
                                                <div class="absolute inset-x-0 top-0 h-1" :style="`background: linear-gradient(to right, ${hex(w.color)[0]}, ${hex(w.color)[1]})`"></div>
                                                <p class="truncate text-xs font-bold text-gray-800 dark:text-white" x-text="w.title || w.id"></p>
                                                <p class="mb-2 text-[10px] uppercase tracking-wider text-gray-400" x-text="w.type"></p>
                                                <div class="pointer-events-none h-20 overflow-hidden" x-html="preview(w)"></div>
                                                <span class="absolute right-2 top-2.5 flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[10px] text-gray-500 transition group-hover:bg-indigo-600 group-hover:text-white dark:bg-gray-700"><i class="fa-solid fa-plus"></i></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <p x-show="!pickerGroups.length" x-cloak class="py-10 text-center text-sm text-gray-400">No cards match.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ Inspector ============ --}}
            <aside class="xl:col-span-4 2xl:col-span-3">
                <div class="sticky top-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <template x-if="!cur">
                        <div class="py-10 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10"><i class="fa-solid fa-hand-pointer"></i></div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Pick a card</p>
                            <p class="mt-1 text-xs text-gray-400">Click a card in the preview to change its title, size and colour.</p>
                        </div>
                    </template>

                    <template x-if="cur">
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <span class="h-8 w-1.5 rounded-full" :style="`background: linear-gradient(${hex(cur.color)[0]}, ${hex(cur.color)[1]})`"></span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-800 dark:text-white" x-text="cur.type === 'insight' ? 'Summary Insight' : (cur.title || cur.id)"></p>
                                    <p class="text-[11px] text-gray-400">All Reports · <span x-text="cur.group"></span> · <span class="font-semibold uppercase" x-text="cur.type"></span></p>
                                </div>
                            </div>

                            <div x-show="cur.type !== 'insight'">
                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-gray-400">Title</label>
                                <input type="text" x-model="cur.title" @input="touch()" maxlength="100"
                                    class="w-full rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-400">Width</label>
                                <div class="grid grid-cols-4 gap-1.5">
                                    @foreach ($spans as $s)
                                        <button type="button" @click="cur.span = {{ $s }}; touch()"
                                            :class="cur.span === {{ $s }} ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'border-gray-200 text-gray-500 hover:border-indigo-300 dark:border-gray-600'"
                                            class="rounded-lg border px-1 py-1.5 text-center text-[11px] font-semibold transition">
                                            <span class="mb-1 block h-1.5 rounded-full bg-current opacity-60" style="width: {{ round($s / 12 * 100) }}%"></span>
                                            {{ $s === 12 ? 'Full' : $s . '/12' }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div x-show="cur.type !== 'insight'">
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-gray-400">Colour</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($colors as $c)
                                        <button type="button" @click="cur.color = '{{ $c }}'; touch()" title="{{ ucfirst($c) }}"
                                            :style="`background: linear-gradient(135deg, ${hex('{{ $c }}')[0]}, ${hex('{{ $c }}')[1]})`"
                                            :class="cur.color === '{{ $c }}' ? 'ring-2 ring-offset-2 ring-gray-800 dark:ring-white dark:ring-offset-gray-800' : ''"
                                            class="h-8 w-8 rounded-full shadow-sm transition hover:scale-110"></button>
                                    @endforeach
                                </div>
                            </div>

                            <div x-show="cur.type === 'donut' || cur.type === 'column'">
                                <label class="mb-1 flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    <span>Chart height</span><span class="normal-case text-gray-500" x-text="cur.height + ' px'"></span>
                                </label>
                                <input type="range" min="150" max="600" step="10" x-model.number="cur.height" @input="touch()" class="w-full accent-indigo-600">
                            </div>

                            <div class="flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-700">
                                <div class="flex gap-1.5">
                                    <button type="button" @click="move(cur.id, -1)" class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300" title="Earlier"><i class="fa-solid fa-arrow-left"></i></button>
                                    <button type="button" @click="move(cur.id, 1)" class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300" title="Later"><i class="fa-solid fa-arrow-right"></i></button>
                                </div>
                                <button type="button" @click="cur.is_visible = false; touch(); sel = null"
                                    class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:hover:bg-rose-500/10"><i class="fa-solid fa-eye-slash mr-1"></i> Hide card</button>
                            </div>
                        </div>
                    </template>
                </div>
            </aside>
        </div>

        <p class="mt-3 text-xs text-gray-400">Cards read from a fixed catalog. A brand-new metric or chart needs a developer to add it once; after that it appears here.</p>
    </div>

    <script>
        function summarySetup(widgets, saveUrl, resetUrl) {
            const HEX = { violet: ['#8B5CF6', '#7C3AED'], blue: ['#3B82F6', '#06B6D4'], green: ['#10B981', '#06B6D4'], orange: ['#F59E0B', '#D97706'], red: ['#EF4444', '#F43F5E'], pink: ['#EC4899', '#C026D3'], cyan: ['#06B6D4', '#3B82F6'] };
            const BAR = 'rounded-full';
            return {
                init() {
                    // inside Dashboard Setup the parent page asks "anything unsaved?" before it swaps this screen
                    if (@js(request()->boolean('embed'))) {
                        this.$watch('dirty', v => { try { parent.postMessage({ type: 'dash-setup-dirty', source: 'summary', dirty: v }, location.origin); } catch (e) {} });
                    }
                },
                list: widgets.map(w => ({ id: w.id, group: w.group || '', type: w.type, source: w.source, is_visible: !!w.is_visible, span: +w.span || 12, title: w.title || '', color: w.color || 'violet', height: +w.height || 260 })),
                sel: null, dragId: null, overId: null,
                dirty: false, busy: false, msg: '', ok: true,

                get shown() { return this.list.filter(w => w.is_visible); },
                get hiddenList() { return this.list.filter(w => !w.is_visible); },

                // card picker (+ Add card): the cards not on the panel, grouped by All Reports section
                picker: false, q: '',
                get pickerGroups() {
                    const q = this.q.trim().toLowerCase(), out = {};
                    this.hiddenList.filter(w => !q || (w.title + ' ' + w.group + ' ' + w.type).toLowerCase().includes(q))
                        .forEach(w => (out[w.group || 'Other'] ??= []).push(w));
                    return Object.entries(out).map(([name, items]) => ({ name, items }));
                },
                openPicker() { this.q = ''; this.picker = true; this.$nextTick(() => this.$refs.pickerSearch?.focus()); },
                // adding puts the card at the end of the panel and selects it
                add(id) {
                    const w = this.list.find(x => x.id === id); if (!w) return;
                    this.list = this.list.filter(x => x.id !== id).concat(w);
                    w.is_visible = true; this.sel = id; this.picker = false; this.touch();
                },
                get cur() { return this.list.find(w => w.id === this.sel && w.is_visible) || null; },

                hex(c) { return HEX[c] || HEX.violet; },
                touch() { this.dirty = true; },
                tileH(w) { return { insight: 80, stat: 120, split: 120, table: 190, donut: 190, mix: 150, column: 190, list: 170 }[w.type] || 140; },

                // move among the visible cards (hidden ones keep their place in the list)
                move(id, d) {
                    const vis = this.shown, i = vis.findIndex(w => w.id === id), j = i + d;
                    if (i < 0 || j < 0 || j >= vis.length) return;
                    const a = this.list.indexOf(vis[i]), b = this.list.indexOf(vis[j]);
                    [this.list[a], this.list[b]] = [this.list[b], this.list[a]];
                    this.touch();
                },
                drop(targetId) {
                    const from = this.dragId; this.dragId = this.overId = null;
                    if (!from || from === targetId) return;
                    const item = this.list.find(w => w.id === from);
                    this.list = this.list.filter(w => w.id !== from);
                    this.list.splice(this.list.findIndex(w => w.id === targetId), 0, item);
                    this.touch();
                },

                // small static illustrations so each tile looks like what it will become
                preview(w) {
                    const c = this.hex(w.color), g = `background:linear-gradient(to right,${c[0]},${c[1]})`;
                    const bar = (pct, cls = '') => `<div class="h-1.5 w-full overflow-hidden ${BAR} bg-gray-100 dark:bg-gray-700 ${cls}"><div class="h-full ${BAR}" style="width:${pct}%;${g}"></div></div>`;
                    switch (w.type) {
                        case 'stat': return `<div class="text-3xl font-extrabold text-gray-800 dark:text-white">24</div><div class="mt-1 h-1.5 w-2/3 ${BAR} bg-gray-100 dark:bg-gray-700"></div>`;
                        case 'split': return `<div class="flex justify-between"><div class="text-xl font-extrabold text-gray-800 dark:text-white">87%</div><div class="text-xl font-extrabold text-gray-400">120</div></div><div class="mt-2">${bar(70)}</div>`;
                        case 'table': return [1, 2, 3, 4].map(i => `<div class="mb-1.5 flex gap-2"><div class="h-2 w-1/4 ${BAR} bg-gray-200 dark:bg-gray-700"></div><div class="h-2 flex-1 ${BAR} bg-gray-100 dark:bg-gray-700/60"></div><div class="h-2 w-8 ${BAR}" style="${g};opacity:.7"></div></div>`).join('');
                        case 'list': return [1, 2, 3].map(i => `<div class="mb-2 flex items-center gap-2"><span class="h-2 w-2 shrink-0 ${BAR}" style="background:${c[0]}"></span><div class="h-2 flex-1 ${BAR} bg-gray-100 dark:bg-gray-700"></div></div>`).join('');
                        case 'insight': return `<div class="flex gap-4">${[60, 45, 52].map(x => `<div class="flex flex-1 items-center gap-1.5"><span class="h-2 w-2 ${BAR} bg-indigo-400"></span><div class="h-2 ${BAR} bg-gray-100 dark:bg-gray-700" style="width:${x}%"></div></div>`).join('')}</div>`;
                        case 'donut': return `<svg viewBox="0 0 36 36" class="mx-auto h-24 w-24 -rotate-90"><circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="5"/><circle cx="18" cy="18" r="14" fill="none" stroke="${c[0]}" stroke-width="5" stroke-dasharray="52 88"/><circle cx="18" cy="18" r="14" fill="none" stroke="${c[1]}" stroke-width="5" stroke-dasharray="22 88" stroke-dashoffset="-52" opacity=".7"/></svg>`;
                        case 'mix': return `<div class="flex h-3 gap-0.5 overflow-hidden ${BAR}"><div class="w-[3%] bg-rose-400"></div><div class="w-[3%] bg-amber-400"></div><div class="flex-1 bg-emerald-400"></div></div>${[1, 2, 3].map(() => `<div class="mt-2 flex gap-2"><div class="h-2 w-2 ${BAR} bg-gray-300"></div><div class="h-2 flex-1 ${BAR} bg-gray-100 dark:bg-gray-700"></div><div class="h-2 w-8 ${BAR} bg-gray-200 dark:bg-gray-600"></div></div>`).join('')}`;
                        case 'column': return `<div class="flex h-24 items-end gap-2">${[40, 60, 50, 75, 65, 90].map(h => `<div class="flex-1 rounded-t" style="height:${h}%;background:linear-gradient(to top,${c[0]},${c[1]});opacity:${h / 100 + .15}"></div>`).join('')}</div>`;
                        default: return '';
                    }
                },

                flash(ok, msg) { this.ok = ok; this.msg = msg; clearTimeout(this._t); this._t = setTimeout(() => this.msg = '', 4000); },
                async call(url, method, body) {
                    this.busy = true;
                    try {
                        const res = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                            body: body ? JSON.stringify(body) : undefined,
                        });
                        const j = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(j.message || 'Request failed (' + res.status + ')');
                        return true;
                    } catch (e) { this.flash(false, e.message); return false; }
                    finally { this.busy = false; }
                },
                async save() {
                    const widgets = this.list.map(w => ({ id: w.id, is_visible: w.is_visible, span: w.span, title: w.type === 'insight' ? null : w.title, color: w.type === 'insight' ? null : w.color, height: (w.type === 'donut' || w.type === 'column') ? w.height : null }));
                    if (await this.call(saveUrl, 'PUT', { widgets })) { this.dirty = false; this.flash(true, 'Layout saved.'); }
                },
                async reset() {
                    const ask = window.Swal ? (await Swal.fire({ title: 'Reset to default?', text: 'The saved layout is removed and the panel goes back to the default cards.', icon: 'question', showCancelButton: true, confirmButtonColor: '#4f46e5' })).isConfirmed : confirm('Reset to the default layout?');
                    if (ask && await this.call(resetUrl, 'DELETE')) location.reload();
                },
            };
        }
    </script>
</x-app-layout>
