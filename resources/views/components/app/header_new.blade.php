<header
    class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-gray-200 bg-white px-4 dark:border-gray-700 dark:bg-gray-800">

    <!-- SIDEBAR BUTTON -->
    <button @click="sidebarOpen = true"
        class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
        <span class="sr-only">Open menu</span>
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="ml-auto flex items-center gap-3">

        <a href="{{ route('manual') }}"
            class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
            title="Manual Book">
            <i class="fas fa-book text-lg"></i>
        </a>
        <!-- Dark mode toggle -->
        <div class="relative z-[50]">
            <x-theme-toggle />
        </div>

        <hr class="h-6 w-px border-none bg-gray-200 dark:bg-gray-700/60" />

        <x-dropdown-profile align="right" />

    </div>
</header>
