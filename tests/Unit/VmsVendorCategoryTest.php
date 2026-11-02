<?php

namespace Tests\Unit;

use App\Http\Controllers\Vms\VendorCategoryController;
use App\Models\Vms\VendorBusinessCategory;
use App\Models\Vms\VendorCategory;
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
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VmsVendorCategoryTest extends TestCase
{
    private $previousApp;

    private $previousResolver;

    private Manager $database;

    private VendorCategoryController $controller;

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
        $schema->create('vendor_category_parent', function ($table) {
            $table->increments('id');
            $table->string('parent_category_name');
            $table->string('ClassID');
            $table->integer('status');
            $table->dateTime('created_at');
        });
        $schema->create('vendor_category', function ($table) {
            $table->increments('category_id');
            $table->string('category_name');
            $table->integer('parent_category');
            $table->integer('status');
            $table->dateTime('created_at');
        });
        $this->controller = new VendorCategoryController;
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

    private function category(array $extra = []): array
    {
        return array_replace(['parent_category_name' => 'Teknik', 'ClassID' => 'TEKNIK', 'status' => 1], $extra);
    }

    private function business(array $extra = []): array
    {
        return array_replace(['category_name' => 'Maintenance', 'parent_category' => 1, 'status' => 1], $extra);
    }

    public function test_business_lists_are_filtered_by_parent(): void
    {
        $this->controller->store($this->request($this->category()), 'category');
        $this->controller->store($this->request($this->category(['parent_category_name' => 'Non Teknik'])), 'category');
        $this->controller->store($this->request($this->business()), 'business');
        $this->controller->store($this->request($this->business(['parent_category' => 2])), 'business');
        $rows = $this->controller->json($this->request(['parent' => 1]), 'business')->getData(true)['data'];
        $this->assertCount(1, $rows);
        $this->assertSame(1, $rows[0]['parent_category']);
    }

    public function test_setup_document_mapping_flags_duplicates_and_context(): void
    {
        $schema = $this->database->getConnection('mysql7')->getSchemaBuilder();
        $schema->create('master_document', function ($table) {
            $table->increments('document_id'); $table->string('document_name'); $table->integer('status');
        });
        $schema->create('setup_document', function ($table) {
            $table->increments('id'); $table->integer('category_id'); $table->integer('document_id');
            $table->integer('is_mandatory'); $table->integer('is_published'); $table->dateTime('created_date');
        });
        $this->controller->store($this->request($this->category()), 'category');
        $this->controller->store($this->request($this->category(['parent_category_name' => 'Other'])), 'category');
        $this->controller->store($this->request($this->business()), 'business');
        \App\Models\Vms\Document::create(['document_name' => 'NIB', 'status' => 1]);
        $controller = new \App\Http\Controllers\Vms\SetupDocumentController;
        $this->assertSame(201, $controller->store($this->request(['document_id' => 1]), 1, 1)->getStatusCode());
        $json = $controller->json($this->request(), 1, 1)->getData(true);
        $this->assertCount(1, $json['data']);
        $this->assertEmpty($json['documents']);
        $this->assertSame(1, $json['data'][0]['is_mandatory']);
        $created = $json['data'][0]['created_date'];
        $controller->update($this->request(['field' => 'is_published', 'value' => 2, 'expected' => 1]), 1, 1, 1);
        $row = \App\Models\Vms\SetupDocument::findOrFail(1);
        $this->assertSame(2, $row->is_published);
        $this->assertSame($created, $row->created_date);
        foreach ([
            fn () => $controller->store($this->request(['document_id' => 1]), 1, 1),
            fn () => $controller->update($this->request(['field' => 'is_published', 'value' => 1, 'expected' => 1]), 1, 1, 1),
            fn () => $controller->json($this->request([], false), 1, 1),
        ] as $index => $action) {
            try { $action(); $this->fail('Expected rejection'); }
            catch (HttpException $e) { $this->assertSame([422, 409, 403][$index], $e->getStatusCode()); }
        }
        try { $controller->json($this->request(), 2, 1); $this->fail('Expected wrong parent rejection'); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { $this->assertTrue(true); }
    }

    public function test_category_create_edit_read_and_generated_id(): void
    {
        $result = $this->controller->store($this->request($this->category(['id' => 999, 'created_at' => '2000-01-01'])), 'category');
        $this->assertSame(201, $result->getStatusCode());
        $this->assertSame(1, $result->getData(true)['data']['id']);
        $created = VendorCategory::find(1)->created_at;
        $this->assertNotSame('2000-01-01', $created->format('Y-m-d'));
        $this->controller->update($this->request($this->category(['parent_category_name' => 'Engineering', 'ClassID' => 'ENG', 'id' => 99])), 'category', 1);
        $this->assertNull(VendorCategory::find(99));
        $this->assertSame('Engineering', VendorCategory::find(1)->parent_category_name);
        $this->assertTrue($created->equalTo(VendorCategory::find(1)->created_at));
        $this->assertSame('ENG', $this->controller->edit($this->request(), 'category', 1)->getData(true)['ClassID']);
        $this->assertCount(1, $this->controller->json($this->request(), 'category')->getData(true)['data']);
    }

    public function test_business_create_and_list_resolve_parent_names(): void
    {
        $this->controller->store($this->request($this->category()), 'category');
        $this->controller->store($this->request($this->business()), 'business');
        $result = $this->controller->json($this->request(), 'business')->getData(true);
        $this->assertSame('Teknik', $result['data'][0]['parent_name']);
        $this->assertSame(1, $result['data'][0]['parent_category']);
        $this->assertCount(1, $result['parents']);
        $this->controller->update($this->request($this->business(['category_name' => 'Service'])), 'business', 1);
        $this->assertSame('Service', VendorBusinessCategory::find(1)->category_name);
    }

    public function test_duplicate_names_and_invalid_fields_are_rejected(): void
    {
        $this->controller->store($this->request($this->category()), 'category');
        foreach ([[], ['status' => 7], ['ClassID' => str_repeat('X', 11)], ['parent_category_name' => str_repeat('X', 51)]] as $invalid) {
            try {
                $this->controller->store($this->request($this->category($invalid)), 'category');
                $this->fail('Invalid category must be rejected');
            } catch (ValidationException $error) {
                $this->assertNotEmpty($error->errors());
            }
        }
        $this->controller->store($this->request($this->business()), 'business');
        try {
            $this->controller->store($this->request($this->business()), 'business');
            $this->fail('Duplicate business must be rejected');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('category_name', $error->errors());
        }
        $this->controller->store($this->request($this->category(['parent_category_name' => 'Non Teknik'])), 'category');
        $this->controller->store($this->request($this->business(['parent_category' => 2])), 'business');
        $this->assertSame(2, VendorBusinessCategory::count());
    }

    public function test_inactive_parent_cannot_be_newly_assigned_but_existing_relation_can_be_edited(): void
    {
        $this->controller->store($this->request($this->category()), 'category');
        $this->controller->store($this->request($this->business()), 'business');
        $this->controller->toggle($this->request(['status' => 0, 'expected' => 1]), 'category', 1);
        try {
            $this->controller->store($this->request($this->business(['category_name' => 'New business'])), 'business');
            $this->fail('Inactive parent must be rejected');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('parent_category', $error->errors());
        }
        $this->controller->update($this->request($this->business(['category_name' => 'Existing renamed'])), 'business', 1);
        $this->assertSame('Existing renamed', VendorBusinessCategory::find(1)->category_name);
        $this->assertSame(1, VendorBusinessCategory::count());
    }

    public function test_status_toggle_preserves_children_and_rejects_stale_updates(): void
    {
        $this->controller->store($this->request($this->category()), 'category');
        $this->controller->store($this->request($this->business()), 'business');
        $this->controller->toggle($this->request(['status' => 0, 'expected' => 1]), 'category', 1);
        $this->assertSame(0, VendorCategory::find(1)->status);
        $this->assertSame(1, VendorBusinessCategory::find(1)->status);
        try {
            $this->controller->toggle($this->request(['status' => 1, 'expected' => 1]), 'category', 1);
            $this->fail('Stale toggle must be rejected');
        } catch (HttpException $error) {
            $this->assertSame(409, $error->getStatusCode());
        }
        $this->controller->toggle($this->request(['status' => 0, 'expected' => 1]), 'business', 1);
        $this->assertSame(0, VendorBusinessCategory::find(1)->status);
        $this->assertSame(1, VendorCategory::count());
    }

    public function test_all_actions_require_admin_and_unknown_kind_is_rejected(): void
    {
        foreach (['index', 'json', 'edit', 'store', 'update', 'toggle'] as $method) {
            try {
                $args = [$this->request([], false)];
                if ($method !== 'index') {
                    $args[] = 'category';
                }
                if (in_array($method, ['edit', 'update', 'toggle'], true)) {
                    $args[] = 1;
                }
                $this->controller->{$method}(...$args);
                $this->fail('Non-admin must be rejected');
            } catch (HttpException $error) {
                $this->assertSame(403, $error->getStatusCode());
            }
        }
        try {
            $this->controller->json($this->request(), 'users');
            $this->fail('Unknown kind must be rejected');
        } catch (HttpException $error) {
            $this->assertSame(404, $error->getStatusCode());
        }
        $this->assertSame(0, VendorCategory::count());
    }
}
