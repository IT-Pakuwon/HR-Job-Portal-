async function openProcessHardwareModal(id) {

    openModal('#processHardwareModal');

    renderProcessHardwareLoading();

    try {

        const res = await fetchProcessHardwareDetail(id);

        const access = res.access ?? {};

        const details = (res.details ?? []).filter(item => {
            return item.group_category === 'HARDWARE';
        });

        renderProcessHardwareInfo(access);

        renderProcessHardwareItems(access, details);

    } catch (xhr) {

        console.error(xhr);

        renderProcessHardwareError(xhr);

    }

}

async function fetchProcessHardwareDetail(id) {

    return $.ajax({
        url: `/access-request/detail/${id}`,
        type: 'GET'
    });

}

function renderProcessHardwareLoading() {

    const loader = `
        <div class="
            flex items-center justify-center
            rounded-xl border border-slate-200
            bg-white py-20
        ">
            <div class="
                h-10 w-10 animate-spin rounded-full
                border-4 border-slate-300 border-t-slate-700
            "></div>
        </div>
    `;

    $('#processHardwareInfoContainer').html(loader);

    $('#processHardwareDetailContainer').html(loader);

    $('#processHardwareActionContainer').html('');

}

function renderProcessHardwareError(xhr) {

    const message =
        xhr.responseJSON?.message ??
        'Failed load hardware process';

    const html = `
        <div class="
            rounded-xl border border-red-200
            bg-red-50 px-6 py-10
            text-center
        ">

            <div class="
                mx-auto flex h-14 w-14
                items-center justify-center
                rounded-full bg-red-100
                text-red-500
            ">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
            </div>

            <p class="mt-4 text-sm font-semibold text-red-700">
                ${message}
            </p>

        </div>
    `;

    $('#processHardwareInfoContainer').html(html);

    $('#processHardwareDetailContainer').html('');

    $('#processHardwareActionContainer').html('');

}

function renderProcessHardwareInfo(access) {

    $('#processHardwareInfoContainer').html(`

        <div class="
            rounded-xl border border-slate-200
            bg-white
        ">

            <div class="
                border-b border-slate-200
                px-5 py-4
            ">

                <h3 class="
                    text-sm font-bold uppercase
                    tracking-wider text-slate-700
                ">
                    Request Information
                </h3>

            </div>

            <div class="
                grid grid-cols-1 gap-x-10 gap-y-4
                p-5 sm:grid-cols-2
            ">

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]
                        text-xs font-semibold uppercase
                        tracking-wider text-slate-400
                    ">
                        Document
                    </p>

                    <span class="text-slate-300">:</span>

                    <p class="
                        text-sm font-semibold text-slate-700
                    ">
                        ${access.docid ?? '-'}
                    </p>

                </div>

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]
                        text-xs font-semibold uppercase
                        tracking-wider text-slate-400
                    ">
                        Requester
                    </p>

                    <span class="text-slate-300">:</span>

                    <p class="
                        text-sm font-semibold text-slate-700
                    ">
                        ${access.user_peminta ?? '-'}
                    </p>

                </div>

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]
                        text-xs font-semibold uppercase
                        tracking-wider text-slate-400
                    ">
                        Company
                    </p>

                    <span class="text-slate-300">:</span>

                    <p class="
                        text-sm font-semibold text-slate-700
                    ">
                        ${access.cpny_id ?? '-'}
                    </p>

                </div>

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]
                        text-xs font-semibold uppercase
                        tracking-wider text-slate-400
                    ">
                        Department
                    </p>

                    <span class="text-slate-300">:</span>

                    <p class="
                        text-sm font-semibold text-slate-700
                    ">
                        ${access.department_id ?? '-'}
                    </p>

                </div>

            </div>

        </div>

    `);

}

function renderProcessHardwareItems(access, details = []) {

    const canViewPassword =
        access.can_view_password === true;

    let rows = '';

    details.forEach(item => {

        const isCompleted = item.status === 'C';

        rows += `

            <tr class="border-b border-slate-100 align-top">

                <td class="px-5 py-5">

                    <div class="space-y-2">

                        <div class="
                            text-sm font-semibold
                            text-slate-700
                        ">
                            ${item.access_descr ?? '-'}
                        </div>

                        ${
                            item.access_pic
                            ? `
                                <div class="
                                    inline-flex items-center gap-2
                                    rounded-full bg-slate-100
                                    px-3 py-1
                                    text-[11px] font-medium
                                    text-slate-600
                                ">
                                    <i class="fa-solid fa-user text-[10px]"></i>
                                    ${item.access_pic}
                                </div>
                            `
                            : ''
                        }

                    </div>

                </td>

                <td class="px-5 py-5">

                ${
                    isCompleted
                    ? `
                        <span class="
                            inline-flex items-center gap-1.5
                            rounded-full
                            bg-emerald-100
                            px-3 py-1.5
                            text-xs font-semibold
                            text-emerald-700
                            whitespace-nowrap
                        ">

                            <i class="fa-solid fa-circle-check text-[11px]"></i>
                        </span>
                    `
                    : `
                        <span class="
                            inline-flex items-center gap-1.5
                            rounded-full
                            bg-blue-100
                            px-3 py-1.5
                            text-xs font-semibold
                            text-blue-700
                            whitespace-nowrap
                        ">

                            <i class="fa-solid fa-clock text-[11px]"></i>

                        </span>
                    `
                }

                </td>

                <td class="px-5 py-5 min-w-[280px]">

                    ${
                        isCompleted
                        ? `
                            <div class="
                                rounded-xl border border-slate-200
                                bg-slate-50 px-4 py-3
                            ">

                                <div class="
                                    whitespace-normal
                                    break-words
                                    text-sm leading-relaxed
                                    text-slate-700
                                ">
                                    ${item.access_response ?? '-'}
                                </div>

                            </div>
                        `
                        : `
                            <textarea
                                rows="3"
                                class="
                                    hardware-response
                                    w-full rounded-xl
                                    border border-slate-200
                                    bg-white
                                    px-4 py-3 text-sm
                                    text-slate-700
                                    focus:border-slate-400
                                    focus:ring-0
                                "
                                data-id="${item.id}"
                                placeholder="Input response ..."
                            >${item.access_response ?? ''}</textarea>
                        `
                    }

                </td>

                <td class="px-5 py-5 min-w-[220px]">

                    ${
                        isCompleted
                        ? `
                            <div class="
                                rounded-xl border border-slate-200
                                bg-slate-50 px-4 py-3
                            ">

                                <div class="
                                    break-all text-sm
                                    text-slate-700
                                ">
                                    ${item.access_username ?? '-'}
                                </div>

                            </div>
                        `
                        : `
                            <input
                                type="text"
                                class="
                                    hardware-username
                                    w-full rounded-xl
                                    border border-slate-200
                                    bg-white
                                    px-4 py-3 text-sm
                                    text-slate-700
                                    focus:border-slate-400
                                    focus:ring-0
                                "
                                data-id="${item.id}"
                                value="${item.access_username ?? ''}"
                                placeholder="Username"
                            >
                        `
                    }

                </td>

                <td class="px-5 py-5 min-w-[220px]">

                    ${
                        isCompleted
                        ? `
                            <div class="
                                rounded-xl border border-slate-200
                                bg-slate-50 px-4 py-3
                            ">

                                <div class="
                                    break-all text-sm
                                    text-slate-700
                                ">
                                    ${
                                        canViewPassword
                                        ? (item.access_password ?? '-')
                                        : '••••••••'
                                    }
                                </div>

                            </div>
                        `
                        : `
                            <input
                                type="${
                                    canViewPassword
                                    ? 'text'
                                    : 'password'
                                }"
                                class="
                                    hardware-password
                                    w-full rounded-xl
                                    border border-slate-200
                                    bg-white
                                    px-4 py-3 text-sm
                                    text-slate-700
                                    focus:border-slate-400
                                    focus:ring-0
                                "
                                data-id="${item.id}"
                                value="${item.access_password ?? ''}"
                                placeholder="Password"
                            >
                        `
                    }

                </td>

                <td class="px-5 py-5 min-w-[180px]">

                    ${
                        isCompleted
                        ? `
                            <div class="
                                inline-flex h-10 items-center
                                justify-center rounded-xl
                                bg-emerald-50 px-4
                                text-xs font-semibold
                                text-emerald-700
                            ">
                                Completed
                            </div>
                        `
                        : `
                            <div class="
                                flex flex-col gap-2
                            ">

                                <button
                                    type="button"
                                    class="
                                        btn-save-hardware
                                        inline-flex h-10 w-full
                                        items-center justify-center
                                        rounded-xl border
                                        border-slate-200
                                        bg-white px-4
                                        text-xs font-semibold
                                        text-slate-700
                                        transition
                                        hover:bg-slate-100
                                    "
                                    data-doc="${access.eid}"
                                    data-id="${item.id}"
                                    data-type="${access.access_type ?? ''}"
                                >

                                    <i class="
                                        fa-solid fa-floppy-disk
                                        mr-2 text-[11px]
                                    "></i>

                                    Save

                                </button>

                                <button
                                    type="button"
                                    class="
                                        btn-complete-hardware
                                        inline-flex h-10 w-full
                                        items-center justify-center
                                        rounded-xl bg-slate-900
                                        px-4 text-xs
                                        font-semibold text-white
                                        transition
                                        hover:bg-slate-800
                                    "
                                    data-doc="${access.eid}"
                                    data-id="${item.id}"
                                    data-type="${access.access_type ?? ''}"
                                >

                                    <i class="
                                        fa-solid fa-check
                                        mr-2 text-[11px]
                                    "></i>

                                    Done

                                </button>

                            </div>
                        `
                    }

                </td>

            </tr>

        `;

    });

    if (!rows) {

        rows = `
            <tr>

                <td colspan="6"
                    class="
                        px-5 py-12 text-center
                        text-sm text-slate-500
                    ">

                    No hardware request available

                </td>

            </tr>
        `;

    }

    $('#processHardwareDetailContainer').html(`

        <div class="
            overflow-hidden rounded-xl
            border border-slate-200
            bg-white
        ">

            <div class="
                border-b border-slate-200
                px-5 py-4
            ">

                <h3 class="
                    text-sm font-bold uppercase
                    tracking-wider text-slate-700
                ">
                    Hardware Request Detail
                </h3>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider text-slate-500
                            ">
                                Access Item
                            </th>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider text-slate-500
                            ">
                                Status
                            </th>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider text-slate-500
                            ">
                                Response
                            </th>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider text-slate-500
                            ">
                                Username
                            </th>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider text-slate-500
                            ">
                                Password
                            </th>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider text-slate-500
                            ">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        ${rows}

                    </tbody>

                </table>

            </div>

        </div>

    `);

}

$(document).on('click', '.btn-save-hardware', async function () {

    const button = $(this);

    const hash = button.data('doc');

    const detailId = button.data('id');

    const response = $(`
        .hardware-response[data-id="${detailId}"]
    `).val()?.trim();

    const username = $(`
        .hardware-username[data-id="${detailId}"]
    `).val()?.trim();

    const password = $(`
        .hardware-password[data-id="${detailId}"]
    `).val()?.trim();

    await submitHardwareItem(
        button,
        hash,
        detailId,
        response,
        username,
        password,
        'SAVE'
    );

});

$(document).on('click', '.btn-complete-hardware', async function () {

    const button = $(this);

    const hash = button.data('doc');

    const detailId = button.data('id');

    const accessType = (
        button.data('type') ?? ''
    ).toUpperCase();

    const response = $(`
        .hardware-response[data-id="${detailId}"]
    `).val()?.trim();

    const username = $(`
        .hardware-username[data-id="${detailId}"]
    `).val()?.trim();

    const password = $(`
        .hardware-password[data-id="${detailId}"]
    `).val()?.trim();

    if (accessType === 'NEW') {

        if (!username) {

            swalWarning('Username is required');

            return;

        }

        if (!password) {

            swalWarning('Password is required');

            return;

        }

    }

    await submitHardwareItem(
        button,
        hash,
        detailId,
        response,
        username,
        password,
        'DONE'
    );

});

async function submitHardwareItem(
    button,
    hash,
    detailId,
    response,
    username,
    password,
    action
) {

    const originalHtml = button.html();

    button.prop('disabled', true);

    button.html(`
        <i class="fa-solid fa-spinner animate-spin text-xs"></i>
    `);

    try {

        const res = await $.ajax({

            url: `/access-request/process-hardware/${hash}`,

            type: 'POST',

            data: {

                details: [
                    {
                        id: detailId,

                        action: action,

                        access_response: response,

                        access_username: username,

                        access_password: password
                    }
                ]
            }

        });

        swalSuccess(
            res.message ??
            'Hardware access updated successfully'
        );

        await openProcessHardwareModal(hash);

        table.ajax.reload(null, false);

    } catch (xhr) {

        console.error(xhr);

        swalError(
            xhr.responseJSON?.message ??
            'Failed process hardware access'
        );

    } finally {

        button.prop('disabled', false);

        button.html(originalHtml);

    }

}
