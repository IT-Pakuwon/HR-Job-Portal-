<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'project-status.index' ? 'Status Master' : '';
        $inputCls = 'w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100';
        $btnPrimary = 'inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-indigo-700 disabled:opacity-50';
        $th = 'px-5 py-3 font-semibold';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2" x-data="statusMaster()" x-init="init()">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                    <i class="fas fa-list-check mr-1.5 text-gray-400"></i> Status Master
                </h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">View and manage the statuses behind the Team, Project and Task boards. A status that still has projects or tasks in it can't be deleted.</p>

                <div class="mt-3 flex gap-1 text-sm">
                    <template x-for="t in tabs" :key="t.key">
                        <button type="button" @click="tab = t.key"
                            :class="tab === t.key ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'"
                            class="rounded-lg px-3 py-1.5 font-medium transition" x-text="t.label"></button>
                    </template>
                </div>
            </div>

            {{-- ── Tab 1: Project statuses ── --}}
            <div x-show="tab === 'project'" x-cloak>
                <div class="flex items-center justify-between px-5 py-3">
                    <p class="text-xs text-gray-500">Global Project statuses — the Kanban columns.</p>
                    <button type="button" @click="openForm('project')" class="{{ $btnPrimary }}"><i class="fas fa-plus text-[10px]"></i> Add status</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-left text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="{{ $th }}">Status</th>
                                <th class="{{ $th }}">Order</th>
                                <th class="{{ $th }}">Projects</th>
                                <th class="{{ $th }}">Teams using</th>
                                <th class="{{ $th }}">State</th>
                                <th class="{{ $th }} text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="s in projectRows" :key="s.status_id">
                                <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/60 dark:border-white/[0.04] dark:hover:bg-white/[0.02]" :class="s.active ? '' : 'opacity-50'">
                                    <td class="px-5 py-3">
                                        <span class="mr-2 inline-block h-2.5 w-2.5 rounded-full align-middle" :style="`background:${s.color || '#9ca3af'}`"></span>
                                        <span class="font-medium text-gray-800 dark:text-gray-100" x-text="s.status_name"></span>
                                        <span class="ml-1 text-xs text-gray-400" x-text="s.status_id"></span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300" x-text="s.sort_order"></td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300" x-text="s.projects"></td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300" x-text="s.teams"></td>
                                    <td class="px-5 py-3 text-xs" x-text="s.active ? 'Active' : 'Deleted'"></td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right">
                                        <button type="button" @click="openForm('project', s)" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-200">Edit</button>
                                        <button type="button" @click="toggle(projectUrl(s.status_id) + '/toggle', s, 'project')"
                                            :class="s.active ? 'border-red-200 text-red-600 hover:bg-red-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'"
                                            class="ml-1 rounded-lg border px-3 py-1.5 text-xs font-medium" x-text="s.active ? 'Delete' : 'Restore'"></button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!projectRows.length"><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400" x-text="loading ? 'Loading…' : 'No statuses.'"></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Tab 2: statuses per Team ── --}}
            <div x-show="tab === 'team'" x-cloak class="px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="sm:w-80">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Team</label>
                        <select x-model="teamId" @change="loadTeamStatuses()" class="{{ $inputCls }}">
                            <option value="">Select a Team…</option>
                            <template x-for="t in teams" :key="t.team_id"><option :value="t.team_id" x-text="t.team_name"></option></template>
                        </select>
                    </div>
                    <button type="button" x-show="teamId" @click="saveTeamStatuses()" class="{{ $btnPrimary }}"><i class="fas fa-floppy-disk text-[10px]"></i> Save</button>
                </div>
                <div x-show="teamId" class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="s in projectRows.filter(r => r.active)" :key="s.status_id">
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-white/10">
                            <input type="checkbox" :value="s.status_id" x-model="teamStatusIds" class="rounded border-gray-300 text-indigo-600">
                            <span class="inline-block h-2.5 w-2.5 rounded-full" :style="`background:${s.color || '#9ca3af'}`"></span>
                            <span class="text-gray-800 dark:text-gray-100" x-text="s.status_name"></span>
                        </label>
                    </template>
                </div>
                <p x-show="!teamId" class="mt-6 text-sm text-gray-400">Pick a Team to choose which Project statuses appear as its Kanban columns.</p>
            </div>

            {{-- ── Tab 3: task statuses ── --}}
            <div x-show="tab === 'task'" x-cloak>
                <div class="flex flex-col gap-3 px-5 py-3 sm:flex-row sm:items-end">
                    <div class="sm:w-40">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Belongs to</label>
                        <select x-model="taskKind" @change="taskOwner = ''; taskRows = []; loadOwners()" class="{{ $inputCls }}">
                            <option value="team">Team</option>
                            <option value="project">Project</option>
                        </select>
                    </div>
                    <div class="sm:w-80">
                        <label class="mb-1 block text-xs font-medium text-gray-500" x-text="taskKind === 'team' ? 'Team' : 'Project'"></label>
                        <select x-model="taskOwner" @change="loadTaskStatuses()" class="{{ $inputCls }}">
                            <option value="">Select…</option>
                            <template x-for="o in owners" :key="o.id"><option :value="o.id" x-text="o.name"></option></template>
                        </select>
                    </div>
                    <button type="button" x-show="taskOwner" @click="openForm('task')" class="{{ $btnPrimary }} sm:ml-auto"><i class="fas fa-plus text-[10px]"></i> Add status</button>
                </div>
                <div class="overflow-x-auto" x-show="taskOwner">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-left text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="{{ $th }}">Status</th>
                                <th class="{{ $th }}">Order</th>
                                <th class="{{ $th }}">Tasks</th>
                                <th class="{{ $th }}">State</th>
                                <th class="{{ $th }} text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="s in taskRows" :key="s.status_id">
                                <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/60 dark:border-white/[0.04] dark:hover:bg-white/[0.02]" :class="s.active ? '' : 'opacity-50'">
                                    <td class="px-5 py-3">
                                        <span class="mr-2 inline-block h-2.5 w-2.5 rounded-full align-middle" :style="`background:${s.color || '#9ca3af'}`"></span>
                                        <span class="font-medium text-gray-800 dark:text-gray-100" x-text="s.status_name"></span>
                                        <span class="ml-1 text-xs text-gray-400" x-text="s.status_id"></span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300" x-text="s.sort_order"></td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300" x-text="s.tasks"></td>
                                    <td class="px-5 py-3 text-xs" x-text="s.active ? 'Active' : 'Deleted'"></td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right">
                                        <button type="button" @click="openForm('task', s)" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-200">Edit</button>
                                        <button type="button" @click="toggle(taskUrl(s.status_id) + '/toggle', s, 'task')"
                                            :class="s.active ? 'border-red-200 text-red-600 hover:bg-red-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'"
                                            class="ml-1 rounded-lg border px-3 py-1.5 text-xs font-medium" x-text="s.active ? 'Delete' : 'Restore'"></button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!taskRows.length"><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">No statuses for this owner.</td></tr>
                        </tbody>
                    </table>
                </div>
                <p x-show="!taskOwner" class="px-5 pb-6 text-sm text-gray-400">Pick a Team or Project to see its task statuses.</p>
            </div>
        </div>

        {{-- ── Add / edit modal (shared) ── --}}
        <div x-show="form.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="form.open = false">
            <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl dark:bg-gray-800" @click.outside="form.open = false">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="form.editing ? 'Edit status' : 'Add status'"></h3>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Name</label>
                        <input type="text" x-model="form.status_name" maxlength="100" class="{{ $inputCls }}">
                    </div>
                    <div x-show="!form.editing">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Status ID <span class="text-gray-400">(optional — derived from the name)</span></label>
                        <input type="text" x-model="form.status_id" maxlength="20" class="{{ $inputCls }}">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500">Color</label>
                            <input type="color" x-model="form.color" class="h-9 w-full rounded-lg border border-gray-200 dark:border-white/10">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500">Order</label>
                            <input type="number" min="0" x-model="form.sort_order" placeholder="auto" class="{{ $inputCls }}">
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="form.open = false" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-600 dark:border-white/10 dark:text-gray-300">Cancel</button>
                    <button type="button" @click="submitForm()" :disabled="form.saving" class="{{ $btnPrimary }}">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function statusMaster() {
            const base = '{{ url('project-status') }}';
            const csrf = '{{ csrf_token() }}';

            const fail = (xhr) => Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
            const ok = (res) => Swal.fire({ icon: 'success', title: res.message, timer: 1200, showConfirmButton: false });

            return {
                tabs: [
                    { key: 'project', label: 'Project Status' },
                    { key: 'team', label: 'Status per Team' },
                    { key: 'task', label: 'Task Status' },
                ],
                tab: 'project',
                loading: true,
                projectRows: [],
                teams: [],
                teamId: '',
                teamStatusIds: [],
                taskKind: 'team',
                taskOwner: '',
                owners: [],
                taskRows: [],
                form: { open: false, kind: '', editing: false, id: '', status_name: '', status_id: '', color: '#56b1f3', sort_order: '', saving: false },

                init() {
                    this.loadProject();
                    $.get(base + '/teams', (rows) => { this.teams = rows; this.loadOwners(); });
                },

                projectUrl(id) { return `${base}/project-statuses/${encodeURIComponent(id)}`; },
                taskUrl(id) { return `${base}/task/${this.taskKind}/${encodeURIComponent(this.taskOwner)}` + (id ? `/${encodeURIComponent(id)}` : ''); },

                loadProject() {
                    this.loading = true;
                    $.get(base + '/project-statuses', (rows) => { this.projectRows = rows; this.loading = false; });
                },

                loadOwners() {
                    if (this.taskKind === 'team') {
                        this.owners = this.teams.map(t => ({ id: t.team_id, name: t.team_name }));
                    } else {
                        $.get(base + '/projects', (rows) => { this.owners = rows.map(p => ({ id: p.project_id, name: p.project_name })); });
                    }
                },

                loadTeamStatuses() {
                    this.teamStatusIds = [];
                    if (!this.teamId) return;
                    $.get(`${base}/teams/${encodeURIComponent(this.teamId)}/statuses`, (ids) => { this.teamStatusIds = ids; });
                },

                saveTeamStatuses() {
                    $.ajax({
                        url: `${base}/teams/${encodeURIComponent(this.teamId)}/statuses`, method: 'PUT',
                        data: { _token: csrf, status_ids: this.teamStatusIds },
                        success: (res) => { ok(res); this.loadProject(); },
                        error: (xhr) => { fail(xhr); this.loadTeamStatuses(); },
                    });
                },

                loadTaskStatuses() {
                    this.taskRows = [];
                    if (!this.taskOwner) return;
                    $.get(this.taskUrl(''), (rows) => { this.taskRows = rows; });
                },

                openForm(kind, row = null) {
                    this.form = {
                        open: true, kind, editing: !!row, id: row ? row.status_id : '',
                        status_name: row ? row.status_name : '', status_id: '',
                        color: row && row.color ? row.color : '#56b1f3',
                        sort_order: row ? row.sort_order : '', saving: false,
                    };
                },

                submitForm() {
                    const f = this.form;
                    if (!f.status_name.trim()) return fail({ responseJSON: { message: 'Name is required.' } });

                    let url, method;
                    if (f.kind === 'project') {
                        url = f.editing ? this.projectUrl(f.id) : base + '/project-statuses';
                    } else {
                        url = f.editing ? this.taskUrl(f.id) : this.taskUrl('');
                    }
                    method = f.editing ? 'PUT' : 'POST';

                    const data = { _token: csrf, status_name: f.status_name, color: f.color };
                    if (f.sort_order !== '' && f.sort_order !== null) data.sort_order = f.sort_order;
                    if (!f.editing && f.status_id) data.status_id = f.status_id;

                    f.saving = true;
                    $.ajax({
                        url, method, data,
                        success: (res) => { f.open = false; ok(res); f.kind === 'project' ? this.loadProject() : this.loadTaskStatuses(); },
                        error: (xhr) => { f.saving = false; fail(xhr); },
                    });
                },

                toggle(url, row, kind) {
                    const verb = row.active ? 'Delete' : 'Restore';
                    Swal.fire({
                        icon: 'question', title: `${verb} this status?`,
                        html: `<b>${$('<div>').text(row.status_name).html()}</b>`,
                        showCancelButton: true, confirmButtonText: verb,
                        confirmButtonColor: row.active ? '#dc2626' : '#059669',
                    }).then((r) => {
                        if (!r.isConfirmed) return;
                        $.ajax({
                            url, method: 'POST', data: { _token: csrf },
                            success: (res) => { ok(res); kind === 'project' ? this.loadProject() : this.loadTaskStatuses(); },
                            error: fail,
                        });
                    });
                },
            };
        }
    </script>
</x-app-layout>
