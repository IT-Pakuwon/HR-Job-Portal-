<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'project-archive' ? 'Project Archive' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2" x-data="projectArchive()" x-init="load()">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06] sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        <i class="fas fa-box-archive mr-1.5 text-gray-400"></i> Project Archive
                    </h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Projects archived from the Projects module. Restoring puts one back on its Teams' boards.</p>
                </div>
                <div class="relative sm:w-72">
                    <i class="fas fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                    <input type="text" x-model="search" placeholder="Search project, team or person…"
                        class="w-full rounded-lg border border-gray-200 py-2 pl-8 pr-8 text-sm placeholder:text-gray-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-gray-800 dark:text-gray-100 dark:focus:ring-indigo-500/20">
                    <button type="button" x-show="search" @click="search = ''" title="Clear"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-left text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-5 py-3 font-semibold">Project</th>
                            <th class="px-5 py-3 font-semibold">Team(s)</th>
                            <th class="px-5 py-3 font-semibold">Created by</th>
                            <th class="px-5 py-3 font-semibold">Archived by</th>
                            <th class="px-5 py-3 font-semibold">Archived at</th>
                            <th class="px-5 py-3 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="p in filtered" :key="p.project_id">
                            <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/60 dark:border-white/[0.04] dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800 dark:text-gray-100" x-text="p.project_name"></p>
                                    <p class="text-xs text-gray-400">
                                        <span x-text="p.project_id"></span>
                                        <span x-show="p.task_count" x-text="`· + ${p.task_count} task(s)`"></span>
                                    </p>
                                </td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">
                                    <span x-text="p.teams.length ? p.teams.join(', ') : '—'"></span>
                                </td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300" x-text="p.created_by || '—'"></td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300" x-text="p.archived_by || '—'"></td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400" x-text="p.archived_at || '—'"></td>
                                <td class="px-5 py-3 text-right">
                                    <button type="button" @click="restore(p)"
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
                                x-text="rows.length ? 'No archived project matches your search.' : 'No archived projects.'"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function projectArchive() {
            return {
                rows: [],
                search: '',
                loading: true,

                get filtered() {
                    const q = this.search.trim().toLowerCase();
                    if (!q) return this.rows;
                    return this.rows.filter(p => [p.project_name, p.project_id, p.created_by, p.archived_by, ...p.teams]
                        .some(v => (v || '').toLowerCase().includes(q)));
                },

                load() {
                    this.loading = true;
                    $.get('{{ route('project-archive.json') }}', (rows) => {
                        this.rows = rows;
                        this.loading = false;
                    });
                },

                restore(p) {
                    Swal.fire({
                        icon: 'question',
                        title: 'Restore this project?',
                        html: `<b>${$('<div>').text(p.project_name).html()}</b> will show again on its Teams' boards${p.task_count ? `, together with the ${p.task_count} task(s) archived with it` : ''}.`,
                        showCancelButton: true,
                        confirmButtonText: 'Restore',
                        confirmButtonColor: '#059669',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: `{{ url('project-archive') }}/${encodeURIComponent(p.project_id)}/restore`,
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
