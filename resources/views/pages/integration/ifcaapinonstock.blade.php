<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="nonStockBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
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
                <div class="font-semibold text-gray-800" id="nonStockBusyTitle">Processing...</div>
                <div class="text-gray-500" id="nonStockBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-6">
    <div>
        <label class="text-sm font-medium text-gray-600">Start Date</label>
        <input type="date" id="ns_from"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">End Date</label>
        <input type="date" id="ns_to"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Status</label>
        <select id="ns_status"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="H">H</option>
            <option value="P">P</option>
            <option value="C">C</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Show</label>
        <select id="ns_per_page"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnLoadNonStock"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
            Load
        </button>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnProcessNonStock"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
            Process
        </button>
    </div>
</div>

<div id="nsInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="nsTotal">0</span>
            <span class="ml-2 text-gray-500" id="nsShowingText"></span>
        </div>
        <div class="text-sm text-gray-500">Pagination enabled</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2 align-middle">
                    <input type="checkbox" id="nsChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-48 px-3 py-2 align-middle">Inventory ID</th>
                <th class="w-[420px] px-3 py-2 align-middle">Description</th>
                <th class="w-24 px-3 py-2 align-middle">UOM</th>
                <th class="w-24 px-3 py-2 align-middle">Status</th>
                <th class="w-[420px] px-3 py-2 align-middle">Response</th>
                <th class="w-40 px-3 py-2 align-middle">Last Update</th>
            </tr>
            </thead>
            <tbody id="nsTbody" class="divide-y divide-gray-100">
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

        <div id="nsPagination" class="flex flex-wrap items-center gap-2"></div>
    </div>
</div>

<script>
    const csrfNonStock = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const nsFromNonStock       = document.getElementById('ns_from');
    const nsToNonStock         = document.getElementById('ns_to');
    const nsStatusNonStock     = document.getElementById('ns_status');
    const nsPerPageNonStock    = document.getElementById('ns_per_page');

    const nsTbodyNonStock      = document.getElementById('nsTbody');
    const nsTotalNonStock      = document.getElementById('nsTotal');
    const nsShowingText        = document.getElementById('nsShowingText');
    const nsInfoNonStock       = document.getElementById('nsInfo');
    const nsChkAllNonStock     = document.getElementById('nsChkAll');
    const nsPaginationNonStock = document.getElementById('nsPagination');

    const btnLoadNonStock      = document.getElementById('btnLoadNonStock');
    const btnProcessNonStock   = document.getElementById('btnProcessNonStock');

    const nonStockBusyOverlay  = document.getElementById('nonStockBusyOverlay');
    const nonStockBusyTitle    = document.getElementById('nonStockBusyTitle');
    const nonStockBusySub      = document.getElementById('nonStockBusySub');

    let nonStockBusy = false;
    let nonStockCurrentPage = 1;

    function getNonStockTabEls() {
        return Array.from(document.querySelectorAll('a, button'))
            .filter(el => {
                if (!el || el === btnLoadNonStock || el === btnProcessNonStock) return false;
                const txt = (el.textContent || '').trim().toLowerCase();
                return ['non stock','stock','supplier','po','grn','sttb','bast','issue','receipt'].some(k => txt === k || txt.includes(k));
            });
    }

    function setInfoNonStock(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function hideInfoNonStock(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    function getStatusBadgeClassNonStock(stage) {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
    }

    function syncChkAllStateNonStock() {
        const enabled = Array.from(nsTbodyNonStock.querySelectorAll('.nsRowChk:not(:disabled)'));
        if (enabled.length === 0) {
            nsChkAllNonStock.checked = false;
            nsChkAllNonStock.indeterminate = false;
            nsChkAllNonStock.disabled = true;
            return;
        }

        nsChkAllNonStock.disabled = false;

        const checkedEnabled = enabled.filter(chk => chk.checked).length;
        nsChkAllNonStock.checked = checkedEnabled === enabled.length;
        nsChkAllNonStock.indeterminate = checkedEnabled > 0 && checkedEnabled < enabled.length;
    }

    nsChkAllNonStock.addEventListener('change', () => {
        if (nonStockBusy) return;
        nsTbodyNonStock.querySelectorAll('.nsRowChk:not(:disabled)').forEach(chk => chk.checked = nsChkAllNonStock.checked);
        syncChkAllStateNonStock();
    });

    function renderRowsNonStock(rows) {
        if (!rows.length) {
            nsTbodyNonStock.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            nsChkAllNonStock.checked = false;
            nsChkAllNonStock.indeterminate = false;
            nsChkAllNonStock.disabled = true;
            return;
        }

        nsTbodyNonStock.innerHTML = rows.map(r => {
            const stage = r.stage_status ?? 'H';
            const isC = stage === 'C';

            const trClass = isC ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const checkboxClass = isC ? 'opacity-40 cursor-not-allowed' : '';
            const title = isC ? 'Sudah completed (C). Tidak bisa diproses.' : '';

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2 align-top">
                        <input type="checkbox"
                               class="nsRowChk rounded border-gray-300 ${checkboxClass}"
                               value="${r.id}"
                               data-stage="${stage}"
                               ${isC ? `disabled title="${title}"` : ''}>
                    </td>
                    <td class="px-3 py-2 align-top font-medium">${r.inventoryid ?? ''}</td>
                    <td class="px-3 py-2 align-top break-words">${r.inventory_descr ?? ''}</td>
                    <td class="px-3 py-2 align-top whitespace-nowrap text-gray-700">${r.stock_unit ?? ''}</td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassNonStock(stage)}">
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

        nsTbodyNonStock.querySelectorAll('.nsRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', () => {
                if (nonStockBusy) return;
                syncChkAllStateNonStock();
            });
        });

        if (nonStockBusy) {
            nsTbodyNonStock.querySelectorAll('.nsRowChk').forEach(chk => chk.disabled = true);
        }

        syncChkAllStateNonStock();
    }

    function renderPaginationNonStock(meta) {
        nsPaginationNonStock.innerHTML = '';

        if (!meta || meta.last_page <= 1) {
            return;
        }

        const current = Number(meta.current_page || 1);
        const last = Number(meta.last_page || 1);

        const makeBtn = (label, page, disabled = false, active = false) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = label;
            btn.disabled = disabled || nonStockBusy;
            btn.className = [
                'rounded-md border px-3 py-1 text-xs font-medium',
                active ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                (disabled || nonStockBusy) ? 'cursor-not-allowed opacity-50' : ''
            ].join(' ');
            btn.addEventListener('click', () => {
                if (nonStockBusy) return;
                nonStockCurrentPage = page;
                loadNonStock(page);
            });
            return btn;
        };

        nsPaginationNonStock.appendChild(makeBtn('Prev', Math.max(current - 1, 1), current <= 1));

        let start = Math.max(1, current - 2);
        let end = Math.min(last, current + 2);

        if (current <= 3) {
            end = Math.min(last, 5);
        }
        if (current >= last - 2) {
            start = Math.max(1, last - 4);
        }

        if (start > 1) {
            nsPaginationNonStock.appendChild(makeBtn('1', 1, false, current === 1));
            if (start > 2) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.className = 'px-1 text-xs text-gray-500';
                nsPaginationNonStock.appendChild(dots);
            }
        }

        for (let i = start; i <= end; i++) {
            nsPaginationNonStock.appendChild(makeBtn(String(i), i, false, current === i));
        }

        if (end < last) {
            if (end < last - 1) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.className = 'px-1 text-xs text-gray-500';
                nsPaginationNonStock.appendChild(dots);
            }
            nsPaginationNonStock.appendChild(makeBtn(String(last), last, false, current === last));
        }

        nsPaginationNonStock.appendChild(makeBtn('Next', Math.min(current + 1, last), current >= last));
    }

    function setBusyNonStock(isBusy, title = 'Processing...', sub = 'Mohon tunggu, jangan klik menu/tab.') {
        nonStockBusy = isBusy;
        nonStockBusyOverlay.classList.toggle('hidden', !isBusy);
        nonStockBusyTitle.textContent = title;
        nonStockBusySub.textContent = sub;

        btnLoadNonStock.disabled = isBusy;
        btnProcessNonStock.disabled = isBusy;
        nsFromNonStock.disabled = isBusy;
        nsToNonStock.disabled = isBusy;
        nsStatusNonStock.disabled = isBusy;
        nsPerPageNonStock.disabled = isBusy;
        nsChkAllNonStock.disabled = isBusy || nsTbodyNonStock.querySelectorAll('.nsRowChk:not(:disabled)').length === 0;

        nsTbodyNonStock.querySelectorAll('.nsRowChk').forEach(chk => chk.disabled = isBusy || chk.dataset.stage === 'C');
        getNonStockTabEls().forEach(el => {
            el.disabled = isBusy;
            el.classList.toggle('pointer-events-none', isBusy);
            el.classList.toggle('opacity-60', isBusy);
            if (isBusy) {
                el.setAttribute('aria-disabled', 'true');
                el.title = 'Tidak bisa pindah tab/menu saat proses Non Stock berjalan.';
            } else {
                el.removeAttribute('aria-disabled');
                el.removeAttribute('title');
            }
        });
    }

    // default tanggal
    // const todayNonStock = new Date();
    // const yyyyNonStock = todayNonStock.getFullYear();
    // const mmNonStock = String(todayNonStock.getMonth() + 1).padStart(2, '0');
    // const ddNonStock = String(todayNonStock.getDate()).padStart(2, '0');
    // nsToNonStock.value = `${yyyyNonStock}-${mmNonStock}-${ddNonStock}`;
    // nsFromNonStock.value = `${yyyyNonStock}-${mmNonStock}-01`;

    // default tanggal
    const formatDateNonStock = (date) => {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');

        return `${yyyy}-${mm}-${dd}`;
    };

    const todayNonStock = new Date();

    const fromDateNonStock = new Date(todayNonStock);
    fromDateNonStock.setDate(todayNonStock.getDate() - 30);

    nsFromNonStock.value = formatDateNonStock(fromDateNonStock);
    nsToNonStock.value = formatDateNonStock(todayNonStock);

    async function loadNonStock(page = 1) {
        hideInfoNonStock(nsInfoNonStock);

        if (!nsFromNonStock.value || !nsToNonStock.value) {
            setInfoNonStock(nsInfoNonStock, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        nonStockCurrentPage = page;
        nsTbodyNonStock.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        nsChkAllNonStock.disabled = true;
        nsChkAllNonStock.checked = false;
        nsChkAllNonStock.indeterminate = false;
        nsPaginationNonStock.innerHTML = '';

        const url = new URL("{{ route('integration.ifcaintegration.nonstock.list') }}", window.location.origin);
        url.searchParams.set('from', nsFromNonStock.value);
        url.searchParams.set('to', nsToNonStock.value);
        url.searchParams.set('status', nsStatusNonStock.value || '');
        url.searchParams.set('per_page', nsPerPageNonStock.value || '25');
        url.searchParams.set('page', String(page));

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                nsTotalNonStock.textContent = '0';
                nsShowingText.textContent = '';
                renderRowsNonStock([]);
                renderPaginationNonStock(null);
                setInfoNonStock(nsInfoNonStock, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            const meta = json.meta || { total: rows.length, from: rows.length ? 1 : 0, to: rows.length, current_page: 1, last_page: 1 };
            const summary = json.summary || { H: 0, P: 0, C: 0, ready: 0 };

            nsTotalNonStock.textContent = meta.total ?? rows.length;
            nsShowingText.textContent = meta.total > 0
                ? `(showing ${meta.from}-${meta.to} of ${meta.total})`
                : '';

            renderRowsNonStock(rows);
            renderPaginationNonStock(meta);

            setInfoNonStock(
                nsInfoNonStock,
                'ok',
                `Loaded ${meta.total ?? rows.length} Non Stock. Ready(H/P): ${summary.ready ?? 0}. Holding(H): ${summary.H ?? 0}. Pending(P): ${summary.P ?? 0}. Completed(C): ${summary.C ?? 0}.`
            );
        } catch (e) {
            nsTotalNonStock.textContent = '0';
            nsShowingText.textContent = '';
            renderRowsNonStock([]);
            renderPaginationNonStock(null);
            setInfoNonStock(nsInfoNonStock, 'err', e.message ?? 'Error saat load.');
        }
    }

    btnLoadNonStock.addEventListener('click', () => loadNonStock(1));

    nsStatusNonStock.addEventListener('change', () => loadNonStock(1));
    nsPerPageNonStock.addEventListener('change', () => loadNonStock(1));

    btnProcessNonStock.addEventListener('click', async () => {
        hideInfoNonStock(nsInfoNonStock);

        const ids = Array.from(nsTbodyNonStock.querySelectorAll('.nsRowChk:checked'))
            .filter(chk => chk.dataset.stage !== 'C')
            .map(chk => parseInt(chk.value, 10))
            .filter(n => !Number.isNaN(n));

        if (ids.length === 0) {
            setInfoNonStock(nsInfoNonStock, 'warn', 'Pilih minimal 1 item status H/P untuk diproses. Item C diabaikan.');
            return;
        }

        try {
            setBusyNonStock(true, 'Processing Non Stock...', 'Sedang kirim data ke staging/API. Mohon tunggu.');

            const resp = await fetch("{{ route('integration.ifcaintegration.nonstock.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfNonStock
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();
            if (!resp.ok || !json.ok) {
                setInfoNonStock(nsInfoNonStock, 'err', json.message ?? 'Gagal process.');
                return;
            }

            setInfoNonStock(nsInfoNonStock, 'ok',
                `Process done. Inserted: ${json.inserted_H_to_P ?? 0}, Sent OK: ${json.sent_success_P_to_C ?? 0}, Failed: ${json.sent_failed_still_P ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );

            await loadNonStock(nonStockCurrentPage);
        } catch (e) {
            setInfoNonStock(nsInfoNonStock, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusyNonStock(false);
        }
    });
</script>
