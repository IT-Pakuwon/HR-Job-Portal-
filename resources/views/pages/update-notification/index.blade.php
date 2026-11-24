@php
    $meta = [
        'new_feature' => [
            'label' => 'New Feature', 'icon' => '🚀',
            'accent' => 'bg-emerald-500',
            'pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-400/20',
            'tile' => 'bg-emerald-50 dark:bg-emerald-500/10',
            'dot' => 'bg-emerald-500',
        ],
        'maintenance' => [
            'label' => 'Maintenance', 'icon' => '🔧',
            'accent' => 'bg-amber-500',
            'pill' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-400/20',
            'tile' => 'bg-amber-50 dark:bg-amber-500/10',
            'dot' => 'bg-amber-500',
        ],
        'information' => [
            'label' => 'Information', 'icon' => 'ℹ️',
            'accent' => 'bg-sky-500',
            'pill' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-400/20',
            'tile' => 'bg-sky-50 dark:bg-sky-500/10',
            'dot' => 'bg-sky-500',
        ],
    ];
    $isAdminOnly = auth()->user()?->isPrimaryAdmin() ?? false;
    $counts = $notifications->groupBy(fn ($n) => isset($meta[$n->news_type]) ? $n->news_type : 'information')->map->count();
@endphp
<x-app-layout>
    <div class="w-full space-y-5 p-2"
        x-data="{ filter: 'all', composeOpen: false, type: 'information', text: '', busy: false, error: '' ,
            wrap(m) { const el = this.$refs.desc, s = el.selectionStart, e = el.selectionEnd;
                this.text = this.text.slice(0, s) + m + this.text.slice(s, e) + m + this.text.slice(e);
                this.$nextTick(() => { el.focus(); el.setSelectionRange(s + m.length, e + m.length); }); },
            async post() {
                if (!this.text.trim()) { this.error = 'Please describe what changed.'; return; }
                this.busy = true; this.error = '';
                try {
                    const r = await fetch('{{ route('update-notification.store') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ type: this.type, description: this.text })
                    });
                    if (!r.ok) throw new Error((await r.json().catch(() => ({}))).message || 'Failed to post');
                    location.reload();
                } catch (e) { this.error = e.message; this.busy = false; }
            } }">

        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-linear-to-br from-violet-500 to-indigo-600 text-white shadow-sm shadow-violet-500/30">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold leading-tight text-slate-900 dark:text-white">Update Notification</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">What's new, planned maintenance and announcements</p>
                </div>
            </div>
            @if($isAdminOnly)
                <button type="button" @click="composeOpen = !composeOpen"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2.5" d="M12 5v14M5 12h14" /></svg>
                    New update
                </button>
            @endif
        </div>

        {{-- Composer (admin only) --}}
        @if($isAdminOnly)
            <div x-show="composeOpen" x-cloak x-transition
                class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                    @foreach($meta as $key => $m)
                        <button type="button" @click="type = '{{ $key }}'"
                            :class="type === '{{ $key }}' ? 'border-violet-500 ring-1 ring-violet-500' : 'border-slate-200 dark:border-slate-600'"
                            class="flex items-center gap-2 rounded-xl border p-2.5 text-left transition hover:border-violet-400">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg text-base {{ $m['tile'] }}">{{ $m['icon'] }}</span>
                            <span class="text-xs font-semibold text-slate-800 dark:text-slate-100">{{ $m['label'] }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="flex gap-1">
                    <button type="button" @click="wrap('**')" class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">B</button>
                    <button type="button" @click="wrap('*')" class="rounded-md border border-slate-300 px-2.5 py-1 text-xs italic text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">I</button>
                </div>
                <textarea x-ref="desc" x-model="text" rows="4" placeholder="What changed?"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-violet-500 focus:ring-violet-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></textarea>
                <p x-show="error" x-text="error" class="text-xs text-red-600"></p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="composeOpen = false" class="rounded-lg px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">Cancel</button>
                    <button type="button" @click="post()" :disabled="busy" :class="busy && 'opacity-60'"
                        class="rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-violet-700">Post update</button>
                </div>
            </div>
        @endif

        {{-- Filter chips --}}
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="filter = 'all'"
                :class="filter === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700'"
                class="rounded-full px-3 py-1 text-xs font-medium transition">All <span class="opacity-60">{{ $notifications->count() }}</span></button>
            @foreach($meta as $key => $m)
                <button type="button" @click="filter = '{{ $key }}'"
                    :class="filter === '{{ $key }}' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700'"
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition">
                    <span class="h-1.5 w-1.5 rounded-full {{ $m['dot'] }}"></span>{{ $m['label'] }}
                    <span class="opacity-60">{{ $counts[$key] ?? 0 }}</span>
                </button>
            @endforeach
        </div>

        {{-- Timeline --}}
        <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:divide-slate-700 dark:border-slate-700 dark:bg-slate-800">
            @forelse($notifications as $n)
                @php
                    $key = isset($meta[$n->news_type]) ? $n->news_type : 'information';
                    $m = $meta[$key];
                    $when = \Carbon\Carbon::parse($n->created_at);
                    $paragraphs = preg_split('/\n\s*\n+/', trim(str_replace("\r", '', (string) $n->news_descr)));
                @endphp
                <article x-show="filter === 'all' || filter === '{{ $key }}'"
                    class="relative flex flex-col gap-3 py-5 pl-6 pr-6 transition hover:bg-slate-50/60 sm:flex-row sm:items-center sm:gap-8 dark:hover:bg-slate-700/20">
                    <span class="absolute inset-y-0 left-0 w-1 {{ $m['accent'] }}"></span>
                    <div class="flex shrink-0 items-center justify-between gap-2 sm:w-52 sm:flex-col sm:items-start sm:justify-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $m['pill'] }}">
                            <span>{{ $m['icon'] }}</span>{{ $n->news_title }}
                        </span>
                        <time class="text-xs text-slate-400 sm:mt-1" title="{{ $when->format('d M Y, H:i') }}">
                            <span class="block font-semibold text-slate-700 dark:text-slate-200">{{ $when->format('d M Y') }}</span>
                            <span class="hidden sm:block">{{ $when->diffForHumans() }}</span>
                        </time>
                    </div>
                    <div class="min-w-0 flex-1 space-y-3 text-[15px] leading-6 text-slate-600 dark:text-slate-300">
                        @foreach($paragraphs as $para)
                            <p>{!! nl2br(preg_replace(['/\*\*(.+?)\*\*/s', '/\*(.+?)\*/s'], ['<strong class="font-semibold text-slate-900 dark:text-white">$1</strong>', '<em>$1</em>'], e($para))) !!}</p>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="py-12 text-center">
                    <p class="text-2xl">📭</p>
                    <p class="mt-1 text-sm text-slate-400">No updates yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
