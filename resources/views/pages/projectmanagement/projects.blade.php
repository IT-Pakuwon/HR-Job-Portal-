<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2" x-data="pmPortfolio('{{ $initialTab }}')">

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                <div class="flex items-center gap-3">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">📁 Projects</h2>
                    <select id="groupFilter" x-model="groupId" @change="loadBoard()"
                        class="h-9 rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">All my Teams</option>
                        <template x-for="g in groups" :key="g.group_id">
                            <option :value="g.group_id" x-text="g.group_name"></option>
                        </template>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('project-groups.index') }}"
                        class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Manage Teams
                    </a>
                    <button @click="openNewProject()"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500">
                        + New Project
                    </button>
                </div>
            </div>

            {{-- TABS --}}
            <div class="flex items-center gap-1 border-b border-gray-100 px-5 pt-2 dark:border-white/[0.06]">
                <button @click="tab = 'kanban'; renderTab()"
                    :class="tab === 'kanban' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">By Status</button>
                <button @click="tab = 'cards'; renderTab()"
                    :class="tab === 'cards' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">All Projects</button>
                <button @click="tab = 'gantt'; renderTab()"
                    :class="tab === 'gantt' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">Gantt</button>
            </div>

            <div class="flex gap-4 p-4">
                <div id="projectsSidePanel"
                    class="hidden w-56 shrink-0 space-y-0.5 overflow-y-auto border-r border-gray-100 pr-4 dark:border-gray-700"
                    style="max-height: 70vh"></div>
                <div class="min-w-0 flex-1">
                    <div id="kanbanPanel" class="overflow-x-auto"></div>
                    <div id="cardsPanel" class="hidden grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"></div>
                    <div id="ganttPanel" class="hidden"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- NEW PROJECT MODAL --}}
    <div id="projectModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex w-full max-w-lg flex-col overflow-hidden rounded-lg bg-white shadow-2xl dark:bg-slate-800">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-white">New Project</h2>
                    <button id="closeProjectModal" type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fas fa-times"></i></button>
                </div>
                <form id="projectForm" class="flex flex-col">
                    <div class="space-y-4 p-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Team</label>
                            <select id="project_group_id" name="group_id" required
                                class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"></select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Project Name</label>
                            <input id="project_name" name="project_name" type="text" required
                                class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Description</label>
                            <textarea id="project_description" name="project_description" rows="3"
                                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Start Date</label>
                                <input id="project_start_date" name="start_date" type="date"
                                    class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">End Date</label>
                                <input id="project_end_date" name="end_date" type="date"
                                    class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                        <button type="button" id="cancelProjectBtn" class="h-10 rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="h-10 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white hover:bg-indigo-500">Create Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function pmPortfolio(initialTab) {
            return {
                tab: initialTab,
                groupId: '',
                groups: [],
                statuses: [],
                projects: [],

                init() {
                    this.loadBoard();
                },

                loadBoard() {
                    $.get('{{ route('projects.board-data') }}', { group_id: this.groupId }, (res) => {
                        this.groups = res.groups;
                        this.statuses = res.statuses;
                        this.projects = res.projects;

                        const groupSelect = $('#project_group_id').empty();
                        res.groups.forEach(g => groupSelect.append(`<option value="${g.group_id}">${g.group_name}</option>`));

                        this.renderTab();
                    });
                },

                renderTab() {
                    $('#kanbanPanel, #cardsPanel, #ganttPanel').addClass('hidden');
                    if (this.tab === 'kanban') { $('#kanbanPanel').removeClass('hidden'); this.renderKanban(); }
                    if (this.tab === 'cards') { $('#cardsPanel').removeClass('hidden'); this.renderCards(); }
                    if (this.tab === 'gantt') { $('#ganttPanel').removeClass('hidden'); this.renderGantt(); }
                    this.renderSidePanel();
                },

                // Persistent list of every Project in the current Team scope,
                // shown alongside the Kanban/Gantt boards (which group by
                // status/time rather than a flat list) so users always know
                // which project they're looking at / adding tasks under.
                renderSidePanel() {
                    const panel = $('#projectsSidePanel');

                    if (this.tab === 'cards') {
                        panel.addClass('hidden');
                        return;
                    }

                    panel.removeClass('hidden').empty();
                    panel.append('<p class="mb-1 px-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Projects</p>');

                    if (!this.projects.length) {
                        panel.append('<p class="px-2 text-xs text-gray-400">No projects yet.</p>');
                        return;
                    }

                    this.projects.forEach(p => {
                        panel.append(`
                            <a href="{{ url('projects') }}/${p.project_id}"
                                class="flex items-center gap-2 truncate rounded-lg px-2 py-1.5 text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">
                                <span class="h-2 w-2 shrink-0 rounded-full" style="background:${this.statusColor(p.status_id)}"></span>
                                <span class="truncate">${p.project_name}</span>
                            </a>
                        `);
                    });
                },

                statusColor(statusId) {
                    const s = this.statuses.find(s => s.status_id === statusId);
                    return s ? s.color : '#9CA3AF';
                },

                statusName(statusId) {
                    const s = this.statuses.find(s => s.status_id === statusId);
                    return s ? s.status_name : 'No status';
                },

                renderKanban() {
                    const panel = $('#kanbanPanel').empty();
                    const wrap = $('<div class="flex gap-4 min-w-max pb-2"></div>');

                    this.statuses.forEach(status => {
                        const items = this.projects.filter(p => p.status_id === status.status_id);
                        const col = $(`
                            <div class="w-72 shrink-0 rounded-lg bg-gray-50 dark:bg-gray-900/40">
                                <div class="flex items-center gap-2 px-3 py-2.5">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background:${status.color}"></span>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">${status.status_name}</span>
                                    <span class="text-xs text-gray-400">${items.length}</span>
                                </div>
                                <div class="kanban-col space-y-2 px-2 pb-2 min-h-[40px]" data-status-id="${status.status_id}"></div>
                            </div>
                        `);

                        const list = col.find('.kanban-col');
                        items.forEach(p => list.append(this.projectCard(p)));

                        wrap.append(col);
                    });

                    wrap.append(`
                        <div class="w-72 shrink-0">
                            <button id="addStatusBtn" class="w-full rounded-lg border-2 border-dashed border-gray-200 px-3 py-2.5 text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 dark:border-gray-700">
                                + Add status
                            </button>
                        </div>
                    `);

                    panel.append(wrap);

                    panel.find('.kanban-col').each((i, el) => {
                        Sortable.create(el, {
                            group: 'projects-kanban',
                            animation: 150,
                            onEnd: (evt) => {
                                const projectId = evt.item.dataset.projectId;
                                const statusId = evt.to.dataset.statusId;
                                $.post(`{{ url('projects') }}/${projectId}/status`, { status_id: statusId, _token: '{{ csrf_token() }}' });
                            }
                        });
                    });
                },

                projectCard(p) {
                    return $(`
                        <a href="{{ url('projects') }}/${p.project_id}" data-project-id="${p.project_id}"
                            class="block cursor-move rounded-lg border border-gray-200 bg-white p-3 shadow-sm hover:shadow dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">${p.project_name}</p>
                            <div class="mt-2 h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-1.5 rounded-full bg-indigo-500" style="width:${p.progress_percent}%"></div>
                            </div>
                        </a>
                    `);
                },

                renderCards() {
                    const panel = $('#cardsPanel').empty();
                    if (this.projects.length === 0) {
                        panel.append('<p class="col-span-full text-sm text-gray-400">No projects yet.</p>');
                        return;
                    }
                    this.projects.forEach(p => {
                        panel.append(`
                            <a href="{{ url('projects') }}/${p.project_id}"
                                class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:shadow dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-center justify-between">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium text-white" style="background:${this.statusColor(p.status_id)}">${this.statusName(p.status_id)}</span>
                                    <span class="text-xs text-gray-400">${p.progress_percent}%</span>
                                </div>
                                <p class="mt-2 font-semibold text-gray-800 dark:text-gray-100">${p.project_name}</p>
                                <p class="mt-1 text-xs text-gray-500 line-clamp-2">${p.project_description || ''}</p>
                                <p class="mt-2 text-xs text-gray-400">${p.start_date || '—'} → ${p.end_date || '—'}</p>
                            </a>
                        `);
                    });
                },

                renderGantt() {
                    const container = $('#ganttPanel').empty();
                    if (this.projects.length === 0) {
                        container.append('<p class="text-sm text-gray-400">No projects yet.</p>');
                        return;
                    }
                    container.append('<svg id="ganttSvg"></svg>');

                    const tasks = this.projects
                        .filter(p => p.start_date && p.end_date)
                        .map(p => ({
                            id: p.project_id,
                            name: p.project_name,
                            start: p.start_date,
                            end: p.end_date,
                            progress: p.progress_percent,
                        }));

                    if (tasks.length === 0) {
                        container.append('<p class="text-sm text-gray-400 mt-2">No projects with both a start and end date yet.</p>');
                        return;
                    }

                    new FrappeGantt('#ganttSvg', tasks, {
                        on_click: (task) => { window.location.href = `{{ url('projects') }}/${task.id}`; },
                    });
                },

                openNewProject() {
                    $('#projectForm')[0].reset();
                    if (this.groupId) $('#project_group_id').val(this.groupId);
                    $('#projectModal').removeClass('hidden');
                },
            };
        }

        $(document).on('click', '#closeProjectModal, #cancelProjectBtn', function () {
            $('#projectModal').addClass('hidden');
        });

        $(document).on('submit', '#projectForm', function (e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route('projects.store') }}',
                method: 'POST',
                data: $(this).serialize() + '&_token={{ csrf_token() }}',
                success: function (res) {
                    $('#projectModal').addClass('hidden');
                    Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false })
                        .then(() => window.location.reload());
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                }
            });
        });

        $(document).on('click', '#addStatusBtn', function () {
            Swal.fire({
                title: 'Add status column',
                input: 'text',
                inputPlaceholder: 'e.g. Blocked',
                showCancelButton: true,
            }).then((result) => {
                if (!result.isConfirmed || !result.value) return;
                $.post('{{ route('projects.statuses.store') }}', { status_name: result.value, _token: '{{ csrf_token() }}' }, function () {
                    window.location.reload();
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
