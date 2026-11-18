<?php

namespace Tests\Unit;

use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class VmsPurchaseOrderIndexesTest extends TestCase
{
    private $previousApp;
    private Manager $database;
    private $migration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApp = Facade::getFacadeApplication();
        $app = new Application(dirname(__DIR__, 2));
        $app->instance('config', new Repository);
        $this->database = new Manager($app);
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'mysql7');
        $app->instance('db', $this->database->getDatabaseManager());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
        $db = $this->database->getConnection('mysql7');
        $db->statement('CREATE TABLE staging_po (id INTEGER, cpny_id TEXT, vendor_id TEXT, material_service TEXT, podate TEXT, extra TEXT)');
        $db->statement('CREATE TABLE staging_kontrak (id INTEGER, cpny_id TEXT, vendor_id TEXT, kontrakdate TEXT)');
        $db->statement('CREATE TABLE staging_receipt_detail (id INTEGER, cpny_id TEXT, ponbr TEXT, receiptqty NUMERIC)');
        $db->statement('CREATE TABLE orderheader (id INTEGER, CpnyID TEXT, SecVendorID TEXT, OrderNo TEXT, Status TEXT)');
        $db->statement('CREATE TABLE orderheaderkontrak (id INTEGER, id_company TEXT, second_vendor_id TEXT, id_kontrak TEXT, status TEXT)');
        $db->statement('CREATE TABLE admin (id INTEGER, idacum TEXT)');
        $this->migration = require dirname(__DIR__, 2).'/database/migrations/2026_11_18_150000_add_vms_purchase_order_indexes.php';
    }

    protected function tearDown(): void
    {
        $this->database->getDatabaseManager()->disconnect('mysql7');
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousApp);
        Application::setInstance($this->previousApp);
        parent::tearDown();
    }

    public function test_migration_can_be_repeated_and_rolled_back(): void
    {
        $this->migration->up();
        $this->migration->up();
        $schema = Schema::connection('mysql7');
        self::assertTrue($schema->hasIndex('staging_po', ['material_service', 'podate', 'id', 'cpny_id']));
        self::assertTrue($schema->hasIndex('staging_receipt_detail', ['cpny_id', 'ponbr', 'receiptqty']));
        self::assertTrue($schema->hasIndex('orderheader', ['CpnyID', 'SecVendorID', 'OrderNo', 'id', 'Status']));
        self::assertTrue($schema->hasIndex('orderheaderkontrak', ['id_company', 'second_vendor_id', 'id_kontrak', 'id', 'status']));
        $this->migration->down();
        self::assertFalse($schema->hasIndex('staging_po', 'vms_po_listing'));
        self::assertFalse($schema->hasIndex('staging_receipt_detail', 'vms_po_receipt_qty'));
    }

    public function test_existing_covering_index_is_preserved(): void
    {
        $this->database->getConnection('mysql7')->statement('CREATE INDEX existing_po_listing ON staging_po (material_service, podate, id, cpny_id, extra)');
        $this->migration->up();
        $schema = Schema::connection('mysql7');
        self::assertTrue($schema->hasIndex('staging_po', 'existing_po_listing'));
        self::assertFalse($schema->hasIndex('staging_po', 'vms_po_listing'));
        $this->migration->down();
        self::assertTrue($schema->hasIndex('staging_po', 'existing_po_listing'));
    }

    public function test_conflicting_index_name_reports_an_error(): void
    {
        $this->database->getConnection('mysql7')->statement('CREATE INDEX vms_po_listing ON staging_po (vendor_id)');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already exists with different columns');
        $this->migration->up();
    }
}
