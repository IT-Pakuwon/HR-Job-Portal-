<x-app-layout>
    <style>
        @keyframes tsScan { 0%, 100% { top: 8%; } 50% { top: 88%; } }
        .ts-scanline { position: absolute; left: 10%; right: 10%; height: 2px; border-radius: 2px; background: linear-gradient(90deg, transparent, #67e8f9, transparent); box-shadow: 0 0 14px 2px rgba(103,232,249,.7); animation: tsScan 2.6s ease-in-out infinite; }
        @keyframes tsPop { from { opacity: 0; transform: translateY(8px) scale(.99); } to { opacity: 1; transform: none; } }
        .ts-pop { animation: tsPop .28s ease-out; }
        .ts-grid { background-image: radial-gradient(rgba(255,255,255,.12) 1px, transparent 1px); background-size: 18px 18px; }
    </style>

    <div class="w-full space-y-4 p-3 sm:p-4" x-data="tsChecker()" x-init="init()">

        {{-- Hero + scanner --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-900 to-violet-800 shadow-xl ring-1 ring-white/10">
            <div class="ts-grid pointer-events-none absolute inset-0 opacity-60"></div>
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-cyan-400/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -left-10 h-64 w-64 rounded-full bg-fuchsia-500/20 blur-3xl"></div>

            <div class="relative p-5 sm:p-7">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-2xl text-cyan-300 ring-1 ring-white/20 backdrop-blur">
                            <i class="fas fa-qrcode"></i>
                            <span class="ts-scanline"></span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold tracking-tight text-white sm:text-2xl">Checker TS</h1>
                            <p class="text-sm text-indigo-200">Check In / Out Posko FCC &middot; scan the QR on a SIK / SIMKB permit</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-medium text-emerald-300 ring-1 ring-emerald-400/30">
                        <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span></span>
                        Ready to scan
                    </span>
                </div>

                <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                    <button type="button" @click="scanning ? stopScan() : startScan()"
                        :class="scanning ? 'bg-rose-500 hover:bg-rose-600 shadow-rose-500/30' : 'bg-cyan-400 text-slate-900 hover:bg-cyan-300 shadow-cyan-400/30'"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-bold text-white shadow-lg transition active:scale-[.98]">
                        <i class="fas" :class="scanning ? 'fa-stop' : 'fa-camera'"></i>
                        <span x-text="scanning ? 'Stop camera' : 'Scan with camera'"></span>
                    </button>
                    <form @submit.prevent="lookup(manual)" class="flex flex-1 gap-2">
                        <div class="relative flex-1">
                            <i class="fas fa-barcode pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-indigo-300"></i>
                            <input x-ref="manual" x-model="manual" type="text" autocomplete="off" placeholder="Barcode scanner / paste QR link, then Enter"
                                class="w-full rounded-2xl border-0 bg-white/10 py-3 pl-11 pr-3 text-sm text-white placeholder-indigo-300 ring-1 ring-white/20 backdrop-blur focus:bg-white/15 focus:ring-2 focus:ring-cyan-300">
                        </div>
                        <button class="rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white ring-1 ring-white/20 transition hover:bg-white/20">Check</button>
                    </form>
                </div>

                <div x-show="scanning" x-cloak class="relative mt-4 overflow-hidden rounded-2xl bg-black/40 p-1.5 ring-2 ring-cyan-300/60">
                    <div id="ts-qr-reader" style="width:100%"></div>
                    <span class="ts-scanline"></span>
                </div>

                <div x-show="error" x-cloak class="ts-pop mt-4 flex items-start gap-2 rounded-2xl bg-red-500/15 px-4 py-3 text-sm text-red-100 ring-1 ring-red-400/40">
                    <i class="fas fa-circle-exclamation mt-0.5 text-red-300"></i><span x-text="error"></span>
                </div>
            </div>
        </section>

        {{-- Selected permit --}}
        <template x-if="permit">
            <section class="ts-pop overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-lg dark:border-white/10 dark:bg-gray-900/70">
                <div class="h-1.5 bg-gradient-to-r" :class="!permit.approved ? 'from-red-500 to-rose-400' : (permit.state === 'IN' ? 'from-emerald-500 to-teal-400' : 'from-indigo-500 to-violet-500')"></div>
                <div class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="rounded-xl px-3 py-1 text-xs font-extrabold tracking-wider"
                                :class="permit.type === 'SIK' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700'" x-text="permit.type"></span>
                            <span class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white" x-text="permit.number"></span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold"
                                :class="permit.approved ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                                <i class="fas" :class="permit.approved ? 'fa-circle-check' : 'fa-circle-xmark'"></i>
                                <span x-text="permit.approved ? 'Approved' : 'Not approved (' + permit.status + ')'"></span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold"
                                :class="permit.state === 'IN' ? 'bg-emerald-100 text-emerald-700' : (permit.state === 'STALE' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600')">
                                <i class="fas fa-circle text-[6px]" :class="permit.state === 'IN' ? 'animate-pulse' : ''"></i>
                                <span x-text="permit.state === 'IN' ? 'Inside now' : (permit.state === 'STALE' ? 'No check-out' : (permit.state === 'OUT' ? 'Checked out' : 'Not checked in'))"></span>
                            </span>
                        </div>
                    </div>

                    <dl class="mt-5 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="[label, val, icon, tone] in [['Site', permit.site, 'fa-building', 'text-sky-500 bg-sky-50'], ['Company', permit.company, 'fa-briefcase', 'text-violet-500 bg-violet-50'], ['Store', permit.store, 'fa-store', 'text-pink-500 bg-pink-50'], ['Requester', cap(permit.requester), 'fa-user', 'text-emerald-500 bg-emerald-50'], ['Period', permit.start + ' → ' + permit.end, 'fa-calendar-days', 'text-amber-500 bg-amber-50'], ['Work hour', permit.workhour, 'fa-clock', 'text-cyan-500 bg-cyan-50']]" :key="label">
                            <div class="flex items-start gap-3 rounded-2xl border border-gray-100 bg-gray-50/70 p-3 dark:border-white/5 dark:bg-white/5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" :class="tone"><i class="fas" :class="icon"></i></span>
                                <div class="min-w-0">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400" x-text="label"></dt>
                                    <dd class="break-words font-semibold text-gray-800 dark:text-gray-100" x-text="val || '—'"></dd>
                                </div>
                            </div>
                        </template>
                    </dl>

                    <div x-show="permit.approved && !permit.in_period" x-cloak class="mt-4 flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-700 ring-1 ring-amber-200"><i class="fas fa-triangle-exclamation"></i> Today is outside the permit period. Check In is blocked.</div>
                    <div x-show="permit.state === 'STALE'" x-cloak class="mt-4 flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-700 ring-1 ring-amber-200"><i class="fas fa-triangle-exclamation"></i> Checked in on an earlier day with no Check Out. Check In again for today, or Check Out to close it.</div>

                    <div class="mt-5 grid grid-cols-2 gap-3" x-show="permit.approved">
                        <button type="button" :disabled="busy || permit.state === 'IN' || !permit.in_period" @click="record('IN')"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-4 text-base font-bold text-white shadow-lg shadow-emerald-500/30 transition hover:brightness-110 active:scale-[.98] disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none">
                            <i class="fas fa-right-to-bracket"></i> Check In
                        </button>
                        <button type="button" :disabled="busy || (permit.state !== 'IN' && permit.state !== 'STALE')" @click="record('OUT')"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-500 to-orange-500 px-4 py-4 text-base font-bold text-white shadow-lg shadow-rose-500/30 transition hover:brightness-110 active:scale-[.98] disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none">
                            <i class="fas fa-right-from-bracket"></i> Check Out
                        </button>
                    </div>

                    <div class="mt-6" x-show="permit.log.length">
                        <div class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">Check log</div>
                        <ol class="space-y-3 border-l-2 border-gray-200 pl-5 dark:border-white/10">
                            <template x-for="l in permit.log" :key="l.at + l.action">
                                <li class="relative flex items-center justify-between text-sm">
                                    <span class="absolute -left-[27px] h-3.5 w-3.5 rounded-full ring-4 ring-white dark:ring-gray-900" :class="l.action === 'IN' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-100" x-text="(l.action === 'IN' ? 'Check In' : 'Check Out') + ' · ' + l.at"></span>
                                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-500 dark:bg-white/10" x-text="l.by"></span>
                                </li>
                            </template>
                        </ol>
                    </div>
                </div>
            </section>
        </template>

        {{-- Completed permits --}}
        <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-lg dark:border-white/10 dark:bg-gray-900/70">
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 p-4 sm:p-5 dark:border-white/5">
                <div class="mr-auto flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600"><i class="fas fa-clipboard-check"></i></span>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Completed permits</h2>
                        <p class="text-xs text-gray-500"><span class="font-semibold text-indigo-600" x-text="total.toLocaleString()"></span> permits &middot; click a row to check in / out</p>
                    </div>
                </div>
                <select x-model="f.year" @change="load(1)" class="rounded-xl border-gray-200 bg-gray-50 py-2 pl-3 pr-8 text-sm font-medium focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-white/10 dark:bg-white/5 dark:text-gray-100">
                    <option value="">All years</option>
                    <template x-for="y in years" :key="y"><option :value="String(y)" :selected="String(y) === f.year" x-text="y"></option></template>
                </select>
                <select x-model="f.type" @change="load(1)" class="rounded-xl border-gray-200 bg-gray-50 py-2 pl-3 pr-8 text-sm font-medium focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-white/10 dark:bg-white/5 dark:text-gray-100">
                    <option value="">SIK + SIMKB</option><option value="SIK">SIK</option><option value="SIMKB">SIMKB</option>
                </select>
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                    <input type="checkbox" x-model="f.today" @change="load(1)" class="rounded text-indigo-600"> Valid today
                </label>
                <div class="relative">
                    <i class="fas fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                    <input x-model.debounce.400ms="f.search" @input="load(1)" type="text" placeholder="Search number / store / company"
                        class="w-64 rounded-xl border-gray-200 bg-gray-50 py-2 pl-8 pr-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-white/10 dark:bg-white/5 dark:text-gray-100">
                </div>
            </div>

            <div class="overflow-x-auto transition-opacity" :class="loading ? 'opacity-50' : ''">
                <table class="w-full text-left text-sm text-gray-700 dark:text-gray-200">
                    <thead class="bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:bg-white/5">
                        <tr>
                            <th class="px-5 py-3">Permit</th><th class="px-3 py-3">Site</th><th class="px-3 py-3">Company / Store</th>
                            <th class="px-3 py-3">Requester</th><th class="px-3 py-3">Period</th><th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <template x-for="r in rows" :key="r.type + r.eid">
                            <tr @click="pick(r)" class="group cursor-pointer transition hover:bg-indigo-50/70 dark:hover:bg-white/5">
                                <td class="whitespace-nowrap border-l-4 px-5 py-3 transition" :class="r.type === 'SIK' ? 'border-indigo-400' : 'border-amber-400'">
                                    <div class="font-bold text-gray-900 dark:text-white" x-text="r.number"></div>
                                    <span class="mt-0.5 inline-block rounded-md px-1.5 py-px text-[10px] font-extrabold tracking-wider"
                                        :class="r.type === 'SIK' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700'" x-text="r.type"></span>
                                </td>
                                <td class="px-3 py-3"><i class="fas fa-location-dot mr-1 text-xs text-gray-400"></i><span x-text="r.site"></span></td>
                                <td class="px-3 py-3"><div class="font-semibold" x-text="r.company"></div><div class="text-xs text-gray-500" x-text="r.store"></div></td>
                                <td class="px-3 py-3" x-text="cap(r.requester)"></td>
                                <td class="whitespace-nowrap px-3 py-3">
                                    <div class="text-gray-700 dark:text-gray-200" x-text="r.start + ' → ' + r.end"></div>
                                    <span x-show="r.in_period" class="mt-0.5 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-px text-[10px] font-bold text-emerald-700"><i class="fas fa-bolt text-[8px]"></i> Valid today</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <span x-show="r.state === 'IN'" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700"><i class="fas fa-circle animate-pulse text-[6px]"></i> Inside</span>
                                    <span x-show="r.state === 'OUT'" class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600"><i class="fas fa-circle text-[6px]"></i> <span x-text="'Out ' + (r.last_at || '')"></span></span>
                                    <span x-show="r.state === 'STALE'" class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700"><i class="fas fa-circle text-[6px]"></i> No check-out</span>
                                    <span x-show="r.state === 'NONE'" class="text-xs text-gray-400">Not checked in</span>
                                    <i class="fas fa-chevron-right ml-2 text-[10px] text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-indigo-500"></i>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!rows.length && !loading"><td colspan="6" class="py-14 text-center text-gray-400"><i class="fas fa-folder-open mb-2 block text-3xl"></i>No completed permits found.</td></tr>
                    </tbody>
                </table>
            </div>

            <x-pager page="page" per="10" total="total" go="load" />
        </section>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        function tsChecker() {
            return {
                manual: '', ref: null, permit: null, rows: [], total: 0, page: 1, loading: false, years: [], f: { year: '{{ now('Asia/Jakarta')->year }}', type: '', today: false, search: '' }, error: '', busy: false, scanning: false, qr: null,
                init() { this.load(1); this.$nextTick(() => this.$refs.manual && this.$refs.manual.focus()); },
                async load(p) {
                    this.loading = true; this.page = p;
                    try {
                        const q = new URLSearchParams({ page: p, year: this.f.year, type: this.f.type, search: this.f.search, today: this.f.today ? 1 : 0 });
                        const r = await fetch('{{ route('tenantsupport.checker.list', [], false) }}?' + q, { headers: { Accept: 'application/json' } });
                        const j = await r.json(); this.rows = j.data; this.total = j.total; this.years = j.years;
                        if (!this.rows.length && p > 1) return this.load(p - 1);
                    } catch (e) { this.error = 'Could not load the list.'; }
                    this.loading = false;
                },
                // Capitalise the first letter of each word ("putri ami" -> "Putri Ami"); other letters are left as typed.
                cap(s) { return (s || '').replace(/(^|[\s\/.-])(\p{Ll})/gu, (m, a, b) => a + b.toUpperCase()); },
                lastPage() { return Math.max(1, Math.ceil(this.total / 10)); },
                pages() {
                    const last = this.lastPage(), cur = this.page, out = [];
                    for (let i = 1; i <= last; i++) if (i === 1 || i === last || Math.abs(i - cur) <= 2) out.push(i); else if (out[out.length - 1] !== '…') out.push('…');
                    return out;
                },
                pick(r) { this.open({ type: r.type, eid: r.eid }); window.scrollTo({ top: 0, behavior: 'smooth' }); },
                lookup(text) { text = (text || '').trim(); if (text) this.open({ code: text }); },
                async open(ref) {
                    this.error = ''; this.busy = true;
                    try { this.permit = await this.post('{{ route('tenantsupport.checker.lookup', [], false) }}', ref); this.ref = ref; this.manual = ''; }
                    catch (e) { this.permit = null; this.error = e.message; }
                    this.busy = false;
                },
                async post(url, body) {
                    const r = await fetch(url, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(body),
                    });
                    const j = await r.json().catch(() => ({}));
                    if (!r.ok) throw new Error(j.message || 'Request failed (' + r.status + ')');
                    return j;
                },
                async record(action) {
                    this.error = ''; this.busy = true;
                    try {
                        this.permit = await this.post('{{ route('tenantsupport.checker.record', [], false) }}', { ...this.ref, action }); this.load(this.page);
                    } catch (e) { this.error = e.message; }
                    this.busy = false;
                },
                async startScan() {
                    if (typeof Html5Qrcode === 'undefined') { this.error = 'Scanner library failed to load.'; return; }
                    this.scanning = true;
                    await this.$nextTick();
                    this.qr = new Html5Qrcode('ts-qr-reader');
                    try {
                        await this.qr.start({ facingMode: 'environment' }, { fps: 10, qrbox: 240 }, (text) => { this.stopScan(); this.lookup(text); });
                    } catch (e) { this.scanning = false; this.error = 'Camera unavailable (needs HTTPS and permission). Use the field above.'; }
                },
                async stopScan() {
                    try { if (this.qr) { await this.qr.stop(); this.qr.clear(); } } catch (e) {}
                    this.scanning = false;
                },
            };
        }
    </script>
</x-app-layout>
