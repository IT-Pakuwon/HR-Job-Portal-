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
        // sumber: header TrSPPB.is_urgent (boolean)
        return !empty($ctx['is_urgent']);
    }

    protected function checkKomputer(MsApproval $rule, array $ctx): bool
    {
        // sumber: hanya dari BARIS PERTAMA inventory_category
        $cat = mb_strtolower((string)($ctx['first_inventory_category'] ?? ''));
        if ($cat === '') return false;

        return str_contains($cat, 'komputer')
            || str_contains($cat, 'computer')
            || str_contains($cat, 'laptop')
            || str_contains($cat, 'pc');
    }

    protected function checkFixedAsset(MsApproval $rule, array $ctx): bool
    {
        // sumber: minimal ADA SATU detail inventory_sub_type = "Fixed Asset" / "FA"
        return !empty($ctx['has_fixed_asset_subtype']);
    }

    protected function checkNominal(MsApproval $rule, array $ctx): bool
    {
        // SPPB: minta ABAlKAN nominal (kecuali dokumen lain)
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
     */
    protected function getConditionChecker(string $cond): ?callable
    {
        $map = [
            'normal'       => [$this, 'checkNormal'],
            'urgent'       => [$this, 'checkUrgent'],
            'komputer'     => [$this, 'checkKomputer'],
            'fixed asset'  => [$this, 'checkFixedAsset'],
            'nominal'      => [$this, 'checkNominal'],
        ];
        $key = mb_strtolower(trim($cond));
        return $map[$key] ?? null;
    }

    /**
     * Evaluasi satu rule MsApproval terhadap context dokumen.
     */
    protected function evaluateCondition(MsApproval $rule, array $ctx): bool
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

        $cond = trim((string)$rule->aprv_condition);
        $checker = $this->getConditionChecker($cond);
        if (!$checker) return false;

        return call_user_func($checker, $rule, $ctx);
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
        array $ctx = [],          // context dokumen untuk evaluasi 'Condition'
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

        foreach ($picked as $m) {
            TrApproval::create([
                'refnbr'             => $refnbr,
                'aprv_leveling'      => $m->aprv_leveling,  // boleh desimal string (1.00, 1.10, 7.80)
                'aprv_doctype'       => $m->aprv_doctype,
                'aprv_cpnyid'        => $m->aprv_cpnyid,
                'aprv_departementid' => $m->aprv_departementid,
                'aprv_username'      => $m->aprv_username,  // bisa ; atau ,
                'aprv_name'          => $m->aprv_name,
                'aprv_type'          => $m->aprv_type,
                'aprv_condition'     => $m->aprv_condition,
                'aprv_start_nominal' => $m->aprv_start_nominal,
                'aprv_end_nominal'   => $m->aprv_end_nominal,
                // level pertama (paling kecil) diberi aprv_datebefore
                'aprv_datebefore'    => (float)$m->aprv_leveling == (float)$picked->first()->aprv_leveling ? $now : null,
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

    /**
     * Kirim email ke approver level pertama (setelah generate ATAU setelah step sebelumnya approve).
     * Return: jumlah email yang berhasil dikirim.
     */
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

        // username boleh ; atau ,
        $usernames = str_replace(';', ',', (string)$firstPending->aprv_username);
        $approvers = array_filter(array_map('trim', explode(',', $usernames)));
        if (!$approvers) return 0;

        $emails = User::query()
            ->whereIn('username', $approvers)
            ->where('status', 'A')
            ->pluck('test_email')
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
                    ->subject($refnbr.' - '.$suffix.' '.$docDisplayName)
                    ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }
        return $emails->count();
    }

    /**
     * Kirim email ke requester/creator untuk status tertentu (R/D/C).
     */
    public function notifyRequesterOnStatus(
        string $refnbr,
        string $docDisplayName,
        string $statusCode,
        string $creatorUsername,
        string $urlToDoc,
        array $extra = []
    ): int {
        $suffix = $this->subjectMap[$statusCode] ?? 'Notification';
        $user   = User::where('username', $creatorUsername)->where('status','A')->first();
        if (!$user) return 0;

        // siapkan default aman untuk template
        $defaults = [
            'docid'     => $refnbr,
            'docname'   => $docDisplayName,
            'status'    => $statusCode,
            'url'       => $urlToDoc,
            'createdby' => $user->name ?? $creatorUsername, // <<< default createdby
            'name'      => $user->name ?? $creatorUsername,
            'fullname'  => $user->name ?? $creatorUsername,
            'cpnyid'    => '',
            'deptname'  => '',
            'date'      => Carbon::now()->toDateString(),
            'info'      => '',
        ];

        $payload = array_merge($defaults, $extra);

        $to = $user->test_email ?? $user->email;
        if (!$to) return 0;

        Mail::send('emails.mailapprovenew', $payload, function ($m) use ($payload, $to, $suffix) {
            $m->to($to)
            ->subject($payload['docid'].' - '.$suffix.' '.$payload['docname'])
            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
        });

        return 1;
    }


    /** ===========================================
     *  3) Flow Generik: Approve / Reject / Revise
     *  =========================================== */

    /**
     * Pastikan user boleh bertindak pada step pending aktif.
     * Return: [bool ok, TrApproval|null step, string|null msg]
     */
    public function assertUserCanAct(string $refnbr, string $doctype, string $action, string $username): array
    {
        $needsActive = in_array(strtolower($action), ['approve','reject','revise'], true);

        $base = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('aprv_doctype', $doctype)
            ->where('status', 'P');
        $this->orderByLevel($base);

        $active = (clone $base)->whereNotNull('aprv_datebefore')->first();

        if ($needsActive && !$active) {
            // >>> AUTO-ACTIVATE jika belum ada step aktif
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


    /**
     * APPROVE satu step. Caller memberi callback:
     * - onComplete($refnbr, $now): update header/detail dokumen saat COMPLETE + email creator
     * - onNotifyNext($next, $now): (opsional) kirim email ke approver berikutnya
     */
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
            // set approved
            $current->status          = 'A';
            $current->aprv_dateafter  = $now;
            $current->aprv_username   = $actorUsername;
            $current->aprv_name       = $actorName;
            $current->save();

            // pending sisa?
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

            // aktifkan next (terendah)
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

            // notifikasi next (optional override)
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

    /**
     * REJECT step aktif. Caller memberi callback:
     * - onAfter($refnbr, $now): update header/detail + email creator (status R)
     */
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
            // set rejected
            $current->status          = 'R';
            $current->aprv_dateafter  = $now;
            $current->aprv_username   = $actorUsername;
            $current->aprv_name       = $actorName;
            $current->save();

            // batalkan pending lain
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

    /**
     * REVISE step aktif. Caller memberi callback:
     * - onAfter($refnbr, $now): update header/detail + email creator (status D)
     */
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
            // set revise
            $current->status          = 'D';
            $current->aprv_dateafter  = $now;
            $current->aprv_username   = $actorUsername;
            $current->aprv_name       = $actorName;
            $current->save();

            // batalkan pending lain
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

    // JSON untuk tabel approval di Blade
    public function getApprovalByDocument(string $refnbr, string $doctype)
    {
        $data = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('aprv_doctype', $doctype)
            ->where('status', '<>', 'X');
        $this->orderByLevel($data);

        $data = $data->get([
            'aprv_leveling',
            'aprv_name',
            'aprv_datebefore',
            'aprv_dateafter',
            'status'
        ]);

        return response()->json([
            'refnbr'  => $refnbr,
            'doctype' => $doctype,
            'data'    => $data,
        ]);
    }

    // Check tombol aksi (approve/reject/revise) boleh ditekan atau tidak
    public function checkApproval(Request $request, string $refnbr, string $action)
    {
        $user     = Auth::user();
        $username = strtolower($user->username ?? '');
        $doctype  = $request->input('doctype'); // opsional: PB/PR/...

        $needsActiveStep = in_array(strtolower($action), ['approve', 'reject', 'revise'], true);

        $baseQuery = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->when($doctype, fn($q) => $q->where('aprv_doctype', $doctype))
            ->where('status', 'P');
        $this->orderByLevel($baseQuery);

        $activeStep = (clone $baseQuery)->whereNotNull('aprv_datebefore')->first();
        if ($needsActiveStep && !$activeStep) {
            return response()->json(['canPerformAction' => false]);
        }

        $stepToCheck = $needsActiveStep ? $activeStep : $baseQuery->first();
        if (!$stepToCheck) {
            return response()->json(['canPerformAction' => false]);
        }

        $list = $this->normalizeApproverList($stepToCheck->aprv_username);
        $canPerform = in_array($username, $list, true);

        return response()->json(['canPerformAction' => $canPerform]);
    }

    // Tambah di class ApprovalController
    protected function ensureActiveStep(string $refnbr, string $doctype): ?TrApproval
    {
        $base = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('aprv_doctype', $doctype)
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

}
