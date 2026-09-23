{{-- Project Detail Modal — header: id, title, status pill, dates, PIC
     avatars and subtask progress. Populated by openProjectDetail() in
     projects.blade.php. --}}
<div class="border-b border-gray-100 px-6 py-5 dark:border-white/[0.06]">
    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
        <div class="flex min-w-0 flex-wrap items-center gap-2.5">
            <h1 id="detailProjectName" class="truncate text-[26px] font-semibold leading-tight text-gray-800 dark:text-gray-100" style="font-family:'Fraunces',serif;"></h1>
            <span id="detailProjectStatusPill" class="hidden shrink-0 rounded-full px-2.5 py-1 text-xs font-bold"></span>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <button id="projectDetailEditBtn" type="button" x-show="canCreateProject"
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
            <div id="detailProjectPics" class="flex items-center"></div>
        </div>

        <div class="flex items-center gap-2.5">
            <span id="detailProjectSubtaskSummary" class="whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">0 / 0 subtasks &middot; 0%</span>
            <div class="h-1.5 w-32 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                <div id="detailProjectProgressBar" class="h-1.5 rounded-full bg-indigo-500 transition-all duration-300" style="width:0%"></div>
            </div>
        </div>
    </div>
</div>
