<?php

namespace App\Http\Controllers;

use App\Models\TrApproval;
use App\Models\TrBookingCar;
use App\Models\TrCS;
use App\Models\TrVoucherTaxi;
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

        // BCR (Booking Car) — may not exist in v_all_das; fetch directly
        try {
            $bcrDocids = $docids->filter(fn ($id) => str_starts_with($id, 'BCR'))->values();
            if ($bcrDocids->isNotEmpty()) {
                $bcrM   = new TrBookingCar();
                $bcrConn  = $bcrM->getConnectionName() ?: config('database.default');
                $bcrTable = $bcrM->getTable();
                $bcrRows  = collect();
                foreach ($bcrDocids->chunk(1200) as $chunk) {
                    $bcrRows = $bcrRows->concat(
                        DB::connection($bcrConn)
                            ->table($bcrTable)
                            ->whereIn('docid', $chunk->all())
                            ->select(
                                'id',
                                'booking_date as docdate',
                                'cpny_id_site as cpnyid',
                                'department_id as departementid',
                                'purpose_descr as infohd',
                                'docid'
                            )
                            ->get()
                            ->map(fn ($r) => (object) array_merge((array) $r, ['url' => '/showbookingcar']))
                    );
                }
                $data = $data->concat($bcrRows);
            }
        } catch (\Throwable $e) {
            Log::warning('approvalJson BCR fetch failed', [
                'err' => $e->getMessage(),
            ]);
        }

        // VCR (Voucher Taxi) — may not exist in v_all_das; fetch directly
        try {
            $vcrDocids = $docids->filter(fn ($id) => str_starts_with($id, 'VCR'))->values();
            if ($vcrDocids->isNotEmpty()) {
                $vcrM   = new TrVoucherTaxi();
                $vcrConn  = $vcrM->getConnectionName() ?: config('database.default');
                $vcrTable = $vcrM->getTable();
                $vcrRows  = collect();
                foreach ($vcrDocids->chunk(1200) as $chunk) {
                    $vcrRows = $vcrRows->concat(
                        DB::connection($vcrConn)
                            ->table($vcrTable)
                            ->whereIn('docid', $chunk->all())
                            ->select(
                                'id',
                                'voucher_date as docdate',
                                'cpny_id as cpnyid',
                                'department_id as departementid',
                                'purpose_descr as infohd',
                                'docid'
                            )
                            ->get()
                            ->map(fn ($r) => (object) array_merge((array) $r, ['url' => '/showvouchertaxi']))
                    );
                }
                $data = $data->concat($vcrRows);
            }
        } catch (\Throwable $e) {
            Log::warning('approvalJson VCR fetch failed', [
                'err' => $e->getMessage(),
            ]);
        }

        // CS (Comparison Sheet) — may not exist in views with correct url; fetch directly
        try {
            $csDocidsToFetch = $docids->filter(fn ($id) => str_starts_with($id, 'CS'))->values();
            if ($csDocidsToFetch->isNotEmpty()) {
                $csFetchM     = new TrCS();
                $csFetchConn  = $csFetchM->getConnectionName() ?: config('database.default');
                $csFetchTable = $csFetchM->getTable();
                $csFetchRows  = collect();
                foreach ($csDocidsToFetch->chunk(1200) as $chunk) {
                    $csFetchRows = $csFetchRows->concat(
                        DB::connection($csFetchConn)
                            ->table($csFetchTable)
                            ->whereIn('csid', $chunk->all())
                            ->whereNull('deleted_at')
                            ->select('id', 'csdate as docdate', 'cpny_id as cpnyid', 'department_id as departementid', 'keperluan as infohd', 'csid as docid')
                            ->get()
                            ->map(fn ($r) => (object) array_merge((array) $r, ['url' => '/showcs']))
                    );
                }
                $data = $data->concat($csFetchRows);
            }
        } catch (\Throwable $e) {
            Log::warning('approvalJson CS fetch failed', [
                'err' => $e->getMessage(),
            ]);
        }

        // De-duplicate: keep first occurrence per docid (in case CS/BCR/VCR also exist in v_all_das)
        $data = $data->unique(fn ($r) => strtoupper(trim($r->docid)));

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
