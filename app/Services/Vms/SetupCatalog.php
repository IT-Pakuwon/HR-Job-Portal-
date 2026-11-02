<?php

namespace App\Services\Vms;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SetupCatalog
{
    public const SECTIONS = [
        'document' => ['title' => 'Document', 'description' => 'Master dokumen registrasi vendor dan attachment transaksi.', 'tabs' => [
            'vendor' => ['label' => 'Dokumen Vendor', 'table' => 'master_document', 'key' => 'document_id', 'columns' => ['document_id' => 'ID', 'document_name' => 'Nama Dokumen', 'document_remark' => 'Keterangan', 'devision_id' => 'Department', 'document_order' => 'Urutan', 'has_expire_date' => 'Masa Berlaku', 'status' => 'Status']],
            'invoice' => ['label' => 'Dokumen Invoice', 'table' => 'ord_masterdoc', 'key' => 'id', 'columns' => ['id' => 'ID', 'Module' => 'Module', 'document_name' => 'Nama Dokumen', 'DivisionID' => 'Department', 'Required' => 'Required']],
        ]],
        'vendor-category' => ['title' => 'Vendor Category', 'description' => 'Kategori vendor dan bidang usaha yang digunakan di portal vendor.', 'tabs' => [
            'category' => ['label' => 'Kategori Vendor', 'table' => 'vendor_category_parent', 'key' => 'id', 'columns' => ['id' => 'ID', 'parent_category_name' => 'Kategori', 'ClassID' => 'Class ID', 'status' => 'Status']],
            'business' => ['label' => 'Bidang Usaha', 'table' => 'vendor_category', 'key' => 'category_id', 'columns' => ['category_id' => 'ID', 'category_name' => 'Bidang Usaha', 'parent_category' => 'Kategori Induk', 'status' => 'Status']],
        ]],
        'company-department' => ['title' => 'Company & Department', 'description' => 'Master company dan departemen reviewer VMS.', 'tabs' => [
            'company' => ['label' => 'Company', 'table' => 'company', 'key' => 'CpnyID', 'columns' => ['CpnyID' => 'Company ID', 'CpnyName' => 'Company', 'CpnyGroup' => 'Group', 'City' => 'Kota', 'Country' => 'Negara']],
            'department' => ['label' => 'Department', 'table' => 'master_devision', 'key' => 'devision_id', 'columns' => ['devision_id' => 'Department ID', 'devision_name' => 'Department']],
        ]],
        'bank' => ['title' => 'Bank', 'description' => 'Master bank yang digunakan pada rekening vendor.', 'tabs' => [
            'bank' => ['label' => 'Bank', 'table' => 'master_bank', 'key' => 'bank_id', 'columns' => ['bank_id' => 'Bank ID', 'bank_name' => 'Nama Bank', 'kode_kliring' => 'Kode Kliring', 'kode_rtgs' => 'Kode RTGS', 'swift_kode' => 'SWIFT', 'transfer_fee' => 'Biaya Transfer']],
        ]],
        'users' => ['title' => 'Users', 'description' => 'Akun internal VMS beserta role dan pengaturan approval.', 'tabs' => [
            'users' => ['label' => 'Users Internal VMS', 'table' => 'admin', 'key' => 'id', 'columns' => ['id' => 'ID', 'name' => 'Nama', 'username' => 'Username', 'email' => 'Email', 'role' => 'Role', 'status' => 'Status', 'approver' => 'Approver', 'notification' => 'Notifikasi']],
        ]],
    ];

    public function definition(string $section, ?string $tab = null): array
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);
        $definition = self::SECTIONS[$section];
        $tab ??= array_key_first($definition['tabs']);
        abort_unless(isset($definition['tabs'][$tab]), 404);

        return $definition + ['section' => $section, 'activeTab' => $tab, 'active' => $definition['tabs'][$tab]];
    }

    public function query(array $definition, string $search = ''): Builder
    {
        $active = $definition['active'];
        // Select only allowlisted public columns, never passwords or session tokens.
        $query = DB::connection('mysql7')->table($active['table'])->select(array_keys($active['columns']));
        if ($search !== '') {
            $query->where(function (Builder $query) use ($active, $search) {
                foreach (array_keys($active['columns']) as $column) {
                    $query->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        return $query->orderBy($active['key']);
    }
}
