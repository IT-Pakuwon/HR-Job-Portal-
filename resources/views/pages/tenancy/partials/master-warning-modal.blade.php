{{-- WARNING MODAL: set / clear warning, allowed documents (checklist), history --}}
@php
    $tile = 'group relative flex cursor-pointer flex-col gap-3 rounded-xl border border-gray-200 bg-white p-3.5 transition-all duration-150 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/60 has-[:checked]:ring-1 has-[:checked]:ring-blue-500 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-blue-400 has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60 has-[:disabled]:hover:translate-y-0 has-[:disabled]:hover:shadow-none dark:border-white/10 dark:bg-white/[0.03] dark:has-[:checked]:border-blue-500 dark:has-[:checked]:bg-blue-500/10';
    $icon = 'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-400 transition group-has-[:checked]:bg-blue-600 group-has-[:checked]:text-white dark:bg-white/[0.06]';
    $tick = 'flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-gray-300 text-[10px] text-transparent transition group-has-[:checked]:border-blue-600 group-has-[:checked]:bg-blue-600 group-has-[:checked]:text-white dark:border-white/20';

    $docGroups = [
        ['title' => 'Permits', 'grid' => 'grid-cols-2', 'items' => [
            ['id' => 'doc_sik', 'icon' => 'fa-hard-hat', 'name' => 'SIK', 'desc' => 'Surat Izin Kerja'],
            ['id' => 'doc_sia', 'icon' => 'fa-calendar-check', 'name' => 'SIA', 'desc' => 'Surat Izin Acara'],
        ]],
        ['title' => 'SIMKB — goods movement', 'grid' => 'grid-cols-2 sm:grid-cols-3', 'items' => [
            ['id' => 'doc_simkb_in', 'icon' => 'fa-sign-in-alt', 'name' => 'Masuk', 'desc' => 'Goods coming in'],
            ['id' => 'doc_simkb_out', 'icon' => 'fa-sign-out-alt', 'name' => 'Keluar', 'desc' => 'Goods going out'],
            ['id' => 'doc_simkb_inout', 'icon' => 'fa-exchange-alt', 'name' => 'Masuk & Keluar', 'desc' => 'In and out'],
        ]],
    ];

    $card    = 'itm-card overflow-hidden border bg-white dark:bg-white/[0.02]';
    $heading = 'flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400';
    $foot    = 'flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/70 px-5 py-3 dark:border-white/[0.06] dark:bg-white/[0.02]';
    $btn     = 'ml-auto inline-flex h-10 items-center gap-2 rounded-lg px-5 text-sm font-semibold text-white shadow-sm transition hover:shadow-md active:scale-[0.98]';
    $area    = 'w-full resize-none px-3 py-2.5';
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
            <section class="{{ $card }}">
                {{-- under warning --}}
                <div id="warnActive" class="hidden">
                    <div class="space-y-4 p-5">
                        <p class="{{ $heading }}"><span class="h-4 w-1 rounded-full bg-red-500"></span>Warning</p>
                        <div class="flex gap-3 rounded-xl border border-red-200 bg-gradient-to-r from-red-50 to-white p-4 dark:border-red-500/30 dark:from-red-500/15 dark:to-transparent">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600 text-white ring-4 ring-red-100 dark:ring-red-500/20"><i class="fas fa-ban"></i></span>
                            <div class="min-w-0 text-sm">
                                <p class="font-semibold text-red-700 dark:text-red-300">Under warning &mdash; new documents are blocked</p>
                                <p id="warnActiveReason" class="mt-1 whitespace-pre-line break-words text-gray-700 dark:text-red-100"></p>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block">Reason to clear <span class="text-red-500">*</span></label>
                            <textarea id="warnClearReason" rows="2" maxlength="500" class="{{ $area }}" placeholder="Why is the warning lifted?"></textarea>
                        </div>
                    </div>
                    <div class="{{ $foot }}">
                        <p class="text-xs text-gray-400">The reason is saved in the history.</p>
                        <button type="button" id="warnClearBtn" class="{{ $btn }} bg-emerald-600 hover:bg-emerald-500"><i class="fas fa-check"></i> Clear Warning</button>
                    </div>
                </div>

                {{-- no warning --}}
                <div id="warnNone" class="hidden">
                    <div class="space-y-4 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="{{ $heading }}"><span class="h-4 w-1 rounded-full bg-amber-500"></span>Warning</p>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30">
                                <i class="fas fa-check-circle"></i> No active warning
                            </span>
                        </div>
                        <div>
                            <label class="mb-1.5 block">Reason <span class="text-red-500">*</span></label>
                            <textarea id="warnReason" rows="3" maxlength="500" class="{{ $area }}" placeholder="Why is this tenant warned?"></textarea>
                        </div>
                    </div>
                    <div class="{{ $foot }}">
                        <p class="text-xs text-gray-400"><i class="fas fa-info-circle mr-1"></i>A warning blocks all new documents until cleared.</p>
                        <button type="button" id="warnSetBtn" class="{{ $btn }} bg-red-600 hover:bg-red-500"><i class="fas fa-exclamation-triangle"></i> Set Warning</button>
                    </div>
                </div>
            </section>

            {{-- ALLOWED DOCUMENTS (checklist) --}}
            <section class="{{ $card }}">
                <div class="space-y-4 p-5">
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <p class="{{ $heading }}"><span class="h-4 w-1 rounded-full bg-blue-500"></span>Allowed Documents</p>
                            <span id="docCount" class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30"></span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Tick the documents this tenant is allowed to create.</p>
                    </div>

                    @foreach ($docGroups as $group)
                        <div>
                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ $group['title'] }}</p>
                            <div class="grid {{ $group['grid'] }} gap-3">
                                @foreach ($group['items'] as $doc)
                                    <label class="{{ $tile }}">
                                        <input type="checkbox" id="{{ $doc['id'] }}" class="doc-check sr-only">
                                        <span class="flex items-start justify-between gap-2">
                                            <span class="{{ $icon }}"><i class="fas {{ $doc['icon'] }} text-sm"></i></span>
                                            <span class="{{ $tick }}"><i class="fas fa-check"></i></span>
                                        </span>
                                        <span class="block min-w-0">
                                            <span class="block text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $doc['name'] }}</span>
                                            <span class="block text-xs text-gray-400">{{ $doc['desc'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="{{ $foot }}">
                    <p id="docReadOnly" class="hidden text-xs text-gray-400"><i class="fas fa-lock mr-1"></i>Only Tenant Relation can change the allowed documents.</p>
                    <button type="button" id="docSaveBtn" class="{{ $btn }} bg-blue-600 hover:bg-blue-500"><i class="fas fa-save"></i> Save Documents</button>
                </div>
            </section>

            {{-- HISTORY --}}
            <section class="{{ $card }} p-5">
                <p class="{{ $heading }} mb-4"><span class="h-4 w-1 rounded-full bg-gray-400"></span>History</p>
                <div id="warnHistory" class="text-sm"></div>
            </section>
        </div>
    </div>
</div>
