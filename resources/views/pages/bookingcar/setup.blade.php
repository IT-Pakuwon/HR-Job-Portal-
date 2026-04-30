<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- HEADER TAB --}}
        <div
            class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-white/5">

            <button id="tabOperational"
                class="tab-button active-tab inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition">

                <span class="text-base">🚗</span>

                <span>
                    Operational Car
                </span>

            </button>

            <button id="tabDriver"
                class="tab-button inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                <span class="text-base">👨‍✈️</span>

                <span>
                    Driver
                </span>

            </button>

        </div>
        {{-- OPERATIONAL CAR --}}
        <div id="operationalPanel">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Operational Car List
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            List of all operational vehicles.
                        </p>
                    </div>

                    <button type="button" onclick="openCreateVehicleModal()"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">

                        <span>＋</span>

                        <span>
                            Add Vehicle
                        </span>

                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="vehicleTable" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>License Plate</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- DRIVER --}}
        <div id="driverPanel" class="hidden">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Driver List
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            List of operational drivers.
                        </p>
                    </div>

                    <button type="button" onclick="openCreateDriverModal()"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">

                        <span>＋</span>

                        <span>
                            Add Driver
                        </span>

                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="driverTable" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Driver Name</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- Create Vehicle Modal --}}
        <div id="createVehicleModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Operational Vehicle
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create new operational car master data.
                        </p>
                    </div>

                    <button type="button" onclick="closeCreateVehicleModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                {{-- BODY --}}
                <form id="createVehicleForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        {{-- LICENSE --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                License Plate
                            </label>

                            <input type="text" name="nopol_kendaraan"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="B 1234 XYZ" required>

                        </div>

                        {{-- DESCRIPTION --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Vehicle Description
                            </label>

                            <input type="text" name="kendaraan_descr"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="TOYOTA INNOVA" required>

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeCreateVehicleModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">

                            Save Vehicle

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- Create Driver Modal --}}
        <div id="createDriverModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Driver
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create new operational driver master data.
                        </p>
                    </div>

                    <button type="button" onclick="closeCreateDriverModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                {{-- BODY --}}
                <form id="createDriverForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        {{-- DRIVER NAME --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Driver Name
                            </label>

                            <input type="text" name="drivername"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="JOHN DOE" required>

                        </div>

                        {{-- PHONE --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Phone Number
                            </label>

                            <input type="text" name="hp"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="08123456789">

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeCreateDriverModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700">

                            Save Driver

                        </button>

                    </div>

                </form>

            </div>

        </div>


        {{-- Edit Vehicle Modal --}}
        <div id="editVehicleModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Operational Vehicle
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update operational vehicle information.
                        </p>
                    </div>

                    <button type="button" onclick="closeEditVehicleModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                {{-- BODY --}}
                <form id="editVehicleForm">

                    @csrf

                    <input type="hidden" id="edit_vehicle_id">

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        {{-- LICENSE --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                License Plate
                            </label>

                            <input type="text" name="nopol_kendaraan" id="edit_nopol_kendaraan"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        {{-- DESCRIPTION --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Vehicle Description
                            </label>

                            <input type="text" name="kendaraan_descr" id="edit_kendaraan_descr"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeEditVehicleModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">

                            Update Vehicle

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- Edit Driver Modal --}}
        <div id="editDriverModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Driver
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update driver information.
                        </p>
                    </div>

                    <button type="button" onclick="closeEditDriverModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                {{-- BODY --}}
                <form id="editDriverForm">

                    @csrf

                    <input type="hidden" id="edit_driver_id">

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        {{-- DRIVER NAME --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Driver Name
                            </label>

                            <input type="text" name="drivername" id="edit_drivername"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        {{-- PHONE --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Phone Number
                            </label>

                            <input type="text" name="hp" id="edit_hp"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeEditDriverModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700">

                            Update Driver

                        </button>

                    </div>

                </form>

            </div>

        </div>


    </div>

    <script>
        const operationalTab = document.getElementById('tabOperational');
        const driverTab = document.getElementById('tabDriver');

        const operationalPanel = document.getElementById('operationalPanel');
        const driverPanel = document.getElementById('driverPanel');

        function activateTab(activeBtn, inactiveBtn) {

            activeBtn.classList.add(
                'active-tab',
                'border-blue-200',
                'bg-blue-50',
                'text-blue-700'
            );

            activeBtn.classList.remove(
                'border-gray-200',
                'bg-white',
                'text-gray-600'
            );

            inactiveBtn.classList.remove(
                'active-tab',
                'border-blue-200',
                'bg-blue-50',
                'text-blue-700'
            );

            inactiveBtn.classList.add(
                'border-gray-200',
                'bg-white',
                'text-gray-600'
            );
        }

        operationalTab.addEventListener('click', function() {

            activateTab(operationalTab, driverTab);

            operationalPanel.classList.remove('hidden');
            driverPanel.classList.add('hidden');

        });

        driverTab.addEventListener('click', function() {

            activateTab(driverTab, operationalTab);

            driverPanel.classList.remove('hidden');
            operationalPanel.classList.add('hidden');

        });

        let vehicleTable;
        let driverTable;

        $(document).ready(function() {
            vehicleTable = $('#vehicleTable').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('bookingcar.setup.vehicle.json') }}",
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Operational_Car_List',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Operational_Car_List',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'nopol_kendaraan',
                        name: 'nopol_kendaraan'
                    },
                    {
                        data: 'kendaraan_descr',
                        name: 'kendaraan_descr'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-right'
                    }
                ],

                pageLength: 10,
                autoWidth: false,
                order: [[1, 'asc']],
                columnDefs: [
                    {
                        targets: [0, 3, 4],
                        orderable: false
                    }
                ],

                language: {
                    search: '',
                    searchPlaceholder: 'Search vehicle...',
                }
            });

            driverTable = $('#driverTable').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('bookingcar.setup.driver.json') }}",
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Driver_Operational_List',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Driver_Operational_List',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'drivername',
                        name: 'drivername'
                    },
                    {
                        data: 'hp',
                        name: 'hp'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-right'
                    }
                ],

                pageLength: 10,
                autoWidth: false,
                order: [[1, 'asc']],
                columnDefs: [
                    {
                        targets: [0, 3, 4],
                        orderable: false
                    }
                ],

                language: {
                    search: '',
                    searchPlaceholder: 'Search driver...',
                }
            });

        });

        function openCreateVehicleModal() {
            $('#createVehicleModal')
                .removeClass('hidden')
                .addClass('flex');
        }

        function closeCreateVehicleModal() {
            $('#createVehicleModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createVehicleForm')[0].reset();
        }

        function openCreateDriverModal() {
            $('#createDriverModal')
                .removeClass('hidden')
                .addClass('flex');
        }

        function closeCreateDriverModal() {
            $('#createDriverModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createDriverForm')[0].reset();
        }

        $('#createVehicleForm').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "{{ route('bookingcar.setup.vehicle.store') }}",
                type: "POST",
                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeCreateVehicleModal();

                    vehicleTable.ajax.reload(null, false);
                },

                error: function(xhr) {

                    let message = 'Something went wrong';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                    });

                }

            });

        });


        $('#createDriverForm').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "{{ route('bookingcar.setup.driver.store') }}",
                type: "POST",
                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeCreateDriverModal();

                    driverTable.ajax.reload(null, false);
                },

                error: function(xhr) {

                    let message = 'Something went wrong';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                    });

                }

            });

        });

        function closeEditVehicleModal() {

            $('#editVehicleModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editVehicleForm')[0].reset();
        }

        function closeEditDriverModal() {

            $('#editDriverModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editDriverForm')[0].reset();
        }


        function editVehicle(id) {

            $.ajax({

                url: `/bookingcar/setup/vehicle/find/${id}`,
                type: 'GET',

                success: function(response) {

                    const data = response.data;

                    $('#edit_vehicle_id').val(data.id);
                    $('#edit_nopol_kendaraan').val(data.nopol_kendaraan);
                    $('#edit_kendaraan_descr').val(data.kendaraan_descr);

                    $('#editVehicleModal')
                        .removeClass('hidden')
                        .addClass('flex');
                }

            });

        }

        $('#editVehicleForm').submit(function(e) {

            e.preventDefault();

            let id = $('#edit_vehicle_id').val();

            $.ajax({

                url: `/bookingcar/setup/vehicle/update/${id}`,
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeEditVehicleModal();

                    vehicleTable.ajax.reload(null, false);
                },

                error: function(xhr) {

                    let message = 'Something went wrong';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                    });

                }

            });

        });

        function editDriver(id) {

            $.ajax({

                url: `/bookingcar/setup/driver/find/${id}`,
                type: 'GET',

                success: function(response) {

                    const data = response.data;

                    $('#edit_driver_id').val(data.id);
                    $('#edit_drivername').val(data.drivername);
                    $('#edit_hp').val(data.hp);

                    $('#editDriverModal')
                        .removeClass('hidden')
                        .addClass('flex');
                }

            });

        }


        $('#editDriverForm').submit(function(e) {

            e.preventDefault();

            let id = $('#edit_driver_id').val();

            $.ajax({

                url: `/bookingcar/setup/driver/update/${id}`,
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeEditDriverModal();

                    driverTable.ajax.reload(null, false);
                },

                error: function(xhr) {

                    let message = 'Something went wrong';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                    });

                }

            });

        });

        function updateVehicleStatus(id, status, element = null) {

            Swal.fire({
                title: 'Are you sure?',
                text: 'Vehicle status will be updated.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Update'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({

                        url: `/bookingcar/setup/vehicle/status/${id}`,
                        type: 'POST',

                        data: {
                            _token: '{{ csrf_token() }}',
                            status: status
                        },

                        beforeSend: function() {

                            Swal.fire({
                                title: 'Updating...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                        },

                        success: function(response) {

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 1800,
                                showConfirmButton: false
                            });

                            vehicleTable.ajax.reload(null, false);
                        },

                        error: function(xhr) {

                            if (element) {
                                element.checked = !element.checked;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ?? 'Something went wrong'
                            });

                        }

                    });

                } else {

                    if (element) {
                        element.checked = !element.checked;
                    }

                }

            });

        }

        function updateDriverStatus(id, status, element = null) {

            Swal.fire({
                title: 'Are you sure?',
                text: 'Driver status will be updated.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Update'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({

                        url: `/bookingcar/setup/driver/status/${id}`,
                        type: 'POST',

                        data: {
                            _token: '{{ csrf_token() }}',
                            status: status
                        },

                        beforeSend: function() {

                            Swal.fire({
                                title: 'Updating...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                        },

                        success: function(response) {

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 1800,
                                showConfirmButton: false
                            });

                            driverTable.ajax.reload(null, false);
                        },

                        error: function(xhr) {

                            if (element) {
                                element.checked = !element.checked;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ?? 'Something went wrong'
                            });

                        }

                    });

                } else {

                    if (element) {
                        element.checked = !element.checked;
                    }

                }

            });

        }
    </script>

</x-app-layout>
