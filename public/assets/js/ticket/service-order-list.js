let soTable = null;
let soSearchTimeout;

function openSOListModal() {
    const $modal = $('#soListModal');
    $modal.removeClass('hidden').addClass('flex');

    requestAnimationFrame(() => {
        $modal.find('.modal-backdrop').addClass('opacity-100').removeClass('opacity-0');
        $modal.find('.modal-panel')
            .addClass('opacity-100 translate-y-0 scale-100')
            .removeClass('opacity-0 translate-y-4 scale-[0.98]');
    });

    if (!soTable) {
        initSOTable();
    } else {
        soTable.ajax.reload();
    }
}

function closeSOListModal() {
    const $modal = $('#soListModal');
    $modal.find('.modal-backdrop').removeClass('opacity-100').addClass('opacity-0');
    $modal.find('.modal-panel')
        .removeClass('opacity-100 translate-y-0 scale-100')
        .addClass('opacity-0 translate-y-4 scale-[0.98]');

    setTimeout(() => {
        $modal.removeClass('flex').addClass('hidden');
    }, 200);
}

function initSOTable() {
    soTable = $('#soListTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],
        dom: 'lrtip',
        order: [[1, 'desc']],

        ajax: {
            url: window.ticketRoutes.serviceOrdersJson,
            data: function (d) {
                d.search_so      = $('#so_filter_search').val();
                d.so_job_status  = $('#so_filter_job_status').val();
                d.so_date_from   = $('#so_filter_date_from').val();
                d.so_date_to     = $('#so_filter_date_to').val();
            },
        },

        columns: [
            {
                data: 'serviceorderid',
                name: 'serviceorderid',
                className: 'px-4 py-3 whitespace-nowrap align-middle font-mono text-xs',
                render: function (data) {
                    return data
                        ? `<span class="font-semibold text-slate-700 dark:text-slate-200">${data}</span>`
                        : '<span class="text-gray-400">-</span>';
                },
            },
            {
                data: 'serviceorderdate',
                name: 'serviceorderdate',
                className: 'px-4 py-3 whitespace-nowrap align-middle',
                render: function (data) {
                    return data ? formatDate(data) : '<span class="text-gray-400">-</span>';
                },
            },
            {
                data: 'ticketid',
                name: 'ticketid',
                className: 'px-4 py-3 whitespace-nowrap align-middle',
                render: function (data) {
                    if (!data) return '<span class="text-gray-400">-</span>';
                    return `<span class="inline-flex items-center gap-2 whitespace-nowrap rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold tabular-nums text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-regular fa-file-lines opacity-80"></i>${data}</span>`;
                },
            },
            {
                data: 'user_pic',
                name: 'user_pic',
                className: 'px-4 py-3 whitespace-nowrap align-middle',
                render: function (data) {
                    if (!data) return '<span class="text-gray-400">-</span>';
                    return `
                        <div class="flex items-center gap-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-teal-100 text-xs font-semibold uppercase text-teal-700 dark:bg-teal-900/30 dark:text-teal-300">
                                ${data.charAt(0)}
                            </div>
                            <span class="text-sm text-gray-700 dark:text-gray-200">${data}</span>
                        </div>`;
                },
            },
            {
                data: 'job_type',
                name: 'job_type',
                className: 'px-4 py-3 whitespace-nowrap align-middle',
                render: function (data) {
                    return data
                        ? `<span class="inline-flex whitespace-nowrap rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">${data}</span>`
                        : '<span class="text-gray-400">-</span>';
                },
            },
            {
                data: 'serviceorder_descr',
                name: 'serviceorder_descr',
                className: 'px-4 py-3 align-middle',
                render: function (data) {
                    if (!data) return '<span class="text-gray-400">-</span>';
                    return `<div class="max-w-[200px] text-sm leading-relaxed text-gray-700 dark:text-gray-200 line-clamp-3">${data}</div>`;
                },
            },
            {
                data: 'serviceorder_action',
                name: 'serviceorder_action',
                className: 'px-4 py-3 align-middle',
                render: function (data) {
                    if (!data) return '<span class="text-gray-400">-</span>';
                    return `<div class="max-w-[200px] text-sm leading-relaxed text-gray-700 dark:text-gray-200 line-clamp-3">${data}</div>`;
                },
            },
            {
                data: 'job_status',
                name: 'job_status',
                className: 'px-4 py-3 whitespace-nowrap align-middle',
                render: function (data) {
                    return renderSOJobStatus(data);
                },
            },
            {
                data: 'completed_at',
                name: 'completed_at',
                className: 'px-4 py-3 whitespace-nowrap align-middle',
                render: function (data) {
                    if (!data) return '<span class="text-gray-400">-</span>';
                    return `<div class="flex flex-col leading-tight">
                        <span class="text-sm text-gray-700 dark:text-gray-200">${formatDate(data)}</span>
                        <span class="text-[11px] text-gray-400">${formatTime(data)}</span>
                    </div>`;
                },
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'px-4 py-3 text-center whitespace-nowrap align-middle',
                render: function (data, type, row) {
                    if (row.status === 'X') {
                        return listBadge("red", "Non Aktif");
                    }
                    return `
                        <button type="button"
                            class="btn-so-non-aktif ${listCls.action.red}"
                            data-id="${row.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Non Aktif
                        </button>`;
                },
            },
        ],

        drawCallback: function () {
            $('#soListTable tbody tr').addClass(
                'transition duration-150 hover:bg-gray-50 dark:hover:bg-gray-800/40'
            );
        },
    });
}

function renderSOJobStatus(status) {
    const map = { O: ['blue', 'Open'], C: ['green', 'Completed'], X: ['red', 'Cancelled'] };
    const item = map[(status || '').toUpperCase()] || ['slate', status || '-'];

    return listBadge(item[0], item[1]);
}

// ── Event listeners ──────────────────────────────────────────────────────────

$(document).on('click', '#btn_open_so_list', openSOListModal);
$(document).on('click', '#btn_close_so_list', closeSOListModal);

$(document).on('click', '#soListModal .modal-backdrop', closeSOListModal);

$(document).on('keyup', '#so_filter_search', function () {
    clearTimeout(soSearchTimeout);
    soSearchTimeout = setTimeout(function () {
        if (soTable) soTable.ajax.reload();
    }, 500);
});

$(document).on('change', '#so_filter_job_status, #so_filter_date_from, #so_filter_date_to', function () {
    if (soTable) soTable.ajax.reload();
});

$(document).on('click', '.btn-so-non-aktif', function () {
    const id  = $(this).data('id');
    const url = window.ticketRoutes.serviceOrderNonAktif.replace(':id', id);

    Swal.fire({
        icon: 'warning',
        title: 'Non Aktif Service Order?',
        text: 'Status will be set to non-active (X). This cannot be undone.',
        showCancelButton: true,
        confirmButtonText: 'Yes, Non Aktif',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        confirmButtonColor: '#dc2626',
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function () {
                showSuccess('Service order set to non-active.');
                if (soTable) soTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                handleAjaxError(xhr);
            },
        });
    });
});
