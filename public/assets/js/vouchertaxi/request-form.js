(function () {
    "use strict";

    VoucherTaxi.RequestForm = {
        form: null,

        init() {
            this.form = $("#voucherTaxiForm");

            if (!this.form.length) {
                return;
            }

            this.initSelect2();
            this.initPurpose();

            this.bindSubmit();
            this.bindTopupFilter();

            VoucherTaxi.log("RequestForm Initialized");
        },

        initSelect2() {
            if (!$.fn.select2) {
                return;
            }

            [
                "#cpny_id",
                "#department_id",
                "#cpny_id_expense",
                "#user_topup",
            ].forEach((selector) => {
                const $el = $(selector);

                if (!$el.length) {
                    return;
                }

                if (!$el.is("select")) {
                    return;
                }

                $el.select2({
                    width: "100%",
                    dropdownParent: $("#createVoucherModal"),
                });
            });
        },

        initPurpose() {
            const $purpose = $("#purpose");

            if (!$purpose.length || !$purpose.is("select") || !$.fn.select2) {
                return;
            }

            $purpose.select2({
                width: "100%",
                dropdownParent: $("#createVoucherModal"),
                placeholder: "Select Purpose",
                allowClear: true,
                ajax: {
                    url: VoucherTaxi.Route.purposeSearch(),
                    dataType: "json",
                    delay: 250,
                    data: (params) => ({
                        q: params.term || "",
                    }),
                    processResults: (response) => ({
                        results: response.data || [],
                    }),
                    cache: true,
                },
            });
        },

        bindSubmit() {
            this.form.on("submit", (e) => {
                e.preventDefault();

                this.submit();
            });
        },

        async submit() {
            const confirm = await VoucherTaxi.Helper.confirm(
                "Submit Voucher Taxi?",
                "Please make sure all information is correct.",
                "Submit",
            );

            if (!confirm.isConfirmed) {
                return;
            }

            VoucherTaxi.Helper.loading("Submitting voucher...");

            $.ajax({
                url: VoucherTaxi.Route.store(),

                method: "POST",

                headers: VoucherTaxi.Helper.headers(),

                data: this.form.serialize(),

                success: async (response) => {

                    VoucherTaxi.Helper.closeLoading();

                    this.reset();

                    await VoucherTaxi.Modal.close(
                        "#createVoucherModal"
                    );

                    VoucherTaxi.DataList.reload();

                    VoucherTaxi.Calendar?.reload();

                    VoucherTaxi.Helper.success(
                        response.message ||
                        "Voucher submitted successfully."
                    );
                },

                error: (xhr) => {
                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Helper.ajaxError(xhr);
                },
            });
        },

        reset() {
            this.form.trigger("reset");

            $("#purpose").val(null).trigger("change");

            $("#cpny_id").trigger("change");

            $("#department_id").trigger("change");

            $("#cpny_id_expense").val(null).trigger("change");

            $("#user_topup").val(null).trigger("change");

           $("#purpose_desc").val("");

            $("#trip_return").prop("checked", true);

            $("#trip_oneway").prop("checked", false);
        },

        bindTopupFilter() {
            const $department = $("#department_id");
            const $userTopup = $("#user_topup");

            const originalOptions = $userTopup.html();

            const applyFilter = () => {
                const dept = ($department.val() || "").trim();

                if ($userTopup.hasClass("select2-hidden-accessible")) {
                    $userTopup.select2("destroy");
                }

                $userTopup.html("");

                $(originalOptions).each(function () {
                    const $option = $(this);

                    const optionDept = ($option.data("dept") || "")
                        .toString()
                        .trim();

                    const isPlaceholder = !$option.val();

                    if (isPlaceholder || optionDept === dept) {
                        $userTopup.append($option.clone());
                    }
                });

                $userTopup.select2({
                    width: "100%",
                    dropdownParent: $("#createVoucherModal"),
                });

                $userTopup.val("").trigger("change");
            };

            $department.off("change.topupFilter");

            $department.on("change.topupFilter", applyFilter);

            applyFilter();
        },
    };
})();
