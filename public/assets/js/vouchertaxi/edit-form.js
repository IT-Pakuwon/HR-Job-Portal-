(function () {
    "use strict";

    VoucherTaxi.EditForm = {

        currentEid: null,
        currentDocId: null,

        init() {

            this.initSelect2();
            this.bindEditTopupFilter();
            this.bindSubmit();

            VoucherTaxi.log("EditForm Initialized");
        },

        initSelect2() {

            $("#edit_cpny_id").select2({
                width: "100%",
                dropdownParent: $("#editVoucherTaxiModal")
            });

            $("#edit_department_id").select2({
                width: "100%",
                dropdownParent: $("#editVoucherTaxiModal")
            });

            $("#edit_purpose").select2({
                width: "100%",
                dropdownParent: $("#editVoucherTaxiModal")
            });

            $("#edit_cpny_id_expense").select2({
                width: "100%",
                dropdownParent: $("#editVoucherTaxiModal")
            });

            $("#edit_user_topup").select2({
                width: "100%",
                dropdownParent: $("#editVoucherTaxiModal")
            });
        },

        open(eid) {

            this.currentEid = eid;

            VoucherTaxi.Helper.loading("Loading voucher...");

            $.ajax({

                url: VoucherTaxi.Route.find(eid),

                method: "GET",

                success: (res) => {

                    VoucherTaxi.Helper.closeLoading();

                    console.log("EDIT RESPONSE", res);

                    const data = res.data || res;

                    console.log("EDIT DATA", data);

                    this.populate(data);

                    VoucherTaxi.Modal.open("#editVoucherTaxiModal");
                },

                error: (xhr) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Helper.ajaxError(xhr);
                }
            });
        },

        populate(data) {

            console.log("Populate Edit Form", data);

            this.currentDocId = data.docid;

            $("#edit_docid").val(data.docid || "");

            $("#edit_eid").val(this.currentEid || "");

            $("#edit_cpny_id")
                .val(data.cpny_id || "")
                .trigger("change");

            $("#edit_department_id")
                .val(data.department_id || "")
                .trigger("change");

            $("#edit_user_peminta").val(
                data.user_peminta ||
                data.username ||
                ""
            );

            $("#edit_requester_name").val(
                data.user_name ||
                data.requester_name ||
                data.name ||
                data.requester ||
                data.user_peminta ||
                ""
            );

            $("#edit_date_used").val(
                data.date_used || ""
            );

            $("#edit_origin").val(
                data.origin || ""
            );

            $("#edit_destination").val(
                data.destination || ""
            );

            const purposeValue =
                data.purpose_id || '';

            const purposeText =
                data.purpose_name || purposeValue;

            $('#edit_purpose').empty();

            const option = new Option(
                purposeText,   // text shown to user
                purposeValue,  // actual value submitted
                true,
                true
            );

            $('#edit_purpose')
                .append(option)
                .trigger('change');

            $("#edit_purpose_desc").val(
                data.purpose_descr ||
                data.purpose_description ||
                ""
            );

            $("#edit_cpny_id_expense")
                .val(data.cpny_id_expense || "")
                .trigger("change");

            setTimeout(() => {

                $("#edit_user_topup")
                    .val(data.user_topup || "")
                    .trigger("change");

            }, 100);

            $('#editVoucherTaxiModal input[name="type_trip"]')
                .prop("checked", false);

            $(
                `#editVoucherTaxiModal input[name="type_trip"][value="${data.type_trip}"]`
            ).prop("checked", true);

            if ($("#editStatusBadge").length) {
                this.renderStatus(data.status);
            }

            this.renderReviseReason(data);
        },

        bindEditTopupFilter() {

            $("#edit_department_id").on("change", function () {

                const dept =
                    ($(this).val() || "")
                        .toString()
                        .trim();

                $("#edit_user_topup option").each(function () {

                    const optionDept =
                        ($(this).data("dept") || "")
                            .toString()
                            .trim();

                    const visible =
                        !dept ||
                        !optionDept ||
                        optionDept === dept;

                    $(this).prop(
                        "disabled",
                        !visible
                    );
                });

                $("#edit_user_topup")
                    .trigger("change.select2");
            });
        },

        renderStatus(status) {

            if (!$("#editStatusBadge").length) {
                return;
            }

            const badge =
                VoucherTaxi.Helper.badge(status);

            $("#editStatusBadge")
                .attr(
                    "class",
                    `inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ${badge.class}`
                )
                .text(
                    badge.text || status
                );
        },

        renderReviseReason(data) {

            const reason =
                data.revise_reason ||
                data.revision_reason ||
                "";

            if (!$("#editReviseReasonWrapper").length) {
                return;
            }

            if (!reason) {

                $("#editReviseReasonWrapper")
                    .addClass("hidden");

                return;
            }

            $("#editReviseReasonWrapper")
                .removeClass("hidden");

            $("#edit_revise_reason")
                .html(
                    VoucherTaxi.Helper.nl2br(reason)
                );
        },

        bindSubmit() {

            $("#editVoucherTaxiForm")
                .off("submit")
                .on("submit", (e) => {

                    e.preventDefault();

                    this.submit();
                });
        },

        async submit() {

            if (!this.currentDocId) {
                return;
            }

            const confirm =
                await VoucherTaxi.Helper.confirm(
                    "Save Changes?",
                    "Voucher will be resubmitted for approval.",
                    "Save"
                );

            if (!confirm.isConfirmed) {
                return;
            }

            VoucherTaxi.Helper.loading(
                "Saving changes..."
            );

            $.ajax({

                url:
                    VoucherTaxi.Route.update(
                        this.currentDocId
                    ),

                method: "POST",

                headers:
                    VoucherTaxi.Helper.headers(),

                data:
                    $("#editVoucherTaxiForm")
                        .serialize(),

                success: (res) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Modal.close(
                        "#editVoucherTaxiModal"
                    );

                    VoucherTaxi.DataList.reload();

                    if (VoucherTaxi.Calendar) {
                        VoucherTaxi.Calendar.reload();
                    }

                    VoucherTaxi.Helper.success(
                        res.message ||
                        "Voucher updated successfully."
                    );
                },

                error: (xhr) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Helper.ajaxError(xhr);
                }
            });
        }
    };

})();
