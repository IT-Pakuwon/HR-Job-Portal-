<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'menus' ? 'Menus' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">📋 Sys Menu Tree</h1>
                <button id="addMenuBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Menu
                </button>
            </div>

            <div id="menuTreeContainer" class="mt-2">
                <!-- Menu tree akan di-render via jQuery -->
                <div class="text-sm text-gray-500">Loading menus...</div>
            </div>
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
                                @foreach ($parentMenus as $pm)
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
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" value="0">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Screen ID</label>
                            <select id="screen_id" name="screen_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                <option value="">-- (optional) pilih Screen --</option>
                                @foreach ($screens as $screen)
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
                                @foreach ($applications as $app)
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
                            <textarea id="menu_icon" name="menu_icon" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" rows="2"
                                placeholder="isi dengan nilai atribut d untuk <path> SVG">
                                </textarea>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeMenuModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {

            let menus = []; // cache semua menu dari API
            let currentParent = null; // untuk "Add Child"

            // ==========================
            //  LOAD & BUILD TREE
            // ==========================
            function loadMenuTree() {
                $('#menuTreeContainer').html('<div class="text-gray-500 text-sm">Loading menus...</div>');

                $.get("{{ route('menus.json') }}", function(res) {
                    menus = res.data || [];

                    // build tree mulai dari root (parent_menu_id = null / '')
                    const html = buildTree(null);

                    $('#menuTreeContainer').html(`
                            <div class="menu-tree">
                                ${html || '<div class="text-gray-500 text-sm">Belum ada menu.</div>'}
                            </div>
                        `);
                });
            }

            function buildTree(parentId) {
                // parentId bisa null atau string
                const children = menus
                    .filter(m => (m.parent_menu_id || null) === (parentId || null))
                    .sort((a, b) => {
                        const aSort = a.menu_sort_order ?? 0;
                        const bSort = b.menu_sort_order ?? 0;
                        if (aSort !== bSort) return aSort - bSort;
                        return (a.menu_id || '').localeCompare(b.menu_id || '');
                    });

                if (!children.length) return '';

                let html = '<ul class="mt-1">';

                children.forEach(m => {
                    const hasChildren = menus.some(x => (x.parent_menu_id || null) === (m.menu_id || null));
                    const statusBadge = m.status === 'A' ?
                        '<span class="bg-green-300/30 text-green-600 text-xs font-semibold px-2 py-0.5 rounded">Active</span>' :
                        '<span class="bg-red-300/30 text-red-600 text-xs font-semibold px-2 py-0.5 rounded">Inactive</span>';

                    html += `
                        <li>
                            <div class="flex items-center space-x-2 py-1">
                                <div class="flex items-center">
                                    ${hasChildren
                                        ? `<button class="tree-toggle text-gray-500" data-menu-id="${m.menu_id}">▾</button>`
                                        : `<span class="inline-block w-3"></span>`
                                    }
                                </div>

                                <div class="flex-1 flex items-center justify-between">
                                    <div>
                                        <span class="font-mono text-[11px] bg-gray-100 dark:bg-gray-700 px-1 rounded">${m.menu_id}</span>
                                        <span class="ml-2 font-semibold text-sm text-gray-800 dark:text-gray-100">${m.menu_name}</span>
                                        <span class="ml-2 text-xs text-gray-500">
                                            ${m.menu_route || m.menu_url || '-'}
                                        </span>
                                        <span class="ml-1 text-[10px] text-gray-400">
                                            [Sort: ${m.menu_sort_order ?? 0}]
                                        </span>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        ${statusBadge}

                                        <label class="switch">
                                            <input type="checkbox" class="toggleStatus"
                                                data-id="${m.id}" ${m.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>

                                        <button class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 text-xs rounded editMenuBtn"
                                            data-id="${m.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-2 py-1 text-xs rounded addChildBtn"
                                            data-parent-menu="${m.menu_id}">
                                            + Child
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="children-node" data-parent="${m.menu_id}">
                                ${buildTree(m.menu_id)}
                            </div>
                        </li>`;
                });

                html += '</ul>';
                return html;
            }

            // ==========================
            //  MODAL ADD / EDIT
            // ==========================
            $('#addMenuBtn').click(function() {
                currentParent = null;
                $('#menuModalTitle').text("Add Menu");
                $('#menuForm')[0].reset();
                $('#id').val('');
                $('#parent_menu_id').val('');
                $('#menuModal').removeClass('hidden');
            });

            // klik tombol "+ Child" pada node
            $(document).on('click', '.addChildBtn', function() {
                const parentMenuId = $(this).data('parent-menu');
                currentParent = parentMenuId;

                $('#menuModalTitle').text("Add Child Menu");
                $('#menuForm')[0].reset();
                $('#id').val('');
                $('#parent_menu_id').val(parentMenuId);
                $('#menuModal').removeClass('hidden');
            });

            // klik edit
            $(document).on('click', '.editMenuBtn', function() {
                let id = $(this).data('id');

                $('#menuModalTitle').text("Loading...");
                $('#menuModal').removeClass('hidden');

                $.get(`/menus/${id}/edit`, function(data) {
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

            $('#closeMenuModal').click(function() {
                $('#menuModal').addClass('hidden');
            });

            // ==========================
            //  TOGGLE STATUS
            // ==========================
            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/menus/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        loadMenuTree(); // reload tree supaya badge & switch ikut update
                    }
                });
            });

            // ==========================
            //  EXPAND / COLLAPSE TREE
            // ==========================
            $(document).on('click', '.tree-toggle', function() {
                const menuId = $(this).data('menu-id');
                const $children = $(`.children-node[data-parent="${menuId}"]`);

                if ($children.is(':visible')) {
                    $children.slideUp(150);
                    $(this).text('▸');
                } else {
                    $children.slideDown(150);
                    $(this).text('▾');
                }
            });

            // ==========================
            //  SUBMIT (CREATE / UPDATE)
            // ==========================
            $('#menuForm').submit(function(e) {
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
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#menuModal').addClass('hidden');
                        loadMenuTree();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal menyimpan data menu');
                    }
                });
            });

            // initial load
            loadMenuTree();
        });
    </script>
</x-app-layout>
