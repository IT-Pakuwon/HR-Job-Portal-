<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- HEADER TAB --}}
        <div
            class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-white/5">

            <button id="tabEvent"
                class="tab-button active-tab inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition">

                <span class="text-base">🎡</span>

                <span>
                    Event
                </span>

            </button>

            <button id="tabPrize"
                class="tab-button inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                <span class="text-base">🎁</span>

                <span>
                    Prize
                </span>

            </button>

        </div>

        {{-- EVENT PANEL --}}
        <div id="eventPanel">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Lucky Draw Event
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage lucky draw event master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createEventModal', true)"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">

                        <span>＋</span>

                        <span>
                            Add Event
                        </span>

                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="tableEvent" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Event ID</th>
                                <th>Event Name</th>
                                <th>Event Date</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- CREATE EVENT MODAL --}}
        <div id="createEventModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Lucky Draw Event
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create lucky draw event master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createEventModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="createEventForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event Name
                            </label>

                            <input type="text" name="event_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="Annual Gathering 2026">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event Date
                            </label>

                            <input type="date" name="event_date"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#createEventModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">

                            Save Event

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- EDIT EVENT MODAL --}}
        <div id="editEventModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Lucky Draw Event
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update lucky draw event master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#editEventModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="editEventForm">

                    @csrf
                    @method('PUT')

                    <input type="hidden" id="edit_event_id_old">

                    <div class="grid grid-cols-1 gap-5 p-6">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event ID
                            </label>

                            <input type="text" id="edit_event_id" readonly
                                class="w-full rounded-xl border border-gray-300 bg-gray-100 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/10 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event Name
                            </label>

                            <input type="text" name="event_name" id="edit_event_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event Date
                            </label>

                            <input type="date" name="event_date" id="edit_event_date"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status" id="edit_event_status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#editEventModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">

                            Update Event

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- PRIZE PANEL --}}
        <div id="prizePanel" class="hidden">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Lucky Draw Prize
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage lucky draw prize master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createPrizeModal', true)"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">

                        <span>＋</span>

                        <span>
                            Add Prize
                        </span>

                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="tablePrize" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Prize ID</th>
                                <th>Event</th>
                                <th>Prize Name</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- CREATE PRIZE MODAL --}}
        <div id="createPrizeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Lucky Draw Prize
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create lucky draw prize master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createPrizeModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="createPrizeForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event
                            </label>

                            <select name="event_id" class="event-select2 w-full">

                                <option value="">
                                    Select Event
                                </option>

                                @foreach ($events as $event)
                                    <option value="{{ $event->event_id }}">
                                        {{ $event->event_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Prize Name
                            </label>

                            <input type="text" name="prize_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="Grand Prize - Motorcycle">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#createPrizeModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700">

                            Save Prize

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- EDIT PRIZE MODAL --}}
        <div id="editPrizeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Lucky Draw Prize
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update lucky draw prize master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#editPrizeModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="editPrizeForm">

                    @csrf
                    @method('PUT')

                    <input type="hidden" id="edit_prize_id_old">

                    <div class="grid grid-cols-1 gap-5 p-6">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Prize ID
                            </label>

                            <input type="text" id="edit_prize_id" readonly
                                class="w-full rounded-xl border border-gray-300 bg-gray-100 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/10 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event
                            </label>

                            <select name="event_id" id="edit_prize_event_id" class="event-select2 w-full">

                                <option value="">
                                    Select Event
                                </option>

                                @foreach ($events as $event)
                                    <option value="{{ $event->event_id }}">
                                        {{ $event->event_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Prize Name
                            </label>

                            <input type="text" name="prize_name" id="edit_prize_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status" id="edit_prize_status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#editPrizeModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700">

                            Update Prize

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <script>
        let tableEvent;
        let tablePrize;

        $(document).ready(function() {

            tableEvent = $('#tableEvent').DataTable(baseTableConfig({

                ajax: routes.event.json,

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'event_id',
                        name: 'event_id'
                    },
                    {
                        data: 'event_name',
                        name: 'event_name'
                    },
                    {
                        data: 'event_date',
                        name: 'event_date'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-right',
                        render: function(data, type, row) {

                            return actionButton(
                                'editEvent',
                                'deleteEvent',
                                row.event_id
                            );

                        }
                    }
                ],

                searchPlaceholder: 'Search event...'

            }));

            tablePrize = $('#tablePrize').DataTable(baseTableConfig({

                ajax: routes.prize.json,

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'prize_id',
                        name: 'prize_id'
                    },
                    {
                        data: 'event_name',
                        name: 'event_name'
                    },
                    {
                        data: 'prize_name',
                        name: 'prize_name'
                    },
                    {
                        data: 'status_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-right',
                        render: function(data, type, row) {

                            return actionButton(
                                'editPrize',
                                'deletePrize',
                                row.prize_id
                            );

                        }
                    }
                ],

                searchPlaceholder: 'Search prize...'

            }));

            initSelect2('#createPrizeModal', '.event-select2');
            initSelect2('#editPrizeModal', '.event-select2');

        });

        $(function() {

            const tabs = {
                tabEvent: '#eventPanel',
                tabPrize: '#prizePanel'
            };

            $('.tab-button').on('click', function() {

                $('.tab-button')
                    .removeClass(
                        'active-tab border-blue-200 bg-blue-50 text-blue-700'
                    )
                    .addClass(
                        'border-transparent bg-transparent text-gray-600 dark:text-gray-300'
                    );

                $(this)
                    .removeClass(
                        'border-transparent bg-transparent text-gray-600 dark:text-gray-300'
                    )
                    .addClass(
                        'active-tab border-blue-200 bg-blue-50 text-blue-700'
                    );

                $('#eventPanel,#prizePanel')
                    .addClass('hidden');

                $(tabs[$(this).attr('id')])
                    .removeClass('hidden');
            });

        });
    </script>

    <script>
        const routes = {

            event: {
                json: "{{ route('luckydrawsetup.eventJson') }}",
                store: "{{ route('luckydrawsetup.storeEvent') }}",
                update: "{{ url('/luckydraw-setup/update-event') }}",
                delete: "{{ url('/luckydraw-setup/destroy-event') }}"
            },

            prize: {
                json: "{{ route('luckydrawsetup.prizeJson') }}",
                store: "{{ route('luckydrawsetup.storePrize') }}",
                update: "{{ url('/luckydraw-setup/update-prize') }}",
                delete: "{{ url('/luckydraw-setup/destroy-prize') }}"
            }

        };

        function initSelect2(modalId, selector) {

            $(modalId + ' ' + selector).each(function() {

                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }

                $(this).select2({
                    width: '100%',
                    dropdownParent: $('body')
                });

            });

        }

        function toggleModal(modalId, show = true) {

            const modal = $(modalId);

            if (show) {

                modal
                    .removeClass('hidden')
                    .addClass('flex');

                initSelect2(modalId, '.event-select2');

            } else {

                modal
                    .removeClass('flex')
                    .addClass('hidden');

                const form = modal.find('form');

                if (form.length) {

                    form[0].reset();

                    form.find('select').trigger('change');

                }

            }

        }

        function showLoading(title = 'Processing...') {

            Swal.fire({
                title,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

        }

        function showSuccess(message) {

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 1500,
                showConfirmButton: false
            });

        }

        function showError(message = 'Something went wrong') {

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });

        }

        function submitForm({
            form,
            url,
            method = 'POST',
            table = null,
            modal = null,
            loadingText = 'Processing...'
        }) {

            $.ajax({

                url,
                type: method,
                data: form.serialize(),

                beforeSend: function() {

                    form.find('button[type="submit"]')
                        .prop('disabled', true);

                    showLoading(loadingText);

                },

                complete: function() {

                    form.find('button[type="submit"]')
                        .prop('disabled', false);

                },

                success: function(response) {

                    showSuccess(response.message);

                    if (modal) {
                        toggleModal(modal, false);
                    }

                    if (table) {
                        table.ajax.reload(null, false);
                    }

                },

                error: function(xhr) {

                    showError(
                        xhr.responseJSON?.message ??
                        'Something went wrong'
                    );

                }

            });

        }

        function confirmDelete({
            url,
            table,
            title = 'Delete Data?',
            text = 'This data will be deleted.'
        }) {

            Swal.fire({

                title,
                text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Delete'

            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({

                    url,
                    type: 'DELETE',

                    data: {
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(response) {

                        showSuccess(response.message);

                        if (table) {
                            table.ajax.reload(null, false);
                        }

                    },

                    error: function(xhr) {

                        showError(
                            xhr.responseJSON?.message ??
                            'Something went wrong'
                        );

                    }

                });

            });

        }

        function actionButton(editFn, deleteFn, key) {

            const safeKey = String(key).replace(/'/g, "\\'");

            return `
                <div class="flex items-center justify-end gap-2">

                    <button
                        onclick="${editFn}('${safeKey}')"
                        class="rounded-lg bg-amber-500 px-3 py-1 text-xs text-white transition hover:bg-amber-600">

                        Edit

                    </button>

                    <button
                        onclick="${deleteFn}('${safeKey}')"
                        class="rounded-lg bg-red-600 px-3 py-1 text-xs text-white transition hover:bg-red-700">

                        Delete

                    </button>

                </div>
            `;

        }

        function baseTableConfig({
            ajax,
            columns,
            order = [
                [1, 'asc']
            ],
            searchPlaceholder = 'Search...'
        }) {

            return {

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,

                ajax,
                columns,
                order,

                language: {
                    search: '',
                    searchPlaceholder
                }

            };

        }
    </script>

    <script>
        function editEvent(event_id) {

            let row = tableEvent.rows().data().toArray().find(x => x.event_id == event_id);

            if (!row) return;

            $('#edit_event_id_old').val(row.event_id);
            $('#edit_event_id').val(row.event_id);
            $('#edit_event_name').val(row.event_name);
            $('#edit_event_date').val(row.event_date);
            $('#edit_event_status').val(row.status);

            toggleModal('#editEventModal', true);

        }

        function deleteEvent(event_id) {

            confirmDelete({
                url: `${routes.event.delete}/${event_id}`,
                table: tableEvent,
                title: 'Delete Event?'
            });

        }

        function editPrize(prize_id) {

            let row = tablePrize.rows().data().toArray().find(x => x.prize_id == prize_id);

            if (!row) return;

            $('#edit_prize_id_old').val(row.prize_id);
            $('#edit_prize_id').val(row.prize_id);
            $('#edit_prize_event_id').val(row.event_id);
            $('#edit_prize_name').val(row.prize_name);
            $('#edit_prize_status').val(row.status);

            toggleModal('#editPrizeModal', true);

        }

        function deletePrize(prize_id) {

            confirmDelete({
                url: `${routes.prize.delete}/${prize_id}`,
                table: tablePrize,
                title: 'Delete Prize?'
            });

        }

        $('#createEventForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: routes.event.store,
                table: tableEvent,
                modal: '#createEventModal',
                loadingText: 'Saving Event...'
            });

        });

        $('#editEventForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: `${routes.event.update}/${$('#edit_event_id_old').val()}`,
                table: tableEvent,
                modal: '#editEventModal',
                loadingText: 'Updating Event...'
            });

        });

        $('#createPrizeForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: routes.prize.store,
                table: tablePrize,
                modal: '#createPrizeModal',
                loadingText: 'Saving Prize...'
            });

        });

        $('#editPrizeForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: `${routes.prize.update}/${$('#edit_prize_id_old').val()}`,
                table: tablePrize,
                modal: '#editPrizeModal',
                loadingText: 'Updating Prize...'
            });

        });
    </script>

</x-app-layout>
