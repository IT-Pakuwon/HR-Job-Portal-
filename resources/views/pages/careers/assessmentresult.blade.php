{{--
    Shared "Final Assessment Result" widget (strengths / weaknesses / comment / pass-failed).
    Included from both assessmenthc.blade.php and assessmentuser.blade.php with a unique
    $suffix ('HC' or 'USER') and $assessmentType ('hc' or 'user') so each tab gets its own
    independent set of element ids AND only ever sees/saves rows for its own type —
    an update made on the User tab never surfaces on the HC tab or vice versa.
--}}
<div class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-700">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Final Assessment Result</p>
                <p id="resultHint{{ $suffix }}" class="text-sm italic text-gray-400"></p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="resultCancelBtn{{ $suffix }}" onclick="cancelEditResult{{ $suffix }}()" style="display:none"
                class="items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-500 transition hover:bg-gray-50 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700/40">
                Cancel
            </button>
            <button type="button" id="resultActionBtn{{ $suffix }}" onclick="handleActionResult{{ $suffix }}()"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add Assessment
            </button>
            <button type="button" id="resultToggleBtn{{ $suffix }}" onclick="toggleSectionResult{{ $suffix }}()" title="Collapse/expand"
                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-200">
                <svg id="resultToggleIcon{{ $suffix }}" class="h-4 w-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </button>
        </div>
    </div>

    <div id="resultContent{{ $suffix }}">
        {{-- ── Other reviewers' submitted results (read-only) ──────────── --}}
        @if ($tr_assessment_results_other->isNotEmpty())
            <div class="mt-4 space-y-2">
                <p class="text-sm font-bold uppercase tracking-widest text-gray-400">Other Reviewers ({{ $tr_assessment_results_other->count() }})</p>
                @foreach ($tr_assessment_results_other as $r)
                    <div class="rounded-lg border border-gray-100 bg-gray-50/60 p-3 dark:border-gray-700 dark:bg-gray-900/40">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $r->created_user_name }}</p>
                            @if ($r->assessment_result === 'PASS')
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-sm font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Pass</span>
                            @elseif ($r->assessment_result === 'FAILED')
                                <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-sm font-bold text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">Failed</span>
                            @endif
                        </div>
                        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-widest text-gray-400">Strengths</p>
                                <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300">{{ $r->assessment_strengths ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-bold uppercase tracking-widest text-gray-400">Weaknesses</p>
                                <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300">{{ $r->assessment_weaknesses ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-bold uppercase tracking-widest text-gray-400">Comment</p>
                                <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300">{{ $r->assessment_comment ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ── My own review ─────────────────────────────────────────────── --}}
        @if ($tr_assessment_results_other->isNotEmpty())
            <p class="mt-4 text-sm font-bold uppercase tracking-widest text-gray-400">My Review</p>
        @endif

        {{-- Read-only summary card, shown once saved and not currently being edited --}}
        <div id="resultViewCard{{ $suffix }}" style="display:none" class="mt-2 rounded-lg border border-indigo-100 bg-indigo-50/30 p-3 dark:border-indigo-500/20 dark:bg-indigo-500/5">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $user->name }}</p>
                <span id="resultViewBadge{{ $suffix }}"></span>
            </div>
            <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <p class="flex items-center gap-1 text-sm font-bold uppercase tracking-widest text-emerald-500 dark:text-emerald-400">Strengths</p>
                    <p id="resultViewStrengths{{ $suffix }}" class="mt-0.5 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300"></p>
                </div>
                <div>
                    <p class="flex items-center gap-1 text-sm font-bold uppercase tracking-widest text-amber-500 dark:text-amber-400">Weaknesses</p>
                    <p id="resultViewWeaknesses{{ $suffix }}" class="mt-0.5 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300"></p>
                </div>
                <div>
                    <p class="flex items-center gap-1 text-sm font-bold uppercase tracking-widest text-indigo-500 dark:text-indigo-400">Comment</p>
                    <p id="resultViewComment{{ $suffix }}" class="mt-0.5 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300"></p>
                </div>
            </div>
        </div>

        <form id="assessmentResultForm{{ $suffix }}" method="POST" class="mt-2">
            @csrf
            <input type="hidden" name="jobapply_id" value="{{ $career->docid }}">
            <input type="hidden" name="assessment_type" value="{{ $assessmentType }}">
            <input type="hidden" name="assessment_result" id="resultValue{{ $suffix }}" value="{{ $tr_assessment_result->assessment_result ?? '' }}">

            <div id="resultBody{{ $suffix }}" style="display:none" class="space-y-4 rounded-xl border border-gray-100 bg-gray-50/40 p-4 dark:border-gray-700 dark:bg-gray-900/30">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-100 border-l-4 border-l-emerald-300 bg-white p-3 dark:border-gray-700 dark:border-l-emerald-400 dark:bg-gray-900/60">
                        <label class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-widest text-emerald-500 dark:text-emerald-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V3a.75.75 0 0 1 .75-.75A2.25 2.25 0 0 1 16.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.6.245.492-.078 1.155-.63 1.155h-.008a4.288 4.288 0 0 1-3.62-1.98A5.99 5.99 0 0 1 1.5 15c0-1.126.279-2.187.771-3.119A4.29 4.29 0 0 1 5.904 9.75Z"/></svg>
                            Strengths
                        </label>
                        <textarea name="assessment_strengths" rows="3" disabled placeholder="Kekuatan / kelebihan kandidat..."
                            class="mt-2 w-full resize-none rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-relaxed text-gray-700 focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300 disabled:cursor-not-allowed disabled:border-transparent disabled:bg-transparent disabled:px-0 disabled:py-0 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ $tr_assessment_result->assessment_strengths ?? '' }}</textarea>
                    </div>
                    <div class="rounded-lg border border-gray-100 border-l-4 border-l-amber-300 bg-white p-3 dark:border-gray-700 dark:border-l-amber-400 dark:bg-gray-900/60">
                        <label class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-widest text-amber-500 dark:text-amber-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 15h2.25m8.024-9.75c.011.05.028.1.052.148.591 1.2.924 2.55.924 3.977a8.96 8.96 0 0 1-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713.518 1.972 1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398C20.613 14.547 19.833 15 19 15h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54M17.5 6.75a1.5 1.5 0 0 0-1.5-1.5H10.5V15a1.5 1.5 0 0 1-.283.879l-.774 1.075a.75.75 0 0 1-1.209 0l-.774-1.075A1.5 1.5 0 0 1 6.75 15V9.75H4.5"/></svg>
                            Weaknesses
                        </label>
                        <textarea name="assessment_weaknesses" rows="3" disabled placeholder="Kelemahan kandidat..."
                            class="mt-2 w-full resize-none rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-relaxed text-gray-700 focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300 disabled:cursor-not-allowed disabled:border-transparent disabled:bg-transparent disabled:px-0 disabled:py-0 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ $tr_assessment_result->assessment_weaknesses ?? '' }}</textarea>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-100 border-l-4 border-l-indigo-300 bg-white p-3 dark:border-gray-700 dark:border-l-indigo-400 dark:bg-gray-900/60">
                    <label class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-widest text-indigo-500 dark:text-indigo-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                        Comment
                    </label>
                    <textarea name="assessment_comment" rows="3" disabled placeholder="Catatan tambahan..."
                        class="mt-2 w-full resize-none rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-relaxed text-gray-700 focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-300 disabled:cursor-not-allowed disabled:border-transparent disabled:bg-transparent disabled:px-0 disabled:py-0 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ $tr_assessment_result->assessment_comment ?? '' }}</textarea>
                </div>

                <div class="rounded-lg border border-gray-100 bg-white p-3 dark:border-gray-700 dark:bg-gray-900/60">
                    <label class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0"/></svg>
                        Final Result
                    </label>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <button type="button" id="resultChoicePassBtn{{ $suffix }}" data-value="PASS" onclick="selectResult{{ $suffix }}('PASS')">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Pass
                        </button>
                        <button type="button" id="resultChoiceFailedBtn{{ $suffix }}" data-value="FAILED" onclick="selectResult{{ $suffix }}('FAILED')">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            Failed
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        const suffix = '{{ $suffix }}';
        const hasResultInitially = {{ $tr_assessment_result ? 'true' : 'false' }};

        window['hasResult' + suffix] = hasResultInitially;
        window['hasScoreAssessment' + suffix] = {{ $hasScoreAssessment ? 'true' : 'false' }};
        window['isEditingResult' + suffix] = false;
        window['isSectionCollapsedResult' + suffix] = false;
        window['originalResult' + suffix] = { strengths: '', weaknesses: '', comment: '', result: '' };

        window['toggleSectionResult' + suffix] = function() {
            const collapsed = !window['isSectionCollapsedResult' + suffix];
            window['isSectionCollapsedResult' + suffix] = collapsed;
            document.getElementById('resultContent' + suffix).style.display = collapsed ? 'none' : 'block';
            document.getElementById('resultToggleIcon' + suffix).style.transform = collapsed ? 'rotate(-90deg)' : 'rotate(0deg)';
        };

        function isLocked() {
            return !window['hasScoreAssessment' + suffix] && !window['hasResult' + suffix];
        }

        window['unlockResult' + suffix] = function() {
            window['hasScoreAssessment' + suffix] = true;
            window['setEditModeResult' + suffix](false);
        };

        function form() {
            return document.getElementById('assessmentResultForm' + suffix);
        }

        function fields() {
            const f = form();
            return {
                strengths: f.querySelector('textarea[name="assessment_strengths"]'),
                weaknesses: f.querySelector('textarea[name="assessment_weaknesses"]'),
                comment: f.querySelector('textarea[name="assessment_comment"]'),
                result: document.getElementById('resultValue' + suffix),
            };
        }

        function choiceClasses(optionValue, selectedValue, editing) {
            const base = 'flex flex-row items-center justify-center gap-2 rounded-lg border-2 px-4 py-2.5 text-sm font-bold transition-all duration-150';
            const isSelected = optionValue === selectedValue && selectedValue !== '';

            if (isSelected && optionValue === 'PASS') {
                return `${base} border-emerald-400 bg-emerald-50 text-emerald-700 shadow-sm dark:border-emerald-400 dark:bg-emerald-500/15 dark:text-emerald-300 ${editing ? 'cursor-pointer' : 'cursor-default'}`;
            }
            if (isSelected && optionValue === 'FAILED') {
                return `${base} border-rose-400 bg-rose-50 text-rose-700 shadow-sm dark:border-rose-400 dark:bg-rose-500/15 dark:text-rose-300 ${editing ? 'cursor-pointer' : 'cursor-default'}`;
            }

            if (editing) {
                return `${base} cursor-pointer border-gray-300 bg-white text-gray-600 hover:border-indigo-300 hover:bg-indigo-50/40 hover:text-indigo-600 dark:border-gray-600 dark:bg-gray-900/60 dark:text-gray-300 dark:hover:border-indigo-500 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300`;
            }
            return `${base} cursor-not-allowed border-dashed border-gray-200 bg-white text-gray-400 opacity-40 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-500`;
        }

        function renderChoices(editing) {
            const selectedValue = fields().result.value;
            document.getElementById('resultChoicePassBtn' + suffix).className = choiceClasses('PASS', selectedValue, editing);
            document.getElementById('resultChoiceFailedBtn' + suffix).className = choiceClasses('FAILED', selectedValue, editing);
        }

        function renderViewCard() {
            const f = fields();
            document.getElementById('resultViewStrengths' + suffix).textContent = f.strengths.value || '—';
            document.getElementById('resultViewWeaknesses' + suffix).textContent = f.weaknesses.value || '—';
            document.getElementById('resultViewComment' + suffix).textContent = f.comment.value || '—';

            const badge = document.getElementById('resultViewBadge' + suffix);
            if (f.result.value === 'PASS') {
                badge.textContent = 'Pass';
                badge.className = 'inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-sm font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300';
            } else if (f.result.value === 'FAILED') {
                badge.textContent = 'Failed';
                badge.className = 'inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-sm font-bold text-rose-700 dark:bg-rose-500/15 dark:text-rose-300';
            } else {
                badge.textContent = '';
                badge.className = '';
            }
        }

        window['selectResult' + suffix] = function(value) {
            if (!window['isEditingResult' + suffix]) return;
            fields().result.value = value;
            renderChoices(true);
        };

        window['captureOriginalResult' + suffix] = function() {
            const f = fields();
            window['originalResult' + suffix] = {
                strengths: f.strengths.value,
                weaknesses: f.weaknesses.value,
                comment: f.comment.value,
                result: f.result.value,
            };
        };

        window['setEditModeResult' + suffix] = function(editing) {
            const locked = isLocked();
            if (locked) editing = false;
            if (editing && window['isSectionCollapsedResult' + suffix]) {
                window['toggleSectionResult' + suffix]();
            }
            window['isEditingResult' + suffix] = editing;
            const hasResult = window['hasResult' + suffix];
            const f = fields();

            [f.strengths, f.weaknesses, f.comment].forEach((el) => { el.disabled = !editing; });
            renderChoices(editing);

            document.getElementById('resultBody' + suffix).style.display = editing ? 'block' : 'none';

            const showViewCard = hasResult && !editing;
            document.getElementById('resultViewCard' + suffix).style.display = showViewCard ? 'block' : 'none';
            if (showViewCard) renderViewCard();

            const btn = document.getElementById('resultActionBtn' + suffix);
            const hint = document.getElementById('resultHint' + suffix);
            document.getElementById('resultCancelBtn' + suffix).style.display = editing ? 'inline-flex' : 'none';
            btn.style.display = locked ? 'none' : 'inline-flex';

            if (locked) {
                hint.textContent = 'Complete the Interview Assessment scoring above before adding the final result.';
            } else if (editing) {
                btn.textContent = 'Save';
                hint.textContent = 'Fill in the details, then click Save.';
            } else if (hasResult) {
                btn.textContent = 'Update';
                hint.textContent = '';
            } else {
                btn.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg> Add Assessment';
                hint.textContent = 'Record the final strengths, weaknesses, and result here.';
            }
        };

        window['handleActionResult' + suffix] = function() {
            if (isLocked()) return;
            if (!window['isEditingResult' + suffix]) {
                window['setEditModeResult' + suffix](true);
            } else {
                if (!fields().result.value) {
                    toastr.error('Please select Pass or Failed before saving.');
                    return;
                }
                $(form()).trigger('submit');
            }
        };

        window['cancelEditResult' + suffix] = function() {
            const f = fields();
            const original = window['originalResult' + suffix];
            f.strengths.value = original.strengths;
            f.weaknesses.value = original.weaknesses;
            f.comment.value = original.comment;
            f.result.value = original.result;
            window['setEditModeResult' + suffix](false);
        };

        document.addEventListener('DOMContentLoaded', function() {
            window['captureOriginalResult' + suffix]();
            window['setEditModeResult' + suffix](false);
        });

        $(form()).on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: '{{ route('assessmentresult.update') }}',
                method: 'POST',
                data: formData,
                success: function() {
                    toastr.success('Assessment result saved successfully!');
                    window['hasResult' + suffix] = true;
                    window['captureOriginalResult' + suffix]();
                    window['setEditModeResult' + suffix](false);
                },
                error: function(xhr) {
                    alert('Gagal menyimpan assessment result: ' + xhr.responseText);
                }
            });
        });
    })();
</script>
