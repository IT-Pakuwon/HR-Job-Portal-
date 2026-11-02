<?php

namespace Tests\Unit;

use App\Http\Controllers\Vms\DocumentController;
use App\Models\Vms\Document;
use App\Models\Vms\InvoiceDocument;
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

class VmsDocumentCrudTest extends TestCase
{
    private $previousApp;

    private $previousResolver;

    private Manager $database;

    private DocumentController $controller;

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
        $schema->create('master_devision', function ($table) {
            $table->string('devision_id')->primary();
            $table->string('devision_name');
        });
        $this->database->getConnection('mysql7')->table('master_devision')->insert(['devision_id' => 'finance', 'devision_name' => 'Finance']);
        $schema->create('master_document', function ($table) {
            $table->increments('document_id');
            $table->string('document_name');
            $table->text('document_remark');
            $table->string('devision_id');
            $table->integer('document_order')->nullable();
            $table->integer('status');
            foreach (['has_expire_date', 'has_conditional', 'conditional_date', 'multiple_files', 'related_privy'] as $column) {
                $table->string($column)->nullable();
            }
        });
        $schema->create('ord_masterdoc', function ($table) {
            $table->integer('id')->primary();
            foreach (['Module', 'document_name', 'DivisionID', 'Required', 'scan_imagick', 'ICDP'] as $column) {
                $table->string($column)->nullable();
            }
        });
        foreach (['setup_document' => 'document_id', 'vendor_document' => 'document_id', 'vendor_document_history' => 'document_id', 'ordconfirmattach' => 'MasterDocID', 'ordconfirmattachkontrak' => 'master_doc_id'] as $name => $column) {
            $schema->create($name, function ($table) use ($column) {
                $table->increments('id');
                $table->integer($column);
            });
        }
        $this->controller = new DocumentController;
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

    private function vendor(array $extra = []): array
    {
        return array_replace(['document_name' => 'Test document', 'document_remark' => 'Notes', 'devision_id' => 'finance', 'document_order' => 1, 'status' => 1, 'has_expire_date' => 'yes', 'has_conditional' => 'N', 'multiple_files' => 'N', 'related_privy' => 'N'], $extra);
    }

    private function invoice(array $extra = []): array
    {
        return array_replace(['id' => 10, 'document_name' => 'Invoice', 'Module' => 'Purchase Service', 'DivisionID' => 'finance', 'Required' => 'Y', 'scan_imagick' => 'N', 'ICDP' => ''], $extra);
    }

    public function test_vendor_create_edit_and_list_only_mutate_document_fields(): void
    {
        $response = $this->controller->store($this->request($this->vendor(['document_id' => 999, 'password' => 'ignored'])), 'vendor');
        $this->assertSame(201, $response->getStatusCode());
        $id = $response->getData(true)['data']['document_id'];
        $this->assertSame(1, $id);
        $this->controller->update($this->request($this->vendor(['document_name' => 'Edited', 'has_conditional' => 'Y', 'conditional_date' => '2020-01-01'])), 'vendor', $id);
        $this->assertSame('2020-01-01', Document::find($id)->conditional_date);
        $this->controller->update($this->request($this->vendor()), 'vendor', $id);
        $this->assertNull(Document::find($id)->conditional_date);
        $this->assertCount(1, $this->controller->json($this->request(), 'vendor')->getData(true)['data']);
        $this->assertSame('Test document', $this->controller->edit($this->request(), 'vendor', $id)->getData(true)['document_name']);
        $this->assertSame(1, Document::count());
    }

    public function test_invalid_department_flags_and_conditional_date_are_rejected(): void
    {
        foreach ([['devision_id' => 'unknown'], ['status' => 9], ['has_expire_date' => 'maybe'], ['has_conditional' => 'Y'], ['multiple_files' => 'anything'], ['document_name' => '']] as $invalid) {
            try {
                $this->controller->store($this->request($this->vendor($invalid)), 'vendor');
                $this->fail('Invalid document must not be saved');
            } catch (ValidationException $error) {
                $this->assertNotEmpty($error->errors());
            }
        }
        $this->assertSame(0, Document::count());
    }

    public function test_toggle_validates_expected_value_and_keeps_invoice_separate(): void
    {
        $this->controller->store($this->request($this->vendor()), 'vendor');
        $this->controller->store($this->request($this->invoice(['id' => 1])), 'invoice');
        $this->controller->toggle($this->request(['status' => 0, 'expected' => 1]), 'vendor', 1);
        $this->assertSame(0, Document::find(1)->status);
        $this->assertSame('Y', InvoiceDocument::find(1)->Required);
        try {
            $this->controller->toggle($this->request(['status' => 1, 'expected' => 1]), 'vendor', 1);
            $this->fail('Stale status must be rejected');
        } catch (HttpException $error) {
            $this->assertSame(409, $error->getStatusCode());
        }
        $this->controller->toggle($this->request(['Required' => 'N', 'expected' => 'Y']), 'invoice', 1);
        $this->assertSame('N', InvoiceDocument::find(1)->Required);
    }

    public function test_invoice_id_is_required_unique_and_immutable(): void
    {
        $this->controller->store($this->request($this->invoice()), 'invoice');
        try {
            $this->controller->store($this->request($this->invoice()), 'invoice');
            $this->fail('Duplicate ID must be rejected');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('id', $error->errors());
        }
        $this->controller->update($this->request($this->invoice(['id' => 50, 'document_name' => 'Edited Invoice'])), 'invoice', 10);
        $this->assertNull(InvoiceDocument::find(50));
        $this->assertSame('Edited Invoice', InvoiceDocument::find(10)->document_name);
        $this->assertSame(1, InvoiceDocument::count());
    }

    public function test_every_action_rejects_non_admin_before_read_or_write(): void
    {
        foreach (['index', 'json', 'edit', 'store', 'update', 'toggle'] as $method) {
            try {
                $args = [$this->request([], false)];
                if ($method !== 'index') {
                    $args[] = 'vendor';
                }
                if (in_array($method, ['edit', 'update', 'toggle'], true)) {
                    $args[] = 1;
                }
                $this->controller->{$method}(...$args);
                $this->fail('Non-admin action must be rejected');
            } catch (HttpException $error) {
                $this->assertSame(403, $error->getStatusCode());
            }
        }
        $this->assertSame(0, Document::count());
    }
}
