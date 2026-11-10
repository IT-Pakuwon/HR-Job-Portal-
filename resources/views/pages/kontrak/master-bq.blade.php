<x-app-layout>
    <div id="masterKontrakBq" class="mx-auto w-full space-y-4 p-2"
        data-json="{{ route('kontrak.master-bq.json') }}" data-store="{{ route('kontrak.master-bq.store') }}"
        data-categories="{{ route('kontrak.master-bq.categories') }}">
        <nav class="flex flex-wrap gap-2">
            <a href="{{ route('kontrak') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-200">Kontrak</a>
            <span aria-current="page" class="rounded-lg border border-indigo-600 bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Master Kontrak</span>
        </nav>
        <div role="tablist" aria-label="Master Kontrak" class="flex flex-wrap gap-2">
            <button type="button" role="tab" id="masterBqTab" data-master-tab="bq" aria-controls="masterBqPanel" aria-selected="true" class="rounded-lg border border-indigo-600 bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Master Kontrak BQ</button>
            <button type="button" role="tab" id="masterCategoryTab" data-master-tab="category" aria-controls="masterCategoryPanel" aria-selected="false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Kategori Kontrak BQ</button>
        </div>
        <section id="masterBqPanel" role="tabpanel" aria-labelledby="masterBqTab" class="border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <header class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 p-4 dark:border-gray-700">
                <div class="flex flex-wrap items-end gap-3">
                    <h1 class="self-center text-base font-semibold text-gray-800 dark:text-gray-100">Master Kontrak BQ</h1>
                    <label class="text-xs text-gray-500">Category
                        <select id="masterBqCategoryFilter" class="mt-1 block max-w-full rounded-lg border-gray-300 text-sm dark:bg-gray-900 dark:text-gray-100">
                            <option value="">All Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->kontrakcategory }}">{{ $category->kontrakcategory }} - {{ $category->kontrakcategory_descr }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-xs text-gray-500">Type
                        <select id="masterBqTypeFilter" class="mt-1 block rounded-lg border-gray-300 text-sm dark:bg-gray-900 dark:text-gray-100">
                            <option value="">All Type</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <button id="masterBqAdd" type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"><i class="fas fa-plus mr-1" aria-hidden="true"></i> Add Kontrak Bq</button>
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
        <section id="masterCategoryPanel" role="tabpanel" aria-labelledby="masterCategoryTab" class="hidden border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
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
        <dialog id="masterBqDialog" aria-labelledby="masterBqModalTitle" class="w-full max-w-2xl rounded-lg border border-gray-200 bg-white p-0 text-gray-800 backdrop:bg-black/50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" style="position:fixed;inset:0;margin:auto;max-width:min(42rem,calc(100vw - 2rem));max-height:90vh;overflow-y:auto;">
            <form id="masterBqForm">
                <header class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <h2 id="masterBqModalTitle" class="text-base font-semibold">Add Kontrak Bq</h2>
                    <button type="button" data-bq-close title="Close" aria-label="Close" class="h-8 w-8 rounded hover:bg-gray-100 dark:hover:bg-gray-700"><i class="fas fa-times" aria-hidden="true"></i></button>
                </header>
                <div class="space-y-4 p-4">
                    <div id="masterBqErrors" role="alert" class="hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="text-sm">BQ ID <input name="kontrak_bq_id" required maxlength="255" autofocus class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-900"></label>
                        <label class="text-sm">Category
                            <select name="kontrakcategory" required class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-900">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->kontrakcategory }}">{{ $category->kontrakcategory }} - {{ $category->kontrakcategory_descr }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-sm">Type <input name="kontrak_bq_type" list="masterBqTypes" required maxlength="255" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-900"></label>
                        <label class="text-sm">Line No <input name="kontrak_bq_line_no" type="number" min="1" step="1" required class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-900"></label>
                        <label class="text-sm">UoM <input name="kontrak_bq_uom" required maxlength="50" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-900"></label>
                        <label class="text-sm">Duration Qty <input name="kontrak_duration_qty" type="number" min="0" step="any" required class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-900"></label>
                    </div>
                    <label class="block text-sm">Description <textarea name="kontrak_bq_descr" rows="4" required maxlength="10000" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-900"></textarea></label>
                    <label class="inline-flex items-center gap-2 text-sm"><input name="active" type="checkbox" checked class="rounded border-gray-300">Active</label>
                </div>
                <footer class="flex justify-end gap-2 border-t border-gray-200 p-4 dark:border-gray-700">
                    <button type="button" data-bq-close class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Cancel</button>
                    <button id="masterBqSave" type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"><i class="fas fa-save mr-1" aria-hidden="true"></i><span>Save</span></button>
                </footer>
            </form>
        </dialog>
        <dialog id="masterCategoryDialog" aria-labelledby="masterCategoryTitle" class="w-full rounded-lg border border-gray-200 bg-white p-0 text-gray-800 backdrop:bg-black/50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" style="position:fixed;inset:0;margin:auto;max-width:min(32rem,calc(100vw - 2rem));max-height:90vh;overflow-y:auto;">
            <form id="masterCategoryForm">
                <header class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <h2 id="masterCategoryTitle" class="text-base font-semibold">Add Kategori</h2>
                    <button type="button" data-category-close title="Close" aria-label="Close" class="h-8 w-8 rounded hover:bg-gray-100 dark:hover:bg-gray-700"><i class="fas fa-times" aria-hidden="true"></i></button>
                </header>
                <div class="space-y-4 p-4">
                    <div id="masterCategoryErrors" role="alert" class="hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
                    <label class="block text-sm">Category <input name="kontrakcategory" required maxlength="255" autofocus class="mt-1 block w-full rounded-lg border-gray-300 read-only:bg-gray-100 dark:bg-gray-900"></label>
                    <label class="block text-sm">Description <textarea name="kontrakcategory_descr" required rows="4" maxlength="10000" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-900"></textarea></label>
                    <label class="inline-flex items-center gap-2 text-sm"><input name="active" type="checkbox" checked class="rounded border-gray-300">Active</label>
                </div>
                <footer class="flex justify-end gap-2 border-t border-gray-200 p-4 dark:border-gray-700">
                    <button type="button" data-category-close class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Cancel</button>
                    <button id="masterCategorySave" type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"><i class="fas fa-save mr-1" aria-hidden="true"></i><span>Save</span></button>
                </footer>
            </form>
        </dialog>
        <datalist id="masterBqTypes">
            @foreach ($types as $type)<option value="{{ $type }}"></option>@endforeach
        </datalist>
    </div>
    <script src="{{ asset('assets/js/kontrak/master-bq.js') }}?v={{ filemtime(public_path('assets/js/kontrak/master-bq.js')) }}"></script>
</x-app-layout>
