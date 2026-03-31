    <style>
        /* =====================================================
   STATUS FILTER — SOFT COLOR (DEFAULT)
===================================================== */

        /* ⚪ ALL */
        .status-filter[data-status="ALL"] .status-card,
        .status-filter[data-status=""] .status-card {
            background: rgba(229, 231, 235, .35);
            border-color: rgba(31, 41, 55, .35);
            color: rgb(75 85 99);
        }

        /* 🟧 ON PROGRESS */
        .status-filter[data-status="P"] .status-card {
            background: rgba(254, 215, 170, .25);
            border-color: rgba(194, 65, 12, .35);
            color: rgb(194 65 12);
        }

        /* 🟥 REJECT */
        .status-filter[data-status="R"] .status-card {
            background: rgba(254, 202, 202, .25);
            border-color: rgba(185, 28, 28, .35);
            color: rgb(185 28 28);
        }

        /* 🟨 HOLD / DRAFT */
        .status-filter[data-status="H,D"] .status-card,
        .status-filter[data-status="D"] .status-card {
            background: rgba(254, 243, 199, .35);
            border-color: rgba(180, 83, 9, .35);
            color: rgb(180 83 9);
        }

        /* 🟩 COMPLETED */
        .status-filter[data-status="C"] .status-card {
            background: rgba(187, 247, 208, .25);
            border-color: rgba(21, 128, 61, .35);
            color: rgb(21 128 61);
        }

        /* 🟪 TRACK */
        .status-filter[data-status="TRACK"] .status-card {
            background: rgba(233, 213, 255, .25);
            border-color: rgba(126, 34, 206, .35);
            color: rgb(126 34 206);
        }

        /* 🔵 UNREAD */
        .status-filter[data-status="is_read_N"] .status-card {
            background: rgba(191, 219, 254, .25);
            border-color: rgba(29, 78, 216, .35);
            color: rgb(29 78 216);
        }

        /* ⚪ READ */
        .status-filter[data-status="is_read_Y"] .status-card {
            background: rgba(243, 244, 246, .35);
            border-color: rgba(107, 114, 128, .35);
            color: rgb(75 85 99);
        }


        /* =====================================================
   STATUS FILTER — ACTIVE COLOR
===================================================== */

        .status-filter.active .status-card {
            transform: scale(1.02);
        }

        /* ⚪ ALL */
        .status-filter[data-status="ALL"].active .status-card,
        .status-filter[data-status=""].active .status-card {
            background: rgb(229 231 235);
            border-color: rgb(31 41 55);
            color: rgb(31 41 55);
        }

        /* 🟧 ON PROGRESS */
        .status-filter[data-status="P"].active .status-card {
            background: rgb(254 215 170);
            border-color: rgb(194 65 12);
            color: rgb(194 65 12);
        }

        /* 🟥 REJECT */
        .status-filter[data-status="R"].active .status-card {
            background: rgb(254 202 202);
            border-color: rgb(185 28 28);
            color: rgb(185 28 28);
        }

        /* 🟨 HOLD / DRAFT */
        .status-filter[data-status="H,D"].active .status-card,
        .status-filter[data-status="D"].active .status-card {
            background: rgb(251 191 36);
            border-color: rgb(180 83 9);
            color: rgb(120 53 15);
        }

        /* 🟩 COMPLETED */
        .status-filter[data-status="C"].active .status-card {
            background: rgb(187 247 208);
            border-color: rgb(21 128 61);
            color: rgb(21 128 61);
        }

        /* 🟪 TRACK */
        .status-filter[data-status="TRACK"].active .status-card {
            background: rgb(233 213 255);
            border-color: rgb(126 34 206);
            color: rgb(126 34 206);
        }

        /* 🔵 UNREAD */
        .status-filter[data-status="is_read_N"].active .status-card {
            background: rgb(191 219 254);
            border-color: rgb(29 78 216);
            color: rgb(29 78 216);
        }

        /* ⚪ READ */
        .status-filter[data-status="is_read_Y"].active .status-card {
            background: rgb(243 244 246);
            border-color: rgb(107 114 128);
            color: rgb(75 85 99);
        }


        /* =====================================================
   SCOPE FILTER — SOFT COLOR
===================================================== */

        /* ⚪ ALL */
        .scope-filter[data-scope="my"] .scope-card {
            background: rgba(229, 231, 235, .35);
            border-color: rgba(31, 41, 55, .35);
            color: rgb(75 85 99);
        }

        /* 🟧 JOB FLOW */
        .scope-filter[data-scope="calrjobs"] .scope-card,
        .scope-filter[data-scope="bastjobs"] .scope-card,
        .scope-filter[data-scope="receiptjobs"] .scope-card,
        .scope-filter[data-scope="issuejobsnew"] .scope-card,
        .scope-filter[data-scope="rfcajobs"] .scope-card,
        .scope-filter[data-scope="partial"] .scope-card,
        .scope-filter[data-scope="sppbprogress"] .scope-card,
        .scope-filter[data-scope="treasurypayment"] .scope-card,
        .scope-filter[data-scope="onprogress"] .scope-card {
            background: rgba(254, 215, 170, .25);
            border-color: rgba(194, 65, 12, .35);
            color: rgb(194 65 12);
        }

        /* 🔵 ACTIVE FLOW */

        .scope-filter[data-scope="hold"] .scope-card,
        .scope-filter[data-scope="financereceived"] .scope-card,
        .scope-filter[data-scope="purchase"] .scope-card {
            background: rgba(191, 219, 254, .25);
            border-color: rgba(29, 78, 216, .35);
            color: rgb(29 78 216);
        }

        /* 🟩 COMPLETED */
        .scope-filter[data-scope="completed"] .scope-card {
            background: rgba(187, 247, 208, .25);
            border-color: rgba(21, 128, 61, .35);
            color: rgb(21 128 61);
        }

        /* 🟪 TRACK */
        .scope-filter[data-scope="issuejobs"] .scope-card,
        .scope-filter[data-scope="tracking"] .scope-card {
            background: rgba(233, 213, 255, .25);
            border-color: rgba(126, 34, 206, .35);
            color: rgb(126 34 206);
        }

        /* 🟥 ISSUE PROGRESS */
        .scope-filter[data-scope="issueprogress"] .scope-card {
            background: rgba(254, 202, 202, .25);
            border-color: rgba(185, 28, 28, .35);
            color: rgb(185 28 28);
        }

        /* ⚪ NEUTRAL */
        .scope-filter[data-scope="all"] .scope-card,
        .scope-filter[data-scope="reuse"] .scope-card {
            background: rgba(229, 231, 235, .35);
            border-color: rgba(31, 41, 55, .35);
            color: rgb(75 85 99);
        }


        /* =====================================================
   SCOPE FILTER — ACTIVE COLOR
===================================================== */

        .scope-filter.active .scope-card {
            transform: scale(1.02);
        }

        /* ⚪ ALL */
        .scope-filter[data-scope="my"].active .scope-card {
            background: rgb(229 231 235);
            border-color: rgb(31 41 55);
            color: rgb(31 41 55);
        }

        /* 🟧 JOB FLOW */
        .scope-filter[data-scope="calrjobs"].active .scope-card,
        .scope-filter[data-scope="bastjobs"].active .scope-card,
        .scope-filter[data-scope="receiptjobs"].active .scope-card,
        .scope-filter[data-scope="issuejobsnew"].active .scope-card,
        .scope-filter[data-scope="rfcajobs"].active .scope-card,
        .scope-filter[data-scope="partial"].active .scope-card,
        .scope-filter[data-scope="sppbprogress"].active .scope-card,
        .scope-filter[data-scope="treasurypayment"].active .scope-card,
        .scope-filter[data-scope="onprogress"].active .scope-card {
            background: rgb(254 215 170);
            border-color: rgb(194 65 12);
            color: rgb(194 65 12);
        }

        /* 🔵 ACTIVE FLOW */

        .scope-filter[data-scope="hold"].active .scope-card,
        .scope-filter[data-scope="financereceived"].active .scope-card,
        .scope-filter[data-scope="purchase"].active .scope-card {
            background: rgb(191 219 254);
            border-color: rgb(29 78 216);
            color: rgb(29 78 216);
        }

        /* 🟩 COMPLETED */
        .scope-filter[data-scope="completed"].active .scope-card {
            background: rgb(187 247 208);
            border-color: rgb(21 128 61);
            color: rgb(21 128 61);
        }

        /* 🟪 TRACK */
        .scope-filter[data-scope="issuejobs"].active .scope-card,
        .scope-filter[data-scope="tracking"].active .scope-card {
            background: rgb(233 213 255);
            border-color: rgb(126 34 206);
            color: rgb(126 34 206);
        }

        /* 🟥 ISSUE PROGRESS */
        .scope-filter[data-scope="issueprogress"].active .scope-card {
            background: rgb(254 202 202);
            border-color: rgb(185 28 28);
            color: rgb(185 28 28);
        }

        /* ⚪ NEUTRAL */
        .scope-filter[data-scope="all"].active .scope-card,
        .scope-filter[data-scope="reuse"].active .scope-card {
            background: rgb(229 231 235);
            border-color: rgb(31 41 55);
            color: rgb(31 41 55);
        }

        /* =====================================================
   DARK MODE FIX — STATUS & SCOPE CARDS
===================================================== */

        .dark .status-card,
        .dark .scope-card {
            border-color: rgba(255, 255, 255, .12);
        }

        /* ⚪ ALL */
        .dark .status-filter[data-status="ALL"] .status-card,
        .dark .status-filter[data-status=""] .status-card,
        .dark .scope-filter[data-scope="my"] .scope-card {
            background: rgba(148, 163, 184, .15);
            color: #e5e7eb;
        }

        /* 🟧 ON PROGRESS */
        .dark .status-filter[data-status="P"] .status-card,
        .dark .scope-filter[data-scope="onprogress"] .scope-card {
            background: rgba(251, 146, 60, .18);
            color: #fdba74;
        }

        /* 🟥 REJECT */
        .dark .status-filter[data-status="R"] .status-card,
        .dark .scope-filter[data-scope="issueprogress"] .scope-card {
            background: rgba(239, 68, 68, .18);
            color: #f87171;
        }

        /* 🟨 HOLD / DRAFT */
        .dark .status-filter[data-status="H,D"] .status-card,
        .dark .status-filter[data-status="D"] .status-card {
            background: rgba(251, 191, 36, .18);
            color: #fcd34d;
        }

        /* 🟩 COMPLETED */
        .dark .status-filter[data-status="C"] .status-card,
        .dark .scope-filter[data-scope="completed"] .scope-card {
            background: rgba(34, 197, 94, .18);
            color: #86efac;
        }

        /* 🟪 TRACK */
        .dark .status-filter[data-status="TRACK"] .status-card,
        .dark .scope-filter[data-scope="tracking"] .scope-card {
            background: rgba(168, 85, 247, .18);
            color: #c084fc;
        }

        /* 🔵 UNREAD / ACTIVE FLOW */
        .dark .status-filter[data-status="is_read_N"] .status-card,
        .dark .scope-filter[data-scope="hold"] .scope-card,
        .dark .scope-filter[data-scope="purchase"] .scope-card {
            background: rgba(59, 130, 246, .18);
            color: #93c5fd;
        }

        /* Active / Selected state */
        .filter-card.active {
            transform: scale(1.02);
        }

        /* ===== READONLY STYLE (Professional Look) ===== */
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

        /* Dark mode */
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

        /* SPPBJKT IN Progress */
        #btn-sppbjkt.active {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
        }

        #btn-completed.active {
            background-color: rgb(226 232 240);
            /* slate-200 */
            border-color: rgb(15 23 42);
            /* slate-900 */
            color: rgb(15 23 42);
        }

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
            z-index: 2000
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
            background: linear-gradient(180deg, rgba(31, 41, 55, .9), rgba(17, 24, 39, .9));
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35), inset 0 0 0 1px rgba(255, 255, 255, .04)
        }

        #loadingSpinnerContainer .loading-spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #6366f1;
            animation: spin 1s linear infinite;
            position: relative
        }

        #loadingSpinnerContainer .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-left-color: #a5b4fc;
            animation: spinReverse .75s linear infinite
        }

        #loadingSpinnerContainer .loading-text {
            color: #e5e7eb;
            font-weight: 600;
            letter-spacing: .02em
        }

        #loadingSpinnerContainer .loading-ellipsis span {
            display: inline-block;
            animation: blink 1.4s infinite both
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(2) {
            animation-delay: .2s
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(3) {
            animation-delay: .4s
        }

        .is-invalid {
            border-color: #ef4444 !important;
        }

        .error-feedback {
            display: block;
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
        }

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

        /* Option hover */
        .select2-results__option--highlighted {
            background: #eef2ff !important;
            color: #4338ca !important;
        }

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

        .req::after {
            content: "*";
            color: #ef4444;
            margin-left: 4px;
        }

        .track-tab, .track-tab-sppb {
            padding: .4rem .75rem;
            border-radius: .5rem;
            font-size: .875rem;
            font-weight: 600;
            color: #4b5563;
            white-space: nowrap;
        }

        .track-tab:hover, .track-tab-sppb:hover {
            background: rgba(0, 0, 0, .05)
        }

        .track-tab.active, .track-tab-sppb.active {
            background: rgba(79, 70, 229, .12);
            color: #4338ca
        }

        .vendor-title {
            white-space: normal;
            /* boleh turun baris */
            overflow-wrap: anywhere;
            /* pecah kata sangat panjang */
            word-break: break-word;
            /* jaga pecah kata yang wajar */
            line-height: 1.1;
            /* rapatkan sedikit */
        }

        .tax-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
            align-items: center
        }

        .tax-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem
        }

        .tax-input {
            width: 3.75rem;
            text-align: right;
            padding: .125rem .25rem
        }

        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            font-size: 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: #fff
        }

        .icon-btn:hover {
            background: #f3f4f6
        }

        .summary-label {
            font-size: 2rem;
            /* sedikit lebih besar dari  text-sm  */
            font-weight: 600;
            color: #374151;
            /* gray-700 */
        }

        .summary-value {
            font-size: 2rem;
            /* font lebih besar utk nominal */
            font-weight: 700;
            color: #111827;
            /* gray-900 */
        }
    </style>

    <style>
        /* =====================================================
   GLOBAL FORM SYSTEM — PROFESSIONAL BASE
===================================================== */

        /* ========== DESIGN TOKENS ========== */
        :root {
            --form-radius: .5rem;
            --form-border: #d1d5db;
            --form-border-hover: #9ca3af;
            --form-border-focus: #6366f1;
            --form-bg: #ffffff;
            --form-text: #374151;
            --form-placeholder: #9ca3af;
            --form-disabled-bg: #f3f4f6;
            --form-readonly-bg: #f9fafb;
        }

        /* ========== BASE INPUTS ========== */
        input:not([type="checkbox"]):not([type="radio"]),
        select,
        textarea {
            width: 100%;
            padding: .5rem .75rem;
            border-radius: var(--form-radius);
            border: 1px solid var(--form-border);
            background: var(--form-bg);
            color: var(--form-text);
            transition: all .18s ease;
            font-size: .875rem;
        }

        /* Placeholder */
        input::placeholder,
        textarea::placeholder {
            color: var(--form-placeholder);
        }

        /* Hover */
        input:not([type="checkbox"]):not([type="radio"]):hover,
        select:hover,
        textarea:hover {
            border-color: var(--form-border-hover);
        }

        /* Focus */
        input:not([type="checkbox"]):not([type="radio"]):focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--form-border-focus);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, .15);
        }

        /* Disabled */
        input:disabled,
        select:disabled,
        textarea:disabled {
            background: var(--form-disabled-bg);
            color: #9ca3af;
            cursor: not-allowed;
        }

        /* Readonly */
        input[readonly],
        textarea[readonly] {
            background: var(--form-readonly-bg);
            cursor: default;
        }

        /* ========== SELECT DROPDOWN ARROW FIX ========== */
        select {
            appearance: none;
            background-image:
                url("data:image/svg+xml,%3Csvg fill='%236b7280' viewBox='0 0 20 20'%3E%3Cpath d='M5.25 7.75L10 12.5l4.75-4.75'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .6rem center;
            background-size: 18px;
            padding-right: 2rem;
        }

        /* =====================================================
   CHECKBOX & RADIO — CLEAN MODERN STYLE
===================================================== */

        input[type="checkbox"],
        input[type="radio"] {
            width: 1rem;
            height: 1rem;
            cursor: pointer;
            accent-color: #6366f1;
            /* modern browser support */
        }

        /* Hover feel */
        input[type="checkbox"]:hover,
        input[type="radio"]:hover {
            transform: scale(1.05);
        }

        /* Disabled */
        input[type="checkbox"]:disabled,
        input[type="radio"]:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        /* =====================================================
   INVALID STATE
===================================================== */

        .is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, .15);
        }

        .error-feedback {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
        }

        /* =====================================================
   DARK MODE
===================================================== */

        .dark input:not([type="checkbox"]):not([type="radio"]),
        .dark select,
        .dark textarea {
            background: #1f2937;
            border-color: #374151;
            color: #e5e7eb;
        }

        .dark input::placeholder,
        .dark textarea::placeholder {
            color: #6b7280;
        }

        .dark input:hover,
        .dark select:hover,
        .dark textarea:hover {
            border-color: #4b5563;
        }

        .dark input:focus,
        .dark select:focus,
        .dark textarea:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 2px rgba(129, 140, 248, .2);
        }

        .dark input:disabled,
        .dark select:disabled,
        .dark textarea:disabled {
            background: #111827;
            color: #6b7280;
        }

        .dark input[readonly],
        .dark textarea[readonly] {
            background: #111827;
        }

        /* Checkbox/Radio Dark */
        .dark input[type="checkbox"],
        .dark input[type="radio"] {
            accent-color: #818cf8;
        }

        select:not([multiple]) {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;

            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%236b7280' viewBox='0 0 20 20'%3E%3Cpath d='M5.25 7.75L10 12.5l4.75-4.75'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            padding-right: 32px;
        }

        #applicantsTable td {
            white-space: normal !important;
            word-break: break-word;
        }


        #applicantsTable th {
            white-space: normal !important;
        }

        .dark .summary-label {
    color: #d1d5db; /* gray-300 */
}

.dark .summary-value {
    color: #f9fafb; /* gray-50 */
}
    </style>
