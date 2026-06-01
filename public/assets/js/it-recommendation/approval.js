async function approvalAction({
    url,
    title,
    text = null,
    input = null,
    inputPlaceholder = null,
    confirmText,
    confirmColor = "#2563eb",
    successTitle,
    errorText,
}) {
    const result = await Swal.fire({
        icon: input ? undefined : "question",

        title,

        text,

        input,

        inputPlaceholder,

        inputAttributes: input
            ? {
                  required: true,
              }
            : undefined,

        showCancelButton: true,

        confirmButtonText: confirmText,

        confirmButtonColor: confirmColor,
    });

    if (!result.isConfirmed || (input && !result.value)) {
        return;
    }

    try {
        await $.ajax({
            url,

            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            data: input
                ? {
                      note: result.value,
                  }
                : {},
        });

        itrToast('success', successTitle);

        table.ajax.reload(null, false);

        if (currentDetailHash) {
            await loadDetail(currentDetailHash);
        }
    } catch (err) {
        Swal.fire({
            icon: "error",

            title: "Error",

            text: err.responseJSON?.message || errorText,
        });
    }
}

$(document).on("click", ".approve-btn", async function () {
    const docid = $(this).data("docid");

    approvalAction({
        url: `/it-recommendation/approve/${docid}`,

        title: "Approve Document?",

        text: "This action cannot be undone",

        confirmText: "Approve",

        confirmColor: "#16a34a",

        successTitle: "Approved",

        errorText: "Failed approve document",
    });
});

$(document).on("click", ".revise-approval-btn", async function () {
    const docid = $(this).data("docid");

    approvalAction({
        url: `/it-recommendation/revise/${docid}`,

        title: "Request Revision",

        input: "textarea",

        inputPlaceholder: "Write revise reason...",

        confirmText: "Submit Revision",

        successTitle: "Revision Requested",

        errorText: "Failed revise document",
    });
});

$(document).on("click", ".reject-approval-btn", async function () {
    const docid = $(this).data("docid");

    approvalAction({
        url: `/it-recommendation/reject/${docid}`,

        title: "Reject Document",

        input: "textarea",

        inputPlaceholder: "Write reject reason...",

        confirmText: "Reject",

        confirmColor: "#dc2626",

        successTitle: "Rejected",

        errorText: "Failed reject document",
    });
});
