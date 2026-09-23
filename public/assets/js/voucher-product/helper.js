// ============================================================
// helper.js — Voucher Product Master
// Number formatting utilities
// ============================================================

const VplMasterHelper = {

    formatNumerator(input) {
        let v = input.value.replace(/\D/g, '');
        input.value = v.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },

    unformat(v) {
        return (v ?? '').toString().replace(/,/g, '');
    },

    formatDisplay(val) {
        const n = parseFloat(val);
        return isNaN(n) ? val : n.toLocaleString('en-US', { maximumFractionDigits: 0 });
    },

    isEmpty(v) {
        return v === null || v === undefined || v.toString().trim() === '';
    },
};
