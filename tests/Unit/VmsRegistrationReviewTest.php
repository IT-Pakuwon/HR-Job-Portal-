<?php

namespace Tests\Unit;

use App\Http\Controllers\Vms\VendorController;
use App\Services\Vms\RegistrationEmail;
use App\Services\Vms\RegistrationReview;
use App\Services\Vms\VendorDirectory;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VmsRegistrationReviewTest extends TestCase
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
            $table->string('username');
            $table->string('email');
            $table->string('npwp');
            $table->string('company_profile');
            $table->integer('vendor_category');
            $table->integer('bidang_usaha');
            $table->integer('account_status');
            $table->integer('step');
            $table->string('type');
            $table->string('data_status');
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->nullable();
            $table->string('second_vendor_id')->nullable();
            $table->dateTime('created_at');
        });
        $schema->create('admin', function ($table) {
            $table->increments('id');
            $table->string('username');
            $table->integer('status');
            $table->string('role');
        });
        $schema->create('vendor_category_parent', function ($table) {
            $table->integer('id')->primary();
            $table->integer('status');
        });
        $schema->create('vendor_category', function ($table) {
            $table->integer('category_id')->primary();
            $table->integer('parent_category');
            $table->integer('status');
        });
        $schema->create('master_document', function ($table) {
            $table->integer('document_id')->primary();
            $table->integer('status');
        });
        $schema->create('setup_document', function ($table) {
            $table->integer('category_id');
            $table->integer('document_id');
            $table->integer('is_published');
        });
        $schema->create('master_tag', function ($table) {
            $table->increments('id');
            $table->string('nama_tag');
            $table->string('status');
            $table->dateTime('created_at');
            $table->integer('created_by');
        });
        $schema->create('vendor_tag', function ($table) {
            $table->integer('id_vendor');
            $table->integer('id_tag');
            $table->dateTime('created_at');
            $table->integer('created_by');
        });
        $schema->create('vendor_internal_activity', function ($table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('activity');
            $table->text('notes');
            $table->integer('created_by')->nullable();
            $table->dateTime('created_at');
        });
        $schema->create('vendor_basic_information', function ($table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('email2');
        });
        $schema->create('email_activity_settings', function ($table) {
            $table->string('activity_key')->primary();
            $table->boolean('enabled');
        });
        $schema->create('email_activity_deliveries', function ($table) {
            $table->increments('id');
            $table->string('activity_key');
            $table->string('event_key')->unique();
            $table->text('recipients');
            $table->text('payload');
            $table->string('status');
            $table->integer('attempts');
            $table->dateTime('available_at');
            $table->dateTime('sent_at')->nullable();
            $table->string('error_type')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
        $db = $this->database->getConnection('mysql7');
        $db->table('vendor')->insert(['vendor_id' => 1, 'vendor_nama' => 'Example Vendor', 'username' => 'example', 'email' => 'vendor@example.com', 'npwp' => '001', 'company_profile' => 'example.pdf', 'vendor_category' => 1, 'bidang_usaha' => 10, 'account_status' => 0, 'step' => 1, 'type' => 'self_registran', 'data_status' => 'inprogress', 'created_at' => '2026-01-01 10:00:00']);
        $db->table('admin')->insert(['id' => 7, 'username' => 'test-admin', 'status' => 1, 'role' => 'procurement']);
        $db->table('vendor_category_parent')->insert(['id' => 1, 'status' => 1]);
        $db->table('vendor_category')->insert(['category_id' => 10, 'parent_category' => 1, 'status' => 1]);
        $db->table('master_document')->insert(['document_id' => 1, 'status' => 1]);
        $db->table('setup_document')->insert(['category_id' => 10, 'document_id' => 1, 'is_published' => 1]);
        $db->table('email_activity_settings')->insert([['activity_key' => 'vendor_account_approved', 'enabled' => 1], ['activity_key' => 'vendor_account_rejected', 'enabled' => 1]]);
        app('config')->set('vms.attachments_url', 'https://example.com/files');
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

    private function payload(string $decision = 'approve'): array
    {
        return ['decision' => $decision, 'review_token' => (new RegistrationReview)->read(1, new VendorDirectory)['review_token'], 'vendor_name' => 'Approved Vendor', 'vendor_category' => 1, 'bidang_usaha' => 10, 'tags' => ['Example Tag'], 'notes' => 'Checked'];
    }

    public function test_approval_updates_account_preserves_registration_and_enqueues_once(): void
    {
        $review = new RegistrationReview;
        $data = $this->payload();
        $result = $review->decide(1, $data, $this->request()->user());
        $db = $this->database->getConnection('mysql7');
        $vendor = $db->table('vendor')->first();
        $this->assertSame(1, $vendor->account_status);
        $this->assertSame('inprogress', $vendor->data_status);
        $this->assertSame('Approved Vendor', $vendor->vendor_nama);
        $this->assertSame(1, $vendor->step);
        $this->assertSame('2026-01-01 10:00:00', $vendor->created_at);
        $this->assertNull($vendor->second_vendor_id);
        $this->assertSame(7, $vendor->updated_by);
        $this->assertNotNull($result['delivery_id']);
        $this->assertSame(1, $db->table('vendor_tag')->count());
        $this->assertSame(1, $db->table('master_tag')->count());
        $log = $db->table('vendor_internal_activity')->first();
        $this->assertSame(7, $log->created_by);
        $this->assertStringContainsString('test-admin', $log->notes);
        try {
            $review->decide(1, $data, $this->request()->user());
            $this->fail('Expected duplicate decision rejection');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $this->assertSame(1, $db->table('email_activity_deliveries')->count());
    }

    public function test_rejection_needs_no_category_and_includes_alternate_email(): void
    {
        $db = $this->database->getConnection('mysql7');
        $db->table('vendor_basic_information')->insert(['vendor_id' => 1, 'email2' => 'other@example.com']);
        $data = $this->payload('reject');
        unset($data['vendor_name'],$data['vendor_category'],$data['bidang_usaha']);
        (new RegistrationReview)->decide(1, $data, $this->request()->user());
        $row = $db->table('vendor')->first();
        $this->assertSame(2, $row->account_status);
        $this->assertSame('Example Vendor', $row->vendor_nama);
        $delivery = $db->table('email_activity_deliveries')->first();
        $this->assertSame('vendor_account_rejected', $delivery->activity_key);
        $this->assertSame(['vendor@example.com', 'other@example.com'], json_decode($delivery->recipients, true)['to']);
    }

    public function test_invalid_parent_inactive_setup_and_stale_review_leave_data_unchanged(): void
    {
        $db = $this->database->getConnection('mysql7');
        $review = new RegistrationReview;
        foreach (['parent', 'published', 'active'] as $condition) {
            if ($condition === 'parent') {
                $db->table('vendor_category')->update(['parent_category' => 2]);
            }
            if ($condition === 'published') {
                $db->table('vendor_category')->update(['parent_category' => 1]);
                $db->table('setup_document')->update(['is_published' => 2]);
            }
            if ($condition === 'active') {
                $db->table('setup_document')->update(['is_published' => 1]);
                $db->table('vendor_category_parent')->update(['status' => 0]);
            }
            try {
                $review->decide(1, $this->payload(), $this->request()->user());
                $this->fail('Expected validation rejection');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('bidang_usaha', $e->errors());
            }
        }
        $data = $this->payload();
        $db->table('vendor')->update(['vendor_nama' => 'Changed']);
        try {
            $review->decide(1, $data, $this->request()->user());
            $this->fail('Expected stale rejection');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $this->assertSame(0, $db->table('vendor')->value('account_status'));
        $this->assertSame(0, $db->table('email_activity_deliveries')->count());
        $this->assertSame(0, $db->table('vendor_internal_activity')->count());
    }

    public function test_decision_permissions_match_procurement_and_admin(): void
    {
        $db = $this->database->getConnection('mysql7');
        $review = new RegistrationReview;
        $user = $this->request([], false)->user();
        $this->assertTrue($review->canDecide($user));
        $db->table('admin')->update(['role' => 'legal']);
        $this->assertFalse($review->canDecide($user));
        try {
            $review->decide(1, $this->payload(), $user);
            $this->fail('Expected forbidden');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertTrue($review->canDecide($this->request()->user()));
        $db->table('admin')->update(['role' => 'procurement', 'status' => 0]);
        $this->assertFalse($review->canDecide($user));
    }

    public function test_email_off_prevents_enqueue_and_delivery_failure_keeps_decision(): void
    {
        $db = $this->database->getConnection('mysql7');
        $sender = new RegistrationEmail;
        $db->table('email_activity_settings')->update(['enabled' => 0]);
        $this->assertNull($sender->enqueue('vendor_account_approved', 'off', ['vendor@example.com'], []));
        $db->table('email_activity_settings')->update(['enabled' => 1]);
        $result = (new RegistrationReview)->decide(1, $this->payload(), $this->request()->user());
        Mail::swap(new class
        {
            public function send(...$args)
            {
                throw new \RuntimeException('Simulated SMTP failure');
            }
        });
        app()->instance('log', new class
        {
            public function warning(...$args): void {}
        });
        $sender->deliver($result['delivery_id']);
        $row = $db->table('email_activity_deliveries')->first();
        $this->assertSame('failed',$row->status);
        $this->assertSame(1,$row->attempts);
        $this->assertSame(1,$db->table('vendor')->value('account_status'));
    }
}
