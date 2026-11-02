<x-app-layout>
<link rel="stylesheet" href="{{ asset('assets/css/vms/document.css').'?v='.filemtime(public_path('assets/css/vms/document.css')) }}">
<div class="vms-document-page" id="vms-master-page" data-kind="bank" data-base="{{ route('vms-setup.bank') }}" data-key="bank_id" data-title="Bank">
<nav class="vms-document-tabs"><button type="button" aria-selected="true"><i class="fas fa-university" aria-hidden="true"></i> Bank</button></nav>
<section class="vms-document-panel"><header><h2>Bank</h2><button type="button" data-master-add>+ Add Bank</button></header>
<div class="vms-document-table-wrap"><table id="vms-master-table" class="w-full" aria-label="Bank"><thead><tr><th>Actions</th><th data-column="bank_id">Bank Code</th><th data-column="bank_name">Bank Name</th><th data-column="kode_kliring">Kliring ID</th><th data-column="kode_rtgs">RTGS Code</th><th data-column="swift_kode">SWIFT Code</th><th data-column="transfer_fee">Transfer Fee</th></tr></thead><tbody></tbody></table></div></section></div>
<dialog id="vms-document-modal" aria-labelledby="vms-master-title"><form id="vms-master-form">@csrf
<header><h2 id="vms-master-title">Add Bank</h2><button type="button" data-master-close aria-label="Tutup">&times;</button></header>
<div class="vms-document-modal-body"><p class="vms-document-feedback" data-master-error role="alert" hidden></p><div class="vms-document-form-grid">
<label>Bank Code <span>*</span><input name="bank_id" type="text" maxlength="4" pattern="[A-Za-z0-9_-]+" data-master-key required autocomplete="off"></label>
<label>Bank Name <span>*</span><input name="bank_name" type="text" maxlength="50" required autocomplete="off"></label>
<label>Kliring ID<input name="kode_kliring" type="text" maxlength="20" autocomplete="off"></label>
<label>RTGS Code<input name="kode_rtgs" type="text" maxlength="20" autocomplete="off"></label>
<label>SWIFT Code<input name="swift_kode" type="text" maxlength="20" autocomplete="off"></label>
<label>Transfer Fee <span>*</span><input name="transfer_fee" type="number" min="0" max="2147483647" value="0" required autocomplete="off"></label>
</div></div><footer><button type="button" data-master-close>Cancel</button><button type="submit" data-master-save>Save</button></footer>
</form></dialog>
@push('scripts')<script src="{{ asset('assets/js/vms/master-crud.js').'?v='.filemtime(public_path('assets/js/vms/master-crud.js')) }}" defer></script>@endpush
</x-app-layout>
