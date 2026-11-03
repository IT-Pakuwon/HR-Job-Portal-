<x-app-layout>
<link rel="stylesheet" href="{{ asset('assets/css/vms/document.css').'?v='.filemtime(public_path('assets/css/vms/document.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/vms/vendor.css').'?v='.filemtime(public_path('assets/css/vms/vendor.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/vms/vendor-directory.css').'?v='.filemtime(public_path('assets/css/vms/vendor-directory.css')) }}">
<div class="vms-document-page" id="vms-vendors-page" data-base="{{ route('vms.vendors.index') }}" data-active="{{ $activeTab }}" data-businesses="{{ $businesses->toJson() }}">
<nav class="vms-document-tabs vms-vendor-tabs" aria-label="Tahap registrasi vendor" role="tablist">
@foreach($tabs as $key=>$label)<button type="button" role="tab" id="vendor-tab-{{ $key }}" data-vendor-tab="{{ $key }}" aria-selected="{{ $key===$activeTab?'true':'false' }}" aria-controls="vendor-panel-{{ $key }}"><span class="vms-directory-tab-icon" aria-hidden="true"><i class="fas {{ ['new'=>'fa-user-plus','incomplete'=>'fa-tasks','review'=>'fa-clipboard-check','master'=>'fa-building'][$key] }}"></i></span><span class="vms-directory-tab-label">{{ $label }}</span> <span class="vms-vendor-count" data-vendor-count="{{ $key }}">{{ number_format($counts[$key]) }}</span></button>@endforeach
</nav>
@foreach($tabs as $key=>$label)
<section class="vms-document-panel" id="vendor-panel-{{ $key }}" role="tabpanel" aria-labelledby="vendor-tab-{{ $key }}" data-vendor-panel="{{ $key }}" @if($key!==$activeTab) hidden @endif>
<header><div><h2>{{ $label }}</h2><p class="vms-vendor-description">{{ ['new'=>'Pendaftaran akun vendor yang menunggu persetujuan.','incomplete'=>'Vendor yang belum menyelesaikan pengisian profil dan dokumen.','review'=>'Vendor yang telah menyelesaikan pengisian dan sedang dalam review atau revisi.','master'=>'Vendor yang akun dan data profilnya telah disetujui.'][$key] }}</p></div><button type="button" data-vendor-refresh="{{ $key }}"><i class="fas fa-sync-alt" aria-hidden="true"></i> Refresh</button></header>
@if($key!=='new')
<div class="vms-vendor-filters" data-vendor-filters="{{ $key }}">
<label>Kategori vendor<select name="category"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->parent_category_name }}</option>@endforeach</select></label>
<label>Bidang Usaha<select name="business" disabled><option value="">Pilih kategori vendor terlebih dahulu</option></select></label>
@if($key==='master')<label>Jenis Vendor<select name="type"><option value="">Semua jenis</option>@foreach($types as $type)<option value="{{ $type }}">{{ ucwords(str_replace('_',' ',$type)) }}</option>@endforeach</select></label>@endif
<button type="button" data-vendor-reset="{{ $key }}"><i class="fas fa-undo" aria-hidden="true"></i> Reset filter</button></div>
@endif
@if($key==='review')<p class="vms-vendor-description vms-directory-legend">W: Waiting Review &middot; X: Rejected &middot; R: Request. Konfirmasi department dan status dokumen ditampilkan terpisah.</p>@endif
<p class="vms-document-feedback" style="margin:12px 16px" data-vendor-load-error="{{ $key }}" role="alert" hidden></p>
<div class="vms-document-table-wrap"><table class="w-full" id="vendor-table-{{ $key }}" aria-label="{{ $label }}"><thead><tr>
@if($key==='master')<th>Vendor ID</th>@endif
<th>Company Name</th>
@if($key==='incomplete')<th>Step</th>@endif
@if($key!=='master')<th>Username</th>@endif
@if($key==='master')<th>Category</th><th>Bidang Usaha</th><th>Jenis</th>@endif
<th>Email</th>
@if($key==='master')<th>Phone</th>@endif
@if(in_array($key,['incomplete','master']))<th>Company Profile</th>@endif
@if($key!=='master')<th>Register Date</th>@endif
@if(in_array($key,['incomplete','review']))<th>Category</th><th>Bidang Usaha</th>@endif
@if($key==='review')<th>Procurement</th><th>Legal</th><th>Tax</th><th>Treasury / Finance</th>@endif
<th>Action</th></tr></thead><tbody></tbody></table></div>
</section>@endforeach
</div>
<dialog id="vms-document-modal" class="vms-registration-modal" aria-labelledby="vms-registration-title">
<form id="vms-registration-form">@csrf
<header><h2 id="vms-registration-title">Review New Registration</h2><button type="button" data-registration-close aria-label="Tutup">&times;</button></header>
<div class="vms-document-modal-body">
<p class="vms-document-feedback" data-registration-error hidden role="alert"></p>
<div class="vms-registration-layout"><div class="vms-document-form-grid">
<label class="vms-document-full">Company Name <span>*</span><input name="vendor_name" required maxlength="50"></label>
<label>Username<input name="username" readonly></label><label>Email<input name="email" readonly></label>
<label class="vms-document-full">NPWP<input name="npwp" readonly></label>
<label>Vendor Category <span>*</span><select name="vendor_category" required><option value="">-- Pilih Category --</option></select></label>
<label>Bidang Usaha <span>*</span><select name="bidang_usaha" required><option value="">-- Pilih Bidang Usaha --</option></select></label>
<label class="vms-document-full">Tag<input name="tags" maxlength="10000" placeholder="Pisahkan tag dengan koma"><small>Opsional. Tag baru akan ditambahkan ke master tag.</small></label>
<label class="vms-document-full">Catatan<textarea name="notes" rows="3" maxlength="5000"></textarea></label>
</div><div class="vms-registration-profile"><a data-registration-pdf target="_blank" rel="noopener noreferrer" class="vms-vendor-link">Buka Company Profile &nearr;</a><p data-registration-no-pdf hidden>Company profile belum tersedia.</p><iframe data-registration-preview title="Company profile vendor" loading="lazy"></iframe></div></div>
<p class="vms-vendor-description" data-registration-readonly hidden>Keputusan registrasi hanya dapat dilakukan Procurement atau administrator.</p>
</div>
<footer><button type="button" data-registration-close>Close</button><button type="button" data-registration-reject class="vms-registration-reject">Reject</button><button type="submit" data-registration-approve>Approve</button></footer>
</form></dialog>
<dialog id="vms-incomplete-modal" aria-labelledby="vms-incomplete-title">
<header><h2 id="vms-incomplete-title">Review Incomplete Registration Vendor</h2><button type="button" data-incomplete-close aria-label="Tutup">&times;</button></header>
<div data-incomplete-content class="vms-incomplete-body" aria-live="polite"></div>
<footer><button type="button" data-incomplete-close>Close</button></footer>
</dialog>
<dialog id="vms-profile-modal" aria-labelledby="vms-profile-title"><header><h2 id="vms-profile-title">Vendor Review</h2><button type="button" data-profile-close aria-label="Tutup">&times;</button></header><div class="vms-profile-modal-body" data-profile-content aria-live="polite"></div><footer><button type="button" data-profile-close>Close</button></footer></dialog>
@push('scripts')<script src="{{ asset('assets/js/vms/vendor-list.js').'?v='.filemtime(public_path('assets/js/vms/vendor-list.js')) }}" defer></script>@endpush
</x-app-layout>
