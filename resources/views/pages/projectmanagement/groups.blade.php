<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">👥 Project
                        Groups / Teams</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Create a Team, scope it to one or more
                        operational departments, then build Projects under it.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('projects.index') }}"
                        class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        View Projects
                    </a>
                    @if ($canManageGroups)
                        <button id="addGroupBtn"
                            class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500">
                            + New Team
                        </button>
                    @endif
                </div>
            </div>

            <div id="groupsGrid" class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"></div>
            <p id="groupsEmptyState" class="hidden px-5 pb-6 text-sm text-gray-400">No teams yet.</p>
        </div>
    </div>

    <!-- Modal -->
    <div id="groupModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-lg bg-white shadow-2xl dark:bg-slate-800">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                    <div>
                        <h2 id="groupModalTitle" class="text-xl font-semibold text-slate-900 dark:text-white">New
                            Team</h2>
                        <p class="mt-1 text-sm text-slate-500">Pick departments to narrow the candidate pool, then
                            check off specific people on the right to add as Members — only Members become
                            assignable to this Team's Projects.</p>
                    </div>
                    <button id="closeGroupModal" type="button"
                        class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="groupForm" class="flex min-h-0 flex-1 flex-col">
                    <input type="hidden" id="group_id" name="group_id">

                    <div class="flex min-h-0 flex-1">
                        {{-- LEFT: Team details --}}
                        <div class="w-full min-w-0 space-y-5 overflow-y-auto border-r border-slate-100 p-6 md:w-1/2 dark:border-slate-700">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Team
                                    Name</label>
                                <input id="group_name" name="group_name" type="text" required
                                    placeholder="e.g. Marketing Team"
                                    class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Description</label>
                                <textarea id="group_description" name="group_description" rows="3"
                                    class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></textarea>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Company</label>
                                <select id="group_cpny_filter" class="select2 w-full" multiple
                                    data-placeholder="Filter departments by company">
                                    @foreach ($companies as $c)
                                        <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }} - {{ $c->cpny_name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-slate-400">Narrows the Departments list below — leave empty to see all companies.</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Departments</label>
                                <select id="department_opr_id" name="department_opr_id[]" class="select2 w-full" multiple
                                    data-placeholder="Search and select departments" required>
                                    @foreach ($departmentOprs as $d)
                                        <option value="{{ $d->department_opr_id }}" data-cpny="{{ $d->cpny_id }}">
                                            {{ $d->cpny_id }} - {{ $d->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- RIGHT: Members panel --}}
                        <div class="hidden w-1/2 min-w-0 flex-col p-6 md:flex">
                            <div class="mb-2 flex items-center justify-between">
                                <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Members</label>
                                <span id="membersSelectedCount" class="text-xs text-slate-400">0 selected</span>
                            </div>
                            <input id="memberSearch" type="text" placeholder="Search name or username..."
                                class="mb-2 h-9 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            <div class="min-h-0 flex-1 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-600">
                                <table class="w-full text-sm">
                                    <thead class="sticky top-0 bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                        <tr>
                                            <th class="w-8 px-3 py-2"><input type="checkbox" id="memberSelectAll"></th>
                                            <th class="px-2 py-2 text-left font-medium">Name</th>
                                            <th class="px-2 py-2 text-left font-medium">Username</th>
                                            <th class="px-2 py-2 text-left font-medium">Dept</th>
                                        </tr>
                                    </thead>
                                    <tbody id="memberTableBody" class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
                                </table>
                            </div>
                            <p id="membersHint" class="mt-1 text-xs text-slate-400">Select at least one department to see candidates.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                        <button type="button" id="cancelGroupBtn"
                            class="h-10 rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">Cancel</button>
                        <button type="submit"
                            class="h-10 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white hover:bg-indigo-500">Save
                            Team</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const canManageGroups = @json($canManageGroups);

        function groupCard(row) {
            const maxTags = 3;
            const deptList = row.departments || [];
            const depts = deptList.slice(0, maxTags)
                .map(x => `<span class="inline-block rounded bg-indigo-50 px-1.5 py-0.5 text-[11px] text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">${x}</span>`)
                .join(' ');
            const moreDepts = deptList.length > maxTags
                ? `<span class="text-[11px] text-gray-400">+${deptList.length - maxTags} more</span>`
                : '';

            const editBtn = canManageGroups
                ? `<button class="edit-group-btn shrink-0 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-gray-700" data-id="${row.group_id}" title="Edit"><i class="fas fa-pen text-xs"></i></button>`
                : '';

            return $(`
                <div class="flex flex-col rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[11px] font-mono text-gray-400">${row.group_id}</p>
                            <h3 class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">${row.group_name}</h3>
                        </div>
                        ${editBtn}
                    </div>
                    <p class="mt-1 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">${row.group_description || '<span class="text-gray-300">No description.</span>'}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-1">${depts}${moreDepts}</div>
                    <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-2 text-[11px] text-gray-400 dark:border-gray-700">
                        <span>${row.member_count} member${row.member_count === 1 ? '' : 's'} · ${row.project_count} project${row.project_count === 1 ? '' : 's'}</span>
                        <span class="truncate">by ${row.created_by || '—'}</span>
                    </div>
                </div>
            `);
        }

        function loadGroups() {
            $.get('{{ route('project-groups.json') }}', function (res) {
                const rows = res.data || [];
                const $grid = $('#groupsGrid').empty();
                $('#groupsEmptyState').toggleClass('hidden', rows.length > 0);
                rows.forEach(row => $grid.append(groupCard(row)));
            });
        }

        $(function () {
            loadGroups();

            function initSelect2() {
                $('.select2').not('#department_opr_id').select2({
                    width: '100%',
                    allowClear: true,
                    closeOnSelect: false,
                    dropdownParent: $('#groupModal'),
                    placeholder: function () { return $(this).data('placeholder') || 'Search and select'; },
                });

                // Departments select gets its own init so it can filter its
                // dropdown list by whichever companies are picked in the
                // Company field above it.
                $('#department_opr_id').select2({
                    width: '100%',
                    allowClear: true,
                    closeOnSelect: false,
                    dropdownParent: $('#groupModal'),
                    placeholder: 'Search and select departments',
                    matcher: function (params, data) {
                        if (!data.id) return data;

                        const term = ($.trim(params.term || '')).toLowerCase();
                        if (term && data.text.toLowerCase().indexOf(term) === -1) return null;

                        const selectedCompanies = $('#group_cpny_filter').val() || [];
                        if (selectedCompanies.length && !selectedCompanies.includes($(data.element).data('cpny'))) {
                            return null;
                        }

                        return data;
                    },
                });
            }
            initSelect2();

            let suppressDeptReset = false;

            $('#group_cpny_filter').on('change', function () {
                if (suppressDeptReset) return;

                // Drop any selected departments that no longer match the
                // narrowed company filter, instead of leaving stale picks.
                const selectedCompanies = $(this).val() || [];
                if (!selectedCompanies.length) return;

                const keep = $('#department_opr_id').val() || [];
                const filtered = keep.filter(id => {
                    const opt = $(`#department_opr_id option[value="${id}"]`);
                    return selectedCompanies.includes(opt.data('cpny'));
                });
                if (filtered.length !== keep.length) {
                    $('#department_opr_id').val(filtered).trigger('change');
                }
            });

            // Members whose selection should be restored once the candidate
            // list for the edited Group's departments finishes loading.
            let pendingMemberSelection = null;

            function updateMembersSelectedCount() {
                const count = $('#memberTableBody input[type="checkbox"]:checked').length;
                $('#membersSelectedCount').text(count + ' selected');
            }

            function renderMemberTable(candidates, checkedUsernames) {
                const $body = $('#memberTableBody').empty();

                candidates.forEach(u => {
                    const checked = checkedUsernames.includes(u.username) ? 'checked' : '';
                    $body.append(`
                        <tr class="member-row hover:bg-slate-50 dark:hover:bg-slate-700/50" data-search="${(u.name + ' ' + u.username).toLowerCase()}">
                            <td class="px-3 py-1.5"><input type="checkbox" name="members[]" value="${u.username}" ${checked}></td>
                            <td class="px-2 py-1.5 text-slate-700 dark:text-slate-200">${u.name}</td>
                            <td class="px-2 py-1.5 text-slate-500 dark:text-slate-400">${u.username}</td>
                            <td class="px-2 py-1.5 text-slate-500 dark:text-slate-400">${u.department_id || '—'}</td>
                        </tr>
                    `);
                });

                $('#memberSelectAll').prop('checked', false);
                updateMembersSelectedCount();
            }

            $('#department_opr_id').on('change', function () {
                const selected = $(this).val() || [];

                if (selected.length === 0) {
                    $('#memberTableBody').empty();
                    $('#membersHint').text('Select at least one department to see candidates.');
                    pendingMemberSelection = null;
                    updateMembersSelectedCount();
                    return;
                }

                $('#membersHint').text('Loading candidates...');

                $.ajax({
                    url: '{{ route('project-groups.preview-eligible-users') }}',
                    method: 'POST',
                    data: { department_opr_id: selected, _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        const list = res.data || [];
                        const keepChecked = pendingMemberSelection
                            || $('#memberTableBody input[type="checkbox"]:checked').map(function () { return this.value; }).get();

                        renderMemberTable(list, keepChecked);
                        pendingMemberSelection = null;
                        $('#memberSearch').val('').trigger('input');

                        $('#membersHint').text(list.length
                            ? 'Only checked people become assignable to this Team\'s Projects.'
                            : 'No candidates found for these departments (need PROJECTACCESS + matching department).');
                    }
                });
            });

            $('#memberTableBody').on('change', 'input[type="checkbox"]', updateMembersSelectedCount);

            $('#memberSelectAll').on('change', function () {
                const checked = $(this).is(':checked');
                $('#memberTableBody tr.member-row:visible input[type="checkbox"]').prop('checked', checked);
                updateMembersSelectedCount();
            });

            $('#memberSearch').on('input', function () {
                const term = $(this).val().trim().toLowerCase();
                $('#memberTableBody tr.member-row').each(function () {
                    $(this).toggle(!term || $(this).data('search').includes(term));
                });
            });

            function resetForm() {
                $('#groupForm')[0].reset();
                $('#group_id').val('');
                $('#group_cpny_filter').val(null).trigger('change');
                pendingMemberSelection = null;
                $('#memberSearch').val('');
                $('#department_opr_id').val(null).trigger('change');
                $('#groupModalTitle').text('New Team');
            }

            $('#addGroupBtn').on('click', function () {
                resetForm();
                $('#groupModal').removeClass('hidden');
            });

            $('#closeGroupModal, #cancelGroupBtn').on('click', function () {
                $('#groupModal').addClass('hidden');
            });

            $('#groupsGrid').on('click', '.edit-group-btn', function () {
                const groupId = $(this).data('id');
                $.get(`{{ url('project-groups') }}/${groupId}/edit`, function (data) {
                    resetForm();
                    $('#group_id').val(data.group_id);
                    $('#group_name').val(data.group_name);
                    $('#group_description').val(data.group_description);

                    // Pre-select the Company filter from whichever companies the
                    // already-assigned departments belong to, without letting the
                    // company-change handler wipe the department selection below.
                    suppressDeptReset = true;
                    const companies = [...new Set((data.department_opr_id || []).map(id =>
                        $(`#department_opr_id option[value="${id}"]`).data('cpny')
                    ))].filter(Boolean);
                    $('#group_cpny_filter').val(companies).trigger('change');
                    suppressDeptReset = false;

                    pendingMemberSelection = data.members || [];
                    $('#department_opr_id').val(data.department_opr_id).trigger('change');
                    $('#groupModalTitle').text('Edit Team — ' + data.group_id);
                    $('#groupModal').removeClass('hidden');
                });
            });

            $('#groupForm').on('submit', function (e) {
                e.preventDefault();

                const groupId = $('#group_id').val();
                const url = groupId ? `{{ url('project-groups') }}/${groupId}` : '{{ route('project-groups.store') }}';
                const method = groupId ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    method: method,
                    data: $(this).serialize() + '&_token={{ csrf_token() }}',
                    success: function (res) {
                        $('#groupModal').addClass('hidden');
                        loadGroups();
                        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Something went wrong.';
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
