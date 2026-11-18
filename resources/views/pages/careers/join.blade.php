@php
    $eyeIcon = 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z';
    $pencilIcon = 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z';
    $docIcon = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
    $dateFieldCls = 'w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-3 text-sm font-medium text-gray-800 transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 disabled:cursor-not-allowed disabled:border-gray-100 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700/40';
    $ghostBtn = 'inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none active:scale-95 dark:border-gray-600 dark:bg-transparent dark:text-gray-300 dark:hover:border-indigo-500 dark:hover:text-indigo-300';
    $saveBtn = 'inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-indigo-500/30 transition hover:bg-indigo-700 focus:outline-none active:scale-95 disabled:cursor-not-allowed disabled:opacity-60';
    $schedulePayroll = $payrolls->first();
    $isScheduled = !empty(optional($schedulePayroll)->work_start_date);
    $scheduleMinWorkStart = optional($schedulePayroll)->work_start_date ? \Carbon\Carbon::parse($schedulePayroll->work_start_date)->format('Y-m-d') : '';
@endphp

<style>
    /* Onboarding checklist cards — highlight follows the checkbox state */
    .ob-item { position: relative; display: flex; align-items: center; gap: .75rem; cursor: pointer; border: 1px solid #e5e7eb; border-radius: 1rem;
        background: #fff; padding: .75rem .875rem; box-shadow: 0 1px 2px rgba(0,0,0,.04); transition: all .18s ease; }
    .ob-item:not(:has(input:disabled)):hover { transform: translateY(-2px); box-shadow: 0 8px 18px -8px rgba(0,0,0,.18); border-color: #a5b4fc; }
    .ob-item:has(input:disabled) { cursor: default; }
    .ob-item:has(input:checked) { border-color: #6ee7b7; background: #f0fdf9; }
    .ob-item:has(input:focus-visible) { outline: 2px solid #6366f1; outline-offset: 2px; }

    .ob-icon { display: flex; height: 2.5rem; width: 2.5rem; flex-shrink: 0; align-items: center; justify-content: center;
        border-radius: .75rem; background: #f3f4f6; color: #9ca3af; transition: all .18s ease; }
    .ob-item:has(input:checked) .ob-icon { background: #d1fae5; color: #059669; }

    .ob-text { display: flex; min-width: 0; flex: 1; flex-direction: column; line-height: 1.25; }
    .ob-name { font-size: .875rem; font-weight: 600; color: #1f2937; }
    .ob-status { margin-top: .125rem; font-size: .6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
    .ob-status .ob-off { color: #9ca3af; }
    .ob-status .ob-on { display: none; color: #059669; }
    .ob-item:has(input:checked) .ob-status .ob-off { display: none; }
    .ob-item:has(input:checked) .ob-status .ob-on { display: inline; }

    /* toggle switch */
    .ob-switch { position: relative; height: 1.5rem; width: 2.75rem; flex-shrink: 0; border-radius: 9999px; background: #d1d5db; transition: background .18s ease; }
    .ob-switch::after { content: ''; position: absolute; left: .1875rem; top: .1875rem; height: 1.125rem; width: 1.125rem; border-radius: 9999px;
        background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.3); transition: transform .18s ease; }
    .ob-item:has(input:checked) .ob-switch { background: #10b981; }
    .ob-item:has(input:checked) .ob-switch::after { transform: translateX(1.25rem); }
    .ob-item:has(input:disabled) .ob-switch { opacity: .75; }

    .dark .ob-item { background: #1f2937; border-color: #374151; }
    .dark .ob-item:has(input:checked) { border-color: rgba(16,185,129,.45); background: rgba(16,185,129,.1); }
    .dark .ob-icon { background: #374151; color: #9ca3af; }
    .dark .ob-item:has(input:checked) .ob-icon { background: rgba(16,185,129,.2); color: #34d399; }
    .dark .ob-name { color: #f3f4f6; }
    .dark .ob-switch { background: #4b5563; }
    .dark .ob-item:has(input:checked) .ob-switch { background: #10b981; }
</style>

<div class="flex flex-col px-5 py-5">

    {{-- ── Phase 1 · Integrity Documents ──────────────────────────── --}}
    <div class="flex items-stretch gap-4">
        <div class="flex flex-shrink-0 flex-col items-center">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white shadow-sm shadow-indigo-500/30 ring-4 ring-white dark:ring-gray-800">1</div>
            <div class="mt-1 w-0.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        </div>
        <div class="mb-5 min-w-0 flex-1 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5 dark:border-gray-700/60">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Phase 1</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Integrity Documents</p>
                    </div>
                </div>
                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400">2 Documents</span>
            </div>
            <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2">
                @foreach ([
                    ['integritasForm', 'Form Pakta Integritas'],
                    ['pernyataanForm', 'Surat Pernyataan Penggunaan Fasilitas Elektronik'],
                ] as [$formId, $docName])
                    <form id="{{ $formId }}" class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3 transition hover:border-indigo-200 hover:bg-white hover:shadow-sm dark:border-gray-700/60 dark:bg-gray-700/20 dark:hover:bg-gray-700/40">
                        @csrf
                        <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                        <input type="hidden" name="job_title" value="{{ $career->job_title ?? '' }}">
                        <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
                        <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                        <input type="hidden" name="job_level" value="{{ $career->job_level ?? '' }}">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-500 dark:bg-rose-500/15 dark:text-rose-300">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $docIcon }}"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100" title="{{ $docName }}">{{ $docName }}</p>
                                <p class="text-[11px] uppercase tracking-wide text-gray-400">PDF</p>
                            </div>
                        </div>
                        <button type="submit" class="{{ $ghostBtn }} shrink-0">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $eyeIcon }}"/></svg>
                            Preview
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Phase 2 · Onboarding Checklist ───────────────────────────── --}}
    <div class="flex items-stretch gap-4" id="docid_onboarding" data-docid="{{ optional($onboarding)->docid }}">
        <div class="flex flex-shrink-0 flex-col items-center">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white shadow-sm shadow-indigo-500/30 ring-4 ring-white dark:ring-gray-800">2</div>
            <div class="mt-1 w-0.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        </div>
        <div class="mb-5 min-w-0 flex-1 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5 dark:border-gray-700/60">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Phase 2</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Onboarding Checklist</p>
                    </div>
                </div>
                <span id="checklistCountPill"
                    class="hidden rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400"></span>
            </div>

            <form id="checklistForm" class="px-5 py-4">
                @csrf
                <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
                <div class="mb-4 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                    <div id="checklistBar" class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width:0%"></div>
                </div>
                <div id="checklistArea" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                </div>

                <div class="mt-5 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700/60">
                    <button type="button" id="btnUpdateChecklist" class="{{ $ghostBtn }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $pencilIcon }}"/></svg>
                        Update
                    </button>
                    <button type="submit" id="btnSaveChecklist" style="display:none;" class="{{ $saveBtn }}">
                        <svg class="checklist-spin hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span class="checklist-text">Save Checklist</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Phase 3 · Onboarding Schedule ────────────────────────────── --}}
    <div class="flex items-start gap-4">
        <div class="flex flex-shrink-0 flex-col items-center">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white shadow-sm shadow-indigo-500/30 ring-4 ring-white dark:ring-gray-800">3</div>
        </div>
        <div class="min-w-0 flex-1 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5 dark:border-gray-700/60">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Phase 3</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Onboarding Schedule</p>
                    </div>
                </div>
                <span id="scheduleStatusPill"
                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $isScheduled ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' }}">
                    {{ $isScheduled ? 'Scheduled' : 'Pending' }}
                </span>
            </div>

            <form id="scheduleForm" class="p-5">
                @csrf
                <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col">
                        <label for="sch_work_start_date" class="mb-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300">Tanggal Mulai Kerja</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                            <input type="date" id="sch_work_start_date" name="work_start_date" disabled
                                value="{{ $scheduleMinWorkStart }}"
                                @if ($scheduleMinWorkStart) min="{{ $scheduleMinWorkStart }}" @endif
                                class="{{ $dateFieldCls }}" required>
                        </div>
                        <p class="mt-1.5 flex items-start gap-1.5 text-xs text-gray-400">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                            Tidak boleh sebelum tanggal yang sudah dikonfirmasi di Payroll.
                        </p>
                    </div>
                    <div class="flex flex-col">
                        <label for="sch_availability_date" class="mb-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300">Tanggal Selesai Kerja</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5"/></svg>
                            <input type="date" id="sch_availability_date" name="availability_date" disabled
                                value="{{ optional($schedulePayroll)->availability_date ? \Carbon\Carbon::parse($schedulePayroll->availability_date)->format('Y-m-d') : '' }}"
                                class="{{ $dateFieldCls }}" required>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700/60">
                    <button type="button" id="btnUpdateSchedule" class="{{ $ghostBtn }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $pencilIcon }}"/></svg>
                        Update
                    </button>
                    <button type="submit" id="btnSaveSchedule" style="display:none;" class="{{ $saveBtn }}">
                        <span class="sch-text">Save Schedule &amp; Send Email</span>
                        <svg class="sch-spin hidden h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    if (window.lucide) lucide.createIcons();
</script>

<script>
    $('#integritasForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: "{{ route('paktaintegritas.pdf') }}",
            method: 'POST',
            data: form.serialize(),
            xhrFields: {
                responseType: 'blob'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(blob) {
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank'); // 👈 preview PDF di tab baru
            },
            error: function() {
                alert("Failed to generate PDF.");
            }
        });
    });
</script>

<script>
    $('#pernyataanForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: "{{ route('pernyataanelectonik.pdf') }}",
            method: 'POST',
            data: form.serialize(),
            xhrFields: {
                responseType: 'blob'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(blob) {
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank'); // 👈 preview PDF di tab baru
            },
            error: function() {
                alert("Failed to generate PDF.");
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        const docidOnboarding = $('#docid_onboarding').data('docid');

        function setChecklistSaving(isSaving) {
            const $button = $('#btnSaveChecklist');
            $button.prop('disabled', isSaving);
            $button.find('.checklist-spin').toggleClass('hidden', !isSaving);
            $button.find('.checklist-text').text(isSaving ? 'Saving...' : 'Save Checklist');
        }

        // Pick an icon from the item's wording (Heroicons outline paths).
        function obIconPath(descr) {
            const d = String(descr || '').toLowerCase();
            const icons = [
                [/video|training|orientasi/, 'M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z'],
                [/komputer|laptop|computer|pc\b/, 'M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25'],
                [/bpjs|asuransi|kesehatan|medical/, 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
                [/e-?mail|surel/, 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'],
                [/id\s*card|kartu|badge/, 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z'],
                [/integritas|pakta|integrity/, 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
                [/talenta|talent|akun|account|user/, 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z'],
                [/parkir|parking|kendaraan/, 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
            ];
            const hit = icons.find(([re]) => re.test(d));
            return hit ? hit[1] : 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        }

        function updateChecklistCountPill() {
            const $items = $('#checklistArea input[name="checklist[]"]');
            const total = $items.length;
            if (!total) {
                $('#checklistCountPill').addClass('hidden');
                return;
            }
            const checkedCount = $items.filter(':checked').length;
            $('#checklistCountPill').removeClass('hidden').text(checkedCount + ' / ' + total + ' Checked');
            $('#checklistBar').css('width', Math.round(checkedCount / total * 100) + '%');
        }

        // Keep the progress bar live while ticking boxes in edit mode.
        $(document).on('change', '#checklistArea input[name="checklist[]"]', updateChecklistCountPill);

        function setChecklistEditing(isEditing) {
            $('#checklistArea input[name="checklist[]"]').prop('disabled', !isEditing);
            $('#btnUpdateChecklist').css('display', isEditing ? 'none' : '');
            $('#btnSaveChecklist').css('display', isEditing ? '' : 'none');
        }

        $('#btnUpdateChecklist').on('click', function() {
            setChecklistEditing(true);
        });

        $('#checklistForm').off('submit.checklist').on('submit.checklist', function(event) {
            event.preventDefault();

            if (!docidOnboarding) {
                toastr.error('Onboarding belum dibuat atau DocID tidak tersedia.');
                return;
            }

            const checked = $('#checklistArea input[name="checklist[]"]:checked')
                .map(function() { return this.value; })
                .get();

            setChecklistSaving(true);

            $.ajax({
                url: "{{ route('onboarding.checklist.update') }}",
                type: 'POST',
                headers: { 'Accept': 'application/json' },
                data: {
                    _token: $('#checklistForm input[name="_token"]').val(),
                    docid_onboarding: docidOnboarding,
                    cpnyid: $('#checklistForm input[name="cpnyid"]').val(),
                    checked: checked
                }
            }).done(function(response) {
                if (response && response.success) {
                    toastr.success('Checklist berhasil disimpan.');
                    updateChecklistCountPill();
                    setChecklistEditing(false);
                } else {
                    toastr.error(response.message || response.error || 'Gagal menyimpan checklist.');
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || xhr.responseJSON?.error ||
                    'Terjadi kesalahan saat menyimpan checklist.');
            }).always(function() {
                setChecklistSaving(false);
            });
        });

        if (!docidOnboarding) {
            $('#checklistArea').html(`
                <div class="col-span-full flex flex-col items-center gap-2 rounded-2xl border border-dashed border-gray-200 bg-gray-50/60 px-5 py-8 text-center dark:border-gray-700 dark:bg-gray-700/20">
                    <svg class="h-5 w-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <circle cx="12" cy="12" r="9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11.25 11.5H12v4.75h.75"/>
                    </svg>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Checklist belum tersedia</p>
                    <p class="max-w-sm text-sm leading-relaxed text-gray-400 dark:text-gray-500">
                        Checklist onboarding otomatis dibuat setelah data <span class="font-semibold text-gray-500 dark:text-gray-400">Payroll Confirmation</span>
                        disimpan di tab <span class="font-semibold text-gray-500 dark:text-gray-400">Payroll</span>.
                    </p>
                </div>
            `);
            $('#btnUpdateChecklist').css('display', 'none');
            return; // ⟵ stop supaya tidak nembak /onboarding tanpa docid
        }

        const url = `/onboarding/${encodeURIComponent(docidOnboarding)}`;
        $.get(url, function(data) {
            let html = '';
            data.forEach(item => {
                const checked = item.checklist_onboarding_receive ? 'checked' : '';
                html += `
                <label class="ob-item">
                    <input type="checkbox" name="checklist[]" value="${item.id}" ${checked} disabled class="sr-only">
                    <span class="ob-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="${obIconPath(item.checklist_onboarding_descr)}"/></svg>
                    </span>
                    <span class="ob-text">
                        <span class="ob-name">${$('<div>').text(item.checklist_onboarding_descr ?? '').html()}</span>
                        <span class="ob-status"><span class="ob-off">Pending</span><span class="ob-on">Received</span></span>
                    </span>
                    <span class="ob-switch" aria-hidden="true"></span>
                </label>
            `;
            });
            $('#checklistArea').html(html);
            updateChecklistCountPill();
        }).fail(function(xhr) {
            toastr.error('Gagal memuat checklist onboarding.');
        });
    });
</script>

<script>
    $(function() {
        function setScheduleSaving(isSaving) {
            const $btn = $('#btnSaveSchedule');
            $btn.prop('disabled', isSaving);
            $btn.find('.sch-spin').toggleClass('hidden', !isSaving);
            $btn.find('.sch-text').text(isSaving ? 'Saving...' : 'Save Schedule');
        }

        function setScheduleEditing(isEditing) {
            $('#sch_work_start_date, #sch_availability_date').prop('disabled', !isEditing);
            $('#btnUpdateSchedule').css('display', isEditing ? 'none' : '');
            $('#btnSaveSchedule').css('display', isEditing ? '' : 'none');
        }

        $('#btnUpdateSchedule').on('click', function() {
            setScheduleEditing(true);
        });

        $('#scheduleForm').on('submit', function(e) {
            e.preventDefault();

            const payload = {
                _token: $(this).find('input[name="_token"]').val(),
                applicant_id: $(this).find('input[name="applicant_id"]').val(),
                jobapply_id: $(this).find('input[name="jobapply_id"]').val(),
                cpnyid: $(this).find('input[name="cpnyid"]').val(),
                availability_date: $('#sch_availability_date').val(),
                work_start_date: $('#sch_work_start_date').val()
            };

            if (!payload.availability_date || !payload.work_start_date) {
                toastr.error('Tanggal Mulai dan Tanggal Selesai Kerja wajib diisi.');
                return;
            }

            setScheduleSaving(true);

            $.ajax({
                    url: "{{ route('onboarding.schedule.update') }}",
                    type: 'POST',
                    data: payload,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .done(function(resp) {
                    if (resp && resp.success) {
                        toastr.success(resp.message ||
                            'Jadwal berhasil disimpan & email terkirim.');
                        setScheduleEditing(false);
                        $('#scheduleStatusPill')
                            .removeClass('bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400')
                            .addClass('bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400')
                            .text('Scheduled');
                    } else {
                        toastr.error(resp.message || 'Gagal menyimpan jadwal.');
                    }
                })
                .fail(function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const msgs = [];
                        Object.values(xhr.responseJSON.errors).forEach(arr => arr[0] && msgs.push(
                            arr[0]));
                        toastr.error(msgs.join('<br>'), 'Validation Error', {
                            escapeHtml: true
                        });
                    } else if (xhr.status === 422 && xhr.responseJSON?.message) {
                        toastr.error(xhr.responseJSON.message, 'Validation Error', {
                            escapeHtml: true
                        });
                    } else {
                        const message = xhr.status === 419
                            ? 'Sesi telah berakhir. Muat ulang halaman lalu coba kembali.'
                            : (xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan jadwal.');
                        toastr.error(message, 'Gagal menyimpan jadwal', {
                            escapeHtml: true
                        });
                    }
                })
                .always(function() {
                    setScheduleSaving(false);
                });
        });
    });
</script>
