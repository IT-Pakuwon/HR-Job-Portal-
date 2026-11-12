<?php

namespace Tests\Unit;

use App\Http\Controllers\MasterKontrakBqController;
use App\Models\User;
use App\Models\MsKontrakCategory;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\PresenceVerifierInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MasterKontrakBqTest extends TestCase
{
    public function test_all_endpoints_deny_users_without_purchaccess_before_reading_database(): void
    {
        $previous = Container::getInstance();
        new Application(dirname(__DIR__, 2));
        try {
            foreach ([null, false] as $hasRole) {
                $request = new Request();
                if ($hasRole !== null) {
                    $user = $this->createMock(User::class);
                    $user->method('hasRole')->with('PURCHACCESS')->willReturn(false);
                    $request->setUserResolver(fn () => $user);
                }
                foreach (['index', 'json', 'store', 'update', 'categoriesJson', 'storeCategory', 'updateCategory'] as $action) {
                    try {
                        $controller = new MasterKontrakBqController();
                        in_array($action, ['update', 'updateCategory'], true)
                            ? $controller->{$action}($request, 1) : $controller->{$action}($request);
                        $this->fail('Unauthorized action was allowed: '.$action);
                    } catch (HttpException $e) {
                        $this->assertSame(403, $e->getStatusCode());
                    }
                }
            }
        } finally {
            Container::setInstance($previous);
        }
    }

    public function test_purchaccess_is_allowed_by_server_authorization(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('hasRole')->with('PURCHACCESS')->willReturn(true);
        $request = new Request();
        $request->setUserResolver(fn () => $user);
        $method = new ReflectionMethod(MasterKontrakBqController::class, 'authorizeAccess');
        $method->setAccessible(true);
        $this->assertNull($method->invoke(new MasterKontrakBqController(), $request));
    }

    private function validator(array $data, int $categoryCount = 1)
    {
        $presence = $this->createMock(PresenceVerifierInterface::class);
        $presence->method('getCount')->willReturn($categoryCount);
        $factory = new Factory(new Translator(new ArrayLoader(), 'en'));
        $factory->setPresenceVerifier($presence);
        $method = new ReflectionMethod(MasterKontrakBqController::class, 'rules');
        $method->setAccessible(true);
        return $factory->make($data, $method->invoke(new MasterKontrakBqController()));
    }

    private function validData(): array
    {
        return [
            'kontrak_bq_id' => 'BQ01', 'kontrakcategory' => 'MSTP', 'kontrak_bq_type' => 'Material',
            'kontrak_bq_line_no' => 1, 'kontrak_bq_descr' => 'Maintenance', 'kontrak_bq_uom' => 'LOT',
            'kontrak_duration_qty' => 12, 'status' => 'A',
        ];
    }

    public function test_valid_create_and_edit_payloads_accept_active_and_inactive_status(): void
    {
        foreach (['A', 'I'] as $status) {
            $this->assertTrue($this->validator(array_replace($this->validData(), ['status' => $status]))->passes());
        }
    }

    public function test_invalid_numbers_status_and_missing_fields_are_rejected(): void
    {
        $validator = $this->validator(array_replace($this->validData(), [
            'kontrak_bq_line_no' => 0, 'kontrak_duration_qty' => -1, 'status' => 'P', 'kontrak_bq_descr' => '',
        ]));
        $this->assertTrue($validator->fails());
        foreach (['kontrak_bq_line_no', 'kontrak_duration_qty', 'status', 'kontrak_bq_descr'] as $field) {
            $this->assertTrue($validator->errors()->has($field));
        }
        $this->assertTrue($this->validator($this->validData(), 0)->errors()->has('kontrakcategory'));
    }

    private function categoryValidator(array $data, ?MsKontrakCategory $category = null, int $count = 0)
    {
        $presence = $this->createMock(PresenceVerifierInterface::class);
        $presence->method('getCount')->willReturn($count);
        $factory = new Factory(new Translator(new ArrayLoader(), 'en'));
        $factory->setPresenceVerifier($presence);
        $method = new ReflectionMethod(MasterKontrakBqController::class, 'categoryRules');
        $method->setAccessible(true);
        return $factory->make($data, $method->invoke(new MasterKontrakBqController(), $category));
    }

    public function test_category_create_requires_unique_code_description_and_valid_status(): void
    {
        $data = ['kontrakcategory' => 'MSTP', 'kontrakcategory_descr' => 'Maintenance STP', 'status' => 'A'];
        $this->assertTrue($this->categoryValidator($data)->passes());
        $this->assertTrue($this->categoryValidator($data, null, 1)->errors()->has('kontrakcategory'));
        $invalid = $this->categoryValidator(array_replace($data, ['kontrakcategory_descr' => '', 'status' => 'P']));
        $this->assertTrue($invalid->errors()->has('kontrakcategory_descr'));
        $this->assertTrue($invalid->errors()->has('status'));
    }

    public function test_category_edit_allows_description_and_status_but_not_referenced_code_changes(): void
    {
        $category = new MsKontrakCategory(['kontrakcategory' => 'MSTP']);
        $data = ['kontrakcategory' => 'MSTP', 'kontrakcategory_descr' => 'Updated description', 'status' => 'I'];
        $this->assertTrue($this->categoryValidator($data, $category)->passes());
        $this->assertTrue($this->categoryValidator(array_replace($data, ['kontrakcategory' => 'OTHER']), $category)->errors()->has('kontrakcategory'));
    }
}
