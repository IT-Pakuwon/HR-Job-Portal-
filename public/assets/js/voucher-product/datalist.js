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
                    const adminAll = VplMasterDatalist.statusFilter === 'ADMINALL';
                    d.status_filter       = VplMasterDatalist.statusFilter;
                    d.filter_type         = $('#filter_type').val()         || '';
                    d.filter_doc_id       = $('#filter_doc_id').val()       || '';
                    d.filter_category     = $('#filter_category').val()     || '';
                    d.filter_source       = $('#filter_source').val()       || '';
                    d.filter_product_name = $('#filter_product_name').val() || '';
                    if (adminAll) {
                        d.filter_company = $('#adm_filter_company').val() || '';
                    }
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
                    data:       'total_stock',
                    name:       'total_stock',
                    className:  'px-5 py-4 text-right align-middle',
                    orderable:  false,
                    searchable: false,
                    width:      '110px',
                    render: (data, type) => {
                        const total = data?.total ?? 0;
                        if (type !== 'display') return total;
                        if (!total) return '<span class="text-slate-400">0</span>';

                        const breakdown = encodeURIComponent(JSON.stringify(data?.breakdown ?? []));
                        return `<span class="vpl-total-stock inline-flex cursor-default items-center gap-1 font-semibold text-slate-900 dark:text-slate-100" data-breakdown="${breakdown}">${VplMasterHelper.formatDisplay(total)}</span>`;
                    },
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
                    className: 'px-5 py-4 align-middle text-center',
                    orderable: false,
                    width:     '100px',
                    render:    d => `<div class="flex justify-center">${d}</div>`,
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
        $(document).on('click', '.status-filter', function (e) {
            e.preventDefault();
            $('.status-filter').removeClass('active-card');
            $(this).addClass('active-card');
            const status = $(this).data('status') ?? '';
            VplMasterDatalist.statusFilter = status;
            $('#adminAllFilters')
                .toggleClass('hidden', status !== 'ADMINALL')
                .toggleClass('flex', status === 'ADMINALL');
            VplMasterDatalist.table?.ajax.reload(null, true);
        });

        // Company dropdown only applies while "All Product" is active
        $(document).on('change', '#adm_filter_company', function () {
            if (VplMasterDatalist.statusFilter === 'ADMINALL') {
                VplMasterDatalist.table?.ajax.reload(null, true);
            }
        });
    },

    // --------------------------------------------------------
    // ACTION MENU — fixed-position floating dropdown
    // --------------------------------------------------------
    initActionMenu() {
        const $menu  = $('#actionMenu');
        const MENU_W = 176;
        const MARGIN = 8;

        // ── open ──
        $(document).on('click', '.action-btn', function (e) {
            e.stopPropagation();

            const $btn = $(this);
            const rect = this.getBoundingClientRect();

            const id      = $btn.data('id');
            const toggle  = $btn.data('toggle');
            const canEdit = String($btn.data('can-edit')) === '1';

            $('#actionMenuEdit')
                .data('id', id)
                .toggle(canEdit);
            $('#actionMenuDivider').toggle(canEdit);
            $('#actionMenuToggle').data('id', id).data('toggle', toggle);
            $('#actionMenuToggleLabel').text($btn.data('label'));
            $('#actionMenuToggleIcon') .attr('class', 'fa-solid w-4 ' + $btn.data('icon'));

            const MENU_H = canEdit ? 90 : 46;
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
    // TOTAL STOCK — hover tooltip (per-warehouse / expiry breakdown)
    // --------------------------------------------------------
    initTotalStockTooltip() {
        const $tip  = $('#totalStockTooltip');
        const $body = $('#totalStockTooltipBody');
        const TIP_W = 220;
        const MARGIN = 8;

        $(document).on('mouseenter', '.vpl-total-stock', function () {
            let breakdown = [];
            try {
                breakdown = JSON.parse(decodeURIComponent($(this).attr('data-breakdown') || '[]'));
            } catch (e) { breakdown = []; }

            $body.html(breakdown.length
                ? breakdown.map(b => {
                    const exp = b.exp && b.exp !== '1900-01-01' ? b.exp : 'No Expired';
                    return `<div class="flex items-center justify-between gap-4">
                        <span>${b.whs_id ?? '-'} <span class="text-slate-400">(${exp})</span></span>
                        <span class="font-semibold">${VplMasterHelper.formatDisplay(b.qty)}</span>
                    </div>`;
                }).join('')
                : '<div class="text-slate-400">No stock detail</div>');

            const rect = this.getBoundingClientRect();
            let left = rect.right - TIP_W;
            let top  = rect.bottom + 6;

            left = Math.max(MARGIN, Math.min(left, window.innerWidth - TIP_W - MARGIN));
            if (top + 40 > window.innerHeight - MARGIN) top = rect.top - MARGIN;

            $tip.css({ top, left }).removeClass('hidden');
        });

        $(document).on('mouseleave', '.vpl-total-stock', () => $tip.addClass('hidden'));
        $(window).on('scroll resize', () => $tip.addClass('hidden'));
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
            const adminAll = VplMasterDatalist.statusFilter === 'ADMINALL';
            const params = new URLSearchParams({
                status_filter:       VplMasterDatalist.statusFilter,
                filter_type:         $('#filter_type').val() || '',
                filter_doc_id:       $('#filter_doc_id').val()       || '',
                filter_category:     $('#filter_category').val()     || '',
                filter_source:       $('#filter_source').val()       || '',
                filter_product_name: $('#filter_product_name').val() || '',
                ...(adminAll ? { filter_company: $('#adm_filter_company').val() || '' } : {}),
            });
            window.open(`${VplMaster.routes.export()}?${params.toString()}`, '_blank');
        });
    },
};
