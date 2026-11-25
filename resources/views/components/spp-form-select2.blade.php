{{-- Styled dropdowns (Select2) for the header fields of the SPP* forms.
     User picks fire the normal "change" event, so the page's own handlers keep working.
     Anything that rebuilds options or sets a value from code must refresh Select2: Business Unit is watched
     here (its options are rebuilt by AJAX); direct .val() calls on Company/BU/Department in the page scripts
     must add .trigger('change.select2'). --}}
<script>
    $(function() {
        $('#cpnyid, #business_unit_id, select[name="departementid"], #perpost, #bqtype, #condition_unit, #beban').each(function() {
            $(this).parent().addClass('sf-field');
            $(this).select2({
                width: '100%',
                minimumResultsForSearch: 8,
                dropdownCssClass: 'sf-s2-dropdown'
            });
        });

        const bu = document.getElementById('business_unit_id');
        if (bu && window.MutationObserver) {
            new MutationObserver(function() {
                $('#business_unit_id').trigger('change.select2');
            }).observe(bu, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['selected', 'disabled']
            });
        }
    });
</script>
