<style>

    :root{
        --ticket-bg:#f8fafc;
        --ticket-card:#ffffff;
        --ticket-border:#e2e8f0;
        --ticket-border-soft:#edf2f7;
        --ticket-text:#0f172a;
        --ticket-muted:#64748b;
        --ticket-muted-2:#94a3b8;
        --ticket-hover:#f8fafc;
        --ticket-primary:#2563eb;
        --ticket-primary-soft:#2563eb1a;
        --ticket-shadow:
            0 1px 2px rgba(15,23,42,.04),
            0 8px 24px rgba(15,23,42,.06);
    }

    .dark{
        --ticket-bg:#020617;
        --ticket-card:#0f172a;
        --ticket-border:rgba(255,255,255,.08);
        --ticket-border-soft:rgba(255,255,255,.04);
        --ticket-text:#f8fafc;
        --ticket-muted:#cbd5e1;
        --ticket-muted-2:#64748b;
        --ticket-hover:#172033;
        --ticket-primary:#3b82f6;
        --ticket-primary-soft:#3b82f624;
        --ticket-shadow:
            0 1px 2px rgba(0,0,0,.2),
            0 12px 32px rgba(0,0,0,.35);
    }

    .select2-container{
        width:100% !important;
    }

    .select2-container--default
    .select2-selection--single{

        height:48px !important;

        display:flex !important;
        align-items:center !important;

        border-radius:8px !important;

        border:1px solid var(--ticket-border) !important;

        background:var(--ticket-card) !important;

        padding:0 16px !important;

        transition:all .18s ease !important;
    }

    .select2-container--default
    .select2-selection--single:hover{

        border-color:var(--ticket-primary) !important;
    }

    .select2-container--default.select2-container--open
    .select2-selection--single{

        border-color:var(--ticket-primary) !important;

        box-shadow:
            0 0 0 4px var(--ticket-primary-soft) !important;
    }

    .select2-container--default
    .select2-selection--single
    .select2-selection__rendered{

        line-height:46px !important;

        color:var(--ticket-text) !important;

        padding-left:0 !important;

        font-size:14px !important;
        font-weight:500 !important;
    }

    .select2-container--default
    .select2-selection--single
    .select2-selection__placeholder{

        color:var(--ticket-muted-2) !important;
    }

    .select2-container--default
    .select2-selection--single
    .select2-selection__arrow{

        height:46px !important;

        right:14px !important;
    }

    .select2-container--default
    .select2-selection--single
    .select2-selection__arrow b{

        border-color:
            var(--ticket-muted-2)
            transparent
            transparent
            transparent !important;
    }

    .select2-dropdown{

        overflow:hidden !important;

        margin-top:8px !important;

        border:none !important;

        border-radius:8px !important;

        background:var(--ticket-card) !important;

        box-shadow:
            0 20px 50px rgba(15,23,42,.16) !important;
    }

    .dark .select2-dropdown{

        box-shadow:
            0 20px 60px rgba(0,0,0,.42) !important;
    }

    .select2-results__option{

        min-height:42px !important;

        display:flex !important;
        align-items:center !important;

        border-radius:8px !important;

        padding:10px 14px !important;

        font-size:13px !important;
        font-weight:500 !important;

        color:var(--ticket-text) !important;

        transition:all .15s ease !important;
    }

    .select2-results__option--highlighted
    .select2-results__option--selectable{

        background:var(--ticket-primary) !important;

        color:#ffffff !important;
    }

    .select2-search--dropdown{
        padding:14px !important;
    }

    .select2-container--default
    .select2-search--dropdown
    .select2-search__field{

        height:42px !important;

        border-radius:8px !important;

        border:1px solid var(--ticket-border) !important;

        background:var(--ticket-card) !important;

        color:var(--ticket-text) !important;

        padding:0 14px !important;

        font-size:13px !important;
    }

    .select2-search__field:focus{

        border-color:var(--ticket-primary) !important;

        box-shadow:
            0 0 0 4px var(--ticket-primary-soft) !important;
    }

    .swal2-container{
        z-index:999999 !important;
    }

    .status-filter.active .status-card{

        border-color:
            rgba(59,130,246,.35);

        background:
            linear-gradient(
                135deg,
                rgba(37,99,235,.22),
                rgba(15,23,42,.75)
            );

        color:#ffffff;
    }

    .status-filter.active .status-card p,
    .status-filter.active .status-card span{

        color:
            rgba(255,255,255,.88);
    }

    .table-wrapper::-webkit-scrollbar{
        height:10px;
        width:10px;
    }

    .table-wrapper::-webkit-scrollbar-thumb{
        background:rgba(148,163,184,.4);
        border-radius:999px;
    }

    .glass-card{
        backdrop-filter:blur(10px);
    }

    .modal-scroll::-webkit-scrollbar{
        width:8px;
    }

    .modal-scroll::-webkit-scrollbar-thumb{
        background:rgba(148,163,184,.35);
        border-radius:999px;
    }

</style>
