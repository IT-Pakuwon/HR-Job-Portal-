(function () {
    "use strict";

    VoucherTaxi.Modal = {

        open(selector) {

            const $modal = $(selector);

            if (!$modal.length) {
                return;
            }

            $modal.removeClass("hidden");
            $modal.addClass("flex");

            document.body.classList.add(
                "overflow-hidden"
            );

            requestAnimationFrame(() => {

                $modal
                    .find(".modal-backdrop")
                    .removeClass("opacity-0")
                    .addClass("opacity-100");

                $modal
                    .find(".modal-panel")
                    .removeClass(
                        "opacity-0 translate-y-4 scale-[0.98]"
                    )
                    .addClass(
                        "opacity-100 translate-y-0 scale-100"
                    );
            });
        },

        resetUrl() {

            const path =
                window.location.pathname;

            if (
                path.includes("/showvouchertaxi/") ||
                path.includes("/editvouchertaxi/") ||
                path.includes("/processvouchertaxi/")
            ) {

                window.history.replaceState(
                    {},
                    document.title,
                    "/vouchertaxi"
                );
            }
        },

        resetModalForm(selector) {

            if (
                selector === "#createVoucherModal" &&
                VoucherTaxi.RequestForm &&
                typeof VoucherTaxi.RequestForm.reset === "function"
            ) {
                VoucherTaxi.RequestForm.reset();
            }

            if (
                selector === "#editVoucherTaxiModal" &&
                VoucherTaxi.EditForm &&
                typeof VoucherTaxi.EditForm.reset === "function"
            ) {
                VoucherTaxi.EditForm.reset();
            }
        },

        close(selector) {

            return new Promise((resolve) => {

                const $modal = $(selector);

                if (!$modal.length) {
                    resolve();
                    return;
                }

                $modal
                    .find(".modal-backdrop")
                    .removeClass("opacity-100")
                    .addClass("opacity-0");

                $modal
                    .find(".modal-panel")
                    .removeClass(
                        "opacity-100 translate-y-0 scale-100"
                    )
                    .addClass(
                        "opacity-0 translate-y-4 scale-[0.98]"
                    );

                setTimeout(() => {

                    $modal.removeClass("flex");
                    $modal.addClass("hidden");

                    this.resetModalForm(
                        selector
                    );

                    if (
                        $(".fixed.flex").length === 0
                    ) {

                        document.body.classList.remove(
                            "overflow-hidden"
                        );

                        this.resetUrl();
                    }

                    resolve();

                }, 200);
            });
        },

        closeAll() {

            [
                "#createVoucherModal",
                "#editVoucherTaxiModal",
                "#viewVoucherModal",
                "#processVoucherModal",
            ].forEach((modal) => {

                this.close(modal);
            });
        },

        bindBackdropClose() {

            $(document).on(
                "click",
                ".modal-backdrop",
                function () {

                    const modal =
                        $(this).closest("[id]");

                    VoucherTaxi.Modal.close(
                        "#" + modal.attr("id")
                    );
                }
            );
        },

        bindEscapeKey() {

            $(document).on(
                "keydown",
                function (e) {

                    if (
                        e.key !== "Escape"
                    ) {
                        return;
                    }

                    VoucherTaxi.Modal.closeAll();
                }
            );
        },

        bindButtons() {

            $("#openCreateVoucherModal").on(
                "click",
                () => {

                    this.open(
                        "#createVoucherModal"
                    );
                }
            );

            $("#closeCreateVoucherModal").on(
                "click",
                () => {

                    this.close(
                        "#createVoucherModal"
                    );
                }
            );

            $("#closeCreateVoucherModalFooter").on(
                "click",
                () => {

                    this.close(
                        "#createVoucherModal"
                    );
                }
            );

            $("#cancelEditVoucherTaxiBtn").on(
                "click",
                () => {

                    this.close(
                        "#editVoucherTaxiModal"
                    );
                }
            );

            $("#cancelEditVoucherTaxiBtnFooter").on(
                "click",
                () => {

                    this.close(
                        "#editVoucherTaxiModal"
                    );
                }
            );

            $("#closeViewVoucherModal").on(
                "click",
                () => {

                    this.close(
                        "#viewVoucherModal"
                    );
                }
            );

            $("#closeViewVoucherModalFooter").on(
                "click",
                () => {

                    this.close(
                        "#viewVoucherModal"
                    );
                }
            );

            $("#closeProcessVoucherModal").on(
                "click",
                () => {

                    this.close(
                        "#processVoucherModal"
                    );
                }
            );

            $("#closeProcessVoucherModalFooter").on(
                "click",
                () => {

                    this.close(
                        "#processVoucherModal"
                    );
                }
            );
        },

        init() {

            this.bindButtons();

            // this.bindBackdropClose();

            // this.bindEscapeKey();

            VoucherTaxi.log(
                "Modal Initialized"
            );
        }
    };

})();
