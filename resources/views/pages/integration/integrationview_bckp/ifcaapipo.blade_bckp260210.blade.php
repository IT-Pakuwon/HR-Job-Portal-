<meta name="csrf-token" content="{{ csrf_token() }}">

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
                class="mt-6 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
                Load
            </button>
            <button type="button" id="btnProcessPO"
                class="mt-6 w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
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
                    <th class="w-20 px-3 py-2">Status</th>
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
        H = belum ada di staging, P = di staging belum terkirim, C = sudah terkirim (disabled).
    </div>
</div>

<script>
    const csrfPO = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

    const poFrom  = document.getElementById('po_from');
    const poTo    = document.getElementById('po_to');
    const poTbody = document.getElementById('poTbody');
    const poTotal = document.getElementById('poTotal');
    const poInfo  = document.getElementById('poInfo');
    const poChkAll= document.getElementById('poChkAll');

    // default tanggal
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    poTo.value = `${yyyy}-${mm}-${dd}`;
    poFrom.value = `${yyyy}-${mm}-01`;

    function getStatusBadgeClassPO(stage) {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
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
        poTbody.querySelectorAll('.poRowChk:not(:disabled)').forEach(chk => chk.checked = poChkAll.checked);
        syncChkAllStatePO();
    });

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
            const stage = r.stage_status ?? 'H';
            const isC = stage === 'C';

            const trClass = isC ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const checkboxClass = isC ? 'opacity-40 cursor-not-allowed' : '';

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2">
                        <input type="checkbox"
                            class="poRowChk rounded border-gray-300 ${checkboxClass}"
                            value="${r.key}"
                            data-stage="${stage}"
                            ${isC ? 'disabled title="Sudah terkirim (C). Tidak bisa diproses."' : ''}>
                    </td>
                    <td class="px-3 py-2 font-medium">${r.cpny_id ?? ''}</td>
                    <td class="px-3 py-2 font-medium">${r.order_no ?? ''}</td>
                    <td class="px-3 py-2">${r.order_date ?? ''}</td>
                    <td class="px-3 py-2">${r.supplier_cd ?? ''}</td>
                    <td class="px-3 py-2">
                        <span class="px-2 py-1 rounded text-sm font-semibold ${getStatusBadgeClassPO(stage)}">${stage}</span>
                    </td>
                    <td class="px-3 py-2 text-gray-600">${r.payload_response ?? ''}</td>
                    <td class="px-3 py-2 text-gray-600">${r.last_update ?? ''}</td>
                </tr>
            `;
        }).join('');

        poTbody.querySelectorAll('.poRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', syncChkAllStatePO);
        });

        syncChkAllStatePO();
    }

    document.getElementById('btnLoadPO').addEventListener('click', async () => {
        hideInfoPO(poInfo);

        if (!poFrom.value || !poTo.value) {
            setInfoPO(poInfo, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

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

            const readyCount = rows.filter(x => (x.stage_status ?? 'H') !== 'C').length;
            const doneCount = rows.length - readyCount;
            setInfoPO(poInfo, 'ok', `Loaded ${rows.length} PO. Ready: ${readyCount}. Completed(C): ${doneCount}.`);
        } catch (e) {
            renderRowsPO([]);
            setInfoPO(poInfo, 'err', e.message ?? 'Error saat load.');
        }
    });

    document.getElementById('btnProcessPO').addEventListener('click', async () => {
        hideInfoPO(poInfo);

        const ids = Array.from(poTbody.querySelectorAll('.poRowChk:checked'))
            .filter(chk => chk.dataset.stage !== 'C')
            .map(chk => chk.value);

        if (ids.length === 0) {
            setInfoPO(poInfo, 'warn', 'Pilih minimal 1 PO status H/P untuk diproses. PO C diabaikan.');
            return;
        }

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
                `Process done. Inserted(H->P lines): ${json.inserted_H_to_P ?? 0}, Sent OK(P->C orders): ${json.sent_success_P_to_C ?? 0}, Failed: ${json.sent_failed_still_P ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );

            document.getElementById('btnLoadPO').click();
        } catch (e) {
            setInfoPO(poInfo, 'err', e.message ?? 'Error saat process.');
        }
    });
</script>
