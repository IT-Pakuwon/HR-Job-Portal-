<div id="updateNotificationToast"
    class="fixed top-20 right-4 z-9999 hidden w-80 -translate-y-4 opacity-0 rounded-xl border shadow-2xl transition-all duration-300">
    <div class="flex items-start gap-3 p-3">
        <div id="updateNotificationToastIcon" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-base"></div>
        <div class="min-w-0 flex-1">
            <p id="updateNotificationToastTitle" class="text-sm font-semibold text-slate-900 dark:text-white"></p>
            <p id="updateNotificationToastDesc" class="mt-0.5 line-clamp-2 text-xs text-slate-600 dark:text-slate-300"></p>
            <button id="updateNotificationToastSeeMore" type="button"
                class="mt-1.5 text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                See more
            </button>
        </div>
        <button id="updateNotificationToastClose" type="button"
            class="shrink-0 text-slate-400 transition hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-200">
            ✕
        </button>
    </div>
</div>

<div id="updateNotificationOverlay"
    class="fixed inset-0 z-40 hidden bg-black/30 transition-opacity"></div>

<div id="updateNotificationPanel"
    class="fixed right-0 top-0 z-50 flex h-full w-full max-w-sm translate-x-full flex-col border-l border-slate-200 bg-white shadow-2xl transition-transform duration-300 dark:border-slate-700 dark:bg-slate-800">

    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-900/40">
        <div class="flex items-center gap-2 text-slate-900 dark:text-white">
            <span>📢</span>
            <span class="text-sm font-semibold">Update Notification</span>
        </div>
        <button id="updateNotificationCloseBtn" type="button"
            class="text-slate-400 transition hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-200">
            ✕
        </button>
    </div>

    <div id="updateNotificationList" class="custom-scrollbar flex-1 space-y-3 overflow-y-auto p-3">
        <p class="py-4 text-center text-sm italic text-slate-400">Loading updates...</p>
    </div>

    @if($isItStaff ?? false)
        <div class="space-y-2 border-t border-slate-200 p-3 dark:border-slate-700">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Notification Type</p>

            <div id="updateNotificationTypePicker" class="space-y-1.5">
                <button type="button" data-type="new_feature"
                    class="update-type-option flex w-full items-center gap-3 rounded-lg border border-slate-200 p-2 text-left transition hover:border-green-300 dark:border-slate-600">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-100 text-base text-green-600 dark:bg-green-900/30 dark:text-green-400">✨</div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-900 dark:text-white">New Feature</p>
                        <p class="truncate text-[10px] text-slate-500 dark:text-slate-400">Introducing exciting new features</p>
                    </div>
                </button>
                <button type="button" data-type="maintenance"
                    class="update-type-option flex w-full items-center gap-3 rounded-lg border border-slate-200 p-2 text-left transition hover:border-orange-300 dark:border-slate-600">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-base text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">🔧</div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-900 dark:text-white">Maintenance</p>
                        <p class="truncate text-[10px] text-slate-500 dark:text-slate-400">Scheduled maintenance updates</p>
                    </div>
                </button>
                <button type="button" data-type="information"
                    class="update-type-option flex w-full items-center gap-3 rounded-lg border border-slate-200 p-2 text-left transition hover:border-blue-300 dark:border-slate-600">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-base text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">ℹ️</div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-900 dark:text-white">Information</p>
                        <p class="truncate text-[10px] text-slate-500 dark:text-slate-400">General information updates</p>
                    </div>
                </button>
            </div>

            <div class="flex items-center gap-1">
                <button type="button" id="updateNotificationBoldBtn" title="Bold"
                    class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                    B
                </button>
                <button type="button" id="updateNotificationItalicBtn" title="Italic"
                    class="rounded-md border border-slate-300 px-2.5 py-1 text-xs italic text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                    I
                </button>
            </div>
            <textarea id="updateNotificationDescInput" rows="2" placeholder="What changed?"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white"></textarea>
            <button id="updateNotificationSubmitBtn" type="button"
                class="w-full rounded-lg bg-black px-3 py-2 text-xs font-semibold text-white transition hover:bg-gray-900 dark:bg-zinc-700 dark:hover:bg-zinc-600">
                Post Update
            </button>
        </div>
    @endif

</div>
