{{-- Generic workflow-action modal. JS toggles which steps are shown based on data-action. --}}
<div id="actionAgreementModal" class="agr-modal fixed inset-0 z-[9999] hidden items-center justify-center p-4" data-form-modal="true">
    <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 transition-opacity duration-200"></div>

    <div class="modal-panel modal-scroll relative max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 opacity-0 translate-y-4 scale-[0.98] shadow-2xl transition-all duration-200 dark:bg-slate-900">

        <div class="mb-6 flex items-start justify-between gap-4 border-b border-slate-100 pb-5 dark:border-white/[0.06]">
            <div class="flex items-center gap-3">
                <span id="action_modal_icon" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-lg text-slate-600 dark:bg-white/[0.06] dark:text-slate-300">
                    <i class="fa-solid fa-bolt"></i>
                </span>
                <div>
                    <h3 id="action_modal_title" class="text-lg font-bold text-slate-800 dark:text-white">Action</h3>
                    <p id="action_modal_subtitle" class="text-xs text-slate-400">-</p>
                </div>
            </div>
            <button type="button" class="btn-close-form-modal flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.08] dark:hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Step indicator — built by JS since which steps apply depends on the action --}}
        <div id="action_steps_indicator_wrap" class="hidden">
            <div id="action_steps_indicator" class="agr-steps mb-7"></div>
        </div>

        <form id="actionAgreementForm">
            <input type="hidden" id="action_eid" />
            <input type="hidden" id="action_type" />

            <div id="action_pic_step" class="agr-step hidden" data-step-key="pic">
                <div class="agr-section rounded-xl border border-slate-200 p-5 dark:border-white/[0.06]">
                    <div class="agr-section-head justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="agr-section-badge"><i class="fa-solid fa-users text-[11px]"></i></span>
                            <p class="agr-section-title">PIC Assignment</p>
                        </div>
                        <button type="button" class="agr-edit-toggle text-[11px] font-semibold text-blue-600 dark:text-blue-400" data-target="#action_pic_fields">
                            <i class="fa-solid fa-pen text-[10px]"></i> Edit
                        </button>
                    </div>
                    <div id="action_pic_fields" class="mt-4 space-y-5">
                        <div>
                            <label class="agr-label">PIC Legal</label>
                            <select id="action_pic_legal" name="pic_legal[]" class="agr-input agr-select2" multiple></select>
                        </div>
                        <div>
                            <label class="agr-label">PIC Leasing</label>
                            <select id="action_pic_leasing" name="pic_leasing[]" class="agr-input agr-select2" multiple></select>
                        </div>
                    </div>
                </div>
            </div>

            <div id="action_psm_step" class="agr-step hidden" data-step-key="psm">
                <div class="agr-section rounded-xl border border-slate-200 p-5 dark:border-white/[0.06]">
                    <div class="agr-section-head">
                        <span class="agr-section-badge"><i class="fa-solid fa-file-signature text-[11px]"></i></span>
                        <p class="agr-section-title">Document Info</p>
                    </div>
                    <p id="action_psm_hint" class="hidden mb-4 mt-3 text-xs text-slate-400"></p>
                    <div class="mt-4 space-y-5">
                        <div>
                            <div class="mb-2.5 flex items-center justify-between">
                                <label class="agr-label mb-0!">No. PSM / Addendum</label>
                                <button type="button" class="agr-edit-toggle text-[11px] font-semibold text-blue-600 dark:text-blue-400" data-target="#action_no_psm_or_addendum">
                                    <i class="fa-solid fa-pen text-[10px]"></i> Edit
                                </button>
                            </div>
                            <input type="text" id="action_no_psm_or_addendum" name="no_psm_or_addendum" class="agr-input agr-locked" readonly />
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="agr-label">PSM / Addendum Date</label>
                                <input type="date" id="action_psm_date" name="psm_or_addendum_date" class="agr-input" max="{{ now()->addDays(3)->format('Y-m-d') }}" />
                            </div>
                            <div>
                                <label id="action_psm_delivery_label" class="agr-label">Delivery Date</label>
                                <input type="date" id="action_psm_delivery_input" name="psm_or_addendum_delivery_date" class="agr-input" max="{{ now()->addDays(3)->format('Y-m-d') }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="action_notes_step" class="agr-step hidden" data-step-key="notes">
                <div class="agr-section rounded-xl border border-slate-200 p-5 dark:border-white/[0.06]">
                    <div class="agr-section-head">
                        <span class="agr-section-badge"><i class="fa-solid fa-message text-[11px]"></i></span>
                        <p id="action_descr_label" class="agr-section-title">Notes</p>
                    </div>
                    <textarea id="action_response_descr" name="response_descr" rows="4" class="agr-textarea mt-4" placeholder="Notes..."></textarea>
                </div>
            </div>

            <div id="action_attachment_step" class="agr-step hidden" data-step-key="attachments">
                <div class="agr-section rounded-xl border border-slate-200 p-5 dark:border-white/[0.06]">
                    <div class="agr-section-head">
                        <span class="agr-section-badge"><i class="fa-solid fa-paperclip text-[11px]"></i></span>
                        <p id="action_attachment_label" class="agr-section-title">Attachments</p>
                    </div>
                    <div class="mt-4">
                        <div class="agr-file">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <input type="file" id="action_attachments" multiple />
                        </div>
                        <div id="action_attachment_list" class="mt-3 space-y-2"></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5 dark:border-white/[0.06]">
                <button type="button" class="btn-close-form-modal rounded-lg border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                    Cancel
                </button>
                <div class="flex items-center gap-3">
                    <button type="button" id="btnActionStepBack" class="hidden flex items-center gap-2 rounded-lg border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <button type="button" id="btnActionStepNext" class="hidden flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        Next <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <button type="submit" id="btnSubmitAction" class="hidden flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        Submit
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
