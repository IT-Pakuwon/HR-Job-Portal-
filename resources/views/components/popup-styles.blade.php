{{-- Shared look for popups that are tagged with role classes (same as the Location / Vendor popups).
     app-popup (overlay) > app-dialog > app-head (+ app-close) | app-tabs | app-body | app-foot
     Also styles tables and fields that page scripts build inside the popup. Add <x-popup-styles /> once per page. --}}
<style>
    .app-popup:not(:has(> .app-popup-shade)),
    .app-popup > .app-popup-shade { background: rgba(15, 23, 42, .5); -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px); }

    .app-popup .app-dialog {
        background: #fff; border-radius: 1rem;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, .35), 0 0 0 1px rgba(15, 23, 42, .05);
    }
    .app-popup .app-dialog:not([class*="overflow"]) { overflow: hidden; } /* scrolling dialogs keep their own overflow */
    .app-popup .app-dialog-padded { padding: 0; }
    .app-popup #tlList { margin: 0; padding: 1.5rem; }
    .app-popup .app-head {
        display: flex; flex-direction: row; align-items: center; justify-content: space-between; gap: .75rem;
        margin: 0; padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9;
    }
    .app-popup .app-head h2, .app-popup .app-head h3 { font-size: 1rem; line-height: 1.4; font-weight: 700; color: #1f2937; }
    .app-popup .app-head h3 + h3 { font-size: .75rem; font-weight: 500; color: #6b7280; }
    .app-popup .app-head h3:empty { display: none; }
    .app-popup #trackDoc { color: var(--color-blue-600); }
    .app-popup .app-close {
        display: inline-flex; flex-shrink: 0; align-items: center; justify-content: center; width: 2rem; height: 2rem;
        border-radius: .5rem; color: #9ca3af; transition: background-color .15s, color .15s;
    }
    .app-popup .app-close:hover { background: #f3f4f6; color: #374151; }

    .app-popup .app-body { padding: 1rem 1.5rem; }
    .app-popup .app-body + .app-body { padding-top: 0; }
    .app-popup .app-tabs { padding: 0 1.5rem; border-bottom: 1px solid #f1f5f9; }
    .app-popup > p, .app-popup .app-dialog > p { margin: 0; padding: 1rem 1.5rem 0; }
    .app-popup .app-foot {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .75rem; margin: 0;
        padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9; background: #fbfcfd; text-align: right;
    }
    .app-popup .app-btn, .app-popup .app-btn-primary {
        display: inline-flex; align-items: center; justify-content: center; border-radius: .5rem; padding: .625rem 1.5rem;
        font-size: .875rem; font-weight: 600; box-shadow: 0 1px 2px rgba(0, 0, 0, .05); transition: background-color .15s;
    }
    .app-popup .app-btn { border: 1px solid #d1d5db; background: #fff; color: #374151; }
    .app-popup .app-btn:hover { background: #f3f4f6; }
    .app-popup .app-btn-primary { border: 1px solid var(--color-blue-600); background: var(--color-blue-600); color: #fff; }
    .app-popup .app-btn-primary:hover { background: var(--color-blue-700); }

    /* tables, including the ones page scripts build inside the popup */
    .app-popup table { width: 100%; }
    .app-popup table thead th {
        padding: .625rem .875rem; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
        font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280;
    }
    .app-popup table tbody td { padding: .625rem .875rem; border-bottom: 1px solid #f1f5f9; font-size: .8125rem; color: #374151; }
    .app-popup table tbody tr:hover td { background: #f8fafc; }

    .app-popup .app-body input[type="text"]:not(.dataTables_filter input), .app-popup .app-body input[type="search"], .app-popup .app-body select:not(.dataTables_length select) {
        min-height: 2.5rem; border: 1px solid #d1d5db; border-radius: .5rem; background-color: #fff; font-size: .875rem; color: #1f2937;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .05); transition: border-color .15s, box-shadow .15s;
    }
    .app-popup .app-body input:focus, .app-popup .app-body select:focus { outline: none; border-color: var(--color-blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
    .app-popup .app-body label:not(.dataTables_wrapper label) { font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; }

    /* tracking tabs as pills */
    .app-popup .app-tabs .track-tab, .app-popup .app-tabs .track-tab-sppb {
        border: 1px solid transparent; border-radius: 9999px; padding: .375rem 1rem; font-size: .8125rem; font-weight: 600;
        background: transparent; color: #6b7280; transition: background-color .15s, color .15s;
    }
    .app-popup .app-tabs .track-tab:hover, .app-popup .app-tabs .track-tab-sppb:hover { background: #f3f4f6; color: #374151; }
    .app-popup .app-tabs .track-tab.active, .app-popup .app-tabs .track-tab-sppb.active { background: var(--color-blue-600); color: #fff; }

    .dark .app-popup .app-dialog { background: #1f2937; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, .6), 0 0 0 1px rgba(255, 255, 255, .08); }
    .dark .app-popup .app-head, .dark .app-popup .app-tabs { border-color: #374151; }
    .dark .app-popup .app-head h2, .dark .app-popup .app-head h3 { color: #fff; }
    .dark .app-popup #trackDoc { color: var(--color-blue-300); }
    .dark .app-popup .app-close:hover { background: #374151; color: #e5e7eb; }
    .dark .app-popup .app-foot { background: rgba(255, 255, 255, .02); border-color: #374151; }
    .dark .app-popup .app-btn { background: #374151; border-color: #4b5563; color: #e5e7eb; }
    .dark .app-popup table thead th { background: rgba(255, 255, 255, .04); border-color: #374151; color: #9ca3af; }
    .dark .app-popup table tbody td { border-color: #374151; color: #e5e7eb; }
    .dark .app-popup table tbody tr:hover td { background: rgba(255, 255, 255, .04); }
    .dark .app-popup .app-body input, .dark .app-popup .app-body select { background-color: #111827; border-color: #4b5563; color: #f3f4f6; }
</style>
