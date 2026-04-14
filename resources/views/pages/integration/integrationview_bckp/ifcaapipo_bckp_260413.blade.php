<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- =========================
     FULLSCREEN BUSY OVERLAY
     ========================= --}}
<div id="poBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
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
                <div class="font-semibold text-gray-800" id="poBusyTitle">Processing...</div>
                <div class="text-gray-500" id="poBusySub">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <div class="grid w-full grid-cols-1 gap-3 md:max-w-4xl md:grid-cols-3">
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
        <div class="flex gap-2">
            <button type="button" id="btnLoadPO"
                    class="mt-6 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
                Load
            </button>
            <button type="button" id="btnProcessPO"
                    class="mt-6 w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                Process
            </button>
        </div>
    </div>
</div>

<div id="poInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="poTotal">0</span>
        </div>
        <div class="text-sm text-gray-500">Limit 100 rows per load</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-white">
            <tr class="border-b border-gray-200 text-left text-gray-600">
                <th class="w-10 px-3 py-2">
                    <input type="checkbox" id="poChkAll" class="rounded border-gray-300">
                </th>
                <th class="w-24 px-3 py-2">Company</th>
                <th class="w-40 px-3 py-2">Order No</th>
                <th class="w-44 px-3 py-2">Order Date</th>
                <th class="w-32 px-3 py-2">Supplier</th>
                <th class="w-32 px-3 py-2">Status</th>
                <th class="px-3 py-2">Response</th>
                <th class="w-44 px-3 py-2">Last Update</th>
            </tr>
            </thead>
            <tbody id="poTbody" class="divide-y divide-gray-100">
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
    // PO Integration (H -> D -> P -> C)
    // =========================
    const csrfPO = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // refs
    const poFrom        = document.getElementById('po_from');
    const poTo          = document.getElementById('po_to');
    const poTbody       = document.getElementById('poTbody');
    const poTotal       = document.getElementById('poTotal');
    const poInfo        = document.getElementById('poInfo');
    const poChkAll      = document.getElementById('poChkAll');
    const btnLoadPO     = document.getElementById('btnLoadPO');
    const btnProcessPO  = document.getElementById('btnProcessPO');

    // overlay refs
    const poBusyOverlay = document.getElementById('poBusyOverlay');
    const poBusyTitle   = document.getElementById('poBusyTitle');
    const poBusySub     = document.getElementById('poBusySub');

    let poBusy = false;

    function getTabEls() {
        return Array.from(document.querySelectorAll('a, button'))
            .filter(el => {
                if (!el || el === btnLoadPO || el === btnProcessPO) return false;
                const txt = (el.textContent || '').trim().toLowerCase();
                return ['non stock','stock','supplier','po','sttb','bast','issue','receipt'].some(k => txt === k || txt.includes(k));
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

    // badge class
    function getStatusBadgeClassPO(stage, it = '') {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'D') return 'bg-blue-200 text-blue-800';
        if (stage === 'P' && it === 'SOLOMON') return 'bg-orange-200 text-orange-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800'; // C
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

    // =========================
    // Opsi A:
    // - renderRowsPO tidak pakai "poBusy" untuk menentukan disabled
    // - saat busy, kita disable semua row checkbox via setBusyPO()
    // =========================
    function renderRowsPO(rows) {
        poTotal.textContent = rows.length;

        if (!rows.length) {
            poTbody.innerHTML = `<tr><td colspan="8" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            poChkAll.checked = false;
            poChkAll.indeterminate = false;
            poChkAll.disabled = true;
            return;
        }

        poTbody.innerHTML = rows.map(r => {
            const stage = (r.stage_status ?? 'H');
            const it = String(r.integration_type ?? '').trim().toUpperCase(); // ✅ trim biar aman
            const stageLabel = (r.stage_label ?? stage);

            // disable rules (tanpa poBusy)
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
                            class="poRowChk rounded border-gray-300 ${checkboxClass}"
                            value="${r.key}"
                            data-stage="${stage}"
                            data-it="${it}"
                            ${disabled ? `disabled title="${title}"` : ''}>
                    </td>
                    <td class="px-3 py-2 font-medium">${r.cpny_id ?? ''}</td>
                    <td class="px-3 py-2 font-medium">${r.order_no ?? ''}</td>
                    <td class="px-3 py-2">${r.order_date ?? ''}</td>
                    <td class="px-3 py-2">${r.supplier_cd ?? ''}</td>
                    <td class="px-3 py-2">
                        <span class="inline-flex items-center whitespace-nowrap px-2 py-1 rounded text-xs font-semibold ${getStatusBadgeClassPO(stage, it)}">
                            ${stageLabel}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-gray-600">${r.payload_response ?? ''}</td>
                    <td class="px-3 py-2 text-gray-600">${r.last_update ?? ''}</td>
                </tr>
            `;
        }).join('');

        poTbody.querySelectorAll('.poRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', () => {
                if (poBusy) return;
                syncChkAllStatePO();
            });
        });

        // kalau sedang busy, pastikan semua row checkbox tetap off
        if (poBusy) {
            poTbody.querySelectorAll('.poRowChk').forEach(chk => chk.disabled = true);
        }

        syncChkAllStatePO();
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
        btnLoadPO.disabled = isBusy;
        btnProcessPO.disabled = isBusy;
        poChkAll.disabled = isBusy;

        // disable tab/menu (optional)
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

        // ✅ Opsi A (FIX): saat busy => disable semua
        // saat tidak busy => hitung ulang disabled sesuai rule stage (JANGAN restore dari cache)
        const rowChks = poTbody.querySelectorAll('.poRowChk');
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

    async function loadPO() {
        hideInfoPO(poInfo);

        if (!poFrom.value || !poTo.value) {
            setInfoPO(poInfo, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        setBusyPO(true, 'Loading PO...', 'Sedang mengambil data PO dari Purchasing.');

        poTbody.innerHTML = `<tr><td colspan="8" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        poChkAll.disabled = true;
        poChkAll.checked = false;
        poChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.po.list') }}", window.location.origin);
        url.searchParams.set('from', poFrom.value);
        url.searchParams.set('to', poTo.value);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsPO([]);
                setInfoPO(poInfo, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            renderRowsPO(rows);

            // Ready hanya H dan P-IFCA
            const readyCount = rows.filter(x => {
                const st = x.stage_status ?? 'H';
                const it = String(x.integration_type ?? '').trim().toUpperCase();
                return st === 'H' || (st === 'P' && it === 'IFCA');
            }).length;

            const waitingReview = rows.filter(x => (x.stage_status ?? '') === 'D').length;
            const doneCount     = rows.filter(x => (x.stage_status ?? '') === 'C').length;

            setInfoPO(poInfo, 'ok',
                `Loaded ${rows.length} PO. Ready(H/P-IFCA): ${readyCount}. Waiting Review(D): ${waitingReview}. Completed(C): ${doneCount}.`
            );
        } catch (e) {
            renderRowsPO([]);
            setInfoPO(poInfo, 'err', e.message ?? 'Error saat load.');
        } finally {
            setBusyPO(false);
        }
    }

    (function initDefaultDatesPO() {
        const todayPO = new Date();
        const yyyyPO = todayPO.getFullYear();
        const mmPO = String(todayPO.getMonth() + 1).padStart(2, '0');
        const ddPO = String(todayPO.getDate()).padStart(2, '0');
        poTo.value = `${yyyyPO}-${mmPO}-${ddPO}`;
        poFrom.value = `${yyyyPO}-${mmPO}-01`;
    })();

    btnLoadPO.addEventListener('click', async () => {
        if (poBusy) return;
        await loadPO();
    });

    btnProcessPO.addEventListener('click', async () => {
        if (poBusy) return;

        hideInfoPO(poInfo);

        // hanya H atau P-IFCA yg boleh diproses
        const ids = Array.from(poTbody.querySelectorAll('.poRowChk:checked'))
            .filter(chk => chk.dataset.stage === 'H' || (chk.dataset.stage === 'P' && chk.dataset.it === 'IFCA'))
            .map(chk => chk.value);

        if (ids.length === 0) {
            setInfoPO(poInfo, 'warn', 'Pilih minimal 1 PO status H atau P-IFCA untuk diproses. Status D/C/P-SOLOMON tidak bisa.');
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

            setInfoPO(poInfo, 'ok',
                `Process done. Inserted(H->D lines): ${json.inserted_H_to_D ?? 0}, ` +
                `Sent OK(P->C orders): ${json.sent_success_P_to_C ?? 0}, ` +
                `Failed(P): ${json.sent_failed_still_P ?? 0}, ` +
                `Skipped(D): ${json.skipped_D ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );
        } catch (e) {
            setInfoPO(poInfo, 'err', e.message ?? 'Error saat process.');
        } finally {
            setBusyPO(false);
        }

        await loadPO();
    });
</script>
