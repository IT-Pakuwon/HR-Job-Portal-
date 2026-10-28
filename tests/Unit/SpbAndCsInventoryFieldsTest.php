<?php

namespace Tests\Unit;

use App\Http\Controllers\CanvassController;
use App\Http\Controllers\SpbController;
use App\Models\TrSPBdetail;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class SpbAndCsInventoryFieldsTest extends TestCase
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

    private function spbSite($requested = null, ?TrSPBdetail $existing = null, string $previousCompany = 'AW', $bu = null, ?string $defaultSite = 'DEFAULT'): string
    {
        $method = new ReflectionMethod(SpbController::class, 'resolveSpbSiteId');
        $method->setAccessible(true);
        return $method->invoke(new SpbController(), 'ITEM01', $requested, $existing, 'AW', $previousCompany, $bu, $defaultSite, 2);
    }

    private function csFields(array $payload = [], array $sources = [], $inventory = null, array $businessUnits = [], ?string $defaultSite = 'DEFAULT'): array
    {
        $method = new ReflectionMethod(CanvassController::class, 'resolveCsInventoryFields');
        $method->setAccessible(true);
        return $method->invoke(new CanvassController(), 'ITEM01', $payload, $sources, $inventory, collect($businessUnits), 'AW', $defaultSite, 2);
    }

    private function source(array $overrides = []): object
    {
        return (object) array_replace([
            'inventoryid' => 'ITEM01', 'inventory_type' => 'GI',
            'inventory_sub_type' => 'STOCK', 'inventory_category' => 'Material',
            'siteid' => 'SOURCE', 'budget_cpny_id' => 'AW', 'budget_business_unit_id' => 'AWMALL',
        ], $overrides);
    }

    private function inventory(): object
    {
        return (object) ['item_type' => 'NS', 'item_sub_type' => 'NONSTOCK', 'item_category' => 'Supplies'];
    }

    public function test_spb_preserves_selected_site_including_leading_zeroes(): void
    {
        $this->assertSame('0010', $this->spbSite(' 0010 '));
    }

    public function test_spb_empty_form_preserves_site_for_existing_inventory_and_company(): void
    {
        $existing = new TrSPBdetail(['inventoryid' => 'ITEM01', 'siteid' => 'EXISTING']);
        $this->assertSame('EXISTING', $this->spbSite('', $existing));
    }

    public function test_spb_changing_inventory_or_company_uses_new_fallback_site(): void
    {
        $existing = new TrSPBdetail(['inventoryid' => 'OTHER', 'siteid' => 'OLD']);
        $this->assertSame('DEFAULT', $this->spbSite(null, $existing));
        $existing->inventoryid = 'ITEM01';
        $this->assertSame('DEFAULT', $this->spbSite(null, $existing, 'EP'));
    }

    public function test_spb_uses_business_unit_site_before_company_default(): void
    {
        $bu = (object) ['ifca_entity_cd' => 'BU_SITE', 'solomon_cpny_id' => 'SOLOMON'];
        $this->assertSame('BU_SITE', $this->spbSite(null, null, 'AW', $bu));
        $bu->ifca_entity_cd = '-';
        $this->assertSame('SOLOMON', $this->spbSite(null, null, 'AW', $bu));
    }

    public function test_spb_missing_site_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->spbSite(null, null, 'AW', null, null);
    }

    public function test_cs_empty_payload_values_use_source_instead_of_saving_blanks(): void
    {
        $fields = $this->csFields(['inventory_type' => '', 'inventory_sub_type' => ' ', 'inventory_category' => null, 'siteid' => '-'], [$this->source()]);
        $this->assertSame(['inventory_type' => 'GI', 'inventory_sub_type' => 'STOCK', 'inventory_category' => 'Material', 'siteid' => 'SOURCE'], $fields);
    }

    public function test_cs_existing_detail_is_used_when_source_is_missing(): void
    {
        $fields = $this->csFields([], [null, $this->source()], null, [], null);
        $this->assertSame('SOURCE', $fields['siteid']);
        $this->assertSame('STOCK', $fields['inventory_sub_type']);
    }

    public function test_cs_falls_back_to_master_inventory_and_business_unit_site(): void
    {
        $fields = $this->csFields(['budget_business_unit_id' => 'AWMALL'], [], $this->inventory(), [
            'AWMALL' => (object) ['ifca_entity_cd' => '', 'solomon_cpny_id' => 'BU_SITE'],
        ]);
        $this->assertSame('NS', $fields['inventory_type']);
        $this->assertSame('NONSTOCK', $fields['inventory_sub_type']);
        $this->assertSame('Supplies', $fields['inventory_category']);
        $this->assertSame('BU_SITE', $fields['siteid']);
    }

    public function test_cs_wrong_inventory_or_company_source_is_not_reused(): void
    {
        foreach ([$this->source(['inventoryid' => 'OTHER']), $this->source(['budget_cpny_id' => 'EP'])] as $source) {
            $fields = $this->csFields([], [$source], $this->inventory());
            $this->assertSame('NS', $fields['inventory_type']);
            $this->assertSame('DEFAULT', $fields['siteid']);
        }
    }

    public function test_cs_valid_payload_values_are_preserved(): void
    {
        $fields = $this->csFields(['siteid' => '0010'], [$this->source()]);
        $this->assertSame('0010', $fields['siteid']);
    }

    public function test_cs_missing_classification_has_detail_specific_validation_errors(): void
    {
        try {
            $this->csFields();
            $this->fail('Missing inventory classification must not be saved.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('details.2.inventory_type', $exception->errors());
            $this->assertArrayHasKey('details.2.inventory_sub_type', $exception->errors());
            $this->assertArrayHasKey('details.2.inventory_category', $exception->errors());
        }
    }

    public function test_cs_missing_site_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->csFields([], [], $this->inventory(), [], null);
    }
}
