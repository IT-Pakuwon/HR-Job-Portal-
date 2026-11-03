<?php
namespace Tests\Unit;
use App\Http\Controllers\Vms\PurchaseOrderController;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
class VmsPurchaseOrderTest extends TestCase {
    private $previousApp; private $previousResolver; private Manager $database;
    protected function setUp(): void {
        parent::setUp();$this->previousApp=Facade::getFacadeApplication();$this->previousResolver=Model::getConnectionResolver();
        $app=new Application(dirname(__DIR__,2));$app->instance('config',new Repository);$this->database=new Manager($app);
        $this->database->addConnection(['driver'=>'sqlite','database'=>':memory:','prefix'=>''],'mysql7');
        $app->instance('db',$this->database->getDatabaseManager());Model::setConnectionResolver($this->database->getDatabaseManager());Facade::clearResolvedInstances();Facade::setFacadeApplication($app);
        $db=$this->database->getConnection('mysql7');
        $db->statement('CREATE TABLE staging_po (id INTEGER, cpny_id TEXT, vendor_id TEXT, material_service TEXT, podate TEXT)');
        $db->statement('CREATE TABLE staging_kontrak (id INTEGER, cpny_id TEXT, vendor_id TEXT)');
        $db->table('staging_po')->insert([
            ['id'=>1,'cpny_id'=>'A','vendor_id'=>'VV1','material_service'=>'Purchase Material','podate'=>'2024-01-01'],
            ['id'=>2,'cpny_id'=>'B','vendor_id'=>'VV1','material_service'=>'Purchase Material','podate'=>'2024-01-01'],
            ['id'=>3,'cpny_id'=>'A','vendor_id'=>'VV2','material_service'=>'Purchase Service','podate'=>'2024-01-01'],
            ['id'=>4,'cpny_id'=>'A','vendor_id'=>'VV1','material_service'=>'Purchase Material','podate'=>'2021-01-01'],
        ]);
        $db->table('staging_kontrak')->insert([['id'=>5,'cpny_id'=>'A','vendor_id'=>'VV2'],['id'=>6,'cpny_id'=>'B','vendor_id'=>'VV2']]);
    }
    protected function tearDown(): void {
        $this->database->getDatabaseManager()->disconnect('mysql7');Facade::clearResolvedInstances();Facade::setFacadeApplication($this->previousApp);if($this->previousResolver)Model::setConnectionResolver($this->previousResolver);else Model::unsetConnectionResolver();parent::tearDown();
    }
    private function rows(string $type,array $companies,array $filters=[]): array {
        $method=new \ReflectionMethod(PurchaseOrderController::class,'base');$method->setAccessible(true);
        return $method->invoke(new PurchaseOrderController,$type,$companies,$filters)->pluck('id')->all();
    }
    public function test_types_company_scope_and_ci3_date_cutoff(): void {
        self::assertSame([1],$this->rows('po',['A']));self::assertSame([3],$this->rows('spk',['A']));self::assertSame([5],$this->rows('kontrak',['A']));
        self::assertSame([],$this->rows('po',[]));self::assertSame([],$this->rows('kontrak',[]));
    }
    public function test_filters_cannot_escape_allowed_companies(): void {
        self::assertSame([],$this->rows('po',['A'],['company'=>'B']));self::assertSame([],$this->rows('po',['A'],['vendor'=>'VV2']));
        self::assertSame([3],$this->rows('spk',['A'],['vendor'=>'VV2']));self::assertSame([],$this->rows('kontrak',['A'],['company'=>'B']));
    }
}
