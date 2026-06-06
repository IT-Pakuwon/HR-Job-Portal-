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
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Document Alerts</span>
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
                    <p class="text-xs text-gray-400 dark:text-gray-500">No document alerts right now.</p>
                </li>
            </template>

            <template x-for="item in items" :key="item.key">
                <li>
                    <a :href="`${item.url}/${item.hid}`"
                        @click="open = false"
                        class="group flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">

                        {{-- Status Icon --}}
                        <div class="mt-0.5 shrink-0">
                            <template x-if="item.status === 'D'">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                                    <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                            </template>
                            <template x-if="item.status === 'R'">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                                    <svg class="h-4 w-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </template>
                            <template x-if="item.status === 'H'">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30">
                                    <svg class="h-4 w-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </template>
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span x-text="item.docid" class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100"></span>
                                <span x-text="item.label"
                                    :class="{
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': item.status === 'D',
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': item.status === 'R',
                                        'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': item.status === 'H'
                                    }"
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
                Refreshes automatically every 15 seconds
            </p>
        </div>

    </div>

    {{-- Toast Popup (bottom-right) --}}
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
            <div :class="{
                    'bg-amber-500': toast.item?.status === 'D',
                    'bg-red-500':   toast.item?.status === 'R',
                    'bg-orange-500': toast.item?.status === 'H'
                }" class="h-1 w-full"></div>

            <div class="flex items-start gap-3 p-4">
                <div :class="{
                        'bg-amber-100 dark:bg-amber-900/30':  toast.item?.status === 'D',
                        'bg-red-100 dark:bg-red-900/30':      toast.item?.status === 'R',
                        'bg-orange-100 dark:bg-orange-900/30': toast.item?.status === 'H'
                    }" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full">
                    <template x-if="toast.item?.status === 'D'">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </template>
                    <template x-if="toast.item?.status === 'R'">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="toast.item?.status === 'H'">
                        <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between">
                        <span :class="{
                                'text-amber-600 dark:text-amber-400':  toast.item?.status === 'D',
                                'text-red-600 dark:text-red-400':      toast.item?.status === 'R',
                                'text-orange-600 dark:text-orange-400': toast.item?.status === 'H'
                            }" class="text-xs font-bold uppercase tracking-widest" x-text="toast.item?.label + ' — Action Required'"></span>
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
                        :class="{
                            'text-amber-600 hover:text-amber-700 dark:text-amber-400':  toast.item?.status === 'D',
                            'text-red-600 hover:text-red-700 dark:text-red-400':        toast.item?.status === 'R',
                            'text-orange-600 hover:text-orange-700 dark:text-orange-400': toast.item?.status === 'H'
                        }"
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
        _seenKey: 'doc_notif_seen_v1',

        init() {
            this.load();
            setInterval(() => this.load(), 15000);

            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },

        _getSeen() {
            try { return JSON.parse(localStorage.getItem(this._seenKey) || '{}'); } catch { return {}; }
        },

        _markSeen(key) {
            const seen = this._getSeen();
            seen[key] = Date.now();
            const cutoff = Date.now() - 7 * 86400 * 1000;
            Object.keys(seen).forEach(k => { if (seen[k] < cutoff) delete seen[k]; });
            localStorage.setItem(this._seenKey, JSON.stringify(seen));
        },

        _ping() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();

                const play = (freq, startAt, duration, vol = 0.25) => {
                    const osc  = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime + startAt);
                    gain.gain.setValueAtTime(vol, ctx.currentTime + startAt);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + startAt + duration);
                    osc.start(ctx.currentTime + startAt);
                    osc.stop(ctx.currentTime + startAt + duration);
                };

                // two-tone WhatsApp-style ping
                play(880,  0,    0.18);
                play(1320, 0.12, 0.22);
            } catch (e) { /* AudioContext not supported */ }
        },

        async load() {
            try {
                const res = await fetch('/my-document-notifications', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const { data = [] } = await res.json();
                const seen = this._getSeen();
                const fresh = data.filter(item => !seen[item.key]);

                if (fresh.length > 0) {
                    const first = fresh[0];
                    fresh.forEach(item => this._markSeen(item.key));

                    this.toast = { show: true, item: first };
                    setTimeout(() => { this.toast.show = false; }, 6000);

                    this._ping();

                    if ('Notification' in window && Notification.permission === 'granted') {
                        const n = new Notification(`Document ${first.label}`, {
                            body: `${first.docid} has been ${first.label.toLowerCase()} (${first.cpnyid})`,
                            icon: '/favicon.ico',
                            tag: first.key,
                        });
                        n.onclick = () => { window.focus(); window.location.href = `${first.url}/${first.hid}`; };
                    }
                }

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
