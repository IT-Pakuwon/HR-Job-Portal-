<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $indexes = [
        'staging_po' => [
            'vms_po_listing' => ['material_service', 'podate', 'id', 'cpny_id'],
            'vms_po_vendor_listing' => ['cpny_id', 'vendor_id', 'material_service', 'podate', 'id'],
        ],
        'staging_kontrak' => [
            'vms_kontrak_listing' => ['kontrakdate', 'id', 'cpny_id'],
            'vms_kontrak_vendor_listing' => ['cpny_id', 'vendor_id', 'kontrakdate', 'id'],
        ],
        'staging_receipt_detail' => [
            'vms_po_receipt_qty' => ['cpny_id', 'ponbr', 'receiptqty'],
        ],
        'orderheader' => [
            'vms_po_document_status' => ['CpnyID', 'SecVendorID', 'OrderNo', 'id', 'Status'],
        ],
        'orderheaderkontrak' => [
            'vms_kontrak_document_status' => ['id_company', 'second_vendor_id', 'id_kontrak', 'id', 'status'],
        ],
        'admin' => [
            'vms_po_purchaser' => ['idacum', 'id'],
        ],
    ];

    public function up(): void
    {
        $schema = Schema::connection('mysql7');
        foreach ($this->indexes as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                $existing = $schema->getIndexes($table);
                $sameName = collect($existing)->first(fn ($index) => $index['name'] === $name);
                if ($sameName && $sameName['columns'] !== $columns) {
                    throw new RuntimeException("Index {$name} on {$table} already exists with different columns.");
                }
                $covered = collect($existing)->contains(fn ($index) => array_slice($index['columns'], 0, count($columns)) === $columns);
                if (! $covered) {
                    $schema->table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
                }
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql7');
        foreach ($this->indexes as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if ($schema->hasIndex($table, $name)) {
                    $schema->table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
                }
            }
        }
    }
};
