function initCreateSelect2() {
    $(".select2").select2({
        width: "100%",
        dropdownParent: $("#createModal"),
        placeholder: "Select option",
        allowClear: true,
    });

    $("#ticketnbr").select2({
        width: "100%",
        dropdownParent: $("#createModal"),
        placeholder: "Search Ticket...",
        allowClear: true,
        minimumInputLength: 0,

        ajax: {
            url: "/it-recommendation/ticket-search",
            dataType: "json",
            delay: 300,

            data: function (params) {
                return {
                    q: params.term || "",
                };
            },

            processResults: function (data) {
                return {
                    results: data.map((item) => ({
                        id: item.ticketid,
                        text: `${item.ticketid} - ${item.issue_summary}`,
                    })),
                };
            },
        },
    });
}
function resetCreateForm() {
    $("#createForm")[0].reset();

    $(".select2").val(null).trigger("change");

    $("#ticketnbr").empty().trigger("change");

    if ($("#create_cpny_id option").length === 2) {
        $("#create_cpny_id")
            .val($("#create_cpny_id option:eq(1)").val())
            .trigger("change");
    }

    if ($("#create_department_id option").length === 2) {
        $("#create_department_id")
            .val($("#create_department_id option:eq(1)").val())
            .trigger("change");
    }

    $("#show_notes").html("");

    resetCreateAttachments();

    existingAttachments = [];

    deletedAttachmentIds = [];
}

function updateCreateModalContent() {
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
            ? `
                <i class="fa-solid fa-floppy-disk mr-2"></i>
                Update Request
            `
            : `
                <i class="fa-solid fa-paper-plane mr-2"></i>
                Submit Request
            `,
    );

    if (editMode && ["D"].includes(editStatus)) {
        $("#btnCancelRequest").removeClass("hidden").addClass("inline-flex");
    } else {
        $("#btnCancelRequest").removeClass("inline-flex").addClass("hidden");
    }
}

function openCreateModal() {
    animateOpenModal("#createModal");

    updateCreateModalContent();
}

async function closeCreateModal(force = false) {
    if (!force && modalState.createDirty) {
        const confirmed = await confirmCloseModal();

        if (!confirmed) {
            return;
        }
    }

    editMode = false;

    editHash = null;

    editStatus = null;

    animateCloseModal("#createModal", function () {
        resetCreateForm();

        resetCreateModalState();

        window.history.pushState({}, "", "/it-recommendation");
    });
}

async function openEditModal(hash) {
    try {

        const res = await $.ajax({
            url: `/it-recommendation/detail/${hash}`,
            type: "GET",
        });

        const header = res.header;

        existingAttachments =
            res.attachments || [];

        createSelectedFiles = [];

        renderCreateAttachmentPreview();

        editMode = true;
        editHash = hash;
        editStatus = header.status;

        $("#create_cpny_id").val(header.cpny_id).trigger("change");
        $("#create_department_id").val(header.department_id).trigger("change");

        if (header.ticketnbr) {

            const option = new Option(
                header.ticketnbr,
                header.ticketnbr,
                true,
                true
            );

            $("#ticketnbr")
                .append(option)
                .trigger("change");
        }

        $("#create_assetnbr").val(header.assetnbr);
        $("#create_keperluan").val(header.keperluan);

        // Show the most recent revision/rejection note (already in the detail response)
        const revisionNotes = res.permissions?.notes || [];
        const reviseEntry = revisionNotes.find(n => n.note);
        if (reviseEntry?.note) {
            $("#show_notes").html(`
                <div class="rounded-lg border border-orange-200 dark:border-orange-500/20 bg-orange-50 dark:bg-orange-500/10 px-4 py-3 text-sm text-orange-700 dark:text-orange-300">
                    <p class="mb-1 font-semibold">Revision Note</p>
                    <p>${reviseEntry.note}</p>
                </div>
            `);
        } else {
            $("#show_notes").html("");
        }

        openCreateModal();

    } catch (err) {

        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed load edit data",
        });
    }
}

async function submitCreateForm(form) {
    const formData = new FormData(form);

    if (editMode) {
        formData.append("_method", "PUT");
    }

    return $.ajax({
        url: editMode
            ? `/it-recommendation/update/${editHash}`
            : window.ITRecommendationRoutes.store,

        type: "POST",

        data: formData,

        processData: false,

        contentType: false,

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
}

$(document).on("click", 'a[href*="/createitrecommendation"]', function (e) {
    e.preventDefault();

    openCreateModal();
});

$(document).on("click", 'a[href*="/edititrecommendation/"]', function (e) {
    e.preventDefault();

    const href = $(this).attr("href");

    const hash = href.split("/").pop();

    window.history.pushState({}, "", href);

    openEditModal(hash);
});

$("#btnCloseCreateModal, #btnCancelCreate").on("click", function () {
    closeCreateModal();
});

$("#ticketnbr").on("select2:open", function () {
    const el = $(this);

    if (!el.data("loaded")) {
        el.data("loaded", true);

        el.select2("trigger", "query", {
            term: "",
        });
    }
});

$("#createForm").on("submit", async function (e) {
    e.preventDefault();

    const btn = $("#btnSubmitCreate");

    btn.prop("disabled", true).html(`
        <i class="fa-solid fa-spinner fa-spin mr-2"></i>
        Submitting...
    `);

    $("#create_cpny_id").prop("disabled", false);

    $("#create_department_id").prop("disabled", false);

    try {
        const res = await submitCreateForm(this);

        Swal.fire({
            icon: "success",
            title: "Success",
            text: res.message || "Request saved successfully",
            timer: 1800,
            showConfirmButton: false,
        });

        closeCreateModal(true);

        table.ajax.reload(null, false);
    } catch (xhr) {
        let msg = "Failed to save request";

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
        btn.prop("disabled", false).html(
            editMode
                ? `
                    <i class="fa-solid fa-floppy-disk mr-2"></i>
                    Update Request
                `
                : `
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    Submit Request
                `,
        );
    }
});

initCreateSelect2();

if (window.location.pathname.includes("/createitrecommendation")) {
    openCreateModal();
}
