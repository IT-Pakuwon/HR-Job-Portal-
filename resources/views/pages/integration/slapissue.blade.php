<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="slIssueBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
    <div class="absolute inset-0 bg-black/40 pointer-events-auto"></div>

    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div class="pointer-events-auto rounded-xl bg-white px-5 py-4 shadow-lg border border-gray-200 flex items-center gap-3">
            <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>

            <div>
                <div id="slIssueBusyTitle" class="font-semibold text-gray-800">Processing...</div>
                <div id="slIssueBusySub" class="text-sm text-gray-500">Mohon tunggu...</div>
            </div>
        </div>
    </div>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-4">
    <div class="mb-3">
        <div class="text-lg font-bold text-gray-800">📦 ISSUE Solomon (P-Solomon → C)</div>
        <div class="text-sm text-gray-500">
            Load data staging berdasarkan range tanggal, filter company/status, dan proses P → C
        </div>
    </div>

    <div id="slIssueInfo" class="hidden mt-3 rounded-md border px-4 py-3 text-sm"></div>

    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" id="slIssueFrom"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" id="slIssueTo"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Company</label>
                <select id="slIssueCompany"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Company</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                <select id="slIssueStatus"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Status</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Show</label>
                <select id="slIssuePerPage"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div class="md:col-span-2 flex items-end justify-end gap-2">
                <button type="button" id="btnLoadSlIssue"
                    class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Load
                </button>

                <button type="button" id="btnProcessSlIssue"
                    class="inline-flex items-center justify-center rounded-md bg-green-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                    Process
                </button>
            </div>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200">
        <div class="flex flex-col gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3 md:flex-row md:items-center md:justify-between">
            <div class="text-sm font-semibold text-gray-700">
                Total: <span id="slIssueTotal">0</span>
            </div>

            <div class="text-xs text-gray-600">
                P: <span id="slIssueSumP">0</span> |
                C: <span id="slIssueSumC">0</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="w-10 px-4 py-2 text-left">
                            <input type="checkbox" id="slIssueChkAll" class="rounded border-gray-300">
                        </th>
                        <th class="px-4 py-2 text-left">Integration Type</th>
                        <th class="px-4 py-2 text-left">Cpny</th>
                        <th class="px-4 py-2 text-left">Issue ID</th>
                        <th class="px-4 py-2 text-left">Issue Date</th>
                        <th class="px-4 py-2 text-left">Dept</th>
                        <th class="px-4 py-2 text-left">Peminta</th>
                        <th class="px-4 py-2 text-left">WOID</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-left">Created</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2 text-left">Response</th>
                        <th class="px-4 py-2 text-left">Last Update</th>
                    </tr>
                </thead>

                <tbody id="slIssueTbody" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="13" class="px-4 py-10 text-center text-gray-500">Belum ada data. Klik Load.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 bg-white px-4 py-3 md:flex-row md:items-center md:justify-between">
            <div id="slIssuePageInfo" class="text-xs text-gray-500">
                Showing 0 to 0 of 0 entries
            </div>
            <div id="slIssuePagination" class="flex flex-wrap items-center gap-1"></div>
        </div>

        <div class="px-4 py-3 text-xs text-gray-500 bg-white border-t border-gray-100">
            Legend: P = ready process, C = completed.
        </div>
    </div>
</div>

<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const el = {
        overlay: document.getElementById('slIssueBusyOverlay'),
        busyTitle: document.getElementById('slIssueBusyTitle'),
        busySub: document.getElementById('slIssueBusySub'),

        from: document.getElementById('slIssueFrom'),
        to: document.getElementById('slIssueTo'),
        company: document.getElementById('slIssueCompany'),
        status: document.getElementById('slIssueStatus'),
        perPage: document.getElementById('slIssuePerPage'),

        btnLoad: document.getElementById('btnLoadSlIssue'),
        btnProcess: document.getElementById('btnProcessSlIssue'),

        info: document.getElementById('slIssueInfo'),
        total: document.getElementById('slIssueTotal'),
        sumP: document.getElementById('slIssueSumP'),
        sumC: document.getElementById('slIssueSumC'),

        chkAll: document.getElementById('slIssueChkAll'),
        tbody: document.getElementById('slIssueTbody'),
        pageInfo: document.getElementById('slIssuePageInfo'),
        pagination: document.getElementById('slIssuePagination'),
    };

    if (!el.from || !el.to || !el.company || !el.status || !el.perPage || !el.btnLoad || !el.btnProcess || !el.tbody) return;

    let isBusy = false;
    let currentPage = 1;

    function hideInfo() {
        el.info.classList.add('hidden');
        el.info.textContent = '';
        el.info.className = 'hidden mt-3 rounded-md border px-4 py-3 text-sm';
    }

    function setInfo(type, msg) {
        el.info.classList.remove('hidden');
        el.info.textContent = msg;
        el.info.className = 'mt-3 rounded-md border px-4 py-3 text-sm';

        if (type === 'ok') {
            el.info.classList.add('bg-green-50', 'border-green-200', 'text-green-800');
        } else if (type === 'warn') {
            el.info.classList.add('bg-yellow-50', 'border-yellow-200', 'text-yellow-800');
        } else {
            el.info.classList.add('bg-red-50', 'border-red-200', 'text-red-800');
        }
    }

    function setBusy(busy, title = 'Processing...', sub = 'Mohon tunggu...') {
        isBusy = busy;

        if (busy) {
            el.busyTitle.textContent = title;
            el.busySub.textContent = sub;
            el.overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            el.overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        [
            el.from, el.to, el.company, el.status, el.perPage,
            el.btnLoad, el.btnProcess, el.chkAll
        ].forEach(node => {
            if (node) node.disabled = busy;
        });

        el.tbody.querySelectorAll('.slIssueRowChk').forEach(chk => {
            if (busy) {
                chk.disabled = true;
            } else {
                const stage = String(chk.dataset.stage || '').toUpperCase();
                chk.disabled = stage !== 'P';
                if (chk.disabled) chk.checked = false;
            }
        });

        syncChkAllState();

        if (busy) {
            el.btnLoad.dataset.txt = el.btnLoad.textContent;
            el.btnProcess.dataset.txt = el.btnProcess.textContent;
            el.btnLoad.textContent = 'Loading...';
            el.btnProcess.textContent = 'Processing...';
        } else {
            if (el.btnLoad.dataset.txt) el.btnLoad.textContent = el.btnLoad.dataset.txt;
            if (el.btnProcess.dataset.txt) el.btnProcess.textContent = el.btnProcess.dataset.txt;
        }
    }

    function syncChkAllState() {
        const enabled = Array.from(el.tbody.querySelectorAll('.slIssueRowChk')).filter(chk => !chk.disabled);

        if (enabled.length === 0) {
            el.chkAll.checked = false;
            el.chkAll.indeterminate = false;
            el.chkAll.disabled = true;
            return;
        }

        el.chkAll.disabled = isBusy;
        const checkedCount = enabled.filter(chk => chk.checked).length;
        el.chkAll.checked = checkedCount === enabled.length;
        el.chkAll.indeterminate = checkedCount > 0 && checkedCount < enabled.length;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function fmtDate(value) {
        if (!value) return '';
        try {
            const d = new Date(value);
            if (isNaN(d.getTime())) return String(value);

            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            const hh = String(d.getHours()).padStart(2, '0');
            const mi = String(d.getMinutes()).padStart(2, '0');
            const ss = String(d.getSeconds()).padStart(2, '0');

            return `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;
        } catch {
            return String(value);
        }
    }

    function badge(stage) {
        const s = String(stage || '').toUpperCase();

        if (s === 'C') {
            return `<span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">C</span>`;
        }

        return `<span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">P</span>`;
    }

    function renderRows(rows) {
        if (!rows || rows.length === 0) {
            el.tbody.innerHTML = `
                <tr>
                    <td colspan="13" class="px-4 py-10 text-center text-gray-500">No data.</td>
                </tr>
            `;
            el.chkAll.checked = false;
            el.chkAll.indeterminate = false;
            el.chkAll.disabled = true;
            return;
        }

        el.tbody.innerHTML = rows.map(r => {
            const stage = String(r.stage_status ?? 'P').toUpperCase();
            const cpny = String(r.cpny_id ?? '');
            const issue = String(r.issue_id ?? '');
            const key = String(r.key ?? `${cpny}||${issue}`);
            const disabled = stage !== 'P' ? 'disabled' : '';
            const rowClass = stage === 'C' ? 'opacity-70' : '';

            return `
                <tr class="hover:bg-gray-50 ${rowClass}">
                    <td class="px-4 py-2">
                        <input type="checkbox"
                            class="slIssueRowChk rounded border-gray-300"
                            value="${escapeHtml(key)}"
                            data-stage="${escapeHtml(stage)}"
                            ${disabled}>
                    </td>
                    <td class="px-4 py-2">${escapeHtml(r.integration_type ?? '')}</td>
                    <td class="px-4 py-2">${escapeHtml(cpny)}</td>
                    <td class="px-4 py-2">${escapeHtml(issue)}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${escapeHtml(fmtDate(r.issue_date ?? ''))}</td>
                    <td class="px-4 py-2">${escapeHtml(r.department_id ?? '')}</td>
                    <td class="px-4 py-2">${escapeHtml(r.user_peminta ?? '')}</td>
                    <td class="px-4 py-2">${escapeHtml(r.wo_id ?? '')}</td>
                    <td class="px-4 py-2 text-right">${escapeHtml(r.total_record ?? 0)}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${escapeHtml(fmtDate(r.created_at ?? ''))}</td>
                    <td class="px-4 py-2 text-center">${badge(stage)}</td>
                    <td class="px-4 py-2">${escapeHtml(r.payload_response ?? '')}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${escapeHtml(fmtDate(r.last_update ?? ''))}</td>
                </tr>
            `;
        }).join('');

        el.tbody.querySelectorAll('.slIssueRowChk').forEach(chk => {
            chk.addEventListener('change', syncChkAllState);
        });

        syncChkAllState();
    }

    function renderSummary(summary = {}) {
        el.total.textContent = String(summary.total ?? 0);
        el.sumP.textContent = String(summary.P ?? 0);
        el.sumC.textContent = String(summary.C ?? 0);
    }

    function renderPageInfo(meta = {}) {
        const from = Number(meta.from ?? 0);
        const to = Number(meta.to ?? 0);
        const total = Number(meta.total ?? 0);
        el.pageInfo.textContent = `Showing ${from} to ${to} of ${total} entries`;
    }

    function paginationBtn(label, page, disabled = false, active = false) {
        const base = 'inline-flex items-center justify-center rounded-md border px-3 py-1.5 text-sm';
        const cls = active
            ? `${base} border-blue-600 bg-blue-600 text-white`
            : disabled
                ? `${base} border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed`
                : `${base} border-gray-300 bg-white text-gray-700 hover:bg-gray-50`;

        return `<button type="button" class="${cls}" data-page="${page}" ${disabled ? 'disabled' : ''}>${label}</button>`;
    }

    function renderPagination(meta = {}) {
        const current = Number(meta.current_page ?? 1);
        const last = Number(meta.last_page ?? 1);

        if (last <= 1) {
            el.pagination.innerHTML = '';
            return;
        }

        let html = '';
        html += paginationBtn('Prev', Math.max(current - 1, 1), current <= 1, false);

        let start = Math.max(1, current - 2);
        let end = Math.min(last, current + 2);

        if (start > 1) {
            html += paginationBtn('1', 1, false, current === 1);
            if (start > 2) {
                html += `<span class="px-2 text-sm text-gray-400">...</span>`;
            }
        }

        for (let i = start; i <= end; i++) {
            html += paginationBtn(String(i), i, false, i === current);
        }

        if (end < last) {
            if (end < last - 1) {
                html += `<span class="px-2 text-sm text-gray-400">...</span>`;
            }
            html += paginationBtn(String(last), last, false, current === last);
        }

        html += paginationBtn('Next', Math.min(current + 1, last), current >= last, false);

        el.pagination.innerHTML = html;

        el.pagination.querySelectorAll('button[data-page]').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (isBusy) return;
                const page = Number(btn.dataset.page || 1);
                if (page < 1 || page === currentPage) return;
                currentPage = page;
                await loadData(page);
            });
        });
    }

    async function safeJson(resp) {
        try {
            return await resp.json();
        } catch {
            return {};
        }
    }

    async function loadFilters() {
        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.issuesolomon.filters') }}", {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            const json = await safeJson(resp);

            if (!resp.ok || !json.ok) {
                return;
            }

            const companies = Array.isArray(json.data?.companies) ? json.data.companies : [];
            const statuses = Array.isArray(json.data?.statuses) ? json.data.statuses : ['P', 'C'];
            const perPages = Array.isArray(json.data?.per_pages) ? json.data.per_pages : [25, 50, 100];

            el.company.innerHTML = `<option value="">All Company</option>` +
                companies.map(c => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('');

            el.status.innerHTML = `<option value="">All Status</option>` +
                statuses.map(s => `<option value="${escapeHtml(s)}">${escapeHtml(s)}</option>`).join('');

            el.perPage.innerHTML = perPages.map(n => {
                const selected = Number(n) === 25 ? 'selected' : '';
                return `<option value="${n}" ${selected}>${n}</option>`;
            }).join('');
        } catch (err) {
            console.error(err);
        }
    }

    async function loadData(page = 1) {
        hideInfo();

        if (!el.from.value || !el.to.value) {
            setInfo('warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        currentPage = page;

        setBusy(true, 'Loading Issue Solomon...', 'Sedang mengambil data Issue Solomon.');

        el.tbody.innerHTML = `
            <tr>
                <td colspan="13" class="px-4 py-10 text-center text-gray-500">Loading...</td>
            </tr>
        `;
        el.chkAll.disabled = true;
        el.chkAll.checked = false;
        el.chkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.issuesolomon.list') }}", window.location.origin);
        url.searchParams.set('from', el.from.value);
        url.searchParams.set('to', el.to.value);
        url.searchParams.set('company', el.company.value || '');
        url.searchParams.set('status', el.status.value || '');
        url.searchParams.set('per_page', el.perPage.value || '25');
        url.searchParams.set('page', String(page));

        try {
            const resp = await fetch(url.toString(), {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            const json = await safeJson(resp);

            if (!resp.ok || !json.ok) {
                renderRows([]);
                renderSummary({ P: 0, C: 0, total: 0 });
                renderPageInfo({ from: 0, to: 0, total: 0 });
                renderPagination({ current_page: 1, last_page: 1 });
                setInfo('err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = Array.isArray(json.data) ? json.data : [];
            const summary = json.summary ?? {};
            const meta = json.meta ?? {};

            renderRows(rows);
            renderSummary(summary);
            renderPageInfo(meta);
            renderPagination(meta);

            setInfo(
                'ok',
                `Loaded ${meta.total ?? rows.length} Issue. P: ${summary.P ?? 0}, C: ${summary.C ?? 0}.`
            );
        } catch (err) {
            console.error(err);
            renderRows([]);
            renderSummary({ P: 0, C: 0, total: 0 });
            renderPageInfo({ from: 0, to: 0, total: 0 });
            renderPagination({ current_page: 1, last_page: 1 });
            setInfo('err', err?.message ?? 'Error saat load.');
        } finally {
            setBusy(false);
        }
    }

    async function processData() {
        if (isBusy) return;

        hideInfo();

        const ids = Array.from(el.tbody.querySelectorAll('.slIssueRowChk:checked'))
            .filter(chk => String(chk.dataset.stage ?? '').toUpperCase() === 'P')
            .map(chk => chk.value)
            .filter(v => v && v !== 'undefined' && !v.endsWith('||'));

        if (ids.length === 0) {
            setInfo('warn', 'Pilih minimal 1 Issue status P untuk diproses. Status C tidak bisa.');
            return;
        }

        setBusy(true, 'Processing Issue Solomon...', 'Sedang insert ke Solomon (P → C).');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.issuesolomon.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ ids })
            });

            const json = await safeJson(resp);

            if (!resp.ok || !json.ok) {
                const fail0 = (json.failed && json.failed.length) ? json.failed[0] : null;
                const detail = fail0
                    ? ` (${fail0.cpny_id || ''}||${fail0.issue_id || ''}: ${fail0.error || ''})`
                    : '';

                setInfo('err', (json.message ?? 'Gagal process.') + detail);
                return;
            }

            setInfo('ok', `Process done. Sent OK(P->C): ${json.sent_success_P_to_C ?? 0}, Failed: ${json.sent_failed ?? 0}`);
        } catch (err) {
            console.error(err);
            setInfo('err', err?.message ?? 'Error saat process.');
        } finally {
            setBusy(false);
        }

        await loadData(currentPage);
    }

    el.chkAll.addEventListener('change', () => {
        if (isBusy) return;
        const enabled = Array.from(el.tbody.querySelectorAll('.slIssueRowChk')).filter(chk => !chk.disabled);
        enabled.forEach(chk => chk.checked = el.chkAll.checked);
        syncChkAllState();
    });

    el.btnLoad.addEventListener('click', async () => {
        if (isBusy) return;
        currentPage = 1;
        await loadData(1);
    });

    el.btnProcess.addEventListener('click', async () => {
        await processData();
    });

    el.company.addEventListener('change', async () => {
        if (isBusy) return;
        currentPage = 1;
        await loadData(1);
    });

    el.status.addEventListener('change', async () => {
        if (isBusy) return;
        currentPage = 1;
        await loadData(1);
    });

    el.perPage.addEventListener('change', async () => {
        if (isBusy) return;
        currentPage = 1;
        await loadData(1);
    });

    // (() => {
    //     const today = new Date();
    //     const yyyy = today.getFullYear();
    //     const mm = String(today.getMonth() + 1).padStart(2, '0');
    //     const dd = String(today.getDate()).padStart(2, '0');

    //     if (!el.to.value) el.to.value = `${yyyy}-${mm}-${dd}`;
    //     if (!el.from.value) el.from.value = `${yyyy}-${mm}-01`;
    // })();

    (() => {
        const formatDate = (date) => {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');

            return `${yyyy}-${mm}-${dd}`;
        };

        const today = new Date();

        const fromDate = new Date(today);
        fromDate.setDate(today.getDate() - 30);

        if (!el.from.value) el.from.value = formatDate(fromDate);
        if (!el.to.value) el.to.value = formatDate(today);
    })();

    (async () => {
        await loadFilters();
    })();
})();
</script>