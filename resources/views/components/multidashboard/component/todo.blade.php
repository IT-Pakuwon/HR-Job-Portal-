@props(['compact' => false])
<div class="col-span-full rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
    x-data="todoList()" x-init="init()">

    <div class="flex items-center justify-between" :class="expanded ? 'mb-4' : ''">
        <button type="button" @click="toggleExpanded()" class="flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100">To Do List</h2>
            <span x-show="openCount > 0" x-text="openCount + ' open'"
                class="rounded-full bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300"></span>
            <svg class="h-3.5 w-3.5 text-gray-400 transition-transform" :class="expanded ? '' : '-rotate-90'"
                fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
            </svg>
        </button>
    </div>

    <div x-show="expanded" x-collapse>
        {{-- compact = a narrow column (under Your Progress): calendar above, list below --}}
        <div class="grid grid-cols-1 gap-4 {{ $compact ? '' : 'lg:grid-cols-[22rem_minmax(0,1fr)]' }}">

            {{-- Left: week calendar. Click a day to filter, click it again to show every task. --}}
            <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/40">
                <div class="mb-3 flex items-center justify-between">
                    <button type="button" @click="goToday()" class="text-left" title="Back to today">
                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500" x-text="monthLabel"></span>
                        <span class="block text-xs font-semibold text-gray-600 dark:text-gray-300" x-text="weekLabel"></span>
                    </button>
                    <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                        <button type="button" @click="shiftWeek(-1)" class="rounded-lg p-1.5 hover:bg-white dark:hover:bg-gray-700" title="Previous week">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button type="button" @click="shiftWeek(1)" class="rounded-lg p-1.5 hover:bg-white dark:hover:bg-gray-700" title="Next week">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-1.5">
                    <template x-for="d in days" :key="d.iso">
                        <button type="button" @click="selectDay(d.iso)"
                            :title="d.off && holidays[d.iso] ? holidays[d.iso] : null"
                            class="flex flex-col items-center rounded-xl py-2 text-center transition"
                            :class="selected === d.iso
                                ? 'bg-indigo-500 text-white shadow-md shadow-indigo-500/30'
                                : (d.iso === today ? 'bg-white text-indigo-600 ring-1 ring-indigo-300 dark:bg-gray-800 dark:text-indigo-300 dark:ring-indigo-500/50'
                                    : 'bg-white text-gray-600 hover:bg-indigo-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700')">
                            <span class="text-[10px] font-semibold uppercase tracking-wide" :class="d.off ? (selected === d.iso ? 'text-rose-200' : 'text-rose-500') : 'opacity-70'" x-text="d.dow"></span>
                            <span class="mt-0.5 text-base font-bold leading-tight" :class="d.off ? (selected === d.iso ? 'text-rose-200' : 'text-rose-500') : ''" x-text="d.day"></span>
                            <span class="mt-1 flex h-1.5 items-center justify-center">
                                <span x-show="countFor(d.iso).open > 0" class="h-1.5 w-1.5 rounded-full"
                                    :class="selected === d.iso ? 'bg-white' : 'bg-indigo-500'"></span>
                                <span x-show="countFor(d.iso).open === 0 && countFor(d.iso).done > 0" class="h-1.5 w-1.5 rounded-full"
                                    :class="selected === d.iso ? 'bg-white/70' : 'bg-emerald-400'"></span>
                            </span>
                        </button>
                    </template>
                </div>

                <div class="mt-4">
                    <div class="mb-1 flex items-center justify-between text-[11px] font-medium text-gray-500 dark:text-gray-400">
                        <span x-text="selected ? selectedFull : 'All tasks'"></span>
                        <span x-text="progress.done + ' / ' + progress.total + ' done'"></span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full rounded-full bg-emerald-400 transition-all duration-300" :style="'width:' + progress.pct + '%'"></div>
                    </div>
                    <button type="button" x-show="selected" @click="selected = null"
                        class="mt-2 text-[11px] font-semibold text-indigo-500 hover:text-indigo-600" style="display: none;">Show all tasks</button>
                </div>
            </div>

            {{-- Right: quick add + list. --}}
            <div class="min-w-0">
                <form @submit.prevent="add()" class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white p-1.5 focus-within:border-indigo-400 dark:border-gray-700 dark:bg-gray-900">
                    <input type="text" x-model="newTitle" maxlength="255"
                        :placeholder="selected ? 'Add a task for ' + selectedLabel + '...' : 'Add a task...'"
                        class="min-w-0 flex-1 border-0 bg-transparent px-2 py-1.5 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-0 dark:text-gray-100">
                    <input type="date" x-model="newDate"
                        class="w-32 shrink-0 rounded-lg border-0 bg-gray-100 px-2 py-1.5 text-xs text-gray-600 focus:outline-none focus:ring-0 dark:bg-gray-700 dark:text-gray-200">
                    <button type="submit" :disabled="!newTitle.trim() || saving"
                        class="shrink-0 rounded-lg bg-indigo-500 px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-600 disabled:opacity-40">Add</button>
                </form>

                {{-- Meetings and tasks side by side on wide screens (rows stretched across the whole width looked empty); stacked when narrow or when there is no meeting. --}}
                <div class="mt-3 grid grid-cols-1 gap-4" @unless ($compact) :class="visibleMeetings.length ? 'xl:grid-cols-2' : ''" @endunless>
                <div x-show="visibleMeetings.length" class="min-w-0" style="display: none;">
                <p class="mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Meetings</p>
                <ul class="max-h-72 space-y-1.5 overflow-y-auto pr-1">
                    <template x-for="m in visibleMeetings" :key="m.docid">
                        <li class="flex items-center gap-3 rounded-xl border border-sky-100 bg-sky-50 px-3 py-2 dark:border-sky-500/30 dark:bg-sky-500/10">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/15 text-sky-600 dark:text-sky-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-gray-700 dark:text-gray-100" x-text="m.title"></span>
                                <span class="block truncate text-[11px] text-gray-500 dark:text-gray-400">
                                    <span x-show="!selected" x-text="shortDate(m.date) + ' · '"></span><span x-text="m.start + ' – ' + m.end"></span>
                                    <span x-show="m.location || m.online" x-text="' · ' + [m.location, m.online].filter(Boolean).join(' · ')"></span>
                                </span>
                            </div>
                            <a x-show="m.join_url" :href="m.join_url" target="_blank" rel="noopener noreferrer"
                                class="shrink-0 rounded-full bg-sky-500 px-2.5 py-0.5 text-[10px] font-semibold text-white transition hover:bg-sky-600"
                                x-text="'Join ' + (m.online || 'meeting')" style="display: none;"></a>
                            <span x-show="!m.join_url" class="shrink-0 rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-semibold text-sky-600 dark:bg-sky-500/20 dark:text-sky-300" style="display: none;">Meeting</span>
                        </li>
                    </template>
                </ul>
                </div>

                <div class="min-w-0">
                <p x-show="visibleMeetings.length" class="mb-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500" style="display: none;">Tasks</p>
                <ul class="max-h-72 space-y-1.5 overflow-y-auto pr-1">
                    <template x-for="t in visible" :key="t.id">
                        <li class="group flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 transition hover:border-indigo-200 dark:border-gray-700 dark:bg-gray-700/30 dark:hover:border-indigo-500/40">
                            <input type="checkbox" :checked="t.is_done" @change="toggle(t)"
                                class="h-4 w-4 shrink-0 cursor-pointer rounded-full border-gray-300 text-emerald-500 focus:ring-emerald-400">
                            <div class="min-w-0 flex-1">
                                <input type="text" x-show="editing === t.id" x-model="editTitle" maxlength="255"
                                    @keydown.enter.prevent="saveEdit(t)" @keydown.escape="editing = null" @blur="saveEdit(t)"
                                    :id="'todo-edit-' + t.id" style="display: none;"
                                    class="w-full rounded border border-indigo-300 px-2 py-0.5 text-sm focus:outline-none dark:border-indigo-500/50 dark:bg-gray-900 dark:text-gray-100">
                                <span x-show="editing !== t.id" @dblclick="startEdit(t)" x-text="t.title" title="Double-click to rename"
                                    class="block cursor-text truncate text-sm"
                                    :class="t.is_done ? 'text-gray-400 line-through dark:text-gray-500' : 'text-gray-700 dark:text-gray-200'"></span>
                            </div>
                            <span x-show="t.due_date && !selected" x-text="shortDate(t.due_date)"
                                class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                :class="!t.is_done && t.due_date < today ? 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300' : 'bg-white text-gray-500 dark:bg-gray-700 dark:text-gray-300'"></span>
                            <button type="button" @click="remove(t)" title="Delete"
                                class="shrink-0 rounded p-1 text-gray-300 opacity-0 transition hover:text-rose-500 group-hover:opacity-100 focus:opacity-100">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </li>
                    </template>

                    <li x-show="!loading && visible.length === 0 && visibleMeetings.length === 0" class="flex flex-col items-center py-8 text-center" style="display: none;">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500/10 text-indigo-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        <span class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400" x-text="selected ? 'Nothing planned for this day' : 'No tasks yet'"></span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">Type above and press Enter to add one.</span>
                    </li>
                </ul>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function todoList() {
    const iso = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    const parse = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
    const mondayOf = d => { const x = new Date(d.getFullYear(), d.getMonth(), d.getDate()); x.setDate(x.getDate() - ((x.getDay() + 6) % 7)); return x; };
    const hdr = () => ({ 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' });
    const url = '{{ route('todo.index') }}';

    return {
        items: [],
        holidays: {},
        meetings: [],
        loading: true,
        saving: false,
        expanded: true,
        today: iso(new Date()),
        weekStart: mondayOf(new Date()),
        selected: iso(new Date()),
        newTitle: '',
        newDate: iso(new Date()),
        editing: null,
        editTitle: '',

        init() {
            try { this.expanded = localStorage.getItem('todo_expanded') !== '0'; } catch (e) {}
            this.load();
        },

        toggleExpanded() {
            this.expanded = !this.expanded;
            try { localStorage.setItem('todo_expanded', this.expanded ? '1' : '0'); } catch (e) {}
        },

        async load() {
            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (res.ok) { const j = await res.json(); this.items = j.data || []; this.holidays = j.holidays || {}; this.meetings = j.meetings || []; }
            } catch (e) { console.error('todo load failed', e); }
            this.loading = false;
        },

        get days() {
            const names = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            return names.map((dow, i) => {
                const d = new Date(this.weekStart); d.setDate(d.getDate() + i);
                const k = iso(d);
                return { iso: k, dow, day: d.getDate(), off: i >= 5 || k in this.holidays };
            });
        },

        get weekLabel() {
            const a = this.weekStart, b = new Date(a); b.setDate(b.getDate() + 6);
            const f = d => d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
            return `${f(a)} – ${f(b)} ${b.getFullYear()}`;
        },

        get selectedLabel() {
            if (!this.selected) return '';
            return this.selected === this.today ? 'today'
                : parse(this.selected).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' });
        },

        get monthLabel() {
            // the month of the picked day when it is in the shown week (a week can straddle two months), else of the week's first day
            const a = this.weekStart, end = new Date(a); end.setDate(end.getDate() + 7);
            const s = this.selected ? parse(this.selected) : null;
            const m = s && s >= a && s < end ? s : a;
            return m.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
        },

        get selectedFull() {
            return this.selected ? parse(this.selected).toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' }) : '';
        },

        get progress() {
            const list = this.visible, done = list.filter(t => t.is_done).length;
            return { done, total: list.length, pct: list.length ? Math.round(done * 100 / list.length) : 0 };
        },

        get visibleMeetings() {
            return this.selected ? this.meetings.filter(m => m.date === this.selected) : this.meetings.filter(m => m.date >= this.today);
        },

        get openCount() { return this.items.filter(t => !t.is_done).length; },

        get visible() {
            return this.selected ? this.items.filter(t => t.due_date === this.selected) : this.items;
        },

        countFor(day) {
            const list = this.items.filter(t => t.due_date === day);
            const open = list.filter(t => !t.is_done).length + this.meetings.filter(m => m.date === day).length;
            return { open, done: list.filter(t => t.is_done).length };
        },

        shortDate(s) { return parse(s).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }); },

        shiftWeek(n) { const d = new Date(this.weekStart); d.setDate(d.getDate() + n * 7); this.weekStart = d; },

        goToday() { this.weekStart = mondayOf(new Date()); this.selectDay(this.today, true); },

        selectDay(day, force = false) {
            this.selected = (!force && this.selected === day) ? null : day;
            if (this.selected) this.newDate = this.selected;
        },

        async add() {
            const title = this.newTitle.trim();
            if (!title || this.saving) return;
            this.saving = true;
            try {
                const res = await fetch(url, { method: 'POST', headers: hdr(), body: JSON.stringify({ title, due_date: this.newDate || null }) });
                if (res.ok) {
                    this.items.push((await res.json()).data);
                    this.sort();
                    this.newTitle = '';
                }
            } catch (e) { console.error('todo add failed', e); }
            this.saving = false;
        },

        async patch(t, body) {
            const res = await fetch(url + '/' + t.id, { method: 'PUT', headers: hdr(), body: JSON.stringify(body) });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            Object.assign(t, (await res.json()).data);
            this.sort();
        },

        async toggle(t) {
            try { await this.patch(t, { is_done: !t.is_done }); } catch (e) { console.error('todo toggle failed', e); this.load(); }
        },

        startEdit(t) {
            this.editing = t.id; this.editTitle = t.title;
            this.$nextTick(() => document.getElementById('todo-edit-' + t.id)?.focus());
        },

        async saveEdit(t) {
            if (this.editing !== t.id) return;
            const title = this.editTitle.trim();
            this.editing = null;
            if (!title || title === t.title) return;
            try { await this.patch(t, { title }); } catch (e) { console.error('todo edit failed', e); this.load(); }
        },

        async remove(t) {
            this.items = this.items.filter(x => x.id !== t.id);
            try { await fetch(url + '/' + t.id, { method: 'DELETE', headers: hdr() }); } catch (e) { this.load(); }
        },

        sort() {
            this.items.sort((a, b) =>
                (a.is_done - b.is_done) || ((a.due_date || '9999') < (b.due_date || '9999') ? -1 : (a.due_date || '9999') > (b.due_date || '9999') ? 1 : 0) || (a.id - b.id));
        },
    };
}
</script>
