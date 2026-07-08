<style>
    /* ================= SIDEBAR LINKS ================= */
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 6px 12px;
        border-radius: 0.5rem;
        color: rgb(75 85 99);
        /* gray-600 */
        transition: background-color .15s ease, color .15s ease;
    }

    .sidebar-link::before {
        content: '';
        width: 5px;
        height: 5px;
        flex-shrink: 0;
        border-radius: 9999px;
        background-color: rgb(209 213 219);
        /* gray-300 */
        transition: background-color .15s ease;
    }

    .sidebar-link:hover {
        color: rgb(17 24 39);
        /* gray-900 */
        background-color: rgb(243 244 246);
        /* gray-100 */
    }

    .sidebar-link:hover::before {
        background-color: rgb(156 163 175);
        /* gray-400 */
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

    .dark .sidebar-link::before {
        background-color: rgb(75 85 99);
        /* gray-600 */
    }

    .dark .sidebar-link:hover {
        color: rgb(243 244 246);
        /* gray-100 */
        background-color: rgba(255, 255, 255, .05);
    }

    .dark .sidebar-link:hover::before {
        background-color: rgb(156 163 175);
        /* gray-400 */
    }

    .dark .sidebar-link.text-indigo-600 {
        background-color: rgba(99, 102, 241, .15);
    }

    .dark .sidebar-link.text-indigo-600::before {
        background-color: rgb(165 180 252);
        /* indigo-300 */
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
</style>
