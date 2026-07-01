<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="issueBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
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
                <div class="font-semibold text-gray-800" id="issueBusyTitle">Processing...</div>
                <div class="text-gray-500" id="issueBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-7">
    <div>
        <label class="text-sm font-medium text-gray-600">Start Date</label>
        <input type="date" id="issue_from"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">End Date</label>
        <input type="date" id="issue_to"
               class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Company</label>
        <select id="issue_company"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="">All Company</option>
        </select>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-600">Status</label>
        <select id="issue_status"
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
        <select id="issue_per_page"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnLoadIssue"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
            Load
        </button>
    </div>

    <div class="flex items-end">
        <button type="button" id="btnProcessIssue"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
            Process
        </button>
    </div>
</div>

<div id="issueInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="issueTotal">0</span>
            <span class="ml-2 text-gray-500" id="issueShowingText"></span>
        </div>
        <div class="text-sm text-gray-500">Pagination enabled</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2 align-middle">
                    <input type="checkbox" id="issueChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-32 px-3 py-2 align-middle">Integration Type</th>
                <th class="w-20 px-3 py-2 align-middle">Cpny</th>
                <th class="w-24 px-3 py-2 align-middle">Entity Cd</th>
                <th class="w-32 px-3 py-2 align-middle">Issue ID</th>
                <th class="w-28 px-3 py-2 align-middle">Issue Date</th>
                <th class="w-32 px-3 py-2 align-middle">Ref</th>
                <th class="w-44 px-3 py-2 align-middle">Department ID</th>
                <th class="w-24 px-3 py-2 align-middle">Status</th>
                <th class="w-[420px] px-3 py-2 align-middle">Response</th>
                <th class="w-40 px-3 py-2 align-middle">Last Update</th>
            </tr>
            </thead>
            <tbody id="issueTbody" class="divide-y divide-gray-100">
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
            H = belum ada di staging,
            D = menunggu review,
            P-IFCA = siap kirim API,
            P-SOLOMON = reviewed Solomon,
            C = completed.
        </div>

        <div id="issuePagination" class="flex flex-wrap items-center gap-2"></div>
    </div>
</div>

<script>
    const csrfIssue = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const issueFrom        = document.getElementById('issue_from');
    const issueTo          = document.getElementById('issue_to');
    const issueCompany     = document.getElementById('issue_company');
    const issueStatus      = document.getElementById('issue_status');
    const issuePerPage     = document.getElementById('issue_per_page');

    const issueTbody       = document.getElementById('issueTbody');
    const issueTotal       = document.getElementById('issueTotal');
    const issueShowingText = document.getElementById('issueShowingText');
    const issueInfo        = document.getElementById('issueInfo');
    const issueChkAll      = document.getElementById('issueChkAll');
    const btnLoadIssue     = document.getElementById('btnLoadIssue');
    const btnProcessIssue  = document.getElementById('btnProcessIssue');
    const issuePagination  = document.getElementById('issuePagination');

    const issueBusyOverlay = document.getElementById('issueBusyOverlay');
    const issueBusyTitle   = document.getElementById('issueBusyTitle');
    const issueBusySub     = document.getElementById('issueBusySub');

    let issueBusy = false;
    let issueCurrentPage = 1;

    function getTabEls() {
        return Array.from(document.querySelectorAll('a, button'))
            .filter(el => {
                if (!el || el === btnLoadIssue || el === btnProcessIssue) return false;
                const txt = (el.textContent || '').trim().toLowerCase();
                return ['non stock','stock','supplier','po','grn','sttb','bast','issue','receipt'].some(k => txt === k || txt.includes(k));
            });
    }

    function setInfoIssue(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');

        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function hideInfoIssue(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    function getStatusBadgeClassIssue(stage, it = '') {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'D') return 'bg-blue-200 text-blue-800';
        if (stage === 'P' && it === 'SOLOMON') return 'bg-orange-200 text-orange-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
    }

    function getIntegrationTypeBadgeClassIssue(it = '') {
        it = String(it || '').trim().toUpperCase();
        if (it === 'IFCA') return 'bg-indigo-100 text-indigo-800';
        if (it === 'SOLOMON') return 'bg-orange-100 text-orange-800';
        return 'bg-gray-100 text-gray-700';
    }

    function syncChkAllStateIssue() {
        const enabled = Array.from(issueTbody.querySelectorAll('.issueRowChk:not(:disabled)'));
        if (enabled.length === 0) {
            issueChkAll.checked = false;
            issueChkAll.indeterminate = false;
            issueChkAll.disabled = true;
            return;
        }

        issueChkAll.disabled = false;

        const checkedEnabled = enabled.filter(chk => chk.checked).length;
        issueChkAll.checked = checkedEnabled === enabled.length;
        issueChkAll.indeterminate = checkedEnabled > 0 && checkedEnabled < enabled.length;
    }

    issueChkAll.addEventListener('change', () => {
        if (issueBusy) return;
        issueTbody.querySelectorAll('.issueRowChk:not(:disabled)').forEach(chk => chk.checked = issueChkAll.checked);
        syncChkAllStateIssue();
    });

    function renderRowsIssue(rows) {
        if (!rows.length) {
            issueTbody.innerHTML = `<tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            issueChkAll.checked = false;
            issueChkAll.indeterminate = false;
            issueChkAll.disabled = true;
            return;
        }

        issueTbody.innerHTML = rows.map(r => {
            const stage = (r.stage_status ?? 'H');
            const it = String(r.integration_type ?? '').trim().toUpperCase();
            const stageLabel = (r.stage_label ?? stage);

            const disableByStage = (stage === 'C' || stage === 'D');
            const disablePSolomon = (stage === 'P' && it !== 'IFCA');
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
                               class="issueRowChk rounded border-gray-300 ${checkboxClass}"
                               value="${r.key}"
                               data-stage="${stage}"
                               data-it="${it}"
                               ${disabled ? `disabled title="${title}"` : ''}>
                    </td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getIntegrationTypeBadgeClassIssue(it)}">
                            ${it || '-'}
                        </span>
                    </td>
                    <td class="px-3 py-2 align-top font-medium">${r.cpny_id ?? ''}</td>
                    <td class="px-3 py-2 align-top">${r.entity_cd ?? ''}</td>
                    <td class="px-3 py-2 align-top font-medium">${r.issue_id ?? ''}</td>
                    <td class="px-3 py-2 align-top whitespace-nowrap">${r.issue_date ?? ''}</td>
                    <td class="px-3 py-2 align-top">${r.reference_no ?? ''}</td>
                    <td class="px-3 py-2 align-top break-words">${r.department_id ?? ''}</td>
                    <td class="px-3 py-2 align-top">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassIssue(stage, it)}">
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

        issueTbody.querySelectorAll('.issueRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', () => {
                if (issueBusy) return;
                syncChkAllStateIssue();
            });
        });

        if (issueBusy) {
            issueTbody.querySelectorAll('.issueRowChk').forEach(chk => chk.disabled = true);
        }

        syncChkAllStateIssue();
    }

    function renderPaginationIssue(meta) {
        issuePagination.innerHTML = '';

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
                    if (issueBusy) return;
                    loadIssue(page);
                });
            }

            return btn;
        };

        issuePagination.appendChild(makeBtn('Prev', current - 1, current <= 1));

        let start = Math.max(1, current - 2);
        let end = Math.min(last, current + 2);

        if (current <= 3) end = Math.min(last, 5);
        if (current >= last - 2) start = Math.max(1, last - 4);

        for (let i = start; i <= end; i++) {
            issuePagination.appendChild(makeBtn(String(i), i, false, i === current));
        }

        issuePagination.appendChild(makeBtn('Next', current + 1, current >= last));
    }

    function setBusyIssue(isBusy, title = 'Processing...', sub = 'Mohon tunggu, jangan klik menu/tab.') {
        issueBusy = isBusy;

        if (isBusy) {
            issueBusyTitle.textContent = title;
            issueBusySub.textContent = sub;
            issueBusyOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            issueBusyOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        issueFrom.disabled = isBusy;
        issueTo.disabled = isBusy;
        issueCompany.disabled = isBusy;
        issueStatus.disabled = isBusy;
        issuePerPage.disabled = isBusy;
        btnLoadIssue.disabled = isBusy;
        btnProcessIssue.disabled = isBusy;
        issueChkAll.disabled = isBusy;

        const tabs = getTabEls();
        tabs.forEach(el => {
            if (isBusy) {
                if (el.dataset._issue_prev_pointer == null) el.dataset._issue_prev_pointer = el.style.pointerEvents || '';
                if (el.dataset._issue_prev_opacity == null) el.dataset._issue_prev_opacity = el.style.opacity || '';
                el.style.pointerEvents = 'none';
                el.style.opacity = '0.6';
            } else {
                el.style.pointerEvents = el.dataset._issue_prev_pointer ?? '';
                el.style.opacity = el.dataset._issue_prev_opacity ?? '';
                delete el.dataset._issue_prev_pointer;
                delete el.dataset._issue_prev_opacity;
            }
        });

        const rowChks = issueTbody.querySelectorAll('.issueRowChk');
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

        syncChkAllStateIssue();

        if (isBusy) {
            btnLoadIssue.dataset._txt = btnLoadIssue.textContent;
            btnProcessIssue.dataset._txt = btnProcessIssue.textContent;
            btnLoadIssue.textContent = 'Loading...';
            btnProcessIssue.textContent = 'Processing...';
        } else {
            if (btnLoadIssue.dataset._txt) btnLoadIssue.textContent = btnLoadIssue.dataset._txt;
            if (btnProcessIssue.dataset._txt) btnProcessIssue.textContent = btnProcessIssue.dataset._txt;
        }
    }

    async function loadIssueFilters() {
        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.issue.filters') }}", {
                headers: { 'Accept': 'application/json' }
            });
            const json = await resp.json();

            if (!resp.ok || !json.ok) return;

            const companies = json.data?.companies || [];
            issueCompany.innerHTML = `<option value="">All Company</option>` +
                companies.map(c => `<option value="${c}">${c}</option>`).join('');
        } catch (e) {
            console.error('Failed load Issue filters', e);
        }
    }

    async function loadIssue(page = 1) {
        hideInfoIssue(issueInfo);

        if (!issueFrom.value || !issueTo.value) {
            setInfoIssue(issueInfo, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        issueCurrentPage = page;

        setBusyIssue(true, 'Loading Issue...', 'Sedang mengambil data Issue dari Purchasing.');

        issueTbody.innerHTML = `<tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        issueChkAll.disabled = true;
        issueChkAll.checked = false;
        issueChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.issue.list') }}", window.location.origin);
        url.searchParams.set('from', issueFrom.value);
        url.searchParams.set('to', issueTo.value);
        url.searchParams.set('company', issueCompany.value);
        url.searchParams.set('status', issueStatus.value);
        url.searchParams.set('per_page', issuePerPage.value);
        url.searchParams.set('page', page);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsIssue([]);
                renderPaginationIssue(null);
                issueTotal.textContent = '0';
                issueShowingText.textContent = '';
                setInfoIssue(issueInfo, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            const summary = json.summary || {};
            const meta = json.meta || {};

            renderRowsIssue(rows);
            renderPaginationIssue(meta);

            issueTotal.textContent = meta.total ?? 0;
            issueShowingText.textContent = meta.total > 0
                ? `(Showing ${meta.from} - ${meta.to})`
                : '';

            setInfoIssue(
                issueInfo,
                'ok',
                `Loaded ${meta.total ?? 0} Issue. Ready(H/P-IFCA): ${summary.ready ?? 0}. Waiting Review(D): ${summary.D ?? 0}. Pending(P): ${summary.P ?? 0}. Completed(C): ${summary.C ?? 0}.`
            );
        } catch (e) {
            renderRowsIssue([]);
            renderPaginationIssue(null);
            issueTotal.textContent = '0';
            issueShowingText.textContent = '';
            setInfoIssue(issueInfo, 'err', e.message ?? 'Error saat load.');
        } finally {
            setBusyIssue(false);
        }
    }

    // (function initDefaultDatesIssue() {
    //     const today = new Date();
    //     const yyyy = today.getFullYear();
    //     const mm = String(today.getMonth() + 1).padStart(2, '0');
    //     const dd = String(today.getDate()).padStart(2, '0');
    //     issueTo.value = `${yyyy}-${mm}-${dd}`;
    //     issueFrom.value = `${yyyy}-${mm}-01`;
    // })();

    (function initDefaultDatesIssue() {
        const formatDateIssue = (date) => {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');

            return `${yyyy}-${mm}-${dd}`;
        };

        const today = new Date();

        const fromDate = new Date(today);
        fromDate.setDate(today.getDate() - 30);

        issueFrom.value = formatDateIssue(fromDate);
        issueTo.value = formatDateIssue(today);
    })();

    btnLoadIssue.addEventListener('click', async () => {
        if (issueBusy) return;
        await loadIssue(1);
    });

    issueCompany.addEventListener('change', () => {
        if (issueBusy) return;
        loadIssue(1);
    });

    issueStatus.addEventListener('change', () => {
        if (issueBusy) return;
        loadIssue(1);
    });

    issuePerPage.addEventListener('change', () => {
        if (issueBusy) return;
        loadIssue(1);
    });

    btnProcessIssue.addEventListener('click', async () => {
        if (issueBusy) return;

        hideInfoIssue(issueInfo);

        const ids = Array.from(issueTbody.querySelectorAll('.issueRowChk:checked'))
            .filter(chk => chk.dataset.stage === 'H' || (chk.dataset.stage === 'P' && chk.dataset.it === 'IFCA'))
            .map(chk => chk.value);

        if (ids.length === 0) {
            setInfoIssue(issueInfo, 'warn', 'Pilih minimal 1 Issue status H atau P-IFCA untuk diproses.');
            return;
        }

        setBusyIssue(true, 'Processing Issue...', 'Sedang insert (H→D) dan/atau kirim ke API (P→C).');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.issue.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfIssue
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                setInfoIssue(issueInfo, 'err', json.message ?? 'Gagal process.');
                return;
            }

            setInfoIssue(
                issueInfo,
                'ok',
                `Process done. Inserted(H->D lines): ${json.inserted_H_to_D ?? 0}, Sent OK(P->C issues): ${json.sent_success_P_to_C ?? 0}, Failed(P): ${json.sent_failed_still_P ?? 0}, Skipped(D): ${json.skipped_D ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );
        } catch (e) {
            setInfoIssue(issueInfo, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusyIssue(false);
        }

        await loadIssue(issueCurrentPage || 1);
    });

    document.addEventListener('DOMContentLoaded', async () => {
        await loadIssueFilters();
    });
</script>