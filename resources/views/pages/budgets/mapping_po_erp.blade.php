<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'mapping_po_erp.index' ? 'Integration' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-lg font-extrabold text-gray-800 dark:text-white">Mapping PO ERP</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-300">Klik row untuk review & update status</p>
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
                        placeholder="order_no / supplier / remark / ref_no...">
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
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Order No</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Order Date</th>
                                <th class="px-4 py-3">Supplier</th>
                                <th class="px-4 py-3">Remark</th>
                                <th class="px-4 py-3">Ref CS</th>
                                <th class="px-4 py-3">Ref SPBJKT</th>
                                <th class="px-4 py-3 text-right">Total Record</th>
                                <th class="px-4 py-3 text-right">Line</th>
                            </tr>
                        </thead>
                        <tbody id="tbody" class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- ===== Modal (Tailwind) ===== --}}
    <div id="editModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Review / Update</h2>
                    <p id="modalSub" class="text-xs text-gray-500 dark:text-gray-300"></p>
                </div>
                <button id="btnCloseModal"
                    class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                    ✕
                </button>
            </div>

            <div class="p-5">
                <input type="hidden" id="rowId">

                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500">Order No</label>
                        <input id="mOrderNo" readonly
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500">Supplier</label>
                        <input id="mSupplier" readonly
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500">Order Date</label>
                        <input id="mOrderDate" readonly
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500">Current Status</label>
                        <input id="mStatusLabel" readonly
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500">Update Status</label>
                        <select id="mStatus"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="D">Waiting Review</option>
                            <option value="P">Review</option>
                            <option value="C">Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500">Process Flag</label>
                        <input id="mProcessFlag" readonly
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-500">Remark</label>
                    <textarea id="mRemark" rows="2" readonly
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                </div>

                <div class="mt-3">
                    <label class="mb-1 block text-xs font-semibold text-gray-500">Process Note (update)</label>
                    <textarea id="mNote" rows="3" placeholder="Catatan review..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
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

            // routes (pakai name route baru)
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

            async function loadTable(){
                const status = document.getElementById('filterStatus').value;
                const search = document.getElementById('searchInput').value.trim();

                tbody.innerHTML = `<tr><td colspan="10" class="px-4 py-8 text-center text-gray-500">Loading...</td></tr>`;

                const qs = new URLSearchParams();
                if (status) qs.set('status', status);
                if (search) qs.set('search', search);

                const res = await fetch(URL_JSON + '?' + qs.toString(), {
                    headers: { 'Accept': 'application/json' }
                });

                const json = await res.json();
                const rows = json.data || [];

                if (!rows.length) {
                    tbody.innerHTML = `<tr><td colspan="10" class="px-4 py-8 text-center text-gray-500">No data</td></tr>`;
                    return;
                }

                tbody.innerHTML = rows.map(r => `
                    <tr data-id="${r.id}" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-3">${statusBadge(r.status)}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">${r.order_no ?? ''}</td>
                        <td class="px-4 py-3">${r.order_type ?? ''}</td>
                        <td class="px-4 py-3">${r.order_date ?? ''}</td>
                        <td class="px-4 py-3">${r.supplier_cd ?? ''}</td>
                        <td class="px-4 py-3">${String(r.remark ?? '').slice(0,120)}</td>
                        <td class="px-4 py-3">${r.ref_no_cs ?? ''}</td>
                        <td class="px-4 py-3">${r.ref_no_spbjkt ?? ''}</td>
                        <td class="px-4 py-3 text-right">${r.total_record ?? ''}</td>
                        <td class="px-4 py-3 text-right">${r.order_line ?? ''}</td>
                    </tr>
                `).join('');
            }

            async function openRow(id){
                const res = await fetch(`${BASE}/${id}`, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                if (!json.success) return;

                const r = json.data;

                document.getElementById('rowId').value = r.id;
                document.getElementById('mOrderNo').value = r.order_no ?? '';
                document.getElementById('mSupplier').value = r.supplier_cd ?? '';
                document.getElementById('mOrderDate').value = r.order_date ?? '';
                document.getElementById('mRemark').value = r.remark ?? '';
                document.getElementById('mProcessFlag').value = r.process_flag ?? '';

                document.getElementById('mStatus').value = (r.status ?? 'D').toUpperCase();
                document.getElementById('mStatusLabel').value = r.status_label ?? (r.status ?? '');

                document.getElementById('mNote').value = r.process_note ?? '';
                document.getElementById('modalSub').textContent =
                    `Entity: ${r.entity_cd ?? '-'} | Cpny: ${r.cpny_id ?? '-'} | Type: ${r.order_type ?? '-'}`;

                openModal();
            }

            async function saveUpdate(){
                const id = document.getElementById('rowId').value;
                if (!id) return;

                const payload = {
                    status: document.getElementById('mStatus').value,
                    process_note: document.getElementById('mNote').value.trim()
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

            tbody.addEventListener('click', (e) => {
                const tr = e.target.closest('tr[data-id]');
                if (!tr) return;
                openRow(tr.dataset.id);
            });

            document.getElementById('btnSave').addEventListener('click', saveUpdate);
            document.getElementById('btnCloseModal').addEventListener('click', closeModal);
            document.getElementById('btnCancel').addEventListener('click', closeModal);

            // close modal if click outside card
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });

            loadTable();
        })();
    </script>
</x-app-layout>
