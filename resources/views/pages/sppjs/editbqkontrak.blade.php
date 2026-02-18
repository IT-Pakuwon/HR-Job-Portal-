<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- (style loading overlay pakai style kamu yang sudah jadi) --}}
    <style>
        /* ... paste style loadingSpinnerContainer kamu di sini ... */
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes spinReverse { to { transform: rotate(-360deg); } }
        @keyframes blink { 0%, 80%, 100% { opacity: .2; } 40% { opacity: 1; } }
    </style>

    <div class="max-w-7xl mx-auto w-full px-6 py-4">
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-extrabold text-gray-800 dark:text-white">Edit BQ Kontrak</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-300">Bisa ganti Category → isi Qty → Update</p>
                </div>
            </div>

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
                    <label class="text-xs text-gray-500 dark:text-gray-300">BQ ID</label>
                    <div class="mt-1 rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:text-gray-100">
                        {{ $bq->bqid }}
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-300">Company</label>
                    <div class="mt-1 rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:text-gray-100">
                        {{ $bq->cpny_id }}
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-500 dark:text-gray-300">SPPJ</label>
                    <div class="mt-1 rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:text-gray-100">
                        {{ $bq->sppjtid }}
                    </div>
                </div>
            </div>

            {{-- Category picker (modal sama seperti create) --}}
            <div class="mt-6">
                <label class="text-xs text-gray-500 dark:text-gray-300">Category Kontrak</label>
                <div class="mt-1 flex items-center gap-2">
                    <input id="kontrakcategory" type="text" readonly
                        class="w-full rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        value="{{ $tempRows->first()->kontrakcategory ?? '' }}"
                        placeholder="Klik 🔍 untuk pilih category">
                    <button id="btnPickCategory" type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        🔍 Pilih
                    </button>
                </div>
            </div>

            <form id="updateForm" method="POST" action="{{ route('bqkontrak.update', $eid) }}">
                @csrf
                <input type="hidden" name="temp_id" id="temp_id_hidden" value="{{ $tempId }}">
                <input type="hidden" name="kontrakcategory" id="kontrakcategory_hidden"
                       value="{{ $tempRows->first()->kontrakcategory ?? '' }}">

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <tr>
                                <th class="px-3 py-2 text-left">No</th>
                                <th class="px-3 py-2 text-left">Type</th>
                                <th class="px-3 py-2 text-left">ID</th>
                                <th class="px-3 py-2 text-left">Description</th>
                                <th class="px-3 py-2 text-left">qty</th>
                                <th class="px-3 py-2 text-left">uom</th>
                                <th class="px-3 py-2 text-left">Duration (Bulan)</th>
                            </tr>
                        </thead>
                        <tbody id="detailTbody" class="divide-y dark:divide-gray-700">
                            @forelse($tempRows as $r)
                                <tr class="dark:text-gray-100">
                                    <td class="px-3 py-2">{{ $r->bq_line_no }}</td>
                                    <td class="px-3 py-2">{{ $r->kontrak_bq_type }}</td>
                                    <td class="px-3 py-2">{{ $r->kontrak_bq_id }}</td>
                                    <td class="px-3 py-2">{{ $r->bq_descr }}</td>
                                    <td class="px-3 py-2">
                                        <input type="number" min="0" step="0.01"
                                            name="qty[{{ $r->id }}]"
                                            value="{{ (float)$r->qty }}"
                                            class="qty-input w-28 rounded-md border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                                    </td>
                                    <td class="px-3 py-2">{{ $r->uom }}</td>
                                    <td class="px-3 py-2">{{ $r->kontrak_duration_qty }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada detail.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <a href="{{ route('bqkontrak.show', $eid) }}"
                       class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Back
                    </a>
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Select Category (copy dari create kamu) --}}
    <div id="categoryModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40"></div>

        <div class="absolute left-1/2 top-1/2 w-[95%] max-w-5xl -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-lg dark:bg-gray-800">
            <div class="flex items-center justify-between border-b px-5 py-3 dark:border-gray-700">
                <div class="text-sm font-bold text-gray-800 dark:text-gray-100">Select Kontrak Category</div>
                <button id="btnCloseModal"
                    class="rounded-md p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">✕</button>
            </div>

            <div class="flex items-center gap-2 px-5 py-3">
                <input id="categorySearch" type="text" placeholder="Search..."
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500
                        dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                <button id="btnCategoryRefresh" type="button"
                    class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                    ↻
                </button>
            </div>

            <div class="px-5 pb-4">
                <div class="overflow-x-auto rounded-lg border dark:border-gray-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <tr>
                                <th class="px-3 py-2 text-left">Kontrak Category</th>
                                <th class="px-3 py-2 text-left">Kontrak Descr</th>
                                <th class="px-3 py-2 text-center">Action</th>
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

                <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <div id="categoryInfo">Showing 0 of 0 items</div>
                    <div class="flex items-center gap-2">
                        <button id="btnPrevCat" class="rounded-md border px-3 py-1.5 disabled:opacity-50 dark:border-gray-700">Prev</button>
                        <button id="btnNextCat" class="rounded-md border px-3 py-1.5 disabled:opacity-50 dark:border-gray-700">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading overlay --}}
    <div id="loadingSpinnerContainer">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">Updating<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span></div>
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const eid = @json($eid);

        // ====== Loading on submit ======
        function showLoading(){ document.getElementById('loadingSpinnerContainer').style.display='block'; }
        function hideLoading(){ document.getElementById('loadingSpinnerContainer').style.display='none'; }

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('updateForm');
            if (!form) return;

            form.addEventListener('submit', (e) => {
                const cat = (document.getElementById('kontrakcategory_hidden')?.value || '').trim();
                if (!cat) { e.preventDefault(); alert('Category kontrak belum dipilih.'); return; }

                const qtyInputs = form.querySelectorAll('input[name^="qty["]');
                let hasQty = false;
                qtyInputs.forEach(inp => { const v = parseFloat(inp.value || '0'); if(!isNaN(v) && v>0) hasQty=true; });
                if (!hasQty) { e.preventDefault(); alert('Qty masih 0 semua. Isi minimal 1 item.'); return; }

                const btn = form.querySelector('button[type="submit"]');
                if (btn) { btn.disabled=true; btn.classList.add('opacity-70','cursor-not-allowed'); btn.textContent='Updating...'; }

                showLoading();
            });

            window.addEventListener('pageshow', () => hideLoading());
        });

        // ====== Modal category logic ======
        let catPage=1, catTotal=0, catPerPage=10, catSearch='', catTotalPages=1, catLoading=false;

        function openModal(){
            document.getElementById('categoryModal').classList.remove('hidden');
            catPage=1; loadCategories();
            setTimeout(()=>document.getElementById('categorySearch')?.focus(), 50);
        }
        function closeModal(){ document.getElementById('categoryModal').classList.add('hidden'); }

        function setLoadingRow(){
            document.getElementById('categoryTbody').innerHTML =
            `<tr><td colspan="3" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">Loading...</td></tr>`;
        }

        function renderCategoryRows(rows){
            const tbody = document.getElementById('categoryTbody');
            if (!rows || rows.length===0){
                tbody.innerHTML = `<tr><td colspan="3" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">No data</td></tr>`;
                return;
            }
            tbody.innerHTML = rows.map(r => `
                <tr class="dark:text-gray-100">
                    <td class="px-3 py-2">${r.kontrakcategory ?? ''}</td>
                    <td class="px-3 py-2">${r.kontrakcategory_descr ?? ''}</td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" class="btn-choose rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700"
                            data-category="${r.kontrakcategory}">
                            Choose
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function renderCategoryMeta(meta){
            catPage=meta.page; catPerPage=meta.per_page; catTotal=meta.total; catTotalPages=meta.total_pages || 1;
            const shown = Math.min(catPerPage, Math.max(catTotal - ((catPage-1)*catPerPage), 0));
            document.getElementById('categoryInfo').textContent = `Showing ${shown} of ${catTotal} items`;
            document.getElementById('btnPrevCat').disabled = (catPage<=1) || catLoading;
            document.getElementById('btnNextCat').disabled = (catPage>=catTotalPages) || catLoading;
        }

        async function loadCategories(){
            if (catLoading) return;
            catLoading=true;
            setLoadingRow();
            renderCategoryMeta({page:catPage, per_page:catPerPage, total:catTotal, total_pages:catTotalPages});

            const url = `/bqkontrak/${eid}/categories?search=${encodeURIComponent(catSearch)}&page=${catPage}`;
            const res = await fetch(url);
            const json = await res.json();

            if (!json.ok){
                renderCategoryRows([]);
                catLoading=false;
                renderCategoryMeta({page:1, per_page:10, total:0, total_pages:1});
                return;
            }
            renderCategoryRows(json.data);
            catLoading=false;
            renderCategoryMeta(json.meta);
        }

        let searchTimer=null;
        document.getElementById('categorySearch').addEventListener('input',(e)=>{
            clearTimeout(searchTimer);
            searchTimer=setTimeout(()=>{
                catSearch=e.target.value.trim();
                catPage=1; loadCategories();
            },300);
        });

        document.getElementById('btnCategoryRefresh').addEventListener('click',()=>{ catPage=1; loadCategories(); });
        document.getElementById('btnPrevCat').addEventListener('click',()=>{ if(catPage>1){catPage--; loadCategories();} });
        document.getElementById('btnNextCat').addEventListener('click',()=>{ if(catPage<catTotalPages){catPage++; loadCategories();} });
        document.getElementById('btnCloseModal').addEventListener('click', closeModal);
        document.getElementById('btnPickCategory').addEventListener('click', openModal);

        function renderDetailRows(rows){
            const tbody = document.getElementById('detailTbody');
            if (!rows || rows.length===0){
                tbody.innerHTML = `<tr><td colspan="7" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Belum ada detail.</td></tr>`;
                return;
            }

            tbody.innerHTML = rows.map(r => `
                <tr class="dark:text-gray-100">
                    <td class="px-3 py-2">${r.bq_line_no ?? ''}</td>
                    <td class="px-3 py-2">${r.kontrak_bq_type ?? ''}</td>
                    <td class="px-3 py-2">${r.kontrak_bq_id ?? ''}</td>
                    <td class="px-3 py-2">${r.bq_descr ?? ''}</td>
                    <td class="px-3 py-2">
                        <input type="number" min="0" step="0.01"
                            name="qty[${r.id}]"
                            value="${parseFloat(r.qty ?? 0)}"
                            class="qty-input w-28 rounded-md border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                    </td>
                    <td class="px-3 py-2">${r.uom ?? ''}</td>
                    <td class="px-3 py-2">${r.kontrak_duration_qty ?? ''}</td>
                </tr>
            `).join('');
        }

        // click choose category
        document.getElementById('categoryTbody').addEventListener('click', async (e)=>{
            const btn = e.target.closest('.btn-choose');
            if (!btn) return;

            const category = btn.getAttribute('data-category');
            document.getElementById('kontrakcategory').value = category;
            document.getElementById('kontrakcategory_hidden').value = category;

            const tempId = document.getElementById('temp_id_hidden').value;

            const res = await fetch(`/bqkontrak/${eid}/pick-category`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ kontrakcategory: category, temp_id: tempId })
            });

            // ✅ aman: coba parse json, kalau gagal tampilkan response text
            const text = await res.text();
            let json = null;

            try {
            json = JSON.parse(text);
            } catch (err) {
            console.error('Non-JSON response:', text);
            alert('Server error (response bukan JSON). Cek laravel.log / console.');
            return;
            }

            if (!res.ok || !json.ok) {
            alert(json.message || `Gagal pick category (HTTP ${res.status})`);
            return;
            }

            renderDetailRows(json.data);
            closeModal();

        });
    </script>
</x-app-layout>
