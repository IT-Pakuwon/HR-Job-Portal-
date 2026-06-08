let table;
let assignedTable;
let currentStatus = '';

$(document).ready(function () {
    initDataTable();
    initAssignedTable();
    initFilters();
    amLoadInventories();
    amLoadFilterCompanies();
    amLoadFilterDepartments('');
    initFilterButtons();
    initModalHandlers();
    initFormHandlers();
});

$(document).ajaxError(function (event, xhr) {
    if (xhr.status === 419) {
        amSwalWarning('Session expired, please refresh the page.');
    }
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    },
});
