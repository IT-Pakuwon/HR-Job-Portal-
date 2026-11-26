<x-app-layout>
    <x-spp-list-styles />
    <x-spp-form-styles />
    <style>
        #masterKontrakBq .master-bq-category-filter { flex: 1 1 150px; min-width: 130px; max-width: 240px; }
        #masterKontrakBq .master-bq-type-filter { flex: 1 1 110px; min-width: 100px; max-width: 160px; }
        #masterKontrakBq .master-bq-filter-select { width: 100%; height: 42px; }
        #masterCategoryTable tbody tr { cursor: pointer; }
        #masterCategoryTable tbody tr.category-selected > td { background-color: #e0e7ff !important; }
        .dark #masterCategoryTable tbody tr.category-selected > td { background-color: #373b68 !important; }
        #masterKontrakBq .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
        }
        #masterKontrakBq .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            padding-left: 12px;
        }
        #masterKontrakBq .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        .dark #masterKontrakBq .select2-container--default .select2-selection--single {
            background-color: #111827;
            border-color: #4b5563;
        }
        .dark #masterKontrakBq .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f3f4f6;
        }
    </style>
    <div id="masterKontrakBq" class="mx-auto w-full space-y-4 p-2"
        data-json="{{ route('kontrak.master-bq.json') }}" data-store="{{ route('kontrak.master-bq.store') }}"
        data-categories="{{ route('kontrak.master-bq.categories') }}">
        <div class="flex flex-wrap items-center justify-between gap-3">
        <nav class="flex flex-wrap gap-2">
            <a href="{{ route('kontrak') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-200">Kontrak</a>
            <span aria-current="page" class="rounded-lg border border-indigo-600 bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Master Kontrak</span>
        </nav>
        <section id="masterCategoryPanel" class="border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 p-4 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Kategori Kontrak BQ</h2>
                <button id="masterCategoryAdd" type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"><i class="fas fa-plus mr-1" aria-hidden="true"></i> Add Kategori</button>
            </header>
            <div id="masterCategoryNotice" role="status" class="hidden px-4 py-3 text-sm text-emerald-700"></div>
            <div class="overflow-x-auto p-4">
                <table id="masterCategoryTable" class="w-full text-sm text-gray-700 dark:text-gray-200">
                    <thead><tr>
                        @foreach (['Edit', 'Category', 'Description', 'Status'] as $heading)
                            <th class="px-3 py-3 text-left">{{ $heading }}</th>
                        @endforeach
                    </tr></thead>
                </table>
            </div>
        </section>
        <section id="masterBqPanel" class="border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <header class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 p-4 dark:border-gray-700">
                <div class="flex min-w-0 flex-1 flex-wrap items-end gap-3">
                    <h1 class="self-center text-base font-semibold text-gray-800 dark:text-gray-100">Master Kontrak BQ</h1>
                    <div class="master-bq-category-filter">
                        <label for="masterBqCategoryFilter" class="mb-1 block text-xs text-gray-500">Category</label>
                        <select id="masterBqCategoryFilter" class="master-bq-filter-select block rounded-lg border-gray-300 text-sm dark:bg-gray-900 dark:text-gray-100">
                            <option value="">All Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->kontrakcategory }}">{{ $category->kontrakcategory }} - {{ $category->kontrakcategory_descr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="master-bq-type-filter sppb-f relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" /></svg></span>
                        <select id="masterBqTypeFilter" aria-label="Type">
                            <option value="">All Type</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="masterBqAdd" type="button" class="shrink-0 whitespace-nowrap rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"><i class="fas fa-plus mr-1" aria-hidden="true"></i> Add Kontrak Bq</button>
                </div>
                
            </header>
            <div id="masterBqNotice" role="status" class="hidden px-4 py-3 text-sm"></div>
            <div class="overflow-x-auto p-4">
                <table id="masterBqTable" class="w-full text-sm text-gray-700 dark:text-gray-200">
                    <thead><tr>
                        @foreach (['Edit', 'BQ ID', 'Category', 'Type', 'Line No', 'Description', 'UoM', 'Duration Qty', 'Status'] as $heading)
                            <th class="px-3 py-3 text-left">{{ $heading }}</th>
                        @endforeach
                    </tr></thead>
                </table>
            </div>
        </section>
        <dialog id="masterBqDialog" aria-labelledby="masterBqModalTitle" class="w-full max-w-2xl rounded-lg border border-gray-200 bg-white p-0 text-gray-800 backdrop:bg-black/50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" style="position:fixed;inset:0;margin:auto;max-width:min(42rem,calc(100vw - 2rem));max-height:90vh;overflow-y:auto;">
            <form id="masterBqForm">
                <header class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span><div><h2 id="masterBqModalTitle" class="text-base font-bold text-gray-800 dark:text-white">Add Kontrak Bq</h2><p class="text-xs text-gray-500 dark:text-gray-400">Define a bill-of-quantity line for kontrak</p></div></div>
                    <button type="button" data-bq-close title="Close" aria-label="Close" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700"><i class="fas fa-times" aria-hidden="true"></i></button>
                </header>
                <div class="space-y-5 p-6">
                    <div id="masterBqErrors" role="alert" class="hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
                    <div class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">BQ ID <input name="kontrak_bq_id" required maxlength="255" autofocus class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-normal normal-case tracking-normal text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 read-only:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></label>
                        <label class="sf-field flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Category
                            <select name="kontrakcategory" required class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-normal normal-case tracking-normal text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 read-only:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->kontrakcategory }}">{{ $category->kontrakcategory }} - {{ $category->kontrakcategory_descr }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Type <input name="kontrak_bq_type" list="masterBqTypes" required maxlength="255" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-normal normal-case tracking-normal text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 read-only:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></label>
                        <label class="flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Line No <input name="kontrak_bq_line_no" type="number" min="1" step="1" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-normal normal-case tracking-normal text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 read-only:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></label>
                        <label class="flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">UoM <input name="kontrak_bq_uom" required maxlength="50" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-normal normal-case tracking-normal text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 read-only:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></label>
                        <label class="flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Duration Qty <input name="kontrak_duration_qty" type="number" min="0" step="any" required class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-normal normal-case tracking-normal text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 read-only:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></label>
                    </div>
                    <label class="flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description <textarea name="kontrak_bq_descr" rows="4" required maxlength="10000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-normal normal-case tracking-normal text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></textarea></label>
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200"><input name="active" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-300">Active</label>
                </div>
                <footer class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                    <button type="button" data-bq-close class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Cancel</button>
                    <button id="masterBqSave" type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300"><i class="fas fa-save mr-1" aria-hidden="true"></i><span>Save</span></button>
                </footer>
            </form>
        </dialog>
        <dialog id="masterCategoryDialog" aria-labelledby="masterCategoryTitle" class="w-full rounded-2xl border border-gray-100 bg-white p-0 text-gray-800 shadow-2xl backdrop:bg-slate-900/40 backdrop:backdrop-blur-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" style="position:fixed;inset:0;margin:auto;max-width:min(32rem,calc(100vw - 2rem));max-height:90vh;overflow-y:auto;">
            <form id="masterCategoryForm">
                <header class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span><div><h2 id="masterCategoryTitle" class="text-base font-bold text-gray-800 dark:text-white">Add Kategori</h2><p class="text-xs text-gray-500 dark:text-gray-400">Define a kontrak category</p></div></div>
                    <button type="button" data-category-close title="Close" aria-label="Close" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700"><i class="fas fa-times" aria-hidden="true"></i></button>
                </header>
                <div class="space-y-5 p-6">
                    <div id="masterCategoryErrors" role="alert" class="hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
                    <label class="flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Category <input name="kontrakcategory" required maxlength="255" autofocus class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-normal normal-case tracking-normal text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 read-only:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></label>
                    <label class="flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description <textarea name="kontrakcategory_descr" required rows="4" maxlength="10000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-normal normal-case tracking-normal text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></textarea></label>
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200"><input name="active" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-300">Active</label>
                </div>
                <footer class="flex justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                    <button type="button" data-category-close class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Cancel</button>
                    <button id="masterCategorySave" type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300"><i class="fas fa-save mr-1" aria-hidden="true"></i><span>Save</span></button>
                </footer>
            </form>
        </dialog>
        <datalist id="masterBqTypes">
            @foreach ($types as $type)<option value="{{ $type }}"></option>@endforeach
        </datalist>
    </div>
    <script src="{{ asset('assets/js/kontrak/master-bq.js') }}?v={{ filemtime(public_path('assets/js/kontrak/master-bq.js')) }}"></script>
</x-app-layout>
