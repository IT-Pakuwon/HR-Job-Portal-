function resetRequestForm() {

    $("#requestForm")[0].reset();

    $("#requestForm .select2").each(function () {

        $(this)
            .val(null)
            .trigger("change");

    });

    $("#requestDetailContainer").html("");

    $("#existingAttachmentContainer").html("");

    $("#newAttachmentContainer").html("");

    $("#requestAttachment").val("");

    existingAttachments = [];

    selectedFiles = [];

    $("#requestMethod").val("POST");

    $("#requestUrl").val("");

    $("#requestHash").val("");

    $("#requestModalTitle").text(
        "Create Access Request"
    );

    detailIndex = 0;

    $(".select2-selection")
        .removeClass("!border-red-500");

    $("#btnSubmitRequest")
        .prop("disabled", false)
        .removeClass(
            "opacity-60 cursor-not-allowed"
        )
        .html(`
            <i class="fa-solid fa-paper-plane text-xs"></i>
            Submit Request
        `);

    $("#access_date").val(
        new Date().toISOString().split("T")[0]
    );

    recalculateSummary();
}

function initRequestForm() {

    $(".select2").select2({

        width: "100%",

        dropdownParent: $("#requestModal"),

        minimumResultsForSearch: 0,
    });

    $("#btnSubmitRequest").on(
        "click",
        async function () {

            await submitRequestForm();

        }
    );
}

function initDetailHandlers() {

    $("#btnAddDetail").on(
        "click",
        function () {

            appendDetailRow();

        }
    );

    $(document).on(
        "click",
        ".btn-remove-detail",
        function () {

            $(this)
                .closest("tr")
                .remove();

            recalculateSummary();

        }
    );

    $(document).on(
        "change",
        ".detail-category",
        function () {

            const selected =
                $(this).select2("data")[0];

            const row =
                $(this).closest("tr");

            const group =
                selected?.group_category ?? "-";

            row.find(".detail-group")
                .text(group);

            row.find(".detail-group-hidden")
                .val(group);

            recalculateSummary();

        }
    );
}

function appendDetailRow(data = null) {

    const index = detailIndex++;

    const html = `
        <tr class="
            detail-item-row
            border-b border-slate-100
            dark:border-white/[0.06]
            transition
            hover:bg-slate-50
            dark:hover:bg-white/[0.02]
        ">

            <td class="px-4 py-3 align-middle">

                <select
                    name="details[${index}][categoryid]"
                    class="detail-category select2 w-full">

                    <option value="">
                        Choose Category
                    </option>

                </select>

            </td>

            <td class="px-4 py-3 align-middle">

                <div class="
                    detail-group
                    flex h-11 min-w-[150px]
                    items-center

                    rounded-lg

                    border border-slate-200
                    dark:border-white/[0.06]

                    bg-slate-50
                    dark:bg-[#0b1525]

                    px-4

                    text-sm font-medium

                    text-slate-700
                    dark:text-slate-200
                ">
                    -
                </div>

                <input
                    type="hidden"
                    name="details[${index}][group_category]"
                    class="detail-group-hidden">

            </td>

            <td class="px-4 py-3 text-right align-middle">

                <button
                    type="button"
                    class="
                        btn-remove-detail

                        inline-flex h-11 w-11
                        items-center justify-center

                        rounded-lg

                        border border-red-200
                        dark:border-red-500/20

                        bg-red-50
                        dark:bg-red-500/10

                        text-red-600
                        dark:text-red-300

                        transition

                        hover:bg-red-100
                        dark:hover:bg-red-500/20

                        hover:scale-[1.03]
                    ">

                    <i class="fa-solid fa-trash"></i>

                </button>

            </td>

        </tr>
    `;

    $("#requestDetailContainer")
        .append(html);

    const row = $("#requestDetailContainer")
        .find(".detail-item-row")
        .last();

    const select =
        row.find(".detail-category");

    select.select2({

        width: "100%",

        dropdownParent: $("#requestModal"),

        placeholder: "Choose Category",

        allowClear: true,

        ajax: {

            url: "/access-request/category-search",

            dataType: "json",

            delay: 250,

            data: function (params) {

                return {
                    q: params.term,
                };
            },

            processResults: function (data) {

                return {

                    results:
                        data.results.map((item) => ({

                            id: item.id,

                            text: item.text,

                            group_category:
                                item.group_category,

                            categoryid:
                                item.categoryid,

                            category_name:
                                item.category_name,

                        })),
                };
            },

            cache: true,
        },
    });

    if (data) {

        const option = new Option(
            data.category_name,
            data.categoryid,
            true,
            true
        );

        select
            .append(option)
            .trigger("change");

        row.find(".detail-group")
            .text(data.group_category ?? "-");

        row.find(".detail-group-hidden")
            .val(data.group_category ?? "");
    }

    recalculateSummary();
}

function recalculateSummary() {

    const rows =
        $(".detail-item-row");

    let total = rows.length;

    let hardware = 0;

    let software = 0;

    rows.each(function () {

        const group = $(this)
            .find(".detail-group-hidden")
            .val();

        if (group === "HARDWARE") {
            hardware++;
        }

        if (group === "SOFTWARE") {
            software++;
        }
    });

    $("#summaryTotalItem")
        .text(total);

    $("#summaryHardware")
        .text(hardware);

    $("#summarySoftware")
        .text(software);
}

async function submitRequestForm() {

    const accessDate =
        $("#access_date").val();

    const accessType =
        $("#access_type").val();

    const cpnyId =
        $("#cpny_id").val();

    const departmentId =
        $("#department_id").val();

    const keperluan =
        $("#keperluan")
            .val()
            .trim();

    const detailRows =
        $(".detail-item-row");

    if (!accessDate) {
        swalWarning("Request date is required.");
        return;
    }

    if (!accessType) {
        swalWarning("Request type is required.");
        return;
    }

    if (!cpnyId) {
        swalWarning("Company is required.");
        return;
    }

    if (!departmentId) {
        swalWarning("Department is required.");
        return;
    }

    if (!keperluan) {
        swalWarning("Purpose / Notes is required.");
        return;
    }

    if (detailRows.length === 0) {
        swalWarning("Please add at least one request item.");
        return;
    }

    let invalidDetail = false;

    detailRows.each(function () {

        const category =
            $(this)
                .find(".detail-category")
                .val();

        if (!category) {

            invalidDetail = true;

            $(this)
                .find(".select2-selection")
                .addClass("!border-red-500");

        } else {

            $(this)
                .find(".select2-selection")
                .removeClass("!border-red-500");
        }
    });

    if (invalidDetail) {
        swalWarning("Please select category for all request items.");
        return;
    }

    const form =
        $("#requestForm")[0];

    const formData =
        new FormData(form);

    formData.append(
        "existing_attachments",
        JSON.stringify(existingAttachments)
    );

    selectedFiles.forEach((file) => {

        formData.append(
            "attachments[]",
            file
        );

    });

    const btn = $("#btnSubmitRequest");

    btn.prop("disabled", true)
        .addClass("opacity-60 cursor-not-allowed")
        .html(`
            <div class="
                h-4 w-4 animate-spin rounded-lg
                border-2 border-white/40 border-t-white
            "></div>

            Submitting...
        `);

    try {

        const res = await $.ajax({

            url:
                $("#requestUrl").val() ||
                "/access-request/store",

            type:
                $("#requestMethod").val() ||
                "POST",

            data: formData,

            processData: false,

            contentType: false,

        });

        Swal.fire({
            icon: "success",
            title: "Success",
            text: res.message ?? "Request submitted successfully.",
            timer: 1800,
            showConfirmButton: false,
        });

        selectedFiles = [];

        existingAttachments = [];

        closeAllModal();

        resetRequestForm();

        table.ajax.reload(null, false);

    } catch (xhr) {

        let msg = "Failed to submit request.";

        if (xhr.responseJSON?.message) {
            msg = xhr.responseJSON.message;
        }

        if (xhr.status === 422 && xhr.responseJSON?.errors) {
            msg = Object.values(xhr.responseJSON.errors).flat().join("<br>");
        }

        Swal.fire({
            icon: "error",
            title: "Validation Error",
            html: msg,
        });

    } finally {

        btn.prop("disabled", false)
            .removeClass("opacity-60 cursor-not-allowed")
            .html(`
                <i class="fa-solid fa-paper-plane text-xs"></i>
                Submit Request
            `);

    }
}

async function openEditModal(id) {

    resetRequestForm();

    $("#requestModalTitle").text(
        "Edit Access Request"
    );

    $("#requestMethod").val("POST");

    $("#requestUrl").val(
        `/access-request/update/${id}`
    );

    $("#requestHash").val(id);

    openModal("#requestModal");

    const btn = $("#btnSubmitRequest");

    btn.prop("disabled", true)
        .addClass("opacity-60 cursor-not-allowed")
        .html(`
            <div class="
                h-4 w-4 animate-spin rounded-lg
                border-2 border-white/40 border-t-white
            "></div>

            Loading...
        `);

    try {

        const res = await $.ajax({
            url: `/access-request/detail/${id}`,
            type: "GET",
        });

        const access =
            res.access ?? {};

        const details =
            res.details ?? [];

        const attachments =
            (res.attachments ?? []).filter(
                (file, index, self) =>
                    index === self.findIndex((f) => f.url === file.url)
            );

        $("#access_date").val(
            access.access_date
                ? access.access_date.split(" ")[0]
                : ""
        );

        $("#access_type")
            .val(access.access_type)
            .trigger("change");

        $("#cpny_id")
            .val(access.cpny_id)
            .trigger("change");

        $("#department_id")
            .val(access.department_id)
            .trigger("change");

        $("#keperluan").val(
            access.keperluan ?? ""
        );

        $("#requestDetailContainer").html("");

        details.forEach((item) => {

            appendDetailRow({
                categoryid:    item.access_id,
                category_name: item.access_descr,
                group_category: item.group_category,
            });

        });

        existingAttachments = attachments;

        renderExistingAttachments(existingAttachments);

        recalculateSummary();

    } catch (xhr) {

        swalError(
            xhr.responseJSON?.message ?? "Failed load edit data"
        );

        closeAllModal();

    } finally {

        btn.prop("disabled", false)
            .removeClass("opacity-60 cursor-not-allowed")
            .html(`
                <i class="fa-solid fa-paper-plane text-xs"></i>
                Submit Request
            `);

    }
}
