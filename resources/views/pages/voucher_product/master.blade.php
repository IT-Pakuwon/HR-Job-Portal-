<x-app-layout>
    <x-spp-list-styles />
    <x-vpl-styles />

    <style>
        #filter_type { display: flex; align-items: center; }

        /* a locked product name (editing existing stock) has to look locked */
        #product_name[readonly] { background: #f3f4f6; color: #6b7280; cursor: not-allowed; }
        .dark #product_name[readonly] { background: rgba(255, 255, 255, .04); color: #9ca3af; }
        .vpl-dialog { animation: vpl-dialog-in .18s ease-out; }
        @keyframes vpl-dialog-in { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { .vpl-dialog { animation: none; } }
    </style>

    {{-- Pass default company to JS --}}
    <input type="hidden" id="defaultCpny" value="{{ $usercpny2->cpny_id ?? '' }}">

    <div class="max-w-9xl mx-auto w-full p-2">

        @php
            $cards = [
                [['data-status' => ''], 'All', '📦', 'all', $countAll, true, null, 'active-card'],
                [['data-status' => 'A'], 'Active', '✅', 'green', $countActive, false],
                [['data-status' => 'X'], 'Inactive', '⛔', 'red', $countInactive, false],
            ];
            // All Product — admin only, system-wide view
            if ($isAdmin) {
                $cards[] = [['data-status' => 'ADMINALL'], 'All Product', '🌐', 'purple', $countAdminAll, false];
            }
        @endphp
        <x-status-filter-cards :custom="$cards" />

        {{-- TABLE CARD --}}
        <div class="dt-pretty mt-3 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <x-list-page-header title="Master Stock" subtitle="Vouchers and products, their stock and status" icon='<i class="fa-solid fa-boxes-stacked"></i>'>
                @if($isAdmin)
                    <div id="adminAllFilters" class="hidden items-center gap-2">
                        <div class="sppb-f relative w-44">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-building text-xs"></i></span>
                            <select id="adm_filter_company" aria-label="Company">
                                <option value="">All Companies</option>
                                @foreach ($allCompanies as $cpny)
                                    <option value="{{ $cpny }}">{{ $cpny }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <a href="{{ route('vpl.msproduct.setupwarehouse') }}" target="_blank" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        <i class="fa-solid fa-sliders text-xs"></i> Setup
                    </a>
                @endif
                <button id="btnNewStock" type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300">
                    <i class="fa-solid fa-plus text-xs"></i> New Stock
                </button>
            </x-list-page-header>

            {{-- FILTERS --}}
            <div class="border-b border-gray-100 bg-gray-50/60 px-5 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <label for="filter_type" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</label>
                        <select id="filter_type" class="sf-select h-10 px-3 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">All Types</option>
                            <option value="V">Voucher</option>
                            <option value="P">Product</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter_doc_id" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Doc ID</label>
                        <div class="sppb-f relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-hashtag text-xs"></i></span>
                            <select id="filter_doc_id" style="width:100%">
                                <option value="">All Doc IDs</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="filter_category" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</label>
                        <div class="sppb-f relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-tags text-xs"></i></span>
                            <select id="filter_category" style="width:100%">
                                <option value="">All Categories</option>
                                @foreach ($allCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="filter_source" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Source</label>
                        <div class="sppb-f relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-location-arrow text-xs"></i></span>
                            <select id="filter_source" style="width:100%">
                                <option value="">All Sources</option>
                                @foreach ($allSources as $src)
                                    <option value="{{ $src }}">{{ $src }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="filter_product_name" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product Name</label>
                        <input type="text" id="filter_product_name" placeholder="Search product name..." class="h-10 px-3 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-end gap-2">
                    <button type="button" id="btn_reset_filter" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        <i class="fa-solid fa-rotate-left text-xs"></i> Reset
                    </button>
                    <button type="button" id="btn_export_filter"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 text-sm font-semibold text-green-700 transition hover:border-green-600 hover:bg-green-600 hover:text-white dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-300">
                        <i class="fa-solid fa-file-excel text-xs"></i> Export to Excel
                    </button>
                    <button type="button" id="btn_apply_filter" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300">
                        <i class="fa-solid fa-filter text-xs"></i> Apply Filter
                    </button>
                </div>
            </div>

            <div class="relative overflow-hidden">
                <table id="masterTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 font-medium text-left">Doc No</th>
                            <th class="px-4 py-3 font-medium text-left">Company</th>
                            <th class="px-4 py-3 font-medium text-left">Type</th>
                            <th class="px-4 py-3 font-medium text-left">Product Name</th>
                            <th class="px-4 py-3 font-medium text-right">Total Stock</th>
                            <th class="px-4 py-3 font-medium text-left">Category</th>
                            <th class="px-4 py-3 font-medium text-left">Source (PT)</th>
                            <th class="px-4 py-3 font-medium text-left">Tenant / Event</th>
                            <th class="px-4 py-3 font-medium text-center">Status</th>
                            <th class="px-4 py-3 font-medium text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================
     NEW / EDIT STOCK
     ============================================================ --}}
    <div id="modalStock" class="vpl-pop fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-2 backdrop-blur-sm sm:p-4">

        <div class="vpl-dialog relative flex max-h-[94vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">

            <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-box"></i></span>
                    <div class="min-w-0">
                        <h2 id="modalTitle" class="truncate text-base font-bold text-gray-800 dark:text-white">New Stock</h2>
                        <p id="modalSubtitle" class="truncate text-xs text-gray-500 dark:text-gray-400">Add a new voucher or product to master data</p>
                    </div>
                </div>
                <button id="btnCloseModal" type="button" aria-label="Close" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>

            <form id="stockForm" class="min-h-0 space-y-4 overflow-y-auto bg-gray-50 p-5 dark:bg-gray-900/60">
                @csrf
                <input type="hidden" id="key_id" name="key_id">

                <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-circle-info"></i></span><span>Stock Identity</span></h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="cpnyidx" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                            <select id="cpnyidx" name="cpnyid" class="select2 vpl-select w-full">
                                <option value="">Select Company</option>
                                @foreach ($usercpny as $c)
                                    <option value="{{ $c->cpny_id }}"
                                        {{ $c->cpny_id == ($usercpny2->cpny_id ?? '') ? 'selected' : '' }}>
                                        {{ $c->cpny_id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="product_typex" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product Type</label>
                            <select id="product_typex" name="product_type" class="select2 vpl-select w-full">
                                <option value="">Select Type</option>
                                <option value="V">Voucher</option>
                                <option value="P">Product</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="product_name" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product Name</label>
                            <input type="text" id="product_name" name="product_name" class="h-10 px-3 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="Enter product name...">
                        </div>
                        <div>
                            <label for="categoryx" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</label>
                            <select id="categoryx" name="product_category" class="select2 vpl-select w-full">
                                <option value="">select type first</option>
                            </select>
                        </div>
                        <div>
                            <label for="product_uom" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">UOM</label>
                            <select id="product_uom" name="product_uom" class="select2 vpl-select w-full">
                                <option value="">Select UOM</option>
                                @foreach ($uomList as $uom)
                                    <option value="{{ $uom }}">{{ $uom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="photoFieldWrap" class="hidden md:col-span-2">
                            <label for="product_photo" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product Photo</label>
                            <div class="flex items-center gap-4">
                                <img id="photoPreview" src="" alt="Preview" class="hidden h-20 w-20 shrink-0 rounded-lg border border-gray-200 object-cover dark:border-gray-700">
                                <input type="file" id="product_photo" name="product_photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="w-full rounded-lg border border-dashed border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-600 transition file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-700 hover:border-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            </div>
                            <p class="mt-1 text-xs text-gray-400">JPG, JPEG or PNG. Max 5MB.</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-location-arrow"></i></span><span>Source Information</span></h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="product_source_company" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama PT</label>
                            <input type="text" id="product_source_company" name="product_source_company" class="h-10 px-3 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="Nama perusahaan...">
                        </div>
                        <div>
                            <label for="product_source_tenant" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Tenant / Event</label>
                            <input type="text" id="product_source_tenant" name="product_source_tenant" class="h-10 px-3 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="Tenant or event name...">
                        </div>
                        <div>
                            <label for="product_value" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Value</label>
                            <input type="text" id="product_value" name="product_value" class="h-10 px-3 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" value="0" placeholder="0">
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-note-sticky"></i></span><span>Additional</span></h3>
                    <div class="space-y-4">
                        <div>
                            <label for="product_remark" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Remarks</label>
                            <textarea id="product_remark" name="product_remark" rows="2" class="min-h-[4.5rem] px-3 py-2 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="Optional remarks..."></textarea>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 transition has-[:checked]:border-blue-200 has-[:checked]:bg-blue-50 dark:border-gray-600 dark:bg-gray-700/40 dark:has-[:checked]:border-blue-500/30 dark:has-[:checked]:bg-blue-500/10">
                            <input type="hidden" name="product_check_exp" value="0">
                            <input type="checkbox" id="product_check_exp" name="product_check_exp" value="1" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-300 dark:border-gray-500">
                            <div>
                                <label for="product_check_exp" class="cursor-pointer text-sm font-semibold text-gray-700 dark:text-gray-200">Check Expired Date</label>
                                <p class="text-xs text-gray-400">Enable expiry tracking for this stock item</p>
                            </div>
                        </div>
                    </div>
                </section>

                <div id="formError" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-900/20 dark:text-red-300"></div>
            </form>

            <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs text-gray-400"><span class="text-red-500">*</span> Required fields</p>
                <div class="ml-auto flex items-center gap-3">
                    <button id="btnCancelModal" type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                    <button id="btnSave" type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300 disabled:opacity-50">
                        <i class="fa-solid fa-floppy-disk text-xs"></i> Save
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Total Stock hover tooltip: per-warehouse / expiry breakdown (fixed-position avoids table overflow clipping) --}}
    <div id="totalStockTooltip"
        class="fixed z-[9999] hidden max-w-xs rounded-xl border border-gray-200 bg-white p-3 shadow-xl dark:border-gray-700 dark:bg-gray-800"
        style="min-width:220px">
        <div class="mb-1.5 border-b border-gray-100 pb-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:border-gray-700">Stock by Warehouse</div>
        <div id="totalStockTooltipBody" class="space-y-1 text-xs text-gray-700 dark:text-gray-200"></div>
    </div>

    {{-- VIEW PRODUCT --}}
    <div id="viewProductModal" class="vpl-pop fixed inset-0 z-[1000] hidden items-center justify-center p-4">

        <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 backdrop-blur-sm transition-opacity duration-200 dark:bg-black/70"></div>

        <div class="modal-panel relative z-10 flex max-h-[92vh] w-full max-w-5xl translate-y-4 scale-[0.98] flex-col overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl ring-1 ring-black/5 transition-all duration-200 dark:bg-gray-800 dark:ring-white/10">

            <input type="hidden" id="viewModalProductDbId">

            <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-box-open"></i></span>
                    <div>
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Master Stock</div>
                        <div class="flex items-center gap-2.5">
                            <h2 id="viewModal_productId" class="text-base font-bold text-gray-800 dark:text-white">-</h2>
                            <div id="viewModal_status"></div>
                        </div>
                    </div>
                </div>
                <button id="btnCloseViewModal" type="button" aria-label="Close" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto bg-gray-50 p-5 dark:bg-gray-900/60">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

                    <div class="space-y-4">

                        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700"><h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-building"></i></span><span>Tenant Information</span></h3></div>
                            <dl class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">
                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Site</dt>
                                    <dd id="viewModal_cpnyid" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Nama PT</dt>
                                    <dd id="viewModal_sourcePT" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Nama Tenant / Event</dt>
                                    <dd id="viewModal_tenant" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700"><h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-circle-info"></i></span><span>Item Information</span></h3></div>
                            <dl class="grid grid-cols-2 gap-4 p-5">
                                <div class="col-span-2 empty:hidden">
                                    <img id="viewModal_photo" src="" alt="Product Photo" class="hidden h-28 w-28 rounded-xl border border-gray-200 object-cover dark:border-gray-700">
                                </div>
                                <div class="col-span-2">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Product Name</dt>
                                    <dd id="viewModal_productName" class="mt-1 break-words text-base font-bold text-gray-900 dark:text-white">-</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Type</dt>
                                    <dd id="viewModal_type" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Category</dt>
                                    <dd id="viewModal_category" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Product Source</dt>
                                    <dd id="viewModal_sourceType" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">UOM</dt>
                                    <dd id="viewModal_uom" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Value</dt>
                                    <dd id="viewModal_value" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Check Expired Date</dt>
                                    <dd id="viewModal_checkExp" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">-</dd>
                                </div>
                                <div class="col-span-2">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Remarks</dt>
                                    <dd id="viewModal_remarks" class="mt-1 break-words whitespace-pre-line text-sm leading-relaxed text-gray-700 dark:text-gray-300">-</dd>
                                </div>
                            </dl>
                        </section>

                    </div>

                    <div class="space-y-4">

                        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                                <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-warehouse"></i></span><span>Stock Detail</span></h3>
                                <div class="w-52">
                                    <select id="viewStockExpFilter" class="w-full">
                                        <option value="">All Expired Dates</option>
                                    </select>
                                </div>
                            </div>
                            <div id="viewStockTabs" class="flex flex-wrap gap-1.5 border-b border-gray-100 px-4 py-2.5 dark:border-gray-700"></div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03]">
                                        <tr>
                                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Expired Date</th>
                                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Warehouse</th>
                                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viewStockBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                                </table>
                            </div>
                        </section>

                        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                                <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-xs text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><i class="fa-solid fa-hourglass-half"></i></span><span>Related Transactions (On Progress)</span></h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03]">
                                        <tr>
                                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Doc No</th>
                                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Warehouse</th>
                                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viewRelatedTrxBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                                </table>
                            </div>
                        </section>

                    </div>
                </div>
            </div>

            <div class="flex shrink-0 items-center justify-between gap-3 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                <button id="btnCloseViewModalFooter" type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Close</button>
                <button id="btnViewModalEdit" type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300">
                    <i class="fa-solid fa-pen-to-square text-xs"></i> Edit
                </button>
            </div>

        </div>
    </div>

    <x-spp-list-scripts />

    <script>
        $(function() {
            $('#adm_filter_company').select2({ width: '100%', dropdownCssClass: 'sppb-s2-dropdown' });
        });
    </script>

    {{-- JS modules --}}
    <script src="{{ asset('assets/js/voucher-product/core.js') }}?v={{ filemtime(public_path('assets/js/voucher-product/core.js')) }}"></script>
    <script src="{{ asset('assets/js/voucher-product/helper.js') }}?v={{ filemtime(public_path('assets/js/voucher-product/helper.js')) }}"></script>
    <script src="{{ asset('assets/js/voucher-product/modal.js') }}?v={{ filemtime(public_path('assets/js/voucher-product/modal.js')) }}"></script>
    <script src="{{ asset('assets/js/voucher-product/datalist.js') }}?v={{ filemtime(public_path('assets/js/voucher-product/datalist.js')) }}"></script>
    <script src="{{ asset('assets/js/voucher-product/form.js') }}?v={{ filemtime(public_path('assets/js/voucher-product/form.js')) }}"></script>
    <script src="{{ asset('assets/js/voucher-product/view-modal.js') }}?v={{ filemtime(public_path('assets/js/voucher-product/view-modal.js')) }}"></script>
    <script src="{{ asset('assets/js/voucher-product/init.js') }}?v={{ filemtime(public_path('assets/js/voucher-product/init.js')) }}"></script>

</x-app-layout>
