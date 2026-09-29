<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'task-archive' ? 'Task Archive' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2" x-data="taskArchive()" x-init="load()">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        <i class="fas fa-box-archive mr-1.5 text-gray-400"></i> Task Archive
                    </h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Archived tasks (hidden from Team and Project boards) and cancelled Team tasks. Restoring an archived task also restores the subtasks archived with it.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                    {{-- Status segmented toggle --}}
                    <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-white/5">
                        <template x-for="opt in kindOptions" :key="opt.value">
                            <button type="button" @click="kind = opt.value"
                                class="flex items-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1.5 text-xs font-medium transition"
                                :class="kind === opt.value
                                    ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-gray-100'
                                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                                <span x-show="opt.dot" class="h-1.5 w-1.5 rounded-full" :class="opt.dot"></span>
                                <span x-text="opt.label"></span>
                                <span class="rounded-full px-1.5 text-[10px] font-semibold tabular-nums"
                                    :class="kind === opt.value ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300' : 'bg-gray-200/70 text-gray-500 dark:bg-white/10 dark:text-gray-400'"
                                    x-text="count('kind', opt.value)"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Source segmented toggle --}}
                    <div class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-white/5">
                        <template x-for="opt in sourceOptions" :key="opt.value">
                            <button type="button" @click="source = opt.value"
                                class="flex items-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1.5 text-xs font-medium transition"
                                :class="source === opt.value
                                    ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-gray-100'
                                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'">
                                <i x-show="opt.icon" class="fas text-[10px]" :class="opt.icon"></i>
                                <span x-text="opt.label"></span>
                            </button>
                        </template>
                    </div>

                    <div class="relative sm:w-64">
                        <i class="fas fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                        <input type="text" x-model="search" placeholder="Search task, team/project or person…"
                            class="w-full rounded-lg border border-gray-200 py-2 pl-8 pr-8 text-sm placeholder:text-gray-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100 dark:focus:ring-indigo-500/20">
                        <button type="button" x-show="search" @click="search = ''" title="Clear"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-left text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-5 py-3 font-semibold">Task</th>
                            <th class="px-5 py-3 font-semibold">Team / Project</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">By</th>
                            <th class="px-5 py-3 font-semibold">When</th>
                            <th class="px-5 py-3 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="t in filtered" :key="`${t.kind}-${t.source}-${t.task_id}`">
                            <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/60 dark:border-white/[0.04] dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800 dark:text-gray-100" x-text="t.task_name"></p>
                                    <p class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-gray-400">
                                        <span x-text="t.task_id"></span>
                                        <span x-show="t.is_subtask" class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-gray-500 dark:bg-white/10 dark:text-gray-400">Subtask</span>
                                        <span x-show="t.subtask_count > 0" x-text="`+ ${t.subtask_count} subtask(s)`"></span>
                                    </p>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="mr-1.5 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                                        :class="t.source === 'TEAM' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300'"
                                        x-text="t.source === 'TEAM' ? 'Team' : 'Project'"></span>
                                    <span class="text-gray-700 dark:text-gray-200" x-text="t.container_name"></span>
                                    <span x-show="t.container_archived" class="ml-1 text-xs text-red-500" title="This Team/Project is itself archived — the task won't be visible until it's restored too.">(archived)</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                                        :class="t.kind === 'CANCELLED' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'"
                                        x-text="t.kind === 'CANCELLED' ? 'Cancelled' : 'Archived'"></span>
                                </td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300" x-text="t.archived_by || '—'"></td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400" x-text="t.archived_at || '—'"></td>
                                <td class="px-5 py-3 text-right">
                                    <button type="button" @click="restore(t)"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-900/20">
                                        <i class="fas fa-rotate-left text-[10px]"></i> Restore
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="loading">
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">Loading…</td>
                        </tr>
                        <tr x-show="!loading && !filtered.length">
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400"
                                x-text="rows.length ? 'No task matches your filter.' : 'No archived or cancelled tasks.'"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function taskArchive() {
            return {
                rows: [],
                search: '',
                source: '',
                kind: '',
                loading: true,

                kindOptions: [
                    { value: '', label: 'All' },
                    { value: 'ARCHIVED', label: 'Archived', dot: 'bg-gray-400' },
                    { value: 'CANCELLED', label: 'Cancelled', dot: 'bg-amber-400' },
                ],
                sourceOptions: [
                    { value: '', label: 'All sources' },
                    { value: 'TEAM', label: 'Team', icon: 'fa-user-group' },
                    { value: 'PROJECT', label: 'Project', icon: 'fa-folder' },
                ],

                // Status pill counts follow the current source filter, so
                // they always match what clicking the pill would show.
                count(field, value) {
                    return this.rows.filter(t => (!this.source || t.source === this.source) && (!value || t[field] === value)).length;
                },

                get filtered() {
                    const q = this.search.trim().toLowerCase();
                    return this.rows.filter(t => (!this.source || t.source === this.source) && (!this.kind || t.kind === this.kind)
                        && (!q || [t.task_name, t.task_id, t.container_name, t.archived_by].some(v => (v || '').toLowerCase().includes(q))));
                },

                load() {
                    this.loading = true;
                    $.get('{{ route('task-archive.json') }}', (rows) => {
                        this.rows = rows;
                        this.loading = false;
                    });
                },

                restore(t) {
                    const esc = (s) => $('<div>').text(s).html();
                    const extra = t.subtask_count > 0 ? ` along with <b>${t.subtask_count}</b> subtask(s) archived with it` : '';
                    const warn = t.container_archived ? `<br><br><span style="color:#DC2626">Its ${t.source === 'TEAM' ? 'Team' : 'Project'} is archived too — restore that as well for the task to be visible.</span>` : '';
                    const body = t.kind === 'CANCELLED'
                        ? `<b>${esc(t.task_name)}</b> on <b>${esc(t.container_name)}</b> will no longer be cancelled and counts toward progress again.`
                        : `<b>${esc(t.task_name)}</b> will show again on <b>${esc(t.container_name)}</b>'s board${extra}.`;

                    Swal.fire({
                        icon: 'question',
                        title: t.kind === 'CANCELLED' ? 'Restore this cancelled task?' : 'Restore this archived task?',
                        html: body + warn,
                        showCancelButton: true,
                        confirmButtonText: 'Restore',
                        confirmButtonColor: '#059669',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: `{{ url('task-archive') }}/${t.source}/${encodeURIComponent(t.task_id)}/restore`,
                            method: 'PUT',
                            data: { _token: '{{ csrf_token() }}' },
                            success: (res) => {
                                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                                this.load();
                            },
                            error: (xhr) => {
                                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                            },
                        });
                    });
                },
            };
        }
    </script>
</x-app-layout>
