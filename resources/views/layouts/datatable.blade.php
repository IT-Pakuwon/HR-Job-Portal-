<style>
    /* =====================================================
GLOBAL / UTILITY
===================================================== */

    .no-pointer {
        pointer-events: none;
    }

    .no-border {
        border: none !important;
    }

    .grid {
        width: 100%;
    }

    .col-wrap {
        white-space: normal;
        word-break: break-word;
    }

    select,
    textarea,
    {
    width: 100%;
    }

    @layer base {

        select,
        option,
        input {
            @apply text-[11px];
        }
    }

    /* Global text wrapping */
    * {
        overflow-wrap: break-word;
        word-break: break-word;
    }


    /*
    /* =====================================================
DATATABLES BASE LAYOUT
===================================================== */

    table.dataTable {
        width: 100% !important;
    }

    .dataTables_wrapper {
        width: 100%;
    }

    @media (max-width: 600px) {
        .dataTables_wrapper {
            padding: 0 10px;
        }
    }

    /* =====================================================
SEARCH / FILTER / LENGTH CONTROLS (GLOBAL)
===================================================== */
    .dataTables_wrapper .dt-toolbar {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 14px;
    }

    /* Length + Buttons group */
    .dataTables_wrapper .dataTables_length {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #4b5563;
    }



    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 8px 18px;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 8px 18px;
    }

    /* LENGTH (Show entries) */
    .dataTables_wrapper .dataTables_length select {
        width: auto !important;
        min-width: 30px !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
        background: #f9fafb !important;
        /* font-size: 14px !important; */
    }

    /* SEARCH */
    .dataTables_wrapper .dataTables_filter input {
        width: auto !important;
        min-width: 30px !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
        background: #f9fafb !important;
        /* font-size: 14px !important; */
    }

    /* =====================================================
TABLE CELLS & ROWS (GLOBAL)
===================================================== */

    table.dataTable td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    table.dataTable th,
    table.dataTable td {
        padding: 10px;
        max-width: 200px;
        min-width: 32px;
        font-size: 12px;
        align-items: center;
        justify-items: center;
    }

    /* Header row banner (Access Request style, applied globally) */
    table.dataTable thead th {
        background-color: rgba(249, 250, 251, .7);
        border-bottom: 1px solid #f3f4f6;
        color: #6b7280;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        text-align: left;
        padding: 12px 16px;
        white-space: nowrap;
    }

    /* DataTables' CDN css loads after this file and uses a dark header/table border — override */
    table.dataTable > thead > tr > th,
    table.dataTable > thead > tr > td {
        border-bottom: 1px solid #f3f4f6 !important;
        background-color: rgba(249, 250, 251, .7);
        color: #6b7280;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 12px 16px;
    }

    table.dataTable.no-footer {
        border-bottom: 1px solid #f3f4f6 !important;
    }

    /* Blank header cells (checkbox / expand columns) get no sort icon */
    table.dataTable > thead > tr > th.sorting:empty::after,
    table.dataTable > thead > tr > th.sorting_asc:empty::after,
    table.dataTable > thead > tr > th.sorting_desc:empty::after {
        display: none !important;
    }

    /* scrollX/scrollY: DataTables keeps a hidden sizing copy of the header inside the body table — keep it collapsed */
    .dataTables_wrapper .dataTables_scrollBody table.dataTable > thead > tr > th,
    .dataTables_wrapper .dataTables_scrollBody table.dataTable > thead > tr > td {
        height: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        border: 0 !important;
        font-size: 0 !important;
        line-height: 0 !important;
        overflow: hidden;
    }

    .dataTables_wrapper .dataTables_scrollBody table.dataTable > thead > tr > th.sorting::before,
    .dataTables_wrapper .dataTables_scrollBody table.dataTable > thead > tr > th.sorting_asc::before,
    .dataTables_wrapper .dataTables_scrollBody table.dataTable > thead > tr > th.sorting_desc::before,
    .dataTables_wrapper .dataTables_scrollBody table.dataTable > thead > tr > th.sorting::after,
    .dataTables_wrapper .dataTables_scrollBody table.dataTable > thead > tr > th.sorting_asc::after,
    .dataTables_wrapper .dataTables_scrollBody table.dataTable > thead > tr > th.sorting_desc::after {
        display: none !important;
        content: none !important;
    }

    .dark table.dataTable > thead > tr > th,
    .dark table.dataTable > thead > tr > td {
        background-color: rgba(255, 255, 255, .02);
        color: #9ca3af;
        border-bottom-color: rgba(255, 255, 255, .06) !important;
    }

    .dark table.dataTable.no-footer {
        border-bottom-color: rgba(255, 255, 255, .06) !important;
    }

    .dark .dataTables_wrapper .dataTables_info {
        color: #9ca3af;
    }

    .dataTables_wrapper .dataTables_info {
        font-size: 12px;
        color: #6b7280;
        padding-top: 14px;
    }

    /* Sort icons: match SIK header (inline icon after label, grey idle / indigo active).
       Selectors mirror DataTables' own (thead > tr > th) so they win on specificity. */
    table.dataTable thead > tr > th.sorting,
    table.dataTable thead > tr > th.sorting_asc,
    table.dataTable thead > tr > th.sorting_desc,
    table.dataTable thead > tr > th.sorting_asc_disabled,
    table.dataTable thead > tr > th.sorting_desc_disabled {
        background-image: none !important;
        padding-right: 16px !important;
        cursor: pointer;
        user-select: none;
    }

    table.dataTable thead > tr > th.sorting:hover,
    table.dataTable thead > tr > th.sorting_asc:hover,
    table.dataTable thead > tr > th.sorting_desc:hover {
        color: #3193da;
    }

    table.dataTable thead > tr > th.sorting::before,
    table.dataTable thead > tr > th.sorting_asc::before,
    table.dataTable thead > tr > th.sorting_desc::before,
    table.dataTable thead > tr > th.sorting_asc_disabled::before,
    table.dataTable thead > tr > th.sorting_desc_disabled::before {
        content: none !important;
        display: none !important;
    }

    table.dataTable thead > tr > th.sorting::after,
    table.dataTable thead > tr > th.sorting_asc::after,
    table.dataTable thead > tr > th.sorting_desc::after {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free" !important;
        font-weight: 900 !important;
        font-size: 10px !important;
        line-height: 1 !important;
        margin-left: 6px;
        position: static !important;
        display: inline-block !important;
        right: auto !important;
        bottom: auto !important;
        opacity: 1 !important;
        content: "\f0dc" !important;
        color: #d1d5db;
    }

    table.dataTable thead > tr > th.sorting_asc::after {
        content: "\f0de" !important;
        color: #3193da;
    }

    table.dataTable thead > tr > th.sorting_desc::after {
        content: "\f0dd" !important;
        color: #3193da;
    }

    table.dataTable tbody tr td {
        padding: 8px;
        line-height: 2;
        font-size: 12px;
    }

    table.dataTable tbody tr {
        transition: background-color .3s ease, color .3s ease;
    }

    table.dataTable tbody tr:hover {
        background-color: rgba(224, 242, 254, 0.5) !important;

        cursor: pointer;
    }

    /* =====================================================
DATATABLES TOOLBAR + BUTTONS
===================================================== */

    .dt-toolbar {
        display: flex !important;
        align-items: center !important;
        gap: 12px;
        margin-bottom: 10px;
    }

    .dataTables_length,
    .dt-buttons,
    .dataTables_filter {
        margin: 0 !important;
        display: flex;
        align-items: center;
    }

    .dataTables_filter {
        margin-left: auto !important;
    }

    /* Buttons */

    .dt-buttons {
        gap: 8px;
        margin-right: 12px;
    }

    .dt-button {
        display: inline-flex !important;
        align-items: center;
        gap: 6px;
        padding: 6px 12px !important;
        border-radius: 9999px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
        transition: all .2s ease-in-out;
    }

    .dt-button.buttons-excel {
        background: #dcfce7 !important;
        color: #166534 !important;
        border-color: #86efac !important;
    }

    .dt-button.buttons-excel:hover {
        background: #bbf7d0 !important;
    }

    .dt-button.buttons-csv {
        background: #e0f2fe !important;
        color: #075985 !important;
        border-color: #7dd3fc !important;
    }

    .dt-button.buttons-csv:hover {
        background: #bae6fd !important;
    }

    .dt-button:focus,
    .dt-button:active {
        outline: none !important;
        box-shadow: none !important;
    }

    /* =====================================================
RESPONSIVE CHILD ROW
===================================================== */

    table.dataTable>tbody>tr.child ul.dtr-details {
        width: 100%;
        margin: 0 auto;
    }

    table.dataTable>tbody>tr.child ul.dtr-details>li {
        display: flex;
        justify-content: flex-start;
        gap: 12px;
        text-align: left;
    }

    /* =====================================================
CUSTOM COMPONENTS (UNCHANGED)
===================================================== */

    /* ---- SWITCH ---- */
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: #ef4444;
        transition: .25s;
        border-radius: 9999px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .25s;
        border-radius: 9999px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
    }

    .switch input:checked + .slider {
        background-color: #16a34a;
    }

    .switch input:checked + .slider:before {
        transform: translateX(22px);
    }

    .switch input:disabled + .slider {
        opacity: .45;
        cursor: not-allowed;
    }

    /* ---- MENU TREE ---- */
    .menu-tree ul {
        margin-left: .75rem;
        padding-left: .75rem;
        border-left: 1px solid rgba(156, 163, 175, .6);
    }

    .menu-tree li {
        margin: 2px 0;
    }

    .tree-toggle {
        cursor: pointer;
        font-size: .75rem;
        padding: 0 4px;
    }

    /* ---- SELECT2 FIXES ---- */
    .select2-container--default .select2-selection--multiple {
        min-height: 34px;
        height: auto !important;
        overflow: visible !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex !important;
        flex-wrap: wrap !important;
        padding: 2px 4px !important;
        font-size: 11px
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        float: none !important;
        margin: 1px 4px 1px 0 !important;
        font-size: 11px
    }

    .select2-container--default .select2-selection--multiple .select2-search__field {
        margin-top: 2px !important;
        padding: 0 !important;
        font-size: 11px
    }

    /* =====================================================
TABLE-SPECIFIC OVERRIDES (KEEP)
===================================================== */

    /* Applicants filter header */
    #applicantsTable thead tr.filters th {
        padding: 6px 8px;
    }

    #applicantsTable thead .input-filter,
    #applicantsTable thead .select-filter {
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 11px;
    }

    .dark #applicantsTable thead .input-filter,
    .dark #applicantsTable thead .select-filter {
        background: #374151;
        color: #e5e7eb;
        border-color: #4b5563;
    }

    /* Fix header alignment (SAFE) */
    #applicantsTable {
        table-layout: fixed;
    }

    /* Approval column widths */
    #approvalTable th.col-actions,
    #approvalTable td.col-actions,
    #gbApprovalTable th.col-actions,
    #gbApprovalTable td.col-actions {
        width: 70px;
    }

    #approvalTable th.col-level,
    #approvalTable td.col-level,
    #gbApprovalTable th.col-level,
    #gbApprovalTable td.col-level {
        width: 60px;
        text-align: center;
    }

    #approvalTable th.col-doctype,
    #approvalTable td.col-doctype,
    #gbApprovalTable th.col-doctype,
    #gbApprovalTable td.col-doctype {
        width: 60px;
    }

    #approvalTable th.col-status,
    #approvalTable td.col-status,
    #gbApprovalTable th.col-status,
    #gbApprovalTable td.col-status {
        width: 80px;
        text-align: center;
    }

    #approvalTable th.col-start,
    #approvalTable td.col-start,
    #approvalTable th.col-end,
    #approvalTable td.col-end {
        width: 100px;
    }

    #approvalTable th.col-name,
    #approvalTable td.col-name,
    #gbApprovalTable th.col-name,
    #gbApprovalTable td.col-name {
        width: 320px;
    }

    /* =====================================================
ROWGROUP (COMPANY GROUPING) – KEEP
===================================================== */

    tr.collapsed-group-row {
        display: none;
    }

    tr.group-row {
        background: #e6e6e6;
        font-weight: bold;
        cursor: pointer;
        user-select: none;
        color: #333;
    }

    tr.group-row:hover {
        background: #d4d4d4;
    }

    tr.group-row .fas {
        margin-right: 8px;
        width: 16px;
        text-align: center;
    }

    tr.group-row td {
        padding: 10px !important;
        border-bottom: 1px solid #ddd;
    }

    tr.group-row td:first-child {
        border-left: none;
    }


/*


    .dataTables_wrapper .dataTables_filter input{
        margin-left:8px;

        height:40px;

        border-radius:10px;

        padding:0 14px;
    }

    .dataTables_wrapper .dataTables_length select{
        height:40px;

        border-radius:10px;

        padding:0 32px 0 10px;
    }

    .dataTables_wrapper .paginate_button{
        min-width:36px;
        height:36px;

        margin:0 3px;

        border-radius:8px !important;
    } */

    /* =====================================================
FOOTER: info + pagination (SIK style)
===================================================== */
    .dataTables_wrapper.no-footer .dataTables_scrollBody {
        border-bottom: 1px solid #f3f4f6 !important;
    }

    .dataTables_wrapper .dataTables_info {
        font-size: 14px !important;
        color: #6b7280 !important;
        padding: 12px 20px !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding: 8px 20px !important;
        display: flex;
        align-items: center;
        gap: 8px;
        float: right;
    }

    /* page numbers: 2px gap between each */
    .dataTables_wrapper .dataTables_paginate > span {
        display: inline-flex;
        align-items: center;
        gap: 2px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        height: 36px;
        min-width: 36px;
        margin: 0 !important;
        padding: 0 16px !important;
        font-size: 14px !important;
        color: #6b7280 !important;
        background: transparent !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
        box-shadow: none !important;
        cursor: pointer;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f9fafb !important;
        color: #3193da !important;
        border-color: #e5e7eb !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #e3f3ff !important;
        color: #3193da !important;
        border-color: #b6e1ff !important;
        font-weight: 600;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        opacity: .4;
        cursor: default;
        background: transparent !important;
        color: #6b7280 !important;
    }

    .dataTables_wrapper .dataTables_paginate .ellipsis {
        padding: 0 4px;
        color: #9ca3af;
    }

    .dark .dataTables_wrapper .dataTables_info {
        color: #9ca3af !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #9ca3af !important;
        border-color: rgba(255, 255, 255, .1) !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: rgba(255, 255, 255, .05) !important;
        color: #a0d7ff !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: rgba(86, 177, 243, .15) !important;
        color: #a0d7ff !important;
        border-color: rgba(86, 177, 243, .4) !important;
    }

    /* =====================================================
MISC
===================================================== */

    #tlList::-webkit-scrollbar {
        display: none;
    }

    #tlList {
        scrollbar-width: none;
    }

    #loadingSpinnerContainer {
        position: fixed;
        inset: 0;
        display: none;
        /* akan ditampilkan via JS */
        background: rgba(17, 24, 39, .55);
        backdrop-filter: blur(2px);
        z-index: 2000;
    }

    /* Kartu spinner di tengah */
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
        box-shadow: 0 10px 30px rgba(0, 0, 0, .35), inset 0 0 0 1px rgba(255, 255, 255, .04);
    }

    /* Spinner dual ring */
    #loadingSpinnerContainer .loading-spinner {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        border: 4px solid transparent;
        border-top-color: #56b1f3;
        /* indigo-500 */
        animation: spin 1s linear infinite;
        position: relative;
    }

    #loadingSpinnerContainer .loading-spinner::after {
        content: "";
        position: absolute;
        inset: 6px;
        border-radius: 50%;
        border: 4px solid transparent;
        border-left-color: #a0d7ff;
        /* indigo-200 */
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



    Kolom kecil table#applicantsTable td.small-col,
    table#applicantsTable th.small-col {
        width: 60px !important;
        max-width: 60px !important;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Applicant Table */
    #applicantsTable_filter {
        margin-bottom: 20px;
        display: flex;
        justify-content: flex-start;
        /* Aligns items to the left */
        align-items: center;
        /* Vertically aligns items */
    }

    #applicantsTable_filter label {
        margin-right: 2px;
    }

    #applicantsTable_filter input {
        width: 200px;
        /* Adjust the width of the input box */
        width: auto;
        padding: 5px;
        min-width: 80px;
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        background-color: #f9fafb;
    }

    #applicantsTable_wrapper {
        width: 100%;
    }

    /* Prevent text from wrapping */
    #applicantsTable td {
        white-space: nowrap;
        /* Prevent text from wrapping */
        overflow: hidden;
        /* Hide overflow content */
        text-overflow: ellipsis;
        /* Display ellipsis ("...") for overflowing content */
    }

    /* Optional: Adjust the width for table cells */
    #applicantsTable th,
    #applicantsTable td {
        padding: 10px;
        /* Adjust padding for better appearance */
        max-width: 200px;
        /* You can set a maximum width to control overflow */
    }


    #applicantsTable_length {
        width: auto;
        display: flex;
        justify-content: flex-start;
    }

    #applicantsTable_length select {
        width: auto;
        padding: 5px;
        min-width: 80px;
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        background-color: #f9fafb;

    }

    #applicantsTable_length select option {
        padding: 5px;
        /* Mengatur jarak antar opsi */
    }

    #applicantsTableinfo {
        margin-top: 10px;
        margin-bottom: 10px;
    }


    #applicantsTable tbody tr td {
        padding: 8px 8px;
        /* Adjust padding for uniform height */
        line-height: 2;
        /* Optional, for better text alignment */
    }

    #applicantsTable tbody tr {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    #applicantsTable tbody tr:hover {
        background-color: #8f8f8f11;
        opacity: 100%;
        cursor: pointer;
    }

    #applicantsTable tbody tr:hover td {
        /* color: black; */
    }

    /* ✅ Memperkecil Lebar Kolom Actions
    #jobpostingsTable th:nth-child(1),
    #jobpostingsTable td:nth-child(1) {
        width: 120px;
        text-align: center;
    }

    #jobpostingsTable th:nth-child(4),
    #jobpostingsTable td:nth-child(4) {
        width: 120px;
        text-align: center;
    }

    #w-full {
        width: 100% !important;
    } */

    .edu-col.hidden {
        display: none;
    }

    /* =====================================================
DARK MODE SUPPORT
===================================================== */

    .dark body {
        background-color: #111827;
        /* gray-900 */
        color: #e5e7eb;
        /* gray-200 */
    }

    /* ================= TABLE ================= */

    .dark table.dataTable {
        background-color: #1f2933;
        /* gray-800 */
        color: #e5e7eb;
    }

    .dark table.dataTable th {
        background-color: #374151;
        /* gray-700 */
        color: #f9fafb;
        /* gray-50 */
        border-color: #4b5563;
        /* gray-600 */
    }

    /* Header row banner - dark mode (Access Request style) */
    .dark table.dataTable thead th {
        background-color: rgba(255, 255, 255, .02);
        color: #9ca3af;
        border-color: rgba(255, 255, 255, .06);
    }

    .dark table.dataTable td {
        background-color: #1f2933;
        /* gray-800 */
        color: #e5e7eb;
        border-color: #374151;
    }

    /* hover row */
    .dark table.dataTable tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.06);
    }

    /* ================= DATATABLE TOOLBAR ================= */

    .dark .dataTables_wrapper .dt-toolbar {
        color: #e5e7eb;
    }

    .dark .dataTables_length,
    .dark .dt-buttons,
    .dark .dataTables_filter {
        color: #e5e7eb;
    }

    /* select + search input */
    .dark .dataTables_wrapper .dataTables_length select,
    .dark .dataTables_wrapper .dataTables_filter input {
        background: #374151 !important;
        color: #e5e7eb !important;
        border-color: #4b5563 !important;
    }

    /* ================= BUTTONS ================= */

    .dark .dt-button {
        background: #374151 !important;
        color: #e5e7eb !important;
        border-color: #4b5563 !important;
    }

    .dark .dt-button:hover {
        background: #4b5563 !important;
    }

    /* Excel button */
    .dark .dt-button.buttons-excel {
        background: rgba(16, 185, 129, .15) !important;
        /* emerald */
        color: #6ee7b7 !important;
        border-color: rgba(16, 185, 129, .35) !important;
    }

    /* CSV button */
    .dark .dt-button.buttons-csv {
        background: rgba(59, 130, 246, .15) !important;
        /* blue */
        color: #93c5fd !important;
        border-color: rgba(59, 130, 246, .35) !important;
    }

    /* ================= SEARCH / FILTER ================= */

    .dark .dataTables_filter label,
    .dark .dataTables_length label {
        color: #d1d5db;
    }

    /* ================= CHILD ROW ================= */

    .dark table.dataTable>tbody>tr.child {
        background: #1f2933;
    }

    .dark table.dataTable>tbody>tr.child ul.dtr-details>li {
        color: #e5e7eb;
    }

    /* ================= GROUP ROW ================= */

    .dark tr.group-row {
        background: #374151;
        color: #f9fafb;
    }

    .dark tr.group-row:hover {
        background: #4b5563;
    }

    /* ================= SCROLLBAR (optional) ================= */

    .dark ::-webkit-scrollbar-thumb {
        background-color: #4b5563;
    }

    .dark ::-webkit-scrollbar-track {
        background-color: #1f2933;
    }

    /* =====================================================
SELECT2 – DARK MODE SEARCH FIELD
===================================================== */

    /* input inside dropdown */
    .dark .select2-container--default .select2-search--dropdown .select2-search__field {
        background-color: #1f2933;
        /* gray-800 */
        color: #e5e7eb;
        /* gray-200 */
        border: 1px solid #4b5563;
        font-size: 11px
            /* gray-600 */
    }

    /* input inside multi-select box */
    .dark .select2-container--default .select2-selection--multiple .select2-search__field {
        background-color: transparent;
        color: #e5e7eb;
        font-size: 11px
    }

    /* placeholder text */
    .dark .select2-container--default .select2-search__field::placeholder {
        color: #9ca3af;
        font-size: 11px
            /* gray-400 */
    }

    /* focus state */
    .dark .select2-container--default .select2-search__field:focus {
        border-color: #56b1f3;
        /* indigo-500 */
        outline: none;
        box-shadow: 0 0 0 1px rgba(86, 177, 243, .4);
        font-size: 11px
    }

    /* =====================================================
IMPORT BUDGET – DARK MODE SUPPORT
===================================================== */

    /* ---------- Card / Panel ---------- */
    .dark .bg-white {
        background-color: #1f2933 !important;
        /* gray-800 */
    }

    /* ---------- Headers ---------- */
    .dark h2,
    .dark h5 {
        color: #f9fafb;
        font-size: 11px
            /* gray-50 */
    }

    /* ---------- Borders ---------- */
    .dark .border,
    .dark .border-b {
        border-color: #4b5563 !important;
        /* gray-600 */
    }

    /* ---------- Labels ---------- */
    .dark label {
        color: #d1d5db;
        /* gray-300 */
    }

    /* ---------- Inputs & Select ---------- */
    .dark input[type="text"],
    .dark input[type="file"],
    .dark select {
        background-color: #1f2933;
        color: #e5e7eb;
        border-color: #4b5563;
        font-size: 11px
    }

    .dark input::placeholder {
        color: #9ca3af;
        /* gray-400 */
    }

    /* focus */
    .dark input:focus,
    .dark select:focus {
        border-color: #56b1f3;
        /* indigo-500 */
        box-shadow: 0 0 0 1px rgba(86, 177, 243, .4);
    }

    /* ---------- File input button ---------- */
    .dark input[type="file"]::file-selector-button {
        background: #374151;
        color: #e5e7eb;
    }

    .dark input[type="file"]::file-selector-button:hover {
        background: #4b5563;
    }

    /* ---------- Table Preview ---------- */
    .dark table {
        background-color: #1f2933;
        color: #e5e7eb;
    }

    .dark thead {
        background-color: #374151;
        color: #f9fafb;
    }

    .dark tbody tr {
        border-color: #374151;
    }

    .dark tbody tr:hover {
        background-color: rgba(255, 255, 255, .06);
    }

    /* ---------- Preview badge ---------- */
    .dark h5.bg-red-100\/50 {
        background-color: rgba(185, 28, 28, .25) !important;
        color: #fca5a5 !important;
    }

    /* ---------- Buttons ---------- */

    /* primary */
    .dark .bg-blue-600 {
        background-color: #3193da !important;
    }

    .dark .bg-blue-600:hover {
        background-color: #1c71ae !important;
    }

    /* secondary */
    .dark .bg-gray-200 {
        background-color: #374151 !important;
        color: #e5e7eb !important;
    }

    .dark .bg-gray-200:hover {
        background-color: #4b5563 !important;
    }

    /* success */
    .dark .bg-green-600 {
        background-color: #16a34a !important;
    }

    .dark .bg-green-600:hover {
        background-color: #15803d !important;
    }

    /* ---------- Attachment area ---------- */
    .dark .attachment-row input[type="file"] {
        background-color: #1f2933;
        color: #e5e7eb;
        border-color: #4b5563;
    }

    .dark .attachment-row button.removeAttachment {
        border-color: #ef4444;
        color: #fca5a5;
    }

    .dark .attachment-row button.removeAttachment:hover {
        background-color: #dc2626;
        color: #fff;
    }

    /* ---------- Details summary ---------- */
    .dark summary {
        color: #e5e7eb;
    }

    /* ---------- Add attachment button ---------- */
    .dark #addAttachment {
        background-color: rgba(255, 255, 255, .05);
        border-color: #4b5563;
        color: #e5e7eb;
    }

    .dark #addAttachment:hover {
        background-color: rgba(239, 68, 68, .15);
        border-color: #ef4444;
        color: #fca5a5;
    }

    /* =====================================================
DARK MODE – HEADER FIX (ONLY FOR TABS)
Approval / Attachment / Comments tables
===================================================== */

    /* wrapper of your tabs */
    .dark [x-show="activeTab === 'approval'"] thead,
    .dark [x-show="activeTab === 'attachment'"] thead {
        background-color: #1f2937;
        /* gray-700 */
    }

    .dark [x-show="activeTab === 'approval'"] thead th,
    .dark [x-show="activeTab === 'attachment'"] thead th {
        background-color: #1f2937;
        color: #e5e7eb;
        /* gray-200 */
    }

    /* ===============================
   SELECT2 – DARK MODE SUPPORT
=============================== */

    /* Dropdown container */
    .dark .select2-dropdown {
        background-color: #1f2933;
        /* gray-800 */
        border-color: #374151;
        /* gray-700 */
        font-size: 11px color: #e5e7eb;
        /* gray-200 */
    }

    /* Dropdown when opens below */
    .dark .select2-dropdown--below {
        background-color: #1f2933;
        border-color: #374151;
    }

    /* Search input inside dropdown */
    .dark .select2-search__field {
        background-color: #111827;
        /* gray-900 */
        border: 1px solid #374151;
        color: #f9fafb;
        font-size: 11px
    }

    .dark .select2-search__field::placeholder {
        color: #9ca3af;
        font-size: 11px
            /* gray-400 */
    }

    /* Options */
    .dark .select2-results__option {
        background-color: transparent;
        color: #e5e7eb;
        font-size: 11px
    }

    /* Hover option */
    .dark .select2-results__option--highlighted {
        background-color: #374151;
        /* gray-700 */
        color: #fff;
        font-size: 11px
    }

    /* Selected option */
    .dark .select2-results__option--selected {
        background-color: #4b5563;
        /* gray-600 */
        color: #fff;
        font-size: 11px
    }

    /* No results */
    .dark .select2-results__message {
        color: #9ca3af;
        font-size: 11px
    }

    /* Dark mode text */
    .dark .select2-selection__rendered {
        color: #e5e7eb;
        /* light text */
        font-size: 11px
    }

    /* Placeholder text */
    .dark .select2-selection__placeholder {
        color: #9ca3af;
        /* muted gray */
        font-size: 11px
    }

    .dark .select2-container--default .select2-selection--single {
        background-color: #1f2937;
        /* gray-800 */
        border-color: #374151;
        /* gray-700 */
    }

    /* =====================================
   Base select
   ===================================== */

    .dark select {
        background-color: #1f2937;
        /* gray-800 */
        color: #e5e7eb;
        /* text */
        border: 1px solid #374151;
        /* gray-700 */
        border-radius: 0.5rem;
        padding: 0.5rem 2.5rem 0.5rem 0.75rem;
        /* height: 42px; */
        font-size: 11px;

        /* remove default OS styles */
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;

        /* custom arrow */
        background-image:
            linear-gradient(45deg, transparent 50%, #9ca3af 50%),
            linear-gradient(135deg, #9ca3af 50%, transparent 50%);
        background-position:
            calc(100% - 18px) 55%,
            calc(100% - 12px) 55%;
        background-size: 6px 6px, 6px 6px;
        background-repeat: no-repeat;
    }

    /* =====================================
   Focus
   ===================================== */

    .dark select:focus {
        outline: none;
        border-color: #56b1f3;
        /* indigo */
        font-size: 11px box-shadow: 0 0 0 1px #56b1f3;
    }

    /* =====================================
   Disabled
   ===================================== */

    .dark select:disabled {
        background-color: #111827;
        color: #6b7280;
        cursor: not-allowed;
        font-size: 11px
    }

    /* =====================================
   Option list (limited support)
   ===================================== */

    .dark select option {
        background-color: #1f2937;
        color: #e5e7eb;
        font-size: 11px
    }

    /* Hover / selected (browser dependent) */
    .dark select option:checked {
        background-color: #3193da;
        color: #ffffff;
        font-size: 11px
    }

    /* ===============================
   Select2 Single (Dark Mode)
   =============================== */

    .dark .select2-container--default .select2-selection--single {
        background-color: #1f2937;
        /* gray-800 */
        border: 1px solid #374151;
        /* gray-700 */
        height: 36px;
        font-size: 11px
    }

    /* Selected text */
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #e5e7eb;
        /* gray-200 */
        font-size: 11px
    }

    /* Placeholder */
    .dark .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
        /* gray-400 */
        font-size: 11px
    }

    /* Arrow */
    .dark .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #9ca3af transparent transparent transparent;
    }

    /* ===== Select2 Dark Mode ===== */
    .dark .select2-container--default .select2-selection--multiple {
        background-color: #374151;
        /* gray-700 */
        border-color: #4b5563;
        /* gray-600 */
        color: #f9fafb;
        /* gray-50 */
    }

    .dark .select2-selection--multiple .select2-selection__rendered {
        color: #f9fafb;
    }

    /* Selected item (chip) */
    .dark .select2-selection--multiple .select2-selection__choice {
        background-color: #4b5563;
        /* gray-600 */
        border: 1px solid #6b7280;
        /* gray-500 */
        color: #f9fafb;
    }

    /* Remove (x) button */
    .dark .select2-selection__choice__remove {
        color: #d1d5db;
        /* gray-300 */
    }

    .dark .select2-selection__choice__remove:hover {
        color: #f87171;
        /* red-400 */
    }

    /* Search input */
    .dark .select2-search__field {
        color: #f9fafb;
    }

    /* Dropdown */
    .dark .select2-dropdown {
        background-color: #1f2933;
        /* gray-800 */
        border-color: #4b5563;
    }

    /* Dropdown options */
    .dark .select2-results__option {
        color: #e5e7eb;
    }

    /* Hovered option */
    .dark .select2-results__option--highlighted {
        background-color: #4b5563;
        color: #ffffff;
    }

    /* Selected option */
    .dark .select2-results__option[aria-selected="true"] {
        background-color: #374151;
    }

    .budget-wrap {
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    /* DataTables responsive control column */
    /* table.dataTable th.dtr-control,
    table.dataTable td.dtr-control {
        width: 28px !important;
        min-width: 28px !important;
        max-width: 28px !important;
        padding-left: 8px !important;
        padding-right: 8px !important;
        text-align: center;
    } */

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 40px !important;
        border: 1px solid #e9e9ea;
        /* = border-gray-300 */
        border-radius: 0.375rem;
        /* = rounded-md */
        background-color: #f5f5f59d;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        padding-left: 10px;
        /* biar sejajar dengan p-2.5 */
        padding-right: 28px;
        color: #111827;
        /* text-gray-900 */
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
        right: 6px;
    }

    /* Optional: Dark mode */
    .dark .select2-container--default .select2-selection--single {
        background-color: #1f2937;
        /* gray-800 */
        border-color: #4b5563;
        /* gray-600 */
    }

    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #e5e7eb;
        /* gray-200 */
    }

    .dark .select2-dropdown {
        background-color: #111827;
        /* gray-900 */
        color: #e5e7eb;
        border-color: #374151;
        /* gray-700 */
    }

    .dark .select2-results__option--highlighted {
        background-color: #2563eb;
        /* blue-600 */
        color: #fff;
    }

    button {
        white-space: nowrap;
    }

    table.dataTable tbody td {
        white-space: normal !important;
        word-break: break-word;
    }

    /* Side gap like SIK (p-2 around table): added by the script below only when the table's container has no padding */
    .dataTables_wrapper.dt-inset > table.dataTable,
    .dataTables_wrapper.dt-inset > .dataTables_scroll,
    .dataTables_wrapper.dt-inset > .dtfc-scroll {
        margin-left: 8px !important;
        margin-right: 8px !important;
        margin-top: 8px !important;
        width: calc(100% - 16px) !important;
    }
</style>
<script>
    // Give DataTables tables the same edge gap as SIK when their container has none.
    (function() {
        function inset(w) {
            if (w.dataset.dtInsetChecked) return;
            w.dataset.dtInsetChecked = '1';
            var p = w.parentElement;
            if (!p) return;
            var cs = getComputedStyle(p);
            if (parseFloat(cs.paddingLeft) < 4 && parseFloat(cs.paddingRight) < 4) w.classList.add('dt-inset');
        }
        function scan(root) {
            if (root.nodeType !== 1) return;
            if (root.classList.contains('dataTables_wrapper')) inset(root);
            root.querySelectorAll && root.querySelectorAll('.dataTables_wrapper').forEach(inset);
        }
        new MutationObserver(function(ms) {
            ms.forEach(function(m) { m.addedNodes.forEach(scan); });
        }).observe(document.documentElement, { childList: true, subtree: true });
    })();
</script>

<style>
    /* =====================================================
    DATATABLES SEARCH BOX — icon inside, no "Search:" label, soft focus ring
    ===================================================== */
    .dataTables_wrapper .dataTables_filter label {
        font-size: 0 !important;      /* hides the "Search:" text, input restores its own size */
        display: flex !important;
        align-items: center;
        margin: 0 !important;
    }

    .dataTables_wrapper .dataTables_filter input[type="search"],
    .dataTables_wrapper .dataTables_filter input {
        box-sizing: border-box;
        width: 16rem !important;
        max-width: 100%;
        height: 40px !important;
        margin: 0 !important;
        padding: 0 14px 0 38px !important;
        font-size: 14px !important;
        color: #1f2937 !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='m20 20-3.5-3.5'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: 12px center !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 10px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04) !important;
        outline: none !important;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .dataTables_wrapper .dataTables_filter input::placeholder { color: #9ca3af; }

    .dataTables_wrapper .dataTables_filter input:hover { border-color: #cbd5e1 !important; }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #56b1f3 !important;
        box-shadow: 0 0 0 4px rgba(86, 177, 243, .14) !important;
    }

    .dark .dataTables_wrapper .dataTables_filter input[type="search"],
    .dark .dataTables_wrapper .dataTables_filter input {
        color: #e5e7eb !important;
        background-color: rgba(255, 255, 255, .04) !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='m20 20-3.5-3.5'/%3E%3C/svg%3E") !important;
        border-color: rgba(255, 255, 255, .12) !important;
        box-shadow: none !important;
    }

    .dark .dataTables_wrapper .dataTables_filter input:hover { border-color: rgba(255, 255, 255, .22) !important; }

    .dark .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #7bc8ff !important;
        box-shadow: 0 0 0 4px rgba(123, 200, 255, .18) !important;
    }

    @media (max-width: 600px) {
        .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_filter label { width: 100%; }
        .dataTables_wrapper .dataTables_filter input { width: 100% !important; }
    }
</style>
<script>
    // Give every DataTables search box a "Search…" placeholder (the "Search:" label is hidden by CSS).
    (function() {
        function mark(root) {
            if (!root || root.nodeType !== 1) return;
            var inputs = root.matches && root.matches('.dataTables_filter input') ? [root] : (root.querySelectorAll ? root.querySelectorAll('.dataTables_filter input') : []);
            inputs.forEach(function(i) { if (!i.placeholder) i.placeholder = 'Search…'; });
        }
        document.addEventListener('DOMContentLoaded', function() { mark(document.body); });
        new MutationObserver(function(ms) {
            ms.forEach(function(m) { m.addedNodes.forEach(mark); });
        }).observe(document.documentElement, { childList: true, subtree: true });
    })();
</script>
