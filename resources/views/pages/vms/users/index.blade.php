<x-app-layout>
<link rel="stylesheet" href="{{ asset('assets/css/vms/document.css').'?v='.filemtime(public_path('assets/css/vms/document.css')) }}">
<div class="vms-document-page" id="vms-master-page" data-kind="users" data-base="{{ route('vms-setup.users') }}" data-key="id" data-title="Users VMS">
<nav class="vms-document-tabs"><button type="button" aria-selected="true"><i class="fas fa-users" aria-hidden="true"></i> Users VMS</button></nav>
<section class="vms-document-panel"><header><h2>Users VMS</h2><button type="button" data-master-add>+ Add User</button></header>
<div class="vms-document-table-wrap"><table id="vms-master-table" class="w-full" aria-label="Users VMS"><thead><tr><th>Actions</th><th data-column="id">User ID</th><th data-column="name">Name</th><th data-column="username">Username</th><th data-column="email">Email</th><th data-column="role">Role</th><th data-column="approver">Approver</th><th data-column="notification">Notification</th><th data-column="api">API</th><th data-column="status">Status</th></tr></thead><tbody></tbody></table></div></section></div>
<dialog id="vms-document-modal" aria-labelledby="vms-master-title"><form id="vms-master-form">@csrf
<header><h2 id="vms-master-title">Add Users VMS</h2><button type="button" data-master-close aria-label="Tutup">&times;</button></header>
<div class="vms-document-modal-body"><p class="vms-document-feedback" data-master-error role="alert" hidden></p><div class="vms-document-form-grid">
<label>User ID<input data-master-key disabled><small>ID dibuat otomatis.</small></label>
<label>Status <span>*</span><select name="status" required><option value="1">Active</option><option value="0">Inactive</option></select></label>
<label>Name <span>*</span><input name="name" required maxlength="50" autocomplete="off"></label>
<label>Username <span>*</span><input name="username" required maxlength="50" autocomplete="off"></label>
<label>Email <span>*</span><input name="email" type="email" required maxlength="50" autocomplete="off"></label>
<label>Employee ID Acumatica<input name="idacum" maxlength="10"></label>
<label>Password <span data-password-required>*</span><input name="password" type="password" minlength="6" maxlength="128" autocomplete="new-password"><small data-password-hint>Minimal 6 karakter.</small></label>
<label>Confirm Password<input name="password_confirmation" type="password" minlength="6" maxlength="128" autocomplete="new-password"></label>
<label>Role <span>*</span><select name="role" required><option value="">-- Pilih Role --</option>@foreach($roles as $role)<option value="{{ $role }}">{{ ucfirst($role) }}</option>@endforeach</select></label>
<label>Telegram<input name="telegram" maxlength="32"></label>
<fieldset class="vms-document-full"><legend>Company</legend><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;max-height:160px;overflow:auto;padding:10px;border:1px solid #dce4ef;border-radius:7px">
@foreach($companies as $company)<label style="display:flex;align-items:center;gap:8px"><input style="width:auto" type="checkbox" name="companies[]" value="{{ $company->CpnyID }}">{{ $company->CpnyID }} - {{ $company->CpnyName }}</label>@endforeach
</div></fieldset>
<label>Approver <span>*</span><select name="approver" required><option value="Y">Yes</option><option value="N" selected>No</option></select></label>
<label>Notification <span>*</span><select name="notification" required><option value="Y" selected>Yes</option><option value="N">No</option></select></label>
<label>API <span>*</span><select name="api" required><option value="Y">Yes</option><option value="N" selected>No</option></select></label>
<label>Approver Vendor Manual <span>*</span><select name="approver_vendor_manual" required><option value="Y">Yes</option><option value="N" selected>No</option></select></label>
<label>Approver e-Document <span>*</span><select name="approver_dokumen" required><option value="Y">Yes</option><option value="N" selected>No</option></select></label>
</div></div><footer><button type="button" data-master-close>Cancel</button><button type="submit" data-master-save>Save</button></footer>
</form></dialog>
@push('scripts')<script src="{{ asset('assets/js/vms/master-crud.js').'?v='.filemtime(public_path('assets/js/vms/master-crud.js')) }}" defer></script>@endpush
</x-app-layout>
