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
     mode only). In full screen, and for kind 'subtask', it shows alongside
     the other panels — see shows() in projects.blade.php. --}}
<div x-show="shows('tasks')">
    {{-- Viewing a Subtask: its children are Checklist items (name + checkbox
         only) — renderTaskTab() relabels this heading and swaps the "Add
         subtask" button for an inline add box. Task → Subtask → Checklist is
         the deepest the tree goes. --}}
    <div x-show="fullscreen || kind === 'subtask'" class="mb-4flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-indigo-500 dark:text-indigo-300">
        <i class="fas fa-list-check text-[10px]"></i> <span id="subtaskTabHeading">Sub Tasks</span>
    </div>

    <div class="mb-4 flex items-center justify-between">
        <p id="taskListSummary" class="text-sm text-gray-500 dark:text-gray-400">0 of 0 subtasks completed</p>
        <button id="addSubtaskBtn" @click="openNewTask()" class="inline-flex h-9 items-center gap-1.5 justify-center rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500">
            <i class="fas fa-plus text-xs"></i> Add subtask
        </button>
    </div>
    <div id="taskListPanel" class="space-y-2"></div>
    <div id="taskKanbanPanel" class="hidden overflow-x-auto"></div>
    <div id="taskGanttPanel" class="hidden"></div>
</div>
