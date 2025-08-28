<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="grid">

            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">📌 Assessments List</h2>
                    <button id="addAppBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Assessment
                    </button>
                </div>

                <table id="assessmentsTable" class="w-full table-fixed border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">AssessmentID</th>
                            <th class="px-4 py-3 text-left">AssessmentGroup</th>
                            <th class="px-4 py-3 text-left">Assessment Descr</th>
                            <th class="px-4 py-3 text-left">Assessment Score</th>
                            <th class="px-4 py-3 text-left">Step Order Group</th>
                            <th class="px-4 py-3 text-left">Step Order</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal -->
            <div id="appModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-1/3 rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="modalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Add Assessment</h2>
                    <form id="appForm">
                        <input type="hidden" id="id">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">AssessmentID</label>
                            <input type="text" id="assessment_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">AssessmentGroup</label>
                            <textarea class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" rows="3" id="assessment_group"
                                name="assessment_group"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Assessment Descr</label>
                            <textarea class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" rows="3" id="assessment_descr"
                                name="assessment_descr"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">AssessmentScore</label>
                            <input type="text" id="assessment_score"
                                class="number-only w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Step Order Group</label>
                            <input type="text" id="step_order_group"
                                class="number-only w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Step Order</label>
                            <input type="text" id="step_order"
                                class="number-only w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" id="closeModal"
                                class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    let table = $('#assessmentsTable').DataTable({
                        ajax: "{{ route('assessments.json') }}",
                        processing: true,
                        serverSide: false,
                        columnDefs: [{
                                width: "120px",
                                targets: 0
                            }, // Lebar kolom Actions
                            {
                                width: "120px",
                                targets: 3
                            } // Lebar kolom Status
                        ],
                        columns: [{
                                data: 'id',
                                width: '80px',
                                render: function(data, type, row) {
                                    return `
                            <div class="flex justify-center space-x-2">
                                <label class="switch">
                                    <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                    <span class="slider round"></span>
                                </label>
                                <button class="editAppBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        `;
                                }
                            },
                            {
                                data: 'assessment_id',
                                width: '80px'
                            },
                            {
                                data: 'assessment_group',
                                width: '300px'
                            },
                            {
                                data: 'assessment_descr',
                                width: '350px'
                            },
                            {
                                data: 'assessment_score',
                                width: '50px'
                            },
                            {
                                data: 'step_order_group',
                                width: '50px'
                            },
                            {
                                data: 'step_order',
                                width: '50px'
                            },
                            {
                                data: 'status',
                                width: '50px',
                                render: function(data) {
                                    return data === 'A' ?
                                        '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                        '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                                }
                            }
                        ]

                    });

                    $('#addAppBtn').click(function() {
                        $('#modalTitle').text("Add Assessment");
                        $('#appForm')[0].reset();
                        $('#id').val('');
                        $('#appModal').removeClass('hidden');
                    });

                    $(document).on('click', '.editAppBtn', function() {
                        let appId = $(this).data('id');
                        $.get(`/assessments/${appId}/edit`, function(app) {
                            $('#modalTitle').text("Edit Assessment");
                            $('#id').val(app.id);
                            $('#assessment_id').val(app.assessment_id);
                            $('#assessment_group').val(app.assessment_group);
                            $('#assessment_descr').val(app.assessment_descr);
                            $('#assessment_score').val(app.assessment_score);
                            $('#step_order_group').val(app.step_order_group);
                            $('#step_order').val(app.step_order);
                            $('#appModal').removeClass('hidden');
                        });
                    });

                    // ✅ Toggle Status (Active <-> Inactive)
                    $(document).on('change', '.toggleStatus', function() {
                        let appId = $(this).data('id');
                        let newStatus = $(this).is(':checked') ? 'A' : 'I';

                        $.ajax({
                            url: `/assessments/${appId}/toggle-status`,
                            type: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: {
                                status: newStatus
                            },
                            success: function() {
                                table.ajax.reload(null, false);
                            }
                        });
                    });

                    $('#appForm').submit(function(e) {
                        e.preventDefault();
                        let appId = $('#id').val();
                        let url = appId ? `/assessments/${appId}` : "{{ route('assessments.store') }}";
                        let method = appId ? 'PUT' : 'POST';

                        $.ajax({
                            url: url,
                            type: method,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: {
                                assessment_id: $('#assessment_id').val(),
                                assessment_group: $('#assessment_group').val(),
                                assessment_descr: $('#assessment_descr').val(),
                                assessment_score: $('#assessment_score').val(),
                                step_order_group: $('#step_order_group').val(),
                                step_order: $('#step_order').val(),
                            },
                            success: function() {
                                $('#appModal').addClass('hidden');
                                table.ajax.reload();
                            }
                        });
                    });

                    $('#closeModal').click(function() {
                        $('#appModal').addClass('hidden');
                    });
                });
            </script>

            <script>
                $(document).ready(function() {
                    // Cegah input selain angka saat mengetik
                    $('.number-only').on('keypress', function(event) {
                        let charCode = event.which ? event.which : event.keyCode;
                        if (charCode < 48 || charCode > 57) {
                            event.preventDefault();
                        }
                    });

                    // Hapus karakter selain angka jika sudah terlanjur masuk
                    $('.number-only').on('input', function() {
                        let value = $(this).val();
                        $(this).val(value.replace(/[^0-9]/g, ''));
                    });
                });
            </script>

            <style>
                /* ✅ Custom Switch Button */
                .switch {
                    position: relative;
                    display: inline-block;
                    width: 40px;
                    height: 22px;
                }

                .switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }

                .slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    transition: .4s;
                    border-radius: 34px;
                }

                .slider:before {
                    position: absolute;
                    content: "";
                    height: 16px;
                    width: 16px;
                    left: 3px;
                    bottom: 3px;
                    background-color: white;
                    transition: .4s;
                    border-radius: 50%;
                }

                input:checked+.slider {
                    background-color: #4CAF50;
                }

                input:checked+.slider:before {
                    transform: translateX(18px);
                }

                /* ✅ Memperkecil Lebar Kolom Actions */
                #assessmentsTable th:nth-child(1),
                #assessmentsTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #assessmentsTable th:nth-child(4),
                #assessmentsTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
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

                #assessmentsTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #assessmentsTable_filter label {
                    margin-right: 2px;
                }

                #assessmentsTable_filter input {
                    width: 200px;
                    /* Adjust the width of the input box */
                }


                #assessmentsTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #assessmentsTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #assessmentsTable th,
                #assessmentsTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #assessmentsTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #assessmentsTable_length select {
                    width: 80px;
                    /* Lebar otomatis untuk select dropdown */
                    padding: 5px;
                    Menambahkan padding agar lebih nyaman min-width: 0px;
                    /* Lebar minimal untuk memastikan angka tidak tertutup */
                }

                #assessmentsTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #assessmentsTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #assessmentsTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 1.6;
                    /* Optional, for better text alignment */
                }

                #assessmentsTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #assessmentsTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #assessmentsTable tbody tr:hover td {
                    color: black;
                }
            </style>

        </div>
    </div>

</x-app-layout>
