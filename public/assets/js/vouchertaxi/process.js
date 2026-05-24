(function () {
    "use strict";

    VoucherTaxi.Process = {
        currentDocId: null,

        init() {
            this.bindOpen();
            this.bindExpenseOwner();
            this.bindBudgetFormatting();
            this.bindDepartmentEmployee();
            this.bindSubmit();

            this.initSelect2();

            VoucherTaxi.log("Process Initialized");
        },

        initSelect2() {

            $('.select2-process').select2({

                width: '100%',

                dropdownParent:
                    $('#processVoucherModal'),

                placeholder:
                    'Select Option',

                allowClear: true
            });
        },
        bindOpen() {
            $(document).on("click", "#openProcessVoucherBtn", () => {
                const data = VoucherTaxi.DetailModal.currentData;

                if (!data) {
                    return;
                }

                this.open(data);
            });
        },

        open(data) {

            this.currentDocId = data.docid;

            $("#process_docid").val(data.docid || "");

            $("#process_docno").text(data.docid || "-");

            $("#process_requester").text(
                data.user_name || data.user_peminta || "-",
            );

            $("#process_date").text(data.date_used || "-");

            $("#process_company").text(data.cpny_name || data.cpny_id || "-");

            $("#process_department").text(
                data.department_name || data.department_id || "-",
            );

            $("#process_trip").text(data.type_trip || "-");

            $("#process_route").text(
                `${data.origin || "-"} → ${data.destination || "-"}`,
            );

            $("#process_budget").text(
                VoucherTaxi.Helper.moneyWithPrefix(
                    data.actual_budget ?? data.max_budget ?? 0,
                ),
            );

            $("#process_purpose").html(`
                <div class="space-y-2">

                    <div>
                        <span class="font-semibold">
                            Purpose :
                        </span>
                        ${data.purpose_name ?? data.purpose_id ?? "-"}
                    </div>

                    <div>
                        <span class="font-semibold">
                            Description :
                        </span>

                        <div class="mt-1">
                            ${VoucherTaxi.Helper.nl2br(
                                data.purpose_descr || "-",
                            )}
                        </div>
                    </div>

                </div>
            `);

            $("#current_expense_company").text(data.cpny_id_expense || "-");

            $("#current_expense_department").text(
                data.department_id_expense || "-",
            );

            $("#current_expense_user").text(
                data.user_peminta_expense || data.user_peminta || "-",
            );

            const badge = VoucherTaxi.Helper.badge(data.status);

            $("#process_status").html(`
                    <span class="
                        rounded-full
                        px-3 py-1
                        text-xs
                        font-semibold
                        ${badge.class}
                    ">
                        ${badge.text}
                    </span>
                `);

            $("#actual_budget_display").val("");

            $("#actual_budget").val("");

            $("#changeExpenseOwner").prop("checked", false);

            $("#expenseOwnerSection").addClass("hidden");

            $("#process_cpny_id_expense").val("");

            $("#process_department_id_expense").val("");

            $("#process_user_peminta_expense").val("");

            VoucherTaxi.Modal.open("#processVoucherModal");
        },

        bindExpenseOwner() {
            $("#changeExpenseOwner").on("change", function () {
                $("#expenseOwnerSection").toggleClass("hidden", !this.checked);
            });
        },

        bindBudgetFormatting() {
            $("#actual_budget_display").on("input", function () {
                const raw = VoucherTaxi.Helper.parseMoney($(this).val());

                $("#actual_budget").val(raw);

                $(this).val(VoucherTaxi.Helper.money(raw));
            });
        },

        bindDepartmentEmployee() {
            $("#process_department_id_expense").on("change", function () {
                const departmentId = $(this).val();

                const $employee = $("#process_user_peminta_expense");

                $employee.html(`
                <option value="">
                    Loading...
                </option>
            `);

                if (!departmentId) {
                    $employee.html(`
                    <option value="">
                        Select Employee
                    </option>
                `);

                    return;
                }

                $.ajax({
                    url: VoucherTaxi.Route.employeeByDepartment(),

                    method: "GET",

                    data: {
                        department_id: departmentId,
                    },

                    success: function (res) {
                        let html = `
                        <option value="">
                            Select Employee
                        </option>
                    `;

                        (res.data || []).forEach((emp) => {
                            html += `
                            <option value="${emp.username}">
                                ${emp.name}
                            </option>
                        `;
                        });

                        $employee.html(html)
                            .trigger('change');
                    },

                    error: function () {
                        $employee.html(`
                        <option value="">
                            Select Employee
                        </option>
                    `);
                    },
                });
            });
        },

        bindSubmit() {
            $("#processVoucherForm").on("submit", (e) => {
                e.preventDefault();

                this.submit();
            });
        },

        async submit() {
            if (!this.currentDocId) {
                return;
            }

            const actualBudget = $("#actual_budget").val();

            if (!actualBudget) {
                VoucherTaxi.Helper.warning("Actual budget is required.");

                return;
            }

            const confirm = await VoucherTaxi.Helper.confirm(
                "Process Voucher?",
                "Actual expense will be saved.",
                "Save Process",
            );

            if (!confirm.isConfirmed) {
                return;
            }

            VoucherTaxi.Helper.loading("Saving process...");

            $.ajax({
                url: VoucherTaxi.Route.process(this.currentDocId),

                method: "POST",

                headers: VoucherTaxi.Helper.headers(),

                data: $("#processVoucherForm").serialize(),

                success: (res) => {
                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Modal.close("#processVoucherModal");

                    VoucherTaxi.Modal.close("#viewVoucherModal");

                    VoucherTaxi.DataList.reload();

                    if (VoucherTaxi.Calendar) {
                        VoucherTaxi.Calendar.reload();
                    }

                    VoucherTaxi.Helper.success(
                        res.message || "Voucher processed successfully.",
                    );
                },

                error: (xhr) => {
                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Helper.ajaxError(xhr);
                },
            });
        },
    };
})();
