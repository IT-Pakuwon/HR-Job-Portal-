<?php

namespace Tests\Unit;

use App\Http\Controllers\Vms\VendorController;
use App\Services\Vms\VendorDirectory;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VmsVendorDirectoryTest extends TestCase
{
    private $previousApp;

    private $previousResolver;

    private Manager $database;

    private VendorController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApp = Facade::getFacadeApplication();
        $this->previousResolver = Model::getConnectionResolver();
        $app = new Application(dirname(__DIR__, 2));
        $app->instance('config', new Repository);
        $this->database = new Manager($app);
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'mysql7');
        $app->instance('db', $this->database->getDatabaseManager());
        Model::setConnectionResolver($this->database->getDatabaseManager());
        $factory = new Factory(new Translator(new ArrayLoader, 'en'), $app);
        $factory->setPresenceVerifier(new DatabasePresenceVerifier($this->database->getDatabaseManager()));
        $app->instance('validator', $factory);
        $app->instance('log', new class
        {
            public function notice(...$arguments): void {}
        });
        $app->instance(ResponseFactory::class, new class
        {
            public function json($data, $status = 200): JsonResponse
            {
                return new JsonResponse($data, $status);
            }
        });
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
        $schema = $this->database->getConnection('mysql7')->getSchemaBuilder();
        $schema->create('vendor', function ($table) {
            $table->increments('vendor_id');
            $table->string('vendor_nama');
            $table->integer('account_status');
            $table->integer('step');
            $table->string('data_status');
            $table->string('type')->nullable();
            $table->integer('vendor_category')->nullable();
            $table->integer('bidang_usaha')->nullable();
            foreach (['second_vendor_id', 'username', 'email', 'company_profile', 'created_at', 'password', 'cookie', 'procurement_approval', 'legal_approval', 'tax_approval', 'finance_approval'] as $column) {
                $table->string($column)->nullable();
            }
        });
        $schema->create('vendor_category_parent', function ($table) {
            $table->integer('id')->primary();
            $table->string('parent_category_name');
        });
        $schema->create('vendor_category', function ($table) {
            $table->integer('category_id')->primary();
            $table->string('category_name');
        });
        $schema->create('vendor_basic_information', function ($table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('phone1');
        });
        $schema->create('vendor_tag', function ($table) {
            $table->integer('id_vendor');
            $table->integer('id_tag');
        });
        $schema->create('vendor_document', function ($table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->integer('document_id');
            $table->string('status');
        });
        $schema->create('master_document', function ($table) {
            $table->integer('document_id')->primary();
            $table->string('devision_id');
            $table->integer('status');
        });
        $db = $this->database->getConnection('mysql7');
        $db->table('vendor_category_parent')->insert([['id' => 1, 'parent_category_name' => 'Teknik'], ['id' => 2, 'parent_category_name' => 'Non Teknik']]);
        $db->table('vendor_category')->insert([['category_id' => 10, 'category_name' => 'Maintenance'], ['category_id' => 20, 'category_name' => 'Service']]);
        app('config')->set('vms.attachments_url', 'https://example.com/attachments');
        $this->controller = new VendorController;
    }

    protected function tearDown(): void
    {
        if ($this->previousResolver) {
            Model::setConnectionResolver($this->previousResolver);
        } else {
            Model::unsetConnectionResolver();
        }
        $this->database->getConnection('mysql7')->disconnect();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousApp);
        Application::setInstance($this->previousApp);
        parent::tearDown();
    }

    private function request(array $data = [], bool $admin = true): Request
    {
        $request = new class extends Request
        {
            public function validate(array $rules, ...$arguments): array
            {
                return app('validator')->make($this->all(), $rules)->validate();
            }
        };
        $request->replace($data);
        $request->setUserResolver(fn () => new class($admin)
        {
            public string $username = 'test-admin';

            public function __construct(private bool $admin) {}

            public function roles(): array
            {
                return [$this->admin ? 'admin' : 'finance'];
            }
        });

        return $request;
    }

    private function vendor(array $extra = []): int
    {
        return $this->database->getConnection('mysql7')->table('vendor')->insertGetId(array_replace(['vendor_nama' => 'Example Vendor', 'account_status' => 1, 'step' => 1, 'data_status' => 'inprogress', 'type' => 'normal', 'vendor_category' => 1, 'bidang_usaha' => 10, 'password' => 'private-hash', 'cookie' => 'private-cookie'], $extra));
    }

    public function test_tab_predicates_match_ci3_and_do_not_overlap(): void
    {
        $this->vendor(['account_status' => 0]);
        $this->vendor(['account_status' => 0, 'type' => 'manual']);
        $this->vendor(['account_status' => 0, 'type' => null]);
        $this->vendor(['step' => 4, 'data_status' => 'revise']);
        $this->vendor(['step' => 5]);
        $this->vendor(['step' => 6, 'data_status' => 'revise']);
        $master = $this->vendor(['step' => 5, 'data_status' => 'approved']);
        $this->database->getConnection('mysql7')->table('vendor_basic_information')->insert(['vendor_id' => $master, 'phone1' => '123']);
        $this->vendor(['step' => 5, 'data_status' => 'approved']);
        $this->vendor(['account_status' => 2]);
        $this->vendor(['account_status' => 3, 'step' => 5, 'data_status' => 'approved']);
        $this->vendor(['bidang_usaha' => 999]);
        $directory = new VendorDirectory;
        $this->assertSame(['new' => 1, 'incomplete' => 1, 'review' => 2, 'master' => 1], $directory->counts());
        $ids = [];
        foreach (array_keys(VendorDirectory::TABS) as $tab) {
            foreach ($directory->query($tab)->pluck('v.vendor_id') as $id) {
                $this->assertNotContains($id, $ids);
                $ids[] = $id;
            }
        }
    }

    public function test_filters_and_master_tags_do_not_duplicate_rows(): void
    {
        $a = $this->vendor(['step' => 5, 'data_status' => 'approved']);
        $b = $this->vendor(['step' => 5, 'data_status' => 'approved', 'vendor_category' => 2, 'bidang_usaha' => 20, 'type' => 'manual']);
        $db = $this->database->getConnection('mysql7');
        $db->table('vendor_basic_information')->insert([['vendor_id' => $a, 'phone1' => '111'], ['vendor_id' => $a, 'phone1' => '222'], ['vendor_id' => $b, 'phone1' => '333']]);
        $db->table('vendor_tag')->insert([['id_vendor' => $a, 'id_tag' => 1], ['id_vendor' => $a, 'id_tag' => 1]]);
        $directory = new VendorDirectory;
        $this->assertCount(2, $directory->rows('master'));
        $this->assertCount(1, $directory->rows('master', ['tag' => 1]));
        $this->assertCount(0, $directory->rows('master', ['category' => 1, 'business' => 20]));
        $this->assertSame($b, $directory->rows('master', ['type' => 'manual'])->first()->vendor_id);
        $this->assertSame('222', $directory->rows('master', ['category' => 1])->first()->phone1);
        $row = $directory->rows('master')->first()->toArray();
        $this->assertArrayNotHasKey('password', $row);
        $this->assertArrayNotHasKey('cookie', $row);
    }

    public function test_review_counts_and_safe_file_urls(): void
    {
        $id = $this->vendor(['step' => 5, 'company_profile' => 'example file.pdf']);
        $db = $this->database->getConnection('mysql7');
        $db->table('master_document')->insert([['document_id' => 1, 'devision_id' => 'tax', 'status' => 1], ['document_id' => 2, 'devision_id' => 'procurement', 'status' => 1], ['document_id' => 3, 'devision_id' => 'tax', 'status' => 0], ['document_id' => 4, 'devision_id' => 'finance', 'status' => 1]]);
        foreach ([[1, 'rejected'], [1, 'rejected'], [2, 'rejected'], [2, 'waiting_review'], [2, 'empty'], [2, 'approved'], [3, 'rejected'], [4, 'waiting_review'], [4, 'empty']] as [$doc,$status]) {
            $db->table('vendor_document')->insert(['vendor_id' => $id, 'document_id' => $doc, 'status' => $status]);
        }
        // A different vendor's documents must not affect this vendor's counts.
        $db->table('vendor_document')->insert(['vendor_id' => 999, 'document_id' => 1, 'status' => 'rejected']);
        $db->enableQueryLog();
        $directory = new VendorDirectory;
        $row = $directory->rows('review')->first();
        $this->assertSame(2, $row->tax_rejected);
        $this->assertSame(3, $row->proc_pending);
        $this->assertSame(1, $row->treasury_waitingreview);
        $this->assertSame(1, $row->treasury_request);
        $this->assertSame(0, $row->legal_pending);
        $queries = $db->getQueryLog();
        $db->disableQueryLog();
        $this->assertCount(2, $queries);
        foreach ($queries as $query) {
            $this->assertStringNotContainsString('view_document', $query['query']);
        }
        $this->assertSame('https://example.com/attachments/company_profile/example%20file.pdf', $row->profile_url);
        $this->assertNull($directory->fileUrl('doc/1', '../secret.pdf'));
        $this->assertNull($directory->fileUrl('doc/1', 'https://bad.example/file'));
        $this->assertNull($directory->fileUrl('doc/1', null));
        try {
            $directory->query('arbitrary');
            $this->fail('Expected invalid tab');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public function test_incomplete_requirements_include_missing_documents_and_conditional_rules(): void
    {
        $id = $this->vendor();
        $db = $this->database->getConnection('mysql7');
        $schema = $db->getSchemaBuilder();
        $schema->table('vendor_basic_information', function ($table) {
            foreach (['company_name', 'attention', 'email', 'email2', 'website', 'phone2', 'fax', 'address1', 'address2', 'country', 'state', 'city', 'postal_code', 'tanggal_berdiri', 'badan_usaha'] as $field) {
                $table->string($field)->nullable();
            }
        });
        $schema->create('badan_usaha', function ($table) {
            $table->string('BadanID');
            $table->string('Description');
        });
        $schema->create('vendor_npwp', function ($table) {
            $table->increments('id');
            $table->integer('vendor_id');
            foreach (['npwp_name', 'npwp_number', 'npwp_address'] as $field) {
                $table->string($field)->nullable();
            }foreach (['shipper', 'payment'] as $prefix) {
                foreach (['company_name', 'attention', 'email', 'website', 'phone1', 'phone2', 'fax', 'address1', 'address2', 'city', 'country', 'state', 'postal_code'] as $field) {
                    $table->string($prefix.'_'.$field)->nullable();
                }
            }
        });
        $schema->table('master_document', function ($table) {
            $table->string('document_name')->nullable();
            $table->string('document_remark')->nullable();
            $table->string('has_conditional');
            $table->string('conditional_date')->nullable();
            $table->integer('document_order');
        });
        $schema->create('setup_document', function ($table) {
            $table->integer('document_id');
            $table->integer('category_id');
            $table->integer('is_published');
            $table->integer('is_mandatory');
        });
        $db->table('vendor_basic_information')->insert(['vendor_id' => $id, 'phone1' => '123', 'tanggal_berdiri' => '2010-01-01']);
        foreach ([1, 2, 3] as $doc) {
            $db->table('master_document')->insert(['document_id' => $doc, 'devision_id' => 'legal', 'document_name' => 'Document '.$doc, 'has_conditional' => $doc === 2 ? 'Y' : 'N', 'conditional_date' => '2007-08-15', 'document_order' => $doc, 'status' => 1]);
            $db->table('setup_document')->insert(['document_id' => $doc, 'category_id' => 10, 'is_published' => $doc === 3 ? 2 : 1, 'is_mandatory' => 1]);
        }
        $directory = new class extends VendorDirectory
        {
            public function detail(int $id): array
            {
                return ['documents' => collect([(object) ['document_id' => 2, 'file_url' => 'https://example.test/2.pdf'], (object) ['document_id' => 2, 'file_url' => 'https://example.test/3.pdf'], (object) ['document_id' => 99, 'file_url' => null]])];
            }
        };
        $data = $directory->incomplete($id);
        $this->assertCount(2, $data['requirements']);
        $this->assertTrue($data['requirements'][0]->mandatory);
        $this->assertCount(0, $data['requirements'][0]->uploads);
        $this->assertFalse($data['requirements'][1]->mandatory);
        $this->assertCount(2, $data['requirements'][1]->uploads);
        $this->assertCount(1, $data['additionalDocuments']);
        $db->table('vendor')->where('vendor_id', $id)->update(['step' => 5]);
        try {
            $directory->incomplete($id);
            $this->fail('Expected stale-stage rejection');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
    }

    public function test_endpoints_require_authenticated_user(): void
    {
        $request = $this->request();
        $request->setUserResolver(fn () => null);
        foreach ([fn () => $this->controller->index($request, new VendorDirectory), fn () => $this->controller->json($request, new VendorDirectory, 'new'), fn () => $this->controller->show($request, new VendorDirectory, 1)] as $action) {
            try {
                $action();
                $this->fail('Expected forbidden');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
    }
}
