<?php

namespace Tests\Unit;

use App\Models\SysMenu;
use App\Services\SidebarMenuVisibility;
use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class SidebarMenuVisibilityTest extends TestCase
{
    private $previousApp;

    private SidebarMenuVisibility $visibility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApp = Facade::getFacadeApplication();
        $app = new Application(dirname(__DIR__, 2));
        $app->instance('config', new Repository);
        $router = new Router(new Dispatcher($app), $app);
        $app->instance('router', $router);
        $router->get('/available', fn () => null)->name('available');
        $router->get('/required/{id}', fn () => null)->name('needs.parameter');
        $router->getRoutes()->refreshNameLookups();
        $app->instance('url', new UrlGenerator($router->getRoutes(), Request::create('https://example.test')));
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
        $this->visibility = new SidebarMenuVisibility;
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousApp);
        Application::setInstance($this->previousApp);
        parent::tearDown();
    }

    private function menu(string $id, ?string $route = null, array $children = [], string $status = 'A'): SysMenu
    {
        $menu = new SysMenu(['menu_id' => $id, 'menu_name' => $id, 'menu_route' => $route, 'status' => $status]);
        $menu->setRelation('children', collect($children));

        return $menu;
    }

    public function test_missing_and_parameterized_routes_return_no_url(): void
    {
        $this->assertNull($this->visibility->routeUrl('dashboards.gm'));
        $this->assertNull($this->visibility->routeUrl('needs.parameter'));
        $this->assertNull($this->visibility->routeUrl(null));
        $this->assertSame('https://example.test/available', $this->visibility->routeUrl('available'));
    }

    public function test_missing_direct_menu_is_hidden_without_breaking_other_menus(): void
    {
        $root = $this->menu('ROOT', null, [$this->menu('GM', 'dashboards.gm'), $this->menu('AVAILABLE', 'available')]);
        $tree = $this->visibility->tree(collect([$root]), collect(['ROOT', 'GM', 'AVAILABLE']));
        $this->assertCount(1, $tree);
        $this->assertSame(['AVAILABLE'], $tree->first()->children->pluck('menu_id')->all());
        $this->assertSame('https://example.test/available', $tree->first()->children->first()->_sidebar_url);
        $this->assertCount(2, $root->children);
        $this->assertArrayNotHasKey('_sidebar_url', $root->children->last()->getAttributes());
    }

    public function test_submenus_and_empty_groups_are_removed(): void
    {
        $root = $this->menu('ROOT', null, [$this->menu('EMPTY', null, [$this->menu('MISSING', 'dashboards.gm')]), $this->menu('GROUP', null, [$this->menu('GOOD', 'available'), $this->menu('BAD', 'dashboards.gm')])]);
        $tree = $this->visibility->tree(collect([$root]), collect(['ROOT', 'EMPTY', 'MISSING', 'GROUP', 'GOOD', 'BAD']));
        $this->assertSame(['GROUP'], $tree->first()->children->pluck('menu_id')->all());
        $this->assertSame(['GOOD'], $tree->first()->children->first()->children->pluck('menu_id')->all());
        $empty = $this->menu('EMPTYROOT', null, [$this->menu('BAD', 'dashboards.gm')]);
        $this->assertCount(0, $this->visibility->tree(collect([$empty]), collect(['EMPTYROOT', 'BAD'])));
    }

    public function test_standalone_roots_and_permissions_are_preserved(): void
    {
        $tree = $this->visibility->tree(collect([$this->menu('MISSING', 'dashboards.gm'), $this->menu('GOOD', 'available'), $this->menu('DENIED', 'available'), $this->menu('INACTIVE', 'available', [], 'I')]), collect(['MISSING', 'GOOD', 'INACTIVE']));
        $this->assertSame(['GOOD'], $tree->pluck('menu_id')->all());
    }

    public function test_route_becomes_visible_when_deployment_registers_it(): void
    {
        $roots = collect([$this->menu('GM', 'dashboards.gm')]);
        $this->assertCount(0, $this->visibility->tree($roots, collect(['GM'])));
        $router = app('router');
        $router->get('/gm', fn () => null)->name('dashboards.gm');
        $router->getRoutes()->refreshNameLookups();
        $this->assertCount(1,$this->visibility->tree($roots,collect(['GM'])));
    }
}
