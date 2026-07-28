<x-app-layout>
    <div class="mx-auto w-full max-w-5xl p-3">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b px-6 py-5 dark:border-gray-700">
                <h1 class="text-xl font-extrabold text-gray-800 dark:text-white">Add Weekly Meeting</h1>
                <p class="mt-1 text-sm text-gray-500">Complete meeting information and attendance, then continue to findings.</p>
            </div>

            <form method="POST" action="{{ route('weekly-meeting.store') }}">
                @csrf
                <input type="hidden" name="cpny_id" value="{{ $companyId }}">
                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-bold">Topic</label>
                        <input type="text" name="weeklymeeting_topic" value="{{ old('weeklymeeting_topic', 'Meeting Koordinasi Rutin') }}"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold">Date</label>
                        <input type="date" name="weeklymeeting_date" value="{{ old('weeklymeeting_date', $defaultDate) }}"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-bold">Time</label>
                        <input type="time" name="meeting_time" value="{{ old('meeting_time', $defaultTime) }}"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" required>
                    </div>

                    <div class="md:col-span-2">
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <h2 class="font-extrabold text-gray-800 dark:text-white">Attendance</h2>
                                <p class="text-xs text-gray-500">Copied from the latest meeting when available.</p>
                            </div>
                            <button type="button" id="btnAddParticipant"
                                class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-700">
                                + Participant
                            </button>
                        </div>
                        <div id="participantRows" class="space-y-3"></div>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-lg bg-red-50 p-4 text-sm text-red-700 md:col-span-2">
                            @foreach ($errors->all() as $error)
                                <div>• {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="flex justify-end gap-3 border-t px-6 py-4 dark:border-gray-700">
                    <a href="{{ route('weekly-meeting') }}" class="rounded-lg border px-4 py-2 text-sm font-bold">Cancel</a>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-bold text-white hover:bg-indigo-700">
                        Save &amp; Next
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                const users = @json($users);
                const initial = @json(old('participants', $participants->all()));
                const options = selected => `<option value="">Select Participant</option>` + users.map(user =>
                    `<option value="${$('<div>').text(user.username).html()}" ${user.username === selected ? 'selected' : ''}>${$('<div>').text(user.name).html()} (${ $('<div>').text(user.username).html() })</option>`
                ).join('');

                function addParticipant(selected = '') {
                    const $row = $(`
                        <div class="participant-row flex items-center gap-2">
                            <div class="min-w-0 flex-1">
                            <select name="participants[]" class="participant-select w-full rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                                ${options(selected)}
                            </select>
                            </div>
                            <button type="button" class="btnRemoveParticipant rounded-lg bg-red-50 px-3 py-2 font-bold text-red-600 hover:bg-red-100">&times;</button>
                        </div>
                    `);
                    $('#participantRows').append($row);
                    $row.find('.participant-select').select2({
                        placeholder: 'Search participant...',
                        allowClear: true,
                        width: '100%'
                    });
                }

                initial.forEach(addParticipant);
                $('#btnAddParticipant').on('click', () => addParticipant());
                $('#participantRows').on('click', '.btnRemoveParticipant', function () {
                    const $row = $(this).closest('.participant-row');
                    $row.find('.participant-select').select2('destroy');
                    $row.remove();
                });
            });
        </script>
    @endpush
</x-app-layout>
