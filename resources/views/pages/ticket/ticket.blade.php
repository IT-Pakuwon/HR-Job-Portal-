<x-app-layout>
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 52px !important;
            border-radius: 0.5rem !important;
            border: 1px solid rgb(226 232 240) !important;
            background: white !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 14px !important;
        }

        .dark .select2-container--default .select2-selection--single {
            background: rgb(30 41 59) !important;
            border-color: rgb(51 65 85) !important;
            color: white !important;
        }

        .select2-selection--single {
            min-height: 52px !important;
        }

        .select2-container {
            z-index: 9999 !important;
        }

        .select2-selection__arrow {
            height: 50px !important;
        }

        .select2-dropdown {
            border-radius: 0.5rem !important;
            overflow: hidden !important;
            border: 1px solid rgb(226 232 240) !important;
        }

        .dark .select2-dropdown {
            background: rgb(15 23 42) !important;
            border-color: rgb(51 65 85) !important;
        }

        .dark .select2-results__option {
            color: white !important;
        }

        .select2-search__field:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .select2-container {
            width: 100% !important;
            max-width: 100% !important;
        }

        .select2-dropdown {
            max-width: 100% !important;
        }

        .select2-results {
            overflow-x: hidden !important;
        }

        body.modal-open {
            overflow: hidden !important;
            height: 100vh !important;
        }

        .section-title {
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 10px;
            letter-spacing: .05em
        }

        .readonly-select+.select2 .select2-selection--single {
            background: #f8fafc !important;
            color: #94a3b8 !important;
            border-color: #e2e8f0 !important;
            cursor: not-allowed !important;
        }

        .dark .readonly-select+.select2 .select2-selection--single {
            background: #1e293b !important;
            color: #64748b !important;
            border-color: #334155 !important;
        }

        .readonly-select {
            background: #f8fafc !important;
            color: #94a3b8 !important;
            border-color: #e2e8f0 !important;
            cursor: not-allowed !important;
        }

        .dark .readonly-select {
            background: #1e293b !important;
            color: #64748b !important;
            border-color: #334155 !important;
        }

        .label {
            font-size: 11px;
            color: #94a3b8
        }

        .input {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
            font-size: 14px;
            background: white
        }

        .upload-box {
            display: flex;
            justify-content: center;
            padding: 20px;
            border: 1px dashed #cbd5f5;
            border-radius: 12px;
            cursor: pointer
        }

        .btn-primary {
            background: #0f172a;
            color: white;
            padding: 8px 16px;
            border-radius: 8px
        }
    </style>

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- STATUS FILTER --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- All Ticket --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter active group block h-full" data-status="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-slate-700 bg-slate-200/20 p-3 text-slate-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-slate-100 hover:shadow-md active:scale-95 dark:border-slate-600 dark:bg-slate-800/40 dark:text-slate-200">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            📄
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">
                                All Ticket
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $all }}
                        </p>

                    </div>
                </a>
            </button>

            {{-- Waiting --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="W">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-amber-700 bg-amber-200/20 p-3 text-amber-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-amber-100 hover:shadow-md active:scale-95 dark:border-amber-500 dark:bg-amber-500/10 dark:text-amber-300">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            ⏳
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">
                                Waiting
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $waiting }}
                        </p>

                    </div>
                </a>
            </button>

            {{-- Progress --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="P">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95 dark:border-blue-500 dark:bg-blue-500/10 dark:text-blue-300">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            ⚡
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">
                                Progress
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $progress }}
                        </p>

                    </div>
                </a>
            </button>

            {{-- Completed --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="C">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95 dark:border-green-500 dark:bg-green-500/10 dark:text-green-300">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            ✅
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">
                                Completed
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $completed }}
                        </p>

                    </div>
                </a>
            </button>

            {{-- Reopen --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="R">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-rose-700 bg-rose-200/20 p-3 text-rose-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-rose-100 hover:shadow-md active:scale-95 dark:border-rose-500 dark:bg-rose-500/10 dark:text-rose-300">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            🔄
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">
                                Reopen
                            </p>
                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $reopen }}
                        </p>

                    </div>
                </a>
            </button>

        </div>

        {{-- LISTING TIKET --}}
        <div
            class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">

            {{-- Header --}}
            <div class="border-b border-slate-200 p-4 dark:border-slate-700">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <h2 class="text-lg font-semibold text-slate-800 dark:text-white">
                            Ticket Listing
                        </h2>

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Monitor all ticket requests and activity progress.
                        </p>
                    </div>


                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                        {{-- Search --}}
                        <div class="relative">
                            <input type="text" id="ticketSearch" placeholder="Search ticket..."
                                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-blue-500/20">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z" />
                            </svg>
                        </div>

                        @if (auth()->user()->user_role === 'admin')
                            <a href="{{ route('ticketsetup') }}"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:hover:bg-slate-700">

                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                </svg>

                                Ticket Setup
                            </a>
                        @endif

                        {{-- Create --}}
                        <button type="button" id="btnCreateTicket"
                            class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">

                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>

                            Create Ticket
                        </button>

                    </div>

                </div>

            </div>

            {{-- CATEGORY TABS FILTER --}}
            <div class="mt-4 overflow-x-auto p-4">

                <div id="ticketCategoryTabs" class="flex min-w-max gap-2 pb-1">

                    <button type="button"
                        class="ticket-category-tab active-category-tab inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:border-slate-700 dark:bg-slate-700"
                        data-category="">

                        <span>📂</span>
                        <span>All Category</span>

                    </button>

                </div>

            </div>

            {{-- Listing --}}
            <div id="ticketListWrapper" class="divide-y divide-slate-100 dark:divide-slate-800">

            </div>

        </div>

        {{-- DETAIL MODAL --}}
        <div id="detailTicketModal"
            class="modal-scroll fixed inset-0 z-[80] hidden overflow-y-auto bg-black/30 backdrop-blur-sm">

            <div class="flex min-h-screen items-start justify-center px-4 py-4">

                <div
                    class="flex h-auto w-full max-w-8xl overflow-hidden rounded-2xl bg-white dark:bg-[#0f172a]">

                    {{-- LEFT --}}
                    <div class="flex min-w-0 flex-1 flex-col">

                        {{-- Header --}}
                        <div class="sticky top-0 z-10 bg-white/80 px-6 py-4 backdrop-blur dark:bg-[#0f172a]/80">
                            <div class="flex items-start justify-between gap-4">

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 id="detail_ticketid"
                                            class="truncate text-lg font-semibold text-slate-900 dark:text-white">-
                                        </h2>

                                        <span id="detail_priority"
                                            class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium dark:bg-white/10">
                                            -
                                        </span>

                                        <span id="detail_status"
                                            class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium dark:bg-white/10">
                                            -
                                        </span>
                                    </div>

                                    <p id="detail_type" class="mt-0.5 text-sm text-slate-600 dark:text-slate-300">-
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">

                                    <button id="primaryActionBtn"
                                        class="inline-flex hidden items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500 active:scale-95">
                                        ⚡ Start Work
                                    </button>

                                    <div class="relative">
                                        <button id="btnTicketActions"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-white/10 dark:bg-slate-800 dark:text-slate-300">
                                            Actions
                                        </button>

                                        <div id="ticketActionDropdown"
                                            class="absolute right-0 mt-2 hidden w-52 rounded-xl border border-slate-200 bg-white shadow-md dark:border-white/10 dark:bg-slate-900">
                                            <div class="py-1.5" id="ticketActionList"></div>
                                        </div>
                                    </div>

                                    <button id="closeDetailTicketModal"
                                        class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5">
                                        Close
                                    </button>

                                </div>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="min-h-0 flex-1 overflow-y-auto">

                            <div
                                class="grid grid-cols-1 gap-5 p-5 xl:grid-cols-[1.35fr_0.65fr] xl:divide-x xl:divide-slate-200 dark:xl:divide-white/10">

                                {{-- MAIN --}}
                                <div class="space-y-5 xl:pr-5">

                                    {{-- Description --}}
                                    <div
                                        class="rounded-xl border border-slate-200/70 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[0.04]">

                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-xs dark:bg-white/10">
                                                    📝
                                                </div>
                                                <h3
                                                    class="text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">
                                                    Description
                                                </h3>
                                            </div>

                                            <p id="detail_date" class="text-xs text-slate-400">-</p>
                                        </div>

                                        <div class="my-3 border-b border-slate-100 dark:border-white/5"></div>

                                        <div id="detail_descr"
                                            class="whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">
                                            -
                                        </div>
                                    </div>

                                    {{-- Information --}}
                                    <div
                                        class="rounded-xl border border-slate-200/70 bg-white/70 p-4 dark:border-white/10 dark:bg-white/[0.03]">

                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-xs dark:bg-white/10">
                                                    📌
                                                </div>
                                                <h3
                                                    class="text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">
                                                    Information
                                                </h3>
                                            </div>

                                            <div class="text-xs text-slate-400">
                                                SLA
                                                <span id="detail_sla"
                                                    class="ml-1 font-medium text-slate-700 dark:text-slate-200">-</span>
                                            </div>
                                        </div>

                                        <div class="my-3 border-b border-slate-100 dark:border-white/5"></div>

                                        <div class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">

                                            <div>
                                                <p class="text-[11px] text-slate-400">Created By</p>
                                                <p id="detail_created_by"
                                                    class="text-sm text-slate-800 dark:text-white">-</p>
                                            </div>

                                            <div>
                                                <p class="text-[11px] text-slate-400">Category</p>
                                                <p class="text-sm text-slate-800 dark:text-white">
                                                    <span id="detail_category">-</span>
                                                    <span class="mx-1 text-slate-300">—</span>
                                                    <span id="detail_subcategory">-</span>
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-[11px] text-slate-400">Location</p>
                                                <p class="text-sm text-slate-800 dark:text-white">
                                                    <span id="detail_location">-</span>
                                                    <span class="mx-1 text-slate-300">—</span>
                                                    <span id="detail_sub_location">-</span>
                                                </p>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- Timeline --}}
                                    <div
                                        class="rounded-xl border border-slate-200/70 bg-white/70 p-4 dark:border-white/10 dark:bg-white/[0.03]">

                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-xs dark:bg-white/10">
                                                    🕒
                                                </div>
                                                <h3
                                                    class="text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">
                                                    Timeline
                                                </h3>
                                            </div>

                                            <div class="text-xs text-slate-400">
                                                PIC
                                                <span id="detail_pic"
                                                    class="ml-1 text-slate-700 dark:text-slate-200">-</span>
                                            </div>
                                        </div>

                                        <div class="my-3 border-b border-slate-100 dark:border-white/5"></div>

                                        <div id="detail_tracking"
                                            class="space-y-4 text-sm text-slate-600 dark:text-slate-300">
                                            No activity
                                        </div>
                                    </div>

                                </div>

                                {{-- SIDEBAR --}}
                                <div class="space-y-5 pl-5">

                                    <div
                                        class="rounded-xl border border-slate-200/70 bg-white/70 p-4 dark:border-white/10 dark:bg-white/[0.03]">

                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-xs dark:bg-white/10">
                                                📎
                                            </div>
                                            <h3
                                                class="text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">
                                                Attachments
                                            </h3>
                                        </div>

                                        <div class="my-3 border-b border-slate-100 dark:border-white/5"></div>

                                        <div id="detail_attachments"
                                            class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                                            No attachment
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- CREATE MODAL --}}
        <div id="createTicketModal"
            class="fixed inset-0 z-[70] hidden overflow-y-auto overflow-x-hidden bg-black/40 backdrop-blur-sm">

            <div class="flex min-h-screen items-start justify-center p-3 md:items-center">

                <div
                    class="flex w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white dark:bg-[#0f172a] md:max-h-[92dvh]">

                    <!-- HEADER -->
                    <div
                        class="flex shrink-0 items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-white/10">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Create Ticket</h2>
                            <p class="text-sm text-slate-500">Submit new request</p>
                        </div>
                        <button id="closeCreateTicketModal" class="text-slate-400 hover:text-red-500">✕</button>
                    </div>

                    <form id="formCreateTicket" class="flex min-h-0 flex-1 flex-col">
                        @csrf

                        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-5">

                            <!-- BASIC -->
                            <div>
                                <div class="section-title">BASIC</div>
                                <div class="grid gap-4 md:grid-cols-2">

                                    {{-- COMPANY --}}
                                    <div>
                                        <label class="label req">Company</label>
                                        <select id="company" name="cpny_id" class="input">
                                            <option value="">Select Company</option>
                                            @foreach ($usercpny as $c)
                                                <option value="{{ $c->cpny_id }}">
                                                    {{ $c->cpny_id }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- DEPARTMENT --}}
                                    <div>
                                        <label class="label req">Department</label>
                                        <select id="department" name="department_id" class="input">
                                            <option value="">Select Department</option>
                                            @foreach ($userdept as $d)
                                                <option value="{{ $d->department_id }}">
                                                    {{ $d->department_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                            </div>

                            <!-- CLASSIFICATION -->
                            <div>
                                <div class="section-title">CLASSIFICATION</div>
                                <div class="grid gap-4 md:grid-cols-2">

                                    <div>
                                        <label class="label req">Type</label>
                                        <select id="ticket_type" name="ticket_type" class="input">
                                            <option value="">Select Type</option>
                                            @foreach ($types as $t)
                                                <option value="{{ $t->ticket_type }}">
                                                    {{ $t->ticket_type_name ?? $t->ticket_type }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="label req">Category</label>
                                        <select id="category" name="ticket_categoryid" class="input"></select>
                                    </div>

                                    <div>
                                        <label class="label req">Subcategory</label>
                                        <select id="subcategory" name="ticket_subcategoryid" class="input"></select>
                                    </div>



                                    <div>
                                        <label class="label req">Priority</label>

                                        <div id="priority_badge"
                                            class="flex h-[52px] w-full items-center rounded-lg border border-slate-200 bg-slate-50 px-[14px] text-[14px] leading-none tracking-[0.01em] text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                            -
                                        </div>

                                        <input type="hidden" name="ticket_priority" id="ticket_priority_hidden">
                                    </div>

                                </div>

                            </div>

                            <!-- LOCATION -->
                            <div>
                                <div class="section-title">LOCATION</div>
                                <div class="grid gap-4 md:grid-cols-2">

                                    <div>
                                        <label class="label req">Location</label>
                                        <select id="location" name="location_id" class="input">
                                            <option value="">Select Location</option>
                                            @foreach ($locations as $l)
                                                <option value="{{ $l->location_id }}">{{ $l->location_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="label req">Sub Location</label>
                                        <select id="sub_location" name="sub_location_id" class="input"></select>
                                    </div>

                                </div>
                            </div>

                            <!-- DETAILS -->
                            <div>
                                <div class="section-title req">DETAILS</div>

                                <input type="text" name="issue_summary" placeholder="Issue Summary"
                                    class="input req mb-3">

                                <textarea name="issue_descr" rows="4" placeholder="Describe the issue..." class="input"></textarea>
                            </div>

                            <!-- ATTACHMENT -->
                            <div>
                                <div class="section-title">ATTACHMENT</div>

                                <label for="attachments"
                                    class="upload-box flex cursor-pointer items-center justify-center gap-2 text-sm text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5">
                                    📎 <span>Select files</span>
                                </label>

                                <input type="file" id="attachments" name="attachments[]" multiple class="hidden">

                                <div id="attachmentPreview" class="mt-2 space-y-1 text-xs text-slate-500"></div>
                            </div>

                        </div>

                        <!-- FOOTER -->
                        <div
                            class="flex items-center justify-end gap-2 border-t border-slate-200 px-6 py-4 dark:border-white/10">

                            <button type="button" id="cancelCreateTicket"
                                class="inline-flex h-[38px] items-center rounded-lg border border-slate-300 px-4 text-sm text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-white/5">
                                Cancel
                            </button>

                            <button type="submit"
                                class="inline-flex h-[38px] items-center rounded-lg bg-slate-900 px-5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">
                                Submit Ticket
                            </button>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- IMAGE PREVIEW MODAL --}}
        <div id="imagePreviewModal"
            class="fixed inset-0 z-[100] flex hidden items-center justify-center bg-black/80 backdrop-blur-sm">

            <div class="relative w-full max-w-5xl px-4">

                <button id="closeImagePreview" class="absolute -top-10 right-0 text-2xl text-white">
                    ✕
                </button>

                <img id="previewImage" class="max-h-[85vh] w-full rounded-lg object-contain shadow-2xl" />

            </div>

        </div>

        {{-- TRANSFER TYPE MODAL --}}
        <div id="transferModal"
            class="fixed inset-0 z-[90] flex hidden items-center justify-center bg-black/30 backdrop-blur-sm">

            <div class="w-full max-w-lg rounded-2xl bg-white p-6 dark:bg-[#0f172a]">

                <div class="mb-5">
                    <h3 class="text-base font-semibold text-slate-800 dark:text-white">
                        Transfer Ticket
                    </h3>

                    <p class="mt-1 text-xs text-slate-500">
                        Move this ticket to another category & subcategory
                    </p>
                </div>

                {{-- CURRENT --}}
                <div class="mb-5 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">

                    <div class="text-[11px] uppercase tracking-wide text-slate-400">
                        Current
                    </div>

                    <div class="mt-2 text-sm text-slate-700 dark:text-slate-200">
                        <span id="transfer_old_category">-</span>
                        <span class="mx-2 text-slate-300">→</span>
                        <span id="transfer_old_subcategory">-</span>
                    </div>

                </div>

                {{-- NEW --}}
                <div class="space-y-4">

                    <div>
                        <div class="mb-1 text-xs text-slate-400">
                            New Category
                        </div>

                        <select id="transfer_category"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-800">
                        </select>
                    </div>

                    <div>
                        <div class="mb-1 text-xs text-slate-400">
                            New Subcategory
                        </div>

                        <select id="transfer_subcategory"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-slate-800">
                        </select>
                    </div>

                </div>

                <div class="mt-6 flex items-center justify-end gap-2">

                    <button id="cancelTransfer"
                        class="px-3 py-1.5 text-sm text-slate-500 hover:text-slate-700 dark:hover:text-white">
                        Cancel
                    </button>

                    <button id="submitTransfer"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                        Transfer Ticket
                    </button>

                </div>

            </div>
        </div>

        {{-- ASSIGN PIC MODAL --}}
        <div id="assignModal"
            class="fixed inset-0 z-[90] flex hidden items-center justify-center bg-black/30 backdrop-blur-sm">

            <div class="w-full max-w-md rounded-2xl bg-white p-6 dark:bg-[#0f172a]">

                <!-- Header -->
                <div class="mb-5">
                    <h3 class="text-base font-semibold text-slate-800 dark:text-white">
                        Assign PIC
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Update person in charge for this request
                    </p>
                </div>

                <!-- Content -->
                <div class="space-y-4">

                    <!-- CURRENT -->
                    <div>
                        <div class="mb-1 text-xs text-slate-400">Current</div>

                        <div id="current_pic_badge"
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 transition hover:bg-slate-50 dark:hover:bg-white/5">

                            <div class="h-6 w-6 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                            <span class="text-sm text-slate-700 dark:text-slate-200">-</span>
                        </div>
                    </div>

                    <!-- CHANGE -->
                    <div>
                        <div class="mb-1 text-xs text-slate-400">Change to</div>

                        <select id="assign_pic_dropdown"
                            class="w-full rounded-lg border border-transparent bg-transparent px-2 py-2 text-sm transition hover:bg-slate-50 focus:border-slate-200 focus:bg-white focus:outline-none dark:hover:bg-white/5 dark:focus:border-white/10 dark:focus:bg-slate-800">
                        </select>
                    </div>

                </div>

                <!-- Footer -->
                <div class="mt-6 flex items-center justify-end gap-2">

                    <button id="cancelAssign"
                        class="px-3 py-1.5 text-sm text-slate-500 transition hover:text-slate-700 dark:hover:text-white">
                        Cancel
                    </button>

                    <button id="submitAssign"
                        class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                        Save
                    </button>

                </div>

            </div>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    <script>
        let currentStatus = '';
        let currentSearch = '';
        let currentCategory = '';
        let currentTicketEid = null;
        let currentTicketData = null;

        $(document).on('click', '.status-filter', function(e) {
            e.preventDefault();

            $('.status-filter').removeClass('active');
            $('.status-filter .status-card').removeClass('ring-2 ring-offset-2 ring-slate-900');

            $(this).addClass('active');
            $(this).find('.status-card').addClass('ring-2 ring-offset-2 ring-slate-900');

            // get status
            currentStatus = $(this).data('status') || '';

            // reload data
            loadTickets();
        });
    </script>

    <script>
        function formatDate(dateString) {
            if (!dateString) return '-';

            const date = new Date(dateString);

            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function formatDateTime(dateString) {
            if (!dateString) return '-';

            const d = new Date(dateString);

            return d.toLocaleString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>

    <script>
        function loadTickets() {

            $('#ticketListWrapper').html(`
                <div class="p-6 text-center text-sm text-slate-400">
                    Loading tickets...
                </div>
            `);

            $.ajax({
                url: "{{ route('ticket.json') }}",
                method: "GET",
                data: {
                    status: currentStatus,
                    search: currentSearch,
                    category: currentCategory
                },
                success: function(res) {


                    const rows = res.data || [];


                    if (!rows.length) {
                        $('#ticketListWrapper').html(`
                            <div class="p-6 text-center text-sm text-slate-400">
                                No tickets found
                            </div>
                        `);
                        return;
                    }

                    let html = '';

                    rows.forEach(row => {

                        html += `
                        <div class="ticket-row group cursor-pointer px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-white/5"
                            data-eid="${row.eid}">

                            <div class="flex items-start justify-between gap-4">

                                <!-- LEFT -->
                                <div class="min-w-0 flex-1">

                                    <!-- TOP -->
                                    <div class="flex items-center gap-2 flex-wrap">

                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">
                                            ${row.ticketid}
                                        </p>

                                        <span class="rounded px-2 py-0.5 text-[11px] font-medium ${getPriorityBadge(row.ticket_priority)}">
                                            ${row.ticket_priority}
                                        </span>

                                        ${(() => {
                                            const s = getStatusBadge(row.status);
                                            return `<span class="rounded px-2 py-0.5 text-[11px] font-medium ${s.class}">
                                                                                                                                                ${s.label}
                                                                                                                                            </span>`;
                                        })()}

                                    </div>

                                    <!-- SUMMARY -->
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300 truncate">
                                        ${row.issue_summary || '-'}
                                    </p>

                                    <!-- META -->
                                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-400">

                                        <span>📂 ${row.ticket_category_name || '-'}</span>
                                        <span>📁 ${row.ticket_subcategory_name || '-'}</span>

                                        <span>📍 ${row.location_name || '-'}</span>
                                        <span>—</span>
                                        <span>${row.sub_location_name || '-'}</span>

                                        <span>👤 ${row.pic_ticket || 'Waiting for PIC'}</span>

                                    </div>

                                </div>

                                <!-- RIGHT -->
                                <!-- RIGHT -->
                                <div class="shrink-0 text-right flex flex-col items-end gap-2">

                                    <span class="text-xs text-slate-400">
                                        ${formatDate(row.ticketdate)}
                                    </span>

                                    ${
                                        row.status === 'W' && row.is_creator
                                        ? `
                                            <button
                                                class="btn-cancel-ticket inline-flex items-center rounded-md border border-red-200 bg-red-50 px-2.5 py-1 text-[11px] font-medium text-red-600 transition hover:bg-red-100"
                                                data-eid="${row.eid}">
                                                Cancel Ticket
                                            </button>
                                        `
                                        : ''
                                    }

                                </div>

                            </div>

                        </div>
                        `;
                    });

                    $('#ticketListWrapper').html(html);
                },
                error: function() {
                    $('#ticketListWrapper').html(`
                        <div class="p-6 text-center text-sm text-red-400">
                            Failed to load tickets
                        </div>
                    `);
                }
            });
        }
    </script>

    <script>
        function openDetailModal(eid) {
            currentTicketEid = eid;


            const url = "{{ route('ticket.detail', ':hash') }}".replace(':hash', eid);

            // show modal
            $('#detailTicketModal').removeClass('hidden');
            $('body').addClass('modal-open');

            // loading state
            $('#detail_ticketid').text('Loading...');
            $('#detail_descr').text('Loading...');
            $('#detail_tracking').html('Loading...');
            $('#detail_attachments').html('Loading...');

            $.ajax({
                url: url,
                method: "GET",
                success: function(res) {

                    const t = res.ticket;
                    currentTicketData = t;
                    const activities = res.activities || [];
                    const attachments = res.attachments || [];

                    renderDetailHeader(t);
                    renderDetailInfo(t);
                    renderTimeline(activities, t);
                    renderAttachments(attachments);
                    renderActions(t);
                    renderPrimaryButton(t);

                },
                error: function() {
                    alert('Failed load detail');
                }
            });
        }
    </script>

    <script>
        function renderDetailHeader(t) {

            $('#detail_ticketid').text(t.ticketid);

            const p = getPriorityBadge(t.ticket_priority);
            $('#detail_priority')
                .attr('class', 'rounded-md px-2 py-0.5 text-[11px] font-medium ' + p)
                .text(formatPriority(t.ticket_priority));

            const s = getStatusBadge(t.status);
            $('#detail_status')
                .attr('class', 'rounded-md px-2 py-0.5 text-[11px] font-medium ' + s.class)
                .text(s.label);

            $('#detail_type').text(t.ticket_type || '-');
        }

        function renderDetailInfo(t) {

            $('#detail_descr').text(t.issue_descr || '-');

            $('#detail_date').text(formatDate(t.ticketdate));

            $('#detail_sla').text(t.ticket_sla_days ? t.ticket_sla_days + ' days' : '-');

            $('#detail_created_by').text(t.created_by || '-');

            $('#detail_category').text(t.ticket_category_name || '-');
            $('#detail_subcategory').text(t.ticket_subcategory_name || '-');

            $('#detail_location').text(t.location_name || '-');
            $('#detail_sub_location').text(t.sub_location_name || '-');
        }

        function renderTimeline(activities, t) {

            if (!activities.length) {
                $('#detail_tracking').html('No activity');
                return;
            }

            let html = '';

            activities.forEach(a => {

                html += `
                <div class="flex gap-3">

                    <div class="mt-1 h-2 w-2 rounded-full bg-slate-400"></div>

                    <div class="flex-1">

                        <div class="flex justify-between text-xs text-slate-400">
                            <span>${a.response_summary}</span>
                            <span>${formatDateTime(a.response_date)}</span>
                        </div>

                        <div class="text-sm text-slate-700 dark:text-slate-300">
                            ${a.response_descr || '-'}
                        </div>

                        <div class="text-xs text-slate-400">
                            👤 ${a.pic_ticket || '-'}
                        </div>

                    </div>

                </div>
                `;
            });

            $('#detail_tracking').html(html);

            $('#detail_pic').text(t.pic_ticket || '-');
        }

        function renderAttachments(files) {

            if (!files.length) {
                $('#detail_attachments').html('No attachment');
                return;
            }

            let html = '';

            files.forEach(f => {
                html += `
                <a href="${f.url}" target="_blank"
                    class="flex items-center justify-between rounded-lg border px-3 py-2 hover:bg-slate-50 dark:hover:bg-white/5">

                    <div>
                        <div class="text-sm text-slate-700 dark:text-slate-300">
                            ${f.display_name}
                        </div>
                        <div class="text-xs text-slate-400">
                            ${formatDateTime(f.created_at)}
                        </div>
                    </div>

                    <div class="text-xs text-blue-500">Open</div>
                </a>
                `;
            });

            $('#detail_attachments').html(html);
        }

        function formatPriority(p) {
            if (!p) return '-';
            return p.charAt(0) + p.slice(1).toLowerCase();
        }

        function buildActions(t) {

            const actions = [];

            // ===== STATUS BASED =====
            if (t.status === 'W') {
                actions.push({
                    label: 'Start Work',
                    action: 'start'
                });
                actions.push({
                    label: 'Assign PIC',
                    action: 'assign'
                });
                actions.push({
                    label: 'Transfer',
                    action: 'transfer'
                });
            }

            if (t.status === 'P') {
                actions.push({
                    label: 'Update Progress',
                    action: 'progress'
                });
                actions.push({
                    label: 'Mark as Completed',
                    action: 'complete'
                });
                actions.push({
                    label: 'Transfer',
                    action: 'transfer'
                });
            }

            if (t.status === 'C') {
                actions.push({
                    label: 'Reopen Ticket',
                    action: 'reopen'
                });
            }

            if (t.status === 'R') {
                actions.push({
                    label: 'Start Work',
                    action: 'start'
                });
            }

            return actions;
        }

        function renderActions(t) {

            const actions = buildActions(t);

            if (!actions.length) {
                $('#ticketActionList').html(`
                    <div class="px-3 py-2 text-xs text-slate-400">
                        No actions available
                    </div>
                `);
                return;
            }

            let html = '';

            actions.forEach(a => {
                html += `
                <button class="action-item w-full text-left px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-white/5"
                    data-action="${a.action}">
                    ${a.label}
                </button>
                `;
            });

            $('#ticketActionList').html(html);
        }

        function renderPrimaryButton(t) {

            const btn = $('#primaryActionBtn');

            btn.addClass('hidden');

            if (t.status === 'W') {
                btn.removeClass('hidden').text('⚡ Start Work').data('action', 'start');
            }

            if (t.status === 'P') {
                btn.removeClass('hidden').text('⚡ Complete').data('action', 'complete');
            }

            if (t.status === 'R') {
                btn.removeClass('hidden').text('⚡ Restart').data('action', 'start');
            }
        }

        function loadAssignData(t) {

            $('#assign_pic_dropdown').html('<option>Loading...</option>');

            $.get("{{ route('ticket.picByCategory') }}", {
                ticket_type: t.ticket_type,
                ticket_categoryid: t.ticket_categoryid,
                department_id: t.department_id
            }, function(rows) {

                let html = `<option value="">Select PIC</option>`;

                rows.forEach(user => {
                    html += `<option value="${user.username}">
                                ${user.name || user.username}
                            </option>`;
                });

                $('#assign_pic_dropdown').html(html);
            });
        }
        $(document).on('click', '.action-item', function() {

            const action = $(this).data('action');

            $('#ticketActionDropdown').addClass('hidden');

            switch (action) {

                case 'start':
                    startWork();
                    break;

                case 'progress':
                    updateProgress();
                    break;

                case 'complete':
                    completeTicket();
                    break;

                case 'reopen':
                    reopenTicket();
                    break;
                case 'assign':
                    loadAssignData(currentTicketData);
                    $('#assignModal').removeClass('hidden');
                    break;

                case 'transfer':
                    loadTransferCategories();
                    $('#transferModal').removeClass('hidden');
                    break;

                case 'cancel':
                    cancelTicket();
                    break;
            }
        });


        function loadTransferCategories() {

            $('#transfer_old_category')
                .text(currentTicketData.ticket_category_name || '-');

            $('#transfer_old_subcategory')
                .text(currentTicketData.ticket_subcategory_name || '-');

            $('#transfer_category')
                .html('<option value="">Loading...</option>');

            $('#transfer_subcategory')
                .html('<option value="">Select Subcategory</option>');

            $.get("{{ route('ticket.categoryByType') }}", {
                ticket_type: currentTicketData.ticket_type
            }, function(rows) {

                let html = `<option value="">Select Category</option>`;

                rows.forEach(cat => {

                    html += `
                        <option value="${cat.ticket_categoryid}">
                            ${cat.ticket_category_name}
                        </option>
                    `;
                });

                $('#transfer_category').html(html);
            });
        }

        $('#transfer_category').on('change', function() {

            const category = $(this).val();

            $('#transfer_subcategory')
                .html('<option value="">Loading...</option>');

            if (!category) return;

            $.get("{{ route('ticket.subcategoryByCategory') }}", {
                ticket_type: currentTicketData.ticket_type,
                ticket_categoryid: category
            }, function(rows) {

                let html = `<option value="">Select Subcategory</option>`;

                rows.forEach(sub => {

                    html += `
                        <option value="${sub.ticket_subcategoryid}">
                            ${sub.ticket_subcategory_name}
                        </option>
                    `;
                });

                $('#transfer_subcategory').html(html);
            });
        });

        $('#primaryActionBtn').on('click', function() {
            const action = $(this).data('action');
            $('.action-item[data-action="' + action + '"]').click();
        });


        $('#btnTicketActions').on('click', function() {

            $('#ticketActionDropdown').toggleClass('hidden');

            $('#actionArrow').toggleClass('rotate-180');
        });



        // close when click outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#btnTicketActions, #ticketActionDropdown').length) {
                $('#ticketActionDropdown').addClass('hidden');
                $('#actionArrow').removeClass('rotate-180');
            }
        });

        $('#submitTransfer').on('click', function() {

            const category = $('#transfer_category').val();
            const subcategory = $('#transfer_subcategory').val();

            if (!category) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Category Required',
                    text: 'Please select category'
                });
                return;
            }

            if (!subcategory) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Subcategory Required',
                    text: 'Please select subcategory'
                });
                return;
            }

            const oldText =
                `${currentTicketData.ticket_category_name} → ${currentTicketData.ticket_subcategory_name}`;

            const newText =
                `${$('#transfer_category option:selected').text()} → ${$('#transfer_subcategory option:selected').text()}`;

            Swal.fire({
                icon: 'question',
                title: 'Transfer Ticket?',
                html: `
                    <div class="text-left text-sm">
                        <div class="mb-3">
                            <div class="text-slate-400">Current</div>
                            <div class="font-medium">${oldText}</div>
                        </div>

                        <div>
                            <div class="text-slate-400">Transfer To</div>
                            <div class="font-medium">${newText}</div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Yes, Transfer',
                confirmButtonColor: '#0f172a'
            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route('ticket.transfer', ':hash') }}"
                        .replace(':hash', currentTicketEid),

                    method: "POST",

                    data: {
                        ticket_categoryid: category,
                        ticket_subcategoryid: subcategory,
                        _token: "{{ csrf_token() }}"
                    },

                    success: function() {

                        closeModal('#transferModal');

                        Swal.fire({
                            icon: 'success',
                            title: 'Transferred',
                            text: 'Ticket transferred successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        loadTickets();

                        closeModal('#detailTicketModal');

                        setTimeout(() => {
                            openDetailModal(currentTicketEid);
                        }, 300);
                    },

                    error: function(xhr) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Transfer Failed',
                            text: xhr.responseJSON?.message || 'Failed transfer ticket'
                        });
                    }
                });

            });
        });

        $('#submitAssign').on('click', function() {

            const pic = $('#assign_pic_dropdown').val();

            if (!pic) {
                alert('Select PIC first');
                return;
            }

            $.ajax({
                url: "{{ route('ticket.assign', ':eid') }}".replace(':eid', currentTicketEid),
                method: "POST",
                data: {
                    pic_ticket: pic,
                    _token: "{{ csrf_token() }}"
                },
                success: function() {

                    $('#assignModal').addClass('hidden');

                    loadTickets();

                    closeModal('#detailTicketModal');

                    setTimeout(() => {
                        openDetailModal(currentTicketEid);
                    }, 300);
                },
                error: function() {
                    alert('Failed to assign PIC');
                }
            });
        });
    </script>

    <script>
        function getStatusBadge(status) {

            const map = {
                W: {
                    label: 'Waiting',
                    class: 'bg-amber-100 text-amber-700'
                },
                P: {
                    label: 'Progress',
                    class: 'bg-blue-100 text-blue-700'
                },
                C: {
                    label: 'Completed',
                    class: 'bg-green-100 text-green-700'
                },
                R: {
                    label: 'Reopen',
                    class: 'bg-rose-100 text-rose-700'
                },
                X: {
                    label: 'Cancelled',
                    class: 'bg-red-200 text-red-600'
                }
            };

            return map[status] || {
                label: status,
                class: 'bg-slate-100 text-slate-600'
            };
        }

        function getPriorityBadge(priority) {
            const map = {
                HIGH: 'bg-red-100 text-red-700',
                MEDIUM: 'bg-amber-100 text-amber-700',
                LOW: 'bg-slate-100 text-slate-600'
            };

            return map[priority] || 'bg-slate-100 text-slate-600';
        }
    </script>

    <script>
        function loadCategoryTabs() {

            $.ajax({
                url: "{{ route('ticket.categories') }}",
                method: "GET",
                success: function(rows) {

                    let html = `
                        <button type="button"
                            class="ticket-category-tab active-category-tab inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                            data-category="">
                            <span>📂</span>
                            <span>All Category</span>
                        </button>
                    `;

                    rows.forEach(cat => {
                        html += `
                            <button type="button"
                                class="ticket-category-tab inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300"
                                data-category="${cat.ticket_categoryid}">

                                <span>📁</span>
                                <span>${cat.ticket_category_name}</span>
                            </button>
                        `;
                    });

                    $('#ticketCategoryTabs').html(html);
                }
            });
        }
    </script>

    <script>
        $(document).on('click', '.ticket-category-tab', function() {

            $('.ticket-category-tab')
                .removeClass('active-category-tab bg-slate-900 text-white')
                .addClass('bg-white text-slate-600 dark:bg-slate-800 dark:text-slate-300');

            $(this)
                .addClass('active-category-tab bg-slate-900 text-white')
                .removeClass('bg-white text-slate-600 dark:bg-slate-800 dark:text-slate-300');

            // set filter
            currentCategory = $(this).data('category') || '';

            // reload tickets
            loadTickets();
        });
    </script>

    <script>
        $(document).ready(function() {

            // ROUTE HANDLING FIRST (your existing logic)
            const path = window.location.pathname;

            if (path.startsWith('/showticket/')) {
                const eid = path.split('/showticket/')[1];
                window.history.replaceState({}, document.title, '/ticket');
                setTimeout(() => openDetailModal(eid), 100);
            }

            if (path === '/ticket/create') {
                window.history.replaceState({}, document.title, '/ticket');
                setTimeout(() => openModal('#createTicketModal'), 100);
            }

            // 🔥 INIT FLOW
            loadCategoryTabs();
            loadTickets();

            $('#btnCreateTicket').on('click', function() {

                $('#formCreateTicket')[0].reset();

                resetSelect('#category');
                resetSelect('#subcategory');

                resetSelect('#sub_location');

                openModal('#createTicketModal');

                setTimeout(() => {
                    $('#createTicketModal select').select2({
                        width: '100%',
                        dropdownParent: $('#createTicketModal')
                    });

                    initCompanyDept(); // 🔥 THIS IS NEW
                }, 100);
            });

            setTimeout(() => {
                $('#createTicketModal select').select2({
                    width: '100%',
                    dropdownParent: $('#createTicketModal')
                });

            }, 100);
        });
    </script>

    <script>
        $(document).on('click', '.ticket-row', function() {
            const eid = $(this).data('eid');

            history.pushState({}, '', '/showticket/' + eid);
            openDetailModal(eid);
        });
    </script>

    <script>
        // ===============================
        // GLOBAL MODAL HANDLER
        // ===============================

        function openModal(id) {
            $(id).removeClass('hidden');
            $('body').addClass('modal-open');
        }

        function closeModal(id) {
            $(id).addClass('hidden');

            // only check modals
            if ($('[id$="Modal"]:not(.hidden)').length === 0) {
                $('body').removeClass('modal-open');
            }
        }

        function closeAllModals() {
            $('[id$="Modal"]').addClass('hidden');
            $('body').removeClass('modal-open');
        }

        // ===============================
        // BACKDROP CLICK (click outside)
        // ===============================
        $(document).on('click', '.fixed', function(e) {
            if ($(e.target).is('.fixed')) {
                $(this).addClass('hidden');

                if ($('[id$="Modal"]:not(.hidden)').length === 0) {
                    $('body').removeClass('modal-open');
                }
            }
        });

        // ===============================
        // ESC KEY CLOSE
        // ===============================
        $(document).on('keydown', function(e) {
            if (e.key === "Escape") {
                closeAllModals();
            }
        });

        window.onpopstate = function() {
            const path = window.location.pathname;

            if (path.startsWith('/showticket/')) {
                const eid = path.split('/showticket/')[1];
                openDetailModal(eid);
            } else {
                closeModal('#detailTicketModal');
            }
        };

        // ===============================
        // CREATE MODAL
        // ===============================

        $('#closeCreateTicketModal, #cancelCreateTicket').on('click', function() {
            resetCreateForm();
            closeModal('#createTicketModal');
        });



        $('#formCreateTicket').on('submit', function(e) {
            e.preventDefault();

            $('#company').prop('disabled', false);
            $('#department').prop('disabled', false);

            const requiredFields = [{
                    field: '#company',
                    label: 'Company'
                },
                {
                    field: '#department',
                    label: 'Department'
                },
                {
                    field: '#ticket_type',
                    label: 'Type'
                },
                {
                    field: '#category',
                    label: 'Category'
                },
                {
                    field: '#subcategory',
                    label: 'Subcategory'
                },
                {
                    field: '#location',
                    label: 'Location'
                },
                {
                    field: '#sub_location',
                    label: 'Sub Location'
                },
                {
                    field: 'input[name="issue_summary"]',
                    label: 'Issue Summary'
                },
                {
                    field: 'textarea[name="issue_descr"]',
                    label: 'Issue Description'
                }
            ];

            for (const item of requiredFields) {

                const value = $(item.field).val();

                if (!String(value ?? '').trim()) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Required Field',
                        text: item.label + ' is required',
                        confirmButtonColor: '#0f172a'
                    });

                    $(item.field).focus();

                    return false;
                }
            }

            if (!$('#ticket_priority_hidden').val()) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Required Field',
                    text: 'Priority is required',
                    confirmButtonColor: '#0f172a'
                });

                return false;
            }

            const formData = new FormData(this);

            $.ajax({
                url: "{{ route('ticket.store') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,

                beforeSend: function() {

                    $('button[type="submit"]')
                        .prop('disabled', true)
                        .html(`
                    <span class="animate-pulse">
                        Submitting...
                    </span>
                `);
                },

                success: function(res) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Ticket created successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    resetCreateForm();
                    closeModal('#createTicketModal');

                    $('#formCreateTicket')[0].reset();

                    $('#attachmentPreview').html('');

                    loadTickets();

                    if (res.eid) {
                        history.pushState({}, '', '/showticket/' + res.eid);

                        setTimeout(() => {
                            openDetailModal(res.eid);
                        }, 300);
                    }
                },

                error: function(xhr) {

                    let message = 'Failed to create ticket';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                        confirmButtonColor: '#dc2626'
                    });
                },

                complete: function() {

                    $('button[type="submit"]')
                        .prop('disabled', false)
                        .html('Submit Ticket');
                }
            });
        });

        let searchTimer;

        $('#ticketSearch').on('keyup', function() {

            clearTimeout(searchTimer);

            searchTimer = setTimeout(() => {

                currentSearch = $(this).val();

                loadTickets();

            }, 400);
        });
                // reset form on close
        function resetCreateForm() {

            $('#formCreateTicket')[0].reset();

            $('#attachmentPreview').html('');

            selectedFiles = [];

            resetSelect('#category');
            resetSelect('#subcategory');
            resetSelect('#sub_location');

            $('#priority_badge').text('-');
            $('#ticket_priority_hidden').val('');

            if ($('#company').hasClass('select2-hidden-accessible')) {
                $('#company').val('').trigger('change');
            }

            if ($('#department').hasClass('select2-hidden-accessible')) {
                $('#department').val('').trigger('change');
            }

            initCompanyDept();
        }

        // ===============================
        // DETAIL MODAL
        // ===============================
        $('#closeDetailTicketModal').on('click', function() {
            closeModal('#detailTicketModal');
            history.pushState({}, '', '/ticket');
        });

        // ===============================
        // ASSIGN MODAL
        // ===============================
        $('#cancelAssign').on('click', function() {
            closeModal('#assignModal');
        });

        // ===============================
        // TRANSFER MODAL
        // ===============================
        $('#cancelTransfer').on('click', function() {
            closeModal('#transferModal');
        });

        // ===============================
        // IMAGE PREVIEW
        // ===============================
        $('#closeImagePreview').on('click', function() {
            closeModal('#imagePreviewModal');
        });

        // ===============================
        // OPEN IMAGE PREVIEW
        // ===============================
        $(document).on('click', '.preview-image', function() {
            const src = $(this).attr('src');
            $('#previewImage').attr('src', src);
            openModal('#imagePreviewModal');
        });

        // ===============================
        // ASSIGN CHECKBOX TOGGLE
        // ===============================
        $('#assign_pic_checkbox').on('change', function() {
            if ($(this).is(':checked')) {
                $('#picSelectionWrapper').removeClass('hidden');
            } else {
                $('#picSelectionWrapper').addClass('hidden');
                $('#pic_ticket').val('');
            }
        });
    </script>
    <script>
        // ===============================
        // RESET HELPER (KEEP ONLY ONE)
        // ===============================
        function resetSelect(id, placeholder = 'Select option') {
            const el = $(id);

            el.html(`<option value="">${placeholder}</option>`)
                .val('')
                .prop('disabled', true)
                .trigger('change');

            // 🔥 force Select2 refresh
            if (el.hasClass('select2-hidden-accessible')) {
                el.select2();
            }
        }

        // ===============================
        // TYPE → CATEGORY
        // ===============================
        $('#ticket_type').on('change', function() {
            const type = $(this).val();

            resetSelect('#category', 'Loading...');
            resetSelect('#subcategory');


            if (!type) return;

            $.get("{{ route('ticket.categoryByType') }}", {
                ticket_type: type
            }, function(rows) {

                let html = `<option value="">Select Category</option>`;

                rows.forEach(r => {
                    html += `<option value="${r.ticket_categoryid}">
                        ${r.ticket_category_name}
                    </option>`;
                });

                const el = $('#category');

                el.html(html)
                    .prop('disabled', false)
                    .trigger('change');

                // 🔥 IMPORTANT
                el.select2();
            });
        });

        // ===============================
        // CATEGORY → SUB + PRIORITY
        // ===============================

        $('#category').on('change', function() {

            const cat = $(this).val();
            const type = $('#ticket_type').val();

            resetSelect('#subcategory', 'Loading...');

            $('#ticket_priority_hidden').val(''); // reset hidden

            if (!cat) return;

            // ===============================
            // SUBCATEGORY
            // ===============================
            $.get("{{ route('ticket.subcategoryByCategory') }}", {
                ticket_categoryid: cat,
                ticket_type: type
            }, function(rows) {

                let html = `<option value="">Select Subcategory</option>`;

                rows.forEach(r => {
                    html += `<option value="${r.ticket_subcategoryid}">
                        ${r.ticket_subcategory_name}
                    </option>`;
                });
                const sub = $('#subcategory');
                sub.html(html).prop('disabled', false).trigger('change');
                sub.select2();
            });

            // ===============================
            // PRIORITY (FIXED CORE)
            // ===============================
            $.get("{{ route('ticket.priorityByCategory') }}", {
                ticket_categoryid: cat,
                ticket_type: type
            }, function(rows) {

                let html = `<option value="">Select Priority</option>`;
                let defaultVal = null;

                // find MEDIUM first
                rows.forEach(r => {
                    html += `<option value="${r.ticket_priority}">
                        ${r.ticket_priority_name}
                    </option>`;

                    if (r.ticket_priority?.toUpperCase() === 'MEDIUM') {
                        defaultVal = r.ticket_priority;
                    }
                });

                // fallback AFTER loop (correct)
                if (!defaultVal && rows.length) {
                    defaultVal = rows[0].ticket_priority;
                }

                // 🔥 CRITICAL: sync hidden input
                $('#priority_badge').text(formatPriority(defaultVal));
                $('#ticket_priority_hidden').val(defaultVal);
            });
        });
        // ===============================
        // LOCATION → SUB LOCATION
        // ===============================
        $('#location').on('change', function() {

            const loc = $(this).val();

            resetSelect('#sub_location', 'Loading...');

            if (!loc) return;

            $.get("{{ route('ticket.subLocation') }}", {
                location_id: loc
            }, function(rows) {

                let html = `<option value="">Select Sub Location</option>`;

                rows.forEach(r => {
                    html += `<option value="${r.sub_location_id}">
                        ${r.sub_location_name}
                    </option>`;
                });

                const subLoc = $('#sub_location');

                subLoc.html(html)
                    .prop('disabled', false)
                    .trigger('change');

                subLoc.select2();
            });
        });

        $('#ticket_priority').on('change', function() {
            $('#ticket_priority_hidden').val($(this).val());
        });

        let selectedFiles = [];

        $('#attachments').on('change', function() {
            selectedFiles = Array.from(this.files);
            renderAttachmentPreview();
        });

        function renderAttachmentPreview() {
            let html = '';

            if (!selectedFiles.length) {
                $('#attachmentPreview').html('');
                return;
            }

            selectedFiles.forEach((file, index) => {
                html += `
                <div class="flex items-center justify-between rounded bg-slate-50 px-3 py-2 dark:bg-white/5">
                    <div class="min-w-0">
                        <div class="truncate text-sm">${file.name}</div>
                        <div class="text-[10px] text-slate-400">${(file.size / 1024).toFixed(1)} KB</div>
                    </div>

                    <button type="button"
                        class="ml-3 text-xs text-red-500 hover:text-red-700"
                        onclick="removeFile(${index})">
                        ✕
                    </button>
                </div>
                `;
            });

            $('#attachmentPreview').html(html);

            syncFileInput();
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            renderAttachmentPreview();
        }

        function syncFileInput() {
            const dt = new DataTransfer();

            selectedFiles.forEach(file => {
                dt.items.add(file);
            });

            document.getElementById('attachments').files = dt.files;
        }

        function initCompanyDept() {

            const companyOptions = $('#company option').length - 1;

            if (companyOptions === 1) {

                const val = $('#company option:eq(1)').val();

                $('#company')
                    .val(val)
                    .trigger('change.select2');

                $('#company')
                    .prop('disabled', true)
                    .trigger('change.select2');
            }

            const deptOptions = $('#department option').length - 1;

            if (deptOptions === 1) {

                const val = $('#department option:eq(1)').val();

                $('#department')
                    .val(val)
                    .trigger('change.select2');

                $('#department')
                    .prop('disabled', true)
                    .trigger('change.select2');
            }
        }
    </script>
    <script>
        $(document).on('click', '.btn-cancel-ticket', function(e) {

            e.stopPropagation();

            const eid = $(this).data('eid');

            Swal.fire({
                icon: 'warning',
                title: 'Cancel Ticket?',
                text: 'This ticket will be cancelled',
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'No',
                confirmButtonColor: '#dc2626'
            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route('ticket.cancel', ':hash') }}".replace(':hash', eid),
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },

                    success: function() {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Ticket cancelled successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        loadTickets();

                        if (currentTicketEid === eid) {
                            closeModal('#detailTicketModal');
                        }
                    },

                    error: function(xhr) {

                        let message = 'Failed to cancel ticket';

                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message,
                            confirmButtonColor: '#dc2626'
                        });
                    }
                });

            });
        });
    </script>

</x-app-layout>
