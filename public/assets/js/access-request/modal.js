   function openModal(selector) {
    currentModal = selector;

    $(selector).removeClass("hidden").addClass("flex");

    $("body").addClass("overflow-hidden");
}
function closeAllModal() {
    currentModal = null;

    $("#requestModal").removeClass("flex").addClass("hidden");
    $("#detailModal").removeClass("flex").addClass("hidden");
    $("#processHardwareModal").removeClass("flex").addClass("hidden");
    $("#processSoftwareModal").removeClass("flex").addClass("hidden");

    $("body").removeClass("overflow-hidden");

    // RESET URL
    window.history.replaceState({}, document.title, "/access-request");
}

function initAutoOpenModal() {
    const path = window.location.pathname;

    if (path.includes("/showaccessrequest/")) {
        const eid = path.split("/showaccessrequest/")[1];

        if (eid) {
            openDetailModal(eid);
        }
    }

    if (path.includes("/editaccessrequest/")) {
        const eid = path.split("/editaccessrequest/")[1];

        if (eid) {
            openEditModal(eid);
        }
    }

    if (path.includes("/processhardwareaccess/")) {
        const eid = path.split("/processhardwareaccess/")[1];

        if (eid) {
            openModal("#processHardwareModal");
        }
    }

    if (path.includes("/processsoftwareaccess/")) {
        const eid = path.split("/processsoftwareaccess/")[1];

        if (eid) {
            openModal("#processSoftwareModal");
        }
    }
}

function initModalHandlers() {
    $("#btnCreate").on("click", function () {
        openModal("#requestModal");
    });
}

$(document).on("click", ".btn-close-modal", function () {
    const modal = $(this).closest('[id$="Modal"]');

    const modalId = modal.attr("id");

    // DETAIL / VIEW ONLY
    if (
        modalId === "detailModal" ||
        modalId === "processHardwareModal" ||
        modalId === "processSoftwareModal"
    ) {
        closeAllModal();

        return;
    }

    // FORM MODAL ONLY
    Swal.fire({
        title: "Close Form?",
        text: "Unsaved changes will be lost.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, Close",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#dc2626",
        reverseButtons: true,
        background: $("html").hasClass("dark") ? "#0f172a" : "#ffffff",
        color: $("html").hasClass("dark") ? "#ffffff" : "#0f172a",
        customClass: {
            popup: "rounded-lg",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            resetRequestForm();

            closeAllModal();
        }
    });
});
