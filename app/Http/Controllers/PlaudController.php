<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\MsPlaudAccount;
use App\Models\TrPlaudMeeting;
use App\Services\Dashboard\PlaudScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlaudController extends Controller
{
    /** Rows of the "Plaud" dashboard panel: recordings of the accounts mapped to the user's companies on that dashboard. */
    public function panelJson(Request $request)
    {
        $accounts = PlaudScope::accounts($request->query('dash'), $request->user());
        if (!$accounts) {
            return response()->json(['data' => []]);
        }

        // never select transcript_content: it is tens of thousands of characters per row
        $rows = TrPlaudMeeting::whereIn('plaud_username', array_keys($accounts))
            ->withCount('summaries')
            ->orderByDesc('meeting_date')->orderByDesc('id')->limit(300)
            ->get(['id', 'plaud_file_id', 'plaud_username', 'meeting_title', 'meeting_date', 'duration_seconds', 'sync_status']);

        return response()->json(['data' => $rows->map(function ($m) use ($accounts) {
            $secs = (int) $m->duration_seconds;

            return [
                'id'          => $m->id,
                'title'       => $m->meeting_title,
                'date'        => $m->meeting_date?->format('Y-m-d'),
                'duration'    => $secs >= 3600 ? intdiv($secs, 3600) . 'h ' . intdiv($secs % 3600, 60) . 'm' : max(1, intdiv($secs, 60)) . 'm',
                'account'     => $accounts[$m->plaud_username] ?? null,
                'has_summary' => $m->summaries_count > 0 && $m->sync_status === 'SUCCESS',
            ];
        })->values()]);
    }

    /** Summary (and action items) of one recording, only if its account is one the user may read on that dashboard. */
    public function summary(Request $request, int $id)
    {
        $accounts = PlaudScope::accounts($request->query('dash'), $request->user());
        $m = $accounts ? TrPlaudMeeting::whereIn('plaud_username', array_keys($accounts))->find($id, ['id', 'plaud_file_id', 'meeting_title', 'sync_status']) : null;
        if (!$m) {
            return response()->json(['message' => 'Recording not found.'], 404);
        }

        $summaries = $m->summaries()->orderBy('id')
            ->get(['summary_title', 'summary_type', 'summary_content', 'summary_format', 'action_items'])
            ->filter(fn ($s) => trim((string) $s->summary_content) !== '' || !empty($s->action_items))
            ->map(fn ($s) => [
                'title'        => $s->summary_title,
                'type'         => $s->summary_type,
                'content'      => $s->summary_content,
                'action_items' => array_values((array) ($s->action_items ?? [])),
            ])->values();

        return response()->json([
            'summaries' => $summaries,
            'message'   => $summaries->isEmpty() ? ($m->sync_status === 'SUCCESS' ? 'No summary for this recording.' : 'Summary not ready yet. Plaud is still syncing it.') : null,
        ]);
    }

    /** Admin (Dashboard Menu): the company → Plaud account rows of one dashboard, plus the choices. */
    public function adminShow(string $dash)
    {
        $dash = $this->dashOrFail($dash);

        return response()->json([
            'rows' => DB::connection(PlaudScope::CONN)->table(PlaudScope::TABLE)->where('dash_cd', $dash)->orderBy('cpny_id')
                ->get(['cpny_id', 'plaud_account_id'])->map(fn ($r) => ['cpny_id' => $r->cpny_id, 'plaud_account_id' => (int) $r->plaud_account_id])->values(),
            'companies' => MsCompany::orderBy('cpny_id')->get(['cpny_id', 'cpny_name'])->map(fn ($c) => ['id' => $c->cpny_id, 'name' => $c->cpny_name])->values(),
            // id + label only: nothing else about the Plaud login leaves the server
            'accounts' => MsPlaudAccount::where('status', 'A')->orderBy('plaud_name')->get(['id', 'plaud_name', 'plaud_username'])
                ->map(fn ($a) => ['id' => $a->id, 'name' => trim(($a->plaud_name ?: 'Plaud account') . ($a->plaud_username ? ' — ' . $a->plaud_username : ''))])->values(),
        ]);
    }

    /** Replace the company → account rows of one dashboard. */
    public function adminSave(Request $request, string $dash)
    {
        $dash = $this->dashOrFail($dash);

        $data = $request->validate([
            'rows'                    => ['present', 'array', 'max:100'],
            'rows.*.cpny_id'          => ['required', 'string', 'max:30'],
            'rows.*.plaud_account_id' => ['required', 'integer', 'min:0'], // 0 = every Plaud account
        ]);

        // a company can have several accounts on one dashboard; only the exact same pair twice is dropped
        $rows = collect($data['rows'])->unique(fn ($r) => $r['cpny_id'] . '|' . $r['plaud_account_id'])->values();
        // company '*' = every company (no check against ms_company)
        $companyIds = $rows->pluck('cpny_id')->reject(fn ($c) => $c === PlaudScope::ALL_COMPANIES)->unique();
        $validCompanies = MsCompany::whereIn('cpny_id', $companyIds)->pluck('cpny_id');
        $accountIds = $rows->pluck('plaud_account_id')->map(fn ($i) => (int) $i)->filter(fn ($i) => $i > 0)->unique();
        $validAccounts = MsPlaudAccount::whereIn('id', $accountIds)->where('status', 'A')->pluck('id');
        if ($validCompanies->count() !== $companyIds->count() || $validAccounts->unique()->count() !== $accountIds->count()) {
            return response()->json(['message' => 'A company or Plaud account no longer exists. Reload the page.'], 422);
        }

        $db = DB::connection(PlaudScope::CONN);
        $by = Auth::user()->username;
        $db->transaction(function () use ($db, $dash, $rows, $by) {
            $existing = $db->table(PlaudScope::TABLE)->where('dash_cd', $dash)->get(['id', 'cpny_id', 'plaud_account_id'])
                ->keyBy(fn ($r) => $r->cpny_id . '|' . $r->plaud_account_id);

            $keep = [];
            foreach ($rows as $r) {
                $row = $existing->get($r['cpny_id'] . '|' . $r['plaud_account_id']);
                $keep[] = $row
                    ? tap($row->id, fn ($id) => $db->table(PlaudScope::TABLE)->where('id', $id)->update(['status' => 'A', 'updated_by' => $by, 'updated_at' => now()]))
                    : $db->table(PlaudScope::TABLE)->insertGetId(['dash_cd' => $dash, 'cpny_id' => $r['cpny_id'], 'plaud_account_id' => $r['plaud_account_id'],
                        'status' => 'A', 'created_by' => $by, 'created_at' => now()], 'id');
            }
            $db->table(PlaudScope::TABLE)->where('dash_cd', $dash)->whereNotIn('id', $keep)->delete();
        });

        return response()->json(['ok' => true]);
    }

    private function dashOrFail(string $dash): string
    {
        $dash = strtoupper($dash);
        abort_unless(array_key_exists($dash, MultiDashboardController::DASHBOARD_COMPONENTS), 404);

        return $dash;
    }
}
