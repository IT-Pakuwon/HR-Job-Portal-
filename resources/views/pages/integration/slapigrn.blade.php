<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="slGrnBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
    <div class="absolute inset-0 bg-black/40 pointer-events-auto"></div>

    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div
            class="pointer-events-auto rounded-xl bg-white px-5 py-4 shadow-lg border border-gray-200 flex items-center gap-3">
            <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>

            <div>
                <div id="slGrnBusyTitle" class="font-semibold text-gray-800">Processing...</div>
                <div id="slGrnBusySub" class="text-sm text-gray-500">Mohon tunggu...</div>
            </div>
        </div>
    </div>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-4">
    <div class="mb-3">
        <div class="text-lg font-bold text-gray-800">📥 GRN Solomon (P-Solomon → C)</div>
        <div class="text-sm text-gray-500">
            Load data staging berdasarkan range tanggal, lalu proses P → C
        </div>
    </div>

    <div id="slGrnInfo" class="hidden mt-3 rounded-md border px-4 py-3 text-sm"></div>

    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
            <div class="md:col-span-3">
                <label class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" id="slGrnFrom"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="md:col-span-3">
                <label class="mb-1 block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" id="slGrnTo"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="md:col-span-6 flex items-end justify-end gap-2">
                <button type="button" id="btnLoadSlGrn"
                    class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Load
                </button>

                <button type="button" id="btnProcessSlGrn"
                    class="inline-flex items-center justify-center rounded-md bg-green-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                    Process
                </button>
            </div>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-gray-200">
        <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
            <div class="text-sm font-semibold text-gray-700">
                Total: <span id="slGrnTotal">0</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="w-10 px-4 py-2 text-left">
                            <input type="checkbox" id="slGrnChkAll" class="rounded border-gray-300">
                        </th>
                        <th class="px-4 py-2 text-left">Cpny</th>
                        <th class="px-4 py-2 text-left">GRN No</th>
                        <th class="px-4 py-2 text-left">GRN Date</th>
                        <th class="px-4 py-2 text-left">PO No</th>
                        <th class="px-4 py-2 text-left">Supplier</th>
                        <th class="px-4 py-2 text-left">Created</th>
                        <th class="px-4 py-2 text-center">Status</th>
                    </tr>
                </thead>

                <tbody id="slGrnTbody" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-500">Belum ada data. Klik Load.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 text-xs text-gray-500 bg-white">
            Legend: P = ready (P-SOLOMON), D = waiting review, C = completed (tidak bisa diproses).
        </div>
    </div>
</div>

<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const el = {
        overlay: document.getElementById('slGrnBusyOverlay'),
        busyTitle: document.getElementById('slGrnBusyTitle'),
        busySub: document.getElementById('slGrnBusySub'),
        from: document.getElementById('slGrnFrom'),
        to: document.getElementById('slGrnTo'),
        btnLoad: document.getElementById('btnLoadSlGrn'),
        btnProcess: document.getElementById('btnProcessSlGrn'),
        info: document.getElementById('slGrnInfo'),
        total: document.getElementById('slGrnTotal'),
        chkAll: document.getElementById('slGrnChkAll'),
        tbody: document.getElementById('slGrnTbody'),
    };

    if (!el.from || !el.to || !el.btnLoad || !el.btnProcess || !el.tbody) return;

    let isBusy = false;

    function hideInfo() {
        el.info.classList.add('hidden');
        el.info.textContent = '';
        el.info.className = 'hidden mt-3 rounded-md border px-4 py-3 text-sm';
    }

    function setInfo(type, msg) {
        el.info.classList.remove('hidden');
        el.info.textContent = msg;
        el.info.className = 'mt-3 rounded-md border px-4 py-3 text-sm';

        if (type === 'ok') {
            el.info.classList.add('bg-green-50', 'border-green-200', 'text-green-800');
        } else if (type === 'warn') {
            el.info.classList.add('bg-yellow-50', 'border-yellow-200', 'text-yellow-800');
        } else {
            el.info.classList.add('bg-red-50', 'border-red-200', 'text-red-800');
        }
    }

    function setBusy(busy, title = 'Processing...', sub = 'Mohon tunggu...') {
        isBusy = busy;

        if (busy) {
            el.busyTitle.textContent = title;
            el.busySub.textContent = sub;
            el.overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            el.overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        el.from.disabled = busy;
        el.to.disabled = busy;
        el.btnLoad.disabled = busy;
        el.btnProcess.disabled = busy;
        el.chkAll.disabled = busy;

        el.tbody.querySelectorAll('.slGrnRowChk').forEach(chk => {
            if (busy) {
                chk.disabled = true;
            } else {
                const stage = String(chk.dataset.stage || '').toUpperCase();
                chk.disabled = stage !== 'P';
                if (chk.disabled) chk.checked = false;
            }
        });

        syncChkAllState();

        if (busy) {
            el.btnLoad.dataset.txt = el.btnLoad.textContent;
            el.btnProcess.dataset.txt = el.btnProcess.textContent;
            el.btnLoad.textContent = 'Loading...';
            el.btnProcess.textContent = 'Processing...';
        } else {
            if (el.btnLoad.dataset.txt) el.btnLoad.textContent = el.btnLoad.dataset.txt;
            if (el.btnProcess.dataset.txt) el.btnProcess.textContent = el.btnProcess.dataset.txt;
        }
    }

    function syncChkAllState() {
        const enabled = Array.from(el.tbody.querySelectorAll('.slGrnRowChk')).filter(chk => !chk.disabled);

        if (enabled.length === 0) {
            el.chkAll.checked = false;
            el.chkAll.indeterminate = false;
            el.chkAll.disabled = true;
            return;
        }

        el.chkAll.disabled = isBusy;
        const checkedCount = enabled.filter(chk => chk.checked).length;
        el.chkAll.checked = checkedCount === enabled.length;
        el.chkAll.indeterminate = checkedCount > 0 && checkedCount < enabled.length;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function fmtDate(value) {
        if (!value) return '';
        try {
            const d = new Date(value);
            if (isNaN(d.getTime())) return String(value);

            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            const hh = String(d.getHours()).padStart(2, '0');
            const mi = String(d.getMinutes()).padStart(2, '0');
            const ss = String(d.getSeconds()).padStart(2, '0');

            return `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;
        } catch {
            return String(value);
        }
    }

    function badge(stage) {
        const s = String(stage || '').toUpperCase();

        if (s === 'C') {
            return `<span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">C</span>`;
        }
        if (s === 'D') {
            return `<span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700">D</span>`;
        }
        return `<span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">P</span>`;
    }

    function renderRows(rows) {
        el.total.textContent = String(rows?.length || 0);

        if (!rows || rows.length === 0) {
            el.tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-gray-500">No data.</td>
                </tr>
            `;
            el.chkAll.checked = false;
            el.chkAll.indeterminate = false;
            el.chkAll.disabled = true;
            return;
        }

        el.tbody.innerHTML = rows.map(r => {
            const stage = String(r.stage_status ?? 'D').toUpperCase();
            const cpny = String(r.cpny_id ?? '');
            const grnNo = String(r.grn_no ?? r.receipt_no ?? '');
            const key = String(r.key ?? `${cpny}||${grnNo}`);
            const disabled = stage !== 'P' ? 'disabled' : '';
            const rowClass = stage === 'C' ? 'opacity-70' : '';

            return `
                <tr class="hover:bg-gray-50 ${rowClass}">
                    <td class="px-4 py-2">
                        <input type="checkbox"
                            class="slGrnRowChk rounded border-gray-300"
                            value="${escapeHtml(key)}"
                            data-stage="${escapeHtml(stage)}"
                            ${disabled}>
                    </td>
                    <td class="px-4 py-2">${escapeHtml(cpny)}</td>
                    <td class="px-4 py-2">${escapeHtml(grnNo)}</td>

                    <td class="px-4 py-2">${escapeHtml(fmtDate(r.grn_date ?? r.receipt_date ?? ''))}</td>
                    <td class="px-4 py-2">${escapeHtml(r.po_no ?? r.ponbr ?? '')}</td>
                    <td class="px-4 py-2">${escapeHtml(r.supplier_cd ?? r.vendor_name ?? r.vendor_id ?? '')}</td>
                    <td class="px-4 py-2">${escapeHtml(fmtDate(r.created_at ?? r.crtd_datetime ?? ''))}</td>
                    <td class="px-4 py-2 text-center">${badge(stage)}</td>
                </tr>
            `;
        }).join('');

        el.tbody.querySelectorAll('.slGrnRowChk').forEach(chk => {
            chk.addEventListener('change', syncChkAllState);
        });

        syncChkAllState();
    }

    async function safeJson(resp) {
        const ct = resp.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
            const text = await resp.text();
            console.error('Non JSON response:', text);
            throw new Error('Response bukan JSON. Kemungkinan redirect/login/error HTML.');
        }
        return resp.json();
    }

    async function loadData() {
        hideInfo();

        if (!el.from.value || !el.to.value) {
            setInfo('warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        setBusy(true, 'Loading GRN Solomon...', 'Sedang mengambil data GRN Solomon.');

        el.tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-4 py-10 text-center text-gray-500">Loading...</td>
            </tr>
        `;
        el.chkAll.disabled = true;
        el.chkAll.checked = false;
        el.chkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.grnsolomon.list') }}", window.location.origin);
        url.searchParams.set('from', el.from.value);
        url.searchParams.set('to', el.to.value);

        try {
            const resp = await fetch(url.toString(), {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            const json = await safeJson(resp);

            if (!resp.ok || !json.ok) {
                renderRows([]);
                setInfo('err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = Array.isArray(json.data) ? json.data : [];
            renderRows(rows);

            const ready = rows.filter(x => String(x.stage_status ?? '').toUpperCase() === 'P').length;
            const waiting = rows.filter(x => String(x.stage_status ?? '').toUpperCase() === 'D').length;
            const done = rows.filter(x => String(x.stage_status ?? '').toUpperCase() === 'C').length;

            setInfo('ok', `Loaded ${rows.length} GRN. Ready(P): ${ready}, Waiting(D): ${waiting}, Completed(C): ${done}.`);
        } catch (err) {
            console.error(err);
            renderRows([]);
            setInfo('err', err?.message ?? 'Error saat load.');
        } finally {
            setBusy(false);
        }
    }

    async function processData() {
        if (isBusy) return;

        hideInfo();

        const ids = Array.from(el.tbody.querySelectorAll('.slGrnRowChk:checked'))
            .filter(chk => String(chk.dataset.stage ?? '').toUpperCase() === 'P')
            .map(chk => chk.value)
            .filter(v => v && v !== 'undefined' && !v.endsWith('||'));

        if (ids.length === 0) {
            setInfo('warn', 'Pilih minimal 1 GRN status P untuk diproses. Status D/C tidak bisa.');
            return;
        }

        setBusy(true, 'Processing GRN Solomon...', 'Sedang insert ke Solomon (P → C).');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.grnsolomon.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ ids })
            });

            const json = await safeJson(resp);

            if (!resp.ok || !json.ok) {
                const fail0 = (json.failed && json.failed.length) ? json.failed[0] : null;
                const detail = fail0
                    ? ` (${fail0.cpny_id || ''}||${fail0.grn_no || fail0.receiptnbr || ''}: ${fail0.error || ''})`
                    : '';

                setInfo('err', (json.message ?? 'Gagal process.') + detail);
                return;
            }

            setInfo('ok', `Process done. Sent OK(P->C): ${json.sent_success_P_to_C ?? 0}, Failed: ${json.sent_failed ?? 0}`);
        } catch (err) {
            console.error(err);
            setInfo('err', err?.message ?? 'Error saat process.');
        } finally {
            setBusy(false);
        }

        await loadData();
    }

    el.chkAll.addEventListener('change', () => {
        if (isBusy) return;
        const enabled = Array.from(el.tbody.querySelectorAll('.slGrnRowChk')).filter(chk => !chk.disabled);
        enabled.forEach(chk => chk.checked = el.chkAll.checked);
        syncChkAllState();
    });

    el.btnLoad.addEventListener('click', async () => {
        if (isBusy) return;
        await loadData();
    });

    el.btnProcess.addEventListener('click', async () => {
        await processData();
    });

    (() => {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');

        if (!el.to.value) el.to.value = `${yyyy}-${mm}-${dd}`;
        if (!el.from.value) el.from.value = `${yyyy}-${mm}-01`;
    })();
})();
</script>