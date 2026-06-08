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
                d.filter_sttb      = $('#filterSttb').val();
                d.filter_po        = $('#filterPo').val();
                d.filter_company   = $('#filterCompany').val();
                d.filter_dept      = $('#filterDept').val();
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

            // 9 — Company (from receipt)
            {
                data:      'cpny_id',
                name:      'cpny_id',
                className: 'px-4 py-3 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap',
                render: function (data) {
                    return data || '<span class="text-slate-300 dark:text-slate-600">—</span>';
                },
            },

            // 10 — Department (from receipt)
            {
                data:      'department_id',
                name:      'department_id',
                className: 'px-4 py-3 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap',
                render: function (data) {
                    return data || '<span class="text-slate-300 dark:text-slate-600">—</span>';
                },
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

        if ($.fn.select2) {
            $('#filterInventory').select2({ placeholder: 'All Inventories', allowClear: true, width: '100%' });
        }
    });

    $('#filterInventory').on('change', function () {
        if (table) table.ajax.reload();
    });
}

function amLoadFilterCompanies() {
    $.get(window.amRoutes.companies, function (data) {
        let opts = '<option value="">All Companies</option>';
        data.forEach(function (c) {
            opts += `<option value="${c.cpny_id}">${c.cpny_id} — ${c.cpny_name}</option>`;
        });
        $('#filterCompany').html(opts);
    });

    $('#filterCompany').on('change', function () {
        amLoadFilterDepartments($(this).val());
        if (table) table.ajax.reload();
    });
}

function amLoadFilterDepartments(cpnyId) {
    const params = cpnyId ? { cpny_id: cpnyId } : {};
    $.get(window.amRoutes.departments, params, function (data) {
        let opts = '<option value="">All Departments</option>';
        data.forEach(function (d) {
            opts += `<option value="${d.department_id}">${d.department_name}</option>`;
        });
        $('#filterDept').html(opts);
    });

    $('#filterDept').off('change.filter').on('change.filter', function () {
        if (table) table.ajax.reload();
    });
}

function initFilterButtons() {

    $('#applyFilter').on('click', function () {
        if (table) table.ajax.reload();
    });

    $('#resetFilter').on('click', function () {
        $('#filterSttb').val('');
        $('#filterPo').val('');
        $('#filterCompany').val('');
        $('#filterDept').val('');

        if ($.fn.select2 && $('#filterInventory').data('select2')) {
            $('#filterInventory').val(null).trigger('change');
        } else {
            $('#filterInventory').val('');
        }

        amLoadFilterDepartments('');
        if (table) table.ajax.reload();
    });

    $('#exportFilter').on('click', function () {
        const params = new URLSearchParams({
            filter_status:    currentStatus               || '',
            filter_inventory: $('#filterInventory').val() || '',
            filter_sttb:      $('#filterSttb').val()      || '',
            filter_po:        $('#filterPo').val()        || '',
            filter_company:   $('#filterCompany').val()   || '',
            filter_dept:      $('#filterDept').val()      || '',
        });
        window.location.href = window.amRoutes.export + '?' + params.toString();
    });
}

function initAssignedTable() {

    assignedTable = $('#assignedTable').DataTable({

        processing: true,
        serverSide: false,
        searching:  true,

        responsive: {
            details: { type: 'column', target: 0 },
        },

        columnDefs: [
            { targets: 0, className: 'dtr-control', orderable: false, width: '28px' },
        ],

        autoWidth:  false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order:      [[1, 'asc']],

        ajax: {
            url:  window.amRoutes.assignedJson,
            type: 'GET',
        },

        columns: [
            // 0 — responsive toggle
            { data: null, defaultContent: '' },

            // 1 — Doc ID
            {
                data:      'assign_id',
                className: 'px-4 py-3 text-xs font-mono font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap',
            },

            // 2 — Inv. Code
            {
                data:      'inventoryid',
                className: 'px-4 py-3 text-xs font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap',
            },

            // 3 — Inventory Name
            {
                data:      'inventory_descr',
                orderable: false,
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300',
                render:    function (data) {
                    return `<span style="max-width:200px;display:block">${data ?? '—'}</span>`;
                },
            },

            // 4 — Unit
            {
                data:      'unit_num',
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap text-center',
            },

            // 5 — Unit Cost
            {
                data:      'unitcost_fmt',
                orderable: false,
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap text-right',
            },

            // 6 — Company
            {
                data:      'assign_cpny_id',
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap',
            },

            // 7 — Department
            {
                data:      'assign_department_id',
                className: 'px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap',
            },

            // 8 — User
            {
                data:      'assign_username',
                className: 'px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-200 whitespace-nowrap',
            },

            // 9 — Warranty Period
            {
                data:      'warranty_period',
                orderable: false,
                className: 'px-4 py-3',
                render:    function (data) { return data || ''; },
            },

            // 10 — Action
            {
                data:      'action',
                orderable: false,
                className: 'px-4 py-3 text-center',
                render:    function (data) { return data || ''; },
            },
        ],

        drawCallback: function () {
            bindAssignedTableActions();
        },
    });
}

function bindAssignedTableActions() {
    $('.see-more-btn').off('click').on('click', function () {
        const id = $(this).data('id');
        amOpenDetail(id);
    });
}

function initFilters() {
    $(document).on('click', '.status-filter', function (e) {
        e.preventDefault();

        $('.status-filter').removeClass('active');
        $(this).addClass('active');

        currentStatus = $(this).data('status');

        if (currentStatus === 'assigned') {
            $('#mainTableWrapper').addClass('hidden');
            $('#assignedTableWrapper').removeClass('hidden');
            if (assignedTable) assignedTable.ajax.reload();
        } else {
            $('#mainTableWrapper').removeClass('hidden');
            $('#assignedTableWrapper').addClass('hidden');
            if (table) table.ajax.reload();
        }
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
