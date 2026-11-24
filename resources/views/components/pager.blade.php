{{--
    Alpine pager that looks like the DataTables footer ("Showing 1 to 10 of 12 entries" + Previous 1 2 Next).
    Usage: <x-pager page="f.page" per="f.per_page" total="total" go="go" />
    page / per / total are Alpine expressions in the parent scope; go is a parent method taking a page number.
--}}
@props(['page' => 'page', 'per' => '10', 'total' => 'total', 'go' => 'go'])

<div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 px-5 py-3 dark:border-white/[0.06]"
    x-data="{
        get cur() { return {{ $page }}; },
        get n() { return Number({{ $total }}) || 0; },
        get size() { return Number({{ $per }}) || 10; },
        get last() { return Math.max(1, Math.ceil(this.n / this.size)); },
        get from() { return this.n ? (this.cur - 1) * this.size + 1 : 0; },
        get to() { return Math.min(this.cur * this.size, this.n); },
        get items() {
            const l = this.last, c = this.cur, out = [];
            for (let i = 1; i <= l; i++) {
                if (i === 1 || i === l || Math.abs(i - c) <= 1 || (c <= 3 && i <= 4) || (c >= l - 2 && i >= l - 3)) out.push(i);
                else if (out[out.length - 1] !== '…') out.push('…');
            }
            return out;
        },
    }">
    <span class="text-sm text-gray-500 dark:text-gray-400"
        x-text="n ? `Showing ${from} to ${to} of ${n.toLocaleString()} entries` : 'No entries'"></span>

    <div class="flex items-center gap-2" x-show="n > 0">
        <button type="button" @click="{{ $go }}(cur - 1)" :disabled="cur <= 1"
            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 px-4 text-sm text-gray-500 transition hover:bg-gray-50 hover:text-indigo-600 disabled:cursor-default disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-gray-500 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-indigo-300">Previous</button>

        <div class="flex items-center gap-0.5">
            <template x-for="(it, i) in items" :key="'pg' + i">
                <span class="contents">
                    <span x-show="it === '…'" class="px-1 text-gray-400">…</span>
                    <button type="button" x-show="it !== '…'" @click="{{ $go }}(it)"
                        :class="it === cur
                            ? 'border-indigo-200 bg-indigo-50 font-semibold text-indigo-600 dark:border-indigo-500/40 dark:bg-indigo-500/15 dark:text-indigo-300'
                            : 'border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-indigo-600 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/5'"
                        class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm transition" x-text="it"></button>
                </span>
            </template>
        </div>

        <button type="button" @click="{{ $go }}(cur + 1)" :disabled="cur >= last"
            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-gray-200 px-4 text-sm text-gray-500 transition hover:bg-gray-50 hover:text-indigo-600 disabled:cursor-default disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-gray-500 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-indigo-300">Next</button>
    </div>
</div>
