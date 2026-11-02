<x-app-layout>
    <link rel="stylesheet" href="{{ asset('assets/css/vms/document.css').'?v='.filemtime(public_path('assets/css/vms/document.css')) }}">
    <div class="vms-document-page" id="vms-document-page" data-base="{{ route('vms-setup.document') }}" data-departments="{{ $departments->toJson() }}">
        <nav class="vms-document-tabs" aria-label="Jenis dokumen">
            <button type="button" data-document-tab="vendor"><i class="fas fa-file-alt" aria-hidden="true"></i> Dokumen Vendor</button>
            <button type="button" data-document-tab="invoice"><i class="fas fa-file-invoice" aria-hidden="true"></i> Dokumen Invoice</button>
        </nav>
        @foreach(['vendor' => 'Dokumen Vendor', 'invoice' => 'Dokumen Invoice'] as $kind => $title)
        <section class="vms-document-panel" data-document-panel="{{ $kind }}" @if($kind !== 'vendor') hidden @endif>
            <header><h2><i class="fas fa-file-alt" aria-hidden="true"></i> {{ $title }}</h2><button type="button" data-add-document="{{ $kind }}">+ Add Document</button></header>
            <div class="vms-document-table-wrap"><table id="document-table-{{ $kind }}" class="w-full" aria-label="{{ $title }}"><thead><tr>
                <th>Actions</th><th>Document ID</th>
                @if($kind === 'vendor')<th>Document Name</th><th>Remarks</th><th>Department</th><th>Order</th><th>Expire Date</th><th>Multiple Files</th><th>Status</th>
                @else<th>Module</th><th>Document Name</th><th>Department</th><th>Scan Imagick</th><th>ICDP</th><th>Required</th>@endif
            </tr></thead><tbody></tbody></table></div>
        </section>
        @endforeach
    </div>
    <dialog id="vms-document-modal" aria-labelledby="vms-document-modal-title">
        <form id="vms-document-form">@csrf
            <header><h2 id="vms-document-modal-title">Add Document</h2><button type="button" data-document-close aria-label="Tutup">&times;</button></header>
            <div class="vms-document-modal-body">
                <p class="vms-document-feedback" data-document-error role="alert" hidden></p>
                <div class="vms-document-form-grid">
                    <label>Document ID<input name="id" type="number" min="1" max="2147483647" data-document-id><small data-document-id-hint>ID dibuat otomatis.</small></label>
                    <label>Document Name <span>*</span><input name="document_name" required maxlength="255" autocomplete="off"></label>
                </div>
                <fieldset data-document-fields="vendor"><div class="vms-document-form-grid">
                    <label>Department <span>*</span><select name="devision_id" required><option value="">-- Pilih Department --</option>@foreach($departments as $department)<option value="{{ $department->devision_id }}">{{ $department->devision_name }}</option>@endforeach</select></label>
                    <label>Urutan<input type="number" name="document_order" min="0" max="2147483647"></label>
                    <label>Status <span>*</span><select name="status"><option value="1">Active</option><option value="0">Inactive</option></select></label>
                    <label>Memiliki masa berlaku <span>*</span><select name="has_expire_date"><option value="no">Tidak</option><option value="yes">Ya</option></select></label>
                    <label>Multiple Files <span>*</span><select name="multiple_files"><option value="N">Tidak</option><option value="Y">Ya</option></select></label>
                    <label>Related Privy <span>*</span><select name="related_privy"><option value="N">Tidak</option><option value="Y">Ya</option></select></label>
                    <label>Bersyarat tanggal pendirian <span>*</span><select name="has_conditional"><option value="N">Tidak</option><option value="Y">Ya</option></select></label>
                    <label data-conditional-date hidden>Batas tanggal pendirian <span>*</span><input type="date" name="conditional_date"></label>
                    <label class="vms-document-full">Remarks<textarea name="document_remark" rows="3" maxlength="10000"></textarea></label>
                </div></fieldset>
                <fieldset data-document-fields="invoice" hidden disabled><div class="vms-document-form-grid">
                    <label>Module <span>*</span><select name="Module" required><option value="Purchase Material">Purchase Material (PO)</option><option value="Purchase Service">Purchase Service (SPK)</option><option value="Kontrak">Kontrak</option></select></label>
                    <label>Department <span>*</span><select name="DivisionID" required><option value="">-- Pilih Department --</option>@foreach($departments as $department)<option value="{{ $department->devision_id }}">{{ $department->devision_name }}</option>@endforeach</select></label>
                    <label>Required <span>*</span><select name="Required"><option value="Y">Required</option><option value="N">Optional</option></select></label>
                    <label>Scan Imagick <span>*</span><select name="scan_imagick"><option value="N">Tidak</option><option value="Y">Ya</option></select></label>
                    <label>ICDP<input name="ICDP" maxlength="5"></label>
                </div></fieldset>
            </div>
            <footer><button type="button" data-document-close>Cancel</button><button type="submit" data-document-save>Save</button></footer>
        </form>
    </dialog>
    @push('scripts')<script src="{{ asset('assets/js/vms/document.js').'?v='.filemtime(public_path('assets/js/vms/document.js')) }}" defer></script>@endpush
</x-app-layout>
