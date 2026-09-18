// assets/js/legal-agreement/jobs.js
// Agreement follow-up "Jobs" list: contracts from the IFCA staging table, one row
// per contract/lot. Depends on agreement.js being loaded first (shared helpers:
// openModal/closeModal, showLoading/showSuccess/showError, the create-modal
// wiring including its submit handler, and loadPicOptions/resetCreateForm).

const Jobs = {
    filters: {
        cpny_id: '',
        tenant_no: '',
        trade_name: '',
        property_cd: '',
    },
};

const JOB_STATUS_LABELS = { A: 'Pending', C: 'Completed', X: 'Cancelled' };

function jobStatusBadgeClass(status) {
    switch (status) {
        case 'A': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300';
        case 'C': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
        case 'X': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
}

function renderJobStatusBadge(status) {
    return `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${jobStatusBadgeClass(status)}">${JOB_STATUS_LABELS[status] || status}</span>`;
}

function jobStatusSelectClass(status) {
    switch (status) {
        case 'A': return 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100 focus:ring-blue-300 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800/60 dark:hover:bg-blue-900/30';
        case 'C': return 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100 focus:ring-green-300 dark:bg-green-900/20 dark:text-green-300 dark:border-green-800/60 dark:hover:bg-green-900/30';
        case 'X': return 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100 focus:ring-red-300 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800/60 dark:hover:bg-red-900/30';
        default: return 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100 focus:ring-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700';
    }
}

const JOB_STATUS_SELECT_CLASS_LIST = [...Object.keys(JOB_STATUS_LABELS), 'default']
    .map((key) => jobStatusSelectClass(key))
    .join(' ');

const PROPERTY_CD_LABELS = {
    OFF: 'Office',
    MALL: 'Mall',
    APT: 'Apartment',
    HOTEL: 'Hotel',
};

function propertyCdBadgeClass(code) {
    switch ((code || '').toUpperCase()) {
        case 'OFF': return 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300';
        case 'MALL': return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300';
        case 'APT': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
        case 'HOTEL': return 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300';
        default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
}

function renderPropertyCdBadge(code) {
    if (!code) return '-';

    const label = PROPERTY_CD_LABELS[code.toUpperCase()] || code;

    return `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${propertyCdBadgeClass(code)}">${label}</span>`;
}

function renderJobStatusAction(row) {
    // Only offer statuses other than the row's current one - changing a
    // status to itself isn't a real action (e.g. Pending can't "change to" Pending).
    const options = Object.keys(JOB_STATUS_LABELS)
        .filter((key) => key !== row.status)
        .map((key) => `<option value="${key}">${JOB_STATUS_LABELS[key]}</option>`)
        .join('');

    return `
        <div class="relative inline-block">
            <select class="job-status-select cursor-pointer rounded-full border pl-3 pr-7 py-1.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 dark:focus:ring-offset-slate-900 ${jobStatusSelectClass('default')}"
                style="-webkit-appearance:none;-moz-appearance:none;appearance:none;background-image:none;"
                data-cpny_id="${row.cpny_id ?? ''}"
                data-business_id="${row.business_id ?? ''}">
                <option value="" selected disabled>Change Status</option>
                ${options}
            </select>
            <i class="fa-solid fa-chevron-down pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-[9px] opacity-60"></i>
        </div>
    `;
}

function initJobsTable() {
    if (!$.fn.DataTable || !$('#jobsTable').length) return;

    window.jobsTable = $('#jobsTable').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 dark:border-white/[0.06]"lf>rt<"flex flex-wrap items-center justify-between gap-3 px-4 py-3"ip>',
        ajax: {
            url: Agreement.routes.jobsJson,
            data: function (d) {
                Object.assign(d, Jobs.filters);
            },
        },
        order: [],
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (row) => `
                    <button type="button" class="btn-create-from-job inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                        data-cpny_id="${row.cpny_id ?? ''}"
                        data-business_id="${row.business_id ?? ''}"
                        data-tenant_no="${row.tenant_no ?? ''}"
                        data-trade_name="${(row.trade_name ?? '').replace(/"/g, '&quot;')}"
                        data-business_name="${(row.name ?? '').replace(/"/g, '&quot;')}"
                        data-property_cd="${row.property_cd ?? ''}"
                        data-floor_id="${row.level_no ?? ''}"
                        data-unit_id="${row.lot_no ?? ''}"
                        data-business_address="${(row.mailing_addr ?? '').replace(/"/g, '&quot;')}"
                        data-pic_email_penyewa="${(row.email_addr || row.email_addr2 || '').replace(/"/g, '&quot;')}"
                        title="Create Agreement">
                        <i class="fa-solid fa-plus text-xs"></i>
                    </button>
                `,
            },
            { data: 'contract_no', render: (d) => d || '-' },
            { data: 'cpny_id', render: (d) => d || '-' },
            { data: 'tenant_no', render: (d) => d || '-' },
            { data: 'trade_name', render: (d) => d || '-' },
            { data: 'property_cd', render: (d) => renderPropertyCdBadge(d) },
            { data: 'status', render: (d) => renderJobStatusBadge(d) },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (row) => renderJobStatusAction(row),
            },
        ],
    });

    // Keep the Export link in sync with whatever's typed into DataTables' own
    // search box, since that search no longer has a dedicated filter field.
    $('#jobsTable').on('search.dt', updateJobsExportLink);
}

function readJobsFilters() {
    Jobs.filters.cpny_id = $('#jobs_cpny_filter').val();
    Jobs.filters.tenant_no = $('#jobs_tenant_no').val();
    Jobs.filters.trade_name = $('#jobs_trade_name').val();
    Jobs.filters.property_cd = $('#jobs_property_type').val();
}

function updateJobsExportLink() {
    const params = new URLSearchParams();

    Object.keys(Jobs.filters).forEach((key) => {
        if (Jobs.filters[key]) {
            params.set(key, Jobs.filters[key]);
        }
    });

    const liveSearch = window.jobsTable ? window.jobsTable.search() : '';

    if (liveSearch) {
        params.set('search', liveSearch);
    }

    const query = params.toString();

    $('#btnExportJobs').attr('href', Agreement.routes.jobsExport + (query ? `?${query}` : ''));
}

function initJobsFilters() {
    updateJobsExportLink();

    $('#btnApplyJobsFilter').on('click', function () {
        readJobsFilters();
        updateJobsExportLink();
        window.jobsTable.ajax.reload();
    });

    $('#btnResetJobsFilter').on('click', function () {
        $('#jobs_tenant_no, #jobs_trade_name').val('');
        $('#jobs_cpny_filter, #jobs_property_type').val('').trigger('change');
        window.jobsTable.search('');

        readJobsFilters();
        updateJobsExportLink();
        window.jobsTable.ajax.reload();
    });

    // Enter key in a filter input applies the filters too.
    $('#jobs_tenant_no, #jobs_trade_name').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#btnApplyJobsFilter').trigger('click');
        }
    });
}

function initCreateFromJob() {
    $(document).on('click', '.btn-create-from-job', function () {
        const $btn = $(this);

        resetCreateForm();

        const cpnyId = $btn.data('cpny_id');
        const propertyCd = $btn.data('property_cd');

        $('#create_cpny_id').val(cpnyId ? String(cpnyId) : '').trigger('change');
        lockCompanyField(true);
        $('#createAgreementForm [name="business_id"]').val($btn.data('business_id') ?? '');
        $('#createAgreementForm [name="tenant_no"]').val($btn.data('tenant_no') ?? '');
        $('#createAgreementForm [name="trade_name"]').val($btn.data('trade_name') ?? '');
        $('#createAgreementForm [name="business_name"]').val($btn.data('business_name') ?? '');
        $('#createAgreementForm [name="floor_id"]').val($btn.data('floor_id') ?? '');
        $('#createAgreementForm [name="unit_id"]').val($btn.data('unit_id') ?? '');
        $('#createAgreementForm [name="business_address"]').val($btn.data('business_address') ?? '');
        $('#createAgreementForm [name="pic_email_penyewa"]').val($btn.data('pic_email_penyewa') ?? '');

        $('#create_property_cd').val(propertyCd ? String(propertyCd) : '');
        toggleCreateTradeName(propertyCd);

        loadPicOptions('#create_pic_legal', { role_id: 'LEGALACCESS' });
        // PIC Leasing is reloaded automatically by the change-event handler
        // bound to #create_cpny_id, fired by .trigger('change') above.

        openModal('#createAgreementModal');
    });
}

function initJobStatusChange() {
    $(document).on('change', '.job-status-select', function () {
        const $select = $(this);
        const status = $select.val();
        const label = JOB_STATUS_LABELS[status] || status;

        Swal.fire({
            title: 'Change status?',
            text: `Change this job's status to "${label}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, change it',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            confirmButtonColor: '#2563eb',
        }).then((result) => {
            if (!result.isConfirmed) {
                // Nothing was actually changed yet — just drop the select back
                // to its "Change Status" placeholder.
                $select.val('');
                return;
            }

            // Optimistic recolor so the pill doesn't sit in its old color while
            // the request round-trips.
            $select
                .removeClass(JOB_STATUS_SELECT_CLASS_LIST)
                .addClass(jobStatusSelectClass(status));

            $.ajax({
                url: Agreement.routes.jobsUpdateStatus,
                type: 'POST',
                data: {
                    cpny_id: $select.data('cpny_id'),
                    business_id: $select.data('business_id'),
                    status,
                },
                beforeSend: showLoading,
                success(res) {
                    hideLoading();
                    showSuccess(res.message || 'Status updated.');
                    window.jobsTable.ajax.reload(null, false);
                    refreshCounts();
                },
                error(xhr) {
                    hideLoading();
                    handleAjaxError(xhr);
                    window.jobsTable.ajax.reload(null, false);
                },
            });
        });
    });
}

$(function () {
    initJobsTable();
    initJobsFilters();
    initCreateFromJob();
    initJobStatusChange();

    if ($.fn.select2) {
        $('#jobs_cpny_filter, #jobs_property_type').select2({ width: '100%' });
    }
});
