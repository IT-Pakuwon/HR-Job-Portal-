<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- =========================
     FULLSCREEN BUSY OVERLAY (ikut IFCA)
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
            <div class="text-sm text-gray-700">
                Processing... please wait
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-gray-100">
        <div class="flex items-center gap-2">
            <div class="text-lg font-semibold text-gray-800">📦 ISSUE Solomon (P-Solomon → C)</div>
        </div>
        <div class="text-xs text-gray-500 mt-1">Load data staging berdasarkan range tanggal, lalu proses P → C</div>

        <div class="mt-4 grid grid-cols-12 gap-3 items-end">
            <div class="col-span-12 sm:col-span-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Start Date</label>
                <input id="slIssueStartDate" type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="dd/mm/yyyy">
            </div>

            <div class="col-span-12 sm:col-span-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
                <input id="slIssueEndDate" type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="dd/mm/yyyy">
            </div>

            <div class="col-span-12 sm:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cpny ID</label>
                <input id="slIssueCpnyId" type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="ex: PKW">
            </div>

            <div class="col-span-12 sm:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Limit</label>
                <input id="slIssueLimit" type="number" min="1" max="500"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       value="100">
            </div>

            <div class="col-span-12 sm:col-span-2 flex gap-2 justify-end">
                <button id="slIssueBtnLoad"
                        class="w-full sm:w-auto px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                    Load
                </button>
                <button id="slIssueBtnProcess"
                        class="w-full sm:w-auto px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">
                    Process
                </button>
            </div>
        </div>

        <div id="slIssueAlert" class="hidden mt-4 px-4 py-3 rounded-lg text-sm"></div>
    </div>

    <div class="p-5">
        <div class="flex items-center justify-between mb-2">
            <div class="text-sm text-gray-600">
                Total: <span id="slIssueTotal" class="font-semibold text-gray-800">0</span>
            </div>
            <div class="text-xs text-gray-400">Limit 100 rows per load</div>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-xl">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-3 py-2 w-10 text-center">
                        <input id="slIssueCheckAll" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </th>
                    <th class="px-3 py-2 text-left">Cpny</th>
                    <th class="px-3 py-2 text-left">Issue ID</th>
                    <th class="px-3 py-2 text-left">Issue Date</th>
                    <th class="px-3 py-2 text-left">Dept</th>
                    <th class="px-3 py-2 text-left">Peminta</th>
                    <th class="px-3 py-2 text-left">WOID</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2 text-left">Created</th>
                    <th class="px-3 py-2 text-center">Status</th>
                </tr>
                </thead>

                <tbody id="slIssueTbody" class="divide-y divide-gray-100 bg-white">
                <tr>
                    <td colspan="10" class="px-4 py-10 text-center text-gray-400">
                        Klik Load untuk mengambil data.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3 text-xs text-gray-500">
            Legend: P = ready (P-SOLOMON), D = waiting review, C = completed.
        </div>
    </div>
</div>

<script>
    // =========================
    // Helpers (ikut IFCA)
    // =========================
    const slIssueEls = {
        start: document.getElementById('slIssueStartDate'),
        end: document.getElementById('slIssueEndDate'),
        cpny: document.getElementById('slIssueCpnyId'),
        limit: document.getElementById('slIssueLimit'),
        btnLoad: document.getElementById('slIssueBtnLoad'),
        btnProcess: document.getElementById('slIssueBtnProcess'),
        tbody: document.getElementById('slIssueTbody'),
        total: document.getElementById('slIssueTotal'),
        alert: document.getElementById('slIssueAlert'),
        overlay: document.getElementById('slIssueBusyOverlay'),
        checkAll: document.getElementById('slIssueCheckAll'),
    };

    function slIssueShowOverlay(show) {
        if (!slIssueEls.overlay) return;
        slIssueEls.overlay.classList.toggle('hidden', !show);
    }

    function slIssueSetAlert(type, msg) {
        const el = slIssueEls.alert;
        if (!el) return;

        el.classList.remove('hidden', 'bg-green-50', 'border', 'border-green-200', 'text-green-700',
            'bg-red-50', 'border-red-200', 'text-red-700',
            'bg-yellow-50', 'border-yellow-200', 'text-yellow-700');

        if (type === 'success') el.classList.add('bg-green-50','border','border-green-200','text-green-700');
        else if (type === 'error') el.classList.add('bg-red-50','border','border-red-200','text-red-700');
        else el.classList.add('bg-yellow-50','border','border-yellow-200','text-yellow-700');

        el.textContent = msg;
    }

    function slIssueClearAlert() {
        slIssueEls.alert.classList.add('hidden');
        slIssueEls.alert.textContent = '';
    }

    function slIssueFormatDateDMY(d) {
        const dd = String(d.getDate()).padStart(2,'0');
        const mm = String(d.getMonth()+1).padStart(2,'0');
        const yyyy = d.getFullYear();
        return `${dd}/${mm}/${yyyy}`;
    }

    // default tanggal: awal bulan & hari ini (ikut IFCA)
    function slIssueSetDefaultDates() {
        const now = new Date();
        const first = new Date(now.getFullYear(), now.getMonth(), 1);
        if (slIssueEls.start && !slIssueEls.start.value) slIssueEls.start.value = slIssueFormatDateDMY(first);
        if (slIssueEls.end && !slIssueEls.end.value) slIssueEls.end.value = slIssueFormatDateDMY(now);
    }

    // checkbox header behavior (ikut IFCA)
    function slIssueSyncHeaderCheckbox() {
        const items = document.querySelectorAll('.slIssueRowCk');
        const checked = document.querySelectorAll('.slIssueRowCk:checked');
        if (!slIssueEls.checkAll) return;

        if (items.length === 0) {
            slIssueEls.checkAll.checked = false;
            slIssueEls.checkAll.indeterminate = false;
            return;
        }

        slIssueEls.checkAll.checked = (checked.length === items.length);
        slIssueEls.checkAll.indeterminate = (checked.length > 0 && checked.length < items.length);
    }

    // ✅ FIX: jangan sampai ids berisi "undefined"
    function slIssueGetSelectedIds() {
        const ids = Array.from(document.querySelectorAll('.slIssueRowCk:checked'))
            .map(el => {
                const v = (el.value || '').trim();
                const dk = (el.dataset.key || '').trim();
                return v || dk;
            })
            .filter(v => v && v !== 'undefined');

        // unique
        return Array.from(new Set(ids));
    }

    function slIssueBadge(stage) {
        if (stage === 'C') return `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">C</span>`;
        if (stage === 'D') return `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">D</span>`;
        return `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">P</span>`;
    }

    async function slIssueLoad() {
        slIssueClearAlert();

        const start = (slIssueEls.start?.value || '').trim();
        const end = (slIssueEls.end?.value || '').trim();
        const cpny = (slIssueEls.cpny?.value || '').trim();
        const limit = (slIssueEls.limit?.value || '100').trim();

        if (!start || !end) {
            slIssueSetAlert('warn', 'Start Date dan End Date wajib diisi.');
            return;
        }

        slIssueShowOverlay(true);
        slIssueEls.btnLoad.disabled = true;
        slIssueEls.btnProcess.disabled = true;

        try {
            const url = new URL("{{ route('integration.ifcaintegration.issuesolomon.list') }}", window.location.origin);
            url.searchParams.set('start_date', start);
            url.searchParams.set('end_date', end);
            url.searchParams.set('limit', limit);
            if (cpny) url.searchParams.set('cpny_id', cpny);

            const res = await fetch(url.toString(), {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            const json = await res.json();
            if (!res.ok || !json.ok) {
                throw new Error(json.message || 'Gagal load data.');
            }

            const rows = json.data || [];
            slIssueEls.total.textContent = rows.length;

            if (rows.length === 0) {
                slIssueEls.tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="px-4 py-10 text-center text-gray-400">No data.</td>
                    </tr>
                `;
                slIssueEls.checkAll.checked = false;
                slIssueEls.checkAll.indeterminate = false;
                slIssueSetAlert('success', 'Loaded 0 Issue.');
                return;
            }

            slIssueEls.tbody.innerHTML = rows.map((r, idx) => {
                const disabled = (r.stage_status === 'D' || r.stage_status === 'C') ? 'disabled' : '';
                const rowCls = (r.stage_status === 'D' || r.stage_status === 'C') ? 'opacity-60' : '';

                // ✅ FIX: fallback key kalau r.key kosong
                const key = (r.key ?? `${r.cpny_id ?? ''}||${r.issue_id ?? ''}`).trim();

                return `
                    <tr class="${rowCls}">
                        <td class="px-3 py-2 text-center">
                            <input type="checkbox"
                                   class="slIssueRowCk rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   value="${key}"
                                   data-key="${key}"
                                   ${disabled}>
                        </td>
                        <td class="px-3 py-2">${r.cpny_id ?? ''}</td>
                        <td class="px-3 py-2 font-medium text-gray-800">${r.issue_id ?? ''}</td>
                        <td class="px-3 py-2">${r.issue_date ?? ''}</td>
                        <td class="px-3 py-2">${r.deptid ?? ''}</td>
                        <td class="px-3 py-2">${r.peminta ?? ''}</td>
                        <td class="px-3 py-2">${r.woid ?? ''}</td>
                        <td class="px-3 py-2 text-right">${r.total_record ?? 0}</td>
                        <td class="px-3 py-2">${r.crtd_datetime ?? ''}</td>
                        <td class="px-3 py-2 text-center">${slIssueBadge(r.stage_status)}</td>
                    </tr>
                `;
            }).join('');

            document.querySelectorAll('.slIssueRowCk').forEach(el => {
                el.addEventListener('change', slIssueSyncHeaderCheckbox);
            });

            slIssueSyncHeaderCheckbox();

            const cntP = rows.filter(x => x.stage_status === 'P').length;
            const cntD = rows.filter(x => x.stage_status === 'D').length;
            const cntC = rows.filter(x => x.stage_status === 'C').length;

            slIssueSetAlert('success', `Loaded ${rows.length} Issue. Ready(P): ${cntP}, Waiting(D): ${cntD}, Completed(C): ${cntC}.`);
        } catch (e) {
            slIssueSetAlert('error', e.message || 'Error load data.');
        } finally {
            slIssueShowOverlay(false);
            slIssueEls.btnLoad.disabled = false;
            slIssueEls.btnProcess.disabled = false;
        }
    }

    async function slIssueProcess() {
        slIssueClearAlert();

        const ids = slIssueGetSelectedIds();
        if (!ids.length) {
            slIssueSetAlert('warn', 'Pilih minimal 1 data berstatus P untuk diproses.');
            return;
        }

        slIssueShowOverlay(true);
        slIssueEls.btnLoad.disabled = true;
        slIssueEls.btnProcess.disabled = true;

        try {
            const res = await fetch("{{ route('integration.ifcaintegration.issuesolomon.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ids })
            });

            const json = await res.json();
            if (!res.ok || !json.ok) {
                const msg = json.message || 'Process gagal.';
                throw new Error(msg);
            }

            slIssueSetAlert('success', `Process OK. Success: ${json.sent_success_P_to_C ?? 0}, Failed: ${json.sent_failed ?? 0}`);
            await slIssueLoad();
        } catch (e) {
            slIssueSetAlert('error', e.message || 'Error process.');
        } finally {
            slIssueShowOverlay(false);
            slIssueEls.btnLoad.disabled = false;
            slIssueEls.btnProcess.disabled = false;
        }
    }

    // =========================
    // Init (ikut IFCA)
    // =========================
    document.addEventListener('DOMContentLoaded', function () {
        slIssueSetDefaultDates();

        slIssueEls.btnLoad?.addEventListener('click', (e) => {
            e.preventDefault();
            slIssueLoad();
        });

        slIssueEls.btnProcess?.addEventListener('click', (e) => {
            e.preventDefault();
            slIssueProcess();
        });

        slIssueEls.checkAll?.addEventListener('change', function () {
            const checked = this.checked;
            document.querySelectorAll('.slIssueRowCk:not(:disabled)').forEach(el => {
                el.checked = checked;
            });
            slIssueSyncHeaderCheckbox();
        });
    });
</script>