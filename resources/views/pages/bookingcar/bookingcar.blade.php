<x-app-layout>

    <div class="mb-4 rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center rounded-lg bg-gradient-to-br from-gray-700 to-gray-900 text-lg text-white shadow-sm">

                    🚘

                </div>

                <div>

                    <h1 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-200">

                        Booking Car

                    </h1>

                    <p class="mt-0.5 text-sm text-gray-500">

                        Manage booking requests and vehicle schedules

                    </p>

                </div>

            </div>

            <div class="flex items-center gap-2">

                <button id="toggleList"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                    <span>📋</span>

                    <span>
                        Listing
                    </span>

                </button>

                @if (auth()->check() && auth()->user()->hasRole('GAACCESS'))
                    <a href="{{ route('bookingcar.setup.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-100 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20">

                        <span class="text-base">
                            ⚙️
                        </span>

                        <span>
                            Booking Car Setup
                        </span>

                    </a>
                @endif

                <button type="button" id="openCreateBookingModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                    <i class="fa-solid fa-plus text-xs"></i>

                    New Booking

                </button>

            </div>

        </div>

    </div>

    <div id="mainGrid" class="grid grid-cols-1 items-start gap-5 lg:grid-cols-12 lg:items-stretch">

        <!-- CALENDAR WRAPPER (left side) -->
        <div id="calendarWrapper"
            class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a] lg:col-span-8">

            <div
                class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
                <!-- ... existing header content ... -->
            </div>

            <div class="flex-1 p-4">

                {{-- Calendar Legend --}}
                <div class="mb-3 flex flex-wrap items-center gap-x-4 gap-y-2 px-1">
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span> Pending
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Approved
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Processed
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Revised
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span> Rejected
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span> Cancelled
                    </span>
                </div>

                <div id="calendar"></div>
            </div>

        </div>

        <!-- BOOKING LIST PANEL (right side) — matches calendar height -->
        <div id="bookingListPanel"
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a] lg:col-span-4">

            <!-- HEADER: Search and Filters -->
            <div class="shrink-0 border-b border-gray-100 px-5 py-5 dark:border-white/10">

                <div class="flex items-start justify-between gap-3">

                    <div>
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                            Request Queue
                        </div>

                        <h3 class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                            Booking Requests
                        </h3>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Latest booking requests and approval status
                        </p>
                    </div>

                    <div
                        class="flex h-10 min-w-[42px] items-center justify-center rounded-lg bg-indigo-50 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                        <span id="bookingCount">0</span>
                    </div>

                </div>

                <!-- Search Input -->
                <div class="relative mt-4">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input type="text" id="bookingSearch" placeholder="Search document, requester, destination..."
                        class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-white dark:placeholder:text-slate-500 dark:focus:ring-indigo-500/20">
                </div>

                <!-- Filter Buttons -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <button class="booking-filter active-filter" data-filter="ALL">All</button>
                    <button class="booking-filter" data-filter="P">Pending</button>
                    <button class="booking-filter" data-filter="C">Approved</button>
                    @if (auth()->check() && auth()->user()->hasRole('GAACCESS'))
                        <button class="booking-filter" data-filter="WAITING_PROCESS">Waiting Process</button>
                    @endif
                    <button class="booking-filter" data-filter="D">Revise</button>
                    <button class="booking-filter" data-filter="R">Rejected</button>
                    <button class="booking-filter" data-filter="X">Closed</button>
                </div>

            </div>

            <!-- SCROLL AREA: Booking List Items (grows to fill available space) -->
            <div class="flex-1 overflow-hidden bg-slate-50 dark:bg-[#0b1220]">
                <div id="bookingListBody" class="h-full space-y-3 overflow-y-auto overflow-x-hidden p-3">
                    <!-- Items rendered here by JS -->
                </div>
            </div>

            <!-- FOOTER: Pagination (stays at bottom, doesn't scroll) -->
            <div
                class="flex shrink-0 items-center justify-between border-t border-slate-100 bg-white px-4 py-3 dark:border-white/10 dark:bg-[#0f172a]">

                <div id="bookingPageInfo" class="text-xs text-slate-500 dark:text-slate-400">
                    Showing 0 - 0
                </div>

                <div class="flex items-center gap-2">
                    <button id="prevBookingPage"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-200">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        Prev
                    </button>

                    <button id="nextBookingPage"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-200">
                        Next
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>

            </div>

        </div>

    </div>

    {{-- CREATE MODAL --}}
    <div id="createBookingModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-5xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <div
                class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                <div>

                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                        Create Booking Car
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Vehicle booking request form.
                    </p>

                </div>

                <button type="button" id="closeCreateBookingModal"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                    <i class="fa-solid fa-xmark text-lg"></i>

                </button>

            </div>

            <div class="space-y-4 bg-slate-50 p-4 dark:bg-[#0b1220]">

                <form id="bookingCarForm" method="POST" class="space-y-4">

                    @csrf

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

                            {{-- COMPANY --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                    Company *

                                </label>

                                <select id="cpny_id" name="cpny_id"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                                    <option value="">
                                        Select Company
                                    </option>

                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}">
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            {{-- DEPARTMENT --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                    Department *

                                </label>

                                <select id="department_id" name="department_id"
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

                            {{-- REQUESTER --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                    Requester

                                </label>

                                <input type="text" readonly value="{{ auth()->user()->name }}"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm dark:border-white/10 dark:bg-white/[0.04]">

                                <input type="hidden" id="user_peminta" name="user_peminta"
                                    value="{{ auth()->user()->username }}">

                            </div>

                            {{-- PASSENGER --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                    Total Passenger *

                                </label>

                                <input type="number" id="passenger" name="passenger" min="1"
                                    placeholder="Input total passenger"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                            </div>

                        </div>

                    </div>

                    {{-- SCHEDULE --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">

                                Schedule Information

                            </h3>

                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                    Booking Date *

                                </label>

                                <input type="date" id="booking_date" name="booking_date"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                    Start Time *

                                </label>

                                <input type="time" id="start_time" name="start_time"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                    End Time *

                                </label>

                                <input type="time" id="end_time" name="end_time"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                            </div>

                        </div>

                    </div>

                    {{-- ROUTE INFORMATION --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-5 py-2 dark:border-white/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">

                                Route Information

                            </h3>

                            <button type="button" id="createAddRouteBtn"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                                <i class="fa-solid fa-plus text-[10px]"></i>

                                Add Route

                            </button>

                        </div>

                        <div class="overflow-hidden">

                            <table class="min-w-full text-sm">

                                <thead class="bg-slate-50 dark:bg-white/[0.03]">

                                    <tr>

                                        <th class="w-16 px-4 py-3 text-left">
                                            No
                                        </th>

                                        <th class="px-4 py-3 text-left">
                                            Pickup
                                        </th>

                                        <th class="px-4 py-3 text-left">
                                            Destination
                                        </th>

                                        <th class="w-20 px-4 py-3"></th>

                                    </tr>

                                </thead>

                                <tbody id="createRouteTableBody"></tbody>

                            </table>

                        </div>

                    </div>

                    {{-- PURPOSE --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">

                                Purpose Information

                            </h3>

                        </div>

                        <div class="space-y-4 p-5">

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Company Expense *

                                    </label>

                                    <select id="cpny_id_site" name="cpny_id_site"
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

                                        User Request *

                                    </label>


                                    <select id="user_request" name="user_request"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                        required>

                                        <option value="">
                                            Select passenger
                                        </option>

                                        @foreach ($requesters as $p)
                                            <option value="{{ $p->username }}"
                                                data-dept="{{ trim($p->department_id) }}">

                                                {{ $p->name }}

                                            </option>
                                        @endforeach

                                    </select>

                                </div>
                            </div>


                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                    Purpose *

                                </label>

                                <select id="purpose_id" name="purpose_id"
                                    class="w-full rounded-lg border border-slate-200 dark:border-white/10" required>

                                    <option value="">
                                        Select purpose
                                    </option>

                                    @foreach ($purposes as $purpose)
                                        <option value="{{ $purpose->categoryid }}">
                                            {{ $purpose->category_name }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                    Purpose Description *

                                </label>

                                <textarea id="purpose_descr" name="purpose_descr" rows="4" placeholder="Explain the booking purpose..."
                                    class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-white"
                                    required></textarea>

                            </div>
                        </div>

                    </div>

                </form>

            </div>

            <div
                class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                <div class="flex items-center justify-end gap-3">

                    <button type="button" id="closeCreateBookingModalFooter"
                        class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                        Cancel

                    </button>

                    <button type="submit" form="bookingCarForm"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                        <i class="fa-solid fa-paper-plane text-xs"></i>

                        Submit Request

                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- VIEW MODAL --}}
    <div id="viewBookingModal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-7xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <input type="hidden" id="view_booking_eid">
            <input type="hidden" id="view_booking_docid">

            <div
                class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                <div>

                    <h2 id="detailBookingTitle" class="font-semibold text-slate-800 dark:text-white">
                        Booking Detail
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Booking information & approval workflow.
                    </p>

                </div>

                <div class="flex items-center gap-3">

                    <a id="printBookingBtn" href="#" target="_blank"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-500">

                        <i class="fa-solid fa-print text-xs"></i>

                        Print PDF

                    </a>

                    <button type="button" id="closeViewBookingModal"
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

                                <div id="view_booking_user"
                                    class="mt-2 text-base font-semibold text-slate-900 dark:text-white">
                                </div>

                            </div>

                            <div id="view_booking_status_badge">

                                Pending

                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-5 p-5">

                            <div>

                                <div class="text-xs text-slate-500">
                                    Booking Date
                                </div>

                                <div id="view_booking_date"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                                </div>

                            </div>

                            <div>

                                <div class="text-xs text-slate-500">
                                    Total Passenger
                                </div>

                                <div id="view_booking_passenger"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                                </div>

                            </div>

                            <div>

                                <div class="text-xs text-slate-500">
                                    Start Time
                                </div>

                                <div id="view_booking_start"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                                </div>

                            </div>

                            <div>

                                <div class="text-xs text-slate-500">
                                    End Time
                                </div>

                                <div id="view_booking_end"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                                </div>

                            </div>

                            <div>

                                <div class="text-xs text-slate-500">
                                    Company - Company Request
                                </div>

                                <div class='flex'>
                                    <div id="view_booking_cpny"
                                        class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                                    </div>

                                    -

                                    <div id="view_booking_cpny_site"
                                        class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                                    </div>
                                </div>
                            </div>

                            <div>

                                <div class="text-xs text-slate-500">
                                    Department
                                </div>

                                <div id="view_booking_dept"
                                    class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                                </div>

                            </div>

                        </div>

                    </div>

                    <div
                        class="overflow-hidden rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-500/20 dark:bg-blue-500/10">

                        <div
                            class="flex items-center justify-between border-b border-blue-100 px-5 py-2 dark:border-blue-500/20">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">

                                Route Detail

                            </h3>

                            <div id="view_total_route_badge"
                                class="rounded-lg bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">

                                0 Route

                            </div>

                        </div>

                        <div class="overflow-x-auto">

                            <table class="min-w-full">

                                <thead class="border-b border-blue-100 dark:border-blue-500/20">

                                    <tr>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">

                                            No

                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">

                                            Pickup

                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">

                                            Destination

                                        </th>

                                    </tr>

                                </thead>

                                <tbody id="view_booking_route_table"
                                    class="divide-y divide-blue-100 dark:divide-blue-500/20">

                                </tbody>

                            </table>

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

                            <div id="view_booking_purpose_name"
                                class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                            </div>

                        </div>

                        <div id="view_booking_purpose"
                            class="p-5 text-sm leading-relaxed text-slate-700 dark:text-slate-200">

                        </div>

                    </div>

                    <div id="driverInfoWrapper"
                        class="hidden overflow-hidden rounded-lg border border-emerald-200 bg-emerald-50 dark:border-emerald-500/20 dark:bg-emerald-500/10">

                        <div class="border-b border-emerald-100 px-5 py-2 dark:border-emerald-500/20">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">

                                Driver Information

                            </h3>

                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-3">

                            <div>

                                <div class="text-xs text-emerald-600 dark:text-emerald-300">
                                    Driver Name
                                </div>

                                <div id="view_booking_driver"
                                    class="mt-1 text-sm font-semibold text-emerald-900 dark:text-emerald-100">
                                </div>

                            </div>

                            <div>

                                <div class="text-xs text-emerald-600 dark:text-emerald-300">
                                    Phone
                                </div>

                                <div id="view_booking_handphone"
                                    class="mt-1 text-sm font-semibold text-emerald-900 dark:text-emerald-100">
                                </div>

                            </div>

                            <div>

                                <div class="text-xs text-emerald-600 dark:text-emerald-300">
                                    Vehicle Plate
                                </div>

                                <div id="view_booking_nopol"
                                    class="mt-1 text-sm font-semibold text-emerald-900 dark:text-emerald-100">
                                </div>

                            </div>

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

                            <div id="bookingViewActions" class="mb-4 flex w-full items-center gap-2">
                            </div>

                            <div id="bookingApprovalActionsWrapper"
                                class="mb-4 hidden w-full items-center justify-between gap-2">

                                <button type="button" id="approveBookingBtn"
                                    class="flex-1 rounded-lg bg-emerald-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-400">

                                    <i class="fa-solid fa-check mr-1"></i>
                                    Approve

                                </button>

                                <button type="button" id="reviseBookingBtn"
                                    class="flex-1 rounded-lg bg-yellow-400 px-4 py-2 text-xs font-semibold text-black transition hover:bg-yellow-300">

                                    <i class="fa-solid fa-rotate-left mr-1"></i>
                                    Revise

                                </button>

                                <button type="button" id="rejectBookingBtn"
                                    class="flex-1 rounded-lg bg-red-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-400">

                                    <i class="fa-solid fa-xmark mr-1"></i>
                                    Reject

                                </button>

                            </div>

                        </div>

                        <div id="bookingTrackingTimeline">
                        </div>

                    </div>

                </div>
            </div>

            <div
                class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                <div class="flex items-center justify-between">

                    <button type="button" id="closeViewBookingModalFooter"
                        class="text-sm text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">

                        Close

                    </button>

                    <div class="flex items-center gap-3">

                        <button type="button" id="cancelBookingBtn"
                            class="hidden rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-500">

                            Cancel Request

                        </button>

                        <button type="button" id="processBookingBtn"
                            class="hidden rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500">

                            <i class="fa-solid fa-car mr-1.5"></i>
                            Process

                        </button>

                        <button type="button" id="changeExpenseBtn"
                            class="hidden rounded-lg border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">

                            <i class="fa-solid fa-building mr-1.5"></i>
                            Change Expense

                        </button>

                        <button type="button" id="editBookingBtn"
                            class="hidden rounded-lg bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                            Edit Booking

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- EDIT MODAL --}}
    <div id="editBookingModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-5xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <form id="editBookingForm" method="POST" class="flex flex-col">

                @csrf

                <input type="hidden" id="edit_booking_docid">
                <input type="hidden" id="edit_booking_eid">

                {{-- HEADER --}}
                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div>

                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                            Edit Booking Car
                        </h2>

                        <p id="editBookingDocInfo" class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Vehicle booking edit form.
                        </p>

                    </div>

                    <button type="button" id="closeEditBookingModal"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-lg"></i>

                    </button>

                </div>

                {{-- BODY --}}
                <div class="space-y-4 bg-slate-50 p-4 dark:bg-[#0b1220]">

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

                            {{-- COMPANY --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Company *
                                </label>

                                <select id="edit_cpny_id" name="cpny_id"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                                    <option value="">Select Company</option>

                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}">
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            {{-- DEPARTMENT --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Department *
                                </label>

                                <select id="edit_department_id" name="department_id"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                                    <option value="">Select Department</option>

                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}">
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            {{-- REQUESTER --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Requester
                                </label>

                                <input type="text" id="edit_user_peminta" readonly
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm dark:border-white/10 dark:bg-white/[0.04]">

                                <input type="hidden" id="edit_user_peminta_val" name="user_peminta">

                            </div>

                            {{-- PASSENGER --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Total Passenger *
                                </label>

                                <input type="number" id="edit_passenger" name="passenger" min="1"
                                    placeholder="Input total passenger"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                            </div>

                        </div>

                    </div>

                    {{-- SCHEDULE --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Schedule Information
                            </h3>

                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Booking Date *
                                </label>

                                <input type="date" id="edit_booking_date" name="booking_date"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Start Time *
                                </label>

                                <input type="time" id="edit_start_time" name="start_time"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    End Time *
                                </label>

                                <input type="time" id="edit_end_time" name="end_time"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm dark:border-white/10 dark:bg-[#0b1220]"
                                    required>

                            </div>

                        </div>

                    </div>

                    {{-- ROUTE INFORMATION --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-5 py-2 dark:border-white/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Route Information
                            </h3>

                            <button type="button" id="editAddRouteBtnEdit"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                                <i class="fa-solid fa-plus text-[10px]"></i>

                                Add Route

                            </button>

                        </div>

                        <div class="overflow-hidden">

                            <table class="min-w-full text-sm">

                                <thead class="bg-slate-50 dark:bg-white/[0.03]">

                                    <tr>

                                        <th class="w-16 px-4 py-3 text-left">No</th>

                                        <th class="px-4 py-3 text-left">Pickup</th>

                                        <th class="px-4 py-3 text-left">Destination</th>

                                        <th class="w-20 px-4 py-3"></th>

                                    </tr>

                                </thead>

                                <tbody id="editRouteTableBody"></tbody>

                            </table>

                        </div>

                    </div>

                    {{-- PURPOSE --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Purpose Information
                            </h3>

                        </div>

                        <div class="space-y-4 p-5">

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                {{-- COMPANY EXPENSE --}}
                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Company Expense *
                                    </label>

                                    <select id="edit_cpny_id_site" name="cpny_id_site"
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

                                {{-- USER REQUEST --}}
                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        User Request *
                                    </label>

                                    <select id="edit_user_request" name="user_request"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]"
                                        required>

                                        <option value="">Select passenger</option>

                                        @foreach ($requesters as $p)
                                            <option value="{{ $p->username }}"
                                                data-dept="{{ trim($p->department_id) }}">
                                                {{ $p->name }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                            </div>

                            {{-- PURPOSE --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Purpose *
                                </label>

                                <select id="edit_purpose_id" name="purpose_id"
                                    class="w-full rounded-lg border border-slate-200 dark:border-white/10" required>

                                    <option value="">Select purpose</option>

                                    @foreach ($purposes as $purpose)
                                        <option value="{{ $purpose->categoryid }}">
                                            {{ $purpose->category_name }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            {{-- PURPOSE DESCRIPTION --}}
                            <div>

                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Purpose Description *
                                </label>

                                <textarea id="edit_purpose_descr" name="purpose_descr" rows="4" placeholder="Explain the booking purpose..."
                                    class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-white"
                                    required></textarea>

                            </div>

                            {{-- REVISION REASON (hidden by default, shown when status = D) --}}
                            <div id="editBookingReviseWrapper"
                                class="hidden rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-500/20 dark:bg-yellow-500/10">

                                <div
                                    class="mb-2 text-xs font-semibold uppercase tracking-wider text-yellow-700 dark:text-yellow-300">
                                    Revision Reason
                                </div>

                                <div id="edit_booking_revise_reason"
                                    class="text-sm leading-relaxed text-yellow-900 dark:text-yellow-100">
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- FOOTER --}}
                <div
                    class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                    <div class="flex items-center justify-between">

                        <button type="button" id="cancelEditBookingBtn"
                            class="text-sm text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">

                            Cancel

                        </button>

                        <div class="flex items-center gap-3">

                            <button type="button" id="resetEditBookingBtn"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                                <i class="fa-solid fa-rotate-left text-xs"></i>

                                Reset

                            </button>

                            <button type="submit" form="editBookingForm"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                                <i class="fa-solid fa-paper-plane text-xs"></i>

                                Save Changes

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- PROCESS MODAL --}}
    <div id="gaProcessModal" class="fixed inset-0 z-[80] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-5xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <form id="gaProcessForm">

                @csrf

                <input type="hidden" id="ga_process_eid">
                <input type="hidden" id="ga_process_docid">

                {{-- HEADER --}}
                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/95 px-6 py-4 dark:border-white/10 dark:bg-[#0f172a]/95">

                    <div class="flex items-center gap-4">

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">

                            <i class="fa-solid fa-car text-lg"></i>

                        </div>

                        <div>

                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                Process Booking Car
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Assign driver, vehicle, and set travel handling result.
                            </p>

                        </div>

                    </div>

                    <button type="button" id="closeGaProcessModal"
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

                                <div id="ga_booking_docid" class="text-lg font-bold text-slate-900 dark:text-white">
                                    -
                                </div>

                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Booking approved and ready for processing.
                                </div>

                            </div>

                            <span
                                class="rounded-lg bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                Waiting Process
                            </span>

                        </div>

                        <div class="grid grid-cols-2 gap-x-8 gap-y-3 p-4 md:grid-cols-4">

                            <div>
                                <span class="text-xs text-slate-500">Requester</span>
                                <div id="ga_booking_requester"
                                    class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">-</div>
                            </div>

                            <div>
                                <span class="text-xs text-slate-500">Booking Date</span>
                                <div id="ga_booking_date"
                                    class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">-</div>
                            </div>

                            <div>
                                <span class="text-xs text-slate-500">Start Time</span>
                                <div id="ga_booking_start"
                                    class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">-</div>
                            </div>

                            <div>
                                <span class="text-xs text-slate-500">End Time</span>
                                <div id="ga_booking_end"
                                    class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">-</div>
                            </div>

                        </div>

                        <div class="border-t border-slate-200 px-4 py-3 dark:border-white/10">

                            <span class="text-xs text-slate-500">Route</span>

                            <div id="ga_booking_route"
                                class="mt-1 text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                -
                            </div>

                        </div>

                        <div class="border-t border-slate-200 px-4 py-3 dark:border-white/10">

                            <span class="text-xs text-slate-500">Purpose</span>

                            <div id="ga_booking_purpose" class="mt-1 text-sm text-slate-700 dark:text-slate-300">
                                -
                            </div>

                        </div>

                    </div>

                    {{-- TRAVEL RESULT --}}
                    <div
                        class="overflow-hidden rounded-lg border border-orange-200 bg-white dark:border-orange-500/20 dark:bg-[#0f172a]">

                        <div
                            class="border-b border-orange-100 bg-orange-50 px-5 py-3 dark:border-orange-500/20 dark:bg-orange-500/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-orange-700 dark:text-orange-300">
                                Travel Result
                            </h3>

                            <p class="mt-1 text-xs text-orange-600 dark:text-orange-400">
                                Select the final travel handling result for this booking.
                            </p>

                        </div>

                        <div class="px-5 py-4">

                            <label class="inline-flex cursor-pointer items-center gap-2.5">
                                <input type="checkbox" id="ga_status_toggle"
                                    class="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400 dark:border-white/20">
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Set Status Perjalanan
                                </span>
                            </label>

                            <div id="ga_status_wrapper" class="mt-3 hidden">
                                <select name="status_perjalanan" id="ga_status_perjalanan"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-orange-500 focus:outline-none dark:border-white/10 dark:bg-[#0b1220] dark:text-white">

                                    <option value="">Select status</option>

                                    @foreach ($statusPerjalanan as $status)
                                        <option value="{{ $status->category_name }}">
                                            {{ $status->category_name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
ve
                        </div>

                    </div>

                    {{-- DRIVER ASSIGNMENT (shown only when Handle by Taxi) --}}
                    <div id="driverAssignmentWrapper"
                        class="hidden overflow-hidden rounded-lg border border-emerald-200 bg-white dark:border-emerald-500/20 dark:bg-[#0f172a]">

                        <div
                            class="border-b border-emerald-100 bg-emerald-50 px-5 py-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                                Driver Assignment
                            </h3>

                            <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">
                                Assign driver and contact details for this booking.
                            </p>

                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                            <div>

                                <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Driver
                                    <span class="text-red-500">*</span>
                                </label>

                                <select name="driver" id="ga_driver"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-emerald-500 focus:outline-none dark:border-white/10 dark:bg-[#0b1220] dark:text-white">

                                    <option value="">Select driver</option>

                                    @foreach ($drivers as $d)
                                        <option value="{{ $d->drivername }}" data-hp="{{ $d->hp }}">
                                            {{ $d->drivername }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Handphone
                                </label>

                                <input type="text" name="handphone" id="ga_handphone" readonly
                                    placeholder="Auto-filled from driver"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">

                            </div>

                        </div>

                    </div>

                    {{-- VEHICLE ASSIGNMENT (shown only when Handle by Taxi) --}}
                    <div id="vehicleAssignmentWrapper"
                        class="hidden overflow-hidden rounded-lg border border-blue-200 bg-white dark:border-blue-500/20 dark:bg-[#0f172a]">

                        <div
                            class="border-b border-blue-100 bg-blue-50 px-5 py-3 dark:border-blue-500/20 dark:bg-blue-500/10">

                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">
                                Vehicle Information
                            </h3>

                            <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                                Select the operational vehicle assigned for this booking.
                            </p>

                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                            <div>

                                <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Vehicle
                                    <span class="text-red-500">*</span>
                                </label>

                                <select id="ga_vehicle"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm focus:border-blue-500 focus:outline-none dark:border-white/10 dark:bg-[#0b1220] dark:text-white">

                                    <option value="">Select vehicle</option>

                                    @foreach ($kendaraan as $k)
                                        <option value="{{ $k->no_polisi }}"
                                            data-name="{{ $k->namakendaraan ?? '-' }}">
                                            {{ $k->no_polisi }} - {{ $k->namakendaraan ?? '-' }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    No Polisi
                                </label>

                                <input type="text" name="no_polisi" id="ga_no_polisi" readonly
                                    placeholder="Auto-filled from vehicle"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">

                            </div>

                        </div>

                    </div>

                </div>

                {{-- FOOTER --}}
                <div
                    class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                    <div class="flex items-center justify-end gap-3">

                        <button type="button" id="cancelGaProcessBtn"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                            Cancel

                        </button>

                        <button type="button" id="saveGaProcessBtn"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-emerald-300 bg-white px-5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:bg-transparent dark:text-emerald-400 dark:hover:bg-emerald-500/10">

                            <i class="fa-solid fa-floppy-disk text-xs"></i>

                            Save

                        </button>

                        <button type="button" id="submitGaProcessBtn"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700">

                            <i class="fa-solid fa-lock text-xs"></i>

                            Submit & Lock

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.BookingCarCompanyList = @json($company->map(fn($c) => ['id' => $c->cpny_id, 'name' => $c->cpny_name]));
    </script>

    <script src="{{ asset('assets/js/bookingcar/core.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/helper.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/route.js') }}"></script>

    <script src="{{ asset('assets/js/bookingcar/modal.js') }}"></script>

    <script src="{{ asset('assets/js/bookingcar/request-form.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/edit-form.js') }}"></script>

    <script src="{{ asset('assets/js/bookingcar/datalist.js') }}"></script>

    <script src="{{ asset('assets/js/bookingcar/detail-modal.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/tracking.js') }}"></script>

    <script src="{{ asset('assets/js/bookingcar/approval.js') }}"></script>

    <script src="{{ asset('assets/js/bookingcar/process.js') }}"></script>

    <script src="{{ asset('assets/js/bookingcar/calendar.js') }}"></script>

    <script src="{{ asset('assets/js/bookingcar/auto-open.js') }}"></script>

    <script src="{{ asset('assets/js/bookingcar/init.js') }}"></script>
</x-app-layout>
