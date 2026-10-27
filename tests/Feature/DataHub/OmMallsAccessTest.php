<?php

namespace Tests\Feature\DataHub;

use App\Models\User;
use App\Services\DataHub\Om\OmMalls;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class OmMallsAccessTest extends TestCase
{
    private function signIn(array $roles, array $companies, bool $admin = false): OmMalls
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('isPrimaryAdmin')->andReturn($admin);
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => in_array($role, $roles, true));
        $user->shouldReceive('scopedCompanyIds')->andReturn($companies);
        auth()->setUser($user);

        return new OmMalls;
    }

    /** @dataProvider allCompanyRoles */
    public function test_all_company_roles_see_every_mall_and_default_to_aw_gandaria_city(string $role): void
    {
        $malls = $this->signIn([$role], ['AW']);

        $this->assertTrue($malls->canSwitchAll());
        $this->assertSame(['GC', 'KK', 'PMB', 'PBM'], array_keys($malls->allowed()));
        $this->assertSame('GC', $malls->default());
        $this->assertSame('AW - Gandaria City', $malls->options()['GC']);
    }

    public static function allCompanyRoles(): array
    {
        return [['DIRECTORACCESS'], ['DASH2ACCESS']];
    }

    public function test_primary_admin_sees_every_mall(): void
    {
        $this->assertSame(['GC', 'KK', 'PMB', 'PBM'], array_keys($this->signIn([], [], admin: true)->allowed()));
    }

    public function test_omaccess_only_sees_the_malls_of_the_assigned_companies(): void
    {
        $this->assertFalse($this->signIn(['OMACCESS'], ['AW'])->canSwitchAll());
        $this->assertSame(['GC'], array_keys($this->signIn(['OMACCESS'], ['AW'])->allowed()));
        $this->assertSame(['KK', 'PMB'], array_keys($this->signIn(['OMACCESS'], ['ep', 'GPS'])->allowed()));
        $this->assertSame('KK', $this->signIn(['OMACCESS'], ['EP'])->default());
    }

    public function test_user_with_no_matching_company_gets_a_403(): void
    {
        $malls = $this->signIn(['OMACCESS'], ['XX']);

        $this->assertSame([], $malls->allowed());
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Tidak ada mall yang dapat diakses.');
        $malls->default();
    }

    public function test_resolve_normalises_and_enforces_the_scope(): void
    {
        $malls = $this->signIn(['OMACCESS'], ['EP']);

        $this->assertSame('KK', $malls->resolve('kk'));
        $this->assertSame('KK', $malls->resolve(null), 'no mall given -> the default');
        $this->assertSame('KK', $malls->resolve('  '));

        try {
            $malls->resolve('GC');
            $this->fail('a mall outside the scope must be refused');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        try {
            $malls->resolve('ZZ');
            $this->fail('an unknown mall must be refused');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public function test_resolve_or_default_never_throws_for_stale_saved_state(): void
    {
        $malls = $this->signIn(['OMACCESS'], ['EP']);

        $this->assertSame('KK', $malls->resolveOrDefault('GC'));
        $this->assertSame('KK', $malls->resolveOrDefault('nonsense'));
        $this->assertSame('KK', $malls->resolveOrDefault(null));
    }

    public function test_the_mall_code_sql_maps_every_configured_mall_name(): void
    {
        $sql = (new OmMalls)->codeCaseSql();

        foreach (config('datahub.om.malls') as $code => $mall) {
            $this->assertStringContainsString("WHEN mall = '{$mall['name']}' THEN '{$code}'", $sql);
        }
        $this->assertStringEndsWith('ELSE mall END', $sql);
    }
}
