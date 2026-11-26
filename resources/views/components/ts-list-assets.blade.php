{{--
    Shared assets for the Tenancy Support lists (Master Data, SIK, SIMKB, SIA, Checker TS, Complaint Jobs):
    the app's list look (DataTables toolbar, row buttons, status pills) plus styled filter dropdowns.
    Include once per page, before the page script. Any <select class="ts-s2"> becomes a Select2 field.
--}}
<x-spp-list-styles />
<x-spp-list-scripts />

<style>
    /* the global table margin pushed the last column past the card edge */
    .dt-pretty table.dataTable,
    .dt-pretty .dataTables_wrapper.dt-inset > table.dataTable { margin-left: 0 !important; margin-right: 0 !important; min-width: 100% !important; }
    /* the whole row opens the record */
    table.ts-rows tbody tr:has(td:not(.dataTables_empty)) { cursor: pointer; }
</style>

<script>
    // amber "edit" icon button, partner of window.listCls.view
    window.tsEditCls = 'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md border text-xs transition-all duration-200 hover:-translate-y-0.5 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 dark:hover:text-white border-amber-200 bg-amber-50 text-amber-700 hover:border-amber-500 hover:bg-amber-500 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500';

    window.tsSelects = function() {
        $('select.ts-s2').each(function() {
            const el = this, $el = $(el);
            if ($el.data('select2')) return;
            $el.select2({ width: '100%', minimumResultsForSearch: 8, dropdownCssClass: 'sppb-s2-dropdown' });
            // Alpine (x-model / @change) only hears native events, Select2 only fires jQuery ones
            if (el.hasAttribute('x-model')) $el.on('select2:select', () => el.dispatchEvent(new Event('change', { bubbles: true })));
            // options that the page script fills in later
            new MutationObserver(() => $el.trigger('change.select2'))
                .observe(el, { childList: true, subtree: true, attributes: true, attributeFilter: ['selected'] });
        });
    };
    $(function() { setTimeout(window.tsSelects, 0); });
</script>
