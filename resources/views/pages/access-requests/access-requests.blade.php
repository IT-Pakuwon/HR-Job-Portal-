<x-app-layout>

    @include('pages.access-requests.partials.styles')
    <div class="max-w-9xl mx-auto w-full p-2">

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


        <div class="mt-4 flex flex-col gap-4 rounded-lg bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-center justify-between gap-4 sm:flex-row sm:items-center">
                <div class="flex flex-col gap-2">

                    <h1 class="text-base font-extrabold text-gray-700 dark:text-white">
                        Access Request
                    </h1>

                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Manage hardware and software access request workflow.
                    </p>

                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                    <button id="btnCreate" type="button"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-medium text-white transition hover:bg-slate-800">
                        <i class="fa-solid fa-plus text-xs"></i>
                        Create Request
                    </button>

                </div>

            </div>


            <div class="rounded-base relative overflow-x-auto"> {{-- Padding applied here instead of outer container --}}

                <table id="accessRequestTable" class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-white dark:bg-[#0f172a]">

                        <tr>

                            {{-- RESPONSIVE CONTROL --}}
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

                    <tbody class="divide-y divide-slate-100 bg-white dark:bg-[#0f172a]"></tbody>

                </table>

            </div>

        </div>


        {{-- CREATE / EDIT MODAL --}}
        <div id="requestModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 p-4">
            <div
                class="modal-scroll flex max-h-[95vh] w-full max-w-6xl flex-col overflow-y-auto rounded-lg bg-white shadow-2xl">
                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-7 py-5 dark:border-white/10">
                    <div>
                        <h2 id="requestModalTitle" class="text-xl font-bold text-slate-900 dark:text-white">
                            Create Access Request
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Hardware & software access request form.
                        </p>
                    </div>

                    <button type="button" class="btn-close-modal text-slate-400 transition hover:text-slate-700">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <div class="space-y-2 p-4">


                    <form id="requestForm" class="space-y-2">

                        <input type="hidden" id="requestMethod" value="POST">
                        <input type="hidden" id="requestUrl">
                        <input type="hidden" id="requestHash">

                        <div class="rounded-lg border border-slate-200 bg-white dark:border-white/10">

                            <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">
                                    Request Information
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 gap-2 p-4 md:grid-cols-2">

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Request Date
                                    </label>

                                    <input type="date" id="access_date" name="access_date"
                                        value="{{ now()->format('Y-m-d') }}"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm focus:border-slate-400 focus:ring-0 dark:border-white/10">
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Request Type
                                    </label>

                                    <select id="access_type" name="access_type"
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10">

                                        <option value="">Choose Type</option>
                                        <option value="NEW">New Access</option>
                                        <option value="CHANGE">Change Access</option>
                                        <option value="REMOVE">Remove Access</option>

                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Company
                                    </label>

                                    @if (count($companies) <= 1)

                                        <input type="text" value="{{ $companies[0] ?? '-' }}"
                                            class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm text-slate-700 dark:border-white/10"
                                            readonly>

                                        <input type="hidden" id="cpny_id" name="cpny_id"
                                            value="{{ $companies[0] ?? '' }}">
                                    @else
                                        <select id="cpny_id" name="cpny_id"
                                            class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10">

                                            <option value="">Choose Company</option>

                                            @foreach ($companies as $company)
                                                <option value="{{ $company }}">
                                                    {{ $company }}
                                                </option>
                                            @endforeach

                                        </select>

                                    @endif
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Department
                                    </label>

                                    @if (count($departments) <= 1)

                                        <input type="text" value="{{ $departments[0] ?? '-' }}"
                                            class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm text-slate-700 dark:border-white/10"
                                            readonly>

                                        <input type="hidden" id="department_id" name="department_id"
                                            value="{{ $departments[0] ?? '' }}">
                                    @else
                                        <select id="department_id" name="department_id"
                                            class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10">

                                            <option value="">Choose Department</option>

                                            @foreach ($departments as $department)
                                                <option value="{{ $department }}">
                                                    {{ $department }}
                                                </option>
                                            @endforeach

                                        </select>

                                    @endif
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        Purpose / Notes
                                    </label>

                                    <textarea id="keperluan" name="keperluan" rows="4" placeholder="Input request purpose..."
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm focus:border-slate-400 focus:ring-0 dark:border-white/10"></textarea>
                                </div>

                            </div>

                        </div>

                        <div class="rounded-lg border border-slate-200 bg-white dark:border-white/10">

                            <div
                                class="flex items-center justify-between border-b border-slate-200 px-2 py-4 dark:border-white/10">

                                <div>
                                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">
                                        Access Request Detail
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Hardware & software request item.
                                    </p>
                                </div>

                                <button type="button" id="btnAddDetail"
                                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 text-sm font-medium text-white transition hover:bg-slate-800">

                                    <i class="fa-solid fa-plus text-xs"></i>
                                    Add Item
                                </button>

                            </div>

                            <div class="overflow-x-auto">

                                <table class="min-w-full divide-y divide-slate-200">

                                    <thead class="bg-white dark:bg-[#0f172a]">

                                        <tr>

                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                                Category
                                            </th>

                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                                Group
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                                Action
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody id="requestDetailContainer" class="divide-y divide-slate-100 bg-white">

                                        {{-- JS RENDER --}}

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <div class="rounded-lg border border-slate-200 bg-white dark:border-white/10">

                            <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">
                                    Attachment
                                </h3>
                            </div>

                            <div class="p-4">

                                <label for="requestAttachment"
                                    class="flex cursor-pointer items-center justify-center gap-3 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-5 transition hover:border-slate-400 hover:bg-slate-100 dark:border-white/10 dark:bg-[#0f172a]">

                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm dark:bg-white/10">
                                        <i class="fa-solid fa-cloud-arrow-up text-slate-500"></i>
                                    </div>

                                    <div class="text-left">
                                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                            Upload Attachment
                                        </p>

                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            PDF, DOCX, XLSX, PNG, JPG
                                        </p>
                                    </div>

                                    <input type="file" id="requestAttachment" name="attachments[]" multiple
                                        class="hidden">

                                </label>

                                <div id="existingAttachmentContainer" class="mt-3 space-y-2"></div>

                                <div id="newAttachmentContainer" class="mt-3 space-y-2"></div>

                            </div>

                        </div>

                        <div
                            class="sticky bottom-0 z-20 mt-4 border-t border-slate-200 bg-white px-5 py-4 dark:border-white/10 dark:bg-[#0f172a]">

                            <div class="flex items-center justify-between">

                                <div class="flex items-center gap-2">

                                    <span
                                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-white/10 dark:text-slate-300">

                                        Total :
                                        <span id="summaryTotalItem" class="ml-1 font-semibold">
                                            0
                                        </span>

                                    </span>

                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">

                                        HW :
                                        <span id="summaryHardware" class="ml-1 font-semibold">
                                            0
                                        </span>

                                    </span>

                                    <span
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">

                                        SW :
                                        <span id="summarySoftware" class="ml-1 font-semibold">
                                            0
                                        </span>

                                    </span>

                                </div>

                                <div class="flex items-center gap-3">

                                    <button type="button"
                                        class="btn-close-modal inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-[#0f172a] dark:text-slate-300 dark:hover:bg-white/5">

                                        Cancel

                                    </button>

                                    <button type="button" id="btnSubmitRequest"
                                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-medium text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

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
        <div id="detailModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 p-4">

            <div
                class="modal-scroll flex max-h-[95vh] w-full max-w-7xl flex-col overflow-y-auto rounded-lg bg-white shadow-2xl">

                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-7 py-5 dark:border-white/10">
                    <div class="flex items-center gap-3">

                        <h2 id="detailModalDocId" class="text-xl font-bold text-slate-900 dark:text-white">
                            -
                        </h2>

                        <div id="detailModalStatus"></div>

                    </div>

                    <div class="flex items-center gap-2">

                        <button
                            type="button"
                            class="
                                btn-print-access
                                inline-flex h-10 w-10
                                items-center justify-center
                                rounded-xl border border-slate-200
                                bg-white text-slate-600
                                transition hover:bg-slate-100
                            "
                            id="btnPrintAccess"
                            title="Print"
                        >

                            <i class="fa-solid fa-print text-sm"></i>

                        </button>

                        <button
                            type="button"
                            class="
                                btn-close-modal
                                inline-flex h-10 w-10
                                items-center justify-center
                                rounded-xl text-slate-400
                                transition hover:bg-slate-100
                                hover:text-slate-600
                            "
                            data-modal="#detailModal"
                        >

                            <i class="fa-solid fa-xmark text-lg"></i>

                        </button>

                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 p-4">

                    <div class="space-y-5 lg:col-span-2">

                        <div id="detailInfoContainer"></div>

                        <div id="detailItemsContainer"></div>

                        <div id="detailAttachmentContainer"></div>

                    </div>

                    <div class="space-y-2">

                        <div id="detailActionContainer"></div>

                        <div id="detailActivityContainer"></div>

                    </div>
                </div>

            </div>

        </div>

        {{-- PROCESS HARDWARE MODAL --}}
        <div id="processHardwareModal"
            class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 p-4">

            <div
                class="modal-scroll flex max-h-[95vh] w-full max-w-6xl flex-col overflow-y-auto rounded-lg bg-white shadow-2xl">

                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-7 py-5 dark:border-white/10">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                            Process Hardware Access
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Complete requested hardware access.
                        </p>
                    </div>

                    <button type="button" class="btn-close-modal text-slate-400 transition hover:text-slate-700">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                <div class="space-y-4 p-4">

                    <div id="processHardwareInfoContainer"></div>

                    <div id="processHardwareDetailContainer"></div>

                </div>

            </div>

        </div>

        {{-- PROCESS SOFTWARE MODAL --}}
        <div id="processSoftwareModal"
            class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 p-4">

            <div
                class="modal-scroll flex max-h-[95vh] w-full max-w-6xl flex-col overflow-y-auto rounded-lg bg-white shadow-2xl">

                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-7 py-5 dark:border-white/10">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                            Process Software Access
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Complete requested software access.
                        </p>
                    </div>

                    <button type="button" class="btn-close-modal text-slate-400 transition hover:text-slate-700">
                        <i class="fa-solid fa-xmark text-xl"></i>
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


</x-app-layout>

<script>
    const currentUser = @json(auth()->user()->username);
</script>
<script>
    window.authUsername = @json(auth()->user()->username);
    window.authRole = @json(auth()->user()->role_id);
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
