<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="bastBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
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
                <div class="font-semibold text-gray-800" id="bastBusyTitle">Processing...</div>
                <div class="text-gray-500" id="bastBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-7">
    <div>
        <label class="text-sm font-medium text-gray-600">Start Date</label>
        <input type="date" id="bast_from"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">End Date</label>
        <input type="date" id="bast_to"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Company</label>
        <select id="bast_company"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Company</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Status</label>
        <select id="bast_status"
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
        <select id="bast_per_page"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnLoadBAST"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
            Load
        </button>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnProcessBAST"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
            Process
        </button>
    </div>
</div>

<div id="bastInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="bastTotal">0</span>
            <span class="ml-2 text-gray-500" id="bastShowingText"></span>
        </div>
        <div class="text-sm text-gray-500">Pagination enabled</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2 align-middle">
                    <input type="checkbox" id="bastChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-32 px-3 py-2 align-middle">Integration Type</th>
                <th class="w-20 px-3 py-2 align-middle">Company</th>
                <th class="w-24 px-3 py-2 align-middle">Entity Cd</th>
                <th class="w-32 px-3 py-2 align-middle">BAST No</th>
                <th class="w-28 px-3 py-2 align-middle">BAST Date</th>
                <th class="w-32 px-3 py-2 align-middle">PO No</th>
                <th class="w-32 px-3 py-2 align-middle">Supplier</th>
                <th class="w-24 px-3 py-2 align-middle">Status</th>
                <th class="w-[420px] px-3 py-2 align-middle">Response</th>
                <th class="w-40 px-3 py-2 align-middle">Last Update</th>
            </tr>
            </thead>
            <tbody id="bastTbody" class="divide-y divide-gray-100">
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
            H = ready insert staging,
            D = revise/tidak diproses,
            P = ready kirim API,
            C = completed.
        </div>

        <div id="bastPagination" class="flex flex-wrap items-center gap-2"></div>
    </div>
</div>

<script>
    const csrfBAST = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const bastFrom        = document.getElementById('bast_from');
    const bastTo          = document.getElementById('bast_to');
    const bastCompany     = document.getElementById('bast_company');
    const bastStatus      = document.getElementById('bast_status');
    const bastPerPage     = document.getElementById('bast_per_page');

    const bastTbody       = document.getElementById('bastTbody');
    const bastTotal       = document.getElementById('bastTotal');
    const bastShowingText = document.getElementById('bastShowingText');
    const bastInfo        = document.getElementById('bastInfo');
    const bastChkAll      = document.getElementById('bastChkAll');
    const btnLoadBAST     = document.getElementById('btnLoadBAST');
    const btnProcessBAST  = document.getElementById('btnProcessBAST');
    const bastPagination  = document.getElementById('bastPagination');

    const bastBusyOverlay = document.getElementById('bastBusyOverlay');
    const bastBusyTitle   = document.getElementById('bastBusyTitle');
    const bastBusySub     = document.getElementById('bastBusySub');

    let bastBusy = false;
    let bastCurrentPage = 1;

    function escapeHtmlBAST(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function setInfoBAST(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function hideInfoBAST(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    function getStatusBadgeClassBAST(stage) {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'D') return 'bg-blue-200 text-blue-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
    }

    function getIntegrationTypeBadgeClassBAST(it = '') {
        it = String(it || '').trim().toUpperCase();
        if (it === 'IFCA') return 'bg-indigo-100 text-indigo-800';
        return 'bg-gray-100 text-gray-700';
    }

    function isRowSelectableBAST(stage) {
        stage = String(stage || '').toUpperCase();
        return stage === 'H' || stage === 'P';
    }

    function syncChkAllStateBAST() {
        const enabled = Array.from(bastTbody.querySelectorAll('.bastRowChk:not(:disabled)'));
        if (enabled.length === 0) {
            bastChkAll.checked = false;
            bastChkAll.indeterminate = false;
            bastChkAll.disabled = true;
            return;
        }

        bastChkAll.disabled = false;

        const checkedEnabled = enabled.filter(chk => chk.checked).length;
        bastChkAll.checked = checkedEnabled === enabled.length;
        bastChkAll.indeterminate = checkedEnabled > 0 && checkedEnabled < enabled.length;
    }

    bastChkAll.addEventListener('change', () => {
        if (bastBusy) return;
        bastTbody.querySelectorAll('.bastRowChk:not(:disabled)').forEach(chk => chk.checked = bastChkAll.checked);
        syncChkAllStateBAST();
    });

    function renderRowsBAST(rows) {
        if (!rows.length) {
            bastTbody.innerHTML = `<tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            bastChkAll.checked = false;
            bastChkAll.indeterminate = false;
            bastChkAll.disabled = true;
            return;
        }

        bastTbody.innerHTML = rows.map(r => {
            const stage = String(r.stage_status ?? 'H').toUpperCase();
            const it = String(r.integration_type ?? '').trim().toUpperCase();
            const selectable = isRowSelectableBAST(stage);
            const disabled = !selectable;

            const trClass = disabled ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const checkboxClass = disabled ? 'opacity-40 cursor-not-allowed' : '';

            let title = '';
            if (stage === 'C') title = 'Sudah completed (C). Tidak bisa diproses.';
            if (stage === 'D') title = 'Status D tidak diproses dari halaman ini.';

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2 align-top">
                        <input type="checkbox"
                               class="bastRowChk rounded border-gray-300 ${checkboxClass}"
                               value="${escapeHtmlBAST(r.key)}"
                               data-stage="${escapeHtmlBAST(stage)}"
                               ${disabled ? `disabled title="${escapeHtmlBAST(title)}"` : ''}>
                    </td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getIntegrationTypeBadgeClassBAST(it)}">
                            ${escapeHtmlBAST(it || '-')}
                        </span>
                    </td>
                    <td class="px-3 py-2 align-top font-medium">${escapeHtmlBAST(r.cpny_id)}</td>
                    <td class="px-3 py-2 align-top">${escapeHtmlBAST(r.entity_cd)}</td>
                    <td class="px-3 py-2 align-top font-medium">${escapeHtmlBAST(r.bast_no)}</td>
                    <td class="px-3 py-2 align-top whitespace-nowrap">${escapeHtmlBAST(r.bast_date)}</td>
                    <td class="px-3 py-2 align-top">${escapeHtmlBAST(r.order_no)}</td>
                    <td class="px-3 py-2 align-top">${escapeHtmlBAST(r.supplier_cd)}</td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassBAST(stage)}">
                            ${escapeHtmlBAST(r.stage_label ?? stage)}
                        </span>
                    </td>
                    <td class="px-3 py-2 align-top text-gray-600">
                        <div class="whitespace-normal break-words leading-5 max-w-full">
                            ${escapeHtmlBAST(r.payload_response)}
                        </div>
                    </td>
                    <td class="px-3 py-2 align-top whitespace-nowrap text-gray-600">${escapeHtmlBAST(r.last_update)}</td>
                </tr>
            `;
        }).join('');

        bastTbody.querySelectorAll('.bastRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', () => {
                if (bastBusy) return;
                syncChkAllStateBAST();
            });
        });

        if (bastBusy) {
            bastTbody.querySelectorAll('.bastRowChk').forEach(chk => chk.disabled = true);
        }

        syncChkAllStateBAST();
    }

    function renderPaginationBAST(meta) {
        bastPagination.innerHTML = '';

        if (!meta || Number(meta.last_page || 1) <= 1) return;

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
                    if (bastBusy) return;
                    loadBAST(page);
                });
            }

            return btn;
        };

        bastPagination.appendChild(makeBtn('Prev', current - 1, current <= 1));

        let start = Math.max(1, current - 2);
        let end = Math.min(last, current + 2);

        if (current <= 3) end = Math.min(last, 5);
        if (current >= last - 2) start = Math.max(1, last - 4);

        for (let i = start; i <= end; i++) {
            bastPagination.appendChild(makeBtn(String(i), i, false, i === current));
        }

        bastPagination.appendChild(makeBtn('Next', current + 1, current >= last));
    }

    function setBusyBAST(isBusy, title = 'Processing...', sub = 'Mohon tunggu, jangan klik menu/tab.') {
        bastBusy = isBusy;

        if (isBusy) {
            bastBusyTitle.textContent = title;
            bastBusySub.textContent = sub;
            bastBusyOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            bastBusyOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        bastFrom.disabled = isBusy;
        bastTo.disabled = isBusy;
        bastCompany.disabled = isBusy;
        bastStatus.disabled = isBusy;
        bastPerPage.disabled = isBusy;
        btnLoadBAST.disabled = isBusy;
        btnProcessBAST.disabled = isBusy;
        bastChkAll.disabled = isBusy;

        bastTbody.querySelectorAll('.bastRowChk').forEach(chk => {
            if (isBusy) {
                chk.disabled = true;
                return;
            }

            const stage = String(chk.dataset.stage ?? '').toUpperCase();
            chk.disabled = !isRowSelectableBAST(stage);
            if (chk.disabled) chk.checked = false;
        });

        syncChkAllStateBAST();

        if (isBusy) {
            btnLoadBAST.dataset._txt = btnLoadBAST.textContent;
            btnProcessBAST.dataset._txt = btnProcessBAST.textContent;
            btnLoadBAST.textContent = 'Loading...';
            btnProcessBAST.textContent = 'Processing...';
        } else {
            if (btnLoadBAST.dataset._txt) btnLoadBAST.textContent = btnLoadBAST.dataset._txt;
            if (btnProcessBAST.dataset._txt) btnProcessBAST.textContent = btnProcessBAST.dataset._txt;
        }
    }

    async function loadBASTFilters() {
        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.bast.filters') }}", {
                headers: { 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (!resp.ok || !json.ok) return;

            const companies = json.data?.companies || [];
            bastCompany.innerHTML = `<option value="">All Company</option>` +
                companies.map(c => `<option value="${escapeHtmlBAST(c)}">${escapeHtmlBAST(c)}</option>`).join('');
        } catch (e) {
            console.error('Failed load BAST filters', e);
        }
    }

    async function loadBAST(page = 1) {
        hideInfoBAST(bastInfo);

        if (!bastFrom.value || !bastTo.value) {
            setInfoBAST(bastInfo, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        bastCurrentPage = page;

        setBusyBAST(true, 'Loading BAST...', 'Sedang mengambil data BAST dari Purchasing.');

        bastTbody.innerHTML = `<tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        bastChkAll.disabled = true;
        bastChkAll.checked = false;
        bastChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.bast.list') }}", window.location.origin);
        url.searchParams.set('from', bastFrom.value);
        url.searchParams.set('to', bastTo.value);
        url.searchParams.set('company', bastCompany.value);
        url.searchParams.set('status', bastStatus.value);
        url.searchParams.set('per_page', bastPerPage.value);
        url.searchParams.set('page', page);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsBAST([]);
                renderPaginationBAST(null);
                bastTotal.textContent = '0';
                bastShowingText.textContent = '';
                setInfoBAST(bastInfo, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            const summary = json.summary || {};
            const meta = json.meta || {};

            renderRowsBAST(rows);
            renderPaginationBAST(meta);

            bastTotal.textContent = meta.total ?? 0;
            bastShowingText.textContent = meta.total > 0
                ? `(Showing ${meta.from} - ${meta.to})`
                : '';

            setInfoBAST(
                bastInfo,
                'ok',
                `Loaded ${meta.total ?? 0} BAST. Ready(H/P): ${summary.ready ?? 0}. Revise(D): ${summary.D ?? 0}. Pending(P): ${summary.P ?? 0}. Completed(C): ${summary.C ?? 0}.`
            );
        } catch (e) {
            renderRowsBAST([]);
            renderPaginationBAST(null);
            bastTotal.textContent = '0';
            bastShowingText.textContent = '';
            setInfoBAST(bastInfo, 'err', e.message ?? 'Error saat load.');
        } finally {
            setBusyBAST(false);
        }
    }

    (function initDefaultDatesBAST() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        bastTo.value = `${yyyy}-${mm}-${dd}`;
        bastFrom.value = `${yyyy}-${mm}-01`;
    })();

    btnLoadBAST.addEventListener('click', async () => {
        if (bastBusy) return;
        await loadBAST(1);
    });

    bastCompany.addEventListener('change', () => {
        if (bastBusy) return;
        loadBAST(1);
    });

    bastStatus.addEventListener('change', () => {
        if (bastBusy) return;
        loadBAST(1);
    });

    bastPerPage.addEventListener('change', () => {
        if (bastBusy) return;
        loadBAST(1);
    });

    btnProcessBAST.addEventListener('click', async () => {
        if (bastBusy) return;

        hideInfoBAST(bastInfo);

        const ids = Array.from(bastTbody.querySelectorAll('.bastRowChk:checked'))
            .filter(chk => isRowSelectableBAST(String(chk.dataset.stage ?? '').toUpperCase()))
            .map(chk => chk.value);

        if (ids.length === 0) {
            setInfoBAST(bastInfo, 'warn', 'Pilih minimal 1 BAST yang valid untuk diproses.');
            return;
        }

        setBusyBAST(true, 'Processing BAST...', 'Sedang insert (H→P) dan/atau kirim ke API (P→C).');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.bast.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfBAST
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                setInfoBAST(bastInfo, 'err', json.message ?? 'Gagal process.');
                return;
            }

            setInfoBAST(
                bastInfo,
                'ok',
                `Process done. Inserted(H->P lines): ${json.inserted_H_to_P ?? 0}, Sent OK(P->C BAST): ${json.sent_success_P_to_C ?? 0}, Failed(P): ${json.sent_failed_still_P ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );
        } catch (e) {
            setInfoBAST(bastInfo, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusyBAST(false);
        }

        await loadBAST(bastCurrentPage || 1);
    });

    loadBASTFilters();
</script>
