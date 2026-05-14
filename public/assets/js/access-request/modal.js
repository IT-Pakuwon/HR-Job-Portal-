function openModal(selector) {

    currentModal = selector;

    $(selector)
        .removeClass("hidden")
        .addClass("flex");

    $("body")
        .addClass("overflow-hidden");

    requestAnimationFrame(() => {

        $(selector)
            .find(".modal-panel")
            .removeClass(
                "opacity-0 translate-y-4 scale-[0.98]"
            )
            .addClass(
                "opacity-100 translate-y-0 scale-100"
            );

        $(selector)
            .find(".modal-backdrop")
            .removeClass("opacity-0")
            .addClass("opacity-100");
    });
}

function closeModal(selector) {

    const modal = $(selector);

    modal
        .find(".modal-panel")
        .removeClass(
            "opacity-100 translate-y-0 scale-100"
        )
        .addClass(
            "opacity-0 translate-y-4 scale-[0.98]"
        );

    modal
        .find(".modal-backdrop")
        .removeClass("opacity-100")
        .addClass("opacity-0");

    setTimeout(() => {

        modal
            .removeClass("flex")
            .addClass("hidden");

    }, 180);
}

function closeAllModal() {

    currentModal = null;

    [
        "#requestModal",
        "#detailModal",
        "#processHardwareModal",
        "#processSoftwareModal",
    ].forEach((selector) => {

        closeModal(selector);

    });

    $("body")
        .removeClass("overflow-hidden");

    window.history.replaceState(
        {},
        document.title,
        "/access-request"
    );
}

function initAutoOpenModal() {

    const path =
        window.location.pathname;

    if (
        path.includes(
            "/showaccessrequest/"
        )
    ) {

        const eid =
            path.split(
                "/showaccessrequest/"
            )[1];

        if (eid) {

            openDetailModal(eid);
        }
    }

    if (
        path.includes(
            "/editaccessrequest/"
        )
    ) {

        const eid =
            path.split(
                "/editaccessrequest/"
            )[1];

        if (eid) {

            openEditModal(eid);
        }
    }

    if (
        path.includes(
            "/processhardwareaccess/"
        )
    ) {

        const eid =
            path.split(
                "/processhardwareaccess/"
            )[1];

        if (eid) {

            openProcessHardwareModal(
                eid
            );
        }
    }

    if (
        path.includes(
            "/processsoftwareaccess/"
        )
    ) {

        const eid =
            path.split(
                "/processsoftwareaccess/"
            )[1];

        if (eid) {

            openProcessSoftwareModal(
                eid
            );
        }
    }
}

function initModalHandlers() {

    $("#btnCreate").on(
        "click",
        function () {

            resetRequestForm();

            openModal(
                "#requestModal"
            );

        }
    );

    $(document).on(
        "click",
        ".btn-close-modal",
        function () {

            const modal =
                $(this).closest(
                    '[id$="Modal"]'
                );

            const modalId =
                modal.attr("id");

            const isViewOnlyModal = [

                "detailModal",

                "processHardwareModal",

                "processSoftwareModal",

            ].includes(modalId);

            if (isViewOnlyModal) {

                closeAllModal();

                return;
            }

            const isDark =
                $("html")
                    .hasClass("dark");

            Swal.fire({

                title: "Close Form?",

                text:
                    "Unsaved changes will be lost.",

                icon: "warning",

                showCancelButton: true,

                confirmButtonText:
                    "Yes, Close",

                cancelButtonText:
                    "Cancel",

                reverseButtons: true,

                confirmButtonColor:
                    "#dc2626",

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
                        ? "#ffffff"
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

            }).then((result) => {

                if (
                    result.isConfirmed
                ) {

                    resetRequestForm();

                    closeAllModal();
                }
            });
        }
    );

    $(document).on(
        "click",
        ".modal-backdrop",
        function () {

            const modal =
                $(this).closest(
                    '[id$="Modal"]'
                );

            const modalId =
                modal.attr("id");

            const isViewOnlyModal = [

                "detailModal",

                "processHardwareModal",

                "processSoftwareModal",

            ].includes(modalId);

            if (isViewOnlyModal) {

                closeAllModal();

                return;
            }

            $(modal)
                .find(".btn-close-modal")
                .trigger("click");
        }
    );

    $(document).on(
        "keydown",
        function (e) {

            if (
                e.key === "Escape" &&
                currentModal
            ) {

                $(currentModal)
                    .find(
                        ".btn-close-modal"
                    )
                    .trigger("click");
            }
        }
    );
}
