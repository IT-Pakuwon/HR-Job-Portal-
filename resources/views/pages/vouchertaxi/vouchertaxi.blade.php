<x-app-layout>
    <div class="mb-4 rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <!-- LEFT -->
            <div class="flex items-center gap-3">

                <!-- ICON -->
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-lg bg-gradient-to-br from-gray-700 to-gray-900 text-lg text-white shadow-sm">
                    🚕
                </div>

                <!-- TITLE -->
                <div>

                    <h1 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-200">

                        Taxi Booking
                    </h1>

                    <p class="mt-0.5 text-sm text-gray-500">
                        Manage booking requests and taxi vouchers
                    </p>

                </div>

            </div>

            <!-- RIGHT -->
            <div class="flex items-center gap-2">

                <!-- LIST -->
                <button id="toggleList"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                    <span>📋</span>

                    <span>
                        Listing
                    </span>

                </button>

                @if (auth()->check() && auth()->user()->hasRole('GAACCESS'))
                    <a href="{{ route('vouchertaxi.setup.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-100 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20">

                        <span class="text-base">
                            ⚙️
                        </span>

                        <span>
                            Voucher Taxi Setup
                        </span>

                    </a>
                @endif

                <!-- CREATE -->
                <button type="button" id="openCreateVoucherModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                    <i class="fa-solid fa-plus text-xs"></i>

                    New Booking

                </button>

            </div>

        </div>

    </div>

    <div id="mainGrid" class="grid grid-cols-1 items-start gap-5 lg:grid-cols-12 lg:items-stretch">

        <!-- 📅 CALENDAR -->
        <div id="calendarWrapper"
            class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a] lg:col-span-8">

            <div
                class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
                {{-- Calendar Legend --}}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-1">
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span> Pending
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Completed
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Processed
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Revised
                    </span>
                </div>
            </div>

            <div class="flex-1 p-4">
                <div id="calendar"></div>
            </div>

        </div>

        <!-- 📋 LIST PANEL -->
        <div id="voucherListPanel"
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a] lg:col-span-4">

            <div class="shrink-0 border-b border-gray-100 px-5 py-5 dark:border-white/10">

                <div class="flex items-start justify-between gap-3">

                    <div>

                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                            Request Queue
                        </div>

                        <h3 class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                            Taxi Requests
                        </h3>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Latest taxi requests and approval status
                        </p>

                    </div>


                    <div
                        class="flex h-10 min-w-[42px] items-center justify-center rounded-lg bg-indigo-50 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                        <span id="voucherCount">
                            0
                        </span>
                    </div>
                </div>

                <!-- Search -->

                <div class="relative mt-4">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input type="text" id="voucherSearch" placeholder="Search document, requester, destination..."
                        class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-3 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-white dark:focus:ring-indigo-500/20">
                </div>

                <!-- Filter -->

                <div class="mt-4 flex flex-wrap gap-2">
                    <button class="voucher-filter active-filter" data-filter="ALL">All</button>
                    <button class="voucher-filter" data-filter="P">Waiting Approval</button>
                    <button class="voucher-filter" data-filter="C">Completed</button>
                    @if (auth()->check() && auth()->user()->hasRole('GAACCESS'))
                        <button class="voucher-filter" data-filter="C">
                            Waiting Process
                        </button>
                    @endif
                    <button class="voucher-filter" data-filter="D">Revise</button>
                    <button class="voucher-filter" data-filter="R">Rejected</button>
                    <button class="voucher-filter" data-filter="X">Cancelled</button>

                </div>

            </div>

            <!-- Scroll Area -->

            <div class="flex-1 overflow-hidden bg-slate-50 dark:bg-[#0b1220]">

                <div id="voucherListBody" class="h-full space-y-3 overflow-y-auto overflow-x-hidden p-3">

                </div>

            </div>

            <!-- Footer -->

            <div
                class="flex shrink-0 items-center justify-between border-t border-slate-100 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#0f172a]">

                <div id="voucherPageInfo" class="text-xs text-slate-500 dark:text-slate-400">
                    Showing 0 - 0
                </div>

                <div class="flex items-center gap-2">

                    <button id="prevVoucherPage"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-200">

                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        Prev

                    </button>

                    <button id="nextVoucherPage"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-200">

                        Next
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>

                    </button>

                </div>

            </div>

        </div>
    </div>

    {{-- CREATE MODAL --}}
    <div id="createVoucherModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">
        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-5xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <div
                class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                        Create Voucher Taxi
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Taxi booking request form.
                    </p>
                </div>

                <button type="button" id="closeCreateVoucherModal"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="space-y-4 bg-slate-50 p-4 dark:bg-[#0b1220]">

                <form id="voucherTaxiForm" method="POST" class="space-y-4">
                    @csrf

                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Basic Information
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Company *
                                </label>

                                @if (count($usercpny) === 1)

                                    <input type="text" value="{{ $usercpny[0]->cpny_id }}" readonly
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm dark:border-white/10 dark:bg-white/[0.04]">

                                    <input type="hidden" id="cpny_id" name="cpny_id"
                                        value="{{ $usercpny[0]->cpny_id }}">
                                @else
                                    <select id="cpny_id" name="cpny_id"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                        required>

                                        <option value="">Select Company</option>

                                        @foreach ($usercpny as $p)
                                            <option value="{{ $p->cpny_id }}">
                                                {{ $p->cpny_id }}
                                            </option>
                                        @endforeach

                                    </select>

                                @endif
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Department *
                                </label>

                                @if (count($userdept) === 1)

                                    <input type="text" value="{{ $userdept[0]->department_id }}" readonly
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm dark:border-white/10 dark:bg-white/[0.04]">

                                    <input type="hidden" id="department_id" name="department_id"
                                        value="{{ $userdept[0]->department_id }}">
                                @else
                                    <select id="department_id" name="department_id"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                        required>

                                        <option value="">Select Department</option>

                                        @foreach ($userdept as $p)
                                            <option value="{{ $p->department_id }}">
                                                {{ $p->department_id }}
                                            </option>
                                        @endforeach

                                    </select>

                                @endif
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Requester
                                </label>

                                <input type="text" value="{{ auth()->user()->name }}" readonly
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                                <input type="hidden" id="user_peminta" name="user_peminta"
                                    value="{{ auth()->user()->username }}">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Date Used *
                                </label>

                                <input type="date" id="date_used" name="date_used"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100"
                                    required>
                            </div>

                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-5 py-2 dark:border-white/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Trip Information
                            </h3>

                            <div class="flex gap-2 rounded-lg bg-slate-100 p-1 dark:bg-white/[0.04]">

                                <label class="cursor-pointer">
                                    <input type="radio" name="type_trip" value="Return" checked
                                        class="peer hidden">

                                    <span
                                        class="rounded-md px-3 py-1 text-xs peer-checked:bg-slate-900 peer-checked:text-white dark:peer-checked:bg-blue-600">
                                        Return
                                    </span>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="type_trip" value="One Way" class="peer hidden">

                                    <span
                                        class="rounded-md px-3 py-1 text-xs peer-checked:bg-slate-900 peer-checked:text-white dark:peer-checked:bg-blue-600">
                                        One Way
                                    </span>
                                </label>

                            </div>

                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Origin *
                                </label>

                                <input type="text" id="origin" name="origin" placeholder="From where?"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Destination *
                                </label>

                                <input type="text" id="destination" name="destination" placeholder="Where to?"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Purpose *
                                </label>

                                <select id="purpose" name="purpose_id"
                                    class="w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>
                                    <option value="">Select Purpose</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Purpose Description *
                                </label>

                                <textarea id="purpose_desc" name="purpose_descr" rows="4"
                                    placeholder="Explain the purpose of this voucher request..."
                                    class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-white"
                                    required></textarea>
                            </div>
                        </div>

                    </div>

                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Finance Information
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Company Expense *
                                </label>

                                <select id="cpny_id_expense" name="cpny_id_expense"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                                    <option value="">Select Company</option>

                                    @foreach ($company as $p)
                                        <option value="{{ $p->cpny_id }}">
                                            {{ $p->cpny_name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Topup *
                                </label>

                                <select id="user_topup" name="user_topup"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>
                                    <option value="">Select User</option>
                                    @if (isset($requesters))
                                        @foreach ($requesters as $emp)
                                            <option value="{{ $emp->username }}">
                                                {{ $emp->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                        </div>

                    </div>

                </form>

            </div>

            <div
                class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                <div class="flex items-center justify-end gap-3">

                    <button type="button" id="closeCreateVoucherModalFooter"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                        Cancel
                    </button>

                    <button type="submit" form="voucherTaxiForm"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                        <i class="fa-solid fa-paper-plane text-xs"></i>

                        Submit Request

                    </button>

                </div>

            </div>

        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div id="editVoucherTaxiModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-5xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <div
                class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                        Edit Voucher Taxi
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Update taxi booking request information.
                    </p>
                </div>

                <button type="button" id="closeEditVoucherModal"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>

            </div>

            <div class="space-y-4 bg-slate-50 p-4 dark:bg-[#0b1220]">

                <form id="editVoucherTaxiForm" method="POST" class="space-y-4">

                    @csrf

                    <input type="hidden" id="edit_docid" name="docid">
                    <input type="hidden" id="edit_eid">
                    {{-- BASIC INFORMATION --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Basic Information
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Company *
                                </label>

                                <select id="edit_cpny_id" name="cpny_id"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                                    <option value="">
                                        Select Company
                                    </option>

                                    @foreach ($company as $p)
                                        <option value="{{ $p->cpny_id }}">
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Department *
                                </label>

                                <select id="edit_department_id" name="department_id"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                                    <option value="">
                                        Select Department
                                    </option>

                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}">
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Requester
                                </label>

                                <input type="text" id="edit_requester_name" readonly
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                                <input type="hidden" id="edit_user_peminta" name="user_peminta">

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Date Used *
                                </label>

                                <input type="date" id="edit_date_used" name="date_used"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100"
                                    required>

                            </div>

                        </div>

                    </div>

                    {{-- TRIP INFORMATION --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-5 py-2 dark:border-white/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Trip Information
                            </h3>

                            <div class="flex gap-2 rounded-lg bg-slate-100 p-1 dark:bg-white/[0.04]">

                                <label class="cursor-pointer">
                                    <input type="radio" name="type_trip" value="Return" class="peer hidden">

                                    <span
                                        class="rounded-md px-3 py-1 text-xs peer-checked:bg-slate-900 peer-checked:text-white dark:peer-checked:bg-blue-600">
                                        Return
                                    </span>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="type_trip" value="One Way" class="peer hidden">

                                    <span
                                        class="rounded-md px-3 py-1 text-xs peer-checked:bg-slate-900 peer-checked:text-white dark:peer-checked:bg-blue-600">
                                        One Way
                                    </span>
                                </label>

                            </div>

                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Origin *
                                </label>

                                <input type="text" id="edit_origin" name="origin"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Destination *
                                </label>

                                <input type="text" id="edit_destination" name="destination"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Purpose *
                                </label>

                                <select id="edit_purpose" name="purpose_id"
                                    class="w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>
                                    <option value="">Select Purpose</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Purpose Description *
                                </label>

                                <textarea id="edit_purpose_desc" name="purpose_descr" rows="4"
                                    class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-white"
                                    required></textarea>

                            </div>

                        </div>

                    </div>
                    {{-- REVISION REASON --}}
                    <div id="editReviseReasonWrapper"
                        class="hidden overflow-hidden rounded-lg border border-amber-200 bg-white dark:border-amber-500/20 dark:bg-[#0f172a]">

                        <div class="border-b border-amber-200 px-5 py-2 dark:border-amber-500/20">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">
                                Revision Reason
                            </h3>

                        </div>

                        <div id="edit_revise_reason"
                            class="p-5 text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                        </div>

                    </div>

                    {{-- FINANCE INFORMATION --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Finance Information
                            </h3>

                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Company Expense *
                                </label>

                                <select id="edit_cpny_id_expense" name="cpny_id_expense"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                                    <option value="">
                                        Select Company
                                    </option>

                                    @foreach ($company as $p)
                                        <option value="{{ $p->cpny_id }}">
                                            {{ $p->cpny_name }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Topup *
                                </label>

                                <select id="edit_user_topup" name="user_topup"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>
                                    <option value="">Select User</option>
                                    @if (isset($requesters))
                                        @foreach ($requesters as $emp)
                                            <option value="{{ $emp->username }}">
                                                {{ $emp->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                        </div>

                    </div>

                </form>

            </div>

            {{-- FOOTER --}}
            <div
                class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                <div class="flex items-center justify-between">

                    <button type="button" id="closeEditVoucherModalFooter"
                        class="text-sm text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">

                        Cancel

                    </button>

                    <div class="flex items-center gap-3">

                        <button type="button" id="resetEditVoucherBtn"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                            <i class="fa-solid fa-rotate-left text-xs"></i>

                            Reset

                        </button>

                        <button type="submit" form="editVoucherTaxiForm"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                            <i class="fa-solid fa-paper-plane text-xs"></i>

                            Save Changes

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- DETAIL MODAL --}}
    <div id="viewVoucherModal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-7xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <input type="hidden" id="view_eid">
            <input type="hidden" id="view_docid">

            <div
                class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                <div>

                    <h2 id="detailDocIdTitle" class="font-semibold text-slate-800 dark:text-white">
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Voucher information & approval workflow.
                    </p>

                </div>

                <div class="flex items-center gap-3">

                    <a id="printVoucherBtn" href="#" target="_blank"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-500">

                        <i class="fa-solid fa-print text-xs"></i>

                        Print PDF

                    </a>

                    <button type="button" id="closeViewVoucherModal"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-lg"></i>

                    </button>

                </div>

            </div>

            <div class="grid grid-cols-1 gap-4 bg-slate-50 p-4 dark:bg-[#0b1220] lg:grid-cols-[1.1fr_.9fr]">

                <div class="space-y-4">

                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-white/10">

                            <div>

                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                    Requester
                                </div>

                                <div id="view_user"
                                    class="mt-2 text-base font-semibold text-slate-900 dark:text-white">
                                </div>

                            </div>

                            <div id="view_status_badge">
                                Pending
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-5 p-5">

                            <div>
                                <div class="text-xs text-slate-500">Date Used</div>
                                <div id="view_date"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100"></div>
                            </div>

                            <div>
                                <div class="text-xs text-slate-500">Trip Type</div>
                                <div id="view_type_trip"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100"></div>
                            </div>

                            <div>
                                <div class="text-xs text-slate-500">Origin</div>
                                <div id="view_origin"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100"></div>
                            </div>

                            <div>
                                <div class="text-xs text-slate-500">Destination</div>
                                <div id="view_destination"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100"></div>
                            </div>

                            <div>
                                <div class="text-xs text-slate-500">Company — Company Expense</div>
                                <div
                                    class="mt-1 flex items-center gap-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                                    <span id="view_cpny"></span>
                                    <span class="text-slate-400">–</span>
                                    <span id="view_cpny_expense"></span>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-slate-500">Department</div>
                                <div id="view_dept"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100"></div>
                            </div>

                            <div>
                                <div class="text-xs text-slate-500">Top Up User</div>
                                <div id="view_topup_user"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100"></div>
                            </div>

                        </div>

                    </div>

                    <div
                        class="overflow-hidden rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-500/20 dark:bg-blue-500/10">

                        <div
                            class="flex items-center justify-between border-b border-blue-100 px-5 py-2 dark:border-blue-500/20">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">
                                Route
                            </h3>

                            <div id="view_trip_type_badge"
                                class="rounded-lg bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                            </div>

                        </div>

                        <div id="view_route" class="p-5 text-sm font-semibold text-blue-900 dark:text-blue-200">
                        </div>

                    </div>

                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-5 py-2 dark:border-white/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Purpose
                            </h3>

                            <div id="view_purpose_name"
                                class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                            </div>

                        </div>

                        <div id="view_purpose" class="p-5 text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                        </div>

                    </div>

                    <div id="actualExpenseWrapper"
                        class="hidden overflow-hidden rounded-lg border border-emerald-200 bg-emerald-50 dark:border-emerald-500/20 dark:bg-emerald-500/10">

                        <div class="border-b border-emerald-100 px-5 py-2 dark:border-emerald-500/20">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                                Actual Expense
                            </h3>

                        </div>

                        <div class="flex items-center justify-between p-5">

                            <div id="view_actual_budget"
                                class="text-sm font-bold text-emerald-800 dark:text-emerald-200">
                                Rp 0
                            </div>

                            <span
                                class="rounded-lg bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                Processed
                            </span>

                        </div>

                    </div>

                </div>

                <div class="space-y-4">

                    <div id="reviseReasonWrapper"
                        class="hidden overflow-hidden rounded-lg border border-yellow-200 bg-yellow-50 dark:border-yellow-500/20 dark:bg-yellow-500/10">

                        <div class="border-b border-yellow-100 px-5 py-2 dark:border-yellow-500/20">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-yellow-700 dark:text-yellow-300">
                                Revision Reason
                            </h3>

                        </div>

                        <div id="view_revise_reason"
                            class="p-5 text-sm leading-relaxed text-yellow-900 dark:text-yellow-100">
                        </div>

                    </div>

                    <div class="overflow-hidden">

                        <div class="flex items-center gap-2">

                            <div id="viewActions" class="mb-4 flex w-full items-center gap-2">
                            </div>

                            <div id="approvalActions"
                                class="mb-4 flex hidden w-full items-center justify-between gap-2">

                                <button type="button" id="approveBtn"
                                    class="flex-1 rounded-lg bg-emerald-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-400">
                                    <i class="fa-solid fa-check mr-1"></i>
                                    Approve
                                </button>

                                <button type="button" id="reviseBtn"
                                    class="flex-1 rounded-lg bg-yellow-400 px-4 py-2 text-xs font-semibold text-black transition hover:bg-yellow-300">
                                    <i class="fa-solid fa-rotate-left mr-1"></i>
                                    Revise
                                </button>

                                <button type="button" id="rejectBtn"
                                    class="flex-1 rounded-lg bg-red-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-400">
                                    <i class="fa-solid fa-xmark mr-1"></i>
                                    Reject
                                </button>

                            </div>

                        </div>

                        <div id="approvalFlow">
                        </div>

                    </div>

                </div>
            </div>

            <div
                class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                <div class="flex items-center justify-between">

                    <button type="button" id="closeViewVoucherModalFooter"
                        class="text-sm text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">

                        Close

                    </button>

                    <div class="flex items-center gap-3">

                        <button type="button" id="cancelVoucherBtn"
                            class="hidden rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-500">

                            Cancel Request

                        </button>

                        <button type="button" id="openEditFromViewBtn"
                            class="hidden rounded-lg bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                            Edit Voucher

                        </button>

                        <button type="button" id="processVoucherBtn"
                            class="hidden rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500">

                            <i class="fa-solid fa-taxi mr-1.5"></i>
                            Process

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- PROCESS MODAL --}}
    <div id="processVoucherModal" class="fixed inset-0 z-[80] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-5xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <form id="processVoucherForm">

                <input type="hidden" id="process_docid">

                {{-- HEADER --}}
                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/95 px-6 py-4 dark:border-white/10 dark:bg-[#0f172a]/95">

                    <div class="flex items-center gap-4">

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">

                            <i class="fa-solid fa-money-bill-wave text-lg"></i>

                        </div>

                        <div>

                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                Process Voucher Taxi
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Review voucher information and update actual transportation expense.
                            </p>

                        </div>

                    </div>

                    <button type="button" id="closeProcessVoucherModal"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-lg"></i>

                    </button>

                </div>

                {{-- BODY --}}
                <div class="space-y-4 bg-slate-50 p-4 dark:bg-[#0b1220]">

                    {{-- DOCUMENT INFORMATION --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-white/10">

                            <div>

                                <div id="process_docno" class="text-lg font-bold text-slate-900 dark:text-white">
                                    -
                                </div>

                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Voucher approved and ready for processing.
                                </div>

                            </div>

                            <div id="process_status">
                                <span
                                    class="rounded-lg bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                                    Waiting Process
                                </span>
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-x-8 gap-y-3 p-4">

                            <div>
                                <span class="text-xs text-slate-500">Requester</span>
                                <div id="process_requester" class="text-sm font-semibold">
                                    -
                                </div>
                            </div>

                            <div>
                                <span class="text-xs text-slate-500">Date Used</span>
                                <div id="process_date" class="text-sm font-semibold">
                                    -
                                </div>
                            </div>

                            <div>
                                <span class="text-xs text-slate-500">Company</span>
                                <div id="process_company" class="text-sm font-semibold">
                                    -
                                </div>
                            </div>

                            <div>
                                <span class="text-xs text-slate-500">Department</span>
                                <div id="process_department" class="text-sm font-semibold">
                                    -
                                </div>
                            </div>

                            <div>
                                <span class="text-xs text-slate-500">Trip Type</span>
                                <div id="process_trip" class="text-sm font-semibold">
                                    -
                                </div>
                            </div>

                            <div>
                                <span class="text-xs text-slate-500">Route</span>
                                <div id="process_route" class="text-sm font-bold text-emerald-600">
                                    -
                                </div>
                            </div>

                        </div>

                        <div class="border-t border-slate-200 px-4 py-3">

                            <div class="text-xs text-slate-500">
                                Purpose
                            </div>

                            <div id="process_purpose" class="mt-1 text-sm text-slate-700">
                                -
                            </div>

                        </div>

                    </div>

                    {{-- PROCESS VOUCHER --}}
                    <div
                        class="overflow-hidden rounded-lg border border-emerald-200 bg-white dark:border-emerald-500/20 dark:bg-[#0f172a]">

                        <div
                            class="border-b border-emerald-100 bg-emerald-50 px-5 py-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                                Process Voucher
                            </h3>

                            <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">
                                Input actual transportation expense and optionally update expense ownership.
                            </p>

                        </div>

                        <div class="space-y-5 p-5">

                            {{-- ACTUAL BUDGET --}}
                            <div>

                                <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">

                                    Actual Budget
                                    <span class="text-red-500">*</span>

                                </label>

                                <div class="relative">

                                    <span
                                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400">

                                        Rp

                                    </span>

                                    <input type="text" id="actual_budget_display" placeholder="0"
                                        autocomplete="off"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-900 focus:border-emerald-500 focus:outline-none dark:border-white/10 dark:bg-[#0b1220] dark:text-white">

                                    <input type="hidden" id="actual_budget" name="actual_budget">

                                </div>

                                <p class="mt-2 text-xs text-slate-500">
                                    Actual amount paid for this transportation voucher.
                                </p>

                            </div>

                            {{-- UPDATE EXPENSE OWNER --}}
                            <div
                                class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-500/20 dark:bg-indigo-500/10">

                                <label class="flex cursor-pointer items-start gap-3">

                                    <input type="checkbox" id="changeExpenseOwner" name="change_expense_owner"
                                        value="1" class="mt-1 rounded border-slate-300">

                                    <div>

                                        <div class="text-sm font-semibold text-indigo-800 dark:text-indigo-200">

                                            Update Expense Owner

                                        </div>

                                        <div class="mt-1 text-xs text-indigo-600 dark:text-indigo-300">

                                            Enable this option if the expense should be charged to a different company,
                                            department, or employee.

                                        </div>

                                    </div>

                                </label>

                            </div>

                            {{-- NEW EXPENSE OWNER --}}
                            <div id="expenseOwnerSection"
                                class="hidden rounded-lg border border-indigo-200 bg-indigo-50 p-5 dark:border-indigo-500/20 dark:bg-indigo-500/10">

                                <div
                                    class="mb-4 text-sm font-semibold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">

                                    New Expense Owner

                                </div>

                                <div class="grid gap-4 md:grid-cols-3">

                                    {{-- COMPANY --}}
                                    <div>

                                        <label
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                            Company
                                            <span class="text-red-500">*</span>

                                        </label>

                                        <select id="process_cpny_id_expense" name="cpny_id_expense"
                                            class="select2-process h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">

                                            <option value="">
                                                Select Company
                                            </option>

                                            @foreach ($company as $c)
                                                <option value="{{ $c->cpny_id }}">
                                                    {{ $c->cpny_name }}
                                                </option>
                                            @endforeach

                                        </select>

                                    </div>

                                    {{-- DEPARTMENT --}}
                                    <div>

                                        <label
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                            Department
                                            <span class="text-red-500">*</span>

                                        </label>

                                        <select id="process_department_id_expense" name="department_id_expense"
                                            class="select2-process h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">

                                            <option value="">
                                                Select Department
                                            </option>

                                            @foreach ($departments as $d)
                                                <option value="{{ $d->department_id }}">
                                                    {{ $d->department_name }}
                                                </option>
                                            @endforeach

                                        </select>

                                    </div>

                                    {{-- EMPLOYEE --}}
                                    <div>

                                        <label
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                            Employee
                                            <span class="text-red-500">*</span>

                                        </label>

                                        <select id="process_user_peminta_expense" name="user_peminta_expense"
                                            class="select2-process h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm">
                                            <option value="">Select Employee</option>
                                        </select>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- FOOTER --}}
                <div
                    class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                    <div class="flex items-center justify-end gap-3">

                        <button type="button" id="closeProcessVoucherModalFooter"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                            Cancel

                        </button>

                        <button type="submit" id="submitProcessVoucherBtn"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700">

                            <i class="fa-solid fa-floppy-disk text-xs"></i>

                            Save Process

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('assets/js/vouchertaxi/core.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/helper.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/modal.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/request-form.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/edit-form.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/datalist.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/detail-modal.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/tracking.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/approval.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/process.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/calendar.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/auto-open.js') }}"></script>
    <script src="{{ asset('assets/js/vouchertaxi/init.js') }}"></script>
</x-app-layout>
