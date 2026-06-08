function initDataTable() {

    table = $('#assetTable').DataTable({

        processing: true,
        serverSide: true,
        searching:  true,

        responsive: {
            details: {
                type:   'column',
                target: 0,
            },
        },

        columnDefs: [
            {
                targets:   0,
                className: 'dtr-control',
                orderable: false,
                width:     '28px',
            },
        ],

        autoWidth:  false,
        pageLength: 10,

        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100],
        ],

        order: [[2, 'desc']],

        ajax: {
            url: window.amRoutes.json,
            data: function (d) {
                d.filter_status    = currentStatus;
                d.filter_inventory = $('#filterInventory').val();
            },
        },

        columns: [

            // 0 — responsive toggle
            {
                data:           null,
                defaultContent: '',
            },

            // 1 — STTB
            {
                data:      'receiptnbr',
                name:      'receiptnbr',
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap',
            },

            // 2 — Date
            {
                data:      'receipt_date_fmt',
                name:      'receiptdate',
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap',
            },

            // 3 — PO
            {
                data:      'ponbr',
                name:      'ponbr',
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap',
                render: function (data) { return data ?? '-'; },
            },

            // 4 — Vendor Name
            {
                data:      'vendorname',
                name:      'vendorname',
                className: 'px-4 py-3',
                render: function (data) {
                    return `<span class="text-sm text-slate-700 dark:text-slate-200">${data ?? '-'}</span>`;
                },
            },

            // 5 — Inventory Code
            {
                data:      'inventoryid',
                name:      'inventoryid',
                className: 'px-4 py-3 text-xs font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap',
                render: function (data) { return data ?? '-'; },
            },

            // 6 — Inventory Name (word wrap)
            {
                data:      'inventory_descr',
                name:      'inventory_descr',
                orderable: false,
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300',
                render: function (data) {
                    return `<span style="max-width:220px;display:block">${data ?? '-'}</span>`;
                },
            },

            // 7 — Unit # (which unit of the total qty, e.g. "2 / 5")
            {
                data:      'unit_label',
                name:      'unit_num',
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap text-center',
            },

            // 8 — Unit Cost
            {
                data:      'unitcost_fmt',
                name:      'unitcost',
                orderable: false,
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap text-right',
            },

            // 9 — Assigned To
            {
                data:      'assign_info',
                orderable: false,
                className: 'px-4 py-3 text-xs',
                render: function (data) {
                    if (!data) {
                        return `<span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 dark:bg-white/10 px-3 py-1 text-xs font-medium text-slate-400 dark:text-slate-500">Not assigned</span>`;
                    }
                    return data;
                },
            },

            // 10 — Warranty
            {
                data:      'warranty_info',
                orderable: false,
                className: 'px-4 py-3',
                render: function (data) { return data || ''; },
            },

            // 11 — Action
            {
                data:      'action',
                orderable: false,
                className: 'px-4 py-3 text-center',
                render: function (data) { return data || ''; },
            },
        ],

        drawCallback: function () {
            bindTableActions();
        },
    });
}

function amLoadInventories() {
    $.get(window.amRoutes.inventories, function (data) {
        let opts = '<option value="">All Inventories</option>';
        data.forEach(function (item) {
            const label = item.inventoryid + (item.inventory_descr ? ' — ' + item.inventory_descr : '');
            opts += `<option value="${item.inventoryid}">${label}</option>`;
        });
        $('#filterInventory').html(opts);

        // Initialize Select2 after options are loaded
        if ($.fn.select2) {
            $('#filterInventory').select2({
                placeholder:  'All Inventories',
                allowClear:   true,
                width:        '320px',
            });
        }
    });

    // Reload table on selection change (works with both native and Select2)
    $('#filterInventory').on('change', function () {
        if (table) table.ajax.reload();
    });
}

function initFilters() {
    $(document).on('click', '.status-filter', function (e) {
        e.preventDefault();

        $('.status-filter').removeClass('active');
        $(this).addClass('active');

        currentStatus = $(this).data('status');

        table.ajax.reload();
    });
}

function bindTableActions() {

    $('.assign-btn').off('click').on('click', function () {
        try {
            const d = JSON.parse($(this).attr('data-receipt'));
            amOpenAssign(d);
        } catch (err) {
            console.error('Failed to parse assign data:', err);
            amSwalError('Could not read item data. Please refresh the page.');
        }
    });

    $('.edit-btn').off('click').on('click', function () {
        const id = $(this).data('id');
        amOpenEdit(id);
    });
}
