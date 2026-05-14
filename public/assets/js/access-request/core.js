let table;
let currentStatus = "";
let currentModal = null;
let existingAttachments = [];
let selectedFiles = [];
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

function initAutoOpenModal() {

    if (
        window.modalType === 'detail'
        && window.modalAccess
    ) {

        openDetailModal(window.modalAccess);

    }

    if (
        window.modalType === 'edit'
        && window.modalAccess
    ) {

        openEditModal(window.modalAccess);

    }

    if (
        window.modalType === 'process-hardware'
        && window.modalAccess
    ) {

        openProcessHardwareModal(window.modalAccess);

    }

    if (
        window.modalType === 'process-software'
        && window.modalAccess
    ) {

        openProcessSoftwareModal(window.modalAccess);

    }

}

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
