(function () {
    'use strict';

    VoucherTaxi.AutoOpen = {

        init() {

            this.openVoucherFromRoute();

            VoucherTaxi.log(
                'AutoOpen Initialized'
            );
        },

        openVoucherFromRoute() {

            const eid =
                $('#autoOpenVoucherEid')
                    .val();

            if (!eid) {
                return;
            }

            setTimeout(() => {

                if (
                    VoucherTaxi.DetailModal
                ) {

                    VoucherTaxi.DetailModal.open(
                        eid
                    );
                }

            }, 500);
        }
    };

})();
