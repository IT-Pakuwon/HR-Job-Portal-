<?php

namespace Tests\Unit;

use App\Http\Controllers\Vms\VendorController;
use App\Services\Vms\VendorProfileWorkflow;
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

class VmsVendorProfileWorkflowTest extends TestCase
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
        $schema->table('vendor', function ($table) {
            $table->string('status')->default('N');
            $table->dateTime('approved_date')->nullable();
            foreach (['procurement', 'legal', 'tax', 'finance'] as $department) {
                $table->string($department.'_approval')->default('no');
                $table->text($department.'_notes')->nullable();
                $table->dateTime($department.'_date')->nullable();
            }
        });
        $schema->table('admin', function ($table) {
            $table->string('approver')->default('Y');
        });
        $schema->table('master_document', function ($table) {
            $table->string('devision_id')->default('legal');
            $table->string('has_expire_date')->default('no');
            $table->string('has_conditional')->default('N');
            $table->string('conditional_date')->nullable();
        });
        $schema->table('setup_document', function ($table) {
            $table->integer('is_mandatory')->default(1);
        });
        $schema->table('vendor_basic_information', function ($table) {
            $table->string('status')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->nullable();
            $table->string('tanggal_berdiri')->nullable();
        });
        $schema->create('vendor_document', function ($table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->integer('document_id');
            $table->string('doc_name')->nullable();
            $table->string('doc_expire')->nullable();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->integer('approver_2')->nullable();
        });
        $schema->create('vendor_npwp', function ($table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('status_npwp');
            $table->text('rejection_note')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('approver_npwp')->nullable();
        });
        $schema->create('vendor_bank', function ($table) {
            $table->increments('id');
            $table->integer('vendor_id');
            $table->string('status');
            $table->text('rejection_note')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('approver')->nullable();
        });
        $schema->create('users', function ($table) {
            $table->increments('id');
            $table->integer('parent_id');
            $table->string('username');
            $table->string('password');
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->nullable();
        });
        $schema->table('vendor', function ($table) {
            $table->string('password')->nullable();
        });
        $schema->create('chat', function ($table) {
            $table->increments('id');
            $table->integer('sender_id');
            $table->integer('receiver_id');
            $table->integer('vendor_id');
            $table->text('messages');
            $table->dateTime('created_at');
            $table->string('ip_address');
        });
        $schema->create('vms_setting', function ($table) {
            $table->integer('id')->primary();
            $table->string('prevendnbr');
            $table->string('lastvendnbr');
        });
        $db->table('vms_setting')->insert(['id' => 1, 'prevendnbr' => 'VV', 'lastvendnbr' => '00041']);
        $db->table('vendor')->update(['account_status' => 1, 'step' => 5]);
        $db->table('vendor_basic_information')->insert(['vendor_id' => 1, 'email2' => 'other@example.com', 'tanggal_berdiri' => '2000-01-01']);
        $db->table('email_activity_settings')->insert(['activity_key' => 'vendor_profile_review', 'enabled' => 1]);
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

    private function row(string $table, int $id = 1)
    {
        return $this->database->getConnection('mysql7')->table($table)->where($table === 'vendor' ? 'vendor_id' : 'id', $id)->first();
    }

    private function action(string $kind, string $decision = 'approve', ?int $record = null, string $department = 'legal'): array
    {
        $workflow = new VendorProfileWorkflow;
        $table = ['document' => 'vendor_document', 'npwp' => 'vendor_npwp', 'bank' => 'vendor_bank'][$kind] ?? 'vendor';

        return ['kind' => $kind, 'decision' => $decision, 'record_id' => $record, 'department' => $department, 'token' => $workflow->token($this->row($table, $record ?? 1)), 'notes' => $decision === 'approve' ? 'Checked' : 'Please correct the document'];
    }

    private function upload(string $status = 'waiting_review', int $vendor = 1): int
    {
        return $this->database->getConnection('mysql7')->table('vendor_document')->insertGetId(['vendor_id' => $vendor, 'document_id' => 1, 'status' => $status, 'doc_name' => 'example.pdf']);
    }

    public function test_basic_and_document_approval_are_scoped_and_logged(): void
    {
        $workflow = new VendorProfileWorkflow;
        $db = $this->database->getConnection('mysql7');
        $workflow->decide(1, $this->action('basic', 'approve', null, 'procurement'), $this->request()->user());
        $this->assertSame('yes', $this->row('vendor')->procurement_approval);
        $this->assertSame('approved', $db->table('vendor_basic_information')->value('status'));
        $doc = $this->upload();
        $data = $this->action('document', 'approve', $doc);
        $workflow->decide(1, $data, $this->request()->user());
        $this->assertSame('approved', $this->row('vendor_document', $doc)->status);
        $this->assertSame(7, $this->row('vendor_document', $doc)->approver_2);
        try {
            $workflow->decide(1, $data, $this->request()->user());
            $this->fail('Expected stale update');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $this->assertSame(2, $db->table('vendor_internal_activity')->count());
        $foreign = $this->upload('waiting_review', 2);
        try {
            $workflow->decide(1, $this->action('document', 'reject', $foreign), $this->request()->user());
            $this->fail('Expected cross-vendor rejection');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public function test_role_flag_and_procurement_gate_are_enforced(): void
    {
        $workflow = new VendorProfileWorkflow;
        $db = $this->database->getConnection('mysql7');
        $doc = $this->upload();
        $user = $this->request([], false)->user();
        $db->table('admin')->update(['role' => 'legal']);
        try {
            $workflow->decide(1, $this->action('document', 'approve', $doc), $user);
            $this->fail('Expected procurement prerequisite');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
        $db->table('vendor')->update(['procurement_approval' => 'yes']);
        $db->table('admin')->update(['approver' => 'N']);
        try {
            $workflow->decide(1, $this->action('document', 'approve', $doc), $user);
            $this->fail('Expected approver flag rejection');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $db->table('admin')->update(['approver' => 'Y', 'role' => 'tax']);
        try {
            $workflow->decide(1, $this->action('document', 'approve', $doc), $user);
            $this->fail('Expected wrong department rejection');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertSame('waiting_review', $this->row('vendor_document', $doc)->status);
    }

    public function test_rejected_document_blocks_confirmation_and_revision_requires_notes(): void
    {
        $workflow = new VendorProfileWorkflow;
        $db = $this->database->getConnection('mysql7');
        $doc = $this->upload();
        $data = $this->action('document', 'reject', $doc);
        $data['notes'] = '';
        try {
            $workflow->decide(1, $data, $this->request()->user());
            $this->fail('Expected required notes');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('notes', $e->errors());
        }
        $workflow->decide(1, $this->action('document', 'reject', $doc), $this->request()->user());
        try {
            $workflow->decide(1, $this->action('department'), $this->request()->user());
            $this->fail('Expected pending rejected documents');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('review', $e->errors());
        }
        $result = $workflow->decide(1, $this->action('department', 'reject'), $this->request()->user());
        $this->assertSame('revise', $this->row('vendor')->data_status);
        $this->assertSame('H', $this->row('vendor')->status);
        $this->assertSame('yes', $this->row('vendor')->legal_approval);
        $this->assertNotNull($result['delivery_id']);
    }

    public function test_final_confirmation_allocates_vendor_id_once_and_preserves_existing_id(): void
    {
        $workflow = new VendorProfileWorkflow;
        $db = $this->database->getConnection('mysql7');
        $this->upload('approved');
        $db->table('vendor')->update(['procurement_approval' => 'yes', 'legal_approval' => 'yes', 'tax_approval' => 'yes']);
        $workflow->decide(1, $this->action('department', 'approve', null, 'finance'), $this->request()->user());
        $vendor = $this->row('vendor');
        $this->assertSame('approved', $vendor->data_status);
        $this->assertSame('A', $vendor->status);
        $this->assertSame('VV00042', $vendor->second_vendor_id);
        $this->assertSame('00042', $db->table('vms_setting')->value('lastvendnbr'));
        try {
            $workflow->decide(1, $this->action('department', 'reject', null, 'finance'), $this->request()->user());
            $this->fail('Expected completed vendor rejection');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $db->table('vendor')->update(['data_status' => 'inprogress', 'finance_approval' => 'no']);
        $workflow->decide(1, $this->action('department', 'approve', null, 'finance'), $this->request()->user());
        $this->assertSame('VV00042', $this->row('vendor')->second_vendor_id);
        $this->assertSame('00042', $db->table('vms_setting')->value('lastvendnbr'));
    }

    public function test_missing_mandatory_document_rolls_back_final_confirmation(): void
    {
        $workflow = new VendorProfileWorkflow;
        $db = $this->database->getConnection('mysql7');
        $db->table('vendor')->update(['procurement_approval' => 'yes', 'legal_approval' => 'yes', 'tax_approval' => 'yes']);
        try {
            $workflow->decide(1, $this->action('department', 'approve', null, 'finance'), $this->request()->user());
            $this->fail('Expected missing requirement rejection');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('review', $e->errors());
        }
        $this->assertSame('no', $this->row('vendor')->finance_approval);
        $this->assertNull($this->row('vendor')->second_vendor_id);
        $this->assertSame('00041', $db->table('vms_setting')->value('lastvendnbr'));
    }

    public function test_npwp_bank_and_revision_transitions(): void
    {
        $workflow = new VendorProfileWorkflow;
        $db = $this->database->getConnection('mysql7');
        $db->table('vendor_npwp')->insert(['id' => 1, 'vendor_id' => 1, 'status_npwp' => 'waiting_review']);
        $db->table('vendor_bank')->insert(['id' => 1, 'vendor_id' => 1, 'status' => 'waiting_review']);
        $workflow->decide(1, $this->action('npwp', 'approve', 1), $this->request()->user());
        $workflow->decide(1, $this->action('bank', 'approve', 1), $this->request()->user());
        $this->assertSame('approved', $this->row('vendor_npwp')->status_npwp);
        $this->assertSame('approved', $this->row('vendor_bank')->status);
        $workflow->decide(1, $this->action('basic', 'revise', null, 'procurement'), $this->request()->user());
        $this->assertSame(1, $this->row('vendor')->step);
        $this->assertSame(1, $this->row('vendor')->account_status);
        $this->assertSame('revise', $this->row('vendor')->data_status);
    }

    public function test_password_and_chat_are_vendor_scoped_and_do_not_log_secrets(): void
    {
        $workflow = new VendorProfileWorkflow;
        $db = $this->database->getConnection('mysql7');
        $db->table('vendor')->update(['data_status' => 'approved', 'password' => 'old']);
        $db->table('users')->insert([['id' => 1, 'parent_id' => 1, 'username' => 'example', 'password' => 'old'], ['id' => 2, 'parent_id' => 2, 'username' => 'other', 'password' => 'old']]);
        try {
            $workflow->password(1, 'master', 2, 'test-password', $this->request()->user());
            $this->fail('Expected cross-vendor password rejection');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
        $workflow->password(1, 'master', 1, 'test-password', $this->request()->user());
        $this->assertSame(md5('test-password'), $db->table('users')->where('id', 1)->value('password'));
        $this->assertSame(md5('test-password'), $this->row('vendor')->password);
        $this->assertStringNotContainsString('test-password',$db->table('vendor_internal_activity')->value('notes'));
        $workflow->chat(1,'master','Example message',$this->request()->user(),'127.0.0.1');
        $this->assertSame(7,$db->table('chat')->value('sender_id'));
        $db->table('admin')->delete();
        try {
            $workflow->chat(1,'master','Blocked',$this->request()->user(),'127.0.0.1');
            $this->fail('Expected unmapped user rejection');
        } catch (HttpException $e) {
            $this->assertSame(403,$e->getStatusCode());
        }
    }
}
