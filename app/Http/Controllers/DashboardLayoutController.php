<?php

namespace App\Http\Controllers;

use App\Models\DashboardMenu;
use App\Services\Dashboard\DashboardLayout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Dashboard Setup: arrange the blocks of each dashboard and set its heading / refresh (admin only; the same for everyone assigned to it). */
class DashboardLayoutController extends Controller
{
    public function index()
    {
        $names = DB::connection(DashboardLayout::CONN)->table('sys_screen')->where('application_id', 'DASHBOARD')->pluck('screen_name', 'screen_id');

        $dashboards = collect(array_keys(MultiDashboardController::DASHBOARD_COMPONENTS))->map(function ($cd) use ($names) {
            // what applies to this dashboard, judged from the tiles the signed-in admin can see on it
            $items = DashboardMenu::forDashboard($cd, auth()->user());
            $has = DashboardLayout::flags($cd, $items);
            $default = DashboardLayout::defaultLayout($has);
            $stored = DashboardLayout::stored($cd);
            $storedRows = DashboardLayout::storedRows($cd);
            $def = DashboardLayout::DEFAULT_HEADERS[$cd] ?? [$names->get($cd, $cd), ''];

            return [
                'code'     => $cd,
                'name'     => $names->get($cd, $cd),
                'has'      => $has,
                'tiles'    => $items->count(),
                'custom'   => $stored !== null,                    // anything saved: an arrangement and / or settings
                'hasRows'  => isset($stored['rows']),              // an arrangement is saved
                'layout'   => $storedRows ? ['rows' => $storedRows] : $default,
                'default'  => $default,
                'settings' => DashboardLayout::settings($cd),
                'headerDefault' => ['title' => $def[0], 'subtitle' => $def[1]],
            ];
        })->values();

        $catalog = collect(DashboardLayout::BLOCKS)->map(fn ($b, $id) => ['id' => $id] + $b)->values();

        return view('pages.dashboard-setup.index', [
            'dashboards' => $dashboards,
            'catalog'    => $catalog,
            'spans'      => DashboardLayout::SPANS,
            'maxRows'    => DashboardLayout::MAX_ROWS,
            'refreshChoices' => DashboardLayout::REFRESH_CHOICES,
            'limits'     => ['title' => DashboardLayout::TITLE_MAX, 'subtitle' => DashboardLayout::SUBTITLE_MAX],
        ]);
    }

    public function save(Request $request, string $dash): JsonResponse
    {
        $this->assertDashboard($dash);
        $data = $request->validate([
            'rows'     => ['nullable', 'array', 'min:1', 'max:' . DashboardLayout::MAX_ROWS],
            'settings' => ['nullable', 'array'],
        ]);

        try {
            $rows = isset($data['rows']) ? DashboardLayout::sanitize($data['rows']) : null;
            $settings = DashboardLayout::sanitizeSettings($data['settings'] ?? []);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        DashboardLayout::save($dash, $rows, $settings, Auth::user()->username ?? null);
        $stored = DashboardLayout::stored($dash);
        $storedRows = DashboardLayout::storedRows($dash);

        return response()->json([
            'ok'       => true,
            'hasRows'  => isset($stored['rows']),
            'layout'   => $storedRows ? ['rows' => $storedRows] : null,
            'settings' => DashboardLayout::settings($dash),
        ]);
    }

    public function reset(string $dash): JsonResponse
    {
        $this->assertDashboard($dash);
        DashboardLayout::reset($dash);

        return response()->json(['ok' => true]);
    }

    private function assertDashboard(string $dash): void
    {
        abort_unless(array_key_exists(strtoupper($dash), MultiDashboardController::DASHBOARD_COMPONENTS), 404);
    }
}
