<x-app-layout>

    @include('pages.access-requests.partials.styles')
    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- Status Filter --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

            {{-- All Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                    </div>
                </a>
            </button>

            {{-- On Progress Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="P">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">On Progress</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $pending }}</p>
                    </div>
                </a>
            </button>

            {{-- Reject Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="R">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Reject</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $reject }}</p>
                    </div>
                </a>
            </button>

            {{-- Revise / Draft Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="D">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Revise / Draft</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                    </div>
                </a>
            </button>

            {{-- Completed Status --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="C">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Completed</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                    </div>
                </a>
            </button>
        </div>

        {{-- Init Datatble --}}
        <div class="mt-4 rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

            <div class="flex flex-col gap-4 border-b border-slate-300 p-5 dark:border-white/10">

                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

                    <div class="flex flex-col gap-1">

                        <h1 class="text-lg font-bold text-slate-800 dark:text-white">
                            Access Request
                        </h1>

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Manage hardware and software access request workflow.
                        </p>

                    </div>

                    <div class="flex items-center gap-3">

                        <button id="btnCreate" type="button"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 active:scale-[0.98] dark:bg-blue-600 dark:hover:bg-blue-500">

                            <i class="fa-solid fa-plus text-xs"></i>

                            Create Request

                        </button>

                    </div>

                </div>

            </div>

            <div class="relative overflow-x-auto p-4">

                <table id="accessRequestTable" class="min-w-full divide-y divide-slate-200 dark:divide-white/10">

                    <thead class="bg-slate-50 dark:bg-[#0b1220]">

                        <tr>

                            <th class="w-[28px]"></th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Document
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Date
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Requester
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Company
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Department
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Group
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Progress
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Status
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-white/[0.05] dark:bg-[#0f172a]">
                    </tbody>

                </table>

            </div>

        </div>

        {{-- CREATE / EDIT MODAL --}}
        <div id="requestModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-6xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-5 backdrop-blur-xl dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div>

                        <h2 id="requestModalTitle" class="text-xl font-bold text-slate-900 dark:text-white">
                            Create Access Request
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Hardware & software access request form.
                        </p>

                    </div>

                    <button type="button"
                        class="btn-close-modal inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-lg"></i>

                    </button>

                </div>

                <div class="space-y-4 bg-slate-50 p-4 dark:bg-[#0b1220]">

                    <form id="requestForm" class="space-y-4">

                        <input type="hidden" id="requestMethod" value="POST">

                        <input type="hidden" id="requestUrl">

                        <input type="hidden" id="requestHash">

                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                            <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    Request Information
                                </h3>

                            </div>

                            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Request Date
                                    </label>

                                    <input type="date" id="access_date" name="access_date"
                                        value="{{ now()->format('Y-m-d') }}"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:focus:border-blue-500">

                                </div>

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Request Type
                                    </label>

                                    <select id="access_type" name="access_type"
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                        <option value="">
                                            Choose Type
                                        </option>

                                        <option value="NEW">
                                            New Access
                                        </option>

                                        <option value="CHANGE">
                                            Change Access
                                        </option>

                                        <option value="REMOVE">
                                            Remove Access
                                        </option>

                                    </select>

                                </div>

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Company
                                    </label>

                                    @if (count($companies) <= 1)

                                        <input type="text" value="{{ $companies[0] ?? '-' }}" readonly
                                            class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                                        <input type="hidden" id="cpny_id" name="cpny_id"
                                            value="{{ $companies[0] ?? '' }}">
                                    @else
                                        <select id="cpny_id" name="cpny_id"
                                            class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                            <option value="">
                                                Choose Company
                                            </option>

                                            @foreach ($companies as $company)
                                                <option value="{{ $company }}">
                                                    {{ $company }}
                                                </option>
                                            @endforeach

                                        </select>

                                    @endif

                                </div>

                                <div>

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Department
                                    </label>

                                    @if (count($departments) <= 1)

                                        <input type="text" value="{{ $departments[0] ?? '-' }}" readonly
                                            class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">

                                        <input type="hidden" id="department_id" name="department_id"
                                            value="{{ $departments[0] ?? '' }}">
                                    @else
                                        <select id="department_id" name="department_id"
                                            class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">

                                            <option value="">
                                                Choose Department
                                            </option>

                                            @foreach ($departments as $department)
                                                <option value="{{ $department }}">
                                                    {{ $department }}
                                                </option>
                                            @endforeach

                                        </select>

                                    @endif

                                </div>

                                <div class="md:col-span-2">

                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Purpose / Notes
                                    </label>

                                    <textarea id="keperluan" name="keperluan" rows="4" placeholder="Input request purpose..."
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500"></textarea>

                                </div>

                            </div>

                        </div>

                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a] dark:shadow-[0_10px_40px_rgba(0,0,0,.28)]">

                            <div
                                class="flex items-center justify-between border-b border-slate-200 bg-slate-50/80 px-5 py-4 dark:border-white/10 dark:bg-white/[0.03]">

                                <div>

                                    <h3
                                        class="text-sm font-bold uppercase tracking-[0.16em] text-slate-700 dark:text-slate-200">
                                        Access Request Detail
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Hardware & software request item.
                                    </p>

                                </div>

                                <button type="button" id="btnAddDetail"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white transition-all duration-200 hover:scale-[1.02] hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                                    <i class="fa-solid fa-plus text-[11px]"></i>

                                    Add Item

                                </button>

                            </div>

                            <div class="overflow-x-auto">

                                <table class="min-w-full">

                                    <thead class="bg-slate-50 dark:bg-white/[0.03]">

                                        <tr>

                                            <th
                                                class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                                Category
                                            </th>

                                            <th
                                                class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                                Group
                                            </th>

                                            <th
                                                class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                                Action
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody id="requestDetailContainer"
                                        class="divide-y divide-slate-100 bg-white dark:divide-white/[0.05] dark:bg-[#0f172a]">

                                        {{-- JS RENDER --}}

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a] dark:shadow-[0_10px_40px_rgba(0,0,0,.28)]">

                            <div
                                class="border-b border-slate-200 bg-slate-50/80 px-5 py-4 dark:border-white/10 dark:bg-white/[0.03]">

                                <h3
                                    class="text-sm font-bold uppercase tracking-[0.16em] text-slate-700 dark:text-slate-200">
                                    Attachment
                                </h3>

                            </div>

                            <div class="p-5">

                                <label for="requestAttachment"
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

                                    <input type="file" id="requestAttachment" name="attachments[]" multiple
                                        class="hidden">

                                </label>

                                <div id="existingAttachmentContainer" class="mt-4 space-y-3"></div>

                                <div id="newAttachmentContainer" class="mt-4 space-y-3"></div>

                            </div>

                        </div>


                        <div
                            class="sticky bottom-0 z-20 mt-4 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur-xl dark:border-white/10 dark:bg-[#0f172a]/95">

                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                                <div class="flex flex-wrap items-center gap-2">

                                    <span
                                        class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 dark:bg-white/[0.06] dark:text-slate-300">

                                        Total :

                                        <span id="summaryTotalItem" class="ml-1.5 font-bold">
                                            0
                                        </span>

                                    </span>

                                    <span
                                        class="inline-flex items-center rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 dark:bg-blue-500/15 dark:text-blue-300">

                                        HW :

                                        <span id="summaryHardware" class="ml-1.5 font-bold">
                                            0
                                        </span>

                                    </span>

                                    <span
                                        class="inline-flex items-center rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">

                                        SW :

                                        <span id="summarySoftware" class="ml-1.5 font-bold">
                                            0
                                        </span>

                                    </span>

                                </div>

                                <div class="flex items-center justify-end gap-3">

                                    <button type="button"
                                        class="btn-close-modal inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]">

                                        Cancel

                                    </button>

                                    <button type="button" id="btnSubmitRequest"
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

        {{-- DETAIL MODAL --}}
        <div id="detailModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/75">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-7xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0b1220]">

                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-5 backdrop-blur-xl dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div class="flex items-center gap-3">

                        <h2 id="detailModalDocId" class="text-xl font-bold text-slate-900 dark:text-white">
                            -
                        </h2>

                        <div id="detailModalStatus"></div>

                    </div>

                    <div class="flex items-center gap-2">

                        <button type="button" id="btnPrintAccess" title="Print"
                            class="btn-print-access inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-100 hover:text-slate-800 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                            <i class="fa-solid fa-print text-sm"></i>

                        </button>

                        <button type="button" data-modal="#detailModal"
                            class="btn-close-modal inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                            <i class="fa-solid fa-xmark text-lg"></i>

                        </button>

                    </div>

                </div>

                <div class="grid grid-cols-1 gap-5 p-5 lg:grid-cols-3">

                    <div class="space-y-5 lg:col-span-2">

                        <div id="detailInfoContainer"></div>

                        <div id="detailItemsContainer"></div>

                        <div id="detailAttachmentContainer"></div>

                    </div>

                    <div class="space-y-4">

                        <div id="detailActionContainer"></div>

                        <div id="detailActivityContainer"></div>

                    </div>

                </div>

                <div id="discussionFab" class="fixed bottom-6 right-6 z-[10001] hidden">

                    <button type="button"
                        class="relative flex h-14 w-14 items-center justify-center rounded-lg bg-slate-900 text-white shadow-2xl transition hover:scale-105 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                        <i class="fa-solid fa-comments text-lg"></i>

                        <span id="discussionUnreadBadge"
                            class="absolute -right-1 -top-1 hidden h-5 min-w-[20px] items-center justify-center rounded-lg bg-red-500 px-1 text-[10px] font-bold text-white">
                            0
                        </span>

                    </button>

                </div>

                <div id="discussionPanel"
                    class="fixed bottom-24 right-6 z-[10001] hidden w-[380px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">

                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-white/10">

                        <div>

                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Discussion
                            </h3>

                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                Internal discussion
                            </p>

                        </div>

                        <button type="button" id="btnCloseDiscussion"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-500 dark:hover:bg-white/[0.08] dark:hover:text-white">

                            <i class="fa-solid fa-xmark"></i>

                        </button>

                    </div>

                    <div id="discussionMessages"
                        class="h-[360px] space-y-4 overflow-y-auto bg-slate-50 p-4 dark:bg-[#0b1220]"></div>

                    <div class="border-t border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-[#0f172a]">

                        <div class="flex items-end gap-2">

                            <textarea id="discussionInput" rows="1" placeholder="Write message..."
                                class="min-h-[46px] flex-1 resize-none rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500"></textarea>

                            <button type="button" id="btnSendDiscussion"
                                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                                <i class="fa-solid fa-paper-plane text-sm"></i>

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- PROCESS HARDWARE MODAL --}}
        <div id="processHardwareModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/75">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-6xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0b1220]">

                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-5 backdrop-blur-xl dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div>

                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                            Process Hardware Access
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Complete requested hardware access.
                        </p>

                    </div>

                    <button type="button"
                        class="btn-close-modal inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-lg"></i>

                    </button>

                </div>

                <div class="space-y-2 p-4">

                    <div id="processHardwareInfoContainer"></div>

                    <div id="processHardwareDetailContainer"></div>

                </div>

            </div>

        </div>

        {{-- PROCESS SOFTWARE MODAL --}}
        <div id="processSoftwareModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/75">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-6xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0b1220]">

                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-5 backdrop-blur-xl dark:border-white/10 dark:bg-[#0f172a]/90">

                    <div>

                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                            Process Software Access
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Complete requested software access.
                        </p>

                    </div>

                    <button type="button"
                        class="btn-close-modal inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">

                        <i class="fa-solid fa-xmark text-lg"></i>

                    </button>

                </div>

                <div class="space-y-2 p-4">

                    <div id="processSoftwareInfoContainer"></div>

                    <div id="processSoftwareDetailContainer"></div>

                    <div id="processSoftwareFormContainer"></div>

                    <div id="processSoftwareResultContainer"></div>

                    <div id="processSoftwareActionContainer"></div>

                </div>

            </div>

        </div>

    </div>
    <div id="modalCreateTicket"
        class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/60 px-4 backdrop-blur-sm">

        <div
            class="w-full max-w-5xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">

            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-neutral-800">

                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                        Create Ticket
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Create new IT support ticket request
                    </p>
                </div>

                <button type="button" data-close-modal="modalCreateTicket"
                    class="flex h-10 w-10 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-neutral-800">

                    <i class="ti ti-x text-lg"></i>

                </button>

            </div>

            <form id="formCreateTicket" enctype="multipart/form-data">

                @csrf

                <div class="max-h-[78vh] space-y-5 overflow-y-auto px-5 py-5">

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div class="space-y-2">

                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <select name="ticket_type" id="create_ticket_type" class="form-select-ticket w-full">
                                <option value="">
                                    Select Ticket Type
                                </option>
                            </select>

                        </div>

                        <div class="space-y-2">

                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Priority
                            </label>

                            <select name="ticket_priority" id="create_ticket_priority"
                                class="form-select-ticket w-full">
                                <option value="">
                                    Select Priority
                                </option>
                            </select>

                        </div>

                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div class="space-y-2">

                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category
                            </label>

                            <select name="ticket_categoryid" id="create_ticket_categoryid"
                                class="form-select-ticket w-full">
                                <option value="">
                                    Select Category
                                </option>
                            </select>

                        </div>

                        <div class="space-y-2">

                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Subcategory
                            </label>

                            <select name="ticket_subcategoryid" id="create_ticket_subcategoryid"
                                class="form-select-ticket w-full">
                                <option value="">
                                    Select Subcategory
                                </option>
                            </select>

                        </div>

                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div class="space-y-2">

                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Location
                            </label>

                            <select name="location_id" id="create_location_id" class="form-select-ticket w-full">
                                <option value="">
                                    Select Location
                                </option>
                            </select>

                        </div>

                        <div class="space-y-2">

                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Sub Location
                            </label>

                            <select name="sub_location_id" id="create_sub_location_id"
                                class="form-select-ticket w-full">
                                <option value="">
                                    Select Sub Location
                                </option>
                            </select>

                        </div>

                    </div>

                    <div class="space-y-2">

                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Issue Summary
                        </label>

                        <input type="text" name="issue_summary" maxlength="255" class="form-input-ticket w-full"
                            placeholder="Enter issue summary">

                    </div>

                    <div class="space-y-2">

                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Issue Description
                        </label>

                        <textarea name="issue_descr" rows="6" class="form-textarea-ticket w-full"
                            placeholder="Explain your issue detail..."></textarea>

                    </div>

                    <div class="space-y-2">

                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Attachment
                        </label>

                        <div class="rounded-2xl border-2 border-dashed border-gray-300 p-6 dark:border-neutral-700">

                            <input type="file" name="attachments[]" id="create_ticket_attachment" multiple
                                class="hidden">

                            <div class="flex flex-col items-center justify-center text-center">

                                <div
                                    class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 dark:bg-blue-500/20">

                                    <i class="ti ti-paperclip text-2xl text-blue-600 dark:text-blue-400"></i>

                                </div>

                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Upload attachment
                                </p>

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    PNG, JPG, PDF, DOCX, XLSX up to 10MB
                                </p>

                                <button type="button" id="btnBrowseCreateAttachment"
                                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">

                                    <i class="ti ti-upload"></i>

                                    <span>
                                        Browse File
                                    </span>

                                </button>

                            </div>

                        </div>

                        <div id="createAttachmentPreview" class="space-y-2"></div>

                    </div>

                </div>

                <div
                    class="flex items-center justify-end gap-3 border-t border-gray-200 px-5 py-4 dark:border-neutral-800">

                    <button type="button" data-close-modal="modalCreateTicket"
                        class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-gray-300 dark:hover:bg-neutral-800">

                        Cancel
                    </button>

                    <button type="submit" id="btnSubmitCreateTicket"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">

                        <i class="ti ti-device-floppy"></i>

                        <span>
                            Submit Ticket
                        </span>

                    </button>

                </div>

            </form>

        </div>

    </div>


</x-app-layout>

<script>
    const currentUser = @json(auth()->user()->username);
</script>
<script>
    window.authUsername = @json(auth()->user()->username);
    window.authRole = @json(auth()->user()->role_id);
    window.modalType = @json($modalType ?? null);
    window.modalAccess = @json($eid ?? null);
</script>
<script>
    window.ticketConfig = {
        showModal: @json($showModal ?? false),
        modalType: @json($modalType),
        modalTicket: @json($modalTicket),
    };
</script>

<script src="{{ asset('assets/js/access-request/core.js') }}"></script>
<script src="{{ asset('assets/js/access-request/helper.js') }}"></script>
<script src="{{ asset('assets/js/access-request/modal.js') }}"></script>
<script src="{{ asset('assets/js/access-request/datatable.js') }}"></script>
<script src="{{ asset('assets/js/access-request/detail-modal.js') }}"></script>
<script src="{{ asset('assets/js/access-request/request-form.js') }}"></script>
<script src="{{ asset('assets/js/access-request/approval.js') }}"></script>
<script src="{{ asset('assets/js/access-request/attachment.js') }}"></script>
<script src="{{ asset('assets/js/access-request/process-hardware.js') }}"></script>
<script src="{{ asset('assets/js/access-request/process-software.js') }}"></script>
