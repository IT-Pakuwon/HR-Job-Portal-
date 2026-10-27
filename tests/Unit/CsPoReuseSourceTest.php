<?php

namespace Tests\Unit;

use App\Http\Controllers\CanvassController;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\PostgresConnection;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CsPoReuseSourceTest extends TestCase
{
    private $previousResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousResolver = Model::getConnectionResolver();
        Model::setConnectionResolver(new ConnectionResolver([
            'pgsql' => new PostgresConnection(function () {
                throw new \RuntimeException('This test must not access a database.');
            }),
        ]));
    }

    protected function tearDown(): void
    {
        if ($this->previousResolver) {
            Model::setConnectionResolver($this->previousResolver);
        } else {
            Model::unsetConnectionResolver();
        }
        parent::tearDown();
    }

    private function sourceQuery(?string $po, string $company = 'GPS')
    {
        $method = new ReflectionMethod(CanvassController::class, 'poReuseSourceQuery');
        $method->setAccessible(true);

        return $method->invoke(new CanvassController(), 'CS26100389', $company, $po);
    }

    public function test_reuse_uses_po_and_company_instead_of_intermediate_cs(): void
    {
        $query = $this->sourceQuery('1000000892')->lockForUpdate();

        $this->assertSame(['GPS', '1000000892'], $query->getBindings());
        $this->assertStringNotContainsString('"csid" =', $query->toSql());
        $this->assertStringContainsString('"cpny_id" = ?', $query->toSql());
        $this->assertStringContainsString('"ponbr" = ?', $query->toSql());
        $this->assertStringContainsString('"deleted_at" is null', $query->toSql());
        $this->assertStringContainsString('for update', $query->toSql());
    }

    public function test_legacy_reuse_without_po_keeps_cs_and_company_filters(): void
    {
        foreach ([null, '', '   '] as $po) {
            $query = $this->sourceQuery($po);
            $this->assertSame(['GPS', 'CS26100389'], $query->getBindings());
            $this->assertStringContainsString('"csid" = ?', $query->toSql());
            $this->assertStringNotContainsString('"ponbr" =', $query->toSql());
        }
    }

    public function test_po_lookup_remains_company_scoped_and_trims_po(): void
    {
        $this->assertSame(['EP', '1000000892'], $this->sourceQuery(' 1000000892 ', 'EP')->getBindings());
        $this->assertSame(['', '1000000892'], $this->sourceQuery('1000000892', '')->getBindings());
    }
}
