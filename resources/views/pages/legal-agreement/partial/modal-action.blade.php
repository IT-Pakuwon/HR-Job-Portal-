{{-- Generic workflow-action modal. JS toggles which fields are visible based on data-action. --}}
<div id="actionAgreementModal" class="agr-modal fixed inset-0 z-[9999] hidden items-center justify-center p-4" data-form-modal="true">
    <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 transition-opacity duration-200"></div>

    <div class="modal-panel modal-scroll relative max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 opacity-0 translate-y-4 scale-[0.98] shadow-2xl transition-all duration-200 dark:bg-slate-900">

        <div class="mb-6 flex items-center justify-between">
            <h3 id="action_modal_title" class="text-lg font-bold text-slate-800 dark:text-white">Action</h3>
            <button type="button" class="btn-close-form-modal text-slate-400 hover:text-slate-700 dark:hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="actionAgreementForm">
            <input type="hidden" id="action_eid" />
            <input type="hidden" id="action_type" />

            <div class="space-y-5">

                <div id="action_pic_fields" class="hidden space-y-5">
                    <div>
                        <label class="agr-label">PIC Legal</label>
                        <select id="action_pic_legal" name="pic_legal[]" class="agr-input agr-select2" multiple></select>
                    </div>
                    <div>
                        <label class="agr-label">PIC Leasing</label>
                        <select id="action_pic_leasing" name="pic_leasing[]" class="agr-input agr-select2" multiple></select>
                    </div>
                </div>

                <div id="action_psm_fields" class="hidden space-y-5">
                    <div>
                        <label class="agr-label">No. PSM / Addendum</label>
                        <input type="text" name="no_psm_or_addendum" class="agr-input" />
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="agr-label">PSM / Addendum Date</label>
                            <input type="date" name="psm_or_addendum_date" class="agr-input" />
                        </div>
                        <div>
                            <label class="agr-label">Delivery Date</label>
                            <input type="date" name="psm_or_addendum_delivery_date" class="agr-input" />
                        </div>
                    </div>
                </div>

                <div>
                    <label id="action_descr_label" class="agr-label">Notes</label>
                    <textarea id="action_response_descr" name="response_descr" rows="4" class="agr-textarea" placeholder="Notes..."></textarea>
                </div>

                <div id="action_attachment_fields" class="hidden">
                    <label class="agr-label">Attachments</label>
                    <input type="file" id="action_attachments" multiple class="agr-input pt-3" />
                    <div id="action_attachment_list" class="mt-3 space-y-2"></div>
                </div>

            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="btn-close-form-modal rounded-lg border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 dark:border-white/[0.08] dark:text-slate-300">
                    Cancel
                </button>
                <button type="submit" id="btnSubmitAction" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Submit
                </button>
            </div>
        </form>

    </div>
</div>
