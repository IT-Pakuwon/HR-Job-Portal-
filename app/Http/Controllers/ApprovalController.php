<?php

namespace App\Http\Controllers;

use App\Models\MsApproval;
use App\Models\TrApproval;
use App\Models\User;
use Carbon\Carbon;
use Mail;

class ApprovalController extends Controller
{
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
            // urutkan leveling yang bisa desimal: 1.00, 1.10, 7.80, dst
            ->orderByRaw("CAST(aprv_leveling AS DECIMAL(10,2)) ASC")
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
        // SPPB: kita minta ABAlKAN nominal
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
                'aprv_leveling'      => $m->aprv_leveling,  // bisa desimal string
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

    /**
     * Kirim email ke approver level pertama (setelah generate).
     * Return: jumlah email yang berhasil dikirimi.
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
            ->where('status', 'P')
            ->orderByRaw("CAST(aprv_leveling AS DECIMAL(10,2)) ASC")
            ->first();

        if (!$firstPending) return 0;

        $subjectMap = [
            'P' => 'Waiting Approval',
            'R' => 'Rejected Approval',
            'D' => 'Revise Approval',
            'A' => 'Approved',
            'C' => 'Completed',
        ];
        $subjectSuffix = $subjectMap[$statusCode] ?? 'Notification';

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
            \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectSuffix, $refnbr, $docDisplayName) {
                $message->to($email)
                    ->subject($refnbr.' - '.$subjectSuffix.' '.$docDisplayName)
                    ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        return $emails->count();
    }

    // ApprovalController.php
    public function getApprovalByDocument(string $refnbr, string $doctype)
    {
        $data = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('aprv_doctype', $doctype)
            ->orderByRaw("CAST(aprv_leveling AS DECIMAL(10,2)) ASC")
            ->get([
                'aprv_leveling',
                'aprv_name',
                'aprv_datebefore',
                'status'
            ]);

        return response()->json([
            'refnbr'  => $refnbr,
            'doctype' => $doctype,
            'data'    => $data,
        ]);
    }

}
