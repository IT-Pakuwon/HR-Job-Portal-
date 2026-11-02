<?php

namespace Tests\Unit;

use App\Http\Controllers\Vms\CompanyDepartmentController;
use App\Models\Vms\Company;
use App\Models\Vms\Department;
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

class VmsCompanyDepartmentTest extends TestCase
{
    private $previousApp;

    private $previousResolver;

    private Manager $database;

    private CompanyDepartmentController $controller;

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
        $schema->create('company', function ($table) {
            $table->string('CpnyID')->primary();
            $table->string('CpnyName');
            $table->string('Addr1');
            $table->string('NpwpName')->nullable();
            $table->string('NpwpNumber')->nullable();
            $table->string('finance_address')->nullable();
            $table->string('privyid')->nullable();
        });
        $schema->create('master_devision', function ($table) {
            $table->string('devision_id')->primary();
            $table->string('devision_name');
            $table->dateTime('cerated_at');
        });
        $this->controller = new CompanyDepartmentController;
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

    public function test_company_create_update_preserves_id_and_unrelated_fields(): void
    {
        $response = $this->controller->store($this->request(['CpnyID' => 'EP', 'CpnyName' => 'Example', 'NpwpNumber' => '001.002', 'privyid' => 'ignored']), 'company');
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('', Company::findOrFail('EP')->Addr1);
        $this->assertNull(Company::findOrFail('EP')->privyid);
        Company::whereKey('EP')->update(['Addr1' => 'Existing address', 'privyid' => 'private-id']);
        $this->controller->update($this->request(['CpnyID' => 'OTHER', 'CpnyName' => 'Updated', 'NpwpName' => 'Tax name', 'NpwpNumber' => '001.002', 'finance_address' => 'Finance address']), 'company', 'EP');
        $row = Company::findOrFail('EP');
        $this->assertSame('Updated', $row->CpnyName);
        $this->assertSame('Existing address', $row->Addr1);
        $this->assertSame('private-id', $row->privyid);
        $this->assertNull(Company::find('OTHER'));
        $json = $this->controller->json($this->request(), 'company')->getData(true)['data'];
        $this->assertCount(1, $json);
        $this->assertArrayNotHasKey('privyid', $json[0]);
        $this->assertSame('001.002', $json[0]['NpwpNumber']);
        $this->assertSame('Tax name', $this->controller->edit($this->request(), 'company', 'EP')->getData(true)['NpwpName']);
    }

    public function test_department_create_update_preserves_timestamp_and_id(): void
    {
        $this->controller->store($this->request(['devision_id' => 'legal', 'devision_name' => 'Legal', 'cerated_at' => '2000-01-01']), 'department');
        $created = Department::findOrFail('legal')->cerated_at;
        $this->assertNotSame('2000-01-01', $created);
        $this->controller->update($this->request(['devision_id' => 'other', 'devision_name' => 'Legal Review']), 'department', 'legal');
        $this->assertSame($created, Department::findOrFail('legal')->cerated_at);
        $this->assertNull(Department::find('other'));
        $this->assertSame('Legal Review', $this->controller->json($this->request(), 'department')->getData(true)['data'][0]['devision_name']);
    }

    public function test_duplicate_ids_invalid_keys_and_lengths_are_rejected(): void
    {
        $this->controller->store($this->request(['CpnyID' => 'EP', 'CpnyName' => 'Example']), 'company');
        foreach ([['CpnyID' => 'EP', 'CpnyName' => 'Duplicate'], ['CpnyID' => '../bad', 'CpnyName' => 'Bad'], ['CpnyID' => 'TOOLONG12345', 'CpnyName' => 'Long'], ['CpnyID' => 'OK', 'CpnyName' => ''], ['CpnyID' => 'OK', 'CpnyName' => 'Example', 'NpwpNumber' => str_repeat('1', 51)]] as $data) {
            try {
                $this->controller->store($this->request($data), 'company');
                $this->fail('Expected validation rejection');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        $this->assertSame(1, Company::count());
    }

    public function test_admin_guard_and_invalid_kind(): void
    {
        foreach ([fn () => $this->controller->index($this->request([], false)), fn () => $this->controller->json($this->request([], false), 'company'), fn () => $this->controller->edit($this->request([], false), 'department', 'legal'), fn () => $this->controller->store($this->request([], false), 'company'), fn () => $this->controller->update($this->request([], false), 'department', 'legal')] as $action) {
            try {
                $action();
                $this->fail('Expected forbidden');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
        try {
            $this->controller->json($this->request(), 'arbitrary');
            $this->fail('Expected invalid kind');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }
}
