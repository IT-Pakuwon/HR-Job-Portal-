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
    </style>
