<div id="pendingTicketModal" data-form-modal="true"
    class="ticket-modal fixed inset-0 z-[9999] hidden overflow-y-hidden p-4">

    <div
        class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
    </div>

    <div
        class="modal-panel relative z-10 mx-auto flex min-h-screen w-full items-center justify-center">

        <div
            class="modal-scroll flex max-h-[95vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div
                class="sticky top-0 z-20 flex items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-5 py-4     dark:border-white/[0.06] dark:bg-[#0f172a]/95">

                <div>

                    <h2
                        class="text-lg font-semibold tracking-tight text-slate-800 dark:text-white">

                        Pending Ticket

                    </h2>

                    <p
                        class="mt-1 text-xs text-slate-500 dark:text-slate-400">

                        Update pending reason and optional working schedule.

                    </p>

                </div>

                <button type="button"
                    class="btn-close-form-modal inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition-all duration-200 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.06] dark:hover:text-white">

                    <i class="fa-solid fa-xmark text-base"></i>

                </button>

            </div>

            <form id="pendingTicketForm"
                class="flex min-h-0 flex-1 flex-col">

                @csrf

                <input type="hidden"
                    id="pending_ticket_eid"
                    name="eid">

                <div
                    class="flex-1 space-y-5 overflow-y-auto px-5 py-5">

                    <div
                        class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/[0.06] dark:bg-white/[0.03]">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">

                                Ticket Number

                            </div>

                            <div id="pending_ticketid"
                                class="mt-1 text-sm font-semibold text-slate-800 dark:text-white">

                                -

                            </div>

                        </div>

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/[0.06] dark:bg-white/[0.03]">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">

                                PIC

                            </div>

                            <div id="pending_pic_ticket"
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

                            <div id="pending_ticket_category"
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

                            <div id="pending_ticket_sla"
                                class="mt-1 text-sm font-semibold text-slate-800 dark:text-white">

                                -

                            </div>

                        </div>

                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 dark:border-white/[0.06] dark:bg-white/[0.03]">

                        <div class="mb-5">

                            <h3
                                class="text-sm font-semibold text-slate-800 dark:text-white">

                                Pending Information

                            </h3>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400">

                                Explain why ticket needs to be pending.

                            </p>

                        </div>

                        <div class="space-y-5">

                            <div>

                                <div class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Pending Reason
                                </div>

                                <textarea id="pending_response_descr" name="response_descr" class="hidden"></textarea>
                                <div id="pending_descr_editor" class="rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]" style="min-height:120px;"></div>

                            </div>

                        </div>

                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 dark:border-white/[0.06] dark:bg-white/[0.03]">

                        <div
                            class="flex items-center justify-between gap-4">

                            <div>

                                <h3
                                    class="text-sm font-semibold text-slate-800 dark:text-white">

                                    Working Schedule

                                </h3>

                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400">

                                    Optional working adjustment.

                                </p>

                            </div>

                            <label
                                class="inline-flex cursor-pointer items-center gap-2">

                                <input type="checkbox"
                                    id="pending_use_schedule"
                                    class="rounded border-slate-300 text-slate-900 focus:ring-slate-400">

                                <span
                                    class="text-sm text-slate-600 dark:text-slate-300">

                                    Update Schedule

                                </span>

                            </label>

                        </div>

                        <div id="pending_schedule_container"
                            class="mt-5 hidden">

                            <div
                                class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                <div>

                                <div class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Working Start Date
                                </div>

                                    <input type="datetime-local"
                                        id="pending_working_start_date"
                                        name="working_start_date"
                                        class="form-input">

                                </div>

                                <div>

                                <div class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Working End Date
                                </div>

                                    <input type="datetime-local"
                                        id="pending_working_end_date"
                                        name="working_end_date"
                                        class="form-input">

                                </div>

                            </div>

                        </div>

                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 dark:border-white/[0.06] dark:bg-white/[0.03]">

                        <div class="mb-5">

                            <h3
                                class="text-sm font-semibold text-slate-800 dark:text-white">

                                Pending Attachment

                            </h3>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400">

                                Upload supporting evidence or screenshot.

                            </p>

                        </div>

                        <label
                            class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-8 text-center transition-all duration-200 hover:border-slate-400 hover:bg-slate-50 dark:border-white/[0.08] dark:bg-white/[0.02] dark:hover:border-white/[0.15] dark:hover:bg-white/[0.04]">

                            <input type="file"
                                id="pending_attachments"
                                class="hidden"
                                multiple>

                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition-all duration-200 group-hover:bg-slate-200 dark:bg-white/[0.05] dark:text-slate-300 dark:group-hover:bg-white/[0.08]">

                                <i class="fa-solid fa-cloud-arrow-up text-lg"></i>

                            </div>

                            <div
                                class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">

                                Upload Attachment

                            </div>

                            <div
                                class="mt-1 text-xs text-slate-400 dark:text-slate-500">

                                PDF, DOCX, XLSX, PNG, JPG

                            </div>

                        </label>

                        <div class="mt-4 space-y-3">

                            <div id="pending_existing_attachment_list"
                                class="space-y-3">
                            </div>

                            <div id="pending_new_attachment_list"
                                class="space-y-3">
                            </div>

                        </div>

                    </div>

                </div>

                <div
                    class="sticky bottom-0 z-20 flex items-center justify-end gap-3 border-t border-slate-200 bg-white/95 px-5 py-4     dark:border-white/[0.06] dark:bg-[#0f172a]/95">

                    <button type="button"
                        class="btn-close-form-modal inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5">
                        Cancel

                    </button>

                    <button type="submit"
                        id="btnSubmitPendingTicket"
                          class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition-all duration-200 hover:scale-[1.01] hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">

                        Submit Pending

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
