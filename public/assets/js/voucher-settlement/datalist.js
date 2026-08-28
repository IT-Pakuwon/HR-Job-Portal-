const VplSettlementDatalist = {

    table: null,
    jobTable: null,

    /** Lazy — the Settlement list is no longer the default landing tab (Job List is), so it's created on first visit. */
    initSettlementTable() {
        if (VplSettlementDatalist.table) return;
        VplSettlementDatalist.table = $('#settlementTable').DataTable({
            processing:  true,
            serverSide:  false,
            ajax: {
                url:  VplSettlement.routes.base,
                type: 'GET',
                data: (d) => {
                    d.status = VplSettlement.state.currentStatus;
                    if (VplSettlement.state.currentStatus === 'ADMINALL') {
                        d.filter_vp_type = $('#f_vp_type').val() || '';
                        d.filter_doc_status = $('#f_doc_status').val() || 'ALL';
                    }
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
            pageLength: 10,
            createdRow(row) {
                $(row).addClass(
                    'border-b border-gray-100 hover:bg-slate-50/60 dark:border-white/[0.05] dark:hover:bg-white/[0.02] transition-colors duration-100'
                );
                $('td', row).addClass('px-4 py-3 text-sm text-slate-700 dark:text-slate-300');
            },
        });
    },

    /** Lazy — the Job List tab is created on first visit so it doesn't fetch data nobody asked for on page load. */
    initJobListTable() {
        if (VplSettlementDatalist.jobTable) return;
        VplSettlementDatalist.jobTable = $('#jobListTable').DataTable({
            processing:  true,
            serverSide:  false,
            ajax: { url: VplSettlement.routes.jobList, type: 'GET' },
            columns: [
                { data: 'action',         name: 'action',        orderable: false, searchable: false, width: '100px' },
                { data: 'usage_id',       name: 'usage_id' },
                { data: 'usage_date_fmt', name: 'usage_date' },
                { data: 'event_date_fmt', name: 'event_date', defaultContent: '-' },
                { data: 'cpnyid',         name: 'cpnyid' },
                { data: 'department',     name: 'department' },
                { data: 'vp_type_label',  name: 'vp_type' },
                { data: 'usage_remark',   name: 'usage_remark',  defaultContent: '-' },
            ],
            autoWidth: false,
            order: [[2, 'desc']],
            pageLength: 10,
            createdRow(row) {
                $(row).addClass(
                    'border-b border-gray-100 hover:bg-slate-50/60 dark:border-white/[0.05] dark:hover:bg-white/[0.02] transition-colors duration-100'
                );
                $('td', row).addClass('px-4 py-3 text-sm text-slate-700 dark:text-slate-300');
            },
        });
    },

    /** Shared by the tab click handler and the initial page load (Job List is now the default landing tab). */
    activateTab(status) {
        VplSettlement.state.currentStatus = status;
        $('#adminAllFilters')
            .toggleClass('hidden', status !== 'ADMINALL')
            .toggleClass('flex', status === 'ADMINALL');

        const isJobList = status === 'JOBLIST';
        $('#settlementTableWrap').toggleClass('hidden', isJobList);
        $('#jobListTableWrap').toggleClass('hidden', !isJobList);
        $('#panelTitle').text(isJobList ? 'Job List — Pending Settlements' : 'Settlement Product / Voucher');
        $('#jobListSubtitle').toggleClass('hidden', !isJobList);

        if (isJobList) {
            const alreadyInit = !!VplSettlementDatalist.jobTable;
            VplSettlementDatalist.initJobListTable();
            if (alreadyInit) VplSettlementDatalist.jobTable.ajax.reload(null, false);
        } else {
            const alreadyInit = !!VplSettlementDatalist.table;
            VplSettlementDatalist.initSettlementTable();
            if (alreadyInit) VplSettlementDatalist.table.ajax.reload(null, false);
        }
    },

    initFilterButtons() {
        $(document).on('click', '.status-filter', function (e) {
            e.preventDefault();
            $('.status-filter').removeClass('active-card');
            $(this).addClass('active-card');
            VplSettlementDatalist.activateTab($(this).data('status'));
        });

        // Type / Status dropdowns only apply while "Settlement All" is active
        $(document).on('change', '#f_vp_type, #f_doc_status', function () {
            if (VplSettlement.state.currentStatus === 'ADMINALL') {
                VplSettlementDatalist.table?.ajax.reload(null, false);
            }
        });
    },

    initRowClick() {
        $('#settlementTable').on('click', '.btn-view-settlement', function () {
            const id = $(this).data('id');
            VplSettlementDetailModal.open(id);
        });

        $('#jobListTable').on('click', '.btn-create-from-job', function () {
            VplSettlementForm.openCreateFromJob({
                usageId:    $(this).data('usageId'),
                cpnyid:     $(this).data('cpnyid'),
                department: $(this).data('department'),
                vpType:     $(this).data('vpType'),
            });
        });
    },

    refresh() {
        VplSettlementDatalist.table?.ajax.reload(null, false);
        VplSettlementDatalist.jobTable?.ajax.reload(null, false);
    },
};
