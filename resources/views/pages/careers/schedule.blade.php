{{-- ── Header ─────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5 dark:border-gray-700/60">
    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Schedule</p>
    <button type="button" onclick="openAgendaModal()"
        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Create Schedule
    </button>
</div>

{{-- ── Table ──────────────────────────────────────────────────────── --}}
<div class="overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-700/60">
                <th class="px-5 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Title</th>
                <th class="px-3 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Description</th>
                <th class="px-3 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Start</th>
                <th class="px-3 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">End</th>
                <th class="px-3 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Participant</th>
                <th class="py-2.5 pl-3 pr-5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
            @forelse ($agenda as $p)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                    <td class="px-5 py-3 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $p->title }}</td>
                    <td class="px-3 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $p->description }}</td>
                    <td class="px-3 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $p->startdate }}</td>
                    <td class="px-3 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $p->enddate }}</td>
                    <td class="px-3 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $p->participant }}</td>
                    <td class="py-3 pl-3 pr-5 text-right">
                        @if ($p->status == 'C')
                            <button onclick="openCancelModal({{ $p->id }})"
                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-500 transition hover:border-red-300 hover:text-red-500 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400">
                                Cancel
                            </button>
                        @endif
                        @if($p->agenda_note)
                            <span class="text-xs text-gray-400">{{ $p->agenda_note }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-xs italic text-gray-400">No schedules yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ── Create Schedule Modal ──────────────────────────────────────── --}}
<div id="agendaModal" style="display:none;"
    class="fixed inset-0 z-9999 flex items-center justify-center bg-black/40">
    <div class="w-full max-w-xl rounded-lg bg-white shadow-xl dark:bg-gray-800">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Create Schedule</h3>
            <button type="button" onclick="closeAgendaModal()" class="text-gray-400 transition hover:text-gray-600 focus:outline-none">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="createAgendaForm" class="space-y-4 p-5">
            @csrf
            <input type="hidden" name="refid" value="{{ $career->docid }}" />
            <input type="hidden" name="cpnyid" value="{{ $jobposting->cpnyid }}" />
            <input type="hidden" name="departementid" value="{{ $jobposting->departementid }}" />

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Title</label>
                <input type="text" name="title" required
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Description</label>
                <textarea name="description" required rows="2"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Start Date</label>
                    <input type="datetime-local" name="startdate" required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">End Date</label>
                    <input type="datetime-local" name="enddate" required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Type</label>
                    <select name="reftype" required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select type</option>
                        <option value="IU">Interview User</option>
                        <option value="IH">Interview HC</option>
                        <option value="IHU">Interview HC & User</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Site</label>
                    <select name="site" id="siteDropdown" required
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select site</option>
                        @foreach ($companyaddress as $site)
                            <option value="{{ $site->site }}">{{ $site->site }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Location</label>
                <input type="text" name="location" id="locationField" readonly
                    class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Address</label>
                <textarea name="location_address" id="addressField" readonly rows="2"
                    class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400"></textarea>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Participant</label>
                <select class="select2 w-full" name="participant[]" multiple required>
                    @foreach ($userlist as $p)
                        <option value="{{ $p->username }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                <button type="button" onclick="closeAgendaModal()"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-400">
                    Cancel
                </button>
                <button id="createAgendaSubmit" type="submit"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Cancel Schedule Modal ──────────────────────────────────────── --}}
<div id="cancelModal" style="display:none;"
    class="fixed inset-0 z-99999 flex items-center justify-center bg-black/40">
    <div class="w-full max-w-sm rounded-lg bg-white shadow-xl dark:bg-gray-800">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Cancel Schedule</h3>
        </div>
        <form id="cancelForm" class="p-5">
            @csrf
            <input type="hidden" name="agenda_id" id="cancel_agenda_id">
            <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Reason for Cancellation</label>
            <textarea name="reason" required rows="3"
                class="mt-1 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeCancelModal()"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-400">
                    Close
                </button>
                <button type="submit"
                    class="rounded-lg bg-red-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-600 focus:outline-none active:scale-95">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAgendaModal() {
        document.getElementById('agendaModal').style.display = 'flex';
    }
    function closeAgendaModal() {
        document.getElementById('agendaModal').style.display = 'none';
    }

    $(document).off('submit', '#createAgendaForm');
    let isCreatingAgenda = false;

    $(document).on('submit', '#createAgendaForm', function(e) {
        e.preventDefault();
        if (isCreatingAgenda) return;
        isCreatingAgenda = true;

        const $btn = $('#createAgendaSubmit');
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '{{ route('agendas.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                toastr.success('Schedule created successfully');
                closeAgendaModal();
                $('#createAgendaForm')[0].reset();
                location.reload();
            },
            error: function(xhr) {
                toastr.error('Failed to create schedule');
                console.error(xhr.responseText);
            },
            complete: function() {
                isCreatingAgenda = false;
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });

    function openCancelModal(id) {
        $('#cancel_agenda_id').val(id);
        document.getElementById('cancelModal').style.display = 'flex';
    }
    function closeCancelModal() {
        document.getElementById('cancelModal').style.display = 'none';
    }

    $('#cancelForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route('agendas.cancel') }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                toastr.success("Schedule cancelled successfully");
                location.reload();
            },
            error: function(xhr) {
                alert('Cancel failed: ' + xhr.responseText);
            }
        });
    });

    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select participants",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#agendaModal')
        });

        $('#siteDropdown').on('change', function() {
            const site = $(this).val();
            if (site) {
                $.ajax({
                    url: '/company-address/' + site,
                    type: 'GET',
                    success: function(data) {
                        if (data) {
                            $('#locationField').val(data.location);
                            $('#addressField').val(data.address2);
                        } else {
                            $('#locationField').val('');
                            $('#addressField').val('');
                        }
                    },
                    error: function() {
                        toastr.error('Failed to fetch site info');
                    }
                });
            } else {
                $('#locationField').val('');
                $('#addressField').val('');
            }
        });
    });
</script>
