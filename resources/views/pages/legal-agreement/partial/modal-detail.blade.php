<div id="detailAgreementModal" class="agr-modal fixed inset-0 z-[9998] hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 transition-opacity duration-200"></div>

    <div class="modal-panel modal-scroll relative max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-2xl bg-white p-6 opacity-0 translate-y-4 scale-[0.98] shadow-2xl transition-all duration-200 dark:bg-slate-900">

        <div class="mb-6 flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Agreement</p>
                <h3 id="detail_agreement_id" class="text-xl font-bold text-slate-800 dark:text-white">-</h3>
            </div>
            <div class="flex items-center gap-2">
                <a id="detail_print_link" href="#" target="_blank" class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                    <i class="fa-solid fa-print"></i> Print
                </a>
                <button type="button" class="btn-close-modal text-slate-400 hover:text-slate-700 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        <input type="hidden" id="detail_agreement_eid" />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Info --}}
            <div class="lg:col-span-2 space-y-6">

                <div class="rounded-xl border border-slate-200 p-4 dark:border-white/[0.06]">
                    <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                        <div><p class="text-xs text-slate-400">Company</p><p id="detail_cpny_id" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">Agreement Date</p><p id="detail_agreement_date" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">Step</p><p id="detail_step" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">Follow-up Cycle</p><p id="detail_cycle" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">Business Name</p><p id="detail_business_name" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">Trade Name</p><p id="detail_trade_name" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">Tenant No</p><p id="detail_tenant_no" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">Floor / Unit</p><p id="detail_floor_unit" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">PIC Legal</p><p id="detail_pic_legal" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">PIC Leasing</p><p id="detail_pic_leasing" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">PIC Penyewa</p><p id="detail_pic_penyewa" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">No. PSM/Addendum</p><p id="detail_no_psm" class="font-semibold">-</p></div>
                        <div><p class="text-xs text-slate-400">Requested By</p><p id="detail_created_user" class="font-semibold">-</p></div>
                    </div>
                    <div class="mt-4">
                        <p class="text-xs text-slate-400">Business Address</p>
                        <p id="detail_business_address" class="text-sm font-medium">-</p>
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="rounded-xl border border-slate-200 p-4 dark:border-white/[0.06]">
                    <p class="mb-3 text-sm font-bold text-slate-700 dark:text-slate-200">Attachments</p>
                    <div id="detail_attachment_list" class="space-y-2 text-sm text-slate-400">No attachments.</div>
                </div>

                {{-- Tracking --}}
                <div class="rounded-xl border border-slate-200 p-4 dark:border-white/[0.06]">
                    <p class="mb-3 text-sm font-bold text-slate-700 dark:text-slate-200">Tracking</p>
                    <div id="detail_tracking_list" class="space-y-4 text-sm"></div>
                </div>

                {{-- Comments --}}
                <div class="rounded-xl border border-slate-200 p-4 dark:border-white/[0.06]">
                    <p class="mb-3 text-sm font-bold text-slate-700 dark:text-slate-200">Discussion</p>
                    <div id="detail_comment_list" class="mb-4 space-y-3 text-sm"></div>
                    <form id="commentForm" class="flex gap-2">
                        <input type="text" id="comment_message" name="message" class="agr-input" placeholder="Write a comment..." />
                        <button type="submit" class="shrink-0 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">Send</button>
                    </form>
                </div>

            </div>

            {{-- Actions --}}
            <div class="space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Actions</p>
                <div id="detail_action_buttons" class="flex flex-col gap-2"></div>
            </div>

        </div>

    </div>
</div>
