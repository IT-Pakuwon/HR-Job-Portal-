@props([
    'align' => 'right',
])

<div x-data="waitingNotification()" x-init="load();
setInterval(load, 60000)" class="relative">

    <!-- 🔔 BUTTON -->
    <button
        class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-gray-100 lg:hover:bg-gray-200 dark:hover:bg-gray-700/50 dark:lg:hover:bg-gray-800"
        :class="{ 'bg-gray-200 dark:bg-gray-800': open }" aria-haspopup="true" @click.prevent="open = !open"
        :aria-expanded="open">
        <span class="sr-only">Notifications</span>
        <svg class="fill-current text-gray-500/80 dark:text-gray-400/80" width="16" height="16" viewBox="0 0 16 16"
            xmlns="http://www.w3.org/2000/svg">
            <path
                d="M7 0a7 7 0 0 0-7 7c0 1.202.308 2.33.84 3.316l-.789 2.368a1 1 0 0 0 1.265 1.265l2.595-.865a1 1 0 0 0-.632-1.898l-.698.233.3-.9a1 1 0 0 0-.104-.85A4.97 4.97 0 0 1 2 7a5 5 0 0 1 5-5 4.99 4.99 0 0 1 4.093 2.135 1 1 0 1 0 1.638-1.148A6.99 6.99 0 0 0 7 0Z" />
            <path
                d="M11 6a5 5 0 0 0 0 10c.807 0 1.567-.194 2.24-.533l1.444.482a1 1 0 0 0 1.265-1.265l-.482-1.444A4.962 4.962 0 0 0 16 11a5 5 0 0 0-5-5Zm-3 5a3 3 0 0 1 6 0c0 .588-.171 1.134-.466 1.6a1 1 0 0 0-.115.82 1 1 0 0 0-.82.114A2.973 2.973 0 0 1 11 14a3 3 0 0 1-3-3Z" />
        </svg>
        <span x-show="count > 0" x-text="count"
            class="absolute -right-1 -top-1 min-w-[18px] rounded-full border-2 border-white bg-red-500 px-1 text-center text-[10px] font-bold text-white dark:border-gray-900">
        </span>

    </button>

    <!-- 🔽 DROPDOWN -->
    <div x-show="open" @click.outside="open = false" x-transition
        class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-xl border bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">

        <!-- HEADER -->
        <div
            class="border-b px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:border-gray-700 dark:text-gray-400">
            Waiting Approval
        </div>

        <!-- LIST -->
        <ul class="max-h-80 overflow-y-auto">

            <!-- EMPTY -->
            <template x-if="items.length === 0">
                <li class="px-4 py-6 text-center text-sm text-gray-400">
                    🎉 No waiting approvals
                </li>
            </template>

            <!-- ITEMS -->
            <template x-for="item in items.slice(0,5)" :key="item.hid">
                <li>
                    <a :href="`${item.url}/${item.hid}`" target="_blank" @click="open = false"
                        class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                            <span x-text="item.docid"></span>
                        </p>

                        <p class="mt-0.5 text-xs text-gray-500">
                            <span x-text="item.cpnyid"></span>
                            •
                            <span x-text="item.docdate"></span>
                        </p>
                    </a>
                </li>
            </template>

        </ul>

        <!-- FOOTER -->
        <div x-show="items.length > 5" class="border-t px-4 py-2 text-center text-xs dark:border-gray-700">
            <button
                @click="
          open = false;
          if (typeof switchTab === 'function') {
            switchTab('waiting');
          }
          document.getElementById('content-waiting')
            ?.scrollIntoView({ behavior: 'smooth' });
        "
                class="font-medium text-violet-600 hover:underline">
                View all waiting approvals →
            </button>
        </div>

    </div>

    <!-- 🔔 TOAST POPUP -->
    <div x-show="showToast" x-transition
        class="fixed right-6 top-16 z-[9999] w-72 rounded-xl border bg-white p-4 shadow-xl dark:border-gray-700 dark:bg-gray-800">

        <p class="text-xs font-semibold uppercase text-violet-600">
            New Approval
        </p>

        <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100" x-text="latestItem?.docid">
        </p>

        <p class="mt-0.5 text-xs text-gray-500">
            Waiting for your approval
        </p>
    </div>

</div>

<script>
    function waitingNotification() {
        return {
            open: false,
            items: [],
            count: 0,

            async load() {
                try {
                    const res = await fetch('/waitingjson');
                    const json = await res.json();

                    this.items = json.data || [];
                    this.count = this.items.length;
                } catch (e) {
                    console.error('Notification load failed', e);
                }
            }
        }
    }
</script>

<script>
    function waitingNotification() {
        return {
            open: false,
            items: [],
            count: 0,
            lastCount: 0,
            showToast: false,
            latestItem: null,

            async load() {
                try {
                    const res = await fetch('/waitingjson');
                    const json = await res.json();
                    const newItems = json.data || [];
                    const newCount = newItems.length;

                    // detect NEW notification (not first load)
                    if (this.lastCount !== 0 && newCount > this.lastCount) {
                        this.latestItem = newItems[0];
                        this.showToast = true;

                        setTimeout(() => {
                            this.showToast = false;
                        }, 4000);
                    }

                    this.items = newItems;
                    this.count = newCount;
                    this.lastCount = newCount;

                } catch (e) {
                    console.error('Notification load failed', e);
                }
            }
        }
    }
</script>

{{-- 
<div class="relative inline-flex" x-data="{ open: false }">
    <button
        class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-gray-100 lg:hover:bg-gray-200 dark:hover:bg-gray-700/50 dark:lg:hover:bg-gray-800"
        :class="{ 'bg-gray-200 dark:bg-gray-800': open }" aria-haspopup="true" @click.prevent="open = !open"
        :aria-expanded="open">
        <span class="sr-only">Notifications</span>
        <svg class="fill-current text-gray-500/80 dark:text-gray-400/80" width="16" height="16"
            viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M7 0a7 7 0 0 0-7 7c0 1.202.308 2.33.84 3.316l-.789 2.368a1 1 0 0 0 1.265 1.265l2.595-.865a1 1 0 0 0-.632-1.898l-.698.233.3-.9a1 1 0 0 0-.104-.85A4.97 4.97 0 0 1 2 7a5 5 0 0 1 5-5 4.99 4.99 0 0 1 4.093 2.135 1 1 0 1 0 1.638-1.148A6.99 6.99 0 0 0 7 0Z" />
            <path
                d="M11 6a5 5 0 0 0 0 10c.807 0 1.567-.194 2.24-.533l1.444.482a1 1 0 0 0 1.265-1.265l-.482-1.444A4.962 4.962 0 0 0 16 11a5 5 0 0 0-5-5Zm-3 5a3 3 0 0 1 6 0c0 .588-.171 1.134-.466 1.6a1 1 0 0 0-.115.82 1 1 0 0 0-.82.114A2.973 2.973 0 0 1 11 14a3 3 0 0 1-3-3Z" />
        </svg>
        <div
            class="absolute right-0 top-0 h-2.5 w-2.5 rounded-full border-2 border-gray-100 bg-red-500 dark:border-gray-900">
        </div>
    </button>
    <div class="{{ $align === 'right' ? 'right-0' : 'left-0' }} absolute top-full z-10 -mr-48 mt-1 min-w-80 origin-top-right overflow-hidden rounded-lg border border-gray-200 bg-white py-1.5 sm:mr-0 dark:border-gray-700/60 dark:bg-gray-800"
        @click.outside="open = false" @keydown.escape.window="open = false" x-show="open"
        x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-out duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" x-cloak>
        <div class="px-4 pb-2 pt-1.5 text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">Notifications
        </div>
        <ul>
            <li class="border-b border-gray-200 last:border-0 dark:border-gray-700/60">
                <a class="block px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/20" href="#0"
                    @click="open = false" @focus="open = true" @focusout="open = false">
                    <span class="mb-2 block text-xs">📣 <span class="font-medium text-gray-800 dark:text-gray-100">Edit
                            your information in a swipe</span> Sint occaecat cupidatat non proident, sunt in culpa qui
                        officia deserunt mollit anim.</span>
                    <span class="block text-xs font-medium text-gray-400 dark:text-gray-500">Feb 12, 2024</span>
                </a>
            </li>
            <li class="border-b border-gray-200 last:border-0 dark:border-gray-700/60">
                <a class="block px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/20" href="#0"
                    @click="open = false" @focus="open = true" @focusout="open = false">
                    <span class="mb-2 block text-xs">📣 <span class="font-medium text-gray-800 dark:text-gray-100">Edit
                            your information in a swipe</span> Sint occaecat cupidatat non proident, sunt in culpa qui
                        officia deserunt mollit anim.</span>
                    <span class="block text-xs font-medium text-gray-400 dark:text-gray-500">Feb 9, 2024</span>
                </a>
            </li>
            <li class="border-b border-gray-200 last:border-0 dark:border-gray-700/60">
                <a class="block px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/20" href="#0"
                    @click="open = false" @focus="open = true" @focusout="open = false">
                    <span class="mb-2 block text-xs">🚀<span class="font-medium text-gray-800 dark:text-gray-100">Say
                            goodbye to paper receipts!</span> Sint occaecat cupidatat non proident, sunt in culpa qui
                        officia deserunt mollit anim.</span>
                    <span class="block text-xs font-medium text-gray-400 dark:text-gray-500">Jan 24, 2024</span>
                </a>
            </li>
        </ul>
    </div>
</div> --}}
