<x-app-layout>
    @php
    $currentPage = Route::currentRouteName() == 'users' ? 'Users' : '';
    @endphp
    <div class="px-4 sm:px-6 lg:px-8  w-full max-w-9xl mx-auto">
        <!-- Dashboard actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8"></div>
        <!-- Breadcrumb dengan Dropdown -->
        {{-- <div class="flex items-center justify-end mb-4 sm:mb-0">          
            <nav class="flex items-center text-gray-600 dark:text-gray-300">
                <a href="#" class="hover:text-gray-900 dark:hover:text-white">Settings</a>
                <span class="mx-2">/</span>

              
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center text-gray-800 dark:text-gray-100 font-bold">
                        Master <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                  
                    <ul x-show="open" @click.away="open = false"
                        class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded shadow-lg z-10">
                        <li><a href="{{ route('account') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">My Account</a></li>
                        <li><a href="{{ route('users') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master User</a></li>
                        <li><a href="{{ route('applications') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Application</a></li>
                        <li><a href="{{ route('groups') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Group</a></li>
                        <li><a href="{{ route('mastercard') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Card</a></li>
                    </ul>
                </div>

                <span class="mx-2">/</span>
                <span class="text-gray-800 dark:text-gray-100 font-bold">{{ $currentPage }}</span>
            </nav>
        </div> --}}
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
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">📌 Users List</h2>
                    <button id="addAppBtn" class="px-5 py-2 bg-indigo-500 text-white rounded-lg">
                        + Add User
                    </button>
                </div>
            
                <table id="usersTable" class="w-full border-collapse table-fixed">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-center w-32">Actions</th>
                            <th class="px-4 py-3 text-left">Name</th>                           
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">Departement</th>    
                            <th class="px-4 py-3 text-center w-32">Status</th>                            
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
            <!-- Modal -->
            <div id="appModal" class="fixed inset-0 flex hidden items-center justify-center bg-black/50 z-50">
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg w-1/3 relative">
                    <h2 id="modalTitle" class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Add User</h2>
                    <form id="appForm">
                        <input type="hidden" id="id">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Name</label>
                            <input type="text" id="name" name="name" class="w-full border px-3 py-2 rounded-lg dark:bg-gray-700" required>
                        </div>                       
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Email</label>
                            <input type="text" id="email" name="email" class="w-full border px-3 py-2 rounded-lg dark:bg-gray-700" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Company</label>
                            <select  name="companyid[]" class="w-full border px-3 py-2 rounded-lg dark:bg-gray-700 select2" multiple required>                                
                                @foreach($company as $p)
                                    <option value="{{ $p->cpnyid }}">{{ $p->cpnyid }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Departement</label>
                            <select  name="departmentid[]" class="w-full border px-3 py-2 rounded-lg dark:bg-gray-700 select2" multiple required>                               
                                @foreach($departement as $p)
                                    <option value="{{ $p->deptname }}">{{ $p->deptname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Jabatan</label>
                            <select name="jabatan" class="w-full border px-3 py-2 rounded-lg dark:bg-gray-700" required>
                                <option value="">Select Option</option>
                                <option value="staff">Staff</option>
                                <option value="manager">Manager</option>                                
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Groups</label>
                            <select  name="groups" class="w-full border px-3 py-2 rounded-lg dark:bg-gray-700" required>    
                                <option value="">Select Option</option>                           
                                @foreach($groups as $p)
                                    <option value="{{ $p->id }}">{{ $p->groupsname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Role</label>
                            <select name="role" class="w-full border px-3 py-2 rounded-lg dark:bg-gray-700" required>
                                <option value="">Select Option</option>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>                                
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">NIP</label>
                            <input type="text" name="npk" class="w-full border px-3 py-2 rounded-lg dark:bg-gray-700">
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
                    ajax: "{{ route('users.json') }}",
                    processing: true,
                    serverSide: false,                 
                    columns: [
                        {
                            data: 'id',
                            render: function (data, type, row) {
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
                        { data: 'name',className: 'no-pointer' },                       
                        { data: 'email',className: 'no-pointer'  },
                        { data: 'companyid',className: 'no-pointer'  },
                        { data: 'departmentid',className: 'no-pointer'  },
                        {
                            data: 'status',className: 'no-pointer',
                            render: function (data) {
                                return data === 'A'
                                    ? '<span class=" w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>'
                                    : '<span class="  w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                            }
                        }
                    ]
                });
            
                $('#addAppBtn').click(function () {
                    $('#modalTitle').text("Add User");
                    $('#appForm')[0].reset();
                    $('#id').val('');
                    $('.select2').val(null).trigger('change');
                    $('#appModal').removeClass('hidden');
                });
            
                // $(document).on('click', '.editAppBtn', function () {
                //     let appId = $(this).data('id');
                //     $.get(`/users/${appId}/edit`, function (app) {
                //         $('#modalTitle').text("Edit User");
                //         $('#id').val(app.id);
                //         $('#name').val(app.name);
                //         $('#username').val(app.username);
                //         $('#email').val(app.email);
                //         $('#npk').val(app.npk);
                //         $('#jabatan').val(app.jabatan);                      
                //         $('#appModal').removeClass('hidden');
                //     });
                // });
                $(document).on('click', '.editAppBtn', function () {
                    let appId = $(this).data('id');
                    $.get(`/users/${appId}/edit`, function (app) {
                        $('#modalTitle').text("Edit User");
                        $('#id').val(app.id);
                        $('#name').val(app.name);                     
                        $('#email').val(app.email);
                        $('#npk').val(app.npk);
                        $('#jabatan').val(app.jabatan);
                        $('select[name="groups"]').val(app.groups).trigger('change');
                        $('select[name="role"]').val(app.role).trigger('change');

                        $('select[name="companyid[]"]').val(app.companyid).trigger('change');
                        $('select[name="departmentid[]"]').val(app.departmentid).trigger('change');

                        $('#appModal').removeClass('hidden');
                    });
                });

            
                // ✅ Toggle Status (Active <-> Inactive)
                $(document).on('change', '.toggleStatus', function () {
                    let appId = $(this).data('id');
                    let newStatus = $(this).is(':checked') ? 'A' : 'X';
            
                    $.ajax({
                        url: `/users/${appId}/toggle-status`,
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
                        let url = appId ? `/users/${appId}` : "{{ route('users.store') }}";
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
                    $('.select2').select2({
                        placeholder: "Select Option",        
                        allowClear: true,
                        width: '100%'
                    });
                });
            </script>
            
        </div>
    </div>
</x-app-layout>


