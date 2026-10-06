{{-- View modal body for a saved PSM/OLA "Pembuatan" agreement. Fetched over
     ajax into #viewAgrModalBody (a flex column) on the PSM/OLA page.
     Same layout as the ticket / Agreement FU detail: header, left info panel,
     right tabbed panel. Edit lives in the header Actions dropdown and opens the
     create modal (psm-ola-create-form) prefilled. --}}
@php
    $dash = fn ($v) => filled($v) ? e($v) : '<span class="text-slate-400">-</span>';
    $dateOnly = fn ($v) => $v ? substr((string) $v, 0, 10) : null;

    $receivedCount = $documents->where('agreementdocument_received', true)->count();
    $totalDocs = $documents->count();
    $pct = $totalDocs ? (int) round($receivedCount / $totalDocs * 100) : 0;
    $missingRequired = $documents->filter(fn ($d) => $d->agreementdocument_required && ! $d->agreementdocument_received)->count();

    $fmtSize = function ($b) {
        $b = (int) $b;
        if ($b >= 1048576) return round($b / 1048576, 1).' MB';
        if ($b >= 1024) return round($b / 1024).' KB';
        return $b.' B';
    };
@endphp

{{-- Header --}}
<div class="flex flex-col gap-4 border-b border-slate-200 bg-white px-6 py-4 xl:flex-row xl:items-start xl:justify-between dark:border-white/[0.06] dark:bg-slate-900">
    <div>
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">{{ $agreement->agreement_id }}</h2>
            @if ($cancelled)
                <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-semibold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">Cancelled</span>
            @elseif ($completed)
                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Completed</span>
            @else
                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-300">Active</span>
            @endif
        </div>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $kind['label'] }} · {{ $agreement->trade_name ?: $agreement->business_name }}</p>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2">
        {{-- Actions: Active = Edit / Complete / Cancel; Completed or Cancelled = Reopen. Creator and PIC Legal only. --}}
        @if ($canManage)
        <div class="relative">
            <button type="button" id="viewAgrActionBtn" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:-translate-y-[1px] hover:border-slate-300 hover:bg-slate-50 hover:shadow-lg dark:border-white/[0.06] dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                <i class="fa-solid fa-bolt text-[15px]"></i>
                <span>Actions</span>
                <i class="fa-solid fa-chevron-down text-[12px]"></i>
            </button>

            <div id="viewAgrActionDropdown" class="absolute right-0 top-[calc(100%+10px)] z-50 hidden w-[220px] overflow-hidden rounded-lg border border-slate-200/80 bg-white/95 shadow-xl dark:border-white/[0.06] dark:bg-slate-800/95">
                <div class="p-2">
                    @if ($canUpdateDocs)
                    <button type="button" class="btn-edit-agr flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-blue-600 hover:bg-slate-100 dark:text-blue-400 dark:hover:bg-white/[0.06]" data-eid="{{ $eid }}">
                        <i class="fa-solid fa-pen-to-square w-4 text-center"></i> Edit Agreement
                    </button>
                    {{-- Creator only: marks the agreement complete (status C). --}}
                    @if ($canUpdateDocs)
                        <button type="button" class="btn-complete-agr flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-emerald-600 hover:bg-slate-100 dark:text-emerald-400 dark:hover:bg-white/[0.06]"
                            data-url="{{ route($kind['r']['complete'], $eid, false) }}" data-missing="{{ $missingRequired }}">
                            <i class="fa-solid fa-flag-checkered w-4 text-center"></i> Complete Agreement
                        </button>
                        <button type="button" class="btn-cancel-agr flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-rose-600 hover:bg-slate-100 dark:text-rose-400 dark:hover:bg-white/[0.06]"
                            data-url="{{ route($kind['r']['cancel'], $eid, false) }}">
                            <i class="fa-solid fa-ban w-4 text-center"></i> Cancel Agreement
                        </button>
                    @endif
                    @else
                        <button type="button" class="btn-reopen-agr flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-amber-600 hover:bg-slate-100 dark:text-amber-400 dark:hover:bg-white/[0.06]"
                            data-url="{{ route($kind['r']['reopen'], $eid, false) }}">
                            <i class="fa-solid fa-lock-open w-4 text-center"></i> Reopen Agreement
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Only shown while full screen (the page script swaps it with the expand button). --}}
        <button type="button" class="btn-view-minimize hidden h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm transition-all duration-200 hover:-translate-y-px hover:border-slate-300 hover:bg-slate-50 hover:shadow-lg dark:border-white/[0.06] dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700" data-eid="{{ $eid }}" title="Minimize" aria-label="Minimize">
            <i class="fa-solid fa-down-left-and-up-right-to-center text-[15px]"></i>
        </button>
        <button type="button" class="btn-view-fullscreen inline-flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm transition-all duration-200 hover:-translate-y-px hover:border-slate-300 hover:bg-slate-50 hover:shadow-lg dark:border-white/[0.06] dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700" data-eid="{{ $eid }}" title="Full screen" aria-label="Full screen">
            <i class="fa-solid fa-up-right-and-down-left-from-center text-[15px]"></i>
        </button>
        <button type="button" class="btn-close-view-agr inline-flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm transition-all duration-200 hover:-translate-y-[1px] hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 hover:shadow-lg dark:border-white/[0.06] dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-rose-500/10">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
</div>

{{-- Body --}}
<div class="grid min-h-0 flex-1 grid-cols-1 overflow-hidden xl:grid-cols-12">

    {{-- Left panel: information --}}
    <div class="modal-scroll min-h-0 space-y-4 overflow-y-auto border-b border-slate-200 bg-slate-50/60 p-6 xl:col-span-5 xl:border-b-0 xl:border-r dark:border-white/[0.06] dark:bg-slate-900/40">

        <div class="agr-section rounded-xl border border-slate-200 bg-white p-5 dark:border-white/[0.06] dark:bg-slate-800/60">
            <div class="agr-section-head">
                <span class="agr-section-badge"><i class="fa-solid fa-file-signature text-[11px]"></i></span>
                <p class="agr-section-title">Agreement</p>
            </div>
            <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                <div><label class="text-xs text-slate-400">Company</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($companyName) !!}</p></div>
                <div><label class="text-xs text-slate-400">Agreement Date</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($dateOnly($agreement->agreement_date)) !!}</p></div>
                <div><label class="text-xs text-slate-400">Type</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{{ $kind['docs'] ? (in_array($agreement->agreement_type, ['PSM', 'OLA'], true) ? $agreement->agreement_type : 'PSM / OLA') : $kind['label'] }}</p></div>
                <div><label class="text-xs text-slate-400">Business ID</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->business_id) !!}</p></div>
                <div><label class="text-xs text-slate-400">Created By</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->created_user) !!}</p></div>
                <div><label class="text-xs text-slate-400">{{ $kind['docs'] ? 'No. PSM / Addendum' : 'No. Addendum' }}</label><p class="mt-1 break-words text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->no_psm_or_addendum) !!}</p></div>
                @if (! $kind['docs'] && $agreement->prev_agreement_id)
                    <div><label class="text-xs text-slate-400">No. PSM / OLA</label><p class="mt-1 break-words text-sm font-medium text-slate-800 dark:text-white">{!! $dash($psmOlaNo) !!}</p></div>
                @endif
            </div>
        </div>

        <div class="agr-section rounded-xl border border-slate-200 bg-white p-5 dark:border-white/[0.06] dark:bg-slate-800/60">
            <div class="agr-section-head">
                <span class="agr-section-badge"><i class="fa-solid fa-building text-[11px]"></i></span>
                <p class="agr-section-title">Property &amp; Tenant</p>
            </div>
            <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div><label class="text-xs text-slate-400">Business Name</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->business_name) !!}</p></div>
                <div><label class="text-xs text-slate-400">Trade Name</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->trade_name) !!}</p></div>
                <div><label class="text-xs text-slate-400">Tenant Number</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->tenant_no) !!}</p></div>
                <div><label class="text-xs text-slate-400">Property Type</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($propertyTypes[strtoupper((string) $agreement->property_cd)] ?? $agreement->property_cd) !!}</p></div>
                <div><label class="text-xs text-slate-400">Floor</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->floor_id) !!}</p></div>
                <div><label class="text-xs text-slate-400">Unit</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->unit_id) !!}</p></div>
            </div>
            <div class="mt-4 border-t border-dashed border-slate-200 pt-4 dark:border-white/[0.06]">
                <label class="text-xs text-slate-400">Business Address</label>
                <p class="mt-1 text-sm font-medium leading-relaxed text-slate-800 dark:text-white">{!! $dash($agreement->business_address) !!}</p>
            </div>
        </div>

        <div class="agr-section rounded-xl border border-slate-200 bg-white p-5 dark:border-white/[0.06] dark:bg-slate-800/60">
            <div class="agr-section-head">
                <span class="agr-section-badge"><i class="fa-solid fa-user-shield text-[11px]"></i></span>
                <p class="agr-section-title">PIC</p>
            </div>
            <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-slate-400">PIC Legal</label>
                    <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $picLegalNames ? e(implode(', ', $picLegalNames)) : '<span class="text-slate-400">-</span>' !!}</p>
                </div>
                <div>
                    <label class="text-xs text-slate-400">PIC Leasing</label>
                    <p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $picLeasingNames ? e(implode(', ', $picLeasingNames)) : '<span class="text-slate-400">-</span>' !!}</p>
                </div>
            </div>
        </div>

        <div class="agr-section rounded-xl border border-slate-200 bg-white p-5 dark:border-white/[0.06] dark:bg-slate-800/60">
            <div class="agr-section-head">
                <span class="agr-section-badge"><i class="fa-solid fa-users text-[11px]"></i></span>
                <p class="agr-section-title">Tenant Contact</p>
            </div>
            <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div><label class="text-xs text-slate-400">PIC Name</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->pic_penyewa) !!}</p></div>
                <div><label class="text-xs text-slate-400">PIC Phone Number</label><p class="mt-1 text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->pic_phonenumber_penyewa) !!}</p></div>
                <div class="sm:col-span-2"><label class="text-xs text-slate-400">Email</label><p class="mt-1 break-words text-sm font-medium text-slate-800 dark:text-white">{!! $dash($agreement->pic_email_penyewa) !!}</p></div>
            </div>
        </div>

    </div>

    {{-- Right panel: timeline / document completeness / related / attachments --}}
    <div class="flex min-h-0 flex-col xl:col-span-7">

        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-2 dark:border-white/[0.06] dark:bg-slate-900/60">
            <div class="flex w-full items-center gap-1 rounded-lg border border-slate-200 bg-white p-1.5 shadow-sm dark:border-white/[0.08] dark:bg-slate-800">
                <button type="button" class="agr-detail-tab view-agr-tab active inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-200" data-tab="timeline">
                    <i class="fa-solid fa-clock-rotate-left text-[12px]"></i> Timeline
                </button>
                @if ($kind['docs'])
                <button type="button" class="agr-detail-tab view-agr-tab inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-200" data-tab="completeness">
                    <i class="fa-solid fa-list-check text-[12px]"></i> Documents
                </button>
                @endif
                <button type="button" class="agr-detail-tab view-agr-tab inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-200" data-tab="related">
                    <i class="fa-solid fa-link text-[12px]"></i> Related
                    @if (count($related))<span class="rounded-full bg-slate-200 px-1.5 text-[10px] dark:bg-white/10">{{ count($related) }}</span>@endif
                </button>
                <button type="button" class="agr-detail-tab view-agr-tab inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-200" data-tab="attachments">
                    <i class="fa-solid fa-paperclip text-[12px]"></i> Attachments
                    @if (count($attachments))<span class="rounded-full bg-slate-200 px-1.5 text-[10px] dark:bg-white/10">{{ count($attachments) }}</span>@endif
                </button>
                <button type="button" class="agr-detail-tab view-agr-tab inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-200" data-tab="activity">
                    <i class="fa-solid fa-list-ul text-[12px]"></i> Activity Log
                </button>
            </div>
        </div>

        {{-- Timeline --}}
        <div data-view-panel="timeline" class="modal-scroll flex-1 overflow-y-auto p-6">
            {{-- Process sheet (like the paper routing form): In/Out dates + notes per
                 step. IN = working_start_date, OUT = working_end_date on
                 tr_agreement_activity. Creator-only: Update / Save per row. --}}
            <h3 class="mb-1 text-sm font-semibold text-slate-800 dark:text-slate-100">Process Tracking</h3>
            <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">Use Update on a row to change it, then Save. Notes are optional.</p>

            <form id="viewAgrProcessForm" data-url="{{ route($kind['r']['process'], $eid, false) }}" autocomplete="off" class="mb-8 space-y-4">
                @php
                    $mainSteps = $processes->where('main', true);
                    $routeSteps = $processes->where('main', false);
                    $inputCls = 'proc-edit agr-input hidden w-full rounded-lg border-slate-300 bg-white shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
                    $inputStyle = 'height:32px; padding:0 6px; font-size:11px;';
                @endphp

                {{-- Process: Create / Cetak. No dates to pick: done once the step has a row, and the date shown is when the creator updated it. --}}
                <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-white/[0.06]">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 dark:bg-white/[0.03]">
                            <tr>
                                <th class="px-3 py-2 text-left">Process</th>
                                <th class="px-3 py-2 text-left" style="width:175px;">Date</th>
                                <th class="px-3 py-2 text-left">Notes</th>
                                <th class="px-3 py-2 text-right" style="width:90px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mainSteps as $proc)
                                <tr class="border-t border-slate-100 align-middle dark:border-white/[0.06]" data-proc-row>
                                    <td class="px-3 py-2">{{ $proc->descr }}</td>
                                    <td class="px-3 py-2">
                                        @if ($proc->done)
                                            <span class="proc-static inline-flex items-center gap-1.5 text-green-700"><i class="fa-solid fa-circle-check"></i> {{ $proc->date->format('l, d M Y') }}</span>
                                        @else
                                            <span class="proc-static text-slate-400">-</span>
                                        @endif

                                        @if ($proc->editable)
                                            <label class="proc-edit hidden cursor-pointer select-none items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                                <input type="hidden" name="process[{{ $proc->process_id }}][done]" value="0">
                                                <input type="checkbox" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-green-600 focus:ring-green-500" name="process[{{ $proc->process_id }}][done]" value="1" @checked($proc->done)>
                                                Done
                                            </label>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($proc->has_note)
                                            <span class="proc-static whitespace-pre-line">{!! $dash($proc->note) !!}</span>
                                            <input type="text" maxlength="2000" name="process[{{ $proc->process_id }}][note]" value="{{ $proc->note }}"
                                                class="{{ $inputCls }}" style="{{ $inputStyle }}" placeholder="Notes">
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap">
                                        @if ($canUpdateDocs && $proc->editable)
                                            <button type="button" class="btn-proc-edit inline-flex h-7 w-7 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-[12px] text-blue-700 hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-900/20 dark:text-blue-300" title="Update" aria-label="Update">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <span class="proc-row-actions hidden items-center gap-1.5">
                                                <button type="button" class="btn-proc-cancel inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-[12px] text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300" title="Cancel" aria-label="Cancel"><i class="fa-solid fa-xmark"></i></button>
                                                <button type="button" class="btn-proc-save inline-flex h-7 w-7 items-center justify-center rounded-lg bg-green-600 text-[12px] text-white hover:bg-green-700" title="Save" aria-label="Save"><i class="fa-solid fa-floppy-disk"></i></button>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Routing: sent Out, then returned In, both picked by the creator. --}}
                <div>
                    <h3 class="mb-2 text-sm font-semibold text-slate-800 dark:text-slate-100">Routing</h3>
                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-white/[0.06]">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 dark:bg-white/[0.03]">
                                <tr>
                                    <th class="px-3 py-2 text-left">To</th>
                                    <th class="px-3 py-2 text-left" style="width:175px;">Out</th>
                                    <th class="px-3 py-2 text-left" style="width:175px;">In</th>
                                    <th class="px-3 py-2 text-left">Notes</th>
                                    <th class="px-3 py-2 text-right" style="width:90px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($routeSteps as $proc)
                                    <tr class="border-t border-slate-100 align-middle dark:border-white/[0.06]" data-proc-row>
                                        <td class="px-3 py-2">{{ $proc->descr }}</td>

                                        @foreach ([['date_out', optional($proc->date_out)->format('Y-m-d'), true, $proc->date_out], ['date_in', optional($proc->date_in)->format('Y-m-d'), $proc->has_in, $proc->date_in]] as [$field, $val, $enabled, $dateObj])
                                            <td class="px-3 py-2">
                                                @if ($enabled)
                                                    <span class="proc-static">{!! $dash(optional($dateObj)->format('l, d M Y')) !!}</span>
                                                    <input type="date" name="process[{{ $proc->process_id }}][{{ $field }}]" value="{{ $val }}"
                                                        class="{{ $inputCls }}" style="{{ $inputStyle }}">
                                                @else
                                                    <span class="block h-4 rounded bg-slate-300 dark:bg-white/10" title="Not applicable"></span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="px-3 py-2">
                                            <span class="proc-static whitespace-pre-line">{!! $dash($proc->note) !!}</span>
                                            <input type="text" maxlength="2000" name="process[{{ $proc->process_id }}][note]" value="{{ $proc->note }}"
                                                class="{{ $inputCls }}" style="{{ $inputStyle }}" placeholder="Notes">
                                        </td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            @if ($canUpdateDocs && $proc->editable)
                                                <button type="button" class="btn-proc-edit inline-flex h-7 w-7 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-[12px] text-blue-700 hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-900/20 dark:text-blue-300" title="Update" aria-label="Update">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <span class="proc-row-actions hidden items-center gap-1.5">
                                                    <button type="button" class="btn-proc-cancel inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-[12px] text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300" title="Cancel" aria-label="Cancel"><i class="fa-solid fa-xmark"></i></button>
                                                    <button type="button" class="btn-proc-save inline-flex h-7 w-7 items-center justify-center rounded-lg bg-green-600 text-[12px] text-white hover:bg-green-700" title="Save" aria-label="Save"><i class="fa-solid fa-floppy-disk"></i></button>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>

        {{-- Document completeness (PSM / OLA only). The creator or PIC Legal can tick documents that
             arrive later: Update unlocks the rows, Save writes them. --}}
        @if ($kind['docs'])
        <div data-view-panel="completeness" class="modal-scroll hidden flex-1 overflow-y-auto p-6">
            <div class="mb-1 flex items-start justify-between gap-3">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Document Completeness</h3>

                @if ($canUpdateDocs && $documents->count())
                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" id="btnViewDocsUpdate" class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-900/20 dark:text-blue-300">
                            <i class="fa-solid fa-pen-to-square"></i> Update
                        </button>
                        <button type="button" id="btnViewDocsCancel" class="hidden h-8 items-center rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300">
                            Cancel
                        </button>
                        <button type="submit" form="viewAgrDocsForm" id="btnViewDocsSave" class="hidden h-8 items-center gap-1.5 rounded-lg bg-green-600 px-3 text-xs font-semibold text-white hover:bg-green-700">
                            <i class="fa-solid fa-floppy-disk"></i> Save
                        </button>
                    </div>
                @endif
            </div>
            <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">
                {{ $receivedCount }} of {{ $totalDocs }} documents received
                @if ($missingRequired)
                    · <span class="font-semibold text-rose-600">{{ $missingRequired }} required still missing</span>
                @elseif ($totalDocs)
                    · <span class="font-semibold text-green-600">all required documents received</span>
                @endif
            </p>

            <div class="mb-4 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                <div class="h-full rounded-full bg-green-500" style="width: {{ $pct }}%"></div>
            </div>

            <form id="viewAgrDocsForm" data-url="{{ route($kind['r']['documents'], $eid, false) }}" class="space-y-2" autocomplete="off">
                @forelse ($documents as $doc)
                    <div class="flex items-start gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 dark:border-white/[0.06] dark:bg-slate-800">
                        <span class="doc-static mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] {{ $doc->agreementdocument_received ? 'bg-green-500 text-white' : 'bg-slate-200 text-slate-400 dark:bg-white/10' }}">
                            <i class="fa-solid {{ $doc->agreementdocument_received ? 'fa-check' : 'fa-minus' }}"></i>
                        </span>
                        <span class="doc-edit mt-0.5 hidden h-5 w-5 shrink-0 items-center justify-center">
                            <input type="hidden" name="documents[{{ $doc->agreementdocument_id }}][received]" value="0">
                            <input type="checkbox" class="h-4 w-4 rounded border-slate-300"
                                name="documents[{{ $doc->agreementdocument_id }}][received]" value="1" @checked($doc->agreementdocument_received)>
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $doc->agreementdocument_descr }}</div>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px]">
                                @if ($doc->agreementdocument_received)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 font-semibold text-green-700">Received</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600">Not received</span>
                                @endif
                                @if ($doc->agreementdocument_required)
                                    <span class="rounded-full bg-rose-50 px-2 py-0.5 font-semibold text-rose-600">Required</span>
                                @else
                                    <span class="text-slate-400">Optional</span>
                                @endif
                                @if ($doc->agreementdocument_received && $doc->agreementdocument_received_at)
                                    <span class="doc-static text-slate-400"><i class="fa-regular fa-calendar-check mr-1"></i>{{ $doc->agreementdocument_received_at->format('Y-m-d H:i') }}</span>
                                @endif
                            </div>
                            @if ($doc->agreementdocument_note)
                                <div class="doc-static mt-2 whitespace-pre-line text-xs text-slate-500 dark:text-slate-400">{{ $doc->agreementdocument_note }}</div>
                            @endif
                            <input type="text" maxlength="2000" placeholder="Notes" value="{{ $doc->agreementdocument_note }}"
                                name="documents[{{ $doc->agreementdocument_id }}][note]"
                                class="doc-edit agr-input mt-2 hidden" style="height:36px;">
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-400 dark:border-white/[0.08]">No documents</div>
                @endforelse
            </form>
        </div>
        @endif

        {{-- Related documents: Addendum and other document types are separate
             menus still to be built; they will list here once they exist. --}}
        <div data-view-panel="related" class="modal-scroll hidden flex-1 overflow-y-auto p-6">
            <h3 class="mb-4 text-sm font-semibold text-slate-800 dark:text-slate-100">Related Document</h3>
            <div class="space-y-2">
                @forelse ($related as $item)
                    <a href="{{ $item['url'] }}" class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 transition-all duration-200 hover:bg-slate-50 dark:border-white/[0.06] dark:bg-slate-800 dark:hover:bg-white/[0.04]">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-white/[0.06] dark:text-slate-300">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $item['agreement_id'] }}</span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $item['type'] }}</span>
                                    @if ($item['completed'])
                                        <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Completed</span>
                                    @else
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700">Active</span>
                                    @endif
                                </div>
                                <div class="mt-1 text-xs text-slate-400">
                                    {{ $item['number'] ? 'No. '.$item['number'].' · ' : '' }}{{ $item['date'] }} · by {{ $item['created_by'] }}
                                </div>
                            </div>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square text-slate-400"></i>
                    </a>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 px-5 py-10 text-center dark:border-white/[0.08]">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.06]">
                            <i class="fa-solid fa-link text-slate-400"></i>
                        </div>
                        <div class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200">No related documents yet</div>
                        <div class="mt-1 text-xs text-slate-400">Addendums and other documents linked to this agreement will appear here.</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Attachments --}}
        <div data-view-panel="attachments" class="modal-scroll hidden flex-1 overflow-y-auto p-6">
            <h3 class="mb-4 text-sm font-semibold text-slate-800 dark:text-slate-100">Attachments</h3>

            @if (! $completed && $canUpdateDocs)
            <form id="viewAgrUploadForm" data-url="{{ route($kind['r']['attachment'], $eid, false) }}" class="mb-4 flex flex-wrap items-center gap-2">
                <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf,.xlsx,.xls,.doc,.docx"
                    class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white text-xs file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold dark:border-white/[0.08] dark:bg-slate-800">
                <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg bg-blue-600 px-4 text-xs font-semibold text-white hover:bg-blue-700">
                    <i class="fa-solid fa-upload"></i> Upload
                </button>
                <p class="w-full text-[11px] text-slate-400">JPG, PNG, PDF, Word or Excel · max 5 MB each.</p>
            </form>
            @endif

            <div class="space-y-2">
                @forelse ($attachments as $file)
                    <div class="flex items-center gap-2">
                    <a href="{{ $file['url'] ?? '#' }}" target="_blank" class="flex min-w-0 flex-1 items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 transition-all duration-200 hover:bg-slate-50 dark:border-white/[0.06] dark:bg-slate-800 dark:hover:bg-white/[0.04]">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-white/[0.06] dark:text-slate-300">
                                <i class="fa-solid fa-file"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ $file['display_name'] }}</div>
                                <div class="mt-1 text-xs text-slate-400">{{ strtoupper($file['extention'] ?: '-') }} &bull; {{ $fmtSize($file['size']) }}</div>
                                <div class="mt-1 text-[11px] text-slate-400">Uploaded {{ $file['created_at'] }}{{ $file['created_by'] ? ' by '.$file['created_by'] : '' }}</div>
                            </div>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square text-slate-400"></i>
                    </a>
                    @if (! $completed && $canUpdateDocs)
                        <button type="button" class="btn-del-attachment inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-900/20"
                            data-url="{{ route($kind['r']['attachment_delete'], $eid, false) }}" data-id="{{ $file['id'] }}" data-name="{{ $file['display_name'] }}" title="Delete" aria-label="Delete">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    @endif
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 px-4 py-5 text-center text-sm text-slate-400 dark:border-white/[0.08]">No attachment available</div>
                @endforelse
            </div>
        </div>

        {{-- Activity log: what happened after creation (Create is the first row of the process table). --}}
        <div data-view-panel="activity" class="modal-scroll hidden flex-1 overflow-y-auto p-6">
            <h3 class="mb-4 text-sm font-semibold text-slate-800 dark:text-slate-100">Activity Log</h3>

            @forelse ($timeline as $item)
                <div class="relative pb-3 pl-10">
                    @unless ($loop->last)
                        <div class="absolute bottom-0 left-[15px] top-10 w-px bg-slate-200 dark:bg-white/[0.06]"></div>
                    @endunless

                    <div class="absolute left-0 top-1 flex h-8 w-8 items-center justify-center rounded-2xl text-white shadow-md {{ $item['color'] }}">
                        <i class="fa-solid {{ $item['icon'] }} text-[11px]"></i>
                    </div>

                    <div class="rounded-lg border border-slate-200/80 bg-slate-50/20 px-4 py-2.5 dark:border-white/[0.05] dark:bg-slate-900/60">
                        <div class="text-[13px] font-semibold text-slate-800 dark:text-white">{{ $item['title'] }}</div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[10px] text-slate-400 dark:text-slate-500">
                            <span>{{ $item['by'] }}</span>
                            <span class="opacity-40">&bull;</span>
                            <span>{{ $item['at'] }}</span>
                        </div>
                        @if ($item['descr'])
                            <div class="mt-2 whitespace-pre-line rounded-lg border border-slate-100 bg-slate-50/70 px-2.5 py-2 text-[11px] leading-5 text-slate-600 dark:border-white/[0.04] dark:bg-white/[0.03] dark:text-slate-300">{{ $item['descr'] }}</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-400 dark:border-white/[0.08]">No activity yet</div>
            @endforelse
        </div>

    </div>
</div>
