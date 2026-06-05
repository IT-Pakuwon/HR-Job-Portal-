<div id="processTicketModal" data-form-modal="true"
    class="ticket-modal fixed inset-0 z-[9999] hidden overflow-y-hidden p-4">

    {{-- Backdrop --}}
    <div
        class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
    </div>

    {{-- Wrapper --}}
    <div class="modal-panel relative z-10 mx-auto flex min-h-screen w-full items-center justify-center">

        {{-- Modal --}}
        <div
            class="modal-scroll flex max-h-[95vh] w-full max-w-[95vw] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-white/[0.06] dark:bg-[#0f172a] sm:max-w-4xl">

            {{-- Header --}}
            <div
                class="sticky top-0 z-20 flex items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-4 py-4     dark:border-white/[0.06] dark:bg-[#0f172a]/95 sm:px-6 sm:py-5">

                <div class="min-w-0">

                    <h2 class="truncate text-lg font-semibold tracking-tight text-slate-800 dark:text-white sm:text-xl">
                        Process Ticket
                    </h2>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                        Update progress and provide working solution.
                    </p>

                </div>

                <button type="button"
                    class="btn-close-form-modal inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-500 transition-all duration-200 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.06] dark:hover:text-white">

                    <i class="fa-solid fa-xmark text-base"></i>

                </button>

            </div>

            {{-- Form --}}
            <form id="processTicketForm"
                enctype="multipart/form-data"
                class="flex min-h-0 flex-1 flex-col">

                @csrf

                <input type="hidden"
                    id="process_ticket_eid"
                    name="eid">

                {{-- Body --}}
                <div class="flex-1 space-y-5 overflow-y-auto px-4 py-4 sm:space-y-6 sm:px-6 sm:py-6">

                    {{-- Ticket Information --}}
                    <div class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2">

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/[0.06] dark:bg-white/[0.03]">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">

                                Ticket ID

                            </div>

                            <div id="process_ticketid"
                                class="mt-1 text-sm font-semibold text-slate-800 dark:text-white">

                                -

                            </div>

                        </div>

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/[0.06] dark:bg-white/[0.03]">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">

                                Current PIC

                            </div>

                            <div id="process_pic_ticket"
                                class="mt-1 text-sm font-semibold text-slate-800 dark:text-white">

                                -

                            </div>

                        </div>

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/[0.06] dark:bg-white/[0.03]">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">

                                Category

                            </div>

                            <div id="process_ticket_category"
                                class="mt-1 text-sm font-semibold text-slate-800 dark:text-white">

                                -

                            </div>

                        </div>

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/[0.06] dark:bg-white/[0.03]">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">

                                SLA Due Date

                            </div>

                            <div id="process_ticket_sla"
                                class="mt-1 text-sm font-semibold text-slate-800 dark:text-white">

                                -

                            </div>

                        </div>

                    </div>

                    {{-- Process Detail --}}
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-white/[0.06] dark:bg-white/[0.03]">

                        <div class="mb-4">

                            <h3 class="text-sm font-semibold text-slate-800 dark:text-white">

                                Process Information

                            </h3>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">

                                Update process activity and solution detail.

                            </p>

                        </div>

                        <div class="space-y-5">

                            {{-- Process Description --}}
                            <div>

                                <div class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Process Description
                                </div>

                                <textarea id="response_descr"
                                    name="response_descr"
                                    rows="5"
                                    class="form-textarea w-full"
                                    placeholder="Write current process progress..."></textarea>

                            </div>

                        </div>

                    </div>

                    {{-- Working Schedule --}}
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-white/[0.06] dark:bg-white/[0.03]">

                        <div class="mb-4 flex items-center justify-between gap-4">

                            <div>

                                <h3 class="text-sm font-semibold text-slate-800 dark:text-white">

                                    Working Schedule

                                </h3>

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">

                                    Optional schedule adjustment.

                                </p>

                            </div>

                            <label
                                class="inline-flex items-center gap-3 text-sm font-medium text-slate-700 dark:text-slate-300">

                                <input type="checkbox"
                                    id="process_use_schedule"
                                    class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-400">

                                <span>

                                    Update Schedule

                                </span>

                            </label>

                        </div>

                        <div id="process_schedule_container"
                            class="hidden">

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                <div>

                                <div class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Working Start Date
                                </div>

                                    <input type="datetime-local"
                                        id="process_working_start_date"
                                        name="working_start_date"
                                        class="form-input">

                                </div>

                                <div>

                                <div class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Working End Date
                                </div>

                                    <input type="datetime-local"
                                        id="process_working_end_date"
                                        name="working_end_date"
                                        class="form-input">

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- Attachment --}}
                   <div
    class="overflow-visible rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-white/[0.06] dark:bg-white/[0.03]">

                        <div class="mb-4">

                            <h3 class="text-sm font-semibold text-slate-800 dark:text-white">

                                Process Attachment

                            </h3>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">

                                Upload evidence, screenshot, or supporting files.

                            </p>

                        </div>

                        <label for="process_attachments"
                            class="group flex cursor-pointer items-center justify-center gap-4 rounded-xl border border-dashed border-slate-300 bg-white px-5 py-6 transition-all duration-200 hover:border-slate-400 hover:bg-slate-50 dark:border-white/[0.08] dark:bg-white/[0.03] dark:hover:border-blue-500/30 dark:hover:bg-blue-500/[0.05]">

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 dark:border-white/[0.06] dark:bg-white/[0.04] dark:text-slate-300">

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

                            <input type="file"
                                id="process_attachments"
                                name="attachments[]"
                                multiple
                                class="hidden">

                        </label>

                        <div class="mt-4 space-y-3">

                            <div id="process_existing_attachment_list"
                                class="space-y-3">
                            </div>

                            <div id="process_new_attachment_list"
                                class="space-y-3">
                            </div>

                        </div>

                    </div>

                </div>

                {{-- Footer --}}
                <div
                    class="sticky bottom-0 z-20 flex flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-4 py-4     dark:border-white/[0.06] dark:bg-[#0f172a]/95 sm:flex-row sm:items-center sm:justify-end sm:px-6 sm:py-5">

                    <button type="button"
                        class="btn-close-form-modal inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5">
                        Cancel

                    </button>

                    <button type="submit"
                        id="btnSubmitProcessTicket"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition-all duration-200 hover:scale-[1.01] hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                        Submit Process

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
