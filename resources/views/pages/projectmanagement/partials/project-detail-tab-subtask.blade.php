{{-- Shared Detail Modal — Sub Task tab (internally still `tab === 'tasks'`).
     For a Project (kind === 'project'): a flat checklist of its own
     top-level Tasks. For a Task/Team-Task or one of its own Subtasks (kind
     'task'/'subtask'): that entity's own immediate children, drillable to
     any depth (see the .subtask-row click handler in projects.blade.php).
     Either way: "X of Y completed" +
     Add subtask, checkbox rows below — see renderTaskTab()/openNewTask() in
     pmProjectShow(). #taskKanbanPanel/#taskGanttPanel stay in the DOM
     (hidden) since renderKanban()/renderGantt() in pmProjectShow() still
     target them if a board/timeline view is ever wired back in (Project
     mode only). For kind 'subtask' this renders unconditionally, side-by-side
     with the Overview panel (see projects.blade.php's grid), instead of
     tab-gated. --}}
<div x-show="kind === 'subtask' || tab === 'tasks'">
    <div x-show="kind === 'subtask'" class="mb-4 flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-indigo-500 dark:text-indigo-300">
        <i class="fas fa-list-check text-[10px]"></i> Sub Tasks
    </div>

    <div class="mb-4 flex items-center justify-between">
        <p id="taskListSummary" class="text-sm text-gray-500 dark:text-gray-400">0 of 0 subtasks completed</p>
        <button @click="openNewTask()" class="inline-flex h-9 items-center gap-1.5 justify-center rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500">
            <i class="fas fa-plus text-xs"></i> Add subtask
        </button>
    </div>
    <div id="taskListPanel" class="space-y-2"></div>
    <div id="taskKanbanPanel" class="hidden overflow-x-auto"></div>
    <div id="taskGanttPanel" class="hidden"></div>
</div>
