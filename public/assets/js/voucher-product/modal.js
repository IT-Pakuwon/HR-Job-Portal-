// ============================================================
// modal.js — Voucher Product Master
// Modal open/close, form reset, select2, category AJAX
// ============================================================

const VplMasterModal = {

    // --------------------------------------------------------
    // OPEN / CLOSE
    // --------------------------------------------------------
    show() {
        document.getElementById('modalStock')?.classList.remove('hidden');
        document.getElementById('modalStock')?.classList.add('flex');
    },

    hide() {
        document.getElementById('modalStock')?.classList.add('hidden');
        document.getElementById('modalStock')?.classList.remove('flex');
    },

    // --------------------------------------------------------
    // RESET FORM to empty / create state
    // --------------------------------------------------------
    reset() {
        document.getElementById('stockForm')?.reset();
        document.getElementById('key_id').value        = '';
        document.getElementById('product_value').value = '0';
        document.getElementById('formError')?.classList.add('hidden');

        // always unlock product name on reset (new stock)
        $('#product_name')
            .prop('readonly', false)
            .removeClass('bg-slate-100 cursor-not-allowed')
            .removeAttr('title');

        // reset all select2 fields
        $('#stockForm .select2').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).val(null).trigger('change');
            }
        });

        // pre-select default company
        const defaultCpny = document.getElementById('defaultCpny')?.value ?? '';
        if (defaultCpny) {
            $('#cpnyidx').val(defaultCpny).trigger('change');
        }

        // clear category
        VplMasterModal.clearCategories();
    },

    // --------------------------------------------------------
    // CATEGORY: clear dropdown
    // --------------------------------------------------------
    clearCategories() {
        const $cat = $('#categoryx');
        $cat.empty().append('<option value="">— select type first —</option>');
        if ($cat.hasClass('select2-hidden-accessible')) $cat.trigger('change');
    },

    // --------------------------------------------------------
    // CATEGORY: load from API (groups=TYPE), optionally pre-select
    // --------------------------------------------------------
    loadCategories(preselectValue) {
        const $cat = $('#categoryx');
        $cat.empty().append('<option value="">Loading...</option>');

        $.get(VplMaster.routes.category, function (data) {
            $cat.empty().append('<option value="">— select category —</option>');
            (data ?? []).forEach(c => {
                const $opt = $('<option>').val(c.category_name).text(c.category_name);
                if (preselectValue && c.category_name === preselectValue) {
                    $opt.prop('selected', true);
                }
                $cat.append($opt);
            });
            if ($cat.hasClass('select2-hidden-accessible')) $cat.trigger('change');
        }).fail(() => {
            $cat.empty().append('<option value="">— failed to load —</option>');
        });
    },

    // --------------------------------------------------------
    // SELECT2 — initialize all .select2 elements in the form.
    // No dropdownParent → appends to body → floats above modal.
    // z-index override in blade <style>.
    // --------------------------------------------------------
    initSelect2() {
        $('#stockForm .select2').select2({ width: '100%' });
    },

    // --------------------------------------------------------
    // INIT: bind open/close events
    // --------------------------------------------------------
    init() {
        VplMasterModal.initSelect2();

        // close buttons only — no backdrop click, no ESC (form modal)
        document.getElementById('btnCloseModal')?.addEventListener('click', VplMasterModal.hide);
        document.getElementById('btnCancelModal')?.addEventListener('click', VplMasterModal.hide);

        // product type change → reload category
        $('#product_typex').on('change', function () {
            VplMasterModal.loadCategories(null);
        });

        // value input formatting
        document.getElementById('product_value')?.addEventListener('input', function () {
            VplMasterHelper.formatNumerator(this);
        });
    },
};
