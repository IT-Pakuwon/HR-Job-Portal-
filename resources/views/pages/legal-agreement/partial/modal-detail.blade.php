<div id="detailAgreementModal" class="agr-modal fixed inset-0 z-[9998] hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70"></div>

    <div class="modal-panel relative flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white opacity-0 translate-y-4 scale-[0.98] shadow-2xl transition-all duration-200 dark:bg-slate-900">

        {{-- Header --}}
        <div class="sticky top-0 z-20 flex flex-col gap-4 border-b border-slate-200 bg-white px-6 py-4 xl:flex-row xl:items-start xl:justify-between dark:border-white/[0.06] dark:bg-slate-900">

            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 id="detail_agreement_id" class="text-xl font-bold text-slate-800 dark:text-white">-</h2>
                    <div id="detail_status_badge"></div>
                </div>
                <p id="detail_subtitle" class="mt-2 text-sm text-slate-500 dark:text-slate-400">-</p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">

                <div class="relative">
                    <button type="button" id="agreementActionBtn" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:-translate-y-[1px] hover:border-slate-300 hover:bg-slate-50 hover:shadow-lg dark:border-white/[0.06] dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-bolt text-[15px]"></i>
                        <span>Actions</span>
                        <i class="fa-solid fa-chevron-down text-[12px]"></i>
                    </button>

                    <div id="agreementActionDropdown" class="absolute right-0 top-[calc(100%+10px)] z-50 hidden w-[260px] overflow-hidden rounded-lg border border-slate-200/80 bg-white/95 shadow-xl dark:border-white/[0.06] dark:bg-slate-800/95">
                        <div id="detail_action_buttons" class="max-h-[320px] overflow-y-auto p-2"></div>
                    </div>
                </div>

                <a id="detail_print_link" href="#" target="_blank" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:-translate-y-[1px] hover:border-slate-300 hover:bg-slate-50 hover:shadow-lg dark:border-white/[0.06] dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    <i class="fa-solid fa-print text-[15px]"></i>
                    <span>Print</span>
                </a>

                <button type="button" class="btn-close-modal inline-flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm transition-all duration-200 hover:-translate-y-[1px] hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 hover:shadow-lg dark:border-white/[0.06] dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-rose-500/10">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>

            </div>

        </div>

        <input type="hidden" id="detail_agreement_eid" />

        {{-- Body --}}
        <div class="grid flex-1 grid-cols-1 overflow-hidden xl:grid-cols-12">

            {{-- Left Panel — Information --}}
            <div class="modal-scroll min-h-0 overflow-y-auto border-b border-slate-200 p-6 xl:col-span-7 xl:border-b-0 xl:border-r dark:border-white/[0.06]">

                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Agreement Information</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div><label class="text-xs text-slate-400">Company</label><p id="detail_cpny_id" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">Agreement Date</label><p id="detail_agreement_date" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">Follow-up Cycle</label><p id="detail_cycle" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">Business Name</label><p id="detail_business_name" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">Trade Name</label><p id="detail_trade_name" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">Tenant No</label><p id="detail_tenant_no" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">Floor / Unit</label><p id="detail_floor_unit" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">PIC Legal</label><p id="detail_pic_legal" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">PIC Leasing</label><p id="detail_pic_leasing" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">PIC Penyewa</label><p id="detail_pic_penyewa" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">No. PSM/Addendum</label><p id="detail_no_psm" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                    <div><label class="text-xs text-slate-400">Requested By</label><p id="detail_created_user" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p></div>
                </div>

                <div class="mt-6">
                    <label class="text-xs text-slate-400">Business Address</label>
                    <p id="detail_business_address" class="mt-1 text-sm font-medium text-slate-800 dark:text-white">-</p>
                </div>

            </div>

            {{-- Right Panel — Tracking / Discussion / Attachments --}}
            <div class="flex min-h-0 flex-col xl:col-span-5">

                {{-- Tabs --}}
                <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-2 dark:border-white/[0.06] dark:bg-slate-900/60">
                    <div class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white p-1.5 shadow-sm dark:border-white/[0.08] dark:bg-slate-800">
                        <button type="button" class="agr-detail-tab active inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-sm font-semibold transition-all duration-200" data-tab="tracking">
                            <i class="fa-solid fa-clock-rotate-left text-[12px]"></i> Tracking
                        </button>
                        <button type="button" class="agr-detail-tab inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-sm font-semibold transition-all duration-200" data-tab="discussion">
                            <i class="fa-solid fa-comments text-[12px]"></i> Discussion
                        </button>
                        <button type="button" class="agr-detail-tab inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-sm font-semibold transition-all duration-200" data-tab="attachments">
                            <i class="fa-solid fa-paperclip text-[12px]"></i> Attachments
                        </button>
                    </div>
                </div>

                {{-- Tracking --}}
                <div id="agr_tracking_panel" class="agr-tab-content modal-scroll flex-1 overflow-y-auto p-6">
                    <h3 class="mb-4 text-sm font-semibold text-slate-800 dark:text-slate-100">Tracking Timeline</h3>
                    <div id="detail_tracking_list" class="space-y-3"></div>
                </div>

                {{-- Discussion --}}
                <div id="agr_discussion_panel" class="agr-tab-content hidden flex-1 overflow-y-auto">
                    <div class="flex h-full flex-col">
                        <div id="detail_comment_list" class="modal-scroll flex-1 space-y-4 overflow-y-auto p-6"></div>
                        <div class="border-t border-slate-200 p-4 dark:border-white/[0.06]">
                            <form id="commentForm" class="flex gap-2">
                                <input type="text" id="comment_message" name="message" class="agr-input" placeholder="Write a comment..." />
                                <button type="submit" class="shrink-0 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">Send</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Attachments --}}
                <div id="agr_attachments_panel" class="agr-tab-content hidden modal-scroll flex-1 overflow-y-auto p-6">
                    <div id="detail_attachment_list" class="space-y-2 text-sm text-slate-400">No attachments.</div>
                </div>

            </div>

        </div>

    </div>
</div>
