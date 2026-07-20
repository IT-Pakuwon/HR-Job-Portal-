const VplSettlementDatalist = {

    table: null,

    init() {
        VplSettlementDatalist.table = $('#settlementTable').DataTable({
            processing:  true,
            serverSide:  false,
            ajax: {
                url:  VplSettlement.routes.base,
                type: 'GET',
                data: (d) => {
                    d.status = VplSettlement.state.currentStatus;
                },
            },
            columns: [
                { data: 'action',                name: 'action',                orderable: false, searchable: false },
                { data: 'settlement_date_fmt',    name: 'settlement_date',       orderable: true },
                { data: 'cpnyid',                 name: 'cpnyid' },
                { data: 'department',             name: 'department' },
                { data: 'vp_type_label',          name: 'vp_type' },
                { data: 'usage_id',                name: 'usage_id' },
                { data: 'settlement_remark',      name: 'settlement_remark',     defaultContent: '-' },
                { data: 'status_badge',           name: 'status',                orderable: false, searchable: false },
            ],
            order: [[1, 'desc']],
            pageLength: 25,
            createdRow(row) {
                $(row).addClass(
                    'border-b border-gray-100 hover:bg-slate-50/60 dark:border-white/[0.05] dark:hover:bg-white/[0.02] transition-colors duration-100'
                );
                $('td', row).addClass('px-4 py-3 text-sm text-slate-700 dark:text-slate-300');
            },
        });
    },

    initFilterButtons() {
        $(document).on('click', '.status-filter', function (e) {
            e.preventDefault();
            $('.status-filter').removeClass('active-card');
            $(this).addClass('active-card');
            VplSettlement.state.currentStatus = $(this).data('status');
            VplSettlementDatalist.table?.ajax.reload(null, false);
        });
    },

    initRowClick() {
        $('#settlementTable').on('click', '.btn-view-settlement', function () {
            const id = $(this).data('id');
            VplSettlementDetailModal.open(id);
        });
    },

    refresh() {
        VplSettlementDatalist.table?.ajax.reload(null, false);
    },
};
