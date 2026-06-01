const modalState = {
    createDirty: false,

    processDirty: false,

    reviseDirty: false,
};

function toggleBodyScroll() {
    const hasOpenModal = [
        "#createModal",
        "#showModal",
        "#processModal",
        "#editRecommendationModal",
    ].some((selector) => {
        return !$(selector).hasClass("hidden");
    });

    $("body").toggleClass("overflow-hidden", hasOpenModal);
}

function animateOpenModal(target) {
    const modal = $(target);

    modal.removeClass("hidden").addClass("flex");

    requestAnimationFrame(() => {
        modal.find(".modal-backdrop").removeClass("opacity-0");

        modal.find(".modal-panel").removeClass(`
                opacity-0
                translate-y-4
                scale-[0.98]
            `).addClass(`
                opacity-100
                translate-y-0
                scale-100
            `);
    });

    toggleBodyScroll();
}

function animateCloseModal(target, callback = null) {
    const modal = $(target);

    modal.find(".modal-backdrop").addClass("opacity-0");

    modal.find(".modal-panel").removeClass(`
            opacity-100
            translate-y-0
            scale-100
        `).addClass(`
            opacity-0
            translate-y-4
            scale-[0.98]
        `);

    setTimeout(() => {
        modal.removeClass("flex").addClass("hidden");

        toggleBodyScroll();

        if (typeof callback === "function") {
            callback();
        }
    }, 200);
}

async function confirmCloseModal() {
    const isDark = $("html").hasClass("dark");

    const result = await Swal.fire({
        title: "Close Form?",

        text: "Unsaved changes will be lost.",

        icon: "warning",

        showCancelButton: true,

        confirmButtonText: "Yes, Close",

        cancelButtonText: "Cancel",

        reverseButtons: true,

        confirmButtonColor: "#dc2626",

        cancelButtonColor: isDark ? "#334155" : "#e2e8f0",

        background: isDark ? "#111c2d" : "#ffffff",

        color: isDark ? "#ffffff" : "#0f172a",

        customClass: {
            popup: `
                rounded-lg
                border border-white/[0.06]
                shadow-[0_25px_80px_rgba(0,0,0,.35)]
            `,
            title: `text-lg font-bold`,
            htmlContainer: `text-sm text-slate-500`,
            confirmButton: `rounded-lg px-5 py-3 text-sm font-semibold`,
            cancelButton: `rounded-lg px-5 py-3 text-sm font-semibold`,
        },
    });

    return result.isConfirmed;
}

function resetUrl() {
    window.history.pushState({}, "", "/it-recommendation");
}

function resetShowModal() {
    $("#show_docid").text("IT Recommendation Detail");

    $("#show_status_badge").html("");

    $("#show_information").html("");

    $("#show_recommendation_info").html("");

    $("#show_detail_items").html("");

    $("#show_attachments").html("");

    $("#show_tracking").html("");

    $("#show_comments").html("");

    $("#show_footer_actions").html("");

    $("#show_approval_actions_wrapper").addClass("hidden").html("");

    $("#show_revision_banner").addClass("hidden");

    $("#show_revision_note").html("");

    $("#commentSection").removeClass("hidden");

    $("#comment_message").val("");
}

function resetProcessModal() {
    $("#processForm")[0].reset();

    $("#process_hash").val("");

    $("#process_docid").text("Process IT Recommendation");

    $("#process_information").html("");

    $("#process_attachments").html("");

    $("#process_detail_body").html("");

    modalState.processDirty = false;
}

function resetEditRecommendationModal() {
    $("#editRecommendationForm")[0].reset();

    $("#edit_recommendation_hash").val("");

    $("#edit_recommendation_information").html("");

    $("#edit_recommendation_attachments").html("");

    $("#edit_recommendation_detail_body").html("");

    $("#revision_note_container").html("");

    modalState.reviseDirty = false;
}

function resetCreateModalState() {
    modalState.createDirty = false;
}

function openShowModal() {
    animateOpenModal("#showModal");
}

async function closeShowModal(force = false) {

    currentDiscussionHash = null;

    $("#discussionPanel")
        .addClass("hidden");

    $("#discussionFab")
        .addClass("hidden");

    animateCloseModal(
        "#showModal",

        function () {

            resetShowModal();

            resetUrl();

        }
    );
}
function openProcessModal() {
    animateOpenModal("#processModal");
}

async function closeProcessModal(force = false) {
    if (!force) {
        const confirmed = await confirmCloseModal();

        if (!confirmed) {
            return;
        }
    }

    animateCloseModal(
        "#processModal",

        function () {
            resetProcessModal();

            resetUrl();
        },
    );
}

function openEditRecommendationModal() {
    $("#showModal").removeClass("flex").addClass("hidden");

    $("#processModal").removeClass("flex").addClass("hidden");

    animateOpenModal("#editRecommendationModal");
}

async function closeEditRecommendationModal(force = false) {
    if (!force) {
        const confirmed = await confirmCloseModal();

        if (!confirmed) {
            return;
        }
    }

    animateCloseModal(
        "#editRecommendationModal",

        function () {
            resetEditRecommendationModal();

            resetUrl();

            table.ajax.reload(null, false);
        },
    );
}

$(document).on(
    "input change",
    `
        #createForm input,
        #createForm textarea,
        #createForm select
    `,
    function () {
        modalState.createDirty = true;
    },
);

$(document).on(
    "input change",
    `
        #processForm input,
        #processForm textarea,
        #processForm select
    `,
    function () {
        modalState.processDirty = true;
    },
);

$(document).on(
    "input change",
    `
        #editRecommendationForm input,
        #editRecommendationForm textarea,
        #editRecommendationForm select
    `,
    function () {
        modalState.reviseDirty = true;
    },
);

$("#btnCloseShowModal, #btnCloseShowModalFooter").on("click", function () {
    closeShowModal();
});

$("#btnCloseProcessModal").on("click", function () {
    closeProcessModal();
});

$("#btnCloseEditRecommendationModal").on("click", function () {
    closeEditRecommendationModal();
});

$(document).on("click", "#showModal .modal-backdrop", function (e) {
    e.preventDefault();
});

$(document).on("click", "#processModal .modal-backdrop", function (e) {
    e.preventDefault();
});

$(document).on(
    "click",
    "#editRecommendationModal .modal-backdrop",
    function (e) {
        e.preventDefault();
    },
);

$(document).on("click", "#createModal .modal-backdrop", function (e) {
    e.preventDefault();
});

$(document).on("keydown", function (e) {
    if (e.key === "Escape") {
        e.preventDefault();
    }
});
