<x-app-layout>
    <div class="mx-auto flex h-[calc(100dvh-72px)] w-full max-w-9xl gap-4 p-2" x-data="pmPortfolio('{{ $initialTab }}')">

        {{-- LEFT NAV: Teams / Projects --}}
        <div class="h-full shrink-0 transition-all duration-200" :class="sidebarOpen ? 'w-64' : 'w-14'">
            <div class="h-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div class="flex items-center gap-2 px-3 py-3" :class="sidebarOpen ? 'justify-between' : 'flex-col'">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
                        <i class="fas fa-diagram-project text-xs"></i>
                    </span>
                    <h2 x-show="sidebarOpen" x-cloak class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-800 dark:text-gray-100">Task Management</h2>
                    <button @click="sidebarOpen = !sidebarOpen" title="Toggle sidebar"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                        <i class="fas text-xs" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
                    </button>
                </div>

                <div x-show="sidebarOpen" x-cloak class="border-t border-gray-100 p-3 dark:border-white/[0.06]">
                    <button @click="openNewProject()"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500">
                        <i class="fas fa-plus text-xs"></i> New Project
                    </button>
                </div>

                <div x-show="sidebarOpen" x-cloak class="border-t border-gray-100 p-3 dark:border-white/[0.06]">
                    <p class="mb-2 flex items-center gap-1.5 px-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        <i class="fas fa-star text-[10px] text-amber-400"></i> My Favorite
                    </p>
                    <div class="space-y-0.5">
                        <template x-for="f in favoriteItems" :key="`${f.type}-${f.id}`">
                            <div class="group flex items-center rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                                <template x-if="f.type === 'team'">
                                    <button @click="selectTeam(f.id)"
                                        class="flex min-w-0 flex-1 items-center gap-2 truncate px-2.5 py-2 text-left text-sm transition"
                                        :class="teamId === f.id ? 'font-medium text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300'">
                                        <i class="fas fa-user-group text-xs" :class="teamId === f.id ? 'text-indigo-500' : 'text-gray-400'"></i>
                                        <span class="truncate" x-text="f.name"></span>
                                    </button>
                                </template>
                                <template x-if="f.type === 'project'">
                                    <button @click="openProjectDetail(f.id, 'push')"
                                        class="flex min-w-0 flex-1 items-center gap-2 truncate px-2.5 py-2 text-left text-sm text-gray-600 dark:text-gray-300">
                                        <span class="h-2 w-2 shrink-0 rounded-full" :style="`background:${statusColor(f.status_id)}`"></span>
                                        <span class="truncate" x-text="f.name"></span>
                                    </button>
                                </template>
                                <button @click.stop.prevent="toggleFavorite(f.type === 'team' ? 'TEAM' : 'PROJECT', f.id)" title="Unfavorite"
                                    class="shrink-0 px-2 py-2 text-xs text-amber-400 transition hover:text-amber-500">
                                    <i class="fas fa-star"></i>
                                </button>
                            </div>
                        </template>
                        <p x-show="!favoriteItems.length" class="px-2.5 py-1.5 text-xs text-gray-400">Star a team or project to pin it here.</p>
                    </div>
                </div>

                <div x-show="sidebarOpen" x-cloak class="border-t border-gray-100 p-3 dark:border-white/[0.06]">
                    <p class="mb-2 flex items-center gap-1.5 px-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        <i class="fas fa-users text-[10px]"></i> Teams
                    </p>
                    <div class="space-y-0.5">
                        <template x-for="t in teams" :key="t.team_id">
                            <div class="group flex items-center rounded-lg"
                                :class="teamId === t.team_id ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'hover:bg-gray-50 dark:hover:bg-gray-800'">
                                <button @click="selectTeam(t.team_id)"
                                    class="flex min-w-0 flex-1 items-center gap-2 truncate px-2.5 py-2 text-left text-sm transition"
                                    :class="teamId === t.team_id ? 'font-medium text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300'">
                                    <i class="fas fa-user-group text-xs" :class="teamId === t.team_id ? 'text-indigo-500' : 'text-gray-400'"></i>
                                    <span class="truncate" x-text="t.team_name"></span>
                                </button>
                                <button @click.stop="toggleFavorite('TEAM', t.team_id)" title="Favorite"
                                    class="shrink-0 px-2 py-2 text-xs transition"
                                    :class="t.is_favorite ? 'text-amber-400' : 'text-gray-300 opacity-0 group-hover:opacity-100 hover:text-amber-400 dark:text-gray-600'">
                                    <i :class="t.is_favorite ? 'fas' : 'far'" class="fa-star"></i>
                                </button>
                            </div>
                        </template>
                        <p x-show="!teams.length" class="px-2.5 py-1.5 text-xs text-gray-400">No teams yet.</p>
                    </div>
                </div>

                <div x-show="sidebarOpen" x-cloak class="border-t border-gray-100 p-3 dark:border-white/[0.06]">
                    <p class="mb-2 flex items-center gap-1.5 px-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        <i class="fas fa-folder text-[10px]"></i> Projects
                    </p>
                    <div class="max-h-[60vh] space-y-0.5 overflow-y-auto">
                        <template x-for="p in projects" :key="p.project_id">
                            <div class="group flex items-center rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                                <button @click="openProjectDetail(p.project_id, 'push')"
                                    class="flex min-w-0 flex-1 items-center gap-2 truncate px-2.5 py-2 text-left text-sm text-gray-600 dark:text-gray-300">
                                    <span class="h-2 w-2 shrink-0 rounded-full" :style="`background:${statusColor(p.status_id)}`"></span>
                                    <span class="truncate" x-text="p.project_name"></span>
                                </button>
                                <button @click.stop.prevent="toggleFavorite('PROJECT', p.project_id)" title="Favorite"
                                    class="shrink-0 px-2 py-2 text-xs transition"
                                    :class="p.is_favorite ? 'text-amber-400' : 'text-gray-300 opacity-0 group-hover:opacity-100 hover:text-amber-400 dark:text-gray-600'">
                                    <i :class="p.is_favorite ? 'fas' : 'far'" class="fa-star"></i>
                                </button>
                            </div>
                        </template>
                        <p x-show="!projects.length" class="px-2.5 py-1.5 text-xs text-gray-400">No projects yet.</p>
                    </div>
                </div>

                {{-- Collapsed icon rail --}}
                <div x-show="!sidebarOpen" x-cloak class="flex flex-col items-center gap-1 border-t border-gray-100 p-2 dark:border-white/[0.06]">
                    <button @click="openNewProject()" title="New Project"
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm transition hover:bg-indigo-500">
                        <i class="fas fa-plus text-xs"></i>
                    </button>
                    <button @click="sidebarOpen = true" title="My Favorite"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-50 hover:text-amber-400 dark:hover:bg-gray-800">
                        <i class="fas fa-star text-sm"></i>
                    </button>
                    <button @click="sidebarOpen = true" title="Teams"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                        <i class="fas fa-users text-sm"></i>
                    </button>
                    <button @click="sidebarOpen = true" title="Projects"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                        <i class="fas fa-folder text-sm"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- MAIN --}}
        <div class="flex h-full min-w-0 flex-1 flex-col rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            {{-- HEADER --}}
            <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">📁 <span x-text="headerTitle"></span></h2>
            </div>

            {{-- TABS --}}
            <div x-show="teams.length || projects.length" class="flex shrink-0 items-center gap-1 border-b border-gray-100 px-5 pt-2 dark:border-white/[0.06]">
                <button @click="tab = 'kanban'; renderTab()"
                    :class="tab === 'kanban' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">By Kanban</button>
                <button @click="tab = 'gantt'; renderTab()"
                    :class="tab === 'gantt' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">By Gantt</button>
            </div>

            <div x-show="teams.length || projects.length" class="flex-1 overflow-y-auto p-4">
                <div id="kanbanPanel" class="overflow-x-auto"></div>
                <div id="ganttPanel" class="hidden"></div>
            </div>

            {{-- EMPTY STATE: no Team/Project access at all --}}
            <div x-show="loaded && !teams.length && !projects.length" x-cloak
                class="flex flex-1 flex-col items-center justify-center gap-3 overflow-y-auto px-6 py-16 text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                    <i class="fas fa-user-group text-lg"></i>
                </span>
                <p class="max-w-sm text-sm text-gray-500 dark:text-gray-400">
                    Please contact your admin to be added to a Team<span x-show="!canCreateProject">.</span>
                    <span x-show="canCreateProject">, or
                        <button @click="openNewProject()" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">start a new project</button>.
                    </span>
                </p>
            </div>
        </div>

    {{-- NEW / EDIT PROJECT MODAL — same form both ways; #projectForm's
         data-mode ("create"/"edit") drives the header, submit label/icon
         and the create-vs-PUT branch in the submit handler. Styled to match
         the Quick Add Card modal (icon badge, icon-labeled fields). --}}
    <div id="projectModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex w-full max-w-lg flex-col overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-slate-800">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">
                            <i id="projectModalIcon" class="fas fa-diagram-project text-sm"></i>
                        </span>
                        <div>
                            <h2 id="projectModalTitle" class="text-base font-semibold text-slate-900 dark:text-white">New Project</h2>
                            <p id="projectModalSubtitle" class="text-xs text-slate-400">Start tracking a new piece of work</p>
                        </div>
                    </div>
                    <button id="closeProjectModal" type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fas fa-times text-sm"></i></button>
                </div>
                <form id="projectForm" class="flex flex-col" data-mode="create" data-project-id="">
                    <input type="hidden" id="project_status_id">
                    <div class="max-h-[70vh] space-y-4 overflow-y-auto p-6">
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <i class="fas fa-people-group text-[10px]"></i> Team(s) &amp; PIC
                            </label>
                            <select id="project_team_pic" class="select2 w-full" multiple data-placeholder="Select team(s) and/or person(s) in charge"></select>
                            <p class="mt-1 text-xs text-slate-400">Picking a Team links the project to it. Picking a person also links their Team.</p>
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <i class="fas fa-heading text-[10px]"></i> Project Name
                            </label>
                            <input id="project_name" name="project_name" type="text" required placeholder="e.g. Legal Doc Review Process"
                                class="h-11 w-full rounded-lg border border-slate-200 px-4 text-sm text-slate-700 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30">
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <i class="fas fa-align-left text-[10px]"></i> Description
                            </label>
                            <textarea id="project_description" name="project_description" rows="3" placeholder="Optional details…"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-700 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    <i class="fas fa-calendar-day text-[10px]"></i> Start Date
                                </label>
                                <input id="project_start_date" name="start_date" type="date"
                                    class="h-11 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-700 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30">
                            </div>
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    <i class="fas fa-calendar-check text-[10px]"></i> End Date
                                </label>
                                <input id="project_end_date" name="end_date" type="date"
                                    class="h-11 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-700 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <i class="fas fa-tag text-[10px]"></i> Tags
                            </label>
                            <select id="project_tags" class="select2-tags w-full" multiple data-placeholder="Pick or type a tag"></select>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <button type="button" id="cancelProjectBtn" class="h-10 rounded-lg border border-slate-200 px-4 text-sm font-medium text-slate-600 transition hover:bg-white dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">Cancel</button>
                        <button type="submit" id="projectSubmitBtn" class="flex h-10 items-center gap-2 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500">
                            <i id="projectSubmitIcon" class="fas fa-plus text-xs"></i> <span id="projectSubmitLabel">Create Project</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ADD STATUS MODAL --}}
    <div id="addStatusModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50" @click="closeAddStatusModal()"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex w-full max-w-sm flex-col overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-slate-800">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">
                            <i class="fas fa-swatchbook text-sm"></i>
                        </span>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Add status column</h2>
                    </div>
                    <button type="button" @click="closeAddStatusModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fas fa-times text-sm"></i></button>
                </div>

                <div class="max-h-[60vh] space-y-4 overflow-y-auto p-5">
                    <template x-if="availableStatuses.length">
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">From master status</p>
                            <div class="space-y-1">
                                <template x-for="s in availableStatuses" :key="s.status_id">
                                    <button type="button" @click="submitStatus(s.status_name)"
                                        class="flex w-full items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-left text-sm text-slate-700 transition hover:border-indigo-300 hover:bg-indigo-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-indigo-900/20">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" :style="`background:${s.color}`"></span>
                                        <span class="truncate" x-text="s.status_name"></span>
                                        <i class="fas fa-plus ml-auto text-xs text-slate-300"></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div>
                        <template x-if="availableStatuses.length">
                            <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <span class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></span>
                                <span>or create new</span>
                                <span class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></span>
                            </div>
                        </template>
                        <div class="flex gap-2">
                            <input id="new_status_name" type="text" x-model="newStatusName" @keydown.enter="submitStatus(newStatusName)"
                                placeholder="e.g. In Progress"
                                class="h-10 w-full rounded-lg border border-slate-300 px-3 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            <button type="button" @click="submitStatus(newStatusName)"
                                class="h-10 shrink-0 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500">Add</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- QUICK ADD CARD MODAL --}}
    <div id="quickAddCardModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50" @click="closeQuickAddCard()"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex w-full max-w-lg flex-col overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-slate-800">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">
                            <i class="fas fa-id-card text-sm"></i>
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white" x-text="teamId ? 'Add Task' : 'Add Card'"></h2>
                            <p class="text-xs text-slate-400" x-text="(teamId ? teamTaskStatuses : statuses).find(s => s.status_id === quickAddStatusId)?.status_name"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeQuickAddCard()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fas fa-times text-sm"></i></button>
                </div>
                <form @submit.prevent="submitQuickAddCard()" class="flex flex-col">
                    <div class="max-h-[70vh] space-y-4 overflow-y-auto p-6">
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <i class="fas fa-heading text-[10px]"></i> Task Name
                            </label>
                            <input id="qc_name" type="text" required placeholder="e.g. Design landing page"
                                class="h-11 w-full rounded-lg border border-slate-200 px-4 text-sm text-slate-700 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30">
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <i class="fas fa-align-left text-[10px]"></i> Deskripsi
                            </label>
                            <textarea id="qc_description" rows="3" placeholder="Optional details…"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-700 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    <i class="fas fa-calendar-day text-[10px]"></i> Start Date
                                </label>
                                <input id="qc_start_date" type="date"
                                    class="h-11 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-700 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30">
                            </div>
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    <i class="fas fa-calendar-check text-[10px]"></i> End Date
                                </label>
                                <input id="qc_end_date" type="date"
                                    class="h-11 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-700 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <i class="fas fa-user text-[10px]"></i> PIC
                            </label>
                            <select id="qc_pic" class="select2 w-full" multiple data-placeholder="Select person(s) in charge"></select>
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <i class="fas fa-tag text-[10px]"></i> Tags
                            </label>
                            <select id="qc_tags" class="select2-tags w-full" multiple data-placeholder="Pick or type a tag"></select>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <button type="button" @click="closeQuickAddCard()" class="h-10 rounded-lg border border-slate-200 px-4 text-sm font-medium text-slate-600 transition hover:bg-white dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="flex h-10 items-center gap-2 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500">
                            <i class="fas fa-plus text-xs"></i> Add Card
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- PROJECT DETAIL MODAL — clicking a card opens this instead of
         navigating away; URL becomes /projects/{eid} while it's open.
         No backdrop-click-to-close: only the X / Close buttons dismiss it.
         Broken into partials/project-detail-*.blade.php by section. --}}
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <div id="projectDetailModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div id="pmProjectShowRoot" x-data="pmProjectShow()" class="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-[#0f172a] dark:ring-white/10">

                @include('pages.projectmanagement.partials.project-detail-header')

                {{-- TABS --}}
                <div class="flex shrink-0 items-center gap-1 border-b border-gray-100 px-5 pt-2 dark:border-white/[0.06]">
                    <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Overview</button>
                    <button @click="tab = 'tasks'; renderTaskTab()" :class="tab === 'tasks' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Sub Task</button>
                    <button @click="tab = 'chat'" :class="tab === 'chat' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Chat</button>
                    <button @click="tab = 'attachments'" :class="tab === 'attachments' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="rounded-t-lg px-4 py-2 text-sm font-medium">File</button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto p-6">
                    @include('pages.projectmanagement.partials.project-detail-tab-overview')
                    @include('pages.projectmanagement.partials.project-detail-tab-subtask')
                    @include('pages.projectmanagement.partials.project-detail-tab-file')
                    @include('pages.projectmanagement.partials.project-detail-tab-chat')
                </div>

                <div class="flex shrink-0 items-center justify-end gap-3 border-t border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                    <button type="button" id="closeProjectDetailModalBtn" class="h-9 rounded-lg bg-gray-100 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- LINK PROJECT MODAL --}}
    <div id="linkProjectModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-2xl dark:bg-slate-800">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Link a Project</h2>
                <select id="linkProjectSelect" class="mt-4 h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"></select>
                <div class="mt-5 flex justify-end gap-3">
                    <button id="cancelLinkBtn" class="h-10 rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-600 dark:border-slate-600 dark:text-slate-300">Cancel</button>
                    <button id="confirmLinkBtn" class="h-10 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white hover:bg-indigo-500">Link</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SUBTASK FORM MODAL (= "New Task" / "Edit Task", i.e. what "+ Add
         subtask" on the Sub Task tab opens) — a single page, no tabs:
         name, description, dates, PIC, attachment. Files staged here upload
         to the project's own File tab after the subtask saves. --}}
    <div id="taskModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-[#0f172a] dark:ring-white/10">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/70 to-transparent px-6 py-5 dark:border-white/[0.06] dark:from-indigo-500/10">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-600/30">
                            <i class="fas fa-list-check text-sm"></i>
                        </div>
                        <div>
                            <h2 id="taskModalTitle" class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-100">New Subtask</h2>
                            <p class="text-xs text-gray-400">Break this task down into a smaller step</p>
                        </div>
                    </div>
                    <button id="closeTaskModal" type="button" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200"><i class="fas fa-times"></i></button>
                </div>

                <form id="taskForm" class="flex min-h-0 flex-1 flex-col">
                    <input type="hidden" id="task_id" name="task_id">

                    <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-5">
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-pen-to-square text-[10px] text-gray-300 dark:text-gray-500"></i> Sub Task Name</label>
                            <input id="task_name" name="task_name" type="text" required placeholder="e.g. Draft Perjanjian Sewa Menyewa (PSM)"
                                class="h-11 w-full rounded-lg border border-gray-200 px-3.5 text-sm text-gray-800 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:focus:ring-indigo-900/30">
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-align-left text-[10px] text-gray-300 dark:text-gray-500"></i> Description</label>
                            <textarea id="task_description" name="task_description" class="hidden"></textarea>
                            <div id="task_description_editor" class="task-quill overflow-hidden rounded-lg border border-gray-200 shadow-sm dark:border-white/10 dark:bg-white/[0.04]"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-calendar-day text-[10px] text-gray-300 dark:text-gray-500"></i> Start Date</label>
                                <input id="task_start_date" name="start_date" type="date" style="color-scheme: light dark;"
                                    class="h-11 w-full rounded-lg border border-gray-200 px-3 text-sm text-gray-800 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:focus:ring-indigo-900/30">
                            </div>
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-calendar-check text-[10px] text-gray-300 dark:text-gray-500"></i> End Date</label>
                                <input id="task_end_date" name="end_date" type="date" style="color-scheme: light dark;"
                                    class="h-11 w-full rounded-lg border border-gray-200 px-3 text-sm text-gray-800 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:focus:ring-indigo-900/30">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-user-group text-[10px] text-gray-300 dark:text-gray-500"></i> PIC</label>
                            <select id="task_assignees" name="assignees[]" class="select2 w-full" multiple data-placeholder="Assign person(s) in charge"></select>
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-paperclip text-[10px] text-gray-300 dark:text-gray-500"></i> Attachment</label>
                            <label for="task_attachments" class="group flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 px-4 py-6 text-center transition hover:border-indigo-300 hover:bg-indigo-50/60 dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/[0.06]">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-indigo-500 shadow-sm ring-1 ring-gray-100 transition group-hover:scale-105 group-hover:text-indigo-600 dark:bg-white/10 dark:text-indigo-300 dark:ring-white/10">
                                    <i class="fas fa-cloud-arrow-up text-sm"></i>
                                </span>
                                <span class="text-sm font-medium text-gray-500 group-hover:text-indigo-600 dark:text-gray-300 dark:group-hover:text-indigo-300">Click to upload or drag &amp; drop</span>
                                <span class="text-xs text-gray-400">Photos, videos or PDFs — max 5MB each</span>
                            </label>
                            <input type="file" id="task_attachments" multiple accept="image/*,video/*,.pdf" class="hidden">
                            <div id="taskAttachmentsPreview" class="mt-2 flex flex-wrap gap-1.5"></div>
                            <p class="mt-1.5 flex items-center gap-1 text-xs text-gray-400"><i class="fas fa-circle-info text-[10px]"></i> Uploaded files will show up in the project's File tab.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" id="deleteTaskBtn" class="hidden h-10 items-center gap-1.5 rounded-lg border border-red-200 px-4 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-500/30 dark:hover:bg-red-900/20"><i class="fas fa-box-archive text-xs"></i> Archive</button>
                        <div class="ml-auto flex gap-3">
                            <button type="button" id="cancelTaskBtn" class="h-10 rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5">Cancel</button>
                            <button type="submit" class="flex h-10 items-center gap-1.5 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-500 hover:shadow-md hover:shadow-indigo-600/30"><i class="fas fa-check text-xs"></i> Save Subtask</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- TASK DETAIL MODAL — clicking a Task card opens this read-only view
         first; "Edit" closes it and opens the edit form (#taskModal) above. --}}
    <div id="taskDetailModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex max-h-[88vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-[#0f172a] dark:ring-white/10">

                <div class="flex items-start justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/70 to-transparent px-6 py-5 dark:border-white/[0.06] dark:from-indigo-500/10">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-600/30">
                            <i class="fas fa-diagram-project text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p id="taskDetailCreatedBy" class="mb-1 truncate text-xs font-medium text-gray-400"></p>
                            <h2 id="taskDetailName" class="truncate text-xl font-semibold text-gray-800 dark:text-gray-100"></h2>
                            <div class="mt-2.5 flex items-center gap-2.5">
                                <div class="h-1.5 w-40 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                    <div id="taskDetailProgressBar" class="h-1.5 rounded-full bg-indigo-500 transition-all duration-300" style="width:0%"></div>
                                </div>
                                <span id="taskDetailProgressLabel" class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">0%</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <button id="taskDetailBackBtn" type="button" title="Back to parent task"
                            class="hidden h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200">
                            <i class="fas fa-arrow-left text-xs"></i>
                        </button>
                        <button id="taskDetailEditBtn" type="button"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 text-xs font-medium text-gray-600 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300">
                            <i class="fas fa-pen text-[10px]"></i> Edit
                        </button>
                        <button id="closeTaskDetailModal" type="button" class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200"><i class="fas fa-times"></i></button>
                    </div>
                </div>

                <div class="grid min-h-0 flex-1 grid-cols-1 md:grid-cols-5">

                    {{-- LEFT: full Task info --}}
                    <div class="min-h-0 space-y-3.5 overflow-y-auto border-b border-gray-100 bg-gray-50/50 p-5 md:col-span-2 md:border-b-0 md:border-r dark:border-white/[0.06] dark:bg-white/[0.015]">
                        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100 dark:bg-white/[0.03] dark:ring-white/[0.06]">
                            <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-align-left text-[10px] text-gray-300 dark:text-gray-500"></i> Description</p>
                            <p id="taskDetailDescription" class="mt-1.5 text-sm leading-relaxed text-gray-600 dark:text-gray-300">—</p>
                        </div>
                        <div class="flex items-center gap-2.5 rounded-xl bg-white px-3.5 py-2.5 text-sm text-gray-600 shadow-sm ring-1 ring-gray-100 dark:bg-white/[0.03] dark:text-gray-300 dark:ring-white/[0.06]">
                            <i class="fas fa-calendar-day text-xs text-indigo-400"></i>
                            <span id="taskDetailDates">—</span>
                        </div>
                        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100 dark:bg-white/[0.03] dark:ring-white/[0.06]">
                            <p class="mb-2 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-tag text-[10px] text-gray-300 dark:text-gray-500"></i> Tags</p>
                            <div id="taskDetailTags" class="flex flex-wrap gap-1.5"></div>
                        </div>
                        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100 dark:bg-white/[0.03] dark:ring-white/[0.06]">
                            <p class="mb-2 flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-user-group text-[10px] text-gray-300 dark:text-gray-500"></i> PIC</p>
                            <div id="taskDetailPic" class="space-y-1"></div>
                        </div>
                    </div>

                    {{-- RIGHT: Sub Task / Message / Attachments tabs --}}
                    <div class="flex min-h-0 flex-col md:col-span-3">
                        <div class="flex items-center gap-1.5 border-b border-gray-100 px-5 pt-3 dark:border-white/[0.06]">
                            <button type="button" class="task-detail-tab-btn flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 transition dark:bg-indigo-900/30 dark:text-indigo-300" data-detail-tab="subtasks"><i class="fas fa-list-check text-xs"></i> Sub Task</button>
                            <button type="button" class="task-detail-tab-btn flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 transition hover:bg-gray-50 dark:hover:bg-white/5" data-detail-tab="message"><i class="fas fa-comment-dots text-xs"></i> Message</button>
                            <button type="button" class="task-detail-tab-btn flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 transition hover:bg-gray-50 dark:hover:bg-white/5" data-detail-tab="attachments"><i class="fas fa-paperclip text-xs"></i> Attachments</button>
                        </div>

                        <div id="taskDetailTabSubtasks" class="task-detail-tab-panel min-h-0 flex-1 overflow-y-auto p-5">
                            <div class="flex items-center justify-between">
                                <p id="taskDetailSubtaskCount" class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500 dark:bg-white/5 dark:text-gray-400">0 subtasks</p>
                                <button type="button" id="detailAddSubtaskBtn" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-500">
                                    <i class="fas fa-plus text-[10px]"></i> Add subtask
                                </button>
                            </div>
                            <div id="taskDetailSubtaskList" class="mt-3 space-y-2"></div>
                        </div>

                        <div id="taskDetailTabMessage" class="task-detail-tab-panel hidden min-h-0 flex-1 flex-col overflow-y-auto p-5">
                            <div id="taskDetailCommentList" class="custom-scrollbar flex-1 space-y-3 overflow-y-auto pr-1"></div>
                            <div class="mt-3 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-white/[0.06]">
                                <input id="taskDetailCommentInput" type="text" placeholder="Write a message… use @ to mention"
                                    class="flex-1 rounded-full border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 transition focus:border-indigo-300 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:focus:ring-indigo-900/30">
                                <button type="button" id="taskDetailPostCommentBtn" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-500"><i class="fas fa-paper-plane text-xs"></i></button>
                            </div>
                        </div>

                        <div id="taskDetailTabAttachments" class="task-detail-tab-panel hidden min-h-0 flex-1 overflow-y-auto p-5">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-left text-xs uppercase text-gray-400 dark:border-white/[0.06]">
                                        <th class="px-2 py-2">File</th>
                                        <th class="px-2 py-2">By</th>
                                        <th class="px-2 py-2">Date</th>
                                    </tr>
                                </thead>
                                <tbody id="taskDetailAttachmentTbody"></tbody>
                            </table>
                            <div class="mt-3 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-white/[0.06]">
                                <input type="file" id="taskDetailAttachFiles" multiple accept=".png,.jpg,.jpeg,.pdf,.xlsx,.doc,.docx"
                                    class="block flex-1 cursor-pointer rounded-lg border border-gray-200 bg-white px-2 py-[7px] text-sm shadow-sm dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-100">
                                <button type="button" id="btnUploadTaskDetailAttachment" class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm shadow-indigo-600/20 hover:bg-indigo-500">Upload</button>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Max 5MB per file — png, jpg, jpeg, pdf, xlsx, doc, docx.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SUBTASK MODAL — create/edit a subtask's name, dates, PIC and
         description; opens above the Task edit form or the Task detail view. --}}
    <div id="subtaskModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-slate-800 dark:ring-white/10">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 bg-gradient-to-r from-indigo-50/70 to-transparent px-6 py-5 dark:border-slate-700 dark:from-indigo-500/10">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-600/30">
                            <i class="fas fa-list-check text-sm"></i>
                        </div>
                        <div>
                            <h2 id="subtaskModalTitle" class="text-base font-semibold leading-tight text-slate-900 dark:text-white">New subtask</h2>
                            <p class="text-xs text-slate-400">A small step inside this task</p>
                        </div>
                    </div>
                    <button type="button" id="closeSubtaskModal" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-200"><i class="fas fa-times text-sm"></i></button>
                </div>
                <form id="subtaskForm" class="flex flex-col">
                    <div class="space-y-4 p-6">
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400"><i class="fas fa-pen-to-square text-[10px] text-slate-300 dark:text-slate-500"></i> Title</label>
                            <input id="subtask_name" type="text" required placeholder="e.g. Send revised PSM to legal"
                                class="h-11 w-full rounded-lg border border-slate-200 px-3.5 text-sm text-slate-700 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400"><i class="fas fa-calendar-day text-[10px] text-slate-300 dark:text-slate-500"></i> Start date</label>
                                <input id="subtask_start_date" type="date"
                                    class="h-11 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-700 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30">
                            </div>
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400"><i class="fas fa-calendar-check text-[10px] text-slate-300 dark:text-slate-500"></i> End date</label>
                                <input id="subtask_end_date" type="date"
                                    class="h-11 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-700 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400"><i class="fas fa-user-group text-[10px] text-slate-300 dark:text-slate-500"></i> PIC</label>
                            <select id="subtask_assignees" class="select2 w-full" multiple data-placeholder="Assign person(s) in charge"></select>
                        </div>
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400"><i class="fas fa-align-left text-[10px] text-slate-300 dark:text-slate-500"></i> Description</label>
                            <textarea id="subtask_description" rows="3" placeholder="What needs to happen for this subtask to be done?"
                                class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-900/30"></textarea>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <button type="button" id="cancelSubtaskBtn" class="h-10 rounded-lg border border-slate-200 px-4 text-sm font-medium text-slate-600 transition hover:bg-white dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="flex h-10 items-center gap-1.5 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-500 hover:shadow-md hover:shadow-indigo-600/30"><i class="fas fa-check text-xs"></i> Save subtask</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- FILE PREVIEW MODAL — click a file row in any Files tab; image/video/PDF
         render inline, anything else falls back to a Download prompt. --}}
    <div id="filePreviewModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>
        <div class="relative flex h-full items-center justify-center p-6">
            <div class="flex max-h-[88vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-3.5 dark:border-white/[0.06]">
                    <p id="filePreviewName" class="min-w-0 truncate text-sm font-semibold text-gray-800 dark:text-gray-100"></p>
                    <div class="flex shrink-0 items-center gap-2">
                        <a id="filePreviewDownload" href="#" target="_blank" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 dark:hover:bg-white/10"><i class="fas fa-arrow-down-to-bracket text-xs"></i></a>
                        <button type="button" id="closeFilePreviewModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 dark:hover:bg-white/10"><i class="fas fa-times text-xs"></i></button>
                    </div>
                </div>
                <div id="filePreviewBody" class="flex min-h-0 flex-1 items-center justify-center overflow-auto bg-gray-50 p-4 dark:bg-black/20"></div>
            </div>
        </div>
    </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

    <style>
        /* Subtask description — Quill editor, styled to match the modal's inputs */
        #task_description_editor .ql-toolbar.ql-snow {
            border: none;
            border-bottom: 1px solid rgb(229 231 235);
            background: rgb(249 250 251 / .6);
            padding: 6px 10px;
        }
        #task_description_editor .ql-container.ql-snow {
            border: none;
            font-family: inherit;
            font-size: .875rem;
        }
        #task_description_editor .ql-editor {
            min-height: 110px;
            color: rgb(31 41 55);
        }
        #task_description_editor .ql-editor.ql-blank::before {
            color: rgb(156 163 175);
            font-style: normal;
        }
        .task-quill:focus-within {
            border-color: rgb(129 140 248) !important;
            box-shadow: 0 0 0 4px rgb(238 242 255);
        }
        .dark #task_description_editor .ql-toolbar.ql-snow {
            background: rgb(255 255 255 / .03);
            border-bottom-color: rgb(255 255 255 / .08);
        }
        .dark #task_description_editor .ql-container.ql-snow,
        .dark #task_description_editor .ql-editor {
            color: rgb(248 250 252);
        }
        .dark #task_description_editor .ql-editor.ql-blank::before {
            color: rgb(100 116 139);
        }
        .dark .task-quill:focus-within {
            box-shadow: 0 0 0 4px rgb(99 102 241 / .18);
        }
        .dark #task_description_editor .ql-snow .ql-stroke {
            stroke: rgb(148 163 184);
        }
        .dark #task_description_editor .ql-snow .ql-fill,
        .dark #task_description_editor .ql-snow .ql-stroke.ql-fill {
            fill: rgb(148 163 184);
        }
        .dark #task_description_editor .ql-snow .ql-picker-label {
            color: rgb(148 163 184);
        }
        .dark #task_description_editor .ql-snow button:hover .ql-stroke,
        .dark #task_description_editor .ql-snow .ql-picker-label:hover .ql-stroke {
            stroke: rgb(248 250 252);
        }
        .dark #task_description_editor .ql-snow button:hover .ql-fill {
            fill: rgb(248 250 252);
        }
        .dark #task_description_editor .ql-snow button.ql-active .ql-stroke,
        .dark #task_description_editor .ql-snow .ql-picker-label.ql-active .ql-stroke {
            stroke: rgb(129 140 248);
        }
        .dark #task_description_editor .ql-picker-options {
            background: #0f172a;
            border-color: rgb(255 255 255 / .08);
        }
        .dark #task_description_editor .ql-picker-item {
            color: rgb(226 232 240);
        }

        /* Prettier select2 everywhere on this page — pill-shaped multi-select
           chips, a soft focus ring, and a polished dropdown/option list
           (previously only applied inside #taskModal/#subtaskModal). */
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            min-height: 44px !important;
            border-radius: .625rem !important;
            border-color: rgb(226 232 240) !important;
            background: #fff;
            padding: 4px 6px;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / .03);
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .select2-container--default .select2-selection--single {
            display: flex;
            align-items: center;
            padding: 4px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding: 0;
            line-height: normal;
            color: inherit;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--open .select2-selection--multiple,
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: rgb(129 140 248) !important;
            box-shadow: 0 0 0 4px rgb(238 242 255);
        }
        .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .select2-selection--multiple .select2-selection__choice {
            display: flex;
            align-items: center;
            margin: 0 !important;
            padding: 3px 8px !important;
            border-radius: 9999px !important;
            border: none !important;
            background: rgb(238 242 255) !important;
            color: rgb(67 56 202) !important;
            font-size: .75rem;
            font-weight: 500;
        }
        .select2-selection__choice__remove {
            order: 2;
            margin-left: 6px !important;
            margin-right: 0 !important;
            color: rgb(129 140 248) !important;
            border: none !important;
        }
        .select2-selection__choice__remove:hover {
            color: rgb(220 38 38) !important;
            background: transparent !important;
        }
        .select2-search--inline .select2-search__field,
        .select2-search--dropdown .select2-search__field {
            font-size: .875rem;
            margin-top: 4px !important;
            border-radius: .5rem;
            border-color: rgb(226 232 240) !important;
            padding: 6px 10px !important;
        }
        .select2-dropdown {
            border-radius: .75rem !important;
            border-color: rgb(226 232 240) !important;
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / .1), 0 8px 10px -6px rgb(0 0 0 / .1);
            overflow: hidden;
        }
        .select2-results__option {
            padding: 8px 12px !important;
            font-size: .875rem;
            border-radius: .375rem;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: rgb(238 242 255) !important;
            color: rgb(67 56 202) !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: rgb(243 244 246);
        }
        .select2-results__group {
            padding: 8px 10px 4px !important;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgb(148 163 184);
        }
        .dark .select2-container--default .select2-selection--single,
        .dark .select2-container--default .select2-selection--multiple {
            background: rgb(255 255 255 / .04);
            border-color: rgb(255 255 255 / .1) !important;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: rgb(248 250 252);
        }
        .dark .select2-container--default.select2-container--focus .select2-selection--multiple,
        .dark .select2-container--default.select2-container--open .select2-selection--multiple,
        .dark .select2-container--default.select2-container--focus .select2-selection--single,
        .dark .select2-container--default.select2-container--open .select2-selection--single {
            box-shadow: 0 0 0 4px rgb(99 102 241 / .18);
        }
        .dark .select2-selection--multiple .select2-selection__choice {
            background: rgb(99 102 241 / .18) !important;
            color: rgb(199 210 254) !important;
        }
        .dark .select2-search--inline .select2-search__field,
        .dark .select2-search--dropdown .select2-search__field {
            color: rgb(248 250 252);
            background: rgb(255 255 255 / .04);
        }
        .dark .select2-dropdown {
            background: #0f172a;
            border-color: rgb(255 255 255 / .1) !important;
        }
        .dark .select2-results__option {
            color: rgb(226 232 240);
        }
        .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: rgb(99 102 241 / .18) !important;
            color: rgb(199 210 254) !important;
        }
        .dark .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: rgb(255 255 255 / .06);
        }
        .dark .select2-results__group {
            color: rgb(100 116 139);
        }
    </style>

    @push('scripts')
    <script src="{{ asset('assets/js/shared/mention-autocomplete.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script>
        function pmPortfolio(initialTab) {
            return {
                tab: initialTab,
                sidebarOpen: true,
                // '' = the Projects portfolio (unscoped by Team — a Project
                // can belong to more than one Team, so it's never filtered
                // to "one Team's Projects" anymore). A specific team_id =
                // that Team's OWN recursive Task board — a wholly separate
                // concept from Projects, never shown in the Projects list.
                teamId: '',
                teams: [],
                statuses: [],
                availableStatuses: [],
                newStatusName: '',
                quickAddStatusId: null,
                projects: [],
                teamTaskStatuses: [],
                teamTasks: [],
                canCreateProject: @json($canCreateProject),
                loaded: false,

                defaultApplied: false,

                get headerTitle() {
                    const t = this.teams.find(t => t.team_id === this.teamId);
                    return t ? t.team_name : 'Project Management';
                },

                get favoriteItems() {
                    const favTeams = this.teams
                        .filter(t => t.is_favorite)
                        .map(t => ({ type: 'team', id: t.team_id, name: t.team_name }));
                    const favProjects = this.projects
                        .filter(p => p.is_favorite)
                        .map(p => ({ type: 'project', id: p.project_id, name: p.project_name, status_id: p.status_id }));
                    return [...favTeams, ...favProjects];
                },

                init() {
                    this.loadSidebar();
                },

                // Default landing scope: a favorited Team, else the
                // unscoped Projects portfolio — a favorited Project no
                // longer implies landing on a Team's Task board, since
                // Projects aren't Team-scoped anymore.
                defaultTeamId() {
                    const firstFavTeam = this.teams.find(t => t.is_favorite);
                    return firstFavTeam ? firstFavTeam.team_id : '';
                },

                selectTeam(teamId) {
                    // Clicking the already-active Team clears the filter,
                    // returning to the Projects portfolio.
                    this.teamId = this.teamId === teamId ? '' : teamId;
                    this.loadMainPanel();
                },

                // Sidebar (Teams / Projects lists) — always unscoped, loaded once.
                loadSidebar() {
                    $.get('{{ route('projects.board-data') }}', {}, (res) => {
                        this.teams = res.teams;
                        this.statuses = res.statuses;
                        this.availableStatuses = res.availableStatuses;
                        this.projects = res.projects;
                        window.PM_ALL_TEAMS = res.teams;

                        if (!this.defaultApplied) {
                            this.defaultApplied = true;
                            this.teamId = this.defaultTeamId();
                        }

                        this.loaded = true;
                        this.loadMainPanel();
                    });
                },

                // Main panel (Kanban/Gantt) — either the Projects portfolio
                // or the selected Team's own Task board.
                loadMainPanel() {
                    if (this.teamId) this.loadTeamTaskBoard();
                    else this.renderTab();
                },

                loadTeamTaskBoard(cb) {
                    currentTaskApiBase = `{{ url('all-team') }}/${this.teamId}/tasks`;
                    currentTaskDoctype = 'TTK';
                    currentTaskRefreshFn = (cb2) => this.loadTeamTaskBoard(cb2);

                    $.get(`${currentTaskApiBase}/board-data`, (res) => {
                        this.teamTaskStatuses = res.statuses;
                        this.teamTasks = res.tasks;
                        currentTasksCache = res.tasks;
                        this.renderTab();
                        if (typeof cb === 'function') cb();
                    });

                    $.get(`{{ url('all-team') }}/${this.teamId}/detail`, (res) => {
                        window.PM_CURRENT_TEAM_MEMBERS = res.members || [];
                    });
                },

                toggleFavorite(favType, refId) {
                    $.post('{{ route('projects.favorites.toggle') }}', {
                        fav_type: favType,
                        ref_id: refId,
                        _token: '{{ csrf_token() }}',
                    }, () => this.loadSidebar());
                },

                renderTab() {
                    $('#kanbanPanel, #ganttPanel').addClass('hidden');
                    if (this.tab === 'kanban') {
                        $('#kanbanPanel').removeClass('hidden');
                        this.teamId ? this.renderTeamKanban() : this.renderKanban();
                    }
                    if (this.tab === 'gantt') {
                        $('#ganttPanel').removeClass('hidden');
                        this.teamId ? this.renderTeamGantt() : this.renderGantt();
                    }
                },

                statusColor(statusId) {
                    const s = this.statuses.find(s => s.status_id === statusId);
                    return s ? s.color : '#9CA3AF';
                },

                // "+ Add status" — on the Projects board, offers master statuses
                // (ms_project_status) not yet enabled for "all my Teams" as
                // one-click adds, alongside a free-text field. On a Team's own
                // Task board there's no shared master list (each Team's Task
                // columns are its own), so availableStatuses is cleared and
                // only the free-text field shows.
                openAddStatusModal() {
                    this.newStatusName = '';
                    if (this.teamId) this.availableStatuses = [];
                    $('#addStatusModal').removeClass('hidden');
                    $('#new_status_name').focus();
                },

                closeAddStatusModal() {
                    $('#addStatusModal').addClass('hidden');
                },

                submitStatus(statusName) {
                    statusName = (statusName ?? '').trim();
                    if (!statusName) return;

                    if (this.teamId) {
                        $.post(`{{ url('all-team') }}/${this.teamId}/tasks/statuses`, {
                            status_name: statusName,
                            _token: '{{ csrf_token() }}',
                        }, () => {
                            this.closeAddStatusModal();
                            this.loadTeamTaskBoard();
                        }).fail((xhr) => {
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                        });
                        return;
                    }

                    $.post('{{ route('projects.statuses.store') }}', {
                        status_name: statusName,
                        team_id: null,
                        _token: '{{ csrf_token() }}',
                    }, () => {
                        this.closeAddStatusModal();
                        this.loadSidebar();
                    }).fail((xhr) => {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                    });
                },

                // "+" inside a Kanban column. On the Projects board (no Team
                // selected) this opens the full "New Project" modal so a
                // Team can be picked (a Project needs at least one). On a
                // Team's own Task board it quick-creates a top-level Task in
                // that column, PIC picked from the Team's own members.
                openQuickAddCard(statusId) {
                    if (!this.teamId) {
                        this.openNewProject(statusId);
                        return;
                    }

                    this.quickAddStatusId = statusId;
                    $('#qc_name, #qc_description, #qc_start_date, #qc_end_date').val('');

                    initPicSelect($('#qc_pic'), $('#quickAddCardModal'));
                    loadTeamPicOptions($('#qc_pic'), this.teamId);

                    initTagsSelect($('#qc_tags'), $('#quickAddCardModal'));
                    loadTagOptions($('#qc_tags'));

                    $('#quickAddCardModal').removeClass('hidden');
                    $('#qc_name').focus();
                },

                closeQuickAddCard() {
                    $('#quickAddCardModal').addClass('hidden');
                },

                submitQuickAddCard() {
                    const name = $('#qc_name').val()?.trim();
                    const assignees = $('#qc_pic').val() || [];
                    const tags = $('#qc_tags').val() || [];
                    if (!name || !this.teamId) return;

                    $.post(`{{ url('all-team') }}/${this.teamId}/tasks`, {
                        task_name: name,
                        task_description: $('#qc_description').val(),
                        start_date: $('#qc_start_date').val(),
                        end_date: $('#qc_end_date').val(),
                        status_id: this.quickAddStatusId,
                        assignees: assignees,
                        tags: tags,
                        _token: '{{ csrf_token() }}',
                    }, () => {
                        this.closeQuickAddCard();
                        this.loadTeamTaskBoard();
                    }).fail((xhr) => {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                    });
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
                                <div class="px-2 pb-2">
                                    <button class="add-card-btn flex w-full items-center justify-center gap-1.5 rounded-lg px-2 py-1.5 text-xs font-medium text-gray-400 transition hover:bg-gray-100 hover:text-indigo-500 dark:hover:bg-gray-800 dark:hover:text-indigo-400">
                                        <i class="fas fa-plus text-[10px]"></i> Add card
                                    </button>
                                </div>
                            </div>
                        `);

                        const list = col.find('.kanban-col');
                        items.forEach(p => list.append(this.projectCard(p)));

                        col.find('.add-card-btn').on('click', () => this.openQuickAddCard(status.status_id));

                        wrap.append(col);
                    });

                    const addStatusBtn = $(`
                        <div class="w-72 shrink-0">
                            <button class="w-full rounded-lg border-2 border-dashed border-gray-200 px-3 py-2.5 text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 dark:border-gray-700">
                                + Add status
                            </button>
                        </div>
                    `);
                    addStatusBtn.find('button').on('click', () => this.openAddStatusModal());
                    wrap.append(addStatusBtn);

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

                // A Team's own Task board — same column/card shape as the
                // Projects Kanban above, sourced from that Team's recursive
                // Task tree instead. Cards are top-level Tasks only (their
                // own children live inside the Task Detail drill-down).
                renderTeamKanban() {
                    const panel = $('#kanbanPanel').empty();
                    const wrap = $('<div class="flex gap-4 min-w-max pb-2"></div>');

                    this.teamTaskStatuses.forEach(status => {
                        const items = this.teamTasks.filter(t => t.status_id === status.status_id);
                        const col = $(`
                            <div class="w-72 shrink-0 rounded-lg bg-gray-50 dark:bg-gray-900/40">
                                <div class="flex items-center gap-2 px-3 py-2.5">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background:${status.color}"></span>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">${status.status_name}</span>
                                    <span class="text-xs text-gray-400">${items.length}</span>
                                </div>
                                <div class="kanban-col space-y-2 px-2 pb-2 min-h-[40px]" data-status-id="${status.status_id}"></div>
                                <div class="px-2 pb-2">
                                    <button class="add-card-btn flex w-full items-center justify-center gap-1.5 rounded-lg px-2 py-1.5 text-xs font-medium text-gray-400 transition hover:bg-gray-100 hover:text-indigo-500 dark:hover:bg-gray-800 dark:hover:text-indigo-400">
                                        <i class="fas fa-plus text-[10px]"></i> Add card
                                    </button>
                                </div>
                            </div>
                        `);

                        const list = col.find('.kanban-col');
                        items.forEach(t => list.append(this.teamTaskCard(t)));

                        col.find('.add-card-btn').on('click', () => this.openQuickAddCard(status.status_id));

                        wrap.append(col);
                    });

                    const addStatusBtn = $(`
                        <div class="w-72 shrink-0">
                            <button class="w-full rounded-lg border-2 border-dashed border-gray-200 px-3 py-2.5 text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 dark:border-gray-700">
                                + Add status
                            </button>
                        </div>
                    `);
                    addStatusBtn.find('button').on('click', () => this.openAddStatusModal());
                    wrap.append(addStatusBtn);

                    panel.append(wrap);

                    panel.find('.kanban-col').each((i, el) => {
                        Sortable.create(el, {
                            group: 'team-tasks-kanban',
                            animation: 150,
                            onEnd: (evt) => {
                                const taskId = evt.item.dataset.taskId;
                                const statusId = evt.to.dataset.statusId;
                                $.post(`{{ url('all-team') }}/${this.teamId}/tasks/${taskId}/status`, { status_id: statusId, _token: '{{ csrf_token() }}' });
                            }
                        });
                    });
                },

                teamTaskCard(t) {
                    const dateRange = (t.start_date || t.end_date)
                        ? `${formatDate(t.start_date)} → ${formatDate(t.end_date)}`
                        : '';

                    const people = t.assignee_people || [];
                    const avatars = people.map((p, i) => p.photo_url
                        ? `<img src="${p.photo_url}" alt="${this.escapeHtml(p.name)}" title="${this.escapeHtml(p.name)}"
                            class="h-6 w-6 rounded-full object-cover ring-2 ring-white dark:ring-gray-800" style="margin-left:${i === 0 ? '0' : '-8px'}">`
                        : `<span title="${this.escapeHtml(p.name)}" style="margin-left:${i === 0 ? '0' : '-8px'}" class="inline-block rounded-full ring-2 ring-white dark:ring-gray-800">${initialsAvatar(p.name, 24)}</span>`
                    ).join('');

                    const tags = t.tags || [];
                    const tagBadges = tags.map(tag => `
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-medium" style="border-color:${tag.color || '#6366F1'};color:${tag.color || '#6366F1'}">${this.escapeHtml(tag.tag_name)}</span>
                    `).join('');

                    const children = t.children || [];
                    const childDone = children.filter(c => c.progress_percent >= 100).length;

                    const $card = $(`
                        <div data-task-id="${t.task_id}"
                            class="group relative block cursor-move rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">

                            <div class="flex items-start justify-between">
                                <span class="text-gray-300 transition group-hover:text-gray-400 dark:text-gray-600"><i class="fas fa-grip-vertical text-xs"></i></span>
                                ${avatars ? `<div class="flex items-center">${avatars}</div>` : ''}
                            </div>

                            <p class="mt-1.5 text-sm font-semibold leading-snug text-gray-800 dark:text-gray-100">${this.escapeHtml(t.task_name)}</p>

                            ${tagBadges ? `<div class="mt-2 flex flex-wrap gap-1">${tagBadges}</div>` : ''}

                            <div class="mt-3 h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-1.5 rounded-full bg-indigo-500" style="width:${t.progress_percent}%"></div>
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2.5 text-xs text-gray-400 dark:border-gray-700">
                                ${dateRange ? `<span class="inline-flex items-center gap-1"><i class="fas fa-calendar-day text-[10px]"></i> ${dateRange}</span>` : '<span></span>'}
                                ${children.length ? `<span class="inline-flex items-center gap-1"><i class="fas fa-list-check text-[10px]"></i> ${childDone}/${children.length}</span>` : ''}
                            </div>
                        </div>
                    `);

                    $card.on('click', () => {
                        currentTaskApiBase = `{{ url('all-team') }}/${this.teamId}/tasks`;
                        currentTaskDoctype = 'TTK';
                        currentTaskRefreshFn = (cb) => this.loadTeamTaskBoard(cb);
                        taskDetailStack = [];
                        openTaskDetail(t);
                    });

                    return $card;
                },

                escapeHtml(str) {
                    return String(str ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                },

                // Card shows Title, PIC photos, and date range. Description +
                // full PIC names (not usernames) surface in a custom tooltip
                // on hover. The tooltip is a `position: fixed` element on
                // <body> (not an in-card absolute overlay) since the Kanban
                // columns scroll (overflow-y/x-auto), which clips anything
                // positioned absolutely inside them regardless of z-index.
                projectCard(p) {
                    const dateRange = (p.start_date || p.end_date)
                        ? `${formatDate(p.start_date)} → ${formatDate(p.end_date)}`
                        : '';

                    const pics = p.pics || [];
                    const picAvatars = pics.map((pic, i) => pic.photo_url
                        ? `<img src="${pic.photo_url}" alt="${this.escapeHtml(pic.name)}" title="${this.escapeHtml(pic.name)}"
                            class="h-6 w-6 rounded-full object-cover ring-2 ring-white dark:ring-gray-800" style="margin-left:${i === 0 ? '0' : '-8px'}">`
                        : `<span title="${this.escapeHtml(pic.name)}${pic.pic_type === 'TEAM' ? ' (Team)' : ''}" style="margin-left:${i === 0 ? '0' : '-8px'}" class="inline-block rounded-full ring-2 ring-white dark:ring-gray-800">${initialsAvatar(pic.name, 24)}</span>`
                    ).join('');

                    const tags = p.tags || [];
                    const tagBadges = tags.map(tag => `
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-medium" style="border-color:${tag.color || '#6366F1'};color:${tag.color || '#6366F1'}">${this.escapeHtml(tag.tag_name)}</span>
                    `).join('');

                    const tooltip = `
                        <p class="text-xs font-semibold text-white">${this.escapeHtml(p.project_name)}</p>
                        ${p.project_description ? `<p class="mt-1 text-[11px] leading-snug text-gray-300">${this.escapeHtml(p.project_description)}</p>` : ''}
                        ${pics.length ? `<p class="mt-1.5 text-[11px] text-gray-400"><span class="font-medium text-gray-300">PIC:</span> ${this.escapeHtml(pics.map(pic => pic.name).join(', '))}</p>` : ''}
                    `;

                    const $card = $(`
                        <div data-project-id="${p.project_id}"
                            class="project-card-open group relative block cursor-move rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">

                            <div class="flex items-start justify-between">
                                <span class="text-gray-300 transition group-hover:text-gray-400 dark:text-gray-600"><i class="fas fa-grip-vertical text-xs"></i></span>
                                ${picAvatars ? `<div class="flex items-center">${picAvatars}</div>` : ''}
                            </div>

                            <p class="mt-1.5 text-sm font-semibold leading-snug text-gray-800 dark:text-gray-100">${this.escapeHtml(p.project_name)}</p>

                            ${tagBadges ? `<div class="mt-2 flex flex-wrap gap-1">${tagBadges}</div>` : ''}

                            <div class="mt-3 h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-1.5 rounded-full bg-indigo-500" style="width:${p.progress_percent}%"></div>
                            </div>

                            ${dateRange ? `
                                <div class="mt-3 flex items-center gap-1.5 border-t border-gray-100 pt-2.5 text-xs text-gray-400 dark:border-gray-700">
                                    <i class="fas fa-calendar-day text-[10px]"></i>
                                    <span>${dateRange}</span>
                                </div>
                            ` : ''}
                        </div>
                    `);

                    $card.on('mouseenter', function () { showCardTooltip(this, tooltip); });
                    $card.on('mouseleave', hideCardTooltip);

                    return $card;
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
                        on_click: (task) => openProjectDetail(task.id, 'push'),
                    });
                },

                renderTeamGantt() {
                    const container = $('#ganttPanel').empty();
                    const items = this.teamTasks
                        .filter(t => t.start_date && t.end_date)
                        .map(t => ({ id: t.task_id, name: t.task_name, start: t.start_date, end: t.end_date, progress: t.progress_percent }));

                    if (items.length === 0) {
                        container.append('<p class="text-sm text-gray-400">No tasks with both a start and end date yet.</p>');
                        return;
                    }
                    container.append('<svg id="ganttSvg"></svg>');

                    new FrappeGantt('#ganttSvg', items, {
                        on_click: (task) => {
                            const t = findTaskInTree(task.id, currentTasksCache);
                            if (t) {
                                currentTaskApiBase = `{{ url('all-team') }}/${this.teamId}/tasks`;
                                currentTaskDoctype = 'TTK';
                                currentTaskRefreshFn = (cb) => this.loadTeamTaskBoard(cb);
                                taskDetailStack = [];
                                openTaskDetail(t);
                            }
                        },
                    });
                },

                // statusId: pre-select a status column when opened from that
                // column's "+ Add card" (see openQuickAddCard()); null for
                // the sidebar's plain "New Project" button.
                openNewProject(statusId = null) {
                    $('#projectForm')[0].reset();
                    $('#projectForm').data('mode', 'create').data('project-id', '');
                    $('#projectModalIcon').removeClass('fa-pen').addClass('fa-diagram-project');
                    $('#projectModalTitle').text('New Project');
                    $('#projectModalSubtitle').text('Start tracking a new piece of work');
                    $('#projectSubmitIcon').removeClass('fa-check').addClass('fa-plus');
                    $('#projectSubmitLabel').text('Create Project');
                    $('#project_status_id').val(statusId || '');

                    initTeamPicMultiSelect($('#project_team_pic'), $('#projectModal'));
                    const preselect = this.teamId ? [this.teamId] : [];
                    loadProjectTeamPicOptions($('#project_team_pic'), preselect, []);

                    initTagsSelect($('#project_tags'), $('#projectModal'));
                    loadTagOptions($('#project_tags'));

                    $('#projectModal').removeClass('hidden');
                },
            };
        }

        // Kanban card hover tooltip — a single reused `position: fixed`
        // element on <body>, positioned via getBoundingClientRect() so it's
        // never clipped by the scrolling Kanban columns and always renders
        // on top. Flips below the card if there isn't room above.
        let $cardTooltip = null;

        function showCardTooltip(cardEl, html) {
            if (!$cardTooltip) {
                $cardTooltip = $('<div class="pointer-events-none fixed z-[9999] w-64 rounded-lg bg-gray-900/95 p-3 shadow-xl backdrop-blur dark:bg-black/95"></div>').appendTo('body');
            }
            $cardTooltip.html(html).removeClass('hidden');

            const rect = cardEl.getBoundingClientRect();
            const width = $cardTooltip.outerWidth();
            const height = $cardTooltip.outerHeight();

            let left = Math.min(Math.max(rect.left, 8), window.innerWidth - width - 8);
            let top = rect.top - height - 8;
            if (top < 8) top = rect.bottom + 8;

            $cardTooltip.css({ left: left + 'px', top: top + 'px' });
        }

        function hideCardTooltip() {
            if ($cardTooltip) $cardTooltip.addClass('hidden');
        }

        $('#kanbanPanel, #ganttPanel').on('scroll', hideCardTooltip);
        window.addEventListener('scroll', hideCardTooltip, true);

        // Shared by the "Add Card" PIC picker (single Team context) — offers
        // that Team's actual members (all-team.detail), never an unrelated user.
        function initPicSelect($select, $dropdownParent) {
            if (!$select.hasClass('select2-hidden-accessible')) {
                $select.select2({ width: '100%', closeOnSelect: false, dropdownParent: $dropdownParent });
            }
        }

        function loadTeamPicOptions($select, teamId, selectedUsernames = []) {
            $select.empty().trigger('change');
            if (!teamId) return;

            $.get(`{{ url('all-team') }}/${teamId}/detail`, (res) => {
                res.members.forEach(m => $select.append(new Option(m.name, m.username, false, selectedUsernames.includes(m.username))));
                $select.trigger('change');
            });
        }

        // "New/Edit Project" combined Team(s) + PIC picker — one select2,
        // two optgroups. Picking a Team both links the project to it and
        // makes the Team itself a PIC; picking a person also implicitly
        // links their Team (see the submit handler's team_ids derivation
        // below). Option values are "TEAM:<team_id>" / "USER:<username>" so
        // the submit handler can split pic_type/ref_id back out.
        function initTeamPicMultiSelect($select, $dropdownParent) {
            if (!$select.hasClass('select2-hidden-accessible')) {
                $select.select2({ width: '100%', closeOnSelect: false, dropdownParent: $dropdownParent });
            }
        }

        // selectedTeamIds/selectedPicEntries pre-check existing selections
        // when editing a Project (selectedPicEntries is the {pic_type,
        // team_id|username} shape PmProjectController::detail() returns).
        function loadProjectTeamPicOptions($select, selectedTeamIds = [], selectedPicEntries = []) {
            $select.empty().trigger('change');
            const allTeams = window.PM_ALL_TEAMS || [];
            if (!allTeams.length) return;

            const selectedKeys = selectedPicEntries.map(e => `${e.pic_type}:${e.pic_type === 'TEAM' ? e.team_id : e.username}`);

            const teamGroup = $('<optgroup label="Teams"></optgroup>');
            allTeams.forEach((t) => {
                const key = `TEAM:${t.team_id}`;
                const checked = selectedTeamIds.includes(t.team_id) || selectedKeys.includes(key);
                teamGroup.append(new Option(t.team_name, key, false, checked));
            });
            $select.append(teamGroup);

            Promise.all(allTeams.map(t => $.get(`{{ url('all-team') }}/${t.team_id}/detail`).then((res) => ({ team: t, res })))).then((results) => {
                const membership = {}; // username(lower) -> [team_id, ...]
                const seen = new Map(); // username(lower) -> {username, name}
                results.forEach(({ team, res }) => {
                    (res.members || []).forEach((m) => {
                        const lower = m.username.toLowerCase();
                        membership[lower] = membership[lower] || [];
                        membership[lower].push(team.team_id);
                        if (!seen.has(lower)) seen.set(lower, m);
                    });
                });
                window.PM_TEAM_MEMBERSHIP = membership;

                const peopleGroup = $('<optgroup label="People"></optgroup>');
                seen.forEach((m) => {
                    const key = `USER:${m.username}`;
                    peopleGroup.append(new Option(m.name, key, false, selectedKeys.includes(key)));
                });
                $select.append(peopleGroup);
                $select.trigger('change');
            });
        }

        // Shared by the "New Project" and "Add Card" Tags pickers — offers
        // the master tag list (ms_task_tag), but select2's `tags: true`
        // still lets the user type a brand new one on top of these.
        function initTagsSelect($select, $dropdownParent) {
            if (!$select.hasClass('select2-hidden-accessible')) {
                $select.select2({ width: '100%', tags: true, allowClear: true, closeOnSelect: false, dropdownParent: $dropdownParent });
            }
        }

        function loadTagOptions($select, selectedNames = []) {
            $.get('{{ route('projects.tags') }}', (tags) => {
                const $s = $select.empty();
                const names = new Set([...(tags || []).map(t => t.tag_name), ...selectedNames]);
                names.forEach(name => $s.append(new Option(name, name, false, selectedNames.includes(name))));
                $s.trigger('change');
            });
        }

        $(document).on('click', '#closeProjectModal, #cancelProjectBtn', function () {
            const wasEditing = $('#projectForm').data('mode') === 'edit';
            const editedProjectId = $('#projectForm').data('project-id');
            $('#projectModal').addClass('hidden');
            if (wasEditing && editedProjectId) openProjectDetail(editedProjectId, 'none');
        });

        $(document).on('submit', '#projectForm', function (e) {
            e.preventDefault();
            const $form = $(this);
            const mode = $form.data('mode') || 'create';
            const projectId = $form.data('project-id');
            const isEdit = mode === 'edit' && projectId;

            const picEntries = ($('#project_team_pic').val() || []).map((v) => {
                const [type, ...rest] = v.split(':');
                return { pic_type: type, ref_id: rest.join(':') };
            });

            // A Team explicitly picked is a linked Team directly; a person
            // picked implicitly links their Team too (see the field's own
            // helper text) — union of both, deduped.
            const explicitTeamIds = picEntries.filter(e => e.pic_type === 'TEAM').map(e => e.ref_id);
            const derivedTeamIds = picEntries.filter(e => e.pic_type === 'USER')
                .flatMap(e => (window.PM_TEAM_MEMBERSHIP || {})[e.ref_id.toLowerCase()] || []);
            const teamIds = [...new Set([...explicitTeamIds, ...derivedTeamIds])];

            if (!teamIds.length) {
                Swal.fire({ icon: 'warning', title: 'Pick at least one Team or person', text: 'Select at least one Team, or a person (which links their Team automatically).' });
                return;
            }

            const data = {
                project_name: $('#project_name').val(),
                project_description: $('#project_description').val(),
                start_date: $('#project_start_date').val(),
                end_date: $('#project_end_date').val(),
                team_ids: teamIds,
                pics: picEntries,
                tags: $('#project_tags').val() || [],
                status_id: $('#project_status_id').val() || null,
                _token: '{{ csrf_token() }}',
            };

            $.ajax({
                url: isEdit ? `{{ url('projects') }}/${projectId}` : '{{ route('projects.store') }}',
                method: isEdit ? 'PUT' : 'POST',
                data,
                success: function (res) {
                    $('#projectModal').addClass('hidden');
                    Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false })
                        .then(() => {
                            if (isEdit && PM_PROJECT_ID === projectId) openProjectDetail(projectId, 'none');
                            else window.location.reload();
                        });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                }
            });
        });

        // ═══════════════════════════════════════════════════════════════
        // PROJECT DETAIL MODAL — clicking a card opens this in place, URL
        // becomes /projects/{eid}. Mirrors the all-team.blade.php detail
        // modal's pushState/replaceState/popstate convention, except the
        // backdrop deliberately does NOT close it (only X / Close do).
        // ═══════════════════════════════════════════════════════════════
        let PM_PROJECT_ID = null;
        let currentProjectDetail = null;
        const PM_CURRENT_USER = { name: @json(Auth::user()->name), username: @json(Auth::user()->username) };

        function pmProjectShow() {
            return {
                tab: 'overview',
                // 'list' drives the Sub Task tab's default flat checklist (see
                // renderTaskList()); renderKanban()/renderGantt() below are kept
                // working but currently unreachable from the UI — set taskView
                // to 'kanban'/'gantt' and wire a toggle back in if that board
                // view is wanted again.
                taskView: 'list',
                statuses: [],
                tasks: [],
                eligibleUsers: [],

                openNewTask() {
                    resetTaskForm();
                    $('#taskModal').removeClass('hidden');
                },

                openLinkModal() {
                    $('#linkProjectModal').removeClass('hidden');
                },

                unlinkProject(linkedProjectId) {
                    $.ajax({
                        url: `{{ url('projects') }}/${PM_PROJECT_ID}/link/${linkedProjectId}`,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: () => openProjectDetail(PM_PROJECT_ID, 'none'),
                    });
                },

                renderTaskTab(cb) {
                    $('#taskListPanel, #taskKanbanPanel, #taskGanttPanel').addClass('hidden');
                    currentTaskApiBase = `{{ url('projects') }}/${PM_PROJECT_ID}/tasks`;
                    currentTaskDoctype = 'TSK';
                    currentTaskRefreshFn = (cb2) => this.renderTaskTab(cb2);

                    $.get(`${currentTaskApiBase}/board-data`, (res) => {
                        this.statuses = res.statuses;
                        this.tasks = res.tasks;
                        currentTasksCache = res.tasks;
                        if (this.taskView === 'kanban') { $('#taskKanbanPanel').removeClass('hidden'); this.renderKanban(); }
                        else if (this.taskView === 'gantt') { $('#taskGanttPanel').removeClass('hidden'); this.renderGantt(); }
                        else { $('#taskListPanel').removeClass('hidden'); this.renderTaskList(); }
                        if (typeof cb === 'function') cb();
                    });
                },

                renderTaskList() {
                    const done = this.tasks.filter(t => t.progress_percent >= 100).length;
                    $('#taskListSummary').html(`<span class="font-semibold text-gray-800 dark:text-gray-100">${done} of ${this.tasks.length}</span> subtasks completed`);

                    const $list = $('#taskListPanel').empty();
                    if (!this.tasks.length) {
                        $list.append('<p class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">No subtasks yet.</p>');
                        return;
                    }
                    this.tasks.forEach(t => $list.append(taskListRowHtml(t)));
                },

                renderKanban() {
                    const panel = $('#taskKanbanPanel').empty();
                    const wrap = $('<div class="flex gap-4 min-w-max pb-2"></div>');

                    this.statuses.forEach(status => {
                        const items = this.tasks.filter(t => t.status_id === status.status_id);
                        const col = $(`
                            <div class="w-72 shrink-0 rounded-lg bg-gray-50 dark:bg-gray-900/40">
                                <div class="flex items-center gap-2 px-3 py-2.5">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background:${status.color}"></span>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">${status.status_name}</span>
                                    <span class="text-xs text-gray-400">${items.length}</span>
                                </div>
                                <div class="kanban-task-col space-y-2 px-2 pb-2 min-h-[40px]" data-status-id="${status.status_id}"></div>
                            </div>
                        `);
                        const list = col.find('.kanban-task-col');
                        items.forEach(t => list.append(this.taskCard(t)));
                        wrap.append(col);
                    });

                    wrap.append(`
                        <div class="w-72 shrink-0">
                            <button id="addTaskStatusBtn" class="w-full rounded-lg border-2 border-dashed border-gray-200 px-3 py-2.5 text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 dark:border-gray-700">+ Add status</button>
                        </div>
                    `);
                    panel.append(wrap);

                    panel.find('.kanban-task-col').each((i, el) => {
                        Sortable.create(el, {
                            group: 'task-kanban',
                            animation: 150,
                            onEnd: (evt) => {
                                const taskId = evt.item.dataset.taskId;
                                const statusId = evt.to.dataset.statusId;
                                $.post(`{{ url('projects') }}/${PM_PROJECT_ID}/tasks/${taskId}/status`, { status_id: statusId, _token: '{{ csrf_token() }}' });
                            }
                        });
                    });
                },

                taskCard(t) {
                    const subtasks = t.children || [];
                    const subDone = subtasks.filter(s => s.progress_percent >= 100).length;
                    const tags = t.tags || [];
                    const badges = tags.map(tag => `
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium text-white" style="background:${tag.color || '#6366F1'}">${tag.tag_name}</span>
                    `).join('');

                    const people = t.assignee_people || [];
                    const avatars = people.slice(0, 3).map((p, i) => p.photo_url
                        ? `<img src="${p.photo_url}" title="${p.name}" class="h-5 w-5 rounded-full object-cover ring-2 ring-white dark:ring-white/10" style="margin-left:${i === 0 ? '0' : '-6px'}">`
                        : `<span title="${p.name}" style="margin-left:${i === 0 ? '0' : '-6px'}" class="inline-block rounded-full ring-2 ring-white dark:ring-white/10">${initialsAvatar(p.name, 20)}</span>`
                    ).join('');

                    return $(`
                        <div class="task-card cursor-pointer rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/[0.03]" data-task-id="${t.task_id}">
                            ${badges ? `<div class="mb-1.5 flex flex-wrap gap-1">${badges}</div>` : ''}
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">${t.task_name}</p>
                            <div class="mt-2 h-1.5 w-full rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="h-1.5 rounded-full bg-indigo-500" style="width:${t.progress_percent}%"></div>
                            </div>
                            <div class="mt-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-[11px] text-gray-400">
                                    ${t.end_date ? `<span class="inline-flex items-center gap-1"><i class="fas fa-calendar text-[9px]"></i> ${t.end_date}</span>` : ''}
                                    ${subtasks.length ? `<span class="inline-flex items-center gap-1"><i class="fas fa-list-check text-[9px]"></i> ${subDone}/${subtasks.length}</span>` : ''}
                                </div>
                                ${avatars ? `<div class="flex">${avatars}</div>` : ''}
                            </div>
                        </div>
                    `).on('click', () => window.pmOpenTask(t.task_id));
                },

                renderGantt() {
                    const container = $('#taskGanttPanel').empty();
                    const items = this.tasks.filter(t => t.start_date && t.end_date)
                        .map(t => ({ id: t.task_id, name: t.task_name, start: t.start_date, end: t.end_date, progress: t.progress_percent }));

                    if (!items.length) {
                        container.append('<p class="text-sm text-gray-400">No tasks with both a start and end date yet.</p>');
                        return;
                    }
                    container.append('<svg id="taskGanttSvg"></svg>');
                    new FrappeGantt('#taskGanttSvg', items, {
                        on_click: (task) => window.pmOpenTask(task.id),
                    });
                },
            };
        }

        let currentTasksCache = [];
        let currentDetailTaskId = null;

        // Recursive Task Detail context — a Task/Team-Task can be nested to
        // unlimited depth, so instead of hardcoding a Project's task URL
        // everywhere, every task-mutating action (create/update/status/
        // delete/comments/attachments) goes through these, set whenever a
        // board loads (pmProjectShow.renderTaskTab() / pmPortfolio.
        // loadTeamTaskBoard()) or a card opens its detail view.
        let currentTaskApiBase = null;   // e.g. `${url('projects')}/PRJ.../tasks` or `${url('all-team')}/TEAM.../tasks`
        let currentTaskDoctype = 'TSK';  // 'TSK' (Project task) or 'TTK' (Team task) — comments/attachments key
        let currentTaskRefreshFn = null; // reloads the current board + currentTasksCache, then calls its own callback
        let taskDetailStack = [];        // ancestor task_ids, for the detail modal's Back button

        function findTaskInTree(taskId, nodes) {
            for (const n of (nodes || [])) {
                if (n.task_id === taskId) return n;
                const found = findTaskInTree(taskId, n.children);
                if (found) return found;
            }
            return null;
        }

        function refreshTaskDetailContext(cb) {
            if (typeof currentTaskRefreshFn === 'function') currentTaskRefreshFn(cb);
            else if (typeof cb === 'function') cb();
        }

        // Cards open the read-only detail view first; "Edit" inside it opens
        // the edit form (#taskModal) — same split as the Team module's
        // detail-modal-plus-edit-modal pattern.
        window.pmOpenTask = function (taskId) {
            const task = currentTasksCache.find(t => t.task_id === taskId);
            if (!task) return;
            openTaskDetail(task);
        };

        function taskDetailTabName(name) {
            return `taskDetailTab${name.charAt(0).toUpperCase()}${name.slice(1)}`;
        }

        function switchTaskDetailTab(tabName) {
            $('.task-detail-tab-btn').removeClass('bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300').addClass('text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5');
            $(`.task-detail-tab-btn[data-detail-tab="${tabName}"]`).removeClass('text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5').addClass('bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300');
            $('.task-detail-tab-panel').addClass('hidden');
            $(`#${taskDetailTabName(tabName)}`).removeClass('hidden');

            if (tabName === 'message') loadTaskDetailComments(currentDetailTaskId);
            if (tabName === 'attachments') loadTaskDetailAttachments(currentDetailTaskId);
        }

        function hexToRgba(hex, alpha) {
            hex = (hex || '#6366F1').replace('#', '');
            if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
            const num = parseInt(hex, 16);
            const r = (num >> 16) & 255, g = (num >> 8) & 255, b = num & 255;
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        // Deterministic color + initials avatar for people without a photo
        // (chat authors) — same palette idea as the tag color chips elsewhere.
        function initialsAvatar(label, size = 28) {
            label = (label || '?').trim();
            const initials = label.split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase() || '?';
            const palette = ['#6366F1', '#0EA5A4', '#F59E0B', '#EF4444', '#8B5CF6', '#059669', '#EC4899', '#3B82F6'];
            let hash = 0;
            for (let i = 0; i < label.length; i++) hash = label.charCodeAt(i) + ((hash << 5) - hash);
            const color = palette[Math.abs(hash) % palette.length];
            return `<span class="inline-flex shrink-0 items-center justify-center rounded-full font-bold text-white" style="width:${size}px;height:${size}px;font-size:${Math.round(size * 0.38)}px;background:${color}">${initials}</span>`;
        }

        // "2026-09-10" -> "10 Sep 2026" for every date-range display; the
        // underlying <input type="date"> fields keep their raw ISO values.
        function formatDate(dateStr) {
            if (!dateStr) return '—';
            const d = dayjs(dateStr);
            return d.isValid() ? d.format('DD MMM YYYY') : dateStr;
        }

        // Plain-text excerpt of a Quill-authored description, for the
        // single-line truncated previews on task/subtask rows — the raw
        // HTML can't be nested inside those rows' own <p> (browsers close a
        // <p> as soon as a block-level child like Quill's <p>/<li> appears).
        function stripHtml(html) {
            const div = document.createElement('div');
            div.innerHTML = html;
            return (div.textContent || '').trim();
        }

        function subtaskPicHtml(people) {
            if (!people || !people.length) return '';
            return `<div class="flex -space-x-1.5">${people.slice(0, 4).map(p => p.photo_url
                ? `<img src="${p.photo_url}" title="${p.name}" class="h-5 w-5 rounded-full object-cover ring-2 ring-white dark:ring-[#0f172a]">`
                : `<span title="${p.name}" class="rounded-full ring-2 ring-white dark:ring-[#0f172a]">${initialsAvatar(p.name, 20)}</span>`
            ).join('')}</div>`;
        }

        // Shared row markup for both the Task-edit-modal subtask list and the
        // read-only Task-detail subtask list; `deleteClass` differentiates the
        // two contexts' delegated delete-click handlers (see below). Rows are
        // themselves ordinary (recursive) Task rows now — clicking one drills
        // into ITS OWN detail view (which can have further children), rather
        // than opening an edit form directly.
        function subtaskRowHtml(s, deleteClass) {
            const done = s.progress_percent >= 100;
            const children = s.children || [];
            const childDone = children.filter(c => c.progress_percent >= 100).length;
            return `
                <div class="subtask-row group flex cursor-pointer items-start gap-3 rounded-xl border border-gray-100 bg-gray-50/60 px-3.5 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/40 dark:border-white/[0.06] dark:bg-white/[0.02] dark:hover:border-indigo-500/30 dark:hover:bg-indigo-900/10" data-subtask-id="${s.task_id}">
                    <button type="button" class="subtask-check mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition ${done ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 text-transparent hover:border-emerald-400 dark:border-white/20'}" title="Mark ${done ? 'incomplete' : 'complete'}">
                        <i class="fas fa-check text-[9px]"></i>
                    </button>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200 ${done ? 'text-gray-400 line-through dark:text-gray-500' : ''}">${s.task_name}</p>
                        ${stripHtml(s.task_description || '') ? `<p class="mt-0.5 truncate text-xs text-gray-400">${stripHtml(s.task_description)}</p>` : ''}
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
                                <i class="fas fa-calendar text-[9px]"></i> ${formatDate(s.start_date)} → ${formatDate(s.end_date)}
                            </span>
                            ${children.length ? `<span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10"><i class="fas fa-list-check text-[9px]"></i> ${childDone}/${children.length}</span>` : ''}
                            ${subtaskPicHtml(s.assignee_people)}
                        </div>
                    </div>
                    <button type="button" class="${deleteClass} shrink-0 rounded-lg p-1.5 text-gray-300 opacity-0 transition hover:bg-red-50 hover:text-red-500 group-hover:opacity-100 dark:hover:bg-red-900/20" data-subtask-id="${s.task_id}" title="Delete">
                        <i class="fas fa-trash-can text-xs"></i>
                    </button>
                </div>
            `;
        }

        // The project's own flat "Sub Task" checklist (row per TrProjectTask) —
        // same visual language as subtaskRowHtml() below, but a Task's "done"
        // is its own progress_percent and clicking the row opens the Task
        // detail view instead of an edit form.
        function taskListRowHtml(t) {
            const done = t.progress_percent >= 100;
            const tags = (t.tags || []).map(tag => `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium text-white" style="background:${tag.color || '#6366F1'}">${tag.tag_name}</span>`).join('');
            return `
                <div class="task-list-row group flex cursor-pointer items-start gap-3 rounded-xl border border-gray-100 bg-gray-50/60 px-3.5 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/40 dark:border-white/[0.06] dark:bg-white/[0.02] dark:hover:border-indigo-500/30 dark:hover:bg-indigo-900/10" data-task-id="${t.task_id}">
                    <button type="button" class="task-list-check mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition ${done ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 text-transparent hover:border-emerald-400 dark:border-white/20'}" title="Mark ${done ? 'incomplete' : 'complete'}">
                        <i class="fas fa-check text-[9px]"></i>
                    </button>
                    <div class="min-w-0 flex-1">
                        ${tags ? `<div class="mb-1 flex flex-wrap gap-1">${tags}</div>` : ''}
                        <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200 ${done ? 'text-gray-400 line-through dark:text-gray-500' : ''}">${t.task_name}</p>
                        ${stripHtml(t.task_description || '') ? `<p class="mt-0.5 truncate text-xs text-gray-400">${stripHtml(t.task_description)}</p>` : ''}
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
                                <i class="fas fa-calendar text-[9px]"></i> ${formatDate(t.start_date)} → ${formatDate(t.end_date)}
                            </span>
                            ${subtaskPicHtml(t.assignee_people)}
                        </div>
                    </div>
                </div>
            `;
        }

        // Toggles a Task/Team-Task's progress_percent between 0/100 — used
        // by both the Project's flat top-level Sub Task list and the Task
        // Detail modal's children list, since both are the same recursive
        // Task shape now.
        function toggleTaskProgress(t, onSaved) {
            $.ajax({
                url: `${currentTaskApiBase}/${t.task_id}`,
                method: 'PUT',
                data: {
                    task_name: t.task_name,
                    task_description: t.task_description,
                    start_date: t.start_date,
                    end_date: t.end_date,
                    progress_percent: t.progress_percent >= 100 ? 0 : 100,
                    _token: '{{ csrf_token() }}',
                },
                success: function () {
                    if (typeof onSaved === 'function') onSaved();
                },
            });
        }

        function renderTaskDetailSubtaskList(task) {
            const $list = $('#taskDetailSubtaskList').empty();
            const children = task.children || [];
            $('#taskDetailSubtaskCount').text(`${children.length} subtask${children.length === 1 ? '' : 's'}`);
            if (!children.length) {
                $list.append('<p class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">No subtasks yet.</p>');
                return;
            }
            children.forEach(s => $list.append(subtaskRowHtml(s, 'delete-detail-subtask-btn')));
        }

        // Create modal for a new child under the currently-open Task Detail
        // view — opened via openSubtaskModal(parentTaskId, null, onSaved).
        // Editing an existing child now happens by drilling into its own
        // detail view (openTaskDetail) and using its own Edit button, same
        // as any top-level task — so `existing` is only kept here for shape
        // compatibility, not exercised by any current call site.
        let subtaskModalCtx = { taskId: null, onSaved: null };

        function openSubtaskModal(taskId, existing, onSaved) {
            subtaskModalCtx = { taskId, onSaved };
            $('#subtaskModalTitle').text(existing ? 'Edit subtask' : 'New subtask');
            $('#subtask_name').val(existing?.task_name || '');
            $('#subtask_description').val(existing?.task_description || '');
            $('#subtask_start_date').val(existing?.start_date || '');
            $('#subtask_end_date').val(existing?.end_date || '');

            const eligible = currentTaskDoctype === 'TTK'
                ? (window.PM_CURRENT_TEAM_MEMBERS || [])
                : (Alpine.$data(document.getElementById('pmProjectShowRoot')).eligibleUsers || []);
            const selected = existing?.assignees || [];
            const $sel = $('#subtask_assignees').empty();
            eligible.forEach(u => $sel.append(new Option(`${u.name} (${u.username})`, u.username, false, selected.includes(u.username))));
            $sel.trigger('change');

            $('#subtaskModal').removeClass('hidden');
        }

        function closeSubtaskModal() {
            $('#subtaskModal').addClass('hidden');
            subtaskModalCtx = { taskId: null, onSaved: null };
        }

        function commentItemHtml(c) {
            const timeAgo = c.message_date ? dayjs(c.message_date).fromNow() : '';
            return `
                <div class="flex items-start gap-2.5">
                    ${initialsAvatar(c.username, 30)}
                    <div class="min-w-0 flex-1 rounded-2xl rounded-tl-sm bg-gray-100 px-3.5 py-2.5 dark:bg-white/[0.06]">
                        <div class="flex items-baseline gap-2">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">${c.username}</span>
                            <span class="text-[11px] text-gray-400">${timeAgo}</span>
                        </div>
                        <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-200">${highlightMentions(c.message)}</p>
                    </div>
                </div>
            `;
        }

        function openTaskDetail(task) {
            currentDetailTaskId = task.task_id;
            $('#taskDetailBackBtn').toggleClass('hidden', taskDetailStack.length === 0);

            $('#taskDetailName').text(task.task_name);
            $('#taskDetailCreatedBy').text(`Created by ${task.created_by || '—'}${task.created_at ? ' · ' + task.created_at : ''}`);
            $('#taskDetailDescription').html(task.task_description || '—');
            $('#taskDetailDates').text(`${formatDate(task.start_date)} → ${formatDate(task.end_date)}`);
            $('#taskDetailProgressLabel').text(Math.round(task.progress_percent) + '%');
            $('#taskDetailProgressBar').css('width', task.progress_percent + '%');

            const $tags = $('#taskDetailTags').empty();
            if (task.tags && task.tags.length) {
                task.tags.forEach(tag => $tags.append(`<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium text-white shadow-sm" style="background:${tag.color || '#6366F1'}">${tag.tag_name}</span>`));
            } else {
                $tags.append('<span class="text-sm text-gray-400">No tags.</span>');
            }

            const $pic = $('#taskDetailPic').empty();
            const people = task.assignee_people || [];
            if (people.length) {
                people.forEach(p => $pic.append(`
                    <div class="flex items-center gap-2 rounded-lg px-1.5 py-1 transition hover:bg-gray-50 dark:hover:bg-white/5">
                        ${p.photo_url
                        ? `<img src="${p.photo_url}" alt="${p.name}" class="h-7 w-7 rounded-full object-cover ring-1 ring-gray-100 dark:ring-white/10">`
                        : initialsAvatar(p.name, 28)}
                        <span class="text-sm text-gray-700 dark:text-gray-300">${p.name}</span>
                    </div>
                `));
            } else {
                $pic.append('<span class="text-sm text-gray-400">Unassigned.</span>');
            }

            renderTaskDetailSubtaskList(task);
            switchTaskDetailTab('subtasks');
            $('#taskDetailModal').removeClass('hidden');
        }

        function closeTaskDetail() {
            $('#taskDetailModal').addClass('hidden');
            currentDetailTaskId = null;
            taskDetailStack = [];
        }

        function backTaskDetail() {
            const prevId = taskDetailStack.pop();
            if (!prevId) return;
            const prev = findTaskInTree(prevId, currentTasksCache);
            if (prev) openTaskDetail(prev);
        }

        function loadTaskDetailAttachments(taskId) {
            $.get(`{{ url('attachments') }}/${currentTaskDoctype}/${taskId}`).done(res => {
                const $tb = $('#taskDetailAttachmentTbody').empty();
                if (!res.success || !res.attachments || !res.attachments.length) {
                    $tb.append('<tr><td colspan="3" class="p-3 text-center italic text-gray-400">No attachments yet.</td></tr>');
                    return;
                }
                res.attachments.forEach(at => {
                    const link = at.url ? `<a href="${at.url}" target="_blank" class="text-indigo-600 hover:underline">📎 ${at.name}</a>` : `<span>📎 ${at.name}</span>`;
                    $tb.append(`<tr class="border-b border-gray-100 dark:border-gray-700"><td class="px-2 py-2">${link}</td><td class="px-2 py-2">${at.created_by || '-'}</td><td class="px-2 py-2">${at.created_at || '-'}</td></tr>`);
                });
            });
        }

        function loadTaskDetailComments(taskId) {
            const $list = $('#taskDetailCommentList').html('<p class="italic text-gray-400 text-sm">Loading comments...</p>');
            $.get(`/comments/${currentTaskDoctype}/${taskId}`, function (res) {
                $list.empty();
                if (!res.comments || !res.comments.length) {
                    $list.append('<p class="text-sm italic text-gray-400">No comments yet.</p>');
                    return;
                }
                res.comments.forEach(c => $list.append(commentItemHtml(c)));
            });
        }

        // Files picked in the subtask form's Attachment field, staged until
        // the form actually saves (there's no task_id to upload against
        // before that) — then pushed to the project's own File tab.
        let stagedSubtaskFiles = [];

        function renderStagedSubtaskFiles() {
            const $wrap = $('#taskAttachmentsPreview').empty();
            stagedSubtaskFiles.forEach((f, i) => $wrap.append(`
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-600 dark:bg-white/10 dark:text-gray-300">
                    <i class="fas fa-paperclip text-[10px]"></i> ${f.name}
                    <button type="button" class="staged-file-remove text-gray-400 hover:text-red-500" data-i="${i}">&times;</button>
                </span>
            `));
        }

        function initTaskDescrEditor() {
            if (window.taskDescrQuill) return;

            window.taskDescrQuill = new Quill('#task_description_editor', {
                theme: 'snow',
                placeholder: 'What needs to happen for this subtask to be done?',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link'],
                        ['clean'],
                    ],
                },
            });
        }

        function resetTaskForm() {
            $('#taskForm')[0].reset();
            $('#task_id').val('');
            $('#task_assignees').val(null).trigger('change');
            $('#deleteTaskBtn').addClass('hidden');
            $('#taskModalTitle').text('New Subtask');
            stagedSubtaskFiles = [];
            renderStagedSubtaskFiles();
            initTaskDescrEditor();
            window.taskDescrQuill?.setText('');
        }

        function openTaskModal(task) {
            resetTaskForm();
            $('#task_id').val(task.task_id);
            $('#task_name').val(task.task_name);
            window.taskDescrQuill?.clipboard.dangerouslyPasteHTML(task.task_description || '');
            $('#task_start_date').val(task.start_date);
            $('#task_end_date').val(task.end_date);

            // #taskModal/#task_assignees is shared between Project and Team
            // contexts — repopulate its eligible pool from whichever is
            // current every time it opens, rather than trusting whatever
            // was last loaded into it.
            const eligible = currentTaskDoctype === 'TTK'
                ? (window.PM_CURRENT_TEAM_MEMBERS || [])
                : (Alpine.$data(document.getElementById('pmProjectShowRoot')).eligibleUsers || []);
            const selected = task.assignees || [];
            const $sel = $('#task_assignees').empty();
            eligible.forEach(u => $sel.append(new Option(`${u.name} (${u.username})`, u.username, false, selected.includes(u.username))));
            $sel.trigger('change');

            $('#deleteTaskBtn').removeClass('hidden');
            $('#taskModalTitle').text('Edit Subtask — ' + task.task_name);
            $('#taskModal').removeClass('hidden');
        }

        function renderLinkedProjects(links) {
            const $wrap = $('#detailLinkedProjects').empty();
            if (!links || !links.length) {
                $wrap.append('<span class="text-sm text-gray-400">No linked projects.</span>');
                return;
            }
            links.forEach(lp => {
                const $chip = $(`
                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs dark:border-gray-700 dark:bg-gray-800">
                        <button type="button" class="linked-project-open text-indigo-600 hover:underline">${lp.project_name}</button>
                        <button type="button" class="linked-project-unlink text-gray-400 hover:text-red-500">&times;</button>
                    </span>
                `);
                $chip.find('.linked-project-open').on('click', () => openProjectDetail(lp.project_id, 'push'));
                $chip.find('.linked-project-unlink').on('click', () => Alpine.$data(document.getElementById('pmProjectShowRoot')).unlinkProject(lp.project_id));
                $wrap.append($chip);
            });
        }

        // historyMode: 'push' (user-initiated) | 'replace' (deep-link load) |
        // 'none' (browser back/forward already moved, or a silent in-place refresh).
        function openProjectDetail(projectId, historyMode = 'push') {
            $.get(`{{ url('projects') }}/${projectId}/detail`, function (data) {
                PM_PROJECT_ID = data.project_id;
                currentProjectDetail = data;

                $('#detailProjectName').text(data.project_name);
                $('#detailProjectDescription').text(data.project_description || '—');
                $('#detailProjectDates').text(`${formatDate(data.start_date)} → ${formatDate(data.end_date)}`);

                const subtaskTotal = data.subtask_total || 0;
                const subtaskDone = data.subtask_done || 0;
                const subtaskPct = subtaskTotal ? Math.round((subtaskDone / subtaskTotal) * 100) : 0;
                $('#detailProjectSubtaskSummary').text(`${subtaskDone} / ${subtaskTotal} subtasks · ${subtaskPct}%`);
                $('#detailProjectProgressBar').css('width', subtaskPct + '%');

                renderLinkedProjects(data.linked_projects);

                const $statusPill = $('#detailProjectStatusPill');
                if (data.status) {
                    $statusPill.text(data.status.status_name)
                        .css({ background: hexToRgba(data.status.color || '#6366F1', 0.15), color: data.status.color || '#6366F1' })
                        .removeClass('hidden');
                } else {
                    $statusPill.addClass('hidden');
                }
                $('#detailProjectStatusMeta').text(data.status?.status_name || '—');
                $('#detailProjectTeam').text((data.teams || []).map(t => t.team_name).join(', ') || '—');
                $('#detailProjectDueDate').text(data.end_date || '—');

                const pics = data.pics || [];
                const $picsHeader = $('#detailProjectPics').empty();
                pics.slice(0, 5).forEach((p, i) => $picsHeader.append(p.photo_url
                    ? `<img src="${p.photo_url}" title="${p.name}" class="h-6 w-6 rounded-full object-cover ring-2 ring-white dark:ring-[#0f172a]" style="margin-left:${i === 0 ? '0' : '-8px'}">`
                    : `<span title="${p.name}${p.pic_type === 'TEAM' ? ' (Team)' : ''}" style="margin-left:${i === 0 ? '0' : '-8px'}" class="inline-block rounded-full ring-2 ring-white dark:ring-[#0f172a]">${initialsAvatar(p.name, 24)}</span>`
                ));

                const $picList = $('#detailProjectPicList').empty();
                if (pics.length) {
                    pics.forEach(p => $picList.append(`
                        <div class="flex items-center gap-2.5">
                            ${p.photo_url ? `<img src="${p.photo_url}" alt="${p.name}" class="h-7 w-7 rounded-full object-cover">` : initialsAvatar(p.name, 28)}
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">${p.name}${p.pic_type === 'TEAM' ? ' <span class=\'text-xs text-gray-400\'>(Team)</span>' : ''}</span>
                        </div>
                    `));
                } else {
                    $picList.append('<p class="text-sm text-gray-400">Unassigned.</p>');
                }

                const $tags = $('#detailProjectTags').empty();
                if (data.tags && data.tags.length) {
                    data.tags.forEach(tag => $tags.append(`<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium text-white" style="background:${tag.color || '#6366F1'}">${tag.tag_name}</span>`));
                } else {
                    $tags.append('<span class="text-sm text-gray-400">No tags.</span>');
                }

                // Chat tab — everyone on the project's team can join in, not
                // just the PICs, so the participant strip uses eligible_users.
                const chatPeople = data.eligible_users || [];
                const $chatParticipants = $('#chatParticipants').empty();
                chatPeople.slice(0, 6).forEach((u, i) => $chatParticipants.append(
                    `<span title="${u.name}" style="margin-left:${i === 0 ? '0' : '-8px'}" class="inline-block rounded-full ring-2 ring-white dark:ring-[#0f172a]">${initialsAvatar(u.name, 22)}</span>`
                ));
                $('#chatParticipantsLabel').text(chatPeople.length ? `${chatPeople.length} people in this project chat` : '');
                $('#chatSelfAvatar').html(initialsAvatar(PM_CURRENT_USER.name, 30));

                const $linkSelect = $('#linkProjectSelect').empty();
                (data.linkable_projects || []).forEach(lp => $linkSelect.append(`<option value="${lp.project_id}">${lp.project_name}</option>`));

                const $assignees = $('#task_assignees');
                const keepSelected = $assignees.val() || [];
                $assignees.empty();
                (data.eligible_users || []).forEach(u => $assignees.append(new Option(`${u.name} (${u.username})`, u.username, false, keepSelected.includes(u.username))));
                $assignees.trigger('change');

                const root = Alpine.$data(document.getElementById('pmProjectShowRoot'));
                root.tab = 'overview';
                root.eligibleUsers = data.eligible_users || [];

                $('#projectDetailModal').removeClass('hidden');
                refreshProjectAttachments();
                loadProjectComments();

                const url = `{{ url('projects') }}/${data.eid}`;
                if (historyMode === 'push') history.pushState({ projectId: data.project_id }, '', url);
                else if (historyMode === 'replace') history.replaceState({ projectId: data.project_id }, '', url);
            }).fail(function () {
                closeProjectDetail('replace');
                Swal.fire({ icon: 'error', title: 'Project not found', text: 'This project may have been archived or removed.' });
            });
        }

        function closeProjectDetail(historyMode = 'push') {
            $('#projectDetailModal').addClass('hidden');
            PM_PROJECT_ID = null;
            const url = '{{ route('projects.index') }}';
            if (historyMode === 'push') history.pushState({}, '', url);
            else if (historyMode === 'replace') history.replaceState({}, '', url);
        }

        // Opens the same modal/form used by "New Project", pre-filled from
        // the currently open Project detail — reuses openNewProject()'s
        // PIC/Tags picker setup rather than duplicating it.
        function openEditProject() {
            const data = currentProjectDetail;
            if (!data) return;

            $('#projectDetailModal').addClass('hidden');

            $('#projectForm')[0].reset();
            $('#projectForm').data('mode', 'edit').data('project-id', data.project_id);
            $('#projectModalIcon').removeClass('fa-diagram-project').addClass('fa-pen');
            $('#projectModalTitle').text('Edit Project');
            $('#projectModalSubtitle').text(data.project_id);
            $('#projectSubmitIcon').removeClass('fa-plus').addClass('fa-check');
            $('#projectSubmitLabel').text('Save Changes');
            $('#project_status_id').val('');

            const teamIds = (data.teams || []).map(t => t.team_id);

            $('#project_name').val(data.project_name);
            $('#project_description').val(data.project_description || '');
            $('#project_start_date').val(data.start_date_raw || '');
            $('#project_end_date').val(data.end_date_raw || '');

            initTeamPicMultiSelect($('#project_team_pic'), $('#projectModal'));
            loadProjectTeamPicOptions($('#project_team_pic'), teamIds, data.pics || []);

            initTagsSelect($('#project_tags'), $('#projectModal'));
            loadTagOptions($('#project_tags'), (data.tags || []).map(t => t.tag_name));

            $('#projectModal').removeClass('hidden');
        }

        // { bg, color } for a file-type badge, keyed off the extension.
        function fileTypeBadge(name) {
            const ext = (name.split('.').pop() || '').toUpperCase();
            const byExt = {
                PDF: '#EF4444', DOC: '#3B82F6', DOCX: '#3B82F6', XLSX: '#059669', XLS: '#059669',
                PNG: '#8B5CF6', JPG: '#8B5CF6', JPEG: '#8B5CF6',
            };
            const color = byExt[ext] || '#6366F1';
            return { label: ext.slice(0, 4) || 'FILE', bg: hexToRgba(color, 0.15), color };
        }

        function attachmentRowHtml(at) {
            const badge = fileTypeBadge(at.name || '');
            return `
                <div class="attachment-row group flex items-center gap-3.5 rounded-xl bg-gray-50 px-4 py-3 transition dark:bg-white/[0.03] ${at.url ? 'cursor-pointer hover:bg-indigo-50/60 dark:hover:bg-indigo-900/10' : ''}" data-url="${at.url || ''}" data-name="${at.name}">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[10px] font-extrabold" style="background:${badge.bg};color:${badge.color}">${badge.label}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">${at.name}</p>
                        <p class="mt-0.5 text-xs text-gray-400">${at.created_by || '—'} · ${at.created_at || '—'}</p>
                    </div>
                    ${at.url ? `<a href="${at.url}" target="_blank" onclick="event.stopPropagation()" class="attachment-download flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 opacity-0 transition hover:bg-white hover:text-indigo-600 group-hover:opacity-100 dark:hover:bg-white/10 dark:hover:text-indigo-400"><i class="fas fa-arrow-down-to-bracket text-xs"></i></a>` : ''}
                </div>
            `;
        }

        // Click-to-preview: images/video/PDF render inline; anything else
        // falls back to a Download-only prompt in the same modal.
        function openFilePreview(name, url) {
            if (!url) return;
            const ext = (name.split('.').pop() || '').toLowerCase();
            const image = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'heic'].includes(ext);
            const video = ['mp4', 'mov', 'webm', 'avi'].includes(ext);
            const pdf = ext === 'pdf';

            $('#filePreviewName').text(name);
            $('#filePreviewDownload').attr('href', url);

            const $body = $('#filePreviewBody').empty();
            if (image) {
                $body.append(`<img src="${url}" alt="${name}" class="max-h-full max-w-full rounded-lg object-contain">`);
            } else if (video) {
                $body.append(`<video src="${url}" controls autoplay class="max-h-full max-w-full rounded-lg"></video>`);
            } else if (pdf) {
                $body.append(`<iframe src="${url}" class="h-[70vh] w-full rounded-lg border-0 bg-white"></iframe>`);
            } else {
                $body.append(`
                    <div class="flex flex-col items-center gap-3 py-10 text-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/10"><i class="fas fa-file text-xl"></i></span>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No inline preview for this file type.</p>
                        <a href="${url}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"><i class="fas fa-arrow-down-to-bracket text-xs"></i> Download</a>
                    </div>
                `);
            }
            $('#filePreviewModal').removeClass('hidden');
        }

        // Shared by the File tab's own upload button and the subtask form's
        // staged Attachment field — both land in the same PRJ-scoped pool,
        // so everything a subtask attaches is visible in the project's File tab.
        function uploadFilesToProjectAttachments(files, onDone) {
            if (!files || !files.length) { if (onDone) onDone(); return; }

            const MAX_BYTES = 5 * 1024 * 1024;
            const tooBig = files.filter(f => f.size > MAX_BYTES);
            const okFiles = files.filter(f => f.size <= MAX_BYTES);
            if (tooBig.length) toastr.warning(`Skipped (over 5MB): ${tooBig.map(f => f.name).join(', ')}`);
            if (!okFiles.length) { if (onDone) onDone(); return; }

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            okFiles.forEach(f => fd.append('attachments[]', f));

            $.ajax({
                url: `{{ url('attachments') }}/PRJ/${PM_PROJECT_ID}`, method: 'POST', data: fd, processData: false, contentType: false,
                success: function (res) {
                    if (!res.success) toastr.error(res.message);
                    if (onDone) onDone();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Attachment upload failed.');
                    if (onDone) onDone();
                },
            });
        }

        function refreshProjectAttachments() {
            const listUrl = `{{ url('attachments') }}/PRJ/${PM_PROJECT_ID}`;
            $.get(listUrl).done(res => {
                const $list = $('#projectAttachmentList').empty();
                if (!res.success || !res.attachments || !res.attachments.length) {
                    $list.append('<p class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">No attachments yet.</p>');
                    return;
                }
                res.attachments.forEach(at => $list.append(attachmentRowHtml(at)));
            });
        }

        function loadProjectComments() {
            const $list = $('#projectCommentList').html('<p class="italic text-gray-400">Loading comments...</p>');
            $.get(`/comments/PRJ/${PM_PROJECT_ID}`, function (res) {
                $list.empty();
                if (!res.comments || !res.comments.length) {
                    $list.append('<p class="text-sm italic text-gray-400">No comments yet.</p>');
                    return;
                }
                res.comments.forEach(c => $list.append(commentItemHtml(c)));
            });
        }

        $(document).ready(function () {
            dayjs.extend(dayjs_plugin_relativeTime);

            // Scoped to #task_assignees specifically — #project_team_pic,
            // #qc_pic and #project_tags/#qc_tags share the .select2(-tags)
            // classes but live in other modals and get their own init (with the right
            // dropdownParent) via initPicSelect()/initTagsSelect().
            $('#task_assignees').select2({ width: '100%', allowClear: true, closeOnSelect: false, dropdownParent: $('#taskModal') });

            $('#task_attachments').on('change', function () {
                stagedSubtaskFiles = stagedSubtaskFiles.concat(Array.from(this.files));
                renderStagedSubtaskFiles();
                this.value = '';
            });

            $(document).on('click', '.staged-file-remove', function () {
                stagedSubtaskFiles.splice($(this).data('i'), 1);
                renderStagedSubtaskFiles();
            });

            // ── Project detail modal open/close — no backdrop-click-close ──
            $('#closeProjectDetailModal, #closeProjectDetailModalBtn').on('click', () => closeProjectDetail('push'));

            $('#projectDetailEditBtn').on('click', () => openEditProject());

            $(document).on('click', '.project-card-open', function (e) {
                e.preventDefault();
                openProjectDetail($(this).data('project-id'), 'push');
            });

            window.addEventListener('popstate', function (e) {
                if (e.state && e.state.projectId) {
                    openProjectDetail(e.state.projectId, 'none');
                } else {
                    $('#projectDetailModal').addClass('hidden');
                    PM_PROJECT_ID = null;
                }
            });

            const initialProjectId = @json($openProjectId ?? null);
            if (initialProjectId) {
                openProjectDetail(initialProjectId, 'replace');
            }

            // ── Task detail modal (read-only) open/close + Edit handoff ──
            $('#closeTaskDetailModal').on('click', closeTaskDetail);
            $('#taskDetailBackBtn').on('click', backTaskDetail);

            $('#taskDetailEditBtn').on('click', function () {
                const task = findTaskInTree(currentDetailTaskId, currentTasksCache);
                closeTaskDetail();
                if (task) openTaskModal(task);
            });

            $('.task-detail-tab-btn').on('click', function () {
                switchTaskDetailTab($(this).data('detail-tab'));
            });

            $('#detailAddSubtaskBtn').on('click', function () {
                const taskId = currentDetailTaskId;
                if (!taskId) return;
                openSubtaskModal(taskId, null, () => {
                    const t = findTaskInTree(taskId, currentTasksCache);
                    if (t) renderTaskDetailSubtaskList(t);
                });
            });

            // A child row drills into its OWN detail view (which can have
            // further children) — the same recursive behavior as a
            // top-level card, reusing openTaskDetail() instead of a
            // nesting-aware component.
            $(document).on('click', '#taskDetailSubtaskList .subtask-row', function () {
                const childId = $(this).data('subtask-id');
                const child = findTaskInTree(childId, currentTasksCache);
                if (child) {
                    taskDetailStack.push(currentDetailTaskId);
                    openTaskDetail(child);
                }
            });

            $(document).on('click', '.delete-detail-subtask-btn', function (e) {
                e.stopPropagation();
                const subtaskId = $(this).data('subtask-id');
                $.ajax({
                    url: `${currentTaskApiBase}/${subtaskId}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        refreshTaskDetailContext(() => {
                            const t = findTaskInTree(currentDetailTaskId, currentTasksCache);
                            if (t) renderTaskDetailSubtaskList(t);
                        });
                    }
                });
            });

            $('#btnUploadTaskDetailAttachment').on('click', function () {
                const taskId = currentDetailTaskId;
                const files = $('#taskDetailAttachFiles')[0].files;
                if (!taskId || !files.length) { toastr.warning('Choose at least one file.'); return; }

                const fd = new FormData();
                Array.from(files).forEach(f => fd.append('attachments[]', f));
                fd.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: `{{ url('attachments') }}/${currentTaskDoctype}/${taskId}`,
                    method: 'POST', data: fd, processData: false, contentType: false,
                    success: function (res) {
                        if (!res.success) { toastr.error(res.message); return; }
                        toastr.success('Uploaded.');
                        $('#taskDetailAttachFiles').val('');
                        loadTaskDetailAttachments(taskId);
                    },
                    error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Upload failed (max 5MB, png/jpg/jpeg/pdf/xlsx/doc/docx).'); }
                });
            });

            attachMentionAutocomplete({
                inputSelector: '#taskDetailCommentInput',
                fetchUrlFn: () => currentDetailTaskId ? `${currentTaskApiBase}/${currentDetailTaskId}/mentionable-users` : null,
            });

            $('#taskDetailPostCommentBtn').on('click', function () {
                const taskId = currentDetailTaskId;
                const val = $('#taskDetailCommentInput').val().trim();
                if (!taskId || !val) return;
                $.post(`/comments/${currentTaskDoctype}/${taskId}`, { comment: val, _token: '{{ csrf_token() }}' }, function () {
                    $('#taskDetailCommentInput').val('');
                    loadTaskDetailComments(taskId);
                });
            });

            // ── Task edit form (#taskModal) open/close ──
            $('#closeTaskModal, #cancelTaskBtn').on('click', () => $('#taskModal').addClass('hidden'));

            $('#taskForm').on('submit', function (e) {
                e.preventDefault();
                if (window.taskDescrQuill) {
                    $('#task_description').val(window.taskDescrQuill.root.innerHTML);
                }
                const taskId = $('#task_id').val();
                const url = taskId ? `${currentTaskApiBase}/${taskId}` : currentTaskApiBase;
                const method = taskId ? 'PUT' : 'POST';
                const filesToUpload = stagedSubtaskFiles.slice();
                const wasDoctype = currentTaskDoctype;

                $.ajax({
                    url, method,
                    data: $(this).serialize() + '&_token={{ csrf_token() }}',
                    success: function (res) {
                        $('#taskModal').addClass('hidden');
                        toastr.success(res.message);
                        if (wasDoctype === 'TSK') uploadFilesToProjectAttachments(filesToUpload, () => refreshProjectAttachments());
                        refreshTaskDetailContext(() => {
                            if (taskId && currentDetailTaskId === taskId) {
                                const t = findTaskInTree(taskId, currentTasksCache);
                                if (t) openTaskDetail(t);
                            }
                        });
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong.');
                    }
                });
            });

            $('#deleteTaskBtn').on('click', function () {
                const taskId = $('#task_id').val();
                if (!taskId || !confirm('Archive this task?')) return;
                $.ajax({
                    url: `${currentTaskApiBase}/${taskId}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        $('#taskModal').addClass('hidden');
                        closeTaskDetail();
                        refreshTaskDetailContext();
                    }
                });
            });

            $(document).on('click', '#addTaskStatusBtn', function () {
                Swal.fire({ title: 'Add status column', input: 'text', inputPlaceholder: 'e.g. Blocked', showCancelButton: true })
                    .then((result) => {
                        if (!result.isConfirmed || !result.value) return;
                        $.post(`{{ url('projects') }}/${PM_PROJECT_ID}/tasks/statuses`, { status_name: result.value, _token: '{{ csrf_token() }}' }, function () {
                            Alpine.$data(document.getElementById('pmProjectShowRoot')).renderTaskTab();
                        });
                    });
            });

            // Checkmark toggle for the Task-detail view's own children list.
            $(document).on('click', '.subtask-check', function (e) {
                e.stopPropagation();
                const subtaskId = $(this).closest('.subtask-row').data('subtask-id');
                const subtask = findTaskInTree(subtaskId, currentTasksCache);
                if (!subtask) return;
                toggleTaskProgress(subtask, () => {
                    refreshTaskDetailContext(() => {
                        const t = findTaskInTree(currentDetailTaskId, currentTasksCache);
                        if (t) renderTaskDetailSubtaskList(t);
                    });
                });
            });

            // ── Project detail modal's flat "Sub Task" list (a TrProjectTask
            // per row) — row opens the Task detail view, checkmark toggles
            // progress_percent between 0/100 without leaving the list.
            $(document).on('click', '#taskListPanel .task-list-row', function () {
                window.pmOpenTask($(this).data('task-id'));
            });

            $(document).on('click', '#taskListPanel .task-list-check', function (e) {
                e.stopPropagation();
                const taskId = $(this).closest('.task-list-row').data('task-id');
                const t = currentTasksCache.find(x => x.task_id === taskId);
                if (!t) return;
                toggleTaskProgress(t, () => {
                    Alpine.$data(document.getElementById('pmProjectShowRoot')).renderTaskTab();
                });
            });

            // ── Subtask create modal (adds a child under the open Task Detail) ──
            $('#subtask_assignees').select2({ width: '100%', allowClear: true, closeOnSelect: false, dropdownParent: $('#subtaskModal') });

            $('#closeSubtaskModal, #cancelSubtaskBtn').on('click', closeSubtaskModal);

            $('#subtaskForm').on('submit', function (e) {
                e.preventDefault();
                const { taskId, onSaved } = subtaskModalCtx;
                if (!taskId) return;

                const payload = {
                    task_name: $('#subtask_name').val(),
                    task_description: $('#subtask_description').val(),
                    start_date: $('#subtask_start_date').val(),
                    end_date: $('#subtask_end_date').val(),
                    assignees: $('#subtask_assignees').val() || [],
                    parent_task_id: taskId,
                    _token: '{{ csrf_token() }}',
                };

                $.ajax({
                    url: currentTaskApiBase, data: payload, method: 'POST',
                    success: function () {
                        closeSubtaskModal();
                        refreshTaskDetailContext(() => {
                            if (typeof onSaved === 'function') onSaved();
                        });
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong.');
                    }
                });
            });

            // keep currentTasksCache in sync for pmOpenTask()
            $(document).ajaxSuccess(function (event, xhr, settings) {
                if (settings.url && settings.url.includes('/tasks/board-data')) {
                    try { currentTasksCache = JSON.parse(xhr.responseText).tasks; } catch (e) {}
                }
            });

            // ── Link project modal ──
            $('#cancelLinkBtn').on('click', () => $('#linkProjectModal').addClass('hidden'));
            $('#confirmLinkBtn').on('click', function () {
                $.post(`{{ url('projects') }}/${PM_PROJECT_ID}/link`, { linked_project_id: $('#linkProjectSelect').val(), _token: '{{ csrf_token() }}' }, function () {
                    $('#linkProjectModal').addClass('hidden');
                    openProjectDetail(PM_PROJECT_ID, 'none');
                });
            });

            // ── Attachments ──
            $('#btnUploadProjectAttachment').on('click', function () {
                const files = Array.from($('#projectAttachFiles')[0].files);
                if (!files.length) { toastr.warning('Choose at least one file.'); return; }
                uploadFilesToProjectAttachments(files, () => {
                    toastr.success('Uploaded.');
                    $('#projectAttachFiles').val('');
                    refreshProjectAttachments();
                });
            });

            $(document).on('click', '.attachment-row', function () {
                openFilePreview($(this).data('name'), $(this).data('url'));
            });

            $('#closeFilePreviewModal').on('click', function () {
                $('#filePreviewModal').addClass('hidden');
                $('#filePreviewBody').empty(); // stop any playing <video>
            });

            // ── Chat ──
            attachMentionAutocomplete({
                inputSelector: '#projectCommentInput',
                fetchUrlFn: () => PM_PROJECT_ID ? `{{ url('projects') }}/${PM_PROJECT_ID}/mentionable-users` : null,
            });

            $('#projectMentionBtn').on('click', function () {
                const $input = $('#projectCommentInput');
                const val = $input.val();
                $input.val(val + (val && !val.endsWith(' ') ? ' @' : '@')).trigger('input').focus();
            });

            $('#projectAttachChatBtn').on('click', function () {
                Alpine.$data(document.getElementById('pmProjectShowRoot')).tab = 'attachments';
            });

            $('#projectPostCommentBtn').on('click', function () {
                const val = $('#projectCommentInput').val().trim();
                if (!val || !PM_PROJECT_ID) return;
                $.post(`/comments/PRJ/${PM_PROJECT_ID}`, { comment: val, _token: '{{ csrf_token() }}' }, function () {
                    $('#projectCommentInput').val('');
                    loadProjectComments();
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
