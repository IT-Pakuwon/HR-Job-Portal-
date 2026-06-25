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
                    className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                },
                {
                    extend: 'csvHtml5',
                    text: '↓ CSV',
                    title: 'Receive Product / Voucher',
                    className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                },
            ],
            lengthMenu:  [[10, 25, 50, -1], [10, 25, 50, 'All']],
            ajax: {
                url:     VplReceive.routes.base,
                headers: { 'X-CSRF-TOKEN': VplReceive.csrf() },
                data(d) { d.status = VplReceive.state.currentStatus; },
            },
            columns: [
                { data: 'action',         name: 'receive_id',     orderable: false, searchable: false },
                { data: 'receive_date',   name: 'receive_date' },
                { data: 'cpnyid',         name: 'cpnyid' },
                { data: 'receive_type',   name: 'receive_type' },
                { data: 'receive_remark', name: 'receive_remark' },
                { data: 'status_badge',   name: 'status', orderable: false, searchable: false },
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
