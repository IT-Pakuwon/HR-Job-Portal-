<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'menus' ? 'Menus' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>

        <div class="grid">
            <style>
                table.dataTable { width: 100% !important; }
                #menusTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }
                #menusTable_filter input {
                    width: auto;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }
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
                    top: 0; left: 0; right: 0; bottom: 0;
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
                input:checked + .slider { background-color: #4CAF50; }
                input:checked + .slider:before { transform: translateX(18px); }
            </style>

            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">📋 Sys Menu List</h2>
                    <button id="addMenuBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                        + Add Menu
                    </button>
                </div>

                <table id="menusTable" class="w-full border-collapse">
                    <thead class="bg-white dark:bg-gray-700">
                        <tr>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Menu ID</th>
                            <th class="px-4 py-3 text-left">Menu Name</th>
                            <th class="px-4 py-3 text-left">Parent</th>
                            <th class="px-4 py-3 text-left">Route</th>
                            <th class="px-4 py-3 text-left">URL</th>
                            <th class="px-4 py-3 text-left">Sort</th>
                            <th class="px-4 py-3 text-left">Screen</th>
                            <th class="px-4 py-3 text-left">App ID</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- Modal --}}
            <div id="menuModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="relative w-full max-w-2xl rounded-lg bg-white p-6 dark:bg-gray-700">
                    <h2 id="menuModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">
                        Add Menu
                    </h2>
                    <form id="menuForm">
                        @csrf
                        <input type="hidden" id="id" name="id">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Menu ID</label>
                                <input type="text" id="menu_id" name="menu_id"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Parent Menu (optional)</label>
                                <select id="parent_menu_id" name="parent_menu_id"
                                        class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                    <option value="">-- ROOT (no parent) --</option>
                                    @foreach($parentMenus as $pm)
                                        <option value="{{ $pm->menu_id }}">
                                            {{ $pm->menu_id }} - {{ $pm->menu_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">Menu Name</label>
                                <input type="text" id="menu_name" name="menu_name"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Route Name (Laravel)</label>
                                <input type="text" id="menu_route" name="menu_route"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                       placeholder="contoh: budgets, departments, dll">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Custom URL (optional)</label>
                                <input type="text" id="menu_url" name="menu_url"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                       placeholder="kalau pakai route, bisa dikosongkan">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Sort Order</label>
                                <input type="number" id="menu_sort_order" name="menu_sort_order"
                                       class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                       value="0">
                            </div>

                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Screen ID</label>
                                <select id="screen_id" name="screen_id"
                                        class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                    <option value="">-- (optional) pilih Screen --</option>
                                    @foreach($screens as $screen)
                                        <option value="{{ $screen->screen_id }}">
                                            {{ $screen->screen_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="mb-3">
                                <label class="block text-gray-700 dark:text-white">Application ID</label>
                                <select id="application_id" name="application_id"
                                        class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                    <option value="">-- (optional) pilih Application --</option>
                                    @foreach($applications as $app)
                                        <option value="{{ $app->application_id }}">
                                            {{ $app->application_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="mb-3 md:col-span-2">
                                <label class="block text-gray-700 dark:text-white">
                                    Menu Icon (SVG path d="...")
                                </label>
                                <textarea id="menu_icon" name="menu_icon"
                                          class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                          rows="2"
                                          placeholder="isi dengan nilai atribut d untuk <path> SVG">
                                </textarea>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end space-x-2">
                            <button type="button" id="closeMenuModal"
                                    class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                            <button type="submit"
                                    class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function () {
                    let table = $('#menusTable').DataTable({
                        ajax: {
                            url: "{{ route('menus.json') }}",
                            type: "GET",
                            dataSrc: 'data'
                        },
                        processing: true,
                        serverSide: false,
                        order: [
                            [0, 'asc'], // kolom ke-7 = menu_sort_order
                           
                        ],
                        columns: [
                            {
                                data: 'id',
                                render: function (data, type, row) {
                                    return `
                                        <div class="flex justify-center space-x-2">
                                            <label class="switch">
                                                <input type="checkbox" class="toggleStatus"
                                                    data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                <span class="slider round"></span>
                                            </label>
                                            <button class="editMenuBtn bg-blue-500 text-white px-2 py-1 rounded"
                                                data-id="${data}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    `;
                                }
                            },
                            { data: 'menu_id' },
                            { data: 'menu_name' },
                            { data: 'parent_menu_id' },
                            { data: 'menu_route' },
                            { data: 'menu_url' },
                            { data: 'menu_sort_order' },
                            { data: 'screen_id' },
                            { data: 'application_id' },
                            {
                                data: 'status',
                                className: 'text-center',
                                render: function (data) {
                                    return data === 'A'
                                        ? '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Active</span>'
                                        : '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-1 rounded">Inactive</span>';
                                }
                            }
                        ]
                    });

                    // Add
                    $('#addMenuBtn').click(function () {
                        $('#menuModalTitle').text("Add Menu");
                        $('#menuForm')[0].reset();
                        $('#id').val('');
                        $('#menuModal').removeClass('hidden');
                    });

                    // Edit
                    $(document).on('click', '.editMenuBtn', function () {
                        let id = $(this).data('id');

                        $('#menuModalTitle').text("Loading...");
                        $('#menuModal').removeClass('hidden');

                        $.get(`/menus/${id}/edit`, function (data) {
                            $('#menuModalTitle').text("Edit Menu");
                            $('#id').val(data.id);
                            $('#menu_id').val(data.menu_id);
                            $('#parent_menu_id').val(data.parent_menu_id);
                            $('#menu_name').val(data.menu_name);
                            $('#menu_route').val(data.menu_route);
                            $('#menu_url').val(data.menu_url);
                            $('#menu_icon').val(data.menu_icon);
                            $('#menu_sort_order').val(data.menu_sort_order);
                            $('#screen_id').val(data.screen_id);
                            $('#application_id').val(data.application_id);
                        });
                    });

                    // Toggle status
                    $(document).on('change', '.toggleStatus', function () {
                        let id = $(this).data('id');
                        let newStatus = $(this).is(':checked') ? 'A' : 'X';

                        $.ajax({
                            url: `/menus/${id}/toggle-status`,
                            type: 'PUT',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: { status: newStatus },
                            success: function () {
                                table.ajax.reload(null, false);
                            }
                        });
                    });

                    // Submit (create/update)
                    $('#menuForm').submit(function (e) {
                        e.preventDefault();

                        let id = $('#id').val();
                        let url = id ? `/menus/${id}` : "{{ route('menus.store') }}";
                        let method = 'POST';
                        let formData = new FormData(document.getElementById('menuForm'));

                        if (id) {
                            formData.append('_method', 'PUT');
                        }

                        $.ajax({
                            url: url,
                            type: method,
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function () {
                                $('#menuModal').addClass('hidden');
                                table.ajax.reload();
                            },
                            error: function (xhr) {
                                console.error(xhr.responseText);
                                alert('Gagal menyimpan data menu');
                            }
                        });
                    });

                    $('#closeMenuModal').click(function () {
                        $('#menuModal').addClass('hidden');
                    });
                });
            </script>
        </div>
    </div>
</x-app-layout>
