/**
 * Recruitment Dashboard — Initialization
 *
 * Chart rendering is handled automatically by each <x-card-chart.*> component's
 * dedicated JS loader (e.g. bar-chart.js, donut-chart.js, area-chart.js, …).
 * This file only manages the pill-style filter UI enhancements.
 */
document.addEventListener('DOMContentLoaded', function () {
    // ── Select2 for pill-style filter dropdowns ──────────────────────────
    if (typeof $ !== 'undefined' && $.fn.select2) {
        var selector = '#filterGroupCpny, #filterArea, #filterCompany, #filterDepartment, #filterLocation, #filterSource';
        $(selector).each(function () {
            var $el = $(this);
            var opts = {
                placeholder: $el.find('option:first').text() || 'Select…',
                allowClear: true,
                width: 'resolve',
                minimumResultsForSearch: $el.attr('id') === 'filterSource' ? -1 : 5,
            };
            // Disable search for short lists
            if ($el.find('option').length <= 6) {
                opts.minimumResultsForSearch = -1;
            }
            $el.select2(opts);
        });

        // ── Auto-submit on Source filter change ─────────────────────────
        $('#filterSource').on('select2:select select2:clear', function () {
            $(this).closest('form').submit();
        });
    }

    // ── Keyboard shortcut: Enter triggers form submit ────────────────────
    document.querySelectorAll('form input, form select').forEach(function (el) {
        el.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                var form = el.closest('form');
                if (form) form.submit();
            }
        });
    });
});
