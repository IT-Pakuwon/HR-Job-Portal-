<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div>
                <h1 class="text-base font-bold text-gray-800 dark:text-white">🎓 Training List</h1>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Browse open trainings and manage your registrations.</p>
            </div>

            {{-- Tabs --}}
            <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700">
                <button class="tabBtn border-b-2 border-gray-900 px-3 py-2 text-sm font-semibold text-gray-900 dark:border-white dark:text-white" data-tab="available">
                    Available Trainings
                </button>
                <button class="tabBtn border-b-2 border-transparent px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="mine">
                    My Registrations
                </button>
                <button id="approvalsTabBtn" class="tabBtn hidden border-b-2 border-transparent px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="approvals">
                    Waiting Approval
                    <span id="approvalsTabCount" class="ml-1 inline-flex rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"></span>
                </button>
                @if (Auth::user()->hasRole('HCDEVACCESS'))
                    <button class="tabBtn border-b-2 border-transparent px-3 py-2 text-sm font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white" data-tab="allregs">
                        List Registration
                    </button>
                @endif
            </div>

            {{-- Available Trainings --}}
            <div id="tab-available" class="tab-panel space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <select id="filterLevel" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All Levels</option>
                    </select>
                    <select id="filterCategory" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All Categories</option>
                    </select>
                    <select id="filterMandatory" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">Mandatory: All</option>
                        <option value="1">Mandatory Only</option>
                        <option value="0">Non-Mandatory Only</option>
                    </select>
                    <button id="filterResetBtn" class="text-xs font-semibold text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">Reset</button>
                </div>

                <div id="availableEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                    No open trainings right now.
                </div>
                <div id="availableList" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"></div>
            </div>

            {{-- My Registrations --}}
            <div id="tab-mine" class="tab-panel hidden">
                <div id="mineEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                    You have no registrations yet.
                </div>
                <div class="overflow-x-auto">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="py-2 pr-4">Doc ID</th>
                                <th class="py-2 pr-4">Training</th>
                                <th class="py-2 pr-4">Level</th>
                                <th class="py-2 pr-4">Speaker</th>
                                <th class="py-2 pr-4">Date</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="mineBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                    </table>
                    <div id="minePagination"></div>
                </div>
            </div>

            {{-- Waiting Approval (only shown once loadPendingApprovals() finds something) --}}
            <div id="tab-approvals" class="tab-panel hidden">
                <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                    Training registrations currently waiting on your approval.
                </p>
                <div class="overflow-x-auto">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="py-2 pr-4">Doc ID</th>
                                <th class="py-2 pr-4">Employee</th>
                                <th class="py-2 pr-4">Company / Dept</th>
                                <th class="py-2 pr-4">Training</th>
                                <th class="py-2 pr-4">Schedule Date</th>
                                <th class="py-2 pr-4">Waiting Since</th>
                                <th class="py-2 pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody id="approvalsBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                    </table>
                    <div id="approvalsEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                        Nothing waiting on your approval right now.
                    </div>
                    <div id="approvalsPagination"></div>
                </div>
            </div>

            {{-- List Registration (HCDEVACCESS) --}}
            @if (Auth::user()->hasRole('HCDEVACCESS'))
                <div id="tab-allregs" class="tab-panel hidden space-y-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Every training registration across all employees, with its current status.
                    </p>

                    {{-- Filters — Training Event also rescopes the summary cards below --}}
                    <div class="rounded-2xl border border-gray-200 bg-linear-to-br from-gray-50 to-cyan-50/30 p-6 shadow-sm dark:border-gray-700 dark:from-gray-800/40 dark:to-cyan-900/10">
                        <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 lg:grid-cols-4">

                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-chalkboard-user mr-1 text-gray-400"></i> Training Event
                                </label>
                                <select id="allRegsTrainingFilter" class="w-full">
                                    <option value="">All Training Events</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-magnifying-glass mr-1 text-gray-400"></i> Search
                                </label>
                                <input id="allRegsSearch" type="text" placeholder="Employee or doc ID" class="form-input w-full">
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-list-check mr-1 text-gray-400"></i> Status
                                </label>
                                <select id="allRegsStatusFilter" class="w-full">
                                    <option value="">All Statuses</option>
                                    <option value="P">Waiting Approval</option>
                                    <option value="C">Approved</option>
                                    <option value="R">Rejected</option>
                                    <option value="W">Waiting List</option>
                                    <option value="O">Slot Offered</option>
                                    <option value="X">Cancelled</option>
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" id="allRegsExportBtn" class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-black hover:shadow active:scale-[0.98] dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
                                    <i class="fa-solid fa-file-arrow-down"></i> Export
                                </button>
                                <button type="button" id="allRegsResetBtn" class="flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-sm transition hover:border-gray-400 hover:bg-gray-50 active:scale-[0.98] dark:border-gray-600 dark:bg-transparent dark:text-gray-200 dark:hover:bg-gray-700/40">
                                    <i class="fa-solid fa-arrow-rotate-left"></i> Reset
                                </button>
                            </div>

                        </div>
                    </div>

                    {{-- Summary cards --}}
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                        <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-amber-600 dark:text-amber-400">Waiting Approval</p>
                            <p id="statWaitingApproval" class="mt-1 text-xl font-bold text-gray-800 dark:text-white">-</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-sky-600 dark:text-sky-400">Waiting List</p>
                            <p id="statWaitingList" class="mt-1 text-xl font-bold text-gray-800 dark:text-white">-</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-green-600 dark:text-green-400">Approved</p>
                            <p id="statApproved" class="mt-1 text-xl font-bold text-gray-800 dark:text-white">-</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-red-600 dark:text-red-400">Rejected</p>
                            <p id="statRejected" class="mt-1 text-xl font-bold text-gray-800 dark:text-white">-</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cancelled</p>
                            <p id="statCancelled" class="mt-1 text-xl font-bold text-gray-800 dark:text-white">-</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Reserved / Total Quota</p>
                            <span id="quotaOverallValue" class="text-sm font-bold text-gray-800 dark:text-white">-</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div id="quotaOverallBar" class="h-full rounded-full bg-gray-900 dark:bg-white" style="width:0%"></div>
                        </div>

                        <p class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Per Company</p>
                        <div id="quotaByCompany" class="flex flex-nowrap gap-2 overflow-x-auto pb-1"></div>
                        <p id="quotaByCompanyEmpty" class="hidden text-xs text-gray-400">No quota configured for this training.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    <th class="py-2 pr-4">Doc ID</th>
                                    <th class="py-2 pr-4">Employee</th>
                                    <th class="py-2 pr-4">Company / Dept</th>
                                    <th class="py-2 pr-4">Training</th>
                                    <th class="py-2 pr-4">Level</th>
                                    <th class="py-2 pr-4">Schedule Date</th>
                                    <th class="py-2 pr-4">Training Status</th>
                                    <th class="py-2 pr-4">Registered On</th>
                                    <th class="py-2 pr-4">Status</th>
                                    <th class="py-2 pr-4">Approval</th>
                                    <th class="py-2 pr-4">Queue #</th>
                                    <th class="py-2 pr-4">Action</th>
                                </tr>
                            </thead>
                            <tbody id="allRegsBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                        </table>
                        <div id="allRegsEmpty" class="hidden rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                            No registrations found.
                        </div>
                        <div id="allRegsPagination"></div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Training Detail modal --}}
        <div id="detailModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
                <button type="button" id="closeDetailModalX"
                    class="absolute right-4 top-4 z-10 rounded-lg bg-white/90 p-1.5 text-gray-500 shadow transition hover:bg-white hover:text-gray-900 dark:bg-gray-900/80 dark:text-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <button type="button" id="detailHeroFullPreviewBtn"
                    class="absolute right-4 top-14 z-10 hidden items-center gap-1 rounded-lg bg-white/90 px-2.5 py-1.5 text-xs font-semibold text-gray-700 shadow transition hover:bg-white dark:bg-gray-900/80 dark:text-gray-200">
                    🖼️ Full Preview
                </button>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    <div id="detailHeroWrap" class="relative h-56 w-full bg-gradient-to-br from-gray-800 via-gray-700 to-gray-900 sm:h-64">
                        <div id="detailHeroPoster" class="absolute inset-0 bg-cover bg-center"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-5">
                            <div id="detailHeroBadges" class="mb-2 flex flex-wrap gap-1.5"></div>
                            <h2 id="detailHeroTitle" class="wrap-break-word text-xl font-bold text-white sm:text-2xl"></h2>
                            <p id="detailHeroMeta" class="mt-1 text-xs text-gray-200"></p>
                        </div>
                    </div>

                    <div class="p-5">
                        <p id="detailDescriptionText" class="hidden text-sm leading-relaxed text-gray-600 dark:text-gray-300"></p>
                        <h3 class="mb-3 mt-5 text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Schedules</h3>
                        <div id="detailScheduleList" class="flex flex-col gap-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Below 640px, data tables stack into label/value cards instead of
           squeezing every column into an unreadably narrow cell — the
           overflow-x-auto scroll wrapper alone still left headers/badges
           wrapping mid-word. Each <td> needs a data-label attribute (set in
           the JS render functions) for the ::before to pick up. */
        @media (max-width: 640px) {
            .responsive-table thead {
                display: none;
            }
            .responsive-table, .responsive-table tbody {
                display: block;
                width: 100%;
            }
            .responsive-table tbody > * + * {
                border-top-width: 0 !important;
            }
            /* Grid instead of plain block stacking so Doc ID can share a row
               with its table's date cell (pinned via grid-row/grid-column
               below) while every other cell still spans the full width. */
            .responsive-table tr {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px 12px;
                margin-bottom: 10px;
                padding: 10px 12px;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
            }
            html.dark .responsive-table tr {
                border-color: #374151;
            }
            .responsive-table td {
                grid-column: 1 / -1;
                padding: 0 !important;
                border: none !important;
            }
            .responsive-table td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .03em;
                color: #9ca3af;
                margin-bottom: 2px;
            }
            /* Doc ID pairs with its table's date column on one row — pinned
               to explicit row/column so it holds regardless of where the
               date cell actually falls in DOM/source order. */
            #tab-mine .responsive-table td[data-label="Doc ID"],
            #tab-approvals .responsive-table td[data-label="Doc ID"],
            #tab-allregs .responsive-table td[data-label="Doc ID"] {
                grid-row: 1;
                grid-column: 1;
            }
            #tab-mine .responsive-table td[data-label="Date"],
            #tab-approvals .responsive-table td[data-label="Schedule Date"],
            #tab-allregs .responsive-table td[data-label="Schedule Date"] {
                grid-row: 1;
                grid-column: 2;
            }
            /* Actions/Action cells hold tap targets — stack them full-width
               and enlarge instead of leaving them at desktop's compact
               inline-row size, which reads as cramped on a touch screen. */
            .responsive-table td[data-label="Actions"] > div,
            .responsive-table td[data-label="Action"] > div {
                flex-direction: column;
                align-items: stretch;
            }
            .responsive-table td[data-label="Actions"] button,
            .responsive-table td[data-label="Actions"] a,
            .responsive-table td[data-label="Action"] button,
            .responsive-table td[data-label="Action"] a {
                width: 100%;
                padding-top: 11px;
                padding-bottom: 11px;
                font-size: 13px;
                text-align: center;
            }
        }
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 4px 0;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            font-size: 13px;
            line-height: 28px;
            color: #111827;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-filter .select2-selection--single {
            height: 38px !important;
            border-radius: 8px;
            border-color: #d1d5db;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .select2-filter .select2-selection--single:hover {
            border-color: #9ca3af;
        }
        .select2-filter .select2-selection--single .select2-selection__rendered {
            font-size: 13px !important;
            line-height: 36px !important;
            padding-left: 12px;
            padding-right: 36px;
        }
        .select2-filter .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
        .select2-filter .select2-selection--single .select2-selection__clear {
            margin-right: 4px;
        }
        .select2-filter.select2-container--focus .select2-selection--single,
        .select2-filter.select2-container--open .select2-selection--single {
            border-color: #9ca3af;
            box-shadow: 0 0 0 2px #e5e7eb;
            outline: none;
        }
        html.dark .select2-filter .select2-selection--single {
            background: #1f2937;
            border-color: #4b5563;
        }
        html.dark .select2-filter .select2-selection--single:hover {
            border-color: #6b7280;
        }
        html.dark .select2-filter .select2-selection--single .select2-selection__rendered {
            color: #e5e7eb;
        }
        html.dark .select2-filter.select2-container--focus .select2-selection--single,
        html.dark .select2-filter.select2-container--open .select2-selection--single {
            border-color: #6b7280;
            box-shadow: 0 0 0 2px #374151;
        }
        html.dark .select2-filter .select2-dropdown {
            background: #1f2937;
            border-color: #4b5563;
        }
        html.dark .select2-filter .select2-results__option {
            color: #e5e7eb;
        }
        html.dark .select2-filter .select2-search--dropdown .select2-search__field {
            background: #111827;
            border-color: #4b5563;
            color: #e5e7eb;
        }
        .dateCardOption {
            cursor: pointer;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 14px;
            min-width: 0;
            transition: border-color .15s ease, background .15s ease, transform .1s ease;
        }
        .dateCardOption:hover {
            border-color: #9ca3af;
            transform: translateY(-1px);
        }
        .dateCardOption.selected {
            border-color: #111827;
            background: #f9fafb;
            box-shadow: 0 0 0 1px #111827;
        }
        .ticketConfirmBtn {
            background: #111827 !important;
            color: #fff !important;
            border: none !important;
            padding: 11px 0 !important;
            width: 100%;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            box-shadow: none !important;
        }
        .ticketConfirmBtn:hover {
            background: #374151 !important;
        }
        .ticketCancelBtn {
            background: transparent !important;
            color: #6b7280 !important;
            box-shadow: none !important;
            font-weight: 500 !important;
            font-size: 12.5px !important;
            padding: 4px 0 !important;
            margin: 0 !important;
        }
        .ticketCancelBtn:hover {
            color: #111827 !important;
            text-decoration: underline;
        }
        .swal2-actions {
            flex-direction: column;
            width: 100%;
            gap: 4px;
        }
        .ticketModalPopup {
            padding: 0 !important;
            border-radius: 16px !important;
            max-width: calc(100vw - 32px) !important;
        }
        .ticketModalPopup .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }
        .ticketModalPopup .swal2-actions {
            margin: 0 !important;
            padding: 16px 24px 20px !important;
            border-top: 1px solid #f0f1f3;
            background: #fafafa;
            border-radius: 0 0 16px 16px;
        }
        .ticketModal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f1f3;
            border-radius: 16px 16px 0 0;
        }
        .ticketModal-thumb {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            flex-shrink: 0;
            object-fit: cover;
        }
        .ticketModal-thumbFallback {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #374151, #111827);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .ticketModal-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .ticketModal-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin: 3px 0 0;
        }
        .ticketModal-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 5px;
        }
        .ticketModal-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 2px 9px;
            font-size: 10px;
            font-weight: 600;
        }
        .ticketModal-badge.neutral {
            background: #f3f4f6;
            color: #374151;
        }
        .ticketModal-badge.info {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .ticketModal-badge.mandatory {
            background: #fee2e2;
            color: #b91c1c;
        }
        .ticketModal-body {
            padding: 20px 24px 24px;
            text-align: left;
        }
        .ticketModal-label {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .ticketModal-sessionGrid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        @media (max-width: 420px) {
            .ticketModal-sessionGrid {
                grid-template-columns: 1fr;
            }
        }
        .ticketModal-card {
            margin-top: 16px;
            padding: 16px;
            border-radius: 12px;
            background: #f9fafb;
            border: 1px solid #f0f1f3;
        }
        .ticketModal-card:first-child {
            margin-top: 0;
        }
        .ticketModal-pickerGrid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
        }
        .ticketModal-field {
            min-width: 0;
        }
        .ticketModal-select {
            display: block;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            color: #111827;
            background: #fff;
        }
        .ticketModal-capacity {
            margin-top: 14px;
        }
        .ticketModal-pickerGrid + .ticketModal-capacity {
            margin-top: 14px;
        }
        .posterPreviewPopup {
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .posterPreviewPopup .swal2-image {
            margin: 0 !important;
            border-radius: 12px;
            max-height: 85vh;
            width: 100%;
            object-fit: contain;
        }
        .approveModalPopup {
            padding: 0 !important;
            border-radius: 16px !important;
            width: 380px !important;
            max-width: calc(100vw - 32px) !important;
        }
        .approveModalPopup .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }
        .approveModalPopup .swal2-actions {
            flex-direction: column;
            width: 100%;
            gap: 8px;
            margin: 0 !important;
            padding: 16px 24px 20px !important;
            border-top: 1px solid #f0f1f3;
            background: #fafafa;
            border-radius: 0 0 16px 16px;
        }
        .approveModal-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 28px 24px 20px;
        }
        .approveModal-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .approveModal-icon.approve {
            background: #dcfce7;
            color: #16a34a;
        }
        .approveModal-icon.reject {
            background: #fee2e2;
            color: #dc2626;
        }
        .approveModal-title {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        .approveModal-card {
            margin: 0 24px 24px;
            padding: 6px 16px;
            border-radius: 12px;
            background: #f9fafb;
            border: 1px solid #f0f1f3;
            text-align: left;
        }
        .approveModal-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            padding: 9px 0;
        }
        .approveModal-row + .approveModal-row {
            border-top: 1px solid #eef0f2;
        }
        .approveModal-key {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #9ca3af;
            flex-shrink: 0;
        }
        .approveModal-value {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            text-align: right;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .approveConfirmBtn {
            background: #16a34a !important;
            color: #fff !important;
            border: none !important;
            padding: 11px 0 !important;
            width: 100%;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            box-shadow: none !important;
        }
        .approveConfirmBtn:hover {
            background: #15803d !important;
        }
        .rejectConfirmBtn {
            background: #dc2626 !important;
            color: #fff !important;
            border: none !important;
            padding: 11px 0 !important;
            width: 100%;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            box-shadow: none !important;
        }
        .rejectConfirmBtn:hover {
            background: #b91c1c !important;
        }
        .feedbackModalPopup {
            padding: 0 !important;
            border-radius: 16px !important;
            width: 560px !important;
            max-width: calc(100vw - 32px) !important;
        }
        .feedbackModalPopup .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
            max-height: none !important;
        }
        .feedbackModalPopup .swal2-actions {
            flex-direction: column;
            width: 100%;
            gap: 4px;
            margin: 0 !important;
            padding: 16px 24px 20px !important;
            border-top: 1px solid #f0f1f3;
            background: #fafafa;
            border-radius: 0 0 16px 16px;
        }
        .feedbackModal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f1f3;
        }
        .feedbackModal-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #374151, #111827);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .feedbackModal-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            text-align: left;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .feedbackModal-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin: 3px 0 0;
        }
        .feedbackModal-body {
            padding: 20px 24px 24px;
            text-align: left;
            max-height: 58vh;
            overflow-y: auto;
        }
        .feedbackModal-notice {
            display: flex;
            gap: 8px;
            align-items: flex-start;
            padding: 10px 12px;
            border-radius: 8px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            font-size: 12px;
            margin-bottom: 16px;
        }
        .feedbackModal-question {
            padding: 16px;
            border-radius: 12px;
            background: #f9fafb;
            border: 1px solid #f0f1f3;
        }
        .feedbackModal-question + .feedbackModal-question {
            margin-top: 12px;
        }
        .feedbackModal-qHead {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 12px;
        }
        .feedbackModal-qNum {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #111827;
            color: #fff;
            font-size: 10.5px;
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .feedbackModal-qText {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            line-height: 1.4;
        }
        .feedbackModal-choiceGroup {
            display: flex;
            gap: 8px;
        }
        .feedbackModal-choice {
            position: relative;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
            transition: border-color .15s, background .15s, color .15s;
        }
        .feedbackModal-choice input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .feedbackModal-choice:has(input:checked) {
            border-color: #111827;
            background: #111827;
            color: #fff;
        }
        .feedbackModal-choice:has(input:disabled) {
            cursor: not-allowed;
        }
        .feedbackModal-choice:has(input:disabled):not(:has(input:checked)) {
            opacity: .5;
        }
        .feedbackModal-ratingGroup {
            display: flex;
            gap: 8px;
        }
        .feedbackModal-ratingItem {
            position: relative;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 0;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            cursor: pointer;
            transition: border-color .15s, background .15s, color .15s;
        }
        .feedbackModal-ratingItem input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .feedbackModal-ratingItem:has(input:checked) {
            border-color: #f59e0b;
            background: #fffbeb;
            color: #b45309;
        }
        .feedbackModal-ratingItem:has(input:disabled) {
            cursor: not-allowed;
        }
        .feedbackModal-ratingItem:has(input:disabled):not(:has(input:checked)) {
            opacity: .5;
        }
        .feedbackModal-ratingScale {
            display: flex;
            justify-content: space-between;
            margin-top: 6px;
            font-size: 10.5px;
            color: #9ca3af;
        }
        .feedbackModal-textarea {
            display: block;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            color: #111827;
            background: #fff;
            resize: vertical;
            min-height: 60px;
            font-family: inherit;
        }
        .feedbackModal-textarea:focus {
            outline: none;
            border-color: #111827;
            box-shadow: 0 0 0 3px rgba(17, 24, 39, .08);
        }
        .feedbackModal-textarea:disabled {
            background: #f9fafb;
            color: #6b7280;
            cursor: not-allowed;
        }
        .feedbackConfirmBtn {
            background: #111827 !important;
            color: #fff !important;
            border: none !important;
            padding: 11px 0 !important;
            width: 100%;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            box-shadow: none !important;
        }
        .feedbackConfirmBtn:hover {
            background: #374151 !important;
        }
        .viewModalPopup {
            padding: 0 !important;
            border-radius: 16px !important;
            width: 480px !important;
            max-width: calc(100vw - 32px) !important;
        }
        .viewModalPopup .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }
        .viewModalPopup .swal2-actions {
            margin: 0 !important;
            padding: 16px 24px 20px !important;
            border-top: 1px solid #f0f1f3;
            background: #fafafa;
            border-radius: 0 0 16px 16px;
        }
        .viewModal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f1f3;
        }
        .viewModal-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #374151, #111827);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .viewModal-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            text-align: left;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .viewModal-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin: 3px 0 0;
            font-family: monospace;
        }
        .viewModal-body {
            padding: 20px 24px 24px;
            text-align: left;
            max-height: 60vh;
            overflow-y: auto;
        }
        .viewModal-card {
            padding: 4px 14px;
            border-radius: 12px;
            background: #f9fafb;
            border: 1px solid #f0f1f3;
        }
        .viewModal-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            padding: 8px 0;
        }
        .viewModal-row + .viewModal-row {
            border-top: 1px solid #eef0f2;
        }
        .viewModal-key {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #9ca3af;
            flex-shrink: 0;
        }
        .viewModal-value {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            text-align: right;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .viewModal-sectionTitle {
            margin: 20px 0 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #6b7280;
        }
        .approvalStepList {
            display: flex;
            flex-direction: column;
        }
        .approvalStep {
            display: flex;
            gap: 10px;
            position: relative;
            padding-bottom: 18px;
        }
        .approvalStep:last-child {
            padding-bottom: 0;
        }
        .approvalStep::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 24px;
            bottom: 0;
            width: 1px;
            background: #e5e7eb;
        }
        .approvalStep:last-child::before {
            display: none;
        }
        .approvalStep-marker {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            z-index: 1;
        }
        .approvalStep-marker.approved {
            background: #dcfce7;
            color: #16a34a;
        }
        .approvalStep-marker.rejected {
            background: #fee2e2;
            color: #dc2626;
        }
        .approvalStep-marker.pending {
            background: #fef9c3;
            color: #a16207;
        }
        .approvalStep-marker.neutral {
            background: #f3f4f6;
            color: #6b7280;
        }
        .approvalStep-body {
            min-width: 0;
            flex: 1;
            padding-top: 2px;
        }
        .approvalStep-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .approvalStep-level {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #9ca3af;
        }
        .approvalStep-badge {
            font-size: 10.5px;
            font-weight: 700;
        }
        .approvalStep-badge.approved { color: #16a34a; }
        .approvalStep-badge.rejected { color: #dc2626; }
        .approvalStep-badge.pending { color: #a16207; }
        .approvalStep-badge.neutral { color: #6b7280; }
        .approvalStep-name {
            margin-top: 2px;
            font-size: 13px;
            font-weight: 600;
            color: #111827;
        }
        .approvalStep-when {
            margin-top: 1px;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
    <script>
        const jsonUrl = "{{ route('training-list.json') }}";
        const myUrl = "{{ route('training-list.my') }}";
        const certificateUrl = "{{ route('training-list.certificate', ['id' => '__ID__']) }}";
        const myViewUrlTpl = "{{ route('training-list.my.show', ['eid' => '__EID__'], false) }}";
        const trainingListPath = "{{ route('training-list', [], false) }}";
        const cancelUrlTpl = "{{ route('training-list.cancel', ['scheduleId' => '__ID__']) }}";
        const colleaguesUrl = "{{ route('training-list.colleagues') }}";
        const pendingApprovalsUrl = "{{ route('training-list.pending-approvals') }}";
        const approvalUrlTpl = "{{ route('approval.get', ['refnbr' => '__REF__', 'doctype' => 'TRN']) }}";
        const isHcdevaccess = @json(Auth::user()->hasRole('HCDEVACCESS'));
        @if (Auth::user()->hasRole('HCDEVACCESS'))
        const allRegistrationsUrl = "{{ route('training-list.all-registrations') }}";
        const registrationSummaryUrl = "{{ route('training-list.registration-summary') }}";
        const allRegistrationsExportUrl = "{{ route('training-list.all-registrations.export') }}";
        const allRegsViewUrlTpl = "{{ route('training-list.allregs.show', ['eid' => '__EID__'], false) }}";
        @endif
        const csrfHeaders = { 'X-CSRF-TOKEN': '{{ csrf_token() }}' };
        const initialEid = @json($initialEid);
        const initialMyEid = @json($initialMyEid ?? null);
        const initialAllRegsEid = @json($initialAllRegsEid ?? null);

        const statusLabels = {
            P: ['Waiting Approval', 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'],
            C: ['Approved', 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'],
            R: ['Rejected', 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'],
            X: ['Cancelled', 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'],
            W: ['Waiting List', 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300'],
            O: ['Slot Offered', 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'],
        };

        function statusBadge(status) {
            const [label, cls] = statusLabels[status] || [status, 'bg-gray-100 text-gray-600'];
            return `<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${cls}">${label}</span>`;
        }

        // Approved registrations read as plain text ("Registration Approved")
        // rather than a colored pill, everywhere a per-schedule/my-status chip
        // is shown — the colored badge is reserved for statuses that need to
        // stand out (pending, rejected, waitlisted, offered).
        function myStatusChip(status) {
            if (status === 'C') {
                return '<span class="text-xs text-gray-400">Registration Approved</span>';
            }
            return statusBadge(status);
        }

        // ms_lnd_training_schedule.status: single-letter codes (see
        // TrainingRegistrationController::SCHEDULE_*).
        const scheduleStatusLabels = {
            D: ['Draft', 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'],
            P: ['Published', 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'],
            C: ['Closed', 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'],
            X: ['Cancelled', 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'],
        };

        function scheduleStatusBadge(status) {
            const [label, cls] = scheduleStatusLabels[status] || [status ?? '-', 'bg-gray-100 text-gray-600'];
            return `<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${cls}">${label}</span>`;
        }

        function fmtDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        // Calendar-day comparison against a 'Y-m-d' string (matches the
        // schedule_date < today check in TrainingRegistrationController::cancel())
        // — same-day is not "passed" yet, only strictly earlier dates are.
        function isDateStrPast(dateStr) {
            if (!dateStr) return false;
            const now = new Date();
            const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
            return dateStr < todayStr;
        }

        const PAGE_SIZE = 10;

        // Slices a filtered row array down to one page, clamping the given
        // page number into range (e.g. after a filter shrinks the result set
        // below the previously-viewed page).
        function paginateRows(rows, page) {
            const totalPages = Math.max(1, Math.ceil(rows.length / PAGE_SIZE));
            const clamped = Math.min(Math.max(1, page), totalPages);
            const start = (clamped - 1) * PAGE_SIZE;
            return { pageRows: rows.slice(start, start + PAGE_SIZE), page: clamped, totalPages };
        }

        function renderPagination(containerId, totalItems, page, totalPages, onChange) {
            const $el = $('#' + containerId);

            if (totalPages <= 1) {
                $el.empty();
                return;
            }

            const start = totalItems === 0 ? 0 : (page - 1) * PAGE_SIZE + 1;
            const end = Math.min(page * PAGE_SIZE, totalItems);

            $el.html(`
                <div class="flex flex-wrap items-center justify-between gap-2 pt-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>Showing ${start}-${end} of ${totalItems}</span>
                    <div class="flex items-center gap-1">
                        <button type="button" class="paginationPrevBtn rounded-lg px-2.5 py-1 font-semibold text-gray-600 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-800" ${page <= 1 ? 'disabled' : ''}>Prev</button>
                        <span class="px-1 font-medium text-gray-600 dark:text-gray-300">Page ${page} of ${totalPages}</span>
                        <button type="button" class="paginationNextBtn rounded-lg px-2.5 py-1 font-semibold text-gray-600 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-800" ${page >= totalPages ? 'disabled' : ''}>Next</button>
                    </div>
                </div>
            `);

            $el.find('.paginationPrevBtn').on('click', () => onChange(page - 1));
            $el.find('.paginationNextBtn').on('click', () => onChange(page + 1));
        }

        // Always shows the field (so a single-option pick reads as "already
        // decided for you" rather than vanishing) and only disables it —
        // never omits it — when there's nothing to actually choose.
        function pickerFieldHtml(icon, label, id, options) {
            if (!options.length) return '';
            const opts = options.map((o) => `<option value="${o.id}">${o.name}</option>`).join('');
            const disabled = options.length === 1 ? ' disabled' : '';
            return `
                <div class="ticketModal-field">
                    <label class="ticketModal-label">${icon} ${label}</label>
                    <select id="${id}" class="ticketModal-select"${disabled}>${opts}</select>
                </div>
            `;
        }

        // Deterministic color per category/training name, so the same
        // training always gets the same card-icon tile color across reloads
        // instead of a random one each render.
        const cardPalettes = [
            ['#eef2ff', '#4338ca'],
            ['#ecfeff', '#0e7490'],
            ['#f0fdf4', '#15803d'],
            ['#fff7ed', '#c2410c'],
            ['#fdf2f8', '#be185d'],
            ['#f5f3ff', '#6d28d9'],
            ['#fefce8', '#a16207'],
        ];

        function paletteFor(key) {
            const str = key || '';
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                hash = (hash * 31 + str.charCodeAt(i)) >>> 0;
            }
            return cardPalettes[hash % cardPalettes.length];
        }

        $('.tabBtn').on('click', function () {
            const tab = $(this).data('tab');
            $('.tabBtn').removeClass('border-gray-900 text-gray-900 dark:border-white dark:text-white')
                .addClass('border-transparent text-gray-500 dark:text-gray-400');
            $(this).removeClass('border-transparent text-gray-500 dark:text-gray-400')
                .addClass('border-gray-900 text-gray-900 dark:border-white dark:text-white');
            $('.tab-panel').addClass('hidden');
            $('#tab-' + tab).removeClass('hidden');

            if (tab === 'mine') loadMine();
            if (tab === 'approvals') loadPendingApprovals();
            if (tab === 'allregs') loadAllRegistrations();
        });

        function toast(icon, title) {
            Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2500, timerProgressBar: true });
        }

        let cardsByDocid = {};
        let deptOptionsGlobal = [];
        let allTrainingRows = [];
        let myCompanyNameGlobal = '';
        let myDepartmentNameGlobal = '';

        function populateFilterOptions(rows) {
            const $level = $('#filterLevel');
            const $category = $('#filterCategory');
            const selectedLevel = $level.val();
            const selectedCategory = $category.val();

            const levels = [...new Set(rows.flatMap((r) => r.levels))].sort();
            const categories = [...new Set(rows.map((r) => r.category_name).filter(Boolean))].sort();

            $level.find('option:not(:first)').remove();
            levels.forEach((l) => $level.append(new Option(l, l)));
            $level.val(levels.includes(selectedLevel) ? selectedLevel : '');

            $category.find('option:not(:first)').remove();
            categories.forEach((c) => $category.append(new Option(c, c)));
            $category.val(categories.includes(selectedCategory) ? selectedCategory : '');
        }

        function applyFilters() {
            const level = $('#filterLevel').val();
            const category = $('#filterCategory').val();
            const mandatory = $('#filterMandatory').val();

            const filtered = allTrainingRows.filter((r) => {
                if (level && !r.levels.includes(level)) return false;
                if (category && r.category_name !== category) return false;
                if (mandatory === '1' && !r.is_mandatory) return false;
                if (mandatory === '0' && r.is_mandatory) return false;
                return true;
            });

            renderTrainingCards(filtered);
        }

        $('#filterLevel, #filterCategory, #filterMandatory').on('change', applyFilters);
        $('#filterResetBtn').on('click', function () {
            $('#filterLevel, #filterCategory, #filterMandatory').val('');
            applyFilters();
        });

        function loadAvailable() {
            $.get(jsonUrl, function (res) {
                const rows = res.data || [];
                deptOptionsGlobal = res.department_options || [];
                allTrainingRows = rows;
                myCompanyNameGlobal = res.my_company_name || '';
                myDepartmentNameGlobal = res.my_department_name || '';

                cardsByDocid = {};
                rows.forEach((r) => { cardsByDocid[r.docid] = r; });

                populateFilterOptions(rows);
                renderTrainingCards(rows);

                if (!initialEidHandled && initialEid) {
                    initialEidHandled = true;
                    const match = Object.values(cardsByDocid).find((g) => g.eid === initialEid);
                    if (match) openDetailModal(match);
                }
            });
        }

        function renderTrainingCards(rows) {
            $('#availableEmpty').toggleClass('hidden', rows.length > 0);
            const $list = $('#availableList').empty();

            rows.forEach(function (r) {

                    const levelLabel = r.levels.length ? r.levels.join(', ') : null;
                    const firstSched = r.schedules[0] || {};

                    const metaParts = [];
                    if (r.schedule_count === 1 && firstSched.schedule_date) {
                        metaParts.push(`🗓 ${fmtDate(firstSched.schedule_date)} · ${firstSched.start_time ?? ''}-${firstSched.end_time ?? ''}`);
                    } else if (r.schedule_count > 1) {
                        metaParts.push(`🗓 ${r.schedule_count} dates`);
                    }
                    if (r.speakers.length) metaParts.push(`🗣️ ${r.speakers.join(', ')}`);
                    const metaLine = metaParts.length
                        ? `<p class="mt-0.5 wrap-break-word text-xs text-gray-500 dark:text-gray-400">${metaParts.join(' &nbsp;·&nbsp; ')}</p>`
                        : '';

                    // Representative mode/location — schedules within one docid batch
                    // (one "Add Schedule" transaction) share a venue, so the earliest
                    // schedule's mode/location stands in for the whole card.
                    const locationLabel = firstSched.mode
                        ? `📍 ${firstSched.mode}${firstSched.location || firstSched.platform ? ' · ' + (firstSched.location || firstSched.platform) : ''}`
                        : null;
                    const locationLine = locationLabel
                        ? `<p class="mt-0.5 wrap-break-word text-xs text-gray-500 dark:text-gray-400">${locationLabel}</p>`
                        : '';

                    // "already registered" only hides the button if every open date is
                    // covered — otherwise the employee can still register for another date.
                    // Past-deadline dates are excluded here (not registerable) but still
                    // count toward schedule_count and appear in View Detail as informational.
                    // Per-schedule status (Approved/Rejected/Waitlisted/etc.) is shown
                    // inside View Detail rather than duplicated here on the card.
                    const openSchedules = r.schedules.filter((s) => !s.my_status && s.is_open && s.level_match);

                    let registerBtnHtml = '';
                    if (!r.eligible) {
                        const reasonText = r.level_eligible ? 'Not available for your company' : 'Your Level can\'t Register to This Training';
                        registerBtnHtml = `<span class="flex items-center justify-center rounded-lg border border-dashed border-gray-200 px-2 py-1.5 text-center text-[11px] text-gray-400 dark:border-gray-700">${reasonText}</span>`;
                    } else if (openSchedules.length > 0) {
                        const anyAvailable = openSchedules.some((s) => s.eligible_companies.some((c) => c.available > 0));
                        const btnCls = anyAvailable ? 'bg-gray-900 hover:bg-gray-700 dark:bg-white dark:text-gray-900' : 'bg-sky-600 hover:bg-sky-500 text-white';
                        const btnText = anyAvailable ? 'Register' : 'Join Waiting List';
                        registerBtnHtml = `<button class="registerBtn rounded-lg px-3 py-1.5 text-xs font-semibold text-white ${btnCls}" data-docid="${r.docid}">${btnText}</button>`;
                    }

                    // grid-cols-2 fits both actions side by side; when there's only one
                    // (no register action applies), it spans both columns instead of
                    // leaving an empty cell next to it.
                    const detailBtn = r.eid
                        ? `<button class="viewDetailBtn${registerBtnHtml === '' ? ' col-span-2' : ''} rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-docid="${r.docid}">View Detail</button>`
                        : '';

                    const [tileBg, tileFg] = paletteFor(r.category_name || r.training_name);
                    const iconTile = `<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg" style="background:${tileBg};color:${tileFg};">🎓</div>`;

                $list.append(`
                    <div class="flex flex-col gap-2.5 rounded-xl border border-gray-200 bg-white p-3.5 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex items-start gap-3">
                            ${iconTile}
                            <div class="min-w-0 flex-1">
                                <h3 class="wrap-break-word text-sm font-semibold leading-snug text-gray-800 dark:text-white">${r.training_name ?? '-'}</h3>
                                <div class="mt-1 flex flex-wrap items-center gap-1">
                                    ${levelLabel ? `<span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">${levelLabel}</span>` : ''}
                                    ${r.is_mandatory ? `<span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-300">Mandatory</span>` : ''}
                                </div>
                            </div>
                        </div>
                        ${metaLine}
                        ${locationLine}
                        <div class="mt-auto grid grid-cols-2 gap-2 pt-1">
                            ${detailBtn}
                            ${registerBtnHtml}
                        </div>
                    </div>
                `);
            });
        }

        let initialEidHandled = false;

        function capacityBar(c) {
            const used = (c.reserved || 0) + (c.used || 0);
            const filled = c.quota_pax > 0 ? Math.min(100, Math.round((used / c.quota_pax) * 100)) : 0;
            const barColor = filled >= 100 ? '#dc2626' : '#111827';
            return `
                <div class="border-t border-dashed border-gray-200 py-1.5 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="text-gray-600 dark:text-gray-300">${c.cpny_name}</span>
                        <span class="font-medium text-gray-500 dark:text-gray-400">${c.available}/${c.quota_pax} avail &nbsp;·&nbsp; ${c.reserved} rsvp</span>
                    </div>
                    <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full rounded-full" style="width:${filled}%;background:${barColor};"></div>
                    </div>
                </div>
            `;
        }

        function openDetailModal(training) {
            if (training.poster_url) {
                $('#detailHeroPoster').css('background-image', `url('${training.poster_url}')`);
                $('#detailHeroFullPreviewBtn').removeClass('hidden').addClass('flex').data('poster', training.poster_url);
            } else {
                $('#detailHeroPoster').css('background-image', '');
                $('#detailHeroFullPreviewBtn').addClass('hidden').removeClass('flex').removeData('poster');
            }

            $('#detailHeroTitle').text(training.training_name ?? '-');
            $('#detailHeroMeta').text(training.schedule_count > 1 ? `🗓 ${training.schedule_count} schedules available` : '');

            const badges = [];
            if (training.category_name) {
                badges.push(`<span class="inline-flex items-center rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">${training.category_name}</span>`);
            }
            if (training.training_type) {
                badges.push(`<span class="inline-flex items-center rounded-full bg-blue-500/80 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">${training.training_type}</span>`);
            }
            if (training.is_mandatory) {
                badges.push('<span class="inline-flex items-center rounded-full bg-red-500/80 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">Mandatory</span>');
            }
            $('#detailHeroBadges').html(badges.join(''));

            if (training.description) {
                $('#detailDescriptionText').text(training.description).removeClass('hidden');
            } else {
                $('#detailDescriptionText').addClass('hidden');
            }

            const $list = $('#detailScheduleList').empty();

            // Group by docid (a distinct HR "Add Schedule" batch — same level,
            // speaker, poster) rather than a flat date list, since two batches
            // can share a level but are still separate schedules with their
            // own docid/quota.
            const groups = new Map();
            (training.schedules || []).forEach((s) => {
                const key = s.docid || 'unknown';
                if (!groups.has(key)) groups.set(key, []);
                groups.get(key).push(s);
            });

            groups.forEach((scheds, docid) => {
                const $group = $(`
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between gap-2 bg-gray-50 px-4 py-2 dark:bg-gray-900">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-white">${scheds[0].grade_name ?? '-'}</h4>
                            <span class="text-[11px] font-medium text-gray-400">${docid} &nbsp;·&nbsp; ${scheds.length} schedule(s)</span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700"></div>
                    </div>
                `);
                const $rows = $group.find('.divide-y');

                scheds.forEach((s) => {
                    const d = new Date(s.schedule_date);
                    const day = d.toLocaleDateString('en-US', { day: '2-digit' });
                    const month = d.toLocaleDateString('en-US', { month: 'short' }).toUpperCase();
                    const year = d.getFullYear();

                    const quotaHtml = !s.level_match
                        ? '<p class="text-xs text-gray-400">Your Level can\'t Register to This Training</p>'
                        : s.eligible_companies.length
                            ? s.eligible_companies.map((c) => capacityBar(c)).join('')
                            : '<p class="text-xs text-gray-400">Not available for your company</p>';

                    let actionHtml;
                    if (s.my_status) {
                        actionHtml = myStatusChip(s.my_status);
                    } else if (!s.is_open) {
                        actionHtml = '<span class="text-xs text-gray-400">Registration closed</span>';
                    } else if (!s.level_match || !s.eligible_companies.length) {
                        actionHtml = '';
                    } else {
                        const anyAvailable = s.eligible_companies.some((c) => c.available > 0);
                        const btnCls = anyAvailable ? 'bg-gray-900 hover:bg-gray-700 dark:bg-white dark:text-gray-900' : 'bg-sky-600 hover:bg-sky-500 text-white';
                        const btnText = anyAvailable ? 'Register' : 'Join Waiting List';
                        actionHtml = `<button class="registerScheduleBtn rounded-lg px-4 py-2 text-xs font-semibold text-white ${btnCls}" data-id="${s.id}" data-docid="${training.docid}">${btnText}</button>`;
                    }

                    $rows.append(`
                        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                            <div class="flex shrink-0 flex-col items-center justify-center rounded-lg bg-gray-50 px-4 py-2 dark:bg-gray-900">
                                <span class="text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">${month}</span>
                                <span class="text-2xl font-bold leading-tight text-gray-800 dark:text-white">${day}</span>
                                <span class="text-[10px] text-gray-400">${year}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-sm font-semibold text-gray-800 dark:text-white">${s.start_time ?? ''}-${s.end_time ?? ''}</span>
                                <div class="mt-1 flex items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>📍 ${s.mode ?? '-'}${s.location || s.platform ? ' · ' + (s.location || s.platform) : ''}</span>
                                    <span>🗣️ ${s.speaker_name ?? '-'}</span>
                                </div>
                                <div class="mt-2">${quotaHtml}</div>
                            </div>
                            <div class="flex shrink-0 items-center justify-end sm:w-40">${actionHtml}</div>
                        </div>
                    `);
                });

                $list.append($group);
            });

            const targetPath = '/training-list/' + training.eid;
            if (location.pathname !== targetPath) {
                history.pushState({ trainingList: true }, '', targetPath);
            }
            $('#detailModal').removeClass('hidden').addClass('flex');
        }

        function closeDetailModal() {
            $('#detailModal').addClass('hidden').removeClass('flex');
            if (location.pathname !== '{{ route('training-list', [], false) }}') {
                history.pushState({ trainingList: true }, '', '{{ route('training-list', [], false) }}');
            }
        }

        $(document).on('click', '.viewDetailBtn', function () {
            const training = cardsByDocid[$(this).data('docid')];
            if (training) openDetailModal(training);
        });

        $('#closeDetailModalX').on('click', closeDetailModal);
        window.addEventListener('popstate', function () {
            if (!$('#detailModal').hasClass('hidden')) closeDetailModal();
        });

        $('#detailHeroFullPreviewBtn').on('click', function () {
            const posterUrl = $(this).data('poster');
            if (!posterUrl) return;

            Swal.fire({
                imageUrl: posterUrl,
                imageAlt: 'Training poster',
                showConfirmButton: false,
                showCloseButton: true,
                width: 'min(90vw, 720px)',
                customClass: { popup: 'posterPreviewPopup' },
            });
        });

        const selfUsername = @json(Auth::user()->username);

        function submitRegistration(scheduleId, participants, closeDetail) {
            $.ajax({
                url: `/training-list/${scheduleId}/register`,
                method: 'POST',
                headers: csrfHeaders,
                data: { participants: participants },
                success: function (res) {
                    toast(res.success ? 'success' : 'error', res.message);
                    if (res.success) {
                        loadAvailable();
                        if (closeDetail) closeDetailModal();
                    }
                },
                error: function (xhr) {
                    toast('error', xhr.responseJSON?.message || 'Gagal melakukan registrasi');
                },
            });
        }

        // Batch registration modal: you are always included, plus any
        // colleagues from the same origin company & department (searchable).
        // A live preview lists everyone before the batch is submitted.
        function openColleaguePicker(training, sched, closeDetail) {
            const thumbHtml = training.poster_url
                ? `<img class="ticketModal-thumb" src="${training.poster_url}">`
                : `<div class="ticketModal-thumbFallback">🎓</div>`;

            const hint = myCompanyNameGlobal && myDepartmentNameGlobal
                ? ` (${myCompanyNameGlobal} · ${myDepartmentNameGlobal})`
                : '';

            const html = `
                <div class="ticketModal-header">
                    ${thumbHtml}
                    <div style="min-width:0;">
                        <h3 class="ticketModal-title">${training.training_name ?? ''}</h3>
                        <p class="ticketModal-subtitle">${fmtDate(sched.schedule_date)} · ${sched.grade_name ?? ''} · ${sched.mode ?? ''}</p>
                    </div>
                </div>
                <div class="ticketModal-body">
                    <div class="ticketModal-card">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="swalAddColleagues" style="width:16px;height:16px;">
                            <span class="ticketModal-label" style="margin:0;">👥 Also register colleagues${hint}</span>
                        </label>
                        <div id="swalColleaguesWrap" style="margin-top:10px;display:none;">
                            <select id="swalColleagues" multiple></select>
                            <p style="font-size:11px;color:#6b7280;margin-top:8px;">
                                Search and add colleagues to register them in the same batch.
                            </p>
                        </div>
                        <div id="swalPreview" class="ticketModal-capacity"></div>
                    </div>
                </div>
            `;

            Swal.fire({
                html,
                width: 480,
                showCancelButton: true,
                confirmButtonText: 'Confirm Registration',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'ticketModalPopup', confirmButton: 'ticketConfirmBtn', cancelButton: 'ticketCancelBtn' },
                didOpen: () => {
                    const $popup = $(Swal.getPopup());
                    const $toggle = $popup.find('#swalAddColleagues');
                    const $wrap = $popup.find('#swalColleaguesWrap');
                    const $sel = $popup.find('#swalColleagues');

                    $sel.select2({
                        dropdownParent: $popup,
                        width: '100%',
                        multiple: true,
                        placeholder: 'Search colleagues...',
                        allowClear: true,
                        minimumInputLength: 1,
                        ajax: {
                            url: colleaguesUrl,
                            dataType: 'json',
                            delay: 250,
                            data: (params) => ({ q: params.term || '' }),
                            processResults: (res) => ({
                                results: (res.data || []).map((c) => ({
                                    id: c.username,
                                    text: `${c.name} (${c.username})`,
                                })),
                            }),
                        },
                    });

                    const renderPreview = () => {
                        const list = $toggle.is(':checked') ? [selfUsername, ...($sel.val() || [])] : [selfUsername];
                        $popup.find('#swalPreview').html(list.map((u) => `
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border:1px solid #f0f1f3;border-radius:8px;margin-top:6px;background:#f9fafb;">
                                <span style="font-size:12px;font-weight:600;color:#111827;">${u}</span>
                                <span style="font-size:10px;color:#6b7280;">${u === selfUsername ? 'You' : 'Colleague'}</span>
                            </div>
                        `).join(''));
                    };

                    // Colleague search stays hidden/inert until the checkbox is ticked,
                    // so nothing can be picked without deliberately opting in first.
                    $toggle.on('change', () => {
                        const checked = $toggle.is(':checked');
                        $wrap.toggle(checked);
                        if (!checked) $sel.val(null).trigger('change');
                        renderPreview();
                    });

                    $sel.on('change', renderPreview);
                    renderPreview();
                },
                preConfirm: () => {
                    const $popup = $(Swal.getPopup());
                    const $sel = $popup.find('#swalColleagues');
                    const addColleagues = $popup.find('#swalAddColleagues').is(':checked');
                    return { participants: addColleagues ? [selfUsername, ...($sel.val() || [])] : [selfUsername] };
                },
            }).then((result) => {
                if (!result.isConfirmed) return;
                submitRegistration(sched.id, result.value.participants, closeDetail);
            });
        }

        $(document).on('click', '.registerScheduleBtn', function () {
            const scheduleId = $(this).data('id');
            const training = cardsByDocid[$(this).data('docid')];
            if (!training) return;
            const sched = (training.schedules || []).find((s) => String(s.id) === String(scheduleId));
            if (!sched) return;

            openColleaguePicker(training, sched, true);
        });

        $(document).on('click', '.registerBtn', function () {
            const docid = $(this).data('docid');
            const training = cardsByDocid[docid];
            if (!training) return;

            const openSchedules = training.schedules.filter((s) => !s.my_status && s.is_open && s.level_match);

            const thumbHtml = training.poster_url
                ? `<img class="ticketModal-thumb" src="${training.poster_url}">`
                : `<div class="ticketModal-thumbFallback">🎓</div>`;

            const badges = [];
            if (training.category_name) {
                badges.push(`<span class="ticketModal-badge neutral">${training.category_name}</span>`);
            }
            if (training.training_type) {
                badges.push(`<span class="ticketModal-badge info">${training.training_type}</span>`);
            }
            if (training.is_mandatory) {
                badges.push('<span class="ticketModal-badge mandatory">Mandatory</span>');
            }

            const dateCardsHtml = openSchedules.map((s, idx) => {
                const totalAvail = s.eligible_companies.reduce((sum, c) => sum + c.available, 0);
                const anyAvail = totalAvail > 0;
                const availLabel = !s.eligible_companies.length
                    ? '<span style="color:#9ca3af;">Not for your company</span>'
                    : anyAvail
                        ? `<span style="color:#15803d;">🟢 ${totalAvail} seats left</span>`
                        : '<span style="color:#b45309;">🟡 Waitlist only</span>';

                return `
                    <div class="dateCardOption${idx === 0 ? ' selected' : ''}" data-id="${s.id}">
                        <div style="font-size:13px;font-weight:700;color:#111827;">${fmtDate(s.schedule_date)}</div>
                        <div style="font-size:11px;color:#6b7280;margin-top:1px;">${s.start_time ?? ''}-${s.end_time ?? ''} · ${s.grade_name ?? ''}</div>
                        <div style="font-size:11px;font-weight:700;margin-top:5px;">${availLabel}</div>
                    </div>
                `;
            }).join('');

            let html = `
                <div class="ticketModal-header">
                    ${thumbHtml}
                    <div style="min-width:0;">
                        <h3 class="ticketModal-title">${training.training_name ?? ''}</h3>
                        <div class="ticketModal-badges">${badges.join('')}</div>
                    </div>
                </div>

                <div class="ticketModal-body">
                    <label class="ticketModal-label">🎟️ Select a Session</label>
                    <div id="swalDateCards" class="ticketModal-sessionGrid">${dateCardsHtml}</div>
                    <input type="hidden" id="swalDate" value="${openSchedules[0]?.id ?? ''}">
                </div>
            `;

            Swal.fire({
                html,
                width: 480,
                showCancelButton: true,
                confirmButtonText: 'Next',
                cancelButtonText: 'Cancel',
                buttonsStyling: true,
                reverseButtons: false,
                customClass: { popup: 'ticketModalPopup', confirmButton: 'ticketConfirmBtn', cancelButton: 'ticketCancelBtn' },
                didOpen: () => {
                    const $popup = $(Swal.getPopup());
                    $popup.find('.dateCardOption').on('click', function () {
                        $popup.find('.dateCardOption').removeClass('selected');
                        $(this).addClass('selected');
                        $popup.find('#swalDate').val($(this).data('id'));
                    });
                },
                preConfirm: () => {
                    const scheduleId = document.getElementById('swalDate').value;
                    const sched = openSchedules.find((s) => String(s.id) === scheduleId);
                    return { sched };
                },
            }).then((result) => {
                if (!result.isConfirmed) return;
                openColleaguePicker(training, result.value.sched, false);
            });
        });

        let myRegistrationsRows = [];

        function feedbackMenuItem(r) {
            if (r.can_fill_feedback) {
                const label = r.feedback_submitted ? 'Edit Feedback' : 'Fill Feedback';
                return `<button type="button" class="fillFeedbackBtn flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/20" data-id="${r.id}">📝 ${label}</button>`;
            }
            if (r.feedback_submitted) {
                return `<button type="button" class="fillFeedbackBtn flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700" data-id="${r.id}">👁 View Feedback</button>`;
            }
            if (r.has_attended) {
                return `<span class="block px-3 py-2 text-xs text-gray-400">Feedback not open yet</span>`;
            }
            return '';
        }

        function certificateMenuItem(r) {
            if (r.can_view_certificate) {
                return `<a href="${certificateUrl.replace('__ID__', r.id)}" target="_blank" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-amber-700 transition hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/20">🎓 Certificate</a>`;
            }
            if (r.has_attended) {
                return `<span class="block px-3 py-2 text-xs text-gray-400">Certificate not available yet</span>`;
            }
            return '';
        }

        let minePage = 1;
        let initialMyEidHandled = false;

        function loadMine() {
            $.get(myUrl, function (res) {
                myRegistrationsRows = res.data || [];
                minePage = 1;
                renderMine();

                if (!initialMyEidHandled && initialMyEid) {
                    initialMyEidHandled = true;
                    const match = myRegistrationsRows.find((row) => row.eid === initialMyEid);
                    if (match) openMyViewModal(match, { pushUrl: false });
                }
            });
        }

        function renderMine() {
            const rows = myRegistrationsRows;
            $('#mineEmpty').toggleClass('hidden', rows.length > 0);
            const $body = $('#mineBody').empty();

            const { pageRows, page, totalPages } = paginateRows(rows, minePage);
            minePage = page;

            pageRows.forEach(function (r) {
                const cancelHtml = (isHcdevaccess && !['R', 'X'].includes(r.status) && !isDateStrPast(r.schedule_date))
                    ? `<button type="button" class="cancelBtn flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20" data-id="${r.id}">🗑 Cancel</button>`
                    : '';
                const feedbackHtml = feedbackMenuItem(r);
                const certificateHtml = certificateMenuItem(r);
                const viewHtml = `<button type="button" class="viewRegBtn flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700" data-id="${r.id}">👁 View</button>`;

                $body.append(`
                    <tr>
                        <td class="py-2 pr-4" data-label="Doc ID"><button type="button" class="viewRegBtn inline-flex items-center gap-1 rounded-lg border border-gray-300 px-2.5 py-1 font-mono text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700" data-id="${r.id}">${r.docid}</button></td>
                        <td class="py-2 pr-4 wrap-break-word text-sm text-gray-800 dark:text-gray-100" data-label="Training">${r.training_name ?? '-'}</td>
                        <td class="py-2 pr-4 whitespace-nowrap" data-label="Level">${r.grade_name ?? '-'}</td>
                        <td class="py-2 pr-4 wrap-break-word" data-label="Speaker">${r.speaker_name ?? '-'}</td>
                        <td class="py-2 pr-4 whitespace-nowrap" data-label="Date">${fmtDate(r.schedule_date)}</td>
                        <td class="py-2 pr-4" data-label="Status">${statusBadge(r.status)}</td>
                        <td class="py-2 pr-4" data-label="Actions">
                            <div class="relative inline-block text-left" x-data="{ open: false, top: 0, left: 0 }" @click.outside="open = false">
                                <button type="button" @click="
                                        const b = \$el.getBoundingClientRect();
                                        top = b.bottom + window.scrollY + 4;
                                        left = b.right + window.scrollX - 192;
                                        open = !open;
                                    "
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Actions
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <template x-teleport="body">
                                    <div x-show="open" x-transition style="display:none;" @click="open = false"
                                        :style="'position:absolute; top:' + top + 'px; left:' + left + 'px;'"
                                        class="z-50 w-48 origin-top-right overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                        ${viewHtml}
                                        ${feedbackHtml}
                                        ${certificateHtml}
                                        ${cancelHtml}
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                `);
            });

            renderPagination('minePagination', rows.length, page, totalPages, (p) => {
                minePage = p;
                renderMine();
            });
        }

        $(document).on('click', '.cancelBtn', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Cancel this registration?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel it',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: cancelUrlTpl.replace('__ID__', id),
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message);
                        if (res.success) loadMine();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal membatalkan registrasi');
                    },
                });
            });
        });

        function approvalStatusMeta(status) {
            switch (status) {
                case 'A': return { label: 'Approved', cls: 'approved', icon: '✓' };
                case 'R': return { label: 'Rejected', cls: 'rejected', icon: '✕' };
                case 'P': return { label: 'Waiting', cls: 'pending', icon: '…' };
                default: return { label: status || '-', cls: 'neutral', icon: '•' };
            }
        }

        function fmtDateTime(d) {
            if (!d) return null;
            return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        function renderApprovalLineHtml(rows) {
            if (!rows.length) {
                return `<p class="text-xs text-gray-400">No approval line configured for this document.</p>`;
            }
            return rows.map((step) => {
                const meta = approvalStatusMeta(step.status);
                const whenLabel = (step.status === 'A' || step.status === 'R')
                    ? fmtDateTime(step.aprv_dateafter)
                    : (step.aprv_datebefore ? 'Since ' + fmtDateTime(step.aprv_datebefore) : null);

                return `
                    <div class="approvalStep">
                        <div class="approvalStep-marker ${meta.cls}">${meta.icon}</div>
                        <div class="approvalStep-body">
                            <div class="approvalStep-top">
                                <span class="approvalStep-level">Level ${step.aprv_leveling}</span>
                                <span class="approvalStep-badge ${meta.cls}">${meta.label}</span>
                            </div>
                            <div class="approvalStep-name">${step.aprv_name ?? '-'}</div>
                            ${whenLabel ? `<div class="approvalStep-when">${whenLabel}</div>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }

        let myViewModalActive = false;

        function openMyViewModal(r, { pushUrl = true, urlTpl = myViewUrlTpl } = {}) {
            if (pushUrl && r.eid) {
                const targetPath = urlTpl.replace('__EID__', r.eid);
                if (location.pathname !== targetPath) {
                    history.pushState({ trainingMyView: true }, '', targetPath);
                }
            }
            myViewModalActive = true;

            const scheduleLabel = r.start_time
                ? `${fmtDate(r.schedule_date)} · ${r.start_time}-${r.end_time ?? ''}`
                : fmtDate(r.schedule_date);
            const modeLabel = r.mode
                ? `${r.mode}${r.location || r.platform ? ' · ' + (r.location || r.platform) : ''}`
                : '-';

            const infoRows = [
                ['Status', statusBadge(r.status)],
                ['Schedule', scheduleLabel],
                ['Mode / Location', modeLabel],
                ['Speaker', r.speaker_name || '-'],
                ['Level', r.grade_name || '-'],
            ];
            const infoHtml = infoRows.map(([k, v]) => `
                <div class="viewModal-row">
                    <span class="viewModal-key">${k}</span>
                    <span class="viewModal-value">${v}</span>
                </div>
            `).join('');

            Swal.fire({
                html: `
                    <div class="viewModal-header">
                        <div class="viewModal-icon">🎓</div>
                        <div style="min-width:0;">
                            <p class="viewModal-title">${r.training_name ?? '-'}</p>
                            <p class="viewModal-subtitle">${r.docid}</p>
                        </div>
                    </div>
                    <div class="viewModal-body">
                        <div class="viewModal-card">${infoHtml}</div>
                        <h4 class="viewModal-sectionTitle">Approval Line</h4>
                        <div id="viewModalApprovalList" class="approvalStepList">
                            <p class="text-xs text-gray-400">Loading…</p>
                        </div>
                    </div>
                `,
                confirmButtonText: 'Close',
                showCancelButton: false,
                customClass: { popup: 'viewModalPopup', confirmButton: 'ticketConfirmBtn' },
                didOpen: () => {
                    $.get(approvalUrlTpl.replace('__REF__', r.docid))
                        .done((res) => {
                            $('#viewModalApprovalList').html(renderApprovalLineHtml(res.data || []));
                        })
                        .fail(() => {
                            $('#viewModalApprovalList').html('<p class="text-xs text-red-500">Failed to load approval line.</p>');
                        });
                },
                didClose: () => {
                    myViewModalActive = false;
                    if (location.pathname !== trainingListPath) {
                        history.pushState({ trainingList: true }, '', trainingListPath);
                    }
                },
            });
        }

        $(document).on('click', '.viewRegBtn', function () {
            const id = $(this).data('id');
            const r = myRegistrationsRows.find((row) => row.id === id);
            if (!r) return;
            openMyViewModal(r);
        });

        window.addEventListener('popstate', function () {
            if (myViewModalActive) Swal.close();
        });

        let pendingApprovalRows = [];
        let approvalsPage = 1;

        // Tab only appears once this actually finds something waiting on the
        // current user — called on initial page load (not just when the tab
        // is clicked) so the tab can decide its own visibility up front.
        function loadPendingApprovals() {
            $.get(pendingApprovalsUrl, function (res) {
                pendingApprovalRows = res.data || [];
                approvalsPage = 1;

                $('#approvalsTabBtn').toggleClass('hidden', pendingApprovalRows.length === 0);
                $('#approvalsTabCount').text(pendingApprovalRows.length || '');

                renderPendingApprovals();
            });
        }

        function renderPendingApprovals() {
            const rows = pendingApprovalRows;
            $('#approvalsEmpty').toggleClass('hidden', rows.length > 0);
            const $body = $('#approvalsBody').empty();

            const { pageRows, page, totalPages } = paginateRows(rows, approvalsPage);
            approvalsPage = page;

            pageRows.forEach(function (r) {
                $body.append(`
                    <tr>
                        <td class="py-2 pr-4 font-mono text-xs" data-label="Doc ID">${r.docid}</td>
                        <td class="py-2 pr-4" data-label="Employee">
                            <span class="block text-xs font-semibold text-gray-800 dark:text-gray-100">${r.name ?? r.username}</span>
                            <span class="block text-[11px] text-gray-400">${r.username}</span>
                        </td>
                        <td class="py-2 pr-4" data-label="Company / Dept">${r.cpny_name ?? r.cpny_id} / ${r.department_name ?? r.department_id}</td>
                        <td class="py-2 pr-4" data-label="Training">${r.training_name ?? '-'}</td>
                        <td class="py-2 pr-4 whitespace-nowrap" data-label="Schedule Date">${fmtDate(r.schedule_date)}</td>
                        <td class="py-2 pr-4 whitespace-nowrap" data-label="Waiting Since">${fmtDate(r.waiting_since)}</td>
                        <td class="py-2 pr-4" data-label="Action">
                            <div class="flex items-center gap-1.5">
                                <button class="approveRegBtn rounded-lg bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-700" data-id="${r.id}">Approve</button>
                                <button class="rejectRegBtn rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-900/20" data-id="${r.id}">Reject</button>
                            </div>
                        </td>
                    </tr>
                `);
            });

            renderPagination('approvalsPagination', rows.length, page, totalPages, (p) => {
                approvalsPage = p;
                renderPendingApprovals();
            });
        }

        $(document).on('click', '.approveRegBtn, .rejectRegBtn', function () {
            const id = $(this).data('id');
            const isApprove = $(this).hasClass('approveRegBtn');
            const r = pendingApprovalRows.find((x) => String(x.id) === String(id));

            Swal.fire({
                html: `
                    <div class="approveModal-header">
                        <div class="approveModal-icon ${isApprove ? 'approve' : 'reject'}">${isApprove ? '✓' : '✕'}</div>
                        <h3 class="approveModal-title">${isApprove ? 'Approve this registration?' : 'Reject this registration?'}</h3>
                    </div>
                    ${r ? `
                        <div class="approveModal-card">
                            <div class="approveModal-row"><span class="approveModal-key">Doc ID</span><span class="approveModal-value">${r.docid}</span></div>
                            <div class="approveModal-row"><span class="approveModal-key">Employee</span><span class="approveModal-value">${r.name ?? r.username}</span></div>
                            <div class="approveModal-row"><span class="approveModal-key">Training</span><span class="approveModal-value">${r.training_name ?? '-'}</span></div>
                        </div>
                    ` : ''}
                `,
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: isApprove ? 'Yes, approve' : 'Yes, reject',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'approveModalPopup',
                    confirmButton: isApprove ? 'approveConfirmBtn' : 'rejectConfirmBtn',
                    cancelButton: 'ticketCancelBtn',
                },
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/training-list/${id}/${isApprove ? 'approve' : 'reject'}`,
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        const message = isApprove
                            ? (res.completed ? 'Registrasi disetujui sepenuhnya' : 'Disetujui, menunggu approver berikutnya')
                            : 'Registrasi ditolak';
                        toast('success', message);
                        loadPendingApprovals();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal memproses approval');
                    },
                });
            });
        });

        function renderFeedbackQuestion(q, readOnly) {
            const name = `feedback_q_${q.question_order}`;

            let inputHtml = '';
            if (q.question_type === 'Single Choice') {
                inputHtml = `<div class="feedbackModal-choiceGroup">` + (q.options || []).map((opt) => `
                    <label class="feedbackModal-choice">
                        <input type="radio" name="${name}" value="${opt}" ${q.answer_text === opt ? 'checked' : ''} ${readOnly ? 'disabled' : ''}>
                        ${opt}
                    </label>
                `).join('') + `</div>`;
            } else if (q.question_type === 'Rating') {
                const options = q.options || [];
                inputHtml = `
                    <div class="feedbackModal-ratingGroup">` + options.map((opt) => `
                        <label class="feedbackModal-ratingItem">
                            <input type="radio" name="${name}" value="${opt}" ${String(q.answer_number ?? '') === String(opt) ? 'checked' : ''} ${readOnly ? 'disabled' : ''}>
                            ${opt}
                        </label>
                    `).join('') + `</div>
                    <div class="feedbackModal-ratingScale">
                        <span>Sangat tidak puas</span>
                        <span>Sangat puas</span>
                    </div>
                `;
            } else {
                inputHtml = `<textarea class="feedbackModal-textarea" name="${name}" rows="2" ${readOnly ? 'disabled' : ''}>${q.answer_text ?? ''}</textarea>`;
            }

            return `
                <div class="feedbackModal-question">
                    <div class="feedbackModal-qHead">
                        <span class="feedbackModal-qNum">${q.question_order}</span>
                        <span class="feedbackModal-qText">${q.question_text}</span>
                    </div>
                    ${inputHtml}
                </div>
            `;
        }

        function openFeedbackModal(row) {
            $.get(`/training-list/my/${row.id}/feedback`, function (res) {
                const readOnly = !res.is_open;
                const questions = res.questions || [];
                const questionsHtml = questions.map((q) => renderFeedbackQuestion(q, readOnly)).join('');
                const notice = readOnly
                    ? `<div class="feedbackModal-notice">⚠️ Feedback window is currently closed — showing your submitted answers (read only).</div>`
                    : '';

                Swal.fire({
                    html: `
                        <div class="feedbackModal-header">
                            <div class="feedbackModal-icon">📝</div>
                            <div>
                                <h3 class="feedbackModal-title">${readOnly ? 'Feedback' : 'Fill Feedback'}</h3>
                                <p class="feedbackModal-subtitle">${row.training_name ?? ''}</p>
                            </div>
                        </div>
                        <div class="feedbackModal-body">
                            ${notice}
                            <div>${questionsHtml}</div>
                        </div>
                    `,
                    showCancelButton: !readOnly,
                    buttonsStyling: false,
                    confirmButtonText: readOnly ? 'Close' : 'Submit',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'feedbackModalPopup',
                        confirmButton: 'feedbackConfirmBtn',
                        cancelButton: 'ticketCancelBtn',
                    },
                    preConfirm: () => {
                        if (readOnly) return true;

                        return questions.map((q) => {
                            const name = `feedback_q_${q.question_order}`;
                            const el = document.querySelector(`input[name="${name}"]:checked, textarea[name="${name}"]`);
                            return { question_order: q.question_order, value: el ? el.value : null };
                        });
                    },
                }).then((result) => {
                    if (readOnly || !result.isConfirmed) return;

                    $.ajax({
                        url: `/training-list/my/${row.id}/feedback`,
                        method: 'POST',
                        headers: csrfHeaders,
                        data: { answers: result.value },
                        success: function (subRes) {
                            toast(subRes.success ? 'success' : 'error', subRes.message);
                            if (subRes.success) loadMine();
                        },
                        error: function (xhr) {
                            toast('error', xhr.responseJSON?.message || 'Gagal menyimpan feedback');
                        },
                    });
                });
            }).fail(function (xhr) {
                toast('error', xhr.responseJSON?.message || 'Gagal memuat feedback');
            });
        }

        $(document).on('click', '.fillFeedbackBtn', function () {
            const id = $(this).data('id');
            const row = myRegistrationsRows.find((r) => String(r.id) === String(id));
            if (row) openFeedbackModal(row);
        });

        @if (Auth::user()->hasRole('HCDEVACCESS'))
        $('#allRegsTrainingFilter').select2({
            containerCssClass: 'select2-filter',
            dropdownCssClass: 'select2-filter',
            placeholder: 'All Training Events',
            allowClear: true,
            width: '100%',
            // Full name as a title tooltip since long training names truncate
            // in the box itself.
            templateSelection: (data) => $('<span></span>').text(data.text).attr('title', data.text),
        });
        $('#allRegsStatusFilter').select2({
            containerCssClass: 'select2-filter',
            dropdownCssClass: 'select2-filter',
            minimumResultsForSearch: -1,
            width: '100%',
        });

        let allRegistrationRows = [];
        let allRegsPage = 1;
        let initialAllRegsEidHandled = false;

        function loadAllRegistrations() {
            $.get(allRegistrationsUrl, function (res) {
                allRegistrationRows = res.data || [];
                allRegsPage = 1;
                renderAllRegistrations();

                if (!initialAllRegsEidHandled && initialAllRegsEid) {
                    initialAllRegsEidHandled = true;
                    const match = allRegistrationRows.find((row) => row.eid === initialAllRegsEid);
                    if (match) openMyViewModal(match, { pushUrl: false, urlTpl: allRegsViewUrlTpl });
                }
            });
            loadRegistrationSummary();
        }

        // Cards are scoped only by the Training Event filter (a dedicated
        // backend fetch, since quota totals aren't derivable from the
        // registration rows alone) — search/status stay table-only filters
        // so the overview cards keep reading as a stable summary.
        function loadRegistrationSummary() {
            const trainingId = $('#allRegsTrainingFilter').val();

            $.get(registrationSummaryUrl, trainingId ? { training_id: trainingId } : {}, function (res) {
                populateTrainingFilterOptions(res.trainings || []);
                renderSummaryCards(res);
            });
        }

        function populateTrainingFilterOptions(trainings) {
            const $select = $('#allRegsTrainingFilter');
            const current = $select.val();

            $select.find('option:not(:first)').remove();
            trainings.forEach((t) => $select.append(new Option(t.training_name, t.training_id)));

            if (current && trainings.some((t) => String(t.training_id) === current)) {
                $select.val(current);
            }

            // Re-sync select2's rendered box after the underlying <select>'s
            // options changed programmatically — namespaced so it doesn't
            // re-fire the plain 'change' handler below (which would re-fetch
            // the summary and recurse back into this function).
            $select.trigger('change.select2');
        }

        function renderSummaryCards(res) {
            const counts = res.status_counts || {};
            $('#statWaitingApproval').text(counts.waiting_approval ?? 0);
            $('#statWaitingList').text(counts.waiting_list ?? 0);
            $('#statApproved').text(counts.approved ?? 0);
            $('#statRejected').text(counts.rejected ?? 0);
            $('#statCancelled').text(counts.cancelled ?? 0);

            const overall = res.overall || { reserved: 0, total_quota: 0 };
            const overallPct = overall.total_quota > 0 ? Math.min(100, Math.round((overall.reserved / overall.total_quota) * 100)) : 0;
            $('#quotaOverallValue').text(`${overall.reserved} / ${overall.total_quota}`);
            $('#quotaOverallBar').css('width', `${overallPct}%`).css('background', overallPct >= 100 ? '#dc2626' : '');

            const byCompany = res.by_company || [];
            $('#quotaByCompanyEmpty').toggleClass('hidden', byCompany.length > 0);
            const $grid = $('#quotaByCompany').empty();

            byCompany.forEach((c) => {
                const pct = c.total_quota > 0 ? Math.min(100, Math.round((c.reserved / c.total_quota) * 100)) : 0;
                const barColor = pct >= 100 ? '#dc2626' : '#111827';
                $grid.append(`
                    <div class="min-w-40 flex-1 rounded-lg border border-gray-100 p-2.5 dark:border-gray-700">
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="truncate font-semibold text-gray-700 dark:text-gray-200" title="${c.cpny_name}">${c.cpny_name}</span>
                            <span class="shrink-0 font-medium text-gray-500 dark:text-gray-400">${c.reserved}/${c.total_quota}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="h-full rounded-full" style="width:${pct}%;background:${barColor};"></div>
                        </div>
                    </div>
                `);
            });
        }

        function renderAllRegistrations() {
            const search = ($('#allRegsSearch').val() || '').toLowerCase().trim();
            const statusFilter = $('#allRegsStatusFilter').val();
            const trainingFilter = $('#allRegsTrainingFilter').val();

            const rows = allRegistrationRows.filter((r) => {
                if (statusFilter && r.status !== statusFilter) return false;
                if (trainingFilter && String(r.training_id) !== trainingFilter) return false;
                if (search) {
                    const haystack = `${r.docid} ${r.name} ${r.username} ${r.training_name ?? ''}`.toLowerCase();
                    if (!haystack.includes(search)) return false;
                }
                return true;
            });

            $('#allRegsEmpty').toggleClass('hidden', rows.length > 0);
            const $body = $('#allRegsBody').empty();

            const { pageRows, page, totalPages } = paginateRows(rows, allRegsPage);
            allRegsPage = page;

            pageRows.forEach(function (r) {
                const viewHtml = `<button type="button" class="allRegsViewBtn flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700" data-id="${r.id}">👁 View</button>`;

                const acceptHtml = r.can_accept
                    ? `<button type="button" class="allRegsAcceptBtn flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-green-600 transition hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20" data-id="${r.id}">✅ Accept</button>`
                    : '';

                // Same guard as TrainingRegistrationController::cancel(): a
                // Rejected/already-Cancelled row has nothing left to cancel,
                // and a past schedule date can no longer be backed out of.
                const canCancel = !['R', 'X'].includes(r.status) && !isDateStrPast(r.schedule_date);
                const cancelHtml = canCancel
                    ? `<button type="button" class="allRegsCancelBtn flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20" data-id="${r.id}">🗑 Cancel</button>`
                    : '';

                // Approval progress, distinct from the combined Status column —
                // this is what decides whether Accept can show up at all for a
                // waitlisted row (see can_accept in allRegistrations()).
                const approvalHtml = r.approval_status === 'C'
                    ? '<span class="text-xs font-semibold text-green-600 dark:text-green-400">Approved</span>'
                    : r.approval_status === 'R'
                        ? '<span class="text-xs font-semibold text-red-600 dark:text-red-400">Rejected</span>'
                        : '<span class="text-xs text-amber-600 dark:text-amber-400">Pending</span>';

                $body.append(`
                    <tr>
                        <td class="py-2 pr-4 font-mono text-xs" data-label="Doc ID">${r.docid}</td>
                        <td class="py-2 pr-4" data-label="Employee">
                            <span class="block text-xs font-semibold text-gray-800 dark:text-gray-100">${r.name ?? r.username}</span>
                            <span class="block text-[11px] text-gray-400">${r.username}</span>
                        </td>
                        <td class="py-2 pr-4" data-label="Company / Dept">${r.cpny_name ?? r.cpny_id} / ${r.department_name ?? r.department_id}</td>
                        <td class="py-2 pr-4" data-label="Training">${r.training_name ?? '-'}</td>
                        <td class="py-2 pr-4 whitespace-nowrap" data-label="Level">${r.grade_name ?? '-'}</td>
                        <td class="py-2 pr-4 whitespace-nowrap" data-label="Schedule Date">${fmtDate(r.schedule_date)}</td>
                        <td class="py-2 pr-4" data-label="Training Status">${scheduleStatusBadge(r.schedule_status)}</td>
                        <td class="py-2 pr-4 whitespace-nowrap" data-label="Registered On">${fmtDate(r.registered_at)}</td>
                        <td class="py-2 pr-4" data-label="Status">${statusBadge(r.status)}</td>
                        <td class="py-2 pr-4" data-label="Approval">${approvalHtml}</td>
                        <td class="py-2 pr-4 text-center" data-label="Queue #">${r.queue_no ? `<span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">#${r.queue_no}</span>` : '-'}</td>
                        <td class="py-2 pr-4" data-label="Action">
                            <div class="relative inline-block text-left" x-data="{ open: false, top: 0, left: 0 }" @click.outside="open = false">
                                <button type="button" @click="
                                        const b = \$el.getBoundingClientRect();
                                        top = b.bottom + window.scrollY + 4;
                                        left = b.right + window.scrollX - 192;
                                        open = !open;
                                    "
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Actions
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <template x-teleport="body">
                                    <div x-show="open" x-transition style="display:none;" @click="open = false"
                                        :style="'position:absolute; top:' + top + 'px; left:' + left + 'px;'"
                                        class="z-50 w-48 origin-top-right overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                        ${viewHtml}
                                        ${acceptHtml}
                                        ${cancelHtml}
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                `);
            });

            renderPagination('allRegsPagination', rows.length, page, totalPages, (p) => {
                allRegsPage = p;
                renderAllRegistrations();
            });
        }

        $(document).on('click', '.allRegsViewBtn', function () {
            const id = $(this).data('id');
            const r = allRegistrationRows.find((row) => row.id === id);
            if (!r) return;
            openMyViewModal(r, { urlTpl: allRegsViewUrlTpl });
        });

        $(document).on('click', '.allRegsCancelBtn', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Cancel this registration?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel it',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: cancelUrlTpl.replace('__ID__', id),
                    method: 'POST',
                    headers: csrfHeaders,
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message);
                        if (res.success) loadAllRegistrations();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal membatalkan registrasi');
                    },
                });
            });
        });

        $(document).on('click', '.allRegsAcceptBtn', function () {
            const id = $(this).data('id');
            const r = allRegistrationRows.find((row) => String(row.id) === String(id));
            if (!r) return;

            const opts = (r.quota_options || []).map((q) => {
                const sel = q.cpny_id === r.cpny_id ? ' selected' : '';
                const label = `${q.cpny_name} — ${q.available}/${q.quota_pax} seats`;
                return `<option value="${q.cpny_id}"${sel}>${label}</option>`;
            }).join('');

            Swal.fire({
                title: `Accept ${r.name ?? r.username}?`,
                html: `
                    <div style="text-align:left;font-size:13px;">
                        <p><strong>Doc ID:</strong> ${r.docid}</p>
                        <p><strong>Training:</strong> ${r.training_name ?? '-'}</p>
                        <p><strong>Date:</strong> ${fmtDate(r.schedule_date)}</p>
                        <div style="margin-top:12px;">
                            <label class="ticketModal-label">🏢 Use Quota From</label>
                            <select id="swalAllRegsAcceptCpny" class="ticketModal-select">${opts}</select>
                            <p style="font-size:11px;color:#6b7280;margin-top:6px;">
                                Defaults to the participant's own company (${r.cpny_id}). Pick another company to consume its quota instead.
                            </p>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Yes, accept',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (!result.isConfirmed) return;

                const cpnyId = document.getElementById('swalAllRegsAcceptCpny')?.value ?? r.cpny_id;

                $.ajax({
                    url: `/training-list/${id}/manual-accept`,
                    method: 'POST',
                    headers: csrfHeaders,
                    data: { cpny_id: cpnyId },
                    success: function (res) {
                        toast(res.success ? 'success' : 'error', res.message);
                        if (res.success) loadAllRegistrations();
                    },
                    error: function (xhr) {
                        toast('error', xhr.responseJSON?.message || 'Gagal menerima peserta');
                    },
                });
            });
        });

        $('#allRegsSearch').on('input', function () {
            allRegsPage = 1;
            renderAllRegistrations();
        });
        $('#allRegsStatusFilter').on('change', function () {
            allRegsPage = 1;
            renderAllRegistrations();
        });
        $('#allRegsTrainingFilter').on('change', function () {
            allRegsPage = 1;
            renderAllRegistrations();
            loadRegistrationSummary();
        });
        $('#allRegsResetBtn').on('click', function () {
            $('#allRegsSearch').val('');
            $('#allRegsStatusFilter').val('').trigger('change.select2');
            $('#allRegsTrainingFilter').val('').trigger('change.select2');
            allRegsPage = 1;
            renderAllRegistrations();
            loadRegistrationSummary();
        });

        // Same training/status/search filters as the table — the download
        // matches what's currently on screen, not just the current page.
        $('#allRegsExportBtn').on('click', function () {
            const params = new URLSearchParams();
            const trainingId = $('#allRegsTrainingFilter').val();
            const status = $('#allRegsStatusFilter').val();
            const search = ($('#allRegsSearch').val() || '').trim();

            if (trainingId) params.set('training_id', trainingId);
            if (status) params.set('status', status);
            if (search) params.set('search', search);

            const qs = params.toString();
            window.location.href = allRegistrationsExportUrl + (qs ? '?' + qs : '');
        });
        @endif

        loadAvailable();
        loadPendingApprovals();

        if (initialMyEid) {
            $('.tabBtn[data-tab="mine"]').trigger('click');
        }
        if (initialAllRegsEid) {
            $('.tabBtn[data-tab="allregs"]').trigger('click');
        }
    </script>
</x-app-layout>
