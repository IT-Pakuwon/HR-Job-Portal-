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
    return `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${stepBadgeClass(step)}">${step ?? '-'}</span>`;
}

function statusLabel(status) {
    switch (status) {
        case 'P': return 'Open';
        case 'C': return 'Completed';
        case 'X': return 'Cancelled';
        default: return status ?? '-';
    }
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
            { data: 'agreement_id', name: 'agreement_id' },
            { data: 'agreement_date', name: 'agreement_date', render: (d) => formatDate(d) },
            { data: 'cpny_id', name: 'cpny_id' },
            {
                data: null,
                render: (row) => `<div class="font-medium">${row.business_name ?? '-'}</div><div class="text-xs text-slate-400">${row.trade_name ?? ''}</div>`,
            },
            { data: 'pic_legal', name: 'pic_legal', render: (d) => d || '-' },
            { data: 'pic_leasing', name: 'pic_leasing', render: (d) => d || '-' },
            { data: 'agreement_step_id', name: 'agreement_step_id', render: (d) => renderStepBadge(d) },
            { data: 'status', name: 'status', render: (d) => statusLabel(d) },
            {
                data: 'eid',
                orderable: false,
                searchable: false,
                className: 'text-right',
                render: (eid) => `
                    <button type="button" class="btn-view-agreement rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]" data-eid="${eid}">
                        View
                    </button>
                `,
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
                ${row('Tenant PIC Name', dash(val('pic_penyewa')))}
                ${row('Tenant Phone Number', dash(val('pic_phonenumber_penyewa')))}
                ${row('Tenant Email', dash(val('pic_email_penyewa')))}
                <div class="sm:col-span-2">
                    <p class="text-xs text-slate-400">Tenant Correspondence Address</p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">${dash(val('business_address'))}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 p-4 dark:border-white/[0.06]">
            <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-400">2. Document Information</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                ${row('PSM / Addendum Number', dash(val('no_psm_or_addendum')))}
                ${row('PSM / Addendum Date', dateOrDash(val('psm_or_addendum_date')))}
                ${row('Hardcopy Delivery Date', dateOrDash(val('psm_or_addendum_delivery_date')))}
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
        beforeSend: showLoading,
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
    openModal('#detailAgreementModal');
    loadAgreementDetail(eid);
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
    $('#detail_cpny_id').text(a.cpny_id || '-');
    $('#detail_agreement_date').text(formatDate(a.agreement_date));
    $('#detail_step').html(renderStepBadge(a.agreement_step_id));
    $('#detail_business_name').text(a.business_name || '-');
    $('#detail_trade_name').text(a.trade_name || '-');
    $('#detail_tenant_no').text(a.tenant_no || '-');
    $('#detail_floor_unit').text(`${a.floor_id || '-'} / ${a.unit_id || '-'}`);
    $('#detail_pic_legal').text(a.pic_legal || '-');
    $('#detail_pic_leasing').text(a.pic_leasing || '-');
    $('#detail_pic_penyewa').text(a.pic_penyewa || '-');
    $('#detail_no_psm').text(a.no_psm_or_addendum || '-');
    $('#detail_created_user').text(a.created_user || '-');
    $('#detail_business_address').text(a.business_address || '-');
}

function renderAttachments(attachments) {
    const container = $('#detail_attachment_list');
    container.empty();

    if (!attachments.length) {
        container.html('<p class="text-slate-400">No attachments.</p>');
        return;
    }

    attachments.forEach((file) => {
        container.append(`
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-white/[0.06] px-4 py-3">
                <div class="min-w-0 flex-1 truncate font-medium text-slate-700 dark:text-slate-200">${file.display_name || file.name}</div>
                <a href="${file.url}" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.06] dark:hover:text-white">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
        `);
    });
}

function renderTracking(tracking) {
    const container = $('#detail_tracking_list');
    container.empty();

    if (!tracking.length) {
        container.html('<p class="text-slate-400">No activity yet.</p>');
        return;
    }

    tracking.forEach((item) => {
        container.append(`
            <div class="border-l-2 border-slate-200 pl-4 dark:border-white/[0.08]">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">${item.title}</p>
                    <p class="text-xs text-slate-400">${formatDateTime(item.datetime)}</p>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">${item.description || ''}</p>
                <p class="mt-1 text-[11px] text-slate-400">by ${item.submitted_by || item.pic || 'System'}</p>
            </div>
        `);
    });
}

function renderComments(comments) {
    const container = $('#detail_comment_list');
    container.empty();

    if (!comments.length) {
        container.html('<p class="text-slate-400">No comments yet.</p>');
        return;
    }

    comments.forEach((c) => {
        container.append(`
            <div class="rounded-xl border border-slate-200 p-3 dark:border-white/[0.06]">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">${c.created_by}</p>
                    <p class="text-[11px] text-slate-400">${formatDateTime(c.created_at)}</p>
                </div>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">${c.message}</p>
            </div>
        `);
    });
}

const ACTION_LABELS = {
    can_edit: { label: 'Edit', action: 'edit', color: 'slate' },
    can_hold: { label: 'Hold', action: 'hold', color: 'yellow' },
    can_activate: { label: 'Activate', action: 'activate', color: 'green' },
    can_escalate: { label: 'Escalate', action: 'escalate', color: 'red' },
    can_complete: { label: 'Complete', action: 'complete', color: 'slate' },
};

function renderActionButtons(actions, agreement) {
    const container = $('#detail_action_buttons');
    container.empty();

    Object.keys(ACTION_LABELS).forEach((key) => {
        if (!actions[key]) return;
        if (key === 'can_edit') return; // edit uses the create-style form; skipped in this lean UI

        const cfg = ACTION_LABELS[key];
        container.append(`
            <button type="button" class="btn-agreement-action rounded-lg border border-${cfg.color}-200 bg-${cfg.color}-50 px-4 py-2.5 text-sm font-semibold text-${cfg.color}-700 hover:bg-${cfg.color}-100 dark:border-${cfg.color}-500/20 dark:bg-${cfg.color}-500/10 dark:text-${cfg.color}-300"
                data-action="${cfg.action}" data-eid="${$('#detail_agreement_eid').val()}">
                ${cfg.label}
            </button>
        `);
    });

    if (!container.children().length) {
        container.html('<p class="text-sm text-slate-400">No actions available.</p>');
    }
}

/* ----------------------------------------------------------------------
 | Generic Workflow Action Modal
 * ---------------------------------------------------------------------- */

const ACTION_CONFIG = {
    hold: { title: 'Put Agreement On Hold', url: Agreement.routes.hold, pic: false, descrRequired: true, attachments: false, psm: false },
    activate: { title: 'Activate Agreement', url: Agreement.routes.activate, pic: true, descrRequired: false, attachments: false, psm: false },
    escalate: { title: 'Escalate Agreement', url: Agreement.routes.escalate, pic: false, descrRequired: true, attachments: false, psm: false },
    complete: { title: 'Complete Agreement', url: Agreement.routes.complete, pic: false, descrRequired: true, attachments: true, psm: true },
};

function initActionModal() {
    $(document).on('click', '.btn-agreement-action', function () {
        const action = $(this).data('action');
        const eid = $(this).data('eid');
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
    $('#action_descr_label').text(cfg.descrRequired ? 'Notes (required)' : 'Notes');
    $('#action_response_descr').prop('required', cfg.descrRequired);

    $('#action_pic_fields').toggleClass('hidden', !cfg.pic);
    $('#action_attachment_fields').toggleClass('hidden', !cfg.attachments);
    $('#action_psm_fields').toggleClass('hidden', !cfg.psm);

    $('#action_pic_legal, #action_pic_leasing').val(null).trigger('change');

    if (cfg.pic) {
        loadPicOptions('#action_pic_legal', { role_id: 'LEGALACCESS' });
        // Not scoped to company here (unlike the create form's PIC Leasing) —
        // this modal only gets an agreement id, not its company, so it can't
        // filter by cpny_id without an extra fetch.
        loadPicOptions('#action_pic_leasing', { role_id: 'LEASINGACCESS' });
    }

    openModal('#actionAgreementModal');
}

function loadPicOptions(selector, params = {}, force = false) {
    const select = $(selector);
    if (!force && select.find('option').length) return;

    select.empty();
    if (!select.prop('multiple')) {
        select.append(new Option('-', ''));
    }

    $.get(Agreement.routes.picSearch, params, function (res) {
        (res.results || []).forEach((item) => select.append(new Option(item.text, item.id)));
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

function submitAction() {
    const action = $('#action_type').val();
    const eid = $('#action_eid').val();
    const cfg = ACTION_CONFIG[action];
    if (!cfg) return;

    const formData = new FormData($('#actionAgreementForm')[0]);
    Agreement.state.actionAttachments.forEach((file) => formData.append('attachments[]', file));

    $('#btnSubmitAction').prop('disabled', true);

    $.ajax({
        url: cfg.url.replace(':eid', eid),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: showLoading,
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
            handleAjaxError(xhr);
        },
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
            success() {
                $('#comment_message').val('');
                loadAgreementDetail(eid);
            },
            error: handleAjaxError,
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
    }
});

window.openAgreementDetailModal = openAgreementDetailModal;
window.removeActionAttachment = removeActionAttachment;
