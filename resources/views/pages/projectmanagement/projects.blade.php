<x-app-layout>
    <div id="pmPortfolioRoot" class="mx-auto flex h-[calc(100dvh-72px)] w-full max-w-9xl gap-4 p-2" x-data='pmPortfolio(@json(["tab" => $initialTab, "openTeamId" => $openTeamId ?? null, "openTaskEid" => $openTaskEid ?? null]))'>

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
                                    <button @click="selectProject(f.id)"
                                        class="flex min-w-0 flex-1 items-center gap-2 truncate px-2.5 py-2 text-left text-sm transition"
                                        :class="projectId === f.id ? 'font-medium text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300'">
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
                            <div class="group flex items-center rounded-lg"
                                :class="projectId === p.project_id ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'hover:bg-gray-50 dark:hover:bg-gray-800'">
                                <button @click="selectProject(p.project_id)"
                                    class="flex min-w-0 flex-1 items-center gap-2 truncate px-2.5 py-2 text-left text-sm transition"
                                    :class="projectId === p.project_id ? 'font-medium text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300'">
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
                <div x-show="projectId" x-cloak class="flex shrink-0 items-center gap-1">
                    <button @click="editSelectedProject()" title="Edit Project"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-50 hover:text-indigo-600 dark:hover:bg-gray-800 dark:hover:text-indigo-400">
                        <i class="fas fa-pen text-xs"></i>
                    </button>
                    <button @click="deleteSelectedProject()" title="Delete Project"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-gray-800 dark:hover:text-red-400">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>
            </div>

            {{-- TABS --}}
            <div x-show="teams.length || projects.length" class="flex shrink-0 items-center gap-1 border-b border-gray-100 px-5 pt-2 dark:border-white/[0.06]">
                <button @click="tab = 'kanban'; renderTab()"
                    :class="tab === 'kanban' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">By Kanban</button>
                <button @click="tab = 'gantt'; renderTab()"
                    :class="tab === 'gantt' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">By Gantt</button>
                <button @click="tab = 'spreadsheet'; renderTab()"
                    :class="tab === 'spreadsheet' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">By Spreadsheet</button>
            </div>

            <div x-show="teams.length || projects.length" class="flex-1 overflow-y-auto p-4">
                <div id="kanbanPanel" class="overflow-x-auto"></div>
                <div id="ganttPanel" class="hidden"></div>
                <div id="spreadsheetPanel" class="hidden"></div>
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
                            <p class="mt-1 text-xs text-slate-400">Picking a Team links the project to it. People are anyone with Project access, org-wide.</p>
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
                        {{-- Not asked in this form (create or edit) — the fields
                             themselves stay so the JS submit stays untouched
                             (they just submit empty, which is fine server-side). --}}
                        <div id="projectDateFields" class="hidden">
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
                            <input type="color" x-model="newStatusColor" title="Column color"
                                class="h-10 w-10 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1 dark:border-slate-600 dark:bg-slate-700">
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
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white" x-text="(teamId || projectId) ? 'Add Task' : 'Add Card'"></h2>
                            <p class="text-xs text-slate-400" x-text="((teamId || projectId) ? teamTaskStatuses : statuses).find(s => s.status_id === quickAddStatusId)?.status_name"></p>
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
                            <textarea id="qc_description" class="hidden"></textarea>
                            <div id="qc_description_editor" class="task-quill overflow-hidden rounded-lg border border-gray-200 shadow-sm dark:border-white/10 dark:bg-white/[0.04]"></div>
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
                        <div>
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <i class="fas fa-paperclip text-[10px]"></i> Attachment
                            </label>
                            <label for="qc_attachments" class="group flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 px-4 py-6 text-center transition hover:border-indigo-300 hover:bg-indigo-50/60 dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/[0.06]">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-indigo-500 shadow-sm ring-1 ring-gray-100 transition group-hover:scale-105 group-hover:text-indigo-600 dark:bg-white/10 dark:text-indigo-300 dark:ring-white/10">
                                    <i class="fas fa-cloud-arrow-up text-sm"></i>
                                </span>
                                <span class="text-sm font-medium text-gray-500 group-hover:text-indigo-600 dark:text-gray-300 dark:group-hover:text-indigo-300">Click to upload or drag &amp; drop</span>
                                <span class="text-xs text-gray-400">Photos, videos or PDFs — max 5MB each</span>
                            </label>
                            <input type="file" id="qc_attachments" multiple accept="image/*,video/*,.pdf" class="hidden">
                            <div id="qcAttachmentsPreview" class="mt-2 flex flex-wrap gap-1.5"></div>
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

                {{-- TABS — a Subtask (kind 'subtask') shows Overview and Sub
                     Task side-by-side instead (see the grid below), since
                     Chat/File don't apply that deep, so the whole bar hides. --}}
                <div x-show="kind !== 'subtask'" class="flex shrink-0 items-center gap-1 border-b border-gray-100 px-5 pt-2 dark:border-white/[0.06]">
                    <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Overview</button>
                    <button @click="tab = 'tasks'; renderTaskTab()" :class="tab === 'tasks' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Sub Task</button>
                    <button @click="tab = 'chat'" :class="tab === 'chat' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Chat</button>
                    <button @click="tab = 'attachments'" :class="tab === 'attachments' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="rounded-t-lg px-4 py-2 text-sm font-medium">File</button>
                </div>

                {{-- For kind 'subtask' the two panels below render together
                     (see their own x-show: "kind === 'subtask' || tab === …")
                     inside this grid; for 'project'/'task' the wrapping divs
                     get no extra classes and the old tab-switched layout applies. --}}
                <div class="min-h-0 flex-1 overflow-y-auto p-6">
                    <div :class="kind === 'subtask' ? 'grid grid-cols-1 gap-8 lg:grid-cols-5 lg:items-start' : ''">
                        <div :class="kind === 'subtask' ? 'lg:col-span-2 lg:border-r lg:border-gray-100 lg:pr-7 dark:lg:border-white/[0.06]' : ''">
                            @include('pages.projectmanagement.partials.project-detail-tab-overview')
                        </div>
                        <div :class="kind === 'subtask' ? 'lg:col-span-3' : ''">
                            @include('pages.projectmanagement.partials.project-detail-tab-subtask')
                        </div>
                        @include('pages.projectmanagement.partials.project-detail-tab-file')
                        @include('pages.projectmanagement.partials.project-detail-tab-chat')
                    </div>
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
                        <div class="flex gap-2">
                            {{-- Toggles the task's own cancelled flag (Team
                                 Task subtasks only — see #toggleCancelTaskBtn's
                                 click handler); distinct from the "Cancel"
                                 button on the right, which just discards
                                 this form's edits without saving. --}}
                            <button type="button" id="toggleCancelTaskBtn" class="hidden h-10 items-center gap-1.5 rounded-lg border border-amber-200 px-4 text-sm font-medium text-amber-600 transition hover:bg-amber-50 dark:border-amber-500/30 dark:hover:bg-amber-900/20"><i class="fas fa-ban text-xs"></i> Cancel Task</button>
                            <button type="button" id="deleteTaskBtn" class="hidden h-10 items-center gap-1.5 rounded-lg border border-red-200 px-4 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-500/30 dark:hover:bg-red-900/20"><i class="fas fa-box-archive text-xs"></i> Archive</button>
                        </div>
                        <div class="ml-auto flex gap-3">
                            <button type="button" id="cancelTaskBtn" class="h-10 rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5">Cancel</button>
                            <button type="submit" class="flex h-10 items-center gap-1.5 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-500 hover:shadow-md hover:shadow-indigo-600/30"><i class="fas fa-check text-xs"></i> Save Subtask</button>
                        </div>
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
        /* Subtask/task description — Quill editor, styled to match the
           modal's inputs. Class-scoped (.task-quill) rather than tied to
           #task_description_editor's id, so any Quill container on this
           page (e.g. #qc_description_editor) can opt in by reusing the class. */
        .task-quill .ql-toolbar.ql-snow {
            border: none;
            border-bottom: 1px solid rgb(229 231 235);
            background: rgb(249 250 251 / .6);
            padding: 6px 10px;
        }
        .task-quill .ql-container.ql-snow {
            border: none;
            font-family: inherit;
            font-size: .875rem;
        }
        .task-quill .ql-editor {
            min-height: 110px;
            color: rgb(31 41 55);
        }
        .task-quill .ql-editor.ql-blank::before {
            color: rgb(156 163 175);
            font-style: normal;
        }
        .task-quill:focus-within {
            border-color: rgb(129 140 248) !important;
            box-shadow: 0 0 0 4px rgb(238 242 255);
        }
        .dark .task-quill .ql-toolbar.ql-snow {
            background: rgb(255 255 255 / .03);
            border-bottom-color: rgb(255 255 255 / .08);
        }
        .dark .task-quill .ql-container.ql-snow,
        .dark .task-quill .ql-editor {
            color: rgb(248 250 252);
        }
        .dark .task-quill .ql-editor.ql-blank::before {
            color: rgb(100 116 139);
        }
        .dark .task-quill:focus-within {
            box-shadow: 0 0 0 4px rgb(99 102 241 / .18);
        }
        .dark .task-quill .ql-snow .ql-stroke {
            stroke: rgb(148 163 184);
        }
        .dark .task-quill .ql-snow .ql-fill,
        .dark .task-quill .ql-snow .ql-stroke.ql-fill {
            fill: rgb(148 163 184);
        }
        .dark .task-quill .ql-snow .ql-picker-label {
            color: rgb(148 163 184);
        }
        .dark .task-quill .ql-snow button:hover .ql-stroke,
        .dark .task-quill .ql-snow .ql-picker-label:hover .ql-stroke {
            stroke: rgb(248 250 252);
        }
        .dark .task-quill .ql-snow button:hover .ql-fill {
            fill: rgb(248 250 252);
        }
        .dark .task-quill .ql-snow button.ql-active .ql-stroke,
        .dark .task-quill .ql-snow .ql-picker-label.ql-active .ql-stroke {
            stroke: rgb(129 140 248);
        }
        .dark .task-quill .ql-picker-options {
            background: #0f172a;
            border-color: rgb(255 255 255 / .08);
        }
        .dark .task-quill .ql-picker-item {
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

        /* Fills the tab panel's full available height instead of shrinking
           to the chart's content height — renderGantt()/renderTeamGantt()
           measure this and pass it as Frappe Gantt's own container_height
           option, so the chart's ROWS stretch to fill the space (grid lines
           and all) instead of leaving a blank gap below a short chart.
           overflow:hidden is a safety net: it stops any sub-pixel rounding
           between that JS measurement and the real box from leaking out as
           a spurious scrollbar on the page itself — .gantt-container below
           is forced to the same 100% and owns the real (internal) scroll. */
        #ganttPanel {
            height: 100%;
            overflow: hidden;
        }

        /* Gantt chart theming (Frappe Gantt) — the library's built-in dark
           theme keys off html[data-theme="dark"], which this app never
           sets (dark mode toggles a `.dark` class on <html> instead), so
           its CSS custom properties are redefined here for both modes. */
        .gantt-container {
            --g-bar-color: #E0E7FF;
            --g-bar-border: #C7D2FE;
            --g-progress-color: #6366F1;
            --g-arrow-color: #A5B4FC;
            --g-tick-color-thick: #E5E7EB;
            --g-tick-color: #F3F4F6;
            --g-actions-background: #EEF2FF;
            --g-border-color: #E5E7EB;
            --g-text-muted: #9CA3AF;
            --g-text-light: #fff;
            --g-text-dark: #374151;
            --g-handle-color: #4338CA;
            --g-weekend-label-color: #EEF2FF;
            --g-expected-progress: #C7D2FE;
            --g-header-background: #fff;
            --g-row-color: #fff;
            --g-row-border-color: #F3F4F6;
            --g-today-highlight: #6366F1;
            --g-popup-actions: #EEF2FF;
            --g-weekend-highlight-color: #FAFAFF;
            border: 1px solid #F3F4F6;
            height: 100% !important;
        }
        .dark .gantt-container {
            --g-bar-color: #312E81;
            --g-bar-border: #4338CA;
            --g-progress-color: #818CF8;
            --g-arrow-color: #4B5563;
            --g-tick-color-thick: rgb(255 255 255 / .08);
            --g-tick-color: rgb(255 255 255 / .04);
            --g-actions-background: rgb(255 255 255 / .06);
            --g-border-color: rgb(255 255 255 / .06);
            --g-text-muted: #9CA3AF;
            --g-text-light: #fff;
            --g-text-dark: #E5E7EB;
            --g-handle-color: #A5B4FC;
            --g-weekend-label-color: rgb(255 255 255 / .06);
            --g-expected-progress: #4338CA;
            --g-header-background: #0f172a;
            --g-row-color: #0f172a;
            --g-row-border-color: rgb(255 255 255 / .06);
            --g-today-highlight: #818CF8;
            --g-popup-actions: rgb(255 255 255 / .06);
            --g-weekend-highlight-color: rgb(255 255 255 / .02);
            border-color: rgb(255 255 255 / .06);
        }
        .gantt-container .popup-wrapper { border: 1px solid var(--g-border-color); }
        .gantt-container .side-header * { font-weight: 500; }
        .gantt .bar-wrapper .bar { outline: none; }
        .gantt .bar-progress { border-radius: 6px; }
        /* White label text needs a dark halo (SVG stroke) to stay legible
           whether it lands on the pale track or the solid progress fill —
           a label too wide for its bar (.big) is repositioned outside the
           bar onto the plain background instead, so it keeps the library's
           default dark-on-light styling rather than the halo treatment. */
        .gantt .bar-label:not(.big) {
            fill: #fff;
            font-weight: 600;
            font-family: inherit;
            paint-order: stroke;
            stroke: rgb(30 27 75 / .45);
            stroke-width: 3px;
            stroke-linejoin: round;
        }
        /* Overdue bars (custom_class: 'gantt-bar-late' — see isLate()) get a
           solid red outline on top of their normal status color, so a late
           task/project stands out without losing which status column it's
           still sitting in. */
        .gantt .bar-wrapper.gantt-bar-late .bar {
            stroke: #EF4444;
            stroke-width: 2px;
        }
    </style>

    @push('scripts')
    <script src="{{ asset('assets/js/shared/mention-autocomplete.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script>
        function pmPortfolio(opts) {
            return {
                tab: opts.tab,
                sidebarOpen: true,
                // '' = the Projects portfolio (unscoped by Team — a Project
                // can belong to more than one Team, so it's never filtered
                // to "one Team's Projects" anymore). A specific team_id =
                // that Team's OWN recursive Task board — a wholly separate
                // concept from Projects, never shown in the Projects list.
                teamId: '',
                // A specific project_id = that Project's OWN Task board —
                // same Kanban/Gantt/Spreadsheet shape as a Team's board
                // above, sourced from PmTaskController instead of
                // TeamTaskController. Mutually exclusive with teamId.
                projectId: '',
                // Set only by a /task/{eid} deep link (TeamTaskController::show())
                // — the specific Task/Subtask to open once that Team's board
                // finishes loading (see loadTeamTaskBoard()). Cleared after use.
                pendingOpenTaskEid: opts.openTaskEid || null,
                teams: [],
                statuses: [],
                availableStatuses: [],
                newStatusName: '',
                newStatusColor: '#6366F1',
                quickAddStatusId: null,
                projects: [],
                teamTaskStatuses: [],
                teamTasks: [],
                canCreateProject: @json($canCreateProject),
                loaded: false,

                defaultApplied: false,

                get headerTitle() {
                    if (this.teamId) {
                        const t = this.teams.find(t => t.team_id === this.teamId);
                        return t ? t.team_name : 'Project Management';
                    }
                    if (this.projectId) {
                        const p = this.projects.find(p => p.project_id === this.projectId);
                        return p ? p.project_name : 'Project Management';
                    }
                    return 'Project Management';
                },

                // Task API base/doctype for whichever scope (Team or
                // Project) is currently selected — same PmTaskController
                // routes already used by the Project Detail modal's own
                // Sub Task tab (see pmProjectShow().renderTaskTab()).
                get taskApiBase() {
                    return this.teamId
                        ? `{{ url('all-team') }}/${this.teamId}/tasks`
                        : `{{ url('projects') }}/${this.projectId}/tasks`;
                },
                get taskDoctype() {
                    return this.teamId ? 'TTK' : 'TSK';
                },
                refreshTaskBoard(cb) {
                    if (this.teamId) return this.loadTeamTaskBoard(cb);
                    if (this.projectId) return this.loadProjectTaskBoard(cb);
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
                    this.projectId = '';
                    this.loadMainPanel();
                },

                // Same idea as selectTeam(), but scopes the main panel to a
                // single Project's own Task board instead of the Projects
                // portfolio's card view — same Kanban/Gantt/Spreadsheet UI a
                // Team gets, just sourced from PmTaskController.
                selectProject(projectId) {
                    this.projectId = this.projectId === projectId ? '' : projectId;
                    this.teamId = '';
                    this.loadMainPanel();
                },

                // Edit/Delete for the currently-scoped Project — the board
                // header's own affordance now that clicking a Project in the
                // sidebar opens its Task board instead of the Overview modal
                // (which still has its own Edit button, reachable from the
                // portfolio Kanban's project cards). Reuses the same
                // #projectModal/#projectForm as that modal's Edit button.
                editSelectedProject() {
                    if (!this.projectId) return;
                    $.get(`{{ url('projects') }}/${this.projectId}/detail`, (data) => {
                        currentProjectDetail = data;
                        openEditProject();
                    });
                },

                deleteSelectedProject() {
                    if (!this.projectId) return;
                    const projectId = this.projectId;

                    Swal.fire({
                        icon: 'warning',
                        title: 'Delete this project?',
                        text: 'This removes it from the portfolio for everyone. This cannot be undone from here.',
                        showCancelButton: true,
                        confirmButtonText: 'Delete',
                        confirmButtonColor: '#DC2626',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: `{{ url('projects') }}/${projectId}`,
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: () => {
                                this.projectId = '';
                                this.loadSidebar();
                            },
                            error: (xhr) => {
                                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                            },
                        });
                    });
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
                            // A /task/{eid} deep link names its own Team —
                            // takes priority over the favorited-Team default.
                            this.teamId = opts.openTeamId || this.defaultTeamId();
                        }

                        this.loaded = true;
                        this.loadMainPanel();
                    });
                },

                // Main panel (Kanban/Gantt) — either the Projects portfolio,
                // the selected Team's own Task board, or the selected
                // Project's own Task board.
                loadMainPanel() {
                    if (this.teamId) this.loadTeamTaskBoard();
                    else if (this.projectId) this.loadProjectTaskBoard();
                    else this.renderTab();
                },

                loadTeamTaskBoard(cb) {
                    currentTaskApiBase = `{{ url('all-team') }}/${this.teamId}/tasks`;
                    currentTaskDoctype = 'TTK';
                    currentTaskRefreshFn = (cb2) => this.loadTeamTaskBoard(cb2);
                    PM_CURRENT_TEAM_ID = this.teamId;

                    $.get(`${currentTaskApiBase}/board-data`, (res) => {
                        this.teamTaskStatuses = res.statuses;
                        this.teamTasks = res.tasks;
                        currentTasksCache = res.tasks;
                        currentTaskStatuses = res.statuses;
                        this.renderTab();

                        // A /task/{eid} deep link (or popstate returning to
                        // one) names a task to open once this Team's data is
                        // in — find it (at any drill depth) and rebuild the
                        // ancestor stack so the modal's Back button works.
                        if (this.pendingOpenTaskEid) {
                            const eid = this.pendingOpenTaskEid;
                            this.pendingOpenTaskEid = null;
                            const target = findTaskByEid(eid, res.tasks);
                            if (target) {
                                taskDetailStack = findAncestorTaskIds(eid, res.tasks) || [];
                                openTaskEntityDetail(target, 'replace');
                            }
                        }

                        if (typeof cb === 'function') cb();
                    });

                    $.get(`{{ url('all-team') }}/${this.teamId}/detail`, (res) => {
                        window.PM_CURRENT_TEAM_MEMBERS = res.members || [];
                    });
                },

                // A Project's own recursive Task board — same shape as
                // loadTeamTaskBoard() above, from PmTaskController instead.
                loadProjectTaskBoard(cb) {
                    currentTaskApiBase = `{{ url('projects') }}/${this.projectId}/tasks`;
                    currentTaskDoctype = 'TSK';
                    currentTaskRefreshFn = (cb2) => this.loadProjectTaskBoard(cb2);

                    $.get(`${currentTaskApiBase}/board-data`, (res) => {
                        this.teamTaskStatuses = res.statuses;
                        this.teamTasks = res.tasks;
                        currentTasksCache = res.tasks;
                        currentTaskStatuses = res.statuses;
                        this.renderTab();

                        if (typeof cb === 'function') cb();
                    });

                    // Same eligible-users pool the Project Detail modal's
                    // Overview tab loads — needed here too so a Task's
                    // assignee/mention picker (openTaskEntityDetail()) has
                    // people to offer without going through that modal first.
                    $.get(`{{ url('projects') }}/${this.projectId}/detail`, (res) => {
                        Alpine.$data(document.getElementById('pmProjectShowRoot')).eligibleUsers = res.eligible_users || [];
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
                    $('#kanbanPanel, #ganttPanel, #spreadsheetPanel').addClass('hidden');
                    const scoped = this.teamId || this.projectId;
                    if (this.tab === 'kanban') {
                        $('#kanbanPanel').removeClass('hidden');
                        scoped ? this.renderTeamKanban() : this.renderKanban();
                    }
                    if (this.tab === 'gantt') {
                        $('#ganttPanel').removeClass('hidden');
                        scoped ? this.renderTeamGantt() : this.renderGantt();
                    }
                    if (this.tab === 'spreadsheet') {
                        $('#spreadsheetPanel').removeClass('hidden');
                        scoped ? this.renderTeamSpreadsheet() : this.renderSpreadsheet();
                    }
                },

                statusColor(statusId) {
                    const s = this.statuses.find(s => s.status_id === statusId);
                    return s ? s.color : '#9CA3AF';
                },

                teamStatusColor(statusId) {
                    const s = this.teamTaskStatuses.find(s => s.status_id === statusId);
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
                    this.newStatusColor = '#6366F1';
                    if (this.teamId || this.projectId) this.availableStatuses = [];
                    $('#addStatusModal').removeClass('hidden');
                    $('#new_status_name').focus();
                },

                closeAddStatusModal() {
                    $('#addStatusModal').addClass('hidden');
                },

                submitStatus(statusName) {
                    statusName = (statusName ?? '').trim();
                    if (!statusName) return;

                    if (this.teamId || this.projectId) {
                        $.post(`${this.taskApiBase}/statuses`, {
                            status_name: statusName,
                            color: this.newStatusColor,
                            _token: '{{ csrf_token() }}',
                        }, () => {
                            this.closeAddStatusModal();
                            this.refreshTaskBoard();
                        }).fail((xhr) => {
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                        });
                        return;
                    }

                    $.post('{{ route('projects.statuses.store') }}', {
                        status_name: statusName,
                        color: this.newStatusColor,
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
                    if (!this.teamId && !this.projectId) {
                        this.openNewProject(statusId);
                        return;
                    }

                    this.quickAddStatusId = statusId;
                    $('#qc_name, #qc_start_date, #qc_end_date').val('');
                    stagedQcFiles = [];
                    renderStagedQcFiles();
                    initQcDescrEditor();
                    window.qcDescrQuill?.setText('');

                    initPicSelect($('#qc_pic'), $('#quickAddCardModal'));
                    if (this.teamId) loadTeamPicOptions($('#qc_pic'), this.teamId);
                    else loadProjectPicOptions($('#qc_pic'), this.projectId);

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
                    if (!name || (!this.teamId && !this.projectId)) return;

                    if (window.qcDescrQuill) {
                        $('#qc_description').val(window.qcDescrQuill.root.innerHTML);
                    }
                    const filesToUpload = stagedQcFiles.slice();

                    $.post(this.taskApiBase, {
                        task_name: name,
                        task_description: $('#qc_description').val(),
                        start_date: $('#qc_start_date').val(),
                        end_date: $('#qc_end_date').val(),
                        status_id: this.quickAddStatusId,
                        assignees: assignees,
                        tags: tags,
                        _token: '{{ csrf_token() }}',
                    }, (res) => {
                        this.closeQuickAddCard();
                        uploadFilesToProjectAttachments(filesToUpload, this.taskDoctype, res.task_id, () => {});
                        this.refreshTaskBoard();
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
                            <div class="w-72 shrink-0 rounded-lg" style="background:${hexToRgba(status.color, 0.08)}">
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
                            <div class="w-72 shrink-0 rounded-lg" style="background:${hexToRgba(status.color, 0.08)}">
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
                                $.post(`${this.taskApiBase}/${taskId}/status`, { status_id: statusId, _token: '{{ csrf_token() }}' });
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

                    const cancelled = t.status === 'C';
                    // Cancelled children are excluded from the completion
                    // math everywhere (subtaskRowHtml, the detail header,
                    // and here) — not counted done, not counted toward total.
                    const children = (t.children || []).filter(c => c.status !== 'C');
                    const childDone = children.filter(c => c.progress_percent >= 100).length;
                    // A task with subtasks shows THEIR completion (same rule
                    // as the detail header) — its own progress_percent field
                    // is a separate, manually-set value that has nothing to
                    // do with subtask checkmarks and would otherwise sit at
                    // 0% forever even with subtasks done. Leaf tasks (no
                    // subtasks) fall back to their own progress_percent.
                    const displayPct = children.length ? Math.round((childDone / children.length) * 100) : t.progress_percent;
                    const late = isLate(t.end_date, displayPct, cancelled);

                    const $card = $(`
                        <div data-task-id="${t.task_id}"
                            class="group relative block cursor-move rounded-xl border ${late ? 'border-red-200 dark:border-red-500/30' : 'border-gray-200 dark:border-gray-700'} bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-800 ${cancelled ? 'opacity-60' : ''}">

                            <div class="flex items-start justify-between">
                                <span class="text-gray-300 transition group-hover:text-gray-400 dark:text-gray-600"><i class="fas fa-grip-vertical text-xs"></i></span>
                                <div class="flex items-center gap-1">
                                    <div class="flex items-center gap-0.5 opacity-0 transition group-hover:opacity-100">
                                        ${this.teamId ? `
                                        <button type="button" class="team-card-cancel-btn rounded-lg p-1 text-gray-300 transition hover:bg-amber-50 hover:text-amber-500 dark:hover:bg-amber-900/20" data-task-id="${t.task_id}" title="${cancelled ? 'Restore' : 'Cancel'}">
                                            <i class="fas ${cancelled ? 'fa-rotate-left' : 'fa-ban'} text-xs"></i>
                                        </button>
                                        ` : ''}
                                        <button type="button" class="team-card-archive-btn rounded-lg p-1 text-gray-300 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20" data-task-id="${t.task_id}" title="Archive">
                                            <i class="fas fa-box-archive text-xs"></i>
                                        </button>
                                    </div>
                                    ${avatars ? `<div class="flex items-center">${avatars}</div>` : ''}
                                </div>
                            </div>

                            <div class="mt-1.5 flex items-center gap-1.5">
                                <p class="min-w-0 flex-1 truncate text-sm font-semibold leading-snug text-gray-800 dark:text-gray-100 ${cancelled ? 'text-gray-400 line-through dark:text-gray-500' : ''}">${this.escapeHtml(t.task_name)}</p>
                                ${cancelled ? `<span class="shrink-0 rounded-full bg-gray-200 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-white/10 dark:text-gray-400">Cancelled</span>` : ''}
                                ${late ? `<span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-red-600 dark:bg-red-500/10 dark:text-red-400"><i class="fas fa-triangle-exclamation text-[9px]"></i> Late</span>` : ''}
                            </div>

                            ${tagBadges ? `<div class="mt-2 flex flex-wrap gap-1">${tagBadges}</div>` : ''}

                            <div class="mt-3 flex items-center gap-2">
                                <div class="h-1.5 min-w-0 flex-1 rounded-full bg-gray-100 dark:bg-gray-700">
                                    <div class="h-1.5 rounded-full ${displayPct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500'}" style="width:${displayPct}%"></div>
                                </div>
                                <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-bold ${displayPct >= 100 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300'}">${displayPct}%</span>
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2.5 text-xs text-gray-400 dark:border-gray-700">
                                ${dateRange ? `<span class="inline-flex items-center gap-1 ${late ? 'font-semibold text-red-500 dark:text-red-400' : ''}"><i class="fas fa-calendar-day text-[10px]"></i> ${dateRange}</span>` : '<span></span>'}
                                ${children.length ? `<span class="inline-flex items-center gap-1"><i class="fas fa-list-check text-[10px]"></i> ${childDone}/${children.length}</span>` : ''}
                            </div>
                        </div>
                    `);

                    $card.on('click', () => {
                        currentTaskApiBase = this.taskApiBase;
                        currentTaskDoctype = this.taskDoctype;
                        currentTaskRefreshFn = (cb) => this.refreshTaskBoard(cb);
                        taskDetailStack = [];
                        openTaskEntityDetail(t);
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

                    const late = isLate(p.end_date, p.progress_percent, false);

                    const $card = $(`
                        <div data-project-id="${p.project_id}"
                            class="project-card-open group relative block cursor-move rounded-xl border ${late ? 'border-red-200 dark:border-red-500/30' : 'border-gray-200 dark:border-gray-700'} bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-800">

                            <div class="flex items-start justify-between">
                                <span class="text-gray-300 transition group-hover:text-gray-400 dark:text-gray-600"><i class="fas fa-grip-vertical text-xs"></i></span>
                                ${picAvatars ? `<div class="flex items-center">${picAvatars}</div>` : ''}
                            </div>

                            <div class="mt-1.5 flex items-center gap-1.5">
                                <p class="min-w-0 flex-1 truncate text-sm font-semibold leading-snug text-gray-800 dark:text-gray-100">${this.escapeHtml(p.project_name)}</p>
                                ${late ? `<span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-red-600 dark:bg-red-500/10 dark:text-red-400"><i class="fas fa-triangle-exclamation text-[9px]"></i> Late</span>` : ''}
                            </div>

                            ${tagBadges ? `<div class="mt-2 flex flex-wrap gap-1">${tagBadges}</div>` : ''}

                            <div class="mt-3 h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-1.5 rounded-full bg-indigo-500" style="width:${p.progress_percent}%"></div>
                            </div>

                            ${dateRange ? `
                                <div class="mt-3 flex items-center gap-1.5 border-t border-gray-100 pt-2.5 text-xs ${late ? 'font-semibold text-red-500 dark:text-red-400' : 'text-gray-400'} dark:border-gray-700">
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

                    // Bars are tinted by the project's own Kanban status
                    // color — a pale tint as the full-duration track, the
                    // solid color as the progress fill — so the Gantt reads
                    // at a glance the same way the status dots do everywhere
                    // else in this file.
                    const tasks = this.projects
                        .filter(p => p.start_date && p.end_date)
                        .map(p => {
                            const color = this.statusColor(p.status_id);
                            const late = isLate(p.end_date, p.progress_percent, false);
                            return {
                                id: p.project_id,
                                name: (late ? '⚠ ' : '') + p.project_name,
                                start: p.start_date,
                                end: p.end_date,
                                progress: p.progress_percent,
                                color: hexToRgba(color, 0.3),
                                color_progress: color,
                                custom_class: late ? 'gantt-bar-late' : '',
                            };
                        });

                    if (tasks.length === 0) {
                        container.append('<p class="text-sm text-gray-400 mt-2">No projects with both a start and end date yet.</p>');
                        return;
                    }

                    new FrappeGantt('#ganttSvg', tasks, {
                        bar_corner_radius: 6,
                        container_height: Math.max(container.height(), 200),
                        on_click: (task) => openProjectDetail(task.id, 'push'),
                    });
                },

                renderTeamGantt() {
                    const container = $('#ganttPanel').empty();
                    const items = this.teamTasks
                        .filter(t => t.start_date && t.end_date)
                        .map(t => {
                            // Same "children win over own progress_percent"
                            // rule as the Kanban card/detail views, so the
                            // bar's fill matches what's shown everywhere else.
                            const children = (t.children || []).filter(c => c.status !== 'C');
                            const childDone = children.filter(c => c.progress_percent >= 100).length;
                            const progress = children.length ? Math.round((childDone / children.length) * 100) : t.progress_percent;
                            const color = this.teamStatusColor(t.status_id);
                            const late = isLate(t.end_date, progress, t.status === 'C');
                            return {
                                id: t.task_id,
                                name: (late ? '⚠ ' : '') + t.task_name,
                                start: t.start_date,
                                end: t.end_date,
                                progress,
                                color: hexToRgba(color, 0.3),
                                color_progress: color,
                                custom_class: late ? 'gantt-bar-late' : '',
                            };
                        });

                    if (items.length === 0) {
                        container.append('<p class="text-sm text-gray-400">No tasks with both a start and end date yet.</p>');
                        return;
                    }
                    container.append('<svg id="ganttSvg"></svg>');

                    new FrappeGantt('#ganttSvg', items, {
                        bar_corner_radius: 6,
                        container_height: Math.max(container.height(), 200),
                        on_click: (task) => {
                            const t = findTaskInTree(task.id, currentTasksCache);
                            if (t) {
                                currentTaskApiBase = this.taskApiBase;
                                currentTaskDoctype = this.taskDoctype;
                                currentTaskRefreshFn = (cb) => this.refreshTaskBoard(cb);
                                taskDetailStack = [];
                                openTaskEntityDetail(t);
                            }
                        },
                    });
                },

                // "By Spreadsheet" for the Projects portfolio — same status
                // grouping as the Kanban columns, laid out as stacked tables
                // instead of side-by-side ones. Projects never carry a task
                // tree here (that only exists once a Project is opened), so
                // rows are flat — no expand/collapse needed.
                renderSpreadsheet() {
                    const panel = $('#spreadsheetPanel').empty();
                    const wrap = $('<div class="space-y-4"></div>');

                    this.statuses.forEach(status => {
                        const items = this.projects.filter(p => p.status_id === status.status_id);
                        const group = $(`
                            <div class="overflow-hidden rounded-lg border border-gray-100 dark:border-white/[0.06]">
                                <div class="flex items-center gap-2 px-3 py-2" style="background:${hexToRgba(status.color, 0.1)}">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background:${status.color}"></span>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">${status.status_name}</span>
                                    <span class="text-xs text-gray-400">${items.length}</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-[760px] text-left">
                                        <thead>
                                            <tr class="border-b border-gray-100 text-xs font-medium uppercase tracking-wide text-gray-400 dark:border-white/[0.06]">
                                                <th class="px-3 py-2">Project</th>
                                                <th class="px-3 py-2">Description</th>
                                                <th class="px-3 py-2">PIC</th>
                                                <th class="px-3 py-2">Due Date</th>
                                                <th class="px-3 py-2">Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div class="px-3 py-2">
                                    <button class="spreadsheet-add-btn flex items-center gap-1.5 text-xs font-medium text-gray-400 transition hover:text-indigo-500">
                                        <i class="fas fa-plus text-[10px]"></i> Add project
                                    </button>
                                </div>
                            </div>
                        `);

                        const tbody = group.find('tbody');
                        items.forEach(p => tbody.append(this.spreadsheetProjectRow(p)));

                        group.find('.spreadsheet-add-btn').on('click', () => this.openQuickAddCard(status.status_id));
                        wrap.append(group);
                    });

                    const addStatusBtn = $(`
                        <button class="w-full rounded-lg border-2 border-dashed border-gray-200 px-3 py-2.5 text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 dark:border-gray-700">
                            + Add status
                        </button>
                    `);
                    addStatusBtn.on('click', () => this.openAddStatusModal());
                    wrap.append(addStatusBtn);

                    panel.append(wrap);
                },

                spreadsheetProjectRow(p) {
                    const dateLabel = p.end_date ? formatDate(p.end_date) : '—';
                    const pct = p.progress_percent || 0;
                    const late = isLate(p.end_date, pct, false);
                    const $row = $(`
                        <tr data-project-id="${p.project_id}"
                            class="spreadsheet-project-row cursor-pointer border-b border-gray-100 last:border-0 transition hover:bg-indigo-50/40 dark:border-white/[0.04] dark:hover:bg-indigo-900/10">
                            <td class="px-3 py-2.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">${this.escapeHtml(p.project_name)}</span>
                                    ${late ? `<span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-red-600 dark:bg-red-500/10 dark:text-red-400"><i class="fas fa-triangle-exclamation text-[9px]"></i> Late</span>` : ''}
                                </div>
                            </td>
                            <td class="max-w-[260px] truncate px-3 py-2.5 text-xs text-gray-400">${p.project_description ? this.escapeHtml(stripHtml(p.project_description)) : '—'}</td>
                            <td class="px-3 py-2.5">${subtaskPicHtml(p.pics) || '<span class="text-xs text-gray-300">—</span>'}</td>
                            <td class="whitespace-nowrap px-3 py-2.5 text-xs ${late ? 'font-semibold text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'}">${dateLabel}</td>
                            <td class="px-3 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-20 rounded-full bg-gray-100 dark:bg-gray-700">
                                        <div class="h-1.5 rounded-full ${pct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500'}" style="width:${pct}%"></div>
                                    </div>
                                    <span class="shrink-0 text-xs font-semibold ${pct >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-indigo-600 dark:text-indigo-300'}">${pct}%</span>
                                </div>
                            </td>
                        </tr>
                    `);
                    $row.on('click', () => openProjectDetail(p.project_id, 'push'));
                    return $row;
                },

                // "By Spreadsheet" for a Team's own Task board — same status
                // grouping as the Team Kanban columns, but rows are the full
                // recursive Task tree (parents expand/collapse their
                // children) instead of top-level cards only.
                renderTeamSpreadsheet() {
                    const panel = $('#spreadsheetPanel').empty();
                    spreadsheetCollapsedIds = new Set();
                    const wrap = $('<div class="space-y-4"></div>');

                    this.teamTaskStatuses.forEach(status => {
                        const items = this.teamTasks.filter(t => t.status_id === status.status_id);
                        const group = $(`
                            <div class="overflow-hidden rounded-lg border border-gray-100 dark:border-white/[0.06]">
                                <div class="flex items-center gap-2 px-3 py-2" style="background:${hexToRgba(status.color, 0.1)}">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background:${status.color}"></span>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">${status.status_name}</span>
                                    <span class="text-xs text-gray-400">${items.length}</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-[820px] text-left">
                                        <thead>
                                            <tr class="border-b border-gray-100 text-xs font-medium uppercase tracking-wide text-gray-400 dark:border-white/[0.06]">
                                                <th class="px-3 py-2">Task</th>
                                                <th class="px-3 py-2">Description</th>
                                                <th class="px-3 py-2">Assignee</th>
                                                <th class="px-3 py-2">Due Date</th>
                                                <th class="px-3 py-2">Progress</th>
                                                <th class="px-3 py-2">Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>${spreadsheetRowsHtml(items, 0, [])}</tbody>
                                    </table>
                                </div>
                                <div class="px-3 py-2">
                                    <button class="spreadsheet-add-btn flex items-center gap-1.5 text-xs font-medium text-gray-400 transition hover:text-indigo-500">
                                        <i class="fas fa-plus text-[10px]"></i> Add task
                                    </button>
                                </div>
                            </div>
                        `);

                        group.find('.spreadsheet-add-btn').on('click', () => this.openQuickAddCard(status.status_id));
                        wrap.append(group);
                    });

                    const addStatusBtn = $(`
                        <button class="w-full rounded-lg border-2 border-dashed border-gray-200 px-3 py-2.5 text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 dark:border-gray-700">
                            + Add status
                        </button>
                    `);
                    addStatusBtn.on('click', () => this.openAddStatusModal());
                    wrap.append(addStatusBtn);

                    wrap.on('click', '.spreadsheet-toggle-btn', (e) => {
                        e.stopPropagation();
                        const id = $(e.currentTarget).closest('tr').attr('data-task-id');
                        spreadsheetCollapsedIds.has(id) ? spreadsheetCollapsedIds.delete(id) : spreadsheetCollapsedIds.add(id);
                        applySpreadsheetCollapse(wrap);
                    });

                    wrap.on('click', '.spreadsheet-check', (e) => {
                        e.stopPropagation();
                        const taskId = $(e.currentTarget).closest('tr').attr('data-task-id');
                        const t = findTaskInTree(taskId, currentTasksCache);
                        if (!t) return;
                        toggleTaskProgress(t, () => this.refreshTaskBoard());
                    });

                    wrap.on('click', '.spreadsheet-row', (e) => {
                        if ($(e.target).closest('.spreadsheet-toggle-btn, .spreadsheet-check').length) return;
                        const $tr = $(e.currentTarget);
                        const t = findTaskInTree($tr.attr('data-task-id'), currentTasksCache);
                        if (!t) return;
                        taskDetailStack = ($tr.attr('data-ancestors') || '').split(',').filter(Boolean);
                        currentTaskApiBase = this.taskApiBase;
                        currentTaskDoctype = this.taskDoctype;
                        currentTaskRefreshFn = (cb) => this.refreshTaskBoard(cb);
                        openTaskEntityDetail(t);
                    });

                    panel.append(wrap);
                    applySpreadsheetCollapse(wrap);
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

        // Same idea as loadTeamPicOptions() above, for a Project's own
        // Task board — offers the union of members across every Team
        // linked to the Project (PmProjectController::detail()'s
        // eligible_users), same pool the Project Detail modal's Chat/
        // assignee pickers already use.
        function loadProjectPicOptions($select, projectId, selectedUsernames = []) {
            $select.empty().trigger('change');
            if (!projectId) return;

            $.get(`{{ url('projects') }}/${projectId}/detail`, (res) => {
                (res.eligible_users || []).forEach(m => $select.append(new Option(m.name, m.username, false, selectedUsernames.includes(m.username))));
                $select.trigger('change');
            });
        }

        // "New/Edit Project" combined Team(s) + PIC picker — one select2,
        // two optgroups. Picking a Team both links the project to it and
        // makes the Team itself a PIC; People (org-wide PROJECTACCESS
        // holders, see loadProjectTeamPicOptions()) are PICs only, no Team
        // link implied. Option values are "TEAM:<team_id>" / "USER:<username>"
        // so the submit handler can split pic_type/ref_id back out.
        function initTeamPicMultiSelect($select, $dropdownParent) {
            if (!$select.hasClass('select2-hidden-accessible')) {
                $select.select2({ width: '100%', closeOnSelect: false, dropdownParent: $dropdownParent });
            }
        }

        // selectedTeamIds/selectedPicEntries pre-check existing selections
        // when editing a Project (selectedPicEntries is the {pic_type,
        // team_id|username} shape PmProjectController::detail() returns).
        // The People group is org-wide — every ms_user holding PROJECTACCESS
        // (PmProjectController::picUsers()), not limited to members of the
        // Teams listed above it; picking one does NOT implicitly link a Team.
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

            $.get('{{ route('projects.pic-users') }}', (users) => {
                const peopleGroup = $('<optgroup label="People"></optgroup>');
                (users || []).forEach((u) => {
                    const key = `USER:${u.username}`;
                    peopleGroup.append(new Option(u.name, key, false, selectedKeys.includes(key)));
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
            // Only reopen the Overview modal if Edit was actually launched
            // from it (PM_PROJECT_ID matches) — editSelectedProject() (the
            // scoped board's own Edit button) opens this same form without
            // ever opening that modal, and cancelling shouldn't pop it open.
            if (wasEditing && editedProjectId && PM_PROJECT_ID === editedProjectId) openProjectDetail(editedProjectId, 'none');
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

            // People are org-wide (any PROJECTACCESS holder, not necessarily
            // on one of the Teams below) so picking one doesn't imply a
            // Team link — only explicit TEAM entries count.
            const teamIds = [...new Set(picEntries.filter(e => e.pic_type === 'TEAM').map(e => e.ref_id))];

            if (!teamIds.length) {
                Swal.fire({ icon: 'warning', title: 'Pick at least one Team', text: 'Select at least one Team this project belongs to.' });
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
                            const portfolio = Alpine.$data(document.getElementById('pmPortfolioRoot'));
                            if (isEdit && PM_PROJECT_ID === projectId) openProjectDetail(projectId, 'none');
                            // Edited from the scoped board's own Edit button
                            // (editSelectedProject()) — refresh sidebar +
                            // board in place instead of a hard reload.
                            else if (isEdit && portfolio.projectId === projectId) portfolio.loadSidebar();
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
        // Which Team's board is currently loaded — set by loadTeamTaskBoard(),
        // stamped onto a Task/Subtask's history state so popstate/a bookmarked
        // /task/{eid} link knows which Team's data to load before finding it.
        let PM_CURRENT_TEAM_ID = null;
        const PM_CURRENT_USER = { name: @json(Auth::user()->name), username: @json(Auth::user()->username) };

        function pmProjectShow() {
            return {
                tab: 'overview',
                // 'project': showing a Project (Overview's Team/Linked
                // Projects sections apply). 'task': showing a Task/Team-Task
                // (openTaskEntityDetail() below) — those sections hide.
                kind: 'project',
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
                    if (this.kind !== 'project') pendingSubtaskParentId = currentDetailTaskId;
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

                    // Viewing a Task/Team-Task's own detail ('task' top level
                    // or 'subtask' drilled-in) — no board fetch, just its
                    // already-in-memory children (recursive, may be nested
                    // further via the .subtask-row drill-in below).
                    if (this.kind !== 'project') {
                        const task = findTaskInTree(currentDetailTaskId, currentTasksCache);
                        const children = (task && task.children) || [];
                        // Cancelled rows still render below (greyed, with a
                        // Cancelled badge) but are excluded from the
                        // completion math — same rule as openTaskEntityDetail().
                        const activeChildren = children.filter(c => c.status !== 'C');
                        const done = activeChildren.filter(c => c.progress_percent >= 100).length;
                        $('#taskListSummary').html(`<span class="font-semibold text-gray-800 dark:text-gray-100">${done} of ${activeChildren.length}</span> subtasks completed`);

                        // Keep the header's pill/progress bar (populated by
                        // openTaskEntityDetail()) in sync whenever this tab
                        // re-renders — i.e. every create/edit/delete/toggle
                        // of a subtask, not just on first open.
                        const pct = activeChildren.length ? Math.round((done / activeChildren.length) * 100) : 0;
                        $('#detailProjectSubtaskSummary').text(`${done} / ${activeChildren.length} subtasks · ${pct}%`);
                        $('#detailProjectProgressBar').css('width', pct + '%');

                        const $list = $('#taskListPanel').removeClass('hidden').empty();
                        if (!children.length) {
                            $list.append('<p class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">No subtasks yet.</p>');
                        } else {
                            children.forEach(s => $list.append(subtaskRowHtml(s, 'delete-detail-subtask-btn')));
                        }
                        if (typeof cb === 'function') cb();
                        return;
                    }

                    currentTaskApiBase = `{{ url('projects') }}/${PM_PROJECT_ID}/tasks`;
                    currentTaskDoctype = 'TSK';
                    currentTaskRefreshFn = (cb2) => this.renderTaskTab(cb2);

                    $.get(`${currentTaskApiBase}/board-data`, (res) => {
                        this.statuses = res.statuses;
                        this.tasks = res.tasks;
                        currentTasksCache = res.tasks;
                        currentTaskStatuses = res.statuses;
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
                            <div class="w-72 shrink-0 rounded-lg" style="background:${hexToRgba(status.color, 0.08)}">
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
        let currentTaskStatuses = [];    // whichever status list currentTasksCache's tree was loaded with (for the shared detail modal's status pill)
        let taskDetailStack = [];        // ancestor task_ids, for the detail modal's Back button
        let spreadsheetCollapsedIds = new Set(); // task_ids collapsed in the current "By Spreadsheet" render

        // The shared detail modal (#projectDetailModal) always shows exactly
        // one entity — either a Project ('PRJ') or a Task/Team-Task ('TSK'/
        // 'TTK') — identified by these two, used by the modal's own Chat/File
        // tabs (comments + attachments belong to whichever entity is open).
        let PM_ENTITY_DOCTYPE = 'PRJ';
        let PM_ENTITY_ID = null;

        function findTaskInTree(taskId, nodes) {
            for (const n of (nodes || [])) {
                if (n.task_id === taskId) return n;
                const found = findTaskInTree(taskId, n.children);
                if (found) return found;
            }
            return null;
        }

        // Same walk as findTaskInTree(), keyed by a Task/Subtask's own
        // /task/{eid} instead of its task_id — used to resolve a deep link.
        function findTaskByEid(eid, nodes) {
            for (const n of (nodes || [])) {
                if (n.eid === eid) return n;
                const found = findTaskByEid(eid, n.children);
                if (found) return found;
            }
            return null;
        }

        // Ancestor task_ids from root down to (not including) whichever task
        // carries this eid — same shape taskDetailStack already keeps while
        // drilling in via the .subtask-row click handler — so a deep link
        // opens at any depth with a working Back button, not just top-level.
        function findAncestorTaskIds(eid, nodes, trail = []) {
            for (const n of (nodes || [])) {
                if (n.eid === eid) return trail;
                const found = findAncestorTaskIds(eid, n.children, [...trail, n.task_id]);
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
            openTaskEntityDetail(task);
        };

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

        // A row/card/bar is "late" once its own end_date has passed with it
        // still short of 100% — cancelled items and already-done items are
        // never late regardless of date. `progressPct` should already be
        // whichever value the caller displays (a parent task's own
        // progress_percent is overridden by its children's completion
        // everywhere else in this file, so late-ness follows the same rule).
        function isLate(endDate, progressPct, cancelled) {
            if (!endDate || cancelled || progressPct >= 100) return false;
            return dayjs(endDate).isBefore(dayjs(), 'day');
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
            const cancelled = s.status === 'C';
            // Cancelled grandchildren don't count toward this row's own
            // child-progress badge — same "excluded from completion %" rule
            // applied at every level.
            const children = (s.children || []).filter(c => c.status !== 'C');
            const childDone = children.filter(c => c.progress_percent >= 100).length;
            // The cancel toggle (POST .../cancel) only exists on the Team
            // Task route today — hide it for a Project's own tasks (TSK)
            // rather than show a button that 404s.
            const showCancelToggle = currentTaskDoctype === 'TTK';
            return `
                <div class="subtask-row group flex cursor-pointer items-start gap-3 rounded-xl border px-3.5 py-3 transition ${cancelled
                    ? 'border-gray-100 bg-gray-50/40 opacity-60 dark:border-white/[0.04] dark:bg-white/[0.01]'
                    : 'border-gray-100 bg-gray-50/60 hover:border-indigo-200 hover:bg-indigo-50/40 dark:border-white/[0.06] dark:bg-white/[0.02] dark:hover:border-indigo-500/30 dark:hover:bg-indigo-900/10'}" data-subtask-id="${s.task_id}">
                    <button type="button" class="subtask-check mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition ${done ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 text-transparent hover:border-emerald-400 dark:border-white/20'}" title="Mark ${done ? 'incomplete' : 'complete'}" ${cancelled ? 'disabled' : ''}>
                        <i class="fas fa-check text-[9px]"></i>
                    </button>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200 ${(done || cancelled) ? 'text-gray-400 line-through dark:text-gray-500' : ''}">${s.task_name}</p>
                            ${cancelled ? `<span class="shrink-0 rounded-full bg-gray-200 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-white/10 dark:text-gray-400">Cancelled</span>` : ''}
                        </div>
                        ${stripHtml(s.task_description || '') ? `<p class="mt-0.5 truncate text-xs text-gray-400">${stripHtml(s.task_description)}</p>` : ''}
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
                                <i class="fas fa-calendar text-[9px]"></i> ${formatDate(s.start_date)} → ${formatDate(s.end_date)}
                            </span>
                            ${children.length ? `<span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10"><i class="fas fa-list-check text-[9px]"></i> ${childDone}/${children.length}</span>` : ''}
                            ${subtaskPicHtml(s.assignee_people)}
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1 opacity-0 transition group-hover:opacity-100">
                        ${showCancelToggle ? `
                            <button type="button" class="subtask-cancel-toggle-btn rounded-lg p-1.5 text-gray-300 transition hover:bg-amber-50 hover:text-amber-500 dark:hover:bg-amber-900/20" data-subtask-id="${s.task_id}" title="${cancelled ? 'Restore' : 'Cancel'}">
                                <i class="fas ${cancelled ? 'fa-rotate-left' : 'fa-ban'} text-xs"></i>
                            </button>
                        ` : ''}
                        <button type="button" class="${deleteClass} rounded-lg p-1.5 text-gray-300 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20" data-subtask-id="${s.task_id}" title="Archive">
                            <i class="fas fa-box-archive text-xs"></i>
                        </button>
                    </div>
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

        // Recursive <tr> markup for "By Spreadsheet" — same "children win
        // over own progress_percent" completion rule as everywhere else in
        // this file (teamTaskCard/subtaskRowHtml/openTaskEntityDetail), plus
        // an ancestor task_id chain per row (data-ancestors) so a click can
        // set taskDetailStack directly and the collapse toggle can hide/show
        // every descendant regardless of depth (see applySpreadsheetCollapse).
        function spreadsheetRowsHtml(nodes, depth, ancestors) {
            return (nodes || []).map(t => {
                const cancelled = t.status === 'C';
                const allChildren = t.children || [];
                // Cancelled children are excluded from the completion math
                // (same rule as teamTaskCard/subtaskRowHtml) but still
                // rendered — via allChildren below — as their own (grayed-
                // out) row rather than dropped from the tree.
                const activeChildren = allChildren.filter(c => c.status !== 'C');
                const childDone = activeChildren.filter(c => c.progress_percent >= 100).length;
                const displayPct = activeChildren.length ? Math.round((childDone / activeChildren.length) * 100) : t.progress_percent;
                const done = displayPct >= 100;
                const desc = stripHtml(t.task_description || '');
                const late = isLate(t.end_date, displayPct, cancelled);

                const row = `
                    <tr data-task-row data-task-id="${t.task_id}" data-ancestors="${ancestors.join(',')}" data-depth="${depth}"
                        class="spreadsheet-row group cursor-pointer border-b border-gray-100 last:border-0 transition hover:bg-indigo-50/40 dark:border-white/[0.04] dark:hover:bg-indigo-900/10 ${cancelled ? 'opacity-60' : ''}">
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-2" style="padding-left:${depth * 20}px">
                                ${allChildren.length
                                    ? `<button type="button" class="spreadsheet-toggle-btn flex h-5 w-5 shrink-0 items-center justify-center rounded text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"><i class="fas fa-chevron-down spreadsheet-toggle-icon text-[10px]"></i></button>`
                                    : `<span class="inline-block h-5 w-5 shrink-0"></span>`}
                                <button type="button" class="spreadsheet-check flex h-4 w-4 shrink-0 items-center justify-center rounded border-2 transition ${done ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 text-transparent hover:border-emerald-400 dark:border-white/20'}" title="Mark ${done ? 'incomplete' : 'complete'}" ${cancelled ? 'disabled' : ''}>
                                    <i class="fas fa-check text-[8px]"></i>
                                </button>
                                <span class="truncate text-sm font-medium text-gray-700 dark:text-gray-200 ${(done || cancelled) ? 'text-gray-400 line-through dark:text-gray-500' : ''}">${t.task_name}</span>
                                ${cancelled ? `<span class="shrink-0 rounded-full bg-gray-200 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-white/10 dark:text-gray-400">Cancelled</span>` : ''}
                                ${late ? `<span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-red-600 dark:bg-red-500/10 dark:text-red-400"><i class="fas fa-triangle-exclamation text-[9px]"></i> Late</span>` : ''}
                                ${activeChildren.length ? `<span class="shrink-0 rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-500 dark:bg-white/10 dark:text-gray-400">${childDone}/${activeChildren.length}</span>` : ''}
                            </div>
                        </td>
                        <td class="max-w-[220px] truncate px-3 py-2.5 text-xs text-gray-400">${desc || '—'}</td>
                        <td class="px-3 py-2.5">${subtaskPicHtml(t.assignee_people) || '<span class="text-xs text-gray-300">—</span>'}</td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-xs ${late ? 'font-semibold text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'}">${formatDate(t.end_date)}</td>
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 w-20 rounded-full bg-gray-100 dark:bg-gray-700">
                                    <div class="h-1.5 rounded-full ${displayPct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500'}" style="width:${displayPct}%"></div>
                                </div>
                                <span class="shrink-0 text-xs font-semibold ${displayPct >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-indigo-600 dark:text-indigo-300'}">${displayPct}%</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-xs text-gray-400">${formatDate(t.created_at)}</td>
                    </tr>
                `;

                return row + (allChildren.length ? spreadsheetRowsHtml(allChildren, depth + 1, [...ancestors, t.task_id]) : '');
            }).join('');
        }

        // Applies spreadsheetCollapsedIds to a freshly-rendered spreadsheet
        // table: a row is hidden if ANY of its ancestors is collapsed, and
        // each parent's chevron reflects its own collapsed state — walking
        // data-ancestors instead of DOM siblings keeps this correct no
        // matter how deep the tree nests.
        function applySpreadsheetCollapse($scope) {
            $scope.find('tr[data-task-row]').each((i, el) => {
                const $row = $(el);
                const ancestors = ($row.attr('data-ancestors') || '').split(',').filter(Boolean);
                $row.toggleClass('hidden', ancestors.some(id => spreadsheetCollapsedIds.has(id)));
                const collapsedSelf = spreadsheetCollapsedIds.has($row.attr('data-task-id'));
                $row.find('> td:first-child .spreadsheet-toggle-icon').toggleClass('fa-chevron-down', !collapsedSelf).toggleClass('fa-chevron-right', collapsedSelf);
            });
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

        // Populates the SAME shared modal (#projectDetailModal) as
        // openProjectDetail() below, but from an in-memory Task/Team-Task
        // node (currentTasksCache) instead of a fresh AJAX call — the whole
        // recursive tree is already loaded by whichever board is showing.
        // Assumes the caller already set currentTaskApiBase/currentTaskDoctype
        // (and currentTaskRefreshFn) for `task`'s own children/CRUD, exactly
        // like the old openTaskDetail() did.
        function openTaskEntityDetail(task, historyMode = 'push') {
            currentDetailTaskId = task.task_id;
            PM_ENTITY_DOCTYPE = currentTaskDoctype;
            PM_ENTITY_ID = task.task_id;

            const root = Alpine.$data(document.getElementById('pmProjectShowRoot'));
            // 'task': opened directly from a card (top level) — Chat/File
            // apply. 'subtask': drilled into one of its children via the
            // .subtask-row handler below — Chat/File hide, only
            // Overview + Sub Task make sense that deep.
            root.kind = taskDetailStack.length > 0 ? 'subtask' : 'task';
            root.tab = 'overview';
            // Sub Task panel used to only refresh when its tab was clicked
            // (@click="tab='tasks'; renderTaskTab()"); now that 'subtask'
            // kind shows it unconditionally (side-by-side with Overview),
            // it needs its own refresh here or it'd still show whichever
            // task's children were last rendered. Cheap: reads from the
            // already-in-memory currentTasksCache, no AJAX.
            root.renderTaskTab();

            $('#detailBackBtn').toggleClass('hidden', taskDetailStack.length === 0);

            $('#detailProjectName').text(task.task_name);
            $('#detailProjectDescription').html(task.task_description || '—');
            $('#detailProjectDates').text(`${formatDate(task.start_date)} → ${formatDate(task.end_date)}`);
            $('#detailProjectCancelledBadge').toggleClass('hidden', task.status !== 'C');

            // Cancelled children are excluded from the completion math
            // entirely (not counted as done, not counted toward the total)
            // — same rule applied everywhere else (subtaskRowHtml, Kanban
            // card, the Sub Task tab's own summary line).
            const children = (task.children || []).filter(c => c.status !== 'C');
            const childDone = children.filter(c => c.progress_percent >= 100).length;
            const childPct = children.length ? Math.round((childDone / children.length) * 100) : 0;
            $('#detailProjectSubtaskSummary').text(`${childDone} / ${children.length} subtasks · ${childPct}%`);
            $('#detailProjectProgressBar').css('width', childPct + '%');

            const status = currentTaskStatuses.find(s => s.status_id === task.status_id);
            const $statusPill = $('#detailProjectStatusPill');
            if (status) {
                $statusPill.text(status.status_name)
                    .css({ background: hexToRgba(status.color || '#6366F1', 0.15), color: status.color || '#6366F1' })
                    .removeClass('hidden');
            } else {
                $statusPill.addClass('hidden');
            }
            $('#detailProjectStatusMeta').text(status?.status_name || '—');
            $('#detailProjectDueDate').text(task.end_date || '—');

            const pics = task.assignee_people || [];
            const $picsHeader = $('#detailProjectPics').empty();
            pics.slice(0, 5).forEach((p, i) => $picsHeader.append(p.photo_url
                ? `<img src="${p.photo_url}" title="${p.name}" class="h-6 w-6 rounded-full object-cover ring-2 ring-white dark:ring-[#0f172a]" style="margin-left:${i === 0 ? '0' : '-8px'}">`
                : `<span title="${p.name}" style="margin-left:${i === 0 ? '0' : '-8px'}" class="inline-block rounded-full ring-2 ring-white dark:ring-[#0f172a]">${initialsAvatar(p.name, 24)}</span>`
            ));

            const $picList = $('#detailProjectPicList').empty();
            if (pics.length) {
                pics.forEach(p => $picList.append(`
                    <div class="flex items-center gap-2.5">
                        ${p.photo_url ? `<img src="${p.photo_url}" alt="${p.name}" class="h-7 w-7 rounded-full object-cover">` : initialsAvatar(p.name, 28)}
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">${p.name}</span>
                    </div>
                `));
            } else {
                $picList.append('<p class="text-sm text-gray-400">Unassigned.</p>');
            }

            const $tags = $('#detailProjectTags').empty();
            if (task.tags && task.tags.length) {
                task.tags.forEach(tag => $tags.append(`<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium text-white" style="background:${tag.color || '#6366F1'}">${tag.tag_name}</span>`));
            } else {
                $tags.append('<span class="text-sm text-gray-400">No tags.</span>');
            }

            // Chat participants + assignee picker pool — same eligible-users
            // source pattern already used by openTaskModal()/openSubtaskModal().
            const eligible = currentTaskDoctype === 'TTK'
                ? (window.PM_CURRENT_TEAM_MEMBERS || [])
                : (root.eligibleUsers || []);
            const $chatParticipants = $('#chatParticipants').empty();
            eligible.slice(0, 6).forEach((u, i) => $chatParticipants.append(
                `<span title="${u.name}" style="margin-left:${i === 0 ? '0' : '-8px'}" class="inline-block rounded-full ring-2 ring-white dark:ring-[#0f172a]">${initialsAvatar(u.name, 22)}</span>`
            ));
            $('#chatParticipantsLabel').text(eligible.length ? `${eligible.length} people in this chat` : '');
            $('#chatSelfAvatar').html(initialsAvatar(PM_CURRENT_USER.name, 30));

            const $assignees = $('#task_assignees');
            const keepSelected = $assignees.val() || [];
            $assignees.empty();
            eligible.forEach(u => $assignees.append(new Option(`${u.name} (${u.username})`, u.username, false, keepSelected.includes(u.username))));
            $assignees.trigger('change');

            $('#projectDetailModal').removeClass('hidden');
            refreshEntityAttachments();
            loadEntityComments();

            // A Team Task/Subtask gets its own shareable /task/{eid} URL
            // (same convention as a Project's /projects/{eid}) — a Project's
            // OWN tasks (doctype 'TSK', opened via window.pmOpenTask()) stay
            // untracked, same as before, since they have no standalone page.
            if (currentTaskDoctype === 'TTK' && task.eid) {
                const url = `{{ url('task') }}/${task.eid}`;
                if (historyMode === 'push') history.pushState({ taskEid: task.eid, teamId: PM_CURRENT_TEAM_ID }, '', url);
                else if (historyMode === 'replace') history.replaceState({ taskEid: task.eid, teamId: PM_CURRENT_TEAM_ID }, '', url);
            }
        }

        // Files picked in the subtask form's Attachment field, staged until
        // the form actually saves (there's no task_id to upload against
        // before that) — then pushed to the currently-open entity's own File tab.
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

        // Same rich-text setup as #taskModal's description, for the Quick Add
        // Card modal (Team boards' "+ Add card" — see openQuickAddCard()).
        function initQcDescrEditor() {
            if (window.qcDescrQuill) return;

            window.qcDescrQuill = new Quill('#qc_description_editor', {
                theme: 'snow',
                placeholder: 'Optional details…',
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

        // Files staged in the Quick Add Card modal's Attachment field, same
        // pattern as stagedSubtaskFiles — uploaded once the new task's id
        // comes back from the create response.
        let stagedQcFiles = [];

        function renderStagedQcFiles() {
            const $wrap = $('#qcAttachmentsPreview').empty();
            stagedQcFiles.forEach((f, i) => $wrap.append(`
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-600 dark:bg-white/10 dark:text-gray-300">
                    <i class="fas fa-paperclip text-[10px]"></i> ${f.name}
                    <button type="button" class="staged-qc-file-remove text-gray-400 hover:text-red-500" data-i="${i}">&times;</button>
                </span>
            `));
        }

        // Set by pmProjectShow.openNewTask() when "Add subtask" is clicked
        // while viewing a Task's own detail (kind !== 'project') — tells
        // #taskForm's submit handler to create the new row as a CHILD of the
        // currently-open task/subtask, instead of a new top-level row.
        // Cleared on every reset so an Edit (which reuses this same form)
        // never sends it.
        let pendingSubtaskParentId = null;

        function resetTaskForm() {
            $('#taskForm')[0].reset();
            $('#task_id').val('');
            $('#task_assignees').val(null).trigger('change');
            $('#deleteTaskBtn, #toggleCancelTaskBtn').addClass('hidden');
            $('#taskModalTitle').text('New Subtask');
            stagedSubtaskFiles = [];
            renderStagedSubtaskFiles();
            initTaskDescrEditor();
            window.taskDescrQuill?.setText('');
            pendingSubtaskParentId = null;
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
            // Cancel/Restore only exists on the Team Task route today (see
            // TeamTaskController::cancel()) — hide it for a Project's own
            // tasks (TSK) rather than show a button that 404s.
            if (currentTaskDoctype === 'TTK') {
                const cancelled = task.status === 'C';
                $('#toggleCancelTaskBtn').removeClass('hidden')
                    .data('cancelled', cancelled)
                    .html(`<i class="fas ${cancelled ? 'fa-rotate-left' : 'fa-ban'} text-xs"></i> ${cancelled ? 'Restore Task' : 'Cancel Task'}`);
            }
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
                PM_ENTITY_DOCTYPE = 'PRJ';
                PM_ENTITY_ID = data.project_id;
                currentDetailTaskId = null;
                taskDetailStack = [];
                $('#detailBackBtn').addClass('hidden');
                currentProjectDetail = data;

                $('#detailProjectName').text(data.project_name);
                $('#detailProjectDescription').text(data.project_description || '—');
                $('#detailProjectDates').text(`${formatDate(data.start_date)} → ${formatDate(data.end_date)}`);
                $('#detailProjectCancelledBadge').addClass('hidden');

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
                root.kind = 'project';
                root.tab = 'overview';
                root.eligibleUsers = data.eligible_users || [];

                $('#projectDetailModal').removeClass('hidden');
                refreshEntityAttachments();
                loadEntityComments();

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
            const root = Alpine.$data(document.getElementById('pmProjectShowRoot'));
            const wasProject = root.kind === 'project';
            // A Project's OWN tasks (doctype 'TSK') never got a URL of their
            // own (unlike a Team Task's /task/{eid} — see openTaskEntityDetail())
            // so only a Team Task/Subtask detail needs its URL cleaned up here too.
            const wasTeamTask = !wasProject && currentTaskDoctype === 'TTK';
            root.kind = 'project';
            PM_PROJECT_ID = null;
            PM_ENTITY_DOCTYPE = 'PRJ';
            PM_ENTITY_ID = null;
            currentDetailTaskId = null;
            taskDetailStack = [];

            if (!wasProject && !wasTeamTask) return;
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
        // staged Attachment field — both land in whichever entity
        // (PM_ENTITY_DOCTYPE/PM_ENTITY_ID) is currently open in the shared
        // detail modal, so everything a subtask attaches is visible in that
        // same entity's own File tab.
        function uploadFilesToProjectAttachments(files, doctype, entityId, onDone) {
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
                url: `{{ url('attachments') }}/${doctype}/${entityId}`, method: 'POST', data: fd, processData: false, contentType: false,
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

        function refreshEntityAttachments() {
            const listUrl = `{{ url('attachments') }}/${PM_ENTITY_DOCTYPE}/${PM_ENTITY_ID}`;
            $.get(listUrl).done(res => {
                const $list = $('#projectAttachmentList').empty();
                if (!res.success || !res.attachments || !res.attachments.length) {
                    $list.append('<p class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">No attachments yet.</p>');
                    return;
                }
                res.attachments.forEach(at => $list.append(attachmentRowHtml(at)));
            });
        }

        function loadEntityComments() {
            const $list = $('#projectCommentList').html('<p class="italic text-gray-400">Loading comments...</p>');
            $.get(`/comments/${PM_ENTITY_DOCTYPE}/${PM_ENTITY_ID}`, function (res) {
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

            $('#qc_attachments').on('change', function () {
                stagedQcFiles = stagedQcFiles.concat(Array.from(this.files));
                renderStagedQcFiles();
                this.value = '';
            });

            $(document).on('click', '.staged-qc-file-remove', function () {
                stagedQcFiles.splice($(this).data('i'), 1);
                renderStagedQcFiles();
            });

            // ── Project detail modal open/close — no backdrop-click-close ──
            $('#closeProjectDetailModal, #closeProjectDetailModalBtn').on('click', () => closeProjectDetail('push'));

            $('#projectDetailEditBtn').on('click', function () {
                if (currentDetailTaskId) {
                    const t = findTaskInTree(currentDetailTaskId, currentTasksCache);
                    $('#projectDetailModal').addClass('hidden');
                    if (t) openTaskModal(t);
                } else {
                    openEditProject();
                }
            });

            $(document).on('click', '.project-card-open', function (e) {
                e.preventDefault();
                openProjectDetail($(this).data('project-id'), 'push');
            });

            window.addEventListener('popstate', function (e) {
                if (e.state && e.state.projectId) {
                    openProjectDetail(e.state.projectId, 'none');
                } else if (e.state && e.state.taskEid) {
                    const portfolio = Alpine.$data(document.getElementById('pmPortfolioRoot'));
                    if (PM_CURRENT_TEAM_ID === e.state.teamId) {
                        // Already on the right Team's board — just find and
                        // reopen (at whatever drill depth it was at).
                        const target = findTaskByEid(e.state.taskEid, currentTasksCache);
                        if (target) {
                            taskDetailStack = findAncestorTaskIds(e.state.taskEid, currentTasksCache) || [];
                            currentTaskApiBase = `{{ url('all-team') }}/${e.state.teamId}/tasks`;
                            currentTaskDoctype = 'TTK';
                            currentTaskRefreshFn = (cb) => portfolio.loadTeamTaskBoard(cb);
                            openTaskEntityDetail(target, 'none');
                        }
                    } else {
                        // Navigated back/forward across two different Teams'
                        // tasks — switch board first, same deep-link path a
                        // fresh /task/{eid} page load uses.
                        portfolio.teamId = e.state.teamId;
                        portfolio.pendingOpenTaskEid = e.state.taskEid;
                        portfolio.loadTeamTaskBoard();
                    }
                } else {
                    $('#projectDetailModal').addClass('hidden');
                    PM_PROJECT_ID = null;
                    PM_ENTITY_DOCTYPE = 'PRJ';
                    PM_ENTITY_ID = null;
                    currentDetailTaskId = null;
                    taskDetailStack = [];
                }
            });

            const initialProjectId = @json($openProjectId ?? null);
            if (initialProjectId) {
                openProjectDetail(initialProjectId, 'replace');
            }

            // ── Shared detail modal's Back button (drilling into a nested
            // Task/Team-Task's own subtasks — see the .subtask-row handler
            // below) ──
            $('#detailBackBtn').on('click', function () {
                const prevId = taskDetailStack.pop();
                if (!prevId) return;
                const prev = findTaskInTree(prevId, currentTasksCache);
                if (prev) openTaskEntityDetail(prev);
            });

            // A child row drills into its OWN detail view (kind becomes
            // 'subtask' — hides Chat/File, see the tab bar above) and can
            // have further children — the same recursive behavior as
            // opening a top-level card.
            $(document).on('click', '#taskListPanel .subtask-row', function () {
                const childId = $(this).data('subtask-id');
                const child = findTaskInTree(childId, currentTasksCache);
                if (child) {
                    taskDetailStack.push(currentDetailTaskId);
                    openTaskEntityDetail(child);
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
                            Alpine.$data(document.getElementById('pmProjectShowRoot')).renderTaskTab();
                        });
                    }
                });
            });

            // Row-level Cancel/Restore toggle (Team Task subtasks only —
            // see subtaskRowHtml()'s showCancelToggle).
            $(document).on('click', '.subtask-cancel-toggle-btn', function (e) {
                e.stopPropagation();
                const subtaskId = $(this).data('subtask-id');
                $.ajax({
                    url: `${currentTaskApiBase}/${subtaskId}/cancel`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        refreshTaskDetailContext(() => {
                            Alpine.$data(document.getElementById('pmProjectShowRoot')).renderTaskTab();
                        });
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong.');
                    }
                });
            });

            // Card-level Cancel/Restore + Archive on a Team's own Kanban
            // (teamTaskCard()) — currentTaskApiBase/currentTaskRefreshFn are
            // already the Team's own (set by loadTeamTaskBoard()), so these
            // don't need any Alpine component lookup.
            $(document).on('click', '.team-card-cancel-btn', function (e) {
                e.stopPropagation();
                const taskId = $(this).data('task-id');
                $.ajax({
                    url: `${currentTaskApiBase}/${taskId}/cancel`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () { refreshTaskDetailContext(); },
                    error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Something went wrong.'); }
                });
            });

            $(document).on('click', '.team-card-archive-btn', function (e) {
                e.stopPropagation();
                const taskId = $(this).data('task-id');
                if (!confirm('Archive this task?')) return;
                $.ajax({
                    url: `${currentTaskApiBase}/${taskId}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () { refreshTaskDetailContext(); },
                    error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Something went wrong.'); }
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
                const parentId = !taskId ? pendingSubtaskParentId : null;
                let serialized = $(this).serialize();
                if (parentId) serialized += `&parent_task_id=${encodeURIComponent(parentId)}`;

                $.ajax({
                    url, method,
                    data: serialized + '&_token={{ csrf_token() }}',
                    success: function (res) {
                        $('#taskModal').addClass('hidden');
                        toastr.success(res.message);
                        uploadFilesToProjectAttachments(filesToUpload, PM_ENTITY_DOCTYPE, PM_ENTITY_ID, () => refreshEntityAttachments());
                        refreshTaskDetailContext(() => {
                            const root = Alpine.$data(document.getElementById('pmProjectShowRoot'));
                            if (taskId && currentDetailTaskId === taskId) {
                                // Editing the currently-open entity itself — full re-populate.
                                const t = findTaskInTree(taskId, currentTasksCache);
                                if (t) openTaskEntityDetail(t, 'replace');
                            } else if (root.kind !== 'project') {
                                // Created/edited a child under the open Task/Subtask — just refresh its list.
                                root.renderTaskTab();
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
                        closeProjectDetail('none');
                        refreshTaskDetailContext();
                    }
                });
            });

            $('#toggleCancelTaskBtn').on('click', function () {
                const taskId = $('#task_id').val();
                if (!taskId) return;
                $.ajax({
                    url: `${currentTaskApiBase}/${taskId}/cancel`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        $('#taskModal').addClass('hidden');
                        // Cancelling stays visible (unlike Archive) — reopen
                        // the entity if it's the one currently shown, else
                        // just refresh whichever list it appears in.
                        refreshTaskDetailContext(() => {
                            const t = findTaskInTree(taskId, currentTasksCache);
                            if (t && currentDetailTaskId === taskId) openTaskEntityDetail(t, 'replace');
                            else Alpine.$data(document.getElementById('pmProjectShowRoot')).renderTaskTab();
                        });
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong.');
                    }
                });
            });

            $(document).on('click', '#addTaskStatusBtn', function () {
                Swal.fire({
                    title: 'Add status column',
                    html: `
                        <div class="flex gap-2">
                            <input id="swal_status_color" type="color" value="#6366F1" title="Column color"
                                class="h-10 w-10 shrink-0 cursor-pointer rounded-lg border border-slate-300 p-1">
                            <input id="swal_status_name" type="text" placeholder="e.g. Blocked"
                                class="swal2-input !m-0 !h-10 !w-full" style="width:100%">
                        </div>
                    `,
                    showCancelButton: true,
                    focusConfirm: false,
                    preConfirm: () => {
                        const name = document.getElementById('swal_status_name').value.trim();
                        if (!name) { Swal.showValidationMessage('Status name is required'); return false; }
                        return { name, color: document.getElementById('swal_status_color').value };
                    },
                }).then((result) => {
                    if (!result.isConfirmed || !result.value) return;
                    $.post(`{{ url('projects') }}/${PM_PROJECT_ID}/tasks/statuses`, {
                        status_name: result.value.name,
                        color: result.value.color,
                        _token: '{{ csrf_token() }}',
                    }, function () {
                        Alpine.$data(document.getElementById('pmProjectShowRoot')).renderTaskTab();
                    });
                });
            });

            // Checkmark toggle for a Task-detail view's own children list.
            $(document).on('click', '.subtask-check', function (e) {
                e.stopPropagation();
                const subtaskId = $(this).closest('.subtask-row').data('subtask-id');
                const subtask = findTaskInTree(subtaskId, currentTasksCache);
                if (!subtask) return;
                toggleTaskProgress(subtask, () => {
                    refreshTaskDetailContext(() => {
                        Alpine.$data(document.getElementById('pmProjectShowRoot')).renderTaskTab();
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
                uploadFilesToProjectAttachments(files, PM_ENTITY_DOCTYPE, PM_ENTITY_ID, () => {
                    toastr.success('Uploaded.');
                    $('#projectAttachFiles').val('');
                    refreshEntityAttachments();
                });
            });

            $(document).on('click', '.attachment-row', function () {
                openFilePreview($(this).data('name'), $(this).data('url'));
            });

            $('#closeFilePreviewModal').on('click', function () {
                $('#filePreviewModal').addClass('hidden');
                $('#filePreviewBody').empty(); // stop any playing <video>
            });

            // ── Chat ── mentionable-users lives at a different URL shape for
            // a Task/Team-Task (nested under its own tasks API) than for a
            // Project, so branch on whether a Task is currently open.
            attachMentionAutocomplete({
                inputSelector: '#projectCommentInput',
                fetchUrlFn: () => {
                    if (!PM_ENTITY_ID) return null;
                    return currentDetailTaskId
                        ? `${currentTaskApiBase}/${currentDetailTaskId}/mentionable-users`
                        : `{{ url('projects') }}/${PM_ENTITY_ID}/mentionable-users`;
                },
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
                if (!val || !PM_ENTITY_ID) return;
                $.post(`/comments/${PM_ENTITY_DOCTYPE}/${PM_ENTITY_ID}`, { comment: val, _token: '{{ csrf_token() }}' }, function () {
                    $('#projectCommentInput').val('');
                    loadEntityComments();
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
