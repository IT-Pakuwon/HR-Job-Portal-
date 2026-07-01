<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="poBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
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
                <div class="font-semibold text-gray-800" id="poBusyTitle">Processing...</div>
                <div class="text-gray-500" id="poBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-7">
    <div>
        <label class="text-sm font-medium text-gray-600">Start Date</label>
        <input type="date" id="po_from"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">End Date</label>
        <input type="date" id="po_to"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Company</label>
        <select id="po_company"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Company</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Status</label>
        <select id="po_status"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="H">H</option>
            <option value="D">D</option>
            <option value="P">P</option>
            <option value="C">C</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Show</label>
        <select id="po_per_page"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnLoadPO"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
            Load
        </button>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnProcessPO"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
            Process
        </button>
    </div>
</div>

<div id="poInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="poTotal">0</span>
            <span class="ml-2 text-gray-500" id="poShowingText"></span>
        </div>
        <div class="text-sm text-gray-500">Pagination enabled</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2 align-middle">
                    <input type="checkbox" id="poChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-32 px-3 py-2 align-middle">Integration Type</th>
                <th class="w-20 px-3 py-2 align-middle">Cpny</th>
                <th class="w-24 px-3 py-2 align-middle">Entity Cd</th>
                <th class="w-32 px-3 py-2 align-middle">Order No</th>
                <th class="w-32 px-3 py-2 align-middle">Order Date</th>
                <th class="w-40 px-3 py-2 align-middle">Department ID</th>
                <th class="w-24 px-3 py-2 align-middle">Status</th>
                <th class="w-[420px] px-3 py-2 align-middle">Response</th>
                <th class="w-40 px-3 py-2 align-middle">Last Update</th>
            </tr>
            </thead>
            <tbody id="poTbody" class="divide-y divide-gray-100">
            <tr>
                <td colspan="10" class="px-4 py-10 text-center text-gray-500">
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
            D = menunggu review,
            P-IFCA = siap kirim API,
            P-SOLOMON = reviewed Solomon,
            C = completed.
        </div>

        <div id="poPagination" class="flex flex-wrap items-center gap-2"></div>
    </div>
</div>

<script>
    const csrfPO = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const poFrom        = document.getElementById('po_from');
    const poTo          = document.getElementById('po_to');
    const poCompany     = document.getElementById('po_company');
    const poStatus      = document.getElementById('po_status');
    const poPerPage     = document.getElementById('po_per_page');

    const poTbody       = document.getElementById('poTbody');
    const poTotal       = document.getElementById('poTotal');
    const poShowingText = document.getElementById('poShowingText');
    const poInfo        = document.getElementById('poInfo');
    const poChkAll      = document.getElementById('poChkAll');
    const btnLoadPO     = document.getElementById('btnLoadPO');
    const btnProcessPO  = document.getElementById('btnProcessPO');
    const poPagination  = document.getElementById('poPagination');

    const poBusyOverlay = document.getElementById('poBusyOverlay');
    const poBusyTitle   = document.getElementById('poBusyTitle');
    const poBusySub     = document.getElementById('poBusySub');

    let poBusy = false;
    let poCurrentPage = 1;

    function getTabEls() {
        return Array.from(document.querySelectorAll('a, button'))
            .filter(el => {
                if (!el || el === btnLoadPO || el === btnProcessPO) return false;
                const txt = (el.textContent || '').trim().toLowerCase();
                return ['non stock','stock','supplier','po','grn','sttb','bast','issue','receipt'].some(k => txt === k || txt.includes(k));
            });
    }

    function setInfoPO(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        el.textContent = msg;
    }

    function hideInfoPO(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    function getStatusBadgeClassPO(stage, it = '') {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'D') return 'bg-blue-200 text-blue-800';
        if (stage === 'P' && it === 'SOLOMON') return 'bg-orange-200 text-orange-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
    }

    function getIntegrationTypeBadgeClassPO(it = '') {
        it = String(it || '').trim().toUpperCase();
        if (it === 'IFCA') return 'bg-indigo-100 text-indigo-800';
        if (it === 'SOLOMON') return 'bg-orange-100 text-orange-800';
        return 'bg-gray-100 text-gray-700';
    }

    function syncChkAllStatePO() {
        const enabled = Array.from(poTbody.querySelectorAll('.poRowChk:not(:disabled)'));
        if (enabled.length === 0) {
            poChkAll.checked = false;
            poChkAll.indeterminate = false;
            poChkAll.disabled = true;
            return;
        }

        poChkAll.disabled = false;

        const checkedEnabled = enabled.filter(chk => chk.checked).length;
        poChkAll.checked = checkedEnabled === enabled.length;
        poChkAll.indeterminate = checkedEnabled > 0 && checkedEnabled < enabled.length;
    }

    poChkAll.addEventListener('change', () => {
        if (poBusy) return;
        poTbody.querySelectorAll('.poRowChk:not(:disabled)').forEach(chk => chk.checked = poChkAll.checked);
        syncChkAllStatePO();
    });

    function renderRowsPO(rows) {
        if (!rows.length) {
            poTbody.innerHTML = `<tr><td colspan="10" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            poChkAll.checked = false;
            poChkAll.indeterminate = false;
            poChkAll.disabled = true;
            return;
        }

        poTbody.innerHTML = rows.map(r => {
            const stage = (r.stage_status ?? 'H');

            // untuk kolom Integration Type
            const itDisplay = String(r.integration_type ?? '').trim().toUpperCase();

            // untuk Status P-IFCA / P-SOLOMON dan checkbox/select
            const itStatus = String(r.status_integration_type ?? '').trim().toUpperCase();

            const stageLabel = (r.stage_label ?? stage);

            const disableByStage = (stage === 'C' || stage === 'D');
            const disablePSolomon = (stage === 'P' && itStatus !== 'IFCA');
            const disabled = disableByStage || disablePSolomon;

            const trClass = disabled ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const checkboxClass = disabled ? 'opacity-40 cursor-not-allowed' : '';

            let title = '';
            if (stage === 'C') title = 'Sudah completed (C). Tidak bisa diproses.';
            if (stage === 'D') title = 'Menunggu review (D). Tidak bisa diproses di screen ini.';
            if (disablePSolomon) title = 'P-SOLOMON tidak dikirim di screen ini.';

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2 align-top">
                        <input type="checkbox"
                            class="poRowChk rounded border-gray-300 ${checkboxClass}"
                            value="${r.key}"
                            data-stage="${stage}"
                            data-it="${itStatus}"
                            ${disabled ? `disabled title="${title}"` : ''}>
                    </td>

                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getIntegrationTypeBadgeClassPO(itDisplay)}">
                            ${itDisplay || '-'}
                        </span>
                    </td>

                    <td class="px-3 py-2 align-top font-medium">${r.cpny_id ?? ''}</td>
                    <td class="px-3 py-2 align-top">${r.entity_cd ?? ''}</td>
                    <td class="px-3 py-2 align-top font-medium">${r.order_no ?? ''}</td>
                    <td class="px-3 py-2 align-top whitespace-nowrap">${r.order_date ?? ''}</td>
                    <td class="px-3 py-2 align-top break-words">${r.department_id ?? ''}</td>

                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassPO(stage, itStatus)}">
                            ${stageLabel}
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

        poTbody.querySelectorAll('.poRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', () => {
                if (poBusy) return;
                syncChkAllStatePO();
            });
        });

        if (poBusy) {
            poTbody.querySelectorAll('.poRowChk').forEach(chk => chk.disabled = true);
        }

        syncChkAllStatePO();
    }

    function renderPaginationPO(meta) {
        poPagination.innerHTML = '';

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
                    if (poBusy) return;
                    loadPO(page);
                });
            }
            return btn;
        };

        poPagination.appendChild(makeBtn('Prev', current - 1, current <= 1));

        let start = Math.max(1, current - 2);
        let end = Math.min(last, current + 2);

        if (current <= 3) end = Math.min(last, 5);
        if (current >= last - 2) start = Math.max(1, last - 4);

        for (let i = start; i <= end; i++) {
            poPagination.appendChild(makeBtn(String(i), i, false, i === current));
        }

        poPagination.appendChild(makeBtn('Next', current + 1, current >= last));
    }

    function setBusyPO(isBusy, title = 'Processing...', sub = 'Mohon tunggu, jangan klik menu/tab.') {
        poBusy = isBusy;

        if (isBusy) {
            poBusyTitle.textContent = title;
            poBusySub.textContent = sub;
            poBusyOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            poBusyOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        poFrom.disabled = isBusy;
        poTo.disabled = isBusy;
        poCompany.disabled = isBusy;
        poStatus.disabled = isBusy;
        poPerPage.disabled = isBusy;
        btnLoadPO.disabled = isBusy;
        btnProcessPO.disabled = isBusy;
        poChkAll.disabled = isBusy;

        const tabs = getTabEls();
        tabs.forEach(el => {
            if (isBusy) {
                if (el.dataset._po_prev_pointer == null) el.dataset._po_prev_pointer = el.style.pointerEvents || '';
                if (el.dataset._po_prev_opacity == null) el.dataset._po_prev_opacity = el.style.opacity || '';
                el.style.pointerEvents = 'none';
                el.style.opacity = '0.6';
            } else {
                el.style.pointerEvents = el.dataset._po_prev_pointer ?? '';
                el.style.opacity = el.dataset._po_prev_opacity ?? '';
                delete el.dataset._po_prev_pointer;
                delete el.dataset._po_prev_opacity;
            }
        });

        const rowChks = poTbody.querySelectorAll('.poRowChk');
        rowChks.forEach(chk => {
            if (isBusy) {
                chk.disabled = true;
                return;
            }

            const stage = String(chk.dataset.stage ?? '').toUpperCase();
            const it    = String(chk.dataset.it ?? '').trim().toUpperCase();

            const disableByStage = (stage === 'C' || stage === 'D');
            const disablePSolomon = (stage === 'P' && it !== 'IFCA');

            chk.disabled = disableByStage || disablePSolomon;
            if (chk.disabled) chk.checked = false;
        });

        syncChkAllStatePO();

        if (isBusy) {
            btnLoadPO.dataset._txt = btnLoadPO.textContent;
            btnProcessPO.dataset._txt = btnProcessPO.textContent;
            btnLoadPO.textContent = 'Loading...';
            btnProcessPO.textContent = 'Processing...';
        } else {
            if (btnLoadPO.dataset._txt) btnLoadPO.textContent = btnLoadPO.dataset._txt;
            if (btnProcessPO.dataset._txt) btnProcessPO.textContent = btnProcessPO.dataset._txt;
        }
    }

    async function loadPOFilters() {
        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.po.filters') }}", {
                headers: { 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (!resp.ok || !json.ok) return;

            const companies = json.data?.companies || [];
            poCompany.innerHTML = `<option value="">All Company</option>` +
                companies.map(c => `<option value="${c}">${c}</option>`).join('');
        } catch (e) {
            console.error('Failed load PO filters', e);
        }
    }

    async function loadPO(page = 1) {
        hideInfoPO(poInfo);

        if (!poFrom.value || !poTo.value) {
            setInfoPO(poInfo, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        poCurrentPage = page;

        setBusyPO(true, 'Loading PO...', 'Sedang mengambil data PO dari Purchasing.');

        poTbody.innerHTML = `<tr><td colspan="10" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        poChkAll.disabled = true;
        poChkAll.checked = false;
        poChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.po.list') }}", window.location.origin);
        url.searchParams.set('from', poFrom.value);
        url.searchParams.set('to', poTo.value);
        url.searchParams.set('company', poCompany.value);
        url.searchParams.set('status', poStatus.value);
        url.searchParams.set('per_page', poPerPage.value);
        url.searchParams.set('page', page);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsPO([]);
                poPagination.innerHTML = '';
                poTotal.textContent = '0';
                poShowingText.textContent = '';
                setInfoPO(poInfo, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            const summary = json.summary || {};
            const meta = json.meta || {};

            renderRowsPO(rows);
            renderPaginationPO(meta);

            poTotal.textContent = meta.total ?? 0;
            poShowingText.textContent = meta.total > 0
                ? `(Showing ${meta.from} - ${meta.to})`
                : '';

            setInfoPO(
                poInfo,
                'ok',
                `Loaded ${meta.total ?? 0} PO. Ready(H/P-IFCA): ${summary.ready ?? 0}. Waiting Review(D): ${summary.D ?? 0}. Pending(P): ${summary.P ?? 0}. Completed(C): ${summary.C ?? 0}.`
            );
        } catch (e) {
            renderRowsPO([]);
            poPagination.innerHTML = '';
            poTotal.textContent = '0';
            poShowingText.textContent = '';
            setInfoPO(poInfo, 'err', e.message ?? 'Error saat load.');
        } finally {
            setBusyPO(false);
        }
    }

    // (function initDefaultDatesPO() {
    //     const todayPO = new Date();
    //     const yyyyPO = todayPO.getFullYear();
    //     const mmPO = String(todayPO.getMonth() + 1).padStart(2, '0');
    //     const ddPO = String(todayPO.getDate()).padStart(2, '0');
    //     poTo.value = `${yyyyPO}-${mmPO}-${ddPO}`;
    //     poFrom.value = `${yyyyPO}-${mmPO}-01`;
    // })();

    (function initDefaultDatesPO() {
        const formatDatePO = (date) => {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');

            return `${yyyy}-${mm}-${dd}`;
        };

        const todayPO = new Date();

        const fromDatePO = new Date(todayPO);
        fromDatePO.setDate(todayPO.getDate() - 30);

        poFrom.value = formatDatePO(fromDatePO);
        poTo.value = formatDatePO(todayPO);
    })();

    btnLoadPO.addEventListener('click', async () => {
        if (poBusy) return;
        await loadPO(1);
    });

    poCompany.addEventListener('change', () => {
        if (poBusy) return;
        loadPO(1);
    });

    poStatus.addEventListener('change', () => {
        if (poBusy) return;
        loadPO(1);
    });

    poPerPage.addEventListener('change', () => {
        if (poBusy) return;
        loadPO(1);
    });

    btnProcessPO.addEventListener('click', async () => {
        if (poBusy) return;

        hideInfoPO(poInfo);

        const ids = Array.from(poTbody.querySelectorAll('.poRowChk:checked'))
            .filter(chk => chk.dataset.stage === 'H' || (chk.dataset.stage === 'P' && chk.dataset.it === 'IFCA'))
            .map(chk => chk.value);

        if (ids.length === 0) {
            setInfoPO(poInfo, 'warn', 'Pilih minimal 1 PO status H atau P-IFCA untuk diproses.');
            return;
        }

        setBusyPO(true, 'Processing PO...', 'Sedang insert (H→D) dan/atau kirim ke API (P→C).');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.po.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfPO
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                setInfoPO(poInfo, 'err', json.message ?? 'Gagal process.');
                return;
            }

            setInfoPO(
                poInfo,
                'ok',
                `Process done. Inserted(H->D lines): ${json.inserted_H_to_D ?? 0}, Sent OK(P->C orders): ${json.sent_success_P_to_C ?? 0}, Failed(P): ${json.sent_failed_still_P ?? 0}, Skipped(D): ${json.skipped_D ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );
        } catch (e) {
            setInfoPO(poInfo, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusyPO(false);
        }

        await loadPO(poCurrentPage || 1);
    });

    document.addEventListener('DOMContentLoaded', async () => {
        await loadPOFilters();
    });
</script>