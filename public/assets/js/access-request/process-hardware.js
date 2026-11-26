async function openProcessHardwareModal(id) {
    openModal('#processHardwareModal');
    renderProcessHardwareLoading();

    try {
        const res = await fetchProcessHardwareDetail(id);
        const access = res.access ?? {};
        const details = (res.details ?? []).filter(item => item.group_category === 'HARDWARE');

        renderProcessHardwareInfo(access);
        renderProcessHardwareItems(access, details);
    } catch (xhr) {
        console.error(xhr);
        renderProcessHardwareError(xhr);
    }
}

async function fetchProcessHardwareDetail(id) {
    return $.ajax({ url: `/access-request/detail/${id}`, type: 'GET' });
}

function renderProcessHardwareLoading() {
    const loader = `
        <div class="flex items-center justify-center rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-[#111c2d] py-20">
            <div class="h-10 w-10 animate-spin rounded-lg border-4 border-slate-300 border-t-slate-700 dark:border-slate-700 dark:border-t-slate-200"></div>
        </div>`;

    $('#processHardwareInfoContainer').html(loader);
    $('#processHardwareDetailContainer').html(loader);
}

function renderProcessHardwareError(xhr) {
    const message = xhr.responseJSON?.message ?? 'Failed load hardware process';
    const html = `
        <div class="rounded-lg border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-6 py-10 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-red-100 dark:bg-red-500/20 text-red-500 dark:text-red-300">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
            </div>
            <p class="mt-4 text-sm font-semibold text-red-700 dark:text-red-300">${message}</p>
        </div>`;

    $('#processHardwareInfoContainer').html(html);
    $('#processHardwareDetailContainer').html('');
}

function renderProcessHardwareInfo(access) {
    $('#processHardwareInfoContainer').html(renderAccessProcessInfo(access));
}

function renderProcessHardwareItems(access, details = []) {
    $('#processHardwareDetailContainer').html(renderAccessProcessItems('hardware', access, details));
}

$(document).on('click', '.btn-save-hardware', async function () {
    const button   = $(this);
    const hash     = button.data('doc');
    const detailId = button.data('id');
    const response = $(`.hardware-response[data-id="${detailId}"]`).val()?.trim();
    const username = $(`.hardware-username[data-id="${detailId}"]`).val()?.trim();
    const password = $(`.hardware-password[data-id="${detailId}"]`).val()?.trim();

    await submitHardwareItem(button, hash, detailId, response, username, password, 'SAVE');
});

$(document).on('click', '.btn-complete-hardware', async function () {
    const button   = $(this);
    const hash     = button.data('doc');
    const detailId = button.data('id');
    const response = $(`.hardware-response[data-id="${detailId}"]`).val()?.trim();
    const username = $(`.hardware-username[data-id="${detailId}"]`).val()?.trim();
    const password = $(`.hardware-password[data-id="${detailId}"]`).val()?.trim();

    await submitHardwareItem(button, hash, detailId, response, username, password, 'DONE');
});

async function submitHardwareItem(button, hash, detailId, response, username, password, action) {
    const originalHtml = button.html();
    button.prop('disabled', true).html('<i class="fa-solid fa-spinner animate-spin text-xs"></i>');

    try {
        const res = await $.ajax({
            url: `/access-request/process-hardware/${hash}`,
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

        itrToast('success', res.message ?? 'Hardware access updated successfully');
        await refreshAccessProcessModal('hardware', hash, detailId);
        table.ajax.reload(null, false);

    } catch (xhr) {
        console.error(xhr);
        swalError(xhr.responseJSON?.message ?? 'Failed process hardware access');
    } finally {
        button.prop('disabled', false).html(originalHtml);
    }
}
