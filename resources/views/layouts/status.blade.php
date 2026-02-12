    <style>
        /* Active / Selected state */
        .status-filter.active .status-card {
            transform: scale(1.02);
        }

        /* Receipt Jobs */
        .scope-filter[data-scope="calrjobs"].active .scope-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12);
            /* orange-700 */
            color: rgb(194 65 12);
        }

        .status-filter[data-status=""].active .status-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12)
        }

        .status-filter[data-status="P"].active .status-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
        }

        .status-filter[data-status="R"].active .status-card {
            background-color: rgb(254 202 202);
            /* red-200 */
            border-color: rgb(185 28 28);
            /* red-700 */
        }

        .status-filter[data-status="H,D"].active .status-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
        }

        .status-filter[data-status="C"].active .status-card {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
        }

        .status-filter[data-status="TRACK"].active .status-card {
            background-color: rgb(233 213 255);
            /* purple-200 */
            border-color: rgb(126 34 206);
            /* purple-700 */
        }

        .status-filter[data-status="is_read_N"].active .status-card,
        .status-filter[data-status="P"].active .status-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
            color: rgb(29 78 216);
        }

        .status-filter[data-status="is_read_Y"].active .status-card,
        .status-filter[data-status="D"].active .status-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
            color: rgb(31 41 55);
        }

        /* Active / Selected state */
        .scope-filter.active .scope-card {
            transform: scale(1.02);
        }

        /* Receipt Jobs */
        .scope-filter[data-scope="bastjobs"].active .scope-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12);
            /* orange-700 */
            color: rgb(194 65 12);
        }

        /* On Progress */
        .scope-filter[data-scope="onprogress"].active .scope-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
            color: rgb(29 78 216);
        }

        /* Completed */
        .scope-filter[data-scope="completed"].active .scope-card {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
            color: rgb(21 128 61);
        }

        /* All */
        .scope-filter[data-scope="all"].active .scope-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
            color: rgb(31 41 55);
        }

        /* Active / Selected state */
        .filter-card.active {
            transform: scale(1.02);
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

        .status-filter.active .status-card {
            transform: scale(1.02);
        }

        .status-filter[data-filter="all"].active .status-card {
            background-color: rgb(254 215 170);
            border-color: rgb(194 65 12);
        }

        .status-filter[data-filter="jobs"].active .status-card {
            background-color: rgb(191 219 254);
            border-color: rgb(29 78 216);
        }

        .status-filter[data-filter="done"].active .status-card {
            background-color: rgb(187 247 208);
            border-color: rgb(21 128 61);
        }

        .status-filter[data-filter="inv"].active .status-card {
            background-color: rgb(224 231 255);
            border-color: rgb(67 56 202);
        }

        /* Issue Jobs */
        .scope-filter[data-scope="issuejobs"].active .scope-card {
            background-color: rgb(254 215 170);
            border-color: rgb(194 65 12);
            color: rgb(194 65 12);
        }

        /* On Progress */
        .scope-filter[data-scope="onprogress"].active .scope-card {
            background-color: rgb(191 219 254);
            border-color: rgb(29 78 216);
            color: rgb(29 78 216);
        }

        /* Completed */
        .scope-filter[data-scope="completed"].active .scope-card {
            background-color: rgb(187 247 208);
            border-color: rgb(21 128 61);
            color: rgb(21 128 61);
        }

        /* All */
        .scope-filter[data-scope="all"].active .scope-card {
            background-color: rgb(229 231 235);
            border-color: rgb(31 41 55);
            color: rgb(31 41 55);
        }

        /* My CS */
        .scope-filter[data-scope="my"].active .scope-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12);
            /* orange-700 */
            color: rgb(194 65 12);
        }

        /* Hold */
        .scope-filter[data-scope="hold"].active .scope-card {
            background-color: rgb(219 234 254);
            /* blue-100 */
            border-color: rgb(29 78 216);
            /* blue-700 */
            color: rgb(29 78 216);
        }

        /* Purchase */
        .scope-filter[data-scope="purchase"].active .scope-card {
            background-color: rgb(224 231 255);
            /* indigo-100 */
            border-color: rgb(67 56 202);
            /* indigo-700 */
            color: rgb(67 56 202);
        }

        /* Partial */
        .scope-filter[data-scope="partial"].active .scope-card {
            background-color: rgb(254 243 199);
            /* amber-100 */
            border-color: rgb(180 83 9);
            /* amber-700 */
            color: rgb(180 83 9);
        }

        /* Reuse */
        .scope-filter[data-scope="reuse"].active .scope-card {
            background-color: rgb(243 244 246);
            /* gray-100 */
            border-color: rgb(31 41 55);
            /* gray-700 */
            color: rgb(31 41 55);
        }

        .scope-filter[data-scope="receiptjobs"].active .scope-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12);
            /* orange-700 */
            color: rgb(194 65 12);
        }

        /* Issue On Progress */
        .scope-filter[data-scope="issueprogress"].active .scope-card {
            background-color: rgb(254 202 202);
            border-color: rgb(185 28 28);
            color: rgb(185 28 28);
        }

        /* SPPB On Progress */
        .scope-filter[data-scope="sppbprogress"].active .scope-card {
            background-color: rgb(254 249 195);
            border-color: rgb(133 77 14);
            color: rgb(133 77 14);
        }

        /* Issue New Jobs */
        .scope-filter[data-scope="issuejobsnew"].active .scope-card {
            background-color: rgb(254 215 170);
            border-color: rgb(194 65 12);
            color: rgb(194 65 12);
        }

        /* Issue Jobs */
        .scope-filter[data-scope="issuejobs"].active .scope-card {
            background-color: rgb(221 214 254);
            border-color: rgb(91 33 182);
            color: rgb(91 33 182);
        }

        .scope-filter[data-scope="rfcajobs"].active .scope-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12);
            /* orange-700 */
            color: rgb(194 65 12);
        }

        /* Finance Received */
        .scope-filter[data-scope="financereceived"].active .scope-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
            color: rgb(29 78 216);
        }

        /* Treasury Payment */
        .scope-filter[data-scope="treasurypayment"].active .scope-card {
            background-color: rgb(254 249 195);
            /* yellow-100 */
            border-color: rgb(202 138 4);
            /* yellow-600 */
            color:

                rgb(202 138 4);
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

        /* tinggi ~42px seperti p-2.5 Tailwind */
        .select2-container .select2-selection--single {
            height: 42px;
            border-radius: 0.5rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
            padding-left: .75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
            right: .5rem;
        }

        .req::after {
            content: " *";
        }

        .track-tab {
            padding: .4rem .75rem;
            border-radius: .5rem;
            font-size: .875rem;
            font-weight: 600;
            color: #4b5563;
            white-space: nowrap;
        }

        .track-tab:hover {
            background: rgba(0, 0, 0, .05)
        }

        .track-tab.active {
            background: rgba(79, 70, 229, .12);
            color: #4338ca
        }
    </style>
