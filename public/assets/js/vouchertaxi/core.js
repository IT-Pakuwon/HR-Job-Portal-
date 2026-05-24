window.VoucherTaxi = window.VoucherTaxi || {};

VoucherTaxi.state = {
    currentPage: 1,
    pageSize: 10,
    totalRows: 0,

    selectedVoucher: null,
    selectedDocId: null,
    selectedEid: null,

    currentFilter: 'P',
    currentSearch: '',

    calendar: null,
};

VoucherTaxi.config = {
    csrf: document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') || '',
};

VoucherTaxi.events = {};

VoucherTaxi.debug = false;

VoucherTaxi.log = function (...args) {
    if (VoucherTaxi.debug) {
        console.log('[VoucherTaxi]', ...args);
    }
};
