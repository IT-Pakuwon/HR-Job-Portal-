<div id="transferTicketModal" class="ticket-modal fixed inset-0 z-[9999] hidden overflow-y-auto p-4">

    {{-- Backdrop --}}
    <div
        class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
    </div>

    {{-- Wrapper --}}
    <div class="modal-panel relative z-10 mx-auto flex min-h-screen w-full items-center justify-center">

        {{-- Modal --}}
        <div
            class="modal-scroll flex max-h-[95vh] w-full max-w-[95vw] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-white/[0.06] dark:bg-[#0f172a] sm:max-w-3xl">

            {{-- Header --}}
            <div
                class="sticky top-0 z-20 flex items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-4 py-4 backdrop-blur-xl dark:border-white/[0.06] dark:bg-[#0f172a]/95 sm:px-6 sm:py-5">

                <div class="min-w-0">

                    <h2 class="truncate text-lg font-semibold tracking-tight text-slate-800 dark:text-white sm:text-xl">
                        Transfer Ticket
                    </h2>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                        Transfer ticket category and assign new PIC.
                    </p>

                </div>

                <button type="button"
                    onclick="closeModal('#transferTicketModal')"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-500 transition-all duration-200 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.06] dark:hover:text-white">

                    <i class="fa-solid fa-xmark text-base"></i>

                </button>

            </div>

            {{-- Form --}}
            <form id="transferTicketForm" class="flex min-h-0 flex-1 flex-col">

                @csrf

                <input type="hidden" id="transfer_ticket_eid" name="eid">

                {{-- Body --}}
                <div class="flex-1 space-y-5 overflow-y-auto px-4 py-4 sm:space-y-6 sm:px-6 sm:py-6">

                    {{-- Current Ticket --}}
                    <div class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2">

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/[0.06] dark:bg-white/[0.03]">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">
                                Current Category
                            </div>

                            <div id="transfer_current_category"
                                class="mt-1 text-sm font-semibold text-slate-800 dark:text-white">
                                -
                            </div>

                        </div>

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/[0.06] dark:bg-white/[0.03]">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">
                                Current Sub Category
                            </div>

                            <div id="transfer_current_subcategory"
                                class="mt-1 text-sm font-semibold text-slate-800 dark:text-white">
                                -
                            </div>

                        </div>

                    </div>

                    {{-- Transfer Config --}}
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-white/[0.06] dark:bg-white/[0.03]">

                        <div class="mb-4">

                            <h3 class="text-sm font-semibold text-slate-800 dark:text-white">
                                Transfer Configuration
                            </h3>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Select new category, subcategory, and PIC assignment.
                            </p>

                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                            <div>

                                <label class="form-label">
                                    Ticket Category
                                </label>

                                <select id="transfer_ticket_categoryid"
                                    name="ticket_categoryid"
                                    class="form-select"
                                    required>
                                </select>

                            </div>

                            <div>

                                <label class="form-label">
                                    Ticket Sub Category
                                </label>

                                <select id="transfer_ticket_subcategoryid"
                                    name="ticket_subcategoryid"
                                    class="form-select"
                                    required>
                                </select>

                            </div>

                            <div>

                                <label class="form-label">
                                    PIC
                                </label>

                                <select id="transfer_pic_ticket"
                                    name="pic_ticket"
                                    class="form-select"
                                    required>

                                    <option value="">
                                        Select PIC
                                    </option>

                                </select>

                            </div>

                            <div>

                                <label class="form-label">
                                    Ticket Type
                                </label>

                                <input type="text"
                                    id="transfer_ticket_type_text"
                                    class="form-input bg-slate-100 dark:bg-white/[0.05]"
                                    readonly>

                                <input type="hidden"
                                    id="transfer_ticket_type"
                                    name="ticket_type">

                            </div>

                        </div>

                    </div>

                    {{-- Transfer Note --}}
                    <div>

                        <label class="form-label">
                            Transfer Note
                        </label>

                        <textarea id="transfer_note"
                            rows="5"
                            class="form-textarea w-full"
                            placeholder="Write transfer note or reason..."></textarea>

                    </div>

                </div>

                {{-- Footer --}}
                <div
                    class="sticky bottom-0 z-20 flex flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-4 py-4 backdrop-blur-xl dark:border-white/[0.06] dark:bg-[#0f172a]/95 sm:flex-row sm:items-center sm:justify-end sm:px-6 sm:py-5">

                    <button type="button"
                        onclick="closeModal('#transferTicketModal')"
                        class="btn-secondary w-full min-w-[110px] sm:w-auto">

                        Cancel

                    </button>

                    <button type="submit"
                        id="btnSubmitTransferTicket"
                        class="btn-primary w-full min-w-[170px] sm:w-auto">

                        Submit Transfer

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
