<?php

namespace Tests\Unit;

use App\Http\Controllers\Vms\SetupController;
use App\Services\Vms\SetupCatalog;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VmsSetupTest extends TestCase
{
    private $previousApp;

    private Manager $database;

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
    }

    protected function tearDown(): void
    {
        $this->database->getConnection('mysql7')->disconnect();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousApp);
        Application::setInstance($this->previousApp);
        parent::tearDown();
    }

    public function test_users_query_omits_credentials_and_binds_search_parameters(): void
    {
        $this->database->getConnection('mysql7')->getSchemaBuilder()->create('admin', function ($table) {
            $table->integer('id');
            foreach (['name', 'username', 'email', 'role', 'status', 'approver', 'notification', 'password', 'cookie'] as $column) {
                $table->string($column)->nullable();
            }
        });
        $this->database->getConnection('mysql7')->table('admin')->insert([
            ['id' => 2, 'username' => 'finance-test', 'password' => 'never-select', 'cookie' => 'never-select'],
            ['id' => 1, 'username' => 'procurement-test', 'password' => 'never-select', 'cookie' => 'never-select'],
        ]);
        $catalog = new SetupCatalog;
        $definition = $catalog->definition('users');
        $rows = $catalog->query($definition)->get();
        $this->assertSame([1, 2], $rows->pluck('id')->all());
        $this->assertFalse(property_exists($rows->first(), 'password'));
        $this->assertFalse(property_exists($rows->first(), 'cookie'));
        $this->assertSame('finance-test', $catalog->query($definition, 'finance')->first()->username);
        $this->assertCount(0, $catalog->query($definition, "' OR 1=1 --")->get());
    }

    public function test_five_sections_and_their_tabs_use_existing_vms_tables(): void
    {
        $catalog = new SetupCatalog;
        foreach (['document' => 'master_document', 'vendor-category' => 'vendor_category_parent', 'company-department' => 'company', 'bank' => 'master_bank', 'users' => 'admin'] as $section => $table) {
            $this->assertSame($table, $catalog->definition($section)['active']['table']);
        }
        $this->assertSame('master_devision', $catalog->definition('company-department', 'department')['active']['table']);
        $this->assertSame('ord_masterdoc', $catalog->definition('document', 'invoice')['active']['table']);
    }

    public function test_unknown_tab_cannot_read_an_arbitrary_table(): void
    {
        $this->expectException(HttpException::class);
        (new SetupCatalog)->definition('users', 'vendor_bank');
    }

    public function test_non_admin_is_rejected_before_data_is_queried(): void
    {
        $request = Request::create('/vms-setup/users');
        $request->setUserResolver(fn () => new class
        {
            public function roles(): array
            {
                return ['finance'];
            }
        });
        try {
            (new SetupController)->index($request, new SetupCatalog);
            $this->fail('Non-admin access must be rejected.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
    }
}
