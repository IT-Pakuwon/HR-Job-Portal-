<?php

namespace Tests\Feature;

use App\Http\Controllers\OmDashboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class DataHubOmNavigationTest extends TestCase
{
    private function signIn(string $username, array $companies): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->username = $username;
        $user->email = $username;
        $user->shouldReceive('isPrimaryAdmin')->andReturn(false);
        $user->shouldReceive('hasFullDataScope')->andReturn(false);
        $user->shouldReceive('scopedCompanyIds')->andReturn($companies);
        auth()->setUser($user);
    }

    public function test_designated_account_can_switch_between_all_malls(): void
    {
        $this->signIn('habsaribanoewati@pakuwon.com', ['AW']);
        $controller = new OmDashboardController;
        $method = new ReflectionMethod($controller, 'allowedMalls');

        $this->assertSame(['GC', 'KK', 'PBM', 'PMB'], array_keys($method->invoke($controller)));
        $response = $controller->omMenu(Request::create('/data-hub/om', 'GET', [
            'start_date' => '2026-10-01', 'end_date' => '2026-10-06', 'tab' => 'kaizen',
        ]));
        $this->assertSame(route('datahub.om.detail', [
            'mall' => 'GC', 'start_date' => '2026-10-01', 'end_date' => '2026-10-06', 'tab' => 'kaizen',
        ]), $response->getTargetUrl());
    }

    public function test_other_accounts_keep_their_company_scope_and_open_their_first_mall(): void
    {
        $this->signIn('other@example.com', ['EP']);
        $controller = new OmDashboardController;
        $method = new ReflectionMethod($controller, 'allowedMalls');

        $this->assertSame(['KK'], array_keys($method->invoke($controller)));
        $this->assertSame(route('datahub.om.detail', 'KK'),
            $controller->omMenu(Request::create('/data-hub/om'))->getTargetUrl());
    }

    public function test_account_without_mall_access_cannot_open_the_dashboard(): void
    {
        $this->signIn('other@example.com', []);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Tidak ada mall yang dapat diakses.');

        (new OmDashboardController)->omMenu(Request::create('/data-hub/om'));
    }
}
