<style>
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
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .status-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 35px rgba(15, 23, 42, .08);
    }

    .dark .status-card:hover {
        box-shadow: 0 18px 40px rgba(0, 0, 0, .35);
    }

    .status-filter.active .status-card {
        outline: 2px solid rgba(99, 102, 241, .35);
        box-shadow: 0 12px 30px rgba(99, 102, 241, .16);
    }

    #createModal,
    #showModal,
    #editModal {
        backdrop-filter: blur(5px);
    }

    .modal-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .modal-scroll::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .35);
        border-radius: 999px;
    }

    label.req::after {
        content: " *";
        color: #ef4444;
        font-weight: 700;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 16px 18px;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 14px 18px;
    }
</style>
