{{-- WARNING MODAL: set / clear warning, allowed documents (checklist), history --}}
@php
    $tile = 'group relative flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 transition hover:border-blue-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60 dark:border-white/10 dark:bg-white/[0.03] dark:has-[:checked]:bg-blue-500/10';
    $box  = 'flex h-6 w-6 shrink-0 items-center justify-center rounded-md border-2 border-gray-300 text-transparent transition peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white dark:border-white/20';
    $area = 'w-full px-3 py-2.5';
@endphp
<div id="warningModal" class="tsModalBackdrop itm sf-field fixed inset-0 z-50 hidden items-center justify-center p-4 opacity-0 transition-opacity duration-200">
    <div class="ts-modal-panel itm-dialog relative flex max-h-[92vh] w-full max-w-2xl scale-95 flex-col overflow-hidden bg-white opacity-0 transition-all duration-200 dark:bg-[#0f172a]">

        {{-- header --}}
        <div class="itm-head">
            <div class="min-w-0">
                <h2 id="warnTitle" class="truncate">Tenant</h2>
                <p id="warnSub" class="truncate">Warning &amp; allowed documents</p>
            </div>
            <button type="button" class="tsModalClose itm-close inline-flex items-center justify-center" data-modal="warningModal" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>

        <div class="itm-body flex-1 space-y-4 overflow-y-auto p-5">
            <input type="hidden" id="warn_tenant_id">

            {{-- WARNING --}}
            <section class="itm-card border bg-white p-4 dark:bg-white/[0.02]">
                <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-amber-500"></span>Warning</p>

                <div id="warnActive" class="hidden space-y-4">
                    <div class="flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-500/30 dark:bg-red-500/10">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/20"><i class="fas fa-ban"></i></span>
                        <div class="min-w-0 text-sm">
                            <p class="font-semibold text-red-700 dark:text-red-300">Under warning &mdash; new SIK / SIMKB are blocked</p>
                            <p id="warnActiveReason" class="mt-1 whitespace-pre-line text-red-700/90 dark:text-red-200"></p>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block">Reason to clear <span class="text-red-500">*</span></label>
                        <textarea id="warnClearReason" rows="2" maxlength="500" class="{{ $area }}" placeholder="Why is the warning lifted?"></textarea>
                        <div class="mt-3 flex justify-end">
                            <button type="button" id="warnClearBtn" class="inline-flex items-center gap-2 h-10 rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500"><i class="fas fa-check"></i> Clear Warning</button>
                        </div>
                    </div>
                </div>

                <div id="warnNone" class="hidden space-y-3">
                    <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                        <i class="fas fa-check-circle"></i> No active warning
                    </div>
                    <div>
                        <label class="mb-1.5 block">Reason <span class="text-red-500">*</span></label>
                        <textarea id="warnReason" rows="3" maxlength="500" class="{{ $area }}" placeholder="Why is this tenant warned?"></textarea>
                        <div class="mt-3 flex justify-end">
                            <button type="button" id="warnSetBtn" class="inline-flex items-center gap-2 h-10 rounded-lg bg-red-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500"><i class="fas fa-exclamation-triangle"></i> Set Warning</button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ALLOWED DOCUMENTS (checklist) --}}
            <section class="itm-card border bg-white p-4 dark:bg-white/[0.02]">
                <p class="mb-1 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-blue-500"></span>Allowed Documents</p>
                <p class="mb-3 text-xs text-gray-400">Tick the documents this tenant is allowed to create.</p>
                <div class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <label class="{{ $tile }}">
                        <input type="checkbox" id="doc_sik" class="peer sr-only">
                        <span class="{{ $box }}"><i class="fas fa-check text-xs"></i></span>
                        <span class="block text-sm font-semibold text-gray-800 dark:text-gray-100">SIK</span>
                    </label>
                    <label class="{{ $tile }}">
                        <input type="checkbox" id="doc_sia" class="peer sr-only">
                        <span class="{{ $box }}"><i class="fas fa-check text-xs"></i></span>
                        <span class="block text-sm font-semibold text-gray-800 dark:text-gray-100">SIA</span>
                    </label>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <label class="{{ $tile }}">
                        <input type="checkbox" id="doc_simkb_in" class="peer sr-only">
                        <span class="{{ $box }}"><i class="fas fa-check text-xs"></i></span>
                        <span class="block text-sm font-semibold text-gray-800 dark:text-gray-100">SIMKB Masuk</span>
                    </label>
                    <label class="{{ $tile }}">
                        <input type="checkbox" id="doc_simkb_out" class="peer sr-only">
                        <span class="{{ $box }}"><i class="fas fa-check text-xs"></i></span>
                        <span class="block text-sm font-semibold text-gray-800 dark:text-gray-100">SIMKB Keluar</span>
                    </label>
                    <label class="{{ $tile }}">
                        <input type="checkbox" id="doc_simkb_inout" class="peer sr-only">
                        <span class="{{ $box }}"><i class="fas fa-check text-xs"></i></span>
                        <span class="block text-sm font-semibold text-gray-800 dark:text-gray-100">SIMKB Masuk &amp; Keluar</span>
                    </label>
                </div>
                <p id="docReadOnly" class="mt-3 hidden text-xs text-gray-400"><i class="fas fa-lock mr-1"></i>Only Tenant Relation can change the allowed documents.</p>
                <div class="mt-3 flex justify-end">
                    <button type="button" id="docSaveBtn" class="inline-flex items-center gap-2 h-10 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500"><i class="fas fa-save"></i> Save Documents</button>
                </div>
            </section>

            {{-- HISTORY --}}
            <section class="itm-card border bg-white p-4 dark:bg-white/[0.02]">
                <p class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><span class="h-4 w-1 rounded-full bg-gray-400"></span>History</p>
                <div id="warnHistory" class="space-y-2 text-sm"></div>
            </section>
        </div>
    </div>
</div>
