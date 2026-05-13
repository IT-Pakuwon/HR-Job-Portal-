$(document).on("click", ".btn-approve", async function () {

    const docid = $(this).data("doc");

    const confirm = await Swal.fire({
        title: "Approve Request?",
        text: "This request will continue to next approval.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Approve",
        confirmButtonColor: "#059669",
        reverseButtons: true,
    });

    if (!confirm.isConfirmed) {
        return;
    }

    $.ajax({
        url: `/access-request/approve/${docid}`,
        type: "POST",

        success: function (res) {

            swalSuccess(
                res.message ?? "Request approved successfully"
            );

            closeAllModal();

            table.ajax.reload(null, false);

        },

        error: function (xhr) {

            swalError(
                xhr.responseJSON?.message ??
                "Failed approve request"
            );

        },
    });

});

$(document).on("click", ".btn-revise", async function () {

    const docid = $(this).data("doc");

    const result = await Swal.fire({
        title: "Revise Request",
        input: "textarea",
        inputPlaceholder: "Input revise reason...",
        inputAttributes: {
            rows: 4,
        },

        icon: "warning",

        showCancelButton: true,

        confirmButtonText: "Submit Revise",

        confirmButtonColor: "#f59e0b",

        reverseButtons: true,

        inputValidator: (value) => {

            if (!value) {
                return "Revise reason is required";
            }

        },
    });

    if (!result.isConfirmed) {
        return;
    }

    $.ajax({
        url: `/access-request/revise/${docid}`,
        type: "POST",

        data: {
            reason: result.value,
        },

        success: function (res) {

            swalSuccess(
                res.message ?? "Request revised successfully"
            );

            closeAllModal();

            table.ajax.reload(null, false);

        },

        error: function (xhr) {

            swalError(
                xhr.responseJSON?.message ??
                "Failed revise request"
            );

        },
    });

});

$(document).on("click", ".btn-reject", async function () {

    const docid = $(this).data("doc");

    const result = await Swal.fire({
        title: "Reject Request",

        input: "textarea",

        inputPlaceholder: "Input reject reason...",

        inputAttributes: {
            rows: 4,
        },

        icon: "warning",

        showCancelButton: true,

        confirmButtonText: "Reject",

        confirmButtonColor: "#dc2626",

        reverseButtons: true,

        inputValidator: (value) => {

            if (!value) {
                return "Reject reason is required";
            }

        },
    });

    if (!result.isConfirmed) {
        return;
    }

    $.ajax({
        url: `/access-request/reject/${docid}`,
        type: "POST",

        data: {
            reason: result.value,
        },

        success: function (res) {

            swalSuccess(
                res.message ?? "Request rejected successfully"
            );

            closeAllModal();

            table.ajax.reload(null, false);

        },

        error: function (xhr) {

            swalError(
                xhr.responseJSON?.message ??
                "Failed reject request"
            );

        },
    });

});
