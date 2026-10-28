@props(['compact' => false])
{{-- Your Progress: what is still outstanding on this dashboard's panels. Fed by the Main Menu tile counts
     (component/menu emits `dash-progress`); renders nothing when the dashboard has no count-bearing tile.
     Progress = how much of today's peak backlog has been cleared (peak is remembered per day in this browser). --}}
<style>
    @keyframes dp-confetti-kf {
        0%   { transform: translate3d(0, 0, 0) rotate(0deg); opacity: 1; }
        100% { transform: translate3d(var(--dx), var(--dy), 0) rotate(var(--rot)); opacity: 0; }
    }
    .dp-confetti { animation: dp-confetti-kf 1.9s cubic-bezier(.2, .7, .3, 1) forwards; }
    @keyframes dp-float-kf { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
    .dp-float { animation: dp-float-kf 2.6s ease-in-out infinite; }
    @keyframes dp-pulse-kf { 0%, 100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, .45); } 50% { box-shadow: 0 0 0 7px rgba(244, 63, 94, 0); } }
    .dp-pulse { animation: dp-pulse-kf 2s ease-out infinite; }
</style>

<div class="relative {{ $compact ? '' : 'col-span-full' }} overflow-hidden rounded-2xl border bg-white p-3 shadow-sm transition-colors dark:bg-gray-800"
    :class="ready && left === 0 ? 'border-emerald-300 dark:border-emerald-500/40' : 'border-gray-200 dark:border-gray-700'"
    x-data="progressCard()" x-init="init()"
    @dash-progress.window="update($event.detail)"
    x-show="items.length" x-cloak style="display: none;">

    {{-- Confetti burst when the last outstanding item gets cleared --}}
    <div x-show="burst" class="pointer-events-none absolute inset-0 z-10" style="display: none;">
        <template x-for="c in confetti" :key="c.id">
            <span class="dp-confetti absolute left-[14%] top-1/2 block h-2.5 w-1.5 rounded-sm"
                :style="`background:${c.color}; --dx:${c.dx}px; --dy:${c.dy}px; --rot:${c.rot}deg; animation-delay:${c.delay}s`"></span>
        </template>
    </div>

    {{-- compact = one slot of a three-card row (hero above, checklist below); otherwise hero left, checklist right --}}
    <div class="grid grid-cols-1 gap-3 {{ $compact ? '' : 'lg:grid-cols-[minmax(0,27rem)_minmax(0,1fr)]' }}">

        {{-- Hero: greeting, ring, mood --}}
        <div class="relative overflow-hidden rounded-xl p-4 text-white transition-all duration-700"
            :class="ready && left === 0 ? 'bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500' : 'bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500'">

            <span class="pointer-events-none absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/15 blur-xl"></span>
            <span class="pointer-events-none absolute -bottom-12 left-1/3 h-28 w-28 rounded-full bg-white/10 blur-xl"></span>

            <div class="relative flex items-center gap-4">
                <div class="relative h-20 w-20 shrink-0">
                    <svg class="h-20 w-20 -rotate-90 drop-shadow" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.5" fill="none" stroke="rgba(255,255,255,.28)" stroke-width="3.2" />
                        <circle cx="18" cy="18" r="15.5" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round"
                            stroke-dasharray="97.4" :stroke-dashoffset="97.4 - (97.4 * pct / 100)"
                            class="transition-all duration-1000 ease-out" />
                    </svg>
                    <span class="absolute inset-0 flex flex-col items-center justify-center leading-none">
                        <span class="text-xl font-extrabold tabular-nums" x-text="!ready ? '…' : pct + '%'"></span>
                        <span class="mt-0.5 text-[9px] font-semibold uppercase tracking-wider opacity-80">cleared</span>
                    </span>
                </div>

                <div class="min-w-0">
                    <p class="truncate text-xs font-medium text-white/80" x-text="greeting"></p>
                    <h2 class="text-lg font-extrabold leading-tight" x-text="headline"></h2>
                    <p class="mt-0.5 text-xs text-white/85" x-text="subline"></p>
                </div>

                <span class="ml-auto hidden shrink-0 text-4xl sm:block" :class="ready && left === 0 ? 'dp-float' : ''" x-text="mood"></span>
            </div>

            <div class="relative mt-4">
                <div class="mb-1 flex justify-between text-[10px] font-semibold uppercase tracking-wider text-white/75">
                    <span x-text="ready ? done + ' of ' + total + ' done' : 'Loading'"></span>
                    <span x-text="ready && left > 0 ? left + ' to go' : ''"></span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-white/25">
                    <div class="h-full rounded-full bg-white transition-all duration-1000 ease-out" :style="'width:' + (ready ? pct : 0) + '%'"></div>
                </div>
            </div>
        </div>

        {{-- What is left: a checklist, one row per panel --}}
        <ul class="min-w-0 divide-y divide-gray-100 self-center overflow-hidden rounded-xl border border-gray-100 dark:divide-gray-700 dark:border-gray-700">
            <template x-for="p in items" :key="p.badge">
                <li>
                    <button type="button" @click="open(p)"
                        class="group flex w-full items-center gap-3 px-3 py-2.5 text-left transition hover:bg-indigo-50/60 dark:hover:bg-gray-700/50">

                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition"
                            :class="p.count === 0 ? 'border-emerald-400 bg-emerald-400 text-white' : (p.count === null ? 'border-gray-200 dark:border-gray-600' : 'border-rose-300 dark:border-rose-400/60')">
                            <svg x-show="p.count === 0" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>

                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="tone(p).icon">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath(p)" />
                            </svg>
                        </span>

                        <span class="min-w-0 flex-1 truncate text-sm font-semibold"
                            :class="p.count === 0 ? 'text-gray-400 line-through decoration-gray-300 dark:text-gray-500 dark:decoration-gray-600' : 'text-gray-700 dark:text-gray-100'"
                            x-text="p.label"></span>

                        <span class="shrink-0 text-xs font-medium"
                            :class="p.count === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500'"
                            x-text="p.count === null ? 'Checking…' : (p.count === 0 ? 'All clear' : (p.count === 1 ? '1 left' : p.count + ' left'))"></span>

                        <span x-show="p.count !== 0" class="flex h-6 min-w-6 shrink-0 items-center justify-center rounded-full px-1.5 text-xs font-extrabold tabular-nums"
                            :class="p.count === null ? 'bg-gray-200 text-gray-400 dark:bg-gray-700' : 'dp-pulse bg-rose-500 text-white'"
                            x-text="p.count === null ? '…' : (p.count > 99 ? '99+' : p.count)"></span>

                        <svg class="h-4 w-4 shrink-0 text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-indigo-500 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </li>
            </template>
        </ul>
    </div>
</div>

<script>
function progressCard() {
    const first = @js(\Illuminate\Support\Str::of(auth()->user()->name ?? '')->before(' ')->lower()->title()->toString());
    const now = new Date(); // local date: toISOString() is UTC, which rolls the "day" over at 07:00 in Jakarta
    const day = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
    const key = 'dashProgressPeak:' + location.pathname + ':' + day;
    const readPeak = () => { try { return parseInt(localStorage.getItem(key), 10) || 0; } catch (e) { return 0; } };
    const writePeak = n => { try { localStorage.setItem(key, String(n)); } catch (e) {} };
    const colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#a855f7', '#ec4899'];

    const icons = {
        approval: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
        mailbox:  'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
        panel:    'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
    };
    const tones = {
        approval: { bar: 'bg-amber-400',  icon: 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300' },
        mailbox:  { bar: 'bg-sky-400',    icon: 'bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300' },
        panel:    { bar: 'bg-violet-400', icon: 'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300' },
    };
    const kind = p => (p.badge === 'approval' || p.badge === 'mailbox') ? p.badge : 'panel';

    return {
        items: window.__dashProgress || [],
        peak: readPeak(),
        sawOutstanding: false,
        burst: false,
        confetti: [],

        init() { this.update(this.items); },

        tone(p) { return p.count === 0 ? { bar: 'bg-emerald-400', icon: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' } : tones[kind(p)]; },
        iconPath(p) { return icons[kind(p)]; },

        // counts are null until each source has answered, so "all done" is never claimed too early
        get ready() { return this.items.length > 0 && this.items.every(i => i.count !== null); },
        get left() { return this.items.reduce((n, p) => n + (p.count || 0), 0); },
        get total() { return Math.max(this.peak, this.left); },
        get done() { return this.total - this.left; },
        get pct() { return this.total > 0 ? Math.round(this.done * 100 / this.total) : 100; },

        get greeting() {
            const h = new Date().getHours();
            const part = h < 11 ? 'Good morning' : (h < 15 ? 'Good afternoon' : (h < 19 ? 'Good evening' : 'Good night'));
            return part + (first ? ', ' + first : '');
        },
        get mood() {
            if (!this.ready) return '👀';
            if (this.left === 0) return '🎉';
            return this.pct >= 75 ? '🚀' : (this.pct >= 40 ? '🔥' : '💪');
        },
        get headline() {
            if (!this.ready) return 'Checking what is left…';
            if (this.left === 0) return this.done > 0 ? 'Yay! All done for today' : 'All clear — nothing waiting';
            // one kind of backlog: name it ("5 approvals waiting"); several kinds: "N things left"
            const open = this.items.filter(p => p.count > 0);
            if (open.length === 1) {
                const n = this.left, one = n === 1, k = kind(open[0]);
                if (k === 'approval') return n + (one ? ' approval waiting' : ' approvals waiting');
                if (k === 'mailbox') return n + (one ? ' unread email' : ' unread emails');
            }
            return this.left === 1 ? 'Just 1 thing left' : this.left + ' things left';
        },
        get subline() {
            if (!this.ready) return 'One moment';
            if (this.left === 0) return this.done > 0 ? 'You cleared ' + this.done + ' item' + (this.done === 1 ? '' : 's') + ' today. Enjoy the rest of your day!' : 'Enjoy the rest of your day!';
            if (this.done === 0) return 'Let’s get started — you’ve got this.';
            return this.pct >= 75 ? 'So close — one last push!' : (this.pct >= 40 ? 'Over halfway there, keep it rolling!' : 'Nice start, keep going!');
        },

        update(items) {
            this.items = items || [];
            if (!this.ready) return;
            const left = this.left;
            if (left > this.peak) { this.peak = left; writePeak(left); }
            if (left > 0) this.sawOutstanding = true;
            if (left === 0 && this.sawOutstanding) { this.sawOutstanding = false; this.celebrate(); }
        },

        celebrate() {
            this.confetti = Array.from({ length: 44 }, (_, i) => ({
                id: i,
                color: colors[i % colors.length],
                dx: Math.round(60 + Math.random() * 480),
                dy: Math.round(-110 + Math.random() * 220),
                rot: Math.round(Math.random() * 720 - 360),
                delay: (Math.random() * 0.3).toFixed(2),
            }));
            this.burst = true;
            setTimeout(() => { this.burst = false; }, 2600);
        },

        open(p) {
            if (p.panel) this.$dispatch('dash-open-panel', { panel: p.panel, host: p.host });
            else if (p.href) window.location.href = p.href;
        },
    };
}
</script>
