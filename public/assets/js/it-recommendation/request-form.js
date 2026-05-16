$(document).on("click", 'a[href*="/createitrecommendation"]', function (e) {
    e.preventDefault();

    openCreateModal();
});

$("#ticketnbr").on("select2:open", function () {
    if (!$(this).data("loaded")) {
        $(this).data("loaded", true);
        $(this).select2("trigger", "query", { term: "" });
    }
});

$(".select2").select2({
    width: "100%",
    dropdownParent: $("#createModal"),
    placeholder: "Select option",
    allowClear: true,
});

$("#ticketnbr").select2({
    dropdownParent: $("#createModal"),
    placeholder: "Search Ticket...",
    allowClear: true,
    minimumInputLength: 0,

    ajax: {
        url: "/it-recommendation/ticket-search",
        dataType: "json",
        delay: 300,
        data: (params) => ({
            q: params.term || "",
        }),
        processResults: (data) => ({
            results: data.map((item) => ({
                id: item.ticketid,
                text: `${item.ticketid} - ${item.issue_summary}`,
            })),
        }),
    },
});

$(document).on("click", 'a[href*="/edititrecommendation/"]', function (e) {
    e.preventDefault();

    const hash = $(this).attr("href").split("/").pop();

    window.history.pushState({}, "", $(this).attr("href"));

    openEditModal(hash);
});

$("#btnCloseCreateModal, #btnCancelCreate").on("click", function () {
    closeCreateModal();
});

$("#createForm").on("submit", function (e) {
    e.preventDefault();

    const btn = $("#btnSubmitCreate");

    btn.prop("disabled", true).html(`
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                    Submitting...
                `);

    $("#create_cpny_id").prop("disabled", false);
    $("#create_department_id").prop("disabled", false);

    const formData = new FormData(this);
    if (editMode) {
        formData.append("_method", "PUT");
    }

    $.ajax({
        url: editMode
            ? `/it-recommendation/update/${editHash}`
            : "{{ route('it-recommendation.store') }}",

        type: "POST",

        data: formData,

        processData: false,
        contentType: false,

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        success: function (res) {
            Swal.fire({
                icon: "success",
                title: "Success",
                text: res.message || "Request saved successfully",
                timer: 1800,
                showConfirmButton: false,
            });

            closeCreateModal();

            table.ajax.reload(null, false);
        },

        error: function (xhr) {
            let msg = "Failed to save request";

            if (xhr.responseJSON?.message) {
                msg = xhr.responseJSON.message;
            }

            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors)
                    .flat()
                    .join("<br>");
            }

            Swal.fire({
                icon: "error",
                title: "Validation Error",
                html: msg,
            });
        },

        complete: function () {
            btn.prop("disabled", false).html(`
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    ${editMode ? "Update Request" : "Submit Request"}
                `);
        },
    });
});

const path = window.location.pathname;

if (path.includes("/createitrecommendation")) {
    openCreateModal();
}

$("#btnCancelRequest").on("click", async function () {
    if (!editHash) return;

    const result = await Swal.fire({
        icon: "warning",
        title: "Cancel Document?",
        text: "This document will be cancelled, and this action cannot be undone !",
        showCancelButton: true,
        confirmButtonText: "Yes, Cancel",
        confirmButtonColor: "#dc2626",
    });

    if (!result.isConfirmed) return;

    try {
        await $.ajax({
            url: `/it-recommendation/cancel/${editHash}`,
            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Document cancelled",
            timer: 1500,
            showConfirmButton: false,
        });

        closeCreateModal();

        table.ajax.reload(null, false);
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed cancel document",
        });
    }
});

function openCreateModal() {
    $("#createModal").removeClass("hidden").addClass("flex");

    $("body").addClass("overflow-hidden");

    $("#createmodaltitle").text(
        editMode ? "Edit IT Recommendation" : "Create IT Recommendation",
    );

    $("#createmodaldesc").text(
        editMode
            ? "Update IT recommendation request"
            : "Submit request for IT recommendation process",
    );

    $("#btnSubmitCreate").html(
        editMode
            ? '<i class="fa-solid fa-floppy-disk mr-2"></i>Update Request'
            : '<i class="fa-solid fa-paper-plane mr-2"></i>Submit Request',
    );
    if (editMode && ["D"].includes(editStatus)) {
        $("#btnCancelRequest").removeClass("hidden").addClass("inline-flex");
    } else {
        $("#btnCancelRequest").removeClass("inline-flex").addClass("hidden");
    }
}

function closeCreateModal() {
    editMode = false;
    editHash = null;
    editStatus = null;
    $("#createModal").removeClass("flex").addClass("hidden");

    if (
        $("#createModal").hasClass("hidden") &&
        $("#showModal").hasClass("hidden") &&
        $("#processModal").hasClass("hidden") &&
        $("#editRecommendationModal").hasClass("hidden")
    ) {
        $("body").removeClass("overflow-hidden");
    }

    $("#createForm")[0].reset();
    if ($("#create_cpny_id option").length === 2) {
        $("#create_cpny_id").val($("#create_cpny_id option:eq(1)").val());
    }

    if ($("#create_department_id option").length === 2) {
        $("#create_department_id").val(
            $("#create_department_id option:eq(1)").val(),
        );
    }

    $("#createAttachmentPreview").html("");
}

async function openEditModal(hash) {
    try {
        const res = await $.ajax({
            url: `/it-recommendation/detail/${hash}`,
            type: "GET",
        });

        const h = res.header;
        if (h.ticketnbr) {
            const option = new Option(h.ticketnbr, h.ticketnbr, true, true);
            $("#ticketnbr").append(option).trigger("change");
        }

        editMode = true;
        editHash = hash;
        editStatus = h.status;

        $("#create_cpny_id").val(h.cpny_id).trigger("change");
        $("#create_department_id").val(h.department_id);
        $("#ticketnbr").val(h.ticketnbr).trigger("change");
        $("#create_assetnbr").val(h.assetnbr);
        $("#create_keperluan").val(h.keperluan);

        openCreateModal();
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed load edit data",
        });
    }
}
