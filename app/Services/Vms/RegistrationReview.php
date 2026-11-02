<?php

namespace App\Services\Vms;

use App\Models\Vms\InternalUser;
use App\Models\Vms\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationReview
{
    private const COLUMNS = ['vendor_id', 'vendor_nama', 'username', 'email', 'npwp', 'company_profile', 'vendor_category', 'bidang_usaha', 'account_status', 'step', 'type', 'updated_at'];

    public function canDecide($user): bool
    {
        if (! $user) {
            return false;
        }
        if (in_array('admin', $user->roles(), true)) {
            return true;
        }

        return InternalUser::where('username', $user->username)->where('status', 1)->whereIn('role', ['procurement', 'superadmin'])->exists();
    }

    private function pending(Vendor $vendor): void
    {
        abort_unless((int) $vendor->account_status === 0 && $vendor->type !== 'manual', 409, 'Registrasi ini sudah diproses atau bukan registrasi baru. Muat ulang daftar.');
    }

    private function token(Vendor $vendor): string
    {
        $values = [];
        foreach (self::COLUMNS as $column) {
            $values[$column] = (string) $vendor->{$column};
        }

        return hash('sha256', json_encode($values, JSON_THROW_ON_ERROR));
    }

    public function read(int $id, VendorDirectory $directory): array
    {
        $vendor = Vendor::select(self::COLUMNS)->findOrFail($id);
        $this->pending($vendor);

        return $vendor->toArray() + ['review_token' => $this->token($vendor), 'profile_url' => $directory->fileUrl('company_profile', $vendor->company_profile),
            'tags' => DB::connection('mysql7')->table('vendor_tag as v')->join('master_tag as t', 't.id', '=', 'v.id_tag')->where('v.id_vendor', $id)->pluck('t.nama_tag')];
    }

    public function decide(int $id, array $data, $actor): array
    {
        abort_unless($this->canDecide($actor), 403, 'Hanya Procurement atau administrator yang dapat memutuskan registrasi.');
        $db = DB::connection('mysql7');

        return $db->transaction(function () use ($db, $id, $data, $actor) {
            $vendor = Vendor::select(self::COLUMNS)->whereKey($id)->lockForUpdate()->firstOrFail();
            $this->pending($vendor);
            abort_unless(hash_equals($this->token($vendor), $data['review_token']), 409, 'Data registrasi telah berubah. Tutup dan buka kembali modal Review.');
            $approved = $data['decision'] === 'approve';
            $changes = ['account_status' => $approved ? 1 : 2, 'updated_at' => now()];
            $legacyActor = InternalUser::where('username', $actor->username)->where('status', 1)->value('id');
            if ($legacyActor) {
                $changes['updated_by'] = $legacyActor;
            }
            if ($approved) {
                $category = $db->table('vendor_category_parent')->where('id', $data['vendor_category'])->where('status', 1)->lockForUpdate()->first();
                $business = $db->table('vendor_category')->where('category_id', $data['bidang_usaha'])->where('parent_category', $data['vendor_category'])->where('status', 1)->lockForUpdate()->first();
                if (! $category || ! $business) {
                    throw ValidationException::withMessages(['bidang_usaha' => 'Pilih kategori dan bidang usaha aktif yang sesuai.']);
                }
                $installed = $db->table('setup_document as s')->join('master_document as d', 'd.document_id', '=', 's.document_id')->where('s.category_id', $business->category_id)->where('s.is_published', 1)->where('d.status', 1)->exists();
                if (! $installed) {
                    throw ValidationException::withMessages(['bidang_usaha' => 'Dokumen untuk bidang usaha terpilih belum di-setup atau belum Published.']);
                }
                $changes += ['vendor_nama' => $data['vendor_name'], 'vendor_category' => $data['vendor_category'], 'bidang_usaha' => $data['bidang_usaha'], 'data_status' => 'inprogress'];
                foreach ($data['tags'] ?? [] as $tag) {
                    $tagId = $db->table('master_tag')->where('nama_tag', $tag)->lockForUpdate()->value('id');
                    if (! $tagId) {
                        $tagId = $db->table('master_tag')->insertGetId(['nama_tag' => $tag, 'status' => '1', 'created_at' => now(), 'created_by' => $legacyActor ?? 0]);
                    }
                    if (! $db->table('vendor_tag')->where('id_vendor', $id)->where('id_tag', $tagId)->exists()) {
                        $db->table('vendor_tag')->insert(['id_vendor' => $id, 'id_tag' => $tagId, 'created_at' => now(), 'created_by' => $legacyActor ?? 0]);
                    }
                }
            }
            $db->table('vendor')->where('vendor_id', $id)->update($changes);
            $db->table('vendor_internal_activity')->insert(['vendor_id' => $id, 'activity' => $approved ? 'Registration Account Approved' : 'Registration Account Rejected', 'notes' => 'Pelaku: '.$actor->username.(empty($data['notes']) ? '' : "\n".$data['notes']), 'created_by' => $legacyActor, 'created_at' => now()]);
            $vendorName = $approved ? $data['vendor_name'] : $vendor->vendor_nama;
            $activity = $approved ? 'vendor_account_approved' : 'vendor_account_rejected';
            $recipients = [$vendor->email];
            if (! $approved) {
                $recipients[] = $db->table('vendor_basic_information')->where('vendor_id', $id)->orderByDesc('id')->value('email2');
            }
            $delivery = app(RegistrationEmail::class)->enqueue($activity, 'registration:'.$id.':'.$data['review_token'], $recipients, ['vendor' => $vendorName, 'username' => $vendor->username, 'notes' => $data['notes'] ?? '']);

            return ['delivery_id' => $delivery, 'message' => $approved ? 'Registrasi berhasil disetujui. Vendor dapat melanjutkan pengisian profil.' : 'Registrasi berhasil ditolak.'];
        });
    }
}
