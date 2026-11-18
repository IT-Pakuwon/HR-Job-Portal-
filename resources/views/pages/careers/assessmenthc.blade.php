<style>
    /* Interview assessment — option cards + criterion cards */
    .asm-opt { position: relative; display: flex; cursor: default; align-items: flex-start; gap: .625rem; border: 1px solid #e5e7eb; border-radius: .875rem;
        background: #fff; padding: .625rem .75rem; text-align: left; transition: all .15s ease; }
    .asm-opt:hover { border-color: #a5b4fc; background: #f5f7ff; }
    .asm-opt .asm-chip { display: flex; height: 1.5rem; width: 1.5rem; flex-shrink: 0; align-items: center; justify-content: center; border-radius: .5rem;
        background: #f3f4f6; color: #6b7280; font-size: .75rem; font-weight: 700; transition: all .15s ease; }
    .asm-opt .opt-descr { font-size: .8125rem; line-height: 1.35; color: #6b7280; transition: color .15s ease; }
    .asm-opt[data-selected="1"] { border-color: #6366f1; background: #eef2ff; box-shadow: 0 0 0 3px rgba(99, 102, 241, .15); }
    .asm-opt[data-selected="1"] .asm-chip { background: #4f46e5; color: #fff; }
    .asm-opt[data-selected="1"] .opt-descr { color: #3730a3; font-weight: 600; }
    .asm-opt:has(input:focus-visible) { outline: 2px solid #6366f1; outline-offset: 2px; }
    .asm-card { transition: border-color .15s ease, box-shadow .15s ease; }
    .asm-card:has(input:checked) { border-color: #c7d2fe; }

    .dark .asm-opt { background: rgba(17, 24, 39, .6); border-color: #374151; }
    .dark .asm-opt:hover { border-color: #6366f1; background: rgba(99, 102, 241, .1); }
    .dark .asm-opt .asm-chip { background: #374151; color: #9ca3af; }
    .dark .asm-opt .opt-descr { color: #9ca3af; }
    .dark .asm-opt[data-selected="1"] { background: rgba(99, 102, 241, .18); border-color: #818cf8; box-shadow: 0 0 0 3px rgba(99, 102, 241, .25); }
    .dark .asm-opt[data-selected="1"] .asm-chip { background: #6366f1; color: #fff; }
    .dark .asm-opt[data-selected="1"] .opt-descr { color: #e0e7ff; }
    .dark .asm-card:has(input:checked) { border-color: rgba(99, 102, 241, .5); }
</style>

<form id="assessmentFormHC" method="POST">
    @csrf

    <input type="hidden" name="totalscore" id="totalScoreHCInput" value="0">
    <input type="hidden" name="result_status" id="resultStatusHC" value="NOT SUITABLE">
    <input type="hidden" name="docid" value="{{ $career->docid }}">

    {{-- ── Action bar ─────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-700/60">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75"/></svg>
            </span>
            <div>
                <p class="text-base font-bold text-gray-900 dark:text-gray-100">HC Interview Assessment</p>
                <p id="editHintHC" class="text-xs italic text-gray-400"></p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="cancelBtnHC" onclick="cancelEditHC()" style="display:none"
                class="items-center rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700/40">
                Cancel
            </button>
            <button type="button" id="actionBtnHC" onclick="handleActionHC()" style="display:none"
                class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm shadow-indigo-500/30 transition hover:bg-indigo-700 focus:outline-none active:scale-95">
                Start Assessment
            </button>
            <button type="button" id="toggleBtnHC" onclick="toggleSectionHC()" title="Collapse/expand"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-200">
                <svg id="toggleIconHC" class="h-4 w-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </button>
        </div>
    </div>

    <div id="hcSectionBody">

    {{-- ── Info header ─────────────────────────────────────────────── --}}
    <div id="infoHeaderHC" style="display:none" class="grid grid-cols-1 gap-3 py-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Interviewer --}}
        <div class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-700/60 dark:bg-gray-700/20">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white ring-4 ring-indigo-100 dark:ring-indigo-500/20">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            </span>
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Nama Interview</p>
                <p id="namaInterviewHC" class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                    {{ $tr_assessment && $tr_assessment->user ? $tr_assessment->user_name : $user->name }}
                </p>
            </div>
        </div>

        {{-- Date & time --}}
        <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-700/60 dark:bg-gray-700/20">
            <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                Tanggal &amp; Jam
            </p>
            <div class="mt-1.5 flex items-center gap-1.5">
                <input type="date" name="interview_date"
                    value="{{ $tr_assessment && $tr_assessment->assessment_date ? \Carbon\Carbon::parse($tr_assessment->assessment_date)->format('Y-m-d') : now('Asia/Jakarta')->format('Y-m-d') }}"
                    required disabled
                    class="min-w-0 flex-1 rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <input type="time" name="interview_time"
                    value="{{ $tr_assessment && $tr_assessment->assessment_date ? \Carbon\Carbon::parse($tr_assessment->assessment_date)->format('H:i') : now('Asia/Jakarta')->format('H:i') }}"
                    required disabled
                    class="w-24 rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-700 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>

        {{-- Result --}}
        <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-700/60 dark:bg-gray-700/20">
            <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Standar Scoring
            </p>
            <p class="mt-2">
                <span id="resultTextHC" class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-bold text-gray-500 dark:bg-gray-800 dark:text-gray-400">NOT SUITABLE</span>
            </p>
        </div>

        {{-- Total --}}
        <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-700/60 dark:bg-gray-700/20">
            <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                Jumlah Nilai
            </p>
            <p class="mt-1 text-2xl font-bold leading-none text-gray-900 dark:text-white">
                <span id="totalScoreHC">0</span><span class="text-sm font-medium text-gray-400"> / 28</span>
            </p>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                <div id="totalBarHC" class="h-full rounded-full bg-rose-500 transition-all duration-500" style="width:0%"></div>
            </div>
        </div>
    </div>

    {{-- ── Scoring: one card per criterion ─────────────────────────── --}}
    <div class="space-y-3">
        @foreach ($assessmentGroups as $groupIndex => $group)
            @php $selectedScore = $group['selected_score']; @endphp
            <section class="asm-card overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                <header class="flex items-center gap-3 px-4 py-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-500 dark:bg-gray-700 dark:text-gray-300">{{ $loop->iteration }}</span>
                    <h4 class="min-w-0 flex-1 text-sm font-bold uppercase leading-snug tracking-wide text-gray-800 dark:text-gray-100">{{ $group['assessment_group'] }}</h4>
                    <div class="flex shrink-0 items-center gap-2">
                        <span class="hidden text-[10px] font-bold uppercase tracking-widest text-gray-400 sm:inline">Nilai</span>
                        @if ($selectedScore !== null)
                            <span id="scoreCellHC-{{ $groupIndex }}" class="score-badge inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-base font-bold text-white shadow-sm shadow-indigo-500/30">{{ $selectedScore }}</span>
                        @else
                            <span id="scoreCellHC-{{ $groupIndex }}" class="score-badge inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gray-100 text-base font-bold text-gray-400 dark:bg-gray-800 dark:text-gray-500">&ndash;</span>
                        @endif
                    </div>
                </header>
                <div class="grid gap-2 px-4 pb-4" style="grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));">
                    @foreach ($group['options'] as $score => $option)
                        <label class="assessment-option-label asm-opt" data-selected="{{ $selectedScore !== null && $selectedScore == $score ? '1' : '0' }}">
                            <span class="asm-chip">{{ $score }}</span>
                            <span class="opt-descr pointer-events-none">{{ $option['assessment_descr'] }}</span>
                            <input type="radio" name="scores[{{ $groupIndex }}]" value="{{ $score }}"
                                {{ $selectedScore !== null && $selectedScore == $score ? 'checked' : '' }}
                                disabled class="sr-only"
                                onclick="updateScoreHC({{ $groupIndex }}, {{ $score }})">
                            <span class="score-check pointer-events-none absolute -right-1.5 -top-1.5 hidden h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-white shadow ring-2 ring-white dark:ring-gray-900">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    {{-- ── Standard scoring legend ────────────────────────────────── --}}
    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/60 p-3.5 dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            </span>
            <div>
                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">SUITABLE</p>
                <p class="text-xs text-emerald-600/80 dark:text-emerald-400/80">21&ndash;28 (Manager/Supervisor) &middot; 20&ndash;25 (Staff)</p>
            </div>
        </div>
        <div class="flex items-start gap-3 rounded-2xl border border-amber-100 bg-amber-50/60 p-3.5 dark:border-amber-500/20 dark:bg-amber-500/10">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>
            <div>
                <p class="text-sm font-bold text-amber-700 dark:text-amber-300">CONSIDER</p>
                <p class="text-xs text-amber-600/80 dark:text-amber-400/80">15&ndash;19</p>
            </div>
        </div>
        <div class="flex items-start gap-3 rounded-2xl border border-rose-100 bg-rose-50/60 p-3.5 dark:border-rose-500/20 dark:bg-rose-500/10">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-500 text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </span>
            <div>
                <p class="text-sm font-bold text-rose-700 dark:text-rose-300">NOT SUITABLE</p>
                <p class="text-xs text-rose-600/80 dark:text-rose-400/80">0&ndash;14</p>
            </div>
        </div>
    </div>
    </div>
</form>

@include('pages.careers.assessmentresult', [
    'suffix' => 'HC',
    'assessmentType' => 'hc',
    'hasScoreAssessment' => (bool) ($tr_assessment && $tr_assessment->assessment_date),
    'tr_assessment_result' => $tr_assessment_result_hc,
    'tr_assessment_results_other' => $tr_assessment_results_other_hc,
])

<script>
    const totalGroups = {{ count($assessmentGroups) }};
    let scoreValues = Array(totalGroups).fill(0);
    let hasFilledHC = {{ $tr_assessment && $tr_assessment->assessment_date ? 'true' : 'false' }};
    let isEditingHC = false;
    let isSectionCollapsedHC = false;
    const currentUserNameHC = @json($user->name);

    function toggleSectionHC() {
        isSectionCollapsedHC = !isSectionCollapsedHC;
        document.getElementById('hcSectionBody').style.display = isSectionCollapsedHC ? 'none' : 'block';
        document.getElementById('toggleIconHC').style.transform = isSectionCollapsedHC ? 'rotate(-90deg)' : 'rotate(0deg)';
    }

    // Snapshot of the last-saved state, used to restore the form when editing is cancelled.
    let originalCheckedHC = [];
    let originalDateHC = '';
    let originalTimeHC = '';

    function captureOriginalHC() {
        const form = document.getElementById('assessmentFormHC');
        originalCheckedHC = Array.from(form.querySelectorAll('input[type="radio"]:checked')).map((radio) => ({
            groupIndex: parseInt(radio.name.match(/\d+/)[0]),
            value: radio.value,
        }));
        originalDateHC = form.querySelector('input[name="interview_date"]').value;
        originalTimeHC = form.querySelector('input[name="interview_time"]').value;
    }

    function computeResultLabelHC(total) {
        if (total < 15) return 'NOT SUITABLE';
        if (total <= 19) return 'CONSIDER';
        if (total <= 28) return 'SUITABLE';
        return '-';
    }

    function resultBadgeClassesHC(result) {
        const base = 'inline-flex items-center rounded-full px-3 py-1 text-sm font-bold';
        if (result === 'SUITABLE') return `${base} bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300`;
        if (result === 'CONSIDER') return `${base} bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300`;
        if (result === 'NOT SUITABLE') return `${base} bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300`;
        return `${base} bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400`;
    }

    function recalcTotalsHC() {
        const total = scoreValues.reduce((sum, val) => sum + parseInt(val || 0), 0);
        document.getElementById('totalScoreHC').textContent = total;
        document.getElementById('totalScoreHCInput').value = total;

        const result = computeResultLabelHC(total);
        const badge = document.getElementById('resultTextHC');
        badge.textContent = result;
        badge.className = resultBadgeClassesHC(result);
        document.getElementById('resultStatusHC').value = result;

        const bar = document.getElementById('totalBarHC');
        if (bar) {
            bar.style.width = Math.min(100, Math.round(total / 28 * 100)) + '%';
            bar.className = 'h-full rounded-full transition-all duration-500 ' +
                (result === 'SUITABLE' ? 'bg-emerald-500' : (result === 'CONSIDER' ? 'bg-amber-500' : 'bg-rose-500'));
        }
    }

    function setScoreCellHC(groupIndex, value) {
        const cell = document.getElementById(`scoreCellHC-${groupIndex}`);
        if (value === null || value === undefined) {
            cell.textContent = '–';
            cell.className = 'score-badge inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gray-100 text-base font-bold text-gray-400 dark:bg-gray-800 dark:text-gray-500';
        } else {
            cell.textContent = value;
            cell.className = 'score-badge inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-base font-bold text-white shadow-sm shadow-indigo-500/30';
        }
    }

    function applySelectionHC(groupIndex, value) {
        const form = document.getElementById('assessmentFormHC');
        form.querySelectorAll(`input[name="scores[${groupIndex}]"]`).forEach((radio) => {
            const label = radio.closest('label');
            const check = label.querySelector('.score-check');
            const isSelected = value !== null && value !== undefined && String(radio.value) === String(value);

            // Highlight styling lives in CSS (.asm-opt[data-selected="1"]).
            label.dataset.selected = isSelected ? '1' : '0';

            check.classList.toggle('hidden', !isSelected);
            check.classList.toggle('flex', isSelected);
        });
    }

    function setEditModeHC(editing) {
        const assessmentForm = document.getElementById('assessmentFormHC');
        const hasCriteria = assessmentForm.querySelector('input[type="radio"]') !== null;
        editing = editing && hasCriteria;
        if (editing) {
            const jakartaNow = new Date(Date.now() + 7 * 60 * 60 * 1000);
            const dateValue = jakartaNow.toISOString().slice(0, 10);
            const timeValue = jakartaNow.toISOString().slice(11, 16);
            const dateInput = assessmentForm.querySelector('input[name="interview_date"]');
            const timeInput = assessmentForm.querySelector('input[name="interview_time"]');
            if (!hasFilledHC || !dateInput.value) dateInput.value = dateValue;
            if (!hasFilledHC || !timeInput.value) timeInput.value = timeValue;
        }
        if (editing && isSectionCollapsedHC) {
            toggleSectionHC();
        }
        isEditingHC = editing;
        const form = document.getElementById('assessmentFormHC');
        const inputs = form.querySelectorAll('input[type="radio"], input[type="date"], input[type="time"]');
        inputs.forEach((el) => {
            el.disabled = !editing;
        });
        form.querySelectorAll('.assessment-option-label').forEach((label) => {
            const radio = label.querySelector('input[type="radio"]');
            const isChecked = radio && radio.checked;
            label.classList.toggle('cursor-pointer', editing);
            label.classList.toggle('cursor-not-allowed', !editing && !isChecked);
            label.classList.toggle('opacity-50', !editing && !isChecked);
        });

        document.getElementById('infoHeaderHC').style.display = (editing || hasFilledHC) ? 'grid' : 'none';
        if (editing) {
            document.getElementById('namaInterviewHC').textContent = currentUserNameHC;
        }

        const btn = document.getElementById('actionBtnHC');
        btn.style.display = hasCriteria ? 'inline-flex' : 'none';
        const hint = document.getElementById('editHintHC');
        document.getElementById('cancelBtnHC').style.display = editing ? 'inline-flex' : 'none';
        if (editing) {
            btn.textContent = 'Save';
            hint.textContent = 'Fill in the scores below, then click Save.';
        } else {
            btn.textContent = hasFilledHC ? 'Update' : 'Fill';
            hint.textContent = !hasCriteria ? 'Interview criteria are not available yet.' : (hasFilledHC ? '' : 'Click Fill to start scoring this interview.');
        }
    }

    function handleActionHC() {
        if (!isEditingHC) {
            setEditModeHC(true);
        } else {
            $('#assessmentFormHC').trigger('submit');
        }
    }

    function cancelEditHC() {
        const form = document.getElementById('assessmentFormHC');

        form.querySelectorAll('input[type="radio"]').forEach((radio) => {
            radio.checked = false;
        });
        scoreValues = Array(totalGroups).fill(0);
        for (let i = 0; i < totalGroups; i++) {
            setScoreCellHC(i, null);
            applySelectionHC(i, null);
        }

        originalCheckedHC.forEach(({ groupIndex, value }) => {
            const radio = form.querySelector(`input[name="scores[${groupIndex}]"][value="${value}"]`);
            if (radio) radio.checked = true;
            scoreValues[groupIndex] = value;
            setScoreCellHC(groupIndex, value);
            applySelectionHC(groupIndex, value);
        });

        form.querySelector('input[name="interview_date"]').value = originalDateHC;
        form.querySelector('input[name="interview_time"]').value = originalTimeHC;

        recalcTotalsHC();
        setEditModeHC(false);
    }

    function updateScoreHC(groupIndex, value) {
        scoreValues[groupIndex] = value;
        setScoreCellHC(groupIndex, value);
        applySelectionHC(groupIndex, value);
        recalcTotalsHC();
    }
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const radios = document.querySelectorAll('#assessmentFormHC input[type="radio"]:checked');

        radios.forEach((radio) => {
            const groupIndex = parseInt(radio.name.match(/\d+/)[0]);
            const score = parseInt(radio.value);
            scoreValues[groupIndex] = score;
            setScoreCellHC(groupIndex, score);
            applySelectionHC(groupIndex, score);
        });

        recalcTotalsHC();
        captureOriginalHC();
        setEditModeHC(false);
    });
</script>
<script>
    $('#assessmentFormHC').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            url: '{{ route('assessment.update') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                toastr.success("Assessment HC berhasil diperbarui!");
                hasFilledHC = true;
                captureOriginalHC();
                setEditModeHC(false);
                if (typeof unlockResultHC === 'function') unlockResultHC();
            },
            error: function(xhr) {
                alert('Gagal menyimpan assessment: ' + xhr.responseText);
            }
        });
    });
</script>
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
