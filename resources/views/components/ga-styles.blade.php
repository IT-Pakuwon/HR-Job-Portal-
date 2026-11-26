{{-- Shared look for the GA Support pages: server-rendered table buttons/badges (.ga-table) and calendars (.fc-skin). --}}
<style>
    /* ---- row buttons and status badges the controllers render ---- */
    .ga-table table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; background: transparent !important; }
    .ga-table table.dataTable tbody tr:hover td { background: #f8fafc !important; }
    .ga-table table.dataTable thead th { border-bottom: 1px solid #f1f5f9; }
    .ga-table table.dataTable tbody td button:is(.bg-blue-100, .bg-violet-100, .bg-indigo-100) {
        display: inline-flex; align-items: center; justify-content: center; height: 2rem; padding: 0 .75rem; border-radius: .375rem;
        background: #eff6ff; border: 1px solid #dbeafe; color: #1d4ed8; font-weight: 600; transition: all .2s ease;
    }
    .ga-table table.dataTable tbody td button:is(.bg-blue-100, .bg-violet-100, .bg-indigo-100):hover {
        background: var(--color-blue-600); border-color: var(--color-blue-600); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 10px -3px rgba(37, 99, 235, .45);
    }
    .ga-table table.dataTable tbody td span.rounded-full:is(.bg-emerald-100, .bg-green-100, .bg-red-100) {
        display: inline-flex; align-items: center; gap: .5rem; padding: .25rem .75rem; font-size: .75rem; font-weight: 600; white-space: nowrap;
    }
    .ga-table table.dataTable tbody td span.rounded-full:is(.bg-emerald-100, .bg-green-100, .bg-red-100)::before { content: ""; width: .375rem; height: .375rem; border-radius: 9999px; background: currentColor; }
    .ga-table table.dataTable tbody td span.rounded-full:is(.bg-emerald-100, .bg-green-100) { background: #f0fdf4; color: #15803d; box-shadow: inset 0 0 0 1px #bbf7d0; }
    .ga-table table.dataTable tbody td span.rounded-full.bg-red-100 { background: #fef2f2; color: #b91c1c; box-shadow: inset 0 0 0 1px #fecaca; }
    .dark .ga-table table.dataTable tbody td { border-color: rgba(255, 255, 255, .06); color: #e5e7eb; }
    .dark .ga-table table.dataTable tbody tr:hover td { background: rgba(255, 255, 255, .03) !important; }
    .dark .ga-table table.dataTable thead th { border-color: rgba(255, 255, 255, .06); }

    /* ---- popup header icons (include after x-it-modal-skin) ---- */
    #schedule-show { --itm-icon: "\f271"; }
    #event-show { --itm-icon: "\f133"; }
    #viewMeetingModal { --itm-icon: "\f03d"; }
    #createRoomModal, #editRoomModal { --itm-icon: "\f52b"; }
    #createAccessoriesModal, #editAccessoriesModal { --itm-icon: "\f1e6"; }
    #roomAccessModal { --itm-icon: "\f023"; }
    #createDateBlockModal, #editDateBlockModal { --itm-icon: "\f017"; }
    #createBookingModal, #editBookingModal, #createVehicleModal, #editVehicleModal { --itm-icon: "\f1b9"; }
    #createDriverModal, #editDriverModal, #noKartuModal { --itm-icon: "\f2c2"; }
    #createCategoryModal, #editCategoryModal { --itm-icon: "\f02b"; }
    #createVoucherModal, #editVoucherTaxiModal { --itm-icon: "\f1ba"; }
    #viewBookingModal, #viewVoucherModal, #showModal { --itm-icon: "\f15c"; }
    #gaProcessModal { --itm-icon: "\f1b9"; }
    #processVoucherModal { --itm-icon: "\f53a"; }
    #companyDepartmentModal { --itm-icon: "\f1ad"; }
    #createModal { --itm-icon: "\f1b9"; }
    #editModal { --itm-icon: "\f304"; }
    #importModal { --itm-icon: "\f093"; }

    /* ---- request queue filter chips (Booking Car / Voucher Taxi) ---- */
    /* !important because layouts/status paints .active-filter with !important */
    #bookingListPanel .booking-filter.active-filter, #voucherListPanel .voucher-filter.active-filter { background: var(--color-blue-600) !important; border-color: var(--color-blue-600); color: #fff !important; }

    /* ---- calendars ---- */
    .fc-skin .fc .fc-toolbar-title { font-size: 1.125rem; font-weight: 700; color: #1f2937; }
    .fc-skin .fc .fc-button-primary {
        background: #fff; border: 1px solid #d1d5db; color: #374151; font-size: .8125rem; font-weight: 600;
        text-transform: capitalize; box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
    }
    .fc-skin .fc .fc-button-primary:not(:disabled):hover { background: #f3f4f6; border-color: #d1d5db; color: #111827; }
    .fc-skin .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc-skin .fc .fc-button-primary:not(:disabled).fc-button-active:hover { background: var(--color-blue-600); border-color: var(--color-blue-600); color: #fff; }
    .fc-skin .fc .fc-button-primary:focus, .fc-skin .fc .fc-button-primary:not(:disabled):active:focus { box-shadow: 0 0 0 3px rgba(59, 130, 246, .2); }
    .fc-skin .fc .fc-button-primary:disabled { background: #f9fafb; border-color: #e5e7eb; color: #9ca3af; }
    .fc-skin .fc .fc-col-header-cell { background: #f9fafb; }
    .fc-skin .fc .fc-col-header-cell-cushion { padding: .5rem; font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; }
    .fc-skin .fc .fc-datagrid-cell-cushion { font-size: .8125rem; color: #374151; }
    .fc-skin .fc .fc-day-today:not(.fc-col-header-cell) { background: rgba(59, 130, 246, .07) !important; }
    .fc-skin .fc .fc-event { border-radius: .375rem; cursor: pointer; }
    @media (max-width: 640px) { .fc-skin .fc .fc-col-header-cell-cushion { padding: .25rem 0; font-size: 10px; letter-spacing: 0; text-transform: none; } }
    .dark .fc-skin .fc .fc-toolbar-title { color: #f3f4f6; }
    .dark .fc-skin .fc .fc-button-primary { background: #1f2937; border-color: #4b5563; color: #e5e7eb; }
    .dark .fc-skin .fc .fc-button-primary:not(:disabled):hover { background: #374151; color: #fff; }
    .dark .fc-skin .fc .fc-button-primary:not(:disabled).fc-button-active { background: var(--color-blue-600); border-color: var(--color-blue-600); color: #fff; }
    .dark .fc-skin .fc .fc-col-header-cell { background: rgba(255, 255, 255, .04); }
    .dark .fc-skin .fc .fc-datagrid-cell-cushion { color: #e5e7eb; }
</style>
