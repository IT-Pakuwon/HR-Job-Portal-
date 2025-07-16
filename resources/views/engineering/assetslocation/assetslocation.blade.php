<x-app-layout>
    @php
    $currentPage = Route::currentRouteName() == 'users' ? 'Users' : '';
    @endphp
    <div class="px-4 sm:px-6 lg:px-8  w-full max-w-9xl mx-auto">
        <!-- Dashboard actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8"></div>
       
        <div class="grid">
            <style>
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
                #usersTable_filter {
                        margin-bottom: 20px;
                        display: flex;
                        justify-content: flex-start; /* Aligns items to the left */
                        align-items: center; /* Vertically aligns items */
                    }
            
                #usersTable_filter label {
                    margin-right: 2px;
                }
            
                #usersTable_filter input {
                    width: 200px; /* Adjust the width of the input box */
                    }
            
            
                #usersTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }
            
                /* Prevent text from wrapping */
                #usersTable td {
                    white-space: nowrap;        /* Prevent text from wrapping */
                    overflow: hidden;           /* Hide overflow content */
                    text-overflow: ellipsis;    /* Display ellipsis ("...") for overflowing content */
                }
            
                /* Optional: Adjust the width for table cells */
                #usersTable th, #usersTable td {
                    padding: 10px; /* Adjust padding for better appearance */
                    max-width: 200px;  /* You can set a maximum width to control overflow */
                }
            
            
                #usersTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }
            
                #usersTable_length select {
                    width: 80px; /* Lebar otomatis untuk select dropdown */
                    padding: 5px; Menambahkan padding agar lebih nyaman
                    min-width:0px; /* Lebar minimal untuk memastikan angka tidak tertutup */
                }
            
                #usersTable_length select option {
                    padding: 5px; /* Mengatur jarak antar opsi */
                }
            
                #usersTable_info{
                    margin-top:10px;
                    margin-bottom:10px;
                }
            
                .dataTables_paginate {
                    margin-top:10px;
                    margin-bottom:10px;
            
                }
                #usersTable tbody tr td {
                    padding: 8px 8px; /* Adjust padding for uniform height */
                    line-height: 1.6; /* Optional, for better text alignment */
                }
            
                #usersTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }
            
                #usersTable tbody tr:hover {
                    background-color: #8f8f8f11;
                        opacity: 100%;
                        cursor: pointer;
                }
            
                #usersTable tbody tr:hover td {
                    color: black;
                }
                        
            </style>
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
                input:checked + .slider {
                    background-color: #4CAF50;
                }
                input:checked + .slider:before {
                    transform: translateX(18px);
                }
                
                /* ✅ Memperkecil Lebar Kolom Actions */
                #usersTable th:nth-child(1), #usersTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }
                #usersTable th:nth-child(4), #usersTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="mt-6 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">📌 Assets Location</h2>
                    <button id="addAppBtn" class="px-5 py-2 bg-indigo-500 text-white rounded-lg">
                        + Add Assets location
                    </button>
                </div>
            
                <table id="usersTable" class="w-full border-collapse table-fixed">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-center w-32">Actions</th>
                            <th class="px-4 py-3 text-left">Building Location</th>                           
                            <th class="px-4 py-3 text-left">Floor Location</th>
                            <th class="px-4 py-3 text-left">Location Name</th>
                            <th class="px-4 py-3 text-left">Location Code</th>                           
                            <th class="px-4 py-3 text-center w-32">Status</th>                            
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
            <!-- Modal -->
            <div id="appModal" class="fixed inset-0 flex hidden items-center justify-center bg-black/50 z-50">
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg w-1/3 relative">
                    <h2 id="modalTitle" class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Add Assets location</h2>
                    <form id="appForm">
                        <input type="hidden" id="id">

                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Building Location</label>
                            <select name="floor_id" id="floor_id" class="form-select select2" required>
                                <option value="">Select Floor</option>
                                @foreach($floorbuilding as $floor)
                                    <option value="{{ $floor->id }}">
                                        {{ $floor->building->Building_name ?? '-' }} - {{ $floor->Floor_name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Location Name</label>
                            <input type="text" id="location_name" name="location_name" class="w-full border px-3 py-2 rounded-lg dark:bg-gray-700" required>
                        </div>     
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Unit Code</label>
                            <input type="text" name="location_code" class="w-full border px-3 py-2 rounded-lg dark:bg-gray-700" required>
                        </div>
                        

                        <div class="flex justify-end space-x-2">
                            <button type="button" id="closeModal" class="bg-red-500 text-white px-4 py-2 rounded-lg">Cancel</button>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Save</button>
                        </div>
                    </form>

                </div>
            </div>
            
            <script>
            $(document).ready(function () {
                let table = $('#usersTable').DataTable({
                    ajax: "{{ route('assetslocation.json') }}",
                    processing: true,
                    serverSide: false,                 
                    columns: [
                        {
                            data: 'id',
                            render: function (data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.active_status === '1' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                                <button class="editAppBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        { data: 'building',className: 'no-pointer' },                       
                        { data: 'floor',className: 'no-pointer'  },  
                        { data: 'location_name', className: 'no-pointer' },
                        { data: 'location_code',className: 'no-pointer'  },                    
                        {
                            data: 'active_status',className: 'no-pointer',
                            render: function (data) {
                                return data === '1'
                                    ? '<span class=" w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>'
                                    : '<span class="  w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                            }
                        }
                    ]
                });
            
                $('#addAppBtn').click(function () {
                    $('#modalTitle').text("Add Assets location");
                    $('#appForm')[0].reset();
                    $('#id').val('');
                    $('.select2').val(null).trigger('change');
                    $('#appModal').removeClass('hidden');
                });
            
          
                $(document).on('click', '.editAppBtn', function () {
                    let appId = $(this).data('id');
                    $.get(`/eng/assetslocation/${appId}/edit`, function (app) {
                        $('#modalTitle').text("Edit Assets location");
                        $('#id').val(app.id);
                        $('#floor_id').val(app.floor_id).trigger('change');
                        $('#location_name').val(app.location_name);
                        $('input[name="location_code"]').val(app.location_code);
                        $('#appModal').removeClass('hidden');
                    });
                });

            
                // ✅ Toggle Status (Active <-> Inactive)
                $(document).on('change', '.toggleStatus', function () {
                    let appId = $(this).data('id');
                    let newStatus = $(this).is(':checked') ? '1' : '0';
            
                    $.ajax({
                        url: `/eng/assetslocation/${appId}/toggle-status`,
                        type: 'PUT',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { status: newStatus },
                        success: function () {
                            table.ajax.reload(null, false);
                        }
                    });
                });
            
                $('#appForm').submit(function (e) {
                        e.preventDefault();
                        let appId = $('#id').val();
                        let url = appId ? `/eng/assetslocation/${appId}` : "{{ route('assetslocation.store') }}";
                        let method = 'POST'; // <-- selalu POST

                        let formData = new FormData(document.getElementById('appForm'));                                              

                        if (appId) {
                            formData.append('_method', 'PUT'); // <-- spoof PUT method
                        }

                        $.ajax({
                            url: url,
                            type: method,
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function () {
                                $('#appModal').addClass('hidden');    
                                toastr.success('Assets Location Save Successfully!');                          
                                table.ajax.reload();
                            }
                        });
                    });
            
                $('#closeModal').click(function () {
                    $('#appModal').addClass('hidden');
                });
            });
            </script>
            
            <script>
                $(document).ready(function () {
                    $('#floor_id').select2({
                        placeholder: "Select Floor",
                        allowClear: true,
                        width: '100%'
                    });
                });
            </script>

            </script>

            <!-- Toastr CSS -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
            <!-- Toastr JS -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
            
        </div>
    </div>
</x-app-layout>


