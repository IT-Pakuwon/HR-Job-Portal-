
<div id="showModal"
    class="fixed inset-0 z-[70] hidden bg-black/50 backdrop-blur-sm">

    <div class="flex h-full w-full items-center justify-center p-4 sm:p-6">
    <div
        class="relative mx-auto w-full max-w-5xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">

            {{-- HEADER --}}
            <div
                class="flex flex-col gap-4 border-b border-gray-200 bg-white px-6 py-5 dark:border-white/10 dark:bg-[#111827] lg:flex-row lg:items-start lg:justify-between">

                <div>

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-900 text-white dark:bg-white dark:text-gray-900">

                            <i class="ri-file-list-3-line text-lg"></i>

                        </div>

                        <div>

                            <h2
                                class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">

                                IT Recommendation Detail

                            </h2>

                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-gray-400">

                                Document information and approval tracking

                            </p>

                        </div>

                    </div>

                </div>

                <div class="flex items-center gap-3">

                    <div id="approvalActionContainer" class="hidden items-center gap-2">

                        <button
                            id="btnApprove"
                            type="button"
                            class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">

                            <i class="ri-check-line mr-2"></i>
                            Approve

                        </button>

                        <button
                            id="btnRevise"
                            type="button"
                            class="inline-flex items-center rounded-xl bg-yellow-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-yellow-600">

                            <i class="ri-edit-line mr-2"></i>
                            Revise

                        </button>

                        <button
                            id="btnReject"
                            type="button"
                            class="inline-flex items-center rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">

                            <i class="ri-close-line mr-2"></i>
                            Reject

                        </button>

                    </div>

                    {{-- EDIT / CANCEL --}}
                    <div id="documentActionContainer" class="hidden items-center gap-2">
                        <button
                            id="btnCancelDocument"
                            type="button"
                            class="inline-flex items-center rounded-xl bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800">

                            <i class="ri-close-circle-line mr-2"></i>
                            Cancel

                        </button>

                    </div>

                    <button
                        id="btnCloseShowModal"
                        type="button"
                        class="flex h-10 items-center gap-2 rounded-xl border border-gray-200 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-800 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            class="h-4 w-4">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"/>

                        </svg>

                        <span>Close</span>

                    </button>

                </div>

            </div>

            {{-- BODY --}}
            <div class="max-h-[calc(100vh-120px)] overflow-y-auto">

                <div class="grid grid-cols-1 gap-6 p-6 xl:grid-cols-3">

                    {{-- LEFT --}}
                    <div class="space-y-6 xl:col-span-2">

                        {{-- DOCUMENT INFO --}}
                        <div
                            class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-white/10 dark:bg-[#111827]">

                            <div
                                class="border-b border-gray-100 px-5 py-4 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">

                                    Document Information

                                </h3>

                            </div>

                            <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

                                <div class="space-y-1">

                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-gray-400">

                                        Document ID

                                    </p>

                                    <p id="showDocid"
                                        class="text-sm font-semibold text-gray-800 dark:text-white">
                                    </p>

                                </div>

                                <div class="space-y-1">

                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-gray-400">

                                        Date

                                    </p>

                                    <p id="showDate"
                                        class="text-sm text-gray-700 dark:text-gray-200">
                                    </p>

                                </div>

                                <div class="space-y-1">

                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-gray-400">

                                        Company

                                    </p>

                                    <p id="showCompany"
                                        class="text-sm text-gray-700 dark:text-gray-200">
                                    </p>

                                </div>

                                <div class="space-y-1">

                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-gray-400">

                                        Department

                                    </p>

                                    <p id="showDepartment"
                                        class="text-sm text-gray-700 dark:text-gray-200">
                                    </p>

                                </div>

                                <div class="space-y-1">

                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-gray-400">

                                        Requester

                                    </p>

                                    <p id="showRequester"
                                        class="text-sm text-gray-700 dark:text-gray-200">
                                    </p>

                                </div>

                                <div class="space-y-1">

                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-gray-400">

                                        Ticket Number

                                    </p>

                                    <p id="showTicket"
                                        class="text-sm text-gray-700 dark:text-gray-200">
                                    </p>

                                </div>

                                <div class="space-y-1 md:col-span-2">

                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-gray-400">

                                        Asset Number

                                    </p>

                                    <p id="showAsset"
                                        class="text-sm text-gray-700 dark:text-gray-200">
                                    </p>

                                </div>

                            </div>

                        </div>

                        {{-- PURPOSE --}}
                        <div
                            class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-white/10 dark:bg-[#111827]">

                            <div
                                class="border-b border-gray-100 px-5 py-4 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">

                                    Purpose / Requirement

                                </h3>

                            </div>

                            <div class="p-5">

                                <div id="showPurpose"
                                    class="whitespace-pre-wrap break-words text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                                </div>

                            </div>

                        </div>

                        {{-- DETAIL --}}
                        <div
                            class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-white/10 dark:bg-[#111827]">

                            <div
                                class="border-b border-gray-100 px-5 py-4 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">

                                    Recommendation Detail

                                </h3>

                            </div>

                            <div class="overflow-x-auto">

                                <table class="min-w-full text-sm">

                                    <thead
                                        class="border-b border-gray-100 bg-gray-50 dark:border-white/10 dark:bg-white/5">

                                        <tr>

                                            <th
                                                class="px-5 py-3 text-left font-semibold text-gray-500 dark:text-gray-300">

                                                Description

                                            </th>

                                            <th
                                                class="px-5 py-3 text-left font-semibold text-gray-500 dark:text-gray-300">

                                                Qty

                                            </th>

                                            <th
                                                class="px-5 py-3 text-left font-semibold text-gray-500 dark:text-gray-300">

                                                UOM

                                            </th>

                                            <th
                                                class="px-5 py-3 text-left font-semibold text-gray-500 dark:text-gray-300">

                                                Category

                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody id="showDetailBody"
                                        class="divide-y divide-gray-100 dark:divide-white/10">

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                    {{-- RIGHT --}}
                    <div class="space-y-6">

                        {{-- APPROVAL --}}
                        <div
                            class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-white/10 dark:bg-[#111827]">

                            <div
                                class="border-b border-gray-100 px-5 py-4 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">

                                    Approval Flow

                                </h3>

                            </div>

                            <div id="approvalContainer"
                                class="space-y-5 p-5">

                            </div>

                        </div>

                        {{-- ATTACHMENT --}}
                        <div
                            class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-white/10 dark:bg-[#111827]">

                            <div
                                class="border-b border-gray-100 px-5 py-4 dark:border-white/10">

                                <h3
                                    class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">

                                    Attachment

                                </h3>

                            </div>

                            <div id="attachmentContainer"
                                class="space-y-3 p-5">

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
