<?php

namespace Tests\Unit;

use App\Http\Controllers\SppbController;
use App\Models\MsInventory;
use App\Models\TrSPPBdetail;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class SppbInventoryFieldsTest extends TestCase
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

    private function resolve(array $fields = [], ?MsInventory $inventory = null, ?TrSPPBdetail $existing = null, ?string $defaultSite = 'DEFAULT'): array
    {
        $method = new ReflectionMethod(SppbController::class, 'resolveSppbInventoryFields');
        $method->setAccessible(true);

        return $method->invoke(new SppbController(), 'ITEM01', $fields, $inventory, $existing, ['DEFAULT', 'SELECTED'], $defaultSite, 3);
    }

    private function inventory(): MsInventory
    {
        return new MsInventory(['item_type' => 'GI', 'item_sub_type' => 'STOCK', 'item_category' => 'Material']);
    }

    public function test_missing_hidden_fields_are_filled_from_inventory_and_default_site(): void
    {
        $this->assertSame([
            'inventory_type' => 'GI',
            'inventory_sub_type' => 'STOCK',
            'inventory_category' => 'Material',
            'siteid' => 'DEFAULT',
        ], $this->resolve([], $this->inventory()));
    }

    public function test_inventory_master_prevents_misaligned_hidden_fields_overriding_classification(): void
    {
        $resolved = $this->resolve([
            'inventory_type' => 'NS', 'inventory_sub_type' => 'NONSTOCK',
            'inventory_category' => 'Other', 'siteid' => 'SELECTED',
        ], $this->inventory());

        $this->assertSame('GI', $resolved['inventory_type']);
        $this->assertSame('Material', $resolved['inventory_category']);
        $this->assertSame('SELECTED', $resolved['siteid']);
    }

    public function test_update_preserves_existing_fields_and_site_when_form_values_are_empty(): void
    {
        $existing = new TrSPPBdetail([
            'inventoryid' => 'ITEM01', 'inventory_type' => 'NS',
            'inventory_sub_type' => 'NONSTOCK', 'inventory_category' => 'Supplies', 'siteid' => 'SELECTED',
        ]);
        $resolved = $this->resolve(['siteid' => '-', 'inventory_type' => ''], null, $existing, null);

        $this->assertSame('NS', $resolved['inventory_type']);
        $this->assertSame('NONSTOCK', $resolved['inventory_sub_type']);
        $this->assertSame('Supplies', $resolved['inventory_category']);
        $this->assertSame('SELECTED', $resolved['siteid']);
    }

    public function test_changing_inventory_does_not_reuse_old_classification_or_site(): void
    {
        $existing = new TrSPPBdetail([
            'inventoryid' => 'OTHER', 'inventory_type' => 'NS',
            'inventory_sub_type' => 'NONSTOCK', 'inventory_category' => 'Other', 'siteid' => 'SELECTED',
        ]);
        $resolved = $this->resolve([], $this->inventory(), $existing);

        $this->assertSame('GI', $resolved['inventory_type']);
        $this->assertSame('DEFAULT', $resolved['siteid']);
    }

    public function test_complete_form_fields_can_fill_legacy_inventory_without_master_data(): void
    {
        $resolved = $this->resolve([
            'inventory_type' => ' NS ', 'inventory_sub_type' => 'NONSTOCK',
            'inventory_category' => 'Supplies', 'siteid' => 'SELECTED',
        ]);

        $this->assertSame('NS', $resolved['inventory_type']);
        $this->assertSame('SELECTED', $resolved['siteid']);
    }

    public function test_site_outside_selected_company_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->resolve(['siteid' => 'OTHER_COMPANY'], $this->inventory());
    }

    public function test_incomplete_inventory_is_rejected_with_row_specific_errors(): void
    {
        try {
            $this->resolve([], new MsInventory(['item_type' => 'NS']));
            $this->fail('Incomplete classification must not be saved.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('inventory_sub_type.3', $exception->errors());
            $this->assertArrayHasKey('inventory_category.3', $exception->errors());
        }
    }

    public function test_missing_site_and_default_site_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->resolve([], $this->inventory(), null, null);
    }
}
