<style>
    /* GLOBAL / UTILITY */

    .no-pointer {
        pointer-events: none;
    }

    table.dataTable {
        width: 100% !important;
    }


    /* Utility class to remove borders when needed */
    .no-border {
        border: none !important;
    }

    /* Force grid containers to span full width */
    .grid {
        width: 100%;
    }

    /* Make all form inputs full width */
    select,
    textarea,
    input {
        width: 100%;
    }

    /* DATATABLES BASE LAYOUT */

    /* Force DataTables to use full available width */
    table.dataTable {
        width: 100% !important;
    }

    /* Ensure wrapper always fills container */
    .dataTables_wrapper {
        width: 100%;
    }

    /* Add side padding on small screens (mobile safety) */
    @media (max-width: 600px) {
        .dataTables_wrapper {
            padding: 0 10px;
        }
    }

    /* Allow table to expand naturally (important for Responsive) */
    #personnelsTable,
    #usersTable,
    #rolesTable,
    #accessRightsTable,
    #roleMenusTable,
    #applicationsTable,
    #menusTable,
    #screensTable,
    #companiesTable,
    #departmentTable,
    #tenantsTable,
    #topTable,
    #vendorsTable,
    #approvalTable,
    #autonbrTable,
    #categoryTable,
    #inventoriesTable,
    #locationsTable,
    #subLocationsTable,
    #topDetailTable,
    #applicantsTable,
    #budgetsTable,
    #tblMaster,
    #tblTrx,
    #imbudgetsTable,
    #sppbsTable,
    #sppjsTable {
        width: auto;
    }

    /* SEARCH / FILTER / LENGTH CONTROLS */

    /* Search bar container */
    #personnelsTable_filter,
    #usersTable_filter,
    #rolesTable_filter,
    #accessRightsTable_filter,
    #roleMenusTable_filter,
    #applicationsTable_filter,
    #menusTable_filter,
    #screensTable_filter,
    #companiesTable_filter,
    #departmentTable_filter,
    #tenantsTable_filter,
    #topTable_filter,
    #vendorsTable_filter,
    #approvalTable_filter,
    #autonbrTable_filter,
    #categoryTable_filter,
    #inventoriesTable_filter,
    #locationsTable_filter,
    #subLocationsTable_filter,
    #topDetailTable_filter,
    #applicantsTable_filter,
    #budgetsTable_filter,
    #tblMaster_filter,
    #tblTrx_filter,
    #imbudgetsTable_filter,
    #sppbsTable_filter,
    #sppjsTable_filter {
        margin-bottom: 20px;
        display: flex;
        justify-content: flex-start;
        align-items: center;
    }

    /* Small spacing for search label */
    #personnelsTable_filter label,
    #usersTable_filter label,
    #rolesTable_filter label,
    #accessRightsTable_filter label,
    #roleMenusTable_filter label,
    #applicationsTable_filter label,
    #menusTable_filter label,
    #screensTable_filter label,
    #companiesTable_filter label,
    #departmentTable_filter label,
    #tenantsTable_filter label,
    #topTable_filter label,
    #vendorsTable_filter label,
    #approvalTable_filter label,
    #autonbrTable_filter label,
    #categoryTable_filter label,
    #inventoriesTable_filter label,
    #locationsTable_filter label,
    #subLocationsTable_filter label,
    #topDetailTable_filter label,
    #applicantsTable_filter label,
    #budgetsTable_filter label,
    #tblMaster_filter label,
    #tblTrx_filter label,
    #imbudgetsTable_filter label,
    #sppbsTable_filter label,
    #sppjsTable_filter label {
        margin-right: 2px;
    }

    /* Search input styling */
    #personnelsTable_filter input,
    #usersTable_filter input,
    #rolesTable_filter input,
    #accessRightsTable_filter input,
    #roleMenusTable_filter input,
    #applicationsTable_filter input,
    #menusTable_filter input,
    #screensTable_filter input,
    #companiesTable_filter input,
    #departmentTable_filter input,
    #tenantsTable_filter input,
    #topTable_filter input,
    #vendorsTable_filter input,
    #approvalTable_filter input,
    #autonbrTable_filter input,
    #categoryTable_filter input,
    #inventoriesTable_filter input,
    #locationsTable_filter input,
    #subLocationsTable_filter input,
    #topDetailTable_filter input,
    #applicantsTable_filter input,
    #budgetsTable_filter input,
    #tblMaster_filter input,
    #tblTrx_filter input,
    #imbudgetsTable_filter input,
    #sppbsTable_filter input,
    #sppjsTable_filter input {
        width: auto;
        min-width: 50px;
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        background-color: #f9fafb;
    }

    /* Wrapper width fix */
    #personnelsTable_wrapper,
    #usersTable_wrapper,
    #rolesTable_wrapper,
    #accessRightsTable_wrapper,
    #roleMenusTable_wrapper,
    #applicationsTable_wrapper,
    #menusTable_wrapper,
    #screensTable_wrapper,
    #companiesTable_wrapper,
    #departmentTable_wrapper,
    #tenantsTable_wrapper,
    #topTable_wrapper,
    #vendorsTable_wrapper,
    #approvalTable_wrapper,
    #autonbrTable_wrapper,
    #categoryTable_wrapper,
    #inventoriesTable_wrapper,
    #locationsTable_wrapper,
    #subLocationsTable_wrapper,
    #topDetailTable_wrapper,
    #applicantsTable_wrapper,
    #budgetsTable_wrapper,
    #tblMaster_wrapper,
    #tblTrx_wrapper,
    #imbudgetsTable_wrapper,
    #sppbsTable_wrapper,
    #sppjsTable_wrapper {
        width: 100%;
    }

    /* "Show entries" container */
    #personnelsTable_length,
    #usersTable_length,
    #rolesTable_length,
    #accessRightsTable_length,
    #roleMenusTable_length,
    #applicationsTable_length,
    #menusTable_length,
    #screensTable_length,
    #companiesTable_length,
    #departmentTable_length,
    #tenantsTable_length,
    #topTable_length,
    #vendorsTable_length,
    #approvalTable_length,
    #autonbrTable_length,
    #categoryTable_length,
    #inventoriesTable_length,
    #locationsTable_length,
    #subLocationsTable_length,
    #topDetailTable_length,
    #applicantsTable_length,
    #budgetsTable_length,
    #tblMaster_length,
    #tblTrx_length,
    #imbudgetsTable_length,
    #sppbsTable_length,
    #sppjsTable_length {
        width: auto;
        display: flex;
        justify-content: flex-start;
    }

    /* "Show entries" select styling */
    #personnelsTable_length select,
    #usersTable_length select,
    #rolesTable_length select,
    #accessRightsTable_length select,
    #roleMenusTable_length select,
    #applicationsTable_length select,
    #menusTable_length select,
    #screensTable_length select,
    #companiesTable_length select,
    #departmentTable_length select,
    #tenantsTable_length select,
    #topTable_length select,
    #vendorsTable_length select,
    #approvalTable_length select,
    #autonbrTable_length select,
    #categoryTable_length select,
    #inventoriesTable_length select,
    #locationsTable_length select,
    #subLocationsTable_length select,
    #topDetailTable_length select,
    #applicantsTable_length select,
    #budgetsTable_length select,
    #tblMaster_length select,
    #tblTrx_length select,
    #imbudgetsTable_length select,
    #sppbsTable_length select,
    #sppjsTable_length select {
        width: auto;
        min-width: 80px;
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        background-color: #f9fafb;
    }

    /* Options spacing */
    #personnelsTable_length select option,
    #usersTable_length select option,
    #rolesTable_length select option,
    #accessRightsTable_length select option,
    #roleMenusTable_length select option,
    #applicationsTable_length select option,
    #menusTable_length select option,
    #screensTable_length select option,
    #companiesTable_length select option,
    #departmentTable_length select option,
    #tenantsTable_length select option,
    #topTable_length select option,
    #vendorsTable_length select option,
    #approvalTable_length select option,
    #autonbrTable_length select option,
    #categoryTable_length select option,
    #inventoriesTable_length select option,
    #locationsTable_length select option,
    #subLocationsTable_length select option,
    #topDetailTable_length select option,
    #applicantsTable_length select option,
    #budgetsTable_length select option,
    #tblMaster_length select option,
    #tblTrx_length select option,
    #imbudgetsTable select option,
    #sppbsTable select option,
    #sppjsTable select option {
        padding: 5px;
    }

    /* Info text spacing (Showing X to Y of Z entries) */
    #personnelsTable_info,
    #usersTable_info,
    #rolesTable_info,
    #accessRightsTable_info,
    #roleMenusTable_info,
    #applicationsTable_info,
    #menusTable_info,
    #screensTable_info,
    #companiesTable_info,
    #departmentTable_info,
    #tenantsTable_info,
    #topTable_info,
    #vendorsTable_info,
    #approvalTable_info,
    #autonbrTable_info,
    #categoryTable_info,
    #inventoriesTable_info,
    #locationsTable_info,
    #subLocationsTable_info,
    #topDetailTable_info,
    #applicantsTable_info,
    #budgetsTable_info,
    #tblMaster_info,
    #tblTrx_info,
    #imbudgetsTable_info,
    #sppbsTable_info,
    #sppjsTable_info {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    /* Pagination spacing */
    .dataTables_paginate {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    /* TABLE CELL & ROW STYLING */

    /* Prevent wrapping, show ellipsis */
    #personnelsTable td,
    #usersTable td,
    #rolesTable td,
    #accessRightsTable td,
    #roleMenusTable td,
    #applicationsTable td,
    #menusTable td,
    #screensTable td,
    #companiesTable td,
    #departmentTable td,
    #tenantsTable td,
    #topTable td,
    #vendorsTable td,
    #approvalTable td,
    #autonbrTable td,
    #categoryTable td,
    #inventoriesTable td,
    #locationsTable td,
    #subLocationsTable td,
    #topDetailTable td,
    #applicantsTable td,
    #budgetsTable td,
    #tblMaster td,
    #tblTrx td,
    #imbudgetsTable td,
    #sppbsTable td,
    #sppjsTable td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Cell padding and max width */
    #personnelsTable th,
    #personnelsTable td,
    #usersTable th,
    #usersTable td,
    #rolesTable th,
    #rolesTable td,
    #accessRightsTable th,
    #accessRightsTable td,
    #roleMenusTable th,
    #roleMenusTable td,
    #applicationsTable th,
    #applicationsTable td,
    #menusTable th,
    #menusTable td,
    #screensTable th,
    #screensTable td,
    #companiesTable th,
    #companiesTable td,
    #departmentTable th,
    #departmentTable td,
    #tenantsTable th,
    #tenantsTable td,
    #topTable th,
    #topTable td,
    #vendorsTable th,
    #vendorsTable td,
    #approvalTable th,
    #approvalTable td,
    #autonbrTable th,
    #autonbrTable td,
    #categoryTable th,
    #categoryTable td,
    #inventoriesTable th,
    #inventoriesTable td,
    #locationsTable th,
    #locationsTable td,
    #subLocationsTable th,
    #subLocationsTable td,
    #topDetailTable th,
    #topDetailTable td,
    #applicantsTable th,
    #applicantsTable td,
    #budgetsTable th,
    #budgetsTable td,
    #tblMaster th,
    #tblMaster td,
    #tblTrx th,
    #tblTrx td,
    #imbudgetsTable th,
    #imbudgetsTable td,
    #sppjsTable th,
    #sppjsTable td,
    #sppbsTable th,
    #sppbsTable td {
        padding: 10px;
        max-width: 200px;
        text-align: center;
        min-width: 32px;
    }

    /* Row padding & vertical spacing */
    #personnelsTable tbody tr td,
    #usersTable tbody tr td,
    #rolesTable tbody tr td,
    #accessRightsTable tbody tr td,
    #roleMenusTable tbody tr td,
    #applicationsTable tbody tr td,
    #menusTable tbody tr td,
    #companiesTable tbody tr td,
    #screensTable tbody tr td,
    #departmentTable tbody tr td,
    #tenantsTable tbody tr td,
    #topTable tbody tr td,
    #vendorsTable tbody tr td,
    #approvalTable tbody tr td,
    #autonbrTable tbody tr td,
    #categoryTable tbody tr td,
    #inventoriesTable tbody tr td,
    #locationsTable tbody tr td,
    #subLocationsTable tbody tr td,
    #topDetailTable tbody tr td,
    #applicantsTable tbody tr td,
    #budgetsTable tbody tr td,
    #tblMaster tbody tr td,
    #tblTrx tbody tr td,
    #imbudgetsTable tbody tr td,
    #sppbsTable tbody tr td,
    #sppjsTable tbody tr td {
        padding: 8px 8px;
        line-height: 2;
    }

    /* Smooth hover transition */
    #personnelsTable tbody tr,
    #usersTable tbody tr,
    #rolesTable tbody tr,
    #accessRightsTable tbody tr,
    #roleMenusTable tbody,
    #applicationsTable tbody tr,
    #menusTable tbody tr,
    #screensTable tbody tr,
    #companiesTable tbody tr,
    #departmentTable tbody tr,
    #tenantsTable tbody tr,
    #topTable tbody tr,
    #vendorsTable tbody tr,
    #approvalTable tbody tr,
    #autonbrTable tbody tr,
    #categoryTable tbody tr,
    #inventoriesTable tbody tr,
    #locationsTable tbody tr,
    #subLocationsTable tbody tr,
    #topDetailTable tbody tr,
    #applicantsTable tbody tr,
    #budgetsTable tbody tr,
    #tblMaster tbody tr,
    #tblTrx tbody tr,
    #imbudgetsTable tbody tr,
    #sppbsTable tbody tr,
    #sppjsTable tbody tr {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Row hover effect */
    #personnelsTable tbody tr:hover,
    #usersTable tbody tr:hover,
    #rolesTable tbody tr:hover,
    #accessRightsTable tbody tr:hover,
    #roleMenusTable tbody tr:hover,
    #applicationsTable tbody tr:hover,
    #menusTable tbody tr:hover,
    #screensTable tbody tr:hover,
    #companiesTable tbody tr:hover,
    #departmentTable tbody tr:hover,
    #tenantsTable tbody tr:hover,
    #topTable tbody tr:hover,
    #vendorsTable tbody tr:hover,
    #approvalTable tbody tr:hover,
    #autonbrTable tbody tr:hover,
    #categoryTable tbody tr:hover,
    #inventoriesTable tbody tr:hover,
    #locationsTable tbody tr:hover,
    #subLocationsTable tbody tr:hover,
    #topDetailTable tbody tr:hover,
    #applicantsTable tbody tr:hover,
    #budgetsTable tbody tr:hover,
    #tblMaster tbody tr:hover,
    #tblTrx tbody tr:hover,
    #imbudgetsTable tbody tr:hover,
    #sppbsTable tbody tr:hover,
    #sppjsTable tbody tr:hover {
        background-color: #8f8f8f11;
        cursor: pointer;
    }

    /* COLUMN WIDTH OVERRIDES */

    /* DocID column */
    #personnelsTable th:nth-child(1),
    #personnelsTable td:nth-child(1),
    #usersTable th:nth-child(1),
    #usersTable td:nth-child(1),
    #rolesTable th:nth-child(1),
    #rolesTable td:nth-child(1),
    #accessRightsTable th:nth-child(1),
    #accessRightsTable td:nth-child(1),
    #roleMenusTable th:nth-child(1),
    #roleMenusTable td:nth-child(1),
    #applicationsTable th:nth-child(1),
    #applicationsTable td:nth-child(1),
    #menusTable th:nth-child(1),
    #menusTable td:nth-child(1),
    #screensTable th:nth-child(1),
    #screensTable td:nth-child(1),
    #companiesTable th:nth-child(1),
    #companiesTable td:nth-child(1),
    #departmentTable th:nth-child(1),
    #departmentTable td:nth-child(1),
    #tenantsTable th:nth-child(1),
    #tenantsTable td:nth-child(1),
    #topTable th:nth-child(1),
    #topTable td:nth-child(1),
    #vendorsTable th:nth-child(1),
    #vendorsTable td:nth-child(1),
    #approvalTable th:nth-child(1),
    #approvalTable td:nth-child(1),
    #autonbrTable th:nth-child(1),
    #autonbrTable td:nth-child(1),
    #categoryTable th:nth-child(1),
    #categoryTable td:nth-child(1),
    #inventoriesTable th:nth-child(1),
    #inventoriesTable td:nth-child(1),
    #locationsTable th:nth-child(1),
    #locationsTable td:nth-child(1),
    #subLocationsTable th:nth-child(1),
    #subLocationsTable td:nth-child(1),
    #topDetailTable th:nth-child(1),
    #topDetailTable td:nth-child(1),
    #applicantsTable th:nth-child(1),
    #applicantsTable td:nth-child(1),
    #jobpostingsTable th:nth-child(1),
    #jobpostingsTable td:nth-child(1),
    #budgetsTable th:nth-child(1),
    #budgetsTable td:nth-child(1),
    #tblMaster th:nth-child(1),
    #tblMaster td:nth-child(1),
    #tblTrx th:nth-child(1),
    #tblTrx td:nth-child(1),
    #imbudgetsTable th:nth-child(1),
    #imbudgetsTable td:nth-child(1),
    #sppbsTable th:nth-child(1),
    #sppbsTable td:nth-child(1),
    #sppjsTable td:nth-child(1),
    #sppjsTable th:nth-child(1) {
        width: 120px;
        text-align: center;
    }

    /* Department column */
    #personnelsTable th:nth-child(4),
    #personnelsTable td:nth-child(4),
    #usersTable th:nth-child(4),
    #usersTable td:nth-child(4),
    #rolesTable th:nth-child(4),
    #rolesTable td:nth-child(4),
    #accessRightsTable th:nth-child(4),
    #accessRightsTable td:nth-child(4),
    #roleMenusTable th:nth-child(4),
    #roleMenusTable td:nth-child(4),
    #applicationsTable th:nth-child(4),
    #applicationsTable td:nth-child(4),
    #menusTable th:nth-child(4),
    #menusTable td:nth-child(4),
    #screensTable th:nth-child(4),
    #screensTable td:nth-child(4),
    #companiesTable th:nth-child(4),
    #companiesTable td:nth-child(4),
    #departmentTable th:nth-child(4),
    #departmentTable td:nth-child(4),
    #tenantsTable th:nth-child(4),
    #tenantsTable td:nth-child(4),
    #topTable th :nth-child(4),
    #topTable td: :nth-child(4),
    #vendorsTable th:nth-child(4),
    #vendorsTable td:nth-child(4),
    #approvalTable th:nth-child(4),
    #approvalTable td:nth-child(4),
    #autonbrTable th:nth-child(4),
    #autonbrTable td:nth-child(4),
    #categoryTable th:nth-child(4),
    #categoryTable td:nth-child(4),
    #inventoriesTable th:nth-child(4),
    #inventoriesTable td:nth-child(4),
    #locationsTable th:nth-child(4),
    #locationsTable td:nth-child(4),
    #subLocationsTable th:nth-child(4),
    #subLocationsTable td:nth-child(4),
    #topDetailTable th:nth-child(4),
    #topDetailTable td:nth-child(4),
    #applicantsTable th:nth-child(4),
    #applicantsTable td:nth-child(4),
    #jobpostingsTable th:nth-child(4),
    #jobpostingsTable td:nth-child(4),
    #budgetsTable th:nth-child(4),
    #budgetsTable td:nth-child(4),
    #tblMaster th:nth-child(4),
    #tblMaster td:nth-child(4),
    #tblTrx th:nth-child(4),
    #tblTrx td:nth-child(4),
    #imbudgetsTable td:nth-child(4),
    #imbudgetsTable th:nth-child(4),
    #sppbsTable td:nth-child(4),
    #sppbsTable th:nth-child(4),
    #sppjsTable th:nth-child(4),
    #sppjsTable td:nth-child(4) {
        width: 120px;
        text-align: center;
    }

    /* lebar kolom */
    #approvalTable th.col-actions,
    #approvalTable td.col-actions,
    #categoryTable th.col-actions,
    #categoryTable td.col-actions {
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
    #approvalTable td.col-status,
    #categoryTable th.col-status,
    #categoryTable td.col-status,
    #categoryTable th.col-actions,
    #categoryTable td.col-actions {
        width: 80px;
        text-align: center;
    }

    #approvalTable th.col-start,
    #approvalTable td.col-start,
    #approvalTable th.col-end,
    #approvalTable td.col-end {
        width: 100px;
    }

    /* Name diperbesar */
    #approvalTable th.col-name,
    #approvalTable td.col-name {
        width: 320px;
    }



    /* ================================
   ROWGROUP (COMPANY GROUPING)
================================ */

    /* Hide rows when group is collapsed */
    #personnelsTable tbody tr.collapsed-group-row,
    #usersTable tbody tr.collapsed-group-row,
    #rolesTable tbody tr.collapsed-group-row,
    #accessRightsTable tbody tr.collapsed-group-row,
    #roleMenusTable tbody tr.collapsed-group-row,
    #applicationsTable tbody tr.collapsed-group-row,
    #menusTable tbody tr.collapsed-group-row,
    #screensTable tbody tr.collapsed-group-row,
    #companiesTable tbody tr.collapsed-group-row,
    #departmentTable tbody tr.collapsed-group-row,
    #tenantsTable tbody tr.collapsed-group-row,
    #topTable tbody tr.collapsed-group-row,
    #vendorsTable tbody tr.collapsed-group-row,
    #approvalTable tbody tr.collapsed-group-row,
    #autonbrTable tbody tr.collapsed-group-row,
    #inventoriesTable tbody tr.collapsed-group-row,
    #categoryTable tbody tr.collapsed-group-row,
    #locationsTable tbody tr.collapsed-group-row,
    #subLocationsTable tbody tr.collapsed-group-row,
    #topDetailTable tbody tr.collapsed-group-row,
    #applicantsTable tbody tr.collapsed-group-row,
    #budgetsTable tbody tr.collapsed-group-row,
    #tblMaster tbody tr.collapsed-group-row,
    #tblTrx tbody tr.collapsed-group-row,
    #imbudgetsTable tbody tr.collapsed-group-row,
    #sppbsTable tbody tr.collapsed-group-row,
    #sppjsTable tbody tr.collapsed-group-row {
        display: none;
    }

    /* Group header row styling */
    #personnelsTable tr.group-row,
    #usersTable tr.group-row,
    #rolesTable tr.group-row,
    #accessRightsTable tr.group-row,
    #roleMenusTable tr.group-row,
    #applicationsTable tr.group-row,
    #menusTable tr.group-row,
    #screensTable tr.group-row,
    #companiesTable tr.group-row,
    #departmentTable tr.group-row,
    #tenantsTable tr.group-row,
    #topTable tr.group-row,
    #vendorsTable tr.group-row,
    #approvalTable tr.group-row,
    #autonbrTable tr.group-row,
    #inventoriesTable tr.group-row,
    #categoryTable tr.group-row,
    #locationsTable tr.group-row,
    #subLocationsTable tr.group-row,
    #topDetailTable tr.group-row,
    #applicantsTable tr.group-row,
    #budgetsTable tr.group-row,
    #tblMaster tr.group-row,
    #tblTrx tr.group-row,
    #imbudgetsTable tr.group-row,
    #sppbsTable tr.group-row,
    #sppjsTable tr.group-row {
        background-color: #e6e6e6;
        font-weight: bold;
        cursor: pointer;
        user-select: none;
        color: #333;
    }

    /* Group row hover */
    #personnelsTable tr.group-row:hover,
    #usersTable tr.group-row:hover,
    #rolesTable tr.group-row:hover,
    #accessRightsTable tr.group-row:hover,
    #roleMenusTable tr.group-row:hover,
    #applicationsTable tr.group-row:hover,
    #menusTable tr.group-row:hover,
    #screensTable tr.group-row:hover,
    #companiesTable tr.group-row:hover,
    #departmentTable tr.group-row:hover,
    #tenantsTable tr.group-row:hover,
    #topTable tr.group-row:hover,
    #vendorsTable tr.group-row:hover,
    #approvalTable tr.group-row:hover,
    #autonbrTable tr.group-row:hover,
    #inventoriesTable tr.group-row:hover,
    #categoryTable tr.group-row:hover,
    #locationsTable tr.group-row:hover,
    #subLocationsTable tr.group-row:hover,
    #topDetailTable tr.group-row:hover,
    #applicantsTable tr.group-row:hover,
    #budgetsTable tr.group-row:hover,
    #tblMaster tr.group-row:hover,
    #tblTrx tr.group-row:hover,
    #imbudgetsTable tr.group-row:hover,
    #sppbsTable tr.group-row:hover,
    #sppjsTable tr.group-row:hover {
        background-color: #d4d4d4;
    }

    /* Group icon spacing */
    #personnelsTable tr.group-row .fas,
    #usersTable tr.group-row .fas,
    #rolesTable tr.group-row .fas,
    #accessRightsTable tr.group-row .fas,
    #roleMenusTable tr.group-row .fas,
    #applicationsTable tr.group-row .fas,
    #menusTable tr.group-row .fas,
    #screensTable tr.group-row .fas,
    #companiesTable tr.group-row .fas,
    #departmentTable tr.group-row .fas,
    #tenantsTable tr.group-row .fas,
    #topTable tr.group-row .fas,
    #vendorsTable tr.group-row .fas,
    #approvalTable tr.group-row .fas,
    #autonbrTable tr.group-row .fas,
    #inventoriesTable tr.group-row .fas,
    #categoryTable tr.group-row .fas,
    #locationsTable tr.group-row .fas,
    #subLocationsTable tr.group-row .fas,
    #topDetailTable tr.group-row .fas,
    #applicantsTable tr.group-row .fas,
    #budgetsTable tr.group-row .fas,
    #tblMaster tr.group-row .fas,
    #tblTrx tr.group-row .fas,
    #imbudgetsTable tr.group-row .fas,
    #sppbsTable tr.group-row .fas,
    #sppjsTable tr.group-row .fas {
        margin-right: 8px;
        width: 16px;
        text-align: center;
    }

    /* Group row padding & separator */
    #personnelsTable tr.group-row td,
    #usersTable tr.group-row td,
    #rolesTable tr.group-row td,
    #accessRightsTable tr.group-row td,
    #roleMenusTable tr.group-row td,
    #applicationsTable tr.group-row td,
    #menusTable tr.group-row td,
    #screensTable tr.group-row td,
    #companiesTable tr.group-row td,
    #departmentTable tr.group-row td,
    #tenantsTable tr.group-row td,
    #topTable tr.group-row td,
    #vendorsTable tr.group-row td,
    #approvalTable tr.group-row td,
    #autonbrTable tr.group-row td,
    #inventoriesTable tr.group-row td,
    #categoryTable tr.group-row td,
    #locationsTable tr.group-row td,
    #subLocationsTable tr.group-row td,
    #topDetailTable tr.group-row td,
    #applicantsTable tr.group-row td,
    #budgetsTable tr.group-row td,
    #tblMaster tr.group-row td,
    #tblTrx tr.group-row td,
    #imbudgetsTable tr.group-row td,
    #sppbsTable tr.group-row td,
    #sppjsTable tr.group-row td {
        padding: 10px !important;
        border-bottom: 1px solid #ddd;
    }

    /* Remove left border for colspan cell */
    #personnelsTable tr.group-row td:first-child,
    #usersTable tr.group-row td:first-child,
    #rolesTable tr.group-row td:first-child,
    #accessRightsTable tr.group-row td:first-child,
    #roleMenusTable tr.group-row td:first-child,
    #applicationsTable tr.group-row td:first-child,
    #menusTable tr.group-row td:first-child,
    #screensTable tr.group-row td:first-child,
    #companiesTable tr.group-row td:first-child,
    #departmentTable tr.group-row td:first-child,
    #tenantsTable tr.group-row td:first-child,
    #topTable tr.group-row td:first-child,
    #vendorsTable tr.group-row td:first-child,
    #approvalTable tr.group-row td:first-child,
    #autonbrTable tr.group-row td:first-child,
    #inventoriesTable tr.group-row td:first-child,
    #categoryTable tr.group-row td:first-child,
    #locationsTable tr.group-row td:first-child,
    #subLocationsTable tr.group-row td:first-child,
    #topDetailTable tr.group-row td:first-child,
    #applicantsTable tr.group-row td:first-child,
    #budgetsTable tr.group-row td:first-child,
    #tblMaster tr.group-row td:first-child,
    #tblTrx tr.group-row td:first-child,
    #imbudgetsTable tr.group-row td:first-child,
    #sppbsTable tr.group-row td:first-child,
    #sppjsTable tr.group-row td:first-child {
        border-left: none;
    }

    #applicantsTable thead tr.filters th {
        padding: 6px;
    }

    #applicantsTable thead tr.filters input,
    #applicantsTable thead tr.filters select {
        width: 100%;
        box-sizing: border-box;
        font-size: 12px;
    }

    /* Force header rows to align perfectly */
    #applicantsTable thead tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    /* CUSTOM SWITCH (GLOBAL COMPONENT) */

    /* Switch container */
    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 22px;
    }

    /* Hide default checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* Switch track */
    .slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    /* Switch knob */
    .slider:before {
        content: "";
        position: absolute;
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    /* Active state */
    input:checked+.slider {
        background-color: #4CAF50;
    }

    /* Move knob when checked */
    input:checked+.slider:before {
        transform: translateX(18px);
    }


    /* DATATABLES EXPORT BUTTONS */

    /* Export buttons container */
    .dt-buttons {
        display: flex;
        gap: 8px;
        margin-right: 12px;
    }

    /* Base export button style */
    .dt-button {
        display: inline-flex !important;
        align-items: center;
        gap: 6px;
        padding: 6px 12px !important;
        border-radius: 9999px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        transition: all .2s ease-in-out;
    }

    /* Excel button */
    .dt-button.buttons-excel {
        background-color: #dcfce7 !important;
        color: #166534 !important;
        border-color: #86efac !important;
    }

    /* Excel hover */
    .dt-button.buttons-excel:hover {
        background-color: #bbf7d0 !important;
    }

    /* CSV button */
    .dt-button.buttons-csv {
        background-color: #e0f2fe !important;
        color: #075985 !important;
        border-color: #7dd3fc !important;
    }

    /* CSV hover */
    .dt-button.buttons-csv:hover {
        background-color: #bae6fd !important;
    }

    /* Remove default focus styles */
    .dt-button:focus,
    .dt-button:active {
        outline: none !important;
        box-shadow: none !important;
    }

    /* DATATABLES TOOLBAR ALIGNMENT */

    /* Toolbar layout */
    .dt-toolbar {
        display: flex !important;
        justify-content: flex-start !important;
        align-items: center !important;
        gap: 12px;
        margin-bottom: 10px;
    }

    /* Reset default margins */
    .dataTables_length,
    .dt-buttons,
    .dataTables_filter {
        margin: 0 !important;
        display: flex;
        align-items: center;
    }

    /* Push search box to the right */
    .dataTables_filter {
        margin-left: auto !important;
    }

    /* RESPONSIVE TOGGLE (dtr-control) */

    /* Align toggle icon and content nicely */
    /* Center Responsive child row content */
    table.dataTable>tbody>tr.child ul.dtr-details {
        width: 100%;
        margin: 0 auto;
    }

    table.dataTable>tbody>tr.child ul.dtr-details>li {
        display: flex;
        justify-content: flex-start;
        /* left align */
        gap: 12px;
        /* horizontal gap */
        text-align: left;
    }

    .menu-tree ul {
        margin-left: 0.75rem;
        padding-left: 0.75rem;
        border-left: 1px solid rgba(156, 163, 175, 0.6);
        /* gray-400 */
    }

    .menu-tree li {
        margin: 2px 0;
    }

    .tree-toggle {
        cursor: pointer;
        font-size: 0.75rem;
        line-height: 1;
        padding: 0 4px;
    }

    /* Biar select2 multiple di modal approval tidak nabrak kolom lain */

    /* container-nya jangan fixed-height */
    .select2-container--default .select2-selection--multiple {
        min-height: 34px;
        height: auto !important;
        overflow: visible !important;
    }

    /* render chip dalam bentuk flex yang bisa wrap ke bawah */
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex !important;
        flex-wrap: wrap !important;
        padding: 2px 4px !important;
    }

    /* hilangkan float bawaan select2 supaya ikut flex */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        float: none !important;
        margin: 1px 4px 1px 0 !important;
    }

    /* cursor & tinggi input di dalam select2 */
    .select2-container--default .select2-selection--multiple .select2-search__field {
        margin-top: 2px !important;
        padding: 0 !important;
    }

    /* Col Input Filter */
    /* Header row filter */
    #applicantsTable thead tr.filters th {
        padding: 6px 8px;
    }

    #applicantsTable thead .col-filter {
        width: 100%;
        box-sizing: border-box;
    }

    #applicantsTable thead .input-filter {
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 12px;
    }

    #applicantsTable thead .select-filter {
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 12px;
        background: white;
    }

    .dark #applicantsTable thead .input-filter,
    .dark #applicantsTable thead .select-filter {
        background: #374151;
        color: #e5e7eb;
        border-color: #4b5563;
    }

    #tlList::-webkit-scrollbar {
        display: none;
    }

    #tlList {
        scrollbar-width: none;
    }
</style>
