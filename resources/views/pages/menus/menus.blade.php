<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'menus' ? 'Menus' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">📋 Sys Menu Tree</h1>
                <button id="addMenuBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Menu
                </button>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative w-full sm:max-w-xs">
                    <input type="text" id="menuSearchInput" autocomplete="off"
                        placeholder="Search by menu id, name, or route..."
                        class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-700 dark:text-white">
                    <button type="button" id="clearSearchBtn"
                        class="absolute right-2 top-1/2 hidden -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        title="Clear search">✕</button>
                </div>
                <div class="flex items-center gap-2">
                    <span id="menuResultInfo" class="text-xs text-gray-400"></span>
                    <button type="button" id="expandAllBtn"
                        class="rounded-md border px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Expand All
                    </button>
                    <button type="button" id="collapseAllBtn"
                        class="rounded-md border px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Collapse All
                    </button>
                </div>
            </div>

            <div id="menuTreeContainer" class="mt-2">
                <!-- Menu tree akan di-render via jQuery -->
                <div class="text-sm text-gray-500">Loading menus...</div>
            </div>
        </div>

        {{-- Modal --}}
        <div id="menuModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-2xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="menuModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">
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
                                <option value="">ROOT (no parent) </option>
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
                                <option value="">(optional) pilih Screen </option>
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
                                <option value="">(optional) pilih Application </option>
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

                        <div id="roleAccessField" class="mb-3 md:col-span-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-gray-700 dark:text-white">
                                    Show this menu to (roles)
                                </label>
                                <label class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-300">
                                    <input type="checkbox" id="roleSelectAll">
                                    Select all
                                </label>
                            </div>
                            <div class="mt-1 grid max-h-40 grid-cols-2 gap-1 overflow-y-auto rounded-lg border p-2 dark:border-gray-600 sm:grid-cols-3">
                                @foreach ($roles as $role)
                                    <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-200">
                                        <input type="checkbox" name="role_ids[]" value="{{ $role->role_id }}" class="roleCheckbox">
                                        {{ $role->role_name }}
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-gray-400">
                                Leave unchecked and enable later from the Role Menu screen. Unselected roles won't see this menu.
                            </p>
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
    <style>
        .drag-handle {
            cursor: grab;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .tree-row.drop-before {
            box-shadow: inset 0 2px 0 0 #6366f1;
        }

        .tree-row.drop-after {
            box-shadow: inset 0 -2px 0 0 #6366f1;
        }

        .tree-row.drop-inside {
            background-color: rgba(99, 102, 241, .15);
        }

        .tree-row:hover {
            background-color: rgba(99, 102, 241, .06);
        }
    </style>
    <script>
        $(document).ready(function() {

            let menus = []; // cache semua menu dari API
            let currentParent = null; // untuk "Add Child"
            let expandedNodes = new Set(); // menu_id yang sedang dibuka user (default: semua tertutup)
            let searchTerm = '';
            let draggedMenuId = null;

            // ==========================
            //  LOAD & BUILD TREE
            // ==========================
            function loadMenuTree() {
                $('#menuTreeContainer').html('<div class="text-gray-500  text-sm ">Loading menus...</div>');

                $.get("{{ route('menus.json') }}", function(res) {
                    menus = res.data || [];
                    renderTree();
                });
            }

            function escapeRegExp(s) {
                return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function highlight(text, term) {
                text = text ?? '';
                if (!term) return text;
                const re = new RegExp('(' + escapeRegExp(term) + ')', 'ig');
                return String(text).replace(re,
                    '<mark class="bg-yellow-200 dark:bg-yellow-600 rounded px-0.5">$1</mark>');
            }

            function getAncestorIds(menuId) {
                const ids = new Set();
                let current = menus.find(m => m.menu_id === menuId);
                while (current && current.parent_menu_id) {
                    ids.add(current.parent_menu_id);
                    current = menus.find(m => m.menu_id === current.parent_menu_id);
                }
                return ids;
            }

            function renderTree() {
                $('#menuTreeContainer').css('opacity', '');
                const term = searchTerm.trim();
                let visibleIds = null; // null = tidak sedang searching
                let matchedCount = 0;

                if (term) {
                    const needle = term.toLowerCase();
                    visibleIds = new Set();
                    menus.forEach(m => {
                        const haystack =
                            `${m.menu_id} ${m.menu_name} ${m.menu_route || ''} ${m.menu_url || ''}`
                            .toLowerCase();
                        if (haystack.includes(needle)) {
                            matchedCount++;
                            visibleIds.add(m.menu_id);
                            getAncestorIds(m.menu_id).forEach(id => visibleIds.add(id));
                        }
                    });
                }

                const html = buildTree(null, term, visibleIds);
                $('#menuTreeContainer').html(`
                        <div class="menu-tree">
                            ${html || `<div class="text-gray-500 text-sm">${term ? 'Tidak ada menu yang cocok.' : 'Belum ada menu.'}</div>`}
                        </div>
                    `);

                $('#menuResultInfo').text(term ? `${matchedCount} match` : '');
                $('#clearSearchBtn').toggleClass('hidden', !term);
            }

            function buildTree(parentId, term, visibleIds) {
                // parentId bisa null atau string
                let children = menus
                    .filter(m => (m.parent_menu_id || null) === (parentId || null));

                if (visibleIds) {
                    children = children.filter(m => visibleIds.has(m.menu_id));
                }

                children = children.sort((a, b) => {
                    const aSort = a.menu_sort_order ?? 0;
                    const bSort = b.menu_sort_order ?? 0;
                    if (aSort !== bSort) return aSort - bSort;
                    return (a.menu_id || '').localeCompare(b.menu_id || '');
                });

                if (!children.length) return '';

                let html = '<ul class="mt-1">';

                children.forEach(m => {
                    const hasChildren = menus.some(x => (x.parent_menu_id || null) === (m.menu_id || null));
                    const childCount = hasChildren ? menus.filter(x => (x.parent_menu_id || null) === m
                        .menu_id).length : 0;
                    const isExpanded = term ? true : expandedNodes.has(m.menu_id);
                    const isActive = m.status === 'A';

                    html += `
                        <li data-menu-id="${m.menu_id}" data-parent="${m.parent_menu_id || ''}">
                            <div class="tree-row flex items-center space-x-2 py-1 rounded ${isActive ? '' : 'opacity-50'}">
                                <div class="flex items-center">
                                    ${term ? '' : `<span class="drag-handle text-gray-400 px-1" draggable="true" title="Drag to reorder / move"><i class="fas fa-grip-vertical"></i></span>`}
                                    ${hasChildren
                                        ? `<button type="button" class="tree-toggle text-gray-500" data-menu-id="${m.menu_id}">${isExpanded ? '▾' : '▸'}</button>`
                                        : `<span class="inline-block w-3"></span>`
                                    }
                                </div>

                                <div class="flex-1 flex items-center justify-between">
                                    <div>
                                        <span class="font-mono text-[11px] bg-gray-100 dark:bg-gray-700 px-1 rounded">${highlight(m.menu_id, term)}</span>
                                        <span class="ml-2 font-semibold  text-sm  text-gray-800 dark:text-gray-100">${highlight(m.menu_name, term)}</span>
                                        <span class="ml-2  text-sm  text-gray-500">
                                            ${highlight(m.menu_route || m.menu_url || '-', term)}
                                        </span>
                                        <span class="ml-1 text-[10px] text-gray-400">
                                            [Sort: ${m.menu_sort_order ?? 0}]
                                        </span>
                                        ${!isExpanded && hasChildren ? `<span class="ml-1 text-[10px] text-gray-400">(${childCount} sub)</span>` : ''}
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <label class="switch" title="${isActive ? 'Active' : 'Inactive'}">
                                            <input type="checkbox" class="toggleStatus"
                                                data-id="${m.id}" ${isActive ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>

                                        <button type="button" title="Edit menu"
                                            class="bg-blue-500 hover:bg-blue-600 text-white w-7 h-7 flex items-center justify-center text-sm rounded editMenuBtn"
                                            data-id="${m.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button type="button" title="Add child menu"
                                            class="bg-emerald-500 hover:bg-emerald-600 text-white w-7 h-7 flex items-center justify-center text-sm rounded addChildBtn"
                                            data-parent-menu="${m.menu_id}">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="children-node ${isExpanded ? '' : 'hidden'}" data-parent="${m.menu_id}">
                                ${buildTree(m.menu_id, term, visibleIds)}
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
                $('#roleAccessField').removeClass('hidden');
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
                $('#roleAccessField').removeClass('hidden');
                $('#menuModal').removeClass('hidden');
            });

            // klik edit
            $(document).on('click', '.editMenuBtn', function() {
                let id = $(this).data('id');

                $('#menuModalTitle').text("Loading...");
                $('#roleAccessField').addClass('hidden'); // role access sudah diatur dari layar Role Menu
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
            //  ROLE ACCESS "SELECT ALL"
            // ==========================
            $('#roleSelectAll').change(function() {
                $('.roleCheckbox').prop('checked', $(this).is(':checked'));
            });

            $(document).on('change', '.roleCheckbox', function() {
                const total = $('.roleCheckbox').length;
                const checked = $('.roleCheckbox:checked').length;
                $('#roleSelectAll').prop('checked', total === checked);
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
                if (expandedNodes.has(menuId)) {
                    expandedNodes.delete(menuId);
                } else {
                    expandedNodes.add(menuId);
                }
                renderTree();
            });

            $('#expandAllBtn').click(function() {
                menus.forEach(m => {
                    if (menus.some(x => (x.parent_menu_id || null) === m.menu_id)) {
                        expandedNodes.add(m.menu_id);
                    }
                });
                renderTree();
            });

            $('#collapseAllBtn').click(function() {
                expandedNodes.clear();
                renderTree();
            });

            // ==========================
            //  DRAG & DROP REORDER / RE-PARENT
            // ==========================
            function isDescendant(candidateId, ancestorId) {
                let current = menus.find(m => m.menu_id === candidateId);
                while (current && current.parent_menu_id) {
                    if (current.parent_menu_id === ancestorId) return true;
                    current = menus.find(m => m.menu_id === current.parent_menu_id);
                }
                return false;
            }

            $(document).on('dragstart', '.drag-handle', function(e) {
                draggedMenuId = $(this).closest('li').data('menu-id');
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/plain', draggedMenuId);
            });

            $(document).on('dragend', '.drag-handle', function() {
                $('.tree-row').removeClass('drop-before drop-after drop-inside');
                draggedMenuId = null;
            });

            $(document).on('dragover', '.tree-row', function(e) {
                if (!draggedMenuId) return;
                const targetMenuId = $(this).closest('li').data('menu-id');

                $('.tree-row').not(this).removeClass('drop-before drop-after drop-inside');

                if (targetMenuId === draggedMenuId || isDescendant(targetMenuId, draggedMenuId)) {
                    $(this).removeClass('drop-before drop-after drop-inside');
                    return;
                }

                e.preventDefault();
                const rect = this.getBoundingClientRect();
                const ratio = (e.originalEvent.clientY - rect.top) / rect.height;

                $(this).removeClass('drop-before drop-after drop-inside');
                if (ratio < 0.25) $(this).addClass('drop-before');
                else if (ratio > 0.75) $(this).addClass('drop-after');
                else $(this).addClass('drop-inside');
            });

            $(document).on('dragleave', '.tree-row', function() {
                $(this).removeClass('drop-before drop-after drop-inside');
            });

            $(document).on('drop', '.tree-row', function(e) {
                e.preventDefault();
                const $row = $(this);
                const zone = $row.hasClass('drop-before') ? 'before' :
                    $row.hasClass('drop-after') ? 'after' :
                    $row.hasClass('drop-inside') ? 'inside' : null;

                $('.tree-row').removeClass('drop-before drop-after drop-inside');

                const targetMenuId = $row.closest('li').data('menu-id');
                const sourceMenuId = draggedMenuId;
                draggedMenuId = null;

                if (!zone || !sourceMenuId || targetMenuId === sourceMenuId ||
                    isDescendant(targetMenuId, sourceMenuId)) return;

                moveMenu(sourceMenuId, targetMenuId, zone);
            });

            function moveMenu(draggedId, targetId, zone) {
                const dragged = menus.find(m => m.menu_id === draggedId);
                const target = menus.find(m => m.menu_id === targetId);
                if (!dragged || !target) return;

                const newParentId = zone === 'inside' ? target.menu_id : (target.parent_menu_id || null);

                let siblings = menus
                    .filter(m => (m.parent_menu_id || null) === (newParentId || null) && m.menu_id !==
                        draggedId)
                    .sort((a, b) => (a.menu_sort_order ?? 0) - (b.menu_sort_order ?? 0));

                if (zone === 'before') {
                    siblings.splice(siblings.findIndex(m => m.menu_id === targetId), 0, dragged);
                } else if (zone === 'after') {
                    siblings.splice(siblings.findIndex(m => m.menu_id === targetId) + 1, 0, dragged);
                } else {
                    siblings.push(dragged);
                }

                const updates = [];
                siblings.forEach((m, idx) => {
                    const isDragged = m.menu_id === draggedId;
                    const parent = isDragged ? newParentId : (m.parent_menu_id || null);
                    const parentChanged = isDragged && parent !== (dragged.parent_menu_id || null);
                    if (m.menu_sort_order !== idx || parentChanged) {
                        updates.push({
                            menu: m,
                            sort: idx,
                            parent
                        });
                    }
                });

                if (!updates.length) return;

                if (zone === 'inside') expandedNodes.add(newParentId);

                $('#menuTreeContainer').css('opacity', 0.5);

                Promise.all(updates.map(u => saveMenuOrder(u.menu, u.sort, u.parent)))
                    .then(loadMenuTree)
                    .catch(() => {
                        alert('Gagal menyimpan urutan menu');
                        loadMenuTree();
                    });
            }

            function saveMenuOrder(menu, sortOrder, parentMenuId) {
                const fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('menu_id', menu.menu_id);
                fd.append('menu_name', menu.menu_name);
                fd.append('parent_menu_id', parentMenuId || '');
                fd.append('menu_route', menu.menu_route || '');
                fd.append('menu_url', menu.menu_url || '');
                fd.append('menu_icon', menu.menu_icon || '');
                fd.append('menu_sort_order', sortOrder);
                fd.append('screen_id', menu.screen_id || '');
                fd.append('application_id', menu.application_id || '');

                return $.ajax({
                    url: `/menus/${menu.id}`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: fd,
                    processData: false,
                    contentType: false,
                });
            }

            // ==========================
            //  SEARCH
            // ==========================
            $('#menuSearchInput').on('input', function() {
                searchTerm = $(this).val();
                renderTree();
            });

            $('#clearSearchBtn').click(function() {
                searchTerm = '';
                $('#menuSearchInput').val('');
                renderTree();
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
