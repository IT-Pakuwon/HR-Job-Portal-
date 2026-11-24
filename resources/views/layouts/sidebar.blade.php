<style>
    /* ================= SIDEBAR LINKS ================= */
    .sidebar-link {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 32px;
        padding: 4px 12px 4px 16px;
        border-radius: 0.5rem;
        color: rgb(75 85 99);
        /* gray-600 */
        transition: background-color .15s ease, color .15s ease;
    }

    /* Left accent bar — replaces the old dot bullet */
    .sidebar-link::before {
        content: '';
        position: absolute;
        left: 3px;
        top: 6px;
        bottom: 6px;
        width: 3px;
        border-radius: 9999px;
        background-color: transparent;
        transition: background-color .15s ease;
    }

    .sidebar-link:hover {
        color: rgb(17 24 39);
        /* gray-900 */
        background-color: rgb(243 244 246);
        /* gray-100 */
    }

    .sidebar-link:hover::before {
        background-color: rgb(209 213 219);
        /* gray-300 */
    }

    /* Active state — Blade already adds text-indigo-600 when the route matches */
    .sidebar-link.text-indigo-600 {
        background-color: rgb(238 242 255);
        /* indigo-50 */
        font-weight: 600;
    }

    .sidebar-link.text-indigo-600::before {
        background-color: rgb(99 102 241);
        /* indigo-500 */
    }

    /* Dark mode */
    .dark .sidebar-link {
        color: rgb(156 163 175);
        /* gray-400 */
    }

    .dark .sidebar-link:hover {
        color: rgb(243 244 246);
        /* gray-100 */
        background-color: rgba(255, 255, 255, .05);
    }

    .dark .sidebar-link:hover::before {
        background-color: rgb(107 114 128);
        /* gray-500 */
    }

    .dark .sidebar-link.text-indigo-600 {
        background-color: rgba(86, 177, 243, .15);
    }

    .dark .sidebar-link.text-indigo-600::before {
        background-color: rgb(165 180 252);
        /* indigo-300 */
    }

    /* ================= SECTION HEADER ICONS ================= */
    .section-icon {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
        color: rgb(156 163 175);
        /* gray-400 */
    }

    .dark .section-icon {
        color: rgb(107 114 128);
        /* gray-500 */
    }

    /* ================= SETTINGS SUBHEADERS ================= */
    .settings-subheader {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        padding: 8px 16px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
        color: rgb(107 114 128);
        /* gray-500 */
    }

    .settings-subheader:hover {
        color: rgb(55 65 81);
        /* gray-700 */
    }

    /* Dark mode */
    .dark .settings-subheader {
        color: rgb(156 163 175);
        /* gray-400 */
    }

    .dark .settings-subheader:hover {
        color: rgb(243 244 246);
        /* gray-100 */
    }

    /* ================= SUBMENU ================= */
    .submenu {
        padding-left: 1rem;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    /* ================= CHEVRON (VISIBLE FIX) ================= */
    .chevron {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        color: rgb(107 114 128);
        /* gray-500 */
    }

    /* Dark mode chevron */
    .dark .chevron {
        color: rgb(209 213 219);
        /* gray-300 */
    }

    /* Hover feedback */
    .settings-subheader:hover .chevron {
        color: rgb(55 65 81);
        /* gray-700 */
    }

    .dark .settings-subheader:hover .chevron {
        color: rgb(243 244 246);
        /* gray-100 */
    }

    .chevron {
        color: rgb(107 114 128);
        /* gray-500 */
    }

    .dark .chevron {
        color: rgb(243 244 246);
        /* gray-100 (white-ish) */
    }

    /* =====================================================================
       REDESIGN — off-canvas sidebar (aside.sb-root)
       Tokens first, light = default, dark under .dark
       ===================================================================== */
    .sb-root {
        --sb-bg: #ffffff;
        --sb-border: rgb(226 232 240);
        --sb-text: rgb(51 65 85);
        --sb-muted: rgb(100 116 139);
        --sb-hover: rgba(14, 165, 233, .07);
        --sb-active-bg: rgba(14, 165, 233, .14);
        --sb-active-text: rgb(3 105 161);
        --sb-group-bg: rgba(14, 165, 233, .06);
        --sb-group-text: rgb(7 89 133);
        --sb-chip: #0ea5e9;
        --sb-field: rgb(241 245 249);
        --sb-accent: #38bdf8;
        background: var(--sb-bg);
        border-right: 1px solid var(--sb-border);
        color: var(--sb-text);
    }

    .dark .sb-root {
        --sb-bg: #0d1829;
        --sb-border: rgba(255, 255, 255, .07);
        --sb-text: rgb(203 213 225);
        --sb-muted: rgb(125 138 160);
        --sb-hover: rgba(255, 255, 255, .06);
        --sb-active-bg: rgba(14, 165, 233, .14);
        --sb-active-text: #ffffff;
        --sb-group-bg: rgba(255, 255, 255, .045);
        --sb-group-text: rgb(226 232 240);
        --sb-field: rgba(255, 255, 255, .06);
    }

    /* Header */
    .sb-root .sb-header {
        padding: 18px 20px 16px;
        border-bottom: 1px solid var(--sb-border);
        background: transparent;
    }

    .sb-root .sb-logo {
        height: 34px;
        width: auto;
        padding: 4px 6px;
        border-radius: 10px;
        background: rgba(255, 255, 255, .92);
        box-shadow: 0 2px 10px rgba(14, 165, 233, .25);
    }

    .sb-root .sb-title {
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .01em;
        background: none;
        

        color: var(--sb-strong, #0f172a);
    }

    .dark .sb-root .sb-title {
        color: #ffffff;
    }

    /* Search */
    .sb-root .sb-search {
        width: 100%;
        min-height: 44px;
        padding: 8px 32px 8px 38px;
        font-size: .875rem;
        color: var(--sb-text);
        background: var(--sb-field);
        border: 1px solid transparent;
        border-radius: 9999px;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
    }

    .sb-root .sb-search::placeholder {
        color: var(--sb-muted);
    }

    .sb-root .sb-search:focus {
        border-color: rgba(56, 189, 248, .75);
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .15);
    }

    /* Group headers */
    .sb-root .sb-group {
        display: flex;
        width: 100%;
        min-height: 44px;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px 8px 10px;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .14em;
        color: var(--sb-group-text);
        background: var(--sb-group-bg);
        border: 1px solid var(--sb-border);
        border-radius: 14px;
        transition: background-color .2s ease, transform .2s ease;
    }

    .sb-root .sb-group:hover {
        background: var(--sb-hover);
    }

    .sb-root .sb-group:active {
        transform: scale(.985);
    }

    /* Icon chip: the first svg in the header becomes a gradient tile */
    .sb-root .sb-group > div > svg:first-child {
        box-sizing: content-box;
        width: 16px;
        height: 16px;
        padding: 6px;
        border-radius: 10px;
        color: #fff;
        background: var(--sb-chip);
        box-shadow: 0 4px 10px -2px rgba(14, 165, 233, .5);
    }

    .sb-root .sb-group--fav > div > svg:first-child {
        background: #f59e0b;
        box-shadow: 0 4px 10px -2px rgba(245, 158, 11, .5);
    }

    .sb-root .sb-group > svg:last-child {
        color: var(--sb-muted);
    }

    /* Menu items */
    .sb-root .sb-item {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        border-radius: 12px;
        color: var(--sb-text);
        transition: background-color .15s ease, color .15s ease;
    }

    .sb-root .sb-item--btn {
        min-height: 40px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 500;
        line-height: 1rem;
        text-align: left;
    }

    .sb-root .sb-item > a > svg:first-child,
    .sb-root .sb-item--btn svg:first-child {
        color: var(--sb-muted);
        transition: color .15s ease, transform .2s ease;
    }

    .sb-root .sb-item:hover {
        background: var(--sb-hover);
    }

    .sb-root .sb-item:hover > a > svg:first-child,
    .sb-root .sb-item--btn:hover svg:first-child {
        color: #38bdf8;
        transform: scale(1.1);
    }

    .sb-root .sb-item.is-active {
        background: var(--sb-active-bg);
        color: var(--sb-active-text);
        font-weight: 600;
        box-shadow: inset 0 0 0 1px rgba(56, 189, 248, .25);
    }

    .sb-root .sb-item.is-active > a > svg:first-child,
    .sb-root .sb-item--btn.is-active svg:first-child {
        color: #38bdf8;
    }

    /* glowing accent bar on the active row */
    .sb-root .sb-item.is-active::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 20%;
        bottom: 20%;
        width: 4px;
        border-radius: 0 4px 4px 0;
        background: var(--sb-accent);
        box-shadow: 0 0 12px rgba(56, 189, 248, .8);
    }

    .sb-root .sb-item:not(.is-active) > a {
        font-weight: 500;
    }

    /* One type scale for every row label (button text does not inherit reliably) */
    .sb-root .sb-item--btn,
    .sb-root .sb-item--btn span,
    .sb-root .sb-item > a,
    .sb-root .sb-item > a span,
    .sb-root .sidebar-link {
        font-size: 12px;
        line-height: 1rem;
    }

    /* Nested links reuse the pill look */
    .sb-root .sidebar-link {
        border-radius: 10px;
        font-size: 12px;
        font-weight: 500;
        line-height: 1rem;
        color: var(--sb-muted);
    }

    .sb-root .sidebar-link:hover {
        color: var(--sb-text);
        background: var(--sb-hover);
    }

    .sb-root .sidebar-link.text-indigo-600 {
        color: var(--sb-active-text);
        background: var(--sb-active-bg);
    }

    .sb-root .chevron {
        color: var(--sb-muted);
    }

    /* Favourite star: quiet until row hover (always visible when starred / on touch) */
    @media (hover: hover) {
        .sb-root .sb-item .favourite-star[data-favourited="0"] {
            opacity: 0;
            transition: opacity .15s ease, color .15s ease;
        }

        .sb-root .sb-item:hover .favourite-star[data-favourited="0"],
        .sb-root .sb-item .favourite-star[data-favourited="0"]:focus-visible {
            opacity: 1;
        }
    }

    .sb-root .favourite-star[data-favourited="1"] svg {
        filter: drop-shadow(0 0 4px rgba(251, 191, 36, .55));
    }

    /* Footer */
    .sb-root .sb-footer {
        padding: 14px 16px;
        text-align: center;
        border-top: 1px solid var(--sb-border);
    }

    .sb-root .sb-footer span {
        font-size: .7rem;
        letter-spacing: .04em;
        color: var(--sb-muted);
    }

    /* Slimmer scrollbar thumb that fits the new look */
    .sb-root.sidebar-scroll::-webkit-scrollbar {
        width: 5px;
    }

    /* ---------- Hierarchy polish ---------- */
    /* kill the heavy browser focus outline, keep a soft keyboard ring */
    .sb-root button:focus,
    .sb-root a:focus {
        outline: none;
    }

    .sb-root button:focus-visible,
    .sb-root a:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, .35);
    }

    /* breathing room between a section card and its rows */
    .sb-root .sb-group + ul {
        margin-top: 10px;
    }

    /* parent rows: lighter, calmer; open state tinted */
    .sb-root .sb-item--btn {
        font-weight: 500;
        min-height: 42px;
    }

    .sb-root .sb-item--btn[aria-expanded="true"] {
        color: var(--sb-active-text);
        background: var(--sb-hover);
    }

    .sb-root .sb-item--btn[aria-expanded="true"] svg:first-child {
        color: #38bdf8;
    }

    .sb-root .sb-item--btn .chevron {
        transition: transform .25s ease;
    }

    /* a parent that merely contains the active page: no pill, no bar */
    .sb-root .sb-item--btn.is-active {
        background: transparent;
        box-shadow: none;
        font-weight: 600;
    }

    .sb-root .sb-item--btn.is-active[aria-expanded="true"] {
        background: var(--sb-hover);
    }

    .sb-root .sb-item--btn.is-active::before {
        display: none;
    }

    /* sub-menu: tree guide line + compact rows */
    .sb-root .sb-sublist {
        margin: 4px 0 6px 21px;
        padding: 0 0 0 10px;
        border-left: 1px solid var(--sb-border);
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .sb-root .sb-subrow {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-radius: 10px;
        transition: background-color .15s ease;
    }

    .sb-root .sb-subrow:hover {
        background: var(--sb-hover);
    }

    .sb-root .sb-sub {
        display: block;
        flex: 1;
        min-width: 0;
        padding: 8px 10px;
        font-size: 12px;
        line-height: 1rem;
        font-weight: 400;
        color: var(--sb-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color .15s ease;
    }

    .sb-root .sb-subrow:hover .sb-sub {
        color: var(--sb-text);
    }

    .sb-root .sb-subrow.is-active {
        background: var(--sb-active-bg);
    }

    .sb-root .sb-subrow.is-active .sb-sub {
        color: var(--sb-active-text);
        font-weight: 600;
    }

    /* active segment lights up on the guide line */
    .sb-root .sb-subrow.is-active::before {
        content: '';
        position: absolute;
        left: -11px;
        top: 18%;
        bottom: 18%;
        width: 3px;
        border-radius: 4px;
        background: var(--sb-accent);
        box-shadow: 0 0 10px rgba(56, 189, 248, .7);
    }

    .sb-root .sb-subrow .favourite-star {
        margin-right: 4px;
        height: 36px;
        width: 36px;
    }

    @media (hover: hover) {
        .sb-root .sb-subrow .favourite-star[data-favourited="0"] {
            opacity: 0;
            transition: opacity .15s ease, color .15s ease;
        }

        .sb-root .sb-subrow:hover .favourite-star[data-favourited="0"] {
            opacity: 1;
        }
    }

    /* =====================================================================
       V3 — clean flat list (settings-menu style). Overrides earlier blocks.
       ===================================================================== */
    .sb-root {
        --sb-fs: 12px;               /* one size for every row label */
        --sb-strong: #0f172a;
        --sb-tint: rgba(14, 165, 233, .11);
        --sb-tint-text: #0369a1;
    }

    .dark .sb-root {
        --sb-strong: #ffffff;
        --sb-tint: rgba(56, 189, 248, .15);
        --sb-tint-text: #7dd3fc;
    }

    .sb-root .sb-logo { box-shadow: none; }

    .sb-root .sb-search {
        min-height: 40px;
        border-radius: 12px;
    }

    /* section labels: plain, quiet */
    .sb-root .sb-group {
        min-height: 32px;
        padding: 6px 12px;
        font-size: 11px;
        letter-spacing: .12em;
        color: var(--sb-muted);
        background: transparent;
        border: 0;
        border-radius: 10px;
    }

    .sb-root .sb-group:hover { background: var(--sb-hover); }

    .sb-root .sb-group > div > svg:first-child,
    .sb-root .sb-group--fav > div > svg:first-child {
        width: 16px;
        height: 16px;
        padding: 0;
        color: var(--sb-muted);
        background: none;
        box-shadow: none;
        border-radius: 0;
    }

    .sb-root .sb-group--fav > div > svg:first-child { color: #f59e0b; }

    .sb-root .sb-group + ul { margin-top: 4px; }

    /* rows */
    .sb-root .sb-item,
    .sb-root .sb-item--btn {
        border-radius: 10px;
        box-shadow: none;
    }

    .sb-root .sb-item--btn {
        min-height: 40px;
        font-weight: 500;
        color: var(--sb-strong);
    }

    .sb-root .sb-item > a { color: var(--sb-strong); }

    .sb-root .sb-item > a > svg:first-child,
    .sb-root .sb-item--btn svg:first-child {
        color: var(--sb-strong);
        opacity: .75;
        transform: none !important;
    }

    .sb-root .sb-item:hover { background: var(--sb-hover); }

    /* active single row + open parent share the same tinted pill */
    .sb-root .sb-item.is-active,
    .sb-root .sb-item--btn[aria-expanded="true"],
    .sb-root .sb-item--btn.is-active[aria-expanded="true"] {
        background: var(--sb-tint);
        color: var(--sb-tint-text);
        box-shadow: none;
        font-weight: 500;
    }

    .sb-root .sb-item.is-active > a,
    .sb-root .sb-item--btn[aria-expanded="true"] { color: var(--sb-tint-text); }

    .sb-root .sb-item.is-active > a > svg:first-child,
    .sb-root .sb-item--btn[aria-expanded="true"] svg:first-child {
        color: var(--sb-tint-text);
        opacity: 1;
    }

    .sb-root .sb-item.is-active::before,
    .sb-root .sb-subrow.is-active::before { display: none; }

    /* closed parent that contains the current page: just a bolder label */
    .sb-root .sb-item--btn.is-active:not([aria-expanded="true"]) {
        background: transparent;
        font-weight: 600;
    }

    /* children: plain indented text, no guide line */
    .sb-root .sb-sublist {
        margin: 2px 0 6px 0;
        padding: 0;
        border-left: 0;
        gap: 0;
    }

    .sb-root .sb-subrow { border-radius: 10px; }

    .sb-root .sb-sub {
        padding: 9px 10px 9px 44px;
        font-weight: 400;
        color: var(--sb-muted);
    }

    .sb-root .sb-subrow:hover { background: transparent; }
    .sb-root .sb-subrow:hover .sb-sub { color: var(--sb-strong); }

    .sb-root .sb-subrow.is-active { background: transparent; }

    .sb-root .sb-subrow.is-active .sb-sub {
        color: var(--sb-strong);
        font-weight: 700;
    }

    /* single type scale */
    .sb-root .sb-item--btn,
    .sb-root .sb-item--btn span,
    .sb-root .sb-item > a,
    .sb-root .sb-item > a span,
    .sb-root .sb-sub {
        font-size: var(--sb-fs);
        line-height: 1rem;
    }

    .sb-root .chevron,
    .sb-root .sb-group > svg:last-child { color: var(--sb-muted); }

    /* ---------- Section headers (flat, no gradient) ---------- */
    .sb-root {
        --sb-hdr-text: #334155;
        --sb-hdr-tile: rgba(14, 165, 233, .13);
        --sb-hdr-icon: #0284c7;
    }

    .dark .sb-root {
        --sb-hdr-text: #e2e8f0;
        --sb-hdr-tile: rgba(56, 189, 248, .16);
        --sb-hdr-icon: #7dd3fc;
    }

    .sb-root .sb-group {
        min-height: 44px;
        padding: 6px 8px 6px 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .14em;
        color: var(--sb-hdr-text);
        border-radius: 12px;
    }

    .sb-root .sb-group > div {
        gap: 12px;
    }

    .sb-root .sb-group > div > svg:first-child {
        box-sizing: content-box;
        width: 16px;
        height: 16px;
        padding: 6px;
        color: var(--sb-hdr-icon);
        background: var(--sb-hdr-tile);
        border-radius: 9px;
        transition: background-color .2s ease, color .2s ease;
    }

    .sb-root .sb-group:hover > div > svg:first-child {
        background: var(--sb-hdr-icon);
        color: #fff;
    }

    .sb-root .sb-group--fav > div > svg:first-child {
        color: #d97706;
        background: rgba(245, 158, 11, .15);
    }

    .sb-root .sb-group--fav:hover > div > svg:first-child {
        background: #f59e0b;
        color: #fff;
    }

    .sb-root .sb-group--fav { color: #b45309; }
    .dark .sb-root .sb-group--fav { color: #fcd34d; }

    /* chevron in a small round button */
    .sb-root .sb-group > svg:last-child {
        box-sizing: content-box;
        width: 12px;
        height: 12px;
        padding: 5px;
        border-radius: 6px;
        color: var(--sb-hdr-icon);
        transition: background-color .2s ease, transform .25s ease;
    }

    .sb-root .sb-group:hover > svg:last-child {
        background: var(--sb-hdr-tile);
    }

    /* hairline between a section and the next */
    .sb-root li.mt-4 { margin-top: 18px; }

    /* ---------- Section headers: whole bar styled, not just the icon ---------- */
    .sb-root .sb-group {
        background: var(--sb-tint);
        border: 0;
        border-radius: 8px;
        padding: 6px 10px 6px 6px;
        color: var(--sb-hdr-text);
    }

    .sb-root .sb-group:hover {
        background: rgba(14, 165, 233, .17);
    }

    .sb-root .sb-group > div > svg:first-child {
        background: #0ea5e9;
        color: #fff;
        border-radius: 6px;
    }

    .sb-root .sb-group:hover > div > svg:first-child {
        background: #0284c7;
    }

    .sb-root .sb-group > svg:last-child {
        background: rgba(14, 165, 233, .16);
        color: #0284c7;
    }

    .dark .sb-root .sb-group {
        border: 0;
    }

    .dark .sb-root .sb-group > svg:last-child {
        color: #7dd3fc;
    }

    .sb-root .sb-group--fav {
        background: rgba(245, 158, 11, .10);
        border: 0;
    }

    .sb-root .sb-group--fav:hover { background: rgba(245, 158, 11, .17); }

    .sb-root .sb-group--fav > div > svg:first-child { background: #f59e0b; color: #fff; }
    .sb-root .sb-group--fav:hover > div > svg:first-child { background: #d97706; }

    .sb-root .sb-group--fav > svg:last-child {
        background: rgba(245, 158, 11, .18);
        color: #d97706;
    }

    /* ---------- Section header icon: plain, no background tile ---------- */
    .sb-root .sb-group > div > svg:first-child,
    .sb-root .sb-group:hover > div > svg:first-child,
    .sb-root .sb-group--fav > div > svg:first-child,
    .sb-root .sb-group--fav:hover > div > svg:first-child {
        width: 20px;
        height: 20px;
        padding: 0;
        margin-left: 6px;
        background: none;
        border-radius: 0;
        color: #0284c7;
    }

    .dark .sb-root .sb-group > div > svg:first-child,
    .dark .sb-root .sb-group:hover > div > svg:first-child {
        color: #7dd3fc;
    }

    .sb-root .sb-group--fav > div > svg:first-child,
    .sb-root .sb-group--fav:hover > div > svg:first-child {
        color: #d97706;
    }

    @media (prefers-reduced-motion: reduce) {
        .sb-root *, .sb-root *::before {
            transition: none !important;
        }
    }

    /* ================= SIDEBAR SCROLLBAR ================= */
    .sidebar-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgb(209 213 219) transparent;
        /* gray-300 thumb */
    }

    .sidebar-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-scroll::-webkit-scrollbar-thumb {
        background-color: rgb(209 213 219);
        /* gray-300 */
        border-radius: 9999px;
    }

    .sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background-color: rgb(156 163 175);
        /* gray-400 */
    }

    .dark .sidebar-scroll {
        scrollbar-color: rgb(75 85 99) transparent;
        /* gray-600 thumb */
    }

    .dark .sidebar-scroll::-webkit-scrollbar-thumb {
        background-color: rgb(75 85 99);
        /* gray-600 */
    }

    .dark .sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background-color: rgb(107 114 128);
        /* gray-500 */
    }
</style>
