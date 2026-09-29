<x-app-layout>
    @php $openTeamId = $openTeamId ?? null; @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🚀 All Team</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Teams can freely mix people from any
                        company or department. Only a Captain (CAPTACCESS) can create one.</p>
                </div>
                <div class="flex items-center gap-2">
                    @if ($canCreate)
                        <button id="addTeamBtn"
                            class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500">
                            + New Team
                        </button>
                    @endif
                </div>
            </div>

            <div id="teamsGrid" class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"></div>
            <p id="teamsEmptyState" class="hidden px-5 pb-6 text-sm text-gray-400">No teams yet.</p>
        </div>
    </div>

    <!-- Modal -->
    <div id="teamModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-slate-800">

                {{-- HEADER --}}
                <div class="relative flex items-start justify-between overflow-hidden bg-linear-to-r from-indigo-600 to-violet-600 px-7 py-6">
                    <div class="pointer-events-none absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
                    <div class="pointer-events-none absolute -right-2 bottom-0 h-16 w-16 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 id="teamModalTitle" class="text-lg font-semibold text-white">New Team</h2>
                            <p class="mt-0.5 text-xs text-indigo-100/90">Search for anyone across every company/department
                                and add them as a Member.</p>
                        </div>
                    </div>
                    <button id="closeTeamModal" type="button"
                        class="relative rounded-full p-2 text-indigo-100 transition hover:bg-white/15 hover:text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="teamForm" class="flex min-h-0 flex-1 flex-col">
                    <input type="hidden" id="team_id" name="team_id">

                    <div class="flex min-h-0 flex-1">
                        {{-- LEFT: Team details --}}
                        <div class="w-full min-w-0 space-y-6 overflow-y-auto border-r border-slate-100 bg-slate-50/50 p-7 md:w-1/2 dark:border-slate-700 dark:bg-slate-800/50">
                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.796 0-5.487-.46-8.135-1.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
                                    Team Name
                                </label>
                                <input id="team_name" name="team_name" type="text" required
                                    placeholder="e.g. Cross-Company Task Force"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-500/20">
                            </div>

                            <div>
                                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.008M3.75 12h.008M3.75 17.25h.008" /></svg>
                                    Description
                                </label>
                                <textarea id="team_description" name="team_description" rows="3"
                                    placeholder="What is this Team for?"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-500/20"></textarea>
                            </div>

                            <div>
                                <div class="mb-1.5 flex items-center justify-between">
                                    <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                                        Selected Members
                                    </label>
                                    <span id="membersSelectedCount" class="rounded-full bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">0</span>
                                </div>
                                <div id="selectedMembersBox" class="flex min-h-14 flex-wrap content-start gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-3 dark:border-slate-600 dark:bg-slate-700/30">
                                    <span class="flex w-full items-center justify-center gap-1.5 py-2 text-xs text-slate-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                                        No members selected yet
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT: Member search panel --}}
                        <div class="hidden w-1/2 min-w-0 flex-col p-7 md:flex">
                            <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z" /></svg>
                                Search People
                            </label>
                            <div class="relative mb-3">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z" />
                                </svg>
                                <input id="memberSearch" type="text" placeholder="Search by name or username..."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm shadow-sm transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:focus:ring-indigo-500/20">
                            </div>
                            <div class="min-h-0 flex-1 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-600">
                                <table class="w-full text-sm">
                                    <thead class="sticky top-0 z-10 bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                        <tr>
                                            <th class="w-10 px-3 py-2.5"></th>
                                            <th class="px-2 py-2.5 text-left font-semibold">Name</th>
                                            <th class="px-2 py-2.5 text-left font-semibold">Company</th>
                                            <th class="px-2 py-2.5 text-left font-semibold">Dept</th>
                                        </tr>
                                    </thead>
                                    <tbody id="searchResultsBody" class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
                                </table>
                                <div id="searchIdleState" class="flex flex-col items-center justify-center gap-2 py-10 text-slate-300 dark:text-slate-600">
                                    <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z" />
                                    </svg>
                                </div>
                            </div>
                            <p id="searchHint" class="mt-2 text-center text-xs text-slate-400">Type at least 2 characters to search.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-white px-7 py-4 dark:border-slate-700 dark:bg-slate-800">
                        <button type="button" id="cancelTeamBtn"
                            class="h-10 rounded-xl border border-slate-200 px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">Cancel</button>
                        <button type="submit"
                            class="flex h-10 items-center gap-2 rounded-xl bg-linear-to-r from-indigo-600 to-violet-600 px-5 text-sm font-medium text-white shadow-sm shadow-indigo-500/30 transition hover:shadow-md hover:shadow-indigo-500/40">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Save Team
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Team Detail Modal (read-only info view — has a real, shareable URL) -->
    <div id="teamDetailModal" class="fixed inset-0 z-50 hidden">
        <div id="teamDetailBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-slate-800">

                <div class="relative flex items-start justify-between overflow-hidden bg-linear-to-r from-indigo-600 to-violet-600 px-7 py-6">
                    <div class="pointer-events-none absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
                    <div class="relative min-w-0">
                        <p id="detailTeamId" class="font-mono text-[11px] text-indigo-100/80"></p>
                        <h2 id="detailTeamName" class="mt-0.5 truncate text-lg font-semibold text-white">Team</h2>
                    </div>
                    <button id="closeTeamDetailModal" type="button"
                        class="relative shrink-0 rounded-full p-2 text-indigo-100 transition hover:bg-white/15 hover:text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto p-7">
                    <p id="detailTeamDescription" class="text-sm text-slate-600 dark:text-slate-300"></p>

                    <div class="mt-5">
                        <div class="mb-2 flex items-center justify-between">
                            <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                                Members
                            </label>
                            <span id="detailMemberCount" class="rounded-full bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">0</span>
                        </div>
                        <div id="detailMembersList" class="space-y-1.5"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 bg-white px-7 py-4 dark:border-slate-700 dark:bg-slate-800">
                    <p id="detailMeta" class="truncate text-xs text-slate-400"></p>
                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" id="detailEditBtn"
                            class="hidden h-9 items-center gap-1.5 rounded-lg border border-slate-200 px-3 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                            Edit
                        </button>
                        <button type="button" id="closeTeamDetailModalBtn"
                            class="h-9 rounded-lg bg-slate-100 px-4 text-xs font-medium text-slate-600 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let selectedMembers = {}; // username -> { username, name, cpny_id, department_id }
        let searchDebounce = null;

        const AVATAR_COLORS = [
            'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
            'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300',
            'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300',
        ];

        function initials(name) {
            return (name || '?').trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase();
        }

        function avatarColor(username) {
            let hash = 0;
            for (const c of (username || '')) hash = (hash * 31 + c.charCodeAt(0)) % AVATAR_COLORS.length;
            return AVATAR_COLORS[hash];
        }

        function updateMembersSelectedCount() {
            $('#membersSelectedCount').text(Object.keys(selectedMembers).length);
        }

        function renderSelectedMembers() {
            const $box = $('#selectedMembersBox').empty();
            const usernames = Object.keys(selectedMembers);
            updateMembersSelectedCount();

            if (!usernames.length) {
                $box.append(`
                    <span class="flex w-full items-center justify-center gap-1.5 py-2 text-xs text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                        No members selected yet
                    </span>
                `);
                return;
            }

            usernames.forEach(username => {
                const m = selectedMembers[username];
                $box.append(`
                    <span class="member-chip inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-1.5 text-xs shadow-sm dark:border-slate-600 dark:bg-slate-700">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold ${avatarColor(m.username)}">${initials(m.name)}</span>
                        <span class="font-medium text-slate-700 dark:text-slate-200">${m.name}</span>
                        <span class="text-slate-400">${m.username}</span>
                        <button type="button" data-username="${m.username}" title="Remove"
                            class="remove-member-chip flex h-4 w-4 items-center justify-center rounded-full text-slate-400 transition hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-500/20">
                            <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                        <input type="hidden" name="members[]" value="${m.username}">
                    </span>
                `);
            });
        }

        function toggleMember(user) {
            if (selectedMembers[user.username]) {
                delete selectedMembers[user.username];
            } else {
                selectedMembers[user.username] = user;
            }
            renderSelectedMembers();
            $(`#searchResultsBody tr[data-username="${user.username}"]`).toggleClass('bg-indigo-50/70 dark:bg-indigo-500/10', !!selectedMembers[user.username]);
            $(`#searchResultsBody tr[data-username="${user.username}"] input[type="checkbox"]`)
                .prop('checked', !!selectedMembers[user.username]);
        }

        function renderSearchResults(users) {
            const $body = $('#searchResultsBody').empty();
            $('#searchIdleState').addClass('hidden');

            if (!users.length) {
                $body.append(`
                    <tr><td colspan="4" class="px-3 py-8 text-center text-xs text-slate-400">
                        No matches for that search.
                    </td></tr>
                `);
                return;
            }

            users.forEach(u => {
                const checked = !!selectedMembers[u.username];
                $body.append(`
                    <tr data-username="${u.username}" class="cursor-pointer transition hover:bg-slate-50 dark:hover:bg-slate-700/50 ${checked ? 'bg-indigo-50/70 dark:bg-indigo-500/10' : ''}">
                        <td class="px-3 py-2"><input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" ${checked ? 'checked' : ''}></td>
                        <td class="px-2 py-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-bold ${avatarColor(u.username)}">${initials(u.name)}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-slate-700 dark:text-slate-200">${u.name}</p>
                                    <p class="truncate text-[11px] text-slate-400">${u.username}${u.jabatan ? ' · ' + u.jabatan : ''}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-2 text-slate-500 dark:text-slate-400">${u.cpny_id || '—'}</td>
                        <td class="px-2 py-2 text-slate-500 dark:text-slate-400">${u.department_id || '—'}</td>
                    </tr>
                `);
                $body.data('users-' + u.username, u);
            });
        }

        function searchUsers(term) {
            $.get('{{ route('all-team.search-users') }}', { q: term }, function (res) {
                const users = res.data || [];
                const total = res.total ?? users.length;
                users.forEach(u => $('#searchResultsBody').data('users-' + u.username, u));
                renderSearchResults(users);

                if (total > users.length) {
                    $('#searchHint').text(`Showing top ${users.length} of ${total} matches — refine your search to narrow down.`);
                } else if (users.length) {
                    $('#searchHint').text(`${users.length} match${users.length === 1 ? '' : 'es'} found.`);
                } else {
                    $('#searchHint').text('No matches for that search.');
                }
            });
        }

        function teamCard(row) {
            const editBtn = row.can_manage
                ? `<button class="edit-team-btn shrink-0 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-gray-700" data-id="${row.team_id}" title="Edit"><i class="fas fa-pen text-xs"></i></button>`
                : '';

            return $(`
                <div class="team-card flex cursor-pointer flex-col rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-500/40" data-id="${row.team_id}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[11px] font-mono text-gray-400">${row.team_id}</p>
                            <h3 class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">${row.team_name}</h3>
                        </div>
                        ${editBtn}
                    </div>
                    <p class="mt-1 line-clamp-1 text-xs text-gray-500 dark:text-gray-400">${row.team_description || '<span class="text-gray-300">No description.</span>'}</p>
                    <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-2 text-[11px] text-gray-400 dark:border-gray-700">
                        <span>${row.member_count} member${row.member_count === 1 ? '' : 's'}</span>
                        <span class="truncate">by ${row.created_by || '—'}</span>
                    </div>
                </div>
            `);
        }

        function detailMemberRow(m) {
            const isCaptain = m.member_role === 'CAPTAIN';
            const meta = [m.cpny_id, m.department_id].filter(Boolean).join(' · ');

            return `
                <div class="flex items-center gap-2.5 rounded-lg border border-slate-100 px-3 py-2 dark:border-slate-700">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold ${avatarColor(m.username)}">${initials(m.name)}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm text-slate-700 dark:text-slate-200">${m.name}</p>
                        <p class="truncate text-[11px] text-slate-400">${m.username}${meta ? ' · ' + meta : ''}</p>
                    </div>
                    ${isCaptain ? `
                        <span class="flex shrink-0 items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                            <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2.5 6.5l3.5 2.5 4-5 4 5 3.5-2.5-1.5 9h-12l-1.5-9z" /></svg>
                            Captain
                        </span>
                    ` : ''}
                </div>
            `;
        }

        // historyMode: 'push' (user-initiated navigation) | 'replace' (deep-link
        // load, URL is already correct) | 'none' (browser back/forward already moved).
        function openTeamDetail(teamId, historyMode = 'push') {
            $.get(`{{ url('all-team') }}/${teamId}/detail`, function (data) {
                $('#detailTeamId').text(data.team_id);
                $('#detailTeamName').text(data.team_name);
                $('#detailTeamDescription').html(data.team_description || '<span class="text-slate-400">No description.</span>');
                $('#detailMemberCount').text(data.member_count);
                $('#detailMembersList').html((data.members || []).map(detailMemberRow).join(''));
                $('#detailMeta').text(`Created by ${data.created_by || '—'}${data.created_at ? ' · ' + data.created_at : ''}`);
                $('#detailEditBtn').toggleClass('hidden', !data.can_manage).toggleClass('inline-flex', !!data.can_manage).data('id', data.team_id);

                $('#teamDetailModal').removeClass('hidden');

                // URL uses the obfuscated eid (Hashids-encoded ms_team.id), matching
                // this app's /showticket/{eid} convention — not the raw team_id.
                const url = `{{ url('all-team') }}/${data.eid}`;
                if (historyMode === 'push') history.pushState({ teamId }, '', url);
                else if (historyMode === 'replace') history.replaceState({ teamId }, '', url);
            }).fail(function () {
                closeTeamDetail('replace');
                Swal.fire({ icon: 'error', title: 'Team not found', text: 'This team may have been archived or removed.' });
            });
        }

        function closeTeamDetail(historyMode = 'push') {
            $('#teamDetailModal').addClass('hidden');
            const url = '{{ route('all-team.index') }}';
            if (historyMode === 'push') history.pushState({}, '', url);
            else if (historyMode === 'replace') history.replaceState({}, '', url);
        }

        function loadTeams() {
            $.get('{{ route('all-team.json') }}', function (res) {
                const rows = res.data || [];
                const $grid = $('#teamsGrid').empty();
                $('#teamsEmptyState').toggleClass('hidden', rows.length > 0);
                rows.forEach(row => $grid.append(teamCard(row)));
            });
        }

        $(function () {
            loadTeams();

            function resetForm() {
                $('#teamForm')[0].reset();
                $('#team_id').val('');
                selectedMembers = {};
                renderSelectedMembers();
                $('#memberSearch').val('');
                $('#searchResultsBody').empty();
                $('#searchIdleState').removeClass('hidden');
                $('#searchHint').text('Type at least 2 characters to search.');
                $('#teamModalTitle').text('New Team');
            }

            $('#addTeamBtn').on('click', function () {
                resetForm();
                $('#teamModal').removeClass('hidden');
            });

            $('#closeTeamModal, #cancelTeamBtn').on('click', function () {
                $('#teamModal').addClass('hidden');
            });

            $('#memberSearch').on('input', function () {
                const term = $(this).val().trim();
                clearTimeout(searchDebounce);

                if (term.length < 2) {
                    $('#searchResultsBody').empty();
                    $('#searchIdleState').removeClass('hidden');
                    $('#searchHint').text('Type at least 2 characters to search.');
                    return;
                }

                $('#searchIdleState').addClass('hidden');
                $('#searchHint').text('Searching...');
                searchDebounce = setTimeout(() => searchUsers(term), 300);
            });

            $('#searchResultsBody').on('click', 'tr[data-username]', function (e) {
                if (e.target.type === 'checkbox') return; // avoid double-toggle from the click + native change
                const username = $(this).data('username');
                const user = $('#searchResultsBody').data('users-' + username);
                if (user) toggleMember(user);
            });

            $('#searchResultsBody').on('change', 'input[type="checkbox"]', function () {
                const $row = $(this).closest('tr');
                const username = $row.data('username');
                const user = $('#searchResultsBody').data('users-' + username);
                if (user) toggleMember(user);
            });

            $('#selectedMembersBox').on('click', '.remove-member-chip', function () {
                const username = $(this).data('username');
                delete selectedMembers[username];
                renderSelectedMembers();
                $(`#searchResultsBody tr[data-username="${username}"] input[type="checkbox"]`).prop('checked', false);
            });

            function openEditTeamModal(teamId) {
                $.get(`{{ url('all-team') }}/${teamId}/edit`, function (data) {
                    resetForm();
                    $('#team_id').val(data.team_id);
                    $('#team_name').val(data.team_name);
                    $('#team_description').val(data.team_description);
                    $('#teamModalTitle').text('Edit Team — ' + data.team_id);

                    // Pre-populate Selected Members from names embedded in the
                    // search-results cache is not possible yet (nothing searched),
                    // so just seed with username as a stand-in name and let the
                    // user re-search if they want to see full details.
                    (data.members || []).forEach(username => {
                        selectedMembers[username] = { username, name: username, cpny_id: '', department_id: '' };
                    });
                    renderSelectedMembers();

                    $('#teamModal').removeClass('hidden');
                });
            }

            $('#teamsGrid').on('click', '.edit-team-btn', function (e) {
                e.stopPropagation(); // don't also trigger the card's detail-view click
                openEditTeamModal($(this).data('id'));
            });

            $('#teamsGrid').on('click', '.team-card', function () {
                openTeamDetail($(this).data('id'), 'push');
            });

            $('#closeTeamDetailModal, #closeTeamDetailModalBtn, #teamDetailBackdrop').on('click', function () {
                closeTeamDetail('push');
            });

            $('#detailEditBtn').on('click', function () {
                const teamId = $(this).data('id');
                closeTeamDetail('push');
                openEditTeamModal(teamId);
            });

            window.addEventListener('popstate', function (e) {
                if (e.state && e.state.teamId) {
                    openTeamDetail(e.state.teamId, 'none');
                } else {
                    closeTeamDetail('none');
                }
            });

            const initialTeamId = @json($openTeamId ?? null);
            if (initialTeamId) {
                openTeamDetail(initialTeamId, 'replace');
            }

            $('#teamForm').on('submit', function (e) {
                e.preventDefault();

                const teamId = $('#team_id').val();
                const url = teamId ? `{{ url('all-team') }}/${teamId}` : '{{ route('all-team.store') }}';
                const method = teamId ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    method: method,
                    data: $(this).serialize() + '&_token={{ csrf_token() }}',
                    success: function (res) {
                        $('#teamModal').addClass('hidden');
                        loadTeams();
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
