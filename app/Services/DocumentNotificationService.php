<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\MsTicketCategoryDept;
use App\Models\MsVplProduct;
use App\Models\MsVplProductDetail;
use App\Models\Personnel;
use App\Models\SysCalendar;
use App\Models\SysUserRole;
use App\Models\TrAccess;
use App\Models\TrApproval;
use App\Models\TrBast;
use App\Models\TrCalr;
use App\Models\TrCalrNonPurch;
use App\Models\TrCS;
use App\Models\TrIMBudget;
use App\Models\TrItemRequest;
use App\Models\TrItrecommend;
use App\Models\TrMessage;
use App\Models\TrLndTrainingRegistration;
use App\Models\TrParkingRegistration;
use App\Models\TrRfca;
use App\Models\TrRfp;
use App\Models\TrRfpNonPurch;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\TrTicket;
use App\Models\TrWO;
use App\Models\User;
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
            $selectCols = ['id', 'cpnyid', 'url', 'docid', 'status'];

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
                    // Document was cancelled (header status X) after the D/R approval step — no longer actionable.
                    if (strtoupper(trim((string) ($r->status ?? ''))) === 'X') return null;
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
                              ->where('updated_at', '>=', \Carbon\Carbon::yesterday()->startOfDay())
                    )
                )
                ->select('id', 'ticketid', 'ticket_type', 'cpny_id', 'status_pekerjaan', 'pic_ticket', 'updated_at', 'updated_by')
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
                    'url'        => self::ticketShowUrl($r->ticket_type),
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
                    ->select('id', 'ticketid', 'ticket_type', 'cpny_id', 'ticket_categoryid', 'ticket_sla_days', 'updated_at', 'created_by', 'user_peminta')
                    ->get();

                $data = $data->concat($newTickets->map(fn($r) => [
                    'key'        => strtoupper(trim($r->ticketid)) . '_TKT_CREATED',
                    'hid'        => Hashids::encode($r->id),
                    'docid'      => $r->ticketid,
                    'status'     => 'TKT_CREATED',
                    'label'      => 'New Ticket',
                    'message'    => 'Hi, a new ticket has been created from ' . ($r->user_peminta ?? $r->created_by) . ', please review and respond to the ticket.',
                    'cpnyid'     => $r->cpny_id,
                    'url'        => self::ticketShowUrl($r->ticket_type),
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

        // ── 6. BAST Jobs: remind users who have pending BAST to create ──
        try {
            $bastJobs = \App\Models\TrPOterm::query()
                ->from('tr_po_term')
                ->leftJoin('tr_po as po', function ($join) {
                    $join->on('po.ponbr',   '=', 'tr_po_term.ponbr')
                         ->on('po.cpny_id', '=', 'tr_po_term.cpny_id')
                         ->on('po.csid',    '=', 'tr_po_term.csid');
                })
                ->whereRaw("lower(trim(coalesce(tr_po_term.user_peminta,''))) = ?", [$username])
                ->where('tr_po_term.flag_bast', true)
                ->whereNull('tr_po_term.bastid')
                ->where('tr_po_term.status', 'A')
                ->whereRaw("NOW()::date <= po.spkendtworkingdate - 7")
                ->orderBy('tr_po_term.updated_at', 'desc')
                ->limit(5)
                ->select(
                    'tr_po_term.id',
                    'tr_po_term.ponbr',
                    'tr_po_term.cpny_id',
                    'tr_po_term.terms_name',
                    'tr_po_term.vendorname',
                    'tr_po_term.updated_at'
                )
                ->get();

            if ($bastJobs->isNotEmpty()) {

                $data = $data->concat($bastJobs->map(fn ($r) => [
                    'key'        => 'BAST_JOB_' . $r->id,
                    'hid'        => Hashids::encode((string) $r->id),
                    'docid'      => $r->ponbr . ($r->terms_name ? ' · ' . $r->terms_name : ''),
                    'status'     => 'BAST_JOB',
                    'label'      => 'BAST Job',
                    'message'    => 'Your work is almost done, please check this BAST that you need to create.',
                    'cpnyid'     => $r->cpny_id,
                    'href'       => '/bast/create?term=' . Hashids::encode((string) $r->id),
                    'url'        => '/bast/create?term=',
                    'by'         => $r->vendorname,
                    'updated_at' => $r->updated_at,
                ]));
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationService: BAST Jobs fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 7. BAST Approval Level 1: notify level-1 approvers on pending BAST ──
        try {
            $aprM    = new TrApproval();
            $aprConn = $aprM->getConnectionName() ?: config('database.default');
            $tblApr  = $aprM->getTable();

            $aprvRows = DB::connection($aprConn)->table($tblApr)
                ->select('refnbr', 'aprv_cpnyid')
                ->whereRaw("lower(trim(coalesce(aprv_username,''))) = ?", [$username])
                ->where('aprv_doctype', 'BA')
                ->where('status', 'P')
                ->whereNotNull('aprv_datebefore')
                ->whereNull('aprv_dateafter')
                ->whereRaw("CAST(aprv_leveling AS NUMERIC) = 1.0")
                ->get();

            if ($aprvRows->isNotEmpty()) {
                $bastIds = $aprvRows->pluck('refnbr')->filter()->unique()->values()->all();

                $basts = \App\Models\TrBast::whereIn('bastid', $bastIds)
                    ->where('status', 'P')
                    ->select('id', 'bastid', 'cpny_id', 'updated_at', 'created_by')
                    ->get()
                    ->keyBy('bastid');

                $data = $data->concat(
                    $aprvRows->map(function ($row) use ($basts) {
                        $bast = $basts->get($row->refnbr);
                        if (!$bast) return null;
                        return [
                            'key'        => 'BAST_APRV1_' . $bast->bastid,
                            'hid'        => Hashids::encode((string) $bast->id),
                            'docid'      => $bast->bastid,
                            'status'     => 'BAST_APRV1',
                            'label'      => 'BAST Approval',
                            'message'    => 'Your work is done, please check this BAST that you need to approve.',
                            'cpnyid'     => $bast->cpny_id,
                            'href'       => null,
                            'url'        => '/showbast',
                            'by'         => $bast->created_by,
                            'updated_at' => $bast->updated_at,
                        ];
                    })->filter()
                );
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationService: BAST Approval Level 1 fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 8. Comment activity: @mention notifies only the mentioned user(s); a plain
        //      comment (no valid mention) notifies the creator + approval line instead ──
        try {
            // 'terminalStatuses' (checked against 'statusCol', default 'status') marks a document
            // as fully approved/completed — once it's there, new comments on it no longer notify
            // anyone (see the terminal-status check in the row loop below).
            $commentDocTypes = [
                'TIC' => ['model' => TrTicket::class,       'idCol' => 'ticketid', 'url' => '/showticket',           'statusCol' => 'status_pekerjaan', 'terminalStatuses' => ['COMPLETED', 'CANCEL', 'ENVISION CHECKED / SOLVED']],
                'ACR' => ['model' => TrAccess::class,        'idCol' => 'docid',    'url' => '/showaccessrequest',    'terminalStatuses' => ['F']],
                'ITR' => ['model' => TrItrecommend::class,   'idCol' => 'docid',    'url' => '/showitrecommendation', 'terminalStatuses' => ['C']],
                'PRJ' => ['model' => \App\Models\MsProject::class,     'idCol' => 'project_id', 'url' => '/projects'],
                'TSK' => ['model' => \App\Models\TrProjectTask::class, 'idCol' => 'task_id',    'url' => '/projects'],
            ];

            foreach (self::extendedDocTypeConfig() as $extDoctype => $extCfg) {
                $commentDocTypes[$extDoctype] = [
                    'model' => $extCfg['model'],
                    'idCol' => $extCfg['idCol'],
                    'url'   => $extCfg['url'],
                    // All extended doctypes reach 'C' on final approval via ApprovalController's
                    // onComplete closures and stop needing comment notifications after that, except:
                    // RC (TrRfca), which has no reliable header-status terminal value yet, and TRN
                    // (training registration), whose seat lifecycle (offer/accept/decline/manual
                    // accept) keeps generating notification-worthy events well after approval
                    // completes — an explicit override in extendedDocTypeConfig() beats guessing.
                    'terminalStatuses' => $extCfg['terminalStatuses'] ?? ($extDoctype === 'RC' ? [] : ['C']),
                ];
            }

            $readKeys        = collect(Cache::get('doc_notif_read_' . $username, []));
            // Widest possible calendar-day span a 5-business-day window could cover (long
            // holiday clusters e.g. Lebaran) — exact cutoff is enforced per-row below.
            $commentHolidays = self::commentExpiryHolidays();

            foreach ($commentDocTypes as $commentDoctype => $cfg) {
                $rows = TrMessage::where('doctype', $commentDoctype)
                    ->where('message_date', '>=', now()->subDays(21))
                    ->whereRaw("lower(trim(coalesce(username,''))) != ?", [$username])
                    ->get()
                    ->filter(fn($row) => !self::isCommentExpired($row->message_date, $commentHolidays))
                    ->reject(fn($row) => $readKeys->contains('CMT_' . $commentDoctype . '_' . $row->id));

                if ($rows->isEmpty()) {
                    continue;
                }

                // Batch-fetch every doc this doctype's rows reference in one query instead of
                // one `first()` per row — with 90+ days of messages across 14 doctypes this was
                // firing hundreds of per-row queries on every buildForUser() call (run for every
                // active user each minute by notifications:refresh-doc), which was blowing past
                // the 60s execution limit.
                $docsByKey = $cfg['model']::whereIn(
                        $cfg['idCol'],
                        $rows->pluck('refnbr')->filter()->unique()->values()->all()
                    )
                    ->get()
                    ->keyBy($cfg['idCol']);

                foreach ($rows as $row) {
                    $key = 'CMT_' . $commentDoctype . '_' . $row->id;

                    $doc = $docsByKey->get($row->refnbr);
                    if (!$doc) {
                        continue;
                    }

                    // Document has reached its terminal/completed status — stop surfacing
                    // comment & mention notifications for it, regardless of when they were posted.
                    if (!empty($cfg['terminalStatuses'])) {
                        $statusCol = $cfg['statusCol'] ?? 'status';
                        $docStatus = strtoupper(trim((string) ($doc->{$statusCol} ?? '')));
                        if (in_array($docStatus, $cfg['terminalStatuses'], true)) {
                            continue;
                        }
                    }

                    $hid = self::commentDocHid($commentDoctype, $doc);

                    preg_match_all('/@([\w.]+)/', (string) $row->message, $m);
                    $tokens = collect($m[1] ?? [])->map(fn($t) => strtolower($t))->unique();

                    $mentionedUsernames = collect();
                    if ($tokens->isNotEmpty()) {
                        $mentionedUsernames = User::query()
                            ->whereIn(DB::raw('lower(username)'), $tokens->all())
                            ->pluck('username')
                            ->map(fn($u) => strtolower(trim($u)));
                    }

                    if ($mentionedUsernames->isNotEmpty()) {
                        if (!$mentionedUsernames->contains($username)) {
                            continue;
                        }

                        $data->push([
                            'key'        => $key,
                            'hid'        => $hid,
                            'docid'      => $row->refnbr,
                            'status'     => 'MENTION',
                            'label'      => 'Mentioned',
                            'message'    => 'You are mentioned in this document, please check.',
                            'comment'    => \Illuminate\Support\Str::limit((string) $row->message, 500),
                            'cpnyid'     => $row->cpny_id,
                            'url'        => $cfg['url'],
                            'by'         => $row->name,
                            'updated_at' => $row->message_date,
                        ]);
                        continue;
                    }

                    $recipients = self::resolveCommentRecipients($commentDoctype, $doc);
                    if (!$recipients->contains($username)) {
                        continue;
                    }

                    $data->push([
                        'key'        => $key,
                        'hid'        => $hid,
                        'docid'      => $row->refnbr,
                        'status'     => 'COMMENT',
                        'label'      => 'New Comment',
                        'message'    => 'There is a new comment in this document, please check.',
                        'comment'    => \Illuminate\Support\Str::limit((string) $row->message, 500),
                        'cpnyid'     => $row->cpny_id,
                        'url'        => $cfg['url'],
                        'by'         => $row->name,
                        'updated_at' => $row->message_date,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationService: Comment activity fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 9. RCA Non Purchase: 14+ days past Tanggal Realisasi (datepenyelesaian), no CALR
        //      yet — remind the creator to create the CALR ──
        try {
            $rcaCalrDue = TrRfpNonPurch::where('rfpnonpurchase_type', 'RCA')
                ->whereNotNull('datepenyelesaian')
                ->where('datepenyelesaian', '<=', now()->subDays(14))
                ->where(fn($q) => $q->whereNull('calrid')->orWhere('calrid', ''))
                ->whereRaw("lower(trim(coalesce(created_by,''))) = ?", [$username])
                ->select('id', 'rfpnonpurchaseid', 'cpny_id', 'datepenyelesaian')
                ->get();

            $data = $data->concat($rcaCalrDue->map(fn($r) => [
                'key'        => strtoupper(trim($r->rfpnonpurchaseid)) . '_RCA_CALR_DUE',
                'hid'        => Hashids::encode($r->id),
                'docid'      => $r->rfpnonpurchaseid,
                'status'     => 'RCA_CALR_DUE',
                'label'      => 'Create CALR',
                'message'    => 'Tanggal Realisasi has passed since ' . optional($r->datepenyelesaian)->format('d M Y') . '. Please create the CALR for this document.',
                'cpnyid'     => $r->cpny_id,
                'href'       => '/calrnonpurch/create?rfpnonpurchase=' . Hashids::encode($r->id),
                'url'        => '/calrnonpurch/create',
                'by'         => null,
                'updated_at' => $r->datepenyelesaian,
            ]));
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationService: RCA CALR due fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 10. TrRfp: RFP Purchase on Hold — notify the creator of the linked SPPBJKT
        //       doc (tr_rfp.created_by is frequently "SYSTEM" for auto-generated RFPs, so
        //       the actionable owner is whoever created the SPPB/SPPJ/SPPK/SPPT it points to).
        try {
            $heldRfps = TrRfp::where('status', 'H')
                ->select('id', 'rfp_id', 'cpny_id', 'sppbjkt_id', 'updated_at', 'created_at')
                ->get()
                ->map(function ($r) {
                    $r->sppbjkt_trim = strtoupper(trim((string) $r->sppbjkt_id));
                    $r->doc_prefix   = substr($r->sppbjkt_trim, 0, 2);
                    return $r;
                });

            if ($heldRfps->isNotEmpty()) {
                // Same prefix → model/id-column mapping as RfpController's SPPBJKT link resolver.
                $sppbjktModels = [
                    'PB' => ['model' => TrSPPB::class, 'idCol' => 'sppbid'],
                    'PJ' => ['model' => TrSPPJ::class, 'idCol' => 'sppjid'],
                    'PK' => ['model' => TrSPPK::class, 'idCol' => 'sppkid'],
                    'PT' => ['model' => TrSPPT::class, 'idCol' => 'spptid'],
                ];

                $creatorsByPrefix = [];
                foreach ($sppbjktModels as $prefix => $cfg) {
                    $ids = $heldRfps->where('doc_prefix', $prefix)->pluck('sppbjkt_trim')->unique()->values();
                    if ($ids->isEmpty()) {
                        continue;
                    }

                    $rows = $cfg['model']::query()
                        ->whereIn(DB::raw('UPPER(TRIM(' . $cfg['idCol'] . '))'), $ids->all())
                        ->select($cfg['idCol'] . ' as doc_key', 'created_by')
                        ->get();

                    $creatorsByPrefix[$prefix] = $rows->mapWithKeys(
                        fn($row) => [strtoupper(trim($row->doc_key)) => $row->created_by]
                    );
                }

                $data = $data->concat(
                    $heldRfps->filter(function ($r) use ($username, $creatorsByPrefix) {
                        $creator = $creatorsByPrefix[$r->doc_prefix][$r->sppbjkt_trim] ?? null;
                        return $creator && strtolower(trim($creator)) === $username;
                    })->map(fn($r) => [
                        'key'        => strtoupper(trim($r->rfp_id)) . '_RFP_H',
                        'hid'        => Hashids::encode($r->id),
                        'docid'      => $r->rfp_id,
                        'status'     => 'RFP_H',
                        'label'      => 'On Hold',
                        'message'    => 'RFP Purchase for your document ' . $r->sppbjkt_id . ' is on hold and needs your attention.',
                        'cpnyid'     => $r->cpny_id,
                        'url'        => '/showrfp',
                        'by'         => null,
                        'updated_at' => $r->updated_at ?? $r->created_at,
                    ])->values()
                );
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationService: TrRfp Hold fetch failed', ['err' => $e->getMessage()]);
        }

        // ── 11. VPL Stock Expiry Reminders — Voucher batches (product_type=V) go to
        //       VPCOLLACCESS holders, Product batches (product_type=P) go to VPPRMTNACCESS
        //       holders. Fires only on the exact day a still-in-stock batch is H-90/60/30/
        //       14/7/3 from its expired_date (re-derived live on every buildForUser() call,
        //       so it naturally stops appearing once the day no longer matches).
        try {
            $expiryRoleMap    = ['V' => 'VPCOLLACCESS', 'P' => 'VPPRMTNACCESS'];
            $expiryThresholds = [90, 60, 30, 14, 7, 3];

            $userRoleIds = SysUserRole::whereRaw('lower(trim(username)) = ?', [$username])
                ->whereIn('role_id', array_values($expiryRoleMap))
                ->where('status', 'A')
                ->pluck('role_id')
                ->all();

            $userExpiryTypes = array_keys(array_filter(
                $expiryRoleMap,
                fn($roleId) => in_array($roleId, $userRoleIds, true)
            ));

            if (!empty($userExpiryTypes)) {
                $placeholders = implode(',', array_fill(0, count($expiryThresholds), '?'));

                $expiringBatches = MsVplProductDetail::query()
                    ->join('ms_vpl_product', 'ms_vpl_product.product_id', '=', 'ms_vpl_product_detail.product_id')
                    ->whereIn('ms_vpl_product.product_type', $userExpiryTypes)
                    ->whereNotNull('ms_vpl_product_detail.expired_date')
                    ->whereRaw('(ms_vpl_product_detail.qty_available - COALESCE(ms_vpl_product_detail.qty_reserved, 0)) > 0')
                    ->whereRaw(
                        "(ms_vpl_product_detail.expired_date::date - CURRENT_DATE) IN ({$placeholders})",
                        $expiryThresholds
                    )
                    ->select(
                        'ms_vpl_product_detail.id',
                        'ms_vpl_product_detail.product_id',
                        'ms_vpl_product_detail.expired_date',
                        'ms_vpl_product_detail.cpnyid',
                        'ms_vpl_product_detail.whs_id',
                        DB::raw('(ms_vpl_product_detail.qty_available - COALESCE(ms_vpl_product_detail.qty_reserved, 0)) AS qty_pickable'),
                        DB::raw('(ms_vpl_product_detail.expired_date::date - CURRENT_DATE) AS days_left'),
                        'ms_vpl_product.product_name',
                        'ms_vpl_product.product_type'
                    )
                    ->get();

                $data = $data->concat($expiringBatches->map(function ($r) {
                    $daysLeft  = (int) $r->days_left;
                    $typeLabel = $r->product_type === 'V' ? 'Voucher' : 'Product';

                    return [
                        'key'        => 'VPLEXP_' . $r->product_id . '_' . $r->whs_id . '_' . optional($r->expired_date)->format('Ymd') . '_H' . $daysLeft,
                        'hid'        => Hashids::encode($r->id),
                        'docid'      => $r->product_id,
                        'status'     => 'VPL_EXPIRING',
                        'label'      => "Expiring in {$daysLeft}d",
                        'message'    => "{$typeLabel} {$r->product_name} ({$r->whs_id}) — " . number_format($r->qty_pickable) . ' pcs still in stock, expiring on ' . optional($r->expired_date)->format('d M Y') . '.',
                        'cpnyid'     => $r->cpnyid,
                        'url'        => '/msproduct',
                        'by'         => null,
                        'updated_at' => $r->expired_date,
                    ];
                }));
            }
        } catch (\Throwable $e) {
            Log::warning('DocumentNotificationService: VPL expiry reminder fetch failed', ['err' => $e->getMessage()]);
        }

        return $data->sortByDesc(fn($r) => $r['updated_at'])->values()->all();
    }

    // aprv_username can hold a comma/semicolon-separated list of usernames for a single
    // multi-approver level (see ApprovalController::normalizeApproverList) — split those
    // out so each individual approver resolves to a User row instead of the whole combined
    // string silently failing to match any username and dropping out of the audience.
    public static function splitApproverUsernames(\Illuminate\Support\Collection $raw): \Illuminate\Support\Collection
    {
        return $raw->flatMap(fn ($u) => preg_split('/[;,]/', (string) $u) ?: [])
            ->map(fn ($u) => trim($u))
            ->filter();
    }

    // Whether $username appears on any tr_approval row's aprv_username list for this
    // doctype+refnbr, regardless of step status — used to scope GAACCESS's private-note
    // access to VCR/BCR documents they're actually an approver on.
    public static function isOnApprovalLine(string $doctype, $refnbr, string $username): bool
    {
        $username = strtolower(trim($username));

        return static::splitApproverUsernames(
            TrApproval::where('aprv_doctype', $doctype)
                ->where('refnbr', $refnbr)
                ->pluck('aprv_username')
        )->map(fn ($u) => strtolower($u))->contains($username);
    }

    // Subset of $refnbrs (for this doctype) where $username appears on the approval line.
    public static function approvalLineRefnbrs(string $doctype, array $refnbrs, string $username): \Illuminate\Support\Collection
    {
        if (empty($refnbrs)) {
            return collect();
        }

        $username = strtolower(trim($username));

        return TrApproval::where('aprv_doctype', $doctype)
            ->whereIn('refnbr', $refnbrs)
            ->get(['refnbr', 'aprv_username'])
            ->filter(fn ($row) => static::splitApproverUsernames(collect([$row->aprv_username]))
                ->map(fn ($u) => strtolower($u))
                ->contains($username))
            ->pluck('refnbr')
            ->unique()
            ->values();
    }

    // National holidays + collective leave ("cuti bersama") dates, used to expire comment/
    // mention notifications on business days rather than plain calendar days.
    private static function commentExpiryHolidays(): \Illuminate\Support\Collection
    {
        return SysCalendar::whereNull('deleted_at')
            ->where('status', 'A')
            ->whereIn('date_calendar_type', ['CUTI_BERSAMA', 'LIBUR_NASIONAL'])
            ->where('date_calendar', '>=', now()->subDays(30))
            ->pluck('date_calendar')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
            ->unique();
    }

    // A comment/mention notification expires 5 business days after it was posted —
    // Saturday, Sunday, and national holidays / cuti bersama don't count toward the 5.
    private static function isCommentExpired($messageDate, \Illuminate\Support\Collection $holidays, int $businessDays = 5): bool
    {
        $expiry = \Carbon\Carbon::parse($messageDate);
        for ($added = 0; $added < $businessDays; ) {
            $expiry->addDay();
            if ($expiry->isWeekend() || $holidays->contains($expiry->toDateString())) {
                continue;
            }
            $added++;
        }
        return now()->greaterThanOrEqualTo($expiry);
    }

    // Most doctypes have their own dedicated "show" page keyed by a Hashids-
    // encoded internal id (`{url}/{hid}`). Project/Task pages are instead
    // keyed by their plain business-key string (`/projects/{project_id}`,
    // no separate Task page — it's a tab within the parent Project page),
    // so PRJ/TSK need their own hid resolution rather than the generic
    // Hashids::encode($doc->id) used everywhere else.
    private static function commentDocHid(string $doctype, $doc)
    {
        if ($doctype === 'PRJ') {
            return $doc->project_id;
        }

        if ($doctype === 'TSK') {
            return $doc->project_id;
        }

        return Hashids::encode($doc->id);
    }

    // Creator + approval-line-equivalent audience for a plain (non-mention) comment.
    private static function resolveCommentRecipients(string $doctype, $doc): \Illuminate\Support\Collection
    {
        if ($doctype === 'TIC') {
            return collect([$doc->user_peminta])
                ->merge(
                    MsTicketCategoryDept::where('ticket_categoryid', $doc->ticket_categoryid)
                        ->where('status', 'A')
                        ->pluck('username')
                )
                ->filter()
                ->map(fn($u) => strtolower(trim($u)))
                ->unique();
        }

        if ($doctype === 'PRJ') {
            $group = \App\Models\MsGroup::where('group_id', $doc->group_id)->first();

            return collect([$doc->created_by, $group?->created_by])
                ->merge(
                    \App\Models\TrProjectTaskAssignee::whereIn(
                        'task_id',
                        \App\Models\TrProjectTask::where('project_id', $doc->project_id)->pluck('task_id')
                    )->where('status', 'A')->pluck('username')
                )
                ->filter()
                ->map(fn($u) => strtolower(trim($u)))
                ->unique();
        }

        if ($doctype === 'TSK') {
            $project = \App\Models\MsProject::where('project_id', $doc->project_id)->first();

            return collect([$doc->created_by, $project?->created_by])
                ->merge(
                    \App\Models\TrProjectTaskAssignee::where('task_id', $doc->task_id)
                        ->where('status', 'A')->pluck('username')
                )
                ->filter()
                ->map(fn($u) => strtolower(trim($u)))
                ->unique();
        }

        $extCfg = self::extendedDocTypeConfig()[$doctype] ?? null;
        if ($extCfg) {
            $creatorUsernames = collect($extCfg['creatorFields'])->map(fn($f) => $doc->{$f} ?? null);

            $approvalUsernames = !empty($extCfg['approvalDoctype'])
                ? self::splitApproverUsernames(
                    TrApproval::where('refnbr', $doc->{$extCfg['idCol']})
                        ->where('aprv_doctype', $extCfg['approvalDoctype'])
                        ->pluck('aprv_username')
                )
                : collect();

            $picUsernames = isset($extCfg['picField']) ? collect([$doc->{$extCfg['picField']} ?? null]) : collect();

            $roleUsernames = !empty($extCfg['roleIds'])
                ? self::resolveRoleUsernamesForCompany($extCfg['roleIds'], $doc->{$extCfg['companyCol'] ?? 'cpny_id'} ?? null)
                : collect();

            return $creatorUsernames->merge($approvalUsernames)->merge($picUsernames)->merge($roleUsernames)
                ->filter()
                ->map(fn($u) => strtolower(trim($u)))
                ->unique();
        }

        // ACR and ITR both use user_peminta/created_by + TrApproval approval line, keyed by docid.
        return collect([$doc->user_peminta, $doc->created_by])
            ->merge(self::splitApproverUsernames(TrApproval::where('refnbr', $doc->docid)->pluck('aprv_username')))
            ->filter()
            ->map(fn($u) => strtolower(trim($u)))
            ->unique();
    }

    // Single source of truth for the 9 "creator + approval line (+ optional PIC)" modules,
    // shared by resolveCommentRecipients() above and SendCommentController::mentionableUsers().
    // tr_ticket is shared by three modules (IT / Eng / BS&FO support tickets),
    // each with its own "show" route — the show URL depends on ticket_type.
    private static function ticketShowUrl(?string $ticketType): string
    {
        return in_array($ticketType, ['ENGSUPPORTTICKET', 'BSSUPPORTTICKET', 'FOSUPPORTTICKET', 'BA_BS', 'BA_ENG', 'BA_FO'], true)
            ? '/showoprtekticket'
            : '/showticket';
    }

    public static function extendedDocTypeConfig(): array
    {
        return [
            'PB' => ['model' => TrSPPB::class,        'idCol' => 'sppbid',     'url' => '/showsppbs',     'creatorFields' => ['created_by'],               'approvalDoctype' => 'PB'],
            'PJ' => ['model' => TrSPPJ::class,        'idCol' => 'sppjid',     'url' => '/showsppjs',     'creatorFields' => ['created_by'],               'approvalDoctype' => 'PJ'],
            'PK' => ['model' => TrSPPK::class,        'idCol' => 'sppkid',     'url' => '/showsppks',     'creatorFields' => ['created_by'],               'approvalDoctype' => 'PK'],
            'PT' => ['model' => TrSPPT::class,        'idCol' => 'spptid',     'url' => '/showsppts',     'creatorFields' => ['created_by'],               'approvalDoctype' => 'PT'],
            'IM' => ['model' => TrIMBudget::class,    'idCol' => 'imbudgetid', 'url' => '/showimbudgets', 'creatorFields' => ['user_peminta', 'created_by'], 'approvalDoctype' => 'IM'],
            'CS' => ['model' => TrCS::class,          'idCol' => 'csid',       'url' => '/showcs',        'creatorFields' => ['user_peminta', 'created_by'], 'approvalDoctype' => 'CS'],
            'WO' => ['model' => TrWO::class,          'idCol' => 'woid',       'url' => '/showwos',       'creatorFields' => ['picrequester', 'created_by'], 'approvalDoctype' => 'WO', 'picField' => 'pic_wo'],
            'BA' => ['model' => TrBast::class,        'idCol' => 'bastid',     'url' => '/showbast',      'creatorFields' => ['user_peminta', 'created_by'], 'approvalDoctype' => 'BA'],
            'SR' => ['model' => TrItemRequest::class, 'idCol' => 'irid',       'url' => '/showitemreq',   'creatorFields' => ['created_by', 'pic_item_req'], 'approvalDoctype' => 'SR'],

            'PKR' => ['model' => TrParkingRegistration::class, 'idCol' => 'docid',            'url' => '/showparkingregistration', 'creatorFields' => ['user_peminta', 'created_by'], 'approvalDoctype' => 'PKR'],
            'PRF' => ['model' => Personnel::class,              'idCol' => 'docid',            'url' => '/showpersonnels',          'creatorFields' => ['created_user'],               'approvalDoctype' => 'PRF'],

            'RFP' => ['model' => TrRfpNonPurch::class, 'idCol' => 'rfpnonpurchaseid', 'url' => '/showrfpnonpurch', 'creatorFields' => ['created_by'], 'approvalDoctype' => 'RFP', 'roleIds' => ['APFINACCESS', 'APTREACCESS', 'FINACCESS']],
            'RCA' => ['model' => TrRfpNonPurch::class, 'idCol' => 'rfpnonpurchaseid', 'url' => '/showrfpnonpurch', 'creatorFields' => ['created_by'], 'approvalDoctype' => 'RCA', 'roleIds' => ['APFINACCESS', 'APTREACCESS', 'FINACCESS']],
            'CAR' => ['model' => TrCalrNonPurch::class, 'idCol' => 'calrnonpurchaseid', 'url' => '/showcalrnonpurch', 'creatorFields' => ['created_by'], 'approvalDoctype' => 'CAR', 'roleIds' => ['APFINACCESS', 'APTREACCESS', 'FINACCESS']],

            'RP' => ['model' => TrRfp::class,  'idCol' => 'rfp_id', 'url' => '/showrfp',  'creatorFields' => ['created_by'],               'approvalDoctype' => 'RP', 'roleIds' => ['APFINACCESS', 'APTREACCESS', 'FINACCESS']],
            // RFCA has no TrApproval line of its own (routed via TrRfcaStep instead) — no approvalDoctype by design.
            'RC' => ['model' => TrRfca::class, 'idCol' => 'rfcaid', 'url' => '/showrfca', 'creatorFields' => ['user_peminta', 'created_by'], 'roleIds' => ['APFINACCESS', 'APTREACCESS', 'FINACCESS']],
            'CA' => ['model' => TrCalr::class, 'idCol' => 'calrid', 'url' => '/showcalr', 'creatorFields' => ['user_peminta', 'created_by'], 'approvalDoctype' => 'CA', 'roleIds' => ['APFINACCESS', 'APTREACCESS', 'FINACCESS']],

            // Training registration: seat-lifecycle events (offer, accept, decline, manual
            // accept) keep firing after approval reaches 'C', so this is never terminal —
            // see the loop above that builds $commentDocTypes.
            'TRN' => ['model' => TrLndTrainingRegistration::class, 'idCol' => 'training_regist_id', 'url' => '/training-list/my', 'creatorFields' => ['created_by'], 'approvalDoctype' => 'TRN', 'roleIds' => ['HCDEVACCESS'], 'terminalStatuses' => []],
        ];
    }

    // Company-scoped role broadcast: e.g. all APFINACCESS/APTREACCESS holders who share the
    // document's own company. No existing "role-holders by company" query existed to reuse —
    // this combines the role-list pluck idiom (SysUserRole::whereIn('role_id',...)->pluck)
    // with the comma-separated User.cpny_id parsing idiom already used elsewhere in the app.
    public static function resolveRoleUsernamesForCompany(array $roleIds, $cpnyId): \Illuminate\Support\Collection
    {
        if (empty($roleIds) || !$cpnyId) {
            return collect();
        }

        $roleUsernames = SysUserRole::whereIn('role_id', $roleIds)
            ->where('status', 'A')
            ->pluck('username')
            ->map(fn($u) => strtolower(trim($u)))
            ->unique();

        if ($roleUsernames->isEmpty()) {
            return collect();
        }

        return User::whereIn(DB::raw('lower(username)'), $roleUsernames->all())
            ->get(['username', 'cpny_id', 'user_role'])
            ->filter(function ($u) use ($cpnyId) {
                $ids = is_string($u->cpny_id)
                    ? array_filter(array_map('trim', explode(',', $u->cpny_id)))
                    : (array) $u->cpny_id;
                return in_array((string) $cpnyId, $ids, true);
            })
            // Admin accounts sometimes pick up finance/treasury access roles for testing or
            // oversight — they're not real finance/treasury staff, so skip them here rather
            // than spamming an admin account on every document comment in these doctypes.
            ->reject(fn($u) => $u->isAdmin())
            ->pluck('username')
            ->map(fn($u) => strtolower(trim($u)));
    }
}
