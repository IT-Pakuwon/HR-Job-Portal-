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

// Staging job status: A = Pending (no agreement yet), P = On Progress (its PSM / OLA is
// Active), C = Completed (that PSM / OLA is completed), X = Cancelled.
// Agreement FU's Jobs are PSM / OLA agreements, so their status is the agreement's own step.
const JOB_STATUS_LABELS = { A: 'Pending', P: 'On Progress', C: 'Completed', X: 'Cancelled', ACTIVE: 'Active', COMPLETED: 'Completed' };

// Rows of the table by eid, so the create button can prefill from the whole row.
Jobs.rows = {};

function jobStatusBadgeClass(status) {
    switch (status) {
        case 'ACTIVE': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
        case 'COMPLETED': return 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
        case 'A': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300';
        case 'P': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
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

const JOB_TYPE_LABELS = { PSM: 'PSM', OLA: 'OLA', ADDENDUM: 'Addendum', OTHERS: 'Others' };

function renderJobTypeBadge(type) {
    if (!type) return '-';

    const cls = type === 'ADDENDUM'
        ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
        : type === 'OTHERS'
            ? 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
            : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300';

    return `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${cls}">${JOB_TYPE_LABELS[type] || type}</span>`;
}

function initJobsTable() {
    if (!$.fn.DataTable || !$('#jobsTable').length) return;

    // Only Agreement FU's table has the Type column (New Agreement reuses this table without it).
    const hasType = !!$('#jobsTable').data('fu');

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
            // New Agreement reuses this table but has its own create flow, so it
            // sets window.jobsActionClass to get a differently-bound button.
            {
                data: null,
                orderable: false,
                searchable: false,
                render: (row) => {
                    if (row.eid) Jobs.rows[row.eid] = row;

                    return `
                    <button type="button" class="${window.jobsActionClass || 'btn-create-from-job'} inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                        data-eid="${row.eid ?? ''}"
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
                `;
                },
            },
            { data: 'contract_no', render: (d) => d || '-' },
            // Agreement FU lists PSM / OLA / Addendum / Others together; New Agreement's rows have no agreement_type.
            ...(hasType ? [{ data: 'agreement_type', render: (d) => renderJobTypeBadge(d) }] : []),
            { data: 'cpny_id', render: (d) => d || '-' },
            { data: 'tenant_no', render: (d) => d || '-' },
            { data: 'trade_name', render: (d) => d || '-' },
            { data: 'property_cd', render: (d) => renderPropertyCdBadge(d) },
            { data: 'status', render: (d) => renderJobStatusBadge(d) },
            // Cancel (every type) / Complete (Others) with notes, from a dropdown (last column, FU only).
            ...(hasType ? [{
                data: null,
                orderable: false,
                searchable: false,
                render: (row) => `<button type="button" class="btn-job-actions inline-flex h-8 items-center gap-1 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white hover:bg-blue-700"
                        data-eid="${row.eid ?? ''}" data-type="${row.agreement_type ?? ''}">
                        Action <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>`,
            }] : []),
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

// Select the PSM / OLA's saved PICs. They may not be in the first page of
// search results, so they are added as options before being selected.
function presetPicOptions(selector, params, force, picked) {
    return loadPicOptions(selector, params, force).always(function () {
        const $select = $(selector);

        (picked || []).forEach((p) => {
            if (!$select.find('option').filter((_, el) => el.value === p.id).length) {
                $select.append(new Option(p.text, p.id));
            }
        });

        $select.val((picked || []).map((p) => p.id)).trigger('change');
    });
}

// Start the follow-up for a PSM / OLA agreement: its data prefills the form and
// saving converts that agreement (no new one is made).
function initCreateFromJob() {
    $(document).on('click', '.btn-create-from-job', function () {
        const $btn = $(this);
        const job = Jobs.rows[$btn.data('eid')] || {};

        resetCreateForm();

        const cpnyId = $btn.data('cpny_id');
        const propertyCd = $btn.data('property_cd');

        // 'change.select2' refreshes the widget only; the delegated 'change'
        // handler would re-fetch PIC Leasing, which is loaded below with the saved PICs.
        $('#create_cpny_id').val(cpnyId ? String(cpnyId) : '').trigger('change.select2');
        lockCompanyField(true);
        $('#createAgreementForm [name="source_eid"]').val($btn.data('eid') ?? '');
        $('#createAgreementForm [name="pic_penyewa"]').val(job.pic_penyewa ?? '');
        $('#createAgreementForm [name="pic_phonenumber_penyewa"]').val(job.pic_phonenumber_penyewa ?? '');
        $('#createAgreementForm [name="no_psm_or_addendum"]').val(job.contract_no ?? '');
        $('#createAgreementForm [name="business_id"]').val($btn.data('business_id') ?? '');
        $('#createAgreementForm [name="tenant_no"]').val($btn.data('tenant_no') ?? '');
        $('#createAgreementForm [name="trade_name"]').val($btn.data('trade_name') ?? '');
        $('#createAgreementForm [name="business_name"]').val($btn.data('business_name') ?? '');
        $('#createAgreementForm [name="floor_id"]').val($btn.data('floor_id') ?? '');
        $('#createAgreementForm [name="unit_id"]').val($btn.data('unit_id') ?? '');
        $('#createAgreementForm [name="business_address"]').val($btn.data('business_address') ?? '');
        $('#createAgreementForm [name="pic_email_penyewa"]').val($btn.data('pic_email_penyewa') ?? '');

        // Unit follows the PSM / OLA (Business ID and Tenant No are hidden inputs, already fixed): locked
        // by CSS + readonly so it still posts; the server ignores it too.
        $('#create_unit_id').prop('readonly', true).addClass('agr-locked').attr('tabindex', -1);

        // Fixed by the PSM / OLA (Mall = PSM, Office = OLA); the server ignores it too.
        $('#create_property_cd').val(propertyCd ? String(propertyCd) : '').prop('disabled', true);
        toggleCreateTradeName(propertyCd);

        presetPicOptions('#create_pic_legal', { role_id: 'LEGALACCESS' }, false, job.pic_legal_options);
        presetPicOptions(
            '#create_pic_leasing',
            { role_id: 'LEASINGACCESS', ...(cpnyId ? { cpny_id: cpnyId } : {}) },
            true,
            job.pic_leasing_options
        );

        openModal('#createAgreementModal');
    });
}

// Ask for notes, then post them to a Job-tab action (cancel / complete) and refresh the list.
function jobActionWithNotes({ eid, url, title, text, placeholder, confirmText, confirmColor, done }) {
    Swal.fire({
        title,
        text,
        input: 'textarea',
        inputLabel: 'Notes',
        inputPlaceholder: placeholder,
        inputAttributes: { maxlength: 2000 },
        showCancelButton: true,
        confirmButtonText: confirmText,
        confirmButtonColor: confirmColor,
        reverseButtons: true,
        inputValidator: (value) => (value && value.trim() ? undefined : 'Notes are required.'),
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: url.replace(':eid', eid),
            type: 'POST',
            data: { response_descr: result.value.trim() },
            beforeSend: () => showLoading(),
            success(res) {
                hideLoading();
                showSuccess(res.message || done);
                window.jobsTable.ajax.reload(null, false);
                refreshCounts();
            },
            error(xhr) {
                hideLoading();
                handleAjaxError(xhr);
            },
        });
    });
}

const JOB_ACTIONS = {
    cancel: (eid) => jobActionWithNotes({
        eid,
        url: Agreement.routes.jobCancel,
        title: 'Cancel Agreement',
        text: 'Cancel this agreement because it needs to be changed. It leaves this list; add a note to continue.',
        placeholder: 'Why is it cancelled?',
        confirmText: 'Cancel Agreement',
        confirmColor: '#dc2626',
        done: 'Agreement cancelled.',
    }),
    complete: (eid) => jobActionWithNotes({
        eid,
        url: Agreement.routes.jobComplete,
        title: 'Complete Agreement',
        text: 'Complete this Others agreement without a follow-up. Add a note to complete it.',
        placeholder: 'Why is this completed?',
        confirmText: 'Complete',
        confirmColor: '#16a34a',
        done: 'Agreement completed.',
    }),
};

// Action dropdown of a Job row. The menu hangs off <body> (fixed), so the table's horizontal scroll box can't clip it.
function closeJobActionMenu() {
    $('#jobActionMenu').remove();
}

function initJobActions() {
    $(document).on('click', '.btn-job-actions', function (e) {
        e.stopPropagation();

        const $btn = $(this);
        const reopen = $btn.data('open') !== true;

        closeJobActionMenu();
        $('.btn-job-actions').data('open', false);

        if (!reopen) return;

        const isOthers = $btn.data('type') === 'OTHERS';
        const item = (action, icon, label, color) => `<button type="button" class="job-action-item" data-action="${action}" data-eid="${$btn.data('eid')}"
            style="display:flex;width:100%;align-items:center;gap:8px;padding:8px 14px;font-size:13px;text-align:left;background:none;border:0;cursor:pointer;color:${color};">
            <i class="fa-solid ${icon}" style="width:14px;"></i> ${label}</button>`;

        const rect = this.getBoundingClientRect();
        const $menu = $(`<div id="jobActionMenu" class="rounded-lg border border-slate-200 bg-white shadow-lg dark:border-white/[0.08] dark:bg-slate-900"
            style="position:fixed;z-index:9999;min-width:190px;padding:4px 0;">
            ${isOthers ? item('complete', 'fa-check', 'Complete with notes', '#16a34a') : ''}
            ${item('cancel', 'fa-ban', 'Cancel with notes', '#dc2626')}
        </div>`).appendTo('body');

        // Open downwards, or upwards when there is no room below (the last rows of the table).
        const menuH = $menu.outerHeight();
        const top = rect.bottom + menuH + 8 > window.innerHeight ? rect.top - menuH - 4 : rect.bottom + 4;
        $menu.css({ top: Math.max(8, top), left: Math.max(8, Math.min(rect.right - $menu.outerWidth(), window.innerWidth - $menu.outerWidth() - 8)) });

        $btn.data('open', true);
    });

    $(document).on('click', '.job-action-item', function () {
        const { action, eid } = $(this).data();

        closeJobActionMenu();
        $('.btn-job-actions').data('open', false);
        JOB_ACTIONS[action]?.(eid);
    });

    // Anything else closes the menu (it is fixed, so it would not follow a scroll).
    $(document).on('click', () => {
        closeJobActionMenu();
        $('.btn-job-actions').data('open', false);
    });
    $(window).on('scroll resize', () => {
        closeJobActionMenu();
        $('.btn-job-actions').data('open', false);
    });
    document.addEventListener('scroll', closeJobActionMenu, true);
}

$(function () {
    initJobsTable();
    initJobsFilters();
    initCreateFromJob();
    initJobActions();

    if ($.fn.select2) {
        $('#jobs_cpny_filter, #jobs_property_type').select2({ width: '100%' });
    }
});
