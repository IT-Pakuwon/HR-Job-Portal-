<div class="shadow-xs col-span-full flex flex-col rounded-xl bg-white sm:col-span-6 xl:col-span-4 dark:bg-gray-800">
    <div class="px-5 pt-5">
        <header class="mb-2 flex items-start justify-between">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Acme Professional</h2>
            <!-- Menu button -->
            <div class="relative inline-flex" x-data="{ open: false }">
                <button class="rounded-full"
                    :class="open ? 'bg-gray-100 dark:bg-gray-700/60 text-gray-500 dark:text-gray-400' :
                        'text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400'"
                    aria-haspopup="true" @click.prevent="open = !open" :aria-expanded="open">
                    <span class="sr-only">Menu</span>
                    <svg class="h-8 w-8 fill-current" viewBox="0 0 32 32">
                        <circle cx="16" cy="16" r="2" />
                        <circle cx="10" cy="16" r="2" />
                        <circle cx="22" cy="16" r="2" />
                    </svg>
                </button>
                <div class="absolute right-0 top-full z-10 mt-1 min-w-36 origin-top-right overflow-hidden rounded-lg border border-gray-200 bg-white py-1.5 dark:border-gray-700/60 dark:bg-gray-800"
                    @click.outside="open = false" @keydown.escape.window="open = false" x-show="open"
                    x-transition:enter="transition ease-out duration-200 transform"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-out duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" x-cloak>
                    <ul>
                        <li>
                            <a class="flex px-3 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-200"
                                href="#0" @click="open = false" @focus="open = true"
                                @focusout="open = false">Option 1</a>
                        </li>
                        <li>
                            <a class="flex px-3 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-200"
                                href="#0" @click="open = false" @focus="open = true"
                                @focusout="open = false">Option 2</a>
                        </li>
                        <li>
                            <a class="flex px-3 py-1 text-sm font-medium text-red-500 hover:text-red-600" href="#0"
                                @click="open = false" @focus="open = true" @focusout="open = false">Remove</a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        <div class="mb-1 text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">Sales</div>
        <div class="flex items-start">
            <div class="mr-2 text-3xl font-bold text-gray-800 dark:text-gray-100">
                ${{ number_format($dataFeed->sumDataSet(3, 1), 0) }}</div>
            <div class="rounded-full bg-green-500/20 px-1.5 text-sm font-medium text-green-700">+29%</div>
        </div>
    </div>
    <!-- Chart built with Chart.js 3 -->
    <!-- Check out src/js/components/dashboard-card-03.js for config -->
    <div class="grow max-sm:max-h-[128px] xl:max-h-[128px]">
        <!-- Change the height attribute to adjust the chart height -->
        <canvas id="dashboard-card-03" width="389" height="128"></canvas>
    </div>
</div>
