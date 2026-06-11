<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\MsTicketCategoryDept;
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

class DocumentNotificationService
{
    public static function buildForUser(string $username): array
    {
        $username = strtolower(trim($username));
        $data     = collect();

        // ── 1. TrApproval: D (Revised) & R (Rejected) ──
        $aprM    = new TrApproval();
        $aprConn = $aprM->getConnectionName() ?: config('database.default');
        $tblApr  = $aprM->getTable();

        $approvalRows = DB::connection($aprConn)->table($tblApr)
            ->select('refnbr', 'status', 'aprv_dateafter', 'aprv_name')
            ->whereRaw("lower(trim(coalesce(created_by,''))) = ?", [$username])
            ->whereIn('status', ['D', 'R'])
            ->whereNotNull('aprv_dateafter')
            ->where(fn($q) => $q->where('status', '!=', 'R')
                                ->orWhere('aprv_dateafter', '>=', now()->subDay()))
            ->whereNotExists(fn($sub) =>
                $sub->select(DB::raw(1))
                    ->from($tblApr . ' as t2')
                    ->whereColumn('t2.refnbr', $tblApr . '.refnbr')
                    ->where(fn($q) =>
                        $q->where(fn($q2) =>
                            $q2->where('t2.status', 'P')->whereNotNull('t2.aprv_datebefore')
                        )->orWhere(fn($q2) =>
                            $q2->where('t2.status', 'A')
                               ->whereColumn('t2.updated_at', '>', $tblApr . '.aprv_dateafter')
                        )
                    )
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
                'D' => ['label' => 'Revised',  'message' => 'Your document has been revised. Please review and resubmit.'],
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

        // ── 2. TrIMBudget: status H ──
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
            Log::warning('DocumentNotificationService: TrIMBudget fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 3. TrTicket: status stages for ticket submitter ──
        try {
            $ticketMeta = [
                'TRANSFER'                  => ['key' => 'TRANSFER',   'label' => 'Transferred',     'message' => 'Your ticket has been transferred to another IT team.',              'expire' => 'attention'],
                'RESPONSE'                  => ['key' => 'RESPONSE',   'label' => 'Responded',       'message' => 'IT has responded to your ticket. Please check for updates.',        'expire' => 'attention'],
                'PROCESS'                   => ['key' => 'PROCESS',    'label' => 'In Process',      'message' => 'Your ticket is now being actively processed by IT.',                'expire' => 'proceed'],
                'PENDING'                   => ['key' => 'PENDING',    'label' => 'Pending',         'message' => 'Your ticket is on pending — IT is waiting for additional info.',    'expire' => 'attention'],
                'ENVISION'                  => ['key' => 'ENVISION',   'label' => 'Envision',        'message' => 'Your ticket has been moved into the Envision system.',              'expire' => 'proceed'],
                'ENVISION CHECKED / SOLVED' => ['key' => 'ENV_SOLVED', 'label' => 'Envision Solved', 'message' => 'Your ticket has been resolved via Envision.',                       'expire' => 'completed'],
                'COMPLETED'                 => ['key' => 'COMPLETED',  'label' => 'Completed',       'message' => 'Your ticket has been completed successfully.',                      'expire' => 'completed'],
                'REOPEN'                    => ['key' => 'REOPEN',     'label' => 'Reopened',        'message' => 'Your ticket has been reopened for further action.',                 'expire' => 'attention'],
                'CANCEL'                    => ['key' => 'CANCEL',     'label' => 'Cancelled',       'message' => 'Your ticket has been cancelled by IT. Please contact IT if needed.','expire' => 'completed'],
            ];

            $oneDayStatuses  = ['CANCEL', 'COMPLETED', 'ENVISION CHECKED / SOLVED'];
            $longTermStatuses = array_diff(array_keys($ticketMeta), $oneDayStatuses);

            $tickets = TrTicket::where(fn($q) =>
                    $q->where(fn($q2) =>
                        $q2->whereRaw("lower(trim(coalesce(user_peminta,''))) = ?", [$username])
                           ->orWhereRaw("lower(trim(coalesce(created_by,''))) = ?", [$username])
                    )->where('status_pekerjaan', '!=', 'ENVISION CHECKED / SOLVED')
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
                ->filter(fn($r) =>
                    $r->status_pekerjaan !== 'CANCEL' ||
                    strtolower(trim((string) $r->updated_by)) !== $username
                );

            $data = $data->concat($tickets->map(function ($r) use ($ticketMeta) {
                $meta = $ticketMeta[$r->status_pekerjaan];
                return [
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
            Log::warning('DocumentNotificationService: TrTicket fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 3b. TrTicket: notify IT category staff on CREATED ──
        try {
            $assignedCategories = MsTicketCategoryDept::whereRaw("lower(trim(username)) = ?", [$username])
                ->where('status', 'A')
                ->pluck('ticket_categoryid');

            if ($assignedCategories->isNotEmpty()) {
                $newTickets = TrTicket::where('status_pekerjaan', 'CREATED')
                    ->whereIn('ticket_categoryid', $assignedCategories)
                    ->where('updated_at', '>=', now()->subDays(90))
                    ->select('id', 'ticketid', 'cpny_id', 'ticket_categoryid', 'ticket_sla_days', 'updated_at', 'created_by', 'user_peminta')
                    ->get();

                $data = $data->concat($newTickets->map(fn($r) => [
                    'key'        => strtoupper(trim($r->ticketid)) . '_TKT_CREATED',
                    'hid'        => Hashids::encode($r->id),
                    'docid'      => $r->ticketid,
                    'status'     => 'TKT_CREATED',
                    'label'      => 'New Ticket',
                    'message'    => 'Hi, a new ticket has been created from ' . ($r->user_peminta ?? $r->created_by) . ', please review and respond to the ticket.',
                    'cpnyid'     => $r->cpny_id,
                    'url'        => '/showticket',
                    'by'         => $r->user_peminta ?? $r->created_by,
                    'sla_days'   => $r->ticket_sla_days,
                    'updated_at' => $r->updated_at,
                ]));
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationService: TrTicket (category IT) fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 4a. TrItrecommend: notify created_user on I / P / D / C / R ──
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
            Log::warning('DocumentNotificationService: TrItrecommend (user) fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 4b. TrItrecommend: notify recommend_pic on W / I ──
        try {
            $itrPicMeta = [
                'W' => ['label' => 'Pending Review',  'message' => 'An IT Recommendation has been submitted and is waiting for your review.'],
                'I' => ['label' => 'Action Required', 'message' => 'An IT Recommendation assigned to you needs processing or revision.'],
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
            Log::warning('DocumentNotificationService: TrItrecommend (pic) fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 5a. TrAccess: notify created_user on R / F ──
        try {
            $acrUserMeta = [
                'R' => ['label' => 'Rejected', 'message' => 'Your Access Request has been rejected.'],
                'F' => ['label' => 'Finished', 'message' => 'Your Access Request has been fully processed and completed by IT.'],
            ];

            $accessesUser = TrAccess::where(fn($q) =>
                    $q->whereRaw("lower(trim(coalesce(user_peminta,''))) = ?", [$username])
                      ->orWhereRaw("lower(trim(coalesce(created_by,''))) = ?", [$username])
                )
                ->whereIn('status', array_keys($acrUserMeta))
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
            Log::warning('DocumentNotificationService: TrAccess (user) fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 5b. TrAccess: notify IT staff when status = C ──
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
            Log::warning('DocumentNotificationService: TrAccess (IT role) fetch failed', ['err' => $e->getMessage()]);
        }

        return $data->sortByDesc(fn($r) => $r['updated_at'])->values()->all();
    }
}
