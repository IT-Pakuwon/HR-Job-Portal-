<style>
    :root {
        --ticket-bg: #f8fafc;
        --ticket-card: #ffffff;
        --ticket-border: #e2e8f0;
        --ticket-border-soft: #edf2f7;
        --ticket-text: #0f172a;
        --ticket-muted: #64748b;
        --ticket-muted-2: #94a3b8;
        --ticket-hover: #f8fafc;
        --ticket-primary: #2563eb;
        --ticket-primary-soft: #2563eb1a;
        --ticket-shadow:
            0 1px 2px rgba(15, 23, 42, .04),
            0 8px 24px rgba(15, 23, 42, .06);
    }

    .dark {
        --ticket-bg: #020617;
        --ticket-card: #0f172a;
        --ticket-border: rgba(255, 255, 255, .08);
        --ticket-border-soft: rgba(255, 255, 255, .04);
        --ticket-text: #f8fafc;
        --ticket-muted: #cbd5e1;
        --ticket-muted-2: #64748b;
        --ticket-hover: #172033;
        --ticket-primary: #3b82f6;
        --ticket-primary-soft: #3b82f624;
        --ticket-shadow:
            0 1px 2px rgba(0, 0, 0, .2),
            0 12px 32px rgba(0, 0, 0, .35);
    }

    body {
        background: var(--ticket-bg);
    }

    .swal2-container {
        z-index: 999999 !important;
    }

    .ticket-modal {
        backdrop-filter: blur(4px);
    }

    .modal-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .modal-scroll::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .35);
        border-radius: 999px;
    }

    .ticket-label {
        display: block;

        margin-bottom: 10px;

        font-size: 13px;
        font-weight: 600;

        color: var(--ticket-muted);
    }

    .ticket-input,
    .ticket-textarea {
        width: 100%;

        border: 1px solid var(--ticket-border);

        background: var(--ticket-card);

        border-radius: 8px;

        font-size: 14px;

        color: var(--ticket-text);

        transition: all .18s ease;
    }

    .ticket-input {
        height: 48px;
        padding: 0 16px;
    }

    .ticket-textarea {
        padding: 14px 16px;
        resize: none;
    }

    .ticket-input::placeholder,
    .ticket-textarea::placeholder {
        color: var(--ticket-muted-2);
    }

    .ticket-input:focus,
    .ticket-textarea:focus {
        border-color: var(--ticket-primary) !important;

        box-shadow:
            0 0 0 4px var(--ticket-primary-soft);

        outline: none;
    }

</style>

<style>
    .ticket-detail-tab {

        color: #64748b;

        background: transparent;
    }

    .dark .ticket-detail-tab {

        color: #94a3b8;
    }

    .ticket-detail-tab:hover {

        background: #f8fafc;

        color: #0f172a;
    }

    .dark .ticket-detail-tab:hover {

        background: rgba(255, 255, 255, .05);

        color: #fff;
    }

    .ticket-detail-tab.active {

        background: #0f172a;

        color: #fff;

        box-shadow:
            0 10px 30px rgba(15, 23, 42, .18);
    }

    .dark .ticket-detail-tab.active {

        background: #fff;

        color: #0f172a;
    }
        .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter{
        padding:16px 18px;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate{
        padding:14px 18px;
    }

    #ticketTable_wrapper,
    .dataTables_wrapper,
    .dataTables_scroll,
    .dataTables_scrollBody,
    .table-responsive {
        overflow: visible !important;
    }

    #ticketTable tbody tr,
    #ticketTable tbody td {
        overflow: visible !important;
    }

    #detail_issue_descr .ql-editor img {
        max-width: 100%;
        height: auto;
        border-radius: 6px;
        display: block;
        margin: 4px 0;
    }

    /* Quill dark mode */
    .dark .ql-toolbar.ql-snow {
        background: #0b1220 !important;
        border-color: rgba(255, 255, 255, .1) !important;
        border-bottom-color: rgba(255, 255, 255, .06) !important;
    }

    .dark .ql-container.ql-snow {
        background: #0b1220 !important;
        border-color: rgba(255, 255, 255, .1) !important;
        color: #f8fafc !important;
    }

    .dark .ql-editor.ql-blank::before {
        color: #64748b !important;
    }

    .dark .ql-snow .ql-stroke {
        stroke: #94a3b8 !important;
    }

    .dark .ql-snow .ql-fill,
    .dark .ql-snow .ql-stroke.ql-fill {
        fill: #94a3b8 !important;
    }

    .dark .ql-snow button:hover .ql-stroke,
    .dark .ql-snow .ql-picker-label:hover .ql-stroke {
        stroke: #f8fafc !important;
    }

    .dark .ql-snow button:hover .ql-fill,
    .dark .ql-snow .ql-picker-label:hover .ql-fill {
        fill: #f8fafc !important;
    }

    .dark .ql-snow .ql-picker-label {
        color: #94a3b8 !important;
    }

    .dark .ql-snow .ql-picker-label:hover {
        color: #f8fafc !important;
    }

    .dark .ql-snow button.ql-active .ql-stroke,
    .dark .ql-snow .ql-picker-label.ql-active .ql-stroke {
        stroke: #3b82f6 !important;
    }

    .dark .ql-snow button.ql-active .ql-fill,
    .dark .ql-snow .ql-picker-label.ql-active .ql-fill {
        fill: #3b82f6 !important;
    }

    .dark .ql-snow button.ql-active {
        color: #3b82f6 !important;
    }
</style>
