<x-app-layout>

    <style>
        /* ── match IT Recommendation sheet ── */
        :root {
            --vpl-border: #e2e8f0;
            --vpl-card: #ffffff;
            --vpl-bg: #f8fafc;
            --vpl-text: #0f172a;
            --vpl-muted: #64748b;
        }

        .dark {
            --vpl-border: rgba(255, 255, 255, .08);
            --vpl-card: #0f172a;
            --vpl-bg: #020617;
            --vpl-text: #f8fafc;
            --vpl-muted: #cbd5e1;
        }

        /* required label asterisk */
        label.req::after {
            content: " *";
            color: #ef4444;
            font-weight: 700;
        }

        /* field inputs */
        .vpl-input,
        .vpl-textarea {
            width: 100%;
            border: 1px solid var(--vpl-border);
            background: var(--vpl-card);
            border-radius: 8px;
            font-size: 14px;
            color: var(--vpl-text);
            transition: border-color .15s, box-shadow .15s;
        }

        .vpl-input {
            height: 44px;
            padding: 0 14px;
        }

        .vpl-textarea {
            padding: 10px 14px;
            resize: vertical;
        }

        .vpl-input::placeholder,
        .vpl-textarea::placeholder {
            color: #94a3b8;
        }

        .vpl-input:focus,
        .vpl-textarea:focus {
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .15);
            outline: none;
        }

        /* Select2 — no z-index on the container itself so it stays
           in normal document flow and the sidebar overlay covers it */
        .select2-container {
            width: 100%;
        }

        .select2-container--default .select2-selection--single {
            height: 44px !important;
            border: 1px solid var(--vpl-border) !important;
            border-radius: 8px !important;
            background: var(--vpl-card) !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--single:focus-within,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .15) !important;
        }

        .select2-selection__rendered {
            padding-left: 14px !important;
            font-size: 14px !important;
            color: var(--vpl-text) !important;
            line-height: 44px !important;
        }

        .select2-selection__arrow {
            top: 10px !important;
            right: 10px !important;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #e2e8f0 !important;
            border-radius: 6px !important;
            padding: 6px 10px !important;
            color: #0f172a !important;
        }

        .select2-results__option {
            color: #0f172a !important;
            font-size: 14px;
            padding: 8px 14px !important;
        }

        .select2-results__option--highlighted {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }

        .select2-results__option[aria-selected="true"] {
            background-color: #e0e7ff !important;
            color: #3730a3 !important;
        }

        /* Only the open floating dropdown list gets a high z-index */
        .select2-dropdown {
            z-index: 9999 !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .12) !important;
            background: #fff !important;
        }

        #modalStock>div {
            animation: vplModalFade .18s ease;
        }

        @keyframes vplModalFade {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── Table — matches IT Recommendation exactly ── */
        #masterTable {
            min-width: 700px;
        }

        #masterTable thead th {
            padding: 12px 20px;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #6b7280;
            background: rgba(249, 250, 251, .7);
            border-bottom: 1px solid #f3f4f6;
            white-space: nowrap;
        }

        /* DataTables wrapper padding — same as IT Recommendation style partial */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            padding: 16px 18px;
            font-size: 13px;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            padding: 14px 18px;
            font-size: 13px;
        }

        /* filter search input */
        .dataTables_wrapper .dataTables_filter input {
            margin-left: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 5px 12px;
            font-size: 13px;
            outline: none;
            transition: border-color .15s;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
        }

        /* mobile: stack length + filter */
        @media (max-width: 640px) {

            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                padding: 10px 12px;
            }

            .dataTables_wrapper .dataTables_filter {
                float: none !important;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 100%;
                margin: 4px 0 0;
            }
        }

        /* ── Modal: reduce padding on very small screens ── */
        @media (max-width: 480px) {
            #modalStock {
                padding: 8px !important;
            }

            #modalStock .px-6 {
                padding-left: 16px;
                padding-right: 16px;
            }
        }
    </style>

    {{-- Pass default company to JS --}}
    <input type="hidden" id="defaultCpny" value="{{ $usercpny2->cpny_id ?? '' }}">

    <div class="max-w-9xl mx-auto w-full p-2">

        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-x md:grid-cols-3 xl:grid-cols-3">

            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-slate-400 bg-slate-200/20 p-3 text-slate-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-slate-100 hover:shadow-md active:scale-95 dark:border-slate-500 dark:text-slate-300 dark:hover:bg-slate-700/30">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $countAll }}</p>
                    </div>
                </a>
            </button>


            {{-- Completed --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="A">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-600 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95 dark:border-green-500 dark:text-green-400 dark:hover:bg-green-500/20">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Active</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $countActive }}</p>
                    </div>
                </a>
            </button>

            {{-- Rejected --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="X">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-red-600 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-500/20">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Inactive</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $countInactive }}</p>
                    </div>
                </a>
            </button>

        </div>

        {{-- FILTER TOOLBAR --}}
        <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-[#0f172a]">

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">

                {{-- Type --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Type</label>
                    <select id="filter_type"
                        class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">All Types</option>
                        <option value="V">Voucher</option>
                        <option value="P">Product</option>
                    </select>
                </div>

                {{-- Doc ID --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Doc ID</label>
                    <select id="filter_doc_id" class="w-full" style="width:100%">
                        <option value="">All Doc IDs</option>
                    </select>
                </div>

                {{-- Category --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Category</label>
                    <select id="filter_category" style="width:100%">
                        <option value="">All Categories</option>
                        @foreach ($allCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Source --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Source</label>
                    <select id="filter_source" style="width:100%">
                        <option value="">All Sources</option>
                        @foreach ($allSources as $src)
                            <option value="{{ $src }}">{{ $src }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Product Name --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Product Name</label>
                    <input type="text" id="filter_product_name" placeholder="Search product name..."
                        class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500">
                </div>

            </div>

            {{-- Action buttons --}}
            <div class="mt-3 flex flex-wrap items-center justify-end gap-2">

                <button type="button" id="btn_reset_filter"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset
                </button>

                <button type="button" id="btn_apply_filter"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    Apply Filter
                </button>

                <button type="button" id="btn_export_filter"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-green-300 bg-green-50 px-4 text-sm font-medium text-green-700 transition hover:bg-green-100 dark:border-green-700 dark:bg-green-900/30 dark:text-green-300 dark:hover:bg-green-900/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 12l-4-4m4 4l4-4m5 8H3" />
                    </svg>
                    Export to Excel
                </button>

            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-3 lg:flex-row lg:items-center lg:justify-between dark:border-white/[0.06]">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Master Stock</h2>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('vpl.msproduct.setupwarehouse') }}" target="_blank"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]">
                        <i class="fa-solid fa-sliders text-xs"></i> Setup
                    </a>
                    <button id="btnNewStock"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">
                        <i class="fa-solid fa-plus text-xs"></i> New Stock
                    </button>
                </div>
            </div>

            <div class="relative overflow-x-auto">
                <table id="masterTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-left font-medium">Doc No</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Type</th>
                            <th class="px-4 py-3 text-left font-medium">Product Name</th>
                            <th class="px-4 py-3 text-left font-medium">Category</th>
                            <th class="px-4 py-3 text-left font-medium">Source (PT)</th>
                            <th class="px-4 py-3 text-left font-medium">Tenant / Event</th>
                            <th class="px-4 py-3 text-center font-medium">Status</th>
                            <th class="px-4 py-3 text-center font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================
     MODAL
     ============================================================ --}}
    <div id="modalStock" class="fixed inset-0 z-[9999] hidden items-center justify-center overflow-y-auto p-2 sm:p-4"
        style="backdrop-filter:blur(4px); background:rgba(15,23,42,.55)">

        <div
            class="relative my-4 flex w-full max-w-2xl flex-col border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">

            {{-- Header (sticky) --}}
            <div
                class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/95 px-4 py-3 sm:px-6 sm:py-4 dark:border-white/10 dark:bg-[#0f172a]/95">
                <div class="min-w-0 pr-4">
                    <h2 id="modalTitle" class="truncate text-base font-bold text-slate-900 sm:text-xl dark:text-white">
                        New Stock</h2>
                    <p id="modalSubtitle" class="mt-0.5 truncate text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                        Add a new voucher or product to master data</p>
                </div>
                <button id="btnCloseModal" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:hover:bg-white/[0.08]">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body --}}
            <form id="stockForm" class="space-y-3 bg-slate-50 p-3 sm:p-4 dark:bg-[#0b1220]">
                @csrf
                <input type="hidden" id="key_id" name="key_id">

                {{-- Section: Stock Identity --}}
                <div
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-3 py-2 sm:px-5 sm:py-2.5 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            Stock Identity</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-3 sm:p-5 md:grid-cols-2">

                        <div>
                            <label
                                class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Company</label>
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
                            <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Product
                                Type</label>
                            <select id="product_typex" name="product_type" class="select2 vpl-select w-full">
                                <option value="">Select Type</option>
                                <option value="V">Voucher</option>
                                <option value="P">Product</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Product
                                Name</label>
                            <input type="text" id="product_name" name="product_name" class="vpl-input"
                                placeholder="Enter product name...">
                        </div>

                        <div>
                            <label
                                class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Category</label>
                            <select id="categoryx" name="product_category" class="select2 vpl-select w-full">
                                <option value="">select type first</option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">UOM</label>
                            <select id="product_uom" name="product_uom" class="select2 vpl-select w-full">
                                <option value="">Select UOM</option>
                                @foreach ($uomList as $uom)
                                    <option value="{{ $uom }}">{{ $uom }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                {{-- Section: Source Information --}}
                <div
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-3 py-2 sm:px-5 sm:py-2.5 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            Source Information</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-3 sm:p-5 md:grid-cols-3">

                        <div>
                            <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama
                                PT</label>
                            <input type="text" id="product_source_company" name="product_source_company"
                                class="vpl-input" placeholder="Nama perusahaan...">
                        </div>

                        <div>
                            <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama
                                Tenant / Event</label>
                            <input type="text" id="product_source_tenant" name="product_source_tenant"
                                class="vpl-input" placeholder="Tenant or event name...">
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Value</label>
                            <input type="text" id="product_value" name="product_value" class="vpl-input"
                                value="0" placeholder="0">
                        </div>

                    </div>
                </div>

                {{-- Section: Additional --}}
                <div
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-3 py-2 sm:px-5 sm:py-2.5 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            Additional</h3>
                    </div>
                    <div class="space-y-4 p-3 sm:p-5">

                        {{-- Remarks full width --}}
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Remarks</label>
                            <textarea id="product_remark" name="product_remark" rows="3" class="vpl-textarea"
                                placeholder="Optional remarks..."></textarea>
                        </div>

                        {{-- Check Expired Date as inline row --}}
                        <div
                            class="dark:border-white/05 flex items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 dark:bg-white/[0.02]">
                            <input type="hidden" name="product_check_exp" value="0">
                            <input type="checkbox" id="product_check_exp" name="product_check_exp" value="1"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600">
                            <div>
                                <label for="product_check_exp"
                                    class="text-sm font-medium text-slate-700 dark:text-slate-200">Check Expired
                                    Date</label>
                                <p class="text-xs text-slate-400">Enable expiry tracking for this stock item</p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Error --}}
                <div id="formError"
                    class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-300">
                </div>

            </form>

            {{-- Footer (sticky) --}}
            <div
                class="sticky bottom-0 z-20 flex items-center justify-end gap-3 border-t border-slate-200 bg-white/95 px-4 py-3 sm:px-6 dark:border-white/10 dark:bg-[#0f172a]/95">
                <button id="btnCancelModal" type="button"
                    class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]">
                    Cancel
                </button>
                <button id="btnSave" type="button"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-6 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:opacity-50 dark:bg-blue-600 dark:hover:bg-blue-500">
                    <i class="fa-solid fa-save text-xs"></i> Save
                </button>
            </div>

        </div>
    </div>

    {{-- Global floating action menu (fixed-position avoids table overflow clipping) --}}
    <div id="actionMenu"
        class="fixed z-[9999] hidden w-44 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl dark:border-white/10 dark:bg-[#0f172a]"
        style="min-width:160px">
        <button id="actionMenuEdit"
            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/[0.05]">
            <i class="fa-solid fa-pen-to-square w-4 text-amber-500"></i> Edit
        </button>
        <div class="border-t border-slate-100 dark:border-white/10"></div>
        <button id="actionMenuToggle"
            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm transition hover:bg-slate-50 dark:hover:bg-white/[0.05]">
            <i id="actionMenuToggleIcon" class="fa-solid fa-ban w-4"></i>
            <span id="actionMenuToggleLabel">Deactivate</span>
        </button>
    </div>

    {{-- VIEW PRODUCT MODAL --}}
    <div id="viewProductModal" class="fixed inset-0 z-[9998] hidden items-center justify-center p-4">

        <div class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70"></div>

        <div class="modal-panel relative z-10 flex max-h-[92vh] w-full max-w-5xl translate-y-4 scale-[0.98] flex-col overflow-hidden rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <input type="hidden" id="viewModalProductDbId">

            {{-- Header --}}
            <div class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-6 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Master Stock</div>
                    <div class="mt-1 flex items-center gap-2.5">
                        <h2 id="viewModal_productId" class="text-base font-bold text-slate-900 dark:text-white">-</h2>
                        <div id="viewModal_status"></div>
                    </div>
                </div>
                <button id="btnCloseViewModal" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:hover:bg-white/[0.08]">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto bg-slate-50 p-4 dark:bg-[#0b1220]">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

                    {{-- Left column --}}
                    <div class="space-y-4">

                        {{-- Tenant Information --}}
                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                            <div class="border-b border-slate-200 px-5 py-2.5 dark:border-white/10">
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Tenant Information</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-4 p-5">
                                <div>
                                    <div class="text-xs text-slate-500">Site</div>
                                    <div id="viewModal_cpnyid" class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">-</div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-500">Nama PT</div>
                                    <div id="viewModal_sourcePT" class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">-</div>
                                </div>
                                <div class="col-span-2">
                                    <div class="text-xs text-slate-500">Nama Tenant / Event</div>
                                    <div id="viewModal_tenant" class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">-</div>
                                </div>
                            </div>
                        </div>

                        {{-- Item Information --}}
                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                            <div class="border-b border-slate-200 px-5 py-2.5 dark:border-white/10">
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Item Information</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-4 p-5">
                                <div class="col-span-2">
                                    <div class="text-xs text-slate-500">Product Name</div>
                                    <div id="viewModal_productName" class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">-</div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-500">Type</div>
                                    <div id="viewModal_type" class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">-</div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-500">Category</div>
                                    <div id="viewModal_category" class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">-</div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-500">Product Source</div>
                                    <div id="viewModal_sourceType" class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">-</div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-500">UOM</div>
                                    <div id="viewModal_uom" class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">-</div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-500">Value</div>
                                    <div id="viewModal_value" class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">-</div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-500">Check Expired Date</div>
                                    <div id="viewModal_checkExp" class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">-</div>
                                </div>
                                <div class="col-span-2">
                                    <div class="text-xs text-slate-500">Remarks</div>
                                    <div id="viewModal_remarks" class="mt-1 text-sm leading-relaxed text-slate-700 dark:text-slate-300">-</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Right column --}}
                    <div class="space-y-4">

                        {{-- Stock Detail --}}
                        <div class="overflow-hidden rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-500/20 dark:bg-blue-500/10">
                            <div class="border-b border-blue-100 px-5 py-2.5 dark:border-blue-500/20">
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">Stock Detail</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="border-b border-blue-100 dark:border-blue-500/20">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-300">Expired Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-300">Warehouse</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-300">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viewStockBody" class="divide-y divide-blue-100 dark:divide-blue-500/20"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Attachments --}}
                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                            <div class="border-b border-slate-200 px-5 py-2.5 dark:border-white/10">
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Attachments</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="border-b border-slate-100 bg-slate-50/70 dark:border-white/[0.06] dark:bg-white/[0.02]">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Filename</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Created By</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viewAttachBody" class="divide-y divide-slate-100 dark:divide-white/[0.04]"></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="sticky bottom-0 z-20 flex items-center justify-between border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
                <button id="btnCloseViewModalFooter" type="button"
                    class="text-sm text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">
                    Close
                </button>
                <button id="btnViewModalEdit" type="button"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">
                    <i class="fa-solid fa-pen-to-square text-xs"></i> Edit
                </button>
            </div>

        </div>
    </div>

    {{-- JS modules --}}
    <script src="{{ asset('assets/js/voucher-product/core.js') }}"></script>
    <script src="{{ asset('assets/js/voucher-product/helper.js') }}"></script>
    <script src="{{ asset('assets/js/voucher-product/modal.js') }}"></script>
    <script src="{{ asset('assets/js/voucher-product/datalist.js') }}"></script>
    <script src="{{ asset('assets/js/voucher-product/form.js') }}"></script>
    <script src="{{ asset('assets/js/voucher-product/view-modal.js') }}"></script>
    <script src="{{ asset('assets/js/voucher-product/init.js') }}"></script>

</x-app-layout>
