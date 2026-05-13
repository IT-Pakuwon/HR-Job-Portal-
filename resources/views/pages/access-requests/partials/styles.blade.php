<style>

    .select2-container{
        width:100% !important;
    }

    .select2-container--default .select2-selection--single{

        height:46px !important;

        border-radius:.75rem !important;

        border:
            1px solid rgba(255,255,255,.08) !important;

        display:flex !important;

        align-items:center !important;

        background:
            #0b1525 !important;

        padding-left:.5rem !important;

        color:#f8fafc !important;

        transition:
            all .2s ease;
    }

    .select2-container--default .select2-selection--single:hover{

        border-color:
            rgba(59,130,246,.35) !important;
    }

    .select2-container--default.select2-container--open .select2-selection--single{

        border-color:
            rgba(59,130,246,.45) !important;

        box-shadow:
            0 0 0 4px rgba(37,99,235,.10);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered{

        line-height:44px !important;

        color:#f8fafc !important;

        padding-left:.5rem !important;

        font-size:.875rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder{
        color:#64748b !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow{

        height:44px !important;

        right:10px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b{

        border-color:
            #94a3b8 transparent transparent transparent !important;
    }

    .select2-dropdown{

        background:
            #111c2d !important;

        border:
            1px solid rgba(255,255,255,.08) !important;

        border-radius:
            .9rem !important;

        overflow:hidden;

        box-shadow:
            0 20px 50px rgba(0,0,0,.45);
    }

    .select2-results__option{

        color:#e2e8f0 !important;

        padding:
            .7rem .9rem !important;

        font-size:.875rem !important;
    }

    .select2-results__option--highlighted.select2-results__option--selectable{

        background:
            rgba(37,99,235,.22) !important;

        color:#fff !important;
    }

    .select2-search--dropdown{
        padding:.5rem !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field{

        border:
            1px solid rgba(255,255,255,.08);

        background:
            #0b1525;

        color:#f8fafc;

        border-radius:
            .75rem;

        height:42px;

        padding:
            .5rem .85rem;
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

        color:white;
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

        background:
            rgba(148,163,184,.25);

        border-radius:999px;
    }

    .table-wrapper::-webkit-scrollbar-thumb:hover{

        background:
            rgba(148,163,184,.45);
    }

    .glass-card{

        backdrop-filter:blur(10px);

        background:
            rgba(255,255,255,.02);

        border:
            1px solid rgba(255,255,255,.06);

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.03),
            0 10px 30px rgba(0,0,0,.28);
    }

    .modal-scroll::-webkit-scrollbar{
        width:8px;
    }

    .modal-scroll::-webkit-scrollbar-thumb{

        background:
            rgba(148,163,184,.28);

        border-radius:999px;
    }

    .modal-scroll::-webkit-scrollbar-thumb:hover{

        background:
            rgba(148,163,184,.45);
    }

</style>
