async function openProcessSoftwareModal(id) {

    openModal('#processSoftwareModal');

    renderProcessSoftwareLoading();

    try {

        const res = await fetchProcessSoftwareDetail(id);

        const access = res.access ?? {};

        const details = (res.details ?? []).filter(item => {
            return item.group_category === 'SOFTWARE';
        });

        renderProcessSoftwareInfo(access);

        renderProcessSoftwareItems(access, details);

    } catch (xhr) {

        console.error(xhr);

        renderProcessSoftwareError(xhr);

    }

}

async function fetchProcessSoftwareDetail(id) {

    return $.ajax({
        url: `/access-request/detail/${id}`,
        type: 'GET'
    });

}

function renderProcessSoftwareLoading() {

    const loader = `
        <div class="
            flex items-center justify-center

            rounded-lg

            border border-slate-200
            dark:border-white/[0.06]

            bg-white
            dark:bg-[#111c2d]

            py-20
        ">

            <div class="
                h-10 w-10 animate-spin rounded-lg border-4

                border-slate-300
                border-t-slate-700

                dark:border-slate-700
                dark:border-t-slate-200
            "></div>

        </div>
    `;

    $('#processSoftwareInfoContainer').html(loader);

    $('#processSoftwareDetailContainer').html(loader);

    $('#processSoftwareActionContainer').html('');
}

function renderProcessSoftwareError(xhr) {

    const message =
        xhr.responseJSON?.message ??
        'Failed load software process';

    const html = `
        <div class="
            rounded-lg

            border border-red-200
            dark:border-red-500/20

            bg-red-50
            dark:bg-red-500/10

            px-6 py-10

            text-center
        ">

            <div class="
                mx-auto flex h-14 w-14
                items-center justify-center

                rounded-lg

                bg-red-100
                dark:bg-red-500/20

                text-red-500
                dark:text-red-300
            ">

                <i class="
                    fa-solid fa-circle-exclamation
                    text-lg
                "></i>

            </div>

            <p class="
                mt-4 text-sm font-semibold

                text-red-700
                dark:text-red-300
            ">

                ${message}

            </p>

        </div>
    `;

    $('#processSoftwareInfoContainer').html(html);

    $('#processSoftwareDetailContainer').html('');

    $('#processSoftwareActionContainer').html('');
}

function renderProcessSoftwareInfo(access) {

    $('#processSoftwareInfoContainer').html(`

        <div class="
            rounded-lg

            border border-slate-200
            dark:border-white/[0.06]

            bg-white
            dark:bg-[#111c2d]

            overflow-hidden
        ">

            <div class="
                border-b border-slate-200
                dark:border-white/[0.06]

                px-5 py-4
            ">

                <h3 class="
                    text-sm font-bold uppercase
                    tracking-wider

                    text-slate-700
                    dark:text-slate-200
                ">

                    Request Information

                </h3>

            </div>

            <div class="
                grid grid-cols-1
                gap-x-10 gap-y-4

                p-5 sm:grid-cols-2
            ">

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]

                        text-xs font-semibold uppercase
                        tracking-wider

                        text-slate-400
                        dark:text-slate-500
                    ">

                        Document

                    </p>

                    <span class="
                        text-slate-300
                        dark:text-slate-600
                    ">
                        :
                    </span>

                    <p class="
                        text-sm font-semibold

                        text-slate-700
                        dark:text-slate-100
                    ">

                        ${access.docid ?? '-'}

                    </p>

                </div>

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]

                        text-xs font-semibold uppercase
                        tracking-wider

                        text-slate-400
                        dark:text-slate-500
                    ">

                        Requester

                    </p>

                    <span class="
                        text-slate-300
                        dark:text-slate-600
                    ">
                        :
                    </span>

                    <p class="
                        text-sm font-semibold

                        text-slate-700
                        dark:text-slate-100
                    ">

                        ${access.user_peminta ?? '-'}

                    </p>

                </div>

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]

                        text-xs font-semibold uppercase
                        tracking-wider

                        text-slate-400
                        dark:text-slate-500
                    ">

                        Company

                    </p>

                    <span class="
                        text-slate-300
                        dark:text-slate-600
                    ">
                        :
                    </span>

                    <p class="
                        text-sm font-semibold

                        text-slate-700
                        dark:text-slate-100
                    ">

                        ${access.cpny_id ?? '-'}

                    </p>

                </div>

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]

                        text-xs font-semibold uppercase
                        tracking-wider

                        text-slate-400
                        dark:text-slate-500
                    ">

                        Department

                    </p>

                    <span class="
                        text-slate-300
                        dark:text-slate-600
                    ">
                        :
                    </span>

                    <p class="
                        text-sm font-semibold

                        text-slate-700
                        dark:text-slate-100
                    ">

                        ${access.department_id ?? '-'}

                    </p>

                </div>

            </div>

        </div>

    `);
}

function renderProcessSoftwareItems(access, details = []) {

    const canViewPassword =
        access.can_view_password === true;

    let rows = '';

    details.forEach(item => {

        const isCompleted =
            item.status === 'C';

        rows += `

            <tr class="
                border-b border-slate-100
                dark:border-white/[0.06]
                align-top
            ">

                <td class="px-5 py-5 min-w-[280px]"

                    <div class="space-y-2">

                        <div class="
                            text-sm font-semibold

                            text-slate-700
                            dark:text-slate-100
                        ">

                            ${item.access_descr ?? '-'}

                        </div>

                        ${
                            item.access_pic
                            ? `
                                <div class="
                                    inline-flex items-center gap-2

                                    rounded-lg

                                    bg-slate-100
                                    dark:bg-white/[0.06]

                                    px-3 py-1

                                    text-[11px] font-medium

                                    text-slate-600
                                    dark:text-slate-300
                                ">

                                    <i class="
                                        fa-solid fa-user text-[10px]
                                    "></i>

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

                                rounded-lg

                                bg-emerald-100
                                dark:bg-emerald-500/15

                                px-3 py-1.5

                                text-xs font-semibold

                                text-emerald-700
                                dark:text-emerald-300

                                whitespace-nowrap
                            ">

                                <i class="
                                    fa-solid fa-circle-check text-[11px]
                                "></i>

                                Completed

                            </span>
                        `
                        : `
                            <span class="
                                inline-flex items-center gap-1.5

                                rounded-lg

                                bg-blue-100
                                dark:bg-blue-500/15

                                px-3 py-1.5

                                text-xs font-semibold

                                text-blue-700
                                dark:text-blue-300

                                whitespace-nowrap
                            ">

                                <i class="
                                    fa-solid fa-clock text-[11px]
                                "></i>

                                Pending

                            </span>
                        `
                    }

                </td>

                <td class="px-5 py-5 min-w-[280px]">

                    ${
                        isCompleted
                        ? `
                            <div class="
                                rounded-lg

                                border border-slate-200
                                dark:border-white/[0.06]

                                bg-slate-50
                                dark:bg-white/[0.03]

                                px-4 py-3
                            ">

                                <div class="
                                    whitespace-normal break-words

                                    text-sm leading-relaxed

                                    text-slate-700
                                    dark:text-slate-200
                                ">

                                    ${item.access_response ?? '-'}

                                </div>

                            </div>
                        `
                        : `
                            <textarea
                                rows="3"
                                class="
                                    software-response

                                    w-full rounded-lg

                                    border border-slate-200
                                    dark:border-white/[0.06]

                                    bg-white
                                    dark:bg-[#0b1525]

                                    px-4 py-3

                                    text-sm

                                    text-slate-700
                                    dark:text-slate-100

                                    placeholder:text-slate-400
                                    dark:placeholder:text-slate-500

                                    focus:border-blue-500/30
                                    dark:focus:border-blue-500/30

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
                                rounded-lg

                                border border-slate-200
                                dark:border-white/[0.06]

                                bg-slate-50
                                dark:bg-white/[0.03]

                                px-4 py-3
                            ">

                                <div class="
                                    break-all

                                    text-sm

                                    text-slate-700
                                    dark:text-slate-200
                                ">

                                    ${item.access_username ?? '-'}

                                </div>

                            </div>
                        `
                        : `
                            <input
                                type="text"
                                class="
                                    software-username

                                    w-full rounded-lg

                                    border border-slate-200
                                    dark:border-white/[0.06]

                                    bg-white
                                    dark:bg-[#0b1525]

                                    px-4 py-3

                                    text-sm

                                    text-slate-700
                                    dark:text-slate-100

                                    placeholder:text-slate-400
                                    dark:placeholder:text-slate-500

                                    focus:border-blue-500/30
                                    dark:focus:border-blue-500/30

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
                                rounded-lg

                                border border-slate-200
                                dark:border-white/[0.06]

                                bg-slate-50
                                dark:bg-white/[0.03]

                                px-4 py-3
                            ">

                                <div class="
                                    break-all

                                    text-sm

                                    text-slate-700
                                    dark:text-slate-200
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
                                    software-password

                                    w-full rounded-lg

                                    border border-slate-200
                                    dark:border-white/[0.06]

                                    bg-white
                                    dark:bg-[#0b1525]

                                    px-4 py-3

                                    text-sm

                                    text-slate-700
                                    dark:text-slate-100

                                    placeholder:text-slate-400
                                    dark:placeholder:text-slate-500

                                    focus:border-blue-500/30
                                    dark:focus:border-blue-500/30

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
                                justify-center

                                rounded-lg

                                bg-emerald-100
                                dark:bg-emerald-500/15

                                px-4

                                text-xs font-semibold

                                text-emerald-700
                                dark:text-emerald-300
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
                                        btn-save-software

                                        inline-flex h-10 w-full
                                        items-center justify-center

                                        rounded-lg

                                        border border-slate-200
                                        dark:border-white/[0.06]

                                        bg-white
                                        dark:bg-[#0b1525]

                                        px-4

                                        text-xs font-semibold

                                        text-slate-700
                                        dark:text-slate-200

                                        transition

                                        hover:bg-slate-100
                                        dark:hover:bg-white/[0.06]
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
                                        btn-complete-software

                                        inline-flex h-10 w-full
                                        items-center justify-center

                                        rounded-lg

                                        bg-slate-900
                                        dark:bg-white

                                        px-4

                                        text-xs font-semibold

                                        text-white
                                        dark:text-slate-900

                                        transition

                                        hover:bg-slate-800
                                        dark:hover:bg-slate-200
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

                <td
                    colspan="6"
                    class="
                        px-5 py-12
                        text-center

                        text-sm

                        text-slate-500
                        dark:text-slate-400
                    "
                >

                    No software request available

                </td>

            </tr>
        `;
    }

    $('#processSoftwareDetailContainer').html(`

        <div class="
            overflow-hidden rounded-lg

            border border-slate-200
            dark:border-white/[0.06]

            bg-white
            dark:bg-[#111c2d]
        ">

            <div class="
                border-b border-slate-200
                dark:border-white/[0.06]

                px-5 py-4
            ">

                <h3 class="
                    text-sm font-bold uppercase
                    tracking-wider

                    text-slate-700
                    dark:text-slate-200
                ">

                    Software Request Detail

                </h3>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="
                        bg-slate-50
                        dark:bg-white/[0.03]
                    ">

                        <tr>

                            <th class="
                                px-5 py-3 text-left

                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-400
                            ">
                                Access Item
                            </th>

                            <th class="
                                px-5 py-3 text-left

                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-400
                            ">
                                Status
                            </th>

                            <th class="
                                px-5 py-3 text-left

                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-400
                            ">
                                Response
                            </th>

                            <th class="
                                px-5 py-3 text-left

                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-400
                            ">
                                Username
                            </th>

                            <th class="
                                px-5 py-3 text-left

                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-400
                            ">
                                Password
                            </th>

                            <th class="
                                px-5 py-3 text-left

                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-400
                            ">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody class="
                        divide-y divide-slate-100
                        dark:divide-white/[0.06]
                    ">

                        ${rows}

                    </tbody>

                </table>

            </div>

        </div>

    `);
}

$(document).on('click', '.btn-save-software', async function () {

    const button = $(this);

    const hash = button.data('doc');

    const detailId = button.data('id');

    const response = $(`
        .software-response[data-id="${detailId}"]
    `).val()?.trim();

    const username = $(`
        .software-username[data-id="${detailId}"]
    `).val()?.trim();

    const password = $(`
        .software-password[data-id="${detailId}"]
    `).val()?.trim();

    await submitSoftwareItem(
        button,
        hash,
        detailId,
        response,
        username,
        password,
        'SAVE'
    );

});

$(document).on('click', '.btn-complete-software', async function () {

    const button = $(this);

    const hash = button.data('doc');

    const detailId = button.data('id');

    const accessType = (
        button.data('type') ?? ''
    ).toUpperCase();

    const response = $(`
        .software-response[data-id="${detailId}"]
    `).val()?.trim();

    const username = $(`
        .software-username[data-id="${detailId}"]
    `).val()?.trim();

    const password = $(`
        .software-password[data-id="${detailId}"]
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

    await submitSoftwareItem(
        button,
        hash,
        detailId,
        response,
        username,
        password,
        'DONE'
    );

});

async function submitSoftwareItem(
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

            url: `/access-request/process-software/${hash}`,

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
            'Software access updated successfully'
        );

        window.location.href =
            `/processsoftwareaccess/${hash}`;

        table.ajax.reload(null, false);

    } catch (xhr) {

        console.error(xhr);

        swalError(
            xhr.responseJSON?.message ??
            'Failed process software access'
        );

    } finally {

        button.prop('disabled', false);

        button.html(originalHtml);

    }

}

$(document).on('click', '.btn-save-software', async function () {

    const button = $(this);

    const hash = button.data('doc');

    const detailId = button.data('id');

    const response = $(`
        .software-response[data-id="${detailId}"]
    `).val()?.trim();

    const username = $(`
        .software-username[data-id="${detailId}"]
    `).val()?.trim();

    const password = $(`
        .software-password[data-id="${detailId}"]
    `).val()?.trim();

    await submitSoftwareItem(
        button,
        hash,
        detailId,
        response,
        username,
        password,
        'SAVE'
    );

});

$(document).on('click', '.btn-complete-software', async function () {

    const button = $(this);

    const hash = button.data('doc');

    const detailId = button.data('id');

    const accessType = (
        button.data('type') ?? ''
    ).toUpperCase();

    const response = $(`
        .software-response[data-id="${detailId}"]
    `).val()?.trim();

    const username = $(`
        .software-username[data-id="${detailId}"]
    `).val()?.trim();

    const password = $(`
        .software-password[data-id="${detailId}"]
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

    await submitSoftwareItem(
        button,
        hash,
        detailId,
        response,
        username,
        password,
        'DONE'
    );

});

async function submitSoftwareItem(
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

            url: `/access-request/process-software/${hash}`,

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
            'Software access updated successfully'
        );

        await openProcessSoftwareModal(hash);

        table.ajax.reload(null, false);

    } catch (xhr) {

        console.error(xhr);

        swalError(
            xhr.responseJSON?.message ??
            'Failed process software access'
        );

    } finally {

        button.prop('disabled', false);

        button.html(originalHtml);

    }

}
