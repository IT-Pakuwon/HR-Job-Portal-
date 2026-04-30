<style>
    /* Base Filter CSS */

    .scope-filter .scope-card {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: .6rem;
        padding: .45rem .7rem;
        font-size: .85rem;
        font-weight: 600;
        transition: all .18s ease;
    }

    .scope-filter:hover .scope-card {
        transform: translateY(-1px);
    }

    .scope-filter.active .scope-card {
        transform: scale(1.03);
    }

    .status-filter .status-card {
        border: 1px solid rgba(0, 0, 0, .12);
        border-radius: .65rem;
        padding: .45rem .7rem;
        font-size: .85rem;
        font-weight: 600;
        transition: all .18s ease;
    }

    .status-filter:hover .status-card {
        transform: translateY(-1px);
    }

    .status-filter.active .status-card {
        transform: scale(1.03);
    }

    /* Scope Data Filter */

    /* =====================================================
SUMMARY / NEUTRAL
===================================================== */

    .scope-filter[data-scope="all"] .scope-card,
    .scope-filter[data-scope="my"] .scope-card,
    .scope-filter[data-scope="spball"] .scope-card {

        background: rgba(148, 163, 184, .20);
        border-color: rgba(71, 85, 105, .45);
        color: #334155;
    }

    .scope-filter[data-scope="all"].active .scope-card,
    .scope-filter[data-scope="my"].active .scope-card,
    .scope-filter[data-scope="spball"].active .scope-card {

        background: #e2e8f0;
        border-color: #475569;
    }

    /* =====================================================
NEW JOBS / REQUEST
===================================================== */

    .scope-filter[data-scope="issuejobsnew"] .scope-card,
    .scope-filter[data-scope="issuejobs"] .scope-card {

        background: rgba(168, 85, 247, .20);
        border-color: rgba(126, 34, 206, .45);
        color: #7e22ce;
    }

    .scope-filter[data-scope="issuejobsnew"].active .scope-card,
    .scope-filter[data-scope="issuejobs"].active .scope-card {

        background: #e9d5ff;
    }

    /* =====================================================
JOB MODULES (CALR / BAST / RFC / RECEIPT)
===================================================== */

    .scope-filter[data-scope="calrjobs"] .scope-card,
    .scope-filter[data-scope="bastjobs"] .scope-card,
    .scope-filter[data-scope="receiptjobs"] .scope-card,
    .scope-filter[data-scope="rfcajobs"] .scope-card {

        background: rgba(251, 146, 60, .20);
        border-color: rgba(194, 65, 12, .45);
        color: #c2410c;
    }

    .scope-filter[data-scope="calrjobs"].active .scope-card,
    .scope-filter[data-scope="bastjobs"].active .scope-card,
    .scope-filter[data-scope="receiptjobs"].active .scope-card,
    .scope-filter[data-scope="rfcajobs"].active .scope-card {

        background: #fed7aa;
    }

    /* =====================================================
ON PROGRESS
===================================================== */

    .scope-filter[data-scope="onprogress"] .scope-card,
    .scope-filter[data-scope="sppbprogress"] .scope-card,
    .scope-filter[data-scope="spbprogress"] .scope-card {

        background: rgba(251, 191, 36, .20);
        border-color: rgba(180, 83, 9, .45);
        color: #b45309;
    }

    .scope-filter[data-scope="onprogress"].active .scope-card,
    .scope-filter[data-scope="sppbprogress"].active .scope-card,
    .scope-filter[data-scope="spbprogress"].active .scope-card {

        background: #fde68a;
    }

    /* =====================================================
ISSUE PROGRESS
===================================================== */

    .scope-filter[data-scope="issueprogress"] .scope-card {

        background: rgba(239, 68, 68, .20);
        border-color: rgba(185, 28, 28, .45);
        color: #b91c1c;
    }

    .scope-filter[data-scope="issueprogress"].active .scope-card {

        background: #fecaca;
    }

    /* =====================================================
REVISION
===================================================== */

    .scope-filter[data-scope="revise"] .scope-card {

        background: rgba(251, 191, 36, .18);
        border-color: rgba(180, 83, 9, .45);
        color: #b45309;
    }

    .scope-filter[data-scope="revise"].active .scope-card {

        background: #fde68a;
    }

    /* =====================================================
REJECTED
===================================================== */

    .scope-filter[data-scope="rejected"] .scope-card {

        background: rgba(239, 68, 68, .18);
        border-color: rgba(185, 28, 28, .45);
        color: #b91c1c;
    }

    .scope-filter[data-scope="rejected"].active .scope-card {

        background: #fecaca;
    }

    /* =====================================================
COMPLETED
===================================================== */

    .scope-filter[data-scope="completed"] .scope-card {

        background: rgba(34, 197, 94, .20);
        border-color: rgba(21, 128, 61, .45);
        color: #15803d;
    }

    .scope-filter[data-scope="completed"].active .scope-card {

        background: #bbf7d0;
    }

    /* =====================================================
WORKFLOW
===================================================== */

    .scope-filter[data-scope="woflow"] .scope-card {

        background: rgba(20, 184, 166, .20);
        border-color: rgba(13, 148, 136, .45);
        color: #0f766e;
    }

    .scope-filter[data-scope="woflow"].active .scope-card {

        background: #99f6e4;
    }

    .scope-filter[data-scope="spbflow"] .scope-card {

        background: rgba(59, 130, 246, .20);
        border-color: rgba(29, 78, 216, .45);
        color: #1d4ed8;
    }

    .scope-filter[data-scope="spbflow"].active .scope-card {

        background: #bfdbfe;
    }

    /* =====================================================
FINANCE
===================================================== */

    .scope-filter[data-scope="financereceived"] .scope-card {

        background: rgba(59, 130, 246, .20);
        border-color: rgba(29, 78, 216, .45);
        color: #1d4ed8;
    }

    .scope-filter[data-scope="financereceived"].active .scope-card {

        background: #bfdbfe;
    }

    .scope-filter[data-scope="treasurypayment"] .scope-card {

        background: rgba(99, 102, 241, .20);
        border-color: rgba(67, 56, 202, .45);
        color: #4338ca;
    }

    .scope-filter[data-scope="treasurypayment"].active .scope-card {

        background: #c7d2fe;
    }

    /* =====================================================
RETURN JOBS
===================================================== */

    .scope-filter[data-scope="returnjobs"] .scope-card {

        background: rgba(244, 63, 94, .20);
        border-color: rgba(190, 18, 60, .45);
        color: #be123c;
    }

    .scope-filter[data-scope="returnjobs"].active .scope-card {

        background: #fecdd3;
    }

    /* ======================================================
DARK MODE BASE
====================================================== */

    .dark .scope-card {

        background: #0f172a;
        border: 1px solid rgba(148, 163, 184, .18);

        color: #e5e7eb;

        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, .03),
            0 6px 18px rgba(0, 0, 0, .35);

    }

    .dark .scope-filter:hover .scope-card {

        transform: translateY(-2px);

        border-color: rgba(148, 163, 184, .35);

        box-shadow:
            0 10px 26px rgba(0, 0, 0, .45);

    }

    .dark .scope-filter.active .scope-card {

        border-color: rgba(148, 163, 184, .55);

        box-shadow:
            0 0 0 1px rgba(148, 163, 184, .15),
            0 8px 30px rgba(0, 0, 0, .55);

    }


    /* ======================================================
SUMMARY
====================================================== */

    .dark .scope-filter[data-scope="all"] .scope-card,
    .dark .scope-filter[data-scope="my"] .scope-card,
    .dark .scope-filter[data-scope="spball"] .scope-card {

        background: rgba(148, 163, 184, .10);
        border-color: rgba(148, 163, 184, .35);
        color: #e2e8f0;

    }


    /* ======================================================
NEW JOBS
====================================================== */

    .dark .scope-filter[data-scope="issuejobsnew"] .scope-card,
    .dark .scope-filter[data-scope="issuejobs"] .scope-card {

        background: rgba(139, 92, 246, .12);
        border-color: rgba(139, 92, 246, .35);
        color: #c4b5fd;

    }


    /* ======================================================
MODULE JOBS
====================================================== */

    .dark .scope-filter[data-scope="calrjobs"] .scope-card,
    .dark .scope-filter[data-scope="bastjobs"] .scope-card,
    .dark .scope-filter[data-scope="receiptjobs"] .scope-card,
    .dark .scope-filter[data-scope="rfcajobs"] .scope-card {

        background: rgba(251, 146, 60, .10);
        border-color: rgba(251, 146, 60, .35);
        color: #fdba74;

    }


    /* ======================================================
ON PROGRESS
====================================================== */

    .dark .scope-filter[data-scope="onprogress"] .scope-card,
    .dark .scope-filter[data-scope="sppbprogress"] .scope-card,
    .dark .scope-filter[data-scope="spbprogress"] .scope-card {

        background: rgba(250, 204, 21, .10);
        border-color: rgba(250, 204, 21, .35);
        color: #fde68a;

    }


    /* ======================================================
ISSUE PROGRESS
====================================================== */

    .dark .scope-filter[data-scope="issueprogress"] .scope-card {

        background: rgba(239, 68, 68, .10);
        border-color: rgba(239, 68, 68, .35);
        color: #fca5a5;

    }


    /* ======================================================
RETURN JOBS
====================================================== */

    .dark .scope-filter[data-scope="returnjobs"] .scope-card {

        background: rgba(244, 63, 94, .10);
        border-color: rgba(244, 63, 94, .35);
        color: #fda4af;

    }


    /* ======================================================
REVISION
====================================================== */

    .dark .scope-filter[data-scope="revise"] .scope-card {

        background: rgba(251, 191, 36, .10);
        border-color: rgba(251, 191, 36, .35);
        color: #fde68a;

    }


    /* ======================================================
REJECTED
====================================================== */

    .dark .scope-filter[data-scope="rejected"] .scope-card {

        background: rgba(220, 38, 38, .12);
        border-color: rgba(220, 38, 38, .40);
        color: #fca5a5;

    }


    /* ======================================================
COMPLETED
====================================================== */

    .dark .scope-filter[data-scope="completed"] .scope-card {

        background: rgba(34, 197, 94, .10);
        border-color: rgba(34, 197, 94, .35);
        color: #86efac;

    }


    /* ======================================================
WORKFLOW
====================================================== */

    .dark .scope-filter[data-scope="woflow"] .scope-card {

        background: rgba(20, 184, 166, .10);
        border-color: rgba(20, 184, 166, .35);
        color: #5eead4;

    }

    .dark .scope-filter[data-scope="spbflow"] .scope-card {

        background: rgba(59, 130, 246, .10);
        border-color: rgba(59, 130, 246, .35);
        color: #93c5fd;

    }


    /* ======================================================
FINANCE
====================================================== */

    .dark .scope-filter[data-scope="financereceived"] .scope-card {

        background: rgba(59, 130, 246, .10);
        border-color: rgba(59, 130, 246, .35);
        color: #93c5fd;

    }

    .dark .scope-filter[data-scope="treasurypayment"] .scope-card {

        background: rgba(99, 102, 241, .10);
        border-color: rgba(99, 102, 241, .35);
        color: #a5b4fc;

    }

    /* =====================================================
BASE CARD
===================================================== */

    .status-card {
        transition: all .25s ease;
    }

    .status-filter:hover .status-card {
        transform: translateY(-2px);
    }

    /* =====================================================
ALL / DEFAULT
===================================================== */

    .status-filter[data-status=""] .status-card,
    .status-filter[data-status="ALL"] .status-card {

        background: rgba(229, 231, 235, .35);
        border-color: rgba(31, 41, 55, .35);
        color: #374151;

    }

    .status-filter[data-status=""]:hover .status-card,
    .status-filter[data-status="ALL"]:hover .status-card {

        background: rgba(229, 231, 235, .55);

    }

    .status-filter[data-status=""].active .status-card,
    .status-filter[data-status="ALL"].active .status-card {

        background: #e5e7eb;
        border-color: #1f2937;

    }


    /* =====================================================
P - PROGRESS
===================================================== */

    .status-filter[data-status="P"] .status-card {

        background: rgba(251, 146, 60, .18);
        border-color: rgba(194, 65, 12, .35);
        color: #c2410c;

    }

    .status-filter[data-status="P"]:hover .status-card {
        background: rgba(251, 146, 60, .28);
    }

    .status-filter[data-status="P"].active .status-card {
        background: #fed7aa;
    }


    /* =====================================================
R - REJECTED
===================================================== */

    .status-filter[data-status="R"] .status-card {

        background: rgba(239, 68, 68, .18);
        border-color: rgba(185, 28, 28, .35);
        color: #b91c1c;

    }

    .status-filter[data-status="R"]:hover .status-card {
        background: rgba(239, 68, 68, .28);
    }

    .status-filter[data-status="R"].active .status-card {
        background: #fecaca;
    }


    /* =====================================================
D - DRAFT
===================================================== */

    .status-filter[data-status="D"] .status-card {

        background: rgba(251, 191, 36, .18);
        border-color: rgba(180, 83, 9, .35);
        color: #b45309;

    }

    .status-filter[data-status="D"]:hover .status-card {
        background: rgba(251, 191, 36, .28);
    }

    .status-filter[data-status="D"].active .status-card {
        background: #fde68a;
    }


    /* =====================================================
H / HOLD
===================================================== */

    .status-filter[data-status="H"] .status-card,
    .status-filter[data-status="H,D"] .status-card {

        background: rgba(253, 224, 71, .18);
        border-color: rgba(202, 138, 4, .35);
        color: #a16207;

    }

    .status-filter[data-status="H"]:hover .status-card,
    .status-filter[data-status="H,D"]:hover .status-card {

        background: rgba(253, 224, 71, .28);

    }

    .status-filter[data-status="H"].active .status-card,
    .status-filter[data-status="H,D"].active .status-card {

        background: #fde68a;

    }


    /* =====================================================
C - COMPLETED
===================================================== */

    .status-filter[data-status="C"] .status-card {

        background: rgba(34, 197, 94, .18);
        border-color: rgba(21, 128, 61, .35);
        color: #15803d;

    }

    .status-filter[data-status="C"]:hover .status-card {
        background: rgba(34, 197, 94, .28);
    }

    .status-filter[data-status="C"].active .status-card {
        background: #bbf7d0;
    }


    /* =====================================================
TRACK
===================================================== */

    .status-filter[data-status="TRACK"] .status-card {

        background: rgba(168, 85, 247, .18);
        border-color: rgba(126, 34, 206, .35);
        color: #7e22ce;

    }

    .status-filter[data-status="TRACK"]:hover .status-card {
        background: rgba(168, 85, 247, .28);
    }

    .status-filter[data-status="TRACK"].active .status-card {
        background: #e9d5ff;
    }


    /* =====================================================
X - CANCELLED
===================================================== */

    .status-filter[data-status="X"] .status-card {

        background: rgba(148, 163, 184, .25);
        border-color: rgba(71, 85, 105, .35);
        color: #475569;

    }

    .status-filter[data-status="X"]:hover .status-card {
        background: rgba(148, 163, 184, .35);
    }

    .status-filter[data-status="X"].active .status-card {
        background: #e2e8f0;
    }


    /* =====================================================
UNREAD
===================================================== */

    .status-filter[data-status="is_read_N"] .status-card {

        background: rgba(59, 130, 246, .18);
        border-color: rgba(29, 78, 216, .35);
        color: #1d4ed8;

    }

    .status-filter[data-status="is_read_N"]:hover .status-card {
        background: rgba(59, 130, 246, .28);
    }

    .status-filter[data-status="is_read_N"].active .status-card {
        background: #bfdbfe;
    }


    /* =====================================================
READ
===================================================== */

    .status-filter[data-status="is_read_Y"] .status-card {

        background: rgba(203, 213, 225, .35);
        border-color: rgba(100, 116, 139, .35);
        color: #475569;

    }

    .status-filter[data-status="is_read_Y"]:hover .status-card {
        background: rgba(203, 213, 225, .45);
    }

    .status-filter[data-status="is_read_Y"].active .status-card {
        background: #e2e8f0;
    }


    /* =====================================================
DARK MODE BASE
===================================================== */

    .dark .status-card {

        background: #0f172a;
        border: 1px solid rgba(148, 163, 184, .18);
        color: #e5e7eb;

        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, .03),
            0 6px 18px rgba(0, 0, 0, .35);

    }

    .dark .status-filter:hover .status-card {

        border-color: rgba(148, 163, 184, .35);
        box-shadow: 0 10px 26px rgba(0, 0, 0, .45);

    }

    .dark .status-filter.active .status-card {

        border-color: rgba(148, 163, 184, .55);
        box-shadow:
            0 0 0 1px rgba(148, 163, 184, .15),
            0 8px 30px rgba(0, 0, 0, .55);

    }


    /* =====================================================
DARK MODE STATUS COLORS
===================================================== */

    .dark .status-filter[data-status="P"] .status-card {
        background: rgba(251, 146, 60, .10);
        border-color: rgba(251, 146, 60, .35);
        color: #fdba74;
    }

    .dark .status-filter[data-status="R"] .status-card {
        background: rgba(239, 68, 68, .10);
        border-color: rgba(239, 68, 68, .35);
        color: #fca5a5;
    }

    .dark .status-filter[data-status="D"] .status-card {
        background: rgba(250, 204, 21, .10);
        border-color: rgba(250, 204, 21, .35);
        color: #fde68a;
    }

    .dark .status-filter[data-status="H"] .status-card,
    .dark .status-filter[data-status="H,D"] .status-card {
        background: rgba(253, 224, 71, .10);
        border-color: rgba(253, 224, 71, .35);
        color: #fde68a;
    }

    .dark .status-filter[data-status="C"] .status-card {
        background: rgba(34, 197, 94, .10);
        border-color: rgba(34, 197, 94, .35);
        color: #86efac;
    }

    .dark .status-filter[data-status="TRACK"] .status-card {
        background: rgba(139, 92, 246, .10);
        border-color: rgba(139, 92, 246, .35);
        color: #c4b5fd;
    }

    .dark .status-filter[data-status="X"] .status-card {
        background: rgba(148, 163, 184, .10);
        border-color: rgba(148, 163, 184, .35);
        color: #cbd5f5;
    }

    .dark .status-filter[data-status="is_read_N"] .status-card {
        background: rgba(59, 130, 246, .10);
        border-color: rgba(59, 130, 246, .35);
        color: #93c5fd;
    }

    .dark .status-filter[data-status="is_read_Y"] .status-card {
        background: rgba(148, 163, 184, .10);
        border-color: rgba(148, 163, 184, .35);
        color: #e2e8f0;
    }

    /* =========================================================
GLOBAL UI BASE
========================================================= */

    :root {
        --ui-radius: .5rem;
        --ui-border: #d1d5db;
        --ui-text: #374151;
        --ui-muted: #6b7280;
        --ui-bg: #ffffff;
        --ui-hover: #f3f4f6;
        --ui-focus: #6366f1;
    }

    .dark {
        --ui-border: #374151;
        --ui-text: #e5e7eb;
        --ui-muted: #9ca3af;
        --ui-bg: #1f2937;
        --ui-hover: #374151;
    }

    /* =========================================================
READONLY INPUT
========================================================= */

    input[readonly],
    textarea[readonly],
    select[readonly] {

        background: #f3f4f6;
        border-color: #d1d5db;
        color: #374151;
        cursor: not-allowed;

    }

    .dark input[readonly],
    .dark textarea[readonly],
    .dark select[readonly] {

        background: #111827;
        border-color: #374151;
        color: #9ca3af;

    }


    /* =========================================================
BUTTON ACTIVE STATES
========================================================= */

    #btn-mine.active {

        background: #c7d2fe;
        border-color: #4338ca;

    }

    #btn-revision.active {

        background: #fde68a;
        border-color: #b45309;

    }

    #btn-all.active {

        background: #e5e7eb;
        border-color: #1f2937;

    }

    #btn-sppbjkt.active {

        background: #bbf7d0;
        border-color: #15803d;

    }

    #btn-completed.active {

        background: #e2e8f0;
        border-color: #0f172a;
        color: #0f172a;

    }

    /* ---------- DARK MODE ---------- */

    .dark #btn-mine.active {

        background: rgba(99, 102, 241, .25);
        border-color: #6366f1;
        color: #c7d2fe;

    }

    .dark #btn-revision.active {

        background: rgba(251, 191, 36, .22);
        border-color: #f59e0b;
        color: #fde68a;

    }

    .dark #btn-all.active {

        background: rgba(148, 163, 184, .22);
        border-color: #94a3b8;
        color: #e5e7eb;

    }

    .dark #btn-sppbjkt.active {

        background: rgba(34, 197, 94, .22);
        border-color: #22c55e;
        color: #bbf7d0;

    }

    .dark #btn-completed.active {

        background: rgba(148, 163, 184, .25);
        border-color: #94a3b8;
        color: #f1f5f9;

    }


    /* =========================================================
LOADING SPINNER SYSTEM
========================================================= */

    @keyframes spin {
        to {
            transform: rotate(360deg)
        }
    }

    @keyframes spinReverse {
        to {
            transform: rotate(-360deg)
        }
    }

    @keyframes blink {
        0% {
            opacity: .3;
            transform: translateY(0)
        }

        20% {
            opacity: 1;
            transform: translateY(-2px)
        }

        100% {
            opacity: .3;
            transform: translateY(0)
        }
    }

    #loadingSpinnerContainer {

        position: fixed;
        inset: 0;

        display: none;

        background: rgba(17, 24, 39, .55);
        backdrop-filter: blur(3px);

        z-index: 2000;

    }

    .dark #loadingSpinnerContainer {
        background: rgba(0, 0, 0, .65);
    }

    #loadingSpinnerContainer .loading-card {

        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;

        padding: 18px 22px;

        border-radius: 16px;

        background: linear-gradient(180deg,
                rgba(31, 41, 55, .95),
                rgba(17, 24, 39, .95));

        border: 1px solid rgba(255, 255, 255, .08);

        box-shadow:
            0 12px 35px rgba(0, 0, 0, .4),
            inset 0 0 0 1px rgba(255, 255, 255, .04);

    }

    .loading-spinner {

        width: 54px;
        height: 54px;

        border-radius: 50%;

        border: 4px solid transparent;
        border-top-color: #6366f1;

        animation: spin 1s linear infinite;

        position: relative;

    }

    .loading-spinner::after {

        content: "";
        position: absolute;
        inset: 6px;

        border-radius: 50%;
        border: 4px solid transparent;
        border-left-color: #a5b4fc;

        animation: spinReverse .75s linear infinite;

    }

    .loading-text {
        color: #e5e7eb;
        font-weight: 600;
    }

    .loading-ellipsis span {
        display: inline-block;
        animation: blink 1.4s infinite both;
    }

    .loading-ellipsis span:nth-child(2) {
        animation-delay: .2s
    }

    .loading-ellipsis span:nth-child(3) {
        animation-delay: .4s
    }


    /* =========================================================
FORM VALIDATION
========================================================= */

    .is-invalid {
        border-color: #ef4444 !important;
    }

    .error-feedback {

        display: block;
        margin-top: 6px;

        font-size: 12px;
        color: #dc2626;

    }

    .dark .error-feedback {
        color: #f87171;
    }


    /* =========================================================
SELECT ARROW DARK FIX
========================================================= */

    .dark select:not([multiple]) {

        background-image: url("data:image/svg+xml,%3Csvg fill='%23d1d5db' viewBox='0 0 20 20'%3E%3Cpath d='M5.25 7.75L10 12.5l4.75-4.75'/%3E%3C/svg%3E");

    }


    /* =========================================================
SELECT2
========================================================= */

    .select2-container {
        width: 100% !important;
    }

    .select2-selection--single {

        height: 42px;
        border-radius: .5rem;

    }

    .select2-container--default .select2-selection--single {

        border: 1px solid var(--ui-border);
        background: #fff;

        display: flex;
        align-items: center;

    }

    .select2-selection__rendered {

        line-height: 42px;
        padding-left: .75rem;

        color: var(--ui-text);

    }

    .select2-selection__arrow {

        height: 42px;
        right: .5rem;

    }

    .select2-container--default:hover .select2-selection--single {

        border-color: #9ca3af;

    }

    .select2-container--default.select2-container--focus .select2-selection--single {

        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, .15);

    }

    .select2-dropdown {

        border-radius: .5rem;
        border: 1px solid var(--ui-border);

    }

    .select2-results__option--highlighted {

        background: #eef2ff !important;
        color: #4338ca !important;

    }

    /* ---------- DARK ---------- */

    .dark .select2-selection--single {

        background: #1f2937;
        border-color: #374151;

    }

    .dark .select2-selection__rendered {
        color: #e5e7eb;
    }

    .dark .select2-dropdown {

        background: #111827;
        border-color: #4b5563;

    }

    .dark .select2-results__option--highlighted {

        background: #3730a3 !important;
        color: #fff !important;

    }


    /* =========================================================
FORM HELPERS
========================================================= */

    .req::after {

        content: "*";
        color: #ef4444;
        margin-left: 4px;

    }


    /* =========================================================
TRACK TAB UI
========================================================= */

    .track-tab,
    .track-tab-sppb {

        padding: .4rem .75rem;
        border-radius: .5rem;

        font-size: .875rem;
        font-weight: 600;

        color: #4b5563;

    }

    .track-tab:hover,
    .track-tab-sppb:hover {
        background: rgba(0, 0, 0, .05);
    }

    .track-tab.active,
    .track-tab-sppb.active {

        background: rgba(79, 70, 229, .12);
        color: #4338ca;

    }

    .dark .track-tab,
    .dark .track-tab-sppb {
        color: #9ca3af;
    }

    .dark .track-tab:hover,
    .dark .track-tab-sppb:hover {
        background: rgba(255, 255, 255, .05);
    }

    .dark .track-tab.active,
    .dark .track-tab-sppb.active {

        background: rgba(99, 102, 241, .25);
        color: #c7d2fe;

    }


    /* =========================================================
VENDOR TITLE
========================================================= */

    .vendor-title {

        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;

        line-height: 1.1;

    }

    .dark .vendor-title {
        color: #e5e7eb;
    }


    /* =========================================================
TAX INPUT
========================================================= */

    .tax-2col {

        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .5rem;

    }

    .tax-chip {

        display: inline-flex;
        align-items: center;
        gap: .35rem;

    }

    .tax-input {

        width: 3.75rem;
        text-align: right;

        padding: .125rem .25rem;

    }

    .dark .tax-input {

        background: #1f2937;
        border-color: #374151;
        color: #e5e7eb;

    }


    /* =========================================================
ICON BUTTON
========================================================= */

    .icon-btn {

        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 18px;
        height: 18px;

        font-size: 11px;

        border: 1px solid var(--ui-border);
        border-radius: 4px;

        background: #fff;

    }

    .icon-btn:hover {
        background: #f3f4f6;
    }

    .dark .icon-btn {

        background: #1f2937;
        border-color: #374151;
        color: #e5e7eb;

    }

    .dark .icon-btn:hover {
        background: #374151;
    }


    /* =========================================================
SUMMARY DISPLAY
========================================================= */

    .summary-label {

        font-size: 2rem;
        font-weight: 600;

        color: #374151;

    }

    .summary-value {

        font-size: 2rem;
        font-weight: 700;

        color: #111827;

    }

    .dark .summary-label {
        color: #d1d5db;
    }

    .dark .summary-value {
        color: #f9fafb;
    }


    /* =========================================================
TABLE WRAP FIX
========================================================= */

    #applicantsTable td,
    #applicantsTable th {

        white-space: normal !important;

    }

    #applicantsTable td {
        word-break: break-word;
    }
    .voucher-filter{
        padding:6px 10px;
        border-radius:10px;
        font-size:11px;
        font-weight:600;
        transition:.2s;
        background:#f3f4f6;
        color:#4b5563;
    }

    .voucher-filter:hover{
        background:#e5e7eb;
    }

    .active-filter{
        background:#111827 !important;
        color:white !important;
    }

    .dark .voucher-filter{
        background:rgba(255,255,255,.06);
        color:#d1d5db;
    }

    .dark .voucher-filter:hover{
        background:rgba(255,255,255,.1);
    }

    .dark .active-filter{
        background:white !important;
        color:black !important;
    }

    .voucher-filter {
        @apply rounded-lg px-3 py-1.5 text-xs font-medium transition;
        @apply bg-gray-100 text-gray-600 hover:bg-gray-200;

        @apply dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10;
    }

    .voucher-filter.active-filter {
        @apply bg-gray-900 text-white;

        @apply dark:bg-white dark:text-black;
    }

    .booking-filter{
        padding:6px 10px;
        border-radius:10px;
        font-size:11px;
        font-weight:600;
        transition:.2s;
        background:#f3f4f6;
        color:#4b5563;
    }

    .booking-filter:hover{
        background:#e5e7eb;
    }

    .active-filter{
        background:#111827 !important;
        color:white !important;
    }

    .dark .booking-filter{
        background:rgba(255,255,255,.06);
        color:#d1d5db;
    }

    .dark .booking-filter:hover{
        background:rgba(255,255,255,.1);
    }

    .dark .active-filter{
        background:white !important;
        color:black !important;
    }

    .booking-filter {
        @apply rounded-lg px-3 py-1.5 text-xs font-medium transition;
        @apply bg-gray-100 text-gray-600 hover:bg-gray-200;

        @apply dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10;
    }

    .booking-filter.active-filter {
        @apply bg-gray-900 text-white;

        @apply dark:bg-white dark:text-black;
    }
</style>
