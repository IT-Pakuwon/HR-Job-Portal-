const VplTransferDatalist = {

    table: null,

    init() {
        VplTransferDatalist.table = $('#transferTable').DataTable({
            processing:  true,
            serverSide:  false,
            ajax: {
                url:  VplTransfer.routes.base,
                type: 'GET',
                data: (d) => {
                    d.status = VplTransfer.state.currentStatus;
                    if (VplTransfer.state.currentStatus === 'ADMINALL') {
                        d.filter_vp_type = $('#f_vp_type').val() || '';
                        d.filter_doctype = $('#f_doctype').val() || '';
                        d.filter_doc_status = $('#f_doc_status').val() || 'ALL';
                    }
                },
            },
            columns: [
                { data: 'action',              name: 'action',            orderable: false, searchable: true , render: (data, type, row) => type === 'display' ? window.vplDocButton('btn-view-transfer', row.id, row.transfer_id) : row.transfer_id },
                { data: 'transfer_date_fmt', className: 'whitespace-nowrap',   name: 'transfer_date',     orderable: true },
                { data: 'cpnyid',              name: 'cpnyid' },
                { data: 'department',          name: 'department' },
                { data: 'vp_type_label',       name: 'vp_type', render: (data, type) => type === 'display' ? window.vplTypePill(data) : data },
                { data: 'transfertype_label',  name: 'transfertype' },
                { data: 'ref_transfer_id',     name: 'ref_transfer_id',   defaultContent: '-' },
                { data: 'transfer_remark',     name: 'transfer_remark',   defaultContent: '-' },
                { data: 'status_badge',        name: 'status',            orderable: false, searchable: true, render: (data, type, row) => type === 'display' ? window.vplStatusBadge(row.status) : window.vplStatusText(row.status) },
            ],
            order: [[1, 'desc']],
            pageLength: 10,
            responsive: true,
            columnDefs: [{ targets: 0, responsivePriority: 1 }, { targets: 8, responsivePriority: 2 }, { targets: 1, responsivePriority: 3 }],
            dom: '<"dt-toolbar"l f>rtip',
            createdRow(row) {
                $(row).addClass(
                    'border-b border-gray-100 hover:bg-gray-50/60 dark:border-white/[0.05] dark:hover:bg-white/[0.02] transition-colors duration-100'
                );
                $('td', row).addClass('px-4 py-3 text-sm text-gray-700 dark:text-gray-300');
            },
        });
    },

    initFilterButtons() {
        $(document).on('click', '.status-filter', function (e) {
            e.preventDefault();
            $('.status-filter').removeClass('active-card active');
            $(this).addClass('active-card active');
            const status = $(this).data('status');
            VplTransfer.state.currentStatus = status;
            $('#adminAllFilters')
                .toggleClass('hidden', status !== 'ADMINALL')
                .toggleClass('flex', status === 'ADMINALL');
            VplTransferDatalist.table?.ajax.reload(null, false);
        });

        // Type / Doctype / Status dropdowns only apply while "Transfer All" is active
        $(document).on('change', '#f_vp_type, #f_doc_status', function () {
            if (VplTransfer.state.currentStatus === 'ADMINALL') {
                VplTransferDatalist.table?.ajax.reload(null, false);
            }
        });
        $(document).on('select2:select select2:clear', '#f_doctype', function () {
            if (VplTransfer.state.currentStatus === 'ADMINALL') {
                VplTransferDatalist.table?.ajax.reload(null, false);
            }
        });
    },

    initRowClick() {
        $('#transferTable').on('click', '.btn-view-transfer', function () {
            const id = $(this).data('id');
            VplTransferDetailModal.open(id);
        });
    },

    refresh() {
        VplTransferDatalist.table?.ajax.reload(null, false);
    },
};
