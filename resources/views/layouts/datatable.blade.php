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

    select,
    textarea,
    input {
        width: 100%;
    }

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
</style>
