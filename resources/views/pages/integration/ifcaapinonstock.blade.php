<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="mb-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <div class="grid w-full grid-cols-1 gap-3 md:max-w-4xl md:grid-cols-3">
        <div>
            <label class="text-sm font-medium text-gray-600">Start Date</label>
            <input type="date" id="ns_from"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
            <label class="text-sm font-medium text-gray-600">End Date</label>
            <input type="date" id="ns_to"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div class="flex gap-2">
            <button type="button" id="btnLoadNonStock"
                class="mt-6 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
                Load
            </button>
            <button type="button" id="btnProcessNonStock"
                class="mt-6 w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Process
            </button>
        </div>
    </div>
</div>

<div id="nsInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="nsTotal">0</span>
        </div>
        <div class="text-sm text-gray-500">Limit 100 rows per load</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-white">
                <tr class="border-b border-gray-200 text-left text-gray-600">
                    <th class="w-10 px-3 py-2">
                        <input type="checkbox" id="nsChkAll" class="rounded border-gray-300">
                    </th>
                    <th class="w-56 px-3 py-2">Inventory ID</th>
                    <th class="px-3 py-2">Description</th>
                    <th class="w-28 px-3 py-2">UOM</th>
                    <th class="w-20 px-3 py-2">Status</th>
                    <th class="px-3 py-2">Response</th>
                    <th class="w-44 px-3 py-2">Last Update</th>
                </tr>
            </thead>
            <tbody id="nsTbody" class="divide-y divide-gray-100">
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-500">
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
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function setInfo(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');
        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');
        el.textContent = msg;
    }

    function hideInfo(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    // Nonstock refs
    const nsFrom = document.getElementById('ns_from');
    const nsTo = document.getElementById('ns_to');
    const nsTbody = document.getElementById('nsTbody');
    const nsTotal = document.getElementById('nsTotal');
    const nsInfo = document.getElementById('nsInfo');
    const nsChkAll = document.getElementById('nsChkAll');

    // default tanggal
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    nsTo.value = `${yyyy}-${mm}-${dd}`;
    nsFrom.value = `${yyyy}-${mm}-01`;

    function syncChkAllState() {
        const enabled = Array.from(nsTbody.querySelectorAll('.nsRowChk:not(:disabled)'));
        if (enabled.length === 0) {
            nsChkAll.checked = false;
            nsChkAll.indeterminate = false;
            nsChkAll.disabled = true;
            return;
        }
        nsChkAll.disabled = false;

        const checkedEnabled = enabled.filter(chk => chk.checked).length;
        nsChkAll.checked = checkedEnabled === enabled.length;
        nsChkAll.indeterminate = checkedEnabled > 0 && checkedEnabled < enabled.length;
    }

    nsChkAll.addEventListener('change', () => {
        nsTbody.querySelectorAll('.nsRowChk:not(:disabled)').forEach(chk => chk.checked = nsChkAll.checked);
        syncChkAllState();
    });

    function getStatusBadgeClass(stage) {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
    }

    function renderRows(rows) {
        nsTotal.textContent = rows.length;

        if (!rows.length) {
            nsTbody.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            nsChkAll.checked = false;
            nsChkAll.indeterminate = false;
            nsChkAll.disabled = true;
            return;
        }

        nsTbody.innerHTML = rows.map(r => {
            const stage = r.stage_status ?? 'H';
            const isC = stage === 'C';

            const trClass = isC ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const tdMain = isC ? 'text-gray-400' : 'text-gray-800';
            const checkboxClass = isC ? 'opacity-40 cursor-not-allowed' : '';

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2">
                        <input type="checkbox"
                            class="nsRowChk rounded border-gray-300 ${checkboxClass}"
                            value="${r.id}"
                            data-stage="${stage}"
                            ${isC ? 'disabled title="Sudah terkirim (C). Tidak bisa diproses."' : ''}
                        >
                    </td>
                    <td class="px-3 py-2 font-medium ${tdMain}">${r.inventoryid ?? ''}</td>
                    <td class="px-3 py-2 ${isC ? 'text-gray-400' : 'text-gray-700'}">${r.inventory_descr ?? ''}</td>
                    <td class="px-3 py-2 ${isC ? 'text-gray-400' : 'text-gray-700'}">${r.stock_unit ?? ''}</td>
                    <td class="px-3 py-2">
                        <span class="px-2 py-1 rounded text-sm font-semibold ${getStatusBadgeClass(stage)}">${stage}</span>
                    </td>
                    <td class="px-3 py-2 text-gray-600">${r.payload_response ?? ''}</td>
                    <td class="px-3 py-2 text-gray-600">${r.last_update ?? ''}</td>
                </tr>
            `;
        }).join('');

        nsTbody.querySelectorAll('.nsRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', syncChkAllState);
        });

        syncChkAllState();
    }

    document.getElementById('btnLoadNonStock').addEventListener('click', async () => {
        hideInfo(nsInfo);

        if (!nsFrom.value || !nsTo.value) {
            setInfo(nsInfo, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        nsTbody.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        nsChkAll.disabled = true;
        nsChkAll.checked = false;
        nsChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.nonstock.list') }}", window.location.origin);
        url.searchParams.set('from', nsFrom.value);
        url.searchParams.set('to', nsTo.value);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRows([]);
                setInfo(nsInfo, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            renderRows(rows);

            const readyCount = rows.filter(x => (x.stage_status ?? 'H') !== 'C').length;
            const doneCount = rows.length - readyCount;
            setInfo(nsInfo, 'ok', `Loaded ${rows.length} items. Ready: ${readyCount}. Completed(C): ${doneCount}.`);
        } catch (e) {
            renderRows([]);
            setInfo(nsInfo, 'err', e.message ?? 'Error saat load.');
        }
    });

    document.getElementById('btnProcessNonStock').addEventListener('click', async () => {
        hideInfo(nsInfo);

        const ids = Array.from(nsTbody.querySelectorAll('.nsRowChk:checked'))
            .filter(chk => chk.dataset.stage !== 'C')
            .map(chk => parseInt(chk.value, 10))
            .filter(n => !Number.isNaN(n));

        if (ids.length === 0) {
            setInfo(nsInfo, 'warn', 'Pilih minimal 1 item status H/P untuk diproses. Item C diabaikan.');
            return;
        }

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.nonstock.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();
            if (!resp.ok || !json.ok) {
                setInfo(nsInfo, 'err', json.message ?? 'Gagal process.');
                return;
            }

            setInfo(nsInfo, 'ok',
                `Process done. Inserted: ${json.inserted_H_to_P ?? 0}, Sent OK: ${json.sent_success_P_to_C ?? 0}, Failed: ${json.sent_failed_still_P ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );

            document.getElementById('btnLoadNonStock').click();
        } catch (e) {
            setInfo(nsInfo, 'err', e.message ?? 'Error saat process.');
        }
    });
</script>
