<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="mb-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <div class="grid w-full grid-cols-1 gap-3 md:max-w-4xl md:grid-cols-3">
        <div>
            <label class="text-sm font-medium text-gray-600">Start Date</label>
            <input type="date" id="sp_from"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
            <label class="text-sm font-medium text-gray-600">End Date</label>
            <input type="date" id="sp_to"
                class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div class="flex gap-2">
            <button type="button" id="btnLoadSupplier"
                class="mt-6 w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
                Load
            </button>
            <button type="button" id="btnProcessSupplier"
                class="mt-6 w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Process
            </button>
        </div>
    </div>
</div>

<div id="spInfo" class="mb-3 hidden rounded-lg border px-4 py-3 text-sm"></div>

<div class="overflow-hidden rounded-xl border border-gray-200">
    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
        <div class="text-sm text-gray-600">
            Total: <span class="font-semibold" id="spTotal">0</span>
        </div>
        <div class="text-sm text-gray-500">Limit 100 rows per load</div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-white">
                <tr class="border-b border-gray-200 text-left text-gray-600">
                    <th class="w-10 px-3 py-2">
                        <input type="checkbox" id="spChkAll" class="rounded border-gray-300">
                    </th>
                    <th class="w-44 px-3 py-2">Supplier Code</th>
                    <th class="px-3 py-2">Supplier Name</th>
                    <th class="w-56 px-3 py-2">NPWP</th>
                    <th class="w-20 px-3 py-2">Status</th>
                    <th class="px-3 py-2">Response</th>
                    <th class="w-44 px-3 py-2">Last Update</th>
                </tr>
            </thead>
            <tbody id="spTbody" class="divide-y divide-gray-100">
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
    const csrfSupplier = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function setInfoSupplier(el, type, msg) {
        el.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-800', 'border-red-200',
            'bg-red-50', 'text-red-800', 'border-yellow-200', 'bg-yellow-50', 'text-yellow-800');
        if (type === 'ok') el.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        if (type === 'err') el.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        if (type === 'warn') el.classList.add('border-yellow-200', 'bg-yellow-50', 'text-yellow-800');
        el.textContent = msg;
    }

    function hideInfoSupplier(el) {
        el.classList.add('hidden');
        el.textContent = '';
    }

    const spFromSupplier  = document.getElementById('sp_from');
    const spToSupplier    = document.getElementById('sp_to');
    const spTbodySupplier = document.getElementById('spTbody');
    const spTotalSupplier = document.getElementById('spTotal');
    const spInfoSupplier  = document.getElementById('spInfo');
    const spChkAllSupplier= document.getElementById('spChkAll');

    // default tanggal
    const todaySupplier = new Date();
    const yyyySupplier = todaySupplier.getFullYear();
    const mmSupplier = String(todaySupplier.getMonth() + 1).padStart(2, '0');
    const ddSupplier = String(todaySupplier.getDate()).padStart(2, '0');
    spToSupplier.value = `${yyyySupplier}-${mmSupplier}-${ddSupplier}`;
    spFromSupplier.value = `${yyyySupplier}-${mmSupplier}-01`;

    function syncChkAllStateSupplier() {
        const enabled = Array.from(spTbodySupplier.querySelectorAll('.spRowChk:not(:disabled)'));
        if (enabled.length === 0) {
            spChkAllSupplier.checked = false;
            spChkAllSupplier.indeterminate = false;
            spChkAllSupplier.disabled = true;
            return;
        }
        spChkAllSupplier.disabled = false;

        const checkedEnabled = enabled.filter(chk => chk.checked).length;
        spChkAllSupplier.checked = checkedEnabled === enabled.length;
        spChkAllSupplier.indeterminate = checkedEnabled > 0 && checkedEnabled < enabled.length;
    }

    spChkAllSupplier.addEventListener('change', () => {
        spTbodySupplier.querySelectorAll('.spRowChk:not(:disabled)').forEach(chk => chk.checked = spChkAllSupplier.checked);
        syncChkAllStateSupplier();
    });

    function getStatusBadgeClassSupplier(stage) {
        if (stage === 'H') return 'bg-gray-200 text-gray-800';
        if (stage === 'P') return 'bg-yellow-200 text-yellow-800';
        return 'bg-green-200 text-green-800';
    }

    function renderRowsSupplier(rows) {
        spTotalSupplier.textContent = rows.length;

        if (!rows.length) {
            spTbodySupplier.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            spChkAllSupplier.checked = false;
            spChkAllSupplier.indeterminate = false;
            spChkAllSupplier.disabled = true;
            return;
        }

        spTbodySupplier.innerHTML = rows.map(r => {
            const stage = r.stage_status ?? 'H';
            const isC = stage === 'C';

            const trClass = isC ? 'bg-gray-50 text-gray-400' : 'hover:bg-gray-50';
            const checkboxClass = isC ? 'opacity-40 cursor-not-allowed' : '';

            return `
                <tr class="${trClass}">
                    <td class="px-3 py-2">
                        <input type="checkbox"
                            class="spRowChk rounded border-gray-300 ${checkboxClass}"
                            value="${r.id}"
                            data-stage="${stage}"
                            ${isC ? 'disabled title="Sudah terkirim (C). Tidak bisa diproses."' : ''}>
                    </td>
                    <td class="px-3 py-2 font-medium">${r.vendor_id ?? ''}</td>
                    <td class="px-3 py-2 text-gray-700">${r.vendor_name ?? ''}</td>
                    <td class="px-3 py-2 text-gray-700">${r.npwp ?? ''}</td>
                    <td class="px-3 py-2">
                        <span class="px-2 py-1 rounded text-sm font-semibold ${getStatusBadgeClassSupplier(stage)}">${stage}</span>
                    </td>
                    <td class="px-3 py-2 text-gray-600">${r.payload_response ?? ''}</td>
                    <td class="px-3 py-2 text-gray-600">${r.last_update ?? ''}</td>
                </tr>
            `;
        }).join('');

        spTbodySupplier.querySelectorAll('.spRowChk:not(:disabled)').forEach(chk => {
            chk.addEventListener('change', syncChkAllStateSupplier);
        });

        syncChkAllStateSupplier();
    }

    document.getElementById('btnLoadSupplier').addEventListener('click', async () => {
        hideInfoSupplier(spInfoSupplier);

        if (!spFromSupplier.value || !spToSupplier.value) {
            setInfoSupplier(spInfoSupplier, 'warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        spTbodySupplier.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        spChkAllSupplier.disabled = true;
        spChkAllSupplier.checked = false;
        spChkAllSupplier.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.supplier.list') }}", window.location.origin);
        url.searchParams.set('from', spFromSupplier.value);
        url.searchParams.set('to', spToSupplier.value);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await resp.json();

            if (!resp.ok || !json.ok) {
                renderRowsSupplier([]);
                setInfoSupplier(spInfoSupplier, 'err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            renderRowsSupplier(rows);

            const readyCount = rows.filter(x => (x.stage_status ?? 'H') !== 'C').length;
            const doneCount = rows.length - readyCount;
            setInfoSupplier(spInfoSupplier, 'ok', `Loaded ${rows.length} items. Ready: ${readyCount}. Completed(C): ${doneCount}.`);
        } catch (e) {
            renderRowsSupplier([]);
            setInfoSupplier(spInfoSupplier, 'err', e.message ?? 'Error saat load.');
        }
    });

    document.getElementById('btnProcessSupplier').addEventListener('click', async () => {
        hideInfoSupplier(spInfoSupplier);

        const ids = Array.from(spTbodySupplier.querySelectorAll('.spRowChk:checked'))
            .filter(chk => chk.dataset.stage !== 'C')
            .map(chk => parseInt(chk.value, 10))
            .filter(n => !Number.isNaN(n));

        if (ids.length === 0) {
            setInfoSupplier(spInfoSupplier, 'warn', 'Pilih minimal 1 supplier status H/P untuk diproses. Supplier C diabaikan.');
            return;
        }

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.supplier.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfSupplier
                },
                body: JSON.stringify({ ids })
            });

            const json = await resp.json();
            if (!resp.ok || !json.ok) {
                setInfoSupplier(spInfoSupplier, 'err', json.message ?? 'Gagal process.');
                return;
            }

            setInfoSupplier(spInfoSupplier, 'ok',
                `Process done. Inserted: ${json.inserted_H_to_P ?? 0}, Sent OK: ${json.sent_success_P_to_C ?? 0}, Failed: ${json.sent_failed_still_P ?? 0}, Skipped(C): ${json.skipped_C ?? 0}`
            );

            document.getElementById('btnLoadSupplier').click();
        } catch (e) {
            setInfoSupplier(spInfoSupplier, 'err', e.message ?? 'Error saat process.');
        }
    });
</script>
