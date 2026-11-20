<x-app-layout>
    @php
        $ctl = 'h-10 rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-10 text-sm dark:border-white/10 dark:bg-[#0f172a] dark:text-slate-200';
        $cards = [
            ['ALL', 'All', '📦', 'border-slate-400 bg-slate-200/20 text-slate-600 hover:bg-slate-100 dark:border-slate-500 dark:text-slate-300 dark:hover:bg-slate-700/30'],
            ['P', 'Pending', '⏳', 'border-yellow-500 bg-yellow-100/30 text-yellow-600 hover:bg-yellow-100 dark:text-yellow-400 dark:hover:bg-yellow-500/20'],
            ['O', 'On Progress', '🛠️', 'border-blue-500 bg-blue-100/30 text-blue-600 hover:bg-blue-100 dark:text-blue-400 dark:hover:bg-blue-500/20'],
            ['C', 'Completed', '✅', 'border-green-600 bg-green-200/20 text-green-600 hover:bg-green-100 dark:border-green-500 dark:text-green-400 dark:hover:bg-green-500/20'],
            ['X', 'Cancelled', '❌', 'border-slate-500 bg-slate-200/20 text-slate-500 hover:bg-slate-100 dark:border-slate-400 dark:text-slate-400 dark:hover:bg-slate-700/30'],
        ];
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2" x-data="complaintLog()" x-init="load()">
        <div class="mb-3 grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
            @foreach ($cards as [$key, $label, $icon, $cls])
                <button type="button" @click="setStatus('{{ $key }}')" class="text-left">
                    <div class="flex h-full items-center gap-3 rounded-lg border p-3 transition-all duration-300 hover:-translate-y-1 hover:shadow-md active:scale-95 {{ $cls }}"
                        :class="f.status === '{{ $key }}' ? 'ring-2 ring-indigo-500' : ''">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">{{ $icon }}</div>
                        <div class="min-w-0 flex-grow leading-tight"><p class="text-sm font-medium">{{ $label }}</p></div>
                        <p class="shrink-0 text-base font-bold" x-text="counts['{{ $key }}'] ?? 0"></p>
                    </div>
                </button>
            @endforeach
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Complaint Log</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="search" x-model.debounce.400ms="f.search" @input="f.page = 1; load()" placeholder="Search no, tenant, description…"
                        class="{{ $ctl }} w-60 pr-3">
                    <select x-model="f.year" @change="f.page = 1; load()" class="{{ $ctl }}">
                        <option value="">All Years</option>
                        @foreach ($years as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach
                    </select>
                    <select x-model="f.siteid" @change="f.page = 1; load()" class="{{ $ctl }} w-44">
                        <option value="">All Sites</option>
                        @foreach ($sites as $s)<option value="{{ $s->siteid }}">{{ $s->sitename }}</option>@endforeach
                    </select>
                    <select x-model="f.category" @change="f.page = 1; load()" class="{{ $ctl }} w-48">
                        <option value="">All Categories</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto p-2">
                <table class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-left font-medium">Complaint No</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Site</th>
                            <th class="px-4 py-3 text-left font-medium">Tenant</th>
                            <th class="px-4 py-3 text-left font-medium">Category</th>
                            <th class="px-4 py-3 text-left font-medium">Description</th>
                            <th class="px-4 py-3 text-left font-medium">Created By</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 dark:text-slate-200">
                        <template x-for="r in rows" :key="r.eid">
                            <tr class="cursor-pointer border-b border-gray-100 hover:bg-slate-50 dark:hover:bg-white/5" @click="detail = r">
                                <td class="px-4 py-2.5 font-semibold text-indigo-600 dark:text-indigo-400" x-text="r.complaintnbr"></td>
                                <td class="whitespace-nowrap px-4 py-2.5" x-text="String(r.complaintdate || '').substring(0, 16)"></td>
                                <td class="px-4 py-2.5" x-text="r.sitename || r.siteid || '—'"></td>
                                <td class="px-4 py-2.5" x-text="r.storename || '—'"></td>
                                <td class="px-4 py-2.5" x-text="r.category || '—'"></td>
                                <td class="max-w-xs truncate px-4 py-2.5" x-text="r.description" :title="r.description"></td>
                                <td class="px-4 py-2.5" x-text="r.created_user || '—'"></td>
                                <td class="px-4 py-2.5"><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="badge(r.status)" x-text="r.status_label"></span></td>
                            </tr>
                        </template>
                        <tr x-show="!rows.length && !loading"><td colspan="8" class="px-4 py-10 text-center text-slate-400">No complaints found.</td></tr>
                        <tr x-show="loading"><td colspan="8" class="px-4 py-10 text-center text-slate-400">Loading…</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3 text-sm text-slate-500 dark:border-white/[0.06]">
                <span x-text="total + ' complaint(s)'"></span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="go(f.page - 1)" :disabled="f.page <= 1" class="rounded-lg border border-slate-200 px-3 py-1.5 disabled:opacity-40 dark:border-white/10">Prev</button>
                    <span x-text="'Page ' + f.page + ' / ' + pages()"></span>
                    <button type="button" @click="go(f.page + 1)" :disabled="f.page >= pages()" class="rounded-lg border border-slate-200 px-3 py-1.5 disabled:opacity-40 dark:border-white/10">Next</button>
                </div>
            </div>
        </div>

        {{-- Detail --}}
        <div x-show="detail" x-cloak @keydown.escape.window="detail = null" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 dark:bg-black/70" @click="detail = null"></div>
            <div class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">
                <template x-if="detail">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-white/10">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-800 dark:text-white" x-text="'Complaint — ' + detail.complaintnbr"></h3>
                                <p class="text-xs text-slate-500" x-text="String(detail.complaintdate || '').substring(0, 16) + ' · ' + (detail.created_user || '')"></p>
                            </div>
                            <button type="button" @click="detail = null" class="h-9 w-9 rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-100 dark:border-white/10"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div class="space-y-4 p-6 text-sm text-slate-700 dark:text-slate-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div><div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Status</div><span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="badge(detail.status)" x-text="detail.status_label"></span></div>
                                <div><div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Category</div><div x-text="detail.category || '—'"></div></div>
                                <div><div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Site</div><div x-text="detail.sitename || detail.siteid || '—'"></div></div>
                                <div><div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Tenant</div><div x-text="[detail.storename, detail.tenantcompanyname].filter(Boolean).join(' · ') || '—'"></div></div>
                                <div><div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Floor / Unit</div><div x-text="[detail.floor, detail.unit].filter(Boolean).join(' / ') || '—'"></div></div>
                            </div>
                            <div><div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Description</div><p class="whitespace-pre-line" x-text="detail.description"></p></div>
                            <div x-show="detail.action"><div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Action</div><p class="whitespace-pre-line" x-text="detail.action"></p></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function complaintLog() {
            return {
                rows: [], counts: {}, total: 0, loading: false, detail: null,
                f: { status: 'ALL', year: '{{ now()->year }}', siteid: '', category: '', search: '', page: 1, per_page: 15 },
                pages() { return Math.max(1, Math.ceil(this.total / this.f.per_page)); },
                setStatus(s) { this.f.status = s; this.f.page = 1; this.load(); },
                go(p) { if (p < 1 || p > this.pages()) return; this.f.page = p; this.load(); },
                badge(s) {
                    return { P: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300', O: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
                        C: 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300' }[s] || 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300';
                },
                async load() {
                    this.loading = true;
                    try {
                        const res = await fetch('{{ route('tenantsupport.complaint.data') }}?' + new URLSearchParams(this.f), { headers: { Accept: 'application/json' } });
                        const j = await res.json();
                        this.rows = j.data; this.counts = j.counts; this.total = j.total;
                    } catch (e) { this.rows = []; } finally { this.loading = false; }
                },
            };
        }
    </script>
</x-app-layout>
