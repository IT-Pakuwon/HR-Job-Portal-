<x-app-layout>
    <div class="mb-4 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <!-- LEFT -->
            <div class="flex items-center gap-3">

                <!-- ICON -->
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-gray-700 to-gray-900 text-lg text-white shadow-sm">
                    🚕
                </div>

                <!-- TITLE -->
                <div>

                    <h1 class="text-lg font-semibold tracking-tight text-gray-900">
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
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                    <span>📋</span>

                    <span>
                        Listing
                    </span>

                </button>

                <!-- CREATE -->
                <button id="openCreateVoucherModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-black">

                    <span>➕</span>

                    <span>
                        New Booking
                    </span>

                </button>

            </div>

        </div>

    </div>

    <div id="mainGrid" class="grid grid-cols-1 gap-4 lg:grid-cols-12">

        <!-- 📅 CALENDAR -->
        <div id="calendarWrapper" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a] lg:col-span-8">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-white/10">

                <div>

                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                        Calendar View
                    </div>

                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Voucher Taxi request schedule overview
                    </div>

                </div>

                <div class="hidden items-center gap-2 md:flex">

                    <div class="flex items-center gap-2 text-xs text-gray-500">

                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        Pending Approval

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

        <!-- 📋 LIST PANEL -->
        <div id="voucherListPanel"
            class="flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a] lg:col-span-4">

            <!-- HEADER -->
            <div class="border-b border-gray-100 p-4 dark:border-white/10">

                <div class="flex items-start justify-between gap-3">

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                            Voucher List
                        </h3>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Latest requests
                        </p>
                    </div>

                    <span id="voucherCount"
                        class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-200">
                        0
                    </span>

                </div>

                <!-- FILTER -->
                <div class="mt-4 flex w-full flex-wrap justify-between gap-2">

                    <button class="voucher-filter active-filter" data-filter="ALL">
                        All
                    </button>

                    <button class="voucher-filter" data-filter="P">
                        Pending
                    </button>

                    <button class="voucher-filter" data-filter="C">
                        Completed
                    </button>

                     @if (auth()->check() && auth()->user()->hasRole('GAACCESS'))
                        <button class="voucher-filter" data-filter="WAITING_PROCESS">
                            Waiting Process
                        </button>
                    @endif

                    <button class="voucher-filter" data-filter="D">
                        Revise
                    </button>

                    <button class="voucher-filter" data-filter="R">
                        Rejected
                    </button>

                    <button class="voucher-filter" data-filter="X">
                        Cancelled
                    </button>

                </div>

            </div>

            <!-- BODY -->
            <div class="flex-1 overflow-y-auto p-2">

                <div id="voucherListBody" class="space-y-2">
                </div>

            </div>

            <!-- PAGINATION -->
            <div class="flex items-center justify-between border-t border-gray-100 px-4 py-3 dark:border-white/10">

                <div id="voucherPageInfo" class="text-xs text-gray-500 dark:text-gray-400">
                    Showing 1–5
                </div>

                <div class="flex items-center gap-2">

                    <button id="prevVoucherPage"
                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                        Prev
                    </button>

                    <button id="nextVoucherPage"
                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                        Next
                    </button>

                </div>

            </div>

        </div>

    </div>

    <div id="createVoucherModal"
        class="fixed inset-0 z-50 flex hidden items-center justify-center overflow-y-auto bg-black/40">

        <div class="mb-10 mt-16 w-full max-w-4xl overflow-hidden rounded-lg bg-white shadow-2xl dark:bg-gray-900">

            <form id="voucherTaxiForm" method="POST"class="flex flex-col">

                <!-- HEADER -->
                <div class="border-b border-gray-200 px-8 py-6 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Create Voucher Taxi
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Fill in the details below
                    </p>
                </div>

                <!-- BODY -->
                <div class="max-h-[70vh] space-y-8 overflow-y-auto px-8 py-6">

                    <!-- BASIC -->
                    <div class="space-y-4">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                            Basic Info
                        </p>

                        <div class="grid gap-5 md:grid-cols-2">

                            <div>
                                <label class="text-xs text-gray-500">Company *</label>
                                <select name="cpny_id"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none dark:border-gray-700 dark:bg-transparent"
                                    required>
                                    <option>Select company</option>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}">{{ $p->cpny_id }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div>
                                <label class="text-xs text-gray-500">Department *</label>
                                <select name="department_id" id="department_id"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none dark:border-gray-700 dark:bg-transparent"
                                    required>
                                    <option>Select department</option>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}">{{ $p->department_id }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Requester *</label>
                                <input type="text" value="{{ auth()->user()->name }}"
                                    class="mt-1 w-full rounded-lg border bg-gray-100 px-3 py-2 text-sm" readonly>

                                <input type="hidden" name="user_peminta" value="{{ auth()->user()->username }}">
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Date Used *</label>
                                <input type="date" name="date_used"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none dark:border-gray-700 dark:bg-transparent"
                                    required>
                            </div>

                        </div>
                    </div>

                    <!-- TRIP -->
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                Trip Info
                            </p>

                            <div class="flex gap-2 rounded-lg bg-gray-100 p-1">
                                <label class="cursor-pointer">
                                    <input type="radio" name="type_trip" value="Return" checked class="peer hidden">
                                    <span
                                        class="rounded-md px-3 py-1 text-xs peer-checked:bg-black peer-checked:text-white">
                                        Return
                                    </span>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="type_trip" value="One Way" class="peer hidden">
                                    <span
                                        class="rounded-md px-3 py-1 text-xs peer-checked:bg-black peer-checked:text-white">
                                        One Way
                                    </span>
                                </label>
                            </div>
                        </div>


                        <div class="flex justify-between gap-4">
                            <div class="flex justify-between gap-4">

                                <!-- ORIGIN -->
                                <div class="flex-1">
                                    <label class="text-xs text-gray-500">Origin *</label>
                                    <input type="text" name="origin"
                                        class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                                        placeholder="From where?" required>
                                </div>

                                <!-- DESTINATION -->
                                <div class="flex-1">
                                    <label class="text-xs text-gray-500">Destination *</label>
                                    <input type="text" name="destination"
                                        class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                                        placeholder="Where to?" required>
                                </div>

                            </div>

                            <div class="flex-1">
                                <label class="text-xs text-gray-500">Purpose *</label>
                                <input type="text" name="purpose"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none dark:border-gray-700 dark:bg-transparent"
                                    placeholder="Purpose..." required>
                            </div>
                        </div>

                    </div>

                    <!-- FINANCE -->
                    <div class="space-y-4">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                            Finance
                        </p>

                        <div class="grid gap-5 md:grid-cols-2">

                            <div>
                                <label class="text-xs text-gray-500">Company Expense *</label>
                                <select name="cpny_id_expense"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none dark:border-gray-700 dark:bg-transparent"
                                    required>
                                    <option>Select company</option>
                                    @foreach ($company as $p)
                                        <option value="{{ $p->cpny_id }}">{{ $p->cpny_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Topup *</label>
                                <select name="user_topup" id="user_topup"
                                    class="mt-1 w-full rounded-lg border px-3 py-2 text-sm" required>

                                    <option value="">Select user</option>

                                    @foreach ($requesters as $p)
                                        <option value="{{ $p->username }}" data-dept="{{ trim($p->department_id) }}">
                                            {{ $p->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="flex items-center justify-between border-t border-gray-200 px-8 py-5 dark:border-gray-700">
                    <button type="button" id="closeCreateVoucherModal"
                        class="text-sm text-gray-500 hover:text-gray-800">
                        Cancel
                    </button>

                    <button type="submit" class="rounded-lg bg-black px-5 py-2 text-sm text-white hover:opacity-90">
                        Submit
                    </button>
                </div>

            </form>
        </div>
    </div>

    <div id="editVoucherTaxiModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/40 p-4">

        <div class="w-full max-w-4xl overflow-hidden rounded-lg bg-white shadow-2xl">

            <form id="editVoucherTaxiForm" class="flex flex-col">
                @csrf

                <input type="hidden" id="edit_docid">

                <div class="flex items-center justify-between border-b px-6 py-5">

                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            ✏️
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                Edit Booking
                            </h2>

                            <div id="editStatusBadge"
                                class="mt-1 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium">
                                Status
                            </div>
                        </div>

                    </div>

                    <div class="text-right text-xs leading-tight text-gray-400">
                        <div id="editMetaUser"></div>
                        <div id="editMetaDate"></div>
                    </div>

                </div>

                <!-- BODY -->
                <div class="max-h-[70vh] space-y-8 overflow-y-auto px-8 py-6">

                    <!-- BASIC -->
                    <div class="space-y-4">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                            Basic Info
                        </p>

                        <div class="grid gap-5 md:grid-cols-2">

                            <div>
                                <label class="text-xs text-gray-500">Company</label>
                                <select name="cpny_id" id="edit_cpny_id"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                                    <option value="">Select company</option>
                                    @foreach ($company as $c)
                                        <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Department</label>
                                <select name="department_id" id="edit_department_id"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                                    <option value="">Select department</option>
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}">{{ $p->department_id }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Requester</label>
                                <input type="text" id="edit_user_peminta" name="user_peminta"
                                    class="mt-1 w-full rounded-lg border bg-gray-100 px-3 py-2 text-sm" readonly>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Date</label>
                                <input type="date" name="date_used" id="edit_date_used"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                            </div>

                        </div>
                    </div>

                    <!-- TRIP -->
                    <div class="space-y-4">

                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                                Trip Info
                            </p>

                            <div class="flex gap-2 rounded-lg bg-gray-100 p-1">
                                <label class="cursor-pointer">
                                    <input type="radio" name="type_trip" value="Return" class="peer hidden">
                                    <span
                                        class="rounded-md px-3 py-1 text-xs peer-checked:bg-black peer-checked:text-white">
                                        Return
                                    </span>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="type_trip" value="One Way" class="peer hidden">
                                    <span
                                        class="rounded-md px-3 py-1 text-xs peer-checked:bg-black peer-checked:text-white">
                                        One Way
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-between gap-4">
                            <div class="flex justify-between gap-4">

                                <!-- ORIGIN -->
                                <div class="flex-1">
                                    <label class="text-xs text-gray-500">Origin</label>
                                    <input type="text" name="origin" id="edit_origin"
                                        class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                                </div>

                                <!-- DESTINATION -->
                                <div class="flex-1">
                                    <label class="text-xs text-gray-500">Destination</label>
                                    <input type="text" name="destination" id="edit_destination"
                                        class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                                </div>

                            </div>

                            <div class="flex-1">
                                <label class="text-xs text-gray-500">Purpose</label>
                                <input type="text" name="purpose" id="edit_purpose"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                            </div>
                        </div>
                        <!-- REVISE NOTE -->
                        <div id="editReviseReasonWrapper"
                            class="mx-8 mb-6 hidden rounded-lg border border-yellow-200 bg-yellow-50 px-5 py-4">

                            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-yellow-700">
                                Revision Reason
                            </div>

                            <div id="edit_revise_reason" class="text-sm font-medium text-yellow-900">
                            </div>

                        </div>

                    </div>

                    <!-- FINANCE -->
                    <div class="space-y-4">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400">
                            Finance
                        </p>

                        <div class="grid gap-5 md:grid-cols-2">

                            <div>
                                <label class="text-xs text-gray-500">Company Expense</label>
                                <select name="cpny_id_expense" id="edit_cpny_id_expense"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                                    <option value="">Select company</option>
                                    @foreach ($company as $c)
                                        <option value="{{ $c->cpny_id }}">
                                            {{ $c->cpny_id }} - {{ $c->cpny_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-500">Topup</label>
                                <select name="user_topup" id="edit_user_topup"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none">
                                    <option value="">Select user</option>
                                    @foreach ($requesters as $p)
                                        <option value="{{ $p->username }}">
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- FOOTER -->
                <div class="flex items-center justify-between border-t bg-gray-50 px-6 py-4">

                    <button type="button" id="cancelEditVoucherTaxiBtn"
                        class="text-sm text-gray-500 hover:text-black">
                        Cancel
                    </button>

                    <button type="submit" id="saveEditVoucherTaxiBtn"
                        class="rounded-lg bg-black px-5 py-2 text-sm font-medium text-white hover:opacity-90">
                        Save Changes
                    </button>

                </div>

            </form>
        </div>
    </div>

    <div id="viewVoucherModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50">

        <!-- CENTER -->
        <div class="flex min-h-screen items-center justify-center p-4">

            <!-- MODAL -->
            <div class="w-full max-w-6xl overflow-hidden rounded-lg border border-gray-200 bg-[#fcfcfd] shadow-2xl">

                <!-- HEADER -->
                <div class="flex items-start justify-between border-b border-gray-200 px-8 py-6">

                    <div>
                        <h2 class="text-xl font-semibold tracking-tight text-gray-900">
                            Voucher Detail
                        </h2>

                        <p class="mt-1 text-sm text-gray-400">
                            Booking information & approval workflow
                        </p>
                    </div>

                    <div class="flex items-center gap-2">

                        {{-- PRINT --}}
                        <a id="printVoucherBtn" href="#" target="_blank"
                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                            🖨 Print PDF
                        </a>

                        {{-- CLOSE --}}
                        <button onclick="closeViewModal()"
                            class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-black">
                            ✕
                        </button>

                    </div>

                </div>

                <!-- BODY -->
                <div class="grid grid-cols-1 gap-4 p-4 lg:grid-cols-[1.1fr_.9fr]">

                    <!-- ================= LEFT ================= -->
                    <div class="space-y-2">

                        <!-- MAIN INFO -->
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">

                            <!-- TOP -->
                            <div class="flex items-start justify-between border-b border-gray-100 pb-5">

                                <div>
                                    <div class="text-[11px] uppercase tracking-[0.18em] text-gray-400">
                                        Requester
                                    </div>

                                    <div id="view_user" class="mt-2 text-base font-medium text-gray-900">
                                    </div>
                                </div>

                                <div id="view_status_badge"
                                    class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                    Pending
                                </div>

                            </div>

                            <!-- CONTENT -->
                            <div class="mt-6 grid grid-cols-2 gap-x-8 gap-y-6">

                                <div>
                                    <div class="text-xs text-gray-400">
                                        Date
                                    </div>

                                    <div id="view_date" class="mt-1 text-sm font-medium text-gray-900">
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-gray-400">
                                        Trip Type
                                    </div>

                                    <div id="view_type_trip" class="mt-1 text-sm font-medium text-gray-900">
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-gray-400">
                                        Origin
                                    </div>

                                    <div id="view_origin" class="mt-1 text-sm font-medium text-gray-900">
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-gray-400">
                                        Destination
                                    </div>

                                    <div id="view_destination" class="mt-1 text-sm font-medium text-gray-900">
                                    </div>
                                </div>

                                <!-- ROUTE -->
                                <div class="col-span-2">

                                    <div class="rounded-lg bg-indigo-50 px-5 py-4">

                                        <div class="text-[11px] uppercase tracking-[0.15em] text-indigo-500">
                                            Route
                                        </div>

                                        <div id="view_route" class="mt-2 text-sm font-medium text-indigo-900">
                                        </div>

                                    </div>

                                </div>

                                <div>
                                    <div class="text-xs text-gray-400">
                                        Company
                                    </div>

                                    <div id="view_cpny" class="mt-1 text-sm font-medium text-gray-900">
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-gray-400">
                                        Department
                                    </div>

                                    <div id="view_dept" class="mt-1 text-sm font-medium text-gray-900">
                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- PURPOSE -->
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">

                            <div class="text-[11px] uppercase tracking-[0.18em] text-gray-400">
                                Purpose
                            </div>

                            <div id="view_purpose" class="mt-3 text-sm font-medium leading-relaxed text-gray-900">
                            </div>

                        </div>

                        <!-- ACTUAL EXPENSE -->
                        <div id="actualExpenseWrapper"
                            class="hidden rounded-lg border border-emerald-100 bg-emerald-50 p-4 shadow-sm">

                            <div class="flex items-center justify-between">

                                <div>

                                    <div class="text-[11px] uppercase tracking-[0.18em] text-emerald-600">
                                        Actual Expense
                                    </div>

                                    <div class="mt-3 text-[11px] font-medium tracking-tight text-emerald-900">

                                        <span id="view_actual_budget">
                                            Rp 0
                                        </span>

                                    </div>

                                </div>

                                <div
                                    class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                                    Processed
                                </div>

                            </div>

                        </div>

                        <!-- REVISE -->
                        <div id="reviseReasonWrapper"
                            class="hidden rounded-lg border border-yellow-200 bg-yellow-50 p-4 shadow-sm">

                            <div class="text-[11px] uppercase tracking-[0.18em] text-yellow-700">
                                Revision Reason
                            </div>

                            <div id="view_revise_reason" class="mt-3 text-sm leading-relaxed text-yellow-900">
                            </div>

                        </div>

                    </div>

                    <!-- ================= RIGHT ================= -->
                    <div class="space-y-4">

                        <!-- HEADER -->
                        <div class="flex items-center justify-between">

                            <div>
                                <h3 class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">
                                    Approval Workflow
                                </h3>
                            </div>

                            <!-- ACTIONS -->
                            <div class="flex items-center gap-2">

                                <div id="approvalActions" class="flex hidden items-center gap-2">

                                    <button id="approveBtn"
                                        class="rounded-lg bg-emerald-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-emerald-400">
                                        ✓ Approve
                                    </button>

                                    <button id="reviseBtn"
                                        class="rounded-lg bg-amber-400 px-4 py-2 text-xs font-medium text-black transition hover:bg-amber-300">
                                        ✎ Revise
                                    </button>

                                    <button id="rejectBtn"
                                        class="rounded-lg bg-red-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-red-400">
                                        ✕ Reject
                                    </button>

                                </div>

                                <div id="viewActions" class="flex items-center gap-2">
                                </div>

                            </div>

                        </div>

                        <!-- APPROVAL CARD -->
                        <div class="min-h-auto rounded-lg border border-gray-200 bg-white p-4 shadow-sm">

                            <!-- TIMELINE -->
                            <div id="approvalFlow" class="relative space-y-6">
                            </div>

                        </div>

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-6 py-4">

                    <button onclick="closeViewModal()" class="text-sm text-gray-500 transition hover:text-black">
                        Close
                    </button>

                    <div class="flex items-center gap-3">

                        <button id="cancelVoucherBtn"
                            class="hidden rounded-lg border border-red-200 bg-red-500 px-5 py-2 text-sm font-medium text-white transition hover:bg-red-600">
                            ✕ Cancel Request
                        </button>

                        <button id="openEditFromViewBtn"
                            class="hidden rounded-lg bg-black px-5 py-2 text-sm font-medium text-white transition hover:bg-gray-800">
                            ✏️ Edit Booking
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div id="reasonModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50">

        <div class="w-full max-w-md rounded-lg bg-white shadow-2xl">

            <!-- HEADER -->
            <div class="border-b px-6 py-4">
                <h2 id="reasonModalTitle" class="text-lg font-semibold text-gray-900">
                    Revision Reason
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Please provide a reason before continuing
                </p>
            </div>

            <!-- BODY -->
            <div class="p-4">

                <textarea id="reasonInput" rows="5" placeholder="Type your reason here..."
                    class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:border-black focus:outline-none"></textarea>

            </div>

            <!-- FOOTER -->
            <div class="flex justify-end gap-3 border-t bg-gray-50 px-6 py-4">

                <button id="cancelReasonBtn"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100">
                    Cancel
                </button>

                <button id="submitReasonBtn"
                    class="rounded-lg bg-black px-5 py-2 text-sm font-medium text-white hover:opacity-90">
                    Submit
                </button>

            </div>

        </div>
    </div>

    <!-- PROCESS MODAL -->
    <div id="processVoucherModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4">

        <div class="w-full max-w-2xl overflow-hidden rounded-lg bg-white shadow-2xl">

            <!-- HEADER -->
            <div class="border-b border-gray-100 px-7 py-6">

                <div class="flex items-start gap-4">

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 shadow-sm">
                        💰
                    </div>

                    <div class="flex-1">

                        <h2 class="text-xl font-semibold tracking-tight text-gray-900">
                            Process Voucher
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Review booking information and submit actual expense
                        </p>

                    </div>

                </div>

            </div>

            <!-- BODY -->
            <form id="processVoucherForm">

                <div class="max-h-[70vh] overflow-y-auto p-4">

                    <input type="hidden" id="process_docid">

                    <!-- INFORMATION -->
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm">

                        <div class="mb-5 flex items-center justify-between">

                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">
                                    Voucher Information
                                </div>

                                <div id="process_docno"
                                    class="mt-2 text-lg font-semibold tracking-tight text-gray-900">
                                    -
                                </div>
                            </div>

                            <div id="process_status"
                                class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-600">
                                Completed
                            </div>

                        </div>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">

                            <div>
                                <div class="text-xs text-gray-400">
                                    Requester
                                </div>

                                <div id="process_requester" class="mt-1 text-sm font-medium text-gray-900">
                                    -
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-400">
                                    Date Used
                                </div>

                                <div id="process_date" class="mt-1 text-sm font-medium text-gray-900">
                                    -
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-400">
                                    Company
                                </div>

                                <div id="process_company" class="mt-1 text-sm font-medium text-gray-900">
                                    -
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-400">
                                    Department
                                </div>

                                <div id="process_department" class="mt-1 text-sm font-medium text-gray-900">
                                    -
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-400">
                                    Trip Type
                                </div>

                                <div id="process_trip" class="mt-1 text-sm font-medium text-gray-900">
                                    -
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-400">
                                    Current Budget
                                </div>

                                <div id="process_budget" class="mt-1 text-sm font-semibold text-emerald-600">
                                    -
                                </div>
                            </div>

                            <div class="md:col-span-2">

                                <div class="rounded-lg border border-indigo-100 bg-indigo-50 px-5 py-4">

                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-indigo-500">
                                        Route
                                    </div>

                                    <div id="process_route" class="mt-2 text-sm font-semibold text-indigo-700">
                                        -
                                    </div>

                                </div>

                            </div>

                            <div class="md:col-span-2">

                                <div class="rounded-lg border border-gray-200 bg-white px-5 py-4">

                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                                        Purpose
                                    </div>

                                    <div id="process_purpose" class="mt-2 text-sm font-medium text-gray-800">
                                        -
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- INPUT -->
                    <div class="mt-6 rounded-lg border border-emerald-100 bg-emerald-50 p-4">

                        <div class="mb-5">

                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-600">
                                Actual Expense
                            </div>

                            <div class="mt-1 text-sm text-emerald-700">
                                Input final taxi cost
                            </div>

                        </div>

                        <div>

                            <label class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Actual Budget *
                            </label>

                            <div class="relative mt-2">

                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-gray-400">
                                    Rp
                                </div>

                                <input type="text" id="actual_budget_display" placeholder="0"
                                    class="w-full rounded-lg border border-gray-200 bg-white py-3 pl-12 pr-4 text-sm font-semibold text-gray-900 shadow-sm transition focus:border-black focus:outline-none focus:ring-4 focus:ring-black/5">

                                <input type="hidden" name="actual_budget" id="actual_budget">

                            </div>

                        </div>

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50 px-7 py-5">

                    <button type="button" id="closeProcessVoucherModal"
                        class="text-sm font-medium text-gray-500 transition hover:text-black">
                        Cancel
                    </button>

                    <button type="submit" id="submitProcessVoucherBtn"
                        class="rounded-lg bg-black px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                        Save Process
                    </button>

                </div>

            </form>

        </div>
    </div>



    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.currentUsername = @json(strtolower(trim(auth()->user()->username)));
        window.isGA = @json(auth()->user()->hasRole('GAACCESS'));

        function escapeHtml(text) {

            const div = document.createElement('div');

            div.innerText = text || '';

            return div.innerHTML;
        }
    </script>
    <script>
        let calendar;
        let selectedCell = null;
        let voucherData = [];
        let currentVoucherPage = 1;
        let voucherPerPage = 6;
        let currentVoucherFilter = 'ALL';
        document.addEventListener('DOMContentLoaded', function() {
            loadVoucherList();

            const calendarEl = document.getElementById('calendar');



            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                fixedWeekCount: false,

                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },

                dayMaxEvents: window.isGA ? 6 : 4,
                selectable: true,
                moreLinkClick: 'popover',

                dayCellContent: function(arg) {
                    return {
                        html: `
                        <div class="text-xs font-medium text-gray-500">
                            ${arg.date.getDate()}
                        </div>
                    `
                    };
                },

                dayCellDidMount: function(info) {

                    info.el.classList.add('rounded-lg', 'transition', 'cursor-pointer');
                    info.el.style.padding = '6px';

                    info.el.addEventListener('mouseenter', () => {
                        info.el.style.background = '#f9fafb';
                    });

                    info.el.addEventListener('mouseleave', () => {
                        if (info.el !== selectedCell) {
                            info.el.style.background = '';
                        }
                    });
                },

                dateClick: function(info) {

                    // clear previous
                    if (selectedCell) {
                        selectedCell.style.background = '';
                        selectedCell.style.border = '';
                    }

                    // set new
                    selectedCell = info.dayEl;
                    selectedCell.style.background = '#eef2ff';
                    selectedCell.style.border = '1px solid #c7d2fe';
                    selectedCell.style.borderRadius = '12px';

                    document.getElementById('createVoucherModal').classList.remove('hidden');
                    document.querySelector('[name="date_used"]').value = info.dateStr;
                },

                events: function(fetchInfo, successCallback, failureCallback) {

                    fetch('/vouchertaxi/json', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        })

                        .then(async (res) => {

                            const text = await res.text();

                            // console.log('RAW RESPONSE:', text);

                            try {
                                return JSON.parse(text);

                            } catch (e) {

                                console.error('❌ Invalid JSON response');
                                console.error(text);

                                throw new Error('Server did not return valid JSON');
                            }
                        })

                        .then((res) => {

                            // 🔥 SAFE GUARD
                            if (!res.data || !Array.isArray(res.data)) {

                                console.error('Invalid response structure:', res);

                                failureCallback(res);

                                return;
                            }

                            // const allowedstatuses = ['p', 'c', 'd', 'r'];
                            const allowedstatuses = ['p', 'c', 'd'];

                            const events = res.data
                                .filter(item =>
                                    allowedstatuses.includes(String(item.status).toLowerCase()))
                                .map(item => {

                                    if (!item.eid) {
                                        console.warn('⚠️ Missing EID from backend:', item);
                                    }

                                    return {
                                        id: item.docid,
                                        title: item.purpose || 'Taxi',
                                        start: item.date_used,
                                        allDay: true,

                                        extendedProps: {
                                            eid: item.eid || null,
                                            status: item.status,
                                            requester: item.user_peminta,
                                            origin: item.origin,
                                            destination: item.destination,

                                            cpny_id: item.cpny_id,
                                            department_id: item.department_id,
                                            cpny_id_expense: item.cpny_id_expense,
                                            user_topup: item.user_topup,
                                            created_by: item.created_by,

                                            actual_budget: item.actual_budget,
                                            type_trip: item.type_trip,
                                            max_budget: item.max_budget

                                        }
                                    };
                                });

                            successCallback(events);
                        })

                        .catch((err) => {

                            console.error('❌ Calendar fetch failed:', err);

                            failureCallback(err);
                        });
                },
                eventClick: function(info) {

                    // // console.log('EVENT CLICK:', info.event.extendedProps);

                    const createdBy = info.event.extendedProps.created_by;
                    const currentUser = window.currentUsername;

                    const eid = info.event.extendedProps.eid;

                    if (!eid) {
                        console.warn('⚠️ Missing EID for event:', info.event);
                    }

                    const data = {
                        docid: info.event.id,
                        eid: eid || null,

                        cpny_id: info.event.extendedProps.cpny_id,
                        department_id: info.event.extendedProps.department_id,
                        user_peminta: info.event.extendedProps.requester,

                        date_used: info.event.startStr,

                        origin: info.event.extendedProps.origin,
                        destination: info.event.extendedProps.destination,

                        purpose: info.event.title,

                        cpny_id_expense: info.event.extendedProps.cpny_id_expense,
                        user_topup: info.event.extendedProps.user_topup,

                        status: info.event.extendedProps.status,
                        created_by: createdBy,

                        max_budget: info.event.extendedProps.max_budget,
                        type_trip: info.event.extendedProps.type_trip
                    };

                    openViewModal(data);
                },

                eventContent: function(arg) {

                    const title = arg.event.title;
                    const requester = arg.event.extendedProps.requester || '';
                    const destination = arg.event.extendedProps.destination || '';
                    const status = arg.event.extendedProps.status;

                    const statusMap = {
                        P: 'Pending',
                        C: 'Completed',
                        R: 'Rejected',
                        D: 'Revise',
                        X: 'Cancelled'
                    };

                    const statusStyle = {

                        P: {
                            bg: '#3b82f6',
                            dot: '#bfdbfe',
                            text: '#ffffff'
                        },

                        C: {
                            bg: '#10b981',
                            dot: '#bbf7d0',
                            text: '#ffffff'
                        },

                        D: {
                            bg: '#facc15',
                            dot: '#dc2626',
                            text: '#000000'
                        },

                        R: {
                            bg: '#9ca3af',
                            dot: '#e5e7eb',
                            text: '#ffffff'
                        },

                        X: {
                            bg: '#6b7280',
                            dot: '#d1d5db',
                            text: '#ffffff'
                        }
                    };

                    const current = statusStyle[status] || statusStyle.P;

                    return {
                        html: `
                            <div style="
                                padding:6px 8px;
                                border-radius:10px;
                                font-size:10px;
                                line-height:1.2;
                                overflow:hidden;
                            ">

                                <!-- TITLE -->
                                <div style="
                                    font-weight:700;
                                    font-size:11px;
                                    color:${current.text};
                                    white-space:nowrap;
                                    overflow:hidden;
                                    text-overflow:ellipsis;
                                ">
                                    ${escapeHtml(requester)} - ${escapeHtml(title)}
                                </div>

                                <!-- BOTTOM -->
                                <div style="
                                    margin-top:3px;
                                    display:flex;
                                    align-items:center;
                                    justify-content:space-between;
                                    gap:6px;
                                ">

                                    <!-- DESTINATION -->
                                    <div style="
                                        display:flex;
                                        align-items:center;
                                        gap:3px;
                                        min-width:0;
                                        flex:1;
                                        color:${current.text};
                                        opacity:.9;
                                        overflow:hidden;
                                    ">
                                        <span style="font-size:9px;">📍</span>

                                        <span style="
                                            white-space:nowrap;
                                            overflow:hidden;
                                            text-overflow:ellipsis;
                                            font-size:10px;
                                        ">
                                            ${escapeHtml(destination)}
                                        </span>
                                    </div>

                                    <!-- STATUS DOT -->
                                    <div style="
                                        width:7px;
                                        height:7px;
                                        border-radius:999px;
                                        background:${current.dot};
                                        flex-shrink:0;
                                    "></div>

                                </div>

                            </div>
                        `
                    };
                },

                eventDidMount: function(info) {

                    info.el.style.border = '0';
                    info.el.style.padding = '0';
                    info.el.style.marginBottom = '2px';
                    info.el.style.borderRadius = '10px';

                    const status = info.event.extendedProps.status;

                    if (status === 'P') {
                        info.el.style.backgroundColor = '#3b82f6';
                    } else if (status === 'C') {
                        info.el.style.backgroundColor = '#10b981';
                        info.el.style.opacity = '0.85';
                    } else if (status === 'D') {
                        info.el.style.backgroundColor = '#facc15';
                    } else if (status === 'R') {
                        info.el.style.backgroundColor = '#9ca3af';
                        info.el.style.opacity = '0.7';
                    } else if (status === 'X') {
                        info.el.style.backgroundColor = '#6b7280';
                        info.el.style.opacity = '0.6';
                    }
                }
            });

            calendar.render();
            if (calendar) {
                calendar.updateSize();
            }


            setTimeout(() => {

                const pathParts = window.location.pathname.split('/');

                const eidFromUrl = pathParts[pathParts.length - 1];

                if (
                    !eidFromUrl ||
                    eidFromUrl === 'vouchertaxi'
                ) {
                    return;
                }

                fetch(`/vouchertaxi/detail/${eidFromUrl}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async res => {

                        const text = await res.text();

                        // console.log('AUTO OPEN RAW:', text);

                        try {
                            return JSON.parse(text);

                        } catch (e) {

                            console.error('INVALID AUTO OPEN JSON');
                            console.error(text);

                            return null;
                        }
                    })
                    .then(res => {

                        if (!res || !res.success || !res.data) {
                            return;
                        }

                        openViewModal(res.data);

                    })
                    .catch(err => {
                        console.error('AUTO OPEN ERROR:', err);
                    });

            }, 800);
        });

        function openEditModal(data) {


            document.getElementById('editVoucherTaxiModal').classList.remove('hidden');

            // fill fields
            document.getElementById('edit_docid').value = data.docid;
            document.getElementById('edit_cpny_id').value = data.cpny_id;
            document.getElementById('edit_department_id').value = data.department_id;
            document.getElementById('edit_user_peminta').value = data.user_peminta;
            document.getElementById('edit_date_used').value = data.date_used;
            document.getElementById('edit_origin').value = data.origin || '';
            document.getElementById('edit_destination').value = data.destination || '';
            document.getElementById('edit_purpose').value = data.purpose;
            document.getElementById('edit_cpny_id_expense').value = data.cpny_id_expense;
            document.getElementById('edit_user_topup').value = data.user_topup;
            // 🔥 SET TRIP TYPE FROM DB
            document.querySelectorAll('#editVoucherTaxiForm input[name="type_trip"]')
                .forEach(radio => {
                    radio.checked = radio.value === data.type_trip;
                });
            document.getElementById('editMetaUser').innerText = data.created_by || '';
            document.getElementById('editMetaDate').innerText = data.date_used || '';

            // 🔥 APPLY SMART LOGIC
            applyEditState(data);
            setStatusBadge(data.status);

            const reviseBox = document.getElementById('editReviseReasonWrapper');

            if (
                (data.status === 'D' || data.status === 'R') &&
                data.revise_reason
            ) {

                reviseBox.classList.remove('hidden');

                document.getElementById('edit_revise_reason').innerText =
                    data.revise_reason;

            } else {

                reviseBox.classList.add('hidden');
            }
        }

        function openViewModal(data) {

            if (data.eid) {

                history.replaceState(
                    null,
                    '',
                    `/showvouchertaxi/${data.eid}`
                );
            }

            window.currentDocid = data.docid;
            window.currentEid = data.eid;
            window.currentVoucherData = data;

            document.getElementById('printVoucherBtn').href = `/vouchertaxi/print/${data.eid}`;


            document.getElementById('viewVoucherModal').classList.remove('hidden');

            document.getElementById('view_user').innerText = data.user_peminta;
            document.getElementById('view_date').innerText = data.date_used;

            document.getElementById('view_origin').innerText = data.origin || '-';
            document.getElementById('view_destination').innerText = data.destination || '-';

            document.getElementById('view_route').innerText =
                `${data.origin || '-'} → ${data.destination || '-'}`;

            // 🔥 ADD THESE (YOU MISSED)
            document.getElementById('view_type_trip').innerText = data.type_trip || '-';
            document.getElementById('view_cpny').innerText = data.cpny_id || '-';
            document.getElementById('view_dept').innerText = data.department_id || '-';

            document.getElementById('view_purpose').innerText = data.purpose || '-';

            const actualWrapper = document.getElementById('actualExpenseWrapper');

            if (data.actual_budget) {

                actualWrapper.classList.remove('hidden');

                document.getElementById('view_actual_budget').innerText =
                    `Rp ${Number(data.actual_budget).toLocaleString('id-ID')}`;

            } else {

                actualWrapper.classList.add('hidden');
            }

            // STATUS BADGE
            const map = {
                // A:['Approved', 'bg-emerald-100 text-emerald-600'],
                P: ['Pending', 'bg-blue-100 text-blue-600'],
                C: ['Completed', 'bg-green-100 text-green-600'],
                R: ['Rejected', 'bg-gray-200 text-gray-600'],
                D: ['Revise', 'bg-yellow-100 text-yellow-600'],
                X: ['Cancelled', 'bg-gray-100 text-gray-600']
            };

            const [label, style] = map[data.status] || ['Unknown', 'bg-gray-100'];

            const el = document.getElementById('view_status_badge');
            el.innerText = label;
            el.className = `
                inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium
                ${style}
            `;

            // ================= EDIT BUTTON =================

            const editBtn = document.getElementById('openEditFromViewBtn');
            const cancelBtn = document.getElementById('cancelVoucherBtn');
            cancelBtn.classList.add('hidden');

            const currentUser = window.currentUsername;

            const isOwner = String(data.created_by || '')
                .trim()
                .toLowerCase() ===
                String(currentUser || '')
                .trim()
                .toLowerCase();

            // reset
            editBtn.classList.add('hidden');

            // ONLY OWNER + REVISE STATUS
            // EDIT ONLY REVISE
            if (isOwner && data.status === 'D') {

                editBtn.classList.remove('hidden');

                editBtn.onclick = () => {

                    closeViewModal();
                    openEditModal(data);

                };
            }

            // CANCEL ALLOWED FOR PENDING + REVISE
            if (isOwner && data.status === 'D') {

                cancelBtn.classList.remove('hidden');

                cancelBtn.onclick = async () => {

                    const result = await showConfirm(
                        'Cancel this voucher request?'
                    );

                    if (!result.isConfirmed) return;

                    fetch(`/vouchertaxi/cancel/${data.docid}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document
                                    .querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(res => {

                            if (!res.success) {
                                showError(res.message || 'Failed cancel request');
                                return;
                            }

                            showSuccess(res.message || 'Voucher cancelled');

                            closeViewModal();

                            loadVoucherList();
                            calendar.refetchEvents();

                        })
                        .catch(() => {
                            showError('Failed cancel request');
                        });
                };
            }

            loadApproval(data.eid);

            // ACTION BUTTON
            // const isGA = window.isGA === true;
            // const actionDiv = document.getElementById('viewActions');

            // if (actionDiv) {

            //     actionDiv.innerHTML = '';

            //     if (isGA && data.status === 'P') {
            //         actionDiv.innerHTML = `
        //             <button onclick="processVoucher('${data.docid}')"
        //                 class="px-3 py-1 bg-black text-white rounded-md text-xs">
        //                 Process
        //             </button>
        //         `;
            //     }
            // }

            const reviseWrapper = document.getElementById('reviseReasonWrapper');
            if (
                (data.status === 'D' || data.status === 'R') &&
                data.revise_reason
            ) {

                reviseWrapper.classList.remove('hidden');

                document.getElementById('view_revise_reason').innerText =
                    data.revise_reason;

            } else {

                reviseWrapper.classList.add('hidden');
            }
        }

        function closeViewModal() {

            document.getElementById('viewVoucherModal')
                .classList.add('hidden');

            // clear hash
            history.replaceState(
                null,
                '',
                window.location.pathname
            );
        }

        function loadApproval(eid) {

            const el = document.getElementById('approvalFlow');
            const actionBox = document.getElementById('approvalActions');

            actionBox.classList.add('hidden'); // always reset

            if (!eid) {
                el.innerHTML = `<div class="text-xs text-red-500">Invalid approval reference</div>`;
                return;
            }

            el.innerHTML = `<div class="text-xs text-gray-400">Loading approval...</div>`;

            fetch(`/vouchertaxi/tracking/${eid}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(async (res) => {

                    const text = await res.text();

                    // // console.log('TRACKING RAW:', text);

                    try {
                        return JSON.parse(text);

                    } catch (e) {

                        console.error('Tracking invalid JSON');
                        console.error(text);

                        throw new Error('Tracking endpoint returned HTML');
                    }
                })
                .then(res => {

                    el.innerHTML = '';

                    if (!res.steps || res.steps.length === 0) {

                        el.innerHTML = `
                        <div class="text-xs text-gray-400">
                            No approval data
                        </div>
                    `;

                        return;
                    }

                    let currentStep = null;

                    res.steps.forEach(step => {

                        if (step.status === 'P' && !currentStep) {
                            currentStep = step;
                        }

                        const isActive = step.status === 'P';
                        const isDone = step.status === 'A';

                        const dotColor = isDone ?
                            'bg-green-500' :
                            isActive ?
                            'bg-blue-500 ring-4 ring-blue-100' :
                            'bg-gray-300';

                        const lineColor = isDone ?
                            'bg-green-400' :
                            'bg-gray-200';

                        el.innerHTML += `
                        <div class="relative pl-6">

                            <div class="absolute left-[7px] top-0 h-full w-[2px] ${lineColor}"></div>

                            <div class="absolute left-0 top-1 w-4 h-4 rounded-full ${dotColor}"></div>

                            <div class="pb-6">

                                <div class="flex justify-between items-center">

                                    <div class="text-sm font-semibold text-gray-900">
                                        ${escapeHtml(step.title)}
                                    </div>

                                    <div class="text-xs px-2 py-0.5 rounded-full
                                        ${
                                            isDone
                                            ? 'bg-green-100 text-green-600'
                                            : isActive
                                            ? 'bg-blue-100 text-blue-600'
                                            : 'bg-gray-100 text-gray-500'
                                        }">

                                        ${escapeHtml(step.status_label)}
                                    </div>

                                </div>

                                ${
                                    step.by
                                    ? `
                                            <div class="text-xs text-gray-400 mt-1">
                                                ${escapeHtml(step.by)}
                                                •
                                                ${escapeHtml(step.at || '')}
                                            </div>
                                        `
                                    : `
                                            <div class="text-xs text-gray-400 mt-1 italic">
                                                Waiting for action
                                            </div>
                                        `
                                }

                                ${
                                    step.comment
                                    ? `
                                            <div class="mt-2 rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2">

                                                <div class="text-[10px] font-semibold uppercase tracking-wide text-yellow-700">
                                                    Reason
                                                </div>

                                                <div class="mt-1 text-xs text-yellow-900">
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

                    if (currentStep) {
                        checkApprovalAccess(currentStep);
                    }

                    const actionDiv = document.getElementById('viewActions');

                    if (actionDiv) {

                        actionDiv.innerHTML = '';

                        const currentUser = String(window.currentUsername || '')
                            .trim()
                            .toLowerCase();

                        const approvers = String(currentStep?.aprv_username || '')
                            .split(',')
                            .map(v => v.trim().toLowerCase());

                        const isCurrentApprover = approvers.includes(currentUser);

                        const isGA = window.isGA === true;

                        // 🔥 ONLY ACTIVE GA APPROVER CAN PROCESS
                        if (
                            isGA &&
                            isCurrentApprover &&
                            window.currentVoucherData?.status === 'C' &&
                            !window.currentVoucherData?.actual_budget
                        ) {

                            actionDiv.innerHTML = `
                            <button id="openProcessBtn"
                                class="rounded-lg bg-black px-4 py-2 text-xs font-semibold text-white hover:bg-gray-800">
                                Process
                            </button>
                        `;

                            document.getElementById('openProcessBtn')
                                ?.addEventListener('click', () => {

                                    processVoucher(window.currentVoucherData);
                                });
                        }
                    }


                })
                .catch(err => {

                    console.error(err);

                    el.innerHTML = `
                    <div class="text-xs text-red-500">
                        Failed to load approval
                    </div>
                `;
                });
        }

        function checkApprovalAccess(step) {

            const actionBox = document.getElementById('approvalActions');

            if (!actionBox) {
                console.error('approvalActions element not found');
                return;
            }

            // reset first
            actionBox.classList.add('hidden');

            // normalize current user
            const currentUser = String(window.currentUsername || '')
                .replace(/\s+/g, '')
                .trim()
                .toLowerCase();

            // validation
            if (!step || !step.aprv_username) {
                console.warn('Missing approval username:', step);
                return;
            }

            // normalize approvers
            const approvers = String(step.aprv_username)
                .split(',')
                .map(user =>
                    user
                    .replace(/\s+/g, '')
                    .trim()
                    .toLowerCase()
                )
                .filter(Boolean);

            // // console.log('================ APPROVAL ACCESS ================');
            // // console.log('CURRENT USER:', currentUser);
            // // console.log('APPROVERS:', approvers);

            const hasAccess = approvers.includes(currentUser);

            // // console.log('HAS ACCESS:', hasAccess);

            if (hasAccess) {
                actionBox.classList.remove('hidden');
            }
        }

        function clearSelectedCell() {
            if (selectedCell) {
                selectedCell.style.background = '';
                selectedCell.style.border = '';
                selectedCell = null;
            }
        }

        function highlightRow(selected) {

            document.querySelectorAll('.voucher-item')
                .forEach(el => el.classList.remove('bg-indigo-50'));

            selected.classList.add('bg-indigo-50');
        }


        // async function processVoucher(docid) {

        //     const result = await showConfirm('Process this voucher?');

        //     if (!result.isConfirmed) return;

        //     const btns = document.querySelectorAll('.process-btn');
        //     btns.forEach(b => b.disabled = true);

        //     fetch(`/vouchertaxi/process/${docid}`, {
        //         method: 'POST',
        //         headers: {
        //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        //             'Content-Type': 'application/json'
        //         }
        //     })
        //     .then(res => res.json())
        //     .then(() => {

        //         showSuccess('Voucher processed');

        //         loadVoucherList();
        //         calendar.refetchEvents();

        //     })
        //     .catch(() => {
        //         showError('Failed');
        //     });
        // }

        function processVoucher(data) {

            const modal = document.getElementById('processVoucherModal');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.getElementById('processVoucherForm').reset();

            document.getElementById('process_docid').value = data.docid;

            // FILL DATA
            document.getElementById('process_docno').innerText =
                data.docid || '-';

            document.getElementById('process_requester').innerText =
                data.user_peminta || '-';

            document.getElementById('process_date').innerText =
                data.date_used || '-';

            document.getElementById('process_company').innerText =
                data.cpny_id || '-';

            document.getElementById('process_department').innerText =
                data.department_id || '-';

            document.getElementById('process_trip').innerText =
                data.type_trip || '-';

            document.getElementById('process_route').innerText =
                `${data.origin || '-'} → ${data.destination || '-'}`;

            document.getElementById('process_purpose').innerText =
                data.purpose || '-';

            document.getElementById('process_budget').innerText =
                data.max_budget ?
                `Rp ${Number(data.max_budget).toLocaleString()}` :
                '-';
        }

        function applyEditState(data) {

            const status = data.status;
            const currentUser = window.currentUsername; // set this globally
            // const isOwner = data.created_by === currentUser;
            const isOwner =
                String(data.created_by || '')
                .trim()
                .toLowerCase() ===
                String(currentUser || '')
                .trim()
                .toLowerCase();
            const isGA = window.isGA === true;

            const form = document.getElementById('editVoucherTaxiForm');

            const inputs = form.querySelectorAll('input, select');

            // reset first
            inputs.forEach(el => {
                el.disabled = false;
                el.classList.remove('bg-gray-100', 'cursor-not-allowed');
            });

            // 🔒 COMPLETED / REJECTED → FULL LOCK
            if (status === 'C' || status === 'R') {

                inputs.forEach(el => {
                    el.disabled = true;
                    el.classList.add('bg-gray-100', 'cursor-not-allowed');
                });

                hideSubmit();
                return;
            }

            // 🔐 NOT OWNER → LOCK
            if (!isOwner && !isGA) {

                inputs.forEach(el => {
                    el.disabled = true;
                    el.classList.add('bg-gray-100', 'cursor-not-allowed');
                });

                hideSubmit();
                return;
            }

            // 🧾 GA SPECIAL CONTROL
            if (isGA) {
                // later: allow GA-only fields
                // example:
                // enableField('max_budget');
            }

            showSubmit();
        }

        function hideSubmit() {
            document.getElementById('saveEditVoucherTaxiBtn').classList.add('hidden');
        }

        function showSubmit() {
            document.getElementById('saveEditVoucherTaxiBtn').classList.remove('hidden');
        }

        function setStatusBadge(status) {

            const el = document.getElementById('editStatusBadge');

            const map = {
                P: ['Pending', 'text-blue-600'],
                C: ['Completed', 'text-green-600'],
                R: ['Rejected', 'text-gray-500'],
                D: ['Revise', 'text-yellow-500'],
                X: ['Cancelled', 'text-gray-500']
            };

            const [label, color] = map[status] || ['Unknown', 'text-gray-400'];

            el.innerHTML = label;
            el.className = `text-xs mt-2 font-medium ${color}`;
        }

        function loadVoucherList() {

            fetch('/vouchertaxi/json')
                .then(res => res.json())
                .then(res => {

                    voucherData = res.data.sort((a, b) =>
                        new Date(b.date_used) - new Date(a.date_used)
                    );

                    renderVoucherList();
                });
        }

        function renderVoucherList() {

            const tbody = document.getElementById('voucherListBody');
            const counter = document.getElementById('voucherCount');

            tbody.innerHTML = '';

            const isGA = window.isGA === true;

            // =========================================
            // FILTER
            // =========================================

            let filteredData = voucherData;

            // 🔥 WAITING PROCESS
            if (currentVoucherFilter === 'WAITING_PROCESS') {

                filteredData = voucherData.filter(v =>
                    v.status === 'C' &&
                    !v.actual_budget
                );

            }

            // 🔥 NORMAL FILTER
            else if (currentVoucherFilter !== 'ALL') {

                filteredData = voucherData.filter(v =>
                    v.status === currentVoucherFilter
                );
            }

            counter.innerText = filteredData.length;

            // =========================================
            // EMPTY STATE
            // =========================================

            if (
                currentVoucherFilter === 'WAITING_PROCESS' &&
                filteredData.length === 0
            ) {

                tbody.innerHTML = `

                    <div class="
                        flex flex-col items-center justify-center
                        rounded-2xl border border-dashed border-gray-200
                        py-16 px-6 text-center
                        dark:border-white/10
                    ">

                        <div class="
                            mb-4 flex h-16 w-16 items-center justify-center
                            rounded-full bg-emerald-100 text-3xl
                            dark:bg-emerald-500/15
                        ">
                            ✅
                        </div>

                        <div class="
                            text-base font-semibold text-gray-900
                            dark:text-white
                        ">
                            Nothing is waiting for you
                        </div>

                        <div class="
                            mt-2 text-sm text-gray-500
                            dark:text-gray-400
                        ">
                            Job is done 🎉
                        </div>

                    </div>
                `;

                document.getElementById('voucherPageInfo').innerText =
                    'No pending process';

                document.getElementById('prevVoucherPage').disabled = true;

                document.getElementById('nextVoucherPage').disabled = true;

                return;
            }

            // =========================================
            // PAGINATION
            // =========================================

            const totalPages = Math.ceil(filteredData.length / voucherPerPage);

            if (currentVoucherPage > totalPages) {
                currentVoucherPage = 1;
            }

            const start = (currentVoucherPage - 1) * voucherPerPage;
            const end = start + voucherPerPage;

            const paginatedData = filteredData.slice(start, end);

            document.getElementById('voucherPageInfo').innerText =
                filteredData.length === 0 ?
                'No data' :
                `Showing ${start + 1}-${Math.min(end, filteredData.length)} of ${filteredData.length}`;

            // =========================================
            // STATUS MAP
            // =========================================

            const statusMap = {

                P: {
                    label: 'Pending',
                    color: `
                        bg-blue-100 text-blue-700
                        dark:bg-blue-500/15 dark:text-blue-300
                    `
                },

                C: {
                    label: 'Completed',
                    color: `
                        bg-emerald-100 text-emerald-700
                        dark:bg-emerald-500/15 dark:text-emerald-300
                    `
                },

                D: {
                    label: 'Revise',
                    color: `
                        bg-yellow-100 text-yellow-700
                        dark:bg-yellow-500/15 dark:text-yellow-300
                    `
                },

                R: {
                    label: 'Rejected',
                    color: `
                        bg-red-100 text-red-700
                        dark:bg-red-500/15 dark:text-red-300
                    `
                },

                X: {
                    label: 'Cancelled',
                    color: `
                        bg-gray-100 text-gray-600
                        dark:bg-gray-500/15 dark:text-gray-300
                    `
                }
            };

            // =========================================
            // RENDER LIST
            // =========================================

            paginatedData.forEach(item => {

                const status = statusMap[item.status] || {
                    label: '-',
                    color: 'bg-gray-100 text-gray-400'
                };

                const row = document.createElement('div');

                row.className = `
                    voucher-item
                    group
                    rounded-2xl
                    border border-gray-200
                    bg-white
                    px-4 py-3
                    cursor-pointer
                    transition-all duration-200

                    hover:-translate-y-[1px]
                    hover:border-gray-300
                    hover:shadow-md

                    dark:border-white/10
                    dark:bg-white/[0.03]
                    dark:hover:border-white/20
                    dark:hover:bg-white/[0.05]
                `;

                row.innerHTML = `

                    <div class="flex items-start justify-between gap-3">

                        <!-- LEFT -->
                        <div class="min-w-0 flex-1">

                            <!-- TOP -->
                            <div class="flex items-center gap-2 text-[11px] text-gray-400">

                                <span class="font-semibold tracking-wide">
                                    ${escapeHtml(item.docid)}
                                </span>

                                <span>•</span>

                                <span>
                                    ${escapeHtml(item.date_used)}
                                </span>

                            </div>

                            <!-- USER -->
                            <div class="mt-1 truncate text-sm font-semibold text-gray-900 dark:text-gray-100">
                                ${escapeHtml(item.user_peminta)}
                            </div>

                            <!-- ROUTE -->
                            <div class="mt-2 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">

                                <span class="text-[11px] opacity-70">📍</span>

                                <div class="truncate">
                                    ${escapeHtml(item.origin || '-')}
                                    →
                                    ${escapeHtml(item.destination || '-')}
                                </div>

                            </div>

                            <!-- PURPOSE -->
                            <div class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                ${escapeHtml(item.purpose || '-')}
                            </div>

                        </div>

                        <!-- STATUS -->
                        <div class="flex flex-col items-end gap-2 shrink-0">

                            <!-- MAIN STATUS -->
                            <span class="
                                rounded-full
                                px-2.5 py-1
                                text-[10px]
                                font-semibold
                                ${status.color}
                            ">
                                ${escapeHtml(status.label)}
                            </span>

                            ${
                                item.status === 'C' && item.actual_budget
                                ? `
                                    <span class="
                                        rounded-full
                                        bg-emerald-100
                                        px-2.5 py-1
                                        text-[10px]
                                        font-semibold
                                        text-emerald-700
                                    ">
                                        Processed
                                    </span>
                                `
                                : ''
                            }

                        </div>

                    </div>

                    ${
                        isGA &&
                        item.status === 'C' &&
                        !item.actual_budget
                        ? `
                                <div class="mt-3 flex justify-end">

                                    <button
                                        class="
                                            process-btn
                                            rounded-lg
                                            bg-black
                                            px-3 py-1.5
                                            text-[11px]
                                            font-medium
                                            text-white
                                            transition
                                            hover:opacity-90
                                        ">
                                        Process
                                    </button>

                                </div>
                            `
                        : ''
                    }
                `;

                // CLICK ROW
                row.addEventListener('click', () => {

                    highlightRow(row);

                    calendar.gotoDate(item.date_used);

                    openViewModal(item);
                });

                // PROCESS BUTTON
                const btn = row.querySelector('.process-btn');

                if (btn) {

                    btn.addEventListener('click', (e) => {

                        e.stopPropagation();

                        processVoucher(item);
                    });
                }

                tbody.appendChild(row);
            });

            // =========================================
            // PAGINATION BUTTON
            // =========================================

            document.getElementById('prevVoucherPage').disabled =
                currentVoucherPage === 1;

            document.getElementById('nextVoucherPage').disabled =
                currentVoucherPage >= totalPages;
        }

        function toast(msg) {
            const el = document.createElement('div');
            el.className = 'fixed bottom-4 right-4 bg-black text-white px-4 py-2 rounded-lg text-sm shadow';
            el.innerText = msg;

            document.body.appendChild(el);

            setTimeout(() => el.remove(), 3000);
        }

        const panel = document.getElementById('voucherListPanel');
        const calendarWrapper = document.getElementById('calendarWrapper');
        const grid = document.getElementById('mainGrid');
        const toggleBtn = document.getElementById('toggleList');

        let hidden = false;

        toggleBtn.addEventListener('click', () => {

            hidden = !hidden;

            if (hidden) {
                panel.classList.add('hidden');
                grid.classList.remove('lg:grid-cols-3');
                grid.classList.add('lg:grid-cols-1');
            } else {
                panel.classList.remove('hidden');
                grid.classList.remove('lg:grid-cols-1');
                grid.classList.add('lg:grid-cols-3');
            }

            setTimeout(() => {
                calendar.updateSize();
            }, 300);
        });

        document.getElementById('closeCreateVoucherModal')
            .addEventListener('click', () => {
                document.getElementById('createVoucherModal').classList.add('hidden');
                clearSelectedCell(); // 🔥 important
            });

        document.getElementById('cancelEditVoucherTaxiBtn')
            .addEventListener('click', () => {
                document.getElementById('editVoucherTaxiModal').classList.add('hidden');
                document.getElementById('editVoucherTaxiForm').reset();
            });

        window.addEventListener('click', function(e) {

            const createModal = document.getElementById('createVoucherModal');
            const editModal = document.getElementById('editVoucherTaxiModal');

            const viewModal = document.getElementById('viewVoucherModal');

            if (e.target === viewModal) {
                closeViewModal();
            }

            if (e.target === createModal) {
                createModal.classList.add('hidden');
                clearSelectedCell();
            }

            if (e.target === editModal) {

                editModal.classList.add('hidden');

                document.getElementById('editVoucherTaxiForm').reset();
            }

        });

        document.getElementById('openCreateVoucherModal')
            .addEventListener('click', () => {

                document.getElementById('voucherTaxiForm').reset();

                document.getElementById('createVoucherModal')
                    .classList.remove('hidden');
            });

        document.addEventListener('keydown', function(e) {

            if (e.key === 'Escape') {

                document.getElementById('createVoucherModal').classList.add('hidden');
                document.getElementById('editVoucherTaxiModal').classList.add('hidden');

                closeViewModal();
                clearSelectedCell();
            }

        });


        const deptSelect = document.getElementById('department_id');
        const userSelect = document.getElementById('user_topup');

        const allOptions = Array.from(userSelect.options);

        function filterUsers() {

            const selectedDept = deptSelect.value;

            userSelect.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.text = 'Select user';
            userSelect.appendChild(defaultOption);

            allOptions.forEach(option => {

                const dept = option.getAttribute('data-dept');

                if (!option.value) return;

                if (!selectedDept || dept === selectedDept) {
                    userSelect.appendChild(option.cloneNode(true));
                }
            });
        }

        // ✅ ONLY department triggers filter
        deptSelect.addEventListener('change', filterUsers);

        const createForm = document.getElementById('voucherTaxiForm');

        if (createForm) {
            createForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const btn = this.querySelector('button[type="submit"]');

                // 🔥 LOADING START
                btn.disabled = true;
                const originalText = btn.innerHTML;
                btn.innerHTML = `
                        <span class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="white" stroke-width="4" fill="none"/>
                            </svg>
                            Submitting...
                        </span>
                    `;

                const confirm = await showConfirm(
                    'Submit this taxi booking request?'
                );

                if (!confirm.isConfirmed) {

                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    return;
                }

                const formData = new FormData(this);

                fetch('/vouchertaxi/store', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {

                        if (!res.success) {
                            showError(res.message);

                            // 🔥 RESET BUTTON
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            return;
                        }

                        this.reset();
                        clearSelectedCell();

                        document.getElementById('createVoucherModal').classList.add('hidden');

                        loadVoucherList();
                        calendar.refetchEvents();

                        // 🔥 RESET BUTTON
                        btn.disabled = false;
                        btn.innerHTML = originalText;

                    })
                    .catch(err => {
                        console.error(err);
                        showError('Create failed');

                        // 🔥 RESET BUTTON
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
            });
        }

        document.getElementById('editVoucherTaxiForm')
            .addEventListener('submit', async function(e) {

                e.preventDefault();

                const btn = document.getElementById('saveEditVoucherTaxiBtn');

                btn.disabled = true;

                const originalText = btn.innerHTML;
                btn.innerHTML = 'Saving...';

                try {

                    const docid = document.getElementById('edit_docid').value;

                    const formData = new FormData(this);

                    const response = await fetch(`/vouchertaxi/update/${docid}`, {

                        // ✅ FIX
                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .content,

                            'Accept': 'application/json'
                        },

                        body: formData
                    });

                    const raw = await response.text();

                    // console.log('UPDATE RAW RESPONSE:', raw);

                    let res;

                    try {

                        res = JSON.parse(raw);

                    } catch (jsonError) {

                        console.error('INVALID JSON RESPONSE:', raw);

                        throw new Error('Server returned HTML instead of JSON');
                    }

                    if (!response.ok || !res.success) {

                        showError(res.message || 'Update failed');

                        btn.disabled = false;
                        btn.innerHTML = originalText;

                        return;
                    }

                    document.getElementById('editVoucherTaxiModal')
                        .classList.add('hidden');

                    loadVoucherList();

                    if (typeof calendar !== 'undefined') {
                        calendar.refetchEvents();
                    }

                    showSuccess(res.message || 'Voucher updated successfully');

                } catch (err) {

                    console.error('UPDATE ERROR:', err);

                    showError(err.message || 'Update failed');

                } finally {

                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        document.getElementById('approveBtn').onclick = () => handleApproval('approve');
        document.getElementById('rejectBtn').onclick = () => handleApproval('reject');
        document.getElementById('reviseBtn').onclick = () => handleApproval('revise');

        function handleApproval(type) {

            const docid = window.currentDocid;

            if (!docid) {
                showError('Invalid document');
                return;
            }

            // ✅ APPROVE DIRECTLY
            if (type === 'approve') {
                submitApproval(type, '');
                return;
            }

            // 🔥 OPEN MODAL
            const modal = document.getElementById('reasonModal');
            const input = document.getElementById('reasonInput');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            input.value = '';

            // title
            document.getElementById('reasonModalTitle').innerText =
                type === 'revise' ?
                'Revision Reason' :
                'Reject Reason';

            // cancel
            document.getElementById('cancelReasonBtn').onclick = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            // submit
            document.getElementById('submitReasonBtn').onclick = () => {

                const reason = input.value.trim();

                if (!reason) {
                    showError('Reason is required');
                    return;
                }

                modal.classList.add('hidden');
                modal.classList.remove('flex');

                submitApproval(type, reason);
            };
        }

        function submitApproval(type, comment = '') {

            const docid = window.currentDocid;

            const urlMap = {
                approve: `/vouchertaxi/approve/${docid}`,
                reject: `/vouchertaxi/reject/${docid}`,
                revise: `/vouchertaxi/revise/${docid}`
            };

            const btn = document.getElementById(type + 'Btn');

            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = 'Processing...';

            fetch(urlMap[type], {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        comment: comment
                    })
                })
                .then(res => res.json())
                .then(res => {

                    if (!res.success) {
                        showError(res.message || 'Failed');
                        return;
                    }

                    showSuccess(res.message || 'Success');

                    closeViewModal();

                    loadVoucherList();
                    calendar.refetchEvents();

                })
                .catch(err => {
                    console.error(err);
                    showError('Error processing approval');
                })
                .finally(() => {

                    btn.disabled = false;
                    btn.innerHTML = originalText;

                });
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message
            });
        }

        function showConfirm(message) {
            return Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            });
        }

        // CLOSE PROCESS MODAL
        document.getElementById('closeProcessVoucherModal')
            .addEventListener('click', () => {

                const modal = document.getElementById('processVoucherModal');

                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });

        // SUBMIT PROCESS
        document.getElementById('processVoucherForm')
            .addEventListener('submit', async function(e) {

                e.preventDefault();

                const confirm = await Swal.fire({
                    title: 'Process Voucher?',
                    text: "This process can't be undone after submitting the actual budget.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Process',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    confirmButtonColor: '#111827',
                    cancelButtonColor: '#d1d5db'
                });

                if (!confirm.isConfirmed) {
                    return;
                }

                const docid = document.getElementById('process_docid').value;

                const btn = document.getElementById('submitProcessVoucherBtn');

                const originalText = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = 'Processing...';

                try {

                    const formData = new FormData(this);

                    const response = await fetch(`/vouchertaxi/process/${docid}`, {

                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .content,

                            'Accept': 'application/json'
                        },

                        body: formData
                    });

                    const res = await response.json();

                    if (!res.success) {

                        showError(res.message || 'Failed process voucher');

                        return;
                    }

                    showSuccess(res.message || 'Voucher processed');

                    document.getElementById('processVoucherModal')
                        .classList.add('hidden');

                    document.getElementById('processVoucherModal')
                        .classList.remove('flex');

                    loadVoucherList();

                    if (calendar) {
                        calendar.refetchEvents();
                    }

                    closeViewModal();

                } catch (err) {

                    console.error(err);

                    showError('Failed process voucher');

                } finally {

                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });

        const budgetDisplay = document.getElementById('actual_budget_display');
        const budgetHidden = document.getElementById('actual_budget');

        budgetDisplay?.addEventListener('input', function() {

            let value = this.value.replace(/\D/g, '');

            budgetHidden.value = value;

            this.value = value ?
                Number(value).toLocaleString('id-ID') :
                '';
        });

        document.querySelectorAll('.voucher-filter')
            .forEach(btn => {

                btn.addEventListener('click', function() {

                    document.querySelectorAll('.voucher-filter')
                        .forEach(b => b.classList.remove('active-filter'));

                    this.classList.add('active-filter');

                    currentVoucherFilter = this.dataset.filter;

                    currentVoucherPage = 1;

                    renderVoucherList();
                });
            });

        document.getElementById('prevVoucherPage')
            .addEventListener('click', () => {

                if (currentVoucherPage > 1) {

                    currentVoucherPage--;

                    renderVoucherList();
                }
            });

        document.getElementById('nextVoucherPage')
            .addEventListener('click', () => {

                const totalPages = Math.ceil(
                    voucherData.length / voucherPerPage
                );

                if (currentVoucherPage < totalPages) {

                    currentVoucherPage++;

                    renderVoucherList();
                }
            });
    </script>
</x-app-layout>
