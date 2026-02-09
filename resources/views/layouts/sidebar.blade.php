<style>
    /* ================= SIDEBAR LINKS ================= */
    .sidebar-link {
        display: block;
        padding: 6px 12px;
        color: rgb(75 85 99);
        /* gray-600 */
    }

    .sidebar-link:hover {
        color: rgb(17 24 39);
        /* gray-900 */
    }

    /* Dark mode */
    .dark .sidebar-link {
        color: rgb(156 163 175);
        /* gray-400 */
    }

    .dark .sidebar-link:hover {
        color: rgb(243 244 246);
        /* gray-100 */
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
