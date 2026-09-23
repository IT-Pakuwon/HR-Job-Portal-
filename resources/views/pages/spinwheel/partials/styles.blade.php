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

    /* Winner "Congratulations" popup (SweetAlert2) */
    @keyframes congratsPopIn {
        0% { transform: scale(.4); opacity: 0; }
        60% { transform: scale(1.08); opacity: 1; }
        100% { transform: scale(1); }
    }

    @keyframes congratsGlow {
        0%, 100% { box-shadow: 0 25px 70px -20px rgba(168, 85, 247, .55), 0 0 0 1px rgba(255, 255, 255, .04); }
        50% { box-shadow: 0 25px 70px -20px rgba(236, 72, 153, .65), 0 0 0 1px rgba(255, 255, 255, .04); }
    }

    .congrats-popup {
        border-radius: 1.75rem !important;
        border: 1px solid rgba(236, 72, 153, .3) !important;
        padding: 2.25rem 1.75rem 2rem !important;
        animation: congratsGlow 3s ease-in-out infinite;
    }

    .congrats-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 5.5rem;
        height: 5.5rem;
        margin: 0 auto .75rem;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(52, 211, 153, .18), rgba(52, 211, 153, 0));
        box-shadow: 0 0 0 4px rgba(52, 211, 153, .18), 0 0 30px rgba(52, 211, 153, .35);
        animation: congratsPopIn .5s cubic-bezier(.34, 1.56, .64, 1);
    }

    .congrats-title {
        font-size: 1.75rem;
        font-weight: 800;
        background: linear-gradient(90deg, #f472b6, #a855f7, #818cf8);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        filter: drop-shadow(0 0 12px rgba(236, 72, 153, .35));
    }

    .congrats-winner-card {
        background: linear-gradient(90deg, rgba(236, 72, 153, .1), rgba(255, 255, 255, .03));
        transition: transform .15s ease, background .15s ease;
    }

    .congrats-winner-card:hover {
        transform: translateX(2px);
        background: linear-gradient(90deg, rgba(236, 72, 153, .16), rgba(255, 255, 255, .04));
    }

    .congrats-confirm-btn {
        background: linear-gradient(90deg, #ec4899, #a855f7) !important;
        color: #fff !important;
        font-weight: 700 !important;
        padding: .8rem 2.75rem !important;
        border-radius: 9999px !important;
        border: none !important;
        box-shadow: 0 12px 28px -8px rgba(236, 72, 153, .65) !important;
        transition: transform .15s ease, box-shadow .15s ease !important;
    }

    .congrats-confirm-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px -8px rgba(236, 72, 153, .8) !important;
    }
</style>
