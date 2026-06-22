<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="grnreturnBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
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
                <div class="font-semibold text-gray-800" id="grnreturnBusyTitle">Processing...</div>
                <div class="text-gray-500" id="grnreturnBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-7">
    <div>
        <label class="text-sm font-medium text-gray-600">Start Date</label>
        <input type="date" id="grnreturn_from"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">End Date</label>
        <input type="date" id="grnreturn_to"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Company</label>
        <select id="grnreturn_company"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Company</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Status</label>
        <select id="grnreturn_status"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="H">H</option>
            <option value="P">P</option>
            <option value="C">C</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Show</label>
        <select id="grnreturn_per_page"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnLoadGRNReturn"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
            Load
        </button>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnProcessGRNReturn"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
            Process
        </button>
    </div>
</div>

<div id="grnreturnInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="grnreturnTotal">0</span>
            <span class="ml-2 text-gray-500" id="grnreturnShowingText"></span>
        </div>
        <div class="text-sm text-gray-500">Pagination enabled</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2 align-middle">
                    <input type="checkbox" id="grnreturnChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-32 px-3 py-2 align-middle">Integration Type</th>
                <th class="w-20 px-3 py-2 align-middle">Cpny</th>
                <th class="w-24 px-3 py-2 align-middle">Entity Cd</th>
                <th class="w-32 px-3 py-2 align-middle">Return No</th>
                <th class="w-28 px-3 py-2 align-middle">Return Date</th>
                <th class="w-32 px-3 py-2 align-middle">GRN No</th>
                <th class="w-44 px-3 py-2 align-middle">Department ID</th>
                <th class="w-24 px-3 py-2 align-middle">Status</th>
                <th class="w-[420px] px-3 py-2 align-middle">Response</th>
                <th class="w-40 px-3 py-2 align-middle">Last Update</th>
            </tr>
            </thead>
            <tbody id="grnreturnTbody" class="divide-y divide-gray-100">
            <tr>
                <td colspan="11" class="px-4 py-10 text-center text-gray-500">
                    Belum ada data. Klik Load.
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 border-t border-gray-200 bg-white px-4 py-3 md:flex-row md:items-center md:justify-between">
        <div class="text-xs text-gray-500">
            <span class="font-semibold">Legend:</span>
            H = semua bisa dicentang,
            P = hanya IFCA yang bisa dicentang,
            C = completed.
        </div>

        <div id="grnreturnPagination" class="flex flex-wrap items-center gap-2"></div>
    </div>
</div>

<script>
    const csrfGRNReturn = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const grnreturnFrom        = document.getElementById('grnreturn_from');
    const grnreturnTo          = document.getElementById('grnreturn_to');
    const grnreturnCompany     = document.getElementById('grnreturn_company');
    const grnreturnStatus      = document.getElementById('grnreturn_status');
    const grnreturnPerPage     = document.getElementById('grnreturn_per_page');

    const grnreturnTbody       = document.getElementById('grnreturnTbody');
    const grnreturnTotal       = document.getElementById('grnreturnTotal');
    const grnreturnShowingText = document.getElementById('grnreturnShowingText');
    const grnreturnInfo        = document.getElementById('grnreturnInfo');
    const grnreturnChkAll      = document.getElementById('grnreturnChkAll');
    const btnLoadGRNReturn     = document.getElementById('btnLoadGRNReturn');
    const btnProcessGRNReturn  = document.getElementById('btnProcessGRNReturn');
    const grnreturnPagination  = document.getElementById('grnreturnPagination');

    const grnreturnBusyOverlay = document.getElementById('grnreturnBusyOverlay');
    const grnreturnBusyTitle   = document.getElementById('grnreturnBusyTitle');
    const grnreturnBusySub     = document.getElementById('grnreturnBusySub');

    let grnreturnBusy = false;
    let grnreturnCurrentPage = 1;

    function setInfoGRNReturn(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function hideInfoGRNReturn(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    function getStatusBadgeClassGRNReturn(stage) {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
    }

    function getIntegrationTypeBadgeClassGRNReturn(it = '') {
        it = String(it || '').trim().toUpperCase();
        if (it === 'IFCA') return 'bg-indigo-100 text-indigo-800';
        if (it === 'SOLOMON') return 'bg-orange-100 text-orange-800';
        return 'bg-gray-100 text-gray-700';
    }

    // RULE FINAL:
    // H + IFCA ✅
    // H + SOLOMON ✅
    // H + kosong ✅
    // P + IFCA ✅
    // P + SOLOMON ❌
    // P + kosong ❌
    // C ❌
    function isRowSelectableGRNReturn(stage, it) {
        stage = String(stage || '').toUpperCase();
        it = String(it || '').toUpperCase();

        if (stage === 'C') return false;
        if (stage === 'H') return true;
        if (stage === 'P' && it === 'IFCA') return true;

        return false;
    }

    function syncChkAllStateGRNReturn() {
        const enabled = Array.from(grnreturnTbody.querySelectorAll('.grnreturnRowChk:not(:disabled)'));
        if (enabled.length === 0) {
            grnreturnChkAll.checked = false;
            grnreturnChkAll.indeterminate = false;
            grnreturnChkAll.disabled = true;
            return;
        }

        grnreturnChkAll.disabled = false;

        const checkedEnabled = enabled.filter(chk => chk.checked).length;
        grnreturnChkAll.checked = checkedEnabled === enabled.length;
        grnreturnChkAll.indeterminate = checkedEnabled > 0 && checkedEnabled < enabled.length;
    }

    grnreturnChkAll.addEventListener('change', () => {
        if (grnreturnBusy) return;
        grnreturnTbody.querySelectorAll('.grnreturnRowChk:not(:disabled)').forEach(chk => chk.checked = grnreturnChkAll.checked);
        syncChkAllStateGRNReturn();
    });

    function renderRowsGRNReturn(rows) {
        if (!rows.length) {
            grnreturnTbody.innerHTML = `<tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            grnreturnChkAll.checked = false;
            grnreturnChkAll.indeterminate = false;
            grnreturnChkAll.disabled = true;
            return;
        }

        grnreturnTbody.innerHTML = rows.map(r => {
            const stage = String(r.stage_status ?? 'H').toUpperCase();
            const it = String(r.integration_type ?? '').trim().toUpperCase();
            const selectable = isRowSelectableGRNReturn(stage, it);
            const disabled = !selectable;

            const trClass = disabled ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const checkboxClass = disabled ? 'opacity-40 cursor-not-allowed' : '';

            let title = '';
            if (stage === 'C') {
                title = 'Sudah completed (C). Tidak bisa diproses.';
            } else if (stage === 'P' && it !== 'IFCA') {
                title = 'Status P hanya bisa dicentang jika Integration Type = IFCA.';
            }

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2 align-top">
                        <input type="checkbox"
                               class="grnreturnRowChk rounded border-gray-300 ${checkboxClass}"
                               value="${r.key}"
                               data-stage="${stage}"
                               data-it="${it}"
                               ${disabled ? `disabled title="${title}"` : ''}>
                    </td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getIntegrationTypeBadgeClassGRNReturn(it)}">
                            ${it || '-'}
                        </span>
                    </td>
                    <td class="px-3 py-2 align-top font-medium">${r.cpny_id ?? ''}</td>
                    <td class="px-3 py-2 align-top">${r.entity_cd ?? ''}</td>
                    <td class="px-3 py-2 align-top font-medium">${r.return_no ?? ''}</td>
                    <td class="px-3 py-2 align-top whitespace-nowrap">${r.return_date ?? ''}</td>
                    <td class="px-3 py-2 align-top">${r.grn_no ?? ''}</td>
                    <td class="px-3 py-2 align-top break-words">${r.department_id ?? ''}</td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassGRNReturn(stage)}">
                            ${r.stage_label ?? stage}
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

        grnreturnTbody.querySelectorAll('.grnreturnRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', () => {
                if (grnreturnBusy) return;
                syncChkAllStateGRNReturn();
            });
        });

        if (grnreturnBusy) {
            grnreturnTbody.querySelectorAll('.grnreturnRowChk').forEach(chk => chk.disabled = true);
        }

        syncChkAllStateGRNReturn();
    }

    function renderPaginationGRNReturn(meta) {
        grnreturnPagination.innerHTML = '';

        if (!meta || Number(meta.last_page || 1) <= 1) {
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
                    if (grnreturnBusy) return;
                    loadGRNReturn(page);
                });
            }

            return btn;
        };

        grnreturnPagination.appendChild(makeBtn('Prev', current - 1, current <= 1));

        let start = Math.max(1, current - 2);
        let end = Math.min(last, current + 2);

        if (current <= 3) end = Math.min(last, 5);
        if (current >= last - 2) start = Math.max(1, last - 4);

        for (let i = start; i <= end; i++) {
            grnreturnPagination.appendChild(makeBtn(String(i), i, false, i === current));
        }

        grnreturnPagination.appendChild(makeBtn('Next', current + 1, current >= last));
    }

    function setBusyGRNReturn(isBusy, title = 'Processing...', sub = 'Mohon tunggu, jangan klik menu/tab.') {
        grnreturnBusy = isBusy;

        if (isBusy) {
            grnreturnBusyTitle.textContent = title;
            grnreturnBusySub.textContent = sub;
            grnreturnBusyOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            grnreturnBusyOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        grnreturnFrom.disabled = isBusy;
        grnreturnTo.disabled = isBusy;
        grnreturnCompany.disabled = isBusy;
        grnreturnStatus.disabled = isBusy;
        grnreturnPerPage.disabled = isBusy;
        btnLoadGRNReturn.disabled = isBusy;
        btnProcessGRNReturn.disabled = isBusy;
        grnreturnChkAll.disabled = isBusy;

        grnreturnTbody.querySelectorAll('.grnreturnRowChk').forEach(chk => {
            if (isBusy) {
                chk.disabled = true;
                return;
            }

            const stage = String(chk.dataset.stage ?? '').toUpperCase();
            const it = String(chk.dataset.it ?? '').toUpperCase();

            chk.disabled = !isRowSelectableGRNReturn(stage, it);
            if (chk.disabled) chk.checked = false;
        });

        syncChkAllStateGRNReturn();

        if (isBusy) {
            btnLoadGRNReturn.dataset._txt = btnLoadGRNReturn.textContent;
            btnProcessGRNReturn.dataset._txt = btnProcessGRNReturn.textContent;
            btnLoadGRNReturn.textContent = 'Loading...';
            btnProcessGRNReturn.textContent = 'Processing...';
        } else {
            if (btnLoadGRNReturn.dataset._txt) btnLoadGRNReturn.textContent = btnLoadGRNReturn.dataset._txt;
            if (btnProcessGRNReturn.dataset._txt) btnProcessGRNReturn.textContent = btnProcessGRNReturn.dataset._txt;
        }
    }

    async function loadGRNReturnFilters() {
        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.grnreturn.filters') }}", {
                headers: { 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (!resp.ok || !json.ok) return;

            const companies = json.data?.companies || [];
            grnreturnCompany.innerHTML = `<option value="">All Company</option>` +
                companies.map(c => `<option value="${c}">${c}</option>`).join('');
        } catch (e) {
            console.error('Failed load GRN Return filters', e);
        }
    }

    async function loadGRNReturn(page = 1) {
        hideInfoGRNReturn(grnreturnInfo);

        if (!grnreturnFrom.value || !grnreturnTo.value) {
            setInfoGRNReturn(grnreturnInfo, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        grnreturnCurrentPage = page;

        setBusyGRNReturn(true, 'Loading GRN Return...', 'Sedang mengambil data GRN Return dari Purchasing.');

        grnreturnTbody.innerHTML = `<tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        grnreturnChkAll.disabled = true;
        grnreturnChkAll.checked = false;
        grnreturnChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.grnreturn.list') }}", window.location.origin);
        url.searchParams.set('from', grnreturnFrom.value);
        url.searchParams.set('to', grnreturnTo.value);
        url.searchParams.set('company', grnreturnCompany.value);
        url.searchParams.set('status', grnreturnStatus.value);
        url.searchParams.set('per_page', grnreturnPerPage.value);
        url.searchParams.set('page', page);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsGRNReturn([]);
                renderPaginationGRNReturn(null);
                grnreturnTotal.textContent = '0';
                grnreturnShowingText.textContent = '';
                setInfoGRNReturn(grnreturnInfo, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            const summary = json.summary || {};
            const meta = json.meta || {};

            renderRowsGRNReturn(rows);
            renderPaginationGRNReturn(meta);

            grnreturnTotal.textContent = meta.total ?? 0;
            grnreturnShowingText.textContent = meta.total > 0
                ? `(Showing ${meta.from} - ${meta.to})`
                : '';

            setInfoGRNReturn(
                grnreturnInfo,
                'ok',
                `Loaded ${meta.total ?? 0} GRN Return. Ready(H + P-IFCA): ${summary.ready ?? 0}. Pending(P): ${summary.P ?? 0}. Completed(C): ${summary.C ?? 0}.`
            );
        } catch (e) {
            renderRowsGRNReturn([]);
            renderPaginationGRNReturn(null);
            grnreturnTotal.textContent = '0';
            grnreturnShowingText.textContent = '';
            setInfoGRNReturn(grnreturnInfo, 'err', e.message ?? 'Error saat load.');
        } finally {
            setBusyGRNReturn(false);
        }
    }

    (function initDefaultDatesGRNReturn() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        grnreturnTo.value = `${yyyy}-${mm}-${dd}`;
        grnreturnFrom.value = `${yyyy}-${mm}-01`;
    })();

    btnLoadGRNReturn.addEventListener('click', async () => {
        if (grnreturnBusy) return;
        await loadGRNReturn(1);
    });

    grnreturnCompany.addEventListener('change', () => {
        if (grnreturnBusy) return;
        loadGRNReturn(1);
    });

    grnreturnStatus.addEventListener('change', () => {
        if (grnreturnBusy) return;
        loadGRNReturn(1);
    });

    grnreturnPerPage.addEventListener('change', () => {
        if (grnreturnBusy) return;
        loadGRNReturn(1);
    });

    btnProcessGRNReturn.addEventListener('click', async () => {
        if (grnreturnBusy) return;

        hideInfoGRNReturn(grnreturnInfo);

        const ids = Array.from(grnreturnTbody.querySelectorAll('.grnreturnRowChk:checked'))
            .filter(chk => {
                const stage = String(chk.dataset.stage ?? '').toUpperCase();
                const it = String(chk.dataset.it ?? '').toUpperCase();
                return isRowSelectableGRNReturn(stage, it);
            })
            .map(chk => chk.value);

        if (ids.length === 0) {
            setInfoGRNReturn(grnreturnInfo, 'warn', 'Pilih minimal 1 GRN Return yang valid untuk diproses.');
            return;
        }

        setBusyGRNReturn(true, 'Processing GRN Return...', 'Sedang insert (H→P) dan/atau kirim ke API (P→C).');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.grnreturn.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfGRNReturn
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                setInfoGRNReturn(grnreturnInfo, 'err', json.message ?? 'Gagal process.');
                return;
            }

            setInfoGRNReturn(
                grnreturnInfo,
                'ok',
                `Process done. Inserted(H->P lines): ${json.inserted_H_to_P ?? 0}, Sent OK(P->C GRN Return): ${json.sent_success_P_to_C ?? 0}, Failed(P): ${json.sent_failed_still_P ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );
        } catch (e) {
            setInfoGRNReturn(grnreturnInfo, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusyGRNReturn(false);
        }

        await loadGRNReturn(grnreturnCurrentPage || 1);
    });

    loadGRNReturnFilters();
</script>