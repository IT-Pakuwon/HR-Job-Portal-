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
    class="fixed inset-0 z-9999 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008Z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Create Schedule</h3>
                    <p class="text-[11px] text-gray-400">Set up an interview or event for this applicant</p>
                </div>
            </div>
            <button type="button" onclick="closeAgendaModal()"
                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none dark:hover:bg-gray-700 dark:hover:text-gray-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="createAgendaForm" class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
            @csrf
            <input type="hidden" name="refid" value="{{ $career->docid }}" />
            <input type="hidden" name="cpnyid" value="{{ $jobposting->cpnyid }}" />
            <input type="hidden" name="departementid" value="{{ $jobposting->departementid }}" />

            {{-- Details --}}
            <div class="space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Details</p>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Title</label>
                    <input type="text" name="title" required placeholder="e.g. Interview with HR"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-900/40">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Description</label>
                    <textarea name="description" required rows="2" placeholder="Additional notes for this schedule"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-900/40"></textarea>
                </div>
            </div>

            {{-- When --}}
            <div class="space-y-4 border-t border-gray-100 pt-5 dark:border-gray-700/60">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">When</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Start Date</label>
                        <input type="datetime-local" name="startdate" required
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-900/40">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">End Date</label>
                        <input type="datetime-local" name="enddate" required
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-900/40">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Type</label>
                        <select name="reftype" required
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 transition focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-900/40">
                            <option value="">Select type</option>
                            <option value="IU">Interview User</option>
                            <option value="IH">Interview HC</option>
                            <option value="IHU">Interview HC & User</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Site</label>
                        <select name="site" id="siteDropdown" required
                            class="select2-site w-full">
                            <option value="">Select site</option>
                            @foreach ($companyaddress as $site)
                                <option value="{{ $site->site }}">
                                    {{ $site->site }}{{ $site->sitelocation ? ' (' . $site->sitelocation . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Where --}}
            <div class="space-y-4 border-t border-gray-100 pt-5 dark:border-gray-700/60">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Where</p>
                    <span class="text-[10px] italic text-gray-400">Auto-filled from selected site</span>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Location</label>
                    <input type="text" name="location" id="locationField" readonly
                        class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700/60 dark:text-gray-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Address</label>
                    <textarea name="location_address" id="addressField" readonly rows="2"
                        class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700/60 dark:text-gray-400"></textarea>
                </div>
            </div>

            {{-- Who --}}
            <div class="space-y-4 border-t border-gray-100 pt-5 dark:border-gray-700/60">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Who</p>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-600 dark:text-gray-300">Participant</label>
                    <select class="select2 w-full" name="participant[]" multiple required>
                        @foreach ($userlist as $p)
                            <option value="{{ $p->username }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        {{-- Footer --}}
        <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/60">
            <button type="button" onclick="closeAgendaModal()"
                class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                Cancel
            </button>
            <button id="createAgendaSubmit" type="submit" form="createAgendaForm"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Save
            </button>
        </div>
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
                    class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
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

<style>
    /* ===== Select2 Dark Mode (Participant multi-select) ===== */
    .dark .select2-container--default .select2-selection--multiple {
        background-color: #374151; /* gray-700 */
        border-color: #4b5563; /* gray-600 */
        color: #f9fafb; /* gray-50 */
    }

    .dark .select2-selection--multiple .select2-selection__rendered {
        color: #f9fafb;
    }

    /* Selected item (chip) */
    .dark .select2-selection--multiple .select2-selection__choice {
        background-color: #4b5563; /* gray-600 */
        border: 1px solid #6b7280; /* gray-500 */
        color: #f9fafb;
    }

    /* Remove (x) button */
    .dark .select2-selection__choice__remove {
        color: #d1d5db; /* gray-300 */
    }

    .dark .select2-selection__choice__remove:hover {
        color: #f87171; /* red-400 */
    }

    /* Search input inside the multi-select box */
    .dark .select2-search__field {
        color: #f9fafb;
    }

    .dark .select2-search__field::placeholder {
        color: #9ca3af; /* gray-400 */
    }

    /* Dropdown */
    .dark .select2-dropdown {
        background-color: #1f2933; /* gray-800 */
        border-color: #4b5563;
    }

    /* Dropdown options */
    .dark .select2-results__option {
        color: #e5e7eb;
    }

    /* Hovered option */
    .dark .select2-results__option--highlighted {
        background-color: #4b5563;
        color: #ffffff;
    }

    /* Selected option */
    .dark .select2-results__option[aria-selected="true"] {
        background-color: #374151;
    }
</style>

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

        $('.select2-site').select2({
            placeholder: "Select site",
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
