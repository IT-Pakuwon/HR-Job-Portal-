let table;
let currentStatus = "";
let currentModal = null;
let detailIndex = 0;

$(document).ready(function () {
    initDataTable();
    initFilters();
    initSearch();
    initModalHandlers();
    initAutoOpenModal();
    initRequestForm();
    initDetailHandlers();
    initAttachmentHandlers();
    initDiscussionUI();

});


$(document).ajaxError(function (event, xhr) {
    if (xhr.status === 419) {
        swalWarning("Session expired, please refresh page.");
    }
});

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
