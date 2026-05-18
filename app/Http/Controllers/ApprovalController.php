<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\MsApproval;
use App\Models\TrApproval;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ApprovalController extends Controller
{
    /** =========================
     *  Utilities & Common Maps
     *  ========================= */
    protected array $subjectMap = [
        'P' => 'Waiting Approval',
        'R' => 'Rejected Approval',
        'D' => 'Revise Approval',
        'A' => 'Approved',
        'C' => 'Completed',
    ];

    protected function orderByLevel($query)
    {
        return $query->orderByRaw("CAST(aprv_leveling AS numeric) ASC");
    }

    protected function normalizeApproverList(?string $raw): array
    {
        if (!$raw) return [];
        $arr = preg_split('/[;,]/', $raw) ?: [];
        return array_filter(array_map(fn($s) => strtolower(trim($s)), $arr));
    }

    /** =========================
     *  CONDITION helpers (NEW)
     *  ========================= */

    protected function normalizeCondition(?string $raw): string
    {
        $s = mb_strtolower(trim((string)$raw));
        $s = preg_replace('/\s+/', ' ', $s);
        return $s ?? '';
    }

    /**
     * Ambil daftar condition dari context dokumen.
     * - 'approval_conditions' => ['MEP','Improvement MEP']
     * - atau 'approval_condition' => 'MEP'
     */
    protected function ctxConditions(array $ctx): array
    {
        $conds = [];

        if (!empty($ctx['approval_conditions']) && is_array($ctx['approval_conditions'])) {
            $conds = array_merge($conds, $ctx['approval_conditions']);
        }

        if (!empty($ctx['approval_condition']) && is_string($ctx['approval_condition'])) {
            $conds[] = $ctx['approval_condition'];
        }

        $conds = array_map(fn($c) => $this->normalizeCondition($c), $conds);
        $conds = array_values(array_unique(array_filter($conds)));

        return $conds;
    }

    /**
     * Generic check condition:
     * Rule aprv_condition akan lolos kalau match ke salah satu condition di dokumen.
     * Support exact + contains (biar fleksibel "mep" vs "improvement mep").
     */
    protected function checkDocCondition(MsApproval $rule, array $ctx): bool
    {
        $ruleCond = $this->normalizeCondition($rule->aprv_condition);
        if ($ruleCond === '') return false;

        $docConds = $this->ctxConditions($ctx);
        if (empty($docConds)) return false;

        // 1) exact match
        if (in_array($ruleCond, $docConds, true)) return true;

        // 2) contains match (SATU ARAH SAJA)
        // docCond mengandung ruleCond (bukan kebalikannya)
        foreach ($docConds as $dc) {
            if ($dc !== '' && str_contains($dc, $ruleCond)) {
                return true;
            }
        }

        return false;
    }


    /** ========================================================
     *  1) Load & Filter Approval Lines (Normal/Condition Rules)
     *  ======================================================== */

    /**
     * Ambil SEMUA line MsApproval aktif untuk doctype/cpny/dept (belum difilter kondisi).
     */
    public function loadLines(string $doctype, $cpnyId, $deptId)
    {
        $lines = MsApproval::query()
            ->where('status', 'A')
            ->where('aprv_doctype', $doctype)
            ->where('aprv_cpnyid', $cpnyId)
            ->where('aprv_departementid', $deptId)
            ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
            ->get();

        if ($lines->isEmpty()) {
            abort(422, 'Approval line belum di-setup, Please contact IT!');
        }

        return $lines;
    }

    // =======================
    // CHECKERS per-kondisi
    // =======================
    protected function checkNormal(MsApproval $rule, array $ctx): bool
    {
        return true; // selalu lolos
    }

    protected function checkUrgent(MsApproval $rule, array $ctx): bool
    {
        // contoh sumber: header.is_urgent
        return !empty($ctx['is_urgent']);
    }

    protected function checkKomputer(MsApproval $rule, array $ctx): bool
    {
        // sumber: hanya dari BARIS PERTAMA inventory_category
        $cat = mb_strtolower((string)($ctx['first_inventory_category'] ?? ''));
        if ($cat === '') return false;

        return str_contains($cat, 'komputer');
    }

    protected function checkFixedAsset(MsApproval $rule, array $ctx): bool
    {
        // sumber: minimal ADA SATU detail inventory_sub_type = "Fixed Asset" / "FA"
        return !empty($ctx['has_fixed_asset_subtype']);
    }

    protected function checkNominal(MsApproval $rule, array $ctx): bool
    {
        // SPPB: minta abaikan nominal (kecuali dokumen lain)
        if (!empty($ctx['ignore_nominal'])) return false;

        $total = (float)($ctx['grand_total'] ?? 0);
        $start = is_null($rule->aprv_start_nominal) ? null : (float)$rule->aprv_start_nominal;
        $end   = is_null($rule->aprv_end_nominal)   ? null : (float)$rule->aprv_end_nominal;

        $geStart = is_null($start) ? true : ($total >= $start);
        $leEnd   = is_null($end)   ? true : ($total <= $end);
        return $geStart && $leEnd;
    }

    /**
     * Dispatcher: pilih checker berdasarkan nama condition.
     * NOTE:
     * - Jika condition tidak ada di map ini, akan fallback ke checkDocCondition() (untuk WO: HVAC/MEP/CIVIL/HSK/Improvement ...).
     */
    protected function getConditionChecker(string $cond): ?callable
    {
        $map = [
            'normal'       => [$this, 'checkNormal'],
            'urgent'       => [$this, 'checkUrgent'],
            'komputer'     => [$this, 'checkKomputer'],
            'fixed asset'  => [$this, 'checkFixedAsset'],
            'nominal'      => [$this, 'checkNominal'],

            'stock'        => [$this, 'checkStockNonStock'],
            'nonstock'     => [$this, 'checkStockNonStock'],
        ];
        $key = $this->normalizeCondition($cond);
        return $map[$key] ?? null;
    }

    /**
     * Evaluasi satu rule MsApproval terhadap context dokumen.
     */
    protected function evaluateCondition_xxx(MsApproval $rule, array $ctx): bool
    {
        $type = trim((string)$rule->aprv_type);

        // Normal → selalu lolos
        if ($type === '' || strcasecmp($type, 'Normal') === 0) {
            return $this->checkNormal($rule, $ctx);
        }

        // Selain "Condition" → anggap tidak lolos
        if (strcasecmp($type, 'Condition') !== 0) {
            return false;
        }

        // Condition:
        // 1) coba checker khusus (urgent/komputer/fixed asset/nominal)
        $cond = trim((string)$rule->aprv_condition);
        $checker = $this->getConditionChecker($cond);
        if ($checker) {
            return call_user_func($checker, $rule, $ctx);
        }

        // 2) fallback generic match (untuk kondisi WO seperti di gambar)
        return $this->checkDocCondition($rule, $ctx);
    }

    protected function evaluateCondition(MsApproval $rule, array $ctx): bool
    {
        $type = trim((string) $rule->aprv_type);

        /*
        |--------------------------------------------------------------------------
        | Normal approval
        |--------------------------------------------------------------------------
        | Kalau aprv_type kosong atau Normal, langsung lolos.
        */
        if ($type === '' || strcasecmp($type, 'Normal') === 0) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Selain Condition, tidak lolos
        |--------------------------------------------------------------------------
        */
        if (strcasecmp($type, 'Condition') !== 0) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Khusus Parking Registration
        |--------------------------------------------------------------------------
        | Jika ctx membawa site_id_parking, maka aprv_condition wajib sama dengan
        | site_id_parking.
        |
        | Contoh:
        | site_id_parking = AW
        | aprv_condition  = AW
        | Maka approval line dipakai.
        |--------------------------------------------------------------------------
        */
        if (array_key_exists('site_id_parking', $ctx)) {
            return $this->checkSiteParking($rule, $ctx);
        }

        /*
        |--------------------------------------------------------------------------
        | Logic condition lama
        |--------------------------------------------------------------------------
        | Biarkan flow lain tetap jalan seperti sebelumnya.
        */
        $cond = trim((string) $rule->aprv_condition);

        $checker = $this->getConditionChecker($cond);

        if ($checker) {
            return call_user_func($checker, $rule, $ctx);
        }

        return $this->checkDocCondition($rule, $ctx);
    }

    /**
     * Filter MsApproval berdasarkan context.
     */
    protected function filterLinesByContext($allLines, array $ctx)
    {
        return $allLines->filter(function (MsApproval $r) use ($ctx) {
            return $this->evaluateCondition($r, $ctx);
        })->values();
    }

    /**
     * Generate TrApproval dari MsApproval TERPILIH (sudah difilter context).
     * Return: [first_level_username_string|null, count]
     */
    public function generateForDocument(
        string $refnbr,
        string $doctype,
        $cpnyId,
        $deptId,
        string $createdBy,
        array $ctx = [],
        ?Carbon $now = null
    ): array {
        $now = $now ?? Carbon::now();

        // 1) load semua line aktif
        $allLines = $this->loadLines($doctype, $cpnyId, $deptId);

        // 2) filter berdasarkan context
        $picked = $this->filterLinesByContext($allLines, $ctx);

        // fallback: kalau tak ada yang match, sisakan semua NORMAL
        if ($picked->isEmpty()) {
            $picked = $allLines->filter(fn($r) => strcasecmp($r->aprv_type, 'Normal') === 0)->values();
        }

        if ($picked->isEmpty()) {
            abort(422, 'Approval line tidak valid (tidak ada rule yang match).');
        }

        $firstLevel = (string)$picked->first()->aprv_leveling;

        foreach ($picked as $m) {
            TrApproval::create([
                'refnbr'             => $refnbr,
                'aprv_leveling'      => $m->aprv_leveling,
                'aprv_doctype'       => $m->aprv_doctype,
                'aprv_cpnyid'        => $m->aprv_cpnyid,
                'aprv_departementid' => $m->aprv_departementid,
                'aprv_username'      => $m->aprv_username,
                'aprv_name'          => $m->aprv_name,
                'aprv_type'          => $m->aprv_type,
                'aprv_condition'     => $m->aprv_condition,
                'aprv_start_nominal' => $m->aprv_start_nominal,
                'aprv_end_nominal'   => $m->aprv_end_nominal,
                'aprv_datebefore'    => ((string)$m->aprv_leveling === $firstLevel) ? $now : null,
                'aprv_dateafter'     => null,
                'status'             => 'P',
                'created_by'         => $createdBy,
            ]);
        }

        $first = $picked->first();
        $firstUsernames = $first ? $first->aprv_username : null;

        return [$firstUsernames, $picked->count()];
    }

    /** ==================================
     *  2) Notifikasi (Email) Reusable
     *  ================================== */

    public function notifyFirstApprover(
        string $refnbr,
        string $doctype,
        string $statusCode,
        string $docDisplayName,
        string $urlToDoc,
        array $extraEmailData = []
    ): int {
        $firstPending = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('aprv_doctype', $doctype)
            ->where('status', 'P');

        $this->orderByLevel($firstPending);
        $firstPending = $firstPending->first();

        if (!$firstPending) return 0;

        $suffix = $this->subjectMap[$statusCode] ?? 'Notification';

        $usernames = str_replace(';', ',', (string)$firstPending->aprv_username);
        $approvers = array_filter(array_map('trim', explode(',', $usernames)));
        if (!$approvers) return 0;

        $emails = User::query()
            ->whereIn('username', $approvers)
            ->where('status', 'A')
            ->pluck('notification_email')
            ->filter()
            ->values();

        $data = array_merge([
            'docid'     => $refnbr,
            'cpnyid'    => $firstPending->aprv_cpnyid,
            'deptname'  => $firstPending->aprv_departementid,
            'date'      => $firstPending->aprv_datebefore,
            'name'      => $firstPending->aprv_name,
            'status'    => $statusCode,
            'docname'   => $docDisplayName,
            'url'       => $urlToDoc,
        ], $extraEmailData);

        foreach ($emails as $email) {
            Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $refnbr, $suffix, $docDisplayName) {
                $message->to($email)
                    ->subject($refnbr . ' - ' . $suffix . ' ' . $docDisplayName)
                    ->from(config('mail.from.address'), config('app.name'));
            });
        }

        return $emails->count();
    }

    public function notifyRequesterOnStatus(
        string $refnbr,
        string $docDisplayName,
        string $statusCode,
        string $creatorUsername,
        string $urlToDoc,
        array $extra = []
    ): int {
        $suffix = $this->subjectMap[$statusCode] ?? 'Notification';
        $user   = User::where('username', $creatorUsername)->where('status', 'A')->first();
        if (!$user) return 0;

        $defaults = [
            'docid'     => $refnbr,
            'docname'   => $docDisplayName,
            'status'    => $statusCode,
            'url'       => $urlToDoc,
            'createdby' => $user->name ?? $creatorUsername,
            'name'      => $user->name ?? $creatorUsername,
            'fullname'  => $user->name ?? $creatorUsername,
            'cpnyid'    => '',
            'deptname'  => '',
            'date'      => Carbon::now()->toDateString(),
            'info'      => '',
        ];

        $payload = array_merge($defaults, $extra);

        $to = $user->notification_email ?? $user->email;
        if (!$to) return 0;

        Mail::send('emails.mailapprovenew', $payload, function ($m) use ($payload, $to, $suffix) {
            $m->to($to)
                ->subject($payload['docid'] . ' - ' . $suffix . ' ' . $payload['docname'])
                ->from(config('mail.from.address'), config('app.name'));
        });

        return 1;
    }

    /** ===========================================
     *  3) Flow Generik: Approve / Reject / Revise
     *  =========================================== */

    public function assertUserCanAct(string $refnbr, string $doctype, string $action, string $username): array
    {
        $needsActive = in_array(strtolower($action), ['approve', 'reject', 'revise'], true);

        $base = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('aprv_doctype', $doctype)
            ->where('status', 'P');

        $this->orderByLevel($base);

        $active = (clone $base)->whereNotNull('aprv_datebefore')->first();

        if ($needsActive && !$active) {
            $active = $this->ensureActiveStep($refnbr, $doctype);
            if (!$active) {
                return [false, null, 'No pending approval step.'];
            }
        }

        $step = $needsActive ? $active : $base->first();
        if (!$step) return [false, null, 'No pending approval step.'];

        $list = $this->normalizeApproverList($step->aprv_username);
        if (!in_array(strtolower($username), $list, true)) {
            return [false, null, "You can't {$action}."];
        }

        return [true, $step, null];
    }

    public function approveStep(
        string $refnbr,
        string $doctype,
        string $actorUsername,
        string $actorName,
        \Closure $onComplete,
        ?\Closure $onNotifyNext = null
    ): array {
        $now = Carbon::now();

        [$ok, $current, $msg] = $this->assertUserCanAct($refnbr, $doctype, 'approve', $actorUsername);
        if (!$ok) return ['ok' => false, 'message' => $msg];

        DB::beginTransaction();
        try {
            $current->status         = 'A';
            $current->aprv_dateafter = $now;
            $current->aprv_username  = $actorUsername;
            $current->aprv_name      = $actorName;
            $current->save();

            $pendingCount = TrApproval::query()
                ->where('refnbr', $refnbr)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->count();

            if ($pendingCount === 0) {
                $onComplete($refnbr, $now);
                DB::commit();
                return ['ok' => true, 'completed' => true];
            }

            $next = TrApproval::query()
                ->where('refnbr', $refnbr)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P');

            $this->orderByLevel($next);
            $next = $next->first();

            if ($next && empty($next->aprv_datebefore)) {
                $next->aprv_datebefore = $now;
                $next->save();
            }

            DB::commit();

            if ($onNotifyNext) {
                $onNotifyNext($next, $now);
            }

            return ['ok' => true, 'completed' => false];

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return ['ok' => false, 'message' => 'Approve failed'];
        }
    }

    public function rejectStep(
        string $refnbr,
        string $doctype,
        string $actorUsername,
        string $actorName,
        \Closure $onAfter
    ): array {
        $now = Carbon::now();

        [$ok, $current, $msg] = $this->assertUserCanAct($refnbr, $doctype, 'reject', $actorUsername);
        if (!$ok) return ['ok' => false, 'message' => $msg];

        DB::beginTransaction();
        try {
            $current->status         = 'R';
            $current->aprv_dateafter = $now;
            $current->aprv_username  = $actorUsername;
            $current->aprv_name      = $actorName;
            $current->save();

            TrApproval::query()
                ->where('refnbr', $refnbr)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();

            $onAfter($refnbr, $now);

            return ['ok' => true];

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return ['ok' => false, 'message' => 'Reject failed'];
        }
    }

    public function reviseStep(
        string $refnbr,
        string $doctype,
        string $actorUsername,
        string $actorName,
        \Closure $onAfter
    ): array {
        $now = Carbon::now();

        [$ok, $current, $msg] = $this->assertUserCanAct($refnbr, $doctype, 'revise', $actorUsername);
        if (!$ok) return ['ok' => false, 'message' => $msg];

        DB::beginTransaction();
        try {
            $current->status         = 'D';
            $current->aprv_dateafter = $now;
            $current->aprv_username  = $actorUsername;
            $current->aprv_name      = $actorName;
            $current->save();

            TrApproval::query()
                ->where('refnbr', $refnbr)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();

            $onAfter($refnbr, $now);

            return ['ok' => true];

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $msg = config('app.debug') ? $e->getMessage() : 'Revise failed';
            return ['ok' => false, 'message' => $msg];
        }
    }

    /** ===========================================
     *  4) API untuk Blade/UI
     *  =========================================== */
    public function getApprovalByDocument(string $refnbr, string $doctype)
    {
        $query = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('aprv_doctype', $doctype)
            ->where('status', '<>', 'X');

        $query->reorder();

        $rows = $query
            ->orderBy('created_at', 'asc')
            ->orderBy('aprv_leveling', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'aprv_leveling',
                'aprv_name',
                'aprv_datebefore',
                'aprv_dateafter',
                'status',
                'aprv_type',
                'aprv_condition',
            ]);

        return response()->json([
            'refnbr'  => $refnbr,
            'doctype' => $doctype,
            'data'    => $rows,
        ]);
    }

    
    public function checkApproval(Request $request, string $refnbr, string $action)
    {
        $user     = Auth::user();
        $username = strtolower($user->username ?? '');
        $doctype  = $request->input('doctype');

        $needsActiveStep = in_array(strtolower($action), ['approve', 'reject', 'revise'], true);

        $baseQuery = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->when($doctype, fn($q) => $q->where('aprv_doctype', $doctype))
            ->where('status', 'P');

        $this->orderByLevel($baseQuery);

        $activeStep = (clone $baseQuery)->whereNotNull('aprv_datebefore')->first();
        if ($needsActiveStep && !$activeStep) {
            $activeStep = $this->ensureActiveStep($refnbr, $doctype ?? '');
            if (!$activeStep) return response()->json(['canPerformAction' => false]);
        }

        $stepToCheck = $needsActiveStep ? $activeStep : $baseQuery->first();
        if (!$stepToCheck) {
            return response()->json(['canPerformAction' => false]);
        }

        $list = $this->normalizeApproverList($stepToCheck->aprv_username);
        $canPerform = in_array($username, $list, true);

        return response()->json([
            'canPerformAction' => $canPerform,
            'aprv_leveling'    => $stepToCheck->aprv_leveling,
            'active' => [
                'id'            => $stepToCheck->id ?? null,
                'aprv_username' => $stepToCheck->aprv_username,
                'doctype'       => $stepToCheck->aprv_doctype,
                'status'        => $stepToCheck->status,
            ],
        ]);

    }

    protected function ensureActiveStep(string $refnbr, string $doctype): ?TrApproval
    {
        $base = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->when($doctype !== '' && $doctype !== null, fn($q) => $q->where('aprv_doctype', $doctype))
            ->where('status', 'P');

        $this->orderByLevel($base);

        $firstPending = $base->first();
        if (!$firstPending) return null;

        if (empty($firstPending->aprv_datebefore)) {
            $firstPending->aprv_datebefore = Carbon::now();
            $firstPending->save();
        }

        return $firstPending;
    }

    protected function checkStockNonStock(MsApproval $rule, array $ctx): bool
    {
        $docType  = strtoupper(trim((string)($ctx['inventory_type'] ?? '')));
        $ruleCond = strtoupper(trim((string)($rule->aprv_condition ?? '')));
        return $docType !== '' && $ruleCond !== '' && $docType === $ruleCond;
    }

    protected function checkSiteParking(MsApproval $rule, array $ctx): bool
    {
        $siteParking = strtoupper(trim((string) ($ctx['site_id_parking'] ?? '')));
        $ruleCondition = strtoupper(trim((string) ($rule->aprv_condition ?? '')));

        if ($siteParking === '' || $ruleCondition === '') {
            return false;
        }

        return $siteParking === $ruleCondition;
    }
}