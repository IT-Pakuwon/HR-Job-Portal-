window.BookingCar = window.BookingCar || {};

BookingCar.state = {
    calendar: null,
    bookingData: [],
    filteredData: [],
    currentFilter: 'ALL',
    currentPage: 1,
    perPage: 5,
    selectedBooking: null,
    selectedApprovalDocId: null,
    routeIndex: 0,
    editRouteIndex: 0,
};

BookingCar.el = {
    mainGrid: document.getElementById('mainGrid'),
    calendar: document.getElementById('calendar'),
    calendarWrapper: document.getElementById('calendarWrapper'),
    bookingListPanel: document.getElementById('bookingListPanel'),

    toggleList: document.getElementById('toggleList'),

    bookingListBody: document.getElementById('bookingListBody'),
    bookingCount: document.getElementById('bookingCount'),

    prevBookingPage: document.getElementById('prevBookingPage'),
    nextBookingPage: document.getElementById('nextBookingPage'),
    bookingPageInfo: document.getElementById('bookingPageInfo'),

    bookingFilters: document.querySelectorAll('.booking-filter'),

    createModal: document.getElementById('createBookingModal'),
    openCreateModalBtn: document.getElementById('openCreateBookingModal'),
    closeCreateModalBtn: document.getElementById('closeCreateBookingModal'),
    closeCreateModalFooterBtn: document.getElementById('closeCreateBookingModalFooter'),

    bookingCarForm: document.getElementById('bookingCarForm'),

    createRouteTableBody: document.getElementById('createRouteTableBody'),
    createAddRouteBtn: document.getElementById('createAddRouteBtn'),

    viewModal: document.getElementById('viewBookingModal'),

    editModal: document.getElementById('editBookingModal'),
    editBookingForm: document.getElementById('editBookingForm'),
    closeEditBookingModal: document.getElementById('closeEditBookingModal'),
    cancelEditBookingBtn: document.getElementById('cancelEditBookingBtn'),

    editRouteTableBody: document.getElementById('editRouteTableBody'),
    editAddRouteBtn: document.getElementById('editAddRouteBtnEdit'),

    gaProcessModal: document.getElementById('gaProcessModal'),
    gaProcessForm: document.getElementById('gaProcessForm'),
    closeGaProcessModal: document.getElementById('closeGaProcessModal'),
    cancelGaProcessBtn: document.getElementById('cancelGaProcessBtn'),

    driverAssignmentWrapper: document.getElementById('driverAssignmentWrapper'),
    vehicleAssignmentWrapper: document.getElementById('vehicleAssignmentWrapper'),

    gaStatusPerjalanan: document.getElementById('ga_status_perjalanan'),
    gaDriver: document.getElementById('ga_driver'),
    gaHandphone: document.getElementById('ga_handphone'),
    gaVehicle: document.getElementById('ga_vehicle'),
    gaNoPolisi: document.getElementById('ga_no_polisi'),

    approveBookingBtn: document.getElementById('approveBookingBtn'),
    reviseBookingBtn: document.getElementById('reviseBookingBtn'),
    rejectBookingBtn: document.getElementById('rejectBookingBtn'),

    bookingApprovalActionsWrapper: document.getElementById('bookingApprovalActionsWrapper'),

    cancelBookingBtn: document.getElementById('cancelBookingBtn'),
    editBookingBtn: document.getElementById('editBookingBtn'),

    bookingApprovalFlow: document.getElementById('bookingApprovalFlow'),
    bookingTrackingTimeline: document.getElementById('bookingTrackingTimeline'),
};

BookingCar.config = {
    routes: {},

    statusColor: {
        P: 'bg-blue-500',
        C: 'bg-emerald-500',
        D: 'bg-amber-400',
        R: 'bg-red-500',
        X: 'bg-gray-500',
        WAITING_PROCESS: 'bg-indigo-500',
    },

    statusLabel: {
        P: 'Pending',
        C: 'Approved',
        D: 'Revise',
        R: 'Rejected',
        X: 'Closed',
        WAITING_PROCESS: 'Waiting Process',
    },
};

BookingCar.resetState = () => {
    BookingCar.state.selectedBooking = null;
    BookingCar.state.selectedApprovalDocId = null;
};

BookingCar.setFilter = (filter) => {
    BookingCar.state.currentFilter = filter;
    BookingCar.state.currentPage = 1;
};

BookingCar.setBookingData = (data = []) => {
    BookingCar.state.bookingData = data;
};

BookingCar.setFilteredData = (data = []) => {
    BookingCar.state.filteredData = data;
};

BookingCar.setSelectedBooking = (data = null) => {
    BookingCar.state.selectedBooking = data;
};
