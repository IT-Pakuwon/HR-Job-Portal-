<!-- Button -->
{{-- @if ($canAccessSchedule) --}}
<button type="button" onclick="openAgendaModal()"
    class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
    <i class="fas fa-plus pr-2"></i>Create Schedule
</button>
{{-- @endif --}}



<!-- Modal -->
<div id="agendaModal"
    style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="background-color: white; margin: 10% auto; padding: 20px; width: 700px; border-radius: 10px;">
        <h3 class="mb-3 font-semibold">Create Schedule</h3>
        <form id="createAgendaForm">
            @csrf
            <input type="hidden" name="refid" value="{{ $career->docid }}" />
            <input type="hidden" name="cpnyid" value="{{ $jobposting->cpnyid }}" />
            <input type="hidden" name="departementid" value="{{ $jobposting->departementid }}" />

            <div class="flex w-full gap-8">
                <div class="flex w-full flex-col">
                    <label>Title</label>
                    <input type="text" name="title" required class="form-control"
                        style="width: 100%; margin-bottom: 10px;" />
                </div>
            </div>
            <label>Description</label>
            <textarea name="description" required class="form-control" style="width: 100%; margin-bottom: 10px;"></textarea>

            <div class="flex w-full gap-8">
                <div class="flex w-1/2 flex-col">
                    <label>Start Date</label>
                    <input type="datetime-local" name="startdate" required class="form-control"
                        style="width: 100%; margin-bottom: 10px;" />
                </div>
                <div class="flex w-1/2 flex-col">
                    <label>End Date</label>
                    <input type="datetime-local" name="enddate" required class="form-control"
                        style="width: 100%; margin-bottom: 10px;" />
                </div>
            </div>

            <div class="flex w-full gap-8">
                <div class="flex w-1/2 flex-col">
                    <label>Type</label>
                    <select
                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                        name="reftype" style="width: 100%; margin-bottom: 16px;" required>
                        {{-- @foreach ($typestep as $p)
                            <option value="{{ $p->step_id }}">{{ $p->step_descr }}</option>
                        @endforeach --}}
                        <option value="">Select</option>
                        <option value="Interview User">Interview User</option>
                        <option value="Interview HC">Interview HC</option>
                        <option value="Interview HC & User">Interview HC & User</option>
                    </select>
                </div>
                <div class="flex w-1/2 flex-col">
                    <label>Site</label>
                    <select name="site" id="siteDropdown" required
                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                        style="width: 100%; margin-bottom: 16px;">
                        <option value="">-- Select Site --</option>
                        @foreach ($companyaddress as $site)
                            <option value="{{ $site->site }}">{{ $site->site }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex w-full gap-8">
                <div class="flex w-full flex-col">
                    <label>Location</label>
                    <input type="text" name="location" id="locationField" readonly
                        class="form-control cursor-not-allowed bg-gray-100" style="width: 100%; margin-bottom: 10px;" />
                </div>
            </div>

            <div class="flex w-full gap-8">
                <div class="flex w-full flex-col">
                    <label>Address</label>
                    <textarea name="location_address" id="addressField" readonly class="form-control cursor-not-allowed bg-gray-100"
                        style="width: 100%; margin-bottom: 10px;"></textarea>
                </div>
            </div>

            <label>Participant</label>
            <select
                class="select2 w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                name="participant[]" multiple style="width: 100%; margin-bottom: 16px;" required>
                @foreach ($userlist as $p)
                    <option value="{{ $p->username }}">{{ $p->name }}</option>
                @endforeach
            </select>

            <div style="margin-top: 10px;">
                {{-- <button type="submit"
                    style="background-color: #22c55e; color: white; padding: 6px 12px; border: none; border-radius: 5px;">Save</button> --}}
                <button id="createAgendaSubmit" type="submit"
                    style="background-color:#22c55e;color:#fff;padding:6px 12px;border:none;border-radius:5px;">
                    Save
                </button>

                <button type="button" onclick="closeAgendaModal()"
                    style="background-color: #e5e7eb; color: #374151; padding: 6px 12px; border: none; border-radius: 5px; margin-left: 10px;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
<div id="cancelModal"
    style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div style="background: white; padding: 20px; width: 400px; margin: 15% auto; border-radius: 10px;">
        <h3>Cancel Schedule</h3>
        <form id="cancelForm">
            @csrf
            <input type="hidden" name="agenda_id" id="cancel_agenda_id">
            <label>Reason for Cancellation</label>
            <textarea name="reason" required style="width: 100%; margin: 10px 0;"></textarea>
            <button type="submit"
                style="background-color: #ef4444; color: white; padding: 6px 12px; border: none; border-radius: 5px;">Submit</button>
            <button type="button" onclick="closeCancelModal()" style="margin-left: 10px;">Close</button>
        </form>
    </div>
</div>


<!-- Table -->
<table class="w-full text-xs">
    <thead class="bg-gray-50 dark:bg-gray-700">
        <tr class="text-gray-600 dark:text-gray-700">
            {{-- <th>DocID</th> --}}
            <th>Title</th>
            <th>Description</th>
            <th>StartDate</th>
            <th>EndDate</th>
            <th>Participant</th>
            {{-- <th>Status</th> --}}
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($agenda as $p)
            <tr>
                {{-- <td>
                    <a href="{{ url('/showagendas/' . $p->id) }}" target="_blank"
                        style="background-color: #6366f1; color: white; padding: 4px 12px; border-radius: 6px; font-weight: bold; display: inline-block; text-decoration: none;">
                        {{ $p->docid }}
                    </a>
                </td> --}}
                <td>{{ $p->title }}</td>
                <td>{{ $p->description }}</td>
                <td>{{ $p->startdate }}</td>
                <td>{{ $p->enddate }}</td>
                <td>{{ $p->participant }}</td>
                {{-- <td>
                    @php
                        $statusText = '';
                        $bgColor = '';
                        $textColor = '';

                        switch ($p->status) {
                            case 'P':
                                $statusText = 'On Progress';
                                $bgColor = '#fef08a'; // kuning
                                $textColor = '#92400e';
                                break;
                            case 'C':
                                $statusText = 'Completed';
                                $bgColor = '#bbf7d0'; // hijau muda
                                $textColor = '#166534';
                                break;
                            case 'R':
                                $statusText = 'Rejected';
                                $bgColor = '#fecaca'; // merah muda
                                $textColor = '#991b1b';
                                break;
                            case 'X':
                                $statusText = 'Cancelled';
                                $bgColor = '#fecaca';
                                $textColor = '#991b1b';
                                break;
                            default:
                                $statusText = ucfirst($p->status);
                                $bgColor = '#e5e7eb';
                                $textColor = '#374151';
                        }
                    @endphp

                    <span
                        style="background-color: {{ $bgColor }}; color: {{ $textColor }}; padding: 4px 10px; border-radius: 6px; font-weight: bold; font-size: 13px;">
                        {{ $statusText }}
                    </span>
                </td> --}}
                <td>
                    @if ($p->status == 'C')
                        <button onclick="openCancelModal({{ $p->id }})"
                            style="padding: 4px 10px; background-color: #f87171; color: white; border: none; border-radius: 4px;">Cancel</button>
                    @endif
                    {{ $p->agenda_note }}
                </td>

            </tr>
        @endforeach
    </tbody>
</table>

<!-- Script -->
<script>
    function openAgendaModal() {
        document.getElementById('agendaModal').style.display = 'block';
    }

    function closeAgendaModal() {
        document.getElementById('agendaModal').style.display = 'none';
    }

    // $('#createAgendaForm').on('submit', function(e) {
    //     e.preventDefault();
    //     $.ajax({
    //         url: '{{ route('agendas.store') }}',
    //         method: 'POST',
    //         data: $(this).serialize(),
    //         success: function(response) {
    //             // alert('Agenda berhasil dibuat!');
    //             toastr.success("Schedule created successfully");
    //             location.reload();
    //         },
    //         error: function(xhr) {
    //             alert('Gagal membuat agenda: ' + xhr.responseText);
    //         }
    //     });
    // });
</script>

<script>
    // pastikan tidak ada handler ganda jika partial ini di-load ulang
    $(document).off('submit', '#createAgendaForm');

    let isCreatingAgenda = false;

    $(document).on('submit', '#createAgendaForm', function(e) {
        e.preventDefault();

        if (isCreatingAgenda) return; // guard anti-double submit
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
                // Optional: tutup modal & reset form
                $('#agendaModal').hide();
                $('#createAgendaForm')[0].reset();
                // refresh list
                location.reload();
            },
            error: function(xhr) {
                toastr.error('Failed to create schedule');
                console.error(xhr.responseText);
            },
            complete: function() {
                // aktifkan kembali tombol & reset flag
                isCreatingAgenda = false;
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
</script>


<script>
    function openCancelModal(id) {
        $('#cancel_agenda_id').val(id);
        $('#cancelModal').show();
    }

    function closeCancelModal() {
        $('#cancelModal').hide();
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
</script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#agendaModal') // ⬅️ ini penting!
        });
    });
</script>

<script>
    $(document).ready(function() {
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

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
