// ============================================================
// datalist.js — Voucher Product Receive
// DataTable initialisation, filter pills, row-click → view modal
// ============================================================

const VplReceiveDatalist = {

    table: null,

    // --------------------------------------------------------
    // INIT DATATABLE
    // --------------------------------------------------------
    init() {
        VplReceiveDatalist.table = $('#receiveTable').DataTable({
            processing:  true,
            serverSide:  true,
            responsive:  true,
            order:       [[1, 'desc']],
            dom:         '<"dt-toolbar"l B f>rtip',
            buttons:     [
                {
                    extend: 'excelHtml5',
                    text: '↓ Excel',
                    title: 'Receive Product / Voucher',
                    exportOptions: {
                        columns: ':visible',
                        modifier: { page: 'current' },
                    },
                },
                {
                    extend: 'csvHtml5',
                    text: '↓ CSV',
                    title: 'Receive Product / Voucher',
                    exportOptions: {
                        columns: ':visible',
                        modifier: { page: 'current' },
                    },
                },
            ],
            lengthMenu:  [[10, 25, 50, -1], [10, 25, 50, 'All']],
            ajax: {
                url:     VplReceive.routes.base,
                headers: { 'X-CSRF-TOKEN': VplReceive.csrf() },
                data(d) { d.status = VplReceive.state.currentStatus; },
            },
            columns: [
                {
                    data: 'action', name: 'receive_id', orderable: false, searchable: false,
                },
                {
                    data: 'receive_date', name: 'receive_date',
                    render: (data) => `<span class="text-sm text-slate-700 dark:text-slate-300">${data || '—'}</span>`,
                },
                {
                    data: 'cpnyid', name: 'cpnyid',
                    render: (data) => `<span class="text-sm font-medium text-slate-700 dark:text-slate-300">${data || '—'}</span>`,
                },
                {
                    data: 'department', name: 'department',
                    render: (data) => `<span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">${data || '—'}</span>`,
                },
                {
                    data: 'vp_type', name: 'vp_type',
                    render: (data) => {
                        if (!data) return '<span class="text-slate-400">—</span>';
                        const label = data.charAt(0).toUpperCase() + data.slice(1).toLowerCase();
                        return `<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">${label}</span>`;
                    },
                },
                {
                    data: 'receive_type', name: 'receive_type',
                    render: (data) => `<span class="text-sm text-slate-600 dark:text-slate-400">${data || '—'}</span>`,
                },
                {
                    data: 'receive_remark', name: 'receive_remark',
                    render: (data) => `<span class="text-sm text-slate-500 dark:text-slate-400">${data || '—'}</span>`,
                },
                {
                    data: 'status_badge', name: 'status', orderable: false, searchable: false,
                    className: 'text-center',
                    render: d => `<div class="flex justify-center">${d}</div>`,
                },
            ],
            createdRow(row) {
                $(row).addClass(
                    'border-b border-gray-100 hover:bg-gray-50/60 ' +
                    'dark:border-white/[0.04] dark:hover:bg-white/[0.02]'
                );
            },
        });
    },

    // --------------------------------------------------------
    // RELOAD (after save / approve / cancel …)
    // --------------------------------------------------------
    refresh() {
        VplReceiveDatalist.table?.ajax.reload(null, false);
    },

    // --------------------------------------------------------
    // STATUS CARD FILTER
    // --------------------------------------------------------
    initFilterButtons() {
        $(document).on('click', '.status-filter', function (e) {
            e.preventDefault();
            VplReceive.state.currentStatus = $(this).data('status') || 'ALL';
            $('.status-filter').removeClass('active-card');
            $(this).addClass('active-card');
            VplReceiveDatalist.table?.ajax.reload(null, true);
        });
    },

    // --------------------------------------------------------
    // ROW CLICK → OPEN VIEW MODAL
    // --------------------------------------------------------
    initRowClick() {
        $(document).on('click', '.btn-view', function () {
            VplReceiveDetailModal.open($(this).data('id'));
        });
    },
};
