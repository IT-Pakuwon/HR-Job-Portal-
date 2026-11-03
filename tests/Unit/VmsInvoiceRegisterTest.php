<?php
namespace Tests\Unit;
use App\Services\Vms\InvoiceRegisterDirectory;
use App\Services\Vms\InvoiceRegisterWorkflow;
use App\Services\Vms\RegistrationEmail;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
class VmsInvoiceRegisterTest extends TestCase {
    private $previousApp;private $previousResolver;private Manager $database;private InvoiceRegisterDirectory $directory;private InvoiceRegisterWorkflow $workflow;
    protected function setUp():void {
        parent::setUp();$this->previousApp=Facade::getFacadeApplication();$this->previousResolver=Model::getConnectionResolver();$app=new Application(dirname(__DIR__,2));
        $app->instance('validator',new \Illuminate\Validation\Factory(new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader,'en'),$app));
        $app->instance('config',new Repository(['vms'=>['attachments_url'=>'https://example.com/files']]));$this->database=new Manager($app);$this->database->addConnection(['driver'=>'sqlite','database'=>':memory:','prefix'=>''],'mysql7');$app->instance('db',$this->database->getDatabaseManager());Model::setConnectionResolver($this->database->getDatabaseManager());Facade::clearResolvedInstances();Facade::setFacadeApplication($app);
        $app->instance(RegistrationEmail::class,new class extends RegistrationEmail {public function enqueue(string $activity,string $event,array $values,array $payload):?int{return null;}public function deliver(?int $id):void{}});
        $db=$this->database->getConnection('mysql7');
        foreach(json_decode(file_get_contents(dirname(__DIR__).'/Fixtures/vms_invoice_schema.json'),true) as $table=>$columns){$fields=[];foreach($columns as $column)$fields[]='"'.$column.'" '.($column==='id'?'INTEGER PRIMARY KEY AUTOINCREMENT':($column==='vendor_id'&&$table==='vendor'?'INTEGER':'TEXT'));$db->statement('CREATE TABLE "'.$table.'" ('.implode(',',$fields).')');}
        $db->table('vendor')->insert(['vendor_id'=>1,'vendor_nama'=>'Example Vendor','second_vendor_id'=>'VV1']);
        $db->table('ord_masterdoc')->insert([['id'=>1,'DivisionID'=>'tax','document_name'=>'Invoice Tax'],['id'=>2,'DivisionID'=>'finance','document_name'=>'Invoice Finance']]);
        $this->seed('po',1,'A','IR1');$this->seed('kontrak',1,'A','IRK1');$this->seed('po',2,'B','IR2');
        $this->directory=new InvoiceRegisterDirectory;$this->workflow=new InvoiceRegisterWorkflow;
    }
    private function seed(string $kind,int $id,string $company,string $number):void {
        $db=$this->database->getConnection('mysql7');$contract=$kind==='kontrak';
        $db->table($contract?'ordconfirmhdrkontrak':'ordconfirmhdr')->insert($contract?['id'=>$id,'id_company'=>$company,'ord_conf_id'=>$number,'ord_conf_date'=>'2026-11-03','id_kontrak'=>'K'.$id,'id_vendor'=>1,'second_vendor_id'=>'VV1','module'=>'Kontrak','status'=>'P','step'=>5,'proforma_status_approval'=>'not_send']:['id'=>$id,'CpnyID'=>$company,'OrdConfID'=>$number,'OrdConfDate'=>'2026-11-03','OrderNo'=>'PO'.$id,'VendorID'=>1,'VendorName'=>'Example Vendor','SecVendorID'=>'VV1','Module'=>'Purchase Material','Status'=>'P','step'=>5,'ProformaStatusApproval'=>'not_send']);
        foreach([1,2] as $doc)$db->table($contract?'ordconfirmattachkontrak':'ordconfirmattach')->insert($contract?['id_ordconfirmhdr'=>$id,'id_company'=>$company,'ord_conf_id'=>$number,'id_kontrak'=>'K'.$id,'vendor_id'=>1,'master_doc_id'=>$doc,'filename'=>'example.pdf','approver_status'=>'P']:['id_ordconfirmhdr'=>$id,'CpnyID'=>$company,'OrdConfID'=>$number,'OrderNo'=>'PO'.$id,'vendor_id'=>1,'MasterDocID'=>$doc,'Filename'=>'example.pdf','ApproverStatus'=>'P']);
        $db->table($contract?'ordconfirmtaxinvkontrak':'ordconfirmtaxinv')->insert(['id_ordconfirmhdr'=>$id,'CpnyID'=>$company,'OrdConfID'=>$number,'VendorID'=>1,'NoFaktur'=>'F'.$kind.$id,'ApproverStatus'=>'P']);
        if($contract)$db->table('ordconfirmkontrak')->insert(['id_ordconfirmhdrkontrak'=>$id,'status'=>'C']);
        if(!$contract)$db->table('ordconfirmreceipt')->insert(['id_ordconfirmhdr'=>$id,'Status'=>'P']);
    }
    protected function tearDown():void {$this->database->getDatabaseManager()->disconnect('mysql7');Facade::clearResolvedInstances();Facade::setFacadeApplication($this->previousApp);if($this->previousResolver)Model::setConnectionResolver($this->previousResolver);else Model::unsetConnectionResolver();parent::tearDown();}
    private function actor(string $dept):array{return ['id'=>7,'all'=>false,'departments'=>[$dept],'username'=>'reviewer'];}
    private function detail(string $kind='po',int $id=1):array {return $this->directory->detail($this->directory->find(['A'],$kind,$id));}
    private function act(string $action,string $dept,array $extra=[],string $kind='po'):void {$detail=$this->detail($kind);$this->workflow->decide($this->directory,['A'],$kind,1,$this->actor($dept),['action'=>$action,'token'=>$detail['token']]+$extra);}
    private function approveAll(string $kind='po'):void {foreach($this->detail($kind)['documents'] as $doc)$this->act('approve',$doc->department,['document'=>$doc->id],$kind);$this->act('approve','tax',['tax_invoice'=>'F'.$kind.'1'],$kind);}
    public function test_company_scope_modules_and_card_statuses():void {
        self::assertSame(2,$this->directory->query(['A'])->count());self::assertSame(0,$this->directory->query([])->count());self::assertSame(2,$this->directory->filter($this->directory->query(['A']),'review')->count());self::assertSame(0,$this->directory->filter($this->directory->query(['A']),'completed')->count());self::assertSame(0,$this->directory->filter($this->directory->query(['A']),'review',['company'=>'B'])->count());
        $db=$this->directory->db();$db->table('staging_po')->insert(['ponbr'=>'PO1','cpny_id'=>'A','business_unit_id'=>'UNIT1']);self::assertSame(1,$this->directory->filter($this->directory->query(['A']),'review',['business_unit'=>'UNIT1'])->count());
    }
    public function test_document_tax_final_and_physical_receipt_flow_for_both_kinds():void {
        foreach(['po','kontrak'] as $kind){$this->approveAll($kind);self::assertSame('A',$this->detail($kind)['invoice']->status);$this->act('final','finance',[],$kind);self::assertSame('C',$this->detail($kind)['invoice']->status);$this->act('receive','finance',[],$kind);self::assertSame('approve',$this->detail($kind)['invoice']->send_status);$this->act('return','finance',['note'=>'Original invoice missing'],$kind);$detail=$this->detail($kind);self::assertSame('return',$detail['invoice']->send_status);self::assertTrue($detail['documents']->every(fn($d)=>$d->status_receive==='return'));self::assertCount(6,$detail['history']);}
        self::assertSame('C',$this->directory->db()->table('ordconfirmreceipt')->where('id_ordconfirmhdr',1)->value('Status'));
    }
    public function test_tax_cannot_approve_finance_documents_and_transaction_is_unchanged():void {
        $document=$this->detail()['documents']->firstWhere('department','finance');try{$this->act('approve','tax',['document'=>$document->id]);self::fail('Forbidden');}catch(HttpException $e){self::assertSame(403,$e->getStatusCode());}self::assertSame('P',$this->detail()['documents']->firstWhere('id',$document->id)->status);self::assertCount(0,$this->detail()['history']);
    }
    public function test_final_before_documents_complete_is_rejected():void {try{$this->act('final','finance');self::fail('Not ready');}catch(HttpException $e){self::assertSame(409,$e->getStatusCode());}self::assertSame('P',$this->detail()['invoice']->status);}
    public function test_stale_token_cannot_repeat_a_review():void {
        $detail=$this->detail();$document=$detail['documents']->first();$this->act('approve','tax',['document'=>$document->id]);try{$this->workflow->decide($this->directory,['A'],'po',1,$this->actor('tax'),['action'=>'approve','document'=>$document->id,'token'=>$detail['token']]);self::fail('Stale');}catch(HttpException $e){self::assertSame(409,$e->getStatusCode());}self::assertCount(1,$this->detail()['history']);
    }
    public function test_revise_requires_note_and_moves_invoice_out_of_reviewable_state():void {
        $document=$this->detail()['documents']->first();try{$this->act('revise','tax',['document'=>$document->id]);self::fail('Missing note');}catch(ValidationException $e){self::assertArrayHasKey('note',$e->errors());}$this->act('revise','tax',['document'=>$document->id,'note'=>'Please replace scan']);self::assertSame('D',$this->detail()['invoice']->status);self::assertSame('D',$this->detail()['documents']->first()->status);
    }
    public function test_file_paths_are_scoped_and_cannot_traverse():void {$invoice=$this->detail()['invoice'];self::assertSame('https://example.com/files/ordattach/PO1/example.pdf',$this->directory->fileUrl($invoice,'example.pdf'));self::assertNull($this->directory->fileUrl($invoice,'../secret'));self::assertNull($this->directory->fileUrl($invoice,'https://example.com/file'));}
    public function test_foreign_company_document_cannot_be_reviewed():void {
        $foreign=$this->directory->db()->table('ordconfirmattach')->where('CpnyID','B')->value('id');try{$this->act('approve','tax',['document'=>$foreign]);self::fail('Wrong invoice');}catch(HttpException $e){self::assertSame(404,$e->getStatusCode());}self::assertSame('P',$this->directory->db()->table('ordconfirmattach')->where('id',$foreign)->value('ApproverStatus'));
    }
    public function test_scan_search_and_statistics_keep_company_and_kind_scopes():void {
        self::assertSame(0,$this->directory->filter($this->directory->query(['A']),'scan',['search'=>'IR2'])->count());self::assertSame(1,$this->directory->filter($this->directory->query(['A']),'scan',['search'=>'IRK1'])->count());
        $rows=$this->directory->query(['A'])->paginate(10);$stats=$this->directory->statistics($rows);self::assertSame(1,$stats['po:1']['tax']['W']);self::assertSame(1,$stats['kontrak:1']['finance']['W']);
    }
    public function test_pending_contract_user_approval_is_separate_from_department_review():void {
        $this->directory->db()->table('ordconfirmkontrak')->update(['status'=>'P']);self::assertSame(1,$this->directory->filter($this->directory->query(['A']),'review')->count());self::assertSame(1,$this->directory->filter($this->directory->query(['A']),'review',['status'=>'user_inprogress'])->count());
        $doc=$this->detail('kontrak')['documents']->first();try{$this->act('approve','tax',['document'=>$doc->id],'kontrak');self::fail('User review pending');}catch(HttpException $e){self::assertSame(409,$e->getStatusCode());}
    }

    public function test_optimized_listing_preserves_filter_results_and_pagination():void {
        $this->directory->db()->table('staging_po')->insert(['ponbr'=>'PO1','cpny_id'=>'A','business_unit_id'=>'UNIT1']);
        foreach(['review','completed','scan'] as $tab)foreach([[],['status'=>'all'],['status'=>'P'],['status'=>'user_inprogress'],['vendor'=>1],['company'=>'B'],['business_unit'=>'UNIT1'],['search'=>'IRK1']] as $filters){
            if($tab==='completed'&&isset($filters['status'])&&$filters['status']!=='all')continue;
            $baseline=$this->directory->filter($this->directory->query(['A']),$tab,$filters)->orderByDesc('date')->orderByDesc('id')->orderBy('kind')->get()->map(fn($r)=>$r->kind.':'.$r->id)->all();
            $page=$this->directory->paginate(['A'],$tab,$filters,1,1);self::assertSame(count($baseline),$page->total());self::assertSame(array_slice($baseline,0,1),$page->getCollection()->map(fn($r)=>$r->kind.':'.$r->id)->all());
            if(count($baseline)>1)self::assertSame(array_slice($baseline,1,1),$this->directory->paginate(['A'],$tab,$filters,1,2)->getCollection()->map(fn($r)=>$r->kind.':'.$r->id)->all());
        }
        self::assertSame(['review'=>2,'completed'=>0],$this->directory->counts(['A']));self::assertSame([1],$this->directory->vendors(['A'])->pluck('vendor_id')->all());self::assertSame(0,$this->directory->paginate(['A'],'scan',[],10,999999999999)->count());
    }

}
