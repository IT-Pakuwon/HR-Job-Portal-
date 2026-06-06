<div x-data="docNotifications()" x-init="init()" class="relative">

    {{-- Bell Button --}}
    <button
        @click.prevent="open = !open"
        class="relative rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
        :class="{ 'bg-gray-100 dark:bg-gray-700': open }"
        title="Document Notifications">

        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        {{-- Badge --}}
        <span x-show="count > 0" x-text="count > 9 ? '9+' : count"
            class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white dark:ring-gray-800">
        </span>
    </button>

    {{-- Dropdown --}}
    <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute right-0 z-50 mt-2 w-96 origin-top-right overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800"
        style="display: none;">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30">
                    <svg class="h-4 w-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notifications</span>
                <span x-show="count > 0" x-text="count"
                    class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-600 dark:bg-red-900/30 dark:text-red-400">
                </span>
            </div>
            <button @click="open = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- List --}}
        <ul class="max-h-[420px] overflow-y-auto divide-y divide-gray-50 dark:divide-gray-700/50">

            <template x-if="items.length === 0">
                <li class="flex flex-col items-center gap-2 px-4 py-10 text-center">
                    <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">All clear!</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">No alerts right now.</p>
                </li>
            </template>

            <template x-for="item in items" :key="item.key">
                <li>
                    <a :href="`${item.url}/${item.hid}`"
                        @click="open = false"
                        class="group flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">

                        {{-- Status Icon --}}
                        <div class="mt-0.5 shrink-0">
                            <div :class="[statusCfg(item.status).iconBg, 'flex h-8 w-8 items-center justify-center rounded-full']">
                                {{-- D: Revised --}}
                                <template x-if="item.status === 'D'">
                                    <svg :class="statusCfg(item.status).iconText" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </template>
                                {{-- R: Rejected --}}
                                <template x-if="item.status === 'R'">
                                    <svg :class="statusCfg(item.status).iconText" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                                {{-- H: On Hold / TKT_PENDING --}}
                                <template x-if="item.status === 'H' || statusCfg(item.status).cat === 'hold'">
                                    <svg :class="statusCfg(item.status).iconText" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                                {{-- Ticket RESPONSE / ITR In Progress: chat icon (blue) --}}
                                <template x-if="statusCfg(item.status).cat === 'info'">
                                    <svg :class="statusCfg(item.status).iconText" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                </template>
                                {{-- Ticket PROCESS: cog icon (indigo) --}}
                                <template x-if="statusCfg(item.status).cat === 'process'">
                                    <svg :class="statusCfg(item.status).iconText" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </template>
                                {{-- Completed / Finished: check-circle (green) --}}
                                <template x-if="statusCfg(item.status).cat === 'success'">
                                    <svg :class="statusCfg(item.status).iconText" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                                {{-- REOPEN / ITR Waiting Approval / ACR Processing: refresh (amber) --}}
                                <template x-if="statusCfg(item.status).cat === 'warn'">
                                    <svg :class="statusCfg(item.status).iconText" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </template>
                                {{-- CANCEL: X circle (red) --}}
                                <template x-if="statusCfg(item.status).cat === 'cancel'">
                                    <svg :class="statusCfg(item.status).iconText" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span x-text="item.docid" class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100"></span>
                                <span x-text="item.label"
                                    :class="statusCfg(item.status).badge"
                                    class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide">
                                </span>
                            </div>
                            <p x-text="item.message" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed"></p>
                            <div class="mt-1 flex items-center gap-1.5 text-[11px] text-gray-400 dark:text-gray-500">
                                <span x-text="item.cpnyid"></span>
                                <template x-if="item.by">
                                    <span>
                                        <span class="mx-1">·</span>
                                        by <span x-text="item.by" class="font-medium"></span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        {{-- Arrow --}}
                        <svg class="mt-1 h-4 w-4 shrink-0 text-gray-300 group-hover:text-gray-500 dark:text-gray-600 dark:group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </li>
            </template>
        </ul>

        {{-- Footer --}}
        <div x-show="items.length > 0" class="border-t border-gray-100 bg-gray-50 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-800/50">
            <p class="text-center text-xs text-gray-400 dark:text-gray-500">
                Refreshes automatically every 10 seconds
            </p>
        </div>

    </div>

    {{-- Toast Popup --}}
    <template x-teleport="body">
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="fixed top-16 right-4 z-9999 w-80 overflow-hidden rounded-2xl border bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800"
            style="display: none;">

            {{-- Colored top bar --}}
            <div :class="statusCfg(toast.item?.status).bar" class="h-1 w-full"></div>

            <div class="flex items-start gap-3 p-4">
                <div :class="[statusCfg(toast.item?.status).iconBg, 'flex h-9 w-9 shrink-0 items-center justify-center rounded-full']">
                    <template x-if="toast.item?.status === 'D'">
                        <svg :class="statusCfg(toast.item?.status).iconText" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </template>
                    <template x-if="toast.item?.status === 'R'">
                        <svg :class="statusCfg(toast.item?.status).iconText" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="toast.item?.status === 'H' || statusCfg(toast.item?.status).cat === 'hold'">
                        <svg :class="statusCfg(toast.item?.status).iconText" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="statusCfg(toast.item?.status).cat === 'info'">
                        <svg :class="statusCfg(toast.item?.status).iconText" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </template>
                    <template x-if="statusCfg(toast.item?.status).cat === 'process'">
                        <svg :class="statusCfg(toast.item?.status).iconText" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </template>
                    <template x-if="statusCfg(toast.item?.status).cat === 'success'">
                        <svg :class="statusCfg(toast.item?.status).iconText" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="statusCfg(toast.item?.status).cat === 'warn'">
                        <svg :class="statusCfg(toast.item?.status).iconText" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </template>
                    <template x-if="statusCfg(toast.item?.status).cat === 'cancel'">
                        <svg :class="statusCfg(toast.item?.status).iconText" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between">
                        <span :class="statusCfg(toast.item?.status).iconText"
                            class="text-xs font-bold uppercase tracking-widest"
                            x-text="(toast.item?.label ?? '') + ' — Notification'"></span>
                        <button @click="toast.show = false" class="ml-2 shrink-0 rounded p-0.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p x-text="toast.item?.docid" class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-100"></p>
                    <p x-text="toast.item?.message" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed"></p>
                    <a :href="toast.item ? `${toast.item.url}/${toast.item.hid}` : '#'"
                        @click="toast.show = false"
                        :class="statusCfg(toast.item?.status).iconText"
                        class="mt-2 inline-flex items-center gap-1 text-xs font-semibold hover:underline">
                        View document
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
function docNotifications() {
    return {
        open: false,
        items: [],
        count: 0,
        toast: { show: false, item: null },
        _seenKey:      'doc_notif_seen_v1',
        _firstSeenKey: 'doc_notif_first_v1',

        // Returns styling config for each status type.
        // All class strings are full literals so Tailwind v4 includes them.
        statusCfg(status) {
            const map = {
                // Document statuses (existing)
                'D': { iconBg: 'bg-amber-100 dark:bg-amber-900/30',   iconText: 'text-amber-600 dark:text-amber-400',   badge: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',   bar: 'bg-amber-500',  cat: 'edit'    },
                'R': { iconBg: 'bg-red-100 dark:bg-red-900/30',       iconText: 'text-red-600 dark:text-red-400',       badge: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',           bar: 'bg-red-500',    cat: 'reject'  },
                'H': { iconBg: 'bg-orange-100 dark:bg-orange-900/30', iconText: 'text-orange-600 dark:text-orange-400', badge: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400', bar: 'bg-orange-500', cat: 'hold'    },
                // Ticket statuses
                'TKT_TRANSFER':   { iconBg: 'bg-amber-100 dark:bg-amber-900/30',   iconText: 'text-amber-600 dark:text-amber-400',   badge: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',     bar: 'bg-amber-500',   cat: 'warn'    },
                'TKT_RESPONSE':   { iconBg: 'bg-blue-100 dark:bg-blue-900/30',     iconText: 'text-blue-600 dark:text-blue-400',     badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',         bar: 'bg-blue-500',    cat: 'info'    },
                'TKT_PROCESS':    { iconBg: 'bg-indigo-100 dark:bg-indigo-900/30', iconText: 'text-indigo-600 dark:text-indigo-400', badge: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',  bar: 'bg-indigo-500',  cat: 'process' },
                'TKT_PENDING':    { iconBg: 'bg-orange-100 dark:bg-orange-900/30', iconText: 'text-orange-600 dark:text-orange-400', badge: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',  bar: 'bg-orange-500',  cat: 'hold'    },
                'TKT_ENVISION':   { iconBg: 'bg-indigo-100 dark:bg-indigo-900/30', iconText: 'text-indigo-600 dark:text-indigo-400', badge: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',  bar: 'bg-indigo-500',  cat: 'process' },
                'TKT_ENV_SOLVED': { iconBg: 'bg-teal-100 dark:bg-teal-900/30',     iconText: 'text-teal-600 dark:text-teal-400',     badge: 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',         bar: 'bg-teal-500',    cat: 'success' },
                'TKT_COMPLETED':  { iconBg: 'bg-green-100 dark:bg-green-900/30',   iconText: 'text-green-600 dark:text-green-400',   badge: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',     bar: 'bg-green-500',   cat: 'success' },
                'TKT_REOPEN':     { iconBg: 'bg-amber-100 dark:bg-amber-900/30',   iconText: 'text-amber-600 dark:text-amber-400',   badge: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',     bar: 'bg-amber-500',   cat: 'warn'    },
                'TKT_CANCEL':     { iconBg: 'bg-red-100 dark:bg-red-900/30',       iconText: 'text-red-600 dark:text-red-400',       badge: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',             bar: 'bg-red-500',     cat: 'cancel'  },
                // ITR statuses
                'ITR_I':     { iconBg: 'bg-blue-100 dark:bg-blue-900/30',   iconText: 'text-blue-600 dark:text-blue-400',   badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',     bar: 'bg-blue-500',   cat: 'info'    },
                'ITR_P':     { iconBg: 'bg-amber-100 dark:bg-amber-900/30', iconText: 'text-amber-600 dark:text-amber-400', badge: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', bar: 'bg-amber-500',  cat: 'warn'    },
                'ITR_D':     { iconBg: 'bg-amber-100 dark:bg-amber-900/30', iconText: 'text-amber-600 dark:text-amber-400', badge: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', bar: 'bg-amber-500',  cat: 'edit'    },
                'ITR_C':     { iconBg: 'bg-green-100 dark:bg-green-900/30', iconText: 'text-green-600 dark:text-green-400', badge: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', bar: 'bg-green-500',  cat: 'success' },
                'ITR_R':     { iconBg: 'bg-red-100 dark:bg-red-900/30',     iconText: 'text-red-600 dark:text-red-400',     badge: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',         bar: 'bg-red-500',    cat: 'cancel'  },
                'ITR_PIC_W': { iconBg: 'bg-amber-100 dark:bg-amber-900/30', iconText: 'text-amber-600 dark:text-amber-400', badge: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400', bar: 'bg-amber-500',  cat: 'warn'    },
                'ITR_PIC_I': { iconBg: 'bg-red-100 dark:bg-red-900/30',     iconText: 'text-red-600 dark:text-red-400',     badge: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',         bar: 'bg-red-500',    cat: 'cancel'  },
                // Access Request statuses
                'ACC_R': { iconBg: 'bg-red-100 dark:bg-red-900/30',        iconText: 'text-red-600 dark:text-red-400',        badge: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',               bar: 'bg-red-500',     cat: 'cancel'  },
                'ACC_P': { iconBg: 'bg-amber-100 dark:bg-amber-900/30',    iconText: 'text-amber-600 dark:text-amber-400',    badge: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',       bar: 'bg-amber-500',   cat: 'warn'    },
                'ACC_C': { iconBg: 'bg-green-100 dark:bg-green-900/30',    iconText: 'text-green-600 dark:text-green-400',    badge: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',       bar: 'bg-green-500',   cat: 'success' },
                'ACC_F': { iconBg: 'bg-emerald-100 dark:bg-emerald-900/30', iconText: 'text-emerald-600 dark:text-emerald-400', badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', bar: 'bg-emerald-500', cat: 'success' },
            };
            return map[status] || { iconBg: 'bg-gray-100 dark:bg-gray-700', iconText: 'text-gray-500 dark:text-gray-400', badge: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400', bar: 'bg-gray-500', cat: 'default' };
        },

        init() {
            this.load();
            setInterval(() => this.load(), 10000);
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },

        // ── Seen tracking (expires after 7 days → triggers re-alert) ──
        _getSeen() {
            try { return JSON.parse(localStorage.getItem(this._seenKey) || '{}'); } catch { return {}; }
        },

        _pruneSeen() {
            const seen = this._getSeen();
            const cutoff = Date.now() - 7 * 86400 * 1000;
            let changed = false;
            Object.keys(seen).forEach(k => { if (seen[k] < cutoff) { delete seen[k]; changed = true; } });
            if (changed) localStorage.setItem(this._seenKey, JSON.stringify(seen));
        },

        _markSeen(key) {
            const seen = this._getSeen();
            seen[key] = Date.now();
            localStorage.setItem(this._seenKey, JSON.stringify(seen));
        },

        // ── First-seen tracking (never auto-expires; cleaned up when item leaves the list) ──
        _getFirstSeen() {
            try { return JSON.parse(localStorage.getItem(this._firstSeenKey) || '{}'); } catch { return {}; }
        },

        _markFirstSeen(key) {
            const fs = this._getFirstSeen();
            if (!fs[key]) {                          // only record the very first time
                fs[key] = Date.now();
                localStorage.setItem(this._firstSeenKey, JSON.stringify(fs));
            }
        },

        // Returns true when a key was first seen 7+ days ago → "still in process" re-alert.
        _isReAlert(key) {
            const fs = this._getFirstSeen();
            return !!fs[key] && (Date.now() - fs[key]) >= 7 * 86400 * 1000;
        },

        // Remove first-seen entries whose items are no longer returned by the server
        // (status changed → old key gone → clean up so next status starts fresh).
        _cleanFirstSeen(activeKeys) {
            const fs = this._getFirstSeen();
            let changed = false;
            Object.keys(fs).forEach(k => { if (!activeKeys.includes(k)) { delete fs[k]; changed = true; } });
            if (changed) localStorage.setItem(this._firstSeenKey, JSON.stringify(fs));
        },

        // ── Audio ping ──
        _ping() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const play = (freq, startAt, duration, vol = 0.25) => {
                    const osc = ctx.createOscillator(), gain = ctx.createGain();
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime + startAt);
                    gain.gain.setValueAtTime(vol, ctx.currentTime + startAt);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + startAt + duration);
                    osc.start(ctx.currentTime + startAt);
                    osc.stop(ctx.currentTime + startAt + duration);
                };
                play(880, 0, 0.18);
                play(1320, 0.12, 0.22);
            } catch (e) { /* AudioContext not supported */ }
        },

        async load() {
            try {
                const res = await fetch('/my-document-notifications', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const { data = [] } = await res.json();

                // Prune seen entries older than 7 days on every poll.
                // When a seen entry expires the item becomes "fresh" again → re-alert fires.
                this._pruneSeen();

                const seen  = this._getSeen();
                const fresh = data.filter(item => !seen[item.key]);

                if (fresh.length > 0) {
                    const first = fresh[0];

                    // Check re-alert BEFORE marking seen (first-seen is already set for old keys).
                    const isReAlert = this._isReAlert(first.key);
                    // Statuses where the recipient must act → "Please proceed your document."
                    // Everything else (creator waiting on others) → "Please wait..."
                    const _proceedStatuses = new Set(['D', 'H', 'ITR_D', 'ITR_PIC_W', 'ITR_PIC_I', 'ACC_C']);
                    const reAlertMsg = _proceedStatuses.has(first.status)
                        ? 'Please proceed your document.'
                        : 'Please wait, your document is still in process.';
                    const toastMsg   = isReAlert ? reAlertMsg : first.message;

                    // Mark all fresh items as seen and record first-seen timestamp.
                    fresh.forEach(item => {
                        this._markSeen(item.key);
                        this._markFirstSeen(item.key); // no-op if already recorded
                    });

                    this.toast = { show: true, item: { ...first, message: toastMsg } };
                    setTimeout(() => { this.toast.show = false; }, 6000);

                    this._ping();

                    if ('Notification' in window && Notification.permission === 'granted') {
                        const n = new Notification(`${first.label} — ${first.docid}`, {
                            body: toastMsg,
                            icon: '/favicon.ico',
                            tag: first.key,
                        });
                        n.onclick = () => { window.focus(); window.location.href = `${first.url}/${first.hid}`; };
                    }
                }

                // Remove first-seen data for items no longer in the server response
                // so the next status the item transitions to gets a fresh first-seen.
                this._cleanFirstSeen(data.map(d => d.key));

                this.items = data;
                this.count = data.length;
                localStorage.setItem('doc_notif_count', data.length);
            } catch (e) {
                console.error('doc-notifications load failed', e);
            }
        }
    };
}
</script>
