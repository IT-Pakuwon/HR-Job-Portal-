<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-scope' => 'rfcajobs'], 'Rfca Jobs', '📋', 'blue', $rfcajobs, false, null, 'scope-filter'],
                [['data-scope' => 'purchasingsubmit'], 'Purchasing Submit', '📦', 'purple', $purchasingSubmit, false, null, 'scope-filter'],
                [['data-scope' => 'financereceived'], 'Finance Received', '📋', 'sky', $financeReceived, false, null, 'scope-filter'],
                [['data-scope' => 'treasurypayment'], 'Treasury Payment', '💰', 'yellow', $treasuryPayment, false, null, 'scope-filter'],
                [['data-scope' => 'purchasingcalr'], 'Purchasing CALR', '📋', 'purple', $purchasingCalr, false, null, 'scope-filter'],
                [['data-scope' => 'completed'], 'Rfca Completed', '✅', 'green', $completed, false, null, 'scope-filter'],
                [['data-scope' => 'all'], 'Rfca All', '📋', 'all', $all, false, null, 'scope-filter'],
            ];
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div
            class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Rfca" subtitle="Rfca jobs, submissions and payments" title-id="pageTitle">
                <div id="rfcaTypeFilterWrap" class="flex items-center gap-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Rfca Type</span>
                    <input type="hidden" id="rfcaTypeFilter" value="">
                    <div class="inline-flex items-center gap-0.5 rounded-full bg-gray-100 p-0.5 dark:bg-white/10" role="group" aria-label="Filter by Rfca Type">
                        <button type="button" data-type="" class="rfca-type-btn rounded-full px-3 py-1 text-xs font-semibold transition-colors">All</button>
                        <button type="button" data-type="RFCA" class="rfca-type-btn rounded-full px-3 py-1 text-xs font-semibold transition-colors">RFCA</button>
                        <button type="button" data-type="RFP" class="rfca-type-btn rounded-full px-3 py-1 text-xs font-semibold transition-colors">RFP</button>
                    </div>
                </div>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="rfcaTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead
                        class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                        <tr id="thead-row"></tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        const currentUser = @json(auth()->user()->username ?? '');
        const dtControlColumn = {
            data: null,
            className: 'dtr-control',
            orderable: false,
            searchable: false,
            defaultContent: ''
        };


        $(function() {
            let scope = 'rfcajobs';
            const $title = $('#pageTitle');
            const $thead = $('#rfcaTable thead');
            let table;

            const titleMap = {
                rfcajobs: 'Rfca - Jobs',
                purchasingsubmit: 'Rfca - Purchasing Submit',
                financereceived: 'Rfca - Finance Received',
                treasurypayment: 'Rfca - Treasury Payment',
                purchasingcalr: 'Rfca - Purchasing CALR',
                completed: 'Rfca - Completed',
                all: 'Rfca - All',
            };


            function headerFor(sc) {
                return `
                    <th></th>
                    <th class="px-4 py-3 text-left font-medium">Rfca ID</th>
                    <th class="px-4 py-3 text-left font-medium">Rfca Date</th>
                    <th class="px-4 py-3 text-left font-medium">PO Nbr</th>
                    <th class="px-4 py-3 text-left font-medium">SPPB/J/K/T</th>
                    <th class="px-4 py-3 text-left font-medium">CS ID</th>
                    <th class="px-4 py-3 text-left font-medium">Company</th>
                    <th class="px-4 py-3 text-left font-medium">Vendor</th>
                    <th class="px-4 py-3 text-left font-medium">Created By</th>
                    <th class="px-4 py-3 text-left font-medium">Rfca Type</th>
                    <th class="px-4 py-3 text-left font-medium">Action</th>
                `;
            }



            function columnsFor(sc) {
                return [
                    dtControlColumn,
                    {
                        data: 'rfcaid',
                        render: (_v, _t, row) => renderRfcaLink(row)
                    },
                    {
                        data: 'rfcadate',
                        render: (_v, _t, row) => row.rfcadate_fmt ?? '',
                        className: 'text-left'
                    },
                    {
                        data: 'ponbr',
                        className: 'text-left',
                        // render: (_v, _t, row) => renderPoLink(row)
                    },
                    {
                        data: 'sppbjktid',
                        className: 'text-left',
                        // render: (_v, _t, row) => renderSppbLink(row)
                    },
                    {
                        data: 'csid',
                        className: 'text-left'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-left'
                    },
                    {
                        data: 'vendorname',
                        className: 'text-left'
                    },
                    // {
                    //     data: 'vendorname',
                    //     className: 'text-left',
                    //     render: (_v, _t, row) => {
                    //         const vendorName = row.vendorname ?? '-';
                    //         const vendorId = row.vendorid ?? '';

                    //         if (vendorId) {
                    //             return `
                    //                 <div class="min-w-[180px]">
                    //                     <div class="font-semibold text-gray-800 dark:text-gray-100">${vendorName}</div>
                    //                     <div class="text-xs text-gray-500 dark:text-gray-400">${vendorId}</div>
                    //                 </div>
                    //             `;
                    //         }

                    //         return vendorName;
                    //     }
                    // },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'rfca_type',
                        className: 'text-left',
                        render: (v) => {
                            const type = (v ?? '').toString().trim().toUpperCase();
                            const badgeMap = {
                                RFCA: 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                RFP: 'bg-purple-100 text-purple-700 dark:bg-purple-800/30 dark:text-purple-300'
                            };
                            const label = type || 'Not Set';
                            const colorClass = badgeMap[type] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';

                            return `<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${colorClass}">${label}</span>`;
                        }
                    },
                    {
                        data: null,
                        className: 'text-left no-export',
                        orderable: false,
                        searchable: false,
                        render: (_v, _t, row) => renderActionButton(row)
                    },
                ];
            }


            function orderFor(sc) {
                return [
                    [2, 'desc'], // rfcadate
                    [1, 'desc'], // rfcaid
                ];
            }


            function updateTitle(sc) {
                $title.text(titleMap[sc] ?? 'Rfca');

                // Jobs rows have no rfca_type yet, so the filter is meaningless there
                const isJobs = sc === 'rfcajobs';
                $('#rfcaTypeFilterWrap').toggleClass('hidden', isJobs);
                if (isJobs) setTypeFilter('');
            }

            function resetThead(sc) {
                const $table = $('#rfcaTable');

                // hapus thead lama (yang mungkin sisa clone DataTables)
                $table.find('thead').remove();

                // buat ulang thead + tr
                const theadHtml = `
                        <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b  text-sm ">
                            <tr id="thead-row">${headerFor(sc)}</tr>
                            </thead>`;
                $table.prepend(theadHtml);

                // pastikan tbody ada
                if ($table.find('tbody').length === 0) {
                    $table.append(
                        `<tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>`
                    );
                }
            }

            function rebuild(sc) {
                if ($.fn.DataTable.isDataTable('#rfcaTable')) {
                    $('#rfcaTable').DataTable().clear().destroy();
                }
                resetThead(sc);

                table = $('#rfcaTable').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],

                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa-solid fa-file-excel"></i> Excel',
                            title: 'List_RFCA',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: ':visible:not(.no-export)',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '<i class="fa-solid fa-file-csv"></i> CSV',
                            title: 'List_RFCA',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible:not(.no-export)',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0 // 👈 this is REQUIRED
                        }
                    },

                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    order: orderFor(sc),
                    ajax: {
                        url: "{{ route('rfcalist.json') }}",
                        type: "GET",
                        data: function(d) {
                            d.scope = sc;
                            d.rfca_type = $('#rfcaTypeFilter').val();
                        }
                    },
                    columns: columnsFor(sc),
                    searchDelay: 400,
                    stateSave: false,
                    responsive: true
                });
            }


            function renderPoLink(row) {
                const text = row.ponbr ?? '';
                if (row.ponbr_eid) {
                    const url = `/showpo/${encodeURIComponent(row.ponbr_eid)}`;
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
                }
                return text;
            }

            function renderSppbLink(row) {
                const text = row.sppbjktid ?? '';
                if (row.sppb_route && row.sppb_eid) {
                    const url = `/${row.sppb_route}/${encodeURIComponent(row.sppb_eid)}`;
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${text}</span></a>`;
                }
                return text;
            }

            function renderRfcaLink(row) {
                const label = row.rfcaid ?? '';
                const hash = row.rfcaid_eid || row.eid || row.hash || row.id;

                if (!label) return '';
                if (!hash) {
                    return `<span class="inline-flex items-center px-3 py-1.5  text-sm  font-semibold rounded bg-gray-400 text-white">${label}</span>`;
                }

                const statusRaw = (row.status ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? '').toString();
                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');

                if (isRevise && isOwner) {
                    const url = `/editrfcas/${encodeURIComponent(hash)}`;
                    return `<a href="${url}" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-amber-800 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300" title="Edit (Revise)"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:rotate-12"><i class="fa-solid fa-pen text-[10px]"></i></span><span>${label}</span></a>`;
                }

                const url = row.showrfca_url || `/showrfca/${encodeURIComponent(hash)}`;
                return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${label}</span></a>`;
            }

            function renderActionButton(row) {
                const hash = row.rfcaid_eid;
                if (!row.can_process_step || !hash) return '';

                return `<button type="button" data-hash="${hash}" data-rfcaid="${row.rfcaid}"
                    class="btnProcessStep inline-flex items-center gap-1 rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-green-700">
                    Process Step</button>`;
            }

            $(document).on('click', '.btnProcessStep', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const hash = $btn.data('hash');

                Swal.fire({
                    title: 'Approve Active Step?',
                    text: `This will mark the current active step of ${$btn.data('rfcaid')} as Approved.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#16a34a',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $btn.prop('disabled', true);
                    $.ajax({
                        url: `/rfca/${encodeURIComponent(hash)}/approve-step`,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success) {
                                toastr.success(res.message || 'Active RFCA Step approved.');
                                table.ajax.reload(null, false);
                            } else {
                                toastr.error(res.message || 'Failed to approve RFCA Step.');
                                $btn.prop('disabled', false);
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Failed to approve RFCA Step.');
                            $btn.prop('disabled', false);
                        }
                    });
                });
            });

            const typeBtnActive = {
                '': 'bg-white text-gray-800 shadow-sm dark:bg-gray-600 dark:text-white',
                RFCA: 'bg-blue-100 text-blue-700 shadow-sm dark:bg-blue-800/40 dark:text-blue-300',
                RFP: 'bg-purple-100 text-purple-700 shadow-sm dark:bg-purple-800/40 dark:text-purple-300',
            };
            const typeBtnIdle = 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';

            // Single place that updates both the hidden value and the pill highlight
            function setTypeFilter(val) {
                $('#rfcaTypeFilter').val(val);
                $('.rfca-type-btn').each(function() {
                    const t = $(this).data('type');
                    $(this)
                        .removeClass(Object.values(typeBtnActive).join(' ') + ' ' + typeBtnIdle)
                        .addClass(t === val ? typeBtnActive[t] : typeBtnIdle);
                });
            }

            $('.rfca-type-btn').on('click', function() {
                const val = $(this).data('type');
                if (val === $('#rfcaTypeFilter').val()) return;
                setTypeFilter(val);
                table.ajax.reload();
            });

            setTypeFilter('');

            // init
            updateTitle(scope);
            rebuild(scope);

            // ganti scope
            $('.scope-filter').on('click', function(e) {
                e.preventDefault();
                scope = $(this).data('scope') || 'rfcajobs';
                updateTitle(scope);
                rebuild(scope);

                $('.scope-filter').removeClass('active');
                $(this).addClass('active');
                localStorage.setItem('activeRfcaScope', scope);
            });

            // restore scope terakhir
            const savedRfcaScope = localStorage.getItem('activeRfcaScope');
            if (savedRfcaScope) {
                scope = savedRfcaScope;
                updateTitle(scope);
                rebuild(scope);
                $('.scope-filter').removeClass('active');
                $(`.scope-filter[data-scope="${scope}"]`).addClass('active');
            } else {
                $(`.scope-filter[data-scope="rfcajobs"]`).addClass('active');
            }
        });
    </script>
</x-app-layout>
