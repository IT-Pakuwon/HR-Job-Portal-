<!-- Search button -->
<div x-data="{ searchOpen: false }">
    <!-- Button -->
    <button
        class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-gray-100 lg:hover:bg-gray-200 dark:hover:bg-gray-700/50 dark:lg:hover:bg-gray-800"
        :class="{ 'bg-gray-200 dark:bg-gray-800': searchOpen }"
        @click.prevent="searchOpen = true;if (searchOpen) $nextTick(()=>{$refs.searchInput.focus()});"
        aria-controls="search-modal">
        <span class="sr-only">Search</span>
        <svg class="fill-current text-gray-500/80 dark:text-gray-400/80" width="16" height="16" viewBox="0 0 16 16"
            xmlns="http://www.w3.org/2000/svg">
            <path
                d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7ZM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5Z" />
            <path d="m13.314 11.9 2.393 2.393a.999.999 0 1 1-1.414 1.414L11.9 13.314a8.019 8.019 0 0 0 1.414-1.414Z" />
        </svg>
    </button>
    <!-- Modal backdrop -->
    <div class="fixed inset-0 z-50 bg-gray-900/30 transition-opacity" x-show="searchOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>
    <!-- Modal dialog -->
    <div id="search-modal"
        class="fixed inset-0 top-20 z-50 mb-4 flex items-start justify-center overflow-hidden px-4 sm:px-6"
        role="dialog" aria-modal="true" x-show="searchOpen" x-transition:enter="transition ease-in-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
        <div class="max-h-full w-full max-w-2xl overflow-auto rounded-lg border border-transparent bg-white dark:border-gray-700/60 dark:bg-gray-800"
            @click.outside="searchOpen = false" @keydown.escape.window="searchOpen = false">
            <!-- Search form -->
            <form class="border-b border-gray-200 dark:border-gray-700/60">
                <div class="relative">
                    <label for="modal-search" class="sr-only">Search</label>
                    <input id="modal-search"
                        class="w-full appearance-none border-0 bg-white py-3 pl-10 pr-4 placeholder-gray-400 focus:ring-transparent dark:bg-gray-800 dark:text-gray-300 dark:placeholder-gray-500"
                        type="search" placeholder="Search Anything…" x-ref="searchInput" />
                    <button class="group absolute inset-0 right-auto" type="submit" aria-label="Search">
                        <svg class="ml-4 mr-2 shrink-0 fill-current text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"
                            width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7ZM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5Z" />
                            <path
                                d="m13.314 11.9 2.393 2.393a.999.999 0 1 1-1.414 1.414L11.9 13.314a8.019 8.019 0 0 0 1.414-1.414Z" />
                        </svg>
                    </button>
                </div>
            </form>
            <div class="px-2 py-4">
                <!-- Recent searches -->
                <div class="mb-3 last:mb-0">
                    <div class="mb-2 px-2 text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">Recent
                        searches</div>
                    <ul class="text-sm">
                        <li>
                            <a class="flex items-center rounded-lg p-2 text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700/20"
                                href="#0" @click="searchOpen = false" @focus="searchOpen = true"
                                @focusout="searchOpen = false">
                                <svg class="mr-3 shrink-0 fill-current text-gray-400 dark:text-gray-500" width="16"
                                    height="16" viewBox="0 0 16 16">
                                    <path
                                        d="M15.707 14.293v.001a1 1 0 01-1.414 1.414L11.185 12.6A6.935 6.935 0 017 14a7.016 7.016 0 01-5.173-2.308l-1.537 1.3L0 8l4.873 1.12-1.521 1.285a4.971 4.971 0 008.59-2.835l1.979.454a6.971 6.971 0 01-1.321 3.157l3.107 3.112zM14 6L9.127 4.88l1.521-1.28a4.971 4.971 0 00-8.59 2.83L.084 5.976a6.977 6.977 0 0112.089-3.668l1.537-1.3L14 6z" />
                                </svg>
                                <span>Form Builder - 23 hours on-demand video</span>
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center rounded-lg p-2 text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700/20"
                                href="#0" @click="searchOpen = false" @focus="searchOpen = true"
                                @focusout="searchOpen = false">
                                <svg class="mr-3 shrink-0 fill-current text-gray-400 dark:text-gray-500" width="16"
                                    height="16" viewBox="0 0 16 16">
                                    <path
                                        d="M15.707 14.293v.001a1 1 0 01-1.414 1.414L11.185 12.6A6.935 6.935 0 017 14a7.016 7.016 0 01-5.173-2.308l-1.537 1.3L0 8l4.873 1.12-1.521 1.285a4.971 4.971 0 008.59-2.835l1.979.454a6.971 6.971 0 01-1.321 3.157l3.107 3.112zM14 6L9.127 4.88l1.521-1.28a4.971 4.971 0 00-8.59 2.83L.084 5.976a6.977 6.977 0 0112.089-3.668l1.537-1.3L14 6z" />
                                </svg>
                                <span>Access Mosaic on mobile and TV</span>
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center rounded-lg p-2 text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700/20"
                                href="#0" @click="searchOpen = false" @focus="searchOpen = true"
                                @focusout="searchOpen = false">
                                <svg class="mr-3 shrink-0 fill-current text-gray-400 dark:text-gray-500" width="16"
                                    height="16" viewBox="0 0 16 16">
                                    <path
                                        d="M15.707 14.293v.001a1 1 0 01-1.414 1.414L11.185 12.6A6.935 6.935 0 017 14a7.016 7.016 0 01-5.173-2.308l-1.537 1.3L0 8l4.873 1.12-1.521 1.285a4.971 4.971 0 008.59-2.835l1.979.454a6.971 6.971 0 01-1.321 3.157l3.107 3.112zM14 6L9.127 4.88l1.521-1.28a4.971 4.971 0 00-8.59 2.83L.084 5.976a6.977 6.977 0 0112.089-3.668l1.537-1.3L14 6z" />
                                </svg>
                                <span>Product Update - Q4 2024</span>
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center rounded-lg p-2 text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700/20"
                                href="#0" @click="searchOpen = false" @focus="searchOpen = true"
                                @focusout="searchOpen = false">
                                <svg class="mr-3 shrink-0 fill-current text-gray-400 dark:text-gray-500" width="16"
                                    height="16" viewBox="0 0 16 16">
                                    <path
                                        d="M15.707 14.293v.001a1 1 0 01-1.414 1.414L11.185 12.6A6.935 6.935 0 017 14a7.016 7.016 0 01-5.173-2.308l-1.537 1.3L0 8l4.873 1.12-1.521 1.285a4.971 4.971 0 008.59-2.835l1.979.454a6.971 6.971 0 01-1.321 3.157l3.107 3.112zM14 6L9.127 4.88l1.521-1.28a4.971 4.971 0 00-8.59 2.83L.084 5.976a6.977 6.977 0 0112.089-3.668l1.537-1.3L14 6z" />
                                </svg>
                                <span>Master Digital Marketing Strategy course</span>
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center rounded-lg p-2 text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700/20"
                                href="#0" @click="searchOpen = false" @focus="searchOpen = true"
                                @focusout="searchOpen = false">
                                <svg class="mr-3 shrink-0 fill-current text-gray-400 dark:text-gray-500" width="16"
                                    height="16" viewBox="0 0 16 16">
                                    <path
                                        d="M15.707 14.293v.001a1 1 0 01-1.414 1.414L11.185 12.6A6.935 6.935 0 017 14a7.016 7.016 0 01-5.173-2.308l-1.537 1.3L0 8l4.873 1.12-1.521 1.285a4.971 4.971 0 008.59-2.835l1.979.454a6.971 6.971 0 01-1.321 3.157l3.107 3.112zM14 6L9.127 4.88l1.521-1.28a4.971 4.971 0 00-8.59 2.83L.084 5.976a6.977 6.977 0 0112.089-3.668l1.537-1.3L14 6z" />
                                </svg>
                                <span>Dedicated forms for products</span>
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center rounded-lg p-2 text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700/20"
                                href="#0" @click="searchOpen = false" @focus="searchOpen = true"
                                @focusout="searchOpen = false">
                                <svg class="mr-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                    width="16" height="16" viewBox="0 0 16 16">
                                    <path
                                        d="M15.707 14.293v.001a1 1 0 01-1.414 1.414L11.185 12.6A6.935 6.935 0 017 14a7.016 7.016 0 01-5.173-2.308l-1.537 1.3L0 8l4.873 1.12-1.521 1.285a4.971 4.971 0 008.59-2.835l1.979.454a6.971 6.971 0 01-1.321 3.157l3.107 3.112zM14 6L9.127 4.88l1.521-1.28a4.971 4.971 0 00-8.59 2.83L.084 5.976a6.977 6.977 0 0112.089-3.668l1.537-1.3L14 6z" />
                                </svg>
                                <span>Product Update - Q4 2024</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Recent pages -->
                <div class="mb-3 last:mb-0">
                    <div class="mb-2 px-2 text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">Recent
                        pages</div>
                    <ul class="text-sm">
                        <li>
                            <a class="flex items-center rounded-lg p-2 text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700/20"
                                href="#0" @click="searchOpen = false" @focus="searchOpen = true"
                                @focusout="searchOpen = false">
                                <svg class="mr-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                    width="16" height="16" viewBox="0 0 16 16">
                                    <path
                                        d="M14 0H2c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h8l5-5V1c0-.6-.4-1-1-1zM3 2h10v8H9v4H3V2z" />
                                </svg>
                                <span><span class="font-medium">Messages</span> - <span
                                        class="text-gray-600 dark:text-gray-400">Conversation / … / Mike
                                        Mills</span></span>
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center rounded-lg p-2 text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700/20"
                                href="#0" @click="searchOpen = false" @focus="searchOpen = true"
                                @focusout="searchOpen = false">
                                <svg class="mr-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                    width="16" height="16" viewBox="0 0 16 16">
                                    <path
                                        d="M14 0H2c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h8l5-5V1c0-.6-.4-1-1-1zM3 2h10v8H9v4H3V2z" />
                                </svg>
                                <span><span class="font-medium">Messages</span> - <span
                                        class="text-gray-600 dark:text-gray-400">Conversation / … / Eva
                                        Patrick</span></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
