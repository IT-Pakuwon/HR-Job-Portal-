<x-app-layout>
    @include('pages.it_recommendation.partial.style')


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
            <a href="#" class="status-filter group block h-full text-left" data-status="W, I">
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
        <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        IT Recommendation
                    </h2>
                </div>

                <div class="flex items-center gap-3">

                    <a href="{{ url('/createitrecommendation') }}"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">

                        <i class="fa-solid fa-plus text-xs mr-2"></i>

                        Create Request

                    </a>

                </div>

            </div>

            <div class="overflow-hidden">

                <div class="overflow-x-auto">

                    <table id="itrTable"
                        class="w-full min-w-full border-separate border-spacing-0 text-sm">

                        <thead>

                            <tr
                                class="
                                    border-b border-gray-100
                                    bg-gray-50/70
                                    text-[11px] uppercase tracking-[0.08em]
                                    text-gray-500
                                    dark:border-white/[0.06]
                                    dark:bg-white/[0.02]
                                    dark:text-gray-400
                                ">

                                <th class="w-10 px-4 py-3"></th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Doc ID
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Date
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Ticket
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Company
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Department
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Requester
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Purpose
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    IT PIC
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Status
                                </th>

                                @if ($isITHardware)
                                    <th class="px-4 py-3 text-center font-semibold">
                                        Action
                                    </th>
                                @endif

                            </tr>

                        </thead>

                        <tbody></tbody>

                    </table>

                </div>

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

                  <div id="show_notes" class="space-y-2 mb-3"></div>
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
                                    class="select2 w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white">

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
                                    class="select2 w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white">

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
                                    <select name="ticketnbr" id="ticketnbr"
                                    class="select2 w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                        required>
                                    <option value="">Select Ticket</option>
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

                      <div id="show_notes" class="space-y-2 mb-3"></div>

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

                <div id="show_notes" class="space-y-2 mb-3"></div>

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

    <script>
        window.statusFilter = '';
        window.table = null;
        window.currentDetailHash = null;
        window.editMode = false;
        window.editHash = null;
        window.editStatus = null;

        window.currentUser = "{{ auth()->user()->username }}";

        window.isITHardware = @json($isITHardware);

    </script>

    <script>

        window.ITRecommendationRoutes = {

            index: @json(route('it-recommendation')),

            json: @json(route('it-recommendation.json')),

            store: @json(route('it-recommendation.store')),

            comment: @json(route('it-recommendation.comment', ['hash' => '__HASH__'])),

            base: @json(url('/it-recommendation'))

        };

    </script>
    <script src="{{ asset('assets/js/it-recommendation/helper.js') }}"></script>

    <script src="{{ asset('assets/js/it-recommendation/modal.js') }}"></script>

    <script src="{{ asset('assets/js/it-recommendation/attachment.js') }}"></script>

    <script src="{{ asset('assets/js/it-recommendation/inventory.js') }}"></script>

    <script src="{{ asset('assets/js/it-recommendation/datatable.js') }}"></script>

    <script src="{{ asset('assets/js/it-recommendation/request-form.js') }}"></script>

    <script src="{{ asset('assets/js/it-recommendation/detail-modal.js') }}"></script>

    <script src="{{ asset('assets/js/it-recommendation/process.js') }}"></script>

    <script src="{{ asset('assets/js/it-recommendation/revise-recommendation.js') }}"></script>

    <script src="{{ asset('assets/js/it-recommendation/approval.js') }}"></script>
</x-app-layout>
