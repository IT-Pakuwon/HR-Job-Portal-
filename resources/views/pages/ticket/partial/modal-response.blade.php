<div id="responseTicketModal" class="ticket-modal fixed inset-0 z-[9999] hidden p-4">

    {{-- Backdrop --}}
    <div
        class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
    </div>

    {{-- Wrapper --}}
    <div class="modal-panel relative z-10 mx-auto flex min-h-screen w-full items-center justify-center">

        {{-- Modal --}}
        <div
            class="modal-scroll flex max-h-[95vh] w-full max-w-[95vw] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-white/[0.06] dark:bg-[#0f172a] sm:max-w-2xl">

            {{-- Header --}}
            <div
                class="sticky top-0 z-20 flex items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-4 py-4     dark:border-white/[0.06] dark:bg-[#0f172a]/95 sm:px-6 sm:py-5">

                <div class="min-w-0">

                    <h2 class="truncate text-lg font-semibold tracking-tight text-slate-800 dark:text-white sm:text-xl">
                        Response Ticket
                    </h2>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                        Assign PIC and define SLA response.
                    </p>

                </div>

                <button type="button"
                    onclick="
                        closeModal(
                            '#responseTicketModal'
                        )
                    "
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-500 transition-all duration-200 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.06] dark:hover:text-white">

                    <i class="fa-solid fa-xmark text-base"></i>

                </button>

            </div>

            {{-- Form --}}
            <form id="responseTicketForm" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col">

                @csrf

                <input type="hidden" id="response_ticket_eid" name="eid">

                {{-- Body --}}
                <div class="flex-1 space-y-5 overflow-y-auto px-4 py-4 sm:space-y-6 sm:px-6 sm:py-6">

                    {{-- Ticket Info --}}
                    <div class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2">

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-white/[0.06] dark:bg-white/[0.03] sm:px-4">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500 sm:text-xs">
                                Category
                            </div>

                            <div id="response_ticket_category"
                                class="mt-1 break-words text-xs font-semibold text-slate-800 dark:text-white sm:text-sm">
                                -
                            </div>

                        </div>

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-white/[0.06] dark:bg-white/[0.03] sm:px-4">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500 sm:text-xs">
                                Sub Category
                            </div>

                            <div id="response_ticket_subcategory"
                                class="mt-1 break-words text-xs font-semibold text-slate-800 dark:text-white sm:text-sm">
                                -
                            </div>

                        </div>

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-white/[0.06] dark:bg-white/[0.03] sm:px-4">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500 sm:text-xs">
                                Location
                            </div>

                            <div id="response_ticket_location"
                                class="mt-1 break-words text-xs font-semibold text-slate-800 dark:text-white sm:text-sm">
                                -
                            </div>

                        </div>

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 dark:border-white/[0.06] dark:bg-white/[0.03] sm:px-4">

                            <div
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500 sm:text-xs">
                                Sub Location
                            </div>

                            <div id="response_ticket_sublocation"
                                class="mt-1 break-words text-xs font-semibold text-slate-800 dark:text-white sm:text-sm">
                                -
                            </div>

                        </div>

                    </div>

                    {{-- Form Fields --}}
                    <div class="grid grid-cols-1 gap-4 sm:gap-5 md:grid-cols-2">

                        <div>

                            <div class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                PIC
                            </div>

                            <select id="response_pic" name="pic_ticket" class="form-select" required>

                                <option value="">
                                    Select PIC
                                </option>

                            </select>

                        </div>

                        <div>

                            <div class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                Priority
                            </div>

                            <select id="response_priority" name="ticket_priority" class="form-select" required>

                                <option value="">
                                    Select Priority
                                </option>

                            </select>

                        </div>

                    </div>

                    {{-- Description --}}
                    <div>

                        <div class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                            Response Description
                        </div>

                        <textarea id="response_descr" name="response_descr" rows="5" class="form-textarea w-full"
                            placeholder="Write response description..."></textarea>

                    </div>

                    {{-- Schedule --}}
                    <div class="border-t border-slate-200 pt-5 dark:border-white/[0.06]">

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-white/[0.06] dark:bg-white/[0.03] sm:p-5">

                            {{-- Header --}}
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

                                <div class="min-w-0">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900">

                                            <i class="fa-regular fa-calendar-days text-sm"></i>

                                        </div>

                                        <div class="min-w-0">

                                            <h3 class="truncate text-sm font-semibold text-slate-800 dark:text-white">
                                                Working Schedule
                                            </h3>

                                            <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                                Optional. Leave empty to auto-generate from SLA.
                                            </p>

                                        </div>

                                    </div>

                                </div>

                                {{-- Toggle --}}
                                <label
                                    class="inline-flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition-all duration-200 hover:border-slate-300 hover:bg-slate-100 dark:border-white/[0.06] dark:bg-white/[0.04] dark:text-slate-300 dark:hover:border-white/[0.12] dark:hover:bg-white/[0.06]">

                                    <input type="checkbox" id="response_use_schedule"
                                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-400">

                                    <span>
                                        Define Schedule
                                    </span>

                                </label>

                            </div>

                            {{-- Schedule Fields --}}
                            <div id="response_schedule_container" class="mt-5 hidden">

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                    {{-- Start --}}
                                    <div
                                        class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/[0.06] dark:bg-[#0b1220]">

                                        <label
                                            class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">

                                            <i class="fa-regular fa-clock text-xs"></i>

                                            <span>
                                                Working Start Date
                                            </span>

                                        </label>

                                        <input type="datetime-local" id="response_working_start_date"
                                            name="working_start_date" class="form-input w-full">

                                    </div>

                                    {{-- End --}}
                                    <div
                                        class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/[0.06] dark:bg-[#0b1220]">

                                        <label
                                            class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">

                                            <i class="fa-solid fa-flag-checkered text-xs"></i>

                                            <span>
                                                Working End Date
                                            </span>

                                        </label>

                                        <input type="datetime-local" id="response_working_end_date"
                                            name="working_end_date" class="form-input w-full">

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- Footer --}}
                <div
                    class="sticky bottom-0 z-20 flex flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-4 py-4     dark:border-white/[0.06] dark:bg-[#0f172a]/95 sm:flex-row sm:items-center sm:justify-end sm:px-6 sm:py-5">

                    <button type="button"
                        onclick="
                            closeModal(
                                '#responseTicketModal'
                            )
                        "
                          class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5">
                        Cancel
                    </button>

                    <button type="submit" id="btnSubmitResponseTicket"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition-all duration-200 hover:scale-[1.01] hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                        Response
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
