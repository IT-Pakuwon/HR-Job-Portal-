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

    table.dataTable tbody tr td {
        padding: 8px;
        line-height: 2;
        font-size: 12px;
    }

    table.dataTable tbody tr {
        transition: background-color .3s ease, color .3s ease;
    }

    table.dataTable tbody tr:hover {
        background-color: #8f8f8f11;
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
        width: 40px;
        height: 22px;
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
        background: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        content: "";
        position: absolute;
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background: #fff;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background: #4CAF50;
    }

    input:checked+.slider:before {
        transform: translateX(18px);
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
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        float: none !important;
        margin: 1px 4px 1px 0 !important;
    }

    .select2-container--default .select2-selection--multiple .select2-search__field {
        margin-top: 2px !important;
        padding: 0 !important;
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
        font-size: 12px;
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
    #approvalTable td.col-actions {
        width: 70px;
    }

    #approvalTable th.col-level,
    #approvalTable td.col-level {
        width: 60px;
        text-align: center;
    }

    #approvalTable th.col-doctype,
    #approvalTable td.col-doctype {
        width: 60px;
    }

    #approvalTable th.col-status,
    #approvalTable td.col-status {
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
    #approvalTable td.col-name {
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
        border-top-color: #6366f1;
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
        border-left-color: #a5b4fc;
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
        /* gray-600 */
    }

    /* input inside multi-select box */
    .dark .select2-container--default .select2-selection--multiple .select2-search__field {
        background-color: transparent;
        color: #e5e7eb;
    }

    /* placeholder text */
    .dark .select2-container--default .select2-search__field::placeholder {
        color: #9ca3af;
        /* gray-400 */
    }

    /* focus state */
    .dark .select2-container--default .select2-search__field:focus {
        border-color: #6366f1;
        /* indigo-500 */
        outline: none;
        box-shadow: 0 0 0 1px rgba(99, 102, 241, .4);
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
    }

    .dark input::placeholder {
        color: #9ca3af;
        /* gray-400 */
    }

    /* focus */
    .dark input:focus,
    .dark select:focus {
        border-color: #6366f1;
        /* indigo-500 */
        box-shadow: 0 0 0 1px rgba(99, 102, 241, .4);
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
        background-color: #4f46e5 !important;
    }

    .dark .bg-blue-600:hover {
        background-color: #4338ca !important;
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
</style>
