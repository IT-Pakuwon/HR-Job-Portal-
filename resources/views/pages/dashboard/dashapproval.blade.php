        <div class="grid">
            <style>
                .no-border {
                    border: none !important;
                }

                .grid {
                    width: 100%;
                }

                select,
                textarea,
                input {
                    width: 100%;
                    /* Make all input elements take full width */
                }

                table.dataTable {
                    width: 100% !important;
                }

                .dataTables_wrapper {
                    width: 100%;
                }

                @media (max-width: 600px) {
                    .dataTables_wrapper {
                        padding: 0 10px;
                    }
                }

                #agendasxTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #agendasxTable_filter label {
                    margin-right: 2px;
                }

                #agendasxTable_filter input {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }


                #agendasxTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #agendasxTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #agendasxTable th,
                #agendasxTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #agendasxTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #agendasxTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #agendasxTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #agendasxTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #agendasxTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 2;
                    /* Optional, for better text alignment */
                }

                #agendasxTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #agendasxTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #agendasxTable tbody tr:hover td {
                    /* color: black; */
                }
            </style>
            <style>
                /* ✅ Memperkecil Lebar Kolom Actions */
                #agendasxTable th:nth-child(1),
                #agendasxTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #agendasxTable th:nth-child(4),
                #agendasxTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="relative overflow-hidden">
                <table id="agendasxTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-32 px-4 py-3 text-left font-medium">DocID</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Departement</th>
                            <th class="px-4 py-3 text-left font-medium">Info</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            {{-- <script>
                var currentUser = "{{ auth()->user()->username }}";
            </script> --}}


            <script>
                $(document).ready(function() {
                let table = $('#agendasxTable').DataTable({
                    ajax: "{{ route('dashapproval.json') }}",
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
                                    'inline-flex justify-center items-center w-[120px] px-3 py-1.5  text-sm  leading-tight font-medium text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ';
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
                                        "w-32 bg-amber-200/60 text-amber-800 dark:bg-amber-300/40 dark:text-amber-900 focus:outline-none pointer-events-none border border-amber-600/40 font-semibold px-4 py-2 text-center rounded";
                                } else if (data === 'P') {
                                    statusText = "On Progress";
                                    badgeClass =
                                        "w-32 bg-orange-200/60 text-orange-800 dark:bg-orange-300/40 dark:text-orange-900 focus:outline-none pointer-events-none border border-orange-600/40 font-semibold px-4 py-2 text-center rounded";
                                }
                            } else if (data === 'C') {
                                statusText = "Completed";
                                badgeClass =
                                    "w-32 bg-green-200/60 text-green-800 dark:bg-green-300/40 dark:text-green-900 focus:outline-none pointer-events-none border border-green-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'X') {
                                statusText = "Cancel";
                                badgeClass =
                                    "w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 focus:outline-none pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'R') {
                                statusText = "Rejected";
                                badgeClass =
                                    "w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 focus:outline-none pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else {
                                statusClass =
                                    "w-32 bg-gray-200/60 text-gray-700 dark:bg-gray-300/40 dark:text-gray-900 focus:outline-none pointer-events-none border border-gray-500/40 font-semibold px-4 py-2 text-center rounded";
                            }
                        }
                        return `<span class="${badgeClass}">${statusText}</span>`;
                    }

                }]
                });
                });
            </script>
        </div>
