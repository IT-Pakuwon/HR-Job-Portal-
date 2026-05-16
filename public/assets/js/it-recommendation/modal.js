function openShowModal() {
    $("#showModal").removeClass("hidden").addClass("flex");

    $("body").addClass("overflow-hidden");
}

function closeShowModal() {
    $("#showModal").removeClass("flex").addClass("hidden");

    if (
        $("#createModal").hasClass("hidden") &&
        $("#showModal").hasClass("hidden") &&
        $("#processModal").hasClass("hidden") &&
        $("#editRecommendationModal").hasClass("hidden")
    ) {
        $("body").removeClass("overflow-hidden");
    }

    $("#show_docid").text("-");
    $("#show_status_badge").html("");
    $("#show_information").html("");
    $("#show_detail_items").html("");
    $("#show_attachments").html("");
    $("#show_tracking").html("");

    $("#show_header_actions").addClass("hidden").html("");

    $("#commentSection").removeClass("hidden");

    $("#show_comments").html("");

    const cleanUrl = '/it-recommendation';

    window.history.pushState({}, "", cleanUrl);
}

function openProcessModal() {
    $("#processModal").removeClass("hidden").addClass("flex");

    $("body").addClass("overflow-hidden");
}

function closeProcessModal() {
    $("#processModal").removeClass("flex").addClass("hidden");

    if (
        $("#createModal").hasClass("hidden") &&
        $("#showModal").hasClass("hidden") &&
        $("#processModal").hasClass("hidden") &&
        $("#editRecommendationModal").hasClass("hidden")
    ) {
        $("body").removeClass("overflow-hidden");
    }

    $("#processForm")[0].reset();

    $("#process_hash").val("");
    $("#process_docid").text("Process IT Recommendation");
    $("#process_information").html("");
    $("#process_attachments").html("");
    $("#process_detail_body").html("");

    const cleanUrl = '/it-recommendation';

    window.history.pushState({}, "", cleanUrl);
}

function openEditRecommendationModal() {
    $("#showModal").removeClass("flex").addClass("hidden");

    $("#processModal").removeClass("flex").addClass("hidden");

    $("#editRecommendationModal").removeClass("hidden").addClass("flex");

    $("body").addClass("overflow-hidden");
}

function closeEditRecommendationModal() {
    $("#editRecommendationModal").removeClass("flex").addClass("hidden");

    if (
        $("#createModal").hasClass("hidden") &&
        $("#showModal").hasClass("hidden") &&
        $("#processModal").hasClass("hidden") &&
        $("#editRecommendationModal").hasClass("hidden")
    ) {
        $("body").removeClass("overflow-hidden");
    }

    $("#editRecommendationForm")[0].reset();

    $("#edit_recommendation_hash").val("");
    $("#edit_recommendation_information").html("");
    $("#edit_recommendation_detail_body").html("");
    $("#revision_note_container").html("");

    const cleanUrl = '/it-recommendation';

    window.history.pushState({}, "", cleanUrl);

    table.ajax.reload(null, false);
}
