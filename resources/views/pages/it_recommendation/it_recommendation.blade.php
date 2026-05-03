<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2">
        {{-- Status Card --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">

            {{-- All --}}
            <a href="#" class="status-filter group block h-full text-left" data-status="">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                        📄
                    </div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">
                            All
                        </p>
                    </div>

                    <p class="shrink-0 text-base font-bold">
                        {{ $all }}
                    </p>

                </div>
            </a>


            {{-- Waiting IT --}}
            <a href="#" class="status-filter group block h-full text-left" data-status="W">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-amber-700 bg-amber-200/20 p-3 text-amber-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-amber-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                        🛠️
                    </div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">
                            Waiting IT
                        </p>
                    </div>

                    <p class="shrink-0 text-base font-bold">
                        {{ $waitingIT }}
                    </p>

                </div>
            </a>

            {{-- Waiting Approval --}}
            <a href="#" class="status-filter group block h-full text-left" data-status="P">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                        ⏳
                    </div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">
                            Waiting Approval
                        </p>
                    </div>

                    <p class="shrink-0 text-base font-bold">
                        {{ $waitingApproval }}
                    </p>

                </div>
            </a>

            {{-- Revise --}}
            <a href="#" class="status-filter group block h-full text-left" data-status="D">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                        ✏️
                    </div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">
                            Revise
                        </p>
                    </div>

                    <p class="shrink-0 text-base font-bold">
                        {{ $revise }}
                    </p>

                </div>
            </a>


            {{-- Rejected --}}
            <a href="#" class="status-filter group block h-full text-left" data-status="R">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                        ⛔️
                    </div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">
                            Rejected
                        </p>
                    </div>

                    <p class="shrink-0 text-base font-bold">
                        {{ $reject }}
                    </p>

                </div>
            </a>

            {{-- Completed --}}
            <a href="#" class="status-filter group block h-full text-left" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

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

        </div>
        {{-- Table --}}
        <div
            class="mt-4 flex flex-col gap-4 overflow-hidden rounded-lg border border-gray-200 bg-white/90 p-4 shadow-sm dark:border-white/10 dark:bg-[#0f172a]/80">

            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                <div>
                    <h1 class="text-lg font-bold tracking-tight text-gray-800 dark:text-white">
                        IT Recommendation
                    </h1>
                </div>

                <a href="{{ url('/createitrecommendation') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">

                    <i class="fa-solid fa-plus mr-2"></i>
                    Create Request

                </a>

            </div>

            <div class="overflow-x-auto">

                <table id="itrTable" class="w-full text-sm">

                    <thead
                        class="border-b border-gray-200 bg-gray-50/80 text-xs uppercase tracking-wider text-gray-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-400">

                        <tr>

                            <th class="w-10"></th>

                            <th class="px-6 py-4 text-left font-semibold">
                                Doc ID
                            </th>

                            <th class="px-6 py-4 text-left font-semibold">
                                Date
                            </th>

                            <th class="px-6 py-4 text-left font-semibold">
                                Ticket
                            </th>

                            <th class="px-6 py-4 text-left font-semibold">
                                Company
                            </th>

                            <th class="px-6 py-4 text-left font-semibold">
                                Department
                            </th>

                            <th class="px-6 py-4 text-left font-semibold">
                                Requester
                            </th>

                            <th class="px-6 py-4 text-left font-semibold">
                                Purpose
                            </th>

                            <th class="px-6 py-4 text-left font-semibold">
                                IT PIC
                            </th>

                            <th class="px-6 py-4 text-left font-semibold">
                                Status
                            </th>

                            @if ($isITHardware)
                            <th class="px-6 py-4 text-center font-semibold">
                                Action
                            </th>
                            @endif

                        </tr>

                    </thead>

                    <tbody></tbody>

                </table>

            </div>

        </div>

        {{-- Create Modal --}}
        <div id="createModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 px-3 py-6">

            <div
                class="relative flex max-h-[95vh] w-full max-w-5xl flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>
                        <h2 id="createmodaltitle" class="text-lg font-bold text-gray-800 dark:text-white">
                            Create IT Recommendation
                        </h2>
                        <p id="createmodaldesc" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Submit request for IT recommendation process
                        </p>
                    </div>

                    <button id="btnCloseCreateModal" type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-sm"></i>

                    </button>

                </div>

                <form id="createForm" class="flex min-h-0 flex-1 flex-col overflow-hidden">

                    @csrf

                    <div class="flex-1 overflow-y-auto px-6 py-5">

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                            <div>
                                <label class="req mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Company
                                </label>

                                <select name="cpny_id" id="create_cpny_id" required
                                    {{ count($usercpny) === 1 ? 'disabled' : '' }}
                                    class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white">

                                    <option value="">
                                        Select Company
                                    </option>

                                    @foreach ($usercpny as $row)
                                        <option value="{{ $row->cpny_id }}"
                                            {{ count($usercpny) === 1 ? 'selected' : '' }}>
                                            {{ $row->cpny_id }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div>
                                <label class="req mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Department
                                </label>

                                <select name="department_id" id="create_department_id" required
                                    {{ count($userdept) === 1 ? 'disabled' : '' }}
                                    class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white">

                                    <option value="">
                                        Select Department
                                    </option>

                                    @foreach ($userdept as $row)
                                        <option value="{{ $row->department_id }}"
                                            {{ count($userdept) === 1 ? 'selected' : '' }}>
                                            {{ $row->department_id }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div>
                                <label class="req mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Ticket Number
                                </label>

                                <select name="ticketnbr" id="create_ticketnbr"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                    required>

                                    <option value="">
                                        Select Ticket
                                    </option>

                                    @foreach ($ticketOptions as $ticket)
                                        <option value="{{ $ticket }}">
                                            {{ $ticket }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Asset Number
                                </label>

                                <input type="text" name="assetnbr" id="create_assetnbr" placeholder="Optional"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white">
                            </div>

                            <div class="md:col-span-2">
                                <label class="req mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Purpose / Requirement
                                </label>

                                <textarea name="keperluan" id="create_keperluan" rows="5" placeholder="Describe requirement..."
                                    class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                    required></textarea>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Attachments
                                </label>

                                <input type="file" id="create_attachments" name="attachments[]" multiple
                                    class="block w-full rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-4 text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-700 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300">

                                <div id="createAttachmentPreview" class="mt-4 flex flex-wrap gap-2"></div>
                            </div>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-between gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">


                        <button id="btnCancelCreate" type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">

                            Cancel

                        </button>

                        <div class="flex gap-4">
                        <button id="btnCancelRequest" type="button"
                            class="hidden items-center justify-center rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                            <i class="fa-solid fa-ban mr-2"></i>
                            Cancel Document

                        </button>

                        <button id="btnSubmitCreate" type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">

                            <i class="fa-solid fa-paper-plane mr-2"></i>
                            Submit Request

                        </button>

                        </div>


                    </div>

                </form>

            </div>

        </div>

        {{-- View Modal --}}
<div id="showModal"
class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/40 p-3">

            <div
                class="relative flex max-h-[70vh] w-full max-w-7xl flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">

                <div
                    class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-3 dark:border-white/10">

                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2">

                            <h2 id="show_docid"
                                class="truncate text-lg font-semibold tracking-tight text-gray-800 dark:text-white">
                                -
                            </h2>

                            <span id="show_status_badge"></span>

                        </div>

                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            IT Recommendation Detail
                        </p>

                    </div>

                    <div class="flex items-center gap-2">

                        <div id="show_header_actions" class="flex items-center gap-2"></div>

                        <button id="btnCloseShowModal" type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white">

                            <i class="fa-solid fa-xmark text-sm"></i>

                        </button>

                    </div>

                    {{-- <button id="btnCloseShowModal" type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-sm"></i>

                    </button> --}}

                </div>

                <div class="flex-1 overflow-y-auto overflow-x-visible">

                    <div class="grid gap-4 p-4 lg:grid-cols-[1.8fr_0.9fr]">

                        <div class="space-y-2">

                            {{-- INFORMATION --}}
                            <div
                                class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                                <div
                                    class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                    <h3
                                        class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                        Request Information
                                    </h3>

                                </div>

                                <div id="show_information"
                                    class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm md:grid-cols-3">
                                </div>

                            </div>

                            <div
                                class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                                <div
                                    class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                    <h3
                                        class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                        IT Recommendation
                                    </h3>

                                </div>

                                <div id="show_recommendation_info"
                                    class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm md:grid-cols-3">
                                </div>

                            </div>

                            {{-- RECOMMENDATION ITEMS --}}
                            <div
                                class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                                <div
                                    class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                    <h3
                                        class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                        Recommendation Items
                                    </h3>

                                </div>

                                <div class="overflow-x-auto">

                                    <table class="w-full text-sm">

                                        <thead
                                            class="border-b border-gray-100 text-[11px] uppercase tracking-[0.15em] text-gray-400 dark:border-white/5 dark:text-gray-500">

                                            <tr>

                                                <th class="w-56 px-3 py-2 text-left font-semibold">
                                                    Description
                                                </th>

                                                <th class="px-3 py-2 text-left font-semibold">
                                                    Qty
                                                </th>

                                                <th class="px-3 py-2 text-left font-semibold">
                                                    UOM
                                                </th>

                                                <th class="px-3 py-2 text-left font-semibold">
                                                    Category
                                                </th>

                                                <th class="px-3 py-2 text-left font-semibold">
                                                    Note
                                                </th>

                                            </tr>

                                        </thead>

                                        <tbody id="show_detail_items"></tbody>

                                    </table>

                                </div>

                            </div>

                            {{-- ATTACHMENT --}}
                            <div
                                class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                                <div
                                    class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                    <h3
                                        class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                        Attachments
                                    </h3>

                                </div>

                                <div id="show_attachments" class="flex flex-wrap gap-2">
                                </div>

                            </div>

                        </div>

                        <div class="space-y-2">

                            {{-- TIMELINE --}}
                            <div
                                class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                                <div
                                    class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                    <h3
                                        class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                        Approval Timeline
                                    </h3>

                                </div>

                                <div id="show_tracking" class="space-y-2">
                                </div>

                            </div>

                            {{-- ACTION --}}
                            <div class="space-y-2">

                                <div id="commentSection"
                                    class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                                    <div
                                        class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                        <h3
                                            class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                            Comments
                                        </h3>

                                    </div>
                                    <div id="show_comments" class="max-h-[150px] space-y-2 overflow-y-auto pr-1">
                                    </div>
                                    <div class="mt-4">

                                        <textarea id="comment_message" rows="1" placeholder="Write comment..."
                                            class="w-full rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:bg-white/[0.03] dark:text-white"></textarea>

                                    </div>

                                    <button type="button" id="btnSubmitComment"
                                        class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">

                                        <i class="fa-solid fa-paper-plane text-xs"></i>
                                        Submit Comment

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Process Modal --}}
        <div id="processModal"
            class="fixed inset-0 z-[90] hidden items-center justify-center overflow-y-auto bg-black/40 p-4 ">

            {{-- <div
                class="relative mx-auto my-6 flex min-h-[85vh] w-full max-w-7xl flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]"> --}}

            <div
                class="relative mx-auto my-6 flex h-auto w-full max-w-7xl flex-col overflow-visible rounded-lg border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">

                {{-- HEADER --}}
                <div class="flex items-start justify-between border-b border-gray-200 px-5 py-3 dark:border-white/10">

                    <div class="min-w-0">

                        <div class="flex items-center gap-2">

                            <h2 id="process_docid"
                                class="truncate text-lg font-semibold tracking-tight text-gray-800 dark:text-white">
                                Process IT Recommendation
                            </h2>

                            <span id="process_status_badge"></span>

                        </div>

                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            IT Hardware recommendation processing
                        </p>

                    </div>

                    <div class="flex items-center gap-2">

                        <button type="submit" form="processForm" id="btnSubmitProcess"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">

                            <i class="fa-solid fa-gears text-xs"></i>
                            Submit

                        </button>

                        <button type="button" id="btnReviseProcess"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">

                            <i class="fa-solid fa-rotate-left text-xs"></i> Revise

                        </button>

                        <button type="button" id="btnRejectProcess"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                            <i class="fa-solid fa-xmark text-xs"></i> Reject

                        </button>

                        <button id="btnCloseProcessModal" type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white">

                            <i class="fa-solid fa-xmark text-sm"></i>

                        </button>

                    </div>

                </div>

                {{-- BODY --}}
                <form id="processForm" class="flex min-h-0 flex-1 flex-col overflow-visible">

                    @csrf

                    <input type="hidden" id="process_hash">

                    <div class="flex-1 overflow-y-auto overflow-x-visible">

                        <div class="space-y-4 p-4">

                            {{-- LEFT --}}
                            <div class="space-y-2">

                                <div class="grid gap-4 lg:grid-cols-[1.8fr_0.9fr]">

                                    {{-- REQUEST INFO --}}
                                    <div
                                        class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                                        <div
                                            class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                            <h3
                                                class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                                Request Information
                                            </h3>

                                        </div>

                                        <div id="process_information"
                                            class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm md:grid-cols-3">
                                        </div>

                                    </div>

                                    {{-- ATTACHMENTS --}}
                                    <div
                                        class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                                        <div
                                            class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                            <h3
                                                class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                                Attachments
                                            </h3>

                                        </div>

                                        <div id="process_attachments" class="flex flex-wrap gap-2">
                                        </div>

                                    </div>

                                </div>

                                {{-- RECOMMENDATION --}}
                                <div
                                    class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                                    <div
                                        class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                        <h3
                                            class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                            Recommendation
                                        </h3>

                                    </div>

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                        <div>
                                            <label
                                                class="req mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500">
                                                Recommendation Type
                                            </label>

                                            <select name="recommend_type" id="recommend_type"
                                                class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                                required>

                                                <option value="">
                                                    Select Type
                                                </option>

                                                <option value="New Purchase">
                                                    New Purchase
                                                </option>

                                                <option value="Repair">
                                                    Repair
                                                </option>

                                                <option value="Replacement">
                                                    Replacement
                                                </option>

                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500">
                                                Warranty
                                            </label>

                                            <input type="text" name="waranty" id="waranty"
                                                placeholder="Optional"
                                                class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white">
                                        </div>

                                        <div class="md:col-span-2">

                                            <label
                                                class="req mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500">
                                                Recommendation
                                            </label>

                                            <textarea name="recommendation" id="recommendation" rows="5" placeholder="Write recommendation..."
                                                class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                                required></textarea>

                                        </div>

                                    </div>

                                </div>

                                {{-- ITEMS --}}
                                <div
                                    class="relative z-50 rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                                    <div
                                        class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                        <h3
                                            class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                            Recommendation Items
                                        </h3>

                                        <button type="button" id="btnAddItem"
                                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">

                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                            Add Item

                                        </button>

                                    </div>
                                    <div class="overflow-visible">

                                        <table class="w-full text-sm">

                                            <thead
                                                class="border-b border-gray-100 text-[11px] uppercase tracking-[0.15em] text-gray-400 dark:border-white/5 dark:text-gray-500">

                                                <tr>

                                                    <th class="px-3 py-2 text-left">
                                                        Inventory
                                                    </th>

                                                    <th class="px-3 py-2 text-left">
                                                        Qty
                                                    </th>

                                                    <th class="px-3 py-2 text-left">
                                                        UOM
                                                    </th>

                                                    <th class="px-3 py-2 text-left">
                                                        Note
                                                    </th>

                                                    <th class="w-14 px-3 py-2 text-center">

                                                    </th>

                                                </tr>

                                            </thead>

                                            <tbody id="process_detail_body"></tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>

        {{-- Edit Recommendation Modal --}}
        <div id="editRecommendationModal"
            class="fixed inset-0 z-[95] hidden items-center justify-center overflow-y-auto bg-black/40 p-4" >

            <div
                class="relative mx-auto my-6 flex h-auto w-full max-w-7xl flex-col overflow-visible rounded-lg border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">

                <div class="flex items-start justify-between border-b border-gray-200 px-5 py-3 dark:border-white/10">

                    <div>

                        <div class="flex items-center gap-2">

                            <h2 id="edit_recommendation_docid"
                                class="text-lg font-semibold tracking-tight text-gray-800 dark:text-white">

                                Revise Recommendation

                            </h2>

                            <span id="edit_recommendation_status"></span>

                        </div>

                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            Update recommendation based on approver revision request
                        </p>

                    </div>

                    <button id="btnCloseEditRecommendationModal" type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-sm"></i>

                    </button>

                </div>

                <form id="editRecommendationForm" class="flex min-h-0 flex-1 flex-col overflow-visible">

                    @csrf

                    <input type="hidden" id="edit_recommendation_hash">

                    <div class="flex-1 space-y-4 overflow-y-auto p-4">

                    <div
                        class="rounded-xl border border-orange-200/70 bg-orange-50/60 p-3 dark:border-orange-500/20 dark:bg-orange-500/10">

                        <div class="flex items-start gap-3">

                            <div class="mt-0.5 text-orange-500">
                                <i class="fa-solid fa-rotate-left"></i>
                            </div>

                            <div>

                                <h3
                                    class="text-xs font-semibold uppercase tracking-[0.15em] text-orange-700 dark:text-orange-300">

                                    Revision Requested by Approver

                                </h3>

                                <p class="mt-1 text-sm text-orange-600 dark:text-orange-200">

                                    Please review and update recommendation before resubmitting approval.

                                </p>

                                <div id="revision_note_container" class="mt-3"></div>

                            </div>

                        </div>

                    </div>

                    <div class="grid gap-4 lg:grid-cols-[1.8fr_0.9fr]">

                        {{-- REQUEST INFO --}}
                        <div
                            class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                            <div
                                class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                <h3
                                    class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">

                                    Request Information

                                </h3>

                            </div>

                            <div id="edit_recommendation_information"
                                class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm md:grid-cols-3">
                            </div>

                        </div>

                        {{-- ATTACHMENTS --}}
                        <div
                            class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:border-white/[0.06] dark:bg-white/[0.03]">

                            <div
                                class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                <h3
                                    class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">

                                    Attachments

                                </h3>

                            </div>

                            <div id="edit_recommendation_attachments" class="flex flex-wrap gap-2">
                            </div>

                        </div>

                    </div>
                        <div
                            class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:bg-white/[0.03]">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                <div>

                                    <label
                                        class="req mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500">
                                        Recommendation Type
                                    </label>

                                    <select id="edit_recommend_type"
                                        class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm dark:border-white/10 dark:bg-[#111827] dark:text-white">

                                        <option value="New Purchase">New Purchase</option>
                                        <option value="Repair">Repair</option>
                                        <option value="Replacement">Replacement</option>

                                    </select>

                                </div>

                                <div>

                                    <label
                                        class="mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500">
                                        Warranty
                                    </label>

                                    <input type="text" id="edit_waranty"
                                        class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm dark:border-white/10 dark:bg-[#111827] dark:text-white">

                                </div>

                                <div class="md:col-span-2">

                                    <label
                                        class="req mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-gray-500">
                                        Recommendation
                                    </label>

                                    <textarea id="edit_recommendation" rows="5"
                                        class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-[#111827] dark:text-white"></textarea>

                                </div>

                            </div>

                        </div>

                        <div
                            class="rounded-xl border border-gray-100/80 bg-white/70 p-4 dark:border-white/10 dark:bg-white/[0.03]">

                            <div
                                class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2 dark:border-white/5">

                                <h3
                                    class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">

                                    Recommendation Items

                                </h3>

                                <button type="button" id="btnAddEditItem"
                                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">

                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                    Add Item

                                </button>

                            </div>

                            <table class="w-full text-sm">
                                <tbody id="edit_recommendation_detail_body"></tbody>
                            </table>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-4 dark:border-white/10">

                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">

                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            Resubmit Approval

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    {{-- Listing  Script --}}
    <script>
        let statusFilter = '';
        let table;
        let currentDetailHash = null;
        let editMode = false;
        let editHash = null;

        const currentUser = "{{ auth()->user()->username }}";

        const isITHardware = @json($isITHardware);

        $(document).ready(function() {

            table = $('#itrTable').DataTable({

                processing: true,
                serverSide: true,
                deferRender: true,

                pageLength: 10,

                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],

                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_ITRecommendation',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'List_ITRecommendation',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],

                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },

                columnDefs: [{
                    targets: 0,
                    className: 'dtr-control',
                    orderable: false,
                    width: '28px'
                }],

                ajax: {
                    url: "{{ route('it-recommendation.json') }}",
                    type: 'GET',
                    data: function(d) {
                        d.status = statusFilter ?? '';
                    }
                },

                order: [
                    [1, 'desc']
                ],

                columns: [

                    {
                        data: null,
                        defaultContent: ''
                    },

                    {
                        data: 'docid',
                        render: function(data, type, row) {

                            let url = `/showitrecommendation/${row.eid}`;

                            let cls =
                                'inline-flex items-center rounded-lg bg-slate-700 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-800';

                            if (row.can_edit) {

                                url = `/edititrecommendation/${row.eid}`;

                                cls =
                                    'inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-amber-600';
                            }

                            return `
                                <div class="flex items-center gap-2">

                                    <a href="${url}" class="${cls}">
                                        ${data}
                                    </a>

                                </div>
                            `;
                        }
                    },

                    {
                        data: 'itrecommend_date',
                        className: 'whitespace-nowrap text-gray-700 dark:text-gray-300',
                        render: function(data) {

                            if (!data) {
                                return '-';
                            }

                            const date = new Date(data);

                            return date.toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });

                        }
                    },

                    {
                        data: 'ticketnbr',
                        defaultContent: '-',
                        className: 'whitespace-nowrap text-gray-700 dark:text-gray-300'
                    },

                    {
                        data: 'cpny_id',
                        className: 'whitespace-nowrap text-gray-700 dark:text-gray-300'
                    },

                    {
                        data: 'department_id',
                        className: 'whitespace-nowrap text-gray-700 dark:text-gray-300'
                    },

                    {
                        data: 'user_peminta',
                        className: 'whitespace-nowrap text-gray-700 dark:text-gray-300'
                    },

                    {
                        data: 'keperluan',
                        className: 'max-w-[320px] whitespace-normal break-words text-gray-700 dark:text-gray-300'
                    },

                    {
                        data: 'recommend_pic',
                        defaultContent: '-',
                        className: 'whitespace-nowrap text-gray-700 dark:text-gray-300'
                    },

                    {
                        data: 'status',
                        className: 'whitespace-nowrap',
                        render: function(data) {

                        const map = {

                            'S': {
                                text: 'Submitted',
                                cls: 'bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-300'
                            },

                            'IT': {
                                text: 'Processed',
                                cls: 'bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300'
                            },

                            'A': {
                                text: 'Approved',
                                cls: 'bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300'
                            },

                            'W': {
                                text: 'Waiting IT',
                                cls: 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-300'
                            },

                            'I': {
                                text: 'Waiting IT Revision',
                                cls: 'bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-500/10 dark:border-orange-500/20 dark:text-orange-300'
                            },

                            'P': {
                                text: 'Waiting Approval',
                                cls: 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-300'
                            },

                            'C': {
                                text: 'Completed',
                                cls: 'bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300'
                            },

                            'R': {
                                text: 'Rejected',
                                cls: 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-300'
                            },

                            'D': {
                                text: 'Revise',
                                cls: 'bg-gray-100 text-gray-700 border border-gray-200 dark:bg-white/10 dark:border-white/10 dark:text-gray-300'
                            },

                            'X': {
                                text: 'Cancelled',
                                cls: 'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-500/10 dark:border-slate-500/20 dark:text-slate-300'
                            }

                        };

                            const item = map[data] || {
                                text: data ?? '-',
                                cls: 'bg-gray-100 text-gray-700 border border-gray-200'
                            };

                            return `
                            <span class="inline-flex min-w-[150px] items-center justify-center rounded-full px-3 py-1.5 text-xs font-semibold ${item.cls}">
                                ${item.text}
                            </span>
                        `;
                        }
                    },

                    ...(isITHardware ? [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {

                            if (!row.can_process) {
                                return '-';
                            }

                            const isRevision = row.status === 'I';

                            return `
                                <div class="flex items-center justify-center">

                                    <button
                                        type="button"
                                        class="${
                                            isRevision
                                                ? 'edit-recommendation-btn bg-orange-500 hover:bg-orange-600'
                                                : 'process-btn bg-indigo-600 hover:bg-indigo-700'
                                        } inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold text-white shadow-sm transition"
                                        data-id="${row.eid}"
                                        title="${isRevision ? 'Edit Recommendation' : 'Process'}">

                                        <i class="fa-solid ${
                                            isRevision
                                                ? 'fa-rotate-left'
                                                : 'fa-gears'
                                        }"></i>

                                        ${
                                            isRevision
                                                ? 'Edit Recommendation'
                                                : 'Process'
                                        }

                                    </button>

                                </div>
                            `;
                        }
                    }] : [])
                ],

                createdRow: function(row) {

                    $(row).addClass(
                        'border-b border-gray-100 transition hover:bg-gray-50/70 dark:border-white/5 dark:hover:bg-white/[0.03]'
                    );

                },

                searchDelay: 400,
                stateSave: true,
                responsive: true

            });

            $('.status-filter').on('click', function(e) {

                e.preventDefault();

                $('.status-filter').removeClass('active');

                $(this).addClass('active');

                statusFilter = $(this).data('status') || '';

                table.ajax.reload(null, true);

            });

        });
    </script>

    {{-- Create Modal Script --}}
    <script>
        $(document).ready(function() {

            $(document).on('click', 'a[href*="/createitrecommendation"]', function(e) {

                e.preventDefault();

                openCreateModal();

            });

            $(document).on('click', 'a[href*="/edititrecommendation/"]', function(e) {

                e.preventDefault();

                const hash = $(this).attr('href').split('/').pop();

                window.history.pushState({}, '', $(this).attr('href'));

                openEditModal(hash);

            });

            $('#btnCloseCreateModal, #btnCancelCreate').on('click', function() {
                closeCreateModal();
            });

            // $('#createModal').on('click', function(e) {

            //     if (e.target.id === 'createModal') {
            //         closeCreateModal();
            //     }

            // });

            // $(document).on('keydown', function(e) {

            //     if (e.key === 'Escape') {
            //         closeCreateModal();
            //     }

            // });

            $('#create_attachments').on('change', function() {

                const files = Array.from(this.files || []);

                let html = '';

                files.forEach(file => {

                    const size = (file.size / 1024 / 1024).toFixed(2);

                    html += `
                    <div class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300">

                        <i class="fa-solid fa-paperclip"></i>

                        <span class="max-w-[200px] truncate">
                            ${file.name}
                        </span>

                        <span class="text-gray-400">
                            ${size} MB
                        </span>

                    </div>
                `;
                });

                $('#createAttachmentPreview').html(html);

            });
            $('#createForm').on('submit', function(e) {

                e.preventDefault();

                const btn = $('#btnSubmitCreate');

                btn.prop('disabled', true)
                    .html(`
            <i class="fa-solid fa-spinner fa-spin mr-2"></i>
            Submitting...
        `);

                $('#create_cpny_id').prop('disabled', false);
                $('#create_department_id').prop('disabled', false);

                const formData = new FormData(this);
                if (editMode) {
                    formData.append('_method', 'PUT');
                }

                $.ajax({

                    url: editMode ?
                        `/it-recommendation/update/${editHash}` :
                        "{{ route('it-recommendation.store') }}",

                    type: 'POST',

                    data: formData,

                    processData: false,
                    contentType: false,

                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },

                    success: function(res) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message || 'Request saved successfully',
                            timer: 1800,
                            showConfirmButton: false
                        });

                        closeCreateModal();

                        table.ajax.reload(null, false);

                    },

                    error: function(xhr) {

                        let msg = 'Failed to save request';

                        if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }

                        if (xhr.status === 422 && xhr.responseJSON?.errors) {

                            msg = Object.values(xhr.responseJSON.errors)
                                .flat()
                                .join('<br>');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: msg
                        });

                    },

                    complete: function() {

                        btn.prop('disabled', false)
                            .html(`
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    ${editMode ? 'Update Request' : 'Submit Request'}
                `);

                    }

                });

            });


            const path = window.location.pathname;

            if (path.includes('/createitrecommendation')) {
                openCreateModal();
            }


            $('#btnCancelRequest').on('click', async function() {

                if (!editHash) return;

                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Cancel Document?',
                    text: 'This document will be cancelled, and this action cannot be undone !',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Cancel',
                    confirmButtonColor: '#dc2626'
                });

                if (!result.isConfirmed) return;

                try {

                    await $.ajax({

                        url: `/it-recommendation/cancel/${editHash}`,
                        type: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }

                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Document cancelled',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeCreateModal();

                    table.ajax.reload(null, false);

                } catch (err) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Failed cancel document'
                    });

                }

            });

        });

        function openCreateModal() {

            $('#createModal')
                .removeClass('hidden')
                .addClass('flex');

            $('body').addClass('overflow-hidden');

            $('#createmodaltitle').text(
                editMode ? 'Edit IT Recommendation' : 'Create IT Recommendation'
            );

            $('#createmodaldesc').text(
                editMode ?
                'Update IT recommendation request' :
                'Submit request for IT recommendation process'
            );

            $('#btnSubmitCreate').html(
                editMode ?
                '<i class="fa-solid fa-floppy-disk mr-2"></i>Update Request' :
                '<i class="fa-solid fa-paper-plane mr-2"></i>Submit Request'
            );
            if (editMode && editStatus === 'D') {
                $('#btnCancelRequest')
                    .removeClass('hidden')
                    .addClass('inline-flex');
            } else {
                $('#btnCancelRequest')
                    .removeClass('inline-flex')
                    .addClass('hidden');
            }
        }

        function closeCreateModal() {

            editMode = false;
            editHash = null;
            editStatus = null;
            $('#createModal')
                .removeClass('flex')
                .addClass('hidden');

            if (
                $('#createModal').hasClass('hidden') &&
                $('#showModal').hasClass('hidden') &&
                $('#processModal').hasClass('hidden') &&
                $('#editRecommendationModal').hasClass('hidden')
            ) {
                $('body').removeClass('overflow-hidden');
            }

            $('#createForm')[0].reset();
            if ($('#create_cpny_id option').length === 2) {
                $('#create_cpny_id').val($('#create_cpny_id option:eq(1)').val());
            }

            if ($('#create_department_id option').length === 2) {
                $('#create_department_id').val($('#create_department_id option:eq(1)').val());
            }

            $('#createAttachmentPreview').html('');


        }

        async function openEditModal(hash) {

            try {

                const res = await $.ajax({
                    url: `/it-recommendation/detail/${hash}`,
                    type: 'GET'
                });

                const h = res.header;

                editMode = true;
                editHash = hash;
                editStatus = h.status;

                $('#create_cpny_id').val(h.cpny_id);
                $('#create_department_id').val(h.department_id);
                $('#create_ticketnbr').val(h.ticketnbr);
                $('#create_assetnbr').val(h.assetnbr);
                $('#create_keperluan').val(h.keperluan);

                openCreateModal();

            } catch (err) {

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.responseJSON?.message || 'Failed load edit data'
                });

            }

        }
    </script>

    {{-- View Modal Script --}}
    <script>
        function statusBadge(status) {
            const raw = status;

            if (
                raw === 'Processed'
            ) {
                return `
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300">
                                Processed
                            </span>
                        `;
            }
            const map = {

                'S': {
                    text: 'Submitted',
                    cls: 'bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-300'
                },

                'IT': {
                    text: 'Processed',
                    cls: 'bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300'
                },

                'A': {
                    text: 'Approved',
                    cls: 'bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300'
                },

                'W': {
                    text: 'Waiting IT',
                    cls: 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-300'
                },

                'I': {
                    text: 'Waiting IT Revision',
                    cls: 'bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-500/10 dark:border-orange-500/20 dark:text-orange-300'
                },

                'P': {
                    text: 'Waiting Approval',
                    cls: 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-300'
                },

                'C': {
                    text: 'Completed',
                    cls: 'bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300'
                },

                'R': {
                    text: 'Rejected',
                    cls: 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-300'
                },

                'D': {
                    text: 'Revise',
                    cls: 'bg-gray-100 text-gray-700 border border-gray-200 dark:bg-white/10 dark:border-white/10 dark:text-gray-300'
                },

                'X': {
                    text: 'Cancelled',
                    cls: 'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-500/10 dark:border-slate-500/20 dark:text-slate-300'
                }

            };

            const item = map[status] || {
                text: status ?? '-',
                cls: 'bg-gray-100 text-gray-700 border border-gray-200'
            };

            return `
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${item.cls}">
                        ${item.text}
                    </span>
                `;
        }

        function timelineIcon(status) {

            const map = {

                'S': 'fa-paper-plane text-indigo-500',
                'IT': 'fa-gears text-green-500',
                'A': 'fa-check text-green-500',
                'W': 'fa-clock text-amber-500',
                'I': 'fa-rotate-left text-orange-500',
                'P': 'fa-hourglass-half text-blue-500',
                'C': 'fa-circle-check text-green-500',
                'R': 'fa-xmark text-red-500',
                'D': 'fa-pen text-gray-500',
                'X': 'fa-ban text-slate-500'

            };

            return map[status] || 'fa-circle text-gray-400';

        }
        $(document).ready(function() {






            function infoItem(label, value) {

                return `
                    <div class="min-w-0">

                        <div class="text-[11px] font-semibold uppercase tracking-[0.15em] text-gray-400">
                            ${label}
                        </div>

                        <div class="mt-1 break-words text-sm text-gray-700 dark:text-gray-200">
                            ${value ?? '-'}
                        </div>

                    </div>
                `;

            }

            async function loadDetail(hash) {

                currentDetailHash = hash;

                try {

                    const res = await $.ajax({

                        url: `/it-recommendation/detail/${hash}`,
                        type: 'GET'

                    });

                    const h = res.header;

                    $('#process_status_badge').html(
                        statusBadge(h.status)
                    );

                    $('#process_hash').val(hash);

                    $('#process_docid').text(
                        `${h.docid}`
                    );

                    renderHeaderInfo(h);

                    renderRecommendationInfo(h);

                    renderDetailItems(res.details);

                    renderAttachments(res.attachments);

                    const tracking = await $.ajax({

                        url: `/it-recommendation/tracking/${h.docid}`,
                        type: 'GET'

                    });

                    renderTimeline(tracking);

                        if (['X'].includes(h.status)) {

                            $('#commentSection').addClass('hidden');

                            $('#show_comments').html('');

                        } else {

                            $('#commentSection').removeClass('hidden');

                            const comments = await $.ajax({

                                url: `/it-recommendation/comments/${h.docid}`,
                                type: 'GET'

                            });

                            renderComments(comments);

                        }
                    renderActions(h, res.permissions, hash);

                    openShowModal();

                } catch (err) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Failed to load detail'
                    });

                }

            }

            function renderHeaderInfo(h) {

                $('#show_docid').text(h.docid);

                $('#show_status_badge').html(
                    statusBadge(h.status)
                );

                $('#show_information').html(`

        ${infoItem(
            'Date',
            h.itrecommend_date
                ? new Date(h.itrecommend_date).toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                })
                : '-'
        )}

        ${infoItem('Company', h.cpny_id)}
        ${infoItem('Department', h.department_id)}
        ${infoItem('Requester', h.user_peminta)}
        ${infoItem('Ticket Number', h.ticketnbr)}
        ${infoItem('Asset Number', h.assetnbr || '-')}
        ${infoItem('IT PIC', h.recommend_pic || '-')}
        ${infoItem('Purpose / Requirement', h.keperluan)}

    `);

            }

            function renderRecommendationInfo(h) {

                $('#show_recommendation_info').html(`

        ${infoItem('Recommendation Type', h.recommend_type || '-')}
        ${infoItem('Warranty', h.waranty || '-')}
        ${infoItem('Recommendation', h.recommendation || '-')}

    `);

            }

            function renderDetailItems(details) {

                let html = '';

                if (details.length === 0) {

                    html = `
            <tr>
                <td colspan="5"
                    class="px-3 py-8 text-center text-sm text-gray-400">
                    No recommendation items
                </td>
            </tr>
        `;

                } else {

                    details.forEach(row => {

                        html += `
                <tr class="border-b border-gray-100 dark:border-white/5">

                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        ${row.recommend_descr ?? '-'}
                    </td>

                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        ${row.qty ?? '-'}
                    </td>

                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        ${row.uom ?? '-'}
                    </td>

                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        ${row.category ?? '-'}
                    </td>

                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        ${row.recommend_note ?? '-'}
                    </td>

                </tr>
            `;

                    });

                }

                $('#show_detail_items').html(html);

            }

            function renderAttachments(files) {

                let html = '';

                if (files.length === 0) {

                    html = `
            <div class="w-full rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">
                No attachments
            </div>
        `;

                } else {

                    files.forEach(file => {

                        html += `
                <a
                    href="${file.signed_url ?? '#'}"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 transition hover:bg-gray-100 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/[0.05]">

                    <i class="fa-solid fa-paperclip text-gray-400"></i>

                    <div class="max-w-[220px] truncate">
                        ${file.filename ?? 'Attachment'}
                    </div>

                </a>
            `;

                    });

                }

                $('#show_attachments').html(html);

            }

            function renderTimeline(tracking) {

                let html = '';

                tracking.forEach(row => {

                    let noteClass =
                        'border-gray-200 bg-gray-50 text-gray-700 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300';

                    if (row.status === 'R') {

                        noteClass =
                            'border-red-200 bg-red-50 text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300';

                    } else if (row.status === 'D' || row.status === 'I') {

                        noteClass =
                            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300';

                    } else if (row.status === 'X') {

                        noteClass =
                            'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-500/20 dark:bg-slate-500/10 dark:text-slate-300';

                    }

                    html += `
                        <div class="flex gap-3">

                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white dark:border-white/10 dark:bg-[#111827]">

                                <i class="fa-solid text-xs ${timelineIcon(row.status)}"></i>

                            </div>

                            <div class="min-w-0 flex-1 pb-2">

                                <div class="flex flex-wrap items-center gap-2">

                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        ${row.title}
                                    </h4>

                                    ${statusBadge(row.status)}

                                </div>

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    ${row.description ?? '-'}
                                </p>

                                <p class="mt-1 text-[11px] text-gray-400">
                                    ${row.date ?? '-'}
                                </p>

                                ${row.note ? `
                                    <div class="
                                        mt-2 rounded-lg px-3 py-2 text-xs leading-relaxed
                                        ${
                                            row.status === 'R'
                                                ? 'border border-red-200 bg-red-50 text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300'
                                            : row.status === 'D' || row.status === 'I'
                                                ? 'border border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300'
                                            : 'border border-gray-200 bg-gray-50 text-gray-700 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300'
                                        }
                                    ">
                                        ${row.note}
                                    </div>
                                ` : ''}

                            </div>

                        </div>
                    `;

                });

                $('#show_tracking').html(html);

            }

            function renderComments(comments) {

                let html = '';

                if (comments.length === 0) {

                    html = `
            <div class="rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">
                No comments yet
            </div>
        `;

                } else {

                    comments.forEach(row => {

                        html += `
                <div class="rounded-lg bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-white/[0.02]">

                    <div class="flex items-center justify-between gap-3">

                        <div class="min-w-0">

                            <div class="truncate text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                ${row.name ?? row.username ?? '-'}
                            </div>

                        </div>

                        <div class="shrink-0 text-[10px] text-gray-400">
                            ${
                                row.message_date
                                    ? new Date(row.message_date).toLocaleString('en-GB', {
                                        day: '2-digit',
                                        month: 'short',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })
                                    : '-'
                            }
                        </div>

                    </div>

                    <div class="mt-1 whitespace-normal break-words text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                        ${row.message ?? '-'}
                    </div>

                </div>
            `;
                    });

                }

                $('#show_comments').html(html);

            }

            function renderActions(h, permissions, hash) {

                let html = '';

                if (permissions.can_edit) {

                    html += `
            <a
                href="/edititrecommendation/${hash}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">

                <i class="fa-solid fa-pen text-xs"></i>
                Edit Request

            </a>
        `;
                }

                if (h.status === 'W' && permissions.can_process && isITHardware) {

                    html += `
            <button
                type="button"
                data-id="${hash}"
                class="process-btn mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">

                <i class="fa-solid fa-gears text-xs"></i>
                Process Request

            </button>
        `;
                }

                if (h.status === 'I' && permissions.can_process && isITHardware) {

                    html += `
            <button
                type="button"
                data-id="${hash}"
                class="edit-recommendation-btn mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600">

                <i class="fa-solid fa-rotate-left text-xs"></i>
                Revise Recommendation

            </button>
        `;
                }

                if (permissions.can_cancel) {

                    html += `
            <button
                type="button"
                data-id="${hash}"
                class="cancel-btn mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">

                <i class="fa-solid fa-ban text-xs"></i>
                Cancel Request

            </button>
        `;
                }

                if (permissions.can_approve) {

                    html += `
            <div class="flex flex-wrap items-center gap-2">

                <button
                    type="button"
                    data-docid="${h.docid}"
                    class="approve-btn inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">

                    <i class="fa-solid fa-check text-xs"></i>
                    Approve

                </button>

                <button
                    type="button"
                    data-docid="${h.docid}"
                    class="revise-approval-btn inline-flex items-center justify-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">

                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    Revise Recommendation

                </button>

                <button
                    type="button"
                    data-docid="${h.docid}"
                    class="reject-approval-btn inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                    <i class="fa-solid fa-xmark text-xs"></i>
                    Reject

                </button>

            </div>
        `;
                }

                if (!html) {

                    $('#show_header_actions')
                        .addClass('hidden')
                        .html('');

                } else {

                    $('#show_header_actions')
                        .removeClass('hidden')
                        .html(html);

                }

            }



            $(document).on('click', '.approve-btn', async function() {

                const docid = $(this).data('docid');

                const result = await Swal.fire({
                    icon: 'question',
                    title: 'Approve Document?',
                    text: 'This action cannot be undone',
                    showCancelButton: true,
                    confirmButtonText: 'Approve',
                    confirmButtonColor: '#16a34a'
                });

                if (!result.isConfirmed) return;

                try {

                    await $.ajax({

                        url: `/it-recommendation/approve/${docid}`,
                        type: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }

                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Approved',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeShowModal();

                    table.ajax.reload(null, false);

                } catch (err) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Failed approve document'
                    });

                }

            });

            $(document).on('click', '.revise-approval-btn', async function() {

                const docid = $(this).data('docid');

                const result = await Swal.fire({
                    title: 'Request Revision',
                    input: 'textarea',
                    inputPlaceholder: 'Write revise reason...',
                    inputAttributes: {
                        required: true
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit Revision'
                });

                if (!result.isConfirmed || !result.value) return;

                try {

                    await $.ajax({

                        url: `/it-recommendation/revise/${docid}`,
                        type: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        data: {
                            note: result.value
                        }

                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Revision Requested',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeShowModal();

                    table.ajax.reload(null, false);

                } catch (err) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Failed revise document'
                    });

                }

            });

            $(document).on('click', '.reject-approval-btn', async function() {

                const docid = $(this).data('docid');

                const result = await Swal.fire({
                    title: 'Reject Document',
                    input: 'textarea',
                    inputPlaceholder: 'Write reject reason...',
                    inputAttributes: {
                        required: true
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    confirmButtonColor: '#dc2626'
                });

                if (!result.isConfirmed || !result.value) return;

                try {

                    await $.ajax({

                        url: `/it-recommendation/reject/${docid}`,
                        type: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        data: {
                            note: result.value
                        }

                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Rejected',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeShowModal();

                    table.ajax.reload(null, false);

                } catch (err) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Failed reject document'
                    });

                }

            });


            $(document).on('click', 'a[href*="/showitrecommendation/"]', function(e) {

                const href = $(this).attr('href');

                if (href.includes('/showitrecommendation/')) {

                    e.preventDefault();

                    const hash = href.split('/').pop();

                    window.history.pushState({}, '', href);

                    loadDetail(hash);
                }

            });


            $('#btnCloseShowModal').on('click', function() {
                closeShowModal();
            });

            // $('#showModal').on('click', function(e) {

            //     if (e.target.id === 'showModal') {
            //         closeShowModal();
            //     }

            // });

            // $(document).on('keydown', function(e) {

            //     if (e.key === 'Escape') {

            //         if ($('#processModal').hasClass('flex')) {
            //             closeProcessModal();
            //         } else if ($('#showModal').hasClass('flex')) {
            //             closeShowModal();
            //         } else if ($('#createModal').hasClass('flex')) {
            //             closeCreateModal();
            //         }

            //     }

            // });

            const path = window.location.pathname;

            if (path.includes('/showitrecommendation/')) {

                const hash = path.split('/').pop();

                loadDetail(hash);

            }

            $(document).on('click', '#btnSubmitComment', async function() {

                const hash = currentDetailHash;

                const message = $('#comment_message').val().trim();

                if (!message) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation',
                        text: 'Comment cannot be empty'
                    });

                    return;
                }

                const btn = $(this);

                btn.prop('disabled', true)
                    .html(`
                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                        Sending...
                    `);

                try {

                    await $.ajax({

                        url: `/it-recommendation/comment/${hash}`,
                        type: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        data: {
                            message: message
                        }

                    });

                    $('#comment_message').val('');

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Comment submitted',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    loadDetail(hash);

                } catch (err) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Failed submit comment'
                    });

                } finally {

                    btn.prop('disabled', false)
                        .html(`
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            Submit Comment
                        `);

                }

            });

        });

        function openShowModal() {

            $('#showModal')
                .removeClass('hidden')
                .addClass('flex');

            $('body').addClass('overflow-hidden');

        }

        function closeShowModal() {

            $('#showModal')
                .removeClass('flex')
                .addClass('hidden');

            if (
                $('#createModal').hasClass('hidden') &&
                $('#showModal').hasClass('hidden') &&
                $('#processModal').hasClass('hidden') &&
                $('#editRecommendationModal').hasClass('hidden')
            ) {
                $('body').removeClass('overflow-hidden');
            }

            $('#show_docid').text('-');
            $('#show_status_badge').html('');
            $('#show_information').html('');
            $('#show_detail_items').html('');
            $('#show_attachments').html('');
            $('#show_tracking').html('');

            $('#show_header_actions')
                .addClass('hidden')
                .html('');

            $('#commentSection')
                .removeClass('hidden');

            $('#show_comments').html('');

            const cleanUrl = "{{ url('/it-recommendation') }}";

            window.history.pushState({}, '', cleanUrl);

        }
    </script>

    {{-- Process Modal Script --}}
    <script>
        $(document).ready(function() {

            // function openProcessModal() {

            //     $('#processModal')
            //         .removeClass('hidden')
            //         .addClass('block');

            //     $('body').addClass('overflow-hidden');

            // }

            // function closeProcessModal() {

            //     $('#processModal')
            //         .removeClass('block')
            //         .addClass('hidden');

            //     $('body').removeClass('overflow-hidden');

            //     $('#processForm')[0].reset();

            //     $('#process_hash').val('');
            //     $('#process_docid').text('Process IT Recommendation');
            //     $('#process_information').html('');
            //     $('#process_attachments').html('');
            //     $('#process_detail_body').html('');

            // }


            function processInfoItem(label, value) {

                return `
                    <div class="min-w-0">

                        <div class="text-[11px] font-semibold uppercase tracking-[0.15em] text-gray-400">
                            ${label}
                        </div>

                        <div class="mt-1 break-words text-sm text-gray-700 dark:text-gray-200">
                            ${value ?? '-'}
                        </div>

                    </div>
                `;

            }

            function addDetailRow(data = {}) {

                const rowId = Date.now() + Math.floor(Math.random() * 1000);

                const html = `
                    <tr class="detail-row border-b border-gray-100 dark:border-white/5">

                        <td class="px-3 py-3 align-top">

                        <div class="relative">

                            <input
                                type="text"
                                class="inventory-search w-full rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-10 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                placeholder="Search inventory name or item code..."
                                autocomplete="off"
                                value="${data.inventory_descr ?? ''}">

                            <input
                                type="hidden"
                                class="inventory-id"
                                value="${data.inventoryid ?? ''}">

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>

                            <div class="inventory-result absolute left-0 bottom-full z-[9999] mt-1 hidden max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-white/10 dark:bg-[#111827]">
                            </div>

                        </div>
                        </td>

                        <td class="px-3 py-3 align-top">

                            <input
                                type="number"
                                min="1"
                                class="item-qty w-24 rounded-lg bg-gray-50 px-3 py-2
                                dark:bg-white/[0.03] text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                value="${data.qty ?? 1}">

                        </td>

                        <td class="px-3 py-3 align-top">

                            <input
                                type="text"
                                class="item-uom w-24 rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-500 outline-none dark:border-white/10 dark:bg-[#1f2937] dark:text-gray-300"
                                readonly
                                value="${data.uom ?? ''}">

                        </td>

                        <td class="px-3 py-3 align-top">

                            <textarea
                                rows="2"
                                class="item-note w-full rounded-lg bg-gray-50 px-3 py-2
                                dark:bg-white/[0.03] text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                placeholder="Optional note">${data.recommend_note ?? ''}</textarea>

                        </td>

                        <td class="px-3 py-3 align-top text-center">

                            <button
                                type="button"
                                class="btn-remove-item inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                                <i class="fa-solid fa-trash text-xs"></i>

                            </button>

                        </td>

                    </tr>
                `;

                $('#process_detail_body').append(html);

            }

            async function loadProcessDetail(hash) {

                try {

                    const res = await $.ajax({

                        url: `/it-recommendation/detail/${hash}`,
                        type: 'GET'

                    });

                    const h = res.header;

                    $('#process_hash').val(hash);

                    $('#process_docid').text(
                        `${h.docid}`
                    );

                    $('#process_information').html(`

                        ${processInfoItem(
                            'Date',
                            h.itrecommend_date
                                ? new Date(h.itrecommend_date).toLocaleDateString('en-GB', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric'
                                })
                                : '-'
                        )}

                        ${processInfoItem('Company', h.cpny_id)}
                        ${processInfoItem('Department', h.department_id)}
                        ${processInfoItem('Requester', h.user_peminta)}
                        ${processInfoItem('Ticket Number', h.ticketnbr)}
                        ${processInfoItem('Asset Number', h.assetnbr || '-')}
                        ${processInfoItem('Purpose / Requirement', h.keperluan)}

                    `);

                    $('#recommend_type').val(h.recommend_type || '');
                    $('#waranty').val(h.waranty || '');
                    $('#recommendation').val(h.recommendation || '');

                    let attachmentHtml = '';

                    if (res.attachments.length === 0) {

                        attachmentHtml = `
                            <div class="w-full rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">
                                No attachments
                            </div>
                        `;

                    } else {

                        res.attachments.forEach(file => {

                            attachmentHtml += `
                                <a
                                    href="${file.signed_url ?? '#'}"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 transition hover:bg-gray-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.05]">

                                    <i class="fa-solid fa-paperclip text-gray-400"></i>

                                    <div class="max-w-[220px] truncate">
                                        ${file.filename ?? 'Attachment'}
                                    </div>

                                </a>
                            `;

                        });

                    }

                    $('#process_attachments').html(attachmentHtml);

                    $('#process_detail_body').html('');

                    if (res.details.length > 0) {

                        res.details.forEach(row => {

                            addDetailRow({
                                inventory_descr: row.recommend_descr,
                                inventoryid: row.inventoryid,
                                qty: row.qty,
                                uom: row.uom,
                                recommend_note: row.recommend_note
                            });

                        });

                    } else {

                        addDetailRow();

                    }

                    openProcessModal();

                } catch (err) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Failed load process data'
                    });

                }

            }

            $(document).on('click', '.process-btn', function() {

                const hash = $(this).data('id');

                window.history.pushState({}, '', `/processitrecommendation/${hash}`);

                loadProcessDetail(hash);

            });

            // $('#process_status_badge').html(
            //     statusBadge(h.status)
            // );

            $('#btnCloseProcessModal').on('click', function() {
                closeProcessModal();
            });

            // $('#processModal').on('click', function(e) {

            //     if (e.target.id === 'processModal') {
            //         closeProcessModal();
            //     }

            // });

            $('#btnAddItem').on('click', function() {

                addDetailRow();

            });

            $(document).on('click', '.btn-remove-item', function() {

                $(this)
                    .closest('tr')
                    .remove();

                if ($('#process_detail_body tr').length === 0) {
                    addDetailRow();
                }

            });

            $(document).on('keyup', '.inventory-search', async function() {

                const input = $(this);

                const keyword = input.val();

                const container = input
                    .closest('td')
                    .find('.inventory-result');

                if (keyword.length < 2) {

                    container
                        .addClass('hidden')
                        .html('');

                    return;

                }

                try {

                    const res = await $.ajax({

                        url: `/it-recommendation/inventory-search`,
                        type: 'GET',
                        data: {
                            q: keyword
                        }

                    });

                    let html = '';

                    if (res.length === 0) {

                        html = `
                            <div class="px-3 py-2 text-xs text-gray-400">
                                No inventory found
                            </div>
                        `;

                    } else {

                        res.forEach(row => {

                            html += `
                            <button
                                type="button"
                                class="inventory-select group flex w-full flex-col gap-1 border-b border-gray-100 px-4 py-3 text-left transition hover:bg-indigo-50 dark:border-white/5 dark:hover:bg-white/[0.03]"
                                data-id="${row.inventoryid}"
                                data-name="${row.inventory_descr}"
                                data-uom="${row.purchase_unit ?? ''}">

                            <span class="line-clamp-2 text-sm font-medium leading-snug text-gray-700 dark:text-gray-200">
                                    ${row.inventory_descr}
                                </span>

                                <div class="flex items-center gap-2 text-[11px] text-gray-400">

                                    <span class="rounded bg-gray-100 px-2 py-0.5 dark:bg-white/10">
                                        ${row.inventoryid}
                                    </span>

                                </div>
                            </button>
                        `;

                        });

                    }

                    container
                        .removeClass('hidden')
                        .html(html);

                } catch (err) {

                    console.log(err);

                }

            });

            $(document).on('click', '.inventory-select', function() {

                const btn = $(this);

                const row = btn.closest('tr');

                row.find('.inventory-search').val(
                    btn.data('name')
                );

                row.find('.inventory-id').val(
                    btn.data('id')
                );

                row.find('.item-uom').val(
                    btn.data('uom') || ''
                );

                row.find('.inventory-result')
                    .addClass('hidden')
                    .html('');

            });

            $(document).on('click', function(e) {

                if (!$(e.target).closest('.inventory-search, .inventory-result').length) {

                    $('.inventory-result')
                        .addClass('hidden');

                }

            });

            $('#processForm').on('submit', async function(e) {

                e.preventDefault();

                const hash = $('#process_hash').val();

                const btn = $('#btnSubmitProcess');

                let details = [];

                $('#process_detail_body tr').each(function() {

                    const row = $(this);

                    const recommend_descr = row.find('.inventory-search').val().trim();

                    if (!recommend_descr) {
                        return;
                    }

                    details.push({

                        recommend_descr: recommend_descr,
                        qty: row.find('.item-qty').val(),
                        uom: row.find('.item-uom').val(),
                        recommend_note: row.find('.item-note').val()

                    });

                });

                if (details.length === 0) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation',
                        text: 'Please select inventory item'
                    });

                    return;

                }

                btn.prop('disabled', true)
                    .html(`
                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                        Processing...
                    `);

                try {

                    await $.ajax({

                        url: `/it-recommendation/process/${hash}`,
                        type: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        data: {

                            recommend_type: $('#recommend_type').val(),
                            waranty: $('#waranty').val(),
                            recommendation: $('#recommendation').val(),
                            details: details

                        }

                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Processed successfully',
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeProcessModal();

                    closeShowModal();

                    table.ajax.reload(null, false);

                } catch (err) {

                    let msg = err.responseJSON?.message || 'Failed process';

                    if (err.status === 422 && err.responseJSON?.errors) {

                        msg = Object.values(err.responseJSON.errors)
                            .flat()
                            .join('<br>');

                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: msg
                    });

                } finally {

                    btn.prop('disabled', false)
                        .html(`
                            <i class="fa-solid fa-gears text-xs"></i>
                            Submit Process
                        `);

                }

            });

            $('#btnReviseProcess').on('click', async function() {

                const hash = $('#process_hash').val();

                const result = await Swal.fire({

                    title: 'Revise Request',
                    input: 'textarea',
                    inputPlaceholder: 'Write revise reason...',
                    showCancelButton: true,
                    confirmButtonText: 'Submit Revise'

                });

                if (!result.isConfirmed || !result.value) {
                    return;
                }

                try {

                    await $.ajax({

                        url: `/it-recommendation/it-revise/${hash}`,
                        type: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        data: {
                            note: result.value
                        }

                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Document revised'
                    });

                    closeProcessModal();

                    closeShowModal();

                    table.ajax.reload(null, false);

                } catch (err) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Failed revise'
                    });

                }

            });

            $('#btnRejectProcess').on('click', async function() {

                const hash = $('#process_hash').val();

                const result = await Swal.fire({

                    title: 'Reject Request',
                    input: 'textarea',
                    inputPlaceholder: 'Write reject reason...',
                    showCancelButton: true,
                    confirmButtonText: 'Reject'

                });

                if (!result.isConfirmed || !result.value) {
                    return;
                }

                try {

                    await $.ajax({

                        url: `/it-recommendation/it-reject/${hash}`,
                        type: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        data: {
                            note: result.value
                        }

                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Document rejected'
                    });

                    closeProcessModal();

                    closeShowModal();

                    table.ajax.reload(null, false);

                } catch (err) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Failed reject'
                    });

                }

            });

            const path = window.location.pathname;

            if (path.includes('/processitrecommendation/')) {

                const hash = path.split('/').pop();

                loadProcessDetail(hash);

            }

        });

        function openProcessModal() {
            $('#processModal')
                .removeClass('hidden')
                .addClass('flex');

            $('body').addClass('overflow-hidden');
        }

        function closeProcessModal() {

            $('#processModal')
                .removeClass('flex')
                .addClass('hidden');

            if (
                $('#createModal').hasClass('hidden') &&
                $('#showModal').hasClass('hidden') &&
                $('#processModal').hasClass('hidden') &&
                $('#editRecommendationModal').hasClass('hidden')
            ) {
                $('body').removeClass('overflow-hidden');
            }

            $('#processForm')[0].reset();

            $('#process_hash').val('');
            $('#process_docid').text('Process IT Recommendation');
            $('#process_information').html('');
            $('#process_attachments').html('');
            $('#process_detail_body').html('');

            const cleanUrl = "{{ url('/it-recommendation') }}";

            window.history.pushState({}, '', cleanUrl);

        }
    </script>

    {{-- Edit Recommendation Modal Script --}}
    <script>
        $(document).ready(function() {

            function editInfoItem(label, value) {

                return `
                    <div class="min-w-0">

                        <div class="text-[11px] font-semibold uppercase tracking-[0.15em] text-gray-400">
                            ${label}
                        </div>

                        <div class="mt-1 break-words text-sm text-gray-700 dark:text-gray-200">
                            ${value ?? '-'}
                        </div>

                    </div>
                `;

            }

            function addEditRecommendationRow(data = {}) {

                const html = `
                    <tr class="edit-detail-row border-b border-gray-100 dark:border-white/5">

                        <td class="px-3 py-3 align-top">

                        <div class="relative">

                                <input
                                    type="text"
                                    class="edit-inventory-search w-full rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-10 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                    placeholder="Search inventory name or item code..."
                                    autocomplete="off"
                                    value="${data.inventory_descr ?? ''}">

                                <input
                                    type="hidden"
                                    class="edit-inventory-id"
                                    value="${data.inventoryid ?? ''}">

                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>

                                <div class="edit-inventory-result  absolute left-0 bottom-full z-[9999] mt-1 hidden max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-white/10 dark:bg-[#111827]">
                            </div>

                            </div>

                        </td>

                        <td class="w-24 px-3 py-3 align-top">

                            <input
                                type="number"
                                min="1"
                                class="edit-item-qty w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                value="${data.qty ?? 1}">

                        </td>

                        <td class="w-24 px-3 py-3 align-top">

                            <input
                                type="text"
                                class="edit-item-uom w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-500 outline-none dark:border-white/10 dark:bg-[#1f2937] dark:text-gray-300"
                                readonly
                                value="${data.uom ?? ''}">

                        </td>

                        <td class="px-3 py-3 align-top">

                            <textarea
                                rows="2"
                                class="edit-item-note w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                placeholder="Optional note">${data.recommend_note ?? ''}</textarea>

                        </td>

                        <td class="w-14 px-3 py-3 align-top text-center">

                            <button
                                type="button"
                                class="btn-remove-edit-item inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                                <i class="fa-solid fa-trash text-xs"></i>

                            </button>

                        </td>

                    </tr>
                `;

                $('#edit_recommendation_detail_body').append(html);

            }

            async function loadEditRecommendation(hash) {

                try {

                    const res = await $.ajax({

                        url: `/it-recommendation/detail/${hash}`,
                        type: 'GET'

                    });

                    const h = res.header;

                    $('#edit_recommendation_hash').val(hash);

                    $('#edit_recommendation_docid').text(
                        `Revise Recommendation - ${h.docid}`
                    );

                    $('#edit_recommendation_status').html(
                        statusBadge(h.status)
                    );

                    $('#edit_recommendation_information').html(`

                        ${editInfoItem(
                            'Date',
                            h.itrecommend_date
                                ? new Date(h.itrecommend_date).toLocaleDateString('en-GB', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric'
                                })
                                : '-'
                        )}

                        ${editInfoItem('Company', h.cpny_id)}
                        ${editInfoItem('Department', h.department_id)}
                        ${editInfoItem('Requester', h.user_peminta)}
                        ${editInfoItem('Ticket Number', h.ticketnbr)}
                        ${editInfoItem('Asset Number', h.assetnbr || '-')}
                        ${editInfoItem('IT PIC', h.recommend_pic || '-')}
                        ${editInfoItem('Purpose / Requirement', h.keperluan)}

                    `);

                    $('#edit_recommend_type').val(h.recommend_type || '');
                    $('#edit_waranty').val(h.waranty || '');
                    $('#edit_recommendation').val(h.recommendation || '');

                    let attachmentHtml = '';

                    if (res.attachments.length === 0) {

                        attachmentHtml = `
                            <div class="w-full rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">
                                No attachments
                            </div>
                        `;

                    } else {

                        res.attachments.forEach(file => {

                            attachmentHtml += `
                                <a
                                    href="${file.signed_url ?? '#'}"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 transition hover:bg-gray-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.05]">

                                    <i class="fa-solid fa-paperclip text-gray-400"></i>

                                    <div class="max-w-[220px] truncate">
                                        ${file.filename ?? 'Attachment'}
                                    </div>

                                </a>
                            `;

                        });

                    }

                    $('#edit_recommendation_attachments').html(attachmentHtml);

                    let reviseNoteHtml = '';

                    const tracking = await $.ajax({

                        url: `/it-recommendation/tracking/${h.docid}`,
                        type: 'GET'

                    });

                    const reviseTimeline = tracking.find(x => x.status === 'D');

                    if (reviseTimeline?.note) {

                        reviseNoteHtml = `
                            <div class="rounded-lg border border-orange-200 bg-white px-4 py-3 text-sm text-orange-700 dark:border-orange-500/20 dark:bg-[#111827] dark:text-orange-300">
                                ${reviseTimeline.note}
                            </div>
                        `;

                    } else {

                        reviseNoteHtml = `
                            <div class="rounded-lg border border-dashed border-orange-200 px-4 py-3 text-sm text-orange-500 dark:border-orange-500/20 dark:text-orange-300">
                                No revision note available
                            </div>
                        `;

                    }

                    $('#revision_note_container').html(reviseNoteHtml);

                    $('#edit_recommendation_detail_body').html('');

                    if (res.details.length > 0) {

                        res.details.forEach(row => {

                            addEditRecommendationRow({

                                inventory_descr: row.recommend_descr,
                                inventoryid: row.inventoryid,
                                qty: row.qty,
                                uom: row.uom,
                                recommend_note: row.recommend_note

                            });

                        });

                    } else {

                        addEditRecommendationRow();

                    }

                    openEditRecommendationModal();

                } catch (err) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.responseJSON?.message || 'Failed load recommendation data'
                    });

                }

            }
            $(document).on('click', '.edit-recommendation-btn', function() {

                const hash = $(this).data('id');

                window.history.pushState({}, '', `/edit-processitrecommendation/${hash}`);

                loadEditRecommendation(hash);

            });

            $('#btnCloseEditRecommendationModal').on('click', function() {
                closeEditRecommendationModal();
            });

            $('#btnAddEditItem').on('click', function() {

                addEditRecommendationRow();

            });

            $(document).on('click', '.btn-remove-edit-item', function() {

                $(this)
                    .closest('tr')
                    .remove();

                if ($('#edit_recommendation_detail_body tr').length === 0) {

                    addEditRecommendationRow();

                }

            });

            $(document).on('keyup', '.edit-inventory-search', async function() {

                const input = $(this);

                const keyword = input.val();

                const container = input
                    .closest('td')
                    .find('.edit-inventory-result');

                if (keyword.length < 2) {

                    container
                        .addClass('hidden')
                        .html('');

                    return;

                }

                try {

                    const res = await $.ajax({

                        url: `/it-recommendation/inventory-search`,
                        type: 'GET',

                        data: {
                            q: keyword
                        }

                    });

                    let html = '';

                    if (res.length === 0) {

                        html = `
                            <div class="px-3 py-2 text-xs text-gray-400">
                                No inventory found
                            </div>
                        `;

                    } else {

                        res.forEach(row => {

                            html += `
                                <button
                                    type="button"
                                    class="edit-inventory-select group flex w-full flex-col gap-1 border-b border-gray-100 px-4 py-3 text-left transition hover:bg-indigo-50 dark:border-white/5 dark:hover:bg-white/[0.03]"
                                    data-id="${row.inventoryid}"
                                    data-name="${row.inventory_descr}"
                                    data-uom="${row.purchase_unit ?? ''}">

                                    <span class="line-clamp-2 text-sm font-medium leading-snug text-gray-700 dark:text-gray-200">
                                        ${row.inventory_descr}
                                    </span>

                                    <div class="flex items-center gap-2 text-[11px] text-gray-400">

                                        <span class="rounded bg-gray-100 px-2 py-0.5 dark:bg-white/10">
                                            ${row.inventoryid}
                                        </span>

                                    </div>

                                </button>
                            `;

                        });

                    }

                    container
                        .removeClass('hidden')
                        .html(html);

                } catch (err) {

                    console.log(err);

                }

            });

            $(document).on('click', '.edit-inventory-select', function() {

                const btn = $(this);

                const row = btn.closest('tr');

                row.find('.edit-inventory-search').val(
                    btn.data('name')
                );

                row.find('.edit-inventory-id').val(
                    btn.data('id')
                );

                row.find('.edit-item-uom').val(
                    btn.data('uom') || ''
                );

                row.find('.edit-inventory-result')
                    .addClass('hidden')
                    .html('');

            });

            $(document).on('click', function(e) {

                if (!$(e.target).closest('.edit-inventory-search, .edit-inventory-result').length) {

                    $('.edit-inventory-result')
                        .addClass('hidden');

                }

            });

            $('#editRecommendationForm').on('submit', async function(e) {

                e.preventDefault();

                const hash = $('#edit_recommendation_hash').val();

                let details = [];
                $('#edit_recommendation_detail_body tr').each(function() {

                    const row = $(this);

                    const recommend_descr = row.find('.edit-inventory-search').val().trim();

                    if (!recommend_descr) {
                        return;
                    }

                    details.push({

                        recommend_descr: recommend_descr,
                        qty: row.find('.edit-item-qty').val(),
                        uom: row.find('.edit-item-uom').val(),
                        recommend_note: row.find('.edit-item-note').val()

                    });

                });
                if (details.length === 0) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation',
                        text: 'Please select inventory item'
                    });

                    return;

                }

                const btn = $(this).find('button[type="submit"]');

                btn.prop('disabled', true)
                    .html(`
                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                        Resubmitting...
                    `);

                try {

                    await $.ajax({

                        url: `/it-recommendation/process/${hash}`,
                        type: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        data: {

                            recommend_type: $('#edit_recommend_type').val(),
                            waranty: $('#edit_waranty').val(),
                            recommendation: $('#edit_recommendation').val(),
                            details: details

                        }

                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Recommendation resubmitted successfully',
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeEditRecommendationModal();

                    closeShowModal();

                    table.ajax.reload(null, false);

                } catch (err) {

                    let msg = err.responseJSON?.message || 'Failed update recommendation';

                    if (err.status === 422 && err.responseJSON?.errors) {

                        msg = Object.values(err.responseJSON.errors)
                            .flat()
                            .join('<br>');

                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: msg
                    });

                } finally {

                    btn.prop('disabled', false)
                        .html(`
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            Resubmit Approval
                        `);

                }

            });

            const path = window.location.pathname;

            if (path.includes('/edit-processitrecommendation/')) {

                const hash = path.split('/').pop();

                loadEditRecommendation(hash);

            }

        });

        function openEditRecommendationModal() {

            $('#showModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#processModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editRecommendationModal')
                .removeClass('hidden')
                .addClass('flex');

            $('body').addClass('overflow-hidden');

        }

        function closeEditRecommendationModal() {

            $('#editRecommendationModal')
                .removeClass('flex')
                .addClass('hidden');

            if (
                $('#createModal').hasClass('hidden') &&
                $('#showModal').hasClass('hidden') &&
                $('#processModal').hasClass('hidden') &&
                $('#editRecommendationModal').hasClass('hidden')
            ) {
                $('body').removeClass('overflow-hidden');
            }

            $('#editRecommendationForm')[0].reset();

            $('#edit_recommendation_hash').val('');
            $('#edit_recommendation_information').html('');
            $('#edit_recommendation_detail_body').html('');
            $('#revision_note_container').html('');

            const cleanUrl = "{{ url('/it-recommendation') }}";

            window.history.pushState({}, '', cleanUrl);

            table.ajax.reload(null, false);

        }
    </script>
</x-app-layout>
