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

    label.req::after {
        content: " *";
        color: #ef4444;
        font-weight: 700;
    }

    .swal2-container {
        z-index: 999999 !important;
    }

    .table-wrapper::-webkit-scrollbar {
        height: 10px;
        width: 10px;
    }

    .table-wrapper::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .4);
        border-radius: 999px;
    }

    .glass-card {
        backdrop-filter: blur(10px);
    }

    .modal-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .modal-scroll::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .35);
        border-radius: 999px;
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
