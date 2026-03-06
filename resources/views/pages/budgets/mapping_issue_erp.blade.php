<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'mapping_issue_erp.index' ? 'Integration' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-lg font-extrabold text-gray-800 dark:text-white">Mapping Issue ERP</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-300">Klik icon 🔍 untuk review & update mapping issue</p>
                </div>

                <button id="btnReload"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 active:scale-95">
                    Reload
                </button>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-500">Filter Status</label>
                    <select id="filterStatus"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">All</option>
                        <option value="D">Waiting Review</option>
                        <option value="P">Review</option>
                        <option value="C">Completed</option>
                    </select>
                </div>

                <div class="md:col-span-6">
                    <label class="mb-1 block text-xs font-semibold text-gray-500">Search</label>
                    <input id="searchInput"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        placeholder="cpny_id / issue_id / reference_no / spb_id / wo_id / user_peminta...">
                </div>

                <div class="flex items-end md:col-span-3">
                    <button id="btnSearch"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 active:scale-95 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        Apply
                    </button>
                </div>
            </div>

            <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                <th class="w-14 px-4 py-3"></th>
                                <th class="px-4 py-3">Cpny</th>
                                <th class="px-4 py-3">Issue ID</th>
                                <th class="px-4 py-3">Issue Date</th>
                                <th class="px-4 py-3">Reference No</th>
                                <th class="px-4 py-3">SPB ID</th>
                                <th class="px-4 py-3">WO ID</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tbody" class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Detail & Mapping Issue</h2>
                    <p id="modalSub" class="text-sm font-semibold text-gray-700 dark:text-gray-200"></p>
                </div>
                <button id="btnCloseModal"
                    class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">✕</button>
            </div>

            <div class="space-y-4 p-5">
                <input type="hidden" id="rowId">

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Company</div><div>:</div>
                        <input id="mCpny" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Issue ID</div><div>:</div>
                        <input id="mIssueId" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Issue Date</div><div>:</div>
                        <input id="mIssueDate" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Reference No</div><div>:</div>
                        <input id="mReferenceNo" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">SPB ID</div><div>:</div>
                        <input id="mSpbId" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">WO ID</div><div>:</div>
                        <input id="mWoId" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Department</div><div>:</div>
                        <input id="mDepartment" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Peminta</div><div>:</div>
                        <input id="mPeminta" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm md:col-span-2">
                        <div class="text-gray-500">Keeper</div><div>:</div>
                        <input id="mKeeper" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-start gap-2 text-sm md:col-span-2">
                        <div class="pt-1 text-gray-500">Remarks</div><div class="pt-1">:</div>
                        <textarea id="mRemark" rows="2" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm md:col-span-2">
                        <div class="text-gray-500">Integration Type</div><div>:</div>
                        <select id="mIntegrationType"
                            class="w-full rounded-md border border-gray-300 bg-white px-2 py-1 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">-- Select Integration Type --</option>
                        </select>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="border-b border-gray-200 px-4 py-2 text-sm font-bold text-gray-700 dark:border-gray-700 dark:text-gray-200">
                        Detail Items (Mapping per Line)
                    </div>

                    <div class="max-h-80 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                            <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900">
                                <tr class="text-left font-bold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    <th class="px-3 py-2 w-12">No</th>
                                    <th class="px-3 py-2 min-w-[280px]">Item Info</th>
                                    <th class="px-3 py-2 w-14">Qty/Uom</th>
                                    <th class="px-3 py-2 min-w-[300px]">IFCA(entity_cd,ic_location,trx_cd,div_cd,dept_cd)</th>
                                    <th class="px-3 py-2 min-w-[320px]">SOLOMON(reason_cd,acct_cd,allocation_cd,subaccount_dept)</th>
                                </tr>
                            </thead>
                            <tbody id="detailTbody" class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-gray-500">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500">Status</label>
                        <select id="mStatus"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="D">Waiting Review</option>
                            <option value="P">Review</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-gray-500">Review Note</label>
                        <input id="mNote"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="Catatan review...">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-5 py-4 dark:border-gray-700">
                <button id="btnCancel"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 active:scale-95 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    Close
                </button>
                <button id="btnSave"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 active:scale-95">
                    Save Update
                </button>
            </div>
        </div>
    </div>

<script>
(function() {
    const tbody = document.getElementById('tbody');
    const modal = document.getElementById('editModal');
    const detailTbody = document.getElementById('detailTbody');
    const btnSave = document.getElementById('btnSave');

    const URL_JSON = @json(route('mapping_issue_erp.json'));
    const BASE     = @json(url('/mapping-issue-erp'));
    const URL_INTEGRATION_TYPES = @json(route('mapping_issue_erp.integration-types'));

    function statusBadge(st) {
        st = (st || '').toUpperCase();
        const map = {
            D: { text: 'Waiting Review', cls: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
            P: { text: 'Review', cls: 'bg-amber-50 text-amber-700 border-amber-200' },
            C: { text: 'Completed', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
        };
        const m = map[st] || { text: (st || '-'), cls: 'bg-gray-50 text-gray-700 border-gray-200' };
        return `<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold ${m.cls}">${m.text}</span>`;
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function magnifierBtn(id) {
        return `
            <button type="button" class="js-open inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-700"
                data-id="${id}" title="View / Edit">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 text-gray-700 dark:text-gray-200">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                </svg>
            </button>
        `;
    }

    function escapeHtml(str) {
        return (str ?? '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setInputEditable(el, editable) {
        if (!el) return;
        el.readOnly = !editable;

        if (editable) {
            el.classList.remove('bg-gray-200', 'text-gray-500', 'cursor-not-allowed', 'opacity-70');
            el.classList.add('bg-white');
        } else {
            el.classList.remove('bg-white');
            el.classList.add('bg-gray-200', 'text-gray-500', 'cursor-not-allowed', 'opacity-70');
        }
    }

    function setSelectEditable(el, editable) {
        if (!el) return;
        el.disabled = !editable;

        if (editable) {
            el.classList.remove('bg-gray-200', 'text-gray-500', 'cursor-not-allowed', 'opacity-70');
            el.classList.add('bg-white');
        } else {
            el.classList.remove('bg-white');
            el.classList.add('bg-gray-200', 'text-gray-500', 'cursor-not-allowed', 'opacity-70');
        }
    }

    function applyIntegrationTypeMode(type, forceReadonly = false) {
        const selected = (type || '').toUpperCase();

        const ifcaFields = detailTbody.querySelectorAll('input[data-group="ifca"]');
        const solomonFields = detailTbody.querySelectorAll('input[data-group="solomon"]');
        const integrationSelect = document.getElementById('mIntegrationType');
        const statusSelect = document.getElementById('mStatus');
        const noteInput = document.getElementById('mNote');

        if (forceReadonly) {
            ifcaFields.forEach(inp => setInputEditable(inp, false));
            solomonFields.forEach(inp => setInputEditable(inp, false));
            setSelectEditable(integrationSelect, false);
            setSelectEditable(statusSelect, false);
            setInputEditable(noteInput, false);
            return;
        }

        setSelectEditable(integrationSelect, true);
        setSelectEditable(statusSelect, true);
        setInputEditable(noteInput, true);

        if (selected === 'IFCA') {
            ifcaFields.forEach(inp => setInputEditable(inp, true));
            solomonFields.forEach(inp => setInputEditable(inp, false));
        } else if (selected === 'SOLOMON') {
            ifcaFields.forEach(inp => setInputEditable(inp, false));
            solomonFields.forEach(inp => setInputEditable(inp, true));
        } else {
            ifcaFields.forEach(inp => setInputEditable(inp, false));
            solomonFields.forEach(inp => setInputEditable(inp, false));
        }
    }

    function formatQty(val) {
        if (val === null || val === undefined || val === '') return '';
        const num = parseFloat(val);
        if (isNaN(num)) return String(val);
        return num.toFixed(2);
    }

    async function loadIntegrationTypes(selectedValue = '') {
        const select = document.getElementById('mIntegrationType');
        if (!select) return;

        select.innerHTML = `<option value="">Loading...</option>`;

        try {
            const res = await fetch(URL_INTEGRATION_TYPES, {
                headers: { 'Accept': 'application/json' }
            });

            const json = await res.json();
            const rows = json.data || [];

            let html = `<option value="">-- Select Integration Type --</option>`;
            html += rows.map(x => {
                const raw = (typeof x === 'object') ? (x.integration_type ?? '') : x;
                const val = (raw ?? '').toString().toUpperCase();
                const selected = val === (selectedValue || '').toUpperCase() ? 'selected' : '';
                return `<option value="${escapeHtml(val)}" ${selected}>${escapeHtml(val)}</option>`;
            }).join('');

            select.innerHTML = html;
        } catch (err) {
            select.innerHTML = `<option value="">-- Failed load option --</option>`;
        }
    }

    async function loadTable() {
        const status = document.getElementById('filterStatus').value;
        const search = document.getElementById('searchInput').value.trim();

        tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Loading...</td></tr>`;

        const qs = new URLSearchParams();
        if (status) qs.set('status', status);
        if (search) qs.set('search', search);

        const res = await fetch(URL_JSON + '?' + qs.toString(), {
            headers: { 'Accept': 'application/json' }
        });

        const json = await res.json();
        const rows = json.data || [];

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No data</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-3">${magnifierBtn(r.id)}</td>
                <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">${escapeHtml(r.cpny_id ?? '')}</td>
                <td class="px-4 py-3">${escapeHtml(r.issue_id ?? '')}</td>
                <td class="px-4 py-3">${escapeHtml(r.issue_date ?? '')}</td>
                <td class="px-4 py-3">${escapeHtml(r.reference_no ?? '')}</td>
                <td class="px-4 py-3">${escapeHtml(r.spb_id ?? '')}</td>
                <td class="px-4 py-3">${escapeHtml(r.wo_id ?? '')}</td>
                <td class="px-4 py-3">${statusBadge(r.status)}</td>
            </tr>
        `).join('');
    }

    function inputCell(name, value, rid, widthCls, group) {
        const v = escapeHtml(value ?? '');
        const w = widthCls || 'w-full';
        const g = group || '';

        return `<input
            data-rid="${rid}"
            data-name="${name}"
            data-group="${g}"
            value="${v}"
            class="${w} rounded-md border border-gray-300 bg-white px-2 py-1 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white" />`;
    }

    async function openRow(id) {
        detailTbody.innerHTML = `<tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">Loading...</td></tr>`;

        const res = await fetch(`${BASE}/${id}`, {
            headers: { 'Accept': 'application/json' }
        });

        const json = await res.json();
        if (!json.success) return;

        const header = json.data.header || {};
        const details = json.data.details || [];

        document.getElementById('rowId').value = id;
        document.getElementById('mCpny').value = header.cpny_id ?? '';
        document.getElementById('mIssueId').value = header.issue_id ?? '';
        document.getElementById('mIssueDate').value = header.issue_date ?? '';
        document.getElementById('mReferenceNo').value = header.reference_no ?? '';
        document.getElementById('mSpbId').value = header.spb_id ?? '';
        document.getElementById('mWoId').value = header.wo_id ?? '';
        document.getElementById('mDepartment').value = header.department_id ?? '';
        document.getElementById('mPeminta').value = header.user_peminta ?? '';
        document.getElementById('mKeeper').value = header.keeper ?? '';
        document.getElementById('mRemark').value = header.issuehd_descs ?? '';

        const status = (header.status ?? 'D').toUpperCase();
        document.getElementById('mStatus').value = status;
        document.getElementById('mNote').value = header.reviewed_note ?? '';

        if (status === 'C') {
            btnSave.classList.add('hidden');
        } else {
            btnSave.classList.remove('hidden');
        }

        document.getElementById('modalSub').textContent =
            `Reference: ${(header.reference_no ?? '-')}`;

        if (!details.length) {
            detailTbody.innerHTML = `<tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">No detail</td></tr>`;
        } else {
            detailTbody.innerHTML = details.map(d => {
                const rid = d.id;
                const item = d.item_cd ?? '';
                const descr = d.item_remark ?? '';

                return `
                    <tr>
                        <td class="px-3 py-2 align-top">${escapeHtml(d.line_no ?? '')}</td>

                        <td class="px-3 py-2 align-top">
                            <div class="font-semibold text-gray-800 dark:text-white">${escapeHtml(item)}</div>
                            <div class="mt-0.5 text-gray-600 dark:text-gray-300 leading-snug">${escapeHtml(descr)}</div>
                        </td>

                        <td class="px-3 py-2 align-top">
                            <div class="text-right font-semibold">${formatQty(d.issue_qty)}</div>
                            <div class="text-right text-gray-500">${escapeHtml(d.uom ?? '')}</div>
                        </td>

                        <td class="px-3 py-2 align-top">
                            <div class="grid grid-cols-2 gap-1">
                                ${inputCell('entity_cd', d.entity_cd, rid, 'w-full', 'ifca')}
                                ${inputCell('ic_location', d.ic_location, rid, 'w-full', 'ifca')}
                                <div class="col-span-2">${inputCell('trx_cd', d.trx_cd, rid, 'w-full', 'ifca')}</div>
                                ${inputCell('div_cd', d.div_cd, rid, 'w-full', 'ifca')}
                                ${inputCell('dept_cd', d.dept_cd, rid, 'w-full', 'ifca')}
                            </div>
                        </td>

                        <td class="px-3 py-2 align-top">
                            <div class="grid grid-cols-1 gap-1">
                                ${inputCell('solomon_reason_cd', d.solomon_reason_cd, rid, 'w-full', 'solomon')}
                                ${inputCell('solomon_acct_cd', d.solomon_acct_cd, rid, 'w-full', 'solomon')}
                                ${inputCell('solomon_allocation_cd', d.solomon_allocation_cd, rid, 'w-full', 'solomon')}
                                ${inputCell('solomon_subaccount_dept', d.solomon_subaccount_dept, rid, 'w-full', 'solomon')}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        const integrationType = (header.integration_type ?? '').toUpperCase();
        await loadIntegrationTypes(integrationType);

        const integrationSelect = document.getElementById('mIntegrationType');
        if (integrationSelect) {
            integrationSelect.value = integrationType;
        }

        applyIntegrationTypeMode(integrationType, status === 'C');

        openModal();
    }

    function collectLinesPayload() {
        const inputs = detailTbody.querySelectorAll('input[data-rid][data-name]');
        const map = {};

        inputs.forEach(inp => {
            const rid = inp.getAttribute('data-rid');
            const name = inp.getAttribute('data-name');

            if (!map[rid]) {
                map[rid] = { id: parseInt(rid, 10) };
            }

            map[rid][name] = inp.value.trim();
        });

        return Object.values(map);
    }

    async function saveUpdate() {
        const id = document.getElementById('rowId').value;
        if (!id) return;

        const integrationSelect = document.getElementById('mIntegrationType');

        const payload = {
            status: document.getElementById('mStatus').value,
            reviewed_note: document.getElementById('mNote').value.trim(),
            integration_type: integrationSelect ? integrationSelect.value : '',
            lines: collectLinesPayload()
        };

        const res = await fetch(`${BASE}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });

        const json = await res.json();

        if (res.ok && json.success) {
            closeModal();
            await loadTable();
        } else {
            alert(json.message || 'Gagal update');
        }
    }

    document.getElementById('btnReload').addEventListener('click', loadTable);
    document.getElementById('btnSearch').addEventListener('click', loadTable);
    document.getElementById('filterStatus').addEventListener('change', loadTable);

    document.getElementById('searchInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') loadTable();
    });

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('button.js-open');
        if (!btn) return;
        openRow(btn.dataset.id);
    });

    const integrationSelect = document.getElementById('mIntegrationType');
    if (integrationSelect) {
        integrationSelect.addEventListener('change', function() {
            const currentStatus = (document.getElementById('mStatus').value || '').toUpperCase();
            applyIntegrationTypeMode(this.value, currentStatus === 'C');
        });
    }

    document.getElementById('btnSave').addEventListener('click', saveUpdate);
    document.getElementById('btnCloseModal').addEventListener('click', closeModal);
    document.getElementById('btnCancel').addEventListener('click', closeModal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    loadTable();
})();
</script>
</x-app-layout>