<x-app-layout>
<link rel="stylesheet" href="{{ asset('assets/css/vms/document.css') }}">
<div class="vms-document-page" id="vms-setup-page" data-base="{{ route('vms-setup.vendor-category.documents.index', ['parent'=>$parent->id, 'business'=>$business->category_id]) }}">
<nav class="vms-document-tabs" style="padding:16px;gap:12px;flex-wrap:wrap" aria-label="Breadcrumb"><a href="{{ route('vms-setup.vendor-category') }}">Vendor Category</a><span>/</span><a href="{{ route('vms-setup.vendor-category.businesses', $parent->id) }}">{{ $parent->parent_category_name }}</a><span>/ {{ $business->category_name }} / Setup Document</span></nav>
<section class="vms-document-panel"><header><h2>Setup Document - {{ $business->category_name }}</h2><button type="button" id="setup-add">+ Add Document</button></header>
<div class="vms-document-table-wrap"><table id="setup-table" class="w-full"><thead><tr><th>ID</th><th>Document Name</th><th>Mandatory</th><th>Published</th><th>Created at</th></tr></thead><tbody></tbody></table></div></section>
</div>
<dialog id="vms-document-modal" aria-labelledby="setup-title"><form id="setup-form">@csrf
<header><h2 id="setup-title">Add Document</h2><button type="button" data-close aria-label="Tutup">&times;</button></header>
<div class="vms-document-modal-body"><p class="vms-document-feedback" id="setup-error" hidden role="alert"></p><label>Document <span>*</span><select name="document_id" required style="width:100%;padding:10px"><option value="">-- Pilih Dokumen --</option></select></label><p style="margin-top:14px">Dokumen baru otomatis Mandatory dan Published. Pengaturan dapat diubah melalui switch pada tabel.</p></div>
<footer><button type="button" data-close>Cancel</button><button type="submit">Save</button></footer></form></dialog>
@push('scripts')<script src="{{ asset('assets/js/vms/setup-document.js').'?v='.filemtime(public_path('assets/js/vms/setup-document.js')) }}" defer></script>@endpush
</x-app-layout>
