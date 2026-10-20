<x-app-layout>
    <style>
        #kontrakReportTable {
            --kr-bg: #fff; --kr-head: #f8fafc; --kr-stripe: #fafbfd; --kr-hover: #eef2ff;
            --kr-border: #eef0f4; --kr-muted: #64748b;
            border-collapse: separate; border-spacing: 0; font-size: 12.5px;
        }
        .dark #kontrakReportTable {
            --kr-bg: #1f2937; --kr-head: #111827; --kr-stripe: #222d3d; --kr-hover: #2b3548;
            --kr-border: #2d3748; --kr-muted: #94a3b8;
        }
        #kontrakReportTable thead th {
            background: var(--kr-head); color: var(--kr-muted);
            font-size: 11px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase;
            padding: 12px 14px; text-align: left; white-space: nowrap;
            border-bottom: 2px solid var(--kr-border);
        }
        #kontrakReportTable tbody td {
            vertical-align: top; padding: 14px; line-height: 1.55;
            background: var(--kr-bg); border-bottom: 1px solid var(--kr-border);
            transition: background-color .15s;
        }
        #kontrakReportTable tbody tr:nth-child(even) td { background: var(--kr-stripe); }
        #kontrakReportTable tbody tr:hover td { background: var(--kr-hover); }
        #kontrakReportTable tbody tr:last-child td { border-bottom: 0; }
        #kontrakReportTable thead th { padding-right: 26px; }
        /* frozen columns 1-5: fixed widths so each sticky offset is exact (sum = 920px) */
        #kontrakReportTable th:nth-child(-n+5),
        #kontrakReportTable td:nth-child(-n+5) { position: sticky; box-sizing: border-box; overflow: hidden; }
        #kontrakReportTable thead th:nth-child(-n+5) { z-index: 4; }
        #kontrakReportTable tbody td:nth-child(-n+5) { z-index: 2; }
        #kontrakReportTable th:nth-child(1), #kontrakReportTable td:nth-child(1) { left: 0;     width: 140px; min-width: 140px; max-width: 140px; white-space: nowrap; font-weight: 600; }
        #kontrakReportTable th:nth-child(2), #kontrakReportTable td:nth-child(2) { left: 140px; width: 80px;  min-width: 80px;  max-width: 80px;  white-space: nowrap; }
        #kontrakReportTable th:nth-child(3), #kontrakReportTable td:nth-child(3) { left: 220px; width: 200px; min-width: 200px; max-width: 200px; white-space: normal; overflow-wrap: break-word; }
        #kontrakReportTable th:nth-child(4), #kontrakReportTable td:nth-child(4) { left: 420px; width: 330px; min-width: 330px; max-width: 330px; }
        #kontrakReportTable th:nth-child(5), #kontrakReportTable td:nth-child(5) { left: 750px; width: 170px; min-width: 170px; max-width: 170px; white-space: normal; box-shadow: 6px 0 8px -4px rgba(15,23,42,.18); }
        #kontrakReportTable .contract-job {
            min-width: 330px; width: 330px; max-width: 330px;
            white-space: normal; overflow-wrap: anywhere;
            display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;
        }
        #kontrakReportTable td.contract-job { display: table-cell; }
        #kontrakReportTable td.contract-job > div {
            display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;
        }
        #kontrakReportTable .contract-month { min-width: 130px; width: 140px; max-width: 170px; white-space: normal; }
        #kontrakReportTable .contract-number { white-space: nowrap; }
        #kontrakReportTable .contract-payment + .contract-payment { margin-top: 12px; }
        #kontrakReportTable .kr-badge {
            display: inline-block; margin-top: 4px; padding: 1px 8px; border-radius: 999px;
            font-size: 11px; font-weight: 500; background: #f1f5f9; color: #475569;
        }
        #kontrakReportTable .kr-badge.ok { background: #dcfce7; color: #15803d; }
        #kontrakReportTable .kr-badge.hold { background: #fef3c7; color: #b45309; }
        #kontrakReportTable .kr-badge.bad { background: #fee2e2; color: #b91c1c; }
        .dark #kontrakReportTable .kr-badge { background: #334155; color: #cbd5e1; }
        .dark #kontrakReportTable .kr-badge.ok { background: rgba(34,197,94,.18); color: #86efac; }
        .dark #kontrakReportTable .kr-badge.hold { background: rgba(245,158,11,.18); color: #fcd34d; }
        .dark #kontrakReportTable .kr-badge.bad { background: rgba(239,68,68,.18); color: #fca5a5; }
        #kontrakReportTable .kr-cpny {
            display: inline-block; padding: 1px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;
            background: #eef2ff; color: #4338ca;
        }
        .dark #kontrakReportTable .kr-cpny { background: rgba(99,102,241,.2); color: #a5b4fc; }
        #kontrakReportTable .kr-empty { color: var(--kr-muted); opacity: .5; }
        #kontrakReportCard { border-radius: 12px; box-shadow: 0 1px 3px rgba(15,23,42,.08); overflow: hidden; }
        #kontrakReportCard .overflow-x-auto { overflow: visible; }
        /* Chrome/Edge/Safari ignore ::-webkit-scrollbar when scrollbar-color is set, so only Firefox gets it */
        @supports not selector(::-webkit-scrollbar) {
            #kontrakReportTable { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
            .dark #kontrakReportTable { scrollbar-color: #475569 transparent; }
        }
        #kontrakReportTable::-webkit-scrollbar { height: 10px; }
        #kontrakReportTable::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 999px; margin: 0 4px; }
        #kontrakReportTable::-webkit-scrollbar-thumb {
            background: #cbd5e1; border-radius: 999px; border: 2px solid #f1f5f9; transition: background .15s;
        }
        #kontrakReportTable::-webkit-scrollbar-thumb:hover { background: #818cf8; }
        #kontrakReportTable::-webkit-scrollbar-button { display: none; width: 0; height: 0; }
        .dark #kontrakReportTable::-webkit-scrollbar-track { background: #111827; }
        .dark #kontrakReportTable::-webkit-scrollbar-thumb { background: #475569; border-color: #111827; }
        .dark #kontrakReportTable::-webkit-scrollbar-thumb:hover { background: #6366f1; }
        #kontrakReportTable {
            display: block; width: 100% !important; overflow-x: auto; overflow-y: hidden; padding-bottom: 6px;
        }
        #kontrakReportCard .dataTables_wrapper::after { content: ""; display: block; clear: both; }
        #kontrakReportCard .dataTables_wrapper { font-size: 13px; }
        #kontrakReportCard .dataTables_info {
            float: left; color: #64748b; padding: 16px 4px 4px; margin-top: 8px; width: 40%;
        }
        #kontrakReportCard .dataTables_paginate {
            float: right; display: flex; gap: 4px; align-items: center;
            padding: 12px 4px 4px; margin-top: 8px; width: 60%; justify-content: flex-end;
        }
        #kontrakReportCard .dataTables_paginate span { display: flex; gap: 4px; }
        #kontrakReportCard .dataTables_paginate .paginate_button {
            min-width: 34px; height: 34px; padding: 0 10px !important; margin: 0 !important;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid #e5e7eb !important; border-radius: 8px !important;
            background: transparent !important; color: #475569 !important; cursor: pointer; font-size: 13px;
        }
        #kontrakReportCard .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
            background: #eef2ff !important; color: #4338ca !important; border-color: #c7d2fe !important;
        }
        #kontrakReportCard .dataTables_paginate .paginate_button.disabled { opacity: .4; cursor: default; }
        #kontrakReportCard .dataTables_paginate .paginate_button.current {
            background: #4f46e5 !important; color: #fff !important; border-color: #4f46e5 !important; font-weight: 600;
        }
        #kontrakReportCard .dataTables_paginate .ellipsis { padding: 0 6px; color: #94a3b8; }
        .dark #kontrakReportCard .dataTables_paginate .paginate_button { border-color: #374151 !important; color: #cbd5e1 !important; }
        @media (max-width: 640px) {
            #kontrakReportCard .dataTables_info,
            #kontrakReportCard .dataTables_paginate { float: none; width: 100%; justify-content: center; text-align: center; }
        }
    </style>
    <div class="max-w-9xl mx-auto space-y-4 p-2">
        <h1 class="px-2 text-lg font-semibold text-gray-800 dark:text-gray-100">Report Kontrak</h1>

        <form id="kontrakReportFilters" class="grid grid-cols-1 gap-4 border-y border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800 md:grid-cols-4">
            <div>
                <label for="report_kontrakid" class="text-xs text-gray-500 dark:text-gray-400">Kontrak ID</label>
                <input id="report_kontrakid" name="kontrakid" type="text" maxlength="100"
                    class="mt-1 w-full rounded-lg border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            </div>
            <div>
                <label for="report_cpny" class="text-xs text-gray-500 dark:text-gray-400">Cpny ID</label>
                @php
                    $companyOptions = $companyIds->map(fn ($id) => ['id' => $id, 'name' => $companyNames[$id] ?? ''])->values();
                @endphp
                <div class="relative mt-1"
                    x-data="{ open: false, value: '', q: '', items: @js($companyOptions),
                        get label() { const i = this.items.find(i => i.id === this.value); return i ? i : null; },
                        get filtered() { const q = this.q.trim().toLowerCase(); return q ? this.items.filter(i => (i.id + ' ' + i.name).toLowerCase().includes(q)) : this.items; },
                        pick(v) { this.value = v; this.open = false; this.q = ''; } }"
                    @reset.window="value = ''; open = false; q = ''" @click.outside="open = false" @keydown.escape.window="open = false">
                    <input type="hidden" id="report_cpny" name="cpny_id" :value="value">
                    <button type="button" @click="open = !open; if (open) $nextTick(() => $refs.search.focus())" :aria-expanded="open"
                        class="flex w-full items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-left text-sm transition hover:border-indigo-300 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                        :class="open && 'border-indigo-500 ring-2 ring-indigo-500/20'">
                        <span class="flex min-w-0 items-center gap-2">
                            <x-heroicon-o-building-office-2 class="h-4 w-4 shrink-0 text-gray-400" />
                            <template x-if="label">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="rounded-md bg-indigo-50 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300" x-text="label.id"></span>
                                    <span class="truncate" x-text="label.name"></span>
                                </span>
                            </template>
                            <span x-show="!label" class="text-gray-500 dark:text-gray-400">Semua Company</span>
                        </span>
                        <x-heroicon-o-chevron-down class="h-4 w-4 shrink-0 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
                    </button>
                    <div x-show="open" x-cloak x-transition.origin.top
                        class="absolute z-30 mt-2 w-full min-w-[300px] rounded-xl border border-gray-200 bg-white p-2 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                        <div class="relative mb-2">
                            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-2.5 top-2.5 h-4 w-4 text-gray-400" />
                            <input x-ref="search" x-model="q" type="text" placeholder="Cari ID atau nama company..."
                                class="w-full rounded-lg border-gray-200 py-2 pl-8 pr-3 text-sm focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        </div>
                        <div class="max-h-64 space-y-0.5 overflow-y-auto pr-1">
                            <button type="button" @click="pick('')" x-show="!q"
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm transition"
                                :class="value === '' ? 'bg-indigo-50 font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700'">
                                Semua Company
                            </button>
                            <template x-for="item in filtered" :key="item.id">
                                <button type="button" @click="pick(item.id)"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm transition"
                                    :class="value === item.id ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-indigo-50 dark:text-gray-300 dark:hover:bg-gray-700'">
                                    <span class="w-12 shrink-0 text-xs font-bold" :class="value === item.id ? 'text-white' : 'text-indigo-600 dark:text-indigo-300'" x-text="item.id"></span>
                                    <span class="truncate" x-text="item.name || '-'"></span>
                                </button>
                            </template>
                            <p x-show="filtered.length === 0" class="px-3 py-4 text-center text-xs text-gray-400">Tidak ada company yang cocok</p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <label for="report_vendor" class="text-xs text-gray-500 dark:text-gray-400">Nama Vendor</label>
                <div class="relative mt-1"
                    x-data="{ open: false, q: '', items: [], loading: false, active: -1, timer: null,
                        search() {
                            clearTimeout(this.timer);
                            this.timer = setTimeout(async () => {
                                this.loading = true;
                                try {
                                    const params = new URLSearchParams({ q: this.q, cpny_id: document.getElementById('report_cpny').value });
                                    const res = await fetch(@js(route('report.kontrak.vendors')) + '?' + params, { headers: { Accept: 'application/json' } });
                                    this.items = res.ok ? await res.json() : [];
                                } catch (e) { this.items = []; }
                                this.active = -1; this.loading = false; this.open = true;
                            }, 250);
                            window.dispatchEvent(new CustomEvent('vendor-typed'));
                        },
                        pick(name) { this.q = name; this.open = false; window.dispatchEvent(new CustomEvent('vendor-typed')); },
                        parts(text) {
                            const t = this.q.trim();
                            const i = t ? text.toLowerCase().indexOf(t.toLowerCase()) : -1;
                            return i < 0 ? [text, '', ''] : [text.slice(0, i), text.slice(i, i + t.length), text.slice(i + t.length)];
                        } }"
                    @reset.window="q = ''; items = []; open = false" @click.outside="open = false" @keydown.escape.window="open = false">
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                    <input id="report_vendor" name="vendor" type="text" maxlength="255" autocomplete="off" placeholder="Cari nama vendor..."
                        x-model="q" @input="search()" @focus="search()"
                        @keydown.arrow-down.prevent="active = Math.min(active + 1, items.length - 1)"
                        @keydown.arrow-up.prevent="active = Math.max(active - 1, 0)"
                        @keydown.enter="if (open && active >= 0) { $event.preventDefault(); pick(items[active].vendorname); }"
                        class="w-full rounded-lg border-gray-200 py-2 pl-9 pr-9 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    <button type="button" x-show="q" x-cloak @click="q = ''; items = []; open = false; window.dispatchEvent(new CustomEvent('vendor-typed'))"
                        class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600" aria-label="Hapus">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                    <div x-show="open && (items.length || !loading)" x-cloak x-transition.origin.top
                        class="absolute z-30 mt-2 w-full min-w-[280px] rounded-xl border border-gray-200 bg-white p-1.5 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                        <template x-for="(item, i) in items" :key="item.vendorname">
                            <button type="button" @click="pick(item.vendorname)" @mouseenter="active = i"
                                class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm transition"
                                :class="active === i ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300'">
                                <span class="truncate"><span x-text="parts(item.vendorname)[0]"></span><mark class="rounded bg-indigo-100 px-0.5 text-indigo-800 dark:bg-indigo-500/30 dark:text-indigo-200" x-show="parts(item.vendorname)[1]" x-text="parts(item.vendorname)[1]"></mark><span x-text="parts(item.vendorname)[2]"></span></span>
                                <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-500 dark:bg-gray-700 dark:text-gray-400" x-text="item.total + ' kontrak'"></span>
                            </button>
                        </template>
                        <p x-show="!items.length" class="px-3 py-4 text-center text-xs text-gray-400">Vendor tidak ditemukan</p>
                    </div>
                </div>
            </div>
            <div>
                <label for="report_year" class="text-xs text-gray-500 dark:text-gray-400">Tahun</label>
                <div class="relative mt-1" x-data="{ open: false, value: '' }" @reset.window="value = ''; open = false"
                    @click.outside="open = false" @keydown.escape.window="open = false">
                    <input type="hidden" id="report_year" name="year" :value="value">
                    <button type="button" @click="open = !open" :aria-expanded="open"
                        class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2 text-left text-sm transition hover:border-indigo-300 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                        :class="open && 'border-indigo-500 ring-2 ring-indigo-500/20'">
                        <span class="flex items-center gap-2">
                            <x-heroicon-o-calendar-days class="h-4 w-4 text-gray-400" />
                            <span x-text="value || 'Semua Tahun'" :class="value ? '' : 'text-gray-500 dark:text-gray-400'"></span>
                        </span>
                        <x-heroicon-o-chevron-down class="h-4 w-4 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
                    </button>
                    <div x-show="open" x-cloak x-transition.origin.top
                        class="absolute z-30 mt-2 w-full min-w-[240px] rounded-xl border border-gray-200 bg-white p-3 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                        <button type="button" @click="value = ''; open = false"
                            class="mb-2 w-full rounded-lg px-3 py-1.5 text-left text-xs font-medium transition"
                            :class="value === '' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700'">
                            Semua Tahun
                        </button>
                        <div class="grid max-h-56 grid-cols-3 gap-1.5 overflow-y-auto pr-1">
                            @foreach ($years as $y)
                                <button type="button" @click="value = '{{ $y }}'; open = false"
                                    class="rounded-lg px-2 py-2 text-sm transition"
                                    :class="value === '{{ $y }}' ? 'bg-indigo-600 font-semibold text-white' : '{{ $y }}' === '{{ now()->year }}' ? 'text-indigo-600 ring-1 ring-indigo-200 hover:bg-indigo-50 dark:text-indigo-300 dark:ring-indigo-500/40 dark:hover:bg-gray-700' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-gray-300 dark:hover:bg-gray-700'">
                                    {{ $y }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 md:col-span-4">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-gray-700">
                    <x-heroicon-o-funnel class="h-4 w-4" /> Apply
                </button>
                <button type="reset" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <x-heroicon-o-arrow-path class="h-4 w-4" /> Reset
                </button>
                <button type="button" id="kontrakReportExport" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900 dark:text-emerald-200">
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" /> Export Excel
                </button>
            </div>
        </form>

        <p id="kontrakReportError" role="alert" class="hidden px-4 text-sm text-red-600 dark:text-red-400"></p>

        <div id="kontrakReportCard" class="bg-white p-4 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table id="kontrakReportTable" class="w-full text-gray-700 dark:text-gray-300">
                    <thead>
                        <tr>
                            <th>Kontrak ID</th>
                            <th>Cpny</th>
                            <th>Nama Vendor</th>
                            <th>Pekerjaan</th>
                            <th>Periode Kontrak</th>
                            <th>Tahun</th>
                            @foreach ($months as $month)
                                <th>{{ $month }}</th>
                            @endforeach
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            const form = document.getElementById('kontrakReportFilters');
            const error = document.getElementById('kontrakReportError');
            let filters = Object.fromEntries(new FormData(form));
            const escapeHtml = value => $('<div>').text(value ?? '').html();
            const textColumn = $.fn.dataTable.render.text();
            const columns = [
                {
                    data: 'kontrakid', name: 'k.kontrakid', className: 'contract-number',
                    render(data, type, row) {
                        if (type !== 'display') return data;
                        return `<a href="${@json(url('/showkontrak'))}/${encodeURIComponent(row.kontrak_hash)}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline dark:text-indigo-400">${escapeHtml(data)}</a>`;
                    }
                },
                {
                    data: 'cpny_id', name: 'k.cpny_id',
                    render: (data, type) => type !== 'display' ? data : `<span class="kr-cpny">${escapeHtml(data)}</span>`
                },
                { data: 'vendorname', name: 'k.vendorname', defaultContent: '-', render: textColumn },
                {
                    data: 'keperluan', name: 'k.keperluan', defaultContent: '-', className: 'contract-job', width: '380px',
                    render(data, type) {
                        if (type !== 'display') return data;
                        return `<div title="${escapeHtml(data).replace(/"/g, '&quot;')}">${escapeHtml(data)}</div>`;
                    }
                },
                { data: 'period', orderable: false, render: textColumn },
                { data: 'year', name: 'year', defaultContent: '-', render: textColumn }
            ];
            for (let month = 1; month <= 12; month++) {
                columns.push({
                    data: `month_${month}`, orderable: false, defaultContent: '', className: 'contract-month', width: '130px',
                    render(data, type, row) {
                        if (type !== 'display') return data;
                        return (data || '').split('\n').filter(Boolean).map(payment => {
                            const separator = payment.indexOf(' - ');
                            const number = separator < 0 ? payment : payment.slice(0, separator);
                            const status = separator < 0 ? '' : payment.slice(separator + 3);
                            const hash = row.rfp_ids?.[number];
                            const label = hash
                                ? `<a href="${@json(url('/showrfp'))}/${encodeURIComponent(hash)}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline dark:text-indigo-400">${escapeHtml(number)}</a>`
                                : escapeHtml(number);
                            const s = status.toLowerCase();
                            const tone = /complete|paid|approved|done/.test(s) ? 'ok'
                                : /hold|pending|wait|progress/.test(s) ? 'hold'
                                : /reject|cancel|void|fail/.test(s) ? 'bad' : '';
                            const badge = status ? `<span class="kr-badge ${tone}">${escapeHtml(status)}</span>` : '';
                            return `<div class="contract-payment"><div class="whitespace-nowrap font-medium">${label}</div>${badge}</div>`;
                        }).join('');
                    }
                });
            }

            const table = $('#kontrakReportTable').DataTable({
                processing: true, serverSide: true, searching: false, responsive: false, autoWidth: false,
                pageLength: 10, lengthMenu: [10, 25, 50, 100],
                order: [[0, 'asc'], [5, 'asc']], columns,
                ajax: {
                    url: @json(route('report.kontrak.json')),
                    data: data => Object.assign(data, filters),
                    error(xhr) {
                        error.textContent = xhr.responseJSON?.message || 'Report gagal dimuat. Silakan coba kembali.';
                        error.classList.remove('hidden');
                        $('#kontrakReportTable_processing').hide();
                    }
                }
            });

            form.addEventListener('submit', event => {
                event.preventDefault();
                filters = Object.fromEntries(new FormData(form));
                error.classList.add('hidden');
                table.ajax.reload();
            });
            // Live filter: reload the table shortly after the vendor text changes.
            let vendorTimer;
            window.addEventListener('vendor-typed', () => {
                clearTimeout(vendorTimer);
                vendorTimer = setTimeout(() => {
                    filters = Object.fromEntries(new FormData(form));
                    error.classList.add('hidden');
                    table.ajax.reload();
                }, 450);
            });
            form.addEventListener('reset', () => {
                filters = {};
                error.classList.add('hidden');
                table.ajax.reload();
            });
            document.getElementById('kontrakReportExport').addEventListener('click', () => {
                window.location.href = @json(route('report.kontrak.export')) + '?' + new URLSearchParams(filters);
            });
        });
    </script>
</x-app-layout>
