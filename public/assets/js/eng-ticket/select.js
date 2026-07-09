// assets/js/ticket/select.js

window.Ticket = window.Ticket || {};

function initTicketSelect() {

    initDepartmentSelect();

    initTicketTypeSelect();

    initTicketCategorySelect();

    initTicketSubcategorySelect();

    initLocationSelect();

    initSubLocationSelect();

    bindTicketSelectEvents();

    initCreateTicketDropdown();

}

function initCreateTicketDropdown() {

    $.ajax({

        url:
            Ticket.routes.createDropdown,

        type:
            'GET',

        success: function (response) {

            if (!response.success) {
                return;
            }

            renderCompanyDropdown(
                response.companies || []
            );

            renderDepartmentDropdown(
                response.departments || []
            );

            renderTicketTypeDropdown(
                response.types || []
            );

            renderLocationDropdown(
                response.locations || []
            );

        },

        error: handleAjaxError,

    });

}

function renderCompanyDropdown(
    companies = []
) {

    const select =
        $('#cpny_id');

    select.empty();

    select.append(`
        <option value="">
            Select Company
        </option>
    `);

    companies.forEach(function (company) {

        select.append(`
            <option value="${String(company.cpny_id).trim()}">
                ${company.cpny_name}
            </option>
        `);

    });

    if (companies.length === 1) {

        select
            .val(
                String(
                    companies[0].cpny_id
                ).trim()
            )
          .trigger('change.select2');

    }

}

function renderDepartmentDropdown(
    departments = []
) {

    const select =
        $('#department_id');

    select.empty();

    select.append(`
        <option value="">
            Select Department
        </option>
    `);

    departments.forEach(function (department) {

        select.append(`
            <option value="${String(department.department_id).trim()}">
                ${department.department_name}
            </option>
        `);

    });

    if (departments.length === 1) {

        const value =
            String(
                departments[0].department_id
            ).trim();

        select
            .val(value)
            .trigger('change');

        select
            .val(value)
            .trigger('change.select2');

    }

}
function renderTicketTypeDropdown(
    types = []
) {

    const select =
        $('#ticket_type');

    select.empty();

    select.append(`
        <option value="">
            Select Ticket Type
        </option>
    `);

    types.forEach(function (type) {

        select.append(`
            <option value="${type.ticket_type}">
                ${type.ticket_type_name}
            </option>
        `);

    });

    select.trigger('change');

}

function renderLocationDropdown(
    locations = []
) {

    Ticket.state.allLocations = locations;

    const cpnyId = $('#cpny_id').val();

    const filtered = cpnyId
        ? locations.filter(function (l) {
            return l.cpny_id === cpnyId || l.cpny_id === 'ALL';
        })
        : locations;

    populateLocationSelect(filtered);

}

function populateLocationSelect(locations = []) {

    const select =
        $('#location_id');

    select.empty();

    select.append(`
        <option value="">
            Select Location
        </option>
    `);

    locations.forEach(function (location) {

        select.append(`
            <option value="${location.location_id}">
                ${location.location_name}
            </option>
        `);

    });

    select.trigger('change');

}

function initTicketTypeSelect() {

    $('#ticket_type').select2({

        width:
            '100%',

        dropdownParent:
            $(Ticket.modal.create),

        placeholder:
            'Select Ticket Type',

        allowClear:
            true,

    });

}

function initDepartmentSelect() {

    $('#department_id').select2({

        width:
            '100%',

        dropdownParent:
            $(Ticket.modal.create),

        placeholder:
            'Select Department',

        allowClear:
            true,

    });

}

function initTicketCategorySelect() {

    $('#ticket_categoryid').select2({

        width:
            '100%',

        dropdownParent:
            $(Ticket.modal.create),

        placeholder:
            'Select Category',

        allowClear:
            true,

        ajax: {

            url:
                Ticket.routes.categorySearch,

            dataType:
                'json',

            delay:
                250,

            data: function () {

                return {

                    ticket_type:
                        $('#ticket_type').val(),

                };

            },

            processResults: function (data) {

                return {
                    results:
                        data.results || [],
                };

            },

        },

    });

}

function initTicketSubcategorySelect() {

    $('#ticket_subcategoryid').select2({

        width:
            '100%',

        dropdownParent:
            $(Ticket.modal.create),

        placeholder:
            'Select Sub Category',

        allowClear:
            true,

        ajax: {

            url:
                Ticket.routes.subcategorySearch,

            dataType:
                'json',

            delay:
                250,

            data: function () {

                return {

                    ticket_type:
                        $('#ticket_type').val(),

                    ticket_categoryid:
                        $('#ticket_categoryid').val(),

                };

            },

            processResults: function (data) {

                return {
                    results:
                        data.results || [],
                };

            },

        },

    });

}

function initLocationSelect() {

    $('#location_id').select2({

        width:
            '100%',

        dropdownParent:
            $(Ticket.modal.create),

        placeholder:
            'Select Location',

        allowClear:
            true,

    });

}

function initSubLocationSelect() {

    $('#sub_location_id').select2({

        width:
            '100%',

        dropdownParent:
            $(Ticket.modal.create),

        placeholder:
            'Select Sub Location',

        allowClear:
            true,

        ajax: {

            url:
                Ticket.routes.subLocationSearch,

            dataType:
                'json',

            delay:
                250,

            data: function () {

                return {

                    location_id:
                        $('#location_id').val(),

                    cpny_id:
                        $('#cpny_id').val(),

                };

            },

            processResults: function (data) {

                return {
                    results:
                        data.results || [],
                };

            },

        },

    });

}

function bindTicketSelectEvents() {

    $('#ticket_type').on(
        'change',
        function () {

            if (Ticket.state.isEditLoading) {
                return;
            }

            $('#ticket_categoryid')
                .val(null)
                .trigger('change');

            $('#ticket_subcategoryid')
                .val(null)
                .trigger('change');

        }
    );

    $('#ticket_categoryid').on(
        'change',
        function () {

            if (Ticket.state.isEditLoading) {
                return;
            }

            $('#ticket_subcategoryid')
                .val(null)
                .trigger('change');

        }
    );

    $('#cpny_id').on(
        'change',
        function () {

            if (Ticket.state.isEditLoading) {
                return;
            }

            const cpnyId = $(this).val();
            const all = Ticket.state.allLocations || [];

            const filtered = cpnyId
                ? all.filter(function (l) {
                    return l.cpny_id === cpnyId || l.cpny_id === 'ALL';
                })
                : all;

            populateLocationSelect(filtered);

            $('#location_id')
                .val(null)
                .trigger('change');

            $('#sub_location_id')
                .val(null)
                .trigger('change');

        }
    );

    $('#location_id').on(
        'change',
        function () {

            if (Ticket.state.isEditLoading) {
                return;
            }

            $('#sub_location_id')
                .val(null)
                .trigger('change');

        }
    );

}
function initResponseTicketSelect() {

    $('#response_pic').select2({

        theme: 'default',

        width: '100%',

        dropdownParent:
            $('#responseTicketModal'),

        ajax: {

            url:
                window.ticketRoutes.picSearch,

            dataType: 'json',

            delay: 250,

            data(params) {

                return {
                    search:
                        params.term
                };
            },

            processResults(data) {

                return {
                    results: data.results
                };
            },

            cache: true
        }
    });

    $('#response_priority').select2({

        theme: 'default',

        width: '100%',

        dropdownParent:
            $('#responseTicketModal'),

        ajax: {

            url:
                window.ticketRoutes.prioritySearch,

            dataType: 'json',

            delay: 250,

            data(params) {

                return {
                    search:
                        params.term
                };
            },

            processResults(data) {

                return {
                    results: data.results
                };
            },

            cache: true
        }
    });
}
