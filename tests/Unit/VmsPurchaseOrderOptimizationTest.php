<?php
namespace Tests\Unit;

use App\Http\Controllers\Vms\PurchaseOrderController;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class VmsPurchaseOrderOptimizationTest extends TestCase
{
    private $previousApp;
    private $previousResolver;
    private Manager $database;
    private PurchaseOrderController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApp=Facade::getFacadeApplication();$this->previousResolver=Model::getConnectionResolver();
        $app=new Application(dirname(__DIR__,2));$app->instance('config',new Repository);
        $this->database=new Manager($app);$this->database->addConnection(['driver'=>'sqlite','database'=>':memory:','prefix'=>''],'mysql7');
        $app->instance('db',$this->database->getDatabaseManager());Model::setConnectionResolver($this->database->getDatabaseManager());
        $app->instance('validator',new Factory(new Translator(new ArrayLoader,'en'),$app));
        $app->instance(ResponseFactory::class,new class {
            public function view($name,$data){return new class($name,$data) {
                public array $headers=[];
                public function __construct(public string $name,public array $data){}
                public function header($key,$value){$this->headers[$key]=$value;return $this;}
            };}
        });
        Facade::clearResolvedInstances();Facade::setFacadeApplication($app);
        $db=$this->database->getConnection('mysql7');
        foreach([
            'CREATE TABLE company (CpnyID TEXT, CpnyName TEXT)',
            'CREATE TABLE staging_po (id INTEGER, cpny_id TEXT, vendor_id TEXT, vendorname TEXT, material_service TEXT, podate TEXT, ponbr TEXT, purchaser TEXT)',
            'CREATE TABLE staging_kontrak (id INTEGER, cpny_id TEXT, vendor_id TEXT, vendorname TEXT, kontrakdate TEXT, kontrakid TEXT, purchaser TEXT)',
            'CREATE TABLE admin (id INTEGER, idacum TEXT, name TEXT)',
            'CREATE TABLE staging_po_detail (cpny_id TEXT, ponbr TEXT, qty NUMERIC)',
            'CREATE TABLE staging_receipt_detail (cpny_id TEXT, ponbr TEXT, receiptqty NUMERIC)',
            'CREATE TABLE orderheader (id INTEGER, CpnyID TEXT, SecVendorID TEXT, OrderNo TEXT, Status TEXT)',
            'CREATE TABLE orderheaderkontrak (id INTEGER, id_company TEXT, second_vendor_id TEXT, id_kontrak TEXT, status TEXT)',
        ] as $sql)$db->statement($sql);
        $db->table('company')->insert([['CpnyID'=>'A','CpnyName'=>'Company A'],['CpnyID'=>'B','CpnyName'=>'Company B']]);
        $db->table('staging_po')->insert([
            ['id'=>1,'cpny_id'=>'A','vendor_id'=>'VV1','vendorname'=>'Vendor One','material_service'=>'Purchase Material','podate'=>'2025-01-01','ponbr'=>'PO1','purchaser'=>'P'],
            ['id'=>2,'cpny_id'=>'A','vendor_id'=>'VV2','vendorname'=>'Vendor Two','material_service'=>'Purchase Service','podate'=>'2025-01-01','ponbr'=>'SPK1','purchaser'=>'P'],
            ['id'=>3,'cpny_id'=>'B','vendor_id'=>'VV1','vendorname'=>'Vendor One','material_service'=>'Purchase Material','podate'=>'2025-01-01','ponbr'=>'PO2','purchaser'=>'P'],
            ['id'=>4,'cpny_id'=>'A','vendor_id'=>'VV1','vendorname'=>'Vendor One','material_service'=>'Purchase Material','podate'=>'2021-01-01','ponbr'=>'OLD','purchaser'=>'P'],
        ]);
        $db->table('staging_kontrak')->insert(['id'=>1,'cpny_id'=>'A','vendor_id'=>'VV2','vendorname'=>'Vendor Two','kontrakdate'=>'2025-01-01','kontrakid'=>'K1','purchaser'=>'P']);
        $this->controller=new PurchaseOrderController;
    }

    protected function tearDown(): void
    {
        $this->database->getDatabaseManager()->disconnect('mysql7');Facade::clearResolvedInstances();Facade::setFacadeApplication($this->previousApp);
        if($this->previousResolver)Model::setConnectionResolver($this->previousResolver);else Model::unsetConnectionResolver();
        Application::setInstance($this->previousApp);parent::tearDown();
    }

    private function request(array $params=[],bool $partial=false,bool $refresh=false): Request
    {
        $request=new class extends Request {
            public function validate(array $rules,...$args): array {return app('validator')->make($this->all(),$rules)->validate();}
        };
        $request->initialize($params,[],[],[],[],['REQUEST_METHOD'=>'GET','REQUEST_URI'=>'/vms/purchase-orders']);
        if($partial){$request->headers->set('X-Requested-With','XMLHttpRequest');$request->headers->set('X-Vms-Order-Partial','1');}
        if($refresh)$request->headers->set('X-Vms-Order-Refresh','1');
        $request->setUserResolver(fn()=>new class {public string $username='fixture';public function roles(){return ['admin'];}});
        return $request;
    }

    public function test_card_counts_match_previous_queries_with_filters_and_company_scope(): void
    {
        $base=new \ReflectionMethod($this->controller,'base');$base->setAccessible(true);
        $counts=new \ReflectionMethod($this->controller,'cardCounts');$counts->setAccessible(true);
        foreach([[],['A'],['A','B']] as $scope)foreach([[],['vendor'=>'VV1'],['company'=>'A'],['company'=>'B','vendor'=>'VV2']] as $filters){
            $expected=[];foreach(['po','spk','kontrak'] as $type)$expected[$type]=$base->invoke($this->controller,$type,$scope,$filters)->count();
            self::assertSame($expected,$counts->invoke($this->controller,$scope,$filters));
        }
    }

    public function test_partial_skips_vendor_query_and_refresh_fetches_it_again(): void
    {
        $db=$this->database->getConnection('mysql7');$db->enableQueryLog();$db->flushQueryLog();
        $partial=$this->controller->index($this->request(['type'=>'po'],true));
        self::assertSame('pages.vms.purchase-order.content',$partial->name);self::assertFalse($partial->data['loadVendorOptions']);
        self::assertCount(0,$partial->data['vendors']);self::assertSame(['po'=>2,'spk'=>1,'kontrak'=>1],$partial->data['counts']);
        self::assertFalse(collect($db->getQueryLog())->contains(fn($q)=>str_contains(strtolower($q['query']),'union')));
        self::assertSame('no-store, private',$partial->headers['Cache-Control']);
        $scope=$partial->data['scopeKey'];
        $refresh=$this->controller->index($this->request(['type'=>'spk'],true,true));
        self::assertTrue($refresh->data['loadVendorOptions']);self::assertCount(2,$refresh->data['vendors']);self::assertSame($scope,$refresh->data['scopeKey']);
        $full=$this->controller->index($this->request(['type'=>'kontrak']));
        self::assertSame('pages.vms.purchase-order.index',$full->name);self::assertTrue($full->data['loadVendorOptions']);
        $db->table('company')->where('CpnyID','B')->delete();
        $changed=$this->controller->index($this->request(['type'=>'po'],true));
        self::assertNotSame($scope,$changed->data['scopeKey']);self::assertSame(['po'=>1,'spk'=>1,'kontrak'=>1],$changed->data['counts']);
    }

    public function test_unpermitted_company_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->controller->index($this->request(['type'=>'po','company'=>'C'],true));
    }
}
