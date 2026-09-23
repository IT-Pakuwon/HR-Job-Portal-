<header
    class="sticky top-0 z-30 flex h-14 items-center gap-2 border-b border-gray-200 bg-white px-2 sm:gap-3 sm:px-4 dark:border-gray-700 dark:bg-gray-800">

    <!-- SIDEBAR BUTTON -->
    <button @click="sidebarOpen = true"
        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
        <span class="sr-only">Open menu</span>
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Global document search (jump to a document by ID or name) -->
    <div class="min-w-0 flex-1 sm:w-72 sm:flex-none">
        <x-app.global-search />
    </div>

    <div class="ml-auto flex shrink-0 items-center gap-1 sm:gap-3">
        <!-- Document status notifications (revised / rejected) -->
        <x-app.document-notifications />

        <a href="{{ route('manual', ['root' => 'faq']) }}"
            class="relative hidden rounded-lg p-2 text-gray-600 hover:bg-gray-100 sm:block dark:text-gray-300 dark:hover:bg-gray-700"
            title="Manual Book"
            x-data="{ seen: localStorage.getItem('manualBookSeen') === 'true' }"
            @click="seen = true; localStorage.setItem('manualBookSeen', 'true')">
            <i class="fas fa-book text-lg"></i>
            <span x-show="!seen" class="absolute right-1 top-1 flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-indigo-500 ring-2 ring-white dark:ring-gray-800"></span>
            </span>
        </a>
        <!-- Dark mode toggle -->
        <div class="relative z-[50]">
            <x-theme-toggle />
        </div>

        <hr class="hidden h-6 w-px border-none bg-gray-200 sm:block dark:bg-gray-700/60" />

        <x-dropdown-profile align="right" />

    </div>
</header>
