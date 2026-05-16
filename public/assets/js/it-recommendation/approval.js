$(document).on("click", ".approve-btn", async function () {
    const docid = $(this).data("docid");

    const result = await Swal.fire({
        icon: "question",
        title: "Approve Document?",
        text: "This action cannot be undone",
        showCancelButton: true,
        confirmButtonText: "Approve",
        confirmButtonColor: "#16a34a",
    });

    if (!result.isConfirmed) return;

    try {
        await $.ajax({
            url: `/it-recommendation/approve/${docid}`,
            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        Swal.fire({
            icon: "success",
            title: "Approved",
            timer: 1500,
            showConfirmButton: false,
        });

        closeShowModal();

        table.ajax.reload(null, false);
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed approve document",
        });
    }
});

$(document).on("click", ".revise-approval-btn", async function () {
    const docid = $(this).data("docid");

    const result = await Swal.fire({
        title: "Request Revision",
        input: "textarea",
        inputPlaceholder: "Write revise reason...",
        inputAttributes: {
            required: true,
        },
        showCancelButton: true,
        confirmButtonText: "Submit Revision",
    });

    if (!result.isConfirmed || !result.value) return;

    try {
        await $.ajax({
            url: `/it-recommendation/revise/${docid}`,
            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            data: {
                note: result.value,
            },
        });

        Swal.fire({
            icon: "success",
            title: "Revision Requested",
            timer: 1500,
            showConfirmButton: false,
        });

        closeShowModal();

        table.ajax.reload(null, false);
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed revise document",
        });
    }
});

$(document).on("click", ".reject-approval-btn", async function () {
    const docid = $(this).data("docid");

    const result = await Swal.fire({
        title: "Reject Document",
        input: "textarea",
        inputPlaceholder: "Write reject reason...",
        inputAttributes: {
            required: true,
        },
        showCancelButton: true,
        confirmButtonText: "Reject",
        confirmButtonColor: "#dc2626",
    });

    if (!result.isConfirmed || !result.value) return;

    try {
        await $.ajax({
            url: `/it-recommendation/reject/${docid}`,
            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            data: {
                note: result.value,
            },
        });

        Swal.fire({
            icon: "success",
            title: "Rejected",
            timer: 1500,
            showConfirmButton: false,
        });

        closeShowModal();

        table.ajax.reload(null, false);
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed reject document",
        });
    }
});
