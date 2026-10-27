<?php

namespace Tests\Feature\DataHub;

use App\Http\Controllers\DataHub\OmDashboardController;
use App\Models\User;
use App\Services\DataHub\Om\OmFilters;
use App\Services\DataHub\Om\OmMalls;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Carbon;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class OmFiltersStateTest extends TestCase
{
    private function signIn(array $roles, array $companies): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('isPrimaryAdmin')->andReturn(false);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => in_array($role, $roles, true));
        $user->shouldReceive('scopedCompanyIds')->andReturn($companies);
        auth()->setUser($user);
    }

    private function request(string $method = 'GET', array $data = []): Request
    {
        $request = Request::create('/om-dashboard', $method, $data);
        $request->setLaravelSession($this->session ??= new Store('t', new ArraySessionHandler(120)));

        return $request;
    }

    private ?Store $session = null;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-27 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_a_fresh_visit_defaults_to_aw_today_and_the_overview_tab(): void
    {
        $this->signIn(['DASH2ACCESS'], []);

        $f = OmFilters::fromSession($this->request(), new OmMalls);

        $this->assertSame('GC', $f->mall);
        $this->assertSame(['2026-10-27', '2026-10-27'], [$f->startDate, $f->endDate]);
        $this->assertSame(['overview', 'monthly', 'all'], [$f->tab, $f->granularity, $f->movementType]);
    }

    public function test_the_tab_is_never_saved(): void
    {
        $this->signIn(['DASH2ACCESS'], []);
        $malls = new OmMalls;

        $this->assertArrayNotHasKey('tab', OmFilters::sanitize(['tab' => 'kaizen'], $malls));

        $this->session = new Store('t', new ArraySessionHandler(120));
        $this->session->put('om_dashboard', ['tab' => 'kaizen']);
        $this->assertSame('overview', OmFilters::fromSession($this->request(), $malls)->tab);
    }

    public function test_a_chosen_company_and_toggles_are_remembered(): void
    {
        $this->signIn(['DASH2ACCESS'], []);
        $malls = new OmMalls;
        $request = $this->request();

        OmFilters::remember($request, OmFilters::sanitize(['mall' => 'kk', 'traffic_granularity' => 'weekly', 'tenant_movement_type' => 'new'], $malls));
        $f = OmFilters::fromSession($this->request(), $malls);

        $this->assertSame(['KK', 'weekly', 'new'], [$f->mall, $f->granularity, $f->movementType]);
    }

    public function test_a_chosen_date_range_holds_for_the_day_but_not_the_next_one(): void
    {
        $this->signIn(['DASH2ACCESS'], []);
        $malls = new OmMalls;

        OmFilters::remember($this->request(), OmFilters::sanitize(['start_date' => '2026-10-01', 'end_date' => '2026-10-20'], $malls));
        $same = OmFilters::fromSession($this->request(), $malls);
        $this->assertSame(['2026-10-01', '2026-10-20'], [$same->startDate, $same->endDate]);

        Carbon::setTestNow('2026-10-28 08:00:00');
        $next = OmFilters::fromSession($this->request(), $malls);
        $this->assertSame(['2026-10-28', '2026-10-28'], [$next->startDate, $next->endDate]);
    }

    public function test_sanitize_drops_invalid_and_forbidden_values(): void
    {
        $this->signIn(['OMACCESS'], ['EP']);

        $clean = OmFilters::sanitize([
            'mall' => 'GC',                       // not this user's company
            'start_date' => 'nope',
            'end_date' => '2026-02-31',          // not a real date
            'traffic_granularity' => 'hourly',
            'tenant_movement_type' => 'everything',
            'injected' => 'x',
        ], new OmMalls);

        $this->assertSame([], $clean);
        $this->assertSame(['mall' => 'KK'], OmFilters::sanitize(['mall' => 'kk'], new OmMalls));
    }

    public function test_a_saved_mall_the_user_can_no_longer_open_falls_back_to_their_default(): void
    {
        $this->signIn(['OMACCESS'], ['EP']);
        $this->session = new Store('t', new ArraySessionHandler(120));
        $this->session->put('om_dashboard', ['mall' => 'GC']);

        $this->assertSame('KK', OmFilters::fromSession($this->request(), new OmMalls)->mall);
    }

    public function test_ajax_sections_refuse_a_mall_outside_the_scope(): void
    {
        $this->signIn(['OMACCESS'], ['EP']);

        $this->assertSame('KK', OmFilters::fromRequest($this->request('GET', ['mall' => 'KK']), new OmMalls)->mall);

        $this->expectException(HttpException::class);
        OmFilters::fromRequest($this->request('GET', ['mall' => 'GC']), new OmMalls);
    }

    public function test_the_controller_keeps_the_url_clean(): void
    {
        $this->signIn(['DASH2ACCESS'], []);
        $controller = app(OmDashboardController::class);

        // an old link with parameters is absorbed into the session, then redirected to the bare URL
        $response = $controller->index($this->request('GET', ['mall' => 'KK', 'tab' => 'kaizen', 'start_date' => '2026-10-05']));
        $this->assertSame(route('datahub-dashboard.om.index'), $response->getTargetUrl());
        $this->assertStringNotContainsString('?', $response->getTargetUrl());
        $this->assertSame('KK', $this->session->get('om_dashboard.mall'));
        $this->assertArrayNotHasKey('tab', $this->session->get('om_dashboard'));

        // choosing something in the page POSTs it; JSON callers get 204, forms get a redirect
        $json = $this->request('POST', ['mall' => 'PBM']);
        $json->headers->set('Accept', 'application/json');
        $this->assertSame(204, $controller->updateState($json)->getStatusCode());
        $this->assertSame('PBM', $this->session->get('om_dashboard.mall'));

        $form = $controller->updateState($this->request('POST', ['traffic_granularity' => 'daily']));
        $this->assertSame(route('datahub-dashboard.om.index'), $form->getTargetUrl());
    }
}
