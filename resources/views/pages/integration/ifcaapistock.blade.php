<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="stockBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
    <div class="absolute inset-0 bg-black/40 pointer-events-auto"></div>

    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div class="pointer-events-auto rounded-xl bg-white px-5 py-4 shadow-lg border border-gray-200 flex items-center gap-3">
            <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <div class="text-sm">
                <div class="font-semibold text-gray-800" id="stockBusyTitle">Processing...</div>
                <div class="text-gray-500" id="stockBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-6">
    <div>
        <label class="text-sm font-medium text-gray-600">Start Date</label>
        <input type="date" id="st_from"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">End Date</label>
        <input type="date" id="st_to"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Status</label>
        <select id="st_status"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="H">H</option>
            <option value="P">P</option>
            <option value="C">C</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Show</label>
        <select id="st_per_page"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnLoadStock"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
            Load
        </button>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnProcessStock"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
            Process
        </button>
    </div>
</div>

<div id="stInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="stTotal">0</span>
            <span class="ml-2 text-gray-500" id="stShowingText"></span>
        </div>
        <div class="text-sm text-gray-500">Pagination enabled</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2 align-middle">
                    <input type="checkbox" id="stChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-48 px-3 py-2 align-middle">Stock Code</th>
                <th class="w-[420px] px-3 py-2 align-middle">Description</th>
                <th class="w-24 px-3 py-2 align-middle">UOM</th>
                <th class="w-24 px-3 py-2 align-middle">Status</th>
                <th class="w-[420px] px-3 py-2 align-middle">Response</th>
                <th class="w-40 px-3 py-2 align-middle">Last Update</th>
            </tr>
            </thead>
            <tbody id="stTbody" class="divide-y divide-gray-100">
            <tr>
                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                    Belum ada data. Klik Load.
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 border-t border-gray-200 bg-white px-4 py-3 md:flex-row md:items-center md:justify-between">
        <div class="text-xs text-gray-500">
            <span class="font-semibold">Legend:</span>
            H = belum ada di staging,
            P = di staging belum terkirim,
            C = sudah terkirim / completed.
        </div>

        <div id="stPagination" class="flex flex-wrap items-center gap-2"></div>
    </div>
</div>

<script>
    const csrfStock = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const stFromStock       = document.getElementById('st_from');
    const stToStock         = document.getElementById('st_to');
    const stStatusStock     = document.getElementById('st_status');
    const stPerPageStock    = document.getElementById('st_per_page');

    const stTbodyStock      = document.getElementById('stTbody');
    const stTotalStock      = document.getElementById('stTotal');
    const stShowingText     = document.getElementById('stShowingText');
    const stInfoStock       = document.getElementById('stInfo');
    const stChkAllStock     = document.getElementById('stChkAll');
    const stPaginationStock = document.getElementById('stPagination');

    const btnLoadStock      = document.getElementById('btnLoadStock');
    const btnProcessStock   = document.getElementById('btnProcessStock');

    const stockBusyOverlay  = document.getElementById('stockBusyOverlay');
    const stockBusyTitle    = document.getElementById('stockBusyTitle');
    const stockBusySub      = document.getElementById('stockBusySub');

    let stockBusy = false;
    let stockCurrentPage = 1;

    function getStockTabEls() {
        return Array.from(document.querySelectorAll('a, button'))
            .filter(el => {
                if (!el || el === btnLoadStock || el === btnProcessStock) return false;
                const txt = (el.textContent || '').trim().toLowerCase();
                return ['non stock','stock','supplier','po','grn','sttb','bast','issue','receipt'].some(k => txt === k || txt.includes(k));
            });
    }

    function setInfoStock(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function hideInfoStock(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    function getStatusBadgeClassStock(stage) {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
    }

    function syncChkAllStateStock() {
        const enabled = Array.from(stTbodyStock.querySelectorAll('.stRowChk:not(:disabled)'));
        if (enabled.length === 0) {
            stChkAllStock.checked = false;
            stChkAllStock.indeterminate = false;
            stChkAllStock.disabled = true;
            return;
        }

        stChkAllStock.disabled = false;

        const checkedEnabled = enabled.filter(chk => chk.checked).length;
        stChkAllStock.checked = checkedEnabled === enabled.length;
        stChkAllStock.indeterminate = checkedEnabled > 0 && checkedEnabled < enabled.length;
    }

    stChkAllStock.addEventListener('change', () => {
        if (stockBusy) return;
        stTbodyStock.querySelectorAll('.stRowChk:not(:disabled)').forEach(chk => chk.checked = stChkAllStock.checked);
        syncChkAllStateStock();
    });

    function renderRowsStock(rows) {
        if (!rows.length) {
            stTbodyStock.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            stChkAllStock.checked = false;
            stChkAllStock.indeterminate = false;
            stChkAllStock.disabled = true;
            return;
        }

        stTbodyStock.innerHTML = rows.map(r => {
            const stage = r.stage_status ?? 'H';
            const isC = stage === 'C';

            const trClass = isC ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const checkboxClass = isC ? 'opacity-40 cursor-not-allowed' : '';
            const title = isC ? 'Sudah completed (C). Tidak bisa diproses.' : '';

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2 align-top">
                        <input type="checkbox"
                               class="stRowChk rounded border-gray-300 ${checkboxClass}"
                               value="${r.id}"
                               data-stage="${stage}"
                               ${isC ? `disabled title="${title}"` : ''}>
                    </td>
                    <td class="px-3 py-2 align-top font-medium">${r.inventoryid ?? ''}</td>
                    <td class="px-3 py-2 align-top break-words">${r.inventory_descr ?? ''}</td>
                    <td class="px-3 py-2 align-top whitespace-nowrap text-gray-700">${r.stock_unit ?? ''}</td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassStock(stage)}">
                            ${stage}
                        </span>
                    </td>
                    <td class="px-3 py-2 align-top text-gray-600">
                        <div class="whitespace-normal break-words leading-5 max-w-full">
                            ${r.payload_response ?? ''}
                        </div>
                    </td>
                    <td class="px-3 py-2 align-top whitespace-nowrap text-gray-600">${r.last_update ?? ''}</td>
                </tr>
            `;
        }).join('');

        stTbodyStock.querySelectorAll('.stRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', () => {
                if (stockBusy) return;
                syncChkAllStateStock();
            });
        });

        if (stockBusy) {
            stTbodyStock.querySelectorAll('.stRowChk').forEach(chk => chk.disabled = true);
        }

        syncChkAllStateStock();
    }

    function renderPaginationStock(meta) {
        stPaginationStock.innerHTML = '';

        if (!meta || meta.last_page <= 1) {
            return;
        }

        const current = Number(meta.current_page || 1);
        const last = Number(meta.last_page || 1);

        const makeBtn = (label, page, disabled = false, active = false) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = label;
            btn.className = `rounded-lg border px-3 py-1.5 text-sm ${
                active
                    ? 'border-blue-600 bg-blue-600 text-white'
                    : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
            } ${disabled ? 'cursor-not-allowed opacity-50' : ''}`;
            btn.disabled = disabled;

            if (!disabled) {
                btn.addEventListener('click', () => {
                    if (stockBusy) return;
                    loadStock(page);
                });
            }

            return btn;
        };

        stPaginationStock.appendChild(makeBtn('Prev', current - 1, current <= 1));

        let start = Math.max(1, current - 2);
        let end = Math.min(last, current + 2);

        if (current <= 3) end = Math.min(last, 5);
        if (current >= last - 2) start = Math.max(1, last - 4);

        for (let i = start; i <= end; i++) {
            stPaginationStock.appendChild(makeBtn(String(i), i, false, i === current));
        }

        stPaginationStock.appendChild(makeBtn('Next', current + 1, current >= last));
    }

    function setBusyStock(isBusy, title = 'Processing...', sub = 'Mohon tunggu, jangan klik menu/tab.') {
        stockBusy = isBusy;

        if (isBusy) {
            stockBusyTitle.textContent = title;
            stockBusySub.textContent = sub;
            stockBusyOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            stockBusyOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        stFromStock.disabled = isBusy;
        stToStock.disabled = isBusy;
        stStatusStock.disabled = isBusy;
        stPerPageStock.disabled = isBusy;
        btnLoadStock.disabled = isBusy;
        btnProcessStock.disabled = isBusy;
        stChkAllStock.disabled = isBusy;

        const tabs = getStockTabEls();
        tabs.forEach(el => {
            if (isBusy) {
                if (el.dataset._stock_prev_pointer == null) el.dataset._stock_prev_pointer = el.style.pointerEvents || '';
                if (el.dataset._stock_prev_opacity == null) el.dataset._stock_prev_opacity = el.style.opacity || '';
                el.style.pointerEvents = 'none';
                el.style.opacity = '0.6';
            } else {
                el.style.pointerEvents = el.dataset._stock_prev_pointer ?? '';
                el.style.opacity = el.dataset._stock_prev_opacity ?? '';
                delete el.dataset._stock_prev_pointer;
                delete el.dataset._stock_prev_opacity;
            }
        });

        const rowChks = stTbodyStock.querySelectorAll('.stRowChk');
        rowChks.forEach(chk => {
            if (isBusy) {
                chk.disabled = true;
                return;
            }

            const stage = String(chk.dataset.stage ?? '').toUpperCase();
            chk.disabled = stage === 'C';
            if (chk.disabled) chk.checked = false;
        });

        syncChkAllStateStock();

        if (isBusy) {
            btnLoadStock.dataset._txt = btnLoadStock.textContent;
            btnProcessStock.dataset._txt = btnProcessStock.textContent;
            btnLoadStock.textContent = 'Loading...';
            btnProcessStock.textContent = 'Processing...';
        } else {
            if (btnLoadStock.dataset._txt) btnLoadStock.textContent = btnLoadStock.dataset._txt;
            if (btnProcessStock.dataset._txt) btnProcessStock.textContent = btnProcessStock.dataset._txt;
        }
    }

    async function loadStock(page = 1) {
        hideInfoStock(stInfoStock);

        if (!stFromStock.value || !stToStock.value) {
            setInfoStock(stInfoStock, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        stockCurrentPage = page;

        setBusyStock(true, 'Loading Stock...', 'Sedang mengambil data Stock.');

        stTbodyStock.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        stChkAllStock.disabled = true;
        stChkAllStock.checked = false;
        stChkAllStock.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.stock.list') }}", window.location.origin);
        url.searchParams.set('from', stFromStock.value);
        url.searchParams.set('to', stToStock.value);
        url.searchParams.set('status', stStatusStock.value);
        url.searchParams.set('per_page', stPerPageStock.value);
        url.searchParams.set('page', page);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsStock([]);
                renderPaginationStock(null);
                stTotalStock.textContent = '0';
                stShowingText.textContent = '';
                setInfoStock(stInfoStock, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            const summary = json.summary || {};
            const meta = json.meta || {};

            renderRowsStock(rows);
            renderPaginationStock(meta);

            stTotalStock.textContent = meta.total ?? 0;
            stShowingText.textContent = meta.total > 0
                ? `(Showing ${meta.from} - ${meta.to})`
                : '';

            setInfoStock(
                stInfoStock,
                'ok',
                `Loaded ${meta.total ?? 0} items. Ready(H/P): ${summary.ready ?? 0}. Holding(H): ${summary.H ?? 0}. Pending(P): ${summary.P ?? 0}. Completed(C): ${summary.C ?? 0}.`
            );
        } catch (e) {
            renderRowsStock([]);
            renderPaginationStock(null);
            stTotalStock.textContent = '0';
            stShowingText.textContent = '';
            setInfoStock(stInfoStock, 'err', e.message ?? 'Error saat load.');
        } finally {
            setBusyStock(false);
        }
    }

    (function initDefaultDatesStock() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        stToStock.value = `${yyyy}-${mm}-${dd}`;
        stFromStock.value = `${yyyy}-${mm}-01`;
    })();

    btnLoadStock.addEventListener('click', async () => {
        if (stockBusy) return;
        await loadStock(1);
    });

    stStatusStock.addEventListener('change', () => {
        if (stockBusy) return;
        loadStock(1);
    });

    stPerPageStock.addEventListener('change', () => {
        if (stockBusy) return;
        loadStock(1);
    });

    btnProcessStock.addEventListener('click', async () => {
        if (stockBusy) return;

        hideInfoStock(stInfoStock);

        const ids = Array.from(stTbodyStock.querySelectorAll('.stRowChk:checked'))
            .filter(chk => chk.dataset.stage !== 'C')
            .map(chk => parseInt(chk.value, 10))
            .filter(n => !Number.isNaN(n));

        if (ids.length === 0) {
            setInfoStock(stInfoStock, 'warn', 'Pilih minimal 1 item status H/P untuk diproses. Item C diabaikan.');
            return;
        }

        setBusyStock(true, 'Processing Stock...', 'Sedang insert staging (H→P) dan/atau kirim ke API (P→C).');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.stock.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfStock
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();
            if (!resp.ok || !json.ok) {
                setInfoStock(stInfoStock, 'err', json.message ?? 'Gagal process.');
                return;
            }

            setInfoStock(
                stInfoStock,
                'ok',
                `Process done. Inserted(H->P): ${json.inserted_H_to_P ?? 0}, Sent OK(P->C): ${json.sent_success_P_to_C ?? 0}, Failed(P): ${json.sent_failed_still_P ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );
        } catch (e) {
            setInfoStock(stInfoStock, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusyStock(false);
        }

        await loadStock(stockCurrentPage || 1);
    });
</script>
