<x-app-layout>
    <x-spp-list-styles />
    <style>
        .vendor-dialog { animation: vendor-dialog-in .18s ease-out; }
        @keyframes vendor-dialog-in { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { .vendor-dialog { animation: none; } }
    </style>
    @php
        $currentPage = Route::currentRouteName() == 'vendors' ? 'Vendors' : '';
        $isAdmin = $isAdmin ?? (isset($user) && $user->isAdmin());
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div
            class="dt-pretty rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Master Vendor" subtitle="Manage vendor data and their status">
                @if ($isAdmin)
                        <button id="syncVendorBtn"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 text-sm font-semibold text-green-700 transition hover:border-green-600 hover:bg-green-600 hover:text-white dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-300">
                            <i class="fa-solid fa-rotate text-xs"></i>Sync Vendor
                        </button>

                        <button id="addVendorBtn"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                            <i class="fa-solid fa-plus text-xs"></i>Add Vendor
                        </button>
                    @endif
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="vendorsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            @if ($isAdmin)
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                            @endif
                            <th class="px-4 py-3 text-left font-medium">Vendor ID</th>
                            <th class="px-4 py-3 text-left font-medium">Vendor Name</th>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">Contact Person</th>
                            <th class="px-4 py-3 text-left font-medium">Phone</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        @if ($isAdmin)
            <!-- Modal -->
            <div id="vendorModal" class="fixed inset-0 z-[1000] flex hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                <div class="vendor-dialog relative flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                    <div class="flex shrink-0 items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-store"></i></span>
                            <div>
                                <h2 id="vendorModalTitle" class="text-base font-bold text-gray-800 dark:text-white">Add Vendor</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Company details, address and contacts</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('closeVendorModal').click()" aria-label="Close"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                    </div>

                    <form id="vendorForm" class="flex min-h-0 flex-col">
                        <input type="hidden" id="id" name="id">

                        <div class="min-h-0 space-y-4 overflow-y-auto bg-gray-50 p-5 dark:bg-gray-900/60">
                            <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="mb-4 flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-building"></i></span>
                                    <div>
                                        <h3 class="text-sm font-bold leading-tight text-gray-800 dark:text-gray-100">Vendor Information</h3>
                                        <p class="text-xs text-gray-400">Identity of the vendor</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                                    <div class="sm:col-span-2">
                                        <label for="vendor_id" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor ID</label>
                                        <div class="relative">
                                            <input type="text" id="vendor_id" name="vendor_id" placeholder="e.g. V0001"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500" required>
                                            <i class="fa-solid fa-hashtag pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-4">
                                        <label for="vendor_name" class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor Name</label>
                                        <div class="relative">
                                            <input type="text" id="vendor_name" name="vendor_name" placeholder="Registered company name"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500" required>
                                            <i class="fa-solid fa-building pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label for="npwp" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">NPWP</label>
                                        <div class="relative">
                                            <input type="text" id="npwp" name="npwp" placeholder="Tax identification number"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-id-card pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label for="email" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</label>
                                        <div class="relative">
                                            <input type="email" id="email" name="email" placeholder="name@company.com"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-envelope pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="mb-4 flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-location-dot"></i></span>
                                    <div>
                                        <h3 class="text-sm font-bold leading-tight text-gray-800 dark:text-gray-100">Address</h3>
                                        <p class="text-xs text-gray-400">Where the vendor is located</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                                    <div class="sm:col-span-6">
                                        <label for="vendor_addr1" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Address Line 1</label>
                                        <div class="relative">
                                            <input type="text" id="vendor_addr1" name="vendor_addr1" placeholder="Street, building, number"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-location-dot pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-4">
                                        <label for="vendor_addr2" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Address Line 2</label>
                                        <div class="relative">
                                            <input type="text" id="vendor_addr2" name="vendor_addr2" placeholder="District, city, province"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-map pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="post_cd" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Postal Code</label>
                                        <div class="relative">
                                            <input type="text" id="post_cd" name="post_cd" placeholder="e.g. 12345"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-envelopes-bulk pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="mb-4 flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-address-book"></i></span>
                                    <div>
                                        <h3 class="text-sm font-bold leading-tight text-gray-800 dark:text-gray-100">Contact</h3>
                                        <p class="text-xs text-gray-400">Who to reach at the vendor</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                                    <div class="sm:col-span-3">
                                        <label for="contact_person" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact Person</label>
                                        <div class="relative">
                                            <input type="text" id="contact_person" name="contact_person" placeholder="Full name"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-user pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label for="contact_email" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact Email</label>
                                        <div class="relative">
                                            <input type="email" id="contact_email" name="contact_email" placeholder="name@company.com"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-at pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label for="contact_number1" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact Number 1</label>
                                        <div class="relative">
                                            <input type="text" id="contact_number1" name="contact_number1" placeholder="Mobile number"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-mobile-screen-button pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label for="contact_number2" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact Number 2</label>
                                        <div class="relative">
                                            <input type="text" id="contact_number2" name="contact_number2" placeholder="Other mobile number"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-mobile-screen-button pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label for="phone_number" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Phone Number</label>
                                        <div class="relative">
                                            <input type="text" id="phone_number" name="phone_number" placeholder="Office phone"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-phone pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label for="fax_no" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Fax No</label>
                                        <div class="relative">
                                            <input type="text" id="fax_no" name="fax_no" placeholder="Fax number"
                                                class="peer h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-300 hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500">
                                            <i class="fa-solid fa-fax pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition peer-focus:text-blue-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="text-xs text-gray-400"><span class="text-red-500">*</span> Required fields</p>
                            <div class="ml-auto flex items-center gap-3">
                                <button type="button" id="closeVendorModal"
                                    class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Cancel</button>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:opacity-60"><i class="fa-solid fa-check text-xs"></i>Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-blue-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const IS_ADMIN = @json($isAdmin);

        function showLoading() {
            $('#loadingOverlay').removeClass('hidden');
        }

        function hideLoading() {
            $('#loadingOverlay').addClass('hidden');
        }

        $(document).ready(function() {
            let dtColumns = [{
                data: null,
                defaultContent: ''
            }];

            if (IS_ADMIN) {
                dtColumns.push({
                    data: 'id',
                    render: function(data, type, row) {
                        return `
                            <div class="flex items-center justify-start gap-3">
                                <label class="switch">
                                    <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                    <span class="slider round"></span>
                                </label>
                                <button type="button" class="editVendorBtn inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-xs text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300" title="Edit vendor" aria-label="Edit vendor" data-id="${data}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        `;
                    }
                });
            }

            dtColumns = dtColumns.concat([
                {
                    data: 'vendor_id',
                    className: 'no-pointer'
                },
                {
                    data: 'vendor_name',
                    className: 'no-pointer'
                },
                {
                    data: 'email',
                    className: 'no-pointer'
                },
                {
                    data: 'contact_person',
                    className: 'no-pointer'
                },
                {
                    data: 'phone_number',
                    className: 'no-pointer'
                },
                {
                    data: 'status',
                    className: 'no-pointer',
                    render: function(data) {
                        return data === 'A'
                            ? '<span class="inline-flex min-w-[96px] items-center justify-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active</span>'
                            : '<span class="inline-flex min-w-[96px] items-center justify-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Inactive</span>';
                    }
                }
            ]);

            let table = $('#vendorsTable').DataTable({
                ajax: "{{ route('vendors.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [{
                    targets: 0,
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel"></i> Excel',
                        title: 'Vendor',
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
                        text: '<i class="fa-solid fa-file-csv"></i> CSV',
                        title: 'Vendor',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                columns: dtColumns
            });

            if (IS_ADMIN) {
                $('#addVendorBtn').click(function() {
                    $('#vendorModalTitle').text("Add Vendor");
                    $('#vendorForm')[0].reset();
                    $('#id').val('');
                    $('#vendorModal').removeClass('hidden');
                });

                $(document).on('click', '.editVendorBtn', function() {
                    let id = $(this).data('id');
                    showLoading();

                    $.get(`/vendors/${id}/edit`, function(v) {
                        $('#vendorModalTitle').text("Edit Vendor");
                        $('#id').val(v.id);
                        $('#vendor_id').val(v.vendor_id);
                        $('#vendor_name').val(v.vendor_name);
                        $('#vendor_addr1').val(v.vendor_addr1);
                        $('#vendor_addr2').val(v.vendor_addr2);
                        $('#email').val(v.email);
                        $('#contact_person').val(v.contact_person);
                        $('#phone_number').val(v.phone_number);
                        $('#npwp').val(v.npwp);
                        $('#contact_email').val(v.contact_email);
                        $('#contact_number1').val(v.contact_number1);
                        $('#contact_number2').val(v.contact_number2);
                        $('#fax_no').val(v.fax_no);
                        $('#post_cd').val(v.post_cd);

                        $('#vendorModal').removeClass('hidden');
                        hideLoading();
                    }).fail(function(xhr) {
                        hideLoading();

                        let msg = 'Gagal mengambil data vendor';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    });
                });

                $(document).on('change', '.toggleStatus', function() {
                    let id = $(this).data('id');
                    let newStatus = $(this).is(':checked') ? 'A' : 'X';

                    $.ajax({
                        url: `/vendors/${id}/toggle-status`,
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            status: newStatus
                        },
                        success: function() {
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            table.ajax.reload(null, false);

                            let msg = 'Gagal update status vendor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });
                        }
                    });
                });

                $('#vendorForm').submit(function(e) {
                    e.preventDefault();

                    let id = $('#id').val();
                    let url = id ? `/vendors/${id}` : "{{ route('vendors.store') }}";
                    let method = 'POST';
                    let formData = new FormData(document.getElementById('vendorForm'));

                    if (id) formData.append('_method', 'PUT');

                    showLoading();
                    $('#vendorForm button[type="submit"]').prop('disabled', true);

                    $.ajax({
                        url: url,
                        type: method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            hideLoading();
                            $('#vendorForm button[type="submit"]').prop('disabled', false);

                            $('#vendorModal').addClass('hidden');
                            $('#vendorForm')[0].reset();
                            $('#id').val('');
                            table.ajax.reload();

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Vendor saved successfully',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            hideLoading();
                            $('#vendorForm button[type="submit"]').prop('disabled', false);

                            let msg = 'Gagal menyimpan data vendor';

                            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors)
                                    .map(arr => arr.join(', '))
                                    .join('\n');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });
                        }
                    });
                });

                $('#closeVendorModal').click(function() {
                    $('#vendorForm')[0].reset();
                    $('#id').val('');
                    $('#vendorModal').addClass('hidden');
                });

                $('#syncVendorBtn').click(function() {
                    Swal.fire({
                        title: 'Sync Vendor?',
                        text: 'Data vendor dari VMS akan disinkronkan ke master vendor.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Sync',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#16a34a',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        showLoading();
                        $('#syncVendorBtn').prop('disabled', true);

                        $.ajax({
                            url: "{{ route('vendors.sync') }}",
                            type: "POST",
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                hideLoading();
                                $('#syncVendorBtn').prop('disabled', false);
                                table.ajax.reload(null, false);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: res.message || 'Sync vendor berhasil',
                                });
                            },
                            error: function(xhr) {
                                hideLoading();
                                $('#syncVendorBtn').prop('disabled', false);

                                let msg = 'Gagal sync vendor';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: msg
                                });
                            }
                        });
                    });
                });
            }
        });
    </script>
</x-app-layout>
