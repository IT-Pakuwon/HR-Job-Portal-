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

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 48px !important;

        display: flex !important;
        align-items: center !important;

        border-radius: 8px !important;

        border: 1px solid var(--ticket-border) !important;

        background: var(--ticket-card) !important;

        padding: 0 16px !important;

        transition: all .18s ease !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,

    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--ticket-primary) !important;

        box-shadow:
            0 0 0 4px var(--ticket-primary-soft) !important;
    }

    .select2-selection__rendered {
        line-height: 46px !important;

        padding-left: 0 !important;

        font-size: 14px !important;
        font-weight: 500 !important;

        color: var(--ticket-text) !important;
    }

    .select2-selection__placeholder {
        color: var(--ticket-muted-2) !important;
    }

    .select2-selection__arrow {
        height: 46px !important;
        right: 14px !important;
    }

    .select2-dropdown {
        overflow: hidden !important;

        margin-top: 8px !important;

        border: none !important;

        border-radius: 8px !important;

        background: var(--ticket-card) !important;

        box-shadow:
            0 20px 50px rgba(15, 23, 42, .16) !important;
    }

    .dark .select2-dropdown {
        box-shadow:
            0 20px 60px rgba(0, 0, 0, .42) !important;
    }

    .select2-search--dropdown {
        padding: 14px !important;
    }

    .select2-search__field {
        height: 42px !important;

        border-radius: 8px !important;

        border: 1px solid var(--ticket-border) !important;

        background: var(--ticket-card) !important;

        color: var(--ticket-text) !important;

        padding: 0 14px !important;

        font-size: 13px !important;
    }

    .select2-search__field:focus {
        border-color: var(--ticket-primary) !important;

        box-shadow:
            0 0 0 4px var(--ticket-primary-soft) !important;
    }

    .select2-results {
        padding: 8px !important;
    }

    .select2-results__option {
        min-height: 42px !important;

        display: flex !important;
        align-items: center !important;

        border-radius: 8px !important;

        padding: 10px 14px !important;

        font-size: 13px !important;
        font-weight: 500 !important;

        color: var(--ticket-text) !important;

        transition: all .15s ease !important;
    }

    .select2-results__option--highlighted {
        background: var(--ticket-primary) !important;
        color: #ffffff !important;
    }

    .select2-results__option--selectable:hover {
        background: rgba(148, 163, 184, .10) !important;
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
</style>
