<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\TrApproval;
use App\Models\TrBookingCar;
use App\Models\TrCS;
use App\Models\TrLndTrainingRegistration;
use App\Models\TrVoucherTaxi;
use App\Models\TrxVplReceive;
use App\Models\TrxVplTransfer;
use App\Models\TrxVplUsage;
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
                'aprv_datebefore',
                'aprv_cpnyid',
                'aprv_departementid'
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

        // Individual company/department codes aren't reliable enough to
        // disambiguate a duplicated refnbr: some approval lines are stamped
        // with the approver's own company/department rather than the
        // document's (e.g. BCR/IS), so an exact match would wrongly hide
        // legitimate approvals. Group Company ID (ms_company.group_cpny_id)
        // is stable across those cases and is still enough to tell apart
        // documents from genuinely different companies (e.g. PRF numbering
        // colliding between a JKT-group and an SBY-group company).
        $groupMap = DB::connection((new MsCompany())->getConnectionName() ?: config('database.default'))
            ->table((new MsCompany())->getTable())
            ->select('cpny_id', 'group_cpny_id')
            ->get()
            ->reduce(function ($map, $r) {
                $cpnyid = strtoupper(trim($r->cpny_id));
                $map[$cpnyid] = strtoupper(trim((string) $r->group_cpny_id)) ?: $cpnyid;

                return $map;
            }, []);

        $resolveGroup = function ($cpnyid) use ($groupMap) {
            $cpnyid = strtoupper(trim((string) $cpnyid));

            return $groupMap[$cpnyid] ?? $cpnyid;
        };

        // A refnbr is only unique within a company group — the same refnbr
        // can legitimately belong to two different documents from two
        // different company groups (e.g. PRF numbering). Key approvals by
        // refnbr+group so we resolve to the correct document.
        $approvalMap = $approvalRows
            ->groupBy(function ($r) use ($resolveGroup) {
                return strtoupper(trim($r->refnbr)).'|'.$resolveGroup($r->aprv_cpnyid);
            })
            ->map(function ($rows) {
                $latest = collect($rows)
                    ->sortByDesc(fn ($r) => $r->aprv_datebefore)
                    ->first();

                return [
                    'refnbr' => strtoupper(trim($latest->refnbr)),
                    'aprv_datebefore' => $latest->aprv_datebefore,
                ];
            });

        $docids = $approvalMap
            ->pluck('refnbr')
            ->unique()
            ->values();

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

        // VPT (Voucher Product Transfer) — not in v_all_das/v_all_trx; fetch directly
        try {
            $vptDocids = $docids->filter(fn ($id) => str_starts_with($id, 'VPT'))->values();
            if ($vptDocids->isNotEmpty()) {
                $vptM     = new TrxVplTransfer();
                $vptConn  = $vptM->getConnectionName() ?: config('database.default');
                $vptTable = $vptM->getTable();
                $vptRows  = collect();
                foreach ($vptDocids->chunk(1200) as $chunk) {
                    $vptRows = $vptRows->concat(
                        DB::connection($vptConn)
                            ->table($vptTable)
                            ->whereIn('transfer_id', $chunk->all())
                            ->select(
                                'id',
                                'transfer_date as docdate',
                                'cpnyid',
                                'department as departementid',
                                'transfer_remark as infohd',
                                'transfer_id as docid'
                            )
                            ->get()
                            ->map(fn ($r) => (object) array_merge((array) $r, ['url' => '/showtransfervp']))
                    );
                }
                $data = $data->concat($vptRows);
            }
        } catch (\Throwable $e) {
            Log::warning('approvalJson VPT fetch failed', [
                'err' => $e->getMessage(),
            ]);
        }

        // VPR (Voucher Product Receive) — not in v_all_das/v_all_trx; fetch directly
        try {
            $vprDocids = $docids->filter(fn ($id) => str_starts_with($id, 'VPR'))->values();
            if ($vprDocids->isNotEmpty()) {
                $vprM     = new TrxVplReceive();
                $vprConn  = $vprM->getConnectionName() ?: config('database.default');
                $vprTable = $vprM->getTable();
                $vprRows  = collect();
                foreach ($vprDocids->chunk(1200) as $chunk) {
                    $vprRows = $vprRows->concat(
                        DB::connection($vprConn)
                            ->table($vprTable)
                            ->whereIn('receive_id', $chunk->all())
                            ->select(
                                'id',
                                'receive_date as docdate',
                                'cpnyid',
                                'department as departementid',
                                'receive_remark as infohd',
                                'receive_id as docid'
                            )
                            ->get()
                            ->map(fn ($r) => (object) array_merge((array) $r, ['url' => '/showreceivevp']))
                    );
                }
                $data = $data->concat($vprRows);
            }
        } catch (\Throwable $e) {
            Log::warning('approvalJson VPR fetch failed', [
                'err' => $e->getMessage(),
            ]);
        }

        // VPU (Voucher Product Usage) — not in v_all_das/v_all_trx; fetch directly
        try {
            $vpuDocids = $docids->filter(fn ($id) => str_starts_with($id, 'VPU'))->values();
            if ($vpuDocids->isNotEmpty()) {
                $vpuM     = new TrxVplUsage();
                $vpuConn  = $vpuM->getConnectionName() ?: config('database.default');
                $vpuTable = $vpuM->getTable();
                $vpuRows  = collect();
                foreach ($vpuDocids->chunk(1200) as $chunk) {
                    $vpuRows = $vpuRows->concat(
                        DB::connection($vpuConn)
                            ->table($vpuTable)
                            ->whereIn('usage_id', $chunk->all())
                            ->select(
                                'id',
                                'usage_date as docdate',
                                'cpnyid',
                                'department as departementid',
                                'usage_remark as infohd',
                                'usage_id as docid'
                            )
                            ->get()
                            ->map(fn ($r) => (object) array_merge((array) $r, ['url' => '/showusagevp']))
                    );
                }
                $data = $data->concat($vpuRows);
            }
        } catch (\Throwable $e) {
            Log::warning('approvalJson VPU fetch failed', [
                'err' => $e->getMessage(),
            ]);
        }

        // TRN (Training Registration) — not in v_all_das/v_all_trx; fetch directly
        try {
            $trnDocids = $docids->filter(fn ($id) => str_starts_with($id, 'TRN'))->values();
            if ($trnDocids->isNotEmpty()) {
                $trnM     = new TrLndTrainingRegistration();
                $trnConn  = $trnM->getConnectionName() ?: config('database.default');
                $trnTable = $trnM->getTable();
                $trnRows  = collect();
                foreach ($trnDocids->chunk(1200) as $chunk) {
                    $trnRows = $trnRows->concat(
                        DB::connection($trnConn)
                            ->table("{$trnTable} as tr")
                            ->leftJoin('ms_lnd_training as evt', 'evt.training_id', '=', 'tr.training_id')
                            ->whereIn('tr.training_regist_id', $chunk->all())
                            ->whereNull('tr.deleted_at')
                            ->select(
                                'tr.id',
                                'tr.training_regist_date as docdate',
                                'tr.cpny_id as cpnyid',
                                'tr.department_id as departementid',
                                DB::raw("coalesce(evt.training_name, 'Training') || ' - ' || tr.user_registration as infohd"),
                                'tr.training_regist_id as docid'
                            )
                            ->get()
                            ->map(fn ($r) => (object) array_merge((array) $r, ['url' => '/training-list/my']))
                    );
                }
                $data = $data->concat($trnRows);
            }
        } catch (\Throwable $e) {
            Log::warning('approvalJson TRN fetch failed', [
                'err' => $e->getMessage(),
            ]);
        }

        $data = $data
            ->map(function ($r) use ($approvalMap, $status, $resolveGroup) {
                $docidKey = strtoupper(
                    trim($r->docid)
                );

                // Match on docid+company-group, not docid alone — a refnbr
                // can be shared by two different documents from two
                // different company groups (e.g. PRF numbering), so
                // docid-only matching could resolve to the wrong document.
                $compositeKey = $docidKey.'|'.$resolveGroup($r->cpnyid);

                $approval = $approvalMap->get($compositeKey);

                if (!$approval) {
                    return null;
                }

                // Eng Ticket (TOK) shares tr_ticket with the IT ticket module in
                // the v_all_trx view, which still points its url at the IT
                // module's /ticket route — override it to the Eng module's own.
                $url = str_starts_with($docidKey, 'TOK')
                    ? '/showoprtekticket'
                    : $r->url;

                return [
                    'hid' => Hashids::encode($r->id),
                    'docid' => $r->docid,
                    'docdate' => $approval['aprv_datebefore'] ?? null,
                    'cpnyid' => $r->cpnyid,
                    'departementid' => $r->departementid,
                    'infohd' => $r->infohd,
                    'url' => $url,
                    'status' => $status,
                ];
            })
            ->filter()
            // De-duplicate: the group match above already resolved which
            // document this approval belongs to, so any rows still sharing
            // a docid here are the same document surfacing from more than
            // one source view (e.g. CS/BCR/VCR also exist in v_all_das,
            // sometimes with a slightly different company code within the
            // same group) — keep one.
            ->unique(fn ($r) => strtoupper(trim($r['docid'])))
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
