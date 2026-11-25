<x-app-layout>
    <style>
        /* ---- Status filter cards: same look + selected state as the shared status cards ---- */
        .sppb-cards .status-filter .status-card {
            --c: 71, 85, 105;
            padding: .75rem !important; font-size: .875rem; font-weight: 500; border-radius: .6rem;
            background: rgba(var(--c), .09) !important; border: 1px solid rgba(var(--c), .38) !important; color: rgb(var(--c)) !important;
            transition: transform .25s ease, box-shadow .25s ease, background-color .25s ease;
        }
        .sppb-cards .status-filter[data-status="P"] .status-card { --c: 234, 88, 12; }
        .sppb-cards .status-filter[data-status="H"] .status-card { --c: 219, 39, 119; }
        .sppb-cards .status-filter[data-status="R"] .status-card { --c: 220, 38, 38; }
        .sppb-cards .status-filter[data-status="D"] .status-card { --c: 217, 119, 6; }
        .sppb-cards .status-filter[data-status="C"] .status-card { --c: 22, 163, 74; }
        .sppb-cards .status-filter[data-mode="all"] .status-card { --c: 147, 51, 234; }
        .sppb-cards .status-filter[data-mode="wo"] .status-card { --c: 2, 132, 199; }
        .sppb-cards .status-filter:hover .status-card { background: rgba(var(--c), .16) !important; box-shadow: 0 6px 16px -6px rgba(var(--c), .45); }
        .sppb-cards .status-filter.active .status-card {
            background: rgba(var(--c), .18) !important; border: 2px solid rgb(var(--c)) !important;
            padding: calc(.75rem - 1px) !important; font-weight: 700; transform: translateY(-1px);
            box-shadow: 0 4px 14px -4px rgba(var(--c), .55);
        }
        .sppb-cards .status-filter.active .status-card p { font-weight: 700; }

        /* ---- Prettier DataTables toolbar/footer (scoped: add class "dt-pretty" to a card to use) ---- */
        .dt-pretty .dt-toolbar {
            padding: 14px 18px; margin: 0 !important; gap: 10px !important;
            border-bottom: 1px solid #f1f5f9;
        }
        .dt-pretty .dataTables_length, .dt-pretty .dataTables_filter { padding: 0 !important; }
        .dt-pretty .dataTables_length label {
            display: flex; align-items: center; gap: 4px; margin: 0; height: 38px; padding: 0 14px 0 16px;
            font-size: 13px; color: #6b7280; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
            transition: border-color .15s, box-shadow .15s;
        }
        .dt-pretty .dataTables_length label:hover { border-color: #cbd5e1; }
        .dt-pretty .dataTables_length label:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
        .dt-pretty .dataTables_length select {
            -webkit-appearance: none; appearance: none; height: 30px; min-width: 0;
            padding: 0 24px 0 8px !important; font-size: 14px; font-weight: 700; color: #2563eb;
            background-color: transparent !important; border: 0 !important; border-radius: 8px !important;
            cursor: pointer; outline: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important; background-position: right 4px center !important; background-size: 16px !important;
        }

        /* Export buttons: soft tinted outline, fill on hover */
        .dt-pretty .dt-buttons { margin: 0 0 0 6px !important; gap: 8px; }
        .dt-pretty .dt-button {
            height: 38px; padding: 0 16px !important; border-radius: 10px !important;
            border: 1px solid transparent !important; box-shadow: none !important;
            font-size: 13px !important; font-weight: 600 !important; gap: 8px !important;
        }
        .dt-pretty .dt-button.buttons-excel { background: #f0fdf4 !important; color: #15803d !important; border-color: #bbf7d0 !important; }
        .dt-pretty .dt-button.buttons-excel:hover { background: #16a34a !important; color: #fff !important; border-color: #16a34a !important; }
        .dt-pretty .dt-button.buttons-csv { background: #f0f9ff !important; color: #0369a1 !important; border-color: #bae6fd !important; }
        .dt-pretty .dt-button.buttons-csv:hover { background: #0284c7 !important; color: #fff !important; border-color: #0284c7 !important; }

        /* Search: soft pill, expands + icon turns blue on focus */
        .dt-pretty .dataTables_wrapper .dataTables_filter input {
            height: 38px !important; width: 18rem !important; padding: 0 16px 0 42px !important;
            border-radius: 9999px !important; font-size: 13px !important;
            background-color: #f8fafc !important; border: 1px solid #e2e8f0 !important; box-shadow: none !important;
            background-position: 15px center !important; background-size: 16px !important;
            transition: width .25s ease, background-color .15s, border-color .15s, box-shadow .15s !important;
        }
        .dt-pretty .dataTables_wrapper .dataTables_filter input::placeholder { color: #94a3b8; }
        .dt-pretty .dataTables_wrapper .dataTables_filter input:hover { background-color: #fff !important; border-color: #cbd5e1 !important; }
        .dt-pretty .dataTables_wrapper .dataTables_filter input:focus {
            width: 24rem !important; background-color: #fff !important; border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .14) !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%233b82f6' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='m20 20-3.5-3.5'/%3E%3C/svg%3E") !important;
        }
        .dark .dt-pretty .dataTables_wrapper .dataTables_filter input { background-color: rgba(255, 255, 255, .04) !important; border-color: rgba(255, 255, 255, .12) !important; }
        .dark .dt-pretty .dataTables_wrapper .dataTables_filter input:focus { background-color: rgba(255, 255, 255, .07) !important; border-color: #60a5fa !important; box-shadow: 0 0 0 3px rgba(96, 165, 250, .2) !important; }
        .dt-pretty .dataTables_info { padding: 14px 18px !important; font-size: 12px; color: #6b7280; }
        .dt-pretty .dataTables_paginate { padding: 8px 18px !important; }
        .dt-pretty .dataTables_wrapper > .dataTables_info,
        .dt-pretty .dataTables_wrapper > .dataTables_paginate { border-top: 1px solid #f1f5f9; }

        .dark .dt-pretty .dt-toolbar { border-bottom-color: rgba(255, 255, 255, .06); }
        .dark .dt-pretty .dataTables_length label { color: #9ca3af; background: rgba(255, 255, 255, .04); border-color: rgba(255, 255, 255, .12); }
        .dark .dt-pretty .dataTables_length select { color: #93c5fd; }
        .dark .dt-pretty .dataTables_length select option { color: #111827; }
        .dark .dt-pretty .dt-button.buttons-excel { background: rgba(22, 163, 74, .12) !important; color: #86efac !important; border-color: rgba(134, 239, 172, .25) !important; }
        .dark .dt-pretty .dt-button.buttons-csv { background: rgba(2, 132, 199, .12) !important; color: #7dd3fc !important; border-color: rgba(125, 211, 252, .25) !important; }
        .dark .dt-pretty .dataTables_wrapper > .dataTables_info,
        .dark .dt-pretty .dataTables_wrapper > .dataTables_paginate { border-top-color: rgba(255, 255, 255, .06); }
        @media (max-width: 640px) {
            .dt-pretty .dt-toolbar { flex-wrap: wrap; }
            .dt-pretty .dataTables_filter { margin-left: 0 !important; width: 100%; }
            .dt-pretty .dataTables_filter input { width: 100% !important; }
        }
    </style>
    @php
        $currentPage = Route::currentRouteName() == 'sppbs' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $hasAllList = auth()->user()->hasRole('COSTCTRLACCESS') || auth()->user()->hasRole('FAACCESS') || auth()->user()->hasRole('FINACCESS');
            $hasWoAccess = auth()->user()->hasRole('WHSACCESS');

            $xlCols = 6; // base cards

            if ($hasAllList) {
                $xlCols++;
            }
            if ($hasWoAccess) {
                $xlCols++;
            }
        @endphp

        <div class="sppb-cards xl:grid-cols-{{ $xlCols }} grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">


            {{-- All Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📦</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                    </div>
                </a>
            </button>

            {{-- On Progress Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="P">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">On Progress</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $onProgress }}</p>
                    </div>
                </a>
            </button>

            {{-- Draft Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="H">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-pink-700 bg-pink-200/20 p-3 text-pink-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-pink-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📝</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Draft</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $draft }}</p>
                    </div>
                </a>
            </button>

            {{-- Reject Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="R">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Reject</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $reject }}</p>
                    </div>
                </a>
            </button>

            {{-- Revise Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="D">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Revise</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                    </div>
                </a>
            </button>

            {{-- Completed Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="C">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Completed</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                    </div>
                </a>
            </button>
            {{-- SPPB All List --}}
            {{-- <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-mode="all">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📊</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">SPPB All List</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">
                            {{ $allListCount }}
                        </p>

                    </div>
                </a>
            </button> --}}
            @if (auth()->user()->hasRole('COSTCTRLACCESS') || auth()->user()->hasRole('WHSACCESS') || auth()->user()->hasRole('FAACCESS') || auth()->user()->hasRole('FINACCESS'))
                {{-- SPPB All List --}}
                <button type="button" class="text-left">
                    <a href="#" class="status-filter group block h-full" data-mode="all">
                        <div
                            class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📊</div>

                            <div class="flex min-w-0 flex-grow flex-col leading-tight">
                                <p class="break-words text-sm font-medium">SPPB All List</p>
                            </div>

                            <p class="shrink-0 text-base font-bold">
                                {{ $allListCount }}
                            </p>
                        </div>
                    </a>
                </button>
            @endif

            @if (auth()->user()->hasRole('WHSACCESS'))
                <button type="button" class="text-left">
                    <a href="#" class="status-filter group block h-full" data-mode="wo">
                        <div
                            class="status-card flex h-full items-center gap-3 rounded-lg border border-sky-700 bg-sky-200/20 p-3 text-sky-600 hover:-translate-y-1 hover:bg-sky-100 hover:shadow-md active:scale-95">

                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🛠️</div>

                            <div class="flex min-w-0 flex-grow flex-col leading-tight">
                                <p class="break-words text-sm font-medium">WO → SPPB</p>
                            </div>

                            <p class="shrink-0 text-base font-bold">
                                {{ $woSppbCount ?? 0 }}
                            </p>
                        </div>
                    </a>
                </button>
            @endif
        </div>

        <div class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div class="flex flex-col gap-4 rounded-t-xl border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white px-5 py-3.5 dark:border-white/[0.06] dark:from-white/[0.03] dark:to-transparent lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                    </span>
                    <div>
                        <h2 id="pageTitle" class="text-base font-bold tracking-tight text-gray-800 dark:text-gray-100">
                            Request SPPB
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Create, track and manage your requests</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    {{-- FILTER SECTION (ONLY FOR ALL MODE) --}}
                    <div id="allFilters" class="hidden items-center gap-2 flex">

                        {{-- Status Filter --}}
                        <select id="filterStatus"
                            class="h-10 cursor-pointer rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 shadow-sm transition hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-white/[0.12] dark:bg-[#0f172a] dark:text-gray-300">
                            <option value="">All Status</option>
                            <option value="P">On Progress</option>
                            <option value="C">Completed</option>
                        </select>

                        {{-- Company Filter --}}
                        <select id="filterCompany"
                            class="h-10 cursor-pointer rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 shadow-sm transition hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-white/[0.12] dark:bg-[#0f172a] dark:text-gray-300">
                            <option value="">All Company</option>
                        </select>

                        {{-- Department Filter --}}
                        <select id="filterDepartment"
                            class="h-10 cursor-pointer rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 shadow-sm transition hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-white/[0.12] dark:bg-[#0f172a] dark:text-gray-300">
                            <option value="">All Department</option>
                        </select>

                    </div>

                    <a id="createBtn" href="{{ url('/createsppbs') }}"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-blue-500 to-blue-600 px-5 text-sm font-semibold text-white shadow-md shadow-blue-600/25 ring-1 ring-inset ring-white/10 transition hover:-translate-y-px hover:from-blue-500 hover:to-blue-700 hover:shadow-lg hover:shadow-blue-600/30 focus:outline-none focus:ring-2 focus:ring-blue-300 active:translate-y-0">
                        <i class="fa-solid fa-plus text-xs"></i>Create
                    </a>
                </div>
            </div>

            <div class="relative overflow-hidden">
                <table id="sppbsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">Doc ID</th>
                            <th id="thWoId" class="hidden px-4 py-3 text-left font-medium">WO ID</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">Request Type</th>
                            <th class="px-4 py-3 text-left font-medium">Description</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Tracking Modal -->
        <!-- ================== TRACKING DETAIL MODAL (TABS) ================== -->
        <div id="trackingModal" class="fixed inset-0 z-50 hidden bg-black/50">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div
                    class="max-h-[90vh] w-full max-w-7xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800">

                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                            Tracking Detail <span id="trackDoc" class="font-bold text-indigo-600"></span>
                        </h3>
                        <button type="button" id="closeTracking"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                            ✕
                        </button>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b border-gray-200 px-4 dark:border-gray-700">
                        <div class="flex gap-2 overflow-x-auto py-2" id="trackTabs">
                            <button class="track-tab active" data-tab="tab-sppb">SPPB</button>
                            <button class="track-tab" data-tab="tab-cs">CS</button>
                            <button class="track-tab" data-tab="tab-po">PO</button>
                            <button class="track-tab" data-tab="tab-receipt">Receipt</button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="max-h-[calc(90vh-110px)] overflow-y-auto p-4">
                        <div id="tlLoading"
                            class="hidden items-center gap-2 text-sm text-gray-500 dark:text-gray-300">
                            <span
                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent dark:border-gray-700"></span>
                            Loading...
                        </div>

                        <!-- SPPB -->
                        <div id="tab-sppb" class="track-pane">
                            <div id="sppbHeaderBox"></div>
                            <div class="mt-3" id="sppbDetailBox"></div>
                        </div>

                        <!-- CS -->
                        <div id="tab-cs" class="track-pane hidden">
                            <div class="mb-2">
                                <label class="text-xs text-gray-500 dark:text-gray-400">Select CS</label>
                                <select id="selCs"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></select>
                            </div>
                            <div id="csHeaderBox"></div>
                            <div class="mt-3" id="csDetailBox"></div>
                        </div>

                        <!-- PO -->
                        <div id="tab-po" class="track-pane hidden">
                            <div class="mb-2">
                                <label class="text-xs text-gray-500 dark:text-gray-400">Select PO</label>
                                <select id="selPo"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></select>
                            </div>
                            <div id="poHeaderBox"></div>
                            <div class="mt-3" id="poDetailBox"></div>
                        </div>

                        <!-- Receipt -->
                        <div id="tab-receipt" class="track-pane hidden">
                            <div class="mb-2">
                                <label class="text-xs text-gray-500 dark:text-gray-400">Select Receipt</label>
                                <select id="selReceipt"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></select>
                            </div>
                            <div id="receiptHeaderBox"></div>
                            <div class="mt-3" id="receiptDetailBox"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>



    </div>
    <script>
        function renderTimeline(steps = []) {
            const list = document.getElementById('tlList');
            if (!list) return;

            if (!Array.isArray(steps) || steps.length === 0) {
                // list.innerHTML = `<p class="text-sm text-gray-500 dark:text-gray-400">No tracking history found.</p>`;
                list.innerHTML = `<li class="text-sm text-gray-500 dark:text-gray-400">No tracking history found.</li>`;
                return;
            }

            const MAP = {
                C: {
                    label: 'Completed',
                    colorDot: 'bg-green-600',
                    colorBorder: 'border-green-600',
                    colorTitle: 'text-green-700'
                },
                P: {
                    label: 'Waiting approval / in progress',
                    colorDot: 'bg-yellow-500',
                    colorBorder: 'border-yellow-500',
                    colorTitle: 'text-yellow-700'
                },
                R: {
                    label: 'Rejected',
                    colorDot: 'bg-red-600',
                    colorBorder: 'border-red-600',
                    colorTitle: 'text-red-700'
                },
                D: {
                    label: 'Revise',
                    colorDot: 'bg-blue-600',
                    colorBorder: 'border-blue-600',
                    colorTitle: 'text-blue-700'
                },
                _: {
                    label: '',
                    colorDot: 'bg-gray-400',
                    colorBorder: 'border-gray-400',
                    colorTitle: 'text-gray-700'
                },
            };

            list.innerHTML = steps.map((s, i) => {
                const st = String(s.status || '').toUpperCase();
                const C = MAP[st] || MAP._;
                const title = (s.title && String(s.title).trim()) || 'SPPB';

                const when = (s.at && String(s.at).trim()) || '';
                const by = (s.by && String(s.by).trim()) || '';
                const statusText = (s.status_label && String(s.status_label).trim()) || C.label;

                // tampilkan jadi multi-line: status, nama, waktu
                let detailHtml = '';
                if (statusText) detailHtml += `<p class="text-sm text-gray-500 dark:text-gray-400">${statusText}</p>`;
                if (by) detailHtml += `<p class="text-sm text-gray-500 dark:text-gray-400">${by}</p>`;
                if (when) detailHtml += `<p class="text-sm text-gray-500 dark:text-gray-400">${when}</p>`;

                const isLast = i === steps.length - 1;
                const connector = !isLast ?
                    'after:absolute after:top-1/2 after:left-7 after:h-0.5 after:w-[calc(100%-1.75rem)] after:-translate-y-1/2 after:bg-gray-300 dark:after:bg-gray-600' :
                    '';

                return `
                        <li class="relative mr-12 flex shrink-0 snap-start pr-12 last:mr-0 last:pr-0 ${connector}">
                            <div class="flex items-center">
                            <div class="grid h-6 w-6 place-items-center rounded-full border-2 ${C.colorBorder} bg-white dark:bg-gray-800">
                                <div class="h-2 w-2 rounded-full ${C.colorDot}"></div>
                            </div>
                            <div class="ml-3">
                                <p class=" text-sm  font-semibold ${C.colorTitle}">${title}</p>
                                ${detailHtml}
                            </div>
                            </div>
                        </li>
                        `;
            }).join('');
        }
    </script>

    <script>
        function fmt2(val) {
            if (val === null || val === undefined || val === '') return '0.00';
            const num = Number(val);
            if (isNaN(num)) return '0.00';
            return num.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    </script>

    <script>
        /* =========================================================
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            TRACKING DETAIL MODAL (TABS) - CLEAN VERSION
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ========================================================= */

        (function() {
                // ---------- Modal open/close ----------
                function openTrackingModal(docText) {
                    document.getElementById('trackDoc').textContent = docText ? `(${docText})` : '';
                    const modal = document.getElementById('trackingModal');
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }

                function closeTrackingModal() {
                    document.getElementById('trackingModal')?.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }

                document.getElementById('closeTracking')?.addEventListener('click', closeTrackingModal);
                document.getElementById('trackingModal')?.addEventListener('click', (e) => {
                    if (e.target.id === 'trackingModal') closeTrackingModal();
                });

                // ---------- Tabs ----------
                (function() {
                    const tabs = document.getElementById('trackTabs');
                    if (!tabs) return;

                    tabs.addEventListener('click', (e) => {
                        const btn = e.target.closest('.track-tab');
                        if (!btn) return;

                        document.querySelectorAll('.track-tab').forEach(x => x.classList.remove('active'));
                        btn.classList.add('active');

                        const target = btn.dataset.tab;
                        document.querySelectorAll('.track-pane').forEach(p => p.classList.add('hidden'));
                        document.getElementById(target)?.classList.remove('hidden');
                    });
                })();

                function resetToSppbTab() {
                    document.querySelectorAll('.track-tab').forEach(x => x.classList.remove('active'));
                    document.querySelector('.track-tab[data-tab="tab-sppb"]')?.classList.add('active');
                    document.querySelectorAll('.track-pane').forEach(p => p.classList.add('hidden'));
                    document.getElementById('tab-sppb')?.classList.remove('hidden');
                }

                // ---------- Utilities ----------
                function esc(s) {
                    return String(s ?? '')
                        .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;').replaceAll("'", '&#039;');
                }

                function setLoading(on) {
                    const el = document.getElementById('tlLoading');
                    if (!el) return;
                    el.classList.toggle('hidden', !on);
                    el.classList.toggle('flex', on);
                }

                function statusLabel(st) {
                    st = String(st || '').toUpperCase();

                    const map = {
                        'C': {
                            text: 'Completed',
                            cls: 'bg-green-100 text-green-700'
                        },
                        'P': {
                            text: 'On Progress',
                            cls: 'bg-yellow-100 text-yellow-700'
                        },
                        'R': {
                            text: 'Rejected',
                            cls: 'bg-red-100 text-red-700'
                        },
                        'D': {
                            text: 'Revise',
                            cls: 'bg-blue-100 text-blue-700'
                        },
                        'H': {
                            text: 'Draft',
                            cls: 'bg-pink-100 text-pink-700'
                        }
                    };

                    const it = map[st] || {
                        text: st || '-',
                        cls: 'bg-gray-100 text-gray-700'
                    };

                    return `
                <span class="inline-block rounded px-2 py-0.5 text-xs font-semibold ${it.cls}">
                    ${it.text}
                </span>
            `;
                }

                function statusLabel2(st) {
                    st = String(st || '').toUpperCase();
                    switch (st) {
                        case 'P':
                            return 'On Progress';
                        case 'C':
                            return 'Completed';
                        case 'R':
                            return 'Rejected';
                        case 'D':
                            return 'Revise';
                        case 'H':
                            return 'Draft';
                        default:
                            return st || '-';
                    }
                }

                function badgeApproved(isApproved) {
                    if (isApproved) {
                        return `<span class="inline-block rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">APPROVED</span>`;
                    }
                    return `<span class="inline-block rounded bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700">IN PROGRESS</span>`;
                }

                function resetBoxes() {
                    [
                        'sppbHeaderBox', 'csHeaderBox', 'poHeaderBox', 'receiptHeaderBox',
                        'sppbDetailBox', 'csDetailBox', 'poDetailBox', 'receiptDetailBox'
                    ].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.innerHTML = '';
                    });
                }

                function renderHeader(boxId, header, title) {
                    const box = document.getElementById(boxId);
                    if (!box) return;

                    if (!header) {
                        box.innerHTML = `
                        <div class="rounded-lg border border-gray-200 p-3 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-300">
                            ${esc(title)} not created yet.
                        </div>`;
                        return;
                    }

                    // ✅ HARUS DI DALAM renderHeader (biar scope benar)
                    // const la = header.last_approval || null;
                    const approvals = header.approval_list || [];
                    // console.log('APPROVAL LIST:', approvals);

                    // let lastApprovalHtml = '';
                    // if (la) {
                    //     const st = String(la.status || '').toUpperCase();
                    //     const stText = st === 'P' ? 'Pending Approval' : (st === 'A' ? 'Approved' : st);

                    //     const who = (la.name ? esc(la.name) : '') || esc(la.username || '-');
                    //     const lvl = (la.aprv_leveling !== undefined && la.aprv_leveling !== null) ?
                    //         `Lvl ${esc(la.aprv_leveling)}` :
                    //         '';
                    //     const dtb = la.date_before ? esc(la.date_before) : '';
                    //     const dta = la.date_after ? esc(la.date_after) : '';

                    //     lastApprovalHtml = `
                //         <div class="sm:col-span-2 mt-2 rounded-lg border border-indigo-200 bg-indigo-50 p-3 text-sm dark:border-indigo-700/40 dark:bg-indigo-900/20">
                //             <div class="flex items-center justify-between">
                //                 <div class="font-semibold text-indigo-700 dark:text-indigo-300">Last Approval</div>
                //                 <div class="text-xs text-indigo-700/80 dark:text-indigo-300/80">
                //                     ${esc(stText)} ${lvl ? `• ${lvl}` : ''}
                //                 </div>
                //             </div>
                //             <div class="mt-1 text-gray-700 dark:text-gray-200">
                //                 <div><span class="text-gray-500 dark:text-gray-400">By:</span> <span class="font-semibold">${who}</span></div>
                //                 ${dtb ? `<div><span class="text-gray-500 dark:text-gray-400">Start:</span> ${dtb}</div>` : ''}
                //                 ${dta ? `<div><span class="text-gray-500 dark:text-gray-400">Finish:</span> ${dta}</div>` : ''}
                //             </div>
                //         </div>
                //     `;
                    // }
                    let approvalHtml = '';


                    let imBudgetHtml = '';
                    if (header.flag_imbudget && header.imbudgetid && header.status_imbudget !== 'C') {
                        imBudgetHtml = `
                            <div class="mt-3 rounded-lg border border-yellow-200 bg-yellow-50 p-3 text-sm dark:border-yellow-700/40 dark:bg-yellow-900/20">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                                        <div class="font-semibold text-yellow-700 dark:text-yellow-400">Waiting IM Budget</div>
                                    </div>
                                    <div class="text-xs font-semibold text-yellow-600 dark:text-yellow-500">${esc(header.imbudgetid)}</div>
                                </div>
                            </div>
                        `;
                    }

                    if (approvals.length > 0) {

                        approvalHtml = `
                            <div class="sm:col-span-2 mt-3 rounded-lg border border-indigo-200 bg-indigo-50 p-3 text-sm">

                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-semibold text-indigo-700">
                                        Approval Flow
                                    </div>
                                    <div class="text-xs text-indigo-600">
                                        ${approvals.filter(a => String(a.status).toUpperCase() === 'A').length}/${approvals.length} Approved
                                    </div>
                                </div>

                                <div class="max-h-64 overflow-y-auto pr-1 space-y-2">
        ${approvals.map(a => {

            const st = String(a.status || '').toUpperCase();

            let badge = '';
            let color = '';
            let dot = '';

            if (st === 'A') {
                badge = 'APPROVED';
                color = 'text-green-700';
                dot = 'bg-green-500';
            } else if (st === 'P') {
                badge = 'WAITING APPROVAL';
                color = 'text-yellow-700 font-semibold';
                dot = 'bg-yellow-500';
            } else if (st === 'R') {
                badge = 'REJECTED';
                color = 'text-red-700';
                dot = 'bg-red-500';
            } else if (st === 'D') {
                badge = 'REVISE';
                color = 'text-blue-700';
                dot = 'bg-blue-500';
            } else {
                badge = 'WAITING';
                color = 'text-gray-500';
                dot = 'bg-gray-400';
            }

         return `
<div class="flex items-start gap-3 border-b pb-2 last:border-0">

    <div class="mt-1 h-2 w-2 rounded-full ${dot}"></div>

    <div class="flex-1">

        <div class="flex justify-between items-center">
            <div class="font-semibold ${color}">
                Lvl ${a.level} - ${esc(a.name || a.username || '-')}
            </div>

            <div class="text-[10px] font-semibold px-2 py-0.5 rounded bg-white border dark:bg-gray-800">
                ${badge}
            </div>
        </div>

        <div class="text-xs text-gray-500 dark:text-gray-400">
            ${a.date_before || ''}
            ${a.date_after ? ' → ' + a.date_after : ''}
        </div>

    </div>

</div>
`;
        }).join('')}
    </div>
</div>
`;
                    }


                        box.innerHTML = `
<div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">

    <div class="flex items-center justify-between gap-3">
        <div>
            <div class="text-sm font-semibold text-gray-800 dark:text-white">
                ${esc(title)}: ${esc(header.doc)}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-300">
                ${esc(header.date || '')}
            </div>
        </div>

        <div class="flex items-center gap-2">
            ${title === 'CS' && header.cs_hash ? `<a href="/showcs/${encodeURIComponent(header.cs_hash)}" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg border border-indigo-200 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-indigo-700/40 dark:text-indigo-300 dark:hover:bg-indigo-900/20">Open This CS ↗</a>` : ''}
            ${statusLabel(header.status)}
        </div>
    </div>

    <div class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">

        <div>
            <span class="text-gray-500 dark:text-gray-400">Company:</span>
            <span class="font-semibold text-gray-800 dark:text-white">
                ${esc(header.cpny_id || '-')}
            </span>
        </div>

        <div>
            <span class="text-gray-500 dark:text-gray-400">Department:</span>
            <span class="font-semibold text-gray-800 dark:text-white">
                ${esc(header.department_id || '-')}
            </span>
        </div>

        <div>
            <span class="text-gray-500 dark:text-gray-400">Created By:</span>
            <span class="font-semibold text-gray-800 dark:text-white">
                ${esc(header.created_by || '-')}
            </span>
        </div>

        ${
            header.vendorname !== undefined
                ? `
        <div class="sm:col-span-2">
            <span class="text-gray-500 dark:text-gray-400">Vendor:</span>
            <span class="font-semibold text-gray-800 dark:text-white">
                ${esc(header.vendorname || '-')}
            </span>
        </div>`
                : ''
        }

        ${
            header.keperluan !== undefined
                ? `
        <div class="sm:col-span-2">
            <span class="text-gray-500 dark:text-gray-400">Keperluan:</span>
            <span class="font-semibold text-gray-800 dark:text-white">
                ${esc(header.keperluan || '-')}
            </span>
        </div>`
                : ''
        }

    </div>

    ${imBudgetHtml}
    ${approvalHtml}

</div>
`;
                    }

                    function renderDetailCs(rows) {
                        if (!Array.isArray(rows) || rows.length === 0)
                            return `<div class="text-sm text-gray-500 dark:text-gray-400">No detail.</div>`;

                        const trs = rows.map(r => `
                                    <tr class="border-b dark:border-gray-700">
                                    <td class="px-3 py-2">${esc(r.inventoryid)}</td>
                                    <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
                                    <td class="px-3 py-2 text-right">${fmt2(r.qty)}</td>
                                    <td class="px-3 py-2">${esc(r.uom)}</td>
                                    <td class="px-3 py-2">${esc(r.vendorname_selected || '-')}</td>
                                    </tr>
                                `).join('');

                        return `
                                    <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-700/30">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Inventory</th>
                                            <th class="px-3 py-2 text-left">Description</th>
                                            <th class="px-3 py-2 text-right">Qty</th>
                                            <th class="px-3 py-2 text-left">UOM</th>
                                            <th class="px-3 py-2 text-left">Selected Vendor</th>
                                        </tr>
                                        </thead>
                                        <tbody>${trs}</tbody>
                                    </table>
                                    </div>`;
                    }


                    function renderDetailPo(rows) {
                        if (!Array.isArray(rows) || rows.length === 0)
                            return `<div class="text-sm text-gray-500 dark:text-gray-400">No detail.</div>`;
                        const trs = rows.map(r => `
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-3 py-2">${esc(r.inventoryid)}</td>
                                    <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
                                    <td class="px-3 py-2 text-right">${fmt2(r.qty)}</td>
                                    <td class="px-3 py-2">${esc(r.uom)}</td>
                                </tr>`).join('');
                        return `
                                <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
                                    <table class="w-full text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700/30">
                                        <tr>
                                        <th class="px-3 py-2 text-left">Inventory</th>
                                        <th class="px-3 py-2 text-left">Description</th>
                                        <th class="px-3 py-2 text-right">Qty</th>
                                        <th class="px-3 py-2 text-left">UOM</th>
                                        </tr>
                                    </thead>
                                    <tbody>${trs}</tbody>
                                    </table>
                                </div>`;
                    }

                    function renderDetailReceipt(rows) {
                        if (!Array.isArray(rows) || rows.length === 0)
                            return `<div class="text-sm text-gray-500 dark:text-gray-400">No detail.</div>`;
                        const trs = rows.map(r => `
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-3 py-2">${esc(r.inventoryid)}</td>
                                    <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
                                    <td class="px-3 py-2 text-right">${fmt2(r.qtyordered)}</td>
                                    <td class="px-3 py-2 text-right">${fmt2(r.qty_received)}</td>
                                    <td class="px-3 py-2">${esc(r.uom)}</td>
                                </tr>`).join('');
                        return `
                                <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
                                    <table class="w-full text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700/30">
                                        <tr>
                                        <th class="px-3 py-2 text-left">Inventory</th>
                                        <th class="px-3 py-2 text-left">Description</th>
                                        <th class="px-3 py-2 text-right">Qty Ordered</th>
                                        <th class="px-3 py-2 text-right">Qty Received</th>
                                        <th class="px-3 py-2 text-left">UOM</th>
                                        </tr>
                                    </thead>
                                    <tbody>${trs}</tbody>
                                    </table>
                                </div>`;
                    }

                    // ---------- Select helpers ----------
                    function fillSelect(selectId, items, selectedDoc) {
                        const sel = document.getElementById(selectId);
                        if (!sel) return;

                        sel.innerHTML = '';

                        if (!items || items.length === 0) {
                            sel.innerHTML = `<option value="">none </option>`;
                            return;
                        }

                        items.forEach(it => {
                            const opt = document.createElement('option');
                            opt.value = it.doc;
                            // opt.textContent = `${it.doc}${it.date ? ' | ' + it.date : ''}${it.is_approved ? ' | APPROVED' : ''}`;
                            opt.textContent = `${it.doc}` +
                                (it.date ? ` | ${it.date}` : '') +
                                (it.status ? ` | ${statusLabel2(it.status)}` : '');

                            if (selectedDoc && it.doc === selectedDoc) opt.selected = true;
                            sel.appendChild(opt);
                        });

                        // kalau selectedDoc kosong, auto pilih pertama
                        if (!selectedDoc && sel.options.length > 0) sel.selectedIndex = 0;
                    }

                    function filterReceiptsByPo(poDoc) {
                        const all = window.__receiptList || [];
                        if (!poDoc) return all;

                        // backend kamu harus kirim: lists.receipt[].ponbr atau .po
                        return all.filter(x => (x.ponbr === poDoc) || (x.po === poDoc));
                    }

                    // ---------- AJAX helpers (jQuery Deferred) ----------
                    function fetchItem(eid, type, doc) {
                        return $.ajax({
                            url: `/sppbs/${eid}/tracking-detail/item`,
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                type,
                                doc
                            }
                        });
                    }

                    function renderDetailSppb(rows) {

                    if (!Array.isArray(rows) || rows.length === 0) {
                        return `<div class="text-sm text-gray-500 dark:text-gray-400">No detail.</div>`;
                    }

                    const trs = rows.map(r => `
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-3 py-2">${esc(r.inventoryid)}</td>
                            <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
                            <td class="px-3 py-2 text-right">${fmt2(r.qty)}</td>
                            <td class="px-3 py-2">${esc(r.uom)}</td>
                            <td class="px-3 py-2">${esc(r.site || '-')}</td>
                            <td class="px-3 py-2 text-right">${fmt2(r.qtyordered || 0)}</td>
                        </tr>
                    `).join('');

                    return `
                        <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/30">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Inventory</th>
                                        <th class="px-3 py-2 text-left">Description</th>
                                        <th class="px-3 py-2 text-right">Qty</th>
                                        <th class="px-3 py-2 text-left">UOM</th>
                                        <th class="px-3 py-2 text-left">Site</th>
                                        <th class="px-3 py-2 text-right">Ordered</th>
                                    </tr>
                                </thead>
                                <tbody>${trs}</tbody>
                            </table>
                        </div>
                    `;
                }

                    // ---------- Change handlers (safe: off/on) ----------
                    $(document).off('change', '#selCs').on('change', '#selCs', function() {
                        const eid = window.__trackEid;
                        const doc = this.value;
                        if (!eid || !doc) return;

                        fetchItem(eid, 'cs', doc).done(res => {
                            renderHeader('csHeaderBox', res.header, 'CS');
                            document.getElementById('csDetailBox').innerHTML = renderDetailCs(res.details ||
                                []);
                        });
                    });

                    $(document).off('change', '#selPo').on('change', '#selPo', function() {
                        const eid = window.__trackEid;
                        const doc = this.value;
                        if (!eid || !doc) return;

                        fetchItem(eid, 'po', doc).done(res => {
                            renderHeader('poHeaderBox', res.header, 'PO');
                            document.getElementById('poDetailBox').innerHTML = renderDetailPo(res.details ||
                                []);
                        });

                        // filter receipt list by PO selected
                        const filtered = filterReceiptsByPo(doc);
                        fillSelect('selReceipt', filtered, (filtered[0]?.doc || ''));

                        // auto load first receipt after filter
                        const first = filtered[0]?.doc;
                        if (first) {
                            fetchItem(eid, 'receipt', first).done(res => {
                                renderHeader('receiptHeaderBox', res.header, 'Receipt');
                                document.getElementById('receiptDetailBox').innerHTML = renderDetailReceipt(
                                    res
                                    .details || []);
                            });
                        } else {
                            renderHeader('receiptHeaderBox', null, 'Receipt');
                            document.getElementById('receiptDetailBox').innerHTML =
                                `<div class="text-sm text-gray-500 dark:text-gray-400">No detail.</div>`;
                        }
                    });

                    $(document).off('change', '#selReceipt').on('change', '#selReceipt', function() {
                        const eid = window.__trackEid;
                        const doc = this.value;
                        if (!eid || !doc) return;

                        fetchItem(eid, 'receipt', doc).done(res => {
                            renderHeader('receiptHeaderBox', res.header, 'Receipt');
                            document.getElementById('receiptDetailBox').innerHTML = renderDetailReceipt(res
                                .details || []);
                        });
                    });

                    // ---------- Main click handler (ONLY ONE) ----------
                    $(document).off('click', '.tracking-btn').on('click', '.tracking-btn', function() {
                        const eid = $(this).data('id');
                        const doc = $(this).data('doc') || '';
                        window.__trackEid = eid;

                        resetToSppbTab();
                        resetBoxes();
                        openTrackingModal(doc);
                        setLoading(true);

                        $.ajax({
                            url: `/sppbs/${eid}/tracking-detail`,
                            method: 'GET',
                            dataType: 'json',
                            success: function(res) {
                                setLoading(false);

                                // simpan list untuk filtering
                                window.__receiptList = res.lists?.receipt || [];

                                // render header default (selected)
                                renderHeader('sppbHeaderBox', res.sppb?.header, 'SPPB');
                                renderHeader('csHeaderBox', res.cs?.header, 'CS');
                                renderHeader('poHeaderBox', res.po?.header, 'PO');
                                renderHeader('receiptHeaderBox', res.receipt?.header, 'Receipt');

                                // render detail default (selected)
                                document.getElementById('sppbDetailBox').innerHTML = renderDetailSppb(
                                    res
                                    .sppb?.details || []);
                                document.getElementById('csDetailBox').innerHTML = renderDetailCs(res.cs
                                    ?.details || []);
                                document.getElementById('poDetailBox').innerHTML = renderDetailPo(res.po
                                    ?.details || []);
                                document.getElementById('receiptDetailBox').innerHTML =
                                    renderDetailReceipt(
                                        res.receipt?.details || []);

                                // dropdown lists (support multiple)
                                fillSelect('selCs', res.lists?.cs || [], res.selected?.cs_no || '');
                                fillSelect('selPo', res.lists?.po || [], res.selected?.po_no || '');

                                // receipt list default: filter by selected PO
                                const poSelected = (res.selected?.po_no) || document.getElementById(
                                        'selPo')
                                    ?.value || '';
                                const filteredReceipt = filterReceiptsByPo(poSelected);
                                const receiptSelected = res.selected?.receipt_no || (filteredReceipt[0]
                                    ?.doc || '');
                                fillSelect('selReceipt', filteredReceipt, receiptSelected);

                            },
                            error: function(xhr) {
                                setLoading(false);
                                document.getElementById('sppbHeaderBox').innerHTML =
                                    `<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                        Failed to load tracking (HTTP ${xhr.status || ''})
                                    </div>`;
                            }
                        });
                    });

                })(); // end IIFE
    </script>



    <script>
        var currentUser = "{{ auth()->user()->username }}";
        $(document).ready(function() {
            // simpan status filter global
            let statusFilter = 'P';
            let mode = 'normal';
            let deptFilter = '';
            let cpnyFilter = '';

            const woColumnIndex = 2; // index WO column

            const table = $('#sppbsTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                // ==== SCROLLER OPSIONAL (butuh plugin DataTables Scroller) ====
                // scrollY: '60vh',
                // scroller: true,

                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],

                // scrollX dimatikan: bentrok dengan responsive (menimbulkan baris header kedua)
                // scrollX: true,
                // responsive: false, // ✅ disable responsive

                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },


                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel"></i> Excel',
                        title: 'List_SPPB',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            orthogonal: 'export',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fa-solid fa-file-csv"></i> CSV',
                        title: 'List_SPPB',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            orthogonal: 'export',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],


                columnDefs: [{
                    targets: 0,
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }, {
                    targets: woColumnIndex,
                    visible: false
                }],

                ajax: {
                    url: "{{ route('sppbs.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
                        d.mode = mode;
                        d.department_extra = deptFilter;
                        d.company_extra = cpnyFilter;
                    },
                    complete: function(xhr) {

                        if (mode === 'all') {

                            const response = xhr.responseJSON;
                            const departments = response?.departments || [];
                            const companies = response?.companies || [];

                            const cpnySelect = $('#filterCompany');

                            cpnySelect.empty();
                            cpnySelect.append('<option value="">All Company</option>');

                            companies.forEach(function(c) {
                                cpnySelect.append('<option value="' + c + '">' + c + '</option>');
                            });

                            if (cpnyFilter) {
                                cpnySelect.val(cpnyFilter);
                            }

                            const deptSelect = $('#filterDepartment');

                            deptSelect.empty();
                            deptSelect.append(`<option value="">All Department</option>`);

                            departments.forEach(function(dep) {
                                deptSelect.append(
                                    `<option value="${dep}">${dep}</option>`
                                );
                            });

                            // keep selected value after reload
                            if (deptFilter) {
                                deptSelect.val(deptFilter);
                            }
                        }
                    }
                },
                order: [
                    [0, 'desc']
                ], // Date desc, lalu DocID desc
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    // DocID (button link)
                    {
                        data: 'sppbid',
                        render: function(data, type, row) {
                            let showUrl = `/showsppbs/${row.eid}`;
                            let editUrl = `/editsppbs/${row.eid}`;

                            let viewCls =
                                'inline-flex h-8 w-8 items-center justify-center rounded-full text-sm ' +
                                'text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-500/10';

                            // Doc ID: clean link-style text (no box); drafts get an amber pencil
                            let linkBase =
                                'group/doc inline-flex items-center gap-1.5 text-[13px] font-semibold tracking-wide tabular-nums ' +
                                'underline decoration-transparent decoration-2 underline-offset-4 transition ';

                            let editCls = linkBase +
                                'text-amber-600 hover:decoration-amber-300 dark:text-amber-400';

                            let defaultCls = linkBase +
                                'text-blue-600 hover:text-blue-700 hover:decoration-blue-300 dark:text-blue-400 dark:hover:text-blue-300';

                            let trackCls =
                                'tracking-btn inline-flex h-8 w-8 items-center justify-center rounded-full text-sm ' +
                                'text-rose-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10';

                            const text = data || row.id;

                            // ===== DRAFT/REVISE & OWNER =====
                            if ((row.status === 'D' || row.status === 'H') && row.created_by === currentUser) {
                                return `
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <!-- EDIT -->
                                        <a href="${editUrl}" class="${editCls}" title="Edit draft">
                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                            <span>${text}</span>
                                        </a>

                                        <!-- VIEW (EYE ICON) -->
                                        <a href="${showUrl}"  target="_blank" class="${viewCls}" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <!-- TRACKING -->
                                        <button type="button"
                                            class="${trackCls}"
                                            data-id="${row.eid}"
                                            data-doc="${text}"
                                            aria-label="Tracking" title="Tracking">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>

                                    </div>
                                `;
                            }

                            // ===== DEFAULT (NON-DRAFT / BUKAN OWNER) =====
                            return `
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <a href="${showUrl}" class="${defaultCls}" title="Open">
                                        <span>${text}</span>
                                    </a>

                                    <button type="button"
                                        class="${trackCls}"
                                        data-id="${row.eid}"
                                        data-doc="${text}"
                                        aria-label="Tracking" title="Tracking">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
                                </div>
                            `;
                        }

                    },
                    {
                        data: 'wo_number',
                        className: 'whitespace-nowrap', // tambahkan ini
                        render: function(data, type, row) {

                            if (mode !== 'wo') return '';
                            if (!data || !row.wo_hash) return '';

                            return `
        <a href="/showwos/${row.wo_hash}"
        target="_blank"
        class="inline-flex whitespace-nowrap items-center justify-center px-3 py-1.5 text-sm font-semibold rounded bg-sky-600 text-white hover:bg-sky-700">
        ${data}
        </a>`;
                        }
                    },
                    {
                        data: 'sppbdate',
                        className: 'text-left'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center w-32'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center whitespace-normal break-words'
                    },
                    {
                        data: 'requesttype_name',
                        defaultContent: '-',
                        className: 'text-left',
                        render: function(data, type) {
                            if (!data) return '-';
                            const str = String(data);
                            if (type === 'export' || str.length <= 40) return str;
                            return `<span title="${str.replace(/"/g, '&quot;')}" class="cursor-help">${str.substring(0, 40)}…</span>`;
                        }
                    },
                    {
                        data: 'keperluan',
                        className: 'text-left',
                        render: function(data, type) {
                            if (!data) return '-';
                            const str = String(data);
                            if (type === 'export' || str.length <= 50) return str;
                            return `<span title="${str.replace(/"/g, '&quot;')}" class="cursor-help">${str.substring(0, 50)}…</span>`;
                        }
                    },

                    {
                        data: 'status',
                        className: 'text-left',
                        render: function(data) {
                            // soft pill + dot; colours match the status filter cards above
                            const map = {
                                'D': {
                                    t: 'Revise',
                                    c: 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30',
                                    d: 'bg-amber-500'
                                },
                                'P': {
                                    t: 'On Progress',
                                    c: 'bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/30',
                                    d: 'bg-orange-500'
                                },
                                'C': {
                                    t: 'Completed',
                                    c: 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30',
                                    d: 'bg-green-500'
                                },
                                'X': {
                                    t: 'Cancel',
                                    c: 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30',
                                    d: 'bg-rose-500'
                                },
                                'R': {
                                    t: 'Rejected',
                                    c: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30',
                                    d: 'bg-red-500'
                                },
                                'H': {
                                    t: 'Draft',
                                    c: 'bg-pink-50 text-pink-700 ring-pink-200 dark:bg-pink-500/10 dark:text-pink-300 dark:ring-pink-500/30',
                                    d: 'bg-pink-500'
                                },
                            };
                            const it = map[data] || {
                                t: data || '-',
                                c: 'bg-slate-50 text-slate-600 ring-slate-200 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10',
                                d: 'bg-slate-400'
                            };
                            return `<span class="inline-flex min-w-[116px] items-center justify-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${it.c}"><span class="h-1.5 w-1.5 rounded-full ${it.d}"></span>${it.t}</span>`;
                        }
                    }
                ],

                // Tweak untuk kinerja
                searchDelay: 400, // debounce search
                stateSave: true, // simpan state tabel (opsional)
                // responsive: true
            });

            // $('.status-filter').on('click', function(e) {
            //     e.preventDefault();

            //     const selectedMode = $(this).data('mode');
            //     const selectedStatus = $(this).data('status');

            //     if (selectedMode === 'all') {

            //         mode = 'all';
            //         statusFilter = '';
            //         deptFilter = '';

            //         $('#pageTitle').text('SPPB All List');
            //         $('#createBtn').addClass('hidden');
            //         $('#allFilters').removeClass('hidden');

            //     } else {

            //         mode = 'normal';
            //         statusFilter = selectedStatus ?? '';

            //         $('#pageTitle').text('Request SPPB');
            //         $('#createBtn').removeClass('hidden');
            //         $('#allFilters').addClass('hidden');
            //     }

            //     table.ajax.reload(null, true);
            // });

            // default selected card = "All"
            $('.status-filter[data-status=""]').first().addClass('active');

            $(document).off('click', '.status-filter').on('click', '.status-filter', function(e) {

                e.preventDefault();

                $('.status-filter').removeClass('active');
                $(this).addClass('active');

                const selectedMode = $(this).data('mode') || 'normal';
                const selectedStatus = $(this).data('status');

                if (selectedMode === 'all') {

                    mode = 'all';
                    statusFilter = '';
                    deptFilter = '';
                    cpnyFilter = '';

                    $('#pageTitle').text('SPPB All List');
                    $('#createBtn').hide();
                    $('#allFilters').removeClass('hidden');

                    table.column(woColumnIndex).visible(false);
                    $('#thWoId').addClass('hidden');

                } else if (selectedMode === 'wo') {

                    mode = 'wo';
                    statusFilter = '';
                    deptFilter = '';

                    $('#pageTitle').text('WO → SPPB');
                    $('#createBtn').hide();
                    $('#allFilters').addClass('hidden');

                    table.column(woColumnIndex).visible(true);
                    $('#thWoId').removeClass('hidden');

                } else {

                    mode = 'normal';
                    statusFilter = selectedStatus ?? '';

                    $('#pageTitle').text('Request SPPB');
                    $('#createBtn').show();
                    $('#allFilters').addClass('hidden');

                    table.column(woColumnIndex).visible(false);
                    $('#thWoId').addClass('hidden');
                }

                table.ajax.reload(null, true);
            });
            $('#filterCompany').on('change', function() {
                cpnyFilter = this.value;
                deptFilter = '';
                table.ajax.reload();
            });

            $('#filterDepartment').on('change', function() {
                deptFilter = this.value;
                table.ajax.reload();
            });

            $('#filterStatus').on('change', function () {
                statusFilter = this.value;
                table.ajax.reload();
            });

            // document.querySelectorAll('.status-filter').forEach(btn => {
            //     btn.addEventListener('click', function(e) {
            //         e.preventDefault();
            //         document.querySelectorAll('.status-filter').forEach(b => b.classList.remove(
            //             'active'));
            //         this.classList.add('active');
            //     });
            // });
        });
    </script>
</x-app-layout>
