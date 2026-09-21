// assets/js/legal-agreement/agreement.js
// Consolidated JS for the Legal Agreement module (list, create, detail, workflow actions, comments).

window.Agreement = window.Agreement || {};
Agreement.csrf = $('meta[name="csrf-token"]').attr('content');
Agreement.routes = window.agrRoutes || {};

Agreement.state = {
    actionAttachments: [],
    currentStatus: '',
    currentCpny: '',
    currentSearch: '',
    createStep: 1,
    actionSteps: [],
    actionStepIndex: 1,
};

Agreement.pushUrl = function (eid) {
    if (!eid || !Agreement.routes.show) return;
    const url = Agreement.routes.show.replace(':eid', eid);
    if (window.location.pathname === url) return;
    history.pushState({ eid }, '', url);
};

Agreement.clearUrl = function () {
    if (!Agreement.routes.index) return;
    if (window.location.pathname === Agreement.routes.index) return;
    history.pushState({}, '', Agreement.routes.index);
};

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': Agreement.csrf,
        'Accept': 'application/json',
    },
});

/* ----------------------------------------------------------------------
 | Generic Helpers
 * ---------------------------------------------------------------------- */

function showSuccess(message = 'Success') {
    Swal.fire({ icon: 'success', title: 'Success', text: message, timer: 1800, showConfirmButton: false });
}

function showError(message = 'Something went wrong') {
    Swal.fire({ icon: 'error', title: 'Error', text: message });
}

function showLoading(title = 'Loading...', text = 'Please wait') {
    Swal.fire({
        title, text, allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
}

function hideLoading() {
    Swal.close();
}

function handleAjaxError(xhr) {
    if (xhr.status === 401) {
        window.location.reload();
        return;
    }

    if (xhr.status === 422) {
        const errors = xhr.responseJSON?.errors || {};
        const firstField = Object.values(errors)[0];
        const firstMessage = Array.isArray(firstField)
            ? firstField[0]
            : (xhr.responseJSON?.message || 'Validation failed.');

        showError(firstMessage);
        return;
    }

    showError(xhr.responseJSON?.message || 'Something went wrong');
}

function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    const d = new Date(dateString);
    return `${formatDate(dateString)} ${d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false })}`;
}

function formatFileSize(bytes = 0) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

function stepBadgeClass(step) {
    switch ((step || '').toUpperCase()) {
        case 'ACTIVE': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
        case 'HOLD': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300';
        case 'ESCALATED': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
        case 'COMPLETED': return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
        default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
}

function renderStepBadge(step) {
    const label = (step || '').toUpperCase() === 'COMPLETED' ? 'Done' : (step ?? '-');
    return `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${stepBadgeClass(step)}">${label}</span>`;
}

const CYCLE_LABELS = {
    AWAL: 'Agreement Sent',
    REMINDER1: 'Reminder 1',
    REMINDER2: 'Reminder 2',
    ESCALATED: 'Escalated',
};

function cycleBadgeClass(cycle) {
    switch (cycle) {
        case 'AWAL': return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
        case 'REMINDER1': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
        case 'REMINDER2': return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300';
        case 'ESCALATED': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    }
}

function renderCycleBadge(info) {
    if (!info || !info.cycle) return '<span class="text-slate-400">-</span>';

    const label = CYCLE_LABELS[info.cycle] || info.cycle;

    return `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${cycleBadgeClass(info.cycle)}">${label}</span>`;
}

function renderDaysCell(info) {
    if (!info || info.days_elapsed === null || info.days_elapsed === undefined) {
        return '<span class="text-slate-400 text-xs">-</span>';
    }

    const { days_elapsed, days_threshold } = info;
    const overdue = days_threshold != null && days_elapsed >= days_threshold;
    const nearing = !overdue && days_threshold != null && days_elapsed >= days_threshold - 3;
    const cls = overdue
        ? 'text-red-600 dark:text-red-400'
        : nearing
            ? 'text-amber-600 dark:text-amber-400'
            : 'text-slate-600 dark:text-slate-300';
    const suffix = days_threshold != null ? ` of ${days_threshold}` : '';

    return `<span class="text-xs font-semibold ${cls}">Day ${days_elapsed}${suffix}</span>`;
}

const ROW_ACTION_LABELS = {
    hold: { label: 'Hold Agreement', icon: 'fa-solid fa-pause', class: 'text-amber-600 dark:text-amber-400' },
    activate: { label: 'Activate Agreement', icon: 'fa-solid fa-bolt', class: 'text-emerald-600 dark:text-emerald-400' },
    complete: { label: 'Complete Agreement', icon: 'fa-solid fa-flag-checkered', class: 'text-slate-600 dark:text-slate-300' },
};

function renderAgreementRowActions(row) {
    const can = row.actions || {};
    const eid = row.eid;

    const items = [{ action: 'view', label: 'View Detail', icon: 'fa-regular fa-eye', class: 'text-slate-700 dark:text-slate-200' }];

    ['hold', 'activate', 'complete'].forEach((key) => {
        if (can[`can_${key}`]) items.push({ action: key, ...ROW_ACTION_LABELS[key] });
    });

    const itemsHtml = items.map((item) => `
        <button type="button" class="agr-row-action-item flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium transition-all duration-200 hover:bg-slate-100 dark:hover:bg-white/[0.05] ${item.class}"
            data-action="${item.action}" data-eid="${eid}">
            <i class="${item.icon} w-4 text-center text-[13px]"></i>
            <span>${item.label}</span>
        </button>
    `).join('');

    return `
        <div class="relative inline-block text-left">
            <button type="button" class="agr-row-action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-all duration-200 hover:bg-slate-100 dark:border-white/[0.06] dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
            <div class="agr-row-action-menu fixed z-[99999] hidden w-[220px] overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl dark:border-white/[0.08] dark:bg-slate-800">
                ${itemsHtml}
            </div>
        </div>
    `;
}

function initRowActionDropdown() {
    $(document).on('click', '.agr-row-action-btn', function (e) {
        e.stopPropagation();

        const $btn = $(this);
        const $menu = $btn.siblings('.agr-row-action-menu');
        const isHidden = $menu.hasClass('hidden');

        $('.agr-row-action-menu').addClass('hidden');

        if (isHidden) {
            const rect = this.getBoundingClientRect();
            const menuWidth = 220;
            const left = Math.max(8, rect.right - menuWidth);
            const top = rect.bottom + 8;
            $menu.css({ top: `${top}px`, left: `${left}px` }).removeClass('hidden');
        }
    });

    $(document).on('click', function () {
        $('.agr-row-action-menu').addClass('hidden');
    });

    $(document).on('click', '.agr-row-action-menu', function (e) {
        e.stopPropagation();
    });

    $(document).on('click', '.agr-row-action-item', function () {
        $('.agr-row-action-menu').addClass('hidden');

        const action = $(this).data('action');
        const eid = $(this).data('eid');

        if (action === 'view') {
            openAgreementDetailModal(eid);
        } else {
            openActionModal(action, eid);
        }
    });
}

/* ----------------------------------------------------------------------
 | Modal Open/Close
 * ---------------------------------------------------------------------- */

let currentModal = null;
let modalAnimating = false;

function openModal(selector) {
    const modal = $(selector);
    if (!modal.length || modal.hasClass('flex') || modalAnimating) return;

    modalAnimating = true;
    currentModal = selector;
    modal.removeClass('hidden').addClass('flex');
    $('body').addClass('overflow-hidden');

    requestAnimationFrame(() => {
        modal.find('.modal-panel').removeClass('opacity-0 translate-y-4 scale-[0.98]').addClass('opacity-100 translate-y-0 scale-100');
        modal.find('.modal-backdrop').removeClass('opacity-0').addClass('opacity-100');
        setTimeout(() => { modalAnimating = false; }, 220);
    });
}

function closeModal(selector) {
    const modal = $(selector);
    if (!modal.length || modalAnimating) return;

    modalAnimating = true;
    if (currentModal === selector) currentModal = null;

    modal.find('.modal-backdrop').removeClass('opacity-100').addClass('opacity-0');
    modal.find('.modal-panel').removeClass('opacity-100 translate-y-0 scale-100').addClass('opacity-0 translate-y-6 scale-[0.98]');

    setTimeout(() => {
        modal.removeClass('flex').addClass('hidden');
        if ($('.agr-modal.flex').length === 0) $('body').removeClass('overflow-hidden');
        modalAnimating = false;
        if (selector === '#detailAgreementModal') Agreement.clearUrl();
    }, 220);
}

function initModal() {
    $(document).on('click.agrFormModalClose', '.btn-close-form-modal', function () {
        const $modal = $(this).closest('.agr-modal');
        const modalId = '#' + $modal.attr('id');

        Swal.fire({
            title: 'Close Form?',
            text: 'Unsaved changes will be lost.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Close',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            confirmButtonColor: '#dc2626',
        }).then((result) => {
            if (!result.isConfirmed) return;
            closeModal(modalId);
        });
    });

    $(document).on('click.agrModalClose', '.btn-close-modal', function () {
        const $modal = $(this).closest('.agr-modal');
        closeModal('#' + $modal.attr('id'));
    });

    $(document).on('click.agrModalBackdrop', '.modal-backdrop', function () {
        const $modal = $(this).closest('.agr-modal');
        if ($modal.data('form-modal')) return;
        $modal.find('.btn-close-modal').first().trigger('click');
    });

    $(document).on('keydown.agrModalEscape', function (e) {
        if (e.key !== 'Escape' || !currentModal || modalAnimating) return;
        if ($(currentModal).data('form-modal')) return;
        $(currentModal).find('.btn-close-modal').first().trigger('click');
    });
}

/* ----------------------------------------------------------------------
 | Counts / Status Filter
 * ---------------------------------------------------------------------- */

function refreshCounts() {
    if (!Agreement.routes.counts) return;

    $.get(Agreement.routes.counts, function (res) {
        Object.keys(res || {}).forEach((key) => {
            $(`[data-count="${key}"]`).text(res[key]);
        });
    });
}

function initViewSwitcher() {
    $(document).on('click', '.legal-nav-card', function (e) {
        e.preventDefault();

        const $card = $(this);
        const view = $card.data('view');

        if (view === 'jobs') {
            $('#agreementSection').addClass('hidden');
            $('#jobsSection').removeClass('hidden');

            if (window.jobsTable) {
                window.jobsTable.columns.adjust();
            }
        } else {
            $('#jobsSection').addClass('hidden');
            $('#agreementSection').removeClass('hidden');

            Agreement.state.currentStatus = $card.data('status') || '';
            reloadTable();

            if ($.fn.DataTable && $('#agreementTable').length) {
                $('#agreementTable').DataTable().columns.adjust();
            }
        }
    });
}

/* ----------------------------------------------------------------------
 | DataTable
 * ---------------------------------------------------------------------- */

function initDataTable() {
    if (!$.fn.DataTable || !$('#agreementTable').length) return;

    $('#agreementTable').DataTable({
        processing: true,
        serverSide: true,
        // No 'f' — the dedicated #agr_search input above the table already
        // covers search; DataTables' own default search box would just
        // duplicate it.
        dom: 'lrtip',
        ajax: {
            url: Agreement.routes.json,
            data: function (d) {
                d.status = Agreement.state.currentStatus;
                d.cpny_id = Agreement.state.currentCpny;
                d.search = Agreement.state.currentSearch;
            },
        },
        order: [],
        columns: [
            {
                data: null,
                name: 'agreement_id',
                render: (row) => `
                    <button type="button" class="btn-view-agreement inline-flex w-[150px] items-center justify-center rounded-lg bg-slate-800 px-3 py-1.5 text-sm font-semibold text-white transition-all duration-200 hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white" data-eid="${row.eid}">
                        ${row.agreement_id ?? '-'}
                    </button>
                `,
            },
            { data: 'agreement_date', name: 'agreement_date', render: (d) => formatDate(d) },
            { data: 'cpny_name', name: 'cpny_name', orderable: false },
            {
                data: null,
                render: (row) => `<div class="font-medium">${row.business_name ?? '-'}</div><div class="text-xs text-slate-400">${row.trade_name ?? ''}</div>`,
            },
            { data: 'pic_legal_names', name: 'pic_legal_names', orderable: false, render: (d) => d || '-' },
            { data: 'pic_leasing_names', name: 'pic_leasing_names', orderable: false, render: (d) => d || '-' },
            { data: 'agreement_step_id', name: 'agreement_step_id', render: (d) => renderStepBadge(d) },
            { data: 'cycle_info', orderable: false, searchable: false, render: (info) => renderDaysCell(info) },
            { data: 'cycle_info', orderable: false, searchable: false, render: (info) => renderCycleBadge(info) },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-right',
                render: (row) => renderAgreementRowActions(row),
            },
        ],
    });

    $('#agr_search').on('keyup', debounce(function () {
        Agreement.state.currentSearch = $(this).val();
        reloadTable();
    }, 350));

    $('#agr_cpny_filter').on('change', function () {
        Agreement.state.currentCpny = $(this).val();
        reloadTable();
    });
}

function debounce(callback, delay = 300) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => callback.apply(this, args), delay);
    };
}

function reloadTable() {
    if ($.fn.DataTable && $('#agreementTable').length) {
        $('#agreementTable').DataTable().ajax.reload(null, false);
    }
}

/* ----------------------------------------------------------------------
 | Create Agreement
 * ---------------------------------------------------------------------- */

const CREATE_STEP_COUNT = 4;

function initCreateAgreement() {
    $('#btnOpenCreateAgreement').on('click', function () {
        resetCreateForm();
        loadPicOptions('#create_pic_legal', { role_id: 'LEGALACCESS' });
        reloadCreatePicLeasingOptions();
        openModal('#createAgreementModal');
    });

    $(document).on('change', '#create_property_cd', function () {
        toggleCreateTradeName($(this).val());
    });

    // PIC Leasing is scoped to the selected company, so its option list is
    // re-fetched (not just filtered client-side) whenever company changes —
    // covers both a manual pick here and the job-flow's programmatic
    // `.trigger('change')` after presetting the company.
    $(document).on('change', '#create_cpny_id', function () {
        reloadCreatePicLeasingOptions();
    });

    $(document).on('input change', '#createAgreementForm .agr-input, #createAgreementForm .agr-textarea', function () {
        markFieldError($(this), false);
    });

    $('#btnCreateStepNext').on('click', function () {
        const step = Agreement.state.createStep;
        if (!validateCreateStep(step)) return;
        setCreateStep(Math.min(step + 1, CREATE_STEP_COUNT));
    });

    $('#btnCreateStepBack').on('click', function () {
        setCreateStep(Math.max(Agreement.state.createStep - 1, 1));
    });

    $('#createAgreementForm').on('submit', function (e) {
        e.preventDefault();

        for (let step = 1; step < CREATE_STEP_COUNT; step++) {
            if (!validateCreateStep(step)) {
                setCreateStep(step);
                return;
            }
        }

        submitCreateAgreement();
    });

    setCreateStep(1);
}

function toggleCreateTradeName(propertyCd) {
    const isMall = (propertyCd || '').toUpperCase() === 'MALL';
    $('#create_trade_name_wrap').toggleClass('hidden', !isMall);
}

function resetCreateForm() {
    const form = $('#createAgreementForm');
    if (form.length) form[0].reset();

    $('#create_pic_legal, #create_pic_leasing').val(null).trigger('change');
    toggleCreateTradeName('');
    $('#createAgreementForm .agr-input-error').removeClass('agr-input-error');
    lockCompanyField(false);
    Agreement.state.createStep = 1;
    setCreateStep(1);
}

// Company is derived from the selected job/business and must not be changed
// afterwards — but a real `disabled` select is dropped from FormData, so we
// lock interaction instead of disabling it, keeping its value submittable.
function lockCompanyField(locked) {
    const $select = $('#create_cpny_id');
    $select.data('locked', locked);
    $select.next('.select2-container').toggleClass('agr-locked', locked);
    $('#create_cpny_lock_note').toggleClass('hidden', !locked);
}

function setCreateStep(step) {
    Agreement.state.createStep = step;

    $('#createAgreementForm .agr-step').each(function () {
        $(this).toggleClass('hidden', parseInt($(this).data('step'), 10) !== step);
    });

    $('.agr-step-item').each(function () {
        const s = parseInt($(this).data('step-indicator'), 10);
        $(this).toggleClass('is-active', s === step).toggleClass('is-done', s < step);
    });

    $('#btnCreateStepBack, #btnCreateStepNext, #btnSubmitCreateAgreement').addClass('hidden');
    if (step > 1) $('#btnCreateStepBack').removeClass('hidden');
    if (step < CREATE_STEP_COUNT) $('#btnCreateStepNext').removeClass('hidden');
    if (step === CREATE_STEP_COUNT) $('#btnSubmitCreateAgreement').removeClass('hidden');

    if (step === CREATE_STEP_COUNT) renderCreateReview();

    $('#createAgreementModal .modal-panel').scrollTop(0);
}

function markFieldError($el, hasError) {
    if ($el.hasClass('agr-select2')) {
        $el.next('.select2-container').find('.select2-selection').toggleClass('agr-input-error', hasError);
    } else {
        $el.toggleClass('agr-input-error', hasError);
    }
}

// A 422 from the server (e.g. a bad file type/size that client-side checks
// don't catch) otherwise just shows a toast while the wizard sits wherever
// the user happened to be (usually the Review step) — this jumps back to
// the earliest step containing an invalid field and highlights it, the
// same way the client-side "Next" validation already does.
function showCreateValidationErrors(errors) {
    const keys = Object.keys(errors || {});
    if (!keys.length) return false;

    let targetStep = null;
    let $firstInvalid = null;

    keys.forEach((key) => {
        const base = key.split('.')[0];
        let $el = $(`#createAgreementForm [name="${base}"]`);
        if (!$el.length) $el = $(`#createAgreementForm [name="${base}[]"]`);
        if (!$el.length) return;

        markFieldError($el, true);

        const step = parseInt($el.closest('.agr-step').data('step'), 10);
        if (!targetStep || step < targetStep) {
            targetStep = step;
            $firstInvalid = $el;
        }
    });

    if (!targetStep) return false;

    setCreateStep(targetStep);

    if ($firstInvalid) {
        const $target = $firstInvalid.hasClass('agr-select2') ? $firstInvalid.next('.select2-container') : $firstInvalid;
        const $panel = $('#createAgreementModal .modal-panel');
        setTimeout(() => {
            $panel.animate({
                scrollTop: $panel.scrollTop() + $target.offset().top - $panel.offset().top - 100,
            }, 250);
        }, 50);
    }

    return true;
}

function validateCreateStep(step) {
    const $container = $(`#createAgreementForm .agr-step[data-step="${step}"]`);
    let valid = true;
    let $firstInvalid = null;

    $container.find('[required]').each(function () {
        const $el = $(this);
        let filled;

        if ($el.hasClass('agr-select2')) {
            const val = $el.val();
            filled = Array.isArray(val) ? val.length > 0 : !!val;
        } else {
            filled = $.trim($el.val() || '').length > 0;
            if (filled && typeof this.checkValidity === 'function') {
                filled = this.checkValidity();
            }
        }

        markFieldError($el, !filled);

        if (!filled) {
            valid = false;
            if (!$firstInvalid) $firstInvalid = $el;
        }
    });

    if (!valid) {
        showError('Please fill in all required fields before continuing.');

        if ($firstInvalid) {
            const $target = $firstInvalid.hasClass('agr-select2') ? $firstInvalid.next('.select2-container') : $firstInvalid;
            const $panel = $('#createAgreementModal .modal-panel');
            $panel.animate({
                scrollTop: $panel.scrollTop() + $target.offset().top - $panel.offset().top - 100,
            }, 250);
        }
    }

    return valid;
}

function renderCreateReview() {
    const val = (name) => $.trim($(`#createAgreementForm [name="${name}"]`).val() || '');
    const dash = (v) => (v ? escapeHtml(v) : '<span class="text-slate-400">-</span>');
    const dateOrDash = (v) => (v ? escapeHtml(formatDate(v)) : '<span class="text-slate-400">-</span>');
    const propertyLabels = { OFF: 'Office', MALL: 'Mall', APT: 'Apartment', HOTEL: 'Hotel' };

    const companyText = $('#create_cpny_id option:selected').text();
    const propertyCd = $('#create_property_cd').val();
    const propertyText = propertyLabels[propertyCd] || propertyCd;
    const picLegalText = $('#create_pic_legal option:selected').map(function () { return this.text; }).get().join(', ');
    const picLeasingText = $('#create_pic_leasing option:selected').map(function () { return this.text; }).get().join(', ');
    const fileEl = document.getElementById('create_bukti_pengiriman');
    const fileNames = fileEl ? Array.from(fileEl.files).map((f) => f.name).join(', ') : '';

    const row = (label, valueHtml) => `
        <div>
            <p class="text-xs text-slate-400">${escapeHtml(label)}</p>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">${valueHtml}</p>
        </div>`;

    const html = `
        <div class="rounded-xl border border-slate-200 p-4 dark:border-white/[0.06]">
            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">1. Tenant Information</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                ${row('Company', dash(companyText))}
                ${row('Tenant Name', dash(val('business_name')))}
                ${row('Property Type', dash(propertyText))}
                ${row('Trade Name', dash(val('trade_name')))}
                ${row('Floor', dash(val('floor_id')))}
                ${row('Unit', dash(val('unit_id')))}
                <div class="grid grid-cols-1 gap-4 sm:col-span-2 sm:grid-cols-3">
                    ${row('Tenant PIC Name', dash(val('pic_penyewa')))}
                    ${row('Tenant Phone Number', dash(val('pic_phonenumber_penyewa')))}
                    ${row('Tenant Email', dash(val('pic_email_penyewa')))}
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs text-slate-400">Tenant Correspondence Address</p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">${dash(val('business_address'))}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 p-4 dark:border-white/[0.06]">
            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">2. Document Information</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="grid grid-cols-1 gap-4 sm:col-span-2 sm:grid-cols-3">
                    ${row('PSM / Addendum Number', dash(val('no_psm_or_addendum')))}
                    ${row('PSM / Addendum Date', dateOrDash(val('psm_or_addendum_date')))}
                    ${row('Hardcopy Delivery Date', dateOrDash(val('psm_or_addendum_delivery_date')))}
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs text-slate-400">PIC Legal</p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">${dash(picLegalText)}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs text-slate-400">Proof of Delivery</p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">${dash(fileNames)}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 p-4 dark:border-white/[0.06]">
            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">3. Leasing Information</p>
            <div class="grid grid-cols-1 gap-4">
                ${row('PIC Leasing', dash(picLeasingText))}
            </div>
        </div>`;

    $('#create_review_content').html(html);
}

function submitCreateAgreement() {
    const formData = new FormData($('#createAgreementForm')[0]);

    $('#btnSubmitCreateAgreement').prop('disabled', true);

    $.ajax({
        url: Agreement.routes.store,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: () => showLoading(),
        success(res) {
            hideLoading();
            $('#btnSubmitCreateAgreement').prop('disabled', false);
            closeModal('#createAgreementModal');
            showSuccess(res.message || 'Agreement created successfully.');
            reloadTable();
            refreshCounts();

            if (window.jobsTable) {
                window.jobsTable.ajax.reload(null, false);
            }
        },
        error(xhr) {
            hideLoading();
            $('#btnSubmitCreateAgreement').prop('disabled', false);

            if (xhr.status === 422) {
                showCreateValidationErrors(xhr.responseJSON?.errors);
            }

            handleAjaxError(xhr);
        },
    });
}

/* ----------------------------------------------------------------------
 | Detail Modal
 * ---------------------------------------------------------------------- */

function openAgreementDetailModal(eid) {
    if (!eid) return;
    $('#detail_agreement_eid').val(eid);
    resetAgreementDetailTabs();
    $('#agreementActionDropdown').addClass('hidden');
    openModal('#detailAgreementModal');
    loadAgreementDetail(eid);
    Agreement.pushUrl(eid);
}

function resetAgreementDetailTabs() {
    $('.agr-detail-tab').removeClass('active');
    $('.agr-detail-tab[data-tab="tracking"]').addClass('active');
    $('.agr-tab-content').addClass('hidden');
    $('#agr_tracking_panel').removeClass('hidden');
}

function initDetailTabs() {
    $(document).on('click', '.agr-detail-tab', function () {
        const tab = $(this).data('tab');
        $('.agr-detail-tab').removeClass('active');
        $(this).addClass('active');
        $('.agr-tab-content').addClass('hidden');
        $(`#agr_${tab}_panel`).removeClass('hidden');
    });
}

function initActionDropdown() {
    $(document).on('click', '#agreementActionBtn', function (e) {
        e.stopPropagation();
        $('#agreementActionDropdown').toggleClass('hidden');
    });

    $(document).on('click', function () {
        $('#agreementActionDropdown').addClass('hidden');
    });

    $(document).on('click', '#agreementActionDropdown', function (e) {
        e.stopPropagation();
    });
}

function loadAgreementDetail(eid) {
    $.get(Agreement.routes.detail.replace(':eid', eid), function (res) {
        const data = res.data || {};
        populateAgreementDetail(data.agreement || {});
        renderAttachments(data.attachments || []);
        renderTracking(data.tracking || []);
        renderComments(data.comments || []);
        renderActionButtons(data.actions || {}, data.agreement || {});
        $('#detail_print_link').attr('href', Agreement.routes.print.replace(':eid', eid));
    }).fail(handleAjaxError);
}

function populateAgreementDetail(a) {
    $('#detail_agreement_id').text(a.agreement_id || '-');
    $('#detail_status_badge').html(renderStepBadge(a.agreement_step_id));
    $('#detail_subtitle').text([a.business_name, a.trade_name].filter(Boolean).join(' • ') || '-');
    $('#detail_cpny_id').text(a.cpny_name || a.cpny_id || '-');
    $('#detail_agreement_date').text(formatDate(a.agreement_date));
    $('#detail_cycle').html(`${renderCycleBadge(a.cycle_info)} ${renderDaysCell(a.cycle_info)}`);
    $('#detail_business_name').text(a.business_name || '-');
    $('#detail_trade_name').text(a.trade_name || '-');
    $('#detail_tenant_no').text(a.tenant_no || '-');
    $('#detail_floor_unit').text(`${a.floor_id || '-'} / ${a.unit_id || '-'}`);
    $('#detail_pic_legal').text(a.pic_legal_names || a.pic_legal || '-');
    $('#detail_pic_leasing').text(a.pic_leasing_names || a.pic_leasing || '-');
    $('#detail_pic_penyewa').text(a.pic_penyewa || '-');
    $('#detail_no_psm').text(a.no_psm_or_addendum || '-');
    $('#detail_created_user').text(a.created_user_name || a.created_user || '-');
    $('#detail_business_address').text(a.business_address || '-');
}

function renderAttachments(attachments) {
    const container = $('#detail_attachment_list');
    container.empty();

    if (!attachments.length) {
        container.html(`
            <div class="rounded-lg border border-dashed border-slate-300 px-4 py-5 text-center text-sm text-slate-400 dark:border-white/[0.08]">
                No attachment available
            </div>
        `);
        return;
    }

    attachments.forEach((file) => {
        container.append(`
            <a href="${file.url}" target="_blank" class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 transition-all duration-200 hover:bg-slate-50 dark:border-white/[0.06] dark:bg-slate-800 dark:hover:bg-white/[0.04]">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-white/[0.06] dark:text-slate-300">
                        <i class="fa-solid fa-file"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">${file.display_name || file.name}</div>
                        <div class="mt-1 text-xs text-slate-400">${(file.extention || '-').toUpperCase()} &bull; ${formatFileSize(file.size || 0)}</div>
                        <div class="mt-1 text-[11px] text-slate-400">Uploaded ${formatDateTime(file.created_at)}${file.created_by ? ` by ${escapeHtml(file.created_by)}` : ''}</div>
                    </div>
                </div>
                <i class="fa-solid fa-arrow-up-right-from-square text-slate-400"></i>
            </a>
        `);
    });
}

function trackingBadgeClass(status) {
    switch ((status || '').toUpperCase()) {
        case 'ACTIVE': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
        case 'HOLD': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
        case 'ESCALATED': return 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300';
        case 'COMPLETED': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
        case 'COMMENT': return 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-300';
        case 'SURAT1_SENT':
        case 'SURAT2_SENT': return 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300';
        default: return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
    }
}

function trackingBadgeLabel(status) {
    const labels = { SURAT1_SENT: 'Surat 1 Sent', SURAT2_SENT: 'Surat 2 Sent' };
    return labels[(status || '').toUpperCase()] || status || 'ACTIVITY';
}

function renderTrackingBadge(status) {
    return `<span class="inline-flex shrink-0 items-center rounded-lg px-2.5 py-1 text-[11px] font-semibold ${trackingBadgeClass(status)}">${trackingBadgeLabel(status)}</span>`;
}

function getAgreementTimelineIconStyle(item) {
    const title = (item.title || '').toLowerCase();
    const status = (item.status || '').toUpperCase();

    if (item.type === 'comment' || status === 'COMMENT') {
        return { icon: 'fa-solid fa-message', wrap: 'bg-fuchsia-500 text-white ring-fuchsia-100 dark:ring-fuchsia-500/10 shadow-fuchsia-500/20' };
    }
    if (title.includes('created')) {
        return { icon: 'fa-solid fa-plus', wrap: 'bg-blue-500 text-white ring-blue-100 dark:ring-blue-500/10 shadow-blue-500/20' };
    }
    if (title.includes('hold') || status === 'HOLD') {
        return { icon: 'fa-solid fa-pause', wrap: 'bg-amber-500 text-white ring-amber-100 dark:ring-amber-500/10 shadow-amber-500/20' };
    }
    if (title.includes('escalat') || status === 'ESCALATED') {
        return { icon: 'fa-solid fa-triangle-exclamation', wrap: 'bg-rose-500 text-white ring-rose-100 dark:ring-rose-500/10 shadow-rose-500/20' };
    }
    if (title.includes('complet') || status === 'COMPLETED') {
        return { icon: 'fa-solid fa-circle-check', wrap: 'bg-emerald-500 text-white ring-emerald-100 dark:ring-emerald-500/10 shadow-emerald-500/20' };
    }
    if (title.includes('sent')) {
        return { icon: 'fa-solid fa-paper-plane', wrap: 'bg-teal-500 text-white ring-teal-100 dark:ring-teal-500/10 shadow-teal-500/20' };
    }
    if (title.includes('updat')) {
        return { icon: 'fa-solid fa-pen', wrap: 'bg-indigo-500 text-white ring-indigo-100 dark:ring-indigo-500/10 shadow-indigo-500/20' };
    }
    if (title.includes('activat') || status === 'ACTIVE') {
        return { icon: 'fa-solid fa-circle-play', wrap: 'bg-green-500 text-white ring-green-100 dark:ring-green-500/10 shadow-green-500/20' };
    }

    return { icon: 'fa-solid fa-bolt', wrap: 'bg-slate-500 text-white ring-slate-100 dark:ring-slate-500/10 shadow-slate-500/20' };
}

function renderTracking(tracking) {
    const container = $('#detail_tracking_list');
    container.empty();

    if (!tracking.length) {
        container.html(`
            <div class="rounded-lg border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-400 dark:border-white/[0.08]">
                No tracking history
            </div>
        `);
        return;
    }

    tracking.forEach((item, index) => {
        const submittedBy = item.submitted_by || item.pic || 'System';
        const iconStyle = getAgreementTimelineIconStyle(item);
        const description = item.description && item.description !== '-' ? item.description : '';

        container.append(`
            <div class="relative pl-10 pb-3">
                ${index !== tracking.length - 1 ? `
                    <div class="absolute left-[15px] top-10 bottom-0 w-px bg-slate-200 dark:bg-white/[0.06]"></div>
                ` : ''}

                <div class="absolute left-0 top-1 flex h-8 w-8 items-center justify-center rounded-2xl ring-[1px] shadow-md transition-all duration-300 hover:scale-105 ${iconStyle.wrap}">
                    <i class="${iconStyle.icon} text-[11px]"></i>
                </div>

                <div class="rounded-lg border border-slate-200/80 bg-slate-50/20 px-4 py-2.5 dark:border-white/[0.05] dark:bg-slate-900/60">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="break-words text-[13px] font-semibold text-slate-800 dark:text-white">${item.title || 'Activity'}</div>
                            <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-[10px] text-slate-400 dark:text-slate-500">
                                <span class="max-w-[130px] truncate">${submittedBy}</span>
                                <span class="opacity-40">&bull;</span>
                                <span>${formatDateTime(item.datetime)}</span>
                            </div>
                        </div>
                        ${renderTrackingBadge(item.status)}
                    </div>

                    ${description ? `
                        <div class="mt-2 rounded-lg border border-slate-100 bg-slate-50/70 px-2.5 py-2 text-[11px] leading-5 text-slate-600 dark:border-white/[0.04] dark:bg-white/[0.03] dark:text-slate-300">
                            ${escapeHtml(description).replace(/\n/g, '<br>')}
                        </div>
                    ` : ''}
                </div>
            </div>
        `);
    });
}

function renderComments(comments) {
    const container = $('#detail_comment_list');
    container.empty();

    if (!comments.length) {
        container.html(`
            <div class="flex h-full items-center justify-center">
                <div class="rounded-lg border border-dashed border-slate-300 px-6 py-10 text-center dark:border-white/[0.08]">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-slate-100 dark:bg-white/[0.06]">
                        <i class="fa-regular fa-comments text-lg text-slate-400"></i>
                    </div>
                    <div class="mt-4 text-sm font-medium text-slate-700 dark:text-slate-200">No discussion yet</div>
                    <div class="mt-1 text-xs text-slate-400">Start conversation here</div>
                </div>
            </div>
        `);
        return;
    }

    comments.forEach((c) => {
        const user = c.created_by || 'User';
        const initials = user.substring(0, 1).toUpperCase();

        container.append(`
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">
                    ${initials}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-white/[0.06] dark:bg-slate-800">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-slate-800 dark:text-white">${escapeHtml(user)}</div>
                            <div class="text-[11px] text-slate-400">${formatDateTime(c.created_at)}</div>
                        </div>
                        <div class="whitespace-normal text-sm leading-7 text-slate-700 dark:text-slate-300">${escapeHtml(c.message).replace(/\n/g, '<br>')}</div>
                    </div>
                </div>
            </div>
        `);
    });
}

const ACTION_LABELS = {
    can_hold: { label: 'Hold Agreement', action: 'hold', icon: 'fa-solid fa-pause', class: 'text-amber-600 dark:text-amber-400' },
    can_activate: { label: 'Activate Agreement', action: 'activate', icon: 'fa-solid fa-bolt', class: 'text-emerald-600 dark:text-emerald-400' },
    can_complete: { label: 'Complete Agreement', action: 'complete', icon: 'fa-solid fa-flag-checkered', class: 'text-slate-600 dark:text-slate-300' },
};

function renderActionButtons(actions, agreement) {
    const container = $('#detail_action_buttons');
    container.empty();

    const eid = $('#detail_agreement_eid').val();
    const keys = Object.keys(ACTION_LABELS).filter((key) => actions[key]);

    if (!keys.length) {
        $('#agreementActionBtn').addClass('hidden');
        container.html('<div class="px-4 py-8 text-center text-sm font-medium text-slate-500 dark:text-slate-400">No action available</div>');
        return;
    }

    $('#agreementActionBtn').removeClass('hidden');

    keys.forEach((key) => {
        const cfg = ACTION_LABELS[key];
        container.append(`
            <button type="button" class="btn-agreement-action group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-all duration-200 hover:bg-slate-100 dark:hover:bg-white/[0.05]"
                data-action="${cfg.action}" data-eid="${eid}">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-100 transition-all duration-200 group-hover:scale-105 dark:bg-white/[0.05] ${cfg.class}">
                    <i class="${cfg.icon} text-[15px]"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-[13px] font-medium ${cfg.class}">${cfg.label}</div>
                </div>
                <i class="fa-solid fa-chevron-right text-[12px] text-slate-300 dark:text-slate-600"></i>
            </button>
        `);
    });
}

/* ----------------------------------------------------------------------
 | Generic Workflow Action Modal
 * ---------------------------------------------------------------------- */

const ACTION_CONFIG = {
    hold: {
        title: 'Put Agreement On Hold', url: Agreement.routes.hold, pic: false, descrRequired: true, attachments: false, psm: false,
        subtitle: 'Pause the follow-up cycle until this agreement is reactivated.',
        icon: 'fa-solid fa-pause', iconClass: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        submitLabel: 'Put On Hold', submitIcon: 'fa-solid fa-pause',
    },
    activate: {
        title: 'Activate Agreement (Revised Hardcopy Sent)', url: Agreement.routes.activate, pic: true, descrRequired: false,
        attachments: true, psm: true, psmRequired: true,
        attachmentField: 'bukti_pengiriman',
        attachmentLabel: 'Proof of Delivery (revised hardcopy) *',
        attachmentAccept: '.jpg,.jpeg,.png,.pdf',
        psmHint: 'Reactivating always means the revised hardcopy was sent back to the tenant — Delivery Date and Proof of Delivery are required, and this restarts the follow-up cycle from this date.',
        subtitle: 'Confirm the revised hardcopy has been sent back to the tenant.',
        icon: 'fa-solid fa-bolt', iconClass: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        submitLabel: 'Activate Agreement', submitIcon: 'fa-solid fa-bolt',
    },
    complete: {
        title: 'Complete Agreement', url: Agreement.routes.complete, pic: false, descrRequired: true, attachments: false, psm: false, attachmentField: 'attachments',
        subtitle: 'Mark this agreement as fully completed and close out the workflow.',
        icon: 'fa-solid fa-flag-checkered', iconClass: 'bg-slate-100 text-slate-600 dark:bg-white/[0.06] dark:text-slate-300',
        submitLabel: 'Complete Agreement', submitIcon: 'fa-solid fa-flag-checkered',
    },
};

function initActionModal() {
    $(document).on('click', '.btn-agreement-action', function () {
        const action = $(this).data('action');
        const eid = $(this).data('eid');
        $('#agreementActionDropdown').addClass('hidden');
        openActionModal(action, eid);
    });

    $(document).on('change', '#action_attachments', function (e) {
        Array.from(e.target.files).forEach((file) => {
            const key = file.name + '_' + file.size;
            if (!Agreement.state.actionAttachments.find((f) => f.name + '_' + f.size === key)) {
                Agreement.state.actionAttachments.push(file);
            }
        });
        renderActionAttachments();
        $(this).val('');
    });

    $('#actionAgreementForm').on('submit', function (e) {
        e.preventDefault();

        const steps = Agreement.state.actionSteps;
        for (let i = 1; i <= steps.length; i++) {
            if (!validateActionStep(i)) {
                setActionStep(i);
                return;
            }
        }

        submitAction();
    });
}

function openActionModal(action, eid) {
    const cfg = ACTION_CONFIG[action];
    if (!cfg || !eid) return;

    $('#actionAgreementForm')[0].reset();
    Agreement.state.actionAttachments = [];
    $('#action_attachment_list').empty();

    $('#action_eid').val(eid);
    $('#action_type').val(action);
    $('#action_modal_title').text(cfg.title);
    $('#action_modal_subtitle').text(cfg.subtitle || '');
    $('#action_modal_icon').attr('class', `flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-lg ${cfg.iconClass}`)
        .html(`<i class="${cfg.icon}"></i>`);
    $('#btnSubmitAction').html(`<i class="${cfg.submitIcon}"></i> ${cfg.submitLabel}`);
    $('#action_descr_label').text(cfg.descrRequired ? 'Notes (required)' : 'Notes');
    $('#action_response_descr').prop('required', cfg.descrRequired);

    $('#action_attachment_label').text(cfg.attachmentLabel || 'Attachments');
    $('#action_attachments').attr('accept', cfg.attachmentAccept || '');
    $('#action_psm_hint').text(cfg.psmHint || '').toggleClass('hidden', !cfg.psmHint);
    $('#action_psm_delivery_input').prop('required', !!cfg.psmRequired);
    $('#action_psm_delivery_label').text(cfg.psmRequired ? 'Delivery Date *' : 'Delivery Date');

    $('#action_no_psm_or_addendum').val('');
    $('#action_pic_legal, #action_pic_leasing').val(null).trigger('change');
    resetActionEditToggles();

    Agreement.state.actionSteps = computeActionSteps(cfg);
    setActionStep(1);

    openModal('#actionAgreementModal');

    // Pre-fill No. PSM/Addendum + current PIC assignment from the agreement
    // itself — both start locked (read-only) since this action isn't meant
    // to silently change them; Edit unlocks a field, Save locks it back.
    if (cfg.pic || cfg.psm) {
        $.get(Agreement.routes.detail.replace(':eid', eid), function (res) {
            // The modal may have been reopened for a different agreement
            // (or a different action) by the time this resolves.
            if ($('#action_eid').val() !== eid) return;

            const a = (res.data || {}).agreement || {};

            if (cfg.psm) {
                $('#action_no_psm_or_addendum').val(a.no_psm_or_addendum || '');
                // Unlike No. PSM/Addendum, this one isn't lock-toggled — just
                // pre-filled and left freely editable from the start.
                $('#action_psm_date').val(a.psm_or_addendum_date || '');
            }

            if (cfg.pic) {
                const picLegal = a.pic_legal_list || [];
                const picLeasing = a.pic_leasing_list || [];

                $.when(
                    loadPicOptions('#action_pic_legal', { role_id: 'LEGALACCESS' }),
                    // Not scoped to company here (unlike the create form's PIC
                    // Leasing) — this modal only gets an agreement id, not its
                    // company, so it can't filter by cpny_id without an extra fetch.
                    loadPicOptions('#action_pic_leasing', { role_id: 'LEASINGACCESS' })
                ).always(function () {
                    fillPicSelectValue('#action_pic_legal', picLegal);
                    fillPicSelectValue('#action_pic_leasing', picLeasing);
                });
            }
        }).fail(handleAjaxError);
    }
}

// Appends the agreement's current PIC usernames as select2 options if the
// pic-search results didn't already include them (e.g. someone no longer
// holding the role), then selects them — done last so it can't create a
// duplicate option for a username pic-search also returned.
function fillPicSelectValue(selector, usernames) {
    const $select = $(selector);

    usernames.forEach((username) => {
        if (!$select.find(`option[value="${username}"]`).length) {
            $select.append(new Option(username, username));
        }
    });

    $select.val(usernames).trigger('change');
}

function resetActionEditToggles() {
    $('.agr-edit-toggle').each(function () {
        setActionEditToggle($(this), false);
    });
}

function setActionEditToggle($btn, editing) {
    $btn.data('editing', editing);

    if (editing) {
        $btn.html('<i class="fa-solid fa-check text-[10px]"></i> Save')
            .removeClass('text-blue-600 dark:text-blue-400')
            .addClass('text-emerald-600 dark:text-emerald-400');
    } else {
        $btn.html('<i class="fa-solid fa-pen text-[10px]"></i> Edit')
            .removeClass('text-emerald-600 dark:text-emerald-400')
            .addClass('text-blue-600 dark:text-blue-400');
    }

    const target = $btn.data('target');

    if (target === '#action_no_psm_or_addendum') {
        $(target).prop('readonly', !editing).toggleClass('agr-locked', !editing);
        if (editing) $(target).trigger('focus');
    }

    if (target === '#action_pic_fields') {
        ['#action_pic_legal', '#action_pic_leasing'].forEach((sel) => {
            $(sel).data('locked', !editing);
            $(sel).next('.select2-container').toggleClass('agr-locked', !editing);
        });
    }
}

function initActionEditToggle() {
    $(document).on('click', '.agr-edit-toggle', function (e) {
        e.stopPropagation();
        const $btn = $(this);
        setActionEditToggle($btn, !$btn.data('editing'));
    });
}

/* ----------------------------------------------------------------------
 | Action Modal — Step Wizard
 *
 | Which steps apply depends on the action (Hold is Notes-only; Activate
 | walks through all four; Complete skips PIC) — so, unlike the fixed
 | 4-step create-agreement wizard, the step list here is rebuilt per
 | action instead of living as static HTML.
 * ---------------------------------------------------------------------- */

const ACTION_STEP_META = {
    pic: { key: 'pic', label: 'PIC', panel: '#action_pic_step' },
    psm: { key: 'psm', label: 'Document', panel: '#action_psm_step' },
    notes: { key: 'notes', label: 'Notes', panel: '#action_notes_step' },
    attachments: { key: 'attachments', label: 'Attachments', panel: '#action_attachment_step' },
};

function computeActionSteps(cfg) {
    return ['pic', 'psm', 'notes', 'attachments'].filter((key) => {
        if (key === 'notes') return true;
        if (key === 'attachments') return !!cfg.attachments;
        return !!cfg[key];
    });
}

function renderActionStepIndicator() {
    const steps = Agreement.state.actionSteps;
    const current = Agreement.state.actionStepIndex;

    $('#action_steps_indicator_wrap').toggleClass('hidden', steps.length <= 1);

    let html = '';
    steps.forEach((key, i) => {
        const num = i + 1;
        const cls = num === current ? 'is-active' : (num < current ? 'is-done' : '');
        html += `
            <div class="agr-step-item ${cls}">
                <span class="agr-step-circle"><span class="agr-step-num">${num}</span><i class="fa-solid fa-check"></i></span>
                <span class="agr-step-label">${ACTION_STEP_META[key].label}</span>
            </div>
        `;
        if (i < steps.length - 1) html += '<span class="agr-step-line"></span>';
    });

    $('#action_steps_indicator').html(html);
}

function setActionStep(index) {
    const steps = Agreement.state.actionSteps;
    Agreement.state.actionStepIndex = index;

    steps.forEach((key, i) => {
        $(ACTION_STEP_META[key].panel).toggleClass('hidden', i + 1 !== index);
    });

    renderActionStepIndicator();

    $('#btnActionStepBack, #btnActionStepNext, #btnSubmitAction').addClass('hidden');
    if (index > 1) $('#btnActionStepBack').removeClass('hidden');
    if (index < steps.length) $('#btnActionStepNext').removeClass('hidden');
    if (index === steps.length) $('#btnSubmitAction').removeClass('hidden');

    $('#actionAgreementModal .modal-panel').scrollTop(0);
}

function validateActionStep(index) {
    const key = Agreement.state.actionSteps[index - 1];
    const $panel = $(ACTION_STEP_META[key].panel);
    let valid = true;
    let $firstInvalid = null;

    // A field left unlocked (still showing "Save") hasn't been confirmed
    // yet — block moving on until it's locked back in, same as any other
    // unfinished required input.
    const $unsaved = $panel.find('.agr-edit-toggle').filter(function () {
        return !!$(this).data('editing');
    });

    if ($unsaved.length) {
        valid = false;
        showError('Please save your changes before continuing.');
        $unsaved.first().get(0)?.scrollIntoView({ block: 'nearest' });
        return false;
    }

    $panel.find('[required]').each(function () {
        const $el = $(this);
        const filled = $.trim($el.val() || '').length > 0
            && (typeof this.checkValidity !== 'function' || this.checkValidity());

        markFieldError($el, !filled);

        if (!filled) {
            valid = false;
            if (!$firstInvalid) $firstInvalid = $el;
        }
    });

    if (key === 'attachments' && ACTION_CONFIG[$('#action_type').val()]?.psmRequired && Agreement.state.actionAttachments.length === 0) {
        valid = false;
        showError('Proof of Delivery is required when reactivating with a revised hardcopy.');
    } else if (!valid) {
        showError('Please fill in all required fields before continuing.');
    }

    if (!valid && $firstInvalid) {
        $panel.get(0)?.scrollIntoView({ block: 'nearest' });
    }

    return valid;
}

// A 422 from the server otherwise just shows a toast while the wizard sits
// on whatever step the user happened to submit from — this jumps back to
// the earliest step containing the invalid field instead.
function jumpToActionFieldError(errors) {
    const keys = Object.keys(errors || {});
    if (!keys.length) return;

    let targetIndex = null;

    keys.forEach((key) => {
        const base = key.split('.')[0];
        const $el = $(`#actionAgreementForm [name="${base}"]`);
        if (!$el.length) return;

        markFieldError($el, true);

        const stepKey = $el.closest('.agr-step').data('step-key');
        const index = Agreement.state.actionSteps.indexOf(stepKey) + 1;
        if (index > 0 && (!targetIndex || index < targetIndex)) targetIndex = index;
    });

    if (targetIndex) setActionStep(targetIndex);
}

function initActionSteps() {
    $('#btnActionStepNext').on('click', function () {
        const index = Agreement.state.actionStepIndex;
        if (!validateActionStep(index)) return;
        setActionStep(Math.min(index + 1, Agreement.state.actionSteps.length));
    });

    $('#btnActionStepBack').on('click', function () {
        setActionStep(Math.max(Agreement.state.actionStepIndex - 1, 1));
    });
}

function loadPicOptions(selector, params = {}, force = false) {
    const select = $(selector);
    if (!force && select.find('option').length) return $.Deferred().resolve().promise();

    select.empty();
    if (!select.prop('multiple')) {
        select.append(new Option('-', ''));
    }

    return $.get(Agreement.routes.picSearch, params, function (res) {
        (res.results || []).forEach((item) => select.append(new Option(item.text, item.id)));
    }).fail(function (xhr) {
        handleAjaxError(xhr);
    });
}

// PIC Leasing's pool depends on which company is selected, so (unlike PIC
// Legal) it can't just be loaded once — this clears the current selection
// and re-fetches against whatever company is selected right now (none yet
// falls back to every LEASINGACCESS holder, unscoped).
function reloadCreatePicLeasingOptions() {
    const cpnyId = $('#create_cpny_id').val();
    const params = { role_id: 'LEASINGACCESS' };
    if (cpnyId) params.cpny_id = cpnyId;

    $('#create_pic_leasing').val(null).trigger('change');
    loadPicOptions('#create_pic_leasing', params, true);
}

function renderActionAttachments() {
    const container = $('#action_attachment_list');
    container.empty();
    Agreement.state.actionAttachments.forEach((file, index) => {
        container.append(`
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.03] px-4 py-3">
                <div class="min-w-0 flex-1 truncate text-sm font-medium text-slate-700 dark:text-slate-200">${file.name}</div>
                <button type="button" onclick="removeActionAttachment(${index})" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100">
                    <i class="fa-solid fa-trash text-xs"></i>
                </button>
            </div>
        `);
    });
}

function removeActionAttachment(index) {
    Agreement.state.actionAttachments.splice(index, 1);
    renderActionAttachments();
}

const ACTION_CONFIRM_TEXT = {
    hold: 'Put this agreement on hold? Follow-up timers pause until it\'s reactivated.',
    activate: 'Activate this agreement? This restarts the follow-up cycle from the delivery date entered above.',
    complete: 'Mark this agreement as complete? This stops all further follow-up and email reminders.',
};

function submitAction() {
    const action = $('#action_type').val();
    const eid = $('#action_eid').val();
    const cfg = ACTION_CONFIG[action];
    if (!cfg) return;

    Swal.fire({
        title: 'Are you sure?',
        text: ACTION_CONFIRM_TEXT[action] || 'Proceed with this action?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        confirmButtonColor: '#2563eb',
    }).then((result) => {
        if (!result.isConfirmed) return;

        const formData = new FormData($('#actionAgreementForm')[0]);
        const attachmentField = cfg.attachmentField || 'attachments';
        Agreement.state.actionAttachments.forEach((file) => formData.append(`${attachmentField}[]`, file));

        $('#btnSubmitAction').prop('disabled', true);

        $.ajax({
            url: cfg.url.replace(':eid', eid),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: () => showLoading(),
            success(res) {
                hideLoading();
                $('#btnSubmitAction').prop('disabled', false);
                closeModal('#actionAgreementModal');
                showSuccess(res.message || 'Action completed successfully.');
                loadAgreementDetail(eid);
                reloadTable();
                refreshCounts();
            },
            error(xhr) {
                hideLoading();
                $('#btnSubmitAction').prop('disabled', false);
                if (xhr.status === 422) jumpToActionFieldError(xhr.responseJSON?.errors);
                handleAjaxError(xhr);
            },
        });
    });
}

/* ----------------------------------------------------------------------
 | Comments
 * ---------------------------------------------------------------------- */

function initComment() {
    $('#commentForm').on('submit', function (e) {
        e.preventDefault();

        const eid = $('#detail_agreement_eid').val();
        const message = $('#comment_message').val();
        if (!eid || !message) return;

        $.ajax({
            url: Agreement.routes.comment.replace(':eid', eid),
            type: 'POST',
            data: { message },
            beforeSend: () => showLoading(),
            success() {
                hideLoading();
                $('#comment_message').val('');
                showSuccess('Comment posted.');
                loadAgreementDetail(eid);
            },
            error(xhr) {
                hideLoading();
                handleAjaxError(xhr);
            },
        });
    });
}

/* ----------------------------------------------------------------------
 | Table Row Click -> Detail
 * ---------------------------------------------------------------------- */

function initTableRowClick() {
    $(document).on('click', '.btn-view-agreement', function () {
        openAgreementDetailModal($(this).data('eid'));
    });
}

/* ----------------------------------------------------------------------
 | Init
 * ---------------------------------------------------------------------- */

$(function () {
    initModal();
    initDataTable();
    initViewSwitcher();
    initCreateAgreement();
    initActionModal();
    initComment();
    initTableRowClick();
    initDetailTabs();
    initActionDropdown();
    initRowActionDropdown();
    initActionEditToggle();
    initActionSteps();

    if ($.fn.select2) {
        $('#agr_cpny_filter').select2({ width: '100%' });

        // Dropdowns append to <body> by default (not scoped to the modal's
        // .modal-panel) so they float freely above the modal instead of
        // being clipped by its rounded corners/overflow — the z-index fix
        // for painting above the modal itself lives in style.blade.php
        // (.select2-dropdown { z-index: ... }).
        $('#create_cpny_id').select2({ width: '100%' });

        $('#create_cpny_id').on('select2:opening', function (e) {
            if ($(this).data('locked')) e.preventDefault();
        });

        $('#create_pic_legal, #create_pic_leasing').select2({
            width: '100%',
            placeholder: 'Select user(s)...',
        });

        $('#action_pic_legal, #action_pic_leasing').select2({
            width: '100%',
            placeholder: 'Select user(s)...',
        });

        $('#action_pic_legal, #action_pic_leasing').on('select2:opening', function (e) {
            if ($(this).data('locked')) e.preventDefault();
        });
    }
});

window.openAgreementDetailModal = openAgreementDetailModal;
window.removeActionAttachment = removeActionAttachment;
