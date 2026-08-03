{{-- Shared dark-theme styling, included by both the admin and audience spinwheel views. --}}

<style>
    header.sticky {
        display: none;
    }

    @keyframes spinBtnGlow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(236, 72, 153, .55); }
        50% { box-shadow: 0 0 0 14px rgba(236, 72, 153, 0); }
    }

    #spinBtn:not(:disabled) {
        animation: spinBtnGlow 2s ease-in-out infinite;
    }

    #spinwheelRoot:fullscreen,
    #spinwheelRoot:-webkit-full-screen {
        height: 100dvh;
        width: 100vw;
        max-width: none;
        border-radius: 0;
    }

    .spinwheel-dark .neon-dots {
        background-image: radial-gradient(rgba(236, 72, 153, .35) 1px, transparent 1px);
        background-size: 20px 20px;
    }

    .spinwheel-dark .neon-title {
        background: linear-gradient(90deg, #f472b6, #a855f7, #818cf8);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        filter: drop-shadow(0 0 14px rgba(236, 72, 153, .45));
    }

    /* selects rendered inside the dark widget */
    .spinwheel-dark .select2-container--default .select2-selection--single {
        background: rgba(255, 255, 255, .06) !important;
        border: 1px solid rgba(255, 255, 255, .15) !important;
        height: 46px !important;
        border-radius: .75rem !important;
    }

    .spinwheel-dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f1f5f9 !important;
        line-height: 44px !important;
        padding-left: 14px !important;
    }

    .spinwheel-dark .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
    }

    .spinwheel-dark .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #f472b6 transparent transparent transparent !important;
    }

    .spinwheel-dark .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #f472b6 transparent !important;
    }

    .spinwheel-dark .select2-selection__placeholder {
        color: rgba(241, 245, 249, .6) !important;
    }

    /* dropdown panel is appended to <body>, styled via dropdownCssClass */
    .sw-select2-dropdown {
        background: #171532 !important;
        border: 1px solid rgba(244, 114, 182, .3) !important;
    }

    .sw-select2-dropdown .select2-search__field {
        background: #201c40 !important;
        border: 1px solid rgba(255, 255, 255, .15) !important;
        color: #fff !important;
    }

    .sw-select2-dropdown .select2-results__option {
        color: #e2e8f0 !important;
    }

    .sw-select2-dropdown .select2-results__option--highlighted {
        background: linear-gradient(90deg, #ec4899, #8b5cf6) !important;
        color: #fff !important;
    }

    .sw-select2-dropdown .select2-results__option[aria-selected="true"] {
        background: rgba(236, 72, 153, .2) !important;
    }

    /* DataTable dark reskin, scoped to this widget only */
    .spinwheel-dark .dataTables_wrapper,
    .spinwheel-dark .dataTables_info,
    .spinwheel-dark .dataTables_length,
    .spinwheel-dark .dataTables_filter {
        color: #cbd5e1 !important;
        font-size: 1rem !important;
    }

    .spinwheel-dark .dataTables_length select,
    .spinwheel-dark .dataTables_filter input {
        background: rgba(255, 255, 255, .06) !important;
        border: 1px solid rgba(255, 255, 255, .15) !important;
        color: #f1f5f9 !important;
        border-radius: .5rem !important;
        font-size: 1rem !important;
        padding: .35rem .6rem !important;
    }

    .spinwheel-dark table.dataTable thead th {
        color: #f1f5f9 !important;
        border-bottom: 1px solid rgba(255, 255, 255, .15) !important;
        font-size: 1rem !important;
        padding-top: .85rem !important;
        padding-bottom: .85rem !important;
    }

    .spinwheel-dark table.dataTable tbody td {
        border-top: 1px solid rgba(255, 255, 255, .08) !important;
        color: #e2e8f0;
        padding-top: .85rem !important;
        padding-bottom: .85rem !important;
    }

    .spinwheel-dark .dataTables_paginate .paginate_button {
        font-size: 1rem !important;
    }

    .spinwheel-dark table.dataTable.stripe tbody tr.odd,
    .spinwheel-dark table.dataTable tbody tr {
        background: transparent !important;
    }

    .spinwheel-dark table.dataTable tbody tr:hover {
        background: rgba(236, 72, 153, .06) !important;
    }

    .spinwheel-dark .dataTables_paginate .paginate_button {
        color: #cbd5e1 !important;
        border-radius: .5rem !important;
    }

    .spinwheel-dark .dataTables_paginate .paginate_button.current {
        background: linear-gradient(90deg, #ec4899, #8b5cf6) !important;
        border-color: transparent !important;
        color: #fff !important;
    }

    .spinwheel-dark .dataTables_paginate .paginate_button:hover {
        background: rgba(255, 255, 255, .08) !important;
        color: #fff !important;
    }
</style>
