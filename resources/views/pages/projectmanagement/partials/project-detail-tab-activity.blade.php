{{-- Task Detail — Activity List: everything that happened to this task and
     its subtasks (edits, status moves, chat, files), newest first with the
     exact date/time. Its own tab for a top-level task; for a drilled-in
     subtask (no tab bar) it sits below Overview/Sub Task. Filled by
     loadTaskActivity() in projects.blade.php. --}}
<div x-show="kind !== 'project' && (kind === 'subtask' || tab === 'activity')">
    <div class="mb-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <h3 class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                <i class="fas fa-clock-rotate-left text-[10px]"></i> Activity List
            </h3>
            <span id="taskActivityCount" class="text-[11px] text-gray-400"></span>
        </div>
        <button type="button" onclick="loadTaskActivity()" title="Refresh"
            class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200">
            <i class="fas fa-rotate-right text-[11px]"></i>
        </button>
    </div>
    <div id="taskActivityList" class="custom-scrollbar max-h-[28rem] overflow-y-auto pr-1"></div>
</div>
