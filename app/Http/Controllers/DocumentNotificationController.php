<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\SysUserRole;
use App\Models\TrAccess;
use App\Models\TrApproval;
use App\Models\TrIMBudget;
use App\Models\TrItrecommend;
use App\Models\TrTicket;
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

        // ── 3. TrTicket: all meaningful status stages for the ticket submitter ──
        // Notification key = TICKETID_TKT (no status in key) so the same entry
        // updates automatically when status changes — the dropdown always shows
        // the current stage and a new toast fires for each transition.
        try {
            // 'key' maps raw status_pekerjaan → clean code (no spaces/slashes).
            // 'expire' controls how long the notification lives:
            //   'proceed'   → 1 day  (IT is actively working — informational)
            //   'completed' → 1 day  (done — user should see then it disappears)
            //   'attention' → 30 days (requires user awareness / follow-up)
            $ticketMeta = [
                'TRANSFER'                  => ['key' => 'TRANSFER',   'label' => 'Transferred',    'message' => 'Your ticket has been transferred to another IT team.',              'expire' => 'attention'],
                'RESPONSE'                  => ['key' => 'RESPONSE',   'label' => 'Responded',      'message' => 'IT has responded to your ticket. Please check for updates.',        'expire' => 'attention'],
                'PROCESS'                   => ['key' => 'PROCESS',    'label' => 'In Process',     'message' => 'Your ticket is now being actively processed by IT.',                'expire' => 'proceed'],
                'PENDING'                   => ['key' => 'PENDING',    'label' => 'Pending',        'message' => 'Your ticket is on pending — IT is waiting for additional info.',    'expire' => 'attention'],
                'ENVISION'                  => ['key' => 'ENVISION',   'label' => 'Envision',       'message' => 'Your ticket has been moved into the Envision system.',              'expire' => 'proceed'],
                'ENVISION CHECKED / SOLVED' => ['key' => 'ENV_SOLVED', 'label' => 'Envision Solved','message' => 'Your ticket has been resolved via Envision.',                       'expire' => 'completed'],
                'COMPLETED'                 => ['key' => 'COMPLETED',  'label' => 'Completed',      'message' => 'Your ticket has been completed successfully.',                      'expire' => 'completed'],
                'REOPEN'                    => ['key' => 'REOPEN',     'label' => 'Reopened',       'message' => 'Your ticket has been reopened for further action.',                 'expire' => 'attention'],
                'CANCEL'                    => ['key' => 'CANCEL',     'label' => 'Cancelled',      'message' => 'Your ticket has been cancelled by IT. Please contact IT if needed.','expire' => 'completed'],
            ];

            // 1-day: final/done states (disappear fast after completion)
            // 90-day: everything else (stays until status changes; 7-day client re-alert handles reminders)
            $oneDayStatuses  = ['CANCEL', 'COMPLETED', 'ENVISION CHECKED / SOLVED'];
            $longTermStatuses = array_diff(array_keys($ticketMeta), $oneDayStatuses);

            $tickets = TrTicket::where(fn($q) =>
                    // Non-ENV_SOLVED statuses → notify the ticket creator/requester
                    $q->where(fn($q2) =>
                        $q2->whereRaw("lower(trim(coalesce(user_peminta,''))) = ?", [$username])
                           ->orWhereRaw("lower(trim(coalesce(created_by,''))) = ?", [$username])
                    )->where('status_pekerjaan', '!=', 'ENVISION CHECKED / SOLVED')
                    // ENV_SOLVED → notify ONLY the PIC (so they can check and complete the ticket)
                    ->orWhere(fn($q2) =>
                        $q2->where('status_pekerjaan', 'ENVISION CHECKED / SOLVED')
                           ->whereRaw("lower(trim(coalesce(pic_ticket,''))) = ?", [$username])
                    )
                )
                ->whereIn('status_pekerjaan', array_keys($ticketMeta))
                ->where(fn($q) =>
                    $q->where(fn($inner) =>
                        $inner->whereIn('status_pekerjaan', $longTermStatuses)
                              ->where('updated_at', '>=', now()->subDays(90))
                    )->orWhere(fn($inner) =>
                        $inner->whereIn('status_pekerjaan', $oneDayStatuses)
                              ->where('updated_at', '>=', now()->subDay())
                    )
                )
                ->select('id', 'ticketid', 'cpny_id', 'status_pekerjaan', 'pic_ticket', 'updated_at', 'updated_by')
                ->get()
                // CANCEL: only notify when cancelled by someone other than the submitter
                ->filter(fn($r) =>
                    $r->status_pekerjaan !== 'CANCEL' ||
                    strtolower(trim((string) $r->updated_by)) !== $username
                );

            $data = $data->concat($tickets->map(function ($r) use ($ticketMeta) {
                $meta = $ticketMeta[$r->status_pekerjaan];
                return [
                    // Key uses ticketid only (no status) so the entry replaces
                    // itself in the dropdown when the status changes.
                    // The status IS included so localStorage seen-tracking fires a
                    // new toast for every transition.
                    'key'        => strtoupper(trim($r->ticketid)) . '_TKT_' . $meta['key'],
                    'hid'        => Hashids::encode($r->id),
                    'docid'      => $r->ticketid,
                    'status'     => 'TKT_' . $meta['key'],
                    'label'      => $meta['label'],
                    'message'    => $meta['message'],
                    'cpnyid'     => $r->cpny_id,
                    'url'        => '/showticket',
                    'by'         => $r->pic_ticket,
                    'updated_at' => $r->updated_at,
                ];
            }));
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationController: TrTicket fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 4a. TrItrecommend: notify the created_user on I / P / D / C ──
        try {
            $itrUserMeta = [
                'I' => ['label' => 'In Progress',      'message' => 'IT is now working on your IT Recommendation request.'],
                'P' => ['label' => 'Waiting Approval', 'message' => 'Your IT Recommendation is done processing, waiting for approval now.'],
                'D' => ['label' => 'Revised',          'message' => 'Your IT Recommendation has been revised. Please review and resubmit.'],
                'C' => ['label' => 'Completed',        'message' => 'Your IT Recommendation has been completed.'],
                'R' => ['label' => 'Rejected',         'message' => 'Your IT Recommendation has been rejected.'],
            ];

            $itrsUser = TrItrecommend::where(fn($q) =>
                    $q->whereRaw("lower(trim(coalesce(user_peminta,''))) = ?", [$username])
                      ->orWhereRaw("lower(trim(coalesce(created_by,''))) = ?", [$username])
                )
                ->whereIn('status', array_keys($itrUserMeta))
                // C and R expire in 1 day; I / P / D stay until status changes (90-day limit)
                ->where(fn($q) =>
                    $q->where(fn($i) => $i->whereIn('status', ['I', 'P', 'D'])->where('updated_at', '>=', now()->subDays(90)))
                     ->orWhere(fn($i) => $i->whereIn('status', ['C', 'R'])->where('updated_at', '>=', now()->subDay()))
                )
                ->select('id', 'docid', 'cpny_id', 'status', 'updated_at', 'completed_at')
                ->get();

            $data = $data->concat($itrsUser->map(fn($r) => [
                'key'        => strtoupper(trim($r->docid)) . '_ITR_' . $r->status,
                'hid'        => Hashids::encode($r->id),
                'docid'      => $r->docid,
                'status'     => 'ITR_' . $r->status,
                'label'      => $itrUserMeta[$r->status]['label'],
                'message'    => $itrUserMeta[$r->status]['message'],
                'cpnyid'     => $r->cpny_id,
                'url'        => '/showitrecommendation',
                'by'         => null,
                'updated_at' => $r->updated_at ?? $r->completed_at,
            ]));
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationController: TrItrecommend (user) fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 4b. TrItrecommend: notify the IT recommend_pic on W (waiting review) and I (in progress) ──
        // W = creator submitted/resubmitted, waiting for IT to pick up.
        // I = IT is actively processing or revising.
        try {
            $itrPicMeta = [
                'W' => ['label' => 'Pending Review',   'message' => 'An IT Recommendation has been submitted and is waiting for your review.'],
                'I' => ['label' => 'Action Required',  'message' => 'An IT Recommendation assigned to you needs processing or revision.'],
            ];

            $itrsPic = TrItrecommend::whereIn('status', array_keys($itrPicMeta))
                ->whereRaw("lower(trim(coalesce(recommend_pic,''))) = ?", [$username])
                ->where('updated_at', '>=', now()->subDays(90))
                ->select('id', 'docid', 'cpny_id', 'status', 'recommend_pic', 'updated_at')
                ->get();

            $data = $data->concat($itrsPic->map(fn($r) => [
                'key'        => strtoupper(trim($r->docid)) . '_ITR_PIC_' . $r->status,
                'hid'        => Hashids::encode($r->id),
                'docid'      => $r->docid,
                'status'     => 'ITR_PIC_' . $r->status,
                'label'      => $itrPicMeta[$r->status]['label'],
                'message'    => $itrPicMeta[$r->status]['message'],
                'cpnyid'     => $r->cpny_id,
                'url'        => '/processitrecommendation',
                'by'         => null,
                'updated_at' => $r->updated_at,
            ]));
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationController: TrItrecommend (pic) fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 5a. TrAccess: notify created_user on D (Revised), R (Rejected), F (Finished) ──
        try {
            // D (Revised) is already covered by TrApproval section — no duplicate needed.
            $acrUserMeta = [
                'R' => ['label' => 'Rejected', 'message' => 'Your Access Request has been rejected.'],
                'F' => ['label' => 'Finished', 'message' => 'Your Access Request has been fully processed and completed by IT.'],
            ];

            $accessesUser = TrAccess::where(fn($q) =>
                    $q->whereRaw("lower(trim(coalesce(user_peminta,''))) = ?", [$username])
                      ->orWhereRaw("lower(trim(coalesce(created_by,''))) = ?", [$username])
                )
                ->whereIn('status', array_keys($acrUserMeta))
                // Both R and F expire in 1 day
                ->where('updated_at', '>=', now()->subDay())
                ->select('id', 'docid', 'cpny_id', 'status', 'updated_at', 'completed_at')
                ->get();

            $data = $data->concat($accessesUser->map(fn($r) => [
                'key'        => strtoupper(trim($r->docid)) . '_ACC_' . $r->status,
                'hid'        => Hashids::encode($r->id),
                'docid'      => $r->docid,
                'status'     => 'ACC_' . $r->status,
                'label'      => $acrUserMeta[$r->status]['label'],
                'message'    => $acrUserMeta[$r->status]['message'],
                'cpnyid'     => $r->cpny_id,
                'url'        => '/showaccessrequest',
                'by'         => null,
                'updated_at' => $r->updated_at ?? $r->completed_at,
            ]));
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationController: TrAccess (user) fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 5b. TrAccess: notify IT staff when status = C, matched by group_category ──
        // ITHARDWARE sees ACRs that have HARDWARE detail lines.
        // ITSOFTWARE sees ACRs that have SOFTWARE detail lines.
        // If an ACR has both, both roles are notified.
        try {
            $isITHardware = SysUserRole::whereRaw("lower(trim(username)) = ?", [$username])
                ->where('role_id', 'ITHARDWARE')->exists();
            $isITSoftware = SysUserRole::whereRaw("lower(trim(username)) = ?", [$username])
                ->where('role_id', 'ITSOFTWARE')->exists();

            if ($isITHardware || $isITSoftware) {
                $userCategories = array_values(array_filter([
                    $isITHardware ? 'HARDWARE' : null,
                    $isITSoftware ? 'SOFTWARE' : null,
                ]));

                $accessesIT = TrAccess::where('status', 'C')
                    ->where('updated_at', '>=', now()->subDay())
                    // Only include ACRs that contain detail lines matching the user's category
                    ->whereHas('details', fn($q) => $q->whereIn('group_category', $userCategories))
                    ->select('id', 'docid', 'cpny_id', 'status', 'updated_at')
                    ->get();

                $categoryLabel = implode(' & ', array_map('ucFirst', array_map('strtolower', $userCategories)));

                $data = $data->concat($accessesIT->map(fn($r) => [
                    'key'        => strtoupper(trim($r->docid)) . '_ACC_C_IT',
                    'hid'        => Hashids::encode($r->id),
                    'docid'      => $r->docid,
                    'status'     => 'ACC_C',
                    'label'      => 'Needs Processing',
                    'message'    => "An Access Request ({$categoryLabel}) has been approved and is waiting for IT to process.",
                    'cpnyid'     => $r->cpny_id,
                    'url'        => '/showaccessrequest',
                    'by'         => null,
                    'updated_at' => $r->updated_at,
                ]));
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationController: TrAccess (IT role) fetch failed', ['err' => $e->getMessage()]);
        }

        return response()->json([
            'data' => $data->sortByDesc(fn($r) => $r['updated_at'])->values(),
        ]);
    }
}
