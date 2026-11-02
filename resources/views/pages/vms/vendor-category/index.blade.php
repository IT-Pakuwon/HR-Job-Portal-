<x-app-layout>
    <link rel="stylesheet" href="{{ asset('assets/css/vms/document.css').'?v='.filemtime(public_path('assets/css/vms/document.css')) }}">
    <div class="vms-document-page" id="vms-category-page" data-base="{{ route('vms-setup.vendor-category') }}" data-parent="{{ $parent?->id }}">
        <nav class="vms-document-tabs" aria-label="Breadcrumb">
            <a href="{{ route('vms-setup.vendor-category') }}" style="padding:16px">Vendor Category</a>
            @if($parent)<span style="padding:16px">/ {{ $parent->parent_category_name }} / Bidang Usaha</span>@endif
        </nav>
        @foreach(($parent ? ['business' => 'Bidang Usaha - '.$parent->parent_category_name] : ['category' => 'Kategori Vendor']) as $kind => $title)
        <section class="vms-document-panel" data-category-panel="{{ $kind }}">
            <header><h2><i class="fas fa-tags" aria-hidden="true"></i> {{ $title }}</h2><button type="button" data-add-category="{{ $kind }}">+ {{ $kind === 'category' ? 'Add Category' : 'Add Bidang Usaha' }}</button></header>
            <div class="vms-document-table-wrap"><table id="category-table-{{ $kind }}" class="w-full" aria-label="{{ $title }}"><thead><tr>
                <th>Actions</th><th>{{ $kind === 'category' ? 'Category ID' : 'Bidang Usaha ID' }}</th><th>{{ $kind === 'category' ? 'Category Name' : 'Bidang Usaha' }}</th><th>{{ $kind === 'category' ? 'Class ID' : 'Kategori Induk' }}</th><th>Status</th><th>Detail</th>
            </tr></thead><tbody></tbody></table></div>
        </section>
        @endforeach
    </div>
    <dialog id="vms-document-modal" aria-labelledby="vms-category-modal-title">
        <form id="vms-category-form">@csrf
            <header><h2 id="vms-category-modal-title">Add Category</h2><button type="button" data-category-close aria-label="Tutup">&times;</button></header>
            <div class="vms-document-modal-body">
                <p class="vms-document-feedback" data-category-error role="alert" hidden></p>
                <div class="vms-document-form-grid">
                    <label>ID<input data-category-id disabled><small>ID dibuat otomatis.</small></label>
                    <label>Status <span>*</span><select name="status"><option value="1">Active</option><option value="0">Inactive</option></select></label>
                </div>
                <fieldset data-category-fields="category"><div class="vms-document-form-grid">
                    <label>Category Name <span>*</span><input name="parent_category_name" required maxlength="50" autocomplete="off"></label>
                    <label>Class ID<input name="ClassID" maxlength="10" autocomplete="off"></label>
                </div></fieldset>
                <fieldset data-category-fields="business" hidden disabled><div class="vms-document-form-grid">
                    <label>Bidang Usaha <span>*</span><input name="category_name" required maxlength="50" autocomplete="off"></label>
                    <label>Kategori Induk <span>*</span><select name="parent_category" required><option value="">-- Pilih Kategori --</option></select></label>
                </div></fieldset>
            </div>
            <footer><button type="button" data-category-close>Cancel</button><button type="submit" data-category-save>Save</button></footer>
        </form>
    </dialog>
    @push('scripts')<script src="{{ asset('assets/js/vms/vendor-category.js').'?v='.filemtime(public_path('assets/js/vms/vendor-category.js')) }}" defer></script>@endpush
</x-app-layout>
