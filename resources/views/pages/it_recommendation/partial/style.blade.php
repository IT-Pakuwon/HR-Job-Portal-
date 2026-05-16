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
        --ticket-primary: #4f46e5;
        --ticket-primary-soft: #4f46e51a;

        --ticket-shadow:
            0 1px 2px rgba(15, 23, 42, .04),
            0 10px 30px rgba(15, 23, 42, .06);
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
        --ticket-primary: #6366f1;
        --ticket-primary-soft: #6366f124;

        --ticket-shadow:
            0 1px 2px rgba(0, 0, 0, .25),
            0 18px 40px rgba(0, 0, 0, .35);
    }

    body {
        background:
            linear-gradient(to bottom,
                #f8fafc,
                #f1f5f9);

        color: var(--ticket-text);
    }

    .dark body {
        background:
            linear-gradient(to bottom,
                #020617,
                #0f172a);
    }

    .swal2-container {
        z-index: 999999 !important;
    }

    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .28);
        border-radius: 999px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, .45);
    }

    .status-card {
        position: relative;

        overflow: hidden;

        border-radius: 18px;

        backdrop-filter: blur(10px);

        transition:
            transform .18s ease,
            box-shadow .18s ease,
            border-color .18s ease;
    }

    .status-card::before {
        content: '';

        position: absolute;

        inset: 0;

        pointer-events: none;

        opacity: .45;

        background:
            radial-gradient(circle at top right,
                rgba(255, 255, 255, .65),
                transparent 60%);
    }

    .status-card:hover {
        transform: translateY(-3px);

        box-shadow:
            0 14px 35px rgba(15, 23, 42, .08);
    }

    .dark .status-card:hover {
        box-shadow:
            0 18px 40px rgba(0, 0, 0, .35);
    }

    .status-filter.active .status-card {
        outline: 2px solid rgba(99, 102, 241, .35);

        box-shadow:
            0 12px 30px rgba(99, 102, 241, .16);
    }

    .ticket-modal {
        backdrop-filter: blur(6px);
    }

    #createModal,
    #showModal,
    #processModal,
    #editRecommendationModal {
        backdrop-filter: blur(5px);
    }

    #createModal>div,
    #showModal>div,
    #processModal>div,
    #editRecommendationModal>div {
        animation: modalFade .18s ease;
    }

    @keyframes modalFade {

        from {
            opacity: 0;
            transform: translateY(10px) scale(.985);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
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

        font-size: 12px;
        font-weight: 700;

        letter-spacing: .08em;

        text-transform: uppercase;

        color: var(--ticket-muted);
    }

    .ticket-input,
    .ticket-textarea {
        width: 100%;

        border: 1px solid var(--ticket-border);

        background: var(--ticket-card);

        border-radius: 14px;

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

        border-radius: 14px !important;

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

        border-radius: 14px !important;

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

        border-radius: 10px !important;

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

        border-radius: 10px !important;

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
