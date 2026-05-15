<x-app-layout>
    @include('pages.ticket.partial.style')

    <div class="max-w-9xl mx-auto w-full p-2">
        @php

            $isIT = \App\Models\SysUserRole::query()
                ->where('username', auth()->user()->username)
                ->whereIn('role_id', ['ITHARDWARE', 'ITSOFTWARE'])
                ->exists();

        @endphp

        {{-- Status Filter --}}
        <div
            class="{{ $isIT ? '2xl:grid-cols-10' : '2xl:grid-cols-6' }} grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

            {{-- All --}}
            <button type="button" class="text-left">

                <a href="#" class="ticket-status-filter group block h-full" data-status="">

                    <div
                        class="ticket-status-card flex h-full items-center gap-3 rounded-lg border border-slate-700 bg-slate-200/20 p-3 text-slate-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-slate-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            🎫
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">

                            <p class="break-words text-sm font-medium">
                                All
                            </p>

                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $counts['all'] ?? 0 }}
                        </p>

                    </div>

                </a>

            </button>

            @if ($isIT)
                {{-- Created --}}
                <button type="button" class="text-left">

                    <a href="#" class="ticket-status-filter group block h-full" data-status="CREATED">

                        <div
                            class="ticket-status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                                🆕
                            </div>

                            <div class="flex min-w-0 flex-grow flex-col leading-tight">

                                <p class="break-words text-sm font-medium">
                                    Created
                                </p>

                            </div>

                            <p class="shrink-0 text-base font-bold">
                                {{ $counts['created'] ?? 0 }}
                            </p>

                        </div>

                    </a>

                </button>

                {{-- Response --}}
                <button type="button" class="text-left">

                    <a href="#" class="ticket-status-filter group block h-full" data-status="RESPONSE">

                        <div
                            class="ticket-status-card flex h-full items-center gap-3 rounded-lg border border-cyan-700 bg-cyan-200/20 p-3 text-cyan-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-cyan-100 hover:shadow-md active:scale-95">

                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                                📩
                            </div>

                            <div class="flex min-w-0 flex-grow flex-col leading-tight">

                                <p class="break-words text-sm font-medium">
                                    Response
                                </p>

                            </div>

                            <p class="shrink-0 text-base font-bold">
                                {{ $counts['response'] ?? 0 }}
                            </p>

                        </div>

                    </a>

                </button>
            @endif

            {{-- Process --}}
            <button type="button" class="text-left">

                <a href="#" class="ticket-status-filter group block h-full" data-status="PROCESS">

                    <div
                        class="ticket-status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            ⚙️
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">

                            <p class="break-words text-sm font-medium">
                                Process
                            </p>

                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $counts['process'] ?? 0 }}
                        </p>

                    </div>

                </a>

            </button>

            {{-- Pending --}}
            <button type="button" class="text-left">

                <a href="#" class="ticket-status-filter group block h-full" data-status="PENDING">

                    <div
                        class="ticket-status-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            ⏳
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">

                            <p class="break-words text-sm font-medium">
                                Pending
                            </p>

                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $counts['pending'] ?? 0 }}
                        </p>

                    </div>

                </a>

            </button>

            @if ($isIT)
                {{-- Envision --}}
                <button type="button" class="text-left">

                    <a href="#" class="ticket-status-filter group block h-full" data-status="ENVISION">

                        <div
                            class="ticket-status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                                🛠️
                            </div>

                            <div class="flex min-w-0 flex-grow flex-col leading-tight">

                                <p class="break-words text-sm font-medium">
                                    Envision
                                </p>

                            </div>

                            <p class="shrink-0 text-base font-bold">
                                {{ $counts['envision'] ?? 0 }}
                            </p>

                        </div>

                    </a>

                </button>

                {{-- Transfer --}}
                <button type="button" class="text-left">

                    <a href="#" class="ticket-status-filter group block h-full" data-status="TRANSFER">

                        <div
                            class="ticket-status-card flex h-full items-center gap-3 rounded-lg border border-pink-700 bg-pink-200/20 p-3 text-pink-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-pink-100 hover:shadow-md active:scale-95">

                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                                🔄
                            </div>

                            <div class="flex min-w-0 flex-grow flex-col leading-tight">

                                <p class="break-words text-sm font-medium">
                                    Transfer
                                </p>

                            </div>

                            <p class="shrink-0 text-base font-bold">
                                {{ $counts['transfer'] ?? 0 }}
                            </p>

                        </div>

                    </a>

                </button>
            @endif

            {{-- Completed --}}
            <button type="button" class="text-left">

                <a href="#" class="ticket-status-filter group block h-full" data-status="COMPLETED">

                    <div
                        class="ticket-status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            ✅
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">

                            <p class="break-words text-sm font-medium">
                                Completed
                            </p>

                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $counts['completed'] ?? 0 }}
                        </p>

                    </div>

                </a>

            </button>

            {{-- Reopen --}}
            <button type="button" class="text-left">

                <a href="#" class="ticket-status-filter group block h-full" data-status="REOPEN">

                    <div
                        class="ticket-status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            ♻️
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">

                            <p class="break-words text-sm font-medium">
                                Reopen
                            </p>

                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $counts['reopen'] ?? 0 }}
                        </p>

                    </div>

                </a>

            </button>

            {{-- Cancel --}}
            <button type="button" class="text-left">

                <a href="#" class="ticket-status-filter group block h-full" data-status="CANCEL">

                    <div
                        class="ticket-status-card flex h-full items-center gap-3 rounded-lg border border-zinc-700 bg-zinc-200/20 p-3 text-zinc-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-zinc-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            ❌
                        </div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">

                            <p class="break-words text-sm font-medium">
                                Cancel
                            </p>

                        </div>

                        <p class="shrink-0 text-base font-bold">
                            {{ $counts['cancel'] ?? 0 }}
                        </p>

                    </div>

                </a>

            </button>

        </div>
        {{-- Filter Toolbar --}}
        @if ($isIT)
            <div
                class="mt-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">

                    {{-- Search --}}
                    <div>

                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">

                            Search

                        </label>

                        <input type="text" id="filter_search" placeholder="Ticket / Summary / PIC"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:ring-blue-800">

                    </div>

                    {{-- Status --}}
                    <div>

                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">

                            Status

                        </label>

                        <select id="filter_status"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:ring-blue-800">

                            <option value="">
                                All Status
                            </option>

                            <option value="OPEN">
                                Open
                            </option>

                            <option value="ONPROGRESS">
                                On Progress
                            </option>

                            <option value="PENDING">
                                Pending
                            </option>

                            <option value="COMPLETED">
                                Completed
                            </option>

                            <option value="CANCEL">
                                Cancelled
                            </option>

                        </select>

                    </div>

                    {{-- Workflow --}}
                    <div>

                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">

                            Workflow

                        </label>

                        <select id="filter_status_pekerjaan"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:ring-blue-800">

                            <option value="">
                                All Workflow
                            </option>

                            <option value="CREATED">
                                Created
                            </option>

                            <option value="RESPONSE">
                                Response
                            </option>

                            <option value="PROCESS">
                                Process
                            </option>

                            <option value="PENDING">
                                Pending
                            </option>

                            <option value="ENVISION">
                                Envision
                            </option>

                            <option value="TRANSFER">
                                Transfer
                            </option>

                            <option value="REOPEN">
                                Reopen
                            </option>

                            <option value="COMPLETED">
                                Completed
                            </option>

                            <option value="CANCEL">
                                Cancelled
                            </option>

                        </select>

                    </div>

                    {{-- Date From --}}
                    <div>

                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">

                            Date From

                        </label>

                        <input type="date" id="filter_date_from"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:ring-blue-800">

                    </div>

                    {{-- Date To --}}
                    <div>

                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">

                            Date To

                        </label>

                        <input type="date" id="filter_date_to"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:ring-blue-800">

                    </div>

                </div>

            </div>
        @endif
        {{-- Table Wrapper --}}
        <div
            class="mt-4 overflow-visible rounded-lg border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-[#0F172A]">

            {{-- Top Toolbar --}}
            <div class="flex flex-col gap-4 border-b border-slate-300 p-5 dark:border-white/10">

                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">

                    <div class="flex flex-col gap-1">

                        <h1 class="text-lg font-bold text-slate-800 dark:text-white">

                            Ticket Support

                        </h1>

                        <p class="text-sm text-slate-500 dark:text-slate-400">

                            Manage hardware and software access request workflow.

                        </p>

                    </div>

                    <div class="flex items-center gap-2">

                        @if ($isIT)
                            <button type="button" id="btn_export_ticket"
                                class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">

                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 16V4m0 12l-4-4m4 4l4-4m5 8H3" />

                                </svg>

                                Export

                            </button>
                        @endif

                        <button type="button" id="btn_create_ticket"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">

                            <span class="mr-2 text-base">
                                +
                            </span>

                            Create Ticket

                        </button>

                    </div>

                </div>

            </div>

            <div class="overflow-x-auto overflow-y-visible p-5">

                <table id="ticketTable" class="w-full min-w-full text-sm">

                    <thead class="bg-gray-50/80 dark:bg-[#1A2234]">

                        <tr class="border-b border-gray-200 dark:border-gray-800">

                            <th
                                class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                No
                            </th>

                            <th
                                class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                Ticket
                            </th>

                            <th
                                class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                Date
                            </th>

                            <th
                                class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                Type
                            </th>

                            <th
                                class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                Category
                            </th>

                            <th
                                class="min-w-[260px] px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                Summary
                            </th>

                            <th
                                class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                PIC
                            </th>

                            <th
                                class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                Priority
                            </th>

                            {{-- Status --}}
                            <th
                                class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                Status
                            </th>

                            @if ($isIT)
                                {{-- Workflow --}}
                                <th
                                    class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                    Workflow
                                </th>
                            @endif

                            <th
                                class="px-5 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                SLA
                            </th>

                            <th
                                class="px-5 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400">
                                Action
                            </th>

                        </tr>

                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800"></tbody>

                </table>

            </div>

        </div>

        {{-- CREATE TICKET MODAL --}}
        <div id="createTicketModal"
            class="ticket-modal fixed inset-0 z-[9999] hidden items-center justify-center p-4">

            {{-- Backdrop --}}
            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
            </div>

            {{-- Panel --}}
            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-6xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

                {{-- Header --}}
                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-5 backdrop-blur-xl dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div>
                        <h2 class="modal-title text-xl font-bold text-slate-900 dark:text-white">
                            Create Ticket
                        </h2>

                        <p class="modal-description mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Create new IT support ticket request.
                        </p>

                    </div>

                    <button type="button"
                        class="btn-close-modal inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white">

                        <i class="fa-solid fa-xmark text-lg"></i>

                    </button>

                </div>

                {{-- Content --}}
                <div class="space-y-4 bg-slate-50 p-4 dark:bg-[#0b1220]">

                    <form id="createTicketForm" class="space-y-4">

                        <input type="hidden" id="ticket_eid" name="ticket_eid">

                        <input type="hidden" name="ticket_priority" value="Medium">

                        {{-- Ticket Information --}}
                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                            <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">

                                    Ticket Information

                                </h3>

                            </div>

                            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                                {{-- Company --}}
                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Company

                                    </label>

                                    <select id="cpny_id" name="cpny_id"
                                        class="ticket-select h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                        <option value="">
                                            Select Company
                                        </option>

                                    </select>

                                </div>

                                {{-- Department --}}
                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Department

                                    </label>

                                    <select id="department_id" name="department_id"
                                        class="ticket-select h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                        <option value="">
                                            Select Department
                                        </option>

                                    </select>

                                </div>

                                {{-- Ticket Type --}}
                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Ticket Type

                                    </label>

                                    <select id="ticket_type" name="ticket_type"
                                        class="ticket-select h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                        <option value="">
                                            Select Ticket Type
                                        </option>

                                    </select>

                                </div>

                                {{-- Priority --}}
                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Priority

                                    </label>

                                    <input type="text" value="Medium Priority" readonly
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                                </div>

                            </div>

                        </div>

                        {{-- Classification --}}
                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                            <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">

                                    Classification

                                </h3>

                            </div>

                            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Category

                                    </label>

                                    <select id="ticket_categoryid" name="ticket_categoryid"
                                        class="ticket-select h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                        <option value="">
                                            Select Category
                                        </option>

                                    </select>

                                </div>

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Sub Category

                                    </label>

                                    <select id="ticket_subcategoryid" name="ticket_subcategoryid"
                                        class="ticket-select h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                        <option value="">
                                            Select Sub Category
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>

                        {{-- Location --}}
                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                            <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">

                                    Location

                                </h3>

                            </div>

                            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Location

                                    </label>

                                    <select id="location_id" name="location_id"
                                        class="ticket-select h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                        <option value="">
                                            Select Location
                                        </option>

                                    </select>

                                </div>

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Sub Location

                                    </label>

                                    <select id="sub_location_id" name="sub_location_id"
                                        class="ticket-select h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                        <option value="">
                                            Select Sub Location
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>

                        {{-- Issue Detail --}}
                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                            <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">

                                    Issue Detail

                                </h3>

                            </div>

                            <div class="space-y-4 p-5">

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Issue Summary

                                    </label>

                                    <input type="text" id="issue_summary" name="issue_summary" maxlength="255"
                                        placeholder="Enter issue summary"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500">

                                </div>

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">

                                        Issue Description

                                    </label>

                                    <textarea id="issue_descr" name="issue_descr" rows="5" placeholder="Explain your issue detail..."
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500"></textarea>

                                </div>

                            </div>

                        </div>

                        {{-- Attachment --}}
                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                            <div
                                class="border-b border-slate-200 bg-slate-50/80 px-5 py-4 dark:border-white/10 dark:bg-white/[0.03]">

                                <h3
                                    class="text-sm font-bold uppercase tracking-[0.16em] text-slate-700 dark:text-slate-200">

                                    Attachment

                                </h3>

                            </div>

                            <div class="p-5">

                                <label for="ticket_attachments"
                                    class="group flex cursor-pointer items-center justify-center gap-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-6 transition-all duration-200 hover:border-slate-400 hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.03] dark:hover:border-blue-500/30 dark:hover:bg-blue-500/[0.05]">

                                    <div
                                        class="flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-all duration-200 group-hover:scale-105 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-300">

                                        <i class="fa-solid fa-cloud-arrow-up"></i>

                                    </div>

                                    <div class="text-left">

                                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">

                                            Upload Attachment

                                        </p>

                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">

                                            PDF, DOCX, XLSX, PNG, JPG

                                        </p>

                                    </div>

                                    <input type="file" id="ticket_attachments" name="attachments[]" multiple
                                        class="hidden">

                                </label>

                                <div id="create_attachment_list" class="mt-4 space-y-3">
                                </div>

                            </div>

                        </div>

                        {{-- Footer --}}
                        <div
                            class="sticky bottom-0 z-20 mt-4 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur-xl dark:border-white/10 dark:bg-[#0f172a]/95">

                            <div class="flex items-center justify-end gap-3">

                                <button type="button"
                                    class="btn-close-modal inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5">

                                    Cancel

                                </button>

                                <button type="submit" id="btn_submit_ticket"
                                    class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition-all duration-200 hover:scale-[1.01] hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                                    <i class="fa-solid fa-paper-plane text-xs"></i>

                                    Submit Ticket

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        {{-- DETAIL TICKET MODAL --}}
        <div id="ticketDetailModal" class="ticket-modal overflow-y fixed inset-0 z-[9999] -hidden hidden p-4">

            {{-- Backdrop --}}
            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
            </div>

            <div class="mx-auto flex min-h-screen w-full max-w-[95vw] items-center justify-center xl:max-w-7xl">

                <div
                    class="modal-panel modal-scroll relative flex max-h-[95vh] w-full flex-col overflow-hidden rounded-lg bg-white opacity-0 shadow-2xl transition-all duration-200 dark:bg-gray-900">

                    {{-- Header --}}
                    <div
                        class="sticky top-0 z-20 flex flex-col gap-4 border-b border-gray-200 bg-white px-6 py-5 dark:border-gray-800 dark:bg-gray-900 xl:flex-row xl:items-start xl:justify-between">

                        <div>

                            <div class="flex flex-wrap items-center gap-3">

                                <h2 id="detail_ticketid" class="text-xl font-bold text-gray-800 dark:text-white">

                                    -

                                </h2>

                                <div id="detail_status_badge"></div>

                            </div>

                            <p id="detail_issue_summary" class="mt-2 text-sm text-gray-500 dark:text-gray-400">

                                -

                            </p>

                        </div>

                        {{-- Action --}}
                        <div class="flex flex-wrap items-center justify-end gap-2">

                            <button type="button" id="btn_print_ticket"
                                class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">

                                Print

                            </button>

                            <button type="button"
                                class="btn-close-modal inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">

                                ✕

                            </button>

                        </div>

                    </div>

                    {{-- Body --}}
                    <div class="grid flex-1 grid-cols-1 overflow-hidden xl:grid-cols-12">

                        {{-- Left Panel --}}
                        <div
                            class="min-h-0 overflow-y-auto border-b border-gray-200 p-6 dark:border-gray-800 xl:col-span-5 xl:border-b-0 xl:border-r 2xl:col-span-4">

                            {{-- Information --}}
                            <div class="space-y-5">

                                <div>

                                    <h3
                                        class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">

                                        Ticket Information

                                    </h3>

                                    <div class="space-y-4">

                                        <div>

                                            <label class="text-xs text-gray-400">

                                                Requester

                                            </label>

                                            <p id="detail_requester"
                                                class="mt-1 text-sm font-medium text-gray-800 dark:text-white">

                                                -

                                            </p>

                                        </div>

                                        <div>

                                            <label class="text-xs text-gray-400">

                                                Ticket Date

                                            </label>

                                            <p id="detail_ticketdate"
                                                class="mt-1 text-sm font-medium text-gray-800 dark:text-white">

                                                -

                                            </p>

                                        </div>

                                        <div>

                                            <label class="text-xs text-gray-400">

                                                Type

                                            </label>

                                            <p id="detail_type"
                                                class="mt-1 text-sm font-medium text-gray-800 dark:text-white">

                                                -

                                            </p>

                                        </div>

                                        <div>

                                            <label class="text-xs text-gray-400">

                                                Category

                                            </label>

                                            <div id="detail_category"
                                                class="mt-1 text-sm font-medium text-gray-800 dark:text-white">

                                                -

                                            </div>

                                        </div>

                                        <div>

                                            <label class="text-xs text-gray-400">

                                                PIC

                                            </label>

                                            <div id="detail_pic"
                                                class="mt-1 text-sm font-medium text-gray-800 dark:text-white">

                                                -

                                            </div>

                                        </div>

                                        <div>

                                            <label class="text-xs text-gray-400">

                                                Priority

                                            </label>

                                            <div id="detail_priority" class="mt-1">
                                            </div>

                                        </div>

                                        <div>

                                            <label class="text-xs text-gray-400">

                                                SLA Due Date

                                            </label>

                                            <p id="detail_sla"
                                                class="mt-1 text-sm font-medium text-gray-800 dark:text-white">

                                                -

                                            </p>

                                        </div>

                                    </div>

                                </div>

                                {{-- Description --}}
                                <div>

                                    <h3
                                        class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">

                                        Issue Description

                                    </h3>

                                    <div class="relative">

                                        <div id="detail_issue_descr"
                                            class="ticket-expandable rounded-lg bg-gray-50 px-4 py-4 text-sm leading-7 text-gray-800 dark:bg-gray-800/70 dark:text-gray-200">

                                            -

                                        </div>

                                        <button type="button"
                                            class="ticket-expand-btn mt-2 hidden text-sm font-medium text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                            data-target="#detail_issue_descr">

                                            Show more

                                        </button>

                                    </div>

                                </div>

                                {{-- Solution --}}
                                <div>

                                    <h3
                                        class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">

                                        Solution

                                    </h3>

                                    <div class="relative">

                                        <div id="detail_solution_descr"
                                            class="ticket-expandable rounded-lg bg-gray-50 px-4 py-4 text-sm leading-7 text-gray-800 dark:bg-gray-800/70 dark:text-gray-200">

                                            -

                                        </div>

                                        <button type="button"
                                            class="ticket-expand-btn mt-2 hidden text-sm font-medium text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                            data-target="#detail_solution_descr">

                                            Show more

                                        </button>

                                    </div>

                                </div>

                                {{-- Attachment --}}
                                <div>

                                    <div class="mb-3 flex items-center justify-between">

                                        <h3
                                            class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">

                                            Attachments

                                        </h3>

                                        <span id="detail_attachment_count"
                                            class="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">

                                            0

                                        </span>

                                    </div>

                                    <div id="detail_attachment_list" class="space-y-2">

                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- Right Panel --}}
                        <div class="flex min-h-0 flex-col xl:col-span-7 2xl:col-span-8">

                            {{-- Tabs --}}
                            <div
                                class="border-b border-gray-200 bg-gray-50/70 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/60">

                                <div
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white p-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                                    <button type="button"
                                        class="ticket-detail-tab active inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-200"
                                        data-tab="tracking">

                                        <i class="fa-solid fa-clock-rotate-left text-[12px]"></i>

                                        Tracking

                                    </button>

                                    <button type="button"
                                        class="ticket-detail-tab inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-200"
                                        data-tab="discussion">

                                        <i class="fa-solid fa-comments text-[12px]"></i>

                                        Discussion

                                    </button>

                                </div>

                            </div>

                            {{-- Tracking --}}
                            <div id="ticket_tracking_panel" class="ticket-tab-content flex-1 overflow-y-auto p-6">

                                <div class="mb-4 flex items-center justify-between">

                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">

                                        Tracking Timeline

                                    </h3>

                                </div>

                                <div id="ticketTimeline" class="space-y-5">

                                    <div class="animate-pulse space-y-3">

                                        <div class="h-20 rounded-lg bg-gray-200 dark:bg-gray-700"></div>

                                        <div class="h-20 rounded-lg bg-gray-200 dark:bg-gray-700"></div>

                                        <div class="h-20 rounded-lg bg-gray-200 dark:bg-gray-700"></div>

                                    </div>

                                </div>

                            </div>

                            {{-- Discussion --}}
                            <div id="ticket_discussion_panel"
                                class="ticket-tab-content hidden flex-1 overflow-y-auto">

                                <div class="flex h-full flex-col">

                                    {{-- Chat --}}
                                    <div id="ticket_comment_list" class="flex-1 space-y-4 overflow-y-auto p-6">

                                    </div>

                                    {{-- Comment Form --}}
                                    <div class="border-t border-gray-200 p-4 dark:border-gray-800">
                                        <form id="form_ticket_comment" class="space-y-4">

                                            <input type="hidden" id="comment_ticket_id">

                                            <div
                                                class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-all duration-200 focus-within:border-blue-400 focus-within:ring-4 focus-within:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:focus-within:border-blue-500 dark:focus-within:ring-blue-500/10">

                                                <textarea id="comment_message" rows="1" placeholder="Write discussion, progress update, or response..."
                                                    class="w-full resize-none border-0 bg-transparent px-5 py-4 text-sm leading-7 text-gray-700 outline-none ring-0 placeholder:text-gray-400 dark:text-gray-200 dark:placeholder:text-gray-500"></textarea>

                                                <div
                                                    class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/70 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/40 sm:flex-row sm:items-center sm:justify-between">

                                                    <div class="flex items-center gap-3">

                                                        <label for="comment_attachments"
                                                            class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">

                                                            <i class="fa-solid fa-paperclip text-xs"></i>

                                                            Attachment

                                                        </label>

                                                        <input type="file" id="comment_attachments" multiple
                                                            class="hidden">

                                                        <span id="comment_attachment_label"
                                                            class="text-xs text-gray-400 dark:text-gray-500">

                                                            No file selected

                                                        </span>

                                                    </div>

                                                    <button type="submit"
                                                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-slate-900 to-slate-700 px-5 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition-all duration-200 hover:-translate-y-[1px] hover:shadow-xl hover:shadow-slate-900/20 dark:from-white dark:to-slate-200 dark:text-slate-900">

                                                        <i class="fa-solid fa-paper-plane text-xs"></i>

                                                        Send Comment

                                                    </button>

                                                </div>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        @include('pages.ticket.partial.modal-response')

    </div>


    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
    <script>
        window.ticketCompanies = @json($companies);
        window.ticketDepartments = @json($departments);
        window.currentUser =
            @json(auth()->user()->username);

        window.isIT =
            @json($isIT);

        window.ticketRoutes = {

            createDropdown: "{{ route('ticket.create-dropdown') }}",

            categorySearch: "{{ route('ticket.categorySearch') }}",

            subcategorySearch: "{{ route('ticket.subcategorySearch') }}",

            prioritySearch: "{{ route('ticket.prioritySearch') }}",

            locationSearch: "{{ route('ticket.locationSearch') }}",

            subLocationSearch: "{{ route('ticket.subLocationSearch') }}",

            picSearch: "{{ route('ticket.picSearch') }}",

            store: "{{ route('ticket.store') }}",

            response: "{{ url('/ticket/response') }}/:eid",

            process: "{{ url('/ticket/process') }}/:eid",

            pending: "{{ url('/ticket/pending') }}/:eid",

            envision: "{{ url('/ticket/envision') }}/:eid",

            transfer: "{{ url('/ticket/transfer') }}/:eid",

            complete: "{{ url('/ticket/complete') }}/:eid",

            reopen: "{{ url('/ticket/reopen') }}/:eid",

            detail: "{{ url('/ticket/detail') }}/:eid",

            tracking: "{{ url('/ticket/tracking') }}/:eid",

            comments: "{{ url('/ticket/comments') }}/:eid",
        };
    </script>
    <script src="{{ asset('assets/js/ticket/core.js') }}"></script>

    <script src="{{ asset('assets/js/ticket/helper.js') }}"></script>
    <script src="{{ asset('assets/js/ticket/modal.js') }}"></script>

    <script src="{{ asset('assets/js/ticket/datatable.js') }}"></script>
    <script src="{{ asset('assets/js/ticket/detail-modal.js') }}"></script>

    <script src="{{ asset('assets/js/ticket/select.js') }}"></script>
    <script src="{{ asset('assets/js/ticket/request-form.js') }}"></script>
    <script src="{{ asset('assets/js/ticket/edit-form.js') }}"></script>

    <script src="{{ asset('assets/js/ticket/attachment.js') }}"></script>
    <script src="{{ asset('assets/js/ticket/comment.js') }}"></script>

    <script src="{{ asset('assets/js/ticket/process.js') }}"></script>
    <script src="{{ asset('assets/js/ticket/response.js') }}"></script>
    <script src="{{ asset('assets/js/ticket/transfer.js') }}"></script>

    <script src="{{ asset('assets/js/ticket/init.js') }}"></script>
</x-app-layout>
