<?php

namespace Tests\Unit;

use App\Http\Controllers\EvaluasiVendorController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VendorEvaluationScopeTest extends TestCase
{
    private function queryFor(bool $allDepartments, string $companies, string $departments)
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->cpny_id = $companies;
        $user->department_id = $departments;
        $user->shouldReceive('hasRole')->with('VEACCESS')->once()->andReturn($allDepartments);
        $request = Request::create('/evaluasivendor/json');
        $this->app->instance('request', $request);
        $request->setUserResolver(fn () => $user);
        $method = new \ReflectionMethod(EvaluasiVendorController::class, 'scopeEvaluasi');
        return $method->invoke(new EvaluasiVendorController(), DB::connection('pgsql')->table('tr_vendor_perfomance'));
    }

    public function test_evoaccess_keeps_company_filter_and_removes_only_department_filter(): void
    {
        $query = $this->queryFor(true, ' AW, EP ', 'ENG');
        $this->assertStringContainsString('"cpny_id" in (?, ?)', $query->toSql());
        $this->assertStringNotContainsString('department_id', $query->toSql());
        $this->assertSame(['AW', 'EP'], $query->getBindings());
    }

    public function test_regular_users_are_filtered_by_both_company_and_department(): void
    {
        $query = $this->queryFor(false, 'AW', 'ENG, IT');
        $this->assertStringContainsString('"department_id" in (?, ?)', $query->toSql());
        $this->assertSame(['AW', 'ENG', 'IT'], $query->getBindings());
    }

    public function test_evoaccess_without_login_company_does_not_expose_all_companies(): void
    {
        $query = $this->queryFor(true, '', 'ENG');
        $this->assertStringContainsString('0 = 1', $query->toSql());
        $this->assertSame([], $query->getBindings());
    }
}
