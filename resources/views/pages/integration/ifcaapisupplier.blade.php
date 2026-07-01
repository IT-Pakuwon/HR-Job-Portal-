<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="supplierBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
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
                <div class="font-semibold text-gray-800" id="supplierBusyTitle">Processing...</div>
                <div class="text-gray-500" id="supplierBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-6">
    <div>
        <label class="text-sm font-medium text-gray-600">Start Date</label>
        <input type="date" id="sp_from"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">End Date</label>
        <input type="date" id="sp_to"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Status</label>
        <select id="sp_status"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="H">H</option>
            <option value="P">P</option>
            <option value="C">C</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Show</label>
        <select id="sp_per_page"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnLoadSupplier"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
            Load
        </button>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnProcessSupplier"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
            Process
        </button>
    </div>
</div>

<div id="spInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="spTotal">0</span>
            <span class="ml-2 text-gray-500" id="spShowingText"></span>
        </div>
        <div class="text-sm text-gray-500">Pagination enabled</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2 align-middle">
                    <input type="checkbox" id="spChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-44 px-3 py-2 align-middle">Supplier Code</th>
                <th class="w-72 px-3 py-2 align-middle">Supplier Name</th>
                <th class="w-56 px-3 py-2 align-middle">NPWP</th>
                <th class="w-24 px-3 py-2 align-middle">Status</th>
                <th class="w-[420px] px-3 py-2 align-middle">Response</th>
                <th class="w-40 px-3 py-2 align-middle">Last Update</th>
            </tr>
            </thead>
            <tbody id="spTbody" class="divide-y divide-gray-100">
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

        <div id="spPagination" class="flex flex-wrap items-center gap-2"></div>
    </div>
</div>

<script>
    const csrfSupplier = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const spFromSupplier       = document.getElementById('sp_from');
    const spToSupplier         = document.getElementById('sp_to');
    const spStatusSupplier     = document.getElementById('sp_status');
    const spPerPageSupplier    = document.getElementById('sp_per_page');

    const spTbodySupplier      = document.getElementById('spTbody');
    const spTotalSupplier      = document.getElementById('spTotal');
    const spShowingText        = document.getElementById('spShowingText');
    const spInfoSupplier       = document.getElementById('spInfo');
    const spChkAllSupplier     = document.getElementById('spChkAll');
    const spPaginationSupplier = document.getElementById('spPagination');

    const btnLoadSupplier      = document.getElementById('btnLoadSupplier');
    const btnProcessSupplier   = document.getElementById('btnProcessSupplier');

    const supplierBusyOverlay  = document.getElementById('supplierBusyOverlay');
    const supplierBusyTitle    = document.getElementById('supplierBusyTitle');
    const supplierBusySub      = document.getElementById('supplierBusySub');

    let supplierBusy = false;
    let supplierCurrentPage = 1;

    function getSupplierTabEls() {
        return Array.from(document.querySelectorAll('a, button'))
            .filter(el => {
                if (!el || el === btnLoadSupplier || el === btnProcessSupplier) return false;
                const txt = (el.textContent || '').trim().toLowerCase();
                return ['non stock','stock','supplier','po','grn','sttb','bast','issue','receipt'].some(k => txt === k || txt.includes(k));
            });
    }

    function setInfoSupplier(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function hideInfoSupplier(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    function getStatusBadgeClassSupplier(stage) {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
    }

    function syncChkAllStateSupplier() {
        const enabled = Array.from(spTbodySupplier.querySelectorAll('.spRowChk:not(:disabled)'));
        if (enabled.length === 0) {
            spChkAllSupplier.checked = false;
            spChkAllSupplier.indeterminate = false;
            spChkAllSupplier.disabled = true;
            return;
        }

        spChkAllSupplier.disabled = false;

        const checkedEnabled = enabled.filter(chk => chk.checked).length;
        spChkAllSupplier.checked = checkedEnabled === enabled.length;
        spChkAllSupplier.indeterminate = checkedEnabled > 0 && checkedEnabled < enabled.length;
    }

    spChkAllSupplier.addEventListener('change', () => {
        if (supplierBusy) return;
        spTbodySupplier.querySelectorAll('.spRowChk:not(:disabled)').forEach(chk => chk.checked = spChkAllSupplier.checked);
        syncChkAllStateSupplier();
    });

    function renderRowsSupplier(rows) {
        if (!rows.length) {
            spTbodySupplier.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            spChkAllSupplier.checked = false;
            spChkAllSupplier.indeterminate = false;
            spChkAllSupplier.disabled = true;
            return;
        }

        spTbodySupplier.innerHTML = rows.map(r => {
            const stage = r.stage_status ?? 'H';
            const isC = stage === 'C';

            const trClass = isC ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const checkboxClass = isC ? 'opacity-40 cursor-not-allowed' : '';
            const title = isC ? 'Sudah completed (C). Tidak bisa diproses.' : '';

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2 align-top">
                        <input type="checkbox"
                               class="spRowChk rounded border-gray-300 ${checkboxClass}"
                               value="${r.id}"
                               data-stage="${stage}"
                               ${isC ? `disabled title="${title}"` : ''}>
                    </td>
                    <td class="px-3 py-2 align-top font-medium">${r.vendor_id ?? ''}</td>
                    <td class="px-3 py-2 align-top break-words">${r.vendor_name ?? ''}</td>
                    <td class="px-3 py-2 align-top break-words text-gray-700">${r.npwp ?? ''}</td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassSupplier(stage)}">
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

        spTbodySupplier.querySelectorAll('.spRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', () => {
                if (supplierBusy) return;
                syncChkAllStateSupplier();
            });
        });

        if (supplierBusy) {
            spTbodySupplier.querySelectorAll('.spRowChk').forEach(chk => chk.disabled = true);
        }

        syncChkAllStateSupplier();
    }

    function renderPaginationSupplier(meta) {
        spPaginationSupplier.innerHTML = '';

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
                    if (supplierBusy) return;
                    loadSupplier(page);
                });
            }

            return btn;
        };

        spPaginationSupplier.appendChild(makeBtn('Prev', current - 1, current <= 1));

        let start = Math.max(1, current - 2);
        let end = Math.min(last, current + 2);

        if (current <= 3) end = Math.min(last, 5);
        if (current >= last - 2) start = Math.max(1, last - 4);

        for (let i = start; i <= end; i++) {
            spPaginationSupplier.appendChild(makeBtn(String(i), i, false, i === current));
        }

        spPaginationSupplier.appendChild(makeBtn('Next', current + 1, current >= last));
    }

    function setBusySupplier(isBusy, title = 'Processing...', sub = 'Mohon tunggu, jangan klik menu/tab.') {
        supplierBusy = isBusy;

        if (isBusy) {
            supplierBusyTitle.textContent = title;
            supplierBusySub.textContent = sub;
            supplierBusyOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            supplierBusyOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        spFromSupplier.disabled = isBusy;
        spToSupplier.disabled = isBusy;
        spStatusSupplier.disabled = isBusy;
        spPerPageSupplier.disabled = isBusy;
        btnLoadSupplier.disabled = isBusy;
        btnProcessSupplier.disabled = isBusy;
        spChkAllSupplier.disabled = isBusy;

        const tabs = getSupplierTabEls();
        tabs.forEach(el => {
            if (isBusy) {
                if (el.dataset._supplier_prev_pointer == null) el.dataset._supplier_prev_pointer = el.style.pointerEvents || '';
                if (el.dataset._supplier_prev_opacity == null) el.dataset._supplier_prev_opacity = el.style.opacity || '';
                el.style.pointerEvents = 'none';
                el.style.opacity = '0.6';
            } else {
                el.style.pointerEvents = el.dataset._supplier_prev_pointer ?? '';
                el.style.opacity = el.dataset._supplier_prev_opacity ?? '';
                delete el.dataset._supplier_prev_pointer;
                delete el.dataset._supplier_prev_opacity;
            }
        });

        const rowChks = spTbodySupplier.querySelectorAll('.spRowChk');
        rowChks.forEach(chk => {
            if (isBusy) {
                chk.disabled = true;
                return;
            }

            const stage = String(chk.dataset.stage ?? '').toUpperCase();
            chk.disabled = stage === 'C';
            if (chk.disabled) chk.checked = false;
        });

        syncChkAllStateSupplier();

        if (isBusy) {
            btnLoadSupplier.dataset._txt = btnLoadSupplier.textContent;
            btnProcessSupplier.dataset._txt = btnProcessSupplier.textContent;
            btnLoadSupplier.textContent = 'Loading...';
            btnProcessSupplier.textContent = 'Processing...';
        } else {
            if (btnLoadSupplier.dataset._txt) btnLoadSupplier.textContent = btnLoadSupplier.dataset._txt;
            if (btnProcessSupplier.dataset._txt) btnProcessSupplier.textContent = btnProcessSupplier.dataset._txt;
        }
    }

    async function loadSupplier(page = 1) {
        hideInfoSupplier(spInfoSupplier);

        if (!spFromSupplier.value || !spToSupplier.value) {
            setInfoSupplier(spInfoSupplier, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        supplierCurrentPage = page;

        setBusySupplier(true, 'Loading Supplier...', 'Sedang mengambil data Supplier.');

        spTbodySupplier.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        spChkAllSupplier.disabled = true;
        spChkAllSupplier.checked = false;
        spChkAllSupplier.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.supplier.list') }}", window.location.origin);
        url.searchParams.set('from', spFromSupplier.value);
        url.searchParams.set('to', spToSupplier.value);
        url.searchParams.set('status', spStatusSupplier.value);
        url.searchParams.set('per_page', spPerPageSupplier.value);
        url.searchParams.set('page', page);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsSupplier([]);
                renderPaginationSupplier(null);
                spTotalSupplier.textContent = '0';
                spShowingText.textContent = '';
                setInfoSupplier(spInfoSupplier, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            const summary = json.summary || {};
            const meta = json.meta || {};

            renderRowsSupplier(rows);
            renderPaginationSupplier(meta);

            spTotalSupplier.textContent = meta.total ?? 0;
            spShowingText.textContent = meta.total > 0
                ? `(Showing ${meta.from} - ${meta.to})`
                : '';

            setInfoSupplier(
                spInfoSupplier,
                'ok',
                `Loaded ${meta.total ?? 0} Supplier. Ready(H/P): ${summary.ready ?? 0}. Holding(H): ${summary.H ?? 0}. Pending(P): ${summary.P ?? 0}. Completed(C): ${summary.C ?? 0}.`
            );
        } catch (e) {
            renderRowsSupplier([]);
            renderPaginationSupplier(null);
            spTotalSupplier.textContent = '0';
            spShowingText.textContent = '';
            setInfoSupplier(spInfoSupplier, 'err', e.message ?? 'Error saat load.');
        } finally {
            setBusySupplier(false);
        }
    }

    // (function initDefaultDatesSupplier() {
    //     const today = new Date();
    //     const yyyy = today.getFullYear();
    //     const mm = String(today.getMonth() + 1).padStart(2, '0');
    //     const dd = String(today.getDate()).padStart(2, '0');
    //     spToSupplier.value = `${yyyy}-${mm}-${dd}`;
    //     spFromSupplier.value = `${yyyy}-${mm}-01`;
    // })();

    (function initDefaultDatesSupplier() {
        const formatDate = (date) => {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        };

        const today = new Date();

        const fromDate = new Date(today);
        fromDate.setDate(today.getDate() - 30);

        spFromSupplier.value = formatDate(fromDate);
        spToSupplier.value = formatDate(today);
    })();

    btnLoadSupplier.addEventListener('click', async () => {
        if (supplierBusy) return;
        await loadSupplier(1);
    });

    spStatusSupplier.addEventListener('change', () => {
        if (supplierBusy) return;
        loadSupplier(1);
    });

    spPerPageSupplier.addEventListener('change', () => {
        if (supplierBusy) return;
        loadSupplier(1);
    });

    btnProcessSupplier.addEventListener('click', async () => {
        if (supplierBusy) return;

        hideInfoSupplier(spInfoSupplier);

        const ids = Array.from(spTbodySupplier.querySelectorAll('.spRowChk:checked'))
            .filter(chk => chk.dataset.stage !== 'C')
            .map(chk => parseInt(chk.value, 10))
            .filter(n => !Number.isNaN(n));

        if (ids.length === 0) {
            setInfoSupplier(spInfoSupplier, 'warn', 'Pilih minimal 1 Supplier status H/P untuk diproses. Supplier C diabaikan.');
            return;
        }

        setBusySupplier(true, 'Processing Supplier...', 'Sedang insert staging (H→P) dan/atau kirim ke API (P→C).');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.supplier.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfSupplier
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();
            if (!resp.ok || !json.ok) {
                setInfoSupplier(spInfoSupplier, 'err', json.message ?? 'Gagal process.');
                return;
            }

            setInfoSupplier(
                spInfoSupplier,
                'ok',
                `Process done. Inserted(H->P): ${json.inserted_H_to_P ?? 0}, Sent OK(P->C): ${json.sent_success_P_to_C ?? 0}, Failed(P): ${json.sent_failed_still_P ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );
        } catch (e) {
            setInfoSupplier(spInfoSupplier, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusySupplier(false);
        }

        await loadSupplier(supplierCurrentPage || 1);
    });
</script>
