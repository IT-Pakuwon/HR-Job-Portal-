<div x-data="globalSearch()" x-init="init()" @keydown.window="onGlobalKeydown($event)" @click.outside="close()" class="relative w-full">

    {{-- Search Input --}}
    <div class="relative">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>

        <input
            x-ref="input"
            x-model="q"
            @input="onInput()"
            @focus="open = true"
            @keydown.escape="close()"
            @keydown.enter="goToFirst()"
            type="text"
            placeholder="Search by document ID or purpose..."
            class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-16 text-sm text-gray-800 placeholder-gray-400 transition-colors focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900 dark:focus:ring-indigo-900/40" />

        <button x-show="q.length > 0" @click="clear()" type="button"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded p-0.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <kbd x-show="q.length === 0" class="pointer-events-none absolute right-2.5 top-1/2 hidden -translate-y-1/2 rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-gray-400 sm:inline-block dark:border-gray-600 dark:bg-gray-800 dark:text-gray-500">Ctrl K</kbd>
    </div>

    {{-- Results Panel --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute left-0 z-50 mt-2 w-full min-w-88 origin-top-left overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800"
        style="display: none;">

        <ul class="max-h-105 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-700/50">

            <template x-if="q.trim().length > 0 && q.trim().length < 2">
                <li class="px-4 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Keep typing…</li>
            </template>

            <template x-if="loading">
                <li class="px-4 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Searching…</li>
            </template>

            <template x-if="!loading && q.trim().length >= 2 && results.length === 0">
                <li class="flex flex-col items-center gap-2 px-4 py-10 text-center">
                    <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No matches</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Try a different document ID or keyword.</p>
                </li>
            </template>

            <template x-if="!loading && q.trim().length === 0">
                <li class="px-4 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Type a document ID or purpose to jump straight to it.</li>
            </template>

            <template x-for="item in results" :key="item.href">
                <li>
                    <a :href="item.href" @click="close()"
                        class="group flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/30">
                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span x-text="item.docid" class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100"></span>
                                <span x-text="item.label" class="shrink-0 rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"></span>
                            </div>
                            <p x-show="item.infohd" x-text="item.infohd" class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400"></p>
                            <div class="mt-1 flex items-center gap-1.5 text-[11px] text-gray-400 dark:text-gray-500">
                                <span x-text="item.cpnyid"></span>
                                <template x-if="item.department"><span><span class="mx-1">·</span><span x-text="item.department"></span></span></template>
                            </div>
                        </div>
                        <svg class="mt-1 h-4 w-4 shrink-0 text-gray-300 group-hover:text-gray-500 dark:text-gray-600 dark:group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </li>
            </template>
        </ul>
    </div>
</div>

<script>
function globalSearch() {
    return {
        open: false,
        q: '',
        results: [],
        loading: false,
        _debounce: null,

        init() {},

        close() {
            this.open = false;
        },

        clear() {
            this.q = '';
            this.results = [];
            this.$refs.input.focus();
        },

        onGlobalKeydown(e) {
            const isMod = e.ctrlKey || e.metaKey;
            if (isMod && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                this.open = true;
                this.$nextTick(() => this.$refs.input.focus());
            }
        },

        onInput() {
            clearTimeout(this._debounce);
            const term = this.q.trim();
            if (term.length < 2) {
                this.results = [];
                this.loading = false;
                return;
            }
            this.loading = true;
            this._debounce = setTimeout(() => this.fetchResults(term), 250);
        },

        async fetchResults(term) {
            try {
                const res = await fetch('/global-search?q=' + encodeURIComponent(term), {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) { this.results = []; return; }
                const { data = [] } = await res.json();
                if (this.q.trim() === term) this.results = data;
            } catch (e) {
                console.error('global-search failed', e);
                this.results = [];
            } finally {
                this.loading = false;
            }
        },

        goToFirst() {
            if (this.results.length > 0) window.location.href = this.results[0].href;
        },
    };
}
</script>
