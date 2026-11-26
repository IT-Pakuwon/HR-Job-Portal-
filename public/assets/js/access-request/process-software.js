async function openProcessSoftwareModal(id) {
    openModal('#processSoftwareModal');
    renderProcessSoftwareLoading();

    try {
        const res = await fetchProcessSoftwareDetail(id);
        const access = res.access ?? {};
        const details = (res.details ?? []).filter(item => item.group_category === 'SOFTWARE');

        renderProcessSoftwareInfo(access);
        renderProcessSoftwareItems(access, details);
    } catch (xhr) {
        console.error(xhr);
        renderProcessSoftwareError(xhr);
    }
}

async function fetchProcessSoftwareDetail(id) {
    return $.ajax({ url: `/access-request/detail/${id}`, type: 'GET' });
}

function renderProcessSoftwareLoading() {
    const loader = `
        <div class="flex items-center justify-center rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-[#111c2d] py-20">
            <div class="h-10 w-10 animate-spin rounded-lg border-4 border-slate-300 border-t-slate-700 dark:border-slate-700 dark:border-t-slate-200"></div>
        </div>`;

    $('#processSoftwareInfoContainer').html(loader);
    $('#processSoftwareDetailContainer').html(loader);
}

function renderProcessSoftwareError(xhr) {
    const message = xhr.responseJSON?.message ?? 'Failed load software process';
    const html = `
        <div class="rounded-lg border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-6 py-10 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-red-100 dark:bg-red-500/20 text-red-500 dark:text-red-300">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
            </div>
            <p class="mt-4 text-sm font-semibold text-red-700 dark:text-red-300">${message}</p>
        </div>`;

    $('#processSoftwareInfoContainer').html(html);
    $('#processSoftwareDetailContainer').html('');
}

function renderProcessSoftwareInfo(access) {
    $('#processSoftwareInfoContainer').html(renderAccessProcessInfo(access));
}

function renderProcessSoftwareItems(access, details = []) {
    $('#processSoftwareDetailContainer').html(renderAccessProcessItems('software', access, details));
}

// Eye toggle for password inputs in process modals
$(document).on('click', '.btn-toggle-pw-input', function () {
    const btn     = $(this);
    const visible = btn.data('visible') == 1;
    const input   = btn.siblings('input');

    if (visible) {
        input.attr('type', 'password');
        btn.data('visible', 0).html('<i class="fa-solid fa-eye-slash text-xs"></i>');
    } else {
        input.attr('type', 'text');
        btn.data('visible', 1).html('<i class="fa-solid fa-eye text-xs"></i>');
    }
});

$(document).on('click', '.btn-save-software', async function () {
    const button   = $(this);
    const hash     = button.data('doc');
    const detailId = button.data('id');
    const response = $(`.software-response[data-id="${detailId}"]`).val()?.trim();
    const username = $(`.software-username[data-id="${detailId}"]`).val()?.trim();
    const password = $(`.software-password[data-id="${detailId}"]`).val()?.trim();

    await submitSoftwareItem(button, hash, detailId, response, username, password, 'SAVE');
});

$(document).on('click', '.btn-complete-software', async function () {
    const button     = $(this);
    const hash       = button.data('doc');
    const detailId   = button.data('id');
    const accessType = (button.data('type') ?? '').toUpperCase();
    const response   = $(`.software-response[data-id="${detailId}"]`).val()?.trim();
    const username   = $(`.software-username[data-id="${detailId}"]`).val()?.trim();
    const password   = $(`.software-password[data-id="${detailId}"]`).val()?.trim();

    if (accessType === 'NEW') {
        if (!username) { swalWarning('Username is required'); return; }
        if (!password) { swalWarning('Password is required'); return; }
    }

    await submitSoftwareItem(button, hash, detailId, response, username, password, 'DONE');
});

async function submitSoftwareItem(button, hash, detailId, response, username, password, action) {
    const originalHtml = button.html();
    button.prop('disabled', true).html('<i class="fa-solid fa-spinner animate-spin text-xs"></i>');

    try {
        const res = await $.ajax({
            url: `/access-request/process-software/${hash}`,
            type: 'POST',
            data: {
                details: [{
                    id: detailId,
                    action,
                    access_response: response,
                    access_username: username,
                    access_password: password,
                }]
            }
        });

        itrToast('success', res.message ?? 'Software access updated successfully');
        await refreshAccessProcessModal('software', hash, detailId);
        table.ajax.reload(null, false);

    } catch (xhr) {
        console.error(xhr);
        swalError(xhr.responseJSON?.message ?? 'Failed process software access');
    } finally {
        button.prop('disabled', false).html(originalHtml);
    }
}
