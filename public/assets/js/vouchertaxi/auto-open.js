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

            const path =
                window.location.pathname;

            const showMatch =
                path.match(
                    /\/showvouchertaxi\/([^\/]+)$/
                );

            const editMatch =
                path.match(
                    /\/editvouchertaxi\/([^\/]+)$/
                );

            const processMatch =
                path.match(
                    /\/processvouchertaxi\/([^\/]+)$/
                );

            if (showMatch) {

                setTimeout(() => {

                    VoucherTaxi.DetailModal.open(
                        showMatch[1]
                    );

                }, 300);

                return;
            }

            if (
                editMatch &&
                VoucherTaxi.EditForm
            ) {

                setTimeout(() => {

                    VoucherTaxi.EditForm.open(
                        editMatch[1]
                    );

                }, 300);

                return;
            }

            if (
                processMatch &&
                VoucherTaxi.Process
            ) {

                setTimeout(() => {

                    VoucherTaxi.Process.open(
                        processMatch[1]
                    );

                }, 300);
            }
        }
    };

})();
