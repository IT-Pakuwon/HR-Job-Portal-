<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\MsTicketCategoryDept;
use App\Models\SysUserRole;
use App\Models\TrCS;
use App\Models\TrTicket;
use App\Models\VUserDocument;

class GlobalSearchController extends Controller
{
    // The four consolidated views already used by DocumentNotificationService to
    // resolve docid -> url across every module; url (not doctype) is the unique
    // key since a few views reuse the same doctype code for different modules
    // (e.g. "ACR" covers parking, access request, booking car and voucher taxi).
    private const VIEWS = [
        ['connection' => 'pgsql3', 'table' => 'view_trx_career'],
        ['connection' => 'mysql3', 'table' => 'v_job_apply_with_posting'],
        ['connection' => 'pgsql',  'table' => 'v_tr_purch'],
        ['connection' => 'pgsql5', 'table' => 'v_all_das'],
    ];

    private const URL_LABELS = [
        '/showstos'                => 'STO',
        '/shownews'                => 'News',
        '/showtasks'               => 'Task',
        '/showchangestos'          => 'Change STO',
        '/showpersonnels'          => 'Personnel',
        '/showcareers'             => 'Career Applicant',
        '/showimbudgets'           => 'IM Budget',
        '/showimbudgetnonpurch'    => 'IM Budget (Non Purch)',
        '/showrfp'                 => 'RFP',
        '/showrfpnonpurch'         => 'RFP (Non Purch)',
        '/showsppts'               => 'SPPT',
        '/showsppjs'               => 'SPPJ',
        '/showsppbs'               => 'SPPB',
        '/showsppks'               => 'SPPK',
        '/showcalr'                => 'CALR',
        '/showcalrnonpurch'        => 'CALR (Non Purch)',
        '/showbast'                => 'BAST',
        '/showbudgets'             => 'Budget',
        '/showwos'                 => 'Work Order',
        '/showreceipt'             => 'Goods Receipt',
        '/showissue'               => 'Issue',
        '/showspbs'                => 'SPB',
        '/showitemreq'             => 'Item Request',
        '/showcs'                  => 'Canvass',
        '/showparkingregistration' => 'Parking Registration',
        '/showitrecommendation'    => 'IT Recommendation',
        '/showaccessrequest'       => 'Access Request',
        '/showbookingcar'          => 'Booking Car',
        '/showvouchertaxi'         => 'Voucher Taxi',
        '/showticket'              => 'Ticket',
    ];

    public function search(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['data' => []], 401);

        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) return response()->json(['data' => []]);

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        if (empty($cpnyIds)) return response()->json(['data' => []]);

        $departmentIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        if (empty($departmentIds)) return response()->json(['data' => []]);

        $username = strtolower(trim((string) $user->username));

        $results = collect();

        foreach (self::VIEWS as $view) {
            try {
                $driver = config("database.connections.{$view['connection']}.driver");
                $likeOp = $driver === 'pgsql' ? 'ilike' : 'like';

                $rows = DB::connection($view['connection'])->table($view['table'])
                    ->whereIn('cpnyid', $cpnyIds)
                    ->whereIn('departementid', $departmentIds)
                    ->whereRaw("lower(trim(coalesce(created_user,''))) = ?", [$username])
                    ->where(function ($query) use ($q, $likeOp) {
                        $query->where('docid', $likeOp, "%{$q}%")
                              ->orWhere('infohd', $likeOp, "%{$q}%");
                    })
                    ->orderByDesc('docdate')
                    ->limit(8)
                    ->get();

                $results = $results->concat($rows);
            } catch (\Throwable $e) {
                Log::warning('GlobalSearchController: view fetch failed', [
                    'table' => $view['table'],
                    'err'   => $e->getMessage(),
                ]);
            }
        }

        // Ticket category PIC staff can search every ticket routed to their category,
        // not just tickets they personally raised (mirrors DocumentNotificationService's
        // "new ticket" rule for IT staff assigned via MsTicketCategoryDept).
        try {
            $assignedCategories = MsTicketCategoryDept::whereRaw("lower(trim(username)) = ?", [$username])
                ->where('status', 'A')
                ->pluck('ticket_categoryid')
                ->unique();

            if ($assignedCategories->isNotEmpty()) {
                $ticketRows = TrTicket::whereIn('cpny_id', $cpnyIds)
                    ->whereIn('ticket_categoryid', $assignedCategories)
                    ->where(function ($query) use ($q) {
                        $query->where('ticketid', 'ilike', "%{$q}%")
                              ->orWhere('issue_summary', 'ilike', "%{$q}%")
                              ->orWhere('issue_descr', 'ilike', "%{$q}%");
                    })
                    ->orderByDesc('ticketdate')
                    ->limit(8)
                    ->get(['id', 'ticketid', 'ticketdate', 'cpny_id', 'department_id', 'issue_summary', 'status']);

                $results = $results->concat($ticketRows->map(fn ($r) => (object) [
                    'id'            => $r->id,
                    'docid'         => $r->ticketid,
                    'docdate'       => $r->ticketdate,
                    'cpnyid'        => $r->cpny_id,
                    'departementid' => $r->department_id,
                    'infohd'        => $r->issue_summary,
                    'status'        => $r->status,
                    'url'           => '/showticket',
                ]));
            }
        } catch (\Throwable $e) {
            Log::warning('GlobalSearchController: ticket category fetch failed', ['err' => $e->getMessage()]);
        }

        // Anyone in a document's approval line can find it, not just its creator —
        // tr_approval already records exactly this audience for every doctype it
        // routes, cross-department and (for company-wide roles) cross-company. It
        // lives in a different physical Postgres database than the document views
        // (db_iamsystem vs db_purchasing_app / db_das_test), so we can't do this as
        // a single SQL join: resolve the visible refnbr list here first, then
        // cross-reference it against v_tr_purch / v_all_das.
        try {
            $approvedRefnbrs = VUserDocument::whereRaw("lower(trim(aprv_username)) = ?", [$username])
                ->whereIn('aprv_cpnyid', $cpnyIds)
                ->distinct()
                ->limit(5000)
                ->pluck('refnbr');

            if ($approvedRefnbrs->isNotEmpty()) {
                foreach ([['pgsql', 'v_tr_purch'], ['pgsql5', 'v_all_das']] as [$conn, $table]) {
                    $driver = config("database.connections.{$conn}.driver");
                    $likeOp = $driver === 'pgsql' ? 'ilike' : 'like';

                    $rows = DB::connection($conn)->table($table)
                        ->whereIn('docid', $approvedRefnbrs)
                        ->where(function ($query) use ($q, $likeOp) {
                            $query->where('docid', $likeOp, "%{$q}%")
                                  ->orWhere('infohd', $likeOp, "%{$q}%");
                        })
                        ->orderByDesc('docdate')
                        ->limit(8)
                        ->get();

                    $results = $results->concat($rows);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GlobalSearchController: approval-line fetch failed', ['err' => $e->getMessage()]);
        }

        // MGRPURCHACCESS holders can search every CS (Canvass Sheet) document company-wide,
        // unfiltered by company/department/creator — purchasing managers approve across
        // the board and need to look any CS up by docid or keperluan, even the ~1.5%
        // the approval-line query above doesn't happen to cover yet.
        try {
            $isPurchManager = SysUserRole::whereRaw("lower(trim(username)) = ?", [$username])
                ->where('role_id', 'MGRPURCHACCESS')
                ->where('status', 'A')
                ->exists();

            if ($isPurchManager) {
                $csRows = TrCS::where(function ($query) use ($q) {
                        $query->where('csid', 'ilike', "%{$q}%")
                              ->orWhere('keperluan', 'ilike', "%{$q}%");
                    })
                    ->orderByDesc('csdate')
                    ->limit(8)
                    ->get(['id', 'csid', 'csdate', 'cpny_id', 'department_id', 'keperluan', 'status']);

                $results = $results->concat($csRows->map(fn ($r) => (object) [
                    'id'            => $r->id,
                    'docid'         => $r->csid,
                    'docdate'       => $r->csdate,
                    'cpnyid'        => $r->cpny_id,
                    'departementid' => $r->department_id,
                    'infohd'        => $r->keperluan,
                    'status'        => $r->status,
                    'url'           => '/showcs',
                ]));
            }
        } catch (\Throwable $e) {
            Log::warning('GlobalSearchController: CS purchasing manager fetch failed', ['err' => $e->getMessage()]);
        }

        $data = $results
            ->unique('docid')
            ->sortByDesc('docdate')
            ->take(20)
            ->map(fn ($r) => [
                'docid'      => $r->docid,
                'label'      => self::URL_LABELS[$r->url] ?? $r->doctype,
                'infohd'     => self::truncateInfo($r->infohd),
                'cpnyid'     => $r->cpnyid,
                'department' => $r->departementid,
                'status'     => $r->status,
                'docdate'    => $r->docdate,
                'href'       => rtrim($r->url, '/') . '/' . Hashids::encode($r->id),
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    // A handful of doc types (e.g. ticket issue_summary) store rich-text with inline
    // base64 images in this column — strip markup and cap the length so a single
    // result can't balloon the response.
    private static function truncateInfo(?string $value): ?string
    {
        if ($value === null || $value === '') return $value;

        $plain = trim(strip_tags($value));

        return mb_strlen($plain) > 160 ? mb_substr($plain, 0, 160) . '…' : $plain;
    }
}
