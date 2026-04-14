<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- =========================
     FULLSCREEN BUSY OVERLAY
     ========================= --}}
<div id="issueBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
    <div class="absolute inset-0 bg-black/40 pointer-events-auto"></div>

    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div class="pointer-events-auto rounded-xl bg-white px-5 py-4 shadow-lg border border-gray-200 flex items-center gap-3">
            <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
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

<div class="mb-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <div class="grid w-full grid-cols-1 gap-3 md:max-w-4xl md:grid-cols-3">
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
        <div class="flex gap-2">
            <button type="button" id="btnLoadIssue"
                    class="mt-6 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
                Load
            </button>
            <button type="button" id="btnProcessIssue"
                    class="mt-6 w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                Process
            </button>
        </div>
    </div>
</div>

<div id="issueInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="issueTotal">0</span>
        </div>
        <div class="text-sm text-gray-500">Limit 100 rows per load</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2">
                    <input type="checkbox" id="issueChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-24 px-3 py-2">Company</th>
                <th class="w-44 px-3 py-2">Issue ID</th>
                <th class="w-44 px-3 py-2">Issue Date</th>
                <th class="w-40 px-3 py-2">Reference</th>
                <th class="w-32 px-3 py-2">Status</th>
                <th class="px-3 py-2">Response</th>
                <th class="w-44 px-3 py-2">Last Update</th>
            </tr>
            </thead>
            <tbody id="issueTbody" class="divide-y divide-gray-100">
            <tr>
                <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                    Belum ada data. Klik Load.
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 bg-white px-4 py-2 text-xs text-gray-500">
        <span class="font-semibold">Legend:</span>
        H = belum ada di staging (boleh insert),
        D = di staging menunggu review (disabled),
        P-IFCA = reviewed siap kirim API,
        P-SOLOMON = reviewed (tidak bisa kirim di screen ini),
        C = completed (disabled).
    </div>
</div>

<script>
    // =========================
    // ISSUE Integration (H -> D -> P -> C) - SAME AS PO STYLE
    // =========================
    const csrfIssue = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // refs
    const issueFrom        = document.getElementById('issue_from');
    const issueTo          = document.getElementById('issue_to');
    const issueTbody       = document.getElementById('issueTbody');
    const issueTotal       = document.getElementById('issueTotal');
    const issueInfo        = document.getElementById('issueInfo');
    const issueChkAll      = document.getElementById('issueChkAll');
    const btnLoadIssue     = document.getElementById('btnLoadIssue');
    const btnProcessIssue  = document.getElementById('btnProcessIssue');

    // overlay refs
    const issueBusyOverlay = document.getElementById('issueBusyOverlay');
    const issueBusyTitle   = document.getElementById('issueBusyTitle');
    const issueBusySub     = document.getElementById('issueBusySub');

    let issueBusy = false;

    function getTabEls() {
        return Array.from(document.querySelectorAll('a, button'))
            .filter(el => {
                if (!el || el === btnLoadIssue || el === btnProcessIssue) return false;
                const txt = (el.textContent || '').trim().toLowerCase();
                return ['non stock','stock','supplier','po','sttb','bast','issue','receipt'].some(k => txt === k || txt.includes(k));
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

    // badge class
    function getStatusBadgeClassIssue(stage, it = '') {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'D') return 'bg-blue-200 text-blue-800';
        if (stage === 'P' && it === 'SOLOMON') return 'bg-orange-200 text-orange-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800'; // C
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

    // =========================
    // Opsi A (SAME AS PO):
    // - renderRowsIssue tidak pakai "issueBusy" untuk menentukan disabled
    // - saat busy, kita disable semua row checkbox via setBusyIssue()
    // =========================
    function renderRowsIssue(rows) {
        issueTotal.textContent = rows.length;

        if (!rows.length) {
            issueTbody.innerHTML = `<tr><td colspan="8" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            issueChkAll.checked = false;
            issueChkAll.indeterminate = false;
            issueChkAll.disabled = true;
            return;
        }

        issueTbody.innerHTML = rows.map(r => {
            const stage = (r.stage_status ?? 'H');
            const it = String(r.integration_type ?? '').trim().toUpperCase(); // ✅ trim biar aman
            const stageLabel = (r.stage_label ?? stage);

            // disable rules (tanpa issueBusy)
            const disableByStage = (stage === 'C' || stage === 'D');
            const disablePSolomon = (stage === 'P' && it !== 'IFCA');
            const disabled = disableByStage || disablePSolomon;

            const trClass = (disableByStage || disablePSolomon) ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const checkboxClass = (disableByStage || disablePSolomon) ? 'opacity-40 cursor-not-allowed' : '';

            let title = '';
            if (stage === 'C') title = 'Sudah completed (C). Tidak bisa diproses.';
            if (stage === 'D') title = 'Menunggu review (D). Tidak bisa diproses di screen ini.';
            if (disablePSolomon) title = 'P-SOLOMON tidak dikirim di screen ini (hanya IFCA).';

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2">
                        <input type="checkbox"
                            class="issueRowChk rounded border-gray-300 ${checkboxClass}"
                            value="${r.key}"
                            data-stage="${stage}"
                            data-it="${it}"
                            ${disabled ? `disabled title="${title}"` : ''}>
                    </td>
                    <td class="px-3 py-2 font-medium">${r.cpny_id ?? ''}</td>
                    <td class="px-3 py-2 font-medium">${r.issue_id ?? ''}</td>
                    <td class="px-3 py-2">${r.issue_date ?? ''}</td>
                    <td class="px-3 py-2">${r.reference_no ?? ''}</td>
                    <td class="px-3 py-2">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassIssue(stage, it)}">
                            ${stageLabel}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-gray-600">${r.payload_response ?? ''}</td>
                    <td class="px-3 py-2 text-gray-600">${r.last_update ?? ''}</td>
                </tr>
            `;
        }).join('');

        issueTbody.querySelectorAll('.issueRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', () => {
                if (issueBusy) return;
                syncChkAllStateIssue();
            });
        });

        // kalau sedang busy, pastikan semua row checkbox tetap off
        if (issueBusy) {
            issueTbody.querySelectorAll('.issueRowChk').forEach(chk => chk.disabled = true);
        }

        syncChkAllStateIssue();
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
        btnLoadIssue.disabled = isBusy;
        btnProcessIssue.disabled = isBusy;
        issueChkAll.disabled = isBusy;

        // disable tab/menu (optional)
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

        // ✅ Opsi A (FIX): saat busy => disable semua
        // saat tidak busy => hitung ulang disabled sesuai rule stage (JANGAN restore dari cache)
        const rowChks = issueTbody.querySelectorAll('.issueRowChk');
        rowChks.forEach(chk => {
            if (isBusy) {
                chk.disabled = true;
                return;
            }

            const stage = String(chk.dataset.stage ?? '').toUpperCase();
            const it    = String(chk.dataset.it ?? '').trim().toUpperCase();

            const disableByStage   = (stage === 'C' || stage === 'D');
            const disablePSolomon  = (stage === 'P' && it !== 'IFCA');

            chk.disabled = disableByStage || disablePSolomon;
            // kalau jadi disabled, sekalian uncheck biar tidak “nyangkut”
            if (chk.disabled) chk.checked = false;
        });

        // update check-all state
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

    async function loadIssue() {
        hideInfoIssue(issueInfo);

        if (!issueFrom.value || !issueTo.value) {
            setInfoIssue(issueInfo, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        setBusyIssue(true, 'Loading Issue...', 'Sedang mengambil data Issue dari Purchasing.');

        issueTbody.innerHTML = `<tr><td colspan="8" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        issueChkAll.disabled = true;
        issueChkAll.checked = false;
        issueChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.issue.list') }}", window.location.origin);
        url.searchParams.set('from', issueFrom.value);
        url.searchParams.set('to', issueTo.value);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsIssue([]);
                setInfoIssue(issueInfo, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            renderRowsIssue(rows);

            // Ready hanya H dan P-IFCA
            const readyCount = rows.filter(x => {
                const st = x.stage_status ?? 'H';
                const it = String(x.integration_type ?? '').trim().toUpperCase();
                return st === 'H' || (st === 'P' && it === 'IFCA');
            }).length;

            const waitingReview = rows.filter(x => (x.stage_status ?? '') === 'D').length;
            const doneCount     = rows.filter(x => (x.stage_status ?? '') === 'C').length;

            setInfoIssue(issueInfo, 'ok',
                `Loaded ${rows.length} Issue. Ready(H/P-IFCA): ${readyCount}. Waiting Review(D): ${waitingReview}. Completed(C): ${doneCount}.`
            );
        } catch (e) {
            renderRowsIssue([]);
            setInfoIssue(issueInfo, 'err', e.message ?? 'Error saat load.');
        } finally {
            setBusyIssue(false);
        }
    }

    (function initDefaultDatesIssue() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        issueTo.value = `${yyyy}-${mm}-${dd}`;
        issueFrom.value = `${yyyy}-${mm}-01`;
    })();

    btnLoadIssue.addEventListener('click', async () => {
        if (issueBusy) return;
        await loadIssue();
    });

    btnProcessIssue.addEventListener('click', async () => {
        if (issueBusy) return;

        hideInfoIssue(issueInfo);

        // hanya H atau P-IFCA yg boleh diproses
        const ids = Array.from(issueTbody.querySelectorAll('.issueRowChk:checked'))
            .filter(chk => chk.dataset.stage === 'H' || (chk.dataset.stage === 'P' && chk.dataset.it === 'IFCA'))
            .map(chk => chk.value);

        if (ids.length === 0) {
            setInfoIssue(issueInfo, 'warn', 'Pilih minimal 1 Issue status H atau P-IFCA untuk diproses. Status D/C/P-SOLOMON tidak bisa.');
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

            setInfoIssue(issueInfo, 'ok',
                `Process done. Inserted(H->D lines): ${json.inserted_H_to_D ?? 0}, ` +
                `Sent OK(P->C issues): ${json.sent_success_P_to_C ?? 0}, ` +
                `Failed(P): ${json.sent_failed_still_P ?? 0}, ` +
                `Skipped(D): ${json.skipped_D ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );
        } catch (e) {
            setInfoIssue(issueInfo, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusyIssue(false);
        }

        await loadIssue();
    });
</script>