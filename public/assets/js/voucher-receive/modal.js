// ============================================================
// modal.js — Voucher Product Receive
// Open / close animations for all modals.
// Modals with forms can ONLY be closed via the X / Close button.
// Confirm-type modals (small) close the same way.
// ============================================================

const VplReceiveModal = {

    open(id) {
        const $m = $(`#${id}`);
        $m.removeClass('hidden').addClass('flex');
        // Slight delay so CSS transition fires
        setTimeout(() => {
            $m.find('.modal-backdrop').removeClass('opacity-0').addClass('opacity-100');
            $m.find('.modal-panel')
              .removeClass('translate-y-4 scale-[0.98] opacity-0')
              .addClass('translate-y-0 scale-100 opacity-100');
        }, 10);
    },

    close(id) {
        const $m = $(`#${id}`);
        $m.find('.modal-backdrop').removeClass('opacity-100').addClass('opacity-0');
        $m.find('.modal-panel')
          .removeClass('translate-y-0 scale-100 opacity-100')
          .addClass('translate-y-4 scale-[0.98] opacity-0');
        setTimeout(() => $m.removeClass('flex').addClass('hidden'), 200);
    },
};
