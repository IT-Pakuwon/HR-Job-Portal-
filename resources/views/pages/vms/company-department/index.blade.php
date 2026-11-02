<x-app-layout>
<link rel="stylesheet" href="{{ asset('assets/css/vms/document.css').'?v='.filemtime(public_path('assets/css/vms/document.css')) }}">
<div class="vms-document-page" id="vms-company-page" data-base="{{ route('vms-setup.company-department') }}">
    <nav class="vms-document-tabs" aria-label="Company dan Department">
        <button type="button" data-company-tab="company"><i class="fas fa-building" aria-hidden="true"></i> Company</button>
        <button type="button" data-company-tab="department"><i class="fas fa-sitemap" aria-hidden="true"></i> Department</button>
    </nav>
    @foreach(['company' => 'Company', 'department' => 'Department'] as $kind => $title)
    <section class="vms-document-panel" data-company-panel="{{ $kind }}" @if($kind !== 'company') hidden @endif>
        <header><h2><i class="fas fa-{{ $kind === 'company' ? 'building' : 'sitemap' }}" aria-hidden="true"></i> {{ $title }}</h2><button type="button" data-company-add="{{ $kind }}">+ Add {{ $title }}</button></header>
        <div class="vms-document-table-wrap"><table id="company-table-{{ $kind }}" class="w-full" aria-label="{{ $title }}"><thead><tr><th>Actions</th>
            @if($kind === 'company')<th>Company ID</th><th>Company Name</th><th>NPWP Name</th><th>NPWP Number</th><th>Finance Address</th>
            @else<th>Department ID</th><th>Department Name</th>@endif
        </tr></thead><tbody></tbody></table></div>
    </section>
    @endforeach
</div>
<dialog id="vms-document-modal" aria-labelledby="vms-company-title">
<form id="vms-company-form">@csrf
    <header><h2 id="vms-company-title">Add Company</h2><button type="button" data-company-close aria-label="Tutup">&times;</button></header>
    <div class="vms-document-modal-body">
        <p class="vms-document-feedback" data-company-error role="alert" hidden></p>
        <fieldset data-company-fields="company"><div class="vms-document-form-grid">
            <label>Company ID <span>*</span><input name="CpnyID" required maxlength="10" pattern="[A-Za-z0-9_-]+" autocomplete="off"><small>ID tidak dapat diubah setelah disimpan.</small></label>
            <label>Company Name <span>*</span><input name="CpnyName" required maxlength="255" autocomplete="off"></label>
            <label>NPWP Name<input name="NpwpName" maxlength="255"></label>
            <label>NPWP Number<input name="NpwpNumber" maxlength="50"></label>
            <label class="vms-document-full">Finance Address<textarea name="finance_address" maxlength="255" rows="3"></textarea></label>
        </div></fieldset>
        <fieldset data-company-fields="department" hidden disabled><div class="vms-document-form-grid">
            <label>Department ID <span>*</span><input name="devision_id" required maxlength="20" pattern="[A-Za-z0-9_-]+" autocomplete="off"><small>ID tidak dapat diubah setelah disimpan.</small></label>
            <label>Department Name <span>*</span><input name="devision_name" required maxlength="225" autocomplete="off"></label>
        </div></fieldset>
    </div>
    <footer><button type="button" data-company-close>Cancel</button><button type="submit" data-company-save>Save</button></footer>
</form>
</dialog>
@push('scripts')<script src="{{ asset('assets/js/vms/company-department.js').'?v='.filemtime(public_path('assets/js/vms/company-department.js')) }}" defer></script>@endpush
</x-app-layout>
