<?php

namespace App\Http\Controllers;

use App\Models\TrMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

// Backs the "My Message" / "My Document" panels shown on every dashboard
// (components/multidashboard/my-activity.blade.php).
class MyActivityController extends Controller
{
    // Same four consolidated docid -> url views GlobalSearchController uses.
    private const VIEWS = [
        ['connection' => 'pgsql3', 'table' => 'view_trx_career'],
        ['connection' => 'mysql3', 'table' => 'v_job_apply_with_posting'],
        ['connection' => 'pgsql',  'table' => 'v_tr_purch'],
        ['connection' => 'pgsql5', 'table' => 'v_all_das'],
    ];

    // url (not doctype) is the unique key — "ACR" covers several modules.
    private const URL_LABELS = [
        '/showstos' => 'STO', '/shownews' => 'News', '/showtasks' => 'Task',
        '/showchangestos' => 'Change STO', '/showpersonnels' => 'Personnel',
        '/showcareers' => 'Career Applicant', '/showimbudgets' => 'IM Budget',
        '/showimbudgetnonpurch' => 'IM Budget (Non Purch)', '/showrfp' => 'RFP',
        '/showrfpnonpurch' => 'RFP (Non Purch)', '/showsppts' => 'SPPT',
        '/showsppjs' => 'SPPJ', '/showsppbs' => 'SPPB', '/showsppks' => 'SPPK',
        '/showcalr' => 'CALR', '/showcalrnonpurch' => 'CALR (Non Purch)',
        '/showbast' => 'BAST', '/showbudgets' => 'Budget', '/showwos' => 'Work Order',
        '/showreceipt' => 'Goods Receipt', '/showissue' => 'Issue', '/showspbs' => 'SPB',
        '/showitemreq' => 'Item Request', '/showcs' => 'CS',
        '/showparkingregistration' => 'Parking Registration',
        '/showitrecommendation' => 'IT Recommendation',
        '/showaccessrequest' => 'Access Request', '/showbookingcar' => 'Booking Car',
        '/showvouchertaxi' => 'Voucher Taxi', '/showticket' => 'Ticket',
    ];

    private const STATUS_LABELS = [
        'H' => 'Hold', 'P' => 'In Approval', 'D' => 'Revised', 'R' => 'Rejected',
        'C' => 'Completed', 'X' => 'Cancelled', 'W' => 'Waiting', 'F' => 'Finished',
        'U' => 'Unposted', 'I' => 'In Progress', 'A' => 'Active',
    ];

    private const PAGE_SIZE = 10;

    // Documents come from four separate databases, so they are merged in memory and
    // paged from that: newest N per view is plenty for one person's own documents.
    private const DOC_FETCH_LIMIT = 500;

    // GET /my-activity/documents?q=&type=
    public function documents(Request $request)
    {
        $username = $this->username($request);
        if (!$username) return response()->json(['data' => [], 'types' => []], 401);

        $q    = trim((string) $request->query('q'));
        $type = trim((string) $request->query('type'));
        $stat = strtoupper(trim((string) $request->query('status')));

        $rows = collect();

        foreach (self::VIEWS as $view) {
            try {
                $driver = config("database.connections.{$view['connection']}.driver");
                $likeOp = $driver === 'pgsql' ? 'ilike' : 'like';

                $query = DB::connection($view['connection'])->table($view['table'])
                    ->whereRaw("lower(trim(coalesce(created_user,''))) = ?", [$username]);

                if ($q !== '') {
                    $query->where(fn ($w) => $w->where('docid', $likeOp, "%{$q}%")
                        ->orWhere('infohd', $likeOp, "%{$q}%"));
                }

                $rows = $rows->concat($query->orderByDesc('docdate')->limit(self::DOC_FETCH_LIMIT)->get());
            } catch (\Throwable $e) {
                Log::warning('MyActivityController: document view failed', [
                    'table' => $view['table'], 'err' => $e->getMessage(),
                ]);
            }
        }

        $all = $rows->unique('docid')->map(fn ($r) => [
            'docid'  => $r->docid,
            'type'   => self::URL_LABELS[$r->url] ?? $r->doctype,
            'status' => $this->statusLabel($r->status),
            'code'   => strtoupper(trim((string) $r->status)),
            'info'   => $this->plain($r->infohd, 100),
            'date'   => $r->docdate,
            'href'   => rtrim($r->url, '/') . '/' . Hashids::encode($r->id),
        ]);

        $types = $all->pluck('type')->unique()->sort()->values();

        $statuses = $all->unique('code')->sortBy('status')
            ->map(fn ($r) => ['code' => $r['code'], 'label' => $r['status']])->values();

        if ($type !== '') $all = $all->where('type', $type);
        if ($stat !== '') $all = $all->where('code', $stat);

        $all   = $all->sortByDesc('date')->values();
        $total = $all->count();
        $page  = $this->page($request, $total);

        return response()->json([
            'data'  => $all->slice(($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE)->values(),
            'types' => $types,
            'statuses' => $statuses,
        ] + $this->pageMeta($page, $total));
    }

    // GET /my-activity/messages?q=
    public function messages(Request $request)
    {
        $username = $this->username($request);
        if (!$username) return response()->json(['data' => []], 401);

        $q = trim((string) $request->query('q'));

        $query = TrMessage::whereRaw("lower(trim(coalesce(username,''))) = ?", [$username])
            ->where('status', 'A')
            ->where(fn ($w) => $w->whereNull('message_type')
                ->orWhere('message_type', 'not like', 'S\_%'));  // skip system events

        if ($q !== '') {
            $query->where(fn ($w) => $w->where('message', 'ilike', "%{$q}%")
                ->orWhere('refnbr', 'ilike', "%{$q}%"));
        }

        $total = (clone $query)->count();
        $page  = $this->page($request, $total);

        $messages = $query->orderByDesc('message_date')->orderByDesc('id')
            ->forPage($page, self::PAGE_SIZE)
            ->get(['id', 'refnbr', 'doctype', 'message_date', 'message_type', 'message']);

        // Resolve each commented document's link/type/status in one pass per view.
        $docs = $this->resolveDocuments($messages->pluck('refnbr')->filter()->unique()->values());

        $data = $messages->map(function ($m) use ($docs) {
            $doc = $docs->get($m->refnbr);

            return [
                'id'      => $m->id,
                'docid'   => $m->refnbr,
                'type'    => $doc['type'] ?? $m->doctype,
                'private' => $m->message_type === 'Private',
                'text'    => Str::limit(TrMessage::plainText(strip_tags((string) $m->message)), 220),
                'date'    => $m->message_date,
                'href'    => $doc['href'] ?? null,
            ];
        });

        return response()->json(['data' => $data] + $this->pageMeta($page, $total));
    }

    // Requested page, clamped to 1..last so a stale page number after a new filter still lands on data.
    private function page(Request $request, int $total): int
    {
        $last = max(1, (int) ceil($total / self::PAGE_SIZE));

        return min(max(1, (int) $request->query('page', 1)), $last);
    }

    private function pageMeta(int $page, int $total): array
    {
        return [
            'page'      => $page,
            'last_page' => max(1, (int) ceil($total / self::PAGE_SIZE)),
            'total'     => $total,
            'from'      => $total ? ($page - 1) * self::PAGE_SIZE + 1 : 0,
            'to'        => min($page * self::PAGE_SIZE, $total),
        ];
    }

    private function resolveDocuments($docids)
    {
        $map = collect();
        if ($docids->isEmpty()) return $map;

        foreach (self::VIEWS as $view) {
            try {
                $found = DB::connection($view['connection'])->table($view['table'])
                    ->whereIn('docid', $docids->all())
                    ->get(['id', 'docid', 'doctype', 'url']);

                foreach ($found as $r) {
                    $map[$r->docid] ??= [
                        'type' => self::URL_LABELS[$r->url] ?? $r->doctype,
                        'href' => rtrim($r->url, '/') . '/' . Hashids::encode($r->id),
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('MyActivityController: resolve failed', [
                    'table' => $view['table'], 'err' => $e->getMessage(),
                ]);
            }
        }

        return $map;
    }

    private function username(Request $request): ?string
    {
        $u = $request->user()?->username;

        return $u ? strtolower(trim($u)) : null;
    }

    private function statusLabel(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        return self::STATUS_LABELS[$code] ?? ($code ?: '-');
    }

    // Some columns hold rich text with inline base64 images — strip and cap.
    private function plain(?string $value, int $max): ?string
    {
        if ($value === null || $value === '') return $value;

        return Str::limit(trim(strip_tags($value)), $max);
    }
}
