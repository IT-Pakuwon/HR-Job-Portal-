{{-- Shared Detail Modal — Overview tab (Project or Task/Team-Task; Team and
     Linked Projects only apply to a Project — see `kind` in the header).
     One compact card: a strip of facts, then the description. Status isn't
     repeated here: the header's pill already shows it.
     In full screen, and for kind 'subtask', it shows alongside the other
     panels under its own heading — see shows() in projects.blade.php. --}}
<style>
    /* Rich-text (Quill) descriptions lose their list and link styles otherwise. */
    #detailProjectDescription ol { list-style: decimal; padding-left: 1.25rem; }
    #detailProjectDescription ul,
    #detailProjectDescription li[data-list="bullet"] { list-style-type: disc; }
    #detailProjectDescription ul { padding-left: 1.25rem; }
    #detailProjectDescription a { color: #0284c7; text-decoration: underline; }
</style>
<div x-show="shows('overview')">
    <div x-show="fullscreen || kind === 'subtask'" class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-indigo-500 dark:text-indigo-300">
        <i class="fas fa-circle-info text-[10px]"></i> Overview
    </div>

    <div class="rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-white/[0.03]">
        {{-- Facts wrap onto more lines only when the panel is narrow. --}}
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 border-b border-gray-100 px-4 py-2.5 dark:border-white/[0.06]">
            <div x-show="kind === 'project'" class="flex min-w-0 items-center gap-2">
                <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-users text-[10px]"></i> Team</span>
                <span id="detailProjectTeam" class="break-words text-xs font-semibold text-gray-700 dark:text-gray-200">—</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-calendar-day text-[10px]"></i> Due</span>
                <span id="detailProjectDueDate" class="text-xs font-semibold text-gray-700 dark:text-gray-200">—</span>
                <span id="detailProjectDueHint" class="hidden"></span>
            </div>
            <div class="flex min-w-0 flex-wrap items-center gap-2">
                <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-tags text-[10px]"></i> Tags</span>
                <div id="detailProjectTags" class="flex min-w-0 flex-wrap items-center gap-1.5"></div>
            </div>
        </div>

        {{-- PIC can be a long list, so it has its own row; renderOverviewPics()
             folds the extras behind a "+N more" button. --}}
        <div class="flex items-start gap-2 border-b border-gray-100 px-4 py-2.5 dark:border-white/[0.06]">
            <span class="flex h-6 shrink-0 items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-user-group text-[10px]"></i> PIC</span>
            <div id="detailProjectPicList" class="flex min-h-[1.5rem] min-w-0 flex-1 flex-wrap items-center gap-1.5"></div>
        </div>

        <div id="detailProjectDescription" class="break-words px-4 py-3 text-sm leading-relaxed text-gray-600 dark:text-gray-300"
            :class="kind === 'project' ? 'whitespace-pre-line' : ''">—</div>

        <div x-show="kind === 'project'" class="flex flex-wrap items-center gap-2 border-t border-gray-100 px-4 py-2.5 dark:border-white/[0.06]">
            <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-400"><i class="fas fa-diagram-project text-[10px]"></i> Linked Projects</span>
            <div id="detailLinkedProjects" class="flex flex-wrap items-center gap-2"></div>
            <button x-show="canCreateProject" @click="openLinkModal()" class="ml-auto inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                <i class="fas fa-link text-[10px]"></i> Link a project
            </button>
        </div>
    </div>
</div>
