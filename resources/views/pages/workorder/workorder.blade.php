<style>
    .no-border {
        border: none !important;
    }

    .grid {
        width: 100%;
    }

    select,
    textarea,
    input {
        width: 100%;
        /* Make all input elements take full width */
    }

    table.dataTable {
        width: 100% !important;
    }

    .dataTables_wrapper {
        width: 100%;
    }

    @media (max-width: 600px) {
        .dataTables_wrapper {
            padding: 0 10px;
        }
    }

    #tasksTable_filter {
        margin-bottom: 20px;
        display: flex;
        justify-content: flex-start;
        /* Aligns items to the left */
        align-items: center;
        /* Vertically aligns items */
    }

    #tasksTable_filter label {
        margin-right: 2px;
    }

    #tasksTable_filter input {
        width: 200px;
        /* Adjust the width of the input box */
    }


    #tasksTable_wrapper {
        margin-top: 20px;
        width: 100%;
    }

    /* Prevent text from wrapping */
    #tasksTable td {
        white-space: nowrap;
        /* Prevent text from wrapping */
        overflow: hidden;
        /* Hide overflow content */
        text-overflow: ellipsis;
        /* Display ellipsis ("...") for overflowing content */
    }

    /* Optional: Adjust the width for table cells */
    #tasksTable th,
    #tasksTable td {
        padding: 10px;
        /* Adjust padding for better appearance */
        max-width: 200px;
        /* You can set a maximum width to control overflow */
    }


    #tasksTable_length {
        width: auto;
        display: flex;
        justify-content: flex-start;
    }

    #tasksTable_length select {
        width: auto;
        padding: 5px;
        min-width: 80px;
    }

    #tasksTable_length select option {
        padding: 5px;
        /* Mengatur jarak antar opsi */
    }

    #tasksTable_info {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .dataTables_paginate {
        margin-top: 10px;
        margin-bottom: 10px;

    }

    #tasksTable tbody tr td {
        padding: 8px 8px;
        /* Adjust padding for uniform height */
        line-height: 2;
        /* Optional, for better text alignment */
    }

    #tasksTable tbody tr {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    #tasksTable tbody tr:hover {
        background-color: #8f8f8f11;
        opacity: 100%;
        cursor: pointer;
    }

    #tasksTable tbody tr:hover td {
        color: black;
    }
</style>
<div class="mt-6 overflow-y-auto rounded-xl bg-white p-4 dark:bg-gray-800">
    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
        <h1 class="align-middle text-2xl font-bold dark:text-white">Work Order</h1>
    </div>
    <div class="rounded-lg bg-white dark:bg-gray-800">
        <table id="tasksTable" class="mt-5 min-w-full rounded">
            <thead class="bg-white-200 dark:text-white">
                <tr>
                    <th class="w-32 px-4 py-3 text-left">DocID</th>
                    <th class="px-4 py-3 text-center">Date</th>
                    <th class="px-4 py-3 text-center">Type</th>
                    <th class="px-4 py-3 text-center">Summary</th>
                    <th class="px-4 py-3 text-center">StartDate</th>
                    <th class="px-4 py-3 text-center">DueDate</th>
                    <th class="px-4 py-3 text-center">Priority</th>
                    <th class="w-32 px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <!-- Modal Create WI -->
    <div id="createWiModal" class="fixed inset-0 z-10 hidden overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="w-full max-w-4xl overflow-hidden rounded-lg bg-white">
                <div class="border-b p-4">
                    <h2 class="text-xl font-semibold">Create Work Instruction</h2>
                </div>
                <div class="space-y-4 p-4">
                    <!-- Top Info Section -->
                    <div class="grid grid-cols-2 gap-4 rounded bg-gray-100 p-4 text-sm">
                        <div><strong>DocID:</strong> <span id="wi_docid"></span></div>
                        <div><strong>Task Date:</strong> <span id="wi_taskdate"></span></div>
                        <div><strong>Company:</strong> <span id="wi_cpnyid"></span></div>
                        <div><strong>Department:</strong> <span id="wi_departementid"></span></div>
                        <div><strong>Type:</strong> <span id="wi_tasktype"></span></div>
                        <div><strong>Priority:</strong> <span id="wi_taskpriority"></span></div>
                        <div class="col-span-2"><strong>Summary:</strong> <span id="wi_summary"></span></div>
                        <div class="col-span-2"><strong>Description:</strong> <span id="wi_description"></span></div>
                    </div>

                    <!-- Form Section -->
                    <form id="wiForm">
                        @csrf
                        <input type="hidden" name="work_id" id="work_id">
                        <div class="grid grid-cols-2 gap-4">
                            <select id="wo_priority" name="wo_priority" class="rounded border p-2" required>
                                <option value="">Select Priority</option>
                                <option value="High">High</option>
                                <option value="Low">Low</option>
                            </select>
                            <select name="complaint_type" id="complaint_type" class="rounded border p-2" required>
                                <option value="">Select Complaint Type</option>
                            </select>
                            <select name="work_type" id="work_type" class="rounded border p-2" required>
                                <option value="">Select WO Type</option>
                            </select>
                            <select name="sub_work_type" id="sub_work_type" class="rounded border p-2" required>
                                <option value="">Select Sub Work Type</option>
                            </select>
                            <select name="location_id" id="location_id" class="rounded border p-2" required>
                                <option value="">Select Location</option>
                            </select>
                            <select name="sub_location_id" id="sub_location_id" class="rounded border p-2" required>
                                <option value="">Select Sub Location</option>
                            </select>
                            {{-- <input type="date" name="work_start_date" id="work_start_date" class="border p-2 rounded" />
                <input type="date" name="work_end_date" id="work_end_date" class="border p-2 rounded" /> --}}
                            <textarea name="work_description" id="work_description" placeholder="Work Description"
                                class="col-span-2 rounded border p-2"></textarea>
                            <select name="workers[]" id="workers" class="select2 rounded border p-2" multiple
                                style="width: 100%; margin-bottom: 30px;" required>
                                <option value="">Select Workers</option>
                            </select>
                        </div>
                        <div class="mt-4 text-right">
                            <button type="submit" class="rounded bg-blue-500 px-4 py-2 text-white">Save</button>
                            <button type="button" class="ml-2 rounded bg-gray-300 px-4 py-2"
                                id="closeWiModal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var table = $('#tasksTable').DataTable({
            "order": [
                [0, "desc"]
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('assignwo') }}",

            },
            columns: [{
                    data: 'docid',
                    name: 'docid'
                },
                {
                    data: 'taskdate',
                    name: 'taskdate'
                },
                {
                    data: 'tasktype',
                    name: 'tasktype'
                },
                {
                    data: 'summary',
                    name: 'summary'
                },
                {
                    data: 'startdate',
                    name: 'startdate'
                },
                {
                    data: 'duedate',
                    name: 'duedate'
                },
                {
                    data: 'taskpriority',
                    name: 'taskpriority'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ],

            // dom:'lBfrtip',
            // buttons: ['excel', 'csv', 'pdf', 'copy'],
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            responsive: true,
        });
    })
</script>
<script>
    $(document).on('click', '.create-wi-btn', function() {
        const cpnyid = $(this).data('cpnyid');
        const departementid = $(this).data('assign');

        $('#work_id').val($(this).data('id'));
        $('#wi_docid').text($(this).data('docid'));
        $('#wi_taskdate').text($(this).data('taskdate'));
        $('#wi_cpnyid').text($(this).data('cpnyid'));
        $('#wi_departementid').text($(this).data('departementid'));
        $('#wi_tasktype').text($(this).data('tasktype'));
        $('#wi_taskpriority').text($(this).data('taskpriority'));
        $('#wi_summary').text($(this).data('summary'));
        $('#wi_description').text($(this).data('description'));
        $('#wo_priority').val($(this).data('taskpriority'));
        $('#work_start_date').val($(this).data('startdate'));
        $('#work_end_date').val($(this).data('enddate'));
        $('#work_description').val($(this).data('description'));

        loadComplaintTypes(cpnyid, departementid);
        loadWOTypes(cpnyid, departementid);
        loadLocations(cpnyid);
        loadWorkers(departementid);

        $('#createWiModal').removeClass('hidden');
    });

    $('#closeWiModal').click(function() {
        $('#createWiModal').addClass('hidden');
    });

    $('#wiForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('wi.store') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                // alert('Work Instruction saved!');
                toastr.success("Work Instruction successfully");
                $('#createWiModal').addClass('hidden');
                $('#wiForm')[0].reset();
                $('#tasksTable').DataTable().ajax.reload();
                if ($('#instructionTable').length && $.fn.DataTable.isDataTable(
                    '#instructionTable')) {
                    $('#instructionTable').DataTable().ajax.reload();
                }
            },
            error: function(err) {
                alert('Error saving data.');
                console.error(err);
            }
        });
    });
</script>

<script>
    function loadComplaintTypes(cpnyid, departementid) {
        console.log("🔍 Load Complaint Types | cpnyid:", cpnyid, "departementid:", departementid);

        $.ajax({
            url: "{{ route('wi.complaints') }}",
            method: "GET",
            data: {
                cpnyid: cpnyid,
                departementid: departementid
            },
            success: function(response) {
                let $select = $('#complaint_type');
                $select.empty().append('<option value="">Select Complaint Type</option>');
                response.forEach(function(item) {
                    $select.append(
                        `<option value="${item.complaintid}">${item.complaint_descr}</option>`);
                });
            },
            error: function(xhr) {
                alert('Failed to load complaint types');
                console.error(xhr);
            }
        });
    }
</script>

<script>
    function loadWOTypes(cpnyid, departementid) {
        console.log("🔍 Load WO Types | cpnyid:", cpnyid, "departementid:", departementid);

        $.ajax({
            url: "{{ route('wi.wotype') }}",
            method: "GET",
            data: {
                cpnyid: cpnyid,
                departementid: departementid
            },
            success: function(response) {
                let $select = $('#work_type');
                $select.empty().append('<option value="">Select WO Type</option>');
                response.forEach(function(item) {
                    $select.append(
                        `<option value="${item.worktype_id}">${item.worktype_descr}</option>`);
                });
            },
            error: function(xhr) {
                alert('Failed to load WO types');
                console.error(xhr);
            }
        });
    }
</script>

<script>
    $('#work_type').on('change', function() {
        const worktype_id = $(this).val();

        console.log("🔄 Load Sub Work Types for:", worktype_id);

        $.ajax({
            url: "{{ route('wi.subworktype') }}",
            method: "GET",
            data: {
                worktype_id: worktype_id
            },
            success: function(response) {
                let $select = $('#sub_work_type');
                $select.empty().append('<option value="">Select Sub Work Type</option>');
                response.forEach(function(item) {
                    $select.append(
                        `<option value="${item.subworktype_id}">${item.subworktype_descr}</option>`
                        );
                });
            },
            error: function(xhr) {
                alert('Failed to load sub work types');
                console.error(xhr);
            }
        });
    });
</script>

<script>
    function loadLocations(cpnyid) {
        console.log("📍 Load Locations | cpnyid:", cpnyid);

        $.ajax({
            url: "{{ route('wi.locations') }}",
            method: "GET",
            data: {
                cpnyid: cpnyid
            },
            success: function(response) {
                let $select = $('#location_id');
                $select.empty().append('<option value="">Select Location</option>');
                response.forEach(function(item) {
                    $select.append(
                        `<option value="${item.location_id}">${item.location_descr}</option>`);
                });
            },
            error: function(xhr) {
                alert('Failed to load locations');
                console.error(xhr);
            }
        });
    }
</script>
<script>
    $('#location_id').on('change', function() {
        const location_id = $(this).val();

        console.log("📍 Load Sub Locations for:", location_id);

        $.ajax({
            url: "{{ route('wi.sublocations') }}",
            method: "GET",
            data: {
                location_id: location_id
            },
            success: function(response) {
                let $select = $('#sub_location_id');
                $select.empty().append('<option value="">Select Sub Location</option>');
                response.forEach(function(item) {
                    $select.append(
                        `<option value="${item.sublocation_id}">${item.sublocation_descr}</option>`
                        );
                });
            },
            error: function(xhr) {
                alert('Failed to load sub locations');
                console.error(xhr);
            }
        });
    });
</script>

<script>
    function loadWorkers(departementid) {
        console.log("👥 Load Workers | departementid:", departementid);

        $.ajax({
            url: "{{ route('wi.workers') }}",
            method: "GET",
            data: {
                departementid: departementid
            },
            success: function(response) {
                let $select = $('#workers');
                $select.empty().append('<option value="">Select Workers</option>');
                response.forEach(function(item) {
                    $select.append(`<option value="${item.username}">${item.name}</option>`);
                });
            },
            error: function(xhr) {
                alert('Failed to load workers');
                console.error(xhr);
            }
        });
    }
</script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select Worker",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#createWiModal') // ⬅️ ini penting!
        });
    });
</script>
