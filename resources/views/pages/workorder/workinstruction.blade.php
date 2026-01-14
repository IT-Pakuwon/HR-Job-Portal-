{{-- <style>
    .no-border{
        border : none !important;
    }
    .grid {
        width: 100%;
    }

    select, textarea, input {
        width: 100%; /* Make all input elements take full width */
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
        justify-content: flex-start; /* Aligns items to the left */
        align-items: center; /* Vertically aligns items */
        }

    #tasksTable_filter label {
        margin-right: 2px;
    }

    #tasksTable_filter input {
        width: 200px; /* Adjust the width of the input box */
        }


    #tasksTable_wrapper {
        margin-top: 20px;
        width: 100%;
    }

    /* Prevent text from wrapping */
    #tasksTable td {
        white-space: nowrap;        /* Prevent text from wrapping */
        overflow: hidden;           /* Hide overflow content */
        text-overflow: ellipsis;    /* Display ellipsis ("...") for overflowing content */
    }

    /* Optional: Adjust the width for table cells */
    #tasksTable th, #tasksTable td {
        padding: 10px; /* Adjust padding for better appearance */
        max-width: 200px;  /* You can set a maximum width to control overflow */
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
        padding: 5px; /* Mengatur jarak antar opsi */
    }

    #tasksTable_info{
        margin-top:10px;
        margin-bottom:10px;
    }

    .dataTables_paginate {
        margin-top:10px;
        margin-bottom:10px;

    }
    #tasksTable tbody tr td {
        padding: 8px 8px; /* Adjust padding for uniform height */
        line-height: 2; /* Optional, for better text alignment */
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


</style>             --}}
<div class="mt-6 overflow-y-auto rounded-xl bg-white p-4 dark:bg-gray-800">
    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
        <h1 class="align-middle text-lg font-bold dark:text-white">Work Instruction</h1>
    </div>
    <div class="rounded-lg bg-white dark:bg-gray-800">
        <table id="instructionTable" class="mt-5 min-w-full rounded">
            <thead class="bg-white-200 dark:text-white">
                <tr>
                    <th class="w-32 px-4 py-3 text-left">DocID</th>
                    <th class="px-4 py-3 text-center">Date</th>
                    <th class="px-4 py-3 text-center">Priority</th>
                    <th class="px-4 py-3 text-center">Description</th>
                    <th class="px-4 py-3 text-center">StartDate</th>
                    <th class="px-4 py-3 text-center">EndDate</th>
                    <th class="w-32 px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var table = $('#instructionTable').DataTable({
            "order": [
                [0, "desc"]
            ],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('workinstruction') }}",

            },
            columns: [{
                    data: 'docid',
                    name: 'docid'
                },
                {
                    data: 'wo_date',
                    name: 'wo_date'
                },
                {
                    data: 'wo_priority',
                    name: 'wo_priority'
                },
                {
                    data: 'work_description',
                    name: 'work_description'
                },
                {
                    data: 'work_start_date',
                    name: 'work_start_date'
                },
                {
                    data: 'work_end_date',
                    name: 'work_end_date'
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
