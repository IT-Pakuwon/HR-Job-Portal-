{{-- Shared JS for the Voucher & Product lists: Doc No button, status badge, type pill, header filter dropdowns.
     Needs <x-spp-list-scripts /> (listCls, listDocText, listBadge) placed before it. --}}
<script>
    // hook: the class the page script listens on (btn-view, btn-view-transfer, ...)
    window.vplDocButton = function(hook, id, text) {
        return `<button type="button" class="${hook} ${window.listCls.doc}" data-id="${id}">${window.listDocText(text || '-')}</button>`;
    };

    // same labels the server badge used
    const statusMap = { P: ['yellow', 'On Progress'], C: ['green', 'Completed'], R: ['red', 'Rejected'], X: ['red', 'Cancel'] };
    window.vplStatusText = (code) => (statusMap[code] || ['blue', 'Revise'])[1];
    window.vplStatusBadge = function(code) {
        return window.listBadge((statusMap[code] || ['blue'])[0], window.vplStatusText(code), true);
    };

    window.vplTypePill = function(value) {
        if (!value) return '<span class="text-gray-400">—</span>';
        const text = String(value).charAt(0).toUpperCase() + String(value).slice(1).toLowerCase();
        const tone = /^v/i.test(text)
            ? 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300'
            : 'border-[#e9d5ff] bg-[#faf5ff] text-[#7e22ce] dark:border-[#a855f7]/30 dark:bg-[#a855f7]/10 dark:text-[#d8b4fe]';
        return `<span class="inline-flex items-center whitespace-nowrap rounded-full border px-2.5 py-0.5 text-xs font-semibold ${tone}">${text}</span>`;
    };

    // header dropdowns of the admin "All" view
    $(function() {
        $('select.vpl-filter').select2({ width: '100%', minimumResultsForSearch: Infinity, dropdownCssClass: 'sppb-s2-dropdown' });
    });
</script>
