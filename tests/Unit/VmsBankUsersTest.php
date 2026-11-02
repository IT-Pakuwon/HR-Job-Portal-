<?php

namespace Tests\Unit;

use App\Http\Controllers\Vms\BankController;
use App\Http\Controllers\Vms\UserController;
use App\Models\Vms\Bank;
use App\Models\Vms\InternalUser;
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

class VmsBankUsersTest extends TestCase
{
    private $previousApp;

    private $previousResolver;

    private Manager $database;

    private BankController $controller;

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
        });
        $schema->create('master_devision', function ($table) {
            $table->string('devision_name');
        });
        $schema->create('master_bank', function ($table) {
            $table->string('bank_id')->primary();
            $table->string('bank_name');
            $table->integer('transfer_fee');
            foreach (['logo', 'kode_kliring', 'kode_rtgs', 'swift_kode'] as $field) {
                $table->string($field);
            }
        });
        $schema->create('admin', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('username');
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->integer('status');
            foreach (['approver', 'notification', 'api', 'telegram', 'idacum', 'approver_vendor_manual', 'approver_dokumen', 'cookie'] as $field) {
                $table->string($field)->nullable();
            }
        });
        $schema->create('admin_company', function ($table) {
            $table->increments('id');
            $table->integer('UserID');
            $table->string('CpnyID');
        });
        $this->database->getConnection('mysql7')->table('company')->insert([['CpnyID' => 'EP'], ['CpnyID' => 'GPS']]);
        $this->database->getConnection('mysql7')->table('master_devision')->insert(['devision_name' => 'Finance']);
        $this->controller = new BankController;
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

    private function bank(array $extra = []): array
    {
        return array_replace(['bank_id' => '001', 'bank_name' => 'Test Bank', 'transfer_fee' => 5000, 'kode_kliring' => '001', 'kode_rtgs' => 'RTGS', 'swift_kode' => 'SWIFT'], $extra);
    }

    private function user(array $extra = []): array
    {
        return array_replace(['name' => 'Test User', 'username' => 'test-user', 'email' => 'test@example.com', 'role' => 'finance', 'status' => 1, 'password' => 'test-password', 'password_confirmation' => 'test-password', 'companies' => ['EP'], 'approver' => 'N', 'notification' => 'Y', 'api' => 'N', 'approver_vendor_manual' => 'N', 'approver_dokumen' => 'N'], $extra);
    }

    public function test_bank_create_update_preserves_key_and_logo(): void
    {
        $this->assertSame(201, $this->controller->store($this->request($this->bank()))->getStatusCode());
        Bank::whereKey('001')->update(['logo' => 'existing.png']);
        $this->controller->update($this->request($this->bank(['bank_id' => '002', 'bank_name' => 'Updated', 'logo' => 'ignored.png'])), '001');
        $row = Bank::findOrFail('001');
        $this->assertSame('Updated', $row->bank_name);
        $this->assertSame('existing.png', $row->logo);
        $this->assertNull(Bank::find('002'));
        $this->assertSame('001', $this->controller->json($this->request())->getData(true)['data'][0]['bank_id']);
    }

    public function test_bank_duplicate_codes_and_invalid_fee_are_rejected(): void
    {
        $this->controller->store($this->request($this->bank()));
        foreach ([$this->bank(), $this->bank(['bank_id' => '002']), $this->bank(['bank_id' => '002', 'transfer_fee' => -1]), $this->bank(['bank_id' => 'TOOLONG'])] as $data) {
            try {
                $this->controller->store($this->request($data));
                $this->fail('Expected validation rejection');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        $this->assertSame(1, Bank::count());
    }

    public function test_user_create_company_mapping_and_password_are_private(): void
    {
        $controller = new UserController;
        $this->assertSame(201, $controller->store($this->request($this->user()))->getStatusCode());
        $row = InternalUser::findOrFail(1);
        $this->assertSame(md5('test-password'), $row->password);
        $json = $controller->json($this->request())->getData(true)['data'][0];
        $this->assertArrayNotHasKey('password', $json);
        $this->assertArrayNotHasKey('cookie', $json);
        $edit = $controller->edit($this->request(), 1)->getData(true);
        $this->assertArrayNotHasKey('password', $edit);
        $this->assertSame(['EP'], $edit['companies']);
        $controller->update($this->request($this->user(['password' => '', 'password_confirmation' => '', 'companies' => ['GPS'], 'name' => 'Updated', 'cookie' => 'ignored'])), 1);
        $this->assertSame(md5('test-password'), InternalUser::findOrFail(1)->password);
        $this->assertSame(['GPS'], $controller->edit($this->request(), 1)->getData(true)['companies']);
        $this->assertNull(InternalUser::findOrFail(1)->cookie);
        $controller->update($this->request($this->user(['password' => 'new-password', 'password_confirmation' => 'new-password', 'companies' => []])), 1);
        $this->assertSame(md5('new-password'), InternalUser::findOrFail(1)->password);
        $this->assertSame([], $controller->edit($this->request(), 1)->getData(true)['companies']);
    }

    public function test_user_validation_does_not_change_mapping_or_password(): void
    {
        $controller = new UserController;
        $controller->store($this->request($this->user()));
        foreach ([['password' => 'short'], ['password_confirmation' => 'mismatch'], ['role' => 'unknown'], ['companies' => ['INVALID']], ['companies' => ['EP', 'EP']], ['status' => 7], ['email' => 'not-email']] as $extra) {
            try {
                $controller->update($this->request($this->user($extra)), 1);
                $this->fail('Expected validation rejection');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        try {
            $controller->store($this->request($this->user()));
            $this->fail('Expected duplicate rejection');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('username', $e->errors());
        }
        $this->assertSame(md5('test-password'), InternalUser::findOrFail(1)->password);
        $this->assertSame(['EP'], $controller->edit($this->request(), 1)->getData(true)['companies']);
    }

    public function test_user_status_conflict_and_role_guard(): void
    {
        $controller = new UserController;
        $controller->store($this->request($this->user()));
        $controller->toggle($this->request(['status' => 0, 'expected' => 1]), 1);
        $this->assertSame(0, InternalUser::findOrFail(1)->status);
        try {
            $controller->toggle($this->request(['status' => 1, 'expected' => 1]), 1);
            $this->fail('Expected stale update rejection');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        foreach ([fn () => $controller->index($this->request([], false)), fn () => $controller->json($this->request([], false)), fn () => $controller->edit($this->request([], false), 1), fn () => $controller->store($this->request([], false)), fn () => $controller->update($this->request([], false), 1), fn () => $controller->toggle($this->request([], false), 1), fn () => $this->controller->index($this->request([], false)), fn () => $this->controller->json($this->request([], false)), fn () => $this->controller->edit($this->request([], false), '001'), fn () => $this->controller->store($this->request([], false)), fn () => $this->controller->update($this->request([], false), '001')] as $action) {
            try {
                $action();
                $this->fail('Expected forbidden');
            } catch (HttpException $e) {
                $this->assertSame(403,$e->getStatusCode());
            }
        }
    }
}
