<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\SummaryPanels\SummaryLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Admin pages (Dashboard Setup > OM Summary / GM Summary) that set the layout of a Summary panel. */
class SummarySetupController extends Controller
{
    /** URL segment => panel key */
    protected const PANELS = ['om' => 'om-summary', 'gm' => 'gm-summary'];

    public function index(string $p)
    {
        $panel = $this->panel($p);

        return view('pages.summary-setup.index', [
            'p'       => $p,
            'panel'   => $panel,
            'config'  => SummaryLayout::panel($panel),
            'widgets' => SummaryLayout::resolved($panel),
            'spans'   => SummaryLayout::SPANS,
            'colors'  => SummaryLayout::COLORS,
        ]);
    }

    /** Save the whole layout: order = array order. */
    public function save(Request $request, string $p)
    {
        $panel = $this->panel($p);
        $ids = collect(SummaryLayout::catalog($panel))->pluck('id')->all();

        $data = $request->validate([
            'widgets'               => ['required', 'array', 'min:1'],
            'widgets.*.id'          => ['required', 'distinct', Rule::in($ids)],
            'widgets.*.is_visible'  => ['required', 'boolean'],
            'widgets.*.span'        => ['nullable', Rule::in(SummaryLayout::SPANS)],
            'widgets.*.title'       => ['nullable', 'string', 'max:100'],
            'widgets.*.color'       => ['nullable', Rule::in(SummaryLayout::COLORS)],
            'widgets.*.height'      => ['nullable', 'integer', 'between:150,600'],
        ]);

        $user = Auth::user()->username;
        $now = now();
        $rows = [];
        foreach ($data['widgets'] as $i => $w) {
            $rows[] = [
                'panel_key'  => $panel,
                'widget_id'  => $w['id'],
                'sort_no'    => $i + 1,
                'is_visible' => (bool) $w['is_visible'],
                'span'       => $w['span'] ?? null,
                'title'      => ($w['title'] ?? '') !== '' ? $w['title'] : null,
                'color'      => $w['color'] ?? null,
                'height'     => $w['height'] ?? null,
                'created_by' => $user, 'created_at' => $now,
                'updated_by' => $user, 'updated_at' => $now,
            ];
        }

        DB::connection(SummaryLayout::CONN)->transaction(function ($db) use ($panel, $rows) {
            $db->table(SummaryLayout::TABLE)->where('panel_key', $panel)->delete();
            $db->table(SummaryLayout::TABLE)->insert($rows);
        });

        return response()->json(['ok' => true]);
    }

    /** Drop the saved layout: the panel goes back to the config default. */
    public function reset(string $p)
    {
        DB::connection(SummaryLayout::CONN)->table(SummaryLayout::TABLE)->where('panel_key', $this->panel($p))->delete();

        return response()->json(['ok' => true]);
    }

    private function panel(string $p): string
    {
        abort_unless(isset(self::PANELS[$p]), 404);

        return self::PANELS[$p];
    }
}
