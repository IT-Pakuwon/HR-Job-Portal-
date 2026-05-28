window.BookingCar = window.BookingCar || {};

BookingCar.openCreateModal = () => {

    BookingCar.resetCreateForm();

    BookingCar.openModal(BookingCar.el.createModal);

    BookingCar.scrollTopModal('#createBookingModal');
};

BookingCar.closeCreateModal = () => {

    BookingCar.closeModal(BookingCar.el.createModal);

    BookingCar.resetCreateForm();
};

BookingCar.openEditModal = () => {

    BookingCar.openModal(BookingCar.el.editModal);

    BookingCar.scrollTopModal('#editBookingModal');
};

BookingCar.closeEditModal = () => {

    BookingCar.closeModal(BookingCar.el.editModal);

    BookingCar.resetEditForm();
};

BookingCar.openDetailModal = () => {

    BookingCar.openModal(BookingCar.el.viewModal);

    BookingCar.scrollTopModal('#viewBookingModal');
};

window.closeBookingDetailModal = () => {

    BookingCar.closeModal(BookingCar.el.viewModal);

    BookingCar.resetState();
};

BookingCar.openGaProcessModal = () => {

    BookingCar.openModal(BookingCar.el.gaProcessModal);

    BookingCar.scrollTopModal('#gaProcessModal');
};

BookingCar.closeGaProcessModal = () => {

    BookingCar.closeModal(BookingCar.el.gaProcessModal);

    if (BookingCar.el.gaProcessForm) {
        BookingCar.el.gaProcessForm.reset();
    }

    BookingCar.toggleDriverAssignment(false);
    BookingCar.toggleVehicleAssignment(false);
};

BookingCar.addCreateRouteRow = ({
    pickup = '',
    destination = '',
} = {}) => {

    const index = BookingCar.state.routeIndex;

    const html = BookingCar.generateRouteRow({
        index,
        pickup,
        destination,
        type: 'create',
    });

    BookingCar.el.createRouteTableBody.insertAdjacentHTML(
        'beforeend',
        html
    );

    BookingCar.state.routeIndex++;
};

BookingCar.addEditRouteRow = ({
    pickup = '',
    destination = '',
} = {}) => {

    const index = BookingCar.state.editRouteIndex;

    const html = BookingCar.generateRouteRow({
        index,
        pickup,
        destination,
        type: 'edit',
    });

    BookingCar.el.editRouteTableBody.insertAdjacentHTML(
        'beforeend',
        html
    );

    BookingCar.state.editRouteIndex++;
};

BookingCar.reorderRouteTable = (tbodySelector) => {

    const rows = document.querySelectorAll(
        `${tbodySelector} tr`
    );

    rows.forEach((row, index) => {

        const noCell = row.querySelector('td:first-child');

        if (noCell) {
            noCell.innerText = index + 1;
        }
    });
};

BookingCar.toggleDriverAssignment = (show = true) => {

    if (!BookingCar.el.driverAssignmentWrapper) return;

    if (show) {
        BookingCar.el.driverAssignmentWrapper.classList.remove('hidden');
    } else {
        BookingCar.el.driverAssignmentWrapper.classList.add('hidden');
    }
};

BookingCar.toggleVehicleAssignment = (show = true) => {

    if (!BookingCar.el.vehicleAssignmentWrapper) return;

    if (show) {
        BookingCar.el.vehicleAssignmentWrapper.classList.remove('hidden');
    } else {
        BookingCar.el.vehicleAssignmentWrapper.classList.add('hidden');
    }
};

BookingCar.handleTravelStatusVisibility = () => {

    const status =
        BookingCar.el.gaStatusPerjalanan?.value || '';

    const needDriverStatuses = [
        'DIJEMPUT',
        'DROP',
        'DIANTAR',
        'SELESAI',
        'ON DUTY',
    ];

    const shouldShow =
        needDriverStatuses.includes(status.toUpperCase());

    BookingCar.toggleDriverAssignment(shouldShow);
    BookingCar.toggleVehicleAssignment(shouldShow);
};

BookingCar.bindModalEvents = () => {

    BookingCar.el.openCreateModalBtn?.addEventListener(
        'click',
        BookingCar.openCreateModal
    );

    BookingCar.el.closeCreateModalBtn?.addEventListener(
        'click',
        BookingCar.closeCreateModal
    );

    BookingCar.el.closeCreateModalFooterBtn?.addEventListener(
        'click',
        BookingCar.closeCreateModal
    );

    BookingCar.el.closeEditBookingModal?.addEventListener(
        'click',
        BookingCar.closeEditModal
    );

    BookingCar.el.cancelEditBookingBtn?.addEventListener(
        'click',
        BookingCar.closeEditModal
    );

    BookingCar.el.closeGaProcessModal?.addEventListener(
        'click',
        BookingCar.closeGaProcessModal
    );

    BookingCar.el.cancelGaProcessBtn?.addEventListener(
        'click',
        BookingCar.closeGaProcessModal
    );

    BookingCar.el.editAddRouteBtn?.addEventListener(
        'click',
        () => {
            BookingCar.addEditRouteRow();
        }
    );

    document.addEventListener('click', (e) => {

        if (e.target.classList.contains('remove-route-btn')) {

            const row = e.target.closest('tr');

            const tbody = row.closest('tbody');

            row.remove();

            if (tbody.id === 'createRouteTableBody') {

                BookingCar.reorderRouteTable(
                    '#createRouteTableBody'
                );

            } else {

                BookingCar.reorderRouteTable(
                    '#editRouteTableBody'
                );
            }
        }
    });

    BookingCar.el.gaStatusPerjalanan?.addEventListener(
        'change',
        BookingCar.handleTravelStatusVisibility
    );

    BookingCar.el.gaDriver?.addEventListener(
        'change',
        function () {

            const selected =
                this.options[this.selectedIndex];

            const hp =
                selected.getAttribute('data-hp') || '-';

            BookingCar.el.gaHandphone.value = hp;
        }
    );

    BookingCar.el.gaVehicle?.addEventListener(
        'change',
        function () {

            const selected =
                this.options[this.selectedIndex];

            const noPolisi =
                selected.value || '-';

            BookingCar.el.gaNoPolisi.value = noPolisi;
        }
    );

    window.addEventListener('keydown', (e) => {

        if (e.key !== 'Escape') return;

        if (
            !BookingCar.el.createModal.classList.contains('hidden')
        ) {
            BookingCar.closeCreateModal();
        }

        if (
            !BookingCar.el.editModal.classList.contains('hidden')
        ) {
            BookingCar.closeEditModal();
        }

        if (
            !BookingCar.el.viewModal.classList.contains('hidden')
        ) {
            closeBookingDetailModal();
        }

        if (
            !BookingCar.el.gaProcessModal.classList.contains('hidden')
        ) {
            BookingCar.closeGaProcessModal();
        }
    });
};
