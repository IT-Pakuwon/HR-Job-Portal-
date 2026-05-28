@props(['tr_approval', 'doctypes'])

<div class="col-span-12 col-span-full rounded-xl bg-white p-4 dark:bg-gray-800">
    <!-- Tabs -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap text-center text-xs font-medium" id="approvalTabs" role="tablist">

            <!-- Waiting Approval -->
            <li class="mr-2">
                <button id="tab-waiting" type="button" role="tab" aria-controls="content-waiting" aria-selected="true"
                    onclick="switchTab('waiting')"
                    class="inline-block rounded-t-lg border-b-2 border-violet-600 p-2 text-violet-600 dark:border-violet-400 dark:text-violet-400">
                    Waiting Approval
                </button>
            </li>

            <!-- Approved -->
            <li class="mr-2">
                <button id="tab-approved" type="button" role="tab" aria-controls="content-approved"
                    aria-selected="false" onclick="switchTab('approved')"
                    class="rounded-t-l inline-block border-b-2 border-transparent p-2 text-gray-500 hover:border-gray-300 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                    Approval
                </button>
            </li>

        </ul>
    </div>

    <!-- Tab Content: Waiting -->
    <div id="content-waiting" class="tab-content">
        <div class="rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

            <div class="grid grid-cols-1 items-center gap-4 lg:grid-cols-3">

                <!-- LEFT -->
                <div class="flex items-center gap-4">

                    <!-- Icon -->
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg
                        bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                        📝
                    </div>

                    <!-- Text -->
                    <div class="flex flex-col">

                        <!-- Title + Badge -->
                        <div class="flex items-center gap-2">
                            <h1 class="text-sm font-semibold text-gray-800 dark:text-white">
                                Waiting Approval
                            </h1>

                            <!-- Total Badge -->
                            <span id="waitingTotal"
                                class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-700
                                dark:bg-indigo-900/40 dark:text-indigo-300">
                                0 documents
                            </span>
                        </div>

                        <!-- Subtitle -->
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                            See what’s your task for today
                        </p>

                    </div>

                    <button id="openAllWaiting"
                        class="ml-2 inline-flex items-center gap-2 rounded-lg
                        bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600
                        px-3 py-2 text-xs font-semibold text-white
                        shadow transition hover:scale-[1.03]">
                        🚀 Open All Document
                    </button>
                </div>

                <!-- CENTER (REAL CENTERED + PROMINENT) -->
                <div class="flex justify-center">
                    <div class="flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-1.5
                        text-xs font-medium text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">

                        ⏱ Refresh in
                        <span id="waitingCountdown" class="font-bold">
                            00:13
                        </span>

                        <span id="waitingLastUpdated" class="ml-2 text-[10px] text-gray-400">
                            • just now
                        </span>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="flex items-center justify-end gap-2">

                    <select id="waitingDoctype"
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs
                        text-gray-700 shadow-sm focus:ring-1 focus:ring-indigo-400
                        dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="ALL">All Doctype</option>
                        @foreach(($doctypes ?? collect()) as $dt)
                            <option value="{{ $dt->doctype }}">
                                {{ $dt->doctype }}{{ $dt->doctype_descr ? ' - '.$dt->doctype_descr : '' }}
                            </option>
                        @endforeach
                    </select>

                    <input id="waitingSearch" type="text" placeholder="Search..."
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs
                        text-gray-700 shadow-sm focus:ring-1 focus:ring-indigo-400
                        dark:border-gray-600 dark:bg-gray-700 dark:text-white" />

                </div>

            </div>
        </div>
        <div class="mt-4 overflow-x-auto rounded-lg bg-white dark:bg-gray-800">
            <div id="waitingTableContainer"></div>
        </div>
    </div>

    <!-- Tab Content: Approved -->
    <div id="content-approved" class="tab-content hidden">
        <div class="rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

            <div class="grid grid-cols-1 items-center gap-4 lg:grid-cols-3">

                <!-- LEFT -->
                <div class="flex items-center gap-4">

                    <!-- Icon -->
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg
                        bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300">
                        ✅
                    </div>

                    <!-- Text -->
                    <div class="flex flex-col">

                        <!-- Title -->
                        <div class="flex items-center gap-2">
                            <h1 class="text-sm font-semibold text-gray-800 dark:text-white">
                                Approval
                            </h1>

                            <!-- Total Badge -->
                            <span id="approvedTotal"
                                class="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700
                                dark:bg-green-900/40 dark:text-green-300">
                                0 documents
                            </span>
                        </div>

                        <!-- Subtitle -->
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                            Track your approved documents
                        </p>

                    </div>

                </div>

                <!-- CENTER (TIMER) -->
                <div class="flex justify-center">
                    <div class="flex items-center gap-2 rounded-full bg-green-50 px-4 py-1.5
                        text-xs font-medium text-green-600 dark:bg-green-900/40 dark:text-green-300">

                        ⏱ Refresh in
                        <span id="approvedCountdown" class="font-bold">
                            00:20
                        </span>

                        <span id="approvedLastUpdated" class="ml-2 text-[10px] text-gray-400">
                            • just now
                        </span>
                    </div>
                </div>

                <!-- RIGHT (FILTERS) -->
                <div class="flex items-center justify-end gap-2">

                    <select id="approvedDoctype"
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs
                        text-gray-700 shadow-sm focus:ring-1 focus:ring-green-400
                        dark:border-gray-600 dark:bg-gray-700 dark:text-white">

                        <option value="ALL">All Doctype</option>
                        @foreach(($doctypes ?? collect()) as $dt)
                            <option value="{{ $dt->doctype }}">
                                {{ $dt->doctype }}{{ $dt->doctype_descr ? ' - '.$dt->doctype_descr : '' }}
                            </option>
                        @endforeach
                    </select>

                    <input id="approvedSearch" type="text" placeholder="Search..."
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs
                        text-gray-700 shadow-sm focus:ring-1 focus:ring-green-400
                        dark:border-gray-600 dark:bg-gray-700 dark:text-white" />

                </div>

            </div>

        </div>
        <div class="mt-4 overflow-x-auto rounded-lg bg-white dark:bg-gray-800">
            <div id="approvedTableContainer"></div>
        </div>
    </div>
</div>
<script>
    class DataTable {
    constructor(config) {
        this.el = document.querySelector(config.selector);
        this.columns = config.columns;
        this.data = config.data || [];
        this.perPage = config.perPage || 10;

        this.currentPage = 1;
        this.search = '';
        this.sortColumn = 'docdate';
        this.sortDirection = 'desc'; // 🔥 newest first

        this.id = config.id; // 🔥 unique id per table

        this.init();
    }

    init() {
        this.render();
    }

    setData(data) {
        this.data = data;
        this.currentPage = 1;
        this.render();
    }

    handleSort(column) {
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = column;
            this.sortDirection = 'asc';
        }
        this.render();
    }

    handleSearch(val) {
        this.search = val.toLowerCase();
        this.currentPage = 1;
        this.render();
    }

    getProcessedData() {
        let filtered = [...this.data];

        if (this.search) {
            filtered = filtered.filter(row =>
                this.columns.some(col => {
                    const val = row[col.key];
                    return val && val.toString().toLowerCase().includes(this.search);
                })
            );
        }

        if (this.sortColumn) {
            filtered.sort((a, b) => {
                let valA = a[this.sortColumn] ?? '';
                let valB = b[this.sortColumn] ?? '';

                if (this.sortColumn.toLowerCase().includes('date')) {
                    valA = new Date(valA);
                    valB = new Date(valB);
                } else {
                    valA = valA.toString().toLowerCase();
                    valB = valB.toString().toLowerCase();
                }

                if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1;
                if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        }

        return filtered;
    }

    paginate(data) {
        const start = (this.currentPage - 1) * this.perPage;
        return data.slice(start, start + this.perPage);
    }

    render() {
        const processed = this.getProcessedData();
        const paged = this.paginate(processed);
        const totalPages = Math.ceil(processed.length / this.perPage) || 1;

        this.el.innerHTML = `

            <table class="w-full text-xs border">
                <thead class="bg-gray-200">
                    <tr>
                        ${this.columns.map(col => `
                            <th onclick="${this.id}.handleSort('${col.key}')"
                                class="cursor-pointer px-3 py-2 text-${col.align || 'left'}">
                                ${col.label}
                                ${this.sortColumn === col.key
                                    ? (this.sortDirection === 'asc' ? '↑' : '↓')
                                    : '↕'}
                            </th>
                        `).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${paged.length
                        ? paged.map(row => `
                            <tr class="border-t hover:bg-gray-50">
                                ${this.columns.map(col => `
                                    <td class="px-3 py-2 text-${col.align || 'left'}">
                                        ${col.render
                                            ? col.render(row[col.key], row)
                                            : (row[col.key] ?? '-')}
                                    </td>
                                `).join('')}
                            </tr>
                        `).join('')
                        : `<tr><td colspan="${this.columns.length}" class="text-center py-4">No data</td></tr>`
                    }
                </tbody>
            </table>

            <div class="flex items-center justify-between mt-3">

                <!-- Prev -->
                <button
                    ${this.currentPage === 1 ? 'disabled' : ''}
                    onclick="${this.id}.prevPage()"
                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300
                    bg-white px-3 py-1.5 text-xs font-medium text-gray-700
                    shadow-sm transition hover:bg-gray-100 hover:shadow
                    disabled:opacity-40 disabled:cursor-not-allowed">
                    ← Prev
                </button>

                <!-- Page Info CENTER -->
                <div class="text-xs font-medium text-gray-600">
                    Page ${this.currentPage} of ${totalPages}
                </div>

                <!-- Next -->
                <button
                    ${this.currentPage === totalPages ? 'disabled' : ''}
                    onclick="${this.id}.nextPage(${totalPages})"
                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300
                    bg-white px-3 py-1.5 text-xs font-medium text-gray-700
                    shadow-sm transition hover:bg-gray-100 hover:shadow
                    disabled:opacity-40 disabled:cursor-not-allowed">
                    Next →
                </button>

            </div>
        `;
    }
    nextPage(total) {
        if (this.currentPage < total) {
            this.currentPage++;
            this.render();
        }
    }

    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.render();
        }
    }
}

    function switchTab(tab) {
        const tabs = ['waiting', 'approved'];

        tabs.forEach(name => {
            const btn = document.getElementById(`tab-${name}`);
            const content = document.getElementById(`content-${name}`);

            if (!btn || !content) return;

            if (name === tab) {
                // active tab
                btn.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                btn.classList.add('border-violet-600', 'text-violet-600', 'dark:border-violet-400', 'dark:text-violet-400');

                content.classList.remove('hidden');
            } else {
                // inactive tab
                btn.classList.remove('border-violet-600', 'text-violet-600', 'dark:border-violet-400', 'dark:text-violet-400');
                btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');

                content.classList.add('hidden');
            }
        });
    }

    document.getElementById('openAllWaiting')?.addEventListener('click', function () {

        const data = waitingTable.data || [];

        if (!data.length) {
            alert('Tidak ada dokumen untuk dibuka');
            return;
        }

        // 🔥 IMPORTANT: open immediately in same call stack
        for (let i = 0; i < data.length; i++) {
            const ap = data[i];

            if (ap.url && ap.hid) {
                window.open(`${ap.url}/${ap.hid}`, '_blank');
            }
        }

    });

        // ===============================
    // AUTO REFRESH + COUNTDOWN (20s)
    // ===============================
    const REFRESH_INTERVAL_SEC = 20; // 20 detik
    let secLeft = REFRESH_INTERVAL_SEC;
    let tickTimer = null;
    let refreshing = false;

    function formatMMSS(sec) {
        const m = String(Math.floor(sec / 60)).padStart(2, '0');
        const s = String(sec % 60).padStart(2, '0');
        return `${m}:${s}`;
    }

    function setCountdownUI() {
        const w = document.getElementById('waitingCountdown');
        const a = document.getElementById('approvedCountdown');

        // tampilkan countdown untuk dua tab (biar konsisten)
        if (w) w.innerText = formatMMSS(secLeft);
        if (a) a.innerText = formatMMSS(secLeft);
    }

    async function fetchJsonSafe(url) {
        const resp = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Accept': 'application/json' }
        });

        const ct = (resp.headers.get('content-type') || '').toLowerCase();

        if (!resp.ok) {
            const txt = await resp.text().catch(() => '');
            throw new Error(`HTTP ${resp.status} ${resp.statusText} | ${txt.slice(0, 120)}`);
        }

        // kalau ternyata HTML/redirect login
        if (!ct.includes('application/json')) {
            const txt = await resp.text().catch(() => '');
            throw new Error(`Not JSON (ct:${ct || '-'}) | body:${txt.slice(0, 120)}`);
        }

        return await resp.json();
    }

    function setLastUpdated(type) {
        const el = document.getElementById(`${type}LastUpdated`);
        if (!el) return;

        const now = new Date();
        const time = now.toLocaleTimeString();

        el.innerText = `• updated at ${time}`;
    }

    function flashTable(selector) {
        const el = document.querySelector(selector);
        if (!el) return;

        el.classList.add('ring-2', 'ring-indigo-300');
        setTimeout(() => {
            el.classList.remove('ring-2', 'ring-indigo-300');
        }, 500);
    }

    async function refreshWaitingNow() {
        const dt = (document.getElementById('waitingDoctype')?.value) || 'ALL';
        const url = `/waitingjson?doctype=${encodeURIComponent(dt)}&t=${Date.now()}`;

        const json = await fetchJsonSafe(url);

        const data = json.data || [];

        waitingTable.setData(data);
        setLastUpdated('waiting');
        setTotal('waiting', data.length); // ✅ ADD
    }

    async function refreshApprovedNow() {
        const dt = (document.getElementById('approvedDoctype')?.value) || 'ALL';
        const url = `/approvejson?doctype=${encodeURIComponent(dt)}&t=${Date.now()}`;

        const json = await fetchJsonSafe(url);
        const data = json.data || [];

        approvedTable.setData(data);
        setLastUpdated('approved');
        setTotal('approved', data.length); // ✅ ADD THIS
    }

    async function refreshActiveTabNow() {
        if (document.hidden) return;
        if (refreshing) return;

        const activeTab = document.querySelector('.tab-content:not(.hidden)');
        if (!activeTab) return;

        refreshing = true;
        try {
            if (activeTab.id === 'content-waiting') {
                console.log('🔄 Auto refresh: Waiting');
                await refreshWaitingNow();
            } else if (activeTab.id === 'content-approved') {
                console.log('🔄 Auto refresh: Approved');
                await refreshApprovedNow();
            }
        } catch (e) {
            console.error('❌ Auto refresh failed:', e.message || e);
        } finally {
            refreshing = false;
        }
    }

    function startCountdown() {
        if (tickTimer) clearInterval(tickTimer);

        secLeft = REFRESH_INTERVAL_SEC;
        setCountdownUI();

        tickTimer = setInterval(async () => {
            if (document.hidden) return;

            secLeft--;
            if (secLeft <= 0) {
                secLeft = REFRESH_INTERVAL_SEC;
                setCountdownUI();
                await refreshActiveTabNow();
            } else {
                setCountdownUI();
            }
        }, 1000);
    }

    // start
    startCountdown();

    // OPTIONAL: refresh sekali saat user balik ke tab browser
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            // reset countdown supaya user lihat bergerak lagi
            secLeft = REFRESH_INTERVAL_SEC;
            setCountdownUI();
            refreshActiveTabNow();
        }
    });

    function setTotal(type, count) {
        const el = document.getElementById(`${type}Total`);
        if (!el) return;

        el.innerText = `${count} document${count !== 1 ? 's' : ''}`;
    }

    const waitingTable = new DataTable({
        id: 'waitingTable',
        selector: '#waitingTableContainer',
        perPage: 10,
        columns: [
            {
                key: 'docid',
                label: 'DocID',
                align: 'center',
                render: (val, row) => {
                    if (!row.url || !row.hid) return val ?? '-';

                    const href = `${row.url}/${row.hid}`;

                    return `
                        <a href="${href}" target="_blank"
                        class="inline-block rounded-md bg-blue-500 px-3 py-1 text-white text-xs
                        transition hover:bg-blue-600">
                            ${val}
                        </a>
                    `;
                }
            },
            { key: 'docdate', label: 'Date', align: 'center' },
            { key: 'cpnyid', label: 'Company', align: 'center' },
            { key: 'departementid', label: 'Department' },
            { key: 'infohd', label: 'Info' }
        ]
    });

    const approvedTable = new DataTable({
        id: 'approvedTable',
    selector: '#approvedTableContainer',// 🔥 using existing table
        perPage: 10,
        columns: [
            {
                key: 'docid',
                label: 'DocID',
                align: 'center',
                render: (val, row) => {
                    if (!row.url || !row.hid) return val ?? '-';

                    const href = `${row.url}/${row.hid}`;

                    return `
                        <a href="${href}" target="_blank"
                        class="inline-block rounded-md bg-blue-500 px-3 py-1 text-white text-xs
                        transition hover:bg-blue-600">
                            ${val}
                        </a>
                    `;
                }
            },
            { key: 'docdate', label: 'Date', align: 'center' },
            { key: 'cpnyid', label: 'Company', align: 'center' },
            { key: 'departementid', label: 'Department' },
            { key: 'infohd', label: 'Info' }
        ]
    });

    window.waitingTable = waitingTable;
    window.approvedTable = approvedTable;

    fetch('/waitingjson')
        .then(res => res.json())
        .then(res => {
            const data = res.data || [];
            waitingTable.setData(data);
            setTotal('waiting', data.length); // ✅ ADD THIS
        });

    fetch('/approvejson')
        .then(res => res.json())
        .then(res => {
            const data = res.data || [];
            approvedTable.setData(data);
            setTotal('approved', data.length); // ✅ ADD
        });

    document.getElementById('waitingSearch')?.addEventListener('input', function(e) {
        waitingTable.handleSearch(e.target.value);
    });

    document.getElementById('approvedSearch')?.addEventListener('input', function(e) {
        approvedTable.handleSearch(e.target.value);
    });
</script>
