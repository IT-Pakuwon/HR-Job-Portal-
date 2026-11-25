<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-9xl mx-auto p-2">
        <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></span>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">Edit BQ Kontrak</h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Default: update Qty langsung. Kalau ganti Category → tabel ambil dari TEMP.</p>
                                    </div>
                                </div>
                            </div>

            <div class="p-6">

            {{-- Flash --}}
            @if (session('error'))
                <div class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-200">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div class="mt-3 rounded-lg bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Header info --}}
            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">BQ ID</label>
                    <div class="flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        {{ $bq->bqid }}
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                    <div class="flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        {{ $bq->cpny_id }}
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SPPJ</label>
                    <div class="flex min-h-[40px] items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        {{ $bq->sppjtid }}
                    </div>
                </div>
            </div>

            {{-- Category picker --}}
            <div class="mt-6">
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Category Kontrak</label>
                <div class="flex items-center gap-2">
                    <input id="kontrakcategory" type="text" readonly
                        class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 cursor-pointer"
                        value="{{ $currentCategory ?? '' }}"
                        placeholder="Klik 🔍 untuk pilih category (optional)">
                    <button id="btnPickCategory" type="button"
                        class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
                            Pilih
                        </button>
                </div>

                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="font-semibold">Mode:</span>
                    <span id="modeBadge"
                        class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-100">
                        Update Qty (Direct)
                    </span>
                </div>
            </div>

            <form id="updateForm" method="POST" action="{{ route('bqkontrak.update', $eid) }}">
                @csrf
                @method('PUT')

                <input type="hidden" name="temp_id" id="temp_id_hidden" value="{{ $tempId }}">
                <input type="hidden" name="kontrakcategory" id="kontrakcategory_hidden" value="{{ $currentCategory ?? '' }}">

                {{-- flag mode: 0=direct update BqDetail, 1=replace from temp --}}
                <input type="hidden" name="use_temp" id="use_temp_hidden" value="0">

                <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-3 text-left">No</th>
                                <th class="px-3 py-3 text-left">Type</th>
                                <th class="px-3 py-3 text-left">ID</th>
                                <th class="px-3 py-3 text-left">Description</th>
                                <th class="px-3 py-3 text-left">Qty</th>
                                <th class="px-3 py-3 text-left">UOM</th>
                                <th class="px-3 py-3 text-left">Duration</th>
                            </tr>
                        </thead>

                        {{-- DEFAULT: pakai BqDetail (direct update) --}}
                        <tbody id="detailTbody" class="divide-y dark:divide-gray-700">
                            @forelse($details as $d)
                                <tr class="transition-colors hover:bg-blue-50/60 dark:text-gray-100 dark:hover:bg-gray-700"
                                    data-mode="direct"
                                    data-id="{{ $d->id }}">
                                    <td class="px-3 py-2.5">{{ $d->bq_line_no }}</td>
                                    <td class="px-3 py-2.5">{{ $d->kontrak_bq_type }}</td>
                                    <td class="px-3 py-2.5">{{ $d->kontrak_bq_id }}</td>
                                    <td class="px-3 py-2.5">{{ $d->bq_descr }}</td>
                                    <td class="px-3 py-2.5">
                                        {{-- IMPORTANT: id = BqDetail.id --}}
                                        <input type="number" min="0" step="0.01"
                                            name="qty[{{ $d->id }}]" value="{{ (float) $d->qty }}"
                                            class="qty-input h-9 w-28 rounded-lg border border-gray-300 bg-white px-2 text-right text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                                    </td>
                                    <td class="px-3 py-2.5">{{ $d->uom }}</td>
                                    <td class="px-3 py-2.5">{{ $d->kontrak_duration_qty }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada detail Kontrak.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <a href="{{ route('bqkontrak.show', $eid) }}"
                        class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Back
                    </a>

                    <button type="submit" id="btnSubmitUpdate"
                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-60">
                        Update
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    {{-- Modal Select Category --}}
    <div id="categoryModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

        <div class="absolute left-1/2 top-1/2 w-[95%] max-w-5xl -translate-x-1/2 -translate-y-1/2 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="text-base font-bold text-gray-800 dark:text-gray-100">Select Kontrak Category</div>
                <button id="btnCloseModal"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>

            <div class="flex items-center gap-2 px-6 py-4">
                <input id="categorySearch" type="text" placeholder="Search..."
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                <button id="btnCategoryRefresh" type="button"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 shadow-sm transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
            </div>

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

    {{-- Loading overlay (optional UI kamu) --}}
    <div id="loadingSpinnerContainer" style="display:none;">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Updating<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const eid = @json($eid);
        const originalCategory = @json($currentCategory ?? '');

        // ====== Loading on submit ======
        function showLoading() {
            const el = document.getElementById('loadingSpinnerContainer');
            if (el) el.style.display = 'block';
        }
        function hideLoading() {
            const el = document.getElementById('loadingSpinnerContainer');
            if (el) el.style.display = 'none';
        }

        function setMode(useTemp) {
            // 0 direct, 1 temp
            document.getElementById('use_temp_hidden').value = useTemp ? '1' : '0';

            const badge = document.getElementById('modeBadge');
            if (!badge) return;

            if (useTemp) {
                badge.textContent = 'Ganti Category (TEMP)';
                badge.className =
                    'inline-flex items-center rounded-md bg-indigo-100 px-2 py-1 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200';
            } else {
                badge.textContent = 'Update Qty (Direct)';
                badge.className =
                    'inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-100';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // default mode direct
            setMode(false);

            const form = document.getElementById('updateForm');
            if (!form) return;

            form.addEventListener('submit', (e) => {
                const qtyInputs = form.querySelectorAll('input[name^="qty["]');
                let hasQty = false;
                qtyInputs.forEach(inp => {
                    const v = parseFloat(inp.value || '0');
                    if (!isNaN(v) && v > 0) hasQty = true;
                });
                if (!hasQty) {
                    e.preventDefault();
                    alert('Qty masih 0 semua. Isi minimal 1 item.');
                    return;
                }

                const btn = document.getElementById('btnSubmitUpdate');
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-70', 'cursor-not-allowed');
                    btn.textContent = 'Updating...';
                }
                showLoading();
            });

            window.addEventListener('pageshow', () => hideLoading());
        });

        // ====== Modal category logic ======
        let catPage = 1, catTotal = 0, catPerPage = 10, catSearch = '', catTotalPages = 1, catLoading = false;

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
            document.getElementById('categoryTbody').innerHTML =
                `<tr><td colspan="3" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">Loading...</td></tr>`;
        }

        function renderCategoryRows(rows) {
            const tbody = document.getElementById('categoryTbody');
            if (!rows || rows.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="3" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">No data</td></tr>`;
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
            document.getElementById('categoryInfo').textContent = `Showing ${shown} of ${catTotal} items`;
            document.getElementById('btnPrevCat').disabled = (catPage <= 1) || catLoading;
            document.getElementById('btnNextCat').disabled = (catPage >= catTotalPages) || catLoading;
        }

        async function loadCategories() {
            if (catLoading) return;
            catLoading = true;
            setLoadingRow();
            renderCategoryMeta({ page: catPage, per_page: catPerPage, total: catTotal, total_pages: catTotalPages });

            const url = `/bqkontrak/${eid}/categories?search=${encodeURIComponent(catSearch)}&page=${catPage}`;
            const res = await fetch(url);
            const json = await res.json();

            if (!json.ok) {
                renderCategoryRows([]);
                catLoading = false;
                renderCategoryMeta({ page: 1, per_page: 10, total: 0, total_pages: 1 });
                return;
            }

            renderCategoryRows(json.data);
            catLoading = false;
            renderCategoryMeta(json.meta);
        }

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
            if (catPage > 1) { catPage--; loadCategories(); }
        });
        document.getElementById('btnNextCat').addEventListener('click', () => {
            if (catPage < catTotalPages) { catPage++; loadCategories(); }
        });
        document.getElementById('btnCloseModal').addEventListener('click', closeModal);
        document.getElementById('btnPickCategory').addEventListener('click', openModal);

        // Render detail rows dari TEMP (mode category)
        function renderTempDetailRows(rows) {
            const tbody = document.getElementById('detailTbody');
            if (!rows || rows.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Belum ada detail.</td></tr>`;
                return;
            }

            tbody.innerHTML = rows.map(r => `
                <tr class="transition-colors hover:bg-blue-50/60 dark:text-gray-100 dark:hover:bg-gray-700" data-mode="temp" data-id="${r.id}">
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

        // click choose category -> pick-category (POST) -> render TEMP rows
        document.getElementById('categoryTbody').addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-choose');
            if (!btn) return;

            const category = btn.getAttribute('data-category') || '';

            // set category input + hidden
            document.getElementById('kontrakcategory').value = category;
            document.getElementById('kontrakcategory_hidden').value = category;

            // mode temp (karena user memilih category)
            setMode(true);

            const tempId = document.getElementById('temp_id_hidden').value;

            // disable button to avoid double click
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');

            const res = await fetch(`/bqkontrak/${eid}/pick-category`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ kontrakcategory: category, temp_id: tempId })
            });

            const text = await res.text();
            let json = null;

            try {
                json = JSON.parse(text);
            } catch (err) {
                console.error('Non-JSON response:', text);
                alert('Server error (response bukan JSON). Cek laravel.log / console.');
                btn.disabled = false;
                btn.classList.remove('opacity-70', 'cursor-not-allowed');
                return;
            }

            if (!res.ok || !json.ok) {
                alert(json.message || `Gagal pick category (HTTP ${res.status})`);
                btn.disabled = false;
                btn.classList.remove('opacity-70', 'cursor-not-allowed');
                return;
            }

            renderTempDetailRows(json.data);
            closeModal();
        });
    </script>
</x-app-layout>