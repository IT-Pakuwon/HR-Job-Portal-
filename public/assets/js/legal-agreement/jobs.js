// assets/js/legal-agreement/jobs.js
// Agreement follow-up "Jobs" list: contracts from the IFCA staging table, one row
// per contract/lot. Depends on agreement.js being loaded first (shared helpers:
// openModal/closeModal, showLoading/showSuccess/showError, the create-modal
// wiring including its submit handler, and loadPicOptions/resetCreateForm).

const Jobs = {
    filters: {
        cpny_id: '',
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

function initJobsTable() {
    if (!$.fn.DataTable || !$('#jobsTable').length) return;

    window.jobsTable = $('#jobsTable').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 dark:border-white/[0.06]"l>rt<"flex flex-wrap items-center justify-between gap-3 px-4 py-3"ip>',
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
        ],
    });
}

function readJobsFilters() {
    Jobs.filters.cpny_id = $('#jobs_cpny_filter').val();
    Jobs.filters.property_cd = $('#jobs_property_type').val();
}

function updateJobsExportLink() {
    const params = new URLSearchParams();

    Object.keys(Jobs.filters).forEach((key) => {
        if (Jobs.filters[key]) {
            params.set(key, Jobs.filters[key]);
        }
    });

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
        $('#jobs_cpny_filter, #jobs_property_type').val('').trigger('change');

        readJobsFilters();
        updateJobsExportLink();
        window.jobsTable.ajax.reload();
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

$(function () {
    initJobsTable();
    initJobsFilters();
    initCreateFromJob();

    if ($.fn.select2) {
        $('#jobs_cpny_filter, #jobs_property_type').select2({ width: '100%' });
    }
});
