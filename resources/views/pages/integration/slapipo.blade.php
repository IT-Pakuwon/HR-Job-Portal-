<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="slPoBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
    <div class="absolute inset-0 bg-black/40 pointer-events-auto"></div>

    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div class="pointer-events-auto rounded-xl bg-white px-5 py-4 shadow-lg border border-gray-200 flex items-center gap-3">
            <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>

            <div>
                <div id="slPoBusyTitle" class="font-semibold text-gray-800">Processing...</div>
                <div id="slPoBusySub" class="text-sm text-gray-500">Mohon tunggu...</div>
            </div>
        </div>
    </div>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-4">
    <div class="mb-3">
        <div class="text-lg font-bold text-gray-800">🛒 PO Solomon (P-Solomon → C)</div>
        <div class="text-sm text-gray-500">
            Load data PO Solomon berdasarkan range tanggal, lalu proses P → C ke Staging_POHdr / Staging_PODet
        </div>
    </div>

    <div id="slPoInfo" class="hidden mt-3 rounded-md border px-4 py-3 text-sm"></div>

    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" id="slPoFrom"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" id="slPoTo"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Company</label>
                <select id="slPoCompany"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Company</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                <select id="slPoStatus"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">All Status</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Show</label>
                <select id="slPoPerPage"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div class="md:col-span-2 flex items-end justify-end gap-2">
                <button type="button" id="btnLoadSlPo"
                    class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Load
                </button>

                <button type="button" id="btnProcessSlPo"
                    class="inline-flex items-center justify-center rounded-md bg-green-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                    Process
                </button>
            </div>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200">
        <div class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3 md:flex-row md:items-center md:justify-between">
            <div class="text-sm font-semibold text-gray-700">
                Total: <span id="slPoTotal">0</span>
            </div>

            <div id="slPoSummary" class="text-sm text-gray-600">
                H: 0, D: 0, P: 0, C: 0
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="w-10 px-4 py-2 text-left">
                            <input type="checkbox" id="slPoChkAll" class="rounded border-gray-300">
                        </th>
                        <th class="px-4 py-2 text-left">Integration Type</th>
                        <th class="px-4 py-2 text-left">Cpny</th>
                        <th class="px-4 py-2 text-left">Entity Cd</th>
                        <th class="px-4 py-2 text-left">Order No</th>
                        <th class="px-4 py-2 text-left">Order Date</th>
                        <th class="px-4 py-2 text-left">Department ID</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2 text-left">Response</th>
                        <th class="px-4 py-2 text-left">Last Update</th>
                    </tr>
                </thead>

                <tbody id="slPoTbody" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="10" class="px-4 py-10 text-center text-gray-500">Belum ada data. Klik Load.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 bg-white px-4 py-3 md:flex-row md:items-center md:justify-between">
            <div id="slPoPageInfo" class="text-xs text-gray-500">
                Showing 0 to 0 of 0 entries
            </div>

            <div class="flex items-center gap-2">
                <button type="button" id="slPoPrevPage"
                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                    Prev
                </button>

                <div id="slPoPageNumbers" class="flex items-center gap-1"></div>

                <button type="button" id="slPoNextPage"
                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                    Next
                </button>
            </div>
        </div>

        <div class="px-4 py-3 text-xs text-gray-500 bg-white border-t border-gray-100">
            Legend: H = belum masuk staging, D = waiting review, P = ready, C = completed.
        </div>
    </div>
</div>

<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const el = {
        overlay: document.getElementById('slPoBusyOverlay'),
        busyTitle: document.getElementById('slPoBusyTitle'),
        busySub: document.getElementById('slPoBusySub'),
        from: document.getElementById('slPoFrom'),
        to: document.getElementById('slPoTo'),
        company: document.getElementById('slPoCompany'),
        status: document.getElementById('slPoStatus'),
        perPage: document.getElementById('slPoPerPage'),
        btnLoad: document.getElementById('btnLoadSlPo'),
        btnProcess: document.getElementById('btnProcessSlPo'),
        info: document.getElementById('slPoInfo'),
        total: document.getElementById('slPoTotal'),
        summary: document.getElementById('slPoSummary'),
        chkAll: document.getElementById('slPoChkAll'),
        tbody: document.getElementById('slPoTbody'),
        pageInfo: document.getElementById('slPoPageInfo'),
        prevPage: document.getElementById('slPoPrevPage'),
        nextPage: document.getElementById('slPoNextPage'),
        pageNumbers: document.getElementById('slPoPageNumbers'),
    };

    let isBusy = false;
    let currentPage = 1;
    let lastPage = 1;

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
            el.btnLoad, el.btnProcess, el.chkAll, el.prevPage, el.nextPage
        ].forEach(node => {
            if (node) node.disabled = busy;
        });

        el.pageNumbers.querySelectorAll('button').forEach(btn => btn.disabled = busy);

        document.querySelectorAll('.slPoRowChk').forEach(chk => {
            if (busy) {
                chk.disabled = true;
            } else {
                const stage = String(chk.dataset.stage || '').toUpperCase();
                chk.disabled = stage !== 'P';
                if (chk.disabled) chk.checked = false;
            }
        });

        syncChkAllState();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function badge(stage) {
        const s = String(stage || '').toUpperCase();

        if (s === 'H') return `<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">H</span>`;
        if (s === 'D') return `<span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700">D</span>`;
        if (s === 'P') return `<span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">P</span>`;
        return `<span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">C</span>`;
    }

    function syncChkAllState() {
        const enabled = Array.from(document.querySelectorAll('.slPoRowChk')).filter(x => !x.disabled);
        if (enabled.length === 0) {
            el.chkAll.checked = false;
            el.chkAll.indeterminate = false;
            el.chkAll.disabled = true;
            return;
        }

        el.chkAll.disabled = false;
        const checked = enabled.filter(x => x.checked).length;
        el.chkAll.checked = checked === enabled.length;
        el.chkAll.indeterminate = checked > 0 && checked < enabled.length;
    }

    function renderRows(rows) {
        if (!rows || rows.length === 0) {
            el.tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="px-4 py-10 text-center text-gray-500">No data.</td>
                </tr>
            `;
            syncChkAllState();
            return;
        }

        el.tbody.innerHTML = rows.map(r => {
            const stage = String(r.stage_status || 'H').toUpperCase();
            const disabled = stage !== 'P' ? 'disabled' : '';

            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 align-top">
                        <input type="checkbox" class="slPoRowChk rounded border-gray-300"
                            value="${escapeHtml(r.key)}"
                            data-stage="${escapeHtml(stage)}"
                            ${disabled}>
                    </td>
                    <td class="px-4 py-2 align-top">${escapeHtml(r.integration_type || '')}</td>
                    <td class="px-4 py-2 align-top">${escapeHtml(r.cpny_id || '')}</td>
                    <td class="px-4 py-2 align-top">${escapeHtml(r.entity_cd || '')}</td>
                    <td class="px-4 py-2 align-top">${escapeHtml(r.order_no || '')}</td>
                    <td class="px-4 py-2 align-top">${escapeHtml(r.order_date || '')}</td>
                    <td class="px-4 py-2 align-top">${escapeHtml(r.department_id || '')}</td>
                    <td class="px-4 py-2 text-center align-top">${badge(stage)}</td>
                    <td class="px-4 py-2 align-top whitespace-normal break-words max-w-[280px]">${escapeHtml(r.payload_response || '')}</td>
                    <td class="px-4 py-2 align-top">${escapeHtml(r.last_update || '')}</td>
                </tr>
            `;
        }).join('');

        document.querySelectorAll('.slPoRowChk').forEach(chk => {
            chk.addEventListener('change', syncChkAllState);
        });

        syncChkAllState();
    }

    function renderPagination(meta) {
        currentPage = Number(meta.current_page || 1);
        lastPage = Number(meta.last_page || 1);

        el.pageInfo.textContent = `Showing ${meta.from || 0} to ${meta.to || 0} of ${meta.total || 0} entries`;

        el.prevPage.disabled = currentPage <= 1;
        el.nextPage.disabled = currentPage >= lastPage;

        let start = Math.max(1, currentPage - 2);
        let end = Math.min(lastPage, currentPage + 2);

        const html = [];
        for (let i = start; i <= end; i++) {
            html.push(`
                <button type="button"
                    class="slPoPageBtn rounded-md px-3 py-1.5 text-sm border ${i === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'}"
                    data-page="${i}">
                    ${i}
                </button>
            `);
        }

        el.pageNumbers.innerHTML = html.join('');

        document.querySelectorAll('.slPoPageBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                currentPage = Number(btn.dataset.page || 1);
                loadData(false);
            });
        });
    }

    async function loadFilters() {
        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.posolomon.filters') }}", {
                headers: { 'Accept': 'application/json' }
            });

            const json = await resp.json();
            if (!json.ok) return;

            const data = json.data || {};

            el.company.innerHTML = `<option value="">All Company</option>` +
                (data.companies || []).map(v => `<option value="${escapeHtml(v)}">${escapeHtml(v)}</option>`).join('');

            el.status.innerHTML = `<option value="">All Status</option>` +
                (data.statuses || []).map(v => `<option value="${escapeHtml(v)}">${escapeHtml(v)}</option>`).join('');

            el.perPage.innerHTML = (data.per_pages || [25, 50, 100])
                .map(v => `<option value="${v}" ${Number(v) === 25 ? 'selected' : ''}>${v}</option>`)
                .join('');
        } catch (err) {
            console.error(err);
        }
    }

    async function loadData(resetPage = true) {
        hideInfo();

        if (!el.from.value || !el.to.value) {
            setInfo('warn', 'Start Date dan End Date wajib diisi.');
            return;
        }

        if (resetPage) currentPage = 1;

        setBusy(true, 'Loading PO Solomon...', 'Sedang mengambil data.');

        try {
            const url = new URL("{{ route('integration.ifcaintegration.posolomon.list') }}", window.location.origin);
            url.searchParams.set('from', el.from.value);
            url.searchParams.set('to', el.to.value);
            url.searchParams.set('company', el.company.value || '');
            url.searchParams.set('status', el.status.value || '');
            url.searchParams.set('per_page', el.perPage.value || '25');
            url.searchParams.set('page', currentPage);

            const resp = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json' }
            });

            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRows([]);
                renderPagination({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 });
                el.total.textContent = '0';
                el.summary.textContent = 'H: 0, D: 0, P: 0, C: 0';
                setInfo('err', json.message || 'Gagal load data.');
                return;
            }

            renderRows(json.data || []);
            renderPagination(json.meta || {});
            el.total.textContent = String(json.summary?.total || 0);
            el.summary.textContent = `P: ${json.summary?.P || 0}, C: ${json.summary?.C || 0}`;

            setInfo('ok', `Loaded ${json.summary?.total || 0} data.`);
        } catch (err) {
            console.error(err);
            renderRows([]);
            renderPagination({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 });
            el.total.textContent = '0';
            el.summary.textContent = 'H: 0, D: 0, P: 0, C: 0';
            setInfo('err', 'Terjadi error saat load data.');
        } finally {
            setBusy(false);
        }
    }

    async function processData() {
        const ids = Array.from(document.querySelectorAll('.slPoRowChk:checked'))
            .filter(x => String(x.dataset.stage || '').toUpperCase() === 'P')
            .map(x => x.value);

        if (ids.length === 0) {
            setInfo('warn', 'Pilih minimal 1 data status P.');
            return;
        }

        setBusy(true, 'Processing...', 'Sedang memproses data.');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.posolomon.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                setInfo('err', json.message || 'Gagal process.');
                return;
            }

            setInfo('ok', `Process done. Success: ${json.sent_success_P_to_C || 0}, Failed: ${json.sent_failed || 0}`);
            await loadData(false);
        } catch (err) {
            console.error(err);
            setInfo('err', 'Terjadi error saat process data.');
        } finally {
            setBusy(false);
        }
    }

    el.btnLoad.addEventListener('click', () => loadData(true));
    el.btnProcess.addEventListener('click', processData);

    el.company.addEventListener('change', () => loadData(true));
    el.status.addEventListener('change', () => loadData(true));
    el.perPage.addEventListener('change', () => loadData(true));

    el.prevPage.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            loadData(false);
        }
    });

    el.nextPage.addEventListener('click', () => {
        if (currentPage < lastPage) {
            currentPage++;
            loadData(false);
        }
    });

    el.chkAll.addEventListener('change', () => {
        document.querySelectorAll('.slPoRowChk:not(:disabled)').forEach(chk => {
            chk.checked = el.chkAll.checked;
        });
        syncChkAllState();
    });

    (() => {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');

        el.to.value = `${yyyy}-${mm}-${dd}`;
        el.from.value = `${yyyy}-${mm}-01`;
    })();

    loadFilters();
})();
</script>
