<x-app-layout>
    {{-- Built up front: @json() splits its argument on commas, so an inline array literal breaks. --}}
    @php
        $pmPortfolioOpts = [
            'tab' => $initialTab,
            'openTeamId' => $openTeamId ?? null,
            'openProjectBoardId' => $openProjectBoardId ?? null,
            'openTaskEid' => $openTaskEid ?? null,
        ];
    @endphp
    <div id="pmPortfolioRoot" class="mx-auto flex h-[calc(100dvh-72px)] w-full max-w-9xl gap-4 p-2" x-data='pmPortfolio(@json($pmPortfolioOpts))'>

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
                    <button x-show="canCreateProject" @click="openNewProject()"
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
                                        <i class="fas shrink-0 text-xs" :class="projectId === f.id ? 'fa-folder-open' : 'fa-folder'" :style="`color:${statusColor(f.status_id)}`"></i>
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
                                    <i class="fas shrink-0 text-xs" :class="projectId === p.project_id ? 'fa-folder-open' : 'fa-folder'" :style="`color:${statusColor(p.status_id)}`"></i>
                                    <span class="truncate" x-text="p.project_name"></span>
                                </button>
                                <button @click.stop.prevent="toggleFavorite('PROJECT', p.project_id)" title="Favorite"
                                    class="shrink-0 px-2 py-2 text-xs transition"
                                    :class="p.is_favorite ? 'text-amber-400' : 'text-gray-300 opacity-0 group-hover:opacity-100 hover:text-amber-400 dark:text-gray-600'">
                                    <i :class="p.is_favorite ? 'fas' : 'far'" class="fa-star"></i>
                                </button>
                                <button x-show="canCreateProject" @click.stop.prevent="archiveProject(p.project_id, p.project_name)" title="Archive project"
                                    class="shrink-0 py-2 pl-0.5 pr-2 text-xs text-gray-300 opacity-0 transition hover:text-red-500 group-hover:opacity-100 dark:text-gray-600 dark:hover:text-red-400">
                                    <i class="fas fa-box-archive"></i>
                                </button>
                            </div>
                        </template>
                        <p x-show="!projects.length" class="px-2.5 py-1.5 text-xs text-gray-400">No projects yet.</p>
                    </div>
                </div>

                {{-- Collapsed icon rail --}}
                <div x-show="!sidebarOpen" x-cloak class="flex flex-col items-center gap-1 border-t border-gray-100 p-2 dark:border-white/[0.06]">
                    <button x-show="canCreateProject" @click="openNewProject()" title="New Project"
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
                <div class="flex min-w-0 items-center gap-2">
                    <h2 class="truncate text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">📁 <span x-text="headerTitle"></span></h2>
                    <button x-show="isPrimaryAdmin && (teamId || projectId)" x-cloak @click="openStatusPanel()" title="Status settings"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-50 hover:text-indigo-600 dark:hover:bg-gray-800 dark:hover:text-indigo-400">
                        <i class="fas fa-gear text-sm"></i>
                    </button>
                </div>
                <div x-show="teamId || projectId" x-cloak class="flex shrink-0 items-center gap-1">
                    <button x-show="projectId" @click="editSelectedProject()" title="Edit Project"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-50 hover:text-indigo-600 dark:hover:bg-gray-800 dark:hover:text-indigo-400">
                        <i class="fas fa-pen text-xs"></i>
                    </button>
                    <button x-show="projectId" @click="archiveSelectedProject()" title="Archive Project"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-gray-800 dark:hover:text-red-400">
                        <i class="fas fa-box-archive text-xs"></i>
                    </button>
                    <button @click="openHistory()" :title="teamId ? 'Team history' : 'Project history'"
                        class="ml-1 inline-flex h-8 items-center gap-1.5 rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 dark:border-white/10 dark:text-gray-300 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300">
                        <i class="fas fa-clock-rotate-left text-[11px]"></i> History
                    </button>
                </div>
            </div>

            {{-- TABS --}}
            <div x-show="teams.length || projects.length" class="flex shrink-0 items-center gap-1 border-b border-gray-100 px-5 pt-2 dark:border-white/[0.06]">
                <button @click="tab = 'kanban'; renderTab()"
                    :class="tab === 'kanban' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">By Kanban</button>
                <button @click="tab = 'calendar'; renderTab()"
                    :class="tab === 'calendar' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">By Calendar</button>
                <button @click="tab = 'spreadsheet'; renderTab()"
                    :class="tab === 'spreadsheet' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="rounded-t-lg px-4 py-2 text-sm font-medium">By Spreadsheet</button>
                {{-- Team/Project-wide chat — only on a scoped board (the
                     Projects portfolio has no single audience to talk to). --}}
                <button x-show="teamId || projectId" x-cloak @click="tab = 'message'; renderTab()"
                    :class="tab === 'message' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="inline-flex items-center gap-1.5 rounded-t-lg px-4 py-2 text-sm font-medium">
                    <i class="fas fa-comments text-xs"></i> Message
                </button>
            </div>

            <div x-show="teams.length || projects.length" class="flex-1 overflow-y-auto p-4">
                <div id="kanbanPanel" class="overflow-x-auto"></div>
                <div id="calendarPanel" class="hidden h-full"></div>
                <div id="spreadsheetPanel" class="hidden"></div>

                {{-- MESSAGE — one thread per Team ('TEAM') / Project ('PRJ',
                     same thread as the Project Detail modal's Chat tab).
                     Driven by openBoardChat() below. --}}
                <div id="messagePanel" class="hidden h-full">
                    <div class="relative flex h-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/[0.06] dark:bg-gray-900">
                        <div class="flex shrink-0 items-center gap-3 border-b border-gray-100 px-4 py-3 dark:border-white/[0.06]">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <i class="fas fa-comments text-sm"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="headerTitle"></p>
                                <p class="text-xs text-gray-400" x-text="teamId ? 'Everyone in this Team can read and reply here' : 'Everyone on this Project can read and reply here'"></p>
                            </div>
                            <div id="boardChatPeople" class="flex shrink-0 items-center"></div>
                        </div>

                        <div id="boardChatList" class="custom-scrollbar flex-1 overflow-y-auto px-4 py-4"></div>

                        <button type="button" id="boardChatNewPill"
                            class="absolute bottom-24 left-1/2 hidden -translate-x-1/2 items-center gap-1.5 rounded-full bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white shadow-lg transition hover:bg-indigo-500">
                            <i class="fas fa-arrow-down text-[10px]"></i> New messages
                        </button>

                        <div id="boardChatStagedFiles" class="hidden shrink-0 flex-wrap gap-1.5 px-4 pt-2"></div>

                        <div class="flex shrink-0 items-end gap-2.5 border-t border-gray-100 px-4 py-3 dark:border-white/[0.06]">
                            <div class="flex flex-1 items-end gap-1 rounded-2xl border border-gray-200 bg-gray-50 pl-4 pr-1.5 dark:border-white/10 dark:bg-white/[0.04]">
                                <textarea id="boardChatInput" rows="1" maxlength="500"
                                    placeholder="Message everyone… use @ to mention, paste a link or a file. Shift+Enter for a new line"
                                    class="max-h-32 flex-1 resize-none border-none bg-transparent py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-0 dark:text-white"></textarea>
                                <button type="button" id="boardChatMentionBtn" title="Mention someone" class="mb-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold text-gray-400 transition hover:bg-white hover:text-indigo-600 dark:hover:bg-white/10 dark:hover:text-indigo-400">@</button>
                                <button type="button" id="boardChatAttachBtn" title="Attach files (max 5MB each)" class="mb-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-gray-400 transition hover:bg-white hover:text-indigo-600 dark:hover:bg-white/10 dark:hover:text-indigo-400"><i class="fas fa-paperclip text-xs"></i></button>
                                <input type="file" id="boardChatFileInput" multiple class="hidden">
                            </div>
                            <button type="button" id="boardChatSendBtn" title="Send"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white shadow-sm transition hover:bg-indigo-500 disabled:cursor-wait disabled:opacity-60"><i class="fas fa-paper-plane text-xs"></i></button>
                        </div>
                    </div>
                </div>
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
                            <p class="mt-1 text-xs text-slate-400">Pick Teams, people, or both. Picking a Team links the project to it. People are anyone with Project access, org-wide.</p>
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
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white" x-text="editingStatusId ? 'Edit status column' : 'Add status column'"></h2>
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
                                class="h-10 shrink-0 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500" x-text="editingStatusId ? 'Save' : 'Add'"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STATUS SETTINGS PANEL (admin only) — full CRUD over the current
         Team/Project's own status columns, sliding in from the right. --}}
    <div x-show="statusPanelOpen" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="statusPanelOpen && closeStatusPanel()">
        <div x-show="statusPanelOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/40" @click="closeStatusPanel()"></div>
        <div x-show="statusPanelOpen"
            x-transition:enter="transform transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="absolute inset-y-0 right-0 flex w-full max-w-2xl flex-col bg-white shadow-2xl dark:bg-slate-800">
            <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">
                        <i class="fas fa-gear text-sm"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Status settings</h2>
                        <p class="text-xs text-slate-400" x-text="headerTitle"></p>
                    </div>
                </div>
                <button type="button" @click="closeStatusPanel()" title="Close" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fas fa-times text-sm"></i></button>
            </div>

            <div class="flex-1 overflow-y-auto p-5">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-medium uppercase tracking-wide text-slate-400 dark:border-slate-700">
                            <th class="py-2 pr-2">Status ID</th>
                            <th class="px-2 py-2">Status Name</th>
                            <th class="px-2 py-2">Color</th>
                            <th class="w-20 px-2 py-2">Order</th>
                            <th class="w-20 py-2 pl-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in statusRows" :key="row.status_id">
                            <tr class="border-b border-slate-100 dark:border-slate-700/60">
                                <td class="py-2 pr-2">
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-slate-600 dark:bg-slate-700 dark:text-slate-300" x-text="row.status_id"></span>
                                </td>
                                <td class="px-2 py-2">
                                    <input type="text" x-model="row.status_name" maxlength="100" @keydown.enter="saveStatusRow(row)"
                                        class="h-9 w-full rounded-lg border border-slate-300 px-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="color" x-model="row.color"
                                        class="h-9 w-10 cursor-pointer rounded-lg border border-slate-300 bg-white p-1 dark:border-slate-600 dark:bg-slate-700">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" min="0" x-model.number="row.sort_order" @keydown.enter="saveStatusRow(row)"
                                        class="h-9 w-full rounded-lg border border-slate-300 px-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                                </td>
                                <td class="py-2 pl-2">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="saveStatusRow(row)" :disabled="!statusRowDirty(row)" title="Save"
                                            class="rounded-lg p-2 text-indigo-600 transition hover:bg-indigo-50 disabled:cursor-default disabled:text-slate-300 disabled:hover:bg-transparent dark:hover:bg-indigo-900/30 dark:disabled:text-slate-600">
                                            <i class="fas fa-check text-xs"></i>
                                        </button>
                                        <button type="button" @click="deleteStatusRow(row)" title="Delete"
                                            class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!statusRows.length">
                            <td colspan="5" class="py-6 text-center text-xs text-slate-400">No statuses yet.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-5 rounded-lg border border-dashed border-slate-300 p-3 dark:border-slate-600">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Add status</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" x-model="newStatusRow.status_id" maxlength="20" placeholder="ID (optional)"
                            class="h-9 w-32 rounded-lg border border-slate-300 px-2.5 font-mono text-xs uppercase dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <input type="text" x-model="newStatusRow.status_name" maxlength="100" placeholder="Status name" @keydown.enter="addStatusRow()"
                            class="h-9 min-w-0 flex-1 rounded-lg border border-slate-300 px-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <input type="color" x-model="newStatusRow.color"
                            class="h-9 w-10 cursor-pointer rounded-lg border border-slate-300 bg-white p-1 dark:border-slate-600 dark:bg-slate-700">
                        <input type="number" min="0" x-model="newStatusRow.sort_order" placeholder="Order"
                            class="h-9 w-20 rounded-lg border border-slate-300 px-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <button type="button" @click="addStatusRow()"
                            class="h-9 shrink-0 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500">Add</button>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-400">ID and order are generated from the name / placed last when left blank. A status ID can't be changed after it's created.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- HISTORY PANEL — everything that happened on the current Team/Project
         board (task changes, chat, files), newest first, with exact
         date/time. Fed by PmProjectController::history() /
         TeamTaskController::history(); rows rendered by activityFeedHtml(). --}}
    <div x-show="historyOpen" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="historyOpen = false">
        <div x-show="historyOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/40" @click="historyOpen = false"></div>
        <div x-show="historyOpen"
            x-transition:enter="transform transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="absolute inset-y-0 right-0 flex w-full max-w-xl flex-col bg-white shadow-2xl dark:bg-[#0f172a]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">
                            <i class="fas fa-clock-rotate-left text-sm"></i>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100" x-text="teamId ? 'Team history' : 'Project history'"></h2>
                            <p class="truncate text-xs text-gray-400" x-text="headerTitle"></p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <button type="button" @click="loadHistory()" title="Refresh" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">
                            <i class="fas fa-rotate-right text-sm" :class="historyLoading ? 'fa-spin' : ''"></i>
                        </button>
                        <button type="button" @click="historyOpen = false" title="Close" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"><i class="fas fa-times text-sm"></i></button>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-1.5">
                    <template x-for="f in historyFilters" :key="f.key">
                        <button type="button" @click="historyFilter = f.key"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition"
                            :class="historyFilter === f.key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10'">
                            <span x-text="f.label"></span>
                            <span class="opacity-70" x-text="historyCount(f.key)"></span>
                        </button>
                    </template>
                </div>
                <div class="relative mt-2.5">
                    <i class="fas fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                    <input type="text" x-model.debounce.200ms="historySearch" placeholder="Search by person, task, or change…"
                        class="h-9 w-full rounded-lg border border-gray-200 pl-8 pr-3 text-sm text-gray-800 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:focus:ring-indigo-900/30">
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4">
                <div x-show="historyLoading && !historyItems.length" class="flex items-center justify-center gap-2 py-16 text-sm text-gray-400">
                    <i class="fas fa-spinner fa-spin"></i> Loading history…
                </div>
                <div x-show="!historyLoading || historyItems.length" x-html="activityFeedHtml(filteredHistory, { showTask: true })"></div>
                <p x-show="historyItems.length >= {{ \App\Services\PmActivityLogger::FEED_LIMIT }}" class="mt-4 text-center text-[11px] text-gray-400">Showing the latest {{ \App\Services\PmActivityLogger::FEED_LIMIT }} activities.</p>
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
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white" x-text="quickAddParentId ? 'Add Subtask' : (teamId || projectId) ? 'Add Task' : 'Add Card'"></h2>
                            <p class="text-xs text-slate-400" x-text="quickAddParentId ? `Under: ${quickAddParentName}` : ((teamId || projectId) ? teamTaskStatuses : statuses).find(s => s.status_id === quickAddStatusId)?.status_name"></p>
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
                            <select id="qc_pic" class="select2 w-full" multiple data-placeholder="Select team(s) and/or person(s) in charge"></select>
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
    {{-- Move a Task (with its subtasks) to another Team/Project board —
         opened from the detail header's Move button (openTaskMoveModal()). --}}
    <div id="taskMoveModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-[#0f172a] dark:ring-white/10">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Move task</h3>
                        <p id="taskMoveTaskName" class="truncate text-xs text-gray-400"></p>
                    </div>
                    <button type="button" class="task-move-close flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10"><i class="fas fa-times"></i></button>
                </div>
                <div class="space-y-4 px-5 py-4">
                    <div>
                        <label for="taskMoveTarget" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Team or Project</label>
                        <select id="taskMoveTarget" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-400 focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-gray-200"></select>
                    </div>
                    <div>
                        <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</p>
                        <div id="taskMoveStatuses" class="flex flex-wrap gap-2"></div>
                    </div>
                    <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                        <i class="fas fa-circle-info mr-1"></i> Subtasks, chat and files move along. People who aren't on the destination board are removed from it. Moving to a Team makes the task public again.
                    </p>
                </div>
                <div class="flex justify-end gap-2 border-t border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                    <button type="button" class="task-move-close h-9 rounded-lg bg-gray-100 px-4 text-sm font-medium text-gray-600 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">Cancel</button>
                    <button type="button" id="taskMoveSave" disabled class="h-9 rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-40">Move</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Full-size, uncropped view of a task's cover — opened from the cover
         banner's "Full preview" button or by clicking the banner image.
         Backdrop click, the X, or Esc closes it. --}}
    <div id="coverPreviewModal" class="fixed inset-0 z-[80] hidden">
        <div class="cover-preview-close absolute inset-0 bg-slate-950/85 backdrop-blur-sm"></div>
        <div class="pointer-events-none relative flex h-full items-center justify-center p-6 sm:p-10">
            <img id="coverPreviewImg" src="" alt="Task cover" class="pointer-events-auto max-h-full max-w-full rounded-xl object-contain shadow-2xl">
        </div>
        <div class="absolute right-4 top-4 flex items-center gap-2">
            <a id="coverPreviewOpen" href="#" target="_blank" rel="noopener" title="Open original in new tab"
                class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20"><i class="fas fa-up-right-from-square text-sm"></i></a>
            <button type="button" title="Close"
                class="cover-preview-close flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20"><i class="fas fa-times"></i></button>
        </div>
    </div>

    {{-- Book a Meeting Room / Zoom for a subtask — opened from a subtask
         row's "Meeting Room" / "Zoom" buttons (openMeetingBooking()). Embeds
         the Meeting module's own page (/meeting or /meetingteams, ?embed=1)
         so its existing Create-booking modal, validation, conflict checks and
         emails are reused as-is; that page postMessage()s 'pm-meeting-booked'
         back here on a successful save. --}}
    <div id="meetingBookingModal" class="fixed inset-0 z-[70] hidden">
        <div class="absolute inset-0 bg-slate-900/60"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex h-[92vh] w-full max-w-7xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-[#0f172a] dark:ring-white/10">
                <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                    <div class="min-w-0">
                        <h3 id="meetingBookingTitle" class="text-base font-semibold text-gray-800 dark:text-gray-100">Booking Meeting Room</h3>
                        <p id="meetingBookingTaskName" class="truncate text-xs text-gray-400"></p>
                    </div>
                    <button type="button" class="meeting-booking-close flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10"><i class="fas fa-times"></i></button>
                </div>
                <iframe id="meetingBookingFrame" class="min-h-0 w-full flex-1 border-0" src="about:blank"></iframe>
            </div>
        </div>
    </div>

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
                    <button x-show="kind === 'task'" @click="tab = 'activity'; loadTaskActivity()" :class="tab === 'activity' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Activity List</button>
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
                        <div :class="kind === 'subtask' ? 'border-t border-gray-100 pt-6 lg:col-span-5 dark:border-white/[0.06]' : ''">
                            @include('pages.projectmanagement.partials.project-detail-tab-activity')
                        </div>
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
                            {{-- No name attr — #taskForm's submit splits TEAM:/USER: values into assignees[]/team_ids[] (splitTaskPicValues()). --}}
                            <select id="task_assignees" class="select2 w-full" multiple data-placeholder="Assign team(s) and/or person(s) in charge"></select>
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
         render inline, Word/Excel/CSV/text render client-side (openFilePreview),
         anything else falls back to a Download prompt. --}}
    <div id="filePreviewModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>
        <div class="relative flex h-full items-center justify-center p-6">
            <div id="filePreviewPanel" class="flex max-h-[88vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-[#0f172a]">
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

        /* Gantt chart theming (Frappe Gantt, Project Detail's hidden
           #taskGanttPanel only) — the library's built-in dark
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

        /* File preview — SheetJS emits a bare <table>; give it grid lines. */
        .sheet-preview table { border-collapse: collapse; }
        .sheet-preview td { border: 1px solid rgba(148, 163, 184, .35); padding: 4px 8px; white-space: nowrap; }
        /* docx-preview draws white pages on a grey backdrop; keep it inside the modal. */
        #filePreviewBody .docx-wrapper { padding: 16px; border-radius: 8px; }
        #filePreviewBody .docx-wrapper > section.docx { max-width: 100%; }
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
                // same Kanban/Calendar/Spreadsheet shape as a Team's board
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
                editingStatusId: null,
                quickAddStatusId: null,
                // Set when the quick-add modal creates a Subtask (spreadsheet
                // row "+") instead of a top-level Task.
                quickAddParentId: null,
                quickAddParentName: '',
                // Status Settings side panel — admin (not adminsby) only.
                isPrimaryAdmin: @json(auth()->user()->isPrimaryAdmin()),
                statusPanelOpen: false,
                statusRows: [],
                newStatusRow: { status_id: '', status_name: '', color: '#6366F1', sort_order: '' },
                projects: [],
                teamTaskStatuses: [],
                teamTasks: [],
                canCreateProject: @json($canCreateProject),
                loaded: false,

                // "By Calendar" tab — 'month' | 'week', and any date inside
                // the period being shown (see renderCalendar()).
                calView: 'month',
                calCursor: dayjs().format('YYYY-MM-DD'),
                // Team/Project boards only: 'task' (top-level Tasks) or
                // 'subtask' (every Subtask at any depth, with its parent).
                calLevel: 'task',
                calResizeBound: false,

                // History panel (header's History button) — see openHistory().
                historyOpen: false,
                historyLoading: false,
                historyItems: [],
                historyFilter: 'all',
                historySearch: '',
                historyFilters: [
                    { key: 'all', label: 'All' },
                    { key: 'task', label: 'Tasks' },
                    { key: 'board', label: 'Team / Project' },
                    { key: 'chat', label: 'Chat' },
                    { key: 'file', label: 'Files' },
                ],

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
                    this.statusPanelOpen = false;
                    this.projectId = '';
                    this.loadMainPanel();
                },

                // Same idea as selectTeam(), but scopes the main panel to a
                // single Project's own Task board instead of the Projects
                // portfolio's card view — same Kanban/Calendar/Spreadsheet UI a
                // Team gets, just sourced from PmTaskController.
                selectProject(projectId) {
                    this.projectId = this.projectId === projectId ? '' : projectId;
                    this.statusPanelOpen = false;
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

                openHistory() {
                    if (!this.teamId && !this.projectId) return;
                    this.historyFilter = 'all';
                    this.historySearch = '';
                    this.historyItems = [];
                    this.historyOpen = true;
                    this.loadHistory();
                },

                loadHistory() {
                    const url = this.teamId
                        ? `{{ url('all-team') }}/${this.teamId}/tasks/history`
                        : `{{ url('projects') }}/${this.projectId}/history`;
                    this.historyLoading = true;
                    $.get(url, (res) => { this.historyItems = res.items || []; })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Could not load history.'))
                        .always(() => { this.historyLoading = false; });
                },

                historyMatches(item, filter) {
                    if (filter === 'all') return true;
                    if (filter === 'board') return item.kind === 'team' || item.kind === 'project';
                    return item.kind === filter;
                },

                historyCount(filter) {
                    return this.historyItems.filter(i => this.historyMatches(i, filter)).length;
                },

                get filteredHistory() {
                    const q = this.historySearch.trim().toLowerCase();
                    return this.historyItems.filter(i => this.historyMatches(i, this.historyFilter)
                        && (!q || activitySearchText(i).includes(q)));
                },

                archiveSelectedProject() {
                    if (!this.projectId) return;
                    const p = this.projects.find(x => x.project_id === this.projectId);
                    this.archiveProject(this.projectId, p?.project_name);
                },

                // Shared by the scoped board header's Archive and the
                // sidebar's per-project archive button — soft-archive
                // (status 'X') via PmProjectController::destroy().
                archiveProject(projectId, projectName) {
                    if (!projectId) return;

                    Swal.fire({
                        icon: 'warning',
                        title: 'Archive this project?',
                        html: `<b>${$('<div>').text(projectName || projectId).html()}</b> and all of its tasks and subtasks will be removed from the portfolio for everyone. An admin can restore it from Global Settings → Project Setup → Project Archive.`,
                        showCancelButton: true,
                        confirmButtonText: 'Archive',
                        confirmButtonColor: '#DC2626',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: `{{ url('projects') }}/${projectId}`,
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: (res) => {
                                if (this.projectId === projectId) this.projectId = '';
                                Swal.fire({ icon: 'success', title: res.message || 'Project archived', timer: 1500, showConfirmButton: false });
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
                            // A /project-task/{eid} deep link opens that
                            // Project's own Task board instead.
                            if (opts.openProjectBoardId) {
                                this.teamId = '';
                                this.projectId = opts.openProjectBoardId;
                            } else {
                                this.teamId = opts.openTeamId || this.defaultTeamId();
                            }
                        }

                        this.loaded = true;
                        this.loadMainPanel();
                    });
                },

                // Main panel (Kanban/Calendar/Spreadsheet) — either the Projects portfolio,
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

                        // A /project-task/{eid} deep link — same as the
                        // Team board's handling in loadTeamTaskBoard().
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

                    // Same eligible-users pool the Project Detail modal's
                    // Overview tab loads — needed here too so a Task's
                    // assignee/mention picker (openTaskEntityDetail()) has
                    // people to offer without going through that modal first.
                    $.get(`{{ url('projects') }}/${this.projectId}/detail`, (res) => {
                        Alpine.$data(document.getElementById('pmProjectShowRoot')).eligibleUsers = res.eligible_users || [];
                        Alpine.$data(document.getElementById('pmProjectShowRoot')).projectTeams = res.teams || [];
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
                    $('#kanbanPanel, #calendarPanel, #spreadsheetPanel, #messagePanel').addClass('hidden');
                    const scoped = this.teamId || this.projectId;
                    // Message is Team/Project-only — the Projects portfolio
                    // falls back to Kanban.
                    if (this.tab === 'message' && !scoped) this.tab = 'kanban';
                    if (this.tab === 'message') {
                        $('#messagePanel').removeClass('hidden');
                        openBoardChat(this.teamId ? 'TEAM' : 'PRJ', this.teamId || this.projectId);
                    } else {
                        closeBoardChat();
                    }
                    if (this.tab === 'kanban') {
                        $('#kanbanPanel').removeClass('hidden');
                        scoped ? this.renderTeamKanban() : this.renderKanban();
                    }
                    if (this.tab === 'calendar') {
                        $('#calendarPanel').removeClass('hidden');
                        this.renderCalendar();
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
                    this.editingStatusId = null;
                    this.newStatusName = '';
                    this.newStatusColor = '#6366F1';
                    if (this.teamId || this.projectId) this.availableStatuses = [];
                    $('#addStatusModal').removeClass('hidden');
                    $('#new_status_name').focus();
                },

                // Edit pencil on a Team/Project Task-board column header —
                // reuses the same modal as "+ Add status", prefilled and
                // switched into update mode via editingStatusId.
                openEditStatusModal(status) {
                    this.editingStatusId = status.status_id;
                    this.newStatusName = status.status_name;
                    this.newStatusColor = status.color || '#6366F1';
                    this.availableStatuses = [];
                    $('#addStatusModal').removeClass('hidden');
                    $('#new_status_name').focus();
                },

                closeAddStatusModal() {
                    this.editingStatusId = null;
                    $('#addStatusModal').addClass('hidden');
                },

                submitStatus(statusName) {
                    statusName = (statusName ?? '').trim();
                    if (!statusName) return;

                    if (this.editingStatusId) {
                        $.ajax({
                            url: `${this.taskApiBase}/statuses/${this.editingStatusId}`,
                            method: 'PUT',
                            data: {
                                status_name: statusName,
                                color: this.newStatusColor,
                                _token: '{{ csrf_token() }}',
                            },
                            success: () => {
                                this.closeAddStatusModal();
                                this.refreshTaskBoard();
                            },
                            error: (xhr) => {
                                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                            },
                        });
                        return;
                    }

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

                // Status Settings side panel — editable copies of the current
                // board's statuses (teamTaskStatuses), each row saved on its
                // own. `_orig` keeps the last-saved values for dirty checks.
                openStatusPanel() {
                    if (!this.teamId && !this.projectId) return;
                    this.syncStatusRows();
                    this.newStatusRow = { status_id: '', status_name: '', color: '#6366F1', sort_order: '' };
                    this.statusPanelOpen = true;
                },

                closeStatusPanel() {
                    this.statusPanelOpen = false;
                },

                syncStatusRows() {
                    this.statusRows = this.teamTaskStatuses.map(s => {
                        const row = { status_id: s.status_id, status_name: s.status_name, color: s.color || '#6366F1', sort_order: s.sort_order ?? 0 };
                        return { ...row, _orig: { ...row } };
                    });
                },

                statusRowDirty(row) {
                    return row.status_name !== row._orig.status_name
                        || row.color !== row._orig.color
                        || Number(row.sort_order) !== Number(row._orig.sort_order);
                },

                statusPanelError(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    const text = errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message || 'Something went wrong.');
                    Swal.fire({ icon: 'error', title: 'Error', text });
                },

                saveStatusRow(row) {
                    if (!row.status_name?.trim()) {
                        Swal.fire({ icon: 'warning', title: 'Status name is required' });
                        return;
                    }
                    if (!this.statusRowDirty(row)) return;

                    $.ajax({
                        url: `${this.taskApiBase}/statuses/${row.status_id}`,
                        method: 'PUT',
                        data: {
                            status_name: row.status_name.trim(),
                            color: row.color,
                            sort_order: row.sort_order === '' ? 0 : row.sort_order,
                            _token: '{{ csrf_token() }}',
                        },
                        success: () => this.refreshTaskBoard(() => this.syncStatusRows()),
                        error: (xhr) => this.statusPanelError(xhr),
                    });
                },

                addStatusRow() {
                    const r = this.newStatusRow;
                    if (!r.status_name.trim()) {
                        Swal.fire({ icon: 'warning', title: 'Status name is required' });
                        return;
                    }

                    $.post(`${this.taskApiBase}/statuses`, {
                        status_id: r.status_id.trim(),
                        status_name: r.status_name.trim(),
                        color: r.color,
                        sort_order: r.sort_order === '' ? '' : r.sort_order,
                        _token: '{{ csrf_token() }}',
                    }, () => {
                        this.newStatusRow = { status_id: '', status_name: '', color: '#6366F1', sort_order: '' };
                        this.refreshTaskBoard(() => this.syncStatusRows());
                    }).fail((xhr) => this.statusPanelError(xhr));
                },

                deleteStatusRow(row) {
                    Swal.fire({
                        icon: 'warning',
                        title: `Delete "${row._orig.status_name}"?`,
                        text: 'Only possible when no task is in this status.',
                        showCancelButton: true,
                        confirmButtonText: 'Delete',
                        confirmButtonColor: '#ef4444',
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        $.ajax({
                            url: `${this.taskApiBase}/statuses/${row.status_id}`,
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: () => this.refreshTaskBoard(() => this.syncStatusRows()),
                            error: (xhr) => this.statusPanelError(xhr),
                        });
                    });
                },

                // Trash icon on a Team/Project Task-board column header —
                // blocked server-side (422) while a card still sits in it.
                deleteStatusColumn(status) {
                    if (!confirm(`Delete the "${status.status_name}" status?`)) return;

                    $.ajax({
                        url: `${this.taskApiBase}/statuses/${status.status_id}`,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: () => this.refreshTaskBoard(),
                        error: (xhr) => {
                            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                        },
                    });
                },

                // "+" inside a Kanban column. On the Projects board (no Team
                // selected) this opens the full "New Project" modal so a
                // Team can be picked (a Project needs at least one). On a
                // Team's own Task board it quick-creates a top-level Task in
                // that column, PIC picked from the Team's own members.
                // parent: {task_id, task_name} to create a Subtask under it
                // instead (the spreadsheet row's "+").
                openQuickAddCard(statusId, parent = null) {
                    if (!this.teamId && !this.projectId) {
                        this.openNewProject(statusId);
                        return;
                    }

                    this.quickAddStatusId = statusId;
                    this.quickAddParentId = parent?.task_id || null;
                    this.quickAddParentName = parent?.task_name || '';
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
                    const { assignees, team_ids } = splitTaskPicValues($('#qc_pic').val());
                    const tags = $('#qc_tags').val() || [];
                    if (!name || (!this.teamId && !this.projectId)) return;

                    if (window.qcDescrQuill) {
                        $('#qc_description').val(window.qcDescrQuill.root.innerHTML);
                    }
                    const filesToUpload = stagedQcFiles.slice();
                    const parentId = this.quickAddParentId;

                    $.post(this.taskApiBase, {
                        task_name: name,
                        task_description: $('#qc_description').val(),
                        start_date: $('#qc_start_date').val(),
                        end_date: $('#qc_end_date').val(),
                        status_id: this.quickAddStatusId,
                        parent_task_id: parentId || '',
                        assignees: assignees,
                        team_ids: team_ids,
                        tags: tags,
                        _token: '{{ csrf_token() }}',
                    }, (res) => {
                        this.closeQuickAddCard();
                        // Make sure the new Subtask is visible under its parent.
                        if (parentId) spreadsheetCollapsedIds.delete(parentId);
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
                                    <button class="add-card-btn flex w-full items-center justify-center gap-1.5 rounded-lg border-2 border-dashed border-gray-200 px-2 py-2 text-xs font-medium text-gray-400 transition hover:border-indigo-300 hover:bg-white/60 hover:text-indigo-500 dark:border-gray-700 dark:hover:border-indigo-500/60 dark:hover:bg-gray-800 dark:hover:text-indigo-400">
                                        <i class="fas fa-plus text-[10px]"></i> Add card
                                    </button>
                                </div>
                            </div>
                        `);

                        const list = col.find('.kanban-col');
                        items.forEach(p => list.append(this.projectCard(p)));

                        // Creating/moving Projects and adding stages is
                        // Project-admin (PROADMINACCESS) only.
                        if (this.canCreateProject) {
                            col.find('.add-card-btn').on('click', () => this.openQuickAddCard(status.status_id));
                        } else {
                            col.find('.add-card-btn').parent().remove();
                        }

                        wrap.append(col);
                    });

                    if (this.canCreateProject) {
                        const addStatusBtn = $(`
                            <div class="w-72 shrink-0">
                                <button class="w-full rounded-lg border-2 border-dashed border-gray-200 px-3 py-2.5 text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 dark:border-gray-700">
                                    + Add status
                                </button>
                            </div>
                        `);
                        addStatusBtn.find('button').on('click', () => this.openAddStatusModal());
                        wrap.append(addStatusBtn);
                    }

                    panel.append(wrap);

                    if (!this.canCreateProject) return;

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
                                <div class="group flex items-center gap-2 px-3 py-2.5">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background:${status.color}"></span>
                                    <span class="truncate text-sm font-semibold text-gray-700 dark:text-gray-200">${this.escapeHtml(status.status_name)}</span>
                                    <button type="button" class="edit-status-btn shrink-0 rounded p-1 text-gray-400 transition hover:bg-gray-200 hover:text-indigo-600 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-indigo-300" title="Edit status name & color">
                                        <i class="fas fa-pencil text-[10px]"></i>
                                    </button>
                                    <span class="text-xs text-gray-400">${items.length}</span>
                                    <div class="ml-auto hidden shrink-0 items-center gap-0.5 group-hover:flex">
                                        <button type="button" class="delete-status-btn rounded p-1 text-gray-300 transition hover:bg-red-100 hover:text-red-500 dark:hover:bg-red-900/20" title="Delete status">
                                            <i class="fas fa-trash text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="kanban-col space-y-2 px-2 pb-2 min-h-[40px]" data-status-id="${status.status_id}"></div>
                                <div class="px-2 pb-2">
                                    <button class="add-card-btn flex w-full items-center justify-center gap-1.5 rounded-lg border-2 border-dashed border-gray-200 px-2 py-2 text-xs font-medium text-gray-400 transition hover:border-indigo-300 hover:bg-white/60 hover:text-indigo-500 dark:border-gray-700 dark:hover:border-indigo-500/60 dark:hover:bg-gray-800 dark:hover:text-indigo-400">
                                        <i class="fas fa-plus text-[10px]"></i> Add card
                                    </button>
                                </div>
                            </div>
                        `);

                        const list = col.find('.kanban-col');
                        items.forEach(t => list.append(this.teamTaskCard(t)));

                        col.find('.add-card-btn').on('click', () => this.openQuickAddCard(status.status_id));
                        col.find('.edit-status-btn').on('click', () => this.openEditStatusModal(status));
                        col.find('.delete-status-btn').on('click', () => this.deleteStatusColumn(status));

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
                                $.post(`${this.taskApiBase}/${taskId}/status`, { status_id: statusId, _token: '{{ csrf_token() }}' })
                                    .fail(xhr => { toastr.error(xhr.responseJSON?.message || 'Could not move this task.'); this.refreshTaskBoard(); });
                            }
                        });
                    });
                },

                teamTaskCard(t) {
                    const esc = (s) => this.escapeHtml(s);

                    const people = t.assignee_people || [];
                    const avatars = people.slice(0, 3).map((p, i) => p.photo_url
                        ? `<img src="${p.photo_url}" alt="${esc(p.name)}" title="${esc(p.name)}"
                            class="h-6 w-6 rounded-full object-cover ring-2 ring-white dark:ring-gray-800" style="margin-left:${i === 0 ? '0' : '-6px'}">`
                        : `<span title="${esc(p.name)}" style="margin-left:${i === 0 ? '0' : '-6px'}" class="inline-block rounded-full ring-2 ring-white dark:ring-gray-800">${initialsAvatar(p.name, 24)}</span>`
                    ).join('');

                    const description = t.task_description ? stripHtml(t.task_description) : '';

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
                    const displayPct = children.length ? Math.round((childDone / children.length) * 100) : Math.round(t.progress_percent);
                    const late = isLate(t.end_date, displayPct, cancelled);
                    const done = !cancelled && displayPct >= 100;

                    // Compact range ("01 Sep → 30 Sep"); the year only shows
                    // when it isn't this year. Full dates live in the tooltip.
                    const shortDate = (d) => {
                        const x = dayjs(d);
                        return x.isValid() ? x.format(x.year() === dayjs().year() ? 'DD MMM' : 'DD MMM YY') : '—';
                    };
                    const dateRange = t.start_date && t.end_date ? `${shortDate(t.start_date)} → ${shortDate(t.end_date)}`
                        : t.end_date ? `Due ${shortDate(t.end_date)}`
                        : t.start_date ? `From ${shortDate(t.start_date)}` : '';
                    const dateTitle = `${formatDate(t.start_date)} → ${formatDate(t.end_date)}`;

                    // Countdown chip next to the dates — the "how urgent is
                    // this" at a glance, colored by how close/over the due date is.
                    let due = null;
                    if (cancelled) {
                        due = { text: 'Cancelled', icon: 'fa-ban', cls: 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400' };
                    } else if (done) {
                        due = { text: 'Done', icon: 'fa-circle-check', cls: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' };
                    } else if (t.end_date && dayjs(t.end_date).isValid()) {
                        const days = dayjs(t.end_date).startOf('day').diff(dayjs().startOf('day'), 'day');
                        due = days < 0 ? { text: `${-days}d overdue`, icon: 'fa-fire', cls: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' }
                            : days === 0 ? { text: 'Due today', icon: 'fa-hourglass-half', cls: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' }
                            : days <= 3 ? { text: `${days}d left`, icon: 'fa-hourglass-half', cls: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' }
                            : { text: `${days}d left`, icon: 'fa-clock', cls: 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400' };
                    }

                    // Left accent stripe: the card's state in one glance.
                    const accent = cancelled ? '#9CA3AF' : done ? '#10B981' : late ? '#EF4444' : '#6366F1';

                    const tagChips = [
                        ...(t.teams || []).map(tm => `<span class="inline-flex max-w-[8rem] items-center gap-1 truncate rounded-md bg-indigo-50 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300" title="Team: ${esc(tm.team_name)}"><i class="fas fa-users text-[8px]"></i> ${esc(tm.team_name)}</span>`),
                        ...(t.tags || []).map(tag => {
                            const c = tag.color || '#6366F1';
                            return `<span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-semibold" style="background:${hexToRgba(c, 0.12)};color:${c}">${tag.tag_id === 'COMPLETE' ? '<i class="fas fa-check text-[8px]"></i>' : `<span class="h-1.5 w-1.5 rounded-full" style="background:${c}"></span>`}${esc(tag.tag_name)}</span>`;
                        }),
                    ].join('');

                    const meta = (icon, count, title) => `<span class="inline-flex items-center gap-1 ${count ? 'text-gray-500 dark:text-gray-300' : 'text-gray-300 dark:text-gray-600'}" title="${title}"><i class="${icon} text-[10px]"></i>${count}</span>`;

                    const $card = $(`
                        <div data-task-id="${t.task_id}"
                            class="group relative block cursor-move overflow-hidden rounded-xl border ${late ? 'border-red-200 dark:border-red-500/30' : 'border-gray-200/80 dark:border-gray-700'} bg-white p-3.5 pl-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/5 dark:bg-gray-800 dark:hover:border-indigo-500/30 ${cancelled ? 'opacity-60' : ''}">

                            <span class="absolute inset-y-0 left-0 w-1" style="background:${accent}"></span>

                            ${t.cover_url ? `<div class="-mt-3.5 -mr-3.5 -ml-4 mb-3 h-28 overflow-hidden bg-gray-100 dark:bg-white/5"><img src="${t.cover_url}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105"></div>` : ''}

                            <div class="flex h-6 items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-2.5 text-[11px] font-medium">
                                    ${t.file_count != null ? meta('fas fa-paperclip', t.file_count, `${t.file_count} file(s)`) : ''}
                                    ${t.comment_count != null ? meta('far fa-comment', t.comment_count, `${t.comment_count} comment(s)`) : ''}
                                    ${children.length ? meta('fas fa-list-check', `${childDone}/${children.length}`, `${childDone} of ${children.length} subtask(s) done`) : ''}
                                    ${t.is_locked ? `<i class="fas fa-lock text-[10px] text-amber-500" title="${t.can_access === false ? 'Private — assignees only' : 'Private'}"></i>` : ''}
                                </div>
                                <div class="flex items-center gap-0.5 opacity-0 transition group-hover:opacity-100">
                                    ${this.teamId ? `
                                    <button type="button" class="team-card-cancel-btn rounded-md p-1 text-gray-400 transition hover:bg-amber-50 hover:text-amber-500 dark:hover:bg-amber-900/20" data-task-id="${t.task_id}" title="${cancelled ? 'Restore' : 'Cancel'}">
                                        <i class="fas ${cancelled ? 'fa-rotate-left' : 'fa-ban'} text-xs"></i>
                                    </button>
                                    ` : ''}
                                    <button type="button" class="team-card-archive-btn rounded-md p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20" data-task-id="${t.task_id}" title="Archive">
                                        <i class="fas fa-box-archive text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            ${tagChips ? `<div class="mt-2 flex flex-wrap gap-1">${tagChips}</div>` : ''}

                            <p class="mt-2 line-clamp-2 text-sm font-semibold leading-snug ${cancelled ? 'text-gray-400 line-through dark:text-gray-500' : 'text-gray-800 dark:text-gray-100'}" title="${esc(t.task_name)}">${esc(t.task_name)}</p>

                            ${description ? `<p class="mt-1 line-clamp-2 text-xs leading-relaxed text-gray-500 dark:text-gray-400" title="${esc(description)}">${esc(description)}</p>` : ''}

                            <div class="mt-3">
                                <div class="mb-1 flex items-center justify-between text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                                    <span>Progress</span>
                                    <span class="${done ? 'text-emerald-600 dark:text-emerald-400' : late ? 'text-red-500 dark:text-red-400' : 'text-indigo-600 dark:text-indigo-300'}">${displayPct}%</span>
                                </div>
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                    <div class="h-full rounded-full transition-all duration-500 ${done ? 'bg-linear-to-r from-emerald-400 to-emerald-500' : late ? 'bg-linear-to-r from-orange-400 to-red-500' : 'bg-linear-to-r from-indigo-500 to-violet-500'}" style="width:${displayPct}%"></div>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-2 border-t border-dashed border-gray-100 pt-2.5 dark:border-gray-700">
                                <div class="flex min-w-0 items-center gap-1.5 text-[11px]">
                                    ${dateRange
                                        ? `<span class="inline-flex min-w-0 items-center gap-1 truncate text-gray-500 dark:text-gray-400" title="${dateTitle}"><i class="far fa-calendar text-[10px]"></i> ${dateRange}</span>`
                                        : '<span class="inline-flex items-center gap-1 italic text-gray-300 dark:text-gray-600"><i class="far fa-calendar text-[10px]"></i> No dates</span>'}
                                    ${due ? `<span class="inline-flex shrink-0 items-center gap-1 rounded-full px-1.5 py-0.5 text-[10px] font-semibold ${due.cls}"><i class="fas ${due.icon} text-[8px]"></i> ${due.text}</span>` : ''}
                                </div>
                                ${people.length ? `
                                    <div class="flex shrink-0 items-center" title="${esc(people.map(p => p.name).join(', '))}">
                                        ${avatars}
                                        ${people.length > 3 ? `<span class="-ml-1.5 inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-gray-100 px-1 text-[10px] font-bold text-gray-500 ring-2 ring-white dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-800">+${people.length - 3}</span>` : ''}
                                    </div>
                                ` : `<span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-dashed border-gray-300 text-gray-300 dark:border-gray-600 dark:text-gray-600" title="No PIC assigned"><i class="fas fa-user-plus text-[9px]"></i></span>`}
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

                // "By Calendar" — normalized items for whichever scope is
                // showing: the Projects portfolio, or a Team/Project's own
                // Tasks (top-level) / Subtasks (any depth, per calLevel).
                // An item with only one of start/end date sits on that
                // single day; one with neither isn't placed but still counts
                // toward `total` (the toolbar's "N without dates").
                calendarItems() {
                    const scoped = this.teamId || this.projectId;
                    const statuses = scoped ? this.teamTaskStatuses : this.statuses;
                    const findStatus = (id) => statuses.find(s => s.status_id === id);

                    // {x, ancestors: task_id[], path: name[], root} rows.
                    const rows = [];
                    if (!scoped) {
                        this.projects.forEach(x => rows.push({ x, ancestors: [], path: [], root: x }));
                    } else if (this.calLevel === 'subtask') {
                        const walk = (nodes, ancestors, path, root) => (nodes || []).forEach(n => {
                            rows.push({ x: n, ancestors, path, root });
                            walk(n.children, [...ancestors, n.task_id], [...path, n.task_name], root);
                        });
                        this.teamTasks.forEach(t => walk(t.children, [t.task_id], [t.task_name], t));
                    } else {
                        this.teamTasks.forEach(x => rows.push({ x, ancestors: [], path: [], root: x }));
                    }

                    const items = rows.map(({ x, ancestors, path, root }) => {
                        let start = x.start_date || x.end_date;
                        let end = x.end_date || x.start_date;
                        if (!start || !dayjs(start).isValid() || !dayjs(end).isValid()) return null;
                        start = dayjs(start).startOf('day');
                        end = dayjs(end).startOf('day');
                        if (end.isBefore(start)) [start, end] = [end, start];

                        // A Subtask without its own board status takes its
                        // top-level Task's color, so a Task's Subtasks read
                        // as one family on the calendar.
                        const status = findStatus(x.status_id) || findStatus(root.status_id);
                        const cancelled = scoped && x.status === 'C';
                        // Same "children win over own progress_percent" rule
                        // as the Kanban card/detail views.
                        const children = scoped ? (x.children || []).filter(c => c.status !== 'C') : [];
                        const subDone = children.filter(c => c.progress_percent >= 100).length;
                        const progress = children.length
                            ? Math.round(subDone / children.length * 100)
                            : Math.round(x.progress_percent || 0);
                        const desc = scoped ? x.task_description : x.project_description;

                        return {
                            id: scoped ? x.task_id : x.project_id,
                            name: scoped ? x.task_name : x.project_name,
                            start, end,
                            color: status?.color || '#9CA3AF',
                            statusName: status?.status_name || '',
                            progress,
                            cancelled,
                            done: !cancelled && progress >= 100,
                            late: isLate(x.end_date, progress, cancelled),
                            people: scoped ? (x.assignee_people || []) : (x.pics || []),
                            description: desc ? stripHtml(desc) : '',
                            subTotal: children.length,
                            subDone,
                            ancestors,
                            parentPath: path, // top-level Task → … → direct parent
                            tags: x.tags || [],
                            isLocked: !!x.is_locked,
                            // Locked TSK the viewer isn't assigned to — the
                            // server already masked its description and
                            // dropped its Subtasks (see PmTaskController).
                            lockedOut: x.can_access === false,
                        };
                    }).filter(Boolean);

                    return { items, total: rows.length };
                },

                openCalendarItem(ev) {
                    if (!(this.teamId || this.projectId)) return openProjectDetail(ev.id, 'push');
                    const t = findTaskInTree(ev.id, currentTasksCache);
                    if (!t) return;
                    currentTaskApiBase = this.taskApiBase;
                    currentTaskDoctype = this.taskDoctype;
                    currentTaskRefreshFn = (cb) => this.refreshTaskBoard(cb);
                    // Ancestors first, so the detail modal's Back button
                    // walks up to the parent Task (same as the spreadsheet).
                    taskDetailStack = [...ev.ancestors];
                    openTaskEntityDetail(t);
                },

                // Splits items into one week's bar segments and stacks them
                // into lanes (greedy, earliest/longest first) so overlapping
                // items never draw on top of each other.
                layoutCalendarWeek(items, weekStart) {
                    const weekEnd = weekStart.add(6, 'day');
                    const segs = items
                        .filter(ev => !ev.end.isBefore(weekStart) && !ev.start.isAfter(weekEnd))
                        .map(ev => {
                            const s = ev.start.isBefore(weekStart) ? weekStart : ev.start;
                            const e = ev.end.isAfter(weekEnd) ? weekEnd : ev.end;
                            return {
                                ev,
                                col: s.diff(weekStart, 'day'),
                                span: e.diff(s, 'day') + 1,
                                clipL: ev.start.isBefore(weekStart),
                                clipR: ev.end.isAfter(weekEnd),
                            };
                        })
                        .sort((a, b) => a.col - b.col || b.span - a.span || a.ev.name.localeCompare(b.ev.name));

                    const laneEnds = [];
                    segs.forEach(seg => {
                        let lane = laneEnds.findIndex(endCol => endCol < seg.col);
                        if (lane === -1) { lane = laneEnds.length; laneEnds.push(0); }
                        laneEnds[lane] = seg.col + seg.span - 1;
                        seg.lane = lane;
                    });
                    return { segs, laneCount: laneEnds.length };
                },

                calendarBarHtml(seg, compact) {
                    const ev = seg.ev;
                    const esc = (s) => this.escapeHtml(s);
                    const radius = `${seg.clipL ? '0' : '6px'} ${seg.clipR ? '0' : '6px'} ${seg.clipR ? '0' : '6px'} ${seg.clipL ? '0' : '6px'}`;
                    const icon = ev.cancelled ? '<i class="fas fa-ban text-[9px] text-gray-400"></i>'
                        : ev.done ? '<i class="fas fa-circle-check text-[10px] text-emerald-500"></i>'
                        : ev.late ? '<i class="fas fa-triangle-exclamation text-[9px] text-red-500"></i>' : '';
                    const lock = ev.isLocked ? `<i class="fas fa-lock text-[9px] text-amber-500" title="${ev.lockedOut ? 'Private — assignees only' : 'Private'}"></i>` : '';
                    const shortDate = (d) => d.format(d.year() === dayjs().year() ? 'DD MMM' : 'DD MMM YY');
                    const range = ev.start.isSame(ev.end, 'day') ? shortDate(ev.start) : `${shortDate(ev.start)} → ${shortDate(ev.end)}`;

                    const parent = ev.parentPath.length ? ev.parentPath[ev.parentPath.length - 1] : '';

                    // Second line of a week-view bar: a Subtask names its
                    // parent Task; a Task/Project shows its own Subtask
                    // count (if any) and a one-line description excerpt.
                    const infoLine = ev.lockedOut
                        ? `<span class="truncate italic text-amber-600 dark:text-amber-400"><i class="fas fa-lock mr-1 text-[9px]"></i>Private — assignees only</span>`
                        : parent
                        ?`<span class="inline-flex min-w-0 items-center gap-1 truncate rounded bg-white/70 px-1.5 py-px text-[10px] font-semibold text-indigo-600 dark:bg-white/10 dark:text-indigo-300" title="Task: ${esc(ev.parentPath.join(' › '))}">
                               <i class="fas fa-turn-up fa-rotate-90 text-[8px]"></i><span class="truncate">${esc(ev.parentPath.join(' › '))}</span>
                           </span>`
                        : `${ev.subTotal ? `<span class="shrink-0 inline-flex items-center gap-1 rounded bg-white/70 px-1.5 py-px text-[10px] font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300"><i class="fas fa-list-check text-[8px]"></i>${ev.subDone}/${ev.subTotal}</span>` : ''}
                           <span class="truncate">${esc(ev.description) || '<span class="italic opacity-60">No description</span>'}</span>`;

                    const body = compact
                        ? `<div class="flex min-w-0 items-center gap-1">
                               ${seg.clipL ? '<i class="fas fa-caret-left text-[9px] opacity-60"></i>' : ''}
                               ${lock}${icon}
                               <span class="truncate">${esc(ev.name)}${parent ? `<span class="font-normal text-gray-500 dark:text-gray-400"> · ${esc(parent)}</span>` : ''}</span>
                           </div>`
                        : `<div class="flex min-w-0 items-center gap-1.5">
                               ${seg.clipL ? '<i class="fas fa-caret-left text-[10px] opacity-60"></i>' : ''}
                               ${lock}${icon}
                               <span class="truncate font-semibold">${esc(ev.name)}</span>
                           </div>
                           <div class="mt-1 flex min-w-0 items-center gap-1.5 text-[11px] font-normal text-gray-500 dark:text-gray-400">${infoLine}</div>
                           <div class="mt-1 flex min-w-0 items-center gap-2 text-[11px] font-normal text-gray-500 dark:text-gray-400">
                               <span class="truncate"><i class="fas fa-calendar-day mr-1 text-[9px]"></i>${range}</span>
                               <span class="shrink-0 font-semibold ${ev.done ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-300'}">${ev.progress}%</span>
                               <span class="ml-auto shrink-0">${subtaskPicHtml(ev.people)}</span>
                           </div>
                           <div class="absolute inset-x-0 bottom-0 h-[3px] bg-black/5 dark:bg-white/5">
                               <div class="h-full" style="width:${ev.progress}%;background:${ev.done ? '#10B981' : ev.color}"></div>
                           </div>`;

                    return `
                        <div class="cal-bar absolute cursor-pointer overflow-hidden px-2 text-xs font-medium text-gray-800 transition hover:brightness-95 dark:text-gray-100 dark:hover:brightness-125 ${compact ? 'flex items-center' : 'py-1.5'} ${ev.late ? 'ring-1 ring-inset ring-red-400/70' : ''} ${ev.cancelled ? 'opacity-60 line-through' : ''}"
                            data-id="${esc(ev.id)}"
                            style="background:${hexToRgba(ev.color, 0.16)};border-left:${seg.clipL ? '0' : `3px solid ${ev.color}`};border-radius:${radius}">
                            ${body}
                        </div>`;
                },

                calendarTooltipHtml(ev) {
                    const esc = (s) => this.escapeHtml(s);
                    const desc = ev.description.length > 160 ? ev.description.slice(0, 160) + '…' : ev.description;
                    return `
                        ${ev.parentPath.length ? `<p class="mb-1 flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-indigo-300"><i class="fas fa-turn-up fa-rotate-90 text-[8px]"></i> ${esc(ev.parentPath.join(' › '))}</p>` : ''}
                        <p class="text-xs font-semibold text-white">${ev.isLocked ? '<i class="fas fa-lock mr-1 text-[10px] text-amber-400"></i>' : ''}${esc(ev.name)}</p>
                        ${ev.lockedOut ? '<p class="mt-1 text-[11px] italic text-amber-300">Private — only its assignees can open it.</p>' : ''}
                        ${desc ?`<p class="mt-1 text-[11px] leading-snug text-gray-300">${esc(desc)}</p>` : ''}
                        ${ev.subTotal ? `<p class="mt-1 text-[11px] text-gray-400"><span class="font-medium text-gray-300">Subtasks:</span> ${ev.subDone}/${ev.subTotal} done</p>` : ''}
                        <p class="mt-1 flex items-center gap-1.5 text-[11px] text-gray-300">
                            <span class="h-2 w-2 rounded-full" style="background:${ev.color}"></span>${esc(ev.statusName || '—')}
                            <span class="text-gray-500">·</span> ${ev.progress}%
                            ${ev.late ? '<span class="font-semibold text-red-400">· Late</span>' : ''}
                        </p>
                        <p class="mt-1 text-[11px] text-gray-400">${formatDate(ev.start)} → ${formatDate(ev.end)}</p>
                        ${ev.people.length ? `<p class="mt-1 text-[11px] text-gray-400"><span class="font-medium text-gray-300">${this.teamId || this.projectId ? 'Assignee' : 'PIC'}:</span> ${esc(ev.people.map(p => p.name).join(', '))}</p>` : ''}
                    `;
                },

                calendarGo(step) {
                    const c = dayjs(this.calCursor);
                    this.calCursor = (step === 0 ? dayjs() : c.add(step, this.calView === 'week' ? 'week' : 'month')).format('YYYY-MM-DD');
                    this.renderCalendar();
                },

                renderCalendar() {
                    const panel = $('#calendarPanel').empty();
                    hideCardTooltip();

                    // Re-fit the rows to the panel when the window resizes
                    // (bound once; only acts while this tab is showing).
                    if (!this.calResizeBound) {
                        this.calResizeBound = true;
                        let timer = null;
                        window.addEventListener('resize', () => {
                            clearTimeout(timer);
                            timer = setTimeout(() => {
                                if (this.tab === 'calendar' && $('#calendarPanel').is(':visible')) this.renderCalendar();
                            }, 150);
                        });
                    }

                    const scoped = this.teamId || this.projectId;
                    const isSub = scoped && this.calLevel === 'subtask';
                    const { items, total } = this.calendarItems();
                    const byId = new Map(items.map(ev => [String(ev.id), ev]));
                    const undated = total - items.length;
                    const noun = !scoped ? 'project' : isSub ? 'subtask' : 'task';

                    const cursor = dayjs(this.calCursor);
                    const startOfWeek = (d) => d.startOf('day').subtract((d.day() + 6) % 7, 'day'); // Monday-first
                    const today = dayjs().startOf('day');
                    const isWeek = this.calView === 'week';

                    const wkStart = startOfWeek(cursor);
                    const wkEnd = wkStart.add(6, 'day');
                    const title = isWeek
                        ? (wkStart.month() === wkEnd.month()
                            ? `${wkStart.format('DD')} – ${wkEnd.format('DD MMM YYYY')}`
                            : `${wkStart.format('DD MMM')} – ${wkEnd.format('DD MMM YYYY')}`)
                        : cursor.format('MMMM YYYY');

                    const segCls = (on) => `rounded-md px-3 py-1 text-xs font-medium transition ${on ? 'bg-white text-indigo-700 shadow-sm dark:bg-gray-700 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'}`;
                    const segBtn = (view, label) => `<button type="button" data-view="${view}" class="cal-view-btn ${segCls(this.calView === view)}">${label}</button>`;
                    const levelBtn = (level, icon, label) => `<button type="button" data-level="${level}" class="cal-level-btn inline-flex items-center gap-1.5 ${segCls(this.calLevel === level)}"><i class="fas ${icon} text-[10px]"></i>${label}</button>`;

                    const toolbar = $(`
                        <div class="mb-3 flex shrink-0 flex-wrap items-center gap-2">
                            <div class="flex items-center gap-1">
                                <button type="button" class="cal-nav flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 hover:text-indigo-600 dark:border-white/10 dark:text-gray-400 dark:hover:bg-gray-800" data-step="-1" title="Previous"><i class="fas fa-chevron-left text-[10px]"></i></button>
                                <button type="button" class="cal-nav h-8 rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition hover:bg-gray-50 hover:text-indigo-600 dark:border-white/10 dark:text-gray-300 dark:hover:bg-gray-800" data-step="0">Today</button>
                                <button type="button" class="cal-nav flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 hover:text-indigo-600 dark:border-white/10 dark:text-gray-400 dark:hover:bg-gray-800" data-step="1" title="Next"><i class="fas fa-chevron-right text-[10px]"></i></button>
                            </div>
                            <h3 class="ml-1 text-base font-semibold text-gray-800 dark:text-gray-100">${title}</h3>
                            ${undated > 0 ? `<span class="text-xs text-gray-400" title="Set a start or end date to place these on the calendar">${undated} ${noun}${undated > 1 ? 's' : ''} without dates</span>` : ''}
                            <div class="ml-auto flex flex-wrap items-center gap-2">
                                ${scoped ? `<div class="flex items-center rounded-lg bg-gray-100 p-0.5 dark:bg-gray-800">
                                    ${levelBtn('task', 'fa-square-check', 'Task')}${levelBtn('subtask', 'fa-list-check', 'Subtask')}
                                </div>` : ''}
                                <div class="flex items-center rounded-lg bg-gray-100 p-0.5 dark:bg-gray-800">
                                    ${segBtn('week', 'Week')}${segBtn('month', 'Month')}
                                </div>
                            </div>
                        </div>
                    `);
                    toolbar.find('.cal-nav').on('click', (e) => this.calendarGo(Number(e.currentTarget.dataset.step)));
                    toolbar.find('.cal-view-btn').on('click', (e) => { this.calView = e.currentTarget.dataset.view; this.renderCalendar(); });
                    toolbar.find('.cal-level-btn').on('click', (e) => { this.calLevel = e.currentTarget.dataset.level; this.renderCalendar(); });
                    panel.append(toolbar);

                    const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    const grid = $('<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/[0.06] dark:bg-gray-900"></div>');
                    panel.append(grid);
                    // Height left for the grid's rows: #calendarPanel is
                    // h-full of the tab area, minus the toolbar and the
                    // grid's own 1px top/bottom borders.
                    const gridAvail = () => panel.height() - toolbar.outerHeight(true) - 2;
                    // Tinted weekend columns, drawn behind the bars.
                    const colBg = (i) => i >= 5 ? 'bg-gray-50/70 dark:bg-white/[0.015]' : '';

                    if (isWeek) {
                        const days = [...Array(7)].map((_, i) => wkStart.add(i, 'day'));
                        grid.append(`
                            <div class="grid grid-cols-7 border-b border-gray-200 dark:border-white/[0.06]">
                                ${days.map((d, i) => {
                                    const isToday = d.isSame(today, 'day');
                                    return `<div class="border-l border-gray-100 px-2 py-2 text-center first:border-l-0 dark:border-white/[0.04] ${colBg(i)}">
                                        <p class="text-[11px] font-medium uppercase tracking-wide ${isToday ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-400'}">${dayNames[i]}</p>
                                        <p class="mt-0.5 inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-full px-1.5 text-sm font-semibold ${isToday ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-200'}">${d.format('DD')}</p>
                                    </div>`;
                                }).join('')}
                            </div>
                        `);

                        const { segs, laneCount } = this.layoutCalendarWeek(items, wkStart);
                        const laneH = 80, pad = 8;
                        const fillH = gridAvail() - grid.children().first().outerHeight();
                        const body = $(`<div class="relative" style="height:${Math.max(laneCount * laneH + pad * 2, fillH, 280)}px"></div>`);
                        body.append(`<div class="absolute inset-0 grid grid-cols-7">${days.map((d, i) =>
                            `<div class="border-l border-gray-100 first:border-l-0 dark:border-white/[0.04] ${colBg(i)} ${d.isSame(today, 'day') ? '!bg-indigo-50/50 dark:!bg-indigo-500/[0.05]' : ''}"></div>`).join('')}</div>`);
                        segs.forEach(seg => {
                            const $bar = $(this.calendarBarHtml(seg, false)).css({
                                left: `calc(${seg.col / 7 * 100}% + ${seg.clipL ? 0 : 4}px)`,
                                width: `calc(${seg.span / 7 * 100}% - ${(seg.clipL ? 0 : 4) + (seg.clipR ? 0 : 4)}px)`,
                                top: `${pad + seg.lane * laneH}px`,
                                height: `${laneH - 6}px`,
                            });
                            body.append($bar);
                        });
                        if (!segs.length) {
                            body.append(`<p class="absolute inset-x-0 top-10 text-center text-sm text-gray-400">No ${noun}s scheduled this week.</p>`);
                        }
                        grid.append(body);
                    } else {
                        const monthStart = cursor.startOf('month');
                        const gridStart = startOfWeek(monthStart);
                        const gridEnd = startOfWeek(cursor.endOf('month')).add(6, 'day');
                        const weeks = gridEnd.diff(gridStart, 'week') + 1;
                        const laneH = 22, headH = 28, moreH = 22;

                        const $dow = $(`<div class="grid grid-cols-7 border-b border-gray-200 dark:border-white/[0.06]">${dayNames.map((n, i) =>
                            `<div class="px-2 py-2 text-center text-[11px] font-medium uppercase tracking-wide text-gray-400 ${colBg(i)}">${n}</div>`).join('')}</div>`);
                        grid.append($dow);

                        // Week rows split the remaining height evenly (never
                        // shorter than 3 bars), and as many bars show per
                        // day as fit — the rest collapse into "+N more".
                        const rowH = Math.max(Math.floor((gridAvail() - $dow.outerHeight() - (weeks - 1)) / weeks), headH + 3 * laneH + moreH);
                        const maxLanes = Math.max(1, Math.floor((rowH - headH - moreH) / laneH));

                        for (let w = 0; w < weeks; w++) {
                            const weekStart = gridStart.add(w, 'week');
                            const days = [...Array(7)].map((_, i) => weekStart.add(i, 'day'));
                            const { segs } = this.layoutCalendarWeek(items, weekStart);

                            // Per-day count of items that didn't fit the lane cap.
                            const hidden = Array(7).fill(0);
                            segs.filter(s => s.lane >= maxLanes).forEach(s => { for (let c = s.col; c < s.col + s.span; c++) hidden[c]++; });

                            const row = $(`<div class="relative border-b border-gray-100 last:border-b-0 dark:border-white/[0.04]" style="height:${rowH}px"></div>`);
                            row.append(`<div class="absolute inset-0 grid grid-cols-7">${days.map((d, i) => {
                                const inMonth = d.month() === cursor.month();
                                const isToday = d.isSame(today, 'day');
                                return `<div class="cal-day relative cursor-pointer border-l border-gray-100 px-1.5 pt-1 transition first:border-l-0 hover:bg-indigo-50/40 dark:border-white/[0.04] dark:hover:bg-indigo-500/[0.05] ${colBg(i)} ${inMonth ? '' : 'opacity-50'}" data-date="${d.format('YYYY-MM-DD')}" title="Open week view">
                                    <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full px-1 text-xs font-semibold ${isToday ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300'}">${d.format('D')}</span>
                                    ${hidden[i] ? `<span class="absolute bottom-1 left-2 text-[11px] font-medium text-indigo-600 hover:underline dark:text-indigo-300">+${hidden[i]} more</span>` : ''}
                                </div>`;
                            }).join('')}</div>`);

                            segs.filter(s => s.lane < maxLanes).forEach(seg => {
                                const $bar = $(this.calendarBarHtml(seg, true)).css({
                                    left: `calc(${seg.col / 7 * 100}% + ${seg.clipL ? 0 : 3}px)`,
                                    width: `calc(${seg.span / 7 * 100}% - ${(seg.clipL ? 0 : 3) + (seg.clipR ? 0 : 3)}px)`,
                                    top: `${headH + seg.lane * laneH}px`,
                                    height: `${laneH - 3}px`,
                                });
                                row.append($bar);
                            });
                            grid.append(row);
                        }

                        // Clicking a day (or its "+N more") drills into that week.
                        grid.on('click', '.cal-day', (e) => {
                            this.calCursor = e.currentTarget.dataset.date;
                            this.calView = 'week';
                            this.renderCalendar();
                        });
                    }

                    grid.on('click', '.cal-bar', (e) => {
                        e.stopPropagation();
                        hideCardTooltip();
                        const ev = byId.get(e.currentTarget.dataset.id);
                        if (ev) this.openCalendarItem(ev);
                    });
                    grid.on('mouseenter', '.cal-bar', (e) => {
                        const ev = byId.get(e.currentTarget.dataset.id);
                        if (ev) showCardTooltip(e.currentTarget, this.calendarTooltipHtml(ev));
                    });
                    grid.on('mouseleave', '.cal-bar', hideCardTooltip);
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
                    // Keep collapsed rows collapsed across a refresh of the
                    // same board (add subtask, drag-and-drop); reset on switch.
                    const boardKey = `${this.taskDoctype}:${this.teamId || this.projectId}`;
                    if (spreadsheetBoardKey !== boardKey) {
                        spreadsheetCollapsedIds = new Set();
                        spreadsheetBoardKey = boardKey;
                    }
                    const wrap = $('<div class="space-y-4"></div>');

                    this.teamTaskStatuses.forEach(status => {
                        const items = this.teamTasks.filter(t => t.status_id === status.status_id);
                        const group = $(`
                            <div class="overflow-hidden rounded-lg border border-gray-100 dark:border-white/[0.06]">
                                <div class="flex items-center gap-2 px-3 py-2" style="background:${hexToRgba(status.color, 0.1)}">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background:${status.color}"></span>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">${this.escapeHtml(status.status_name)}</span>
                                    <button type="button" class="edit-status-btn shrink-0 rounded p-1 text-gray-400 transition hover:bg-gray-200 hover:text-indigo-600 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-indigo-300" title="Edit status name & color">
                                        <i class="fas fa-pencil text-[10px]"></i>
                                    </button>
                                    <span class="text-xs text-gray-400">${items.length}</span>
                                </div>
                                <div class="spreadsheet-top-drop mx-3 mt-2 hidden items-center justify-center gap-1.5 rounded-lg border-2 border-dashed border-gray-200 px-3 py-2 text-xs text-gray-400 transition dark:border-gray-700" data-status-id="${status.status_id}">
                                    <i class="fas fa-arrow-turn-up text-[10px]"></i> Drop here to make it a main task in ${this.escapeHtml(status.status_name)}
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
                        group.find('.edit-status-btn').on('click', () => this.openEditStatusModal(status));
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

                    wrap.on('click', '.spreadsheet-add-sub-btn', (e) => {
                        e.stopPropagation();
                        const t = findTaskInTree($(e.currentTarget).closest('tr').attr('data-task-id'), currentTasksCache);
                        if (!t) return;
                        this.openQuickAddCard(t.status_id, t);
                    });

                    this.bindSpreadsheetDragDrop(wrap);

                    wrap.on('click', '.spreadsheet-row', (e) => {
                        if ($(e.target).closest('.spreadsheet-toggle-btn, .spreadsheet-check, .spreadsheet-add-sub-btn').length) return;
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

                // Spreadsheet drag-and-drop: a row dropped onto another row
                // becomes its subtask (whole subtree comes along); dropped on
                // a group's "Drop here" strip it becomes a main Task in that
                // status. A row never highlights as a target under itself or
                // its own descendants. Saved via POST {taskApiBase}/{id}/parent.
                bindSpreadsheetDragDrop(wrap) {
                    let dragId = null;
                    const rowHi = 'bg-indigo-100 outline outline-2 -outline-offset-2 outline-indigo-400 dark:bg-indigo-900/40';
                    const zoneHi = 'border-indigo-400 bg-indigo-50 text-indigo-600 dark:border-indigo-500 dark:bg-indigo-900/30 dark:text-indigo-300';
                    const clearHi = () => {
                        wrap.find('tr[data-task-row]').removeClass(rowHi);
                        wrap.find('.spreadsheet-top-drop').removeClass(zoneHi);
                    };
                    const canNestUnder = ($tr) => {
                        const id = $tr.attr('data-task-id');
                        return !!dragId && id !== dragId && !($tr.attr('data-ancestors') || '').split(',').includes(dragId);
                    };
                    const $dragRow = () => wrap.find(`tr[data-task-row][data-task-id="${dragId}"]`);

                    wrap.on('dragstart', 'tr[data-task-row]', (e) => {
                        const tr = e.currentTarget;
                        dragId = $(tr).attr('data-task-id');
                        e.originalEvent.dataTransfer.effectAllowed = 'move';
                        e.originalEvent.dataTransfer.setData('text/plain', dragId);
                        // Next tick — changing layout inside dragstart cancels the drag in Chromium.
                        setTimeout(() => {
                            $(tr).addClass('opacity-40');
                            wrap.find('.spreadsheet-top-drop').removeClass('hidden').addClass('flex');
                        });
                    });

                    wrap.on('dragend', 'tr[data-task-row]', (e) => {
                        dragId = null;
                        $(e.currentTarget).removeClass('opacity-40');
                        wrap.find('.spreadsheet-top-drop').addClass('hidden').removeClass('flex');
                        clearHi();
                    });

                    wrap.on('dragover', 'tr[data-task-row]', (e) => {
                        const $tr = $(e.currentTarget);
                        if (!canNestUnder($tr)) return;
                        e.preventDefault();
                        e.originalEvent.dataTransfer.dropEffect = 'move';
                        if (!$tr.hasClass('outline-indigo-400')) {
                            clearHi();
                            $tr.addClass(rowHi);
                        }
                    });

                    wrap.on('dragleave', 'tr[data-task-row]', (e) => {
                        // Also fires when moving between the row's own cells.
                        if (!e.currentTarget.contains(e.originalEvent.relatedTarget)) $(e.currentTarget).removeClass(rowHi);
                    });

                    wrap.on('drop', 'tr[data-task-row]', (e) => {
                        const $tr = $(e.currentTarget);
                        if (!canNestUnder($tr)) return;
                        e.preventDefault();
                        const parentId = $tr.attr('data-task-id');
                        if ($dragRow().attr('data-parent-id') === parentId) return;
                        spreadsheetCollapsedIds.delete(parentId);
                        this.moveSpreadsheetTask(dragId, { parent_task_id: parentId });
                    });

                    wrap.on('dragover', '.spreadsheet-top-drop', (e) => {
                        if (!dragId) return;
                        e.preventDefault();
                        e.originalEvent.dataTransfer.dropEffect = 'move';
                        clearHi();
                        $(e.currentTarget).addClass(zoneHi);
                    });

                    wrap.on('dragleave', '.spreadsheet-top-drop', (e) => {
                        if (!e.currentTarget.contains(e.originalEvent.relatedTarget)) $(e.currentTarget).removeClass(zoneHi);
                    });

                    wrap.on('drop', '.spreadsheet-top-drop', (e) => {
                        if (!dragId) return;
                        e.preventDefault();
                        const statusId = $(e.currentTarget).attr('data-status-id');
                        const t = findTaskInTree(dragId, currentTasksCache);
                        if (!$dragRow().attr('data-parent-id') && t?.status_id === statusId) return;
                        this.moveSpreadsheetTask(dragId, { parent_task_id: '', status_id: statusId });
                    });
                },

                moveSpreadsheetTask(taskId, data) {
                    $.post(`${this.taskApiBase}/${taskId}/parent`, { ...data, _token: '{{ csrf_token() }}' })
                        .done(() => this.refreshTaskBoard())
                        .fail((xhr) => toastr.error(xhr.responseJSON?.message || 'Something went wrong.'));
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

        $('#kanbanPanel, #calendarPanel').on('scroll', hideCardTooltip);
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
        function loadProjectPicOptions($select, projectId) {
            $select.empty().trigger('change');
            if (!projectId) return;

            $.get(`{{ url('projects') }}/${projectId}/detail`, (res) => {
                fillTaskPicSelect($select, 'TSK', res.eligible_users || [], res.teams || []);
            });
        }

        // PIC picker for #taskModal and Quick Add. A Project Task (TSK) gets
        // two optgroups, Teams (only the ones linked to this Project) and
        // People (the Project's eligible users), valued "TEAM:<team_id>" /
        // "USER:<username>". A Team Task (TTK) is people-only, plain usernames.
        // Split back out with splitTaskPicValues() before posting.
        function fillTaskPicSelect($select, doctype, people, teams, selectedUsernames = [], selectedTeamIds = []) {
            $select.empty();

            if (doctype !== 'TSK') {
                people.forEach(u => $select.append(new Option(`${u.name} (${u.username})`, u.username, false, selectedUsernames.includes(u.username))));
                $select.trigger('change');
                return;
            }

            if (teams.length) {
                const $teams = $('<optgroup label="Teams"></optgroup>');
                teams.forEach(t => $teams.append(new Option(t.team_name, `TEAM:${t.team_id}`, false, selectedTeamIds.includes(t.team_id))));
                $select.append($teams);
            }
            const $people = $('<optgroup label="People"></optgroup>');
            people.forEach(u => $people.append(new Option(`${u.name} (${u.username})`, `USER:${u.username}`, false, selectedUsernames.includes(u.username))));
            $select.append($people);
            $select.trigger('change');
        }

        function splitTaskPicValues(values) {
            const assignees = [], team_ids = [];
            (values || []).forEach(v => {
                if (v.startsWith('TEAM:')) team_ids.push(v.slice(5));
                else assignees.push(v.startsWith('USER:') ? v.slice(5) : v);
            });
            return { assignees, team_ids };
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

            const selectedKeys = selectedPicEntries.map(e => `${e.pic_type}:${e.pic_type === 'TEAM' ? e.team_id : e.username}`);

            // No Teams is fine — a Project can be people-only, so the
            // People group below still loads.
            if (allTeams.length) {
                const teamGroup = $('<optgroup label="Teams"></optgroup>');
                allTeams.forEach((t) => {
                    const key = `TEAM:${t.team_id}`;
                    const checked = selectedTeamIds.includes(t.team_id) || selectedKeys.includes(key);
                    teamGroup.append(new Option(t.team_name, key, false, checked));
                });
                $select.append(teamGroup);
            }

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

            // Teams, people, or both — just not neither.
            if (!teamIds.length && !picEntries.some(e => e.pic_type === 'USER')) {
                Swal.fire({ icon: 'warning', title: 'Pick a Team or person', text: 'Select at least one Team or person for this project.' });
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
                projectTeams: [],   // Teams linked to the open Project — PIC picker's Teams group

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
                                $.post(`{{ url('projects') }}/${PM_PROJECT_ID}/tasks/${taskId}/status`, { status_id: statusId, _token: '{{ csrf_token() }}' })
                                    .fail(xhr => { toastr.error(xhr.responseJSON?.message || 'Could not move this task.'); this.renderTaskTab(); });
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
                            ${t.cover_url ? `<div class="-mx-3.5 -mt-3.5 mb-2.5 h-28 overflow-hidden rounded-t-xl bg-gray-100 dark:bg-white/5"><img src="${t.cover_url}" alt="" loading="lazy" class="h-full w-full object-cover"></div>` : ''}
                            ${badges ? `<div class="mb-1.5 flex flex-wrap gap-1">${badges}</div>` : ''}
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">${t.is_locked ? '<i class="fas fa-lock mr-1 text-[11px] text-amber-500"></i>' : ''}${t.task_name}</p>
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
        let spreadsheetBoardKey = null; // which board spreadsheetCollapsedIds belongs to

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

        // ── Activity feed (History panel + a Task's Activity tab) ─────────
        // Items come from PmActivityLogger::feed(): {kind, action, at
        // ('YYYY-MM-DD HH:mm:ss', server time), name, text, task_id,
        // task_name, is_subtask, changes[], message?, file?}.
        const ACTIVITY_ICONS = {
            created: ['fa-plus', '#059669'],
            updated: ['fa-pen', '#6366F1'],
            status: ['fa-arrow-right-arrow-left', '#3B82F6'],
            completed: ['fa-circle-check', '#059669'],
            reopened: ['fa-rotate-left', '#F59E0B'],
            locked: ['fa-lock', '#D97706'],
            unlocked: ['fa-lock-open', '#D97706'],
            cover: ['fa-image', '#8B5CF6'],
            moved: ['fa-arrow-right-arrow-left', '#0EA5E9'],
            archived: ['fa-box-archive', '#DC2626'],
            cancelled: ['fa-ban', '#DC2626'],
            restored: ['fa-rotate-left', '#059669'],
            commented: ['fa-comment', '#0EA5E9'],
            uploaded: ['fa-paperclip', '#8B5CF6'],
            file_deleted: ['fa-trash', '#DC2626'],
            file_renamed: ['fa-i-cursor', '#8B5CF6'],
            linked: ['fa-link', '#6366F1'],
            unlinked: ['fa-link-slash', '#6B7280'],
            status_column: ['fa-table-columns', '#6B7280'],
            meeting_room: ['fa-door-open', '#0284C7'],
            meeting_zoom: ['fa-video', '#7C3AED'],
            meeting_teams: ['fa-video', '#4F46E5'],
        };

        function activityEsc(str) {
            return $('<div>').text(str == null ? '' : String(str)).html();
        }

        function activitySearchText(i) {
            return [i.name, i.text, i.task_name, i.message, i.file,
                ...(i.changes || []).flatMap(c => [c.label, c.from, c.to, c.value, ...(c.added || []), ...(c.removed || [])])]
                .filter(Boolean).join(' ').toLowerCase();
        }

        function activityChangesHtml(changes) {
            if (!changes || !changes.length) return '';
            const rows = changes.map(c => {
                const label = `<span class="w-24 shrink-0 text-gray-400">${activityEsc(c.label)}</span>`;
                if (c.note) {
                    return `<div class="flex gap-2">${label}<span class="italic text-gray-500 dark:text-gray-400">${activityEsc(c.note)}</span></div>`;
                }
                // Plain detail line, no before/after (e.g. a booked meeting's Room / When).
                if ('value' in c) {
                    return `<div class="flex gap-2">${label}<span class="min-w-0 break-words font-medium text-gray-700 dark:text-gray-200">${activityEsc(c.value)}</span></div>`;
                }
                if (c.added || c.removed) {
                    const chips = [
                        ...(c.added || []).map(v => `<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"><i class="fas fa-plus text-[8px]"></i>${activityEsc(v)}</span>`),
                        ...(c.removed || []).map(v => `<span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-red-600 line-through dark:bg-red-500/10 dark:text-red-300"><i class="fas fa-minus text-[8px]"></i>${activityEsc(v)}</span>`),
                    ].join('');
                    return `<div class="flex gap-2">${label}<span class="flex min-w-0 flex-wrap gap-1">${chips}</span></div>`;
                }
                return `<div class="flex gap-2">${label}<span class="min-w-0 break-words"><span class="text-gray-400 line-through">${activityEsc(c.from)}</span> <i class="fas fa-arrow-right mx-1 text-[9px] text-gray-400"></i> <span class="font-medium text-gray-700 dark:text-gray-200">${activityEsc(c.to)}</span></span></div>`;
            }).join('');
            return `<div class="mt-2 space-y-1 rounded-lg border border-gray-100 bg-gray-50/70 px-3 py-2 text-xs dark:border-white/[0.06] dark:bg-white/[0.03]">${rows}</div>`;
        }

        // opts.showTask: link the task/subtask each row is about (off inside
        // a single task's own Activity tab, where it's the task itself).
        // opts.currentTaskId: rows about that task don't repeat its name.
        function activityFeedHtml(items, opts = {}) {
            if (!items || !items.length) {
                return '<p class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-400 dark:border-white/10">No activity yet.</p>';
            }

            const today = dayjs().format('YYYY-MM-DD');
            const yesterday = dayjs().subtract(1, 'day').format('YYYY-MM-DD');
            let html = '';
            let lastDay = null;

            items.forEach(i => {
                const at = dayjs(i.at);
                const day = at.format('YYYY-MM-DD');
                if (day !== lastDay) {
                    lastDay = day;
                    const prefix = day === today ? 'Today · ' : (day === yesterday ? 'Yesterday · ' : '');
                    html += `<div class="sticky top-0 z-10 -mx-1 mb-2 mt-4 bg-white/95 px-1 py-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400 backdrop-blur first:mt-0 dark:bg-[#0f172a]/95">${prefix}${at.format('dddd, DD MMM YYYY')}</div>`;
                }

                const [icon, color] = ACTIVITY_ICONS[i.action] || ['fa-circle-info', '#6B7280'];
                const showTarget = i.task_id && i.task_name && i.task_id !== opts.currentTaskId && (opts.showTask || i.is_subtask);
                const target = showTarget
                    ? ` <button type="button" class="activity-task-link font-medium text-indigo-600 hover:underline dark:text-indigo-400" data-task-id="${activityEsc(i.task_id)}">${i.is_subtask ? '<i class="fas fa-list-check mr-0.5 text-[10px]"></i>' : ''}${activityEsc(i.task_name)}</button>`
                    : '';
                const message = i.message
                    ? `<div class="mt-2 rounded-2xl rounded-tl-sm bg-sky-50 px-3 py-2 text-sm text-gray-700 dark:bg-sky-500/10 dark:text-gray-200">${highlightMentions(i.message)}</div>` : '';
                const file = i.file
                    ? `<div class="mt-2 inline-flex max-w-full items-center gap-1.5 rounded-lg border border-gray-200 px-2.5 py-1 text-xs text-gray-600 dark:border-white/10 dark:text-gray-300"><i class="fas fa-file text-[10px] text-violet-500"></i><span class="truncate">${activityEsc(i.file)}</span></div>` : '';

                html += `
                    <div class="relative flex gap-3 pb-4 last:pb-1">
                        <div class="relative flex flex-col items-center">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-white shadow-sm" style="background:${color}"><i class="fas ${icon} text-[11px]"></i></span>
                            <span class="mt-1 w-px flex-1 bg-gray-100 dark:bg-white/[0.06]"></span>
                        </div>
                        <div class="min-w-0 flex-1 pt-0.5">
                            <p class="text-sm leading-snug text-gray-600 dark:text-gray-300"><span class="font-semibold text-gray-800 dark:text-gray-100">${activityEsc(i.name)}</span> ${activityEsc(i.text)}${target}</p>
                            <p class="mt-0.5 font-mono text-[11px] text-gray-400" title="${activityEsc(at.fromNow())}"><i class="far fa-clock mr-1"></i>${at.format('DD MMM YYYY, HH:mm:ss')}</p>
                            ${activityChangesHtml(i.changes)}${message}${file}
                        </div>
                    </div>`;
            });

            return html;
        }

        // task_ids from the root down to (not including) taskId, or null if
        // it's not on the current board (archived, or behind a lock).
        function findTaskPath(taskId, nodes, trail = []) {
            for (const n of (nodes || [])) {
                if (n.task_id === taskId) return trail;
                const found = findTaskPath(taskId, n.children, [...trail, n.task_id]);
                if (found) return found;
            }
            return null;
        }

        $(document).on('click', '.activity-task-link', function () {
            const taskId = $(this).data('task-id');
            const path = findTaskPath(taskId, currentTasksCache);
            const task = path && findTaskInTree(taskId, currentTasksCache);
            if (!task) {
                toastr.info('This task is no longer on the board (it may have been archived).');
                return;
            }
            const portfolio = Alpine.$data(document.getElementById('pmPortfolioRoot'));
            if (portfolio) portfolio.historyOpen = false;
            taskDetailStack = path;
            openTaskEntityDetail(task);
        });

        // Task Detail → Activity tab (and the always-visible section when
        // drilled into a subtask). Stale responses are dropped if the user
        // has since opened a different task.
        function loadTaskActivity() {
            const taskId = currentDetailTaskId;
            if (!taskId || !currentTaskApiBase) return;
            const $list = $('#taskActivityList');
            if (!$list.children().length) $list.html('<p class="py-6 text-center text-sm text-gray-400"><i class="fas fa-spinner fa-spin mr-1"></i> Loading activity…</p>');

            $.get(`${currentTaskApiBase}/${taskId}/activity`, function (res) {
                if (currentDetailTaskId !== taskId) return;
                const items = res.items || [];
                $('#taskActivityCount').text(items.length ? `${items.length} ${items.length === 1 ? 'entry' : 'entries'}` : '');
                $list.html(activityFeedHtml(items, { showTask: false, currentTaskId: taskId }));
            }).fail(() => $list.html('<p class="py-6 text-center text-sm text-red-500">Could not load activity.</p>'));
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

        // "Meeting Room" / "Zoom" pills on a subtask/task row — open
        // openMeetingBooking() (see the .meeting-book-btn handler below).
        function meetingBookBtnsHtml(taskId) {
            const pill = 'meeting-book-btn inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] font-medium ring-1 transition dark:bg-white/5';
            return `
                <button type="button" class="${pill} text-sky-600 ring-sky-200 hover:bg-sky-50 dark:text-sky-300 dark:ring-sky-500/30 dark:hover:bg-sky-900/20" data-kind="room" data-task-id="${taskId}" title="Booking Meeting Room">
                    <i class="fas fa-door-open text-[9px]"></i> Meeting Room
                </button>
                <button type="button" class="${pill} text-violet-600 ring-violet-200 hover:bg-violet-50 dark:text-violet-300 dark:ring-violet-500/30 dark:hover:bg-violet-900/20" data-kind="zoom" data-task-id="${taskId}" title="Booking Zoom">
                    <i class="fas fa-video text-[9px]"></i> Zoom
                </button>
            `;
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
                            <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200 ${(done || cancelled) ? 'text-gray-400 line-through dark:text-gray-500' : ''}">${s.is_locked ? '<i class="fas fa-lock mr-1 text-[10px] text-amber-500"></i>' : ''}${s.task_name}</p>
                            ${cancelled ? `<span class="shrink-0 rounded-full bg-gray-200 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-white/10 dark:text-gray-400">Cancelled</span>` : ''}
                        </div>
                        ${stripHtml(s.task_description || '') ? `<p class="mt-0.5 truncate text-xs text-gray-400">${stripHtml(s.task_description)}</p>` : ''}
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
                                <i class="fas fa-calendar text-[9px]"></i> ${formatDate(s.start_date)} → ${formatDate(s.end_date)}
                            </span>
                            ${children.length ? `<span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10"><i class="fas fa-list-check text-[9px]"></i> ${childDone}/${children.length}</span>` : ''}
                            ${subtaskPicHtml(s.assignee_people)}
                            ${cancelled ? '' : meetingBookBtnsHtml(s.task_id)}
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
                        <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200 ${done ? 'text-gray-400 line-through dark:text-gray-500' : ''}">${t.is_locked ? '<i class="fas fa-lock mr-1 text-[10px] text-amber-500"></i>' : ''}${t.task_name}</p>
                        ${stripHtml(t.task_description || '') ? `<p class="mt-0.5 truncate text-xs text-gray-400">${stripHtml(t.task_description)}</p>` : ''}
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[11px] text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
                                <i class="fas fa-calendar text-[9px]"></i> ${formatDate(t.start_date)} → ${formatDate(t.end_date)}
                            </span>
                            ${subtaskPicHtml(t.assignee_people)}
                            ${meetingBookBtnsHtml(t.task_id)}
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
                    <tr data-task-row data-task-id="${t.task_id}" data-parent-id="${ancestors[ancestors.length - 1] || ''}" data-ancestors="${ancestors.join(',')}" data-depth="${depth}" draggable="true"
                        class="spreadsheet-row group cursor-pointer border-b border-gray-100 last:border-0 transition hover:bg-indigo-50/40 dark:border-white/[0.04] dark:hover:bg-indigo-900/10 ${cancelled ? 'opacity-60' : ''}">
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-2" style="padding-left:${depth * 20}px">
                                <i class="fas fa-grip-vertical w-2 shrink-0 cursor-grab text-[10px] text-gray-300 opacity-0 transition group-hover:opacity-100 dark:text-gray-600" title="Drag onto another task to make it a subtask"></i>
                                ${allChildren.length
                                    ? `<button type="button" class="spreadsheet-toggle-btn flex h-5 w-5 shrink-0 items-center justify-center rounded text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"><i class="fas fa-chevron-down spreadsheet-toggle-icon text-[10px]"></i></button>`
                                    : `<span class="inline-block h-5 w-5 shrink-0"></span>`}
                                <button type="button" class="spreadsheet-check flex h-4 w-4 shrink-0 items-center justify-center rounded border-2 transition ${done ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-gray-300 text-transparent hover:border-emerald-400 dark:border-white/20'}" title="Mark ${done ? 'incomplete' : 'complete'}" ${cancelled ? 'disabled' : ''}>
                                    <i class="fas fa-check text-[8px]"></i>
                                </button>
                                <span class="truncate text-sm font-medium text-gray-700 dark:text-gray-200 ${(done || cancelled) ? 'text-gray-400 line-through dark:text-gray-500' : ''}">${t.is_locked ? '<i class="fas fa-lock mr-1 text-[10px] text-amber-500"></i>' : ''}${t.task_name}</span>
                                ${cancelled ? `<span class="shrink-0 rounded-full bg-gray-200 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-white/10 dark:text-gray-400">Cancelled</span>` : ''}
                                ${late ? `<span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-red-600 dark:bg-red-500/10 dark:text-red-400"><i class="fas fa-triangle-exclamation text-[9px]"></i> Late</span>` : ''}
                                ${activeChildren.length ? `<span class="shrink-0 rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-500 dark:bg-white/10 dark:text-gray-400">${childDone}/${activeChildren.length}</span>` : ''}
                                ${cancelled ? '' : `<button type="button" class="spreadsheet-add-sub-btn hidden h-5 shrink-0 items-center gap-1 rounded px-1.5 text-[11px] font-medium text-gray-400 transition hover:bg-indigo-100 hover:text-indigo-600 group-hover:inline-flex dark:hover:bg-indigo-900/40 dark:hover:text-indigo-300" title="Add subtask"><i class="fas fa-plus text-[9px]"></i> Subtask</button>`}
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
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">${$('<div>').text(c.username ?? '').html()}</span>
                            <span class="text-[11px] text-gray-400">${timeAgo}</span>
                        </div>
                        <div class="mt-0.5 text-sm text-gray-700 dark:text-gray-200">${renderChatMessage(c.message)}</div>
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
        const PM_BYPASSES_TASK_LOCK = @json(\App\Models\TrProjectTask::bypassesLock(Auth::user()));

        // Header lock button + "Locked" badge. Project Tasks (TSK) only —
        // Team Tasks and Projects never show it. The button itself is only
        // offered to the task's assignees (or admins), matching
        // PmTaskController::toggleLock(); everyone else just sees the badge.
        function renderTaskLockState(task) {
            const $btn = $('#detailLockBtn');
            const isProjectTask = !!task && currentTaskDoctype === 'TSK';
            const locked = isProjectTask && !!task.is_locked;

            $('#detailLockedBadge').toggleClass('hidden', !locked).toggleClass('inline-flex', locked);

            const me = (PM_CURRENT_USER.username || '').trim().toLowerCase();
            // is_assignee (board-data) already counts PIC Team membership.
            const assigned = isProjectTask && (task.is_assignee ?? (task.assignees || []).some(u => (u || '').trim().toLowerCase() === me));
            const showBtn = isProjectTask && (assigned || PM_BYPASSES_TASK_LOCK);

            $btn.toggleClass('hidden', !showBtn).toggleClass('inline-flex', showBtn)
                .attr('title', locked ? 'Make task public — everyone on the project can open it' : 'Make task private — only assignees can open it');
            $btn.find('i').toggleClass('fa-lock', !locked).toggleClass('fa-lock-open', locked);
            $btn.find('span').text(locked ? 'Make public' : 'Make private');
        }

        $(document).on('click', '#detailLockBtn', function () {
            const task = findTaskInTree(currentDetailTaskId, currentTasksCache);
            if (!task) return;

            const lock = !task.is_locked;
            const send = () => $.post(`${currentTaskApiBase}/${task.task_id}/lock`, { locked: lock ? 1 : 0, _token: '{{ csrf_token() }}' })
                .done(res => {
                    task.is_locked = res.is_locked;
                    renderTaskLockState(task);
                    toastr.success(res.message);
                    refreshTaskDetailContext();
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' }));

            if (!lock) { send(); return; }

            Swal.fire({
                icon: 'question',
                title: 'Make this task private?',
                text: 'Only people assigned to it (and its subtasks) will be able to open it, chat, or see its files.',
                showCancelButton: true,
                confirmButtonText: 'Make private',
            }).then(r => { if (r.isConfirmed) send(); });
        });

        // Header cover banner + Cover button (Tasks/Subtasks of both
        // doctypes; a Project passes null and hides both). The button adds
        // straight away when there's no cover, else opens Replace/Remove.
        function renderTaskCover(task) {
            const url = task?.cover_url || null;
            $('#detailCoverBanner').toggleClass('hidden', !url);
            $('#detailCoverImg').attr('src', url || '');
            $('#detailCoverWrap').toggleClass('hidden', !task);
            $('#detailCoverBtn').attr('title', url ? 'Replace or remove the cover' : 'Add a cover image')
                .find('span').text(url ? 'Cover' : 'Add cover');
            $('#detailCoverMenu').addClass('hidden');
        }

        function saveTaskCover(task, request) {
            const $btn = $('#detailCoverBtn').prop('disabled', true);
            $.ajax(Object.assign({ url: `${currentTaskApiBase}/${task.task_id}/cover` }, request))
                .done(res => {
                    task.cover_url = res.cover_url;
                    renderTaskCover(task);
                    toastr.success(res.message);
                    refreshTaskDetailContext();
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' }))
                .always(() => $btn.prop('disabled', false));
        }

        $(document).on('click', '#detailCoverBtn', function (e) {
            e.stopPropagation();
            const task = findTaskInTree(currentDetailTaskId, currentTasksCache);
            if (!task) return;
            if (task.cover_url) $('#detailCoverMenu').toggleClass('hidden');
            else $('#detailCoverInput').trigger('click');
        });

        // Cover banner → full, uncropped preview (#coverPreviewModal).
        $(document).on('click', '#detailCoverPreviewBtn, #detailCoverImg', function () {
            const url = $('#detailCoverImg').attr('src');
            if (!url) return;
            $('#coverPreviewImg').attr('src', url);
            $('#coverPreviewOpen').attr('href', url);
            $('#coverPreviewModal').removeClass('hidden');
        });

        $(document).on('click', '.cover-preview-close', () => $('#coverPreviewModal').addClass('hidden'));

        // Capture phase so Esc closes only the preview, not the detail modal behind it.
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape' || $('#coverPreviewModal').hasClass('hidden')) return;
            e.stopImmediatePropagation();
            $('#coverPreviewModal').addClass('hidden');
        }, true);

        $(document).on('click', '#detailCoverReplace', function () {
            $('#detailCoverMenu').addClass('hidden');
            $('#detailCoverInput').trigger('click');
        });

        $(document).on('click', function (e) {
            if (!document.contains(e.target)) return;
            if (!$(e.target).closest('#detailCoverWrap').length) $('#detailCoverMenu').addClass('hidden');
        });

        $(document).on('change', '#detailCoverInput', function () {
            const file = this.files[0];
            this.value = '';
            const task = findTaskInTree(currentDetailTaskId, currentTasksCache);
            if (!file || !task) return;
            if (!file.type.startsWith('image/')) { toastr.error('Please pick an image file.'); return; }
            if (file.size > 5 * 1024 * 1024) { toastr.error('Cover image must be 5 MB or smaller.'); return; }

            const fd = new FormData();
            fd.append('cover', file);
            fd.append('_token', '{{ csrf_token() }}');
            saveTaskCover(task, { method: 'POST', data: fd, processData: false, contentType: false });
        });

        $(document).on('click', '#detailCoverRemove', function () {
            $('#detailCoverMenu').addClass('hidden');
            const task = findTaskInTree(currentDetailTaskId, currentTasksCache);
            if (!task) return;

            Swal.fire({
                icon: 'question',
                title: 'Remove this cover?',
                showCancelButton: true,
                confirmButtonText: 'Remove',
                confirmButtonColor: '#dc2626',
            }).then(r => {
                if (r.isConfirmed) saveTaskCover(task, { method: 'DELETE', data: { _token: '{{ csrf_token() }}' } });
            });
        });

        // Header "+" next to the PIC avatars (Tasks/Subtasks, both doctypes).
        // Offers whoever is eligible for the task (same pool as the Edit
        // form's picker) minus people already on it; saves additively via
        // addAssignees() — removing people still goes through Edit.
        let addPeoplePool = [];
        let addPeoplePicked = new Set();

        function renderAddPeoplePicker(task, eligible) {
            $('#detailAddPeopleWrap').toggleClass('hidden', !task);
            $('#detailAddPeopleMenu').addClass('hidden');
            if (!task) return;

            const onTask = new Set((task.assignees || []).map(u => (u || '').trim().toLowerCase()));
            addPeoplePool = (eligible || []).filter(u => !onTask.has((u.username || '').trim().toLowerCase()));
            addPeoplePicked = new Set();
        }

        function renderAddPeopleList() {
            const q = ($('#detailAddPeopleSearch').val() || '').trim().toLowerCase();
            const rows = addPeoplePool.filter(u => !q || `${u.name} ${u.username}`.toLowerCase().includes(q));
            const esc = s => $('<div>').text(s ?? '').html();
            const $list = $('#detailAddPeopleList').empty();

            if (!rows.length) {
                $list.append(`<p class="px-3 py-4 text-center text-xs text-gray-400">${addPeoplePool.length ? 'No one matches.' : 'Everyone eligible is already on this task.'}</p>`);
            }
            rows.forEach(u => {
                const picked = addPeoplePicked.has(u.username);
                $list.append(`
                    <button type="button" class="add-people-row flex w-full items-center gap-2.5 px-3 py-1.5 text-left hover:bg-gray-50 dark:hover:bg-white/5" data-username="${esc(u.username)}">
                        ${u.photo_url ? `<img src="${u.photo_url}" class="h-7 w-7 rounded-full object-cover">` : initialsAvatar(u.name, 28)}
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-gray-700 dark:text-gray-200">${esc(u.name)}</span>
                            <span class="block truncate text-[11px] text-gray-400">${esc(u.username)}</span>
                        </span>
                        <i class="fas fa-circle-check text-sm ${picked ? 'text-indigo-600' : 'text-gray-200 dark:text-white/10'}"></i>
                    </button>
                `);
            });

            $('#detailAddPeopleCount').text(addPeoplePicked.size ? `${addPeoplePicked.size} selected` : 'None selected');
            $('#detailAddPeopleSave').prop('disabled', !addPeoplePicked.size);
        }

        $(document).on('click', '#detailAddPeopleBtn', function (e) {
            e.stopPropagation();
            const $menu = $('#detailAddPeopleMenu').toggleClass('hidden');
            if ($menu.hasClass('hidden')) return;
            addPeoplePicked = new Set();
            $('#detailAddPeopleSearch').val('');
            renderAddPeopleList();
            $('#detailAddPeopleSearch').trigger('focus');
        });

        $(document).on('input', '#detailAddPeopleSearch', renderAddPeopleList);

        $(document).on('click', '.add-people-row', function () {
            const username = $(this).data('username').toString();
            addPeoplePicked.has(username) ? addPeoplePicked.delete(username) : addPeoplePicked.add(username);
            renderAddPeopleList();
        });

        // Picking a row re-renders the list, detaching the clicked element
        // before this runs — a detached target isn't an outside click.
        $(document).on('click', function (e) {
            if (!document.contains(e.target)) return;
            if (!$(e.target).closest('#detailAddPeopleWrap').length) $('#detailAddPeopleMenu').addClass('hidden');
        });

        $(document).on('click', '#detailAddPeopleSave', function () {
            const task = findTaskInTree(currentDetailTaskId, currentTasksCache);
            if (!task || !addPeoplePicked.size) return;

            const $btn = $(this).prop('disabled', true).text('Adding…');
            $.post(`${currentTaskApiBase}/${task.task_id}/assignees`, { usernames: [...addPeoplePicked], _token: '{{ csrf_token() }}' })
                .done(res => {
                    toastr.success(res.message);
                    $('#detailAddPeopleMenu').addClass('hidden');
                    // Re-open from the freshly loaded tree so the avatars,
                    // Overview PIC list and this picker's pool all update.
                    refreshTaskDetailContext(() => {
                        const fresh = findTaskInTree(task.task_id, currentTasksCache);
                        if (fresh) openTaskEntityDetail(fresh, 'none');
                    });
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' }))
                .always(() => $btn.text('Add'));
        });

        // ── Move task (header Move button → #taskMoveModal) ─────────────
        // The source board comes from currentTaskApiBase
        // (`.../projects/{id}/tasks` or `.../all-team/{id}/tasks`). Targets
        // (every Team/Project the user can open, with status columns) come
        // from PmTaskMoveController::targets(); the move itself re-homes the
        // whole subtree server-side.
        let taskMoveTargets = { teams: [], projects: [] };
        let taskMoveStatusId = null;

        function currentTaskBoard() {
            const m = (currentTaskApiBase || '').match(/\/(projects|all-team)\/([^/]+)\/tasks$/);
            return m ? { type: m[1] === 'projects' ? 'PROJECT' : 'TEAM', id: decodeURIComponent(m[2]) } : null;
        }

        function renderTaskMoveStatuses() {
            const [type, id] = ($('#taskMoveTarget').val() || '').split('|');
            const board = (type === 'TEAM' ? taskMoveTargets.teams : taskMoveTargets.projects).find(b => b.id === id);
            const statuses = board?.statuses || [];
            const esc = s => $('<div>').text(s ?? '').html();

            if (!statuses.some(s => s.status_id === taskMoveStatusId)) taskMoveStatusId = statuses[0]?.status_id || null;

            const $wrap = $('#taskMoveStatuses').empty();
            if (!board) {
                $wrap.append('<p class="text-sm text-gray-400">Pick a Team or Project first.</p>');
            } else if (!statuses.length) {
                $wrap.append('<p class="text-sm text-gray-400">This board has no status columns yet.</p>');
            }
            statuses.forEach(s => {
                const color = s.color || '#6366F1';
                const active = s.status_id === taskMoveStatusId;
                $wrap.append(`
                    <button type="button" class="task-move-status rounded-full border px-3 py-1.5 text-xs font-semibold transition" data-status-id="${esc(s.status_id)}"
                        style="border-color:${color};${active ? `background:${color};color:#fff;` : `color:${color};background:${hexToRgba(color, 0.08)};`}">
                        ${active ? '<i class="fas fa-check mr-1 text-[10px]"></i>' : ''}${esc(s.status_name)}
                    </button>
                `);
            });

            $('#taskMoveSave').prop('disabled', !board || !taskMoveStatusId);
        }

        function openTaskMoveModal() {
            const task = findTaskInTree(currentDetailTaskId, currentTasksCache);
            const source = currentTaskBoard();
            if (!task || !source) return;

            $('#taskMoveTaskName').text(task.task_name);
            $('#taskMoveTarget').html('<option>Loading…</option>').prop('disabled', true);
            $('#taskMoveStatuses').empty();
            $('#taskMoveSave').prop('disabled', true);
            $('#taskMoveModal').removeClass('hidden');

            $.get('{{ route('pm-task-move.targets') }}', res => {
                taskMoveTargets = res;
                const esc = s => $('<div>').text(s ?? '').html();
                const group = (label, type, boards) => boards.length ? `<optgroup label="${label}">${boards.map(b => {
                    const current = type === source.type && b.id === source.id;
                    return `<option value="${type}|${esc(b.id)}" ${current ? 'disabled' : ''}>${esc(b.name)}${current ? ' (current)' : ''}</option>`;
                }).join('')}</optgroup>` : '';

                $('#taskMoveTarget').html('<option value="">Choose a Team or Project…</option>'
                    + group('Teams', 'TEAM', res.teams) + group('Projects', 'PROJECT', res.projects)).prop('disabled', false);
                taskMoveStatusId = null;
                renderTaskMoveStatuses();
            }).fail(() => { toastr.error('Could not load Teams/Projects.'); $('#taskMoveModal').addClass('hidden'); });
        }

        $(document).on('click', '#detailMoveBtn', openTaskMoveModal);
        $(document).on('click', '.task-move-close', () => $('#taskMoveModal').addClass('hidden'));

        // ── Book a Meeting Room / Zoom for a subtask (#meetingBookingModal) ──
        // kind 'room' → /meeting (Create modal auto-opens, room pickable);
        // kind 'zoom' → /meetingteams (user picks a slot on a Zoom/Teams row).
        // Title/Description/date come prefilled from the subtask.
        function openMeetingBooking(task, kind) {
            const parentName = $('#detailProjectName').text().trim();
            const params = new URLSearchParams({
                embed: 1,
                title: task.task_name || '',
                descr: parentName && parentName !== task.task_name
                    ? `Meeting for "${task.task_name}" — ${parentName}`
                    : `Meeting for "${task.task_name}"`,
                date: task.start_date ? dayjs(task.start_date).format('YYYY-MM-DD') : '',
                // MeetingController::logPmTaskMeeting() writes the booking
                // into this task's Activity List on save.
                pm_doctype: currentTaskDoctype,
                pm_task_id: task.task_id,
            });
            const path = kind === 'zoom' ? '{{ url('meetingteams') }}' : '{{ url('meeting') }}';

            $('#meetingBookingTitle').text(kind === 'zoom' ? 'Booking Zoom' : 'Booking Meeting Room');
            $('#meetingBookingTaskName').text(task.task_name || '');
            $('#meetingBookingFrame').attr('src', `${path}?${params.toString()}`);
            $('#meetingBookingModal').removeClass('hidden');
        }

        function closeMeetingBooking() {
            $('#meetingBookingModal').addClass('hidden');
            $('#meetingBookingFrame').attr('src', 'about:blank');
        }

        $(document).on('click', '.meeting-book-btn', function (e) {
            e.stopPropagation(); // don't drill into / open the row itself
            const task = findTaskInTree($(this).data('task-id'), currentTasksCache);
            if (!task) return;
            openMeetingBooking(task, $(this).data('kind'));
        });

        $(document).on('click', '.meeting-booking-close', closeMeetingBooking);

        window.addEventListener('message', function (e) {
            if (e.origin !== window.location.origin || e.data?.type !== 'pm-meeting-booked') return;
            closeMeetingBooking();
            toastr.success(e.data.message || 'Meeting booked.');
            if (currentDetailTaskId) loadTaskActivity();
        });
        $(document).on('change', '#taskMoveTarget', renderTaskMoveStatuses);
        $(document).on('click', '.task-move-status', function () {
            taskMoveStatusId = $(this).data('status-id').toString();
            renderTaskMoveStatuses();
        });

        $(document).on('click', '#taskMoveSave', function () {
            const task = findTaskInTree(currentDetailTaskId, currentTasksCache);
            const source = currentTaskBoard();
            const [toType, toId] = ($('#taskMoveTarget').val() || '').split('|');
            if (!task || !source || !toId || !taskMoveStatusId) return;

            const $btn = $(this).prop('disabled', true).text('Moving…');
            $.post('{{ route('pm-task-move') }}', {
                from_type: source.type,
                from_id: source.id,
                task_id: task.task_id,
                to_type: toType,
                to_id: toId,
                status_id: taskMoveStatusId,
                _token: '{{ csrf_token() }}',
            })
                .done(res => {
                    $('#taskMoveModal').addClass('hidden');
                    closeProjectDetail('replace');
                    refreshTaskDetailContext();
                    toastr.success(res.message);
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Could not move', text: xhr.responseJSON?.message || 'Something went wrong.' }))
                .always(() => $btn.prop('disabled', false).text('Move'));
        });

        function openTaskEntityDetail(task, historyMode = 'push') {
            // Locked Project Task the current user isn't assigned to —
            // board-data already masked it; every open path funnels through
            // here, so this one check covers cards, calendar, spreadsheet and
            // subtask rows alike. The server enforces the same rule.
            if (task.can_access === false) {
                Swal.fire({
                    icon: 'info',
                    title: 'This task is private',
                    text: 'Only people assigned to this task can open it.',
                });
                return;
            }

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
            renderTaskLockState(task);
            renderTaskCover(task);
            $('#detailMoveBtn').removeClass('hidden').addClass('inline-flex');

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

            // PIC Teams (Project Tasks only) — header chip + one row each
            // in the Overview PIC list, above the individual people.
            const picTeams = task.teams || [];
            picTeams.forEach(t => $picsHeader.append(`
                <span class="ml-2 inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="fas fa-users text-[9px]"></i> ${t.team_name}</span>
            `));

            const $picList = $('#detailProjectPicList').empty();
            picTeams.forEach(t => $picList.append(`
                <div class="flex items-center gap-2.5">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"><i class="fas fa-users text-[11px]"></i></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">${t.team_name}</span>
                    <span class="text-[11px] text-gray-400">Team</span>
                </div>
            `));
            if (pics.length) {
                pics.forEach(p => $picList.append(`
                    <div class="flex items-center gap-2.5">
                        ${p.photo_url ? `<img src="${p.photo_url}" alt="${p.name}" class="h-7 w-7 rounded-full object-cover">` : initialsAvatar(p.name, 28)}
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">${p.name}</span>
                    </div>
                `));
            } else if (!picTeams.length) {
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

            renderAddPeoplePicker(task, eligible);

            const keep = splitTaskPicValues($('#task_assignees').val());
            fillTaskPicSelect($('#task_assignees'), currentTaskDoctype, eligible, root.projectTeams || [], keep.assignees, keep.team_ids);

            $('#projectDetailModal').removeClass('hidden');
            refreshEntityAttachments();
            loadEntityComments();
            $('#taskActivityList').empty();
            $('#taskActivityCount').text('');
            loadTaskActivity();

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
            const root = Alpine.$data(document.getElementById('pmProjectShowRoot'));
            fillTaskPicSelect($('#task_assignees'), currentTaskDoctype, eligible, root.projectTeams || [],
                task.assignees || [], (task.teams || []).map(t => t.team_id));

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
                if (Alpine.$data(document.getElementById('pmProjectShowRoot')).canCreateProject) {
                    $chip.find('.linked-project-unlink').on('click', () => Alpine.$data(document.getElementById('pmProjectShowRoot')).unlinkProject(lp.project_id));
                } else {
                    $chip.find('.linked-project-unlink').remove();
                }
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
                renderTaskLockState(null);
                renderTaskCover(null);
                renderAddPeoplePicker(null);
                $('#detailMoveBtn').addClass('hidden').removeClass('inline-flex');

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

                const keep = splitTaskPicValues($('#task_assignees').val());
                fillTaskPicSelect($('#task_assignees'), 'TSK', data.eligible_users || [], data.teams || [], keep.assignees, keep.team_ids);
                const root = Alpine.$data(document.getElementById('pmProjectShowRoot'));
                root.kind = 'project';
                root.tab = 'overview';
                root.eligibleUsers = data.eligible_users || [];
                root.projectTeams = data.teams || [];

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

        // attachment_name is stored without its extension (it lives in
        // `extention`), so rejoin them for display, badge and preview.
        function attachmentRowHtml(at) {
            const fullName = at.extention ? `${at.name}.${at.extention}` : (at.name || '');
            const badge = fileTypeBadge(fullName);
            const esc = s => $('<div>').text(s ?? '').html();
            const iconBtn = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 opacity-0 transition hover:bg-white group-hover:opacity-100 dark:hover:bg-white/10';
            return `
                <div class="attachment-row group flex items-center gap-3.5 rounded-xl bg-gray-50 px-4 py-3 transition dark:bg-white/[0.03] ${at.url ? 'cursor-pointer hover:bg-indigo-50/60 dark:hover:bg-indigo-900/10' : ''}" data-id="${at.id}" data-url="${esc(at.url)}" data-name="${esc(fullName)}" data-label="${esc(at.name)}">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[10px] font-extrabold" style="background:${badge.bg};color:${badge.color}">${esc(badge.label)}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">${esc(fullName)}</p>
                        <p class="mt-0.5 text-xs text-gray-400">${esc(at.created_by || '—')} · ${esc(at.created_at || '—')}</p>
                    </div>
                    ${at.url ? `<a href="${esc(at.url)}" target="_blank" onclick="event.stopPropagation()" title="Download" class="attachment-download ${iconBtn} hover:text-indigo-600 dark:hover:text-indigo-400"><i class="fas fa-arrow-down-to-bracket text-xs"></i></a>` : ''}
                    <button type="button" title="Rename" class="attachment-rename ${iconBtn} hover:text-amber-600 dark:hover:text-amber-400"><i class="fas fa-pen text-xs"></i></button>
                    <button type="button" title="Delete" class="attachment-delete ${iconBtn} hover:text-red-600 dark:hover:text-red-400"><i class="fas fa-trash text-xs"></i></button>
                </div>
            `;
        }

        // Preview libraries are only fetched the first time a file of that
        // type is opened — most visits never need them.
        const previewScriptCache = {};
        function loadPreviewScript(src) {
            return previewScriptCache[src] ??= new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = src;
                s.onload = resolve;
                s.onerror = () => { delete previewScriptCache[src]; reject(new Error('Failed to load ' + src)); };
                document.head.appendChild(s);
            });
        }

        // Bumped on every open/close so a slow Word/Excel render can't land
        // in the modal after the user has moved on to another file.
        let filePreviewSeq = 0;

        // Click-to-preview: images/video/PDF render straight from the signed
        // GCS URL; Word/Excel/CSV/text are fetched through the same-origin
        // /attachments/{id}/stream endpoint and rendered client-side, so file
        // contents never go to a third-party viewer. Anything else (legacy
        // .doc/.xls, PowerPoint, archives…) falls back to a Download prompt.
        function openFilePreview(name, url, id) {
            if (!url) return;
            const seq = ++filePreviewSeq;
            const ext = (name.split('.').pop() || '').toLowerCase();
            const esc = s => $('<div>').text(s ?? '').html();
            const streamUrl = id ? `{{ url('attachments') }}/${id}/stream` : null;
            const kind =
                ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'].includes(ext) ? 'image' :
                ['mp4', 'mov', 'webm', 'm4v', 'ogg'].includes(ext) ? 'video' :
                ext === 'pdf' ? 'pdf' :
                streamUrl && ext === 'docx' ? 'docx' :
                streamUrl && ['xlsx', 'xlsm', 'xls', 'csv', 'ods'].includes(ext) ? 'sheet' :
                streamUrl && ['txt', 'log', 'json', 'xml', 'md'].includes(ext) ? 'text' : 'other';

            $('#filePreviewName').text(name);
            $('#filePreviewDownload').attr('href', url);

            // Documents read better wide and top-aligned; media stays centered.
            const isDoc = ['docx', 'sheet', 'text'].includes(kind);
            $('#filePreviewPanel').toggleClass('max-w-3xl', !isDoc).toggleClass('max-w-5xl', isDoc);
            const $body = $('#filePreviewBody').empty()
                .toggleClass('items-center justify-center', !isDoc)
                .toggleClass('items-start justify-start', isDoc);

            const downloadFallback = (msg) => `
                <div class="flex w-full flex-col items-center gap-3 py-10 text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/10"><i class="fas fa-file text-xl"></i></span>
                    <p class="text-sm text-gray-500 dark:text-gray-400">${msg}</p>
                    <a href="${esc(url)}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"><i class="fas fa-arrow-down-to-bracket text-xs"></i> Download</a>
                </div>`;

            if (kind === 'image') {
                $body.append(`<img src="${esc(url)}" alt="${esc(name)}" class="max-h-full max-w-full rounded-lg object-contain">`);
            } else if (kind === 'video') {
                $body.append(`<video src="${esc(url)}" controls autoplay playsinline class="max-h-[75vh] max-w-full rounded-lg bg-black"></video>`);
                $body.find('video').on('error', () => $body.html(downloadFallback("This video format can't be played in the browser.")));
            } else if (kind === 'pdf') {
                $body.append(`<iframe src="${esc(url)}" class="h-[70vh] w-full rounded-lg border-0 bg-white"></iframe>`);
            } else if (kind === 'other') {
                $body.append(downloadFallback('No inline preview for this file type.'));
            } else {
                $body.append('<p class="w-full py-10 text-center text-sm text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Loading preview…</p>');
                renderDocumentPreview(kind, ext, streamUrl)
                    .then(node => { if (seq === filePreviewSeq) $body.empty().append(node); })
                    .catch(err => {
                        console.error('File preview failed', err);
                        if (seq === filePreviewSeq) $body.html(downloadFallback("Couldn't render a preview for this file."));
                    });
            }
            $('#filePreviewModal').removeClass('hidden');
        }

        async function renderDocumentPreview(kind, ext, streamUrl) {
            const res = await fetch(streamUrl, { credentials: 'same-origin' });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            if (kind === 'text') {
                const pre = document.createElement('pre');
                pre.className = 'w-full whitespace-pre-wrap break-words rounded-lg bg-white p-4 font-mono text-xs text-gray-800 dark:bg-white/[0.03] dark:text-gray-200';
                pre.textContent = await res.text();
                return pre;
            }

            const buf = await res.arrayBuffer();

            if (kind === 'docx') {
                await loadPreviewScript('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js');
                await loadPreviewScript('https://cdn.jsdelivr.net/npm/docx-preview@0.3.5/dist/docx-preview.min.js');
                const wrap = document.createElement('div');
                wrap.className = 'w-full overflow-auto rounded-lg';
                await docx.renderAsync(buf, wrap, null, { inWrapper: true, ignoreLastRenderedPageBreak: true });
                return wrap;
            }

            // Spreadsheets: one tab per sheet, rendered as a plain HTML table.
            await loadPreviewScript('https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js');
            const wb = ext === 'csv'
                ? XLSX.read(new TextDecoder().decode(buf), { type: 'string' })
                : XLSX.read(buf, { type: 'array' });

            const wrap = document.createElement('div');
            wrap.className = 'w-full';
            const tabs = document.createElement('div');
            tabs.className = 'mb-3 flex flex-wrap gap-1.5';
            const sheetBox = document.createElement('div');
            sheetBox.className = 'sheet-preview w-full overflow-auto rounded-lg border border-gray-200 bg-white text-xs text-gray-800 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200';

            const showSheet = (i) => {
                sheetBox.innerHTML = XLSX.utils.sheet_to_html(wb.Sheets[wb.SheetNames[i]], { header: '', footer: '' });
                [...tabs.children].forEach((b, j) => {
                    b.className = 'rounded-md px-2.5 py-1 text-xs font-medium ' + (i === j
                        ? 'bg-indigo-600 text-white'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-300');
                });
            };
            wb.SheetNames.forEach((sheetName, i) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.textContent = sheetName;
                b.onclick = () => showSheet(i);
                tabs.appendChild(b);
            });
            if (wb.SheetNames.length > 1) wrap.appendChild(tabs);
            wrap.appendChild(sheetBox);
            showSheet(0);
            return wrap;
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
                // onDone gets the response (res.uploaded_ids = the new rows) or null on failure.
                success: function (res) {
                    if (!res.success) toastr.error(res.message);
                    if (onDone) onDone(res.success ? res : null);
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Attachment upload failed.');
                    if (onDone) onDone(null);
                },
            });
        }

        // The open entity's attachments by id — the chat resolves its
        // [[file:id|name]] markers against this, so a chat file chip shows
        // the file's current (renamed) name, or "removed" once deleted.
        let entityAttachmentsById = {};
        let entityCommentsCache = [];
        const currentEntityKey = () => `${PM_ENTITY_DOCTYPE}/${PM_ENTITY_ID}`;

        function refreshEntityAttachments() {
            const key = currentEntityKey();
            const listUrl = `{{ url('attachments') }}/${key}`;
            $.get(listUrl).done(res => {
                if (key !== currentEntityKey()) return;
                entityAttachmentsById = {};
                (res.attachments || []).forEach(at => { entityAttachmentsById[at.id] = at; });
                renderEntityComments();

                const $list = $('#projectAttachmentList').empty();
                if (!res.success || !res.attachments || !res.attachments.length) {
                    $list.append('<p class="rounded-xl border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">No attachments yet.</p>');
                    return;
                }
                res.attachments.forEach(at => $list.append(attachmentRowHtml(at)));
            });
        }

        let lastCommentsEntityKey = null;
        function loadEntityComments() {
            const key = currentEntityKey();
            if (key !== lastCommentsEntityKey) {
                // Switched to a different Project/Task — drop anything staged for the old one.
                lastCommentsEntityKey = key;
                stagedChatFiles = [];
                renderStagedChatFiles();
                entityCommentsCache = [];
                $('#projectCommentList').html('<p class="italic text-gray-400">Loading comments...</p>');
            }
            $.get(`/comments/${key}`, function (res) {
                if (key !== currentEntityKey()) return;
                entityCommentsCache = res.comments || [];
                renderEntityComments();
            });
        }

        function renderEntityComments() {
            const $list = $('#projectCommentList').empty();
            if (!entityCommentsCache.length) {
                $list.append('<p class="text-sm italic text-gray-400">No comments yet.</p>');
                return;
            }
            entityCommentsCache.forEach(c => $list.append(commentItemHtml(c)));
        }

        // ── Chat message body: links, @mentions and shared files ──
        const CHAT_FILE_MARKER = /\[\[file:(\d+)\|([^\]]*)\]\]/g;
        const CHAT_URL = /\b(https?:\/\/[^\s<>"']+|www\.[^\s<>"']+)/gi;

        function linkifyChatText(text) {
            let html = '', last = 0;
            for (const m of text.matchAll(CHAT_URL)) {
                // Trailing punctuation is almost always sentence, not URL.
                const url = m[0].replace(/[.,;:!?)\]]+$/, '');
                html += highlightMentions(text.slice(last, m.index));
                const href = /^https?:\/\//i.test(url) ? url : `https://${url}`;
                const esc = s => $('<div>').text(s).html();
                html += `<a href="${esc(href)}" target="_blank" rel="noopener noreferrer" class="break-all text-indigo-600 underline decoration-indigo-300 underline-offset-2 hover:text-indigo-500 dark:text-indigo-400">${esc(url)}</a>`;
                last = m.index + url.length;
            }
            return html + highlightMentions(text.slice(last));
        }

        function chatFileChipHtml(id, fallbackName, attachmentsById = entityAttachmentsById) {
            const esc = s => $('<div>').text(s ?? '').html();
            const at = attachmentsById[id];
            if (!at) {
                return `<div class="mt-1.5 inline-flex max-w-full items-center gap-2 rounded-lg border border-dashed border-gray-300 px-3 py-2 text-xs text-gray-400 dark:border-white/15"><i class="fas fa-file-circle-xmark"></i><span class="truncate line-through">${esc(fallbackName)}</span><span class="shrink-0">· removed</span></div>`;
            }
            const fullName = at.extention ? `${at.name}.${at.extention}` : at.name;
            const badge = fileTypeBadge(fullName);
            const isImage = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'].includes((at.extention || '').toLowerCase());
            const size = at.size ? (at.size >= 1048576 ? (at.size / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(at.size / 1024)) + ' KB') : '';
            return `
                <button type="button" class="chat-file mt-1.5 block max-w-full overflow-hidden rounded-lg border border-gray-200 bg-white text-left transition hover:border-indigo-300 hover:shadow-sm dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-indigo-500/50" data-id="${at.id}" data-url="${esc(at.url)}" data-name="${esc(fullName)}">
                    ${isImage && at.url ? `<img src="${esc(at.url)}" alt="${esc(fullName)}" loading="lazy" class="max-h-44 w-auto max-w-full bg-gray-100 object-contain dark:bg-black/20">` : ''}
                    <span class="flex items-center gap-2.5 px-3 py-2">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-[9px] font-extrabold" style="background:${badge.bg};color:${badge.color}">${esc(badge.label)}</span>
                        <span class="min-w-0">
                            <span class="block truncate text-xs font-medium text-gray-800 dark:text-gray-100">${esc(fullName)}</span>
                            <span class="block text-[11px] text-gray-400">${size ? size + ' · ' : ''}Click to preview</span>
                        </span>
                    </span>
                </button>`;
        }

        // attachmentsById: the thread's own files by id — the open detail
        // modal's by default, or a board Message thread's (boardChat).
        function renderChatMessage(message, attachmentsById = entityAttachmentsById) {
            const text = String(message ?? '');
            let html = '', last = 0;
            for (const m of text.matchAll(CHAT_FILE_MARKER)) {
                const before = text.slice(last, m.index).trim();
                if (before) html += `<p class="whitespace-pre-line break-words">${linkifyChatText(before)}</p>`;
                html += chatFileChipHtml(m[1], m[2], attachmentsById);
                last = m.index + m[0].length;
            }
            const rest = text.slice(last).trim();
            if (rest) html += `<p class="whitespace-pre-line break-words">${linkifyChatText(rest)}</p>`;
            return html;
        }

        // Files picked/pasted into the chat composer, uploaded on Send.
        let stagedChatFiles = [];
        function renderStagedChatFiles() {
            const esc = s => $('<div>').text(s ?? '').html();
            const $box = $('#chatStagedFiles').empty()
                .toggleClass('hidden', !stagedChatFiles.length).toggleClass('flex', !!stagedChatFiles.length);
            stagedChatFiles.forEach((f, i) => {
                const tooBig = f.size > 5 * 1024 * 1024;
                $box.append(`
                    <span class="inline-flex max-w-[16rem] items-center gap-1.5 rounded-full px-2.5 py-1 text-xs ${tooBig ? 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300'}" title="${tooBig ? 'Over 5MB — will be skipped' : esc(f.name)}">
                        <i class="fas ${tooBig ? 'fa-triangle-exclamation' : 'fa-paperclip'} text-[10px]"></i>
                        <span class="truncate">${esc(f.name)}</span>
                        <button type="button" class="chat-staged-remove ml-0.5 opacity-60 hover:opacity-100" data-i="${i}"><i class="fas fa-times text-[10px]"></i></button>
                    </span>`);
            });
        }

        // ── Team / Project "Message" tab (board-wide chat) ─────────────
        // One thread per scope: doctype 'TEAM' (refnbr = team_id) or 'PRJ'
        // (refnbr = project_id — the same thread as the Project Detail
        // modal's Chat tab). Same /comments + /attachments endpoints as a
        // Task's chat, so @mentions, links and shared files all work the
        // same; bell notifications come from DocumentNotificationService.
        // Polled every 10s while the tab is showing.
        const boardChat = { doctype: null, id: null, messages: [], attachmentsById: {}, checkedFileIds: new Set(), staged: [], timer: null, sig: '' };
        const boardChatKey = () => boardChat.id ? `${boardChat.doctype}/${boardChat.id}` : null;

        function boardChatMentionUrl() {
            if (!boardChat.id) return null;
            return boardChat.doctype === 'TEAM'
                ? `{{ url('all-team') }}/${boardChat.id}/tasks/mentionable-users`
                : `{{ url('projects') }}/${boardChat.id}/mentionable-users`;
        }

        function openBoardChat(doctype, id) {
            if (boardChat.doctype !== doctype || boardChat.id !== id) {
                Object.assign(boardChat, { doctype, id, messages: [], attachmentsById: {}, checkedFileIds: new Set(), staged: [], sig: '' });
                $('#boardChatInput').val('');
                autosizeBoardChatInput();
                renderBoardChatStaged();
                $('#boardChatPeople').empty();
                $('#boardChatList').html('<p class="py-10 text-center text-sm italic text-gray-400">Loading messages…</p>');
                loadBoardChatPeople();
            }
            loadBoardChat(true);

            clearInterval(boardChat.timer);
            boardChat.timer = setInterval(() => {
                if (document.visibilityState === 'visible') loadBoardChat(false);
            }, 10000);
        }

        function closeBoardChat() {
            clearInterval(boardChat.timer);
            boardChat.timer = null;
        }

        // scrollToEnd: jump to the newest message (opening the tab, or
        // after sending); otherwise only re-renders when something changed.
        function loadBoardChat(scrollToEnd) {
            const key = boardChatKey();
            if (!key) return;

            $.get(`/comments/${key}`).done(res => {
                if (key !== boardChatKey()) return;
                const messages = (res.comments || []).slice().reverse(); // oldest first

                const finish = () => {
                    const sig = messages.map(m => m.id).join(',');
                    if (sig === boardChat.sig && !scrollToEnd) return;
                    boardChat.sig = sig;
                    boardChat.messages = messages;
                    renderBoardChat(scrollToEnd);
                };

                // Only (re)load the file list when a message points at a
                // file we haven't looked up yet — not on every poll.
                const fileIds = messages.flatMap(m => [...String(m.message ?? '').matchAll(CHAT_FILE_MARKER)].map(x => x[1]));
                const unknown = fileIds.filter(fid => !boardChat.attachmentsById[fid] && !boardChat.checkedFileIds.has(fid));
                if (!unknown.length) return finish();

                $.get(`{{ url('attachments') }}/${key}`).done(r => {
                    if (key !== boardChatKey()) return;
                    boardChat.attachmentsById = {};
                    (r.attachments || []).forEach(at => { boardChat.attachmentsById[at.id] = at; });
                    unknown.forEach(fid => boardChat.checkedFileIds.add(fid)); // removed files: don't re-check every poll
                }).always(finish);
            }).fail(xhr => {
                if (key !== boardChatKey() || boardChat.messages.length) return;
                $('#boardChatList').html(`<p class="py-10 text-center text-sm text-red-500">${$('<div>').text(xhr.responseJSON?.message || 'Could not load messages.').html()}</p>`);
            });
        }

        function loadBoardChatPeople() {
            const url = boardChatMentionUrl(), key = boardChatKey();
            if (!url) return;
            $.get(url).done(list => {
                if (key !== boardChatKey()) return;
                const people = [{ username: PM_CURRENT_USER.username, name: PM_CURRENT_USER.name }, ...(Array.isArray(list) ? list : [])];
                const shown = people.slice(0, 5).map((p, i) =>
                    `<span class="inline-block rounded-full ring-2 ring-white dark:ring-gray-900" style="margin-left:${i ? '-8px' : '0'}">${initialsAvatar(p.name || p.username, 28)}</span>`).join('');
                const more = people.length > 5 ? `<span class="-ml-2 flex h-7 min-w-[1.75rem] items-center justify-center rounded-full bg-gray-100 px-1.5 text-[10px] font-semibold text-gray-500 ring-2 ring-white dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-900">+${people.length - 5}</span>` : '';
                $('#boardChatPeople')
                    .attr('title', people.map(p => p.name || p.username).join(', '))
                    .html(`<div class="flex items-center">${shown}${more}</div><span class="ml-2 hidden text-xs text-gray-400 sm:inline">${people.length} ${people.length === 1 ? 'person' : 'people'}</span>`);
            });
        }

        function renderBoardChat(scrollToEnd) {
            const $list = $('#boardChatList');
            const el = $list[0];
            const wasNearBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 80;
            const prevCount = $list.data('count') || 0;
            const msgs = boardChat.messages;
            const esc = s => $('<div>').text(s ?? '').html();
            const me = (PM_CURRENT_USER.username || '').trim().toLowerCase();

            if (!msgs.length) {
                $list.data('count', 0).html(`
                    <div class="flex h-full flex-col items-center justify-center gap-2 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="fas fa-comments text-lg"></i></span>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">No messages yet</p>
                        <p class="max-w-xs text-xs text-gray-400">Start the conversation — everyone here will see it, and anyone you @mention gets a notification.</p>
                    </div>`);
                $('#boardChatNewPill').addClass('hidden').removeClass('inline-flex');
                return;
            }

            const dayLabel = (d) => d.isSame(dayjs(), 'day') ? 'Today'
                : d.isSame(dayjs().subtract(1, 'day'), 'day') ? 'Yesterday'
                : d.format(d.year() === dayjs().year() ? 'dddd, DD MMM' : 'DD MMM YYYY');

            let html = '', prev = null;
            msgs.forEach(m => {
                const at = dayjs(m.message_date);
                const user = (m.username || '').trim().toLowerCase();
                const mine = user === me;
                const newDay = !prev || !dayjs(prev.message_date).isSame(at, 'day');
                // Consecutive messages from one person within 5 minutes
                // stack under a single name/avatar.
                const grouped = !newDay && (prev.username || '').trim().toLowerCase() === user && at.diff(dayjs(prev.message_date), 'minute') < 5;
                const mentionsMe = !mine && me && new RegExp(`@${me.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'i').test(m.message || '');

                if (newDay) {
                    html += `<div class="my-4 flex items-center gap-3 text-[11px] font-medium text-gray-400"><span class="h-px flex-1 bg-gray-100 dark:bg-white/[0.06]"></span>${dayLabel(at)}<span class="h-px flex-1 bg-gray-100 dark:bg-white/[0.06]"></span></div>`;
                }

                const body = renderChatMessage(m.message, boardChat.attachmentsById);
                const time = `<span class="text-[11px] text-gray-400" title="${at.format('DD MMM YYYY HH:mm')}">${at.format('HH:mm')}</span>`;

                if (mine) {
                    html += `
                        <div class="flex justify-end ${grouped ? 'mt-1' : 'mt-3'}">
                            <div class="flex max-w-[75%] flex-col items-end">
                                ${grouped ? '' : `<div class="mb-1 flex items-center gap-2">${time}<span class="text-xs font-semibold text-gray-700 dark:text-gray-200">You</span></div>`}
                                <div class="rounded-2xl ${grouped ? '' : 'rounded-tr-sm'} bg-indigo-50 px-3.5 py-2 text-sm text-gray-800 dark:bg-indigo-500/15 dark:text-gray-100" title="${at.format('DD MMM YYYY HH:mm')}">${body}</div>
                            </div>
                        </div>`;
                } else {
                    html += `
                        <div class="flex items-start gap-2.5 ${grouped ? 'mt-1' : 'mt-3'}">
                            <div class="w-8 shrink-0">${grouped ? '' : initialsAvatar(m.name || m.username, 32)}</div>
                            <div class="flex min-w-0 max-w-[75%] flex-col items-start">
                                ${grouped ? '' : `<div class="mb-1 flex items-center gap-2"><span class="text-xs font-semibold text-gray-700 dark:text-gray-200">${esc(m.name || m.username)}</span>${time}</div>`}
                                <div class="rounded-2xl ${grouped ? '' : 'rounded-tl-sm'} px-3.5 py-2 text-sm text-gray-800 dark:text-gray-100 ${mentionsMe ? 'bg-amber-50 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:ring-amber-500/30' : 'bg-gray-100 dark:bg-white/[0.06]'}" title="${at.format('DD MMM YYYY HH:mm')}">${body}</div>
                            </div>
                        </div>`;
                }
                prev = m;
            });

            $list.data('count', msgs.length).html(html);

            const stick = scrollToEnd || wasNearBottom;
            if (stick) {
                el.scrollTop = el.scrollHeight;
                // Shared images finish loading after this — keep the view pinned.
                $list.find('img').one('load', () => { el.scrollTop = el.scrollHeight; });
                $('#boardChatNewPill').addClass('hidden').removeClass('inline-flex');
            } else if (msgs.length > prevCount) {
                $('#boardChatNewPill').removeClass('hidden').addClass('inline-flex');
            }
        }

        function renderBoardChatStaged() {
            const esc = s => $('<div>').text(s ?? '').html();
            const $box = $('#boardChatStagedFiles').empty()
                .toggleClass('hidden', !boardChat.staged.length).toggleClass('flex', !!boardChat.staged.length);
            boardChat.staged.forEach((f, i) => {
                const tooBig = f.size > 5 * 1024 * 1024;
                $box.append(`
                    <span class="inline-flex max-w-[16rem] items-center gap-1.5 rounded-full px-2.5 py-1 text-xs ${tooBig ? 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300'}" title="${tooBig ? 'Over 5MB — will be skipped' : esc(f.name)}">
                        <i class="fas ${tooBig ? 'fa-triangle-exclamation' : 'fa-paperclip'} text-[10px]"></i>
                        <span class="truncate">${esc(f.name)}</span>
                        <button type="button" class="board-chat-staged-remove ml-0.5 opacity-60 hover:opacity-100" data-i="${i}"><i class="fas fa-times text-[10px]"></i></button>
                    </span>`);
            });
        }

        function autosizeBoardChatInput() {
            const el = document.getElementById('boardChatInput');
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 128) + 'px';
        }

        function sendBoardChat() {
            const $btn = $('#boardChatSendBtn');
            const key = boardChatKey();
            const val = $('#boardChatInput').val().trim();
            if (!key || (!val && !boardChat.staged.length) || $btn.prop('disabled')) return;

            const { doctype, id } = boardChat;
            const tooBig = boardChat.staged.filter(f => f.size > 5 * 1024 * 1024);
            if (tooBig.length) {
                toastr.warning(`Not sent (over 5MB): ${tooBig.map(f => f.name).join(', ')}`);
                boardChat.staged = boardChat.staged.filter(f => !tooBig.includes(f));
                renderBoardChatStaged();
            }
            const files = boardChat.staged.slice();
            $btn.prop('disabled', true);

            const post = (attachmentIds) => {
                if (!val && !attachmentIds.length) { $btn.prop('disabled', false); return; }
                $.post(`/comments/${doctype}/${id}`, { comment: val, attachment_ids: attachmentIds, _token: '{{ csrf_token() }}' })
                    .done(() => {
                        if (key !== boardChatKey()) return;
                        $('#boardChatInput').val('');
                        autosizeBoardChatInput();
                        boardChat.staged = [];
                        renderBoardChatStaged();
                        loadBoardChat(true);
                    })
                    .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Message could not be sent.'))
                    .always(() => $btn.prop('disabled', false));
            };

            if (!files.length) return post([]);

            uploadFilesToProjectAttachments(files, doctype, id, (res) => {
                if (!res) { $btn.prop('disabled', false); return; } // upload failed — keep text & files staged
                post(res.uploaded_ids || []);
            });
        }

        $(function () {
            // Registered before the Enter-to-send keydown below, so an open
            // @mention dropdown gets Enter first (it stops propagation).
            attachMentionAutocomplete({ inputSelector: '#boardChatInput', fetchUrlFn: boardChatMentionUrl });

            $(document).on('keydown', '#boardChatInput', function (e) {
                if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
                    e.preventDefault();
                    sendBoardChat();
                }
            });
            $(document).on('input', '#boardChatInput', autosizeBoardChatInput);

            $('#boardChatSendBtn').on('click', sendBoardChat);

            $('#boardChatMentionBtn').on('click', function () {
                const $input = $('#boardChatInput');
                const val = $input.val();
                $input.val(val + (val && !/\s$/.test(val) ? ' @' : '@')).trigger('input').focus();
            });

            const stage = (files) => {
                if (!files.length) return;
                boardChat.staged = boardChat.staged.concat(files).slice(0, 10);
                renderBoardChatStaged();
            };
            $('#boardChatAttachBtn').on('click', () => $('#boardChatFileInput').trigger('click'));
            $('#boardChatFileInput').on('change', function () {
                stage(Array.from(this.files));
                this.value = '';
            });
            $('#boardChatInput').on('paste', function (e) {
                const files = Array.from(e.originalEvent.clipboardData?.files || []);
                if (!files.length) return; // plain text / links paste normally
                e.preventDefault();
                stage(files.map(f => f.name === 'image.png'
                    ? new File([f], `pasted-${dayjs().format('YYYYMMDD-HHmmss')}.png`, { type: f.type })
                    : f));
            });
            $(document).on('click', '.board-chat-staged-remove', function () {
                boardChat.staged.splice($(this).data('i'), 1);
                renderBoardChatStaged();
            });

            $('#boardChatNewPill').on('click', function () {
                const el = document.getElementById('boardChatList');
                el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
                $(this).addClass('hidden').removeClass('inline-flex');
            });
            $('#boardChatList').on('scroll', function () {
                if (this.scrollHeight - this.scrollTop - this.clientHeight < 80) {
                    $('#boardChatNewPill').addClass('hidden').removeClass('inline-flex');
                }
            });
        });

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
                const pics = splitTaskPicValues($('#task_assignees').val());
                serialized += '&' + $.param({ assignees: pics.assignees, team_ids: pics.team_ids });
                // Lets the server tell "cleared every PIC" apart from "didn't send PICs".
                if (currentTaskDoctype === 'TSK') serialized += '&pic_submitted=1';

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
                openFilePreview($(this).data('name'), $(this).data('url'), $(this).data('id'));
            });

            $(document).on('click', '.attachment-rename', function (e) {
                e.stopPropagation();
                const $row = $(this).closest('.attachment-row');
                Swal.fire({
                    title: 'Rename file',
                    input: 'text',
                    inputValue: String($row.data('label') ?? ''),
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    confirmButtonColor: '#4F46E5',
                    inputValidator: v => (!v || !v.trim()) ? 'Name cannot be empty.' : undefined,
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: `{{ url('attachments') }}/${$row.data('id')}/rename`,
                        method: 'PUT',
                        data: { name: result.value.trim(), _token: '{{ csrf_token() }}' },
                        success: () => { toastr.success('Renamed.'); refreshEntityAttachments(); },
                        error: xhr => toastr.error(xhr.responseJSON?.message || 'Rename failed.'),
                    });
                });
            });

            $(document).on('click', '.attachment-delete', function (e) {
                e.stopPropagation();
                const $row = $(this).closest('.attachment-row');
                Swal.fire({
                    icon: 'warning',
                    title: 'Delete this file?',
                    html: `<b>${$('<div>').text($row.data('name')).html()}</b> will be removed from this item's files.`,
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonColor: '#DC2626',
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: `{{ url('attachments') }}/${$row.data('id')}/soft`,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: () => { toastr.success('Deleted.'); refreshEntityAttachments(); },
                        error: xhr => toastr.error(xhr.responseJSON?.message || 'Delete failed.'),
                    });
                });
            });

            $('#closeFilePreviewModal').on('click', function () {
                filePreviewSeq++; // drop any Word/Excel render still in flight
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

            // ── Chat file sharing: pick via the paperclip or paste straight
            // into the input; files go to this item's Files tab on Send and
            // the message links to them. ──
            const stageChatFiles = (files) => {
                if (!files.length) return;
                stagedChatFiles = stagedChatFiles.concat(files).slice(0, 10);
                renderStagedChatFiles();
            };

            $('#projectAttachChatBtn').on('click', () => $('#chatFileInput').trigger('click'));

            $('#chatFileInput').on('change', function () {
                stageChatFiles(Array.from(this.files));
                this.value = '';
            });

            $('#projectCommentInput').on('paste', function (e) {
                const files = Array.from(e.originalEvent.clipboardData?.files || []);
                if (!files.length) return; // plain text / links paste normally
                e.preventDefault();
                // Pasted screenshots all arrive as "image.png" — give them a unique name.
                stageChatFiles(files.map(f => f.name === 'image.png'
                    ? new File([f], `pasted-${dayjs().format('YYYYMMDD-HHmmss')}.png`, { type: f.type })
                    : f));
            });

            $(document).on('click', '.chat-staged-remove', function () {
                stagedChatFiles.splice($(this).data('i'), 1);
                renderStagedChatFiles();
            });

            $(document).on('click', '.chat-file', function () {
                openFilePreview($(this).data('name'), $(this).data('url'), $(this).data('id'));
            });

            $('#projectPostCommentBtn').on('click', function () {
                const $btn = $(this);
                const val = $('#projectCommentInput').val().trim();
                if ((!val && !stagedChatFiles.length) || !PM_ENTITY_ID || $btn.prop('disabled')) return;

                const doctype = PM_ENTITY_DOCTYPE, entityId = PM_ENTITY_ID;
                const tooBig = stagedChatFiles.filter(f => f.size > 5 * 1024 * 1024);
                if (tooBig.length) {
                    toastr.warning(`Not sent (over 5MB): ${tooBig.map(f => f.name).join(', ')}`);
                    stagedChatFiles = stagedChatFiles.filter(f => !tooBig.includes(f));
                    renderStagedChatFiles();
                }
                const files = stagedChatFiles.slice();
                $btn.prop('disabled', true);

                const postComment = (attachmentIds) => {
                    if (!val && !attachmentIds.length) { $btn.prop('disabled', false); return; }
                    $.post(`/comments/${doctype}/${entityId}`, { comment: val, attachment_ids: attachmentIds, _token: '{{ csrf_token() }}' })
                        .done(() => {
                            $('#projectCommentInput').val('');
                            stagedChatFiles = [];
                            renderStagedChatFiles();
                            loadEntityComments();
                        })
                        .fail(xhr => toastr.error(xhr.responseJSON?.message || 'Message could not be sent.'))
                        .always(() => $btn.prop('disabled', false));
                };

                if (!files.length) return postComment([]);

                uploadFilesToProjectAttachments(files, doctype, entityId, (res) => {
                    refreshEntityAttachments(); // Files tab + chat chips
                    if (!res) { $btn.prop('disabled', false); return; } // upload failed — keep text & files staged
                    postComment(res.uploaded_ids || []);
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
