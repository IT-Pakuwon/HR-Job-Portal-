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
                    class="ticket-category-tab active-category-tab inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:border-slate-700 dark:bg-slate-700"
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

            {{-- Listing --}}
            <div id="ticketListWrapper" class="divide-y divide-slate-100 dark:divide-slate-800">

            </div>

        </div>

        {{-- CREATE MODAL --}}
        <div id="createTicketModal" class="fixed inset-0 z-[70] hidden bg-slate-900/60 backdrop-blur-sm">

        <div class="flex items-center justify-center min-h-screen p-2">

            <div class="w-full max-w-4xl h-[90vh] flex flex-col rounded-3xl
                        bg-white dark:bg-slate-900
                        border border-slate-200 dark:border-slate-700
                        shadow-2xl">

                <div class="shrink-0 flex items-center justify-between border-b border-slate-200 dark:border-slate-700 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Create Ticket</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Submit new support ticket request.</p>
                    </div>
                    <button type="button" id="closeCreateTicketModal"
                        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white">✕</button>
                </div>

                    {{-- Body --}}
                    <form id="formCreateTicket" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">

                        @csrf

                        <div class="flex-1 overflow-y-auto p-2">
                            <div class="grid grid-cols-1 gap-5 p-2 md:grid-cols-2">

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
                                            class="w-full rounded-lg border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                                        <input type="hidden" name="cpny_id"
                                            value="{{ $userCompanies->first()->cpny_id }}">
                                    @else
                                        <select name="cpny_id" id="cpny_id" required
                                            class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

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
                                <div >
                                    <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Department
                                    </label>

                                    @if ($userDepartments->count() === 1)

                                        <input type="text" value="{{ $userDepartments->first()->department_id }}"
                                            readonly
                                            class="w-full rounded-lg border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                                        <input type="hidden" name="department_id" id="department_id"
                                            value="{{ $userDepartments->first()->department_id }}">
                                    @else
                                        <select name="department_id" id="department_id" required
                                            class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

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
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
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
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
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
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
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
                                   <select name="ticket_priority" id="ticket_priority"></select>
                                    {{-- <input type="hidden" name="ticket_priority" id="ticket_priority_hidden"> --}}
                                </div>

                                {{-- Location --}}
                                <div>
                                    <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Location
                                    </label>

                                    <select name="location_id" id="location_id"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
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
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        required>

                                        <option value="">
                                            Select Sub Location
                                        </option>

                                    </select>
                                </div>

                                {{-- Assign PIC --}}
                                <div class="md:col-span-2">

                                    <div class="flex items-center gap-3">

                                        <input type="checkbox" id="assign_pic_checkbox"
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                                        <label for="assign_pic_checkbox"
                                            class="text-sm font-medium text-slate-700 dark:text-slate-300">

                                            Assign Specific PIC
                                        </label>

                                    </div>

                                </div>

                                {{-- PIC Selection --}}
                                <div id="picSelectionWrapper" class="hidden md:col-span-2">

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        PIC Ticket
                                    </label>

                                    <select name="pic_ticket" id="pic_ticket">
                                        <option value="">Select PIC</option>
                                    </select>

                                </div>

                                {{-- Summary --}}
                                <div class="md:col-span-2">
                                    <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Issue Summary
                                    </label>

                                    <input type="text" name="issue_summary" maxlength="255"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        required>
                                </div>

                                {{-- Description --}}
                                <div class="md:col-span-2">
                                    <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Issue Description
                                    </label>

                                    <textarea name="issue_descr" rows="5"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        required></textarea>
                                </div>

                                {{-- Attachment --}}
                                <div class="md:col-span-2">

                                    <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Attachment
                                    </label>

                                    <label for="attachments"
                                        class="flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center transition hover:border-blue-400 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-blue-500 dark:hover:bg-slate-700/50">

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
                        </div>

                        {{-- Footer --}}
                    <div class="shrink-0 flex justify-end gap-3 border-t border-slate-200 dark:border-slate-700 px-6 py-4">
                        <button type="button" id="cancelCreateTicket"
                            class="rounded-lg border border-slate-200 px-5 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                            Cancel
                        </button>
                        <button type="submit" id="submitCreateTicket"
                            class="rounded-lg bg-blue-600 px-5 py-2 text-sm text-white hover:bg-blue-500">
                            Submit Ticket
                        </button>
                    </div>

                    </form>

                </div>

            </div>

        </div>

        {{-- DETAIL MODAL --}}
        <div id="detailTicketModal"
            class="modal-scroll fixed inset-0 z-[80] hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm">

            <div class="flex min-h-screen items-start justify-center px-3 py-4 lg:px-6 lg:py-6">

                <div
                    class="max-w-9xl flex h-[96vh] w-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">

                    {{-- LEFT --}}
                    <div class="flex min-w-0 flex-1 flex-col">

                        {{-- Header --}}
                        <div
                            class="sticky top-0 z-10 border-b border-slate-200 bg-white/90 px-6 py-5 backdrop-blur dark:border-slate-700 dark:bg-slate-900/90">

                            <div class="flex items-start justify-between gap-4">

                                <div class="min-w-0 flex-1">

                                    <div class="flex flex-wrap items-center gap-2">

                                        <h2 id="detail_ticketid"
                                            class="truncate text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                                            -
                                        </h2>

                                        <span id="detail_priority"
                                            class="rounded-lg px-2 py-1 text-[11px] font-semibold">
                                            -
                                        </span>

                                        <span id="detail_status"
                                            class="rounded-lg px-2 py-1 text-[11px] font-semibold">
                                            -
                                        </span>

                                    </div>

                                    <p id="detail_type"
                                        class="mt-1 text-sm font-medium text-slate-800 dark:text-white">
                                        -
                                    </p>

                                </div>

                                <div class="flex items-center gap-2">

                                    <!-- PRIMARY ACTION -->
                                    <button id="primaryActionBtn"
                                        class="hidden inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-500">
                                        ⚡ Process Ticket
                                    </button>

                                    <!-- SECONDARY -->
                                    <div class="relative">
                                        <button id="btnTicketActions"
                                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                                            Actions

                                            <svg id="actionArrow"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="w-4 h-4 transition-transform duration-200"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2">

                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                                            </svg>

                                        </button>

                                        <div id="ticketActionDropdown"
                                            class="hidden absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
                                            <div class="py-2" id="ticketActionList"></div>
                                        </div>
                                    </div>

                                    <!-- CLOSE -->
                                    <button id="closeDetailTicketModal"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                                        Close
                                    </button>

                                </div>

                            </div>

                        </div>

                        {{-- Content --}}
                        <div class="min-h-0 flex-1 overflow-y-auto">

                            <div class="grid grid-cols-1 gap-5 p-5 xl:grid-cols-[1.3fr_0.7fr]">

                                {{-- MAIN --}}
                                <div class="space-y-5">

                                    {{-- Description --}}
                                    <div
                                        class="rounded-lg border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-700 dark:bg-slate-800/40">

                                        <div class="mb-3 flex items-center gap-3">

                                            <div
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-sm dark:bg-slate-700">
                                                📝
                                            </div>

                                            <div class="flex w-full items-center justify-between gap-4">

                                                <h3
                                                    class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                                                    Description
                                                </h3>

                                                <p id="detail_date"
                                                    class="shrink-0 text-xs font-medium text-slate-500 dark:text-slate-400">
                                                    -
                                                </p>

                                            </div>

                                        </div>

                                        <div id="detail_descr"
                                            class="whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300">
                                            -
                                        </div>

                                    </div>

                                    {{-- Information --}}
                                    <div class="rounded-lg border border-slate-200 dark:border-slate-700">

                                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">

                                            <div class="flex items-center justify-between gap-4">

                                                <div class="flex items-center gap-2">

                                                    <div
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-sm dark:bg-slate-800">
                                                        📌
                                                    </div>

                                                    <h3
                                                        class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                                                        Ticket Information
                                                    </h3>

                                                </div>

                                                <div class="flex items-center gap-1.5">

                                                    <p class="text-[11px] uppercase tracking-wide text-slate-400">
                                                        SLA :
                                                    </p>

                                                    <p id="detail_sla"
                                                        class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                        -
                                                    </p>

                                                </div>

                                            </div>

                                        </div>

                                        <div
                                            class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 sm:grid-cols-2 lg:grid-cols-3">

                                            <div>
                                                <p class="text-[11px] uppercase tracking-wide text-slate-400">
                                                    Created By
                                                </p>

                                                <p id="detail_created_by"
                                                    class="mt-1 text-sm font-medium text-slate-800 dark:text-white">
                                                    -
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-[11px] uppercase tracking-wide text-slate-400">
                                                    Category
                                                </p>

                                                <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">
                                                    <span id="detail_category">-</span>

                                                    <span class="mx-1 text-slate-400">
                                                        —
                                                    </span>

                                                    <span id="detail_subcategory">-</span>
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-[11px] uppercase tracking-wide text-slate-400">
                                                    Location
                                                </p>

                                                <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">
                                                    <span id="detail_location">-</span>

                                                    <span class="mx-1 text-slate-400">
                                                        —
                                                    </span>

                                                    <span id="detail_sub_location">-</span>
                                                </p>
                                            </div>

                                        </div>

                                    </div>


                                    {{-- Timeline --}}
                                    <div class="rounded-lg border border-slate-200 dark:border-slate-700">

                                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                                            <div class="flex items-center justify-between gap-4">

                                                <div class="flex items-center gap-2">

                                                    <div
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-sm dark:bg-slate-800">
                                                        🕒
                                                    </div>

                                                    <h3
                                                        class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                                                        Activity Timeline
                                                    </h3>

                                                </div>

                                                <div class="flex items-center gap-2">

                                                    <span class="text-[11px] uppercase tracking-wide text-slate-400">
                                                        PIC
                                                    </span>

                                                    <span id="detail_pic"
                                                        class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                        -
                                                    </span>

                                                </div>

                                            </div>

                                        </div>

                                        <div id="detail_tracking" class="space-y-5 p-5">

                                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                                No activity
                                            </div>

                                        </div>

                                    </div>





                                </div>

                                {{-- SIDEBAR --}}
                                <div class="space-y-5">

                                    {{-- Attachment --}}
                                    <div class="rounded-lg border border-slate-200 dark:border-slate-700">

                                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">

                                            <div class="flex items-center gap-2">

                                                <div
                                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-sm dark:bg-slate-800">
                                                    📎
                                                </div>

                                                <h3
                                                    class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                                                    Attachments
                                                </h3>

                                            </div>

                                        </div>

                                        <div id="detail_attachments" class="space-y-3 p-5">

                                            <div class="text-sm text-slate-500 dark:text-slate-400">
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

        </div>

        <div id="imagePreviewModal"
            class="fixed inset-0 z-[100] hidden bg-black/80 backdrop-blur-sm flex items-center justify-center">

            <div class="relative max-w-5xl w-full px-4">

                <button id="closeImagePreview"
                    class="absolute -top-10 right-0 text-white text-2xl">
                    ✕
                </button>

                <img id="previewImage"
                    class="w-full max-h-[85vh] object-contain rounded-lg shadow-2xl" />

            </div>

        </div>
    </div>

    <div id="transferModal" class="fixed inset-0 z-[90] hidden bg-black/50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md p-5 space-y-4">

            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
                Transfer Ticket
            </h3>

            <select id="transfer_category" class="w-full border rounded-lg px-3 py-2"></select>

            <div class="flex justify-end gap-2">
                <button id="cancelTransfer" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button id="submitTransfer" class="px-4 py-2 bg-amber-600 text-white rounded-lg">
                    Transfer
                </button>
            </div>

        </div>
    </div>

    <div id="assignModal" class="fixed inset-0 z-[90] hidden bg-black/50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md p-5 space-y-4">

            <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
                Assign PIC
            </h3>

           <div class="space-y-3 flex gap-4">

                <!-- CURRENT PIC -->
                <div>
                    <label class="text-xs text-slate-500">Current PIC</label>

                    <div id="current_pic_badge"
                        class="mt-1 px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-sm font-medium text-slate-700 dark:text-white">
                        -
                    </div>
                </div>

                <!-- CHANGE TO -->
                <div>
                    <label class="text-xs text-slate-500">Change To</label>

                    <select id="assign_pic_dropdown" class="w-full"></select>
                </div>

            </div>

            <div class="flex justify-end gap-2">
                <button id="cancelAssign" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button id="submitAssign" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Save
                </button>
            </div>

        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function() {
                // $('#cpny_id, #department_id, #ticket_type, #ticket_categoryid, #ticket_subcategoryid, #ticket_priority, #location_id, #sub_location_id, #pic_ticket')
                // .select2('destroy').select2({
                //     placeholder: 'Select option',
                //     allowClear: true,
                //     width: '100%',
                //     dropdownParent: $('#createTicketModal') // 🔥 IMPORTANT
                // });

                const CURRENT_USER = {
                    username: "{{ auth()->user()->username }}",
                    isIT: {{ auth()->user()->isIT() ? 'true' : 'false' }}
                };

                function initSelect2(el, parent = '#createTicketModal') {
                    let $el = $(el);
                    if (!$el.length) return;

                    let currentVal = $el.val();

                    if ($el.data('select2')) {
                        $el.select2('destroy');
                    }

                    if ($el.is(':hidden')) return;

                    $el.select2({
                        placeholder: 'Select option',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $(parent)
                    });

                    if (currentVal) {
                        $el.val(currentVal).trigger('change');
                    }
                }

            const API = {
                list: "{{ route('ticket.json') }}",
                create: "{{ route('ticket.store') }}",
                category: "{{ route('ticket.categoryByType') }}",
                subcategory: "{{ route('ticket.subcategoryByCategory') }}",
                pic: "{{ url('ticket/pic-by-category') }}",
                priority: "{{ route('ticket.priorityByCategory') }}",
                sublocation: "{{ route('ticket.subLocation') }}",
                detail: (eid) => `{{ url('ticket/detail') }}/${eid}`,
                cancel: (eid) => `{{ url('ticket/cancel') }}/${eid}`,

                // 🔥 ADD THESE
                assign: (eid) => `{{ url('ticket/assign') }}/${eid}`,
                transfer: (eid) => `{{ url('ticket/transfer') }}/${eid}`
            };

            const state = {
                status: '',
                search: '',
                category: ''
            };



            const UI = {
                wrapper: $('#ticketListWrapper'),
                modalCreate: $('#createTicketModal'),
                modalDetail: $('#detailTicketModal')
            };

            // =========================
            // LOADER
            // =========================
            function showLoading() {
                UI.wrapper.html(`
                    <div class="p-10 text-center text-sm text-slate-400">
                        Loading tickets...
                    </div>
                `);
            }


            // =========================
            // LOAD LIST
            // =========================
            function loadTickets() {
                showLoading();

                $.get(API.list, {
                    status: state.status,
                    search: state.search
                })
                .done(res => {

                    // ✅ FIX HERE
                    window.__tickets = res.data;

                    if (!$('#ticketCategoryTabs').data('loaded')) {
                        renderCategoryTabs(res.data);
                        $('#ticketCategoryTabs').data('loaded', true);
                    }

                    renderTickets(res.data);
                })
                .fail(() => {
                    UI.wrapper.html(`<div class="p-10 text-red-500 text-center">Failed load data</div>`);
                });
            }

            function renderCategoryTabs(rows) {

            let container = $('#ticketCategoryTabs');

            let categories = [...new Set(
                rows.map(r => r.ticket_category_name || r.ticket_categoryid)
            )];

            let html = `
                <button type="button"
                    class="ticket-category-tab text-sm active-category-tab bg-slate-900 text-white px-4 py-2 rounded-lg"
                    data-category="">
                    📂 All Category
                </button>
            `;

            categories.forEach(cat => {
                html += `
                    <button type="button"
                        class="ticket-category-tab px-4 py-2 rounded-lg border bg-gray-50 text-sm dark:bg-gray-800"
                        data-category="${cat}">
                        📁 ${cat}
                    </button>
                `;
            });

            container.html(html);
        }

        function openAssignModal(t){

            if (!t) {
                Swal.fire('Error', 'Ticket not found', 'error');
                return;
            }

            window.currentTicket = t;

            $('#assignModal').removeClass('hidden');

            let currentPic = t.pic_ticket || 'Unassigned';

            // 🔥 SET CURRENT PIC BADGE
            $('#current_pic_badge').text(currentPic);

            let $dropdown = $('#assign_pic_dropdown');

            $dropdown.html('<option>Loading...</option>');

            $.get(API.pic, {
                ticket_type: t.ticket_type,
                ticket_categoryid: t.ticket_categoryid,
                department_id: t.department_id
            })
            .done(function(res){

                let opt = '<option value="">Select New PIC</option>';

                res.forEach(u => {

                    // ❌ jangan auto selected
                    opt += `<option value="${u.username}">
                                ${u.name}
                            </option>`;
                });

                if ($dropdown.data('select2')) {
                    $dropdown.select2('destroy');
                }

                $dropdown.html(opt);

                setTimeout(() => {
                    initSelect2('#assign_pic_dropdown', '#assignModal');
                }, 50);

            })
            .fail(function(){
                $dropdown.html('<option value="">Failed load PIC</option>');
            });
        }

        function openTransferModal(t){

            if (!t) {
                Swal.fire('Error', 'Ticket not found', 'error');
                return;
            }

            window.currentTicket = t;

            $('#transferModal').removeClass('hidden');

            let currentCategory = t.ticket_category_name || t.ticket_categoryid;

            let $dropdown = $('#transfer_category');

            // 🔥 HEADER INFO
            let infoHtml = `
                <div class="text-sm text-slate-500 mb-2">
                    Current Category :
                    <span class="font-semibold text-slate-800 dark:text-white">
                        ${currentCategory}
                    </span>
                </div>
            `;

            $('#transferModal .modal-info').remove();
            $('#transferModal .bg-white').prepend(`<div class="modal-info">${infoHtml}</div>`);

            $dropdown.html('<option>Loading...</option>');

            $.get(API.category, { ticket_type: t.ticket_type })
            .done(function(res){

                let opt = '<option value="">Select New Category</option>';

                res.forEach(x => {

                    let selected = (x.ticket_categoryid === t.ticket_categoryid) ? 'selected' : '';

                    opt += `<option value="${x.ticket_categoryid}" ${selected}>
                                ${x.ticket_category_name}
                            </option>`;
                });

                if ($dropdown.data('select2')) {
                    $dropdown.select2('destroy');
                }

                $dropdown.html(opt);

                setTimeout(() => {
                    initSelect2('#transfer_category', '#transferModal');
                }, 50);

            })
            .fail(function(){
                $dropdown.html('<option value="">Failed load category</option>');
            });
        }

        function buildTicketActions(ticket, user){

            let actions = [];

            // =========================
            // ASSIGN PIC
            // =========================
            if (['W'].includes(ticket.status) && user.isIT) {
                actions.push({
                    label: 'Assign PIC',
                    icon: '👤',
                    class: 'text-blue-600',
                    action: () => openAssignModal(ticket)
                });
            }

            if (['W'].includes(ticket.status) && user.isIT) {
                actions.push({
                    label: 'Transfer Ticket',
                    icon: '🔁',
                    class: 'text-amber-600',
                    action: () => openTransferModal(ticket)
                });
            }

            // =========================
            // START / PROCESS
            // =========================
            if (ticket.status === 'W' && ticket.pic_ticket === user.username) {
                actions.push({
                    label: 'Start Work',
                    icon: '🚀',
                    class: 'text-green-600',
                    action: () => startTicket(ticket.eid)
                });
            }

            // =========================
            // CANCEL
            // =========================
            if (ticket.status === 'W' && ticket.created_by === user.username) {
                actions.push({
                    label: 'Cancel Ticket',
                    icon: '❌',
                    class: 'text-red-600',
                    action: () => cancelTicket(ticket.eid)
                });
            }

            return actions;
        }

        function renderTicketActions(ticket){

            // 🔥 WAJIB: sync global state
            window.currentTicket = ticket;

            let actions = buildTicketActions(ticket, CURRENT_USER);

            let html = '';

            if (!actions.length) {
                html = `
                    <div class="px-4 py-2 text-sm text-slate-400">
                        No actions available
                    </div>
                `;
            } else {

                actions.forEach((a, i) => {
                    html += `
                        <button
                            class="ticket-action-item w-full flex items-center gap-3 px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800 ${a.class}"
                            data-index="${i}">
                            <span>${a.icon}</span>
                            <span>${a.label}</span>
                        </button>
                    `;
                });

            }

            $('#ticketActionList').html(html);
        }
        function renderPrimaryAction(ticket, user){

            let btn = $('#primaryActionBtn');

            btn.addClass('hidden');

            // PRIORITY ACTION RULE
            if (ticket.status === 'W' && ticket.pic_ticket === user.username) {
                btn.removeClass('hidden')
                    .text('Start Work')
                    .off('click')
                    .on('click', () => startTicket(ticket.eid));
            }
        }

                    // =========================
            // RENDER LIST (PRETTY)
            // =========================

            function formatDate(dateStr) {
                if (!dateStr) return '-';
                let d = new Date(dateStr);
                return d.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            }

            function showValidationErrors(errors) {
                $('.input-error').remove();
                $('.border-red-500').removeClass('border-red-500');

                Object.keys(errors).forEach(field => {
                    let input = $(`[name="${field}"]`);
                    if (!input.length) return;

                    // detect select2
                    if (input.hasClass('select2-hidden-accessible')) {

                        input.next('.select2-container')
                            .find('.select2-selection')
                            .addClass('border-red-500');

                    } else {
                        input.addClass('border-red-500');
                    }

                    input.after(`
                        <p class="input-error text-xs text-red-500 mt-1">
                            ${errors[field][0]}
                        </p>
                    `);
                });
            }

            function renderTickets(rows) {

                rows = rows.filter(r => {
                    if (!state.category) return true;
                    let cat = r.ticket_category_name || r.ticket_categoryid;
                    return cat === state.category;
                });

                if (!rows.length) {
                    UI.wrapper.html(`
                        <div class="flex flex-col items-center justify-center px-6 py-20 text-center">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-3xl dark:bg-slate-800">
                                🎫
                            </div>
                            <h3 class="mt-5 text-lg font-semibold text-slate-700 dark:text-slate-200">
                                No Ticket Found
                            </h3>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                Try adjusting your filters or create a new ticket.
                            </p>
                        </div>
                    `);
                    return;
                }

                let html = '';

                rows.forEach(row => {

                    const statusMap = {
                        W: ['WAITING', 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
                        P: ['IN PROGRESS', 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300'],
                        C: ['COMPLETED', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'],
                        R: ['REOPENED', 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300'],
                        X: ['CANCELLED', 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300']
                    };

                    const priorityMap = {
                        HIGH: 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                        MEDIUM: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                        LOW: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                    };

                    let [statusLabel, statusClass] = statusMap[row.status] || [row.status, 'bg-slate-100 text-slate-700'];
                    let priorityClass = priorityMap[row.ticket_priority] || 'bg-slate-100 text-slate-700';

                    html += `
                    <div class="group relative flex flex-col gap-4 p-5 transition-all duration-200 border-b border-slate-100 last:border-none hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50 lg:flex-row lg:items-center lg:justify-between">

                        <div class="min-w-0 flex-1">

                            <!-- TOP -->
                            <div class="flex flex-wrap items-center gap-2">

                                <button type="button"
                                    class="ticket-detail-btn inline-flex items-center rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200"
                                    data-eid="${row.eid}">
                                    ${row.ticketid}
                                </button>

                                <span class="rounded-lg px-2.5 py-1 text-[11px] font-semibold ${statusClass}">
                                    ${statusLabel}
                                </span>

                                <span class="rounded-lg px-2.5 py-1 text-[11px] font-semibold ${priorityClass}">
                                    ${row.ticket_priority}
                                </span>

                            </div>

                            <!-- TITLE -->
                            <div class="mt-3">
                                <h3 class="text-lg font-semibold text-slate-900 leading-snug dark:text-white">
                                    ${row.issue_summary ?? '-'}
                                </h3>

                                <p class="mt-1 text-sm text-slate-500 line-clamp-2 dark:text-slate-400">
                                    ${row.issue_descr ?? '-'}
                                </p>
                            </div>

                            <!-- META -->
                            <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500 dark:text-slate-400">

                                <span>👤 ${row.created_by ?? '-'}</span>

                                <span>🛠 ${row.pic_ticket ?? 'Unassigned'}</span>

                                <span>📂 ${row.ticket_category_name ?? row.ticket_categoryid ?? '-'}</span>

                                <span>📍 ${row.location_name ?? row.location_id ?? '-'}</span>

                                <span>📅 ${formatDate(row.ticketdate)}</span>

                                <span class="font-medium text-slate-600 dark:text-slate-300">
                                    ⚡ SLA ${row.ticket_sla_days ?? 0}d
                                </span>

                            </div>

                        </div>

                        <!-- ACTION -->
                        <div class="flex items-center gap-2">

                            <button type="button"
                                class="ticket-detail-btn inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500"
                                data-eid="${row.eid}">
                                Detail
                            </button>

                            ${(row.status === 'W' || row.status === 'R') ? `
                                <button type="button"
                                    class="ticket-process-btn rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300"
                                    data-eid="${row.eid}">
                                    ⚡ Process
                                </button>
                            ` : ''}

                            ${(row.status === 'W') ? `
                                <button type="button"
                                    class="ticket-cancel-btn rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300"
                                    data-eid="${row.eid}">
                                    ❌ Cancel
                                </button>
                            ` : ''}

                            ${(row.status === 'C') ? `
                                <button type="button"
                                    class="ticket-reopen-btn rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300"
                                    data-eid="${row.eid}">
                                    Reopen
                                </button>
                            ` : ''}

                        </div>

                    </div>
                    `;
                });

                UI.wrapper.html(html);
            }

            let picXHR = null;

            function loadPIC() {

                let type = $('#ticket_type').val();
                let category = $('#ticket_categoryid').val();
                let dept = $('#department_id').val();

                if (!type || !category || !dept) return;

                let $pic = $('#pic_ticket');

                // 🔥 cancel previous request
                if (picXHR) {
                    picXHR.abort();
                }

                $pic.html('<option>Loading...</option>');

                picXHR = $.get(API.pic, {
                    ticket_type: type,
                    ticket_categoryid: category,
                    department_id: dept
                })
                .done(res => {

                    let opt = '<option value="">Select PIC</option>';

                    res.forEach(u => {
                        opt += `<option value="${u.username}">${u.name}</option>`;
                    });

                    if ($pic.data('select2')) {
                        $pic.select2('destroy');
                    }

                    $pic.html(opt);

                    initSelect2('#pic_ticket');

                    if (res.length === 1) {
                        $pic.val(res[0].username).trigger('change');
                    }
                });
            }

            // =========================
            // FILTER
            // =========================
            $('.status-filter').click(function(e) {
                e.preventDefault();

                $('.status-filter').removeClass('active');
                $(this).addClass('active');

                state.status = $(this).data('status');

                // ✅ RESET CATEGORY STATE + UI
                state.category = '';

                $('.ticket-category-tab')
                    .removeClass('bg-slate-900 text-white active-category-tab')
                    .addClass('border');

                $('.ticket-category-tab[data-category=""]')
                    .addClass('bg-slate-900 text-white active-category-tab')
                    .removeClass('border');

                loadTickets();
            });

            $('#btnTicketActions').click(function(e){
                e.stopPropagation();

                let dropdown = $('#ticketActionDropdown');
                let arrow = $('#actionArrow');

                dropdown.toggleClass('hidden');

                // rotate arrow
                arrow.toggleClass('rotate-180');
            });

            $(document).click(function(){
                $('#ticketActionDropdown').addClass('hidden');
                $('#actionArrow').removeClass('rotate-180');
            });

            $('#imagePreviewModal').click(function(e){
                if(e.target.id === 'imagePreviewModal'){
                    $(this).addClass('hidden');
                    $('#previewImage').attr('src','');
                }
            });
            // =========================
            // SEARCH (DEBOUNCE)
            // =========================
            let debounce;
            $('#ticketSearch').on('keyup', function() {
                clearTimeout(debounce);
                debounce = setTimeout(() => {
                    state.search = $(this).val();
                    loadTickets();
                }, 400);
            });

            // =========================
            // MODAL CONTROL
            // =========================
            $('#btnCreateTicket').click(() => {
                window.location.href = '/ticket/create';
            });
            $('#closeCreateTicketModal, #cancelCreateTicket').click(() => {
                $('#createTicketModal').addClass('hidden');
                $('body').removeClass('modal-open');

                window.history.pushState({}, '', '/ticket');
            });

            $('#closeDetailTicketModal').click(() => {
                $('#detailTicketModal').addClass('hidden');
                window.history.pushState({}, '', '/ticket');
            });

            // =========================
            // TOGGLE PIC DROPDOWN
            // =========================
            $('#assign_pic_checkbox').on('change', function () {

                if ($(this).is(':checked')) {

                    $('#picSelectionWrapper').removeClass('hidden');

                    // optional: make required
                    $('#pic_ticket').attr('required', true);

                    // re-init select2 (important inside modal)
                    initSelect2('#pic_ticket');

                } else {

                    $('#picSelectionWrapper').addClass('hidden');

                    // remove required
                    $('#pic_ticket').removeAttr('required');

                    // reset value
                    $('#pic_ticket').val(null).trigger('change');
                }

            });

            // =========================
            // CATEGORY FILTER (FIX)
            // =========================
            $(document).on('click', '.ticket-category-tab', function () {

                $('.ticket-category-tab')
                    .removeClass('bg-slate-900 text-white active-category-tab')
                    .addClass('border');

                $(this)
                    .addClass('bg-slate-900 text-white active-category-tab')
                    .removeClass('border');

                state.category = $(this).data('category');

                renderTickets(window.__tickets || []);
            });

            // =========================
            // SELECT CHAINING
            // =========================
            $('#ticket_type').change(function() {

                $('#ticket_categoryid').html('<option>Loading...</option>');

                $.get(API.category, {
                    ticket_type: this.value
                }, res => {
                    let opt = '<option value="">Select Category</option>';
                    res.forEach(x => {
                        opt +=
                            `<option value="${x.ticket_categoryid}">${x.ticket_category_name}</option>`;
                    });
                    let $cat = $('#ticket_categoryid');

                    if ($cat.hasClass("select2-hidden-accessible")) {
                        $cat.select2('destroy');
                    }

                    $cat.html(opt);

                    // 🔥 INIT FIRST
                    initSelect2('#ticket_categoryid');

                    // 🔥 THEN trigger change
                    // $cat.trigger('change');
                });
            });

            $('#ticket_categoryid').change(function () {

                let type = $('#ticket_type').val();

                // reset
                $('#ticket_subcategoryid').html('<option>Loading...</option>');
                $('#ticket_priority').html('<option>Loading...</option>');

                // =====================
                // SUBCATEGORY
                // =====================
                $.get(API.subcategory, {
                    ticket_type: type,
                    ticket_categoryid: this.value
                }, res => {

                    let opt = '<option value="">Select Subcategory</option>';

                    res.forEach(x => {
                        opt += `<option value="${x.ticket_subcategoryid}">${x.ticket_subcategory_name}</option>`;
                    });

                    let $sub = $('#ticket_subcategoryid');

                    if ($sub.hasClass("select2-hidden-accessible")) {
                        $sub.select2('destroy');
                    }

                    $sub.html(opt);

                    // ✅ INIT FIRST
                    initSelect2('#ticket_subcategoryid');

                    // ❌ DO NOT trigger change unless needed
                });

                // =====================
                // PRIORITY
                // =====================
                $.get(API.priority, {
                    ticket_type: type,
                    ticket_categoryid: this.value
                }, res => {

                    let opt = '';
                    let selectedValue = '';

                    let hasMedium = res.some(x => x.ticket_priority.toUpperCase() === 'MEDIUM');

                    res.forEach((x, i) => {

                        let isSelected = false;

                        if (hasMedium) {
                            isSelected = x.ticket_priority.toUpperCase() === 'MEDIUM';
                        } else if (i === 0) {
                            isSelected = true;
                        }

                        if (isSelected) selectedValue = x.ticket_priority;

                        opt += `<option value="${x.ticket_priority}" ${isSelected ? 'selected' : ''}>
                                    ${x.ticket_priority_name}
                                </option>`;
                    });

                    let $pri = $('#ticket_priority');

                    // destroy select2 lama
                    if ($pri.hasClass("select2-hidden-accessible")) {
                        $pri.select2('destroy');
                    }

                    // set option
                    $pri.html(opt);

                    // init select2
                    initSelect2('#ticket_priority');

                    // 🔥 LOCK UI (INI YANG LO CARI)
                    $pri.next('.select2-container')
                        .addClass('pointer-events-none opacity-70');

                    // 🔥 IMPORTANT: jangan disable
                    $pri.prop('disabled', false);

                    // 🔥 pastikan value ke-set (biar masuk DB)
                    $pri.val(selectedValue).trigger('change');

                });
                // =====================
                // PIC
                // =====================
                setTimeout(loadPIC, 200);
            });

            $('#location_id').on('change', function () {

                let locationId = this.value;
                let $subLoc = $('#sub_location_id');

                // =====================
                // RESET STATE
                // =====================
                if ($subLoc.hasClass("select2-hidden-accessible")) {
                    $subLoc.select2('destroy');
                }

                $subLoc.html('<option value="">Loading...</option>');

                // =====================
                // FETCH DATA
                // =====================
                $.get(API.sublocation, {
                    location_id: locationId
                })
                .done(function (res) {

                    let opt = '<option value="">Select Sub Location</option>';

                    res.forEach(x => {
                        opt += `<option value="${x.sub_location_id}">${x.sub_location_name}</option>`;
                    });

                    // =====================
                    // SET HTML
                    // =====================
                    $subLoc.html(opt);

                    // =====================
                    // INIT SELECT2 (AFTER HTML)
                    // =====================
                    initSelect2('#sub_location_id');

                    // =====================
                    // OPTIONAL AUTO SELECT
                    // =====================
                    if (res.length === 1) {
                        $subLoc.val(res[0].sub_location_id).trigger('change');
                    }

                })
                .fail(function () {
                    $subLoc.html('<option value="">Failed load data</option>');
                });

            });

            // =========================
            // LOAD PIC BASED ON SETUP
            // =========================
            $('#ticket_type').on('change', loadPIC);
            // $('#ticket_categoryid').on('change', loadPIC);
            $('#department_id').on('change', loadPIC);

            // =========================
            // ATTACHMENT PREVIEW
            // =========================
            let selectedFiles = [];

            $('#attachments').on('change', function () {

                // simpan file ke state
                selectedFiles = [...this.files];

                renderPreview();
            });

            function renderPreview() {

                let preview = $('#attachmentPreview').empty();

                selectedFiles.forEach((file, index) => {

                    preview.append(`
                        <div class="flex items-center justify-between text-xs bg-slate-100 px-3 py-2 rounded">
                            <span class="truncate">${file.name}</span>

                            <button type="button"
                                class="text-red-500 hover:text-red-700 font-bold"
                                onclick="removeFile(${index})">
                                ✕
                            </button>
                        </div>
                    `);
                });

                // 🔥 IMPORTANT: reassign ke input
                let dt = new DataTransfer();
                selectedFiles.forEach(f => dt.items.add(f));
                document.getElementById('attachments').files = dt.files;
            }

            function removeFile(index) {

                selectedFiles.splice(index, 1);

                renderPreview();
            }



            // =========================
            // CREATE SUBMIT (CLEAN UX)
            // =========================
            $('#formCreateTicket').submit(function(e) {
                e.preventDefault();

                let btn = $('#submitCreateTicket');
                btn.prop('disabled', true).text('Submitting...');

                // =========================
                // 🔥 FORCE SYNC SELECT2 VALUE
                // =========================
                let picVal = $('#pic_ticket').val();

                // // debug (optional, boleh hapus nanti)
                // console.log('PIC VALUE:', picVal);

                // =========================
                // FORM DATA
                // =========================
                let formData = new FormData(this);

                // 🔥 IMPORTANT: pastikan ikut ke submit
                formData.set('pic_ticket', picVal || '');

                // debug semua payload
                // console.log([...formData.entries()]);

                // =========================
                // AJAX SUBMIT
                // =========================
                $.ajax({
                    url: API.create,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                })
                .done(res => {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Ticket berhasil dibuat',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // close modal
                    UI.modalCreate.addClass('hidden');
                    $('body').removeClass('modal-open');

                    // reset form
                    resetCreateForm();

                    // reload list
                    loadTickets();

                })
                .fail(err => {

                    if (err.status === 422) {
                        showValidationErrors(err.responseJSON.errors);

                        let first = Object.keys(err.responseJSON.errors)[0];
                        $(`[name="${first}"]`).focus();

                        return;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: err.responseJSON?.message || 'Something went wrong'
                    });

                })
                .always(() => {
                    btn.prop('disabled', false).text('Submit Ticket');
                });
            });

            $('#submitAssign').click(function(){

                let t = window.currentTicket;
                let pic = $('#assign_pic_dropdown').val();

                if (!t) {
                    Swal.fire('Error','No ticket selected','error');
                    return;
                }

                if (!pic) {
                    Swal.fire('Error','Please select PIC','error');
                    return;
                }

                let btn = $(this);
                btn.prop('disabled', true).text('Saving...');

                $.post(API.assign(t.eid), {
                    _token: '{{ csrf_token() }}',
                    pic_ticket: pic
                })
                .done(res => {

                    Swal.fire({
                        icon: 'success',
                        title: 'Assigned',
                        text: res.message || 'PIC assigned successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    $('#assignModal').addClass('hidden');

                    // refresh detail
                    openDetail(t.eid);

                    // refresh list
                    loadTickets();

                })
                .fail(err => {

                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: err.responseJSON?.message || 'Failed assign PIC'
                    });

                })
                .always(() => {
                    btn.prop('disabled', false).text('Save');
                });

            });

            $('#submitTransfer').click(function(){

                let t = window.currentTicket;
                let category = $('#transfer_category').val();

                if (!t) {
                    Swal.fire('Error','No ticket selected','error');
                    return;
                }

                if (!category) {
                    Swal.fire('Error','Please select category','error');
                    return;
                }

                let btn = $(this);
                btn.prop('disabled', true).text('Transferring...');

                $.post(API.transfer(t.eid), {
                    _token: '{{ csrf_token() }}',
                    ticket_categoryid: category
                })
                .done(res => {

                    Swal.fire({
                        icon: 'success',
                        title: 'Transferred',
                        text: res.message || 'Ticket transferred successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    $('#transferModal').addClass('hidden');

                    // refresh detail
                    openDetail(t.eid);

                    // refresh list
                    loadTickets();

                })
                .fail(err => {

                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: err.responseJSON?.message || 'Failed transfer ticket'
                    });

                })
                .always(() => {
                    btn.prop('disabled', false).text('Transfer');
                });

            });

            // =========================
            // DETAIL MODAL
            // =========================
            function openDetail(eid) {

                $('#detailTicketModal').removeClass('hidden');

                $('#detail_tracking').html('Loading...');
                $('#detail_attachments').html('Loading...');

                $.get(`{{ url('ticket/detail') }}/${eid}`, function(res) {

                    let t = res.ticket;
                    window.currentTicket = t;

                    renderTicketActions(t);
                    renderPrimaryAction(t, CURRENT_USER);


                    // =========================
                    // ACTION VISIBILITY (ONLY W )
                    // =========================
                    if (['W'].includes(t.status)) {
                        $('#btnTransferTicket').removeClass('hidden');
                        $('#btnAssignTicket').removeClass('hidden');
                    } else {
                        $('#btnTransferTicket').addClass('hidden');
                        $('#btnAssignTicket').addClass('hidden');
                    }

                    // =========================
                    // BASIC INFO
                    // =========================
                    $('#detail_ticketid').text(t.ticketid);
                    $('#detail_descr').text(t.issue_descr);
                    $('#detail_created_by').text(t.created_by);
                    $('#detail_category').text(t.ticket_category_name);
                    $('#detail_subcategory').text(t.ticket_subcategory_name);
                    $('#detail_location').text(t.location_name);
                    $('#detail_sub_location').text(t.sub_location_name);
                    $('#detail_pic').text(t.pic_ticket ?? 'Unassigned');

                    // =========================
                    // PRIORITY
                    // =========================
                    let priorityClass = {
                        HIGH: 'bg-red-100 text-red-700',
                        MEDIUM: 'bg-amber-100 text-amber-700',
                        LOW: 'bg-emerald-100 text-emerald-700'
                    } [t.ticket_priority] || 'bg-slate-100 text-slate-700';

                    $('#detail_priority')
                        .text(t.ticket_priority)
                        .attr('class', `rounded-lg px-2 py-1 text-[11px] font-semibold ${priorityClass}`);

                    // =========================
                    // STATUS
                    // =========================
                    let statusMap = {
                        W: ['WAITING', 'bg-amber-100 text-amber-700'],
                        P: ['IN PROGRESS', 'bg-blue-100 text-blue-700'],
                        C: ['COMPLETED', 'bg-emerald-100 text-emerald-700'],
                        R: ['REOPENED', 'bg-rose-100 text-rose-700'],
                        X: ['CANCELLED', 'bg-slate-200 text-slate-700']
                    };

                    let [statusLabel, statusClass] = statusMap[t.status] || [t.status, 'bg-slate-100'];

                    $('#detail_status')
                        .text(statusLabel)
                        .attr('class', `rounded-lg px-2 py-1 text-[11px] font-semibold ${statusClass}`);

                    // =========================
                    // TYPE / DATE / SLA
                    // =========================
                    function formatDate(dateStr) {
                        if (!dateStr) return '-';

                        let d = new Date(dateStr);

                        return d.toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                    $('#detail_type').text(t.ticket_type || '-');
                    $('#detail_date').text(formatDate(t.ticketdate));
                    $('#detail_sla').text(`${t.ticket_sla_days ?? 0} Day(s)`);

                    // =========================
                    // TIMELINE
                    // =========================

                    function timelineStyle(type) {

                        return {
                            CREATED: ['bg-gray-500', '📝'],
                            START: ['bg-blue-500', '🚀'],
                            PENDING: ['bg-yellow-500', '⏳'],
                            COMPLETED: ['bg-green-600', '✅'],
                            REOPEN: ['bg-rose-500', '🔄'],
                            ASSIGN: ['bg-indigo-500', '👤'],     // 🔥 add
                            TRANSFER: ['bg-amber-500', '🔁']     // 🔥 add
                        }[type] || ['bg-gray-400', '•'];

                    }

                    function buildTimeline(ticket, activities) {

                        let timeline = [];

                        // CREATED (manual)
                        timeline.push({
                            type: 'CREATED',
                            title: 'Ticket Created',
                            user: ticket.created_by,
                            date: ticket.ticketdate,
                            desc: ticket.issue_summary
                        });

                        // FROM DB
                        activities.forEach(a => {

                            const map = {
                                START: 'Ticket Started',
                                PENDING: 'Progress Update',
                                COMPLETED: 'Completed',
                                REOPEN: 'Reopened',
                                CANCEL: 'Cancelled',
                                ASSIGN: 'Assign PIC',          // 🔥 add
                                TRANSFER: 'Transferred'        // 🔥 add
                            };

                            timeline.push({
                                type: a.status_pekerjaan,
                                title: map[a.status_pekerjaan] || 'Activity',
                                user: a.pic_ticket,
                                date: a.response_date,
                                desc: a.response_descr,
                                summary: a.response_summary
                            });
                        });

                        timeline.sort((a, b) => new Date(a.date) - new Date(b.date));

                        return timeline;
                    }

                    let timelineData = buildTimeline(t, res.activities);

                    let html = '';

                    timelineData.forEach(item => {

                        let [color, icon] = timelineStyle(item.type);

                       html += `
                        <div class="flex gap-3">

                            <!-- DOT -->
                            <div class="flex flex-col items-center">
                                <div class="w-7 h-7 rounded-full ${color} text-white flex items-center justify-center text-xs">
                                    ${icon}
                                </div>
                                <div class="flex-1 w-px bg-slate-200 dark:bg-slate-700"></div>
                            </div>

                            <!-- CONTENT -->
                            <div class="flex-1 rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800/40">

                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-slate-800 dark:text-white">
                                        ${item.title}
                                    </p>
                                    <p class="text-[11px] text-slate-400">
                                        ${formatDate(item.date)}
                                    </p>
                                </div>

                                <p class="text-[12px] text-slate-500">
                                    by <span class="font-medium">${item.user}</span>
                                </p>

                                ${item.desc ? `
                                    <div class="mt-1 text-[12px]">
                                        <span class="text-slate-400">Change:</span>
                                        <span class="font-medium text-red-500">${item.desc.split(' to ')[0].replace('PIC changed from ','')}</span>
                                        →
                                        <span class="font-medium text-green-600">${item.desc.split(' to ')[1]}</span>
                                    </div>
                                ` : ''}

                            </div>

                        </div>
                        `;
                    });

                    $('#detail_tracking').html(html);

                    // =========================
                    // ATTACHMENTS
                    // =========================
                    let attach = res.attachments.map(a => {

                        let isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(a.display_name);

                        if (isImage) {
                            return `
                                <button type="button"
                                    class="attachment-image-preview flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
                                    data-url="${a.url}">
                                    <span>🖼</span>
                                    <span class="truncate">${a.display_name}</span>
                                </button>
                            `;
                        }

                        return `
                            <a href="${a.url}" target="_blank"
                                class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                                <span>📎</span>
                                <span class="truncate">${a.display_name}</span>
                            </a>
                        `;

                    }).join('');

                    // ✅ IMPORTANT: render to UI
                    $('#detail_attachments').html(attach || `
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            No attachment
                        </div>
                    `);


                });

            }




            $(document).on('click', '.attachment-image-preview', function () {

                let url = $(this).data('url');

                $('#previewImage').attr('src', url);
                $('#imagePreviewModal').removeClass('hidden');
            });

            $('#closeImagePreview').click(function () {
                $('#imagePreviewModal').addClass('hidden');
                $('#previewImage').attr('src', '');
            });

            // $('#btnTransferTicket').click(function(){

            //     let t = window.currentTicket;

            //     if (!t) return;

            //     $('#transferModal').removeClass('hidden');

            //     // load category
            //     $.get(API.category, { ticket_type: t.ticket_type }, function(res){

            //         let opt = '<option value="">Select Category</option>';

            //         res.forEach(x => {
            //             opt += `<option value="${x.ticket_categoryid}">
            //                         ${x.ticket_category_name}
            //                     </option>`;
            //         });

            //         $('#transfer_category').html(opt);
            //         let $transfer = $('#transfer_category');

            //         if ($transfer.data('select2')) {
            //             $transfer.select2('destroy');
            //         }

            //         $transfer.html(opt);

            //         initSelect2('#transfer_category', '#transferModal');

            //     });

            // });

            $('#cancelTransfer').click(() => {
                $('#transferModal').addClass('hidden');
            });

            $('#cancelAssign').click(() => {
                $('#assignModal').addClass('hidden');
            });

            // $('#btnAssignTicket').click(function(){

            //     let t = window.currentTicket;

            //     if (!t) return;

            //     $('#assignModal').removeClass('hidden');

            //     // load PIC based on current category
            //     $.get(API.pic, {
            //         ticket_type: t.ticket_type,
            //         ticket_categoryid: t.ticket_categoryid,
            //         department_id: t.department_id
            //     }, function(res){

            //         let opt = '<option value="">Select PIC</option>';

            //         res.forEach(u => {
            //             opt += `<option value="${u.username}">${u.name}</option>`;
            //         });

            //         $('#assign_pic_dropdown').html(opt);

            //         initSelect2('#assign_pic_dropdown', '#assignModal');

            //     });

            // });
            $(document).on('click', '.ticket-action-item', function(){

                let index = $(this).data('index');

                let t = window.currentTicket;

                if (!t) {
                    Swal.fire('Error', 'Ticket context not found', 'error');
                    return;
                }

                let actions = buildTicketActions(t, CURRENT_USER);

                if (actions[index]) {
                    actions[index].action();
                }

                $('#ticketActionDropdown').addClass('hidden');
            });

            $(document).on('click', '.ticket-detail-btn', function() {
                let eid = $(this).data('eid');
                window.location.href = `/showticket/${eid}`;
            });

            $(document).on('click', '.ticket-cancel-btn', function () {

                let eid = $(this).data('eid');

                Swal.fire({
                    title: 'Cancel Ticket?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Cancel',
                    cancelButtonText: 'No',
                    confirmButtonColor: '#ef4444'
                }).then(result => {

                    if (!result.isConfirmed) return;

                    $.post(API.cancel(eid), {
                        _token: '{{ csrf_token() }}'
                    })
                    .done(res => {

                        Swal.fire({
                            icon: 'success',
                            title: 'Cancelled',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        loadTickets();

                    })
                    .fail(err => {

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: err.responseJSON?.message || 'Failed cancel ticket'
                        });

                    });

                });

            });

            // =========================
            // INIT
            // =========================
            loadTickets();

            // =========================
            // AUTO OPEN FROM URL
            // =========================
            (function handleAutoOpen() {

                const path = window.location.pathname;

                const getEidFromUrl = () => {
                    let parts = path.split('/');
                    return parts[parts.length - 1];
                };

                // =========================
                // CREATE
                // =========================
                if (path === '/ticket/create') {

                    setTimeout(() => {

                        resetCreateForm(); // 🔥 WAJIB TAMBAH INI

                        $('#createTicketModal').removeClass('hidden');
                        $('body').addClass('modal-open');

                        $('#createTicketModal select:visible').each(function(){
                            initSelect2(this);
                        });

                    }, 200);
                }

                // =========================
                // DETAIL
                // =========================
                if (path.includes('/showticket/')) {

                    let eid = getEidFromUrl();

                    setTimeout(() => {
                        openDetail(eid);
                    }, 200);
                }

            })();


            function resetCreateForm() {

                let form = $('#formCreateTicket')[0];
                form.reset();

                // reset select2
                $('#formCreateTicket select').each(function () {
                    let $el = $(this);

                    if ($el.hasClass("select2-hidden-accessible")) {
                        $el.val(null).trigger('change');
                    } else {
                        $el.val('');
                    }
                });

                // reset attachment preview
                selectedFiles = [];
                $('#attachmentPreview').empty();

                // reset PIC section
                $('#assign_pic_checkbox').prop('checked', false);
                $('#picSelectionWrapper').addClass('hidden');
                $('#pic_ticket').removeAttr('required');

                // reset priority UI lock
                $('#ticket_priority')
                    .next('.select2-container')
                    .removeClass('pointer-events-none opacity-70');

                // reset dropdown dependent
                $('#ticket_categoryid').html('<option value="">Select Category</option>');
                $('#ticket_subcategoryid').html('<option value="">Select Subcategory</option>');
                $('#ticket_priority').html('<option value="">Select Priority</option>');
                $('#sub_location_id').html('<option value="">Select Sub Location</option>');
            }

            $(document).keydown(function(e){
                if(e.key === "Escape"){
                    $('#createTicketModal').addClass('hidden');
                    $('#detailTicketModal').addClass('hidden');
                    $('body').removeClass('modal-open');
                }
            });

        });
    </script>
</x-app-layout>
