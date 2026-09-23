// ============================================================
// route.js — Booking Car
// Route table management for Create and Edit forms
// ============================================================

const BookingCarRoute = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    createIndex:  0,
    editIndex:    0,
    processIndex: 0,

    // --------------------------------------------------------
    // INIT — called once on page load
    // --------------------------------------------------------
    init() {
        BookingCarRoute.initCreate();
        BookingCarRoute.initEdit();
        BookingCarRoute.initProcess();
    },

    // --------------------------------------------------------
    // CREATE FORM
    // --------------------------------------------------------
    initCreate() {
        const addBtn = document.getElementById('createAddRouteBtn');
        const tbody  = document.getElementById('createRouteTableBody');

        if (!addBtn || !tbody) return;

        // Start with one empty row
        BookingCarRoute.addCreateRow();

        addBtn.addEventListener('click', () => {
            BookingCarRoute.addCreateRow();
        });

        // Delegate remove button
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-route-btn');
            if (!btn) return;

            const row = btn.closest('tr');
            if (!row) return;

            // Must keep at least 1 row
            if (tbody.querySelectorAll('tr').length <= 1) {
                BookingCar.toast('warning', 'At least one route is required.');
                return;
            }

            row.remove();
            BookingCarRoute.reindexCreate();
        });
    },

    addCreateRow(origin = '', destination = '') {
        const tbody = document.getElementById('createRouteTableBody');
        if (!tbody) return;

        const index = tbody.querySelectorAll('tr').length;
        tbody.insertAdjacentHTML(
            'beforeend',
            BookingCarHelper.renderEditableRouteRow(index, origin, destination)
        );

        BookingCarRoute.createIndex = tbody.querySelectorAll('tr').length;
    },

    reindexCreate() {
        const tbody = document.getElementById('createRouteTableBody');
        if (!tbody) return;

        tbody.querySelectorAll('tr').forEach((row, i) => {
            row.dataset.routeIndex = i;

            const numCell = row.querySelector('td:first-child');
            if (numCell) numCell.textContent = i + 1;
        });
    },

    clearCreate() {
        const tbody = document.getElementById('createRouteTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        BookingCarRoute.createIndex = 0;
        BookingCarRoute.addCreateRow();
    },

    // --------------------------------------------------------
    // EDIT FORM
    // --------------------------------------------------------
    initEdit() {
        const addBtn = document.getElementById('editAddRouteBtnEdit');
        const tbody  = document.getElementById('editRouteTableBody');

        if (!addBtn || !tbody) return;

        addBtn.addEventListener('click', () => {
            BookingCarRoute.addEditRow();
        });

        // Delegate remove button
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-route-btn');
            if (!btn) return;

            const row = btn.closest('tr');
            if (!row) return;

            // Must keep at least 1 row
            if (tbody.querySelectorAll('tr').length <= 1) {
                BookingCar.toast('warning', 'At least one route is required.');
                return;
            }

            row.remove();
            BookingCarRoute.reindexEdit();
        });
    },

    addEditRow(origin = '', destination = '') {
        const tbody = document.getElementById('editRouteTableBody');
        if (!tbody) return;

        const index = tbody.querySelectorAll('tr').length;
        tbody.insertAdjacentHTML(
            'beforeend',
            BookingCarHelper.renderEditableRouteRow(index, origin, destination)
        );

        BookingCarRoute.editIndex = tbody.querySelectorAll('tr').length;
    },

    reindexEdit() {
        const tbody = document.getElementById('editRouteTableBody');
        if (!tbody) return;

        tbody.querySelectorAll('tr').forEach((row, i) => {
            row.dataset.routeIndex = i;

            const numCell = row.querySelector('td:first-child');
            if (numCell) numCell.textContent = i + 1;
        });
    },

    // Load existing routes into edit form
    loadEditRoutes(details) {
        const tbody = document.getElementById('editRouteTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        BookingCarRoute.editIndex = 0;

        if (!details || details.length === 0) {
            BookingCarRoute.addEditRow();
            return;
        }

        details.forEach((route) => {
            BookingCarRoute.addEditRow(
                route.origin      ?? '',
                route.destination ?? ''
            );
        });
    },

    clearEdit() {
        const tbody = document.getElementById('editRouteTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        BookingCarRoute.editIndex = 0;
        BookingCarRoute.addEditRow();
    },

    // --------------------------------------------------------
    // GET ROUTES FROM FORM  (for validation before submit)
    // --------------------------------------------------------
    getCreateRoutes() {
        return BookingCarRoute._collectRoutes('createRouteTableBody');
    },

    getEditRoutes() {
        return BookingCarRoute._collectRoutes('editRouteTableBody');
    },

    _collectRoutes(tbodyId) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return [];

        const routes = [];

        tbody.querySelectorAll('tr').forEach((row) => {
            const origin      = row.querySelector('input[name="location_from[]"]')?.value?.trim() ?? '';
            const destination = row.querySelector('input[name="destination[]"]')?.value?.trim()   ?? '';

            if (origin || destination) {
                routes.push({ origin, destination });
            }
        });

        return routes;
    },

    // --------------------------------------------------------
    // PROCESS FORM (GA edit route during processing)
    // --------------------------------------------------------
    initProcess() {
        const addBtn = document.getElementById('gaProcessAddRouteBtn');
        const tbody  = document.getElementById('gaProcessRouteTableBody');

        if (!addBtn || !tbody) return;

        addBtn.addEventListener('click', () => {
            BookingCarRoute.addProcessRow();
        });

        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-route-btn');
            if (!btn) return;

            const row = btn.closest('tr');
            if (!row) return;

            if (tbody.querySelectorAll('tr').length <= 1) {
                BookingCar.toast('warning', 'At least one route is required.');
                return;
            }

            row.remove();
            BookingCarRoute.reindexProcess();
        });
    },

    addProcessRow(origin = '', destination = '') {
        const tbody = document.getElementById('gaProcessRouteTableBody');
        if (!tbody) return;

        const index = tbody.querySelectorAll('tr').length;
        tbody.insertAdjacentHTML(
            'beforeend',
            BookingCarHelper.renderEditableRouteRow(index, origin, destination)
        );

        BookingCarRoute.processIndex = tbody.querySelectorAll('tr').length;
    },

    reindexProcess() {
        const tbody = document.getElementById('gaProcessRouteTableBody');
        if (!tbody) return;

        tbody.querySelectorAll('tr').forEach((row, i) => {
            row.dataset.routeIndex = i;

            const numCell = row.querySelector('td:first-child');
            if (numCell) numCell.textContent = i + 1;
        });
    },

    loadProcessRoutes(details) {
        const tbody = document.getElementById('gaProcessRouteTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        BookingCarRoute.processIndex = 0;

        if (!details || details.length === 0) {
            BookingCarRoute.addProcessRow();
            return;
        }

        details.forEach((route) => {
            BookingCarRoute.addProcessRow(
                route.origin      ?? '',
                route.destination ?? ''
            );
        });
    },

    getProcessRoutes() {
        return BookingCarRoute._collectRoutes('gaProcessRouteTableBody');
    },

    clearProcess() {
        const tbody = document.getElementById('gaProcessRouteTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        BookingCarRoute.processIndex = 0;
        BookingCarRoute.addProcessRow();
    },

    // Validate — returns true if all rows have both fields filled
    validateRoutes(routes) {
        if (!routes || routes.length === 0) {
            BookingCar.toast('warning', 'At least one route is required.');
            return false;
        }

        for (let i = 0; i < routes.length; i++) {
            if (BookingCarHelper.isEmpty(routes[i].origin)) {
                BookingCar.toast('warning', `Row ${i + 1}: Pickup location is required.`);
                return false;
            }

            if (BookingCarHelper.isEmpty(routes[i].destination)) {
                BookingCar.toast('warning', `Row ${i + 1}: Destination is required.`);
                return false;
            }
        }

        return true;
    },
};
