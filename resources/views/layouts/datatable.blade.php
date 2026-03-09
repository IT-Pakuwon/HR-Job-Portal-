<style>
    /* =====================================================
GLOBAL / UTILITY
===================================================== */

    .no-pointer {
        pointer-events: none
    }

    .no-border {
        border: none !important
    }

    .grid {
        width: 100%
    }

    .col-wrap {
        white-space: normal;
        word-break: break-word
    }

    select,
    textarea,
    input {
        width: 100%;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    * {
        overflow-wrap: break-word;
        word-break: break-word;
    }

    /* =====================================================
DATATABLE WRAPPER
===================================================== */

    .dataTables_wrapper {
        width: 100%;
        font-size: 13px;

        background: white;
        border-radius: 14px;
        padding: 14px;

        border: 1px solid #f0f0f0;
    }

    .dark .dataTables_wrapper {
        background: #111827;
        border: 1px solid #1f2937;
    }

    @media(max-width:600px) {
        .dataTables_wrapper {
            padding: 0 10px
        }
    }

    /* =====================================================
DATATABLE TOOLBAR
===================================================== */

    .dt-toolbar,
    .dataTables_length,
    .dataTables_filter,
    .dt-buttons {
        display: flex;
        align-items: center;
    }

    .dt-toolbar {
        gap: 10px;
        margin-bottom: 14px;
    }

    .dataTables_filter {
        margin-left: auto
    }

    /* =====================================================
SEARCH BAR
===================================================== */

    .dataTables_filter label {
        font-size: 0
    }

    .dataTables_filter input {

        width: 240px;

        border: 1px solid #e5e7eb;
        background: #f7f7f7;

        border-radius: 18px;

        padding: 8px 14px 8px 36px;

        font-size: 13px;

        transition: .15s ease;

        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%239ca3af' stroke-width='2' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 12px center;
        background-size: 14px;
    }

    .dataTables_filter input:hover {
        background: #f3f4f6
    }

    .dataTables_filter input:focus {
        outline: none;
        background: white;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .15);
    }

    /* =====================================================
SHOW ENTRIES
===================================================== */

    .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 8px;

        font-size: 13px;
        color: #6b7280;
    }

    .dataTables_length select {

        appearance: none;

        border: 1px solid #e5e7eb;
        background: #f7f7f7;

        border-radius: 14px;

        padding: 6px 26px 6px 10px;

        font-size: 13px;
        font-weight: 500;

        color: #374151;

        transition: .15s ease;

        cursor: pointer;
    }

    .dataTables_length select:hover {
        background: #f3f4f6
    }

    .dataTables_length select:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .15);
    }

    /* =====================================================
EXPORT BUTTONS
===================================================== */

    .dt-buttons {
        gap: 8px
    }

    .dt-button {

        display: inline-flex !important;
        align-items: center;
        gap: 6px;

        padding: 6px 12px !important;

        border-radius: 9999px !important;

        font-size: 12px !important;
        font-weight: 600 !important;

        border: 1px solid #e5e7eb;

        background: #fafafa;

        transition: .15s ease;
    }

    .dt-button:hover {
        background: #f3f4f6
    }

    .dt-button.buttons-excel {
        background: #dcfce7 !important;
        color: #166534 !important;
        border-color: #86efac !important;
    }

    .dt-button.buttons-excel:hover {
        background: #bbf7d0 !important
    }

    .dt-button.buttons-csv {
        background: #e0f2fe !important;
        color: #075985 !important;
        border-color: #7dd3fc !important;
    }

    .dt-button.buttons-csv:hover {
        background: #bae6fd !important
    }

    .dt-button:focus,
    .dt-button:active {
        outline: none !important;
        box-shadow: none !important;
    }

    /* =====================================================
TABLE STYLE
===================================================== */

    table.dataTable {

        width: 100% !important;

        border-collapse: separate !important;
        border-spacing: 0;

        overflow: hidden;

        background: white;

        font-size: 13px;
    }

    table.dataTable thead th {

        background: #fafafa;

        color: #6b7280;

        font-weight: 600;

        padding: 14px 12px;

        border-bottom: 1px solid #ececec;
    }

    table.dataTable th,
    table.dataTable td {
        padding: 10px;
        max-width: 200px;
        min-width: 32px;
        font-size: 12px;
    }

    table.dataTable tbody td {
        padding: 12px;
        border-bottom: 1px solid #f1f1f1;
        white-space: normal !important;
        word-break: break-word;
    }

    table.dataTable tbody tr {
        transition: .15s ease;
    }

    table.dataTable tbody tr:hover td {
        background-color: #f8fafc !important;
        cursor: pointer;
    }

    /* =====================================================
PAGINATION
===================================================== */

    .dataTables_paginate {

        display: flex;

        gap: 6px;

        margin-top: 10px;
    }

    .dataTables_paginate .paginate_button {

        border-radius: 10px;

        padding: 4px 10px;

        border: 1px solid transparent;

        color: #6b7280;

        font-size: 13px;

        transition: .15s ease;
    }

    .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6;
    }

    .dataTables_paginate .paginate_button.current {

        background: #6366f1;

        color: white !important;
    }

    /* =====================================================
INFO TEXT
===================================================== */

    .dataTables_info {
        font-size: 13px;
        color: #6b7280;
    }

    /* =====================================================
CUSTOM SWITCH
===================================================== */

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
        transition: .3s;
        border-radius: 34px;
    }

    .slider:before {
        content: "";
        position: absolute;
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background: white;
        transition: .3s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background: #4CAF50
    }

    input:checked+.slider:before {
        transform: translateX(18px)
    }

    /* =====================================================
MENU TREE
===================================================== */

    .menu-tree ul {
        margin-left: .75rem;
        padding-left: .75rem;
        border-left: 1px solid rgba(156, 163, 175, .6);
    }

    .menu-tree li {
        margin: 2px 0
    }

    .tree-toggle {
        cursor: pointer;
        font-size: .75rem;
        padding: 0 4px;
    }

    /* =====================================================
SCROLLBAR HIDE
===================================================== */

    #tlList::-webkit-scrollbar {
        display: none
    }

    #tlList {
        scrollbar-width: none
    }

    /* =====================================================
DARK MODE
===================================================== */

    .dark {
        background: #111827;
        color: #e5e7eb;
    }

    .dark body {
        background: #111827
    }

    .dark table.dataTable {
        background: #111827;
    }

    .dark table.dataTable thead th {
        background: #1f2937;
        border-bottom: 1px solid #374151;
        color: #9ca3af;
    }

    .dark table.dataTable tbody td {
        border-bottom: 1px solid #1f2937;
        color: #e5e7eb;
    }

    .dark table.dataTable tbody tr:hover td {
        background: #1f2937 !important;
    }

    .dark .dataTables_filter input,
    .dark .dataTables_length select {
        background: #1f2937;
        border: 1px solid #374151;
        color: #e5e7eb;
    }

    .dark .dataTables_filter input:hover,
    .dark .dataTables_length select:hover {
        background: #2b3443;
    }

    .dark .dataTables_paginate .paginate_button.current {
        background: #6366f1;
    }

    .dark .dataTables_info {
        color: #9ca3af;
    }

    /* Fix DataTables length dropdown */
    .dataTables_length select {
        width: auto !important;
        min-width: 60px;
    }

    /* Prevent label text breaking */
    .dataTables_length label {
        white-space: nowrap;
    }
</style>

<style>
    /* =====================================================
DATATABLE GLOBAL UI
Modern clean style
===================================================== */

    /* Toolbar layout */

    .dataTables_wrapper .dt-toolbar,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dt-buttons {
        display: flex;
        align-items: center;
    }

    .dataTables_wrapper .dt-toolbar {
        gap: 10px;
        margin-bottom: 14px;
    }

    .dataTables_wrapper .dataTables_filter {
        margin-left: auto;
    }

    /* =====================================================
SEARCH INPUT
===================================================== */

    .dataTables_filter label {
        font-size: 0;
        /* hide "Search:" text */
    }

    .dataTables_filter input {

        width: 220px !important;

        height: 32px;

        border-radius: 999px;

        border: 1px solid #e5e7eb;

        background: #f9fafb;

        padding: 4px 10px 4px 30px;

        font-size: 12px;

        transition: all .15s ease;

        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%239ca3af' stroke-width='2' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");

        background-repeat: no-repeat;
        background-position: 8px center;
        background-size: 14px;
    }

    .dataTables_filter input:hover {
        background: #f3f4f6;
    }

    .dataTables_filter input:focus {

        outline: none;

        background: white;

        border-color: #6366f1;

        box-shadow: 0 0 0 2px rgba(99, 102, 241, .15);
    }

    /* =====================================================
SHOW ENTRIES
===================================================== */

    .dataTables_length label {

        display: flex;

        align-items: center;

        gap: 6px;

        font-size: 12px;

        color: #6b7280;

        white-space: nowrap;
    }

    .dataTables_length select {

        width: auto !important;

        height: 30px;

        border-radius: 999px;

        border: 1px solid #e5e7eb;

        background: #f9fafb;

        padding: 2px 10px;

        font-size: 12px;

        cursor: pointer;

        transition: all .15s ease;
    }

    .dataTables_length select:hover {
        background: #f3f4f6;
    }

    .dataTables_length select:focus {

        outline: none;

        border-color: #6366f1;

        box-shadow: 0 0 0 2px rgba(99, 102, 241, .15);
    }

    /* =====================================================
EXPORT BUTTONS
===================================================== */

    .dt-buttons {
        gap: 8px;
    }

    .dt-button {

        display: inline-flex !important;

        align-items: center;

        gap: 6px;

        padding: 6px 12px !important;

        border-radius: 999px !important;

        font-size: 12px !important;

        font-weight: 600 !important;

        border: 1px solid #e5e7eb;

        background: #fafafa;

        transition: all .15s ease;
    }

    .dt-button:hover {
        background: #f3f4f6;
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

    /* =====================================================
TABLE LOOK
===================================================== */

    table.dataTable {

        border-collapse: separate !important;

        border-spacing: 0;

        background: white;

        font-size: 13px;
    }

    table.dataTable thead th {

        background: #fafafa;

        color: #6b7280;

        font-weight: 600;

        border-bottom: 1px solid #ececec;
    }

    table.dataTable tbody td {

        border-bottom: 1px solid #f1f1f1;

        padding: 10px;
    }

    /* hover */

    table.dataTable tbody tr:hover td {

        background: #f8fafc;

        cursor: pointer;
    }

    /* =====================================================
PAGINATION
===================================================== */

    .dataTables_paginate {

        display: flex;

        gap: 6px;

        margin-top: 10px;
    }

    .dataTables_paginate .paginate_button {

        border-radius: 8px;

        padding: 4px 10px;

        font-size: 12px;

        border: 1px solid transparent;

        color: #6b7280;

        transition: all .15s ease;
    }

    .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6;
    }

    .dataTables_paginate .paginate_button.current {

        background: #6366f1;

        color: white !important;
    }

    /* =====================================================
INFO TEXT
===================================================== */

    .dataTables_info {

        font-size: 12px;

        color: #6b7280;
    }

    /* =====================================================
DARK MODE
===================================================== */

    .dark table.dataTable {
        background: #111827;
    }

    .dark table.dataTable thead th {

        background: #1f2937;

        border-bottom: 1px solid #374151;

        color: #9ca3af;
    }

    .dark table.dataTable tbody td {

        border-bottom: 1px solid #1f2937;

        color: #e5e7eb;
    }

    .dark table.dataTable tbody tr:hover td {
        background: #1f2937;
    }

    /* controls */

    .dark .dataTables_filter input,
    .dark .dataTables_length select {

        background: #1f2937;

        border: 1px solid #374151;

        color: #e5e7eb;
    }

    .dark .dataTables_filter input:hover,
    .dark .dataTables_length select:hover {

        background: #2b3443;
    }
</style>
