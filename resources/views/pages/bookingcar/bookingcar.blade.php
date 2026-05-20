<x-app-layout>

<div class="mb-4 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm">

     <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <!-- ================================================= -->
        <!-- LEFT -->
        <!-- ================================================= -->

        <div class="flex items-center gap-3">

            <!-- ICON -->
            <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-gray-700 to-gray-900 text-lg text-white shadow-sm">

                🚘

            </div>

            <!-- TITLE -->
            <div>

                <h1 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-gray-200">

                    Booking Car

                </h1>

                <p class="mt-0.5 text-sm text-gray-500">

                    Manage booking requests, schedules & approval workflows

                </p>

            </div>

        </div>

        <!-- ================================================= -->
        <!-- RIGHT -->
        <!-- ================================================= -->

        <div class="flex flex-wrap items-center gap-2">

            <!-- LIST TOGGLE -->
            <button type="button"
                id="toggleList"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10">

                <span class="text-base">
                    📋
                </span>

                <span>
                    Listing
                </span>

            </button>

            <!-- SETUP -->
            @if (auth()->check() && auth()->user()->hasRole('GAACCESS'))
                <a href="{{ route('bookingcar.setup.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-100 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20">

                    <span class="text-base">
                        ⚙️
                    </span>

                    <span>
                        Booking Car Setup
                    </span>

                </a>
            @endif

            <!-- CREATE -->
            <button type="button"
                id="openCreateBookingModal"
                class="inline-flex items-center gap-2 rounded-xl bg-black px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-black/10 transition hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-200">

                <span class="text-base">
                    ➕
                </span>

                <span>
                    New Booking
                </span>

            </button>

        </div>

    </div>

</div>

    <div id="mainGrid" class="grid grid-cols-1 gap-4 lg:grid-cols-12">

        <div id="calendarWrapper"
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a] lg:col-span-8">

            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-white/10">

                <div>

                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                        Calendar View
                    </div>

                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Booking Car request schedule overview
                    </div>

                </div>

                <div class="hidden items-center gap-2 md:flex">

                    <div class="flex items-center gap-2 text-xs text-gray-500">

                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        Pending

                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-500">

                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Completed

                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-500">

                        <span class="h-2 w-2 rounded-full bg-yellow-400"></span>
                        Revise

                    </div>

                </div>

            </div>

            <div class="p-5">
                <div id="calendar"></div>
            </div>

        </div>

        <div id="bookingListPanel"
            class="flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a] lg:col-span-4">

            <div class="border-b border-gray-100 p-4 dark:border-white/10">

                <div class="flex items-start justify-between gap-3">

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                            Booking List
                        </h3>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Latest requests
                        </p>
                    </div>

                    <span id="bookingCount"
                        class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-200">
                        0
                    </span>

                </div>

                <div class="mt-4 flex w-full flex-wrap justify-between gap-2">

                    <button class="booking-filter active-filter" data-filter="ALL">
                        All
                    </button>

                    <button type="button" class="booking-filter" data-filter="P">
                        Pending
                    </button>

                    <button type="button" class="booking-filter" data-filter="C">
                        Completed
                    </button>

                    @if (auth()->check() && auth()->user()->hasRole('GAACCESS'))
                        <button type="button" class="booking-filter" data-filter="WAITING_PROCESS">
                            Waiting Process
                        </button>
                    @endif


                    <button type="button" class="booking-filter" data-filter="D">
                        Revise
                    </button>

                    <button type="button" class="booking-filter" data-filter="R">
                        Rejected
                    </button>

                    <button type="button" class="booking-filter" data-filter="X">
                        Cancelled
                    </button>

                </div>

            </div>

            <div class="flex-1 overflow-y-auto p-2">
                <div id="bookingListBody" class="space-y-2"></div>
            </div>

            <div class="flex items-center justify-between border-t border-gray-100 px-4 py-3 dark:border-white/10">

                <div id="bookingPageInfo" class="text-xs text-gray-500 dark:text-gray-400">
                    Showing 1–5
                </div>

                <div class="flex items-center gap-2">

                    <button type="button" id="prevBookingPage"
                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                        Prev
                    </button>

                    <button type="button" id="nextBookingPage"
                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                        Next
                    </button>

                </div>

            </div>

        </div>

    </div>

    <div id="createBookingModal" class="fixed inset-0 z-50 hidden bg-black/40 p-4">

        <div class="flex min-h-full items-center justify-center">

            <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-[#0f172a]">

                <form id="bookingCarForm" method="POST" class="flex flex-col">

                    <div class="border-b border-gray-200 px-7 py-5 dark:border-white/10">

                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Create Booking Car
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Fill booking request information
                        </p>

                    </div>

                    <div class="max-h-[78vh] space-y-7 overflow-y-auto px-7 py-6">

                        <div class="space-y-4">

                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                                Basic Information
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">

                                <div>
                                    <label class="text-xs text-gray-500">
                                        Company *
                                    </label>

                                    <select name="cpny_id"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none dark:border-white/10 dark:bg-transparent"
                                        required>

                                        <option value="">
                                            Select company
                                        </option>

                                        @foreach ($usercpny as $p)
                                            <option value="{{ $p->cpny_id }}">
                                                {{ $p->cpny_id }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">
                                        Department *
                                    </label>

                                    <select name="department_id" id="booking_department_id"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none dark:border-white/10 dark:bg-transparent"
                                        required>

                                        <option value="">
                                            Select department
                                        </option>

                                        @foreach ($userdept as $p)
                                            <option value="{{ $p->department_id }}">
                                                {{ $p->department_id }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <label class="text-xs text-gray-500">
                                        Requester *
                                    </label>

                                    <input type="text" value="{{ auth()->user()->name }}"
                                        class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-100 px-3 py-2.5 text-sm text-gray-700"
                                        readonly>

                                    <input type="hidden" name="user_peminta" value="{{ auth()->user()->username }}">
                                </div>

                                <div>

                                    <label class="text-xs text-gray-500">
                                        User Request *
                                    </label>

                                    <select name="user_request" id="booking_user_request"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none dark:border-white/10 dark:bg-transparent"
                                        required>

                                        <option value="">
                                            Select passenger
                                        </option>

                                        @foreach ($requesters as $p)
                                            <option value="{{ $p->name }}"
                                                data-dept="{{ trim($p->department_id) }}">

                                                {{ $p->name }}

                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">
                                        Total Passenger *
                                    </label>

                                    <input type="number" name="passenger" min="1"
                                        placeholder="Total passenger"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none dark:border-white/10 dark:bg-transparent"
                                        required>
                                </div>
                            </div>

                        </div>

                        <div class="space-y-4">

                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                                Schedule
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">

                                <div>
                                    <label class="text-xs text-gray-500">
                                        Booking Date *
                                    </label>

                                    <input type="date" name="booking_date"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none dark:border-white/10 dark:bg-transparent"
                                        required>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">
                                        Start Time *
                                    </label>

                                    <input type="time" name="start_time"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none dark:border-white/10 dark:bg-transparent"
                                        required>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">
                                        End Time *
                                    </label>

                                    <input type="time" name="end_time"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none dark:border-white/10 dark:bg-transparent"
                                        required>
                                </div>

                            </div>

                        </div>

                        <div class="space-y-4">

                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                                Trip Information
                            </div>

                            <div class="space-y-4">

                                <div class="flex items-center justify-between">

                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                                        Route Information
                                    </div>

                                    <button type="button" id="createAddRouteBtn"
                                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">

                                        + Add Route

                                    </button>

                                </div>

                                <div class="overflow-hidden rounded-2xl border border-gray-200">

                                    <table class="min-w-full divide-y divide-gray-200 text-sm">

                                        <thead class="bg-gray-50">

                                            <tr>

                                                <th
                                                    class="w-16 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                                    No
                                                </th>

                                                <th
                                                    class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                                    Pickup Location
                                                </th>

                                                <th
                                                    class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                                    Destination
                                                </th>

                                                <th class="w-20 px-4 py-3">
                                                </th>

                                            </tr>

                                        </thead>

                                        <tbody id="createRouteTableBody">
                                        </tbody>

                                    </table>

                                </div>

                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">

                                <div>
                                    <label class="text-xs text-gray-500">
                                        Site Company *
                                    </label>

                                    <select name="cpny_id_site"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none dark:border-white/10 dark:bg-transparent"
                                        required>

                                        <option value="">
                                            Select company
                                        </option>

                                        @foreach ($company as $p)
                                            <option value="{{ $p->cpny_id }}">
                                                {{ $p->cpny_name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">
                                        Purpose ID *
                                    </label>

                                    <select name="purpose_id" id="purpose_id"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none dark:border-white/10 dark:bg-transparent"
                                        required>

                                        <option value="">
                                            Select purpose
                                        </option>

                                        <option value="MEETING">
                                            Meeting
                                        </option>

                                        <option value="VISIT">
                                            Site Visit
                                        </option>

                                        <option value="EVENT">
                                            Event
                                        </option>

                                        <option value="OTHER">
                                            Other
                                        </option>

                                    </select>
                                </div>

                            </div>

                            <div id="purposeDescrWrapper" class="hidden">

                                <label class="text-xs text-gray-500">
                                    Purpose Description *
                                </label>

                                <textarea name="purpose_descr" id="purpose_descr" rows="4" placeholder="Purpose detail..."
                                    class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-black focus:outline-none dark:border-white/10 dark:bg-transparent"></textarea>

                            </div>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-between border-t border-gray-200 px-7 py-5 dark:border-white/10">

                        <button type="button" id="closeCreateBookingModal"
                            class="text-sm text-gray-500 transition hover:text-black dark:hover:text-white">
                            Cancel
                        </button>

                        <button type="submit"
                            class="rounded-xl bg-black px-5 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800">
                            Submit Booking
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- VIEW BOOKING MODAL -->
    <div id="viewBookingModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm">

        <div class="flex min-h-screen items-center justify-center p-4 lg:p-6">

            <div
                class="relative w-full max-w-7xl overflow-hidden rounded-[28px] border border-gray-200/80 bg-[#f8fafc] shadow-[0_25px_80px_rgba(0,0,0,0.18)] dark:border-white/10 dark:bg-[#0f172a]">

                <!-- ===================================================== -->
                <!-- HEADER -->
                <!-- ===================================================== -->

                <div
                    class="flex flex-col gap-5 border-b border-gray-200 bg-white/80 px-8 py-6 backdrop-blur dark:border-white/10 dark:bg-white/5 lg:flex-row lg:items-start lg:justify-between">

                    <div class="flex items-start gap-4">

                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 text-2xl text-white shadow-lg shadow-slate-900/20">

                            🚘

                        </div>

                        <div>

                            <div
                                class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">

                                Booking Detail

                            </div>

                            <h2 class="mt-3 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">

                                Booking Car Information

                            </h2>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">

                                Booking information, route detail & approval workflow

                            </p>

                        </div>

                    </div>

                    <div class="flex items-center gap-3">

                        <a id="printBookingBtn" href="#" target="_blank"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">

                            <span>🖨</span>

                            <span>Print PDF</span>

                        </a>

                        <button type="button" onclick="closeBookingDetailModal()"
                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-100 hover:text-black dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                            ✕

                        </button>

                    </div>

                </div>

                <!-- ===================================================== -->
                <!-- BODY -->
                <!-- ===================================================== -->

                <div class="grid grid-cols-1 gap-5 p-5 lg:grid-cols-[1.1fr_.9fr]">

                    <!-- ================================================= -->
                    <!-- LEFT SIDE -->
                    <!-- ================================================= -->

                    <div class="space-y-5">

                        <!-- MAIN INFO -->
                        <div
                            class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                            <div
                                class="border-b border-gray-100 bg-gradient-to-r from-white to-slate-50 px-6 py-5 dark:border-white/10 dark:from-white/5 dark:to-white/[0.03]">

                                <div class="flex items-start justify-between gap-4">

                                    <div>

                                        <div
                                            class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">

                                            Requester

                                        </div>

                                        <div id="view_booking_user"
                                            class="mt-2 text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                                        </div>

                                        <div id="view_booking_docid"
                                            class="mt-1 text-xs font-medium text-gray-400 dark:text-gray-500">
                                        </div>

                                    </div>

                                    <div id="view_booking_status_badge">
                                    </div>

                                </div>

                            </div>

                            <div class="grid grid-cols-2 gap-x-8 gap-y-7 px-6 py-6">

                                <div>

                                    <div class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                        Booking Date
                                    </div>

                                    <div id="view_booking_date"
                                        class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-white">
                                    </div>

                                </div>

                                <div>

                                    <div class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                        Passenger
                                    </div>

                                    <div id="view_booking_passenger"
                                        class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-white">
                                    </div>

                                </div>

                                <div>

                                    <div class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                        Start Time
                                    </div>

                                    <div id="view_booking_start"
                                        class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-white">
                                    </div>

                                </div>

                                <div>

                                    <div class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                        End Time
                                    </div>

                                    <div id="view_booking_end"
                                        class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-white">
                                    </div>

                                </div>

                                <div>

                                    <div class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                        Company
                                    </div>

                                    <div id="view_booking_cpny"
                                        class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-white">
                                    </div>

                                </div>

                                <div>

                                    <div class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                        Department
                                    </div>

                                    <div id="view_booking_dept"
                                        class="mt-1.5 text-sm font-semibold text-gray-900 dark:text-white">
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- ROUTE -->
                        <div
                            class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                            <div class="flex items-center justify-between px-6 py-5">

                                <div>

                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">

                                        Route Information

                                    </div>

                                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">

                                        Pickup & destination detail

                                    </div>

                                </div>

                                <div id="view_total_route"
                                    class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">

                                    0 Route

                                </div>

                            </div>

                            <div class="px-6 pb-6">

                                <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">

                                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">

                                        <thead class="bg-gray-50 dark:bg-white/[0.03]">

                                            <tr>

                                                <th
                                                    class="w-16 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                    No
                                                </th>

                                                <th
                                                    class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                    Pickup
                                                </th>

                                                <th
                                                    class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                    Destination
                                                </th>

                                            </tr>

                                        </thead>

                                        <tbody id="view_booking_route_table"
                                            class="divide-y divide-gray-100 bg-white dark:divide-white/10 dark:bg-transparent">

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>

                        <!-- PURPOSE -->
                        <div
                            class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5">

                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">

                                Purpose

                            </div>

                            <div id="view_booking_purpose"
                                class="mt-4 text-sm leading-relaxed text-gray-700 dark:text-gray-200">
                            </div>

                        </div>

                        <!-- DRIVER -->
                        <div id="driverInfoWrapper"
                            class="hidden rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm dark:border-emerald-500/20 dark:from-emerald-500/10 dark:to-transparent">

                            <div class="flex items-center justify-between">

                                <div>

                                    <div
                                        class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">

                                        Driver Information

                                    </div>

                                    <div class="mt-1 text-sm text-emerald-600 dark:text-emerald-400">

                                        Vehicle assignment completed

                                    </div>

                                </div>

                                <div
                                    class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">

                                    Assigned

                                </div>

                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-3">

                                <div>

                                    <div class="text-xs text-emerald-700 dark:text-emerald-400">
                                        Driver
                                    </div>

                                    <div id="view_booking_driver"
                                        class="mt-1.5 text-sm font-bold text-emerald-900 dark:text-emerald-100">
                                    </div>

                                </div>

                                <div>

                                    <div class="text-xs text-emerald-700 dark:text-emerald-400">
                                        Handphone
                                    </div>

                                    <div id="view_booking_handphone"
                                        class="mt-1.5 text-sm font-bold text-emerald-900 dark:text-emerald-100">
                                    </div>

                                </div>

                                <div>

                                    <div class="text-xs text-emerald-700 dark:text-emerald-400">
                                        No Polisi
                                    </div>

                                    <div id="view_booking_nopol"
                                        class="mt-1.5 text-sm font-bold text-emerald-900 dark:text-emerald-100">
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- REVISE -->
                        <div id="bookingReviseWrapper"
                            class="hidden rounded-3xl border border-yellow-200 bg-yellow-50 p-6 shadow-sm dark:border-yellow-500/20 dark:bg-yellow-500/10">

                            <div
                                class="text-[11px] font-semibold uppercase tracking-[0.18em] text-yellow-700 dark:text-yellow-300">

                                Revision Reason

                            </div>

                            <div id="view_booking_revise_reason"
                                class="mt-4 text-sm leading-relaxed text-yellow-900 dark:text-yellow-100">
                            </div>

                        </div>

                    </div>

                    <!-- ================================================= -->
                    <!-- RIGHT SIDE -->
                    <!-- ================================================= -->

                    <div class="space-y-5">

                        <!-- ACTION HEADER -->
                        <div class="flex items-center justify-between gap-3">

                            <div>

                                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">

                                    Approval Workflow

                                </div>

                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">

                                    Approval status & process history

                                </div>

                            </div>

                            <div id="bookingApprovalActions" class="hidden flex-wrap items-center gap-2">

                                <button type="button" id="approveBookingBtn"
                                    class="rounded-xl bg-emerald-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-400">

                                    ✓ Approve

                                </button>

                                <button type="button" id="reviseBookingBtn"
                                    class="rounded-xl bg-amber-400 px-4 py-2 text-xs font-semibold text-black transition hover:bg-amber-300">

                                    ✎ Revise

                                </button>

                                <button type="button" id="rejectBookingBtn"
                                    class="rounded-xl bg-red-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-400">

                                    ✕ Reject

                                </button>

                            </div>

                        </div>

                        <!-- APPROVAL FLOW -->
                        <div
                            class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5">

                            <div id="bookingApprovalFlow" class="relative space-y-6">
                            </div>

                        </div>

                    </div>

                </div>

                <!-- ===================================================== -->
                <!-- FOOTER -->
                <!-- ===================================================== -->

                <div
                    class="flex flex-col gap-4 border-t border-gray-200 bg-gray-50 px-6 py-5 dark:border-white/10 dark:bg-white/[0.03] sm:flex-row sm:items-center sm:justify-between">

                    <button type="button" onclick="closeBookingDetailModal()"
                        class="text-sm font-medium text-gray-500 transition hover:text-black dark:text-gray-400 dark:hover:text-white">

                        Close

                    </button>

                    <div class="flex flex-wrap items-center gap-3">

                        <button type="button" id="cancelBookingBtn"
                            class="hidden rounded-xl border border-red-200 bg-red-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600 dark:border-red-500/20">

                            ✕ Cancel Request

                        </button>

                       <button type="button" id="editBookingBtn"
                            class="hidden rounded-xl bg-black px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-200">

                            ✏️ Edit Booking

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>
    <div id="editBookingModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50">

        <div class="flex min-h-screen items-center justify-center p-4">

            <div class="w-full max-w-5xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">

                <form id="editBookingForm">

                    @csrf

                    <input type="hidden" id="edit_booking_docid">
                    <input type="hidden" id="edit_booking_eid">

                    <!-- HEADER -->
                    <div class="flex items-start justify-between border-b border-gray-200 px-7 py-5">

                        <div class="flex items-center gap-4">

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-black text-lg text-white">
                                ✏️
                            </div>

                            <div>

                                <h2 class="text-lg font-semibold text-gray-900">
                                    Edit Booking Car
                                </h2>

                                <div id="editBookingStatus"
                                    class="mt-1 inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-medium text-gray-700">
                                    Draft
                                </div>

                            </div>

                        </div>

                        <button type="button" id="closeEditBookingModal"
                            class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-black">
                            ✕
                        </button>

                    </div>

                    <!-- BODY -->
                    <div class="max-h-[78vh] space-y-7 overflow-y-auto px-7 py-6">

                        <!-- BASIC -->
                        <div class="space-y-4">

                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                                Basic Information
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">

                                <div>
                                    <label class="text-xs text-gray-500">
                                        Company *
                                    </label>

                                    <select name="cpny_id" id="edit_cpny_id"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                        <option value="">
                                            Select company
                                        </option>

                                        @foreach ($usercpny as $p)
                                            <option value="{{ $p->cpny_id }}">
                                                {{ $p->cpny_id }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">
                                        Department *
                                    </label>

                                    <select name="department_id" id="edit_department_id"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                        <option value="">
                                            Select department
                                        </option>

                                        @foreach ($userdept as $p)
                                            <option value="{{ $p->department_id }}">
                                                {{ $p->department_id }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                            </div>

                            <div class="grid gap-4 md:grid-cols-3">

                                <div>

                                    <label class="text-xs text-gray-500">
                                        Requester
                                    </label>

                                    <input type="text" id="edit_user_peminta"
                                        class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-100 px-3 py-2.5 text-sm text-gray-700"
                                        readonly>

                                </div>
                                <div>

                                    <label class="text-xs text-gray-500">
                                        User Request
                                    </label>

                                    <select id="edit_user_request" name="user_request"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                        <option value="">
                                            Select passenger
                                        </option>

                                        @foreach ($requesters as $p)
                                            <option value="{{ $p->name }}"
                                                data-dept="{{ trim($p->department_id) }}">

                                                {{ $p->name }}

                                            </option>
                                        @endforeach

                                    </select>

                                </div>
                                <div>

                                    <label class="text-xs text-gray-500">
                                        Passenger
                                    </label>

                                    <input type="number" name="passenger" id="edit_passenger"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                </div>

                            </div>

                        </div>

                        <!-- SCHEDULE -->
                        <div class="space-y-4">

                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                                Schedule
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">

                                <div>

                                    <label class="text-xs text-gray-500">
                                        Booking Date
                                    </label>

                                    <input type="date" name="booking_date" id="edit_booking_date"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                </div>

                                <div>

                                    <label class="text-xs text-gray-500">
                                        Start Time
                                    </label>

                                    <input type="time" name="start_time" id="edit_start_time"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                </div>

                                <div>

                                    <label class="text-xs text-gray-500">
                                        End Time
                                    </label>

                                    <input type="time" name="end_time" id="edit_end_time"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                </div>

                            </div>

                        </div>

                        <!-- ROUTE -->
                        <div class="space-y-4">

                            <div class="flex items-center justify-between">

                                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                                    Route Information
                                </div>

                                <button type="button" id="editAddRouteBtnEdit"
                                    class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">

                                    + Add Route

                                </button>

                            </div>

                            <div class="overflow-hidden rounded-2xl border border-gray-200">

                                <table class="min-w-full divide-y divide-gray-200 text-sm">

                                    <thead class="bg-gray-50">

                                        <tr>

                                            <th
                                                class="w-16 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                                No
                                            </th>

                                            <th
                                                class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                                Pickup Location
                                            </th>

                                            <th
                                                class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                                Destination
                                            </th>

                                            <th class="w-20 px-4 py-3"></th>

                                        </tr>

                                    </thead>

                                    <tbody id="editRouteTableBody">
                                    </tbody>

                                </table>

                            </div>

                        </div>

                        <!-- PURPOSE -->
                        <div class="space-y-4">

                            <div class="grid gap-4 md:grid-cols-2">

                                <div>

                                    <label class="text-xs text-gray-500">
                                        Site Company
                                    </label>

                                    <select name="cpny_id_site" id="edit_cpny_id_site"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                        <option value="">
                                            Select company
                                        </option>

                                        @foreach ($company as $p)
                                            <option value="{{ $p->cpny_id }}">
                                                {{ $p->cpny_name }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                <div>

                                    <label class="text-xs text-gray-500">
                                        Purpose
                                    </label>

                                    <select name="purpose_id" id="edit_purpose_id"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                        <option value="MEETING">Meeting</option>
                                        <option value="VISIT">Site Visit</option>
                                        <option value="EVENT">Event</option>
                                        <option value="OTHER">Other</option>

                                    </select>

                                </div>

                            </div>

                            <div id="editPurposeDescrWrapper" class="hidden">

                                <label class="text-xs text-gray-500">
                                    Purpose Description
                                </label>

                                <textarea name="purpose_descr" id="edit_purpose_descr" rows="4"
                                    class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-black focus:outline-none"></textarea>

                            </div>

                        </div>

                        <!-- REVISE -->
                        <div id="editBookingReviseWrapper"
                            class="hidden rounded-2xl border border-yellow-200 bg-yellow-50 p-5 shadow-sm">

                            <div class="text-[11px] uppercase tracking-[0.18em] text-yellow-700">
                                Revision Reason
                            </div>

                            <div id="edit_booking_revise_reason" class="mt-3 text-sm leading-relaxed text-yellow-900">
                            </div>

                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-7 py-5">

                        <button type="button" id="cancelEditBookingBtn"
                            class="text-sm text-gray-500 transition hover:text-black">
                            Cancel
                        </button>

                        <button type="submit" id="saveEditBookingBtn"
                            class="rounded-xl bg-black px-5 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800">
                            Save Changes
                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>

    <!-- REASON MODAL -->
    <div id="reasonModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4">

        <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">

            <!-- HEADER -->
            <div class="border-b border-gray-200 px-6 py-5">

                <h2 id="reasonModalTitle" class="text-lg font-semibold tracking-tight text-gray-900">
                    Revision Reason
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Please provide a comment before continuing
                </p>

            </div>

            <!-- BODY -->
            <div class="p-6">

                <textarea id="reasonInput" rows="5" placeholder="Type your comment here..."
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-black focus:outline-none"></textarea>

                <div id="reasonError" class="mt-2 hidden text-xs text-red-500">
                    Comment is required
                </div>

            </div>

            <!-- FOOTER -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">

                <button id="cancelReasonBtn"
                    class="rounded-xl border border-gray-300 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-100">
                    Cancel
                </button>

                <button id="submitReasonBtn"
                    class="rounded-xl bg-black px-5 py-2 text-sm font-medium text-white transition hover:bg-gray-800">
                    Submit
                </button>

            </div>

        </div>

    </div>

    <div id="gaProcessModal" class="fixed inset-0 z-[80] hidden overflow-y-auto bg-black/50">

        <div class="flex min-h-screen items-center justify-center p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">

                <!-- HEADER -->
                <div class="flex items-start justify-between border-b border-gray-200 px-7 py-5">

                    <div class="flex items-center gap-4">

                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500 text-lg text-white shadow-sm">

                            🚘

                        </div>

                        <div>

                            <h2 class="text-lg font-semibold tracking-tight text-gray-900">
                                GA Process Booking Car
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Assign driver & vehicle information
                            </p>

                        </div>

                    </div>

                    <button type="button" id="closeGaProcessModal"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-black">

                        ✕

                    </button>

                </div>

                <!-- FORM -->
                <form id="gaProcessForm">

                    @csrf

                    <input type="hidden" id="ga_process_docid">

                    <!-- BODY -->
                    <div class="space-y-6 px-7 py-6">

                        <!-- BOOKING INFO -->
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-5 py-4">

                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">

                                Booking Information

                            </div>

                            <div class="mt-4 grid gap-4 md:grid-cols-2">

                                <div>

                                    <div class="text-xs text-gray-400">
                                        Document No
                                    </div>

                                    <div id="ga_booking_docid" class="mt-1 text-sm font-semibold text-gray-900">
                                        -
                                    </div>

                                </div>

                                <div>

                                    <div class="text-xs text-gray-400">
                                        Requester
                                    </div>

                                    <div id="ga_booking_requester" class="mt-1 text-sm font-semibold text-gray-900">
                                        -
                                    </div>

                                </div>

                                <div>

                                    <div class="text-xs text-gray-400">
                                        Route
                                    </div>

                                    <div id="ga_booking_route" class="mt-1 text-sm font-semibold text-gray-900">
                                        -
                                    </div>

                                </div>

                                <div>

                                    <div class="text-xs text-gray-400">
                                        Booking Date
                                    </div>

                                    <div id="ga_booking_date" class="mt-1 text-sm font-semibold text-gray-900">
                                        -
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- DRIVER -->
                        <div class="space-y-4">

                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">

                                Driver Assignment

                            </div>

                            <div class="grid gap-4 md:grid-cols-2">

                                <!-- DRIVER -->
                                <div>

                                    <label class="text-xs text-gray-500">
                                        Driver *
                                    </label>

                                    <select name="driver" id="ga_driver"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none"
                                        required>

                                        <option value="">
                                            Select driver
                                        </option>

                                        @foreach ($drivers as $d)
                                            <option value="{{ $d->drivername }}" data-hp="{{ $d->hp }}">

                                                {{ $d->drivername }}

                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                <!-- HP -->
                                <div>

                                    <label class="text-xs text-gray-500">
                                        Handphone
                                    </label>

                                    <input type="text" name="handphone" id="ga_handphone"
                                        placeholder="Driver phone number"
                                        class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                </div>

                            </div>

                        </div>

                        <!-- VEHICLE -->
                        <div class="space-y-4">

                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">

                                Vehicle Information

                            </div>

                            <div class="grid gap-4 md:grid-cols-2">

                                <!-- KENDARAAN -->
                                <div>

                                    <label class="text-xs text-gray-500">
                                        Vehicle *
                                    </label>

                                    <select id="ga_vehicle"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                        <option value="">
                                            Select vehicle
                                        </option>

                                        {{-- @foreach ($kendaraan as $k)
                                            <option value="{{ $k->no_polisi }}"
                                                data-name="{{ $k->namakendaraan ?? '-' }}">

                                                {{ $k->no_polisi }}
                                                -
                                                {{ $k->namakendaraan ?? '-' }}

                                            </option>
                                        @endforeach --}}

                                        @foreach ($kendaraan as $k)
                                            <option value="{{ $k->nopol_kendaraan }}"
                                                data-name="{{ $k->kendaraan_descr ?? '-' }}">

                                                {{ $k->nopol_kendaraan }}
                                                -
                                                {{ $k->kendaraan_descr ?? '-' }}

                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                <!-- NOPOL -->
                                <div>

                                    <label class="text-xs text-gray-500">
                                        No Polisi
                                    </label>

                                    <input type="text" name="no_polisi" id="ga_no_polisi"
                                        placeholder="Vehicle plate number"
                                        class="mt-1 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-black focus:outline-none">

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-7 py-5">

                        <button type="button" id="cancelGaProcessBtn"
                            class="text-sm text-gray-500 transition hover:text-black">

                            Cancel

                        </button>

                        <button type="submit" id="submitGaProcessBtn"
                            class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-600">

                            Save Driver Assignment

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    @push('scripts')

    <script src="{{ asset('assets/js/bookingcar/helper.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/modal.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/route.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/request-form.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/detail-modal.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/approval.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/process.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/datalist.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/calendar.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/auto-open.js') }}"></script>
    <script src="{{ asset('assets/js/bookingcar/init.js') }}"></script>

    @endpush
  </x-app-layout>
