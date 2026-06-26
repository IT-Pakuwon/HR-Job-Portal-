<x-app-layout>
    @include('pages.carexpense.partial.style')

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- Cost Type Filter Cards --}}
        <div class="grid grid-cols-4 gap-4">

            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter active group block h-full flex-1 w-full" data-filter="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-slate-400 bg-slate-200/20 p-3 text-slate-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-slate-100 hover:shadow-md active:scale-95 dark:border-slate-500 dark:text-slate-300 dark:hover:bg-slate-700/30">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $countAll }}</p>
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
                            class="status-card flex h-full items-center gap-3 rounded-lg border p-3 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md active:scale-95 {{ $cardColors[$loop->index % count($cardColors)] }}">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🚗</div>
                            <div class="flex min-w-0 flex-grow flex-col leading-tight">
                                <p class="break-words text-sm font-medium">{{ $type->category_name }}</p>
                            </div>
                            <p class="shrink-0 text-base font-bold">{{ $countByType[$type->id] ?? 0 }}</p>
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
                    <a href="{{ route('carexpense.download-template') }}"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-emerald-600 px-5 text-sm font-medium text-emerald-600 transition hover:bg-emerald-50 dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-500/10">
                        <i class="fa-solid fa-file-arrow-down mr-2 text-xs"></i>
                        Template
                    </a>
                    <button type="button" id="btnOpenImport"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-blue-600 px-5 text-sm font-medium text-blue-600 transition hover:bg-blue-50 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-500/10">
                        <i class="fa-solid fa-file-import mr-2 text-xs"></i>
                        Import
                    </button>
                    <button type="button" id="btnOpenCreate"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        <i class="fa-solid fa-plus mr-2 text-xs"></i>
                        Create
                    </button>
                </div>

            </div>

            {{-- Filter Row --}}
            <div class="grid w-full grid-cols-4 items-end gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Nopol</label>
                    <select id="filterNopol"
                        class="select2-filter w-full rounded-lg border border-slate-200 bg-white text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-100">
                        <option value="">All Nopol</option>
                        @foreach ($kendaraan as $k)
                            <option value="{{ $k->no_polisi }}">{{ $k->no_polisi }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">From</label>
                    <input type="date" id="filterDateFrom"
                        class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-100">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">To</label>
                    <input type="date" id="filterDateTo"
                        class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-100">
                </div>

                <div class="flex items-end">
                    <button type="button" id="btnResetFilters"
                        class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-500 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-400 dark:hover:bg-white/[0.08]">
                        <i class="fa-solid fa-rotate-left mr-1 text-xs"></i> Reset
                    </button>
                </div>

            </div>

            <div class="relative overflow-x-auto p-4">
                <table id="carExpenseTable" class="w-full text-left text-sm">
                    <thead
                        class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                        <tr>
                            <th></th>
                            <th class="px-4 py-3 font-medium">Ref No</th>
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium">Nopol</th>
                            <th class="px-4 py-3 font-medium">Driver</th>
                            <th class="px-4 py-3 font-medium">Cost Type</th>
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium text-right">Qty</th>
                            <th class="px-4 py-3 font-medium text-right">Amount</th>
                            <th class="px-4 py-3 font-medium text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
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

        {{-- =========================================================
             IMPORT MODAL
        ========================================================= --}}
        <div id="importModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">

            <div class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70"></div>

            <div class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-3xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

                <div class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Import Car Expense</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Upload an Excel file (.xlsx) using the provided template, then review before importing.</p>
                    </div>
                    <button id="btnCloseImportModal" type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="space-y-5 p-6">

                    {{-- Step 1: Excel file --}}
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Step 1 — Excel File</p>

                        <div id="importDropZone"
                            class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center transition hover:border-blue-400 hover:bg-blue-50/30 dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-blue-500/50 dark:hover:bg-blue-500/5">
                            <i class="fa-solid fa-file-excel mb-3 text-3xl text-emerald-500"></i>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Click to choose or drag & drop</p>
                            <p class="mt-1 text-xs text-slate-400">Accepted: .xlsx, .xls — Max 5 MB</p>
                            <input type="file" id="importFileInput" accept=".xlsx,.xls" class="hidden">
                        </div>

                        <div id="importFileInfo" class="mt-2 hidden rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 dark:border-white/10 dark:bg-white/[0.03]">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-file-excel text-emerald-500"></i>
                                <span id="importFileName" class="flex-1 truncate text-sm text-slate-700 dark:text-slate-200"></span>
                                <button type="button" id="btnClearImportFile" class="text-slate-400 transition hover:text-red-500">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Parsing spinner --}}
                        <div id="importParsingBox" class="mt-3 hidden flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <i class="fa-solid fa-spinner fa-spin text-blue-500"></i> Parsing file...
                        </div>

                        {{-- Error list --}}
                        <div id="importErrorBox" class="mt-3 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-500/20 dark:bg-red-500/10">
                            <p class="mb-2 text-sm font-semibold text-red-700 dark:text-red-400">Please fix the following errors:</p>
                            <ul id="importErrorList" class="space-y-1 text-sm text-red-600 dark:text-red-300"></ul>
                        </div>
                    </div>

                    {{-- Preview table --}}
                    <div id="importPreviewBox" class="hidden">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Preview — <span id="importPreviewCount" class="text-emerald-600 dark:text-emerald-400">0</span> row(s) ready to import
                        </p>
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-white/10">
                            <table class="w-full text-left text-xs">
                                <thead class="border-b border-slate-100 bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-slate-400">
                                    <tr>
                                        <th class="px-3 py-2">#</th>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">Nopol</th>
                                        <th class="px-3 py-2">Driver</th>
                                        <th class="px-3 py-2">Cost Type</th>
                                        <th class="px-3 py-2">Description</th>
                                        <th class="px-3 py-2 text-right">Qty</th>
                                        <th class="px-3 py-2 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="importPreviewBody" class="divide-y divide-slate-100 dark:divide-white/[0.04]"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Step 2: Supporting documents --}}
                    <div id="importAttachSection" class="hidden">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Step 2 — Supporting Documents <span class="normal-case font-normal">(optional)</span></p>

                        <div id="importAttachDropZone"
                            class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-6 text-center transition hover:border-blue-400 hover:bg-blue-50/30 dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-blue-500/50 dark:hover:bg-blue-500/5">
                            <i class="fa-solid fa-paperclip mb-2 text-2xl text-slate-400"></i>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Click to attach or drag & drop files</p>
                            <p class="mt-1 text-xs text-slate-400">PDF, PNG, JPG, JPEG — Max 5 MB each</p>
                            <input type="file" id="importAttachInput" accept=".pdf,.png,.jpg,.jpeg" multiple class="hidden">
                        </div>

                        <ul id="importAttachList" class="mt-2 space-y-1"></ul>
                    </div>

                </div>

                <div class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-6 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" id="btnCancelImport"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]">
                            Cancel
                        </button>
                        <button type="button" id="btnSubmitImport" disabled
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50">
                            <i class="fa-solid fa-file-import text-xs"></i>
                            Import
                        </button>
                    </div>
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
            importPreview:    @json(route('carexpense.import.preview')),
            import:           @json(route('carexpense.import')),
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
            box.innerHTML = attachments.map(a => {
                const url      = a.url ?? a.signed_url ?? null;
                const label    = a.name ?? a.display_name ?? a.attachment_name ?? a.filename ?? 'file';
                const filesize = a.size ?? a.filesize ?? null;
                const linkEl   = url
                    ? `<a href="${url}" target="_blank" rel="noopener noreferrer" class="block truncate text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">${label}</a>`
                    : `<span class="block truncate text-sm font-medium text-slate-400 dark:text-slate-500">${label}</span>`;
                return `
                <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 dark:border-white/10 dark:bg-white/[0.03]">
                    <i class="fa-solid ${fileIcon(a.extention)} w-5 text-center"></i>
                    <div class="min-w-0 flex-1">
                        ${linkEl}
                        <p class="text-xs text-slate-400">${formatBytes(filesize)}</p>
                    </div>
                    ${canDelete ? `
                    <button type="button" onclick="deleteAttachment(${a.id}, '${containerId}')"
                        class="ml-auto shrink-0 text-slate-400 transition hover:text-red-500">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>` : ''}
                </div>`;
            }).join('');
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
                            window.carExpenseTable.ajax.reload(null, false);
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

                if (files.length > 0) {
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs mr-2"></i>Uploading...';
                    await uploadFilesToExpense(json.data.eid, files);
                }

                closeModal('createModal');
                Swal.fire('Success', json.message, 'success');
                window.carExpenseTable.ajax.reload(null, false);
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
                        window.carExpenseTable.ajax.reload(null, false);
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

        // ---- DATATABLE INIT ----

        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });

            $('#filterNopol').select2({
                width: '100%',
                allowClear: true,
                placeholder: 'All Nopol',
            });

            let currentFilter  = '';
            let currentNopol   = '';
            let currentDateFrom = '';
            let currentDateTo   = '';

            window.carExpenseTable = $('#carExpenseTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,

                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],

                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Car_Expense',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: { columns: ':visible', modifier: { page: 'current' } }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Car_Expense',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: { columns: ':visible', modifier: { page: 'current' } }
                    }
                ],

                responsive: {
                    details: { type: 'column', target: 0 }
                },

                columnDefs: [
                    { targets: 0, width: '28px', className: 'dtr-control', orderable: false },
                    { targets: 9, orderable: false }
                ],

                ajax: {
                    url: CarExpenseRoutes.json,
                    type: 'GET',
                    data: function(d) {
                        d.filter    = currentFilter;
                        d.nopol     = currentNopol;
                        d.date_from = currentDateFrom;
                        d.date_to   = currentDateTo;
                    }
                },

                order: [[1, 'desc']],

                columns: [
                    { data: null, defaultContent: '' },
                    { data: 'refnbr', defaultContent: '-' },
                    { data: 'ref_date', defaultContent: '-' },
                    { data: 'nopol', defaultContent: '-' },
                    { data: 'driver', defaultContent: '-' },
                    {
                        data: 'cost_type_name',
                        defaultContent: '-',
                        render: function(data) {
                            return `<span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">${data ?? '-'}</span>`;
                        }
                    },
                    { data: 'cost_descr', defaultContent: '-' },
                    { data: 'cost_qty', defaultContent: '0', className: 'text-right' },
                    {
                        data: 'cost_amount',
                        defaultContent: '0',
                        className: 'text-right font-medium',
                        render: function(data) {
                            return 'Rp ' + Number(data).toLocaleString('id-ID');
                        }
                    },
                    {
                        data: 'eid',
                        className: 'text-center',
                        render: function(data) {
                            return `<button type="button" onclick="openDetail('${data}')"
                                class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-600 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300 dark:hover:bg-white/[0.08]">
                                <i class="fa-solid fa-eye mr-1 text-[10px]"></i> View
                            </button>`;
                        }
                    }
                ],

                searchDelay: 400,
            });

            // Cost type filter cards
            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                currentFilter = $(this).data('filter') ?? '';
                window.carExpenseTable.ajax.reload(null, true);
            });

            document.querySelectorAll('.status-filter').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Nopol & date range filters
            $('#filterNopol').on('change', function() {
                currentNopol = $(this).val() ?? '';
                window.carExpenseTable.ajax.reload(null, true);
            });

            document.getElementById('filterDateFrom').addEventListener('change', function() {
                currentDateFrom = this.value;
                window.carExpenseTable.ajax.reload(null, true);
            });

            document.getElementById('filterDateTo').addEventListener('change', function() {
                currentDateTo = this.value;
                window.carExpenseTable.ajax.reload(null, true);
            });

            document.getElementById('btnResetFilters').addEventListener('click', function() {
                $('#filterNopol').val('').trigger('change');
                document.getElementById('filterDateFrom').value = '';
                document.getElementById('filterDateTo').value = '';
                currentNopol   = '';
                currentDateFrom = '';
                currentDateTo   = '';
                window.carExpenseTable.ajax.reload(null, true);
            });
        });

        // ---- IMPORT MODAL ----

        const importFileInput     = document.getElementById('importFileInput');
        const importDropZone      = document.getElementById('importDropZone');
        const importFileInfo      = document.getElementById('importFileInfo');
        const importFileName      = document.getElementById('importFileName');
        const importParsingBox    = document.getElementById('importParsingBox');
        const importErrorBox      = document.getElementById('importErrorBox');
        const importErrorList     = document.getElementById('importErrorList');
        const importPreviewBox    = document.getElementById('importPreviewBox');
        const importPreviewCount  = document.getElementById('importPreviewCount');
        const importPreviewBody   = document.getElementById('importPreviewBody');
        const importAttachSection = document.getElementById('importAttachSection');
        const importAttachInput   = document.getElementById('importAttachInput');
        const importAttachDropZone= document.getElementById('importAttachDropZone');
        const importAttachList    = document.getElementById('importAttachList');
        const btnSubmitImport     = document.getElementById('btnSubmitImport');

        let importAttachFiles = [];

        document.getElementById('btnOpenImport').addEventListener('click', () => openModal('importModal'));
        document.getElementById('btnCloseImportModal').addEventListener('click', closeImportModal);
        document.getElementById('btnCancelImport').addEventListener('click', closeImportModal);

        function closeImportModal() {
            closeModal('importModal');
            resetImportForm();
        }

        function resetImportForm() {
            importFileInput.value = '';
            importFileInfo.classList.add('hidden');
            importFileName.textContent = '';
            importParsingBox.classList.add('hidden');
            importErrorBox.classList.add('hidden');
            importErrorList.innerHTML = '';
            importPreviewBox.classList.add('hidden');
            importPreviewBody.innerHTML = '';
            importAttachSection.classList.add('hidden');
            importAttachFiles = [];
            importAttachList.innerHTML = '';
            importAttachInput.value = '';
            btnSubmitImport.disabled = true;
            btnSubmitImport.innerHTML = '<i class="fa-solid fa-file-import text-xs"></i> Import';
        }

        // Excel file drag & drop
        importDropZone.addEventListener('click', () => importFileInput.click());
        importDropZone.addEventListener('dragover', (e) => { e.preventDefault(); importDropZone.classList.add('border-blue-400'); });
        importDropZone.addEventListener('dragleave', () => importDropZone.classList.remove('border-blue-400'));
        importDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            importDropZone.classList.remove('border-blue-400');
            const f = e.dataTransfer.files[0];
            if (f) { importFileInput.files = e.dataTransfer.files; triggerPreview(f); }
        });
        importFileInput.addEventListener('change', () => {
            const f = importFileInput.files[0];
            if (f) triggerPreview(f);
        });
        document.getElementById('btnClearImportFile').addEventListener('click', () => {
            importFileInput.value = '';
            resetImportForm();
        });

        async function triggerPreview(file) {
            importFileName.textContent = file.name;
            importFileInfo.classList.remove('hidden');
            importParsingBox.classList.remove('hidden');
            importErrorBox.classList.add('hidden');
            importPreviewBox.classList.add('hidden');
            importAttachSection.classList.add('hidden');
            btnSubmitImport.disabled = true;

            const fd = new FormData();
            fd.append('file', file);
            fd.append('_token', CSRF);

            try {
                const res  = await fetch(CarExpenseRoutes.importPreview, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                const json = await res.json();
                importParsingBox.classList.add('hidden');

                if (json.success) {
                    importPreviewCount.textContent = json.count;
                    importPreviewBody.innerHTML = json.rows.map(r => `
                        <tr class="text-xs text-slate-700 dark:text-slate-300">
                            <td class="px-3 py-2 text-slate-400">${r.row}</td>
                            <td class="px-3 py-2">${r.date}</td>
                            <td class="px-3 py-2">${r.nopol}</td>
                            <td class="px-3 py-2">${r.driver}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                                    ${r.cost_type}
                                </span>
                            </td>
                            <td class="px-3 py-2">${r.description}</td>
                            <td class="px-3 py-2 text-right">${r.qty}</td>
                            <td class="px-3 py-2 text-right">${Number(r.amount).toLocaleString('id-ID')}</td>
                        </tr>
                    `).join('');
                    importPreviewBox.classList.remove('hidden');
                    importAttachSection.classList.remove('hidden');
                    btnSubmitImport.disabled = false;
                } else {
                    if (json.errors && json.errors.length) {
                        importErrorList.innerHTML = json.errors.map(e =>
                            `<li><strong>Row ${e.row}:</strong> ${e.errors.join('; ')}</li>`
                        ).join('');
                        importErrorBox.classList.remove('hidden');
                    } else {
                        importErrorList.innerHTML = `<li>${json.message}</li>`;
                        importErrorBox.classList.remove('hidden');
                    }
                }
            } catch (err) {
                importParsingBox.classList.add('hidden');
                importErrorList.innerHTML = '<li>Failed to connect to server.</li>';
                importErrorBox.classList.remove('hidden');
            }
        }

        // Attachment file handling
        importAttachDropZone.addEventListener('click', () => importAttachInput.click());
        importAttachDropZone.addEventListener('dragover', (e) => { e.preventDefault(); importAttachDropZone.classList.add('border-blue-400'); });
        importAttachDropZone.addEventListener('dragleave', () => importAttachDropZone.classList.remove('border-blue-400'));
        importAttachDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            importAttachDropZone.classList.remove('border-blue-400');
            addAttachFiles(Array.from(e.dataTransfer.files));
        });
        importAttachInput.addEventListener('change', () => {
            addAttachFiles(Array.from(importAttachInput.files));
            importAttachInput.value = '';
        });

        const ALLOWED_ATTACH = ['application/pdf', 'image/png', 'image/jpeg'];
        const MAX_ATTACH_SIZE = 5 * 1024 * 1024;

        function addAttachFiles(files) {
            files.forEach(file => {
                if (!ALLOWED_ATTACH.includes(file.type)) {
                    Swal.fire({ icon: 'warning', title: 'Invalid file type', text: `${file.name} is not PDF, PNG, JPG, or JPEG.`, timer: 2500, showConfirmButton: false });
                    return;
                }
                if (file.size > MAX_ATTACH_SIZE) {
                    Swal.fire({ icon: 'warning', title: 'File too large', text: `${file.name} exceeds 5 MB.`, timer: 2500, showConfirmButton: false });
                    return;
                }
                importAttachFiles.push(file);
                renderAttachFile(file, importAttachFiles.length - 1);
            });
        }

        function renderAttachFile(file, idx) {
            const ext = file.name.split('.').pop().toLowerCase();
            const icon = ext === 'pdf' ? 'fa-file-pdf text-red-500' : 'fa-image text-emerald-500';
            const li = document.createElement('li');
            li.id = `importAttach_${idx}`;
            li.className = 'flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/[0.03]';
            li.innerHTML = `
                <i class="fa-solid ${icon} w-4 shrink-0 text-center"></i>
                <span class="flex-1 truncate text-slate-700 dark:text-slate-200">${file.name}</span>
                <span class="text-xs text-slate-400">${(file.size/1024).toFixed(0)} KB</span>
                <button type="button" onclick="removeAttachFile(${idx})" class="ml-1 text-slate-400 hover:text-red-500 transition">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            `;
            importAttachList.appendChild(li);
        }

        window.removeAttachFile = function(idx) {
            importAttachFiles[idx] = null;
            const li = document.getElementById(`importAttach_${idx}`);
            if (li) li.remove();
        };

        // Final submit
        btnSubmitImport.addEventListener('click', async () => {
            const excelFile = importFileInput.files[0];
            if (!excelFile) return;

            btnSubmitImport.disabled = true;
            btnSubmitImport.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs mr-2"></i> Importing...';

            const fd = new FormData();
            fd.append('file', excelFile);
            fd.append('_token', CSRF);
            importAttachFiles.filter(Boolean).forEach(f => fd.append('attachments[]', f));

            try {
                const res  = await fetch(CarExpenseRoutes.import, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                const json = await res.json();

                if (json.success) {
                    closeImportModal();
                    window.carExpenseTable.ajax.reload(null, true);
                    Swal.fire({ icon: 'success', title: 'Imported!', text: json.message, timer: 2500, showConfirmButton: false });
                } else {
                    if (json.errors && json.errors.length) {
                        importErrorList.innerHTML = json.errors.map(e =>
                            `<li><strong>Row ${e.row}:</strong> ${e.errors.join('; ')}</li>`
                        ).join('');
                        importErrorBox.classList.remove('hidden');
                    } else {
                        Swal.fire({ icon: 'error', title: 'Import Failed', text: json.message });
                    }
                    btnSubmitImport.disabled = false;
                    btnSubmitImport.innerHTML = '<i class="fa-solid fa-file-import text-xs"></i> Import';
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to connect to the server.' });
                btnSubmitImport.disabled = false;
                btnSubmitImport.innerHTML = '<i class="fa-solid fa-file-import text-xs"></i> Import';
            }
        });
    </script>

</x-app-layout>
