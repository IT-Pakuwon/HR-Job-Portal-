<?php

namespace App\Http\Controllers;

use App\Models\TrApproval;
use App\Models\TrCS;
use App\Models\ViewDasAll;
use App\Models\ViewJobApply;
use App\Models\ViewtrPurch;
use App\Models\Viewtrxall;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;

class ApprovalDashboardController extends Controller
{
    public function summaryJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $waiting = $this->getApprovalCollection($request, 'P');

        $approved = $this->getApprovalCollection($request, 'A');

        $today = now()->toDateString();
        $threeDaysAgo = now()->subDays(3)->toDateString();

        return response()->json([
            'data' => [
                'waiting' => $waiting->count(),

                'long_waiting' => $waiting
                    ->filter(fn ($row) => !empty($row['docdate'])
                        && substr($row['docdate'], 0, 10) <= $threeDaysAgo
                    )
                    ->count(),

                'approved_today' => $approved
                    ->filter(fn ($row) => !empty($row['docdate'])
                        && substr($row['docdate'], 0, 10) === $today
                    )
                    ->count(),
            ],
        ]);
    }

    public function waitingJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'data' => $this->getApprovalCollection($request, 'P'),
        ]);
    }

    public function approveJson(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'data' => $this->getApprovalCollection($request, 'A'),
        ]);
    }

    private function getApprovalCollection(Request $request, string $status): Collection
    {
        $user = $request->user();

        if (!$user) {
            return collect();
        }

        $doctype = strtoupper(
            trim((string) $request->get('doctype', ''))
        );

        $doctype = $doctype === 'ALL'
            ? ''
            : $doctype;

        $trxM = new Viewtrxall();
        $appM = new ViewJobApply();
        $aprM = new TrApproval();
        $purchM = new ViewtrPurch();
        $dasM = new ViewDasAll();

        $trxConn = $trxM->getConnectionName() ?: config('database.default');
        $appConn = $appM->getConnectionName() ?: config('database.default');
        $aprConn = $aprM->getConnectionName() ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');
        $dasConn = $dasM->getConnectionName() ?: config('database.default');

        $tblTrx = $trxM->getTable();
        $tblApp = $appM->getTable();
        $tblApr = $aprM->getTable();
        $tblPurch = $purchM->getTable();
        $tblDas = $dasM->getTable();

        $username = strtolower(
            trim((string) $user->username)
        );

        $approvalRows = DB::connection($aprConn)
            ->table($tblApr)
            ->select(
                'refnbr',
                'aprv_datebefore'
            )
            ->whereRaw(
                "(',' || lower(regexp_replace(coalesce(aprv_username,''), '\s+', '', 'g')) || ',') like ?",
                ['%,'.$username.',%']
            )
            ->where('status', $status)
            ->whereNotNull('aprv_datebefore')
            ->get();

        if ($approvalRows->isEmpty()) {
            return collect();
        }

        $approvalMap = $approvalRows
            ->groupBy(fn ($r) => strtoupper(trim($r->refnbr)))
            ->map(function ($rows, $refnbr) {
                $latest = collect($rows)
                    ->sortByDesc(fn ($r) => $r->aprv_datebefore)
                    ->first();

                return [
                    'refnbr' => $refnbr,
                    'aprv_datebefore' => $latest->aprv_datebefore,
                ];
            });

        $docids = $approvalMap->keys()->values();

        if ($doctype !== '') {
            $docids = $docids
                ->filter(function ($docid) use ($doctype) {
                    if (!preg_match('/^[A-Z]+/', $docid, $m)) {
                        return false;
                    }

                    return $m[0] === $doctype;
                })
                ->values();
        }

        if ($docids->isEmpty()) {
            return collect();
        }

        $selectCols = [
            'id',
            'docdate',
            'cpnyid',
            'departementid',
            'infohd',
            'url',
            'docid',
        ];

        $fetch = function (
            string $conn,
            string $table
        ) use (
            $docids,
            $selectCols
        ) {
            $out = collect();

            foreach ($docids->chunk(1200) as $chunk) {
                $rows = DB::connection($conn)
                    ->table($table)
                    ->whereIn('docid', $chunk->all())
                    ->select($selectCols)
                    ->get();

                $out = $out->concat($rows);
            }

            return $out;
        };

        $t0 = microtime(true);

        $data = collect()
            ->concat($fetch($trxConn, $tblTrx))
            ->concat($fetch($appConn, $tblApp));

        try {
            $data = $data->concat(
                $fetch($purchConn, $tblPurch)
            );
        } catch (\Throwable $e) {
            Log::warning('approvalJson purchasing failed', [
                'err' => $e->getMessage(),
            ]);
        }

        try {
            $data = $data->concat(
                $fetch($dasConn, $tblDas)
            );
        } catch (\Throwable $e) {
            Log::warning('approvalJson das failed', [
                'err' => $e->getMessage(),
            ]);
        }

        $data = $data
            ->map(function ($r) use ($approvalMap, $status) {
                $docidKey = strtoupper(
                    trim($r->docid)
                );

                $approval = $approvalMap->get($docidKey);

                return [
                    'hid' => Hashids::encode($r->id),
                    'docid' => $r->docid,
                    'docdate' => $approval['aprv_datebefore'] ?? null,
                    'cpnyid' => $r->cpnyid,
                    'departementid' => $r->departementid,
                    'infohd' => $r->infohd,
                    'url' => $r->url,
                    'status' => $status,
                ];
            })
            ->sortByDesc(fn ($r) => $r['docdate'] ?? '')
            ->values();

        $csDocids = $data
            ->filter(fn ($r) => str_starts_with(strtoupper($r['docid'] ?? ''), 'CS'))
            ->pluck('docid')
            ->values();

        if ($csDocids->isNotEmpty()) {
            $csM = new TrCS();
            $imBudgetMap = DB::connection($csM->getConnectionName() ?: config('database.default'))
                ->table($csM->getTable())
                ->whereIn('csid', $csDocids->all())
                ->select('csid', 'flag_imbudget', 'imbudgetid', 'status_imbudget')
                ->get()
                ->keyBy(fn ($r) => strtoupper(trim($r->csid)));

            $data = $data->map(function ($r) use ($imBudgetMap) {
                $cs = $imBudgetMap->get(strtoupper(trim($r['docid'] ?? '')));
                $r['flag_imbudget']   = $cs?->flag_imbudget   ?? null;
                $r['imbudgetid']      = $cs?->imbudgetid      ?? null;
                $r['status_imbudget'] = $cs?->status_imbudget ?? null;
                return $r;
            })->values();
        }

        Log::info('approvalDashboard', [
            'user' => $user->username,
            'status' => $status,
            'doctype' => $doctype ?: 'ALL',
            'rows' => $data->count(),
            'ms' => (int) ((microtime(true) - $t0) * 1000),
        ]);

        return $data;
    }
}
