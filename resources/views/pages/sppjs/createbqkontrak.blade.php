<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="max-w-9xl mx-auto p-2">
        <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">Create BQ Kontrak</h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih Category Kontrak → isi Qty → Save</p>
                                    </div>
                                </div>
                            </div>

            <div class="p-6">

            <div class="flex flex-col gap-2">
                {{-- Header info --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SPPJ ID</label>
                        <div class="flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            {{ $sppj->sppjid }}
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                        <div class="flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            {{ $sppj->cpny_id }}
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                        <div class="flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            {{ $sppj->department_id }}
                        </div>
                    </div>
                </div>

                {{-- Category picker --}}
                <div>
                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Category Kontrak</label>
                    <div class="flex items-center gap-2">
                        <input id="kontrakcategory" type="text" readonly
                            class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 cursor-pointer"
                            placeholder="Klik 🔍 untuk pilih category">
                        <button id="btnPickCategory" type="button"
                            class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
                            Pilih
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pilih category akan mengisi tabel detail
                        dari
                        MsKontrakBQ ke temp.</p>
                </div>
            </div>


            {{-- Detail table --}}
            {{-- Flash message --}}
            @if (session('error'))
                <div class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-200">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div
                    class="mt-3 rounded-lg bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            <form id="saveForm" method="POST" action="{{ route('bqkontrak.save', $sppj->id) }}">
                @csrf
                <input type="hidden" name="kontrakcategory" id="kontrakcategory_hidden">
                <input type="hidden" name="temp_id" id="temp_id_hidden"
                    value="{{ $tempRows->first()->temp_id ?? '' }}">


                {{-- Detail table --}}
                <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-3 text-left">No</th>
                                <th class="px-3 py-3 text-left">Type</th>
                                <th class="px-3 py-3 text-left">ID</th>
                                <th class="px-3 py-3 text-left">Description</th>
                                <th class="px-3 py-3 text-left">qty</th>
                                <th class="px-3 py-3 text-left">uom</th>
                                <th class="px-3 py-3 text-left">Duration</th>
                            </tr>
                        </thead>

                        <tbody id="detailTbody" class="divide-y dark:divide-gray-700">
                            @forelse($tempRows as $r)
                                <tr class="transition-colors hover:bg-blue-50/60 dark:text-gray-100 dark:hover:bg-gray-700">
                                    <td class="px-3 py-2.5">{{ $r->bq_line_no }}</td>
                                    <td class="px-3 py-2.5">{{ $r->kontrak_bq_type }}</td>
                                    <td class="px-3 py-2.5">{{ $r->kontrak_bq_id }}</td>
                                    <td class="px-3 py-2.5">{{ $r->bq_descr }}</td>
                                    <td class="px-3 py-2.5">
                                        <input type="number" min="0" step="0.01"
                                            name="qty[{{ $r->id }}]" {{-- ✅ ini kunci --}}
                                            value="{{ (float) $r->qty }}"
                                            class="qty-input h-9 w-28 rounded-lg border border-gray-300 bg-white px-2 text-right text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                                    </td>
                                    <td class="px-3 py-2.5">{{ $r->uom }}</td>
                                    <td class="px-3 py-2.5">{{ $r->kontrak_duration_qty }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada detail. Klik 🔍 pilih Category Kontrak.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Save --}}
                <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <button type="submit" id="btnSave"
                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-60">
                        Save
                    </button>
                </div>
            </form>

        </div>
        </div>
    </div>

    {{-- Modal Select Category --}}
    <div id="categoryModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

        <div
            class="absolute left-1/2 top-1/2 w-[95%] max-w-5xl -translate-x-1/2 -translate-y-1/2 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="text-base font-bold text-gray-800 dark:text-gray-100">Select Kontrak Category</div>
                <button id="btnCloseModal"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>

            {{-- Search bar --}}
            <div class="flex items-center gap-2 px-6 py-4">
                <input id="categorySearch" type="text" placeholder="Search..."
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                <button id="btnCategoryRefresh" type="button"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 shadow-sm transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
            </div>

            {{-- Table --}}
            <div class="px-6 pb-5">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-3 text-left">Kontrak Category</th>
                                <th class="px-3 py-3 text-left">Kontrak Descr</th>
                                <th class="px-3 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="categoryTbody" class="divide-y dark:divide-gray-700">
                            <tr>
                                <td colspan="3" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Ketik untuk search...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Footer pagination --}}
                <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                    <div id="categoryInfo">Showing 0 of 0 items</div>
                    <div class="flex items-center gap-2">
                        <button id="btnPrevCat"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Prev</button>
                        <button id="btnNextCat"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingSpinnerContainer">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Saving
                <span class="loading-ellipsis">
                    <span>.</span><span>.</span><span>.</span>
                </span>
            </div>
        </div>
    </div>

    <script>
        function showLoading() {
            const el = document.getElementById('loadingSpinnerContainer');
            if (el) el.style.display = 'block';
        }

        function hideLoading() {
            const el = document.getElementById('loadingSpinnerContainer');
            if (el) el.style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('saveForm');
            if (!form) return;

            form.addEventListener('submit', (e) => {
                // ✅ sync hidden category (biar selalu ikut)
                const cat = (document.getElementById('kontrakcategory_hidden')?.value || '').trim();

                // if (!cat) {
                //     e.preventDefault();
                //     alert('Category kontrak belum dipilih.');
                //     return;
                // }

                // ✅ minimal 1 qty > 0 dari input yg ada di form
                // const qtyInputs = form.querySelectorAll('input[name^="qty["]');
                // let hasQty = false;
                // qtyInputs.forEach(inp => {
                //     const v = parseFloat(inp.value || '0');
                //     if (!isNaN(v) && v > 0) hasQty = true;
                // });

                // if (!hasQty) {
                //     e.preventDefault();
                //     alert('Qty masih 0 semua. Isi minimal 1 item.');
                //     return;
                // }

                // ✅ show loading + disable button biar ga double submit
                const btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-70', 'cursor-not-allowed');
                    btn.textContent = 'Saving...';
                }

                showLoading();
            });

            // kalau user klik back / reload, pastikan spinner gak nyangkut
            window.addEventListener('pageshow', () => hideLoading());
        });
    </script>




    {{-- JS --}}
   <script>
    const sppjId = @json($sppj->id);
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ===== State & UI Helpers =====
    const $tempHidden = () => document.getElementById('temp_id_hidden');
    const $catHidden  = () => document.getElementById('kontrakcategory_hidden');
    const $catInput   = () => document.getElementById('kontrakcategory');
    const $btnSave    = () => document.getElementById('btnSave');

    function setSaveEnabled(on) {
        const btn = $btnSave();
        if (!btn) return;
        btn.disabled = !on;
    }

    // default: kalau belum ada tempRows (first load), disable save
    document.addEventListener('DOMContentLoaded', () => {
        const hasTemp = !!($tempHidden()?.value || '').trim();
        setSaveEnabled(hasTemp);
    });

    // ===== Modal paging =====
    let catPage = 1;
    let catTotal = 0;
    let catPerPage = 10;
    let catSearch = '';
    let catTotalPages = 1;
    let catLoading = false;

    function openModal() {
        document.getElementById('categoryModal').classList.remove('hidden');
        catPage = 1;
        loadCategories();
        setTimeout(() => document.getElementById('categorySearch')?.focus(), 50);
    }

    function closeModal() {
        document.getElementById('categoryModal').classList.add('hidden');
    }

    function setLoadingRow() {
        document.getElementById('categoryTbody').innerHTML = `
            <tr>
                <td colspan="3" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">Loading...</td>
            </tr>`;
    }

    function renderCategoryRows(rows) {
        const tbody = document.getElementById('categoryTbody');

        if (!rows || rows.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">No data</td>
                </tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr class="transition-colors hover:bg-blue-50/60 dark:text-gray-100 dark:hover:bg-gray-700">
                <td class="px-3 py-2.5">${r.kontrakcategory ?? ''}</td>
                <td class="px-3 py-2.5">${r.kontrakcategory_descr ?? ''}</td>
                <td class="px-3 py-2.5 text-center">
                    <button type="button"
                        class="btn-choose inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                        data-category="${r.kontrakcategory}">
                        Choose
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function renderCategoryMeta(meta) {
        catPage = meta.page;
        catPerPage = meta.per_page;
        catTotal = meta.total;
        catTotalPages = meta.total_pages || 1;

        const shown = Math.min(catPerPage, Math.max(catTotal - ((catPage - 1) * catPerPage), 0));

        document.getElementById('categoryInfo').textContent =
            `Showing ${shown} of ${catTotal} items`;

        document.getElementById('btnPrevCat').disabled = (catPage <= 1) || catLoading;
        document.getElementById('btnNextCat').disabled = (catPage >= catTotalPages) || catLoading;
    }

    async function loadCategories() {
        if (catLoading) return;
        catLoading = true;

        setLoadingRow();
        renderCategoryMeta({
            page: catPage,
            per_page: catPerPage,
            total: catTotal,
            total_pages: catTotalPages
        });

        const url = `/createbqkontrak/${sppjId}/categories?search=${encodeURIComponent(catSearch)}&page=${catPage}`;
        const res = await fetch(url);
        const json = await res.json();

        if (!json.ok) {
            renderCategoryRows([]);
            catLoading = false;
            renderCategoryMeta({
                page: 1,
                per_page: 10,
                total: 0,
                total_pages: 1
            });
            return;
        }

        renderCategoryRows(json.data);

        catLoading = false;
        renderCategoryMeta(json.meta);
    }

    // Debounce search
    let searchTimer = null;
    document.getElementById('categorySearch').addEventListener('input', (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            catSearch = e.target.value.trim();
            catPage = 1;
            loadCategories();
        }, 300);
    });

    document.getElementById('btnCategoryRefresh').addEventListener('click', () => {
        catPage = 1;
        loadCategories();
    });

    document.getElementById('btnPrevCat').addEventListener('click', () => {
        if (catPage > 1) {
            catPage--;
            loadCategories();
        }
    });

    document.getElementById('btnNextCat').addEventListener('click', () => {
        if (catPage < catTotalPages) {
            catPage++;
            loadCategories();
        }
    });

    document.getElementById('btnCloseModal').addEventListener('click', closeModal);
    document.getElementById('btnPickCategory').addEventListener('click', openModal);

    // ===== render detail =====
    function renderDetailRows(rows) {
        const tbody = document.getElementById('detailTbody');
        if (!rows || rows.length === 0) {
            tbody.innerHTML =
                `<tr><td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Belum ada detail.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr class="transition-colors hover:bg-blue-50/60 dark:text-gray-100 dark:hover:bg-gray-700">
                <td class="px-3 py-2.5">${r.bq_line_no ?? ''}</td>
                <td class="px-3 py-2.5">${r.kontrak_bq_type ?? ''}</td>
                <td class="px-3 py-2.5">${r.kontrak_bq_id ?? ''}</td>
                <td class="px-3 py-2.5">${r.bq_descr ?? ''}</td>
                <td class="px-3 py-2.5">
                    <input type="number" min="0" step="0.01"
                        name="qty[${r.id}]"
                        value="${parseFloat(r.qty ?? 0)}"
                        class="qty-input h-9 w-28 rounded-lg border border-gray-300 bg-white px-2 text-right text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                </td>
                <td class="px-3 py-2.5">${r.uom ?? ''}</td>
                <td class="px-3 py-2.5">${r.kontrak_duration_qty ?? ''}</td>
            </tr>
        `).join('');
    }

    // ===== Choose category (IMPORTANT FIX) =====
    document.getElementById('categoryTbody').addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-choose');
        if (!btn) return;

        // disable save while loading new detail
        setSaveEnabled(false);

        const category = btn.getAttribute('data-category') || '';

        // set category input + hidden
        $catInput().value = category;
        $catHidden().value = category;

        // current temp_id (if any)
        const currentTempId = ($tempHidden().value || '').trim();

        const res = await fetch(`/createbqkontrak/${sppjId}/pick-category`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({
                kontrakcategory: category,
                temp_id: currentTempId
            })
        });

        const text = await res.text();
        let json;
        try {
            json = JSON.parse(text);
        } catch (err) {
            console.error('Non-JSON response:', text);
            alert('Server error (response bukan JSON). Cek laravel.log');
            setSaveEnabled(false);
            return;
        }

        if (!res.ok || !json.ok) {
            alert(json.message || 'Gagal ambil data kontrak BQ');
            setSaveEnabled(false);
            return;
        }

        // ✅ MUST set temp_id from server (ini yg bikin save gagal kalau kosong)
        if (json.temp_id) {
            $tempHidden().value = json.temp_id;
        }

        renderDetailRows(json.data || []);
        closeModal();

        // enable save only when we have temp_id + rows
        const okEnable = !!($tempHidden().value || '').trim() && Array.isArray(json.data) && json.data.length > 0;
        setSaveEnabled(okEnable);
    });

    // ===== Submit guard (prevent submit if temp_id empty) =====
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('saveForm');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            const tempId = ($tempHidden()?.value || '').trim();
            const cat = ($catHidden()?.value || '').trim();

            // kalau user belum pick category / belum ada temp rows
            if (!tempId) {
                e.preventDefault();
                alert('Detail Kontrak masih kosong. Pilih Category dulu.');
                return;
            }

            // optional: kalau category kosong tapi temp_id ada (harusnya tidak terjadi)
            if (!cat) {
                e.preventDefault();
                alert('Category kontrak belum dipilih.');
                return;
            }

            const btn = $btnSave();
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');
                btn.textContent = 'Saving...';
            }

            // show loading spinner if you want
            const el = document.getElementById('loadingSpinnerContainer');
            if (el) el.style.display = 'block';
        });

        window.addEventListener('pageshow', () => {
            const el = document.getElementById('loadingSpinnerContainer');
            if (el) el.style.display = 'none';
        });
    });
</script>



</x-app-layout>
