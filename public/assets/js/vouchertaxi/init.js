(function () {
    'use strict';

    $(document).ready(function () {

        VoucherTaxi.Modal.init();

        VoucherTaxi.DataList.init();

        VoucherTaxi.Calendar.init();

        VoucherTaxi.RequestForm.init();

        VoucherTaxi.EditForm.init();

        VoucherTaxi.DetailModal.init();

        VoucherTaxi.Approval.init();

        VoucherTaxi.Process.init();

        VoucherTaxi.AutoOpen.init();


        $(window).on('resize', function () {

            VoucherTaxi.syncPanelHeight();

        });

        VoucherTaxi.log(
            'Voucher Taxi Application Ready'
        );
    });

})();
