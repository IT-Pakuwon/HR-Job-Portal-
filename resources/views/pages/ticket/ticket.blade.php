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

        .select2-selection__rendered {
            line-height: 50px !important;
            padding-left: 0 !important;
            color: inherit !important;
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

    body.modal-open {
        overflow: hidden !important;
        height: 100vh !important;
    }

    .modal-scroll {
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }
    </style>

    <div class="max-w-9xl mx-auto w-full p-2">

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

        {{-- Category Tabs --}}
        <div class="mt-4 overflow-x-auto">

            <div id="ticketCategoryTabs" class="flex min-w-max gap-2 pb-1">

                <button type="button"
                    class="ticket-category-tab active-category-tab inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:border-slate-700 dark:bg-slate-700"
                    data-category="">

                    <span>📂</span>
                    <span>All Category</span>

                </button>

            </div>

        </div>

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
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:ring-blue-500/20">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="absolute left-3 top-2.5 h-5 w-5 text-slate-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z" />
                            </svg>
                        </div>

                        @if (auth()->user()->user_role === 'admin')
                            <a href="{{ route('ticketsetup') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:hover:bg-slate-700">

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
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">

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

            {{-- Listing --}}
            <div id="ticketListWrapper" class="divide-y divide-slate-100 dark:divide-slate-800">

                {{-- Card Item --}}
                {{-- LOOP HERE --}}
                @foreach ($tickets ?? [] as $row)
                    <div
                        class="group flex flex-col gap-5 p-5 transition hover:bg-slate-50 dark:hover:bg-slate-800/50 lg:flex-row lg:items-center lg:justify-between">

                        {{-- Left --}}
                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <button type="button"
                                    class="ticket-detail-btn inline-flex items-center rounded-xl bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200"
                                    data-eid="{{ $row->eid }}">

                                    {{ $row->ticketid }}
                                </button>

                                {{-- Priority --}}
                                @php
                                    $priorityClass = match ($row->ticket_priority) {
                                        'HIGH' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                        'MEDIUM'
                                            => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                                        default
                                            => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                                    };
                                @endphp

                                <span class="{{ $priorityClass }} rounded-xl px-2.5 py-1 text-xs font-semibold">
                                    {{ $row->ticket_priority }}
                                </span>

                                @php
                                    $statusClass = match ($row->status) {
                                        'W' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                                        'P' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
                                        'C'
                                            => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                                        'R' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
                                        'X' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                        default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                    };
                                @endphp

                                {{-- Status --}}
                                @php
                                    $statusLabel = match ($row->status) {
                                        'W' => 'WAITING',
                                        'P' => 'IN PROGRESS',
                                        'C' => 'COMPLETED',
                                        'R' => 'REOPENED',
                                        'X' => 'CANCELLED',
                                        default => $row->status,
                                    };
                                @endphp

                                <span class="{{ $statusClass }} rounded-xl px-2.5 py-1 text-xs font-semibold">
                                    {{ $statusLabel }}
                                </span>

                            </div>

                            <div class="mt-3">

                                <h3 class="text-base font-semibold text-slate-800 dark:text-white">
                                    {{ $row->issue_summary ?? '-' }}
                                </h3>

                                <p
                                    class="mt-1 line-clamp-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                                    {{ $row->issue_descr ?? '-' }}
                                </p>

                            </div>

                            <div
                                class="mt-4 grid grid-cols-1 gap-2 text-sm text-slate-500 dark:text-slate-400 sm:grid-cols-2 xl:grid-cols-3">

                                <div
                                    class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                                    <span>👤</span>
                                    <span class="truncate">
                                        {{ $row->created_by ?? '-' }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                                    <span>🛠️</span>
                                    <span class="truncate">
                                        {{ $row->pic_ticket ?? 'Unassigned PIC' }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                                    <span>📂</span>
                                    <span class="truncate">
                                        {{ $row->ticket_categoryid ?? '-' }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                                    <span>🧩</span>
                                    <span class="truncate">
                                        {{ $row->ticket_subcategoryid ?? '-' }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                                    <span>📍</span>
                                    <span class="truncate">
                                        {{ $row->location_id ?? '-' }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                                    <span>🏢</span>
                                    <span class="truncate">
                                        {{ $row->sub_location_id ?? '-' }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                                    <span>📅</span>
                                    <span>
                                        {{ \Carbon\Carbon::parse($row->ticketdate)->translatedFormat('d M Y') }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                                    <span>⚡</span>
                                    <span>
                                        SLA {{ $row->ticket_sla_days ?? 0 }} Day(s)
                                    </span>
                                </div>

                            </div>

                        </div>

                        {{-- Right --}}
                        <div class="flex flex-wrap items-center gap-2">

                            <button type="button"
                                class="ticket-detail-btn rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                                data-eid="{{ $row->eid }}">

                                Detail
                            </button>

                            @if ($row->status === 'W' || $row->status === 'R')
                                <button type="button"
                                    class="ticket-edit-btn rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300"
                                    data-eid="{{ $row->eid }}">

                                    Edit
                                </button>
                            @endif

                            @if ($row->status === 'C')
                                <button type="button"
                                    class="ticket-reopen-btn rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300"
                                    data-eid="{{ $row->eid }}">

                                    Reopen
                                </button>
                            @endif

                        </div>

                    </div>
                @endforeach

                {{-- Empty State --}}
                @if (empty($tickets) || count($tickets) === 0)
                    <div class="flex flex-col items-center justify-center px-6 py-20 text-center">

                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-3xl dark:bg-slate-800">
                            🎫
                        </div>

                        <h3 class="mt-5 text-lg font-semibold text-slate-700 dark:text-slate-200">
                            No Ticket Found
                        </h3>

                        <p class="mt-2 max-w-sm text-sm text-slate-500 dark:text-slate-400">
                            There is no ticket available yet. Create your first ticket request.
                        </p>

                    </div>
                @endif

            </div>

        </div>

        {{-- CREATE MODAL --}}
        <div id="createTicketModal"
            class="fixed inset-0 z-[70] hidden overflow-y-auto bg-slate-900/50 modal-scroll">

            <div class="flex min-h-screen items-center justify-center px-4 py-10">

                <div
                    class="w-full max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">

                    {{-- Header --}}
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700">

                        <div>
                            <h2 class="text-lg font-semibold text-slate-800 dark:text-white">
                                Create Ticket
                            </h2>

                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Submit new support ticket request.
                            </p>
                        </div>

                        <button type="button" id="closeCreateTicketModal"
                            class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white">

                            ✕
                        </button>

                    </div>

                    {{-- Body --}}
                    <form id="formCreateTicket" enctype="multipart/form-data">

                        @csrf

                        <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                            @php
                                $userCompanies = \App\Models\Usercpny::where(
                                    'username',
                                    auth()->user()->username,
                                )->get();
                                $userDepartments = \App\Models\Userdept::where(
                                    'username',
                                    auth()->user()->username,
                                )->get();
                            @endphp

                            {{-- Company --}}
                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Company
                                </label>

                                @if ($userCompanies->count() === 1)

                                    <input type="text" value="{{ $userCompanies->first()->cpny_id }}" readonly
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                                    <input type="hidden" name="cpny_id"
                                        value="{{ $userCompanies->first()->cpny_id }}">
                                @else
                                    <select name="cpny_id" id="cpny_id" required
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                                        <option value="">
                                            Select Company
                                        </option>

                                        @foreach ($userCompanies as $cpny)
                                            <option value="{{ $cpny->cpny_id }}">
                                                {{ $cpny->cpny_id }}
                                            </option>
                                        @endforeach

                                    </select>

                                @endif
                            </div>

                            {{-- Department --}}
                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Department
                                </label>

                                @if ($userDepartments->count() === 1)

                                    <input type="text" value="{{ $userDepartments->first()->department_id }}"
                                        readonly
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                                    <input type="hidden" name="department_id"
                                        value="{{ $userDepartments->first()->department_id }}">
                                @else
                                    <select name="department_id" id="department_id" required
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                                        <option value="">
                                            Select Department
                                        </option>

                                        @foreach ($userDepartments as $dept)
                                            <option value="{{ $dept->department_id }}">
                                                {{ $dept->department_id }}
                                            </option>
                                        @endforeach

                                    </select>

                                @endif
                            </div>

                            {{-- Type --}}
                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Ticket Type
                                </label>

                                <select name="ticket_type" id="ticket_type"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required>

                                    <option value="">
                                        Select Type
                                    </option>

                                    @foreach (\App\Models\MsTicketType::where('status', 'A')->orderBy('ticket_type_name')->get() as $row)
                                        <option value="{{ $row->ticket_type }}">
                                            {{ $row->ticket_type_name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            {{-- Category --}}
                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Category
                                </label>

                                <select name="ticket_categoryid" id="ticket_categoryid"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required>

                                    <option value="">
                                        Select Category
                                    </option>

                                </select>
                            </div>

                            {{-- Subcategory --}}
                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Subcategory
                                </label>

                                <select name="ticket_subcategoryid" id="ticket_subcategoryid"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required>

                                    <option value="">
                                        Select Subcategory
                                    </option>

                                </select>
                            </div>

                            {{-- Priority --}}
                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Priority
                                </label>

                                <select name="ticket_priority" id="ticket_priority"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required>

                                    <option value="">
                                        Select Priority
                                    </option>

                                </select>
                            </div>

                            {{-- Location --}}
                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Location
                                </label>

                                <select name="location_id" id="location_id"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required>

                                    <option value="">
                                        Select Location
                                    </option>

                                    @foreach (\App\Models\MsLocation::where('status', 'A')->orderBy('location_name')->get() as $row)
                                        <option value="{{ $row->location_id }}">
                                            {{ $row->location_name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            {{-- Sub Location --}}
                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Sub Location
                                </label>

                                <select name="sub_location_id" id="sub_location_id"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required>

                                    <option value="">
                                        Select Sub Location
                                    </option>

                                </select>
                            </div>

                            {{-- Summary --}}
                            <div class="md:col-span-2">
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Issue Summary
                                </label>

                                <input type="text" name="issue_summary" maxlength="255"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required>
                            </div>

                            {{-- Description --}}
                            <div class="md:col-span-2">
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Issue Description
                                </label>

                                <textarea name="issue_descr" rows="5"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required></textarea>
                            </div>

                            {{-- Attachment --}}
                            <div class="md:col-span-2">

                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Attachment
                                </label>

                                <label for="attachments"
                                    class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center transition hover:border-blue-400 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-blue-500 dark:hover:bg-slate-700/50">

                                    <div class="text-4xl">
                                        📎
                                    </div>

                                    <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Click to upload attachment
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Multiple files allowed • Max 5MB per file
                                    </p>

                                    <input type="file" name="attachments[]" id="attachments" multiple required
                                        class="hidden">
                                </label>

                                <div id="attachmentPreview" class="mt-4 space-y-2">
                                </div>

                            </div>

                        </div>

                        {{-- Footer --}}
                        <div
                            class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">

                            <button type="button" id="cancelCreateTicket"
                                class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">

                                Cancel
                            </button>

                            <button type="submit" id="submitCreateTicket"
                                class="inline-flex items-center rounded-2xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-500">

                                Submit Ticket
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        {{-- DETAIL MODAL --}}
        <div id="detailTicketModal"
            class="fixed inset-0 z-[80] hidden overflow-y-auto bg-slate-900/60 modal-scroll">

            <div class="flex min-h-screen items-center justify-center px-4 py-10">

                <div
                    class="h-[95vh] w-full max-w-[95vw] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                    {{-- Header --}}
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-700">

                        <div>

                            <div class="flex flex-wrap items-center gap-2">

                                <h2 class="text-xl font-bold text-slate-800 dark:text-white" id="detail_ticketid">
                                    -
                                </h2>

                                <span id="detail_priority" class="rounded-xl px-2.5 py-1 text-xs font-semibold">
                                    -
                                </span>

                                <span id="detail_status" class="rounded-xl px-2.5 py-1 text-xs font-semibold">
                                    -
                                </span>

                            </div>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                Ticket request detail information.
                            </p>

                        </div>

                        <button type="button" id="closeDetailTicketModal"
                            class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white">

                            ✕
                        </button>

                    </div>

                    {{-- Body --}}
                    <div class="max-h-[80vh] overflow-y-auto">

                        <div class="grid grid-cols-1 gap-6 p-6 xl:grid-cols-3">

                            {{-- LEFT --}}
                            <div class="space-y-6 xl:col-span-2">

                                {{-- Summary --}}
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">

                                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white"
                                        id="detail_summary">
                                        -
                                    </h3>

                                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-300"
                                        id="detail_descr">
                                        -
                                    </p>

                                </div>

                                {{-- Information --}}
                                <div class="rounded-2xl border border-slate-200 dark:border-slate-700">

                                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">

                                        <h3
                                            class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                                            Ticket Information
                                        </h3>

                                    </div>

                                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Created By
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white"
                                                id="detail_created_by">
                                                -
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                PIC
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white"
                                                id="detail_pic">
                                                -
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Type
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white"
                                                id="detail_type">
                                                -
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Category
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white"
                                                id="detail_category">
                                                -
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Subcategory
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white"
                                                id="detail_subcategory">
                                                -
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Location
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white"
                                                id="detail_location">
                                                -
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Sub Location
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white"
                                                id="detail_sub_location">
                                                -
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                Ticket Date
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white"
                                                id="detail_date">
                                                -
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                SLA
                                            </p>

                                            <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white"
                                                id="detail_sla">
                                                -
                                            </p>
                                        </div>

                                    </div>

                                </div>

                            </div>

                            {{-- RIGHT --}}
                            <div class="space-y-6">

                                {{-- Attachment --}}
                                <div class="rounded-2xl border border-slate-200 dark:border-slate-700">

                                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">

                                        <h3
                                            class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                                            Attachment
                                        </h3>

                                    </div>

                                    <div id="detail_attachments" class="space-y-3 p-5">

                                        <div class="text-sm text-slate-500 dark:text-slate-400">
                                            No attachment
                                        </div>

                                    </div>

                                </div>

                                {{-- Activity --}}
                                <div class="rounded-2xl border border-slate-200 dark:border-slate-700">

                                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">

                                        <h3
                                            class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                                            Activity Timeline
                                        </h3>

                                    </div>

                                    <div id="detail_tracking" class="space-y-4 p-5">

                                        <div class="text-sm text-slate-500 dark:text-slate-400">
                                            No activity
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

            </div>

        </div>

        </div>


        {{-- EDIT MODAL --}}
        <div id="editTicketModal"
            class="fixed inset-0 z-[85] hidden overflow-y-auto bg-slate-900/60 modal-scroll">

            <div class="flex min-h-screen items-center justify-center px-4 py-10">

                <div
                    class="h-[95vh] w-full max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">

                    {{-- Header --}}
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700">

                        <div>

                            <h2 class="text-lg font-semibold text-slate-800 dark:text-white">
                                Edit Ticket
                            </h2>

                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Update ticket request information.
                            </p>

                        </div>

                        <button type="button" id="closeEditTicketModal"
                            class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white">

                            ✕
                        </button>

                    </div>

                    {{-- Body --}}
                    <form id="formEditTicket" enctype="multipart/form-data">

                        @csrf

                        <input type="hidden" id="edit_eid">

                        <div class="max-h-[82vh] overflow-y-auto">

                            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                                {{-- Type --}}
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Ticket Type
                                    </label>

                                    <input type="text" id="edit_ticket_type" readonly
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                </div>

                                {{-- Category --}}
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Category
                                    </label>

                                    <input type="text" id="edit_ticket_category" readonly
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                </div>

                                {{-- Subcategory --}}
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Subcategory
                                    </label>

                                    <input type="text" id="edit_ticket_subcategory" readonly
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                </div>

                                {{-- Priority --}}
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Priority
                                    </label>

                                    <input type="text" id="edit_ticket_priority" readonly
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                </div>

                                {{-- Summary --}}
                                <div class="md:col-span-2">

                                    <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Issue Summary
                                    </label>

                                    <input type="text" name="issue_summary" id="edit_issue_summary"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        required>

                                </div>

                                {{-- Description --}}
                                <div class="md:col-span-2">

                                    <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Issue Description
                                    </label>

                                    <textarea name="issue_descr" id="edit_issue_descr" rows="6"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        required></textarea>

                                </div>

                                {{-- Existing Attachment --}}
                                <div class="md:col-span-2">

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Existing Attachment
                                    </label>

                                    <div id="edit_existing_attachments" class="space-y-3">
                                    </div>

                                </div>

                                {{-- Add Attachment --}}
                                <div class="md:col-span-2">

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Add New Attachment
                                    </label>

                                    <input type="file" id="edit_attachments" name="attachments[]" multiple
                                        class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                                    <div id="edit_attachment_preview" class="mt-4 space-y-2">
                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- Footer --}}
                        <div
                            class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">

                            <button type="button" id="cancelEditTicket"
                                class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">

                                Cancel
                            </button>

                            <button type="submit" id="submitEditTicket"
                                class="rounded-2xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-500">

                                Update Ticket
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        {{-- REOPEN MODAL --}}
        <div id="reopenTicketModal"
            class="fixed inset-0 z-[90] hidden overflow-y-auto bg-slate-900/60 modal-scroll">

            <div class="flex min-h-screen items-center justify-center px-4 py-10">

                <div
                    class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">


                    {{-- Header --}}
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700">

                        <div>

                            <h2 class="text-lg font-semibold text-slate-800 dark:text-white">
                                Reopen Ticket
                            </h2>

                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Reopen completed ticket request.
                            </p>

                        </div>

                        <button type="button" id="closeReopenTicketModal"
                            class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white">

                            ✕
                        </button>

                    </div>

                    {{-- Body --}}
                    <form id="formReopenTicket">

                        @csrf

                        <input type="hidden" id="reopen_eid">

                        <div class="p-6">

                            <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Reopen Reason
                            </label>

                            <textarea id="reopen_descr" name="reopen_descr" rows="5"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                required></textarea>

                        </div>

                        {{-- Footer --}}
                        <div
                            class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">

                            <button type="button" id="cancelReopenTicket"
                                class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">

                                Cancel
                            </button>

                            <button type="submit" id="submitReopenTicket"
                                class="rounded-2xl bg-rose-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-rose-500">

                                Reopen Ticket
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Listing Script --}}
    <script>
        let currentStatus = '';
        let currentCategory = '';
        let allTicketRows = [];
        let filteredTicketRows = [];
        let currentPage = 1;
        let perPage = 10;

        async function loadTickets(status = '') {

            currentStatus = status;

            $('#ticketListWrapper').html(`
                <div class="flex items-center justify-center px-6 py-16">
                    <div class="flex items-center gap-3 text-sm text-slate-500 dark:text-slate-400">

                        <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>

                        Loading ticket...

                    </div>
                </div>
            `);

            try {

                const response = await $.ajax({
                    url: `{{ route('ticket.json') }}`,
                    type: 'GET',
                    data: {
                        status: status,
                        category: currentCategory
                    }
                });

                allTicketRows = response.data || [];
                filteredTicketRows = [...allTicketRows];

                renderCategoryTabs(allTicketRows);

                currentPage = 1;

                renderTicketList();

            } catch (err) {

                console.error(err);

                $('#ticketListWrapper').html(`
                    <div class="flex flex-col items-center justify-center px-6 py-20 text-center">

                        <div class="text-5xl">
                            ⚠️
                        </div>

                        <h3 class="mt-4 text-lg font-semibold text-slate-700 dark:text-slate-200">
                            Failed Load Ticket
                        </h3>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Unable to retrieve ticket listing.
                        </p>

                    </div>
                `);

            }

        }

        function renderCategoryTabs(rows) {

            const wrapper = $('#ticketCategoryTabs');

            const categories = [...new Set(
                rows
                .map(x => x.ticket_categoryid)
                .filter(Boolean)
            )];

            let html = `
                <button
                    type="button"
                    class="ticket-category-tab ${currentCategory === '' ? 'active-category-tab bg-slate-900 text-white dark:bg-slate-700' : 'bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-200'} inline-flex items-center gap-2 rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-700"
                    data-category="">

                    <span>📂</span>
                    <span>All Category</span>

                </button>
            `;

            categories.forEach(category => {

                const total = rows.filter(x => x.ticket_categoryid === category).length;

                html += `
                    <button
                        type="button"
                        class="ticket-category-tab ${currentCategory === category ? 'active-category-tab bg-blue-600 text-white dark:bg-blue-600' : 'bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-200'} inline-flex items-center gap-2 rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-700"
                        data-category="${category}">

                        <span>🧩</span>

                        <span>${category}</span>

                        <span class="rounded-xl bg-black/10 px-2 py-0.5 text-xs">
                            ${total}
                        </span>

                    </button>
                `;

            });

            wrapper.html(html);

        }

        function renderTicketList(filteredRows = null) {

            if (filteredRows !== null) {
                filteredTicketRows = filteredRows;
            }

            const rows = filteredTicketRows;

            if (!rows.length) {

                $('#ticketListWrapper').html(`
                    <div class="flex flex-col items-center justify-center px-6 py-20 text-center">

                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-3xl dark:bg-slate-800">
                            🎫
                        </div>

                        <h3 class="mt-5 text-lg font-semibold text-slate-700 dark:text-slate-200">
                            No Ticket Found
                        </h3>

                        <p class="mt-2 max-w-sm text-sm text-slate-500 dark:text-slate-400">
                            There is no ticket available yet.
                        </p>

                    </div>
                `);

                return;

            }

            const totalPages = Math.ceil(rows.length / perPage);

            const start = (currentPage - 1) * perPage;
            const end = start + perPage;

            const paginatedRows = rows.slice(start, end);

            let html = '';

            paginatedRows.forEach(row => {

                let priorityClass = `
                    bg-emerald-100 text-emerald-700
                    dark:bg-emerald-500/10 dark:text-emerald-300
                `;

                if (row.ticket_priority === 'HIGH') {
                    priorityClass = `
                        bg-red-100 text-red-700
                        dark:bg-red-500/10 dark:text-red-300
                    `;
                }

                if (row.ticket_priority === 'MEDIUM') {
                    priorityClass = `
                        bg-amber-100 text-amber-700
                        dark:bg-amber-500/10 dark:text-amber-300
                    `;
                }

                let statusClass = `
                    bg-slate-100 text-slate-700
                    dark:bg-slate-700 dark:text-slate-300
                `;

                let statusLabel = row.status;

                if (row.status === 'W') {
                    statusClass = `
                        bg-amber-100 text-amber-700
                        dark:bg-amber-500/10 dark:text-amber-300
                    `;
                    statusLabel = 'WAITING';
                }

                if (row.status === 'P') {
                    statusClass = `
                        bg-blue-100 text-blue-700
                        dark:bg-blue-500/10 dark:text-blue-300
                    `;
                    statusLabel = 'IN PROGRESS';
                }

                if (row.status === 'C') {
                    statusClass = `
                        bg-emerald-100 text-emerald-700
                        dark:bg-emerald-500/10 dark:text-emerald-300
                    `;
                    statusLabel = 'COMPLETED';
                }

                if (row.status === 'R') {
                    statusClass = `
                        bg-rose-100 text-rose-700
                        dark:bg-rose-500/10 dark:text-rose-300
                    `;
                    statusLabel = 'REOPENED';
                }

                if (row.status === 'X') {
                    statusClass = `
                        bg-slate-200 text-slate-700
                        dark:bg-slate-700 dark:text-slate-300
                    `;
                    statusLabel = 'CANCELLED';
                }

                let buttons = `
                    <button
                        type="button"
                        class="ticket-detail-btn rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                        data-eid="${row.eid}">
                        Detail
                    </button>
                `;

                if (row.status === 'W' || row.status === 'R') {

                    buttons += `
                        <button
                            type="button"
                            class="ticket-edit-btn rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300"
                            data-eid="${row.eid}">
                            Edit
                        </button>
                    `;
                }

                if (row.status === 'C') {

                    buttons += `
                        <button
                            type="button"
                            class="ticket-reopen-btn rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300"
                            data-eid="${row.eid}">
                            Reopen
                        </button>
                    `;
                }

                html += `
                <div class="group border-b border-slate-200/70 px-6 py-5 transition hover:bg-slate-50/70 dark:border-white/10 dark:hover:bg-white/[0.03]">

                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <button
                                    type="button"
                                    class="ticket-detail-btn inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold tracking-wide text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-200"
                                    data-eid="${row.eid}">

                                    ${row.ticketid}

                                </button>

                                <span class="rounded-lg px-2 py-1 text-[11px] font-semibold ${priorityClass}">
                                    ${row.ticket_priority}
                                </span>

                                <span class="rounded-lg px-2 py-1 text-[11px] font-semibold ${statusClass}">
                                    ${statusLabel}
                                </span>

                            </div>

                            <div class="mt-3">

                                <h3 class="truncate text-[15px] font-semibold tracking-tight text-slate-900 dark:text-white">
                                    ${row.issue_summary ?? '-'}
                                </h3>

                                <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                                    ${row.issue_descr ?? '-'}
                                </p>

                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[13px] text-slate-500 dark:text-slate-400">

                                <div class="flex items-center gap-1.5">
                                    <span class="opacity-70">👤</span>
                                    <span>${row.created_by ?? '-'}</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="opacity-70">🛠️</span>
                                    <span>${row.pic_ticket ?? 'Unassigned PIC'}</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="opacity-70">📂</span>
                                    <span>${row.ticket_categoryid ?? '-'}</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="opacity-70">🧩</span>
                                    <span>${row.ticket_subcategoryid ?? '-'}</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="opacity-70">📍</span>
                                    <span>${row.location_id ?? '-'}</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="opacity-70">🏢</span>
                                    <span>${row.sub_location_id ?? '-'}</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="opacity-70">📅</span>
                                    <span>
                                        ${new Date(row.ticketdate).toLocaleDateString('id-ID', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric'
                                        })}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <span class="opacity-70">⚡</span>
                                    <span>SLA ${row.ticket_sla_days ?? 0} Day(s)</span>
                                </div>

                            </div>

                        </div>

                        <div class="flex items-center gap-2 xl:ml-6">

                            ${buttons}

                        </div>

                    </div>

                </div>
                `;
            });

            html += `
                <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4 dark:border-slate-700">

                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        Showing ${start + 1} - ${Math.min(end, rows.length)} of ${rows.length} tickets
                    </div>

                    <div class="flex items-center gap-2">

                        <button
                            type="button"
                            id="btnPrevPage"
                            ${currentPage === 1 ? 'disabled' : ''}
                            class="rounded-xl border border-slate-200 px-3 py-2 text-sm disabled:opacity-40 dark:border-slate-700">

                            Prev
                        </button>

                        <div class="px-3 text-sm font-medium text-slate-600 dark:text-slate-300">
                            ${currentPage} / ${totalPages}
                        </div>

                        <button
                            type="button"
                            id="btnNextPage"
                            ${currentPage >= totalPages ? 'disabled' : ''}
                            class="rounded-xl border border-slate-200 px-3 py-2 text-sm disabled:opacity-40 dark:border-slate-700">

                            Next
                        </button>

                    </div>

                </div>
            `;

            $('#ticketListWrapper').html(html);

        }

        $(document).on('click', '.ticket-category-tab', function() {

            const selectedCategory = $(this).data('category');

            currentCategory = selectedCategory;

            console.log('Selected Category:', currentCategory);

            $('.ticket-category-tab')
                .removeClass(`
                    active-category-tab
                    bg-slate-900 text-white
                    bg-blue-600
                    dark:bg-blue-600
                `)
                .addClass(`
                    bg-white text-slate-700
                    dark:bg-slate-800 dark:text-slate-200
                `);

            $(this)
                .removeClass(`
                    bg-white text-slate-700
                    dark:bg-slate-800 dark:text-slate-200
                `)
                .addClass(`
                    active-category-tab
                    bg-blue-600 text-white
                    dark:bg-blue-600
                `);

            let filtered = allTicketRows;

            if (currentCategory) {

                filtered = filtered.filter(row =>
                    row.ticket_categoryid == currentCategory
                );

            }

            currentPage = 1;

            renderTicketList(filtered);

        });

        $('#activeCategoryLabel').text(
            currentCategory || 'All Category'
        );
        $(document).on('click', '#btnPrevPage', function() {

            if (currentPage > 1) {

                currentPage--;

                renderTicketList();

            }

        });

        $(document).on('click', '.ticket-category-tab', function() {

            $('.ticket-category-tab')
                .removeClass('active-category-tab bg-slate-900 text-white bg-blue-600 dark:bg-blue-600')
                .addClass('bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-200');

            $(this)
                .addClass('active-category-tab');

            currentCategory = $(this).data('category');

            const keyword = $('#ticketSearch').val().toLowerCase();

            let filtered = allTicketRows;

            if (currentCategory) {

                filtered = filtered.filter(row =>
                    row.ticket_categoryid === currentCategory
                );

            }

            if (keyword) {

                filtered = filtered.filter(row =>
                    JSON.stringify(row)
                    .toLowerCase()
                    .includes(keyword)
                );

            }

            currentPage = 1;

            renderTicketList(filtered);

        });

        $(document).on('click', '#btnNextPage', function() {

            const totalPages = Math.ceil(allTicketRows.length / perPage);

            if (currentPage < totalPages) {

                currentPage++;

                renderTicketList();

            }

        });
        $(document).ready(function() {
            initTicketSelect2();

            loadTickets();

            $(document).on('click', '.status-filter', function(e) {

                e.preventDefault();

                $('.status-filter').removeClass('active');

                $(this).addClass('active');

                const status = $(this).data('status');

                loadTickets(status);

            });

            $('#ticketSearch').on('keyup', function() {

                const keyword = $(this).val().toLowerCase();

                let filtered = allTicketRows;

                if (currentCategory) {

                    filtered = filtered.filter(row =>
                        row.ticket_categoryid === currentCategory
                    );

                }

                filtered = filtered.filter(row => {

                    return JSON.stringify(row)
                        .toLowerCase()
                        .includes(keyword);

                });

                currentPage = 1;

                renderTicketList(filtered);

            });

            $(document).on('click', '.ticket-detail-btn', async function() {

                const eid = $(this).data('eid');

                $('#detailTicketModal')
                    .removeClass('hidden')
                    .hide()
                    .fadeIn(150);

                try {

                    const response = await $.ajax({
                        url: `/ticket/detail/${eid}`,
                        type: 'GET'
                    });

                    const row = response.ticket;
                    const activities = response.activities || [];
                    const attachments = response.attachments || [];

                    let priorityClass = `
                        bg-emerald-100 text-emerald-700
                        dark:bg-emerald-500/10 dark:text-emerald-300
                    `;

                    if (row.ticket_priority === 'HIGH') {
                        priorityClass = `
                            bg-red-100 text-red-700
                            dark:bg-red-500/10 dark:text-red-300
                        `;
                    }

                    if (row.ticket_priority === 'MEDIUM') {
                        priorityClass = `
                            bg-amber-100 text-amber-700
                            dark:bg-amber-500/10 dark:text-amber-300
                        `;
                    }

                    let statusClass = `
                        bg-slate-100 text-slate-700
                        dark:bg-slate-700 dark:text-slate-300
                    `;

                    let statusLabel = row.status;

                    if (row.status === 'W') {
                        statusClass = `
                            bg-amber-100 text-amber-700
                            dark:bg-amber-500/10 dark:text-amber-300
                        `;
                        statusLabel = 'WAITING';
                    }

                    if (row.status === 'P') {
                        statusClass = `
                            bg-blue-100 text-blue-700
                            dark:bg-blue-500/10 dark:text-blue-300
                        `;
                        statusLabel = 'IN PROGRESS';
                    }

                    if (row.status === 'C') {
                        statusClass = `
                            bg-emerald-100 text-emerald-700
                            dark:bg-emerald-500/10 dark:text-emerald-300
                        `;
                        statusLabel = 'COMPLETED';
                    }

                    if (row.status === 'R') {
                        statusClass = `
                            bg-rose-100 text-rose-700
                            dark:bg-rose-500/10 dark:text-rose-300
                        `;
                        statusLabel = 'REOPENED';
                    }

                    if (row.status === 'X') {
                        statusClass = `
                            bg-slate-200 text-slate-700
                            dark:bg-slate-700 dark:text-slate-300
                        `;
                        statusLabel = 'CANCELLED';
                    }

                    $('#detail_ticketid').text(row.ticketid ?? '-');

                    $('#detail_priority')
                        .attr('class', `rounded-xl px-2.5 py-1 text-xs font-semibold ${priorityClass}`)
                        .text(row.ticket_priority ?? '-');

                    $('#detail_status')
                        .attr('class', `rounded-xl px-2.5 py-1 text-xs font-semibold ${statusClass}`)
                        .text(statusLabel);

                    $('#detail_summary').text(row.issue_summary ?? '-');

                    $('#detail_descr').text(row.issue_descr ?? '-');

                    $('#detail_created_by').text(row.created_by ?? '-');

                    $('#detail_pic').text(row.pic_ticket ?? 'Unassigned PIC');

                    $('#detail_type').text(row.ticket_type ?? '-');

                    $('#detail_category').text(row.ticket_categoryid ?? '-');

                    $('#detail_subcategory').text(row.ticket_subcategoryid ?? '-');

                    $('#detail_location').text(row.location_id ?? '-');

                    $('#detail_sub_location').text(row.sub_location_id ?? '-');

                    $('#detail_date').text(
                        row.ticketdate ?
                        new Date(row.ticketdate).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric'
                        }) :
                        '-'
                    );

                    $('#detail_sla').text(`SLA ${row.ticket_sla_days ?? 0} Day(s)`);

                    // =========================
                    // ATTACHMENTS
                    // =========================
                    let attachmentHtml = '';

                    if (attachments.length) {

                        attachmentHtml += `
                            <div class="grid grid-cols-1 gap-3">
                        `;

                        attachments.forEach(file => {

                            const ext = (file.extention || '').toLowerCase();

                            const isImage = [
                                'jpg',
                                'jpeg',
                                'png',
                                'webp',
                                'gif'
                            ].includes(ext);

                            if (isImage) {

                                attachmentHtml += `
                                    <a
                                        href="${file.url}"
                                        target="_blank"
                                        class="group overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">

                                        <div class="aspect-video overflow-hidden bg-slate-100 dark:bg-slate-900">

                                            <img
                                                src="${file.url}"
                                                alt="${file.display_name}"
                                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">

                                        </div>

                                        <div class="flex items-center justify-between p-3">

                                            <div class="min-w-0">

                                                <p class="truncate text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                    ${file.display_name}
                                                </p>

                                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                    ${ext.toUpperCase()}
                                                </p>

                                            </div>

                                            <div class="text-xl">
                                                🖼️
                                            </div>

                                        </div>

                                    </a>
                                `;

                            } else {

                                attachmentHtml += `
                                    <a
                                        href="${file.url}"
                                        target="_blank"
                                        class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:bg-slate-100 hover:shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700">

                                        <div class="min-w-0">

                                            <p class="truncate text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                ${file.display_name}
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                ${ext.toUpperCase()}
                                            </p>

                                        </div>

                                        <div class="text-xl">
                                            📎
                                        </div>

                                    </a>
                                `;

                            }

                        });

                        attachmentHtml += `
                            </div>
                        `;

                    } else {

                        attachmentHtml = `
                            <div class="rounded-2xl border border-dashed border-slate-300 px-6 py-10 text-center dark:border-slate-700">

                                <div class="text-4xl">
                                    📂
                                </div>

                                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                                    No attachment available
                                </p>

                            </div>
                        `;
                    }

                    $('#detail_attachments').html(attachmentHtml);

                    // =========================
                    // TIMELINE
                    // =========================

                    let trackingHtml = '';

                    if (activities.length) {

                        activities.forEach(item => {

                            trackingHtml += `
                                <div class="relative pl-6">

                                    <div class="absolute left-0 top-1 h-3 w-3 rounded-full bg-blue-500"></div>

                                    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">

                                        <div class="flex flex-wrap items-center justify-between gap-2">

                                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                ${item.response_summary ?? '-'}
                                            </h4>

                                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                                ${item.response_date ?? '-'}
                                            </span>

                                        </div>

                                        <p class="mt-2 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">
                                            ${item.response_descr ?? '-'}
                                        </p>

                                        <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                                            PIC : ${item.pic_ticket ?? '-'}
                                        </div>

                                    </div>

                                </div>
                            `;

                        });

                    } else {

                        trackingHtml = `
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                No activity
                            </div>
                        `;

                    }

                    $('#detail_tracking').html(trackingHtml);

                } catch (err) {

                    console.error(err);

                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: err?.responseJSON?.message ?? 'Failed load detail ticket.'
                    });

                }

            });

            $(document).on('click', '#closeDetailTicketModal', function() {

                $('#detailTicketModal').fadeOut(150);

                setTimeout(() => {
                    $('#detailTicketModal').addClass('hidden');
                }, 150);

            });

            $(document).on('click', '#detailTicketModal', function(e) {

                if (e.target.id === 'detailTicketModal') {

                    $('#detailTicketModal').fadeOut(150);

                    setTimeout(() => {
                        $('#detailTicketModal').addClass('hidden');
                    }, 150);

                }

            });

            $(document).on('click', '.ticket-edit-btn', function() {

                const eid = $(this).data('eid');

                console.log('edit', eid);

                // OPEN EDIT MODAL HERE

            });

            $(document).on('click', '.ticket-reopen-btn', function() {

                const eid = $(this).data('eid');

                console.log('reopen', eid);

                // OPEN REOPEN MODAL HERE

            });

            $('#btnCreateTicket').on('click', function() {

                console.log('create');

                // OPEN CREATE MODAL HERE

            });

            let ticketFiles = new DataTransfer();

            $('#attachments').on('change', function() {

                const newFiles = Array.from(this.files);

                newFiles.forEach(file => {
                    ticketFiles.items.add(file);
                });

                this.files = ticketFiles.files;

                renderAttachmentPreview();

            });

            function renderAttachmentPreview() {

                const preview = $('#attachmentPreview');

                preview.html('');

                Array.from(ticketFiles.files).forEach((file, index) => {

                    const size = (file.size / 1024 / 1024).toFixed(2);

                    preview.append(`
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800">

                            <div class="flex min-w-0 items-center gap-3">

                                <div class="text-xl">
                                    📄
                                </div>

                                <div class="min-w-0">

                                    <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">
                                        ${file.name}
                                    </p>

                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        ${size} MB
                                    </p>

                                </div>

                            </div>

                            <button
                                type="button"
                                class="remove-attachment rounded-lg px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10"
                                data-index="${index}">

                                Remove
                            </button>

                        </div>
                    `);

                });

            }

            $(document).on('click', '.remove-attachment', function() {

                const index = $(this).data('index');

                let newFileList = new DataTransfer();

                Array.from(ticketFiles.files).forEach((file, i) => {

                    if (i !== index) {
                        newFileList.items.add(file);
                    }

                });

                ticketFiles = newFileList;

                $('#attachments')[0].files = ticketFiles.files;

                renderAttachmentPreview();

            });

        });
    </script>

    {{-- Create Modal --}}
    <script>
        function initTicketSelect2() {

            $('#cpny_id').select2({
                dropdownParent: $('#createTicketModal'),
                placeholder: 'Select Company',
                width: '100%'
            });

            $('#department_id').select2({
                dropdownParent: $('#createTicketModal'),
                placeholder: 'Select Department',
                width: '100%'
            });

            $('#ticket_type').select2({
                dropdownParent: $('#createTicketModal'),
                placeholder: 'Select Type',
                width: '100%'
            });

            $('#ticket_categoryid').select2({
                dropdownParent: $('#createTicketModal'),
                placeholder: 'Select Category',
                width: '100%'
            });

            $('#ticket_subcategoryid').select2({
                dropdownParent: $('#createTicketModal'),
                placeholder: 'Select Subcategory',
                width: '100%'
            });

            $('#ticket_priority').select2({
                dropdownParent: $('#createTicketModal'),
                placeholder: 'Select Priority',
                width: '100%'
            });

            $('#location_id').select2({
                dropdownParent: $('#createTicketModal'),
                placeholder: 'Select Location',
                width: '100%'
            });

            $('#sub_location_id').select2({
                dropdownParent: $('#createTicketModal'),
                placeholder: 'Select Sub Location',
                width: '100%'
            });

        }

        function openModal(modalId) {

                $('body').addClass('modal-open');

                $(modalId)
                    .removeClass('hidden')
                    .hide()
                    .fadeIn(150);

            }

            function closeModal(modalId) {

                $(modalId).fadeOut(150);

                setTimeout(() => {

                    $(modalId).addClass('hidden');

                    const visibleModal = $('.fixed.inset-0').filter(function() {
                        return !$(this).hasClass('hidden');
                    });

                    if (!visibleModal.length) {
                        $('body').removeClass('modal-open');
                    }

                }, 150);

            }

        function openCreateTicketModal() {

            $('#createTicketModal')
                .removeClass('hidden')
                .hide()
                .fadeIn(150);

        }


        function closeCreateTicketModal() {

            $('#createTicketModal').fadeOut(150);

            setTimeout(() => {

                $('#createTicketModal').addClass('hidden');

                $('#formCreateTicket')[0].reset();

                $('#ticket_categoryid').html('<option value="">Select Category</option>');
                $('#ticket_subcategoryid').html('<option value="">Select Subcategory</option>');
                $('#ticket_priority').html('<option value="">Select Priority</option>');
                $('#sub_location_id').html('<option value="">Select Sub Location</option>');

            }, 150);

        }



        $(document).on('click', '#btnCreateTicket', function() {

            openCreateTicketModal();

        });

        $(document).on('click', '#closeCreateTicketModal, #cancelCreateTicket', function() {

            closeCreateTicketModal();

        });

        $(document).on('click', '#createTicketModal', function(e) {

            if (e.target.id === 'createTicketModal') {

                Swal.fire({
                    icon: 'warning',
                    title: 'Close Ticket Form?',
                    text: 'Unsaved form data will be lost.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Close',
                    cancelButtonText: 'Stay'
                }).then((result) => {

                    if (result.isConfirmed) {

                        closeCreateTicketModal();

                    }

                });

            }

        });

        $(document).on('keydown', function(e) {

            const modalVisible = !$('#createTicketModal').hasClass('hidden');

            const select2Open = $('.select2-container--open').length > 0;

            if (modalVisible && e.key === 'Escape' && !select2Open) {

                e.preventDefault();

                return false;

            }

        });

        $('#ticket_type').on('change', async function() {

            const ticketType = $(this).val();

            $('#ticket_categoryid').html('<option value="">Loading...</option>');

            try {

                const response = await $.ajax({
                    url: `{{ route('ticket.categoryByType') }}`,
                    type: 'GET',
                    data: {
                        ticket_type: ticketType
                    }
                });

                let html = '<option value="">Select Category</option>';

                response.forEach(row => {

                    html += `
                    <option value="${row.ticket_categoryid}">
                        ${row.ticket_category_name}
                    </option>
                `;

                });

                $('#ticket_categoryid')
                    .html(html)
                    .trigger('change.select2');

            } catch (err) {

                console.error(err);

            }

        });

        $('#ticket_categoryid').on('change', async function() {

            const ticketType = $('#ticket_type').val();
            const category = $(this).val();

            $('#ticket_subcategoryid').html('<option value="">Loading...</option>');
            $('#ticket_priority').html('<option value="">Loading...</option>');

            try {

                const subcategory = await $.ajax({
                    url: `{{ route('ticket.subcategoryByCategory') }}`,
                    type: 'GET',
                    data: {
                        ticket_type: ticketType,
                        ticket_categoryid: category
                    }
                });

                let subHtml = '<option value="">Select Subcategory</option>';

                subcategory.forEach(row => {

                    subHtml += `
                    <option value="${row.ticket_subcategoryid}">
                        ${row.ticket_subcategory_name}
                    </option>
                `;

                });

                $('#ticket_subcategoryid').html(subHtml);

                const priority = await $.ajax({
                    url: `{{ route('ticket.priorityByCategory') }}`,
                    type: 'GET',
                    data: {
                        ticket_type: ticketType,
                        ticket_categoryid: category
                    }
                });

                let priorityHtml = '<option value="">Select Priority</option>';

                priority.forEach(row => {

                    priorityHtml += `
                    <option value="${row.ticket_priority}">
                        ${row.ticket_priority_name}
                    </option>
                `;

                });

                $('#ticket_priority').html(priorityHtml);

            } catch (err) {

                console.error(err);

            }

        });

        $('#location_id').on('change', async function() {

            const locationId = $(this).val();

            $('#sub_location_id').html('<option value="">Loading...</option>');

            try {

                const response = await $.ajax({
                    url: `{{ route('ticket.subLocation') }}`,
                    type: 'GET',
                    data: {
                        location_id: locationId
                    }
                });

                let html = '<option value="">Select Sub Location</option>';

                response.forEach(row => {

                    html += `
                    <option value="${row.sub_location_id}">
                        ${row.sub_location_name}
                    </option>
                `;

                });

                $('#sub_location_id').html(html);

            } catch (err) {

                console.error(err);

            }

        });

        $('#formCreateTicket').on('submit', async function(e) {

            e.preventDefault();

            const form = this;
            const requiredFields = [
                '#ticket_type',
                '#ticket_categoryid',
                '#ticket_subcategoryid',
                '#ticket_priority',
                '#location_id',
                '#sub_location_id',
                'input[name="issue_summary"]',
                'textarea[name="issue_descr"]',
                '#attachments'
            ];

            let firstInvalid = null;

            requiredFields.forEach(selector => {

                const field = $(selector);

                if (!field.val() || field.val().length === 0) {

                    field.addClass('border-red-500');

                    if (!firstInvalid) {
                        firstInvalid = field;
                    }

                } else {

                    field.removeClass('border-red-500');

                }

            });

            if (firstInvalid) {

                $('html, body, #createTicketModal').animate({
                    scrollTop: firstInvalid.offset().top - 120
                }, 300);

                if (firstInvalid.hasClass('select2-hidden-accessible')) {

                    firstInvalid.select2('open');

                } else {

                    firstInvalid.focus();

                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Form',
                    text: 'Please complete all required fields.'
                });

                return;

            }

            const btn = $('#submitCreateTicket');

            btn.prop('disabled', true);

            btn.html(`
                <svg class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>

                Submitting...
            `);

            try {

                const formData = new FormData(form);

                const response = await $.ajax({
                    url: `{{ route('ticket.store') }}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 1800,
                    showConfirmButton: false
                });

                closeCreateTicketModal();

                loadTickets(currentStatus);

            } catch (err) {

                console.error(err);

                let msg = 'Failed create ticket';

                if (err.responseJSON?.message) {
                    msg = err.responseJSON.message;
                }

                if (err.status === 422 && err.responseJSON?.errors) {

                    msg = Object.values(err.responseJSON.errors)
                        .flat()
                        .join('<br>');

                }

                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: msg
                });

            } finally {

                btn.prop('disabled', false);

                btn.html(`Submit Ticket`);

            }

        });
    </script>

    <script>
        // ========================
        // EDIT MODAL
        // ========================

        let editTicketFiles = new DataTransfer();

        $(document).on('click', '.ticket-edit-btn', async function() {

            const eid = $(this).data('eid');

            try {

                const response = await $.ajax({
                    url: `/ticket/detail/${eid}`,
                    type: 'GET'
                });

                const row = response.ticket;
                const attachments = response.attachments || [];

                $('#edit_eid').val(eid);

                $('#edit_ticket_type').val(row.ticket_type ?? '-');
                $('#edit_ticket_category').val(row.ticket_categoryid ?? '-');
                $('#edit_ticket_subcategory').val(row.ticket_subcategoryid ?? '-');
                $('#edit_ticket_priority').val(row.ticket_priority ?? '-');

                $('#edit_issue_summary').val(row.issue_summary ?? '');
                $('#edit_issue_descr').val(row.issue_descr ?? '');

                let html = '';

                if (attachments.length) {

                    attachments.forEach(file => {

                        html += `
                    <a href="${file.url}"
                        target="_blank"
                        class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800">

                        <div class="min-w-0">

                            <p class="truncate text-sm font-semibold text-slate-700 dark:text-slate-200">
                                ${file.display_name}
                            </p>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                ${(file.extention || '').toUpperCase()}
                            </p>

                        </div>

                        <div class="text-xl">
                            📎
                        </div>

                    </a>
                `;
                    });

                } else {

                    html = `
                <div class="rounded-2xl border border-dashed border-slate-300 px-6 py-8 text-center dark:border-slate-700">

                    <div class="text-4xl">
                        📂
                    </div>

                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        No attachment available
                    </p>

                </div>
            `;
                }

                $('#edit_existing_attachments').html(html);

                $('#editTicketModal')
                    .removeClass('hidden')
                    .hide()
                    .fadeIn(150);

            } catch (err) {

                console.error(err);

                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: err?.responseJSON?.message ?? 'Failed load edit ticket.'
                });

            }

        });

        $(document).on('click',
            '#closeEditTicketModal, #cancelEditTicket',
            function() {

                $('#editTicketModal').fadeOut(150);

                setTimeout(() => {
                    $('#editTicketModal').addClass('hidden');
                }, 150);

            });

        $('#edit_attachments').on('change', function() {

            const files = Array.from(this.files);

            files.forEach(file => {
                editTicketFiles.items.add(file);
            });

            this.files = editTicketFiles.files;

            renderEditAttachmentPreview();

        });

        function renderEditAttachmentPreview() {

            let html = '';

            Array.from(editTicketFiles.files).forEach((file, index) => {

                html += `
            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800">

                <div>

                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                        ${file.name}
                    </p>

                </div>

                <button
                    type="button"
                    class="remove-edit-attachment rounded-lg px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50"
                    data-index="${index}">

                    Remove
                </button>

            </div>
        `;
            });

            $('#edit_attachment_preview').html(html);

        }

        $(document).on('click', '.remove-edit-attachment', function() {

            const index = $(this).data('index');

            let files = new DataTransfer();

            Array.from(editTicketFiles.files).forEach((file, i) => {

                if (i !== index) {
                    files.items.add(file);
                }

            });

            editTicketFiles = files;

            $('#edit_attachments')[0].files = editTicketFiles.files;

            renderEditAttachmentPreview();

        });

        $('#formEditTicket').on('submit', async function(e) {

            e.preventDefault();

            const eid = $('#edit_eid').val();

            const formData = new FormData(this);

            try {

                $('#submitEditTicket')
                    .prop('disabled', true)
                    .text('Updating...');

                await $.ajax({
                    url: `/ticket/update/${eid}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Ticket updated successfully.',
                    timer: 1800,
                    showConfirmButton: false
                });

                $('#editTicketModal').fadeOut(150);

                setTimeout(() => {
                    $('#editTicketModal').addClass('hidden');
                }, 150);

                loadTickets(currentStatus);

            } catch (err) {

                console.error(err);

                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: err?.responseJSON?.message ?? 'Failed update ticket.'
                });

            } finally {

                $('#submitEditTicket')
                    .prop('disabled', false)
                    .text('Update Ticket');

            }

        });

        // ========================
        // REOPEN MODAL
        // ========================

        $(document).on('click', '.ticket-reopen-btn', function() {

            const eid = $(this).data('eid');

            $('#reopen_eid').val(eid);

            $('#reopen_descr').val('');

            $('#reopenTicketModal')
                .removeClass('hidden')
                .hide()
                .fadeIn(150);

        });

        $(document).on('click',
            '#closeReopenTicketModal, #cancelReopenTicket',
            function() {

                $('#reopenTicketModal').fadeOut(150);

                setTimeout(() => {
                    $('#reopenTicketModal').addClass('hidden');
                }, 150);

            });

        $('#formReopenTicket').on('submit', async function(e) {

            e.preventDefault();

            const eid = $('#reopen_eid').val();

            try {

                $('#submitReopenTicket')
                    .prop('disabled', true)
                    .text('Submitting...');

                await $.ajax({
                    url: `/ticket/reopen/${eid}`,
                    type: 'POST',
                    data: {
                        _token: `{{ csrf_token() }}`,
                        reopen_descr: $('#reopen_descr').val()
                    }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Ticket reopened successfully.',
                    timer: 1800,
                    showConfirmButton: false
                });

                $('#reopenTicketModal').fadeOut(150);

                setTimeout(() => {
                    $('#reopenTicketModal').addClass('hidden');
                }, 150);

                loadTickets(currentStatus);

            } catch (err) {

                console.error(err);

                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: err?.responseJSON?.message ?? 'Failed reopen ticket.'
                });

            } finally {

                $('#submitReopenTicket')
                    .prop('disabled', false)
                    .text('Reopen Ticket');

            }

        });
    </script>

</x-app-layout>
