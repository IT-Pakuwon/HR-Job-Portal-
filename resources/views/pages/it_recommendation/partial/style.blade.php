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

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter{
        padding:16px 18px;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate{
        padding:14px 18px;
    }

    label.req::after {
        content: " *";
        color: #ef4444;
        font-weight: 700;
    }
</style>
