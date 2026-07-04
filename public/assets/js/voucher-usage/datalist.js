const VplUsageDatalist = {

    table: null,

    init() {
        VplUsageDatalist.table = $('#usageTable').DataTable({
            processing:  true,
            serverSide:  false,
            ajax: {
                url:  VplUsage.routes.base,
                type: 'GET',
                data: (d) => {
                    d.status = VplUsage.state.currentStatus;
                },
            },
            columns: [
                { data: 'action',            name: 'action',            orderable: false, searchable: false },
                { data: 'usage_date_fmt',    name: 'usage_date',        orderable: true },
                { data: 'cpnyid',            name: 'cpnyid' },
                { data: 'department',        name: 'department' },
                { data: 'vp_type',           name: 'vp_type' },
                { data: 'usagetype_label',   name: 'usagetype' },
                { data: 'ref_usage_id',      name: 'ref_usage_id',      defaultContent: '' },
                { data: 'usage_remark',      name: 'usage_remark',      defaultContent: '' },
                { data: 'status_badge',      name: 'status',            orderable: false, searchable: false },
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
            VplUsage.state.currentStatus = $(this).data('status');
            VplUsageDatalist.table?.ajax.reload(null, false);
        });
    },

    initRowClick() {
        $('#usageTable').on('click', '.btn-view-usage', function () {
            const id = $(this).data('id');
            VplUsageDetailModal.open(id);
        });
    },

    refresh() {
        VplUsageDatalist.table?.ajax.reload(null, false);
    },
};
