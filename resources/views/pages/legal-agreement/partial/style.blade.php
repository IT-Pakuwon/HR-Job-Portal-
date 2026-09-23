<style>
    :root {
        --agr-bg: #f8fafc;
        --agr-card: #ffffff;
        --agr-border: #e2e8f0;
        --agr-border-soft: #edf2f7;
        --agr-text: #0f172a;
        --agr-muted: #64748b;
        --agr-muted-2: #94a3b8;
        --agr-hover: #f8fafc;
        --agr-primary: #2563eb;
        --agr-primary-soft: #2563eb1a;
        --agr-shadow:
            0 1px 2px rgba(15, 23, 42, .04),
            0 8px 24px rgba(15, 23, 42, .06);
    }

    .dark {
        --agr-bg: #020617;
        --agr-card: #0f172a;
        --agr-border: rgba(255, 255, 255, .08);
        --agr-border-soft: rgba(255, 255, 255, .04);
        --agr-text: #f8fafc;
        --agr-muted: #cbd5e1;
        --agr-muted-2: #64748b;
        --agr-hover: #172033;
        --agr-primary: #3b82f6;
        --agr-primary-soft: #3b82f624;
        --agr-shadow:
            0 1px 2px rgba(0, 0, 0, .2),
            0 12px 32px rgba(0, 0, 0, .35);
    }

    body {
        background: var(--agr-bg);
    }

    .swal2-container {
        z-index: 999999 !important;
    }

    .agr-modal {
        backdrop-filter: blur(4px);
    }

    .modal-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .modal-scroll::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .35);
        border-radius: 999px;
    }

    .agr-label {
        display: block;
        margin-bottom: 10px;
        font-size: 13px;
        font-weight: 600;
        color: var(--agr-muted);
    }

    .agr-section {
        background: var(--agr-bg);
        transition: border-color .18s ease;
    }

    .agr-section-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .agr-section-badge {
        display: flex;
        height: 24px;
        width: 24px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: var(--agr-primary-soft);
        color: var(--agr-primary);
        font-size: 12px;
        font-weight: 700;
    }

    .agr-section-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: var(--agr-text);
    }

    .agr-file {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        min-height: 48px;
        padding: 0 16px;
        border: 1px dashed var(--agr-border);
        border-radius: 8px;
        background: var(--agr-card);
        color: var(--agr-muted-2);
        font-size: 14px;
        transition: all .18s ease;
    }

    .agr-file i {
        color: var(--agr-muted-2);
    }

    .agr-file:has(input:focus) {
        border-color: var(--agr-primary);
        border-style: solid;
        box-shadow: 0 0 0 4px var(--agr-primary-soft);
    }

    .agr-file input[type="file"] {
        flex: 1;
        border: none;
        background: transparent;
        font-size: 13px;
        color: var(--agr-text);
        padding: 10px 0;
    }

    .agr-file input[type="file"]::file-selector-button {
        margin-right: 14px;
        padding: 8px 14px;
        border: none;
        border-radius: 6px;
        background: var(--agr-primary-soft);
        color: var(--agr-primary);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all .18s ease;
    }

    .agr-file input[type="file"]::file-selector-button:hover {
        background: var(--agr-primary);
        color: #ffffff;
    }

    .agr-input,
    .agr-textarea {
        width: 100%;
        border: 1px solid var(--agr-border);
        background: var(--agr-card);
        border-radius: 8px;
        font-size: 14px;
        color: var(--agr-text);
        transition: all .18s ease;
    }

    .agr-input {
        height: 48px;
        padding: 0 16px;
    }

    .agr-textarea {
        padding: 14px 16px;
        resize: none;
    }

    .agr-input::placeholder,
    .agr-textarea::placeholder {
        color: var(--agr-muted-2);
    }

    .agr-input:focus,
    .agr-textarea:focus {
        border-color: var(--agr-primary) !important;
        box-shadow: 0 0 0 4px var(--agr-primary-soft);
        outline: none;
    }

    .agr-input-error,
    .agr-input-error.select2-selection {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, .12) !important;
    }

    .agr-locked .select2-selection {
        background: var(--agr-hover) !important;
        color: var(--agr-muted-2) !important;
        cursor: not-allowed !important;
        pointer-events: none;
    }

    input.agr-locked,
    textarea.agr-locked {
        background: var(--agr-hover) !important;
        color: var(--agr-muted-2) !important;
        cursor: not-allowed !important;
    }

    .agr-edit-toggle {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .agr-locked .select2-selection .select2-selection__arrow {
        display: none;
    }

    /* Step indicator */
    .agr-steps {
        display: flex;
        align-items: center;
    }

    .agr-step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        color: var(--agr-muted-2);
    }

    .agr-step-circle {
        position: relative;
        display: flex;
        height: 32px;
        width: 32px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 2px solid var(--agr-border);
        background: var(--agr-card);
        font-size: 13px;
        font-weight: 700;
        color: var(--agr-muted-2);
        transition: all .18s ease;
    }

    .agr-step-circle i {
        display: none;
        font-size: 13px;
    }

    .agr-step-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .03em;
        white-space: nowrap;
    }

    .agr-step-line {
        height: 2px;
        flex: 1;
        margin: 0 8px 22px;
        background: var(--agr-border);
        transition: background .18s ease;
    }

    .agr-step-item.is-active .agr-step-circle {
        border-color: var(--agr-primary);
        background: var(--agr-primary);
        color: #ffffff;
    }

    .agr-step-item.is-active .agr-step-label {
        color: var(--agr-primary);
    }

    .agr-step-item.is-done .agr-step-circle {
        border-color: var(--agr-primary);
        background: var(--agr-primary-soft);
        color: var(--agr-primary);
    }

    .agr-step-item.is-done .agr-step-circle .agr-step-num {
        display: none;
    }

    .agr-step-item.is-done .agr-step-circle i {
        display: block;
    }

    .agr-step-item.is-done + .agr-step-line {
        background: var(--agr-primary);
    }

    #agreementTable_wrapper,
    .dataTables_wrapper,
    .dataTables_scroll,
    .dataTables_scrollBody,
    .table-responsive {
        overflow: visible !important;
    }

    .dataTables_wrapper .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-size: 13px;
        color: var(--agr-muted-2);
    }

    .dataTables_wrapper .dataTables_filter input {
        height: 36px;
        padding: 0 12px;
        border: 1px solid var(--agr-border);
        background: var(--agr-card);
        border-radius: 8px;
        font-size: 13px;
        color: var(--agr-text);
        outline: none;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--agr-primary);
        box-shadow: 0 0 0 4px var(--agr-primary-soft);
    }

    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--agr-border);
        background: var(--agr-card);
        border-radius: 8px;
        color: var(--agr-text);
    }

    #agreementTable tbody tr,
    #agreementTable tbody td {
        overflow: visible !important;
    }

    /* Select2 */
    .agr-select2 + .select2-container {
        width: 100% !important;
    }

    .agr-select2 + .select2-container .select2-selection--single {
        height: 48px !important;
        border-radius: 8px !important;
        border-color: var(--agr-border) !important;
        background: var(--agr-card);
        display: flex;
        align-items: center;
        padding: 0 16px;
        transition: all .18s ease;
    }

    .agr-select2 + .select2-container .select2-selection--single .select2-selection__rendered {
        padding: 0;
        line-height: normal;
        color: var(--agr-text);
    }

    .agr-select2 + .select2-container .select2-selection--single .select2-selection__placeholder {
        color: var(--agr-muted-2);
    }

    .agr-select2 + .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
        right: 12px;
    }

    .agr-select2 + .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: var(--agr-muted) transparent transparent transparent;
    }

    .agr-select2 + .select2-container .select2-selection--multiple {
        min-height: 48px !important;
        border-radius: 8px !important;
        border-color: var(--agr-border) !important;
        background: var(--agr-card);
        padding: 6px 8px;
        transition: all .18s ease;
    }

    .agr-select2 + .select2-container .select2-selection--multiple .select2-selection__choice {
        background: var(--agr-primary-soft);
        border-color: var(--agr-primary) !important;
        color: var(--agr-primary);
        border-radius: 6px;
        padding: 2px 8px;
        margin-top: 4px;
    }

    .agr-select2 + .select2-container .select2-selection--multiple .select2-selection__choice__remove {
        color: var(--agr-primary);
        margin-right: 6px;
    }

    .agr-select2 + .select2-container--open .select2-selection--multiple {
        border-color: var(--agr-primary) !important;
        box-shadow: 0 0 0 4px var(--agr-primary-soft);
    }

    .agr-select2 + .select2-container--open .select2-selection--single {
        border-color: var(--agr-primary) !important;
        box-shadow: 0 0 0 4px var(--agr-primary-soft);
    }

    .select2-dropdown {
        border-color: var(--agr-border) !important;
        border-radius: 8px !important;
        overflow: hidden;
        box-shadow: var(--agr-shadow);
        /* Dropdowns append to <body>, as siblings of the .agr-modal wrappers
           (z-index up to 9999) — without this they paint behind the modal
           instead of floating above it. */
        z-index: 10000;
    }

    .select2-search--dropdown {
        padding: 8px;
    }

    .select2-search--dropdown .select2-search__field {
        border: 1px solid var(--agr-border) !important;
        border-radius: 6px !important;
        padding: 8px 10px !important;
        background: var(--agr-card);
        color: var(--agr-text);
        outline: none;
    }

    .select2-results__option {
        padding: 8px 14px !important;
        font-size: 14px;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--agr-primary) !important;
        color: #ffffff !important;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: var(--agr-hover);
    }

    .dark .select2-dropdown,
    .dark .select2-search--dropdown .select2-search__field {
        background-color: var(--agr-card);
        color: var(--agr-text);
    }

    .dark .select2-results__option {
        color: var(--agr-text);
    }

    .agr-detail-tab {
        color: #64748b;
        background: transparent;
    }

    .dark .agr-detail-tab {
        color: #94a3b8;
    }

    .agr-detail-tab:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .dark .agr-detail-tab:hover {
        background: rgba(255, 255, 255, .05);
        color: #fff;
    }

    .agr-detail-tab.active {
        background: #0f172a;
        color: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
    }

    .dark .agr-detail-tab.active {
        background: #fff;
        color: #0f172a;
    }
</style>
