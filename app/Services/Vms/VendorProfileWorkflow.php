<?php

namespace App\Services\Vms;

use App\Models\Vms\InternalUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VendorProfileWorkflow
{
    public const DEPARTMENTS = ['procurement' => 'Procurement', 'legal' => 'Legal', 'tax' => 'Tax', 'finance' => 'Treasury / Finance'];

    public function actor($user): array
    {
        $legacy = $user ? InternalUser::where('username', $user->username)->where('status', 1)->first(['id', 'role', 'approver']) : null;
        $all = $user && (in_array('admin', $user->roles(), true) || ($legacy?->role === 'superadmin' && $legacy?->approver === 'Y'));

        return ['id' => $legacy?->id, 'username' => $user?->username, 'all' => (bool) $all, 'departments' => $all ? array_keys(self::DEPARTMENTS) : ($legacy && $legacy->approver === 'Y' && isset(self::DEPARTMENTS[$legacy->role]) ? [$legacy->role] : [])];
    }

    public function token($row): string
    {
        $keys = ['id', 'vendor_id', 'account_status', 'data_status', 'step', 'status', 'status_npwp', 'updated_at', 'approved_at', 'doc_name', 'doc_expire', 'notes', 'rejection_note', 'procurement_approval', 'legal_approval', 'tax_approval', 'finance_approval'];
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = (string) ($row->{$key} ?? '');
        }

        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function vendor(int $id, string $mode, bool $lock = false)
    {
        $q = DB::connection('mysql7')->table('vendor')->where('vendor_id', $id);
        if ($lock) {
            $q->lockForUpdate();
        }
        $row = $q->first(['vendor_id', 'vendor_nama', 'username', 'email', 'account_status', 'data_status', 'step', 'status', 'bidang_usaha', 'second_vendor_id', 'updated_at', 'procurement_approval', 'legal_approval', 'tax_approval', 'finance_approval']);
        abort_unless($row, 404);
        abort_unless((int) $row->account_status === 1 && (int) $row->step >= 5 && ($mode === 'master' ? $row->data_status === 'approved' : in_array($row->data_status, ['inprogress', 'revise'], true)), 409, 'Tahap vendor sudah berubah. Muat ulang daftar.');

        return $row;
    }

    public function read(int $id, string $mode, $user, VendorDirectory $directory): array
    {
        $vendor = $this->vendor($id, $mode);
        $data = $directory->information($id);
        $db = DB::connection('mysql7');
        $actor = $this->actor($user);
        $data['permissions'] = $actor;
        $data['mode'] = $mode;
        $data['vendorToken'] = $this->token($vendor);
        $data['documentTokens'] = $db->table('vendor_document')->where('vendor_id', $id)->get()->mapWithKeys(fn ($r) => [$r->id => $this->token($r)]);
        $data['bankTokens'] = $db->table('vendor_bank')->where('vendor_id', $id)->get()->mapWithKeys(fn ($r) => [$r->id => $this->token($r)]);
        $npwp = $db->table('vendor_npwp')->where('vendor_id', $id)->orderByDesc('id')->first();
        $data['npwpToken'] = $npwp ? $this->token($npwp) : null;
        $data['accounts'] = $db->table('users')->where('parent_id', $id)->get(['id', 'username', 'email', 'account', 'status']);
        $data['messages'] = $db->table('chat as c')->leftJoin('admin as a', 'a.id', '=', 'c.sender_id')->where('c.vendor_id', $id)->orderBy('c.created_at')->orderBy('c.id')->get(['c.id', 'c.messages', 'c.created_at', 'a.name', 'a.username']);
        $data['tags'] = $db->table('vendor_tag as v')->join('master_tag as t', 't.id', '=', 'v.id_tag')->where('v.id_vendor', $id)->pluck('t.nama_tag');

        return $data;
    }

    private function requireDepartment(array $actor, string $department, $vendor): void
    {
        abort_unless(in_array($department, $actor['departments'], true), 403, 'Anda tidak memiliki hak approval departemen ini.');
        abort_unless($actor['all'] || $department === 'procurement' || $vendor->procurement_approval === 'yes', 422, 'Informasi dasar harus disetujui Procurement terlebih dahulu.');
        abort_if($vendor->{$department.'_approval'} === 'yes', 409, 'Departemen ini sudah dikonfirmasi. Muat ulang data.');
    }

    private function activity(int $id, array $actor, string $activity, string $notes): void
    {
        DB::connection('mysql7')->table('vendor_internal_activity')->insert(['vendor_id' => $id, 'activity' => $activity, 'notes' => 'Pelaku: '.$actor['username'].($notes !== '' ? "\n".$notes : ''), 'created_by' => $actor['id'], 'created_at' => now()]);
    }

    public function decide(int $id, array $data, $user): array
    {
        $actor = $this->actor($user);
        abort_unless($actor['departments'], 403);
        $db = DB::connection('mysql7');

        return $db->transaction(function () use ($db, $id, $data, $actor) {
            $vendor = $this->vendor($id, 'review', true);
            $kind = $data['kind'];
            $decision = $data['decision'];
            $notes = trim($data['notes'] ?? '');
            $delivery = null;
            if (in_array($decision, ['reject', 'revise'], true) && $notes === '') {
                throw ValidationException::withMessages(['notes' => 'Catatan wajib diisi untuk penolakan atau revisi.']);
            }
            abort_if($decision === 'revise' && $kind !== 'basic', 422);
            if (in_array($kind, ['document', 'npwp', 'bank'], true)) {
                $table = ['document' => 'vendor_document', 'npwp' => 'vendor_npwp', 'bank' => 'vendor_bank'][$kind];
                $row = $db->table($table)->where('vendor_id', $id)->where('id', $data['record_id'])->lockForUpdate()->first();
                abort_unless($row, 404);
                $department = $kind === 'document' ? $db->table('master_document')->where('document_id', $row->document_id)->where('status', 1)->value('devision_id') : ($kind === 'npwp' ? 'tax' : 'finance');
                $this->requireDepartment($actor, (string) $department, $vendor);
                abort_unless(hash_equals($this->token($row), $data['token']), 409, 'Data telah berubah. Buka ulang Review.');
                $statusField = $kind === 'npwp' ? 'status_npwp' : 'status';
                abort_if($row->{$statusField} === 'approved', 409, 'Data sudah disetujui.');
                if ($kind === 'document' && $decision === 'approve') {
                    if (empty($row->doc_name)) {
                        throw ValidationException::withMessages(['document' => 'File dokumen belum diunggah.']);
                    }
                    $expires = $db->table('master_document')->where('document_id', $row->document_id)->value('has_expire_date');
                    if ($expires === 'yes' && (! $row->doc_expire || $row->doc_expire < now()->toDateString())) {
                        throw ValidationException::withMessages(['document' => 'Dokumen sudah kedaluwarsa atau tanggal berlaku belum diisi.']);
                    }
                }
                $values = [$statusField => $decision === 'approve' ? 'approved' : 'rejected', 'updated_at' => now()];
                if ($kind === 'document') {
                    $values += ['approver_2' => $actor['id'], 'approved_at' => now(), 'notes' => $notes];
                } else {
                    $values += [$kind === 'npwp' ? 'approver_npwp' : 'approver' => $actor['id'], 'rejection_note' => $decision === 'reject' ? $notes : null];
                }
                $db->table($table)->where('id', $row->id)->where('vendor_id', $id)->update($values);
                $this->activity($id, $actor, ucfirst($kind).' '.($decision === 'approve' ? 'Approved' : 'Rejected'), $notes);
            } else {
                $department = $kind === 'basic' ? 'procurement' : $data['department'];
                $this->requireDepartment($actor, $department, $vendor);
                abort_unless(hash_equals($this->token($vendor), $data['token']), 409, 'Status review telah berubah. Buka ulang Review.');
                if ($kind === 'basic' && $decision !== 'approve') {
                    $values = ['procurement_approval' => 'no', 'procurement_notes' => $notes, 'procurement_date' => now(), 'updated_at' => now()];
                    $values += $decision === 'reject' ? ['account_status' => 2] : ['data_status' => 'revise', 'step' => 1, 'status' => 'H'];
                    $db->table('vendor')->where('vendor_id', $id)->update($values);
                    $db->table('vendor_basic_information')->where('vendor_id', $id)->update(['status' => $decision === 'reject' ? 'rejected' : 'revise', 'updated_at' => now(), 'updated_by' => $actor['id']]);
                } else {
                    if ($decision === 'approve') {
                        $this->checkDepartment($id, $department);
                    }
                    $values = [$department.'_approval' => 'yes', $department.'_notes' => $notes, $department.'_date' => now(), 'updated_at' => now()];
                    if ($decision === 'reject') {
                        $values += ['data_status' => 'revise', 'status' => 'H'];
                    }
                    $db->table('vendor')->where('vendor_id', $id)->update($values);
                    if ($department === 'procurement') {
                        $db->table('vendor_basic_information')->where('vendor_id', $id)->update(['status' => 'approved', 'updated_at' => now(), 'updated_by' => $actor['id']]);
                    }
                    if ($decision === 'approve') {
                        $this->finalize($id);
                    }
                }
                $this->activity($id, $actor, ucfirst($department).' Review '.ucfirst($decision), $notes);
                $fresh = $db->table('vendor')->where('vendor_id', $id)->first(['data_status']);
                if ($decision !== 'approve' || $fresh->data_status === 'approved') {
                    $delivery = app(RegistrationEmail::class)->enqueue('vendor_profile_review', 'profile:'.$id.':'.$kind.':'.$data['token'], [$vendor->email], ['vendor' => $vendor->vendor_nama, 'status' => $fresh->data_status, 'notes' => $notes]);
                }
            }

            return ['message' => 'Hasil review berhasil disimpan.', 'delivery_id' => $delivery];
        });
    }

    private function checkDepartment(int $id, string $department): void
    {
        $db = DB::connection('mysql7');
        $pending = $db->table('vendor_document as d')->join('master_document as m', 'm.document_id', '=', 'd.document_id')->where('d.vendor_id', $id)->where('m.status', 1)->where('m.devision_id', $department)->where(fn ($q) => $q->whereNull('d.status')->orWhere('d.status', '!=', 'approved'))->exists();
        if ($department === 'finance') {
            $pending = $pending || $db->table('vendor_bank')->where('vendor_id', $id)->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'approved'))->exists();
        }
        if ($department === 'tax') {
            $pending = $pending || $db->table('vendor_npwp')->where('vendor_id', $id)->where(fn ($q) => $q->whereNull('status_npwp')->orWhere('status_npwp', '!=', 'approved'))->exists();
        }
        if ($pending) {
            throw ValidationException::withMessages(['review' => 'Masih ada dokumen atau data departemen yang belum disetujui. Selesaikan pemeriksaan atau gunakan Reject dengan catatan.']);
        }
    }

    private function finalize(int $id): void
    {
        $db = DB::connection('mysql7');
        $vendor = $db->table('vendor')->where('vendor_id', $id)->first();
        foreach (array_keys(self::DEPARTMENTS) as $department) {
            if ($vendor->{$department.'_approval'} !== 'yes') {
                return;
            }
        }
        foreach (array_keys(self::DEPARTMENTS) as $department) {
            $this->checkDepartment($id, $department);
        }
        // Published mandatory requirements must have a valid approved upload as well.
        $requirements = $db->table('setup_document as s')->join('master_document as m', 'm.document_id', '=', 's.document_id')->where('s.category_id', $vendor->bidang_usaha)->where('s.is_published', 1)->where('s.is_mandatory', 1)->where('m.status', 1)->get(['m.document_id', 'm.has_conditional', 'm.conditional_date', 'm.has_expire_date']);
        $established = $db->table('vendor_basic_information')->where('vendor_id', $id)->value('tanggal_berdiri');
        foreach ($requirements as $required) {
            $cutoff = $required->conditional_date && $required->conditional_date !== '0000-00-00' ? $required->conditional_date : '2007-08-15';
            if ($required->has_conditional === 'Y' && $established && $established > $cutoff) {
                continue;
            }
            $q = $db->table('vendor_document')->where('vendor_id', $id)->where('document_id', $required->document_id)->where('status', 'approved')->whereNotNull('doc_name')->where('doc_name', '!=', '');
            if ($required->has_expire_date === 'yes') {
                $q->where('doc_expire', '>=', now()->toDateString());
            }
            if (! $q->exists()) {
                throw ValidationException::withMessages(['review' => 'Dokumen wajib belum lengkap atau sudah kedaluwarsa. Vendor belum dapat diaktifkan.']);
            }
        }
        $values = ['data_status' => 'approved', 'status' => 'A', 'updated_at' => now()];
        if (! $vendor->approved_date) {
            $values['approved_date'] = now();
        }
        if (! $vendor->second_vendor_id) {
            $setting = $db->table('vms_setting')->where('id', 1)->lockForUpdate()->first();
            abort_unless($setting && $setting->prevendnbr, 422, 'Penomoran vendor belum di-setup.');
            $number = (int) $setting->lastvendnbr;
            do {
                $number++;
                $second = $setting->prevendnbr.str_pad((string) $number, 5, '0', STR_PAD_LEFT);
            } while ($db->table('vendor')->where('second_vendor_id', $second)->exists());
            $values['second_vendor_id'] = $second;
            $values['approved_date'] = now();
            $db->table('vms_setting')->where('id', 1)->update(['lastvendnbr' => str_pad((string) $number, 5, '0', STR_PAD_LEFT)]);
        }
        $db->table('vendor')->where('vendor_id', $id)->update($values);
    }

    public function chat(int $id, string $mode, string $message, $user, string $ip): void
    {
        $this->vendor($id, $mode);
        $actor = $this->actor($user);
        abort_unless($actor['id'], 403, 'Akun internal belum terhubung dengan user VMS.');
        DB::connection('mysql7')->table('chat')->insert(['sender_id' => $actor['id'], 'receiver_id' => 0, 'messages' => $message, 'vendor_id' => $id, 'created_at' => now(), 'ip_address' => $ip]);
    }

    public function password(int $id, string $mode, int $userId, string $password, $user): void
    {
        $actor = $this->actor($user);
        abort_unless($actor['all'] || in_array('procurement', $actor['departments'], true), 403);
        $db = DB::connection('mysql7');
        $db->transaction(function () use ($db, $id, $mode, $userId, $password, $actor) {
            $vendor = $this->vendor($id, $mode, true);
            $account = $db->table('users')->where('parent_id', $id)->where('id', $userId)->lockForUpdate()->first(['id', 'username']);
            abort_unless($account, 404);
            // The shared CI3 vendor login still uses MD5.
            $hash = md5($password);
            $db->table('users')->where('id', $userId)->update(['password' => $hash, 'updated_at' => now(), 'updated_by' => $actor['id']]);
            if ($account->username === $vendor->username) {
                $db->table('vendor')->where('vendor_id', $id)->update(['password' => $hash, 'updated_at' => now(), 'updated_by' => $actor['id']]);
            }
            $this->activity($id, $actor, 'Vendor Account Password Changed', 'Account ID: '.$userId);
        });
    }
}
