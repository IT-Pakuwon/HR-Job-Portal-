{{-- Project Detail Modal — Chat tab (project-level comments, @mention aware) --}}
<div x-show="tab === 'chat'" class="flex h-full flex-col">
    <div class="mb-3 flex shrink-0 items-center gap-2.5">
        <div id="chatParticipants" class="flex items-center"></div>
        <span id="chatParticipantsLabel" class="text-xs text-gray-400"></span>
    </div>

    <div id="projectCommentList" class="custom-scrollbar max-h-96 flex-1 space-y-3 overflow-y-auto pr-1"></div>

    <div class="mt-3 flex items-center gap-2.5 border-t border-gray-100 pt-3 dark:border-white/[0.06]">
        <div id="chatSelfAvatar" class="shrink-0"></div>
        <div class="flex flex-1 items-center gap-1 rounded-full border border-gray-200 bg-gray-50 pl-4 pr-1.5 dark:border-white/10 dark:bg-white/[0.04]">
            <input id="projectCommentInput" type="text" placeholder="Write a message… use @ to mention someone"
                class="flex-1 border-none bg-transparent py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-0 dark:text-white">
            <button type="button" id="projectMentionBtn" title="Mention someone" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold text-gray-400 transition hover:bg-white hover:text-indigo-600 dark:hover:bg-white/10 dark:hover:text-indigo-400">@</button>
            <button type="button" id="projectAttachChatBtn" title="Go to Files" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-gray-400 transition hover:bg-white hover:text-indigo-600 dark:hover:bg-white/10 dark:hover:text-indigo-400"><i class="fas fa-paperclip text-xs"></i></button>
        </div>
        <button id="projectPostCommentBtn" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white shadow-sm transition hover:bg-indigo-500"><i class="fas fa-paper-plane text-xs"></i></button>
    </div>
</div>
