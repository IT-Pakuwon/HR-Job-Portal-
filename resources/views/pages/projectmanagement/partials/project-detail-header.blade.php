{{-- Shared Detail Modal header — id, title, status pill, dates, PIC avatars
     and subtask progress. Shows a Project (openProjectDetail()), a top-level
     Task/Team-Task card (openTaskEntityDetail(), kind 'task'), or one of its
     own Subtasks drilled into (same function, kind 'subtask') — both in
     projects.blade.php. `kind` (Alpine, on pmProjectShowRoot) tells them apart. --}}
{{-- Task cover banner — Tasks/Subtasks only, filled by renderTaskCover(). --}}
<div id="detailCoverBanner" class="relative hidden h-36 shrink-0 overflow-hidden bg-gray-100 dark:bg-white/5">
    <img id="detailCoverImg" src="" alt="Task cover" class="h-full w-full cursor-zoom-in object-cover">
    {{-- Opens #coverPreviewModal (projects.blade.php) with the uncropped image. --}}
    <button id="detailCoverPreviewBtn" type="button" title="Full preview"
        class="absolute bottom-2.5 right-3 inline-flex items-center gap-1.5 rounded-lg bg-black/50 px-2.5 py-1.5 text-xs font-medium text-white shadow-sm backdrop-blur-sm transition hover:bg-black/70">
        <i class="fas fa-expand text-[10px]"></i> Full preview
    </button>
</div>
<div class="border-b border-gray-100 px-6 py-5 dark:border-white/[0.06]"
     :class="kind !== 'project' ? 'bg-gradient-to-r from-indigo-50/60 to-transparent dark:from-indigo-500/[0.06]' : ''">
    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
        <div class="flex min-w-0 flex-wrap items-center gap-2.5">
            <span x-show="kind !== 'project'" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm shadow-indigo-600/30">
                <i class="fas text-xs" :class="kind === 'subtask' ? 'fa-list-check' : 'fa-diagram-project'"></i>
            </span>
            <h1 id="detailProjectName" class="truncate font-semibold leading-tight text-gray-800 dark:text-gray-100"
                :class="kind !== 'project' ? 'text-xl' : 'text-[26px]'"
                :style="kind === 'project' ? 'font-family:Fraunces,serif;' : ''"></h1>
            <span id="detailProjectStatusPill" class="hidden shrink-0 rounded-full px-2.5 py-1 text-xs font-bold"></span>
            {{-- Independent of the status pill above (that's the Kanban
                 column, e.g. To Do/Done) — this reflects the task's own
                 cancelled flag (openTaskEntityDetail()'s cancel toggle). --}}
            <span id="detailProjectCancelledBadge" class="hidden shrink-0 rounded-full bg-gray-200 px-2.5 py-1 text-xs font-bold text-gray-600 dark:bg-white/10 dark:text-gray-300">Cancelled</span>
            <span id="detailLockedBadge" class="hidden shrink-0 items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                <i class="fas fa-lock text-[10px]"></i> Locked
            </span>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <button id="detailBackBtn" type="button" title="Back to parent task"
                class="hidden h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200">
                <i class="fas fa-arrow-left text-xs"></i>
            </button>
            {{-- Task cover — add straight away when there's none, otherwise
                 a small Replace/Remove menu. Wired in projects.blade.php
                 (renderTaskCover()); saved via Traits\ManagesTaskCover. --}}
            <div id="detailCoverWrap" class="relative hidden">
                <button id="detailCoverBtn" type="button" title="Task cover"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 disabled:opacity-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300">
                    <i class="fas fa-image text-[10px]"></i> <span>Cover</span>
                </button>
                <div id="detailCoverMenu" class="absolute right-0 top-full z-20 mt-1 hidden w-40 overflow-hidden rounded-lg border border-gray-200 bg-white py-1 text-sm shadow-lg dark:border-white/10 dark:bg-[#1e293b]">
                    <button type="button" id="detailCoverReplace" class="flex w-full items-center gap-2 px-3 py-2 text-left text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">
                        <i class="fas fa-arrows-rotate w-3 text-[11px] text-gray-400"></i> Replace
                    </button>
                    <button type="button" id="detailCoverRemove" class="flex w-full items-center gap-2 px-3 py-2 text-left text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                        <i class="fas fa-trash w-3 text-[11px]"></i> Remove
                    </button>
                </div>
                <input id="detailCoverInput" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="hidden">
            </div>
            {{-- Tasks/Subtasks only — opens #taskMoveModal (projects.blade.php,
                 openTaskMoveModal()); saved by PmTaskMoveController. --}}
            <button id="detailMoveBtn" type="button" title="Move to another Team or Project"
                class="hidden h-9 items-center gap-1.5 rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 dark:border-white/10 dark:text-gray-300 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300">
                <i class="fas fa-arrow-right-arrow-left text-[10px]"></i> Move
            </button>
            {{-- Project Tasks only (doctype TSK) — shown/labelled by
                 openTaskEntityDetail(); toggled via PmTaskController::toggleLock(). --}}
            <button id="detailLockBtn" type="button" title="Lock task — only assignees can open it"
                class="hidden h-9 items-center gap-1.5 rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700 dark:border-white/10 dark:text-gray-300 dark:hover:bg-amber-900/20 dark:hover:text-amber-300">
                <i class="fas fa-lock-open text-[10px]"></i> <span>Lock</span>
            </button>
            <button id="projectDetailEditBtn" type="button" x-show="kind === 'project' ? canCreateProject : true"
                class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 dark:border-white/10 dark:text-gray-300 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300">
                <i class="fas fa-pen text-[10px]"></i> Edit
            </button>
            <button id="closeProjectDetailModal" type="button" class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 dark:hover:bg-white/10"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div class="mt-2.5 flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm text-gray-500 dark:text-gray-400">
            <span class="inline-flex items-center gap-1.5">
                <i class="fas fa-calendar-day text-xs text-gray-400"></i>
                <span id="detailProjectDates"></span>
            </span>
            <div class="flex items-center gap-1.5">
                <div id="detailProjectPics" class="flex items-center"></div>
                {{-- Tasks/Subtasks only — adds people without opening Edit.
                     Wired in projects.blade.php (renderAddPeoplePicker()). --}}
                <div id="detailAddPeopleWrap" class="relative hidden">
                    <button id="detailAddPeopleBtn" type="button" title="Add people to this task"
                        class="flex h-6 w-6 items-center justify-center rounded-full border border-dashed border-gray-300 text-gray-400 transition hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 dark:border-white/20 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300">
                        <i class="fas fa-plus text-[9px]"></i>
                    </button>
                    <div id="detailAddPeopleMenu" class="absolute left-0 top-full z-30 mt-1.5 hidden w-72 rounded-xl border border-gray-200 bg-white shadow-xl dark:border-white/10 dark:bg-[#1e293b]">
                        <div class="border-b border-gray-100 p-2 dark:border-white/[0.06]">
                            <input id="detailAddPeopleSearch" type="text" placeholder="Search people…" autocomplete="off"
                                class="w-full rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-sm text-gray-700 focus:border-indigo-400 focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                        </div>
                        <div id="detailAddPeopleList" class="max-h-60 overflow-y-auto py-1"></div>
                        <div class="flex items-center justify-between border-t border-gray-100 px-3 py-2 dark:border-white/[0.06]">
                            <span id="detailAddPeopleCount" class="text-xs text-gray-400">None selected</span>
                            <button id="detailAddPeopleSave" type="button" disabled
                                class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-40">Add</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <span id="detailProjectSubtaskSummary" class="whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">0 / 0 subtasks &middot; 0%</span>
            <div class="h-1.5 w-32 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                <div id="detailProjectProgressBar" class="h-1.5 rounded-full bg-indigo-500 transition-all duration-300" style="width:0%"></div>
            </div>
        </div>
    </div>
</div>
