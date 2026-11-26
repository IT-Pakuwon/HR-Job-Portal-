{{-- HR PRF Create/Edit: every dropdown on the form uses the shared form skin (the form carries .sf-field,
     styles come from x-spp-form-styles). Include after the page's own scripts. --}}
<script>
    // set before the page initialises its dropdowns, so their lists get the same look
    $.fn.select2.defaults.set('dropdownCssClass', 'sf-s2-dropdown');

    $(function() {
        // dropdowns the page leaves native
        $('#personnelForm select').not('.select2-hidden-accessible').select2({
            width: '100%',
            minimumResultsForSearch: 8
        });
    });
</script>
