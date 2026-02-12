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
        <div class="flex flex-col">
            <div class="flex flex-col justify-between gap-4 sm:flex-row">
                <div>
                    <h1 class="text-lg font-bold dark:text-white">📝 Waiting Approval</h1>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-gray-500 dark:text-gray-300">Auto refresh in</span>
                    <span id="waitingCountdown"
                        class="rounded-md bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-700 dark:bg-gray-700 dark:text-white">
                        01:00
                    </span>
                </div>
                <div class="flex gap-2">
                    <select id="waitingDoctype"
                        class="rounded-md border bg-gray-100 px-3 py-2 text-xs text-gray-700 dark:bg-gray-700 dark:text-white">
                        <option value="ALL">ALL Doctype</option>

                        @foreach(($doctypes ?? collect()) as $dt)
                            <option value="{{ $dt->doctype }}">
                            {{ $dt->doctype }}{{ $dt->doctype_descr ? ' - '.$dt->doctype_descr : '' }}
                            </option>
                        @endforeach
                    </select>


                    <input id="waitingSearch" type="text" placeholder="Search..."
                        class="rounded-md border bg-gray-100 px-3 py-2 text-xs text-gray-700 dark:bg-gray-700 dark:text-white" />
                </div>               
            </div>
            <div>
                <p class="text-m ml-8 dark:text-white">See what's your task for today!</p>
            </div>
        </div>
        <div class="mt-4 overflow-x-auto rounded-lg bg-white dark:bg-gray-800">
            <table class="w-full min-w-full rounded" id="waitingTable">
                <thead class="bg-gray-200 text-xs text-gray-700 dark:bg-gray-700 dark:text-white">
                    <tr>
                        <th class="px-4 py-2 text-center uppercase">DocID</th>
                        <th class="px-4 py-2 text-center uppercase">Date</th>
                        <th class="px-4 py-2 text-center uppercase">Company</th>
                        <th class="px-4 py-2 text-left uppercase">Department</th>
                        <th class="px-4 py-2 text-center uppercase">Info</th>
                    </tr>
                </thead>
                <tbody class="text-xs text-gray-800 dark:text-gray-300">
                    <!-- Data will be loaded by JS -->
                </tbody>
            </table>
            <div class="mt-2 flex items-center justify-between">
                <button id="waitingPrev"
                    class="rounded-md bg-gray-200 px-3 py-2 text-gray-700 transition hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">Previous</button>
                <span id="waitingPaginationInfo" class="text-xs text-gray-700 dark:text-white">Page 1 of 1</span>
                <button id="waitingNext"
                    class="rounded-md bg-gray-200 px-3 py-2 text-gray-700 transition hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">Next</button>
            </div>
        </div>
    </div>

    <!-- Tab Content: Approved -->
    <div id="content-approved" class="tab-content hidden">
        <div class="flex flex-col">
            <div class="flex flex-col justify-between gap-4 sm:flex-row">
                <div>
                    <h1 class="text-lg font-bold dark:text-white">✅ Approval</h1>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-gray-500 dark:text-gray-300">Auto refresh in</span>
                    <span id="approvedCountdown"
                        class="rounded-md bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-700 dark:bg-gray-700 dark:text-white">
                        00:20
                    </span>
                </div>

                <div class="flex gap-2">
                    <select id="approvedDoctype"
                        class="rounded-md border bg-gray-100 px-3 py-2 text-xs text-gray-700 dark:bg-gray-700 dark:text-white">
                        <option value="ALL">ALL Doctype</option>

                        @foreach(($doctypes ?? collect()) as $dt)
                            <option value="{{ $dt->doctype }}">
                            {{ $dt->doctype }}{{ $dt->doctype_descr ? ' - '.$dt->doctype_descr : '' }}
                            </option>
                        @endforeach
                    </select>


                    <input id="approvedSearch" type="text" placeholder="Search..."
                        class="rounded-md border bg-gray-100 px-3 py-2 text-xs text-gray-700 dark:bg-gray-700 dark:text-white" />
                </div>
            </div>
            <div>
                <p class="text-m ml-8 dark:text-white">Track your approved documents here!</p>
            </div>
        </div>
        <div class="mt-4 overflow-x-auto rounded-lg bg-white dark:bg-gray-800">
            <table class="w-full min-w-full rounded" id="approvedTable">
                <thead class="bg-gray-200 text-xs text-gray-700 dark:bg-gray-700 dark:text-white">
                    <tr>
                        <th class="px-4 py-2 text-center uppercase">DocID</th>
                        <th class="px-4 py-2 text-center uppercase">Date</th>
                        <th class="px-4 py-2 text-center uppercase">Company</th>
                        <th class="px-4 py-2 text-left uppercase">Department</th>
                        <th class="max-w-xs px-4 py-2 text-center uppercase">Info</th>
                    </tr>
                </thead>
                <tbody class="text-xs text-gray-800 dark:text-gray-300">
                    <!-- Data will be loaded by JS -->
                </tbody>
            </table>
            <div class="mt-2 flex items-center justify-between">
                <button id="approvedPrev"
                    class="rounded-md bg-gray-200 px-3 py-2 text-gray-700 transition hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">Previous</button>
                <span id="approvedPaginationInfo" class="text-xs text-gray-700 dark:text-white">Page 1 of 1</span>
                <button id="approvedNext"
                    class="rounded-md bg-gray-200 px-3 py-2 text-gray-700 transition hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">Next</button>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        const tabs = ['waiting', 'approved'];

        tabs.forEach(name => {
            const btn = document.getElementById(`tab-${name}`);
            const content = document.getElementById(`content-${name}`);
            if (!btn || !content) return;

            if (name === tab) {
                btn.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                btn.classList.add('border-violet-600', 'text-violet-600', 'dark:border-violet-400',
                    'dark:text-violet-400');
                content.classList.remove('hidden');
            } else {
                btn.classList.remove('border-violet-600', 'text-violet-600', 'dark:border-violet-400',
                    'dark:text-violet-400');
                btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                content.classList.add('hidden');
            }
        });

        // reset timer setiap ganti tab
        secLeft = REFRESH_INTERVAL_SEC;
        setCountdownUI();
        refreshActiveTabNow(); // optional: langsung refresh tab yg baru dibuka

    }

    function renderTable(data, tbodySelector, page, perPage, searchValue) {
        const tbody = document.querySelector(tbodySelector);
        tbody.innerHTML = '';

        let filtered = data;
        if (searchValue) {
            const val = searchValue.toLowerCase();
            filtered = data.filter(ap =>
                (ap.docid && ap.docid.toLowerCase().includes(val)) ||
                (ap.docdate && ap.docdate.toLowerCase().includes(val)) ||
                (ap.cpnyid && ap.cpnyid.toLowerCase().includes(val)) ||
                (ap.departementid && ap.departementid.toLowerCase().includes(val)) ||
                (ap.infohd && ap.infohd.toLowerCase().includes(val))
            );
        }

        const total = filtered.length;
        const maxPage = Math.max(1, Math.ceil(total / perPage));
        const safePage = Math.min(Math.max(1, page), maxPage);

        const start = (safePage - 1) * perPage;
        const end = start + perPage;
        const paged = filtered.slice(start, end);

        if (paged.length > 0) {
            paged.forEach(ap => {
                const href = `${ap.url}/${ap.hid}`;
                tbody.innerHTML += `
                    <tr class="border-b border-gray-200 transition hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                        <td class="whitespace-nowrap p-3 text-center">
                            <a href="${href}" target="_blank"
                               class="rounded-md bg-blue-500 px-3 py-1 text-white transition hover:bg-blue-600">${ap.docid}</a>
                        </td>
                        <td class="px-4 py-2 text-center break-words whitespace-normal max-w-xs">${ap.docdate ?? '-'}</td>
                        <td class="px-4 py-2 text-center break-words whitespace-normal max-w-xs">${ap.cpnyid ?? '-'}</td>
                        <td class="px-4 py-2 text-left break-words whitespace-normal max-w-xs">${ap.departementid ?? '-'}</td>
                        <td class="px-4 py-2 text-left break-words whitespace-normal max-w-xs">${ap.infohd ?? '-'}</td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center">No data found.</td></tr>';
        }

        return {
            total,
            filteredCount: total,
            page: safePage,
            maxPage
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        let waitingData = [];
        let approvedData = [];

        let waitingPage = 1;
        let approvedPage = 1;

        const perPage = 10;
        let waitingSearch = '';
        let approvedSearch = '';

        // doctype filter value
        let waitingDoctype = (document.getElementById('waitingDoctype')?.value) || 'ALL';
        let approvedDoctype = (document.getElementById('approvedDoctype')?.value) || 'ALL';

        function updateWaitingTable() {
            const res = renderTable(waitingData, '#waitingTable tbody', waitingPage, perPage, waitingSearch);
            waitingPage = res.page;

            document.getElementById('waitingPaginationInfo').innerText =
                `Page ${res.page} of ${res.maxPage}`;

            document.getElementById('waitingPrev').disabled = (res.page <= 1);
            document.getElementById('waitingNext').disabled = (res.page >= res.maxPage);
        }

        function updateApprovedTable() {
            const res = renderTable(approvedData, '#approvedTable tbody', approvedPage, perPage,
                approvedSearch);
            approvedPage = res.page;

            document.getElementById('approvedPaginationInfo').innerText =
                `Page ${res.page} of ${res.maxPage}`;

            document.getElementById('approvedPrev').disabled = (res.page <= 1);
            document.getElementById('approvedNext').disabled = (res.page >= res.maxPage);
        }

        async function loadWaiting() {
            const dt = waitingDoctype || 'ALL';
            const url = `/waitingjson?doctype=${encodeURIComponent(dt)}`;

            const resp = await fetch(url);
            const json = await resp.json();

            waitingData = json.data || [];
            waitingPage = 1;
            updateWaitingTable();
        }

        async function loadApproved() {
            const dt = approvedDoctype || 'ALL';
            const url = `/approvejson?doctype=${encodeURIComponent(dt)}`;

            const resp = await fetch(url);
            const json = await resp.json();

            approvedData = json.data || [];
            approvedPage = 1;
            updateApprovedTable();
        }

        // initial load
        loadWaiting();
        loadApproved();
        // const activeTab = document.querySelector('.tab-content:not(.hidden)');
        // if (activeTab?.id === 'content-waiting') {
        //     loadWaiting();
        // }
        // if (activeTab?.id === 'content-approved') {
        //     loadApproved();
        // }


        // Search handlers
        document.getElementById('waitingSearch')?.addEventListener('input', function(e) {
            waitingSearch = e.target.value;
            waitingPage = 1;
            updateWaitingTable();
        });

        document.getElementById('approvedSearch')?.addEventListener('input', function(e) {
            approvedSearch = e.target.value;
            approvedPage = 1;
            updateApprovedTable();
        });

        // Doctype handlers -> reload from server
        document.getElementById('waitingDoctype')?.addEventListener('change', function(e) {
            waitingDoctype = e.target.value || 'ALL';
            loadWaiting();
        });

        document.getElementById('approvedDoctype')?.addEventListener('change', function(e) {
            approvedDoctype = e.target.value || 'ALL';
            loadApproved();
        });

        // Pagination handlers
        document.getElementById('waitingPrev')?.addEventListener('click', function() {
            if (waitingPage > 1) {
                waitingPage--;
                updateWaitingTable();
            }
        });

        document.getElementById('waitingNext')?.addEventListener('click', function() {
            waitingPage++;
            updateWaitingTable();
        });

        document.getElementById('approvedPrev')?.addEventListener('click', function() {
            if (approvedPage > 1) {
                approvedPage--;
                updateApprovedTable();
            }
        });

        document.getElementById('approvedNext')?.addEventListener('click', function() {
            approvedPage++;
            updateApprovedTable();
        });

        window.__approval = {
            get waitingData() { return waitingData; },
            set waitingData(v) { waitingData = v; },

            get approvedData() { return approvedData; },
            set approvedData(v) { approvedData = v; },

            get waitingPage() { return waitingPage; },
            set waitingPage(v) { waitingPage = v; },

            get approvedPage() { return approvedPage; },
            set approvedPage(v) { approvedPage = v; },

            updateWaitingTable,
            updateApprovedTable,
            loadWaiting,
            loadApproved
        };

        console.log('✅ window.__approval ready');


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

    

    async function refreshWaitingNow() {
        const dt = (document.getElementById('waitingDoctype')?.value) || 'ALL';
        const url = `/waitingjson?doctype=${encodeURIComponent(dt)}&t=${Date.now()}`;

        const json = await fetchJsonSafe(url);

        if (!window.__approval) {
            console.error('window.__approval belum ready');
            return;
        }

        window.__approval.waitingData = json.data || [];
        window.__approval.waitingPage = 1;
        window.__approval.updateWaitingTable();
    }


    async function refreshApprovedNow() {
        const dt = (document.getElementById('approvedDoctype')?.value) || 'ALL';
        const url = `/approvejson?doctype=${encodeURIComponent(dt)}&t=${Date.now()}`;

        const json = await fetchJsonSafe(url);

        if (!window.__approval) {
            console.error('window.__approval belum ready');
            return;
        }

        window.__approval.approvedData = json.data || [];
        window.__approval.approvedPage = 1;
        window.__approval.updateApprovedTable();
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






</script>
