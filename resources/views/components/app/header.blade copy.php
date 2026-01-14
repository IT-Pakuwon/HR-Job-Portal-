<header
    class="{{ $variant === 'v2' || $variant === 'v3' ? 'before:bg-gray-100/50 after:absolute after:h-px after:inset-x-0 after:top-full after:bg-gray-200 dark:after:bg-gray-700/60 after:-z-10' : 'max-lg:shadow-xs lg:before:bg-gray-100/50 dark:lg:before:bg-gray-900/90' }} {{ $variant === 'v2' ? 'dark:before:bg-gray-800' : '' }} {{ $variant === 'v3' ? 'dark:before:bg-gray-900' : '' }} sticky top-0 z-30 before:absolute before:inset-0 before:-z-10 before:backdrop-blur-md max-lg:before:bg-white/90 dark:max-lg:before:bg-gray-800/90">
    <div class="px-4 sm:px-6 lg:px-8">
        <div
            class="{{ $variant === 'v2' || $variant === 'v3' ? '' : 'lg:border-b border-gray-200 dark:border-gray-700/60' }} flex h-16 items-center justify-between">

            <!-- Header: Left side -->
            <div class="flex">

                <!-- Hamburger button -->
                <button class="text-gray-500 hover:text-gray-600 lg:hidden dark:hover:text-gray-400"
                    @click.stop="sidebarOpen = !sidebarOpen" aria-controls="sidebar" :aria-expanded="sidebarOpen">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="5" width="16" height="2" />
                        <rect x="4" y="11" width="16" height="2" />
                        <rect x="4" y="17" width="16" height="2" />
                    </svg>
                </button>

            </div>

            <!-- Header: Right side -->
            <!-- Header: Right side -->
            <div class="flex items-center space-x-3">

                <!-- Notifications (Small devices only) -->
                <div x-data="{
                    open: false,
                    notifications: [
                        { id: 1, title: 'PO #2304', status: 'waiting', icon: '🛒', createdAt: '2025-12-18' },
                        { id: 2, title: 'PO #2298', status: 'revised', icon: '🛒', createdAt: '2025-12-15' },
                        { id: 3, title: 'SPP Barang #118', status: 'waiting', icon: '📝', createdAt: '2025-12-17' }
                    ],
                    openNotification(id) {
                        this.notifications = this.notifications.filter(n => n.id !== id)
                    },
                    formatDate(date) {
                        return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: '2-digit' });
                    }
                }" class="relative flex md:hidden">
                    <button @click="open = !open"
                        class="relative rounded-full p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                        🛎️
                        <span x-show="notifications.length > 0"
                            class="absolute -right-1 -top-1 h-3 w-3 rounded-full bg-red-500"></span>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" @click.away="open = false"
                        class= "absolute top-full z-10 -mr-48 mt-1 min-w-80 origin-top-right overflow-hidden rounded-lg border border-gray-200 bg-white py-1.5 sm:mr-0 dark:border-gray-700/60 dark:bg-gray-800"
                        @click.outside="open = false" @keydown.escape.window="open = false" x-show="open"
                        x-transition:enter="transition ease-out duration-200 transform"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-out duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" x-cloak>
                        <template x-for="item in notifications" :key="item.id">
                            <a href="#" @click.prevent="openNotification(item.id)"
                                class="flex items-start gap-2 rounded-lg p-3 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <div x-text="item.icon" class="text-sm"></div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800 dark:text-gray-100" x-text="item.title"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-300"
                                        x-text="item.status === 'waiting' ? 'Waiting approval' : 'Revision needed'"></p>
                                </div>
                                <span class="text-[10px] text-gray-400" x-text="formatDate(item.createdAt)"></span>
                            </a>
                        </template>

                        <template x-if="notifications.length === 0">
                            <div class="p-3 text-center text-xs text-gray-400 dark:text-gray-300">No pending
                                notifications</div>
                        </template>
                    </div>
                </div>

                <!-- Dark mode toggle -->
                <x-theme-toggle />

                <!-- Divider -->
                <hr class="h-6 w-px border-none bg-gray-200 dark:bg-gray-700/60" />

                <!-- User button -->
                <x-dropdown-profile align="right" />

            </div>


        </div>
    </div>
</header>
