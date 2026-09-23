<style>
    .perizinan-modal {
        backdrop-filter: blur(4px);
    }

    .perizinan-modal .modal-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .perizinan-modal .modal-scroll::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .35);
        border-radius: 999px;
    }

    /* Select2 — match h-11 rounded-lg input look */
    .perizinan-select2 + .select2-container,
    .user-select2 + .select2-container {
        width: 100% !important;
    }

    .perizinan-select2 + .select2-container .select2-selection--single {
        height: 44px !important;
        border-radius: 8px !important;
        border-color: #e2e8f0 !important;
        display: flex;
        align-items: center;
        padding: 0 12px;
    }

    .perizinan-select2 + .select2-container .select2-selection--single .select2-selection__rendered {
        padding: 0;
        line-height: normal;
        color: #0f172a;
    }

    .perizinan-select2 + .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
        right: 8px;
    }

    .perizinan-select2 + .select2-container.select2-container--disabled .select2-selection--single {
        background-color: #f8fafc;
        cursor: not-allowed;
    }

    .user-select2 + .select2-container .select2-selection--multiple {
        min-height: 44px !important;
        border-radius: 8px !important;
        border-color: #e2e8f0 !important;
        padding: 4px 8px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #94a3b8 !important;
    }

    /* Select2 — dark mode */
    .dark .perizinan-select2 + .select2-container .select2-selection--single,
    .dark .user-select2 + .select2-container .select2-selection--multiple {
        background-color: #0b1220;
        border-color: rgba(255, 255, 255, .1) !important;
    }

    .dark .perizinan-select2 + .select2-container .select2-selection--single .select2-selection__rendered {
        color: #f8fafc;
    }

    .dark .perizinan-select2 + .select2-container .select2-selection--single .select2-selection__placeholder {
        color: #64748b;
    }

    .dark .perizinan-select2 + .select2-container .select2-selection--single .select2-selection__arrow b {
        border-color: #94a3b8 transparent transparent transparent;
    }

    .dark .perizinan-select2 + .select2-container.select2-container--disabled .select2-selection--single {
        background-color: #0f172a;
        border-color: rgba(255, 255, 255, .06) !important;
        cursor: not-allowed;
    }

    .dark .perizinan-select2 + .select2-container.select2-container--disabled .select2-selection--single .select2-selection__rendered {
        color: #64748b;
    }

    .dark .user-select2 + .select2-container .select2-selection--multiple .select2-selection__choice {
        background-color: #1e293b;
        border-color: rgba(255, 255, 255, .1);
        color: #f8fafc;
    }

    .dark .select2-dropdown {
        background-color: #0f172a;
        border-color: rgba(255, 255, 255, .1);
        color: #f8fafc;
    }

    .dark .select2-search--dropdown .select2-search__field {
        background-color: #0b1220;
        border-color: rgba(255, 255, 255, .1);
        color: #f8fafc;
    }

    .dark .select2-results__option {
        color: #e2e8f0;
    }

    .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5;
        color: #ffffff;
    }

    .dark .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #1e293b;
        color: #f8fafc;
    }
</style>
