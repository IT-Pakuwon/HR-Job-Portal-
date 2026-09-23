{{-- Project Detail Modal — Overview tab --}}
<div x-show="tab === 'overview'" class="space-y-6">
    <div>
        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Description</p>
        <p id="detailProjectDescription" class="mt-1.5 text-sm leading-relaxed text-gray-600 dark:text-gray-300">—</p>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Team</p>
            <p id="detailProjectTeam" class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-100">—</p>
        </div>
        <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Status</p>
            <p id="detailProjectStatusMeta" class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-100">—</p>
        </div>
        <div class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Due date</p>
            <p id="detailProjectDueDate" class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-100">—</p>
        </div>
    </div>

    <div>
        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-gray-400">Tags</p>
        <div id="detailProjectTags" class="flex flex-wrap gap-1.5"></div>
    </div>

    <div>
        <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-gray-400">PIC</p>
        <div id="detailProjectPicList" class="space-y-2"></div>
    </div>

    <div>
        <div class="flex items-center justify-between">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Linked Projects</p>
            <button @click="openLinkModal()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                <i class="fas fa-link text-[10px]"></i> Link a project
            </button>
        </div>
        <div id="detailLinkedProjects" class="mt-2.5 flex flex-wrap gap-2"></div>
    </div>
</div>
