<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- =========================
     FULLSCREEN BUSY OVERLAY
     ========================= --}}
<div id="slIssueBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
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
                <div class="font-semibold text-gray-800" id="slIssueBusyTitle">Processing...</div>
                <div class="text-gray-500" id="slIssueBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-9xl mx-auto p-4">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-extrabold text-gray-800 dark:text-white">
            📦 ISSUE Solomon (P-Solomon → C)
        </h1>
    </div>

    <div class="rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Cpny ID</label>
                <input id="sl_cpny_id" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:bg-gray-900 dark:text-white"
                       placeholder="ex: PKW">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Issue ID</label>
                <input id="sl_issue_id" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:bg-gray-900 dark:text-white"
                       placeholder="optional">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Limit</label>
                <input id="sl_limit" value="200"
                       class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:bg-gray-900 dark:text-white">
            </div>
            <div class="flex items-end gap-2">
                <button type="button" id="btnLoadSLIssue"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Load
                </button>
                <button type="button" id="btnProcessSLIssue"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Process
                </button>
            </div>
        </div>
    </div>

    {{-- INFO BOX --}}
    <div id="slIssueInfo" class="hidden mt-3 rounded-xl border p-3 text-sm"></div>

    <div class="mt-4 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
        <div class="mb-3 flex items-center justify-between">
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Total: <span class="font-bold" id="slTotal">0</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="btnCheckAll"
                        class="rounded-md border px-3 py-1 text-sm dark:text-white">Check All</button>
                <button type="button" id="btnUncheckAll"
                        class="rounded-md border px-3 py-1 text-sm dark:text-white">Uncheck</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="border-b bg-gray-50 text-left dark:bg-gray-900 dark:text-white">
                    <th class="p-2 w-12">#</th>
                    <th class="p-2">Cpny</th>
                    <th class="p-2">Issue ID</th>
                    <th class="p-2">Dept</th>
                    <th class="p-2">Peminta</th>
                    <th class="p-2">WOID</th>
                    <th class="p-2">Total</th>
                    <th class="p-2">Created</th>
                    <th class="p-2">Status</th>
                </tr>
                </thead>
                <tbody id="slIssueTbody" class="dark:text-white">
                    <tr>
                        <td colspan="9" class="p-4 text-center text-gray-500">Klik Load untuk mengambil data.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const overlay = document.getElementById('slIssueBusyOverlay');
    const busyTitle = document.getElementById('slIssueBusyTitle');
    const busySub = document.getElementById('slIssueBusySub');

    const infoBox = document.getElementById('slIssueInfo');
    const tbody = document.getElementById('slIssueTbody');
    const totalEl = document.getElementById('slTotal');

    const inpCpny = document.getElementById('sl_cpny_id');
    const inpIssue = document.getElementById('sl_issue_id');
    const inpLimit = document.getElementById('sl_limit');

    const btnLoad = document.getElementById('btnLoadSLIssue');
    const btnProc = document.getElementById('btnProcessSLIssue');

    function setBusy(on, title = 'Processing...', sub = 'Mohon tunggu.') {
        busyTitle.textContent = title;
        busySub.textContent = sub;
        overlay.classList.toggle('hidden', !on);
        btnLoad.disabled = on;
        btnProc.disabled = on;
    }

    function setInfo(type, msg) {
        infoBox.classList.remove('hidden');
        infoBox.className = 'mt-3 rounded-xl border p-3 text-sm ' + (
            type === 'ok' ? 'border-green-200 bg-green-50 text-green-800' :
            type === 'warn' ? 'border-yellow-200 bg-yellow-50 text-yellow-800' :
            'border-red-200 bg-red-50 text-red-800'
        );
        infoBox.textContent = msg;
    }

    function hideInfo() { infoBox.classList.add('hidden'); }

    function badge(st) {
        const s = (st || '').toUpperCase();
        if (s === 'C') return `<span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">C</span>`;
        if (s === 'D') return `<span class="inline-flex rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700">D</span>`;
        return `<span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">P</span>`;
    }

    function render(rows) {
        totalEl.textContent = rows.length;

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="9" class="p-4 text-center text-gray-500">No data.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => {
            const key = `${r.cpny_id}||${r.issue_id}`;
            const st  = (r.stage_status || 'P').toUpperCase();
            const canPick = (st === 'P');

            return `
                <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-900/40">
                    <td class="p-2">
                        <input type="checkbox" class="rowCheck" value="${key}" ${canPick ? '' : 'disabled'}>
                    </td>
                    <td class="p-2 font-semibold">${r.cpny_id ?? ''}</td>
                    <td class="p-2">${r.issue_id ?? ''}</td>
                    <td class="p-2">${r.deptid ?? ''}</td>
                    <td class="p-2">${r.peminta ?? ''}</td>
                    <td class="p-2">${r.woid ?? ''}</td>
                    <td class="p-2">${r.total_record ?? ''}</td>
                    <td class="p-2">${r.crtd_datetime ?? ''}</td>
                    <td class="p-2">${badge(st)}</td>
                </tr>
            `;
        }).join('');
    }

    document.getElementById('btnCheckAll').addEventListener('click', () => {
        document.querySelectorAll('.rowCheck').forEach(cb => {
            if (!cb.disabled) cb.checked = true;
        });
    });

    document.getElementById('btnUncheckAll').addEventListener('click', () => {
        document.querySelectorAll('.rowCheck').forEach(cb => cb.checked = false);
    });

    async function loadData() {
        hideInfo();

        const cpny = (inpCpny.value || '').trim();
        const issue = (inpIssue.value || '').trim();
        const limit = (inpLimit.value || '200').trim();

        // ✅ validasi IFCA-style
        if (!cpny && !issue) {
            setInfo('warn', 'Cpny ID atau Issue ID wajib diisi minimal salah satu.');
            return;
        }

        setBusy(true, 'Loading Issue Solomon...', 'Ambil data staging (status P-Solomon).');

        tbody.innerHTML = `<tr><td colspan="9" class="p-4 text-center text-gray-500">Loading...</td></tr>`;
        totalEl.textContent = '0';

        try {
            const url = new URL(`{{ route('integration.ifcaintegration.issuesolomon.list') }}`, window.location.origin);
            if (cpny) url.searchParams.set('cpny_id', cpny);
            if (issue) url.searchParams.set('issue_id', issue);
            if (limit) url.searchParams.set('limit', limit);

            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await res.json();

            if (!res.ok || !json.ok) {
                render([]);
                setInfo('err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            render(rows);

            const ready = rows.filter(x => (x.stage_status || 'P') === 'P').length;
            const done  = rows.filter(x => (x.stage_status || '') === 'C').length;
            const wait  = rows.filter(x => (x.stage_status || '') === 'D').length;

            setInfo('ok', `Loaded ${rows.length} Issue. Ready(P): ${ready}, Waiting(D): ${wait}, Completed(C): ${done}.`);
        } catch (e) {
            render([]);
            setInfo('err', e.message ?? 'Error saat load.');
        } finally {
            setBusy(false);
        }
    }

    btnLoad.addEventListener('click', loadData);

    btnProc.addEventListener('click', async () => {
        hideInfo();

        const ids = Array.from(document.querySelectorAll('.rowCheck'))
            .filter(cb => cb.checked && !cb.disabled)
            .map(cb => cb.value);

        if (!ids.length) {
            setInfo('warn', 'Pilih minimal 1 Issue dengan status P.');
            return;
        }

        setBusy(true, 'Processing Issue Solomon...', 'Insert ke SQL Server (StagingAcum) lalu update status P→C.');

        try {
            const res = await fetch(`{{ route('integration.ifcaintegration.issuesolomon.process') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids })
            });

            const json = await res.json();

            if (!res.ok || !json.ok) {
                setInfo('err', json.message ?? 'Process gagal.');
                return;
            }

            setInfo('ok', `Process done. Sent OK(P→C): ${json.sent_success_P_to_C ?? 0}, Failed(P): ${json.sent_failed_still_P ?? 0}`);
        } catch (e) {
            setInfo('err', e.message ?? 'Error saat process.');
        } finally {
            setBusy(false);
        }

        await loadData();
    });
</script>