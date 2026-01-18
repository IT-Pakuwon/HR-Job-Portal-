<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="bg-white border border-gray-200 rounded-xl">
            <div class="px-5 py-4 border-b border-gray-200">
                <h1 class="text-xl font-semibold text-gray-800">IFCA Integration</h1>
                <p class="text-sm text-gray-500">Scheduler & Integration Master Data</p>
            </div>

            {{-- Tabs --}}
            <div class="px-5 pt-4">
                <div class="inline-flex gap-2 rounded-lg bg-gray-50 border border-gray-200 p-1">
                    <button type="button" data-tab="tab-nonstock"
                        class="tab-btn px-4 py-2 text-sm font-medium rounded-md bg-white border border-gray-200 shadow-sm">
                        Non Stock
                    </button>
                    <button type="button" data-tab="tab-stock"
                        class="tab-btn px-4 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-white hover:border-gray-200">
                        Stock (soon)
                    </button>
                    <button type="button" data-tab="tab-supplier"
                        class="tab-btn px-4 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-white hover:border-gray-200">
                        Supplier (soon)
                    </button>
                    <button type="button" data-tab="tab-po"
                        class="tab-btn px-4 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-white hover:border-gray-200">
                        PO (soon)
                    </button>
                    <button type="button" data-tab="tab-sttb"
                        class="tab-btn px-4 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-white hover:border-gray-200">
                        STTB (soon)
                    </button>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-5">
                {{-- Default empty state --}}
                <div id="emptyState" class="text-sm text-gray-500">
                    Klik tab untuk menampilkan data.
                </div>

                {{-- TAB: Non Stock --}}
                <div id="tab-nonstock" class="hidden">
                    {{-- Header filter --}}
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full md:max-w-3xl">
                            <div>
                                <label class="text-xs font-medium text-gray-600">Start Date</label>
                                <input type="date" id="ns_from"
                                    class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600">End Date</label>
                                <input type="date" id="ns_to"
                                    class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="flex gap-2">
                                <button type="button" id="btnLoadNonStock"
                                    class="mt-6 w-full px-4 py-2 rounded-lg bg-white border border-gray-300 text-sm font-medium hover:bg-gray-50">
                                    Load
                                </button>
                                <button type="button" id="btnProcessNonStock"
                                    class="mt-6 w-full px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                                    Process
                                </button>
                            </div>
                        </div>

                    </div>

                    {{-- Info bar --}}
                    <div id="nsInfo" class="hidden mb-3 rounded-lg border px-4 py-3 text-sm"></div>

                    {{-- Table --}}
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Total: <span class="font-semibold" id="nsTotal">0</span>
                            </div>
                            <div class="text-xs text-gray-500">Limit 100 rows per load</div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-white">
                                    <tr class="text-left text-gray-600 border-b border-gray-200">
                                        <th class="px-3 py-2 w-10">
                                            <input type="checkbox" id="nsChkAll" class="rounded border-gray-300">
                                        </th>
                                        <th class="px-3 py-2 w-56">Inventory ID</th>
                                        <th class="px-3 py-2">Description</th>
                                        <th class="px-3 py-2 w-28">UOM</th>
                                        <th class="px-3 py-2 w-20">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="nsTbody" class="divide-y divide-gray-100">
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center text-gray-500">
                                            Belum ada data. Klik Load.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Placeholder tabs --}}
                <div id="tab-stock" class="hidden text-sm text-gray-500">Stock tab (soon)</div>
                <div id="tab-supplier" class="hidden text-sm text-gray-500">Supplier tab (soon)</div>
                <div id="tab-po" class="hidden text-sm text-gray-500">PO tab (soon)</div>
                <div id="tab-sttb" class="hidden text-sm text-gray-500">STTB tab (soon)</div>
            </div>
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function setInfo(el, type, msg) {
            el.classList.remove('hidden', 'border-green-200','bg-green-50','text-green-800','border-red-200','bg-red-50','text-red-800','border-yellow-200','bg-yellow-50','text-yellow-800');
            if (type === 'ok')    el.classList.add('border-green-200','bg-green-50','text-green-800');
            if (type === 'err')   el.classList.add('border-red-200','bg-red-50','text-red-800');
            if (type === 'warn')  el.classList.add('border-yellow-200','bg-yellow-50','text-yellow-800');
            el.textContent = msg;
        }

        function hideInfo(el){ el.classList.add('hidden'); el.textContent=''; }

        // Tabs: default kosong, load hanya saat klik tab
        const emptyState = document.getElementById('emptyState');
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabPanels = ['tab-nonstock','tab-stock','tab-supplier','tab-po','tab-sttb'].map(id => document.getElementById(id));

        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                // active style
                tabButtons.forEach(b => b.classList.remove('bg-white','border','border-gray-200','shadow-sm'));
                btn.classList.add('bg-white','border','border-gray-200','shadow-sm');

                // show tab
                tabPanels.forEach(p => p.classList.add('hidden'));
                const panel = document.getElementById(btn.dataset.tab);
                panel.classList.remove('hidden');
                emptyState.classList.add('hidden');
            });
        });

        // Nonstock logic
        const nsFrom = document.getElementById('ns_from');
        const nsTo   = document.getElementById('ns_to');
        const nsTbody = document.getElementById('nsTbody');
        const nsTotal = document.getElementById('nsTotal');
        const nsInfo  = document.getElementById('nsInfo');
        const nsChkAll = document.getElementById('nsChkAll');

        // default tanggal (biar user tidak repot)
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth()+1).padStart(2,'0');
        const dd = String(today.getDate()).padStart(2,'0');
        nsTo.value = `${yyyy}-${mm}-${dd}`;
        nsFrom.value = `${yyyy}-${mm}-01`;

        nsChkAll.addEventListener('change', () => {
            nsTbody.querySelectorAll('.nsRowChk').forEach(chk => chk.checked = nsChkAll.checked);
        });

        document.getElementById('btnLoadNonStock').addEventListener('click', async () => {
            hideInfo(nsInfo);

            if (!nsFrom.value || !nsTo.value) {
                setInfo(nsInfo, 'warn', 'Start Date & End Date wajib diisi.');
                return;
            }

            nsTbody.innerHTML = `
                <tr><td colspan="4" class="px-4 py-10 text-center text-gray-500">Loading...</td></tr>
            `;

            const url = new URL("{{ route('integration.ifcaintegration.nonstock.list') }}", window.location.origin);
            url.searchParams.set('from', nsFrom.value);
            url.searchParams.set('to', nsTo.value);

            try {
                const resp = await fetch(url.toString(), { headers: { 'Accept':'application/json' }});
                const json = await resp.json();

                if (!resp.ok || !json.ok) {
                    nsTbody.innerHTML = `<tr><td colspan="4" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
                    setInfo(nsInfo, 'err', json.message ?? 'Gagal load data.');
                    nsTotal.textContent = '0';
                    return;
                }

                const rows = json.data || [];
                nsTotal.textContent = rows.length;

                if (rows.length === 0) {
                    nsTbody.innerHTML = `<tr><td colspan="4" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
                    return;
                }

                nsTbody.innerHTML = rows.map(r => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <input type="checkbox" class="nsRowChk rounded border-gray-300" value="${r.id}">
                        </td>
                        <td class="px-3 py-2 font-medium text-gray-800">${r.inventoryid ?? ''}</td>
                        <td class="px-3 py-2 text-gray-700">${r.inventory_descr ?? ''}</td>
                        <td class="px-3 py-2 text-gray-700">${r.purchase_unit ?? ''}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                ${r.stage_status === 'H' ? 'bg-gray-200 text-gray-800' :
                                r.stage_status === 'P' ? 'bg-yellow-200 text-yellow-800' :
                                'bg-green-200 text-green-800'}">
                                ${r.stage_status}
                            </span>
                        </td>
                    </tr>
                `).join('');

                setInfo(nsInfo, 'ok', `Loaded ${rows.length} items.`);
            } catch (e) {
                nsTbody.innerHTML = `<tr><td colspan="4" class="px-4 py-10 text-center text-gray-500">No data.</td></tr>`;
                setInfo(nsInfo, 'err', e.message ?? 'Error saat load.');
            }
        });

        document.getElementById('btnProcessNonStock').addEventListener('click', async () => {
            hideInfo(nsInfo);

            const ids = Array.from(nsTbody.querySelectorAll('.nsRowChk:checked')).map(x => parseInt(x.value, 10));
            if (ids.length === 0) {
                setInfo(nsInfo, 'warn', 'Pilih minimal 1 item untuk diproses.');
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

                setInfo(nsInfo, 'ok', `Process triggered. Count: ${json.count ?? ids.length}`);
            } catch (e) {
                setInfo(nsInfo, 'err', e.message ?? 'Error saat process.');
            }
        });
    </script>
</x-app-layout>
