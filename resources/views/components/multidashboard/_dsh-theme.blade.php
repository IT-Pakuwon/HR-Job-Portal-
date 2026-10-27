{{-- Shared look for every multidashboard (header pill, KPI cards, underline tabs, mono doc IDs). Plain CSS: dash*.js lives in /public, so Tailwind never scans it. --}}
<style>
    /* Plain CSS on purpose (dashit.js lives in /public, so Tailwind never scans it). Dark mode follows the .dark class. */
    :root {
        --dsh-line: #e2e8f0;
        --dsh-soft: #f1f5f9;
        --dsh-muted: #64748b;
        --dsh-card: #fff;
        --dsh-fg: #0f172a;
        --dsh-accent: #2563eb;
        --dsh-accent-soft: #e8effe;
        --dsh-hot: #b45309;
    }

    .dark {
        --dsh-line: #334155;
        --dsh-soft: #273449;
        --dsh-muted: #94a3b8;
        --dsh-card: #1e293b;
        --dsh-fg: #f1f5f9;
        --dsh-accent: #7aa2ff;
        --dsh-accent-soft: #1f2c4d;
        --dsh-hot: #fbbf24;
    }

    .dsh-kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: .75rem;
    }

    .dsh-kpi {
        display: flex;
        flex-direction: column;
        gap: .25rem;
        text-align: left;
        cursor: pointer;
        padding: .875rem 1rem;
        border-radius: .75rem;
        border: 1px solid var(--dsh-line);
        background: var(--dsh-card);
        color: var(--dsh-fg);
        transition: border-color .15s, background-color .15s;
    }

    .dsh-kpi:hover {
        border-color: var(--dsh-accent);
    }

    /* the KPI card whose tab / filter is showing */
    .dsh-kpis .dsh-kpi.dsh-kpi-on {
        border-color: var(--dsh-accent);
        box-shadow: 0 0 0 1px var(--dsh-accent);
    }

    .dsh-kpi-static {
        cursor: default;
    }
    /* a held panel makes every KPI card a way to open it */
    body:has([data-panel-held]) .dsh-kpi-static {
        cursor: pointer;
    }

    .dsh-kpi-static:hover {
        border-color: var(--dsh-line);
    }

    .dsh-kpi-l {
        font-size: .75rem;
        font-weight: 500;
        color: var(--dsh-muted);
    }

    .dsh-kpi-n {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -.02em;
        font-variant-numeric: tabular-nums;
    }

    .dsh-kpi-hot .dsh-kpi-n.dsh-has {
        color: var(--dsh-hot);
    }

    .dsh-tabbar {
        display: flex;
        gap: .25rem;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: none;
    }

    .dsh-tabbar::-webkit-scrollbar {
        display: none;
    }

    .dsh-tab {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        white-space: nowrap;
        cursor: pointer;
        padding: .5rem .75rem .625rem;
        font-size: .8125rem;
        font-weight: 600;
        color: var(--dsh-muted);
        background: none;
        border: 0;
        border-bottom: 2px solid transparent;
        transition: color .15s, border-color .15s;
    }

    .dsh-tab:hover {
        color: var(--dsh-fg);
    }

    .dsh-tab-on,
    .dsh-tab-on:hover {
        color: var(--dsh-accent);
        border-bottom-color: var(--dsh-accent);
    }

    .dsh-id {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: .75rem;
        font-weight: 600;
        color: var(--dsh-accent);
        text-decoration: none;
    }

    .dsh-id:hover {
        text-decoration: underline;
    }

    .dsh-id-btn {
        display: inline-block;
        padding: .25rem .625rem;
        border-radius: .5rem;
        border: 1px solid #0891b2;
        background: #0891b2;
        color: #fff;
        transition: filter .15s;
    }

    .dsh-id-btn:hover {
        text-decoration: none;
        filter: brightness(.9);
    }

    .dark .dsh-id-btn {
        border-color: #22d3ee;
        background: #22d3ee;
        color: #0f172a;
    }

    .dsh-sub {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .375rem;
        margin-top: .125rem;
        font-size: .75rem;
        color: var(--dsh-muted);
    }

    .dsh-st {
        display: inline-flex;
        align-items: center;
        gap: .375rem;
        white-space: nowrap;
        border-radius: 9999px;
        padding: .1875rem .625rem .1875rem .5rem;
        font-size: .75rem;
        font-weight: 600;
    }

    .dsh-st::before {
        content: "";
        width: .375rem;
        height: .375rem;
        border-radius: 9999px;
        background: currentColor;
    }
</style>
<script>
    // The dashboards' own JS re-assigns the old pill classes on every tab click; re-skin them as underline tabs.
    (function() {
        var SEL = 'button[id^="tab-"], button[id^="wh-tab-"]';

        function skin(b) {
            var c = b.className || '';
            if (c.indexOf('dsh-tab') !== -1) return;
            b.className = /(^|\s)(bg-black|bg-zinc-700)(\s|$)/.test(c) ? 'dsh-tab dsh-tab-on' : 'dsh-tab';
        }

        function setup() {
            var tabs = document.querySelectorAll(SEL);
            if (!tabs.length) return;
            var bar = tabs[0].parentElement,
                header = bar.parentElement;
            bar.classList.add('dsh-tabbar');
            if (header) {
                header.style.paddingBottom = '0';
                header.style.paddingTop = '.5rem';
            }
            var mo = new MutationObserver(function(list) {
                list.forEach(function(m) {
                    skin(m.target);
                });
            });
            tabs.forEach(function(b) {
                skin(b);
                mo.observe(b, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup);
        else setup();
    })();
</script>

<script>
    // A KPI card click also opens the dashboard's panel when a Menu tile is keeping it hidden
    // (the root's Alpine listens for this). The dashboard's own handler still switches the tab.
    // Static KPI cards (no tab of their own) open the panel too, but only when one is being held.
    (function () {
        function open(e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
            if (e.target.closest('.dsh-kpis .dsh-kpi') && document.querySelector('[data-panel-held]')) {
                window.dispatchEvent(new CustomEvent('dash-open-panel'));
            }
        }
        document.addEventListener('click', open, true);
        document.addEventListener('keydown', open, true);
    })();
</script>
