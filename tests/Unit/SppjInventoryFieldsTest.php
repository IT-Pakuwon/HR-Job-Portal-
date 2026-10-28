<?php

namespace Tests\Unit;

use App\Http\Controllers\SppjController;
use App\Models\MsInventory;
use App\Models\TrSPPJdetail;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class SppjInventoryFieldsTest extends TestCase
{
    private $previousApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApplication = Facade::getFacadeApplication();
        $container = new Container();
        $container->instance('validator', new Factory(new Translator(new ArrayLoader(), 'en')));
        Facade::clearResolvedInstance('validator');
        Facade::setFacadeApplication($container);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstance('validator');
        Facade::setFacadeApplication($this->previousApplication);
        parent::tearDown();
    }

    private function resolve(array $fields = [], ?MsInventory $inventory = null, ?TrSPPJdetail $existing = null, ?string $buSite = null, ?string $defaultSite = 'DEFAULT'): array
    {
        $method = new ReflectionMethod(SppjController::class, 'resolveSppjInventoryFields');
        $method->setAccessible(true);

        return $method->invoke(new SppjController(), 'ITEM01', $fields, $inventory, $existing, $buSite, $defaultSite, 2);
    }

    private function inventory(): MsInventory
    {
        return new MsInventory(['item_type' => 'SE', 'item_sub_type' => 'JASA', 'item_category' => 'Jasa']);
    }

    private function existing(): TrSPPJdetail
    {
        return new TrSPPJdetail([
            'inventoryid' => 'ITEM01', 'inventory_type' => 'SE',
            'inventory_sub_type' => 'JASA', 'inventory_category' => 'Jasa',
            'budget_cpny_id' => 'AW', 'budget_business_unit_id' => 'AWMALL', 'siteid' => 'EXISTING',
        ]);
    }

    public function test_missing_hidden_fields_are_filled_from_master_and_company_default(): void
    {
        $this->assertSame([
            'inventory_type' => 'SE', 'inventory_sub_type' => 'JASA',
            'inventory_category' => 'Jasa', 'siteid' => 'DEFAULT',
        ], $this->resolve([], $this->inventory()));
    }

    public function test_business_unit_site_has_priority_over_existing_and_default_site(): void
    {
        $resolved = $this->resolve(['cpny_id' => 'AW', 'business_unit_id' => 'AWMALL'], $this->inventory(), $this->existing(), 'BU_SITE');
        $this->assertSame('BU_SITE', $resolved['siteid']);
    }

    public function test_existing_fields_and_site_are_preserved_for_same_inventory_company_and_bu(): void
    {
        $resolved = $this->resolve(['cpny_id' => 'AW', 'business_unit_id' => 'AWMALL'], null, $this->existing(), null, null);
        $this->assertSame('SE', $resolved['inventory_type']);
        $this->assertSame('JASA', $resolved['inventory_sub_type']);
        $this->assertSame('Jasa', $resolved['inventory_category']);
        $this->assertSame('EXISTING', $resolved['siteid']);
    }

    public function test_changing_company_or_bu_does_not_reuse_old_site(): void
    {
        foreach ([['cpny_id' => 'EP', 'business_unit_id' => 'AWMALL'], ['cpny_id' => 'AW', 'business_unit_id' => 'OTHER']] as $fields) {
            $resolved = $this->resolve($fields, $this->inventory(), $this->existing());
            $this->assertSame('DEFAULT', $resolved['siteid']);
        }
    }

    public function test_changing_inventory_does_not_reuse_old_metadata_or_site(): void
    {
        $existing = $this->existing();
        $existing->inventoryid = 'OTHER';
        $existing->inventory_category = 'Other';
        $resolved = $this->resolve(['cpny_id' => 'AW', 'business_unit_id' => 'AWMALL'], $this->inventory(), $existing);
        $this->assertSame('Jasa', $resolved['inventory_category']);
        $this->assertSame('DEFAULT', $resolved['siteid']);
    }

    public function test_master_classification_takes_priority_over_misaligned_form_values(): void
    {
        $resolved = $this->resolve(['inventory_type' => 'NS', 'inventory_sub_type' => 'NONSTOCK', 'inventory_category' => 'Wrong'], $this->inventory());
        $this->assertSame('SE', $resolved['inventory_type']);
        $this->assertSame('JASA', $resolved['inventory_sub_type']);
        $this->assertSame('Jasa', $resolved['inventory_category']);
    }

    public function test_complete_form_can_fill_legacy_inventory_without_master_data(): void
    {
        $resolved = $this->resolve(['inventory_type' => ' SE ', 'inventory_sub_type' => 'JASA', 'inventory_category' => 'Jasa']);
        $this->assertSame('SE', $resolved['inventory_type']);
        $this->assertSame('DEFAULT', $resolved['siteid']);
    }

    public function test_placeholder_bu_site_falls_back_to_default(): void
    {
        $resolved = $this->resolve([], $this->inventory(), null, '-');
        $this->assertSame('DEFAULT', $resolved['siteid']);
    }

    public function test_incomplete_classification_is_rejected_with_row_errors(): void
    {
        try {
            $this->resolve([], new MsInventory(['item_type' => 'SE']));
            $this->fail('Incomplete classification must not be saved.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('inventory_sub_type.2', $exception->errors());
            $this->assertArrayHasKey('inventory_category.2', $exception->errors());
        }
    }

    public function test_missing_site_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->resolve([], $this->inventory(), null, null, null);
    }
}
