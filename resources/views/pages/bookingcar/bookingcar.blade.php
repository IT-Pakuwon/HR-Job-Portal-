<x-app-layout>

    <div class="mb-4 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

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

            <div class="flex flex-wrap items-center gap-2">

                <!-- LIST TOGGLE -->
                <button type="button" id="toggleList"
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
                <button type="button" id="openCreateBookingModal"
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
                                                {{ $p->cpny_id }}
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

                                        @foreach ($purposes as $purpose)
                                            <option value="{{ $purpose->category_id }}">
                                               {{ $purpose->category_name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                            </div>

                            <div id="purposeDescrWrapper">

                                <label class="text-xs text-gray-500">
                                    Purpose Description *
                                </label>

                                <textarea name="purpose_descr" id="purpose_descr"required rows="4" placeholder="Purpose detail..."
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
                                                {{ $p->cpny_id }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                <div>

                                    <label class="text-xs text-gray-500">
                                        Purpose
                                    </label>

                                    <select name="purpose_id" id="edit_purpose_id"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-black focus:outline-none"
                                        required>

                                        <option value="">
                                            Select purpose
                                        </option>

                                        @foreach ($purposes as $purpose)
                                            <option value="{{ $purpose->category_id }}">
                                                {{ $purpose->category_name }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                            </div>

                            <div id="editPurposeDescrWrapper">

                                <label class="text-xs text-gray-500">
                                    Purpose Description
                                </label>

                                <textarea name="purpose_descr" id="edit_purpose_descr" required rows="4"
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

    <script>
        let bookingCalendar;
        let bookingRows = [];
        let bookingCurrentPage = 1;
        const bookingPerPage = 5;
        let bookingCurrentFilter = 'ALL';
        let currentReasonAction = null;
        let currentBookingDocid = null;

        const bookingCalendarEl = document.getElementById('calendar');
        const bookingListBody = document.getElementById('bookingListBody');
        const bookingCount = document.getElementById('bookingCount');
        const bookingPageInfo = document.getElementById('bookingPageInfo');

        const prevBookingPage = document.getElementById('prevBookingPage');
        const nextBookingPage = document.getElementById('nextBookingPage');

        const bookingFilters = document.querySelectorAll('.booking-filter');

        const createBookingModal = document.getElementById('createBookingModal');
        const openCreateBookingModal = document.getElementById('openCreateBookingModal');
        const closeCreateBookingModal = document.getElementById('closeCreateBookingModal');

        const bookingCarForm = document.getElementById('bookingCarForm');

        const purposeSelect = document.getElementById('purpose_id');
        const purposeDescrWrapper = document.getElementById('purposeDescrWrapper');
        const purposeDescr = document.getElementById('purpose_descr');

        const bookingDeptSelect = document.getElementById('booking_department_id');
        const bookingUserRequestSelect = document.getElementById('booking_user_request');

        const bookingUserRequestOptions = Array.from(
            bookingUserRequestSelect.options
        );

        const viewBookingModal = document.getElementById('viewBookingModal');

        const editBookingModal = document.getElementById('editBookingModal');
        const editBookingForm = document.getElementById('editBookingForm');

        const editDeptSelect =
            document.getElementById('edit_department_id');

        const editUserRequestSelect =
            document.getElementById('edit_user_request');

        const editUserRequestOptions = Array.from(
            editUserRequestSelect.options
        );

        const editPurposeSelect =
            document.getElementById('edit_purpose_id');

        const editPurposeDescr =
            document.getElementById('edit_purpose_descr');

        const editPurposeDescrWrapper =
            document.getElementById(
                'editPurposeDescrWrapper'
            );

        const toggleListBtn = document.getElementById('toggleList');
        const bookingListPanel = document.getElementById('bookingListPanel');
        const calendarWrapper = document.getElementById('calendarWrapper');

        let listHidden = false;

        toggleListBtn?.addEventListener('click', function() {

            listHidden = !listHidden;

            if (listHidden) {

                bookingListPanel.classList.add('hidden');

                calendarWrapper.classList.remove('lg:col-span-8');
                calendarWrapper.classList.add('lg:col-span-12');

                this.innerHTML = `
                    <span>📋</span>
                    <span>Show Listing</span>
                `;

            } else {

                bookingListPanel.classList.remove('hidden');

                calendarWrapper.classList.remove('lg:col-span-12');
                calendarWrapper.classList.add('lg:col-span-8');

                this.innerHTML = `
                    <span>📋</span>
                    <span>Listing</span>
                `;
            }

            setTimeout(() => {
                bookingCalendar?.updateSize();
            }, 200);
        });

        function createRouteRow(index, from = '', destination = '') {

            return `
                <tr>

                    <td class="px-4 py-3 text-sm font-medium text-gray-500">
                        ${index}
                    </td>

                    <td class="px-4 py-3">

                        <input type="text"
                            name="location_from[]"
                            value="${escapeHtml(from)}"
                            placeholder="Pickup location"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                            required>

                    </td>

                    <td class="px-4 py-3">

                        <input type="text"
                            name="destination[]"
                            value="${escapeHtml(destination)}"
                            placeholder="Destination"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                            required>

                    </td>

                    <td class="px-4 py-3 text-right">

                        <button type="button"
                            onclick="removeRouteRow(this)"
                            class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100">

                            Remove

                        </button>

                    </td>

                </tr>
            `;
        }

        function refreshEditRouteNumber() {

            document.querySelectorAll('#editRouteTableBody tr')
                .forEach((tr, index) => {

                    tr.querySelector('td').innerText = index + 1;
                });
        }

        function removeRouteRow(btn) {

            const tbody =
                document.getElementById('createRouteTableBody');

            if (tbody.querySelectorAll('tr').length <= 1) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Minimum 1 Route',
                    text: 'At least one route is required'
                });

                return;
            }

            btn.closest('tr').remove();

            refreshRouteNumber();
        }

        function refreshRouteNumber() {

            document.querySelectorAll('#createRouteTableBody tr').forEach((tr, index) => {
                tr.querySelector('td').innerText = index + 1;
            });

            // document.querySelectorAll('#editRouteTableBody tr').forEach((tr, index) => {
            //     tr.querySelector('td').innerText = index + 1;
            // });
        }


        document.getElementById('createAddRouteBtn')?.addEventListener('click', function() {

            const tbody = document.getElementById('createRouteTableBody');

            const index = tbody.querySelectorAll('tr').length + 1;

            tbody.insertAdjacentHTML(
                'beforeend',
                createRouteRow(index)
            );
        });

        function openBookingModal() {
            createBookingModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            const tbody = document.getElementById('createRouteTableBody');

            if (!tbody.querySelector('tr')) {

                tbody.innerHTML = createRouteRow(1);
            }
        }

        function closeBookingModal() {
            createBookingModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            bookingCarForm.reset();
            purposeDescrWrapper.classList.add('hidden');
            purposeDescr.required = false;
            document.getElementById('createRouteTableBody').innerHTML = '';
        }

        function closeBookingDetailModal() {

            viewBookingModal.classList.add('hidden');

            document.body.classList.remove('overflow-hidden');

            window.history.pushState({},
                '',
                '/bookingcar'
            );
        }

        function closeEditBookingModal() {
            editBookingModal.classList.add('hidden');

            viewBookingModal.classList.remove('hidden');

            document.body.classList.add('overflow-hidden');

            editBookingForm.reset();
        }

        openCreateBookingModal?.addEventListener('click', openBookingModal);
        closeCreateBookingModal?.addEventListener('click', closeBookingModal);

        // createBookingModal?.addEventListener('click', function(e) {
        //     if (e.target === createBookingModal) {
        //         closeBookingModal();
        //     }
        // });

        document.getElementById('closeEditBookingModal')?.addEventListener(
            'click',
            closeEditBookingModal
        );

        document.getElementById('cancelEditBookingBtn')?.addEventListener(
            'click',
            closeEditBookingModal
        );

        function filterUserRequest() {

            const selectedDept = bookingDeptSelect.value;

            bookingUserRequestSelect.innerHTML = `
                <option value="">Select passenger</option>
            `;

            bookingUserRequestOptions.forEach(option => {

                if (!option.value) return;

                const dept = option.getAttribute('data-dept');

                if (!selectedDept || dept === selectedDept) {
                    bookingUserRequestSelect.appendChild(
                        option.cloneNode(true)
                    );
                }
            });
        }

        bookingDeptSelect?.addEventListener(
            'change',
            filterUserRequest
        );

        purposeSelect?.addEventListener(
            'change',
            function() {

                if (this.value === 'OTHER') {

                    purposeDescrWrapper.classList.remove('hidden');
                    purposeDescr.required = true;
                    purposeDescr.value = '';

                } else {

                    purposeDescrWrapper.classList.add('hidden');
                    purposeDescr.required = false;
                    purposeDescr.value = this.value;
                }
            }
        );

        function bookingStatusBadge(status) {

            const map = {
                P: 'bg-blue-100 text-blue-700',
                C: 'bg-emerald-100 text-emerald-700',
                D: 'bg-yellow-100 text-yellow-700',
                R: 'bg-red-100 text-red-700',
                X: 'bg-gray-200 text-gray-700'
            };

            const label = {
                P: 'Pending',
                C: 'Completed',
                D: 'Revise',
                R: 'Rejected',
                X: 'Cancelled'
            };

            return `
                <span class="rounded-full px-2 py-1 text-[10px] font-semibold ${map[status] || 'bg-gray-100 text-gray-700'}">
                    ${label[status] || '-'}
                </span>
            `;
        }

        function bookingStatusHtml(status) {

            const map = {
                P: 'bg-blue-100 text-blue-700',
                C: 'bg-emerald-100 text-emerald-700',
                D: 'bg-yellow-100 text-yellow-700',
                R: 'bg-red-100 text-red-700',
                X: 'bg-gray-200 text-gray-700'
            };

            const label = {
                P: 'Pending',
                C: 'Completed',
                D: 'Revise',
                R: 'Rejected',
                X: 'Cancelled'
            };

            return `
                <div class="rounded-full px-3 py-1 text-xs font-medium ${map[status] || 'bg-gray-100 text-gray-700'}">
                    ${label[status]}
                </div>
            `;
        }

        async function fetchBookingList() {

            try {

                let url = `/bookingcar/json?length=999`;

                if (
                    bookingCurrentFilter !== 'ALL' &&
                    bookingCurrentFilter !== 'WAITING_PROCESS'
                ) {
                    url += `&status=${bookingCurrentFilter}`;
                }
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();

                let rows = result.data || [];

                // =====================================================
                // WAITING PROCESS FILTER
                // =====================================================

                if (bookingCurrentFilter === 'WAITING_PROCESS') {

                    rows = rows.filter(row =>
                        row.status === 'C' &&
                        (
                            !row.driver ||
                            !row.no_polisi
                        )
                    );
                }

                bookingRows = rows;

                renderBookingList();
                renderBookingCalendar();

            } catch (err) {

                console.error(err);

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed load booking data'
                });
            }
        }

        function renderBookingList() {

            bookingCount.innerText = bookingRows.length;

            const start = (bookingCurrentPage - 1) * bookingPerPage;
            const end = start + bookingPerPage;

            const rows = bookingRows.slice(start, end);

            bookingListBody.innerHTML = '';

            if (!rows.length) {

                bookingListBody.innerHTML = `
                    <div class="flex h-32 items-center justify-center text-sm text-gray-400">
                        No data
                    </div>
                `;

                bookingPageInfo.innerText = 'Showing 0';
                return;
            }


            rows.forEach(row => {

                const routeHtml = row.routes?.length ?
                    row.routes.map(route => `
                        <div class="truncate">
                            ${escapeHtml(route.origin || '-')}
                            <span class="mx-1 opacity-50">→</span>
                            ${escapeHtml(route.destination || '-')}
                        </div>
                    `).join('') :
                    `<div>-</div>`;

                bookingListBody.innerHTML += `
                    <div onclick="showBookingDetail('${row.eid}')"
                        class="cursor-pointer rounded-2xl border border-gray-100 p-4 transition hover:border-gray-300 hover:bg-gray-50">

                        <div class="flex items-start justify-between gap-3">

                            <div class="min-w-0 flex-1">

                                <div class="truncate text-sm font-semibold text-gray-900">
                                    ${row.docid}
                                </div>

                                <div class="mt-1 space-y-1 text-sm text-gray-500">
                                    ${routeHtml}
                                </div>

                                <div class="mt-3 flex items-center gap-2 text-xs text-gray-400">

                                    <span>${row.booking_date || '-'}</span>

                                    <span>•</span>

                                    <span>
                                        ${row.start_time ? row.start_time.substring(11, 16) : '-'}
                                    </span>

                                </div>

                            </div>

                            <div class="flex flex-col items-end gap-2">

                                ${bookingStatusBadge(row.status)}

                            </div>

                        </div>

                    </div>
                `;
            });

            bookingPageInfo.innerText =
                `Showing ${start + 1}-${Math.min(end, bookingRows.length)} of ${bookingRows.length}`;
        }

        prevBookingPage?.addEventListener(
            'click',
            function() {

                if (bookingCurrentPage > 1) {
                    bookingCurrentPage--;
                    renderBookingList();
                }
            }
        );

        nextBookingPage?.addEventListener(
            'click',
            function() {

                const maxPage = Math.ceil(
                    bookingRows.length / bookingPerPage
                );

                if (bookingCurrentPage < maxPage) {
                    bookingCurrentPage++;
                    renderBookingList();
                }
            }
        );

        bookingFilters.forEach(btn => {

            btn.addEventListener(
                'click',
                function() {

                    bookingFilters.forEach(x => {
                        x.classList.remove('active-filter');
                    });

                    this.classList.add('active-filter');

                    bookingCurrentFilter = this.dataset.filter;
                    bookingCurrentPage = 1;

                    fetchBookingList();
                }
            );
        });

        function renderBookingCalendar() {

            if (bookingCalendar) {
                bookingCalendar.destroy();
            }

            bookingCalendar = new FullCalendar.Calendar(
                bookingCalendarEl, {
                    initialView: 'timeGridWeek',
                    height: 720,
                    selectable: true,
                    allDaySlot: false,
                    nowIndicator: true,
                    slotMinTime: '06:00:00',
                    slotMaxTime: '22:00:00',

                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'timeGridWeek,dayGridMonth'
                    },

                    eventContent(arg) {

                        const props = arg.event.extendedProps;

                        return {
                            html: `
                                <div class="px-1 py-0.5 leading-tight">

                                    <div class="text-[10px] font-semibold opacity-90">
                                        ${arg.timeText}
                                    </div>

                                    <div class="mt-1 text-[11px] font-bold">
                                        ${arg.event.title || '-'}
                                    </div>

                                    ${
                                        props.routes?.length
                                            ? `
                                                    <div class="mt-1 space-y-0.5 text-[10px] opacity-90">
                                                        ${props.routes.map(route => `
                                                        <div>
                                                            📌 ${escapeHtml(route.origin || '-')}
                                                            →
                                                            ${escapeHtml(route.destination || '-')}
                                                        </div>
                                                    `).join('')}
                                                    </div>
                                                `
                                            : ''
                                    }

                                    ${
                                        props.purpose
                                            ? `
                                                    <div class="mt-1 text-[10px] opacity-90">
                                                        📋 ${escapeHtml(props.purpose)}
                                                    </div>
                                                `
                                            : ''
                                    }

                                </div>
                            `
                        };
                    },

                    select(info) {

                        openBookingModal();

                        document.querySelector('[name="booking_date"]').value =
                            info.startStr.split('T')[0];

                        document.querySelector('[name="start_time"]').value =
                            info.startStr.substring(11, 16);

                        document.querySelector('[name="end_time"]').value =
                            info.endStr.substring(11, 16);
                    },

                    events: bookingRows
                        .filter(row => row.status !== 'X')
                        .map(row => {

                            let color = '#3b82f6';

                            if (row.status === 'C') color = '#10b981';
                            if (row.status === 'D') color = '#f59e0b';
                            if (row.status === 'R') color = '#ef4444';

                            return {
                                title: escapeHtml(
                                    row.user_request ||
                                    row.user_peminta ||
                                    '-'
                                ),

                                start: row.start_time,
                                end: row.end_time,

                                backgroundColor: color,
                                borderColor: color,
                                textColor: '#ffffff',

                                extendedProps: {
                                    eid: row.eid,

                                    status: row.status,

                                    purpose: [
                                        row.purpose_id || '-',
                                        row.purpose_descr || '-'
                                    ].join(' - '),

                                    routes: row.routes || []
                                }
                            };
                        }),

                    eventClick(info) {
                        showBookingDetail(
                            info.event.extendedProps.eid
                        );
                    }
                }
            );

            bookingCalendar.render();
        }

        async function showBookingDetail(eid) {

            window.history.pushState({},
                '',
                `/showbookingcar/${eid}`
            );


            try {

                const response = await fetch(
                    `/bookingcar/detail/${eid}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    }
                );
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message);
                }

                const d = result.data;

                currentBookingDocid = d.docid;

                viewBookingModal.classList.remove('hidden');

                document.body.classList.add('overflow-hidden');

                // =========================================================
                // BASIC INFO
                // =========================================================

                document.getElementById('view_booking_user').innerText =
                    d.user_request || d.user_peminta || '-';

                document.getElementById('view_booking_docid').innerText =
                    d.docid || '-';

                document.getElementById('view_booking_status_badge').innerHTML =
                    bookingStatusHtml(d.status);

                document.getElementById('view_booking_date').innerText =
                    d.booking_date || '-';

                document.getElementById('view_booking_passenger').innerText =
                    d.passenger || '-';

                document.getElementById('view_booking_start').innerText =
                    d.start_time ?
                    d.start_time.substring(11, 16) :
                    '-';

                document.getElementById('view_booking_end').innerText =
                    d.end_time ?
                    d.end_time.substring(11, 16) :
                    '-';

                document.getElementById('view_booking_cpny').innerText =
                    d.cpny_id || '-';

                document.getElementById('view_booking_dept').innerText =
                    d.department_id || '-';

                // =========================================================
                // ROUTE
                // =========================================================

                const routeTable =
                    document.getElementById('view_booking_route_table');

                routeTable.innerHTML = '';

                if (d.routes && d.routes.length) {

                    document.getElementById('view_total_route').innerText =
                        `${d.routes.length} Route`;

                    d.routes.forEach((route, index) => {

                        routeTable.innerHTML += `
                            <tr>

                                <td class="px-4 py-3 text-sm text-gray-500">
                                    ${index + 1}
                                </td>

                                <td class="px-4 py-3 text-sm font-medium text-gray-700">
                                     ${escapeHtml(route.origin || '-')}
                                </td>

                                <td class="px-4 py-3 text-sm font-medium text-gray-700">
                                    ${escapeHtml(route.destination || '-')}
                                </td>

                            </tr>
                        `;
                    });

                } else {

                    routeTable.innerHTML = `
                        <tr>
                            <td colspan="3"
                                class="px-4 py-6 text-center text-sm text-gray-400">
                                No route data
                            </td>
                        </tr>
                    `;

                    document.getElementById('view_total_route').innerText =
                        '0 Route';
                }
                // =========================================================
                // PURPOSE
                // =========================================================

                document.getElementById('view_booking_purpose').innerText =
                    d.purpose_descr || '-';

                // =========================================================
                // DRIVER
                // =========================================================

                const driverWrapper =
                    document.getElementById('driverInfoWrapper');

                if (
                    d.driver ||
                    d.handphone ||
                    d.no_polisi
                ) {

                    driverWrapper.classList.remove('hidden');

                    document.getElementById('view_booking_driver').innerText =
                        d.driver || '-';

                    document.getElementById('view_booking_handphone').innerText =
                        d.handphone || '-';

                    document.getElementById('view_booking_nopol').innerText =
                        d.no_polisi || '-';

                } else {

                    driverWrapper.classList.add('hidden');
                }

                // =========================================================
                // REVISE HISTORY RESET
                // =========================================================

                document.getElementById('bookingReviseWrapper')
                    .classList.add('hidden');

                document.getElementById('view_booking_revise_reason')
                    .innerHTML = '';

                // =========================================================
                // PRINT
                // =========================================================

                document.getElementById('printBookingBtn').href =
                    `/bookingcar/print/${d.eid}`;

                // =========================================================
                // BUTTONS
                // =========================================================

                const editBtn =
                    document.getElementById('editBookingBtn');

                const cancelBtn =
                    document.getElementById('cancelBookingBtn');

                const approvalActions =
                    document.getElementById('bookingApprovalActions');

                editBtn.classList.add('hidden');
                cancelBtn.classList.add('hidden');
                approvalActions.classList.add('hidden');

                // reset old onclick
                editBtn.onclick = null;
                cancelBtn.onclick = null;

                document.getElementById('approveBookingBtn').onclick = null;

                document.getElementById('reviseBookingBtn').onclick = null;

                document.getElementById('rejectBookingBtn').onclick = null;

                @if (auth()->check())

                    const currentUser =
                        '{{ strtolower(auth()->user()->username) }}';

                    const isOwner =
                        String(d.created_by || '')
                        .toLowerCase() === currentUser;

                    if (
                        d.status === 'D' &&
                        isOwner
                    ) {

                        editBtn.classList.remove('hidden');

                        editBtn.onclick = async function() {

                            const eid = d.eid;

                            const response = await fetch(
                                `/bookingcar/detail/${eid}`, {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    }
                                }
                            );

                            const result = await response.json();

                            if (!result.success) return;

                            const dEdit = result.data;

                            viewBookingModal.classList.add('hidden');

                            editBookingModal.classList.remove('hidden');

                            document.body.classList.add('overflow-hidden');

                            document.getElementById('edit_booking_docid').value =
                                dEdit.docid;

                            document.getElementById('edit_booking_eid').value =
                                dEdit.eid;

                            document.getElementById('edit_cpny_id').value =
                                dEdit.cpny_id || '';

                            document.getElementById('edit_department_id').value =
                                dEdit.department_id || '';

                            document.getElementById('edit_user_peminta').value =
                                dEdit.user_peminta || '';

                            filterEditUserRequest();

                            document.getElementById('edit_user_request').value =
                                dEdit.user_request || '';

                            document.getElementById('edit_passenger').value =
                                dEdit.passenger || '';

                            document.getElementById('edit_booking_date').value =
                                dEdit.booking_date || '';

                            document.getElementById('edit_start_time').value =
                                dEdit.start_time ?
                                dEdit.start_time.substring(11, 16) :
                                '';

                            document.getElementById('edit_end_time').value =
                                dEdit.end_time ?
                                dEdit.end_time.substring(11, 16) :
                                '';

                            const editRouteBody =
                                document.getElementById('editRouteTableBody');

                            editRouteBody.innerHTML = '';

                            if (dEdit.routes && dEdit.routes.length) {

                                dEdit.routes.forEach((route, index) => {

                                    editRouteBody.insertAdjacentHTML(
                                        'beforeend',
                                        createEditRouteRow(
                                            index + 1,
                                            route.origin || '',
                                            route.destination || ''
                                        )
                                    );
                                });

                            } else {

                                editRouteBody.innerHTML =
                                    createEditRouteRow(1);
                            }

                            document.getElementById('edit_cpny_id_site').value =
                                dEdit.cpny_id_site || '';

                            document.getElementById('edit_purpose_id').value =
                                dEdit.purpose_id || '';

                            document.getElementById('edit_purpose_descr').value =
                                dEdit.purpose_descr || '';

                            toggleEditPurposeDescription();
                        };
                    }

                    if (
                        d.status === 'D' &&
                        isOwner
                    ) {

                        cancelBtn.classList.remove('hidden');

                        cancelBtn.onclick = async function() {

                            const confirm = await Swal.fire({
                                icon: 'warning',
                                title: 'Cancel Booking?',
                                text: 'This request will be cancelled.',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, Cancel',
                                cancelButtonText: 'Back',
                                confirmButtonColor: '#ef4444'
                            });

                            if (!confirm.isConfirmed) {
                                return;
                            }

                            try {

                                const response = await fetch(
                                    `/bookingcar/cancel/${d.docid}`, {
                                        method: 'POST',

                                        headers: {
                                            'X-CSRF-TOKEN': document
                                                .querySelector(
                                                    'meta[name="csrf-token"]'
                                                )
                                                .getAttribute('content')
                                        }
                                    }
                                );

                                const result = await response.json();

                                if (!response.ok) {

                                    throw new Error(
                                        result.message || 'Cancel failed'
                                    );
                                }

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: result.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                });

                                closeBookingDetailModal();

                                fetchBookingList();

                            } catch (err) {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: err.message
                                });
                            }
                        };
                    }

                    let canApprove = false;

                    try {

                        const trackingResponse = await fetch(
                            `/bookingcar/tracking/${d.eid}`
                        );

                        const trackingResult = await trackingResponse.json();

                        const activeStep =
                            (trackingResult.steps || [])
                            .find(step => step.status === 'P');

                        if (activeStep?.aprv_username) {

                            const approvers =
                                String(activeStep.aprv_username)
                                .split(',')
                                .map(v => v.trim().toLowerCase());

                            canApprove =
                                approvers.includes(currentUser);
                        }

                    } catch (err) {

                        console.error(
                            'Failed check approval access',
                            err
                        );
                    }

                    if (canApprove && d.status === 'P') {

                        approvalActions.classList.remove('hidden');

                        // APPROVE
                        document.getElementById('approveBookingBtn').onclick = async function() {

                            const confirm = await Swal.fire({
                                icon: 'question',
                                title: 'Approve Booking?',
                                text: 'Approve this booking request?',
                                showCancelButton: true,
                                confirmButtonText: 'Approve'
                            });

                            if (!confirm.isConfirmed) return;

                            try {

                                const response = await fetch(
                                    `/bookingcar/approve/${d.docid}`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document
                                                .querySelector('meta[name="csrf-token"]')
                                                .getAttribute('content')
                                        }
                                    }
                                );

                                const result = await response.json();

                                if (!response.ok) {
                                    throw new Error(
                                        result.message || 'Approve failed'
                                    );
                                }

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: result.message,
                                    timer: 1800,
                                    showConfirmButton: false
                                });

                                closeBookingDetailModal();

                                fetchBookingList();

                            } catch (err) {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: err.message
                                });
                            }
                        };

                        // REVISE
                        document.getElementById('reviseBookingBtn').onclick =
                            function() {
                                openReasonModal('revise', d.docid);
                            };

                        // REJECT
                        document.getElementById('rejectBookingBtn').onclick =
                            function() {
                                openReasonModal('reject', d.docid);
                            };
                    }
                    @if (auth()->user()->hasRole('GAACCESS'))

                        const approveBtn = document.getElementById('approveBookingBtn');
                        const reviseBtn = document.getElementById('reviseBookingBtn');
                        const rejectBtn = document.getElementById('rejectBookingBtn');

                        // RESET BUTTON STATE
                        approveBtn.classList.remove('hidden', 'bg-black');
                        approveBtn.classList.add('bg-emerald-500');

                        reviseBtn.classList.remove('hidden');
                        rejectBtn.classList.remove('hidden');

                        approveBtn.innerHTML = '✓ Approve';

                        if (
                            d.status === 'C' &&
                            (
                                !d.driver ||
                                !d.no_polisi
                            )
                        ) {

                            approvalActions.classList.remove('hidden');

                            approveBtn.classList.remove('bg-emerald-500');
                            approveBtn.classList.add('bg-black');

                            approveBtn.innerHTML = 'Process';

                            approveBtn.onclick = function() {
                                openGaProcessModal(d.eid);
                            };

                            reviseBtn.classList.add('hidden');
                            rejectBtn.classList.add('hidden');

                        }
                    @endif
                @endif

                // =========================================================
                // LOAD TRACKING
                // =========================================================

                await loadBookingTracking(d.eid);

            } catch (err) {

                console.error(err);

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message
                });
            }
        }

        // async function loadBookingTracking(eid) {

        //     try {

        //         const response = await fetch(
        //             `/bookingcar/tracking/${eid}`
        //         );

        //         const result = await response.json();

        //         const wrapper =
        //             document.getElementById(
        //                 'bookingApprovalFlow'
        //             );

        //         wrapper.innerHTML = '';

        //         if (!result.steps?.length) {

        //             wrapper.innerHTML = `
    //                 <div class="text-sm text-gray-400">
    //                     No approval data
    //                 </div>
    //             `;

        //             return;
        //         }

        //         let currentStep = null;

        //         const totalSteps =
        //             result.steps.length;

        //         result.steps.forEach((step, index) => {

        //             step.is_last =
        //                 index === totalSteps - 1;

        //             if (
        //                 step.status === 'P' &&
        //                 !currentStep
        //             ) {
        //                 currentStep = step;
        //             }

        //             const isActive =
        //                 step.status === 'P';

        //             const isDone =
        //                 step.status === 'A' ||
        //                 step.status === 'C';

        //             const isRejected =
        //                 step.status === 'R';

        //             const isRevise =
        //                 step.status === 'D';

        //             let dotColor =
        //                 'bg-gray-300';

        //             let lineColor =
        //                 'bg-gray-200';

        //             let badgeClass =
        //                 'bg-gray-100 text-gray-500';

        //             if (isDone) {

        //                 dotColor =
        //                     'bg-emerald-500';

        //                 lineColor =
        //                     'bg-emerald-400';

        //                 badgeClass =
        //                     'bg-emerald-100 text-emerald-700';
        //             }

        //             if (isActive) {

        //                 dotColor =
        //                     'bg-blue-500 ring-4 ring-blue-100';

        //                 badgeClass =
        //                     'bg-blue-100 text-blue-700';
        //             }

        //             if (isRejected) {

        //                 dotColor =
        //                     'bg-red-500';

        //                 lineColor =
        //                     'bg-red-200';

        //                 badgeClass =
        //                     'bg-red-100 text-red-700';
        //             }

        //             if (isRevise) {

        //                 dotColor =
        //                     'bg-yellow-400';

        //                 lineColor =
        //                     'bg-yellow-200';

        //                 badgeClass =
        //                     'bg-yellow-100 text-yellow-700';
        //             }

        //             wrapper.innerHTML += `

    //                 <div class="relative pl-7">

    //                     ${
    //                         !step.is_last
    //                         ? `
        //                                     <div class="absolute left-[8px] top-0 h-full w-[2px] ${lineColor}"></div>
        //                                 `
    //                         : ''
    //                     }

    //                     <!-- DOT -->
    //                     <div class="absolute left-0 top-1">

    //                         <div class="h-4 w-4 rounded-full ${dotColor}"></div>

    //                     </div>

    //                     <!-- CONTENT -->
    //                     <div class="pb-6">

    //                         <div class="flex items-start justify-between gap-3">

    //                             <div class="min-w-0">

    //                                 <div class="text-sm font-semibold text-gray-900">
    //                                     ${escapeHtml(step.title || '-')}
    //                                 </div>

    //                                 ${
    //                                     step.by
    //                                     ? `
        //                                                 <div class="mt-1 text-xs text-gray-400">
        //                                                     ${escapeHtml(step.by)}
        //                                                     ${
        //                                                         step.at
        //                                                         ? `• ${escapeHtml(step.at)}`
        //                                                         : ''
        //                                                     }
        //                                                 </div>
        //                                             `
    //                                     : `
        //                                                 <div class="mt-1 text-xs italic text-gray-400">
        //                                                     Waiting for action
        //                                                 </div>
        //                                             `
    //                                 }

    //                             </div>

    //                             <div class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ${badgeClass}">
    //                                 ${escapeHtml(step.status_label || '-')}
    //                             </div>

    //                         </div>

    //                         ${
    //                             step.comment
    //                             ? `
        //                                         <div class="mt-2 rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2">

        //                                             <div class="text-[10px] font-semibold uppercase tracking-wide text-yellow-700">
        //                                                 Comment
        //                                             </div>

        //                                             <div class="mt-1 text-xs leading-relaxed text-yellow-900">
        //                                                 ${escapeHtml(step.comment)}
        //                                             </div>

        //                                         </div>
        //                                     `
    //                             : ''
    //                         }

    //                     </div>

    //                 </div>
    //             `;
        //         });

        //     } catch (err) {

        //         console.error(err);

        //         document.getElementById(
        //             'bookingApprovalFlow'
        //         ).innerHTML = `
    //             <div class="text-sm text-red-500">
    //                 Failed to load approval
    //             </div>
    //         `;
        //     }
        // }

        async function loadBookingTracking(eid) {

            try {

                const response = await fetch(
                    `/bookingcar/tracking/${eid}`
                );

                const result = await response.json();

                const wrapper =
                    document.getElementById(
                        'bookingApprovalFlow'
                    );

                wrapper.innerHTML = '';

                if (!result.steps?.length) {

                    wrapper.innerHTML = `
                        <div class="text-sm text-gray-400 dark:text-gray-500">
                            No approval data
                        </div>
                    `;

                    return;
                }

                let currentStep = null;

                const totalSteps =
                    result.steps.length;

                result.steps.forEach((step, index) => {

                    step.is_last =
                        index === totalSteps - 1;

                    if (
                        step.status === 'P' &&
                        !currentStep
                    ) {
                        currentStep = step;
                    }

                    const isActive =
                        step.status === 'P';

                    const isDone =
                        step.status === 'A' ||
                        step.status === 'C';

                    const isRejected =
                        step.status === 'R';

                    const isRevise =
                        step.status === 'D';

                    // =====================================================
                    // DOT COLOR
                    // =====================================================

                    const dotColor =
                        isDone ?
                        'bg-emerald-500 shadow-lg shadow-emerald-500/20' :
                        isActive ?
                        'bg-blue-500 ring-4 ring-blue-100 dark:ring-blue-500/20' :
                        isRejected ?
                        'bg-red-500' :
                        isRevise ?
                        'bg-yellow-400' :
                        'bg-gray-300 dark:bg-gray-600';

                    // =====================================================
                    // LINE COLOR
                    // =====================================================

                    const lineColor =
                        isDone ?
                        'bg-emerald-400 dark:bg-emerald-500' :
                        'bg-gray-200 dark:bg-white/10';

                    // =====================================================
                    // BADGE COLOR
                    // =====================================================

                    const badgeClass =
                        isDone ?
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' :
                        isActive ?
                        'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300' :
                        isRejected ?
                        'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' :
                        isRevise ?
                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-300' :
                        'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-300';

                    wrapper.innerHTML += `

                        <div class="relative pl-7">

                            ${
                                !step.is_last
                                ? `
                                            <div class="
                                                absolute
                                                left-[7px]
                                                top-4
                                                h-full
                                                w-[2px]
                                                ${lineColor}
                                            "></div>
                                        `
                                : ''
                            }

                            <!-- DOT -->
                            <div class="
                                absolute
                                left-0
                                top-1
                                h-4
                                w-4
                                rounded-full
                                ${dotColor}
                            "></div>

                            <!-- CONTENT -->
                            <div class="pb-7">

                                <!-- HEADER -->
                                <div class="flex items-start justify-between gap-3">

                                    <div class="min-w-0 flex-1">

                                        <div class="
                                            text-sm
                                            font-semibold
                                            text-gray-900
                                            dark:text-white
                                        ">
                                            ${escapeHtml(step.title || '-')}
                                        </div>

                                        ${
                                            step.by
                                            ? `
                                                        <div class="
                                                            mt-1
                                                            text-xs
                                                            text-gray-400
                                                            dark:text-gray-500
                                                        ">
                                                            ${escapeHtml(step.by)}

                                                            ${
                                                                step.at
                                                                ? ` • ${escapeHtml(step.at)}`
                                                                : ''
                                                            }
                                                        </div>
                                                    `
                                            : `
                                                        <div class="
                                                            mt-1
                                                            text-xs
                                                            italic
                                                            text-gray-400
                                                            dark:text-gray-500
                                                        ">
                                                            Waiting for action
                                                        </div>
                                                    `
                                        }

                                    </div>

                                    <!-- STATUS -->
                                    <div class="
                                        shrink-0
                                        rounded-full
                                        px-2.5
                                        py-1
                                        text-[10px]
                                        font-semibold
                                        ${badgeClass}
                                    ">
                                        ${escapeHtml(step.status_label || '-')}
                                    </div>

                                </div>

                                ${
                                    step.comment
                                    ? `
                                                <div class="
                                                    mt-3
                                                    rounded-xl
                                                    border
                                                    border-yellow-200
                                                    bg-yellow-50
                                                    px-4
                                                    py-3
                                                    dark:border-yellow-500/20
                                                    dark:bg-yellow-500/10
                                                ">

                                                    <div class="
                                                        text-[10px]
                                                        font-semibold
                                                        uppercase
                                                        tracking-[0.15em]
                                                        text-yellow-700
                                                        dark:text-yellow-300
                                                    ">
                                                        Comment
                                                    </div>

                                                    <div class="
                                                        mt-2
                                                        text-xs
                                                        leading-relaxed
                                                        text-yellow-900
                                                        dark:text-yellow-100
                                                    ">
                                                        ${escapeHtml(step.comment)}
                                                    </div>

                                                </div>
                                            `
                                    : ''
                                }

                            </div>

                        </div>
                    `;
                });

            } catch (err) {

                console.error(err);

                document.getElementById(
                    'bookingApprovalFlow'
                ).innerHTML = `
                    <div class="text-sm text-red-500 dark:text-red-400">
                        Failed to load approval
                    </div>
                `;
            }
        }

        const reasonModal =
            document.getElementById('reasonModal');

        const reasonModalTitle =
            document.getElementById('reasonModalTitle');

        const reasonInput =
            document.getElementById('reasonInput');

        const reasonError =
            document.getElementById('reasonError');

        const cancelReasonBtn =
            document.getElementById('cancelReasonBtn');

        const submitReasonBtn =
            document.getElementById('submitReasonBtn');

        function openReasonModal(type, docid) {

            currentReasonAction = type;

            currentBookingDocid = docid;

            reasonInput.value = '';

            reasonError.classList.add('hidden');

            if (type === 'revise') {
                reasonModalTitle.innerText =
                    'Revision Reason';
            }

            if (type === 'reject') {
                reasonModalTitle.innerText =
                    'Reject Reason';
            }

            reasonModal.classList.remove('hidden');

            reasonModal.classList.add('flex');

            document.body.classList.add('overflow-hidden');

            setTimeout(() => {
                reasonInput.focus();
            }, 100);
        }

        function closeReasonModal() {

            reasonModal.classList.add('hidden');

            reasonModal.classList.remove('flex');

            if (
                !viewBookingModal.classList.contains('hidden') ||
                !editBookingModal.classList.contains('hidden')
            ) {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }

            currentReasonAction = null;
            currentBookingDocid = null;
        }

        function createEditRouteRow(index, from = '', destination = '') {

            return `
                <tr>

                    <td class="px-4 py-3 text-sm font-medium text-gray-500">
                        ${index}
                    </td>

                    <td class="px-4 py-3">

                        <input type="text"
                            name="location_from[]"
                            value="${escapeHtml(from)}"
                            placeholder="Pickup location"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                            required>

                    </td>

                    <td class="px-4 py-3">

                        <input type="text"
                            name="destination[]"
                            value="${escapeHtml(destination)}"
                            placeholder="Destination"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                            required>

                    </td>

                    <td class="px-4 py-3 text-right">

                        <button type="button"
                            onclick="removeEditRouteRow(this)"
                            class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100">

                            Remove

                        </button>

                    </td>

                </tr>
            `;
        }

        function removeEditRouteRow(btn) {

            const tbody =
                document.getElementById('editRouteTableBody');

            if (tbody.querySelectorAll('tr').length <= 1) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Minimum 1 Route',
                    text: 'At least one route is required'
                });

                return;
            }

            btn.closest('tr').remove();

            refreshEditRouteNumber();
        }
        document.getElementById('editAddRouteBtnEdit')
            ?.addEventListener('click', function() {

                const tbody =
                    document.getElementById('editRouteTableBody');

                const index =
                    tbody.querySelectorAll('tr').length + 1;

                tbody.insertAdjacentHTML(
                    'beforeend',
                    createEditRouteRow(index)
                );
            });

        function filterEditUserRequest() {

            const selectedDept = editDeptSelect.value;

            editUserRequestSelect.innerHTML = `
                <option value="">
                    Select passenger
                </option>
            `;

            editUserRequestOptions.forEach(option => {

                if (!option.value) return;

                const dept =
                    option.getAttribute('data-dept');

                if (!selectedDept || dept === selectedDept) {

                    editUserRequestSelect.appendChild(
                        option.cloneNode(true)
                    );
                }
            });
        }

        function toggleEditPurposeDescription() {

            editPurposeDescrWrapper.classList.remove('hidden');

            editPurposeDescr.required = true;
        }
        function escapeHtml(str = '') {

            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        editPurposeSelect?.addEventListener(
            'change',
            toggleEditPurposeDescription
        );

        editDeptSelect?.addEventListener(
            'change',
            filterEditUserRequest
        );

        cancelReasonBtn?.addEventListener(
            'click',
            closeReasonModal
        );

        // reasonModal?.addEventListener(
        //     'click',
        //     function(e) {

        //         if (e.target === reasonModal) {
        //             closeReasonModal();
        //         }
        //     }
        // );

        submitReasonBtn?.addEventListener(
            'click',
            async function() {

                try {

                    const comment =
                        reasonInput.value.trim();

                    if (!comment) {

                        reasonError.classList.remove(
                            'hidden'
                        );

                        return;
                    }

                    submitReasonBtn.disabled = true;

                    submitReasonBtn.innerText =
                        'Submitting...';

                    let url = '';

                    if (currentReasonAction === 'revise') {
                        url =
                            `/bookingcar/revise/${currentBookingDocid}`;
                    }

                    if (currentReasonAction === 'reject') {
                        url =
                            `/bookingcar/reject/${currentBookingDocid}`;
                    }

                    const response =
                        await fetch(url, {
                            method: 'POST',

                            headers: {
                                'Content-Type': 'application/json',

                                'X-CSRF-TOKEN': document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .getAttribute('content')
                            },

                            body: JSON.stringify({
                                comment
                            })
                        });

                    const result =
                        await response.json();

                    if (!response.ok) {
                        throw new Error(
                            result.message ||
                            'Process failed'
                        );
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeReasonModal();

                    closeBookingDetailModal();

                    fetchBookingList();

                } catch (err) {

                    console.error(err);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message
                    });

                } finally {

                    submitReasonBtn.disabled = false;

                    submitReasonBtn.innerText =
                        'Submit';
                }
            }
        );

        // document.getElementById('editBookingBtn')?.addEventListener(
        //     'click',
        //     async function() {

        //         const eid = this.dataset.eid;

        //         const response = await fetch(
        //             `/bookingcar/detail/${eid}`
        //         );

        //         const result = await response.json();

        //         if (!result.success) return;

        //         const d = result.data;

        //         // const tracking =
        //         //     result.tracking || [];

        //         viewBookingModal.classList.add('hidden');

        //         editBookingModal.classList.remove('hidden');
        //         document.body.classList.add('overflow-hidden');

        //         document.getElementById('edit_booking_docid').value =
        //             d.docid;

        //         document.getElementById('edit_booking_eid').value =
        //             d.eid;

        //         document.getElementById('edit_cpny_id').value =
        //             d.cpny_id || '';

        //         document.getElementById('edit_department_id').value =
        //             d.department_id || '';

        //         document.getElementById('edit_user_peminta').value =
        //             d.user_peminta || '';

        //         filterEditUserRequest();

        //         document.getElementById('edit_user_request').value =
        //             d.user_request || '';


        //         document.getElementById('edit_passenger').value =
        //             d.passenger || '';

        //         document.getElementById('edit_booking_date').value =
        //             d.booking_date || '';

        //         document.getElementById('edit_start_time').value =
        //             d.start_time ? d.start_time.substring(11, 16) : '';

        //         document.getElementById('edit_end_time').value =
        //             d.end_time ? d.end_time.substring(11, 16) : '';

        //         const editRouteBody = document.getElementById('editRouteTableBody');

        //         editRouteBody.innerHTML = '';

        //         if (d.routes && d.routes.length) {

        //             d.routes.forEach((route, index) => {

        //                 editRouteBody.insertAdjacentHTML(
        //                     'beforeend',
        //                     createEditRouteRow(
        //                         index + 1,
        //                         route.location_from || '',
        //                         route.destination || ''
        //                     )
        //                 );
        //             });

        //         } else {

        //             editRouteBody.innerHTML =
        //                 createEditRouteRow(1);
        //         }

        //         document.getElementById('edit_cpny_id_site').value =
        //             d.cpny_id_site || '';

        //         document.getElementById('edit_purpose_id').value =
        //             d.purpose_id || '';

        //         document.getElementById('edit_purpose_descr').value =
        //             d.purpose_descr || '';

        //         toggleEditPurposeDescription();

        //         document.getElementById('editBookingReviseWrapper')
        //             .classList.add('hidden');

        //         document.getElementById('edit_booking_revise_reason')
        //             .innerHTML = '';

        //         try {

        //             const trackingResponse = await fetch(
        //                 `/bookingcar/tracking/${d.eid}`
        //             );

        //             const trackingResult =
        //                 await trackingResponse.json();

        //             const reviseSteps =
        //                 (trackingResult.steps || []).filter(
        //                     step => ['D', 'R'].includes(step.status) &&
        //                     step.comment
        //                 );

        //             if (reviseSteps.length) {

        //                 document.getElementById('editBookingReviseWrapper')
        //                     .classList.remove('hidden');

        //                 document.getElementById('edit_booking_revise_reason')
        //                     .innerHTML = reviseSteps.map(step => `
    //                         <div class="mb-3 rounded-xl border border-yellow-200 bg-white px-4 py-3">

    //                             <div class="flex items-center justify-between gap-3">

    //                                 <div class="text-xs font-semibold text-yellow-700">
    //                                     ${
    //                                         step.status === 'D'
    //                                         ? 'REVISION'
    //                                         : 'REJECTION'
    //                                     }
    //                                 </div>

    //                                 <div class="text-[11px] text-gray-400">
    //                                     ${step.by || '-'}
    //                                 </div>

    //                             </div>

    //                             ${
    //                                 step.at
    //                                 ? `
        //                                                 <div class="mt-1 text-[11px] text-gray-400">
        //                                                     ${step.at}
        //                                                 </div>
        //                                             `
    //                                 : ''
    //                             }

    //                             <div class="mt-3 text-sm leading-relaxed text-gray-700">
    //                                 ${escapeHtml(step.comment)}
    //                             </div>

    //                         </div>
    //                     `).join('');
        //             }

        //         } catch (err) {

        //             console.error(err);
        //         }
        //     }
        // );

        bookingCarForm?.addEventListener(
            'submit',
            async function(e) {

                e.preventDefault();

                try {

                    const submitBtn =
                        bookingCarForm.querySelector(
                            'button[type="submit"]'
                        );

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Submitting...';

                    const formData = new FormData(bookingCarForm);

                    const response = await fetch(
                        `/bookingcar/store`, {
                            method: 'POST',

                            headers: {
                                'X-CSRF-TOKEN': document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .getAttribute('content')
                            },

                            body: formData
                        }
                    );

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            result.message || 'Submit failed'
                        );
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeBookingModal();
                    fetchBookingList();

                } catch (err) {

                    console.error(err);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message
                    });

                } finally {

                    const submitBtn =
                        bookingCarForm.querySelector(
                            'button[type="submit"]'
                        );

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Submit Booking';
                }
            }
        );

        editBookingForm?.addEventListener(
            'submit',
            async function(e) {

                e.preventDefault();

                try {

                    const submitBtn =
                        document.getElementById(
                            'saveEditBookingBtn'
                        );

                    submitBtn.disabled = true;

                    submitBtn.innerHTML =
                        'Saving...';

                    const docid =
                        document.getElementById(
                            'edit_booking_docid'
                        ).value;

                    const formData =
                        new FormData(editBookingForm);

                    formData.append(
                        'user_peminta',
                        document.getElementById(
                            'edit_user_peminta'
                        ).value
                    );

                    const response =
                        await fetch(
                            `/bookingcar/update/${docid}`, {
                                method: 'POST',

                                headers: {
                                    'X-CSRF-TOKEN': document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        .getAttribute('content')
                                },

                                body: formData
                            }
                        );

                    const result =
                        await response.json();

                    if (!response.ok) {
                        throw new Error(
                            result.message ||
                            'Update failed'
                        );
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    editBookingModal.classList.add(
                        'hidden'
                    );

                    document.body.classList.remove(
                        'overflow-hidden'
                    );

                    fetchBookingList();

                    const eid =
                        document.getElementById(
                            'edit_booking_eid'
                        ).value;

                    if (eid) {

                        setTimeout(() => {
                            showBookingDetail(eid);
                        }, 300);

                    }

                } catch (err) {

                    console.error(err);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message
                    });

                } finally {

                    const submitBtn =
                        document.getElementById(
                            'saveEditBookingBtn'
                        );

                    submitBtn.disabled = false;

                    submitBtn.innerHTML =
                        'Save Changes';
                }
            }
        );


        // =========================================================
        // GA PROCESS MODAL
        // =========================================================

        const gaProcessModal =
            document.getElementById('gaProcessModal');

        const gaProcessForm =
            document.getElementById('gaProcessForm');

        const gaDriver =
            document.getElementById('ga_driver');

        const gaHandphone =
            document.getElementById('ga_handphone');

        const gaVehicle =
            document.getElementById('ga_vehicle');

        const gaNoPolisi =
            document.getElementById('ga_no_polisi');

        // =========================================================
        // OPEN MODAL
        // =========================================================

        async function openGaProcessModal(eid) {

            try {

                const response = await fetch(
                    `/bookingcar/detail/${eid}`
                );

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message);
                }

                const d = result.data;

                document.getElementById(
                    'ga_process_docid'
                ).value = d.docid;

                document.getElementById(
                    'ga_booking_docid'
                ).innerText = d.docid || '-';

                document.getElementById(
                        'ga_booking_requester'
                    ).innerText =
                    d.user_request || d.user_peminta || '-';

                document.getElementById('ga_booking_route').innerText =
                    d.routes?.length ?
                    d.routes.map(r =>
                        `${r.origin || '-'} → ${r.destination || '-'}`
                    ).join(', ') :
                    '-';

                document.getElementById(
                        'ga_booking_date'
                    ).innerText =
                    d.booking_date || '-';

                gaDriver.value =
                    d.driver || '';

                gaHandphone.value =
                    d.handphone || '';

                gaNoPolisi.value =
                    d.no_polisi || '';

                gaVehicle.value =
                    d.no_polisi || '';

                gaProcessModal.classList.remove('hidden');

                document.body.classList.add('overflow-hidden');

            } catch (err) {

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message
                });
            }
        }

        // =========================================================
        // CLOSE MODAL
        // =========================================================

        function closeGaProcessModal() {

            gaProcessModal.classList.add('hidden');

            gaProcessForm.reset();

            if (
                !viewBookingModal.classList.contains('hidden')
            ) {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        }

        // =========================================================
        // AUTO FILL DRIVER HP
        // =========================================================

        gaDriver?.addEventListener(
            'change',
            function() {

                const selected =
                    this.options[this.selectedIndex];

                gaHandphone.value =
                    selected.dataset.hp || '';
            }
        );

        // =========================================================
        // AUTO FILL NOPOL
        // =========================================================

        gaVehicle?.addEventListener(
            'change',
            function() {

                gaNoPolisi.value =
                    this.value || '';
            }
        );

        // =========================================================
        // CLOSE EVENTS
        // =========================================================

        document.getElementById(
            'closeGaProcessModal'
        )?.addEventListener(
            'click',
            closeGaProcessModal
        );

        document.getElementById(
            'cancelGaProcessBtn'
        )?.addEventListener(
            'click',
            closeGaProcessModal
        );

        // gaProcessModal?.addEventListener(
        //     'click',
        //     function(e) {

        //         if (e.target === gaProcessModal) {
        //             closeGaProcessModal();
        //         }
        //     }
        // );

        // =========================================================
        // SUBMIT PROCESS
        // =========================================================

        gaProcessForm?.addEventListener(
            'submit',
            async function(e) {

                e.preventDefault();

                try {

                    const submitBtn =
                        document.getElementById(
                            'submitGaProcessBtn'
                        );

                    submitBtn.disabled = true;

                    submitBtn.innerHTML =
                        'Saving...';

                    const docid =
                        document.getElementById(
                            'ga_process_docid'
                        ).value;

                    const formData =
                        new FormData(gaProcessForm);

                    const response =
                        await fetch(
                            `/bookingcar/process/${docid}`, {
                                method: 'POST',

                                headers: {
                                    'X-CSRF-TOKEN': document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        .getAttribute('content')
                                },

                                body: formData
                            }
                        );

                    const result =
                        await response.json();

                    if (!response.ok) {

                        throw new Error(
                            result.message ||
                            'Process failed'
                        );
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeGaProcessModal();

                    closeBookingDetailModal();

                    fetchBookingList();

                } catch (err) {

                    console.error(err);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message
                    });

                } finally {

                    const submitBtn =
                        document.getElementById(
                            'submitGaProcessBtn'
                        );

                    submitBtn.disabled = false;

                    submitBtn.innerHTML =
                        'Save Driver Assignment';
                }
            }
        );

        filterUserRequest();
        fetchBookingList().then(() => {

            const path = window.location.pathname;

            const match = path.match(/\/showbookingcar\/(.+)/);

            if (match && match[1]) {

                showBookingDetail(match[1]);
            }
        });
    </script>
</x-app-layout>
