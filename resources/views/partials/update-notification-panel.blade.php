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
