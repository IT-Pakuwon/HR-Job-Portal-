<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="slGrnBusyOverlay" class="hidden fixed inset-0 z-[9999] pointer-events-auto">
    <div class="absolute inset-0 bg-black/40 pointer-events-auto"></div>

    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div class="pointer-events-auto rounded-xl bg-white px-5 py-4 shadow-lg border border-gray-200 flex items-center gap-3">
            <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>

            <div>
                <div id="slGrnBusyTitle" class="font-semibold text-gray-800">Processing...</div>
                <div id="slGrnBusySub" class="text-sm text-gray-600">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-lg">📥</span>
                <h2 class="text-lg font-semibold text-gray-900">GRN Solomon (P-Solomon → C)</h2>
            </div>
            <p class="text-sm text-gray-600 mt-1">Load data staging berdasarkan range tanggal, lalu proses P → C</p>
        </div>
    </div>

    <div class="mt-5 rounded-xl border border-gray-200 bg-gray-50 p-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input id="slGrnFrom" type="date"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-[38px]">
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input id="slGrnTo" type="date"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-[38px]">
            </div>

            <div class="md:col-span-6 flex gap-2 justify-end">
                <button id="btnLoadSlGrn"
                        class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Load
                </button>
                <button id="btnProcessSlGrn"
                        class="inline-flex items-center justify-center rounded-md bg-green-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                    Process
                </button>
            </div>
        </div>

        <div id="slGrnInfo" class="hidden mt-3 rounded-md border px-4 py-3 text-sm"></div>
    </div>

    <div class="mt-5 rounded-xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 bg-white">
            <div class="text-sm text-gray-700">
                Total: <span id="slGrnTotal" class="font-semibold">0</span>
            </div>
            <div class="text-xs text-gray-500"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-4 py-2 w-10">
                        <input id="slGrnChkAll" type="checkbox" class="rounded border-gray-300">
                    </th>
                    <th class="px-4 py-2 text-left">Cpny</th>
                    <th class="px-4 py-2 text-left">Receipt No</th>
                    <th class="px-4 py-2 text-left">Receipt Date</th>
                    <th class="px-4 py-2 text-left">PO No</th>
                    <th class="px-4 py-2 text-left">Vendor ID</th>
                    <th class="px-4 py-2 text-left">Vendor Name</th>
                    <th class="px-4 py-2 text-left">Requestor</th>
                    <th class="px-4 py-2 text-right">Qty</th>
                    <th class="px-4 py-2 text-right">Amount</th>
                    <th class="px-4 py-2 text-center">Lines</th>
                    <th class="px-4 py-2 text-left">Created</th>
                    <th class="px-4 py-2 text-center">Status</th>
                </tr>
                </thead>

                <tbody id="slGrnTbody" class="divide-y divide-gray-100">
                <tr>
                    <td colspan="13" class="px-4 py-10 text-center text-gray-500">Belum ada data. Klik Load.</td>
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
    const csrfSlGrn = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const slGrnBusyOverlay = document.getElementById('slGrnBusyOverlay');
    const slGrnBusyTitle   = document.getElementById('slGrnBusyTitle');
    const slGrnBusySub     = document.getElementById('slGrnBusySub');

    const slGrnFrom    = document.getElementById('slGrnFrom');
    const slGrnTo      = document.getElementById('slGrnTo');
    const btnLoadSlGrn = document.getElementById('btnLoadSlGrn');
    const btnProcessSlGrn = document.getElementById('btnProcessSlGrn');

    const slGrnInfo   = document.getElementById('slGrnInfo');
    const slGrnTotal  = document.getElementById('slGrnTotal');
    const slGrnChkAll = document.getElementById('slGrnChkAll');
    const slGrnTbody  = document.getElementById('slGrnTbody');

    let slGrnBusy = false;

    function hideInfo(){
        slGrnInfo.classList.add('hidden');
        slGrnInfo.textContent='';
        slGrnInfo.className='hidden mt-3 rounded-md border px-4 py-3 text-sm';
    }

    function setInfo(type, msg){
        slGrnInfo.classList.remove('hidden');
        slGrnInfo.textContent = msg;
        slGrnInfo.className = 'mt-3 rounded-md border px-4 py-3 text-sm';
        if (type === 'ok') slGrnInfo.classList.add('bg-green-50','border-green-200','text-green-800');
        else if (type === 'warn') slGrnInfo.classList.add('bg-yellow-50','border-yellow-200','text-yellow-800');
        else slGrnInfo.classList.add('bg-red-50','border-red-200','text-red-800');
    }

    function setBusy(isBusy, title='Processing...', sub='Mohon tunggu, jangan klik menu/tab.'){
        slGrnBusy = isBusy;

        if (isBusy) {
            slGrnBusyTitle.textContent = title;
            slGrnBusySub.textContent = sub;
            slGrnBusyOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            slGrnBusyOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        slGrnFrom.disabled = isBusy;
        slGrnTo.disabled = isBusy;
        btnLoadSlGrn.disabled = isBusy;
        btnProcessSlGrn.disabled = isBusy;
        slGrnChkAll.disabled = isBusy;

        slGrnTbody.querySelectorAll('.slGrnRowChk').forEach(chk => {
            if (isBusy) {
                chk.disabled = true;
                return;
            }
            const stage = String(chk.dataset.stage ?? '').toUpperCase();
            chk.disabled = (stage !== 'P');
            if (chk.disabled) chk.checked = false;
        });

        syncChkAllState();

        if (isBusy) {
            btnLoadSlGrn.dataset._txt = btnLoadSlGrn.textContent;
            btnProcessSlGrn.dataset._txt = btnProcessSlGrn.textContent;
            btnLoadSlGrn.textContent = 'Loading...';
            btnProcessSlGrn.textContent = 'Processing...';
        } else {
            if (btnLoadSlGrn.dataset._txt) btnLoadSlGrn.textContent = btnLoadSlGrn.dataset._txt;
            if (btnProcessSlGrn.dataset._txt) btnProcessSlGrn.textContent = btnProcessSlGrn.dataset._txt;
        }
    }

    function syncChkAllState() {
        const enabled = Array.from(slGrnTbody.querySelectorAll('.slGrnRowChk')).filter(chk => !chk.disabled);
        if (enabled.length === 0) {
            slGrnChkAll.checked = false;
            slGrnChkAll.indeterminate = false;
            slGrnChkAll.disabled = true;
            return;
        }
        slGrnChkAll.disabled = slGrnBusy ? true : false;

        const checked = enabled.filter(chk => chk.checked).length;
        slGrnChkAll.checked = (checked === enabled.length);
        slGrnChkAll.indeterminate = (checked > 0 && checked < enabled.length);
    }

    slGrnChkAll.addEventListener('change', () => {
        if (slGrnBusy) return;
        const enabled = Array.from(slGrnTbody.querySelectorAll('.slGrnRowChk')).filter(chk => !chk.disabled);
        enabled.forEach(chk => chk.checked = slGrnChkAll.checked);
        syncChkAllState();
    });

    function fmtDate(x){
        if (!x) return '';
        try {
            const d = new Date(x);
            if (isNaN(d.getTime())) return String(x);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth()+1).padStart(2,'0');
            const dd = String(d.getDate()).padStart(2,'0');
            const hh = String(d.getHours()).padStart(2,'0');
            const mi = String(d.getMinutes()).padStart(2,'0');
            const ss = String(d.getSeconds()).padStart(2,'0');
            return `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;
        } catch { return String(x); }
    }

    function fmtNum(x){
        const n = Number(x ?? 0);
        if (Number.isNaN(n)) return x ?? '';
        return n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function badge(stage){
        stage = String(stage ?? '').toUpperCase();
        if (stage === 'C') return `<span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">C</span>`;
        if (stage === 'D') return `<span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700">D</span>`;
        return `<span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">P</span>`;
    }

    function renderRows(rows){
        slGrnTotal.textContent = String(rows?.length || 0);

        if (!rows || rows.length === 0) {
            slGrnTbody.innerHTML = `<tr><td colspan="13" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            slGrnChkAll.checked = false;
            slGrnChkAll.indeterminate = false;
            slGrnChkAll.disabled = true;
            return;
        }

        slGrnTbody.innerHTML = rows.map((r) => {
            const stage = String(r.stage_status ?? 'P').toUpperCase();
            const cpny  = (r.cpny_id ?? '').toString();
            const rcpt  = (r.receipt_no ?? '').toString();
            const key = `${cpny}||${rcpt}`;

            const disabled = (stage !== 'P') ? 'disabled' : '';
            const rowClass = (stage === 'C') ? 'opacity-70' : '';

            return `
                <tr class="hover:bg-gray-50 ${rowClass}">
                    <td class="px-4 py-2">
                        <input type="checkbox"
                               class="slGrnRowChk rounded border-gray-300"
                               value="${key}"
                               data-stage="${stage}"
                               ${disabled}>
                    </td>
                    <td class="px-4 py-2">${cpny}</td>
                    <td class="px-4 py-2">${rcpt}</td>
                    <td class="px-4 py-2">${fmtDate(r.receipt_date ?? '')}</td>
                    <td class="px-4 py-2">${r.po_no ?? ''}</td>
                    <td class="px-4 py-2">${r.vendor_id ?? ''}</td>
                    <td class="px-4 py-2">${r.vendor_name ?? ''}</td>
                    <td class="px-4 py-2">${r.requestor ?? ''}</td>
                    <td class="px-4 py-2 text-right">${fmtNum(r.total_qty ?? '')}</td>
                    <td class="px-4 py-2 text-right">${fmtNum(r.total_amount ?? '')}</td>
                    <td class="px-4 py-2 text-center">${r.total_record ?? ''}</td>
                    <td class="px-4 py-2">${fmtDate(r.crtd_datetime ?? '')}</td>
                    <td class="px-4 py-2 text-center">${badge(stage)}</td>
                </tr>
            `;
        }).join('');

        slGrnTbody.querySelectorAll('.slGrnRowChk').forEach(chk => {
            chk.addEventListener('change', syncChkAllState);
        });

        syncChkAllState();
    }

    async function safeJson(resp) {
        const ct = resp.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
            const text = await resp.text();
            throw new Error('Response bukan JSON. Kemungkinan session expired / redirect. (lihat Network response HTML)');
        }
        return await resp.json();
    }

    async function loadSlGrn(){
        hideInfo();

        if (!slGrnFrom.value || !slGrnTo.value) {
            setInfo('warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        setBusy(true, 'Loading GRN Solomon...', 'Sedang mengambil data GRN Solomon.');

        slGrnTbody.innerHTML = `<tr><td colspan="13" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        slGrnChkAll.disabled = true;
        slGrnChkAll.checked = false;
        slGrnChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.grnsolomon.list') }}", window.location.origin);
        url.searchParams.set('from', slGrnFrom.value);
        url.searchParams.set('to', slGrnTo.value);

        try {
            const resp = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const json = await safeJson(resp);

            if (!resp.ok || !json.ok) {
                renderRows([]);
                setInfo('err', json.message ?? 'Gagal load data.');
                return;
            }

            const rows = json.data || [];
            renderRows(rows);

            const ready = rows.filter(x => (String(x.stage_status ?? '').toUpperCase() === 'P')).length;
            const waiting = rows.filter(x => (String(x.stage_status ?? '').toUpperCase() === 'D')).length;
            const done = rows.filter(x => (String(x.stage_status ?? '').toUpperCase() === 'C')).length;

            setInfo('ok', `Loaded ${rows.length} GRN. Ready(P): ${ready}, Waiting(D): ${waiting}, Completed(C): ${done}.`);
        } catch (e) {
            renderRows([]);
            setInfo('err', e?.message ?? 'Error saat load.');
        } finally {
            setBusy(false);
        }
    }

    (function initDefaultDates(){
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        slGrnTo.value = `${yyyy}-${mm}-${dd}`;
        slGrnFrom.value = `${yyyy}-${mm}-01`;
    })();

    btnLoadSlGrn.addEventListener('click', async () => {
        if (slGrnBusy) return;
        await loadSlGrn();
    });

    btnProcessSlGrn.addEventListener('click', async () => {
        if (slGrnBusy) return;

        hideInfo();

        const ids = Array.from(slGrnTbody.querySelectorAll('.slGrnRowChk:checked'))
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
                    'X-CSRF-TOKEN': csrfSlGrn
                },
                body: JSON.stringify({ ids })
            });

            const json = await safeJson(resp);

            if (!resp.ok || !json.ok) {
                const fail0 = (json.failed && json.failed.length) ? json.failed[0] : null;
                const detail = fail0 ? ` (${fail0.cpny_id || ''}||${fail0.receipt_no || ''}: ${fail0.error || ''})` : '';
                setInfo('err', (json.message ?? 'Gagal process.') + detail);
                return;
            }

            setInfo('ok', `Process done. Sent OK(P->C): ${json.sent_success_P_to_C ?? 0}, Failed: ${json.sent_failed ?? 0}`);
        } catch (e) {
            setInfo('err', e?.message ?? 'Error saat process.');
        } finally {
            setBusy(false);
        }

        await loadSlGrn();
    });

    // loadSlGrn();
</script>