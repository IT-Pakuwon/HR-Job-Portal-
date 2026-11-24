@props(['timeId' => 'dashboardRefreshTime', 'buttonId' => 'refreshDashboard'])
{{-- Auto-refresh button: a round button with the seconds left to the next refresh inside it and a ring that runs down with the
     countdown; click (hover shows the icon) to refresh now. The ids are what the dashboard's own script looks up (it writes the
     "mm:ss" countdown into the time element and runs the click), so they are passed in per dashboard. The time element is kept
     (hidden) for the script and mirrored into the visible number and ring here, so none of the dashboard scripts had to change. --}}
<button id="{{ $buttonId }}" type="button" title="Next refresh in … — click to refresh now" aria-label="Refresh now"
    {{ $attributes->merge(['class' => 'group relative flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700']) }}>
    <svg class="pointer-events-none absolute inset-0 h-full w-full -rotate-90" viewBox="0 0 44 44" aria-hidden="true">
        <circle cx="22" cy="22" r="20" fill="none" stroke-width="3" class="stroke-slate-200 dark:stroke-slate-700" />
        <circle data-refresh-ring cx="22" cy="22" r="20" fill="none" stroke-width="3" stroke-linecap="round"
            class="stroke-green-500" stroke-dasharray="125.66" stroke-dashoffset="0"
            style="transition: stroke-dashoffset 1s linear" />
    </svg>
    <span id="{{ $timeId }}" class="sr-only">--</span>
    <span data-refresh-num aria-hidden="true" class="text-[11px] font-semibold tabular-nums leading-none group-hover:hidden">--</span>
    <svg class="hidden h-3.5 w-3.5 group-hover:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
    </svg>
    <script>
        (function () {
            const btn = document.currentScript.parentElement;
            const src = btn.querySelector('.sr-only');
            const out = btn.querySelector('[data-refresh-num]');
            const ring = btn.querySelector('[data-refresh-ring]');
            const CIRC = 2 * Math.PI * 20;
            let total = 0, last = Infinity;
            const show = () => {
                const t = (src.textContent || '').trim();
                const m = t.match(/^(\d+):(\d{2})$/);
                const secs = m ? parseInt(m[1], 10) * 60 + parseInt(m[2], 10) : NaN;
                // "00:17" -> "17", "01:05" -> "1:05"
                out.textContent = m ? (parseInt(m[1], 10) === 0 ? String(parseInt(m[2], 10)) : parseInt(m[1], 10) + ':' + m[2]) : t;
                btn.title = 'Next refresh in ' + t + ' — click to refresh now';
                if (isNaN(secs)) return;
                // a jump up means the countdown restarted: that value is the full length of the cycle
                if (secs > last || secs > total) total = secs;
                last = secs;
                const left = total ? secs / total : 1;
                ring.style.transition = (left >= 1) ? 'none' : 'stroke-dashoffset 1s linear';
                // ring empties as time runs out; aim one tick ahead so the 1s linear animation lands on the next value
                const next = total ? Math.max(secs - 1, 0) / total : 1;
                ring.style.strokeDasharray = CIRC;
                if (left >= 1) {
                    ring.style.strokeDashoffset = 0;
                    requestAnimationFrame(() => { ring.style.transition = 'stroke-dashoffset 1s linear'; ring.style.strokeDashoffset = CIRC * (1 - next); });
                } else {
                    ring.style.strokeDashoffset = CIRC * (1 - next);
                }
            };
            new MutationObserver(show).observe(src, { childList: true, characterData: true, subtree: true });
            show();
        })();
    </script>
</button>
