<x-app-layout>
    @include('pages.carexpense.partial.style')

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- Cost Type Filter Cards --}}
        <div class="grid grid-cols-4 gap-4">

            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter active group block h-full flex-1 w-full" data-filter="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-slate-400 bg-slate-200/20 p-3 text-slate-600 transition-all duration-300 ease-in-out hover:bg-slate-100 hover:shadow-md active:scale-95 dark:border-slate-500 dark:text-slate-300 dark:hover:bg-slate-700/30">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>
                    </div>
                </a>
            </button>

            @php
                $cardColors = [
                    'border-blue-600 bg-blue-200/20 text-blue-600 hover:bg-blue-100 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-500/20',
                    'border-emerald-600 bg-emerald-200/20 text-emerald-600 hover:bg-emerald-100 dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-500/20',
                    'border-orange-500 bg-orange-200/20 text-orange-600 hover:bg-orange-100 dark:border-orange-400 dark:text-orange-400 dark:hover:bg-orange-500/20',
                    'border-violet-600 bg-violet-200/20 text-violet-600 hover:bg-violet-100 dark:border-violet-500 dark:text-violet-400 dark:hover:bg-violet-500/20',
                    'border-rose-600 bg-rose-200/20 text-rose-600 hover:bg-rose-100 dark:border-rose-500 dark:text-rose-400 dark:hover:bg-rose-500/20',
                    'border-cyan-600 bg-cyan-200/20 text-cyan-600 hover:bg-cyan-100 dark:border-cyan-500 dark:text-cyan-400 dark:hover:bg-cyan-500/20',
                ];
            @endphp
            @foreach ($costTypes as $type)
                <button type="button" class="text-left">
                    <a href="#" class="status-filter group block h-full flex-1 w-full" data-filter="{{ $type->id }}">
                        <div
                            class="status-card flex h-full items-center gap-3 rounded-lg border p-3 transition-all duration-300 ease-in-out hover:shadow-md active:scale-95 {{ $cardColors[$loop->index % count($cardColors)] }}">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🚗</div>
                            <div class="flex min-w-0 flex-grow flex-col leading-tight">
                                <p class="break-words text-sm font-medium">{{ $type->category_name }}</p>
                            </div>
                        </div>
                    </a>
                </button>
            @endforeach

        </div>

        {{-- Table --}}
        <div
            class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div
                class="flex flex-col gap-4 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">

                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                    Car Expense
                </h2>

                <div class="flex items-center gap-3">

                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search..."
                            class="h-10 rounded-lg border border-slate-200 bg-white pl-9 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-100 dark:placeholder:text-slate-500">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    </div>

                    <button type="button" id="btnOpenCreate"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        <i class="fa-solid fa-plus mr-2 text-xs"></i>
                        Create
                    </button>

                </div>

            </div>

            {{-- Filter Row --}}
            <div class="flex flex-wrap items-end gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Nopol</label>
                    <select id="filterNopol"
                        class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-100">
                        <option value="">All</option>
                        @foreach ($kendaraan as $k)
                            <option value="{{ $k->no_polisi }}">{{ $k->no_polisi }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">From</label>
                    <input type="date" id="filterDateFrom"
                        class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-100">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">To</label>
                    <input type="date" id="filterDateTo"
                        class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-100">
                </div>

                <button type="button" id="btnResetFilters"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-500 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-400 dark:hover:bg-white/[0.08]">
                    <i class="fa-solid fa-rotate-left mr-1 text-xs"></i> Reset
                </button>

            </div>

            <div class="relative overflow-x-auto">

                <table class="w-full min-w-full border-separate border-spacing-0 text-sm">

                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3 text-center font-medium">#</th>
                            <th class="px-4 py-3 text-left font-medium">Ref No</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Nopol</th>
                            <th class="px-4 py-3 text-left font-medium">Driver</th>
                            <th class="px-4 py-3 text-left font-medium">Cost Type</th>
                            <th class="px-4 py-3 text-left font-medium">Description</th>
                            <th class="px-4 py-3 text-right font-medium">Qty</th>
                            <th class="px-4 py-3 text-right font-medium">Amount</th>
                            <th class="px-4 py-3 text-center font-medium">Action</th>
                        </tr>
                    </thead>

                    <tbody id="carExpenseTableBody">
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-sm text-slate-400">Loading...</td>
                        </tr>
                    </tbody>

                </table>

            </div>

            {{-- Pagination --}}
            <div
                class="flex flex-col items-center justify-between gap-3 border-t border-gray-100 px-5 py-3 dark:border-white/[0.06] sm:flex-row">
                <p id="paginationInfo" class="text-sm text-slate-500 dark:text-slate-400"></p>
                <div id="paginationControls" class="flex items-center gap-1"></div>
            </div>

        </div>

        {{-- =========================================================
             CREATE MODAL
        ========================================================= --}}
        <div id="createModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-2xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Create Car Expense</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Record a new car expense entry.</p>
                    </div>
                    <button id="btnCloseCreateModal" type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="space-y-4 bg-slate-50 p-5 dark:bg-[#0b1220]">

                    <form id="createForm" class="space-y-4">

                        @csrf

                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                            <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                                <h3
                                    class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    Expense Information
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Date
                                    </label>
                                    <input type="date" name="ref_date" id="create_ref_date" required
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                </div>

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Cost Type
                                    </label>
                                    <select name="cost_type" id="create_cost_type" required
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">
                                        <option value="">Select Cost Type</option>
                                        @foreach ($costTypes as $ct)
                                            <option value="{{ $ct->id }}">{{ $ct->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Nopol
                                    </label>
                                    <select name="nopol" id="create_nopol" required
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">
                                        <option value="">Select Nopol</option>
                                        @foreach ($kendaraan as $k)
                                            <option value="{{ $k->no_polisi }}">
                                                {{ $k->no_polisi }}{{ $k->namakendaraan ? ' - ' . $k->namakendaraan : '' }}{{ $k->merk_kendaraan ? ' (' . $k->merk_kendaraan . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Driver
                                    </label>
                                    <select name="driver" id="create_driver" required
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">
                                        <option value="">Select Driver</option>
                                        @foreach ($drivers as $d)
                                            <option value="{{ $d->drivername }}">{{ $d->drivername }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Description
                                    </label>
                                    <textarea name="cost_descr" id="create_cost_descr" rows="3" required placeholder="Describe the expense..."
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500"></textarea>
                                </div>

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Qty
                                    </label>
                                    <input type="number" name="cost_qty" id="create_cost_qty" required
                                        min="1" placeholder="0"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                </div>

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Amount (IDR)
                                    </label>
                                    <input type="text" inputmode="numeric" name="cost_amount" id="create_cost_amount" required
                                        placeholder="0" autocomplete="off"
                                        class="amount-input h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                </div>

                            </div>

                        </div>

                        {{-- ATTACHMENT --}}
                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                            <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                                <h3
                                    class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    Attachments <span class="font-normal normal-case text-slate-400">(optional, max 5 MB each)</span>
                                </h3>
                            </div>

                            <div class="p-5">
                                <label for="create_attachments"
                                    class="group flex cursor-pointer items-center justify-center gap-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-5 transition-all duration-200 hover:border-slate-400 hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.03] dark:hover:border-blue-500/30 dark:hover:bg-blue-500/[0.05]">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-300">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Upload Files</p>
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">PDF, DOCX, XLSX, PNG, JPG</p>
                                    </div>
                                    <input type="file" id="create_attachments" multiple class="hidden">
                                </label>
                                <div id="createAttachmentPreview" class="mt-3 flex flex-wrap gap-2"></div>
                            </div>

                        </div>

                        <div
                            class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
                            <div class="flex items-center justify-end gap-3">
                                <button type="button" id="btnCancelCreate"
                                    class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]">
                                    Cancel
                                </button>
                                <button type="submit" id="btnSubmitCreate"
                                    class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                                    Save
                                </button>
                            </div>
                        </div>

                    </form>

                </div>

            </div>

        </div>

        {{-- =========================================================
             SHOW MODAL
        ========================================================= --}}
        <div id="showModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-2xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
                    <div>
                        <h2 id="show_refnbr" class="text-xl font-bold text-slate-900 dark:text-white">
                            Car Expense Detail
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Expense record information.</p>
                    </div>
                    <button id="btnCloseShowModal" type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="space-y-4 bg-slate-50 p-5 dark:bg-[#0b1220]">

                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div class="border-b border-slate-200 px-5 py-3 dark:border-white/10">
                            <h3
                                class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                                Expense Information
                            </h3>
                        </div>

                        <div id="show_information"
                            class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 text-sm md:grid-cols-2">
                        </div>

                    </div>

                    {{-- ATTACHMENTS --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-white/10">
                            <h3
                                class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                                Attachments
                            </h3>
                            <label for="show_upload_files"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]">
                                <i class="fa-solid fa-plus text-[10px]"></i> Upload
                                <input type="file" id="show_upload_files" multiple class="hidden">
                            </label>
                        </div>

                        <div id="show_attachments" class="min-h-[60px] p-4">
                            <p class="text-sm text-slate-400">Loading...</p>
                        </div>

                    </div>

                </div>

                <div
                    class="sticky bottom-0 z-20 flex items-center justify-between border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
                    <button type="button" id="btnCloseShowModalFooter"
                        class="text-sm text-slate-500 transition hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">
                        Close
                    </button>
                    <div class="flex items-center gap-3">
                        <button type="button" id="btnDeleteFromShow"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                            <i class="fa-solid fa-trash text-xs"></i>
                            Delete
                        </button>
                        <button type="button" id="btnEditFromShow"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                            <i class="fa-solid fa-pen text-xs"></i>
                            Edit
                        </button>
                    </div>
                </div>

            </div>

        </div>

        {{-- =========================================================
             EDIT MODAL
        ========================================================= --}}
        <div id="editModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

            <div
                class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
            </div>

            <div
                class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-2xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

                <div
                    class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
                    <div>
                        <h2 id="edit_refnbr" class="text-xl font-bold text-slate-900 dark:text-white">
                            Edit Car Expense
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Update expense record.</p>
                    </div>
                    <button id="btnCloseEditModal" type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="space-y-4 bg-slate-50 p-5 dark:bg-[#0b1220]">

                    <form id="editForm" class="space-y-4">

                        @csrf
                        @method('PUT')

                        <input type="hidden" id="edit_eid">

                        <div
                            class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                            <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                                <h3
                                    class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                    Expense Information
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Date
                                    </label>
                                    <input type="date" name="ref_date" id="edit_ref_date" required
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                </div>

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Cost Type
                                    </label>
                                    <select name="cost_type" id="edit_cost_type" required
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">
                                        <option value="">Select Cost Type</option>
                                        @foreach ($costTypes as $ct)
                                            <option value="{{ $ct->id }}">{{ $ct->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Nopol
                                    </label>
                                    <select name="nopol" id="edit_nopol" required
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">
                                        <option value="">Select Nopol</option>
                                        @foreach ($kendaraan as $k)
                                            <option value="{{ $k->no_polisi }}">
                                                {{ $k->no_polisi }}{{ $k->namakendaraan ? ' - ' . $k->namakendaraan : '' }}{{ $k->merk_kendaraan ? ' (' . $k->merk_kendaraan . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Driver
                                    </label>
                                    <select name="driver" id="edit_driver" required
                                        class="select2 h-11 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">
                                        <option value="">Select Driver</option>
                                        @foreach ($drivers as $d)
                                            <option value="{{ $d->drivername }}">{{ $d->drivername }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Description
                                    </label>
                                    <textarea name="cost_descr" id="edit_cost_descr" rows="3" required
                                        class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100"></textarea>
                                </div>

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Qty
                                    </label>
                                    <input type="number" name="cost_qty" id="edit_cost_qty" required min="1"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                </div>

                                <div>
                                    <label
                                        class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                        Amount (IDR)
                                    </label>
                                    <input type="text" inputmode="numeric" name="cost_amount" id="edit_cost_amount" required
                                        autocomplete="off"
                                        class="amount-input h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                </div>

                            </div>

                        </div>

                        <div
                            class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
                            <div class="flex items-center justify-end gap-3">
                                <button type="button" id="btnCancelEdit"
                                    class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]">
                                    Cancel
                                </button>
                                <button type="submit" id="btnSubmitEdit"
                                    class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                                    Save Changes
                                </button>
                            </div>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

    <script>
        const CarExpenseRoutes = {
            json:             @json(route('carexpense.json')),
            store:            @json(route('carexpense.store')),
            show:             @json(url('/carexpense/show/__EID__')),
            update:           @json(url('/carexpense/update/__EID__')),
            destroy:          @json(url('/carexpense/delete/__EID__')),
            attachments:      @json(url('/carexpense/attachments/__EID__')),
            uploadAttachment: @json(url('/carexpense/upload-attachment/__EID__')),
            deleteAttachment: @json(url('/carexpense/delete-attachment/__ID__')),
        };

        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const ajaxHeaders = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

        // ---- ATTACHMENT HELPERS ----

        function fileIcon(ext) {
            const e = (ext || '').toLowerCase();
            if (['jpg','jpeg','png','gif','webp'].includes(e)) return 'fa-image text-green-500';
            if (e === 'pdf') return 'fa-file-pdf text-red-500';
            if (['doc','docx'].includes(e)) return 'fa-file-word text-blue-500';
            if (['xls','xlsx'].includes(e)) return 'fa-file-excel text-emerald-500';
            return 'fa-file text-slate-400';
        }

        function formatBytes(bytes) {
            if (!bytes) return '';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        function renderAttachmentList(attachments, containerId, { canDelete = true } = {}) {
            const box = document.getElementById(containerId);
            if (!attachments || attachments.length === 0) {
                box.innerHTML = `<p class="text-sm text-slate-400 dark:text-slate-500">No attachments yet.</p>`;
                return;
            }
            box.innerHTML = attachments.map(a => `
                <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 dark:border-white/10 dark:bg-white/[0.03]">
                    <i class="fa-solid ${fileIcon(a.extention)} w-5 text-center"></i>
                    <div class="min-w-0 flex-1">
                        <a href="${a.signed_url ?? '#'}" target="_blank"
                            class="block truncate text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">
                            ${a.attachment_name ?? a.filename}
                        </a>
                        <p class="text-xs text-slate-400">${formatBytes(a.filesize)}</p>
                    </div>
                    ${canDelete ? `
                    <button type="button" onclick="deleteAttachment(${a.id}, '${containerId}')"
                        class="ml-auto shrink-0 text-slate-400 transition hover:text-red-500">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>` : ''}
                </div>
            `).join('');
        }

        async function loadShowAttachments(eid) {
            const box = document.getElementById('show_attachments');
            box.innerHTML = `<p class="text-sm text-slate-400">Loading...</p>`;
            try {
                const res  = await fetch(CarExpenseRoutes.attachments.replace('__EID__', eid), { headers: ajaxHeaders });
                const json = await res.json();
                renderAttachmentList(json.attachments ?? [], 'show_attachments');
            } catch {
                box.innerHTML = `<p class="text-sm text-red-400">Failed to load attachments.</p>`;
            }
        }

        async function deleteAttachment(id, containerId) {
            const confirmed = await Swal.fire({
                title: 'Delete attachment?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Delete',
            });
            if (!confirmed.isConfirmed) return;

            const url = CarExpenseRoutes.deleteAttachment.replace('__ID__', id);
            await fetch(url, {
                method: 'DELETE',
                headers: { ...ajaxHeaders, 'X-CSRF-TOKEN': CSRF },
            });
            loadShowAttachments(currentEid);
        }

        async function uploadFilesToExpense(eid, files) {
            if (!files || files.length === 0) return;
            const fd = new FormData();
            Array.from(files).forEach(f => fd.append('attachments[]', f));
            fd.append('_token', CSRF);
            try {
                await fetch(CarExpenseRoutes.uploadAttachment.replace('__EID__', eid), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });
            } catch (e) {
                console.error('Attachment upload failed', e);
            }
        }

        // Create modal file preview
        document.getElementById('create_attachments').addEventListener('change', function () {
            const preview = document.getElementById('createAttachmentPreview');
            preview.innerHTML = Array.from(this.files).map(f => `
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600 dark:bg-white/[0.07] dark:text-slate-300">
                    <i class="fa-solid ${fileIcon(f.name.split('.').pop())}"></i>
                    ${f.name}
                </span>
            `).join('');
        });

        // Show modal file upload
        document.getElementById('show_upload_files').addEventListener('change', async function () {
            if (!this.files.length || !currentEid) return;
            const label = this.labels[0];
            if (label) label.classList.add('opacity-50', 'pointer-events-none');
            await uploadFilesToExpense(currentEid, this.files);
            this.value = '';
            if (label) label.classList.remove('opacity-50', 'pointer-events-none');
            loadShowAttachments(currentEid);
        });

        const CostTypes = @json($costTypes->keyBy('id'));

        let currentFilter = '';
        let currentPage = 1;
        let currentSearch = '';
        let currentNopol = '';
        let currentDateFrom = '';
        let currentDateTo = '';
        let currentEid = null;

        function formatIDR(val) {
            return 'Rp ' + Number(val).toLocaleString('id-ID');
        }

        function formatDate(val) {
            if (!val) return '-';
            return val;
        }

        function formatDateTime(val) {
            if (!val) return '-';
            const d = new Date(val);
            if (isNaN(d)) return val;
            return d.toLocaleString('id-ID', {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', second: '2-digit',
                hour12: false,
            }).replace(/\//g, '-');
        }

        function getCostTypeName(id) {
            return CostTypes[id]?.category_name ?? id ?? '-';
        }

        function openModal(id) {
            const el = document.getElementById(id);
            el.classList.remove('hidden');
            el.classList.add('flex');
            requestAnimationFrame(() => {
                el.querySelector('.modal-backdrop')?.classList.replace('opacity-0', 'opacity-100');
                const panel = el.querySelector('.modal-panel');
                if (panel) {
                    panel.classList.replace('opacity-0', 'opacity-100');
                    panel.classList.replace('translate-y-4', 'translate-y-0');
                    panel.classList.replace('scale-[0.98]', 'scale-100');
                }
            });
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            el.querySelector('.modal-backdrop')?.classList.replace('opacity-100', 'opacity-0');
            const panel = el.querySelector('.modal-panel');
            if (panel) {
                panel.classList.replace('opacity-100', 'opacity-0');
                panel.classList.replace('translate-y-0', 'translate-y-4');
                panel.classList.replace('scale-100', 'scale-[0.98]');
            }
            setTimeout(() => {
                el.classList.add('hidden');
                el.classList.remove('flex');
            }, 200);
        }

        // ---- FETCH TABLE ----

        async function fetchTable() {
            const params = new URLSearchParams({
                page: currentPage,
                search: currentSearch,
                filter: currentFilter,
                nopol: currentNopol,
                date_from: currentDateFrom,
                date_to: currentDateTo,
            });

            const tbody = document.getElementById('carExpenseTableBody');
            tbody.innerHTML =
                `<tr><td colspan="10" class="px-4 py-8 text-center text-sm text-slate-400">Loading...</td></tr>`;

            try {
                const res  = await fetch(`${CarExpenseRoutes.json}?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const json = await res.json();
                renderTable(json.data, json.total, json.page);
            } catch (err) {
                tbody.innerHTML =
                    `<tr><td colspan="10" class="px-4 py-8 text-center text-sm text-red-400">Failed to load data.</td></tr>`;
                console.error('fetchTable error:', err);
            }
        }

        function renderTable(rows, total, page) {
            const tbody = document.getElementById('carExpenseTableBody');
            const perPage = 10;
            const offset = (page - 1) * perPage;
            const totalPages = Math.ceil(total / perPage);

            if (!rows || rows.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="10" class="px-4 py-8 text-center text-sm text-slate-400">No data found.</td></tr>`;
                document.getElementById('paginationInfo').textContent = '';
                document.getElementById('paginationControls').innerHTML = '';
                return;
            }

            tbody.innerHTML = rows.map((row, i) => `
                <tr class="border-b border-gray-100 transition hover:bg-slate-50 dark:border-white/[0.04] dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-3 text-center text-xs text-slate-400">${offset + i + 1}</td>
                    <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200">${row.refnbr ?? '-'}</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">${formatDate(row.ref_date)}</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">${row.nopol ?? '-'}</td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">${row.driver ?? '-'}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                            ${row.cost_type_name ?? row.cost_type ?? '-'}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300 max-w-[180px] truncate" title="${row.cost_descr ?? ''}">${row.cost_descr ?? '-'}</td>
                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">${row.cost_qty ?? 0}</td>
                    <td class="px-4 py-3 text-right font-medium text-slate-700 dark:text-slate-200">${formatIDR(row.cost_amount ?? 0)}</td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" onclick="openDetail('${row.eid}')"
                            class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-600 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]">
                            <i class="fa-solid fa-eye mr-1 text-[10px]"></i> View
                        </button>
                    </td>
                </tr>
            `).join('');

            const from = offset + 1;
            const to = Math.min(offset + rows.length, total);
            document.getElementById('paginationInfo').textContent = `Showing ${from}–${to} of ${total} records`;

            renderPagination(page, totalPages);
        }

        function renderPagination(page, totalPages) {
            const ctrl = document.getElementById('paginationControls');
            if (totalPages <= 1) {
                ctrl.innerHTML = '';
                return;
            }

            const btnClass = (active) =>
                `inline-flex h-8 w-8 items-center justify-center rounded-lg text-xs font-medium transition
                ${active
                    ? 'bg-slate-900 text-white dark:bg-blue-600'
                    : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]'}`;

            let html = `
                <button class="${btnClass(false)}" ${page === 1 ? 'disabled' : ''} onclick="goPage(${page - 1})">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                </button>`;

            const range = [...new Set([1, page - 1, page, page + 1, totalPages].filter(p => p >= 1 && p <= totalPages))]
                .sort((a, b) => a - b);
            let prev = 0;
            range.forEach(p => {
                if (prev && p - prev > 1) html += `<span class="px-1 text-slate-400">…</span>`;
                html += `<button class="${btnClass(p === page)}" onclick="goPage(${p})">${p}</button>`;
                prev = p;
            });

            html += `
                <button class="${btnClass(false)}" ${page === totalPages ? 'disabled' : ''} onclick="goPage(${page + 1})">
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>`;

            ctrl.innerHTML = html;
        }

        function goPage(p) {
            currentPage = p;
            fetchTable();
        }

        // ---- FILTER CARDS ----

        document.querySelectorAll('.status-filter').forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.status-filter').forEach(x => x.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter ?? '';
                currentPage = 1;
                fetchTable();
            });
        });

        // ---- SEARCH ----

        let searchTimer;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                currentSearch = this.value.trim();
                currentPage = 1;
                fetchTable();
            }, 400);
        });

        // ---- NOPOL & DATE RANGE FILTERS ----

        document.getElementById('filterNopol').addEventListener('change', function() {
            currentNopol = this.value;
            currentPage = 1;
            fetchTable();
        });

        document.getElementById('filterDateFrom').addEventListener('change', function() {
            currentDateFrom = this.value;
            currentPage = 1;
            fetchTable();
        });

        document.getElementById('filterDateTo').addEventListener('change', function() {
            currentDateTo = this.value;
            currentPage = 1;
            fetchTable();
        });

        document.getElementById('btnResetFilters').addEventListener('click', function() {
            document.getElementById('filterNopol').value = '';
            document.getElementById('filterDateFrom').value = '';
            document.getElementById('filterDateTo').value = '';
            currentNopol = '';
            currentDateFrom = '';
            currentDateTo = '';
            currentPage = 1;
            fetchTable();
        });

        // ---- OPEN DETAIL ----

        async function openDetail(eid) {
            currentEid = eid;

            openModal('showModal');

            document.getElementById('show_refnbr').textContent = 'Loading...';
            document.getElementById('show_information').innerHTML = '';

            const url = CarExpenseRoutes.show.replace('__EID__', eid);
            const res  = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const json = await res.json();

            if (!json.success) {
                Swal.fire('Error', json.message, 'error');
                closeModal('showModal');
                return;
            }

            const d = json.data;

            document.getElementById('show_refnbr').textContent = d.refnbr;

            const field = (label, value) => `
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">${label}</p>
                    <p class="mt-1 text-sm font-medium text-slate-700 dark:text-slate-200">${value ?? '-'}</p>
                </div>`;

            document.getElementById('show_information').innerHTML = `
                ${field('Date', formatDate(d.ref_date))}
                ${field('Nopol', d.nopol)}
                ${field('Driver', d.driver)}
                ${field('Cost Type', d.cost_type_name)}
                ${field('Qty', d.cost_qty)}
                ${field('Amount', formatIDR(d.cost_amount))}
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Description</p>
                    <p class="mt-1 text-sm font-medium text-slate-700 dark:text-slate-200">${d.cost_descr ?? '-'}</p>
                </div>
                ${field('Created By', d.created_by)}
                ${field('Created At', formatDateTime(d.created_at))}
            `;

            loadShowAttachments(eid);
        }

        // ---- EDIT FROM SHOW ----

        document.getElementById('btnEditFromShow').addEventListener('click', function() {
            if (!currentEid) return;

            const url = CarExpenseRoutes.show.replace('__EID__', currentEid);

            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).then(json => {
                if (!json.success) return;
                const d = json.data;

                document.getElementById('edit_eid').value = d.eid;
                document.getElementById('edit_refnbr').textContent = d.refnbr;
                document.getElementById('edit_ref_date').value = d.ref_date ?? '';
                document.getElementById('edit_cost_descr').value = d.cost_descr ?? '';
                document.getElementById('edit_cost_qty').value = d.cost_qty ?? '';
                document.getElementById('edit_cost_amount').value = formatThousands(d.cost_amount ?? '');

                const setSelect2 = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) {
                        $(el).val(val).trigger('change');
                    }
                };

                setSelect2('edit_nopol', d.nopol);
                setSelect2('edit_driver', d.driver);
                setSelect2('edit_cost_type', d.cost_type);

                closeModal('showModal');
                openModal('editModal');
            });
        });

        // ---- DELETE FROM SHOW ----

        document.getElementById('btnDeleteFromShow').addEventListener('click', function() {
            if (!currentEid) return;

            Swal.fire({
                title: 'Delete this record?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            }).then(result => {
                if (!result.isConfirmed) return;

                const url = CarExpenseRoutes.destroy.replace('__EID__', currentEid);

                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    })
                    .then(r => r.json())
                    .then(json => {
                        if (json.success) {
                            closeModal('showModal');
                            Swal.fire('Deleted!', json.message, 'success');
                            fetchTable();
                        } else {
                            Swal.fire('Error', json.message, 'error');
                        }
                    });
            });
        });

        // ---- AMOUNT FORMATTING ----

        function formatThousands(val) {
            const num = String(val).replace(/[^0-9]/g, '');
            return num === '' ? '' : Number(num).toLocaleString('id-ID');
        }

        function stripAmountCommas(inputId) {
            const el = document.getElementById(inputId);
            if (el) el.value = el.value.replace(/\./g, '').replace(/,/g, '');
        }

        document.querySelectorAll('.amount-input').forEach(el => {
            el.addEventListener('input', function () {
                const raw = this.value.replace(/\./g, '').replace(/[^0-9]/g, '');
                const pos = this.selectionStart;
                const prevLen = this.value.length;
                this.value = raw === '' ? '' : Number(raw).toLocaleString('id-ID');
                const diff = this.value.length - prevLen;
                this.setSelectionRange(pos + diff, pos + diff);
            });
        });

        // ---- CREATE FORM ----

        document.getElementById('btnOpenCreate').addEventListener('click', () => {
            document.getElementById('createForm').reset();
            document.getElementById('createAttachmentPreview').innerHTML = '';
            $('.select2').val(null).trigger('change');
            openModal('createModal');
        });

        document.getElementById('createForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitCreate');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs mr-2"></i>Saving...';

            const files = document.getElementById('create_attachments').files;

            stripAmountCommas('create_cost_amount');

            const createFd = new FormData(this);
            createFd.set('nopol',      $('#create_nopol').val()      || '');
            createFd.set('driver',     $('#create_driver').val()     || '');
            createFd.set('cost_type',  $('#create_cost_type').val()  || '');

            try {
                const res  = await fetch(CarExpenseRoutes.store, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: createFd,
                });
                const json = await res.json();

                if (!json.success) {
                    Swal.fire('Error', json.message, 'error');
                    return;
                }

                // Upload attachments if any were selected
                if (files.length > 0) {
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs mr-2"></i>Uploading...';
                    await uploadFilesToExpense(json.data.eid, files);
                }

                closeModal('createModal');
                Swal.fire('Success', json.message, 'success');
                fetchTable();
            } catch {
                Swal.fire('Error', 'Request failed.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk text-xs mr-2"></i>Save';
            }
        });

        // ---- EDIT FORM ----

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const eid = document.getElementById('edit_eid').value;
            const btn = document.getElementById('btnSubmitEdit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs mr-2"></i>Saving...';

            const url = CarExpenseRoutes.update.replace('__EID__', eid);

            stripAmountCommas('edit_cost_amount');

            const editFd = new FormData(this);
            editFd.set('nopol',      $('#edit_nopol').val()      || '');
            editFd.set('driver',     $('#edit_driver').val()     || '');
            editFd.set('cost_type',  $('#edit_cost_type').val()  || '');

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'PUT',
                    },
                    body: editFd,
                })
                .then(r => r.json())
                .then(json => {
                    if (json.success) {
                        closeModal('editModal');
                        Swal.fire('Success', json.message, 'success');
                        fetchTable();
                    } else {
                        Swal.fire('Error', json.message, 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Request failed.', 'error'))
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-floppy-disk text-xs mr-2"></i>Save Changes';
                });
        });

        // ---- CLOSE BUTTONS ----

        function isFormDirty(formId) {
            const form = document.getElementById(formId);
            if (!form) return false;
            for (const el of form.elements) {
                if (['INPUT', 'TEXTAREA'].includes(el.tagName) && !['hidden', 'submit', 'button'].includes(el.type) && el.value.trim() !== '') return true;
                if (el.tagName === 'SELECT' && el.value !== '') return true;
            }
            return false;
        }

        async function confirmCloseForm(modalId, formId) {
            if (isFormDirty(formId)) {
                const result = await Swal.fire({
                    title: 'Discard changes?',
                    text: 'You have unsaved data. Are you sure you want to close?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, discard',
                    cancelButtonText: 'Keep editing',
                });
                if (!result.isConfirmed) return;
            }
            closeModal(modalId);
        }

        ['btnCloseCreateModal', 'btnCancelCreate'].forEach(id =>
            document.getElementById(id)?.addEventListener('click', () => confirmCloseForm('createModal', 'createForm')));

        ['btnCloseShowModal', 'btnCloseShowModalFooter'].forEach(id =>
            document.getElementById(id)?.addEventListener('click', () => closeModal('showModal')));

        ['btnCloseEditModal', 'btnCancelEdit'].forEach(id =>
            document.getElementById(id)?.addEventListener('click', () => confirmCloseForm('editModal', 'editForm')));

        // ---- INIT ----

        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });
            fetchTable();
        });
    </script>

</x-app-layout>
