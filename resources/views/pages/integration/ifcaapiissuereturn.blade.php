<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="issuereturnBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
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
                <div class="font-semibold text-gray-800" id="issuereturnBusyTitle">Processing...</div>
                <div class="text-gray-500" id="issuereturnBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-7">
    <div>
        <label class="text-sm font-medium text-gray-600">Start Date</label>
        <input type="date" id="issuereturn_from"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">End Date</label>
        <input type="date" id="issuereturn_to"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Company</label>
        <select id="issuereturn_company"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Company</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Status</label>
        <select id="issuereturn_status"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="H">H</option>
            <option value="P">P</option>
            <option value="C">C</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Show</label>
        <select id="issuereturn_per_page"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnLoadIssueReturn"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
            Load
        </button>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnProcessIssueReturn"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
            Process
        </button>
    </div>
</div>

<div id="issuereturnInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="issuereturnTotal">0</span>
            <span class="ml-2 text-gray-500" id="issuereturnShowingText"></span>
        </div>
        <div class="text-sm text-gray-500">Pagination enabled</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2 align-middle">
                    <input type="checkbox" id="issuereturnChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-32 px-3 py-2 align-middle">Integration Type</th>
                <th class="w-20 px-3 py-2 align-middle">Cpny</th>
                <th class="w-24 px-3 py-2 align-middle">Entity Cd</th>
                <th class="w-32 px-3 py-2 align-middle">Issue Return ID</th>
                <th class="w-28 px-3 py-2 align-middle">Issue Return Date</th>
                <th class="w-32 px-3 py-2 align-middle">Reference Issue</th>
                <th class="w-44 px-3 py-2 align-middle">Department ID</th>
                <th class="w-24 px-3 py-2 align-middle">Status</th>
                <th class="w-[420px] px-3 py-2 align-middle">Response</th>
                <th class="w-40 px-3 py-2 align-middle">Last Update</th>
            </tr>
            </thead>
            <tbody id="issuereturnTbody" class="divide-y divide-gray-100">
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

        <div id="issuereturnPagination" class="flex flex-wrap items-center gap-2"></div>
    </div>
</div>

<script>
    const csrfIssueReturn = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const issuereturnFrom        = document.getElementById('issuereturn_from');
    const issuereturnTo          = document.getElementById('issuereturn_to');
    const issuereturnCompany     = document.getElementById('issuereturn_company');
    const issuereturnStatus      = document.getElementById('issuereturn_status');
    const issuereturnPerPage     = document.getElementById('issuereturn_per_page');

    const issuereturnTbody       = document.getElementById('issuereturnTbody');
    const issuereturnTotal       = document.getElementById('issuereturnTotal');
    const issuereturnShowingText = document.getElementById('issuereturnShowingText');
    const issuereturnInfo        = document.getElementById('issuereturnInfo');
    const issuereturnChkAll      = document.getElementById('issuereturnChkAll');
    const btnLoadIssueReturn     = document.getElementById('btnLoadIssueReturn');
    const btnProcessIssueReturn  = document.getElementById('btnProcessIssueReturn');
    const issuereturnPagination  = document.getElementById('issuereturnPagination');

    const issuereturnBusyOverlay = document.getElementById('issuereturnBusyOverlay');
    const issuereturnBusyTitle   = document.getElementById('issuereturnBusyTitle');
    const issuereturnBusySub     = document.getElementById('issuereturnBusySub');

    let issuereturnBusy = false;
    let issuereturnCurrentPage = 1;

    function setInfoIssueReturn(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function hideInfoIssueReturn(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    function getStatusBadgeClassIssueReturn(stage) {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
    }

    function getIntegrationTypeBadgeClassIssueReturn(it = '') {
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
    function isRowSelectableIssueReturn(stage, it) {
        stage = String(stage || '').toUpperCase();
        it = String(it || '').toUpperCase();

        if (stage === 'C') return false;
        if (stage === 'H') return true;
        if (stage === 'P' && it === 'IFCA') return true;

        return false;
    }

    function syncChkAllStateIssueReturn() {
        const enabled = Array.from(issuereturnTbody.querySelectorAll('.issuereturnRowChk:not(:disabled)'));
        if (enabled.length === 0) {
            issuereturnChkAll.checked = false;
            issuereturnChkAll.indeterminate = false;
            issuereturnChkAll.disabled = true;
            return;
        }

        issuereturnChkAll.disabled = false;

        const checkedEnabled = enabled.filter(chk => chk.checked).length;
        issuereturnChkAll.checked = checkedEnabled === enabled.length;
        issuereturnChkAll.indeterminate = checkedEnabled > 0 && checkedEnabled < enabled.length;
    }

    issuereturnChkAll.addEventListener('change', () => {
        if (issuereturnBusy) return;
        issuereturnTbody.querySelectorAll('.issuereturnRowChk:not(:disabled)').forEach(chk => chk.checked = issuereturnChkAll.checked);
        syncChkAllStateIssueReturn();
    });

    function renderRowsIssueReturn(rows) {
        if (!rows.length) {
            issuereturnTbody.innerHTML = `<tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            issuereturnChkAll.checked = false;
            issuereturnChkAll.indeterminate = false;
            issuereturnChkAll.disabled = true;
            return;
        }

        issuereturnTbody.innerHTML = rows.map(r => {
            const stage = String(r.stage_status ?? 'H').toUpperCase();
            const it = String(r.integration_type ?? '').trim().toUpperCase();
            const selectable = isRowSelectableIssueReturn(stage, it);
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
                               class="issuereturnRowChk rounded border-gray-300 ${checkboxClass}"
                               value="${r.key}"
                               data-stage="${stage}"
                               data-it="${it}"
                               ${disabled ? `disabled title="${title}"` : ''}>
                    </td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getIntegrationTypeBadgeClassIssueReturn(it)}">
                            ${it || '-'}
                        </span>
                    </td>
                    <td class="px-3 py-2 align-top font-medium">${r.cpny_id ?? ''}</td>
                    <td class="px-3 py-2 align-top">${r.entity_cd ?? ''}</td>
                    <td class="px-3 py-2 align-top font-medium">${r.issuereturn_id ?? ''}</td>
                    <td class="px-3 py-2 align-top whitespace-nowrap">${r.issuereturn_date ?? ''}</td>
                    <td class="px-3 py-2 align-top">${r.reference_no ?? ''}</td>
                    <td class="px-3 py-2 align-top break-words">${r.department_id ?? ''}</td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassIssueReturn(stage)}">
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

        issuereturnTbody.querySelectorAll('.issuereturnRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', () => {
                if (issuereturnBusy) return;
                syncChkAllStateIssueReturn();
            });
        });

        if (issuereturnBusy) {
            issuereturnTbody.querySelectorAll('.issuereturnRowChk').forEach(chk => chk.disabled = true);
        }

        syncChkAllStateIssueReturn();
    }

    function renderPaginationIssueReturn(meta) {
        issuereturnPagination.innerHTML = '';

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
                    if (issuereturnBusy) return;
                    loadIssueReturn(page);
                });
            }

            return btn;
        };

        issuereturnPagination.appendChild(makeBtn('Prev', current - 1, current <= 1));

        let start = Math.max(1, current - 2);
        let end = Math.min(last, current + 2);

        if (current <= 3) end = Math.min(last, 5);
        if (current >= last - 2) start = Math.max(1, last - 4);

        for (let i = start; i <= end; i++) {
            issuereturnPagination.appendChild(makeBtn(String(i), i, false, i === current));
        }

        issuereturnPagination.appendChild(makeBtn('Next', current + 1, current >= last));
    }

    function setBusyIssueReturn(isBusy, title = 'Processing...', sub = 'Mohon tunggu, jangan klik menu/tab.') {
        issuereturnBusy = isBusy;

        if (isBusy) {
            issuereturnBusyTitle.textContent = title;
            issuereturnBusySub.textContent = sub;
            issuereturnBusyOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            issuereturnBusyOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        issuereturnFrom.disabled = isBusy;
        issuereturnTo.disabled = isBusy;
        issuereturnCompany.disabled = isBusy;
        issuereturnStatus.disabled = isBusy;
        issuereturnPerPage.disabled = isBusy;
        btnLoadIssueReturn.disabled = isBusy;
        btnProcessIssueReturn.disabled = isBusy;
        issuereturnChkAll.disabled = isBusy;

        issuereturnTbody.querySelectorAll('.issuereturnRowChk').forEach(chk => {
            if (isBusy) {
                chk.disabled = true;
                return;
            }

            const stage = String(chk.dataset.stage ?? '').toUpperCase();
            const it = String(chk.dataset.it ?? '').toUpperCase();

            chk.disabled = !isRowSelectableIssueReturn(stage, it);
            if (chk.disabled) chk.checked = false;
        });

        syncChkAllStateIssueReturn();

        if (isBusy) {
            btnLoadIssueReturn.dataset._txt = btnLoadIssueReturn.textContent;
            btnProcessIssueReturn.dataset._txt = btnProcessIssueReturn.textContent;
            btnLoadIssueReturn.textContent = 'Loading...';
            btnProcessIssueReturn.textContent = 'Processing...';
        } else {
            if (btnLoadIssueReturn.dataset._txt) btnLoadIssueReturn.textContent = btnLoadIssueReturn.dataset._txt;
            if (btnProcessIssueReturn.dataset._txt) btnProcessIssueReturn.textContent = btnProcessIssueReturn.dataset._txt;
        }
    }

    async function loadIssueReturnFilters() {
        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.issuereturn.filters') }}", {
                headers: { 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (!resp.ok || !json.ok) return;

            const companies = json.data?.companies || [];
            issuereturnCompany.innerHTML = `<option value="">All Company</option>` +
                companies.map(c => `<option value="${c}">${c}</option>`).join('');
        } catch (e) {
            console.error('Failed load Issue Return filters', e);
        }
    }

    async function loadIssueReturn(page = 1) {
        hideInfoIssueReturn(issuereturnInfo);

        if (!issuereturnFrom.value || !issuereturnTo.value) {
            setInfoIssueReturn(issuereturnInfo, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        issuereturnCurrentPage = page;

        setBusyIssueReturn(true, 'Loading Issue Return...', 'Sedang mengambil data Issue Return dari Purchasing.');

        issuereturnTbody.innerHTML = `<tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        issuereturnChkAll.disabled = true;
        issuereturnChkAll.checked = false;
        issuereturnChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.issuereturn.list') }}", window.location.origin);
        url.searchParams.set('from', issuereturnFrom.value);
        url.searchParams.set('to', issuereturnTo.value);
        url.searchParams.set('company', issuereturnCompany.value);
        url.searchParams.set('status', issuereturnStatus.value);
        url.searchParams.set('per_page', issuereturnPerPage.value);
        url.searchParams.set('page', page);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsIssueReturn([]);
                renderPaginationIssueReturn(null);
                issuereturnTotal.textContent = '0';
                issuereturnShowingText.textContent = '';
                setInfoIssueReturn(issuereturnInfo, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            const summary = json.summary || {};
            const meta = json.meta || {};

            renderRowsIssueReturn(rows);
            renderPaginationIssueReturn(meta);

            issuereturnTotal.textContent = meta.total ?? 0;
            issuereturnShowingText.textContent = meta.total > 0
                ? `(Showing ${meta.from} - ${meta.to})`
                : '';

            setInfoIssueReturn(
                issuereturnInfo,
                'ok',
                `Loaded ${meta.total ?? 0} Issue Return. Ready(H + P-IFCA): ${summary.ready ?? 0}. Pending(P): ${summary.P ?? 0}. Completed(C): ${summary.C ?? 0}.`
            );
        } catch (e) {
            renderRowsIssueReturn([]);
            renderPaginationIssueReturn(null);
            issuereturnTotal.textContent = '0';
            issuereturnShowingText.textContent = '';
            setInfoIssueReturn(issuereturnInfo, 'err', e.message ?? 'Error saat load.');
        } finally {
            setBusyIssueReturn(false);
        }
    }

    // (function initDefaultDatesIssueReturn() {
    //     const today = new Date();
    //     const yyyy = today.getFullYear();
    //     const mm = String(today.getMonth() + 1).padStart(2, '0');
    //     const dd = String(today.getDate()).padStart(2, '0');
    //     issuereturnTo.value = `${yyyy}-${mm}-${dd}`;
    //     issuereturnFrom.value = `${yyyy}-${mm}-01`;
    // })();

    (function initDefaultDatesIssueReturn() {
        const formatDateIssueReturn = (date) => {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');

            return `${yyyy}-${mm}-${dd}`;
        };

        const today = new Date();

        const fromDate = new Date(today);
        fromDate.setDate(today.getDate() - 30);

        issuereturnFrom.value = formatDateIssueReturn(fromDate);
        issuereturnTo.value = formatDateIssueReturn(today);
    })();

    btnLoadIssueReturn.addEventListener('click', async () => {
        if (issuereturnBusy) return;
        await loadIssueReturn(1);
    });

    issuereturnCompany.addEventListener('change', () => {
        if (issuereturnBusy) return;
        loadIssueReturn(1);
    });

    issuereturnStatus.addEventListener('change', () => {
        if (issuereturnBusy) return;
        loadIssueReturn(1);
    });

    issuereturnPerPage.addEventListener('change', () => {
        if (issuereturnBusy) return;
        loadIssueReturn(1);
    });

    btnProcessIssueReturn.addEventListener('click', async () => {
        if (issuereturnBusy) return;

        hideInfoIssueReturn(issuereturnInfo);

        const ids = Array.from(issuereturnTbody.querySelectorAll('.issuereturnRowChk:checked'))
            .filter(chk => {
                const stage = String(chk.dataset.stage ?? '').toUpperCase();
                const it = String(chk.dataset.it ?? '').toUpperCase();
                return isRowSelectableIssueReturn(stage, it);
            })
            .map(chk => chk.value);

        if (ids.length === 0) {
            setInfoIssueReturn(issuereturnInfo, 'warn', 'Pilih minimal 1 Issue Return yang valid untuk diproses.');
            return;
        }

        setBusyIssueReturn(true, 'Processing Issue Return...', 'Sedang insert (H→P) dan/atau kirim ke API (P→C).');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.issuereturn.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfIssueReturn
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                setInfoIssueReturn(issuereturnInfo, 'err', json.message ?? 'Gagal process.');
                return;
            }

            setInfoIssueReturn(
                issuereturnInfo,
                'ok',
                `Process done. Inserted(H->P lines): ${json.inserted_H_to_P ?? 0}, Sent OK(P->C Issue Return): ${json.sent_success_P_to_C ?? 0}, Failed(P): ${json.sent_failed_still_P ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );
        } catch (e) {
            setInfoIssueReturn(issuereturnInfo, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusyIssueReturn(false);
        }

        await loadIssueReturn(issuereturnCurrentPage || 1);
    });

    loadIssueReturnFilters();
</script>