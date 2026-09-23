{{-- Project Detail Modal — Sub Task tab (internally still `tab === 'tasks'`;
     each row is a TrProjectTask, shown as a flat checklist matching the
     approved design: "X of Y completed" + Add subtask, checkbox rows below.
     #taskKanbanPanel/#taskGanttPanel stay in the DOM (hidden) since
     renderKanban()/renderGantt() in pmProjectShow() still target them if a
     board/timeline view is ever wired back in. --}}
<div x-show="tab === 'tasks'">
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
