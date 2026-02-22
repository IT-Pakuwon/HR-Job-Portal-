<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- =========================
     FULLSCREEN BUSY OVERLAY (BAST)
     ========================= --}}
<div id="bastBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
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
                <div class="font-semibold text-gray-800" id="bastBusyTitle">Processing...</div>
                <div class="text-gray-500" id="bastBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <div class="grid w-full grid-cols-1 gap-3 md:max-w-4xl md:grid-cols-3">
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
        <div class="flex gap-2">
            <button type="button" id="btnLoadBAST"
                    class="mt-6 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
                Load
            </button>
            <button type="button" id="btnProcessBAST"
                    class="mt-6 w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                Process
            </button>
        </div>
    </div>
</div>

<div id="bastInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="bastTotal">0</span>
        </div>
        <div class="text-sm text-gray-500">Limit 100 rows per load</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2">
                    <input type="checkbox" id="bastChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-24 px-3 py-2">Company</th>
                <th class="w-48 px-3 py-2">BAST No</th>
                <th class="w-44 px-3 py-2">BAST Date</th>
                <th class="w-44 px-3 py-2">PO No</th>
                <th class="w-32 px-3 py-2">Supplier</th>
                <th class="w-24 px-3 py-2">Status</th>
                <th class="px-3 py-2">Response</th>
                <th class="w-44 px-3 py-2">Last Update</th>
            </tr>
            </thead>
            <tbody id="bastTbody" class="divide-y divide-gray-100">
            <tr>
                <td colspan="9" class="px-4 py-10 text-center text-gray-500">
                    Belum ada data. Klik Load.
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-200 bg-white px-4 py-2 text-xs text-gray-500">
        <span class="font-semibold">Legend:</span>
        H = belum ada di staging (boleh insert ke P),
        P = ready / retry kirim API,
        C = completed (disabled).
    </div>
</div>

<script>
    // =========================
    // BAST Integration (H -> P -> C)
    // =========================
    const csrfBAST = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const bastFrom       = document.getElementById('bast_from');
    const bastTo         = document.getElementById('bast_to');
    const bastTbody      = document.getElementById('bastTbody');
    const bastTotal      = document.getElementById('bastTotal');
    const bastInfo       = document.getElementById('bastInfo');
    const bastChkAll     = document.getElementById('bastChkAll');
    const btnLoadBAST    = document.getElementById('btnLoadBAST');
    const btnProcessBAST = document.getElementById('btnProcessBAST');

    const bastBusyOverlay = document.getElementById('bastBusyOverlay');
    const bastBusyTitle   = document.getElementById('bastBusyTitle');
    const bastBusySub     = document.getElementById('bastBusySub');

    let bastBusy = false;

    function setInfoBAST(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');
        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');
        el.textContent = msg;
    }

    function hideInfoBAST(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    function getStatusBadgeClassBAST(stage) {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800'; // C
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
        bastTotal.textContent = rows.length;

        if (!rows.length) {
            bastTbody.innerHTML = `<tr><td colspan="9" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            bastChkAll.checked = false;
            bastChkAll.indeterminate = false;
            bastChkAll.disabled = true;
            return;
        }

        bastTbody.innerHTML = rows.map(r => {
            const stage = (r.stage_status ?? 'H');
            const disabled = (stage === 'C');

            const trClass = disabled ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const checkboxClass = disabled ? 'opacity-40 cursor-not-allowed' : '';

            let title = '';
            if (stage === 'C') title = 'Sudah completed (C). Tidak bisa diproses.';

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2">
                        <input type="checkbox"
                            class="bastRowChk rounded border-gray-300 ${checkboxClass}"
                            value="${r.key}"
                            data-stage="${stage}"
                            ${disabled ? `disabled title="${title}"` : ''}>
                    </td>
                    <td class="px-3 py-2 font-medium">${r.cpny_id ?? ''}</td>
                    <td class="px-3 py-2 font-medium">${r.grn_no ?? ''}</td>
                    <td class="px-3 py-2">${r.grn_date ?? ''}</td>
                    <td class="px-3 py-2">${r.order_no ?? ''}</td>
                    <td class="px-3 py-2">${r.supplier_cd ?? ''}</td>
                    <td class="px-3 py-2">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassBAST(stage)}">
                            ${stage}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-gray-600">${r.payload_response ?? ''}</td>
                    <td class="px-3 py-2 text-gray-600">${r.last_update ?? ''}</td>
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
        btnLoadBAST.disabled = isBusy;
        btnProcessBAST.disabled = isBusy;
        bastChkAll.disabled = isBusy;

        bastTbody.querySelectorAll('.bastRowChk').forEach(chk => {
            if (isBusy) {
                chk.disabled = true;
                return;
            }
            const stage = String(chk.dataset.stage ?? '').toUpperCase();
            chk.disabled = (stage === 'C');
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

    async function loadBAST() {
        hideInfoBAST(bastInfo);

        if (!bastFrom.value || !bastTo.value) {
            setInfoBAST(bastInfo, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        setBusyBAST(true, 'Loading BAST...', 'Sedang mengambil data BAST dari Purchasing.');

        bastTbody.innerHTML = `<tr><td colspan="9" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        bastChkAll.disabled = true;
        bastChkAll.checked = false;
        bastChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.bast.list') }}", window.location.origin);
        url.searchParams.set('from', bastFrom.value);
        url.searchParams.set('to', bastTo.value);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsBAST([]);
                setInfoBAST(bastInfo, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            renderRowsBAST(rows);

            const readyCount = rows.filter(x => {
                const st = x.stage_status ?? 'H';
                return st === 'H' || st === 'P';
            }).length;

            const doneCount = rows.filter(x => (x.stage_status ?? '') === 'C').length;

            setInfoBAST(bastInfo, 'ok',
                `Loaded ${rows.length} BAST. Ready(H/P): ${readyCount}. Completed(C): ${doneCount}.`
            );
        } catch (e) {
            renderRowsBAST([]);
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
        await loadBAST();
    });

    btnProcessBAST.addEventListener('click', async () => {
        if (bastBusy) return;

        hideInfoBAST(bastInfo);

        const ids = Array.from(bastTbody.querySelectorAll('.bastRowChk:checked'))
            .filter(chk => chk.dataset.stage === 'H' || chk.dataset.stage === 'P')
            .map(chk => chk.value);

        if (ids.length === 0) {
            setInfoBAST(bastInfo, 'warn', 'Pilih minimal 1 BAST status H atau P untuk diproses. Status C tidak bisa.');
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

            setInfoBAST(bastInfo, 'ok',
                `Process done. Inserted(H->P lines): ${json.inserted_H_to_P ?? 0}, ` +
                `Sent OK(P->C BAST): ${json.sent_success_P_to_C ?? 0}, ` +
                `Failed(still P): ${json.sent_failed_still_P ?? 0}, ` +
                `Skipped(C): ${json.skipped_C ?? 0}`
            );
        } catch (e) {
            setInfoBAST(bastInfo, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusyBAST(false);
        }

        await loadBAST();
    });
</script>