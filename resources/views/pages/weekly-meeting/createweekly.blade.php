<x-app-layout>
    <div class="mx-auto w-full max-w-9xl p-3">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 px-6 py-6 text-white">
                <div class="pointer-events-none absolute -right-10 -top-20 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex items-center gap-4">
                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-extrabold">Add Weekly Meeting</h1>
                            <span class="rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide ring-1 ring-white/20">New Meeting</span>
                        </div>
                        <p class="mt-1 text-sm text-indigo-100">Complete meeting information and attendance, then continue to findings.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('weekly-meeting.store') }}">
                @csrf
                <input type="hidden" name="cpny_id" value="{{ $companyId }}">
                <div class="space-y-5 bg-gray-50/70 p-6 dark:bg-white/[0.02]">
                    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-5 flex items-center gap-3 border-b pb-4 dark:border-gray-700">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5V4a2 2 0 0 1 2-2h12v20H6a2 2 0 0 1-2-2.5Z"/><path d="M8 7h6M8 11h6"/></svg>
                            </span>
                            <div>
                                <h2 class="font-extrabold text-gray-800 dark:text-white">Meeting Information</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Topic, schedule, and meeting time.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-sm font-bold text-gray-700 dark:text-gray-200">Topic <span class="text-red-500">*</span></label>
                                <input type="text" name="weeklymeeting_topic" value="{{ old('weeklymeeting_topic', 'Meeting Koordinasi Rutin') }}"
                                    class="meeting-input w-full rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-600 dark:bg-gray-700" required>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-bold text-gray-700 dark:text-gray-200">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="weeklymeeting_date" value="{{ old('weeklymeeting_date', $defaultDate) }}"
                                    class="meeting-input w-full rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-600 dark:bg-gray-700" required>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-bold text-gray-700 dark:text-gray-200">Time <span class="text-red-500">*</span></label>
                                <input type="time" name="meeting_time" value="{{ old('meeting_time', $defaultTime) }}"
                                    class="meeting-input w-full rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-600 dark:bg-gray-700" required>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex flex-col gap-3 border-b pb-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="font-extrabold text-gray-800 dark:text-white">Attendance</h2>
                                        <span id="participantCount" class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">0</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Copied from the latest meeting when available.</p>
                                </div>
                            </div>
                            <button type="button" id="btnAddParticipant"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                Add Participant
                            </button>
                        </div>
                        <div id="participantRows" class="space-y-3"></div>
                    </section>

                    @if ($errors->any())
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <div>• {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 border-t bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <a href="{{ route('weekly-meeting') }}"
                        class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-700 hover:shadow">
                        Save &amp; Next
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .meeting-input {
                transition: border-color .2s ease, box-shadow .2s ease;
            }
            .meeting-input:focus {
                border-color: #818cf8;
                outline: none;
                box-shadow: 0 0 0 3px rgb(99 102 241 / .12);
            }
            #participantRows .select2-container .select2-selection--single {
                height: 46px;
                border-color: #e2e8f0;
                border-radius: .75rem;
                padding: .5rem .75rem;
            }
            #participantRows .select2-selection__rendered {
                line-height: 28px;
            }
            #participantRows .select2-selection__arrow {
                height: 44px;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(function () {
                @if (session('approval_error'))
                    Swal.fire({
                        icon: 'warning',
                        title: 'Approval Belum Tersedia',
                        text: @json(session('approval_error')),
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#4f46e5'
                    });
                @endif

                const users = @json($users);
                const initial = @json(old('participants', $participants->all()));
                const options = selected => `<option value="">Select Participant</option>` + users.map(user =>
                    `<option value="${$('<div>').text(user.username).html()}" ${user.username === selected ? 'selected' : ''}>${$('<div>').text(user.name).html()} (${$('<div>').text(user.username).html()})</option>`
                ).join('');

                function updateParticipantCount() {
                    $('#participantCount').text($('#participantRows .participant-row').length);
                }

                function addParticipant(selected = '') {
                    const $row = $(`
                        <div class="participant-row flex items-center gap-2 rounded-xl border border-gray-100 bg-gray-50/70 p-2 dark:border-gray-700 dark:bg-gray-700/30">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-gray-400 shadow-sm dark:bg-gray-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <select name="participants[]" class="participant-select w-full">${options(selected)}</select>
                            </div>
                            <button type="button" class="btnRemoveParticipant inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-50 font-bold text-red-600 transition hover:bg-red-100">&times;</button>
                        </div>
                    `);
                    $('#participantRows').append($row);
                    $row.find('.participant-select').select2({
                        placeholder: 'Search participant...',
                        allowClear: true,
                        width: '100%'
                    });
                    updateParticipantCount();
                }

                initial.forEach(addParticipant);
                updateParticipantCount();
                $('#btnAddParticipant').on('click', () => addParticipant());
                $('#participantRows').on('click', '.btnRemoveParticipant', function () {
                    const $row = $(this).closest('.participant-row');
                    $row.find('.participant-select').select2('destroy');
                    $row.remove();
                    updateParticipantCount();
                });
            });
        </script>
    @endpush
</x-app-layout>
