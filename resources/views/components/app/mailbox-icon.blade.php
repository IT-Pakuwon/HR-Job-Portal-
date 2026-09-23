@if (Route::has('mailbox.index'))
    <a href="{{ route('mailbox.index') }}"
        x-data="{
            count: 0,
            async load() {
                try {
                    const res = await fetch('{{ route('mailbox.unread-count') }}', { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.count = data.count || 0;
                } catch (e) { /* ignore, retried on next poll */ }
            },
            init() {
                this.load();
                this._timer = setInterval(() => {
                    if (document.visibilityState !== 'hidden') this.load();
                }, 30000);
            },
        }"
        x-init="init()"
        class="relative flex h-11 w-11 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
        title="Mailbox">

        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
        </svg>

        <span x-show="count > 0" x-text="count > 9 ? '9+' : count"
            class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white dark:ring-gray-800">
        </span>
    </a>
@endif
