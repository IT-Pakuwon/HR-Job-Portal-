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
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>

            <div>
                <div id="slIssueBusyTitle" class="font-semibold text-gray-800">Processing...</div>
                <div id="slIssueBusySub" class="text-sm text-gray-600">Mohon tunggu, jangan klik menu/tab.</div>
            </div>
        </div>
    </div>
</div>

<div class="rounded-xl border border-gray-200 bg-white p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-lg">📦</span>
                <h2 class="text-lg font-semibold text-gray-900">ISSUE Solomon (P-Solomon → C)</h2>
            </div>
            <p class="text-sm text-gray-600 mt-1">Load data staging berdasarkan range tanggal, lalu proses P → C</p>
        </div>
    </div>

    <div class="mt-5 rounded-xl border border-gray-200 bg-gray-50 p-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input id="slIssueFrom" type="date"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-[38px]">
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input id="slIssueTo" type="date"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-[38px]">
            </div>

            <div class="md:col-span-6 flex gap-2 justify-end">
                <button id="btnLoadSlIssue"
                        class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Load
                </button>
                <button id="btnProcessSlIssue"
                        class="inline-flex items-center justify-center rounded-md bg-green-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                    Process
                </button>
            </div>
        </div>

        <div id="slIssueInfo" class="hidden mt-3 rounded-md border px-4 py-3 text-sm"></div>
    </div>

    <div class="mt-5 rounded-xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 bg-white">
            <div class="text-sm text-gray-700">
                Total: <span id="slIssueTotal" class="font-semibold">0</span>
            </div>
            {{-- seperti IFCA API Issue: gak ada Cpny filter & gak ada limit label --}}
            <div class="text-xs text-gray-500"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-4 py-2 w-10">
                        <input id="slIssueChkAll" type="checkbox" class="rounded border-gray-300">
                    </th>
                    <th class="px-4 py-2 text-left">Cpny</th>
                    <th class="px-4 py-2 text-left">Issue ID</th>
                    <th class="px-4 py-2 text-left">Issue Date</th>
                    <th class="px-4 py-2 text-left">Dept</th>
                    <th class="px-4 py-2 text-left">Peminta</th>
                    <th class="px-4 py-2 text-left">WOID</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-left">Created</th>
                    <th class="px-4 py-2 text-center">Status</th>
                </tr>
                </thead>

                <tbody id="slIssueTbody" class="divide-y divide-gray-100">
                <tr>
                    <td colspan="10" class="px-4 py-10 text-center text-gray-500">Belum ada data. Klik Load.</td>
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
    const csrfSlIssue = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const slIssueBusyOverlay = document.getElementById('slIssueBusyOverlay');
    const slIssueBusyTitle   = document.getElementById('slIssueBusyTitle');
    const slIssueBusySub     = document.getElementById('slIssueBusySub');

    const slIssueFrom    = document.getElementById('slIssueFrom');
    const slIssueTo      = document.getElementById('slIssueTo');
    const btnLoadSlIssue = document.getElementById('btnLoadSlIssue');
    const btnProcessSlIssue = document.getElementById('btnProcessSlIssue');

    const slIssueInfo  = document.getElementById('slIssueInfo');
    const slIssueTotal = document.getElementById('slIssueTotal');
    const slIssueChkAll = document.getElementById('slIssueChkAll');
    const slIssueTbody  = document.getElementById('slIssueTbody');

    let slIssueBusy = false;

    function hideInfo(){
        slIssueInfo.classList.add('hidden');
        slIssueInfo.textContent='';
        slIssueInfo.className='hidden mt-3 rounded-md border px-4 py-3 text-sm';
    }
    function setInfo(type, msg){
        slIssueInfo.classList.remove('hidden');
        slIssueInfo.textContent = msg;
        slIssueInfo.className = 'mt-3 rounded-md border px-4 py-3 text-sm';
        if (type === 'ok') slIssueInfo.classList.add('bg-green-50','border-green-200','text-green-800');
        else if (type === 'warn') slIssueInfo.classList.add('bg-yellow-50','border-yellow-200','text-yellow-800');
        else slIssueInfo.classList.add('bg-red-50','border-red-200','text-red-800');
    }

    function setBusy(isBusy, title='Processing...', sub='Mohon tunggu, jangan klik menu/tab.'){
        slIssueBusy = isBusy;

        if (isBusy) {
            slIssueBusyTitle.textContent = title;
            slIssueBusySub.textContent = sub;
            slIssueBusyOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            slIssueBusyOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        slIssueFrom.disabled = isBusy;
        slIssueTo.disabled = isBusy;
        btnLoadSlIssue.disabled = isBusy;
        btnProcessSlIssue.disabled = isBusy;
        slIssueChkAll.disabled = isBusy;

        slIssueTbody.querySelectorAll('.slIssueRowChk').forEach(chk => {
            if (isBusy) {
                chk.disabled = true;
                return;
            }
            const stage = String(chk.dataset.stage ?? '').toUpperCase();
            chk.disabled = (stage !== 'P');     // hanya P yang bisa dicentang
            if (chk.disabled) chk.checked = false;
        });

        syncChkAllState();

        if (isBusy) {
            btnLoadSlIssue.dataset._txt = btnLoadSlIssue.textContent;
            btnProcessSlIssue.dataset._txt = btnProcessSlIssue.textContent;
            btnLoadSlIssue.textContent = 'Loading...';
            btnProcessSlIssue.textContent = 'Processing...';
        } else {
            if (btnLoadSlIssue.dataset._txt) btnLoadSlIssue.textContent = btnLoadSlIssue.dataset._txt;
            if (btnProcessSlIssue.dataset._txt) btnProcessSlIssue.textContent = btnProcessSlIssue.dataset._txt;
        }
    }

    function syncChkAllState() {
        const enabled = Array.from(slIssueTbody.querySelectorAll('.slIssueRowChk')).filter(chk => !chk.disabled);
        if (enabled.length === 0) {
            slIssueChkAll.checked = false;
            slIssueChkAll.indeterminate = false;
            slIssueChkAll.disabled = true;
            return;
        }
        slIssueChkAll.disabled = slIssueBusy ? true : false;

        const checked = enabled.filter(chk => chk.checked).length;
        slIssueChkAll.checked = (checked === enabled.length);
        slIssueChkAll.indeterminate = (checked > 0 && checked < enabled.length);
    }

    slIssueChkAll.addEventListener('change', () => {
        if (slIssueBusy) return;
        const enabled = Array.from(slIssueTbody.querySelectorAll('.slIssueRowChk')).filter(chk => !chk.disabled);
        enabled.forEach(chk => chk.checked = slIssueChkAll.checked);
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

    function badge(stage){
        stage = String(stage ?? '').toUpperCase();
        if (stage === 'C') return `<span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">C</span>`;
        if (stage === 'D') return `<span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700">D</span>`;
        return `<span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">P</span>`;
    }

    function renderRows(rows){
        slIssueTotal.textContent = String(rows?.length || 0);

        if (!rows || rows.length === 0) {
            slIssueTbody.innerHTML = `<tr><td colspan="10" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
            slIssueChkAll.checked = false;
            slIssueChkAll.indeterminate = false;
            slIssueChkAll.disabled = true;
            return;
        }

        slIssueTbody.innerHTML = rows.map((r) => {
            const stage = String(r.stage_status ?? 'P').toUpperCase();
            const cpny  = (r.cpny_id ?? '').toString();
            const issue = (r.issue_id ?? '').toString();
            const key = `${cpny}||${issue}`; // jangan sampai undefined

            const disabled = (stage !== 'P') ? 'disabled' : '';
            const rowClass = (stage === 'C') ? 'opacity-70' : '';

            return `
                <tr class="hover:bg-gray-50 ${rowClass}">
                    <td class="px-4 py-2">
                        <input type="checkbox"
                               class="slIssueRowChk rounded border-gray-300"
                               value="${key}"
                               data-stage="${stage}"
                               ${disabled}>
                    </td>
                    <td class="px-4 py-2">${cpny}</td>
                    <td class="px-4 py-2">${issue}</td>
                    <td class="px-4 py-2">${fmtDate(r.issue_date ?? '')}</td>
                    <td class="px-4 py-2">${r.deptid ?? ''}</td>
                    <td class="px-4 py-2">${r.peminta ?? ''}</td>
                    <td class="px-4 py-2">${r.woid ?? ''}</td>
                    <td class="px-4 py-2 text-right">${r.total_record ?? ''}</td>
                    <td class="px-4 py-2">${fmtDate(r.crtd_datetime ?? '')}</td>
                    <td class="px-4 py-2 text-center">${badge(stage)}</td>
                </tr>
            `;
        }).join('');

        slIssueTbody.querySelectorAll('.slIssueRowChk').forEach(chk => {
            chk.addEventListener('change', syncChkAllState);
        });

        syncChkAllState();
    }

    async function safeJson(resp) {
        const ct = resp.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
            const text = await resp.text();
            // kasus paling sering: redirect ke login / error HTML
            throw new Error('Response bukan JSON. Kemungkinan session expired / redirect. (lihat Network response HTML)');
        }
        return await resp.json();
    }

    async function loadSlIssue(){
        hideInfo();

        if (!slIssueFrom.value || !slIssueTo.value) {
            setInfo('warn', 'Start Date & End Date wajib diisi.');
            return;
        }

        setBusy(true, 'Loading Issue Solomon...', 'Sedang mengambil data Issue Solomon.');

        slIssueTbody.innerHTML = `<tr><td colspan="10" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>`;
        slIssueChkAll.disabled = true;
        slIssueChkAll.checked = false;
        slIssueChkAll.indeterminate = false;

        const url = new URL("{{ route('integration.ifcaintegration.issuesolomon.list') }}", window.location.origin);
        url.searchParams.set('from', slIssueFrom.value);
        url.searchParams.set('to', slIssueTo.value);

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

            setInfo('ok', `Loaded ${rows.length} Issue. Ready(P): ${ready}, Waiting(D): ${waiting}, Completed(C): ${done}.`);
        } catch (e) {
            renderRows([]);
            setInfo('err', e?.message ?? 'Error saat load.');
        } finally {
            setBusy(false);
        }
    }

    // default date: start = awal bulan, end = hari ini (seperti IFCA API Issue)
    (function initDefaultDates(){
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        slIssueTo.value = `${yyyy}-${mm}-${dd}`;
        slIssueFrom.value = `${yyyy}-${mm}-01`;
    })();

    btnLoadSlIssue.addEventListener('click', async () => {
        if (slIssueBusy) return;
        await loadSlIssue();
    });

    btnProcessSlIssue.addEventListener('click', async () => {
        if (slIssueBusy) return;

        hideInfo();

        // hanya P yang boleh diproses
        const ids = Array.from(slIssueTbody.querySelectorAll('.slIssueRowChk:checked'))
            .filter(chk => String(chk.dataset.stage ?? '').toUpperCase() === 'P')
            .map(chk => chk.value)
            .filter(v => v && v !== 'undefined' && !v.endsWith('||'));

        if (ids.length === 0) {
            setInfo('warn', 'Pilih minimal 1 Issue status P untuk diproses. Status D/C tidak bisa.');
            return;
        }

        setBusy(true, 'Processing Issue Solomon...', 'Sedang insert ke Solomon (P → C).');

        try {
            const resp = await fetch("{{ route('integration.ifcaintegration.issuesolomon.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfSlIssue
                },
                body: JSON.stringify({ ids })
            });

            const json = await safeJson(resp);

            if (!resp.ok || !json.ok) {
                // kalau controller kirim detail failed, tampilkan 1 baris ringkas
                const fail0 = (json.failed && json.failed.length) ? json.failed[0] : null;
                const detail = fail0 ? ` (${fail0.cpny_id || ''}||${fail0.issue_id || ''}: ${fail0.error || ''})` : '';
                setInfo('err', (json.message ?? 'Gagal process.') + detail);
                return;
            }

            setInfo('ok', `Process done. Sent OK(P->C): ${json.sent_success_P_to_C ?? 0}, Failed: ${json.sent_failed ?? 0}`);
        } catch (e) {
            setInfo('err', e?.message ?? 'Error saat process.');
        } finally {
            setBusy(false);
        }

        // reload supaya row yang sudah C tetap muncul tapi tidak bisa dicentang
        await loadSlIssue();
    });

    // optional: auto-load pertama kali seperti IFCA (kalau kamu mau)
    // loadSlIssue();
</script>