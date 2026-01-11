<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-2 py-2 sm:px-6 lg:px-2">
        <div class="mt-2 overflow-y-auto rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <h1 class="align-middle text-2xl font-bold dark:text-white"></h1>

            </div>
            <div x-data="{ tab: 'waitingapp' }" class="mt-4">
                <div class="mb-4 flex space-x-4">
                    <button @click="tab = 'waitingapp'"
                        :class="tab === 'waitingapp' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="rounded px-4 py-2 font-semibold">
                        📄 Waiting Approval
                    </button>
                    <button @click="tab = 'approval'"
                        :class="tab === 'approval' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                        class="rounded px-4 py-2 font-semibold">
                        📅 Approval
                    </button>
                </div>
                <div class="grid" x-show="tab === 'waitingapp'">
                    <div class="rounded-lg bg-white dark:bg-gray-800">
                        <table id="agendasTable" class="mt-5 min-w-full rounded">
                            <thead class="bg-white-200 dark:text-white">
                                <tr>
                                    <th class="w-32 px-4 py-3 text-left">DocID</th>
                                    <th class="px-4 py-3 text-center">Date</th>
                                    <th class="px-4 py-3 text-center">Company</th>
                                    <th class="px-4 py-3 text-center">Departement</th>
                                    <th class="px-4 py-3 text-center">Info</th>
                                    <th class="w-32 px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Calendar -->
                <div x-show="tab === 'approval'">
                    @include('pages.dashboard.dashapproval')
                    {{-- @include('pages.agendas.calendar') --}}
                </div>
            </div>
        </div>
    </div>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        var currentUser = "{{ auth()->user()->username }}";
    </script>
    <script>
        $(document).ready(function() {
            let table = $('#agendasTable').DataTable({
                ajax: "{{ route('waitingapproval.json') }}",
                processing: true,
                serverSide: false,
                responsive: true,
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: null,
                        defaultContent: ''
                    }, {
                        data: 'id',
                        render: function(data, type, row) {
                            let url = `${window.location.origin}${row.url}/${row.id}`;
                            let buttonClass =
                                'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-500 hover:bg-gray-700';
                            let buttonText = row.docid;


                            return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                        }
                    },
                    {
                        data: 'docdate',
                        className: 'no-pointer'
                    },
                    {
                        data: 'cpnyid',
                        className: 'no-pointer'
                    },
                    {
                        data: 'departementid',
                        className: 'no-pointer'
                    },
                    {
                        data: 'infohd',
                        className: 'no-pointer'
                    },
                    {
                        data: 'status',
                        className: 'no-pointer',
                        render: function(data) {
                            let statusText = "";
                            let badgeClass = "";

                            if (data === 'D') {
                                statusText = "Revise";
                                badgeClass =
                                    "w-32 bg-gray-300/30 dark:bg-gray-300 text-gray-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'P') {
                                statusText = "On Progress";
                                badgeClass =
                                    "w-32 bg-blue-300/30 dark:bg-blue-300 text-blue-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'C') {
                                statusText = "Completed";
                                badgeClass =
                                    "w-32 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'X') {
                                statusText = "Cancel";
                                badgeClass =
                                    "w-32 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'R') {
                                statusText = "Rejected";
                                badgeClass =
                                    "w-32 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            } else {
                                statusClass =
                                    "  w-full max-w-32 bg-gray-300/30  bg-gray-300  text-gray-600 flex justify-items-center  focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            }
                            return `<span class="${badgeClass}">${statusText}</span>`;
                        }

                    }
                ]
            });

        });
    </script>
</x-app-layout>
