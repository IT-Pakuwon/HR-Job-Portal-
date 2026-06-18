<x-app-layout>
    @include('pages.it_recommendation.partial.style')


    <div class="max-w-9xl mx-auto w-full p-2">
        {{-- Status Card --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">

            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-slate-400 bg-slate-200/20 p-3 text-slate-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-slate-100 hover:shadow-md active:scale-95 dark:border-slate-500 dark:text-slate-300 dark:hover:bg-slate-700/30">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                    </div>
                </a>
            </button>

            {{-- Waiting IT --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="W,I">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-600 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-500/20">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🛠️</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Waiting IT</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $waitingIT }}</p>
                    </div>
                </a>
            </button>

            {{-- Waiting Approval --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="P">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-600 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95 dark:border-orange-500 dark:text-orange-400 dark:hover:bg-orange-500/20">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Waiting Approval</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $waitingApproval }}</p>
                    </div>
                </a>
            </button>

            {{-- Revise --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="D">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-amber-600 bg-amber-200/20 p-3 text-amber-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-amber-100 hover:shadow-md active:scale-95 dark:border-amber-500 dark:text-amber-400 dark:hover:bg-amber-500/20">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Revise</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                    </div>
                </a>
            </button>

            {{-- Rejected --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="R">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-red-600 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-500/20">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Rejected</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $reject }}</p>
                    </div>
                </a>
            </button>

            {{-- Completed --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="C">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-600 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95 dark:border-green-500 dark:text-green-400 dark:hover:bg-green-500/20">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Completed</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                    </div>
                </a>
            </button>

        </div>
        {{-- Table --}}
        <div
            class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div
                class="flex flex-col gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        IT Recommendation
                    </h2>
                </div>

                <div class="flex items-center gap-3">

                    <a href="{{ url('/createitrecommendation') }}"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">

                        <i class="fa-solid fa-plus mr-2 text-xs"></i>

                        Create Request

                    </a>

                </div>

            </div>

            <div class="relative overflow-hidden">

                <table id="itrTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">

                    <thead>

                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">

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

                            <th class="px-4 py-3 text-center font-semibold">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody></tbody>

                </table>


            </div>

        </div>

        {{-- Create Modal --}}
        <div id="createModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-5xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div>

                        <h2 id="createmodaltitle" class="text-xl font-bold text-slate-900 dark:text-white">
                            Create IT Recommendation
                        </h2>

                        <p id="createmodaldesc" class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Submit request for IT recommendation process.
                        </p>

                    </div>

                    <button id="btnCloseCreateModal" type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-lg"></i>

                    </button>

                </div>

                <div class="space-y-4 bg-slate-50 p-4 dark:bg-[#0b1220]">

                    <div id="show_notes" class="space-y-2"></div>

                    <form id="createForm" class="space-y-2">

                        @csrf

                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                            <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    Request Information
                                </h3>

                            </div>

                            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                                <div>

                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Company
                                    </label>

                                    <select name="cpny_id" id="create_cpny_id" required
                                        {{ count($usercpny) === 1 ? 'disabled' : '' }}
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

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

                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Department
                                    </label>

                                    <select name="department_id" id="create_department_id" required
                                        {{ count($userdept) === 1 ? 'disabled' : '' }}
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

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

                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Ticket Number
                                    </label>

                                    <select name="ticketnbr" id="ticketnbr" required
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                        <option value="">
                                            Select Ticket
                                        </option>

                                    </select>

                                </div>

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Asset Number
                                    </label>

                                    <input type="text" name="assetnbr" id="create_assetnbr"
                                        placeholder="Optional"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500">

                                </div>

                                <div class="md:col-span-2">

                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Purpose / Requirement
                                    </label>

                                    <textarea name="keperluan" id="create_keperluan" rows="5" placeholder="Describe requirement..."
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500"
                                        required></textarea>

                                </div>

                            </div>

                        </div>

                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                            <div
                                class="border-b border-slate-200 bg-slate-50/80 px-5 py-2 dark:border-white/10 dark:bg-white/[0.03]">

                                <h3
                                    class="text-sm font-bold uppercase tracking-[0.16em] text-slate-700 dark:text-slate-200">
                                    Attachment
                                </h3>

                            </div>

                            <div class="p-5">

                                <label for="create_attachments"
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

                                    <input type="file" id="create_attachments" name="attachments[]" multiple
                                        class="hidden">

                                </label>

                                <div id="createAttachmentPreview" class="mt-4 flex flex-wrap gap-3"></div>

                            </div>

                        </div>

                        <div
                            class="sticky bottom-0 z-20 mt-4 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                                <div class="flex items-center gap-2">

                                    <button id="btnCancelRequest" type="button"
                                        class="inline-flex hidden h-11 items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20">

                                        <i class="fa-solid fa-ban text-xs"></i>

                                        Cancel Document

                                    </button>

                                </div>

                                <div class="flex items-center justify-end gap-3">

                                    <button id="btnCancelCreate" type="button"
                                        class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]">

                                        Cancel

                                    </button>

                                    <button id="btnSubmitCreate" type="submit"
                                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition-all duration-200 hover:scale-[1.01] hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                                        <i class="fa-solid fa-paper-plane text-xs"></i>

                                        Submit Request

                                    </button>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        {{-- View Modal --}}
        <div id="showModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/75">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-7xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div class="min-w-0">

                        <h2 id="show_docid" class="font-semibold text-slate-800 dark:text-white">
                            IT Recommendation Detail
                        </h2>

                        <p class="mt-1 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            IT Recommendation information &amp; approval workflow.
                            <span id="show_status_badge"></span>
                        </p>

                    </div>

                    <div class="flex items-center gap-2">

                        {{-- Discussion FAB --}}
                        <div id="discussionFab" class="hidden">
                            <button type="button"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                                <i class="fa-solid fa-comments text-sm"></i>
                            </button>
                        </div>

                        {{-- Print Button --}}
                        <button id="btnPrintRecommendation" type="button"
                            title="Print PDF"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                            <i class="fa-solid fa-print text-sm"></i>
                        </button>

                        <button id="btnCloseShowModal" type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                            <i class="fa-solid fa-xmark text-lg"></i>

                        </button>

                    </div>

                </div>

                    <div class="grid grid-cols-1 gap-4 bg-slate-50 p-4 dark:bg-[#0b1220] lg:grid-cols-6">

                        <div class="space-y-4 lg:col-span-4">

                            {{-- REQUEST INFORMATION --}}
                            <div
                                class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                    <h3
                                        class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                        Request Information

                                    </h3>

                                </div>

                                <div id="show_information"
                                    class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 text-sm md:grid-cols-2 xl:grid-cols-3">
                                </div>

                            </div>

                            {{-- RECOMMENDATION --}}
                            <div
                                class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                    <h3
                                        class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                        IT Recommendation

                                    </h3>

                                </div>

                                <div id="show_recommendation_info"
                                    class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 text-sm md:grid-cols-2 xl:grid-cols-3">
                                </div>

                            </div>

                            {{-- ITEMS --}}
                            <div
                                class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                    <h3
                                        class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                        Recommendation Items

                                    </h3>

                                </div>

                                <div class="overflow-x-auto">

                                    <table class="w-full text-sm">

                                        <thead
                                            class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase tracking-[0.15em] text-slate-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-400">

                                            <tr>

                                                <th class="px-4 py-3 text-left font-semibold">
                                                    Description
                                                </th>

                                                <th class="px-4 py-3 text-left font-semibold">
                                                    Qty
                                                </th>

                                                <th class="px-4 py-3 text-left font-semibold">
                                                    UOM
                                                </th>

                                                <th class="px-4 py-3 text-left font-semibold">
                                                    Category
                                                </th>

                                                <th class="px-4 py-3 text-left font-semibold">
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
                                class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                    <h3
                                        class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                        Attachments

                                    </h3>

                                </div>

                                <div id="show_attachments" class="flex flex-wrap gap-3 p-5">
                                </div>

                            </div>

                        </div>

                        <div class="space-y-2 lg:col-span-2">

                            {{-- APPROVAL ACTIONS (Approve / Revise / Reject) --}}
                            <div id="show_approval_actions_wrapper" class="hidden"></div>

                            {{-- TIMELINE --}}
                            <div
                                class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                    <h3
                                        class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                        Approval Timeline

                                    </h3>

                                </div>

                                <div id="show_tracking" class="space-y-4 p-5">
                                </div>

                            </div>

                        </div>

                    </div>

                {{-- DISCUSSION PANEL (fixed to viewport, pops up from bottom-right) --}}
                <div id="discussionPanel"
                    class="fixed bottom-20 right-6 z-[10001] hidden w-[380px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">

                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-2 dark:border-white/10">

                        <h3
                            class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                            Discussion
                        </h3>

                        <button type="button" id="btnCloseDiscussion">
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                    </div>

                    <div id="discussionMessages"
                        class="h-[360px] space-y-4 overflow-y-auto bg-slate-50 p-4 dark:bg-[#0b1220]">
                    </div>

                    <div class="border-t border-slate-200 p-3 dark:border-white/10">

                        <div class="flex items-end gap-2">

                            <textarea id="discussionInput" rows="1" placeholder="Write message..."
                                class="min-h-[46px] flex-1 resize-none rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500"></textarea>

                            <button type="button" id="btnSendDiscussion"
                                class="h-11 w-11 rounded-lg bg-slate-900 text-white dark:bg-blue-600 dark:hover:bg-blue-500">
                                <i class="fa-solid fa-paper-plane text-sm"></i>
                            </button>

                        </div>

                    </div>

                </div>

                {{-- STICKY FOOTER --}}
                <div
                    class="sticky bottom-0 z-20 flex items-center justify-between border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">

                    <button type="button" id="btnCloseShowModalFooter"
                        class="text-sm text-slate-500 transition hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">
                        Close
                    </button>

                    <div id="show_footer_actions" class="flex items-center gap-3"></div>

                </div>

            </div>

        </div>

        {{-- Process Modal --}}
        <div id="processModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/75">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-7xl translate-y-4 scale-[0.98] flex-col overflow-hidden rounded-lg border border-slate-200 bg-slate-50 opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0b1220]">

                {{-- HEADER --}}
                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-3">

                            <h2 id="process_docid" class="truncate text-xl font-bold text-slate-900 dark:text-white">
                                Process IT Recommendation
                            </h2>

                            <div id="process_status_badge"></div>

                        </div>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            IT Hardware recommendation processing
                        </p>

                    </div>

                    <div class="flex items-center gap-2">

                        <button type="submit" form="processForm" id="btnSubmitProcess"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">

                            <i class="fa-solid fa-gears text-xs"></i>

                            Submit

                        </button>

                        <button type="button" id="btnReviseProcess"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">

                            <i class="fa-solid fa-rotate-left text-xs"></i>

                            Revise

                        </button>

                        <button type="button" id="btnRejectProcess"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                            <i class="fa-solid fa-xmark text-xs"></i>

                            Reject

                        </button>

                        <button id="btnCloseProcessModal" type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                            <i class="fa-solid fa-xmark text-lg"></i>

                        </button>

                    </div>

                </div>

                {{-- BODY --}}
                <form id="processForm" class="flex min-h-0 flex-1 flex-col">

                    @csrf

                    <input type="hidden" id="process_hash">

                    <div class="flex-1 overflow-y-auto">

                        {{-- LEFT --}}
                        <div class="space-y-2 p-2">

                            {{-- REQUEST INFORMATION --}}
                            <div
                                class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                    <h3
                                        class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                        Request Information

                                    </h3>

                                </div>

                                <div id="process_information"
                                    class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 text-sm md:grid-cols-2 xl:grid-cols-3">
                                </div>

                            </div>

                            {{-- RECOMMENDATION --}}
                            <div
                                class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                    <h3
                                        class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                        Recommendation

                                    </h3>

                                </div>

                                <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                                    <div>

                                        <label
                                            class="req mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">

                                            Recommendation Type

                                        </label>

                                        <select name="recommend_type" id="recommend_type" required
                                            class="process-select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#111827]">

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
                                            class="mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">

                                            Warranty

                                        </label>

                                        <input type="text" name="waranty" id="waranty" placeholder="Optional"
                                            class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white">

                                    </div>

                                    <div class="md:col-span-2">

                                        <label
                                            class="req mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">

                                            Recommendation

                                        </label>

                                        <textarea name="recommendation" id="recommendation" rows="5" required placeholder="Write recommendation..."
                                            class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"></textarea>

                                    </div>

                                </div>

                            </div>

                            {{-- ITEMS --}}
                            <div
                                class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                <div
                                    class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                    <h3
                                        class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

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
                                            class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase tracking-[0.15em] text-slate-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-400">

                                            <tr>

                                                <th class="px-4 py-3 text-left font-semibold">
                                                    Inventory
                                                </th>

                                                <th class="px-4 py-3 text-left font-semibold">
                                                    Qty
                                                </th>

                                                <th class="px-4 py-3 text-left font-semibold">
                                                    UOM
                                                </th>

                                                <th class="px-4 py-3 text-left font-semibold">
                                                    Note
                                                </th>

                                                <th class="w-14 px-4 py-3 text-center">
                                                </th>

                                            </tr>

                                        </thead>

                                        <tbody id="process_detail_body"></tbody>

                                    </table>

                                </div>

                            </div>

                            {{-- ATTACHMENTS --}}
                            <div
                                class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                    <h3
                                        class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                        Attachments

                                    </h3>

                                </div>

                                <div class="space-y-3 p-4">

                                    <label for="process_attachments_input"
                                        class="group flex cursor-pointer items-center justify-center gap-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-6 transition-all duration-200 hover:border-slate-400 hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.03] dark:hover:border-blue-500/30 dark:hover:bg-blue-500/[0.05]">

                                        <div
                                            class="flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-300">

                                            <i class="fa-solid fa-cloud-arrow-up"></i>

                                        </div>

                                        <div>

                                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                Upload Process Attachment
                                            </p>

                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                (Max 5 MB)
                                            </p>

                                        </div>

                                        <input type="file" id="process_attachments_input" name="attachments[]"
                                            multiple class="hidden">

                                    </label>

                                    <div id="processAttachmentPreview" class="flex flex-wrap gap-3">
                                    </div>

                                    <div id="process_attachments" class="flex flex-wrap gap-3">
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                </form>

            </div>

        </div>

        {{-- Edit Recommendation Modal --}}
        <div id="editRecommendationModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/75">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-7xl translate-y-4 scale-[0.98] flex-col overflow-hidden rounded-lg border border-slate-200 bg-slate-50 opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0b1220]">

                {{-- HEADER --}}
                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-3">

                            <h2 id="edit_recommendation_docid"
                                class="truncate text-xl font-bold text-slate-900 dark:text-white">

                                Revise Recommendation

                            </h2>

                            <div id="edit_recommendation_status"></div>

                        </div>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Update recommendation based on approver revision request
                        </p>

                    </div>

                    <button id="btnCloseEditRecommendationModal" type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-lg"></i>

                    </button>

                </div>

                {{-- BODY --}}
                <form id="editRecommendationForm" class="flex min-h-0 flex-1 flex-col">

                    @csrf

                    <input type="hidden" id="edit_recommendation_hash">

                    <div class="flex-1 overflow-y-auto">

                        <div class="grid grid-cols-1 gap-5 p-5 lg:grid-cols-1">

                            {{-- LEFT --}}
                            <div class="space-y-5 lg:col-span-2">

                                {{-- REVISION NOTE --}}
                                <div
                                    class="rounded-lg border border-orange-200 bg-orange-50 shadow-sm dark:border-orange-500/20 dark:bg-orange-500/10">

                                        <div class="flex flex-col w-full gap-2 p-4">

                                            <h3
                                                class="text-xs font-bold uppercase tracking-[0.18em] text-orange-700 dark:text-orange-300">

                                                Revision Requested by Approver

                                            </h3>

                                            <div id="revision_note_container" ></div>

                                        </div>
                                </div>

                                {{-- REQUEST INFORMATION --}}
                                <div
                                    class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                    <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                        <h3
                                            class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                            Request Information

                                        </h3>

                                    </div>

                                    <div id="edit_recommendation_information"
                                        class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 text-sm md:grid-cols-2 xl:grid-cols-3">
                                    </div>

                                </div>

                                {{-- RECOMMENDATION --}}
                                <div
                                    class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                    <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                        <h3
                                            class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                            Recommendation

                                        </h3>

                                    </div>

                                    <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                                        <div>

                                            <label
                                                class="req mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">

                                                Recommendation Type

                                            </label>

                                            <select id="edit_recommend_type"
                                                class="edit-select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#111827]">

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
                                                class="mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">

                                                Warranty

                                            </label>

                                            <input type="text" id="edit_waranty"
                                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white">

                                        </div>

                                        <div class="md:col-span-2">

                                            <label
                                                class="req mb-2 block text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">

                                                Recommendation

                                            </label>

                                            <textarea id="edit_recommendation" rows="5"
                                                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"></textarea>

                                        </div>

                                    </div>

                                </div>

                                {{-- RECOMMENDATION ITEMS --}}
                                <div
                                    class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                    <div
                                        class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                        <h3
                                            class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                            Recommendation Items

                                        </h3>

                                        <button type="button" id="btnAddEditItem"
                                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">

                                            <i class="fa-solid fa-plus text-[10px]"></i>

                                            Add Item

                                        </button>

                                    </div>

                                    <div class="overflow-visible">

                                        <table class="w-full text-sm">

                                            <tbody id="edit_recommendation_detail_body"></tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                            {{-- RIGHT --}}
                            <div class="space-y-5 lg:col-span-2">

                                {{-- ATTACHMENTS --}}
                                <div
                                    class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                                    <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">

                                        <h3
                                            class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">

                                            Attachments

                                        </h3>

                                    </div>
                                    <div class="space-y-4 p-5">

                                        <label for="edit_recommendation_attachments_input"
                                            class="group flex cursor-pointer items-center justify-center gap-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-6 transition-all duration-200 hover:border-slate-400 hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.03] dark:hover:border-blue-500/30 dark:hover:bg-blue-500/[0.05]">

                                            <div
                                                class="flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-300">

                                                <i class="fa-solid fa-cloud-arrow-up"></i>

                                            </div>

                                            <div>

                                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                                    Upload Attachment
                                                </p>

                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    (Max 5 MB)
                                                </p>

                                            </div>

                                            <input type="file" id="edit_recommendation_attachments_input" multiple
                                                class="hidden">

                                        </label>

                                        <div id="editRecommendationAttachmentPreview" class="flex flex-wrap gap-3">
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

                            <button type="button" id="btnCloseEditRecommendationFooter"
                                class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]">

                                Cancel

                            </button>

                            <button type="submit"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition-all duration-200 hover:scale-[1.01] hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                                <i class="fa-solid fa-paper-plane text-xs"></i>

                                Resubmit Approval

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

        <div id="attachmentModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

            <div class="modal-backdrop absolute inset-0 bg-slate-900/60"></div>

            <div
                class="relative z-10 flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_30px_80px_rgba(0,0,0,0.35)] dark:border-white/10 dark:bg-[#0f172a]">

                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h3 class="text-lg font-semibold text-slate-800 dark:text-white">
                            Upload Attachment
                        </h3>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Upload supporting documents, photos or files
                            (maximum 5 MB per file)
                        </p>

                    </div>

                    <button type="button"
                        class="btn-close-attachment-modal inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition-all duration-200 hover:bg-slate-100 dark:hover:bg-white/10">

                        <i class="fa-solid fa-xmark"></i>

                    </button>

                </div>

                <div class="flex-1 overflow-y-auto p-6">

                    <input type="hidden" id="attachment_hash">

                    <label for="attachment_files"
                        class="group relative flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-indigo-200 bg-gradient-to-br from-indigo-50 via-white to-slate-50 px-6 py-12 transition-all duration-300 hover:border-indigo-400 hover:shadow-lg hover:shadow-indigo-500/10 dark:border-indigo-500/30 dark:from-indigo-500/10 dark:via-[#0f172a] dark:to-[#111827]">

                        <div
                            class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 transition-transform duration-300 group-hover:scale-110 dark:bg-indigo-500/15 dark:text-indigo-300">

                            <i class="fa-solid fa-cloud-arrow-up text-2xl"></i>

                        </div>

                        <p class="text-base font-semibold text-slate-800 dark:text-white">
                            Upload Attachment
                        </p>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Click here to browse files
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Maximum 5 MB per file
                        </p>

                    </label>

                    <input type="file" id="attachment_files" multiple class="hidden">

                    <div id="attachmentPreview"
                        class="mt-6 grid gap-3 rounded-xl bg-slate-50/70 p-3 dark:bg-white/[0.02]">

                        <div
                            class="rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400 dark:border-white/10">
                            No attachments
                        </div>

                    </div>

                </div>

                <div
                    class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-white/10 dark:bg-white/[0.02]">

                    <button type="button"
                        class="btn-close-attachment-modal inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition-all hover:bg-slate-100 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">

                        <i class="fa-solid fa-xmark"></i>

                        Cancel

                    </button>

                    <button type="button" id="btnUploadAttachment"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-500/30">

                        <i class="fa-solid fa-cloud-arrow-up"></i>

                        Upload Attachment

                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- ATTACHMENT PREVIEW MODAL --}}
    <div id="attachmentPreviewModal"
        class="fixed inset-0 z-[10002] hidden items-center justify-center bg-black/80 p-4">

        <button type="button" onclick="closeAttachmentPreview()"
            class="absolute right-5 top-5 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 text-white transition hover:bg-white/20">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div id="attachmentPreviewContent" class="w-full max-w-5xl">
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

            uploadAttachment: @json(route('it-recommendation.upload-attachment', ['hash' => '__HASH__'])),

            deleteAttachment: @json(route('it-recommendation.delete-attachment', ['attachment' => '__ID__'])),

            print: @json(url('/it-recommendation/print')) + '/__HASH__',

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
