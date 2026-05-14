$(document).on("click", ".btn-approve", async function () {

    const docid = $(this).data("doc");

    const isDark =
        $("html").hasClass("dark");

    const confirm = await Swal.fire({

        title: "Approve Request?",

        text:
            "This request will continue to next approval.",

        icon: "question",

        showCancelButton: true,

        confirmButtonText: "Approve",

        cancelButtonText: "Cancel",

        reverseButtons: true,

        confirmButtonColor: "#059669",

        cancelButtonColor:
            isDark
                ? "#334155"
                : "#e2e8f0",

        background:
            isDark
                ? "#111c2d"
                : "#ffffff",

        color:
            isDark
                ? "#f8fafc"
                : "#0f172a",

        customClass: {

            popup: `
                rounded-2xl
                border border-white/[0.06]
                shadow-[0_25px_80px_rgba(0,0,0,.35)]
            `,

            title: `
                text-lg font-bold
            `,

            htmlContainer: `
                text-sm text-slate-500
            `,

            confirmButton: `
                rounded-xl
                px-5 py-3
                text-sm font-semibold
            `,

            cancelButton: `
                rounded-xl
                px-5 py-3
                text-sm font-semibold
            `,
        },

    });

    if (!confirm.isConfirmed) {
        return;
    }

    $.ajax({

        url: `/access-request/approve/${docid}`,

        type: "POST",

        beforeSend: function () {

            Swal.showLoading();

        },

        success: function (res) {

            Swal.close();

            swalSuccess(
                res.message ??
                "Request approved successfully"
            );

            closeAllModal();

            table.ajax.reload(null, false);

        },

        error: function (xhr) {

            Swal.close();

            swalError(

                xhr.responseJSON?.message ??

                "Failed approve request"

            );

        },

    });

});

$(document).on("click", ".btn-revise", async function () {

    const docid = $(this).data("doc");

    const isDark =
        $("html").hasClass("dark");

    const result = await Swal.fire({

        title: "Revise Request",

        input: "textarea",

        inputPlaceholder:
            "Input revise reason...",

        inputAttributes: {

            rows: 4,

        },

        icon: "warning",

        showCancelButton: true,

        confirmButtonText: "Submit Revise",

        cancelButtonText: "Cancel",

        reverseButtons: true,

        confirmButtonColor: "#f59e0b",

        cancelButtonColor:
            isDark
                ? "#334155"
                : "#e2e8f0",

        background:
            isDark
                ? "#111c2d"
                : "#ffffff",

        color:
            isDark
                ? "#f8fafc"
                : "#0f172a",

        customClass: {

            popup: `
                rounded-2xl
                border border-white/[0.06]
                shadow-[0_25px_80px_rgba(0,0,0,.35)]
            `,

            title: `
                text-lg font-bold
            `,

            input: `
                rounded-xl
                border border-slate-200
                dark:border-white/[0.08]

                bg-white
                dark:bg-[#0b1525]

                text-slate-700
                dark:text-slate-100

                placeholder:text-slate-400
                dark:placeholder:text-slate-500

                focus:border-amber-500/40
                focus:ring-4
                focus:ring-amber-500/10
            `,

            confirmButton: `
                rounded-xl
                px-5 py-3
                text-sm font-semibold
            `,

            cancelButton: `
                rounded-xl
                px-5 py-3
                text-sm font-semibold
            `,
        },

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

        beforeSend: function () {

            Swal.showLoading();

        },

        success: function (res) {

            Swal.close();

            swalSuccess(

                res.message ??

                "Request revised successfully"

            );

            closeAllModal();

            table.ajax.reload(null, false);

        },

        error: function (xhr) {

            Swal.close();

            swalError(

                xhr.responseJSON?.message ??

                "Failed revise request"

            );

        },

    });

});

$(document).on("click", ".btn-reject", async function () {

    const docid = $(this).data("doc");

    const isDark =
        $("html").hasClass("dark");

    const result = await Swal.fire({

        title: "Reject Request",

        input: "textarea",

        inputPlaceholder:
            "Input reject reason...",

        inputAttributes: {

            rows: 4,

        },

        icon: "warning",

        showCancelButton: true,

        confirmButtonText: "Reject",

        cancelButtonText: "Cancel",

        reverseButtons: true,

        confirmButtonColor: "#dc2626",

        cancelButtonColor:
            isDark
                ? "#334155"
                : "#e2e8f0",

        background:
            isDark
                ? "#111c2d"
                : "#ffffff",

        color:
            isDark
                ? "#f8fafc"
                : "#0f172a",

        customClass: {

            popup: `
                rounded-2xl
                border border-white/[0.06]
                shadow-[0_25px_80px_rgba(0,0,0,.35)]
            `,

            title: `
                text-lg font-bold
            `,

            input: `
                rounded-xl
                border border-slate-200
                dark:border-white/[0.08]

                bg-white
                dark:bg-[#0b1525]

                text-slate-700
                dark:text-slate-100

                placeholder:text-slate-400
                dark:placeholder:text-slate-500

                focus:border-red-500/40
                focus:ring-4
                focus:ring-red-500/10
            `,

            confirmButton: `
                rounded-xl
                px-5 py-3
                text-sm font-semibold
            `,

            cancelButton: `
                rounded-xl
                px-5 py-3
                text-sm font-semibold
            `,
        },

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

        beforeSend: function () {

            Swal.showLoading();

        },

        success: function (res) {

            Swal.close();

            swalSuccess(

                res.message ??

                "Request rejected successfully"

            );

            closeAllModal();

            table.ajax.reload(null, false);

        },

        error: function (xhr) {

            Swal.close();

            swalError(

                xhr.responseJSON?.message ??

                "Failed reject request"

            );

        },

    });

});
