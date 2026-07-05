@if ($akses_cc ?? false)
    {{-- Floating Private Note widget (Cost Control only). Reused on document pages and dashboards. --}}
    <div id="privateNoteWidget" class="fixed bottom-6 right-6 z-50" data-doctype="{{ $doctype ?? 'CS' }}"
        data-refnbr="{{ $refnbr ?? '' }}">
        <div id="privateNotePanel"
            class="mb-3 hidden h-[420px] w-80 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between bg-gradient-to-r from-gray-600 to-gray-700 px-4 py-3">
                <div class="flex min-w-0 items-center gap-2 text-white">
                    <span>🗒️</span>
                    <span class="truncate text-sm font-semibold">Private Note<span id="privateNoteTarget"></span></span>
                </div>
                <button id="privateNoteCloseBtn" type="button" class="text-white/90 transition hover:text-white">
                    ✕
                </button>
            </div>
            <p class="border-b border-gray-200 bg-gray-100 px-4 py-1.5 text-[11px] italic text-gray-600 dark:border-gray-700 dark:bg-gray-700/50 dark:text-gray-300">
                Only visible to Cost Control.
            </p>
            <div id="privateNoteList" class="custom-scrollbar flex-1 space-y-3 overflow-y-auto p-3">
                <p class="py-4 text-center text-sm italic text-gray-500">Select a document to view notes.</p>
            </div>
            <div class="flex items-center gap-2 border-t border-gray-200 p-3 dark:border-gray-700">
                <input id="privateNoteInput" type="text" placeholder="Write a private note..."
                    class="flex-1 rounded-full bg-gray-100 px-4 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:bg-gray-700 dark:text-white dark:focus:ring-gray-400">
                <button id="postPrivateNoteBtn" type="button"
                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gray-600 text-white shadow transition hover:bg-gray-700 active:scale-95">
                    ➤
                </button>
            </div>
        </div>

        @if ($floatingButton ?? true)
            <button id="privateNoteToggleBtn" type="button"
                class="relative flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-gray-600 to-gray-700 text-2xl text-white shadow-lg transition hover:scale-105 hover:shadow-xl active:scale-95 {{ empty($refnbr) ? 'hidden' : '' }}">
                <span id="privateNoteToggleIcon">🗒️</span>
                <span id="privateNoteToggleBadge"
                    class="absolute -top-1 -right-1 hidden min-w-[20px] rounded-full bg-red-500 px-1.5 text-xs font-bold leading-5 text-white"></span>
            </button>
        @endif
    </div>

    <script src="{{ asset('assets/js/private-note-widget.js') }}?v={{ filemtime(public_path('assets/js/private-note-widget.js')) }}"></script>
@endif
