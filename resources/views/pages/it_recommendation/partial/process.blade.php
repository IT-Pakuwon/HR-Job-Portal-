<div id="processModal"
    class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 p-4 backdrop-blur-sm">

    <div
        class="relative flex h-[95vh] w-full max-w-[1700px] flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-900">

        <div
            class="flex shrink-0 items-start justify-between border-b border-gray-200 px-8 py-6 dark:border-white/10">

            <div class="flex items-start gap-4">

                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-900 text-2xl text-white dark:bg-white dark:text-gray-900">
                    <i class="ri-tools-line"></i>
                </div>

                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Process IT Recommendation
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Add recommendation items and submit approval flow
                    </p>
                </div>

            </div>

            <button id="btnCloseProcessModal"
                class="flex h-12 w-12 items-center justify-center rounded-2xl border border-gray-200 text-gray-500 transition hover:bg-gray-100 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">

                <i class="ri-close-line text-xl"></i>

            </button>

        </div>

        <div class="flex-1 overflow-y-auto px-8 py-6">

            <div class="grid grid-cols-1 gap-6 2xl:grid-cols-12">

                <div class="space-y-6 2xl:col-span-9">

                    <div class="rounded-3xl border border-gray-200 dark:border-white/10">

                        <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">

                            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-700 dark:text-white">
                                Document Information
                            </h3>

                        </div>

                        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2 xl:grid-cols-4">

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Document ID
                                </p>

                                <p id="processDocid"
                                    class="mt-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    -
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Ticket Number
                                </p>

                                <p id="processTicket"
                                    class="mt-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    -
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Requester
                                </p>

                                <p id="processRequester"
                                    class="mt-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    -
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Company
                                </p>

                                <p id="processCompany"
                                    class="mt-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    -
                                </p>
                            </div>

                            <div class="xl:col-span-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Purpose / Requirement
                                </p>

                                <p id="processPurpose"
                                    class="mt-2 whitespace-normal break-words text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                                    -
                                </p>
                            </div>

                        </div>

                    </div>

                    <div class="rounded-3xl border border-gray-200 dark:border-white/10">

                        <div
                            class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wide text-gray-700 dark:text-white">
                                    Recommendation Items
                                </h3>

                                <p class="mt-1 text-xs text-gray-400">
                                    Select inventory recommendation items
                                </p>
                            </div>

                            <button
                                type="button"
                                id="btnAddProcessRow"
                                class="inline-flex items-center rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-black dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">

                                <i class="ri-add-line mr-2"></i>
                                Add Item

                            </button>

                        </div>

                        <div class="p-6">

                            <div id="processTableBody" class="space-y-5">

                                <div
                                    class="rounded-2xl border border-dashed border-gray-200 px-6 py-10 text-center text-sm text-gray-400 dark:border-white/10 dark:text-gray-500">

                                    No recommendation item added

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="space-y-6 2xl:col-span-3">

                    <div class="rounded-3xl border border-gray-200 dark:border-white/10">

                        <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">

                            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-700 dark:text-white">
                                Approval Information
                            </h3>

                        </div>

                        <div class="space-y-4 p-6">

                            <div
                                class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-5 py-5 text-sm leading-relaxed text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">

                                After processing, approval flow will automatically be generated.

                            </div>

                            <div
                                class="rounded-2xl bg-amber-50 px-5 py-4 text-xs leading-relaxed text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">

                                Make sure selected recommendation items are valid before submitting.

                            </div>

                        </div>

                    </div>

                    <div class="rounded-3xl border border-gray-200 dark:border-white/10">

                        <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">

                            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-700 dark:text-white">
                                Actions
                            </h3>

                        </div>

                        <div class="space-y-3 p-6">

                            <button type="button" id="btnSubmitProcess"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-black dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">

                                <i class="ri-send-plane-line mr-2"></i>
                                Submit Process

                            </button>

                            <button type="button" id="btnCancelProcess"
                                class="inline-flex w-full items-center justify-center rounded-2xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">

                                Cancel

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
