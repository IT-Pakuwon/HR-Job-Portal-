<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'mapping_po_erp.index' ? 'Integration' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-lg font-extrabold text-gray-800 dark:text-white">Mapping PO ERP</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-300">Klik icon 🔍 untuk review & update mapping</p>
                </div>

                <button id="btnReload"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 active:scale-95">
                    Reload
                </button>
            </div>

            {{-- Filters --}}
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
                        placeholder="cpny_id / order_no / vendor / remark / ref_no...">
                </div>

                <div class="md:col-span-3 flex items-end">
                    <button id="btnSearch"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 active:scale-95 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        Apply
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                <th class="px-4 py-3 w-14"></th>
                                <th class="px-4 py-3">Cpny</th>
                                <th class="px-4 py-3">Order No</th>
                                <th class="px-4 py-3">Order Date</th>
                                <th class="px-4 py-3">Vendor</th>
                                <th class="px-4 py-3">Ref SPBJKT</th>
                                <th class="px-4 py-3">Ref CS</th>
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

    {{-- ===== Modal (Tailwind) ===== --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Detail & Mapping</h2>
                    <p id="modalSub" class="text-base font-extrabold text-gray-800 dark:text-white"></p>
                </div>
                <button id="btnCloseModal"
                    class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">✕</button>
            </div>

            <div class="p-5 space-y-4">
                <input type="hidden" id="rowId">

                {{-- HEADER (seperti gambar) --}}
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Company</div><div>:</div>
                        <input id="mCpny" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Sppjkt</div><div>:</div>
                        <input id="mSppjkt" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Order Nbr</div><div>:</div>
                        <input id="mOrderNo" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">CS</div><div>:</div>
                        <input id="mCs" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Order Date</div><div>:</div>
                        <input id="mOrderDate" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Ordertype</div><div>:</div>
                        <input id="mOrderType" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="md:col-span-2 grid grid-cols-[120px_12px_1fr] items-start gap-2 text-sm">
                        <div class="text-gray-500 pt-1">Remaks</div><div class="pt-1">:</div>
                        <textarea id="mRemark" rows="2" readonly
                            class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Item</div><div>:</div>
                        <input id="item_cd" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Description</div><div>:</div>
                        <input id="item_remark" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Uom</div><div>:</div>
                        <input id="uom" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Qty</div><div>:</div>
                        <input id="order_qty" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                        <div class="text-gray-500">Cost</div><div>:</div>
                        <input id="item_cost" readonly class="w-full rounded-md border border-gray-300 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>                  
                </div>

                {{-- BAGIAN BAWAH YANG BISA DIEDIT (mapping) --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-2 text-sm font-bold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">
                        Mapping (Editable)
                    </div>

                    <div class="p-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- IFCA --}}
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">IFCA</div>

                            <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                                <div class="text-gray-500">entity_cd</div><div>:</div>
                                <input id="e_entity_cd" class="w-full rounded-md border border-gray-300 bg-white px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                                <div class="text-gray-500">location_cd</div><div>:</div>
                                <input id="e_location_cd" class="w-full rounded-md border border-gray-300 bg-white px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                                <div class="text-gray-500">acct_cd</div><div>:</div>
                                <input id="e_acct_cd" class="w-full rounded-md border border-gray-300 bg-white px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                                <div class="text-gray-500">div_cd</div><div>:</div>
                                <input id="e_div_cd" class="w-full rounded-md border border-gray-300 bg-white px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div class="grid grid-cols-[120px_12px_1fr] items-center gap-2 text-sm">
                                <div class="text-gray-500">dept_cd</div><div>:</div>
                                <input id="e_dept_cd" class="w-full rounded-md border border-gray-300 bg-white px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                        </div>

                        {{-- SOLOMON --}}
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">SOLOMON</div>

                            <div class="grid grid-cols-[170px_12px_1fr] items-center gap-2 text-sm">
                                <div class="text-gray-500">solomon_acct_cd</div><div>:</div>
                                <input id="e_solomon_acct_cd" class="w-full rounded-md border border-gray-300 bg-white px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div class="grid grid-cols-[170px_12px_1fr] items-center gap-2 text-sm">
                                <div class="text-gray-500">solomon_allocation_cd</div><div>:</div>
                                <input id="e_solomon_allocation_cd" class="w-full rounded-md border border-gray-300 bg-white px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div class="grid grid-cols-[170px_12px_1fr] items-center gap-2 text-sm">
                                <div class="text-gray-500">solomon_subaccount_dept</div><div>:</div>
                                <input id="e_solomon_subaccount_dept" class="w-full rounded-md border border-gray-300 bg-white px-2 py-1 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STATUS + NOTE --}}
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500">Status</label>
                        <select id="mStatus"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="D">Waiting Review</option>
                            <option value="P">Review</option>
                            {{-- <option value="C">Completed</option> --}}
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-gray-500">Process Note</label>
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
        (function () {
            const tbody = document.getElementById('tbody');
            const modal = document.getElementById('editModal');

            const URL_JSON = @json(route('mapping_po_erp.json'));
            const BASE     = @json(url('/mapping-po-erp'));

            function statusBadge(st){
                st = (st || '').toUpperCase();
                const map = {
                    D: { text:'Waiting Review', cls:'bg-indigo-50 text-indigo-700 border-indigo-200' },
                    P: { text:'Review',        cls:'bg-amber-50 text-amber-700 border-amber-200' },
                    C: { text:'Completed',     cls:'bg-emerald-50 text-emerald-700 border-emerald-200' },
                };
                const m = map[st] || { text: (st || '-'), cls:'bg-gray-50 text-gray-700 border-gray-200' };
                return `<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold ${m.cls}">${m.text}</span>`;
            }

            function openModal(){
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
            function closeModal(){
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function magnifierBtn(id){
                return `
                    <button type="button" class="js-open inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-700"
                        data-id="${id}" title="View / Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 text-gray-700 dark:text-gray-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                    </button>
                `;
            }

            async function loadTable(){
                const status = document.getElementById('filterStatus').value;
                const search = document.getElementById('searchInput').value.trim();

                tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Loading...</td></tr>`;

                const qs = new URLSearchParams();
                if (status) qs.set('status', status);
                if (search) qs.set('search', search);

                const res = await fetch(URL_JSON + '?' + qs.toString(), { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                const rows = json.data || [];

                if (!rows.length) {
                    tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No data</td></tr>`;
                    return;
                }

                tbody.innerHTML = rows.map(r => {
                    const vendor = r.vendor_name ? r.vendor_name : (r.supplier_cd ?? '');
                    return `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3">${magnifierBtn(r.id)}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">${r.cpny_id ?? ''}</td>
                            <td class="px-4 py-3">${r.order_no ?? ''}</td>
                            <td class="px-4 py-3">${r.order_date ?? ''}</td>
                            <td class="px-4 py-3">${vendor}</td>
                            <td class="px-4 py-3">${r.ref_no_spbjkt ?? ''}</td>
                            <td class="px-4 py-3">${r.ref_no_cs ?? ''}</td>
                            <td class="px-4 py-3">${statusBadge(r.status)}</td>
                        </tr>
                    `;
                }).join('');
            }

            async function openRow(id){
                const res = await fetch(`${BASE}/${id}`, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                if (!json.success) return;

                const r = json.data;

                document.getElementById('rowId').value = r.id;

                // header like image
                document.getElementById('mCpny').value      = r.cpny_id ?? '';
                document.getElementById('mSppjkt').value    = r.ref_no_spbjkt ?? '';
                document.getElementById('mOrderNo').value   = r.order_no ?? '';
                document.getElementById('mCs').value        = r.ref_no_cs ?? '';
                document.getElementById('mOrderDate').value = r.order_date ?? '';
                document.getElementById('mOrderType').value = r.order_type ?? '';
                document.getElementById('mRemark').value    = r.remark ?? '';

                document.getElementById('item_cd').value      = r.item_cd ?? '';
                document.getElementById('item_remark').value    = r.item_remark ?? '';
                document.getElementById('uom').value   = r.uom ?? '';
                document.getElementById('order_qty').value   = r.order_qty ?? '';
                document.getElementById('item_cost').value   = r.item_cost ?? '';

                // editable mapping
                document.getElementById('e_entity_cd').value = r.entity_cd ?? '';
                document.getElementById('e_location_cd').value = r.location_cd ?? '';
                document.getElementById('e_acct_cd').value = r.acct_cd ?? '';
                document.getElementById('e_div_cd').value = r.div_cd ?? '';
                document.getElementById('e_dept_cd').value = r.dept_cd ?? '';

                document.getElementById('e_solomon_acct_cd').value = r.solomon_acct_cd ?? '';
                document.getElementById('e_solomon_allocation_cd').value = r.solomon_allocation_cd ?? '';
                document.getElementById('e_solomon_subaccount_dept').value = r.solomon_subaccount_dept ?? '';

                // status + note
                document.getElementById('mStatus').value = (r.status ?? 'D').toUpperCase();
                document.getElementById('mNote').value   = r.process_note ?? '';

                document.getElementById('modalSub').textContent =
                    `Vendor: ${(r.vendor_name ?? r.supplier_cd ?? '-')}`;

                openModal();
            }

            async function saveUpdate(){
                const id = document.getElementById('rowId').value;
                if (!id) return;

                const payload = {
                    status: document.getElementById('mStatus').value,
                    process_note: document.getElementById('mNote').value.trim(),

                    entity_cd: document.getElementById('e_entity_cd').value.trim(),
                    location_cd: document.getElementById('e_location_cd').value.trim(),
                    acct_cd: document.getElementById('e_acct_cd').value.trim(),
                    div_cd: document.getElementById('e_div_cd').value.trim(),
                    dept_cd: document.getElementById('e_dept_cd').value.trim(),

                    solomon_acct_cd: document.getElementById('e_solomon_acct_cd').value.trim(),
                    solomon_allocation_cd: document.getElementById('e_solomon_allocation_cd').value.trim(),
                    solomon_subaccount_dept: document.getElementById('e_solomon_subaccount_dept').value.trim(),
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

            // events
            document.getElementById('btnReload').addEventListener('click', loadTable);
            document.getElementById('btnSearch').addEventListener('click', loadTable);
            document.getElementById('filterStatus').addEventListener('change', loadTable);

            document.getElementById('searchInput').addEventListener('keydown', (e) => {
                if (e.key === 'Enter') loadTable();
            });

            // click icon 🔍 (bukan click row)
            tbody.addEventListener('click', (e) => {
                const btn = e.target.closest('button.js-open');
                if (!btn) return;
                openRow(btn.dataset.id);
            });

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
