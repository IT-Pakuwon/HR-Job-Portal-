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
            responsive: true,
            columnDefs: [{ targets: 0, responsivePriority: 1 }, { targets: 7, responsivePriority: 2 }, { targets: 1, responsivePriority: 3 }],
            order:       [[1, 'desc']],
            dom:         '<"dt-toolbar"l B f>rtip',
            buttons:     [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa-solid fa-file-excel"></i> Excel',
                    title: 'Receive Product / Voucher',
                    exportOptions: {
                        columns: ':visible',
                        modifier: { page: 'current' },
                    },
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
                data(d) {
                    d.status = VplReceive.state.currentStatus;
                    if (VplReceive.state.currentStatus === 'ADMINALL') {
                        d.filter_vp_type = $('#f_vp_type').val() || '';
                        d.filter_doc_status = $('#f_doc_status').val() || 'ALL';
                    }
                },
            },
            columns: [
                {
                    data: 'action', name: 'receive_id', orderable: false, searchable: true,
                    render: (data, type, row) => type === 'display' ? window.vplDocButton('btn-view', row.id, row.receive_id) : row.receive_id,
                },
                {
                    data: 'receive_date', name: 'receive_date', className: 'whitespace-nowrap',
                    render: (data) => `<span class="text-sm text-gray-700 dark:text-gray-300">${data || '—'}</span>`,
                },
                {
                    data: 'cpnyid', name: 'cpnyid',
                    render: (data) => `<span class="text-sm font-medium text-gray-700 dark:text-gray-300">${data || '—'}</span>`,
                },
                {
                    data: 'department', name: 'department',
                    render: (data) => `<span class="text-sm font-medium text-blue-600 dark:text-blue-400">${data || '—'}</span>`,
                },
                {
                    data: 'vp_type', name: 'vp_type',
                    render: (data, type) => type === 'display' ? window.vplTypePill(data) : data,
                },
                {
                    data: 'receive_type', name: 'receive_type',
                    render: (data) => `<span class="text-sm text-gray-600 dark:text-gray-400">${data || '—'}</span>`,
                },
                {
                    data: 'receive_remark', name: 'receive_remark',
                    render: (data) => `<span class="text-sm text-gray-500 dark:text-gray-400">${data || '—'}</span>`,
                },
                {
                    data: 'status_badge', name: 'status', orderable: false, searchable: false,
                    render: (data, type, row) => type === 'display' ? window.vplStatusBadge(row.status) : window.vplStatusText(row.status),
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
            const status = $(this).data('status') || 'ALL';
            VplReceive.state.currentStatus = status;
            $('.status-filter').removeClass('active-card active');
            $(this).addClass('active-card active');
            $('#adminAllFilters')
                .toggleClass('hidden', status !== 'ADMINALL')
                .toggleClass('flex', status === 'ADMINALL');
            VplReceiveDatalist.table?.ajax.reload(null, true);
        });

        // Type / Status dropdowns only apply while "Receive All" is active
        $(document).on('change', '#f_vp_type, #f_doc_status', function () {
            if (VplReceive.state.currentStatus === 'ADMINALL') {
                VplReceiveDatalist.table?.ajax.reload(null, true);
            }
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
