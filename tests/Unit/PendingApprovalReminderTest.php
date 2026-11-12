<?php

namespace Tests\Unit;

use App\Http\Controllers\SendApprovalEmailController;
use App\Models\User;
use App\Services\PendingApprovalReminder;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\PostgresConnection;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PendingApprovalReminderTest extends TestCase
{
    private $previousContainer;
    private $previousFacade;
    private $previousResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousContainer = Container::getInstance();
        $this->previousFacade = Facade::getFacadeApplication();
        $this->previousResolver = Model::getConnectionResolver();
        $app = new Application(dirname(__DIR__, 2));
        $app->instance('config', new Repository(['mail' => ['from' => ['address' => 'system@example.test']], 'app' => ['name' => 'Test App']]));
        $app->instance('url', new class {
            public function to($path, $extra = [], $secure = null) { return 'https://example.test/'.ltrim($path, '/'); }
        });
        $app->instance('hashids', new class {
            public function encode($id) { return 'encoded'.$id; }
        });
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
        Model::setConnectionResolver(new ConnectionResolver([
            'pgsql2' => new PostgresConnection(fn () => throw new \RuntimeException('No live database access.')),
        ]));
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousFacade);
        Container::setInstance($this->previousContainer);
        if ($this->previousResolver) Model::setConnectionResolver($this->previousResolver);
        else Model::unsetConnectionResolver();
        parent::tearDown();
    }

    private function approval(array $overrides = []): object
    {
        return (object) array_replace(['refnbr' => 'CS26110001', 'aprv_doctype' => 'CS', 'aprv_cpnyid' => 'AW',
            'aprv_departementid' => 'IT', 'aprv_datebefore' => '2026-11-10 09:00:00', 'created_by' => 'requester'], $overrides);
    }

    private function document(array $overrides = []): object
    {
        return (object) array_replace(['id' => 10, 'docid' => 'CS26110001', 'cpnyid' => 'AW',
            'url' => '/showcs', 'infohd' => 'Test purchase', 'departementid' => 'IT'], $overrides);
    }

    public function test_pending_query_matches_exact_approver_tokens_and_active_pending_step(): void
    {
        $query = (new PendingApprovalReminder())->pendingQuery(' Alice ');
        $this->assertSame(['P', 'alice'], $query->getBindings());
        $this->assertStringContainsString('"aprv_datebefore" is not null', $query->toSql());
        $this->assertStringContainsString('ANY(regexp_split_to_array', $query->toSql());
        $this->assertStringContainsString('[,;]', $query->toSql());
        $this->assertStringNotContainsString(' like ', $query->toSql());
    }

    public function test_documents_use_daily_template_fields_and_company_specific_links(): void
    {
        $rows = (new PendingApprovalReminder())->documents(collect([$this->approval()]), collect([
            $this->document(['id' => 99, 'cpnyid' => 'EP']), $this->document(),
        ]));
        $this->assertCount(1, $rows);
        $this->assertSame('Canvass Sheet', $rows[0]['docname']);
        $this->assertSame('10-11-2026 09:00', $rows[0]['date']);
        $this->assertSame('https://example.test/showcs/encoded10', $rows[0]['url']);
        $this->assertSame('AW', $rows[0]['cpnyid']);
    }

    public function test_missing_or_ambiguous_documents_are_not_sent(): void
    {
        $service = new PendingApprovalReminder();
        $this->assertSame([], $service->documents(collect([$this->approval()]), collect()));
        $this->assertSame([], $service->documents(collect([$this->approval()]), collect([$this->document(), $this->document(['id' => 20])])));
    }

    public function test_manual_email_uses_same_template_and_stored_notification_address(): void
    {
        $mailer = new class {
            public array $call;
            public function send($view, $data, $callback) {
                $message = new class {
                    public $recipient;
                    public $subject;
                    public function to($value) { $this->recipient = $value; return $this; }
                    public function subject($value) { $this->subject = $value; return $this; }
                    public function from($address, $name) { return $this; }
                };
                $callback($message);
                $this->call = [$view, $data, $message];
            }
        };
        Mail::swap($mailer);
        $user = new User(['username' => 'alice', 'notification_email' => 'alice@example.test']);
        $summary = ['total' => 2, 'documents' => []];
        (new PendingApprovalReminder())->send($user, $summary);
        $this->assertSame('emails.sendapprovenew', $mailer->call[0]);
        $this->assertSame('alice@example.test', $mailer->call[2]->recipient);
        $this->assertSame('Approval Pending Summary - Total 2 Document(s)', $mailer->call[2]->subject);
    }

    public function test_non_admin_and_guest_cannot_preview_or_send_other_users_approval_emails(): void
    {
        foreach ([null, new User(['user_role' => 'adminsby'])] as $actor) {
            $request = new Request();
            $request->setUserResolver(fn () => $actor);
            foreach (['index', 'users', 'pending', 'send'] as $action) {
                try {
                    $controller = new SendApprovalEmailController();
                    in_array($action, ['pending', 'send'], true)
                        ? $controller->{$action}($request, new PendingApprovalReminder()) : $controller->{$action}($request);
                    $this->fail('Non-admin request was allowed.');
                } catch (HttpException $e) {
                    $this->assertSame(403, $e->getStatusCode());
                }
            }
        }
    }
}
