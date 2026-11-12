<?php

namespace Tests\Unit;

use App\Http\Controllers\ApprovalDashboardController;
use App\Models\User;
use App\Services\DocumentNotificationService;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\PostgresConnection;
use PHPUnit\Framework\TestCase;

class RecruitmentNotificationTest extends TestCase
{
    public function test_waiting_candidates_notify_their_own_group_only(): void
    {
        $previousContainer = Container::getInstance();
        $previousResolver = Model::getConnectionResolver();
        try {
            $container = new Container();
            Container::setInstance($container);
            $controller = $this->createMock(ApprovalDashboardController::class);
            $rows = collect([
                ['hid' => 'sby-id', 'docid' => 'JAP261100053', 'docdate' => '2026-11-11 09:57:10', 'cpnyid' => 'TP', 'url' => '/showcareers'],
                ['hid' => 'jkt-id', 'docid' => 'JAP261100053', 'docdate' => '2026-11-11 10:00:00', 'cpnyid' => 'AW', 'url' => '/showcareers'],
                ['hid' => 'other-id', 'docid' => 'BA26110001', 'cpnyid' => 'TP', 'url' => '/showbast'],
            ]);
            $controller->method('waitingForUser')->willReturn($rows);
            $container->instance(ApprovalDashboardController::class, $controller);

            $connection = new class extends PostgresConnection {
                public function __construct() { parent::__construct(fn () => throw new \RuntimeException('No live database access')); }
                public function select($query, $bindings = [], $useReadPdo = true) {
                    return [(object) ['cpny_id' => 'TP', 'group_cpny_id' => 'SBY'], (object) ['cpny_id' => 'AW', 'group_cpny_id' => 'JKT']];
                }
            };
            $resolver = new ConnectionResolver(['pgsql2' => $connection]);
            $resolver->setDefaultConnection('pgsql2');
            Model::setConnectionResolver($resolver);

            foreach (['SBY' => 'sby-id', 'JKT' => 'jkt-id'] as $group => $expectedId) {
                $user = $this->getMockBuilder(User::class)->onlyMethods(['isPrimaryAdmin'])->getMock();
                $user->method('isPrimaryAdmin')->willReturn(false);
                $user->group_cpny_id = $group;
                $notifications = DocumentNotificationService::recruitmentWaitingForUser($user);
                $this->assertCount(1, $notifications);
                $this->assertSame($expectedId, $notifications->first()['hid']);
                $this->assertSame('/showcareers', $notifications->first()['url']);
                $this->assertSame('CAREER_APPROVAL', $notifications->first()['status']);
            }
        } finally {
            Container::setInstance($previousContainer);
            if ($previousResolver) {
                Model::setConnectionResolver($previousResolver);
            } else {
                Model::unsetConnectionResolver();
            }
        }
    }
}
