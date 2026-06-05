<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\TrApproval;
use App\Models\TrIMBudget;
use App\Models\Viewtrxall;
use App\Models\ViewJobApply;
use App\Models\ViewtrPurch;
use App\Models\ViewDasAll;

class DocumentNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['data' => []], 401);

        $username = strtolower(trim((string) $user->username));
        $data     = collect();

        // ── 1. TrApproval: D (Revised) & R (Rejected) for documents created by this user ──
        $aprM    = new TrApproval();
        $aprConn = $aprM->getConnectionName() ?: config('database.default');
        $tblApr  = $aprM->getTable();

        $approvalRows = DB::connection($aprConn)->table($tblApr)
            ->select('refnbr', 'status', 'aprv_dateafter', 'aprv_name')
            ->whereRaw("lower(trim(coalesce(created_by,''))) = ?", [$username])
            ->whereIn('status', ['D', 'R'])
            ->whereNotNull('aprv_dateafter')
            // R older than 1 day is dismissed automatically
            ->where(fn($q) => $q->where('status', '!=', 'R')
                                ->orWhere('aprv_dateafter', '>=', now()->subDay()))
            // exclude docs that have already been resubmitted (new P row with aprv_datebefore)
            ->whereNotExists(fn($sub) =>
                $sub->select(DB::raw(1))
                    ->from($tblApr . ' as t2')
                    ->whereColumn('t2.refnbr', $tblApr . '.refnbr')
                    ->where('t2.status', 'P')
                    ->whereNotNull('t2.aprv_datebefore')
            )
            ->get();

        if ($approvalRows->isNotEmpty()) {
            $approvalMap = $approvalRows
                ->groupBy(fn($r) => strtoupper(trim($r->refnbr)))
                ->map(fn($rows) => collect($rows)->sortByDesc(fn($r) => $r->aprv_dateafter)->first());

            $docids     = $approvalMap->keys()->values();
            $trxM       = new Viewtrxall();
            $appM       = new ViewJobApply();
            $purchM     = new ViewtrPurch();
            $dasM       = new ViewDasAll();
            $selectCols = ['id', 'cpnyid', 'url', 'docid'];

            $fetch = function (string $conn, string $table) use ($docids, $selectCols) {
                $out = collect();
                foreach ($docids->chunk(500) as $chunk) {
                    $out = $out->concat(
                        DB::connection($conn)->table($table)
                            ->whereIn('docid', $chunk->all())
                            ->select($selectCols)->get()
                    );
                }
                return $out;
            };

            $docs = collect()
                ->concat($fetch($trxM->getConnectionName() ?: config('database.default'), $trxM->getTable()))
                ->concat($fetch($appM->getConnectionName() ?: config('database.default'), $appM->getTable()));

            try { $docs = $docs->concat($fetch($purchM->getConnectionName() ?: config('database.default'), $purchM->getTable())); } catch (\Throwable $e) {}
            try { $docs = $docs->concat($fetch($dasM->getConnectionName() ?: config('database.default'), $dasM->getTable())); } catch (\Throwable $e) {}

            $statusMeta = [
                'D' => ['label' => 'Revised', 'message' => 'Your document has been revised. Please review and resubmit.'],
                'R' => ['label' => 'Rejected', 'message' => 'Your document has been rejected. Please check for details.'],
            ];

            $data = $data->concat(
                $docs->map(function ($r) use ($approvalMap, $statusMeta) {
                    $key      = strtoupper(trim($r->docid));
                    $approval = $approvalMap->get($key);
                    if (!$approval) return null;
                    $meta = $statusMeta[$approval->status] ?? ['label' => $approval->status, 'message' => ''];

                    return [
                        'key'        => $key . '_' . $approval->status,
                        'hid'        => Hashids::encode($r->id),
                        'docid'      => $r->docid,
                        'status'     => $approval->status,
                        'label'      => $meta['label'],
                        'message'    => $meta['message'],
                        'cpnyid'     => $r->cpnyid,
                        'url'        => $r->url,
                        'by'         => $approval->aprv_name,
                        'updated_at' => $approval->aprv_dateafter,
                    ];
                })->filter()
            );
        }

        // ── 2. TrIMBudget: status H for user_peminta or created_by ──
        try {
            $imBudgets = TrIMBudget::where('status', 'H')
                ->where(fn($q) => $q->whereRaw("lower(trim(coalesce(user_peminta,''))) = ?", [$username])
                                    ->orWhereRaw("lower(trim(coalesce(created_by,''))) = ?", [$username]))
                ->select('id', 'imbudgetid', 'cpny_id', 'updated_at', 'created_at')
                ->get();

            $data = $data->concat(
                $imBudgets->map(fn($r) => [
                    'key'        => strtoupper(trim($r->imbudgetid)) . '_H',
                    'hid'        => Hashids::encode($r->id),
                    'docid'      => $r->imbudgetid,
                    'status'     => 'H',
                    'label'      => 'On Hold',
                    'message'    => 'Your IM Budget document is on hold and needs your attention.',
                    'cpnyid'     => $r->cpny_id,
                    'url'        => '/showimbudgets',
                    'by'         => null,
                    'updated_at' => $r->updated_at ?? $r->created_at,
                ])
            );
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationController: TrIMBudget fetch failed', ['err' => $e->getMessage()]);
        }

        return response()->json([
            'data' => $data->sortByDesc(fn($r) => $r['updated_at'])->values(),
        ]);
    }
}
