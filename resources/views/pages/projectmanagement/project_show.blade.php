<x-app-layout>
    <div id="pmProjectShowRoot" class="max-w-9xl mx-auto w-full p-2" x-data="pmProjectShow()">

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                <div>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <a href="{{ route('projects.index') }}" class="hover:underline">Projects</a>
                        <span>/</span>
                        <span>{{ $project->project_id }}</span>
                    </div>
                    <h1 class="mt-1 text-xl font-semibold text-gray-800 dark:text-gray-100">{{ $project->project_name }}</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ optional($project->start_date)->format('d M Y') ?? '—' }} →
                        {{ optional($project->end_date)->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div class="w-48">
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Progress</span>
                        <span>{{ round($project->progress_percent) }}%</span>
                    </div>
                    <div class="mt-1 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                        <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $project->progress_percent }}%"></div>
                    </div>
                </div>
            </div>

            {{-- TABS --}}
            <div class="flex items-center gap-1 border-b border-gray-100 px-5 pt-2 dark:border-white/[0.06]">
                <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Overview</button>
                <button @click="tab = 'tasks'; renderTaskTab()" :class="tab === 'tasks' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Tasks</button>
                <button @click="tab = 'attachments'" :class="tab === 'attachments' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Attachments</button>
                <button @click="tab = 'chat'" :class="tab === 'chat' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800'" class="rounded-t-lg px-4 py-2 text-sm font-medium">Chat</button>
            </div>

            <div class="p-5">

                {{-- OVERVIEW --}}
                <div x-show="tab === 'overview'" class="space-y-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Description</p>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $project->project_description ?: '—' }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Linked Projects</p>
                            <button @click="openLinkModal()" class="text-xs font-medium text-indigo-600 hover:underline">+ Link a project</button>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($linkedProjects as $lp)
                                <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs dark:border-gray-700 dark:bg-gray-800">
                                    <a href="{{ route('projects.show', $lp->project_id) }}" class="text-indigo-600 hover:underline">{{ $lp->project_name }}</a>
                                    <button @click="unlinkProject('{{ $lp->project_id }}')" class="text-gray-400 hover:text-red-500">&times;</button>
                                </span>
                            @empty
                                <span class="text-sm text-gray-400">No linked projects.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- TASKS --}}
                <div x-show="tab === 'tasks'">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            <button @click="taskView = 'kanban'; renderTaskTab()" :class="taskView === 'kanban' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30' : 'text-gray-500'" class="rounded-lg px-3 py-1.5 text-xs font-medium">Kanban</button>
                            <button @click="taskView = 'gantt'; renderTaskTab()" :class="taskView === 'gantt' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30' : 'text-gray-500'" class="rounded-lg px-3 py-1.5 text-xs font-medium">Gantt</button>
                        </div>
                        <button @click="openNewTask()" class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white hover:bg-indigo-500">+ New Task</button>
                    </div>
                    <div id="taskKanbanPanel" class="overflow-x-auto"></div>
                    <div id="taskGanttPanel" class="hidden"></div>
                </div>

                {{-- ATTACHMENTS --}}
                <div x-show="tab === 'attachments'">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-left text-xs uppercase text-gray-400 dark:border-gray-700">
                                <th class="px-3 py-2">File</th>
                                <th class="px-3 py-2">Uploaded By</th>
                                <th class="px-3 py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody id="projectAttachmentTbody"></tbody>
                    </table>
                    <form id="projectAttachmentUploadForm" enctype="multipart/form-data" class="mt-4 flex items-center gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                        @csrf
                        <input type="file" id="projectAttachFiles" name="attachments[]" multiple accept=".png,.jpg,.jpeg,.pdf,.xlsx,.doc,.docx"
                            class="block flex-1 cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <button type="button" id="btnUploadProjectAttachment" class="inline-flex h-9 items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700">Upload</button>
                    </form>
                    <p class="mt-1 text-xs text-gray-400">Max 5MB per file — png, jpg, jpeg, pdf, xlsx, doc, docx.</p>
                </div>

                {{-- CHAT --}}
                <div x-show="tab === 'chat'">
                    <div id="projectCommentList" class="custom-scrollbar max-h-96 space-y-3 overflow-y-auto"></div>
                    <div class="mt-3 flex items-center gap-3 border-t border-gray-100 pt-3 dark:border-gray-700">
                        <input id="projectCommentInput" type="text" placeholder="Write a comment... use @ to mention"
                            class="flex-1 rounded-lg bg-gray-100 p-3 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                        <button id="projectPostCommentBtn" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Post</button>
                    </div>
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
                <select id="linkProjectSelect" class="mt-4 h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    @foreach ($linkableProjects as $lp)
                        <option value="{{ $lp->project_id }}">{{ $lp->project_name }} ({{ $lp->group_id }})</option>
                    @endforeach
                </select>
                <div class="mt-5 flex justify-end gap-3">
                    <button id="cancelLinkBtn" class="h-10 rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-600 dark:border-slate-600 dark:text-slate-300">Cancel</button>
                    <button id="confirmLinkBtn" class="h-10 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white hover:bg-indigo-500">Link</button>
                </div>
            </div>
        </div>
    </div>

    {{-- TASK MODAL --}}
    <div id="taskModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50"></div>
        <div class="relative flex h-full items-center justify-center p-4">
            <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white shadow-2xl dark:bg-slate-800">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                    <h2 id="taskModalTitle" class="text-xl font-semibold text-slate-900 dark:text-white">New Task</h2>
                    <button id="closeTaskModal" type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fas fa-times"></i></button>
                </div>
                <div class="flex items-center gap-1 border-b border-slate-200 px-6 dark:border-slate-700">
                    <button type="button" class="task-modal-tab-btn border-b-2 border-indigo-600 px-3 py-2 text-sm font-medium text-indigo-600" data-task-tab="details">Details</button>
                    <button type="button" class="task-modal-tab-btn border-b-2 border-transparent px-3 py-2 text-sm font-medium text-slate-500" data-task-tab="attachments">Attachments</button>
                    <button type="button" class="task-modal-tab-btn border-b-2 border-transparent px-3 py-2 text-sm font-medium text-slate-500" data-task-tab="chat">Chat</button>
                </div>

                <form id="taskForm" class="flex min-h-0 flex-1 flex-col">
                    <input type="hidden" id="task_id" name="task_id">

                    <div id="taskTabDetails" class="task-tab-panel min-h-0 flex-1 overflow-y-auto p-6 space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Task Name</label>
                            <input id="task_name" name="task_name" type="text" required class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Description</label>
                            <textarea id="task_description" name="task_description" rows="2" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Start Date</label>
                                <input id="task_start_date" name="start_date" type="date" class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">End Date</label>
                                <input id="task_end_date" name="end_date" type="date" class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Assignees</label>
                            <select id="task_assignees" name="assignees[]" class="select2 w-full" multiple data-placeholder="Assign team members">
                                <option></option>
                                @foreach ($eligibleUsers as $u)
                                    <option value="{{ $u->username }}">{{ $u->name }} ({{ $u->username }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="progressWrap" class="hidden">
                            <label class="mb-2 flex items-center justify-between text-sm font-medium text-slate-700 dark:text-slate-300">
                                <span>Progress</span>
                                <span id="progressValueLabel">0%</span>
                            </label>
                            <input id="task_progress" name="progress_percent" type="range" min="0" max="100" value="0" class="w-full">
                        </div>

                        <div id="subtasksWrap" class="hidden border-t border-slate-200 pt-4 dark:border-slate-700">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Subtasks</p>
                                <button type="button" id="addSubtaskBtn" class="text-xs font-medium text-indigo-600 hover:underline">+ Add subtask</button>
                            </div>
                            <div id="subtaskList" class="mt-2 space-y-2"></div>
                        </div>
                    </div>

                    <div id="taskTabAttachments" class="task-tab-panel hidden min-h-0 flex-1 overflow-y-auto p-6">
                        <p id="taskAttachmentsUnsaved" class="text-sm italic text-slate-400">Save the task first to add attachments.</p>
                        <div id="taskAttachmentsBody" class="hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-left text-xs uppercase text-gray-400 dark:border-gray-700">
                                        <th class="px-2 py-2">File</th>
                                        <th class="px-2 py-2">By</th>
                                        <th class="px-2 py-2">Date</th>
                                    </tr>
                                </thead>
                                <tbody id="taskAttachmentTbody"></tbody>
                            </table>
                            <div class="mt-3 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-700">
                                <input type="file" id="taskAttachFiles" multiple accept=".png,.jpg,.jpeg,.pdf,.xlsx,.doc,.docx"
                                    class="block flex-1 cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <button type="button" id="btnUploadTaskAttachment" class="inline-flex h-9 items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700">Upload</button>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Max 5MB per file — png, jpg, jpeg, pdf, xlsx, doc, docx.</p>
                        </div>
                    </div>

                    <div id="taskTabChat" class="task-tab-panel hidden min-h-0 flex-1 flex-col overflow-y-auto p-6">
                        <p id="taskChatUnsaved" class="text-sm italic text-slate-400">Save the task first to start chatting.</p>
                        <div id="taskChatBody" class="hidden flex h-full flex-col">
                            <div id="taskCommentList" class="custom-scrollbar flex-1 space-y-3 overflow-y-auto"></div>
                            <div class="mt-3 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-700">
                                <input id="taskCommentInput" type="text" placeholder="Write a comment... use @ to mention"
                                    class="flex-1 rounded-lg bg-gray-100 p-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                                <button type="button" id="taskPostCommentBtn" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Post</button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                        <button type="button" id="deleteTaskBtn" class="hidden h-10 rounded-lg border border-red-200 px-4 text-sm font-medium text-red-600 hover:bg-red-50">Archive</button>
                        <div class="ml-auto flex gap-3">
                            <button type="button" id="cancelTaskBtn" class="h-10 rounded-lg border border-slate-300 px-4 text-sm font-medium text-slate-600 dark:border-slate-600 dark:text-slate-300">Cancel</button>
                            <button type="submit" class="h-10 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white hover:bg-indigo-500">Save Task</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('assets/js/shared/mention-autocomplete.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const PM_PROJECT_ID = @json($project->project_id);

        function pmProjectShow() {
            return {
                tab: 'overview',
                taskView: 'kanban',
                statuses: [],
                tasks: [],

                openNewTask() {
                    resetTaskForm();
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
                        success: () => window.location.reload(),
                    });
                },

                renderTaskTab() {
                    $('#taskKanbanPanel, #taskGanttPanel').addClass('hidden');
                    $.get(`{{ url('projects') }}/${PM_PROJECT_ID}/tasks/board-data`, (res) => {
                        this.statuses = res.statuses;
                        this.tasks = res.tasks;
                        if (this.taskView === 'kanban') { $('#taskKanbanPanel').removeClass('hidden'); this.renderKanban(); }
                        else { $('#taskGanttPanel').removeClass('hidden'); this.renderGantt(); }
                    });
                },

                renderKanban() {
                    const panel = $('#taskKanbanPanel').empty();
                    const wrap = $('<div class="flex gap-4 min-w-max pb-2"></div>');

                    this.statuses.forEach(status => {
                        const items = this.tasks.filter(t => t.status_id === status.status_id);
                        const col = $(`
                            <div class="w-72 shrink-0 rounded-lg bg-gray-50 dark:bg-gray-900/40">
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
                    const subCount = t.subtasks?.length || 0;
                    return $(`
                        <div class="task-card cursor-pointer rounded-lg border border-gray-200 bg-white p-3 shadow-sm hover:shadow dark:border-gray-700 dark:bg-gray-800" data-task-id="${t.task_id}">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">${t.task_name}</p>
                            ${subCount ? `<p class="mt-1 text-xs text-gray-400">${subCount} subtask(s)</p>` : ''}
                            <div class="mt-2 h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-1.5 rounded-full bg-indigo-500" style="width:${t.progress_percent}%"></div>
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

        window.pmOpenTask = function (taskId) {
            const task = currentTasksCache.find(t => t.task_id === taskId);
            if (!task) return;
            openTaskModal(task);
        };

        function switchTaskTab(tabName) {
            $('.task-modal-tab-btn').removeClass('border-indigo-600 text-indigo-600').addClass('border-transparent text-slate-500');
            $(`.task-modal-tab-btn[data-task-tab="${tabName}"]`).removeClass('border-transparent text-slate-500').addClass('border-indigo-600 text-indigo-600');
            $('.task-tab-panel').addClass('hidden');
            $(`#taskTab${tabName.charAt(0).toUpperCase()}${tabName.slice(1)}`).removeClass('hidden');

            const taskId = $('#task_id').val();
            if (tabName === 'attachments' && taskId) loadTaskAttachments(taskId);
            if (tabName === 'chat' && taskId) loadTaskComments(taskId);
        }

        function resetTaskForm() {
            $('#taskForm')[0].reset();
            $('#task_id').val('');
            $('#task_assignees').val(null).trigger('change');
            $('#progressWrap, #subtasksWrap, #deleteTaskBtn').addClass('hidden');
            $('#subtaskList').empty();
            $('#taskModalTitle').text('New Task');
            $('#taskAttachmentsBody, #taskChatBody').addClass('hidden');
            $('#taskAttachmentsUnsaved, #taskChatUnsaved').removeClass('hidden');
            switchTaskTab('details');
        }

        function openTaskModal(task) {
            resetTaskForm();
            $('#task_id').val(task.task_id);
            $('#task_name').val(task.task_name);
            $('#task_description').val(task.task_description);
            $('#task_start_date').val(task.start_date);
            $('#task_end_date').val(task.end_date);
            $('#task_assignees').val(task.assignees || []).trigger('change');
            $('#task_progress').val(task.progress_percent);
            $('#progressValueLabel').text(Math.round(task.progress_percent) + '%');
            $('#progressWrap, #subtasksWrap, #deleteTaskBtn').removeClass('hidden');
            $('#taskModalTitle').text('Edit Task — ' + task.task_name);
            $('#taskAttachmentsUnsaved, #taskChatUnsaved').addClass('hidden');
            $('#taskAttachmentsBody, #taskChatBody').removeClass('hidden');
            renderSubtaskList(task);
            $('#taskModal').removeClass('hidden');
        }

        function loadTaskAttachments(taskId) {
            const listUrl = `{{ url('attachments') }}/TSK/${taskId}`;
            $.get(listUrl).done(res => {
                const $tb = $('#taskAttachmentTbody').empty();
                if (!res.success || !res.attachments || !res.attachments.length) {
                    $tb.append('<tr><td colspan="3" class="p-3 text-center italic text-gray-400">No attachments yet.</td></tr>');
                    return;
                }
                res.attachments.forEach(at => {
                    const link = at.url ? `<a href="${at.url}" target="_blank" class="text-indigo-600 hover:underline">📎 ${at.name}</a>` : `<span>📎 ${at.name}</span>`;
                    $tb.append(`<tr class="border-b border-gray-100 dark:border-gray-700"><td class="px-2 py-2">${link}</td><td class="px-2 py-2">${at.created_by || '-'}</td><td class="px-2 py-2">${at.created_at || '-'}</td></tr>`);
                });
            });
        }

        function loadTaskComments(taskId) {
            const $list = $('#taskCommentList').html('<p class="italic text-gray-400 text-sm">Loading comments...</p>');
            $.get(`/comments/TSK/${taskId}`, function (res) {
                $list.empty();
                if (!res.comments || !res.comments.length) {
                    $list.append('<p class="text-sm italic text-gray-400">No comments yet.</p>');
                    return;
                }
                res.comments.forEach(c => {
                    const timeAgo = c.message_date ? dayjs(c.message_date).fromNow() : '';
                    $list.append(`
                        <div class="rounded-lg bg-gray-100 px-3 py-2 dark:bg-gray-800">
                            <p class="text-sm font-semibold">${c.username} <span class="text-xs text-gray-500">(${timeAgo})</span></p>
                            <p class="text-sm text-gray-800 dark:text-gray-200">${highlightMentions(c.message)}</p>
                        </div>
                    `);
                });
            });
        }

        function renderSubtaskList(task) {
            const $list = $('#subtaskList').empty();
            (task.subtasks || []).forEach(s => {
                $list.append(`
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600">
                        <div>
                            <p class="font-medium text-slate-700 dark:text-slate-200">${s.subtask_name}</p>
                            <p class="text-xs text-slate-400">${s.start_date || '—'} → ${s.end_date || '—'} · ${Math.round(s.progress_percent)}%</p>
                        </div>
                        <button type="button" class="delete-subtask-btn text-slate-400 hover:text-red-500" data-subtask-id="${s.task_detail_id}">&times;</button>
                    </div>
                `);
            });
        }

        $(document).ready(function () {
            $('.select2').select2({ width: '100%', allowClear: true, closeOnSelect: false, dropdownParent: $('#taskModal') });

            $('#task_progress').on('input', function () {
                $('#progressValueLabel').text($(this).val() + '%');
            });

            // ── Task modal open/close ──
            $('#closeTaskModal, #cancelTaskBtn').on('click', () => $('#taskModal').addClass('hidden'));

            $('.task-modal-tab-btn').on('click', function () {
                switchTaskTab($(this).data('task-tab'));
            });

            $('#btnUploadTaskAttachment').on('click', function () {
                const taskId = $('#task_id').val();
                const files = $('#taskAttachFiles')[0].files;
                if (!taskId || !files.length) { toastr.warning('Choose at least one file.'); return; }

                const fd = new FormData();
                Array.from(files).forEach(f => fd.append('attachments[]', f));
                fd.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: `{{ url('attachments') }}/TSK/${taskId}`,
                    method: 'POST', data: fd, processData: false, contentType: false,
                    success: function (res) {
                        if (!res.success) { toastr.error(res.message); return; }
                        toastr.success('Uploaded.');
                        $('#taskAttachFiles').val('');
                        loadTaskAttachments(taskId);
                    },
                    error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Upload failed (max 5MB, png/jpg/jpeg/pdf/xlsx/doc/docx).'); }
                });
            });

            attachMentionAutocomplete({
                inputSelector: '#taskCommentInput',
                fetchUrlFn: () => {
                    const taskId = $('#task_id').val();
                    return taskId ? `{{ url('projects') }}/${PM_PROJECT_ID}/tasks/${taskId}/mentionable-users` : null;
                },
            });

            $('#taskPostCommentBtn').on('click', function () {
                const taskId = $('#task_id').val();
                const val = $('#taskCommentInput').val().trim();
                if (!taskId || !val) return;
                $.post(`/comments/TSK/${taskId}`, { comment: val, _token: '{{ csrf_token() }}' }, function () {
                    $('#taskCommentInput').val('');
                    loadTaskComments(taskId);
                });
            });

            $('#taskForm').on('submit', function (e) {
                e.preventDefault();
                const taskId = $('#task_id').val();
                const url = taskId
                    ? `{{ url('projects') }}/${PM_PROJECT_ID}/tasks/${taskId}`
                    : `{{ url('projects') }}/${PM_PROJECT_ID}/tasks`;
                const method = taskId ? 'PUT' : 'POST';

                $.ajax({
                    url, method,
                    data: $(this).serialize() + '&_token={{ csrf_token() }}',
                    success: function (res) {
                        $('#taskModal').addClass('hidden');
                        toastr.success(res.message);
                        Alpine.$data(document.getElementById('pmProjectShowRoot')).renderTaskTab();
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
                    url: `{{ url('projects') }}/${PM_PROJECT_ID}/tasks/${taskId}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        $('#taskModal').addClass('hidden');
                        Alpine.$data(document.getElementById('pmProjectShowRoot')).renderTaskTab();
                    }
                });
            });

            $(document).on('click', '#addTaskStatusBtn', function () {
                Swal.fire({ title: 'Add status column', input: 'text', inputPlaceholder: 'e.g. Blocked', showCancelButton: true })
                    .then((result) => {
                        if (!result.isConfirmed || !result.value) return;
                        $.post(`{{ url('projects') }}/${PM_PROJECT_ID}/tasks/statuses`, { status_name: result.value, _token: '{{ csrf_token() }}' }, function () {
                            Alpine.$data(document.getElementById('pmProjectShowRoot')).renderTaskTab();
                        });
                    });
            });

            $('#addSubtaskBtn').on('click', function () {
                const taskId = $('#task_id').val();
                if (!taskId) { toastr.warning('Save the task first.'); return; }

                Swal.fire({ title: 'New subtask', input: 'text', inputPlaceholder: 'Subtask name', showCancelButton: true })
                    .then((result) => {
                        if (!result.isConfirmed || !result.value) return;
                        $.post(`{{ url('projects') }}/${PM_PROJECT_ID}/tasks/${taskId}/subtasks`, { subtask_name: result.value, _token: '{{ csrf_token() }}' }, function () {
                            $.get(`{{ url('projects') }}/${PM_PROJECT_ID}/tasks/board-data`, function (res) {
                                currentTasksCache = res.tasks;
                                const t = res.tasks.find(t => t.task_id === taskId);
                                if (t) renderSubtaskList(t);
                            });
                        });
                    });
            });

            $(document).on('click', '.delete-subtask-btn', function () {
                const taskId = $('#task_id').val();
                const subtaskId = $(this).data('subtask-id');
                $.ajax({
                    url: `{{ url('projects') }}/${PM_PROJECT_ID}/tasks/${taskId}/subtasks/${subtaskId}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        $.get(`{{ url('projects') }}/${PM_PROJECT_ID}/tasks/board-data`, function (res) {
                            currentTasksCache = res.tasks;
                            const t = res.tasks.find(t => t.task_id === taskId);
                            if (t) renderSubtaskList(t);
                        });
                    }
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
                    window.location.reload();
                });
            });

            // ── Attachments ──
            const listUrl = @json(route('attachments.list', ['doctype' => 'PRJ', 'refnbr' => $project->project_id]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'PRJ', 'refnbr' => $project->project_id]));

            function renderAttachmentRows(rows) {
                const $tb = $('#projectAttachmentTbody').empty();
                if (!rows || !rows.length) {
                    $tb.append('<tr><td colspan="3" class="p-4 text-center italic text-gray-400">No attachments yet.</td></tr>');
                    return;
                }
                rows.forEach(at => {
                    const link = at.url
                        ? `<a href="${at.url}" target="_blank" class="text-indigo-600 hover:underline">📎 ${at.name}</a>`
                        : `<span>📎 ${at.name}</span>`;
                    $tb.append(`<tr class="border-b border-gray-100 dark:border-gray-700"><td class="px-3 py-2">${link}</td><td class="px-3 py-2">${at.created_by || '-'}</td><td class="px-3 py-2">${at.created_at || '-'}</td></tr>`);
                });
            }

            function refreshAttachments() {
                $.get(listUrl).done(res => { if (res.success) renderAttachmentRows(res.attachments); });
            }
            refreshAttachments();

            $('#btnUploadProjectAttachment').on('click', function () {
                const files = $('#projectAttachFiles')[0].files;
                if (!files.length) { toastr.warning('Choose at least one file.'); return; }
                const fd = new FormData($('#projectAttachmentUploadForm')[0]);
                $.ajax({
                    url: uploadUrl, method: 'POST', data: fd, processData: false, contentType: false,
                    success: function (res) {
                        if (!res.success) { toastr.error(res.message); return; }
                        toastr.success('Uploaded.');
                        $('#projectAttachFiles').val('');
                        renderAttachmentRows(res.attachments || []);
                    },
                    error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Upload failed (max 5MB, png/jpg/jpeg/pdf/xlsx/doc/docx).'); }
                });
            });

            // ── Chat ──
            attachMentionAutocomplete({
                inputSelector: '#projectCommentInput',
                fetchUrlFn: () => `{{ url('projects') }}/${PM_PROJECT_ID}/mentionable-users`,
            });

            function loadComments() {
                const $list = $('#projectCommentList').html('<p class="italic text-gray-400">Loading comments...</p>');
                $.get(`/comments/PRJ/${PM_PROJECT_ID}`, function (res) {
                    $list.empty();
                    if (!res.comments || !res.comments.length) {
                        $list.append('<p class="text-sm italic text-gray-400">No comments yet.</p>');
                        return;
                    }
                    res.comments.forEach(c => {
                        const timeAgo = c.message_date ? dayjs(c.message_date).fromNow() : '';
                        $list.append(`
                            <div class="rounded-lg bg-gray-100 px-3 py-2 dark:bg-gray-800">
                                <p class="text-sm font-semibold">${c.username} <span class="text-xs text-gray-500">(${timeAgo})</span></p>
                                <p class="text-sm text-gray-800 dark:text-gray-200">${highlightMentions(c.message)}</p>
                            </div>
                        `);
                    });
                });
            }
            loadComments();

            $('#projectPostCommentBtn').on('click', function () {
                const val = $('#projectCommentInput').val().trim();
                if (!val) return;
                $.post(`/comments/PRJ/${PM_PROJECT_ID}`, { comment: val, _token: '{{ csrf_token() }}' }, function () {
                    $('#projectCommentInput').val('');
                    loadComments();
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
