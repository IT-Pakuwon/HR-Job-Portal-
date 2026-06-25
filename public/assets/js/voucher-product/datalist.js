// ============================================================
// datalist.js — Voucher Product Master
// DataTable: matches IT Recommendation table style exactly
// ============================================================

const VplMasterDatalist = {

    table:        null,
    statusFilter: '',    // '' = All, 'A' = Active, 'X' = Inactive

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------
    init() {
        VplMasterDatalist.table = $('#masterTable').DataTable({
            processing:   true,
            serverSide:   true,
            autoWidth:    false,
            order:        [[0, 'desc']],
            pageLength:   10,
            lengthMenu:   [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            dom:          'lfrtip',
            ajax: {
                url:     VplMaster.routes.list,
                type:    'GET',
                headers: { 'X-CSRF-TOKEN': VplMaster.csrf() },
                data:    d => {
                    d.status_filter       = VplMasterDatalist.statusFilter;
                    d.filter_type         = $('#filter_type').val()         || '';
                    d.filter_doc_id       = $('#filter_doc_id').val()       || '';
                    d.filter_category     = $('#filter_category').val()     || '';
                    d.filter_source       = $('#filter_source').val()       || '';
                    d.filter_product_name = $('#filter_product_name').val() || '';
                },
            },
            columns: [
                {
                    data:      'product_id',
                    name:      'product_id',
                    className: 'px-5 py-4 whitespace-nowrap align-middle',
                    width:     '130px',
                },
                {
                    data:      'cpnyid',
                    name:      'cpnyid',
                    className: 'px-5 py-4 text-center whitespace-nowrap align-middle',
                    width:     '80px',
                },
                {
                    data:      'product_type',
                    name:      'product_type',
                    className: 'px-5 py-4 text-center align-middle',
                    width:     '90px',
                    render:    d => d === 'V'
                        ? '<span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Voucher</span>'
                        : '<span class="inline-flex items-center rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700">Product</span>',
                },
                {
                    data:      'product_name',
                    name:      'product_name',
                    className: 'px-5 py-4 align-middle',
                },
                {
                    data:           'product_category',
                    name:           'product_category',
                    className:      'px-5 py-4 align-middle',
                    defaultContent: '-',
                    width:          '120px',
                },
                {
                    data:           'product_source_company',
                    name:           'product_source_company',
                    className:      'px-5 py-4 align-middle',
                    defaultContent: '-',
                },
                {
                    data:           'product_source_tenant',
                    name:           'product_source_tenant',
                    className:      'px-5 py-4 align-middle',
                    defaultContent: '-',
                },
                {
                    data:      'status',
                    name:      'status',
                    className: 'px-5 py-4 text-center align-middle',
                    orderable: false,
                    width:     '100px',
                },
                {
                    data:       'action',
                    name:       'action',
                    className:  'px-5 py-4 text-center align-middle',
                    orderable:  false,
                    searchable: false,
                    width:      '110px',
                },
            ],

            // row hover matching IT Recommendation
            createdRow(row) {
                $(row).addClass(
                    'border-b border-gray-100 text-sm text-gray-700 ' +
                    'transition-colors duration-150 hover:bg-gray-50/60 ' +
                    'dark:border-white/[0.04] dark:text-gray-300 dark:hover:bg-white/[0.02]'
                );
            },
        });
    },

    // --------------------------------------------------------
    // REFRESH
    // --------------------------------------------------------
    refresh() {
        VplMasterDatalist.table?.ajax.reload(null, false);
    },

    // --------------------------------------------------------
    // STATUS FILTER PILLS
    // --------------------------------------------------------
    initStatusFilter() {
        $(document).on('click', '.vpl-filter-btn', function (e) {
            e.preventDefault();
            $('.vpl-filter-btn').removeClass('active-filter');
            $(this).addClass('active-filter');
            VplMasterDatalist.statusFilter = $(this).data('status') ?? '';
            VplMasterDatalist.table?.ajax.reload(null, true);
        });
    },

    // --------------------------------------------------------
    // ACTION MENU — fixed-position floating dropdown
    // --------------------------------------------------------
    initActionMenu() {
        const $menu  = $('#actionMenu');
        const MENU_W = 176;
        const MENU_H = 90;
        const MARGIN = 8;

        // ── open ──
        $(document).on('click', '.action-btn', function (e) {
            e.stopPropagation();

            const $btn = $(this);
            const rect = this.getBoundingClientRect();

            const id     = $btn.data('id');
            const toggle = $btn.data('toggle');

            $('#actionMenuEdit')  .data('id', id);
            $('#actionMenuToggle').data('id', id).data('toggle', toggle);
            $('#actionMenuToggleLabel').text($btn.data('label'));
            $('#actionMenuToggleIcon') .attr('class', 'fa-solid w-4 ' + $btn.data('icon'));

            let top  = rect.bottom + MARGIN;
            let left = rect.right  - MENU_W;

            if (top + MENU_H > window.innerHeight - MARGIN) {
                top = rect.top - MENU_H - MARGIN;
            }
            left = Math.max(MARGIN, Math.min(left, window.innerWidth - MENU_W - MARGIN));
            top  = Math.max(MARGIN, top);

            $menu.css({ top, left }).removeClass('hidden');
        });

        // ── close ──
        $(document).on('click', () => $menu.addClass('hidden'));
        $(window)  .on('scroll resize', () => $menu.addClass('hidden'));

        // ── Edit ──
        $('#actionMenuEdit').on('click', function () {
            $menu.addClass('hidden');
            VplMasterForm.loadEdit($(this).data('id'));
        });

        // ── Deactivate / Activate ──
        $('#actionMenuToggle').on('click', function () {
            const id     = $(this).data('id');
            const action = $(this).data('toggle');
            $menu.addClass('hidden');
            if (action === 'deactivateProduct') {
                VplMasterForm.deactivate(id);
            } else {
                VplMasterForm.activate(id);
            }
        });
    },

    // --------------------------------------------------------
    // FILTER SELECT2 + HANDLERS
    // --------------------------------------------------------
    initFilterBar() {
        // Doc ID — AJAX select2
        $('#filter_doc_id').select2({
            placeholder:        'All Doc IDs',
            allowClear:         true,
            minimumInputLength: 0,
            ajax: {
                url:      VplMaster.routes.docIds(),
                type:     'GET',
                dataType: 'json',
                headers:  { 'X-CSRF-TOKEN': VplMaster.csrf() },
                delay:    250,
                data:     params => ({ q: params.term || '' }),
                processResults: data => data,
            },
        });

        // Category — static select2
        $('#filter_category').select2({ placeholder: 'All Categories', allowClear: true });

        // Source — static select2
        $('#filter_source').select2({ placeholder: 'All Sources', allowClear: true });

        // Apply
        $(document).on('click', '#btn_apply_filter', () => {
            VplMasterDatalist.table?.ajax.reload(null, true);
        });

        // Product name — live search on Enter / 500ms debounce
        let _nameTimer;
        $(document).on('keyup', '#filter_product_name', () => {
            clearTimeout(_nameTimer);
            _nameTimer = setTimeout(() => VplMasterDatalist.table?.ajax.reload(null, true), 500);
        });

        // Reset
        $(document).on('click', '#btn_reset_filter', () => {
            $('#filter_type').val('');
            $('#filter_doc_id').val(null).trigger('change');
            $('#filter_category').val(null).trigger('change');
            $('#filter_source').val(null).trigger('change');
            $('#filter_product_name').val('');
            VplMasterDatalist.table?.ajax.reload(null, true);
        });

        // Export
        $(document).on('click', '#btn_export_filter', () => {
            const params = new URLSearchParams({
                status_filter:       VplMasterDatalist.statusFilter,
                filter_type:         $('#filter_type').val()         || '',
                filter_doc_id:       $('#filter_doc_id').val()       || '',
                filter_category:     $('#filter_category').val()     || '',
                filter_source:       $('#filter_source').val()       || '',
                filter_product_name: $('#filter_product_name').val() || '',
            });
            window.open(`${VplMaster.routes.export()}?${params.toString()}`, '_blank');
        });
    },
};
