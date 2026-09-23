<form id="assessmentFormHC" method="POST">
    @csrf

    <input type="hidden" name="totalscore" id="totalScoreHCInput" value="0">
    <input type="hidden" name="result_status" id="resultStatusHC" value="NOT SUITABLE">
    <input type="hidden" name="docid" value="{{ $career->docid }}">

    {{-- ── Action bar ─────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-700/60">
        <div class="flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">HC Interview Assessment</p>
                <p id="editHintHC" class="text-sm italic text-gray-400"></p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="cancelBtnHC" onclick="cancelEditHC()" style="display:none"
                class="items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-500 transition hover:bg-gray-50 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700/40">
                Cancel
            </button>
            <button type="button" id="actionBtnHC" onclick="handleActionHC()"
                class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                Start Assessment
            </button>
            <button type="button" id="toggleBtnHC" onclick="toggleSectionHC()" title="Collapse/expand"
                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-200">
                <svg id="toggleIconHC" class="h-4 w-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </button>
        </div>
    </div>

    <div id="hcSectionBody">

    {{-- ── Info header ─────────────────────────────────────────────── --}}
    <div id="infoHeaderHC" style="display:none" class="grid grid-cols-2 gap-3 py-4 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-3 dark:border-gray-700 dark:bg-gray-900/50">
            <div class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-widest text-gray-400">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                Nama Interview
            </div>
            <p id="namaInterviewHC" class="mt-1 truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                {{ $tr_assessment && $tr_assessment->user ? $tr_assessment->user_name : $user->name }}
            </p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-3 dark:border-gray-700 dark:bg-gray-900/50">
            <div class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-widest text-gray-400">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                Tanggal &amp; Jam
            </div>
            <div class="mt-1 flex items-center gap-1.5">
                <input type="date" name="interview_date"
                    value="{{ $tr_assessment && $tr_assessment->assessment_date ? \Carbon\Carbon::parse($tr_assessment->assessment_date)->format('Y-m-d') : '' }}"
                    required disabled
                    class="rounded-md border border-gray-200 bg-white px-2 py-1 text-sm text-gray-700 focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <input type="time" name="interview_time"
                    value="{{ $tr_assessment && $tr_assessment->assessment_date ? \Carbon\Carbon::parse($tr_assessment->assessment_date)->format('H:i') : '' }}"
                    required disabled
                    class="rounded-md border border-gray-200 bg-white px-2 py-1 text-sm text-gray-700 focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>
        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-3 dark:border-gray-700 dark:bg-gray-900/50">
            <div class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-widest text-gray-400">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Standar Scoring
            </div>
            <p class="mt-1.5">
                <span id="resultTextHC" class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-sm font-bold text-gray-500 dark:bg-gray-800 dark:text-gray-400">NOT SUITABLE</span>
            </p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-3 dark:border-gray-700 dark:bg-gray-900/50">
            <div class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-widest text-gray-400">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                Jumlah Nilai
            </div>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                <span id="totalScoreHC">0</span><span class="text-sm font-medium text-gray-400"> / 28</span>
            </p>
        </div>
    </div>

    {{-- ── Scoring matrix ─────────────────────────────────────────── --}}
    <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
        <table class="w-full border-collapse text-left">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <th class="w-1/6 py-3 pl-5 pr-3 text-left text-sm font-bold uppercase tracking-widest text-gray-400">Kriteria</th>
                    @php $scoreHeaders = $assessmentGroups[0]['options'] ?? []; @endphp
                    @foreach ($scoreHeaders as $option)
                        <th class="px-2 py-3 text-center text-sm font-bold uppercase tracking-widest text-gray-400">Skor {{ $option['assessment_score'] }}</th>
                    @endforeach
                    <th class="w-16 py-3 pl-2 pr-5 text-center text-sm font-bold uppercase tracking-widest text-gray-400">Nilai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($assessmentGroups as $groupIndex => $group)
                    @php $selectedScore = $group['selected_score']; @endphp
                    <tr class="align-top hover:bg-gray-50/60 dark:hover:bg-white/[0.03]">
                        <td class="py-3 pl-5 pr-3 align-middle text-sm font-semibold text-gray-800 dark:text-gray-100">
                            {{ $group['assessment_group'] }}
                        </td>
                        @foreach ($group['options'] as $score => $option)
                            <td class="p-1.5 align-top">
                                <label class="assessment-option-label group relative flex h-full min-h-[64px] flex-col items-center justify-center gap-1 rounded-lg border border-gray-200 bg-white p-2 text-center transition-all duration-150 hover:border-indigo-300 hover:bg-indigo-50/40 dark:border-gray-700 dark:bg-gray-900/60 dark:hover:border-indigo-500 dark:hover:bg-indigo-500/10">
                                    <span class="pointer-events-none text-sm leading-snug text-gray-500 transition-colors dark:text-gray-400">{{ $option['assessment_descr'] }}</span>
                                    <input type="radio" name="scores[{{ $groupIndex }}]" value="{{ $score }}"
                                        {{ $selectedScore !== null && $selectedScore == $score ? 'checked' : '' }}
                                        disabled class="sr-only"
                                        onclick="updateScoreHC({{ $groupIndex }}, {{ $score }})">
                                    <span class="score-check pointer-events-none absolute -top-1.5 -right-1.5 hidden h-4 w-4 items-center justify-center rounded-full bg-indigo-600 text-white shadow ring-2 ring-white dark:ring-gray-900">
                                        <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </span>
                                </label>
                            </td>
                        @endforeach
                        <td class="py-3 pl-2 pr-5 text-center align-middle">
                            @if ($selectedScore !== null)
                                <span id="scoreCellHC-{{ $groupIndex }}" class="score-badge inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white shadow-sm">{{ $selectedScore }}</span>
                            @else
                                <span id="scoreCellHC-{{ $groupIndex }}" class="score-badge inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-400 dark:bg-gray-800 dark:text-gray-500">&ndash;</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Standard scoring legend ────────────────────────────────── --}}
    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
        <div class="flex items-start gap-2 rounded-lg bg-emerald-50/60 p-3 dark:bg-emerald-500/10">
            <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-emerald-500"></span>
            <div>
                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">SUITABLE</p>
                <p class="text-sm text-emerald-600/80 dark:text-emerald-400/80">21&ndash;28 (Manager/Supervisor) &middot; 20&ndash;25 (Staff)</p>
            </div>
        </div>
        <div class="flex items-start gap-2 rounded-lg bg-amber-50/60 p-3 dark:bg-amber-500/10">
            <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-amber-500"></span>
            <div>
                <p class="text-sm font-bold text-amber-700 dark:text-amber-300">CONSIDER</p>
                <p class="text-sm text-amber-600/80 dark:text-amber-400/80">15&ndash;19</p>
            </div>
        </div>
        <div class="flex items-start gap-2 rounded-lg bg-rose-50/60 p-3 dark:bg-rose-500/10">
            <span class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-rose-500"></span>
            <div>
                <p class="text-sm font-bold text-rose-700 dark:text-rose-300">NOT SUITABLE</p>
                <p class="text-sm text-rose-600/80 dark:text-rose-400/80">0&ndash;14</p>
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
        const base = 'inline-flex items-center rounded-full px-2.5 py-1 text-sm font-bold';
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
    }

    function setScoreCellHC(groupIndex, value) {
        const cell = document.getElementById(`scoreCellHC-${groupIndex}`);
        if (value === null || value === undefined) {
            cell.textContent = '–';
            cell.className = 'score-badge inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-400 dark:bg-gray-800 dark:text-gray-500';
        } else {
            cell.textContent = value;
            cell.className = 'score-badge inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white shadow-sm';
        }
    }

    function applySelectionHC(groupIndex, value) {
        const form = document.getElementById('assessmentFormHC');
        form.querySelectorAll(`input[name="scores[${groupIndex}]"]`).forEach((radio) => {
            const label = radio.closest('label');
            const check = label.querySelector('.score-check');
            const descr = label.querySelector('span:not(.score-check)');
            const isSelected = value !== null && value !== undefined && String(radio.value) === String(value);

            label.classList.toggle('border-indigo-400', isSelected);
            label.classList.toggle('bg-indigo-50', isSelected);
            label.classList.toggle('ring-1', isSelected);
            label.classList.toggle('ring-indigo-300', isSelected);
            label.classList.toggle('dark:bg-indigo-500/15', isSelected);
            label.classList.toggle('dark:border-indigo-400', isSelected);
            label.classList.toggle('dark:ring-indigo-500/40', isSelected);

            check.classList.toggle('hidden', !isSelected);
            check.classList.toggle('flex', isSelected);

            if (descr) {
                descr.classList.toggle('text-indigo-700', isSelected);
                descr.classList.toggle('font-medium', isSelected);
                descr.classList.toggle('dark:text-indigo-300', isSelected);
            }
        });
    }

    function setEditModeHC(editing) {
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
        const hint = document.getElementById('editHintHC');
        document.getElementById('cancelBtnHC').style.display = editing ? 'inline-flex' : 'none';
        if (editing) {
            btn.textContent = 'Save';
            hint.textContent = 'Fill in the scores above, then click Save.';
        } else {
            btn.textContent = hasFilledHC ? 'Update' : 'Fill';
            hint.textContent = hasFilledHC ? '' : 'Click Fill to start scoring this interview.';
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
