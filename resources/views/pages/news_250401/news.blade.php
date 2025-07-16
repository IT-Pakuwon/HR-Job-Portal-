<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'news' ? 'Master New' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <!-- Dashboard actions -->
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>
        <!-- Breadcrumb dengan Dropdown -->
        <div class="mb-4 flex items-center justify-end sm:mb-0">
            <!-- Title Page -->
            {{-- <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">{{ $currentPage }}</h1> --}}
            <!-- Breadcrumb -->
            <nav class="flex items-center text-gray-600 dark:text-gray-300">
                <a href="#" class="hover:text-gray-900 dark:hover:text-white">Settings</a>
                <span class="mx-2">/</span>

                <!-- Dropdown untuk Master -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center font-bold text-gray-800 dark:text-gray-100">
                        Master <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <ul x-show="open" @click.away="open = false"
                        class="absolute left-0 z-10 mt-2 w-48 rounded border border-gray-300 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                        <li><a href="{{ route('account') }}"
                                class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">My Account</a></li>
                        <li><a href="{{ route('news') }}"
                                class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master New</a></li>
                        <li><a href="{{ route('applications') }}"
                                class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Application</a>
                        </li>
                        <li><a href="{{ route('groups') }}"
                                class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Group</a></li>
                        <li><a href="{{ route('mastercard') }}"
                                class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Card</a></li>
                    </ul>
                </div>

                <span class="mx-2">/</span>
                <span class="font-bold text-gray-800 dark:text-gray-100">{{ $currentPage }}</span>
            </nav>
        </div>
        <div class="grid">
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

                #newsTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #newsTable_filter label {
                    margin-right: 2px;
                }

                #newsTable_filter input {
                    width: 200px;
                    /* Adjust the width of the input box */
                }


                #newsTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #newsTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #newsTable th,
                #newsTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #newsTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #newsTable_length select {
                    width: 80px;
                    /* Lebar otomatis untuk select dropdown */
                    padding: 5px;
                    Menambahkan padding agar lebih nyaman min-width: 0px;
                    /* Lebar minimal untuk memastikan angka tidak tertutup */
                }

                #newsTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #newsTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #newsTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 1.6;
                    /* Optional, for better text alignment */
                }

                #newsTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #newsTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #newsTable tbody tr:hover td {
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

                input:checked+.slider {
                    background-color: #4CAF50;
                }

                input:checked+.slider:before {
                    transform: translateX(18px);
                }

                /* ✅ Memperkecil Lebar Kolom Actions */
                #newsTable th:nth-child(1),
                #newsTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #newsTable th:nth-child(4),
                #newsTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="mt-6 rounded-xl bg-white p-4 shadow-lg dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">📌 News List</h2>
                    <button id="addAppBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add New
                    </button>
                </div>

                <table id="newsTable" class="w-full table-fixed border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Title</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal -->
            <div id="appModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div
                    class="relative max-h-[90vh] w-2/3 max-w-4xl overflow-y-auto rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="modalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Create News</h2>
                    <form id="appForm" class="pb-10">
                        <input type="hidden" id="id">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Title</label>
                            <textarea id="title" name="title"
                                class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                required></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-white">Description</label>
                            <textarea id="description" name="description" class="h-40 w-full rounded-lg border px-3 py-2 dark:bg-gray-700"></textarea>

                        </div>
                        <div class="mb-4">
                            <details class="group mb-4" open>
                                <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                    <span class="text-lg font-semibold">Attachments</span>
                                    <span class="transition-all group-open:hidden">See details</span>
                                    <span class="hidden transition-all group-open:inline">Hide details</span>
                                </summary>
                                <div class="flex h-auto flex-col justify-start">
                                    <div id="attachmentsContainer">
                                        <div class="attachment-row flex items-center gap-2">
                                            <input type="file" name="attachments[]"
                                                class="mt-4 w-full border p-3 text-lg">
                                            <button type="button"
                                                class="removeAttachment mt-4 hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-600 hover:text-white">
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" id="addAttachment"
                                        class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-gray-700 bg-gray-200/10 p-2 text-gray-800 hover:border-red-700 hover:bg-red-200/10 hover:font-medium hover:text-red-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg> Add Attachment
                                    </button>
                                </div>
                            </details>
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
                    let table = $('#newsTable').DataTable({
                        ajax: "{{ route('news.json') }}",
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
                                data: 'title',
                                className: 'no-pointer',
                                render: function(data, type, row) {
                                    return `
                                        <div class="text-sm font-semibold text-gray-800 dark:text-white">
                                            ${data}
                                        </div>
                                    `;
                                }
                            },
                            {
                                data: 'description',
                                className: 'no-pointer',
                                render: function(data, type, row) {
                                    return `
                                        <div class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                            ${data}
                                        </div>
                                    `;
                                }
                            },

                            {
                                data: 'status',
                                className: 'no-pointer',
                                render: function(data) {
                                    return data === 'A' ?
                                        '<span class=" w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                        '<span class="  w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                                }
                            }
                        ]
                    });

                    $('#addAppBtn').click(function() {
                        $('#modalTitle').text("Add News");
                        $('#appForm')[0].reset();
                        $('#id').val('');
                        tinymce.get('description').setContent('');
                        $('#appModal').removeClass('hidden');
                    });

                    $(document).on('click', '.editAppBtn', function() {
                        let appId = $(this).data('id');
                        $.get(`/news/${appId}/edit`, function(app) {
                            $('#modalTitle').text("Edit News");
                            $('#id').val(app.id);
                            $('#title').val(app
                            .title); // ini jika field title memang title, bukan screen_code
                            tinymce.get('description').setContent(app.description || '');
                            $('#appModal').removeClass('hidden');
                        });
                    });

                    // ✅ Toggle Status (Active <-> Inactive)
                    $(document).on('change', '.toggleStatus', function() {
                        let appId = $(this).data('id');
                        let newStatus = $(this).is(':checked') ? 'A' : 'I';

                        $.ajax({
                            url: `/news/${appId}/toggle-status`,
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
                        let url = appId ? `/news/${appId}` : "{{ route('news.store') }}";
                        let method = 'POST'; // <-- selalu POST

                        let formData = new FormData(document.getElementById('appForm'));
                        formData.set('description', tinymce.get('description').getContent());

                        if (appId) {
                            formData.append('_method', 'PUT'); // <-- spoof PUT method
                        }

                        $.ajax({
                            url: url,
                            type: method,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: formData,
                            processData: false,
                            contentType: false,
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
                    // Fungsi Tambah Attachment
                    $('#addAttachment').click(function() {
                        $('#attachmentsContainer').append(`
                        <div class="attachment-row flex items-center gap-2">
                            <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-lg border rounded mt-4">
                                <button type="button" class="removeAttachment bg-red-200/30 mt-4 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">🗑️</button>
                        </div>
                    `);
                        toggleDeleteButton();
                    });

                    // Fungsi Hapus Attachment
                    $(document).on('click', '.removeAttachment', function() {
                        $(this).closest('.attachment-row').remove();
                        toggleDeleteButton();
                    });

                    // Fungsi untuk Menampilkan atau Menyembunyikan Tombol Delete
                    function toggleDeleteButton() {
                        if ($('.attachment-row').length > 1) {
                            $('.removeAttachment').removeClass('hidden');
                        } else {
                            $('.removeAttachment').addClass('hidden');
                        }
                    }
                });
            </script>
            <!-- TinyMCE - versi bebas tanpa key -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="no-referrer"></script>
            <script>
                tinymce.init({
                    selector: '#description',
                    height: 250,
                    menubar: false,
                    plugins: 'lists link image preview',
                    toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link image | preview',
                    skin: 'oxide',
                    content_css: 'default',
                });
            </script>
        </div>
    </div>
</x-app-layout>
