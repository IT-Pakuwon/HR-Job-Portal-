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

    .scope-filter[data-scope="all"] .scope-card,
    .scope-filter[data-scope="my"] .scope-card,
    .scope-filter[data-scope="spball"] .scope-card {
        background: rgba(229, 231, 235, .35);
        border-color: rgba(31, 41, 55, .35);
        color: #374151;
    }

    .scope-filter[data-scope="all"]:hover .scope-card,
    .scope-filter[data-scope="my"]:hover .scope-card {
        background: rgba(229, 231, 235, .55);
    }

    .scope-filter[data-scope="all"].active .scope-card,
    .scope-filter[data-scope="my"].active .scope-card {
        background: #e5e7eb;
        border-color: #1f2937;
    }

    .scope-filter[data-scope="calrjobs"] .scope-card,
    .scope-filter[data-scope="bastjobs"] .scope-card,
    .scope-filter[data-scope="receiptjobs"] .scope-card,
    .scope-filter[data-scope="rfcajobs"] .scope-card {
        background: rgba(251, 146, 60, .18);
        border-color: rgba(194, 65, 12, .35);
        color: #c2410c;
    }

    .scope-filter[data-scope="calrjobs"]:hover .scope-card,
    .scope-filter[data-scope="bastjobs"]:hover .scope-card,
    .scope-filter[data-scope="receiptjobs"]:hover .scope-card,
    .scope-filter[data-scope="rfcajobs"]:hover .scope-card {
        background: rgba(251, 146, 60, .28);
    }

    .scope-filter[data-scope="calrjobs"].active .scope-card,
    .scope-filter[data-scope="bastjobs"].active .scope-card,
    .scope-filter[data-scope="receiptjobs"].active .scope-card,
    .scope-filter[data-scope="rfcajobs"].active .scope-card {
        background: #fed7aa;
        border-color: #c2410c;
    }

    .scope-filter[data-scope="onprogress"] .scope-card {
        background: rgba(251, 146, 60, .18);
        border-color: rgba(194, 65, 12, .35);
        color: #c2410c;
    }

    .scope-filter[data-scope="onprogress"]:hover .scope-card {
        background: rgba(251, 146, 60, .28);
    }

    .scope-filter[data-scope="onprogress"].active .scope-card {
        background: #fed7aa;
    }

    .scope-filter[data-scope="revise"] .scope-card {
        background: rgba(251, 191, 36, .18);
        border-color: rgba(180, 83, 9, .35);
        color: #b45309;
    }

    .scope-filter[data-scope="revise"]:hover .scope-card {
        background: rgba(251, 191, 36, .28);
    }

    .scope-filter[data-scope="revise"].active .scope-card {
        background: #fde68a;
    }

    .scope-filter[data-scope="rejected"] .scope-card {
        background: rgba(239, 68, 68, .18);
        border-color: rgba(185, 28, 28, .35);
        color: #b91c1c;
    }

    .scope-filter[data-scope="rejected"]:hover .scope-card {
        background: rgba(239, 68, 68, .28);
    }

    .scope-filter[data-scope="rejected"].active .scope-card {
        background: #fecaca;
    }

    .scope-filter[data-scope="completed"] .scope-card {
        background: rgba(34, 197, 94, .18);
        border-color: rgba(21, 128, 61, .35);
        color: #15803d;
    }

    .scope-filter[data-scope="completed"]:hover .scope-card {
        background: rgba(34, 197, 94, .28);
    }

    .scope-filter[data-scope="completed"].active .scope-card {
        background: #bbf7d0;
    }

    .scope-filter[data-scope="financereceived"] .scope-card {
        background: rgba(59, 130, 246, .18);
        border-color: rgba(29, 78, 216, .35);
        color: #1d4ed8;
    }

    .scope-filter[data-scope="financereceived"].active .scope-card {
        background: #bfdbfe;
    }

    .scope-filter[data-scope="treasurypayment"] .scope-card {
        background: rgba(99, 102, 241, .18);
        border-color: rgba(67, 56, 202, .35);
        color: #4338ca;
    }

    .scope-filter[data-scope="treasurypayment"].active .scope-card {
        background: #c7d2fe;
    }

    .scope-filter[data-scope="issuejobsnew"] .scope-card,
    .scope-filter[data-scope="issuejobs"] .scope-card {
        background: rgba(168, 85, 247, .18);
        border-color: rgba(126, 34, 206, .35);
        color: #7e22ce;
    }

    .scope-filter[data-scope="issuejobsnew"].active .scope-card,
    .scope-filter[data-scope="issuejobs"].active .scope-card {
        background: #e9d5ff;
    }

    .scope-filter[data-scope="issueprogress"] .scope-card {
        background: rgba(239, 68, 68, .18);
        border-color: rgba(185, 28, 28, .35);
        color: #b91c1c;
    }

    .scope-filter[data-scope="issueprogress"].active .scope-card {
        background: #fecaca;
    }

    .scope-filter[data-scope="sppbprogress"] .scope-card,
    .scope-filter[data-scope="spbprogress"] .scope-card {
        background: rgba(139, 92, 246, .18);
        border-color: rgba(109, 40, 217, .35);
        color: #6d28d9;
    }

    .scope-filter[data-scope="sppbprogress"].active .scope-card,
    .scope-filter[data-scope="spbprogress"].active .scope-card {
        background: #ddd6fe;
    }

    .scope-filter[data-scope="woflow"] .scope-card {
        background: rgba(20, 184, 166, .18);
        border-color: rgba(13, 148, 136, .35);
        color: #0f766e;
    }

    .scope-filter[data-scope="woflow"].active .scope-card {
        background: #99f6e4;
    }

    .dark .scope-card {
        border-color: rgba(255, 255, 255, .12);
    }

    .dark .scope-filter .scope-card {
        backdrop-filter: blur(2px);
    }

    /* Status Data CSS */

    .status-filter[data-status=""],
    .status-filter[data-status="ALL"] {}

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

    .status-filter[data-status="H"],
    .status-filter[data-status="H,D"] {}

    .status-filter[data-status="H"] .status-card,
    .status-filter[data-status="H,D"] .status-card {
        background: rgba(253, 224, 71, .18);
        border-color: rgba(202, 138, 4, .35);
        color: #a16207;
    }

    .status-filter[data-status="H"]:hover .status-card {
        background: rgba(253, 224, 71, .28);
    }

    .status-filter[data-status="H"].active .status-card {
        background: #fde68a;
    }

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

    .status-filter[data-status="TRACK"] .status-card {
        background: rgba(168, 85, 247, .18);
        border-color: rgba(126, 34, 206, .35);
        color: #7e22ce;
    }

    .status-filter[data-status="TRACK"].active .status-card {
        background: #e9d5ff;
    }

    .status-filter[data-status="X"] .status-card {
        background: rgba(148, 163, 184, .25);
        border-color: rgba(71, 85, 105, .35);
        color: #475569;
    }

    .status-filter[data-status="X"].active .status-card {
        background: #e2e8f0;
    }

    .status-filter[data-status="is_read_N"] .status-card {
        background: rgba(59, 130, 246, .18);
        border-color: rgba(29, 78, 216, .35);
        color: #1d4ed8;
    }

    .status-filter[data-status="is_read_N"].active .status-card {
        background: #bfdbfe;
    }

    .status-filter[data-status="is_read_Y"] .status-card {
        background: rgba(203, 213, 225, .35);
        border-color: rgba(100, 116, 139, .35);
        color: #475569;
    }

    .status-filter[data-status="is_read_Y"].active .status-card {
        background: #e2e8f0;
    }

    .dark .status-card {
        border-color: rgba(255, 255, 255, .12);
    }

    .dark .status-filter .status-card {
        backdrop-filter: blur(2px);
    }

    /* =====================================================
READONLY INPUT STYLE
===================================================== */

    input[readonly],
    textarea[readonly],
    select[readonly] {
        background: #f3f4f6;
        /* gray-100 */
        border-color: #d1d5db;
        /* gray-300 */
        color: #374151;
        /* gray-700 */
        cursor: not-allowed;
    }

    /* Dark Mode */
    .dark input[readonly],
    .dark textarea[readonly],
    .dark select[readonly] {
        background: #111827;
        /* gray-900 */
        border-color: #374151;
        /* gray-700 */
        color: #9ca3af;
        /* gray-400 */
    }


    /* =====================================================
BUTTON ACTIVE STATES
===================================================== */

    /* CS Jobs */
    #btn-mine.active {
        background-color: rgb(199 210 254);
        /* indigo-200 */
        border-color: rgb(67 56 202);
        /* indigo-700 */
    }

    /* CS Revision */
    #btn-revision.active {
        background-color: rgb(253 230 138);
        /* amber-200 */
        border-color: rgb(180 83 9);
        /* amber-700 */
    }

    /* All CS Jobs */
    #btn-all.active {
        background-color: rgb(229 231 235);
        /* gray-200 */
        border-color: rgb(31 41 55);
        /* gray-700 */
    }

    /* SPPBJKT In Progress */
    #btn-sppbjkt.active {
        background-color: rgb(187 247 208);
        /* green-200 */
        border-color: rgb(21 128 61);
        /* green-700 */
    }

    /* Completed */
    #btn-completed.active {
        background-color: rgb(226 232 240);
        /* slate-200 */
        border-color: rgb(15 23 42);
        /* slate-900 */
        color: rgb(15 23 42);
    }

    /* =====================================================
BUTTON ACTIVE STATES — DARK MODE
===================================================== */

    /* CS Jobs */
    .dark #btn-mine.active {
        background-color: rgba(99, 102, 241, .25);
        /* indigo soft */
        border-color: #6366f1;
        color: #c7d2fe;
    }

    /* CS Revision */
    .dark #btn-revision.active {
        background-color: rgba(251, 191, 36, .22);
        /* amber soft */
        border-color: #f59e0b;
        color: #fde68a;
    }

    /* All CS Jobs */
    .dark #btn-all.active {
        background-color: rgba(148, 163, 184, .22);
        /* slate soft */
        border-color: #94a3b8;
        color: #e5e7eb;
    }

    /* SPPBJKT In Progress */
    .dark #btn-sppbjkt.active {
        background-color: rgba(34, 197, 94, .22);
        /* green soft */
        border-color: #22c55e;
        color: #bbf7d0;
    }

    /* Completed */
    .dark #btn-completed.active {
        background-color: rgba(148, 163, 184, .25);
        /* slate soft */
        border-color: #94a3b8;
        color: #f1f5f9;
    }

    /* =====================================================
LOADING SPINNER SYSTEM
===================================================== */

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes spinReverse {
        to {
            transform: rotate(-360deg);
        }
    }

    @keyframes blink {
        0% {
            opacity: .3;
            transform: translateY(0);
        }

        20% {
            opacity: 1;
            transform: translateY(-2px);
        }

        100% {
            opacity: .3;
            transform: translateY(0);
        }
    }

    #loadingSpinnerContainer {
        position: fixed;
        inset: 0;
        display: none;
        background: rgba(17, 24, 39, .55);
        backdrop-filter: blur(2px);
        z-index: 2000;
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
                rgba(31, 41, 55, .9),
                rgba(17, 24, 39, .9));

        border: 1px solid rgba(255, 255, 255, .08);

        box-shadow:
            0 10px 30px rgba(0, 0, 0, .35),
            inset 0 0 0 1px rgba(255, 255, 255, .04);
    }

    #loadingSpinnerContainer .loading-spinner {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        border: 4px solid transparent;
        border-top-color: #6366f1;
        animation: spin 1s linear infinite;
        position: relative;
    }

    #loadingSpinnerContainer .loading-spinner::after {
        content: "";
        position: absolute;
        inset: 6px;
        border-radius: 50%;
        border: 4px solid transparent;
        border-left-color: #a5b4fc;
        animation: spinReverse .75s linear infinite;
    }

    #loadingSpinnerContainer .loading-text {
        color: #e5e7eb;
        font-weight: 600;
        letter-spacing: .02em;
    }

    #loadingSpinnerContainer .loading-ellipsis span {
        display: inline-block;
        animation: blink 1.4s infinite both;
    }

    #loadingSpinnerContainer .loading-ellipsis span:nth-child(2) {
        animation-delay: .2s;
    }

    #loadingSpinnerContainer .loading-ellipsis span:nth-child(3) {
        animation-delay: .4s;
    }

    .dark #loadingSpinnerContainer {
        background: rgba(0, 0, 0, .65);
    }

    /* =====================================================
FORM VALIDATION
===================================================== */

    .is-invalid {
        border-color: #ef4444 !important;
    }

    .error-feedback {
        display: block;
        color: #dc2626;
        font-size: 12px;
        margin-top: 6px;
    }

    .dark .error-feedback {
        color: #f87171;
    }


    .dark select:not([multiple]) {
        background-image: url("data:image/svg+xml,%3Csvg fill='%23d1d5db' viewBox='0 0 20 20'%3E%3Cpath d='M5.25 7.75L10 12.5l4.75-4.75'/%3E%3C/svg%3E");
    }


    /* =====================================================
SELECT2 STYLING
===================================================== */

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 42px;
        border-radius: .5rem;
        border: 1px solid #d1d5db;
        display: flex;
        align-items: center;
        background: #fff;
        transition: .18s ease;
    }

    .select2-container--default .select2-selection__rendered {
        line-height: 42px;
        padding-left: .75rem;
        color: #374151;
    }

    .select2-container--default .select2-selection__arrow {
        height: 42px;
        right: .5rem;
    }

    /* Hover */
    .select2-container--default:hover .select2-selection--single {
        border-color: #9ca3af;
    }

    /* Focus */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, .15);
    }

    /* Dropdown */
    .select2-dropdown {
        border-radius: .5rem;
        border: 1px solid #d1d5db;
    }

    /* Option Hover */
    .select2-results__option--highlighted {
        background: #eef2ff !important;
        color: #4338ca !important;
    }


    /* =====================================================
SELECT2 DARK MODE
===================================================== */

    .dark .select2-container--default .select2-selection--single {
        background: #1f2937;
        border-color: #374151;
    }

    .dark .select2-container--default .select2-selection__rendered {
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


    /* =====================================================
FORM HELPERS
===================================================== */

    .req::after {
        content: "*";
        color: #ef4444;
        margin-left: 4px;
    }


    /* =====================================================
TRACK TAB UI
===================================================== */

    .track-tab {
        padding: .4rem .75rem;
        border-radius: .5rem;
        font-size: .875rem;
        font-weight: 600;
        color: #4b5563;
        white-space: nowrap;
    }

    .track-tab:hover {
        background: rgba(0, 0, 0, .05);
    }

    .track-tab.active {
        background: rgba(79, 70, 229, .12);
        color: #4338ca;
    }

    .dark .track-tab {
        color: #9ca3af;
    }

    .dark .track-tab:hover {
        background: rgba(255, 255, 255, .05);
    }

    .dark .track-tab.active {
        background: rgba(99, 102, 241, .25);
        color: #c7d2fe;
    }

    /* =====================================================
VENDOR TITLE WRAPPING
===================================================== */

    .vendor-title {
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.1;
    }


    .dark .vendor-title {
        color: #e5e7eb;
    }

    /* =====================================================
TAX INPUT COMPONENT
===================================================== */

    .tax-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .5rem;
        align-items: center;
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

    /* =====================================================
ICON BUTTON
===================================================== */

    .icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 16px;
        height: 16px;

        font-size: 10px;

        border: 1px solid #d1d5db;
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

    /* =====================================================
SUMMARY DISPLAY
===================================================== */

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
        /* gray-300 */
    }

    .dark .summary-value {
        color: #f9fafb;
        /* gray-50 */
    }

    /* =====================================================
TABLE TEXT WRAPPING
===================================================== */

    #applicantsTable td,
    #applicantsTable th {
        white-space: normal !important;
    }

    #applicantsTable td {
        word-break: break-word;
    }
</style>
